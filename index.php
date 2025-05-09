<?php session_start(); ?>
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
		
		
        <title>Authena - Smart Product Authentication Platform.</title>
		
        <link rel="icon" href="img/favicon.png">
		
		<link href="https://fonts.googleapis.com/css?family=Poppins:200i,300,300i,400,400i,500,500i,600,600i,700,700i,800,800i,900,900i&display=swap" rel="stylesheet">
		<link rel="stylesheet" href="css/bootstrap.min.css">
		<link rel="stylesheet" href="css/nice-select.css">
        <link rel="stylesheet" href="css/font-awesome.min.css">
        <link rel="stylesheet" href="css/icofont.css">
		<link rel="stylesheet" href="css/slicknav.min.css">
        <link rel="stylesheet" href="css/owl-carousel.css">
		<link rel="stylesheet" href="css/datepicker.css">
        <link rel="stylesheet" href="css/animate.min.css">
        <link rel="stylesheet" href="css/magnific-popup.css">
        <link rel="stylesheet" href="css/normalize.css">
        <link rel="stylesheet" href="style.css">
        <link rel="stylesheet" href="css/responsive.css">
		
    </head>
    <body>
	
		
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
                                            <li class="active"><a href="index.php">Home</a></li>
        
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
<script>
									document.addEventListener('DOMContentLoaded', function() {
    // Find the profile dropdown and adjust its position
    const profileLink = document.querySelector('.nav li:last-child > a');
    const profileDropdown = document.querySelector('.nav li:last-child .dropdown');
    
    if (profileLink && profileDropdown) {
        // Set position directly
        profileDropdown.style.top = '50px';
        profileDropdown.style.transform = 'none';
    }
});
									</script>
		<!-- End Header Area -->
		
		<!-- Slider Area -->
		<section class="slider">
			<div class="hero-slider">
				
				<!-- Start Single Slider -->
				<div class="single-slider" style="background-image:url('img/slider.avif')">
					<div class="container">
						<div class="row">
							<div class="col-lg-7">
								<div class="text">
									<h1>Verify <span>Products</span> Anytime, <span>Anywhere</span></h1>
									<p>Scan QR codes, enter serial numbers, or upload product tags to instantly check authenticity.</p>
									<div class="button">
										<a href="serial.php" class="btn">Start Verifying</a>
										<a href="about.php" class="btn primary">Learn More</a>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
				<!-- End Single Slider -->
		
				<!-- Start Single Slider -->
				<div class="single-slider" style="background-image:url('img/slider4.jpg')">
					<div class="container">
						<div class="row">
							<div class="col-lg-7">
								<div class="text">
									<h1>Protect <span>Consumers</span> & Build <span>Trust</span></h1>
									<p>Help eliminate counterfeit products and empower your customers with real-time verification tools.</p>
									<div class="button">
										<a href="brands.php" class="btn">Explore Brands</a>
										<a href="fake-reports.php" class="btn primary">Report a Fake</a>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
				<!-- End Single Slider -->
		
				<!-- Start Single Slider -->
				<div class="single-slider" style="background-image:url('img/slider5.jpg')">
					<div class="container">
						<div class="row">
							<div class="col-lg-7">
								<div class="text">
									<h1>Your Shield Against <span>Fake Products</span></h1>
									<p>Authena is your trusted platform for detecting and reporting fake or tampered products globally.</p>
									<div class="button">
										<a href="qr_code.php" class="btn">Upload QR</a>
										<a href="about.php" class="btn primary">Contact Support</a>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
				<!-- End Single Slider -->
		
			</div>
		</section>
		
		<!--/ End Slider Area -->
		
		<!-- Start Schedule Area -->
		<section class="schedule">
			<div class="container">
				<div class="schedule-inner">
					<div class="row">
						
						<!-- Verify Methods -->
						<div class="col-lg-4 col-md-6 col-12">
							<div class="single-schedule first">
								<div class="inner">
									<div class="icon">
										<i class="fa fa-qrcode"></i>
									</div>
									<div class="single-content">
										<span>Fast & Secure</span>
										<h4>Verification Methods</h4>
										<p>Choose to verify via live QR scan, manual serial entry, or by uploading a product tag image.</p>
										<a href="serial.php">LEARN MORE <i class="fa fa-long-arrow-right"></i></a>
									</div>
								</div>
							</div>
						</div>
		
						<!-- Report Fake -->
						<div class="col-lg-4 col-md-6 col-12">
							<div class="single-schedule middle">
								<div class="inner">
									<div class="icon">
										<i class="fa fa-exclamation-triangle"></i>
									</div>
									<div class="single-content">
										<span>Community Support</span>
										<h4>Report Fake Products</h4>
										<p>Detected a counterfeit item? Help others by reporting it. Your action keeps the ecosystem safe.</p>
										<a href="fake-reports.php">LEARN MORE <i class="fa fa-long-arrow-right"></i></a>
									</div>
								</div>
							</div>
						</div>
		
						<!-- Platform Availability -->
						<div class="col-lg-4 col-md-12 col-12">
							<div class="single-schedule last">
								<div class="inner">
									<div class="icon">
										<i class="fa fa-clock-o"></i>
									</div>
									<div class="single-content">
										<span>24/7 Access</span>
										<h4>Platform Availability</h4>
										<ul class="time-sidual">
											<li class="day">All Days <span>00:00 - 23:59</span></li>
											<li class="day">Support Hours <span>9:00 - 18:00</span></li>
											<li class="day">Report Review <span>Within 24 Hours</span></li>
										</ul>
										<a href="about.php">LEARN MORE <i class="fa fa-long-arrow-right"></i></a>
									</div>
								</div>
							</div>
						</div>
		
					</div>
				</div>
			</div>
		</section>
		
		<!--/End Start schedule Area -->

		<!-- Start Feautes -->
		<section class="Feautes section">
			<div class="container">
				<div class="row">
					<div class="col-lg-12">
						<div class="section-title">
							<h2>Empowering You to Verify Every Product with Confidence</h2>
							<img src="img/section-img.png" alt="#">
							<p>Ensure authenticity and protect yourself from counterfeits with our powerful verification tools and community support.</p>
						</div>
					</div>
				</div>
				<div class="row">
					
					<!-- Start Single Feature -->
					<div class="col-lg-4 col-12">
						<div class="single-features">
							<div class="signle-icon">
								<i class="icofont icofont-barcode"></i>
							</div>
							<h3>Multiple Verification Modes</h3>
							<p>Scan live QR codes, enter serial numbers, or upload QR images for flexible product verification.</p>
						</div>
					</div>
					<!-- End Single Feature -->
		
					<!-- Start Single Feature -->
					<div class="col-lg-4 col-12">
						<div class="single-features">
							<div class="signle-icon">
								<i class="icofont icofont-ui-file"></i>
							</div>
							<h3>Product Authenticity Reports</h3>
							<p>Instantly view trusted information about any product’s authenticity and traceability record.</p>
						</div>
					</div>
					<!-- End Single Feature -->
		
					<!-- Start Single Feature -->
					<div class="col-lg-4 col-12">
						<div class="single-features last">
							<div class="signle-icon">
								<i class="icofont icofont-warning-alt"></i>
							</div>
							<h3>Fake Product Reporting</h3>
							<p>Help others stay safe by reporting counterfeit items directly to our system for review and alerting.</p>
						</div>
					</div>
					<!-- End Single Feature -->
		
				</div>
			</div>
		</section>
		
		<!--/ End Feautes -->
		
		<?php
// Database connection
$servername = "localhost";
$username = "root";  // Change as per your configuration
$password = "";      // Change as per your configuration
$dbname = "authena";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Function to get total verification count
function getTotalVerifications($conn) {
    $sql = "SELECT COUNT(*) as total FROM verification_logs";
    $result = $conn->query($sql);
    if ($result && $result->num_rows > 0) {
        $row = $result->fetch_assoc();
        return $row['total'];
    }
    return 0;
}

// Function to get active brands count
function getActiveBrands($conn) {
    $sql = "SELECT COUNT(*) as total FROM brands WHERE status = 'active'";
    $result = $conn->query($sql);
    if ($result && $result->num_rows > 0) {
        $row = $result->fetch_assoc();
        return $row['total'];
    }
    return 0;
}

// Function to get active users count
function getActiveUsers($conn) {
    $sql = "SELECT COUNT(*) as total FROM users WHERE status = 'active'";
    $result = $conn->query($sql);
    if ($result && $result->num_rows > 0) {
        $row = $result->fetch_assoc();
        return $row['total'];
    }
    return 0;
}

// Function to calculate years of operation
function getYearsOfOperation($conn) {
    // Get the earliest record date from any relevant table
    $sql = "SELECT MIN(created_at) as earliest FROM users";
    $result = $conn->query($sql);
    if ($result && $result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $earliestDate = strtotime($row['earliest']);
        $currentDate = time();
        $yearsDiff = floor((($currentDate - $earliestDate) / (60 * 60 * 24)) / 365);
        return max(1, $yearsDiff); // Return at least 1 year
    }
    return 7; // Default value if no data is found
}

// Get data for fun facts
$totalVerifications = getTotalVerifications($conn);
$activeBrands = getActiveBrands($conn);
$activeUsers = getActiveUsers($conn);
$yearsOfTrust = getYearsOfOperation($conn);

// Close connection
$conn->close();
?>

<!-- Start Fun-facts --> 
<div id="fun-facts" class="fun-facts section overlay"> 
    <div class="container"> 
        <div class="row"> 
            <!-- Start Single Fun --> 
            <div class="col-lg-3 col-md-6 col-12"> 
                <div class="single-fun"> 
                    <i class="icofont icofont-bar-code"></i> 
                    <div class="content"> 
                        <span class="counter"><?php echo $totalVerifications; ?></span> 
                        <p>Products Verified</p> 
                    </div> 
                </div> 
            </div> 
            <!-- End Single Fun --> 
 
            <!-- Start Single Fun --> 
            <div class="col-lg-3 col-md-6 col-12"> 
                <div class="single-fun"> 
                    <i class="icofont icofont-shield"></i> 
                    <div class="content"> 
                        <span class="counter"><?php echo $activeBrands; ?></span> 
                        <p>Brands Secured</p> 
                    </div> 
                </div> 
            </div> 
            <!-- End Single Fun --> 
 
            <!-- Start Single Fun --> 
            <div class="col-lg-3 col-md-6 col-12"> 
                <div class="single-fun"> 
                    <i class="icofont icofont-users-alt-5"></i> 
                    <div class="content"> 
                        <span class="counter"><?php echo $activeUsers; ?></span> 
                        <p>Active Users</p> 
                    </div> 
                </div> 
            </div> 
            <!-- End Single Fun --> 
 
            <!-- Start Single Fun --> 
            <div class="col-lg-3 col-md-6 col-12"> 
                <div class="single-fun"> 
                    <i class="icofont icofont-certificate-alt-1"></i> 
                    <div class="content"> 
                        <span class="counter"><?php echo $yearsOfTrust; ?></span> 
                        <p>Years of Trust</p> 
                    </div> 
                </div> 
            </div> 
            <!-- End Single Fun --> 
        </div> 
    </div> 
</div>
		<!--/ End Fun-facts -->
		
		<!-- Start Why choose -->
		<section class="why-choose section">
			<div class="container">
				<div class="row">
					<div class="col-lg-12">
						<div class="section-title">
							<h2>Why Brands & Users Trust Authena</h2>
							<img src="img/section-img.png" alt="Section Separator">
							<p>We deliver reliable product verification and anti-counterfeit solutions to protect both businesses and consumers worldwide.</p>
						</div>
					</div>
				</div>
		
				<div class="row">
					<!-- Left Content -->
					<div class="col-lg-6 col-12">
						<div class="choose-left">
							<h3>What We Do</h3>
							<p>Authena offers advanced verification tools to confirm the authenticity of products using QR codes, serial numbers, and image recognition. Our platform empowers users to stay protected from fake goods.</p>
							<p>We also support brands by offering dashboard analytics and real-time data on scan activity and counterfeit alerts.</p>
							
							<div class="row">
								<div class="col-lg-6">
									<ul class="list">
										<li><i class="fa fa-caret-right"></i>QR & Serial Code Verification</li>
										<li><i class="fa fa-caret-right"></i>Image-Based Authenticity Checks</li>
										<li><i class="fa fa-caret-right"></i>Real-Time Scan Reports</li>
									</ul>
								</div>
								<div class="col-lg-6">
									<ul class="list">
										<li><i class="fa fa-caret-right"></i>Brand Protection Services</li>
										<li><i class="fa fa-caret-right"></i>User-Friendly Interface</li>
										<li><i class="fa fa-caret-right"></i>Global Product Coverage</li>
									</ul>
								</div>
							</div>
						</div>
					</div>
		
					<!-- Right Content (Video) -->
					<div class="col-lg-6 col-12">
						<div class="choose-right">
							<div class="video-image">
								<div class="promo-video">
									<div class="waves-block">
										<div class="waves wave-1"></div>
										<div class="waves wave-2"></div>
										<div class="waves wave-3"></div>
									</div>
								</div>
								<a href="https://www.youtube.com/watch?v=GggtUJyXWAI" class="video video-popup mfp-iframe"><i class="fa fa-play"></i></a>
							</div>
						</div>
					</div>
				</div>
			</div>
		</section>
		
		<!--/ End Why choose -->
		
		<!-- Start Call to action -->
	<!-- Start Call to Action -->
<section class="call-action overlay" data-stellar-background-ratio="0.5">
	<div class="container">
		<div class="row">
			<div class="col-lg-12 col-md-12 col-12">
				<div class="content">
					<h2>Need Help Verifying a Product? Call @ 1800-Authena</h2>
					<p>Unsure about a product's authenticity? Our experts are ready to assist you 24/7 with instant QR or serial number verification.</p>
					<div class="button">
						<a href="#contact" class="btn">Get Support</a>
						<a href="about.php" class="btn second">How It Works <i class="fa fa-long-arrow-right"></i></a>
					</div>
				</div>
			</div>
		</div>
	</div>
</section>
<!--/ End Call to Action -->

		<!--/ End Call to action -->
		
		
		<!--/ End portfolio -->
		
		<!-- Start service -->
		<section class="services section">
			<div class="container">
				<div class="row">
					<div class="col-lg-12">
						<div class="section-title">
							<h2>Explore Our Smart Product Authentication Services</h2>
							<img src="img/section-img.png" alt="section separator">
							<p>At Authena, we provide innovative tools to detect counterfeit products and protect consumers and brands alike.</p>
						</div>
					</div>
				</div>
				<div class="row">
					<div class="col-lg-4 col-md-6 col-12">
						<!-- Start Single Service -->
						<div class="single-service">
							<i class="icofont icofont-barcode"></i>
							<h4><a href="service-details.php">QR Code Scanning</a></h4>
							<p>Instantly verify a product's authenticity by scanning its QR code with our built-in verification system.</p>	
						</div>
					</div>
					<div class="col-lg-4 col-md-6 col-12">
						<div class="single-service">
							<i class="icofont icofont-file-alt"></i>
							<h4><a href="service-details.php">Serial Number Check</a></h4>
							<p>Enter the product's unique serial number to validate its originality and view full verification details.</p>	
						</div>
					</div>
					<div class="col-lg-4 col-md-6 col-12">
						<div class="single-service">
							<i class="icofont icofont-upload"></i>
							<h4><a href="service-details.php">Image Upload Verification</a></h4>
							<p>Upload a picture of a QR label or product to let our AI system analyze and confirm authenticity.</p>	
						</div>
					</div>
					<div class="col-lg-4 col-md-6 col-12">
						<div class="single-service">
							<i class="icofont icofont-dashboard-web"></i>
							<h4><a href="service-details.php">Admin Dashboard</a></h4>
							<p>Manage product entries, review scan logs, and track counterfeit attempts from one secure place.</p>	
						</div>
					</div>
					<div class="col-lg-4 col-md-6 col-12">
						<div class="single-service">
							<i class="icofont icofont-database"></i>
							<h4><a href="service-details.php">Secure Product Database</a></h4>
							<p>All verified product data is stored securely, ensuring accuracy, traceability, and data integrity.</p>	
						</div>
					</div>
					<div class="col-lg-4 col-md-6 col-12">
						<div class="single-service">
							<i class="icofont icofont-warning-alt"></i>
							<h4><a href="service-details.php">Fake Detection Alerts</a></h4>
							<p>Get real-time alerts and analytics if a fake or tampered product is detected during a scan.</p>	
						</div>
					</div>
				</div>
			</div>
		</section>
		
		<!--/ End service -->
		
		<!-- Pricing Table -->
		
		<!--/ End Pricing Table -->
		
		
		
		<!-- Start Blog Area -->
		<section class="blog section" id="blog">
			<div class="container">
				<div class="row">
					<div class="col-lg-12">
						<div class="section-title">
							<h2>Latest Updates & Insights from Authena</h2>
							<img src="img/section-img.png" alt="section divider">
							<p>Stay informed with the latest updates on fake product detection, feature rollouts, and tips to protect your brand.</p>
						</div>
					</div>
				</div>
				<div class="row">
					<!-- Single Blog -->
					<div class="col-lg-4 col-md-6 col-12">
						<div class="single-news">
						
							<div class="news-body">
								<div class="news-content">
									<div class="date">18 Apr, 2025</div>
									<h2><a href="blog-single.php">Authena Helps Bust Major Fake Cosmetics Ring</a></h2>
									<p class="text">Authorities intercepted counterfeit products using our image scan feature. Learn how brands are leveraging Authena to trace fakes.</p>
								</div>
							</div>
						</div>
					</div>
					<!-- Single Blog -->
					<div class="col-lg-4 col-md-6 col-12">
						<div class="single-news">
							
							<div class="news-body">
								<div class="news-content">
									<div class="date">12 Mar, 2025</div>
									<h2><a href="blog-single.php">New Feature: Bulk QR Upload for Businesses</a></h2>
									<p class="text">We just launched a powerful bulk-upload tool that lets brands validate entire batches of products with just one click.</p>
								</div>
							</div>
						</div>
					</div>
					<!-- Single Blog -->
					<div class="col-lg-4 col-md-6 col-12">
						<div class="single-news">
						
							<div class="news-body">
								<div class="news-content">
									<div class="date">27 Feb, 2025</div>
									<h2><a href="blog-single.php">5 Tips to Spot Fake Products Using QR Codes</a></h2>
									<p class="text">Not all codes are created equal. Here are 5 simple techniques to instantly detect tampered or duplicate QR codes.</p>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
		</section>
		
		<!-- End Blog Area -->
		
		<!-- Start clients -->
		<div class="clients overlay">
			<div class="container">
				<div class="row">
					<div class="col-lg-12 col-md-12 col-12">
						<div class="owl-carousel clients-slider">
							<div class="single-clients">
								<img src="img/client1.png" alt="#">
							</div>
							<div class="single-clients">
								<img src="img/client2.png" alt="#">
							</div>
							<div class="single-clients">
								<img src="img/client3.png" alt="#">
							</div>
							<div class="single-clients">
								<img src="img/client4.png" alt="#">
							</div>
							<div class="single-clients">
								<img src="img/client5.png" alt="#">
							</div>
							<div class="single-clients">
								<img src="img/client1.png" alt="#">
							</div>
							<div class="single-clients">
								<img src="img/client2.png" alt="#">
							</div>
							<div class="single-clients">
								<img src="img/client3.png" alt="#">
							</div>
							<div class="single-clients">
								<img src="img/client4.png" alt="#">
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
		<!--/Ens clients -->
		
		<!-- Start Appointment -->
		<section class="appointment section" id="contact">
			<div class="container">
				<div class="row">
					<div class="col-lg-12">
						<div class="section-title">
							<h2>Need Help with a Product? Reach Out to Authena</h2>
							<img src="img/section-img.png" alt="section divider">
							<p>Submit your query to report a suspicious product, get in touch for business integration, or request verification support.</p>
						</div>
					</div>
				</div>
				<div class="row">
					<!-- Contact Form -->
					<div class="col-lg-6 col-md-12 col-12">
						<form class="form" action="#">
							<div class="row">
								<div class="col-lg-6 col-md-6 col-12">
									<div class="form-group">
										<input name="name" type="text" placeholder="Your Name">
									</div>
								</div>
								<div class="col-lg-6 col-md-6 col-12">
									<div class="form-group">
										<input name="email" type="email" placeholder="Your Email">
									</div>
								</div>
								<div class="col-lg-6 col-md-6 col-12">
									<div class="form-group">
										<input name="phone" type="text" placeholder="Contact Number">
									</div>
								</div>
								<div class="col-lg-6 col-md-6 col-12">
									<div class="form-group">
										<select class="form-control wide">
											<option value="">Select Query Type</option>
											<option value="verify-product">Product Verification</option>
											<option value="report-fake">Report Fake Product</option>
											<option value="business-inquiry">Business / Partnership</option>
											<option value="technical-support">Technical Support</option>
										</select>
									</div>
								</div>
						
								<div class="col-lg-12 col-md-12 col-12">
									<div class="form-group">
										<input name="product-code" type="text" placeholder="Product Serial / QR Code (Optional)">
									</div>
								</div>
								<div class="col-lg-12 col-md-12 col-12">
									<div class="form-group">
										<textarea name="message" placeholder="Describe your concern or query..."></textarea>
									</div>
								</div>
								<div class="col-lg-5 col-md-4 col-12">
									<div class="form-group">
										<div class="button">
											<button type="submit" class="btn" onclick='window.alert("Thank You !! Our team will contact")'>Submit Request</button>
										</div>
									</div>
								</div>
								<div class="col-lg-7 col-md-8 col-12">
									<p>( Our team will contact you via email or phone within 24 hours )</p>
								</div>
							</div>
						</form>
					</div>
		
					<!-- Contact Illustration -->
					<div class="col-lg-6 col-md-12">
						<div class="appointment-image">
							<img src="img/contact-img3.png" alt="Contact VerifyTag">
						</div>
					</div>
				</div>
			</div>
		</section>
		
		<!-- End Appointment -->
		
		<!-- Start Newsletter Area -->
		<section class="newsletter section" id="newsletter">
			<div class="container">
				<div class="row align-items-center">
					<!-- Left Content -->
					<div class="col-lg-6 col-12">
						<div class="subscribe-text">
							<h6>Stay Ahead of Counterfeits</h6>
							<p>Subscribe to receive updates on new product verification features, latest scam alerts, and brand authentication news — straight to your inbox.</p>
						</div>
					</div>
		
					<!-- Right Form -->
					<div class="col-lg-6 col-12">
						<div class="subscribe-form">
							<form action="#" method="POST" class="newsletter-inner">
								<input name="email" type="email" class="common-input" placeholder="Enter your email address"
									onfocus="this.placeholder=''" onblur="this.placeholder='Enter your email address'" required>
								<button class="btn" type="submit"onclick='window.alert("Thank You !! Updates will via email ")'>Subscribe Now</button>
							</form>
						</div>
					</div>
				</div>
			</div>
		</section>
		
		<!-- /End Newsletter Area -->
		
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