<?php
session_start();
// Check if user is logged in and is admin
if (!isset($_SESSION['EMAIL']) || $_SESSION['EMAIL'] !== 'Admin@a') {
    header("Location: stafflogin.php");
    exit();
}

// API base URL
$api_base_url = "http://localhost:5268/api";

// Handle form submissions
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action'])) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));

    switch ($_POST['action']) {
        case 'create_tender':
            $tender_data = array(
                "TenderName" => $_POST['tender_title'],
                "DrugName" => $_POST['drug_name'],
                "Quantity" => intval($_POST['quantity']),
                "Specifications" => $_POST['specifications'],
                "SubmissionDeadline" => $_POST['deadline'],
                "ContractTerms" => $_POST['contract_terms']
            );
            
            curl_setopt($ch, CURLOPT_URL, "$api_base_url/Tender/AddTender");
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($tender_data));
            break;

            case 'update_tender':
                $tender_data = array(
                    "TenderId" => $_POST['tenderId'],
                    "TenderName" => $_POST['tender_title'],
                    "DrugName" => $_POST['drug_name'],
                    "Quantity" => intval($_POST['quantity']),
                    "Specifications" => $_POST['specifications'],
                    "SubmissionDeadline" => $_POST['deadline'],
                    "ContractTerms" => $_POST['contract_terms']
                );
                
                $tender_id = $_POST['tenderId'];
                curl_setopt($ch, CURLOPT_URL, "$api_base_url/Tender/UpdateTender/$tender_id");
                curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT");
                curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($tender_data));
                break;

                case 'accept_proposal':
                    $tender_id = $_POST['tenderId'];
                    curl_setopt($ch, CURLOPT_URL, "$api_base_url/Proposal/AcceptProposal/$tender_id");
                    curl_setopt($ch, CURLOPT_POST, true);
                    break;

                case 'delete_tender':
                $tender_id = $_POST['tenderId'];
                curl_setopt($ch, CURLOPT_URL, "$api_base_url/Tender/DeleteTender/$tender_id");
                curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "DELETE");
                break;

                case 'delete_drug_request':
                    $requestId = $_POST['requestId'];
                    curl_setopt($ch, CURLOPT_URL, "$api_base_url/Request/DeleteDrugRequest/$requestId");
                    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "DELETE");
                    break;

                case 'send_message':
                $messageData = array(
                    "Subject" => $_POST['subject'],
                    "Body" => $_POST['body']
                );
                curl_setopt($ch, CURLOPT_URL, "$api_base_url/Message/SendMessageToStaff");
                curl_setopt($ch, CURLOPT_POST, true);
                curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($messageData));
                break;

                case 'delete_supplier':
                    $supplierId = $_POST['supplieR_ID'];
                    curl_setopt($ch, CURLOPT_URL, "$api_base_url/Supplier/DeleteSupplier/$supplierId");
                    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "DELETE");
                    break;
                
                case 'delete_pharmacy':
                    $pharmacyId = $_POST['pharmacY_ID'];
                    curl_setopt($ch, CURLOPT_URL, "$api_base_url/Pharmacy/DeletePharmacy/$pharmacyId");
                    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "DELETE");
                    break;
    }

    $response = curl_exec($ch);
    $response_data = json_decode($response, true);
    
    if (curl_errno($ch)) {
        $message = "Error: " . curl_error($ch);
    } else {
        $message = $response_data['statusMessage'] ?? "Operation completed successfully";
    }
    
    curl_close($ch);
}


// Fetch current data
function fetchData($endpoint) {
    $ch = curl_init("http://localhost:5268/api/$endpoint");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $response = curl_exec($ch);
    curl_close($ch);
    return json_decode($response, true) ?? [];
}


$tenders = fetchData("Tender/GetAllTenders");
$suppliers = fetchData("Supplier/GetAllSuppliers");
$pharmacies = fetchData("Pharmacy/GetAllPharmacies");
$proposals = fetchData("Proposal/GetAllProposals");
$drugRequests = fetchData("Request/GetAllDrugRequests");
$messages = fetchData("Message/GetStaffMessages");
// Fetch replies for each message
foreach ($messages as &$messagess) {
    $messageId = $messagess['messageId'];
    $replies = fetchData("Reply/GetRepliesForMessage/$messageId");
    $messagess['Replies'] = $replies;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - SPC</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root {
            --primary-color: #1a5f7a;
            --secondary-color: #2d9cdb;
            --accent-color: #27ae60;
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

        .dashboard-container {
            display: flex;
            min-height: 100vh;
        }

        /* Sidebar Styles */
        .sidebar {
            width: 250px;
            background-color: var(--primary-color);
            color: white;
            padding: 2rem 1rem;
        }

        .logo {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 1.5rem;
            margin-bottom: 2rem;
            padding: 0 1rem;
        }

        .nav-item {
            padding: 0.8rem 1rem;
            margin: 0.5rem 0;
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.3s;
        }

        .nav-item:hover {
            background-color: rgba(255, 255, 255, 0.1);
        }

        .nav-item.active {
            background-color: var(--accent-color);
        }

        /* Main Content Styles */
        .main-content {
            flex: 1;
            padding: 2rem;
        }

        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
        }

        .card {
            background-color: white;
            border-radius: 10px;
            padding: 1.5rem;
            margin-bottom: 1.5rem;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        .grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        .stats-card {
            background-color: white;
            border-radius: 10px;
            padding: 1.5rem;
            text-align: center;
        }

        .stats-card h3 {
            color: var(--dark-gray);
            margin-bottom: 0.5rem;
        }

        .stats-card .number {
            font-size: 2rem;
            color: var(--accent-color);
            font-weight: bold;
        }

        /* Form Styles */
        .form-group {
            margin-bottom: 1rem;
        }

        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            color: var(--dark-gray);
        }

        .form-group input,
        .form-group textarea,
        .form-group select {
            width: 100%;
            padding: 0.8rem;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 1rem;
        }

        .btn {
            padding: 0.8rem 1.5rem;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-weight: 600;
            transition: all 0.3s;
        }

        .btn-primary {
            background-color: var(--accent-color); 
            color: white;
            border: none;
            padding: 10px 20px;
            font-size: 16px;
            font-weight: bold;
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        .btn-primary:hover {
            background-color: #219653; 
            transform: translateY(-2px);
            box-shadow: 0 6px 8px rgba(0, 0, 0, 0.2);
        }

        .btn-primary:active {
            background-color: #1e7e34; 
            transform: translateY(0);
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        .btn-primary:focus {
            outline: none;
            border: 2px solid #219653; 
        }

        .btn-danger {
            background-color: #ff4d4d;
            color: white;
            border: none;
            padding: 10px 20px;
            font-size: 16px;
            font-weight: bold;
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        .btn-danger:hover {
            background-color: #ff3333;
            transform: translateY(-2px);
            box-shadow: 0 6px 8px rgba(0, 0, 0, 0.2);
        }

        .btn-danger:active {
            background-color: #ff1a1a;
            transform: translateY(0);
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        .btn-danger:focus {
            outline: none;
            border: 2px solid #ff9999;
        }

        /* Table Styles */
        .table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 1rem;
        }

        .table th,
        .table td {
            padding: 1rem;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }

        .table th {
            background-color: var(--light-gray);
            color: var(--dark-gray);
        }

        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0,0,0,0.5);
            z-index: 1000;
            overflow-y: auto; /* Add scroll if content is too long */
        }

        .modal-content {
            background-color: white;
            margin: 5% auto; /* Changed from 10% to 5% to position higher */
            padding: 20px;
            border-radius: 10px;
            width: 80%;
            max-width: 600px;
            position: relative;
            max-height: 90vh; /* Prevent modal from being taller than viewport */
            overflow-y: auto; /* Add scroll if content is too long */
        }

        /* Add smooth animation for modal */
        @keyframes modalFade {
            from {
                opacity: 0;
                transform: translateY(-20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .modal-content {
            animation: modalFade 0.3s ease-out;
        }

        /* Ensure the close button stays visible */
        .close {
            position: sticky;
            top: 10px;
            float: right;
            font-size: 28px;
            font-weight: bold;
            cursor: pointer;
            z-index: 1;
        }

        .close:hover {
            color: #666;
        }

        /* Additional spacing for form elements */
        .modal-content .form-group {
            margin-bottom: 15px;
        }

        /* Make sure the modal header is clear of the close button */
        .modal-content h2 {
            padding-right: 30px;
            margin-top: 0;
        }

        .tab-content {
            display: none;
        }

        .tab-content.active {
            display: block;
        }

        .table-filters {
            margin-bottom: 1rem;
        }

        .table-filters select {
            padding: 0.5rem;
            border-radius: 4px;
            border: 1px solid #ddd;
        }

        .status-badge {
            padding: 0.25rem 0.5rem;
            border-radius: 4px;
            font-size: 0.875rem;
            font-weight: 500;
        }

        .status-badge.pending {
            background-color: #FEF3C7;
            color: #92400E;
        }

        .status-badge.accepted {
            background-color: #D1FAE5;
            color: #065F46;
        }

        .accepted-text {
            color: #065F46;
            font-weight: 500;
        }

        .proposal-row.accepted {
            background-color: rgba(209, 250, 229, 0.1);
        }

        .replies {
            margin-top: 1rem;
            padding-left: 1rem;
            border-left: 2px solid var(--primary-color);
        }

        .replies ul {
            list-style-type: none;
            padding: 0;
            margin: 0;
        }

        .replies li {
            margin-bottom: 0.5rem;
        }

        .replies strong {
            color: var(--primary-color);
        }

        .replies small {
            color: #666;
        }
    </style>
</head>
<body>
    <div class="dashboard-container">
        <!-- Sidebar -->
        <div class="sidebar">
            <div class="logo">
                <i class="fas fa-clinic-medical"></i>
                <span>SPC Admin</span>
            </div>
            <div class="nav-item active" onclick="showTab('dashboard')">
                <i class="fas fa-home"></i>
                <span>Dashboard</span>
            </div>
            <div class="nav-item" onclick="showTab('tenders')">
                <i class="fas fa-file-contract"></i>
                <span>Manage Tenders</span>
            </div>
            <div class="nav-item" onclick="showTab('suppliers')">
                <i class="fas fa-users"></i>
                <span>Manage Suppliers</span>
            </div>
            <div class="nav-item" onclick="showTab('pharmacies')">
                <i class="fas fa-clinic-medical"></i>
                <span>Manage Pharmacies</span>
            </div>
            <div class="nav-item" onclick="showTab('drugRequests')">
                <i class="fas fa-pills"></i>
                <span>View Drug Requests</span>
            </div>
            <div class="nav-item" onclick="showTab('proposals')">
                <i class="fas fa-clipboard-check"></i>
                <span>Tender Proposals</span>
            </div>
            <div class="nav-item" onclick="showTab('sendMessage')">
                <i class="fas fa-envelope"></i>
                <span>Send Message to Staff</span>
            </div>
            <div class="nav-item" onclick="logout()">
                <i class="fas fa-sign-out-alt"></i>
                <span>Logout</span>
            </div>
        </div>

        <!-- Main Content -->
        <div class="main-content">
            <?php if (isset($message)): ?>
                <div class="alert"><?php echo $message; ?></div>
            <?php endif; ?>

            <!-- Dashboard Tab -->
            <div id="dashboard" class="tab-content active">
                <div class="header">
                    <h1>Admin Dashboard</h1>
                    <div class="user-info">
                        <span>Welcome, Admin</span>
                    </div>
                </div>

                <div class="grid">
                    <div class="stats-card">
                        <h3>Active Tenders</h3>
                        <div class="number"><?php echo count($tenders); ?></div>
                    </div>
                    <div class="stats-card">
                        <h3>Registered Suppliers</h3>
                        <div class="number"><?php echo count($suppliers); ?></div>
                    </div>
                    <div class="stats-card">
                        <h3>Pending Proposals</h3>
                        <div class="number"><?php echo count(array_filter($proposals, fn($request) => $request['status'] === 'Pending')); ?></div>
                    </div>
                    <div class="stats-card">
                        <h3>Registered Pharmacies</h3>
                        <div class="number"><?php echo count($pharmacies); ?></div>
                    </div>
                    <div class="stats-card">
                        <h3>Drug Requests</h3>
                        <div class="number"><?php echo count($drugRequests); ?></div>
                    </div>
                </div>
            </div>

            <!-- Tenders Tab -->
            <div id="tenders" class="tab-content">
                <div class="header">
                    <h1>Manage Tenders</h1>
                    <button class="btn btn-primary" onclick="showModal('createTenderModal')">Create New Tender</button>
                </div>

                <div class="card">
                    <h2>Active Tenders</h2>
                    <table class="table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Title</th>
                                <th>Drug Name</th>
                                <th>Quantity</th>
                                <th>Deadline</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($tenders as $tender): ?>
                            <tr>
                                <td><?php echo $tender['tenderId']; ?></td>
                                <td><?php echo $tender['tenderName']; ?></td>
                                <td><?php echo $tender['drugName']; ?></td>
                                <td><?php echo $tender['quantity']; ?></td>
                                <td><?php echo $tender['submissionDeadline']; ?></td>
                                <td>
                                    <button class="btn btn-primary" onclick="editTender(<?php echo htmlspecialchars(json_encode($tender)); ?>)">Edit</button>
                                    <button class="btn btn-danger" onclick="deleteTender(<?php echo $tender['tenderId']; ?>)">Delete</button>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Suppliers Tab -->
            <div id="suppliers" class="tab-content">
                <div class="header">
                    <h1>Manage Suppliers</h1>
                </div>
                <div class="card">
                    <h2>Registered Suppliers</h2>
                    <table class="table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Phone</th>
                                <th>Address</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($suppliers as $supplier): ?>
                            <tr>
                                <td><?php echo $supplier['supplieR_ID']; ?></td>
                                <td><?php echo $supplier['name']; ?></td>
                                <td><?php echo $supplier['email']; ?></td>
                                <td><?php echo $supplier['phone']; ?></td>
                                <td><?php echo $supplier['address']; ?></td>
                                <td>
                                    <button class="btn btn-danger" onclick="deleteSupplier(<?php echo $supplier['supplieR_ID']; ?>)">Delete</button>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Pharmacies Tab -->
            <div id="pharmacies" class="tab-content">
                <div class="header">
                    <h1>Manage Pharmacies</h1>
                </div>

                <div class="card">
                    <h2>Registered Pharmacies</h2>
                    <table class="table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Phone</th>
                                <th>Address</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($pharmacies as $pharmacy): ?>
                            <tr>
                                <td><?php echo $pharmacy['pharmacY_ID']; ?></td>
                                <td><?php echo $pharmacy['name']; ?></td>
                                <td><?php echo $pharmacy['email']; ?></td>
                                <td><?php echo $pharmacy['contacT_NUMBER']; ?></td>
                                <td><?php echo $pharmacy['address']; ?></td>
                                <td>
                                    <button class="btn btn-danger" onclick="deletePharmacy(<?php echo $pharmacy['pharmacY_ID']; ?>)">Delete</button>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Drug Requests Tab -->
            <div id="drugRequests" class="tab-content">
                <div class="header">
                    <h1>View Drug Requests</h1>
                </div>
                <div class="card">
                    <h2>Drug Requests</h2>
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Request ID</th>
                                <th>Drug Name</th>
                                <th>Drug Category</th>
                                <th>Quantity</th>
                                <th>Pharmacy Email</th>
                                <th>Request Date</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($drugRequests as $request): ?>
                            <tr>
                                <td><?php echo $request['requestId']; ?></td>
                                <td><?php echo $request['drugName']; ?></td>
                                <td><?php echo $request['drugCategory']; ?></td>
                                <td><?php echo $request['quantity']; ?></td>
                                <td><?php echo $request['pharmacyEmail']; ?></td>
                                <td><?php echo $request['requestDate']; ?></td>
                                <td>
                                    <button class="btn btn-danger" onclick="deleteDrugRequest(<?php echo $request['requestId']; ?>)">Delete</button>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Proposals Tab -->
            <div id="proposals" class="tab-content">
                <div class="header">
                    <h1>Tender Proposals</h1>
                </div>
                <div class="card">
                    <h2>Submitted Proposals</h2>
                    <div class="table-filters">
                        <select id="proposalStatusFilter" onchange="filterProposals(this.value)">
                            <option value="all">All Proposals</option>
                            <option value="pending">Pending</option>
                            <option value="accepted">Accepted</option>
                        </select>
                    </div>
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Tender ID</th>
                                <th>Supplier</th>
                                <th>Price</th>
                                <th>Delivery Time</th>
                                <th>Submission Date</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($proposals as $proposal): ?>
                            <tr class="proposal-row <?php echo strtolower($proposal['status']); ?>">
                                <td><?php echo $proposal['tenderId']; ?></td>
                                <td><?php echo $proposal['supplierName']; ?></td>
                                <td>Rs:<?php echo number_format($proposal['price'], 2); ?></td>
                                <td><?php echo $proposal['deliveryTime']; ?> days</td>
                                <td><?php echo date('Y-m-d', strtotime($proposal['submissionDate'])); ?></td>
                                <td>
                                    <span class="status-badge <?php echo strtolower($proposal['status']); ?>">
                                        <?php echo $proposal['status']; ?>
                                    </span>
                                </td>
                                <td>
                                    <?php if ($proposal['status'] !== 'Accepted'): ?>
                                        <button class="btn btn-primary" onclick="acceptProposal(<?php echo $proposal['tenderId']; ?>)">Accept</button>
                                    <?php else: ?>
                                        <span class="accepted-text">Accepted</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Send Message to Staff Tab -->
            <div id="sendMessage" class="tab-content">
                <div class="header">
                    <h1>Send Message to Staff</h1>
                </div>
                <div class="card">
                    <h2>Compose Message</h2>
                    <form method="POST">
                        <input type="hidden" name="action" value="send_message">
                        <div class="form-group">
                            <label for="message_subject">Subject</label>
                            <input type="text" id="message_subject" name="subject" required>
                        </div>
                        <div class="form-group">
                            <label for="message_body">Message</label>
                            <textarea id="message_body" name="body" rows="5" required></textarea>
                        </div>
                        <button type="submit" class="btn btn-primary">Send Message</button>
                    </form>
                </div>

                <div class="card">
                    <h2>Sent Messages</h2>
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Message ID</th>
                                <th>Subject</th>
                                <th>Message</th>
                                <th>Sent Date</th>
                                <th>Replies</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($messages as $message): ?>
                            <tr>
                                <td><?php echo $message['messageId']; ?></td>
                                <td><?php echo htmlspecialchars($message['subject']); ?></td>
                                <td><?php echo htmlspecialchars($message['body']); ?></td>
                                <td><?php echo $message['sentDate']; ?></td>
                                <td>
                                    <?php if (!empty($message['Replies'])): ?>
                                        <ul>
                                            <?php foreach ($message['Replies'] as $reply): ?>
                                                <li>
                                                    <strong><?php echo $reply['replierEmail']; ?>:</strong>
                                                    <?php echo htmlspecialchars($reply['replyText']); ?>
                                                    <small>(<?php echo $reply['replyDate']; ?>)</small>
                                                </li>
                                            <?php endforeach; ?>
                                        </ul>
                                    <?php else: ?>
                                        <p>No replies yet.</p>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>

    <!-- Create Tender Modal -->
    <div id="createTenderModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="hideModal('createTenderModal')">&times;</span>
            <h2>Create New Tender</h2>
            <form method="POST">
                <input type="hidden" name="action" value="create_tender">
                <div class="form-group">
                    <label for="tender_title">Tender Title</label>
                    <input type="text" id="tender_title" name="tender_title" required>
                </div>
                <div class="form-group">
                    <label for="drug_name">Drug Name</label>
                    <input type="text" id="drug_name" name="drug_name" required>
                </div>
                <div class="form-group">
                    <label for="quantity">Quantity</label>
                    <input type="number" id="quantity" name="quantity" required>
                </div>
                <div class="form-group">
                    <label for="specifications">Specifications</label>
                    <textarea id="specifications" name="specifications" required></textarea>
                </div>
                <div class="form-group">
                    <label for="deadline">Submission Deadline</label>
                    <input type="date" id="deadline" name="deadline" required>
                </div>
                <div class="form-group">
                    <label for="contract_terms">Contract Terms</label>
                    <textarea id="contract_terms" name="contract_terms" required></textarea>
                </div>
                <button type="submit" class="btn btn-primary">Create Tender</button>
            </form>
        </div>
    </div>

    <!-- Update Tender Modal -->
    <div id="updateTenderModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="hideModal('updateTenderModal')">&times;</span>
            <h2>Update Tender</h2>
            <form method="POST">
                <input type="hidden" name="action" value="update_tender">
                <input type="hidden" name="tenderId" value="">
                <div class="form-group">
                    <label for="update_tender_title">Tender Title</label>
                    <input type="text" id="update_tender_title" name="tender_title" required>
                </div>
                <div class="form-group">
                    <label for="update_drug_name">Drug Name</label>
                    <input type="text" id="update_drug_name" name="drug_name" required>
                </div>
                <div class="form-group">
                    <label for="update_quantity">Quantity</label>
                    <input type="number" id="update_quantity" name="quantity" required>
                </div>
                <div class="form-group">
                    <label for="update_specifications">Specifications</label>
                    <textarea id="update_specifications" name="specifications" required></textarea>
                </div>
                <div class="form-group">
                    <label for="update_deadline">Submission Deadline</label>
                    <input type="date" id="update_deadline" name="deadline" required>
                </div>
                <div class="form-group">
                    <label for="update_contract_terms">Contract Terms</label>
                    <textarea id="update_contract_terms" name="contract_terms" required></textarea>
                </div>
                <button type="submit" class="btn btn-primary">Update Tender</button>
            </form>
        </div>
    </div>

    <script>
        function showTab(tabId) {
            // Hide all tabs
            document.querySelectorAll('.tab-content').forEach(tab => {
                tab.classList.remove('active');
            });
            // Show selected tab
            document.getElementById(tabId).classList.add('active');
            // Update active state in sidebar
            document.querySelectorAll('.nav-item').forEach(item => {
                item.classList.remove('active');
            });
            event.currentTarget.classList.add('active');
        }

        function showModal(modalId) {
            document.getElementById(modalId).style.display = 'block';
        }

        function hideModal(modalId) {
            document.getElementById(modalId).style.display = 'none';
        }

        function editTender(tender) {
            // Populate and show the update tender modal
            showModal('updateTenderModal');
            
            // Populate form fields
            document.getElementById('update_tender_title').value = tender.tenderName;
            document.getElementById('update_drug_name').value = tender.drugName;
            document.getElementById('update_quantity').value = tender.quantity;
            document.getElementById('update_specifications').value = tender.specifications;
            document.getElementById('update_deadline').value = tender.submissionDeadline.split('T')[0];
            document.getElementById('update_contract_terms').value = tender.contractTerms;

            // Set the tenderId in the hidden input
            document.querySelector('#updateTenderModal input[name="tenderId"]').value = tender.tenderId;
        }

        function deleteTender(tenderId) {
            if (confirm('Are you sure you want to delete this tender? This action cannot be undone.')) {
                const formData = new FormData();
                formData.append('action', 'delete_tender');
                formData.append('tenderId', tenderId);

                fetch(window.location.href, {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.text())
                .then(result => {
                    // Show success message
                    alert('Tender deleted successfully');
                    // Refresh the page to reflect the changes
                    location.reload();
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Failed to delete tender. Please try again.');
                });
            }
        }

        function filterProposals(status) {
    const rows = document.querySelectorAll('.proposal-row');
    rows.forEach(row => {
        if (status === 'all' || row.classList.contains(status)) {
            row.style.display = '';
        } else {
            row.style.display = 'none';
        }
    });
}

function acceptProposal(tenderId) {
    if (confirm('Are you sure you want to accept this proposal? This action cannot be undone.')) {
        const formData = new FormData();
        formData.append('action', 'accept_proposal');
        formData.append('tenderId', tenderId);

        fetch(window.location.href, {
            method: 'POST',
            body: formData
        })
        .then(response => response.text())
        .then(result => {
            // Show success message
            alert('Proposal accepted successfully');
            // Refresh the page to show updated status
            location.reload();
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Failed to accept proposal. Please try again.');
        });
    }
}

// Optional: Add sorting functionality
function sortTable(columnIndex) {
    const table = document.querySelector('.table');
    const tbody = table.querySelector('tbody');
    const rows = Array.from(tbody.querySelectorAll('tr'));
    
    rows.sort((a, b) => {
        const aValue = a.cells[columnIndex].textContent;
        const bValue = b.cells[columnIndex].textContent;
        return aValue.localeCompare(bValue);
    });
    
    rows.forEach(row => tbody.appendChild(row));
}

function logout() {
    if (confirm('Are you sure you want to logout?')) {
        window.location.href = 'logouts.php';
    }
}

// Close modal when clicking outside of it
window.onclick = function(event) {
    const modals = document.getElementsByClassName('modal');
    for (let modal of modals) {
        if (event.target === modal) {
            modal.style.display = 'none';
        }
    }
}

// Form validation
document.addEventListener('DOMContentLoaded', function() {
    const tenderForm = document.querySelector('#createTenderModal form');
    if (tenderForm) {
        tenderForm.addEventListener('submit', function(e) {
            const deadline = new Date(document.getElementById('deadline').value);
            const today = new Date();
            
            if (deadline < today) {
                e.preventDefault();
                alert('Submission deadline cannot be in the past');
                return false;
            }
            
            const quantity = document.getElementById('quantity').value;
            if (quantity <= 0) {
                e.preventDefault();
                alert('Quantity must be greater than 0');
                return false;
            }
        });
    }
});

// Add error handling for API calls
window.addEventListener('unhandledrejection', function(event) {
    console.error('API Error:', event.reason);
    alert('An error occurred. Please try again later.');
});

// Add date formatting utility
function formatDate(dateString) {
    const options = { 
        year: 'numeric', 
        month: 'short', 
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    };
    return new Date(dateString).toLocaleDateString('en-US', options);
}

// Add dynamic table sorting
function sortTable(table, column) {
    const tbody = table.querySelector('tbody');
    const rows = Array.from(tbody.querySelectorAll('tr'));
    
    rows.sort((a, b) => {
        const aValue = a.cells[column].textContent;
        const bValue = b.cells[column].textContent;
        return aValue.localeCompare(bValue);
    });
    
    rows.forEach(row => tbody.appendChild(row));
}

// Add table search functionality
function searchTable(tableId, searchTerm) {
    const table = document.getElementById(tableId);
    const rows = table.getElementsByTagName('tr');
    
    for (let row of rows) {
        const cells = row.getElementsByTagName('td');
        let found = false;
        
        for (let cell of cells) {
            if (cell.textContent.toLowerCase().includes(searchTerm.toLowerCase())) {
                found = true;
                break;
            }
        }
        
        row.style.display = found ? '' : 'none';
    }
}

// Add form reset functionality
function resetForm(formId) {
    document.getElementById(formId).reset();
}

// Add confirmation for delete operations
function confirmDelete(message = 'Are you sure you want to delete this item?') {
    return confirm(message);
}

// Add responsive sidebar toggle
function toggleSidebar() {
    const sidebar = document.querySelector('.sidebar');
    sidebar.classList.toggle('collapsed');
    
    const mainContent = document.querySelector('.main-content');
    mainContent.classList.toggle('expanded');
}

function deleteDrugRequest(requestId) {
    if (confirm('Are you sure you want to delete this drug request? This action cannot be undone.')) {
        const formData = new FormData();
        formData.append('action', 'delete_drug_request');
        formData.append('requestId', requestId);

        fetch(window.location.href, {
            method: 'POST',
            body: formData
        })
        .then(response => response.text())
        .then(result => {
            // Show success message
            alert('Drug request deleted successfully');
            // Refresh the page to reflect the changes
            location.reload();
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Failed to delete drug request. Please try again.');
        });
    }
}

function deleteSupplier(supplierId) {
    if (confirm('Are you sure you want to delete this supplier? This action cannot be undone.')) {
        const formData = new FormData();
        formData.append('action', 'delete_supplier');
        formData.append('supplieR_ID', supplierId);

        fetch(window.location.href, {
            method: 'POST',
            body: formData
        })
        .then(response => response.text())
        .then(result => {
            alert('Supplier deleted successfully');
            location.reload();
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Failed to delete supplier. Please try again.');
        });
    }
}

function deletePharmacy(pharmacyId) {
    if (confirm('Are you sure you want to delete this pharmacy? This action cannot be undone.')) {
        const formData = new FormData();
        formData.append('action', 'delete_pharmacy');
        formData.append('pharmacY_ID', pharmacyId);

        fetch(window.location.href, {
            method: 'POST',
            body: formData
        })
        .then(response => response.text())
        .then(result => {
            alert('Pharmacy deleted successfully');
            location.reload();
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Failed to delete pharmacy. Please try again.');
        });
    }
}

</script>

</body>
</html>