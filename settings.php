<?php
session_start();
require_once 'config/database.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

// Get user information
$stmt = $pdo->prepare("SELECT username, role FROM users WHERE user_id = ?");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch();

// Get current settings
$stmt = $pdo->query("SELECT * FROM settings");
$settings = $stmt->fetch();

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $stmt = $pdo->prepare("UPDATE settings SET 
            changeover_threshold = ?,
            pressure_limit = ?,
            temperature_limit = ?,
            updated_at = NOW()
            WHERE id = ?");
        
        $stmt->execute([
            $_POST['changeover_threshold'],
            $_POST['pressure_limit'],
            $_POST['temperature_limit'],
            $settings['id']
        ]);

        $success = "Settings updated successfully!";
        
        // Refresh settings
        $stmt = $pdo->query("SELECT * FROM settings");
        $settings = $stmt->fetch();
    } catch (PDOException $e) {
        $error = "Error updating settings: " . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Settings - Flow Metering System</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="css/tailwind.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body class="bg-gray-100">
    <!-- Navigation -->
    <nav class="bg-white shadow-lg">
        <div class="max-w-7xl mx-auto px-4">
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
                        <a href="streams.php" class="border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700 inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium">
                            <i class="fas fa-stream mr-2"></i> Streams
                        </a>
                        <a href="settings.php" class="border-green-500 text-gray-900 inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium">
                            <i class="fas fa-cog mr-2"></i> Settings
                        </a>
                    </div>
                </div>
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
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
        <!-- Settings Form -->
        <div class="bg-white overflow-hidden shadow rounded-lg">
            <div class="px-4 py-5 sm:p-6">
                <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4">
                    <i class="fas fa-cog mr-2"></i> System Settings
                </h3>

                <?php if (isset($success)): ?>
                    <div class="mb-4 bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded relative" role="alert">
                        <i class="fas fa-check-circle mr-2"></i>
                        <?php echo $success; ?>
                    </div>
                <?php endif; ?>

                <?php if (isset($error)): ?>
                    <div class="mb-4 bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded relative" role="alert">
                        <i class="fas fa-exclamation-circle mr-2"></i>
                        <?php echo $error; ?>
                    </div>
                <?php endif; ?>

                <form method="POST" class="space-y-8 max-w-4xl mx-auto">
                    <div class="grid grid-cols-1 gap-8">
                        <!-- Changeover Threshold -->
                        <div class="relative">
                            <label for="changeover_threshold" class="block text-sm font-medium text-gray-700 mb-2">
                                Changeover Threshold (m³/h)
                            </label>
                            <div class="mt-1 relative rounded-md shadow-sm">
                                <input type="number" 
                                       name="changeover_threshold" 
                                       id="changeover_threshold" 
                                       step="0.01" 
                                       value="<?php echo htmlspecialchars($settings['changeover_threshold'] ?? ''); ?>"
                                       class="focus:ring-green-500 focus:border-green-500 block w-full pl-4 pr-16 py-3 sm:text-lg border-gray-300 rounded-md"
                                       required>
                                <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                                    <span class="text-gray-500 sm:text-lg">m³/h</span>
                                </div>
                            </div>
                            <p class="mt-2 text-sm text-gray-500">Flow rate threshold for automatic stream changeover</p>
                        </div>

                        <!-- Pressure Limit -->
                        <div class="relative">
                            <label for="pressure_limit" class="block text-sm font-medium text-gray-700 mb-2">
                                Pressure Limit (bar)
                            </label>
                            <div class="mt-1 relative rounded-md shadow-sm">
                                <input type="number" 
                                       name="pressure_limit" 
                                       id="pressure_limit" 
                                       step="0.1" 
                                       value="<?php echo htmlspecialchars($settings['pressure_limit'] ?? ''); ?>"
                                       class="focus:ring-green-500 focus:border-green-500 block w-full pl-4 pr-16 py-3 sm:text-lg border-gray-300 rounded-md"
                                       required>
                                <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                                    <span class="text-gray-500 sm:text-lg">bar</span>
                                </div>
                            </div>
                            <p class="mt-2 text-sm text-gray-500">Maximum allowable pressure for safe operation</p>
                        </div>

                        <!-- Temperature Limit -->
                        <div class="relative">
                            <label for="temperature_limit" class="block text-sm font-medium text-gray-700 mb-2">
                                Temperature Limit (°C)
                            </label>
                            <div class="mt-1 relative rounded-md shadow-sm">
                                <input type="number" 
                                       name="temperature_limit" 
                                       id="temperature_limit" 
                                       step="0.1" 
                                       value="<?php echo htmlspecialchars($settings['temperature_limit'] ?? ''); ?>"
                                       class="focus:ring-green-500 focus:border-green-500 block w-full pl-4 pr-16 py-3 sm:text-lg border-gray-300 rounded-md"
                                       required>
                                <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                                    <span class="text-gray-500 sm:text-lg">°C</span>
                                </div>
                            </div>
                            <p class="mt-2 text-sm text-gray-500">Maximum allowable temperature for safe operation</p>
                        </div>
                    </div>

                    <div class="flex justify-end">
                        <button type="submit" 
                                class="bg-green-600 text-white px-6 py-3 rounded-md hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 flex items-center text-lg">
                            <i class="fas fa-save mr-2"></i>
                            Save Settings
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        // User Menu Dropdown functionality
        const userMenuButton = document.getElementById('userMenuButton');
        const userMenuDropdown = document.getElementById('userMenuDropdown');
        const chevronIcon = userMenuButton.querySelector('.fa-chevron-down');

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
    </script>
</body>
</html> 