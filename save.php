<?php
/**
 * WealthTrack — save.php v3
 * Persists app data + credentials.
 *
 * v3 changes:
 *  - Removed the unauthenticated `html` write endpoint (deployment is handled
 *    by the token-protected _deploy.php instead).
 *  - Keeps rolling backups of data.json before each overwrite.
 *  - Optimistic concurrency: a client may send `baseSavedAt` (the _savedAt it
 *    believes the server holds). If the server has moved on, the write is
 *    refused with 409 instead of silently clobbering newer data.
 */

header('Content-Type: application/json');
header('Cache-Control: no-store');

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

/**
 * Keep the last N copies of data.json. Stored outside the web root when
 * possible so backups are never downloadable; otherwise locked down with
 * .htaccess.
 */
function wt_backup($dataFile, $keep = 30) {
    if (!file_exists($dataFile) || filesize($dataFile) < 50) return null;

    $dir = dirname($dataFile) . '/../wt_backups';
    if (!is_dir($dir)) @mkdir($dir, 0700, true);
    if (!is_dir($dir) || !is_writable($dir)) {
        $dir = dirname($dataFile) . '/wt_backups';
        if (!is_dir($dir)) @mkdir($dir, 0700, true);
        if (!is_dir($dir) || !is_writable($dir)) return null;
        $ht = $dir . '/.htaccess';
        if (!file_exists($ht)) {
            @file_put_contents($ht, "Require all denied\n<IfModule !mod_authz_core.c>\nDeny from all\n</IfModule>\n");
        }
    }

    $dest = $dir . '/data-' . date('Ymd-His') . '.json';
    @copy($dataFile, $dest);

    // Rotate: keep only the newest $keep files
    $files = glob($dir . '/data-*.json');
    if ($files && count($files) > $keep) {
        usort($files, function ($a, $b) { return filemtime($a) <=> filemtime($b); });
        foreach (array_slice($files, 0, count($files) - $keep) as $old) @unlink($old);
    }
    return $dest;
}

$backupPath = null;

// ── Save main app data ─────────────────────────────────────────
if (array_key_exists('data', $payload) && $payload['data'] !== null) {

    // Optimistic concurrency check
    if (array_key_exists('baseSavedAt', $payload) && $payload['baseSavedAt'] !== null) {
        $currentSavedAt = 0;
        if (file_exists($dataFile)) {
            $cur = json_decode(file_get_contents($dataFile), true);
            if (is_array($cur) && isset($cur['_savedAt'])) $currentSavedAt = $cur['_savedAt'];
        }
        if ($currentSavedAt > 0 && (float)$currentSavedAt !== (float)$payload['baseSavedAt']) {
            http_response_code(409);
            echo json_encode([
                'error'        => 'conflict',
                'message'      => 'The server copy changed since this page loaded.',
                'serverSavedAt'=> $currentSavedAt,
                'yourBase'     => $payload['baseSavedAt'],
            ]);
            exit;
        }
    }

    $encoded = json_encode($payload['data']);
    if ($encoded === false || strlen($encoded) < 50) {
        http_response_code(500);
        echo json_encode(['error' => 'Refusing to write empty or unencodable data']);
        exit;
    }

    $backupPath = wt_backup($dataFile);

    $result = file_put_contents($dataFile, $encoded, LOCK_EX);
    if ($result === false) {
        http_response_code(500);
        echo json_encode(['error' => 'Failed to write data.json — check file permissions']);
        exit;
    }
}

// ── Save credentials ───────────────────────────────────────────
$credKeys = ['passHash', 'userHash', 'contUserHash', 'contPassHash'];
$hasCredUpdate = false;
foreach ($credKeys as $key) {
    if (array_key_exists($key, $payload)) { $hasCredUpdate = true; break; }
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

echo json_encode(['ok' => true, 'backup' => $backupPath ? basename($backupPath) : null]);
