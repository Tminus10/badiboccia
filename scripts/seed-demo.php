<?php
// Creates the demo season, or resets it to a fresh state: a playable sandbox on the live site
// for the App Store review, the App Store screenshots and anyone who wants to try the app.
// Run it nightly by cron (see README), and by hand whenever a clean demo is needed.
//
// Usage: php scripts/seed-demo.php [YYYY-MM-DD]
//   The optional date stands in for "today" (default: today in Swiss time), e.g. to prepare
//   screenshots for a specific day.
//
// What it builds, relative to "today": the group stage and the quarter-finals are played, the
// first semi-final is on tonight, the second one in two days, and the third-place game and the
// final in a week -- so the app has recent results, a game today, upcoming games and a half
// finished bracket. Results are the same on every run (fixed random seed), only the dates move.
//
// The demo teams keep their ids across resets (so the app stays logged in), but get their
// names, players, colors, photos and the public PIN (Season::DEMO_PIN) back. Demo seasons and
// teams are hidden in the league everywhere (see the is_demo columns, migrations/005_demo_season.sql).

require __DIR__ . '/../src/autoload.php';
require __DIR__ . '/../src/helpers.php';

/**
 * group => [name, player1, player2, photo file in scripts/demo-photos/ or null].
 * Changing a name here creates a new team on the next run; teams no longer listed are deleted.
 */
const DEMO_TEAMS = [
    'A' => [
        ['Rösti & Rivella', 'Rösti', 'Rivella', 'roesti-rivella.jpg'],
        ['Heidi & Geissenpeter', 'Heidi', 'Geissenpeter', null],
        ['Pallino & Pallina', 'Pallino', 'Pallina', 'pallino-pallina.jpg'],
        ['Kugelblitz & Donnerwetter', 'Kugelblitz', 'Donnerwetter', null],
    ],
    'B' => [
        ['Fondue & Fendant', 'Fondue', 'Fendant', null],
        ['Matterhorn & Toblerone', 'Matterhorn', 'Toblerone', 'matterhorn-toblerone.jpg'],
        ['Gipfeli & Ovomaltine', 'Gipfeli', 'Ovomaltine', null],
        ['Zytglogge & Bärengraben', 'Zytglogge', 'Bärengraben', null],
    ],
    'C' => [
        ['Schoggi & Weggli', 'Schoggi', 'Weggli', 'schoggi-weggli.jpg'],
        ['Cervelat & Bürli', 'Cervelat', 'Bürli', null],
        ['Tell & Apfel', 'Tell', 'Apfel', 'tell-apfel.jpg'],
        ['Chuchichäschtli & Chäs', 'Chuchichäschtli', 'Chäs', null],
    ],
    'D' => [
        ['Kiesel & Kegel', 'Kiesel', 'Kegel', null],
        ['Glace & Gartenzwerg', 'Glace', 'Gartenzwerg', 'glace-gartenzwerg.jpg'],
        ['Sonnenhut & Sonnencreme', 'Sonnenhut', 'Sonnencreme', null],
        ['Badmeister & Bademeister', 'Badmeister', 'Bademeister', null],
    ],
];

$zone = new DateTimeZone('Europe/Zurich');
$today = isset($argv[1]) ? DateTimeImmutable::createFromFormat('!Y-m-d', $argv[1], $zone) : new DateTimeImmutable('today', $zone);
if ($today === false) {
    fwrite(STDERR, "Usage: php scripts/seed-demo.php [YYYY-MM-DD]\n");
    exit(1);
}
$day = fn (int $offset): string => $today->modify(($offset >= 0 ? '+' : '') . $offset . ' days')->format('Y-m-d');

$pdo = Db::pdo();
$pdo->beginTransaction();

// --- Season and groups ----------------------------------------------------------------------

$label = 'Demo Cup ' . $today->format('Y');
$season = Season::demo();
if ($season === null) {
    $seasonId = Season::create($label, (int) $today->format('Y'), count(DEMO_TEAMS));
    $pdo->prepare('UPDATE seasons SET is_demo = 1, is_current = 0 WHERE id = ?')->execute([$seasonId]);
} else {
    $seasonId = (int) $season['id'];
    Season::update($seasonId, $label, (int) $today->format('Y'));
}
$groupIds = [];
foreach (Season::groups($seasonId) as $group) {
    $groupIds[$group['name']] = (int) $group['id'];
}

// --- Teams ------------------------------------------------------------------------------------

$pdo->prepare('DELETE FROM games WHERE season_id = ?')->execute([$seasonId]); // audit log cascades
$pdo->prepare('DELETE FROM team_seasons WHERE season_id = ?')->execute([$seasonId]);

$existing = [];
foreach ($pdo->query('SELECT * FROM teams WHERE is_demo = 1')->fetchAll() as $team) {
    $existing[$team['name']] = $team;
}

$uploadDir = __DIR__ . '/../public/uploads/teams';
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0755, true);
}

$index = 0;
$teamIdsByGroup = [];
foreach (DEMO_TEAMS as $groupName => $teams) {
    foreach ($teams as [$name, $player1, $player2, $photo]) {
        $color = ColorAssigner::colorForIndex($index++);
        if (isset($existing[$name])) {
            $teamId = (int) $existing[$name]['id'];
            unset($existing[$name]);
            $pdo->prepare('UPDATE teams SET player1 = ?, player2 = ?, color_hex = ?, pin = ? WHERE id = ?')
                ->execute([$player1, $player2, $color, Season::DEMO_PIN, $teamId]);
        } else {
            $pdo->prepare('INSERT INTO teams (name, player1, player2, color_hex, pin, is_demo) VALUES (?, ?, ?, ?, ?, 1)')
                ->execute([$name, $player1, $player2, $color, Season::DEMO_PIN]);
            $teamId = (int) $pdo->lastInsertId();
        }

        // Back to the demo photo (or none), whatever was uploaded during the day.
        $target = "$uploadDir/team-$teamId.jpg";
        if ($photo !== null && is_file(__DIR__ . "/demo-photos/$photo")) {
            copy(__DIR__ . "/demo-photos/$photo", $target);
            Team::setPhoto($teamId, "uploads/teams/team-$teamId.jpg");
        } else {
            @unlink($target);
            $pdo->prepare('UPDATE teams SET photo_path = NULL WHERE id = ?')->execute([$teamId]);
        }

        Team::enroll($teamId, $seasonId, $groupIds[$groupName]);
        $teamIdsByGroup[$groupName][] = $teamId;
    }
}
foreach ($existing as $team) {
    @unlink("$uploadDir/team-{$team['id']}.jpg");
    Team::deleteCompletely((int) $team['id']);
}

$demoTeamIds = array_merge(...array_values($teamIdsByGroup));
$placeholders = implode(',', array_fill(0, count($demoTeamIds), '?'));
$pdo->prepare("DELETE FROM team_login_attempts WHERE team_id IN ($placeholders)")->execute($demoTeamIds);

// --- Games ------------------------------------------------------------------------------------

mt_srand(2026); // same results on every run
$scores = [[2, 0], [2, 1], [2, 1], [1, 2], [1, 2], [0, 2], [2, 0], [0, 2], [1, 1]];
$evening = ['18:00', '18:30', '19:00', '19:30', '20:00'];
$play = function (int $gameId, string $date, string $time, bool $knockout) use ($scores): void {
    do {
        [$a, $b] = $scores[mt_rand(0, count($scores) - 1)];
    } while ($knockout && $a === $b);
    Game::recordResult($gameId, $a, $b, $date, $time, 'admin', 0, 'Demo');
};

// Group stage: six games per group, spread over the six weeks before the quarter-finals.
foreach ($teamIdsByGroup as $groupName => $teamIds) {
    Game::createGroupFixtures($seasonId, $groupIds[$groupName], $teamIds);
    foreach (Game::forGroup($groupIds[$groupName]) as $i => $game) {
        $play((int) $game['id'], $day(-55 + $i * 7 + mt_rand(0, 3)), $evening[mt_rand(0, count($evening) - 1)], false);
    }
}

// Quarter-finals: the top two of each group, crossed like the admin's suggestion, played last week.
Game::ensureBracketSkeleton($seasonId);
$qualifiers = array_map(fn (array $standings): array => array_slice($standings, 0, 2), BracketService::qualifierCandidates($seasonId));
$pairings = BracketService::recommendedQfPairings($qualifiers);
$phases = BracketService::byPhase(Game::bracketGames($seasonId));
foreach ($phases['qf'] as $game) {
    [$teamA, $teamB] = $pairings[(int) $game['slot_index']];
    Game::setQfTeam((int) $game['id'], 'a', $teamA);
    Game::setQfTeam((int) $game['id'], 'b', $teamB);
    $play((int) $game['id'], $day(-9 + (int) $game['slot_index']), (int) $game['slot_index'] % 2 === 1 ? '18:00' : '19:30', true);
}

// Semi-finals tonight and in two days, without results; third place and final in a week.
$phases = BracketService::byPhase(Game::bracketGames($seasonId));
$schedule = function (array $game, string $date, string $time): void {
    $pdo = Db::pdo();
    $pdo->prepare('UPDATE games SET played_date = ?, game_time = ? WHERE id = ?')->execute([$date, $time, $game['id']]);
};
foreach ($phases['sf'] as $game) {
    $schedule($game, (int) $game['slot_index'] === 1 ? $day(0) : $day(2), (int) $game['slot_index'] === 1 ? '18:30' : '19:00');
}
$schedule($phases['third'][0], $day(7), '10:00');
$schedule($phases['final'][0], $day(7), '14:00');

$pdo->prepare('DELETE FROM audit_log WHERE game_id IN (SELECT id FROM games WHERE season_id = ?)')->execute([$seasonId]);
$pdo->commit();

echo "Demo season #$seasonId \"$label\" reset for {$today->format('Y-m-d')}: " . count($demoTeamIds) . " teams, PIN " . Season::DEMO_PIN . ".\n";
