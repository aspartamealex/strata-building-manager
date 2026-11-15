<?php
require_once __DIR__ . '/lib/strata.php';

// This endpoint used to support POST, PUT and DELETE against the users table.
// With the database retired the seed data is read-only, so only GET remains.
strata_begin(['GET']);

// Passwords are deliberately never returned.
$columns = ['id', 'username', 'pfp', 'role', 'age', 'email', 'phone'];
$users = strata_table('users');

// Previously: SELECT * FROM users WHERE id = ?
if (isset($_GET['id']))
{
    $id = (string) $_GET['id'];

    foreach ($users as $user)
    {
        if ((string) $user['id'] === $id)
        {
            echo json_encode(strata_stringify(strata_columns($user, $columns)));
            exit;
        }
    }

    strata_fail(404, 'User not found');
}

// Previously: SELECT * FROM users ORDER BY role, username
usort($users, function ($a, $b)
{
    return [$a['role'], $a['username']] <=> [$b['role'], $b['username']];
});

echo json_encode(array_map(
    fn($user) => strata_stringify(strata_columns($user, $columns)),
    $users
));
