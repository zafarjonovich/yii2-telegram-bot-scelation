<?php


namespace zafarjonovich\Yii2TelegramBotScelation\form\field;


use zafarjonovich\Telegram\Emoji;
use zafarjonovich\Telegram\Keyboard;
use zafarjonovich\Yii2TelegramBotScelation\form\Field;

class TimePickerFormField extends Field
{
    const TYPE_SELECTOR = 1;

    const TYPE_TAPPER = 2;

    const HOURS_OF_DAY = 24;

    const MINUTES_OF_HOUR = 60;

    public $type = self::TYPE_TAPPER;

    public $minutesDifference = 10;

    public $hourseDifference = 1;

    public $hour = null;

    public $minute = null;

    public $intervalStartHour = 0;

    public $intervalStartMinute = 0;

    public $intervalEndHour = 23;

    public $intervalEndMiniute = 59;

    public $lockBeforeNow = false;

    private $keyboard_type_selector = false;

    public $showNowButton = false;

    public $nowText = 'Now';

    public function afterOverAction(){
        $update = $this->telegramBotApi->update;

        if($update->isCallbackQuery()){
            $this->telegramBotApi->deleteMessage(
                $this->telegramBotApi->chat_id,
                $this->telegramBotApi->message_id
            );
        }
    }

    public function goBack(){
        $update = $this->telegramBotApi->update;

        if($update->isCallbackQuery()){
            $data = json_decode($update->getCallbackQuery()->getData(),true);
            return $data and isset($data['go']) and $data['go'] == 'back';
        }

        return false;
    }

    public function isSkipped()
    {
        $update = $this->telegramBotApi->update;

        if($update->isCallbackQuery()){
            $data = json_decode($update->getCallbackQuery()->getData(),true);
            return $data and isset($data['go']) and $data['go'] == 'skip';
        }

        return false;
    }

    public function goHome()
    {
        $update = $this->telegramBotApi->update;

        if($update->isCallbackQuery()){
            $data = json_decode($update->getCallbackQuery()->getData(),true);
            return $data and isset($data['go']) and $data['go'] == 'home';
        }

        return false;
    }

    public function getFormFieldValue()
    {

        $update = $this->telegramBotApi->update;

        if($update->isCallbackQuery()){
            $data = json_decode($update->getCallbackQuery()->getData(),true);

            if (isset($data['a']))
                return  $data['a'];

        }

        return null;
    }

    public function atHandling()
    {
        $update = $this->telegramBotApi->update;

        if($update->isMessage()){
            $this->telegramBotApi->deleteMessage(
                $this->telegramBotApi->chat_id,
                $this->telegramBotApi->message_id
            );
        }
    }

    public function beforeHandling()
    {
        if(isset($this->telegramBotApi->callback_query['data']) and
            $data = json_decode($this->telegramBotApi->callback_query['data'],true)
        ){

            if(isset($data['h'])){
                $this->hour = $data['h'];
            }

            if(isset($data['m'])){
                $this->minute = $data['m'];
            }

            if(isset($data['u'])){
                if($data['u'] == 'h'){
                    $this->addDeltaForHour();
                }else{
                    $this->addDeltaForMinute();
                }
            }

            if(isset($data['d'])){
                if($data['d'] == 'h'){
                    $this->removeDeltaForHour();
                }else{
                    $this->removeDeltaForMinute();
                }
            }

        }

    }

    public function addDeltaForHour()
    {
        $this->hour = ($this->hour + $this->hourseDifference <= $this->intervalEndHour)?$this->hour + $this->hourseDifference:$this->intervalStartHour;
    }

    public function removeDeltaForHour(){
        $this->hour = ($this->hour - $this->hourseDifference < $this->intervalStartHour)? $this->intervalEndHour:$this->hour - $this->hourseDifference;
    }

    public function addDeltaForMinute(){
        if($this->hour == $this->intervalStartHour) {
            $this->minute = ($this->minute + $this->minutesDifference >= self::MINUTES_OF_HOUR)?$this->intervalStartMinute:$this->minute + $this->minutesDifference;
        }else if($this->hour == $this->intervalEndHour){
            $this->minute = ($this->minute + $this->minutesDifference > $this->intervalEndMiniute)?0:$this->minute + $this->minutesDifference;
        }else{
            $this->minute = ($this->minute + $this->minutesDifference >= self::MINUTES_OF_HOUR)?0:$this->minute + $this->minutesDifference;
        }
    }

    public function removeDeltaForMinute(){
        if($this->hour == $this->intervalStartHour) {
            $this->minute = ($this->minute - $this->minutesDifference < $this->intervalStartMinute)?self::MINUTES_OF_HOUR - $this->minutesDifference:$this->minute - $this->minutesDifference;
        }else if($this->hour == $this->intervalEndHour){
            $this->minute = ($this->minute - $this->minutesDifference < 0)?$this->intervalEndMiniute:$this->minute - $this->minutesDifference;
        }else{
            $this->minute = ($this->minute - $this->minutesDifference < 0)?self::MINUTES_OF_HOUR - $this->minutesDifference:$this->minute - $this->minutesDifference;
        }
    }


    private function initTimes(){
        if ($this->lockBeforeNow) {

        }

        if($this->hour === null){
            $this->hour = 00;
        }

        if($this->minute == null){
            $this->minute = 00;
        }
    }

    private function generateSelectorKeyboard()
    {

        $default_callback = ['-'=>'-'];
        $keyboard = [];
        $buttons = [];

        $lock = Emoji::Decode('\\ud83d\\udd12');

        $locked_times = $this->params['lock']['times'] ?? [];

        for($h = $this->interval['start_hour']; $h <= $this->interval['end_hour']; $h += $this->delta_hour){
            if($this->hour == $this->interval['start_hour']){
                $start = $this->interval['start_minute'];
                $end = 59;
            }else if($this->hour == $this->interval['end_hour']){
                $start = 00;
                $end = $this->interval['end_minute'];
            }else{
                $start = 00;
                $end = 59;
            }
            for($m = $start; $m <= $end; $m += $this->delta_minute){
                $show = $this->format($h).':'.$this->format($m);
                if(in_array($show,$locked_times)){
                    $buttons[] = ['text'=>$lock,'callback_data'=>json_encode($default_callback)];
                }else{
                    $buttons[] = ['text'=>$show,'callback_data'=>json_encode(['a'=>$show])];
                }
            }
        }

        $keyboard = array_chunk($buttons,5);

        $keyboard = $this->createNavigatorButtons($keyboard);

        return $keyboard;
    }

    private function generateTapSelectorKeyboard($type){
        $default_callback = ['-'=>'-'];

        $keyboard = new Keyboard();

        $lock = Emoji::Decode('\\ud83d\\udd12');
        
        $locked_times = [];

        $n = 1;

        if($type == 'h'){
            for($e = $this->intervalStartHour; $e <= $this->intervalEndHour; $e += $this->hourseDifference){
                if(in_array($e.':'.$this->minute,$locked_times)){
                    $keyboard->addCallbackDataButton(
                        $lock,json_encode($default_callback)
                    );
                }else{
                    $keyboard->addCallbackDataButton(
                        $this->format($e),json_encode(['h' => $e, 'm' => $this->minute])
                    );
                }

                if ($n%5 == 0)
                    $keyboard->newRow();

                $n++;
            }
        }else{
            if($this->hour == $this->intervalStartHour){
                $start = $this->intervalStartHour;
                $end = 59;
            }else if($this->hour == $this->intervalEndHour){
                $start = 00;
                $end = $this->intervalEndHour;
            }else{
                $start = 00;
                $end = 59;
            }
            for($e = $start; $e <= $end; $e += $this->minutesDifference){
                if(in_array($this->hour.':'.$e,$locked_times)){
                    $keyboard->addCallbackDataButton(
                        $lock,json_encode($default_callback)
                    );
                }else{
                    $keyboard->addCallbackDataButton(
                        $this->format($e),json_encode(['h' => $this->hour, 'm' => $e])
                    );
                }

                if ($n%5 == 0)
                    $keyboard->newRow();

                $n++;
            }
        }

        return $keyboard;
    }

    private function generateTapperKeyboard()
    {
        $default_callback = ['-'=>'-'];

        $up = "🔼";
        $down = "🔽";
        $non = ' ';
        $ok = '✅';

        $hour = $this->hour;
        $minute = $this->minute;

        $keyboard = new Keyboard();

        $keyboard->addCallbackDataButton($up, json_encode(['u'=>'h','h'=>$hour,'m'=>$minute]));
        $keyboard->addCallbackDataButton($non, json_encode($default_callback));
        $keyboard->addCallbackDataButton($up, json_encode(['u'=>'m','h'=>$hour,'m'=>$minute]));

        $keyboard->newRow();

        $keyboard->addCallbackDataButton($this->format($hour), json_encode(['s'=>'h','h'=>$hour,'m'=>$minute]));
        $keyboard->addCallbackDataButton(':', json_encode($default_callback));
        $keyboard->addCallbackDataButton($this->format($minute), json_encode(['s'=>'m','h'=>$hour,'m'=>$minute]));

        $keyboard->newRow();

        $keyboard->addCallbackDataButton($down, json_encode(['d'=>'h','h'=>$hour,'m'=>$minute]));
        $keyboard->addCallbackDataButton($non, json_encode($default_callback));
        $keyboard->addCallbackDataButton($down, json_encode(['d'=>'m','h'=>$hour,'m'=>$minute]));

        $keyboard->newRow();


        if ($this->showNowButton) {
            $keyboard->newRow();
            $keyboard->addCallbackDataButton($this->nowText,json_encode(['a'=>date('H:i')]));
            $keyboard->newRow();
        }

        $keyboard->addCallbackDataButton($ok,json_encode(['a'=>self::format($hour).":".self::format($minute)]));

        return $keyboard;
    }

    private function format($value){
        $value = strval($value);
        return (strlen($value) == 1)?'0'.$value:$value;
    }

    public function render(){
        $this->initTimes();

        $api = $this->telegramBotApi;

        $update = $api->update;

        if($update->isMessage()){
            $response = $this->telegramBotApi->sendMessage(
                $this->telegramBotApi->chat_id,
                '~',
                [
                    'reply_markup' => $this->telegramBotApi->removeCustomKeyboard()
                ]
            );
            $this->telegramBotApi->deleteMessage(
                $this->telegramBotApi->chat_id,
                $response['result']['message_id']
            );
        }

        if($this->type == self::TYPE_TAPPER){

            if($update->isCallbackQuery() && ($cq = $update->getCallbackQuery()) &&
                ($data = json_decode($cq->getData(),true)) &&
                isset($data['s'])
            ){
                $keyboard = $this->generateTapSelectorKeyboard($data['s']);
            }else{
                $keyboard = $this->generateTapperKeyboard();
            }
        }else {
            $keyboard = $this->generateSelectorKeyboard();
        }

        $keyboard = $this->createNavigatorButtons($keyboard);

        $options = [
            'reply_markup' => $keyboard
        ];

        $options = array_merge($this->textOptions,$options);


        if($update->isMessage()){
            $response = $this->telegramBotApi->sendMessage(
                $this->telegramBotApi->chat_id,
                $this->text,
                $options
            );
        } else if ($update->isCallbackQuery()){
            $response = $this->telegramBotApi->editMessageText(
                $this->telegramBotApi->chat_id,
                $this->telegramBotApi->message_id,
                $this->text,
                $options
            );
        }

        if(isset($response['ok']) and $response['ok']){
            $this->state['message_id'] = $response['result']['message_id'];
        }

    }
}