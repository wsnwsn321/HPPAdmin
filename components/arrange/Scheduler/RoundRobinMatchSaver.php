<?php
/* CaryYe , 04/08/2017 2:07 PM */
namespace app\components\arrange\Scheduler;
use app\components\arrange\Match\BatchMatchSaver;

class RoundRobinMatchSaver extends BatchMatchSaver
{
    public $isGroup;

    /**
     * @inheritdoc
     */
    public function init()
    {
        $this->isGroup = (bool) ($this->stage->is_group);
        return parent::init();
    }

    /* (non-PHPdoc)
     * @see IBatchMatchSaver::saveMore()
     */
    public function saveMore($match) {
        $fields['group_round'] = $match['r'];
        $fields['rounds'] = $this->getFirstRound($this->matchRounds);
        $fields['placeholder1'] = $match["p1"];
        $fields['placeholder2'] = $match["p2"];
        $fields['placeholder1_ori'] = $match["p1"];
        $fields['placeholder2_ori'] = $match["p2"];
        if (isset($this->players[$match["p1"]])) {
            $fields['opponent1_id'] = $this->players[$match["p1"]];
        }
        if (isset($this->players[$match["p2"]])) {
            $fields['opponent2_id'] = $this->players[$match["p2"]];
        }
        $fields['label'] = ($this->isGroup ? '小组' : '') . '循环赛';
        $fields['weight'] = 0;
        return $fields;
    }

    /* (non-PHPdoc)
     * @see BatchMatchSaver::getCurrentSerial()
     */
    function getCurrentSerial() {
        return $this->model->getCurrentContestSerial($this->contest);
    }

    /**
     * @param int $matchRounds
     * @return integer
     */
    public function getFirstRound($matchRounds = 0)
    {
        if (preg_match('/^\[\d+?,.*?\]$/is', $matchRounds)) {
            $explodedMatchRounds = explode(',', $matchRounds);
            $firstMatchRound = array_shift($explodedMatchRounds);
            $round = substr($firstMatchRound, 1);
        } else {
            return $matchRounds;
        }

        return $round[0];
    }
}