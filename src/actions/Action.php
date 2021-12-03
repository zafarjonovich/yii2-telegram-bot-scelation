<?php

namespace zafarjonovich\Yii2TelegramBotScelation\actions;

class Action extends \yii\base\Action
{
    public function run($method,$params = null)
    {
        call_user_func([$this,$method],$params);
    }
}