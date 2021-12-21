<?php

namespace zafarjonovich\Yii2TelegramBotScelation\controllers;

use app\actions\UserAction;
use yii\web\Response;
use zafarjonovich\Telegram\BotApi;
use zafarjonovich\Telegram\call\exception\CallParseException;
use zafarjonovich\Telegram\update\Update;
use zafarjonovich\Yii2TelegramBotScelation\calls\RouteManagerCall;
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

    public $throwException = true;

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

        $this->response->format = Response::FORMAT_JSON;
    }

    public function otherCondition(Update $update)
    {
    }

    public function canHandle(Update $update)
    {
        return true;
    }

    public function afterStateLoad()
    {
    }

    public function afterHandle()
    {
    }

    public function onStart($param = null)
    {
    }

    public function catchException(\Exception $exception)
    {
    }

    public function actionHandle()
    {
        try {
            if($this->loadUpdate()) {

                $this->state = \Yii::createObject($this->getStateManagerConfiguration($this->api->from_id));
                $update = $this->api->update;
                $this->afterStateLoad();

                if ($this->canHandle($update)) {

                    try {
                        if ($update->isCallbackQuery()) {

                            $call = RouteManagerCall::parse($update->getCallbackQuery()->getData());

                            $route = $this->routeManager->initRoute($call);

                            $action = $this->createAction($route->getAction());

                            if($action === null) {
                                throw new \Exception('Invalid action');
                            }

                            $action->runWithParams([
                                'method' => $route->getMethod(),
                                'params' => $route->getParams()
                            ]);
                        }
                    } catch (CallParseException $exception) {
                    } catch (\Exception $exception) {
                        throw $exception;
                    }

                    if (
                        $update->isMessage() &&
                        ($message = $update->getMessage()) &&
                        $message->isText() &&
                        strpos($message->getText(),'/start') !== false
                    ) {
                        $this->onStart(substr($message->getText(),6));
                    } else {
                        $this->otherCondition($update);
                    }



                    $this->state->save();
                }

                $this->afterHandle();
            }
        } catch (\Exception $exception) {
            $this->catchException($exception);

            if ($this->throwException)
                throw $exception;
        }

        return 'Running';
    }
}
