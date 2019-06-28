<?php
/* User: CaryYe , 03/08/2017 5:08 PM */
namespace app\components\arrange\Group;
use app\models\Groups;

class AbstractSchemeAwareGroup extends Groups {

    /**
     * 如果两阶段赛制一样返回阶段label，否则null
     * If two scheme of stages as same as each other, return label or null
     * @param $contest
     * @param $stage
     * @return string|null
     */
    protected function getStageLabel($contest, $stage) {
        if (count($contest->stages) > 1
            && $contest->stages[0]->scheme == $contest->stages[1]->scheme)
        {
            return $stage->label;
        }
        return null;
    }
}