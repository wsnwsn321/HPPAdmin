<?php
/**
 * User: CaryYe , 29/07/2017 12:26 PM
 */
namespace app\models;

class Matches extends CActiveRecord
{
    use \app\models\StaticVars\Matches;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return "matches";
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
     * @param $matchIds
     * @param $deleteMatch
     * @return void
     */
    public static function deleteRelateDetails($matchIds, $deleteMatch = false)
    {
        foreach ($matchIds as $k => $matchId)
            self::deleteRelateDetail($matchId, $deleteMatch);
    }

    /**
     * The codes copied from game/Model/Match/Match.php
     *
     * @param $matchId
     * @param $deleteMatch
     * @return void
     */
    public static function deleteRelateDetail($matchId, $deleteMatch = false)
    {
        $matchId = (int) $matchId;

        \yii::$app->db->createCommand("delete from match_result_details where match_result_id in (select id from match_results where match_id = $matchId)")->execute();
        \yii::$app->db->createCommand("delete from match_result_details where match_result_id in (select id from match_results where match_id in (select id from matches where parent_id = $matchId))")->execute();
        \yii::$app->db->createCommand("delete from match_results where match_id = $matchId")->execute();
        \yii::$app->db->createCommand("delete from match_results where match_id in (select id from matches where parent_id = $matchId)")->execute();

        if ($deleteMatch)
            self::deleteAll(["id" => $matchId]);
    }

    /**
     * Delete all match data and modify opponents
     * @param $contest_id
     * @param null $stage_serial
     * @return boolean
     */
    public static function deleteAllMatchData($contest_id, $stage_serial = null)
    {
        $contest_id = (int) $contest_id;
        $cond = is_null($stage_serial) ? '' : " and `stage`=". $stage_serial;

        if (is_null($stage_serial)) {

            $matches = \yii::$app->db->createCommand(
                "SELECT `id` FROM `matches` WHERE `contest_id`=". $contest_id .' '.$cond
            )->queryAll();

            $match_ids = [];
            foreach ($matches as $k => $v) array_push($match_ids, $v["id"]);

            if (!empty($match_ids)) {
                $match_results = \yii::$app->db->createCommand(
                    "SELECT `id` FROM `match_results`"
                    ." WHERE `match_id` in (". implode(',',$match_ids).")"
                )->queryAll();

                $match_result_ids = [];
                foreach($match_results as $k => $v)
                    array_push($match_result_ids, $v["id"]);

                if (!empty($match_result_ids))
                    \yii::$app->db->createCommand(
                        "DELETE FROM `match_result_details` WHERE"
                         ." `match_result_id` in (". implode(',', $match_result_ids). ")"
                    )->execute();

                \Yii::$app->db->createCommand(
                    "DELETE FROM `match_results`"
                    ." WHERE `match`_id in (". implode(',', $match_ids). ")"
                )->execute();
            }

            \yii::$app->db->createCommand("update opponents as o left join opponent_stages as s on o.id=s.opponent_id and o.contest_id=$contest_id 
				and stage=0 set s.is_giveup=null, s.win=null, s.lose=null, s.rank=null, s.score=null, s.calculate=null, s.is_outlet=null, s.win_verify=null, s.lose_verify=null,  s.score_verify=null")->execute();
            \yii::$app->db->createCommand("update opponents o set o.rank=null, o.is_giveup=null where o.contest_id = $contest_id")->execute();

            $opponent_ids = [];
            $opponents = \yii::$app->db->createCommand("SELECT `id` FROM `opponents` WHERE `contest_id`=$contest_id")->queryAll();
            foreach($opponents as $k => $v) $opponent_ids[] = $v["id"];

            if (!empty($opponent_ids))
                \yii::$app->db->createCommand("delete from opponent_stages where stage=1 and opponent_id in (". implode(',', $opponent_ids). ")")->execute();
        }

        return true;
    }

    /**
     * @param $contest
     * @return integer
     */
    public function getCurrentContestSerial($contest) {
        $match = self::find()
            ->where(["contest_id" => $contest->id])
            ->orderBy("number desc")
            ->one();

        if (!is_null($match)) {
            $number = $match->number;
            return intval(preg_replace('/[0]*(\d+)/i', '${1}', substr($number, -5)));
        }
        return 0;
    }

    /**
     * @param mixed string or ContestBean $contest
     * @param int $serial
     * @author songlin
     */
    public static function generateMatchNo($contest, $serial) {
        $number = $contest;
        if ($contest instanceof Contests) {
            $number = $contest->number;
        }
        return 'M' . substr($number, 1) . str_pad($serial, 5, '0', STR_PAD_LEFT);
    }
}