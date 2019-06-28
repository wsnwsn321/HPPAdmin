<?php
/**
 * Created by PhpStorm.
 * User: CaryYe
 * Date: 10/07/2017
 * Time: 10:00 AM
 */
namespace app\models;

class Rules extends CActiveRecord
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        $validates = [
            [["contest_id"], "required", "on" => self::SCENARIO_CREATE],
            ["contest_id", "integer", "min" => 1],
        ];

        $defaults = [];

        $filters = [
            ["params", "filter", "filter" => [$this, "ArrayToJson"]],
            ["options", "filter", "filter" => [$this, "ArrayToJson"]]
        ];

        return array_merge($validates, $defaults, $filters);
    }

    /**
     * @param array $val
     * @return string
     */
    public function ArrayToJson($val)
    {
        if (is_array($val)) {
            $applicable_columns = [];

            foreach($val as $k => $v) {
                if (!is_null($v)
                    && strtolower(trim($v)) !== "null")
                {
                    $applicable_columns[$k] = $v;
                }
            }

            return !empty($applicable_columns)
                ? json_encode($applicable_columns) :
                null;
        }

        return $val;
    }
}