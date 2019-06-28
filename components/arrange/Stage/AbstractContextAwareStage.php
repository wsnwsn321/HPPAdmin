<?php
/* CaryYe , 27/07/2017 1:36 PM */
namespace app\components\arrange\Stage;

use app\components\arrange\Engines\ContestEngine;
use app\components\ScheduleAlgorithm;
use app\models\Groups;
use app\models\Opponents;
use app\models\Schemes;
use app\models\Stages;

class AbstractContextAwareStage extends Stages
{
    /**
     * The method using as router to call partitionStage{X}
     *
     * @return array|null
     */
    public function partition($contest, $stage, $params)
    {
        return $this->{"partitionStage{$stage->serial}"}($contest, $stage, $params);
    }

    /**
     * Partition groups of first stage, and assign property total
     *
     * @param $contest
     * @param $stage
     * @param $params
     * @return array|null
     */
    protected function partitionStage0 ($contest, $stage, $params)
    {
        // Number of groups
        $model = new Groups();
        $amount = (int) $params['amount'];
        // Number of contestant
        $total = (int) $params['total'];
        $group_arr = array();

        for ($i = 0; $i < $amount; $i++) {
            $group = [];
            $group["serial"] = $i+1;
            $group["stage_id"] = $stage->id;

            if ($total % $amount == 0) {
                $group["total"] = $total / $amount;
            } else {
                /** Cary Algorithm interpretation
                 * 商为奇数时 , 后面余数组的人数为商+1 , 前面组人数就是商。
                 * 商为偶数时 , 前面余数组的人数为商+1 , 后面组人数都为商。
                 */
                $group["total"] = $size = ceil($total / $amount);

                // $size & 1 : Odd number / Even number judgement
                if ((($size & 1) && $i >= $total % $amount)
                    || ! ($size & 1) && $i < ($amount - $total % $amount))
                {
                    $group["total"] = $group["total"] -1;
                }
            }
            array_push($group_arr, $group);

            $model->isNewRecord = true;
            $model->setAttributes($group);
            $model->save() && $model->id = null;
        }

        return $group_arr;
        //$this->Group->saveMany($group_arr,array('validate' => 'false','callbacks' => false));
    }

    /**
     * Partition group for second stage
     * @param $contest,
     * @param $stage
     * @param int $total
     * @param array $params
     */
    protected function partitionStage1($contest, $stage, $params) {
        $groups = [];
        //Refind prev stage data
        $groupModel = new Groups();
        $PreStage = $contest->stages[$stage->serial-1];
        $numLastStageGroups = count($PreStage->groups);
        if ($stage->scheme == Schemes::$SINGLE_ROUND_ROBIN) {
            for ($i = 1; $i <= $params['group_promotion_count']; $i++) {
                $data = [
                    "serial" => $i,
                    "stage_id" => $stage->id,
                    "total" => $numLastStageGroups,
                    "size" => $numLastStageGroups
                ];
                $groups[] = $data;
            }
        } else {
            // assert ($stage->isGroup == false);
            $total = $params['group_promotion_count'] * $numLastStageGroups;
            $data = [
                "serial" => 1,
                "stage_id" => $stage->id,
                "total" => $total,
                "size" => ScheduleAlgorithm::determineAppropriateKnockoutTotal($total),
                "rank" => $params['ranking_scope']
            ];
            $groups[] = $data;
        }

        // Save groups
        foreach ($groups as $group) {
            $groupModel->isNewRecord = true;
            $groupModel->setAttributes($group);
            $groupModel->save() && $groupModel->id = null;
        }
    }

    /**
     * @param $contest
     * @param $stage
     * @param $factors
     * @return array
     */
    public function resolveSchedulableGroups($contest, $stage, $factors)
    {
        $ret = [];
        $groupModel = ContestEngine::loader("Group", $stage->scheme);
        $groups = $stage->groups;
        $opponentModel  = new Opponents();
        $opponents = $opponentModel->getGroupedOpponents($contest, $stage);

        foreach ($groups as $group) {
            $playGroup = $groupModel->initializePlayGroup(
                $contest,
                $group,
                $factors
            );

            if (!empty($opponents))
                $playGroup->setOpponents($opponents[$group->serial]);

            $ret[] = $playGroup;
        }

        //$stage->promotion_count = count($ret)*$factors->group_promotion_count;
        $stage->save();

        return $ret;
    }
}