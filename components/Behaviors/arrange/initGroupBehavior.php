<?php
/**
 * CaryYe , 27/07/2017 8:15 AM
 */
namespace app\components\Behaviors\arrange;
use app\components\arrange\Engines\ContestEngine;
use app\models\Matches;
use app\models\Groups;
use app\models\ScheduleGroups;

/**
 * This is a behavior
 *
 * Class initGroup
 * @package app\components\Behaviors\arrange
 */
class  initGroupBehavior extends \yii\base\Behavior
{
    /**
     * @return boolean
     */
    public function initGroup($ctrl)
    {
        $amount = (int)\yii::$app->getRequest()->post("amount");
        $total = (int) $this->owner->contest->enroll_count;

        if ($amount === 0) {
            $total < 8 && $amount = 1;
            $total >= 8 && $total <= 11 && $amount = 2;
            $total > 11 && $total <= 15 && $amount = 3;
            $total > 15 && $amount = ceil(($total + 1) / 5);
        }

        $data = [
            "amount" => $amount,
            "seed_type" => "none",
            "contest_id" => (int) $this->owner->contest->id,
            "total" => $total
        ];

        $contest = $this->owner->contest;
        $stage0 = $contest->stages[0];
        switch ($stage0->scheme) {
            case "single_knock_out":
                $tmp_total = ceil($data["total"] / $data["amount"]);
                $i = log($tmp_total, 2);
                $r = round($i, 0);
                if (abs(pow(2, $r) - $tmp_total) > (int)$tmp_total / 2)
                    return false;
                $data["size"] = (int)pow(2, $r);
                break;

            case "single_round_robin":
            default:
                $data["size"] = ceil($data["total"]/$data["amount"]);
                break;
        }

        // Save data for the schedule_groups
        $scheduleGroup = $this->owner->contest->schedule_group
            ? $this->owner->contest->schedule_group
            : new ScheduleGroups();
        $scheduleGroup->setAttributes($data);
        $scheduleGroup->save();

        if ($data["amount"] > $this->owner->contest->enroll_count)
            return false;

        // At least 2 players in each group
        if (!($data["total"] >= $data["amount"] * 2))
            return false;

        // Delete the old data of Matches
        foreach ($this->owner->contest->stages as $Istage) {
            $match_ids = [];
            $matches = Matches::find()->select(["id"])
                ->where([
                    "contest_id" => $this->owner->contest->id,
                    "stage" => $Istage->serial
                ])->all();
            foreach ($matches as $k => $v) array_push($match_ids, $v["id"]);
            Matches::deleteRelateDetails($match_ids, true);
        }

        // Delete the old groups of first stage
        Groups::deleteAll(["stage_id" => $stage0->id]);

        // Partition the first stage according by scheduled args.
        \Yii::createObject("Stage")->partition($contest, $stage0, $data);

        // Grouping players , and assign serial of group for each player
        \Yii::createObject("Opponent")->allocate($contest, $data);

        ContestEngine::refresh();
        return true;
    }
}