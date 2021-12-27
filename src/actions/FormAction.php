<?php


namespace zafarjonovich\Yii2TelegramBotScelation\actions;

use zafarjonovich\Yii2TelegramBotScelation\controllers\Controller;
use zafarjonovich\Yii2TelegramBotScelation\form\Field;
use zafarjonovich\Yii2TelegramBotScelation\form\Model;
use zafarjonovich\Yii2TelegramBotScelation\form\Route;

class FormAction extends \yii\base\Action
{
    const CONFIGURATION = 'zafarjonovich-Yii2TelegramBotScelation-form-Form-CONFIGURATION';

    /**
     * @var Model $model
     */
    private $model;

    /**
     * @var Controller $controller
     */
    public $controller;

    private $modelState = [];

    private $fieldState = [];

    private $fieldName = null;

    private $isFinished = false;

    public function start($configuration)
    {
        $configuration = array_merge(
            $configuration,
            [
                'answers' => [],
                'modelState' => [],
                'fieldState' => [],
                'fieldName' => null
            ]
        );

        $this->controller->state->set(self::CONFIGURATION,$configuration);
        $this->runWithParams([]);
    }

    protected function beforeRun()
    {
        if (!$this->controller->state->has(self::CONFIGURATION)) {
            throw new \Exception(self::class .' configuration not found');
        }

        $configuration = $this->controller->state->get(self::CONFIGURATION);

        if (!isset($configuration['model'])) {
            throw new \Exception('Property model must install in configuration');
        }

        /**
         * @var Model $model
         */
        $this->model = \Yii::createObject($configuration['model']);

        $this->model->state = $configuration['modelState'];

        $this->fieldState = $configuration['fieldState'];
        $this->modelState = $configuration['modelState'];
        $this->fieldName = $configuration['fieldName'];

        $this->model->setValues($configuration['answers']);

        return parent::beforeRun();
    }

    private function callback(Route $route)
    {
        $action = $this->controller->createAction($route->getAction());
        $answers = $this->model->getValues();
        $answers = array_merge($this->model->hiddenInputs,$answers);
        $callClass = $this->model->callClass();
        $call = $callClass !== null ? \Yii::createObject($callClass,$answers) : $answers;
        $this->isFinished = true;
        call_user_func([$action,$route->getMethod()],$call);
    }

    private function getFieldOptions()
    {
        return [
            'telegramBotApi' => $this->controller->api,
            'state' => $this->fieldState,
            'canGoToHome' => $this->model->canGoHome(),
            'buttonTextBack' => $this->model->buttonTextBack,
            'buttonTextHome' => $this->model->buttonTextHome,
            'skipText' => $this->model->buttonTextSkip,
        ];
    }

    public function goHome($field)
    {
        $field->afterOverAction();
        $this->callback($this->model->getFailRoute());
    }

    private function goBack($field)
    {
        if ($this->model->isEmty()) {
            $this->goHome($field);
        } else {
            $this->model->unsetLastAnswer();
            $this->run();
        }
    }

    /**
     * @param $fieldData
     * @return Field
     * @throws \yii\base\InvalidConfigException
     */
    private function createField($fieldData)
    {
        return \Yii::createObject(array_merge($fieldData,$this->getFieldOptions()));
    }

    private function goNextStep($field)
    {
        if ($this->model->isFilled()) {
            $field->afterOverAction();
            $this->callback($this->model->getSuccessRoute());
        } else {
            $this->run();
        }
    }

    public function run()
    {
        static $renderCount = 1;

        if ($renderCount > 1)
            return;

        $fieldData = $this->model->getCurrentFormFieldData();

        $field = $this->createField($fieldData);

        $field->beforeHandling();

        if ($this->fieldName == $field->name) {

            $field->atHandling();

            $value = '';

            if ($field->isSkipped()) {
                $this->model->filled($field->name);
                $this->model->{$field->name} = null;
                $this->goNextStep($field);
                return;
            } else if ($field->goHome()) {
                $this->goHome($field);
                return;
            } else if ($field->goBack()) {
                $this->goBack($field);
                $this->fieldState = [];
                return;
            } else if (
                ((($value = $field->getFormFieldValue()) !== null) &&
                $this->model->validateCurrentField($this->fieldName,$value))
            ) {
                $this->model->filled($field->name);
                $this->goNextStep($field);
               return;
            } else if($errors = $this->model->getErrors($this->fieldName)){
                $this->model->{$this->fieldName} = null;
                $field->showErrors($errors);
                return;
            }
        } else {
            $field->state = [];
        }

        $newFieldData = $this->model->getCurrentFormFieldData();

        if (json_encode($newFieldData) != json_encode($fieldData)) {
            $field = $this->createField($newFieldData);
        }

        $field->render();

        $this->modelState = $this->model->state;
        $this->fieldState = $field->state;
        $this->fieldName = $field->name;

        $renderCount++;
    }

    protected function afterRun()
    {
        if (!$this->isFinished) {
            $configuration = $this->controller->state->get(self::CONFIGURATION);
            $configuration['fieldState'] = $this->fieldState;
            $configuration['modelState'] = $this->modelState;
            $configuration['fieldName'] = $this->fieldName;
            $configuration['answers'] = $this->model->getValues();
            $this->controller->state->set(self::CONFIGURATION,$configuration);
        } else {
            $this->controller->state->unset(self::CONFIGURATION);
        }
    }
}