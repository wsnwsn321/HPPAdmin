<?php
/**
 * Created by PhpStorm.
 * User: CaryYe
 * Date: 06/07/2017
 * Time: 12:20 PM
 */
namespace app\models;

use yii\web\ServerErrorHttpException;

class Contests extends CActiveRecord
{
    use \app\models\StaticVars\Contests;
    protected $Ppdata = [];
    protected $retExMsg = [];
    private $PingpongContestsObj = null;

    CONST SCENARIO_PUBLISHED = "verify_published";

    /**
     * @inheritdoc
     */
    public function init()
    {
        $this->retExMsg = \yii::$app->params["retMsg"]["global_exception"];
        parent::init();
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        $validates = [
            [["name", "game_id", "arena", "scheduled_start_date", "scheduled_end_date"], "required", "on" => self::SCENARIO_CREATE],
            ["enroll_count", "required", "message" => "当前比赛尚无人报名,无法开赛!", "on" => self::SCENARIO_PUBLISHED],
            [["scheduled_start_date", "scheduled_end_date", "enroll_start_date", "enroll_end_date"], "date", "format" => "yyyy-mm-dd"],
            ["game_id", "integer"],
            ["arena", "string", "length" => [2,50]],
            ["name", "string", "length" => [2,255]],
            ["enroll_count", "compare", "compareValue" => '2', "operator" => ">=", "message" => "Two players of this contest at least.", "on" => self::SCENARIO_PUBLISHED],
            ["published", "compare", "compareValue" => '1', "operator" => "!=", "message" => "Contest has published!", "on" => self::SCENARIO_PUBLISHED],
            [["scheduled_start_date", "scheduled_end_date"], "compare", "type" => "strtotime", "compareValue" => date("Y-m-d", strtotime("+0 day")), "operator" => ">=", "on" => self::SCENARIO_CREATE],
            [["enroll_start_date", "enroll_end_date"], "compare", "type" => "strtotime", "compareValue" => date("Y-m-d"), "operator" => ">=", "on" => self::SCENARIO_CREATE],
            ["scheduled_end_date", "compare", "compareAttribute" => "scheduled_start_date", "type" => "strtotime", "operator" => ">="]
        ];

        $defaults = [
            [["enroll_verify", "enroll_verify", "enroll_cost"], "default", "value" => 0],
            ["enroll_start_date", "default", "value" => date("Y-m-d", time())],
            ["published", "default", "value" => "0"],
            ["number", "default", "value" => $this->generateNumber()],
            ["sport_id", "default", "value" => 1],
            ["status", "default", "value" => "open"],
            ["created", "default", "value" => date("Y-m-d H:i:s", time())]
        ];

        return array_merge($validates, parent::onCreate($defaults));
    }

    /**
     * @inheritdoc
     */
    public function assignValuesAfterValidate()
    {
        return [
            [
                "enroll_end_date",
                "value" => date("Y-m-d", (strtotime($this->scheduled_start_date)))
            ]
        ];
    }

    /**
     * @inheritdoc
     */
    public function afterValidate()
    {
        if ($this->getScenario() === self::SCENARIO_CREATE) {
            $m = new PingpongContests(["scenario" => self::SCENARIO_CREATE]);
            $m->setAttributes($this->Ppdata);
            $r = $m->validate();
            if ($r !== true) {
                foreach ($m->getErrors() as $k => $e)
                    $this->addError($k, $e);
                return $r;
            }
        }
        return parent::afterValidate();
    }

    /**
     * @inheritdoc
     * @return boolean
     */
    public function afterSave($insert, $changedAttributes)
    {
        if ($insert === true) {
            $this->Ppdata["id"] = $this->id;
            $m = new PingpongContests(["scenario" => self::SCENARIO_CREATE]);
            $m->setAttributes($this->Ppdata);
            if (!$m->save()) return false;
        }

        $game_start = current(self::find()
            ->select(["min(scheduled_start_date) as scheduled_start_date"])
            ->where(["game_id" => $this->game_id])
            ->one()
            ->toArray());
        $game_end = current(self::find()
            ->select(["max(scheduled_end_date) as scheduled_end_date"])
            ->where(["game_id" => $this->game_id])
            ->one()
            ->toArray());

        $game = \app\models\Games::findOne(["id" => $this->game_id]);
        $game->setScenario("update");
        $game->scheduled_start_date = $game_start;
        $game->scheduled_end_date = $game_end;
        $game->save();

        return true;
    }

    /**
     * Desc: Generate serial number of Contest.
     * @return void
     */
    public function generateNumber()
    {
       $number =  \app\components\SerialNumber::Obtain("contest");
       if (!$number) {
           $msg = $this->retExMsg["generate_number_failed"];
           throw new ServerErrorHttpException($msg["message"], $msg["code"]);
       } else {
           $this->number = $number;
       }
    }

    /**
     * @param $v
     * @return void
     */
    public function setCategory($v)
    {
        $this->Ppdata["category"] = $v;
    }

    /**
     * @param $v
     * @return void
     */
    public function setMode($v)
    {
        $this->Ppdata["mode"] = $v;
    }

    /**
     * @param $v
     * @return void
     */
    public function setGender($v)
    {
        $this->Ppdata["gender"] = $v;
    }

    /**
     * @param $v
     * @return void
     */
    public function setMisc($v)
    {
        $this->Ppdata["misc"] = $v;
    }

    /**
     * @param $v
     * @return void
     */
    public function setRegion($v)
    {
        $this->Ppdata["region"] = $v;
    }

    /**
     * @return string|null
     */
    public function getMode()
    {
        if ($this->pingpongcontests)
            return $this->pingpongcontests->mode;
        return null;
    }

    /**
     * @return string|null
     */
    public function getCategory()
    {
        if ($this->pingpongcontests)
            return $this->pingpongcontests->category;
        return null;
    }

    /**
     * @return bool|static
     */
    public function getPingpongContests()
    {
        if (!is_null($this->PingpongContestsObj)) {
            return $this->PingpongContestsObj;
        }

        $this->PingpongContestsObj = PingpongContests::findOne([
            "id" => $this->id
        ]);

        return is_null($this->PingpongContestsObj)
            ? false
            : $this->PingpongContestsObj;
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getStages()
    {
        return $this->hasMany(Stages::className(), ["contest_id" => "id"])
            ->orderBy("serial asc");
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getGame()
    {
        return $this->hasOne(Games::className(), ["id" => "game_id"]);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getSchedule_group()
    {
        return $this->hasOne(ScheduleGroups::className(), ["contest_id" => "id"]);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getSchedule_factor_collection()
    {
        return $this->hasOne(
            ScheduleFactorCollection::className(),
            ["contest_id" => "id"]
        );
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getSchedule_factor_collections()
    {
        return $this->hasMany(
            ScheduleFactorCollection::className(),
            ["contest_id" => "id"]
        );
    }

    /**
     * @return boolean
     */
    public function isReadyForSchedule()
    {
        return ($this->status == Contests::$STATUS_ENROLLMENT_CLOSED
            || $this->status == Contests::$STATUS_STARTED
            || $this->status == Contests::$STATUS_OVER
            || $this->status == Contests::$STATUS_COMPLETED);
    }

    /**
     * Change published status.
     * @return void
     */
    public function changePublishedStatus()
    {
        if ($this->id) {
            $target = $this->published ? 0 : 1;
            $this->setAttribute("published", $target);
            $this->save();
        }
    }

    /**
     * Clear scheduled data.
     * @param $contest
     * @return void
     */
    public function clearScheduleData($contest = null, $skip = false)
    {
        is_null($contest) && $contest = $this;
        $contest->scheduled_stage = null;

        foreach ($contest->stages as $stage) {
            $sql = "update `stages` set"
                . " `start_date`=null, `end_date`=null,`scheduled_group`=null"
                . " where `id`=".$stage->id;
            \yii::$app->db->createCommand($sql)->execute();
        }

        if (!$skip) {
            $sql = "update `contests` set"
                . " `scheduled_stage`=null where `id`=".$contest->id;
            \yii::$app->db->createCommand($sql)->execute();
        }

        // delete scheduled days
        ScheduleDatetimes::deleteAll(["contest_id" => $contest->id]);
        // delete tables
        Venues::deleteAll(["contest_id" => $contest->id]);
    }

    /** @inheritdoc */
    public function attributeLabels()
    {
        return [
            "scheduled_start_date" => "赛事开始时间",
            "scheduled_end_date" => "赛事结束时间",
            "arena" => "地点"
        ];
    }

    public function getEnrollments()
    {
        return $this->hasMany(Enrollments::className(), ["contest_id" => "id"]);
    }
}