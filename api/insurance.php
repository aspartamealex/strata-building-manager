<?php
require_once __DIR__ . '/lib/strata.php';

strata_begin(['GET']);

$owners = strata_table('owners');

// Previously: SELECT id, name, building, room FROM owners
//             WHERE insured = 0 / levies = 0 ORDER BY building, room
usort($owners, function ($a, $b)
{
    return [$a['building'], $a['room']] <=> [$b['building'], $b['room']];
});

$insuredCount = 0;
$leviesPaidCount = 0;
$uninsuredOwners = [];
$unpaidOwners = [];

foreach ($owners as $owner)
{
    $summary = strata_stringify(strata_columns($owner, ['id', 'name', 'building', 'room']));

    if ((int) $owner['insured'] === 1)
    {
        $insuredCount++;
    }
    else
    {
        $uninsuredOwners[] = $summary;
    }

    if ((int) $owner['levies'] === 1)
    {
        $leviesPaidCount++;
    }
    else
    {
        $unpaidOwners[] = $summary;
    }
}

echo json_encode([
    'total' => (string) count($owners),
    'insured' => (string) $insuredCount,
    'leviesPaid' => (string) $leviesPaidCount,
    'uninsuredOwners' => $uninsuredOwners,
    'unpaidOwners' => $unpaidOwners,
]);
