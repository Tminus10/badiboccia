<?php
// Decision D1: knockout games where the winner is known but not the score, stored as a flagged
// placeholder (Changeset::setWinner) and shown as "winner known, result unknown". The winners are
// the ones already recorded in the season changesets and PROPOSALS.md.
// Order: after all whatsapp-history season changesets and both data-changes/teams changesets.
// Rounds run in order, so each winner is already in the next round's slot before it's played.
// Not included: 2021, 2023 and 2025 third-place games (winner unknown) and 2025 QF4 Jöggu & Puppe
// – Patrick & Philipp (apparently never played).

$open = ['sets' => [null, null]];

return [
    'name' => 'whatsapp-history-knockout-winners',
    'actor_label' => 'WhatsApp-Import',
    'summary' => 'Records the known winners of 35 knockout games from 2019 and 2021-2025 as "result unknown".',
    'apply' => function (Changeset $c) use ($open): void {
        // 2019
        $c->setWinner(2019, 'sf', 'Lilo & Roli', 'Jöggu & Puppe', 'Jöggu & Puppe', $open, 'chat L 24.08.19 Lilo "kei 👸"');
        $c->setWinner(2019, 'third', 'Bea & Gysi', 'Lilo & Roli', 'Bea & Gysi', $open, 'chat L 01.09.19 "3. isch ä Roli"');
        $c->setWinner(2019, 'final', 'Odi & Stierli', 'Jöggu & Puppe', 'Jöggu & Puppe', $open, 'chat L 01.09.19 Puppe 👑, "dr zweifachi" 2020');

        // 2021
        $c->setWinner(2021, 'qf', 'Erika & Debi', 'Dani & Peter', 'Erika & Debi', $open, 'PROPOSALS 2021 #221');
        $c->setWinner(2021, 'qf', 'Lilo & Roli', 'Andrea & Armin', 'Lilo & Roli', $open, 'PROPOSALS 2021 #222');
        $c->setWinner(2021, 'qf', 'Stella & Patrik', 'Jöggu & Puppe', 'Stella & Patrik', $open, 'chat L 04.09.21 "äs gid ai das Jahr ä Königin"');
        $c->setWinner(2021, 'qf', 'Frei & Frei', 'Sandra & Werni', 'Sandra & Werni', $open, 'PROPOSALS 2021 #224');
        $c->setWinner(2021, 'sf', 'Erika & Debi', 'Lilo & Roli', 'Erika & Debi', $open, 'PROPOSALS 2021 #218');
        $c->setWinner(2021, 'sf', 'Stella & Patrik', 'Sandra & Werni', 'Sandra & Werni', $open, 'PROPOSALS 2021 #219');
        $c->setWinner(2021, 'final', 'Erika & Debi', 'Sandra & Werni', 'Erika & Debi', $open, 'chat L 26.09.21 Bea "Die Königin und der König bleiben"');

        // 2022 (SF, third place and final have real scores)
        $c->setWinner(2022, 'qf', 'Lilo & Roli', 'Kusi & Pädi', 'Lilo & Roli', $open, 'PROPOSALS 2022, changeset 2022 QF');
        $c->setWinner(2022, 'qf', 'Tubä & Wurm', 'Zimi & Roman', 'Tubä & Wurm', $open, 'PROPOSALS 2022, changeset 2022 QF');
        $c->setWinner(2022, 'qf', 'Erika & Debi', 'Frei & Frei', 'Erika & Debi', $open, 'PROPOSALS 2022, changeset 2022 QF');
        $c->setWinner(2022, 'qf', 'Dani & Peter', 'Sandra & Stella', 'Dani & Peter', $open, 'PROPOSALS 2022, changeset 2022 QF');

        // 2023
        $c->setWinner(2023, 'qf', 'Lilo & Roli', 'Kusi & Pädi', 'Lilo & Roli', $open, 'PROPOSALS 2023 QF1');
        $c->setWinner(2023, 'qf', 'Erika & Debi', 'Bruni & Tiz', 'Erika & Debi', $open, 'PROPOSALS 2023 QF2');
        $c->setWinner(2023, 'qf', 'Georges & Stierli', 'Moni & Erwin', 'Georges & Stierli', $open, 'PROPOSALS 2023 QF3');
        $c->setWinner(2023, 'qf', 'Tubä & Wurm', 'Zimi & Roman', 'Tubä & Wurm', $open, 'PROPOSALS 2023 QF4');
        $c->setWinner(2023, 'sf', 'Lilo & Roli', 'Erika & Debi', 'Lilo & Roli', $open, 'PROPOSALS 2023 SF1');
        $c->setWinner(2023, 'sf', 'Georges & Stierli', 'Tubä & Wurm', 'Georges & Stierli', $open, 'PROPOSALS 2023 SF2');
        $c->setWinner(2023, 'final', 'Lilo & Roli', 'Georges & Stierli', 'Lilo & Roli', $open, 'chat L 10.09.23 congratulations to the new queen and king');

        // 2024
        $c->setWinner(2024, 'qf', 'Erika & Debi', 'Henry & Husi', 'Erika & Debi', $open, 'PROPOSALS 2024 QF1');
        $c->setWinner(2024, 'qf', 'Tubä & Wurm', 'Georges & Stierli', 'Tubä & Wurm', $open, 'PROPOSALS 2024 QF2');
        $c->setWinner(2024, 'qf', 'Bea & Gysi', 'Lilo & Roli', 'Bea & Gysi', $open, 'chat L 03.08.24 Odi "Bea/Gisi … zum sieg"');
        $c->setWinner(2024, 'qf', 'Evelyne & Sven', 'Sandra & Werni', 'Evelyne & Sven', $open, 'chat L 14.08.24 Odi "Swen/Evelyn … zum sieg"');
        $c->setWinner(2024, 'sf', 'Erika & Debi', 'Tubä & Wurm', 'Erika & Debi', $open, 'PROPOSALS 2024 SF1');
        $c->setWinner(2024, 'sf', 'Bea & Gysi', 'Evelyne & Sven', 'Evelyne & Sven', $open, 'PROPOSALS 2024 SF2');
        $c->setWinner(2024, 'third', 'Tubä & Wurm', 'Bea & Gysi', 'Tubä & Wurm', $open, 'chat L 08.09.24 Odi "3 tubä Wurm 4 bea gisi"');
        $c->setWinner(2024, 'final', 'Erika & Debi', 'Evelyne & Sven', 'Erika & Debi', $open, 'chat L 08.09.24 Odi "1erika debi 2 Evelyn Sven"');

        // 2025
        $c->setWinner(2025, 'qf', 'Bea & Gysi', 'Erika & Debi', 'Bea & Gysi', $open, 'changeset 2025 QF (B&G won)');
        $c->setWinner(2025, 'qf', 'Georges & Stierli', 'Sandra & Werni', 'Sandra & Werni', $open, 'changeset 2025 QF (S&W won)');
        $c->setWinner(2025, 'qf', 'Tubä & Wurm', 'Lilo & Roli', 'Lilo & Roli', $open, 'changeset 2025 QF (L&R won)');
        $c->setWinner(2025, 'sf', 'Bea & Gysi', 'Sandra & Werni', 'Bea & Gysi', $open, 'changeset 2025 SF (B&G won)');
        $c->setWinner(2025, 'sf', 'Lilo & Roli', 'Jöggu & Puppe', 'Lilo & Roli', $open, 'changeset 2025 SF (L&R won)');
        $c->setWinner(2025, 'final', 'Bea & Gysi', 'Lilo & Roli', 'Lilo & Roli', $open, 'chat L 08.09.25 "König Roli … Dä chlinär"');
    },
];
