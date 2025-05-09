<?php
session_start();
require_once 'db_con.php';

// Check if admin is logged in

// Get admin info
$adminName = $_SESSION['admin_name'] ?? 'Admin User';

// Fetch analytics data from database
// Get total verifications by month for the last 12 months
$stmt = $conn->prepare("
    SELECT 
        MONTH(verification_timestamp) as month,
        YEAR(verification_timestamp) as year,
        COUNT(*) as total_scans,
        SUM(CASE WHEN status = 'authentic' THEN 1 ELSE 0 END) as authentic_count,
        SUM(CASE WHEN status = 'fake' THEN 1 ELSE 0 END) as fake_count
    FROM verification_logs
    WHERE verification_timestamp >= DATE_SUB(NOW(), INTERVAL 12 MONTH)
    GROUP BY YEAR(verification_timestamp), MONTH(verification_timestamp)
    ORDER BY year ASC, month ASC
");
$stmt->execute();
$monthlyData = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Format data for Chart.js
$labels = [];
$totalScans = [];
$authenticScans = [];
$fakeScans = [];

$monthNames = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
foreach ($monthlyData as $data) {
    $labels[] = $monthNames[$data['month']-1] . ' ' . $data['year'];
    $totalScans[] = $data['total_scans'];
    $authenticScans[] = $data['authentic_count'];
    $fakeScans[] = $data['fake_count'];
}

// Fill in missing months with zeros
if (count($labels) < 12) {
    $currentMonth = date('n');
    $currentYear = date('Y');
    
    for ($i = 11; $i >= 0; $i--) {
        $month = ($currentMonth - $i) % 12;
        if ($month <= 0) $month += 12;
        $year = $currentYear;
        if ($month > $currentMonth) $year--;
        
        $monthLabel = $monthNames[$month-1] . ' ' . $year;
        
        if (!in_array($monthLabel, $labels)) {
            $index = 0;
            while ($index < count($labels) && $labels[$index] < $monthLabel) {
                $index++;
            }
            
            array_splice($labels, $index, 0, [$monthLabel]);
            array_splice($totalScans, $index, 0, [0]);
            array_splice($authenticScans, $index, 0, [0]);
            array_splice($fakeScans, $index, 0, [0]);
        }
    }
}

// Get verification by status
$stmt = $conn->prepare("
    SELECT 
        status,
        COUNT(*) as count
    FROM verification_logs
    GROUP BY status
");
$stmt->execute();
$statusData = $stmt->fetchAll(PDO::FETCH_ASSOC);

$statusLabels = [];
$statusCounts = [];
$statusColors = [
    'authentic' => '#4ade80',
    'fake' => '#f87171',
    'unknown' => '#fbbf24'
];

foreach ($statusData as $data) {
    $statusLabels[] = ucfirst($data['status']);
    $statusCounts[] = $data['count'];
}

// Get verifications by country
$stmt = $conn->prepare("
    SELECT 
        SUBSTRING_INDEX(location_address, ',', -1) as country,
        COUNT(*) as count
    FROM verification_logs
    WHERE location_address IS NOT NULL
    GROUP BY country
    ORDER BY count DESC
    LIMIT 10
");
$stmt->execute();
$countryData = $stmt->fetchAll(PDO::FETCH_ASSOC);

$countryLabels = [];
$countryCounts = [];

foreach ($countryData as $data) {
    $countryLabels[] = trim($data['country']);
    $countryCounts[] = $data['count'];
}

// Top brands by verification count
$stmt = $conn->prepare("
    SELECT 
        b.name as brand_name,
        COUNT(v.id) as verification_count
    FROM verification_logs v
    JOIN products p ON v.product_id = p.id
    JOIN brands b ON p.brand_id = b.id
    GROUP BY b.id
    ORDER BY verification_count DESC
    LIMIT 5
");
$stmt->execute();
$brandData = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Top fake reported products
$stmt = $conn->prepare("
    SELECT 
        p.name as product_name,
        b.name as brand_name,
        COUNT(f.id) as report_count
    FROM fake_reports f
    JOIN products p ON f.product_id = p.id
    JOIN brands b ON p.brand_id = b.id
    GROUP BY p.id
    ORDER BY report_count DESC
    LIMIT 5
");
$stmt->execute();
$fakeReportData = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Top users by verification count
$stmt = $conn->prepare("
    SELECT 
        u.username,
        u.verification_count
    FROM users u
    ORDER BY u.verification_count DESC
    LIMIT 5
");
$stmt->execute();
$userVerificationData = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Overall stats
$stmt = $conn->prepare("SELECT COUNT(*) as total FROM verification_logs");
$stmt->execute();
$totalVerifications = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

$stmt = $conn->prepare("SELECT COUNT(*) as total FROM fake_reports");
$stmt->execute();
$totalReports = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

$stmt = $conn->prepare("SELECT COUNT(*) as total FROM products");
$stmt->execute();
$totalProducts = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

$stmt = $conn->prepare("SELECT COUNT(*) as total FROM users");
$stmt->execute();
$totalUsers = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

// Recent activity timeline
$stmt = $conn->prepare("
    SELECT 
        'verification' as type,
        v.verification_timestamp as timestamp,
        p.name as product_name,
        u.username as username,
        v.status as status
    FROM verification_logs v
    JOIN products p ON v.product_id = p.id
    LEFT JOIN users u ON v.user_id = u.id
    
    UNION ALL
    
    SELECT 
        'report' as type,
        f.created_at as timestamp,
        p.name as product_name,
        u.username as username,
        f.status as status
    FROM fake_reports f
    JOIN products p ON f.product_id = p.id
    LEFT JOIN users u ON f.user_id = u.id
    
    ORDER BY timestamp DESC
    LIMIT 10
");
$stmt->execute();
$recentActivity = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Authena Admin - Analytics</title>
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
    <style>
        /* Additional CSS for analytics page */
        .analytics-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }
        
        .stat-card {
            background-color: #fff;
            border-radius: 10px;
            padding: 1.5rem;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
            display: flex;
            flex-direction: column;
            transition: transform 0.3s ease;
        }
        
        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 15px rgba(0, 0, 0, 0.1);
        }
        
        .stat-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
        }
        
        .stat-icon {
            background-color: #f3f4f6;
            width: 48px;
            height: 48px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.2rem;
        }
        
        .stat-title {
            font-size: 0.875rem;
            color: #6b7280;
            font-weight: 500;
        }
        
        .stat-value {
            font-size: 1.8rem;
            font-weight: 600;
            color: #111827;
            margin-bottom: 0.5rem;
        }
        
        .stat-subtitle {
            font-size: 0.875rem;
            color: #6b7280;
        }
        
        .chart-card {
            background-color: #fff;
            border-radius: 10px;
            padding: 1.5rem;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
            margin-bottom: 1.5rem;
        }
        
        .chart-title {
            font-size: 1.125rem;
            font-weight: 600;
            margin-bottom: 1rem;
            color: #111827;
        }
        
        .chart-container {
            position: relative;
            height: 300px;
            width: 100%;
        }
        
        .data-table {
            width: 100%;
            border-collapse: collapse;
        }
        
        .data-table th, .data-table td {
            padding: 0.75rem 1rem;
            text-align: left;
        }
        
        .data-table thead {
            background-color: #f9fafb;
            border-bottom: 1px solid #e5e7eb;
        }
        
        .data-table th {
            font-weight: 500;
            color: #4b5563;
            font-size: 0.875rem;
        }
        
        .data-table tbody tr {
            border-bottom: 1px solid #e5e7eb;
        }
        
        .data-table tbody tr:hover {
            background-color: #f9fafb;
        }
        
        .badge {
            padding: 0.25rem 0.5rem;
            border-radius: 9999px;
            font-size: 0.75rem;
            font-weight: 500;
        }
        
        .badge-authentic {
            background-color: rgba(74, 222, 128, 0.1);
            color: #16a34a;
        }
        
        .badge-fake {
            background-color: rgba(248, 113, 113, 0.1);
            color: #dc2626;
        }
        
        .badge-pending {
            background-color: rgba(251, 191, 36, 0.1);
            color: #d97706;
        }
        
        .timeline {
            position: relative;
            padding-left: 2rem;
        }
        
        .timeline::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0.75rem;
            height: 100%;
            width: 2px;
            background-color: #e5e7eb;
        }
        
        .timeline-item {
            position: relative;
            padding-bottom: 1.5rem;
        }
        
        .timeline-dot {
            position: absolute;
            left: -2rem;
            top: 0;
            width: 1rem;
            height: 1rem;
            border-radius: 50%;
            background-color: #4f46e5;
        }
        
        .timeline-dot-verification {
            background-color: #4f46e5;
        }
        
        .timeline-dot-report {
            background-color: #dc2626;
        }
        
        .timeline-content {
            background-color: #f9fafb;
            border-radius: 8px;
            padding: 1rem;
        }
        
        .timeline-header {
            display: flex;
            justify-content: space-between;
            margin-bottom: 0.5rem;
        }
        
        .timeline-title {
            font-weight: 500;
            color: #111827;
        }
        
        .timeline-time {
            font-size: 0.75rem;
            color: #6b7280;
        }
        
        .timeline-details {
            font-size: 0.875rem;
            color: #4b5563;
        }
        
        .grid-2-1 {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 1.5rem;
        }
        
        @media (max-width: 1024px) {
            .grid-2-1 {
                grid-template-columns: 1fr;
            }
        }
        
        .flex-cards {
            display: flex;
            flex-wrap: wrap;
            gap: 1.5rem;
        }
        
        .flex-card {
            flex: 1;
            min-width: 250px;
        }
        
        /* Icon colors */
        .icon-blue {
            color: #4f46e5;
        }
        
        .icon-green {
            color: #16a34a;
        }
        
        .icon-red {
            color: #dc2626;
        }
        
        .icon-orange {
            color: #ea580c;
        }
        
        .icon-purple {
            color: #9333ea;
        }
        
        .text-success {
            color: #16a34a;
        }
        
        .text-danger {
            color: #dc2626;
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
                    <li><a href="admin-map.php"><i class="fas fa-map-marked-alt"></i> Scan Map</a></li>
                    <li><a href="admin-analytics.php" class="active"><i class="fas fa-chart-bar"></i> Analytics</a></li>
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

            <!-- Analytics Content -->
            <div class="dashboard">
                <div class="dashboard-header">
                    <h1>Analytics</h1>
                    <div class="breadcrumb">
                        <a href="admin-dashboard.php">Home</a>
                        <span>/</span>
                        <a href="admin-analytics.php">Analytics</a>
                    </div>
                </div>

                <!-- Overall Stats Cards -->
               

                <!-- Monthly Verification Chart -->
                <div class="chart-card">
                    <div class="chart-title">Monthly Verification Trends</div>
                    <div class="chart-container">
                        <canvas id="verificationTrendChart"></canvas>
                    </div>
                </div>

                <!-- Two Column Layout -->
                <div class="grid-2-1">
                    <!-- Verification Status Chart -->
                    <div class="chart-card">
                        <div class="chart-title">Verification Status Distribution</div>
                        <div class="chart-container" style="height: 250px;">
                            <canvas id="statusChart"></canvas>
                        </div>
                    </div>
                    
                    <!-- Top Countries -->
                    <div class="chart-card">
                        <div class="chart-title">Top Verification Countries</div>
                        <div class="chart-container" style="height: 250px;">
                            <canvas id="countryChart"></canvas>
                        </div>
                    </div>
                </div>

                <!-- Flex Cards Row -->
                <div class="flex-cards">
                    <!-- Top Brands -->
                    <div class="chart-card flex-card">
                        <div class="chart-title">
                            <i class="fas fa-copyright"></i> Top Verified Brands
                        </div>
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>Brand</th>
                                    <th>Verifications</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($brandData as $brand): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($brand['brand_name']); ?></td>
                                    <td><?php echo number_format($brand['verification_count']); ?></td>
                                </tr>
                                <?php endforeach; ?>
                                <?php if (empty($brandData)): ?>
                                <tr>
                                    <td colspan="2" style="text-align: center;">No data available</td>
                                </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                    
                    <!-- Top Fake Reported Products -->
                    <div class="chart-card flex-card">
                        <div class="chart-title">
                            <i class="fas fa-exclamation-triangle icon-red"></i> Top Reported Products
                        </div>
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>Product</th>
                                    <th>Brand</th>
                                    <th>Reports</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($fakeReportData as $report): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($report['product_name']); ?></td>
                                    <td><?php echo htmlspecialchars($report['brand_name']); ?></td>
                                    <td><?php echo number_format($report['report_count']); ?></td>
                                </tr>
                                <?php endforeach; ?>
                                <?php if (empty($fakeReportData)): ?>
                                <tr>
                                    <td colspan="3" style="text-align: center;">No data available</td>
                                </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                    
                    <!-- Top Users -->
                    <div class="chart-card flex-card">
                        <div class="chart-title">
                            <i class="fas fa-user-check icon-blue"></i> Top Users by Verifications
                        </div>
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>Username</th>
                                    <th>Verifications</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($userVerificationData as $user): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($user['username']); ?></td>
                                    <td><?php echo number_format($user['verification_count']); ?></td>
                                </tr>
                                <?php endforeach; ?>
                                <?php if (empty($userVerificationData)): ?>
                                <tr>
                                    <td colspan="2" style="text-align: center;">No data available</td>
                                </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Recent Activity Timeline -->
                <div class="chart-card">
                    <div class="chart-title">
                        <i class="fas fa-history"></i> Recent Activity Timeline
                    </div>
                    <div class="timeline">
                        <?php foreach ($recentActivity as $activity): ?>
                        <div class="timeline-item">
                            <div class="timeline-dot timeline-dot-<?php echo $activity['type']; ?>"></div>
                            <div class="timeline-content">
                                <div class="timeline-header">
                                    <div class="timeline-title">
                                        <?php if ($activity['type'] == 'verification'): ?>
                                            <i class="fas fa-qrcode"></i> Product Verification
                                        <?php else: ?>
                                            <i class="fas fa-flag"></i> Fake Report Submitted
                                        <?php endif; ?>
                                    </div>
                                    <div class="timeline-time">
                                        <?php 
                                        $timestamp = strtotime($activity['timestamp']);
                                        echo date('M j, Y g:i A', $timestamp); 
                                        ?>
                                    </div>
                                </div>
                                <div class="timeline-details">
                                    <p>
                                        User <strong><?php echo htmlspecialchars($activity['username'] ?? 'Anonymous'); ?></strong> 
                                        <?php if ($activity['type'] == 'verification'): ?>
                                            verified <strong><?php echo htmlspecialchars($activity['product_name']); ?></strong>
                                            and found it 
                                            <?php if ($activity['status'] == 'authentic'): ?>
                                                <span class="badge badge-authentic">Authentic</span>
                                            <?php elseif ($activity['status'] == 'fake'): ?>
                                                <span class="badge badge-fake">Fake</span>
                                            <?php else: ?>
                                                <span class="badge badge-pending">Unknown</span>
                                            <?php endif; ?>
                                        <?php else: ?>
                                            reported <strong><?php echo htmlspecialchars($activity['product_name']); ?></strong>
                                            as counterfeit. Status: 
                                            <?php if ($activity['status'] == 'resolved'): ?>
                                                <span class="badge badge-authentic">Resolved</span>
                                            <?php elseif ($activity['status'] == 'rejected'): ?>
                                                <span class="badge badge-fake">Rejected</span>
                                            <?php else: ?>
                                                <span class="badge badge-pending">Pending</span>
                                            <?php endif; ?>
                                        <?php endif; ?>
                                    </p>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                        <?php if (empty($recentActivity)): ?>
                        <div class="timeline-item">
                            <div class="timeline-dot"></div>
                            <div class="timeline-content">
                                <div class="timeline-details">
                                    <p>No recent activity found.</p>
                                </div>
                            </div>
                        </div>
                        <?php endif; ?>
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

        // Monthly Verification Trend Chart
        const trendCtx = document.getElementById('verificationTrendChart').getContext('2d');
        const verificationTrendChart = new Chart(trendCtx, {
            type: 'line',
            data: {
                labels: <?php echo json_encode($labels); ?>,
                datasets: [
                    {
                        label: 'Total Scans',
                        data: <?php echo json_encode($totalScans); ?>,
                        backgroundColor: 'rgba(79, 70, 229, 0.1)',
                        borderColor: '#4f46e5',
                        borderWidth: 2,
                        tension: 0.4,
                        pointBackgroundColor: '#ffffff',
                        pointBorderColor: '#4f46e5',
                        pointBorderWidth: 2,
                        pointRadius: 4,
                        pointHoverRadius: 6,
                        order: 3
                    },
                    {
                        label: 'Authentic',
                        data: <?php echo json_encode($authenticScans); ?>,
                        backgroundColor: 'rgba(74, 222, 128, 0.1)',
                        borderColor: '#4ade80',
                        borderWidth: 2,
                        tension: 0.4,
                        pointBackgroundColor: '#ffffff',
                        pointBorderColor: '#4ade80',
                        pointBorderWidth: 2,
                        pointRadius: 3,
                        pointHoverRadius: 5,
                        order: 2
                    },
                    {
                        label: 'Fake',
                        data: <?php echo json_encode($fakeScans); ?>,
                        backgroundColor: 'rgba(248, 113, 113, 0.1)',
                        borderColor: '#f87171',
                        borderWidth: 2,
                        tension: 0.4,
                        pointBackgroundColor: '#ffffff',
                        pointBorderColor: '#f87171',
                        pointBorderWidth: 2,
                        pointRadius: 3,
                        pointHoverRadius: 5,
                        order: 1
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'top',
                        labels: {
                            usePointStyle: true,
                            boxWidth: 6
                        }
                    },
                    tooltip: {
                        mode: 'index',
                        intersect: false,
                        backgroundColor: 'rgba(0, 0, 0, 0.7)',
                        padding: 10,
                        cornerRadius: 4,
                        caretSize: 6
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: {
                            drawBorder: false,
                            color: '#e5e7eb'
                        },
                        ticks: {
                            precision: 0
                        }
                    },
                    x: {
                        grid: {
                            display: false
                        }
                    }
                }
            }
        });

        // Status Distribution Chart
        const statusCtx = document.getElementById('statusChart').getContext('2d');
        const statusColors = [
            '#4ade80', // Authentic
            '#f87171', // Fake
            '#fbbf24'  // Unknown
        ];
        
        const statusChart = new Chart(statusCtx, {
            type: 'doughnut',
            data: {
                labels: <?php echo json_encode($statusLabels); ?>,
                datasets: [{
                    data: <?php echo json_encode($statusCounts); ?>,
                    backgroundColor: statusColors,
                    borderColor: '#ffffff',
                    borderWidth: 2
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'right',
                        labels: {
                            usePointStyle: true,
                            boxWidth: 6,
                            padding: 20
                        }
                    },
                    tooltip: {
                        backgroundColor: 'rgba(0, 0, 0, 0.7)',
                        padding: 10,
                        cornerRadius: 4,
                        caretSize: 6
                    }
                },
                cutout: '70%'
            }
        });

        // Country Chart
        const countryCtx = document.getElementById('countryChart').getContext('2d');
        const countryColors = [
            '#4f46e5', '#4ade80', '#f87171', '#fbbf24', '#f472b6', 
            '#38bdf8', '#fb923c', '#a3e635', '#a78bfa', '#60a5fa'
        ];
        
        const countryChart = new Chart(countryCtx, {
            type: 'bar',
            data: {
                labels: <?php echo json_encode($countryLabels); ?>,
                datasets: [{
                    label: 'Verifications by Country',
                    data: <?php echo json_encode($countryCounts); ?>,
                    backgroundColor: countryColors,
                    borderRadius: 4
                }]
            },
            options: {
                indexAxis: 'y',
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        backgroundColor: 'rgba(0, 0, 0, 0.7)',
                        padding: 10,
                        cornerRadius: 4,
                        caretSize: 6
                    }
                },
                scales: {
                    x: {
                        beginAtZero: true,
                        grid: {
                            drawBorder: false,
                            color: '#e5e7eb'
                        },
                        ticks: {
                            precision: 0
                        }
                    },
                    y: {
                        grid: {
                            display: false
                        }
                    }
                }
            }
        });
    </script>
</body>
</html>