<?php
session_start();
require_once 'config.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$error = '';
$success = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])) {
    try {
        // Sanitize and validate input
        $first_name = trim($_POST['first_name'] ?? '');
        $last_name = trim($_POST['last_name'] ?? '');
        $email = filter_var(trim($_POST['email'] ?? ''), FILTER_SANITIZE_EMAIL);
        $phone = trim($_POST['phone'] ?? '');

        // Basic validation
        if (empty($first_name) || empty($last_name) || empty($email)) {
            throw new Exception("Please fill in all required fields.");
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new Exception("Please enter a valid email address.");
        }

        // Check if email already exists for another user
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
        $stmt->execute([$email, $_SESSION['user_id']]);
        if ($stmt->fetch()) {
            throw new Exception("This email is already registered to another account.");
        }

        // Update user data
        $stmt = $pdo->prepare("UPDATE users SET first_name = ?, last_name = ?, email = ?, phone = ?, updated_at = NOW() WHERE id = ?");
        $result = $stmt->execute([$first_name, $last_name, $email, $phone, $_SESSION['user_id']]);

        if ($result) {
            $success = "Profile updated successfully!";
            // Refresh user data
            $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
            $stmt->execute([$_SESSION['user_id']]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
        } else {
            throw new Exception("Failed to update profile. Please try again.");
        }
    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}

// Handle add payment method
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_payment_method'])) {
    try {
        $card_number = str_replace(' ', '', $_POST['card_number'] ?? '');
        $expiry_date = $_POST['expiry_date'] ?? '';
        $cvv = $_POST['cvv'] ?? '';
        $card_holder_name = trim($_POST['card_holder_name'] ?? '');
        $billing_address = trim($_POST['billing_address'] ?? 'none');
        $is_default = isset($_POST['is_default']) ? 1 : 0;
        
        // Basic validation
        if (empty($card_number) || empty($expiry_date) || empty($cvv) || empty($card_holder_name)) {
            throw new Exception("Please fill in all required payment method fields.");
        }
        
        // If this is set as default, unset other defaults
        if ($is_default) {
            $stmt = $pdo->prepare("UPDATE payment_methods SET is_default = 0 WHERE user_id = ?");
            $stmt->execute([$_SESSION['user_id']]);
        }
        
        // Insert new payment method
        $stmt = $pdo->prepare("
            INSERT INTO payment_methods 
            (user_id, card_number, expiry_date, cvv, card_holder_name, billing_address, is_default) 
            VALUES (?, ?, ?, ?, ?, ?, ?)
        ");
        $result = $stmt->execute([
            $_SESSION['user_id'],
            $card_number,
            $expiry_date,
            $cvv,
            $card_holder_name,
            $billing_address,
            $is_default
        ]);
        
        if ($result) {
            $success = "Payment method added successfully!";
        } else {
            throw new Exception("Failed to add payment method. Please try again.");
        }
    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}

// Fetch user details
try {
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$user) {
        throw new Exception("User not found.");
    }
    
    // Fetch user's payment methods
    $payment_methods = [];
    try {
        $stmt = $pdo->prepare("SELECT * FROM payment_methods WHERE user_id = ? ORDER BY is_default DESC, created_at DESC");
        $stmt->execute([$_SESSION['user_id']]);
        $payment_methods = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Error fetching payment methods: " . $e->getMessage());
        $payment_methods = [];
    }
    
    // Initialize balance with default value of 0
    $balance = 0.00;
    
    // Check if user_balance table exists and has data for the user
    try {
        $stmt = $pdo->prepare("SELECT balance FROM user_balance WHERE user_id = ?");
        $stmt->execute([$_SESSION['user_id']]);
        if ($balance_row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $balance = is_null($balance_row['balance']) ? 0.00 : (float)$balance_row['balance'];
        }
    } catch (PDOException $e) {
        // If table doesn't exist or any other error, just use the default 0.00
        error_log("Error fetching user balance: " . $e->getMessage());
        $balance = 0.00;
    }
} catch(PDOException $e) {
    $error = "Error fetching user data: " . $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Account - Gaming Hub</title>
    <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@400;500;600;700;800;900&family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
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
        .header {
            background-color: rgba(15, 23, 42, 0.8);
            box-shadow: 0 4px 30px rgba(0, 0, 0, 0.5);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }
        .nav-link {
            color: #94a3b8;
            transition: color 0.2s;
        }
        .nav-link:hover {
            color: #ffffff !important;
        }
        .account-card {
            background-color: rgba(30, 41, 59, 0.8);
            border-radius: 0.75rem;
            backdrop-filter: blur(5px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            box-shadow: 0 8px 32px 0 rgba(0, 0, 0, 0.36);
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
            <nav class="hidden md:flex space-x-6">
                <a href="dashboard.php" class="nav-link">Home</a>
                <a href="#" class="nav-link text-blue-400">Manage Account</a>
                <a href="cart.php" class="nav-link flex items-center">
                    <i class="fas fa-shopping-cart mr-1"></i> Cart
                    <span id="cart-count" class="ml-1 bg-blue-500 text-white text-xs rounded-full h-5 w-5 flex items-center justify-center">0</span>
                </a>
                <a href="logout.php" class="text-red-400 hover:text-red-300">Logout</a>
            </nav>
            <button class="md:hidden text-white">
                <i class="fas fa-bars text-2xl"></i>
            </button>
        </div>
    </header>

    <!-- Main Content -->
<div class="container mx-auto px-4 py-12">
    <div class="max-w-6xl mx-auto">
        <div class="mb-8 text-center">
            <h1 class="text-4xl font-bold mb-2">Account Settings</h1>
            <p class="text-gray-400">Manage your account information and preferences</p>
        </div>

        <!-- MAIN GRID (Navigation + Content) -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">

            <!-- LEFT NAVIGATION -->
            <div class="account-card p-6 h-fit sticky top-6">
                <div class="flex flex-col space-y-4">
                    <a href="#profile" class="nav-item flex items-center px-4 py-3 rounded-lg bg-blue-900/30 text-blue-400">
                        <i class="fas fa-user-circle mr-3"></i>
                        <span>Profile</span>
                    </a>
                    <a href="#contact" class="nav-item flex items-center px-4 py-3 rounded-lg hover:bg-gray-800/50 text-gray-300 hover:text-white">
                        <i class="fas fa-address-book mr-3"></i>
                        <span>Contact Info</span>
                    </a>
                    <a href="#payment-methods" class="nav-item flex items-center px-4 py-3 rounded-lg hover:bg-gray-800/50 text-gray-300 hover:text-white">
                        <i class="fas fa-credit-card mr-3"></i>
                        <span>Payment Methods</span>
                    </a>
                    <a href="#balance" class="nav-item flex items-center px-4 py-3 rounded-lg hover:bg-gray-800/50 text-gray-300 hover:text-white">
                        <i class="fas fa-wallet mr-3"></i>
                        <span>Balance & Transactions</span>
                    </a>
                </div>
            </div>

            <!-- RIGHT CONTENT (2 Columns Wide) -->
            <div class="md:col-span-2 space-y-10">

                <!-- PROFILE SECTION -->
                <div id="profile" class="account-card p-8">
                    <h2 class="text-2xl font-bold mb-6 flex items-center">
                        <i class="fas fa-user-circle mr-2 text-blue-400"></i>
                        Profile Information
                    </h2>

                    <?php if (!empty($success)): ?>
                        <div class="bg-green-500/20 border border-green-500 text-green-200 px-4 py-3 rounded-lg mb-6">
                            <i class="fas fa-check-circle mr-2"></i>
                            <?php echo htmlspecialchars($success); ?>
                        </div>
                    <?php endif; ?>

                    <form method="POST" class="space-y-6">
                        <input type="hidden" name="update_profile" value="1">

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-sm font-medium text-gray-300 mb-1">First Name</label>
                                <input type="text" name="first_name"
                                       value="<?php echo htmlspecialchars($user['first_name'] ?? ''); ?>"
                                       class="w-full px-4 py-2 bg-gray-700 border border-gray-600 rounded-md">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-300 mb-1">Last Name</label>
                                <input type="text" name="last_name"
                                       value="<?php echo htmlspecialchars($user['last_name'] ?? ''); ?>"
                                       class="w-full px-4 py-2 bg-gray-700 border border-gray-600 rounded-md">
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-300 mb-1">Email Address</label>
                            <input type="email" name="email"
                                   value="<?php echo htmlspecialchars($user['email'] ?? ''); ?>"
                                   class="w-full px-4 py-2 bg-gray-700 border border-gray-600 rounded-md">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-300 mb-1">Phone Number</label>
                            <input type="text" name="phone"
                                   value="<?php echo htmlspecialchars($user['phone'] ?? ''); ?>"
                                   class="w-full px-4 py-2 bg-gray-700 border border-gray-600 rounded-md">
                        </div>

                        <button type="submit"
                                class="w-full bg-blue-600 hover:bg-blue-700 text-white py-2 rounded-lg">
                            Save Changes
                        </button>
                    </form>
                </div>

                <!-- CONTACT INFO -->
                <div id="contact" class="account-card p-8">
                    <h2 class="text-2xl font-bold mb-6 flex items-center">
                        <i class="fas fa-address-book mr-2 text-blue-400"></i>
                        Contact Information
                    </h2>

                    <div class="space-y-4">
                        <div class="flex items-center">
                            <div class="w-12 h-12 rounded-full bg-blue-900/50 flex items-center justify-center text-blue-400 mr-4">
                                <i class="fas fa-envelope text-xl"></i>
                            </div>
                            <div>
                                <p class="text-gray-400 text-sm">Email</p>
                                <p class="text-white"><?php echo htmlspecialchars($user['email'] ?? 'Not provided'); ?></p>
                            </div>
                        </div>

                        <div class="flex items-center">
                            <div class="w-12 h-12 rounded-full bg-blue-900/50 flex items-center justify-center text-blue-400 mr-4">
                                <i class="fas fa-phone text-xl"></i>
                            </div>
                            <div>
                                <p class="text-gray-400 text-sm">Phone</p>
                                <p class="text-white"><?php echo !empty($user['phone']) ? htmlspecialchars($user['phone']) : 'Not provided'; ?></p>
                            </div>
                        </div>

                        <div class="flex items-center">
                            <div class="w-12 h-12 rounded-full bg-blue-900/50 flex items-center justify-center text-blue-400 mr-4">
                                <i class="fas fa-map-marker-alt text-xl"></i>
                            </div>
                            <div>
                                <p class="text-gray-400 text-sm">Address</p>
                                <p class="text-white">123 Gaming Street<br>Manila, 1000<br>Philippines</p>
                            </div>
                        </div>

                        <button class="text-blue-400 hover:text-blue-300 text-sm font-medium flex items-center">
                            <i class="fas fa-edit mr-2"></i>
                            Update Contact Information
                        </button>
                    </div>
                </div>

                <!-- PAYMENT METHODS -->
                <div id="payment-methods" class="account-card p-8">
                    <h2 class="text-2xl font-bold mb-6 flex items-center">
                        <i class="fas fa-credit-card mr-2 text-blue-400"></i>
                        Payment Methods
                    </h2>

                    <!-- Add New Payment Method -->
                    <form method="POST" class="space-y-6">
                        <input type="hidden" name="add_payment_method" value="1">

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-sm font-medium text-gray-300 mb-1">Card Number</label>
                                <input type="text" name="card_number"
                                       placeholder="1234 5678 9012 3456"
                                       class="w-full px-4 py-2 bg-gray-700 border border-gray-600 rounded-md">
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-300 mb-1">Cardholder Name</label>
                                <input type="text" name="card_holder_name"
                                       placeholder="JOHN DOE"
                                       class="w-full px-4 py-2 bg-gray-700 border border-gray-600 rounded-md">
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-300 mb-1">Expiry Date</label>
                                <input type="text" name="expiry_date"
                                       placeholder="MM/YY"
                                       class="w-full px-4 py-2 bg-gray-700 border border-gray-600 rounded-md">
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-300 mb-1">CVV</label>
                                <input type="text" name="cvv"
                                       placeholder="123"
                                       class="w-full px-4 py-2 bg-gray-700 border border-gray-600 rounded-md">
                            </div>

                            <div class="md:col-span-2">
                                <label class="block text-sm font-medium text-gray-300 mb-1">Billing Address</label>
                                <textarea name="billing_address"
                                          class="w-full px-4 py-2 bg-gray-700 border border-gray-600 rounded-md h-20">none</textarea>
                            </div>

                            <div class="flex items-center">
                                <input type="checkbox" name="is_default" class="h-4 w-4 text-blue-600">
                                <label class="ml-2 text-gray-300 text-sm">Set as default payment method</label>
                            </div>
                        </div>

                        <button type="submit"
                                class="bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-6 rounded-md">
                            Add Payment Method
                        </button>
                    </form>
                </div>

                <!-- BALANCE -->
                <div id="balance" class="account-card p-8">
                    <h2 class="text-2xl font-bold mb-6 flex items-center">
                        <i class="fas fa-wallet mr-2 text-blue-400"></i>
                        Account Balance
                    </h2>

                    <div class="bg-gray-800/50 border border-gray-700 rounded-lg p-6">
                        <div class="flex justify-between items-center">
                            <div>
                                <p class="text-gray-400">Available Balance</p>
                                <p class="text-3xl font-bold text-white">
                                    $<?php echo isset($balance) ? number_format((float)$balance, 2) : '0.00'; ?>
                                </p>
                            </div>
                            <div class="bg-blue-600/20 p-3 rounded-full">
                                <i class="fas fa-dollar-sign text-blue-400 text-2xl"></i>
                            </div>
                        </div>

                        <div class="mt-6">
                            <h3 class="text-lg font-semibold mb-3 text-gray-300">Recent Transactions</h3>

                            <div class="bg-gray-800/50 border border-gray-700 rounded-lg p-6 text-center">
                                <i class="fas fa-exchange-alt text-4xl text-gray-500 mb-3"></i>
                                <p class="text-gray-400">No recent transactions</p>
                            </div>
                        </div>
                    </div>

                </div>

            </div><!-- END RIGHT -->
        </div>
    </div>
</div>


        <script>
            // Highlight active section in navigation
            document.addEventListener('DOMContentLoaded', function() {
                const sections = document.querySelectorAll('div[id]');
                const navItems = document.querySelectorAll('.nav-item');
                
                // Highlight on scroll
                window.addEventListener('scroll', function() {
                    let current = '';
                    
                    sections.forEach(section => {
                        const sectionTop = section.offsetTop - 100;
                        const sectionHeight = section.offsetHeight;
                        
                        if (pageYOffset >= sectionTop && pageYOffset < sectionTop + sectionHeight) {
                            current = section.getAttribute('id');
                        }
                    });
                    
                    navItems.forEach(item => {
                        item.classList.remove('bg-blue-900/30', 'text-blue-400');
                        item.classList.add('hover:bg-gray-800/50', 'text-gray-300', 'hover:text-white');
                        
                        if (item.getAttribute('href') === `#${current}`) {
                            item.classList.remove('hover:bg-gray-800/50', 'text-gray-300', 'hover:text-white');
                            item.classList.add('bg-blue-900/30', 'text-blue-400');
                        }
                    });
                });
                
                // Smooth scroll for navigation links
                document.querySelectorAll('a[href^="#"]').forEach(anchor => {
                    anchor.addEventListener('click', function(e) {
                        e.preventDefault();
                        const targetId = this.getAttribute('href');
                        if (targetId === '#') return;
                        
                        const targetElement = document.querySelector(targetId);
                        if (targetElement) {
                            window.scrollTo({
                                top: targetElement.offsetTop - 20,
                                behavior: 'smooth'
                            });
                            
                            // Update URL without page jump
                            history.pushState(null, null, targetId);
                        }
                    });
                });
                
                // Highlight initial section on page load
                const currentHash = window.location.hash;
                if (currentHash) {
                    const targetElement = document.querySelector(currentHash);
                    if (targetElement) {
                        setTimeout(() => {
                            window.scrollTo({
                                top: targetElement.offsetTop - 20,
                                behavior: 'smooth'
                            });
                        }, 100);
                    }
                }
            });
            
            // Mobile menu toggle
            document.querySelector('.md\:hidden').addEventListener('click', function() {
                const nav = document.querySelector('.md\:flex');
                nav.classList.toggle('hidden');
                nav.classList.toggle('flex');
                nav.classList.toggle('flex-col');
                nav.classList.toggle('absolute');
                nav.classList.toggle('right-0');
                nav.classList.toggle('top-16');
                nav.classList.toggle('bg-gray-800');
                nav.classList.toggle('p-4');
                nav.classList.toggle('rounded-lg');
                nav.classList.toggle('shadow-lg');
                nav.classList.toggle('space-x-0');
                nav.classList.toggle('space-y-4');
            });
            nav.classList.toggle('rounded-lg');
            nav.classList.toggle('shadow-lg');
            nav.classList.toggle('space-x-0');
            nav.classList.toggle('space-y-4');
        });

        // Update cart count on page load
        document.addEventListener('DOMContentLoaded', function() {
            fetch('get_cart_count.php')
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        const cartCount = document.getElementById('cart-count');
                        if (cartCount) {
                            cartCount.textContent = data.count;
                        }
                    }
                });
        });
    </script>
</body>
</html>
