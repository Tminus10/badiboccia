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
     * Same crossed-seeding template as recommendedQfPairings(), but as plain
     * "1./2. Gruppe X" labels rather than actual teams. Used before any group-phase
     * game has a result, when standings are still all-tied and "top 2" would just be
     * alphabetical -- showing real (but meaningless) team names as the suggestion
     * would look like a real assignment. Needs only the four group names, not standings.
     *
     * @param string[] $groupNames the season's group names, in their display order
     * @return array<int, array{0:string,1:string}>|null [slot => [labelA, labelB]] for QF slots 1-4,
     *   or null if there aren't exactly four groups.
     */
    public static function recommendedQfPairingLabels(array $groupNames): ?array
    {
        if (count($groupNames) !== 4) {
            return null;
        }
        [$n1, $n2, $n3, $n4] = array_values($groupNames);

        return [
            1 => ["1. Gruppe $n1", "2. Gruppe $n2"],
            2 => ["1. Gruppe $n3", "2. Gruppe $n4"],
            3 => ["1. Gruppe $n2", "2. Gruppe $n1"],
            4 => ["1. Gruppe $n4", "2. Gruppe $n3"],
        ];
    }
}
