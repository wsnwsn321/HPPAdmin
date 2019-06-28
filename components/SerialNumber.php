<?php
/**
 * Created by PhpStorm.
 * User: CaryYe
 * Date: 07/07/2017
 * Time: 9:13 AM
 */
namespace app\components;

use app\models\SequenceGenerators;

class SerialNumber extends \yii\base\Component
{
    private static $GAME_NO_KEY = "game_no";
    private static $CONTEST_NO_KEY = "contest_no";
    private static $COLONY_NO_KEY = "colony_no";
    private static $NUMBER_NO_KEY = "number_no";

    /**
     * Desc: Increment serial number of contest.
     * @return string|boolean
     */
    public static function Obtain($type = "")
    {
        $key = self::switchKey($type);
        $f = ucfirst(substr($key, 0, 1));
        $key === self::$COLONY_NO_KEY && $f = 'M';
        $key === self::$NUMBER_NO_KEY && $f = 'SZ';

        $s = SequenceGenerators::findOne(["seq_name" => $key]);
        if (is_null($s)) return false;
        $s->last_value++;

        return $s->save()
            ? $f.$s->last_value
            : false;
    }

    /**
     * @param string $type
     * @return string $key
     */
    private static function switchKey($type = "")
    {
        switch (strtolower(trim($type))) {
            case "game":
                $key = self::$GAME_NO_KEY;
            break;

            case "colony":
                $key = self::$COLONY_NO_KEY;
            break;

            case "number":
                $key = self::$NUMBER_NO_KEY;
            break;

            case "contest":
            default:
                $key = self::$CONTEST_NO_KEY;
            break;
        }
        return $key;
    }
}