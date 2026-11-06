<?php
// Corrections from the independent re-analysis of all chats and photos and the 24 scanned printouts
// (November 2026). Every result found matched the database; these five entries are what it changed.
// Order: after knockout-winners.php, which flagged the 2021 quarter-final and the 2023 semi-final.

return [
    'name' => 'reanalysis-corrections',
    'actor_label' => 'Reanalyse',
    'summary' => 'Corrects two 2023 dates, adds a 2019 date and a 2021 time, and replaces the estimated 2021 QF2 with its real 2:0.',
    'apply' => function (Changeset $c): void {
        $c->setDate(2023, 'sf', 'Lilo & Roli', 'Erika & Debi', '2023-08-30', '17:00', ['sets' => [2, 0], 'played_date' => '2023-08-26', 'game_time' => '13:15:00'], 'chat L 26.08.23 14:10 Roli Imboden "Miär spilid am mittw. 17.00"; photos 30.08.23 show the game');
        $c->setDate(2023, 'third', 'Erika & Debi', 'Tubä & Wurm', '2023-09-03', '10:00', ['sets' => [null, null], 'played_date' => '2023-09-10', 'game_time' => '10:00:00'], 'chat L 30.08.23 20:19 Wurm "erscht am Sunntig um 10.00 Uhr"; 03.09 confirmed by the user 2026-11-06');
        $c->setDate(2019, 'group', 'Andrea & Armin', 'Tubä & Wurm', '2019-07-13', null, ['sets' => [2, 0], 'played_date' => null, 'game_time' => null], 'chat L 13.07.19 16:30 Lilo "Königin 👸 Andrea hed mit meym Armin gwunne"');
        $c->setDate(2021, 'group', 'Jöggu & Puppe', 'Moni & Erwin', '2021-07-03', '15:00', ['sets' => [2, 1], 'played_date' => '2021-07-03', 'game_time' => null], 'chat L 02.07.21 19:11 Moni Flury "Gad ai am 3"; 03.07 15:33 Sandra "1:1 anfang 3 satz"');
        $c->setResult(2021, 'qf', 'Lilo & Roli', 'Andrea & Armin', 2, 0, '2021-08-27', '17:30', ['sets' => [2, 0], 'played_date' => '2021-08-27', 'game_time' => '17:30:00'], 'scan IMG_20261104_0015 (2021 bracket): Lilo 2:0 Armin; real score, clears the estimate flag');
    },
];
