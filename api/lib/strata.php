<?php
/**
 * Shared helpers for the Strata Building Manager PHP functions.
 *
 * These endpoints originally queried a MySQL database hosted on Railway. That
 * database has been shut down, so the seed data it held now lives as JSON in
 * api/data/ and is served read-only. The JSON response shape is unchanged, so
 * the frontend still talks to these endpoints exactly as it did before.
 */

define('STRATA_DATA_DIR', dirname(__DIR__) . '/data');

/**
 * Origins allowed to call these endpoints with credentials. In production the
 * frontend and the functions are same-origin, so this only matters when the
 * Next.js dev server on :3000 talks to a separate PHP server.
 */
function strata_allowed_origin(): ?string
{
    $origin = $_SERVER['HTTP_ORIGIN'] ?? '';

    if ($origin === '')
    {
        return null;
    }

    $allowed = [
        'http://localhost:3000',
        'http://127.0.0.1:3000',
        'http://localhost:8000',
    ];

    if (in_array($origin, $allowed, true))
    {
        return $origin;
    }

    // Any Vercel deployment of this project, including preview URLs.
    if (preg_match('#^https://[a-z0-9-]+\.vercel\.app$#i', $origin))
    {
        return $origin;
    }

    return null;
}

/**
 * Emit the JSON and CORS headers, then short-circuit preflight requests.
 */
function strata_begin(array $methods): void
{
    header('Content-Type: application/json');
    header('Cache-Control: no-store');
    header('Vary: Origin');
    header('Access-Control-Allow-Methods: ' . implode(', ', array_merge($methods, ['OPTIONS'])));
    header('Access-Control-Allow-Headers: Content-Type, Accept');

    $origin = strata_allowed_origin();

    if ($origin !== null)
    {
        header('Access-Control-Allow-Origin: ' . $origin);
        header('Access-Control-Allow-Credentials: true');
    }

    if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS')
    {
        http_response_code(204);
        exit;
    }

    if (!in_array($_SERVER['REQUEST_METHOD'], $methods, true))
    {
        header('Allow: ' . implode(', ', array_merge($methods, ['OPTIONS'])));
        strata_fail(405, 'Method not allowed');
    }
}

/**
 * Send an error response and stop.
 */
function strata_fail(int $status, string $message): void
{
    http_response_code($status);
    echo json_encode(['error' => $message]);
    exit;
}

/**
 * Read a seed table from api/data/. Replaces the old mysqli SELECT.
 */
function strata_table(string $name): array
{
    $path = STRATA_DATA_DIR . '/' . $name . '.json';
    $raw = @file_get_contents($path);

    if ($raw === false)
    {
        strata_fail(500, 'Seed data for "' . $name . '" is unavailable');
    }

    $rows = json_decode($raw, true);

    if (!is_array($rows))
    {
        strata_fail(500, 'Seed data for "' . $name . '" is malformed');
    }

    return $rows;
}

/**
 * mysqli returned every column as a string. Keep doing that so the JSON the
 * frontend receives is byte-for-byte what the database used to produce.
 */
function strata_stringify(array $row): array
{
    return array_map(
        fn($value) => $value === null ? null : (string) $value,
        $row
    );
}

/**
 * Keep only the named columns, in the order given. Replaces the column list in
 * the old SELECT statements.
 */
function strata_columns(array $row, array $columns): array
{
    $picked = [];

    foreach ($columns as $column)
    {
        $picked[$column] = $row[$column] ?? null;
    }

    return $picked;
}

/**
 * Decode a JSON request body, failing with 400 if it is missing or invalid.
 */
function strata_json_body(): array
{
    $raw = file_get_contents('php://input');

    if ($raw === false || $raw === '')
    {
        strata_fail(400, 'No data received');
    }

    $data = json_decode($raw, true);

    if (json_last_error() !== JSON_ERROR_NONE)
    {
        strata_fail(400, 'Invalid JSON data: ' . json_last_error_msg());
    }

    if (!is_array($data))
    {
        strata_fail(400, 'Expected a JSON object');
    }

    return $data;
}

/**
 * Validate that every required field is present and non-empty.
 */
function strata_require_fields(array $data, array $fields): void
{
    foreach ($fields as $field)
    {
        if (!isset($data[$field]) || $data[$field] === '')
        {
            strata_fail(400, "Missing required field: $field");
        }
    }
}
