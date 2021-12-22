<?php


namespace zafarjonovich\Yii2TelegramBotScelation\form\field;

use zafarjonovich\Telegram\Keyboard;
use zafarjonovich\Yii2TelegramBotScelation\form\Field;
use zafarjonovich\Telegram\update\objects\Response;
use zafarjonovich\Telegram\update\Update;

class MultipleField extends Field
{
    /**
     * @var \Closure
     */
    public $getter;

    public $doneText = 'Done';

    public $acceptedText = 'Accepted';

    private $isFound = false;

    public $limit;

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

        $response = $this->telegramBotApi->sendMessage(
            $this->telegramBotApi->chat_id,
            $text,
            ['reply_markup' => $this->telegramBotApi->makeCustomKeyboard([
                [['text' => \Yii::t('app','Back')]]])
            ]
        );

        $this->state['message_id'] = $response['result']['message_id'];
    }

    public function getFormFieldValue()
    {
        $update = $this->telegramBotApi->update;

        $this->state['answers'] = $this->state['answers'] ?? [];

        if (
            $this->canSkip &&
            $update->isMessage() &&
            ($message = $update->getMessage()) &&
            $message->isText() &&
            ($message->getText() == $this->skipText || $message->getText() == $this->doneText)
        ) {
            return $this->state['answers'];
        }

        $getter = $this->getter;

        $value = $getter($update);

        if ($value !== null) {
            $this->state['answers'][] = $value;
            $this->isFound = true;
        }

        if (is_numeric($this->limit) && count($this->state['answers']) == $this->limit) {
            return $this->state['answers'];
        }

        return null;
    }

    public function render(){

        if (!isset($this->state['answers'])) {
            $this->state['answers'] = [];
        }

        /** @var Update $update */
        $update = $this->telegramBotApi->update;

        if($update->isCallbackQuery()){
            $this->telegramBotApi->deleteMessage(
                $this->telegramBotApi->chat_id,
                $this->telegramBotApi->message_id
            );
        }

        $options = [];

        $keyboard = new Keyboard();

        $answers = $this->state['answers'];

        if (count($answers)) {
            $keyboard->addCustomButton($this->doneText);
        }

        $keyboard = $this->createNavigatorButtons($keyboard);

        if(!empty($keyboard)){
            $options['reply_markup'] = $keyboard;
        }

        $response = $this->telegramBotApi->sendMessage(
            $this->telegramBotApi->chat_id,
            $this->isFound ?
                    $this->acceptedText :
                    $this->text,
            $options
        );

        $response = new Response($response);

        if($response->ok()){
            $this->state['message_id'] = $response->getResult()->getMessageId();
        }
    }
}