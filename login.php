<?php
session_start();
require_once 'config.php';

$error = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);
    
    try {
        $stmt = $pdo->prepare("SELECT id, username, password FROM users WHERE username = ?");
        $stmt->execute([$username]);
        $user = $stmt->fetch();
        
        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            
            header("Location: dashboard.php");
            exit();
        } else {
            $error = "Invalid username or password.";
        }
    } catch(PDOException $e) {
        $error = "Error: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Gaming Hub</title>
    <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@400;500;600;700;800;900&family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        body {
            background: url('images/background.jpg') no-repeat center center fixed;
            background-size: cover;
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
        .login-container {
            background: rgba(30, 41, 59, 0.9);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            box-shadow: 0 8px 32px 0 rgba(0, 0, 0, 0.36);
        }
        .brand-text {
            font-family: 'Orbitron', sans-serif;
            background: linear-gradient(90deg, #60a5fa 0%, #3b82f6 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            text-shadow: 0 2px 10px rgba(59, 130, 246, 0.3);
        }
        .input-field {
            background: rgba(15, 23, 42, 0.6);
            border: 1px solid rgba(255, 255, 255, 0.1);
            transition: all 0.3s ease;
        }
        .input-field:focus {
            border-color: #3b82f6;
            box-shadow: 0 0 0 2px rgba(59, 130, 246, 0.25);
        }
        .login-btn {
            background: linear-gradient(45deg, #2563eb, #3b82f6);
            transition: all 0.3s ease;
            letter-spacing: 0.5px;
            text-transform: uppercase;
            font-weight: 600;
        }
        .login-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(59, 130, 246, 0.4);
        }
    </style>
</head>
<body class="min-h-screen flex items-center justify-center p-4">
    <div class="login-container w-full max-w-md p-8 rounded-xl">
        <div class="text-center mb-8">
            <div class="flex items-center justify-center mb-2">
                <i class="fas fa-gamepad text-blue-400 text-4xl mr-3"></i>
                <h1 class="text-4xl font-bold brand-text">GamingHub</h1>
            </div>
            <p class="text-gray-400">Enter your credentials to continue</p>
        </div>
        
        <?php if ($error): ?>
            <div class="bg-red-500/20 border border-red-500 text-red-200 px-4 py-3 rounded-lg mb-6 flex items-center">
                <i class="fas fa-exclamation-circle mr-2"></i>
                <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>
        
        <form method="POST" action="login.php" class="space-y-6">
            <div class="space-y-2">
                <label for="username" class="block text-sm font-medium text-gray-300">Username</label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <i class="fas fa-user text-gray-500"></i>
                    </div>
                    <input type="text" id="username" name="username" required 
                           class="input-field w-full pl-10 pr-4 py-3 rounded-lg focus:outline-none text-white">
                </div>
            </div>
            
            <div class="space-y-2">
                <div class="flex justify-between items-center">
                    <label for="password" class="block text-sm font-medium text-gray-300">Password</label>
                    <a href="#" class="text-xs text-blue-400 hover:text-blue-300">Forgot password?</a>
                </div>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <i class="fas fa-lock text-gray-500"></i>
                    </div>
                    <input type="password" id="password" name="password" required 
                           class="input-field w-full pl-10 pr-4 py-3 rounded-lg focus:outline-none text-white">
                </div>
            </div>
            
            <div class="flex items-center">
                <input type="checkbox" id="remember" name="remember" class="h-4 w-4 text-blue-600 rounded bg-gray-700 border-gray-600">
                <label for="remember" class="ml-2 text-sm text-gray-300">Remember me</label>
            </div>
            
            <button type="submit" class="login-btn w-full text-white font-bold py-3 px-4 rounded-lg transition-all duration-200">
                Sign In
            </button>
            
            <div class="text-center text-sm text-gray-400 pt-4 border-t border-gray-700">
                New to GamingHub? 
                <a href="signup.php" class="text-blue-400 hover:text-blue-300 font-medium">Create an account</a>
            </div>
        </form>
    </div>

    <script>
        // Add animation on load
        document.addEventListener('DOMContentLoaded', () => {
            const form = document.querySelector('form');
            form.classList.add('animate-fade-in');
        });
    </script>
</body>
</html>
