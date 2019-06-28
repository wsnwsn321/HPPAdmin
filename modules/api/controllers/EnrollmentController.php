<?php
/**  CaryYe 12/07/2017 6:37 PM */
namespace app\modules\api\controllers;
use app\modules\api\controllers\behaviors\SinglyEnrollBehavior;
use app\modules\api\controllers\behaviors\SinglyCancelBehavior;

class EnrollmentController extends CActiveController
{
    /** @inheritdoc */
    public $modelClass = "app\models\Enrollments";

    /** @inheritdoc */
    public function behaviors()
    {
        return array_merge(
            parent::behaviors(),
            [
                "SinglyEnroll" => [
                    "class" => SinglyEnrollBehavior::className()
                ],
                "SinglyCancel" => [
                    "class" => SinglyCancelBehavior::className()
                ]
            ]
        );
    }

    /**
     * @Desc This method is for enrolling multiple users simultaneously
     * (The adverb "simultaneously" means , in one request).
     * @return array
     */
    public function actionCreate()
    {
        $contestId = (int) \yii::$app->getRequest()->post("contest_id");
        $userIds = explode(',', \yii::$app->getRequest()->post("user_id"));

        if ( !empty($userIds) && $contestId > 0 ) {
            $db = \yii::$app->db;
            $transaction = $db->beginTransaction();

            foreach($userIds as $k => $uid) {
                $r = $this->getBehavior("SinglyEnroll")->enroll([
                    "contest_id" => $contestId,
                    "user_id" => (int) $uid
                ], $transaction);
                if ((int) trim($r["code"]) !== 10001) {
                    return $r;
                }
            }

            $transaction->commit();
        }
        return $this->retMsg["success"];
    }

    /** @return array */
    public function actionDelete($id)
    {
        return $this->cancel($id);
    }
}