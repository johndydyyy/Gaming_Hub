<?php
require_once 'config.php';

try {
    // Check if first_name column exists
    $stmt = $pdo->query("SHOW COLUMNS FROM users LIKE 'first_name'");
    if ($stmt->rowCount() == 0) {
        // Add first_name column
        $pdo->exec("ALTER TABLE users ADD COLUMN first_name VARCHAR(100) AFTER username");
        echo "Added first_name column to users table<br>";
    }

    // Check if last_name column exists
    $stmt = $pdo->query("SHOW COLUMNS FROM users LIKE 'last_name'");
    if ($stmt->rowCount() == 0) {
        // Add last_name column
        $pdo->exec("ALTER TABLE users ADD COLUMN last_name VARCHAR(100) AFTER first_name");
        echo "Added last_name column to users table<br>";
    }

    // Check if phone column exists
    $stmt = $pdo->query("SHOW COLUMNS FROM users LIKE 'phone'");
    if ($stmt->rowCount() == 0) {
        // Add phone column
        $pdo->exec("ALTER TABLE users ADD COLUMN phone VARCHAR(20) AFTER email");
        echo "Added phone column to users table<br>";
    }

    echo "Database update completed successfully!";

} catch (PDOException $e) {
    echo "Error updating database: " . $e->getMessage();
    error_log("Database update error: " . $e->getMessage());
}
?>

<a href="manage_account.php">Return to Account Settings</a>
