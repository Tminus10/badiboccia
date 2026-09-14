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
        $enrollment = Team::currentEnrollment((int) $team['id']);
        $season = $enrollment !== null ? Season::find((int) $enrollment['season_id']) : null;
        $group = $enrollment !== null ? TeamGroup::find((int) $enrollment['group_id']) : null;
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

        // A team's games can span multiple seasons; look up each game's own season/group
        // (not just the "current" one above) so the fixture list can label them correctly.
        $seasonsById = [];
        $groupsById = [];
        foreach ($games as $g) {
            $sid = (int) $g['season_id'];
            if (!isset($seasonsById[$sid])) {
                $seasonsById[$sid] = Season::find($sid);
            }
            if ($g['group_id'] !== null && !isset($groupsById[$g['group_id']])) {
                $groupsById[$g['group_id']] = TeamGroup::find((int) $g['group_id']);
            }
        }

        render('team', [
            'season' => $season,
            'group' => $group,
            'targetTeam' => $team,
            'games' => $games,
            'teamsById' => $teamsById,
            'seasonsById' => $seasonsById,
            'groupsById' => $groupsById,
            'viewerTeam' => TeamAuth::current(),
            'admin' => AdminAuth::current(),
        ]);
    }
}
