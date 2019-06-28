<?php
/* CaryYe , 02/08/2017 1:17 PM */
namespace app\models;

class ScheduleFactorCollection extends CActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return "schedule_factor_collections";
    }

    /**
     * @param string $scheme
     * @author Songlin
     * @return array (group_round => match_rounds)
     */
    public function getMatchRounds($scheme, $numRound = 1) {
        if ($scheme == Schemes::$SINGLE_ROUND_ROBIN) {
            return array_fill(0, $numRound, (int) $this->match_round_count);
        } else {

            $matchRounds = array_fill(
                0,
                $numRound,
                (int) $this->knockout_round_count
            );

            if ($this->final_round_count) {
                $matchRounds["final"] = (int) $this->final_round_count;
            }

            return $matchRounds;
        }
    }
}