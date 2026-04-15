<?php
/**
 * WealthTrack — index.php
 * Serves WealthTrack.html with server-side data and credentials injected.
 */

$dataFile   = __DIR__ . '/data.json';
$configFile = __DIR__ . '/config.json';

// Load persisted data (or null if none yet)
$dataJson = file_exists($dataFile) ? trim(file_get_contents($dataFile)) : 'null';
if (!$dataJson || $dataJson === '') $dataJson = 'null';

// Load credentials config
$config       = file_exists($configFile) ? json_decode(file_get_contents($configFile), true) : [];
$passHash     = isset($config['passHash'])     ? $config['passHash']     : '';
$userHash     = isset($config['userHash'])     ? $config['userHash']     : '';
$contUserHash = isset($config['contUserHash']) ? $config['contUserHash'] : '';
$contPassHash = isset($config['contPassHash']) ? $config['contPassHash'] : '';

// Read HTML template
$html = file_get_contents(__DIR__ . '/WealthTrack.html');

// Inject data
$html = preg_replace(
    '/<!-- __WT_DATA_START__ -->.*?<!-- __WT_DATA_END__ -->/s',
    '<!-- __WT_DATA_START__ --><script>window.__WT_DATA__=' . $dataJson . ';</script><!-- __WT_DATA_END__ -->',
    $html
);

// Inject password hash
if ($passHash !== '') {
    $html = preg_replace(
        '/<!-- __WT_PASS_START__ -->.*?<!-- __WT_PASS_END__ -->/s',
        '<!-- __WT_PASS_START__ --><script>window.__WT_PASS__="' . htmlspecialchars($passHash, ENT_QUOTES) . '";</script><!-- __WT_PASS_END__ -->',
        $html
    );
}

// Inject username hash
if ($userHash !== '') {
    $html = preg_replace(
        '/<!-- __WT_USER_START__ -->.*?<!-- __WT_USER_END__ -->/s',
        '<!-- __WT_USER_START__ --><script>window.__WT_USER__="' . htmlspecialchars($userHash, ENT_QUOTES) . '";</script><!-- __WT_USER_END__ -->',
        $html
    );
}

// Inject contractor username hash
if ($contUserHash !== '') {
    $html = preg_replace(
        '/<!-- __WT_CONT_USER_START__ -->.*?<!-- __WT_CONT_USER_END__ -->/s',
        '<!-- __WT_CONT_USER_START__ --><script>window.__WT_CONT_USER__="' . htmlspecialchars($contUserHash, ENT_QUOTES) . '";</script><!-- __WT_CONT_USER_END__ -->',
        $html
    );
}

// Inject contractor password hash
if ($contPassHash !== '') {
    $html = preg_replace(
        '/<!-- __WT_CONT_PASS_START__ -->.*?<!-- __WT_CONT_PASS_END__ -->/s',
        '<!-- __WT_CONT_PASS_START__ --><script>window.__WT_CONT_PASS__="' . htmlspecialchars($contPassHash, ENT_QUOTES) . '";</script><!-- __WT_CONT_PASS_END__ -->',
        $html
    );
}

header('Content-Type: text/html; charset=utf-8');
// Prevent caching so each load always gets latest data
header('Cache-Control: no-store, no-cache, must-revalidate');
header('Pragma: no-cache');

echo $html;
