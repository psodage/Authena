<?php
// Start the session
session_start();

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

// Initialize variables
$reports = [];
$message = '';
$messageType = '';
$loggedIn = isset($_SESSION['user_id']) ? true : false;
$user_id = $loggedIn ? $_SESSION['user_id'] : null;

// Handle the report submission form
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['submit_report'])) {
    if (!$loggedIn) {
        $message = "You must be logged in to submit a report.";
        $messageType = "danger";
    } else {
        $product_id = $conn->real_escape_string($_POST['product_id']);
        $report_type = $conn->real_escape_string($_POST['report_type']);
        $description = $conn->real_escape_string($_POST['description']);
        $location_address = $conn->real_escape_string($_POST['location_address']);
        
        // Handle image upload if present
        $evidence_images = null;
        if (isset($_FILES['evidence_image']) && $_FILES['evidence_image']['error'] == 0) {
            $target_dir = "uploads/evidence/";
            if (!file_exists($target_dir)) {
                mkdir($target_dir, 0777, true);
            }
            
            $filename = time() . '_' . basename($_FILES['evidence_image']['name']);
            $target_file = $target_dir . $filename;
            
            if (move_uploaded_file($_FILES['evidence_image']['tmp_name'], $target_file)) {
                $evidence_images = $target_file;
            }
        }
        
        // Get location coordinates (this would be from a geocoding API in production)
        // For example purposes, using static values
        $location_lat = 40.7128;
        $location_lng = -74.0060;
        
        // Insert the report
        $sql = "INSERT INTO fake_reports (product_id, user_id, report_type, description, evidence_images, 
                location_lat, location_lng, location_address, status) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'pending')";
        
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("iisssdds", $product_id, $user_id, $report_type, $description, $evidence_images, 
        $location_lat, $location_lng, $location_address);
        
        if ($stmt->execute()) {
            // Update the user's report count
            $update_user = "UPDATE users SET report_count = report_count + 1 WHERE id = ?";
            $stmt_user = $conn->prepare($update_user);
            $stmt_user->bind_param("i", $user_id);
            $stmt_user->execute();
            
            $message = "Your report has been submitted successfully.";
            $messageType = "success";
        } else {
            $message = "Error submitting report: " . $conn->error;
            $messageType = "danger";
        }
    }
}

// Fetch recent fake reports with product and user details
$sql = "SELECT fr.*, p.name as product_name, p.product_code, b.name as brand_name, 
        u.username as reporter_username
        FROM fake_reports fr
        JOIN products p ON fr.product_id = p.id
        LEFT JOIN brands b ON p.brand_id = b.id
        LEFT JOIN users u ON fr.user_id = u.id
        ORDER BY fr.created_at DESC
        LIMIT 10";

$result = $conn->query($sql);

if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $reports[] = $row;
    }
}

// Fetch all products for the reporting form dropdown
$products = [];
$sql_products = "SELECT id, name, product_code FROM products WHERE status = 'active'";
$result_products = $conn->query($sql_products);

if ($result_products && $result_products->num_rows > 0) {
    while ($row = $result_products->fetch_assoc()) {
        $products[] = $row;
    }
}

// Close the database connection
$conn->close();
?>

<!doctype html>
<html class="no-js" lang="zxx">
    <head>
        <!-- Meta Tags -->
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="keywords" content="Fake products, Counterfeit products, Product authentication">
        <meta name="description" content="Report and view fake product reports">
        <meta name='copyright' content=''>
        <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
        
        <!-- Title -->
        <title>Fake Product Reports - Authena</title>
        
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
            .report-card {
                border: 1px solid #eee;
                border-radius: 8px;
                margin-bottom: 20px;
                box-shadow: 0 2px 10px rgba(0,0,0,0.05);
                padding: 20px;
            }
            .report-header {
                display: flex;
                justify-content: space-between;
                border-bottom: 1px solid #eee;
                margin-bottom: 15px;
                padding-bottom: 10px;
            }
            .report-badge {
                display: inline-block;
                padding: 3px 10px;
                border-radius: 30px;
                font-size: 12px;
                font-weight: 600;
                text-transform: uppercase;
            }
            .badge-pending {
                background-color: #FFD166;
                color: #333;
            }
            .badge-investigating {
                background-color: #06D6A0;
                color: #fff;
            }
            .badge-resolved {
                background-color: #118AB2;
                color: #fff;
            }
            .badge-rejected {
                background-color: #EF476F;
                color: #fff;
            }
            .report-meta {
                font-size: 13px;
                color: #777;
                margin-bottom: 15px;
            }
            .report-description {
                margin-bottom: 15px;
            }
            .report-location {
                display: flex;
                align-items: center;
                font-size: 14px;
                color: #555;
            }
            .report-location i {
                margin-right: 5px;
                color: #2D3047;
            }
            .report-form-container {
                background: #f9f9f9;
                padding: 30px;
                border-radius: 8px;
                margin-top: 20px;
            }
            .stats-box {
                background-color: #fff;
                border-radius: 8px;
                padding: 20px;
                margin-bottom: 20px;
                box-shadow: 0 2px 10px rgba(0,0,0,0.05);
                text-align: center;
            }
            .stats-number {
                font-size: 36px;
                font-weight: 700;
                color: #2D3047;
                margin-bottom: 10px;
            }
            .stats-label {
                font-size: 14px;
                color: #777;
                text-transform: uppercase;
                letter-spacing: 1px;
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
        
        <!-- Header Area -->
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
									<a href="index.php"><img src="img/logo3.png" alt="VerifyTag Logo"></a>
								</div>
								<div class="mobile-nav"></div>
							</div>
		
							<!-- Main Navigation -->
							<div class="col-lg-7 col-md-9 col-12">
								<div class="main-menu">
									<nav class="navigation">
										<ul class="nav menu">
											<li ><a href="index.php">Home</a></li>
		
											<li><a href="#">Verify Product <i class="icofont-rounded-down"></i></a>
												<ul class="dropdown">
													<li><a href="serial.php">Enter Serial Number</a></li>
                                            <li><a href="scan_pc.php">Scan QR Code</a></li>
                                            <li><a href="qr_code.php">Upload QR Image</a></li>
												</ul>
											</li>
		
											<li class="active"><a href="#">Insights <i class="icofont-rounded-down"></i></a>
												<ul class="dropdown">
													<li><a href="map-activity.php">Live Scan Map</a></li>
													<li><a href="analytics.php">Scan Analytics</a></li>
													<li><a href="fake-reports.php">Fake Product Reports</a></li>
												</ul>
											</li>
		
											<li ><a href="#">Resources <i class="icofont-rounded-down"></i></a>
												<ul class="dropdown">
													<li><a href="faq.php">FAQ</a></li>
													<li><a href="blog.php">Blog</a></li>
													<li><a href="help.php">Help Center</a></li>
												</ul>
											</li>
										
											<li><a href="#">User Profile<i class="icofont-rounded-down"></i></a>
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
		
							<!-- Login/Signup Button (Only shown when logged out) -->
							<div class="col-lg-2 col-12">
								<div class="get-quote">
									<?php if(!isset($_SESSION['user_id'])) { ?>
										<a href="login.php" class="btn">Login / SignUp</a>
									<?php } ?>
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
                            <h2>Fake Product Reports</h2>
                            <ul class="bread-list">
                                <li><a href="index.php">Home</a></li>
                                <li><i class="icofont-simple-right"></i></li>
                                <li class="active">Fake Product Reports</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- End Breadcrumbs -->
        
        <!-- Fake Reports Section -->
        <section class="fake-reports section">
            <div class="container">
                <!-- Message alert if any -->
                <?php if (!empty($message)): ?>
                <div class="alert alert-<?php echo $messageType; ?> alert-dismissible fade show" role="alert">
                    <?php echo $message; ?>
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <?php endif; ?>
                
                <div class="row">
                    <div class="col-12">
                        <div class="section-title">
                            <h2>Fight Against Counterfeits</h2>
                            <p>Help us combat fake products by reporting suspected counterfeits. Together we can protect consumers and authentic brands.</p>
                        </div>
                    </div>
                </div>
                
                <div class="row">
                    <!-- Stats Section -->
                    <div class="col-lg-3 col-md-6 col-12">
                        <div class="stats-box">
                            <?php
                            // In a real scenario, these would be actual database queries
                            $total_reports = count($reports);
                            ?>
                            <div class="stats-number"><?php echo $total_reports; ?></div>
                            <div class="stats-label">Recent Reports</div>
                        </div>
                    </div>
                    
                    <div class="col-lg-3 col-md-6 col-12">
                        <div class="stats-box">
                            <?php
                            // Count reports by status
                            $investigating = 0;
                            $resolved = 0;
                            foreach ($reports as $report) {
                                if ($report['status'] == 'investigating') $investigating++;
                                if ($report['status'] == 'resolved') $resolved++;
                            }
                            ?>
                            <div class="stats-number"><?php echo $investigating; ?></div>
                            <div class="stats-label">Under Investigation</div>
                        </div>
                    </div>
                    
                    <div class="col-lg-3 col-md-6 col-12">
                        <div class="stats-box">
                            <div class="stats-number"><?php echo $resolved; ?></div>
                            <div class="stats-label">Resolved Reports</div>
                        </div>
                    </div>
                    
                    <div class="col-lg-3 col-md-6 col-12">
                        <div class="stats-box">
                            <?php
                            // This would normally be a database query for total products verified
                            $verified_products = 4; // Placeholder value based on verification_logs table
                            ?>
                            <div class="stats-number"><?php echo $verified_products; ?></div>
                            <div class="stats-label">Products Verified</div>
                        </div>
                    </div>
                </div>
                
                <div class="row">
                    <!-- Recent Reports Section -->
                    <div class="col-lg-8 col-md-12">
                        <h3 class="mt-5 mb-4">Recent Reports</h3>
                        
                        <?php if (empty($reports)): ?>
                            <div class="alert alert-info">No fake product reports found.</div>
                        <?php else: ?>
                            <?php foreach ($reports as $report): ?>
                                <div class="report-card">
                                    <div class="report-header">
                                        <h4><?php echo htmlspecialchars($report['product_name']); ?> (<?php echo htmlspecialchars($report['product_code']); ?>)</h4>
                                        <span class="report-badge badge-<?php echo strtolower($report['status']); ?>">
                                            <?php echo ucfirst($report['status']); ?>
                                        </span>
                                    </div>
                                    
                                    <div class="report-meta">
                                        <strong>Brand:</strong> <?php echo htmlspecialchars($report['brand_name'] ?? 'Unknown'); ?> |
                                        <strong>Reported by:</strong> <?php echo htmlspecialchars($report['reporter_username'] ?? 'Anonymous'); ?> |
                                        <strong>Date:</strong> <?php echo date('M d, Y', strtotime($report['created_at'])); ?>
                                    </div>
                                    
                                    <div class="report-description">
                                        <strong>Issue Type:</strong> <?php echo ucfirst($report['report_type']); ?><br>
                                        <strong>Description:</strong> <?php echo htmlspecialchars($report['description']); ?>
                                    </div>
                                    
                                    <?php if ($report['evidence_images']): ?>
                                        <div class="report-evidence mb-3">
                                            <img src="<?php echo htmlspecialchars($report['evidence_images']); ?>" alt="Evidence" class="img-fluid" style="max-height: 200px;">
                                        </div>
                                    <?php endif; ?>
                                    
                                    <div class="report-location">
                                        <i class="fa fa-map-marker"></i> <?php echo htmlspecialchars($report['location_address']); ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                    
                        <!-- Report Form Section -->
                        <div class="col-lg-4 col-md-12">
                            <div class="report-form-container">
                                <h3 class="mb-4">Report a Fake Product</h3>
                                
                                <?php if (!$loggedIn): ?>
                                    <div class="alert alert-warning">
                                        You need to <a href="login.php">login</a> to submit a report.
                                    </div>
                                <?php else: ?>
                                    <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post" enctype="multipart/form-data">
                                        <div class="form-group">
                                            <label for="product_id">Select Product</label>
                                            <select name="product_id" id="product_id" class="form-control" required>
                                                <option value="">-- Select Product --</option>
                                                <?php foreach ($products as $product): ?>
                                                    <option value="<?php echo $product['id']; ?>">
                                                        <?php echo htmlspecialchars($product['name']) . ' (' . htmlspecialchars($product['product_code']) . ')'; ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                        
                                        <div class="form-group">
                                            <label for="report_type">Issue Type</label>
                                            <select name="report_type" id="report_type" class="form-control" required>
                                                <option value="counterfeit">Counterfeit Product</option>
                                                <option value="tampered">Tampered Product</option>
                                                <option value="expired">Expired Product</option>
                                                <option value="other">Other Issue</option>
                                            </select>
                                        </div>
                                        
                                        <div class="form-group">
                                            <label for="description">Description</label>
                                            <textarea name="description" id="description" class="form-control" rows="5" placeholder="Describe why you think this product is fake or problematic" required></textarea>
                                        </div>
                                        
                                        <div class="form-group">
                                            <label for="location_address">Purchase Location</label>
                                            <input type="text" name="location_address" id="location_address" class="form-control" placeholder="Where did you buy/find this product?" required>
                                        </div>
                                        
                                        <div class="form-group">
                                            <label for="evidence_image">Upload Evidence (Optional)</label>
                                            <input type="file" name="evidence_image" id="evidence_image" class="form-control-file">
                                            <small class="form-text text-muted">Upload images showing signs of a counterfeit product.</small>
                                        </div>
                                        
                                        <div class="form-group">
                                            <button type="submit" name="submit_report" class="btn btn-primary">Submit Report</button>
                                        </div>
                                    </form>
                                <?php endif; ?>
                            </div>
                        </div>
                </div>
            </div>
        </section>
        <!-- End Fake Reports Section -->
        
        <!-- Educational Section -->
        <section class="section gray-bg">
            <div class="container">
                <div class="row">
                    <div class="col-12">
                        <div class="section-title">
                            <h2>How to Identify Fake Products</h2>
                            <p>Learn the common signs of counterfeit products to protect yourself</p>
                        </div>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-lg-4 col-md-6 col-12">
                        <div class="single-service">
                            <i class="icofont icofont-price"></i>
                            <h4>Price Too Good To Be True</h4>
                            <p>If a luxury or premium product is being sold at a suspiciously low price, it's likely counterfeit. Compare prices with official retailers.</p>
                        </div>
                    </div>
                    
                    <div class="col-lg-4 col-md-6 col-12">
                        <div class="single-service">
                            <i class="icofont icofont-tags"></i>
                            <h4>Poor Quality Packaging</h4>
                            <p>Authentic products typically have high-quality packaging without misspellings, blurry logos, or poor printing quality.</p>
                        </div>
                    </div>
                    
                    <div class="col-lg-4 col-md-6 col-12">
                        <div class="single-service">
                            <i class="icofont icofont-qr-code"></i>
                            <h4>Missing Authentication Features</h4>
                            <p>Look for security features like holograms, QR codes, or serial numbers that can be verified with the manufacturer.</p>
                        </div>
                    </div>
                    
                    <div class="col-lg-4 col-md-6 col-12">
                        <div class="single-service">
                            <i class="icofont icofont-shopping-cart"></i>
                            <h4>Suspicious Retail Channel</h4>
                            <p>Be cautious when purchasing from unauthorized retailers, online marketplaces, or street vendors.</p>
                        </div>
                    </div>
                    
                    <div class="col-lg-4 col-md-6 col-12">
                        <div class="single-service">
                            <i class="icofont icofont-touch"></i>
                            <h4>Poor Material Quality</h4>
                            <p>Counterfeit products often use cheaper materials that look or feel different from the authentic product.</p>
                        </div>
                    </div>
                    
                    <div class="col-lg-4 col-md-6 col-12">
                        <div class="single-service">
                            <i class="icofont icofont-verification-check"></i>
                            <h4>Verify with Authena</h4>
                            <p>Always scan product QR codes or check serial numbers with our verification tool to confirm authenticity.</p>
                        </div>
                    </div>
                </div>
            </div>
        </section>
        <!-- End Educational Section -->
        
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
        <!-- End Footer Area -->
        
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