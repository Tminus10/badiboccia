<?php

final class Season
{
    public static function all(): array
    {
        return Db::pdo()->query('SELECT * FROM seasons ORDER BY year DESC, id DESC')->fetchAll();
    }

    public static function find(int $id): ?array
    {
        $stmt = Db::pdo()->prepare('SELECT * FROM seasons WHERE id = ?');
        $stmt->execute([$id]);
        return $stmt->fetch() ?: null;
    }

    public static function current(): ?array
    {
        $row = Db::pdo()->query('SELECT * FROM seasons WHERE is_current = 1 ORDER BY id DESC LIMIT 1')->fetch();
        return $row ?: null;
    }

    public static function create(string $label, int $year): int
    {
        $pdo = Db::pdo();
        $stmt = $pdo->prepare('INSERT INTO seasons (label, year) VALUES (?, ?)');
        $stmt->execute([$label, $year]);
        $seasonId = (int) $pdo->lastInsertId();

        $groupStmt = $pdo->prepare('INSERT INTO team_groups (season_id, name) VALUES (?, ?)');
        foreach (['A', 'B', 'C', 'D'] as $name) {
            $groupStmt->execute([$seasonId, $name]);
        }

        return $seasonId;
    }

    public static function update(int $id, string $label, int $year): void
    {
        $stmt = Db::pdo()->prepare('UPDATE seasons SET label = ?, year = ? WHERE id = ?');
        $stmt->execute([$label, $year, $id]);
    }

    public static function setCurrent(int $id): void
    {
        $pdo = Db::pdo();
        $pdo->exec('UPDATE seasons SET is_current = 0');
        $stmt = $pdo->prepare('UPDATE seasons SET is_current = 1 WHERE id = ?');
        $stmt->execute([$id]);
    }

    public static function deactivate(int $id): void
    {
        $stmt = Db::pdo()->prepare('UPDATE seasons SET is_current = 0 WHERE id = ?');
        $stmt->execute([$id]);
    }

    /** Permanently deletes a season and everything scoped to it (groups, team enrollments, games, audit log) via cascade. */
    public static function delete(int $id): void
    {
        $stmt = Db::pdo()->prepare('DELETE FROM seasons WHERE id = ?');
        $stmt->execute([$id]);
    }

    public static function groups(int $seasonId): array
    {
        $stmt = Db::pdo()->prepare('SELECT * FROM team_groups WHERE season_id = ? ORDER BY name ASC');
        $stmt->execute([$seasonId]);
        return $stmt->fetchAll();
    }
}
