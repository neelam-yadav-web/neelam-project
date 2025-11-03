<?php
// CORS headers — allow frontend (must match exactly)
header("Access-Control-Allow-Origin: http://localhost");
header("Access-Control-Allow-Credentials: true");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header('Content-Type: application/json');

// Handle preflight
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

require_once __DIR__ . '/../auth/check.php';
require_once __DIR__ . '/../db.php';

$input = json_decode(file_get_contents('php://input'), true);
$id = intval($input['id'] ?? 0);
$title = trim($input['title'] ?? '');
$content = trim($input['content'] ?? '');

if ($id <= 0 || $title === '' || $content === '') {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid data']);
    exit;
}

try {
    $stmt = $pdo->prepare("SELECT user_id FROM blogs WHERE id = ? LIMIT 1");
    $stmt->execute([$id]);
    $owner = $stmt->fetchColumn();
    if (!$owner) {
        http_response_code(404);
        echo json_encode(['error' => 'Blog not found']);
        exit;
    }
    if (intval($owner) !== intval($user_id ?? 0)) {
        http_response_code(403);
        echo json_encode(['error' => 'Not allowed']);
        exit;
    }
    $stmt = $pdo->prepare("UPDATE blogs SET title = ?, content = ? WHERE id = ?");
    $stmt->execute([$title, $content, $id]);
    echo json_encode(['success' => true]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
}
?>