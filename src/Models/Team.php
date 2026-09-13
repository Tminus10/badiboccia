<?php

final class Team
{
    public static function find(int $id): ?array
    {
        $stmt = Db::pdo()->prepare('SELECT * FROM teams WHERE id = ?');
        $stmt->execute([$id]);
        return $stmt->fetch() ?: null;
    }

    /** Every team that has ever existed, for the central team-management page. */
    public static function all(): array
    {
        return Db::pdo()->query('SELECT * FROM teams ORDER BY name ASC')->fetchAll();
    }

    /** Every season/group a team has been enrolled in, newest first. */
    public static function seasonHistory(int $teamId): array
    {
        $stmt = Db::pdo()->prepare(
            'SELECT team_seasons.season_id, team_seasons.group_id, seasons.label AS season_label,
                    seasons.year, team_groups.name AS group_name
             FROM team_seasons
             JOIN seasons ON seasons.id = team_seasons.season_id
             JOIN team_groups ON team_groups.id = team_seasons.group_id
             WHERE team_seasons.team_id = ?
             ORDER BY seasons.year DESC'
        );
        $stmt->execute([$teamId]);
        return $stmt->fetchAll();
    }

    /** Teams enrolled in a given group this season, with that season's group_id attached. */
    public static function byGroup(int $groupId): array
    {
        $stmt = Db::pdo()->prepare(
            'SELECT teams.*, team_seasons.season_id, team_seasons.group_id
             FROM teams
             JOIN team_seasons ON team_seasons.team_id = teams.id
             WHERE team_seasons.group_id = ?
             ORDER BY teams.name ASC'
        );
        $stmt->execute([$groupId]);
        return $stmt->fetchAll();
    }

    /** Teams enrolled in a given season, with that season's group_id attached. */
    public static function bySeason(int $seasonId): array
    {
        $stmt = Db::pdo()->prepare(
            'SELECT teams.*, team_seasons.season_id, team_seasons.group_id
             FROM teams
             JOIN team_seasons ON team_seasons.team_id = teams.id
             WHERE team_seasons.season_id = ?
             ORDER BY teams.name ASC'
        );
        $stmt->execute([$seasonId]);
        return $stmt->fetchAll();
    }

    /** Teams not yet enrolled in the given season, for the "add existing team" picker. */
    public static function availableForSeason(int $seasonId): array
    {
        $stmt = Db::pdo()->prepare(
            'SELECT teams.* FROM teams
             WHERE teams.id NOT IN (
                 SELECT team_id FROM team_seasons WHERE season_id = ?
             )
             ORDER BY teams.name ASC'
        );
        $stmt->execute([$seasonId]);
        return $stmt->fetchAll();
    }

    /** The season/group this team is currently or was most recently enrolled in (for its profile header). */
    public static function currentEnrollment(int $teamId): ?array
    {
        $stmt = Db::pdo()->prepare(
            'SELECT team_seasons.* FROM team_seasons
             JOIN seasons ON seasons.id = team_seasons.season_id
             WHERE team_seasons.team_id = ?
             ORDER BY seasons.is_current DESC, seasons.year DESC
             LIMIT 1'
        );
        $stmt->execute([$teamId]);
        return $stmt->fetch() ?: null;
    }

    private static function count(): int
    {
        return (int) Db::pdo()->query('SELECT COUNT(*) FROM teams')->fetchColumn();
    }

    /** Creates a brand-new persistent team (PIN + color for life) and enrolls it in a season/group. */
    public static function createAndEnroll(int $seasonId, int $groupId, string $name, string $player1, string $player2): int
    {
        $pdo = Db::pdo();
        $color = ColorAssigner::colorForIndex(self::count());
        $pin = str_pad((string) random_int(0, 9999), 4, '0', STR_PAD_LEFT);

        $stmt = $pdo->prepare(
            'INSERT INTO teams (name, player1, player2, color_hex, pin) VALUES (?, ?, ?, ?, ?)'
        );
        $stmt->execute([$name, $player1, $player2, $color, $pin]);
        $teamId = (int) $pdo->lastInsertId();

        self::enroll($teamId, $seasonId, $groupId);

        return $teamId;
    }

    /** Enrolls an existing team (returning from a previous season) into a season/group. */
    public static function enroll(int $teamId, int $seasonId, int $groupId): void
    {
        $stmt = Db::pdo()->prepare(
            'INSERT INTO team_seasons (team_id, season_id, group_id) VALUES (?, ?, ?)'
        );
        $stmt->execute([$teamId, $seasonId, $groupId]);
    }

    public static function update(int $id, string $name, string $player1, string $player2): void
    {
        $stmt = Db::pdo()->prepare(
            'UPDATE teams SET name = ?, player1 = ?, player2 = ? WHERE id = ?'
        );
        $stmt->execute([$name, $player1, $player2, $id]);
    }

    public static function updateGroup(int $teamId, int $seasonId, int $groupId): void
    {
        $stmt = Db::pdo()->prepare(
            'UPDATE team_seasons SET group_id = ? WHERE team_id = ? AND season_id = ?'
        );
        $stmt->execute([$groupId, $teamId, $seasonId]);
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

    /** Removes a team from one season. If that was its only season, the team itself is deleted too. */
    public static function removeFromSeason(int $teamId, int $seasonId): void
    {
        $pdo = Db::pdo();
        $stmt = $pdo->prepare('DELETE FROM team_seasons WHERE team_id = ? AND season_id = ?');
        $stmt->execute([$teamId, $seasonId]);

        $remaining = $pdo->prepare('SELECT COUNT(*) FROM team_seasons WHERE team_id = ?');
        $remaining->execute([$teamId]);
        if ((int) $remaining->fetchColumn() === 0) {
            $pdo->prepare('DELETE FROM teams WHERE id = ?')->execute([$teamId]);
        }
    }

    /** Permanently deletes a team and its entire history, from every season. */
    public static function deleteCompletely(int $id): void
    {
        Db::pdo()->prepare('DELETE FROM teams WHERE id = ?')->execute([$id]);
    }

    public static function verifyPin(array $team, string $pin): bool
    {
        return hash_equals($team['pin'], $pin);
    }
}
