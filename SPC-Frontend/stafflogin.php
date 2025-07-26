<?php
session_start();
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST["EMAIL"] ?? '';
    $password = $_POST["PASSWORD"] ?? '';
    
    // Check for admin credentials
    if ($email === "Admin@a" && $password === "123") {
        $_SESSION['EMAIL'] = $email;
        $_SESSION['IS_ADMIN'] = true;
        header("Location: admindashboard.php");
        exit();
    }
    $data = array(
        "EMAIL" => $email,
        "PASSWORD" => $password
    );
    $ch = curl_init();
    $url = "http://localhost:5268/api/Staff/StaffLogin";  // Replace with your API URL
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
        // Decode the response and check for errors
        $response_data = json_decode($response, true);
        // Check if decoding was successful
        if (json_last_error() != JSON_ERROR_NONE) {
            echo '<script>alert("Error decoding response: ' . json_last_error_msg() . '");</script>';
        } else {
            // Assuming your API sends back a response with StatusCode and StatusMessage
            if (isset($response_data['statusCode'])) {
                if ($response_data['statusCode'] == 200) {
                    $_SESSION['EMAIL'] = $email;
                    echo '<script>alert("Login Successful!");</script>';
                    // Redirect to another page (e.g., dashboard) after successful login
                     header("Location: staffdashboard.php");
                } else {
                    echo '<script>alert("Invalid email or password!");</script>';
                }
            } else {
                echo '<script>alert("Unexpected response format!");</script>';
            }
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
    <title>SPC Staff Login</title>
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
                        url('Images/staff3.jpg');
            background-size: cover;
            background-position: center;
            background-attachment: fixed;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem;
        }

        .login-container {
            background-color: white;
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
            width: 100%;
            max-width: 450px;
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

        .login-header {
            text-align: center;
            margin-bottom: 2rem;
        }

        .login-header .logo {
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

        .login-header h1 {
            color: var(--primary-color);
            font-size: 1.8rem;
            margin-bottom: 0.5rem;
        }

        .login-header p {
            color: var(--dark-gray);
            opacity: 0.8;
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

        .form-group input {
            width: 100%;
            padding: 0.8rem 1rem 0.8rem 2.5rem;
            border: 2px solid #e1e1e1;
            border-radius: 8px;
            font-size: 1rem;
            transition: all 0.3s;
        }

        .form-group input:focus {
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

        .login-footer {
            text-align: center;
            margin-top: 2rem;
            color: var(--dark-gray);
        }

        .login-footer a {
            color: var(--secondary-color);
            text-decoration: none;
            font-weight: 500;
        }

        .login-footer a:hover {
            text-decoration: underline;
        }

        @media (max-width: 480px) {
            .login-container {
                padding: 2rem;
            }

            .login-header h1 {
                font-size: 1.5rem;
            }
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-header">
            <div class="logo">
                <i class="fas fa-clinic-medical"></i>
                <span>SPC</span>
            </div>
            <h1>Staff Login</h1>
            <p>Access your staff dashboard</p>
        </div>

        <form method="POST" action="">
            <div class="form-group">
                <label for="email">Email Address</label>
                <div class="input-group">
                    <i class="fas fa-envelope"></i>
                    <input type="email" id="email" name="EMAIL" placeholder="Enter your email" required>
                </div>
            </div>

            <div class="form-group">
                <label for="password">Password</label>
                <div class="input-group">
                    <i class="fas fa-lock"></i>
                    <input type="password" id="password" name="PASSWORD" placeholder="Enter your password" required>
                </div>
            </div>

            <div class="form-group">
                <button type="submit">Login</button>
            </div>
        </form>

        <div class="login-footer">
            <p>Don't have an account? <a href="staffreg.php">Register Now</a></p>
            <p style="margin-top: 1rem;">Need help? <a href="support.php">Contact Support</a></p>
        </div>
    </div>
</body>
</html>