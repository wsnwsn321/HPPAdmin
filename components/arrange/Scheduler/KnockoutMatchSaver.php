<?php
/* CaryYe , 08/08/2017 9:23 AM */
namespace app\components\arrange\Scheduler;

use app\components\arrange\Match\BatchMatchSaver;
use app\models\Matches;

class KnockoutMatchSaver extends BatchMatchSaver
{
    CONST PLACEHOLDER_REGEXP = 'M\d+ [winner|loser]';
    protected $baseRank = 0;

    private $start; //start serial, lazy loading & cached
    private $contestNo; //contest number
    public $hasPreLevel;
    public $size;

    /**
     * @inheritdoc
     */
    public function init()
    {
        $this->contestNo = $this->contest->number;
        $this->hasPreLevel = $this->group->has_pre_level;
        $this->size = $this->group->size;
        return parent::init();
    }

    /**
     * @param $match
     * @return mixed
     */
    public function saveMore($match) {
        $fields['group_round'] = $match['r'] +  ($this->hasPreLevel ? 1 : 0);
        $fields['rounds'] = $this->matchRounds[$fields['group_round']];
        if (is_scalar($match["p1"]) && isset($this->players[$match["p1"]])) {
            $fields['opponent1_id'] = $this->players[$match["p1"]];
        }
        if (is_scalar($match["p2"]) && isset($this->players[$match["p2"]])) {
            $fields['opponent2_id'] = $this->players[$match["p2"]];
        }

        $player1 = (int) $match["p1"];
        if (is_array($match["p1"])) {
            $p1n = Matches::generateMatchNo($this->contestNo, $this->startMatchSerial + $match["p1"][0]);
            $player1 = "$p1n {$match["p1"][1]}";
        }
        $player2 = (int) $match["p2"];
        if (is_array($match["p2"])) {
            $p2n = Matches::generateMatchNo($this->contestNo, $this->startMatchSerial + $match["p2"][0]);
            $player2 = "$p2n {$match["p2"][1]}";
        }
        $fields['placeholder1'] = $player1;
        $fields['placeholder2'] = $player2;
        $fields['placeholder1_ori'] = $player1;
        $fields['placeholder2_ori'] = $player2;
        $fields['weight'] = $match['w'];

        $groupRankBase = 0;
        if ($match["isCompRank"]) {
            $groupRankBase = $this->size + (int)$this->baseRank;
        } else {
            $groupRankBase = $this->baseRank;
        }
        // label
        if ($match['r'] != -1) {
            if (isset($match["addLvl"]) && $match["addLvl"] !== null) {
                $label = sprintf("附加赛(决%d-%d名)", $groupRankBase + $match["rankBase"] + 1, $groupRankBase + $match["rankBase"] + $match["numPlayer"]);
                if ($match["numPlayer"] == 1) {
                    $fields['no_need_save'] = true; //songlin TODO
                }
            } else {
                $label = sprintf("%d进%d", $groupRankBase + $match["rankBase"], $groupRankBase + $match["rankBase"] / 2);
                if ($match["rankBase"] == 2) {
                    $label .= "(决赛)";
                    if (isset($this->matchRounds['final'])) {
                        $fields['rounds'] = $this->matchRounds['final'];
                    }
                } else if ($match["rankBase"] == 4) {
                    $label .= "(半决赛)";
                }
            }
        } else {
            $label = '抢位赛';
        }
        $fields['label'] = $label;
        return $fields;
    }

    /**
     * @return mixed
     */
    function getCurrentSerial() {
        if ($this->start === null) {
            $this->start = $this->model->getCurrentContestSerial($this->contest);
        }
        return $this->start;
    }
}