<?php
/**
 * CaryYe 13/07/2017 3:01 PM
 */
namespace app\models\behaviors;

use app\models\Credits;
use app\models\Enrollments;
use app\components\HttpRequest;

class EnrollmentsBehavior extends \yii\base\behavior
{
    // new HttpRequest
    public $h = null;

    /**
     * @inheritdoc
     */
    public function events()
    {
        return [
            Enrollments::ENROLLMENTS_AFTER_SAVE => [$this, "afterSave"]
        ];
    }

    /**
     * This function is a direct function to perform child funcions
     *
     * @param $event
     * @return void
     */
    public function afterSave($event)
    {
        //is_null($this->h) && $this->h = new HttpRequest();
        $this->credit($event);
    }

    /**
     * @param $event
     * @return void
     */
    public function credit($event)
    {
        //$cid = (int) $event->sender->contest->id;
        if ($event->sender->contest->PingpongContests->category == "credit") {
            $uid = (int)$event->sender->user_id;
            $credit = Credits::findOne(["user_id" => $uid]);
            if (is_null($credit)) {
                $model = new Credits(["scenario" => Credits::SCENARIO_CREATE]);
                $model->setAttributes([
                    "user_id" => $uid,
                    "sport_id" => 1,
                    "amount" => 1500,
                    "is_active" => 0,
                    "fc" => 1500
                ]);
                $model->save();
            }
        }
    }
}