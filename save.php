<?php
/**
 * WealthTrack — save.php
 * Accepts POST requests from the app and persists data + credentials.
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Handle CORS preflight
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

$body    = file_get_contents('php://input');
$payload = json_decode($body, true);

if (!is_array($payload)) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid JSON payload']);
    exit;
}

$dataFile   = __DIR__ . '/data.json';
$configFile = __DIR__ . '/config.json';

// ── Save main app data ─────────────────────────────────────────
if (array_key_exists('data', $payload) && $payload['data'] !== null) {
    $encoded = json_encode($payload['data']);
    if ($encoded === false) {
        http_response_code(500);
        echo json_encode(['error' => 'Failed to encode data']);
        exit;
    }
    file_put_contents($dataFile, $encoded, LOCK_EX);
}

// ── Save credentials (passHash, userHash, contUserHash, contPassHash) ──
$credKeys = ['passHash', 'userHash', 'contUserHash', 'contPassHash'];
$hasCredUpdate = false;
foreach ($credKeys as $key) {
    if (array_key_exists($key, $payload)) {
        $hasCredUpdate = true;
        break;
    }
}

if ($hasCredUpdate) {
    $config = file_exists($configFile)
        ? json_decode(file_get_contents($configFile), true)
        : [];
    if (!is_array($config)) $config = [];

    foreach ($credKeys as $key) {
        if (array_key_exists($key, $payload)) {
            if ($payload[$key] === '' || $payload[$key] === null) {
                unset($config[$key]);
            } else {
                $config[$key] = $payload[$key];
            }
        }
    }
    file_put_contents($configFile, json_encode($config), LOCK_EX);
}

echo json_encode(['ok' => true]);
