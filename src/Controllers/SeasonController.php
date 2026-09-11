<?php

final class SeasonController
{
    public static function home(array $params): void
    {
        $season = Season::current();
        if ($season === null) {
            render('no-season');
            return;
        }
        self::renderDashboard($season);
    }

    public static function show(array $params): void
    {
        $season = Season::find((int) $params['id']);
        if ($season === null) {
            http_response_code(404);
            render('404');
            return;
        }
        self::renderDashboard($season);
    }

    private static function renderDashboard(array $season): void
    {
        $groups = Season::groups((int) $season['id']);
        $groupData = [];
        foreach ($groups as $group) {
            $teams = Team::byGroup((int) $group['id']);
            $games = Game::forGroup((int) $group['id']);
            $groupData[] = [
                'group' => $group,
                'standings' => StandingsCalculator::compute($teams, $games),
                'gamesTotal' => count($games),
                'gamesPlayed' => count(array_filter($games, fn ($g) => Game::isComplete($g))),
            ];
        }

        render('season', [
            'season' => $season,
            'groupData' => $groupData,
            'viewerTeam' => TeamAuth::current(),
            'admin' => AdminAuth::current(),
        ]);
    }
}
