<?php


namespace zafarjonovich\Yii2TelegramBotScelation\states;


class FileState extends State
{
    public $filePath;

    public function init()
    {
        if(!is_dir(dirname($this->filePath))) {
            throw new \Exception('Directory not exists');
        }

        if(!file_exists($this->filePath)) {
            $this->save();
        }

        $this->state = json_decode(file_get_contents($this->filePath),true);
    }

    public function save()
    {
        return file_put_contents($this->filePath,json_encode($this->stateData,JSON_PRETTY_PRINT));
    }

    public static function delete($unique)
    {

    }
}