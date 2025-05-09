<?php
// Start session to maintain login state
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id']; // Get the current user's ID

// Database connection
$host = 'localhost';
$dbname = 'authena';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

// Function to get verification data by date range for specific user
function getVerificationsByDateRange($pdo, $user_id, $days = 30) {
    $sql = "SELECT DATE(verification_timestamp) as date, 
                   COUNT(*) as total_verifications,
                   SUM(CASE WHEN status = 'authentic' THEN 1 ELSE 0 END) as authentic,
                   SUM(CASE WHEN status = 'fake' THEN 1 ELSE 0 END) as fake
            FROM verification_logs 
            WHERE verification_timestamp >= DATE_SUB(CURDATE(), INTERVAL $days DAY)
            AND user_id = :user_id
            GROUP BY DATE(verification_timestamp)
            ORDER BY date ASC";
    
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Function to get verification data by location for specific user
function getVerificationsByLocation($pdo, $user_id, $limit = 10) {
    $sql = "SELECT location_address, 
                   COUNT(*) as total_verifications,
                   SUM(CASE WHEN status = 'authentic' THEN 1 ELSE 0 END) as authentic,
                   SUM(CASE WHEN status = 'fake' THEN 1 ELSE 0 END) as fake
            FROM verification_logs 
            WHERE location_address IS NOT NULL
            AND user_id = :user_id
            GROUP BY location_address
            ORDER BY total_verifications DESC
            LIMIT $limit";
    
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Function to get verification data by brand for specific user
function getVerificationsByBrand($pdo, $user_id) {
    $sql = "SELECT b.name as brand_name, 
                   COUNT(*) as total_verifications,
                   SUM(CASE WHEN vl.status = 'authentic' THEN 1 ELSE 0 END) as authentic,
                   SUM(CASE WHEN vl.status = 'fake' THEN 1 ELSE 0 END) as fake
            FROM verification_logs vl
            JOIN products p ON vl.product_id = p.id
            JOIN brands b ON p.brand_id = b.id
            WHERE vl.user_id = :user_id
            GROUP BY b.name
            ORDER BY total_verifications DESC";
    
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Function to get verification data by product category for specific user
function getVerificationsByCategory($pdo, $user_id) {
    $sql = "SELECT c.name as category_name, 
                   COUNT(*) as total_verifications,
                   SUM(CASE WHEN vl.status = 'authentic' THEN 1 ELSE 0 END) as authentic,
                   SUM(CASE WHEN vl.status = 'fake' THEN 1 ELSE 0 END) as fake
            FROM verification_logs vl
            JOIN products p ON vl.product_id = p.id
            JOIN categories c ON p.category_id = c.id
            WHERE vl.user_id = :user_id
            GROUP BY c.name
            ORDER BY total_verifications DESC";
    
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Function to get most verified products for specific user
function getMostVerifiedProducts($pdo, $user_id, $limit = 5) {
    $sql = "SELECT p.name as product_name, 
                   p.product_code,
                   b.name as brand_name,
                   COUNT(*) as verification_count,
                   SUM(CASE WHEN vl.status = 'authentic' THEN 1 ELSE 0 END) as authentic,
                   SUM(CASE WHEN vl.status = 'fake' THEN 1 ELSE 0 END) as fake
            FROM verification_logs vl
            JOIN products p ON vl.product_id = p.id
            JOIN brands b ON p.brand_id = b.id
            WHERE vl.user_id = :user_id
            GROUP BY p.id
            ORDER BY verification_count DESC
            LIMIT $limit";
    
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Function to get total statistics for specific user
function getTotalStats($pdo, $user_id) {
    $sql = "SELECT 
            (SELECT COUNT(*) FROM verification_logs WHERE user_id = :user_id1) as total_verifications,
            (SELECT COUNT(*) FROM verification_logs WHERE status = 'authentic' AND user_id = :user_id2) as authentic_verifications,
            (SELECT COUNT(*) FROM verification_logs WHERE status = 'fake' AND user_id = :user_id3) as fake_verifications,
            (SELECT COUNT(*) FROM fake_reports WHERE user_id = :user_id4) as total_reports";
    
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':user_id1', $user_id, PDO::PARAM_INT);
    $stmt->bindParam(':user_id2', $user_id, PDO::PARAM_INT);
    $stmt->bindParam(':user_id3', $user_id, PDO::PARAM_INT);
    $stmt->bindParam(':user_id4', $user_id, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

// Fetch data for charts
$verificationsByDate = getVerificationsByDateRange($pdo, $user_id);
$verificationsByLocation = getVerificationsByLocation($pdo, $user_id);
$verificationsByBrand = getVerificationsByBrand($pdo, $user_id);
$verificationsByCategory = getVerificationsByCategory($pdo, $user_id);
$mostVerifiedProducts = getMostVerifiedProducts($pdo, $user_id);
$totalStats = getTotalStats($pdo, $user_id);

// Function to convert data to JSON for JavaScript charts
function dataToChartJson($data, $dateKey = null) {
    $result = [];
    foreach ($data as $row) {
        if ($dateKey) {
            $row[$dateKey] = strtotime($row[$dateKey]) * 1000; // Convert to JS timestamp
        }
        $result[] = $row;
    }
    return json_encode($result);
}

// Prepare data for charts
$dateChartData = dataToChartJson($verificationsByDate, 'date');
$locationChartData = dataToChartJson($verificationsByLocation);
$brandChartData = dataToChartJson($verificationsByBrand);
$categoryChartData = dataToChartJson($verificationsByCategory);
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
    <title>Authena - My Analytics Dashboard</title>
    
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
    
    <!-- ApexCharts -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/apexcharts@3.37.1/dist/apexcharts.css">
    <script src="https://cdn.jsdelivr.net/npm/apexcharts@3.37.1/dist/apexcharts.min.js"></script>
    
    <style>
        .card {
            border-radius: 15px;
            box-shadow: 0 6px 16px rgba(0, 0, 0, 0.08);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            margin-bottom: 25px;
            border: none;
        }
        
        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 12px 20px rgba(0, 0, 0, 0.12);
        }
        
        .card-header {
            background-color: #fff;
            border-bottom: 1px solid rgba(0, 0, 0, 0.08);
            border-top-left-radius: 15px !important;
            border-top-right-radius: 15px !important;
            padding: 20px;
            font-weight: 600;
            font-size: 18px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .user-info {
            font-size: 14px;
            color: #666;
            font-weight: normal;
        }
        
        .card-body {
            padding: 20px;
        }
        
        .stat-card {
            border-radius: 12px;
            padding: 20px;
            color: white;
            margin-bottom: 20px;
            min-height: 130px;
            position: relative;
            overflow: hidden;
        }
        
        .stat-card .icon {
            position: absolute;
            bottom: -10px;
            right: 10px;
            font-size: 60px;
            opacity: 0.2;
        }
        
        .stat-card h3 {
            font-size: 28px;
            margin-bottom: 5px;
            font-weight: 700;
        }
        
        .stat-card p {
            font-size: 16px;
            margin: 0;
            font-weight: 500;
        }
        
        .bg-primary-gradient {
            background: linear-gradient(45deg, #1a76d2, #4f9ef3);
        }
        
        .bg-success-gradient {
            background: linear-gradient(45deg, #28a745, #48d368);
        }
        
        .bg-danger-gradient {
            background: linear-gradient(45deg, #dc3545, #ff6678);
        }
        
        .bg-warning-gradient {
            background: linear-gradient(45deg, #ffc107, #ffd54f);
        }
        
        .bg-info-gradient {
            background: linear-gradient(45deg, #17a2b8, #55d5e0);
        }
        
        .table-responsive {
            border-radius: 10px;
            overflow: hidden;
        }
        
        .table {
            margin-bottom: 0;
        }
        
        .table thead th {
            border-top: none;
            background-color: #f8f9fa;
            color: #495057;
            font-weight: 600;
        }
        
        .breadcrumbs {
            padding: 150px 0 60px;
        }
        
        .chart-container {
            min-height: 400px;
        }
        
        .section-title {
            margin-bottom: 40px;
        }
        
        .section-title h2 {
            position: relative;
            margin-bottom: 20px;
            padding-bottom: 15px;
            font-weight: 700;
        }
        
        .section-title h2:after {
            content: '';
            position: absolute;
            left: 0;
            bottom: 0;
            height: 3px;
            width: 60px;
            background-color: #1a76d2;
        }
        
        .badge-auth {
            background-color: #28a745;
            color: white;
        }
        
        .badge-fake {
            background-color: #dc3545;
            color: white;
        }
        
        .no-data {
            text-align: center;
            padding: 40px;
            color: #666;
        }
        
        .no-data i {
            font-size: 48px;
            margin-bottom: 15px;
            color: #ddd;
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
        
                                            <li ><a href="#">Verify Product <i class="icofont-rounded-down"></i></a>
                                                <ul class="dropdown">
                                                    <li class="active"><a href="serial.php">Enter Serial Number</a></li>
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
    <!-- End Header Area -->

    <!-- Breadcrumbs -->
    <div class="breadcrumbs overlay">
        <div class="container">
            <div class="bread-inner">
                <div class="row">
                    <div class="col-12">
                        <h2>My Scan Analytics</h2>
                        <ul class="bread-list">
                            <li><a href="index.php">Home</a></li>
                            <li><i class="icofont-simple-right"></i></li>
                            <li>Insights</li>
                            <li><i class="icofont-simple-right"></i></li>
                            <li class="active">My Scan Analytics</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- End Breadcrumbs -->

    <!-- Analytics Section -->
    <section class="section">
        <div class="container">
            <div class="row">
                <div class="col-12">
                    <div class="section-title">
                        <h2>My Verification History</h2>
                        <p>View your personal product verification history and insights across the platform.</p>
                    </div>
                </div>
            </div>

            <!-- Stats Cards -->
            <div class="row">
                <div class="col-lg-3 col-md-6">
                    <div class="stat-card bg-primary-gradient">
                        <div class="icon">
                            <i class="icofont-chart-bar-graph"></i>
                        </div>
                        <h3><?php echo number_format($totalStats['total_verifications']); ?></h3>
                        <p>Total Verifications</p>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6">
                    <div class="stat-card bg-success-gradient">
                        <div class="icon">
                            <i class="icofont-verification-check"></i>
                        </div>
                        <h3><?php echo number_format($totalStats['authentic_verifications']); ?></h3>
                        <p>Authentic Products</p>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6">
                    <div class="stat-card bg-danger-gradient">
                        <div class="icon">
                            <i class="icofont-warning-alt"></i>
                        </div>
                        <h3><?php echo number_format($totalStats['fake_verifications']); ?></h3>
                        <p>Counterfeit Detected</p>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6">
                    <div class="stat-card bg-info-gradient">
                        <div class="icon">
                            <i class="icofont-file-alt"></i>
                        </div>
                        <h3><?php echo number_format($totalStats['total_reports']); ?></h3>
                        <p>Reports Submitted</p>
                    </div>
                </div>
            </div>

            <!-- Most Verified Products Table -->
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <span>My Most Verified Products</span>
                            <span class="user-info">Showing data for: <?php echo htmlspecialchars($_SESSION['username']); ?></span>
                        </div>
                        <div class="card-body">
                            <?php if (count($mostVerifiedProducts) > 0): ?>
                            <div class="table-responsive">
                                <table class="table table-striped">
                                    <thead>
                                        <tr>
                                            <th>Product Name</th>
                                            <th>Product Code</th>
                                            <th>Brand</th>
                                            <th>Total Verifications</th>
                                            <th>Authentic</th>
                                            <th>Fake</th>
                                            <th>Authentication Rate</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($mostVerifiedProducts as $product): ?>
                                            <?php 
                                            $authRate = ($product['verification_count'] > 0) ? 
                                                        round(($product['authentic'] / $product['verification_count']) * 100) : 0;
                                            
                                            $authClass = '';
                                            if ($authRate >= 90) $authClass = 'text-success';
                                            else if ($authRate >= 70) $authClass = 'text-warning';
                                            else $authClass = 'text-danger';
                                            ?>
                                            <tr>
                                                <td><?php echo $product['product_name']; ?></td>
                                                <td><?php echo $product['product_code']; ?></td>
                                                <td><?php echo $product['brand_name']; ?></td>
                                                <td><?php echo $product['verification_count']; ?></td>
                                                <td><span class="badge badge-auth"><?php echo $product['authentic']; ?></span></td>
                                                <td><span class="badge badge-fake"><?php echo $product['fake']; ?></span></td>
                                                <td class="<?php echo $authClass; ?>"><?php echo $authRate; ?>%</td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                            <?php else: ?>
                            <div class="no-data">
                                <i class="icofont-chart-bar-graph"></i>
                                <p>You haven't verified any products yet.</p>
                                <a href="serial.php" class="btn">Start Verifying Products</a>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Verifications by Location -->
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <span>My Verification Locations</span>
                            <span class="user-info">Showing data for: <?php echo htmlspecialchars($_SESSION['username']); ?></span>
                        </div>
                        <div class="card-body">
                            <?php if (count($verificationsByLocation) > 0): ?>
                            <div class="table-responsive">
                                <table class="table table-striped">
                                    <thead>
                                        <tr>
                                            <th>Location</th>
                                            <th>Total Verifications</th>
                                            <th>Authentic</th>
                                            <th>Fake</th>
                                            <th>Authentication Rate</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($verificationsByLocation as $location): ?>
                                            <?php 
                                            $authRate = ($location['total_verifications'] > 0) ? 
                                                        round(($location['authentic'] / $location['total_verifications']) * 100) : 0;
                                            
                                            $authClass = '';
                                            if ($authRate >= 90) $authClass = 'text-success';
                                            else if ($authRate >= 70) $authClass = 'text-warning';
                                            else $authClass = 'text-danger';
                                            ?>
                                            <tr>
                                                <td><?php echo $location['location_address']; ?></td>
                                                <td><?php echo $location['total_verifications']; ?></td>
                                                <td><span class="badge badge-auth"><?php echo $location['authentic']; ?></span></td>
                                                <td><span class="badge badge-fake"><?php echo $location['fake']; ?></span></td>
                                                <td class="<?php echo $authClass; ?>"><?php echo $authRate; ?>%</td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                            <?php else: ?>
                            <div class="no-data">
                                <i class="icofont-location-pin"></i>
                                <p>No location data available for your verifications.</p>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Verifications by Brand -->
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <span>My Brand Verification History</span>
                            <span class="user-info">Showing data for: <?php echo htmlspecialchars($_SESSION['username']); ?></span>
                        </div>
                        <div class="card-body">
                            <?php if (count($verificationsByBrand) > 0): ?>
                            <div class="table-responsive">
                                <table class="table table-striped">
                                    <thead>
                                        <tr>
                                            <th>Brand</th>
                                            <th>Total Verifications</th>
                                            <th>Authentic</th>
                                            <th>Fake</th>
                                            <th>Authentication Rate</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($verificationsByBrand as $brand): ?>
                                            <?php 
                                            $authRate = ($brand['total_verifications'] > 0) ? 
                                                        round(($brand['authentic'] / $brand['total_verifications']) * 100) : 0;
                                            
                                            $authClass = '';
                                            if ($authRate >= 90) $authClass = 'text-success';
                                            else if ($authRate >= 70) $authClass = 'text-warning';
                                            else $authClass = 'text-danger';
                                            ?>
                                            <tr>
                                                <td><?php echo $brand['brand_name']; ?></td>
                                                <td><?php echo $brand['total_verifications']; ?></td>
                                                <td><span class="badge badge-auth"><?php echo $brand['authentic']; ?></span></td>
                                                <td><span class="badge badge-fake"><?php echo $brand['fake']; ?></span></td>
                                                <td class="<?php echo $authClass; ?>"><?php echo $authRate; ?>%</td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                            <?php else: ?>
                            <div class="no-data">
                                <i class="icofont-brand-acer"></i>
                                <p>No brand verification data available.</p>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Verifications by Category -->
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <span>My Category Verification History</span>
                            <span class="user-info">Showing data for: <?php echo htmlspecialchars($_SESSION['username']); ?></span>
                        </div>
                        <div class="card-body">
                            <?php if (count($verificationsByCategory) > 0): ?>
                            <div class="table-responsive">
                                <table class="table table-striped">
                                    <thead>
                                        <tr>
                                            <th>Category</th>
                                            <th>Total Verifications</th>
                                            <th>Authentic</th>
                                            <th>Fake</th>
                                            <th>Authentication Rate</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($verificationsByCategory as $category): ?>
                                            <?php 
                                            $authRate = ($category['total_verifications'] > 0) ? 
                                                        round(($category['authentic'] / $category['total_verifications']) * 100) : 0;
                                            
                                            $authClass = '';
                                            if ($authRate >= 90) $authClass = 'text-success';
                                            else if ($authRate >= 70) $authClass = 'text-warning';
                                            else $authClass = 'text-danger';
                                            ?>
                                            <tr>
                                                <td><?php echo $category['category_name']; ?></td>
                                                <td><?php echo $category['total_verifications']; ?></td>
                                                <td><span class="badge badge-auth"><?php echo $category['authentic']; ?></span></td>
                                                <td><span class="badge badge-fake"><?php echo $category['fake']; ?></span></td>
                                                <td class="<?php echo $authClass; ?>"><?php echo $authRate; ?>%</td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                            <?php else: ?>
                            <div class="no-data">
                                <i class="icofont-tags"></i>
                                <p>No category verification data available.</p>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Charts Section -->
            <div class="row">
                <!-- Verifications Over Time -->
                <div class="col-lg-6 col-md-12">
                    <div class="card">
                        <div class="card-header">
                            <span>Verification History (Last 30 Days)</span>
                            <span class="user-info">Showing data for: <?php echo htmlspecialchars($_SESSION['username']); ?></span>
                        </div>
                        <div class="card-body">
                            <?php if (count($verificationsByDate) > 0): ?>
                            <div id="timeSeriesChart" class="chart-container"></div>
                            <?php else: ?>
                            <div class="no-data">
                                <i class="icofont-chart-line"></i>
                                <p>No verification history data available.</p>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Verifications by Brand Chart -->
                <div class="col-lg-6 col-md-12">
                    <div class="card">
                        <div class="card-header">
                            <span>Verifications by Brand</span>
                            <span class="user-info">Showing data for: <?php echo htmlspecialchars($_SESSION['username']); ?></span>
                        </div>
                        <div class="card-body">
                            <?php if (count($verificationsByBrand) > 0): ?>
                            <div id="brandBarChart" class="chart-container"></div>
                            <?php else: ?>
                            <div class="no-data">
                                <i class="icofont-chart-pie"></i>
                                <p>No brand data available.</p>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- More Charts -->
            <div class="row">
                <!-- Verifications by Category Chart -->
                <div class="col-lg-6 col-md-12">
                    <div class="card">
                        <div class="card-header">
                            <span>Verifications by Category</span>
                            <span class="user-info">Showing data for: <?php echo htmlspecialchars($_SESSION['username']); ?></span>
                        </div>
                        <div class="card-body">
                            <?php if (count($verificationsByCategory) > 0): ?>
                            <div id="categoryPieChart" class="chart-container"></div>
                            <?php else: ?>
                            <div class="no-data">
                                <i class="icofont-chart-pie-alt"></i>
                                <p>No category data available.</p>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Authentic vs Fake Distribution -->
                <div class="col-lg-6 col-md-12">
                    <div class="card">
                        <div class="card-header">
                            <span>Authentic vs Fake Distribution</span>
                            <span class="user-info">Showing data for: <?php echo htmlspecialchars($_SESSION['username']); ?></span>
                        </div>
                        <div class="card-body">
                            <?php if ($totalStats['total_verifications'] > 0): ?>
                            <div id="authenticFakeChart" class="chart-container"></div>
                            <?php else: ?>
                            <div class="no-data">
                                <i class="icofont-chart-radar-graph"></i>
                                <p>No verification data available.</p>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <!-- End Analytics Section -->

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
    <!-- Bootstrap JS -->
    <script src="js/bootstrap.min.js"></script>

    <!-- Main JS -->
    <script src="js/main.js"></script>

    <!-- ApexCharts Initialize -->
    <script>
        // Only initialize charts if data exists
        <?php if (count($verificationsByDate) > 0): ?>
        // Time Series Chart
        var timeSeriesOptions = {
            series: [{
                name: 'Authentic',
                data: <?php echo json_encode(array_map(function($item) { return $item['authentic']; }, $verificationsByDate)); ?>
            }, {
                name: 'Fake',
                data: <?php echo json_encode(array_map(function($item) { return $item['fake']; }, $verificationsByDate)); ?>
            }],
            chart: {
                type: 'area',
                height: 350,
                stacked: false,
                toolbar: {
                    show: false
                },
            },
            colors: ['#28a745', '#dc3545'],
            dataLabels: {
                enabled: false
            },
            stroke: {
                curve: 'smooth',
                width: 2,
            },
            fill: {
                type: 'gradient',
                gradient: {
                    opacityFrom: 0.6,
                    opacityTo: 0.1,
                }
            },
            legend: {
                position: 'top',
                horizontalAlign: 'right'
            },
            xaxis: {
                type: 'datetime',
                categories: <?php echo json_encode(array_map(function($item) { return $item['date']; }, $verificationsByDate)); ?>,
            },
            yaxis: {
                title: {
                    text: 'Verifications'
                },
            },
            tooltip: {
                shared: true
            }
        };
        
        var timeSeriesChart = new ApexCharts(document.querySelector("#timeSeriesChart"), timeSeriesOptions);
        timeSeriesChart.render();
        <?php endif; ?>

        <?php if (count($verificationsByBrand) > 0): ?>
        // Brand Bar Chart
        var brandBarOptions = {
            series: [{
                name: 'Authentic',
                data: <?php echo json_encode(array_map(function($item) { return $item['authentic']; }, $verificationsByBrand)); ?>
            }, {
                name: 'Fake',
                data: <?php echo json_encode(array_map(function($item) { return $item['fake']; }, $verificationsByBrand)); ?>
            }],
            chart: {
                type: 'bar',
                height: 350,
                stacked: true,
                toolbar: {
                    show: false
                },
            },
            colors: ['#28a745', '#dc3545'],
            plotOptions: {
                bar: {
                    horizontal: false,
                    borderRadius: 5,
                    columnWidth: '70%',
                }
            },
            dataLabels: {
                enabled: false
            },
            legend: {
                position: 'top',
                horizontalAlign: 'right'
            },
            xaxis: {
                categories: <?php echo json_encode(array_map(function($item) { return $item['brand_name']; }, $verificationsByBrand)); ?>,
            },
            yaxis: {
                title: {
                    text: 'Verifications'
                }
            },
            fill: {
                opacity: 1
            },
            tooltip: {
                y: {
                    formatter: function (val) {
                        return val + " verifications"
                    }
                }
            }
        };
        
        var brandBarChart = new ApexCharts(document.querySelector("#brandBarChart"), brandBarOptions);
        brandBarChart.render();
        <?php endif; ?>

        <?php if (count($verificationsByCategory) > 0): ?>
        // Category Pie Chart
        var categoryPieOptions = {
            series: <?php echo json_encode(array_map(function($item) { return $item['total_verifications']; }, $verificationsByCategory)); ?>,
            chart: {
                type: 'pie',
                height: 350,
                toolbar: {
                    show: false
                },
            },
            labels: <?php echo json_encode(array_map(function($item) { return $item['category_name']; }, $verificationsByCategory)); ?>,
            colors: ['#1a76d2', '#28a745', '#ffc107', '#17a2b8', '#6c757d', '#dc3545', '#fd7e14', '#20c997', '#6610f2'],
            legend: {
                position: 'bottom'
            },
            responsive: [{
                breakpoint: 480,
                options: {
                    chart: {
                        width: 300
                    },
                    legend: {
                        position: 'bottom'
                    }
                }
            }],
            dataLabels: {
                formatter(val, opts) {
                    const name = opts.w.globals.labels[opts.seriesIndex]
                    return [name, Math.round(val) + '%']
                }
            }
        };
        
        var categoryPieChart = new ApexCharts(document.querySelector("#categoryPieChart"), categoryPieOptions);
        categoryPieChart.render();
        <?php endif; ?>

        <?php if ($totalStats['total_verifications'] > 0): ?>
        // Authentic vs Fake Donut Chart
        var authenticFakeOptions = {
            series: [<?php echo $totalStats['authentic_verifications']; ?>, <?php echo $totalStats['fake_verifications']; ?>],
            chart: {
                type: 'donut',
                height: 350,
                toolbar: {
                    show: false
                }
            },
            labels: ['Authentic', 'Fake'],
            colors: ['#28a745', '#dc3545'],
            plotOptions: {
                pie: {
                    donut: {
                        size: '65%',
                        labels: {
                            show: true,
                            total: {
                                show: true,
                                showAlways: true,
                                label: 'Total',
                                fontSize: '18px',
                                fontWeight: 600,
                                color: '#373d3f',
                                formatter: function (w) {
                                    return w.globals.seriesTotals.reduce((a, b) => {
                                        return a + b
                                    }, 0)
                                }
                            }
                        }
                    }
                }
            },
            dataLabels: {
                enabled: true,
                formatter: function (val, opts) {
                    return Math.round(val) + '%'
                }
            },
            legend: {
                position: 'bottom'
            }
        };
        
        var authenticFakeChart = new ApexCharts(document.querySelector("#authenticFakeChart"), authenticFakeOptions);
        authenticFakeChart.render();
        <?php endif; ?>
    </script>
</body>
</html>