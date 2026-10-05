<?php
// WhatsApp history import, season 2022 (missing in the DB) -- blocks 1-5 approved 2026-10-05.
// See PROPOSALS.md for the evidence. Chat L = Lilo's export; draw = photo 2074.
// "Patrick + Kusi" -> existing team "Pädi & Kusi" (#5), by decision. Group results are unknown
// (Frei & Frei beat Maja & Paschi, score unknown -> date only). QFs: winners known, no scores ->
// teams + dates; SF/3rd/final scores are known. QF slots arranged so the real semi-finals come out.

$open = ['sets' => [null, null], 'played_date' => null, 'game_time' => null];

return [
    'name' => 'whatsapp-history-2022',
    'actor_label' => 'WhatsApp-Import 2022',
    'summary' => 'Adds the 2022 season (missing): groups, 1 new team, enrolments, fixtures, 29 group dates and the bracket (SF/3rd/final with scores), from the WhatsApp chat (Lilo export) and draw photo 2074.',
    'apply' => function (Changeset $c) use ($open): void {
        // Block 1 -- season + new team
        $c->createSeason('Sommerturnier 2022', 2022, 4);
        $c->createTeam('Caroline & Marius', 'Caroline', 'Marius', 'chat L 22.04.22 Erika "Caroline und Marius"; draw photo 2074 "Caroline + Marius"');

        // Block 2 -- enrolments per draw photo 2074, fixtures
        $groups = [
            'A' => ['Caroline & Marius', 'Henry & Husi', 'Evelyne & Sven', 'Dani & Peter', 'Zimi & Roman'],
            'B' => ['Andrea & Armin', 'Tubä & Wurm', 'Bruno & Werni', 'Frei & Frei', 'Maja & Paschi'],
            'C' => ['Sandra & Stella', 'Bruni & Tiz', 'Pädi & Kusi', 'Moni & Erwin'],
            'D' => ['Erika & Debi', 'Jöggu & Puppe', 'Lilo & Roli', 'Bea & Gysi', 'Georges & Stierli'],
        ];
        foreach ($groups as $group => $teams) {
            foreach ($teams as $team) {
                $c->enroll(2022, $group, $team);
            }
            $c->generateFixtures(2022, $group);
        }

        // Block 3 -- groups A and B (dates only)
        $c->setDate(2022, 'group', 'Henry & Husi', 'Caroline & Marius', '2022-06-27', '18:00', $open, 'chat L 25.06.22 Caro "gad au 18.00 uhr?", Husi "Ja isch guät mo 18.00"');
        $c->setDate(2022, 'group', 'Evelyne & Sven', 'Caroline & Marius', '2022-07-05', '18:15', $open, 'chat L 27.06.22 Evelyne "di. 5. juli, ab 18.15 uhr", Caro "mier"; 04.07 Caro confirms');
        $c->setDate(2022, 'group', 'Henry & Husi', 'Evelyne & Sven', '2022-07-05', null, $open, 'chat L 28.06.22 Husi "Anschliessed hädit de Henry und de Husi ai zeyt", Evelyne "yes"');
        $c->setDate(2022, 'group', 'Henry & Husi', 'Dani & Peter', '2022-07-06', '19:00', $open, 'chat L 05.07.22 Dani "Spiel Mi 06.07, 19:00 Uhr, Henry/Husi gegen Peter/Dani"');
        $c->setDate(2022, 'group', 'Evelyne & Sven', 'Zimi & Roman', '2022-07-13', '18:30', $open, 'chat L 06.07.22 Evelyne "mi. 13. 07., 18.30"; 11.07 "am mittwuch 18.30gi"');
        $c->setDate(2022, 'group', 'Dani & Peter', 'Caroline & Marius', '2022-07-15', '18:15', $open, 'chat L 10.07.22 Caro "fritig 15. juli 18.15 peter + dani gäge marius & caro"');
        $c->setDate(2022, 'group', 'Evelyne & Sven', 'Dani & Peter', '2022-07-17', '09:15', $open, 'chat L 15.07.22 Dani "So 17.07, 09:15 Uhr, Match Eveline/Sven gägä Peter/Dani"');
        $c->setDate(2022, 'group', 'Zimi & Roman', 'Dani & Peter', '2022-07-18', '18:30', $open, 'chat L 16.07.22 Dani "Zimi/Roman gägä Peter/Dani am Mo 18.07, 18:30 Uhr", Roman 👍');
        $c->setDate(2022, 'group', 'Zimi & Roman', 'Caroline & Marius', '2022-07-22', '18:30', $open, 'chat L 19.07.22 Zimmi "Freitag, 22.7., 18:30 Uhr gegen uns?" (proposed, no reply)');
        $c->setDate(2022, 'group', 'Henry & Husi', 'Zimi & Roman', '2022-07-26', '18:30', $open, 'chat L 24.07.22 Roman "Di. 18:30", Husi "Ok machid mir Di 18:30"');
        $c->setDate(2022, 'group', 'Tubä & Wurm', 'Andrea & Armin', '2022-06-23', '18:00', $open, 'chat L 20.06.22 Joli "Dunschtig Abig am 6i isch fir eys o.k."');
        $c->setDate(2022, 'group', 'Maja & Paschi', 'Tubä & Wurm', '2022-06-26', '16:30', $open, 'chat L 26.06.22 Tube "Ok, spielen wir heute ab 1630"');
        $c->setDate(2022, 'group', 'Tubä & Wurm', 'Frei & Frei', '2022-07-06', '18:30', $open, 'chat L 05.07.22 Tube "Isch scho es Spiel morn am1830..... Tube Wurm. Frey frey"');
        $c->setDate(2022, 'group', 'Maja & Paschi', 'Frei & Frei', '2022-07-09', '09:30', $open, 'chat L 04.07.22 Peter Frei "Samstig 9i 30 isch sehr guet"; 09.07 Maja "Glückwunsch an Frey/Frey" (score unknown)');
        $c->setDate(2022, 'group', 'Andrea & Armin', 'Frei & Frei', '2022-07-12', '18:00', $open, 'chat L 04.07.22 Patrick Frei "Di 12.07. 18:00 Uhr passt!"');
        $c->setDate(2022, 'group', 'Tubä & Wurm', 'Bruno & Werni', '2022-07-14', '18:00', $open, 'chat L 12.07.22 Bea "Do 14.7.22. 18.00 Wurm/ Tuibä versus Bruno/Werni"');
        $c->setDate(2022, 'group', 'Frei & Frei', 'Bruno & Werni', '2022-07-21', '18:00', $open, 'chat L 20.07.22 Bea "Neuer Termin Frei/Frei gäg Bruno /Werni Do 21.7.22 18.00"');
        $c->setDate(2022, 'group', 'Andrea & Armin', 'Maja & Paschi', '2022-08-01', '09:30', $open, 'chat L 27.07.22 Maja "Dann machen wir doch 9:30 Ihr am 1. August"');
        $c->setDate(2022, 'group', 'Bruno & Werni', 'Maja & Paschi', '2022-08-03', '16:30', $open, 'chat L 01.08.22 Paschi "Diesen Mittwoch 3. August 1630: Werni/Bruno vs. Maja/Paschi"');

        // Block 4 -- groups C and D (dates only)
        $c->setDate(2022, 'group', 'Sandra & Stella', 'Moni & Erwin', '2022-06-25', '09:30', $open, 'chat L 21.06.22 Moni "Wemmer Samschtig 9.30?", Sandra + Stella "Passt"');
        $c->setDate(2022, 'group', 'Pädi & Kusi', 'Moni & Erwin', '2022-06-26', '12:00', $open, 'chat L 23.06.22 Pädi "am Sunntig … uf die 12fi", Moni "12 isch o.k."');
        $c->setDate(2022, 'group', 'Bruni & Tiz', 'Sandra & Stella', '2022-06-29', '18:30', $open, 'chat L 26.06.22 Tiz "Mittwuch 18.30 Stella ???", Stella "Passt am Mittwoch"');
        $c->setDate(2022, 'group', 'Moni & Erwin', 'Bruni & Tiz', '2022-06-30', '18:30', $open, 'chat L 26.06.22 Moni "Donnschtig 18.30 Moni Erwin? Tiziano und Brunella", Tiz ok');
        $c->setDate(2022, 'group', 'Sandra & Stella', 'Pädi & Kusi', '2022-07-02', '11:00', $open, 'chat L 29.06.22 Pädi "Sandra&Stella / Kusi&Pädi am Samschtig am 11.00"');
        $c->setDate(2022, 'group', 'Bruni & Tiz', 'Pädi & Kusi', '2022-07-04', '18:30', $open, 'chat L 29.06.22 Pädi "Tiz&Bruni / Kusi&Pädi am Mäntig 4.7. am 18.30"');
        $c->setDate(2022, 'group', 'Erika & Debi', 'Jöggu & Puppe', '2022-06-18', '14:00', $open, 'chat L 17.06.22 Debi "Morgen spielen Doppel 🤴 gegen Doppel 🤴 um 14.00 Uhr"');
        $c->setDate(2022, 'group', 'Erika & Debi', 'Bea & Gysi', '2022-06-26', '10:00', $open, 'chat L 23.06.22 Bea "So 10 Uhr Debys gegen Gysi (s) Bea", Gysi "Passt"');
        $c->setDate(2022, 'group', 'Jöggu & Puppe', 'Bea & Gysi', '2022-07-12', '19:30', $open, 'chat L 06.07.22 Bea "anschliessend 12.7. 19.30 Thomi/Jürg:Roli/Bea"');
        $c->setDate(2022, 'group', 'Bea & Gysi', 'Georges & Stierli', '2022-07-13', '10:00', $open, 'chat L 06.07.22 Bea "Mi 13.7. morgens um 10 Uhr: Roli/Bea: Stierli/Georg"');

        // Block 5 -- bracket
        $c->setBracketTeams(2022, 'qf', 1, 'Lilo & Roli', 'Pädi & Kusi', 'chat L 31.07.22 Pädi "Am 10.08 um 18.00 spilid Lilo&Roli gägä Kusi&Pädi"');
        $c->setBracketTeams(2022, 'qf', 2, 'Tubä & Wurm', 'Zimi & Roman', 'chat L 01./02.08.22 Tube/Zimmi "9 Aug … haubi 7ni"');
        $c->setBracketTeams(2022, 'qf', 3, 'Erika & Debi', 'Frei & Frei', 'chat L 19.08.22 Erika "De machemer doch 13.00Uhr" (21.08)');
        $c->setBracketTeams(2022, 'qf', 4, 'Dani & Peter', 'Sandra & Stella', 'chat L 23.08.22 Sandra "Samstig 27.August 13 uhr spielit Peter/Dani gägä Stella / Sandra"');
        $c->setDate(2022, 'qf', 'Lilo & Roli', 'Pädi & Kusi', '2022-08-10', '18:00', $open, 'chat L 31.07.22 Pädi "Am 10.08 um 18.00" (L&R won, score unknown)');
        $c->setDate(2022, 'qf', 'Tubä & Wurm', 'Zimi & Roman', '2022-08-09', '18:30', $open, 'chat L 02.08.22 Zimmi "Gahd ai haubi 7ni?", Tube "Jepp"; 09.08 18:50 "Das Match läuft" (T&W won, score unknown)');
        $c->setDate(2022, 'qf', 'Erika & Debi', 'Frei & Frei', '2022-08-21', '13:00', $open, 'chat L 19.08.22 Erika "13.00Uhr", Patrick Frei "Esch guet" (E&D won, score unknown)');
        $c->setDate(2022, 'qf', 'Dani & Peter', 'Sandra & Stella', '2022-08-27', '13:00', $open, 'chat L 23.08.22 Sandra "Samstig 27.August 13 uhr" (D&P won, score unknown)');
        $c->setBracketTeams(2022, 'sf', 1, 'Lilo & Roli', 'Tubä & Wurm', 'QF1 + QF2 winners');
        $c->setBracketTeams(2022, 'sf', 2, 'Erika & Debi', 'Dani & Peter', 'QF3 + QF4 winners');
        $c->setResult(2022, 'sf', 'Lilo & Roli', 'Tubä & Wurm', 2, 0, '2022-08-28', '13:00', $open, 'chat L 25.08.22 Tube "Sunntig lillo Rolli gegä Wurm tubä ab 1300uhr"; 28.08 Lilo "Erstes Spiel 2:0"');
        $c->setResult(2022, 'sf', 'Erika & Debi', 'Dani & Peter', 2, 1, '2022-08-28', null, $open, 'chat L 27.08.22 Sandra "nach lilo/roli - tubä/wurm grad erika/debi - peter/dani"; 28.08 17:33 Lilo "2:1 Liveticker beendet"');
        $c->setResult(2022, 'third', 'Dani & Peter', 'Tubä & Wurm', 2, 1, '2022-09-10', '10:00', $open, 'chat L 06.09.22 Erika "10.00 Uhr Final 3.+4.Platz"; 10.09 sets 21:20 / 13:21 / 3rd set, "Gwunnä hend dr Peter und Dani"');
        $c->setResult(2022, 'final', 'Erika & Debi', 'Lilo & Roli', 2, 1, '2022-09-11', '13:25', $open, 'chat L 11.09.22 Sandra 13:25 "das FINALE hat begonnen", "1:0 Erika/Debi 21:20", "1:1 21:6", 15:41 "Königspaar Erika & Debi"');
    },
];
