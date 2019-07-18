<?php
/** CaryYe 2019/6/25 12:31 PM */
namespace app\modules\admin\controllers;

use app\models\Contests;
use app\modules\admin\models\profiles;

/**
 * @Desc This class is used for managing enrollments of contests.
 * Class CustomController
 * @package app\modules\admin\controllers
 */
class EnManagerController extends LoginController
{

    /**
     * @Desc List of enrollments of a contest.
     * @return string
     */
    public function actionEnrollments()
    {
        $id = (int) \yii::$app->request->get("id", 0);

        is_null($c = Contests::findOne(["id" => $id]))
            && parent::error(400, "Contest( id : $id ) couldn't be found!");

        $this->checkTheStatusOfTheContest($c);

        $name = "";
        if (\yii::$app->getRequest()->isPost) {
            $name = trim(\yii::$app->getRequest()->post("table_search"));
        }

        return $this->render("enrollments", ["c" => $c, "name" => $name]);
    }

    /** @return void */
    private function checkTheStatusOfTheContest(& $c)
    {
        $c->status !== "open"
            && parent::error(401, "当前比赛的状态不能被编辑!");

        $startData = strtotime($c->enroll_start_date.' 00:00:00');
        $endData =  strtotime($c->enroll_end_date.' 23:59:59');
        (!(time() >= $startData && time() <= $endData))
            && parent::error(402, "报名超过截至日期.");
    }
}