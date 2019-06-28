<?php
/**
 * Created by PhpStorm.
 * User: CaryYe
 * Date: 10/07/2017
 * Time: 8:54 AM
 */
namespace app\components;

class Rules extends \yii\base\Component
{
    /** return mixed*/
    public function credit($rule, $enrollment)
    {
        $low = (int) $rule["params"]["low"];
        $high = (int) $rule["params"]["high"];
        $high === 0 && $high = 9999999999999;
        $credit = $enrollment->user->credit;
        $credit = is_null($credit) ? 1500 : $credit->amount;

        if ($credit >= $low
            && $credit <= $high)
        {
            return true;
        } else {
            return "积分不满足报名条件!";
        }
    }

    /** return mixed*/
    public function minCreditTimes($rule, $enrollment)
    {
        $low = (int) $rule["params"]["low"];
        $uid = $enrollment->user_id;
        $count = current((new \yii\db\Query())
            ->select(["count(*)"])
            ->from("enrollments")
            ->leftJoin("pingpong_contests",
                "enrollments.contest_id = pingpong_contests.id")
            ->where(["enrollments.user_id" => $uid,
                "pingpong_contests.category" => "credit"])
            ->one());
        return $count >= $low ? true :  "不满足最低参赛 $low 次的要求!";
    }
}