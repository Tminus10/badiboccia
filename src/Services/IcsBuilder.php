<?php

/** Builds a minimal RFC 5545 .ics feed spanning every season's scheduled games and custom calendar entries. */
final class IcsBuilder
{
    private const GAME_DURATION_MINUTES = 120; // a game slot is assumed to take 2 hours
    private const CUSTOM_EVENT_DURATION_MINUTES = 60; // draws, celebrations, etc. -- no fixed duration is known
    private const TZ = 'Europe/Zurich';

    /**
     * @param array[] $games rows from Game::scheduledAll(), each with a non-null played_date and both teams known
     * @param array[] $customEvents rows from CalendarEvent::all()
     * @param array<int,array> $teamsById
     * @param array<int,array> $groupsById group_id => team_groups row, for labeling group-phase games
     * @param array<int,array> $seasonsById season_id => seasons row, so events from different seasons stay distinguishable
     */
    public static function build(string $calendarName, string $uidDomain, array $games, array $customEvents, array $teamsById, array $groupsById, array $seasonsById): string
    {
        $lines = [
            'BEGIN:VCALENDAR',
            'VERSION:2.0',
            'PRODID:-//BadiBoccia//Kalender//DE',
            'CALSCALE:GREGORIAN',
            'METHOD:PUBLISH',
            'X-WR-CALNAME:' . self::escape($calendarName),
            'X-WR-TIMEZONE:' . self::TZ,
        ];

        foreach ($games as $game) {
            $lines = array_merge($lines, self::gameEvent($game, $teamsById, $groupsById, $seasonsById, $uidDomain));
        }
        foreach ($customEvents as $event) {
            $lines = array_merge($lines, self::customEvent($event, $seasonsById, $uidDomain));
        }

        $lines[] = 'END:VCALENDAR';

        return implode("\r\n", array_map([self::class, 'fold'], $lines)) . "\r\n";
    }

    private static function gameEvent(array $game, array $teamsById, array $groupsById, array $seasonsById, string $uidDomain): array
    {
        $teamA = $teamsById[$game['team_a_id']] ?? null;
        $teamB = $teamsById[$game['team_b_id']] ?? null;
        $teamAName = $teamA['name'] ?? 'Team A';
        $teamBName = $teamB['name'] ?? 'Team B';

        $phase = game_phase_label($game, $groupsById);
        $seasonLabel = $seasonsById[$game['season_id']]['label'] ?? null;
        $phaseWithSeason = $seasonLabel !== null ? $phase . ' · ' . $seasonLabel : $phase;

        // Leading label (not a trailing parenthetical) so it's the first thing visible even
        // where a calendar app truncates the title -- e.g. a narrow month-grid cell or a
        // notification banner.
        $summary = $phase . ': ' . $teamAName . ' – ' . $teamBName;

        $description = $phaseWithSeason;
        if (Game::isComplete($game)) {
            $description .= ' · Resultat: ' . $game['sets_a'] . ':' . $game['sets_b'];
        }

        $lines = ['BEGIN:VEVENT'];
        $lines[] = 'UID:game-' . $game['id'] . '@' . $uidDomain;
        $lines[] = 'DTSTAMP:' . gmdate('Ymd\THis\Z');
        $lines = array_merge($lines, self::dateLines($game['played_date'], $game['game_time'], self::GAME_DURATION_MINUTES));
        $lines[] = 'SUMMARY:' . self::escape($summary);
        $lines[] = 'DESCRIPTION:' . self::escape($description);
        $lines[] = 'LOCATION:' . self::escape('Strandbad Buochs-Ennetbürgen');
        $lines[] = 'STATUS:CONFIRMED';
        // Every timed game gets a reminder; there's no per-game opt-out (unlike custom events).
        $lines = array_merge($lines, self::alarmLines(!empty($game['game_time'])));
        $lines[] = 'END:VEVENT';

        return $lines;
    }

    private static function customEvent(array $event, array $seasonsById, string $uidDomain): array
    {
        $seasonLabel = $seasonsById[$event['season_id']]['label'] ?? null;

        $lines = ['BEGIN:VEVENT'];
        $lines[] = 'UID:event-' . $event['id'] . '@' . $uidDomain;
        $lines[] = 'DTSTAMP:' . gmdate('Ymd\THis\Z');
        $duration = !empty($event['duration_minutes']) ? (int) $event['duration_minutes'] : self::CUSTOM_EVENT_DURATION_MINUTES;
        $lines = array_merge($lines, self::dateLines($event['event_date'], $event['event_time'], $duration));
        $lines[] = 'SUMMARY:' . self::escape($event['title']);
        if ($seasonLabel !== null) {
            $lines[] = 'DESCRIPTION:' . self::escape($seasonLabel);
        }
        if (!empty($event['location'])) {
            $lines[] = 'LOCATION:' . self::escape($event['location']);
        }
        $lines[] = 'STATUS:CONFIRMED';
        $lines = array_merge($lines, self::alarmLines(!empty($event['event_time']) && !empty($event['reminder'])));
        $lines[] = 'END:VEVENT';

        return $lines;
    }

    /** DTSTART/DTEND lines: a timed range when $time is set, an all-day date range otherwise. */
    private static function dateLines(string $date, ?string $time, int $durationMinutes): array
    {
        if (!empty($time)) {
            [$startUtc, $endUtc] = self::timedRange($date, $time, $durationMinutes);
            return ['DTSTART:' . $startUtc, 'DTEND:' . $endUtc];
        }

        $start = str_replace('-', '', $date);
        $end = date('Ymd', strtotime($date . ' +1 day'));
        return ['DTSTART;VALUE=DATE:' . $start, 'DTEND;VALUE=DATE:' . $end];
    }

    /**
     * A "1 hour before" reminder, when requested -- an all-day event's DTSTART is just a date
     * with no time-of-day, so "-PT1H" would fire at 23:00 the day before, which isn't a useful
     * reminder; callers pass false for those.
     */
    private static function alarmLines(bool $enabled): array
    {
        if (!$enabled) {
            return [];
        }
        return [
            'BEGIN:VALARM',
            'ACTION:DISPLAY',
            'DESCRIPTION:Erinnerung',
            'TRIGGER:-PT1H',
            'END:VALARM',
        ];
    }

    /** @return array{0:string,1:string} [DTSTART, DTEND] as UTC "Ymd\THis\Z" strings */
    private static function timedRange(string $date, string $time, int $durationMinutes): array
    {
        $tz = new DateTimeZone(self::TZ);
        $start = new DateTimeImmutable($date . ' ' . $time, $tz);
        $end = $start->modify('+' . $durationMinutes . ' minutes');

        return [
            $start->setTimezone(new DateTimeZone('UTC'))->format('Ymd\THis\Z'),
            $end->setTimezone(new DateTimeZone('UTC'))->format('Ymd\THis\Z'),
        ];
    }

    /** Escapes text per RFC 5545 §3.3.11. */
    private static function escape(string $text): string
    {
        return str_replace(
            ["\\", "\n", ",", ";"],
            ["\\\\", "\\n", "\\,", "\\;"],
            $text
        );
    }

    /** Folds a line to 75 octets per RFC 5545 §3.1, continuation lines prefixed with a space. */
    private static function fold(string $line): string
    {
        if (strlen($line) <= 75) {
            return $line;
        }
        $out = '';
        $rest = $line;
        $first = true;
        while (strlen($rest) > 0) {
            $limit = $first ? 75 : 74;
            $out .= ($first ? '' : "\r\n ") . substr($rest, 0, $limit);
            $rest = substr($rest, $limit);
            $first = false;
        }
        return $out;
    }
}
