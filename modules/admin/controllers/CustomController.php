<?php
namespace app\modules\admin\controllers;
use yii\web\Controller;
class CustomController extends Controller {
    public function actionGreet() {
        return $this->render('greet');
    }
}

?>
