<?php

namespace app\models;

class User extends CActiveRecord implements \yii\web\IdentityInterface
{
    private $profileObj = null;
    private $creditObj= null;

    /** @inheritdoc */
    public function rules()
    {
        $defaults = [
            ["isFollow", "default", "value" => 0]
        ];

        return parent::onCreate($defaults);
    }

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return "users";
    }

    /**
     * @inheritdoc
     */
    public static function findIdentity($id)
    {
        return self::findOne(["id" => (int) $id]);
    }

    /**
     * @inheritdoc
     */
    public static function findIdentityByAccessToken($token, $type = null)
    {
        return null;
    }

    /**
     * @inheritdoc
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @inheritdoc
     */
    public function getAuthKey()
    {
        return $this->authKey;
    }

    /**
     * @inheritdoc
     */
    public function validateAuthKey($authKey)
    {
        return $this->authKey === $authKey;
    }

    /**
     * @return string
     */
    public  function getName()
    {
        if (!$this->profile) return null;
        $name = trim($this->profile->fullname);
        return $name === '' ? $this->username : $name;
    }

    /**
     * @return string|null
     */
    public function getContact()
    {
        if (!$this->profile) return null;
        $mobile = trim($this->profile->mobile);
        return $mobile === '' ? null : $mobile;
    }

    /**
     * @return string|null
     */
    public function getOrganization()
    {
        if (!$this->profile) return null;
        if (isset($this->profile->job)
            && $this->profile->job == "student")
        {
            return trim($this->profile->school);
        }
        return trim($this->profile->company);
    }

    /**
     * @return string
     */
    public function getNickname() {
        if (!$this->profile) return null;
        $name = trim($this->profile->nickname);
        return $name === '' ? $this->username : $name;
    }

    /**
     * @return string|null
     */
    public function getCity()
    {
        if (!$this->profile) return null;
        $city = trim($this->profile->city);
        return $city === '' ? null : $city;
    }

    /**
     * @return boolean
     */
    public function getGender() {
        if (!$this->profile) return null;
        return (int) $this->profile->gender == 1 ? true : false;
    }

    /**
     * @return string
     */
    public function getAvatar($size = 'mid_', $full = false) {
        if (!$this->profile) return null;

        switch (strtolower(trim($this->profile->portrait_image_name))) {
            case "qq":
                $N = ($size == "big_") ? (100) : ($size == "small_" ? 30 : 50);
                return "http://qzapp.qlogo.cn/qzapp"
                    . $this->profile->portrait_image_path
                    . trim($N);
            break;

            case "sina":
                return $this->profile->portrait_image_path;
            break;

            case "wechat":
                return $this->profile->portrait_image_path;
            break;

            case "wx":
                return $this->profile->portrait_image_path;
            break;

            default:
                if (isset($this->profile->portrait_image_path)
                    && !empty($this->profile->portrait_image_path)
                    && isset($this->profile->portrait_image_name))
                {
                    $relative = $this->profile->portrait_image_path
                        . DIRECTORY_SEPARATOR
                        . $size
                        . $this->profile->portrait_image_name;

                    return $full ? "http://www.hpp.cn/" . $relative: $relative;
                }

                $picture =  ($this->profile->gender === null)
                    ? ('img/unknown-gender.png')
                    : (intval($this->profile->gender) === 1
                            ? 'img/boy.gif'
                            : 'img/girl.gif');
                return ($full ? "http://www.hpp.cn/" : '/'). $picture;
            break;
        }
    }

    /**
     * @return string|null
     */
    public function getEmail()
    {
        if (!$this->profile) return null;
        $email = trim($this->profile->email);
        return $email === '' ? null : $email;
    }

    /**
     * @return null|string
     */
    public function getCard_number() {
        if (!$this->profile) return null;
        $cardNumber = trim($this->profile->card_number);
        return $cardNumber === '' ? null : $cardNumber;
    }

    /**
     * @return bool|\yii\db\ActiveRecord
     */
    public function getProfile()
    {
        if (!is_null($this->profileObj)) {
            return $this->profileObj;
        }
        $this->profileObj = Profiles::findOne(["user_id" => $this->id]);
        return is_null($this->profileObj) ? false : $this->profileObj;
    }

    /**
     * @return string|null
     */
    public function getAmount() {
        if (!$this->credit) return null;
        $amount = trim($this->credit->amount);
        return $amount === '' ? null : $amount;
    }

    /**
     * @return string|null
     */
    public function getIs_active()
    {
        if (!$this->credit) return null;
        $is_active = trim($this->credit->is_active);
        return $is_active === '' ? null : $is_active;
    }

    /**
     * @return string|null
     */
    public function getRanking()
    {
        if (!$this->credit) return null;
        $rank = trim($this->credit->ranking);
        return $rank === '' ? null : $rank;
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCredit()
    {
        $data = [];
        return $this->hasOne(Credits::className(), ["user_id" => "id"]);
    }
}
