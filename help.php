<!doctype html>
<html class="no-js" lang="en">
    <head>
        <!-- Meta Tags -->
		<meta charset="utf-8">
		<meta http-equiv="X-UA-Compatible" content="IE=edge">
		<meta name="keywords" content="Help Center, Support, FAQ, Authena, Product Authentication">
		<meta name="description" content="Authena Help Center - Get assistance with product verification, authentication, and reporting counterfeits">
		<meta name='copyright' content=''>
		<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
		
		<!-- Title -->
        <title>Authena - Help Center</title>
		
		<!-- Favicon -->
        <link rel="icon" href="img/favicon.png">
		
		<!-- Google Fonts -->
		<link href="https://fonts.googleapis.com/css?family=Poppins:200i,300,300i,400,400i,500,500i,600,600i,700,700i,800,800i,900,900i&display=swap" rel="stylesheet">
        <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">

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
        
        <!-- Custom CSS for modern design -->
        <style>
            :root {
                --primary-color: #1a77e1;
                --secondary-color: #1a77e1;
                --accent-color: #1a77e1;
                --light-color: #f7fafc;
                --dark-color: #2d3748;
                --success-color: #38b2ac;
                --warning-color: #ecc94b;
                --danger-color: #e53e3e;
                --border-radius: 8px;
                --box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
            }
            
            body {
                font-family: 'Inter', 'Poppins', sans-serif;
                color: #4a5568;
                background-color: #f8fafc;
            }
            
            h1, h2, h3, h4, h5, h6 {
                font-weight: 600;
            }
            
            /* Search Area Redesign */
            .help-search {
                background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
                padding: 3rem;
                border-radius: var(--border-radius);
                margin-bottom: 3rem;
                text-align: center;
                box-shadow: var(--box-shadow);
            }
            
            .help-search h3 {
                color: white;
                font-size: 2rem;
                margin-bottom: 1.5rem;
                font-weight: 700;
            }
            
            .help-search form {
                display: flex;
                max-width: 600px;
                margin: 0 auto;
                box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
                border-radius: 50px;
                overflow: hidden;
            }
            
            .help-search input {
                flex: 1;
                border: none;
                padding: 1rem 1.5rem;
                font-size: 1rem;
                border-radius: 50px 0 0 50px;
            }
            
            .help-search .btn {
                background: var(--dark-color);
                color: white;
                border: none;
                padding: 1rem 1.5rem;
                border-radius: 0 50px 50px 0;
                transition: all 0.3s ease;
            }
            
            .help-search .btn:hover {
                background: #1a202c;
            }
            
            /* Help Categories Redesign */
            .help-categories {
                margin-bottom: 3rem;
            }
            
            .help-category {
                background: white;
                border-radius: var(--border-radius);
                padding: 2rem;
                height: 100%;
                transition: all 0.3s ease;
                box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
                position: relative;
                overflow: hidden;
                z-index: 1;
                border-bottom: 3px solid var(--primary-color);
            }
            
            .help-category::before {
                content: '';
                position: absolute;
                top: 0;
                left: 0;
                width: 100%;
                height: 0;
                background: linear-gradient(180deg, rgba(78, 84, 200, 0.05) 0%, rgba(78, 84, 200, 0) 100%);
                transition: all 0.3s ease;
                z-index: -1;
            }
            
            .help-category:hover {
                transform: translateY(-5px);
                box-shadow: var(--box-shadow);
            }
            
            .help-category:hover::before {
                height: 100%;
            }
            
            .help-category .icon {
                background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
                width: 70px;
                height: 70px;
                display: flex;
                align-items: center;
                justify-content: center;
                border-radius: 50%;
                margin-bottom: 1.5rem;
                box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            }
            
            .help-category .icon i {
                font-size: 2rem;
                color: white;
            }
            
            .help-category h4 {
                font-size: 1.25rem;
                margin-bottom: 1rem;
                color: var(--dark-color);
            }
            
            .help-category p {
                margin-bottom: 1.5rem;
                color: #718096;
            }
            
            .cat-link {
                color: var(--primary-color);
                font-weight: 500;
                text-decoration: none;
                display: inline-block;
                transition: all 0.3s ease;
            }
            
            .cat-link:hover {
                color: var(--secondary-color);
                transform: translateX(5px);
            }
            
            .cat-link i {
                margin-left: 5px;
                transition: all 0.3s ease;
            }
            
            .cat-link:hover i {
                margin-left: 10px;
            }
            
            /* Help Section Redesign */
            .help-section {
                padding: 3rem 0;
                border-top: 1px solid #e2e8f0;
            }
            
            .section-title {
                text-align: center;
                margin-bottom: 3rem;
            }
            
            .section-title h2 {
                font-size: 2rem;
                color: var(--dark-color);
                position: relative;
                padding-bottom: 15px;
                margin-bottom: 15px;
            }
            
            .section-title h2::after {
                content: '';
                position: absolute;
                bottom: 0;
                left: 50%;
                transform: translateX(-50%);
                width: 80px;
                height: 3px;
                background: linear-gradient(to right, var(--primary-color), var(--secondary-color));
            }
            
            .section-title p {
                color: #718096;
                font-size: 1.1rem;
            }
            
            .help-article {
                background: white;
                border-radius: var(--border-radius);
                box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
                padding: 1.5rem;
                height: 100%;
                transition: all 0.3s ease;
                border-left: 4px solid var(--primary-color);
                margin-bottom: 2rem;
            }
            
            .help-article:hover {
                box-shadow: var(--box-shadow);
                transform: translateY(-5px);
            }
            
            .help-article h4 {
                font-size: 1.1rem;
                margin-bottom: 1rem;
                display: flex;
                align-items: center;
                color: var(--dark-color);
            }
            
            .help-article h4 i {
                color: var(--primary-color);
                margin-right: 10px;
                font-size: 1.2rem;
            }
            
            .help-article p {
                color: #718096;
                margin-bottom: 1.5rem;
            }
            
            .help-article .btn {
                background: var(--light-color);
                color: var(--primary-color);
                border: 1px solid #e2e8f0;
                border-radius: 50px;
                padding: 0.5rem 1.5rem;
                font-weight: 500;
                transition: all 0.3s ease;
            }
            
            .help-article .btn:hover {
                background: var(--primary-color);
                color: white;
                border-color: var(--primary-color);
            }
            
            /* Contact Support Redesign */
            .contact-support {
                background: white;
                border-radius: var(--border-radius);
                padding: 3rem;
                margin-top: 3rem;
                text-align: center;
                box-shadow: var(--box-shadow);
                position: relative;
                overflow: hidden;
            }
            
            .contact-support::before {
                content: '';
                position: absolute;
                top: 0;
                left: 0;
                width: 100%;
                height: 5px;
                background: linear-gradient(to right, var(--primary-color), var(--secondary-color));
            }
            
            .contact-support h3 {
                font-size: 1.8rem;
                margin-bottom: 1rem;
                color: var(--dark-color);
            }
            
            .contact-support p {
                color: #718096;
                font-size: 1.1rem;
                margin-bottom: 2rem;
            }
            
            .support-option {
                background: #f8fafc;
                border-radius: var(--border-radius);
                padding: 2rem;
                height: 100%;
                transition: all 0.3s ease;
                box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
            }
            
            .support-option:hover {
                transform: translateY(-5px);
                box-shadow: var(--box-shadow);
            }
            
            .support-option i {
                font-size: 2.5rem;
                color: var(--primary-color);
                margin-bottom: 1.5rem;
                display: inline-block;
            }
            
            .support-option h4 {
                font-size: 1.3rem;
                margin-bottom: 1rem;
                color: var(--dark-color);
            }
            
            .support-option p {
                color: #718096;
                margin-bottom: 1.5rem;
            }
            
            .support-option .btn {
                background: var(--primary-color);
                color: white;
                border: none;
                border-radius: 50px;
                padding: 0.75rem 1.5rem;
                font-weight: 500;
                transition: all 0.3s ease;
            }
            
            .support-option .btn:hover {
                background: var(--secondary-color);
                transform: scale(1.05);
            }
            
            /* Animations */
            @keyframes fadeInUp {
                from {
                    opacity: 0;
                    transform: translateY(20px);
                }
                to {
                    opacity: 1;
                    transform: translateY(0);
                }
            }
            
            .animated {
                animation-duration: 0.6s;
                animation-fill-mode: both;
            }
            
            .fadeInUp {
                animation-name: fadeInUp;
            }
            
            /* Custom Buttons */
            .btn-primary {
                background: var(--primary-color);
                color: white;
                border-radius: 50px;
                padding: 0.75rem 1.5rem;
                font-weight: 500;
                transition: all 0.3s ease;
                border: none;
            }
            
            .btn-primary:hover {
                background: var(--secondary-color);
                transform: translateY(-2px);
                box-shadow: 0 4px 6px rgba(78, 84, 200, 0.3);
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
		
											<li class="active"><a href="#">Resources <i class="icofont-rounded-down"></i></a>
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
										<a href="login.php" class="btn">Login</a>
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
							<h2>Help Center</h2>
							<ul class="bread-list">
								<li><a href="index.php">Home</a></li>
								<li><i class="icofont-simple-right"></i></li>
								<li class="active">Help Center</li>
							</ul>
						</div>
					</div>
				</div>
			</div>
		</div>
		<!-- End Breadcrumbs -->
	
		<!-- Start Help Center Area -->
        <section class="help-center section">
            <div class="container">
                <!-- Search Area -->
                <div class="row">
                    <div class="col-lg-10 offset-lg-1 col-md-12 col-12">
                        <div class="help-search animated fadeInUp">
                            <h3>How can we help you today?</h3>
                            <form action="#" method="get">
                                <input type="text" placeholder="Search for answers or topics..." required>
                                <button type="submit" class="btn"><i class="fa fa-search"></i> Search</button>
                            </form>
                        </div>
                    </div>
                </div>
                
                <!-- Help Categories -->
                <div class="row help-categories">
                    <div class="col-lg-3 col-md-6 col-12 animated fadeInUp" style="animation-delay: 0.1s;">
                        <div class="help-category">
                            <div class="icon">
                                <i class="icofont-verification-check"></i>
                            </div>
                            <h4>Product Verification</h4>
                            <p>Learn how to verify if your product is authentic using our tools.</p>
                            <a href="#verification" class="cat-link">View Articles <i class="fa fa-arrow-right"></i></a>
                        </div>
                    </div>
                    
                    <div class="col-lg-3 col-md-6 col-12 animated fadeInUp" style="animation-delay: 0.2s;">
                        <div class="help-category">
                            <div class="icon">
                                <i class="icofont-warning-alt"></i>
                            </div>
                            <h4>Report Counterfeits</h4>
                            <p>How to report fake products and protect others from scams.</p>
                            <a href="#reporting" class="cat-link">View Articles <i class="fa fa-arrow-right"></i></a>
                        </div>
                    </div>
                    
                    <div class="col-lg-3 col-md-6 col-12 animated fadeInUp" style="animation-delay: 0.3s;">
                        <div class="help-category">
                            <div class="icon">
                                <i class="icofont-user"></i>
                            </div>
                            <h4>Account Management</h4>
                            <p>Setting up and managing your Authena user account.</p>
                            <a href="#account" class="cat-link">View Articles <i class="fa fa-arrow-right"></i></a>
                        </div>
                    </div>
                    
                    <div class="col-lg-3 col-md-6 col-12 animated fadeInUp" style="animation-delay: 0.4s;">
                        <div class="help-category">
                            <div class="icon">
                                <i class="icofont-brand-qr-code"></i>
                            </div>
                            <h4>QR & Serial Numbers</h4>
                            <p>Understanding how our authentication technologies work.</p>
                            <a href="#technology" class="cat-link">View Articles <i class="fa fa-arrow-right"></i></a>
                        </div>
                    </div>
                </div>
                
                <!-- Product Verification Articles -->
                <div id="verification" class="row help-section">
                    <div class="col-12">
                        <div class="section-title">
                            <h2>Product Verification Help</h2>
                            <p>Learn how to verify if the products you purchase are authentic</p>
                        </div>
                    </div>
                    
                    <div class="col-lg-6 col-md-6 col-12">
                        <div class="help-article">
                            <h4><i class="fa fa-file-text"></i> How to scan a QR code for verification</h4>
                            <p>A step-by-step guide to scanning product QR codes using our mobile app or website.</p>
                            <a href="article-qr-scan.php" class="btn">Read More</a>
                        </div>
                    </div>
                    
                    <div class="col-lg-6 col-md-6 col-12">
                        <div class="help-article">
                            <h4><i class="fa fa-file-text"></i> Verifying products with serial numbers</h4>
                            <p>How to find and enter product serial numbers to check authenticity.</p>
                            <a href="article-serial-verification.php" class="btn">Read More</a>
                        </div>
                    </div>
                    
                    <div class="col-lg-6 col-md-6 col-12">
                        <div class="help-article">
                            <h4><i class="fa fa-file-text"></i> Understanding verification results</h4>
                            <p>Learn how to interpret product verification results and what each status means.</p>
                            <a href="article-results-explained.php" class="btn">Read More</a>
                        </div>
                    </div>
                    
                    <div class="col-lg-6 col-md-6 col-12">
                        <div class="help-article">
                            <h4><i class="fa fa-file-text"></i> Troubleshooting verification problems</h4>
                            <p>Common issues with product verification and how to resolve them.</p>
                            <a href="article-verification-troubleshooting.php" class="btn">Read More</a>
                        </div>
                    </div>
                </div>
                
                <!-- Reporting Counterfeits Articles -->
                <div id="reporting" class="row help-section">
                    <div class="col-12">
                        <div class="section-title">
                            <h2>Reporting Fake Products</h2>
                            <p>Help us combat counterfeit goods by reporting suspicious products</p>
                        </div>
                    </div>
                    
                    <div class="col-lg-6 col-md-6 col-12">
                        <div class="help-article">
                            <h4><i class="fa fa-file-text"></i> How to report a counterfeit product</h4>
                            <p>Steps to submit a counterfeit product report through our platform.</p>
                            <a href="article-report-fake.php" class="btn">Read More</a>
                        </div>
                    </div>
                    
                    <div class="col-lg-6 col-md-6 col-12">
                        <div class="help-article">
                            <h4><i class="fa fa-file-text"></i> What happens after submitting a report</h4>
                            <p>Understanding our investigation process after you submit a counterfeit report.</p>
                            <a href="article-report-process.php" class="btn">Read More</a>
                        </div>
                    </div>
                    
                    <div class="col-lg-6 col-md-6 col-12">
                        <div class="help-article">
                            <h4><i class="fa fa-file-text"></i> Spotting fake products: Warning signs</h4>
                            <p>Common indicators that might help you identify counterfeit products.</p>
                            <a href="article-spotting-fakes.php" class="btn">Read More</a>
                        </div>
                    </div>
                    
                    <div class="col-lg-6 col-md-6 col-12">
                        <div class="help-article">
                            <h4><i class="fa fa-file-text"></i> Legal actions against counterfeiters</h4>
                            <p>How Authena works with brands and authorities to take action against counterfeiting.</p>
                            <a href="article-legal-actions.php" class="btn">Read More</a>
                        </div>
                    </div>
                </div>
                
                <!-- Account Management Articles -->
                <div id="account" class="row help-section">
                    <div class="col-12">
                        <div class="section-title">
                            <h2>Account Management</h2>
                            <p>Setting up and managing your Authena user account</p>
                        </div>
                    </div>
                    
                    <div class="col-lg-6 col-md-6 col-12">
                        <div class="help-article">
                            <h4><i class="fa fa-file-text"></i> Creating an Authena account</h4>
                            <p>How to sign up and set up your account with Authena.</p>
                            <a href="article-create-account.php" class="btn">Read More</a>
                        </div>
                    </div>
                    
                    <div class="col-lg-6 col-md-6 col-12">
                        <div class="help-article">
                            <h4><i class="fa fa-file-text"></i> Managing your verification history</h4>
                            <p>How to access and manage your product verification history.</p>
                            <a href="article-verification-history.php" class="btn">Read More</a>
                        </div>
                    </div>
                    
                    <div class="col-lg-6 col-md-6 col-12">
                        <div class="help-article">
                            <h4><i class="fa fa-file-text"></i> Account security and privacy</h4>
                            <p>Best practices for keeping your Authena account secure.</p>
                            <a href="article-account-security.php" class="btn">Read More</a>
                        </div>
                    </div>
                    
                    <div class="col-lg-6 col-md-6 col-12">
                        <div class="help-article">
                            <h4><i class="fa fa-file-text"></i> Brand representative accounts</h4>
                            <p>Information for brand owners and representatives using our platform.</p>
                            <a href="article-brand-accounts.php" class="btn">Read More</a>
                        </div>
                    </div>
                </div>
                
                <!-- QR & Technology Articles -->
                <div id="technology" class="row help-section">
                    <div class="col-12">
                        <div class="section-title">
                            <h2>Technology Information</h2>
                            <p>Understanding our authentication technologies</p>
                        </div>
                    </div>
                    
                    <div class="col-lg-6 col-md-6 col-12">
                        <div class="help-article">
                            <h4><i class="fa fa-file-text"></i> How Authena QR codes work</h4>
                            <p>Technical explanation of our secure QR code authentication technology.</p>
                            <a href="article-qr-tech.php" class="btn">Read More</a>
                        </div>
                    </div>
                    
                    <div class="col-lg-6 col-md-6 col-12">
                        <div class="help-article">
                            <h4><i class="fa fa-file-text"></i> Serial number authentication</h4>
                            <p>Understanding how our serial number verification system works.</p>
                            <a href="article-serial-tech.php" class="btn">Read More</a>
                        </div>
                    </div>
                    
                    <div class="col-lg-6 col-md-6 col-12">
                        <div class="help-article">
                            <h4><i class="fa fa-file-text"></i> Blockchain for product authentication</h4>
                            <p>How we use blockchain technology to ensure product authenticity.</p>
                            <a href="article-blockchain.php" class="btn">Read More</a>
                        </div>
                    </div>
                    
                    <div class="col-lg-6 col-md-6 col-12">
                        <div class="help-article">
                            <h4><i class="fa fa-file-text"></i> Supported product categories</h4>
                            <p>List of product categories and brands supported by our platform.</p>
                            <a href="article-supported-products.php" class="btn">Read More</a>
                        </div>
                    </div>
                </div>
                
                <!-- Contact Support Section -->
                <div class="row">
                    <div class="col-12">
                        <div class="contact-support animated fadeInUp">
                            <h3>Still need help?</h3>
                            <p>Our support team is available to assist you with any questions or concerns.</p>
                            <div class="row">
                                <div class="col-lg-4 col-md-4 col-12 animated fadeInUp" style="animation-delay: 0.1s;">
                                    <div class="support-option">
                                        <i class="icofont-envelope"></i>
                                        <h4>Email Support</h4>
                                        <p>Send us an email and we'll respond within 24 hours.</p>
                                        <a href="mailto:support@authena.com" class="btn">Email Us</a>
                                    </div>
                                </div>
                                
                                <div class="col-lg-4 col-md-4 col-12 animated fadeInUp" style="animation-delay: 0.2s;">
                                    <div class="support-option">
                                        <i class="icofont-live-support"></i>
                                        <h4>Live Chat</h4>
                                        <p>Chat with our support team during business hours.</p>
                                        <a href="#" class="btn" id="start-chat">Start Chat</a>
                                    </div>
                                </div>
                                
                                <div class="col-lg-4 col-md-4 col-12 animated fadeInUp" style="animation-delay: 0.3s;">
                                    <div class="support-option">
                                        <i class="icofont-ticket"></i>
                                        <h4>Submit Ticket</h4>
                                        <p>Submit a support ticket for complex issues.</p>
                                        <a href="submit-ticket.php" class="btn">Create Ticket</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
        <!-- End Help Center Area -->
		
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
        
        <!-- Help Center Specific JS -->
        <script>
            // Smooth scroll to help sections
            $(document).ready(function(){
                // Initialize animations on scroll
                function animateElements() {
                    $('.animated').each(function(){
                        let elementPosition = $(this).offset().top;
                        let topOfWindow = $(window).scrollTop();
                        let windowHeight = $(window).height();
                        let animationDelay = $(this).css('animation-delay');
                        
                        if(elementPosition < topOfWindow + windowHeight - 50) {
                            if(!$(this).hasClass('fadeInUp')) {
                                $(this).addClass('fadeInUp');
                            }
                        }
                    });
                }
                
                // Run animation on page load
                animateElements();
                
                // Run animation on scroll
                $(window).scroll(function() {
                    animateElements();
                });
                
                // Smooth scroll to sections
                $('.cat-link').on('click', function(event) {
                    if (this.hash !== "") {
                        event.preventDefault();
                        var hash = this.hash;
                        $('html, body').animate({
                            scrollTop: $(hash).offset().top - 100
                        }, 800, 'easeInOutExpo');
                    }
                });
                
                // Live chat functionality
                $('#start-chat').on('click', function(e){
                    e.preventDefault();
                    // Initialize chat widget
                    alert('Live chat support is connecting...');
                    // In a real implementation, this would open a chat widget
                });
                
                // Add hover effects to articles
                $('.help-article').hover(
                    function() {
                        $(this).find('.btn').addClass('btn-hover');
                    },
                    function() {
                        $(this).find('.btn').removeClass('btn-hover');
                    }
                );
                
                // Search box focus effect
                $('.help-search input').focus(function(){
                    $(this).parent().addClass('focused');
                }).blur(function(){
                    $(this).parent().removeClass('focused');
                });
            });
        </script>
        <!-- Modal Structure -->
<div class="modal fade" id="articleModal" tabindex="-1" role="dialog" aria-labelledby="articleModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="articleModalLabel">Article Title</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <!-- Dynamic content will be loaded here -->
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-primary" data-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>

<!-- JavaScript to handle modal functionality -->
<script>
$(document).ready(function(){
    // Article data - this could be loaded from a database in a real implementation
    const articleData = {
        'article-qr-scan': {
            title: 'How to scan a QR code for verification',
            content: `
                <h4>Scanning QR Codes for Product Authentication</h4>
                <p>Authena's QR code system provides a quick and reliable way to verify product authenticity. Follow these steps to scan a product QR code:</p>
                
                <h5>Using Your Mobile Device:</h5>
                <ol>
                    <li>Locate the QR code on your product packaging or label</li>
                    <li>Open the Authena mobile app (or your device's camera app)</li>
                    <li>Point your camera at the QR code and hold steady</li>
                    <li>Wait for the app to recognize and scan the code</li>
                    <li>View the authentication results on your screen</li>
                </ol>
                
                <h5>Using Our Website:</h5>
                <ol>
                    <li>Visit the Authena website and navigate to the "Verify Product" section</li>
                    <li>Select "Scan QR Code" from the options</li>
                    <li>Allow camera access when prompted</li>
                    <li>Position the QR code in the scanning area</li>
                    <li>Review the authentication results</li>
                </ol>
                
                <h5>Troubleshooting Tips:</h5>
                <ul>
                    <li>Ensure the QR code is clean and undamaged</li>
                    <li>Make sure you have good lighting conditions</li>
                    <li>Hold your device steady while scanning</li>
                    <li>If scanning fails, try adjusting the distance between your device and the QR code</li>
                </ul>
                
                <p>If you continue to experience issues with QR code scanning, please contact our support team for assistance.</p>
            `
        },
        'article-serial-verification': {
            title: 'Verifying products with serial numbers',
            content: `
                <h4>Product Verification Using Serial Numbers</h4>
                <p>When QR code scanning isn't available, you can verify your product using its unique serial number. Here's how:</p>
                
                <h5>Finding Your Product's Serial Number:</h5>
                <p>Product serial numbers are typically located:</p>
                <ul>
                    <li>On the product packaging</li>
                    <li>On the product label</li>
                    <li>Underneath or inside the product</li>
                    <li>On a certificate of authenticity</li>
                </ul>
                
                <h5>Verification Steps:</h5>
                <ol>
                    <li>Navigate to the "Verify Product" section on our website or mobile app</li>
                    <li>Select "Enter Serial Number" from the options</li>
                    <li>Type the full serial number exactly as it appears (including any letters, numbers, and symbols)</li>
                    <li>Click "Verify" to check the product's authenticity</li>
                    <li>Review the verification results</li>
                </ol>
                
                <h5>Common Serial Number Formats:</h5>
                <p>Different brands use different serial number formats. Some common formats include:</p>
                <ul>
                    <li>Alphanumeric codes (e.g., ABC123XYZ)</li>
                    <li>Numeric-only codes (e.g., 123456789)</li>
                    <li>Hyphenated codes (e.g., ABC-123-XYZ)</li>
                </ul>
                
                <p>Always enter the serial number exactly as it appears, including any hyphens, spaces, or special characters.</p>
            `
        },
        'article-results-explained': {
            title: 'Understanding verification results',
            content: `
                <h4>Interpreting Your Product Verification Results</h4>
                <p>After scanning a QR code or entering a serial number, you'll receive one of the following verification results:</p>
                
                <h5>Authentication Status Types:</h5>
                
                <div class="result-type" style="border-left: 4px solid #38b2ac; padding: 10px; margin-bottom: 15px;">
                    <h6 style="color: #38b2ac;"><i class="fa fa-check-circle"></i> AUTHENTIC</h6>
                    <p>The product has been verified as genuine. It was manufactured by the official brand and is safe to use.</p>
                </div>
                
                <div class="result-type" style="border-left: 4px solid #e53e3e; padding: 10px; margin-bottom: 15px;">
                    <h6 style="color: #e53e3e;"><i class="fa fa-times-circle"></i> COUNTERFEIT ALERT</h6>
                    <p>Our system has identified this product as counterfeit. We recommend not using this product as it may be unsafe.</p>
                </div>
                
                <div class="result-type" style="border-left: 4px solid #ecc94b; padding: 10px; margin-bottom: 15px;">
                    <h6 style="color: #ecc94b;"><i class="fa fa-exclamation-circle"></i> SUSPICIOUS</h6>
                    <p>There are some inconsistencies with this product's authentication markers. The product might be counterfeit or tampered with.</p>
                </div>
                
                <div class="result-type" style="border-left: 4px solid #a0aec0; padding: 10px; margin-bottom: 15px;">
                    <h6 style="color: #a0aec0;"><i class="fa fa-question-circle"></i> NOT FOUND</h6>
                    <p>The system couldn't find any record matching this product. This could indicate a counterfeit or a product not yet registered in our database.</p>
                </div>
                
                <h5>Additional Information in Results:</h5>
                <ul>
                    <li><strong>Product Details:</strong> Authentic products will display official product information</li>
                    <li><strong>Manufacturing Date:</strong> When the product was produced</li>
                    <li><strong>Verification History:</strong> Number of times this product has been verified</li>
                    <li><strong>Location Map:</strong> Geographic locations where this product has been verified</li>
                </ul>
                
                <p>If you receive anything other than an "AUTHENTIC" result, we recommend contacting the retailer where you purchased the product.</p>
            `
        },
        'article-report-fake': {
            title: 'How to report a counterfeit product',
            content: `
                <h4>Reporting Counterfeit Products</h4>
                <p>If you believe you've encountered a counterfeit product, reporting it helps protect other consumers and supports anti-counterfeiting efforts. Here's how to submit a report:</p>
                
                <h5>Reporting Steps:</h5>
                <ol>
                    <li>Navigate to the "Report Counterfeit" section on our website or mobile app</li>
                    <li>Fill out the report form with the following information:
                        <ul>
                            <li>Product name and brand</li>
                            <li>Where and when you purchased the product</li>
                            <li>Serial number or QR code information (if available)</li>
                            <li>Description of why you believe the product is counterfeit</li>
                        </ul>
                    </li>
                    <li>Upload clear photos of the product and packaging (if possible)</li>
                    <li>Provide your contact information for follow-up</li>
                    <li>Submit the report</li>
                </ol>
                
                <h5>What Happens Next:</h5>
                <p>After submitting your report:</p>
                <ul>
                    <li>You'll receive a confirmation email with a reference number</li>
                    <li>Our team will review your report within 24-48 hours</li>
                    <li>The affected brand will be notified about the potential counterfeit</li>
                    <li>You may be contacted for additional information if needed</li>
                    <li>You'll receive updates on the status of your report</li>
                </ul>
                
                <p>Your report helps us identify counterfeit products and their sources, protecting consumers and legitimate brands. Thank you for your contribution to our anti-counterfeiting efforts.</p>
            `
        }
    };

    // Default content for articles not in our data object
    const defaultContent = `
        <div class="text-center">
            <i class="fa fa-file-text" style="font-size: 48px; color: #1a77e1;"></i>
            <h4 class="mt-3">Article Content Coming Soon</h4>
            <p>We're currently working on this help article. Please check back later for the complete information.</p>
            <p>If you need immediate assistance with this topic, please contact our support team.</p>
        </div>
    `;

    // Handle click on "Read More" buttons
    $('.help-article .btn').on('click', function(e) {
        e.preventDefault();
        
        // Get the article ID from the href attribute
        const articleId = $(this).attr('href').replace('.php', '');
        
        // Get the article title from the current article
        const articleTitle = $(this).closest('.help-article').find('h4').text();
        
        // Set the modal title
        $('#articleModalLabel').text(articleTitle);
        
        // Set the modal content based on our data object
        if (articleData[articleId]) {
            $('.modal-body').html(articleData[articleId].content);
        } else {
            $('.modal-body').html(defaultContent);
        }
        
        // Show the modal
        $('#articleModal').modal('show');
    });
});
</script>
    </body>
</html>