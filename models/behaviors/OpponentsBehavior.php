<?php
/**
 * Created by PhpStorm.
 * User: CaryYe
 * Date: 17/07/2017
 * Time: 2:51 PM
 */
namespace app\models\behaviors;

use app\models\Enrollments;
use app\components\HttpRequest;
use app\models\Opponents;

class OpponentsBehavior extends \yii\base\Behavior
{
    public function events()
    {
        return [
            Opponents::OPPONENTS_AFTER_SAVE => [$this, "createOpponentStage"]
        ];
    }

    /**
     * after insert , need to create create opponent_stage
     * @return void
     */
    public function createOpponentStage($event)
    {
        $h = new HttpRequest();
        $h = new \app\components\HttpRequest();
        $h->send(
            \yii::$app->params["baseurl"]."/api/opponent-stages",
            [
                "opponent_id" => $event->sender->id,
                "stage" => $event->sender->contest->stages[0]->serial
            ],
            "POST"
        );
    }
}