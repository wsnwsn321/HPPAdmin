<?php
/** CaryYe @ 2018/7/19 3:02 PM */
namespace app\controllers;

use yii\rest\Controller;
use app\components\Wx\Js;

class JssdkController extends Controller
{
    /**
     * Displays homepage.
     * @return array
     */
    public function actionIndex()
    {
        $cid = (int) \yii::$app->request->get("cid", 0);
        $APIs = explode(',', trim(\yii::$app->request->get("apis", '')));
        $url = urldecode(trim(\yii::$app->request->get("url", "")));

        // Get contest by query-param "cid".
        $contest = \app\models\Contests::findOne(["id" => $cid]);

        if (is_null($contest)) {
            return [
                "code" => -1,
                "message" => "Contest does not existence."
            ];
        }

        if ((int)$contest->game->wechatId === 0) {
            return [
                "code" => -1,
                "message" => "Match wasn't a weChat-match."
            ];
        }

        // Get weChat account.
        $weChat = \app\models\Wechats::findOne((int)$contest->game->wechatId);
        if (is_null($weChat)) {
            return [
                "code" => -1,
                "message" => "WeChat does not existence."
            ];
        }

        $appId = $weChat->appID;
        $secret = $weChat->appsecret;
        $js =  new Js($appId, $secret);
        $config = $js->config($APIs, false, true, false, $url);

        //if (isset($config["beta"])) unset($config["beta"]);
        //if (isset($config["debug"])) unset($config["debug"]);

        return $config;
    }

    /** @return array */
    public function actionGeneral()
    {
        $wid = (int) \yii::$app->request->get("wid", 0);
        $APIs = explode(',', trim(\yii::$app->request->get("apis", '')));
        $url = urldecode(trim(\yii::$app->request->get("url", "")));

        // Get weChat account.
        $weChat = \app\models\Wechats::findOne($wid);
        if (is_null($weChat)) {
            return [
                "code" => -1,
                "message" => "WeChat does not existence."
            ];
        }

        $appId = $weChat->appID;
        $secret = $weChat->appsecret;
        $js =  new Js($appId, $secret);
        $config = $js->config($APIs, false, true, false, $url);

        return $config;
    }
}
