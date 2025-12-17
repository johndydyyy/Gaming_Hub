<?php
session_start();
require_once 'config.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Check if order ID is provided
if (!isset($_POST['order_id'])) {
    $_SESSION['error'] = "Invalid request.";
    header("Location: orders.php");
    exit();
}

$order_id = (int)$_POST['order_id'];
$user_id = $_SESSION['user_id'];

try {
    // Check if order belongs to user and is pending
    $stmt = $pdo->prepare("
        SELECT id FROM orders 
        WHERE id = ? AND user_id = ? AND status = 'pending'
    ");
    $stmt->execute([$order_id, $user_id]);
    $order = $stmt->fetch();

    if (!$order) {
        $_SESSION['error'] = "Order not found or cannot be cancelled.";
        header("Location: orders.php");
        exit();
    }

    // Update order status to cancelled
    $stmt = $pdo->prepare("UPDATE orders SET status = 'cancelled' WHERE id = ?");
    $stmt->execute([$order_id]);

    $_SESSION['success'] = "Order #$order_id has been cancelled successfully.";
    
} catch (PDOException $e) {
    error_log("Error cancelling order: " . $e->getMessage());
    $_SESSION['error'] = "An error occurred while cancelling the order. Please try again.";
}

header("Location: orders.php");
exit();
