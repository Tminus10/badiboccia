<?php
// WhatsApp history import, season 2026 -- blocks 1-3 approved 2026-10-05. See PROPOSALS.md for the evidence.
// Chat L = Lilo's export; plan = photo 4145 (31.07.26). All results are already correct in the DB
// (checked against the plan photo) -- only dates/times are added; each change checks the result first.
// #281: chat (11.07 11:30) followed over the faint plan entry ("10.7."?), by decision.

$res = fn (int $a, int $b): array => ['sets' => [$a, $b], 'played_date' => null, 'game_time' => null];
$has = fn (int $a, int $b, string $date): array => ['sets' => [$a, $b], 'played_date' => $date, 'game_time' => null];

return [
    'name' => 'whatsapp-history-2026',
    'actor_label' => 'WhatsApp-Import 2026',
    'summary' => 'Season 2026: dates/times for 38 group games and 6 KO games (results unchanged), from the WhatsApp chat (Lilo export) and plan photo 4145.',
    'apply' => function (Changeset $c) use ($res, $has): void {
        // Block 1 -- groups A and B
        $c->setDate(2026, 'group', 'Erika & Debi', 'Sarah & Jonas', '2026-05-30', '10:00', $res(2, 0), 'chat L 28.05.26 Erika "Samstag, 10.00 Uhr Sarah/Jonas gäg Debi/ Erika"');
        $c->setDate(2026, 'group', 'Bruni & Tiz', 'Erika & Debi', '2026-06-12', '18:00', $res(1, 2), 'chat L 10.06.26 Tiz "Freitig 12. Juni Bruni/Tiz gägä Erika/Debi 18.00"');
        $c->setDate(2026, 'group', 'Bruni & Tiz', 'Tubä & Wurm', '2026-06-17', '18:00', $res(2, 1), 'chat L 12.06.26 Sandra "Mi 17.Juni 18Uhr Tubä/Wurm gägä Bruni/Tiz"');
        $c->setDate(2026, 'group', 'Bruni & Tiz', 'Sarah & Jonas', '2026-06-21', '10:30', $res(2, 0), 'chat L 16.06.26 Sarah "So 21. Juni 10:30 Uhr Tiz/Bruni gägä Jonas/Sarah"');
        $c->setDate(2026, 'group', 'Sarah & Jonas', 'Tubä & Wurm', '2026-06-24', '18:00', $res(0, 2), 'chat L 21.06.26 Tube "Mittwoch 24.Juni wurm tubä vs.jonas sarah 18 uhr"');
        $c->setDate(2026, 'group', 'Erika & Debi', 'Tubä & Wurm', '2026-06-27', '09:30', $res(2, 0), 'chat L 26.06.26 Erika "9.30 Uhr: Tube/Wurm gäg Debi/Erika", "dr 27., eifach nachem Match vo disnä"');
        $c->setDate(2026, 'group', 'Erika & Debi', 'Zimi & Roman', '2026-07-16', '18:30', $has(2, 1, '2026-07-16'), 'chat L 11.07.26 Erika "Donnerstag, 16.7.26, 18.30 Uhr Zimi/Roman gäg Debi/Erika"');
        $c->setDate(2026, 'group', 'Bruni & Tiz', 'Zimi & Roman', '2026-07-21', '18:30', $has(0, 2, '2026-07-21'), 'chat L 16.07.26 Zimmi "Dienstag, 21.07. um 18:30 Uhr: Tiz/Bruni vs. Zimi/Roman"');
        $c->setDate(2026, 'group', 'Sarah & Jonas', 'Zimi & Roman', '2026-07-28', '18:30', $has(1, 2, '2026-07-28'), 'chat L 23.07.26 Zimmi "Dienstag, 28.07., 18:30 Uhr Sarah/Jonas vs. Zimi/Roman"');
        $c->setDate(2026, 'group', 'Tubä & Wurm', 'Zimi & Roman', '2026-08-05', '18:30', $res(2, 0), 'chat L 29.07.26 Roman "Tubä/Wurm vs. Zimi/Roman Mi. 05.08. 18:30Uhr"');
        $c->setDate(2026, 'group', 'Bea & Gysi', 'Erwin & Stierli', '2026-06-12', '09:30', $res(1, 2), 'chat L 08.06.26 Bea "Fr 12.6. 09.30. Stierli/Edwin gägä Roli/Bea"');
        $c->setDate(2026, 'group', 'Henry & Husi', 'Kusi & Muisli', '2026-06-26', '10:00', $has(2, 0, '2026-06-26'), 'chat L 20.06.26 Bea "Fr 26.6. 10.15 Muisli/ Kusi gägä Husi/ Henry", "Sorry 10.00"');
        $c->setDate(2026, 'group', 'Bea & Gysi', 'Henry & Husi', '2026-06-29', '11:00', $has(0, 2, '2026-06-29'), 'chat L 23.06.26 Bea "Mo 29.6. Match von 09.39 verschoben neu: 11.00"');
        $c->setDate(2026, 'group', 'Bea & Gysi', 'Evelyne & Sven', '2026-07-02', null, $res(0, 2), 'chat L 24.06.26 Evelyne "Do. 2. Juli, im Anschluss an das 18.00-Spiel, Roli/Bea versus Evelyne /Sven"');
        $c->setDate(2026, 'group', 'Erwin & Stierli', 'Henry & Husi', '2026-07-06', '10:30', $res(2, 1), 'chat L 29.06.26 Husi "Montag O6 Juli 10:30 Uhr Roli/Edwän // Henry/Husi"; plan "6.7.26"');
        $c->setDate(2026, 'group', 'Erwin & Stierli', 'Evelyne & Sven', '2026-07-07', '19:00', $res(2, 1), 'chat L 04.07.26 Evelyne "Di. 7.Juli, 19. 00, Sven-Evelyne/ Edwä-Roli"; plan "7.7.26"');
        $c->setDate(2026, 'group', 'Erwin & Stierli', 'Kusi & Muisli', '2026-07-11', '11:30', $res(2, 0), 'chat L 09.07.26 Stierli "11.7 11.30 uhr kusi tomas edwin roli", Muisli 👍 (plan entry faint)');
        $c->setDate(2026, 'group', 'Evelyne & Sven', 'Henry & Husi', '2026-07-18', '10:00', $has(2, 0, '2026-07-18'), 'chat L 06.07.26 Evelyne "Sa. 18. 7., 10.00, Husi/Henry - Sven/Evelyne"');
        $c->setDate(2026, 'group', 'Bea & Gysi', 'Kusi & Muisli', '2026-08-02', '18:00', $has(2, 1, '2026-08-02'), 'chat L 26.07.26 Bea "So 2.08.26 18.00 Kusi/Muisli : Roli/Bea"');
        $c->setDate(2026, 'group', 'Evelyne & Sven', 'Kusi & Muisli', '2026-08-06', '19:00', $res(2, 1), 'chat L 02.08.26 Evelyne "Do. 6. Aug. 19.00 Uhr, Muis/Kusi vs. Sven/Evelyne"');

        // Block 2 -- groups C and D
        $c->setDate(2026, 'group', 'Dani & Peter', 'Kusi S. & Noldi', '2026-06-15', '18:00', $res(2, 0), 'chat L 09.06.26 Kusi S. "Mo. 15. Juni Dani/Peter gägä Noldi/Kusi 18:00 Uhr"');
        $c->setDate(2026, 'group', 'Kusi S. & Noldi', 'Kusi W. & Patrik', '2026-06-18', '18:00', $res(2, 1), 'chat L 02.06.26 Kusi S. "Dunnschtig 18.06.26, 18:00 Uhr Kusi&Pädi gägä Noldi&Kusi"');
        $c->setDate(2026, 'group', 'Jöggu & Puppe', 'Kusi W. & Patrik', '2026-06-22', '18:30', $res(2, 0), 'chat L 18.06.26 Pädi "Mäntig 22. Juni 18.30 Puppe&Jöggu/Kusi&Pädi"');
        $c->setDate(2026, 'group', 'Jöggu & Puppe', 'Kusi S. & Noldi', '2026-06-25', '18:00', $res(2, 0), 'chat L 09.06.26 Kusi S. "Do. 25. Juni Puppe/Jöggu gägä Noldi/Kusi 18:00 Uhr"');
        $c->setDate(2026, 'group', 'Dani & Peter', 'Kusi W. & Patrik', '2026-06-27', '08:30', $res(2, 1), 'chat L 22.06.26 Dani "SA, 27.06. 08:30 Uhr Kusi W/Patrik gägä Peter/Dani"');
        $c->setDate(2026, 'group', 'Armin & Beat', 'Kusi S. & Noldi', '2026-07-02', '18:00', $res(1, 2), 'chat L 16.06.26 Kusi S. "Do 2. Juli 18:00 Uhr Armin/Beat gägä Noldi/Kusi"');
        $c->setDate(2026, 'group', 'Dani & Peter', 'Jöggu & Puppe', '2026-07-08', '18:00', $res(0, 2), 'chat L 03.07.26 Jöggu "Mittwoch 8. Juli 18.00Uhr" (29.06 cancelled), Puppe 👍');
        $c->setDate(2026, 'group', 'Armin & Beat', 'Kusi W. & Patrik', '2026-07-15', '18:30', $res(0, 2), 'chat L 06.07.26 Pädi "Mittwuch 15.07 18.30 Armin& Beat / Kusi&Pädi"');
        $c->setDate(2026, 'group', 'Armin & Beat', 'Jöggu & Puppe', '2026-07-20', '18:00', $res(1, 2), 'chat L 10.07.26 Armin "Mäntig 20. Juli, 18:00 Uhr. Bocciaspiel Jöggu / Puppe gägä Beat / Armin"');
        $c->setDate(2026, 'group', 'Armin & Beat', 'Dani & Peter', '2026-07-22', '18:30', $has(0, 2, '2026-07-22'), 'chat L 13.07.26 Dani "Mittwoch, 22.07.26, 18:30Uhr, Armin/Beat gägä Peter/Dani"');
        $c->setDate(2026, 'group', 'Attila & Rolf', 'Siri & Peter', '2026-06-14', '17:00', $res(0, 2), 'chat L 14.06.26 Peter "Hit am 5i Atila Rolf spielid mit Siri u Peter"');
        $c->setDate(2026, 'group', 'Lilo & Roli', 'Siri & Peter', '2026-06-28', '11:00', $res(2, 0), 'chat L 14.06.26 Sandra "So 28.Juni 11 Uhr Siri/Peter gägä Lilo/ Roli"; plan "28.6.26"');
        $c->setDate(2026, 'group', 'Lilo & Roli', 'Luana & Guli', '2026-06-28', '12:30', $res(2, 0), 'chat L 23.06.26 Roli "So. 28 juni guli luana lilo roli ca.12.30"');
        $c->setDate(2026, 'group', 'Sandra & Werni', 'Siri & Peter', '2026-06-28', '17:00', $res(2, 0), 'chat L 14.06.26 Sandra "17 Uhr Siri / Peter gägä Werni / Sandra"');
        $c->setDate(2026, 'group', 'Attila & Rolf', 'Lilo & Roli', '2026-07-04', '09:30', $res(0, 2), 'chat L 03.07.26 Lilo "4. Juli 9.30 Lilo u Roli / Rolf u Atti"; plan "04.07.2026"');
        $c->setDate(2026, 'group', 'Attila & Rolf', 'Luana & Guli', '2026-07-09', '18:00', $res(0, 2), 'chat L 04.07.26 Atti "09.07. 18.00 fortsetzig Match Luana/Guli vs. Rolf/Atti"');
        $c->setDate(2026, 'group', 'Luana & Guli', 'Sandra & Werni', '2026-07-19', '17:00', $res(1, 2), 'chat L 16.07.26 Sandra "Sonntig 19.Juli 17 Uhr Sandra/Werni gägä Luana/Guli"');
        $c->setDate(2026, 'group', 'Luana & Guli', 'Siri & Peter', '2026-07-29', '18:00', $res(0, 2), 'chat L 22.07.26 Guli "Mittwoch, 29. 07. um 18:00Uhr Siri/Peter vs. Luana/Guli"');
        $c->setDate(2026, 'group', 'Lilo & Roli', 'Sandra & Werni', '2026-08-03', '18:00', $res(2, 1), 'chat L 01.08.26 Sandra "Montag 3.August 18 Uhr Werni/Sandra gägä Lilo/Roli"');
        $c->setDate(2026, 'group', 'Attila & Rolf', 'Sandra & Werni', '2026-08-04', '18:00', $res(0, 2), 'chat L 02.08.26 Atti "Di. 04.08. 18.00 Uhr Sandra/ Werni vs. Rolf/ Atti"');

        // Block 3 -- KO dates
        $c->setDate(2026, 'qf', 'Lilo & Roli', 'Tubä & Wurm', '2026-08-09', '17:00', $res(2, 0), 'chat L 08.08.26 Tube "Morn sunntig 9 aug. Spiel Tubä Wurm vs Roli Lilo 1700"');
        $c->setDate(2026, 'qf', 'Erika & Debi', 'Evelyne & Sven', '2026-08-16', '18:00', $res(2, 0), 'chat L 11.08.26 Erika "So, 16.8, 18.00 Uhr, Sven/Eveline gäg Debi/Erika"');
        $c->setDate(2026, 'qf', 'Jöggu & Puppe', 'Sandra & Werni', '2026-08-18', '16:00', $res(2, 0), 'chat L 17.08.26 Sandra "Di. 18.8. 16 Uhr Werni/ Sandra gägä Puppe/Jöggu" (17.08 postponed)');
        $c->setDate(2026, 'qf', 'Erwin & Stierli', 'Dani & Peter', '2026-08-18', '18:30', $res(2, 0), 'chat L 13.08.26 Stierli "Di.18.8.18.30 uhr peter dani erwin roli"');
        $c->setDate(2026, 'sf', 'Erwin & Stierli', 'Lilo & Roli', '2026-08-19', '13:30', $res(0, 2), 'chat L 18.08.26 Lilo "Edwä/Stierli-Roli/Lilo 19.8. 26, 13.30 Uhr"');
        $c->setDate(2026, 'sf', 'Erika & Debi', 'Jöggu & Puppe', '2026-08-22', '10:00', $res(2, 0), 'chat L 18.08.26 Erika "Samstag, 22.8.26, 10.00 Uhr Halbfinal Puppe/Jöggu gägä Debi/Erika"');
    },
];
