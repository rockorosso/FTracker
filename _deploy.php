<?php
/**
 * WealthTrack — deploy webhook
 * GitHub Actions POSTs file content here instead of using FTP.
 * POST body (JSON): {"token":"...","file":"WealthTrack.html","content":"<raw file content>"}
 */
header('Content-Type: application/json');

$DEPLOY_TOKEN = 'WT_DEPLOY_96f2a';

$body    = file_get_contents('php://input');
$payload = json_decode($body, true);

if (!is_array($payload) || ($payload['token'] ?? '') !== $DEPLOY_TOKEN) {
    http_response_code(403);
    echo json_encode(['error' => 'Forbidden']);
    exit;
}

$file    = $payload['file'] ?? '';
$content = $payload['content'] ?? null;

$ALLOWED = ['WealthTrack.html', 'save.php', 'index.php', '_deploy.php'];

if (!in_array($file, $ALLOWED, true)) {
    http_response_code(400);
    echo json_encode(['error' => 'File not allowed: ' . $file]);
    exit;
}

if (!is_string($content) || strlen($content) < 10) {
    http_response_code(400);
    echo json_encode(['error' => 'Missing or empty content']);
    exit;
}

$dest   = __DIR__ . '/' . $file;
$result = file_put_contents($dest, $content, LOCK_EX);

if ($result === false) {
    http_response_code(500);
    echo json_encode(['error' => 'Failed to write ' . $file]);
    exit;
}

echo json_encode(['ok' => true, 'file' => $file, 'bytes' => $result]);
