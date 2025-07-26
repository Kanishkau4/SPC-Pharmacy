<?php
session_start();
if (!isset($_SESSION['EMAIL'])) {
    header("Location: suplogin.php");
    exit();
}

// Fetch All Tenders with updated status
$ch = curl_init('http://localhost:5268/api/Tender/GetAllTenders');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($httpCode == 200) {
    $tenders = json_decode($response, true);
} else {
    $tenders = null;
    $error = "Failed to fetch tenders. Status Code: $httpCode";
}

$email = $_SESSION['EMAIL']; // Get the logged-in supplier's email

// Fetch proposals for the logged-in supplier
$ch = curl_init('http://localhost:5268/api/Proposal/GetProposalsBySupplier?email=' . urlencode($email));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($httpCode == 200) {
    $allProposals = json_decode($response, true);
    
    // Count pending proposals
    $pendingCount = count(array_filter($allProposals, function($proposal) {
        return strtolower($proposal['status']) === 'pending';
    }));

    // Count approved proposals
    $approvedCount = count(array_filter($allProposals, function($proposal) {
        return strtolower($proposal['status']) === 'accepted';
    }));
} else {
    $allProposals = null;
    $pendingCount = 0;
    $approvedCount = 0;
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Supplier Dashboard - SPC</title>
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

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        .stat-card {
            background: white;
            padding: 1.5rem;
            border-radius: 15px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
            transition: transform 0.2s;
        }

        .stat-card:hover {
            transform: translateY(-5px);
        }

        .stat-card i {
            font-size: 2rem;
            color: var(--primary-color);
            margin-bottom: 0.5rem;
        }

        .stat-card h3 {
            color: var(--dark-gray);
            font-size: 1.1rem;
        }

        .stat-card .value {
            font-size: 2rem;
            color: var(--primary-color);
            font-weight: bold;
        }

        .tenders-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 1.5rem;
            margin-top: 2rem;
        }

        .tender-card {
            background: white;
            border-radius: 15px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            overflow: hidden;
            transition: transform 0.2s;
        }

        .tender-card:hover {
            transform: translateY(-5px);
        }

        .tender-header {
            background-color: var(--primary-color);
            color: white;
            padding: 1rem;
            font-size: 1.2rem;
            font-weight: bold;
        }

        .tender-body {
            padding: 1.5rem;
        }

        .tender-info {
            margin-bottom: 1.5rem;
        }

        .tender-info-item {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            margin-bottom: 0.8rem;
            color: var(--dark-gray);
        }

        .tender-info-item i {
            color: var(--primary-color);
            width: 20px;
        }

        .tender-status {
            background-color: #e8f5e9;
            color: var(--accent-color);
            display: inline-block;
            padding: 0.4rem 1rem;
            border-radius: 20px;
            font-size: 0.9rem;
            margin-bottom: 1rem;
        }

        .tender-actions {
            display: flex;
            gap: 1rem;
            margin-top: 1rem;
        }

        .btn {
            padding: 0.8rem 1.2rem;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            color: white;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            flex: 1;
            justify-content: center;
            transition: opacity 0.2s;
        }

        .btn:hover {
            opacity: 0.9;
        }

        .btn-view {
            background-color: var(--secondary-color);
        }

        .btn-submit {
            background-color: var(--accent-color);
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
            
            .stats-grid {
                grid-template-columns: 1fr;
            }
            
            .tenders-grid {
                grid-template-columns: 1fr;
            }
        }
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            justify-content: center;
            align-items: center;
        }

        .modal-content {
            background-color: white;
            padding: 2rem;
            border-radius: 15px;
            max-width: 600px;
            width: 90%;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
        }

        .modal-header {
            font-size: 1.5rem;
            font-weight: bold;
            margin-bottom: 1.5rem;
            color: var(--primary-color);
        }

        .modal-body {
            margin-bottom: 1.5rem;
        }

        .modal-body .tender-info-item {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            margin-bottom: 0.8rem;
            color: var(--dark-gray);
        }

        .modal-body .tender-info-item i {
            color: var(--primary-color);
            width: 20px;
        }

        .modal-close {
            background-color: var(--danger-color);
            color: white;
            padding: 0.8rem 1.2rem;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }

        .modal-close:hover {
            opacity: 0.9;
        }

        /* Tender Grid Layout */
        .tenders-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr); /* Three cards per row */
            gap: 1.5rem;
            margin-top: 2rem;
        }

        @media (max-width: 1200px) {
            .tenders-grid {
                grid-template-columns: repeat(2, 1fr); /* Two cards per row for smaller screens */
            }
        }

        @media (max-width: 768px) {
            .tenders-grid {
                grid-template-columns: 1fr; /* One card per row for mobile */
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
            <h1>Supplier Dashboard</h1>
            <p>Welcome back, <span id="supplierName">Supplier</span></p>
        </div>

        <div class="stats-grid">
            <div class="stat-card">
                <i class="fas fa-file-contract"></i>
                <h3>Active Tenders</h3>
                <div class="value"><?php echo $tenders ? count($tenders) : 0; ?></div>
            </div>
            <div class="stat-card">
                <i class="fas fa-clock"></i>
                <h3>Pending Proposals</h3>
                <div class="value"><?php echo $pendingCount; ?></div>
            </div>
            <div class="stat-card">
                <i class="fas fa-check-circle"></i>
                <h3>Approved Proposals</h3>
                <div class="value"><?php echo $approvedCount; ?></div>
            </div>
        </div>

        <?php if (isset($error)): ?>
            <div class="error-message">
                <i class="fas fa-exclamation-circle"></i>
                <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>

        <div class="tenders-grid">
            <?php if ($tenders !== null && count($tenders) > 0): ?>
                <?php foreach ($tenders as $tender): ?>
                    <div class="tender-card">
                        <div class="tender-header">
                            <?php echo htmlspecialchars($tender['tenderName']); ?>
                        </div>
                        <div class="tender-body">
                            <div class="tender-status <?php echo ($tender['status'] === 'Awarded' ? 'status-active' : ''); ?>">
                                <?php echo htmlspecialchars($tender['status'] ?? 'Active'); ?>
                            </div>
                            <div class="tender-info">
                                <div class="tender-info-item">
                                    <i class="fas fa-hashtag"></i>
                                    <span>ID: <?php echo htmlspecialchars($tender['tenderId']); ?></span>
                                </div>
                                <div class="tender-info-item">
                                    <i class="fas fa-pills"></i>
                                    <span>Drug: <?php echo htmlspecialchars($tender['drugName']); ?></span>
                                </div>
                                <div class="tender-info-item">
                                    <i class="fas fa-box"></i>
                                    <span>Quantity: <?php echo htmlspecialchars($tender['quantity']); ?></span>
                                </div>
                                <div class="tender-info-item">
                                    <i class="fas fa-calendar-alt"></i>
                                    <span>Deadline: <?php echo htmlspecialchars($tender['submissionDeadline']); ?></span>
                                </div>
                            </div>
                            <div class="tender-actions">
                                <button class="btn btn-view" onclick="openModal(<?php echo htmlspecialchars(json_encode($tender)); ?>)">
                                    <i class="fas fa-eye"></i> View Details
                                </button>
                                <?php if ($tender['status'] !== 'Awarded'): ?>
                                    <a href="submit-proposal.php?id=<?php echo htmlspecialchars($tender['tenderId']); ?>" class="btn btn-submit">
                                        <i class="fas fa-pen"></i> Submit
                                    </a>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p style="grid-column: 1/-1; text-align: center; padding: 2rem;">No tenders found or failed to load tenders.</p>
            <?php endif; ?>
        </div>
    </main>

    <!-- Popup Modal -->
    <div id="tenderModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <span id="modalTenderName"></span>
            </div>
            <div class="modal-body">
                <div class="tender-info-item">
                    <i class="fas fa-hashtag"></i>
                    <span>ID: <span id="modalTenderId"></span></span>
                </div>
                <div class="tender-info-item">
                    <i class="fas fa-pills"></i>
                    <span>Drug: <span id="modalDrugName"></span></span>
                </div>
                <div class="tender-info-item">
                    <i class="fas fa-box"></i>
                    <span>Quantity: <span id="modalQuantity"></span></span>
                </div>
                <div class="tender-info-item">
                    <i class="fas fa-clipboard-list"></i>
                    <span>Specifications: <span id="modalSpecifications"></span></span>
                </div>
                <div class="tender-info-item">
                    <i class="fas fa-calendar-alt"></i>
                    <span>Deadline: <span id="modalDeadline"></span></span>
                </div>
                <div class="tender-info-item">
                    <i class="fas fa-file-contract"></i>
                    <span>Contract Terms: <span id="modalContractTerms"></span></span>
                </div>
            </div>
            <button class="modal-close" onclick="closeModal()">
                <i class="fas fa-times"></i> Close
            </button>
        </div>
    </div>

    <script>
        // JavaScript for Modal
        function openModal(tender) {
            document.getElementById('modalTenderName').innerText = tender.tenderName;
            document.getElementById('modalTenderId').innerText = tender.tenderId;
            document.getElementById('modalDrugName').innerText = tender.drugName;
            document.getElementById('modalQuantity').innerText = tender.quantity;
            document.getElementById('modalSpecifications').innerText = tender.specifications;
            document.getElementById('modalDeadline').innerText = tender.submissionDeadline;
            document.getElementById('modalContractTerms').innerText = tender.contractTerms;
            document.getElementById('tenderModal').style.display = 'flex';
        }

        function closeModal() {
            document.getElementById('tenderModal').style.display = 'none';
        }
    </script>
</body>
</html>

