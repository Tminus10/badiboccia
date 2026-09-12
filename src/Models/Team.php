<?php

final class Team
{
    public static function find(int $id): ?array
    {
        $stmt = Db::pdo()->prepare('SELECT * FROM teams WHERE id = ?');
        $stmt->execute([$id]);
        return $stmt->fetch() ?: null;
    }

    public static function byGroup(int $groupId): array
    {
        $stmt = Db::pdo()->prepare('SELECT * FROM teams WHERE group_id = ? ORDER BY name ASC');
        $stmt->execute([$groupId]);
        return $stmt->fetchAll();
    }

    public static function bySeason(int $seasonId): array
    {
        $stmt = Db::pdo()->prepare('SELECT * FROM teams WHERE season_id = ? ORDER BY name ASC');
        $stmt->execute([$seasonId]);
        return $stmt->fetchAll();
    }

    public static function countInSeason(int $seasonId): int
    {
        $stmt = Db::pdo()->prepare('SELECT COUNT(*) FROM teams WHERE season_id = ?');
        $stmt->execute([$seasonId]);
        return (int) $stmt->fetchColumn();
    }

    public static function create(int $seasonId, int $groupId, string $name, string $player1, string $player2, string $pin): int
    {
        $pdo = Db::pdo();
        $colorIndex = self::countInSeason($seasonId);
        $color = ColorAssigner::colorForIndex($colorIndex);

        $stmt = $pdo->prepare(
            'INSERT INTO teams (season_id, group_id, name, player1, player2, color_hex, pin) VALUES (?, ?, ?, ?, ?, ?, ?)'
        );
        $stmt->execute([$seasonId, $groupId, $name, $player1, $player2, $color, $pin]);

        return (int) $pdo->lastInsertId();
    }

    public static function update(int $id, string $name, string $player1, string $player2, int $groupId): void
    {
        $stmt = Db::pdo()->prepare(
            'UPDATE teams SET name = ?, player1 = ?, player2 = ?, group_id = ? WHERE id = ?'
        );
        $stmt->execute([$name, $player1, $player2, $groupId, $id]);
    }

    public static function setPhoto(int $id, string $relativePath): void
    {
        $stmt = Db::pdo()->prepare('UPDATE teams SET photo_path = ? WHERE id = ?');
        $stmt->execute([$relativePath, $id]);
    }

    public static function resetPin(int $id, string $newPin): void
    {
        $stmt = Db::pdo()->prepare('UPDATE teams SET pin = ? WHERE id = ?');
        $stmt->execute([$newPin, $id]);
    }

    public static function delete(int $id): void
    {
        $stmt = Db::pdo()->prepare('DELETE FROM teams WHERE id = ?');
        $stmt->execute([$id]);
    }

    public static function verifyPin(array $team, string $pin): bool
    {
        return hash_equals($team['pin'], $pin);
    }
}
