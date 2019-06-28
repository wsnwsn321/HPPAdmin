<?php
/* CaryYe , 01/08/2017 7:35 AM */
namespace app\components\arrange\Opponent;

class RoundRobinOpponent extends AbstractSchemeAwareOpponent
{
    /**
     * Return seed positions and normal positions according to sum of players
     * @param $count
     * @param $seedCount
     * @return array
     */
    protected function resolvePositions($count, $seedCount) {
        if ($seedCount == 0) {
            return array(null, array_keys(array_fill(1, $count, 0)));
        }

        $seedPositions = array_keys(array_fill(1, $seedCount, 0));

        $normalPositions = ($count - $seedCount > 0)
            ?array_keys(array_fill(1 + $seedCount, $count - $seedCount, 0))
            :array();

        return array($seedPositions, $normalPositions);
    }
}