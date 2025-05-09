<?php
// Start session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
?>
<!doctype html>
<html class="no-js" lang="en">
    <head>
        <!-- Meta Tags -->
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="keywords" content="Product Authentication, Anti-Counterfeit, Verification Platform">
        <meta name="description" content="Authena - Smart Product Authentication Platform to protect consumers from counterfeit products">
        <meta name='copyright' content='Authena'>
        <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
        
        <!-- Title -->
        <title>About Authena - Smart Product Authentication Platform</title>
        
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
                                <li><a href="about.php" class="active">About</a></li>
                                <li><a href="brands.php">Brands</a></li>
                                <li><a href="contact.php">Contact</a></li>
                                <li><a href="faq.html">FAQ</a></li>
                            </ul>
                        </div>
                        <!-- Top Right Contact Info -->
                        <div class="col-lg-6 col-md-7 col-12">
                            <ul class="top-contact">
                                <li><i class="fa fa-phone"></i>+880 1234 56789</li>
                                <li><i class="fa fa-envelope"></i>
                                    <a href="mailto:support@authena.com">support@authena.com</a>
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
                                    <a href="index.html"><img src="img/logo3.png" alt="Authena Logo"></a>
                                </div>
                                <div class="mobile-nav"></div>
                            </div>
        
                            <!-- Main Navigation -->
                            <div class="col-lg-7 col-md-9 col-12">
                                <div class="main-menu">
                                    <nav class="navigation">
                                        <ul class="nav menu">
                                            <li><a href="index.html">Home</a></li>
        
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
                            <h2>About Authena</h2>
                            <ul class="bread-list">
                                <li><a href="index.html">Home</a></li>
                                <li><i class="icofont-simple-right"></i></li>
                                <li class="active">About Us</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- End Breadcrumbs -->
    
        <!-- Start About Area -->
        <section class="about-area section">
            <div class="container">
                <div class="row align-items-center">
                    <div class="col-lg-6 col-md-12 col-12">
                        <div class="about-image">
                            <img src="img/contact-img1.jpg" alt="About Authena">
                        </div>
                    </div>
                    <div class="col-lg-6 col-md-12 col-12">
                        <div class="about-content">
                            <h3>Our Mission</h3>
                            <p>At Authena, we are dedicated to protecting consumers and brands from the growing threat of counterfeit products. Our mission is to provide a secure, reliable, and easy-to-use platform that empowers consumers to verify the authenticity of their purchases and helps brands protect their reputation.</p>
                            <p>Founded in 2020, Authena combines cutting-edge technology with a user-friendly interface to create a comprehensive product authentication solution that benefits both consumers and manufacturers.</p>
                            <div class="button">
                                <a href="contact.php" class="btn">Contact Us</a>
                                <a href="how-it-works.html" class="btn primary">How It Works</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
        <!-- End About Area -->
        
        <!-- Start Why Choose Us -->
        <section class="why-choose section">
            <div class="container">
                <div class="row">
                    <div class="col-lg-12">
                        <div class="section-title">
                            <h2>Why Choose Authena</h2>
                            <img src="img/section-img.png" alt="#">
                            <p>We provide the most reliable product authentication system on the market, protecting both consumers and brands.</p>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-lg-4 col-md-6 col-12">
                        <!-- Start Single Feature -->
                        <div class="single-feature">
                            <i class="icofont-safety"></i>
                            <h4>Reliable Security</h4>
                            <p>Our platform uses military-grade encryption and blockchain technology to create tamper-proof product identities that cannot be duplicated or falsified.</p>
                        </div>
                        <!-- End Single Feature -->
                    </div>
                    <div class="col-lg-4 col-md-6 col-12">
                        <!-- Start Single Feature -->
                        <div class="single-feature">
                            <i class="icofont-ui-touch-phone"></i>
                            <h4>Easy Verification</h4>
                            <p>Multiple verification methods including QR code scanning, serial number lookup, and image recognition make product authentication simple and fast.</p>
                        </div>
                        <!-- End Single Feature -->
                    </div>
                    <div class="col-lg-4 col-md-6 col-12">
                        <!-- Start Single Feature -->
                        <div class="single-feature">
                            <i class="icofont-chart-bar-graph"></i>
                            <h4>Real-time Analytics</h4>
                            <p>For brands, our platform provides valuable insights into product verification patterns, helping identify potential counterfeit hotspots.</p>
                        </div>
                        <!-- End Single Feature -->
                    </div>
                    <div class="col-lg-4 col-md-6 col-12">
                        <!-- Start Single Feature -->
                        <div class="single-feature">
                            <i class="icofont-globe-alt"></i>
                            <h4>Global Coverage</h4>
                            <p>Our system works worldwide, allowing consumers to verify products no matter where they are purchased or used.</p>
                        </div>
                        <!-- End Single Feature -->
                    </div>
                    <div class="col-lg-4 col-md-6 col-12">
                        <!-- Start Single Feature -->
                        <div class="single-feature">
                            <i class="icofont-database-add"></i>
                            <h4>Comprehensive Database</h4>
                            <p>We maintain a vast database of authentic products and known counterfeits to ensure accurate verification results every time.</p>
                        </div>
                        <!-- End Single Feature -->
                    </div>
                    <div class="col-lg-4 col-md-6 col-12">
                        <!-- Start Single Feature -->
                        <div class="single-feature">
                            <i class="icofont-support"></i>
                            <h4>24/7 Support</h4>
                            <p>Our dedicated team is always available to assist with any authentication issues or questions you may have.</p>
                        </div>
                        <!-- End Single Feature -->
                    </div>
                </div>
            </div>
        </section>
        <!--/ End Why Choose Us -->
        
        <!-- Start Team -->
       
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
                                <p>© 2025 Authena. All rights reserved. | Designed with <i class="icofont-heart"></i> to fight fakes.</p>
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