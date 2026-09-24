<?php

final class Game
{
    public static function find(int $id): ?array
    {
        $stmt = Db::pdo()->prepare('SELECT * FROM games WHERE id = ?');
        $stmt->execute([$id]);
        return $stmt->fetch() ?: null;
    }

    public static function forGroup(int $groupId): array
    {
        $stmt = Db::pdo()->prepare(
            "SELECT * FROM games WHERE group_id = ? AND phase = 'group'
             ORDER BY (played_date IS NULL) ASC, played_date ASC, id ASC"
        );
        $stmt->execute([$groupId]);
        return $stmt->fetchAll();
    }

    /** A team's full history, oldest season first; each season's games grouped together and ordered group -> qf -> sf -> third -> final within it. */
    public static function forTeam(int $teamId): array
    {
        $stmt = Db::pdo()->prepare(
            "SELECT games.* FROM games
             JOIN seasons ON seasons.id = games.season_id
             WHERE games.team_a_id = ? OR games.team_b_id = ?
             ORDER BY seasons.year ASC, FIELD(games.phase, 'group','qf','sf','third','final'),
                      (games.played_date IS NULL) ASC, games.played_date ASC, games.id ASC"
        );
        $stmt->execute([$teamId, $teamId]);
        return $stmt->fetchAll();
    }

    /** A team's games within one specific season only. */
    public static function forTeamInSeason(int $teamId, int $seasonId): array
    {
        $stmt = Db::pdo()->prepare(
            "SELECT * FROM games WHERE season_id = ? AND (team_a_id = ? OR team_b_id = ?)
             ORDER BY FIELD(phase, 'group','qf','sf','third','final'), (played_date IS NULL) ASC, played_date ASC, id ASC"
        );
        $stmt->execute([$seasonId, $teamId, $teamId]);
        return $stmt->fetchAll();
    }

    public static function bracketGames(int $seasonId): array
    {
        $stmt = Db::pdo()->prepare(
            "SELECT * FROM games WHERE season_id = ? AND phase != 'group' ORDER BY FIELD(phase, 'qf','sf','third','final'), slot_index ASC"
        );
        $stmt->execute([$seasonId]);
        return $stmt->fetchAll();
    }

    public static function isParticipant(array $game, int $teamId): bool
    {
        return (int) $game['team_a_id'] === $teamId || (int) $game['team_b_id'] === $teamId;
    }

    public static function isComplete(array $game): bool
    {
        return $game['sets_a'] !== null && $game['sets_b'] !== null;
    }

    public static function winnerTeamId(array $game): ?int
    {
        if (!self::isComplete($game) || $game['sets_a'] === $game['sets_b']) {
            return null;
        }
        return (int) $game['sets_a'] > (int) $game['sets_b'] ? (int) $game['team_a_id'] : (int) $game['team_b_id'];
    }

    public static function loserTeamId(array $game): ?int
    {
        if (!self::isComplete($game) || $game['sets_a'] === $game['sets_b']) {
            return null;
        }
        return (int) $game['sets_a'] > (int) $game['sets_b'] ? (int) $game['team_b_id'] : (int) $game['team_a_id'];
    }

    /** Creates the round-robin group-phase fixtures for a set of teams. */
    public static function createGroupFixtures(int $seasonId, int $groupId, array $teamIds): void
    {
        $pdo = Db::pdo();
        $stmt = $pdo->prepare(
            "INSERT INTO games (season_id, phase, group_id, team_a_id, team_b_id) VALUES (?, 'group', ?, ?, ?)"
        );
        foreach (RoundRobinScheduler::generate($teamIds) as [$a, $b]) {
            $stmt->execute([$seasonId, $groupId, $a, $b]);
        }
    }

    /** Creates the empty QF/SF/Final/third-place skeleton for a season, wired via next_game_id, if it doesn't exist yet. */
    public static function ensureBracketSkeleton(int $seasonId): void
    {
        $pdo = Db::pdo();
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM games WHERE season_id = ? AND phase != 'group'");
        $stmt->execute([$seasonId]);
        if ((int) $stmt->fetchColumn() > 0) {
            return;
        }

        $insert = $pdo->prepare(
            "INSERT INTO games (season_id, phase, slot_index, next_game_id, next_game_slot) VALUES (?, ?, ?, ?, ?)"
        );

        $insert->execute([$seasonId, 'final', 1, null, null]);
        $finalId = (int) $pdo->lastInsertId();

        $insert->execute([$seasonId, 'sf', 1, $finalId, 'a']);
        $sf1Id = (int) $pdo->lastInsertId();
        $insert->execute([$seasonId, 'sf', 2, $finalId, 'b']);
        $sf2Id = (int) $pdo->lastInsertId();

        // Fed by the two SF losers -- see recordResult().
        $insert->execute([$seasonId, 'third', 1, null, null]);

        $insert->execute([$seasonId, 'qf', 1, $sf1Id, 'a']);
        $insert->execute([$seasonId, 'qf', 2, $sf1Id, 'b']);
        $insert->execute([$seasonId, 'qf', 3, $sf2Id, 'a']);
        $insert->execute([$seasonId, 'qf', 4, $sf2Id, 'b']);
    }

    /** The season's "Spiel um Platz 3" game, fed by the two SF losers, or null if not created yet. */
    public static function thirdPlaceGame(int $seasonId): ?array
    {
        $stmt = Db::pdo()->prepare("SELECT * FROM games WHERE season_id = ? AND phase = 'third' LIMIT 1");
        $stmt->execute([$seasonId]);
        return $stmt->fetch() ?: null;
    }

    public static function setQfTeam(int $gameId, string $slot, ?int $teamId): void
    {
        $column = $slot === 'a' ? 'team_a_id' : 'team_b_id';
        $stmt = Db::pdo()->prepare("UPDATE games SET $column = ? WHERE id = ?");
        $stmt->execute([$teamId, $gameId]);
    }

    public static function recordResult(int $gameId, int $setsA, int $setsB, ?string $playedDate, ?string $gameTime, string $actorType, int $actorId, string $actorLabel): void
    {
        $pdo = Db::pdo();
        $game = self::find($gameId);
        if ($game === null) {
            throw new RuntimeException('Game not found');
        }

        $stmt = $pdo->prepare(
            'UPDATE games SET sets_a = ?, sets_b = ?, played_date = ?, game_time = ?, updated_by_type = ?, updated_by_id = ? WHERE id = ?'
        );
        $stmt->execute([$setsA, $setsB, $playedDate, $gameTime, $actorType, $actorId, $gameId]);

        $audit = $pdo->prepare(
            'INSERT INTO audit_log (game_id, actor_type, actor_id, actor_label, old_sets_a, old_sets_b, old_played_date, new_sets_a, new_sets_b, new_played_date)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)'
        );
        $audit->execute([
            $gameId, $actorType, $actorId, $actorLabel,
            $game['sets_a'], $game['sets_b'], $game['played_date'],
            $setsA, $setsB, $playedDate,
        ]);

        if ($game['phase'] !== 'group' && $game['next_game_id'] !== null && $setsA !== $setsB) {
            $winnerId = $setsA > $setsB ? (int) $game['team_a_id'] : (int) $game['team_b_id'];
            self::setQfTeam((int) $game['next_game_id'], $game['next_game_slot'], $winnerId);
        }

        if ($game['phase'] === 'sf' && $setsA !== $setsB) {
            $loserId = $setsA > $setsB ? (int) $game['team_b_id'] : (int) $game['team_a_id'];
            $thirdPlace = self::thirdPlaceGame((int) $game['season_id']);
            if ($thirdPlace !== null) {
                $slot = (int) $game['slot_index'] === 1 ? 'a' : 'b';
                self::setQfTeam((int) $thirdPlace['id'], $slot, $loserId);
            }
        }
    }

    /** Sets a scheduled/played date (and optional time) without recording a result (sets_a/sets_b stay untouched). */
    public static function scheduleDate(int $gameId, string $date, ?string $time, string $actorType, int $actorId, string $actorLabel): void
    {
        $pdo = Db::pdo();
        $game = self::find($gameId);
        if ($game === null) {
            throw new RuntimeException('Game not found');
        }

        $stmt = $pdo->prepare('UPDATE games SET played_date = ?, game_time = ?, updated_by_type = ?, updated_by_id = ? WHERE id = ?');
        $stmt->execute([$date, $time, $actorType, $actorId, $gameId]);

        $audit = $pdo->prepare(
            'INSERT INTO audit_log (game_id, actor_type, actor_id, actor_label, old_sets_a, old_sets_b, old_played_date, new_sets_a, new_sets_b, new_played_date)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)'
        );
        $audit->execute([
            $gameId, $actorType, $actorId, $actorLabel,
            $game['sets_a'], $game['sets_b'], $game['played_date'],
            $game['sets_a'], $game['sets_b'], $date,
        ]);
    }

    /** Every game in a season that has a scheduled/played date and a known opponent, earliest first, for the calendar. */
    public static function scheduledForSeason(int $seasonId): array
    {
        $stmt = Db::pdo()->prepare(
            "SELECT * FROM games
             WHERE season_id = ? AND played_date IS NOT NULL AND team_a_id IS NOT NULL AND team_b_id IS NOT NULL
             ORDER BY played_date ASC, (game_time IS NULL) ASC, game_time ASC, id ASC"
        );
        $stmt->execute([$seasonId]);
        return $stmt->fetchAll();
    }

    /** Every scheduled/played game across every season, earliest first -- backs the all-seasons .ics subscription feed. */
    public static function scheduledAll(): array
    {
        $stmt = Db::pdo()->query(
            'SELECT * FROM games
             WHERE played_date IS NOT NULL AND team_a_id IS NOT NULL AND team_b_id IS NOT NULL
             ORDER BY played_date ASC, (game_time IS NULL) ASC, game_time ASC, id ASC'
        );
        return $stmt->fetchAll();
    }

    /** Count of games in a season whose opponents are known but that still have no scheduled date. */
    public static function countUnscheduled(int $seasonId): int
    {
        $stmt = Db::pdo()->prepare(
            'SELECT COUNT(*) FROM games WHERE season_id = ? AND played_date IS NULL AND team_a_id IS NOT NULL AND team_b_id IS NOT NULL'
        );
        $stmt->execute([$seasonId]);
        return (int) $stmt->fetchColumn();
    }

    public static function recentAuditLog(int $seasonId, int $limit = 50): array
    {
        $stmt = Db::pdo()->prepare(
            'SELECT audit_log.*, games.phase, games.season_id FROM audit_log
             JOIN games ON games.id = audit_log.game_id
             WHERE games.season_id = ?
             ORDER BY audit_log.changed_at DESC LIMIT ' . (int) $limit
        );
        $stmt->execute([$seasonId]);
        return $stmt->fetchAll();
    }
}
