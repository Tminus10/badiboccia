<?php

// Local dev config pointing at the throwaway dev/start-db.sh MariaDB instance.
return [
    'db' => [
        'host' => '127.0.0.1',
        'port' => 3907,
        'name' => 'badiboccia',
        'user' => 'badiboccia',
        'pass' => 'badiboccia',
        'charset' => 'utf8mb4',
    ],
    'app' => [
        'base_path' => '',
        'session_name' => 'badiboccia_session',
        'session_lifetime_days' => 90,
        'env' => 'dev',
    ],
];
