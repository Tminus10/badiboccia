<?php

/** Helpers for the knockout bracket admin assignment screen. */
final class BracketService
{
    /** Groups bracket games by phase for display: ['qf' => [...], 'sf' => [...], 'final' => [...]] */
    public static function byPhase(array $bracketGames): array
    {
        $out = ['qf' => [], 'sf' => [], 'final' => []];
        foreach ($bracketGames as $game) {
            $out[$game['phase']][] = $game;
        }
        return $out;
    }

    /** All teams in a season, grouped by their group name, with current-standing rank for reference. */
    public static function qualifierCandidates(int $seasonId): array
    {
        $out = [];
        foreach (Season::groups($seasonId) as $group) {
            $teams = Team::byGroup((int) $group['id']);
            $games = Game::forGroup((int) $group['id']);
            $standings = StandingsCalculator::compute($teams, $games);
            $out[$group['name']] = $standings;
        }
        return $out;
    }
}
