<?php
/** User: CaryYe , 2018/4/16 9:34 AM */
namespace app\models\wx;
use app\components\HttpRequest;

/**
 * Class User
 * @package app\models\wx
 */
class User extends Base
{
    /**
     * @param string $type
     * @return string
     */
    public function getCodeUrl($type = "snsapi_userinfo", $backUrl = "", $firmId = 0)
    {
        $weChat = \yii::createObject("weChat")->obj();
        $weChatId = $weChat->wechatId;
        $apiUrl = \yii::$app->params["ApiHost"]."active-we-api/user/";
        $url = "https://open.weixin.qq.com/connect/oauth2/authorize";

        $params = [
            "appid" => $weChat->appID,
            "redirect_uri" => $apiUrl.$weChatId.'/'.$type."?backUrl=$backUrl&firm=$firmId",
            "response_type" => "code",
            "scope" => $type
        ];

        return $url.'?'.http_build_query($params)."#wechat_redirect";
    }

    /**
     * @param $code
     * @return array
     */
    public function exchangeAccessToken($code)
    {
        $weChat = \yii::createObject("weChat")->obj();
        $url = "https://api.weixin.qq.com/sns/oauth2/access_token";

        $params = [
            "appid" => $weChat->appID,
            "secret" => $weChat->appsecret,
            "code" => $code,
            "grant_type" => "authorization_code"
        ];

        $http = new HttpRequest();
        return json_decode($http->send($url, $params), true);
    }

    /**
     * @param $accessToken
     * @param $openId
     * @param string $lang
     * @return array
     */
    public function snsApiUserInfo($accessToken, $openId, $lang = "zh_CN")
    {
        $url = "https://api.weixin.qq.com/sns/userinfo";
        $params = [
            "access_token" => $accessToken,
            "openid" => $openId,
            "lang" => $lang
        ];

        $http = new HttpRequest();
        return json_decode($http->send($url, $params), true);
    }

    /**
     * @param $openId
     * @param string $lang
     * @return mixed
     */
    public function snsApiBase($openId, $lang = "zh_CN")
    {
        $url = "https://api.weixin.qq.com/cgi-bin/user/info";
        $params = [
            "access_token" => $this->obtain(),
            "openid" => $openId,
            "lang" => $lang
        ];

        $http = new HttpRequest();
        return json_decode($http->send($url, $params), true);
    }
}