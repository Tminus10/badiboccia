<?php

/** Helpers for the knockout bracket admin assignment screen. */
final class BracketService
{
    /** Groups bracket games by phase for display: ['qf' => [...], 'sf' => [...], 'final' => [...], 'third' => [...]] */
    public static function byPhase(array $bracketGames): array
    {
        $out = ['qf' => [], 'sf' => [], 'final' => [], 'third' => []];
        foreach ($bracketGames as $game) {
            $out[$game['phase']][] = $game;
        }
        return $out;
    }

    /**
     * The final's champion/runner-up plus the third-place game's winner, once decided --
     * null until the final is complete. The 'third' entry stays null until that game is
     * complete too, even though champion/runner-up are already known.
     */
    public static function podium(array $bracketGames, array $teamsById): ?array
    {
        $final = null;
        $third = null;
        foreach ($bracketGames as $game) {
            if ($game['phase'] === 'final') {
                $final = $game;
            } elseif ($game['phase'] === 'third') {
                $third = $game;
            }
        }

        if ($final === null) {
            return null;
        }
        $championId = Game::winnerTeamId($final);
        $runnerUpId = Game::loserTeamId($final);
        if ($championId === null || $runnerUpId === null) {
            return null;
        }

        $thirdId = $third !== null ? Game::winnerTeamId($third) : null;

        return [
            'champion' => $teamsById[$championId] ?? null,
            'runnerUp' => $teamsById[$runnerUpId] ?? null,
            'third' => $thirdId !== null ? ($teamsById[$thirdId] ?? null) : null,
        ];
    }

    /** All teams in a season, grouped by their group name, with current-standing rank for reference. */
    public static function qualifierCandidates(int $seasonId): array
    {
        $out = [];
        foreach (Season::groups($seasonId) as $group) {
            $teams = Team::byGroup((int) $group['id']);
            $games = Game::forGroup((int) $group['id']);
            $standings = StandingsCalculator::compute($teams, $games);
            $out[$group['name']] = $standings;
        }
        return $out;
    }

    /**
     * Recommended QF pairings for four groups' top two teams, using the standard
     * "crossed" knockout seeding: a group's 1st plays another group's 2nd, and the
     * two teams that crossed swap sides for the other pairing. That keeps every
     * same-group pair (1st vs 2nd, or two group winners) apart until a possible
     * final -- they can never meet in the QF or SF.
     *
     * @param array[] $qualifiersByGroup group name => that group's top-2 standings rows (each with a 'team')
     * @return array<int, array{0:int,1:int}>|null [slot => [teamAId, teamBId]] for QF slots 1-4,
     *   or null if there aren't exactly four groups with two qualifiers each.
     */
    public static function recommendedQfPairings(array $qualifiersByGroup): ?array
    {
        if (count($qualifiersByGroup) !== 4) {
            return null;
        }
        $groups = array_values($qualifiersByGroup);
        foreach ($groups as $top2) {
            if (count($top2) !== 2) {
                return null;
            }
        }
        [$g1, $g2, $g3, $g4] = $groups;
        $id = fn (array $row): int => (int) $row['team']['id'];

        // QF1+QF2 feed SF1, QF3+QF4 feed SF2 (see Game::ensureBracketSkeleton) --
        // crossing 1st/2nd across groups within each half keeps same-group teams
        // out of the same semifinal too.
        return [
            1 => [$id($g1[0]), $id($g2[1])],
            2 => [$id($g3[0]), $id($g4[1])],
            3 => [$id($g2[0]), $id($g1[1])],
            4 => [$id($g4[0]), $id($g3[1])],
        ];
    }

    /**
     * Same crossed-seeding template as recommendedQfPairings(), but naming the slots
     * ("winner of group X", "runner-up of group Y") rather than actual teams. Used before any
     * group-phase game has a result, when standings are still all-tied and "top 2" would just
     * be alphabetical -- showing real (but meaningless) team names as the suggestion would look
     * like a real assignment. Needs only the four group names, not standings.
     *
     * Returned as structured slots (see slotLabel()) so the app API can localize them; the
     * website uses recommendedQfPairingLabels() below.
     *
     * @param string[] $groupNames the season's group names, in their display order
     * @return array<int, array{0:array,1:array}>|null [slot => [slotA, slotB]] for QF slots 1-4,
     *   or null if there aren't exactly four groups.
     */
    public static function recommendedQfPairingSlots(array $groupNames): ?array
    {
        if (count($groupNames) !== 4) {
            return null;
        }
        [$n1, $n2, $n3, $n4] = array_values($groupNames);
        $winner = fn (string $group): array => ['type' => 'group_winner', 'group' => $group];
        $runnerUp = fn (string $group): array => ['type' => 'group_runner_up', 'group' => $group];

        return [
            1 => [$winner($n1), $runnerUp($n2)],
            2 => [$winner($n3), $runnerUp($n4)],
            3 => [$winner($n2), $runnerUp($n1)],
            4 => [$winner($n4), $runnerUp($n3)],
        ];
    }

    /**
     * recommendedQfPairingSlots() as plain "Sieger/Zweiter Gruppe X" labels.
     *
     * @return array<int, array{0:string,1:string}>|null
     */
    public static function recommendedQfPairingLabels(array $groupNames): ?array
    {
        $slots = self::recommendedQfPairingSlots($groupNames);
        return $slots === null ? null : array_map(fn (array $pair): array => array_map([self::class, 'slotLabel'], $pair), $slots);
    }

    /**
     * The games feeding a not-yet-filled SF/final/third-place game's two slots -- mirrors the
     * next_game_id wiring of Game::ensureBracketSkeleton() (QF 1+2 -> SF 1, QF 3+4 -> SF 2,
     * SF winners -> final, SF losers -> third place). Games are numbered top to bottom, as
     * they're shown in the tournament tree.
     *
     * @return array{0:array,1:array}|null [slotA, slotB] (see slotLabel()), or null for QF
     *   games (see recommendedQfPairingSlots()).
     */
    public static function feederSlots(string $phase, int $slotIndex): ?array
    {
        $feeder = fn (string $type, string $fromPhase, int $slot): array => ['type' => $type, 'phase' => $fromPhase, 'slot' => $slot];
        return match ($phase) {
            'sf' => [$feeder('winner', 'qf', 2 * $slotIndex - 1), $feeder('winner', 'qf', 2 * $slotIndex)],
            'final' => [$feeder('winner', 'sf', 1), $feeder('winner', 'sf', 2)],
            'third' => [$feeder('loser', 'sf', 1), $feeder('loser', 'sf', 2)],
            default => null,
        };
    }

    /**
     * feederSlots() as plain labels for a not-yet-filled game's slots, e.g. "Sieger Viertelfinal 1".
     *
     * @return array{0:string,1:string}|null [labelA, labelB], or null for QF games (see recommendedQfPairingLabels()).
     */
    public static function feederLabels(string $phase, int $slotIndex): ?array
    {
        $slots = self::feederSlots($phase, $slotIndex);
        return $slots === null ? null : array_map([self::class, 'slotLabel'], $slots);
    }

    /** Label for one structured placeholder slot from recommendedQfPairingSlots()/feederSlots(). */
    public static function slotLabel(array $slot): string
    {
        return match ($slot['type']) {
            'group_winner' => t('Sieger Gruppe %s', $slot['group']),
            'group_runner_up' => t('Zweiter Gruppe %s', $slot['group']),
            'winner' => t('Sieger %1$s %2$d', phase_label($slot['phase']), $slot['slot']),
            'loser' => t('Verlierer %1$s %2$d', phase_label($slot['phase']), $slot['slot']),
        };
    }
}
