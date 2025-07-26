<?php
session_start();
if (!isset($_SESSION['EMAIL'])) {
    header("Location: pharmlogin.php");
    exit();
}

$currentPage = basename($_SERVER['PHP_SELF']);

// Handle drug request submission
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['request_drug'])) {
    $drugName = $_POST['drug_name'];
    $drugCategory = $_POST['drug_category'];
    $quantity = $_POST['quantity'];
    $pharmacyEmail = $_SESSION['EMAIL'];

    $requestData = array(
        "drugName" => $drugName,
        "drugCategory" => $drugCategory,
        "quantity" => $quantity,
        "pharmacyEmail" => $pharmacyEmail
    );

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, "http://localhost:5268/api/Request/RequestDrug");
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($requestData));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));

    $response = curl_exec($ch);

    if (curl_errno($ch)) {
        echo '<script>alert("Error: ' . curl_error($ch) . '");</script>';
    } else {
        echo '<script>alert("Drug request submitted successfully");</script>';
    }

    curl_close($ch);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Drug Request</title>
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

        .form-container {
            max-width: 500px;
            margin: 0 auto;
            background-color: white;
            padding: 2rem;
            border-radius: 12px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            color: var(--dark-gray);
        }

        .form-group input,
        .form-group select {
            width: 100%;
            padding: 0.8rem;
            border: 1px solid #e1e1e1;
            border-radius: 8px;
            font-size: 1rem;
        }

        .form-group button {
            padding: 0.8rem 1.5rem;
            background-color: var(--accent-color);
            color: white;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            transition: background-color 0.3s;
        }

        .form-group button:hover {
            background-color: #d35400;
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
            <div class="form-container">
                <h2>Request a Drug</h2>
                <form method="POST">
                    <div class="form-group">
                        <label for="drug_name">Drug Name</label>
                        <input type="text" id="drug_name" name="drug_name" required>
                    </div>
                    <div class="form-group">
                        <label for="drug_category">Drug Category</label>
                        <select id="drug_category" name="drug_category" required>
                            <option value="Painkiller">Painkiller</option>
                            <option value="Antibiotic">Antibiotic</option>
                            <option value="Antiviral">Antiviral</option>
                            <option value="Antifungal">Antifungal</option>
                            <option value="Other">Other</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="quantity">Quantity</label>
                        <input type="number" id="quantity" name="quantity" min="1" required>
                    </div>
                    <div class="form-group">
                        <button type="submit" name="request_drug">Submit Request</button>
                    </div>
                </form>
            </div>
        </main>
    </div>
</body>
</html>