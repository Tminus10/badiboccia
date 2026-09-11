<?php

return [
    'db' => [
        'host' => 'localhost',
        'port' => 3306,
        'name' => 'badiboccia',
        'user' => 'dbuser',
        'pass' => 'dbpass',
        'charset' => 'utf8mb4',
    ],
    'app' => [
        // Leave empty if the app is served at the domain root (e.g. https://boccia.reutener.swiss/).
        // Set to '/badiboccia' if it lives in a subfolder instead.
        'base_path' => '',
        'session_name' => 'badiboccia_session',
        'session_lifetime_days' => 90,
    ],
];
