<?php
/**
 * Created by PhpStorm.
 * User: CaryYe
 * Date: 17/07/2017
 * Time: 9:12 AM
 */
namespace app\modules\api\controllers;

class OpponentStageController extends CActiveController
{
    /**
     * @inheritdoc
     */
    public $modelClass = "app\models\OpponentStages";

    /**
     * @return array
     */
    public function actionCreate()
    {
        $scenario = $this->modelClass::SCENARIO_CREATE;
        $m = new $this->modelClass(["scenario" => $scenario]);
        $m->setAttributes(\yii::$app->getRequest()->post());

        return $m->save()
            ? array_merge($this->retMsg["success"], ["data" => ["id" => $m->id]])
            : array_merge($this->retMsg["sf"], ["data" => $m->getErrors()]);
    }
}