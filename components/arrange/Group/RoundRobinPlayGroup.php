<?php
/* CaryYe , 04/08/2017 1:07 PM */
namespace app\components\arrange\Group;

use app\components\arrange\Scheduler\RoundRobinMatchSaver;
use app\models\Matches;
use app\models\Schemes;
use app\components\ScheduleAlgorithm;

class RoundRobinPlayGroup extends PlayGroup
{
    /**
     * @return string
     */
    public function getScheme()
    {
        return Schemes::$SINGLE_ROUND_ROBIN;
    }

    /**
     * @return void
     */
    public function schedule() {
        $rounds = ScheduleAlgorithm::roundMatch(
            $this->getTotal(),
            $this->group->circle
        );

        $i = 0;
        $matches = array();
        foreach ($rounds as $round => $againsts) {
            foreach ($againsts as $against) {
                $match = [];
                // 循环赛不需要插入轮空比赛
                if ($against[0] == 0 || $against[1] == 0) {
                    continue;
                }
                $match["p1"] = $against[0];
                $match["p2"] = $against[1];
                $match['r'] = $round;
                $match['n'] = ++$i;
                $match['w'] = 0;
                $match["type"] = Matches::$TYPE_NORMAL;
                $matches[$i] = $match;
            }
        }
        $callback = $this->getMatchSaver();
        $callback->setOpponents($this->getSaverPlayerSerialAndIds());
        $callback->save($matches);
    }

    /**
     * @return RoundRobinMatchSaver
     */
    public function getMatchSaver()
    {
        return new RoundRobinMatchSaver([
            "contest" => $this->contest,
            "stage" => $this->stage,
            "group" => $this->group
        ]);
    }
}