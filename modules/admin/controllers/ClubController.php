<?php
namespace app\modules\admin\controllers;
use app\models\Contests;
use app\modules\admin\controllers\LoginController;
use app\modules\admin\models\association_intro;
use app\modules\admin\models\association_news;
use app\modules\admin\models\coaches;
use yii\helpers\Json;

class ClubController extends LoginController {
    public function actionIndex()
    {
        if (\yii::$app->request->post('hasEditable')) {
            $id = \yii::$app->request->post('editableKey');
            $formKey = \yii::$app->getRequest()->post("editableAttribute");
            $model = association_news::findOne(["id" => $id]);
            //$model->setScenario("update");
            $posted = current($_POST['association_news']);
            $formVal = $posted[$formKey];
            $model->$formKey = $formVal;
            $model->save();
            return Json::encode(['output' => $formVal, 'message' => '']);
        }
        return $this->render('index',[
        ]);
    }

    public function actionIntro()
    {
        if (\yii::$app->request->post('hasEditable')) {
            $id = \yii::$app->request->post('editableKey');
            $formKey = \yii::$app->getRequest()->post("editableAttribute");
            $model = association_intro::findOne(["id" => $id]);
            //$model->setScenario("update");
            $posted = current($_POST['association_intro']);
            $formVal = $posted[$formKey];
            $model->$formKey = $formVal;
            $model->save();
            return Json::encode(['output' => $formVal, 'message' => '']);
        }
        return $this->render('intro',[
        ]);
    }

    public function actionCoach()
    {
        if (\yii::$app->request->post('hasEditable')) {
            $id = \yii::$app->request->post('editableKey');
            $formKey = \yii::$app->getRequest()->post("editableAttribute");
            $model = coaches::findOne(["id" => $id]);
            //$model->setScenario("update");
            $posted = current($_POST['coaches']);
            $formVal = $posted[$formKey];
            $model->$formKey = $formVal;
            $model->save();
            return Json::encode(['output' => $formVal, 'message' => '']);
        }
        return $this->render('coach',[
        ]);
    }
}