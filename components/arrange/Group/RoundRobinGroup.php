<?php
/* User: CaryYe , 03/08/2017 5:23 PM */
namespace app\components\arrange\Group;

use app\models\Schemes;

class RoundRobinGroup extends AbstractSchemeAwareGroup
{
    /**
     * @param $contest
     * @param $group
     * @param $factors
     * @return RoundRobinPlayGroup
     */
    public function initializePlayGroup($contest, $group, $factors) {
        $group->size = $group->total;
        $group->mode = $contest->mode;

        if ($contest->mode == "team") {
            $group->team_scheme = $factors->team_match_scheme;
        }

        $group->rank = $factors->group_promotion_count;
        $group->scheme = Schemes::$SINGLE_ROUND_ROBIN;
        $group->circle = $factors->group_circle_algorithm;

        $numRound = $group->size % 2 == 0
            ? $group->size - 1
            : $group->size;

        $group->match_rounds = $factors->getMatchRounds(
            Schemes::$SINGLE_ROUND_ROBIN,
            $numRound
        );

        // Set label
        $group->label = sprintf(
            "%s循环赛%d组",
            $this->getStageLabel($contest, $group->stage),
            $group->serial
        );

        $match_rounds = $group->match_rounds;
        $match_rounds = count($match_rounds) > 1
            ? '['.implode(',', $match_rounds).']'
            : current($match_rounds);
        $group->match_rounds = $match_rounds;
        $group->save();
        $stage = $group->stage;
        return new RoundRobinPlayGroup([
            "contest" => $contest,
            "stage" => $stage,
            "group" => $group
        ]);
    }
}