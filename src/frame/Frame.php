<?php

namespace zafarjonovich\Yii2TelegramBotScelation\frame;

use yii\base\BaseObject;

class Frame extends BaseObject implements FrameInterface
{
    private $scelation;

    private $options = [];

    protected function setScelation($scelation)
    {
        $this->scelation = $scelation;
    }

    protected function addOption($key,$value)
    {
        $this->options[$key] = $value;
    }

    public function getScelation()
    {
        return $this->scelation;
    }

    public function setOptions($options)
    {
        $this->options = $options;
    }

    public function getOptions()
    {
        return $this->options;
    }
}