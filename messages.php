<?php
session_start();
require_once '../config/database.php';

// Check if user is logged in and is admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../login.php');
    exit();
}

// Handle message status updates
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['message_id'], $_POST['status'])) {
    try {
        $stmt = $pdo->prepare("UPDATE contact_messages SET status = ? WHERE message_id = ?");
        $stmt->execute([$_POST['status'], $_POST['message_id']]);
        $success = "Message status updated successfully!";
    } catch (PDOException $e) {
        $error = "Error updating message status: " . $e->getMessage();
    }
}

// Get all messages
try {
    $stmt = $pdo->query("SELECT * FROM contact_messages ORDER BY created_at DESC");
    $messages = $stmt->fetchAll();
} catch (PDOException $e) {
    $error = "Error fetching messages: " . $e->getMessage();
    $messages = [];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contact Messages - Admin Dashboard</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body class="bg-gray-100">
    <nav class="bg-white shadow-lg">
        <div class="max-w-7xl mx-auto px-4">
            <div class="flex justify-between h-16">
                <div class="flex">
                    <div class="flex-shrink-0 flex items-center">
                        <i class="fas fa-tachometer-alt text-green-500 text-2xl"></i>
                        <span class="ml-2 text-xl font-semibold">Admin Dashboard</span>
                    </div>
                </div>
                <div class="flex items-center">
                    <a href="../logout.php" class="text-gray-700 hover:text-gray-900 px-3 py-2 rounded-md text-sm font-medium">
                        <i class="fas fa-sign-out-alt"></i> Logout
                    </a>
                </div>
            </div>
        </div>
    </nav>

    <div class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
        <div class="px-4 py-6 sm:px-0">
            <h1 class="text-2xl font-semibold text-gray-900 mb-6">Contact Messages</h1>
            
            <?php if (isset($success)): ?>
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4">
                    <?php echo $success; ?>
                </div>
            <?php endif; ?>

            <?php if (isset($error)): ?>
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4">
                    <?php echo $error; ?>
                </div>
            <?php endif; ?>

            <div class="bg-white shadow overflow-hidden sm:rounded-md">
                <ul class="divide-y divide-gray-200">
                    <?php foreach ($messages as $msg): ?>
                        <li>
                            <div class="px-4 py-5 sm:px-6">
                                <div class="flex items-center justify-between">
                                    <div>
                                        <h3 class="text-lg font-medium text-gray-900">
                                            <?php echo htmlspecialchars($msg['subject']); ?>
                                        </h3>
                                        <p class="mt-1 text-sm text-gray-500">
                                            From: <?php echo htmlspecialchars($msg['name']); ?> 
                                            (<?php echo htmlspecialchars($msg['email']); ?>)
                                        </p>
                                    </div>
                                    <div class="flex items-center">
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                            <?php echo $msg['status'] === 'new' ? 'bg-yellow-100 text-yellow-800' : 
                                                    ($msg['status'] === 'read' ? 'bg-blue-100 text-blue-800' : 
                                                    'bg-green-100 text-green-800'); ?>">
                                            <?php echo ucfirst($msg['status']); ?>
                                        </span>
                                        <form method="POST" class="ml-3">
                                            <input type="hidden" name="message_id" value="<?php echo $msg['message_id']; ?>">
                                            <select name="status" onchange="this.form.submit()" 
                                                    class="block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-green-500 focus:border-green-500 sm:text-sm rounded-md">
                                                <option value="new" <?php echo $msg['status'] === 'new' ? 'selected' : ''; ?>>New</option>
                                                <option value="read" <?php echo $msg['status'] === 'read' ? 'selected' : ''; ?>>Read</option>
                                                <option value="replied" <?php echo $msg['status'] === 'replied' ? 'selected' : ''; ?>>Replied</option>
                                            </select>
                                        </form>
                                    </div>
                                </div>
                                <div class="mt-4 text-sm text-gray-900">
                                    <?php echo nl2br(htmlspecialchars($msg['message'])); ?>
                                </div>
                                <div class="mt-2 text-xs text-gray-500">
                                    Received: <?php echo date('F j, Y, g:i a', strtotime($msg['created_at'])); ?>
                                </div>
                            </div>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>
        </div>
    </div>
</body>
</html> 