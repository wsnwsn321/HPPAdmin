<?php
/* CaryYe , 08/08/2017 9:14 AM */
namespace app\components\arrange\Group;

use app\models\Schemes;
use app\components\ScheduleAlgorithm;
use app\components\arrange\Scheduler\KnockoutMatchSaver;

class KnockoutPlayGroup extends PlayGroup
{
    /**
     * @return string
     */
    public function getScheme()
    {
        return Schemes::$SINGLE_KNOCK_OUT;
    }

    /**
     * @return void
     */
    public function schedule() {
        $algorithm = ScheduleAlgorithm::getInstance(true);
        $callback = $this->getMatchSaver();
        $callback->setOpponents($this->getSaverPlayerSerialAndIds());
        $algorithm->knockoutMatch($this->getTotal(), $this->group->rank, $callback);
    }

    /**
     * @return KnockoutMatchSaver
     */
    public function getMatchSaver() {
        return new KnockoutMatchSaver([
            "contest" => $this->contest,
            "stage" => $this->stage,
            "group" => $this->group,
            "hasPreLevel" => $this->group->has_pre_level
        ]);
    }
}