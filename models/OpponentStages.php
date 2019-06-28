<?php
/**
 * User: CaryYe 17/07/2017 9:15 AM
 */
namespace app\models;

class OpponentStages extends CActiveRecord
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        $validates = [
            [["opponent_id", "stage"], "required", "on" => self::SCENARIO_CREATE],
            [["opponent_id"], "integer", "min" => 1],
            ["stage", "integer"],
            ["opponent_id",  "wasExists", "params" => ['m' => "Opponents", 'c' => "id"]],
        ];

        $defaults = [
            ["is_seed", "default", "value" => 0],
            ["serial", "default", "value" => 0],
            ["grade", "default", "value" => 1],
            ["score", "default", "value" => 0],
            ["win", "default", "value" => 0],
            ["lose", "default", "value" => 0],
            ["win_verify", "default", "value" => 0],
            ["lose_verify", "default", "value" => 0],
            ["score_verify", "default", "value" => 0],
            ["draw", "default", "value" => 0]
        ];

        return array_merge($validates, parent::onCreate($defaults));
    }
}