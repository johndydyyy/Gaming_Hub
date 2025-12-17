<?php
require_once 'config.php';

echo "<h2>Users Table Structure</h2>";

try {
    // Get table structure
    $stmt = $pdo->query("DESCRIBE users");
    $columns = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    echo "<h3>Current columns in users table:</h3>";
    echo "<ul>";
    foreach ($columns as $column) {
        echo "<li>$column</li>";
    }
    echo "</ul>";
    
    // Show sample data (first row)
    echo "<h3>Sample user data (first row):</h3>";
    $stmt = $pdo->query("SELECT * FROM users LIMIT 1");
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "<pre>";
    print_r($user);
    echo "</pre>";
    
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>

<a href="manage_account.php">Return to Account Settings</a>
