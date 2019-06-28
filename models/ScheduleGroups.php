<?php
/**
 * User: CaryYe , 29/07/2017 3:26 PM
 */
namespace app\models;

class ScheduleGroups extends CActiveRecord
{
    // trait , static vars
    use \app\models\StaticVars\ScheduleGroups;

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
}