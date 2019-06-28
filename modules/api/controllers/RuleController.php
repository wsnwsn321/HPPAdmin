<?php
/**
 * Created by PhpStorm.
 * User: CaryYe
 * Date: 10/07/2017
 * Time: 9:48 AM
 */
namespace app\modules\api\controllers;

class RuleController extends CActiveController
{
    public $modelClass = "app\models\Rules";

    /**
     * @return array
     */
    public function actionCreate()
    {
        $m = new $this->modelClass(["scenario" => $this->modelClass::SCENARIO_CREATE]);
        $data = [];

        $contest_id = (int) \yii::$app->getRequest()->post("contest_id", 0);
        $post = \yii::$app->getRequest()->post();

        if (isset($post["credit"])
            && (trim($post["credit"]["params"]["low"]) != ''
                || trim($post["credit"]["params"]["high"]) != '' ))
        {
            $tmp =  [
                "render" => "Range",
                "name" => "credit",
                "description" => "报名选手的积分必须在%d-%d之间",
                "expression" => '$credit>=$low && $credit<=$high',
                "type" => "enrollment",
                "apply_to" => "PingpongContest",
                "params" => [
                    'low' => null,
                    'high' => null
                ]
            ];
            $tmp["params"] = $post["credit"]["params"];
            array_push($data, $tmp);
        }

        if (isset($post["minCreditTimes"])
            && trim($post["minCreditTimes"]["params"]["low"]) != '')
        {
            $tmp =  [
                "render" => "Text",
                "name" => "minCreditTimes",
                "description" => "至少需要参加过积分赛%d次",
                "expression" => '$creditTimes-$low>=0',
                "type" => "enrollment",
                "apply_to" => "PingpongContest",
                "params" => [
                    "low" => null
                ]
            ];
            $tmp["params"] = $post["minCreditTimes"]["params"];
            array_push($data, $tmp);
        }

        foreach ($data as $key => $val) {
            if (true/*isset($val["checked"])
                && trim($val["checked"]) === "on"*/)
            {
                $m->isNewRecord = true;

                $val["contest_id"] = $contest_id;
                $m->setAttributes($val);

                if ($m->save())
                    $m->id = 0;
                else
                    return array(
                        $this->retMsg["sf"],
                        ["data" => $m->getErrors()]
                    );
            }
        }

        return $this->retMsg["success"];
    }
}