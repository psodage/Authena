<?php
// Start the session
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    // Redirect to login page if not logged in
    header("Location: login.php");
    exit();
}

// Database connection
$servername = "localhost";
$username = "root"; // Replace with your database username
$password = ""; // Replace with your database password
$dbname = "authena"; // Replace with your database name

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
$userId = $_SESSION['user_id'] ?? 0; // Replace with your actual user ID source

// Prepare and execute queries to get user stats
// 1. Get number of products (assuming there's a user-product relationship)
// Get number of products by counting products the user has reported
$productsQuery = "SELECT COUNT(DISTINCT product_id) as product_count FROM fake_reports WHERE user_id = ?";
$productsStmt = $conn->prepare($productsQuery);
$productsStmt->bind_param("i", $userId);
$productsStmt->execute();
$productsResult = $productsStmt->get_result();
$productCount = $productsResult->fetch_assoc()['product_count'] ?? 0;

// 2. Get number of scans (verification_count from users table)
$scansQuery = "SELECT verification_count FROM users WHERE id = ?";
$scansStmt = $conn->prepare($scansQuery);
$scansStmt->bind_param("i", $userId);
$scansStmt->execute();
$scansResult = $scansStmt->get_result();
$scanCount = $scansResult->fetch_assoc()['verification_count'] ?? 0;

// 3. Get number of reports (report_count from users table)
$reportsQuery = "SELECT report_count FROM users WHERE id = ?";
$reportsStmt = $conn->prepare($reportsQuery);
$reportsStmt->bind_param("i", $userId);
$reportsStmt->execute();
$reportsResult = $reportsStmt->get_result();
$reportCount = $reportsResult->fetch_assoc()['report_count'] ?? 0;
// Initialize variables for form data and error messages
$success_message = "";
$error_message = "";

// Process form submission for profile update
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_profile'])) {
    $user_id = $_SESSION['user_id'];
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    
    // Check if the new username already exists (excluding current user)
    $check_username = $conn->prepare("SELECT id FROM users WHERE username = ? AND id != ?");
    $check_username->bind_param("si", $username, $user_id);
    $check_username->execute();
    $username_result = $check_username->get_result();
    
    // Check if the new email already exists (excluding current user)
    $check_email = $conn->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
    $check_email->bind_param("si", $email, $user_id);
    $check_email->execute();
    $email_result = $check_email->get_result();
    
    if ($username_result->num_rows > 0) {
        $error_message = "Username already taken. Please choose another.";
    } elseif ($email_result->num_rows > 0) {
        $error_message = "Email already in use. Please use another email.";
    } else {
        // Update the user's profile
        $update_sql = $conn->prepare("UPDATE users SET username = ?, email = ? WHERE id = ?");
        $update_sql->bind_param("ssi", $username, $email, $user_id);
        
        if ($update_sql->execute()) {
            $success_message = "Profile updated successfully!";
            // Update session variables
            $_SESSION['username'] = $username;
            $_SESSION['email'] = $email;
        } else {
            $error_message = "Error updating profile: " . $conn->error;
        }
        
        $update_sql->close();
    }
    
    $check_username->close();
    $check_email->close();
}

// Process password change
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['change_password'])) {
    $user_id = $_SESSION['user_id'];
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];
    
    // Get current password from database
    $get_password = $conn->prepare("SELECT password FROM users WHERE id = ?");
    $get_password->bind_param("i", $user_id);
    $get_password->execute();
    $result = $get_password->get_result();
    
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $hashed_password = $row['password'];
        
        // Verify current password
        if (password_verify($current_password, $hashed_password)) {
            // Check if new passwords match
            if ($new_password === $confirm_password) {
                // Hash the new password
                $new_hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
                
                // Update password in database
                $update_password = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
                $update_password->bind_param("si", $new_hashed_password, $user_id);
                
                if ($update_password->execute()) {
                    $success_message = "Password changed successfully!";
                } else {
                    $error_message = "Error changing password: " . $conn->error;
                }
                
                $update_password->close();
            } else {
                $error_message = "New passwords do not match!";
            }
        } else {
            $error_message = "Current password is incorrect!";
        }
    } else {
        $error_message = "User not found!";
    }
    
    $get_password->close();
}

// Fetch user data
$user_id = $_SESSION['user_id'];
$sql = "SELECT id, username, email, created_at FROM users WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$stmt->close();
$conn->close();
?>

<!doctype html>
<html class="no-js" lang="zxx">
    <head>
        <!-- Meta Tags -->
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="keywords" content="Site keywords here">
        <meta name="description" content="">
        <meta name='copyright' content=''>
        <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
        
        <!-- Title -->
        <title>User Profile - Authena</title>
        
        <!-- Favicon -->
        <link rel="icon" href="img/favicon.png">
        
        <!-- Google Fonts -->
        <link href="https://fonts.googleapis.com/css?family=Poppins:200i,300,300i,400,400i,500,500i,600,600i,700,700i,800,800i,900,900i&display=swap" rel="stylesheet">

        <!-- Bootstrap CSS -->
        <link rel="stylesheet" href="css/bootstrap.min.css">
        <!-- Nice Select CSS -->
        <link rel="stylesheet" href="css/nice-select.css">
        <!-- Font Awesome CSS -->
        <link rel="stylesheet" href="css/font-awesome.min.css">
        <!-- icofont CSS -->
        <link rel="stylesheet" href="css/icofont.css">
        <!-- Slicknav -->
        <link rel="stylesheet" href="css/slicknav.min.css">
        <!-- Owl Carousel CSS -->
        <link rel="stylesheet" href="css/owl-carousel.css">
        <!-- Datepicker CSS -->
        <link rel="stylesheet" href="css/datepicker.css">
        <!-- Animate CSS -->
        <link rel="stylesheet" href="css/animate.min.css">
        <!-- Magnific Popup CSS -->
        <link rel="stylesheet" href="css/magnific-popup.css">
        
        <!-- Medipro CSS -->
        <link rel="stylesheet" href="css/normalize.css">
        <link rel="stylesheet" href="style.css">
        <link rel="stylesheet" href="css/responsive.css">
        
        <style>
            .profile-section {
                padding: 80px 0;
            }
            .profile-box {
                background: #fff;
                padding: 30px;
                border-radius: 8px;
                box-shadow: 0 0 20px rgba(0, 0, 0, 0.1);
                margin-bottom: 30px;
            }
            .profile-box h3 {
                margin-bottom: 20px;
                padding-bottom: 15px;
                border-bottom: 1px solid #eee;
                color: #1A76D1;
            }
            .form-group {
                margin-bottom: 20px;
            }
            .btn-primary {
                background: #1A76D1;
                border-color: #1A76D1;
            }
            .btn-primary:hover {
                background: #0d62b3;
                border-color: #0d62b3;
            }
            .alert {
                margin-bottom: 20px;
            }
            .profile-stats {
                display: flex;
                justify-content: space-between;
                padding: 15px 0;
            }
            .stat-item {
                text-align: center;
                padding: 15px;
                border-radius: 5px;
                background: #f9f9f9;
                flex: 1;
                margin: 0 5px;
            }
            .stat-item h4 {
                margin-bottom: 5px;
                color: #1A76D1;
            }
            .stat-item p {
                margin: 0;
                font-size: 14px;
            }
        </style>
    </head>
    <body>
    
        <!-- Preloader -->
        <div class="preloader">
            <div class="loader">
                <div class="loader-outter"></div>
                <div class="loader-inner"></div>

                <div class="indicator"> 
                    <svg width="16px" height="12px">
                        <polyline id="back" points="1 6 4 6 6 11 10 1 12 6 15 6"></polyline>
                        <polyline id="front" points="1 6 4 6 6 11 10 1 12 6 15 6"></polyline>
                    </svg>
                </div>
            </div>
        </div>
        <!-- End Preloader -->
        
        <header class="header">
            <!-- Topbar -->
            <div class="topbar">
                <div class="container">
                    <div class="row">
                        <!-- Top Left Links -->
                        <div class="col-lg-6 col-md-5 col-12">
                        <ul class="top-link">
                                <li><a href="about.php">About</a></li>
                                <li><a href="brands.php">Brands</a></li>
                                <li><a href="index.php">Contact</a></li>
                                <li><a href="faq.php">FAQ</a></li>
                            </ul>
                        </div>
                        <!-- Top Right Contact Info -->
                        <div class="col-lg-6 col-md-7 col-12">
                            <ul class="top-contact">
                                <li><i class="fa fa-phone"></i>+880 1234 56789</li>
                                <li><i class="fa fa-envelope"></i>
                                    <a href="mailto:support@yourmail.com">support@yourmail.com</a>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
            <!-- End Topbar -->
        
            <!-- Header Inner -->
            <div class="header-inner">
                <div class="container">
                    <div class="inner">
                        <div class="row align-items-center">
                            <!-- Logo -->
                            <div class="col-lg-3 col-md-3 col-12">
                                <div class="logo">
                                    <a href="index.php"><img src="img/logo3.png" alt="Authena Logo"></a>
                                </div>
                                <div class="mobile-nav"></div>
                            </div>
        
                            <!-- Main Navigation -->
                            <div class="col-lg-7 col-md-9 col-12">
                                <div class="main-menu">
                                    <nav class="navigation">
                                        <ul class="nav menu">
                                            <li><a href="index.php">Home</a></li>
        
                                            <li ><a href="#">Verify Product <i class="icofont-rounded-down"></i></a>
                                                <ul class="dropdown">
                                                    <li class="active"><a href="serial.php">Enter Serial Number</a></li>
                                                    <li><a href="scan_pc.php">Scan QR Code</a></li>
                                                    <li><a href="qr_code.php">Upload QR Image</a></li>
                                                </ul>
                                            </li>
        
                                            <li><a href="#">Insights <i class="icofont-rounded-down"></i></a>
                                                <ul class="dropdown">
                                                    <li><a href="map-activity.php">Live Scan Map</a></li>
                                                    <li><a href="analytics.php">Scan Analytics</a></li>
                                                    <li><a href="fake-reports.php">Fake Product Reports</a></li>
                                                </ul>
                                            </li>
        
                                            <li><a href="#">Resources <i class="icofont-rounded-down"></i></a>
                                                <ul class="dropdown">
                                                    <li><a href="faq.php">FAQ</a></li>
                                                    <li><a href="blog.php">Blog</a></li>
                                                    <li><a href="help.php">Help Center</a></li>
                                                </ul>
                                            </li>
                                        
                                            <li class="active"><a href="#">User Profile<i class="icofont-rounded-down"></i></a>
                                            <ul class="dropdown">
                                                    <li><a href="profile.php">My Profile</a></li>
                                                    <li><a href="dashboard.php">Dashboard</a></li>
                                                   
                                                    <li><a href="logout.php">Logout</a></li>
                                                </ul>
                                            </li>
                                        </ul>
                                    </nav>
                                </div>
                            </div>
        
                            <!-- Login/Signup Button -->
                            <div class="col-lg-2 col-12">
                                <div class="get-quote">
                                    <?php if (isset($_SESSION['username'])): ?>
                                        <a href="logout.php" class="btn">Logout</a>
                                    <?php else: ?>
                                        <a href="login.php" class="btn">Login / SignUp</a>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>  
            <!--/ End Header Inner -->
        </header>
        <!-- End Header Area -->
    
        <!-- Breadcrumbs -->
        <div class="breadcrumbs overlay">
            <div class="container">
                <div class="bread-inner">
                    <div class="row">
                        <div class="col-12">
                            <h2>User Profile</h2>
                            <ul class="bread-list">
                                <li><a href="index.php">Home</a></li>
                                <li><i class="icofont-simple-right"></i></li>
                                <li class="active">My Profile</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- End Breadcrumbs -->
        
        <!-- Profile Section -->
        <section class="profile-section">
            <div class="container">
                <?php if(!empty($success_message)): ?>
                    <div class="alert alert-success"><?php echo $success_message; ?></div>
                <?php endif; ?>
                
                <?php if(!empty($error_message)): ?>
                    <div class="alert alert-danger"><?php echo $error_message; ?></div>
                <?php endif; ?>
                
                <div class="row">
                    <!-- Profile Information -->
                    <div class="col-lg-8">
                        <div class="profile-box">
                            <h3>Profile Information</h3>
                            <form action="profile.php" method="POST">
                                <div class="form-group">
                                    <label for="username">Username</label>
                                    <input type="text" class="form-control" id="username" name="username" value="<?php echo htmlspecialchars($user['username']); ?>" required>
                                </div>
                                <div class="form-group">
                                    <label for="email">Email Address</label>
                                    <input type="email" class="form-control" id="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required>
                                </div>
                                <div class="form-group">
                                    <label>Member Since</label>
                                    <input type="text" class="form-control" value="<?php echo date('F d, Y', strtotime($user['created_at'])); ?>" disabled>
                                </div>
                                <button type="submit" name="update_profile" class="btn btn-primary">Update Profile</button>
                            </form>
                        </div>
                        
                        <!-- Change Password -->
                        <div class="profile-box">
                            <h3>Change Password</h3>
                            <form action="profile.php" method="POST">
                                <div class="form-group">
                                    <label for="current_password">Current Password</label>
                                    <input type="password" class="form-control" id="current_password" name="current_password" required>
                                </div>
                                <div class="form-group">
                                    <label for="new_password">New Password</label>
                                    <input type="password" class="form-control" id="new_password" name="new_password" required>
                                </div>
                                <div class="form-group">
                                    <label for="confirm_password">Confirm New Password</label>
                                    <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                                </div>
                                <button type="submit" name="change_password" class="btn btn-primary">Change Password</button>
                            </form>
                        </div>
                    </div>
                    
                    <!-- Profile Sidebar -->
                    <div class="col-lg-4">
                        <div class="profile-box">
                            <h3>Account Summary</h3>
                            <div class="text-center mb-4">
                                <img src="img/profile-placeholder.png" alt="Profile" class="img-fluid rounded-circle" style="width: 150px; height: 150px;">
                                <h4 class="mt-3"><?php echo htmlspecialchars($user['username']); ?></h4>
                                <p class="text-muted"><?php echo htmlspecialchars($user['email']); ?></p>
                            </div>
                            


<div class="profile-stats">
    <div class="stat-item">
        <h4><?php echo htmlspecialchars($productCount); ?></h4>
        <p>Products</p>
    </div>
    <div class="stat-item">
        <h4><?php echo htmlspecialchars($scanCount); ?></h4>
        <p>Scans</p>
    </div>
    <div class="stat-item">
        <h4><?php echo htmlspecialchars($reportCount); ?></h4>
        <p>Reports</p>
    </div>
</div>
                            
                            <div class="mt-4">
                                <a href="dashboard.php" class="btn btn-primary btn-block">Go to Dashboard</a>
                            
                                <a href="settings.php" class="btn btn-outline-primary btn-block mt-2">Account Settings</a>
                                <a href="logout.php" class="btn btn-outline-danger btn-block mt-2">Logout</a>
                            </div>
                        </div>
                        
                        <div class="profile-box">
                            <h3>Security Tips</h3>
                            <ul class="list-unstyled">
                                <li><i class="fa fa-check-circle text-success mr-2"></i> Use a strong, unique password</li>
                                <li><i class="fa fa-check-circle text-success mr-2"></i> Update your password regularly</li>
                                <li><i class="fa fa-check-circle text-success mr-2"></i> Keep your email address current</li>
                                <li><i class="fa fa-check-circle text-success mr-2"></i> Report suspicious activities</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </section>
        <!-- End Profile Section -->
        
        <!-- Footer Area -->
        <footer id="footer" class="footer">
			<!-- Footer Top -->
			<div class="footer-top">
				<div class="container">
					<div class="row">
						
						<!-- About Us -->
						<div class="col-lg-3 col-md-6 col-12">
							<div class="single-footer">
								<h2>About Authena</h2>
								<p>Authena is dedicated to protecting consumers from counterfeit products by offering simple, fast, and accurate product verification.</p>
								<ul class="social">
									<li><a href="#"><i class="icofont-facebook"></i></a></li>
									<li><a href="#"><i class="icofont-twitter"></i></a></li>
									<li><a href="#"><i class="icofont-instagram"></i></a></li>
									<li><a href="#"><i class="icofont-linkedin"></i></a></li>
								</ul>
							</div>
						</div>
		
						<!-- Quick Links -->
						<div class="col-lg-3 col-md-6 col-12">
							<div class="single-footer f-link">
								<h2>Quick Links</h2>
								<div class="row">
									<div class="col-lg-6 col-md-6 col-12">
										<ul>
											<li><a href="index.php"><i class="fa fa-caret-right"></i>Home</a></li>
											<li><a href="serial.php"><i class="fa fa-caret-right"></i>Verify Product</a></li>
											<li><a href="qr_code.php"><i class="fa fa-caret-right"></i>Scan QR Code</a></li>
											<li><a href="serial.php"><i class="fa fa-caret-right"></i>Submit Serial</a></li>
											<li><a href="scan_pc.php"><i class="fa fa-caret-right"></i>Upload Image</a></li>
										</ul>
									</div>
									<div class="col-lg-6 col-md-6 col-12">
										<ul>
											<li><a href="help.php"><i class="fa fa-caret-right"></i>How It Works</a></li>
											<li><a href="about.php"><i class="fa fa-caret-right"></i>Support</a></li>
											<li><a href="faq.php"><i class="fa fa-caret-right"></i>FAQ</a></li>
											<li><a href="fake-reports.php"><i class="fa fa-caret-right"></i>Report Scam</a></li>
											<li><a href="index.phps"><i class="fa fa-caret-right"></i>Contact Us</a></li>
										</ul>
									</div>
								</div>
							</div>
						</div>
		
						<!-- Working Hours -->
						<div class="col-lg-3 col-md-6 col-12">
							<div class="single-footer">
								<h2>Support Hours</h2>
								<p>Our team is available to help you with any product authentication issues or scam reports.</p>
								<ul class="time-sidual">
									<li class="day">Mon - Fri <span>9:00 AM - 6:00 PM</span></li>
									<li class="day">Saturday <span>10:00 AM - 4:00 PM</span></li>
									<li class="day">Sunday <span>Closed</span></li>
								</ul>
							</div>
						</div>
		
						<!-- Newsletter -->
						<div class="col-lg-3 col-md-6 col-12">
							<div class="single-footer">
								<h2>Subscribe</h2>
								<p>Get the latest scam alerts, updates on new features, and verification tips delivered to your inbox.</p>
								<form action="#" method="POST" class="newsletter-inner">
									<input name="email" type="email" class="common-input" placeholder="Your email address"
										onfocus="this.placeholder=''" onblur="this.placeholder='Your email address'" required>
									<button class="button" type="submit"><i class="icofont-paper-plane" onclick='window.alert("Thank You !! Updates will via email ")'></i></button>
								</form>
							</div>
						</div>
		
					</div>
				</div>
			</div>
		
			<!-- Copyright -->
			<div class="copyright">
				<div class="container">
					<div class="row">
						<div class="col-12 text-center">
							<div class="copyright-content">
								<p>© 2025 Authena. All rights reserved. | Designed with 🖕 to fight fakes.</p>
							</div>
						</div>
					</div>
				</div>
			</div>
		</footer>
        <!--/ End Footer Area -->
        
        <!-- jquery Min JS -->
        <script src="js/jquery.min.js"></script>
        <!-- jquery Migrate JS -->
        <script src="js/jquery-migrate-3.0.0.js"></script>
        <!-- jquery Ui JS -->
        <script src="js/jquery-ui.min.js"></script>
        <!-- Easing JS -->
        <script src="js/easing.js"></script>
        <!-- Color JS -->
        <script src="js/colors.js"></script>
        <!-- Popper JS -->
        <script src="js/popper.min.js"></script>
        <!-- Bootstrap Datepicker JS -->
        <script src="js/bootstrap-datepicker.js"></script>
        <!-- Jquery Nav JS -->
        <script src="js/jquery.nav.js"></script>
        <!-- Slicknav JS -->
        <script src="js/slicknav.min.js"></script>
        <!-- ScrollUp JS -->
        <script src="js/jquery.scrollUp.min.js"></script>
        <!-- Niceselect JS -->
        <script src="js/niceselect.js"></script>
        <!-- Tilt Jquery JS -->
        <script src="js/tilt.jquery.min.js"></script>
        <!-- Owl Carousel JS -->
        <script src="js/owl-carousel.js"></script>
        <!-- counterup JS -->
        <script src="js/jquery.counterup.min.js"></script>
        <!-- Steller JS -->
        <script src="js/steller.js"></script>
        <!-- Wow JS -->
        <script src="js/wow.min.js"></script>
        <!-- Magnific Popup JS -->
        <script src="js/jquery.magnific-popup.min.js"></script>
        <!-- Counter Up CDN JS -->
        <script src="http://cdnjs.cloudflare.com/ajax/libs/waypoints/2.0.3/waypoints.min.js"></script>
        <!-- Bootstrap JS -->
        <script src="js/bootstrap.min.js"></script>
        <!-- Main JS -->
        <script src="js/main.js"></script>
    </body>
</html>