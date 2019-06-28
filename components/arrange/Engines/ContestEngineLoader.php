<?php
/** CaryYe At 31/07/2017 6:02 PM */
namespace app\components\arrange\Engines;

trait ContestEngineLoader
{
    // Single mode
    private static $_objects = [];

    private static $_MAP = array(
        "single_knock_out" => "KnockOut",
        "single_round_robin" => "RoundRobin"
    );

    /**
     * @param $name
     * @param null $scheme
     * @return \Object|null
     */
    public static function loader($name, $scheme = null)
    {
        $key = md5(trim($name).trim($scheme));
        $ns = '\\'.str_replace("Engines", $name,__NAMESPACE__).'\\';

        if (!isset(self::$_objects[$key])) {
            $class = is_null($scheme)
                ? $ns.self::$eng.$name
                : $ns.self::$_MAP[$scheme].$name;

            self::$_objects[$key] = class_exists($class) ? new $class() : null;
        }

        return self::$_objects[$key];
    }
}