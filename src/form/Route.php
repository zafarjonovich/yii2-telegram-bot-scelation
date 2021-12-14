<?php


namespace zafarjonovich\Yii2TelegramBotScelation\form;


class Route
{
    private $action;

    private $method;

    public function __construct($action,$method)
    {
        $this->action = $action;
        $this->method = $method;
    }

    public function getMethod()
    {
        return $this->method;
    }

    public function getAction()
    {
        return $this->action;
    }
}