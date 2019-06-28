<?php
/**
 * Created by PhpStorm.
 * User: CaryYe
 * Date: 14/07/2017
 * Time: 10:16 AM
 */
namespace app\modules\api\controllers;

class OpponentController extends CActiveController
{
    /**
     * @inheritdoc
     */
    public $modelClass = "app\models\Opponents";

    /**
     * @return array
     */
    public function actionCreate()
    {
        $scenario = $this->modelClass::SCENARIO_CREATE;
        $m = new $this->modelClass(["scenario" => $scenario]);
        $m->setProperties(\yii::$app->getRequest()->post());

        return $m->save()
            ? array_merge($this->retMsg["success"], ["data" => ["id" => $m->id]])
            : array_merge($this->retMsg["sf"], ["data" => $m->getErrors()]);
    }
}