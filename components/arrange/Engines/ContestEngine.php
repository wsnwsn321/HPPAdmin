<?php
/**
 * User: CaryYe / 26/07/2017 8:36 AM
 */
namespace app\components\arrange\Engines;

use app\components\Behaviors\arrange\checkEnrollDataIntegrityBehavior;
use app\components\Behaviors\arrange\initGroupBehavior;
use app\components\Behaviors\arrange\initMatchBehavior;
use app\components\Behaviors\arrange\ResetContestBehavior;
use app\models\Contests;

/**
 * This class provide various Contest and it's dependence
 * Class ContestEngine
 * @package app\components\arrange\Engines
 */
class ContestEngine extends \yii\base\component
 {
     // This variable reference to Games.category
     private static $engine = null;

     // This variable output Games.category
    public static $eng = "";

    // Contest
    public $contest = null;

    // trait, loader
    use \app\components\arrange\Engines\ContestEngineLoader;

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            "ResetContest" => ["class" => ResetContestBehavior::className()],
            "initGroup" => ["class" => initGroupBehavior::className()],
            "integrity" => ["class" => checkEnrollDataIntegrityBehavior::className()],
            "initMatch" => ["class" => initMatchBehavior::className()]
        ];
    }

    /**
     * @param $contestId
     * @return \Object
     */
    public static function startup($contestId)
    {
        if (is_null(self::$engine)) {
            $contest = Contests::find()
                ->where(["contests.id" => (int) $contestId])
                ->innerjoinWith("game", true)
                ->one();

            if (is_null($contest)) return null;
            if (is_null($contest->game)) return null;

            $ns = '\\'.__NAMESPACE__.'\\';
            $eng = self::$eng = ucfirst($contest->category);

            // The following codes using DI Container
            $sns = str_replace("Engines", "Stage", $ns);
            \Yii::$container->setSingleton("Stage", $sns.$eng."Stage");

            $ons = str_replace("Engines", "Opponent", $ns);
            \Yii::$container->setSingleton("Opponent", $ons.$eng."Opponent");

            $fns = str_replace("Engines\\", "", $ns);
            \Yii::$container->setSingleton("ScheduleFacade", $fns."ScheduleFacade");

            $class = new \ReflectionClass($ns.$eng."ContestEngine");
            self::$engine = $class->newInstanceArgs([["contest" => $contest]]);
        }

        return self::$engine;
    }

    /**
     * Refresh contest and it's contains data.
     *
     * @return void
     */
    public static function refresh()
    {
        if (!is_null(self::$engine)) {
            $contest = Contests::find()
                ->where(["contests.id" => (int) self::$engine->contest->id])
                ->innerjoinWith("game", true)
                ->one();

            self::$engine->contest = $contest;
        }
    }
 }