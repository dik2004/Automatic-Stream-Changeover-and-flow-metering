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
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>About Us - Flow Metering System</title>
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
                        <a href="about.php" class="border-green-500 text-gray-900 inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium">
                            <i class="fas fa-users mr-2"></i> About Us
                        </a>
                        <a href="contact.php" class="border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700 inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium">
                            <i class="fas fa-envelope mr-2"></i> Contact
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
        <div class="bg-white overflow-hidden shadow rounded-lg">
            <div class="px-4 py-5 sm:p-6">
                <h2 class="text-3xl font-bold text-gray-900 mb-8 text-center">Our Team</h2>
                
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8">
                    <!-- Team Leader -->
                    <div class="bg-white p-6 rounded-lg shadow-md transform transition-all duration-300 hover:scale-105">
                        <div class="flex items-center justify-center mb-4">
                            <img src="https://avataaars.io/?avatarStyle=Circle&topType=LongHairStraight&accessoriesType=Blank&hairColor=Black&facialHairType=Blank&clotheType=BlazerShirt&eyeType=Default&eyebrowType=Default&mouthType=Smile&skinColor=Light" 
                                 alt="Diksha Rani" 
                                 class="w-32 h-32 rounded-full border-4 border-green-500 shadow-lg">
                        </div>
                        <h3 class="text-xl font-bold text-gray-800 mb-2 text-center">Diksha Rani</h3>
                        <p class="text-green-600 font-semibold text-center mb-2">Leader & Developer</p>
                        <p class="text-gray-600 text-center mb-4">Led the development of our Flow Metering System, leveraging expertise in system architecture and project management — including the design and implementation of the system's dashboard interface.</p>
                        <div class="flex justify-center space-x-4">
                            <a href="mailto:dilipdiksha2004@gmail.com" class="text-gray-600 hover:text-green-500 transition-colors duration-200">
                                <i class="fas fa-envelope text-xl"></i>
                            </a>
                        </div>
                    </div>

                    <!-- Team Member - Kushagra -->
                    <div class="bg-white p-6 rounded-lg shadow-md transform transition-all duration-300 hover:scale-105">
                        <div class="flex items-center justify-center mb-4">
                            <img src="https://avataaars.io/?avatarStyle=Circle&topType=ShortHairShortWaved&accessoriesType=Blank&hairColor=Black&facialHairType=Blank&clotheType=Hoodie&clotheColor=Gray&eyeType=Happy&eyebrowType=Default&mouthType=Smile&skinColor=Light" 
                                 alt="Kushagra" 
                                 class="w-32 h-32 rounded-full border-4 border-green-500 shadow-lg">
                        </div>
                        <h3 class="text-xl font-bold text-gray-800 mb-2 text-center">Kushagra</h3>
                        <p class="text-green-600 font-semibold text-center mb-2">Developer</p>
                        <p class="text-gray-600 text-center mb-4">Specializing in system architecture and application design, with hands-on experience in building key system components — including the design and development of a secure, user-friendly registration page.</p>
                        <div class="flex justify-center space-x-4">
                            <a href="mailto:kushagrachoudhary76@gmail.com" class="text-gray-600 hover:text-green-500 transition-colors duration-200">
                                <i class="fas fa-envelope text-xl"></i>
                            </a>
                        </div>
                    </div>

                    <!-- Team Member - Chandrapal -->
                    <div class="bg-white p-6 rounded-lg shadow-md transform transition-all duration-300 hover:scale-105">
                        <div class="flex items-center justify-center mb-4">
                            <img src="https://avataaars.io/?avatarStyle=Circle&topType=ShortHairShortFlat&accessoriesType=Blank&hairColor=Black&facialHairType=BeardLight&clotheType=CollarSweater&clotheColor=Blue&eyeType=Default&eyebrowType=Default&mouthType=Smile&skinColor=Light" 
                                 alt="Chandrapal" 
                                 class="w-32 h-32 rounded-full border-4 border-green-500 shadow-lg">
                        </div>
                        <h3 class="text-xl font-bold text-gray-800 mb-2 text-center">Chandrapal</h3>
                        <p class="text-green-600 font-semibold text-center mb-2">Developer</p>
                        <p class="text-gray-600 text-center mb-4">Focused on interface design and user experience, contributing significantly to the project’s visual identity — including the creation and implementation of the project's About Us page.</p>
                        <div class="flex justify-center space-x-4">
                            <a href="mailto:chandrapaljadon1124@gmail.com" class="text-gray-600 hover:text-green-500 transition-colors duration-200">
                                <i class="fas fa-envelope text-xl"></i>
                            </a>
                        </div>
                    </div>

                    <!-- Team Member - Sai -->
                    <div class="bg-white p-6 rounded-lg shadow-md transform transition-all duration-300 hover:scale-105">
                        <div class="flex items-center justify-center mb-4">
                            <img src="https://avataaars.io/?avatarStyle=Circle&topType=ShortHairShortRound&accessoriesType=Blank&hairColor=Black&facialHairType=Blank&clotheType=ShirtCrewNeck&clotheColor=Red&eyeType=Happy&eyebrowType=Default&mouthType=Smile&skinColor=Light" 
                                 alt="Sai" 
                                 class="w-32 h-32 rounded-full border-4 border-green-500 shadow-lg">
                        </div>
                        <h3 class="text-xl font-bold text-gray-800 mb-2 text-center"> Dasetti Sai Kumar</h3>
                        <p class="text-green-600 font-semibold text-center mb-2">Developer</p>
                        <p class="text-gray-600 text-center mb-4">Played a key role in enhancing user access and security features — including the design and development of the project's login and logout functionalities.</p>
                        <div class="flex justify-center space-x-4">
                            <a href="mailto:dasettisaikumar@gmail.com" class="text-gray-600 hover:text-green-500 transition-colors duration-200">
                                <i class="fas fa-envelope text-xl"></i>
                            </a>
                        </div>
                    </div>
                </div>

                <!-- About Project -->
                <div class="mt-12 text-center max-w-3xl mx-auto">
                    <h3 class="text-2xl font-semibold text-gray-900 mb-4">About Our Project</h3>
                    <p class="text-gray-600 mb-6">
                        The Flow Metering System is an advanced solution for monitoring and managing fluid flow in industrial applications. 
                        Our system provides real-time monitoring of flow rates, pressure, and temperature, with automatic stream changeover 
                        capabilities to ensure optimal performance and safety.
                    </p>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <div class="p-4">
                            <i class="fas fa-tachometer-alt text-3xl text-green-500 mb-3"></i>
                            <h4 class="font-semibold mb-2">Real-time Monitoring</h4>
                            <p class="text-sm text-gray-600">Continuous tracking of flow parameters with instant updates</p>
                        </div>
                        <div class="p-4">
                            <i class="fas fa-exchange-alt text-3xl text-green-500 mb-3"></i>
                            <h4 class="font-semibold mb-2">Auto Changeover</h4>
                            <p class="text-sm text-gray-600">Intelligent stream switching based on configurable thresholds</p>
                        </div>
                        <div class="p-4">
                            <i class="fas fa-shield-alt text-3xl text-green-500 mb-3"></i>
                            <h4 class="font-semibold mb-2">Safety Controls</h4>
                            <p class="text-sm text-gray-600">Built-in safety measures and alerts for critical conditions</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // User menu dropdown functionality
        const userMenuButton = document.getElementById('userMenuButton');
        const userMenuDropdown = document.getElementById('userMenuDropdown');
        const chevronIcon = userMenuButton.querySelector('.fa-chevron-down');

        userMenuButton.addEventListener('click', () => {
            userMenuDropdown.classList.toggle('hidden');
            chevronIcon.style.transform = userMenuDropdown.classList.contains('hidden') ? 'rotate(0deg)' : 'rotate(180deg)';
        });

        // Close dropdown when clicking outside
        document.addEventListener('click', (event) => {
            if (!userMenuButton.contains(event.target) && !userMenuDropdown.contains(event.target)) {
                userMenuDropdown.classList.add('hidden');
                chevronIcon.style.transform = 'rotate(0deg)';
            }
        });
    </script>
</body>
</html> 