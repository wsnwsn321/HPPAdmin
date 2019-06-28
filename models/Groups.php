<?php
/**
 * User: CaryYe , 29/07/2017 3:25 PM
 */
namespace app\models;

class Groups extends CActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return "groups";
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        $validates = [
        ];

        $defaults = [
        ];

        return array_merge($validates, parent::onCreate($defaults));
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getStage()
    {
        return $this->hasOne(Stages::className(), ["id" => "stage_id"]);
    }
}