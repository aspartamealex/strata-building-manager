<?php
require_once __DIR__ . '/lib/strata.php';

strata_begin(['GET']);

echo json_encode([
    'name' => 'Strata Building Manager API',
    'runtime' => 'PHP ' . PHP_VERSION . ' on Vercel',
    'storage' => 'read-only JSON seed data (originally MySQL on Railway)',
    'endpoints' => [
        'GET  /api/owners.php'        => 'All owners, or one via ?id=',
        'GET  /api/insurance.php'     => 'Insurance and levy coverage, plus who is outstanding',
        'GET  /api/announcements.php' => 'All announcements, or one via ?id=',
        'POST /api/announcements.php' => 'Validates a new announcement and returns 201 (not persisted)',
        'GET  /api/users.php'         => 'Committee members, or one via ?id=',
        'POST /api/login.php'         => 'Sign in and receive a session cookie',
        'POST /api/logout.php'        => 'Clear the session cookie',
    ],
]);
