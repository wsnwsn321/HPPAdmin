<?php
/**
 * Created by PhpStorm.
 * User: CaryYe
 * Date: 11/07/2017
 * Time: 10:14 AM
 */
namespace app\components\Events;

class AfterContestCreateEvent extends \yii\base\Event
{
    public $rule = [];
    public $stage = [];
    public $game = [];
}