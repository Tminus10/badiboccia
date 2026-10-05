<?php
// WhatsApp history import, season 2021 -- blocks 1-4 approved 2026-10-05. See PROPOSALS.md for the evidence.
// Chat L = Lilo's export. KO winners are known but no set scores, so (decision D1) the SF/3rd/final
// are filled with teams + dates only. Group results already in the DB are not touched.

$dated = fn (int $a, int $b): array => ['sets' => [$a, $b], 'played_date' => null, 'game_time' => null];
$open = ['sets' => [null, null], 'played_date' => null, 'game_time' => null];

return [
    'name' => 'whatsapp-history-2021',
    'actor_label' => 'WhatsApp-Import 2021',
    'summary' => 'Season 2021: QF2 opponent Bea & Gysi -> Andrea & Armin, KO pairings + dates (no scores), dates for 25 group games, from the WhatsApp chat (Lilo export).',
    'apply' => function (Changeset $c) use ($dated, $open): void {
        // Block 1 -- QF2 opponent
        $c->replaceBracketTeam(2021, 'qf', 2, 'Bea & Gysi', 'Andrea & Armin', 'chat L 26.08.21 Lilo "Morn am houbi 6i. Joli Armin / Lilo Roli" (Joli = Andrea); no QF ever arranged for Bea & Gysi');

        // Block 2 -- KO: QF dates, then later rounds as teams + dates (winners known, scores not)
        $c->setDate(2021, 'qf', 'Erika & Debi', 'Dani & Peter', '2021-09-03', '17:30', $open, 'chat L 31.08.21 Debi "Freitag 17.30 Uhr Peter/Dani gegen Erika und Debi den Viertelfinale"');
        $c->setDate(2021, 'qf', 'Lilo & Roli', 'Andrea & Armin', '2021-08-27', '17:30', $open, 'chat L 26.08.21 Lilo "Morn am houbi 6i. Joli Armin / Lilo Roli"');
        $c->setDate(2021, 'qf', 'Stella & Patrik', 'Jöggu & Puppe', '2021-09-04', '09:30', $open, 'chat L 31.08.21 Stella "Samstag, 09:30 Uhr Uhu/Jöggu gegen Patrick/Stella"; 04.09 "äs gid ai das Jahr ä Königin"');
        $c->setDate(2021, 'qf', 'Frei & Frei', 'Sandra & Werni', '2021-09-01', '18:30', $open, 'chat L 29.08.21 Bea "Mi oder Do 18.30 Sandra/Werni : Frei/Frei?"; 30.08 Peter Frei "am Mi isch guet"');
        $c->setBracketTeams(2021, 'sf', 1, 'Erika & Debi', 'Lilo & Roli', 'QF1 + QF2 winners');
        $c->setDate(2021, 'sf', 'Erika & Debi', 'Lilo & Roli', '2021-09-04', '13:30', $open, 'chat L 03.09.21 Lilo "Morn am houbi 2 spielid Erika / Debi - Lilo / Roli"');
        $c->setBracketTeams(2021, 'sf', 2, 'Stella & Patrik', 'Sandra & Werni', 'QF3 + QF4 winners');
        $c->setDate(2021, 'sf', 'Stella & Patrik', 'Sandra & Werni', '2021-09-23', '17:30', $open, 'chat L 20.09.21 Stella "Donnerstag … auf die sechse"; 23.09 Sandra "uf diä 17.30"');
        $c->setBracketTeams(2021, 'third', 1, 'Lilo & Roli', 'Stella & Patrik', 'SF losers');
        $c->setDate(2021, 'third', 'Lilo & Roli', 'Stella & Patrik', '2021-09-26', '11:00', $open, 'chat L 24.09.21 Lilo "Finaltag … 11.00 Uhr Stella Pädi-Lilo Roli" (winner unknown)');
        $c->setBracketTeams(2021, 'final', 1, 'Erika & Debi', 'Sandra & Werni', 'SF winners');
        $c->setDate(2021, 'final', 'Erika & Debi', 'Sandra & Werni', '2021-09-26', '13:30', $open, 'chat L 24.09.21 Lilo "13.30 Uhr Erika Debi- Sandra Werni"; 26.09 Bea "Die Königin und der König bleiben" (E&D won)');

        // Block 3 -- groups A and B
        $c->setDate(2021, 'group', 'Jöggu & Puppe', 'Moni & Erwin', '2021-07-03', null, $dated(2, 1), 'chat L 02.07.21 Jöggu "Samstig ab 13:30", Puppe "Okay"; 03.07 Moni "2-1 verlorä"');
        $c->setDate(2021, 'group', 'Erika & Debi', 'Moni & Erwin', '2021-07-31', '11:00', $dated(2, 0), 'chat L 30.07.21 Erika "11.00 Uhr gäg Moni/Edwä"');
        $c->setDate(2021, 'group', 'Erika & Debi', 'Bruno & Georges', '2021-07-31', '14:00', $dated(2, 0), 'chat L 30.07.21 Erika "14.00 Uhr gäg Bruno/ Georges", Georg "Okay"');
        $c->setDate(2021, 'group', 'Bruni & Tiz', 'Moni & Erwin', '2021-08-06', '18:15', $dated(2, 1), 'chat L 06.08.21 Moni "uf diä 6i?", Bruni "vielleicht wirds viertel ab 6i"');
        $c->setDate(2021, 'group', 'Bruni & Tiz', 'Erika & Debi', '2021-08-12', '18:30', $dated(0, 2), 'chat L 08.08.21 Debi "Super Donnerstag 18.30 Uhr Tiz Bruni gägä Erika & Debi"');
        $c->setDate(2021, 'group', 'Bruni & Tiz', 'Jöggu & Puppe', '2021-08-17', '18:30', $dated(0, 2), 'chat L 11.08.21 Puppe "Dienstag … ca.18.30", Tiz ok; 17.08 both coming');
        $c->setDate(2021, 'group', 'Erika & Debi', 'Jöggu & Puppe', '2021-08-18', '18:30', $dated(2, 1), 'chat L 11.08.21 Erika "mittwuch 18.8.2021! Um 18.30 guät"');
        $c->setDate(2021, 'group', 'Bruni & Tiz', 'Bruno & Georges', '2021-08-25', '18:30', $dated(1, 2), 'chat L 22.08.21 Georg "Mechemer am Mittwoch 18:30", Tiz "Perfetto"');
        $c->setDate(2021, 'group', 'Bruno & Georges', 'Jöggu & Puppe', '2021-08-31', '18:00', $dated(2, 1), 'chat L 29.08.21 Puppe "Dienstag", Jöggu "Erscht 18.00 Uhr ai möglich?", Puppe "Ja", Georg "Okay"');
        $c->setDate(2021, 'group', 'Zimi & Roman', 'Lilo & Roli', '2021-07-29', '19:00', $dated(1, 2), 'chat L 29.07.21 Lilo "Hit am 7ni spielt Zimi Roman/Imbodä"');
        $c->setDate(2021, 'group', 'Tubä & Wurm', 'Zimi & Roman', '2021-08-02', '19:00', $dated(2, 0), 'chat L 29.07.21 Tube "Am Montag spielen Wurm tubä VS zimmi Roman 19uhr"; 02.08 "Isch offe"');
        $c->setDate(2021, 'group', 'Sandra & Werni', 'Zimi & Roman', '2021-08-11', '19:00', $dated(2, 1), 'chat L 08.08.21 Sandra "mittwuch am 7ni zimi/roman gägä werni/ sandra"; 11.08 "isch hit"');
        $c->setDate(2021, 'group', 'Sandra & Werni', 'Tubä & Wurm', '2021-08-23', '18:30', $dated(2, 1), 'chat L 21.08.21 Tube "Montag spielit Tube Wurm VS. Sandra Werni 1830!"');

        // Block 4 -- groups C and D
        $c->setDate(2021, 'group', 'Maja & Paschi', 'Stella & Patrik', '2021-06-27', '15:00', $dated(0, 2), 'chat L 27.06.21 Maja "So 15 Uhr ungefähr?", Pädi 👍');
        $c->setDate(2021, 'group', 'Dani & Peter', 'Maja & Paschi', '2021-07-10', '15:00', $dated(2, 0), 'chat L 07.07.21 Maja "am Samstag so gegen 15 Uhr", Peter "super"');
        $c->setDate(2021, 'group', 'Dani & Peter', 'Henry & Stierli', '2021-08-06', '16:00', $dated(2, 1), 'chat L 06.08.21 Ruth "Heute 16.00 Uhr spielen Dani und Peter gegen Stierli und Henry"');
        $c->setDate(2021, 'group', 'Dani & Peter', 'Evelyne & Sven', '2021-08-19', '18:00', $dated(2, 0), 'chat L 18.08.21 Dani "ist 18:00 Uhr okay?", Evelyne "tipptopp"');
        $c->setDate(2021, 'group', 'Henry & Stierli', 'Maja & Paschi', '2021-08-20', '15:00', $dated(1, 2), 'chat L 18.08.21 Paschi "Freitag 1500: Stierli/Henry gegen The Boccialors"');
        $c->setDate(2021, 'group', 'Henry & Stierli', 'Stella & Patrik', '2021-08-21', '11:00', $dated(2, 1), 'chat L 17.08.21 Stella "Samstag: 11:00 Uhr Patrick/Stella gegen Stierli/Henry"');
        $c->setDate(2021, 'group', 'Evelyne & Sven', 'Stella & Patrik', '2021-08-21', '13:00', $dated(0, 2), 'chat L 17.08.21 Stella "Samstag: 13:00 Uhr Patrick/Stella gegen Evelyn/Sven", Evelyne "das passt"');
        $c->setDate(2021, 'group', 'Dani & Peter', 'Stella & Patrik', '2021-08-30', '18:00', $dated(1, 2), 'chat L 27.08.21 Stella "nur ein Satz zu spielen"; 29.08 "Montag 18.00 Uhr", Dani "passt"');
        $c->setDate(2021, 'group', 'Frei & Frei', 'Pia & Kusi', '2021-07-07', '17:30', $dated(2, 0), 'chat L 06.07.21 Peter Frei "Morn wär guet ab 17ni 30", Kusi "für mich io"');
        $c->setDate(2021, 'group', 'Bea & Gysi', 'Frei & Frei', '2021-07-31', '12:30', $dated(1, 2), 'chat L 30.07.21 Bea "12.30 Frei/Frei - Gysi/Bea", Peter Frei "ischs guet"');
        $c->setDate(2021, 'group', 'Bea & Gysi', 'Pia & Kusi', '2021-08-13', '15:30', $dated(2, 0), 'chat L 09.08.21 Bea "Freitag 15.30 Boccia Pia/Kusi - Roli/Bea", Kusi "Ja das isch guet"');
        $c->setDate(2021, 'group', 'Andrea & Armin', 'Frei & Frei', '2021-08-19', '19:15', $dated(0, 2), 'chat L 18.08.21 Peter Frei "morn nachem 1.Spiel so 19ni15", Joli "Passt"');
    },
];
