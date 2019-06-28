<?php
/**
 * Created by PhpStorm.
 * User: CaryYe
 * Date: 13/07/2017
 * Time: 10:23 AM
 */
namespace app\modules\api\controllers;

use yii\rest\ActiveController;

class CActiveController extends ActiveController
{
    public $retMsg = [];

    /**
     * @inheritdoc
     */
    public function init()
    {
        $this->retMsg = \yii::$app->params["retMsg"]["global"];
        parent::init();
    }

    /**
     * @inheritdoc
     */
    public function actions()
    {
        return []; // TODO: Change the autogenerated stub
    }
}