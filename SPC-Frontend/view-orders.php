<?php
session_start();
if (!isset($_SESSION['EMAIL'])) {
    header("Location: stafflogin.php");
    exit();
}

// Fetch all orders
$apiUrl = "http://localhost:5268/api/Pharmacy/GetAllOrders";
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $apiUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPGET, true);

$response = curl_exec($ch);
curl_close($ch);

$orders = [];
if ($response) {
    $orders = json_decode($response, true);
}

// Handle order confirmation
if (isset($_GET['orderId'])) {
    $orderId = $_GET['orderId'];
    $confirmUrl = "http://localhost:5268/api/Pharmacy/ConfirmOrder?orderId=" . urlencode($orderId);
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $confirmUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);

    $confirmResponse = curl_exec($ch);
    curl_close($ch);

    if ($confirmResponse) {
        $confirmData = json_decode($confirmResponse, true);
        if ($confirmData['StatusCode'] === 200) {
            echo "<script>alert('Order confirmed successfully!');</script>";
            echo "<script>window.location.href = 'view-orders.php';</script>";
        } else {
            echo "<script>alert('Failed to confirm order: " . $confirmData['StatusMessage'] . "');</script>";
        }
    } else {
        echo "<script>alert('Failed to confirm order: No response from server.');</script>";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Orders Management - SPC Staff Portal</title>
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

        .orders-container {
            background-color: white;
            padding: 2rem;
            border-radius: 15px;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.1);
        }

        .orders-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
        }

        .orders-header h2 {
            color: var(--primary-color);
        }

        .order-stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        .stat-card {
            background-color: var(--light-gray);
            padding: 1.5rem;
            border-radius: 10px;
            text-align: center;
        }

        .stat-card h3 {
            color: var(--dark-gray);
            margin-bottom: 0.5rem;
        }

        .stat-card .value {
            font-size: 1.8rem;
            font-weight: bold;
            color: var(--primary-color);
        }

        .orders-table {
            width: 100%;
            border-collapse: collapse;
        }

        .orders-table th,
        .orders-table td {
            padding: 1rem;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }

        .orders-table th {
            background-color: var(--light-gray);
            color: var(--dark-gray);
            font-weight: 600;
        }

        .orders-table tr:hover {
            background-color: var(--light-gray);
        }

        .status-badge {
            padding: 0.5rem 1rem;
            border-radius: 20px;
            font-weight: 500;
            text-align: center;
            display: inline-block;
        }

        .status-pending {
            background-color: #fff3cd;
            color: #856404;
        }

        .status-processing {
            background-color: #cce5ff;
            color: #004085;
        }

        .status-completed {
            background-color: #d4edda;
            color: #155724;
        }

        .status-cancelled {
            background-color: #f8d7da;
            color: #721c24;
        }

        .confirm-button {
            background-color: var(--accent-color);
            color: white;
            border: none;
            padding: 0.5rem 1rem;
            border-radius: 5px;
            cursor: pointer;
            font-size: 0.9rem;
            transition: background-color 0.3s ease;
        }

        .confirm-button:hover {
            background-color: #219653; /* Darker shade of the accent color */
        }

        .confirm-button:disabled {
            background-color: #bdc3c7;
            cursor: not-allowed;
        }

        /* Status Badge Styles */
        .status-badge {
            padding: 0.5rem 1rem;
            border-radius: 20px;
            font-weight: 500;
            text-align: center;
            display: inline-block;
            font-size: 0.9rem;
        }

        .status-pending {
            background-color: #fff3cd;
            color: #856404;
        }

        .status-processing {
            background-color: #cce5ff;
            color: #004085;
        }

        .status-completed {
            background-color: #d4edda;
            color: #155724;
        }

        .status-cancelled {
            background-color: #f8d7da;
            color: #721c24;
        }

        @media (max-width: 768px) {
            .nav-links {
                display: none;
            }

            .main-content {
                padding: 6rem 1rem 2rem;
            }

            .dashboard-header h1 {
                font-size: 2rem;
            }

            .orders-table {
                display: block;
                overflow-x: auto;
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

    <main class="main-content">
        <div class="dashboard-header">
            <h1>Orders Management</h1>
            <p>View and manage all customer orders</p>
        </div>

        <div class="orders-container">
            <div class="orders-header">
                <h2>All Orders</h2>
            </div>

            <div class="order-stats">
                <div class="stat-card">
                    <h3>Total Orders</h3>
                    <div class="value"><?php echo count($orders); ?></div>
                </div>
            </div>

            <?php if (!empty($orders)): ?>
                <table class="orders-table">
                    <thead>
                        <tr>
                            <th>Order ID</th>
                            <th>Drug ID</th>
                            <th>Quantity</th>
                            <th>Pharmacy Email</th>
                            <th>Order Date</th>
                            <th>Total Price</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($orders as $order): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($order['orderId']); ?></td>
                                <td><?php echo htmlspecialchars($order['drugId']); ?></td>
                                <td><?php echo htmlspecialchars($order['quantity']); ?></td>
                                <td><?php echo htmlspecialchars($order['pharmacyEmail']); ?></td>
                                <td><?php echo htmlspecialchars(date('Y-m-d', strtotime($order['orderDate']))); ?></td>
                                <td>LKR <?php echo htmlspecialchars(number_format($order['totalPrice'], 2)); ?></td>
                                <td>
                                    <span class="status-badge status-<?php echo strtolower(htmlspecialchars($order['status'])); ?>">
                                        <?php echo htmlspecialchars($order['status']); ?>
                                    </span>
                                </td>
                                <td>
                                    <?php if ($order['status'] === 'Pending'): ?>
                                        <button class="confirm-button" onclick="confirmOrder(<?php echo $order['orderId']; ?>)">Confirm</button>
                                    <?php else: ?>
                                        <button class="confirm-button" disabled>Confirmed</button>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p>No orders found.</p>
            <?php endif; ?>
        </div>
    </main>

    <script>
        function confirmOrder(orderId) {
            fetch(`view-orders.php?orderId=${orderId}`)
                .then(response => response.json())
                .then(data => {
                    if (data.StatusCode === 200) {
                        alert(data.StatusMessage);
                        location.reload(); // Refresh the page to reflect the updated status
                    } else {
                        alert(data.StatusMessage);
                    }
                })
                .catch(error => console.error('Error:', error));
        }
    </script>
</body>
</html>