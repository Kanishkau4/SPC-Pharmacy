<?php
session_start();
if (!isset($_SESSION['EMAIL'])) {
    header("Location: suplogin.php");
    exit();
}

$email = $_SESSION['EMAIL']; // Get the logged-in supplier's email

// Fetch proposals for the logged-in supplier
$ch = curl_init('http://localhost:5268/api/Proposal/GetProposalsBySupplier?email=' . urlencode($email));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($httpCode == 200) {
    $proposals = json_decode($response, true);
} else {
    $proposals = null;
    $error = "Failed to fetch proposals. Status Code: $httpCode";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Notifications - SPC</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root {
            --primary-color: #1a5f7a;
            --secondary-color: #2d9cdb;
            --accent-color: #27ae60;
            --light-gray: #f7f9fc;
            --dark-gray: #2c3e50;
            --danger-color: #e74c3c;
            --warning-color: #f39c12;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        body {
            background-color: var(--light-gray);
            min-height: 100vh;
        }

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

        .user-menu {
            display: flex;
            align-items: center;
            gap: 1.5rem;
            color: white;
        }

        .user-menu a {
            color: white;
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .dashboard-container {
            padding: 100px 2rem 2rem;
            max-width: 1400px;
            margin: 0 auto;
        }

        .dashboard-header {
            margin-bottom: 2rem;
        }

        .dashboard-header h1 {
            color: var(--primary-color);
            margin-bottom: 0.5rem;
        }

        .proposals-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 1.5rem;
            margin-top: 2rem;
        }

        .proposal-card {
            background: white;
            border-radius: 15px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            overflow: hidden;
            transition: transform 0.2s;
        }

        .proposal-card:hover {
            transform: translateY(-5px);
        }

        .proposal-header {
            background-color: var(--primary-color);
            color: white;
            padding: 1rem;
            font-size: 1.2rem;
            font-weight: bold;
        }

        .proposal-body {
            padding: 1.5rem;
        }

        .proposal-info {
            margin-bottom: 1.5rem;
        }

        .proposal-info-item {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            margin-bottom: 0.8rem;
            color: var(--dark-gray);
        }

        .proposal-info-item i {
            color: var(--primary-color);
            width: 20px;
        }

        .proposal-status {
            background-color: #e8f5e9;
            color: var(--accent-color);
            display: inline-block;
            padding: 0.4rem 1rem;
            border-radius: 20px;
            font-size: 0.9rem;
            margin-bottom: 1rem;
        }

        .error-message {
            background-color: var(--danger-color);
            color: white;
            padding: 1rem;
            border-radius: 8px;
            margin-bottom: 1rem;
        }

        @media (max-width: 768px) {
            .dashboard-container {
                padding: 80px 1rem 1rem;
            }

            .proposals-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <header>
        <nav>
            <div class="logo">
                <i class="fas fa-clinic-medical"></i>
                <span>SPC</span>
            </div>
            <div class="user-menu">
                <a href="supnotifications.php">
                    <i class="fas fa-bell"></i>
                    Notifications
                </a>
                <a href="supprofile.php">
                    <i class="fas fa-user-circle"></i>
                    Profile
                </a>
                <a href="logout.php">
                    <i class="fas fa-sign-out-alt"></i>
                    Logout
                </a>
            </div>
        </nav>
    </header>

    <main class="dashboard-container">
        <div class="dashboard-header">
            <h1>Notifications</h1>
            <p>View your accepted proposals.</p>
        </div>

        <?php if (isset($error)): ?>
            <div class="error-message">
                <i class="fas fa-exclamation-circle"></i>
                <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>

        <div class="proposals-grid">
            <?php if ($proposals !== null && count($proposals) > 0): ?>
                <?php foreach ($proposals as $proposal): ?>
                    <?php if ($proposal['status'] === 'Accepted'): ?>
                        <div class="proposal-card">
                            <div class="proposal-header">
                                Tender ID: <?php echo htmlspecialchars($proposal['tenderId']); ?>
                            </div>
                            <div class="proposal-body">
                                <div class="proposal-status">
                                    <?php echo htmlspecialchars($proposal['status']); ?>
                                </div>
                                <div class="proposal-info">
                                    <div class="proposal-info-item">
                                        <i class="fas fa-user"></i>
                                        <span>Supplier: <?php echo htmlspecialchars($proposal['supplierName']); ?></span>
                                    </div>
                                    <div class="proposal-info-item">
                                        <i class="fas fa-dollar-sign"></i>
                                        <span>Price: Rs:<?php echo htmlspecialchars($proposal['price']); ?></span>
                                    </div>
                                    <div class="proposal-info-item">
                                        <i class="fas fa-clock"></i>
                                        <span>Delivery Time: <?php echo htmlspecialchars($proposal['deliveryTime']); ?> days</span>
                                    </div>
                                    <div class="proposal-info-item">
                                        <i class="fas fa-calendar-alt"></i>
                                        <span>Submission Date: <?php echo htmlspecialchars($proposal['submissionDate']); ?></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>
                <?php endforeach; ?>
            <?php else: ?>
                <p style="grid-column: 1/-1; text-align: center; padding: 2rem;">No accepted proposals found.</p>
            <?php endif; ?>
        </div>
    </main>
</body>
</html>