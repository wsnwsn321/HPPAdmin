<?php

namespace app\modules\admin\models;


use yii\db\ActiveRecord;

class association_news extends ActiveRecord
{
    public function attributeLabels()
    {
        return [
            'id' =>'新闻编号',
            "title" => "新闻标题",
            "author" => "作者",
            "date" => "日期",
            "cover" => "封面图片",
            'content' =>'新闻内容',
        ];
    }
}