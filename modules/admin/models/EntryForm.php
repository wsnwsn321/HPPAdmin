<?php
namespace app\modules\admin\models;
use yii\db\ActiveRecord;
class EntryForm extends ActiveRecord
{
    public $name;
    public $email;

    public function rules()
    {
        return [
            [['name', 'email'], 'required'],
            ['email', 'email'],
        ];
    }
}

