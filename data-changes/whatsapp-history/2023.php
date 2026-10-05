<?php
// WhatsApp history import, season 2023 -- blocks 1-4 approved 2026-10-05. See PROPOSALS.md for the evidence.
// Chat L = Lilo's export; plan = photo 2492 (11.06.23). KO winners known but no scores (decision D1):
// bracket filled with teams + dates only. Results already in the DB are not touched.

$open = ['sets' => [null, null], 'played_date' => null, 'game_time' => null];

return [
    'name' => 'whatsapp-history-2023',
    'actor_label' => 'WhatsApp-Import 2023',
    'summary' => 'Season 2023: swapped dates #8/#29 (2023-11-06 -> 2023-06-11), date for #18, 26 group dates, KO pairings + dates (no scores), from the WhatsApp chat (Lilo export) and plan photo 2492.',
    'apply' => function (Changeset $c) use ($open): void {
        // Block 1 -- date corrections from plan photo 2492
        $c->setDate(2023, 'group', 'Atti & Sarah', 'Evelyne & Sven', '2023-06-11', '17:00',
            ['sets' => [0, 2], 'played_date' => '2023-11-06', 'game_time' => '17:00:00'],
            'plan photo 2492 "Eveline/Sven – Atti/Sarah 11. Juni / 17.00, 2:0" (day/month were swapped)');
        $c->setDate(2023, 'group', 'Erika & Debi', 'Sandra & Stella', '2023-06-11', '14:00',
            ['sets' => [2, 0], 'played_date' => '2023-11-06', 'game_time' => '14:00:00'],
            'plan photo 2492 "Erika/Debi – Sandra/Stella 11. Juni 14.00, 2:0" (day/month were swapped)');
        $c->setDate(2023, 'group', 'Bea & Gysi', 'Frei & Frei', '2023-06-08', '17:00',
            ['sets' => [2, 1], 'played_date' => null, 'game_time' => null],
            'plan photo 2492 "Frei/Frei – Bea/Gysi 8. Juni 17.00, 1/2" (chat 08.06 17:22 Frei still looking for a game -- plan photo followed)');

        // Block 2 -- groups A and B
        $c->setDate(2023, 'group', 'Jöggu & Puppe', 'Georges & Stierli', '2023-06-26', '18:30', $open, 'chat L 22.06.23 Jöggu "Mendig, 26.6. ab 18:30 Uhr", Georg + Stierli ok');
        $c->setDate(2023, 'group', 'Evelyne & Sven', 'Georges & Stierli', '2023-06-28', '19:00', $open, 'chat L 23.06.23 Evelyne "mittwuch, 28. 6., am 19.00i", Georg + Stierli ok');
        $c->setDate(2023, 'group', 'Evelyne & Sven', 'Jöggu & Puppe', '2023-07-05', '19:00', $open, 'chat L 28.06.23 Evelyne "mi. 5. juli, 19.00", Puppe + Jöggu ok');
        $c->setDate(2023, 'group', 'Atti & Sarah', 'Jöggu & Puppe', '2023-07-09', '14:00', $open, 'chat L 05.07.23 Sarah "dä Sunntig … 10.00 / 14.00?", Puppe "14.00Uhr ist Ok"');
        $c->setDate(2023, 'group', 'Georges & Stierli', 'Atti & Sarah', '2023-07-15', '10:00', $open, 'chat L 10.07.23 Sarah "SA, 15.7. / 10.00 Uhr Georg/Roland und Attila/Sarah", Georg + Stierli ok');
        $c->setDate(2023, 'group', 'Pädi & Kusi', 'Georges & Stierli', '2023-07-19', '19:00', $open, 'chat L 12.07.23 Georg "hüt nid spielä", Pädi "nechscht Mittwuch um 19.00", Georg "okay"');
        $c->setDate(2023, 'group', 'Jöggu & Puppe', 'Pädi & Kusi', '2023-07-20', '19:00', $open, 'chat L 13.07.23 Jöggu "Dunnschtig, 20.7. am 19.00?", Kusi + Pädi ok');
        $c->setDate(2023, 'group', 'Pädi & Kusi', 'Evelyne & Sven', '2023-08-02', '18:30', $open, 'chat L 27.07.23 Pädi "Mittwuch 2.8 … 18.30", "Sven und Eveline am Mittwoch"');
        $c->setDate(2023, 'group', 'Pädi & Kusi', 'Atti & Sarah', '2023-08-05', '09:00', $open, 'chat L 27.07.23 Pädi "Samschtig 5.8 um 9.00"; 05.08 Debi "Findet das Spiel statt", Kusi "Ja"');
        $c->setDate(2023, 'group', 'Tubä & Wurm', 'Frei & Frei', '2023-06-14', '17:00', $open, 'chat L 10.06.23 Tube "am 14 … abem 1700", Patrick Frei "Passt tube!"');
        $c->setDate(2023, 'group', 'Tubä & Wurm', 'Bea & Gysi', '2023-06-15', '18:30', $open, 'chat L 12.06.23 Bea "Do 15.6. 18.30", Gysi "passt … Donnerstag 18.30 Uhr"');
        $c->setDate(2023, 'group', 'Tubä & Wurm', 'Lilo & Roli', '2023-07-08', '11:00', $open, 'chat L 07.07.23 Lilo "Samstag 11.00 Wurm/Tube - Roli/ Lilo"');
        $c->setDate(2023, 'group', 'Lilo & Roli', 'Bea & Gysi', '2023-07-10', '18:00', $open, 'chat L 06.07.23 Bea "Montag, 10.7. 23. 18.00 Lilo/Roli:Gysi/Bea"');
        $c->setDate(2023, 'group', 'Henry & Husi', 'Bea & Gysi', '2023-07-20', '10:30', $open, 'chat L 17.07.23 Husi "Do 20 juli spielt henry und Husi gegen bea und rolly", "10.30"');
        $c->setDate(2023, 'group', 'Henry & Husi', 'Lilo & Roli', '2023-07-22', '10:00', $open, 'chat L 20.07.23 Husi "Samstag 22 juli 10..00 Uhr … gegen Rolli und Lilo"');
        $c->setDate(2023, 'group', 'Henry & Husi', 'Tubä & Wurm', '2023-07-23', '09:30', $open, 'chat L 22.07.23 Tube "Morn sunntig husi henrie gägä tubä Wurm. 0930"');
        $c->setDate(2023, 'group', 'Lilo & Roli', 'Frei & Frei', '2023-08-16', '17:00', $open, 'chat L 08.08.23 Patrick Frei "Mi, 16.08. 17:00 uhr lilo/roli vs. frei/frei"');

        // Block 3 -- groups C and D
        $c->setDate(2023, 'group', 'Maja & Paschi', 'Erika & Debi', '2023-06-18', '10:00', $open, 'chat L 15.06.23 Maja "Sonntagmorgen … So zehn Uhr?", Erika "Wir sind parat"; 18.06 "Wir sind gleich da"');
        $c->setDate(2023, 'group', 'Zimi & Roman', 'Erika & Debi', '2023-07-04', '19:00', $open, 'chat L 28.06.23 Zimmi "Dienstag, 4. Juli um 19:00 Uhr", Debi "passt uns"');
        $c->setDate(2023, 'group', 'Sandra & Stella', 'Zimi & Roman', '2023-07-23', '14:00', $open, 'chat L 22.07.23 Stella "morre 14:00 Uhr", Roman + Zimmi "Passt"');
        $c->setDate(2023, 'group', 'Maja & Paschi', 'Zimi & Roman', '2023-07-25', '18:00', $open, 'chat L 24.07.23 Zimmi "18:00 Uhr", Paschi "Passt au"; 25.07 Maja "Bis 18 Uhr dann!"');
        $c->setDate(2023, 'group', 'Moni & Erwin', 'Andrea & Armin', '2023-07-02', '10:30', $open, 'chat L 29.06.23 Moni "Sunntig morgä 10.30", Joli "tiptop"; 02.07 "Boccia findet statt"');
        $c->setDate(2023, 'group', 'Bruni & Tiz', 'Dani & Peter', '2023-07-06', '18:00', $open, 'chat L 03.07.23 Dani "am Dunnschtig, 6. Juli, 18:00 Uhr", Tiz "perfetto"');
        $c->setDate(2023, 'group', 'Moni & Erwin', 'Dani & Peter', '2023-07-13', '18:00', $open, 'chat L 06.07.23 Dani "Donnerstag, 13.07, 18:00 Uhr … Moni/Edwin - Peter/Dani"');
        $c->setDate(2023, 'group', 'Andrea & Armin', 'Bruni & Tiz', '2023-07-18', '18:15', $open, 'chat L 16.07.23 Bruni "Am Di.18.Juli", "18.15", Armin "Super"');
        $c->setDate(2023, 'group', 'Moni & Erwin', 'Bruni & Tiz', '2023-07-21', '18:15', $open, 'chat L 14.07.23 Bruni "Am Fritig abig ab dä 6i", Moni "18.15 o.k."');
        $c->setDate(2023, 'group', 'Andrea & Armin', 'Dani & Peter', '2023-07-22', '13:00', $open, 'chat L 16.07.23 Dani "Sa, 22.07, 13:00 Uhr?", Armin "isch ok"');

        // Block 4 -- bracket, teams + dates (winners: L&R, E&D, G&S, T&W; SF: L&R, G&S; final: L&R)
        $c->setBracketTeams(2023, 'qf', 1, 'Lilo & Roli', 'Pädi & Kusi', 'chat L 16.08.23 Pädi "Dunnschtig 24.8 um 18.30"');
        $c->setBracketTeams(2023, 'qf', 2, 'Erika & Debi', 'Bruni & Tiz', 'chat L 08.08.23 Erika "Viertelsfinal"');
        $c->setBracketTeams(2023, 'qf', 3, 'Georges & Stierli', 'Moni & Erwin', 'chat L 11.08.23 Moni "Georg Stierli /Moni Edwä"');
        $c->setBracketTeams(2023, 'qf', 4, 'Tubä & Wurm', 'Zimi & Roman', 'chat L 16./17.08.23 Tube/Roman');
        $c->setDate(2023, 'qf', 'Lilo & Roli', 'Pädi & Kusi', '2023-08-24', '18:30', $open, 'chat L 16.08.23 Pädi "Dunnschtig 24.8 um 18.30 Uhr", Lilo "Ja Donnerstag isch gued"');
        $c->setDate(2023, 'qf', 'Erika & Debi', 'Bruni & Tiz', '2023-08-14', '17:00', $open, 'chat L 11.08.23 Erika "Spiel am 14.8.23 ab 17.00 Uhr?", Bruni 👍');
        $c->setDate(2023, 'qf', 'Georges & Stierli', 'Moni & Erwin', '2023-08-13', '10:00', $open, 'chat L 11.08.23 Moni "13.08.2023 Georg Stierli /Moni Edwä 10.00"');
        $c->setDate(2023, 'qf', 'Tubä & Wurm', 'Zimi & Roman', '2023-08-22', '18:30', $open, 'chat L 17.08.23 Roman "am Zeyschtig ab 18:30", Wurm "Passt"');
        $c->setBracketTeams(2023, 'sf', 1, 'Lilo & Roli', 'Erika & Debi', 'QF1 + QF2 winners');
        $c->setDate(2023, 'sf', 'Lilo & Roli', 'Erika & Debi', '2023-08-26', '13:15', $open, 'chat L 24.08.23 Erika "Samstag, 16.00 Uhr s\'Imbodä\'s gägä s\'Debi\'s"; 26.08 "vorverschiäbä uf diä 13.15 Uhr"');
        $c->setBracketTeams(2023, 'sf', 2, 'Georges & Stierli', 'Tubä & Wurm', 'QF3 + QF4 winners');
        $c->setDate(2023, 'sf', 'Georges & Stierli', 'Tubä & Wurm', '2023-08-25', '17:00', $open, 'chat L 22.08.23 Tube "Tubä Wurm spielen am Freitag 1700 gegen stierli georg"');
        $c->setBracketTeams(2023, 'third', 1, 'Erika & Debi', 'Tubä & Wurm', 'SF losers');
        $c->setDate(2023, 'third', 'Erika & Debi', 'Tubä & Wurm', '2023-09-10', '10:00', $open, 'chat L 30.08.23 Wurm "Wurm/Tubä gägä Erika/Debi isch erscht am Sunntig um 10.00 Uhr" (winner unknown)');
        $c->setBracketTeams(2023, 'final', 1, 'Lilo & Roli', 'Georges & Stierli', 'SF winners');
        $c->setDate(2023, 'final', 'Lilo & Roli', 'Georges & Stierli', '2023-09-10', '11:00', $open, 'chat L 31.08.23 Lilo "Boccia Final 10. September 11.00"; 10.09 congratulations to new queen + king (L&R won, score unknown)');
    },
];
