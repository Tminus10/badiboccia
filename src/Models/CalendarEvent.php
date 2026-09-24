<?php

/** Admin-created calendar entries with no game behind them (group draw, victory party, etc.). */
final class CalendarEvent
{
    public static function find(int $id): ?array
    {
        $stmt = Db::pdo()->prepare('SELECT * FROM calendar_events WHERE id = ?');
        $stmt->execute([$id]);
        return $stmt->fetch() ?: null;
    }

    /** A season's custom calendar entries, earliest first. */
    public static function forSeason(int $seasonId): array
    {
        $stmt = Db::pdo()->prepare(
            'SELECT * FROM calendar_events WHERE season_id = ?
             ORDER BY event_date ASC, (event_time IS NULL) ASC, event_time ASC, id ASC'
        );
        $stmt->execute([$seasonId]);
        return $stmt->fetchAll();
    }

    /** Every custom calendar entry across every season, for the all-seasons .ics feed. */
    public static function all(): array
    {
        $stmt = Db::pdo()->query(
            'SELECT * FROM calendar_events
             ORDER BY event_date ASC, (event_time IS NULL) ASC, event_time ASC, id ASC'
        );
        return $stmt->fetchAll();
    }

    public static function create(int $seasonId, string $title, ?string $location, string $date, ?string $time, ?int $durationMinutes, bool $reminder, int $adminId): int
    {
        $pdo = Db::pdo();
        $stmt = $pdo->prepare(
            'INSERT INTO calendar_events (season_id, title, location, event_date, event_time, duration_minutes, reminder, created_by_admin_id)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?)'
        );
        $stmt->execute([$seasonId, $title, $location, $date, $time, $durationMinutes, $reminder ? 1 : 0, $adminId]);

        return (int) $pdo->lastInsertId();
    }

    public static function update(int $id, string $title, ?string $location, string $date, ?string $time, ?int $durationMinutes, bool $reminder): void
    {
        $stmt = Db::pdo()->prepare(
            'UPDATE calendar_events SET title = ?, location = ?, event_date = ?, event_time = ?, duration_minutes = ?, reminder = ? WHERE id = ?'
        );
        $stmt->execute([$title, $location, $date, $time, $durationMinutes, $reminder ? 1 : 0, $id]);
    }

    public static function delete(int $id): void
    {
        Db::pdo()->prepare('DELETE FROM calendar_events WHERE id = ?')->execute([$id]);
    }
}
