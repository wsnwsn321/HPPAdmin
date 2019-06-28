<?php
/* CaryYe , 16/08/2017 9:28 AM */
namespace app\controllers;

use app\models\Stages;
use yii\rest\Controller;
use app\models\Games;
use app\models\Contests;
use app\components\Events\AfterContestCreateEvent;
use app\components\Behaviors\AfterContestCreateBehavior;

/**
 * Class WapArrangeController
 * @package app\controllers
 */
class GameController extends Controller
{
    public $retMsg = [];

    /** @inheritdoc */
    public function behaviors()
    {
        $behaviors = parent::behaviors();

        $behaviors["afterCreate"] = [
            "class" => AfterContestCreateBehavior::className()
        ];

        return $behaviors;
    }

    /**
     * @inheritdoc
     */
    public function init()
    {
        $this->retMsg = \yii::$app->params["retMsg"]["global"];
        parent::init();
    }

    /**
     * Create game and it's contest one-off.
     * @return array
     */
    public function actionLeagueCreate()
    {
        $errors = [];
        $game = new Games(["scenario" => Games::SCENARIO_CREATE]);
        $contest = new Contests(["scenario" => Contests::SCENARIO_CREATE]);
        $stage = new Stages(["scenario" => Stages::SCENARIO_CREATE]);
        $post = \yii::$app->getRequest()->post();
        $post["game_id"] = 1;
        $post["contest_id"] = 1;
        $post["is_group"] = 0;

        // Prepare data for game and contest, then validate it.
        $game->setAttributes($post);
        $contest->setProperties($post);
        $stage->setAttributes($post);
        $game->validate() !== true && array_push($errors, $game->getErrors());
        $contest->validate() !== true && array_push($errors, $contest->getErrors());
        $stage->validate() !== true && array_push($errors, $stage->getErrors());

        if (!empty($errors)) {
            return array_merge($this->retMsg["vf"], ["data" => $errors]);
        }

        $db = \yii::$app->db;
        $transaction = $db->beginTransaction();

        // Save data through the rest api(s).
        if (! $game->save()) {
            $transaction->rollBack();
            return array_merge(
                $this->retMsg["sf"],
                ["data" => $game->getErrors()]
            );
        }

        $ret["game_id"] = $post["game_id"] = $game->id;
        $contest->setProperties($post);

        if (!in_array(
            $post["scheme"],
            ["single_round_robin", "single_knock_out"]
        ))
        {
            $transaction->rollBack();
            return array_merge(
                $this->retMsg["vf"],
                ["data" => ["scheme" => ["Scheme of contest illegal."]]]
            );
        }

        if ($contest->save()) {
            // When saved succeed, first, trigger the event to insert associated data for tables(`rules`,`stages`), and then update status of `games`
            $rules = is_array($post["rules"]) ? $post["rules"] : [];
            $event = new AfterContestCreateEvent([
                "rule" => array_merge(["contest_id" => $contest->id], $rules),
                "stage" => [
                    "is_group" => 0,
                    "scheme" => \yii::$app->getRequest()->post("scheme"),
                    "contest_id" => $contest->id
                ]
            ]);
            $this->trigger("contest_after_create", $event);

            $game->setAttributes([
                "status" => "published",
                "publish_date" => date("Y-m-d H:i:s", time())]
            );
            $game->save();
        } else {
            $transaction->rollBack();
            return array_merge(
                $this->retMsg["sf"],
                ["data" => $contest->getErrors()]
            );
        }

        $transaction->commit();
        $ret["contest_id"] = $contest->id;

        return array_merge(
            $this->retMsg["success"],
            ["data" => $ret]
        );
    }
}