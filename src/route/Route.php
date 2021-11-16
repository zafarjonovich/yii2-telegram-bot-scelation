<?php


namespace zafarjonovich\Yii2TelegramBotScelation\route;


class Route
{
    private $configuration;

    private $params;

    public function __construct($configuration,$params)
    {
        $this->configuration = $configuration;
        $this->params = $params;
    }

    public function getParams()
    {
        return $this->params;
    }

    public function getMethod()
    {
        return $this->configuration['method'];
    }

    public function getAction()
    {
        return $this->configuration['action'];
    }
}