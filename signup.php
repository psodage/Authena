<?php
// Start session (optional)
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
    $username = trim($_POST["name"]);
    $email = trim($_POST["email"]);
    $password = trim($_POST["password"]);
    $confirm_password = trim($_POST["confirm_password"]);
    $first_name = trim($_POST["first_name"]);
    $last_name = trim($_POST["last_name"]);
    $phone = trim($_POST["phone"]);
    $country = trim($_POST["country"]);
    $city = trim($_POST["city"]);

    if ($password !== $confirm_password) {
        $message = "Passwords do not match!";
    } else {
        // Check if username already exists
        $checkUsername = $conn->prepare("SELECT * FROM users WHERE username = ?");
        $checkUsername->bind_param("s", $username);
        $checkUsername->execute();
        $usernameResult = $checkUsername->get_result();
        
        // Check if email already exists
        $checkEmail = $conn->prepare("SELECT * FROM users WHERE email = ?");
        $checkEmail->bind_param("s", $email);
        $checkEmail->execute();
        $emailResult = $checkEmail->get_result();

        if ($usernameResult->num_rows > 0) {
            $message = "Username already taken!";
        } 
        elseif ($emailResult->num_rows > 0) {
            $message = "Email already registered!";
        } 
        else {
            $hashed_password = password_hash($password, PASSWORD_BCRYPT);
            
            // Set default values for other fields
            $verification_count = 0;
            $report_count = 0;
            $status = "active";
            $current_time = date("Y-m-d H:i:s");

            // Insert user into database with all the fields from the schema
            $stmt = $conn->prepare("INSERT INTO users (username, email, password, first_name, last_name, phone, country, city, verification_count, report_count, status, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("ssssssssiisss", $username, $email, $hashed_password, $first_name, $last_name, $phone, $country, $city, $verification_count, $report_count, $status, $current_time, $current_time);

            if ($stmt->execute()) {
                echo "<script>alert('Registration Successful!!');</script>";
                echo "<script>window.location.href='login.php';</script>";
            } else {
                $message = "Error: " . $stmt->error;
            }

            $stmt->close();
        }
        
        $checkEmail->close();
        $checkUsername->close();
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign Up</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            margin: 0;
            background: #f9f9f9;
            padding: 20px 0;
        }
        .container {
            display: flex;
            flex-direction: row;
            flex-wrap: wrap;
            background: #fff;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0px 0px 10px rgba(0, 0, 0, 0.1);
            max-width: 900px;
            width: 100%;
        }
        .form-container {
            flex: 1;
            min-width: 300px;
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
        .register-btn {
            width: 100%;
            padding: 10px;
            border: none;
            background-color: #00C6FF; /* Initial background color */
            color: white; /* Text color */
            font-size: 16px;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.4s ease, transform 0.4s ease; /* Smooth transition for background color and transformation */
        }

        .register-btn:hover {
            background: linear-gradient(to right, #00C6FF, #0072FF); /* Gradient effect on hover */
            background-color: #0072FF; /* Fallback background color */
            transform: scale(1.05); /* Slightly enlarge the button on hover */
        }
        .already-member {
            margin-top: 10px;
            text-align: center;
        }
        .already-member a {
            color: #5d99e5;
            text-decoration: none;
        }
        .image-container {
            flex: 1;
            min-width: 250px;
            display: flex;
            justify-content: center;
            align-items: center;
        }
        .image-container img {
            max-width: 100%;
            height: auto;
        }
        .back-home {
            margin-top: 10px;
            text-align: center;
            margin-bottom: 20px;
        }
        .back-home a {
            color: #4a90e2;
            text-decoration: none;
            display: flex;
            justify-content: center;
            align-items: center;
        }
        .message {
            text-align: center;
            margin-bottom: 10px;
            color: red;
        }
        .gradient-text {
            background: linear-gradient(to right, #4d6d95, #36ddde); /* Your gradient colors */
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            font-size: 40px;
            margin-top: -10px;
        }
        .back-home a,
        .already-member a {
            color: #4a90e2; /* Link color */
            text-decoration: none;
            transition: color 0.3s ease, transform 0.3s ease; /* Smooth transition */
        }

        .back-home a:hover,
        .already-member a:hover {
            color: #0072FF; /* Hover color */
            transform: scale(1.05); /* Slightly enlarge on hover */
        }
        .form-row {
            display: flex;
            gap: 15px;
            margin-bottom: 15px;
        }
        .form-row .input-field {
            margin-bottom: 0;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="form-container">
            <h2 class="gradient-text">Sign up</h2>

            <?php if (!empty($message)) { echo "<p class='message'>$message</p>"; } ?>

            <form action="" method="POST">
                <div class="input-field">
                    <i class="fa fa-user"></i>
                    <input type="text" name="name" placeholder="Username" required>
                </div>
                <div class="form-row">
                    <div class="input-field">
                        <i class="fa fa-user"></i>
                        <input type="text" name="first_name" placeholder="First Name" required>
                    </div>
                    <div class="input-field">
                        <i class="fa fa-user"></i>
                        <input type="text" name="last_name" placeholder="Last Name" required>
                    </div>
                </div>
                <div class="input-field">
                    <i class="fa fa-envelope"></i>
                    <input type="email" name="email" placeholder="Your Email" required>
                </div>
                <div class="input-field">
                    <i class="fa fa-phone"></i>
                    <input type="tel" name="phone" placeholder="Phone Number">
                </div>
                <div class="form-row">
                    <div class="input-field">
                        <i class="fa fa-globe"></i>
                        <input type="text" name="country" placeholder="Country">
                    </div>
                    <div class="input-field">
                        <i class="fa fa-city"></i>
                        <input type="text" name="city" placeholder="City">
                    </div>
                </div>
                <div class="input-field">
                    <i class="fa fa-lock"></i>
                    <input type="password" name="password" placeholder="Password" required>
                </div>
                <div class="input-field">
                    <i class="fa fa-lock"></i>
                    <input type="password" name="confirm_password" placeholder="Repeat your password" required>
                </div>
                <div class="back-home">
                    <a href="index.php"><i class="fa fa-arrow-left">&nbsp;</i>Back to Home</a>
                </div>
                <button class="register-btn" type="submit">Register</button>
                <div class="already-member">
                    <a href="login.php">Already Registered?</a>
                </div>
            </form>
        </div>
        <div class="image-container">
            <img src="assets/2.jpg" alt="Illustration">
        </div>
    </div>
</body>
</html>