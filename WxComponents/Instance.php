<?php
/* CaryYe 22/03/2018 1:42 PM */
namespace app\WxComponents;
use app\models\User;
use app\models\Wechats;

/**
 * Class Instance
 * @package app\WxComponents
 */
class Instance extends \yii\base\Component
{
    /** @var $obj: Instance of Wechats */
    private $weChatObj = null;

    /** @var $obj: WechatId. */
    public $weChatId = "";

    /** @inheritdoc */
    public function init()
    {
        if ($this->weChatId === "") {
            throw new \Exception("Null");
        }


        if (is_null($this->weChatObj)) {

            $this->weChatObj = Wechats::findOne([
                "wechatId" => $this->weChatId
            ]);

            if (is_null($this->weChatObj)) {
                throw new \Exception("Null");
            }
        }
    }

    /** @return \app\models\Wechats|null */
    public function obj()
    {
        return $this->weChatObj;
    }

    /**
     * @var \app\models\Wechats $obj
     * @return void;
     */
    public function setObj(\app\models\Wechats $obj)
    {
        $this->weChatObj = $obj;
    }
}