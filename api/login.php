<?php
require_once __DIR__ . '/lib/strata.php';

// The dashboard polls this endpoint with HEAD to show whether the backend is up.
strata_begin(['POST', 'HEAD']);

if ($_SERVER['REQUEST_METHOD'] === 'HEAD')
{
    http_response_code(200);
    exit;
}

$data = strata_json_body();

if (!isset($data['username']) || !isset($data['password']))
{
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Missing username or password']);
    exit;
}

// Previously: SELECT id, username, pfp, role, age, email, phone FROM users
//             WHERE username = ? AND password = ?
$match = null;

foreach (strata_table('users') as $user)
{
    if ($user['username'] === $data['username'] && hash_equals($user['password'], (string) $data['password']))
    {
        $match = strata_columns($user, ['id', 'username', 'pfp', 'role', 'age', 'email', 'phone']);
        break;
    }
}

if ($match === null)
{
    echo json_encode(['success' => false, 'error' => 'Incorrect username or password']);
    exit;
}

$match = strata_stringify($match);

if ($match['pfp'] && !str_starts_with($match['pfp'], '/'))
{
    $match['pfp'] = '/' . $match['pfp'];
}

// Start session and store user data
session_start();
$_SESSION['user_id'] = $match['id'];
$_SESSION['username'] = $match['username'];
$_SESSION['role'] = $match['role'];

// Set session cookie
setcookie('PHPSESSID', session_id(), [
    'expires' => time() + 86400, // 24 hours
    'path' => '/',
    'secure' => true,
    'httponly' => true,
    'samesite' => 'Lax',
]);

echo json_encode(['success' => true, 'user' => $match]);
