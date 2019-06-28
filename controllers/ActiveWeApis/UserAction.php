<?php
/* CaryYe 2018/4/16 9:32 AM */
namespace app\controllers\ActiveWeApis;
use app\models\WechatUsers;
use app\models\wx\User;

/**
 * Class UserAction
 * @package app\controllers\ActiveWeApis
 */
class UserAction extends \yii\base\Action
{
    /**
     * Runs the Action.
     * @return string
     */
    public function run()
    {
        $user = new User();
        $code = trim(\yii::$app->request->get("code", ""));
        $type = trim(\yii::$app->request->get("type", "snsapi_userinfo"));
        $backUrl = rawurlencode(rawurldecode(trim(\yii::$app->request->get("backUrl"))));
        $firmId = (int) trim(\yii::$app->request->get("firm"));

        if ($firmId !== 0
            && is_null(\app\models\Firms::findOne(["id" => $firmId])))
        {
            return json_encode(["code" => 0, "message" => "尝试登陆不存在的公司!"]);
        }

        if ($code === "") {
            header("Location: ".$user->getCodeUrl($type, $backUrl, $firmId));
        }

        $d = $user->exchangeAccessToken($code);
        if (isset($d["errcode"])) {
            return json_encode([
                "code" => 0,
                "message" => "Invalid code"
            ]);
        }

        switch ($type) {
            case "snsapi_base":
                $info = $user->snsApiBase($d["openid"]);
            break;

            case "snsapi_userinfo":
            default:
                $info = $user->snsApiUserInfo($d["access_token"], $d["openid"]);
            break;
        }

        $r = $this->saveInfo($info);
        if ($r['r']) {
            $m = $r['m'];
            if (! is_null($m->user)) {
                $args = http_build_query([
                    "username" => $m->user->username,
                    "password" => $m->user->password,
                    "backUrl" => $backUrl,
                    "firm" => $firmId
                ]);

                if ($firmId !== 0) {
                    $this->bindUserFirm($m->user->id, $firmId);
                }

                $location = \yii::$app->params["afterLoginRedirect"].'?'.$args;
                header("Location: $location");
            }
        }

        return json_encode(["code" => 0, "message" => "failed"]);
    }


    /**
     * @param $info
     * @return array
     */
    public function saveInfo($info)
    {
        $weChat = \yii::createObject("weChat")->obj();
        $info["wechatId"] = $weChat->id;

        $m = WechatUsers::findOne([
            "openid" => $info["openid"],
            "wechatId" => $info["wechatId"]
        ]);

        if (is_null($m)) {
            $m = new WechatUsers(["scenario" => WechatUsers::SCENARIO_CREATE]);
        }
        $m->setProperties($info);
        $r = $m->save($info);
        return ['r' => $r, 'm' => $m];
    }

    /** @return void */
    public function bindUserFirm($userId, $firmId)
    {
        $belong = \app\models\FirmUsers::findOne([
            "userId" => $userId,
            "firmId" => $firmId
        ]);
        if (is_null($belong)) {
            $m = new \app\models\FirmUsers();
            $u = \app\models\User::findOne(["id" => $userId]);
            $p = \app\models\Profiles::findOne(["user_id" => $userId]);
            $m->setAttributes([
                "userId" => $userId,
                "firmId" => $firmId,
                "username" => $u->username,
                "mobile" => $p->mobile,
                "nickname" => $p->nickname,
                "fullname" => $p->fullname
            ]);
            $m->save();
        }
    }
}