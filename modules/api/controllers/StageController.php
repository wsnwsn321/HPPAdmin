<?php
/**
 * Created by PhpStorm.
 * User: CaryYe
 * Date: 10/07/2017
 * Time: 5:07 PM
 */
namespace app\modules\api\controllers;

class StageController extends CActiveController
{
    public $modelClass = "app\models\Stages";

    /**
     * @return array
     */
    public function actionCreate()
    {
        $m = new $this->modelClass(["scenario" => $this->modelClass::SCENARIO_CREATE]);
        $m->setAttributes(\yii::$app->getRequest()->post());

        return $m->save()
            ? array_merge($this->retMsg["success"], ["data" => ["id" => $m->id]])
            : array_merge($this->retMsg["sf"], ["data" => $m->getErrors()]);
    }

    /**
     * @return array
     */
    public function actionUpdate($id)
    {
        $game = $this->modelClass::findOne(["id" => (int) $id]);
        if (is_null($game)) return $this->retMsg["id_not_found"];

        $game->setAttributes(\yii::$app->getRequest()->post());
        return $game->save()
            ? $this->retMsg["success"]
            : array_merge($this->retMsg["sf"], ["data" => $game->getErrors()]);
    }
}