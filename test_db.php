<?php
try {
    $pdo = new PDO("mysql:host=localhost;dbname=webdev_project;charset=utf8mb4", "root", "");
    echo "✅ Database connected successfully!";
} catch(PDOException $e) {
    echo "❌ Connection failed: " . $e->getMessage();
}
?>
