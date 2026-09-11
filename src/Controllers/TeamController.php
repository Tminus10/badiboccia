<?php

final class TeamController
{
    public static function show(array $params): void
    {
        $team = Team::find((int) $params['id']);
        if ($team === null) {
            http_response_code(404);
            render('404');
            return;
        }
        $season = Season::find((int) $team['season_id']);
        $group = TeamGroup::find((int) $team['group_id']);
        $games = Game::forTeam((int) $team['id']);

        $teamIds = [];
        foreach ($games as $g) {
            if ($g['team_a_id'] !== null) {
                $teamIds[$g['team_a_id']] = true;
            }
            if ($g['team_b_id'] !== null) {
                $teamIds[$g['team_b_id']] = true;
            }
        }
        $teamsById = [];
        foreach (array_keys($teamIds) as $id) {
            $t = Team::find((int) $id);
            if ($t !== null) {
                $teamsById[$id] = $t;
            }
        }

        render('team', [
            'season' => $season,
            'group' => $group,
            'targetTeam' => $team,
            'games' => $games,
            'teamsById' => $teamsById,
            'viewerTeam' => TeamAuth::current(),
            'admin' => AdminAuth::current(),
        ]);
    }
}
