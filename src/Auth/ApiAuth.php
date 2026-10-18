<?php

/**
 * Bearer-token auth for the JSON API (/api/v1), the app's counterpart to the session-based
 * TeamAuth. A team trades its PIN for a random token once (same lockout rules as the web
 * login); only the token's SHA-256 hash is stored, see migrations/004_api_tokens.sql.
 */
final class ApiAuth
{
    /** Creates a token for the team and returns the plain value -- the only time it's ever visible. */
    public static function issueToken(int $teamId, ?string $deviceLabel): string
    {
        $token = bin2hex(random_bytes(32));
        $stmt = Db::pdo()->prepare('INSERT INTO api_tokens (token_hash, team_id, device_label) VALUES (?, ?, ?)');
        $stmt->execute([hash('sha256', $token), $teamId, $deviceLabel !== null ? mb_substr($deviceLabel, 0, 100) : null]);
        return $token;
    }

    /** The team behind the request's bearer token, or null if there is none or it's unknown. */
    public static function currentTeam(): ?array
    {
        static $resolved = false;
        static $team = null;
        if ($resolved) {
            return $team;
        }
        $resolved = true;

        $token = self::requestToken();
        if ($token === null) {
            return null;
        }
        $hash = hash('sha256', $token);
        $stmt = Db::pdo()->prepare('SELECT team_id FROM api_tokens WHERE token_hash = ?');
        $stmt->execute([$hash]);
        $teamId = $stmt->fetchColumn();
        if ($teamId === false) {
            return null;
        }
        Db::pdo()->prepare('UPDATE api_tokens SET last_used_at = NOW() WHERE token_hash = ?')->execute([$hash]);
        $team = Team::find((int) $teamId);
        return $team;
    }

    /** Revokes the token the current request was made with (app logout). */
    public static function revokeCurrent(): void
    {
        $token = self::requestToken();
        if ($token !== null) {
            Db::pdo()->prepare('DELETE FROM api_tokens WHERE token_hash = ?')->execute([hash('sha256', $token)]);
        }
    }

    /** Logs the team out of the app on every device, e.g. after its PIN was reset. */
    public static function revokeAllForTeam(int $teamId): void
    {
        Db::pdo()->prepare('DELETE FROM api_tokens WHERE team_id = ?')->execute([$teamId]);
    }

    /**
     * The token from "Authorization: Bearer ..." -- or from X-Api-Token, since some
     * shared-hosting PHP setups (CGI/FastCGI) drop the Authorization header before PHP sees it.
     */
    private static function requestToken(): ?string
    {
        $header = $_SERVER['HTTP_AUTHORIZATION'] ?? $_SERVER['REDIRECT_HTTP_AUTHORIZATION'] ?? '';
        if (preg_match('/^Bearer\s+([0-9a-f]{64})$/i', trim((string) $header), $m)) {
            return strtolower($m[1]);
        }
        $fallback = trim((string) ($_SERVER['HTTP_X_API_TOKEN'] ?? ''));
        if (preg_match('/^[0-9a-f]{64}$/i', $fallback)) {
            return strtolower($fallback);
        }
        return null;
    }
}
