<?php
/* CaryYe 2018/7/26 8:54 AM */
namespace app\controllers\ActiveWeApis;

/**
 * Class InformAction
 * @package app\controllers\ActiveWeApis
 */
class InformAction extends \yii\base\Action
{
    /**
     * Runs the Action.
     * @return string
     */
    public function run()
    {
        $cid = (int) \yii::$app->getRequest()->get("cid", 0);
        $c = \app\models\Contests::findOne(["id" => $cid]);
        if (is_null($c)) {
            return json_encode(["code" => 0, "message" => "比赛不存在!"]);
        }

        $m = new \app\models\wx\Template();
        $opponents = \app\models\Opponents::findAll(["contest_id" => $cid]);
        foreach($opponents as $k => $o) {
            $m->send($o->user->username, $c);
        }

        return  json_encode(["code" => 1, "message" => "success!"]);
    }
}