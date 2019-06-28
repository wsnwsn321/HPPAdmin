<?php
/**
 * Created by PhpStorm.
 * User: CaryYe
 * Date: 04/08/2017
 * Time: 1:40 PM
 */
namespace app\models\StaticVars;

trait Matches
{

    public static $TYPE_NORMAL = 0;
    public static $TYPE_BYE = 1;
    public static $TYPE_COMPETITION = 2;

    public static $STATUS_DRAFT = 'draft';
    public static $STATUS_CREATED = 'created';
    public static $STATUS_SCHEDULED = 'scheduled';
    public static $STATUS_ACTIVE = 'active';
    public static $STATUS_READY = 'ready';

    public static $STATUS_GIVEUP_1 = 'giveup_1';
    public static $STATUS_GIVEUP_2 = 'giveup_2';
    public static $STATUS_GIVEUP_BOTH = 'giveup_both';
    public static $STATUS_RECORED = 'recorded';

    public static $PROJECT_MIXDOUBLE = 1; //混双
    public static $PROJECT_MALEDOUBLE = 2; //男双
    public static $PROJECT_FEMALEDOUBLE = 3; //女双
    public static $PROJECT_MALESINGLE = 4; //男单
    public static $PROJECT_FEMALESINGLE = 5; //女单
}