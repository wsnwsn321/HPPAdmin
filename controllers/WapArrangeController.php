<?php
/* CaryYe / 26/07/2017 9:31 AM */
namespace app\controllers;

use yii\rest\Controller;
use app\models\Contests;
use app\components\arrange\Engines\ContestEngine;

/**
 * Class WapArrangeController
 * @package app\controllers
 */
class WapArrangeController extends Controller
{
    // Prepare to  schedule step
    public $step = 0;

    public $retMsg = [];

    /**
     * @inheritdoc
     */
    public function init()
    {
        $this->retMsg = \yii::$app->params["retMsg"]["global"];
        parent::init();
    }

    /**
     * @return array
     */
    public function actionArrange($contest_id)
    {
        // This step similar to "initContest"
        $c = ContestEngine::startup((int) $contest_id);
        if (is_null($c)) {
            return array_merge(
                $this->retMsg["vf"],
                ["data" => ["message" => "Contest_id error!"]]
            );
        }

        // Verify contest can be schedule.
        $c->contest->scenario = Contests::SCENARIO_PUBLISHED;
        if (!$c->contest->validate()) {
            return array_merge(
                $this->retMsg["vf"],
                ["data" => $c->contest->getErrors()]
            );
        }
        $c->contest->scenario = Contests::SCENARIO_DEFAULT;

        // Reset contest
        if (($r = $c->_resetContest($this)) !== true) {
            return array_merge($this->retMsg["vf"], ["data" => $r]);
        }

        // Change status of contest
        if (! $c->contest->isReadyForSchedule()) {
            $c->contest->setAttribute("status", "closed");
            $c->contest->save();
        }

        // Start schedule
        $c->checkEnrollDataIntegrity($c->contest);

        // Init group
        if (($r = $c->initGroup($this)) !== true) {
            return array_merge($this->retMsg["vf"], ["data" => $r]);
        }

        // Init match
        if (($r = $c->initMatch($this)) !== true) {
            return array_merge($this->retMsg["vf"], ["data" => $r]);
        }

        // Change status for contest.
        $c->contest->changePublishedStatus();

        return $this->retMsg["success"];
    }
}