<?php

/** Computes group standings from teams + their group-phase games. */
final class StandingsCalculator
{
    /**
     * @param array[] $teams
     * @param array[] $games
     * @return array[] standings rows, ranked
     */
    public static function compute(array $teams, array $games): array
    {
        $rows = [];
        foreach ($teams as $team) {
            $rows[$team['id']] = [
                'team' => $team,
                'played' => 0,
                'won' => 0,
                'lost' => 0,
                'points' => 0,
                'sets_for' => 0,
                'sets_against' => 0,
            ];
        }

        foreach ($games as $game) {
            if (!Game::isComplete($game)) {
                continue;
            }
            $aId = (int) $game['team_a_id'];
            $bId = (int) $game['team_b_id'];
            $setsA = (int) $game['sets_a'];
            $setsB = (int) $game['sets_b'];

            if (!isset($rows[$aId]) || !isset($rows[$bId])) {
                continue;
            }

            $rows[$aId]['played']++;
            $rows[$bId]['played']++;
            $rows[$aId]['sets_for'] += $setsA;
            $rows[$aId]['sets_against'] += $setsB;
            $rows[$bId]['sets_for'] += $setsB;
            $rows[$bId]['sets_against'] += $setsA;

            $shutout = min($setsA, $setsB) === 0;
            $winnerPoints = $shutout ? 3 : 2;
            $loserPoints = $shutout ? 0 : 1;
            [$winnerId, $loserId] = $setsA > $setsB ? [$aId, $bId] : [$bId, $aId];

            $rows[$winnerId]['won']++;
            $rows[$winnerId]['points'] += $winnerPoints;
            $rows[$loserId]['lost']++;
            $rows[$loserId]['points'] += $loserPoints;
        }

        $result = array_values($rows);
        usort($result, function (array $x, array $y): int {
            $diffX = $x['sets_for'] - $x['sets_against'];
            $diffY = $y['sets_for'] - $y['sets_against'];
            return $y['points'] <=> $x['points']
                ?: $diffY <=> $diffX
                ?: $y['sets_for'] <=> $x['sets_for']
                ?: strcmp($x['team']['name'], $y['team']['name']);
        });

        foreach ($result as $i => &$row) {
            $row['rank'] = $i + 1;
        }

        return $result;
    }
}
