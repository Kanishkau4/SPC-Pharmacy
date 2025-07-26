<?php
session_start();
if (!isset($_SESSION['EMAIL'])) {
    header("Location: stafflogin.php");
    exit();
}

// Fetch staff details
$email = $_SESSION['EMAIL'];
$ch = curl_init("http://localhost:5268/api/Staff/GetStaffByEmail?email=" . urlencode($email));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($httpCode == 200) {
    $staff = json_decode($response, true);
} else {
    $error = "Failed to fetch staff details. Status Code: $httpCode";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Staff Profile - SPC</title>
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
            margin: 0; /* Remove the default body margin */
        }

        header {
            background-color: var(--primary-color);
            padding: 1rem;
            position: fixed;
            width: 100%;
            top: 0; /* Ensures the header is at the top of the page */
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

        .profile-container {
            max-width: 800px;
            margin: 100px auto 2rem; /* Adjust this margin to add some space from the fixed header */
            background: white;
            border-radius: 15px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            padding: 2rem;
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

        .profile-container {
            max-width: 800px;
            margin: 100px auto 2rem;
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
<header>
        <nav>
            <div class="logo">
                <i class="fas fa-clinic-medical"></i>
                <span>SPC Staff</span>
            </div>
            <div class="nav-links">
                <a href="staffhome.html">Home</a>
                <a href="staffdashboard.php">Dashboard</a>
                <a href="inventory.html">Inventory</a>
                <a href="view-orders.php">Orders</a>
                <a href="staffprofile.php">Profile</a>
                <a href="logouts.php">Logout</a>
            </div>
        </nav>
    </header>

    <main>
        <?php if (isset($error)): ?>
            <div class="error-message">
                <i class="fas fa-exclamation-circle"></i>
                <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>

        <?php if (isset($staff)): ?>
        <div class="profile-container">
            <div class="profile-header">
                <div class="profile-avatar">
                    <i class="fas fa-user"></i>
                </div>
                <div class="profile-info">
                    <h2><?php echo htmlspecialchars($staff['firstname']) . " " . htmlspecialchars($staff['lastname']); ?></h2>
                    <p>Staff ID: <?php echo htmlspecialchars($staff['stafF_ID']); ?></p>
                </div>
            </div>

            <div class="profile-details">
                <div class="detail-group">
                    <label>Email Address</label>
                    <div class="value"><?php echo htmlspecialchars($staff['email']); ?></div>
                </div>
                <div class="detail-group">
                    <label>Address</label>
                    <div class="value"><?php echo htmlspecialchars($staff['address']); ?></div>
                </div>
            </div>
        </div>
        <?php endif; ?>
    </main>
</body>
</html>
