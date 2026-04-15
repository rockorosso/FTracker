<?php
/**
 * WealthTrack Expenses — setup.php
 * Run ONCE at: tracker.mydocta.com/expenses/setup.php
 * Then DELETE this file from the server for security.
 */
require_once __DIR__ . '/config.php';

try {
    $pdo = new PDO("mysql:host=".DB_HOST.";dbname=".DB_NAME.";charset=utf8mb4", DB_USER, DB_PASS,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
} catch (PDOException $e) {
    die('<b>DB connection failed:</b> ' . $e->getMessage());
}

// ── Create tables ──────────────────────────────────────────────
$pdo->exec("
CREATE TABLE IF NOT EXISTS users (
  id            INT AUTO_INCREMENT PRIMARY KEY,
  username      VARCHAR(100) NOT NULL UNIQUE,
  name          VARCHAR(100) NOT NULL,
  password_hash VARCHAR(255) NOT NULL,
  role          ENUM('admin','contractor') NOT NULL DEFAULT 'contractor',
  created_at    TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
");

$pdo->exec("
CREATE TABLE IF NOT EXISTS expenses (
  id             INT AUTO_INCREMENT PRIMARY KEY,
  user_id        INT NOT NULL,
  date           DATE NOT NULL,
  company        VARCHAR(200) DEFAULT '',
  category       VARCHAR(50)  NOT NULL DEFAULT 'other',
  amount         DECIMAL(10,2) NOT NULL DEFAULT 0,
  currency       CHAR(3)      NOT NULL DEFAULT 'EUR',
  note           TEXT,
  photo_filename VARCHAR(255) DEFAULT NULL,
  ai_extracted   TINYINT(1)   DEFAULT 0,
  created_at     TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at     TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
");

// ── Seed users ─────────────────────────────────────────────────
$users = [
    ['rockorosso', 'Pablo',   'password123', 'admin'],
    ['gabriel',    'Gabriel', 'gabriel123',  'contractor'],
];

$stmt = $pdo->prepare('INSERT IGNORE INTO users (username, name, password_hash, role) VALUES (?,?,?,?)');
foreach ($users as [$username, $name, $password, $role]) {
    $hash = password_hash($password, PASSWORD_BCRYPT);
    $stmt->execute([$username, $name, $hash, $role]);
    echo "✅ User <b>$username</b> ($role) created<br>";
}

// ── Create uploads directory ───────────────────────────────────
$uploadDir = __DIR__ . '/uploads/';
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0755, true);
    echo "✅ Uploads directory created<br>";
} else {
    echo "ℹ️ Uploads directory already exists<br>";
}

echo "<br><b style='color:green'>✅ Setup complete!</b><br>";
echo "<br><b style='color:red'>⚠️ DELETE this file (setup.php) from your server now for security!</b>";
