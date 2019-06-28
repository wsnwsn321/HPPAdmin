<?php
/* CaryYe , 01/08/2017 7:36 AM */
namespace app\components\arrange\Opponent;

class AbstractSchemeAwareOpponent extends \app\models\Opponents
{
    /**
     * Position players in each group in the stage
     * @param $opponents
     * @param $stage
     * @param null $sort
     * @return boolean
     */
    public function position($opponents, $stage, $sort = null)
    {
        $effectiveOpponents = [];

        // If position players for the first stage, no need to verify player is give up
        if ((int) $stage->serial === 0) {
            $effectiveOpponents = $opponents;
        } else {
            foreach ($opponents as $key => & $op) {
                if ($op->stages[$stage->serial]->is_outlet
                   || (int) $op->stages[$stage->serial]->is_giveup === 0)
                {
                    $effectiveOpponents[] = $op;
                } else {
                    $op->stages[$stage->serial]->serial = null;
                }
            }
        }

        is_null($sort)
            && $sort = $this->compareWithIsSeedAndCredit($stage->serial);
        usort($effectiveOpponents, $sort);

        $seeds = $normals = [];
        foreach ($effectiveOpponents as $op) {
            $op->stages[$stage->serial]->is_seed
                ? $seeds[] = $op
                : $normals[] = $op;
        }
        $count = count($effectiveOpponents);
        $seedCount = count($seeds);

        list ($seedPositions, $normalPositions)
            = $this->resolvePositions($count, $seedCount);

        // Number of seed player more than expected
        if ($seedCount > count($seedPositions)) {
            foreach ($seeds as $key => $seed) {
                unset($seeds[$key]);
                $normals[] = $seed;
            }
        }

        (count($seedPositions) && count($seeds))
            && $seeds = array_combine($seedPositions, $seeds);

        if (count($normals) > 0) {
            $normals = array_combine($normalPositions, $normals);
            $all = $seeds + $normals;
        } else {
            $all = $seeds;
        }

        // Set position in group (Opponent_stages.serial)
        foreach ($all as $pos => $op) {
            $stages = $op->stages;
            $stages[$stage->serial]->serial = $pos;
        }

        return true;
    }

    /**
     * @param $stage
     * @return \Closure
     */
    protected function compareWithIsSeedAndCredit($stage) {
        return function ($a, $b) use ($stage) {
            $ret = 0;

            if ($a->stages[$stage]->is_giveup != $b->stages[$stage]->is_giveup) {
                return ($a->stages[$stage]->is_giveup ? 1 : -1);
            }

            if ($a->stages[$stage]->is_seed != $b->stages[$stage]->is_seed) {
                return ($a->stages[$stage]->is_seed == true) ? -1 : 1;
            }

            if ($a->credit != '' || $b->credit != '') {
                if ($a->credit == '') {
                    return 1;
                } else if ($b->credit == '') {
                    return -1;
                } else {
                    return -($a->credit - $b->credit);
                }
            }

            if (!empty($a->enroll_ranking) && !empty($b->enroll_ranking)) {
                $ret = -($a->enroll_ranking - $b->enroll_ranking);
            } else {
                if (empty($a->enroll_ranking)) {
                    $ret = -1;
                } else if (empty($b->enroll_ranking)) {
                    $ret = 1;
                } else {
                    $ret = 0;
                }
            }
            return -$ret;
        };
    }
}