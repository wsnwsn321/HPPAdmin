<?php
/** CaryYe 2019/6/25 1:44 PM */
namespace app\modules\admin\controllers;

class ErrorController extends \yii\web\Controller
{
    public $layout = "@vendor/dmstr/yii2-adminlte-asset/example-views/yiisoft/yii2-app/layouts/main.php";
    public $errorView = "@vendor/dmstr/yii2-adminlte-asset/example-views/yiisoft/yii2-app/site/error.php";

    public function init()
    {
        parent::init();
        if (\yii::$app->getUser()->isGuest) {
            $this->layout = "@app/views/layouts/main.php";
        }
    }

    public function actionIndex()
    {
        $exception = \Yii::$app->errorHandler->exception;
        if ($exception !== null) {
            return $this->render($this->errorView, [
                "exception" => $exception,
                "name" => $exception->statusCode,
                "message" => $exception->getMessage()
            ]);
        }
    }
}