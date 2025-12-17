<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}


if (!isset($pageTitle)) {
    $pageTitle = 'GamingHub - Your Ultimate Gaming PC Store';
}

// Check if user is logged in
$isLoggedIn = isset($_SESSION['user_id']);
$isAdmin = $isLoggedIn && isset($_SESSION['is_admin']) && $_SESSION['is_admin'] == 1;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($pageTitle); ?></title>
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <style>
        :root {
            --primary-color: #0d6efd;
            --secondary-color: #6c757d;
            --accent-color: #0dcaf0;
            --dark-color: #212529;
            --light-color: #f8f9fa;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f5f5f5;
            color: #333;
        }
        
        /* Navbar Styling */
        .navbar {
            background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%);
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.3);
            padding: 0.8rem 0;
            border-bottom: 1px solid rgba(255, 255, 255, 0.05);
        }
        
        .navbar-brand {
            font-family: 'Orbitron', 'Segoe UI', sans-serif;
            font-weight: 800;
            font-size: 1.8rem;
            letter-spacing: 1px;
            background: linear-gradient(90deg, #60a5fa 0%, #3b82f6 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            text-shadow: 0 2px 10px rgba(59, 130, 246, 0.3);
            padding: 0.5rem 0;
            transition: all 0.3s ease;
        }
        
        .navbar-brand:hover {
            transform: translateY(-1px);
            text-shadow: 0 4px 15px rgba(59, 130, 246, 0.4);
        }
        
        .nav-link {
            font-weight: 600;
            color: #cbd5e1 !important;
            padding: 0.6rem 1.2rem !important;
            margin: 0 0.2rem;
            border-radius: 6px;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }
        
        .nav-link::before {
            content: '';
            position: absolute;
            bottom: 0;
            left: 50%;
            transform: translateX(-50%);
            width: 0;
            height: 2px;
            background: linear-gradient(90deg, #60a5fa 0%, #3b82f6 100%);
            transition: width 0.3s ease;
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
        
        .btn-primary {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
        }
        
        .btn-outline-primary {
            color: var(--primary-color);
            border-color: var(--primary-color);
        }
        
        .btn-outline-primary:hover {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
        }
        
        .card {
            border: none;
            border-radius: 10px;
            overflow: hidden;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            margin-bottom: 1.5rem;
        }
        
        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
        }
        
        .card-img-top {
            height: 200px;
            object-fit: cover;
        }
        
        .product-price {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--primary-color);
        }
        
        footer {
            background-color: var(--dark-color);
            color: white;
            padding: 3rem 0;
            margin-top: 3rem;
        }
        
        .footer-links a {
            color: #adb5bd;
            text-decoration: none;
            transition: color 0.3s ease;
        }
        
        .footer-links a:hover {
            color: white;
            text-decoration: none;
        }
        
        .social-icons a {
            color: white;
            font-size: 1.5rem;
            margin-right: 1rem;
            transition: color 0.3s ease;
        }
        
        .social-icons a:hover {
            color: var(--accent-color);
        }
        
        .alert {
            border-radius: 10px;
            border: none;
        }
    </style>
</head>
<body>
    <!-- Google Fonts - Orbitron for headings -->
    <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@600;700;800&display=swap" rel="stylesheet">
    
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark sticky-top">
        <div class="container">
            <a class="navbar-brand" href="<?php echo BASE_URL; ?>">
                <i class="fas fa-gamepad me-2"></i>GamingHub
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav mx-auto">
                    <li class="nav-item">
                        <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'index.php' ? 'active' : ''; ?>" href="<?php echo BASE_URL; ?>">
                            <i class="fas fa-home me-1"></i> Home
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'products.php' ? 'active' : ''; ?>" href="products.php">
                            <i class="fas fa-desktop me-1"></i> Gaming Rigs
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'components.php' ? 'active' : ''; ?>" href="#">
                            <i class="fas fa-microchip me-1"></i> Components
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'about.php' ? 'active' : ''; ?>" href="#">
                            <i class="fas fa-info-circle me-1"></i> About
                        </a>
                    </li>
                </ul>
                <div class="d-flex align-items-center">
                    <a href="cart.php" class="nav-link position-relative me-3">
                        <i class="fas fa-shopping-cart fa-lg"></i>
                        <?php 
                        $cartCount = 0;
                        if (isset($_SESSION['cart']) && is_array($_SESSION['cart'])) {
                            $cartCount = count($_SESSION['cart']);
                        }
                        if ($cartCount > 0): ?>
                            <span class="cart-count"><?php echo $cartCount; ?></span>
                        <?php endif; ?>
                    </a>
                    <?php if ($isLoggedIn): ?>
                        <div class="dropdown">
                            <a class="nav-link dropdown-toggle d-flex align-items-center" href="#" role="button" id="userDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="fas fa-user-circle fa-lg me-1"></i>
                                <?php echo htmlspecialchars($_SESSION['username']); ?>
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userDropdown">
                                <li><a class="dropdown-item" href="profile.php"><i class="fas fa-user me-2"></i>Profile</a></li>
                                <?php if ($isAdmin): ?>
                                    <li><a class="dropdown-item" href="admin/"><i class="fas fa-cog me-2"></i>Admin Panel</a></li>
                                <?php endif; ?>
                                    <i class="fas fa-user me-2"></i>Profile</a>
                                </li>
                                <li><a class="dropdown-item" href="<?php echo BASE_URL; ?>/orders.php">
                                    <i class="fas fa-shopping-bag me-2"></i>My Orders</a>
                                </li>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item text-danger" href="<?php echo BASE_URL; ?>/logout.php">
                                    <i class="fas fa-sign-out-alt me-2"></i>Logout</a>
                                </li>
                            </ul>
                        </div>
                    <?php else: ?>
                        <a href="<?php echo BASE_URL; ?>/login.php" class="btn btn-outline-primary me-2">Login</a>
                        <a href="<?php echo BASE_URL; ?>/signup.php" class="btn btn-primary">Sign Up</a>
                    <?php endif; ?>
                    <a href="<?php echo BASE_URL; ?>/cart.php" class="btn btn-outline-dark ms-3 position-relative">
                        <i class="fas fa-shopping-cart"></i>
                        <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                            <?php echo isset($_SESSION['cart']) ? count($_SESSION['cart']) : '0'; ?>
                            <span class="visually-hidden">items in cart</span>
                        </span>
                    </a>
                </div>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <main class="container py-4">
        <?php if (isset($_SESSION['flash_message'])): ?>
            <div class="alert alert-<?php echo $_SESSION['flash_type'] ?? 'info'; ?> alert-dismissible fade show" role="alert">
                <?php 
                    echo $_SESSION['flash_message']; 
                    unset($_SESSION['flash_message']);
                    if (isset($_SESSION['flash_type'])) {
                        unset($_SESSION['flash_type']);
                    }
                ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>
