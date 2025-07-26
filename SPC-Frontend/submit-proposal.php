<?php
// Get tender ID from URL
$tender_id = isset($_GET['id']) ? $_GET['id'] : null;

// Fetch tender details if ID is provided
if ($tender_id) {
    $ch = curl_init("http://localhost:5268/api/Tender/GetTender/$tender_id");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($httpCode == 200) {
        $tender = json_decode($response, true);
    }
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Retrieve form data
    $tender_id = $_POST['tender_id'];
    $supplier_name = $_POST['supplier_name'];
    $price = $_POST['price'];
    $delivery_time = $_POST['delivery_time'];
    $specifications = $_POST['specifications'];
    $additional_notes = $_POST['additional_notes'];

    // Data to send
    $data = array(
        'tenderId' => $tender_id,
        'supplierName' => $supplier_name,
        'price' => $price,
        'deliveryTime' => $delivery_time,
        'specifications' => $specifications,
        'additionalNotes' => $additional_notes
    );

    // API endpoint
    $url = 'http://localhost:5268/api/Proposal/AddProposal';

    // cURL setup
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
        'Content-Type: application/json',
    ));

    // Execute request
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    
    if(curl_errno($ch)) {
        $error_message = 'Error: ' . curl_error($ch);
    } else {
        if ($httpCode == 200) {
            $success_message = "Proposal submitted successfully!";
            // Redirect after 2 seconds
            header("refresh:2;url=supdashboard.php");
        } else {
            $error_message = "Error submitting proposal. Please try again.";
        }
    }
    curl_close($ch);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Submit Proposal - SPC</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root {
            --primary-color: #1a5f7a;
            --secondary-color: #2d9cdb;
            --accent-color: #27ae60;
            --light-gray: #f7f9fc;
            --dark-gray: #2c3e50;
            --danger-color: #e74c3c;
            --success-color: #27ae60;
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
            text-decoration: none;
        }

        .logo i {
            color: var(--accent-color);
        }

        .container {
            max-width: 800px;
            margin: 0 auto;
            padding: 100px 2rem 2rem;
        }

        .tender-details {
            background: white;
            padding: 1.5rem;
            border-radius: 15px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            margin-bottom: 2rem;
        }

        .tender-details h2 {
            color: var(--primary-color);
            margin-bottom: 1rem;
        }

        .tender-info-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
        }

        .tender-info-item {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            color: var(--dark-gray);
        }

        .tender-info-item i {
            color: var(--primary-color);
            width: 20px;
        }

        .proposal-form {
            background: white;
            padding: 2rem;
            border-radius: 15px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        label {
            display: block;
            margin-bottom: 0.5rem;
            color: var(--dark-gray);
            font-weight: 500;
        }

        input[type="text"],
        input[type="number"],
        textarea {
            width: 100%;
            padding: 0.8rem;
            border: 1px solid #ddd;
            border-radius: 8px;
            font-size: 1rem;
        }

        textarea {
            resize: vertical;
            min-height: 100px;
        }

        .btn {
            padding: 1rem 2rem;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-size: 1rem;
            font-weight: 500;
            transition: opacity 0.2s;
        }

        .btn-submit {
            background-color: var(--accent-color);
            color: white;
            width: 100%;
        }

        .btn-submit:hover {
            opacity: 0.9;
        }

        .message {
            padding: 1rem;
            border-radius: 8px;
            margin-bottom: 1rem;
        }

        .success {
            background-color: #d4edda;
            color: var(--success-color);
        }

        .error {
            background-color: #f8d7da;
            color: var(--danger-color);
        }

        @media (max-width: 768px) {
            .container {
                padding: 80px 1rem 1rem;
            }
        }
    </style>
</head>
<body>
    <header>
        <nav>
            <a href="index.php" class="logo">
                <i class="fas fa-clinic-medical"></i>
                <span>SPC</span>
            </a>
        </nav>
    </header>

    <div class="container">
        <?php if (isset($success_message)): ?>
            <div class="message success">
                <i class="fas fa-check-circle"></i>
                <?php echo htmlspecialchars($success_message); ?>
            </div>
        <?php endif; ?>

        <?php if (isset($error_message)): ?>
            <div class="message error">
                <i class="fas fa-exclamation-circle"></i>
                <?php echo htmlspecialchars($error_message); ?>
            </div>
        <?php endif; ?>

        <?php if (isset($tender)): ?>
        <div class="tender-details">
            <h2>Tender Details</h2>
            <div class="tender-info-grid">
                <div class="tender-info-item">
                    <i class="fas fa-hashtag"></i>
                    <span>ID: <?php echo htmlspecialchars($tender['tenderId']); ?></span>
                </div>
                <div class="tender-info-item">
                    <i class="fas fa-file-contract"></i>
                    <span>Name: <?php echo htmlspecialchars($tender['tenderName']); ?></span>
                </div>
                <div class="tender-info-item">
                    <i class="fas fa-pills"></i>
                    <span>Drug: <?php echo htmlspecialchars($tender['drugName']); ?></span>
                </div>
                <div class="tender-info-item">
                    <i class="fas fa-box"></i>
                    <span>Quantity: <?php echo htmlspecialchars($tender['quantity']); ?></span>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <div class="proposal-form">
            <h2>Submit Proposal</h2>
            <form action="" method="post">
                <input type="hidden" name="tender_id" value="<?php echo htmlspecialchars($tender_id); ?>">
                
                <div class="form-group">
                    <label for="supplier_name">Supplier Email</label>
                    <input type="text" id="supplier_name" name="supplier_name" required>
                </div>

                <div class="form-group">
                    <label for="price">Proposed Price (LKR)</label>
                    <input type="number" step="0.01" id="price" name="price" required>
                </div>

                <div class="form-group">
                    <label for="delivery_time">Delivery Time (days)</label>
                    <input type="number" id="delivery_time" name="delivery_time" required>
                </div>

                <div class="form-group">
                    <label for="specifications">Product Specifications</label>
                    <textarea id="specifications" name="specifications" required></textarea>
                </div>

                <div class="form-group">
                    <label for="additional_notes">Additional Notes</label>
                    <textarea id="additional_notes" name="additional_notes"></textarea>
                </div>

                <button type="submit" class="btn btn-submit">
                    <i class="fas fa-paper-plane"></i> Submit Proposal
                </button>
            </form>
        </div>
    </div>
</body>
</html>