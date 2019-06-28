<?php
/**
 * CaryYe , 31/07/2017 9:30 AM
 */
namespace app\components\arrange\Opponent;
use app\components\arrange\Engines\ContestEngine;
use app\components\ScheduleAlgorithm;
use app\models\Opponents;
use app\models\ScheduleGroups;
use app\components\Set;

class AbstractContextAwareOpponent extends Opponents
{
    /**
     * Allocate group belong to, and serial of the group
     * @param \app\models\Contests $contest
     * @param array $params
     * @return void
     */
    public function allocate($contest, $params)
    {
        $opponents = $this->getAllOpponents($contest, $contest->stages[0]);

        if ($contest->stages[0]->scheme == "single_knock_out") {
            $params["random"] = true;
        } else {
            $params["random"] = false;
        }

        $groupedOpponents = $this->group($opponents, $params, $contest);
        $this->position($contest, $contest->stages[0], $groupedOpponents);
    }

    /**
     * Grouping opponents
     * @param $opponents
     * @param $params
     * @param $contest
     * @return array
     */
    public function group($opponents, $params, $contest)
    {
        $amount = (int) $params["amount"];
        $size = (int) $params["size"];
        $total = count($opponents);
        $seedOpponents = [];
        $expectedSeedCount = ScheduleAlgorithm::numSeed($size) * $amount;
        $seedMethod = empty($params["seedMethod"]) ? null : $params["seedMethod"];

        switch ($seedMethod) {
            case ScheduleGroups::$SEED_SYSTEM:
                // Waiting for fill
                break;

            case ScheduleGroups::$SEED_MANUAL:
                // Waiting for fill
                break;

            case ScheduleGroups::$SEED_NONE:
            default:
                $seedCount = 0;
                $seeds = [];
                $isSetSeed = false;
                break;
        }

        $otherOpponents = array_udiff(
            $opponents,
            $seedOpponents,
            function($a, $b) {return ($a->id - $b->id);}
        );

        foreach($otherOpponents as $op) {
            $stages = $op->stages;
            $stages[0]->is_seed = 0;
            //$op->stages[0]->is_seed = 0;
        }

        if ($params["random"]) {
            shuffle($seedOpponents);
            shuffle($otherOpponents);
        }

        $all = array_merge($seedOpponents, $otherOpponents);
        $result = ScheduleAlgorithm::snakeAllocate($all, $amount);

        // Set group of each opponent in stage 0 (Opponent_stages.group)
        foreach ($result as $index => $ops) {
            foreach ($ops as $op) {
                $stages = $op->stages;
                $stages[0]->group = 1 + $index;
                //$op->stages[0]->group = 1 + $index;
            }
        }

        return $result;
    }

    /**
     * Set serial of opponents in each group
     * @param $contest
     * @param $stage
     * @param null $groupedOpponents
     * @return void
     */
    public function position($contest, $stage, $groupedOpponents = null)
    {
        $model = ContestEngine::loader("Opponent", $stage->scheme);

        foreach ($groupedOpponents as $group => $oppos)
            $model->position($oppos, $stage);

        $this->batchSaveOpponent(Set::flatten($groupedOpponents), $stage->serial);
    }

    /**
     * @param $opponents
     * @param int $stageSerial
     * @return void
     */
    public function batchSaveOpponent($opponents, $stageSerial = 0)
    {
        foreach($opponents as $k => $op) {
            $op->save();
            foreach ($op->stages as $stage) $stage->save();
        }
    }
}