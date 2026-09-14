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
        $teamId = (int) $team['id'];

        // By default (no ?season=) a team's page shows only its current/most recent season -
        // matching wherever the visitor navigated from. ?season=<id> scopes it to that specific
        // season instead (used when linking from within that season's own pages), and
        // ?season=all shows the full cross-season history as an explicit opt-in.
        $seasonParam = $_GET['season'] ?? null;
        $showAllSeasons = $seasonParam === 'all';

        if ($showAllSeasons) {
            $season = null;
            $group = null;
            $games = Game::forTeam($teamId);
        } else {
            $enrollment = $seasonParam !== null
                ? Team::enrollmentFor($teamId, (int) $seasonParam)
                : Team::currentEnrollment($teamId);
            $season = $enrollment !== null ? Season::find((int) $enrollment['season_id']) : null;
            $group = $enrollment !== null ? TeamGroup::find((int) $enrollment['group_id']) : null;
            $games = $enrollment !== null ? Game::forTeamInSeason($teamId, (int) $enrollment['season_id']) : [];
        }

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
            'showAllSeasons' => $showAllSeasons,
            'viewerTeam' => TeamAuth::current(),
            'admin' => AdminAuth::current(),
        ]);
    }
}
