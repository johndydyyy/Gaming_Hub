<?php
session_start();
require_once 'config.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Check if order ID is provided
if (!isset($_GET['id'])) {
    header("Location: orders.php");
    exit();
}

$order_id = $_GET['id'];
$order = null;
$order_items = [];

try {
    // Get order details
    $stmt = $pdo->prepare("
        SELECT o.*, u.username, u.email 
        FROM orders o
        JOIN users u ON o.user_id = u.id
        WHERE o.id = ? AND o.user_id = ?
    ");
    $stmt->execute([$order_id, $_SESSION['user_id']]);
    $order = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($order) {
        // Get order items
        $stmt = $pdo->prepare("
            SELECT oi.*, p.name, p.image_url
            FROM order_items oi
            JOIN products p ON oi.product_id = p.id
            WHERE oi.order_id = ?
        ");
        $stmt->execute([$order_id]);
        $order_items = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
} catch (PDOException $e) {
    $error = "Error fetching order details: " . $e->getMessage();
}

// Redirect if order not found
if (!$order) {
    $_SESSION['error'] = "Order not found or you don't have permission to view this order.";
    header("Location: orders.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order #<?php echo $order['id']; ?> - Gaming Hub</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@400;500;600;700;800;900&family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            background: url('images/background.jpg') no-repeat center center fixed;
            background-size: cover;
            color: #ffffff;
            font-family: 'Poppins', sans-serif;
            min-height: 100vh;
            position: relative;
        }
        body::before {
            content: '';
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(15, 23, 42, 0.9);
            z-index: -1;
        }
        h1, h2, h3, h4, h5, h6, .font-orbitron {
            font-family: 'Orbitron', sans-serif;
            font-weight: 700;
            letter-spacing: 0.5px;
        }
        .order-card {
            background: rgba(30, 41, 59, 0.8);
            border: 1px solid rgba(255, 255, 255, 0.1);
        }
        .status-pending {
            background-color: rgba(234, 179, 8, 0.2);
            color: #fbbf24;
        }
        .status-completed {
            background-color: rgba(34, 197, 94, 0.2);
            color: #4ade80;
        }
        .status-cancelled {
            background-color: rgba(239, 68, 68, 0.2);
            color: #f87171;
        }
    </style>
</head>
<body>
    <!-- Header -->
    <header class="header py-4 px-6">
        <div class="container mx-auto flex justify-between items-center">
            <a class="navbar-brand flex items-center" href="dashboard.php">
                <span class="text-2xl font-bold text-blue-400">GAMING HUB</span>
            </a>
            <nav class="hidden md:flex space-x-6">
                <a href="dashboard.php" class="text-gray-300 hover:text-white transition-colors">Home</a>
                <a href="products.php" class="text-gray-300 hover:text-white transition-colors">Products</a>
                <a href="about.php" class="text-gray-300 hover:text-white transition-colors">About</a>
                <a href="contact.php" class="text-gray-300 hover:text-white transition-colors">Contact</a>
            </nav>
            <div class="flex items-center space-x-4">
                <a href="cart.php" class="text-white hover:text-blue-400 transition-colors relative">
                    <i class="fas fa-shopping-cart text-xl"></i>
                    <?php if (!empty($_SESSION['cart'])): ?>
                        <span class="absolute -top-2 -right-2 bg-blue-500 text-white text-xs rounded-full h-5 w-5 flex items-center justify-center cart-count">
                            <?php echo array_sum(array_column($_SESSION['cart'], 'quantity')); ?>
                        </span>
                    <?php endif; ?>
                </a>
                <div class="relative group">
                    <button class="flex items-center space-x-1 text-white hover:text-blue-400 transition-colors">
                        <i class="fas fa-user-circle text-xl"></i>
                        <span class="hidden md:inline">My Account</span>
                        <i class="fas fa-chevron-down text-xs"></i>
                    </button>
                    <div class="absolute right-0 mt-2 w-48 bg-gray-800 rounded-md shadow-lg py-1 hidden group-hover:block z-50">
                        <a href="profile.php" class="block px-4 py-2 text-sm text-gray-300 hover:bg-gray-700">Profile</a>
                        <a href="orders.php" class="block px-4 py-2 text-sm text-gray-300 hover:bg-gray-700">My Orders</a>
                        <a href="logout.php" class="block px-4 py-2 text-sm text-red-400 hover:bg-gray-700">Logout</a>
                    </div>
                </div>
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <div class="container mx-auto px-4 py-12">
        <div class="max-w-4xl mx-auto">
            <div class="flex items-center justify-between mb-8">
                <div>
                    <a href="orders.php" class="text-blue-400 hover:text-blue-300 transition-colors inline-flex items-center mb-2">
                        <i class="fas fa-arrow-left mr-2"></i> Back to Orders
                    </a>
                    <h1 class="text-3xl font-bold bg-gradient-to-r from-blue-400 to-blue-600 bg-clip-text text-transparent">
                        Order #<?php echo $order['id']; ?>
                    </h1>
                    <p class="text-gray-400 mt-1">
                        Placed on <?php echo date('F j, Y \a\t g:i A', strtotime($order['order_date'])); ?>
                    </p>
                </div>
                <div class="text-right">
                    <?php 
                    $statusClass = 'status-pending';
                    if (strtolower($order['status']) === 'completed') {
                        $statusClass = 'status-completed';
                    } elseif (strtolower($order['status']) === 'cancelled') {
                        $statusClass = 'status-cancelled';
                    }
                    ?>
                    <span class="inline-block px-3 py-1 rounded-full text-sm font-medium <?php echo $statusClass; ?>">
                        <?php echo ucfirst($order['status']); ?>
                    </span>
                </div>
            </div>

            <?php if (isset($error)): ?>
                <div class="bg-red-500/20 border-l-4 border-red-500 text-white p-4 mb-6 rounded-r-lg">
                    <p class="text-sm text-red-100"><?php echo htmlspecialchars($error); ?></p>
                </div>
            <?php endif; ?>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                <!-- Order Items -->
                <div class="lg:col-span-2">
                    <div class="order-card rounded-xl overflow-hidden">
                        <div class="p-6 border-b border-gray-700">
                            <h2 class="text-xl font-semibold mb-6">Order Items</h2>
                            
                            <div class="space-y-6">
                                <?php foreach ($order_items as $item): ?>
                                    <div class="flex items-start space-x-4">
                                        <div class="w-20 h-20 bg-gray-700 rounded-lg overflow-hidden flex-shrink-0">
                                            <img src="<?php echo htmlspecialchars($item['image_url']); ?>" 
                                                 alt="<?php echo htmlspecialchars($item['name']); ?>"
                                                 class="w-full h-full object-cover">
                                        </div>
                                        <div class="flex-1">
                                            <h3 class="font-medium"><?php echo htmlspecialchars($item['name']); ?></h3>
                                            <p class="text-sm text-gray-400">Quantity: <?php echo $item['quantity']; ?></p>
                                        </div>
                                        <div class="text-right">
                                            <p class="font-medium">₱<?php echo number_format($item['price'], 2); ?></p>
                                            <p class="text-sm text-gray-400">₱<?php echo number_format($item['price'] * $item['quantity'], 2); ?> total</p>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                        <div class="p-6 bg-gray-800/50">
                            <div class="flex justify-between items-center">
                                <span class="text-gray-400">Subtotal</span>
                                <span>₱<?php echo number_format($order['total_amount'], 2); ?></span>
                            </div>
                            <div class="flex justify-between items-center mt-2">
                                <span class="text-gray-400">Shipping</span>
                                <span>Free</span>
                            </div>
                            <div class="flex justify-between items-center mt-4 pt-4 border-t border-gray-700">
                                <span class="font-semibold">Total</span>
                                <span class="text-xl font-bold">₱<?php echo number_format($order['total_amount'], 2); ?></span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Order Summary -->
                <div class="space-y-6">
                    <!-- Shipping Address -->
                    <div class="order-card p-6 rounded-xl">
                        <h2 class="text-lg font-semibold mb-4">Shipping Address</h2>
                        <div class="space-y-2 text-gray-300">
                            <p><?php echo htmlspecialchars($order['shipping_name']); ?></p>
                            <p><?php echo htmlspecialchars($order['shipping_street']); ?></p>
                            <p><?php 
                                echo htmlspecialchars($order['shipping_city']) . ', ' . 
                                   htmlspecialchars($order['shipping_state']) . ' ' . 
                                   htmlspecialchars($order['shipping_zip']); 
                            ?></p>
                            <p><?php echo htmlspecialchars($order['shipping_country']); ?></p>
                            <p class="mt-2">
                                <i class="fas fa-phone-alt mr-2"></i>
                                <?php echo htmlspecialchars($order['shipping_phone']); ?>
                            </p>
                        </div>
                    </div>

                    <!-- Payment Method -->
                    <div class="order-card p-6 rounded-xl">
                        <h2 class="text-lg font-semibold mb-4">Payment Method</h2>
                        <div class="flex items-center">
                            <div class="bg-gray-700 p-3 rounded-lg mr-4">
                                <i class="fas fa-credit-card text-2xl text-blue-400"></i>
                            </div>
                            <div>
                                <p class="font-medium">Credit / Debit Card</p>
                                <p class="text-sm text-gray-400">Ending in <?php echo substr($order['payment_card_number'], -4); ?></p>
                            </div>
                        </div>
                    </div>

                    <!-- Order Actions -->
                    <?php if (strtolower($order['status']) === 'pending'): ?>
                        <div class="order-card p-6 rounded-xl">
                            <h2 class="text-lg font-semibold mb-4">Order Actions</h2>
                            <button class="w-full bg-red-600 hover:bg-red-700 text-white py-2 px-4 rounded-lg font-medium transition-colors"
                                    onclick="if(confirm('Are you sure you want to cancel this order?')) { document.getElementById('cancelForm').submit(); }">
                                Cancel Order
                            </button>
                            <form id="cancelForm" action="cancel_order.php" method="POST" class="hidden">
                                <input type="hidden" name="order_id" value="<?php echo $order['id']; ?>">
                            </form>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer class="bg-gray-900/80 backdrop-blur-md border-t border-gray-800 mt-16 py-12">
        <div class="container mx-auto px-4">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
                <!-- Logo and Description -->
                <div>
                    <h3 class="text-2xl font-bold text-blue-400 mb-4">GAMING HUB</h3>
                    <p class="text-gray-400">Your one-stop shop for high-performance gaming PCs and components.</p>
                </div>
                
                <!-- Other Pages -->
                <div>
                    <h4 class="text-lg font-semibold mb-4">Other Pages</h4>
                    <ul class="space-y-2">
                        <li><a href="about.php" class="text-gray-400 hover:text-white">About Us</a></li>
                        <li><a href="terms.php" class="text-gray-400 hover:text-white">Terms of Use</a></li>
                        <li><a href="privacy.php" class="text-gray-400 hover:text-white">Privacy Policy</a></li>
                        <li><a href="cookies.php" class="text-gray-400 hover:text-white">Cookies Policy</a></li>
                    </ul>
                </div>
                
                <!-- Quick Links -->
                <div>
                    <h4 class="text-lg font-semibold mb-4">Quick Links</h4>
                    <ul class="space-y-2">
                        <li><a href="products.php" class="text-gray-400 hover:text-white">All Products</a></li>
                        <li><a href="prebuilt.php" class="text-gray-400 hover:text-white">Pre-built PCs</a></li>
                        <li><a href="components.php" class="text-gray-400 hover:text-white">Components</a></li>
                        <li><a href="contact.php" class="text-gray-400 hover:text-white">Contact Us</a></li>
                    </ul>
                </div>
                
                <!-- Contact Info -->
                <div>
                    <h4 class="text-lg font-semibold mb-4">Our Contacts</h4>
                    <address class="not-italic text-gray-400 space-y-2">
                        <p>123 Gaming Street<br>Tech City, TC 12345</p>
                        <p>Email: info@gaminghub.com</p>
                        <p>Phone: (123) 456-7890</p>
                    </address>
                </div>
            </div>
            
            <div class="border-t border-gray-800 mt-12 pt-8 text-center text-gray-400">
                <p>&copy; <?php echo date('Y'); ?> Gaming Hub. All rights reserved.</p>
            </div>
        </div>
    </footer>

    <script>
        // Mobile menu toggle
        document.querySelector('.mobile-menu-button')?.addEventListener('click', function() {
            const menu = document.querySelector('.mobile-menu');
            if (menu) {
                menu.classList.toggle('hidden');
            }
        });
    </script>
</body>
</html>
