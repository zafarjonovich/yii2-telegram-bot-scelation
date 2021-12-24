<?php


namespace zafarjonovich\Yii2TelegramBotScelation\form;


use zafarjonovich\Telegram\BotApi;
use zafarjonovich\Telegram\Keyboard;

class Field
{
    /** @var BotApi $telegramBotApi*/
    public $telegramBotApi;

    public $state = [];

    public $canGoToHome = false;

    public $canGoToBack = true;

    public $buttonTextBack = 'Back';

    public $buttonTextHome = 'Home';

    public $skipText = 'Skip';

    public $isInlineKeyboard = false;

    public $clearChat = false;

    public $keyboard = [];

    public $name;

    public $text;

    public $canSkip = false;

    public $textOptions = [];

    public function atHandling(){

    }

    public function beforeHandling(){

    }

    public function afterOverAction(){

    }

    public function showErrors($errors){

    }

    public function getFormFieldValue(){
        return false;
    }

    public function goHome(){
        return false;
    }

    public function goBack(){
        return false;
    }

    public function isSkipped()
    {
        return false;
    }

    public function render(){
        return false;
    }

    protected function addSkipButton(Keyboard $keyboard)
    {
        if ($this->canSkip)
            if ($this->isInlineKeyboard)
                $keyboard->newRow()->addCallbackDataButton($this->skipText,json_encode(['go'=>'skip']))->newRow();
            else
                $keyboard->newRow()->addCustomButton($this->skipText)->newRow();

        return $keyboard;
    }

    public function createNavigatorButtons($keyboard)
    {
        $keyboard = new Keyboard($keyboard);

        $keyboard = $this->addSkipButton($keyboard);

        if($this->isInlineKeyboard){
            if($this->canGoToBack)
                $keyboard->addCallbackDataButton($this->buttonTextBack,json_encode(['go'=>'back']));

            if($this->canGoToHome)
                $keyboard->addCallbackDataButton($this->buttonTextHome,json_encode(['go'=>'home']));

        }else {
            if ($this->canGoToBack)
                $keyboard->addCustomButton($this->buttonTextBack);

            if ($this->canGoToHome)
                $keyboard->addCustomButton($this->buttonTextHome);

        }

        return $keyboard->init();
    }
}