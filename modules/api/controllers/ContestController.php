<?php
/** CaryYe 06/07/2017 3:22 PM */
namespace app\modules\api\controllers;

use app\components\Events\AfterContestCreateEvent;
use app\components\Behaviors\AfterContestCreateBehavior;

class ContestController extends CActiveController
{
    const AFTER_CREATE = "contest_after_create";
    public $modelClass = "app\models\Contests";

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        $behaviors = parent::behaviors();

        $behaviors["afterCreate"] = [
            "class" => AfterContestCreateBehavior::className()
        ];

        return $behaviors;
    }

    /**
     * Desc: Create contest.
     * @return array
     */
    function actionCreate()
    {
        $m = new $this->modelClass(["scenario" => $this->modelClass::SCENARIO_CREATE]);
        $m->setProperties(\yii::$app->getRequest()->post());

        if (!in_array(
            \yii::$app->request->post("scheme"),
            ["single_round_robin", "single_knock_out"]
        ))
        {
            return array_merge(
                $this->retMsg["vf"],
                ["data" => ["scheme" => ["Scheme of contest illegal."]]]
            );
        }

        if ($m->validate()) {

            if ($m->save()) {
                // When saved succeed, first, trigger the event to insert associated data for tables(`rules`,`stages`), and then update status of `games`
                $event = new AfterContestCreateEvent([
                    "rule" => ["contest_id" => $m->id],
                    "stage" => [
                        "is_group" => 0,
                        "scheme" => \yii::$app->getRequest()->post("scheme"),
                        "contest_id" => $m->id
                    ],
                    "game" => ["status" => "published", "publish_date" => date("Y-m-d H:i:s", time()), "game_id" => $m->game_id]
                ]);
                $this->trigger(self::AFTER_CREATE, $event);

                return array_merge(
                    $this->retMsg["success"],
                    ["data" => ["id" => $m->id]]
                );
            }

            return array_merge(
                $this->retMsg["sf"],
                ["data" => $m->getErrors()]
            );
        }

        return array_merge($this->retMsg["vf"], ["data" => $m->getErrors()]);
    }

    /**
     * When enroll succeed, the enroll event will visit this as event(callback).
     *
     * @return array
     */
    public function actionUpdate($id)
    {
        $contest = $this->modelClass::findOne(["id" => (int) $id]);
        if (is_null($contest)) return $this->retMsg["id_not_found"];

        $contest->setAttributes(\yii::$app->getRequest()->post());
        return $contest->save()
            ? $this->retMsg["success"]
            : array_merge($this->retMsg["sf"], ["data" => $contest->getErrors()]);
    }
}
