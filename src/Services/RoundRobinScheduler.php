<?php

/** Generates a single round-robin fixture list: every team plays every other team exactly once. */
final class RoundRobinScheduler
{
    /** @param int[] $teamIds @return array<int, array{0:int,1:int}> */
    public static function generate(array $teamIds): array
    {
        $fixtures = [];
        $count = count($teamIds);
        for ($i = 0; $i < $count; $i++) {
            for ($j = $i + 1; $j < $count; $j++) {
                $fixtures[] = [$teamIds[$i], $teamIds[$j]];
            }
        }
        return $fixtures;
    }
}
