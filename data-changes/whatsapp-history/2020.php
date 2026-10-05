<?php
// WhatsApp history import, season 2020 -- blocks 1, 2, 3a, 3b approved 2026-10-02.
// See PROPOSALS.md for the evidence. Chat L = Lilo's export. Results already in the DB are not
// touched except #183 (QF score correction); each date change checks the existing result first.

// Expected current state: given result (in the order the teams are named), no date, no time.
$dated = fn (int $a, int $b): array => ['sets' => [$a, $b], 'played_date' => null, 'game_time' => null];

return [
    'name' => 'whatsapp-history-2020',
    'actor_label' => 'WhatsApp-Import 2020',
    'summary' => 'Season 2020: QF score correction (#183 2:1 -> 2:0) and dates for 7 KO and 21 group games, from the WhatsApp chat (Lilo export).',
    'apply' => function (Changeset $c) use ($dated): void {
        // Block 1 -- QF score correction (date/time stay as they are)
        $c->setResult(2020, 'qf', 'Jöggu & Puppe', 'Paschi & Patrik', 2, 0, '2020-08-15', '17:00',
            ['sets' => [2, 1], 'played_date' => '2020-08-15', 'game_time' => '17:00:00'],
            'chat L 15.08.20 20:05 Lilo "21:19/21:13" (two sets)');

        // Block 2 -- KO dates
        $c->setDate(2020, 'qf', 'Odi & Stierli', 'Sandra & Werni', '2020-08-09', '11:00', $dated(2, 1), 'chat L 04.08.20 Sandra "sunntig am 11h", Odi "sunntig um 11uhr isch sehr guät"');
        $c->setDate(2020, 'qf', 'Lilo & Roli', 'Tubä & Wurm', '2020-08-09', null, $dated(1, 2), 'chat L 04.08.20 Tube "am sunntig nachem spiel walti werni, spiel gäge tube wurm", Roli "Ok"');
        $c->setDate(2020, 'qf', 'Erika & Debi', 'Bea & Gysi', '2020-08-09', null, $dated(2, 0), 'chat L 04.08.20 Erika "grad nachär spielä", Gysi/Bea ok; 10.08 Lilo semi-finalists');
        $c->setDate(2020, 'sf', 'Erika & Debi', 'Jöggu & Puppe', '2020-08-16', '14:00', $dated(2, 1), 'chat L 15.08.20 Puppe "Sonntag, 14.00 Uhr Halbfinale Erika/Debi- Jöggu/Puppe" (Stella 18.08 "gestern" not followed)');
        $c->setDate(2020, 'sf', 'Odi & Stierli', 'Tubä & Wurm', '2020-08-20', '17:00', $dated(1, 2), 'chat L 12.08.20 Odi "Donnerstag 20.8.2020 … halbfinal … um 17.00"; 20.08 "2:1"');
        $c->setDate(2020, 'third', 'Jöggu & Puppe', 'Odi & Stierli', '2020-08-23', '11:00', $dated(0, 2), 'chat L 21.08.20 Puppe "11.00Uhr kleines Finale"; 23.08 "stierli/walti hend gwunnä"');
        $c->setDate(2020, 'final', 'Erika & Debi', 'Tubä & Wurm', '2020-08-23', null, $dated(2, 1), 'chat L 21.08.20 Puppe "Danach das GROßE Finale"; 23.08 15:16 congratulations');

        // Block 3a -- group dates 21.06 - 11.07
        $c->setDate(2020, 'group', 'Erika & Debi', 'Sandra & Werni', '2020-06-21', '13:30', $dated(2, 0), 'chat L 21.06.20 Debi "Wemmer morn s Turnier eröffnä?", Roli "Dä werni chund ca.13.30"; 14:39 "Das Turnier hat begonnen"');
        $c->setDate(2020, 'group', 'Bea & Gysi', 'Odi & Stierli', '2020-06-27', null, $dated(2, 1), 'chat L 27.06.20 Lilo sets 15:9/5:15/15:13 "rot" = Bea/Gysi (opponent inferred)');
        $c->setDate(2020, 'group', 'Moni & Erwin', 'Odi & Stierli', '2020-06-28', '12:00', $dated(0, 2), 'chat L 24./26.06.20 Odi "sonntig 12.00 moni edwä - stierli odi", Moni 👍');
        $c->setDate(2020, 'group', 'Bruni & Tiz', 'Erika & Debi', '2020-07-04', '13:30', $dated(1, 2), 'chat L 29.06.20 Tiz "13.30 megemer", Debi "Perfekt"');
        $c->setDate(2020, 'group', 'Jöggu & Puppe', 'Tubä & Wurm', '2020-07-05', '14:00', $dated(2, 0), 'chat L 29.06.20 Puppe "Sonntag 14.00Uhr okay"');
        $c->setDate(2020, 'group', 'Bea & Gysi', 'Bruno & Georges', '2020-07-06', '14:00', $dated(2, 0), 'chat L 06.07.20 Bea "so am zwey", Georg "Okay"; 15:04 Lilo "15:14 griän/15:13 griän" = Bea u Gysi');
        $c->setDate(2020, 'group', 'Bruno & Georges', 'Odi & Stierli', '2020-07-07', null, $dated(1, 2), 'chat L 07.07.20 Lilo "15;5//6:15/15:7 für rot" = Odi Stierli (opponent inferred)');
        $c->setDate(2020, 'group', 'Frei & Frei', 'Lilo & Roli', '2020-07-07', '18:30', $dated(0, 2), 'chat L 03.07.20 Peter Frei "Halbi 7ni am Ziistig isch guet"; 08.07 Lilo "2:0"');
        $c->setDate(2020, 'group', 'Erika & Debi', 'Trix & Kusi', '2020-07-11', '13:30', $dated(2, 0), 'chat L 11.07.20 Debi "am haubizwei scho aafa", Kusi "ia Super"');
        $c->setDate(2020, 'group', 'Lilo & Roli', 'Paschi & Patrik', '2020-07-11', null, $dated(2, 0), 'chat L 10.07.20 Lilo "am Samstig nach Trix Kusi und Debi Erika. Paschi Patrick/Lilo Roli"');

        // Block 3b -- group dates 12.07 - 05.08
        $c->setDate(2020, 'group', 'Eveline & CG', 'Tubä & Wurm', '2020-07-12', '11:30', $dated(0, 2), 'chat L 10.07.20 Wurm "am Sunntig, 11.30 Uhr gägä üch abträte"');
        $c->setDate(2020, 'group', 'Eveline & CG', 'Jöggu & Puppe', '2020-07-14', '18:30', $dated(0, 2), 'chat L 10.07.20 Jöggu "Di um 18.30 h gegä🤴🤴"; 14.07 18:57 photo');
        $c->setDate(2020, 'group', 'Andrea & Armin', 'Jöggu & Puppe', '2020-07-17', '18:30', $dated(0, 2), 'chat L 16.07.20 Puppe "Gaht\'s morä … 18.30 Uhr", Armin ok; 17.07 "15-9, 15-9 rot"');
        $c->setDate(2020, 'group', 'Bea & Gysi', 'Moni & Erwin', '2020-07-18', '14:00', $dated(0, 2), 'chat L 13.07.20 Bea "Platz reserviert am Sa 18.7.20. 14.00"');
        $c->setDate(2020, 'group', 'Andrea & Armin', 'Tubä & Wurm', '2020-07-18', null, $dated(1, 2), 'chat L 13.07.20 Wurm "nachem Erwin/Moni Match", Joli "isch reservierd"');
        $c->setDate(2020, 'group', 'Bruni & Tiz', 'Sandra & Werni', '2020-07-19', '14:00', $dated(0, 2), 'chat L 14.07.20 Sandra "bruni/tiz so am 14h", Tiz 👍');
        $c->setDate(2020, 'group', 'Andrea & Armin', 'Eveline & CG', '2020-07-20', '18:30', $dated(2, 0), 'chat L 18.07.20 Evelyne "18.30gi", Armin "Mäntig 18:30 Uhr wunderbar"');
        $c->setDate(2020, 'group', 'Lilo & Roli', 'Zimi & Roman', '2020-07-21', '18:30', $dated(2, 0), 'chat L 19.07.20 Lilo "Zeyschtig 18:30 Zimmi/Roman-Roli/Lilo"');
        $c->setDate(2020, 'group', 'Bruni & Tiz', 'Trix & Kusi', '2020-07-23', '18:45', $dated(2, 0), 'chat L 20.07.20 Tiz "Dunschtig 18.45", Kusi "Donschtig isch guet"');
        $c->setDate(2020, 'group', 'Frei & Frei', 'Zimi & Roman', '2020-08-05', '18:30', $dated(0, 2), 'chat L 31.07.20 Roman "Mi. Abig... 18:30?", Peter Frei "Isch guet"');
    },
];
