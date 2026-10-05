<?php
// Renames the merged team "Kusi W. & Pädi" to "Kusi & Pädi" with the players' full names, as the
// user set it on DEV in the admin area after the merge -- approved 2026-10-05.
// Order: after merge-kusi-w-paedi.php. On DEV it is already in this state (counts as done).

return [
    'name' => 'rename-team-kusi-paedi',
    'actor_label' => 'Team-Rename',
    'summary' => 'Renames team "Kusi W. & Pädi" to "Kusi & Pädi" (Markus Widmer / Patrik Felber), matching the DEV edit.',
    'apply' => function (Changeset $c): void {
        $c->renameTeam('Kusi W. & Pädi', 'Kusi & Pädi', 'Markus Widmer', 'Patrik Felber', 'user edit on DEV after the merge, approved for PROD 2026-10-05');
    },
];
