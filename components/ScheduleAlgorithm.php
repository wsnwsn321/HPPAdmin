<?php
/**
 * This algorithm file copied from old project.
 * CaryYe , 31/07/2017 1:00 PM
 */
namespace app\components;

use app\models\Matches;

class ScheduleAlgorithm extends \yii\base\Component
{
    protected $current = 0;
    protected $hasPreLevel = false;
    //是否是决淘汰赛名次的编排
    protected $isCompetitionRank = false;

    protected $callback = null;
    // 总共的轮数
    protected $round = null;
    // 从哪一轮开始编排附加赛
    protected $addonRound = null;

    const ROUND_MATCH_CLOCKWISE = 'clockwise';
    const ROUND_MATCH_COUNTERCLOCKWISE = 'counterclockwise';
    const ROUND_MATCH_BEGER = 'berger';

    //淘汰赛种子位置表
    private static $knockoutSeedTable
        = array( 1,256,129,128,65,192,193,64,
            33,224,161,96,97,160,225,32,
            17,240,145,112,81,176,209,48,
            49,208,177,80,113,144,241,16,
            9,248,137,120,73,184,201,56,
            41,216,169,88,105,152,233,24,
            25,232,153,104,89,168,217,40,
            57,200,185,72,121,136,249,8);

    //淘汰赛轮空位置表
    private static $knockoutByeTable
        = array(	2,255,130,127,66,191,194,63,
            34,223,162,95,98,159,226,31,
            18,239,146,111,82,175,210,47,
            50,207,178,79,114,143,242,15,
            10,247,138,119,74,183,202,55,
            42,215,170,87,106,151,234,23,
            26,231,154,103,90,167,218,39,
            58,199,186,71,122,135,250,7,
            6,251,134,123,70,187,198,59,
            38,219,166,91,102,155,230,27,
            22,235,150,107,86,171,214,43,
            54,203,182,75,118,139,246,11,
            14,243,142,115,78,179,206,51,
            46,211,174,83,110,147,238,19,
            30,227,158,99,94,163,222,35,
            62,195,190,67,126,131,354,3);

    private static $_this;
    private $rankingScope;

    public static function getInstance($new = false) {
        if (! self::$_this || $new) {
            self::$_this = new ScheduleAlgorithm();
        }
        return self::$_this;
    }

    /**
     * ScheduleAlgorithm constructor.
     * @inheritdoc
     */
    public function __construct($config = []) {
        $this->current = 0;
        $this->hasPreLevel = false;
        //是否是决淘汰赛名次的编排
        $this->isCompetitionRank = false;
        $this->callback = null;
        // 总共的轮数
        $this->round = null;
        // 从哪一轮开始编排附加赛
        $this->addonRound = null;

        parent::__construct($config);
    }

    /**
     * 介于size/6和size/2之间最大的2的乘数
     * @param int $groupSize
     * @return int $num
     */
    public static function numSeed($total) {
        $j =  floor(log($total / 6, 2));
        while (pow(2, ++$j) < ceil($total/2)) {
            ;
        }
        return pow(2, --$j);
    }

    /**
     * 给定人数，确定单淘汰轮数，考虑轮空和抢位的情况
     * @param int $total
     */
    public static function determineAppropriateKnockoutTotal($total) {
        $i = log($total, 2);
        $r = round($i, 0);
        if (abs(pow(2, $r) - $total) > (int) $total/2) {
            trigger_error("unreasonable total for single knockout $total", E_USER_WARNING);
        }
        //$r = ($r < 2) ? 2 : $r; //at least 8 players for the seed table to work
        return (int) pow(2, $r);
    }

    /**
     * 根据人数返回淘汰赛种子位
     * @param int $total
     */
    public static function seedPositions($total) {
        $total = ScheduleAlgorithm::determineAppropriateKnockoutTotal($total);
        $seeds = array();
        //查找轮空表
        foreach (self::$knockoutSeedTable as $pos) {
            if ($pos <= $total) {
                $seeds[] = $pos;
            }
        }
        return $seeds;
    }

    /**
     * 根据人数返回轮空位
     * @param int $total
     */
    public static function byePositions($total, $giveupCount = 0) {
        $count = ScheduleAlgorithm::determineAppropriateKnockoutTotal($total);
        if ($count <= $total) {
            return array();
        }
        $byes = array();
        //查找轮空表
        foreach (self::$knockoutByeTable as $pos) {
            if ($pos <= $count) {
                $byes[] = $pos;
            }
            if (count($byes) == $count - $total + $giveupCount) {
                break;
            }
        }
        return $byes;
    }

    /**
     * 返回弃权位
     * @param unknown_type $total
     * @param unknown_type $letOutCount
     * @return multitype:|multitype:number
     */
    public static function giveupPositions($total, $letOutCount) {
        $giveupCount = $total - $letOutCount;
        if ($giveupCount == 0 ) {
            return array();
        }
        $count = ScheduleAlgorithm::determineAppropriateKnockoutTotal($total);

        $giveups = array();
        $byes = array();
        $addiPos = 0;
        if ($total != $count){
            $addiPos = abs($total - $count);
        }

        $giveupCount = $giveupCount + $addiPos;//如果有抢位或轮空
        //查找轮空表
        foreach (self::$knockoutByeTable as $pos) {
            if ($pos <= $count) {
                $giveups[] = $pos;
            }
            if (count($giveups) == $giveupCount) {
                break;
            }
        }
        $giveupOpps = array(); //可以存放弃权的相对位置
        $allOpps = array(); //所有的位置
        for ($i=1; $i<=$count; $i++){
            $allOpps[$i] = $i;
        }
        if ($total != $count){
            $byes = array_slice($giveups, 0, abs($total-$count));
            $giveups = array_slice($giveups, abs($total-$count));
        }

        $needAddi = $total-$letOutCount-count($giveups);
        if ($needAddi > 0){
            foreach ($giveups as $giveupPos){
                $giveupOpps[] = $giveupPos %2 ==0? $giveupPos - 1 : $giveupPos +1;
            }
            $arrDiff = array_diff($allOpps, $byes, $giveups, $giveupOpps);
            $giveups = array_merge($giveups, array_slice($arrDiff, -$needAddi, $needAddi));
        }
        return $giveups;
    }

    /**
     * 线性分配 总是分配相邻的
     * @param array $sorted
     * @param int $count
     * @return array linear allocated
     */
    public static function linearAllocate($sorted, $count) {
        $total = count($sorted);
        $alloc = array();
        $j = 0;
        for ($i = 0; $i < $count; $i++) {
            $len = floor($total / $count) + ($total % $count > $i ? 1 : 0);
            $alloc[$i] = array_slice($sorted, $j, $len);
            $j = $j + $len;
        }
        return $alloc;
    }

    /**
     * 蛇形分配
     * @param array $sorted
     * @param int $count
     * @return array snake allocated
     */
    public static function snakeAllocate($sorted, $count, $eager = false) {
        $backupSorted = $sorted;
        $alloc = array_fill(0, $count, array());
        $total = count($sorted);
        $batchCount = ceil($total/$count);
        $i = 0;
        while ($i < $batchCount) {
            $batchData = array_splice($sorted, 0, $count);
            /**
             * eager mode, makes sure each group gets equal
             */
            if ($eager && count($batchData) < $count) {
                $j = 0;
                $gap = $count - count($batchData);
                while ($j < $gap) {
                    $batchData[] = $backupSorted[$j];
                    $j++;
                }
            }
            if ($i % 2 == 1) {
                $batchData = array_reverse($batchData);
            }
            //last round could lack of data
            $lack = ($i % 2 == 1) ? ($count - count($batchData)) : 0;
            foreach ($batchData as $key => $value) {
                array_push($alloc[$lack + $key], $value);
            }
            $i++;
        }
        return $alloc;
    }

    /**
     * 获取分组的位置号
     * @param unknown_type $total
     * @param unknown_type $count
     * @return multitype:
     */
    public static function snakeByGroup($total, $count) {
        $positions = array();
        $groups = array_fill(0, $count, array());
        $batchCount = ceil($total/$count);

        for ($j=0; $j<$total; $j++){
            $positions[$j] = $j;
        }
        $i = 0;
        while ($i < $batchCount) {
            $batchData = array_splice($positions, 0, $count);
            if ($i % 2 == 1) {
                $batchData = array_reverse($batchData);
            }
            //last round could lack of data
            $lack = ($i % 2 == 1) ? ($count - count($batchData)) : 0;
            foreach ($batchData as $key => $value) {
                array_push($groups[$lack + $key], $value);
            }
            $i++;
        }
        return $groups;
    }

    /**
     * 抽签前2名的固定位置
     * @see http://jira.happysports.com/browse/HPPPHASE-4907
     * @author jianping
     */
    public static function fixedPostions($divisions) {
        $leftDivisions = array_merge($divisions[0], $divisions[2]);
        $fixedPostions = array();
        $consecutiveTimes = 1;
        $nth = 2;
        for ($i = 0;$i < count($leftDivisions);$i++) {
            if ($i == 0 || $i == (count($leftDivisions) - 1)) {
                //第一个和最后一个属于第一名的位置
                $fixedPostions[1][] = $leftDivisions[$i];
                continue;
            }
            $fixedPostions[$nth][] = $leftDivisions[$i];
            $consecutiveTimes ++;
            if ($consecutiveTimes == 3) {
                $consecutiveTimes = 1;
                $nth = ($nth == 1 ? 2 : 1);
            }
        }
        return $fixedPostions;
    }

    /**
     * 抽签3,4名的固定位置
     * @author jianping
     */
    public static function fixedPostions3And4($total, $byes) {
        $divisions = static::dividePosistions($total);
        $rightDivisions = array_merge($divisions[1], $divisions[3]);
        $fixedPostions = array();
        $consecutiveTimes = 1;
        $nth = 3;
        for ($i = 0;$i < count($rightDivisions);$i++) {
            if ($i == 0 || $i == (count($rightDivisions) - 1)) {
                //第一个和最后一个属于第4名的位置
                $fixedPostions[4][] = $rightDivisions[$i];
                continue;
            }
            $fixedPostions[$nth][] = $rightDivisions[$i];
            $consecutiveTimes ++;
            if ($consecutiveTimes == 3) {
                $consecutiveTimes = 1;
                $nth = ($nth == 3 ? 4 : 3);
            }
        }
        foreach ($fixedPostions as $nth => $positions) {
            foreach ($positions as $key =>$pos) {
                if (in_array($pos, $byes)) {
                    unset($fixedPostions[$nth][$key]);
                }
            }
        }
        return $fixedPostions;
    }

    /**
     * 根据传入位置号获得和它对阵的位置号
     * @author jianping
     * @param number $posSerial
     * @return number $pos
     */
    public static function getAgainstPos($posSerial) {
        $againstSerial = $posSerial % 2 == 0 ? $posSerial - 1 : $posSerial + 1;
        return $againstSerial;
    }

    /**
     * 根据人数划分位置分区
     * @param int $total
     * @param array $byes
     * @author dongliang
     */
    public static function dividePosistions($total,$byes = array()) {
        static $_player_count = 0;
        static $_division_cache = array();
        static $byes_cache = array();

        if($_player_count == $total && count($_division_cache) && count($byes) == count($byes_cache)){
            return $_division_cache;
        }
        $tmp = self::determineAppropriateKnockoutTotal($total);
        if ($total != $tmp) {
            //trigger_error("unreasonable total $total to divide positions into 4 halfs", E_USER_WARNING);
            $total = $tmp;
        }

        $size = $total / 4;
        $divisions = array();
        for ($i = 0; $i < $total; $i++) {
            $r = (int)$i/2;
            if(!in_array($i+1,$byes)){
                $divisions[($i % 2 == 0 ? $r % 2 : ($r+1) % 2) + ($r < $size ? 0 : 2)][] = $i+1;
            }
        }
        $_division_cache = $divisions;
        $_player_count = $total;
        $byes_cache = $byes;
        return $divisions;
    }

    /**
     * 根据位置返回分区
     * @param array $divisions   array($div=>array($pos1,$pos2))
     * @author dongliang
     */
    public static function positionMappping($divisions){
        $mapping = array();
        foreach($divisions as $d => $poses){
            foreach($poses as $pos){
                $mapping[$pos] = $d;
            }
        }
        return $mapping;
    }

    /**
     * 根据分区划分，组内排名和轮空位抽签位置
     * @param array $positions
     * @param array $group
     * @param int $nth
     * @param array $byes
     * @author dongliang
     * @author jianping
     */
    public static function halfDivisionDraw(array & $positions, $group, $nth, $byes = array(), & $nearByes, $fixedPosition = false) {
        $divisions = self::dividePosistions(count($positions),$byes);
        $posMapping = self::positionMappping($divisions);
        $fixedPositions = self::fixedPostions($divisions);
        $find = false;
        $div = null; //同组人的分组号
        foreach ($positions as $pos => $info) {
            // this players has drew before, return directly
            if ($info[0] == $group && $info[1] == $nth) {
                return $pos;
            }
            //找到了同组的人
            if ($info[0] == $group && $info[1] == 1) {
                $find = true;
                break;
            }
        }
        if ($find) {
            $div  = $posMapping[$pos];
        }
        $available = array();
        $result = null;
        //第一第二名优先抽固定位
        if ($fixedPosition && ($nth == 1 || $nth == 2)) {
            $available = $fixedPositions[$nth];
            foreach ($positions as $pos => $info) {
                if (in_array($pos, $available) && $info !== 0) {
                    $key = array_search($pos, $available);
                    unset($available[$key]);
                }
            }
            //第二名最好不要和第一名在同一个区
            if ($nth == 2) {
                foreach ($available as $key=>$pos) {
                    if ($div == $posMapping[$pos]) {
                        unset($available[$key]);
                    }
                }
            }
            //固定位没了优先抽左半区
            if (count($available) == 0) {
                $available = array_merge($divisions[0], $divisions[2]);
                foreach ($positions as $pos => $info) {
                    if (in_array($pos, $available) && $info !== 0) {
                        $key = array_search($pos, $available);
                        unset($available[$key]);
                    }
                }
            }
            //如果有位置直接返回结果
            if (count($available)) {
                $result = $available[array_rand($available)];
            }
        }

        if (!$fixedPosition && $nth == 1) {
            //从左半区取
            $available = array_merge($divisions[0], $divisions[2]);
            foreach ($positions as $pos => $info) {
                if (in_array($pos, $available) && $info !== 0) {
                    $key = array_search($pos, $available);
                    unset($available[$key]);
                }
            }
            if (count($available)) {
                $result = $available[array_rand($available)];
            }
        }

        //第一名已经安排好位置后，从第一名的对角区抽取位置
        if ($result == null && isset($div)) {
            $available = isset($divisions[3-$div]) ? $divisions[3-$div] : array();
            $available = array_diff($available, $nearByes);
            foreach ($positions as $pos => $info) {
                if (in_array($pos, $available) && $info !== 0) {
                    $key = array_search($pos, $available);
                    unset($available[$key]);
                }
            }
            if (count($available)) {
                $result = $available[array_rand($available)];
            }
        }

        //第一名对角区找不到位置或者第一名在左半区没有位置了
        if ($result == null) {
            foreach($positions as $pos=>$info){
                if($info == 0 && !in_array($pos,$byes) && !in_array($pos,$nearByes)){
                    $result = $pos;
                    break;
                }
            }
        }

        //实在找不到，再从种子位取
        if($result == null){
            $result = $nearByes[array_rand($nearByes)];
        }

        /* if ($nth == 1 || $nth == 2) {
            //$available = array_merge($divisions[0], $divisions[2]);
            //先从固定位置取
            $available = $fixedPositions[$nth];
        }else {
            $available = isset($divisions[3-$div]) ? $divisions[3-$div] : array();
            $available = array_diff($available, $nearByes);
        } */

        /* if ($div === null) {
            $available = ($nth == 1 ? array_merge($divisions[0], $divisions[2]) : array_merge($divisions[1], $divisions[3]));
        } else {
            $available = isset($divisions[3-$div]) ? $divisions[3-$div] : array();
            $available = array_diff($available, $nearByes);
        } */

        /* foreach ($positions as $pos => $info) {
            if (in_array($pos, $available) && $info !== 0) {
                $key = array_search($pos, $available);
                unset($available[$key]);
            }
        }

        $result = 0;
        if(count($available)  >= 1){
            if($nth == 1){
                $nearByeIndex = array_rand($nearByes);
                $result = count($nearByes) >= 1 ? $nearByes[$nearByeIndex] : $available[array_rand($available)];
            }
            else{
                $result = $available[array_rand($available)];
            }
        } else{
            //第二名如果不能分在左半区，就要先放在第一名的对角区,对角区没位置就算了
            if ($nth == 2) {
                $available = isset($divisions[3-$div]) ? $divisions[3-$div] : array();
                $available = array_diff($available, $nearByes);
                if (count($available)  >= 1) {
                    $result = $available[array_rand($available)];
                }
            }

            //如果没有合适的位置，则自动获取一个未被占用的位置,优先不从种子位取
            if($result == null){
                foreach($positions as $pos=>$info){
                    if($info == 0 && !in_array($pos,$byes) && !in_array($pos,$nearByes)){
                        $result = $pos;
                        break;
                    }
                }
            }

            //实在找不到，再从种子位取
            if($result == null){
                $result = $nearByes[array_rand($nearByes)];
            }
        } */

        $positions[$result] = array($group, $nth, $posMapping[$result]);

        if(in_array($result,$nearByes)){
            $key = array_search($result, $nearByes);
            unset($nearByes[$key]);
        }

        return $result;
    }

    /**
     * 提前分配抢位赛的抽签位置
     * @author jianping
     */
    public static function preLevelDraw(&$positions, &$results, $preLevelPositions, $num, $competitionPlayers, $competitionRanks, $byes){
        $divisions = self::dividePosistions($num,$byes);
        $posMapping = self::positionMappping($divisions);
        foreach ($preLevelPositions as $key => $pos) {
            $player = $competitionPlayers[$key];
            $positions[$pos] = array($competitionRanks[$player][0], $competitionRanks[$player][1], $posMapping[$pos]);
            $results[$pos] = $player;
        }
    }

    /**
     * 轮空位对应的位置，2号位对应1号位,7号位对应8号位
     * @param array $byes
     * @author dongliang
     */
    public static function nearByes($byes){
        $nearPoses = array();
        foreach($byes as $bye){
            $nearPoses[] = $bye % 2 == 0 ? $bye - 1 : $bye + 1;
        }

        return $nearPoses;
    }

    /**
     * 对阵位置分配
     * @param array $rank ($g=>array(uids))
     * @param int $total
     * @param bool $hasPreLevel
     * @param int $giveupCount
     * @return array (pos => uid);
     * @author Dongliang
     */
    public static function knockoutPosition(array $rank, $total, $hasPreLevel, $giveupCount = 0) {
        $byes = ScheduleAlgorithm::byePositions($total, $giveupCount);//轮空位
        $nearByes = self::nearByes($byes);//和轮空位对应的位置,相当于种子位

        $num = self::determineAppropriateKnockoutTotal($total);

        //只有每组出现4人的情况才按照固定位置抽签 by jianping
        $fixedPosition = ($total / count($rank)) == 4;
        //获取所有组内人员,排名靠前的在前面
        $allPlayers = array();
        $rank1EachGroup = array();
        $rank2EachGroup = array();

        $rankStart = key($rank);
        $maxLength = 0;
        foreach ($rank as $g => $userIdList) {
            $maxLength = max($maxLength, count($userIdList));
            //先取出每个组的前两名
            foreach ($userIdList as $rankSerial=>$opId) {
                if ($rankSerial == 1) {
                    $rank1EachGroup[$g][$rankSerial] = $opId;
                }
                if ($rankSerial == 2) {
                    $rank2EachGroup[$g][$rankSerial] = $opId;
                }
            }
        }
        for ($i = 1; $i <= $maxLength; $i++) {
            for ($j = $rankStart; $j <= count($rank); $j++) {
                if (isset($rank[$j][$i])) {
                    $allPlayers[] = $rank[$j][$i];
                }
            }
        }

        //抢位的情况
        if($hasPreLevel){
            //获取需要抢位的位置
            $replacedPoses = array();
            for($i = $num+1; $i <= $total ; $i++){
                $replacedPoses[] = $i;
            }
            //抢位赛出现弃权情况，可能会出现编排人数和抽签人数不等的情况
            $actualCount = $total > count($allPlayers) ? count($allPlayers) : $total;
            //获取排名最后的人员并替换相应位置
            $toReplacePlayers = array_slice($allPlayers,0,$actualCount);
            $drawPlayers = array_slice($toReplacePlayers, 0, $actualCount - count($replacedPoses) , true);
            $competitionPlayers = array_slice($toReplacePlayers, count($drawPlayers) , count($replacedPoses), true);
            shuffle($competitionPlayers);
            $resultPreLevel = array_combine(array_values($replacedPoses), array_values($competitionPlayers));

            //获得抢位位置，直接分配相应的位置
            $preLevelPositions = array();
            foreach (self::$knockoutByeTable as $pos) {
                if ($pos < $num) {
                    $preLevelPositions[] = $pos;
                }
                if (count($preLevelPositions) == abs($num - count($allPlayers))) {
                    break;
                }
            }
            $competitionPlayers = array_slice($toReplacePlayers, count($drawPlayers) - count($replacedPoses), count($replacedPoses), true);
            shuffle($competitionPlayers);

            //获取没有分配过位置的人员排名
            $rankNew = array();

            //抢位人的组和排名
            $competitionRanks = array();
            foreach ($rank as $group => $players) {
                foreach ($players as $nth => $player) {
                    if (in_array($player, $competitionPlayers)) {
                        $competitionRanks[$player] = array($group, $nth);
                        continue;
                    }
                    if(in_array($player,$drawPlayers)) {
                        $rankNew[$group][$nth] = $player;
                    }
                }
            }

            $rank1EachGroup = array();
            $rank2EachGroup = array();
            foreach ($rankNew as $g=>$userIdList) {
                //先取出每个组的前两名
                foreach ($userIdList as $rankSerial=>$opId) {
                    if ($rankSerial == 1) {
                        $rank1EachGroup[$g][$rankSerial] = $opId;
                    }
                    if ($rankSerial == 2) {
                        $rank2EachGroup[$g][$rankSerial] = $opId;
                    }
                }
            }

            //分配未分配过位置的人员位置
            $results = array();
            $positions = array_fill(1, $num, 0);

            //直接分配抢位赛固定位置的人
            self::preLevelDraw($positions, $results, $preLevelPositions, $num, $competitionPlayers, $competitionRanks, $byes);

            //先安排每组的第一名
            foreach ($rank1EachGroup as $gorup => $players) {
                foreach ($players as $nth => $player) {
                    $pos = self::halfDivisionDraw($positions, $group, $nth, $byes, $nearByes, $fixedPosition);
                    $results[$pos] = $player;
                    //从总的里面去掉已经排过位的
                    unset($rank[$group][$nth]);
                }
            }
            //再安排第二名
            foreach ($rank2EachGroup as $gorup => $players) {
                foreach ($players as $nth => $player) {
                    $pos = self::halfDivisionDraw($positions, $group, $nth, $byes, $nearByes, $fixedPosition);
                    $results[$pos] = $player;
                    //从总的里面去掉已经排过位的
                    unset($rank[$group][$nth]);
                }
            }
            foreach ($rankNew as $group => $players) {
                foreach ($players as $nth => $player) {
                    $pos = self::halfDivisionDraw($positions, $group, $nth,$byes,$nearByes, $fixedPosition);
                    $results[$pos] = $player;
                }
            }

            //整理抢位赛的位置分配和正常比赛的位置分配
            $return = array();
            foreach($resultPreLevel as $k=>$v){
                $return[$k] = $v;
            }
            foreach($results as $k=>$v){
                $return[$k] = $v;
            }
            ksort($return);
            return $return;

        }else{
            $results = array();
            $positions = array_fill(1, $total>$num ? $total:$num , 0);
            //先安排每组的第一名
            foreach ($rank1EachGroup as $group => $players) {
                foreach ($players as $nth => $player) {
                    $pos = self::halfDivisionDraw($positions, $group, $nth, $byes, $nearByes, $fixedPosition);
                    $results[$pos] = $player;
                    //从总的里面去掉已经排过位的
                    unset($rank[$group][$nth]);
                }
            }
            //再安排第二名
            foreach ($rank2EachGroup as $group => $players) {
                foreach ($players as $nth => $player) {
                    $pos = self::halfDivisionDraw($positions, $group, $nth, $byes, $nearByes, $fixedPosition);
                    $results[$pos] = $player;
                    //从总的里面去掉已经排过位的
                    unset($rank[$group][$nth]);
                }
            }
            //安排剩下的
            foreach ($rank as $group => $players) {
                foreach ($players as $nth => $player) {
                    $pos = self::halfDivisionDraw($positions, $group, $nth, $byes, $nearByes, $fixedPosition);
                    $results[$pos] = $player;
                }
            }
            return $results;
        }
    }

    /**
     * 淘汰赛编排,（含附加赛）
     * 考虑轮空，抢位
     * @param array $players 淘汰赛序号名单
     * @param int $rankingScope 决出前多少名
     * @param $saveCallback 保存比赛的回调函数
     * @since 2.4
     */
    public function knockoutMatch($numPlayer, $rankingScope = 2, $saver) {
        $this->callback = $saver;
        $total = self::determineAppropriateKnockoutTotal($numPlayer);
        if ($total > 256) {
            trigger_error("knockout players can't exceed 256", E_USER_ERROR);
        }
        if ($rankingScope < 2) $rankingScope = 2;
        if ($rankingScope > $numPlayer) $rankingScope = $numPlayer;

        $byes = array();
        if ($total != $numPlayer) {
            //查找轮空表
            foreach (self::$knockoutByeTable as $pos) {
                if ($pos < $total) {
                    $byes[] = $pos;
                }
                if (count($byes) == abs($total-$numPlayer)) {
                    break;
                }
            }
            $players = array();
            if ($total > $numPlayer) {
                // 轮空
                for ($i = 0; $i < $total; $i++) {
                    $players[$i] = in_array($i + 1, $byes) ? 0 : $i + 1;
                }
            } else if ($total < $numPlayer) {
                // 抢位
                $i = 0;
                while ($i < $numPlayer - $total) {
                    $tmp[] = $total+$i+1;
                    $tmp[] = $byes[$i];
                    $i++;
                }
                $players = array_keys(array_fill(1, $total, 0));
                //先编排抢位赛
                $overflowMatches = $this->preLevelMatch($total, $tmp, ($rankingScope == $numPlayer));
                foreach ($byes as $key => $byePos) {
                    $players[$byePos-1] = array($overflowMatches[$key]['n'], "winner");
                }
            }
        } else {
            $players = array_keys(array_fill(1, $total, 0));
        }
        $this->round = log($total, 2);
        if ($rankingScope == $numPlayer) $rankingScope = $total;
        $this->rankingScope = $rankingScope;
        //附加赛如果决出人次不为2的次幂，进位到整数
        $this->addonRound = $this->round - ceil(log($rankingScope, 2)) + 1;
        $firstRoundMatches = $this->linearSchedule(0, $total, $players);
        $this->roundKnockout(1, $firstRoundMatches, null, null, $total/2);
    }

    /**
     * 抢位赛
     * @param $total 淘汰赛size
     * @param array $tmp players
     * @param bool $rankAll 是否决出全部名次
     */
    private function preLevelMatch($total, $tmp, $rankAll = false) {
        $overflowMatches = $this->linearSchedule(-1, 0, $tmp, null, $total);
        // 抢位赛失败的还要决出名次
        if (count($overflowMatches) > 1 && $rankAll) {
            $losers = self::loser($overflowMatches);
            $numPlayer = count($losers);
            $this->isCompetitionRank = true;
            $this->_players = $losers;
            $this->knockoutMatch($numPlayer, $numPlayer, $this->callback);
            $this->_players = null;
            $this->isCompetitionRank = false;
        }
        return $overflowMatches;
    }

    /**
     * 分组循环
     * @param array $sorted 组
     * @param string $type 轮转方式
     * // TODO cache this data
     */
    public static function roundMatch($numPlayer, $type = self::ROUND_MATCH_COUNTERCLOCKWISE) {
        switch ($type) {
            case self::ROUND_MATCH_CLOCKWISE:
                return self::clockwiseRoundMatch($numPlayer);
            case self::ROUND_MATCH_BEGER:
                return self::bergerRoundMatch($numPlayer);
            case self::ROUND_MATCH_COUNTERCLOCKWISE:
            default:
                return self::counterClockwiseRoundMatch($numPlayer);
        }
    }

    /**
     * 团体赛详细对阵
     * @param string $teamScheme  团体赛赛制
     * @return array();
     * 1:A-X, 2:B-Y, 3:C-Z, 4:A-Y, 5: B-X
     * 9局5胜制 1:A-X, 2:B-Y, 3:C-Z, 4:B-X, 5:A-Z, 6:C-Y, 7:B-Z, 8:C-X, 9:A-Y
     */
    public static function teamMatch($teamScheme){
        switch ($teamScheme) {
            case GroupBean::TEAM_SCHEME_SWAYTHLING:
                return array(array(1, 1), array(2, 2), array(3, 3), array(1, 2), array(2, 1));
                break;
            case GroupBean::TEAM_SCHEME_NINE_FIVE:
                return array(array(1, 1), array(2, 2), array(3, 3), array(2, 1), array(1, 3), array(3, 2), array(2, 3), array(3, 1), array(1, 2));
                break;
            case GroupBean::TEAM_SCHEME_MULTI_GROUP:
                return array(array(1, 1), array(2, 2), array(3, 3), array(4, 4), array(5, 5), array(6, 6), array(7, 7), array(8, 8), array(9, 9));
                break;
            default:
                return array(array(1, 1), array(2, 2), array(3, 3), array(4, 4), array(5, 5));
        }
        return array();
    }
    /**
     * 顺时针轮转
     * @param array $sorted players
     * @param bool if false, 逆时针
     */
    public static function clockwiseRoundMatch($numPlayer, $direction = true) {
        $shiftPostions = self::generateShiftArray($numPlayer, $direction);
        $matchups = array();
        for ($numRound = count($shiftPostions), $i = 0; $i < $numRound; $i++) {
            if ($i >= 1) {
                //start to shift from 2nd iteration, put 1st to last
                array_push($shiftPostions, array_shift($shiftPostions));
            }
            $matchups[$i] = self::generateRoundMatchup(array_merge(array(1), $shiftPostions));
        }
        return $matchups;
    }

    /**
     * 逆时针轮转
     * @param array $sorted players
     */
    public static function counterClockwiseRoundMatch($numPlayer) {
        return self::clockwiseRoundMatch($numPlayer, false);
    }

    /**
     * 贝格尔轮转
     * @param array $sorted players
     */
    public static function bergerRoundMatch($numPlayer) {
        $positions = self::generateShiftArray($numPlayer, false);
        $biggest = array_shift($positions);
        array_unshift($positions, 1);

        $matchups = array();
        $shiftPositions = array();
        // iterate every round
        for ($numRound = (($numPlayer%2==1) ? $numPlayer:$numPlayer-1), $i = 0; $i < $numRound; $i++) {
            $shiftPositions[($i + 1) % 2] = $biggest;
            $j = 0; $l = 0;
            while ($l < count($positions) ) {
                if (! isset($shiftPositions[$j])) {
                    $shiftPositions[$j] = $positions[$l];
                    $l++;
                }
                $j++;
            }
            ksort($shiftPositions);
            $matchups[] = self::generateRoundMatchup($shiftPositions);

            // berger shift
            $fixture = $positions[floor($numRound/2)];
            $k = 0; $l = $fixture;
            while ($l > 0) {
                $positions[$k++] = $l--;
            }
            $l = $numRound;
            while ($l > $fixture ) {
                $positions[$k++] = $l--;
            }
            $shiftPositions = array();
        }
        return $matchups;
    }

    /**
     * 生成将要轮转的数组
     * @param int $numPlayer
     * @param bool $direction 顺序，或者逆序
     * @return array $poistions
     */
    private static function generateShiftArray($numPlayer, $order = true) {
        $issetBye = ($numPlayer % 2 == 1) ? true : false;
        $numRound = $issetBye ? $numPlayer : $numPlayer - 1;
        // 1 is settled, shift others
        $shiftPostions = array_keys(array_fill(2, $numRound, 0));
        if ($issetBye) {
            // Odd players, last one set to 0 for bye
            $shiftPostions[count($shiftPostions) -1 ] = 0;
        }
        return ($order) ? $shiftPostions : array_reverse($shiftPostions);
    }

    /**
     * @param array $roundArray
     * @return array matchup per round
     */
    private static function generateRoundMatchup($roundArray) {
        $m = 0;
        $matchup[$m++] = array($roundArray[0], $roundArray[1]);
        $i = 0; $count = count($roundArray);
        while ($i < $count/2 - 1) {
            $matchup[$m++] = array($roundArray[$count-1-$i], $roundArray[2+$i]);
            $i++;
        }
        return $matchup;
    }

    /**
     * 按轮编排淘汰赛，含附加赛
     * @param int $i 淘汰赛轮数
     * @param array $lmatches 上一轮的全部比赛
     * @param IMatchSaverCallback custom function for save match
     * @param bool or null $isAddon 是否是附加赛
     * @param int $rankBase 附加赛的排名基数
     */
    private function roundKnockout($i, $lmatches, $isAddon = false, $rankBase = null, $weight) {
        if ($i >= $this->round) return; // 递归结束条件

        //默认本轮比赛 胜者组
        $winnerMatches = $this->linearSchedule($i, $weight, self::winner($lmatches),
            ($isAddon === true ? $i - $this->addonRound : null), ($isAddon === true ? $rankBase : null));
        $this->roundKnockout($i + 1, $winnerMatches, ($isAddon === true), ($isAddon === true ? $rankBase : null), $weight / 2);
        if ($i >= $this->addonRound) {
            //考虑编排附加赛,败者组
            if ($rankBase === null) {
                //第一次遇到附加赛的情况
                //可以这样想：离终点轮数越远，基数越大
                $rankBase = pow(2, $this->round - $i);
            } else {
                $rankBase += count($winnerMatches) * 2;
            }
            if ($rankBase >= $this->rankingScope) return;
            $loserMatches = $this->linearSchedule($i, $weight/2, self::loser($lmatches), $i - $this->addonRound, $rankBase);
            $this->roundKnockout($i + 1, $loserMatches, true, $rankBase, $weight / 4);
        }
    }

    /**
     * 遍历出每场比赛的胜者
     * @param array $matches
     * @return array of string
     */
    private static function winner($matches) {
        $w = array();
        foreach ($matches as $m) {
            $w[] = array($m['n'], 'winner', $m["type"]);
        }
        return $w;
    }

    /**
     * 遍历出每场比赛的负者
     * @param array $matches
     * @return array of string
     */
    private static function loser($matches) {
        $l = array();
        foreach ($matches as $m) {
            $l[] =  array($m->n, 'loser', $m->type);
        }
        return $l;
    }

    /**
     * 编排相邻比赛
     * @param int current $round
     * @param int $w  该场比赛的权重
     * @param array of string $players
     * @param int or null $addLvl  附加赛的轮次， 从0开始，default 为 null
     * @param int rankBase 附加赛的排名基数
     */
    private function linearSchedule($i, $w, $players, $addLvl = null, $rankBase = null) {
        $k = & $this->current;
        if ($this->isCompetitionRank && isset($this->_players) && $i == 0) {
            $players = $this->_players;
        }
        $matches = array();
        $num = count($players);
        for($j = 0; $j < $num; $j+=2) {
            $tmp["p1"] = isset($players[$j]) ? $players[$j] : 0;
            $tmp["p2"] = isset($players[$j+1]) ? $players[$j+1] : 0;
            $tmp['r'] = $i;
            $tmp["addLvl"] = $addLvl;
            $tmp["rankBase"] = ($rankBase == null ? $num : $rankBase);
            $tmp["numPlayer"] = $num;
            $tmp["isCompRank"] = $this->isCompetitionRank;

            if ($tmp['r'] == -1) {
                $tmp["type"] = Matches::$TYPE_COMPETITION;
            } else if ($tmp["p1"] != 0 && $tmp["p2"] != 0) {
                if ($addLvl !== null // 附加赛
                    && is_array($tmp["p1"]) && is_array($tmp["p2"]) // 非第一轮
                    && ($tmp["p1"][1] == 'loser' && $tmp["p2"][1] == 'loser') // 同时是败者组
                ) {
                    // 上一轮两场都轮空
                    if (($tmp["p1"][2] == Matches::$TYPE_BYE && $tmp["p2"][2] == Matches::$TYPE_BYE)) {
                        continue; // 这场比赛不计
                    } else if (($tmp["p1"][2] == Matches::$TYPE_BYE || $tmp["p2"][2] == Matches::$TYPE_BYE)) {
                        $tmp["type"] = Matches::$TYPE_BYE; // 设为轮空
                    } else {
                        $tmp["type"] = Matches::$TYPE_NORMAL;
                    }
                } else {
                    $tmp["type"] = Matches::$TYPE_NORMAL;
                }
            } else {
                $tmp["type"] = Matches::$TYPE_BYE;
            }

            $tmp['n']  = ++$k;
            //权重 weight
            $tmp['w'] = $w;
            $matches[] = $tmp;
        }
        $this->callback->save($matches);
        return $matches;
    }

    /**
     * 针对锦标赛的分级分组
     * @param int $creditNumbers
     * @param int $nonCreditNumbers
     * @param bool $isInput	//是否是手动输入各分级人数
     * @author Yinlong
     * @author Songlin
     */
    public static function generateGroupGrade($creditNumbers, $nonCreditNumbers, $isInput=false){
        $grades = self::gradeRule($creditNumbers, $nonCreditNumbers);
        if (!$isInput){
            return $grades;
        }

        $newGrades = array();
        foreach ($grades as $key=>$g) {
            if (!is_array($g)){
                $newGrades[$key] = $g;
            }
            else {
                //只取分级中的一种情况
                foreach ($g[0] as $subKey=>$sg) {
                    $newGrades[$subKey] = $sg;
                }
            }
        }
        return $newGrades;
    }

    /**
     * 根据有积分的人数，生成可能的分级
     *
     * @param integer $creditNumbers	//有积分的人数
     * @param integer $nonCreditNumbers //没有积分的人数
     *
     */
    //各分级的组合人数定义
    const gradeMembers8 = 8;
    const gradeMembers32 = 32;
    const gradeMembers40 = 40;
    const gradeMembers44 = 44;
    const gradeMembers45 = 45;
    const gradeMembers90 = 90;
    const gradeMembers91 = 91;
    const gradeMembers130 = 130;
    const gradeMembers170 = 170;
    private static function gradeRule($creditNumbers, $nonCreditNumbers){
        $grades = array();
        if ($creditNumbers <= self::gradeMembers44){
            if ($creditNumbers > self::gradeMembers40){
                if ($nonCreditNumbers <self::gradeMembers8){//非积分人数较少的情况
                    $grades[1] = $creditNumbers + $nonCreditNumbers;
                }
                else {
                    $grades[1] = self::gradeMembers40;
                    $grades[2] = $creditNumbers - self::gradeMembers40 + $nonCreditNumbers;
                }
            }
            else {
                if ($creditNumbers + $nonCreditNumbers > self::gradeMembers44 && $creditNumbers >=20){
                    //每组五人后多余的人数
                    $modNum = $creditNumbers % 10;
                    $grades[1] = $creditNumbers - $modNum;
                    $grades[2] = $modNum + $nonCreditNumbers;

                }
                else{
                    $grades[1] = $creditNumbers + $nonCreditNumbers;
                }
            }

        }
        elseif ($creditNumbers<=self::gradeMembers90 && $creditNumbers>=self::gradeMembers45) {
            $grades[1] = self::gradeMembers40;
            $grades = $grades + self::handleLeftGrades(2, $creditNumbers - self::gradeMembers40, $nonCreditNumbers);
        }
        elseif ($creditNumbers<=self::gradeMembers130 && $creditNumbers>=self::gradeMembers91) {
            $grades[1] = self::gradeMembers40;
            $grades[2] = self::gradeMembers40;
            if ($creditNumbers- 2 * self::gradeMembers40 > self::gradeMembers32){
                if ($creditNumbers >= 3 * self::gradeMembers40){
                    $subGrade1 = array(3 => self::gradeMembers40, 4 => $creditNumbers - 3 * self::gradeMembers40 + $nonCreditNumbers);
                    $subGrade2 = array(3 => $creditNumbers - 2 * self::gradeMembers40 + $nonCreditNumbers);
                }
                else {
                    $subGrade1 = self::handleLeftGrades(3, $creditNumbers - 2 * self::gradeMembers40, $nonCreditNumbers);
                    $subGrade2 = array(3=>$creditNumbers - 2 * self::gradeMembers40 + $nonCreditNumbers);
                }
                $grades[3] = array($subGrade1, $subGrade2);
            }
            else {
                $grades = $grades + self::handleLeftGrades(3, $creditNumbers - 2 * self::gradeMembers40, $nonCreditNumbers);
            }
        }
        else {
            $grades[1] = self::gradeMembers40;
            $grades[2] = self::gradeMembers40;
            $div80Val = ($creditNumbers - 2 * self::gradeMembers40)/2 * self::gradeMembers40;
            $div40Val = ($creditNumbers - 2 * self::gradeMembers40)/self::gradeMembers40;
            $mod4oVal = ($creditNumbers - 2 * self::gradeMembers40)%self::gradeMembers40;
            $subGrade1 = array();
            $subGrade2 = array();
            $subGrade3 = array();
            $n = 3;

            //已经分级的人数总和
            $grade1Num = 2 * self::gradeMembers40;
            for ($i=1;$i<=floor($div40Val);$i++){
                $subGrade1[$n] = self::gradeMembers40;
                $grade1Num += self::gradeMembers40;
                $n++;
            }
            $subGrade1 = $subGrade1 + self::handleLeftGrades($n, $creditNumbers - $grade1Num, $nonCreditNumbers);

            $n = 3;
            $grade2Num = 2 * self::gradeMembers40;	//已经分级的人数总和
            for ($i=1;$i<=floor($div40Val)-1;$i++){
                $subGrade2[$n] = self::gradeMembers40;
                $grade2Num += self::gradeMembers40;
                $n++;
            }
            $subGrade2 = $subGrade2 + self::handleLeftGrades($n, $creditNumbers - $grade2Num, $nonCreditNumbers);

            if ($creditNumbers>=self::gradeMembers170){
                $n = 3;
                $grade3Num = 2 * self::gradeMembers40;	//已经分级的人数总和
                for ($i=1;$i<=floor($div40Val)-2;$i++){
                    $subGrade3[$n] = self::gradeMembers40;
                    $grade3Num += self::gradeMembers40;
                    $n++;
                }

                $subGrade3[$n] = 2 * self::gradeMembers40;
                $grade3Num += 2 * self::gradeMembers40;
                $n++;
                $subGrade3 = $subGrade3 + self::handleLeftGrades($n, $creditNumbers - $grade3Num, $nonCreditNumbers);
            }
            $grades[3] = array($subGrade1,$subGrade2,$subGrade3);
        }

        return $grades;
    }

    /**
     * 对正常分级后余下的有积分的和无积分的处理
     * @param $startNum
     * @param $leftCreditNums
     * @param $nonCreditsNums
     */
    private static function handleLeftGrades($startNum, $leftCreditNums, $nonCreditsNums){
        $grades = array();
        if ($leftCreditNums<=self::gradeMembers32){
            $grades[$startNum] = $leftCreditNums + $nonCreditsNums;
        }
        else {
            $grades[$startNum] = $leftCreditNums;
            $grades[$startNum+1] = $nonCreditsNums;
        }
        return $grades;
    }

    /**
     * 获得抢位位置
     * @param number $num
     * @param number $total
     * @return multitype:number
     */
    public static function getPreLevelPositions($num, $total) {
        $preLevelPositions = array();
        foreach (self::$knockoutByeTable as $pos) {
            if ($pos < $num) {
                $preLevelPositions[] = $pos;
            }
            if (count($preLevelPositions) == abs($num - $total)) {
                break;
            }
        }
        return $preLevelPositions;
    }

    /**
     * 给参赛人员分级
     * @param unknown_type $enrolls
     * @param unknown_type $grades
     */
    public static function setEnrollGradeAndSnakeAllocate($enrolls, $grades){
        $currentGradeKey = 0;
        $num = 1;
        $gradeEnrolls = array();	//分级后的参赛人员
        //分级中再采取蛇形排列
        foreach ($enrolls as $key=>$enroll) {
            if ($num<=$grades[$currentGradeKey]){
                $enroll->_grade = $currentGradeKey;
                $enrolls[$key] = $enroll;
                $gradeEnrolls[$currentGradeKey][] = $enroll;
                if ($num == $grades[$currentGradeKey]){
                    $currentGradeKey++;
                    //人数超过分级数组中的值，则后面的都采用一个分级
                    if (!isset($grades[$currentGradeKey]) || $grades[$currentGradeKey] == 'S'){
                        $currentGradeKey--;
                    }
                    $num = 0;
                }
                $num++;
            }
        }

        foreach ($gradeEnrolls as $key=>$enrolls) {

            $groupCount = floor(count($enrolls)/5);
            $gradeEnrolls[$key] = self::snakeAllocate($enrolls, $groupCount);
        }

        return $gradeEnrolls;
    }

    /**
     * 抢位的对阵位置
     * @author jianping
     * @param unknown_type $total
     * @return Ambigous <multitype:, number, unknown>
     */
    public static function getPrelevelAgainst($total) {
        $num = self::determineAppropriateKnockoutTotal($total);
        for($i = $num+1; $i <= $total; $i++){
            $replacedPoses[] = $i;
        }
        $preLevelPositions = ScheduleAlgorithm::getPreLevelPositions($num, $total);
        $against = array();
        foreach ($preLevelPositions as $key => $pos) {
            $against[$pos][] = $pos;
            if (isset($replacedPoses[$key])) {
                $against[$pos][] = $replacedPoses[$key];
            }
        }
        return $against;
    }

    /**
     * FIX JIRA HPPPHASE-4689,根据单位再次分配一次位置，如果对手单位相同，在对手所在半区再找一个不是同一单位的对手
     * @author jianping
     * @param array $positions(position=>opponentId)
     * @param array $companys(opponentId=>company)
     * @param int $giveupCount
     * @return array $positions(position=>opponentId)
     */
    public static function allocateDifferentCompany($positions, $companys, $giveupCount) {
        $total = count($positions);
        $num = self::determineAppropriateKnockoutTotal($total);
        $byes = self::byePositions($total, $giveupCount);//轮空位
        $divisions = self::dividePosistions($total>$num ? $total:$num,$byes);

        for ($i = 1; $i <= $num; $i+=2) {
            $pos1 = $i;
            $pos2 = $i+1;
            //如果轮空，不用比较单位直接跳过
            if (!isset($positions[$pos1]) || !isset($positions[$pos2])) {
                continue;
            }
            //任意一方没有填写单位，不用比较直接跳过
            if (empty($companys[$positions[$pos1]]) || empty($companys[$positions[$pos2]])) {
                continue;
            }
            //如果单位相等，从相同半区随机调换一个对手
            if (strcmp(trim($companys[$positions[$pos1]]), trim($companys[$positions[$pos2]])) == 0) {
                $companyName = trim($companys[$positions[$pos1]]);
                foreach ($divisions as $division) {
                    //先找到$pos2所在的半区
                    if (in_array($pos2, $division)) {
                        $others = array();
                        foreach ($division as $pos) {
                            if ($pos != $pos2) {
                                $others[] = $pos;
                            }
                        }
                        //尽量去找此半区单名名字不同于$pos1的选手，如果找不到，就不调换了
                        while (count($others) > 0) {
                            //随机在此半区取一名不是$pos2的另一名选手
                            $randomKey = array_rand($others);
                            $randomPos = $others[$randomKey];

                            //先算随机选手的对手位置
                            //如果随机的位置为奇数，则对手为随机的位置+1，如果为偶数，则对手为随机的位置-1，例如，随机的位置为3，则对手为4
                            if ($randomPos % 2 == 0) {
                                $randomOpponentPos = $randomPos-1;
                            }else {
                                $randomOpponentPos = $randomPos+1;
                            }

                            //检查所取随机的位置是不是也为相同的单位名称
                            //如果相同，再找下一个随机对手
                            //如果不同，查找成功，对换randomPos和pos2的人
                            if (strcmp(trim($companys[$positions[$randomPos]]), $companyName) == 0) {
                                unset($others[$randomKey]);
                            }else {
                                //pos2的选手换到randomPos后，pos2的单位会不会和randomPos的对手单位相同
                                if (isset($positions[$randomOpponentPos]) && !empty($companys[$positions[$randomOpponentPos]]) && strcmp(trim($companys[$positions[$randomOpponentPos]]), $companyName) == 0) {
                                    unset($others[$randomKey]);
                                }else {
                                    $temp = $positions[$pos2];
                                    $positions[$pos2] = $positions[$randomPos];
                                    $positions[$randomPos] = $temp;
                                    //清空others 跳出while
                                    $others = array();
                                }
                            }
                        }

                    }
                }
            }
        }

        return $positions;
    }
}