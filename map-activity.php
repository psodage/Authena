<?php
// Start session
session_start();

// Database connection
$servername = "127.0.0.1";
$username = "root"; // Typically the default username for XAMPP
$password = ""; // Default password is empty for XAMPP
$dbname = "authena";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get verification statistics
$stats = [
    'total' => 0,
    'authentic' => 0,
    'fake' => 0,
    'countries' => 0
];

// Total verifications today
$sql = "SELECT COUNT(*) as total FROM verification_logs 
        WHERE DATE(verification_timestamp) = CURDATE()";
$result = $conn->query($sql);
if ($result && $result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $stats['total'] = $row['total'];
}

// Authentic verifications today
$sql = "SELECT COUNT(*) as authentic FROM verification_logs 
        WHERE DATE(verification_timestamp) = CURDATE() 
        AND status = 'authentic'";
$result = $conn->query($sql);
if ($result && $result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $stats['authentic'] = $row['authentic'];
}

// Fake verifications today
$sql = "SELECT COUNT(*) as fake FROM verification_logs 
        WHERE DATE(verification_timestamp) = CURDATE() 
        AND status = 'fake'";
$result = $conn->query($sql);
if ($result && $result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $stats['fake'] = $row['fake'];
}

// Count unique countries (this is simplified as we may not have country data readily available)
// In a real scenario, you'd use a proper geolocation service to convert coordinates to countries
$sql = "SELECT COUNT(DISTINCT location_address) as countries FROM verification_logs 
        WHERE location_address IS NOT NULL";
$result = $conn->query($sql);
if ($result && $result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $stats['countries'] = $row['countries'];
}

// Get recent verification logs for the map and activity feed
$sql = "SELECT v.*, p.name as product_name, u.username 
        FROM verification_logs v
        LEFT JOIN products p ON v.product_id = p.id
        LEFT JOIN users u ON v.user_id = u.id
        ORDER BY v.verification_timestamp DESC
        LIMIT 50";
$verifications = [];
$result = $conn->query($sql);
if ($result && $result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $verifications[] = $row;
    }
}

// Get counterfeit hotspots
$sql = "SELECT 
            location_address, 
            COUNT(*) as total_scans,
            SUM(CASE WHEN status = 'fake' THEN 1 ELSE 0 END) as fake_count,
            (SUM(CASE WHEN status = 'fake' THEN 1 ELSE 0 END) / COUNT(*)) * 100 as fake_percentage
        FROM verification_logs
        WHERE location_address IS NOT NULL
        GROUP BY location_address
        HAVING COUNT(*) > 5
        ORDER BY fake_percentage DESC
        LIMIT 5";
$hotspots = [];
$result = $conn->query($sql);
if ($result && $result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        // Determine risk level based on fake percentage
        if($row['fake_percentage'] > 30) {
            $row['risk_level'] = 'High';
        } elseif($row['fake_percentage'] > 15) {
            $row['risk_level'] = 'Medium';
        } else {
            $row['risk_level'] = 'Low';
        }
        $hotspots[] = $row;
    }
}

// Convert verification data to JSON for JavaScript
$map_data = [];
foreach($verifications as $verification) {
    if(!empty($verification['location_lat']) && !empty($verification['location_lng'])) {
        $map_data[] = [
            'lat' => (float)$verification['location_lat'],
            'lng' => (float)$verification['location_lng'],
            'type' => $verification['status'],
            'product' => $verification['product_name'] ?? 'Unknown Product',
            'location' => $verification['location_address'] ?? 'Unknown Location',
            'timestamp' => $verification['verification_timestamp']
        ];
    }
}
$map_data_json = json_encode($map_data);

// Most recent activity data for feed
$recent_activity = array_slice($verifications, 0, 7);

// Check if user is logged in
$loggedIn = isset($_SESSION['user_id']);

?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Authena - Live Scan Map</title>
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
    <link rel="stylesheet" href="map.css">
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
                        <h2>Live Scan Map</h2>
                        <ul class="bread-list">
                            <li><a href="index.php">Home</a></li>
                            <li><i class="icofont-simple-right"></i></li>
                            <li class="active">Live Scan Map</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <section class="section">
        <div class="container">
            <div class="section-title">
                <h2>Global Product Authentication Activity</h2>
                <p>Watch real-time product verification scans happening around the world. See how Authena is fighting counterfeits globally.</p>
            </div>

            <!-- Time Controls -->
            <div class="time-controls">
                <div class="time-btn active" data-period="live">Live</div>
                <div class="time-btn" data-period="24h">Last 24h</div>
                <div class="time-btn" data-period="week">Last Week</div>
                <div class="time-btn" data-period="month">Last Month</div>
            </div>

            <!-- Stats Cards -->
            <div class="row">
                <div class="col-lg-3 col-md-6">
                    <div class="stats-card total">
                        <div class="icon">
                            <i class="icofont-globe"></i>
                        </div>
                        <div class="number"><?php echo number_format($stats['total']); ?></div>
                        <div class="label">Total Scans Today</div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6">
                    <div class="stats-card authentic">
                        <div class="icon">
                            <i class="icofont-check-circled"></i>
                        </div>
                        <div class="number"><?php echo number_format($stats['authentic']); ?></div>
                        <div class="label">Authentic Products</div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6">
                    <div class="stats-card fake">
                        <div class="icon">
                            <i class="icofont-close-circled"></i>
                        </div>
                        <div class="number"><?php echo number_format($stats['fake']); ?></div>
                        <div class="label">Counterfeit Products</div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6">
                    <div class="stats-card">
                        <div class="icon">
                            <i class="icofont-location-pin"></i>
                        </div>
                        <div class="number"><?php echo number_format($stats['countries']); ?></div>
                        <div class="label">Countries Active</div>
                    </div>
                </div>
            </div>

            <div class="row">
                <!-- Map Section -->
                <div class="col-lg-8 col-md-7">
                    <div id="map-container">
                        <div class="world-map">
                            <!-- Scan points will be added dynamically via JavaScript -->
                        </div>
                        
                        <!-- Map Legend -->
                        <div class="map-legend">
                            <div class="legend-item">
                                <div class="legend-color authentic"></div>
                                <span>Authentic Product</span>
                            </div>
                            <div class="legend-item">
                                <div class="legend-color fake"></div>
                                <span>Counterfeit Product</span>
                            </div>
                            <div class="legend-item">
                                <div class="legend-color unknown"></div>
                                <span>Scan in Progress</span>
                            </div>
                        </div>
                        
                        <!-- Heat Map Toggle -->
                        <div class="heatmap-toggle">
                            <label class="toggle-switch">
                                <input type="checkbox" id="heatmapToggle">
                                <span class="slider"></span>
                            </label>
                            <span class="toggle-label">Heat Map View</span>
                        </div>
                    </div>
                </div>
                
                <!-- Activity Feed -->
                <div class="col-lg-4 col-md-5">
                    <h4 class="mb-3">Live Activity Feed</h4>
                    <div class="activity-feed" id="activity-feed">
                        <?php foreach($recent_activity as $activity): ?>
                            <?php 
                                // Format timestamp
                                $timestamp = strtotime($activity['verification_timestamp']);
                                $now = time();
                                $diff = $now - $timestamp;
                                
                                if($diff < 60) {
                                    $time_display = "Just now";
                                } elseif($diff < 3600) {
                                    $time_display = floor($diff / 60) . " minutes ago";
                                } elseif($diff < 86400) {
                                    $time_display = floor($diff / 3600) . " hours ago";
                                } else {
                                    $time_display = floor($diff / 86400) . " days ago";
                                }
                            ?>
                            <div class="activity-item <?php echo $activity['status']; ?>">
                                <div class="product"><?php echo htmlspecialchars($activity['product_name'] ?? 'Unknown Product'); ?></div>
                                <div class="location"><?php echo htmlspecialchars($activity['location_address'] ?? 'Unknown Location'); ?></div>
                                <div class="status <?php echo $activity['status']; ?>">
                                    <?php if($activity['status'] == 'authentic'): ?>✓ Authentic
                                    <?php elseif($activity['status'] == 'fake'): ?>✗ Counterfeit
                                    <?php else: ?>? Unknown
                                    <?php endif; ?>
                                </div>
                                <div class="time"><?php echo $time_display; ?></div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
            
            <!-- Top Counterfeit Hotspots -->
            <div class="row mt-5">
                <div class="col-12">
                    <h3 class="mb-4">Top Counterfeit Hotspots</h3>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead class="thead-dark">
                                <tr>
                                    <th>Rank</th>
                                    <th>Location</th>
                                    <th>Total Scans</th>
                                    <th>Counterfeit %</th>
                                    <th>Risk Level</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $rank = 1; ?>
                                <?php foreach($hotspots as $hotspot): ?>
                                    <tr>
                                        <td><?php echo $rank++; ?></td>
                                        <td><?php echo htmlspecialchars($hotspot['location_address']); ?></td>
                                        <td><?php echo number_format($hotspot['total_scans']); ?></td>
                                        <td><?php echo number_format($hotspot['fake_percentage'], 1); ?>%</td>
                                        <td>
                                            <?php if($hotspot['risk_level'] == 'High'): ?>
                                                <span class="badge bg-danger">High</span>
                                            <?php elseif($hotspot['risk_level'] == 'Medium'): ?>
                                                <span class="badge bg-warning text-dark">Medium</span>
                                            <?php else: ?>
                                                <span class="badge bg-success">Low</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <a href="hotspot-details.php?location=<?php echo urlencode($hotspot['location_address']); ?>" class="btn btn-sm btn-primary">View Details</a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                                
                                <?php if(empty($hotspots)): ?>
                                    <tr>
                                        <td colspan="6" class="text-center">No hotspot data available</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <!-- End Live Scan Map Section -->

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
    <!-- Bootstrap JS -->
    <script src="js/bootstrap.min.js"></script>
    <!-- Main JS -->
    <script src="js/main.js"></script>
    
    <!-- Live Scan Map JavaScript -->
    <script>
        // Map data from the database
        const mapData = <?php echo $map_data_json; ?>;
        
        // Function to create a scan point animation on the map
        function createScanPoint(lat, lng, type) {
            // Convert latitude and longitude to relative positions on the map
            // This is a simplification - in a real application, you would use proper map projection
            const mapWidth = document.querySelector('.world-map').offsetWidth;
            const mapHeight = document.querySelector('.world-map').offsetHeight;
            
            // Simple conversion for demo purposes (not accurate for a real map)
            const x = (lng + 180) * (mapWidth / 360);
            const y = (90 - lat) * (mapHeight / 180);
            
            // Create the scan point element
            const scanPoint = document.createElement('div');
            scanPoint.classList.add('scan-point');
            scanPoint.classList.add(type);
            scanPoint.style.left = `${x}px`;
            scanPoint.style.top = `${y}px`;
            
            // Add to the map
            document.querySelector('.world-map').appendChild(scanPoint);
            
            // Remove after animation completes
            setTimeout(() => {
                scanPoint.remove();
            }, 5000);
        }

        // Function to add a new activity item to the feed
        function addActivityItem(product, location, status, timeAgo) {
            const activityFeed = document.getElementById('activity-feed');
            
            // Create activity item
            const activityItem = document.createElement('div');
            activityItem.classList.add('activity-item');
            activityItem.classList.add(status);
            
            // Add content
            activityItem.innerHTML = `
                <div class="product">${product}</div>
                <div class="location">${location}</div>
                <div class="status ${status}">${status === 'authentic' ? '✓ Authentic' : status === 'fake' ? '✗ Counterfeit' : '? Unknown'}</div>
                <div class="time">${timeAgo}</div>
            `;
            
            // Add to the top of the feed
            activityFeed.insertBefore(activityItem, activityFeed.firstChild);
            
            // Remove oldest item if there are too many
            if (activityFeed.children.length > 10) {
                activityFeed.removeChild(activityFeed.lastChild);
            }
        }

        // Function to simulate new scans using the existing data
        function simulateNewScan() {
            if (mapData.length > 0) {
                // Pick a random entry from map data
                const randomIndex = Math.floor(Math.random() * mapData.length);
                const scanData = mapData[randomIndex];
                
                // Create scan point on map
                createScanPoint(scanData.lat, scanData.lng, scanData.type);
                
                // Add to activity feed
                addActivityItem(
                    scanData.product,
                    scanData.location,
                    scanData.type,
                    'Just now'
                );
                
                // Update counters (simulated for demo)
                const totalScans = document.querySelector('.stats-card.total .number');
                totalScans.textContent = (parseInt(totalScans.textContent.replace(/,/g, '')) + 1).toLocaleString();
                
                if (scanData.type === 'authentic') {
                    const authenticCount = document.querySelector('.stats-card.authentic .number');
                    authenticCount.textContent = (parseInt(authenticCount.textContent.replace(/,/g, '')) + 1).toLocaleString();
                } else if (scanData.type === 'fake') {
                    const fakeCount = document.querySelector('.stats-card.fake .number');
                    fakeCount.textContent = (parseInt(fakeCount.textContent.replace(/,/g, '')) + 1).toLocaleString();
                }
            }
        }

        // Function to load data for different time periods
        function loadDataForPeriod(period) {
            // In a real application, this would make an AJAX call to get data for the selected period
            // For now, we'll just show a notification
            if (period !== 'live') {
                $.ajax({
                    url: 'get_map_data.php',
                    method: 'GET',
                    data: { period: period },
                    dataType: 'json',
                    success: function(response) {
                        if (response.success) {
                            // Update stats
                            document.querySelector('.stats-card.total .number').textContent = response.stats.total.toLocaleString();
                            document.querySelector('.stats-card.authentic .number').textContent = response.stats.authentic.toLocaleString();
                            document.querySelector('.stats-card.fake .number').textContent = response.stats.fake.toLocaleString();
                            document.querySelector('.stats-card.total .number').textContent = response.stats.total.toLocaleString();
                            document.querySelector('.stats-card.authentic .number').textContent = response.stats.authentic.toLocaleString();
                            document.querySelector('.stats-card.fake .number').textContent = response.stats.fake.toLocaleString();
                            
                            // Update activity feed
                            const activityFeed = document.getElementById('activity-feed');
                            activityFeed.innerHTML = '';
                            
                            response.activities.forEach(activity => {
                                const activityItem = document.createElement('div');
                                activityItem.classList.add('activity-item');
                                activityItem.classList.add(activity.status);
                                
                                activityItem.innerHTML = `
                                    <div class="product">${activity.product_name}</div>
                                    <div class="location">${activity.location_address}</div>
                                    <div class="status ${activity.status}">${activity.status === 'authentic' ? '✓ Authentic' : activity.status === 'fake' ? '✗ Counterfeit' : '? Unknown'}</div>
                                    <div class="time">${activity.time_display}</div>
                                `;
                                
                                activityFeed.appendChild(activityItem);
                            });
                            
                            // Update map data
                            mapData.length = 0;
                            response.map_data.forEach(item => {
                                mapData.push(item);
                            });
                            
                            // Show all points on map
                            clearAllMapPoints();
                            showAllMapPoints();
                        }
                    },
                    error: function() {
                        alert('Failed to load data for the selected time period');
                    }
                });
            }
        }
        
        // Function to clear all map points
        function clearAllMapPoints() {
            const mapContainer = document.querySelector('.world-map');
            const points = mapContainer.querySelectorAll('.scan-point');
            points.forEach(point => point.remove());
        }
        
        // Function to show all map points for historical data
        function showAllMapPoints() {
            mapData.forEach(data => {
                createScanPoint(data.lat, data.lng, data.type);
            });
        }

        // Initialize the map
        document.addEventListener('DOMContentLoaded', function() {
            // Simulate initial scan data
            for (let i = 0; i < 10; i++) {
                setTimeout(() => {
                    simulateNewScan();
                }, i * 500);
            }
            
            // Set up interval for new scans
            setInterval(simulateNewScan, 5000);
            
            // Handle time period buttons
            document.querySelectorAll('.time-btn').forEach(btn => {
                btn.addEventListener('click', function() {
                    document.querySelectorAll('.time-btn').forEach(b => b.classList.remove('active'));
                    this.classList.add('active');
                    loadDataForPeriod(this.getAttribute('data-period'));
                });
            });
            
            // Handle heatmap toggle
            document.getElementById('heatmapToggle').addEventListener('change', function() {
                const worldMap = document.querySelector('.world-map');
                if (this.checked) {
                    worldMap.classList.add('heatmap-mode');
                } else {
                    worldMap.classList.remove('heatmap-mode');
                }
            });
        });
    </script>
</body>
</html>