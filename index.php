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

// Get current stream status
$stmt = $pdo->query("SELECT * FROM streams ORDER BY stream_id");
$streams = $stmt->fetchAll();

// Get latest measurements
$stmt = $pdo->query("SELECT m.*, s.name as stream_name 
                     FROM measurements m 
                     JOIN streams s ON m.stream_id = s.stream_id 
                     ORDER BY m.timestamp DESC LIMIT 5");
$latest_measurements = $stmt->fetchAll();

// Get or initialize system settings
try {
    // Get settings
    $stmt = $pdo->query("SELECT setting_key, setting_value FROM settings");
    $settings = [];
    while ($row = $stmt->fetch()) {
        $settings[$row['setting_key']] = $row['setting_value'];
    }

    if (empty($settings)) {
        // If no settings exist, create default settings
        $stmt = $pdo->prepare("INSERT INTO settings (setting_key, setting_value) VALUES 
            ('changeover_threshold', 100.00),
            ('pressure_limit', 10.00),
            ('temperature_limit', 50.00)");
        $stmt->execute();
        
        // Get the newly created settings
        $stmt = $pdo->query("SELECT setting_key, setting_value FROM settings");
        while ($row = $stmt->fetch()) {
            $settings[$row['setting_key']] = $row['setting_value'];
        }
    }
} catch (PDOException $e) {
    $error = "Error initializing settings: " . $e->getMessage();
    $settings = [
        'changeover_threshold' => 100.00,
        'pressure_limit' => 10.00,
        'temperature_limit' => 50.00
    ];
}

// Initialize default values if not set
foreach ($streams as &$stream) {
    if (!isset($stream['current_flow_rate'])) $stream['current_flow_rate'] = 0.00;
    if (!isset($stream['current_pressure'])) $stream['current_pressure'] = 0.00;
    if (!isset($stream['current_temperature'])) $stream['current_temperature'] = 0.00;
}
unset($stream); // Break the reference

// Handle settings form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Update each setting individually
        $stmt = $pdo->prepare("UPDATE settings SET setting_value = ? WHERE setting_key = ?");
        
        $stmt->execute([floatval($_POST['changeover_threshold']), 'changeover_threshold']);
        $stmt->execute([floatval($_POST['pressure_limit']), 'pressure_limit']);
        $stmt->execute([floatval($_POST['temperature_limit']), 'temperature_limit']);

        // Simulate random values for streams
        foreach ($streams as $stream) {
            $flow_rate = rand(0, 200) / 2; // Random value between 0 and 100
            $pressure = rand(0, 200) / 10; // Random value between 0 and 20
            $temperature = rand(200, 800) / 10; // Random value between 20 and 80
            
            $stmt = $pdo->prepare("UPDATE streams SET 
                current_flow_rate = ?,
                current_pressure = ?,
                current_temperature = ?
                WHERE stream_id = ?");
            $stmt->execute([$flow_rate, $pressure, $temperature, $stream['stream_id']]);
        }

        $success = "Settings updated successfully!";
        
        // Refresh settings and streams
        $stmt = $pdo->query("SELECT setting_key, setting_value FROM settings");
        $settings = [];
        while ($row = $stmt->fetch()) {
            $settings[$row['setting_key']] = $row['setting_value'];
        }

        // Refresh streams data
        $stmt = $pdo->query("SELECT * FROM streams ORDER BY stream_id");
        $streams = $stmt->fetchAll();

        // Update stream statuses based on new settings
        foreach ($streams as $stream) {
            if ($stream['current_flow_rate'] < $settings['changeover_threshold']) {
                $stmt = $pdo->prepare("UPDATE streams SET status = 'standby' WHERE stream_id = ?");
                $stmt->execute([$stream['stream_id']]);
            }
        }

        // Record measurement
        $stmt = $pdo->prepare("INSERT INTO measurements (stream_id, flow_rate, pressure, temperature) 
                              SELECT stream_id, current_flow_rate, current_pressure, current_temperature 
                              FROM streams");
        $stmt->execute();

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
    <title>Dashboard - Flow Metering System</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="css/tailwind.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .animated-background {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: -1;
            background: linear-gradient(132deg, #001f3f, #004d40, #006064, #1a237e);
            background-size: 400% 400%;
            animation: gradient 15s ease infinite;
            opacity: 1;
            transition: opacity 0.5s ease;
        }

        .circuit-background {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: -1;
            background-color: #0a192f;
            background-image: 
                radial-gradient(circle at 50% 50%, rgba(37, 99, 235, 0.1) 0%, transparent 100%),
                linear-gradient(90deg, rgba(37, 99, 235, 0.05) 1px, transparent 1px),
                linear-gradient(0deg, rgba(37, 99, 235, 0.05) 1px, transparent 1px),
                linear-gradient(45deg, rgba(37, 99, 235, 0.05) 1px, transparent 1px);
            background-size: 100% 100%, 20px 20px, 20px 20px, 40px 40px;
            background-position: center center;
            opacity: 0;
            transition: opacity 0.5s ease;
        }

        .circuit-background::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: repeating-linear-gradient(
                45deg,
                rgba(37, 99, 235, 0.1),
                rgba(37, 99, 235, 0.1) 2px,
                transparent 2px,
                transparent 10px
            );
            animation: circuit-flow 20s linear infinite;
        }

        .circuit-background.active {
            opacity: 1;
        }

        .animated-background.inactive {
            opacity: 0;
        }

        @keyframes circuit-flow {
            0% {
                background-position: 0 0;
            }
            100% {
                background-position: 100px 100px;
            }
        }

        .theme-toggle-container {
            position: fixed;
            bottom: 20px;
            right: 20px;
            z-index: 50;
            display: flex;
            gap: 10px;
        }

        .theme-toggle {
            position: relative;
            padding: 10px;
            border-radius: 50%;
            cursor: pointer;
            background: rgba(255, 255, 255, 0.9);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
        }

        .theme-toggle.active {
            background: rgba(14, 165, 233, 0.9);
            color: white;
        }

        .animated-particles {
            position: absolute;
            width: 100%;
            height: 100%;
            overflow: hidden;
            background: radial-gradient(circle at center, transparent 0%, rgba(0, 0, 0, 0.5) 100%);
        }

        .particle {
            position: absolute;
            width: 3px;
            height: 3px;
            background: linear-gradient(to right, rgba(255, 255, 255, 0.8), rgba(255, 255, 255, 0));
            border-radius: 50%;
            filter: blur(1px);
            animation: particleFloat 20s infinite linear;
        }

        .particle::after {
            content: '';
            position: absolute;
            width: 100px;
            height: 1px;
            background: linear-gradient(to right, rgba(255, 255, 255, 0.3), transparent);
            transform: translateX(-100%);
        }

        .hexagon-grid {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-image: 
                linear-gradient(60deg, rgba(255, 255, 255, 0.05) 25%, transparent 25.5%),
                linear-gradient(-60deg, rgba(255, 255, 255, 0.05) 25%, transparent 25.5%),
                linear-gradient(60deg, transparent 75%, rgba(255, 255, 255, 0.05) 75.5%),
                linear-gradient(-60deg, transparent 75%, rgba(255, 255, 255, 0.05) 75.5%);
            background-size: 50px 87px;
            background-position: 0 0, 0 0, 25px 43.5px, 25px 43.5px;
            animation: moveHexagons 20s linear infinite;
        }

        @keyframes gradient {
            0% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
            100% { background-position: 0% 50%; }
        }

        @keyframes particleFloat {
            0% {
                transform: translateY(0) translateX(0) rotate(0deg);
                opacity: 1;
            }
            50% {
                opacity: 0.5;
            }
            100% {
                transform: translateY(-1000px) translateX(500px) rotate(360deg);
                opacity: 0;
            }
        }

        @keyframes moveHexagons {
            0% { background-position: 0 0, 0 0, 25px 43.5px, 25px 43.5px; }
            100% { background-position: 500px 0, 500px 0, 525px 43.5px, 525px 43.5px; }
        }

        .glass-card {
            background: rgba(255, 255, 255, 0.9);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            transition: all 0.3s ease;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1), 
                       0 1px 3px rgba(0, 0, 0, 0.08);
        }

        .glass-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.2), 
                       0 10px 10px -5px rgba(0, 0, 0, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.3);
        }

        .floating {
            animation: float 6s ease-in-out infinite;
        }

        @keyframes float {
            0% { transform: translateY(0px); }
            50% { transform: translateY(-20px); }
            100% { transform: translateY(0px); }
        }

        .nav-glass {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-bottom: 1px solid rgba(255, 255, 255, 0.3);
        }

        .fluid-background {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: -1;
            background: #0c4a6e;
            opacity: 0;
            transition: opacity 0.5s ease;
        }

        .fluid-wave {
            position: absolute;
            width: 100%;
            height: 100%;
            opacity: 0.5;
            background: linear-gradient(45deg, 
                rgba(14, 165, 233, 0.2),
                rgba(56, 189, 248, 0.3),
                rgba(125, 211, 252, 0.2)
            );
        }

        .fluid-wave:nth-child(1) {
            background-image: repeating-linear-gradient(
                45deg,
                transparent,
                transparent 50px,
                rgba(14, 165, 233, 0.1) 50px,
                rgba(14, 165, 233, 0.1) 100px
            );
            animation: wave 15s linear infinite;
        }

        .fluid-wave:nth-child(2) {
            background-image: repeating-linear-gradient(
                -45deg,
                transparent,
                transparent 50px,
                rgba(56, 189, 248, 0.1) 50px,
                rgba(56, 189, 248, 0.1) 100px
            );
            animation: wave 12s linear infinite;
        }

        .fluid-wave:nth-child(3) {
            background-image: 
                radial-gradient(circle at 50% 50%, 
                    rgba(125, 211, 252, 0.2) 0%, 
                    transparent 50%),
                repeating-linear-gradient(
                    90deg,
                    transparent,
                    transparent 30px,
                    rgba(125, 211, 252, 0.1) 30px,
                    rgba(125, 211, 252, 0.1) 60px
                );
            animation: wave 10s linear infinite;
        }

        .fluid-bubble {
            position: absolute;
            background: radial-gradient(
                circle at center,
                rgba(255, 255, 255, 0.8),
                rgba(255, 255, 255, 0.1)
            );
            border-radius: 50%;
            animation: bubble-float linear infinite;
        }

        .fluid-background.active {
            opacity: 1;
        }

        @keyframes wave {
            0% {
                background-position: 0 0;
            }
            100% {
                background-position: 100px 100px;
            }
        }

        @keyframes bubble-float {
            0% {
                transform: translateY(100vh) scale(0);
                opacity: 0.8;
            }
            100% {
                transform: translateY(-100px) scale(1.5);
                opacity: 0;
            }
        }
    </style>
</head>
<body class="bg-gray-900 text-black">
    <div class="animated-background"></div>
    <div class="circuit-background"></div>
    <div class="fluid-background">
        <div class="fluid-wave"></div>
        <div class="fluid-wave"></div>
        <div class="fluid-wave"></div>
    </div>
    <div class="hexagon-grid"></div>
    <div class="animated-particles" id="particles"></div>

    <div class="theme-toggle-container">
        <button class="theme-toggle active" id="gradientTheme" title="Gradient Theme">
            <i class="fas fa-palette text-xl"></i>
        </button>
        <button class="theme-toggle" id="circuitTheme" title="Circuit Theme">
            <i class="fas fa-microchip text-xl"></i>
        </button>
        <button class="theme-toggle" id="fluidTheme" title="Fluid Theme">
            <i class="fas fa-water text-xl"></i>
        </button>
    </div>

    <!-- Navigation -->
    <nav class="nav-glass shadow-lg sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-4">
            <div class="flex justify-between h-16">
                <div class="flex">
                    <div class="flex-shrink-0 flex items-center">
                        <i class="fas fa-tachometer-alt text-green-500 text-2xl"></i>
                        <span class="ml-2 text-xl font-semibold text-gray-800">Flow Metering</span>
                    </div>
                    <div class="hidden sm:ml-6 sm:flex sm:space-x-8">
                        <a href="index.php" class="border-green-500 text-gray-900 inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium">
                            <i class="fas fa-home mr-2"></i> Dashboard
                        </a>
                        <a href="about.php" class="border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700 inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium">
                            <i class="fas fa-users mr-2"></i> About Us
                        </a>
                        <a href="contact.php" class="border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700 inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium">
                            <i class="fas fa-envelope mr-2"></i> Contact
                        </a>
                    </div>
                    <!-- Mobile menu button -->
                    <div class="flex items-center sm:hidden">
                        <button type="button" class="inline-flex items-center justify-center p-2 rounded-md text-gray-400 hover:text-gray-500 hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-inset focus:ring-green-500" id="mobile-menu-button">
                            <span class="sr-only">Open main menu</span>
                            <i class="fas fa-bars text-xl"></i>
                        </button>
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
        <!-- Mobile menu -->
        <div id="mobile-menu" class="sm:hidden hidden">
            <div class="pt-2 pb-3 space-y-1">
                <a href="index.php" class="bg-green-50 border-green-500 text-green-700 block pl-3 pr-4 py-2 border-l-4 text-base font-medium">
                    <i class="fas fa-home mr-2"></i> Dashboard
                </a>
                <a href="about.php" class="border-transparent text-gray-500 hover:bg-gray-50 hover:border-gray-300 hover:text-gray-700 block pl-3 pr-4 py-2 border-l-4 text-base font-medium">
                    <i class="fas fa-users mr-2"></i> About Us
                </a>
                <a href="contact.php" class="border-transparent text-gray-500 hover:bg-gray-50 hover:border-gray-300 hover:text-gray-700 block pl-3 pr-4 py-2 border-l-4 text-base font-medium">
                    <i class="fas fa-envelope mr-2"></i> Contact
                </a>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="min-h-screen py-6 relative z-10">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <!-- Dashboard Header -->
            <div class="text-center mb-8">
                <h1 class="text-4xl font-bold text-white mb-4 floating">Flow Metering Dashboard</h1>
                <p class="text-xl text-gray-300">Monitor and control your flow metering system</p>
            </div>

            <!-- Stream Status Section -->
            <div class="mb-8">
                <h2 class="text-2xl font-semibold text-white mb-4">Stream Status</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <?php foreach ($streams as $stream): ?>
                    <div class="glass-card rounded-lg p-6">
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="text-lg font-medium text-gray-900"><?php echo htmlspecialchars($stream['name']); ?></h3>
                            <span class="px-3 py-1 rounded-full text-sm font-medium <?php echo $stream['status'] === 'active' ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800'; ?>">
                                <?php echo ucfirst(htmlspecialchars($stream['status'])); ?>
                            </span>
                        </div>
                        <div class="space-y-3">
                            <div class="flex justify-between">
                                <span class="text-gray-600">Flow Rate:</span>
                                <span class="text-gray-900 font-medium"><?php echo number_format($stream['current_flow_rate'], 2); ?> m³/h</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600">Pressure:</span>
                                <span class="text-gray-900 font-medium"><?php echo number_format($stream['current_pressure'], 2); ?> bar</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600">Temperature:</span>
                                <span class="text-gray-900 font-medium"><?php echo number_format($stream['current_temperature'], 2); ?> °C</span>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Settings Section -->
            <div class="mb-8">
                <h2 class="text-2xl font-semibold text-white mb-4">System Settings</h2>
                <div class="glass-card rounded-lg p-6">
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

                    <form method="POST" class="space-y-6">
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                            <!-- Changeover Threshold -->
                            <div class="relative">
                                <label for="changeover_threshold" class="block text-sm font-medium text-gray-700 mb-1">
                                    Changeover Threshold (m³/h)
                                </label>
                                <div class="mt-1 relative rounded-md shadow-sm">
                                    <input type="number" 
                                           name="changeover_threshold" 
                                           id="changeover_threshold" 
                                           step="0.01" 
                                           value="<?php echo htmlspecialchars($settings['changeover_threshold'] ?? ''); ?>"
                                           class="focus:ring-green-500 focus:border-green-500 block w-full pl-4 pr-16 py-2 sm:text-sm border-gray-300 rounded-md"
                                           required>
                                    <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                                        <span class="text-gray-500 sm:text-sm">m³/h</span>
                                    </div>
                                </div>
                            </div>

                            <!-- Pressure Limit -->
                            <div class="relative">
                                <label for="pressure_limit" class="block text-sm font-medium text-gray-700 mb-1">
                                    Pressure Limit (bar)
                                </label>
                                <div class="mt-1 relative rounded-md shadow-sm">
                                    <input type="number" 
                                           name="pressure_limit" 
                                           id="pressure_limit" 
                                           step="0.1" 
                                           value="<?php echo htmlspecialchars($settings['pressure_limit'] ?? ''); ?>"
                                           class="focus:ring-green-500 focus:border-green-500 block w-full pl-4 pr-16 py-2 sm:text-sm border-gray-300 rounded-md"
                                           required>
                                    <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                                        <span class="text-gray-500 sm:text-sm">bar</span>
                                    </div>
                                </div>
                            </div>

                            <!-- Temperature Limit -->
                            <div class="relative">
                                <label for="temperature_limit" class="block text-sm font-medium text-gray-700 mb-1">
                                    Temperature Limit (°C)
                                </label>
                                <div class="mt-1 relative rounded-md shadow-sm">
                                    <input type="number" 
                                           name="temperature_limit" 
                                           id="temperature_limit" 
                                           step="0.1" 
                                           value="<?php echo htmlspecialchars($settings['temperature_limit'] ?? ''); ?>"
                                           class="focus:ring-green-500 focus:border-green-500 block w-full pl-4 pr-16 py-2 sm:text-sm border-gray-300 rounded-md"
                                           required>
                                    <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                                        <span class="text-gray-500 sm:text-sm">°C</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="flex justify-end">
                            <button type="submit" 
                                    class="bg-green-600 text-white px-4 py-2 rounded-md hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 flex items-center">
                                <i class="fas fa-save mr-2"></i>
                                Save Settings
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Control Panel -->
            <div>
                <h2 class="text-2xl font-semibold text-white mb-4">Control Panel</h2>
                <div class="glass-card rounded-lg p-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="bg-gray-50 rounded-lg p-4">
                            <h4 class="text-md font-medium text-gray-900 mb-2">Manual Override</h4>
                            <div class="space-y-2">
                                <?php foreach ($streams as $stream): ?>
                                    <button onclick="manualOverride(<?php echo $stream['stream_id']; ?>)" 
                                            class="w-full bg-white border border-gray-300 rounded-md px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                                        <i class="fas fa-power-off mr-2"></i> Activate <?php echo htmlspecialchars($stream['name']); ?>
                                    </button>
                                <?php endforeach; ?>
                            </div>
                        </div>
                        <div class="bg-gray-50 rounded-lg p-4">
                            <h4 class="text-md font-medium text-gray-900 mb-2">Automatic Mode</h4>
                            <button onclick="autoMode()" 
                                    class="w-full bg-green-600 border border-transparent rounded-md px-4 py-2 text-sm font-medium text-white hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                                <i class="fas fa-robot mr-2"></i> Enable Auto Mode
                            </button>
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
        const chevronIcon = userMenuButton.querySelector('.fa-chevron-down');
        const mobileMenuButton = document.getElementById('mobile-menu-button');
        const mobileMenu = document.getElementById('mobile-menu');

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

        function toggleMobileMenu() {
            mobileMenu.classList.toggle('hidden');
            mobileMenu.classList.toggle('block');
        }

        userMenuButton.addEventListener('click', (e) => {
            e.stopPropagation();
            toggleDropdown();
        });

        mobileMenuButton.addEventListener('click', (e) => {
            e.stopPropagation();
            toggleMobileMenu();
        });

        document.addEventListener('click', (e) => {
            if (!userMenuButton.contains(e.target) && !userMenuDropdown.contains(e.target)) {
                userMenuDropdown.classList.add('hidden');
                userMenuDropdown.classList.remove('block');
                chevronIcon.style.transform = 'rotate(0deg)';
                userMenuButton.classList.remove('bg-gray-100');
            }
            if (!mobileMenuButton.contains(e.target) && !mobileMenu.contains(e.target)) {
                mobileMenu.classList.add('hidden');
                mobileMenu.classList.remove('block');
            }
        });

        function manualOverride(streamId) {
            fetch('api/stream_control.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    action: 'manual_override',
                    stream_id: streamId
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    location.reload();
                } else {
                    alert(data.message || 'Error occurred');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error occurred');
            });
        }

        function autoMode() {
            fetch('api/stream_control.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    action: 'auto_mode'
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    location.reload();
                } else {
                    alert(data.message || 'Error occurred');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error occurred');
            });
        }

        // Add particle animation
        function createParticles() {
            const particlesContainer = document.getElementById('particles');
            const numberOfParticles = 50;

            for (let i = 0; i < numberOfParticles; i++) {
                const particle = document.createElement('div');
                particle.className = 'particle';
                
                // Random position
                particle.style.left = Math.random() * 100 + '%';
                particle.style.top = Math.random() * 100 + '%';
                
                // Random animation
                const duration = 15 + Math.random() * 15;
                const delay = Math.random() * 5;
                
                particle.style.animation = `float ${duration}s ${delay}s infinite`;
                
                particlesContainer.appendChild(particle);
            }
        }

        // Initialize particles
        createParticles();

        const gradientTheme = document.getElementById('gradientTheme');
        const circuitTheme = document.getElementById('circuitTheme');
        const fluidTheme = document.getElementById('fluidTheme');
        const animatedBg = document.querySelector('.animated-background');
        const circuitBg = document.querySelector('.circuit-background');
        const fluidBg = document.querySelector('.fluid-background');

        function createBubbles() {
            const fluidBackground = document.querySelector('.fluid-background');
            const numberOfBubbles = 20;

            for (let i = 0; i < numberOfBubbles; i++) {
                const bubble = document.createElement('div');
                bubble.className = 'fluid-bubble';
                
                // Random size between 10px and 30px
                const size = 10 + Math.random() * 20;
                bubble.style.width = `${size}px`;
                bubble.style.height = `${size}px`;
                
                // Random horizontal position
                bubble.style.left = `${Math.random() * 100}%`;
                
                // Random animation duration between 4 and 8 seconds
                const duration = 4 + Math.random() * 4;
                bubble.style.animation = `bubble-float ${duration}s ${Math.random() * 4}s infinite`;
                
                fluidBackground.appendChild(bubble);
            }
        }

        function setActiveTheme(theme) {
            // Remove active class from all themes
            [gradientTheme, circuitTheme, fluidTheme].forEach(btn => btn.classList.remove('active'));
            
            // Hide all backgrounds
            animatedBg.classList.add('inactive');
            circuitBg.classList.remove('active');
            fluidBg.classList.remove('active');
            
            // Activate selected theme
            switch(theme) {
                case 'gradient':
                    gradientTheme.classList.add('active');
                    animatedBg.classList.remove('inactive');
                    break;
                case 'circuit':
                    circuitTheme.classList.add('active');
                    circuitBg.classList.add('active');
                    break;
                case 'fluid':
                    fluidTheme.classList.add('active');
                    fluidBg.classList.add('active');
                    break;
            }
        }

        gradientTheme.addEventListener('click', () => setActiveTheme('gradient'));
        circuitTheme.addEventListener('click', () => setActiveTheme('circuit'));
        fluidTheme.addEventListener('click', () => setActiveTheme('fluid'));

        // Initialize bubbles
        createBubbles();
    </script>
</body>
</html> 