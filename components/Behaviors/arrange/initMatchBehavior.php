<?php
/* CaryYe , 02/08/2017 11:23 AM */
namespace app\components\Behaviors\arrange;
use app\components\arrange\Engines\ContestEngine;
use app\models\Groups;
use app\models\ScheduleFactorCollection;

/**
 * This is a behavior
 *
 * Class initGroup
 * @package app\components\Behaviors\arrange
 */
class  initMatchBehavior extends \yii\base\Behavior
{
    /**
     * This function using for schedule match.
     *
     * @param null $ctrl
     * @return boolean
     */
    public function initMatch($ctrl = null)
    {
        $round_count = (int) \yii::$app->request->post("round_count");
        $round_count === 0 && $round_count = 5;
        $skip = true;

        $data = count($this->owner->contest->stages) === 1
            ? [
                "contest_id" => $this->owner->contest->id,
                "match_round_count" => $round_count,                          // 常规赛制
                "knockout_round_count" => $round_count,                       // 淘汰赛制胜
                "final_round_count" => $round_count,                          // 决赛制胜
                "match_point_per_round" => 11,                                 // 每局分制
                "group_circle_algorithm" => "counterclockwise",               // 默认逆时针  //clockwise
                "skip_time_setting" => "on",
            ]
            : [
                "contest_id" => $this->owner->contest->id,
                "group_promotion_count" => 2,                                 // 前几名进入比赛
                "match_round_count" => $round_count,                          // 小组赛胜制
                "knockout_round_count" => $round_count,                       // 淘汰赛制胜
                "final_round_count" => $round_count,                          // 决赛制胜
                "match_point_per_round" => 11,                                 // 每局分制
                "group_circle_algorithm" => "counterclockwise",               // 默认逆时针  //clockwise
                "ranking_scope" => 2,                                        // 二阶段决赛名次
                "skip_time_setting" => "on"
            ];

        // if ($factors->teamMatchScheme != GroupBean::TEAM_SCHEME_MULTI_GROUP)
        $data["round_num"] = 0;
        $data["group_promotion_count"] = 2;

        // Update factor of contest.
        $factor = $this->owner->contest->schedule_factor_collection
            ? $this->owner->contest->schedule_factor_collection
            : new ScheduleFactorCollection();

        $factor->setAttributes($data);
        $factor->save();
        ContestEngine::refresh();

        $scheduled_stage = trim($this->owner->contest->scheduled_stage);

        // Skip time settings
        $this->owner->contest->setAttributes([
            "skip_time_setting" => 1,
            "scheduled_stage" => count($this->owner->contest->stages)-1
        ]);

        // Partition groups for second stage.
        $contest = $this->owner->contest;
        $stages = $contest->stages;
        $stage1 = end($stages);
        if (count($this->owner->contest->stages) > 1) {
            Groups::deleteAll(["stage_id" => $stage1->id]);
            \yii::createObject("Stage")
                ->partition($this->owner->contest, $stage1, $data);
        }

        // If has scheduled at any time past.
        if ($scheduled_stage !== '') {
            // Delete all time setting and second stage setting, generate against table again.
            $contest->clearScheduleData($contest, $skip);
            \Yii::createObject("ScheduleFacade")->schedule($contest);
        } else {
            // Generate against table
            \Yii::createObject("ScheduleFacade")->schedule($contest);
            // If the match need verify, after match start all player no verified will be reject
            /* Code ... */
        }

        return true;
    }
}