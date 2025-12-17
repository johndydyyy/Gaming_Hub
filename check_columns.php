<?php
require_once 'config.php';

try {
    // Check users table columns
    $stmt = $pdo->query("SHOW COLUMNS FROM users");
    $columns = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    echo "Users table columns: " . implode(", ", $columns) . "\n";
    
    // Show sample user data
    $stmt = $pdo->query("SELECT * FROM users LIMIT 1");
    $user = $stmt->fetch();
    echo "Sample user data: " . print_r($user, true) . "\n";
    
} catch (PDOException $e) {
    die("Error: " . $e->getMessage());
}
