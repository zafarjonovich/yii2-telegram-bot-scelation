<?php


namespace zafarjonovich\Yii2TelegramBotScelation\form;


use zafarjonovich\Telegram\helpers\Call;

abstract class Model extends \yii\base\Model
{
    public $state = [
        'filledFields' => []
    ];


    public $hiddenInputs = [];

    public $buttonTextBack = 'Back';

    public $buttonTextHome = 'Home';

    public $buttonTextSkip = 'Skip';

    protected $formValues = [];

    /**
     * @return Route
     */
    abstract public function getSuccessRoute();

    /**
     * @return Route
     */
    abstract public function getFailRoute();

    /**
     * @return array
     */
    abstract public function formFields();

    public function canGoHome()
    {
    }

    /**
     * @return Call|null
     */
    public function callClass()
    {
        return null;
    }

    public function attributes()
    {
        return array_column($this->formFields(),'name');
    }

    public function setValues($values)
    {
        $this->setAttributes($values,false);
    }

    public function getValues()
    {
        return $this->getAttributes($this->attributes());
    }

    /**
     * @return array|null
     */
    public function getCurrentFormFieldData()
    {
        foreach ($this->formFields() as $formField)
            if (!isset($this->state['filledFields'][$formField['name']]))
                return $formField;

        return null;
    }

    public function validateCurrentField($name,$value){
        $this->{$name} = $value;
        return $this->validate([$name]);
    }

    public function isEmty()
    {
        return !(bool)($this->state['filledFields'] ?? []);
    }

    public function isFilled()
    {
        foreach ($this->formFields() as $formField) {

            if (!isset($this->state['filledFields'][$formField['name']]))
                return false;
        }

        return true;
    }

    public function filled($name)
    {
        $this->state['filledFields'][$name] = true;
    }

    public function unsetLastAnswer()
    {
        $fields = $this->formFields();

        $fields = array_reverse($fields);

        foreach ($fields as $field) {
            $name = $field['name'];
            if (isset($this->state['filledFields'][$name])) {
                unset($this->state['filledFields'][$name]);
                $this->{$name} = null;
                return true;
            }
        }

        return false;
    }
}