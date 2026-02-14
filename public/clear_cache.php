<?php
/**
 * One-time script to clear Laravel config cache (no DB, no full bootstrap).
 * Run once by visiting: https://financials.lumino-ph.com/clear_cache.php
 * Then DELETE this file from the server.
 */

$base = __DIR__.'/../bootstrap/cache';
$files = ['config.php', 'services.php', 'packages.php', 'events.php'];
$cleared = 0;
foreach ($files as $f) {
    if (file_exists($base.'/'.$f) && @unlink($base.'/'.$f)) {
        $cleared++;
    }
}

echo 'Config cache cleared ('.$cleared.' files). Delete public/clear_cache.php from the server now.<br><br>';
echo 'If the site still shows "Access denied", the DB password in .env on the server is wrong. In CyberPanel: List Databases → Change Password for fina_finance1 → set a new password → put that exact password in .env as DB_PASSWORD="newpassword" (with quotes).';
