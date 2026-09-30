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

    public static function teamsIndex(array $params): void
    {
        $admin = AdminAuth::requireLogin();
        $teams = [];
        foreach (Team::all() as $team) {
            $teams[] = $team + ['seasons' => Team::seasonHistory((int) $team['id'])];
        }

        render('admin/teams', [
            'admin' => $admin,
            'teams' => $teams,
        ]);
    }

    /** Creates a brand-new persistent team from the central team-management page, not yet enrolled in any season. */
    public static function teamCreateGlobal(array $params): void
    {
        AdminAuth::requireLogin();
        if (!csrf_check()) {
            redirect('/admin/teams');
            return;
        }

        $name = trim((string) ($_POST['name'] ?? ''));
        $player1 = trim((string) ($_POST['player1'] ?? ''));
        $player2 = trim((string) ($_POST['player2'] ?? ''));

        if ($name === '' || $player1 === '' || $player2 === '') {
            flash_set('error', 'Bitte alle Felder ausfüllen.');
            redirect('/admin/teams');
            return;
        }

        $teamId = Team::create($name, $player1, $player2);
        $pin = Team::find($teamId)['pin'];

        flash_set('success', "Team \"$name\" erstellt. PIN für den Login: $pin. Füge das Team über eine Saison einer Gruppe hinzu.");
        redirect('/admin/teams');
    }

    /** Permanently deletes a team and its entire history, from every season. */
    public static function teamDeleteCompletely(array $params): void
    {
        AdminAuth::requireLogin();
        $teamId = (int) $params['id'];
        $team = Team::find($teamId);
        if ($team === null) {
            http_response_code(404);
            render('404');
            return;
        }
        if (!csrf_check()) {
            redirect('/admin/teams');
            return;
        }

        Team::deleteCompletely($teamId);
        flash_set('success', 'Team "' . $team['name'] . '" und seine gesamte Geschichte wurden gelöscht.');
        redirect('/admin/teams');
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
        $qualifiersPerGroup = Season::qualifiersPerGroup(count($groups));
        $groupData = [];
        $qualifiedTeams = [];
        $qualifiersByGroup = [];
        $groupResultsCount = 0;
        foreach ($groups as $group) {
            $teams = Team::byGroup((int) $group['id']);
            $games = Game::forGroup((int) $group['id']);
            $groupData[] = [
                'group' => $group,
                'teams' => $teams,
                'gamesCount' => count($games),
            ];
            $groupResultsCount += count(array_filter($games, fn ($g) => Game::isComplete($g)));
            $standings = StandingsCalculator::compute($teams, $games);
            $topN = array_slice($standings, 0, $qualifiersPerGroup);
            foreach ($topN as $row) {
                $qualifiedTeams[] = $row['team'];
            }
            $qualifiersByGroup[$group['name']] = $topN;
        }

        Game::ensureBracketSkeleton((int) $season['id']);
        $bracketGames = Game::bracketGames((int) $season['id']);
        $qfGames = array_values(array_filter($bracketGames, fn ($g) => $g['phase'] === 'qf'));

        // Before any group-phase game has a result, standings are all tied and "top 2"
        // is really just alphabetical -- prefilling real (but meaningless) team names
        // would look like an actual assignment. Show the crossed-seeding template as
        // plain group labels instead, and leave the dropdowns themselves unselected.
        $qfLabelSuggestions = [];
        if ($groupResultsCount === 0) {
            $qfLabelSuggestions = BracketService::recommendedQfPairingLabels(array_keys($qualifiersByGroup)) ?? [];
        } else {
            // Suggest the standard crossed pairing for any QF slot the admin hasn't
            // assigned yet -- purely a display default, only saved if the admin submits.
            $recommended = BracketService::recommendedQfPairings($qualifiersByGroup);
            if ($recommended !== null) {
                foreach ($qfGames as &$game) {
                    $slot = (int) $game['slot_index'];
                    if ($game['team_a_id'] === null && $game['team_b_id'] === null && isset($recommended[$slot])) {
                        [$game['team_a_id'], $game['team_b_id']] = $recommended[$slot];
                        $game['prefilled'] = true;
                    }
                }
                unset($game);
            }
        }

        $availableTeams = Team::availableForSeason((int) $season['id']);

        render('admin/season', [
            'admin' => $admin,
            'season' => $season,
            'groupData' => $groupData,
            'qfGames' => $qfGames,
            'qfLabelSuggestions' => $qfLabelSuggestions,
            'qualifiedTeams' => $qualifiedTeams,
            'qualifiersPerGroup' => $qualifiersPerGroup,
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
        $groupCount = (int) ($_POST['group_count'] ?? 4);
        if (!in_array($groupCount, [3, 4], true)) {
            $groupCount = 4;
        }
        if ($label === '') {
            flash_set('error', 'Bitte einen Namen für die Saison angeben.');
            redirect('/admin');
            return;
        }
        $id = Season::create($label, $year, $groupCount);
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

    public static function seasonDelete(array $params): void
    {
        AdminAuth::requireLogin();
        $id = (int) $params['id'];
        $season = Season::find($id);
        if ($season === null) {
            http_response_code(404);
            render('404');
            return;
        }
        if (!csrf_check()) {
            redirect('/admin/season/' . $id);
            return;
        }

        Season::delete($id);
        flash_set('success', 'Saison "' . $season['label'] . '" und alle zugehörigen Gruppen, Spiele und Resultate wurden gelöscht.');
        redirect('/admin');
    }

    public static function teamCreate(array $params): void
    {
        AdminAuth::requireLogin();
        $seasonId = (int) $params['id'];
        $groupId = (int) ($_POST['group_id'] ?? 0);
        if (!csrf_check()) {
            self::redirectAfterTeamAction($seasonId, $groupId);
            return;
        }
        if (!self::isSeasonCurrent($seasonId)) {
            flash_set('error', 'Diese Saison ist archiviert. Teams können nur in der aktiven Saison hinzugefügt werden.');
            self::redirectAfterTeamAction($seasonId, $groupId);
            return;
        }

        $name = trim((string) ($_POST['name'] ?? ''));
        $player1 = trim((string) ($_POST['player1'] ?? ''));
        $player2 = trim((string) ($_POST['player2'] ?? ''));

        if ($name === '' || $player1 === '' || $player2 === '' || $groupId === 0) {
            flash_set('error', 'Bitte alle Felder ausfüllen.');
            self::redirectAfterTeamAction($seasonId, $groupId);
            return;
        }

        $teamId = Team::createAndEnroll($seasonId, $groupId, $name, $player1, $player2);
        $pin = Team::find($teamId)['pin'];

        flash_set('success', "Team \"$name\" erstellt. PIN für den Login: $pin (bitte dem Team mitteilen).");
        self::redirectAfterTeamAction($seasonId, $groupId);
    }

    /** Enrolls a team that already exists from a previous season into this one. */
    public static function teamEnroll(array $params): void
    {
        AdminAuth::requireLogin();
        $seasonId = (int) $params['id'];
        $groupId = (int) ($_POST['group_id'] ?? 0);
        if (!csrf_check()) {
            self::redirectAfterTeamAction($seasonId, $groupId);
            return;
        }
        if (!self::isSeasonCurrent($seasonId)) {
            flash_set('error', 'Diese Saison ist archiviert. Teams können nur in der aktiven Saison hinzugefügt werden.');
            self::redirectAfterTeamAction($seasonId, $groupId);
            return;
        }

        $teamId = (int) ($_POST['team_id'] ?? 0);
        $team = Team::find($teamId);

        if ($team === null || $groupId === 0) {
            flash_set('error', 'Bitte ein Team und eine Gruppe wählen.');
            self::redirectAfterTeamAction($seasonId, $groupId);
            return;
        }

        Team::enroll($teamId, $seasonId, $groupId);
        flash_set('success', 'Team "' . $team['name'] . '" zur Saison hinzugefügt.');
        self::redirectAfterTeamAction($seasonId, $groupId);
    }

    /** Whether teams may still be added to / moved within this season's groups. */
    private static function isSeasonCurrent(int $seasonId): bool
    {
        $season = Season::find($seasonId);
        return $season !== null && (int) $season['is_current'] === 1;
    }

    /**
     * /admin/season/{id}, anchored at a group's section when known (so the browser lands back
     * where the admin was instead of jumping to the top of the page), or the central
     * /admin/teams page when there's no season context.
     */
    private static function redirectAfterTeamAction(int $seasonId, int $groupId = 0): void
    {
        if ($seasonId === 0) {
            redirect('/admin/teams');
            return;
        }
        redirect('/admin/season/' . $seasonId . ($groupId !== 0 ? '#group-' . $groupId : ''));
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
        $groupId = (int) ($_POST['group_id'] ?? 0);
        if ($groupId === 0) {
            $groupId = self::currentGroupId($teamId, $seasonId);
        }
        if (!csrf_check()) {
            self::redirectAfterTeamAction($seasonId, $groupId);
            return;
        }

        $name = trim((string) ($_POST['name'] ?? ''));
        $player1 = trim((string) ($_POST['player1'] ?? ''));
        $player2 = trim((string) ($_POST['player2'] ?? ''));

        if ($name !== '' && $player1 !== '' && $player2 !== '') {
            Team::update($teamId, $name, $player1, $player2);
            if ($groupId !== 0 && $seasonId !== 0 && (int) ($_POST['group_id'] ?? 0) !== 0) {
                if (self::isSeasonCurrent($seasonId)) {
                    Team::updateGroup($teamId, $seasonId, $groupId);
                } else {
                    flash_set('error', 'Diese Saison ist archiviert. Die Gruppe wurde nicht geändert.');
                }
            }
            flash_set('success', 'Team aktualisiert.');
        }

        self::redirectAfterTeamAction($seasonId, $groupId);
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
        $groupId = self::currentGroupId($teamId, $seasonId);
        if (!csrf_check()) {
            self::redirectAfterTeamAction($seasonId, $groupId);
            return;
        }

        try {
            $path = PhotoUploader::handle($teamId, $_FILES['photo'] ?? []);
            Team::setPhoto($teamId, $path);
            flash_set('success', 'Foto gespeichert.');
        } catch (RuntimeException $e) {
            flash_set('error', $e->getMessage());
        }

        self::redirectAfterTeamAction($seasonId, $groupId);
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
        $groupId = self::currentGroupId($teamId, $seasonId);
        if (!csrf_check()) {
            self::redirectAfterTeamAction($seasonId, $groupId);
            return;
        }

        $pin = str_pad((string) random_int(0, 9999), 4, '0', STR_PAD_LEFT);
        Team::resetPin($teamId, $pin);
        flash_set('success', 'Neuer PIN für ' . $team['name'] . ': ' . $pin);
        self::redirectAfterTeamAction($seasonId, $groupId);
    }

    /** This team's group in the given season, or 0 if it isn't enrolled there. */
    private static function currentGroupId(int $teamId, int $seasonId): int
    {
        if ($seasonId === 0) {
            return 0;
        }
        $enrollment = Team::enrollmentFor($teamId, $seasonId);
        return $enrollment !== null ? (int) $enrollment['group_id'] : 0;
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
        // Resolved before removal -- the enrollment row (and its group_id) is gone afterwards.
        $groupId = self::currentGroupId($teamId, $seasonId);
        if (!csrf_check()) {
            self::redirectAfterTeamAction($seasonId, $groupId);
            return;
        }

        Team::removeFromSeason($teamId, $seasonId);
        flash_set('success', 'Team aus der Saison entfernt.');
        self::redirectAfterTeamAction($seasonId, $groupId);
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
        $seasonId = (int) $group['season_id'];
        if (!csrf_check()) {
            self::redirectAfterTeamAction($seasonId, $groupId);
            return;
        }

        $existing = Game::forGroup($groupId);
        if (count($existing) > 0) {
            flash_set('error', 'Für diese Gruppe wurden die Spiele bereits erstellt.');
            self::redirectAfterTeamAction($seasonId, $groupId);
            return;
        }

        $teams = Team::byGroup($groupId);
        if (count($teams) < 2) {
            flash_set('error', 'Eine Gruppe braucht mindestens 2 Teams.');
            self::redirectAfterTeamAction($seasonId, $groupId);
            return;
        }

        $teamIds = array_map(fn ($t) => (int) $t['id'], $teams);
        Game::createGroupFixtures($seasonId, $groupId, $teamIds);

        flash_set('success', count(RoundRobinScheduler::generate($teamIds)) . ' Spiele erstellt.');
        self::redirectAfterTeamAction($seasonId, $groupId);
    }

    /**
     * Removes an unused group (e.g. a leftover 4th group on a season that's really only
     * played with 3). Only allowed while the group has no teams enrolled, so this can never
     * cascade-delete real fixtures or results.
     */
    public static function groupDelete(array $params): void
    {
        AdminAuth::requireLogin();
        $groupId = (int) $params['id'];
        $group = TeamGroup::find($groupId);
        if ($group === null) {
            http_response_code(404);
            render('404');
            return;
        }
        $seasonId = (int) $group['season_id'];
        if (!csrf_check()) {
            redirect('/admin/season/' . $seasonId);
            return;
        }

        if (count(Team::byGroup($groupId)) > 0) {
            flash_set('error', 'Gruppe ' . $group['name'] . ' enthält noch Teams und kann nicht gelöscht werden.');
            redirect('/admin/season/' . $seasonId);
            return;
        }

        TeamGroup::delete($groupId);
        flash_set('success', 'Gruppe ' . $group['name'] . ' wurde gelöscht.');
        redirect('/admin/season/' . $seasonId);
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

    /** Adds a custom calendar entry with no game behind it (group draw, victory party, etc.). */
    public static function calendarEventCreate(array $params): void
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
            redirect(self::calendarUrl($season));
            return;
        }
        if (!self::isSeasonCurrent($seasonId)) {
            flash_set('error', 'Diese Saison ist archiviert. Termine können nur in der aktiven Saison hinzugefügt werden.');
            redirect(self::calendarUrl($season));
            return;
        }

        $title = trim((string) ($_POST['title'] ?? ''));
        $location = trim((string) ($_POST['location'] ?? ''));
        $date = (string) ($_POST['event_date'] ?? '');
        $time = parse_time_input((string) ($_POST['event_time'] ?? ''));
        $duration = parse_duration_input((string) ($_POST['duration_minutes'] ?? ''));
        $reminder = !empty($_POST['reminder']);

        if ($title === '' || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
            flash_set('error', 'Bitte Titel und Datum angeben.');
            redirect(self::calendarUrl($season));
            return;
        }

        $admin = AdminAuth::current();
        CalendarEvent::create($seasonId, $title, $location !== '' ? $location : null, $date, $time, $duration, $reminder, (int) $admin['id']);

        flash_set('success', 'Termin "' . $title . '" gespeichert.');
        redirect(self::calendarUrl($season));
    }

    public static function calendarEventUpdate(array $params): void
    {
        AdminAuth::requireLogin();
        $event = CalendarEvent::find((int) $params['id']);
        if ($event === null) {
            http_response_code(404);
            render('404');
            return;
        }
        $season = Season::find((int) $event['season_id']);
        if (!csrf_check()) {
            redirect(self::calendarUrl($season));
            return;
        }
        if (!self::isSeasonCurrent((int) $event['season_id'])) {
            flash_set('error', 'Diese Saison ist archiviert. Termine können nicht mehr bearbeitet werden.');
            redirect(self::calendarUrl($season));
            return;
        }

        $title = trim((string) ($_POST['title'] ?? ''));
        $location = trim((string) ($_POST['location'] ?? ''));
        $date = (string) ($_POST['event_date'] ?? '');
        $time = parse_time_input((string) ($_POST['event_time'] ?? ''));
        $duration = parse_duration_input((string) ($_POST['duration_minutes'] ?? ''));
        $reminder = !empty($_POST['reminder']);

        if ($title === '' || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
            flash_set('error', 'Bitte Titel und Datum angeben.');
            redirect(self::calendarUrl($season));
            return;
        }

        CalendarEvent::update((int) $event['id'], $title, $location !== '' ? $location : null, $date, $time, $duration, $reminder);

        flash_set('success', 'Termin "' . $title . '" aktualisiert.');
        redirect(self::calendarUrl($season));
    }

    public static function calendarEventDelete(array $params): void
    {
        AdminAuth::requireLogin();
        $event = CalendarEvent::find((int) $params['id']);
        if ($event === null) {
            http_response_code(404);
            render('404');
            return;
        }
        $season = Season::find((int) $event['season_id']);
        if (!csrf_check()) {
            redirect(self::calendarUrl($season));
            return;
        }
        if (!self::isSeasonCurrent((int) $event['season_id'])) {
            flash_set('error', 'Diese Saison ist archiviert. Termine können nicht mehr gelöscht werden.');
            redirect(self::calendarUrl($season));
            return;
        }

        CalendarEvent::delete((int) $event['id']);
        flash_set('success', 'Termin "' . $event['title'] . '" gelöscht.');
        redirect(self::calendarUrl($season));
    }

    /** The calendar page to return to after adding/removing a custom event. */
    private static function calendarUrl(?array $season): string
    {
        if ($season === null) {
            return '/calendar';
        }
        return (int) $season['is_current'] === 1 ? '/calendar' : '/calendar/' . $season['id'];
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
