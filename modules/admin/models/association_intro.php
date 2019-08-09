<?php

namespace app\modules\admin\models;


use yii\db\ActiveRecord;

class association_intro extends ActiveRecord
{
    public function attributeLabels()
    {
        return [
            'id' =>'编号',
            "introduction" => "简介",
        ];
    }
}