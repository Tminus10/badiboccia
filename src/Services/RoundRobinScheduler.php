<?php

/**
 * Generates a single round-robin fixture list: every team plays every other team exactly once.
 *
 * Same order as the paper scorecards hung up at the Badi: with the teams numbered 1..n, first
 * all neighbours play (1-2, 3-4, 5-1, 2-3, 4-5), then teams two apart (1-3, 2-4, 3-5, 4-1, 5-2),
 * and so on. Within each round, a game is skipped for now if it shares a team with the game
 * just before it, so nobody has two games in a row while the list is read top to bottom.
 */
final class RoundRobinScheduler
{
    /** @param int[] $teamIds @return array<int, array{0:int,1:int}> */
    public static function generate(array $teamIds): array
    {
        $teamIds = array_values($teamIds);
        $count = count($teamIds);
        $fixtures = [];
        $previous = [];
        for ($distance = 1; $distance <= intdiv($count, 2); $distance++) {
            // With an even number of teams, the pairs exactly half-way around come up twice -- keep one.
            $starts = $distance * 2 === $count ? $distance : $count;
            $round = [];
            for ($i = 0; $i < $starts; $i++) {
                $round[] = [$teamIds[$i], $teamIds[($i + $distance) % $count]];
            }

            while ($round) {
                $pick = 0;
                foreach ($round as $index => $game) {
                    if (!array_intersect($game, $previous)) {
                        $pick = $index;
                        break;
                    }
                }
                $previous = $round[$pick];
                $fixtures[] = $previous;
                array_splice($round, $pick, 1);
            }
        }
        return $fixtures;
    }
}
