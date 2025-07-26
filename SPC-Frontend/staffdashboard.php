<?php
session_start();
if (!isset($_SESSION['EMAIL'])) {
    header("Location: stafflogin.php");
    exit();
}

// Fetch all drugs
$apiUrl = "http://localhost:5268/api/Drug/GetAllDrugs";
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $apiUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPGET, true);

$response = curl_exec($ch);
curl_close($ch);

$drugs = [];
if ($response) {
    $drugs = json_decode($response, true);
}

// Fetch specific drug details if edit mode
$editDrug = null;
if (isset($_GET['edit'])) {
    $drugId = $_GET['edit'];
    foreach ($drugs as $drug) {
        if ($drug['druG_ID'] == $drugId) {
            $editDrug = $drug;
            break;
        }
    }
}

if (isset($_SESSION['message'])):
    $messageClass = $_SESSION['message_type'] == 'success' ? 'success' : 'error';
?>
    <div class="response-message <?php echo $messageClass; ?>">
        <?php echo $_SESSION['message']; ?>
    </div>
    <?php
    // Clear the message after displaying it
    unset($_SESSION['message']);
    unset($_SESSION['message_type']);
endif;

// Handle drug operations
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['action'])) {
        $data = [
            "NAME" => $_POST['name'],
            "CATEGORY" => $_POST['category'],
            "PRICE" => $_POST['price'],
            "QUANTITY" => $_POST['quantity']
        ];

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ["Content-Type: application/json"]);

        if ($_POST['action'] == 'add') {
            $apiUrl = "http://localhost:5268/api/Drug/AddDrug";
            curl_setopt($ch, CURLOPT_URL, $apiUrl);
            curl_setopt($ch, CURLOPT_POST, true);
        } elseif ($_POST['action'] == 'update') {
            $apiUrl = "http://localhost:5268/api/Drug/UpdateDrug/" . $_POST['drug_id'];
            curl_setopt($ch, CURLOPT_URL, $apiUrl);
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT");
        }

        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        $response = curl_exec($ch);
        $success = !curl_errno($ch);
        curl_close($ch);

        if ($success) {
            $_SESSION['message'] = 'Drug ' . ($_POST['action'] == 'add' ? 'added' : 'updated') . ' successfully!';
            $_SESSION['message_type'] = 'success';
        } else {
            $_SESSION['message'] = 'There was an error processing the drug. Please try again.';
            $_SESSION['message_type'] = 'error';
        }

        // Redirect to refresh the page and avoid form resubmission
        header("Location: " . $_SERVER['PHP_SELF']);
        exit();
    }
}

// Handle delete operation
if (isset($_GET['delete'])) {
    $drugId = $_GET['delete'];
    $apiUrl = "http://localhost:5268/api/Drug/DeleteDrug/" . $drugId;

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $apiUrl);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "DELETE");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $response = curl_exec($ch);
    $success = !curl_errno($ch);
    curl_close($ch);

    if ($success) {
        $_SESSION['message'] = 'Drug deleted successfully!';
        $_SESSION['message_type'] = 'success';
    } else {
        $_SESSION['message'] = 'There was an error deleting the drug. Please try again.';
        $_SESSION['message_type'] = 'error';
    }

    // Redirect to refresh the page and avoid resubmission
    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Drug Management - SPC Staff Portal</title>
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

        .cancel-btn {
            background-color: #6c757d;
            color: white;
            padding: 1rem 2rem;
            border: none;
            border-radius: 8px;
            font-size: 1.1rem;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s;
            width: 100%;
            margin-top: 1rem;
        }

        .cancel-btn:hover {
            background-color: #5a6268;
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
            text-decoration: none;
            display: inline-block;
            margin: 0.25rem;
        }

        .edit-btn {
            background-color: var(--secondary-color);
            color: white;
        }

        .edit-btn:hover {
            background-color: #1a91d1;
        }

        .delete-btn {
            background-color: #e74c3c;
            color: white;
        }

        .delete-btn:hover {
            background-color: #c0392b;
        }

        .action-buttons {
            display: flex;
            justify-content: flex-start;
            align-items: center;
            flex-wrap: wrap;
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
        }

        .drug-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 1rem;
        }

        .drug-table th, .drug-table td {
            padding: 1rem;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }

        .drug-table th {
            background-color: var(--light-gray);
            color: var(--dark-gray);
            font-weight: 600;
        }

        .drug-table tr:hover {
            background-color: var(--light-gray);
        }
        
        .hidden {
            display: none;
        }

        .response-message {
            margin-top: 1rem;
            padding: 1rem;
            border-radius: 8px;
            text-align: center;
            max-width: 800px;
            margin: 1rem auto;
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

        /* Add this to your existing style section */
        .message-container {
            position: fixed;
            top: 100px;
            right: 20px;
            z-index: 1000;
            max-width: 350px;
            transition: all 0.5s ease-in-out;
        }

        .message-box {
            padding: 1rem 2rem;
            border-radius: 8px;
            margin-bottom: 1rem;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
            animation: slideIn 0.5s ease-out forwards;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .message-box.success {
            background-color: #4caf50;
            color: white;
            border-left: 4px solid #2e7d32;
        }

        .message-box.error {
            background-color: #f44336;
            color: white;
            border-left: 4px solid #c62828;
        }

        @keyframes slideIn {
            0% {
                transform: translateX(100%);
                opacity: 0;
            }
            100% {
                transform: translateX(0);
                opacity: 1;
            }
        }

        @keyframes fadeOut {
            0% {
                transform: translateX(0);
                opacity: 1;
            }
            100% {
                transform: translateX(100%);
                opacity: 0;
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
                <a href="messages.php">Messages</a>
                <a href="view-orders.php">Orders</a>
                <a href="staffprofile.php">Profile</a>
                <a href="logouts.php">Logout</a>
            </div>
        </nav>
    </header>

    <main class="main-content">
        <div class="dashboard-header">
            <h1>Drug Management</h1>
            <p>Add, update, and manage drugs in the inventory</p>
        </div>

        <!-- Place this right after opening the <body> tag -->
        <?php if (isset($_SESSION['message'])): ?>
            <div class="message-container">
                <div class="message-box <?php echo $_SESSION['message_type']; ?>">
                    <i class="fas <?php echo $_SESSION['message_type'] == 'success' ? 'fa-check-circle' : 'fa-exclamation-circle'; ?>"></i>
                    <?php echo $_SESSION['message']; ?>
                </div>
            </div>
            <?php
            // Clear the message after displaying it
            unset($_SESSION['message']);
            unset($_SESSION['message_type']);
        endif;
        ?>

        <div class="dashboard-grid">
            <!-- Add Drug Form -->
            <div id="addDrugForm" class="form-container <?php echo isset($_GET['edit']) ? 'hidden' : ''; ?>">
                <h2>Add New Drug</h2>
                <form method="POST" action="">
                    <input type="hidden" name="action" value="add">

                    <div class="form-group">
                        <label for="add-name">Drug Name</label>
                        <input type="text" id="add-name" name="name" required placeholder="Enter drug name">
                    </div>

                    <div class="form-group">
                        <label for="add-category">Category</label>
                        <select id="add-category" name="category" required>
                            <option value="">Select category</option>
                            <option value="Antibiotics">Antibiotics</option>
                            <option value="Analgesics">Analgesics</option>
                            <option value="Antacids">Antacids</option>
                            <option value="Antivirals">Antivirals</option>
                            <option value="Antiseptics">Antiseptics</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="add-price">Price (LKR)</label>
                        <input type="number" id="add-price" name="price" step="0.01" required placeholder="Enter price">
                    </div>

                    <div class="form-group">
                        <label for="add-quantity">Quantity</label>
                        <input type="number" id="add-quantity" name="quantity" required placeholder="Enter quantity">
                    </div>

                    <button type="submit" class="submit-btn">Add Drug</button>
                </form>
            </div>

            <!-- Edit Drug Form -->
            <div id="editDrugForm" class="form-container <?php echo isset($_GET['edit']) ? '' : 'hidden'; ?>">
                <h2>Update Drug</h2>
                <form method="POST" action="">
                    <input type="hidden" name="action" value="update">
                    <input type="hidden" name="drug_id" value="<?php echo isset($_GET['edit']) ? $_GET['edit'] : ''; ?>">

                    <div class="form-group">
                        <label for="edit-name">Drug Name</label>
                        <input type="text" id="edit-name" name="name" required 
                               value="<?php echo isset($editDrug) ? htmlspecialchars($editDrug['name']) : ''; ?>">
                    </div>

                    <div class="form-group">
                        <label for="edit-category">Category</label>
                        <select id="edit-category" name="category" required>
                            <option value="">Select category</option>
                            <option value="Antibiotics" <?php echo (isset($editDrug) && $editDrug['category'] == 'Antibiotics') ? 'selected' : ''; ?>>Antibiotics</option>
                            <option value="Analgesics" <?php echo (isset($editDrug) && $editDrug['category'] == 'Analgesics') ? 'selected' : ''; ?>>Analgesics</option>
                            <option value="Antacids" <?php echo (isset($editDrug) && $editDrug['category'] == 'Antacids') ? 'selected' : ''; ?>>Antacids</option>
                            <option value="Antivirals" <?php echo (isset($editDrug) && $editDrug['category'] == 'Antivirals') ? 'selected' : ''; ?>>Antivirals</option>
                            <option value="Antiseptics" <?php echo (isset($editDrug) && $editDrug['category'] == 'Antiseptics') ? 'selected' : ''; ?>>Antiseptics</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="edit-price">Price (LKR)</label>
                        <input type="number" id="edit-price" name="price" step="0.01" required 
                               value="<?php echo isset($editDrug) ? htmlspecialchars($editDrug['price']) : ''; ?>">
                    </div>

                    <div class="form-group">
                        <label for="edit-quantity">Quantity</label>
                        <input type="number" id="edit-quantity" name="quantity" required 
                               value="<?php echo isset($editDrug) ? htmlspecialchars($editDrug['quantity']) : ''; ?>">
                    </div>

                    <button type="submit" class="submit-btn">Update Drug</button>
                    <button type="button" onclick="location.href='<?php echo $_SERVER['PHP_SELF']; ?>'" class="cancel-btn">Cancel</button>
                </form>
            </div>

            <!-- Current Inventory -->
            <div class="drug-list">
                <h2>Current Inventory</h2>
                <?php if (!empty($drugs)): ?>
                    <table class="drug-table">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Category</th>
                                <th>Price (LKR)</th>
                                <th>Quantity</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($drugs as $drug): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($drug['name']); ?></td>
                                    <td><?php echo htmlspecialchars($drug['category']); ?></td>
                                    <td><?php echo htmlspecialchars($drug['price']); ?></td>
                                    <td><?php echo htmlspecialchars($drug['quantity']); ?></td>
                                    <td class="action-buttons">
                                        <a href="?edit=<?php echo $drug['druG_ID']; ?>" class="action-btn edit-btn">
                                            <i class="fas fa-edit"></i> Edit
                                        </a>
                                        <a href="?delete=<?php echo $drug['druG_ID']; ?>" class="action-btn delete-btn" 
                                           onclick="return confirm('Are you sure you want to delete this drug?');">
                                            <i class="fas fa-trash-alt"></i> Delete
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <p>No drugs found in the inventory.</p>
                <?php endif; ?>
            </div>
        </div>
    </main>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Handle message animations
        const messageContainer = document.querySelector('.message-container');
        if (messageContainer) {
            const messageBox = messageContainer.querySelector('.message-box');
            
            // Automatically remove the message after 3 seconds
            setTimeout(() => {
                messageBox.style.animation = 'fadeOut 0.5s ease-out forwards';
                setTimeout(() => {
                    messageContainer.remove();
                }, 500);
            }, 3000);
        }

        // Add confirmation for delete action
        const deleteButtons = document.querySelectorAll('.delete-btn');
        deleteButtons.forEach(button => {
            button.addEventListener('click', (e) => {
                if (!confirm('Are you sure you want to delete this drug?')) {
                    e.preventDefault();
                }
            });
        });
    });
</script>
</body>
</html>