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
        $stmt = Db::pdo()->prepare("SELECT * FROM games WHERE group_id = ? AND phase = 'group' ORDER BY id ASC");
        $stmt->execute([$groupId]);
        return $stmt->fetchAll();
    }

    public static function forTeam(int $teamId): array
    {
        $stmt = Db::pdo()->prepare(
            'SELECT * FROM games WHERE team_a_id = ? OR team_b_id = ? ORDER BY phase ASC, id ASC'
        );
        $stmt->execute([$teamId, $teamId]);
        return $stmt->fetchAll();
    }

    public static function bracketGames(int $seasonId): array
    {
        $stmt = Db::pdo()->prepare(
            "SELECT * FROM games WHERE season_id = ? AND phase != 'group' ORDER BY FIELD(phase, 'qf','sf','final'), slot_index ASC"
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
        if (!self::isComplete($game)) {
            return null;
        }
        return (int) $game['sets_a'] > (int) $game['sets_b'] ? (int) $game['team_a_id'] : (int) $game['team_b_id'];
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

    /** Creates the empty QF/SF/Final skeleton for a season, wired via next_game_id, if it doesn't exist yet. */
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

        $insert->execute([$seasonId, 'qf', 1, $sf1Id, 'a']);
        $insert->execute([$seasonId, 'qf', 2, $sf1Id, 'b']);
        $insert->execute([$seasonId, 'qf', 3, $sf2Id, 'a']);
        $insert->execute([$seasonId, 'qf', 4, $sf2Id, 'b']);
    }

    public static function setQfTeam(int $gameId, string $slot, ?int $teamId): void
    {
        $column = $slot === 'a' ? 'team_a_id' : 'team_b_id';
        $stmt = Db::pdo()->prepare("UPDATE games SET $column = ? WHERE id = ?");
        $stmt->execute([$teamId, $gameId]);
    }

    public static function recordResult(int $gameId, int $setsA, int $setsB, ?string $playedDate, string $actorType, int $actorId, string $actorLabel): void
    {
        $pdo = Db::pdo();
        $game = self::find($gameId);
        if ($game === null) {
            throw new RuntimeException('Game not found');
        }

        $stmt = $pdo->prepare(
            'UPDATE games SET sets_a = ?, sets_b = ?, played_date = ?, updated_by_type = ?, updated_by_id = ? WHERE id = ?'
        );
        $stmt->execute([$setsA, $setsB, $playedDate, $actorType, $actorId, $gameId]);

        $audit = $pdo->prepare(
            'INSERT INTO audit_log (game_id, actor_type, actor_id, actor_label, old_sets_a, old_sets_b, old_played_date, new_sets_a, new_sets_b, new_played_date)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)'
        );
        $audit->execute([
            $gameId, $actorType, $actorId, $actorLabel,
            $game['sets_a'], $game['sets_b'], $game['played_date'],
            $setsA, $setsB, $playedDate,
        ]);

        if ($game['phase'] !== 'group' && $game['next_game_id'] !== null) {
            $winnerId = $setsA > $setsB ? (int) $game['team_a_id'] : (int) $game['team_b_id'];
            self::setQfTeam((int) $game['next_game_id'], $game['next_game_slot'], $winnerId);
        }
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
