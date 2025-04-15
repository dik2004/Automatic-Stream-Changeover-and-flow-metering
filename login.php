<?php
session_start();
require_once 'config/database.php';

// If user is already logged in, redirect to index.php
if (isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit();
}

$error = '';

// Handle login form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($username) || empty($password)) {
        $error = 'Please enter both username and password';
    } else {
        try {
            // Test database connection first
            $pdo->query("SELECT 1");
            
            $stmt = $pdo->prepare("SELECT user_id, username, password, role FROM users WHERE username = ?");
            $stmt->execute([$username]);
            $user = $stmt->fetch();

            if ($user) {
                if (password_verify($password, $user['password'])) {
                    $_SESSION['user_id'] = $user['user_id'];
                    $_SESSION['username'] = $user['username'];
                    $_SESSION['role'] = $user['role'];
                    header('Location: index.php');
                    exit();
                } else {
                    $error = 'Invalid password';
                }
            } else {
                $error = 'Username not found';
            }
        } catch (PDOException $e) {
            error_log("Login error: " . $e->getMessage());
            $error = 'Database error: ' . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Flow Metering System</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="css/tailwind.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    animation: {
                        'float': 'float 6s ease-in-out infinite',
                        'pulse-slow': 'pulse 3s infinite',
                    },
                    keyframes: {
                        float: {
                            '0%, 100%': { transform: 'translateY(0)' },
                            '50%': { transform: 'translateY(-20px)' },
                        }
                    }
                }
            }
        }
    </script>
</head>
<body class="min-h-screen flex items-center justify-center bg-gradient-to-br from-gray-900 via-gray-800 to-black relative overflow-hidden">
    <!-- Animated Background Elements -->
    <div class="fixed inset-0 -z-10">
        <!-- Animated Grid -->
        <div class="absolute inset-0 bg-[linear-gradient(rgba(76,175,80,0.1)_1px,transparent_1px),linear-gradient(90deg,rgba(76,175,80,0.1)_1px,transparent_1px)] bg-[size:50px_50px] animate-pulse-slow"></div>
        
        <!-- Floating Elements -->
        <div class="absolute inset-0">
            <div class="absolute w-64 h-64 bg-green-500/10 rounded-full blur-3xl animate-float" style="top: 10%; left: 10%; animation-delay: 0s;"></div>
            <div class="absolute w-64 h-64 bg-blue-500/10 rounded-full blur-3xl animate-float" style="top: 60%; right: 10%; animation-delay: 2s;"></div>
            <div class="absolute w-64 h-64 bg-purple-500/10 rounded-full blur-3xl animate-float" style="bottom: 10%; left: 30%; animation-delay: 4s;"></div>
        </div>
    </div>

    <!-- Main Container -->
    <div class="w-full max-w-md p-10 bg-white/10 backdrop-blur-xl rounded-2xl shadow-2xl border border-white/10 relative overflow-hidden">
        <!-- Animated gradient -->
        <div class="absolute inset-0 bg-gradient-to-r from-transparent via-white/10 to-transparent -translate-x-full hover:translate-x-full transition-transform duration-500"></div>

        <!-- Logo and Title -->
        <div class="text-center mb-8">
            <div class="w-20 h-20 mx-auto mb-4 bg-green-500/20 rounded-full flex items-center justify-center">
                <i class="fas fa-tachometer-alt text-green-500 text-3xl"></i>
            </div>
            <h1 class="text-3xl font-bold text-white mb-2">Welcome Back</h1>
            <p class="text-white/80 text-sm">Login to Flow Metering System</p>
        </div>

        <?php if ($error): ?>
            <div class="bg-red-500/10 border border-red-500/20 text-red-500 px-4 py-3 rounded-lg mb-6 flex items-center">
                <i class="fas fa-exclamation-circle mr-2"></i>
                <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>

        <!-- Login Form -->
        <form class="space-y-6" method="POST" action="login.php">
            <!-- Username Field -->
            <div class="relative group">
                <i class="fas fa-user absolute left-4 top-1/2 -translate-y-1/2 text-white/50 group-focus-within:text-green-500 transition-colors"></i>
                <input type="text" name="username" placeholder="Username" required 
                       class="w-full pl-12 pr-4 py-3 bg-white/5 border border-white/10 rounded-lg text-white placeholder-white/50 focus:outline-none focus:border-green-500 focus:ring-2 focus:ring-green-500/20 transition-all">
            </div>

            <!-- Password Field -->
            <div class="relative group">
                <i class="fas fa-lock absolute left-4 top-1/2 -translate-y-1/2 text-white/50 group-focus-within:text-green-500 transition-colors"></i>
                <input type="password" name="password" id="password" placeholder="Password" required
                       class="w-full pl-12 pr-12 py-3 bg-white/5 border border-white/10 rounded-lg text-white placeholder-white/50 focus:outline-none focus:border-green-500 focus:ring-2 focus:ring-green-500/20 transition-all">
                <i class="fas fa-eye password-toggle absolute right-4 top-1/2 -translate-y-1/2 text-white/50 cursor-pointer hover:text-green-500 transition-colors" id="togglePassword"></i>
            </div>

            <!-- Remember Me -->
            <div class="flex items-center justify-between">
                <label class="flex items-center text-white/80 text-sm">
                    <input type="checkbox" name="remember" class="rounded border-white/10 text-green-500 focus:ring-green-500/20 bg-white/5">
                    <span class="ml-2">Remember me</span>
                </label>
                <a href="#" class="text-sm text-green-500 hover:text-green-400 transition-colors">Forgot password?</a>
            </div>

            <!-- Submit Button -->
            <button type="submit" class="w-full py-3 bg-green-500 hover:bg-green-600 text-white rounded-lg font-medium transition-colors duration-300 flex items-center justify-center group">
                <i class="fas fa-sign-in-alt mr-2 group-hover:rotate-12 transition-transform"></i>
                Login
            </button>
        </form>

        <!-- Register Link -->
        <div class="text-center mt-6 text-white/80">
            Don't have an account? 
            <a href="register.php" class="text-green-500 hover:text-green-400 transition-colors">Register here</a>
        </div>
    </div>

    <script>
        // Password visibility toggle
        const togglePassword = document.querySelector('#togglePassword');
        const password = document.querySelector('#password');

        togglePassword.addEventListener('click', function (e) {
            const type = password.getAttribute('type') === 'password' ? 'text' : 'password';
            password.setAttribute('type', type);
            this.classList.toggle('fa-eye');
            this.classList.toggle('fa-eye-slash');
        });

        // Add loading animation to login button
        document.querySelector('form').addEventListener('submit', function(e) {
            const button = this.querySelector('button');
            button.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i> Logging in...';
            button.disabled = true;
        });
    </script>
</body>
</html> 