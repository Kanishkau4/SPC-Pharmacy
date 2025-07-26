<?php
session_start();
if (!isset($_SESSION['EMAIL'])) {
    header("Location: suplogin.php");
    exit();
}

// Fetch supplier details
$email = $_SESSION['EMAIL'];
$ch = curl_init("http://localhost:5268/api/Supplier/GetSupplierByEmail?email=" . urlencode($email));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($httpCode == 200) {
    $supplier = json_decode($response, true);
} else {
    $error = "Failed to fetch supplier details. Status Code: $httpCode";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Supplier Profile - SPC</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        /* Include the existing styles from the dashboard */
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

        .profile-container {
            max-width: 800px;
            margin: 2rem auto;
            background: white;
            border-radius: 15px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            padding: 2rem;
        }

        .profile-header {
            display: flex;
            align-items: center;
            gap: 2rem;
            margin-bottom: 2rem;
            padding-bottom: 1rem;
            border-bottom: 1px solid #eee;
        }

        .profile-avatar {
            width: 120px;
            height: 120px;
            background-color: var(--primary-color);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .profile-avatar i {
            font-size: 3rem;
            color: white;
        }

        .profile-info h2 {
            color: var(--primary-color);
            margin-bottom: 0.5rem;
        }

        .profile-details {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1.5rem;
        }

        .detail-group {
            margin-bottom: 1.5rem;
        }

        .detail-group label {
            display: block;
            color: var(--dark-gray);
            font-weight: 500;
            margin-bottom: 0.5rem;
        }

        .detail-group .value {
            font-size: 1.1rem;
            color: var(--primary-color);
        }

        @media (max-width: 768px) {
            .profile-header {
                flex-direction: column;
                text-align: center;
            }

            .profile-details {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <!-- Include the existing header -->
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
                <a href="profile.php">
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
        <?php if (isset($error)): ?>
            <div class="error-message">
                <i class="fas fa-exclamation-circle"></i>
                <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>

        <?php if (isset($supplier)): ?>
        <div class="profile-container">
            <div class="profile-header">
                <div class="profile-avatar">
                    <i class="fas fa-user"></i>
                </div>
                <div class="profile-info">
                    <h2><?php echo htmlspecialchars($supplier['name']); ?></h2>
                    <p>Supplier ID: <?php echo htmlspecialchars($supplier['supplieR_ID']); ?></p>
                </div>
            </div>

            <div class="profile-details">
                <div class="detail-group">
                    <label>Email Address</label>
                    <div class="value"><?php echo htmlspecialchars($supplier['email']); ?></div>
                </div>
                <div class="detail-group">
                    <label>Phone Number</label>
                    <div class="value"><?php echo htmlspecialchars($supplier['phone']); ?></div>
                </div>
                <div class="detail-group">
                    <label>Address</label>
                    <div class="value"><?php echo htmlspecialchars($supplier['address']); ?></div>
                </div>
            </div>
        </div>
        <?php endif; ?>
    </main>
</body>
</html>