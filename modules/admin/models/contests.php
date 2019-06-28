<?php

namespace app\modules\admin\models;


use yii\db\ActiveRecord;

class contests extends ActiveRecord
{
    public function attributeLabels()
    {
        return [
            'id' =>'项目编号',
            "name" => "项目名称",
            "contact" => "联系人",
            "scheduled_start_date" => "项目开始日期",
            "scheduled_end_date" => "赛事结束日期",
            'enroll_count' =>'报名人数',
        ];
    }
}