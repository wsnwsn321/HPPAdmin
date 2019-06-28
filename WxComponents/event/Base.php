<?php
/* CaryYe 23/03/2018 4:45 PM */
namespace app\WxComponents\event;

class Base extends \yii\base\Component
{
    /** @var array $data , POST DATA of WeChat */
    public $data;

    /** @return mixed */
    public function exe()
    {
        return "";
    }
}