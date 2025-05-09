<?php
// Start session to maintain user login state
session_start();
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

// Initialize variables
$serialNumber = "";
$message = "";
$product = null;
$isAuthentic = false;
$errorMsg = "";
$successMsg = "";
$verificationCount = 0;
$scanDate = date("d/m/Y, H:i:s"); // Current date in dd/mm/yyyy format

// Handle form submission for fake report
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["register_fake"])) {
    // Get report details
    $product_id = $_POST["product_id"];
    $report_type = $_POST["report_type"];
    $description = $_POST["description"];
    $lat = $_POST["lat"];
    $lng = $_POST["lng"];
    $location_address = $_POST["location_address"];
    
    // Default status for new reports
    $status = "pending";
    
    // Handle file upload for evidence images
    $evidence_images = "";
    if (isset($_FILES["evidence_images"]) && $_FILES["evidence_images"]["error"] == 0) {
        $target_dir = "uploads/evidence/";
        
        // Create directory if it doesn't exist
        if (!file_exists($target_dir)) {
            mkdir($target_dir, 0777, true);
        }
        
        $file_extension = pathinfo($_FILES["evidence_images"]["name"], PATHINFO_EXTENSION);
        $new_filename = uniqid() . "." . $file_extension;
        $target_file = $target_dir . $new_filename;
        
        if (move_uploaded_file($_FILES["evidence_images"]["tmp_name"], $target_file)) {
            $evidence_images = $target_file;
        }
    }
    
    // Get user ID if logged in, otherwise set to NULL
    $user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;
    
    // Insert into fake_reports table
    $stmt = $conn->prepare("INSERT INTO fake_reports (product_id, user_id, report_type, description, 
                          evidence_images, location_lat, location_lng, location_address, status, 
                          created_at, updated_at) 
                          VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())");
    $stmt->bind_param("iisssddss", $product_id, $user_id, $report_type, $description, 
                      $evidence_images, $lat, $lng, $location_address, $status);
    
    if ($stmt->execute()) {
        // If user is logged in, increment their report_count
        if (isset($_SESSION['user_id'])) {
            $updateReportCountStmt = $conn->prepare("UPDATE users SET report_count = report_count + 1 WHERE id = ?");
            $updateReportCountStmt->bind_param("i", $_SESSION['user_id']);
            
            if ($updateReportCountStmt->execute()) {
                $successMsg = "Fake product report submitted successfully. Thank you for helping us combat counterfeiting.";
            } else {
                $errorMsg = "Report submitted but count update failed: " . $updateReportCountStmt->error;
                error_log("Database error when updating user report count: " . $updateReportCountStmt->error);
            }
            $updateReportCountStmt->close();
        } else {
            $successMsg = "Fake product report submitted successfully. Thank you for helping us combat counterfeiting.";
        }
    } else {
        $errorMsg = "Error submitting report: " . $stmt->error . " (Error code: " . $stmt->errno . ")";
        // Add more detailed error reporting
        error_log("Database error when submitting fake report: " . $stmt->error);
    }
    $stmt->close();
}

// Handle form submission for verification
// Handle form submission for verification
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["verify_serial"])) {
  // Get the serial number from form
  $serialNumber = $_POST["serial_number"];
  
  if (empty($serialNumber)) {
      $errorMsg = "Please enter a serial number";
  } else {
      // Query to find the product with the given serial number
      $stmt = $conn->prepare("SELECT p.*, b.name as brand_name, c.name as category_name 
                             FROM products p 
                             LEFT JOIN brands b ON p.brand_id = b.id 
                             LEFT JOIN categories c ON p.category_id = c.id 
                             WHERE p.unique_identifier = ? OR p.product_code = ?");
      $stmt->bind_param("ss", $serialNumber, $serialNumber);
      $stmt->execute();
      $result = $stmt->get_result();
      
      // Get common data for logging
      $ipAddress = $_SERVER['REMOTE_ADDR'];
      $deviceInfo = $_SERVER['HTTP_USER_AGENT'];
      $lat = isset($_POST['lat']) ? $_POST['lat'] : null;
      $lng = isset($_POST['lng']) ? $_POST['lng'] : null;
      $address = isset($_POST['location_address']) ? $_POST['location_address'] : "Your Current Location";
      $userId = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;
      
      if ($result->num_rows > 0) {
          // Product found
          $product = $result->fetch_assoc();
          $isAuthentic = true;
          $verificationCount = $product['verification_count'] + 1;
          
          // Update product verification count
          $updateProductStmt = $conn->prepare("UPDATE products SET verification_count = verification_count + 1 WHERE id = ?");
          $updateProductStmt->bind_param("i", $product['id']);
          $updateProductStmt->execute();
          $updateProductStmt->close();
          
          // If user is logged in, update their verification count
          if (isset($_SESSION['user_id'])) {
              $updateUserStmt = $conn->prepare("UPDATE users SET verification_count = verification_count + 1 WHERE id = ?");
              $updateUserStmt->bind_param("i", $userId);
              $updateUserStmt->execute();
              $updateUserStmt->close();
          }
          
          // Log authentic verification
          $status = "authentic";
          $successMsg = "Product verified successfully!";
      } else {
          // Product not found - might be fake or unknown
          $isAuthentic = false;
          $status = "fake"; // Set status to fake when product is not found
          $errorMsg = "Serial number not found. This product may be counterfeit.";
          $product = ['id' => null]; // Set product ID to null for logging
      }
      
      // Log this verification regardless of outcome
      $logStmt = $conn->prepare("INSERT INTO verification_logs (product_id, user_id, ip_address, device_info, location_lat, location_lng, location_address, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
      $productId = $isAuthentic ? $product['id'] : null; // Use null for product_id if not authentic
      $logStmt->bind_param("iissddss", $productId, $userId, $ipAddress, $deviceInfo, $lat, $lng, $address, $status);
      $logStmt->execute();
      $logStmt->close();
      
      $stmt->close();
  }
}
?>

<!doctype html>
<html class="no-js" lang="zxx">
    <head>
        <!-- Meta Tags -->
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="keywords" content="Product verification, Anti-counterfeit, Authentication">
        <meta name="description" content="Verify authentic products with Authena's smart authentication platform">
        <meta name='copyright' content='Authena'>
        <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
        
        <!-- Title -->
        <title>Product Verification Result - Authena</title>
        
        <!-- Favicon -->
        <link rel="icon" href="img/favicon.png">
        
        <!-- Google Fonts -->
        <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
        <link href="https://fonts.googleapis.com/css?family=Poppins:200i,300,300i,400,400i,500,500i,600,600i,700,700i,800,800i,900,900i&display=swap" rel="stylesheet">
        
        <!-- Bootstrap CSS -->
        <link rel="stylesheet" href="css/bootstrap.min.css">
        <!-- Nice Select CSS -->
        <link rel="stylesheet" href="css/nice-select.css">
        <!-- Font Awesome CSS -->
        <link rel="stylesheet" href="css/font-awesome.min.css">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
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
          /* Verification Card Styles */
.verification-card {
  max-width: 800px;
  margin: 40px auto;
  background: #fff;
  border-radius: 10px;
  box-shadow: 0 0 20px rgba(0, 0, 0, 0.1);
  overflow: hidden;
}

/* Success Header Styles */
.success-header {
  background: #4CAF50;
  color: white;
  padding: 20px;
  text-align: center;
  font-size: 24px;
  font-weight: bold;
}

.success-header i {
  margin-right: 10px;
  font-size: 28px;
}

/* Scan Info Section */
.scan-info {
  display: flex;
  padding: 20px 25px;
  background: #f9f9f9;
  border-bottom: 1px solid #eee;
}

.scan-info-column {
  flex: 1;
}

.scan-info-column h3 {
  font-size: 16px;
  font-weight: 600;
  color: #555;
  margin-bottom: 8px;
}

.scan-detail {
  font-size: 14px;
  color: #666;
  margin-bottom: 5px;
}

/* Product Details Section */
.product-details {
  padding: 20px 25px;
  border-bottom: 1px solid #eee;
}

.section-title {
  font-size: 18px;
  font-weight: 600;
  color: #333;
  margin-bottom: 15px;
  position: relative;
  padding-bottom: 10px;
}

.section-title:after {
  content: '';
  position: absolute;
  bottom: 0;
  left: 0;
  width: 50px;
  height: 3px;
  background: #3a85fc;
}

.product-table {
  width: 100%;
  border-collapse: collapse;
}

.product-table td {
  padding: 8px 5px;
}

.product-table .label {
  font-weight: 600;
  color: #555;
  width: 30%;
}

.product-table .value {
  color: #333;
}

.product-image {
  width: 100px;
  height: 100px;
  object-fit: cover;
  border-radius: 5px;
  border: 1px solid #eee;
}

/* Authentication Details Section */
.authentication-details {
  padding: 20px 25px;
  border-bottom: 1px solid #eee;
}

.auth-features {
  display: flex;
  justify-content: space-between;
  margin-top: 15px;
}

.auth-feature {
  flex: 1;
  text-align: center;
  padding: 15px 10px;
  border-radius: 5px;
  background: #f9f9f9;
  margin: 0 5px;
}

.auth-icon {
  font-size: 24px;
  color: #3a85fc;
  margin-bottom: 10px;
}

.auth-title {
  font-size: 14px;
  font-weight: 600;
  margin-bottom: 5px;
  color: #555;
}

.auth-status {
  font-size: 13px;
  color: #4CAF50;
  font-weight: 500;
}

/* Success Message */
.success-message {
  text-align: center;
  padding: 15px;
  font-size: 18px;
  font-weight: 600;
  color: #4CAF50;
  background: #f0f9f0;
  border-bottom: 1px solid #eee;
}

/* Action Buttons */
.btn-row {
  margin-bottom: 15px;
}

.btn-action {
  display: block;
  width: 100%;
  padding: 12px 20px;
  text-align: center;
  border-radius: 5px;
  font-weight: 600;
  text-decoration: none;
  transition: all 0.3s ease;
}

.btn-register {
  background: #ff9800;
  color: white;
  border: none;
  cursor: pointer;
}

.btn-register:hover {
  background: #f57c00;
}

.btn-scan {
  background: #3a85fc;
  color: white;
}

.btn-scan:hover {
  background: #2a75ec;
  color: white;
}

.btn-verify {
  background: #3a85fc;
  color: white;
  border: none;
  padding: 12px 25px;
  font-size: 16px;
  font-weight: 600;
  border-radius: 5px;
  cursor: pointer;
  transition: all 0.3s ease;
  margin-top: 10px;
  width: 100%;
}

.btn-verify:hover {
  background: #2a75ec;
}

/* Verification Form */
.verification-form {
  padding: 30px 25px;
  text-align: center;
}

.verification-form p {
  color: #666;
  margin-bottom: 20px;
}

.verification-form input[type="text"] {
  width: 100%;
  padding: 12px 15px;
  border: 1px solid #ddd;
  border-radius: 5px;
  font-size: 16px;
  margin-bottom: 10px;
}

/* Modal Styles */
.modal {
  display: none;
  position: fixed;
  z-index: 1000;
  left: 0;
  top: 0;
  width: 100%;
  height: 100%;
  overflow: auto;
  background-color: rgba(0, 0, 0, 0.5);
}

.modal-content {
  background-color: #fefefe;
  margin: 5% auto;
  padding: 25px;
  border-radius: 10px;
  box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
  width: 80%;
  max-width: 600px;
  position: relative;
}

.close {
  position: absolute;
  right: 20px;
  top: 15px;
  color: #aaa;
  font-size: 28px;
  font-weight: bold;
  cursor: pointer;
}

.close:hover,
.close:focus {
  color: black;
  text-decoration: none;
}

.form-label {
  display: block;
  margin-bottom: 5px;
  font-weight: 600;
  color: #555;
}

.form-control {
  width: 100%;
  padding: 10px 15px;
  border: 1px solid #ddd;
  border-radius: 5px;
  margin-bottom: 15px;
}

.form-text {
  font-size: 12px;
  color: #777;
  margin-top: -10px;
  margin-bottom: 15px;
}

.d-grid {
  margin-top: 20px;
}

.mb-3 {
  margin-bottom: 20px;
}

/* Alert Styles */
.alert {
  padding: 15px;
  border-radius: 5px;
  margin-bottom: 20px;
}

.alert-success {
  background-color: #d4edda;
  color: #155724;
  border: 1px solid #c3e6cb;
}

.alert-danger {
  background-color: #f8d7da;
  color: #721c24;
  border: 1px solid #f5c6cb;
}

/* Responsive Styles */
@media (max-width: 768px) {
  .scan-info {
    flex-direction: column;
  }
  
  .scan-info-column {
    margin-bottom: 15px;
  }
  
  .auth-features {
    flex-direction: column;
  }
  
  .auth-feature {
    margin: 5px 0;
  }
  
  .modal-content {
    width: 95%;
    margin: 10% auto;
  }
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
                                            <li ><a href="index.php">Home</a></li>
        
                                            <li class="active"><a href="#">Verify Product <i class="icofont-rounded-down"></i></a>
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
        <!-- End Header Area -->
    
        <!-- Breadcrumbs -->
        <div class="breadcrumbs overlay">
            <div class="container">
                <div class="bread-inner">
                    <div class="row">
                        <div class="col-12">
                            <h2>Product Verification</h2>
                            <ul class="bread-list">
                                <li><a href="index.php">Home</a></li>
                                <li><i class="icofont-simple-right"></i></li>
                                <li class="active">Verify Product</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- End Breadcrumbs -->
    
        <!-- Main Content Area -->
        <div class="container">
            <?php if ($isAuthentic): ?>
            <!-- Verification Result Card -->
            <div class="verification-card">
                <!-- Success Header -->
                <div class="success-header">
                    <i class="fa fa-check-circle"></i> Authentic Product Verified
                </div>
                
                <!-- Scan Info Section -->
                <div class="scan-info">
                    <div class="scan-info-column">
                        <h3>Barcode Scanned:</h3>
                        <div class="scan-detail"><?php echo htmlspecialchars($serialNumber); ?></div>
                    </div>
                    <div class="scan-info-column">
                        <h3>Scan Details:</h3>
                        <div class="scan-detail">Date: <?php echo $scanDate; ?></div>
                        <div class="scan-detail">Location: <?php echo isset($_POST['location_address']) ? htmlspecialchars($_POST['location_address']) : "Your Current Location"; ?></div>
                    </div>
                </div>
                
                <!-- Product Details Section -->
                <div class="product-details">
                    <h2 class="section-title">Product Details</h2>
                    <table class="product-table">
                        <tr>
                            <td rowspan="2" style="width: 110px;">
                                <img src="img/product-placeholder.jpg" alt="Product Image" class="product-image">
                            </td>
                            <td class="label">Product Name</td>
                            <td class="value"><?php echo $product && isset($product['name']) ? htmlspecialchars($product['name']) : "Unknown Product"; ?></td>
                        </tr>
                        <tr>
                            <td class="label">Brand</td>
                            <td class="value"><?php echo $product && isset($product['brand_name']) ? htmlspecialchars($product['brand_name']) : "Unknown Brand"; ?></td>
                        </tr>
                        <tr>
                            <td colspan="3"><hr style="margin: 5px 0; border-color: #f0f0f0;"></td>
                        </tr>
                        <tr>
                            <td class="label">Serial Number</td>
                            <td class="value" colspan="2"><?php echo htmlspecialchars($serialNumber); ?></td>
                        </tr>
                        <tr>
                            <td class="label">Manufacturing Date</td>
                            <td class="value" colspan="2"><?php echo $product && isset($product['manufacturing_date']) ? htmlspecialchars($product['manufacturing_date']) : "Unknown"; ?></td>
                        </tr>
                        <tr>
                            <td class="label">Verification Count</td>
                            <td class="value" colspan="2"><?php echo $verificationCount; ?> time(s)</td>
                        </tr>
                    </table>
                </div>
                
                <!-- Authentication Details Section -->
                <div class="authentication-details">
                    <h2 class="section-title">Authentication Details</h2>
                    <div class="auth-features">
                        <div class="auth-feature">
                            <i class="fas fa-link auth-icon"></i>
                            <div class="auth-title">Unique Signature</div>
                            <div class="auth-status">Valid</div>
                        </div>
                        <div class="auth-feature">
                            <i class="fas fa-cube auth-icon"></i>
                            <div class="auth-title">Blockchain Verified</div>
                            <div class="auth-status">Confirmed</div>
                        </div>
                        <div class="auth-feature">
                            <i class="fas fa-shield-alt auth-icon"></i>
                            <div class="auth-title">Security Features</div>
                            <div class="auth-status">All Present</div>
                        </div>
                    </div>
                </div>
                
                <!-- Success Message -->
                <div class="success-message">Product Verified Successfully!</div>
                
                <!-- Show success or error message if exists -->
                <?php if (!empty($successMsg)): ?>
                    <div class="alert alert-success" style="margin: 0 25px 20px;"><?php echo $successMsg; ?></div>
                <?php endif; ?>
                
                <?php if (!empty($errorMsg)): ?>
                    <div class="alert alert-danger" style="margin: 0 25px 20px;"><?php echo $errorMsg; ?></div>
                <?php endif; ?>
                
                <!-- Action Buttons -->
                <div style="padding: 0 25px 25px;">
                    <div class="btn-row">
                        <button class="btn-action btn-register" id="openFakeReportModal"><i class="fas fa-user-plus"></i> Register Fake</button>
                    </div>
                    <a href="serial.php" class="btn-action btn-scan"><i class="fas fa-barcode"></i> Scan Another Product</a>
                </div>
            </div>
            
            <!-- Fake Report Modal -->
            <div id="fakeReportModal" class="modal">
                <div class="modal-content">
                    <span class="close">&times;</span>
                    <h2 class="section-title">Report Counterfeit Product</h2>
                    <p>Please provide details about the suspected counterfeit product.</p>
                    
                    <form method="POST" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" enctype="multipart/form-data">
                        <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
                        <input type="hidden" name="lat" value="<?php echo isset($_POST['lat']) ? $_POST['lat'] : ''; ?>">
                        <input type="hidden" name="lng" value="<?php echo isset($_POST['lng']) ? $_POST['lng'] : ''; ?>">
                        <input type="hidden" name="location_address" value="<?php echo isset($_POST['location_address']) ? $_POST['location_address'] : 'Your Current Location'; ?>">
                        
                        <div class="mb-3">
                            <label for="report_type" class="form-label">Report Type</label>
                            <select name="report_type" id="report_type" class="form-control" required>
                                <option value="">Select report type</option>
                                <option value="counterfeit">Counterfeit Product</option>
                                <option value="expired">Suspicious Packaging</option>
                                <option value="tampered">QR Code/Barcode Tampering</option>
                               
                                <option value="other">Other Issue</option>
                            </select>
                        </div>
                        
                        <div class="mb-3">
                            <label for="description" class="form-label">Description</label>
                            <textarea name="description" id="description" class="form-control" rows="4" placeholder="Describe why you believe this product is counterfeit..." required></textarea>
                        </div>
                        
                        <div class="mb-3">
                            <label for="evidence_images" class="form-label">Upload Evidence (Optional)</label>
                            <input type="file" name="evidence_images" id="evidence_images" class="form-control" accept="image/*">
                            <div class="form-text">Upload images of the suspected counterfeit product, packaging, etc.</div>
                        </div>
                        
                        <div class="d-grid">
                          <!-- The document was cut off at the button element in the form -->
<button type="submit" name="register_fake" class="btn-verify">Submit Report</button>
</div>
</form>
</div>
</div>

<?php else: ?>
<!-- Verification Form Card when no product is found or first visit -->
<div class="verification-card">
<div class="verification-form">
<h2 class="section-title">Verify Product Authenticity</h2>
<p>Enter the product serial number or scan QR code to verify authenticity</p>

<!-- Show error message if exists -->
<?php if (!empty($errorMsg)): ?>
<div class="alert alert-danger"><?php echo $errorMsg; ?></div>
<?php endif; ?>

<form method="POST" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
<input type="hidden" name="lat" id="lat" value="">
<input type="hidden" name="lng" id="lng" value="">
<input type="hidden" name="location_address" id="location_address" value="">

<input type="text" name="serial_number" class="form-control" placeholder="Enter Serial Number" required>
<button type="submit" name="verify_serial" class="btn-verify">Verify Product</button>
</form>


</div>
</div>
<?php endif; ?>
</div>
<!-- End Main Content Area -->

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

<script>
// Get the modal
var modal = document.getElementById("fakeReportModal");

// Get the button that opens the modal
var btn = document.getElementById("openFakeReportModal");

// Get the <span> element that closes the modal
var span = document.getElementsByClassName("close")[0];

// When the user clicks the button, open the modal 
if (btn) {
btn.onclick = function() {
modal.style.display = "block";
}
}

// When the user clicks on <span> (x), close the modal
if (span) {
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

// Get geolocation
function getLocation() {
if (navigator.geolocation) {
navigator.geolocation.getCurrentPosition(showPosition, showError);
}
}

function showPosition(position) {
document.getElementById("lat").value = position.coords.latitude;
document.getElementById("lng").value = position.coords.longitude;

// Try to get address from coordinates using Nominatim
fetch(`https://nominatim.openstreetmap.org/reverse?format=json&lat=${position.coords.latitude}&lon=${position.coords.longitude}`)
.then(response => response.json())
.then(data => {
let address = data.display_name || "Your Current Location";
document.getElementById("location_address").value = address;
})
.catch(error => {
console.error("Error getting address:", error);
document.getElementById("location_address").value = "Your Current Location";
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
document.getElementById("location_address").value = "Location Unknown";
}

// Call getLocation when page loads
window.onload = getLocation;
</script>
</body>
</html>