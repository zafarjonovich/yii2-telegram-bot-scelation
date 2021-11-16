<?php

namespace zafarjonovich\Yii2TelegramBotScelation\controllers;

use zafarjonovich\Telegram\BotApi;
use zafarjonovich\Telegram\update\Update;
use zafarjonovich\Yii2TelegramBotScelation\route\Route;
use zafarjonovich\Yii2TelegramBotScelation\route\RouteManager;
use zafarjonovich\Yii2TelegramBotScelation\states\FileState;
use zafarjonovich\Yii2TelegramBotScelation\states\State;

class Controller extends \yii\web\Controller
{
    public $enableCsrfValidation = false;

    /**
     * @var BotApi $api
     */
    public $api;

    /**
     * @var RouteManager $routeManager
     */
    public $routeManager;

    /**
     * @var State $state
     */
    public $state;

    public $hasUpdate = false;

    /**
     * Telegram bot token
     *
     * @return string
     */
    public function getToken()
    {
        return '';
    }

    /**
     * Bot state configuration
     *
     * @return array
     */
    public function getStateManagerConfiguration($chat_id)
    {
        return [
            'class' => FileState::class,
            'unique' => $chat_id,
            'filePath' => \Yii::getAlias('@webroot/state.json')
        ];
    }

    public function loadUpdate()
    {
        $update = $this->api->getWebHookUpdate();

        if(!$update) {
            return false;
        }

        $this->api->update = new Update($update);
        $this->api->invokeUpdates();

        return true;
    }

    public function init()
    {
        parent::init();

        $this->api = new BotApi($this->getToken());

        $this->routeManager = new RouteManager();
    }

    public function otherCondition(Update $update)
    {
    }

    public function canHandle()
    {
        return true;
    }

    public function actionHandle()
    {
        if($this->loadUpdate() && $this->canHandle()) {

            $this->state = \Yii::createObject($this->getStateManagerConfiguration($this->api->chat_id));
            $update = $this->api->update;

            try {
                if(
                    $update->isCallbackQuery() &&
                    $route = $this->routeManager->initRoute(json_decode($update->getCallbackQuery()->getData()))
                ) {
                    $action = $this->createAction($route->getAction());

                    if($action === null) {
                        throw new \Exception('Invalid action');
                    }

                    $action->runWithParams([
                        'method' => $route->getMethod(),
                        'params' => $route->getParams()
                    ]);

                } else {

                    $this->otherCondition($update);
                }
            } catch (\Exception $exception) {
                throw $exception;
            }

            $this->state->save();
        }

        return 'Running';
    }
}
