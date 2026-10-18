<?php

/**
 * JSON API for the iOS/iPadOS app (/api/v1). Read endpoints are public, like the website;
 * result entry and scheduling need a team bearer token (see ApiAuth) and follow the same rules
 * as GameController: only the two participating teams, only in the current season. With the
 * token a team can also replace its own photo.
 *
 * Errors are {"error": "<code>", "message": "<German text>"}; the app localizes by code.
 */
final class ApiController
{
    public const VERSION = 1;

    public static function info(array $params): void
    {
        self::respond([
            'api_version' => self::VERSION,
            'env' => is_dev_env() ? 'dev' : 'prod',
            'current_season_id' => ($s = Season::current()) !== null ? (int) $s['id'] : null,
        ]);
    }

    public static function seasons(array $params): void
    {
        self::respond(['seasons' => array_map([self::class, 'seasonJson'], Season::all())]);
    }

    public static function currentSeason(array $params): void
    {
        $season = Season::current();
        if ($season === null) {
            self::error(404, 'no_current_season', 'Es gibt aktuell keine laufende Saison.');
        }
        self::respond(self::seasonDetail($season));
    }

    public static function season(array $params): void
    {
        $season = Season::find((int) $params['id']);
        if ($season === null) {
            self::error(404, 'not_found', 'Saison nicht gefunden.');
        }
        self::respond(self::seasonDetail($season));
    }

    /** A team with every season it played in and all of its games, oldest season first. */
    public static function team(array $params): void
    {
        $team = Team::find((int) $params['id']);
        if ($team === null) {
            self::error(404, 'not_found', 'Team nicht gefunden.');
        }
        $teamId = (int) $team['id'];
        $games = Game::forTeam($teamId);

        $opponentIds = [];
        foreach ($games as $g) {
            foreach (['team_a_id', 'team_b_id'] as $key) {
                if ($g[$key] !== null && (int) $g[$key] !== $teamId) {
                    $opponentIds[(int) $g[$key]] = true;
                }
            }
        }
        $opponents = [];
        foreach (array_keys($opponentIds) as $id) {
            $t = Team::find($id);
            if ($t !== null) {
                $opponents[] = self::teamJson($t);
            }
        }

        $groupNames = [];
        $enrollments = array_map(function (array $row) use (&$groupNames): array {
            $groupNames[(int) $row['group_id']] = $row['group_name'];
            return [
                'season_id' => (int) $row['season_id'],
                'season_label' => $row['season_label'],
                'year' => (int) $row['year'],
                'group_id' => (int) $row['group_id'],
                'group_name' => $row['group_name'],
            ];
        }, Team::seasonHistory($teamId));

        self::respond([
            'team' => self::teamJson($team),
            'enrollments' => $enrollments,
            'games' => array_map(fn (array $g): array => self::gameJson($g, $groupNames), $games),
            'opponents' => $opponents,
        ]);
    }

    /** Trades a team's PIN for a bearer token. Same 5-attempts-per-15-minutes lockout as the web login. */
    public static function login(array $params): void
    {
        $body = self::jsonBody();
        $teamId = is_int($body['team_id'] ?? null) ? $body['team_id'] : 0;
        $pin = is_string($body['pin'] ?? null) ? $body['pin'] : '';
        $team = $teamId > 0 ? Team::find($teamId) : null;
        if ($team === null) {
            self::error(404, 'not_found', 'Team nicht gefunden.');
        }
        if (TeamAuth::isLockedOut($teamId)) {
            self::error(429, 'locked_out', 'Zu viele Fehlversuche. Bitte in 15 Minuten erneut versuchen.');
        }
        if (!TeamAuth::checkPin($teamId, $pin)) {
            self::error(401, 'wrong_pin', 'Falscher PIN.');
        }

        $device = isset($body['device']) && is_string($body['device']) ? trim($body['device']) : null;
        self::respond([
            'token' => ApiAuth::issueToken($teamId, $device !== '' ? $device : null),
            'team' => self::teamJson($team),
        ]);
    }

    public static function logout(array $params): void
    {
        ApiAuth::revokeCurrent();
        self::respond(['ok' => true]);
    }

    public static function me(array $params): void
    {
        self::respond(['team' => self::teamJson(self::requireTeam())]);
    }

    /**
     * Body: {"sets_a": 2, "sets_b": 1, "played_date": "2026-07-01", "game_time": "18:30"}.
     * played_date/game_time are optional: leave a key out to keep the game's current value,
     * send null to clear it.
     */
    public static function submitResult(array $params): void
    {
        $team = self::requireTeam();
        $game = self::editableGame((int) $params['id'], $team);
        $body = self::jsonBody();

        $setsA = $body['sets_a'] ?? null;
        $setsB = $body['sets_b'] ?? null;
        if (!is_int($setsA) || !is_int($setsB) || !in_array([$setsA, $setsB], GameController::VALID_SCORES, true)) {
            self::error(422, 'invalid_score', 'Ungültiges Resultat. Erlaubt sind 2:0, 2:1, 1:2, 0:2, 1:1.');
        }
        if ($setsA === $setsB && $game['phase'] !== 'group') {
            self::error(422, 'draw_not_allowed', 'Ein Unentschieden ist nur in der Gruppenphase möglich, da hier ein Sieger feststehen muss.');
        }

        $date = array_key_exists('played_date', $body) ? self::parseDate($body['played_date'], true) : $game['played_date'];
        $time = array_key_exists('game_time', $body) ? self::parseTime($body['game_time']) : $game['game_time'];

        Game::recordResult((int) $game['id'], $setsA, $setsB, $date, $time, 'team', (int) $team['id'], $team['name']);
        self::respond(['game' => self::gameJson(Game::find((int) $game['id']))]);
    }

    /**
     * Body: {"played_date": "2026-07-01", "game_time": "18:30", "force": false}. A clash with
     * another planned game answers 409 with the conflicting games; resend with force=true to
     * save anyway (the website's "Trotzdem speichern").
     */
    public static function scheduleGame(array $params): void
    {
        $team = self::requireTeam();
        $game = self::editableGame((int) $params['id'], $team);
        $body = self::jsonBody();

        $date = self::parseDate($body['played_date'] ?? null, false);
        $time = self::parseTime($body['game_time'] ?? null);

        if (($body['force'] ?? false) !== true) {
            $conflicts = Game::scheduleConflicts($game, $date, $time);
            if ($conflicts !== []) {
                self::respond([
                    'error' => 'schedule_conflict',
                    'message' => 'Zu dieser Zeit ist bereits ein anderes Spiel geplant.',
                    'slot_minutes' => Game::slotMinutes($game['phase']),
                    'conflicts' => array_map(fn (array $g): array => self::gameJson($g), $conflicts),
                ], 409);
            }
        }

        Game::scheduleDate((int) $game['id'], $date, $time, 'team', (int) $team['id'], $team['name']);
        self::respond(['game' => self::gameJson(Game::find((int) $game['id']))]);
    }

    /**
     * Multipart upload, field "photo" (JPG, PNG or WEBP): replaces the team's photo. Like the
     * website's TeamController::uploadPhoto, a team may only change its own photo, in any season.
     */
    public static function uploadPhoto(array $params): void
    {
        $team = self::requireTeam();
        if ((int) $params['id'] !== (int) $team['id']) {
            self::error(403, 'not_own_team', 'Du kannst nur das Foto deines eigenen Teams ändern.');
        }

        try {
            $path = PhotoUploader::handle((int) $team['id'], $_FILES['photo'] ?? []);
        } catch (RuntimeException $e) {
            self::error(422, 'invalid_photo', $e->getMessage());
        }
        Team::setPhoto((int) $team['id'], $path);
        self::respond(['team' => self::teamJson(Team::find((int) $team['id']))]);
    }

    public static function notFound(): void
    {
        self::error(404, 'not_found', 'Unbekannter API-Endpunkt.');
    }

    // ---------------------------------------------------------------------------------------

    /** Everything one season's screens need, in one response: groups with standings and games, the bracket, teams. */
    private static function seasonDetail(array $season): array
    {
        $seasonId = (int) $season['id'];
        $groups = Season::groups($seasonId);
        $groupNames = [];
        foreach ($groups as $group) {
            $groupNames[(int) $group['id']] = $group['name'];
        }

        $groupsJson = [];
        foreach ($groups as $group) {
            $teams = Team::byGroup((int) $group['id']);
            $games = Game::forGroup((int) $group['id']);
            $groupsJson[] = [
                'id' => (int) $group['id'],
                'name' => $group['name'],
                'standings' => array_map(fn (array $row): array => [
                    'team_id' => (int) $row['team']['id'],
                    'rank' => (int) $row['rank'],
                    'played' => (int) $row['played'],
                    'won' => (int) $row['won'],
                    'tied' => (int) $row['tied'],
                    'lost' => (int) $row['lost'],
                    'sets_for' => (int) $row['sets_for'],
                    'sets_against' => (int) $row['sets_against'],
                    'points' => (int) $row['points'],
                ], StandingsCalculator::compute($teams, $games)),
                'games' => array_map(fn (array $g): array => self::gameJson($g, $groupNames), $games),
            ];
        }

        $qfSlots = BracketService::recommendedQfPairingSlots(array_values($groupNames)) ?? [];
        $phases = BracketService::byPhase(Game::bracketGames($seasonId));
        $bracketGame = function (array $g) use ($qfSlots): array {
            $placeholders = $g['phase'] === 'qf'
                ? ($qfSlots[(int) $g['slot_index']] ?? null)
                : BracketService::feederSlots($g['phase'], (int) $g['slot_index']);
            return self::gameJson($g, [], $placeholders);
        };

        $teamsById = [];
        foreach (Team::bySeason($seasonId) as $t) {
            $teamsById[(int) $t['id']] = $t;
        }
        $allBracketGames = array_merge($phases['qf'], $phases['sf'], $phases['final'], $phases['third']);
        $podium = BracketService::podium($allBracketGames, $teamsById);

        return [
            'season' => self::seasonJson($season),
            'qualifiers_per_group' => Season::qualifiersPerGroup(count($groups)),
            'teams' => array_map([self::class, 'teamJson'], array_values($teamsById)),
            'groups' => $groupsJson,
            'bracket' => [
                'qf' => array_map($bracketGame, $phases['qf']),
                'sf' => array_map($bracketGame, $phases['sf']),
                'final' => isset($phases['final'][0]) ? $bracketGame($phases['final'][0]) : null,
                'third' => isset($phases['third'][0]) ? $bracketGame($phases['third'][0]) : null,
            ],
            'podium' => $podium === null ? null : [
                'champion_id' => $podium['champion'] !== null ? (int) $podium['champion']['id'] : null,
                'runner_up_id' => $podium['runnerUp'] !== null ? (int) $podium['runnerUp']['id'] : null,
                'third_id' => $podium['third'] !== null ? (int) $podium['third']['id'] : null,
            ],
            'events' => array_map(fn (array $e): array => [
                'id' => (int) $e['id'],
                'title' => $e['title'],
                'location' => $e['location'],
                'date' => $e['event_date'],
                'time' => $e['event_time'] !== null ? substr($e['event_time'], 0, 5) : null,
                'duration_minutes' => $e['duration_minutes'] !== null ? (int) $e['duration_minutes'] : null,
            ], CalendarEvent::forSeason($seasonId)),
        ];
    }

    private static function seasonJson(array $season): array
    {
        return [
            'id' => (int) $season['id'],
            'label' => $season['label'],
            'year' => (int) $season['year'],
            'is_current' => (int) $season['is_current'] === 1,
        ];
    }

    /** Never includes the PIN. */
    private static function teamJson(array $team): array
    {
        $photo = team_photo_url($team);
        return [
            'id' => (int) $team['id'],
            'name' => $team['name'],
            'player1' => $team['player1'],
            'player2' => $team['player2'],
            'color_hex' => $team['color_hex'],
            'photo_url' => $photo !== null ? absolute_url(substr($photo, strlen(rtrim(base_path(), '/')))) : null,
            'group_id' => isset($team['group_id']) ? (int) $team['group_id'] : null,
        ];
    }

    /**
     * @param array<int,string> $groupNames group id => name, to label group games ("A")
     * @param array{0:array,1:array}|null $placeholders what will fill still-empty bracket slots
     */
    private static function gameJson(array $game, array $groupNames = [], ?array $placeholders = null): array
    {
        $groupId = $game['group_id'] !== null ? (int) $game['group_id'] : null;
        return [
            'id' => (int) $game['id'],
            'season_id' => (int) $game['season_id'],
            'phase' => $game['phase'],
            'group_id' => $groupId,
            'group_name' => $groupId !== null ? ($groupNames[$groupId] ?? TeamGroup::find($groupId)['name'] ?? null) : null,
            'slot_index' => $game['slot_index'] !== null ? (int) $game['slot_index'] : null,
            'team_a_id' => $game['team_a_id'] !== null ? (int) $game['team_a_id'] : null,
            'team_b_id' => $game['team_b_id'] !== null ? (int) $game['team_b_id'] : null,
            'sets_a' => $game['sets_a'] !== null ? (int) $game['sets_a'] : null,
            'sets_b' => $game['sets_b'] !== null ? (int) $game['sets_b'] : null,
            'played_date' => $game['played_date'],
            'game_time' => $game['game_time'] !== null ? substr($game['game_time'], 0, 5) : null,
            'slot_minutes' => Game::slotMinutes($game['phase']),
            'winner_team_id' => Game::winnerTeamId($game),
            'placeholder_a' => $game['team_a_id'] === null ? ($placeholders[0] ?? null) : null,
            'placeholder_b' => $game['team_b_id'] === null ? ($placeholders[1] ?? null) : null,
            'updated_at' => $game['updated_at'],
        ];
    }

    private static function requireTeam(): array
    {
        $team = ApiAuth::currentTeam();
        if ($team === null) {
            self::error(401, 'unauthorized', 'Bitte melde dich mit dem PIN deines Teams an.');
        }
        return $team;
    }

    /** The game, if it exists, its season is still current, and $team plays in it with a known opponent. */
    private static function editableGame(int $gameId, array $team): array
    {
        $game = Game::find($gameId);
        if ($game === null) {
            self::error(404, 'not_found', 'Spiel nicht gefunden.');
        }
        $season = Season::find((int) $game['season_id']);
        if ($season === null || (int) $season['is_current'] !== 1) {
            self::error(403, 'season_archived', 'Diese Saison ist archiviert und kann nicht mehr bearbeitet werden.');
        }
        if (!Game::isParticipant($game, (int) $team['id'])) {
            self::error(403, 'not_participant', 'Nur die beiden beteiligten Teams können dieses Spiel bearbeiten.');
        }
        if ($game['team_a_id'] === null || $game['team_b_id'] === null) {
            self::error(422, 'opponent_unknown', 'Für dieses Spiel steht der Gegner noch nicht fest.');
        }
        return $game;
    }

    private static function parseDate(mixed $value, bool $nullable): ?string
    {
        if ($nullable && ($value === null || $value === '')) {
            return null;
        }
        if (!is_string($value) || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $value) || !checkdate((int) substr($value, 5, 2), (int) substr($value, 8, 2), (int) substr($value, 0, 4))) {
            self::error(422, 'invalid_date', 'Ungültiges Datum.');
        }
        return $value;
    }

    private static function parseTime(mixed $value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }
        $time = is_string($value) ? parse_time_input($value) : null;
        if ($time === null) {
            self::error(422, 'invalid_time', 'Ungültige Uhrzeit.');
        }
        return $time;
    }

    private static function jsonBody(): array
    {
        $raw = file_get_contents('php://input');
        $data = $raw !== false && $raw !== '' ? json_decode($raw, true) : [];
        if (!is_array($data)) {
            self::error(400, 'invalid_json', 'Ungültige Anfrage.');
        }
        return $data;
    }

    private static function respond(array $data, int $status = 200): never
    {
        header('Cache-Control: no-store');
        json_response($data, $status);
        exit;
    }

    private static function error(int $status, string $code, string $message): never
    {
        self::respond(['error' => $code, 'message' => $message], $status);
    }
}
