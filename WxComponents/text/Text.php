<?php
/* CaryYe 2018/4/13 4:06 PM */
namespace app\WxComponents\text;

class Text extends \app\WxComponents\event\Base
{
    /** @inheritdoc */
    public function exe()
    {
        $text = "Hi!";

        // Reply a text message to user who sent out messages.
        return \app\WxComponents\Replies::text(
            $this->data["FromUserName"],
            $this->data["ToUserName"],
            $text
        );
    }
}