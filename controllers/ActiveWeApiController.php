<?php
/* CaryYe 2018/4/12 9:38 AM */
namespace app\controllers;
use app\WxComponents\DiRegister;
use yii\web\BadRequestHttpException;

/**
 * Class ActiveWeApiController
 * @package app\controllers
 */
class ActiveWeApiController extends \yii\web\Controller
{
    /** @inheritdoc */
    public $enableCsrfValidation = false;

    /** @inheritdoc */
    public function init()
    {
        parent::init();
        if (! DiRegister::exe(trim(\yii::$app->request->get("weChat")))) {
            throw new BadRequestHttpException("Invalid WeChat Id", 400);
        }
    }

    /** @inheritdoc */
    public function actions()
    {
        return [
            "menu" => ["class" => "app\controllers\ActiveWeApis\MenuAction"],
            "user" => ["class" => "app\controllers\ActiveWeApis\UserAction"],
            "inform" => ["class" => "app\controllers\ActiveWeApis\InformAction"],
            "template-industry" => ["class" => "app\controllers\ActiveWeApis\TemplateIndustryAction"],
            "subscribed" => ["class" => "app\controllers\ActiveWeApis\SubscribedAction"]
        ];
    }
}