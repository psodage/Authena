<?php
// Start session
session_start();

// Database connection
$conn = new mysqli("localhost", "root", "", "authena");

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Handle form submission
$message = "";
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST["email"]);
    $password = trim($_POST["password"]);
    
    // Special case for admin login
    if ($email === "admin" && $password === "admin") {
        // Set admin session if needed
        $_SESSION['user_id'] = 0; // Special admin ID
        $_SESSION['username'] = 'Administrator';
        $_SESSION['is_admin'] = true;
        
        // Redirect to admin dashboard
        header("Location: admin/admin-dashboard.php");
        exit(); // Important to prevent further execution
    }
    
    // Regular user authentication
    // Prepare SQL statement to prevent SQL injection
    $stmt = $conn->prepare("SELECT id, username, password FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();
        
        // Verify password
        if (password_verify($password, $user['password'])) {
            // Password is correct, start a new session
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            
            // Redirect to homepage
            header("Location: index.php");
            exit(); // Important to prevent further execution
        } else {
            $message = "Invalid password!";
        }
    } else {
        $message = "Email not found!";
    }
    
    $stmt->close();
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - VerifyTag</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <!-- Include your main stylesheet if you have one -->
    <link rel="stylesheet" href="css/style.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
            background: #f9f9f9;
        }
        .container {
            display: flex;
            background: #fff;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0px 0px 10px rgba(0, 0, 0, 0.1);
        }
        .form-container {
            width: 300px;
            padding-right: 20px;
        }
        .form-container h2 {
            margin-bottom: 20px;
        }
        .input-field {
            width: 100%;
            margin-bottom: 15px;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 5px;
            display: flex;
            align-items: center;
        }
        .input-field input {
            border: none;
            outline: none;
            width: 100%;
            margin-left: 10px;
        }
        .login-btn {
            width: 100%;
            padding: 10px;
            border: none;
            background-color: #00C6FF;
            color: white;
            font-size: 16px;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.4s ease, transform 0.4s ease;
        }
        .login-btn:hover {
            background: linear-gradient(to right, #00C6FF, #0072FF);
            background-color: #0072FF;
            transform: scale(1.05);
        }
        .forgot-password {
            margin-top: 10px;
            text-align: center;
        }
        .forgot-password a {
            color: #5d99e5;
            text-decoration: none;
        }
        .not-registered {
            margin-top: 10px;
            text-align: center;
        }
        .not-registered a {
            color: #5d99e5;
            text-decoration: none;
        }
        .image-container img {
            width: 250px;
            margin-left: 25px;
        }
        .back-home {
            margin-top: 10px;
            text-align: center;
        }
        .back-home a {
            color: #4a90e2;
            text-decoration: none;
            display: flex;
            margin-bottom: 10px;
        }
        .message {
            text-align: center;
            margin-bottom: 10px;
            color: red;
        }
        .gradient-text {
            background: linear-gradient(to right, #4d6d95, #36ddde);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            font-size: 40px;
            margin-top: -10px;
        }
        .back-home a,
        .not-registered a,
        .forgot-password a {
            color: #4a90e2;
            text-decoration: none;
            transition: color 0.3s ease, transform 0.3s ease;
        }
        .back-home a:hover,
        .not-registered a:hover,
        .forgot-password a:hover {
            color: #0072FF;
            transform: scale(1.05);
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="form-container">
            <h2 class="gradient-text">Login</h2>

            <?php if (!empty($message)) { echo "<p class='message'>$message</p>"; } ?>

            <form action="" method="POST">
                <div class="input-field">
                    <i class="fa fa-envelope"></i>
                    <input type="text" name="email" placeholder="Your Email" required>
                </div>
                <div class="input-field">
                    <i class="fa fa-lock"></i>
                    <input type="password" name="password" placeholder="Password" required>
                </div>
                <div class="back-home">
                    <a href="index.php"><i class="fa fa-arrow-left">&nbsp;</i>Back to Home</a>
                </div>
                <button class="login-btn" type="submit">Login</button>
                <div class="not-registered">
                    <a href="signup.php">Not registered? Create an account</a>
                </div>
                <div class="forgot-password">
                    <a href="forgot-password.php">Forgot your password?</a>
                </div>
            </form>
        </div>
        <div class="image-container">
            <img src="assets/1.jpg" alt="Illustration">
        </div>
    </div>
</body>
</html>