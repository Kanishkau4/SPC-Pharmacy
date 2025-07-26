<?php
session_start();
if (!isset($_SESSION['EMAIL'])) {
    header("Location: pharmlogin.php");
    exit();
}

$currentPage = basename($_SERVER['PHP_SELF']);

// Fetch pharmacy details
$email = $_SESSION['EMAIL'];
$ch = curl_init("http://localhost:5268/api/Pharmacy/GetPharmacyByEmail?email=" . urlencode($email));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($httpCode == 200) {
    $pharmacy = json_decode($response, true);
} else {
    $error = "Failed to fetch pharmacy details. Status Code: $httpCode";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pharmacy Profile - SPC</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root {
            --primary-color: #1a5f7a;
            --secondary-color: #2d9cdb;
            --accent-color: #e67e22;
            --light-gray: #f7f9fc;
            --dark-gray: #2c3e50;
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

        .dashboard {
            display: grid;
            grid-template-columns: 250px 1fr;
            min-height: 100vh;
        }

        .sidebar {
            background-color: var(--primary-color);
            color: white;
            padding: 2rem;
        }

        .sidebar-header {
            display: flex;
            align-items: center;
            gap: 1rem;
            margin-bottom: 2rem;
        }

        .sidebar-menu {
            list-style: none;
        }

        .sidebar-menu li {
            margin-bottom: 1rem;
        }

        .sidebar-menu a {
            color: white;
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 1rem;
            padding: 0.8rem;
            border-radius: 8px;
            transition: background-color 0.3s;
        }

        .sidebar-menu a:hover {
            background-color: rgba(255, 255, 255, 0.1);
        }

        .sidebar-menu a.active {
            background-color: rgba(255, 255, 255, 0.2);
            font-weight: bold;
        }

        .sidebar-menu a.active:hover {
            background-color: rgba(255, 255, 255, 0.3);
        }

        .main-content {
            padding: 2rem;
        }

        .profile-container {
            max-width: 800px;
            margin: 0 auto;
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

        .error-message {
            background-color: #e74c3c;
            color: white;
            padding: 1rem;
            border-radius: 8px;
            margin-bottom: 1rem;
        }

        @media (max-width: 768px) {
            .dashboard {
                grid-template-columns: 1fr;
            }

            .sidebar {
                display: none;
            }

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
    <div class="dashboard">
        <aside class="sidebar">
            <div class="sidebar-header">
                <i class="fas fa-prescription-bottle-alt fa-2x"></i>
                <h2>Pharmacy Panel</h2>
            </div>
            <ul class="sidebar-menu">
                <li>
                    <a href="pharmacydashboard.php" class="<?php echo ($currentPage == 'pharmacydashboard.php') ? 'active' : ''; ?>">
                        <i class="fas fa-search"></i>
                        Search Drugs
                    </a>
                </li>
                <li>
                    <a href="orders.php" class="<?php echo ($currentPage == 'orders.php') ? 'active' : ''; ?>">
                        <i class="fas fa-shopping-cart"></i>
                        My Orders
                    </a>
                </li>
                <li>
                    <a href="request.php" class="<?php echo ($currentPage == 'request.php') ? 'active' : ''; ?>">
                        <i class="fas fa-pills"></i>
                        Drug Requests
                    </a>
                </li>
                <li>
                    <a href="phaprofile.php" class="<?php echo ($currentPage == 'phaprofile.php') ? 'active' : ''; ?>">
                        <i class="fas fa-user"></i>
                        Profile
                    </a>
                </li>
                <li>
                    <a href="logout.php" class="<?php echo ($currentPage == 'logout.php') ? 'active' : ''; ?>">
                        <i class="fas fa-sign-out-alt"></i>
                        Logout
                    </a>
                </li>
            </ul>
        </aside>

        <main class="main-content">
            <?php if (isset($error)): ?>
                <div class="error-message">
                    <i class="fas fa-exclamation-circle"></i>
                    <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>

            <?php if (isset($pharmacy)): ?>
                <div class="profile-container">
                    <div class="profile-header">
                        <div class="profile-avatar">
                            <i class="fas fa-hospital-alt"></i>
                        </div>
                        <div class="profile-info">
                            <h2><?php echo htmlspecialchars($pharmacy['name']); ?></h2>
                            <p>Pharmacy ID: <?php echo htmlspecialchars($pharmacy['pharmacY_ID']); ?></p>
                        </div>
                    </div>

                    <div class="profile-details">
                        <div class="detail-group">
                            <label>Email Address</label>
                            <div class="value"><?php echo htmlspecialchars($pharmacy['email']); ?></div>
                        </div>
                        <div class="detail-group">
                            <label>Contact Number</label>
                            <div class="value"><?php echo htmlspecialchars($pharmacy['contacT_NUMBER']); ?></div>
                        </div>
                        <div class="detail-group">
                            <label>Address</label>
                            <div class="value"><?php echo htmlspecialchars($pharmacy['address']); ?></div>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </main>
    </div>
</body>
</html>