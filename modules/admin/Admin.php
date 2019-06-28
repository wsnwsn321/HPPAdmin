<?php
namespace app\modules\admin;
/**
 * @Desc Admin module, used for managing the backend of SaaS
 * Class Admin
 * @package app\modules\admin
 */
class Admin extends \yii\base\Module {
    public $controllerNamespace = "app\modules\admin\controllers";

    public function init() {
        \yii::$app->errorHandler->errorAction = 'admin/error/index';
        parent::init();
    }
}
?>