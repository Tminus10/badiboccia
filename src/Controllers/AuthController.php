<?php

final class AuthController
{
    public static function teamLoginForm(array $params): void
    {
        $season = Season::current();
        $groupData = [];
        if ($season !== null) {
            foreach (Season::groups((int) $season['id']) as $group) {
                $groupData[] = [
                    'group' => $group,
                    'teams' => Team::byGroup((int) $group['id']),
                ];
            }
        }
        render('auth/team-login', [
            'season' => $season,
            'groupData' => $groupData,
            'error' => $_GET['error'] ?? null,
        ]);
    }

    public static function teamPinForm(array $params): void
    {
        $team = Team::find((int) $params['id']);
        if ($team === null) {
            http_response_code(404);
            render('404');
            return;
        }
        render('auth/team-pin', [
            'targetTeam' => $team,
            'error' => $_GET['error'] ?? null,
        ]);
    }

    public static function teamPinSubmit(array $params): void
    {
        if (!csrf_check()) {
            redirect('/login');
            return;
        }
        $teamId = (int) $params['id'];
        $team = Team::find($teamId);
        if ($team === null) {
            redirect('/login');
            return;
        }

        if (TeamAuth::isLockedOut($teamId)) {
            redirect('/login/team/' . $teamId . '?error=locked');
            return;
        }

        $pin = (string) ($_POST['pin'] ?? '');
        if (TeamAuth::attempt($teamId, $pin)) {
            flash_set('success', 'Willkommen, ' . $team['name'] . '!');
            $returnTo = $_POST['return_to'] ?? ('/team/' . $teamId);
            redirect($returnTo);
            return;
        }

        redirect('/login/team/' . $teamId . '?error=wrong');
    }

    public static function adminLoginForm(array $params): void
    {
        render('auth/admin-login', ['error' => $_GET['error'] ?? null]);
    }

    public static function adminLoginSubmit(array $params): void
    {
        if (!csrf_check()) {
            redirect('/admin/login');
            return;
        }
        $username = trim((string) ($_POST['username'] ?? ''));
        $password = (string) ($_POST['password'] ?? '');

        if (AdminAuth::attempt($username, $password)) {
            flash_set('success', 'Als Admin angemeldet.');
            redirect('/admin');
            return;
        }

        redirect('/admin/login?error=1');
    }

    public static function logout(array $params): void
    {
        TeamAuth::logout();
        AdminAuth::logout();
        flash_set('success', 'Abgemeldet.');
        $returnTo = $_POST['return_to'] ?? '/';
        redirect($returnTo);
    }
}
