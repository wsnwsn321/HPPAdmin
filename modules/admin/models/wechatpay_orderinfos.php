<?php

namespace app\modules\admin\models;


use app\modules\admin\models\firmusers;
use yii\db\ActiveRecord;

class wechatpay_orderinfos extends ActiveRecord
{

    public function attributeLabels()
    {
        return [
            'user_id' =>'用户编号',
            "out_trade_no" => "交易单号",
            "total_fee" => "支付金额（元）",
            "return_timestamp" => "支付时间",
            'confirm_pay'=>"支付状态",
        ];
    }

    public function getUserName()
    {
        // 第一个参数为要关联的子表模型类名，
        // 第二个参数指定 通过子表的customer_id，关联主表的id字段
        return $this->hasOne(firmusers::className(), ['userId' => 'user_id']);
    }

}