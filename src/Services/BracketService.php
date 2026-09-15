<?php

/** Helpers for the knockout bracket admin assignment screen. */
final class BracketService
{
    /** Groups bracket games by phase for display: ['qf' => [...], 'sf' => [...], 'final' => [...], 'third' => [...]] */
    public static function byPhase(array $bracketGames): array
    {
        $out = ['qf' => [], 'sf' => [], 'final' => [], 'third' => []];
        foreach ($bracketGames as $game) {
            $out[$game['phase']][] = $game;
        }
        return $out;
    }

    /**
     * The final's champion/runner-up plus the third-place game's winner, once decided --
     * null until the final is complete. The 'third' entry stays null until that game is
     * complete too, even though champion/runner-up are already known.
     */
    public static function podium(array $bracketGames, array $teamsById): ?array
    {
        $final = null;
        $third = null;
        foreach ($bracketGames as $game) {
            if ($game['phase'] === 'final') {
                $final = $game;
            } elseif ($game['phase'] === 'third') {
                $third = $game;
            }
        }

        if ($final === null) {
            return null;
        }
        $championId = Game::winnerTeamId($final);
        $runnerUpId = Game::loserTeamId($final);
        if ($championId === null || $runnerUpId === null) {
            return null;
        }

        $thirdId = $third !== null ? Game::winnerTeamId($third) : null;

        return [
            'champion' => $teamsById[$championId] ?? null,
            'runnerUp' => $teamsById[$runnerUpId] ?? null,
            'third' => $thirdId !== null ? ($teamsById[$thirdId] ?? null) : null,
        ];
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
