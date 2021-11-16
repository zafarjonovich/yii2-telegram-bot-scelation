<?php


namespace zafarjonovich\Yii2TelegramBotScelation\states;


class FileState extends State
{
    public $filePath;

    private $stateData = [];

    public function init()
    {
        if(!is_dir(dirname($this->filePath))) {
            throw new \Exception('Directory not exists');
        }

        if(!file_exists($this->filePath)) {
            $this->save();
        }

        $this->stateData = json_decode(file_get_contents($this->filePath),true);
    }

    public function has($key)
    {
        return isset($this->stateData[$this->unique][$key]);
    }

    public function get($key)
    {
        return $this->stateData[$this->unique][$key];
    }

    public function unset($key)
    {
        unset($this->stateData[$this->unique][$key]);
    }

    public function set($key, $value)
    {
        $this->stateData[$this->unique][$key] = $value;
    }

    public function save()
    {
        return file_put_contents($this->filePath,json_encode($this->stateData));
    }
}