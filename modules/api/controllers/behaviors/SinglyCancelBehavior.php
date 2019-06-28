<?php
/** CaryYe 2019/6/20 1:22 PM */
namespace app\modules\api\controllers\behaviors;

use app\models\Enrollments;
use app\models\Opponents;
use app\models\Contests;
use app\models\OpponentStages;
use \Exception;

class  SinglyCancelBehavior extends \yii\base\Behavior
{
    private $modelClass;
    private $retMsg;

    /** @return array */
    public function cancel($id)
    {
        $this->modelClass = $this->owner->modelClass;
        $this->retMsg = $this->owner->retMsg;

        $db = \yii::$app->db;
        $transaction = $db->beginTransaction();

        $scenario = "delete";
        $enrollment = Enrollments::findOne(["id" => $id]);

        if (!is_null($enrollment)) {
            $contestId = (int) $enrollment->contest_id;
            $userId = (int) $enrollment->user_id;
            $opponents = (new Opponents())->find()->where(
                ["contest_id" => $contestId, "user_id" => $userId]
            )->all();


            if (is_array($opponents) && !empty($opponents)) {

                // Logic of deleting the enrolled user.
                try {
                    $contest = Contests::findOne(["id" => $contestId]);
                    if (is_null($contest)) {
                        throw new \Exception("Contest was not found!", 500);
                    }
                    foreach ($opponents as $k => $opponent) {
                        $stages = $opponent->stages;
                        foreach ($stages as $_k => $stage) {
                            $stage->delete();
                        }
                        $opponent->delete();
                    }
                    // after deleting stages and opponents, delete enrollment.
                    $enrollment->delete();
                    // After deleting all related data.
                    $contest->enroll_count--;
                    if (! $contest->save()) {
                        throw new \Exception("Saving contest failed, please try again.");
                    }

                } catch (\Exception $e) {
                    $transaction->rollBack();
                    return ["code" => -100005, "message" => $e->getMessage()];
                }

                $transaction->commit();
                return $this->retMsg["success"];
            }
        }

        return ["code" => -100006, "message" => "enrollment was not existed"];
    }
}