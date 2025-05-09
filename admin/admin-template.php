<?php
session_start();

// Fetch dashboard data from database
// This would normally connect to your database and fetch real data
$totalProducts = 2487;
$totalBrands = 156;
$totalUsers = 8932;
$totalScans = 43291;
$fakeReports = 142;
$pendingReports = 38;

// Recent verification data
$recentVerifications = [
    ['id' => 'VS78923', 'product' => 'Premium Leather Wallet', 'brand' => 'LuxLeather', 'user' => 'john.doe@example.com', 'location' => 'New York, USA', 'status' => 'Authentic', 'time' => '10 minutes ago'],
    ['id' => 'VS78922', 'product' => 'Designer Sunglasses', 'brand' => 'OptiVision', 'user' => 'sarah.smith@example.com', 'location' => 'London, UK', 'status' => 'Authentic', 'time' => '23 minutes ago'],
    ['id' => 'VS78921', 'product' => 'Smartwatch Pro', 'brand' => 'TechWear', 'user' => 'mike.johnson@example.com', 'location' => 'Sydney, Australia', 'status' => 'Counterfeit', 'time' => '47 minutes ago'],
    ['id' => 'VS78920', 'product' => 'Premium Headphones', 'brand' => 'AudioElite', 'user' => 'lisa.wong@example.com', 'location' => 'Tokyo, Japan', 'status' => 'Authentic', 'time' => '1 hour ago'],
    ['id' => 'VS78919', 'product' => 'Designer Handbag', 'brand' => 'FashionLux', 'user' => 'emma.davis@example.com', 'location' => 'Paris, France', 'status' => 'Authentic', 'time' => '2 hours ago']
];

// Fake report data
$recentReports = [
    ['id' => 'FR4392', 'product' => 'Smartwatch Pro', 'brand' => 'TechWear', 'reporter' => 'mike.johnson@example.com', 'location' => 'Sydney, Australia', 'status' => 'Under Investigation', 'time' => '47 minutes ago'],
    ['id' => 'FR4391', 'product' => 'Designer Belt', 'brand' => 'LuxFashion', 'reporter' => 'alex.carter@example.com', 'location' => 'Berlin, Germany', 'status' => 'Confirmed Fake', 'time' => '3 hours ago'],
    ['id' => 'FR4390', 'product' => 'Premium Watch', 'brand' => 'TimeCraft', 'reporter' => 'david.wilson@example.com', 'location' => 'Toronto, Canada', 'status' => 'Under Investigation', 'time' => '5 hours ago']
];

// Get monthly scan data for chart
$monthlyScans = [4350, 5290, 6123, 7201, 6823, 7602, 8123, 9210, 8932, 10231, 9879, 10578];
$monthNames = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];

// Get top verification locations for map
$topLocations = [
    ['city' => 'New York', 'country' => 'USA', 'count' => 4587],
    ['city' => 'London', 'country' => 'UK', 'count' => 3892],
    ['city' => 'Tokyo', 'country' => 'Japan', 'count' => 3201],
    ['city' => 'Paris', 'country' => 'France', 'count' => 2845],
    ['city' => 'Sydney', 'country' => 'Australia', 'count' => 2132]
];

// Get admin info
$adminName = $_SESSION['admin_name'] ?? 'Admin User';
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
                    <div class="notifications">
                        <i class="fas fa-bell"></i>
                        <span class="notification-badge">5</span>
                    </div>
                    <div class="user-profile">
                        <img src="https://randomuser.me/api/portraits/men/1.jpg" alt="Admin Profile">
                        <div>
                            <h4><?php echo htmlspecialchars($adminName); ?></h4>
                            <small>Admin</small>
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
                    data: <?php echo json_encode($monthlyScans); ?>,
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

        // Notification dropdown functionality
        document.querySelector('.notifications').addEventListener('click', function() {
            // Here you would toggle a notification dropdown
            console.log('Notification clicked');
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
