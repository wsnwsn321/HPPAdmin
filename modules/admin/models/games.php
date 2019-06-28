<?php

namespace app\modules\admin\models;

use yii\db\ActiveRecord;
use app\models\Contests;

class games extends ActiveRecord
{

    public function rules(){
        return [];
    }
    private $arena;
    public function getArena(){
        return $this->contests->arena;
    }
    public function getContests()
    {
        // 第一个参数为要关联的子表模型类名，
        // 第二个参数指定 通过子表的customer_id，关联主表的id字段
        return $this->hasOne(Contests::className(), ['id' => 'id']);
    }
    public function scenarios()
    {
        $s = parent::scenarios();
        $s["update"] = [];
        return $s;
    }
    public function attributeLabels()
    {
        return [
            'id' =>'赛事编号',
            "name" => "赛事名称",
            "contact" => "联系人",
            "scheduled_start_date" => "赛事开始日期",
            "scheduled_end_date" => "赛事结束日期"
        ];
    }
}