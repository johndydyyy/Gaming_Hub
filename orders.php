<?php
session_start();
require_once 'config.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Fetch user's orders with order items
$orders = [];
try {
    $stmt = $pdo->prepare("
        SELECT o.*, 
               GROUP_CONCAT(CONCAT(oi.quantity, 'x ', p.name) SEPARATOR ', ') as items,
               COUNT(oi.id) as item_count,
               SUM(oi.quantity * oi.price) as order_total
        FROM orders o
        JOIN order_items oi ON o.id = oi.order_id
        JOIN products p ON oi.product_id = p.id
        WHERE o.user_id = ?
        GROUP BY o.id
        ORDER BY o.order_date DESC
    ");
    $stmt->execute([$_SESSION['user_id']]);
    $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $error = "Error fetching orders: " . $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Orders - Gaming Hub</title>
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
            transition: all 0.3s ease;
        }
        .order-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
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
        <div class="max-w-6xl mx-auto">
            <div class="flex items-center justify-between mb-8">
                <h1 class="text-3xl font-bold bg-gradient-to-r from-blue-400 to-blue-600 bg-clip-text text-transparent">
                    My Orders
                </h1>
                <a href="dashboard.php" class="text-blue-400 hover:text-blue-300 transition-colors">
                    <i class="fas fa-arrow-left mr-2"></i> Back to Shop
                </a>
            </div>

            <?php if (isset($error)): ?>
                <div class="bg-red-500/20 border-l-4 border-red-500 text-white p-4 mb-6 rounded-r-lg">
                    <p class="text-sm text-red-100"><?php echo htmlspecialchars($error); ?></p>
                </div>
            <?php endif; ?>

            <?php if (empty($orders)): ?>
                <div class="bg-gray-800/50 rounded-lg p-12 text-center">
                    <div class="w-20 h-20 bg-blue-900/30 rounded-full flex items-center justify-center mx-auto mb-6">
                        <i class="fas fa-box-open text-blue-400 text-3xl"></i>
                    </div>
                    <h3 class="text-xl font-semibold mb-2">No Orders Yet</h3>
                    <p class="text-gray-400 mb-6">You haven't placed any orders yet.</p>
                    <a href="dashboard.php" class="inline-block bg-blue-600 hover:bg-blue-700 text-white px-6 py-3 rounded-lg font-medium transition-colors">
                        Start Shopping
                    </a>
                </div>
            <?php else: ?>
                <div class="space-y-6">
                    <?php foreach ($orders as $order): 
                        $statusClass = 'status-pending';
                        if (strtolower($order['status']) === 'completed') {
                            $statusClass = 'status-completed';
                        } elseif (strtolower($order['status']) === 'cancelled') {
                            $statusClass = 'status-cancelled';
                        }
                    ?>
                        <div class="order-card rounded-xl overflow-hidden">
                            <div class="p-6 border-b border-gray-700">
                                <div class="flex flex-col md:flex-row md:items-center md:justify-between">
                                    <div class="mb-4 md:mb-0">
                                        <div class="flex items-center space-x-4">
                                            <h3 class="text-lg font-semibold">Order #<?php echo $order['id']; ?></h3>
                                            <span class="text-xs px-3 py-1 rounded-full <?php echo $statusClass; ?>">
                                                <?php echo ucfirst($order['status']); ?>
                                            </span>
                                        </div>
                                        <p class="text-sm text-gray-400 mt-1">
                                            Placed on <?php echo date('F j, Y', strtotime($order['order_date'])); ?>
                                        </p>
                                    </div>
                                    <div class="text-right">
                                        <p class="text-sm text-gray-400">Total Amount</p>
                                        <p class="text-xl font-bold">₱<?php echo number_format($order['order_total'], 2); ?></p>
                                    </div>
                                </div>
                            </div>
                            <div class="p-6">
                                <h4 class="font-medium mb-3">Order Items (<?php echo $order['item_count']; ?>)</h4>
                                <p class="text-gray-400 text-sm"><?php echo $order['items']; ?></p>
                                
                                <div class="mt-6 pt-6 border-t border-gray-700 flex flex-col sm:flex-row justify-between items-start sm:items-center space-y-4 sm:space-y-0">
                                    <div>
                                        <p class="text-sm text-gray-400">Order ID: #<?php echo $order['id']; ?></p>
                                    </div>
                                    <div class="space-x-3">
                                        <a href="order_details.php?id=<?php echo $order['id']; ?>" 
                                           class="inline-block bg-gray-700 hover:bg-gray-600 text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors">
                                            View Details
                                        </a>
                                        <?php if (strtolower($order['status']) === 'pending'): ?>
                                            <button class="cancel-order inline-block bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors" 
                                                    data-order-id="<?php echo $order['id']; ?>">
                                                Cancel Order
                                            </button>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
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

        // Handle order cancellation
        document.querySelectorAll('.cancel-order').forEach(button => {
            button.addEventListener('click', function() {
                const orderId = this.getAttribute('data-order-id');
                if (confirm('Are you sure you want to cancel this order?')) {
                    const form = document.createElement('form');
                    form.method = 'POST';
                    form.action = 'cancel_order.php';
                    
                    const input = document.createElement('input');
                    input.type = 'hidden';
                    input.name = 'order_id';
                    input.value = orderId;
                    
                    form.appendChild(input);
                    document.body.appendChild(form);
                    form.submit();
                }
            });
        });
    </script>
</body>
</html>
