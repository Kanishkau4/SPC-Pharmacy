<?php
session_start();
if (!isset($_SESSION['EMAIL'])) {
    header("Location: pharmlogin.php");
    exit();
}

$currentPage = basename($_SERVER['PHP_SELF']);

// Fetch all orders for the logged-in pharmacy
$pharmacyEmail = $_SESSION['EMAIL'];
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, "http://localhost:5268/api/Pharmacy/GetOrdersByPharmacyEmail?pharmacyEmail=" . urlencode($pharmacyEmail));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$response = curl_exec($ch);
$orders = json_decode($response, true);
curl_close($ch);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Orders</title>
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

        .orders-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 2rem;
            background-color: white;
            border-radius: 12px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        .orders-table th,
        .orders-table td {
            padding: 1rem;
            text-align: left;
            border-bottom: 1px solid #e1e1e1;
        }

        .orders-table th {
            background-color: var(--primary-color);
            color: white;
        }

        .orders-table tr:hover {
            background-color: var(--light-gray);
        }

        .orders-table td {
            color: var(--dark-gray);
        }

        @media (max-width: 768px) {
            .dashboard {
                grid-template-columns: 1fr;
            }

            .sidebar {
                display: none;
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
            <h1>My Orders</h1>
            <?php if (isset($orders) && is_array($orders) && !empty($orders)): ?>
            <table class="orders-table">
                <thead>
                    <tr>
                        <th>Order ID</th>
                        <th>Drug Name</th>
                        <th>Quantity</th>
                        <th>Total Price</th>
                        <th>Order Date</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($orders as $order): ?>
                        <?php if (is_array($order)): ?>
                            <tr>
                                <td><?php echo isset($order['orderId']) ? htmlspecialchars($order['orderId']) : ''; ?></td>
                                <td><?php echo isset($order['drugId']) ? htmlspecialchars($order['drugId']) : ''; ?></td>
                                <td><?php echo isset($order['quantity']) ? htmlspecialchars($order['quantity']) : ''; ?></td>
                                <td>Rs:<?php echo isset($order['totalPrice']) ? number_format($order['totalPrice'], 2) : '0.00'; ?></td>
                                <td><?php echo isset($order['orderDate']) ? htmlspecialchars($order['orderDate']) : ''; ?></td>
                                <td><?php echo isset($order['status']) ? htmlspecialchars($order['status']) : ''; ?></td>
                            </tr>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p>No orders found.</p>
        <?php endif; ?>
        </main>
    </div>
</body>
</html>