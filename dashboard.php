<?php
// Start session
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    // Redirect to login page if not logged in
    header("Location: login.php");
    exit();
}

// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "authena";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get user data
$user_id = $_SESSION['user_id'];
$stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user_result = $stmt->get_result();
$user = $user_result->fetch_assoc();
$stmt->close();

// Get user verification stats
$stmt = $conn->prepare("SELECT COUNT(*) as total_verifications FROM verification_logs WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$verification_result = $stmt->get_result();
$verification_count = $verification_result->fetch_assoc()['total_verifications'];
$stmt->close();

// Get authentic vs fake verification stats
$stmt = $conn->prepare("SELECT status, COUNT(*) as count FROM verification_logs WHERE user_id = ? GROUP BY status");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$verification_stats_result = $stmt->get_result();
$verification_stats = [];
while ($row = $verification_stats_result->fetch_assoc()) {
    $verification_stats[$row['status']] = $row['count'];
}
$stmt->close();

// Get recently verified products
$stmt = $conn->prepare("
    SELECT v.*, p.name as product_name, p.product_code, b.name as brand_name
    FROM verification_logs v
    LEFT JOIN products p ON v.product_id = p.id
    LEFT JOIN brands b ON p.brand_id = b.id
    WHERE v.user_id = ?
    ORDER BY v.verification_timestamp DESC
    LIMIT 5
");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$recent_verifications_result = $stmt->get_result();
$recent_verifications = [];
while ($row = $recent_verifications_result->fetch_assoc()) {
    $recent_verifications[] = $row;
}
$stmt->close();

// Get user's fake reports
$stmt = $conn->prepare("
    SELECT f.*, p.name as product_name, p.product_code, b.name as brand_name
    FROM fake_reports f
    LEFT JOIN products p ON f.product_id = p.id
    LEFT JOIN brands b ON p.brand_id = b.id
    WHERE f.user_id = ?
    ORDER BY f.created_at DESC
    LIMIT 5
");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$fake_reports_result = $stmt->get_result();
$fake_reports = [];
while ($row = $fake_reports_result->fetch_assoc()) {
    $fake_reports[] = $row;
}
$stmt->close();

// Close connection
$conn->close();
?>

<!doctype html>
<html class="no-js" lang="zxx">
<head>
    <!-- Meta Tags -->
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="keywords" content="Dashboard, Authena, Product Authentication">
    <meta name="description" content="Authena User Dashboard">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    
    <!-- Title -->
    <title>User Dashboard - Authena</title>
    
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

    <!-- Dashboard specific CSS -->
    <style>
        .dashboard-stats-box {
            background: #f9f9f9;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 0 15px rgba(0,0,0,0.1);
            transition: all 0.3s ease;
        }
        
        .dashboard-stats-box:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 20px rgba(0,0,0,0.15);
        }
        
        .stats-number {
            font-size: 32px;
            font-weight: 700;
            color: #1A76D1;
            margin-bottom: 5px;
        }
        
        .stats-title {
            font-size: 16px;
            color: #555;
        }
        
        .table-container {
            background: #fff;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 30px;
            box-shadow: 0 0 15px rgba(0,0,0,0.1);
        }
        
        .dashboard-title {
            margin-bottom: 30px;
            padding-bottom: 10px;
            border-bottom: 1px solid #eee;
        }
        
        .authentic-status {
            color: #28a745;
            font-weight: 600;
        }
        
        .fake-status {
            color: #dc3545;
            font-weight: 600;
        }
        
        .unknown-status {
            color: #ffc107;
            font-weight: 600;
        }
        
        .status-badge {
            padding: 5px 10px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: 600;
        }
        
        .status-pending {
            background-color: #ffeeba;
            color: #856404;
        }
        
        .status-investigating {
            background-color: #b8daff;
            color: #004085;
        }
        
        .status-resolved {
            background-color: #c3e6cb;
            color: #155724;
        }
        
        .status-rejected {
            background-color: #f5c6cb;
            color: #721c24;
        }
        
        .welcome-message {
            margin-bottom: 40px;
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
    
    <!-- Breadcrumbs -->
    <div class="breadcrumbs overlay">
        <div class="container">
            <div class="bread-inner">
                <div class="row">
                    <div class="col-12">
                        <h2>Dashboard</h2>
                        <ul class="bread-list">
                            <li><a href="index.php">Home</a></li>
                            <li><i class="icofont-simple-right"></i></li>
                            <li class="active">Dashboard</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- End Breadcrumbs -->
    
    <!-- Dashboard Section -->
    <section class="dashboard section">
        <div class="container">
            <div class="row">
                <div class="col-12">
                    <div class="welcome-message">
                        <h3>Welcome, <?php echo htmlspecialchars($user['first_name'] ? $user['first_name'] : $user['username']); ?>!</h3>
                        <p>Here's an overview of your product verification activity and reports.</p>
                    </div>
                </div>
            </div>
            
            <!-- Stats Overview -->
            <div class="row">
                <div class="col-lg-3 col-md-6">
                    <div class="dashboard-stats-box">
                        <div class="stats-number"><?php echo $verification_count; ?></div>
                        <div class="stats-title">Total Verifications</div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6">
                    <div class="dashboard-stats-box">
                        <div class="stats-number"><?php echo isset($verification_stats['authentic']) ? $verification_stats['authentic'] : 0; ?></div>
                        <div class="stats-title">Authentic Products</div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6">
                    <div class="dashboard-stats-box">
                        <div class="stats-number"><?php echo isset($verification_stats['fake']) ? $verification_stats['fake'] : 0; ?></div>
                        <div class="stats-title">Fake Products</div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6">
                    <div class="dashboard-stats-box">
                        <div class="stats-number"><?php echo count($fake_reports); ?></div>
                        <div class="stats-title">Reports Submitted</div>
                    </div>
                </div>
            </div>
            
            <!-- Recent Verifications -->
            <div class="row mt-4">
                <div class="col-12">
                    <div class="table-container">
                        <h4 class="dashboard-title">Recent Product Verifications</h4>
                        <?php if (count($recent_verifications) > 0): ?>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Date</th>
                                        <th>Product</th>
                                        <th>Brand</th>
                                        <th>Location</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($recent_verifications as $verification): ?>
                                    <tr>
                                        <td><?php echo date('M d, Y', strtotime($verification['verification_timestamp'])); ?></td>
                                        <td>
                                            <?php 
                                            if (!empty($verification['product_name'])) {
                                                echo htmlspecialchars($verification['product_name']);
                                                if (!empty($verification['product_code'])) {
                                                    echo ' (' . htmlspecialchars($verification['product_code']) . ')';
                                                }
                                            } else {
                                                echo 'Unknown Product';
                                            }
                                            ?>
                                        </td>
                                        <td><?php echo $verification['brand_name'] ? htmlspecialchars($verification['brand_name']) : 'Unknown Brand'; ?></td>
                                        <td><?php echo $verification['location_address'] ? htmlspecialchars($verification['location_address']) : 'Unknown Location'; ?></td>
                                        <td>
                                            <?php 
                                            if ($verification['status'] == 'authentic') {
                                                echo '<span class="authentic-status">Authentic</span>';
                                            } elseif ($verification['status'] == 'fake') {
                                                echo '<span class="fake-status">Fake</span>';
                                            } else {
                                                echo '<span class="unknown-status">Unknown</span>';
                                            }
                                            ?>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                  
                        <?php else: ?>
                        <div class="alert alert-info">
                            You haven't verified any products yet. <a href="serial.php">Verify a product now</a>!
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            
            <!-- Fake Reports -->
            <div class="row">
                <div class="col-12">
                    <div class="table-container">
                        <h4 class="dashboard-title">Your Fake Product Reports</h4>
                        <?php if (count($fake_reports) > 0): ?>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Date</th>
                                        <th>Product</th>
                                        <th>Report Type</th>
                                        <th>Location</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($fake_reports as $report): ?>
                                    <tr>
                                        <td><?php echo date('M d, Y', strtotime($report['created_at'])); ?></td>
                                        <td>
                                            <?php 
                                            if (!empty($report['product_name'])) {
                                                echo htmlspecialchars($report['product_name']);
                                                if (!empty($report['product_code'])) {
                                                    echo ' (' . htmlspecialchars($report['product_code']) . ')';
                                                }
                                            } else {
                                                echo 'Unknown Product';
                                            }
                                            ?>
                                        </td>
                                        <td><?php echo ucfirst(htmlspecialchars($report['report_type'])); ?></td>  
                                        <td><?php echo $report['location_address'] ? htmlspecialchars($report['location_address']) : 'Unknown Location'; ?></td>
                                        <td>
                                            <?php 
                                            $status_class = 'status-' . $report['status'];
                                            echo '<span class="status-badge ' . $status_class . '">' . ucfirst($report['status']) . '</span>';
                                            ?>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    
                        <?php else: ?>
                        <div class="alert alert-info">
                            You haven't submitted any fake product reports yet.
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            
            <!-- Quick Actions -->
            <div class="row">
                <div class="col-12">
                    <div class="table-container">
                        <h4 class="dashboard-title">Quick Actions</h4>
                        <div class="row">
                            <div class="col-md-4 col-sm-6 mb-3">
                                <a href="serial.php" class="btn btn-primary btn-block">
                                    <i class="fa fa-search mr-2"></i> Verify by Serial
                                </a>
                            </div>
                            <div class="col-md-4 col-sm-6 mb-3">
                                <a href="scan_pc.php" class="btn btn-primary btn-block">
                                    <i class="fa fa-qrcode mr-2"></i> Scan QR Code
                                </a>
                            </div>
                            <div class="col-md-4 col-sm-6 mb-3">
                                <a href="report-fake.php" class="btn btn-danger btn-block">
                                    <i class="fa fa-flag mr-2"></i> Report Fake Product
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <!-- End Dashboard Section -->
    
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