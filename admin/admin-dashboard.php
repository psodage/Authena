<?php
session_start();


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

// Fetch admin info

$adminQuery = "SELECT first_name, last_name FROM admins WHERE id = ?";
$stmt = $conn->prepare($adminQuery);
$stmt->bind_param("i", $adminId);
$stmt->execute();
$adminResult = $stmt->get_result();
$adminData = $adminResult->fetch_assoc();


// Fetch dashboard stats
$totalProductsQuery = "SELECT COUNT(*) as count FROM products";
$totalProducts = $conn->query($totalProductsQuery)->fetch_assoc()['count'];

$totalBrandsQuery = "SELECT COUNT(*) as count FROM brands";
$totalBrands = $conn->query($totalBrandsQuery)->fetch_assoc()['count'];

$totalUsersQuery = "SELECT COUNT(*) as count FROM users";
$totalUsers = $conn->query($totalUsersQuery)->fetch_assoc()['count'];

$totalScansQuery = "SELECT COUNT(*) as count FROM verification_logs";
$totalScans = $conn->query($totalScansQuery)->fetch_assoc()['count'];

$fakeReportsQuery = "SELECT COUNT(*) as count FROM fake_reports";
$fakeReports = $conn->query($fakeReportsQuery)->fetch_assoc()['count'];

$pendingReportsQuery = "SELECT COUNT(*) as count FROM fake_reports WHERE status = 'pending'";
$pendingReports = $conn->query($pendingReportsQuery)->fetch_assoc()['count'];

// Recent verification data
$recentVerificationsQuery = "
    SELECT vl.id, p.name as product, b.name as brand, u.email as user, 
           vl.location_address as location, vl.status, 
           CASE 
               WHEN TIMESTAMPDIFF(MINUTE, vl.verification_timestamp, NOW()) < 60 
                   THEN CONCAT(TIMESTAMPDIFF(MINUTE, vl.verification_timestamp, NOW()), ' minutes ago')
               WHEN TIMESTAMPDIFF(HOUR, vl.verification_timestamp, NOW()) < 24 
                   THEN CONCAT(TIMESTAMPDIFF(HOUR, vl.verification_timestamp, NOW()), ' hours ago')
               ELSE DATE_FORMAT(vl.verification_timestamp, '%M %d, %Y')
           END as time
    FROM verification_logs vl
    LEFT JOIN products p ON vl.product_id = p.id
    LEFT JOIN users u ON vl.user_id = u.id
    LEFT JOIN brands b ON p.brand_id = b.id
    ORDER BY vl.verification_timestamp DESC
    LIMIT 5
";
$recentVerifications = $conn->query($recentVerificationsQuery)->fetch_all(MYSQLI_ASSOC);

// Fake report data
$recentReportsQuery = "
    SELECT 
        CONCAT('FR', fr.id) as id, 
        p.name as product, 
        b.name as brand, 
        u.email as reporter, 
        fr.location_address as location, 
        CASE
            WHEN fr.status = 'pending' OR fr.status = 'investigating' THEN 'Under Investigation'
            WHEN fr.status = 'resolved' THEN 'Confirmed Fake'
            ELSE fr.status
        END as status,
        CASE 
            WHEN TIMESTAMPDIFF(MINUTE, fr.created_at, NOW()) < 60 
                THEN CONCAT(TIMESTAMPDIFF(MINUTE, fr.created_at, NOW()), ' minutes ago')
            WHEN TIMESTAMPDIFF(HOUR, fr.created_at, NOW()) < 24 
                THEN CONCAT(TIMESTAMPDIFF(HOUR, fr.created_at, NOW()), ' hours ago')
            ELSE DATE_FORMAT(fr.created_at, '%M %d, %Y')
        END as time
    FROM fake_reports fr
    LEFT JOIN products p ON fr.product_id = p.id
    LEFT JOIN users u ON fr.user_id = u.id
    LEFT JOIN brands b ON p.brand_id = b.id
    ORDER BY fr.created_at DESC
    LIMIT 3
";
$recentReports = $conn->query($recentReportsQuery)->fetch_all(MYSQLI_ASSOC);

// Get monthly scan data for chart
$monthlyScanQuery = "
    SELECT 
        MONTH(verification_timestamp) as month,
        COUNT(*) as count
    FROM verification_logs
    WHERE verification_timestamp >= DATE_SUB(NOW(), INTERVAL 1 YEAR)
    GROUP BY MONTH(verification_timestamp)
    ORDER BY MONTH(verification_timestamp)
";
$monthlyScanResult = $conn->query($monthlyScanQuery);
$monthlyScans = [];
$monthNames = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];

// Initialize array with zeros
for ($i = 0; $i < 12; $i++) {
    $monthlyScans[$i] = 0;
}

// Fill in actual data
while ($row = $monthlyScanResult->fetch_assoc()) {
    $monthIndex = $row['month'] - 1; // Adjust for 0-indexed array
    $monthlyScans[$monthIndex] = (int)$row['count'];
}

// Get top verification locations
$topLocationsQuery = "
    SELECT 
        SUBSTRING_INDEX(location_address, ',', 1) as city,
        TRIM(SUBSTRING_INDEX(location_address, ',', -1)) as country,
        COUNT(*) as count
    FROM verification_logs
    WHERE location_address IS NOT NULL
    GROUP BY city, country
    ORDER BY count DESC
    LIMIT 5
";
$topLocations = $conn->query($topLocationsQuery)->fetch_all(MYSQLI_ASSOC);

// Close the database connection
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Authena Admin - Dashboard</title>
    <!-- Favicon -->
    <link rel="shortcut icon" href="assets/images/favicon.ico" type="image/x-icon">
    <!-- Font Awesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <!-- Chart.js -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/3.9.1/chart.min.js"></script>
    <!-- Custom CSS -->
   <link rel="stylesheet" href="dash.css">
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
                    <li><a href="admin-dashboard.php" class="active"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
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
                    <li><a href="admin-map.php"><i class="fas fa-map-marked-alt"></i> Scan Map</a></li>
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
                            <h4>Admin</h4>
                          
                        </div>
                    </div>
                </div>
            </header>

            <!-- Dashboard Content -->
            <div class="dashboard">
                <div class="dashboard-header">
                    <h1>Dashboard</h1>
                    <div class="breadcrumb">
                        <a href="admin-dashboard.php">Home</a>
                        <span>/</span>
                        <a href="admin-dashboard.php">Dashboard</a>
                    </div>
                </div>

                <!-- Stats Cards -->
                <div class="stats-grid">
                    <div class="stat-card">
                        <div class="stat-icon bg-primary">
                            <i class="fas fa-boxes"></i>
                        </div>
                        <div class="stat-info">
                            <h3><?php echo number_format($totalProducts); ?></h3>
                            <p>Total Products</p>
                        </div>
                    </div>
                    
                    <div class="stat-card">
                        <div class="stat-icon bg-success">
                            <i class="fas fa-copyright"></i>
                        </div>
                        <div class="stat-info">
                            <h3><?php echo number_format($totalBrands); ?></h3>
                            <p>Registered Brands</p>
                        </div>
                    </div>
                    
                    <div class="stat-card">
                        <div class="stat-icon bg-info">
                            <i class="fas fa-users"></i>
                        </div>
                        <div class="stat-info">
                            <h3><?php echo number_format($totalUsers); ?></h3>
                            <p>Total Users</p>
                        </div>
                    </div>
                    
                    <div class="stat-card">
                        <div class="stat-icon bg-purple">
                            <i class="fas fa-qrcode"></i>
                        </div>
                        <div class="stat-info">
                            <h3><?php echo number_format($totalScans); ?></h3>
                            <p>Product Scans</p>
                        </div>
                    </div>
                </div>

                <!-- Chart and Fake Reports -->
                <div class="grid-2">
                    <div class="card">
                        <div class="card-header">
                            <h2>Monthly Scan Activity</h2>
                            
                        </div>
                        <div class="card-body">
                            <div class="chart-container">
                                <canvas id="scanChart"></canvas>
                            </div>
                        </div>
                    </div>
                    
                    <div class="card">
                        <div class="card-header">
                            <h2>Fake Reports</h2>
                           >
                        </div>
                        <div class="card-body">
                            <div class="fake-reports-summary">
                                <div class="stat-card" style="margin-bottom: 1rem;">
                                    <div class="stat-icon bg-danger">
                                        <i class="fas fa-exclamation-triangle"></i>
                                    </div>
                                    <div class="stat-info">
                                        <h3><?php echo number_format($fakeReports); ?></h3>
                                        <p>Total Reports</p>
                                    </div>
                                </div>
                                <div class="stat-card">
                                    <div class="stat-icon bg-warning">
                                        <i class="fas fa-hourglass-half"></i>
                                    </div>
                                    <div class="stat-info">
                                        <h3><?php echo number_format($pendingReports); ?></h3>
                                        <p>Pending Investigation</p>
                                    </div>
                                </div>
                            </div>
                            <div style="margin-top: 1rem;">
                                <h3 style="font-size: 1rem; margin-bottom: 0.5rem;">Recent Reports</h3>
                                <table class="table">
                                    <thead>
                                        <tr>
                                            <th>ID</th>
                                            <th>Product</th>
                                            <th>Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($recentReports as $report): ?>
                                        <tr>
                                            <td><a href="admin-fake-report-details.php?id=<?php echo $report['id']; ?>" style="color: var(--primary); text-decoration: none; font-weight: 500;"><?php echo $report['id']; ?></a></td>
                                            <td><?php echo htmlspecialchars($report['product']); ?></td>
                                            <td>
                                                <?php if ($report['status'] == 'Under Investigation'): ?>
                                                <span class="status status-investigation">Under Investigation</span>
                                                <?php elseif ($report['status'] == 'Confirmed Fake'): ?>
                                                <span class="status status-confirmed">Confirmed Fake</span>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Recent Verifications and Locations -->
                <div class="grid-2">
                    <div class="card">
                        <div class="card-header">
                            <h2>Recent Verifications</h2>
                            
                        </div>
                        <div class="card-body">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Product</th>
                                        <th>Brand</th>
                                        <th>User</th>
                                        <th>Status</th>
                                        <th>Time</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($recentVerifications as $verification): ?>
                                    <tr>
                                        <td><a href="admin-log-details.php?id=<?php echo $verification['id']; ?>" style="color: var(--primary); text-decoration: none; font-weight: 500;">VS<?php echo $verification['id']; ?></a></td>
                                        <td><?php echo htmlspecialchars($verification['product']); ?></td>
                                        <td><?php echo htmlspecialchars($verification['brand']); ?></td>
                                        <td><?php echo htmlspecialchars($verification['user']); ?></td>
                                        <td>
                                            <?php if ($verification['status'] == 'authentic'): ?>
                                            <span class="status status-authentic">Authentic</span>
                                            <?php elseif ($verification['status'] == 'fake'): ?>
                                            <span class="status status-counterfeit">Counterfeit</span>
                                            <?php endif; ?>
                                        </td>
                                        <td><?php echo $verification['time']; ?></td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    
                    <div class="card">
                        <div class="card-header">
                            <h2>Top Verification Locations</h2>
                           
                        </div>
                        <div class="card-body">
                            <?php foreach ($topLocations as $location): ?>
                            <div class="location-item">
                                <div class="location-info">
                                    <div class="location-icon">
                                        <i class="fas fa-map-marker-alt"></i>
                                    </div>
                                    <div>
                                        <div class="location-name"><?php echo htmlspecialchars($location['city']); ?></div>
                                        <div class="location-country"><?php echo htmlspecialchars($location['country']); ?></div>
                                    </div>
                                </div>
                                <div class="location-count"><?php echo number_format($location['count']); ?></div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script>
        // Toggle sidebar
        document.getElementById('toggleMenu').addEventListener('click', function() {
            document.querySelector('.sidebar').classList.toggle('active');
        });

        // Monthly Scan Chart
        const ctx = document.getElementById('scanChart').getContext('2d');
        const scanChart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: <?php echo json_encode($monthNames); ?>,
                datasets: [{
                    label: 'Product Scans',
                    data: <?php echo json_encode(array_values($monthlyScans)); ?>,
                    backgroundColor: 'rgba(79, 70, 229, 0.1)',
                    borderColor: '#4f46e5',
                    borderWidth: 2,
                    tension: 0.4,
                    pointBackgroundColor: '#ffffff',
                    pointBorderColor: '#4f46e5',
                    pointBorderWidth: 2,
                    pointRadius: 4,
                    pointHoverRadius: 6
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        backgroundColor: '#1f2937',
                        titleColor: '#ffffff',
                        bodyColor: '#e5e7eb',
                        bodySpacing: 5,
                        padding: 12,
                        boxPadding: 5,
                        usePointStyle: true,
                        callbacks: {
                            title: (context) => context[0].label,
                            label: (context) => `Scans: ${context.raw.toLocaleString()}`
                        }
                    }
                },
                scales: {
                    x: {
                        grid: {
                            display: false
                        },
                        ticks: {
                            color: '#6b7280'
                        }
                    },
                    y: {
                        beginAtZero: true,
                        grid: {
                            color: '#e5e7eb'
                        },
                        ticks: {
                            color: '#6b7280',
                            callback: (value) => value.toLocaleString()
                        }
                    }
                }
            }
        });

        // Add dynamic time update
        function updateOnlineTime() {
            const now = new Date();
            const timeString = now.toLocaleTimeString();
            const dateString = now.toLocaleDateString();
            // You could update a time element here if needed
        }
        
        // Update time every second
        setInterval(updateOnlineTime, 1000);
        
        // Initialize tooltips or other UI components here
        document.addEventListener('DOMContentLoaded', function() {
            // Additional initialization code
            console.log('Dashboard loaded successfully');
        });
    </script>
</body>
</html>