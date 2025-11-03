<?php
// Debug endpoint to inspect session and request headers locally.
// Usage: visit /backend/auth/debug.php?show=1 in your browser (must be on http://localhost)

header("Access-Control-Allow-Origin: http://localhost");
header("Access-Control-Allow-Credentials: true");
header("Access-Control-Allow-Methods: GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Safety: only show output when explicitly requested
if (!isset($_GET['show']) || $_GET['show'] !== '1') {
    http_response_code(403);
    echo json_encode(['error' => 'Forbidden. Add ?show=1 to view debug info.']);
    exit;
}

session_start();

$headers = function_exists('getallheaders') ? getallheaders() : [];

echo json_encode([
    'time' => date('c'),
    'method' => $_SERVER['REQUEST_METHOD'] ?? '',
    'cookies' => $_COOKIE,
    'headers' => $headers,
    'session_id' => session_id(),
    'session' => $_SESSION,
    'remote_addr' => $_SERVER['REMOTE_ADDR'] ?? null,
]);

?>
