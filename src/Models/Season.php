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

    /** $groupCount is how many groups (A, B, C, ...) the group phase starts with -- 3 or 4. */
    public static function create(string $label, int $year, int $groupCount = 4): int
    {
        $pdo = Db::pdo();
        $stmt = $pdo->prepare('INSERT INTO seasons (label, year) VALUES (?, ?)');
        $stmt->execute([$label, $year]);
        $seasonId = (int) $pdo->lastInsertId();

        $groupStmt = $pdo->prepare('INSERT INTO team_groups (season_id, name) VALUES (?, ?)');
        foreach (array_slice(['A', 'B', 'C', 'D'], 0, $groupCount) as $name) {
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

    /**
     * How many teams per group advance to the quarter-finals, so the 4 QF games (8 slots)
     * can always be filled: with 4 groups the top 2 fill the bracket exactly; with fewer
     * groups, more teams per group are highlighted as candidates than there are slots to
     * fill, so the admin can pick which of them go through.
     */
    public static function qualifiersPerGroup(int $groupCount): int
    {
        return $groupCount > 0 ? (int) ceil(8 / $groupCount) : 2;
    }
}
