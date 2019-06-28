<?php
/**
 * author     : forecho <caizh@chexiu.cn>
 * createTime : 2016/3/10 14:39
 * description:
 */
namespace app\modules\admin\controllers;

use yii\filters\AccessControl;

class LoginController extends \yii\web\Controller
{
    public $layout = "@vendor/dmstr/yii2-adminlte-asset/example-views/yiisoft/yii2-app/layouts/main.php";

    public function behaviors()
    {
        return [
            // 后台必须登录才能使用
            'access' => [
                'class' => AccessControl::className(),
                'rules' => [
                    [
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],
        ];
    }

    public static function error($code = 400, $message = "") {
        throw new \yii\web\HttpException($code, $message);
    }
}