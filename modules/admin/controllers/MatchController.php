<?php

namespace app\modules\admin\controllers;
use  \app\modules\admin\models\games;
use yii\helpers\Json;
use app\models\Contests;


class MatchController extends LoginController {

    public function actionMatchInfo()
    {
        if (\yii::$app->request->post('hasEditable')) {
            $id = \yii::$app->request->post('editableKey');
            $formKey = \yii::$app->getRequest()->post("editableAttribute");
            $model = games::findOne(["id" => $id]);
            $model->setScenario("update");
            $posted = current($_POST['games']);
            $formVal = $posted[$formKey];
            if($formKey=='name'){
                $contests =Contests::findOne(['game_id'=>$model->id]);
                $contests->$formKey = $formVal;
                $contests->save();
            }
            else  if($formKey =='arena'){
                $model = Contests::findOne(['game_id'=>$model->id]);
                $formKey = 'arena';
            }
            $model->$formKey = $formVal;
            $model->save();



            return Json::encode(['output' => $formVal, 'message' => '']);
        }
        return $this->render('match-info',[
        ]);
    }

    public function actionContests(){
        return $this->render('contests',[
        ]);
    }
}