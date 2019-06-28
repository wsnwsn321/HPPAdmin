<?php
/* CaryYe on 05/07/20173:33 PM */
/**
 * Desc: This controller using for create game, that was provided in the form of rest.
 */
namespace app\modules\api\controllers;

class GameController extends CActiveController
{
    public $modelClass = "app\models\Games";

    function actionTest()
    {
        echo __method__;
    }

    /**
     * Desc: The rest api of create game(s).
     * @return array
     */
    function actionCreate()
    {
        $m = new $this->modelClass(["scenario" => $this->modelClass::SCENARIO_CREATE]);
        $m->setAttributes(\yii::$app->getRequest()->post());

        $r = $m->save();
        $p = $r
            ? array_merge($this->retMsg["success"], ["data" => ["id" => $m->id]])
            : array_merge($this->retMsg["sf"], ["data" => $m->getErrors()]);

        return $p;
    }

    /**
     * Desc: Provide the single row data
     * @return array
     */
    public function actionView($id)
    {
        $row = $this->modelClass::findOne(["id" => (int) $id]);
        return ! is_null($row)
            ? $row->toArray()
            : $this->retMsg["id_not_found"];
    }

    /**
     * Desc: Update game , contains publish game/disable game ...
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
