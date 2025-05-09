<?php
// Start session if not already started
session_start();
?>
<!doctype html>
<html class="no-js" lang="zxx">
    <head>
        <!-- Meta Tags -->
		<meta charset="utf-8">
		<meta http-equiv="X-UA-Compatible" content="IE=edge">
		<meta name="keywords" content="Product authentication, anti-counterfeit, fake product detection, Authena brands">
		<meta name="description" content="Trusted brands using Authena's product authentication platform">
		<meta name='copyright' content=''>
		<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
		
		<!-- Title -->
        <title>Trusted Brands - Authena Product Authentication Platform</title>
		
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
            .brand-box {
                background: #fff;
                padding: 30px;
                border-radius: 8px;
                box-shadow: 0 0 20px rgba(0,0,0,0.1);
                margin-bottom: 30px;
                transition: all 0.3s ease;
                text-align: center;
                height: 100%;
            }
            
            .brand-box:hover {
                transform: translateY(-5px);
                box-shadow: 0 10px 30px rgba(0,0,0,0.15);
            }
            
            .brand-logo {
                background: #b3b3cc;
                height: 120px;
                width: auto;
                margin-bottom: 20px;
                object-fit: contain;
            }
            
            .brand-title {
                color: #1A76D1;
                margin-bottom: 15px;
                font-weight: 600;
            }
            
            .brand-description {
                color: #777;
                margin-bottom: 20px;
            }
            
            .brand-info {
                background: #f9f9f9;
                padding: 15px;
                border-radius: 5px;
                font-size: 14px;
            }
            
            .verified-badge {
                background: #00B894;
                color: #fff;
                padding: 5px 15px;
                border-radius: 30px;
                display: inline-block;
                margin-top: 15px;
                font-size: 13px;
                font-weight: 500;
            }
            
            .brands-section {
                padding: 80px 0;
            }
            
            .section-title {
                margin-bottom: 50px;
                text-align: center;
            }
            
            .section-title h2 {
                color: #1A76D1;
                font-weight: 700;
                margin-bottom: 20px;
            }
            
            .section-intro {
                max-width: 800px;
                margin: 0 auto 50px;
                text-align: center;
                color: #555;
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
								<li><a href="#">About</a></li>
								<li><a href="brands.php" class="active">Brands</a></li>
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
		
											<li><a href="#">Verify Product <i class="icofont-rounded-down"></i></a>
												<ul class="dropdown">
													<li><a href="serial.php">Enter Serial Number</a></li>
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
													<li><a href="my-products.php">My Products</a></li>
													<li><a href="settings.php">Settings</a></li>
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
							<h2>Trusted Brands</h2>
							<ul class="bread-list">
								<li><a href="index.php">Home</a></li>
								<li><i class="icofont-simple-right"></i></li>
								<li class="active">Brands</li>
							</ul>
						</div>
					</div>
				</div>
			</div>
		</div>
		<!-- End Breadcrumbs -->
	
		<!-- Brands Section -->
		<section class="brands-section">
			<div class="container">
				<div class="row">
					<div class="col-12">
						<div class="section-title">
							<h2>Our Trusted Brand Partners</h2>
						</div>
					</div>
				</div>
				
				<div class="section-intro">
					<p>Authena works with leading brands across healthcare, pharmaceuticals, and medical devices to protect consumers from counterfeit products. Each of these trusted partners has implemented our advanced authentication technology to ensure product integrity and safety.</p>
				</div>
				
				<div class="row">
					<!-- Central Hospital -->
					<div class="col-lg-3 col-md-6 col-12">
						<div class="brand-box">
							<img src="img/client1.png" alt="Central Hospital" class="brand-logo">
							<h3 class="brand-title">Central Hospital</h3>
							<p class="brand-description">Leading healthcare provider ensuring authentic medical supplies and equipment for patient safety.</p>
							<div class="brand-info">
								<p>Products protected: 235</p>
								<p>Authentication scans: 45,620+</p>
							</div>
							<span class="verified-badge"><i class="fa fa-check-circle"></i> Verified Partner</span>
						</div>
					</div>
					
					<!-- Panacea Clinic -->
					<div class="col-lg-3 col-md-6 col-12">
						<div class="brand-box">
							<img src="img/client2.png" alt="Panacea Clinic" class="brand-logo">
							<h3 class="brand-title">Panacea Clinic</h3>
							<p class="brand-description">Specialized medical facility with a commitment to using only verified authentic products.</p>
							<div class="brand-info">
								<p>Products protected: 178</p>
								<p>Authentication scans: 32,845+</p>
							</div>
							<span class="verified-badge"><i class="fa fa-check-circle"></i> Verified Partner</span>
						</div>
					</div>
					
					<!-- Cardiac Science -->
					<div class="col-lg-3 col-md-6 col-12">
						<div class="brand-box">
							<img src="img/client3.png" alt="Cardiac Science" class="brand-logo">
							<h3 class="brand-title">Cardiac Science</h3>
							<p class="brand-description">Manufacturer of life-saving cardiac devices with advanced anti-counterfeit protection.</p>
							<div class="brand-info">
								<p>Products protected: 123</p>
								<p>Authentication scans: 28,765+</p>
							</div>
							<span class="verified-badge"><i class="fa fa-check-circle"></i> Verified Partner</span>
						</div>
					</div>
					
					<!-- Pharmacy -->
					<div class="col-lg-3 col-md-6 col-12">
						<div class="brand-box">
							<img src="img/client4.png" alt="Pharmacy" class="brand-logo">
							<h3 class="brand-title">Pharmacy</h3>
							<p class="brand-description">Trusted pharmaceutical retailer with a focus on providing authentic medications and health products.</p>
							<div class="brand-info">
								<p>Products protected: 412</p>
								<p>Authentication scans: 67,920+</p>
							</div>
							<span class="verified-badge"><i class="fa fa-check-circle"></i> Verified Partner</span>
						</div>
					</div>
				</div>
				
				<!-- Benefits Section -->
				<div class="row mt-5 pt-5">
					<div class="col-12">
						<div class="section-title">
							<h2>Benefits of Brand Partnership</h2>
						</div>
					</div>
					
					<div class="col-lg-4 col-md-6 col-12">
						<div class="single-service">
							<i class="icofont-shield"></i>
							<h4>Enhanced Product Protection</h4>
							<p>Our authentication technology helps brands prevent counterfeiting and protect their reputation in the market.</p>
						</div>
					</div>
					
					<div class="col-lg-4 col-md-6 col-12">
						<div class="single-service">
							<i class="icofont-chart-flow-1"></i>
							<h4>Supply Chain Visibility</h4>
							<p>Track products from manufacturing to end-user with complete transparency and real-time monitoring.</p>
						</div>
					</div>
					
					<div class="col-lg-4 col-md-6 col-12">
						<div class="single-service">
							<i class="icofont-user-suited"></i>
							<h4>Consumer Trust</h4>
							<p>Build stronger relationships with customers by providing easy verification of authentic products.</p>
						</div>
					</div>
					
					<div class="col-lg-4 col-md-6 col-12">
						<div class="single-service">
							<i class="icofont-chart-bar-graph"></i>
							<h4>Market Insights</h4>
							<p>Gain valuable data on product usage, verification patterns, and potential market risks.</p>
						</div>
					</div>
					
					<div class="col-lg-4 col-md-6 col-12">
						<div class="single-service">
							<i class="icofont-notification"></i>
							<h4>Counterfeit Alerts</h4>
							<p>Receive immediate notifications about potential counterfeit attempts or unauthorized distribution.</p>
						</div>
					</div>
					
					<div class="col-lg-4 col-md-6 col-12">
						<div class="single-service">
							<i class="icofont-law-document"></i>
							<h4>Regulatory Compliance</h4>
							<p>Meet industry standards and regulations for product authentication and tracking requirements.</p>
						</div>
					</div>
				</div>
				
				<!-- Call to Action -->
				<div class="row mt-5">
					<div class="col-12 text-center">
						<div class="appointment">
							<h3>Want to protect your brand from counterfeits?</h3>
							<p>Join our growing network of trusted partners and implement Authena's advanced authentication technology.</p>
							<a href="#" class="btn">Become a Partner</a>
						</div>
					</div>
				</div>
			</div>
		</section>
		<!-- End Brands Section -->
		
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
											<li><a href="#"><i class="fa fa-caret-right"></i>Home</a></li>
											<li><a href="#"><i class="fa fa-caret-right"></i>Verify Product</a></li>
											<li><a href="#"><i class="fa fa-caret-right"></i>Scan QR Code</a></li>
											<li><a href="#"><i class="fa fa-caret-right"></i>Submit Serial</a></li>
											<li><a href="#"><i class="fa fa-caret-right"></i>Upload Image</a></li>
										</ul>
									</div>
									<div class="col-lg-6 col-md-6 col-12">
										<ul>
											<li><a href="#"><i class="fa fa-caret-right"></i>How It Works</a></li>
											<li><a href="#"><i class="fa fa-caret-right"></i>Support</a></li>
											<li><a href="#"><i class="fa fa-caret-right"></i>FAQ</a></li>
											<li><a href="#"><i class="fa fa-caret-right"></i>Report Scam</a></li>
											<li><a href="#"><i class="fa fa-caret-right"></i>Contact Us</a></li>
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
									<button class="button" type="submit"><i class="icofont-paper-plane"></i></button>
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
								<p>© 2025 Authena. All rights reserved. | Designed with ❤️ to fight fakes.</p>
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