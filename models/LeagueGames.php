<?php
/** User: CaryYe , 13/02/2018 9:56 AM */
namespace app\models;
use yii\db\ActiveRecord;

class LeagueGames extends ActiveRecord
{
    /** @return integer */
    public function getAlbumId()
    {
        return $this->album_id;
    }

    /**
     * @param $v
     * @return void
     */
    public function setAlbumId($v)
    {
        $this->album_id = $v;
    }
}