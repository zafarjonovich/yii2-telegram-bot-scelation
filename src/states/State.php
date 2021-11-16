<?php


namespace zafarjonovich\Yii2TelegramBotScelation\states;

use yii\base\BaseObject;

/**
 * @property string $unique
 */

abstract class State extends BaseObject
{
    public $unique;

    public function init()
    {
    }

    abstract public function has($key);

    abstract public function get($key);

    abstract public function unset($key);

    abstract public function set($key,$value);

    abstract public function save();
}