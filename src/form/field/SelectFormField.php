<?php


namespace zafarjonovich\Yii2TelegramBotScelation\form\field;


use zafarjonovich\Telegram\Keyboard;
use zafarjonovich\Telegram\update\objects\Response;
use zafarjonovich\Yii2TelegramBotScelation\form\Field;
use zafarjonovich\YiiTelegramBotForm\Cache;
use zafarjonovich\YiiTelegramBotForm\FormField;

class SelectFormField extends Field
{
    public $options = [];

    const CALLBACL_QUERY_KEY = 'cqk';

    public function isSkipped()
    {
        $update = $this->telegramBotApi->update;

        if ($this->canSkip) {
            if ($update->isMessage() && $update->getMessage()->isText()) {
                return $update->getMessage()->getText() == $this->skipText;
            }

            if($this->isInlineKeyboard and $update->isCallbackQuery()){
                $data = json_decode($update->getCallbackQuery()->getData(),true);
                return $data and isset($data['go']) and $data['go'] == 'skip';
            }
        }
        return false;
    }

    public function goBack()
    {
        $update = $this->telegramBotApi->update;
        
        if($this->isInlineKeyboard and $update->isCallbackQuery()){
            $data = json_decode($update->getCallbackQuery()->getData(),true);
            return $data and isset($data['go']) and $data['go'] == 'back';
        }

        if(!$this->isInlineKeyboard and $update->isMessage()){
            return $update->getMessage()->getText() == $this->buttonTextBack;
        }

        return false;
    }

    public function goHome()
    {
        $update = $this->telegramBotApi->update;

        if($this->isInlineKeyboard and $update->isCallbackQuery()){
            $data = json_decode($update->getCallbackQuery()->getData(),true);
            return $data and isset($data['go']) and $data['go'] == 'home';
        }

        if(!$this->isInlineKeyboard and $update->isMessage()){
            return $update->getMessage()->getText() == \Yii::t('app','Home');
        }

        return false;
    }

    private function deleteCustopmMessage()
    {
        $response = $this->telegramBotApi->sendMessage(
            $this->telegramBotApi->chat_id,
            '~',
            [
                'reply_markup' => $this->telegramBotApi->removeCustomKeyboard()
            ]
        );
        $response = new Response($response);

        if($response->ok()){
            $this->telegramBotApi->deleteMessage(
                $this->telegramBotApi->chat_id,
                $response->getResult()->getMessageId()
            );
        }
    }

    public function atHandling()
    {
        $update = $this->telegramBotApi->update;

        if(($this->clearChat || $this->isInlineKeyboard) && $update->isMessage()) {
            $this->telegramBotApi->deleteCurrentMessage();

            $this->deleteCustopmMessage();

            if(isset($this->state['message_id']) && ! $this->isInlineKeyboard)
                $this->telegramBotApi->deleteMessage(
                    $this->telegramBotApi->chat_id,
                    $this->state['message_id']
                );
        }
    }

    public function beforeHandling()
    {
        $update = $this->telegramBotApi->update;

        if(!$this->isInlineKeyboard && $update->isCallbackQuery()){
            $this->telegramBotApi->deleteMessage(
                $this->telegramBotApi->chat_id,
                $this->telegramBotApi->message_id
            );
        }
    }

    public function afterFillAllFields(){
        $update = $this->telegramBotApi->update;
        if($this->isInlineKeyboard and $update->isCallbackQuery()){
            $this->telegramBotApi->deleteMessage(
                $this->telegramBotApi->chat_id,
                $this->telegramBotApi->message_id
            );
        }
    }

    public function afterOverAction()
    {
        $update = $this->telegramBotApi->update;
        if($this->isInlineKeyboard and $update->isCallbackQuery()){
            $this->telegramBotApi->deleteMessage(
                $this->telegramBotApi->chat_id,
                $this->telegramBotApi->message_id
            );
        }
    }

    public function getFormFieldValue(){
        $update = $this->telegramBotApi->update;

        if($this->isInlineKeyboard and $update->isCallbackQuery()){
            $data = json_decode($update->getCallbackQuery()->getData(),true);

            if($data and isset($data[self::CALLBACL_QUERY_KEY])){
                return $data[self::CALLBACL_QUERY_KEY];
            }
        }

        if(!$this->isInlineKeyboard and $update->isMessage()){

            $calls = [];

            foreach ($this->options as $option) {
                foreach ($option as $item) {
                    $calls[$item[1]] = $item[0];
                }
            }

            return isset($calls[$update->getMessage()->getText()])?$calls[$update->getMessage()->getText()]:null;
        }

        return null;
    }

    public function render()
    {
        $update = $this->telegramBotApi->update;

        $keyboard = new Keyboard();

        foreach ($this->options as $option) {
            foreach ($option as $item) {
                if($this->isInlineKeyboard){
                    $keyboard->addCallbackDataButton($item[1],json_encode([self::CALLBACL_QUERY_KEY => $item[0]]));
                }else{
                    $keyboard->addCustomButton($item[1]);
                }
            }
            $keyboard->newRow();
        }

        $keyboard->newRow();

        $options = [
            'reply_markup' => $this->createNavigatorButtons($keyboard)
        ];

        $options = array_merge($this->textOptions,$options);

        if (!isset($this->state['cp'])) {
            $this->deleteCustopmMessage();
        }

        if((!$this->isInlineKeyboard && $update->isCallbackQuery() && !isset($this->state['cp'])) || ($update->isMessage() && (!$this->isInlineKeyboard || !isset($this->state['cp'])))) {
            $response = $this->telegramBotApi->sendMessage(
                $this->telegramBotApi->chat_id,
                $this->text,
                $options
            );
        } else if($update->isCallbackQuery()) {
            $response = $this->telegramBotApi->editMessageText(
                $this->telegramBotApi->chat_id,
                $this->telegramBotApi->message_id,
                $this->text,
                $options
            );
        }

        if(isset($response['ok']) and $response['ok']){
            $this->state['message_id'] = $response['result']['message_id'];
            $this->state['cp'] = true;
        }
    }
}