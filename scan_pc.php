<?php
session_start();
// Database connection
$db_host = "localhost";
$db_user = "root";  // Replace with your database username
$db_pass = "";  // Replace with your database password
$db_name = "authena";

$conn = new mysqli($db_host, $db_user, $db_pass, $db_name);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// API endpoint to verify product
if (isset($_POST['action']) && $_POST['action'] == 'verify_product') {
    $qrCode = $_POST['qr_code'];
    $userId = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;
    $ip = $_SERVER['REMOTE_ADDR'];
    $deviceInfo = $_SERVER['HTTP_USER_AGENT'];
    $lat = isset($_POST['lat']) ? $_POST['lat'] : null;
    $lng = isset($_POST['lng']) ? $_POST['lng'] : null;
    $address = isset($_POST['address']) ? $_POST['address'] : null;
    
    // Check if product exists in database
    $stmt = $conn->prepare("SELECT p.*, b.name as brand_name, c.name as category_name 
                           FROM products p 
                           LEFT JOIN brands b ON p.brand_id = b.id 
                           LEFT JOIN categories c ON p.category_id = c.id 
                           WHERE p.unique_identifier = ? OR p.product_code = ?");
    $stmt->bind_param("ss", $qrCode, $qrCode);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $response = [
        'status' => 'unknown',
        'product' => null,
        'message' => 'Unknown product'
    ];
    
    if ($result->num_rows > 0) {
        $product = $result->fetch_assoc();
        
        // Check if product is active
        if ($product['status'] == 'active') {
            $response = [
                'status' => 'authentic',
                'product' => $product,
                'message' => 'Authentic product verified'
            ];
        } else {
            $response = [
                'status' => 'fake',
                'product' => null,
                'message' => 'This product is not active in our system'
            ];
        }
        
        // Log verification
        $stmt = $conn->prepare("INSERT INTO verification_logs (product_id, user_id, ip_address, device_info, 
                                location_lat, location_lng, location_address, status) 
                                VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("iissddsss", $product['id'], $userId, $ip, $deviceInfo, $lat, $lng, $address, $response['status']);
        $stmt->execute();
        
        // Update product verification count
        $stmt = $conn->prepare("UPDATE products SET verification_count = verification_count + 1 WHERE id = ?");
        $stmt->bind_param("i", $product['id']);
        $stmt->execute();
        
        // Update user verification count if user is logged in
        if ($userId) {
            $stmt = $conn->prepare("UPDATE users SET verification_count = verification_count + 1 WHERE id = ?");
            $stmt->bind_param("i", $userId);
            $stmt->execute();
        }
    }
    
    echo json_encode($response);
    exit;
}

// API endpoint to report fake product
if (isset($_POST['action']) && $_POST['action'] == 'report_fake') {
    $productId = $_POST['product_id'];
    $userId = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;
    $reportType = $_POST['report_type'];
    $description = $_POST['description'];
    $lat = isset($_POST['lat']) ? $_POST['lat'] : null;
    $lng = isset($_POST['lng']) ? $_POST['lng'] : null;
    $address = isset($_POST['address']) ? $_POST['address'] : null;
    
    // Insert into fake_reports table
    $stmt = $conn->prepare("INSERT INTO fake_reports (product_id, user_id, report_type, description, 
                           location_lat, location_lng, location_address) 
                           VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("iissdds", $productId, $userId, $reportType, $description, $lat, $lng, $address);
    $stmt->execute();
    
    // Update user report count if user is logged in
    if ($userId) {
        $stmt = $conn->prepare("UPDATE users SET report_count = report_count + 1 WHERE id = ?");
        $stmt->bind_param("i", $userId);
        $stmt->execute();
    }
    
    echo json_encode(['success' => true, 'message' => 'Report submitted successfully']);
    exit;
}
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
        <title>Authena - Scan QR Code</title>
		
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
		
		<!-- Get Pro Button -->
		
	
		<!-- Header Area -->
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
											<li ><a href="index.html">Home</a></li>
		
											<li class="active"><a href="#">Verify Product <i class="icofont-rounded-down"></i></a>
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
							<h2>Scan QR</h2>
							<ul class="bread-list">
								<li><a href="index.html">Home</a></li>
								<li><i class="icofont-simple-right"></i></li>
								<li class="active">Scan QR</li>
							</ul>
						</div>
					</div>
				</div>
			</div>
		</div>

		<!-- Scan QR Code Area -->
		<!-- Scan QR Code Area - Improved Layout -->
<section class="scan-qr section">
    <div class="container">
        <div class="row">
            <div class="col-12">
                <div class="section-title">
                    <h2>Verify Your Product</h2>
                    <p>Use your device's camera to scan the QR code on your product and instantly check its authenticity. Our secure blockchain verification ensures you know your product is genuine.</p>
                </div>
            </div>
        </div>
        
        <div class="row">
            <div class="col-lg-8 offset-lg-2 col-md-10 offset-md-1 col-12">
                <div class="scan-container">
                    <!-- Scanner Instructions -->
                    <div class="text-center mb-4">
                        <div class="scan-instructions-icon mb-3">
                            <i class="icofont-qr-code" style="font-size: 36px; color: #1A76D1;"></i>
                        </div>
                        <h5>Position the QR code within the frame</h5>
                        <p class="text-muted">Hold your device steady for a few seconds</p>
                    </div>
                    
                    <!-- QR Scanner Element with Corner Markers -->
                    <div class="scanner-wrapper position-relative">
                        <div id="reader" class="mx-auto" style="width: 400px; height: 400px; max-width: 100%;"></div>
                        <div class="scan-overlay">
                            <div class="corner corner-tl"></div>
                            <div class="corner corner-tr"></div>
                            <div class="corner corner-bl"></div>
                            <div class="corner corner-br"></div>
                            <div class="scan-line"></div>
                        </div>
                    </div>
                </div>
                
                <!-- Results Area (Initially Hidden) -->
                <div id="scan-result" class="mt-4" style="display: none;">
                    <div class="verification-result p-4 mb-4">
                        <h4 class="mb-3">Verification Result</h4>
                        <div id="result-container">
                            <div id="verification-status" class="mt-3 mb-3"></div>
                            
                            <div class="scan-details p-3 mb-3" style="background: #f8f9fa; border-radius: 8px;">
                                <div class="row">
                                    <div class="col-md-6">
                                        <p class="mb-1"><strong>QR Code Scanned:</strong></p>
                                        <p id="scanned-result" class="mb-2"></p>
                                    </div>
                                    <div class="col-md-6">
                                        <p class="mb-1"><strong>Scan Details:</strong></p>
                                        <p>Date: <span id="scan-date"></span></p>
                                        <p>Location: <span id="scan-location">Your Current Location</span></p>
                                    </div>
                                </div>
                            </div>
                            
                            <div id="product-details" class="mt-4"></div>
                            
                            <div id="authentic-actions" class="text-center mt-4" style="display: none;">
                                <h5 class="mb-3">Product Verified Successfully!</h5>
                                <div class="row">
                          
                                    <div class="col-md-4">
                                        <button class="btn btn-outline-primary btn-block mb-2"><i class="fa fa-star"></i> Register Fake</button>
                                    </div>
                                </div>
                            </div>
                            
                            <div id="fake-actions" class="text-center mt-4" style="display: none;">
                                <button class="btn btn-danger"><i class="fa fa-flag"></i> Report This Counterfeit</button>
                                <p class="mt-3 text-muted">Your report helps us track and prevent counterfeit products in the market.</p>
                            </div>
                        </div>
                    </div>
                    <div class="text-center">
                        <button id="restart-button" class="btn" style="display: none;">Scan Another Product</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Benefits Section - Improved Visual Appeal -->

        

<!-- Modify the scan-result section to enhance the post-scan display -->
<div id="scan-result" class="mt-4" style="display: none;">
    <div class="verification-result p-4 mb-4">
        <h4 class="mb-3 text-center">Verification Result</h4>
        <div id="result-container">
            <div id="verification-status" class="mt-3 mb-4"></div>
            
            <div class="scan-details p-3 mb-3" style="background: #f8f9fa; border-radius: 8px;">
                <div class="row">
                    <div class="col-md-6">
                        <p class="mb-1"><strong>QR Code Scanned:</strong></p>
                        <p id="scanned-result" class="mb-2"></p>
                    </div>
                    <div class="col-md-6">
                        <p class="mb-1"><strong>Scan Details:</strong></p>
                        <p>Date: <span id="scan-date"></span></p>
                        <p>Location: <span id="scan-location">Your Current Location</span></p>
                    </div>
                </div>
            </div>
            
            <div id="product-details" class="mt-4"></div>
            
            <div id="authentic-actions" class="text-center mt-4" style="display: none;">
                <h5 class="mb-3">Product Verified Successfully!</h5>
                <div class="row">
                
                    <div class="col-md-4">
                        <button class="btn btn-outline-primary btn-block"><i class="fa fa-star"></i> Register Fake</button>
                    </div>
                </div>
            </div>
            
            <div id="fake-actions" class="text-center mt-4" style="display: none;">
                <button class="btn btn-danger"><i class="fa fa-flag"></i> Report This Counterfeit</button>
                <p class="mt-3 text-muted">Your report helps us track and prevent counterfeit products in the market.</p>
            </div>
        </div>
    </div>
    <div class="text-center">
        <button id="restart-button" class="btn" style="display: none;">Scan Another Product</button>
    </div>
</div>



<!-- Update the QR Code Scanner Script to enhance the post-scan experience -->
<script>
// Improved QR Code Scanner with Database Integration
document.addEventListener('DOMContentLoaded', function () {
    // Initialize the scanner
    const html5QrCode = new Html5Qrcode('reader');
    const scannerWrapper = document.querySelector('.scanner-wrapper');
    
    // Get user's location if available
    let userLat = null;
    let userLng = null;
    let userAddress = "Location not available";
    
    if (navigator.geolocation) {
        navigator.geolocation.getCurrentPosition(function(position) {
            userLat = position.coords.latitude;
            userLng = position.coords.longitude;
            
            // Get address from coordinates (simplified, normally would use a geocoding service)
            fetch(`https://nominatim.openstreetmap.org/reverse?format=json&lat=${userLat}&lon=${userLng}`)
                .then(response => response.json())
                .then(data => {
                    if (data && data.display_name) {
                        userAddress = data.display_name;
                        document.getElementById('scan-location').textContent = userAddress;
                    }
                })
                .catch(error => console.error("Error getting address:", error));
        });
    }
    
    // Function to handle successful scans
    const qrCodeSuccessCallback = (decodedText, decodedResult) => {
        // Stop scanning
        html5QrCode.stop();
        
        // Hide the scanner element completely
        scannerWrapper.style.display = 'none';
        
        // Display the scan result section
        document.getElementById('scan-result').style.display = 'block';
        document.getElementById('restart-button').style.display = 'inline-block';
        
        // Set the scanned result text
        document.getElementById('scanned-result').innerHTML = decodedText;
        
        // Set current date
        const now = new Date();
        document.getElementById('scan-date').textContent = now.toLocaleString();
        
        // Animate the result container
        document.getElementById('scan-result').classList.add('active');
        
        // Scroll to results
        document.getElementById('scan-result').scrollIntoView({
            behavior: 'smooth',
            block: 'start'
        });
        
        // Verify product with the database
        verifyProduct(decodedText);
    };
    
    // Configure scanner options
    const config = { 
        fps: 10, 
        qrbox: { width: 250, height: 250 },
        aspectRatio: 1.0
    };
    
    // Start scanning
    html5QrCode.start(
        { facingMode: "environment" }, 
        config, 
        qrCodeSuccessCallback
    ).catch((err) => {
        console.error("Error starting scanner:", err);
        document.getElementById('reader').innerHTML = `
            <div class="alert alert-danger">
                <p>Camera access denied or not available. Please ensure you've granted camera permissions.</p>
                <p>Error: ${err}</p>
            </div>
        `;
    });
    
    // Restart button functionality
    document.getElementById('restart-button').addEventListener('click', function() {
        // Show scanner again
        scannerWrapper.style.display = 'block';
        
        // Hide results
        document.getElementById('scan-result').style.display = 'none';
        document.getElementById('scan-result').classList.remove('active');
        document.getElementById('restart-button').style.display = 'none';
        document.getElementById('verification-status').innerHTML = '';
        document.getElementById('product-details').innerHTML = '';
        document.getElementById('authentic-actions').style.display = 'none';
        document.getElementById('fake-actions').style.display = 'none';
        
        // Scroll back to scanner
        scannerWrapper.scrollIntoView({
            behavior: 'smooth',
            block: 'start'
        });
        
        // Start scanning again
        html5QrCode.start(
            { facingMode: "environment" }, 
            config, 
            qrCodeSuccessCallback
        );
    });
    
    // Function to verify product with database
    function verifyProduct(code) {
        // Show loading status with progress animation
        document.getElementById('verification-status').innerHTML = `
            <div class="verification-loading">
                <i class="fa fa-circle-o-notch fa-spin"></i> 
                <div>Verifying product authenticity...</div>
                <div class="progress mt-2" style="height: 10px;">
                    <div class="progress-bar progress-bar-striped progress-bar-animated" 
                         role="progressbar" style="width: 0%" 
                         aria-valuenow="0" aria-valuemin="0" aria-valuemax="100"></div>
                </div>
            </div>
        `;
        
        // Animate progress bar
        const progressBar = document.querySelector('.progress-bar');
        let width = 0;
        const interval = setInterval(() => {
            if (width >= 100) {
                clearInterval(interval);
            } else {
                width += 5;
                progressBar.style.width = width + '%';
            }
        }, 100);
        
        // Send verification request to server
        const formData = new FormData();
        formData.append('action', 'verify_product');
        formData.append('qr_code', code);
        formData.append('lat', userLat);
        formData.append('lng', userLng);
        formData.append('address', userAddress);
        
        fetch('verify_products.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            clearInterval(interval);
            progressBar.style.width = '100%';
            
            setTimeout(() => {
                if (data.status === 'authentic') {
                    // Authentic product
                    document.getElementById('verification-status').innerHTML = `
                        <div class="verification-authentic">
                            <i class="fa fa-check-circle"></i> Authentic Product Verified
                        </div>
                    `;
                    
                    // Display product details from database
                    const product = data.product;
                    document.getElementById('product-details').innerHTML = `
                        <div class="product-info">
                            <h5>Product Details</h5>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="product-image text-center mb-3">
                                        <img src="${product.image_url || '/api/placeholder/300/300'}" alt="Product Image" class="img-fluid rounded" style="max-height: 200px;">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <table class="table table-bordered">
                                        <tr>
                                            <th>Product Name</th>
                                            <td>${product.name}</td>
                                        </tr>
                                        <tr>
                                            <th>Brand</th>
                                            <td>${product.brand_name}</td>
                                        </tr>
                                        <tr>
                                            <th>Category</th>
                                            <td>${product.category_name}</td>
                                        </tr>
                                        <tr>
                                            <th>Serial Number</th>
                                            <td>${product.unique_identifier}</td>
                                        </tr>
                                        <tr>
                                            <th>Manufacturing Date</th>
                                            <td>${formatDate(product.manufacturing_date)}</td>
                                        </tr>
                                        <tr>
                                            <th>Verification Count</th>
                                            <td>${product.verification_count} time(s)</td>
                                        </tr>
                                    </table>
                                </div>
                            </div>
                            
                            <div class="authentication-details mt-4">
                                <h5>Authentication Details</h5>
                                <div class="card">
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-md-4 text-center">
                                                <div class="auth-detail">
                                                    <div class="auth-icon mb-2">
                                                        <i class="fa fa-fingerprint" style="font-size: 28px; color: #1A76D1;"></i>
                                                    </div>
                                                    <h6>Unique Signature</h6>
                                                    <p class="small">Valid</p>
                                                </div>
                                            </div>
                                            <div class="col-md-4 text-center">
                                                <div class="auth-detail">
                                                    <div class="auth-icon mb-2">
                                                        <i class="fa fa-link" style="font-size: 28px; color: #1A76D1;"></i>
                                                    </div>
                                                    <h6>Blockchain Verified</h6>
                                                    <p class="small">Confirmed</p>
                                                </div>
                                            </div>
                                            <div class="col-md-4 text-center">
                                                <div class="auth-detail">
                                                    <div class="auth-icon mb-2">
                                                        <i class="fa fa-shield" style="font-size: 28px; color: #1A76D1;"></i>
                                                    </div>
                                                    <h6>Security Features</h6>
                                                    <p class="small">All Present</p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    `;
                    
                    // Store product ID for report functionality
                    document.getElementById('authentic-actions').setAttribute('data-product-id', product.id);
                    
                    // Show authentic product actions
                    document.getElementById('authentic-actions').style.display = 'block';
                    
                } else {
                    // Counterfeit or unknown product
                    document.getElementById('verification-status').innerHTML = `
                        <div class="verification-fake">
                            <i class="fa fa-exclamation-triangle"></i> Warning: Potential Counterfeit Detected
                        </div>
                    `;
                    
                    // Display warning message with more details
                    document.getElementById('product-details').innerHTML = `
                        <div class="counterfeit-warning p-4">
                            <div class="alert alert-danger">
                                <h5><i class="fa fa-exclamation-circle"></i> Authentication Failed</h5>
                                <p>${data.message || 'This product code does not match our database records or has been reported as counterfeit.'}</p>
                            </div>
                            
                            <div class="row mt-4">
                                <div class="col-md-6">
                                    <h6>Why did verification fail?</h6>
                                    <ul>
                                        <li>Unknown product identifier</li>
                                        <li>QR code has been duplicated</li>
                                        <li>Product code has been reported as counterfeit</li>
                                        <li>Multiple scans from different locations</li>
                                    </ul>
                                </div>
                                <div class="col-md-6">
                                    <h6>What should you do?</h6>
                                    <ul>
                                        <li>Contact the seller immediately</li>
                                        <li>Report this counterfeit using the button below</li>
                                        <li>Do not use the product if it's consumable</li>
                                        <li>Keep purchase receipts and packaging</li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    `;
                    
                    // Store scanned code for report functionality
                    document.getElementById('fake-actions').setAttribute('data-product-code', code);
                    
                    // Show fake product actions
                    document.getElementById('fake-actions').style.display = 'block';
                    
                    // Setup report button functionality
                    document.querySelector('#fake-actions .btn-danger').addEventListener('click', function() {
                        // Open report modal or redirect to report page
                        reportFakeProduct(code);
                    });
                }
            }, 500);
        })
        .catch(error => {
            clearInterval(interval);
            console.error("Error verifying product:", error);
            
            document.getElementById('verification-status').innerHTML = `
                <div class="verification-error">
                    <i class="fa fa-exclamation-circle"></i> Verification Error
                </div>
            `;
            
            document.getElementById('product-details').innerHTML = `
                <div class="alert alert-warning">
                    <p>There was an error connecting to the verification server. Please try again later.</p>
                </div>
            `;
        });
    }
    
    // Function to report fake product
    // Fix the reportFakeProduct function
// Function to report fake product
// Function to report fake product (updated)
function reportFakeProduct(code) {
    // Show loading indicator
    const reportButton = document.querySelector('#fake-actions .btn-danger');
    const originalText = reportButton.innerHTML;
    reportButton.innerHTML = '<i class="fa fa-spinner fa-spin"></i> Submitting...';
    reportButton.disabled = true;
    
    // Create form data for API call
    const formData = new FormData();
    formData.append('action', 'report_fake');
    formData.append('product_code', code);
    formData.append('report_type', 'counterfeit');
    formData.append('description', 'Reported via QR scan verification');
    
    // Add location data if available
    if (userLat && userLng) {
        formData.append('lat', userLat);
        formData.append('lng', userLng);
    }
    if (userAddress) {
        formData.append('address', userAddress);
    }
    
    // Debug output
    console.log("Submitting report with data:", {
        action: 'report_fake',
        product_code: code,
        lat: userLat,
        lng: userLng
    });
    
    // Send to the correct endpoint
    fetch('verify_product.php', {
        method: 'POST',
        body: formData
    })
    .then(response => {
        console.log("Raw response:", response);
        return response.json();
    })
    .then(data => {
        console.log("Response data:", data);
        
        // Restore button
        reportButton.innerHTML = originalText;
        reportButton.disabled = false;
        
        if (data.success) {
            alert('Thank you for reporting this counterfeit product. Our team will investigate.');
        } else {
            // Show specific error message
            alert(data.message || 'There was an error submitting your report. Please try again later.');
        }
    })
    .catch(error => {
        console.error("Error reporting fake product:", error);
        
        // Restore button
        reportButton.innerHTML = originalText;
        reportButton.disabled = false;
        
        alert('Connection error while submitting your report. Please check your internet connection and try again.');
    });
}

// Function to report authentic product as fake (updated)
function reportAuthenticAsFake(productId) {
    // Show loading indicator
    const registerButton = document.querySelector('#authentic-actions .btn-outline-primary');
    const originalText = registerButton.innerHTML;
    registerButton.innerHTML = '<i class="fa fa-spinner fa-spin"></i> Submitting...';
    registerButton.disabled = true;
    
    // Get product code from verified product
    const productCode = document.getElementById('scanned-result').textContent.trim();
    
    // Create form data
    const formData = new FormData();
    formData.append('action', 'report_fake');
    formData.append('product_id', productId); // Use ID if available
    formData.append('product_code', productCode); // Also send code as backup
    formData.append('report_type', 'suspected_counterfeit');
    formData.append('description', 'Product verified as authentic but suspected to be counterfeit');
    
    // Add location data if available
    if (userLat && userLng) {
        formData.append('lat', userLat);
        formData.append('lng', userLng);
    }
    if (userAddress) {
        formData.append('address', userAddress);
    }
    
    // Debug output
    console.log("Submitting authentic-as-fake report with data:", {
        action: 'report_fake',
        product_id: productId,
        product_code: productCode
    });
    
    // Send to the correct endpoint
    fetch('verify_product.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        console.log("Response data:", data);
        
        // Restore button state
        registerButton.innerHTML = originalText;
        registerButton.disabled = false;
        
        if (data.success) {
            // Show success message
            alert('Thank you for your report. Our team will investigate this product further.');
            
            // Update UI to reflect the report
            document.getElementById('verification-status').innerHTML = `
                <div class="verification-warning">
                    <i class="fa fa-exclamation-circle"></i> Product Reported for Investigation
                </div>
            `;
            
            // Hide the authentic actions section
            document.getElementById('authentic-actions').style.display = 'none';
        } else {
            // Show specific error message
            alert(data.message || 'There was an error submitting your report. Please try again later.');
        }
    })
    .catch(error => {
        console.error("Error reporting product:", error);
        
        // Restore button state
        registerButton.innerHTML = originalText;
        registerButton.disabled = false;
        
        alert('Connection error while submitting your report. Please check your internet connection and try again.');
    });
}
    // Helper function to format dates
    function formatDate(dateString) {
        if (!dateString) return 'N/A';
        
        const date = new Date(dateString);
        return date.toLocaleDateString();
    }
    
// Add this to the existing JavaScript code, right after the report button click handler

// Setup Register Fake button click handler
document.addEventListener('click', function(event) {
    if (event.target.closest('#authentic-actions .btn-outline-primary')) {
        const productId = document.getElementById('authentic-actions').getAttribute('data-product-id');
        
        // Ask for confirmation before reporting
        if (confirm('Are you sure you want to report this product as fake? This helps us identify counterfeit products in the market.')) {
            // Call the report function with the product ID
            reportAuthenticAsFake(productId);
        }
    }
});

// Function to report a product that was verified as authentic but is suspected to be fake
function reportAuthenticAsFake(productId) {
    // Show loading indicator or some feedback
    const registerButton = document.querySelector('#authentic-actions .btn-outline-primary');
    const originalText = registerButton.innerHTML;
    registerButton.innerHTML = '<i class="fa fa-spinner fa-spin"></i> Submitting...';
    registerButton.disabled = true;
    
    // Get location data for the report
    const lat = userLat || null;
    const lng = userLng || null;
    const address = userAddress || "Location not available";
    
    // Prepare form data for the report
    const formData = new FormData();
    formData.append('action', 'report_fake');
    formData.append('product_id', productId);
    formData.append('report_type', 'suspected_counterfeit');
    formData.append('description', 'Product verified as authentic but suspected to be counterfeit');
    formData.append('lat', lat);
    formData.append('lng', lng);
    formData.append('address', address);
    
    // Send report to server
    fetch('verify_products.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        // Restore button state
        registerButton.innerHTML = originalText;
        registerButton.disabled = false;
        
        if (data.success) {
            // Show success message
            alert('Thank you for your report. Our team will investigate this product further.');
            
            // Update UI to reflect the report
            document.getElementById('verification-status').innerHTML = `
                <div class="verification-warning">
                    <i class="fa fa-exclamation-circle"></i> Product Reported for Investigation
                </div>
            `;
            
            // Hide the authentic actions section
            document.getElementById('authentic-actions').style.display = 'none';
        } else {
            // Show error message
            alert('There was an error submitting your report. Please try again later.');
        }
    })
    .catch(error => {
        // Restore button state
        registerButton.innerHTML = originalText;
        registerButton.disabled = false;
        
        console.error("Error reporting product:", error);
        alert('There was an error submitting your report. Please try again later.');
    });
}
  
});
</script>
		
		
		<!-- jquery Min JS -->
        <script src="js/jquery.min.js"></script>
		<!-- jquery Migrate JS -->
		<script src="js/jquery-migrate-3.0.0.js"></script>
		<!-- jquery Ui JS -->
		<script src="js/jquery-ui.min.js"></script>
		<!-- Easing JS a-->
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
		<!-- HTML5 QR Code Scanner -->
		<script src="https://cdnjs.cloudflare.com/ajax/libs/html5-qrcode/2.3.4/html5-qrcode.min.js"></script>
		
		<!-- QR Code Scanner Script -->
		<script>
			document.addEventListener('DOMContentLoaded', function () {
                // Initialize the scanner
                const html5QrCode = new Html5Qrcode('reader');
                
                // Function to handle successful scans
                const qrCodeSuccessCallback = (decodedText, decodedResult) => {
                    // Stop scanning
                    html5QrCode.stop();
                    
                    // Display the scan result section
                    document.getElementById('scan-result').style.display = 'block';
                    document.getElementById('restart-button').style.display = 'inline-block';
                    
                    // Set the scanned result text
                    document.getElementById('scanned-result').innerHTML = `<strong>QR Code:</strong> ${decodedText}`;
                    
                    // Simulate verification process
                    verifyProduct(decodedText);
                };
                
                // Configure scanner options
                const config = { 
                    fps: 10, 
                    qrbox: { width: 250, height: 250 },
                    aspectRatio: 1.0
                };
                
                // Start scanning
                html5QrCode.start(
                    { facingMode: "environment" }, 
                    config, 
                    qrCodeSuccessCallback
                ).catch((err) => {
                    console.error("Error starting scanner:", err);
                    document.getElementById('reader').innerHTML = `
                        <div class="alert alert-danger">
                            <p>Camera access denied or not available. Please ensure you've granted camera permissions.</p>
                            <p>Error: ${err}</p>
                        </div>
                    `;
                });
                
                // Restart button functionality
                document.getElementById('restart-button').addEventListener('click', function() {
                    // Hide results
                    document.getElementById('scan-result').style.display = 'none';
                    document.getElementById('restart-button').style.display = 'none';
                    document.getElementById('verification-status').innerHTML = '';
                    document.getElementById('product-details').innerHTML = '';
                    
                    // Start scanning again
                    html5QrCode.start(
                        { facingMode: "environment" }, 
                        config, 
                        qrCodeSuccessCallback
                    );
                });
                
                // Function to simulate product verification
                function verifyProduct(code) {
                    // Show loading status
                    document.getElementById('verification-status').innerHTML = `
                        <div class="verification-loading">
                            <i class="fa fa-circle-o-notch fa-spin"></i> Verifying product...
                        </div>
                    `;
                    
                    // Simulate API call delay
                    setTimeout(() => {
                        // For demo purposes, we'll verify codes that start with "AUTH" 
                        if (code.startsWith('AUTH') || Math.random() > 0.3) {
                            // Authentic product
                            document.getElementById('verification-status').innerHTML = `
                                <div class="verification-authentic">
                                    <i class="fa fa-check-circle"></i> Authentic Product
                                </div>
                            `;
                            
                            // Display simulated product details
                            document.getElementById('product-details').innerHTML = `
                                <div class="product-info">
                                    <h5>Product Details</h5>
                                    <table class="table table-bordered">
                                        <tr>
                                            <th>Product Name</th>
                                            <td>Premium Watch XYZ</td>
                                        </tr>
                                        <tr>
                                            <th>Brand</th>
                                            <td>Luxury Brand Co.</td>
                                        </tr>
                                        <tr>
                                            <th>Serial Number</th>
                                            <td>${code}</td>
                                        </tr>
                                        <tr>
                                            <th>Manufacturing Date</th>
                                            <td>January 15, 2025</td>
                                        </tr>
                                        <tr>
                                            <th>Verification Count</th>
                                            <td>1 time(s)</td>
                                        </tr>
                                    </table>
                                </div>
                            `;
                        } else {
                            // Counterfeit product
                            document.getElementById('verification-status').innerHTML = `
                                <div class="verification-fake">
                                    <i class="fa fa-exclamation-triangle"></i> Warning: Potential Counterfeit
                                </div>
                            `;
                            
                            // Display warning message
                            document.getElementById('product-details').innerHTML = `
                                <div class="counterfeit-warning">
                                    <p>This product code does not match our database records or has been scanned an unusual number of times.</p>
                                    <p>If you purchased this item believing it to be authentic, please <a href="report-fake.php">report this counterfeit</a>.</p>
                                </div>
                            `;
                        }
                    }, 2000);
                }
            });
		</script>
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
		<!-- Additional styles for verification results -->
		
    </body>
</html>
