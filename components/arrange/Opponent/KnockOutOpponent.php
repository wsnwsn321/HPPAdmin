<?php
/** CaryYe At 11/12/2017 2:10 PM */
namespace app\components\arrange\Opponent;
use app\components\ScheduleAlgorithm;

class KnockOutOpponent extends AbstractSchemeAwareOpponent
{
    /**
     * @param array $opponents
     * @param $stage
     * @param $sort
     * @return mixed
     */
    public function position($opponents, $stage, $sort = null)
    {
        if ($stage->serial == 0) {
            $sort = function ($a, $b)  {
                //return 0 表示不排序
                return 0;
            };
            return parent::position($opponents, $stage, $sort);
        } else {
            $contest = $stage->contest;
            //只获取第二阶段出线的人的排名
            $ranking = $this->getRank($contest, $contest->stages[$stage->serial - 1], true , true);
            $letOutCount = 0;
            foreach($ranking as $group=>$players) {
                foreach($players as $rank => $op) {
                    $letOutCount++;
                }
            }

            $groupModel = ClassRegistry::init("Group");
            $iter = $groupModel->createIterator($contest);
            $iter->seekStage($stage->serial - 1);
            /* @var $group GroupBean */
            $rankScore = 0;
            $scheduledCount = 0;
            foreach ($iter as $group) {
                $rankScore = $group->rank;
                $scheduledCount += $group->rank;
                if(isset($ranking[$group->serial])){
                    $ranking[$group->serial] = array_slice($ranking[$group->serial], 0, $rankScore, true);
                }
            }

            $letOutCount = $letOutCount > $scheduledCount ? $scheduledCount : $letOutCount;//实际需要出线的人数

            $iter->seekStage($stage->serial);
            // 第二阶段只有一个组
            $secondStageGroup = $iter->current();
            $hasPreLevel = $secondStageGroup->hasPreLevel();
            $promotionCount = $secondStageGroup->total;
            $giveupCount = $promotionCount - $letOutCount;

            //抽签工厂，根据不同逻辑返回不同的责任链 by jianping
            //$byes = ScheduleAlgorithm::byePositions($promotionCount, $giveupCount);//轮空位
            //弃权不能算轮空位
            $byes = ScheduleAlgorithm::byePositions($promotionCount);//轮空位
            $nearByes = ScheduleAlgorithm::nearByes($byes);//和轮空位对应的位置,相当于种子位
            $num = ScheduleAlgorithm::determineAppropriateKnockoutTotal($promotionCount);
            $rank = $ranking;
            $positions = array_fill(1, $promotionCount > $num ? $promotionCount : $num , 0);
            $drawClass = DrawFactory::getDraw($rank, $promotionCount, $hasPreLevel);
            $drawClass->instantiate($positions, $byes, $promotionCount)->getProcess()->draw($positions, $rank, $byes, $nearByes);

            $pos2OppIds = array();
            foreach ($positions as $pos => $info) {
                if (is_array($info)) {
                    $pos2OppIds[$pos] = $ranking[$info[0]][$info[1]];
                }
            }
            //获取弃权位
            $giveups = ScheduleAlgorithm::giveupPositions($promotionCount, $letOutCount);//弃权

            //$positions = ScheduleAlgorithm::knockoutPosition($ranking, $promotionCount, $hasPreLevel, $giveupCount);
            $positions = array_flip($pos2OppIds);
            $positions = $this->handleGiveupPosition($promotionCount, $positions, $giveups, $byes);
            foreach ($opponents as $op) {
                $pos = isset($positions[$op->id]) ? $positions[$op->id] : null;
                $op->setSerial($pos, $stage->serial);
            }
        }
    }

    /**
     * 处理弃权位，如果弃权位有人了则调整
     * @param unknown_type $promotionCount
     * @param unknown_type $positions
     * @param unknown_type $giveups
     * @param unknown_type $byes
     * @return multitype:
     */
    private function handleGiveupPosition($promotionCount, $positions, $giveups, $byes){

        $needExchanges = array();
        foreach ($positions as $key=>$pos){
            if (in_array($pos, $giveups) || in_array($pos, $byes)){
                $needExchanges[$pos] = $key;
            }
        }
        if (count($needExchanges)){
            $positions = array_flip($positions);

            for ($i=1; $i<=$promotionCount; $i++){
                if (!in_array($i, $giveups) && !in_array($i, $byes)){
                    $needExchangePos = array_keys($needExchanges);
                    if (!isset($positions[$i])){
                        $currentPos = current($needExchangePos);
                        $positions[$i] = $needExchanges[$currentPos];
                        unset($positions[$currentPos]);
                        unset($needExchanges[$currentPos]);
                    }
                }
                if (count($needExchanges)==0){
                    break;
                }
            }
            ksort($positions);
            $positions = array_flip($positions);
        }
        return $positions;
    }

    /**
     * (non-PHPdoc)
     * @see ISchemeAwareOpponent::score()
     */
    public function score(MatchBean $match, MatchResultBean $result, StageBean $stage) {
        $fields = array();
        foreach ($match->opponents as $op) {
            if($op != null){
                $score = $op->score($stage->serial);
                $lastChange = 0;
                if ($match->result != null && $match->result->isCounted) {
                    $lastChange = $match->result->getWinner() == $op ?  $match->weight : $match->weight / 2;
                }
                $currentChange = $result->getWinner() == $op ?  $match->weight : $match->weight / 2;
                $op->setScore($score - $lastChange + $currentChange, $stage->serial);
                if (! in_array($op->id, $result->giveups)) {
                    $op->setIsGiveup(false, $stage->serial);
                }
                $fields[] = $op;
            }
        }

        $saveResult =  $this->batchSaveOpponent($fields, $stage->serial);
        if($saveResult && isset($result->id)){
            $this->MatchResult = ClassRegistry::init("MatchResult");
            $this->MatchResult->id = $result->id;
            $this->MatchResult->saveField("is_counted",1);
        }

        return $saveResult;
    }

    /**
     * (non-PHPdoc)
     * @see ISchemeAwareOpponent::promote()
     */
    public function promote(ContestBean $contest, StageBean $stage, $group) {
        //本组所有的opponents
        $all = $this->getCachedAllOpponents($contest->id);
        $stageSerial = $stage->serial;
        $opponents = array_filter($all, function($opponent) use ($stageSerial, $group) {
            return ($opponent->group($stageSerial) == $group);
        });

        $rankGroupedOpponents = $this->groupByRank($opponents, $stage);
        $stageGroups = $this->getCachedAllGroups($stage->id);
        $groupBean = $stageGroups[(int) $group];
        $promotionCount = 0;
        $toRemoveList = array();
        foreach ($rankGroupedOpponents as $rank=>$opList) {
            $sameRankCount = count($opList);
            foreach ($opList as $op){
                if ($rank <= $groupBean->rank && $promotionCount < $groupBean->rank && $sameRankCount == 1) {
                    //在group->rank内都出线，除非小组赛全部弃权
                    $giveupCount = $this->getGiveupCount($contest->id, $stage->serial, $op->id);
                    //淘汰赛弃权也需要出线
                    $isGiveup = false;
                    $op->setIsGiveup($isGiveup, $stage->serial);
                    $op->setIsOutlet($isGiveup ? null : true, $stage->serial);
                    $fields[] = $this->preparePromote($contest, $stage, $op);
                    $promotionCount++;
                } else {
                    $op->setIsOutlet(null, $stage->serial);
                    //重新录分，如果原来第二阶段出线，则删除相关信息
                    if(isset($op->stages[$stage->serial+1])){
                        $toRemoveList[] = $op->stages[$stage->serial+1]["id"];
                    }
                    continue;
                }
            }
        }

        $this->OpponentStage->saveMany($fields);
        if(count($toRemoveList)){
            $this->OpponentStage->deleteAll(array("id"=>$toRemoveList));
        }
        return $this->batchSaveOpponent($opponents, $stage->serial);
    }

    /**
     * 根据名次分组，并按照名次从高到低排序
     * @param array $opponents
     * @param StageBean $stage
     */
    private function groupByRank($opponents, StageBean $stage){
        $data = array();
        foreach($opponents as $op){
            $data[$op->rank($stage->serial)][] = $op;
        }
        ksort($data);
        return $data;
    }

    /**
     * (non-PHPdoc)
     * @see ISchemeAwareOpponent::rankGroup()
     * //TODO
     */
    public function rankGroup(ContestBean $contest, StageBean $stage, $group) {
        $conditions["OpponentStage.group"] = $group;
        $order = "OpponentStage.score DESC";
        $opponents = $this->getAllOpponents($contest, $stage, false, $order, null, $conditions);
        $scores = array();
        $i = 0;

        $this->ScheduleFactorCollection = ClassRegistry::init("ScheduleFactorCollection");
        $factors = $this->ScheduleFactorCollection->findByContestId($contest->id);
        $rankingScore = $factors->rankingScope;

        /* @var $op OpponentBean */

        $opponents = $this->formatRanking($opponents, $stage->serial);
        $newopponents = array();
        foreach ($opponents as $rank=>$opps) {
            foreach ($opps as $opp) {
                if(!$opp->isGiveup($stage->serial)){
                    $opp->setRank($rank, $stage->serial);
                }
                else{
                    $opp->setRank(null, $stage->serial);
                }
                $newopponents[] = $opp;
            }
        }
        $this->batchSaveOpponent($newopponents, $stage->serial);
        return $opponents;
    }

    /**
     * 按照score 格式化opponent列表
     * Foramt: array[$rank][] = $data
     * @param unknown_type $opponents
     * @param unknown_type $stageSerial
     * @return Ambigous <multitype:, unknown>
     */
    public function formatRanking($opponents, $stageSerial){
        $newOpponents = array();
        foreach ($opponents as $opp) {
            $newOpponents[$opp->stages[$stageSerial]["score"]][] = $opp;
        }
        krsort($newOpponents);
        $rankOpponents = array();
        $rank = 1;
        $count = 0;
        foreach ($newOpponents as $score=>$opps) {
            $rankOpponents[$rank] = $opps;
            $count = count($opps);
            $rank += $count;
        }
        return $rankOpponents;
    }

    /**
     * (non-PHPdoc)
     * @see AbstractSchemeAwareOpponent::resolvePositions()
     */
    protected function resolvePositions($count, $seedCount)
    {
        $seedPositions = ($seedCount > 0 ? ScheduleAlgorithm::seedPositions($count) : array());
        // 种子选手比实际需要的少
        if ($seedCount < count($seedPositions)) {
            $seedPositions = array_slice($seedPositions, 0, $seedCount);
        }
        $byePositions = ScheduleAlgorithm::byePositions($count);
        $size = ScheduleAlgorithm::determineAppropriateKnockoutTotal($count);
        $total = max($size, $count);
        $normalPositions = array_diff(array_keys(array_fill(1, $total, 0)), $seedPositions, $byePositions);
        return array($seedPositions, $normalPositions);
    }

    /**
     * 获取小组排名
     * @param ContestBean $contest
     * @param StageBean $stage
     * @param bool $singleMode
     * @param bool $onlyLetout 只获取第二阶段出现的人排名
     * @return $singleMode为true时仅返回 array(g=>array(i=>id)),为false时返回 array(g=>array(i=>opponentBean))
     */
    public function getRank($contest, $stage, $singleMode = true, $onlyLetout = false) {
        $all = $this->getGroupedOpponents($contest, $stage);
        $allGrouped = array();
        foreach ($all as $group => $opponents){
            $rankGroupedOpponents = $this->groupByRank($opponents, $stage);
            $allGrouped[$group] = $rankGroupedOpponents;
        }

        $ranking = array();
        foreach ($allGrouped as $group => $opList) {
            foreach($opList as $rank=>$ops){
                foreach($ops as $op){
                    if($onlyLetout){
                        if(($op->isOutlet($stage->serial) === true)){
                            $ranking[$group][$rank] = $singleMode ? $op->id : $op;
                        }
                    }
                    else{
                        $ranking[$group][$rank][] = $singleMode ? $op->id : $op;
                    }
                }
            }
        }
        return $ranking;
    }

    /**
     * 淘汰赛最后一阶段最后一轮比赛弃权则算弃权
     * @param OpponentBean $opponent
     * @param StageBean $stage
     */
    public function determineGiveUp($opponent, $stage){
        $this->Match = ClassRegistry::init("Match");
        $lastMatch = $this->Match->getLastMatch($opponent,$stage);
        if(!empty($lastMatch)){
            foreach($lastMatch->opponents as $opp){
                if(isset($opp) && $opp->id == $opponent->id){
                    $index = $opp->id == $lastMatch->opponent1Id ? 0 : 1;
                    $isGiveUp = $lastMatch->result->isGiveup($index);
                    if($isGiveUp){
                        $opponent->setIsGiveup(1);
                    }
                    else{
                        $opponent->setIsGiveup(null);
                    }
                }
            }
        }
    }
}