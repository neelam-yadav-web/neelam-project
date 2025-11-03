<?php
header("Access-Control-Allow-Origin: http://localhost");
header("Access-Control-Allow-Credentials: true");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
session_start();

header('Content-Type: application/json');

// Adjust path based on folder structure
require_once __DIR__ . '/../db.php';

// Get JSON input
$input = json_decode(file_get_contents('php://input'), true);
$email = trim($input['email'] ?? '');
$pass = $input['password'] ?? '';

// Validate email and password
if (!filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($pass) < 6) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid email or password (min 6 chars).']);
    exit;
}

try {
    // Check if user already exists
    $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->execute([$email]);
    if ($stmt->fetch()) {
        http_response_code(409);
        echo json_encode(['error' => 'Email already exists.']);
        exit;
    }

    // Insert new user
    $hash = password_hash($pass, PASSWORD_DEFAULT);
    $stmt = $pdo->prepare("INSERT INTO users (email, password) VALUES (?, ?)");
    $stmt->execute([$email, $hash]);

    $_SESSION['user_id'] = $pdo->lastInsertId();
    echo json_encode(['success' => true, 'user_id' => $_SESSION['user_id']]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Server error: ' . $e->getMessage()]);
}
?>
