<?php
/**
 * Created by PhpStorm.
 * User: CaryYe
 * Date: 12/07/2017
 * Time: 5:57 PM
 */
namespace app\models\StaticVars;

trait Contests
{
    public static $STATUS_DRAFT = 'draft';
    public static $STATUS_PUBLISHED = 'published';
    public static $STATUS_ENROLLMENT_OPEN = 'open';
    public static $STATUS_ENROLLMENT_CLOSED = 'closed';
    public static $STATUS_STARTED = 'started';
    public static $STATUS_OVER = 'over';
    public static $STATUS_COMPLETED = 'completed';
    public static $STATUS_CANCEL = 'cancel';

    public static $ITERATOR_ROUND = 0;
    public static $ITERATOR_GROUP = 1;

    public static $MODE_TEAM = 'team';
    public static $MODE_SINGLE = 'single';
    public static $MODE_DOUBLE = 'double';
    public static $MODE_SINGLE_TEAM = 'single_team';

    public static $SCHEDULE_CACHE_KEY = 'schedule_contest_';
    public static $CONTEXT_CACHE_KEY = 'engine_contest_';
    public static $RECORD_CACHE_KEY = 'record_contest_';

    public static $CATEGORY_GRADE = 'grade';
    public static $CATEGORY_CREDIT = 'credit';
    public static $CATEGORY_REGULAR = 'regular';
    public static $CATEGORY_CUMULATE = 'cumulate';
    //TODO delete
    public static $CATEGORY_GAOXIAO = 'gaoxiao';

    public static $GENDER_MALE	= 'male';
    public static $GENDER_FEMALE	= 'female';
    public static $GENDER_MIXED	= 'mixed';
    public static $GENDER_NO_LIMITED	= 'nolimited';
}