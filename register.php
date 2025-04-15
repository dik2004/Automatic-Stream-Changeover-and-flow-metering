<?php
session_start();
require_once 'config/database.php';

// If user is already logged in, redirect to index.php
if (isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit();
}

$error = '';
$success = '';

// Handle registration form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    // Validate input
    if (empty($username) || empty($password) || empty($confirm_password)) {
        $error = 'Please fill in all fields';
    } elseif ($password !== $confirm_password) {
        $error = 'Passwords do not match';
    } elseif (strlen($password) < 6) {
        $error = 'Password must be at least 6 characters long';
    } else {
        try {
            // Check if username already exists
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE username = ?");
            $stmt->execute([$username]);
            if ($stmt->fetchColumn() > 0) {
                $error = 'Username already exists';
            } else {
                // Insert new user
                $stmt = $pdo->prepare("INSERT INTO users (username, password, role) VALUES (?, ?, 'operator')");
                $stmt->execute([$username, password_hash($password, PASSWORD_DEFAULT)]);
                $success = 'Registration successful! You can now login.';
            }
        } catch (PDOException $e) {
            $error = 'Registration failed. Please try again.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - Flow Metering System</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="css/tailwind.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    animation: {
                        'float': 'float 15s infinite linear',
                        'fade-in-up': 'fadeInUp 0.8s ease-out',
                        'pulse-slow': 'pulse 3s infinite',
                    },
                    keyframes: {
                        float: {
                            '0%': { transform: 'translateY(100vh) scale(0)', opacity: '0' },
                            '10%': { opacity: '1' },
                            '90%': { opacity: '1' },
                            '100%': { transform: 'translateY(-100px) scale(1)', opacity: '0' },
                        },
                        fadeInUp: {
                            '0%': { transform: 'translateY(20px)', opacity: '0' },
                            '100%': { transform: 'translateY(0)', opacity: '1' },
                        }
                    }
                }
            }
        }
    </script>
</head>
<body class="min-h-screen flex items-center justify-center bg-black relative overflow-hidden">
    <!-- Background Video -->
    <video class="fixed top-0 left-0 w-full h-full object-cover -z-10 opacity-70" autoplay muted loop>
        <source src="videos/flow-metering.mp4" type="video/mp4">
    </video>

    <!-- Animated Background Elements -->
    <div class="fixed inset-0 -z-10">
        <!-- Gradient Overlay -->
        <div class="absolute inset-0 bg-gradient-to-b from-black/80 via-black/50 to-black/80"></div>
        
        <!-- Animated Grid -->
        <div class="absolute inset-0 bg-[linear-gradient(rgba(76,175,80,0.1)_1px,transparent_1px),linear-gradient(90deg,rgba(76,175,80,0.1)_1px,transparent_1px)] bg-[size:50px_50px] animate-pulse-slow"></div>
        
        <!-- Floating Particles -->
        <div id="particles" class="absolute inset-0"></div>
    </div>

    <!-- Main Container -->
    <div class="w-full max-w-md p-10 bg-white/10 backdrop-blur-lg rounded-2xl shadow-2xl border border-white/10 relative overflow-hidden animate-fade-in-up">
        <!-- Animated gradient -->
        <div class="absolute inset-0 bg-gradient-to-r from-transparent via-white/10 to-transparent -translate-x-full hover:translate-x-full transition-transform duration-500"></div>

        <!-- Header -->
        <div class="text-center mb-8 text-white">
            <h1 class="text-3xl font-bold mb-3 text-shadow-lg">Create Account</h1>
            <p class="text-white/80 text-sm">Join the Flow Metering System</p>
        </div>

        <?php if ($error): ?>
            <div class="bg-red-500/10 border border-red-500/20 text-red-500 px-4 py-3 rounded-lg mb-6 flex items-center">
                <i class="fas fa-exclamation-circle mr-2"></i>
                <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>

        <?php if ($success): ?>
            <div class="bg-green-500/10 border border-green-500/20 text-green-500 px-4 py-3 rounded-lg mb-6 flex items-center">
                <i class="fas fa-check-circle mr-2"></i>
                <?php echo htmlspecialchars($success); ?>
            </div>
        <?php endif; ?>

        <!-- Registration Form -->
        <form class="space-y-6" method="POST" action="register.php">
            <!-- Username Field -->
            <div class="relative group">
                <i class="fas fa-user absolute left-4 top-1/2 -translate-y-1/2 text-white/50 group-focus-within:text-green-500 transition-colors"></i>
                <input type="text" name="username" placeholder="Username" required 
                       value="<?php echo isset($_POST['username']) ? htmlspecialchars($_POST['username']) : ''; ?>"
                       class="w-full pl-12 pr-4 py-3 bg-white/5 border border-white/10 rounded-lg text-white placeholder-white/50 focus:outline-none focus:border-green-500 focus:ring-2 focus:ring-green-500/20 transition-all">
            </div>

            <!-- Password Field -->
            <div class="relative group">
                <i class="fas fa-lock absolute left-4 top-1/2 -translate-y-1/2 text-white/50 group-focus-within:text-green-500 transition-colors"></i>
                <input type="password" name="password" id="password" placeholder="Password" required
                       class="w-full pl-12 pr-12 py-3 bg-white/5 border border-white/10 rounded-lg text-white placeholder-white/50 focus:outline-none focus:border-green-500 focus:ring-2 focus:ring-green-500/20 transition-all">
                <i class="fas fa-eye password-toggle absolute right-4 top-1/2 -translate-y-1/2 text-white/50 cursor-pointer hover:text-green-500 transition-colors" id="togglePassword"></i>
                <div class="h-1 mt-2 bg-white/5 rounded-full overflow-hidden">
                    <div class="password-strength h-full w-0 transition-all duration-300"></div>
                </div>
            </div>

            <!-- Confirm Password Field -->
            <div class="relative group">
                <i class="fas fa-lock absolute left-4 top-1/2 -translate-y-1/2 text-white/50 group-focus-within:text-green-500 transition-colors"></i>
                <input type="password" name="confirm_password" id="confirmPassword" placeholder="Confirm Password" required
                       class="w-full pl-12 pr-12 py-3 bg-white/5 border border-white/10 rounded-lg text-white placeholder-white/50 focus:outline-none focus:border-green-500 focus:ring-2 focus:ring-green-500/20 transition-all">
                <i class="fas fa-eye password-toggle absolute right-4 top-1/2 -translate-y-1/2 text-white/50 cursor-pointer hover:text-green-500 transition-colors" id="toggleConfirmPassword"></i>
            </div>

            <!-- Submit Button -->
            <button type="submit" class="w-full py-3 bg-green-500 hover:bg-green-600 text-white rounded-lg font-medium transition-colors duration-300 flex items-center justify-center group">
                <i class="fas fa-user-plus mr-2 group-hover:rotate-12 transition-transform"></i>
                Register
            </button>
        </form>

        <!-- Login Link -->
        <div class="text-center mt-6 text-white/80">
            Already have an account? 
            <a href="login.php" class="text-green-500 hover:text-green-400 transition-colors">Login here</a>
        </div>
    </div>

    <script>
        // Create particles
        function createParticles() {
            const particlesContainer = document.getElementById('particles');
            const particleCount = 50;

            for (let i = 0; i < particleCount; i++) {
                const particle = document.createElement('div');
                particle.className = 'absolute w-1 h-1 bg-green-500/50 rounded-full animate-float';
                particle.style.left = `${Math.random() * 100}%`;
                particle.style.animationDelay = `${Math.random() * 15}s`;
                particlesContainer.appendChild(particle);
            }
        }

        // Initialize particles
        createParticles();

        // Password visibility toggle
        const togglePassword = document.querySelector('#togglePassword');
        const toggleConfirmPassword = document.querySelector('#toggleConfirmPassword');
        const password = document.querySelector('#password');
        const confirmPassword = document.querySelector('#confirmPassword');

        togglePassword.addEventListener('click', function (e) {
            const type = password.getAttribute('type') === 'password' ? 'text' : 'password';
            password.setAttribute('type', type);
            this.classList.toggle('fa-eye');
            this.classList.toggle('fa-eye-slash');
        });

        toggleConfirmPassword.addEventListener('click', function (e) {
            const type = confirmPassword.getAttribute('type') === 'password' ? 'text' : 'password';
            confirmPassword.setAttribute('type', type);
            this.classList.toggle('fa-eye');
            this.classList.toggle('fa-eye-slash');
        });

        // Password strength indicator
        const strengthIndicator = document.querySelector('.password-strength');

        password.addEventListener('input', function() {
            const value = this.value;
            let strength = 0;

            if (value.length >= 8) strength++;
            if (value.match(/[a-z]/) && value.match(/[A-Z]/)) strength++;
            if (value.match(/\d/)) strength++;
            if (value.match(/[^a-zA-Z\d]/)) strength++;

            if (value.length === 0) {
                strengthIndicator.style.width = '0';
                strengthIndicator.className = 'password-strength h-full w-0 transition-all duration-300';
            } else if (strength <= 1) {
                strengthIndicator.style.width = '30%';
                strengthIndicator.className = 'password-strength h-full bg-red-500 transition-all duration-300';
            } else if (strength <= 2) {
                strengthIndicator.style.width = '60%';
                strengthIndicator.className = 'password-strength h-full bg-yellow-500 transition-all duration-300';
            } else {
                strengthIndicator.style.width = '100%';
                strengthIndicator.className = 'password-strength h-full bg-green-500 transition-all duration-300';
            }
        });

        // Add loading animation to register button
        document.querySelector('form').addEventListener('submit', function(e) {
            const button = this.querySelector('button');
            button.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i> Registering...';
            button.disabled = true;
        });
    </script>
</body>
</html> 