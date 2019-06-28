<?php
/* CaryYe 2018/4/18 4:35 PM */
namespace app\WxComponents\event;
use app\models\User;
use app\models\WechatUsers;

class Unsubscribe extends Base
{
    /** @inheritdoc */
    public function exe()
    {
        \yii::info($this->data, "WX");
        $weChat = \yii::createObject("weChat")->obj();

        $info["wechatId"] = $weChat->id;
        $m = WechatUsers::findOne([
            "openid" => $this->data["FromUserName"],
            "wechatId" => $weChat->id
        ]);

        if (! is_null($m)) {
            $m->afterFlag = false;
            $m->setAttribute("isFollow", 0);
            $m->save();

            $t = User::findOne(["id" => $m->userId]);
            if (! is_null($t)) {
                $t->setAttribute("isFollow", 0);
                $t->save();
            }
        }

        $u = User::findOne([
            "username" => $this->data["FromUserName"],
            "wechatId" => $weChat->id
        ]);

        if (! is_null($u)) {
            $u->setAttribute("isFollow", 0);
            $u->save();
        }

        return "";
    }
}