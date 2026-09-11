<?php

final class TeamAuth
{
    private const MAX_ATTEMPTS = 5;
    private const LOCKOUT_MINUTES = 15;

    public static function current(): ?array
    {
        if (empty($_SESSION['team_id'])) {
            return null;
        }
        return Team::find((int) $_SESSION['team_id']);
    }

    public static function isLockedOut(int $teamId): bool
    {
        $stmt = Db::pdo()->prepare(
            "SELECT COUNT(*) FROM team_login_attempts
             WHERE team_id = ? AND success = 0 AND attempted_at > (NOW() - INTERVAL ? MINUTE)"
        );
        $stmt->execute([$teamId, self::LOCKOUT_MINUTES]);
        return (int) $stmt->fetchColumn() >= self::MAX_ATTEMPTS;
    }

    public static function attempt(int $teamId, string $pin): bool
    {
        $team = Team::find($teamId);
        if ($team === null) {
            return false;
        }

        if (self::isLockedOut($teamId)) {
            return false;
        }

        $ok = Team::verifyPin($team, $pin);

        $stmt = Db::pdo()->prepare('INSERT INTO team_login_attempts (team_id, success) VALUES (?, ?)');
        $stmt->execute([$teamId, $ok ? 1 : 0]);

        if ($ok) {
            $_SESSION['team_id'] = $teamId;
        }

        return $ok;
    }

    public static function logout(): void
    {
        unset($_SESSION['team_id']);
    }

    public static function canEditGame(array $game): bool
    {
        $team = self::current();
        return $team !== null && Game::isParticipant($game, (int) $team['id']);
    }
}
