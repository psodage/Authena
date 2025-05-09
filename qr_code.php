<?php
// Start session if not already started
session_start();

// Database connection
$db_host = "localhost";
$db_user = "root";
$db_pass = "";
$db_name = "authena";

$conn = mysqli_connect($db_host, $db_user, $db_pass, $db_name);
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

// Function to get product details by barcode
function getProductByBarcode($barcode) {
    global $conn;
    
    // Query to find product by product_code or unique_identifier
    $sql = "SELECT p.*, b.name as brand_name, c.name as category_name 
            FROM products p
            LEFT JOIN brands b ON p.brand_id = b.id
            LEFT JOIN categories c ON p.category_id = c.id
            WHERE p.product_code = ? OR p.unique_identifier = ?";
            
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "ss", $barcode, $barcode);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    if(mysqli_num_rows($result) > 0) {
        return mysqli_fetch_assoc($result);
    }
    
    return null;
}

// Function to register a fake product report
function registerFakeReport($product_id, $user_id, $barcode, $reason = "User reported as fake", $latitude = null, $longitude = null, $location_address = null) {
    global $conn;
    
    // Get current date and time in MySQL format
    $reported_date = date('Y-m-d H:i:s');
    
    $report_sql = "INSERT INTO fake_reports (product_id, user_id, report_type, description, status, location_lat, location_lng, location_address, created_at) 
                  VALUES (?, ?, 'counterfeit', ?, 'pending', ?, ?, ?, ?)";
    $report_stmt = mysqli_prepare($conn, $report_sql);
    mysqli_stmt_bind_param($report_stmt, "iisssss", $product_id, $user_id, $reason, $latitude, $longitude, $location_address, $reported_date);
    $report_result = mysqli_stmt_execute($report_stmt);
    
    // Update user report count if user is logged in
    if($user_id) {
        $user_update_sql = "UPDATE users SET report_count = report_count + 1 WHERE id = ?";
        $user_update_stmt = mysqli_prepare($conn, $user_update_sql);
        mysqli_stmt_bind_param($user_update_stmt, "i", $user_id);
        mysqli_stmt_execute($user_update_stmt);
    }
    
    return $report_result;
}

// Process barcode if submitted
$scannedProduct = null;
$barcode = "";
$reportSuccess = false;
$reportError = "";

if(isset($_POST['barcode'])) {
    $barcode = $_POST['barcode'];
    $scannedProduct = getProductByBarcode($barcode);
    
    // Log verification if product found
    if($scannedProduct) {
        $product_id = $scannedProduct['id'];
        $user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;
        $ip = $_SERVER['REMOTE_ADDR'];
        $device = $_SERVER['HTTP_USER_AGENT'];
        
        // Insert verification log
        $log_sql = "INSERT INTO verification_logs (product_id, user_id, ip_address, device_info, status) 
                   VALUES (?, ?, ?, ?, 'authentic')";
        $log_stmt = mysqli_prepare($conn, $log_sql);
        mysqli_stmt_bind_param($log_stmt, "iiss", $product_id, $user_id, $ip, $device);
        mysqli_stmt_execute($log_stmt);
        
        // Update product verification count
        $update_sql = "UPDATE products SET verification_count = verification_count + 1 WHERE id = ?";
        $update_stmt = mysqli_prepare($conn, $update_sql);
        mysqli_stmt_bind_param($update_stmt, "i", $product_id);
        mysqli_stmt_execute($update_stmt);
        
        // Update user verification count if user is logged in
        if($user_id) {
            $user_update_sql = "UPDATE users SET verification_count = verification_count + 1 WHERE id = ?";
            $user_update_stmt = mysqli_prepare($conn, $user_update_sql);
            mysqli_stmt_bind_param($user_update_stmt, "i", $user_id);
            mysqli_stmt_execute($user_update_stmt);
        }
    }
}

// Process fake product report submission
if(isset($_POST['report_fake']) && isset($_POST['product_id']) && isset($_POST['report_barcode'])) {
    $product_id = $_POST['product_id'];
    $report_barcode = $_POST['report_barcode'];
    $reason = isset($_POST['reason']) ? $_POST['reason'] : "User reported as fake";
    $user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;
    
    // Get location data from form
    $latitude = isset($_POST['latitude']) ? $_POST['latitude'] : null;
    $longitude = isset($_POST['longitude']) ? $_POST['longitude'] : null;
    $location_address = isset($_POST['location_address']) ? $_POST['location_address'] : null;
    
    // Register the fake report with location data
    $reportSuccess = registerFakeReport($product_id, $user_id, $report_barcode, $reason, $latitude, $longitude, $location_address);
    
    if($reportSuccess) {
        // Redirect to avoid form resubmission
        header("Location: " . $_SERVER['PHP_SELF'] . "?report_status=success");
        exit();
    } else {
        $reportError = "Failed to submit report. Please try again.";
    }
}

// Check for report status in URL
$reportStatus = isset($_GET['report_status']) ? $_GET['report_status'] : '';
?>
<!doctype html>
<html class="no-js" lang="en">
    <head>
        <!-- Meta Tags -->
		<meta charset="utf-8">
		<meta http-equiv="X-UA-Compatible" content="IE=edge">
		<meta name="keywords" content="Barcode Scanner, Product Authentication">
		<meta name="description" content="Authena - Scan barcodes to verify product authenticity">
		<meta name='copyright' content=''>
		<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
		
		<!-- Title -->
        <title>Authena - Barcode Reader</title>
		
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
        
        <!-- QuaggaJS -->
        <script src="https://cdnjs.cloudflare.com/ajax/libs/quagga/0.12.1/quagga.min.js"></script>
      <link rel="stylesheet" href="qr.css">
      
      <!-- Custom styles for the fake report modal -->
      <style>
        /* Fake Report Modal Styles */
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            overflow: auto;
            background-color: rgba(0,0,0,0.6);
        }
        
        .modal-content {
            background-color: #fff;
            margin: 15% auto;
            padding: 30px;
            border-radius: 5px;
            width: 50%;
            max-width: 500px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.2);
            position: relative;
        }
        
        .close-modal {
            position: absolute;
            right: 20px;
            top: 15px;
            color: #aaa;
            font-size: 28px;
            font-weight: bold;
            cursor: pointer;
        }
        
        .close-modal:hover {
            color: #555;
        }
        
        .modal-header {
            margin-bottom: 20px;
        }
        
        .modal-header h4 {
            margin: 0;
            color: #222;
            font-size: 22px;
        }
        
        .form-group {
            margin-bottom: 15px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: 500;
        }
        
        .form-control {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        
        textarea.form-control {
            height: 100px;
        }
        
        .submit-btn {
            background: #1A76D1;
            color: #fff;
            padding: 12px 25px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
            font-weight: 500;
            transition: all 0.3s ease;
        }
        
        .submit-btn:hover {
            background: #0d62b3;
        }
        
        .alert-success {
            background-color: #d4edda;
            color: #155724;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
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
								<li><a href="#">About</a></li>
								<li><a href="#">Brands</a></li>
								<li><a href="#">Contact</a></li>
								<li><a href="#">FAQ</a></li>
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
									<a href="index.html"><img src="img/logo3.png" alt="VerifyTag Logo"></a>
								</div>
								<div class="mobile-nav"></div>
							</div>
		
							<!-- Main Navigation -->
							<div class="col-lg-7 col-md-9 col-12">
								<div class="main-menu">
									<nav class="navigation">
										<ul class="nav menu">
											<li class="active"><a href="index.html">Home</a></li>
		
											<li><a href="#">Verify Product <i class="icofont-rounded-down"></i></a>
												<ul class="dropdown">
													<li><a href="serial.php">Enter Serial Number</a></li>
                                            <li><a href="scan_pc.php">Scan QR Code</a></li>
                                            <li><a href="qr_code.php">Upload QR Image</a></li>
												</ul>
											</li>
		
											<li><a href="#">Insights <i class="icofont-rounded-down"></i></a>
												<ul class="dropdown">
													<li><a href="map-activity.html">Live Scan Map</a></li>
													<li><a href="analytics.html">Scan Analytics</a></li>
													<li><a href="fake-reports.html">Fake Product Reports</a></li>
												</ul>
											</li>
		
											<li><a href="#">Resources <i class="icofont-rounded-down"></i></a>
												<ul class="dropdown">
													<li><a href="faq.html">FAQ</a></li>
													<li><a href="blog.html">Blog</a></li>
													<li><a href="help.html">Help Center</a></li>
												</ul>
											</li>
										
											<li><a href="#">User Profile<i class="icofont-rounded-down"></i></a>
											<ul class="dropdown">
													<li><a href="profile.php">My Profile</a></li>
													<li><a href="dashboard.php">Dashboard</a></li>
													<li><a href="my-products.php">My Products</a></li>
													<li><a href="settings.php">Settings</a></li>
													<li><a href="logout.php">Logout</a></li>
												</ul>
											</li>
										</ul>
									</nav>
								</div>
							</div>
		
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
        <div class="breadcrumbs overlay">
			<div class="container">
				<div class="bread-inner">
					<div class="row">
						<div class="col-12">
							<h2>Upload QR</h2>
							<ul class="bread-list">
								<li><a href="index.html">Home</a></li>
								<li><i class="icofont-simple-right"></i></li>
								<li class="active">Upload QR</li>
							</ul>
						</div>
					</div>
				</div>
			</div>
		</div>
		<!-- End Breadcrumbs -->
	
		<!-- Main Content Area -->
		<section class="section">
			<div class="container">
                <!-- Show success message if report was submitted -->
                <?php if($reportStatus === 'success'): ?>
                <div class="alert-success">
                    <i class="fa fa-check-circle"></i> Your report has been submitted successfully. Thank you for helping us combat counterfeit products.
                </div>
                <?php endif; ?>
                
				<!-- Scanner Container -->
				<div class="scanner-container" id="scannerContainer" style="<?php echo $scannedProduct || (isset($_POST['barcode']) && !$scannedProduct) ? 'display:none;' : 'display:block;'; ?>">
					<div class="scanner-header">
						<h2>Barcode Scanner</h2>
						<p>Upload an image containing a barcode to verify product authenticity</p>
					</div>
					
					<div class="scanner-body">
						<div class="file-upload">
							<button class="file-upload-btn">
								<i class="fa fa-upload"></i> Upload Image
							</button>
							<input type="file" id="fileInput" accept="image/*">
						</div>
						
						<div class="result-container" id="result">
							Waiting for barcode...
						</div>
						
						<div class="preview-container">
							<img id="preview" style="display: none;"/>
						</div>
						
						<div class="instructions">
							<h4>Tips for successful barcode scanning:</h4>
							<ul>
								<li>Ensure the barcode is clearly visible in the image</li>
								<li>Good lighting improves recognition accuracy</li>
								<li>Keep the barcode centered in the image</li>
								<li>Avoid glare or reflections on the barcode surface</li>
								<li>Supported formats: EAN, UPC, Code 128</li>
							</ul>
						</div>
					</div>
				</div>
				<!-- Verification Result Container -->
                <div class="verification-result" id="verificationResult" style="<?php echo ($scannedProduct || (isset($_POST['barcode']) && !$scannedProduct)) ? 'display:block;' : 'display:none;'; ?>">
                    <div class="verification-header">
                        <h2>Verification Result</h2>
                    </div>
                    
                    <?php if($scannedProduct): ?>
                    <div class="authentic-badge">
                        <i class="fa fa-check-circle"></i> Authentic Product Verified
                    </div>
                    
                    <div class="qr-info">
                        <div class="qr-code-section">
                            <h4>Barcode Scanned:</h4>
                            <p id="barcodeValue"><?php echo htmlspecialchars($barcode); ?></p>
                        </div>
                        <div class="scan-details-section">
                            <h4>Scan Details:</h4>
                            <p><strong>Date:</strong> <span id="scanDate"><?php echo date('d/m/Y, H:i:s'); ?></span></p>
                            <p><strong>Location:</strong> Your Current Location</p>
                        </div>
                    </div>
                    
                    <div class="product-details">
                        <h3>Product Details</h3>
                        <table>
                            <tr>
                                <td rowspan="6" style="width:100px; vertical-align:top;">
                                    <?php if($scannedProduct['image_url']): ?>
                                        <img src="<?php echo htmlspecialchars($scannedProduct['image_url']); ?>" alt="Product Image" class="product-image">
                                    <?php else: ?>
                                        <img src="/api/placeholder/100/100" alt="Product Image" class="product-image">
                                    <?php endif; ?>
                                </td>
                                <td>Product Name</td>
                                <td id="productName"><?php echo htmlspecialchars($scannedProduct['name']); ?></td>
                            </tr>
                            <tr>
                                <td>Brand</td>
                                <td id="brandName"><?php echo htmlspecialchars($scannedProduct['brand_name'] ?? 'Unknown'); ?></td>
                            </tr>
                            <tr>
                                <td>Serial Number</td>
                                <td id="serialNumber"><?php echo htmlspecialchars($scannedProduct['unique_identifier']); ?></td>
                            </tr>
                            <tr>
                                <td>Manufacturing Date</td>
                                <td id="manufactureDate"><?php echo $scannedProduct['manufacturing_date'] ? date('F j, Y', strtotime($scannedProduct['manufacturing_date'])) : 'Unknown'; ?></td>
                            </tr>
                            <tr>
                                <td>Verification Count</td>
                                <td id="verificationCount"><?php echo $scannedProduct['verification_count']; ?> time(s)</td>
                            </tr>
                        </table>
                    </div>
                    
                    <div class="authentication-details">
                        <h3>Authentication Details</h3>
                        <div class="authentication-icons">
                            <div class="auth-icon">
                                <i class="fa fa-fingerprint"></i>
                                <h5>Unique Signature</h5>
                                <p>Valid</p>
                            </div>
                            <div class="auth-icon">
                                <i class="fa fa-link"></i>
                                <h5>Blockchain Verified</h5>
                                <p>Confirmed</p>
                            </div>
                            <div class="auth-icon">
                                <i class="fa fa-shield"></i>
                                <h5>Security Features</h5>
                                <p>All Present</p>
                            </div>
                        </div>
                    </div>
                    
                    <h4>Product Verified Successfully!</h4>
                    
                    <div class="verification-actions">
                        <button class="action-btn register-btn" id="reportFakeBtn">
                            <i class="fa fa-flag"></i> Report Fake
                        </button>
                    </div>
                    
                    <button class="scan-again-btn" id="scanAgainBtn" onclick="window.location.href='qr_code.php'">
                        <i class="fa fa-barcode"></i> Scan Another Product
                    </button>
                    <?php else: ?>
                    <?php if(isset($_POST['barcode'])): ?>
                    <div class="alert" style="background-color: #ffebee; color: #c62828; padding: 15px; border-radius: 5px; margin-bottom: 20px;">
                        <i class="fa fa-exclamation-circle"></i> 
                        <strong>Product Not Found</strong><br>
                        The scanned barcode "<?php echo htmlspecialchars($barcode); ?>" was not found in our database.
                    </div>
                    <button class="scan-again-btn" id="scanAgainBtn" onclick="location.href = window.location.pathname;">
                        <i class="fa fa-barcode"></i> Try Again
                    </button>
                    <?php endif; ?>
                    <?php endif; ?>
                </div>
			</div>
		</section>
		<!-- End Main Content Area -->
		
		<!-- Fake Report Modal -->
        <?php if($scannedProduct): ?>
        <div id="fakeReportModal" class="modal">
            <div class="modal-content">
                <span class="close-modal">&times;</span>
                <div class="modal-header">
                    <h4><i class="fa fa-flag"></i> Report Counterfeit Product</h4>
                </div>
                <form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="POST">
                    <input type="hidden" name="product_id" value="<?php echo $scannedProduct['id']; ?>">
                    <input type="hidden" name="report_barcode" value="<?php echo htmlspecialchars($barcode); ?>">
                    
                    <div class="form-group">
                        <label for="product-name">Product Name:</label>
                        <input type="text" class="form-control" id="product-name" value="<?php echo htmlspecialchars($scannedProduct['name']); ?>" readonly>
                    </div>
                    
                    <div class="form-group">
                        <label for="barcode-value">Barcode/Serial Number:</label>
                        <input type="text" class="form-control" id="barcode-value" value="<?php echo htmlspecialchars($barcode); ?>" readonly>
                    </div>
                    <div class="form-group">
                        <!-- Hidden fields to store location data -->
                        <input type="hidden" id="latitude" name="latitude" value="">
                        <input type="hidden" id="longitude" name="longitude" value="">
                        <input type="hidden" id="location_address" name="location_address" value="">
                    </div>
                    <div class="form-group">
                        <label for="reason">Reason for Reporting as Fake:</label>
                        <textarea class="form-control" id="reason" name="reason" placeholder="Please describe why you believe this product is counterfeit..." required></textarea>
                    </div>
                    
                    <?php if(!isset($_SESSION['user_id'])): ?>
                    <div class="form-group" style="background-color: #fff3cd; padding: 10px; border-radius: 4px;">
                        <p style="margin: 0; color: #856404;"><i class="fa fa-info-circle"></i> You are not logged in. For better tracking of your report, please <a href="login.php" style="font-weight: bold;">login</a> first.</p>
                    </div>
                    <?php endif; ?>
                    
                    <button type="submit" name="report_fake" class="submit-btn">
                        <i class="fa fa-paper-plane"></i> Submit Report
                    </button>
                </form>
            </div>
        </div>
        <?php endif; ?>
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
		<!-- Bootstrap JS -->
		<script src="js/bootstrap.min.js"></script>
		<!-- Main JS -->
		<script src="js/main.js"></script>
        
        <script>
            // File input handling
            document.getElementById('fileInput').addEventListener('change', function(e) {
                var file = e.target.files[0];
                if(file) {
                    var reader = new FileReader();
                    reader.onload = function(e) {
                        var img = document.getElementById('preview');
                        img.src = e.target.result;
                        img.style.display = 'block';
                        
                        // Process the image with QuaggaJS
                        processImage(e.target.result);
                    }
                    reader.readAsDataURL(file);
                }
            });
            
            // Process image with QuaggaJS
            function processImage(src) {
                Quagga.decodeSingle({
                    decoder: {
                        readers: ["ean_reader", "ean_8_reader", "code_128_reader", "code_39_reader", "upc_reader"]
                    },
                    locate: true,
                    src: src
                }, function(result) {
                    if(result && result.codeResult) {
                        document.getElementById('result').innerHTML = "Barcode detected: " + result.codeResult.code;
                        
                        // Submit the form with the barcode value
                        var form = document.createElement('form');
                        form.method = 'POST';
                        form.action = window.location.href;
                        
                        var input = document.createElement('input');
                        input.type = 'hidden';
                        input.name = 'barcode';
                        input.value = result.codeResult.code;
                        
                        form.appendChild(input);
                        document.body.appendChild(form);
                        form.submit();
                    } else {
                        document.getElementById('result').innerHTML = "No barcode detected. Please try again with a clearer image.";
                    }
                });
            }
            
            // Report Fake Modal
            var modal = document.getElementById("fakeReportModal");
            var btn = document.getElementById("reportFakeBtn");
            var span = document.getElementsByClassName("close-modal")[0];
            
            // When the user clicks the button, open the modal 
            if(btn) {
                btn.onclick = function() {
                    modal.style.display = "block";
                    // Get geolocation when opening the modal
                    getLocation();
                }
            }
            
            // When the user clicks on <span> (x), close the modal
            if(span) {
                span.onclick = function() {
                    modal.style.display = "none";
                }
            }
            
            // When the user clicks anywhere outside of the modal, close it
            window.onclick = function(event) {
                if (event.target == modal) {
                    modal.style.display = "none";
                }
            }
            
            // Get user's geolocation
            function getLocation() {
                if (navigator.geolocation) {
                    navigator.geolocation.getCurrentPosition(showPosition, showError);
                } else {
                    console.log("Geolocation is not supported by this browser.");
                }
            }
            
            function showPosition(position) {
                document.getElementById("latitude").value = position.coords.latitude;
                document.getElementById("longitude").value = position.coords.longitude;
                
                // Use reverse geocoding to get address
                fetch(`https://nominatim.openstreetmap.org/reverse?format=json&lat=${position.coords.latitude}&lon=${position.coords.longitude}`)
                    .then(response => response.json())
                    .then(data => {
                        document.getElementById("location_address").value = data.display_name;
                    })
                    .catch(error => {
                        console.log("Error getting address: ", error);
                    });
            }
            
            function showError(error) {
                switch(error.code) {
                    case error.PERMISSION_DENIED:
                        console.log("User denied the request for Geolocation.");
                        break;
                    case error.POSITION_UNAVAILABLE:
                        console.log("Location information is unavailable.");
                        break;
                    case error.TIMEOUT:
                        console.log("The request to get user location timed out.");
                        break;
                    case error.UNKNOWN_ERROR:
                        console.log("An unknown error occurred.");
                        break;
                }
            }
        </script>
    </body>
</html>     