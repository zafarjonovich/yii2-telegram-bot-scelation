<?php


namespace zafarjonovich\Yii2TelegramBotScelation\form\field;

use zafarjonovich\Yii2TelegramBotScelation\form\Field;
use zafarjonovich\Telegram\update\objects\Response;
use zafarjonovich\Telegram\update\Update;

class TextFormField extends Field
{

    public function isSkipped()
    {
        /** @var Update $update */
        $update = $this->telegramBotApi->update;

        if(
            $update->isMessage() and
            $update->getMessage()->isText() and
            $update->getMessage()->getText() == $this->skipText
        ){
            return true;
        }

        return false;
    }

    public function goBack(){
        /** @var Update $update */
        $update = $this->telegramBotApi->update;

        if(
            $update->isMessage() and
            $update->getMessage()->isText() and
            $update->getMessage()->getText() == $this->buttonTextBack
        ){
            return true;
        }

        return false;
    }

    public function goHome(){
        /** @var Update $update */
        $update = $this->telegramBotApi->update;

        if(
            $update->isMessage() and
            $update->getMessage()->isText() and
            $update->getMessage()->getText() == $this->buttonTextHome
        ){
            return true;
        }

        return false;
    }

    public function atHandling(){

        if($this->clearChat){
            $this->telegramBotApi->deleteMessage(
                $this->telegramBotApi->chat_id,
                $this->telegramBotApi->message_id
            );
            if(isset($this->state['message_id'])){
                $this->telegramBotApi->deleteMessage(
                    $this->telegramBotApi->chat_id,
                    $this->state['message_id']
                );
            }
        }
    }

    public function showErrors($errors){
        $text = implode(PHP_EOL.PHP_EOL,$errors);

        $options = ['reply_markup' => $this->telegramBotApi->makeCustomKeyboard([
            [['text' => \Yii::t('app','Back')]]])
        ];

        $options = array_merge($this->textOptions,$options);

        $response = $this->telegramBotApi->sendMessage(
            $this->telegramBotApi->chat_id,
            $text,
            $options
        );

        $this->state['message_id'] = $response['result']['message_id'];
    }

    public function getFormFieldValue(){
        /** @var Update $update */
        $update = $this->telegramBotApi->update;

        if(!($update->isMessage() and $update->getMessage()->isText())){
            return false;
        }

        $value = $update->getMessage()->getText();

        return $value;
    }

    public function render(){

        /** @var Update $update */
        $update = $this->telegramBotApi->update;

        if($update->isCallbackQuery()){
            $this->telegramBotApi->deleteMessage(
                $this->telegramBotApi->chat_id,
                $this->telegramBotApi->message_id
            );
        }

        $options = [];

        $keyboard = $this->createNavigatorButtons($this->keyboard);

        if(!empty($keyboard)){
            $options['reply_markup'] = $keyboard;
        }

        $response = $this->telegramBotApi->sendMessage(
            $this->telegramBotApi->chat_id,
            $this->text,$options
        );

        $response = new Response($response);

        if($response->ok()){
            $this->state['message_id'] = $response->getResult()->getMessageId();
        }
    }
}