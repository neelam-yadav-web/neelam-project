<?php
// Allow frontend access (must match origin your frontend is served from)
header("Access-Control-Allow-Origin: http://localhost");
header("Access-Control-Allow-Credentials: true");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header('Content-Type: application/json');

// Respond to preflight requests immediately
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    // No body for preflight
    http_response_code(200);
    exit;
}

// Debug flag: add ?debug=1 to request URL to enable server-side logs in backend/logs/delete_debug.log
$debug = (isset($_GET['debug']) && $_GET['debug'] === '1');
$logPath = __DIR__ . '/../logs/delete_debug.log';
$log = function($msg) use ($debug, $logPath) {
    if (!$debug) return;
    @file_put_contents($logPath, date('[Y-m-d H:i:s] ') . $msg . PHP_EOL, FILE_APPEND | LOCK_EX);
};

require_once __DIR__ . '/../auth/check.php';
require_once __DIR__ . '/../db.php';
// Accept id from JSON body, POST form or GET query
$input = json_decode(file_get_contents('php://input'), true);
$log('REQUEST_METHOD=' . ($_SERVER['REQUEST_METHOD'] ?? '')); 
$log('RAW_INPUT=' . substr((string)file_get_contents('php://input'),0,1000));
$id = intval($input['id'] ?? ($_POST['id'] ?? ($_GET['id'] ?? 0)));
$log('PARSED_ID=' . $id);
if ($id <= 0) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid id']);
    exit;
}

try {
    $stmt = $pdo->prepare("SELECT user_id FROM blogs WHERE id = ? LIMIT 1");
    $stmt->execute([$id]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    $log('DB_ROW=' . var_export($row, true));
    if (!$row) {
        http_response_code(404);
        echo json_encode(['error' => 'Not found']);
        exit;
    }

    $owner = intval($row['user_id']);
    $currentUser = intval($user_id ?? 0);
    $log('OWNER=' . $owner . ' CURRENTUSER=' . $currentUser . ' SESSION=' . var_export($_SESSION, true));

    // Only the owner (or an admin if you add that later) may delete
    if ($owner !== $currentUser) {
        http_response_code(403);
        echo json_encode(['error' => 'Not allowed']);
        exit;
    }

    $del = $pdo->prepare("DELETE FROM blogs WHERE id = ?");
    $del->execute([$id]);
    $log('DELETE_ROWCOUNT=' . $del->rowCount());
    if ($del->rowCount() === 0) {
        // Nothing deleted for some reason
        http_response_code(500);
        echo json_encode(['error' => 'Delete failed']);
        exit;
    }

    echo json_encode(['success' => true]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Server error']);
    // In development you might include: 'detail' => $e->getMessage()
    exit;
}
?>

<script>
// replace 123 with the id you want to delete
fetch('http://localhost/web-dev-project/backend/blog/delete.php?debug=1', {
  method: 'POST',
  credentials: 'include',
  headers: {'Content-Type': 'application/json'},
  body: JSON.stringify({ id: 123 })
}).then(r => r.json()).then(console.log).catch(console.error);
</script>