<?php
require_once 'config.php';

try {
    // Test database connection
    echo "<h2>Testing Database Connection</h2>";
    $stmt = $pdo->query("SELECT DATABASE()");
    $db = $stmt->fetchColumn();
    echo "Connected to database: " . htmlspecialchars($db) . "<br><br>";
    
    // Check users table structure
    echo "<h3>Users Table Structure</h3>";
    $stmt = $pdo->query("DESCRIBE users");
    $columns = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    if (empty($columns)) {
        throw new Exception("Users table not found or empty!");
    }
    
    echo "<pre>Users table columns: " . print_r($columns, true) . "</pre>";
    
    // Check if we can fetch user data
    if (isset($_SESSION['user_id'])) {
        echo "<h3>Current User Data</h3>";
        $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
        $stmt->execute([$_SESSION['user_id']]);
        $user = $stmt->fetch();
        
        if ($user) {
            echo "<pre>" . print_r($user, true) . "</pre>";
        } else {
            echo "No user found with ID: " . $_SESSION['user_id'];
        }
    } else {
        echo "<p>Not logged in. <a href='login.php'>Login here</a>.</p>";
    }
    
} catch (Exception $e) {
    echo "<div style='color:red;'><h2>Error:</h2><p>" . htmlspecialchars($e->getMessage()) . "</p></div>";
    
    // Show PDO error info if available
    if (isset($pdo)) {
        echo "<h3>PDO Error Info:</h3><pre>" . print_r($pdo->errorInfo(), true) . "</pre>";
    }
    
    // Show PHP version and extensions
    echo "<h3>PHP Info:</h3>";
    echo "PHP Version: " . phpversion() . "<br>";
    echo "PDO Available: " . (extension_loaded('pdo') ? 'Yes' : 'No') . "<br>";
    echo "PDO MySQL Available: " . (extension_loaded('pdo_mysql') ? 'Yes' : 'No') . "<br>";
}

// Show any PHP errors that occurred
echo "<h3>PHP Errors:</h3>";
print_r(error_get_last());
?>
