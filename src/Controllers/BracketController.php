<?php

final class BracketController
{
    public static function current(array $params): void
    {
        $season = Season::current();
        if ($season === null) {
            render('no-season');
            return;
        }
        self::renderBracket((int) $season['id']);
    }

    public static function forSeason(array $params): void
    {
        $season = Season::find((int) $params['id']);
        if ($season === null) {
            http_response_code(404);
            render('404');
            return;
        }
        self::renderBracket((int) $season['id']);
    }

    private static function renderBracket(int $seasonId): void
    {
        $season = Season::find($seasonId);
        $bracketGames = Game::bracketGames($seasonId);

        $teamsById = [];
        foreach ($bracketGames as $g) {
            foreach (['team_a_id', 'team_b_id'] as $key) {
                $id = $g[$key];
                if ($id !== null && !isset($teamsById[$id])) {
                    $t = Team::find((int) $id);
                    if ($t !== null) {
                        $teamsById[$id] = $t;
                    }
                }
            }
        }

        $groupData = [];
        foreach (Season::groups($seasonId) as $group) {
            $teams = Team::byGroup((int) $group['id']);
            $games = Game::forGroup((int) $group['id']);
            $groupData[] = [
                'group' => $group,
                'standings' => StandingsCalculator::compute($teams, $games),
                'anyPlayed' => count(array_filter($games, fn ($g) => Game::isComplete($g))) > 0,
            ];
        }

        render('bracket', [
            'season' => $season,
            'groupData' => $groupData,
            'phases' => BracketService::byPhase($bracketGames),
            'teamsById' => $teamsById,
            'viewerTeam' => TeamAuth::current(),
            'admin' => AdminAuth::current(),
        ]);
    }
}
