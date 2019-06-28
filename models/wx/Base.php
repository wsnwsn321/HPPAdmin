<?php
/** User: CaryYe , 23/03/2018 8:53 AM */
namespace app\models\wx;
use yii\base\Model;

/**
 * Class Menu
 * @package app\models
 */
class Base extends Model
{
    protected $accessToken = null;

    /** @inheritdoc */
    public function init()
    {
        $this->refresh();
        parent::init();
    }

    /** @return string */
    public function obtain()
    {
        return $this->accessToken->obtain();
    }

    /** @return void */
    public function refresh()
    {
        if (is_null($this->accessToken)) {
            $this->accessToken = new AccessToken();
        }
    }
}