<?php

session_start();

// CORS — allow frontend origin and credentials
header("Access-Control-Allow-Origin: http://localhost");
header("Access-Control-Allow-Credentials: true");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header('Content-Type: application/json');

// Reply to preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Use the correct relative path to the shared DB connection
require_once __DIR__ . '/../db.php';

$input = json_decode(file_get_contents('php://input'), true);
$email = trim($input['email'] ?? '');
$pass = $input['password'] ?? '';

if (!filter_var($email, FILTER_VALIDATE_EMAIL) || $pass === '') {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid credentials.']);
    exit;
}

try {
    $stmt = $pdo->prepare("SELECT id, password FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user || !password_verify($pass, $user['password'])) {
        http_response_code(401);
        echo json_encode(['error' => 'Wrong email or password.']);
        exit;
    }

    $_SESSION['user_id'] = $user['id'];
    echo json_encode(['success' => true, 'user_id' => $user['id']]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Server error: ' . $e->getMessage()]);
}
?>
