<?php

final class CalendarController
{
    public static function current(array $params): void
    {
        $season = Season::current();
        if ($season === null) {
            render('no-season');
            return;
        }
        self::renderCalendar((int) $season['id'], true);
    }

    public static function forSeason(array $params): void
    {
        $season = Season::find((int) $params['id']);
        if ($season === null) {
            http_response_code(404);
            render('404');
            return;
        }
        self::renderCalendar((int) $season['id'], (int) $season['is_current'] === 1);
    }

    /**
     * The single subscribed .ics feed: every season's scheduled games and custom calendar
     * entries, past and future, so the URL someone subscribes to once always has content
     * (nothing to re-subscribe to between seasons) and their calendar app also picks up past
     * seasons' history.
     */
    public static function feed(array $params): void
    {
        $games = Game::scheduledAll();
        $customEvents = CalendarEvent::all();

        $teamsById = [];
        $groupsById = [];
        $seasonsById = [];
        foreach ($games as $g) {
            foreach (['team_a_id', 'team_b_id'] as $key) {
                $id = $g[$key];
                if ($id !== null && !isset($teamsById[$id])) {
                    $t = Team::find((int) $id);
                    if ($t !== null) {
                        $teamsById[$id] = $t;
                    }
                }
            }
            if ($g['group_id'] !== null && !isset($groupsById[$g['group_id']])) {
                $group = TeamGroup::find((int) $g['group_id']);
                if ($group !== null) {
                    $groupsById[$g['group_id']] = $group;
                }
            }
            self::rememberSeason($seasonsById, (int) $g['season_id']);
        }
        foreach ($customEvents as $e) {
            self::rememberSeason($seasonsById, (int) $e['season_id']);
        }

        $host = (string) ($_SERVER['HTTP_HOST'] ?? 'badiboccia.local');
        $ics = IcsBuilder::build('Badi Boccia Buochs', $host, $games, $customEvents, $teamsById, $groupsById, $seasonsById);

        header('Content-Type: text/calendar; charset=utf-8');
        header('Content-Disposition: inline; filename="badiboccia.ics"');
        echo $ics;
        exit;
    }

    private static function rememberSeason(array &$seasonsById, int $seasonId): void
    {
        if (!isset($seasonsById[$seasonId])) {
            $season = Season::find($seasonId);
            if ($season !== null) {
                $seasonsById[$seasonId] = $season;
            }
        }
    }

    private static function renderCalendar(int $seasonId, bool $isCurrent): void
    {
        $season = Season::find($seasonId);
        $games = Game::scheduledForSeason($seasonId);
        $customEvents = CalendarEvent::forSeason($seasonId);
        [$teamsById, $groupsById] = self::lookups($seasonId, $games);
        $items = self::mergedItems($games, $customEvents);

        $view = ($_GET['view'] ?? '') === 'list' ? 'list' : 'month';

        // Default month: the season's next upcoming item (today or later); if none, the most
        // recent one; if there's nothing at all, this month -- so the grid opens on whatever is
        // actually relevant.
        $today = date('Y-m-d');
        $defaultMonth = date('Y-m');
        $nextItem = null;
        foreach ($items as $item) {
            if ($item['date'] >= $today) {
                $nextItem = $item;
                break;
            }
        }
        if ($nextItem !== null) {
            $defaultMonth = substr($nextItem['date'], 0, 7);
        } elseif (!empty($items)) {
            $defaultMonth = substr($items[count($items) - 1]['date'], 0, 7);
        }

        $monthParam = (string) ($_GET['month'] ?? '');
        $month = preg_match('/^\d{4}-\d{2}$/', $monthParam) ? $monthParam : $defaultMonth;

        render('calendar', [
            'season' => $season,
            'isCurrent' => $isCurrent,
            'items' => $items,
            'customEvents' => $customEvents,
            'teamsById' => $teamsById,
            'groupsById' => $groupsById,
            'unscheduledCount' => Game::countUnscheduled($seasonId),
            'view' => $view,
            'month' => $month,
            'feedUrl' => absolute_url('/calendar.ics'),
            'admin' => AdminAuth::current(),
        ]);
    }

    /**
     * Games and custom events, normalized to a common shape and sorted chronologically together
     * (timed entries before all-day ones on the same date, matching how Game::scheduledForSeason
     * already orders games on its own).
     *
     * @return array{kind:string,date:string,time:?string,game?:array,event?:array}[]
     */
    private static function mergedItems(array $games, array $customEvents): array
    {
        $items = [];
        foreach ($games as $g) {
            $items[] = ['kind' => 'game', 'date' => $g['played_date'], 'time' => $g['game_time'], 'game' => $g];
        }
        foreach ($customEvents as $e) {
            $items[] = ['kind' => 'event', 'date' => $e['event_date'], 'time' => $e['event_time'], 'event' => $e];
        }

        usort($items, function (array $a, array $b): int {
            $cmp = $a['date'] <=> $b['date'];
            if ($cmp !== 0) {
                return $cmp;
            }
            $aNull = $a['time'] === null;
            $bNull = $b['time'] === null;
            if ($aNull !== $bNull) {
                return $aNull <=> $bNull;
            }
            return $a['time'] <=> $b['time'];
        });

        return $items;
    }

    /** @return array{0: array<int,array>, 1: array<int,array>} [teamsById, groupsById] */
    private static function lookups(int $seasonId, array $games): array
    {
        $teamsById = [];
        foreach ($games as $g) {
            foreach (['team_a_id', 'team_b_id'] as $key) {
                $id = $g[$key];
                if ($id !== null && !isset($teamsById[$id])) {
                    $t = Team::find((int) $id);
                    if ($t !== null) {
                        $teamsById[$id] = $t;
                    }
                }
            }
        }

        $groupsById = [];
        foreach (Season::groups($seasonId) as $group) {
            $groupsById[$group['id']] = $group;
        }

        return [$teamsById, $groupsById];
    }
}
