<?php
// WhatsApp history import, season 2018 -- approved 2026-10-02. See PROPOSALS.md for the evidence.
// The chat starts 28.08.2018, so only the finals day (09.09.2018) is covered.

return [
    'name' => 'whatsapp-history-2018',
    'actor_label' => 'WhatsApp-Import 2018',
    'summary' => 'Dates for the 2018 final and 3rd-place game from the WhatsApp chat (Lilo export).',
    'apply' => function (Changeset $c): void {
        $c->setDate(2018, 'final', 'Stella & Patrik', 'Jöggu & Puppe', '2018-09-09', '16:00',
            ['sets' => [1, 2], 'played_date' => null, 'game_time' => null],
            'Chat L 06.09.18 Debi "Finale so ca. ab 16.00 Uhr"; 09.09.18 18:04 "1:1 Puppe/Jöggu:Stella/Pädi", 18:59 Puppe "Sieg"');
        $c->setDate(2018, 'third', 'Tubä & Wurm', 'Debi & Städi', '2018-09-09', '13:00',
            ['sets' => [0, 2], 'played_date' => null, 'game_time' => null],
            'Chat L 08.09.18 Erika "Doch: 13.00 Uhr" (first match of finals day)');
    },
];
