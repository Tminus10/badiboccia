<?php
// Applies one reviewed data changeset (a file under data-changes/) to this environment's database,
// all-or-nothing in a single transaction, and records it in data_changesets so it can never be
// applied twice. Games are looked up by season year + phase + team names, never by id, so the
// same changeset can be replayed on PROD after it was applied to DEV. Every change first checks the
// value the changeset expects to find; if any row differs, nothing is written (see --allow-skips).
//
// Usage: php scripts/apply-changeset.php <changeset.php> [--dry-run] [--allow-skips]

require __DIR__ . '/../src/autoload.php';

/** The API a changeset file's 'apply' callback works with. */
final class Changeset
{
    /** @var string[] */
    public array $report = [];
    public int $changed = 0;
    public int $skipped = 0;

    public function __construct(private PDO $pdo, private string $actorLabel)
    {
    }

    /**
     * Sets played_date/game_time of one game, identified by season year, phase and the two team
     * names (in either order). $expect lists the values the game must currently have, with sets
     * given in the order of $teamA/$teamB: ['sets' => [1, 2], 'played_date' => null, 'game_time' => null].
     */
    public function setDate(int $year, string $phase, string $teamA, string $teamB, string $date, ?string $time, array $expect, string $source): void
    {
        $label = "$year $phase $teamA – $teamB";
        $game = $this->findGame($year, $phase, $teamA, $teamB, $label);
        if ($game === null) {
            return;
        }

        $mismatch = $this->mismatch($game, $expect);
        if ($mismatch !== null) {
            $this->skip("#{$game['id']} $label: $mismatch");
            return;
        }

        $newTime = $time === null ? null : (strlen($time) === 5 ? "$time:00" : $time);
        $this->pdo->prepare('UPDATE games SET played_date = ?, game_time = ? WHERE id = ?')
            ->execute([$date, $newTime, $game['id']]);
        $this->audit($game, $game['sets_a'], $game['sets_b'], $date);

        $old = ($game['played_date'] ?? '–') . ' ' . ($game['game_time'] ?? '');
        $this->changed++;
        $this->report[] = "OK   #{$game['id']} $label: date " . trim($old) . " -> $date " . ($newTime ?? '') . "  [$source]";
    }

    /**
     * Records a result (and optionally date/time) the same way the admin UI does (Game::recordResult),
     * so knockout winners are moved on and SF losers fill the 3rd-place game. Sets are given in the
     * order of $teamA/$teamB.
     */
    public function setResult(int $year, string $phase, string $teamA, string $teamB, int $setsA, int $setsB, ?string $date, ?string $time, array $expect, string $source): void
    {
        $label = "$year $phase $teamA – $teamB";
        $game = $this->findGame($year, $phase, $teamA, $teamB, $label);
        if ($game === null) {
            return;
        }
        $mismatch = $this->mismatch($game, $expect);
        if ($mismatch !== null) {
            $this->skip("#{$game['id']} $label: $mismatch");
            return;
        }

        [$dbA, $dbB] = $game['swapped'] ? [$setsB, $setsA] : [$setsA, $setsB];
        $newTime = $time === null ? null : (strlen($time) === 5 ? "$time:00" : $time);
        Game::recordResult((int) $game['id'], $dbA, $dbB, $date, $newTime, 'import', 0, $this->actorLabel);

        $this->changed++;
        $this->report[] = "OK   #{$game['id']} $label: result $setsA:$setsB" . ($date !== null ? ", date $date " . ($newTime ?? '') : '') . "  [$source]";
    }

    /** Creates a season with its groups (Season::create) unless one with that year already exists. */
    public function createSeason(string $label, int $year, int $groupCount): void
    {
        if ($this->seasonId($year) !== null) {
            $this->skip("season $year already exists");
            return;
        }
        $id = Season::create($label, $year, $groupCount);
        $this->changed++;
        $this->report[] = "OK   season #$id \"$label\" ($year, $groupCount groups)";
    }

    /** Creates a team with new PIN and color (Team::create) unless a team with that name exists. */
    public function createTeam(string $name, string $player1, string $player2, string $source): void
    {
        if ($this->teamId($name) !== null) {
            $this->skip("team \"$name\" already exists");
            return;
        }
        $id = Team::create($name, $player1, $player2);
        $this->changed++;
        $this->report[] = "OK   team #$id \"$name\" ($player1 / $player2)  [$source]";
    }

    /** Enrolls an existing team into a season's group. */
    public function enroll(int $year, string $groupName, string $teamName): void
    {
        $seasonId = $this->seasonId($year);
        $teamId = $this->teamId($teamName);
        $groupId = $seasonId === null ? null : $this->groupId($seasonId, $groupName);
        if ($teamId === null || $groupId === null) {
            $this->skip("enroll $teamName in $year/$groupName: team or group not found");
            return;
        }
        Team::enroll($teamId, $seasonId, $groupId);
        $this->changed++;
        $this->report[] = "OK   enrolled #$teamId $teamName in $year group $groupName";
    }

    /** Creates a group's round-robin fixtures exactly as the admin button does, if it has none yet. */
    public function generateFixtures(int $year, string $groupName): void
    {
        $seasonId = $this->seasonId($year);
        $groupId = $seasonId === null ? null : $this->groupId($seasonId, $groupName);
        if ($groupId === null) {
            $this->skip("fixtures $year/$groupName: group not found");
            return;
        }
        if (count(Game::forGroup($groupId)) > 0) {
            $this->skip("fixtures $year/$groupName: group already has games");
            return;
        }
        $teamIds = array_map(fn ($t) => (int) $t['id'], Team::byGroup($groupId));
        Game::createGroupFixtures($seasonId, $groupId, $teamIds);
        $this->changed++;
        $this->report[] = "OK   " . count(RoundRobinScheduler::generate($teamIds)) . " fixtures for $year group $groupName";
    }

    /**
     * Puts two teams into an empty knockout game (Game::ensureBracketSkeleton creates the bracket if
     * needed). For QFs this is the admin's pairing; for later rounds it is only used when no score is
     * known to move the winner on automatically.
     */
    public function setBracketTeams(int $year, string $phase, int $slot, string $teamA, string $teamB, string $source): void
    {
        $seasonId = $this->seasonId($year);
        if ($seasonId === null) {
            $this->skip("bracket $year $phase$slot: season not found");
            return;
        }
        Game::ensureBracketSkeleton($seasonId);
        $stmt = $this->pdo->prepare('SELECT * FROM games WHERE season_id = ? AND phase = ? AND slot_index = ?');
        $stmt->execute([$seasonId, $phase, $slot]);
        $game = $stmt->fetch();
        $a = $this->teamId($teamA);
        $b = $this->teamId($teamB);
        if (!$game || $a === null || $b === null) {
            $this->skip("bracket $year $phase$slot: game or team not found");
            return;
        }
        foreach (['team_a_id' => $a, 'team_b_id' => $b] as $column => $teamId) {
            if ($game[$column] !== null && (int) $game[$column] !== $teamId) {
                $this->skip("#{$game['id']} $year $phase$slot: $column is already #{$game[$column]}");
                return;
            }
        }
        Game::setQfTeam((int) $game['id'], 'a', $a);
        Game::setQfTeam((int) $game['id'], 'b', $b);
        $this->changed++;
        $this->report[] = "OK   #{$game['id']} $year $phase$slot: $teamA – $teamB  [$source]";
    }

    /** Swaps one team of an unplayed knockout game for another, only if the expected team is still there. */
    public function replaceBracketTeam(int $year, string $phase, int $slot, string $expectedTeam, string $newTeam, string $source): void
    {
        $label = "$year $phase$slot";
        $seasonId = $this->seasonId($year);
        $old = $this->teamId($expectedTeam);
        $new = $this->teamId($newTeam);
        $stmt = $this->pdo->prepare('SELECT * FROM games WHERE season_id = ? AND phase = ? AND slot_index = ?');
        $stmt->execute([$seasonId, $phase, $slot]);
        $game = $stmt->fetch();
        if (!$game || $old === null || $new === null) {
            $this->skip("$label: game or team not found");
            return;
        }
        if ($game['sets_a'] !== null || $game['sets_b'] !== null) {
            $this->skip("#{$game['id']} $label: already has a result");
            return;
        }
        $side = (int) $game['team_a_id'] === $old ? 'a' : ((int) $game['team_b_id'] === $old ? 'b' : null);
        if ($side === null) {
            $this->skip("#{$game['id']} $label: $expectedTeam is not in this game");
            return;
        }
        Game::setQfTeam((int) $game['id'], $side, $new);
        $this->changed++;
        $this->report[] = "OK   #{$game['id']} $label: $expectedTeam -> $newTeam  [$source]";
    }

    /**
     * Merges a duplicate team into another: moves its season enrolments, games, game-update
     * attributions and login attempts over, renames the surviving team and deletes the duplicate.
     * The survivor keeps its own PIN, color and photo. Refused if both teams are in the same season.
     */
    public function mergeTeam(string $duplicate, string $survivor, string $newName, string $player1, string $player2, string $source): void
    {
        $from = $this->teamId($duplicate);
        $into = $this->teamId($survivor);
        if ($from === null || $into === null || $from === $into) {
            $this->skip("merge $duplicate -> $survivor: team not found");
            return;
        }
        $overlap = $this->pdo->prepare(
            'SELECT COUNT(*) FROM team_seasons a JOIN team_seasons b ON a.season_id = b.season_id WHERE a.team_id = ? AND b.team_id = ?'
        );
        $overlap->execute([$from, $into]);
        if ((int) $overlap->fetchColumn() > 0) {
            $this->skip("merge $duplicate -> $survivor: both teams are enrolled in the same season");
            return;
        }

        $moved = [];
        foreach ([
            'enrolments' => 'UPDATE team_seasons SET team_id = ? WHERE team_id = ?',
            'games (team A)' => 'UPDATE games SET team_a_id = ? WHERE team_a_id = ?',
            'games (team B)' => 'UPDATE games SET team_b_id = ? WHERE team_b_id = ?',
            'game updates' => "UPDATE games SET updated_by_id = ? WHERE updated_by_type = 'team' AND updated_by_id = ?",
            'login attempts' => 'UPDATE team_login_attempts SET team_id = ? WHERE team_id = ?',
        ] as $what => $sql) {
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([$into, $from]);
            $moved[] = "{$stmt->rowCount()} $what";
        }
        Team::update($into, $newName, $player1, $player2);
        $this->pdo->prepare('DELETE FROM teams WHERE id = ?')->execute([$from]);

        $this->changed++;
        $this->report[] = "OK   merged #$from \"$duplicate\" into #$into \"$survivor\" -> \"$newName\" ($player1 / $player2); moved " . implode(', ', $moved) . "; deleted #$from  [$source]";
    }

    private function seasonId(int $year): ?int
    {
        $stmt = $this->pdo->prepare('SELECT id FROM seasons WHERE year = ?');
        $stmt->execute([$year]);
        $ids = $stmt->fetchAll(PDO::FETCH_COLUMN);
        return count($ids) === 1 ? (int) $ids[0] : null;
    }

    private function groupId(int $seasonId, string $name): ?int
    {
        $stmt = $this->pdo->prepare('SELECT id FROM team_groups WHERE season_id = ? AND name = ?');
        $stmt->execute([$seasonId, $name]);
        $id = $stmt->fetchColumn();
        return $id === false ? null : (int) $id;
    }

    private function teamId(string $name): ?int
    {
        $stmt = $this->pdo->prepare('SELECT id FROM teams WHERE name = ?');
        $stmt->execute([$name]);
        $ids = $stmt->fetchAll(PDO::FETCH_COLUMN);
        return count($ids) === 1 ? (int) $ids[0] : null;
    }

    private function findGame(int $year, string $phase, string $teamA, string $teamB, string $label): ?array
    {
        $stmt = $this->pdo->prepare(
            'SELECT g.*, ta.name AS team_a_name, tb.name AS team_b_name FROM games g
             JOIN seasons s ON s.id = g.season_id
             JOIN teams ta ON ta.id = g.team_a_id
             JOIN teams tb ON tb.id = g.team_b_id
             WHERE s.year = ? AND g.phase = ?
               AND ((ta.name = ? AND tb.name = ?) OR (ta.name = ? AND tb.name = ?))'
        );
        $stmt->execute([$year, $phase, $teamA, $teamB, $teamB, $teamA]);
        $rows = $stmt->fetchAll();
        if (count($rows) !== 1) {
            $this->skip("$label: expected exactly 1 game, found " . count($rows));
            return null;
        }
        $game = $rows[0];
        $game['swapped'] = $game['team_a_name'] !== $teamA;
        return $game;
    }

    /** Returns a description of the first expected value that doesn't match, or null if all match. */
    private function mismatch(array $game, array $expect): ?string
    {
        if (array_key_exists('sets', $expect)) {
            [$a, $b] = $expect['sets'];
            $actual = $game['swapped'] ? [$game['sets_b'], $game['sets_a']] : [$game['sets_a'], $game['sets_b']];
            $actual = array_map(fn ($v) => $v === null ? null : (int) $v, $actual);
            if ($actual !== [$a, $b]) {
                return 'sets are ' . json_encode($actual) . ', expected ' . json_encode([$a, $b]);
            }
        }
        foreach (['played_date', 'game_time'] as $field) {
            if (array_key_exists($field, $expect) && $game[$field] !== $expect[$field]) {
                return "$field is " . json_encode($game[$field]) . ', expected ' . json_encode($expect[$field]);
            }
        }
        return null;
    }

    private function audit(array $game, $newSetsA, $newSetsB, ?string $newDate): void
    {
        $this->pdo->prepare(
            'INSERT INTO audit_log (game_id, actor_type, actor_id, actor_label, old_sets_a, old_sets_b, old_played_date, new_sets_a, new_sets_b, new_played_date)
             VALUES (?, ?, 0, ?, ?, ?, ?, ?, ?, ?)'
        )->execute([
            $game['id'], 'import', $this->actorLabel,
            $game['sets_a'], $game['sets_b'], $game['played_date'],
            $newSetsA, $newSetsB, $newDate,
        ]);
    }

    private function skip(string $message): void
    {
        $this->skipped++;
        $this->report[] = "SKIP $message";
    }
}

$args = array_slice($argv, 1);
$dryRun = in_array('--dry-run', $args, true);
$allowSkips = in_array('--allow-skips', $args, true);
$files = array_values(array_filter($args, fn ($a) => !str_starts_with($a, '--')));
if (count($files) !== 1 || !is_file($files[0])) {
    fwrite(STDERR, "Usage: php scripts/apply-changeset.php <changeset.php> [--dry-run] [--allow-skips]\n");
    exit(1);
}

$definition = require $files[0];
foreach (['name', 'summary', 'actor_label', 'apply'] as $key) {
    if (!isset($definition[$key])) {
        fwrite(STDERR, "Changeset is missing '$key'.\n");
        exit(1);
    }
}

$pdo = Db::pdo();
$hasTable = (bool) $pdo->query("SHOW TABLES LIKE 'data_changesets'")->fetchColumn();
if (!$hasTable && !$dryRun) {
    fwrite(STDERR, "Table data_changesets is missing -- run migrations/003_data_changesets.sql first.\n");
    exit(1);
}
if (!$hasTable) {
    echo "Note: table data_changesets is missing (migration 003 not run yet); dry run continues.\n";
} else {
    $applied = $pdo->prepare('SELECT applied_at FROM data_changesets WHERE name = ?');
    $applied->execute([$definition['name']]);
    if ($at = $applied->fetchColumn()) {
        fwrite(STDERR, "Changeset '{$definition['name']}' was already applied on $at.\n");
        exit(1);
    }
}

$changeset = new Changeset($pdo, $definition['actor_label']);
$pdo->beginTransaction();
try {
    ($definition['apply'])($changeset);
} catch (Throwable $e) {
    $pdo->rollBack();
    fwrite(STDERR, "Error, nothing written: {$e->getMessage()}\n");
    exit(1);
}

echo "Changeset {$definition['name']}: {$changeset->changed} change(s), {$changeset->skipped} skipped\n";
echo implode("\n", $changeset->report), "\n";

if ($dryRun || ($changeset->skipped > 0 && !$allowSkips)) {
    $pdo->rollBack();
    echo $dryRun ? "Dry run, nothing written.\n" : "Skipped rows found, nothing written (re-run with --allow-skips to apply the rest).\n";
    exit($dryRun ? 0 : 2);
}

$pdo->prepare('INSERT INTO data_changesets (name, summary) VALUES (?, ?)')
    ->execute([$definition['name'], $definition['summary'] . "\n\n" . implode("\n", $changeset->report)]);
$pdo->commit();
echo "Applied.\n";
