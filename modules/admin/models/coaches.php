<?php


namespace app\modules\admin\models;


use yii\db\ActiveRecord;

class coaches extends ActiveRecord
{
    public function attributeLabels()
    {
        return [
            'id' => '教练编号',
            "name" => "教练姓名",
            "credit" => "积分",
            "age" => "年龄",
            "teachingAge" => "执教年数",
            'sex' => '性别',
            'fee' => '费用',
            'honor'=>'生涯荣誉',
            'grade'=>'执教成绩'
        ];
    }
}