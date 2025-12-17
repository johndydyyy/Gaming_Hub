<?php
session_start();
require_once 'config.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Check if there's a successful order in session
if (!isset($_SESSION['order_success'])) {
    header("Location: dashboard.php");
    exit();
}

$order = $_SESSION['order_success'];
unset($_SESSION['order_success']); // Clear the success message
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Confirmation - Gaming Hub</title>
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
        .success-card {
            background: rgba(30, 41, 59, 0.8);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.1);
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
                        <span class="absolute -top-2 -right-2 bg-blue-500 text-white text-xs rounded-full h-5 w-5 flex items-center justify-center">
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
    <main class="container mx-auto px-4 py-12">
        <div class="max-w-4xl mx-auto">
            <div class="success-card rounded-xl p-8 text-center">
                <div class="w-20 h-20 bg-green-500 rounded-full flex items-center justify-center mx-auto mb-6">
                    <i class="fas fa-check text-white text-3xl"></i>
                </div>
                <h1 class="text-3xl md:text-4xl font-bold text-white mb-4">Order Confirmed!</h1>
                <p class="text-gray-300 mb-8">Thank you for your purchase. Your order has been received and is being processed.</p>
                
                <div class="bg-gray-800/50 rounded-lg p-6 mb-8 text-left">
                    <h2 class="text-xl font-semibold text-blue-400 mb-4">Order Details</h2>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <p class="text-gray-400">Order Number</p>
                            <p class="text-white font-medium">#<?php echo htmlspecialchars($order['order_id']); ?></p>
                        </div>
                        <div>
                            <p class="text-gray-400">Date</p>
                            <p class="text-white font-medium"><?php echo date('F j, Y'); ?></p>
                        </div>
                        <div>
                            <p class="text-gray-400">Total</p>
                            <p class="text-white font-medium">$<?php echo number_format($order['total'], 2); ?></p>
                        </div>
                        <div>
                            <p class="text-gray-400">Payment Method</p>
                            <p class="text-white font-medium">Credit/Debit Card</p>
                        </div>
                    </div>
                </div>
                
                <div class="flex flex-col sm:flex-row justify-center gap-4">
                    <a href="dashboard.php" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-3 rounded-lg font-medium transition-colors">
                        Continue Shopping
                    </a>
                    <a href="orders.php" class="bg-gray-700 hover:bg-gray-600 text-white px-6 py-3 rounded-lg font-medium transition-colors">
                        View My Orders
                    </a>
                </div>
                
                <p class="mt-8 text-gray-400 text-sm">
                    Need help? <a href="contact.php" class="text-blue-400 hover:underline">Contact our support team</a>
                </p>
            </div>
        </div>
    </main>

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
        document.querySelector('.mobile-menu-button').addEventListener('click', function() {
            const menu = document.querySelector('.mobile-menu');
            menu.classList.toggle('hidden');
        });
    </script>
</body>
</html>
