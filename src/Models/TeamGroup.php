<?php

final class TeamGroup
{
    public static function find(int $id): ?array
    {
        $stmt = Db::pdo()->prepare('SELECT * FROM team_groups WHERE id = ?');
        $stmt->execute([$id]);
        return $stmt->fetch() ?: null;
    }

    /** Every group across every season, with its season's label attached, for cross-season pickers. */
    public static function allWithSeason(): array
    {
        return Db::pdo()->query(
            'SELECT team_groups.*, seasons.label AS season_label, seasons.year
             FROM team_groups
             JOIN seasons ON seasons.id = team_groups.season_id
             ORDER BY seasons.year DESC, team_groups.name ASC'
        )->fetchAll();
    }
}
