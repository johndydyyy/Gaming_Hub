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

// Handle payment method update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_payment'])) {
    try {
        $card_number = str_replace(' ', '', $_POST['card_number'] ?? '');
        $exp_month = $_POST['exp_month'] ?? '';
        $exp_year = $_POST['exp_year'] ?? '';
        $cvc = $_POST['cvc'] ?? '';
        $cardholder_name = trim($_POST['cardholder_name'] ?? '');
        $billing_address = trim($_POST['billing_address'] ?? '');
        
        // Basic validation
        if (empty($card_number) || empty($exp_month) || empty($exp_year) || empty($cvc) || empty($cardholder_name)) {
            throw new Exception("Please fill in all required fields.");
        }
        
        // In a real application, you would process the payment here
        // For this example, we'll just store the last 4 digits
        $last4 = substr($card_number, -4);
        $exp_date = "$exp_month/$exp_year";
        
        // Update user's payment method
        $stmt = $pdo->prepare("UPDATE users SET default_payment_method = ?, billing_address = ? WHERE id = ?");
        $result = $stmt->execute(["Card ending in $last4 ($exp_date)", $billing_address, $_SESSION['user_id']]);
        
        if ($result) {
            $success = "Payment method updated successfully!";
        } else {
            throw new Exception("Failed to update payment method. Please try again.");
        }
    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}

// Fetch user details
$user = [];
try {
    $stmt = $pdo->prepare("SELECT *, COALESCE(billing_address, 'None') as billing_address FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$user) {
        throw new Exception("User not found.");
    }
} catch(PDOException $e) {
    die("Error fetching user data: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Methods - Gaming Hub</title>
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
        .account-card {
            background-color: rgba(30, 41, 59, 0.8);
            border-radius: 0.75rem;
            backdrop-filter: blur(5px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            box-shadow: 0 8px 32px 0 rgba(0, 0, 0, 0.36);
        }
        .nav-link {
            color: #94a3b8;
            transition: color 0.2s;
        }
        .nav-link:hover {
            color: #ffffff !important;
        }
        .payment-card {
            background: linear-gradient(135deg, rgba(30, 41, 59, 0.8) 0%, rgba(15, 23, 42, 0.9) 100%);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 0.75rem;
            padding: 1.5rem;
            margin-bottom: 1.5rem;
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
                <a href="manage_account.php" class="nav-link">Account</a>
                <a href="payment.php" class="nav-link text-blue-400">Payments</a>
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
        <div class="max-w-4xl mx-auto">
            <div class="mb-8 text-center">
                <h1 class="text-4xl font-bold mb-2">Payment Methods</h1>
                <p class="text-gray-400">Manage your payment options and billing information</p>
            </div>

            <?php if (isset($error)): ?>
                <div class="bg-red-500/20 border border-red-500 text-red-200 px-4 py-3 rounded mb-6">
                    <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>

            <?php if (!empty($success)): ?>
                <div class="bg-green-500/20 border border-green-500 text-green-200 px-4 py-3 rounded-lg mb-6">
                    <i class="fas fa-check-circle mr-2"></i>
                    <?php echo htmlspecialchars($success); ?>
                </div>
            <?php endif; ?>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <!-- Navigation -->
                <div>
                    <div class="account-card p-6">
                        <div class="flex flex-col space-y-4">
                            <a href="manage_account.php#profile" class="flex items-center px-4 py-3 rounded-lg hover:bg-gray-800/50 text-gray-300 hover:text-white">
                                <i class="fas fa-user-circle mr-3"></i>
                                <span>Profile</span>
                            </a>
                            <a href="manage_account.php#contact" class="flex items-center px-4 py-3 rounded-lg hover:bg-gray-800/50 text-gray-300 hover:text-white">
                                <i class="fas fa-address-book mr-3"></i>
                                <span>Contact Info</span>
                            </a>
                            <a href="payment.php" class="flex items-center px-4 py-3 rounded-lg bg-blue-900/30 text-blue-400">
                                <i class="fas fa-credit-card mr-3"></i>
                                <span>Payment Methods</span>
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Payment Methods -->
                <div class="md:col-span-2">
                    <!-- Current Payment Method -->
                    <div class="payment-card mb-8">
                        <h2 class="text-xl font-bold mb-4 flex items-center">
                            <i class="fas fa-credit-card mr-2 text-blue-400"></i>
                            Current Payment Method
                        </h2>
                        
                        <?php if (!empty($user['default_payment_method'])): ?>
                            <div class="bg-gray-800/50 p-4 rounded-lg mb-4">
                                <div class="flex justify-between items-center">
                                    <div>
                                        <p class="text-gray-400 text-sm">Payment Method</p>
                                        <p class="text-white"><?php echo htmlspecialchars($user['default_payment_method']); ?></p>
                                    </div>
                                    <div class="text-right">
                                        <p class="text-gray-400 text-sm">Billing Address</p>
                                        <p class="text-white"><?php echo nl2br(htmlspecialchars($user['billing_address'])); ?></p>
                                    </div>
                                </div>
                            </div>
                        <?php else: ?>
                            <div class="bg-gray-800/50 p-4 rounded-lg mb-4 text-center">
                                <p class="text-gray-400">No payment method saved</p>
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- Add/Update Payment Method -->
                    <div class="payment-card">
                        <h2 class="text-xl font-bold mb-4 flex items-center">
                            <i class="fas fa-plus-circle mr-2 text-blue-400"></i>
                            <?php echo !empty($user['default_payment_method']) ? 'Update' : 'Add'; ?> Payment Method
                        </h2>
                        
                        <form method="POST" action="" class="space-y-4">
                            <input type="hidden" name="update_payment" value="1">
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-300 mb-1">Card Number</label>
                                <div class="relative">
                                    <input type="text" name="card_number" 
                                           placeholder="1234 1234 1234 1234"
                                           class="w-full px-4 py-2 bg-gray-700 border border-gray-600 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                           required>
                                    <div class="absolute right-3 top-2.5 flex space-x-2">
                                        <i class="fab fa-cc-visa text-gray-400"></i>
                                        <i class="fab fa-cc-mastercard text-gray-400"></i>
                                        <i class="fab fa-cc-amex text-gray-400"></i>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-300 mb-1">Expiration Date</label>
                                    <div class="grid grid-cols-2 gap-2">
                                        <input type="text" name="exp_month" 
                                               placeholder="MM" 
                                               class="w-full px-4 py-2 bg-gray-700 border border-gray-600 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                               required>
                                        <input type="text" name="exp_year" 
                                               placeholder="YY" 
                                               class="w-full px-4 py-2 bg-gray-700 border border-gray-600 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                               required>
                                    </div>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-300 mb-1">CVC</label>
                                    <div class="relative">
                                        <input type="text" name="cvc" 
                                               placeholder="CVC" 
                                               class="w-full px-4 py-2 bg-gray-700 border border-gray-600 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                               required>
                                        <div class="absolute right-3 top-2.5">
                                            <i class="far fa-credit-card text-gray-400"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-300 mb-1">Cardholder Name</label>
                                <input type="text" name="cardholder_name" 
                                       placeholder="Full name on card"
                                       class="w-full px-4 py-2 bg-gray-700 border border-gray-600 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                       required>
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-300 mb-1">Billing Address</label>
                                <textarea name="billing_address" 
                                          rows="3"
                                          placeholder="Enter your billing address"
                                          class="w-full px-4 py-2 bg-gray-700 border border-gray-600 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-transparent"><?php echo htmlspecialchars($user['billing_address'] !== 'None' ? $user['billing_address'] : ''); ?></textarea>
                            </div>
                            
                            <div class="pt-2">
                                <button type="submit" class="w-full bg-gradient-to-r from-blue-500 to-blue-600 hover:from-blue-600 hover:to-blue-700 text-white font-medium py-2 px-6 rounded-lg transition duration-200">
                                    <?php echo !empty($user['default_payment_method']) ? 'Update' : 'Add'; ?> Payment Method
                                </button>
                            </div>
                        </form>
                    </div>
                    
                    <!-- Transaction History -->
                    <div class="payment-card mt-8">
                        <h2 class="text-xl font-bold mb-4 flex items-center">
                            <i class="fas fa-history mr-2 text-blue-400"></i>
                            Transaction History
                        </h2>
                        
                        <div class="bg-gray-800/50 rounded-lg overflow-hidden">
                            <table class="min-w-full divide-y divide-gray-700">
                                <thead class="bg-gray-700/50">
                                    <tr>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">Date</th>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">Description</th>
                                        <th class="px-4 py-3 text-right text-xs font-medium text-gray-300 uppercase tracking-wider">Amount</th>
                                        <th class="px-4 py-3 text-center text-xs font-medium text-gray-300 uppercase tracking-wider">Status</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-700">
                                    <!-- Sample transaction -->
                                    <tr>
                                        <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-300">Dec 9, 2023</td>
                                        <td class="px-4 py-3 text-sm text-gray-300">Gaming PC - Pulsar Build-1</td>
                                        <td class="px-4 py-3 text-right text-sm text-gray-300">$3,499.99</td>
                                        <td class="px-4 py-3 text-center">
                                            <span class="px-2 py-1 text-xs font-semibold rounded-full bg-green-500/20 text-green-300">Completed</span>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-300">Dec 5, 2023</td>
                                        <td class="px-4 py-3 text-sm text-gray-300">Gaming Accessories</td>
                                        <td class="px-4 py-3 text-right text-sm text-gray-300">$249.99</td>
                                        <td class="px-4 py-3 text-center">
                                            <span class="px-2 py-1 text-xs font-semibold rounded-full bg-green-500/20 text-green-300">Completed</span>
                                        </td>
                                    </tr>
                                    <?php if (empty($user['default_payment_method'])): ?>
                                    <tr>
                                        <td colspan="4" class="px-4 py-6 text-center text-sm text-gray-400">
                                            No transaction history available
                                        </td>
                                    </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
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

        // Format card number
        document.querySelector('input[name="card_number"]').addEventListener('input', function(e) {
            let value = e.target.value.replace(/\D/g, '');
            value = value.replace(/(\d{4})(?=\d)/g, '$1 ');
            e.target.value = value.trim();
        });

        // Format expiration date
        document.querySelector('input[name="exp_month"], input[name="exp_year"]').forEach(input => {
            input.addEventListener('input', function(e) {
                e.target.value = e.target.value.replace(/\D/g, '').substring(0, 2);
            });
        });

        // Format CVC
        document.querySelector('input[name="cvc"]').addEventListener('input', function(e) {
            e.target.value = e.target.value.replace(/\D/g, '').substring(0, 4);
        });
    </script>
</body>
</html>
