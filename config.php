<?php
// define('DB_HOST', 'localhost');
// define('DB_NAME', 'webdev_project');
// define('DB_USER', 'root');
// define('DB_PASS', ''); // Blank for XAMPP

// try { -->
//     $pdo = new PDO(
//         "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
//         DB_USER,
//         DB_PASS
//     );
//     $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
// } catch (PDOException $e) {
//     http_response_code(500);
//     echo json_encode(['error' => 'Database connection failed: ' . $e->getMessage()]);
//     exit;
// }
// 
// backend/config.php
define('DB_HOST', 'localhost');
define('DB_NAME', 'webdev_project');
define('DB_USER', 'root');
define('DB_PASS', ''); // blank for XAMPP
?>
