<?php
/** User: CaryYe , 22/03/2018 5:46 PM */
namespace app\models\wx;
use app\components\HttpRequest;

/**
 * Class menu
 * @package app\models
 * Describe: Create menu.
 */
class Menu extends Base
{
    /** Submit url */
    public $URL = "https://api.weixin.qq.com/cgi-bin/menu/create?access_token=";
    /** Method */
    CONST METHOD = "POST";
    /** Pending paraments  */
    public $args = '';

    public $pubArg = [];

    /** @inheritdoc */
    public function init()
    {
        parent::init();
        $this->URL = $this->URL .= $this->obtain();
        //$this->args = '{"button":[{"type":"view","name":"观看比赛","url":"http://wp.happypingpang.com/"},{"type":"view","name":"加入比赛","url":"http://wp.happypingpang.com/"}]}';

        $weChat = \yii::createObject("weChat")->obj();
        $host = \yii::$app->params["WapHost"];
        $url = $host."/match/?";
        $loginUrl = $url.http_build_query(array_merge([
            "php7Login_WeChat" => $weChat->wechatId,
            "php7Login_base" => 1
        ], $this->pubArg));

        $this->args = '{"button":[{"type":"view","name":"比赛列表","url":"'.$loginUrl.'"}]}';
    }

    /**
     * @param string $body (Json string)
     * @return boolean
     */
    public function create($body = '')
    {
        $body !== '' && $this->args = $body;

        $http = new HttpRequest();
        $h = $http->send($this->URL, $this->args, self::METHOD);
        $r = json_decode($h, true);

        if (isset($r["errcode"])
            && (int)$r["errcode"] === 0
        ) {
            return true;
        }

        return false;
    }
}