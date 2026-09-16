<?php

final class TeamGroup
{
    public static function find(int $id): ?array
    {
        $stmt = Db::pdo()->prepare('SELECT * FROM team_groups WHERE id = ?');
        $stmt->execute([$id]);
        return $stmt->fetch() ?: null;
    }

    /** Only safe to call once the group has no teams enrolled (and therefore no games either). */
    public static function delete(int $id): void
    {
        $stmt = Db::pdo()->prepare('DELETE FROM team_groups WHERE id = ?');
        $stmt->execute([$id]);
    }
}
