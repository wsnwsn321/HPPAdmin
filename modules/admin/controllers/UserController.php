<?php

namespace app\modules\admin\controllers;
use yii\helpers\Json;
use app\modules\admin\models\firmusers;
use app\models\Credits;
use app\models\Profiles;

class UserController extends LoginController
{
    public function actionIndex()
    {
        if (\yii::$app->request->post('hasEditable')) {

            $id = \yii::$app->request->post('editableKey');
            $formKey = \yii::$app->getRequest()->post("editableAttribute");
            $posted = current($_POST['firmusers']);
            $formVal = $posted[$formKey];
            if($formKey == 'credit'){
                $model = firmusers::findOne(["id" => $id]);
                $user = $model->userId;
                $model = Credits::findOne(["user_id" => $user]);
                $formKey = 'amount';
            }
            else{
                $model = firmusers::findOne(["id" => $id]);
                $user = $model->userId;
                $model = firmusers::findOne(["id" => $id],["userId" => $user],['firmId'=>\yii::$app->params['admin_firm'][\yii::$app->getUser()->getId()]]);
                $model2 = Profiles::findOne(["id" => $user]);
                $model2->$formKey = $formVal;
                $model2->save();
            }
            $model->$formKey = $formVal;
            $model->save();
            return Json::encode(['output' => $formVal, 'message' => '']);
        }

        return $this->render('index', [
            'dataProvider' => firmusers::find(),
            'searchModel' => null //$searchModel
        ]);
    }

    /**
     * @return void
     */
    public function actionDelete(){
        firmusers::deleteAll([
            'id' => (int) \yii::$app->getRequest()->get("id")
        ]);

        echo("<script>location.href = '/admin/user';</script>");
        exit;
    }

    public function actionDeleteall()
    {
        return firmusers::deleteAll(['id'=>\yii::$app->request->post('arr_id')]);
    }
}