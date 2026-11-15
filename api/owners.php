<?php
require_once __DIR__ . '/lib/strata.php';

strata_begin(['GET']);

$columns = ['id', 'name', 'building', 'room', 'email', 'phone', 'levies', 'insured'];
$owners = strata_table('owners');

// Previously: SELECT ... FROM owners WHERE id = ?
if (isset($_GET['id']))
{
    $id = (string) $_GET['id'];

    foreach ($owners as $owner)
    {
        if ((string) $owner['id'] === $id)
        {
            echo json_encode(strata_stringify(strata_columns($owner, $columns)));
            exit;
        }
    }

    strata_fail(404, 'Owner not found');
}

// Previously: SELECT ... FROM owners ORDER BY building, room
usort($owners, function ($a, $b)
{
    return [$a['building'], $a['room']] <=> [$b['building'], $b['room']];
});

echo json_encode([
    // Previously: SELECT COUNT(*) as total FROM owners
    'total' => (string) count($owners),
    'owners' => array_map(
        fn($owner) => strata_stringify(strata_columns($owner, $columns)),
        $owners
    ),
]);
