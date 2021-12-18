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
        $state = StateModel::findOne(['chat_id' => $this->unique]);

        if($state === null) {
            $state = new StateModel([
                'chat_id' => $this->unique,
                'created_at' => date('Y-m-d H:i:s'),
                'state' => []
            ]);
        }

        $state->status = StateModel::STATUS_ACTIVE;
        $state->updated_at = date('Y-m-d H:i:s');
        $this->state = $state->state;

        $this->stateModel = $state;
    }

    public function save()
    {
        $this->stateModel->state = $this->state;
        return $this->stateModel->save();
    }

    public static function delete($unique)
    {
        $state = StateModel::findOne(['chat_id' => $unique]);

        return $state->delete();
    }
}