<?php
session_start();

// Allow access from frontend
header("Access-Control-Allow-Origin: http://localhost");
header("Access-Control-Allow-Credentials: true");
header("Access-Control-Allow-Methods: GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header('Content-Type: application/json');

// Load DB connection
require_once __DIR__ . '/../db.php';

// ✅ Optional authentication check (uncomment if you want only logged-in users to view blogs)
// if (!isset($_SESSION['user_id'])) {
//     http_response_code(401);
//     echo json_encode(['error' => 'Not authenticated. Please log in.']);
//     exit;
// }

try {
    // Fetch latest 100 blogs along with author email
    $stmt = $pdo->prepare("
        SELECT b.id, b.title, b.content, b.created_at, u.email AS author
        FROM blogs b
        JOIN users u ON b.user_id = u.id
        ORDER BY b.created_at DESC
        LIMIT 100
    ");
    $stmt->execute();
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        'success' => true,
        'blogs' => $rows
    ]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
}
?>
