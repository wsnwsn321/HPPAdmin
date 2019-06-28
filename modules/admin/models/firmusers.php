<?php

namespace app\modules\admin\models;
use app\models\Enrollments;
use app\models\User;
use yii\db\ActiveRecord;
use app\models\Credits;

class firmusers extends ActiveRecord
{
    public static function tableName(){
        return 'firm_users';
    }
    private $credit;
    public function getCredit(){
        return $this->credits->amount;
    }
    public function getGender()
    {
        // 第一个参数为要关联的子表模型类名，
        // 第二个参数指定 通过子表的customer_id，关联主表的id字段
        return $this->hasOne(Enrollments::className(), ['user_id' => 'userId']);
    }
    public function getWx()
    {
        return $this->hasOne(User::className(), ['id' => 'userId']);
    }
    public function getCredits()
    {
        return $this->hasOne(Credits::className(), ['user_id' => 'userId']);
    }
    public function getProfileData()
    {
        return $this->hasOne(\app\models\Profiles::className(), ['user_id' => 'userId']);
    }

    public function attributeLabels()
    {
        return [
            'userId'=>'用户编号',
            'nickname'=>'姓名',
            'mobile'=>'手机号码',
            'fullname'=>'真实姓名'

        ];
    }

    public function getUser()
    {
        // 第一个参数为要关联的子表模型类名，
        // 第二个参数指定 通过子表的customer_id，关联主表的id字段
        return $this->hasOne(User::className(), ['id' => 'user_id']);
    }
}