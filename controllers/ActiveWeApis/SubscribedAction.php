<?php
/** CaryYe 2018/8/16 4:35 PM */
namespace app\controllers\ActiveWeApis;
use app\models\wx\User;

class SubscribedAction extends \yii\base\Action
{
    /** @return string */
    public function run()
    {
        $uid = (int) trim(\yii::$app->request->get("uid", ""));
        $user = \app\models\User::findOne(["id" => $uid]);

        if (is_null($user)) {
            return json_encode(["code" => 0, "message" => "failed"]);
        }

        $wxUser = new User();
        $info = $wxUser->snsApiBase($user->username);
        return json_encode(["code" => 1, "data"=> $info]);
    }
}