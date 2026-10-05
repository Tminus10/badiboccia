<?php
// Merges the duplicate team "Pädi & Kusi" into "Kusi W. & Patrik" and renames it "Kusi W. & Pädi"
// (Kusi Widmer / Pädi = Patrick Felber) -- approved 2026-10-05 after the user confirmed both records
// are the same pair. The survivor keeps its PIN and color (it is the record used in 2026).
// Order on PROD: apply AFTER whatsapp-history-2022 ... -2026, which still refer to both old names.

return [
    'name' => 'merge-team-kusi-w-paedi',
    'actor_label' => 'Team-Merge',
    'summary' => 'Merges duplicate team "Pädi & Kusi" (2022, 2023, 2025) into "Kusi W. & Patrik" (2024, 2026), renamed "Kusi W. & Pädi".',
    'apply' => function (Changeset $c): void {
        $c->mergeTeam('Pädi & Kusi', 'Kusi W. & Patrik', 'Kusi W. & Pädi', 'Kusi Widmer', 'Pädi',
            'user confirmed same pair 2026-10-05; chat L calls them "Kusi & Pädi" 2022-2026 (Patrick Felber + Kusi)');
    },
];
