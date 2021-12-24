<?php


namespace zafarjonovich\Yii2TelegramBotScelation\form\field;


use zafarjonovich\Telegram\BotApi;
use zafarjonovich\Telegram\Emoji;
use zafarjonovich\Telegram\Keyboard;
use zafarjonovich\Yii2TelegramBotScelation\form\Field;

class CalendarFormField extends Field
{

    public $days = ['M','T','W','T','F','S','S'];

    public $months = [
        'January',
        'February',
        'March',
        'April',
        'May',
        'June',
        'July',
        'August',
        'September',
        'October',
        'November',
        'December'
    ];

    public $lock = false;

    public $date = 'now';

    public $isInlineKeyboard = true;

    /**
     * @var array of locked days
     *
     * Example: $lockedDays = ['2021-12-23','2021-12-14'];
     */
    public $lockedDays = [];

    public $lockBeforeNow = false;

    /**
     * @var int this number means if this number set this column will locks
     * Example: $lockedColumnDayNumber = 1; Every monday will locks
     * Example: $lockedColumnDayNumber = 5; Every friday will locks
     */
    public $lockedColumnDayNumber = 0;

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

    public function beforeHandling()
    {

        $update = $this->telegramBotApi->update;

        if($update->isCallbackQuery()){
            $data = json_decode($update->getCallbackQuery()->getData(),true);

            if($data and isset($data['todate'])){
                $this->date = $data['todate'];
            }
        }
    }

    public function atHandling()
    {
        $update = $this->telegramBotApi->update;

        if($update->isMessage()){
            $this->telegramBotApi->deleteMessage(
                $this->telegramBotApi->chat_id,
                $this->telegramBotApi->message_id
            );
            $this->telegramBotApi->message = false;
        }
    }

    public function afterOverAction()
    {
        $update = $this->telegramBotApi->update;

        if($update->isCallbackQuery()){
            $this->telegramBotApi->deleteMessage(
                $this->telegramBotApi->chat_id,
                $this->telegramBotApi->message_id
            );
        }

    }

    public function getFormFieldValue()
    {
        $update = $this->telegramBotApi->update;

        if($update->isCallbackQuery()){
            $data = json_decode($update->getCallbackQuery()->getData(),true);

            if($data and isset($data[$this->name])){
                return $data[$this->name];
            }
        }

        return null;
    }

    private function isLockedDay($year,$month,$day)
    {
        $lock_day = false;

        $mktime = mktime(0,0,0,$month,$day,$year);

        if ($this->lockedColumnDayNumber == date('N',$mktime))
            $lock_day = true;

        if ($this->lockBeforeNow && $mktime < strtotime('Today'))
            $lock_day = true;

        if (in_array(date('Y-m-d',$mktime),$this->lockedDays))
            $lock_day = true;

        return $lock_day;
    }

    private function getHeaderText($year,$month)
    {
        return "{$year}-{$this->months[($month-1)]}";
    }

    private function getEmptyText()
    {
        return ' ';
    }

    private function getDayText($day)
    {
        return strlen($day) == 1?"0$day":"$day";
    }

    private function getKeyboard()
    {
        $date = new \DateTime($this->date);

        $count_days_of_week = 7;
        $default_callback = '-';
        $lock = Emoji::Decode('\\ud83d\\udd12');

        $year = $date->format('Y');
        $month = $date->format('m');

        $keyboard = new Keyboard();

        $keyboard->addCallbackDataButton($this->getHeaderText($year,$month),$default_callback);

        $keyboard->newRow();

        foreach ($this->days as $day) {
            $keyboard->addCallbackDataButton($day,$default_callback);
        }

        $keyboard->newRow();

        $n = 0;

        if(($first_q = date("N",strtotime("First day of {$year}-{$month}"))-1)%$count_days_of_week){
            for($i=0;$i<$first_q;$i++){
                ++$n;
                $keyboard->addCallbackDataButton($this->getEmptyText(),$default_callback);
            }
        }

        $count_of_days = date("d",strtotime("Last day of {$year}-{$month}"));


        for($d=1;$d<=$count_of_days;$d++){

            $name = $d;
            $d = (strlen($d) == 1)?'0'.$d:$d;
            $callback = [$this->name => "{$year}-{$month}-{$d}"];

            if($this->isLockedDay($year,$month,$d)){
                $name = $lock;
                $callback = $default_callback;
            }

            $keyboard->addCallbackDataButton($name,json_encode($callback));

            if(++$n%$count_days_of_week == 0){
                $keyboard->newRow();
            }
        }

        unset($n);

        $last_q = (($q = ($first_q+$count_of_days)%$count_days_of_week) != 0)?$count_days_of_week-$q:0;

        if($last_q){
            for($i=0;$i<$last_q;$i++){
                $keyboard->addCallbackDataButton($this->getEmptyText(),$default_callback);
            }
        }

        $keyboard->newRow();

        if(
            strtotime("23:59",strtotime("Last day of",strtotime("{$year}-{$month}"))) > strtotime("00:01",strtotime("First day of",time())) and
            strtotime("00:01",strtotime("First day of",time())) != strtotime("00:01",strtotime("First day of",strtotime("{$year}-{$month}")))
        ){
            $prev_callback = ['todate'=>date("Y-m",strtotime("First day of last month",strtotime("{$year}-{$month}")))];
            $keyboard->addCallbackDataButton(Emoji::Decode("\\u2b05\\ufe0f"),json_encode($prev_callback));
        }

        $next_callback = ['todate'=>date("Y-m",strtotime("First day of next month",strtotime("{$year}-{$month}")))];
        $keyboard->addCallbackDataButton(Emoji::Decode("\\u27a1\\ufe0f"),json_encode($next_callback));

        $keyboard = $this->createNavigatorButtons($keyboard);

        return $keyboard;
    }

    public function render()
    {
        $update = $this->telegramBotApi->update;

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

        $keyboard = $this->getKeyboard();


        $options = [
            'reply_markup' =>$keyboard
        ];

        $options = array_merge($this->textOptions,$options);
        
        if((bool)$this->telegramBotApi->message){
            $response = $this->telegramBotApi->sendMessage(
                $this->telegramBotApi->chat_id,
                $this->text,
                $options
            );
        }else{
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