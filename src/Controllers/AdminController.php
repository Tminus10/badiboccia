<?php

final class AdminController
{
    public static function dashboard(array $params): void
    {
        $admin = AdminAuth::requireLogin();
        render('admin/dashboard', [
            'admin' => $admin,
            'seasons' => Season::all(),
            'admins' => Admin::all(),
        ]);
    }

    public static function seasonManage(array $params): void
    {
        $admin = AdminAuth::requireLogin();
        $season = Season::find((int) $params['id']);
        if ($season === null) {
            http_response_code(404);
            render('404');
            return;
        }

        $groups = Season::groups((int) $season['id']);
        $groupData = [];
        foreach ($groups as $group) {
            $teams = Team::byGroup((int) $group['id']);
            $games = Game::forGroup((int) $group['id']);
            $groupData[] = [
                'group' => $group,
                'teams' => $teams,
                'gamesCount' => count($games),
            ];
        }

        Game::ensureBracketSkeleton((int) $season['id']);
        $bracketGames = Game::bracketGames((int) $season['id']);
        $qfGames = array_values(array_filter($bracketGames, fn ($g) => $g['phase'] === 'qf'));
        $allTeams = Team::bySeason((int) $season['id']);
        $availableTeams = Team::availableForSeason((int) $season['id']);

        render('admin/season', [
            'admin' => $admin,
            'season' => $season,
            'groupData' => $groupData,
            'qfGames' => $qfGames,
            'allTeams' => $allTeams,
            'availableTeams' => $availableTeams,
        ]);
    }

    public static function seasonCreate(array $params): void
    {
        AdminAuth::requireLogin();
        if (!csrf_check()) {
            redirect('/admin');
            return;
        }
        $label = trim((string) ($_POST['label'] ?? ''));
        $year = (int) ($_POST['year'] ?? date('Y'));
        if ($label === '') {
            flash_set('error', 'Bitte einen Namen für die Saison angeben.');
            redirect('/admin');
            return;
        }
        $id = Season::create($label, $year);
        if (!empty($_POST['make_current'])) {
            Season::setCurrent($id);
        }
        flash_set('success', 'Saison "' . $label . '" erstellt.');
        redirect('/admin/season/' . $id);
    }

    public static function seasonActivate(array $params): void
    {
        AdminAuth::requireLogin();
        if (!csrf_check()) {
            redirect('/admin');
            return;
        }
        $id = (int) $params['id'];
        Season::setCurrent($id);
        flash_set('success', 'Saison ist jetzt aktiv.');
        redirect('/admin/season/' . $id);
    }

    public static function seasonDeactivate(array $params): void
    {
        AdminAuth::requireLogin();
        if (!csrf_check()) {
            redirect('/admin');
            return;
        }
        $id = (int) $params['id'];
        Season::deactivate($id);
        flash_set('success', 'Saison ist jetzt archiviert (nicht mehr bearbeitbar).');
        redirect('/admin/season/' . $id);
    }

    public static function seasonUpdate(array $params): void
    {
        AdminAuth::requireLogin();
        $id = (int) $params['id'];
        if (Season::find($id) === null) {
            http_response_code(404);
            render('404');
            return;
        }
        if (!csrf_check()) {
            redirect('/admin/season/' . $id);
            return;
        }

        $label = trim((string) ($_POST['label'] ?? ''));
        $year = (int) ($_POST['year'] ?? 0);

        if ($label === '' || $year === 0) {
            flash_set('error', 'Bitte Bezeichnung und Jahr angeben.');
            redirect('/admin/season/' . $id);
            return;
        }

        Season::update($id, $label, $year);
        flash_set('success', 'Saison aktualisiert.');
        redirect('/admin/season/' . $id);
    }

    public static function teamCreate(array $params): void
    {
        AdminAuth::requireLogin();
        $seasonId = (int) $params['id'];
        if (!csrf_check()) {
            redirect('/admin/season/' . $seasonId);
            return;
        }

        $name = trim((string) ($_POST['name'] ?? ''));
        $player1 = trim((string) ($_POST['player1'] ?? ''));
        $player2 = trim((string) ($_POST['player2'] ?? ''));
        $groupId = (int) ($_POST['group_id'] ?? 0);

        if ($name === '' || $player1 === '' || $player2 === '' || $groupId === 0) {
            flash_set('error', 'Bitte alle Felder ausfüllen.');
            redirect('/admin/season/' . $seasonId);
            return;
        }

        $teamId = Team::createAndEnroll($seasonId, $groupId, $name, $player1, $player2);
        $pin = Team::find($teamId)['pin'];

        flash_set('success', "Team \"$name\" erstellt. PIN für den Login: $pin (bitte dem Team mitteilen).");
        redirect('/admin/season/' . $seasonId);
    }

    /** Enrolls a team that already exists from a previous season into this one. */
    public static function teamEnroll(array $params): void
    {
        AdminAuth::requireLogin();
        $seasonId = (int) $params['id'];
        if (!csrf_check()) {
            redirect('/admin/season/' . $seasonId);
            return;
        }

        $teamId = (int) ($_POST['team_id'] ?? 0);
        $groupId = (int) ($_POST['group_id'] ?? 0);
        $team = Team::find($teamId);

        if ($team === null || $groupId === 0) {
            flash_set('error', 'Bitte ein Team und eine Gruppe wählen.');
            redirect('/admin/season/' . $seasonId);
            return;
        }

        Team::enroll($teamId, $seasonId, $groupId);
        flash_set('success', 'Team "' . $team['name'] . '" zur Saison hinzugefügt.');
        redirect('/admin/season/' . $seasonId);
    }

    public static function teamUpdate(array $params): void
    {
        AdminAuth::requireLogin();
        $teamId = (int) $params['id'];
        $team = Team::find($teamId);
        $seasonId = (int) ($_POST['season_id'] ?? 0);
        if ($team === null) {
            http_response_code(404);
            render('404');
            return;
        }
        if (!csrf_check()) {
            redirect('/admin/season/' . $seasonId);
            return;
        }

        $name = trim((string) ($_POST['name'] ?? ''));
        $player1 = trim((string) ($_POST['player1'] ?? ''));
        $player2 = trim((string) ($_POST['player2'] ?? ''));
        $groupId = (int) ($_POST['group_id'] ?? 0);

        if ($name !== '' && $player1 !== '' && $player2 !== '') {
            Team::update($teamId, $name, $player1, $player2);
            if ($groupId !== 0) {
                Team::updateGroup($teamId, $seasonId, $groupId);
            }
            flash_set('success', 'Team aktualisiert.');
        }

        redirect('/admin/season/' . $seasonId);
    }

    public static function teamPhoto(array $params): void
    {
        AdminAuth::requireLogin();
        $teamId = (int) $params['id'];
        $team = Team::find($teamId);
        $seasonId = (int) ($_POST['season_id'] ?? 0);
        if ($team === null) {
            http_response_code(404);
            render('404');
            return;
        }
        if (!csrf_check()) {
            redirect('/admin/season/' . $seasonId);
            return;
        }

        try {
            $path = PhotoUploader::handle($teamId, $_FILES['photo'] ?? []);
            Team::setPhoto($teamId, $path);
            flash_set('success', 'Foto gespeichert.');
        } catch (RuntimeException $e) {
            flash_set('error', $e->getMessage());
        }

        redirect('/admin/season/' . $seasonId);
    }

    public static function teamPinReset(array $params): void
    {
        AdminAuth::requireLogin();
        $teamId = (int) $params['id'];
        $team = Team::find($teamId);
        $seasonId = (int) ($_POST['season_id'] ?? 0);
        if ($team === null) {
            http_response_code(404);
            render('404');
            return;
        }
        if (!csrf_check()) {
            redirect('/admin/season/' . $seasonId);
            return;
        }

        $pin = str_pad((string) random_int(0, 9999), 4, '0', STR_PAD_LEFT);
        Team::resetPin($teamId, $pin);
        flash_set('success', 'Neuer PIN für ' . $team['name'] . ': ' . $pin);
        redirect('/admin/season/' . $seasonId);
    }

    /** Removes a team from this season only; the team itself (and its history) is kept unless this was its only season. */
    public static function teamDelete(array $params): void
    {
        AdminAuth::requireLogin();
        $teamId = (int) $params['id'];
        $team = Team::find($teamId);
        $seasonId = (int) ($_POST['season_id'] ?? 0);
        if ($team === null) {
            http_response_code(404);
            render('404');
            return;
        }
        if (!csrf_check()) {
            redirect('/admin/season/' . $seasonId);
            return;
        }

        Team::removeFromSeason($teamId, $seasonId);
        flash_set('success', 'Team aus der Saison entfernt.');
        redirect('/admin/season/' . $seasonId);
    }

    public static function fixturesGenerate(array $params): void
    {
        AdminAuth::requireLogin();
        $groupId = (int) $params['id'];
        $group = TeamGroup::find($groupId);
        if ($group === null) {
            http_response_code(404);
            render('404');
            return;
        }
        if (!csrf_check()) {
            redirect('/admin/season/' . $group['season_id']);
            return;
        }

        $existing = Game::forGroup($groupId);
        if (count($existing) > 0) {
            flash_set('error', 'Für diese Gruppe wurden die Spiele bereits erstellt.');
            redirect('/admin/season/' . $group['season_id']);
            return;
        }

        $teams = Team::byGroup($groupId);
        if (count($teams) < 2) {
            flash_set('error', 'Eine Gruppe braucht mindestens 2 Teams.');
            redirect('/admin/season/' . $group['season_id']);
            return;
        }

        $teamIds = array_map(fn ($t) => (int) $t['id'], $teams);
        Game::createGroupFixtures((int) $group['season_id'], $groupId, $teamIds);

        flash_set('success', count(RoundRobinScheduler::generate($teamIds)) . ' Spiele erstellt.');
        redirect('/admin/season/' . $group['season_id']);
    }

    public static function bracketAssign(array $params): void
    {
        AdminAuth::requireLogin();
        $seasonId = (int) $params['id'];
        $season = Season::find($seasonId);
        if ($season === null) {
            http_response_code(404);
            render('404');
            return;
        }
        if (!csrf_check()) {
            redirect('/admin/season/' . $seasonId);
            return;
        }

        Game::ensureBracketSkeleton($seasonId);
        $qfGames = array_values(array_filter(
            Game::bracketGames($seasonId),
            fn ($g) => $g['phase'] === 'qf'
        ));

        foreach ($qfGames as $game) {
            $slotIndex = (int) $game['slot_index'];
            $teamA = $_POST["qf{$slotIndex}_a"] ?? '';
            $teamB = $_POST["qf{$slotIndex}_b"] ?? '';
            Game::setQfTeam((int) $game['id'], 'a', $teamA !== '' ? (int) $teamA : null);
            Game::setQfTeam((int) $game['id'], 'b', $teamB !== '' ? (int) $teamB : null);
        }

        flash_set('success', 'Viertelfinal-Paarungen gespeichert.');
        redirect('/admin/season/' . $seasonId);
    }

    public static function adminCreate(array $params): void
    {
        AdminAuth::requireLogin();
        if (!csrf_check()) {
            redirect('/admin');
            return;
        }
        $username = trim((string) ($_POST['username'] ?? ''));
        $password = (string) ($_POST['password'] ?? '');
        $displayName = trim((string) ($_POST['display_name'] ?? ''));

        if ($username === '' || strlen($password) < 6 || $displayName === '') {
            flash_set('error', 'Benutzername, Anzeigename und ein Passwort (min. 6 Zeichen) sind nötig.');
            redirect('/admin');
            return;
        }
        if (Admin::findByUsername($username) !== null) {
            flash_set('error', 'Dieser Benutzername existiert bereits.');
            redirect('/admin');
            return;
        }

        Admin::create($username, $password, $displayName);
        flash_set('success', 'Admin "' . $displayName . '" erstellt.');
        redirect('/admin');
    }

    public static function adminDelete(array $params): void
    {
        $current = AdminAuth::requireLogin();
        if (!csrf_check()) {
            redirect('/admin');
            return;
        }
        $id = (int) $params['id'];

        if (Admin::count() <= 1) {
            flash_set('error', 'Der letzte Admin-Account kann nicht gelöscht werden.');
            redirect('/admin');
            return;
        }

        Admin::delete($id);
        if ((int) $current['id'] === $id) {
            AdminAuth::logout();
        }
        flash_set('success', 'Admin gelöscht.');
        redirect('/admin');
    }

    public static function auditLog(array $params): void
    {
        $admin = AdminAuth::requireLogin();
        $season = Season::find((int) $params['id']);
        if ($season === null) {
            http_response_code(404);
            render('404');
            return;
        }

        $entries = Game::recentAuditLog((int) $season['id'], 200);
        $teamsById = [];
        foreach (Team::bySeason((int) $season['id']) as $t) {
            $teamsById[$t['id']] = $t;
        }
        $gamesById = [];
        foreach (Game::bracketGames((int) $season['id']) as $g) {
            $gamesById[$g['id']] = $g;
        }
        foreach (Season::groups((int) $season['id']) as $group) {
            foreach (Game::forGroup((int) $group['id']) as $g) {
                $gamesById[$g['id']] = $g;
            }
        }

        render('admin/audit', [
            'admin' => $admin,
            'season' => $season,
            'entries' => $entries,
            'teamsById' => $teamsById,
            'gamesById' => $gamesById,
        ]);
    }
}
