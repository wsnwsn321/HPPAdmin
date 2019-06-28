<?php
/** CaryYe 2019/6/20 10:32 AM */
namespace app\modules\api\controllers\behaviors;

use app\models\Opponents;
use app\models\Contests;
use app\models\OpponentStages;

class  SinglyEnrollBehavior extends \yii\base\Behavior
{
    private $modelClass;
    private $retMsg;

    /** @return array */
    public function enroll($enrollArray, &$transaction)
    {
        $this->modelClass = $this->owner->modelClass;
        $this->retMsg = $this->owner->retMsg;

        $scenario = $this->modelClass::SCENARIO_CREATE;
        $m = new $this->modelClass(["scenario" => $scenario]);
        $m->setAttributes($enrollArray);
        if ($m->save()) {
            // Generate a proportional opponent
            $o = new Opponents(["scenario" => $scenario]);
            $o->setProperties(array_merge(
                $m->getAttributes(),
                ["username" => $m->user->username]
            ));
            if ($o->save()) {
                // Create an opponentStage.
                $os = new OpponentStages(["scenario" => $scenario]);
                $os->setAttributes([
                    "opponent_id" => $o->id,
                    "stage" => $o->contest->stages[0]->serial
                ]);

                if (! $os->save()) {
                    $transaction->rollBack();
                    return array_merge(
                        $this->retMsg["sf"],
                        ["data" => $os->getErrors()]
                    );
                }
            } else {

                $transaction->rollBack();
                return array_merge(
                    $this->retMsg["sf"],
                    ["data" => $o->getErrors()]
                );
            }

            // Update enroll number of contest.
            $contest = Contests::findOne(["id" => $m->contest->id]);
            $contest->setAttributes([
                "enroll_count" => ++$m->contest->enroll_count,
                "last_enroll_created" => date("Y-m-d H:i:s", time())
            ]);

            if (! $contest->save()) {
                $transaction->rollBack();
                return array_merge(
                    $this->retMsg["sf"],
                    ["data" => $contest->getErrors()]
                );
            }

            return array_merge(
                $this->retMsg["success"],
                ["data" => ["id" => $m->id]]
            );

        } else {

            // Something failed. Execute the operation rollBack.
            $transaction->rollBack();
            return array_merge(
                $this->retMsg["sf"],
                ["data" => $m->getErrors()]
            );
        }
    }
}