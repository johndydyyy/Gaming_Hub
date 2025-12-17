<?php
session_start();
require_once 'config.php';

$error = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);
    $confirm_password = trim($_POST['confirm_password']);
    
    if ($password !== $confirm_password) {
        $error = "Passwords do not match.";
    } else {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        
        try {
            $stmt = $pdo->prepare("INSERT INTO users (username, email, password) VALUES (?, ?, ?)");
            $stmt->execute([$username, $email, $hashed_password]);
            
            $_SESSION['user_id'] = $pdo->lastInsertId();
            $_SESSION['username'] = $username;
            
            header("Location: dashboard.php");
            exit();
        } catch(PDOException $e) {
            if ($e->getCode() == 23000) {
                $error = "Username or email already exists.";
            } else {
                $error = "Error: " . $e->getMessage();
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign Up - Gaming Hub</title>
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
        .signup-container {
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
            color: white;
        }
        .input-field:focus {
            border-color: #3b82f6;
            box-shadow: 0 0 0 2px rgba(59, 130, 246, 0.25);
            outline: none;
        }
        .signup-btn {
            background: linear-gradient(45deg, #2563eb, #3b82f6);
            transition: all 0.3s ease;
            letter-spacing: 0.5px;
            text-transform: uppercase;
            font-weight: 600;
        }
        .signup-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(59, 130, 246, 0.4);
        }
        .password-strength {
            height: 4px;
            background: #1e293b;
            margin-top: 4px;
            border-radius: 2px;
            overflow: hidden;
        }
        .password-strength::after {
            content: '';
            display: block;
            height: 100%;
            width: 0%;
            background: #ef4444;
            transition: width 0.3s ease, background 0.3s ease;
        }
        .password-strength.weak::after { width: 33%; background: #ef4444; }
        .password-strength.medium::after { width: 66%; background: #f59e0b; }
        .password-strength.strong::after { width: 100%; background: #10b981; }
    </style>
</head>
<body class="min-h-screen flex items-center justify-center p-4">
    <div class="signup-container w-full max-w-md p-8 rounded-xl">
        <div class="text-center mb-8">
            <div class="flex items-center justify-center mb-2">
                <i class="fas fa-gamepad text-blue-400 text-4xl mr-3"></i>
                <h1 class="text-4xl font-bold brand-text">GamingHub</h1>
            </div>
            <p class="text-gray-400">Create your gaming account</p>
        </div>
        
        <?php if ($error): ?>
            <div class="bg-red-500/20 border border-red-500 text-red-200 px-4 py-3 rounded-lg mb-6 flex items-center">
                <i class="fas fa-exclamation-circle mr-2"></i>
                <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>
        
        <form method="POST" action="signup.php" class="space-y-6">
            <div class="space-y-2">
                <label for="username" class="block text-sm font-medium text-gray-300">Username</label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <i class="fas fa-user text-gray-500"></i>
                    </div>
                    <input type="text" id="username" name="username" required 
                           class="input-field w-full pl-10 pr-4 py-3 rounded-lg focus:outline-none"
                           placeholder="Enter your username">
                </div>
            </div>
            
            <div class="space-y-2">
                <label for="email" class="block text-sm font-medium text-gray-300">Email Address</label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <i class="fas fa-envelope text-gray-500"></i>
                    </div>
                    <input type="email" id="email" name="email" required 
                           class="input-field w-full pl-10 pr-4 py-3 rounded-lg focus:outline-none"
                           placeholder="your@email.com">
                </div>
            </div>
            
            <div class="space-y-2">
                <label for="password" class="block text-sm font-medium text-gray-300">Password</label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <i class="fas fa-lock text-gray-500"></i>
                    </div>
                    <input type="password" id="password" name="password" required 
                           class="input-field w-full pl-10 pr-10 py-3 rounded-lg focus:outline-none"
                           placeholder="••••••••"
                           onkeyup="checkPasswordStrength(this.value)">
                    <div class="absolute inset-y-0 right-0 pr-3 flex items-center">
                        <i class="fas fa-eye-slash text-gray-500 cursor-pointer" id="togglePassword"></i>
                    </div>
                </div>
                <div class="password-strength mt-1" id="passwordStrength"></div>
                <p class="text-xs text-gray-400 mt-1">Use 8 or more characters with a mix of letters, numbers & symbols</p>
            </div>
            
            <div class="space-y-2">
                <label for="confirm_password" class="block text-sm font-medium text-gray-300">Confirm Password</label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <i class="fas fa-lock text-gray-500"></i>
                    </div>
                    <input type="password" id="confirm_password" name="confirm_password" required 
                           class="input-field w-full pl-10 pr-4 py-3 rounded-lg focus:outline-none"
                           placeholder="••••••••">
                </div>
            </div>
            
            <div class="flex items-start">
                <div class="flex items-center h-5">
                    <input id="terms" name="terms" type="checkbox" required
                           class="h-4 w-4 rounded bg-gray-700 border-gray-600 text-blue-600 focus:ring-blue-500">
                </div>
                <div class="ml-3 text-sm">
                    <label for="terms" class="text-gray-300">
                        I agree to the <a href="#" class="text-blue-400 hover:text-blue-300">Terms of Service</a> and 
                        <a href="#" class="text-blue-400 hover:text-blue-300">Privacy Policy</a>
                    </label>
                </div>
            </div>
            
            <button type="submit" class="signup-btn w-full text-white font-bold py-3 px-4 rounded-lg transition-all duration-200">
                Create Account
            </button>
            
            <div class="text-center text-sm text-gray-400 pt-4 border-t border-gray-700">
                Already have an account? 
                <a href="login.php" class="text-blue-400 hover:text-blue-300 font-medium">Sign in</a>
            </div>
        </form>
    </div>

    <script>
        // Toggle password visibility
        const togglePassword = document.querySelector('#togglePassword');
        const password = document.querySelector('#password');
        const confirmPassword = document.querySelector('#confirm_password');
        
        togglePassword.addEventListener('click', function() {
            const type = password.getAttribute('type') === 'password' ? 'text' : 'password';
            password.setAttribute('type', type);
            confirmPassword.setAttribute('type', type);
            this.classList.toggle('fa-eye');
            this.classList.toggle('fa-eye-slash');
        });

        // Password strength indicator
        function checkPasswordStrength(password) {
            const strengthBar = document.getElementById('passwordStrength');
            let strength = 0;
            
            // Check password length
            if (password.length >= 8) strength++;
            // Check for numbers
            if (password.match(/([0-9])/)) strength++;
            // Check for special characters
            if (password.match(/([!,%,&,@,#,$,^,*,?,_,~])/)) strength++;
            // Check for uppercase letters
            if (password.match(/([A-Z])/)) strength++;
            
            // Update strength bar
            strengthBar.className = 'password-strength';
            if (password.length > 0) {
                if (strength <= 2) {
                    strengthBar.classList.add('weak');
                } else if (strength === 3) {
                    strengthBar.classList.add('medium');
                } else {
                    strengthBar.classList.add('strong');
                }
            }
        }

        // Add animation on load
        document.addEventListener('DOMContentLoaded', () => {
            const form = document.querySelector('form');
            form.classList.add('animate-fade-in');
        });
    </script>
</body>
</html>
