<?php
/* CaryYe 21/03/2018 5:39 PM */
namespace app\WxComponents;

/**
 * Class Signature
 * @package app\WxComponents
 * Describe: Verify public/describe WeChat accounts
 */
class Signature extends \yii\base\Component
{
    /** @var \app\WxComponents\Instance $weChat  */
    private $weChat = null;

    /** inheritdoc */
    public function __construct(array $config = [], Instance $weChat)
    {
        $this->weChat = $weChat;
        parent::__construct($config);
    }

    /**
     * @param $signature
     * @param $timestamp
     * @param $nonce
     * @return bool
     */
    public function verify($signature, $timestamp, $nonce)
    {
        $token = trim($this->weChat->obj()->verifyToken);
        $tmpArr = array($token,$timestamp,$nonce);
        sort($tmpArr, SORT_STRING);
        $tmpArr = implode($tmpArr);
        $tmpArr = sha1($tmpArr);

        return $tmpArr === $signature ? true : false;
    }
}