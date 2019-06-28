<?php
/**
 * Created by PhpStorm.
 * User: CaryYe
 * Date: 12/07/2017
 * Time: 5:43 PM
 */
namespace app\models\StaticVars;

trait Enrollments
{
    public static $STATUS_CREATED = 'created';
    public static $STATUS_APPROVE = 'approve';
    public static $STATUS_REJECT = 'reject';
    public static $STATUS_CANCELED = 'canceled';
    public static $STATUS_NOTENROLL = 'notenroll';

    public static $TYPE_SINGLE = 'single';
    public static $TYPE_TEAM = 'team';
    public static $TYPE_DOUBLE = 'double';
    public static $TYPE_SINGLE_TEAM = 'single_team';

    public static $ETYPE_NORMAL = 'normal';
    public static $ETYPE_FAST = 'fast';    // 快速报名
    public static $ETYPE_BATCH = 'batch';    // 批量报名
    public static $ETYPE_APP = 'app';        // APP报名
}