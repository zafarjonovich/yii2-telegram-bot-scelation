<?php


namespace zafarjonovich\Yii2TelegramBotScelation\states;

use zafarjonovich\Yii2TelegramBotScelation\models\State as StateModel;


class DbState extends State
{
    /**
     * @var StateModel $state
     */
    private $stateModel;

    public function init()
    {
        $state = StateModel::findOne(['unique' => $this->unique]);

        if($state === null) {
            $state = new StateModel([
                'unique' => $this->unique,
                'created_at' => date('Y-m-d H:i:s'),
                'state' => []
            ]);
        }

        $state->status = StateModel::STATUS_ACTIVE;
        $state->updated_at = date('Y-m-d H:i:s');

        $this->stateModel = $state;
    }

    public function has($key)
    {
        return isset($this->stateModel->state[$key]);
    }

    public function get($key)
    {
        return $this->stateModel->state[$key];
    }

    public function set($key, $value)
    {
        $this->stateModel->state[$key] = $value;
    }

    public function save()
    {
        return $this->stateModel->save();
    }
}