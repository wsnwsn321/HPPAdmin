<?php
/* CaryYe 2018/4/17 1:11 PM */
namespace app\models;
use app\components\SerialNumber;

class WechatUsers extends CActiveRecord
{
    public $afterFlag = true;

    /** @return \yii\db\ActiveQuery */
    public function getUser()
    {
        return $this->hasOne(User::className(), ["id" => "userId"]);
    }

    /** @inheritdoc */
    public function rules()
    {
        $defaults = [
            ["isFollow", "default", "value" => 0]
        ];

        return parent::onCreate($defaults);
    }

    /**
     * @param $s
     * @return void
     */
    public function setSubscribe($s)
    {
        $this->isFollow = (int) $s;
    }

    /** @return int */
    public function getSubscribe()
    {
        return (int) $this->isFollow;
    }

    /** @inheritdoc */
    public function beforeValidate()
    {
        if (isset($this->privilege)
            && is_array($this->privilege))
        {
            $this->privilege = json_encode($this->privilege);
        }
        return true;
    }

    /** @inheritdoc */
    public function afterSave($insert, $changedAttributes)
    {
        parent::afterSave($insert, $changedAttributes);
        if (! $this->afterFlag) return;

        $weChat = \yii::createObject("weChat")->obj();
        $user = User::findOne([
            "username" => $this->openid,
            "wechatId" => $weChat->id
        ]);

        if (is_null($user)) {
            $user = new User(["scenario" => User::SCENARIO_CREATE]);
            $user->setAttribute(
                "password",
                \yii::$app->getSecurity()->generateRandomString()
            );
        }
        $user->setAttributes([
            "isWX" => 1,
            "wechatId" => $weChat->id,
            "isFollow" => $this->isFollow,
            "username" => $this->openid,
            "number" => SerialNumber::Obtain("number")
        ]);
        $user->save();

        $profile = Profiles::findOne(["user_id" => $user->id]);
        if (is_null($profile))
            $profile = new Profiles(["scenario" => Profiles::SCENARIO_CREATE]);
        $profile->setAttributes([
            "user_id" => $user->id,
            "firstname" => mb_substr($this->nickname, 0, 1),
            "lastname" => mb_substr($this->nickname, 1, mb_strlen($this->nickname)-1),
            "gender" => (int) $this->sex === 2 ? 0 :1,
            "nickname" => $this->nickname,
            //"fullname" => $this->nickname,
            "portrait_image_path" => $this->headimgurl,
            "portrait_image_name" => "wx"
        ]);
        $profile->save();

        $this->afterFlag = false;
        $this->userId = $user->id;
        $this->save();
    }
}