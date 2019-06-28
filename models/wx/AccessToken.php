<?php
/** User: CaryYe , 22/03/2018 5:53 PM */
namespace app\models\wx;
use app\components\HttpRequest;
use yii\base\Model;

/**
 * Class AccessToken
 * @package app\models
 * Obtain and refresh access_token.
 */
class AccessToken extends Model
{
    /** RestApi of WeChat */
    CONST URL = "https://api.weixin.qq.com/cgi-bin/token";

    /** Pending parament  */
    public $args = [
        "grant_type" => "client_credential",
        "appid" => "",
        "secret" => ""
    ];

    /** Method */
    CONST METHOD = "GET";

    /** Refresh time interval */
    CONST RefreshInterval = 60;

    /** @var string */
    public $accessToken = "";

    /** @var integer */
    public $lastInteractTime = 0;

    /** @inheritdoc */
    public function init()
    {
        $weChat = \yii::createObject("weChat")->obj();
        $this->accessToken = $weChat->accessToken;
        $this->lastInteractTime = (int) strtotime($weChat->lastInteractTime);
        $this->args["appid"] = $weChat->appID;
        $this->args["secret"] = $weChat->appsecret;
        $this->update();
        parent::init();
    }

    /** @return string */
    public function obtain()
    {
        return $this->accessToken;
    }

    /** @return boolean */
    private function update()
    {
        $time = time();

        // First time to get accessToken.
        if (trim($this->accessToken) === ''
            || $this->lastInteractTime === 0)
        {
            return $this->synchronization($time);
        }

        // Do update.
        if ($time - self::RefreshInterval >= $this->lastInteractTime) {
            return $this->synchronization($time);
        }

        return true;
    }

    /**
     * @param $time
     * @return boolean
     */
    private function synchronization($time)
    {
        $http = new HttpRequest();
        $h = $http->send(self::URL, $this->args, self::METHOD);
        $r = json_decode($h, true);
        unset($http);

        if (isset($r["access_token"])) {
            $this->accessToken = $r["access_token"];
            $this->lastInteractTime = $time;
            $weChat = \yii::createObject("weChat");
            $weChat->obj()->setAttributes([
                "accessToken" => $this->accessToken,
                "lastInteractTime" => date("Y-m-d H:i:s", $this->lastInteractTime)
            ]);
            $obj = $weChat->obj();
            $obj->save();
            $weChat->setObj($obj);
            return true;
        }
        return false;
    }
}