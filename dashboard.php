<?php
session_start();
require_once 'config.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Fetch products from database
try {
    $stmt = $pdo->query("SELECT * FROM products");
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    die("Error fetching products: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Gaming Hub</title>
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
        
        .product-card h3 {
            font-size: 1.25rem;
            margin-bottom: 0.75rem;
            color: #e2e8f0;
        }
        
        .product-card {
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        
        .product-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.2);
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
            background: rgba(255, 255, 255, 0.05);
        }
        
        .nav-link:hover::before {
            width: 60%;
        }
        
        .nav-link.active {
            color: #ffffff !important;
            font-weight: 700;
        }
        
        .nav-link.active::before {
            width: 60%;
        }
        
        .cart-count {
            position: absolute;
            top: -5px;
            right: -8px;
            background: #ef4444;
            color: white;
            border-radius: 50%;
            width: 20px;
            height: 20px;
            font-size: 0.7rem;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
        }
        
        .navbar-toggler {
            border: none;
            padding: 0.5rem;
        }
        
        .navbar-toggler:focus {
            box-shadow: 0 0 0 2px rgba(96, 165, 250, 0.5);
            outline: none;
        }
        
        .navbar-toggler-icon {
            background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 30 30'%3e%3cpath stroke='rgba%28255, 255, 255, 0.8%29' stroke-linecap='round' stroke-miterlimit='10' stroke-width='2' d='M4 7h22M4 15h22M4 23h22'/%3e%3c/svg%3e");
        }
        
        @media (max-width: 991.98px) {
            .navbar-collapse {
                background: rgba(15, 23, 42, 0.98);
                padding: 1rem;
                border-radius: 8px;
                margin-top: 0.5rem;
                box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
            }
            
            .nav-link {
                margin: 0.3rem 0;
                padding: 0.8rem 1rem !important;
                border-radius: 6px;
            }
            
            .nav-link:hover {
                background: rgba(96, 165, 250, 0.1);
            }
        }
        .product-card {
            background-color: rgba(30, 41, 59, 0.8);
            border-radius: 0.5rem;
            transition: all 0.3s ease;
            backdrop-filter: blur(5px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            box-shadow: 0 8px 32px 0 rgba(0, 0, 0, 0.36);
        }
        .product-card:hover {
            transform: translateY(-4px);
        }
        .add-to-cart {
            background: linear-gradient(45deg, #2563eb, #3b82f6);
            transition: all 0.3s ease;
            border: none;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            box-shadow: 0 4px 15px rgba(59, 130, 246, 0.3);
        }
        .add-to-cart:hover {
            background-color: #1d4ed8;
        }
        .footer {
            background-color: rgba(15, 23, 42, 0.9);
            border-top: 1px solid rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
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
                <a href="#" class="nav-link">Home</a>
                <a href="manage_account.php" class="nav-link">Manage Account</a>
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

    <!-- Hero Section -->
    <div class="container mx-auto px-4 py-8 relative z-10">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
            <div class="relative rounded-lg overflow-hidden h-64">
                <img src="images/rtx.gif" 
                     alt="RTX Gaming" 
                     class="w-full h-full object-cover">
                <div class="absolute inset-0 bg-gradient-to-t from-black to-transparent opacity-70"></div>

            </div>
            <div class="relative rounded-lg overflow-hidden h-64">
                <img src="images/ads.gif"
                     alt="PC Build" 
                     class="w-full h-full object-cover">
                <div class="absolute inset-0 bg-gradient-to-t from-black to-transparent opacity-70"></div>
            </div>
        </div>

        <!-- Products Grid -->
            <div class="mb-12">
                <div class="mb-10 text-center md:text-left">
                    <h2 class="text-3xl font-medium text-white relative inline-block">
                        <span class="relative">
                            <span class="relative">Featured Builds</span>
                        </span>
                    </h2>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <?php foreach ($products as $product): ?>
                <div class="product-card p-6">
                    <div class="h-48 bg-gray-700 rounded-lg mb-4 overflow-hidden">
                        <img src="<?php echo htmlspecialchars($product['image_url']); ?>" 
                             alt="<?php echo htmlspecialchars($product['name']); ?>" 
                             class="w-full h-full object-cover">
                    </div>
                    <h3 class="text-xl font-semibold mb-2"><?php echo htmlspecialchars($product['name']); ?></h3>
                    <ul class="text-gray-300 text-sm space-y-1 mb-4">
                        <li><strong>CPU:</strong> <?php echo htmlspecialchars($product['cpu']); ?></li>
                        <li><strong>GPU:</strong> <?php echo htmlspecialchars($product['gpu']); ?></li>
                        <li><strong>RAM:</strong> <?php echo htmlspecialchars($product['ram']); ?></li>
                        <li><strong>Cooler:</strong> <?php echo htmlspecialchars($product['cooler']); ?></li>
                        <li><strong>PSU:</strong> <?php echo htmlspecialchars($product['psu']); ?></li>
                        <li><strong>Case:</strong> <?php echo htmlspecialchars($product['case_type']); ?></li>
                    </ul>
                    <div class="flex justify-between items-center">
                        <span class="text-xl font-bold">₱<?php echo number_format($product['price'], 2); ?></span>
                        <button class="add-to-cart text-white px-4 py-2 rounded">
                            Add to Cart
                        </button>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer class="footer py-12 relative z-10">
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
                        <li><a href="#" class="text-gray-400 hover:text-white">About Us</a></li>
                        <li><a href="#" class="text-gray-400 hover:text-white">Terms of Use</a></li>
                        <li><a href="#" class="text-gray-400 hover:text-white">Privacy Policy</a></li>
                        <li><a href="#" class="text-gray-400 hover:text-white">Cookies Policy</a></li>
                        <li><a href="#" class="text-gray-400 hover:text-white">Take Down Policy</a></li>
                        <li><a href="#" class="text-gray-400 hover:text-white">Advertise with us</a></li>
                    </ul>
                </div>
                
                <!-- Contact Info -->
                <div>
                    <h4 class="text-lg font-semibold mb-4">Our Contacts</h4>
                    <address class="not-italic text-gray-400 space-y-2">
                        <p>SJIT T.CALO ST<br>BUTUAN CITY</p>
                        <p>Email: info@gaminghub.com</p>
                        <p>Phone: (123) 456-7890</p>
                    </address>
                </div>
            
        
        </div>
    </footer>

    <script>
        // Mobile menu toggle
        document.querySelector('.md\\:hidden').addEventListener('click', function() {
            const nav = document.querySelector('.md\\:flex');
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

        // Add to cart
        document.querySelectorAll('.add-to-cart').forEach((button, index) => {
            button.addEventListener('click', function() {
                const productCard = this.closest('.product-card');
                const productId = <?php echo $products[0]['id']; ?> + index;
                const productName = productCard.querySelector('h3').textContent;
                
                const formData = new FormData();
                formData.append('product_id', productId);
                formData.append('quantity', 1);
                formData.append('add_to_cart', true);
                
                fetch('update_cart.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Update cart count
                        const cartCount = document.getElementById('cart-count');
                        if (cartCount) {
                            cartCount.textContent = data.cart_count;
                        }
                        
                        // Show success message with GamingHub theme
                        const notification = document.createElement('div');
                        notification.className = 'fixed top-4 right-4 bg-dark text-white px-6 py-3 rounded-lg shadow-lg flex items-center border-l-4 border-accent transform transition-all duration-300 translate-x-0 opacity-100';
                        notification.style.background = 'linear-gradient(135deg, #1a1a2e 0%, #16213e 100%)';
                        notification.style.boxShadow = '0 4px 20px rgba(0, 0, 0, 0.2)';
                        notification.style.zIndex = '9999';
                        notification.style.transition = 'all 0.3s ease-in-out';
                        notification.innerHTML = `
                            <div class="flex items-center">
                                <div class="mr-3 text-accent">
                                    <i class="fas fa-gamepad text-xl"></i>
                                </div>
                                <div>
                                    <div class="font-bold text-accent">Game On!</div>
                                    <div class="text-sm">${productName} added to cart!</div>
                                </div>
                                <button onclick="this.parentElement.parentElement.remove()" class="ml-4 text-gray-300 hover:text-white focus:outline-none">
                                    <i class="fas fa-times"></i>
                                </button>
                            </div>
                        `;
                        document.body.appendChild(notification);
                        
                        // Remove notification after 3 seconds
                        setTimeout(() => {
                            notification.style.opacity = '0';
                            notification.style.transition = 'opacity 0.5s';
                            setTimeout(() => notification.remove(), 500);
                        }, 3000);
                    } else {
                        alert('Failed to add item to cart: ' + (data.message || 'Unknown error'));
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('An error occurred while adding the item to cart');
                });
            });
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