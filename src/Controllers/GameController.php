<?php

final class GameController
{
    private const VALID_SCORES = [[2, 0], [2, 1], [1, 2], [0, 2]];

    public static function submitResult(array $params): void
    {
        $gameId = (int) $params['id'];
        $returnTo = (string) ($_POST['return_to'] ?? '/');

        if (!csrf_check()) {
            flash_set('error', 'Ungültige Anfrage. Bitte erneut versuchen.');
            redirect($returnTo);
            return;
        }

        $game = Game::find($gameId);
        if ($game === null) {
            flash_set('error', 'Spiel nicht gefunden.');
            redirect($returnTo);
            return;
        }

        $actor = self::authorizeEdit($game, $returnTo, 'Bitte melde dich als Team oder Admin an, um ein Resultat einzutragen.');
        if ($actor === null) {
            return;
        }
        [$actorType, $actorId, $actorLabel] = $actor;

        if ($game['team_a_id'] === null || $game['team_b_id'] === null) {
            flash_set('error', 'Für dieses Spiel steht der Gegner noch nicht fest.');
            redirect($returnTo);
            return;
        }

        $score = (string) ($_POST['score'] ?? '');
        $parts = explode('-', $score);
        if (count($parts) !== 2 || !ctype_digit($parts[0]) || !ctype_digit($parts[1])) {
            flash_set('error', 'Ungültiges Resultat. Erlaubt sind 2:0, 2:1, 1:2, 0:2.');
            redirect($returnTo);
            return;
        }
        $setsA = (int) $parts[0];
        $setsB = (int) $parts[1];
        if (!in_array([$setsA, $setsB], self::VALID_SCORES, true)) {
            flash_set('error', 'Ungültiges Resultat. Erlaubt sind 2:0, 2:1, 1:2, 0:2.');
            redirect($returnTo);
            return;
        }

        $date = (string) ($_POST['played_date'] ?? '');
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
            $date = date('Y-m-d');
        }

        Game::recordResult($gameId, $setsA, $setsB, $date, $actorType, $actorId, $actorLabel);

        flash_set('success', 'Resultat gespeichert: ' . $setsA . ':' . $setsB);
        redirect($returnTo);
    }

    public static function scheduleDate(array $params): void
    {
        $gameId = (int) $params['id'];
        $returnTo = (string) ($_POST['return_to'] ?? '/');

        if (!csrf_check()) {
            flash_set('error', 'Ungültige Anfrage. Bitte erneut versuchen.');
            redirect($returnTo);
            return;
        }

        $game = Game::find($gameId);
        if ($game === null) {
            flash_set('error', 'Spiel nicht gefunden.');
            redirect($returnTo);
            return;
        }

        $actor = self::authorizeEdit($game, $returnTo, 'Bitte melde dich als Team oder Admin an, um einen Termin festzulegen.');
        if ($actor === null) {
            return;
        }
        [$actorType, $actorId, $actorLabel] = $actor;

        if ($game['team_a_id'] === null || $game['team_b_id'] === null) {
            flash_set('error', 'Für dieses Spiel steht der Gegner noch nicht fest.');
            redirect($returnTo);
            return;
        }

        $date = (string) ($_POST['played_date'] ?? '');
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
            flash_set('error', 'Ungültiges Datum.');
            redirect($returnTo);
            return;
        }

        Game::scheduleDate($gameId, $date, $actorType, $actorId, $actorLabel);

        flash_set('success', 'Termin gespeichert: ' . format_date_ch($date));
        redirect($returnTo);
    }

    /**
     * Checks the game's season is still current and the requester is admin or a participating
     * team. On failure, flashes an error, redirects, and returns null. On success returns
     * [actorType, actorId, actorLabel].
     *
     * @return array{0:string,1:int,2:string}|null
     */
    private static function authorizeEdit(array $game, string $returnTo, string $deniedMessage): ?array
    {
        $season = Season::find((int) $game['season_id']);
        if ($season === null || (int) $season['is_current'] !== 1) {
            flash_set('error', 'Diese Saison ist archiviert und kann nicht mehr bearbeitet werden.');
            redirect($returnTo);
            return null;
        }

        $admin = AdminAuth::current();
        $team = TeamAuth::current();
        $isAdmin = $admin !== null;
        $isTeam = !$isAdmin && $team !== null && Game::isParticipant($game, (int) $team['id']);

        if (!$isAdmin && !$isTeam) {
            flash_set('error', $deniedMessage);
            redirect($returnTo);
            return null;
        }

        if ($isAdmin) {
            return ['admin', (int) $admin['id'], $admin['display_name']];
        }
        return ['team', (int) $team['id'], $team['name']];
    }
}
