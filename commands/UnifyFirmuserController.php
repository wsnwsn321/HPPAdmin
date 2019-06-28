<?php
/** CaryYe 2019/6/26 8:50 AM */
namespace app\commands;

class UnifyFirmuserController extends \yii\console\Controller
{
    public function actionIndex()
    {
        $firmUsers = \app\models\FirmUsers::find()->all();
        if (!empty($firmUsers)) {
            foreach ($firmUsers as $k => $fm) {
                $u = \app\models\User::findOne(["id" => $fm->userId]);
                $p = \app\models\Profiles::findOne(["user_id" => $fm->userId]);
                $fm->setAttributes([
                    "username" => $u->username,
                    "mobile" => $p->mobile,
                    "nickname" => $p->nickname,
                    "fullname" => $p->fullname
                ]);
                $fm->save();
            }
        }
        return true;
    }
}