<?php
session_start();

// Check if admin is logged in


// Admin info
$adminName = $_SESSION['admin_name'] ?? 'Admin User';

// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "authena";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get verification location data for map
$mapDataQuery = "SELECT 
                    location_lat, 
                    location_lng, 
                    location_address, 
                    COUNT(*) as scan_count,
                    status
                FROM verification_logs
                WHERE location_lat IS NOT NULL AND location_lng IS NOT NULL
                GROUP BY location_lat, location_lng, location_address, status
                ORDER BY scan_count DESC";

$result = $conn->query($mapDataQuery);
$mapData = array();

if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $mapData[] = $row;
    }
}

// Get top verification cities with count
$topCitiesQuery = "SELECT 
                    SUBSTRING_INDEX(location_address, ',', 1) as city,
                    SUBSTRING_INDEX(SUBSTRING_INDEX(location_address, ',', -1), ',', 1) as country,
                    COUNT(*) as total_scans
                FROM verification_logs
                WHERE location_address IS NOT NULL
                GROUP BY city, country
                ORDER BY total_scans DESC
                LIMIT 10";

$result = $conn->query($topCitiesQuery);
$topCities = array();

if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $topCities[] = $row;
    }
}

// Get verification stats by country
$countryStatsQuery = "SELECT 
                        TRIM(SUBSTRING_INDEX(location_address, ',', -1)) as country,
                        COUNT(*) as total_scans,
                        SUM(CASE WHEN status = 'authentic' THEN 1 ELSE 0 END) as authentic_count,
                        SUM(CASE WHEN status = 'fake' THEN 1 ELSE 0 END) as fake_count
                    FROM verification_logs
                    WHERE location_address IS NOT NULL
                    GROUP BY country
                    ORDER BY total_scans DESC
                    LIMIT 8";

$result = $conn->query($countryStatsQuery);
$countryStats = array();

if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $countryStats[] = $row;
    }
}

// Get recent verification locations
$recentLocationsQuery = "SELECT 
                        v.product_id,
                        p.name as product_name,
                        b.name as brand_name,
                        v.location_address,
                        v.status,
                        v.verification_timestamp
                    FROM verification_logs v
                    LEFT JOIN products p ON v.product_id = p.id
                    LEFT JOIN brands b ON p.brand_id = b.id
                    WHERE v.location_address IS NOT NULL
                    ORDER BY v.verification_timestamp DESC
                    LIMIT 5";

$result = $conn->query($recentLocationsQuery);
$recentLocations = array();

if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $recentLocations[] = $row;
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Authena Admin - Verification Map</title>
    <!-- Favicon -->
    <link rel="shortcut icon" href="assets/images/favicon.ico" type="image/x-icon">
    <!-- Font Awesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <!-- Leaflet CSS -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/leaflet.min.css">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="dash.css">
    <style>
        #map-container {
            height: 500px;
            width: 100%;
            border-radius: 12px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
            margin-bottom: 24px;
        }
        
        .map-filters {
            display: flex;
            gap: 16px;
            margin-bottom: 16px;
            flex-wrap: wrap;
        }
        
        .map-filter-btn {
            padding: 8px 16px;
            border-radius: 8px;
            border: none;
            background-color: #f3f4f6;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .map-filter-btn.active {
            background-color: #4f46e5;
            color: white;
        }
        
        .map-filter-btn:hover:not(.active) {
            background-color: #e5e7eb;
        }
        
        .stats-cards {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 24px;
            margin-bottom: 24px;
        }
        
        .stats-card {
            background-color: white;
            border-radius: 12px;
            padding: 20px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
        }
        
        .stats-card h3 {
            font-size: 18px;
            margin-bottom: 16px;
            color: #111827;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(120px, 1fr));
            gap: 16px;
        }
        
        .stats-item {
            background-color: #f9fafb;
            border-radius: 8px;
            padding: 12px;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            text-align: center;
        }
        
        .stats-item .value {
            font-size: 24px;
            font-weight: 600;
            color: #4f46e5;
            margin-bottom: 4px;
        }
        
        .stats-item .label {
            font-size: 14px;
            color: #6b7280;
        }
        
        .location-list {
            list-style-type: none;
            padding: 0;
            margin: 0;
        }
        
        .location-item {
            display: flex;
            justify-content: space-between;
            padding: 12px 0;
            border-bottom: 1px solid #f3f4f6;
        }
        
        .location-item:last-child {
            border-bottom: none;
        }
        
        .location-info {
            flex: 1;
        }
        
        .location-product {
            font-weight: 500;
            color: #111827;
            margin-bottom: 4px;
        }
        
        .location-address {
            font-size: 14px;
            color: #6b7280;
        }
        
        .location-status {
            padding: 4px 8px;
            border-radius: 6px;
            font-size: 12px;
            font-weight: 500;
        }
        
        .status-authentic {
            background-color: #d1fae5;
            color: #047857;
        }
        
        .status-fake {
            background-color: #fee2e2;
            color: #b91c1c;
        }
        
        .status-unknown {
            background-color: #f3f4f6;
            color: #6b7280;
        }
        
        .flex-between {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .location-time {
            font-size: 12px;
            color: #9ca3af;
            text-align: right;
        }
        
        .country-stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
            gap: 16px;
        }
        
        .country-stat-card {
            background-color: #f9fafb;
            border-radius: 8px;
            padding: 16px;
            position: relative;
            overflow: hidden;
        }
        
        .country-name {
            font-weight: 600;
            color: #111827;
            margin-bottom: 12px;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .scan-stat {
            display: flex;
            justify-content: space-between;
            margin-bottom: 8px;
        }
        
        .scan-value {
            font-weight: 500;
        }
        
        .total-scans {
            color: #4f46e5;
        }
        
        .authentic-scans {
            color: #047857;
        }
        
        .fake-scans {
            color: #b91c1c;
        }
        
        .leaflet-popup-content {
            margin: 12px;
            min-width: 200px;
        }
        
        .popup-title {
            font-weight: 600;
            margin-bottom: 8px;
            color: #111827;
        }
        
        .popup-detail {
            font-size: 13px;
            color: #6b7280;
            margin-bottom: 4px;
        }
        
        .popup-status {
            margin-top: 8px;
            font-weight: 600;
        }
        
        .popup-authentic {
            color: #047857;
        }
        
        .popup-fake {
            color: #b91c1c;
        }
        
        .popup-mixed {
            color: #9333ea;
        }
    </style>
</head>
<body>
    <div class="wrapper">
        <!-- Sidebar -->
        <aside class="sidebar">
            <div class="sidebar-brand">
                <i class="fas fa-shield-alt"></i>
                <h2>Authena<span>Admin</span></h2>
            </div>
            <div class="sidebar-menu">
                <div class="menu-category">Authentication</div>
                <ul>
                    <li><a href="admin-logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
                </ul>
                
                <div class="menu-category">Main</div>
                <ul>
                    <li><a href="admin-dashboard.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
                </ul>
                
                <div class="menu-category">Product Management</div>
                <ul>
                    <li><a href="admin-products.php"><i class="fas fa-boxes"></i> Products</a></li>
                    <li><a href="admin-add-product.php"><i class="fas fa-plus-circle"></i> Add Product</a></li>
                </ul>
                
                <div class="menu-category">Brand Management</div>
                <ul>
                    <li><a href="admin-brands.php"><i class="fas fa-copyright"></i> Brands</a></li>
                    <li><a href="admin-add-brand.php"><i class="fas fa-plus-square"></i> Add Brand</a></li>
                </ul>
                
                <div class="menu-category">User Management</div>
                <ul>
                    <li><a href="admin-users.php"><i class="fas fa-users"></i> Users</a></li>
                </ul>
                
                <div class="menu-category">Reports & Analytics</div>
                <ul>
                    <li><a href="admin-fake-reports.php"><i class="fas fa-flag"></i> Fake Reports</a></li>
                    <li><a href="admin-logs.php"><i class="fas fa-history"></i> Verification Logs</a></li>
                    <li><a href="admin-map.php" class="active"><i class="fas fa-map-marked-alt"></i> Scan Map</a></li>
                    <li><a href="admin-analytics.php"><i class="fas fa-chart-bar"></i> Analytics</a></li>
                </ul>
            </div>
        </aside>

        <!-- Main Content -->
        <div class="main-content">
            <!-- Header -->
            <header class="header">
                <button class="toggle-menu" id="toggleMenu">
                    <i class="fas fa-bars"></i>
                </button>
                <div class="search-container">
                    <i class="fas fa-search"></i>
                    <input type="text" placeholder="Search...">
                </div>
                <div class="user-actions">
              
                    <div class="user-profile">
                        <img src="https://randomuser.me/api/portraits/men/1.jpg" alt="Admin Profile">
                        <div>
                            <h4><?php echo htmlspecialchars($adminName); ?></h4>
                            <small>Admin</small>
                        </div>
                    </div>
                </div>
            </header>

            <!-- Map Content -->
            <div class="dashboard">
                <div class="dashboard-header">
                    <h1>Verification Map</h1>
                    <div class="breadcrumb">
                        <a href="admin-dashboard.php">Home</a>
                        <span>/</span>
                        <a href="admin-map.php">Verification Map</a>
                    </div>
                </div>

                <div class="map-filters">
                    <button class="map-filter-btn active" data-filter="all">
                        <i class="fas fa-globe"></i> All Verifications
                    </button>
                    <button class="map-filter-btn" data-filter="authentic">
                        <i class="fas fa-check-circle"></i> Authentic Products
                    </button>
                    <button class="map-filter-btn" data-filter="fake">
                        <i class="fas fa-times-circle"></i> Counterfeit Products
                    </button>
                </div>

                <div id="map-container"></div>

                <div class="stats-cards">
                    <div class="stats-card">
                        <h3><i class="fas fa-chart-pie"></i> Top Verification Cities</h3>
                        <div class="stats-grid">
                            <?php foreach ($topCities as $index => $city): ?>
                                <?php if ($index < 6): ?>
                                <div class="stats-item">
                                    <div class="value"><?php echo number_format($city['total_scans']); ?></div>
                                    <div class="label"><?php echo htmlspecialchars($city['city']); ?></div>
                                </div>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <div class="stats-card">
                        <h3><i class="fas fa-clock"></i> Recent Verifications</h3>
                        <ul class="location-list">
                            <?php foreach ($recentLocations as $location): ?>
                                <li class="location-item">
                                    <div class="location-info">
                                        <div class="location-product"><?php echo htmlspecialchars($location['product_name']); ?> - <?php echo htmlspecialchars($location['brand_name']); ?></div>
                                        <div class="location-address"><?php echo htmlspecialchars($location['location_address']); ?></div>
                                    </div>
                                    <div class="flex-between">
                                        <span class="location-status status-<?php echo strtolower($location['status']); ?>"><?php echo ucfirst($location['status']); ?></span>
                                        <div class="location-time">
                                            <?php 
                                                $timestamp = strtotime($location['verification_timestamp']);
                                                $timeAgo = time() - $timestamp;
                                                
                                                if ($timeAgo < 60) {
                                                    echo "Just now";
                                                } elseif ($timeAgo < 3600) {
                                                    echo floor($timeAgo / 60) . " mins ago";
                                                } elseif ($timeAgo < 86400) {
                                                    echo floor($timeAgo / 3600) . " hours ago";
                                                } else {
                                                    echo floor($timeAgo / 86400) . " days ago";
                                                }
                                            ?>
                                        </div>
                                    </div>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                </div>

                <div class="stats-card">
                    <h3><i class="fas fa-flag"></i> Verification Statistics by Country</h3>
                    <div class="country-stats-grid">
                        <?php foreach ($countryStats as $country): ?>
                            <div class="country-stat-card">
                                <div class="country-name">
                                    <?php echo htmlspecialchars($country['country']); ?>
                                </div>
                                <div class="scan-stat">
                                    <span>Total Scans:</span>
                                    <span class="scan-value total-scans"><?php echo number_format($country['total_scans']); ?></span>
                                </div>
                                <div class="scan-stat">
                                    <span>Authentic:</span>
                                    <span class="scan-value authentic-scans"><?php echo number_format($country['authentic_count']); ?></span>
                                </div>
                                <div class="scan-stat">
                                    <span>Counterfeit:</span>
                                    <span class="scan-value fake-scans"><?php echo number_format($country['fake_count']); ?></span>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/leaflet.min.js"></script>
    <script>
        // Toggle sidebar
        document.getElementById('toggleMenu').addEventListener('click', function() {
            document.querySelector('.sidebar').classList.toggle('active');
        });

        // Initialize the map
        const map = L.map('map-container').setView([20, 0], 2);

        // Add the tile layer (OpenStreetMap)
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors',
            maxZoom: 18
        }).addTo(map);

        // Map data
        const mapData = <?php echo json_encode($mapData); ?>;
        
        // Marker groups
        const allMarkers = L.layerGroup().addTo(map);
        const authenticMarkers = L.layerGroup();
        const fakeMarkers = L.layerGroup();

        // Add markers to the map
        mapData.forEach(location => {
            const lat = parseFloat(location.location_lat);
            const lng = parseFloat(location.location_lng);
            
            if (!isNaN(lat) && !isNaN(lng)) {
                let markerIcon, markerColor;
                
                if (location.status === 'authentic') {
                    markerColor = '#047857'; // Green
                } else if (location.status === 'fake') {
                    markerColor = '#b91c1c'; // Red
                } else {
                    markerColor = '#6b7280'; // Gray
                }
                
                // Custom marker icon
                markerIcon = L.divIcon({
                    className: 'custom-div-icon',
                    html: `<div style="background-color: ${markerColor}; width: 12px; height: 12px; border-radius: 50%; border: 2px solid #ffffff; box-shadow: 0 0 4px rgba(0,0,0,0.3);"></div>`,
                    iconSize: [12, 12],
                    iconAnchor: [6, 6]
                });
                
                // Create marker
                const marker = L.marker([lat, lng], {icon: markerIcon});
                
                // Add popup
                marker.bindPopup(`
                    <div class="popup-content">
                        <div class="popup-title">${location.location_address}</div>
                        <div class="popup-detail">Scans: ${location.scan_count}</div>
                        <div class="popup-status popup-${location.status.toLowerCase()}">Status: ${location.status.charAt(0).toUpperCase() + location.status.slice(1)}</div>
                    </div>
                `);
                
                // Add to appropriate layer groups
                allMarkers.addLayer(marker);
                
                if (location.status === 'authentic') {
                    authenticMarkers.addLayer(marker);
                } else if (location.status === 'fake') {
                    fakeMarkers.addLayer(marker);
                }
            }
        });

        // Filter button handling
        const filterButtons = document.querySelectorAll('.map-filter-btn');
        
        filterButtons.forEach(button => {
            button.addEventListener('click', function() {
                // Remove active class from all buttons
                filterButtons.forEach(btn => btn.classList.remove('active'));
                
                // Add active class to clicked button
                this.classList.add('active');
                
                // Get filter type
                const filterType = this.getAttribute('data-filter');
                
                // Remove all marker layers
                map.removeLayer(allMarkers);
                map.removeLayer(authenticMarkers);
                map.removeLayer(fakeMarkers);
                
                // Add appropriate layer
                if (filterType === 'all') {
                    map.addLayer(allMarkers);
                } else if (filterType === 'authentic') {
                    map.addLayer(authenticMarkers);
                } else if (filterType === 'fake') {
                    map.addLayer(fakeMarkers);
                }
            });
        });

        // Notification dropdown functionality
        document.querySelector('.notifications').addEventListener('click', function() {
            // Here you would toggle a notification dropdown
            console.log('Notification clicked');
        });
    </script>
</body>
</html>