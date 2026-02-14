<?php
/**
 * One-time script to clear Laravel config and cache on the server.
 * Run once by visiting: https://financials.lumino-ph.com/clear_cache.php
 * Then DELETE this file from the server.
 */

require __DIR__.'/../vendor/autoload.php';

$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$kernel->call('config:clear');
$kernel->call('cache:clear');

echo 'Config and cache cleared. Delete public/clear_cache.php from the server now.';
