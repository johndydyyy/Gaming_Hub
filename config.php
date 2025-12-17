<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Relax CSP for student project to allow potential eval/inline scripts
header("Content-Security-Policy: default-src * 'unsafe-inline' 'unsafe-eval'; script-src * 'unsafe-inline' 'unsafe-eval'; connect-src * 'unsafe-inline'; img-src * data: blob: 'unsafe-inline'; frame-src *; style-src * 'unsafe-inline';");

// Error reporting
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
ini_set('log_errors', 1);
ini_set('error_log', 'C:\\xampp\\php\\logs\\php_error.log');

// Database config
$host = 'localhost';
$dbname = 'gaming_hub';
$username = 'root';
$password = '';

// Set character set
try {
    $options = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
        PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4"
    ];
    
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password, $options);
    
} catch(PDOException $e) {

    error_log("Database connection failed: " . $e->getMessage());
    
    // Display a user-friendly message
    if (strpos($_SERVER['REQUEST_URI'], '/admin/') !== false) {
        die("Database connection error. Please try again later or contact the administrator.");
    } else {
        header('Location: /error.php?code=db_connection');
        exit();
    }
}

// Set timezone
date_default_timezone_set('Asia/Manila');

// Define base URL and other constants
define('BASE_URL', 'http://' . ($_SERVER['HTTP_HOST'] ?? 'localhost') . '/GamingHub');
define('SITE_NAME', 'GamingHub');

// Security function to prevent XSS
function sanitize($data) {
    return htmlspecialchars(trim($data), ENT_QUOTES, 'UTF-8');
}
?>