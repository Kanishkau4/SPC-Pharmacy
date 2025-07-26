<?php
session_start();
if (!isset($_SESSION['EMAIL'])) {
    header("Location: pharmlogin.php");
    exit();
}

$currentPage = basename($_SERVER['PHP_SELF']);

// Search drugs
if (isset($_GET['search'])) {
    $searchTerm = urlencode($_GET['search']);
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, "http://localhost:5268/api/Drug/SearchDrug?name=" . $searchTerm);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));

    // Enable HTTP status code checking
    curl_setopt($ch, CURLOPT_FAILONERROR, true); // Fail on 400+ status codes
    $response = curl_exec($ch);

    // Check for cURL errors or HTTP status codes
    if (curl_errno($ch)) {
        $apiError = "cURL Error: " . curl_error($ch);
    } else {
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        if ($httpCode == 400) {
            // Handle 400 Bad Request
            $apiError = "400 Bad Request: " . $response;
        } elseif ($httpCode != 200) {
            // Handle other HTTP errors
            $apiError = "HTTP Error: Status Code " . $httpCode;
        } else {
            // Decode the JSON response
            $drugs = json_decode($response, true);

            // Check if the response is valid and is an array
            if (json_last_error() === JSON_ERROR_NONE && is_array($drugs)) {
                // Check if the drugs array is empty
                if (empty($drugs)) {
                    $noDrugsFound = true;
                }
            } else {
                // Handle invalid JSON or unexpected response
                $apiError = "Invalid API response. Please try again later.";
            }
        }
    }

    curl_close($ch);
}

// Place order
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['order'])) {
    $orderData = array(
        "drugId" => $_POST['druG_ID'],
        "quantity" => $_POST['quantity'],
        "totalPrice" => $_POST['totalPrice'], // Include total price
        "pharmacyEmail" => $_SESSION['EMAIL']
    );
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, "http://localhost:5268/api/Pharmacy/PlaceOrder");
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($orderData));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
    
    $response = curl_exec($ch);

    if (curl_errno($ch)) {
        echo '<script>alert("Error: ' . curl_error($ch) . '");</script>';
    } else {
        echo '<script>alert("Order Successfully Placed");</script>';
    }

    $result = json_decode($response, true);
    curl_close($ch);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pharmacy Dashboard</title>
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

        .search-container {
            margin-bottom: 2rem;
        }

        .search-box {
            display: flex;
            gap: 1rem;
            max-width: 600px;
        }

        .search-box input {
            flex: 1;
            padding: 0.8rem;
            border: 2px solid #e1e1e1;
            border-radius: 8px;
            font-size: 1rem;
        }

        .search-box button {
            padding: 0.8rem 1.5rem;
            background-color: var(--accent-color);
            color: white;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            transition: background-color 0.3s;
        }

        .search-box button:hover {
            background-color: #d35400;
        }

        .drug-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 2rem;
        }

        .drug-card {
            background-color: white;
            border-radius: 12px;
            padding: 1.5rem;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        .drug-card h3 {
            color: var(--primary-color);
            margin-bottom: 1rem;
        }

        .drug-info {
            margin-bottom: 1rem;
        }

        .drug-info p {
            margin-bottom: 0.5rem;
            color: var(--dark-gray);
        }

        .order-form {
            display: flex;
            gap: 1rem;
            margin-top: 1rem;
        }

        .order-form input {
            width: 80px;
            padding: 0.5rem;
            border: 1px solid #e1e1e1;
            border-radius: 4px;
        }

        .order-form button {
            padding: 0.5rem 1rem;
            background-color: var(--secondary-color);
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            transition: background-color 0.3s;
        }

        .order-form button:hover {
            background-color: #2980b9;
        }

        @media (max-width: 768px) {
            .dashboard {
                grid-template-columns: 1fr;
            }

            .sidebar {
                display: none;
            }
        }
        
        .error-message {
            color: red;
            font-weight: bold;
            margin-bottom: 1rem;
        }
    </style>
    <script>
        function calculateTotalPrice(input) {
            const quantity = input.value;
            const price = input.getAttribute('data-price');
            const totalPrice = (quantity * price).toFixed(2);
            input.parentElement.querySelector('.total-price').textContent = `Total: Rs ${totalPrice}`;
            input.parentElement.querySelector('input[name="totalPrice"]').value = totalPrice; // Update hidden input
        }
    </script>
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
            <div class="search-container">
                <form class="search-box" method="GET">
                    <input type="text" name="search" placeholder="Search for drugs..." value="<?php echo $_GET['search'] ?? ''; ?>">
                    <button type="submit">
                        <i class="fas fa-search"></i>
                        Search
                    </button>
                </form>
            </div>

            <div class="drug-grid">
                <?php if (isset($apiError)): ?>
                    <p class="error-message"><?php echo $apiError; ?></p>
                <?php elseif (isset($drugs) && is_array($drugs) && !empty($drugs)): ?>
                    <?php foreach ($drugs as $drug): ?>
                        <div class="drug-card">
                            <h3><?php echo htmlspecialchars($drug['name']); ?></h3>
                            <div class="drug-info">
                                <p><strong>Category:</strong> <?php echo htmlspecialchars($drug['category']); ?></p>
                                <p><strong>Price:</strong> Rs:<?php echo number_format($drug['price'], 2); ?></p>
                                <p><strong>Available:</strong> <?php echo $drug['quantity']; ?> units</p>
                            </div>
                            <form class="order-form" method="POST">
                                <input type="hidden" name="druG_ID" value="<?php echo $drug['druG_ID']; ?>">
                                <input type="number" name="quantity" min="1" max="<?php echo $drug['quantity']; ?>" value="1" required 
                                       data-price="<?php echo $drug['price']; ?>" 
                                       oninput="calculateTotalPrice(this)">
                                <input type="hidden" name="totalPrice" value="<?php echo $drug['price']; ?>"> <!-- Hidden input for total price -->
                                <button type="submit" name="order">
                                    <i class="fas fa-shopping-cart"></i>
                                    Order
                                </button>
                                <span class="total-price">Total: Rs <?php echo number_format($drug['price'], 2); ?></span>
                            </form>
                        </div>
                    <?php endforeach; ?>
                <?php elseif (isset($noDrugsFound) && $noDrugsFound): ?>
                    <p>No drugs found matching your search.</p>
                <?php endif; ?>
            </div>
        </main>
    </div>
</body>
</html>