<!doctype html>
<html class="no-js" lang="en">
    <head>
        <!-- Meta Tags -->
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="keywords" content="Authena Blog, Product Authentication, Anti-counterfeit">
        <meta name="description" content="Latest news and insights about product authentication and counterfeit prevention">
        <meta name='copyright' content=''>
        <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
        
        <!-- Title -->
        <title>Authena Blog - Smart Product Authentication Platform.</title>
        
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
                                                    <li><a href="blog.php" class="active">Blog</a></li>
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
                            <h2>Blog</h2>
                            <ul class="bread-list">
                                <li><a href="index.php">Home</a></li>
                                <li><i class="icofont-simple-right"></i></li>
                                <li class="active">Blog</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- End Breadcrumbs -->
    
        <!-- Start Blog Area -->
        <section class="blog section" id="blog">
            <div class="container">
                <div class="row">
                    <div class="col-lg-8 col-md-12 col-12">
                        <!-- Single Blog -->
                        <div class="single-news">
                            <div class="news-head">
                                <img src="img/blog-small1.png" alt="Blog Image">
                                <div class="news-tag">
                                    <span class="category">Authentication</span>
                                </div>
                            </div>
                            <div class="news-body">
                                <div class="meta">
                                    <span><i class="fa fa-calendar"></i>April 15, 2025</span>
                                    <span><i class="fa fa-user"></i>By Admin</span>
                                    <span><i class="fa fa-comments"></i>34 Comments</span>
                                </div>
                                <h2><a href="blog.php">How Blockchain is Revolutionizing Product Authentication</a></h2>
                                <p>The rise of counterfeit products continues to plague global markets, with fake goods costing businesses billions annually. Blockchain technology offers a promising solution by providing immutable, transparent record-keeping for product journeys from manufacturer to consumer.</p>
                                <a href="blog.php" class="btn">Read More</a>
                            </div>
                        </div>
                        <!-- End Single Blog -->
                        
                        <!-- Single Blog -->
                        <div class="single-news">
                            <div class="news-head">
                                <img src="img/blog-small4.jpg" alt="Blog Image">
                                <div class="news-tag">
                                    <span class="category">Security</span>
                                </div>
                            </div>
                            <div class="news-body">
                                <div class="meta">
                                    <span><i class="fa fa-calendar"></i>April 10, 2025</span>
                                    <span><i class="fa fa-user"></i>By Admin</span>
                                    <span><i class="fa fa-comments"></i>28 Comments</span>
                                </div>
                                <h2><a href="blog.php">The Hidden Dangers of Counterfeit Pharmaceuticals</a></h2>
                                <p>Counterfeit medications pose severe health risks to consumers worldwide. From ineffective treatments to harmful ingredients, fake pharmaceuticals can lead to treatment failure, adverse reactions, and even death. Learn how to identify authentic medications and protect yourself.</p>
                                <a href="blog.php" class="btn">Read More</a>
                            </div>
                        </div>
                        <!-- End Single Blog -->
                        
                        <!-- Single Blog -->
                        <div class="single-news">
                            <div class="news-head">
                                <img src="img/blog4.jpg" alt="Blog Image">
                                <div class="news-tag">
                                    <span class="category">Technology</span>
                                </div>
                            </div>
                            <div class="news-body">
                                <div class="meta">
                                    <span><i class="fa fa-calendar"></i>April 5, 2025</span>
                                    <span><i class="fa fa-user"></i>By Admin</span>
                                    <span><i class="fa fa-comments"></i>15 Comments</span>
                                </div>
                                <h2><a href="blog.php">QR Codes vs NFC: Which Authentication Method Works Best?</a></h2>
                                <p>As brands seek more secure ways to authenticate products, QR codes and NFC technology have emerged as leading solutions. This article compares the benefits and limitations of each method, helping businesses choose the right approach for their authentication needs.</p>
                                <a href="blog.php" class="btn">Read More</a>
                            </div>
                        </div>
                        <!-- End Single Blog -->
                        
                        <!-- Single Blog -->
                        <div class="single-news">
                            <div class="news-head">
                                <img src="img/blog5.png" alt="Blog Image">
                                <div class="news-tag">
                                    <span class="category">Consumer Guide</span>
                                </div>
                            </div>
                            <div class="news-body">
                                <div class="meta">
                                    <span><i class="fa fa-calendar"></i>March 28, 2025</span>
                                    <span><i class="fa fa-user"></i>By Admin</span>
                                    <span><i class="fa fa-comments"></i>42 Comments</span>
                                </div>
                                <h2><a href="blog.php">5 Ways to Spot Fake Luxury Products Before You Buy</a></h2>
                                <p>The counterfeit luxury market continues to grow more sophisticated, making fake items harder to detect. From examining stitching to verifying authentication markers, this guide provides practical tips to help consumers avoid purchasing counterfeit luxury goods.</p>
                                <a href="blog.php" class="btn">Read More</a>
                            </div>
                        </div>
                        <!-- End Single Blog -->
                        
                        <!-- Pagination -->
                        <div class="row">
                            <div class="col-12">
                                <div class="pagination">
                                    <ul class="pagination-list">
                                        <li><a href="#"><i class="icofont-rounded-left"></i></a></li>
                                        <li class="active"><a href="#">1</a></li>
                                        <li><a href="#">2</a></li>
                                        <li><a href="#">3</a></li>
                                        <li><a href="#"><i class="icofont-rounded-right"></i></a></li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                        <!-- End Pagination -->
                    </div>
                    
                    <!-- Sidebar -->
                    <div class="col-lg-4 col-md-12 col-12">
                        <div class="main-sidebar">
                            <!-- Single Widget -->
                            <div class="single-widget search">
                                <div class="form">
                                    <input type="text" placeholder="Search Here...">
                                    <button class="button" type="submit"><i class="fa fa-search"></i></button>
                                </div>
                            </div>
                            <!-- End Single Widget -->
                            
                            <!-- Single Widget -->
                            <div class="single-widget category">
                                <h3 class="title">Blog Categories</h3>
                                <ul class="categor-list">
                                    <li><a href="#">Authentication Technology</a></li>
                                    <li><a href="#">Counterfeit Prevention</a></li>
                                    <li><a href="#">Industry News</a></li>
                                    <li><a href="#">Consumer Guides</a></li>
                                    <li><a href="#">Case Studies</a></li>
                                    <li><a href="#">Company Updates</a></li>
                                </ul>
                            </div>
                            <!-- End Single Widget -->
                            
                            <!-- Single Widget -->
                            <div class="single-widget recent-post">
                                <h3 class="title">Recent Posts</h3>
                                <!-- Single Post -->
                                <div class="single-post">
                                    <div class="image">
                                        <img src="img/blog-small1.jpg" alt="#">
                                    </div>
                                    <div class="content">
                                        <h5><a href="#">AI-Powered Verification: The Next Frontier</a></h5>
                                        <ul class="comment">
                                            <li><i class="fa fa-calendar"></i>April 12, 2025</li>
                                            <li><i class="fa fa-comments"></i>20 Comments</li>
                                        </ul>
                                    </div>
                                </div>
                                <!-- End Single Post -->
                                <!-- Single Post -->
                                <div class="single-post">
                                    <div class="image">
                                        <img src="img/blog-small2.jpg" alt="#">
                                    </div>
                                    <div class="content">
                                        <h5><a href="#">Securing Supply Chains with Blockchain</a></h5>
                                        <ul class="comment">
                                            <li><i class="fa fa-calendar"></i>April 8, 2025</li>
                                            <li><i class="fa fa-comments"></i>35 Comments</li>
                                        </ul>
                                    </div>
                                </div>
                                <!-- End Single Post -->
                                <!-- Single Post -->
                                <div class="single-post">
                                    <div class="image">
                                        <img src="img/blog-small3.jpg" alt="#">
                                    </div>
                                    <div class="content">
                                        <h5><a href="#">The Economic Impact of Counterfeiting</a></h5>
                                        <ul class="comment">
                                            <li><i class="fa fa-calendar"></i>March 25, 2025</li>
                                            <li><i class="fa fa-comments"></i>15 Comments</li>
                                        </ul>
                                    </div>
                                </div>
                                <!-- End Single Post -->
                            </div>
                            <!-- End Single Widget -->
                            
                            <!-- Single Widget -->
                            <div class="single-widget tags">
                                <h3 class="title">Popular Tags</h3>
                                <ul class="tag-cloud">
                                    <li><a href="#">Authentication</a></li>
                                    <li><a href="#">Blockchain</a></li>
                                    <li><a href="#">QR Code</a></li>
                                    <li><a href="#">Security</a></li>
                                    <li><a href="#">Brand Protection</a></li>
                                    <li><a href="#">Counterfeit</a></li>
                                    <li><a href="#">Supply Chain</a></li>
                                    <li><a href="#">NFC</a></li>
                                    <li><a href="#">Consumer Safety</a></li>
                                </ul>
                            </div>
                            <!-- End Single Widget -->
                            
                            <!-- Single Widget -->
                            <div class="single-widget newsletter">
                                <h3 class="title">Newsletter</h3>
                                <div class="form">
                                    <p>Subscribe to our newsletter to receive authentication tips and industry updates.</p>
                                    <form action="#" method="post">
                                        <input type="email" placeholder="Your Email" required>
                                        <button type="submit" class="button btn">Subscribe</button>
                                    </form>
                                </div>
                            </div>
                            <!-- End Single Widget -->
                        </div>
                    </div>
                </div>
            </div>
        </section>
        <!-- End Blog Area -->
        
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