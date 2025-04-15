<?php
session_start();

$user = null;
$db_connected = false;

try {
    require_once 'database/db_connect.php';
    
    // Check if user is logged in
    if (!isset($_SESSION['user_id'])) {
        header('Location: login.php');
        exit();
    }

    // Get user info
    $stmt = $pdo->prepare("SELECT username, role FROM users WHERE user_id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch();
    $db_connected = true;
} catch (Exception $e) {
    error_log("Database error: " . $e->getMessage());
}

// Handle form submission
$message = '';
$messageType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $subject = trim($_POST['subject']);
    $message_text = trim($_POST['message']);

    // Validate inputs
    if (empty($name) || empty($email) || empty($subject) || empty($message_text)) {
        $message = 'Please fill in all fields.';
        $messageType = 'error';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message = 'Please enter a valid email address.';
        $messageType = 'error';
    } else {
        try {
            // Store message in database
            $stmt = $pdo->prepare("INSERT INTO contact_messages (name, email, subject, message) VALUES (?, ?, ?, ?)");
            if ($stmt->execute([$name, $email, $subject, $message_text])) {
                $message = 'Thank you for your message! We will get back to you soon.';
                $messageType = 'success';
            } else {
                throw new Exception("Failed to save message");
            }
        } catch (Exception $e) {
            error_log("Contact form error: " . $e->getMessage());
            $message = 'Sorry, there was an error processing your message. Please try again later.';
            $messageType = 'error';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contact Us - Flow Metering System</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body {
            background: linear-gradient(-45deg, #ee7752, #e73c7e, #23a6d5, #23d5ab);
            background-size: 400% 400%;
            animation: gradient 15s ease infinite;
        }
        @keyframes gradient {
            0% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
            100% { background-position: 0% 50%; }
        }
        .floating {
            animation: float 6s ease-in-out infinite;
        }
        @keyframes float {
            0% { transform: translateY(0px); }
            50% { transform: translateY(-20px); }
            100% { transform: translateY(0px); }
        }
        .form-input:focus {
            box-shadow: 0 0 0 3px rgba(34, 197, 94, 0.2);
        }
        .card-hover {
            transition: all 0.3s ease;
        }
        .card-hover:hover {
            transform: translateY(-5px);
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="bg-white/90 backdrop-blur-sm shadow-lg sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16">
                <div class="flex">
                    <div class="flex-shrink-0 flex items-center">
                        <i class="fas fa-tachometer-alt text-green-500 text-2xl"></i>
                        <span class="ml-2 text-xl font-semibold text-gray-800">Flow Metering</span>
                    </div>
                    <div class="hidden sm:ml-6 sm:flex sm:space-x-8">
                        <a href="index.php" class="border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700 inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium">
                            <i class="fas fa-home mr-2"></i> Dashboard
                        </a>
                        <a href="about.php" class="border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700 inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium">
                            <i class="fas fa-users mr-2"></i> About Us
                        </a>
                        <a href="contact.php" class="border-green-500 text-gray-900 inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium">
                            <i class="fas fa-envelope mr-2"></i> Contact
                        </a>
                    </div>
                </div>
                <?php if ($db_connected && $user): ?>
                <div class="flex items-center">
                    <div class="relative" id="userMenu">
                        <button class="flex items-center text-gray-700 hover:text-gray-900 transition-colors duration-200 cursor-pointer px-3 py-2 rounded-md hover:bg-gray-100" id="userMenuButton">
                            <i class="fas fa-user-circle text-xl mr-2"></i>
                            <span class="text-sm font-medium"><?php echo htmlspecialchars($user['username']); ?></span>
                            <i class="fas fa-chevron-down ml-2 text-xs transition-transform duration-200"></i>
                        </button>
                        <div id="userMenuDropdown" class="absolute right-0 mt-2 w-48 bg-white rounded-md shadow-lg py-1 hidden transform transition-all duration-200 z-50">
                            <a href="logout.php" class="block px-4 py-2 text-sm text-gray-700 hover:bg-green-50 hover:text-green-700 cursor-pointer transition-colors duration-200 flex items-center">
                                <i class="fas fa-sign-out-alt mr-2"></i> 
                                <span>Logout</span>
                            </a>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="min-h-screen py-12 px-4 sm:px-6 lg:px-8">
        <div class="max-w-7xl mx-auto">
            <div class="text-center mb-12">
                <h1 class="text-4xl font-bold text-white mb-4 floating">Contact Us</h1>
                <p class="text-lg text-white/90">Get in touch with our team for any questions or support</p>
            </div>

            <?php if ($message): ?>
            <div class="mb-8 p-4 rounded-lg <?php echo $messageType === 'success' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700'; ?>">
                <?php echo htmlspecialchars($message); ?>
            </div>
            <?php endif; ?>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-12">
                <!-- Contact Form -->
                <div class="bg-white/90 backdrop-blur-sm rounded-lg shadow-xl p-8 card-hover">
                    <form action="contact.php" method="POST" class="space-y-6">
                        <div>
                            <label for="name" class="block text-sm font-medium text-gray-700">Name</label>
                            <input type="text" name="name" id="name" required
                                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-green-500 focus:ring-green-500 form-input">
                        </div>
                        <div>
                            <label for="email" class="block text-sm font-medium text-gray-700">Email</label>
                            <input type="email" name="email" id="email" required
                                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-green-500 focus:ring-green-500 form-input">
                        </div>
                        <div>
                            <label for="subject" class="block text-sm font-medium text-gray-700">Subject</label>
                            <input type="text" name="subject" id="subject" required
                                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-green-500 focus:ring-green-500 form-input">
                        </div>
                        <div>
                            <label for="message" class="block text-sm font-medium text-gray-700">Message</label>
                            <textarea name="message" id="message" rows="4" required
                                      class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-green-500 focus:ring-green-500 form-input"></textarea>
                        </div>
                        <button type="submit" 
                                class="w-full flex justify-center py-3 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 transition-colors duration-200">
                            <i class="fas fa-paper-plane mr-2"></i> Send Message
                        </button>
                    </form>
                </div>

                <!-- Contact Information -->
                <div class="space-y-8">
                    <!-- Office Location -->
                    <div class="bg-white/90 backdrop-blur-sm rounded-lg shadow-xl p-8 card-hover">
                        <div class="flex items-center mb-4">
                            <div class="flex-shrink-0">
                                <i class="fas fa-map-marker-alt text-green-500 text-2xl"></i>
                            </div>
                            <div class="ml-4">
                                <h3 class="text-lg font-medium text-gray-900">Office Location</h3>
                                <p class="mt-1 text-gray-600">123 Tech Street, Innovation Hub<br>Silicon Valley, CA 94025</p>
                            </div>
                        </div>
                    </div>

                    <!-- Contact Details -->
                    <div class="bg-white/90 backdrop-blur-sm rounded-lg shadow-xl p-8 card-hover">
                        <div class="flex items-center mb-4">
                            <div class="flex-shrink-0">
                                <i class="fas fa-phone text-green-500 text-2xl"></i>
                            </div>
                            <div class="ml-4">
                                <h3 class="text-lg font-medium text-gray-900">Phone</h3>
                                <p class="mt-1 text-gray-600">+1 (555) 123-4567</p>
                            </div>
                        </div>
                    </div>

                    <!-- Team Contact -->
                    <div class="bg-white/90 backdrop-blur-sm rounded-lg shadow-xl p-8 card-hover">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Team Contact</h3>
                        <div class="space-y-4">
                            <div class="flex items-center">
                                <img src="https://avataaars.io/?avatarStyle=Circle&topType=LongHairStraight&accessoriesType=Blank&hairColor=Black&facialHairType=Blank&clotheType=BlazerShirt&eyeType=Default&eyebrowType=Default&mouthType=Smile&skinColor=Light" 
                                     alt="Diksha Rani" 
                                     class="w-12 h-12 rounded-full border-2 border-green-500">
                                <div class="ml-4">
                                    <p class="text-sm font-medium text-gray-900">Diksha Rani</p>
                                    <p class="text-sm text-gray-600">Team Leader</p>
                                    <a href="mailto:dilipdiksha2004@gmail.com" class="text-sm text-green-600 hover:text-green-700">
                                        dilipdiksha2004@gmail.com
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // User Menu Dropdown functionality
        const userMenuButton = document.getElementById('userMenuButton');
        const userMenuDropdown = document.getElementById('userMenuDropdown');
        const chevronIcon = userMenuButton?.querySelector('.fa-chevron-down');

        if (userMenuButton && userMenuDropdown && chevronIcon) {
            function toggleDropdown() {
                const isOpen = userMenuDropdown.classList.contains('block');
                userMenuDropdown.classList.toggle('hidden');
                userMenuDropdown.classList.toggle('block');
                
                if (!isOpen) {
                    userMenuDropdown.style.opacity = '0';
                    userMenuDropdown.style.transform = 'translateY(-10px)';
                    setTimeout(() => {
                        userMenuDropdown.style.opacity = '1';
                        userMenuDropdown.style.transform = 'translateY(0)';
                    }, 50);
                }
                
                chevronIcon.style.transform = isOpen ? 'rotate(0deg)' : 'rotate(180deg)';
                userMenuButton.classList.toggle('bg-gray-100');
            }

            userMenuButton.addEventListener('click', (e) => {
                e.stopPropagation();
                toggleDropdown();
            });

            document.addEventListener('click', (e) => {
                if (!userMenuButton.contains(e.target) && !userMenuDropdown.contains(e.target)) {
                    userMenuDropdown.classList.add('hidden');
                    userMenuDropdown.classList.remove('block');
                    chevronIcon.style.transform = 'rotate(0deg)';
                    userMenuButton.classList.remove('bg-gray-100');
                }
            });
        }
    </script>
</body>
</html> 