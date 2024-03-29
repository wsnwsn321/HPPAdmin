<?php
/** CaryYe 2019/7/3 2:05 PM */
namespace app\modules\admin\controllers;

class ScheduleController extends LoginController
{
    private $url1 = "/admin/schedule/index";
    private $url2 = "/admin/schedule/groups";
    private $url3 = "/admin/schedule/setting";
    private $contest;

    /** @inheritdoc */
    public function init()
    {
        parent::init(); // TODO: Change the autogenerated stub
        $id = (int) \yii::$app->getRequest()->get("id", 0);
        $returnURL = \yii::$app->getRequest()->get("returnURL", "");
        $this->url1 .= "?id=$id&returnURL=$returnURL";
        $this->url2 .= "?id=$id&returnURL=$returnURL";
        $this->url3 .= "?id=$id&returnURL=$returnURL";
    }

    /** @return void */
    private function getContest()
    {
        // Set up the public contest
        $id = (int) \yii::$app->getRequest()->get("id", 0);
        is_null($this->contest = \app\models\Contests::findOne(["id" => $id]))
            && parent::error(400, "Contest( id : $id ) couldn't be found!");
        $this->contest->scenario = \app\models\Contests::SCENARIO_PUBLISHED;
        if (!$this->contest->validate()) {
            $errors = "";
            foreach ($this->contest->getErrors() as $k => $e) {
                $errors .= $k. ' : ' . implode(' / ', $e) . "\r\n";
            }
            parent::error(400, $errors);
        }
        $this->contest->scenario = \app\models\Contests::SCENARIO_DEFAULT;
    }

    /** @return string */
    public function actionIndex()
    {
        $this->getContest();
        return $this->render("index", ['c' => $this->contest, "url1" => $this->url1, "url2" => $this->url2, "url3" => $this->url3]);
    }

    /** @return string */
    public function actionGroups()
    {
        $this->getContest();
        $groupedOpponents = [];

        foreach($this->contest->opponents as $k => $o) {
            foreach($o->stages as $_k => $s)
                ((int)$s->stage === 0) && $stage = $s;

            ! isset($groupedOpponents[$stage->group])
                && $groupedOpponents[$stage->group] = [];
            $groupedOpponents[$stage->group][$stage->serial] = $stage;
            ksort($groupedOpponents[$stage->group]);
        }
        ksort($groupedOpponents);

        return $this->render("groups", [
            'c' => $this->contest,
            "url1" => $this->url1,
            "url2" => $this->url2,
            "url3" => $this->url3,
            "groupedOpponents" => $groupedOpponents
        ]);
    }

    /** @return string */
    public function actionExchange()
    {
        $id1 = (int) \yii::$app->getRequest()->post("id1", 0);
        $id2 = (int) \yii::$app->getRequest()->post("id2", 0);
        if ($id1 === $id2) {
            return json_encode(["code" => -999, "message" => "Two id to exchange is equal!"]);
        }

        $os1 = \app\models\OpponentStages::findOne(["id" => $id1]);
        $os2 = \app\models\OpponentStages::findOne(["id" => $id2]);
        if (is_null($os1) || is_null($os2)) {
            return json_encode(["code" => -998, "message" => "An Id from post is invalid!"]);
        }

        $swap = $os1->getAttributes();
        $os1->setAttributes([
            "group" => $os2->getAttribute("group"),
            "serial" => $os2->getAttribute("serial"),
            "grade" => $os2->getAttribute("grade")
        ]);
        $os2->setAttributes([
            "group" => $swap["group"],
            "serial" => $swap["serial"],
            "grade" => $swap["grade"]
        ]);
        $os1->save();
        $os2->save();

        return json_encode(["code" => 10001, "message" => "succeed!"]);
    }

    /** @return string */
    public function actionSetting()
    {
        $this->getContest();
        return $this->render("setting", ['c' => $this->contest, "url1" => $this->url1, "url2" => $this->url2, "url3" => $this->url3]);
    }
}