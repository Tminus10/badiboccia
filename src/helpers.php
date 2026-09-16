<?php

function app_config(): array
{
    static $config = null;
    if ($config === null) {
        $config = require __DIR__ . '/../config/config.php';
    }
    return $config;
}

function base_path(): string
{
    return app_config()['app']['base_path'] ?? '';
}

function url(string $path = ''): string
{
    return rtrim(base_path(), '/') . $path;
}

/**
 * Like url(), but for a static file under public/ -- appends a ?v= cache-buster derived from
 * the file's own mtime, so a deploy that changes e.g. style.css is picked up immediately
 * instead of serving whatever a returning browser already cached under that same URL.
 */
function asset_url(string $path): string
{
    $absolute = __DIR__ . '/../public/' . ltrim($path, '/');
    $version = is_file($absolute) ? (string) filemtime($absolute) : '0';
    return url('/' . ltrim($path, '/')) . '?v=' . $version;
}

/** Escapes a value for safe HTML output. */
function h(?string $value): string
{
    return htmlspecialchars($value ?? '', ENT_QUOTES, 'UTF-8');
}

function csrf_token(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function csrf_field(): string
{
    return '<input type="hidden" name="csrf" value="' . h(csrf_token()) . '">';
}

function csrf_check(): bool
{
    $token = $_POST['csrf'] ?? '';
    return is_string($token) && !empty($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

function flash_set(string $type, string $message): void
{
    $_SESSION['flash'][] = ['type' => $type, 'message' => $message];
}

function flash_take(): array
{
    $flashes = $_SESSION['flash'] ?? [];
    unset($_SESSION['flash']);
    return $flashes;
}

function redirect(string $path): void
{
    header('Location: ' . url($path));
    exit;
}

/** Renders a view file with the shared layout. $template is relative to src/Views without .php */
function render(string $template, array $data = []): void
{
    extract($data, EXTR_SKIP);
    ob_start();
    require __DIR__ . "/Views/$template.php";
    $content = ob_get_clean();
    require __DIR__ . '/Views/layout.php';
}

/** Renders a view partial without the layout, for AJAX responses. */
function render_partial(string $template, array $data = []): string
{
    extract($data, EXTR_SKIP);
    ob_start();
    require __DIR__ . "/Views/$template.php";
    return ob_get_clean();
}

function json_response(array $data, int $status = 200): void
{
    http_response_code($status);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit;
}

/** Returns "Player1 & Player2" only if it differs from the team name (avoids showing the same text twice). */
function team_players_subtitle(array $team): ?string
{
    $players = trim($team['player1']) . ' & ' . trim($team['player2']);
    if (mb_strtolower(trim($team['name'])) === mb_strtolower($players)) {
        return null;
    }
    return $players;
}

function initials(string $name): string
{
    $parts = preg_split('/\s*&\s*|\s+/', trim($name)) ?: [];
    $parts = array_values(array_filter($parts, fn ($p) => $p !== '' && $p !== '&'));
    if (count($parts) === 0) {
        return '?';
    }
    if (count($parts) === 1) {
        return mb_strtoupper(mb_substr($parts[0], 0, 2));
    }
    return mb_strtoupper(mb_substr($parts[0], 0, 1) . mb_substr($parts[count($parts) - 1], 0, 1));
}

/**
 * Formats the set score. Pass $perspectiveTeamId to orient it as "this team : opponent"
 * (used on a team's own page, where only the opponent's name is shown); leave it null
 * for an absolute "team A : team B" score (used wherever both team names are shown).
 */
function format_result(array $game, ?int $perspectiveTeamId = null): string
{
    if (!Game::isComplete($game)) {
        return '–';
    }
    if ($perspectiveTeamId !== null && (int) $game['team_b_id'] === $perspectiveTeamId) {
        return $game['sets_b'] . ':' . $game['sets_a'];
    }
    return $game['sets_a'] . ':' . $game['sets_b'];
}

function render_bracket_slot(?array $team, ?int $seasonId = null): string
{
    if ($team === null) {
        return '<span class="bracket-slot bracket-slot-empty">?</span>';
    }
    return render_partial('partials/team-dot', ['team' => $team, 'seasonId' => $seasonId]);
}

/**
 * A team's page URL. Pass the season being browsed so the page shows that team's games in
 * that season only, instead of defaulting to its current season or (with 'all') its full
 * cross-season history.
 */
function team_url(int $teamId, int|string|null $seasonId = null): string
{
    if ($seasonId === null) {
        return url('/team/' . $teamId);
    }
    return url('/team/' . $teamId) . '?season=' . $seasonId;
}

function team_photo_url(array $team): ?string
{
    if (empty($team['photo_path'])) {
        return null;
    }
    $abs = __DIR__ . '/../public/' . $team['photo_path'];
    $v = @filemtime($abs) ?: time();
    return url('/' . $team['photo_path']) . '?v=' . $v;
}

function format_date_ch(?string $date): string
{
    if (empty($date)) {
        return '';
    }
    $ts = strtotime($date);
    return $ts ? date('d.m.Y', $ts) : '';
}
