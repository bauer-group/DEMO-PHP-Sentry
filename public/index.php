<?php
require __DIR__ . '/../bootstrap.php';

use App\Services\DemoService;

// Wenn kein mode-parameter -> Dashboard anzeigen
if (!isset($_GET['mode'])) {
    // Dashboard
    header('Content-Type: text/html; charset=utf-8');
    include __DIR__ . '/dashboard.html';
    exit;
}

// API Mode
header('Content-Type: application/json');

// Demo Modi:
// ?mode=normal      (default) - Success
// ?mode=error       - Exception
// ?mode=crash       - Fatal Error
// ?mode=warning     - User Warning
// ?mode=deprecated  - Deprecated Warning

$result = DemoService::executeDemo();
echo json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
