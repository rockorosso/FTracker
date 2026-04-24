<?php
session_start();
require_once __DIR__ . '/config.php';

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') { http_response_code(200); exit; }

// ── DB connection ──────────────────────────────────────────────
try {
    $pdo = new PDO("mysql:host=".DB_HOST.";dbname=".DB_NAME.";charset=utf8mb4", DB_USER, DB_PASS,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC]);
} catch (PDOException $e) {
    http_response_code(500);
    die(json_encode(['error' => 'DB error: ' . $e->getMessage()]));
}

$action = $_GET['action'] ?? '';
$body   = json_decode(file_get_contents('php://input'), true) ?? [];

// ── Route ──────────────────────────────────────────────────────
switch ($action) {
    case 'login':          doLogin($pdo, $body);         break;
    case 'logout':         doLogout();                   break;
    case 'me':             doMe();                       break;
    case 'expenses':       doGetExpenses($pdo);          break;
    case 'add':            doAdd($pdo, $body);           break;
    case 'update':         doUpdate($pdo, $body);        break;
    case 'delete':         doDelete($pdo, $body);        break;
    case 'upload_photo':   doUploadPhoto($pdo);          break;
    case 'extract':        doExtract($body);             break;
    case 'report':         doReport($pdo);               break;
    case 'users':          doUsers($pdo);                break;
    case 'update_user':    doUpdateUser($pdo, $body);     break;
    case 'download_zip':   doDownloadZip($pdo);          break;
    default: http_response_code(400); echo json_encode(['error' => 'Unknown action']);
}

// ── Helpers ────────────────────────────────────────────────────
function requireAuth() {
    if (empty($_SESSION['user_id'])) {
        http_response_code(401);
        echo json_encode(['error' => 'Not authenticated']);
        exit;
    }
}
function isAdmin() { return ($_SESSION['role'] ?? '') === 'admin'; }
function ok($data = []) { echo json_encode(array_merge(['ok' => true], $data)); }
function err($msg, $code = 400) { http_response_code($code); echo json_encode(['error' => $msg]); exit; }

// ── Login ──────────────────────────────────────────────────────
function doLogin($pdo, $body) {
    $username = trim($body['username'] ?? '');
    $password = trim($body['password'] ?? '');
    if (!$username || !$password) err('Username and password required');

    $stmt = $pdo->prepare('SELECT * FROM users WHERE username = ?');
    $stmt->execute([$username]);
    $user = $stmt->fetch();

    if (!$user || !password_verify($password, $user['password_hash'])) {
        err('Invalid username or password', 401);
    }
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['username'] = $user['username'];
    $_SESSION['name']     = $user['name'];
    $_SESSION['role']     = $user['role'];
    ok(['user' => ['id' => $user['id'], 'username' => $user['username'], 'name' => $user['name'], 'role' => $user['role']]]);
}

function doLogout() {
    session_destroy();
    ok();
}

function doMe() {
    if (empty($_SESSION['user_id'])) { echo json_encode(['user' => null]); return; }
    ok(['user' => ['id' => $_SESSION['user_id'], 'username' => $_SESSION['username'], 'name' => $_SESSION['name'], 'role' => $_SESSION['role']]]);
}

// ── Get expenses ───────────────────────────────────────────────
function doGetExpenses($pdo) {
    requireAuth();
    $month   = $_GET['month']   ?? date('Y-m');
    $user_id = $_GET['user_id'] ?? null;

    $sql  = 'SELECT e.*, u.name as user_name FROM expenses e JOIN users u ON e.user_id = u.id WHERE DATE_FORMAT(e.date, "%Y-%m") = ?';
    $params = [$month];

    if (!isAdmin()) {
        $sql .= ' AND e.user_id = ?';
        $params[] = $_SESSION['user_id'];
    } elseif ($user_id) {
        $sql .= ' AND e.user_id = ?';
        $params[] = $user_id;
    }
    $sql .= ' ORDER BY e.date DESC, e.created_at DESC';

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $rows = $stmt->fetchAll();

    // Add photo URL
    foreach ($rows as &$row) {
        $row['photo_url'] = $row['photo_filename'] ? UPLOAD_BASE_URL . $row['photo_filename'] : null;
    }
    ok(['expenses' => $rows]);
}

// ── Add expense ────────────────────────────────────────────────
function doAdd($pdo, $body) {
    requireAuth();
    $date     = $body['date']     ?? date('Y-m-d');
    $company  = $body['company']  ?? '';
    $category = $body['category'] ?? 'other';
    $amount   = floatval($body['amount'] ?? 0);
    $currency = $body['currency'] ?? 'EUR';
    $note     = $body['note']     ?? '';
    $ai       = $body['ai_extracted'] ?? 0;

    if (!$amount) err('Amount is required');

    $stmt = $pdo->prepare('INSERT INTO expenses (user_id, date, company, category, amount, currency, note, ai_extracted) VALUES (?,?,?,?,?,?,?,?)');
    $stmt->execute([$_SESSION['user_id'], $date, $company, $category, $amount, $currency, $note, $ai ? 1 : 0]);
    $id = $pdo->lastInsertId();
    ok(['id' => $id]);
}

// ── Update expense ─────────────────────────────────────────────
function doUpdate($pdo, $body) {
    requireAuth();
    $id = intval($body['id'] ?? 0);
    if (!$id) err('ID required');

    // Verify ownership
    $stmt = $pdo->prepare('SELECT user_id FROM expenses WHERE id = ?');
    $stmt->execute([$id]);
    $row = $stmt->fetch();
    if (!$row || ($row['user_id'] != $_SESSION['user_id'] && !isAdmin())) err('Not allowed', 403);

    $fields = ['date','company','category','amount','currency','note'];
    $sets = []; $params = [];
    foreach ($fields as $f) {
        if (isset($body[$f])) { $sets[] = "$f = ?"; $params[] = $body[$f]; }
    }
    if (empty($sets)) err('Nothing to update');
    $params[] = $id;
    $pdo->prepare('UPDATE expenses SET ' . implode(', ', $sets) . ' WHERE id = ?')->execute($params);
    ok();
}

// ── Delete expense ─────────────────────────────────────────────
function doDelete($pdo, $body) {
    requireAuth();
    $id = intval($body['id'] ?? 0);
    if (!$id) err('ID required');

    $stmt = $pdo->prepare('SELECT user_id, photo_filename FROM expenses WHERE id = ?');
    $stmt->execute([$id]);
    $row = $stmt->fetch();
    if (!$row || ($row['user_id'] != $_SESSION['user_id'] && !isAdmin())) err('Not allowed', 403);

    // Delete photo file if exists
    if ($row['photo_filename']) {
        $path = UPLOAD_DIR . $row['photo_filename'];
        if (file_exists($path)) unlink($path);
    }
    $pdo->prepare('DELETE FROM expenses WHERE id = ?')->execute([$id]);
    ok();
}

// ── Upload photo ───────────────────────────────────────────────
function doUploadPhoto($pdo) {
    requireAuth();
    $id = intval($_POST['expense_id'] ?? 0);
    if (!$id) err('expense_id required');

    // Verify ownership
    $stmt = $pdo->prepare('SELECT user_id, photo_filename FROM expenses WHERE id = ?');
    $stmt->execute([$id]);
    $row = $stmt->fetch();
    if (!$row || ($row['user_id'] != $_SESSION['user_id'] && !isAdmin())) err('Not allowed', 403);

    if (empty($_FILES['photo'])) err('No file uploaded');
    $file = $_FILES['photo'];
    if ($file['error'] !== UPLOAD_ERR_OK) err('Upload error: ' . $file['error']);

    $allowed = ['image/jpeg','image/png','image/webp','image/gif','application/pdf'];
    $mime = mime_content_type($file['tmp_name']);
    if (!in_array($mime, $allowed)) err('Invalid file type (images and PDFs only)');
    if ($file['size'] > 10 * 1024 * 1024) err('File too large (max 10MB)');

    // Delete old photo
    if ($row['photo_filename'] && file_exists(UPLOAD_DIR . $row['photo_filename'])) {
        unlink(UPLOAD_DIR . $row['photo_filename']);
    }

    $ext = pathinfo($file['name'], PATHINFO_EXTENSION) ?: 'jpg';
    $filename = 'exp_' . $id . '_' . time() . '.' . $ext;
    if (!move_uploaded_file($file['tmp_name'], UPLOAD_DIR . $filename)) err('Failed to save file');

    $pdo->prepare('UPDATE expenses SET photo_filename = ? WHERE id = ?')->execute([$filename, $id]);
    ok(['photo_url' => UPLOAD_BASE_URL . $filename, 'filename' => $filename]);
}

// ── AI extraction (proxy to Anthropic) ────────────────────────
function doExtract($body) {
    requireAuth();
    if (!ANTHROPIC_KEY) err('Anthropic API key not configured in config.php');

    $imageData  = $body['image_data']  ?? '';  // base64 string
    $mediaType  = $body['media_type']  ?? 'image/jpeg';
    if (!$imageData) err('image_data required');

    $payload = [
        'model'      => 'claude-haiku-4-5-20251001',
        'max_tokens' => 256,
        'messages'   => [[
            'role'    => 'user',
            'content' => [
                ['type' => 'image', 'source' => ['type' => 'base64', 'media_type' => $mediaType, 'data' => $imageData]],
                ['type' => 'text',  'text'   => 'Extract from this invoice/receipt: date (YYYY-MM-DD), company/store name, total amount (number only, no currency symbol), currency code (EUR/USD/GBP/CHF). Return ONLY valid JSON with keys: date, company, amount, currency. If unsure, use empty string or 0.']
            ]
        ]]
    ];

    $ch = curl_init('https://api.anthropic.com/v1/messages');
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST           => true,
        CURLOPT_POSTFIELDS     => json_encode($payload),
        CURLOPT_HTTPHEADER     => [
            'Content-Type: application/json',
            'x-api-key: ' . ANTHROPIC_KEY,
            'anthropic-version: 2023-06-01'
        ],
        CURLOPT_TIMEOUT => 30
    ]);
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($httpCode !== 200) err('Anthropic API error: ' . $httpCode);

    $data    = json_decode($response, true);
    $text    = $data['content'][0]['text'] ?? '';
    preg_match('/\{[^}]+\}/', $text, $matches);
    $extracted = $matches ? json_decode($matches[0], true) : [];
    ok(['extracted' => $extracted ?: new stdClass()]);
}

// ── Monthly report ─────────────────────────────────────────────
function doReport($pdo) {
    requireAuth();
    $month = $_GET['month'] ?? date('Y-m');

    $sql = 'SELECT e.category, e.currency, e.amount, e.user_id, u.name as user_name
            FROM expenses e JOIN users u ON e.user_id = u.id
            WHERE DATE_FORMAT(e.date, "%Y-%m") = ?';
    $params = [$month];
    if (!isAdmin()) { $sql .= ' AND e.user_id = ?'; $params[] = $_SESSION['user_id']; }

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $rows = $stmt->fetchAll();

    $byCategory = []; $byUser = []; $total = 0;
    foreach ($rows as $r) {
        $amt = floatval($r['amount']);
        $total += $amt;
        $byCategory[$r['category']] = ($byCategory[$r['category']] ?? 0) + $amt;
        $byUser[$r['user_name']]    = ($byUser[$r['user_name']]    ?? 0) + $amt;
    }
    arsort($byCategory); arsort($byUser);
    ok(['total' => $total, 'by_category' => $byCategory, 'by_user' => $byUser, 'count' => count($rows)]);
}

// ── Users list (admin only) ────────────────────────────────────
function doUsers($pdo) {
    requireAuth();
    if (!isAdmin()) err('Admin only', 403);
    $rows = $pdo->query('SELECT id, username, name, role FROM users ORDER BY role, name')->fetchAll();
    ok(['users' => $rows]);
}

// ── Update user (admin only) ───────────────────────────────────
function doUpdateUser($pdo, $body) {
    requireAuth();
    if (!isAdmin()) err('Admin only', 403);

    $id       = intval($body['id'] ?? 0);
    $name     = trim($body['name']     ?? '');
    $username = trim($body['username'] ?? '');
    $password = $body['password'] ?? '';

    if (!$id)       err('ID required');
    if (!$name)     err('Name required');
    if (!$username) err('Username required');

    // Check username not taken by another user
    $stmt = $pdo->prepare('SELECT id FROM users WHERE username = ? AND id != ?');
    $stmt->execute([$username, $id]);
    if ($stmt->fetch()) err('Username already taken by another user');

    if ($password) {
        $hash = password_hash($password, PASSWORD_BCRYPT);
        $pdo->prepare('UPDATE users SET name = ?, username = ?, password_hash = ? WHERE id = ?')
            ->execute([$name, $username, $hash, $id]);
    } else {
        $pdo->prepare('UPDATE users SET name = ?, username = ? WHERE id = ?')
            ->execute([$name, $username, $id]);
    }
    ok();
}

// ── Download ZIP (report + original photos) ───────────────────
function doDownloadZip($pdo) {
    requireAuth();
    $month   = $_GET['month'] ?? date('Y-m');
    $user_id = $_GET['user_id'] ?? null;

    $sql = 'SELECT e.*, u.name as user_name
            FROM expenses e JOIN users u ON e.user_id = u.id
            WHERE DATE_FORMAT(e.date, "%Y-%m") = ?';
    $params = [$month];
    if (!isAdmin()) {
        $sql .= ' AND e.user_id = ?';
        $params[] = $_SESSION['user_id'];
    } elseif ($user_id) {
        $sql .= ' AND e.user_id = ?';
        $params[] = $user_id;
    }
    $sql .= ' ORDER BY e.date DESC, e.created_at DESC';

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $rows = $stmt->fetchAll();

    if (!class_exists('ZipArchive')) {
        http_response_code(500);
        echo json_encode(['error' => 'ZipArchive not available on this server']);
        exit;
    }

    $tmpFile = tempnam(sys_get_temp_dir(), 'expenses_') . '.zip';
    $zip = new ZipArchive();
    if ($zip->open($tmpFile, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
        http_response_code(500);
        echo json_encode(['error' => 'Could not create ZIP file']);
        exit;
    }

    // Category labels
    $catLabels = [
        'transport_work'             => 'Transport Work',
        'transport_entertainment'    => 'Transport Entertainment',
        'accommodation_work'         => 'Accommodation Work',
        'accommodation_entertainment'=> 'Accommodation Entertainment',
        'equipment'                  => 'Equipment & Supplies',
        'software'                   => 'Software & Subscriptions',
        'other'                      => 'Other',
    ];

    // Build CSV
    $csvRows = [];
    $isAdmin = isAdmin();
    $headers = ['Date', 'Company', 'Category', 'Amount', 'Currency', 'Note'];
    if ($isAdmin) $headers[] = 'User';
    $headers[] = 'Attachment';
    $csvRows[] = $headers;

    $total = 0;
    foreach ($rows as $r) {
        $total += floatval($r['amount']);
        $photoName = $r['photo_filename'] ? 'attachments/' . $r['photo_filename'] : '';
        $row = [
            $r['date'],
            $r['company'] ?? '',
            $catLabels[$r['category']] ?? $r['category'],
            number_format(floatval($r['amount']), 2, '.', ''),
            $r['currency'],
            $r['note'] ?? '',
        ];
        if ($isAdmin) $row[] = $r['user_name'];
        $row[] = $photoName;
        $csvRows[] = $row;
    }
    // Totals row
    $totalsRow = array_fill(0, count($headers), '');
    $totalsRow[0] = 'TOTAL';
    $totalsRow[3] = number_format($total, 2, '.', '');
    $csvRows[] = $totalsRow;

    $csv = implode("\n", array_map(function($row) {
        return implode(',', array_map(function($v) {
            return '"' . str_replace('"', '""', $v) . '"';
        }, $row));
    }, $csvRows));

    $zip->addFromString('expenses_' . $month . '.csv', "\xEF\xBB\xBF" . $csv); // UTF-8 BOM for Excel

    // Add attachments at original quality
    $added = [];
    foreach ($rows as $r) {
        if ($r['photo_filename'] && !in_array($r['photo_filename'], $added)) {
            $path = UPLOAD_DIR . $r['photo_filename'];
            if (file_exists($path)) {
                $zip->addFile($path, 'attachments/' . $r['photo_filename']);
                $added[] = $r['photo_filename'];
            }
        }
    }

    $zip->close();

    // Stream ZIP to browser
    $zipName = 'expenses_' . $month . '.zip';
    header('Content-Type: application/zip');
    header('Content-Disposition: attachment; filename="' . $zipName . '"');
    header('Content-Length: ' . filesize($tmpFile));
    header('Cache-Control: no-cache');
    readfile($tmpFile);
    unlink($tmpFile);
    exit;
}
