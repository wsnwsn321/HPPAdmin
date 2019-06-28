<?php
/* User: CaryYe , 04/08/2017 1:04 PM */
namespace app\components\arrange\Group;

class PlayGroup extends \yii\base\Component
{
    public $contest = null;

    public $stage = null;

    public $group = null;

    public $opponents = [];

    /**
     * @return integer
     */
    public function getTotal()
    {
        return $this->group->total;
    }

    /**
     * @param array $opponents
     * @return void
     */
    public function setOpponents($opponents) {
        $this->opponents = $opponents;
    }

    /**
     * @return array
     */
    protected function getSaverPlayerSerialAndIds() {
        $players = array();

        foreach ($this->opponents as $op) {
            $players[$op->stages[$this->stage->serial]->serial] = $op->id;
        }

        return $players;
    }
}