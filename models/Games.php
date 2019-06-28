<?php
/** CaryYe at 05/07/2017 5:14 PM */
namespace app\models;
use \yii\web\ServerErrorHttpException;

class Games extends CActiveRecord
{
    public $primaryKey = ["id"];
    private $retExMsg = [];

    public function scenarios()
    {
        $s = parent::scenarios();
        $s["update"] = [];
        return $s;
    }

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
            [["name", "category", "contact", "phone", "user_id"], "required", "on" => self::SCENARIO_CREATE],
            [["scheduled_start_date", "scheduled_end_date", "enroll_start_date", "enroll_end_date"], "date", "format" => "yyyy-mm-dd"],
            ["number", "match", "pattern" => "/^[A-Za-z0-9]+$/"],
            ["number", "unique", "on" => self::SCENARIO_CREATE],
            ["name", "unique", "on" => self::SCENARIO_CREATE],
            ["name", "string", "length" => [2, 255]],
            ["category", "in", "range" => ["sand", "regular", "gaoxiao", "credit"]],
            ["contact", "string", "length" => [2, 100]],
            ["phone", "string", "length" => [2, 50]],
            ["user_id", "integer"],
            ["region", "integer"],
            ["state", "integer"],
        ];

        $defaults = [
            ["description", "default", "value" => "本次比赛旨在加快推广乒乓球运动，使更多的人热爱和参与这项活动，为更多的球友创造良好的交流平台，以球会友、相互学习、弘扬国球文化，充分体现我参与、我运动、我快乐的积极、健康的体育精神！"],
            ["type", "default", "value" => 3],
            ["placard", "default", "value" => "placard_regular.png"],
            ["status", "default", "value" => "draft"],
            ["sponsor", "default", "value" => "快乐乒乓网"],
            ["cosponsor", "default", "value" => "海贝"],
            ["club_id", "default", "value" => 0],
            ["private", "default", "value" => 0],
            ["sport_id", "default", "value" => 1],
            //["state", "default", "value" => $this->State()],
            ["number", "default", "value" => $this->generateNumber()],
            ["wechatId", "default", "value" => $this->getUserWeChat()],
            ["created", "default", "value" => date("Y-m-d H:i:s")]
        ];

        return array_merge($validates, parent::onCreate($defaults));
    }

    /** @return integer */
    public function getUserWeChat()
    {
        $user = User::findOne(["id" => (int) $this->user_id]);
        if (! is_null($user)) {
            $this->wechatId = $user->wechatId;
            return $user->wechatId;
        }
        return 0;
    }

    /** @inheritdoc */
    public function assignValuesAfterValidate()
    {
        return [
            ["state", "value" => substr($this->region, 0, 2) . "0000"]
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getContests()
    {
        $data = [];
        return $this->hasMany(Contests::className(), ["game_id" => "id"]);
    }

    /**
     * Desc: Generate serial number of Contest.
     * @return void
     */
    public function generateNumber()
    {
        if($this->scenario=='update') return;
        $number =  \app\components\SerialNumber::Obtain("game");
        if (!$number) {
            $msg = $this->retExMsg["generate_number_failed"];
            throw new ServerErrorHttpException($msg["message"], $msg["code"]);
        } else {
            $this->number = $number;
        }
    }

    /** @inheritdoc */
    public function attributeLabels()
    {
        return [
            'id' =>'赛事编号',
            "name" => "赛事名称",
            "contact" => "联系人",
            "scheduled_start_date" => "赛事开始日期",
            "scheduled_end_date" => "赛事结束日期"
        ];
    }
}