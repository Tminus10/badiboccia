<?php

final class GroupController
{
    public static function show(array $params): void
    {
        $group = TeamGroup::find((int) $params['id']);
        if ($group === null) {
            http_response_code(404);
            render('404');
            return;
        }
        $season = Season::find((int) $group['season_id']);
        $teams = Team::byGroup((int) $group['id']);
        $games = Game::forGroup((int) $group['id']);
        $standings = StandingsCalculator::compute($teams, $games);

        $teamsById = [];
        foreach ($teams as $t) {
            $teamsById[$t['id']] = $t;
        }

        render('group', [
            'season' => $season,
            'group' => $group,
            'standings' => $standings,
            'games' => $games,
            'teamsById' => $teamsById,
            'viewerTeam' => TeamAuth::current(),
            'admin' => AdminAuth::current(),
        ]);
    }
}
