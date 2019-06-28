<?php
/**
 * CaryYe , 26/07/2017 1:41 PM
 */
namespace app\components\Behaviors\arrange;

use app\components\arrange\Engines\ContestEngine;
use app\components\HttpRequest;
use app\models\Contests;
use app\models\Opponents;
use app\models\Stages;

/**
 * Class ResetContest
 * @package app\components\Behaviors\arrange
 */
class  ResetContestBehavior extends \yii\base\Behavior
{
    /**
     * Rest stages of contest according by http params
     *
     * @return boolean
     */
    public function _resetContest($ctrl)
    {
        $amount = (int) \yii::$app->getRequest()->post("amount");

        if (($amount === 1)
            || ($amount < 2 && $this->owner->contest->enroll_count < 8))
        {
            $ctrl->step = 1;
            return true;
        }

        // Update stage No.1
        $stage0 = $this->owner->contest->stages[0];
        $stage0->setAttributes(["is_group" => 1]);
        if (($r = $stage0->save()) !== true)
            return $stage0->getErrors();

        // Update stage No.2
        $stage1 = count($this->owner->contest->stages) > 1
            ?$this->owner->contest->stages[1]
            : new Stages();

        $data = [
            "contest_id" => $this->owner->contest->id,
            "scheme" => "single_knock_out",
            "is_draw" => '0',
            "is_ranked_circle" => '0',
            //"is_group" => '0',
            "serial" => '1',
            "label" => "第2阶段"
        ]; // 交叉单淘汰

        $this->owner->contest->game->category == Contests::$CATEGORY_CUMULATE
            && $data["is_group"] = '1';

        $stage1->setAttributes($data);
        if (($r = $stage1->save()) !== true)
            return $stage1->getErrors();

        // Refresh contest
        ContestEngine::refresh();
        $ctrl->step = 1;

        return true;
    }
}