<?php
session_start();
require_once 'config.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Initialize cart if it doesn't exist
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

// Handle remove from cart
if (isset($_POST['remove_from_cart'])) {
    $productId = $_POST['product_id'];
    if (isset($_SESSION['cart'][$productId])) {
        unset($_SESSION['cart'][$productId]);
    }
    header("Location: cart.php");
    exit();
}

// Handle checkout
if (isset($_POST['checkout'])) {
    if (!empty($_SESSION['cart'])) {
        try {
            // Validate user is logged in
            if (!isset($_SESSION['user_id'])) {
                throw new Exception("User not logged in");
            }
            
            $pdo->beginTransaction();

            // Fetch latest product details and calculate total
            $productIds = array_keys($_SESSION['cart']);
            if (empty($productIds)) {
                 throw new Exception("Cart is empty");
            }
            
            $placeholders = str_repeat('?,', count($productIds) - 1) . '?';
            $stmt = $pdo->prepare("SELECT * FROM products WHERE id IN ($placeholders) FOR UPDATE");
            $stmt->execute($productIds);
            $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            $productsById = [];
            foreach ($products as $p) {
                $productsById[$p['id']] = $p;
            }

            $validatedItems = [];
            $subtotal = 0;
            
            foreach ($_SESSION['cart'] as $productId => $item) {
                if (!isset($productsById[$productId])) {
                    // Product deleted or unavailable
                    throw new Exception("One or more products in your cart are no longer available.");
                }
                
                $product = $productsById[$productId];
                $quantity = isset($item['quantity']) ? (int)$item['quantity'] : 0;
                
                if ($quantity < 1) {
                     continue; // Skip invalid items
                }
                
                $price = $product['price']; // Use DB price
                $itemTotal = $price * $quantity;
                
                $validatedItems[] = [
                    'product_id' => $product['id'],
                    'name' => $product['name'],
                    'price' => $price,
                    'quantity' => $quantity,
                    'subtotal' => $itemTotal,
                    'total' => $itemTotal
                ];
                
                $subtotal += $itemTotal;
            }
            
            if (empty($validatedItems)) {
                throw new Exception("No valid items in cart");
            }
            
            $total = $subtotal; // Add tax/shipping here if needed
            
            if ($total <= 0) {
                throw new Exception("Invalid order total");
            }
            
            // Check user balance
            $stmt = $pdo->prepare("SELECT balance FROM user_balance WHERE user_id = ? FOR UPDATE");
            if (!$stmt->execute([$_SESSION['user_id']])) {
                throw new Exception("Failed to check account balance");
            }
            
            $balance = $stmt->fetch(PDO::FETCH_ASSOC);
            if (!$balance || (float)$balance['balance'] < $total) {
                throw new Exception("Insufficient balance. Your balance: ₱" . number_format($balance['balance'] ?? 0, 2) . ", Required: ₱" . number_format($total, 2));
            }
            
            // Deduct balance
            $stmt = $pdo->prepare("UPDATE user_balance SET balance = balance - ? WHERE user_id = ?");
            if (!$stmt->execute([$total, $_SESSION['user_id']])) {
                throw new Exception("Failed to update account balance");
            }
            
            // Generate Order Number
            $orderNumber = 'ORD-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -5));
            
            // Create order
            $stmt = $pdo->prepare("INSERT INTO orders (user_id, order_number, subtotal, total_amount, status, payment_status, payment_method) VALUES (?, ?, ?, ?, 'completed', 'paid', 'balance')");
            if (!$stmt->execute([$_SESSION['user_id'], $orderNumber, $subtotal, $total])) {
                throw new Exception("Failed to create order: " . implode(" ", $stmt->errorInfo()));
            }
            
            $orderId = $pdo->lastInsertId();
            if (!$orderId) {
                throw new Exception("Failed to get order ID");
            }
            
            // Record the transaction
            $stmt = $pdo->prepare("INSERT INTO transactions (user_id, type, amount, description, status, reference_id) VALUES (?, 'purchase', ?, ?, 'completed', ?)");
            $desc = "Purchase Order #" . $orderNumber;
            if (!$stmt->execute([$_SESSION['user_id'], $total, $desc, $orderNumber])) {
                 throw new Exception("Failed to record transaction");
            }
            
            // Add order items
            $stmt = $pdo->prepare("INSERT INTO order_items (order_id, product_id, product_name, quantity, price, subtotal, total) VALUES (?, ?, ?, ?, ?, ?, ?)");
            foreach ($validatedItems as $item) {
                if (!$stmt->execute([$orderId, $item['product_id'], $item['name'], $item['quantity'], $item['price'], $item['subtotal'], $item['total']])) {
                    throw new Exception("Failed to add order item: " . implode(" ", $stmt->errorInfo()));
                }
            }
            
            $pdo->commit();
            
            // Clear cart
            $_SESSION['cart'] = [];
            
            // Session success
            $_SESSION['order_success'] = [
                'order_id' => $orderId,
                'total' => $total,
                'items' => count($validatedItems)
            ];
            
            header("Location: order_success.php");
            exit();
            
        } catch (Exception $e) {
            if (isset($pdo) && $pdo->inTransaction()) {
                $pdo->rollBack();
            }
            
            error_log("Checkout Error: " . $e->getMessage());
            $_SESSION['checkout_error'] = $e->getMessage();
            header("Location: cart.php");
            exit();
        }
    }
}
// Get cart items with product details
$cartItems = [];
$total = 0;

if (!empty($_SESSION['cart'])) {
    $productIds = array_keys($_SESSION['cart']);
    $placeholders = str_repeat('?,', count($productIds) - 1) . '?';
    $stmt = $pdo->prepare("SELECT * FROM products WHERE id IN ($placeholders)");
    $stmt->execute($productIds);
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($products as $product) {
        $quantity = $_SESSION['cart'][$product['id']]['quantity'];
        $itemTotal = $product['price'] * $quantity;
        $cartItems[] = [
            'id' => $product['id'],
            'name' => $product['name'],
            'price' => $product['price'],
            'quantity' => $quantity,
            'total' => $itemTotal,
            'image_url' => $product['image_url']
        ];
        $total += $itemTotal;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shopping Cart - Gaming Hub</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Orbitron:wght@400;500;600;700;800;900&family=Poppins:wght@300;400;500;600;700&display=swap');
        
        :root {
            --primary: #3b82f6;
            --primary-hover: #2563eb;
            --bg-dark: #0f172a;
            --bg-card: rgba(30, 41, 59, 0.5);
            --text-primary: #f8fafc;
            --text-secondary: #94a3b8;
        }
        
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
        
        .navbar-brand {
            font-family: 'Orbitron', sans-serif;
            font-weight: 800;
            font-size: 1.75rem;
            letter-spacing: 1px;
            background: linear-gradient(90deg, #60a5fa 0%, #3b82f6 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            text-shadow: 0 2px 10px rgba(59, 130, 246, 0.3);
            transition: all 0.3s ease;
        }
        
        .navbar-brand:hover {
            transform: translateY(-2px);
            text-shadow: 0 4px 15px rgba(59, 130, 246, 0.4);
        }
        
        .nav-link {
            font-weight: 500;
            letter-spacing: 0.5px;
            transition: all 0.2s ease;
            position: relative;
            color: #e2e8f0;
        }
        
        .nav-link::after {
            content: '';
            position: absolute;
            width: 0;
            height: 2px;
            bottom: -4px;
            left: 0;
            background: linear-gradient(90deg, #60a5fa 0%, #3b82f6 100%);
            transition: width 0.3s ease;
        }
        
        .nav-link:hover::after {
            width: 100%;
        }
        
        body {
            background: linear-gradient(rgba(15, 23, 42, 0.95), rgba(15, 23, 42, 0.95)), url('images/background.jpg') no-repeat center center fixed;
            background-size: cover;
            color: var(--text-primary);
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
            min-height: 100vh;
        }
        
        .account-card {
            background: var(--bg-card);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.05);
            border-radius: 0.75rem;
            transition: all 0.3s ease;
        }
        
        .account-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
        }
        
        .btn-primary {
            background: linear-gradient(135deg, var(--primary), var(--primary-hover));
            transition: all 0.3s ease;
            font-weight: 500;
        }
        
        .btn-primary:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
        }
        
        .cart-item {
            background: var(--bg-card);
            border: 1px solid rgba(255, 255, 255, 0.05);
            border-radius: 0.75rem;
            transition: all 0.3s ease;
        }
        
        .cart-item:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
        }
        
        .quantity-btn {
            transition: all 0.2s ease;
        }
        
        .quantity-btn:hover {
            background: rgba(255, 255, 255, 0.1);
        }
        
        .sticky-nav {
            position: sticky;
            top: 1.5rem;
        }
    </style>
</head>
<body>
    <!-- Header -->
    <header class="header py-4 px-6">
        <div class="container mx-auto flex justify-between items-center">
            <a class="navbar-brand flex items-center" href="dashboard.php">
                <i class="fas fa-gamepad text-blue-400 me-3 text-2xl"></i>
                <span class="text-transparent bg-clip-text bg-gradient-to-r from-blue-400 to-blue-600">GamingHub</span>
            </a>
            <nav id="main-nav" class="hidden md:flex space-x-6">
                <a href="dashboard.php" class="nav-link">Home</a>
                <a href="manage_account.php" class="nav-link">Manage Account</a>
                <a href="cart.php" class="nav-link flex items-center">
                    <i class="fas fa-shopping-cart mr-1"></i> Cart
                    <?php $cartCount = array_sum(array_column($_SESSION['cart'] ?? [], 'quantity')); ?>
                    <span id="cart-count" class="ml-1 bg-blue-500 text-white text-xs rounded-full h-5 w-5 flex items-center justify-center"><?php echo $cartCount; ?></span>
                </a>
                <a href="logout.php" class="text-red-400 hover:text-red-300">Logout</a>
            </nav>
            <button id="mobile-menu-button" class="md:hidden text-white">
                <i class="fas fa-bars text-2xl"></i>
            </button>
        </div>
    </header>

    <!-- Cart Section -->
    <div class="max-w-7xl mx-auto px-4 pt-28 pb-12">
        <?php if (isset($_SESSION['checkout_error'])): ?>
            <div class="bg-red-500/20 border-l-4 border-red-500 text-white p-4 mb-6 rounded-r-lg">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <i class="fas fa-exclamation-circle text-red-300 text-xl"></i>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm text-red-100">
                            <?php 
                            echo htmlspecialchars($_SESSION['checkout_error']); 
                            unset($_SESSION['checkout_error']);
                            ?>
                        </p>
                    </div>
                </div>
            </div>
        <?php endif; ?>
        
        <div class="flex items-center justify-between mb-8">
            <h1 class="text-3xl font-bold bg-gradient-to-r from-blue-400 to-blue-600 bg-clip-text text-transparent">Your Shopping Cart</h1>
            <a href="dashboard.php" class="text-blue-400 hover:text-blue-300 transition-colors">
                <i class="fas fa-arrow-left mr-2"></i> Continue Shopping
            </a>
        </div>
        
        <?php if (empty($cartItems)): ?>
            <div class="account-card p-12 text-center">
                <div class="w-24 h-24 bg-blue-900/30 rounded-full flex items-center justify-center mx-auto mb-6">
                    <i class="fas fa-shopping-cart text-4xl text-blue-400"></i>
                </div>
                <h2 class="text-2xl font-semibold mb-2">Your cart is empty</h2>
                <p class="text-gray-400 mb-6">Looks like you haven't added any items to your cart yet.</p>
                <a href="dashboard.php" class="btn-primary inline-flex items-center px-6 py-3 rounded-lg text-white">
                    <i class="fas fa-gamepad mr-2"></i> Browse Builds
                </a>
            </div>
        <?php else: ?>
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                <!-- Cart Items -->
                <div class="lg:col-span-2 space-y-4">
                    <?php foreach ($cartItems as $item): ?>
                        <div class="cart-item p-6 flex flex-col md:flex-row items-start md:items-center">
                            <div class="w-24 h-24 bg-gray-800 rounded-lg overflow-hidden flex-shrink-0">
                                <img src="<?php echo htmlspecialchars($item['image_url']); ?>" 
                                     alt="<?php echo htmlspecialchars($item['name']); ?>" 
                                     class="w-full h-full object-cover">
                            </div>
                            <div class="flex-1 px-6 py-2">
                                <h3 class="text-lg font-semibold text-white"><?php echo htmlspecialchars($item['name']); ?></h3>
                                <p class="text-blue-400 text-lg font-bold my-1">₱<?php echo number_format($item['price'], 2); ?></p>
                                <div class="flex items-center mt-3">
                                    <span class="text-gray-400 text-sm mr-3">Quantity:</span>
                                    <div class="flex items-center rounded-lg overflow-hidden border border-gray-700">
                                        <button type="button" 
                                                class="quantity-btn px-3 py-1 text-gray-300 hover:bg-gray-700 h-full" 
                                                onclick="updateQuantity(<?php echo $item['id']; ?>, <?php echo $item['quantity'] - 1; ?>)">
                                            <i class="fas fa-minus text-xs"></i>
                                        </button>
                                        <span class="px-4 py-1 bg-gray-800 text-center w-12"><?php echo $item['quantity']; ?></span>
                                        <button type="button" 
                                                class="quantity-btn px-3 py-1 text-gray-300 hover:bg-gray-700 h-full"
                                                onclick="updateQuantity(<?php echo $item['id']; ?>, <?php echo $item['quantity'] + 1; ?>)">
                                            <i class="fas fa-plus text-xs"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                            <div class="flex flex-col items-end mt-4 md:mt-0">
                                <p class="text-xl font-bold text-white">₱<?php echo number_format($item['total'], 2); ?></p>
                                <form method="POST" class="mt-2">
                                    <input type="hidden" name="product_id" value="<?php echo $item['id']; ?>">
                                    <button type="submit" name="remove_from_cart" class="text-red-400 hover:text-red-300 text-sm transition-colors">
                                        <i class="fas fa-trash-alt mr-1"></i> Remove
                                    </button>
                                </form>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
                
                <!-- Order Summary -->
                <div class="lg:sticky lg:top-24">
                    <div class="account-card p-6">
                        <h2 class="text-xl font-semibold mb-6 flex items-center">
                            <i class="fas fa-receipt mr-2 text-blue-400"></i>
                            Order Summary
                        </h2>
                        
                        <div class="space-y-4 mb-6">
                            <div class="flex justify-between">
                                <span class="text-gray-400">Subtotal (<?php echo count($cartItems); ?> items)</span>
                                <span class="font-medium">₱<?php echo number_format($total, 2); ?></span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-400">Shipping</span>
                                <span class="text-green-400">Free</span>
                            </div>
                            <div class="border-t border-gray-700 my-3"></div>
                            <div class="flex justify-between text-lg font-bold">
                                <span>Total</span>
                                <span class="text-blue-400">₱<?php echo number_format($total, 2); ?></span>
                            </div>
                        </div>
                        
                        <form method="POST">
                            <button type="submit" name="checkout" class="btn-primary w-full py-3 rounded-lg font-medium flex items-center justify-center">
                                <i class="fas fa-credit-card mr-2"></i> Proceed to Checkout
                            </button>
                        </form>
                        
                        <div class="mt-4 flex items-center justify-center text-xs text-gray-500">
                            <i class="fas fa-lock mr-2"></i>
                            <span>Secure checkout with SSL encryption</span>
                        </div>
                        
                        <div class="mt-6 pt-4 border-t border-gray-700">
                            <h3 class="text-sm font-semibold text-gray-400 mb-2">We accept</h3>
                            <div class="flex space-x-3">
                                <div class="w-10 h-6 bg-gray-700 rounded flex items-center justify-center">
                                    <i class="fab fa-cc-visa text-lg"></i>
                                </div>
                                <div class="w-10 h-6 bg-gray-700 rounded flex items-center justify-center">
                                    <i class="fab fa-cc-mastercard text-lg"></i>
                                </div>
                                <div class="w-10 h-6 bg-gray-700 rounded flex items-center justify-center">
                                    <i class="fab fa-cc-paypal text-lg"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <!-- Footer -->
    <footer class="bg-gray-900/80 backdrop-blur-md border-t border-gray-800 mt-16 py-8">
        <div class="max-w-7xl mx-auto px-4">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
                <div>
                    <h3 class="text-lg font-semibold mb-4">Gaming Hub</h3>
                    <p class="text-gray-400 text-sm">Your one-stop shop for the latest and greatest games and gaming accessories.</p>
                </div>
                <div>
                    <h4 class="text-sm font-semibold text-gray-400 uppercase tracking-wider mb-4">Shop</h4>
                    <ul class="space-y-2">
                        <li><a href="#" class="text-gray-300 hover:text-white transition-colors">New Releases</a></li>
                        <li><a href="#" class="text-gray-300 hover:text-white transition-colors">Best Sellers</a></li>
                        <li><a href="#" class="text-gray-300 hover:text-white transition-colors">Pre-orders</a></li>
                        <li><a href="#" class="text-gray-300 hover:text-white transition-colors">Deals</a></li>
                    </ul>
                </div>
                <div>
                    <h4 class="text-sm font-semibold text-gray-400 uppercase tracking-wider mb-4">Support</h4>
                    <ul class="space-y-2">
                        <li><a href="#" class="text-gray-300 hover:text-white transition-colors">Contact Us</a></li>
                        <li><a href="#" class="text-gray-300 hover:text-white transition-colors">FAQs</a></li>
                        <li><a href="#" class="text-gray-300 hover:text-white transition-colors">Shipping</a></li>
                        <li><a href="#" class="text-gray-300 hover:text-white transition-colors">Returns</a></li>
                    </ul>
                </div>
                <div>
                    <h4 class="text-sm font-semibold text-gray-400 uppercase tracking-wider mb-4">Connect</h4>
                    <div class="flex space-x-4">
                        <a href="#" class="text-gray-400 hover:text-white transition-colors"><i class="fab fa-facebook-f"></i></a>
                        <a href="#" class="text-gray-400 hover:text-white transition-colors"><i class="fab fa-twitter"></i></a>
                        <a href="#" class="text-gray-400 hover:text-white transition-colors"><i class="fab fa-instagram"></i></a>
                        <a href="#" class="text-gray-400 hover:text-white transition-colors"><i class="fab fa-discord"></i></a>
                    </div>
                </div>
            </div>
            <div class="border-t border-gray-800 mt-8 pt-8 text-center text-sm text-gray-500">
                <p>&copy; <?php echo date('Y'); ?> Gaming Hub. All rights reserved.</p>
            </div>
        </div>
    </footer>

    <script>
        // Mobile menu toggle
        document.addEventListener('DOMContentLoaded', function() {
            const menuButton = document.getElementById('mobile-menu-button');
            const nav = document.getElementById('main-nav');
            
            if (menuButton && nav) {
                // Initialize mobile menu as hidden on small screens
                if (window.innerWidth < 768) {
                    nav.classList.add('hidden');
                    nav.classList.add('absolute');
                    nav.classList.add('right-0');
                    nav.classList.add('top-16');
                    nav.classList.add('bg-gray-900');
                    nav.classList.add('p-4');
                    nav.classList.add('rounded-lg');
                    nav.classList.add('shadow-lg');
                    nav.classList.add('w-64');
                    nav.classList.add('z-50');
                }

                // Toggle menu on button click
                menuButton.addEventListener('click', function() {
                    nav.classList.toggle('hidden');
                });

                // Handle window resize
                function handleResize() {
                    if (window.innerWidth >= 768) {
                        // Desktop - show menu and remove mobile styles
                        nav.classList.remove('hidden');
                        nav.classList.remove('absolute');
                        nav.classList.remove('right-0');
                        nav.classList.remove('top-16');
                        nav.classList.remove('bg-gray-900');
                        nav.classList.remove('p-4');
                        nav.classList.remove('rounded-lg');
                        nav.classList.remove('shadow-lg');
                        nav.classList.remove('w-64');
                    } else {
                        // Mobile - add mobile styles
                        if (!nav.classList.contains('absolute')) {
                            nav.classList.add('absolute');
                            nav.classList.add('right-0');
                            nav.classList.add('top-16');
                            nav.classList.add('bg-gray-900');
                            nav.classList.add('p-4');
                            nav.classList.add('rounded-lg');
                            nav.classList.add('shadow-lg');
                            nav.classList.add('w-64');
                            nav.classList.add('z-50');
                        }
                    }
                }

                // Add resize listener
                window.addEventListener('resize', handleResize);
            }
        });

        // Update cart quantity
        function updateQuantity(btn, productId, change) {
            const quantityElement = btn.closest('.quantity-selector').querySelector('.quantity');
            let currentQuantity = parseInt(quantityElement.textContent);
            let newQuantity = currentQuantity + change;
            
            if (newQuantity < 1) return;
            
            // Show loading state
            const originalContent = btn.innerHTML;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
            btn.disabled = true;
            
            // Update the quantity display immediately for better UX
            quantityElement.textContent = newQuantity;
            
            // Prepare form data
            const formData = new FormData();
            formData.append('product_id', productId);
            formData.append('quantity', newQuantity);
            formData.append('update_cart', 'true');
            
            // Send the update request
            fetch('update_cart.php', {
                method: 'POST',
                body: formData
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error('Network response was not ok');
                }
                return response.json();
            })
            .then(data => {
                if (data.success) {
                    // Update the cart total if needed
                    if (data.cart_total !== undefined) {
                        const totalElement = document.querySelector('.cart-total');
                        if (totalElement) {
                            totalElement.textContent = data.cart_total;
                        }
                    }
                    // Update the cart count in the header
                    if (data.cart_count !== undefined) {
                        const cartCountElements = document.querySelectorAll('.cart-count');
                        cartCountElements.forEach(element => {
                            element.textContent = data.cart_count;
                        });
                    }
                    
                    // If the quantity is 0, remove the item from the cart UI
                    if (newQuantity === 0) {
                        const cartItem = btn.closest('.cart-item');
                        if (cartItem) {
                            cartItem.remove();
                            
                            // Check if cart is empty and update UI
                            if (document.querySelectorAll('.cart-item').length === 0) {
                                window.location.reload();
                            }
                        }
                    }
                } else {
                    // Revert the quantity on error
                    quantityElement.textContent = currentQuantity;
                    alert(data.message || 'Failed to update cart');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                // Revert the quantity on error
                quantityElement.textContent = currentQuantity;
                alert('An error occurred while updating the cart. Please try again.');
            })
            .finally(() => {
                // Restore button state
                btn.innerHTML = originalContent;
                btn.disabled = false;
            });
        }
    </script>
</body>
</html>
