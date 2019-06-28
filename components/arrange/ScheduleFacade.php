<?php
/* CaryYe , 02/08/2017 5:03 PM */
namespace app\components\arrange;

use app\models\Matches;

class ScheduleFacade extends \yii\base\Component
{
    /**
     * Schedule match - generate the against table
     *
     * @param $contest
     * @param null $stage
     * @return boolean
     */
    public function schedule($contest, $stage = null)
    {
        $lastStage = null;
        // Delete all matches and change opponent's status
        Matches::deleteAllMatchData($contest->id);

        foreach ($contest->stages as $k => $s) {
            if ($stage && $stage->serial != $s->serial) {
                $lastStage = $s;
                continue;
            }

            $S = \yii::createObject("Stage");
            $factors = $contest->schedule_factor_collection;
            $playGroups = $S->resolveSchedulableGroups($contest, $s, $factors);

            foreach ($playGroups as $_k => $group) $group->schedule();

            $lastStage = $s;
        }

        return true;
    }
}