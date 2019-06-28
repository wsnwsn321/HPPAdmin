<?php
/**
 * CaryYe , 11/07/2017 9:59 AM
 */
namespace app\components\Behaviors;

use app\components\HttpRequest;
use app\modules\api\controllers\ContestController;

class  AfterContestCreateBehavior extends \yii\base\Behavior
{
    private $hr;

    /**
     * @inheritdoc
     */
    public function events()
    {
        return [ContestController::AFTER_CREATE => [$this, "afterContestCreate"]];
    }

    /**
     * @inheritdoc
     */
    public function init()
    {
        $this->hr = new HttpRequest();
    }

    /**
     * @param \yii\base\Event $event
     * @return void
     */
    public function afterContestCreate($event)
    {
        if (isset($event->rule)
            && is_array($event->rule)
            && !empty($event->rule))
        {
            $this->createRule($event->rule);
        }

        if (isset($event->stage)
            && is_array($event->stage)
            && !empty($event->stage))
        {
            $this->createStage($event->stage);
        }

        if (isset($event->game)
            && is_array($event->game)
            && !empty($event->game))
        {
            $this->updateGame($event->game);
        }
    }

    /**
     * @param array $rule
     * @return void
     */
    public function createRule($rule)
    {
        $this->hr->send(
            \yii::$app->params["baseurl"]."/api/rules", $rule, "POST"
        );
    }

    /**
     * @param array $stage
     * @return void
     */
    public function createStage($stage)
    {
        $this->hr->send(
            \yii::$app->params["baseurl"]."/api/stages", $stage, "POST"
        );
    }

    /**
     * @param array $game
     * @return void
     */
    public function updateGame($game)
    {
        $this->hr->send(
            \yii::$app->params["baseurl"]."/api/games/".trim($game["game_id"]),
            $game,
            "PUT"
        );
    }
}