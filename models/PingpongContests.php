<?php
/**
 * Created by PhpStorm.
 * User: CaryYe
 * Date: 06/07/2017
 * Time: 2:04 PM
 */
namespace app\models;

class PingpongContests extends CActiveRecord
{
    use \app\models\StaticVars\PingpongContests;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        $validates = [
            [["misc"], "required", "on" => self::SCENARIO_CREATE],
            [["id", "region"], "integer"],
            ["category", "in", "range" => ["regular", "credit", "grade", "sand"]],
            ["mode", "in", "range" => ["single", "double", "team"]],
            ["gender", "in", "range" => ["male", "female", "mixed"]],
            ["misc", "string", "length" => [2, 50]]
        ];

        $defaults = [
            ["region", "default", "value" => 320500],
            ["category", "default", "value" => "regular"],
            ["mode", "default", "value" => "single"],
            ["gender", "default", "value" => "mixed"]
        ];

        return array_merge($validates, parent::onCreate($defaults));
    }

    /**
     * Because there's no attribute value of "gaoxiao" in table pingpong_contests , so , Transform it now .
     * @inheritdoc
     */
    public function beforeValidate()
    {
        if (trim($this->category) === "gaoxiao") {
            $this->category = "regular";
        }
        return parent::beforeValidate();
    }

    /** @inheritdoc */
    public function attributeLabels()
    {
        return [
            "misc" => "其他说明"
        ];
    }
}