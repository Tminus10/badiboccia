<?php
// WhatsApp history import, season 2019 (missing in the DB) -- blocks 1-5 approved 2026-10-02.
// See PROPOSALS.md for the evidence. Chat L = Lilo's export; photo numbers are attachment file numbers.
// SF2, 3rd place and final: only the winners are known (J&P, Bea & Gysi, J&P) -- by decision stored
// as pairings + dates without scores.

$empty = ['sets' => [null, null], 'played_date' => null, 'game_time' => null];

return [
    'name' => 'whatsapp-history-2019',
    'actor_label' => 'WhatsApp-Import 2019',
    'summary' => 'Adds the 2019 season (missing): groups, 1 new team, enrolments, fixtures, known group results/dates and the bracket, from the WhatsApp chat (Lilo export) and photos 0245/0287/0394/0404/0631.',
    'apply' => function (Changeset $c) use ($empty): void {
        // Block 1 -- season + new team (draw photo 0245, plans photo 0287)
        $c->createSeason('Sommerturnier 2019', 2019, 4);
        $c->createTeam('Sandra & Matthias', 'Sandra', 'Matthias', 'photo 0245 "Sandra + Matthias", photo 0287 "Sandra / Matthias"');

        // Block 2 -- enrolments (photos 0245 + 0287 agree) and round-robin fixtures
        $groups = [
            'A' => ['Frei & Frei', 'Odi & Stierli', 'Georges & Peter', 'Debi & Städi'],
            'B' => ['Zimi & Roman', 'Bruno & Werni', 'Lilo & Roli', 'Stella & Patrik'],
            'C' => ['Andrea & Armin', 'Moni & Erwin', 'Sandra & Matthias', 'Tubä & Wurm'],
            'D' => ['Bea & Gysi', 'Erika & Christine', 'Maja & Paschi', 'Jöggu & Puppe'],
        ];
        foreach ($groups as $group => $teams) {
            foreach ($teams as $team) {
                $c->enroll(2019, $group, $team);
            }
            $c->generateFixtures(2019, $group);
        }

        // Block 3 -- groups A and B
        $c->setResult(2019, 'group', 'Frei & Frei', 'Odi & Stierli', 2, 0, null, null, $empty, 'plan photo 0394 (13.07.19)');
        $c->setResult(2019, 'group', 'Frei & Frei', 'Georges & Peter', 0, 2, null, null, $empty, 'plan photo 0394');
        $c->setResult(2019, 'group', 'Odi & Stierli', 'Georges & Peter', 2, 0, null, null, $empty, 'plan photo 0394');
        $c->setResult(2019, 'group', 'Odi & Stierli', 'Debi & Städi', 2, 0, '2019-07-13', '14:00', $empty, 'plan photo 0394; chat L 13.07.19 Debi "Heute am zwei Odi Stirli gägä Städi Debi"');
        $c->setDate(2019, 'group', 'Debi & Städi', 'Frei & Frei', '2019-07-27', '14:00', $empty, 'chat L 23.07.19 Debi "Samstag ab 14.00", Peter Frei "Isch guet"; photo 0497 27.07 15:07');
        $c->setDate(2019, 'group', 'Debi & Städi', 'Georges & Peter', '2019-08-03', '14:00', $empty, 'chat L 30.07.19 Debi "Samstag 14.00 Uhr würde es Peter, Georg und mir gehen", Städi "sollte gehen"');
        $c->setDate(2019, 'group', 'Lilo & Roli', 'Stella & Patrik', '2019-07-20', '14:00', $empty, 'chat L 19.07.19 Lilo "Mier spielid am 2 Stella Paddy Lilo Roli"');
        $c->setDate(2019, 'group', 'Stella & Patrik', 'Zimi & Roman', '2019-07-24', '19:00', $empty, 'chat L 17.07.19 Zimmi "Nechscht Mittwuch, 24. Juli, 19:00 Stella/Pädi vs. Roman/Zimi"');
        $c->setDate(2019, 'group', 'Lilo & Roli', 'Bruno & Werni', '2019-08-01', null, $empty, 'chat L 30.07.19 Lilo "1.8. ab nach de königliche … spielid Lilo Roli Bruno Beat"');
        $c->setDate(2019, 'group', 'Stella & Patrik', 'Bruno & Werni', '2019-08-03', '16:00', $empty, 'chat L 03.08.19 Odi "um 16.00 … bruno und beat", Stella "Ja passt auf viere"');

        // Block 4 -- groups C and D
        $c->setResult(2019, 'group', 'Moni & Erwin', 'Tubä & Wurm', 0, 2, '2019-06-26', null, $empty, 'plan photo 0404 "26.6.19 · 0:2"');
        $c->setResult(2019, 'group', 'Andrea & Armin', 'Tubä & Wurm', 2, 0, null, null, $empty, 'plan photo 0404; chat L 13.07.19 Lilo "Königin Andrea hed mit meym Armin gwunne", Puppe "Tubä Wurm"');
        $c->setDate(2019, 'group', 'Sandra & Matthias', 'Tubä & Wurm', '2019-07-16', '18:30', $empty, 'chat L 13.07.19 Tube "Sandra mathi spielet am dienstag gege wurm und tubä Um 1830"');
        $c->setDate(2019, 'group', 'Moni & Erwin', 'Andrea & Armin', '2019-07-18', '19:15', $empty, 'chat L 14.07.19 Moni "erscht 19.15", Armin "ai ok"');
        $c->setDate(2019, 'group', 'Andrea & Armin', 'Sandra & Matthias', '2019-07-22', '18:30', $empty, 'chat L 21.07.19 Armin "morn Abig … Ab 18:30 Uhr"');
        $c->setDate(2019, 'group', 'Moni & Erwin', 'Sandra & Matthias', '2019-07-23', '19:15', $empty, 'chat L 17.07.19 Sandra "Di 23.Juli 19.15h"');
        $c->setDate(2019, 'group', 'Jöggu & Puppe', 'Erika & Christine', '2019-07-10', '18:30', $empty, 'chat L 09.07.19 Puppe "18.30 Uhr okay"');
        $c->setDate(2019, 'group', 'Maja & Paschi', 'Bea & Gysi', '2019-07-15', '19:00', $empty, 'chat L 14.07.19 Bea "19.00 Bahn frei für Maja/Paschi und Roli/Bea"');
        $c->setDate(2019, 'group', 'Jöggu & Puppe', 'Maja & Paschi', '2019-07-17', '19:00', $empty, 'chat L 13.07.19 Puppe "Jöggu Puppe hend am Mittwoch zyt 19.00 Uhr"');
        $c->setResult(2019, 'group', 'Erika & Christine', 'Bea & Gysi', 0, 2, '2019-07-20', '16:00', $empty, 'chat L 20.07.19 Christine "16.00 Uhr … Erika/Christine vs. Bea/Gysi"; Erika 18:18 "Hend wieder verlore... 2:0"');
        $c->setDate(2019, 'group', 'Bea & Gysi', 'Jöggu & Puppe', '2019-08-01', '14:00', $empty, 'chat L 29.07.19 Bea "Auso 1.8. um 14.00 Boccia"');
        $c->setDate(2019, 'group', 'Maja & Paschi', 'Erika & Christine', '2019-08-07', '19:00', $empty, 'chat L 02.08.19 Maja "Mittwoch um 19 Uhr"; 07.08 "Heute 19 Uhr"');

        // Block 5a -- bracket per sheet photo 0631 (QF1+QF2 -> SF1, QF3+QF4 -> SF2); scores from the sheet
        $c->setBracketTeams(2019, 'qf', 1, 'Odi & Stierli', 'Stella & Patrik', 'photo 0631 "1A Stierli/Odi – 2B Stella/Patrik"');
        $c->setBracketTeams(2019, 'qf', 2, 'Andrea & Armin', 'Bea & Gysi', 'photo 0631 "1C Armin/Andrea – 2D Bea/Gisi"');
        $c->setBracketTeams(2019, 'qf', 3, 'Lilo & Roli', 'Frei & Frei', 'photo 0631 "1B Roli/Lilo – 2A Frey/Frey"');
        $c->setBracketTeams(2019, 'qf', 4, 'Jöggu & Puppe', 'Tubä & Wurm', 'photo 0631 "1D Jöggu/Puppe – 2C Wurm/Tube"');
        $c->setResult(2019, 'qf', 'Odi & Stierli', 'Stella & Patrik', 2, 1, '2019-08-12', '18:00', $empty, 'photo 0631 2:1; chat L 12.08.19 Stella "Viertelfinalspiel heute schon um 18.00 Uhr", Odi "Dä stiärli"');
        $c->setResult(2019, 'qf', 'Andrea & Armin', 'Bea & Gysi', 1, 2, '2019-08-15', '11:00', $empty, 'photo 0631 1:2; chat L 13.08.19 Joli "11.00 am Dunnschtig isch bueched"; 15.08 "3. Satz nach 3 std"');
        $c->setResult(2019, 'qf', 'Lilo & Roli', 'Frei & Frei', 2, 0, '2019-08-17', '11:00', $empty, 'photo 0631 2:0; chat L 15.08.19 Peter Frei "Lilo und Roli de bis am Samstig ca 11i"');
        $c->setResult(2019, 'qf', 'Jöggu & Puppe', 'Tubä & Wurm', 2, 0, '2019-08-13', '18:00', $empty, 'photo 0631 2:0; chat L 09.08.19 Puppe "am Dienstag, ca. 18.00 Uhr", Tube/Wurm ok (agreed date)');
        $c->setResult(2019, 'sf', 'Odi & Stierli', 'Bea & Gysi', 2, 0, '2019-08-19', '16:00', $empty, 'photo 0631 2:0; chat L 18.08.19 Odi "Das 1.halbfienale montag 19.8 um 16.00"');

        // Block 5b -- winners known, scores not: pairings + dates only
        $c->setDate(2019, 'sf', 'Lilo & Roli', 'Jöggu & Puppe', '2019-08-24', '14:00', $empty, 'chat L 20.08.19 Puppe "am Samschtig 14.00 Uhr zyt", Lilo 👍; 24.08 Lilo "kei 👸" (J&P won, score unknown)');
        $c->setBracketTeams(2019, 'third', 1, 'Bea & Gysi', 'Lilo & Roli', 'SF losers');
        $c->setDate(2019, 'third', 'Bea & Gysi', 'Lilo & Roli', '2019-09-01', '11:00', $empty, 'chat L 26.08.19 Lilo "De chley Final isch am Sunntig am 11i"; 01.09 "3. isch ä Roli" (Bea & Gysi won, score unknown)');
        $c->setBracketTeams(2019, 'final', 1, 'Odi & Stierli', 'Jöggu & Puppe', 'SF winners');
        $c->setDate(2019, 'final', 'Odi & Stierli', 'Jöggu & Puppe', '2019-09-01', '14:00', $empty, 'chat L 26.08.19 Odi "Am sonntig um 14.00 steigt das grosse finale"; Puppe 👑, "dr zweifachi" 2020 (J&P won, score unknown)');
    },
];
