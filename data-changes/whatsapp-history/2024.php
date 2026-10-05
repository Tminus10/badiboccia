<?php
// WhatsApp history import, season 2024 -- blocks 1-4 approved 2026-10-05. See PROPOSALS.md for the evidence.
// Chat L = Lilo's export, chat P = Paschi's export. Group results already in the DB are not touched.
// #53 and #74 at 19:30 by the user's decision (games right after an 18:00 game). KO: winners known,
// no scores (decision D1) -> teams + dates only.

// Expected current state for time-only changes: given result and date, no time yet.
$has = fn (int $a, int $b, string $date): array => ['sets' => [$a, $b], 'played_date' => $date, 'game_time' => null];
$open = ['sets' => [null, null], 'played_date' => null, 'game_time' => null];

return [
    'name' => 'whatsapp-history-2024',
    'actor_label' => 'WhatsApp-Import 2024',
    'summary' => 'Season 2024: interrupted matches #57/#70 moved to the day they were finished, start times for 26 group games, KO pairings + dates (no scores), from the WhatsApp chat (Lilo + Paschi exports).',
    'apply' => function (Changeset $c) use ($has, $open): void {
        // Block 1 -- interrupted matches
        $c->setDate(2024, 'group', 'Maja & Paschi', 'Siri & Peter', '2024-08-13', '17:00',
            ['sets' => [2, 1], 'played_date' => '2024-08-05', 'game_time' => '18:30:00'],
            'chat L 30.06.24 Maja "spielen jetzt" (start); chat P 03.08 Paschi "nu dä 3. Satz z spilä?"; chat L 12.08 Peter "Ziistig ab 17ni 00", Maja "Dienstag um fünf"');
        $c->setDate(2024, 'group', 'Jöggu & Puppe', 'Kusi W. & Patrik', '2024-07-30', '18:00',
            ['sets' => [2, 1], 'played_date' => '2024-07-17', 'game_time' => null],
            'chat L 26.07.24 Jöggu "eysä unterbrochnig Match fertig zuspielä?!"; 27.07 "Zeyschtig 30.7. 18:00 Uhr Kusi/Pädi -Puppe/Jöggu!"');

        // Block 2 -- start times groups A and B
        $c->setDate(2024, 'group', 'Bea & Gysi', 'Moni & Erwin', '2024-06-26', '09:30', $has(2, 1, '2024-06-26'), 'chat L 25.06.24 Moni "O.k. morgen 9.30 Bea Roli Moni Edwä"');
        $c->setDate(2024, 'group', 'Bea & Gysi', 'Tubä & Wurm', '2024-06-26', '16:00', $has(0, 2, '2024-06-26'), 'chat L 23.06.24 Bea "Mi 16.00. Wurm/Tuibä : Gysi/Bea", "Bocciabahn reserviert: Mi 16.00"');
        $c->setDate(2024, 'group', 'Guli & Odi', 'Tubä & Wurm', '2024-06-28', '16:00', $has(0, 2, '2024-06-28'), 'chat L 23.06.24 Guli "Fr 16.00 Uhr Wurm\\Tuibä: Odi\\Guli", all 👍');
        $c->setDate(2024, 'group', 'Guli & Odi', 'Bruni & Tiz', '2024-06-23', '15:00', $has(2, 0, '2024-06-23'), 'chat L 22.06.24 Guli "Odi/Guli vs. Bruni/Tiz am Sunntig uf diä drü", Odi 👍');
        $c->setDate(2024, 'group', 'Moni & Erwin', 'Bruni & Tiz', '2024-06-19', '18:00', $has(2, 0, '2024-06-19'), 'chat L 16.06.24 Moni "Moni und Edwä hend Zyt 18.00", Tiz "perfetto"');
        $c->setDate(2024, 'group', 'Guli & Odi', 'Moni & Erwin', '2024-06-29', '13:30', $has(2, 1, '2024-06-29'), 'chat L 27.06.24 Moni "Samstag 13 30 Odi Guli Moni Edwä"');
        $c->setDate(2024, 'group', 'Bea & Gysi', 'Bruni & Tiz', '2024-07-08', '18:00', $has(2, 1, '2024-07-08'), 'chat L 03.07.24 Bea "Mo 8.7. 18.00 Tiz/Bruni : Gysi/Bea"');
        $c->setDate(2024, 'group', 'Moni & Erwin', 'Tubä & Wurm', '2024-07-14', '14:00', $has(0, 2, '2024-07-14'), 'chat L 11.07.24 Moni "Tubä /Wurm Edwä/Moni Sonntag 14 00 Match"');
        $c->setDate(2024, 'group', 'Bruni & Tiz', 'Tubä & Wurm', '2024-07-17', '18:00', $has(0, 2, '2024-07-17'), 'chat L 11.07.24 Tiz "Mittwuch, 17.7. uf diä 18.00", Wurm + Tube 👍');
        $c->setDate(2024, 'group', 'Georges & Stierli', 'Maja & Paschi', '2024-06-25', '17:00', $has(2, 0, '2024-06-25'), 'chat L 21.06.24 Maja "Dienstag 25. Juni um 17 Uhr", Georg "Okay"');
        $c->setDate(2024, 'group', 'Evelyne & Sven', 'Georges & Stierli', '2024-07-04', '19:00', $has(2, 1, '2024-07-04'), 'chat L 29.06.24 Evelyne "donnerstag, 4. juli, 19.00 uhr"; 02.07 Stierli "19.00 uhr donnerstag ok"');
        $c->setDate(2024, 'group', 'Georges & Stierli', 'Zimi & Roman', '2024-07-19', '19:00', $has(2, 0, '2024-07-19'), 'chat L 19.07.24 Roman "hit ab 7ni uf Platz", Georg "dä roli und ich sind da"');
        $c->setDate(2024, 'group', 'Evelyne & Sven', 'Zimi & Roman', '2024-07-30', '19:30', $has(2, 0, '2024-07-30'), 'chat L 27.07.24 Evelyne "im aschluss … zimi/roman-sven/evelyne" (after 18:00 game); 19:30 by user decision');

        // Block 3 -- start times groups C and D
        $c->setDate(2024, 'group', 'Henry & Husi', 'Sandra & Werni', '2024-07-09', '18:00', $has(2, 0, '2024-07-09'), 'chat L 02.07.24 Bea "Di 9.7. 18.00 Henry/ Husi : Sandra/Werni", Henry "Okey"');
        $c->setDate(2024, 'group', 'Andrea & Armin', 'Sandra & Werni', '2024-07-15', '18:00', $has(0, 2, '2024-07-15'), 'chat L 02.07.24 Joli "Mändig, 15.7. 18.00 Uhr Sandra/ Werni gägä Andrea/Armin?", Sandra 👍');
        $c->setDate(2024, 'group', 'Andrea & Armin', 'Henry & Husi', '2024-07-18', '18:00', $has(1, 2, '2024-07-18'), 'chat L 18.07.24 Husi "Heute 18 00 Uhr Spiel Henry/Husi Armin /AndreaBeat"');
        $c->setDate(2024, 'group', 'Andrea & Armin', 'Attila & Rolf', '2024-07-23', '18:00', $has(2, 0, '2024-07-23'), 'chat L 21.07.24 Lilo "Di 23.7. Armin/Beat-Atti/Rolf 18.00 Uhr"');
        $c->setDate(2024, 'group', 'Attila & Rolf', 'Henry & Husi', '2024-07-24', '13:00', $has(0, 2, '2024-07-24'), 'chat L 21.07.24 Husi "Am 24.07 Spielen Henry/Husi gegen Attila /Rolf Um 1300 Uhr"');
        $c->setDate(2024, 'group', 'Erika & Debi', 'Dani & Peter', '2024-06-16', '13:00', $has(2, 1, '2024-06-16'), 'chat L 16.06.24 Dani "heute ab 13:00 Uhr gegen Erika & Debi", Debi "Perfekt"');
        $c->setDate(2024, 'group', 'Erika & Debi', 'Kusi W. & Patrik', '2024-06-18', '18:30', $has(2, 0, '2024-06-18'), 'chat L 16.06.24 Debi "Dienstag 18.30 Uhr Pady Kusi gägä Erika &Debi"');
        $c->setDate(2024, 'group', 'Jöggu & Puppe', 'Lilo & Roli', '2024-06-27', '18:00', $has(2, 1, '2024-06-27'), 'chat L 23.06.24 Lilo "Donnschtig 18.00 ?", Puppe "Ja"');
        $c->setDate(2024, 'group', 'Kusi W. & Patrik', 'Lilo & Roli', '2024-06-28', '18:00', $has(1, 2, '2024-06-28'), 'chat L 23.06.24 Pädi "Freytig 18.00 Lilo&Roli gägä Kusi&Pädi"');
        $c->setDate(2024, 'group', 'Erika & Debi', 'Jöggu & Puppe', '2024-07-10', '18:00', $has(2, 0, '2024-07-10'), 'chat L 07.07.24 Erika "10.7.24 18.00 Uhr: Jöggu/Puppe-Debi/Erika"');
        $c->setDate(2024, 'group', 'Erika & Debi', 'Lilo & Roli', '2024-07-16', '18:00', $has(0, 2, '2024-07-16'), 'chat L 12.07.24 Roli "16.7. Debi erika lilo roli 18.00"');
        $c->setDate(2024, 'group', 'Jöggu & Puppe', 'Dani & Peter', '2024-07-19', '17:00', $has(1, 2, '2024-07-19'), 'chat L 11.07.24 Dani "Puppe-Jöggu/Peter-Dani, Fr. 19.7, 17 Uhr Match"');
        $c->setDate(2024, 'group', 'Lilo & Roli', 'Dani & Peter', '2024-07-20', '14:00', $has(2, 0, '2024-07-20'), 'chat L 19.07.24 Lilo "20.7. 2024 14.00 Uhr Peter- Dani/ Lilo - Roli"');
        $c->setDate(2024, 'group', 'Kusi W. & Patrik', 'Dani & Peter', '2024-07-15', '19:30', $has(0, 2, '2024-07-15'), 'chat L 10.07.24 Pädi "Am Mäntig 15.07 nachäm Match vo dä Sandra" (after 18:00 game); 19:30 by user decision');

        // Block 4 -- bracket, teams + dates (winners: E&D, T&W, B&G, E&S; SF: E&D, E&S; 3rd T&W; final E&D)
        $c->setBracketTeams(2024, 'qf', 1, 'Erika & Debi', 'Henry & Husi', 'chat L 03.08.24 Husi "Freitag 18.00 Erika/Debi Husi/Henry"');
        $c->setBracketTeams(2024, 'qf', 2, 'Tubä & Wurm', 'Georges & Stierli', 'chat L 05.08.24 Wurm "Georg+Roli … Tubä+Wurm"');
        $c->setBracketTeams(2024, 'qf', 3, 'Bea & Gysi', 'Lilo & Roli', 'chat L 02.08.24 Lilo "Bea/ Gysi - Lilo/Roli"');
        $c->setBracketTeams(2024, 'qf', 4, 'Evelyne & Sven', 'Sandra & Werni', 'chat L 11.08.24 Sven "Sandra & Werni … Mittwuch"');
        $c->setDate(2024, 'qf', 'Erika & Debi', 'Henry & Husi', '2024-08-09', '18:00', $open, 'chat L 02.08.24 Erika "Dann machen wir 18.00 Uhr, 9.8.24"; 03.08 Husi "ist ok"');
        $c->setDate(2024, 'qf', 'Tubä & Wurm', 'Georges & Stierli', '2024-08-11', '11:00', $open, 'chat L 07.08.24 Tube "Wegen hochwasser heute kein Spiel..... Wird am Sonntag 11 Uhr gespielt"');
        $c->setDate(2024, 'qf', 'Bea & Gysi', 'Lilo & Roli', '2024-08-03', '13:00', $open, 'chat L 02.08.24 Lilo "Morn 13.00 Uhr Bea/ Gysi - Lilo/Roli"; 03.08 Odi "Bea/Gisi … zum sieg"');
        $c->setDate(2024, 'qf', 'Evelyne & Sven', 'Sandra & Werni', '2024-08-14', '09:30', $open, 'chat L 11.08.24 Sandra "9.30 liäber", Sven "voll okay"; 14.08 Odi "Swen/Evelyn … zum sieg"');
        $c->setBracketTeams(2024, 'sf', 1, 'Erika & Debi', 'Tubä & Wurm', 'QF1 + QF2 winners');
        $c->setDate(2024, 'sf', 'Erika & Debi', 'Tubä & Wurm', '2024-08-15', '10:30', $open, 'chat L 11.08.24 Tube "Do 15 Aug. Spiel Wurm tubä. Vs debi Erika. 1030 uhr"');
        $c->setBracketTeams(2024, 'sf', 2, 'Bea & Gysi', 'Evelyne & Sven', 'QF3 + QF4 winners');
        $c->setDate(2024, 'sf', 'Bea & Gysi', 'Evelyne & Sven', '2024-08-20', '17:30', $open, 'chat L 19.08.24 Gysi "Morgen Dienstag spiel 17.30 Uhr Kaiser Gysi gegen Gysi Gysi"');
        $c->setBracketTeams(2024, 'third', 1, 'Tubä & Wurm', 'Bea & Gysi', 'SF losers');
        $c->setDate(2024, 'third', 'Tubä & Wurm', 'Bea & Gysi', '2024-09-08', '10:00', $open, 'chat L 07.09.24 Bea "Chlises Final: 10.00"; 08.09 Odi "3 tubä Wurm 4 bea gisi"');
        $c->setBracketTeams(2024, 'final', 1, 'Erika & Debi', 'Evelyne & Sven', 'SF winners');
        $c->setDate(2024, 'final', 'Erika & Debi', 'Evelyne & Sven', '2024-09-08', null, $open, 'chat L 20.08.24 Erika "Finalspiele am Sonntag, 8.09.2024"; 08.09 Odi "1erika debi 2 Evelyn Sven"');
    },
];
