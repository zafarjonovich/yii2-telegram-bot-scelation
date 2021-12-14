<?php


namespace zafarjonovich\Yii2TelegramBotScelation\states;

use yii\base\BaseObject;
use yii\helpers\ArrayHelper;

/**
 * @property string $unique
 */

abstract class State extends BaseObject
{
    public $unique;

    protected $state;

    /**
     * @param $key
     * @return bool
     */
    public function has($key)
    {
        return ArrayHelper::keyExists($key,$this->state);
    }

    /**
     * @param $key
     * @return mixed
     * @throws \Exception
     */
    public function get($key,$default = null)
    {
        return ArrayHelper::getValue($this->state,$key,$default);
    }

    /**
     * @param $key
     * @param $value
     */
    public function set($key, $value)
    {
        ArrayHelper::setValue($this->state,$key,$value);
    }

    /**
     * @param $key
     * @param null $default
     * @return mixed|null
     */
    public function unset($key,$default = null)
    {
        return ArrayHelper::remove($this->state,$key);
    }

    /**
     * @param null $except
     * @throws \Exception
     */
    public function flush($except = null)
    {
        if (is_array($except)) {
            $state = [];
            foreach ($except as $item)
                if ($this->has($item))
                    $state[$item] = $this->get($item);

            $this->state = $state;
        } else {
            $this->state = [];
        }
    }

    abstract public function save();
}