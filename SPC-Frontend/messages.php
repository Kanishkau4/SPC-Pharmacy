<?php
session_start();
if (!isset($_SESSION['EMAIL'])) {
    header("Location: stafflogin.php");
    exit();
}

// Fetch messages
$messages = [];
$apiUrl = "http://localhost:5268/api/Reply/GetMessages";
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $apiUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPGET, true);
$response = curl_exec($ch);
curl_close($ch);

if ($response) {
    $messages = json_decode($response, true);
}

// Handle reply submission
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['reply'])) {
    $replyData = [
        "messageId" => $_POST['message_id'],
        "replyText" => $_POST['reply_text'],
        "replierEmail" => $_SESSION['EMAIL']
    ];

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, "http://localhost:5268/api/Reply/ReplyToMessage");
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($replyData));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ["Content-Type: application/json"]);

    $response = curl_exec($ch);
    $success = !curl_errno($ch);
    curl_close($ch);

    // Redirect to refresh the page
    header("Location: messages.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Messages - SPC Staff Portal</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
                * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        :root {
            --primary-color: #1a5f7a;
            --secondary-color: #2d9cdb;
            --accent-color: #27ae60;
            --light-gray: #f7f9fc;
            --dark-gray: #2c3e50;
        }

        body {
            background-color: var(--light-gray);
            min-height: 100vh;
        }

        /* Header Styles */
        header {
            background-color: var(--primary-color);
            padding: 1rem;
            position: fixed;
            width: 100%;
            z-index: 100;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        nav {
            max-width: 1400px;
            margin: 0 auto;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .logo {
            color: white;
            font-size: 1.8rem;
            font-weight: bold;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .logo i {
            color: var(--accent-color);
        }

        .nav-links {
            display: flex;
            gap: 2.5rem;
        }

        .nav-links a {
            color: white;
            text-decoration: none;
            font-weight: 500;
            transition: all 0.3s;
        }

        /* Main Content Styles */
        .main-content {
            padding: 8rem 2rem 4rem;
            max-width: 1400px;
            margin: 0 auto;
        }

        .dashboard-header {
            margin-bottom: 2rem;
        }

        .dashboard-header h1 {
            color: var(--primary-color);
            font-size: 2.5rem;
            margin-bottom: 0.5rem;
        }

        .dashboard-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 2rem;
        }

        /* Form Styles */
        .form-container {
            background-color: white;
            padding: 2rem;
            border-radius: 15px;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.1);
        }

        .form-container h2 {
            color: var(--primary-color);
            margin-bottom: 1.5rem;
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            color: var(--dark-gray);
            font-weight: 500;
        }

        .form-group input,
        .form-group select {
            width: 100%;
            padding: 0.8rem;
            border: 1px solid #ddd;
            border-radius: 8px;
            font-size: 1rem;
            transition: all 0.3s;
        }

        .form-group input:focus,
        .form-group select:focus {
            outline: none;
            border-color: var(--secondary-color);
            box-shadow: 0 0 0 2px rgba(45, 156, 219, 0.2);
        }

        .submit-btn {
            background-color: var(--accent-color);
            color: white;
            padding: 1rem 2rem;
            border: none;
            border-radius: 8px;
            font-size: 1.1rem;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s;
            width: 100%;
        }

        .submit-btn:hover {
            background-color: #219653;
            transform: translateY(-2px);
        }

        /* Drug List Styles */
        .drug-list {
            background-color: white;
            padding: 2rem;
            border-radius: 15px;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.1);
        }

        .drug-list h2 {
            color: var(--primary-color);
            margin-bottom: 1.5rem;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th, td {
            padding: 1rem;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }

        th {
            background-color: var(--light-gray);
            color: var(--dark-gray);
            font-weight: 600;
        }

        tr:hover {
            background-color: var(--light-gray);
        }

        .action-btn {
            padding: 0.5rem 1rem;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            transition: all 0.3s;
            font-weight: 500;
        }

        .edit-btn {
            background-color: var(--secondary-color);
            color: white;
            margin-right: 0.5rem;
        }

        .delete-btn {
            background-color: #e74c3c;
            color: white;
        }

        .response-message {
            margin-top: 1rem;
            padding: 1rem;
            border-radius: 8px;
            text-align: center;
        }

        .success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .error {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }

        .messages-container {
            background-color: white;
            padding: 2rem;
            border-radius: 15px;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.1);
        }

        .message-card {
            margin-bottom: 1.5rem;
            padding: 1rem;
            border: 1px solid #ddd;
            border-radius: 8px;
        }

        .message-card h3 {
            color: var(--primary-color);
            margin-bottom: 0.5rem;
        }

        .message-card p {
            margin-bottom: 1rem;
            color: var(--dark-gray);
        }

        .reply-form {
            margin-top: 1rem;
        }

        .reply-form textarea {
            width: 100%;
            padding: 0.8rem;
            border: 1px solid #ddd;
            border-radius: 8px;
            font-size: 1rem;
            margin-bottom: 1rem;
        }

        .reply-form button {
            padding: 0.8rem 1.5rem;
            background-color: var(--accent-color);
            color: white;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.3s;
        }

        .reply-form button:hover {
            background-color: #219653;
        }

        .replies {
            margin-top: 1rem;
            padding-left: 1rem;
            border-left: 2px solid var(--primary-color);
        }

        .reply {
            margin-bottom: 1rem;
        }

        .reply p {
            margin: 0;
            color: var(--dark-gray);
        }

        .reply small {
            color: #666;
        }
    </style>
</head>
<body>
    <header>
        <nav>
            <div class="logo">
                <i class="fas fa-clinic-medical"></i>
                <span>SPC Staff</span>
            </div>
            <div class="nav-links">
                <a href="staffhome.html">Home</a>
                <a href="staffdashboard.php">Dashboard</a>
                <a href="messages.php">Messages</a>
                <a href="view-orders.php">Orders</a>
                <a href="staffprofile.php">Profile</a>
                <a href="logouts.php">Logout</a>
            </div>
        </nav>
    </header>

    <main class="main-content">
        <div class="dashboard-header">
            <h1>Messages</h1>
            <p>View and reply to messages from the admin</p>
        </div>

        <div class="messages-container">
            <?php if (!empty($messages)): ?>
                <?php foreach ($messages as $message): ?>
                    <div class="message-card">
                        <h3><?php echo htmlspecialchars($message['subject']); ?></h3>
                        <p><?php echo htmlspecialchars($message['body']); ?></p>
                        <small>Sent on: <?php echo $message['sentDate']; ?></small>

                        <!-- Reply Form -->
                        <form class="reply-form" method="POST">
                            <input type="hidden" name="message_id" value="<?php echo $message['messageId']; ?>">
                            <textarea name="reply_text" rows="3" placeholder="Type your reply..." required></textarea>
                            <button type="submit" name="reply">Send Reply</button>
                        </form>

                        <!-- Display Replies -->
                        <?php if (!empty($message['Replies'])): ?>
                            <div class="replies">
                                <?php foreach ($message['Replies'] as $reply): ?>
                                    <div class="reply">
                                        <p><?php echo htmlspecialchars($reply['replyText']); ?></p>
                                        <small>Replied by: <?php echo $reply['replierEmail']; ?> on <?php echo $reply['replyDate']; ?></small>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p>No messages found.</p>
            <?php endif; ?>
        </div>
    </main>
</body>
</html>