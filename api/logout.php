<?php
require_once __DIR__ . '/lib/strata.php';

strata_begin(['POST']);

// Start the session
session_start();

// Clear all session variables
$_SESSION = [];

// Destroy the session cookie
if (isset($_COOKIE[session_name()]))
{
    setcookie(session_name(), '', [
        'expires' => time() - 3600,
        'path' => '/',
        'secure' => true,
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
}

// Destroy the session
session_destroy();

echo json_encode(['success' => true]);
