<?php
/* CaryYe 2018/4/13 4:24 PM */
namespace app\WxComponents;

/**
 * Class Replies
 * @package app\WxComponents
 */
class Replies extends \yii\base\Component
{
    /**
     * @param $ToUser , OpenId of user who will receive the message.
     * @param $FromUser , WeChat account.
     * @param $text , Message content.
     * @return string , Xml construct
     */
    public static function text($ToUser, $FromUser, $text)
    {
        $replyText  = "<xml>";
        $replyText .= "<ToUserName><![CDATA[$ToUser]]></ToUserName>";
        $replyText .= "<FromUserName><![CDATA[$FromUser]]></FromUserName>";
        $replyText .= "<CreateTime>".time()."</CreateTime>";
        $replyText .= "<MsgType><![CDATA[text]]></MsgType>";
        $replyText .= "<Content><![CDATA[$text]]></Content>";
        $replyText .= "</xml>";
        return $replyText;
    }
}