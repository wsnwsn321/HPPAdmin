<?php
/* CaryYe 23/03/2018 4:43 PM */
namespace app\WxComponents\event;
use app\models\wx\User;
use app\models\WechatUsers;

class Subscribe extends Base
{
    /** @inheritdoc */
    public function exe()
    {
        $weChat = \yii::createObject("weChat")->obj();
        $host = \yii::$app->params["WapHost"];
        $url = $host."/match/?";
        $loginUrl = $url.http_build_query([
                "php7Login_WeChat" => $weChat->wechatId,
                "php7Login_base" => 1
            ]);

        $user = new User();
        $info = $user->snsApiBase($this->data["FromUserName"]);
        $Info["subscribe"] = 1;
        $info["wechatId"] = $weChat->id;
        $m = WechatUsers::findOne([
            "openid" => $info["openid"],
            "wechatId" => $info["wechatId"]
        ]);
        if (is_null($m)) {
            $m = new WechatUsers(["scenario" => WechatUsers::SCENARIO_CREATE]);
        }
        $m->setProperties($info);
        $m->save($info);

        // Reply a text message to user who subscribed out WeChat account.
        return \app\WxComponents\Replies::text(
            $this->data["FromUserName"],
            $this->data["ToUserName"],
            "Hi !"/*$loginUrl*/
        );
    }
}