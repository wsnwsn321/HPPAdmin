<?php
/* CaryYe , 09/08/2017 9:55 AM */
namespace app\components\Behaviors\arrange;

use app\components\arrange\Engines\ContestEngine;
use app\models\Contests;
use app\models\Enrollments;
use app\models\Opponents;
use app\models\OpponentStages;

class  checkEnrollDataIntegrityBehavior extends \yii\base\Behavior
{
    /**
     * Delete illegal data , using to verify data before schedule .
     * If enrollment data illegal , then delete it .
     * Ensure against table can generate correctly.
     *
     * @param $contest
     * @return boolean
     */
    public function checkEnrollDataIntegrity($contest)
    {
        $enrollments = Enrollments::find()
            ->where([
                "contest_id" => $contest->id,
                "status" => Enrollments::$STATUS_APPROVE
            ])->andWhere([
               "!=", "dtype", "single_team"
            ])->all();

        $mode = $contest->mode;

        $type = $contest->mode == Contests::$MODE_SINGLE
            ? Opponents::$DTYPE_PLAYER
            : $contest->mode;

        $opponents = Opponents::find()
            ->where([
                "contest_id" => $contest->id,
                "dtype" => $type,
                "is_single_team" => null
            ])->all();

        // Data compares
        if (count($enrollments) != count($opponents)) {
            $enrollUsers = $opponentUsers = [];
            if ($type == Opponents::$DTYPE_TEAM) {
                //团体赛 可能出现一个人报多个球队的情况
                foreach ($enrollments as $enroll) {
                    $enrollUsers[$enroll["colony_id"]] = $enroll["id"];
                }
                foreach ($opponents as $opp) {
                    $opponentUsers[$opp["colony_id"]] = $opp["id"];
                }
            } else {
                foreach ($enrollments as $enroll) {
                    $enrollUsers[$enroll["user_id"]] = $enroll["id"];
                }
                foreach ($opponents as $opp) {
                    $opponentUsers[$opp["user_id"]] = $opp["id"];
                }
            }

            $diffEnrollIds = array_diff_key($enrollUsers, $opponentUsers);
            $diffOppIds = array_diff_key($opponentUsers, $enrollUsers);
            $total = count($diffEnrollIds) + count($diffOppIds);

            if (count($diffEnrollIds)) {
                Enrollments::removeErrorEnroll($contest, $diffEnrollIds);
            }

            if (count($diffOppIds)) {
                if ($type != Opponents::$DTYPE_PLAYER) {
                    foreach ($diffOppIds as $id)
                        Opponents::deleteAll([
                            "contest_id" => $contest->id,
                            "parent_id" => $id
                        ]);
                }

                foreach ($diffOppIds as $id) {
                    OpponentStages::deleteAll(["opponent_id" => $id]);
                    Opponents::deleteAll([
                        "contest_id" => $contest->id,
                        "id" => $id
                    ]);
                }
            }

            $contest->enroll_count -= $total;
            $contest->save();
        } else {
            if ($contest->enroll_count != count($enrollments)) {
                $contest->setAttribute("enroll_count", count($enrollments));
                $contest->save();
            }
        }

        ContestEngine::refresh();
        return true;
    }
}