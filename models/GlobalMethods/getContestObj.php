<?php
/**
 * Created by PhpStorm.
 * User: CaryYe
 * Date: 14/07/2017
 * Time: 3:31 PM
 */
namespace app\models\GlobalMethods;

use app\models\Contests;

trait getContestObj
{
    /**
     * Similar with `contest_id` Reference Contests.id
     */
    private $contestObj = null;

    /**
     * @return bool|\yii\db\ActiveRecord
     */
    public function getContest()
    {
        if (!is_null($this->contestObj)) {
            return $this->contestObj;
        }
        $this->contestObj = Contests::findOne(["id" => (int) $this->contest_id]);
        return  is_null($this->contestObj) ? false : $this->contestObj;
    }
}