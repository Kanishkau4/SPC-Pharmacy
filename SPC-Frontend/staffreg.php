<?php
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id = $_POST['STAFF_ID'] ?? '';
    $firstname = $_POST["FIRSTNAME"] ?? '';
    $lastname = $_POST["LASTNAME"] ?? '';
    $email = $_POST["EMAIL"] ?? '';
    $password = $_POST["PASSWORD"] ?? '';
    $address = $_POST["ADDRESS"] ?? '';
    $data = array(
        "id" => $id,
        "firstname" => $firstname,
        "lastname" => $lastname,
        "email" => $email,
        "password" => $password,
        "address" => $address
    );
    $ch = curl_init();
    $url = "http://localhost:5268/api/Staff/Addstaff";
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
        'Content-Type: application/json'
    ));
    $response = curl_exec($ch);
    if (curl_errno($ch)) {
        echo '<script>alert("Error: ' . curl_error($ch) . '");</script>';
    } else {
        echo '<script>alert("Staff Member Successfully Added");</script>';
    }
    curl_close($ch);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SPC Staff Registration</title>
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
            background: linear-gradient(rgba(26, 95, 122, 0.9), rgba(26, 95, 122, 0.95)),
                        url('Images/staff5.jpg');
            background-size: cover;
            background-position: center;
            background-attachment: fixed;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem;
        }

        .register-container {
            background-color: white;
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
            width: 100%;
            max-width: 600px;
            padding: 2.5rem;
            animation: fadeIn 0.5s ease-out;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(-20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .register-header {
            text-align: center;
            margin-bottom: 2rem;
        }

        .register-header .logo {
            font-size: 2rem;
            color: var(--primary-color);
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            margin-bottom: 1rem;
        }

        .logo i {
            color: var(--accent-color);
        }

        .register-header h1 {
            color: var(--primary-color);
            font-size: 1.8rem;
            margin-bottom: 0.5rem;
        }

        .register-header p {
            color: var(--dark-gray);
            opacity: 0.8;
        }

        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1rem;
            margin-bottom: 1rem;
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-group label {
            display: block;
            color: var(--dark-gray);
            margin-bottom: 0.5rem;
            font-weight: 500;
        }

        .input-group {
            position: relative;
        }

        .input-group i {
            position: absolute;
            left: 1rem;
            top: 50%;
            transform: translateY(-50%);
            color: var(--primary-color);
        }

        .form-group input, .form-group select {
            width: 100%;
            padding: 0.8rem 1rem 0.8rem 2.5rem;
            border: 2px solid #e1e1e1;
            border-radius: 8px;
            font-size: 1rem;
            transition: all 0.3s;
        }

        .form-group input:focus, .form-group select:focus {
            border-color: var(--secondary-color);
            outline: none;
            box-shadow: 0 0 0 3px rgba(45, 156, 219, 0.2);
        }

        .form-group button {
            width: 100%;
            padding: 1rem;
            background-color: var(--accent-color);
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
        }

        .form-group button:hover {
            background-color: #219653;
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(39, 174, 96, 0.3);
        }

        .register-footer {
            text-align: center;
            margin-top: 2rem;
            color: var(--dark-gray);
        }

        .register-footer a {
            color: var(--secondary-color);
            text-decoration: none;
            font-weight: 500;
        }

        .register-footer a:hover {
            text-decoration: underline;
        }

        @media (max-width: 768px) {
            .form-row {
                grid-template-columns: 1fr;
            }
            
            .register-container {
                padding: 2rem;
            }
        }
    </style>
</head>
<body>
    <div class="register-container">
        <div class="register-header">
            <div class="logo">
                <i class="fas fa-clinic-medical"></i>
                <span>SPC</span>
            </div>
            <h1>Staff Registration</h1>
            <p>Create a new staff account</p>
        </div>

        <form method="POST" action="" onsubmit="return validateForm()">
        <div class="form-row">
            <div class="form-group">
                <label for="firstname">First Name</label>
                <div class="input-group">
                    <i class="fas fa-user"></i>
                    <input type="text" id="firstname" name="FIRSTNAME" placeholder="Enter first name" required>
                </div>
            </div>

            <div class="form-group">
                <label for="lastname">Last Name</label>
                <div class="input-group">
                    <i class="fas fa-user"></i>
                    <input type="text" id="lastname" name="LASTNAME" placeholder="Enter last name" required>
                </div>
            </div>
        </div>

        <div class="form-group">
            <label for="email">Email Address</label>
            <div class="input-group">
                <i class="fas fa-envelope"></i>
                <input type="email" id="email" name="EMAIL" placeholder="Enter email address" required>
            </div>
        </div>

        <div class="form-group">
            <label for="password">Password</label>
            <div class="input-group">
                <i class="fas fa-lock"></i>
                <input type="password" id="password" name="PASSWORD" placeholder="Enter password" required>
            </div>
        </div>

        <div class="form-group">
            <label for="address">Address</label>
            <div class="input-group">
                <i class="fas fa-map-marker-alt"></i>
                <input type="text" id="address" name="ADDRESS" placeholder="Enter address" required>
            </div>
        </div>

        <div class="form-group">
            <button type="submit">Register Staff</button>
        </div>

        <div class="register-footer">
            <p>Already have an account? <a href="stafflogin.php">Login here</a></p>
            <p style="margin-top: 1rem;">Need help? <a href="support.php">Contact Support</a></p>
        </div>
    </div>

    <script>
        function validateForm() {
            const email = document.getElementById('email').value;
            const password = document.getElementById('password').value;
            const firstname = document.getElementById('firstname').value;
            const lastname = document.getElementById('lastname').value;

            if (!email || !password || !firstname || !lastname) {
                alert('Please fill in all required fields');
                return false;
            }

            return true;
        }
    </script>
</body>
</html>
