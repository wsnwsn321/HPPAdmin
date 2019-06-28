<?php
/**
 * Created by PhpStorm.
 * User: CaryYe
 * Date: 14/07/2017
 * Time: 1:04 PM
 */
namespace app\models;

class Opponents extends CActiveRecord
{
    const OPPONENTS_AFTER_SAVE = "after_opponents_save";

    const ORDER_BY_CREDIT = 'opponents.credit DESC';
    const ORDER_BY_SERIAL = 'OpponentStage.serial ASC';
    const ORDER_BY_RANK = 'OpponentStage.rank ASC';

    /* Static vars */
    use \app\models\StaticVars\Opponents;

    /**
     * You can use $this->user to visit instance of User(["id" => $this->user_id])
     */
    use \app\models\GlobalMethods\getUserObj;

    /**
     * You can use $this->contest to visit instance of User(["id" => $this->contest_id])
     */
    use \app\models\GlobalMethods\getContestObj;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return "opponents";
    }

    /**
     * @return array
     */
    public function behaviors()
    {
        $behaviors = parent::behaviors();

        $behaviors["opponents_after_defaults"] = [
            "class" => \app\models\behaviors\OpponentsBehavior::className()
        ];

        return $behaviors;
    }

    /**
     * @inheritdoc
     */
    public function scenarios()
    {
        $scenarios = parent::scenarios();

        $key = array_search("id", $scenarios[self::SCENARIO_CREATE]);
        unset($scenarios[self::SCENARIO_CREATE][$key]);

        return $scenarios;
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        $validates = [
            [["user_id", "contest_id"], "required", "on" => self::SCENARIO_CREATE],
            [["user_id", "contest_id"], "integer", "min" => 1],
            ["user_id", "uniqueWithContestId"]
        ];

        $defaults = parent::onCreate([
            ["created", "default", "value" => date("Y-m-d H:i:s", time())],
        ]);

        $filters = [
            ["dtype", "filter", "filter" => [$this, "dtype"], "on" => self::SCENARIO_CREATE]
        ];

        return array_merge($validates, $defaults, $filters);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getStages()
    {
        return $this->hasMany(
            OpponentStages::className(),
            ["opponent_id" => "id"]
        )->orderBy("stage asc");
    }

    /**
     * @inheritdoc
     */
    public function assignValuesAfterValidate()
    {
        return [
            "card" => $this->user->card_number,
        ];
    }

    /**
     * @param $realname
     * @return void
     */
    public function setRealname($realname)
    {
        $this->fullname = $realname;
    }

    /**
     * @param $number
     * @return void
     */
    public function setMember_no($number)
    {
        $this->number = $number;
    }

    /**
     * @return void
     */
    public function uniqueWithContestId($attribute, $params)
    {
        if(!$this->hasErrors()){
            $op = self::findOne([
                "user_id" => (int) $this->user_id,
                "contest_id" => (int) $this->contest_id
            ]);

            if(!is_null($op)
                && $this->id != $op->id)
                $this->addError ("user_id, contest_id", "The user has been opponent, shall not add again.");
        }
    }

    /**
     * @inheritdoc
     */
    public function afterSave($insert, $changedAttributes)
    {
        if (!$this->hasErrors()
            && $this->scenario === self::SCENARIO_CREATE)
        {
            $this->trigger(self::OPPONENTS_AFTER_SAVE);
        }
        return parent::afterSave($insert, $changedAttributes);
    }

    /**
     * @param $dtype
     * @return string
     */
    public function dtype($dtype)
    {
        return trim($dtype) == Enrollments::$TYPE_SINGLE
            ? "player"
            : Enrollments::$TYPE_SINGLE;
    }

    /**
     * Get all opponents
     * @param $contest
     * @param null $stage
     * @param bool $seedOnly
     * @param string $order
     * @param null $grade
     * @param null $extConditions
     * @param null $joinType
     * @return array
     */
    public function getAllOpponents(
        $contest,
        $stage = null,
        $seedOnly = false,
        $order =self::ORDER_BY_CREDIT,
        $grade = null,
        $extConditions = null,
        $joinType = null
    ) {
        $type = "leftJoin";

        $conditions = [
            "opponents.contest_id" => $contest->id,
            "opponents.is_single_team" => null
        ];

        $joins = [
            "opponent_stages as OpponentStage",
            "opponents.id=OpponentStage.opponent_id"
        ];

        if ($stage) {
            empty($joinType)
                ? $type = $stage->serial == 0 ? "leftJoin" : "innerJoin"
                : $order = "opponents.id asc";

            $conditions["OpponentStage.stage"] = $stage->serial;

            if ($grade) $conditions["OpponentStage.grade"] = $grade;
            if ($seedOnly) $conditions['OpponentStage.is_seed'] = 1;
            if ($extConditions) $conditions = array_merge($conditions, $extConditions);
        }

        is_null($stage) && $order = [];

        // Search result
        $query = self::find()
            ->select("opponents.*")
            ->$type($joins[0], $joins[1])
            ->where($conditions);

        $opponents = $query->orderBy($order)->all();
        return $opponents;
    }

    /**
     * @param $contest
     * @param $stage
     * @param $byGrade
     * @return array
     */
    public function getGroupedOpponents(
        $contest,
        $stage = null,
        $byGrade = false,
        $extConditions = null
    ) {
        $ret = [];
        $opponents = $this->getAllOpponents(
            $contest,
            $stage,
            false,
            self::ORDER_BY_SERIAL,
            $extConditions
        );

        $stageSerial = $stage ? $stage->serial : 0;

        foreach ($opponents as $op) {
            $group = $op->stages[$stageSerial]["group"];
            if ($byGrade) {
                $grade = $op->stages[$stageSerial]["grade"];
                $ret[$grade][$group] = $op;
            } else {
                $ret[$group][] = $op;
            }
        }

        ksort($ret);

        if ($byGrade)
            foreach($ret as $g => & $arr)
                ksort($arr);

        return $ret;
    }
}