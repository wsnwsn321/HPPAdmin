<?php
/**
 * Created by PhpStorm.
 * User: CaryYe
 * Date: 04/08/2017
 * Time: 2:03 PM
 */
namespace app\components\arrange\Match;

use app\models\Matches;

class BatchMatchSaver extends \yii\base\Component
{
    public $contest;
    public $stage;
    public $group;
    public $stageSerial;
    public $groupSerial;
    public $players = [];
    public $model;
    public $matchRounds;
    protected $startMatchSerial;

    /**
     * @inheritdoc
     */
    public function init()
    {
        $this->model = new Matches();
        $this->stageSerial = $this->stage->serial;
        $this->groupSerial = $this->group->serial;
        $this->matchRounds = $this->group->match_rounds;
    }

    /**
     * @param array $players
     * @return void;
     */
    public function setOpponents(array $players)
    {
        $this->players = $players;
    }


    /**
     * (non-PHPdoc)
     * @see IMatchSaverCallback::save()
     */
    public function save(array $arr) {
        $this->startMatchSerial = $this->getCurrentSerial();
        $matches = array();

        foreach ($arr as $m) {
            $player1 = $player2 = $number = null;
            if (isset($m['n'])) {
                $number = Matches::generateMatchNo($this->contest, $this->startMatchSerial + $m['n']);
            }
            $match = array('type' =>$m["type"], 'number' => $number,
                'contest_id' => $this->contest->id, 'status' => Matches::$STATUS_ACTIVE,
                'stage' => $this->stageSerial, 'addition_level' => isset($m["addLvl"]) ? $m["addLvl"] + 1 : 0,
                'group' => $this->groupSerial);

            $match = array_merge($match, $this->saveMore($m));
            // songlin TODO
            if (isset($match['no_need_save'])) {
                continue;
            }
            if ($this->contest->mode == 'team') {
                $match['label'] = '团体'.$match['label'];
                $match['rounds'] = $this->teamScheme == GroupBean::TEAM_SCHEME_NINE_FIVE || $this->teamScheme == GroupBean::TEAM_SCHEME_MULTI_GROUP ? self::TEAM_MATCH_COUNT_NINE : self::TEAM_MATCH_COUNT;
            }
            //$this->model->isNewRecord = true;
            // $this->model->setAttributes($match);
            $model = new Matches();
            $model->setAttributes($match);
            if ($savedMatch = $model->save()) {
                $this->model->setAttributes($model->getAttributes());
                $this->model->id = null;
            }
            //$savedMatch = $this->model->save() && $this->model->id = null;
            if ($this->model->id && $this->contest->mode == "team") {
                $this->generateTeamMatches($savedMatch['Match']);
            }
        }
    }

}