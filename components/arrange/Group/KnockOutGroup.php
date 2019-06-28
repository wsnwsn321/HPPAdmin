<?php
/* CaryYe , 08/08/2017 8:52 AM */
namespace app\components\arrange\Group;

use app\components\ScheduleAlgorithm;
use app\models\Contests;
use app\models\Schemes;

class KnockOutGroup extends AbstractSchemeAwareGroup
{
    /**
     * @param $contest
     * @param $group
     * @param $factors
     * @return KnockoutPlayGroup
     */
    public function initializePlayGroup($contest, $group, $factors) {
        $group->mode = $contest->mode;

        if ($contest->mode == Contests::$MODE_TEAM) {
            $group->team_scheme = $factors->team_match_scheme;
        }

        $group->size = ScheduleAlgorithm::determineAppropriateKnockoutTotal($group->total);
        $group->scheme = Schemes::$SINGLE_KNOCK_OUT;

        //第一阶段的rank为第一阶段出现人数
        if ($group->stage->serial == 0 && count($contest->stages) > 1) {
            $group->rank = $factors->group_promotion_count;
        } else {
            $group->rank = $factors->ranking_scope;
        }

        // Set label
        $group->label = sprintf(
            "%s淘汰赛%d组",
            $this->getStageLabel($contest, $group->stage),
            $group->serial
        );

        $group->has_pre_level = (bool) ($group->total > $group->size);

        $rounds = log($group->size, 2);
        $group->match_rounds = $factors->getMatchRounds(
            Schemes::$SINGLE_KNOCK_OUT, $rounds + ($group->has_pre_level ? 1 : 0)
        );

        $group->save();

        return new KnockoutPlayGroup([
            "contest" => $contest,
            "stage" => $group->stage,
            "group" => $group
        ]);
    }
}