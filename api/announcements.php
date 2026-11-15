<?php
require_once __DIR__ . '/lib/strata.php';

strata_begin(['GET', 'POST']);

$columns = ['id', 'title', 'content', 'date', 'people'];
$announcements = strata_table('announcements');

if ($_SERVER['REQUEST_METHOD'] === 'GET')
{
    // Previously: SELECT * FROM announcements WHERE id = ?
    if (isset($_GET['id']))
    {
        $id = (string) $_GET['id'];

        foreach ($announcements as $announcement)
        {
            if ((string) $announcement['id'] === $id)
            {
                echo json_encode(['announcements' => [strata_stringify($announcement)]]);
                exit;
            }
        }

        strata_fail(404, 'Announcement not found');
    }

    // Previously: SELECT ... FROM announcements ORDER BY date DESC
    usort($announcements, fn($a, $b) => strcmp($b['date'], $a['date']));

    echo json_encode([
        // Previously: SELECT COUNT(*) as total FROM announcements
        'total' => (string) count($announcements),
        'announcements' => array_map(
            fn($announcement) => strata_stringify(strata_columns($announcement, $columns)),
            $announcements
        ),
    ]);
    exit;
}

// POST used to INSERT into the announcements table. The database is gone and
// Vercel's filesystem is read-only at runtime, so the submission is validated
// and echoed back but not stored. The client keeps it for the session only.
$data = strata_json_body();
strata_require_fields($data, ['title', 'content', 'date', 'people']);

$nextId = 1;

foreach ($announcements as $announcement)
{
    $nextId = max($nextId, (int) $announcement['id'] + 1);
}

http_response_code(201);
echo json_encode([
    'success' => true,
    'id' => (string) $nextId,
    'persisted' => false,
    'message' => 'Announcement accepted. This demo runs on read-only seed data, so it will not be saved.',
    'announcement' => [
        'id' => (string) $nextId,
        'title' => (string) $data['title'],
        'content' => (string) $data['content'],
        'date' => (string) $data['date'],
        'people' => (string) $data['people'],
    ],
]);
