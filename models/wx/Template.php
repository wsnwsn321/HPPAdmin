<?php
/** User: CaryYe , 2018/7/26 10:35 PM */
namespace app\models\wx;
use app\components\HttpRequest;

/**
 * Class Template
 * @package app\models
 */
class Template extends Base
{
    /** @return string  */
    public function setIndustries($industries = [
        "industry_id1" => 2,
        "industry_id2" => 38
    ])
    {
        $url = "https://api.weixin.qq.com/cgi-bin/template/api_set_industry";
        $url .= '?'.http_build_query(["access_token" => $this->obtain()]);
        $industries = json_encode($industries);

        $http = new HttpRequest();
        $r = $http->send($url, $industries, "POST");
        return $r;
    }

    /** @return string */
    public function getIndustries()
    {
        $url = "https://api.weixin.qq.com/cgi-bin/template/get_industry";
        $url .= '?'.http_build_query(["access_token" => $this->obtain()]);

        $http = new HttpRequest();
        return $http->send($url, [], "GET");
    }

    /** @return string */
    public function getList()
    {
        $url = "https://api.weixin.qq.com/cgi-bin/template/get_all_private_template";
        $url .= '?'.http_build_query(["access_token" => $this->obtain()]);

        $http = new HttpRequest();
        return $http->send($url, [], "GET");
    }

    /** @return string */
    public function send($openid, $contest)
    {
        $weChat = \yii::createObject("weChat")->obj();
        $url = "https://api.weixin.qq.com/cgi-bin/message/template/send";
        $url .= '?'.http_build_query(["access_token" => $this->obtain()]);

        $tpl = trim($weChat->templateMsg);
        if ($tpl === '') {
            return false;
        }
        $tpl = json_decode($tpl, true);
        if (!isset($tpl["over"])) {
            return false;
        }
        $tpl = trim($tpl["over"]);

        $data = [
            "touser" => $openid,
            "template_id" => $tpl,
            "url" => "http://wp.happypingpang.com/match/weixinView/{$contest->game->id}?php7Login_WeChat={$weChat->wechatId}&php7Login_firm={$contest->game->firmId}&php7Login_base=1",
            "data" => [
                "first" => [
                    "value" => "您参与的 {$contest->name} 已经结束!",
                    "color" => "#173177"
                ],
                "keyword1" => [
                    "value" => "{$contest->name}",
                    "color" => "#173177"
                ],
                "keyword2" => [
                    "value" => date("Y-m-d H:i:s", time()),
                    "color" => "#173177"
                ],
                "remark" => [
                    "value" => "赛事结束 , 感谢您的使用",
                    "color" => "#173177"
                ]
            ]
        ];
        $http = new HttpRequest();
        return $http->send($url, json_encode($data), "POST");
    }
}