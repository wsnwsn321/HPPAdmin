<?php
/**
 * Created by PhpStorm.
 * User: CaryYe
 * Date: 10/07/2017
 * Time: 4:50 PM
 */
namespace app\models;

class Stages extends CActiveRecord
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        $validates = [
            [["contest_id", "scheme", "is_group"], "required", "on" => self::SCENARIO_CREATE],
            ["contest_id", "integer", "min" => 1],
            ["scheme", "in", "range" => ["single_round_robin", "single_knock_out"]],
            ["is_group", "in", "range" => [0, 1]]
        ];

        $defaults = [
            ["serial", "default", "value" => 0],
            ["label", "default", "value" => "第1阶段"],
            ["is_draw", "default", "value" => 0],
            ["default_match_minutes", "default", "value" => 10],
            ["scheduled_group", "default", "value" => 1]
        ];

        return array_merge($validates, parent::onCreate($defaults));
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getGroups()
    {
        return $this->hasMany(Groups::className(), ["stage_id" => "id"])
            ->orderBy("serial asc");
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getContest()
    {
        return $this->hasOne(Contests::className(), ["id" => "contest_id"]);
    }
}