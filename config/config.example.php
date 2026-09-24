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
        // 'dev' shows a banner on every page so a dev copy of the site can never be mistaken
        // for the live one. Leave as 'prod' (or omit entirely) on the real server.
        'env' => 'prod',
    ],
];
