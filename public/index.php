<?php

declare(strict_types=1);

require __DIR__ . '/../src/autoload.php';
require __DIR__ . '/../src/helpers.php';

$cfg = app_config();
session_name($cfg['app']['session_name']);
session_set_cookie_params([
    'lifetime' => 60 * 60 * 24 * (int) $cfg['app']['session_lifetime_days'],
    'path' => '/',
    'samesite' => 'Lax',
]);
session_start();

$router = new Router();

// Public, view-only routes
$router->get('/', ['SeasonController', 'home']);
$router->get('/season/{id}', ['SeasonController', 'show']);
$router->get('/group/{id}', ['GroupController', 'show']);
$router->get('/team/{id}', ['TeamController', 'show']);
$router->get('/bracket', ['BracketController', 'current']);
$router->get('/bracket/{id}', ['BracketController', 'forSeason']);
$router->get('/archive', ['ArchiveController', 'index']);
$router->get('/regeln', ['PageController', 'rules']);

// Login / logout
$router->get('/login', ['AuthController', 'teamLoginForm']);
$router->get('/login/team/{id}', ['AuthController', 'teamPinForm']);
$router->post('/login/team/{id}', ['AuthController', 'teamPinSubmit']);
$router->get('/admin/login', ['AuthController', 'adminLoginForm']);
$router->post('/admin/login', ['AuthController', 'adminLoginSubmit']);
$router->post('/logout', ['AuthController', 'logout']);

// Result entry (team or admin)
$router->post('/game/{id}/result', ['GameController', 'submitResult']);
$router->post('/game/{id}/schedule', ['GameController', 'scheduleDate']);

// Admin area
$router->get('/admin', ['AdminController', 'dashboard']);
$router->get('/admin/teams', ['AdminController', 'teamsIndex']);
$router->post('/admin/teams/create', ['AdminController', 'teamCreateGlobal']);
$router->post('/admin/team/{id}/delete-all', ['AdminController', 'teamDeleteCompletely']);
$router->post('/admin/season', ['AdminController', 'seasonCreate']);
$router->get('/admin/season/{id}', ['AdminController', 'seasonManage']);
$router->post('/admin/season/{id}/activate', ['AdminController', 'seasonActivate']);
$router->post('/admin/season/{id}/deactivate', ['AdminController', 'seasonDeactivate']);
$router->post('/admin/season/{id}/update', ['AdminController', 'seasonUpdate']);
$router->post('/admin/season/{id}/delete', ['AdminController', 'seasonDelete']);
$router->post('/admin/season/{id}/team', ['AdminController', 'teamCreate']);
$router->post('/admin/season/{id}/team/enroll', ['AdminController', 'teamEnroll']);
$router->post('/admin/season/{id}/bracket', ['AdminController', 'bracketAssign']);
$router->get('/admin/season/{id}/audit', ['AdminController', 'auditLog']);
$router->post('/admin/team/{id}', ['AdminController', 'teamUpdate']);
$router->post('/admin/team/{id}/photo', ['AdminController', 'teamPhoto']);
$router->post('/admin/team/{id}/pin', ['AdminController', 'teamPinReset']);
$router->post('/admin/team/{id}/delete', ['AdminController', 'teamDelete']);
$router->post('/admin/group/{id}/fixtures', ['AdminController', 'fixturesGenerate']);
$router->post('/admin/group/{id}/delete', ['AdminController', 'groupDelete']);
$router->post('/admin/admins', ['AdminController', 'adminCreate']);
$router->post('/admin/admins/{id}/delete', ['AdminController', 'adminDelete']);

$router->dispatch($_SERVER['REQUEST_METHOD'], $_SERVER['REQUEST_URI']);
