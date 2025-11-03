<?php
session_start();

// Allow frontend access
header("Access-Control-Allow-Origin: http://localhost");
header("Access-Control-Allow-Credentials: true");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header('Content-Type: application/json');

// Load database connection
require_once __DIR__ . '/../db.php';

// --- 1️⃣ Check authentication ---
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Not authenticated. Please log in.']);
    exit;
}

$user_id = $_SESSION['user_id'];

// --- 2️⃣ Read and validate input ---
$input = json_decode(file_get_contents('php://input'), true);
$title = trim($input['title'] ?? '');
$content = trim($input['content'] ?? '');

if ($title === '' || $content === '') {
    http_response_code(400);
    echo json_encode(['error' => 'Title and content are required.']);
    exit;
}

// --- 3️⃣ Insert blog post ---
try {
    $stmt = $pdo->prepare("INSERT INTO blogs (user_id, title, content) VALUES (?, ?, ?)");
    $stmt->execute([$user_id, $title, $content]);
    echo json_encode([
        'success' => true,
        'id' => $pdo->lastInsertId()
    ]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
}
?>
