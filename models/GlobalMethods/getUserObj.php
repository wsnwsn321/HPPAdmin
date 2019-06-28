<?php
/**
 * Created by PhpStorm.
 * User: CaryYe
 * Date: 14/07/2017
 * Time: 3:29 PM
 */
namespace app\models\GlobalMethods;

use app\models\User;

trait getUserObj
{
    /**
     * Similar with `user_id` Reference User.id
     */
    private $userObj = null;

    /**
     * @return bool|\yii\db\ActiveRecord
     */
    public function getUser()
    {
        if (!is_null($this->userObj)) {
            return $this->userObj;
        }
        $this->userObj = User::findOne(["id" => (int) $this->user_id]);
        return  is_null($this->userObj) ? false : $this->userObj;
    }
}