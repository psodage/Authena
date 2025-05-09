<?php
session_start();

// Check if admin is logged in, redirect if not

// Database connection
$conn = new mysqli("localhost", "root", "", "authena");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Pagination setup
$records_per_page = 10;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $records_per_page;

// Search functionality
$search = isset($_GET['search']) ? $conn->real_escape_string($_GET['search']) : '';
$search_condition = '';
if (!empty($search)) {
    $search_condition = " AND (
        p.name LIKE '%$search%' OR 
        p.product_code LIKE '%$search%' OR 
        u.username LIKE '%$search%' OR 
        u.email LIKE '%$search%' OR 
        vl.location_address LIKE '%$search%' OR
        vl.ip_address LIKE '%$search%'
    )";
}

// Filter by status
$status_filter = isset($_GET['status']) ? $conn->real_escape_string($_GET['status']) : '';
$status_condition = '';
if (!empty($status_filter)) {
    $status_condition = " AND vl.status = '$status_filter'";
}

// Date range filter
$date_from = isset($_GET['date_from']) ? $conn->real_escape_string($_GET['date_from']) : '';
$date_to = isset($_GET['date_to']) ? $conn->real_escape_string($_GET['date_to']) : '';
$date_condition = '';

if (!empty($date_from) && !empty($date_to)) {
    $date_condition = " AND DATE(vl.verification_timestamp) BETWEEN '$date_from' AND '$date_to'";
} elseif (!empty($date_from)) {
    $date_condition = " AND DATE(vl.verification_timestamp) >= '$date_from'";
} elseif (!empty($date_to)) {
    $date_condition = " AND DATE(vl.verification_timestamp) <= '$date_to'";
}

// Get total records
$total_query = "SELECT COUNT(*) as total FROM verification_logs vl 
                LEFT JOIN products p ON vl.product_id = p.id 
                LEFT JOIN users u ON vl.user_id = u.id 
                WHERE 1=1 $search_condition $status_condition $date_condition";
$total_result = $conn->query($total_query);
$total_row = $total_result->fetch_assoc();
$total_records = $total_row['total'];
$total_pages = ceil($total_records / $records_per_page);

// Get verification logs with product and user info
$query = "SELECT vl.*, 
          p.name as product_name, 
          p.product_code,
          p.image_url as product_image,
          u.username, 
          u.email,
          u.first_name,
          u.last_name,
          b.name as brand_name
          FROM verification_logs vl 
          LEFT JOIN products p ON vl.product_id = p.id 
          LEFT JOIN users u ON vl.user_id = u.id
          LEFT JOIN brands b ON p.brand_id = b.id
          WHERE 1=1 $search_condition $status_condition $date_condition
          ORDER BY vl.verification_timestamp DESC 
          LIMIT $offset, $records_per_page";

$result = $conn->query($query);

// Get counts by status for statistics
$status_counts = [
    'authentic' => 0,
    'fake' => 0,
    'unknown' => 0
];

$status_query = "SELECT status, COUNT(*) as count FROM verification_logs GROUP BY status";
$status_result = $conn->query($status_query);
while ($row = $status_result->fetch_assoc()) {
    $status_counts[$row['status']] = $row['count'];
}

// Get admin info
$adminName = $_SESSION['admin_name'] ?? 'Admin User';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verification Logs - Authena Admin</title>
    <!-- Favicon -->
    <link rel="shortcut icon" href="assets/images/favicon.ico" type="image/x-icon">
    <!-- Font Awesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <!-- Chart.js -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/3.9.1/chart.min.js"></script>
    <!-- Leaflet.js for maps -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <!-- Custom CSS -->
    <link rel="stylesheet" href="assets/css/admin.css">
    <style>
        :root {
            --primary-color: #4f46e5;
            --secondary-color: #818cf8;
            --success-color: #10b981;
            --danger-color: #ef4444;
            --warning-color: #f59e0b;
            --dark-color: #1f2937;
            --light-color: #f9fafb;
            --gray-color: #6b7280;
            --border-color: #e5e7eb;
            --sidebar-width: 260px;
            --border-radius: 0.5rem;
        }

        body {
            font-family: 'Inter', sans-serif;
            background-color: #f3f4f6;
            margin: 0;
            padding: 0;
            color: var(--dark-color);
            overflow-x: hidden;
        }

        .wrapper {
            display: flex;
            min-height: 100vh;
        }

        /* Sidebar styles */
        .sidebar {
            width: var(--sidebar-width);
            background: var(--dark-color);
            color: #fff;
            position: fixed;
            height: 100vh;
            overflow-y: auto;
            transition: all 0.3s ease;
            z-index: 1000;
        }

        .sidebar.active {
            margin-left: -260px;
        }

        .sidebar-brand {
            padding: 1.5rem;
            display: flex;
            align-items: center;
            gap: 0.75rem;
            border-bottom: 1px solid #374151;
        }

        .sidebar-brand i {
            font-size: 1.5rem;
            color: var(--primary-color);
        }

        .sidebar-brand h2 {
            margin: 0;
            font-size: 1.25rem;
            font-weight: 600;
        }

        .sidebar-brand span {
            color: var(--secondary-color);
        }

        .sidebar-menu {
            padding: 1rem 0;
        }

        .menu-category {
            padding: 0.5rem 1.5rem;
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
            color: #9ca3af;
            margin-top: 1rem;
        }

        .sidebar-menu ul {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .sidebar-menu ul li a {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding: 0.75rem 1.5rem;
            color: #e5e7eb;
            text-decoration: none;
            transition: all 0.2s ease;
            font-size: 0.875rem;
        }

        .sidebar-menu ul li a:hover, .sidebar-menu ul li a.active {
            background-color: #374151;
            color: #fff;
        }

        .sidebar-menu ul li a i {
            width: 20px;
            text-align: center;
        }

        /* Main content styles */
        .main-content {
            flex: 1;
            margin-left: var(--sidebar-width);
            transition: all 0.3s ease;
        }

        .main-content.active {
            margin-left: 0;
        }

        /* Header styles */
        .header {
            background-color: #fff;
            padding: 1rem 1.5rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
            position: sticky;
            top: 0;
            z-index: 100;
        }

        .toggle-menu {
            background: none;
            border: none;
            color: var(--dark-color);
            font-size: 1.25rem;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            width: 40px;
            height: 40px;
            border-radius: var(--border-radius);
            transition: all 0.2s ease;
        }

        .toggle-menu:hover {
            background-color: var(--border-color);
        }

        .search-container {
            display: flex;
            align-items: center;
            background-color: #f3f4f6;
            border-radius: 9999px;
            padding: 0.5rem 1rem;
            flex: 1;
            max-width: 400px;
            margin: 0 1rem;
        }

        .search-container i {
            color: var(--gray-color);
            margin-right: 0.5rem;
        }

        .search-container input {
            background: none;
            border: none;
            outline: none;
            color: var(--dark-color);
            font-size: 0.875rem;
            width: 100%;
        }

        .user-actions {
            display: flex;
            align-items: center;
            gap: 1.5rem;
        }

        .notifications {
            position: relative;
            cursor: pointer;
        }

        .notifications i {
            font-size: 1.25rem;
            color: var(--gray-color);
        }

        .notification-badge {
            position: absolute;
            top: -8px;
            right: -8px;
            background-color: var(--danger-color);
            color: #fff;
            font-size: 0.75rem;
            border-radius: 9999px;
            padding: 0.15rem 0.375rem;
            min-width: 18px;
            height: 18px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .user-profile {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            cursor: pointer;
        }

        .user-profile img {
            width: 40px;
            height: 40px;
            border-radius: 9999px;
            object-fit: cover;
        }

        .user-profile div {
            display: flex;
            flex-direction: column;
        }

        .user-profile h4 {
            margin: 0;
            font-size: 0.875rem;
            font-weight: 600;
        }

        .user-profile small {
            color: var(--gray-color);
            font-size: 0.75rem;
        }

        /* Content container */
        .content-container {
            padding: 1.5rem;
        }

        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
        }

        .page-header h1 {
            margin: 0;
            font-size: 1.5rem;
            font-weight: 600;
        }

        .breadcrumb {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 0.875rem;
        }

        .breadcrumb a {
            color: var(--gray-color);
            text-decoration: none;
        }

        .breadcrumb a:hover {
            color: var(--primary-color);
        }

        .breadcrumb span {
            color: var(--gray-color);
        }

        /* Dashboard stats */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1.5rem;
            margin-bottom: 1.5rem;
        }

        .stat-card {
            background-color: #fff;
            border-radius: var(--border-radius);
            padding: 1.5rem;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
            display: flex;
            align-items: center;
            gap: 1rem;
            transition: all 0.3s ease;
        }

        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        .stat-icon {
            width: 48px;
            height: 48px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
        }

        .stat-icon.primary {
            background-color: rgba(79, 70, 229, 0.1);
            color: var(--primary-color);
        }

        .stat-icon.success {
            background-color: rgba(16, 185, 129, 0.1);
            color: var(--success-color);
        }

        .stat-icon.danger {
            background-color: rgba(239, 68, 68, 0.1);
            color: var(--danger-color);
        }

        .stat-icon.warning {
            background-color: rgba(245, 158, 11, 0.1);
            color: var(--warning-color);
        }

        .stat-info h3 {
            margin: 0;
            font-size: 1.25rem;
            font-weight: 600;
        }

        .stat-info p {
            margin: 0.25rem 0 0;
            font-size: 0.875rem;
            color: var(--gray-color);
        }

        /* Filter section */
        .filters-section {
            background-color: #fff;
            border-radius: var(--border-radius);
            padding: 1.5rem;
            margin-bottom: 1.5rem;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        }

        .filters-title {
            font-size: 1rem;
            font-weight: 600;
            margin-top: 0;
            margin-bottom: 1rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .filters-title i {
            color: var(--primary-color);
        }

        .filters-form {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
        }

        .form-group {
            margin-bottom: 0;
        }

        .form-group label {
            display: block;
            font-size: 0.875rem;
            font-weight: 500;
            margin-bottom: 0.5rem;
        }

        .form-control {
            width: 100%;
            padding: 0.5rem 0.75rem;
            font-size: 0.875rem;
            border: 1px solid var(--border-color);
            border-radius: 0.375rem;
            outline: none;
            transition: border-color 0.2s ease;
        }

        .form-control:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.1);
        }

        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            padding: 0.5rem 1rem;
            font-size: 0.875rem;
            font-weight: 500;
            border-radius: 0.375rem;
            cursor: pointer;
            transition: all 0.2s ease;
            text-decoration: none;
        }

        .btn-primary {
            background-color: var(--primary-color);
            color: #fff;
            border: none;
        }

        .btn-primary:hover {
            background-color: #4338ca;
        }

        .btn-outline {
            background-color: transparent;
            border: 1px solid var(--border-color);
            color: var(--gray-color);
        }

        .btn-outline:hover {
            border-color: var(--gray-color);
            color: var(--dark-color);
        }

        .filter-buttons {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            margin-top: 1.5rem;
        }

        /* Data table */
        .card {
            background-color: #fff;
            border-radius: var(--border-radius);
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
            margin-bottom: 1.5rem;
            overflow: hidden;
        }

        .card-header {
            padding: 1.25rem 1.5rem;
            border-bottom: 1px solid var(--border-color);
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .card-title {
            margin: 0;
            font-size: 1.125rem;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .card-title i {
            color: var(--primary-color);
        }

        .card-actions {
            display: flex;
            gap: 0.75rem;
        }

        .data-table {
            width: 100%;
            border-collapse: collapse;
        }

        .data-table th, .data-table td {
            padding: 1rem 1.5rem;
            text-align: left;
            font-size: 0.875rem;
        }

        .data-table th {
            background-color: #f9fafb;
            font-weight: 600;
            border-bottom: 1px solid var(--border-color);
        }

        .data-table tr {
            border-bottom: 1px solid var(--border-color);
        }

        .data-table tr:last-child {
            border-bottom: none;
        }

        .data-table tr:hover {
            background-color: #f3f4f6;
        }

        .table-avatar {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            object-fit: cover;
        }

        .product-info, .user-info {
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .product-details, .user-details {
            display: flex;
            flex-direction: column;
        }

        .product-name, .user-name {
            font-weight: 500;
            margin-bottom: 0.25rem;
        }

        .product-code, .user-email {
            color: var(--gray-color);
            font-size: 0.75rem;
        }

        .status-badge {
            display: inline-flex;
            align-items: center;
            padding: 0.25rem 0.5rem;
            border-radius: 9999px;
            font-size: 0.75rem;
            font-weight: 500;
        }

        .status-authentic {
            background-color: rgba(16, 185, 129, 0.1);
            color: var(--success-color);
        }

        .status-fake {
            background-color: rgba(239, 68, 68, 0.1);
            color: var(--danger-color);
        }

        .status-unknown {
            background-color: rgba(245, 158, 11, 0.1);
            color: var(--warning-color);
        }

        .action-btn {
            color: var(--gray-color);
            font-size: 1rem;
            background: none;
            border: none;
            cursor: pointer;
            padding: 0.25rem;
            transition: color 0.2s ease;
        }

        .action-btn:hover {
            color: var(--primary-color);
        }

        .action-btn.view:hover {
            color: var(--primary-color);
        }

        .action-btn.edit:hover {
            color: var(--warning-color);
        }

        .action-btn.delete:hover {
            color: var(--danger-color);
        }

        /* Pagination */
        .pagination-container {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 1rem 1.5rem;
            border-top: 1px solid var(--border-color);
        }

        .pagination-info {
            font-size: 0.875rem;
            color: var(--gray-color);
        }

        .pagination {
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .pagination-btn {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 36px;
            height: 36px;
            border-radius: 0.375rem;
            border: 1px solid var(--border-color);
            background-color: #fff;
            color: var(--gray-color);
            font-size: 0.875rem;
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .pagination-btn:hover {
            border-color: var(--primary-color);
            color: var(--primary-color);
        }

        .pagination-btn.active {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
            color: #fff;
        }

        .pagination-btn.disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }

        /* Map container */
        #verification-map {
            height: 300px;
            border-radius: var(--border-radius);
            margin-top: 1rem;
        }

        .map-card {
            margin-bottom: 1.5rem;
        }

        /* Responsive */
        @media (max-width: 1024px) {
            .sidebar {
                margin-left: -260px;
            }
            
            .sidebar.active {
                margin-left: 0;
            }
            
            .main-content {
                margin-left: 0;
            }
            
            .stats-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        @media (max-width: 768px) {
            .stats-grid {
                grid-template-columns: 1fr;
            }
            
            .filters-form {
                grid-template-columns: 1fr;
            }
            
            .card-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 1rem;
            }
            
            .card-actions {
                width: 100%;
                justify-content: flex-end;
            }
            
            .data-table {
                display: block;
                overflow-x: auto;
            }
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
                    <li><a href="admin-logs.php" class="active"><i class="fas fa-history"></i> Verification Logs</a></li>
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
                            <h4><?php echo htmlspecialchars($adminName); ?></h4>
                            <small>Admin</small>
                        </div>
                    </div>
                </div>
            </header>

            <!-- Content Container -->
            <div class="content-container">
                <!-- Page Header -->
                <div class="page-header">
                    <h1>Verification Logs</h1>
                    <div class="breadcrumb">
                        <a href="admin-dashboard.php">Home</a>
                        <span>/</span>
                        <a href="admin-logs.php">Verification Logs</a>
                    </div>
                </div>

                <!-- Stats Grid -->
                <div class="stats-grid">
                    <div class="stat-card">
                        <div class="stat-icon primary">
                            <i class="fas fa-qrcode"></i>
                        </div>
                        <div class="stat-info">
                            <h3><?php echo number_format($total_records); ?></h3>
                            <p>Total Scans</p>
                        </div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon success">
                            <i class="fas fa-check-circle"></i>
                        </div>
                        <div class="stat-info">
                            <h3><?php echo number_format($status_counts['authentic']); ?></h3>
                            <p>Authentic Products</p>
                        </div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon danger">
                            <i class="fas fa-times-circle"></i>
                        </div>
                        <div class="stat-info">
                            <h3><?php echo number_format($status_counts['fake']); ?></h3>
                            <p>Fake Products</p>
                        </div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon warning">
                            <i class="fas fa-question-circle"></i>
                        </div>
                        <div class="stat-info">
                            <h3><?php echo number_format($status_counts['unknown']); ?></h3>
                            <p>Unknown Status</p>
                        </div>
                    </div>
                </div>

                <!-- Map Card -->
           

                <!-- Filters Section -->
                <div class="filters-section">
                    <h3 class="filters-title">
                        <i class="fas fa-filter"></i>
                        Filter Logs
                    </h3>
                    <form action="" method="GET" class="filters-form">
                        <div class="form-group">
                            <label for="search">Search</label>
                            <input type="text" id="search" name="search" class="form-control" value="<?php echo htmlspecialchars($search); ?>" placeholder="Product, User, Location, IP">
                        </div>
                        <div class="form-group">
                            <label for="status">Status</label>
                            <select id="status" name="status" class="form-control">
                                <option value="">All Statuses</option>
                                <option value="authentic" <?php if($status_filter == 'authentic') echo 'selected'; ?>>Authentic</option>
                                <option value="fake" <?php if($status_filter == 'fake') echo 'selected'; ?>>Fake</option>
                                <option value="unknown" <?php if($status_filter == 'unknown') echo 'selected'; ?>>Unknown</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="date_from">Date From</label>
                            <input type="date" id="date_from" name="date_from" class="form-control" value="<?php echo htmlspecialchars($date_from); ?>">
                        </div>
                        <div class="form-group">
                            <label for="date_to">Date To</label>
                            <input type="date" id="date_to" name="date_to" class="form-control" value="<?php echo htmlspecialchars($date_to); ?>">
                        </div>
                        <div class="form-group">
                            <label>&nbsp;</label>
                            <div class="filter-buttons">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-filter"></i>
                                    Apply Filters
                                </button>
                                <a href="admin-logs.php" class="btn btn-outline">
                                    <i class="fas fa-times"></i>
                                    Clear Filters
                                </a>
                            </div>
                        </div>
                    </form>
                </div>

                <!-- Data Table Card -->
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-history"></i>
                            Verification Log Entries
                        </h3>
                        <div class="card-actions">
                     
                        </div>
                    </div>
                    <div class="card-body">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Product</th>
                                    <th>User</th>
                                    <th>Status</th>
                                    <th>Location</th>
                                    <th>IP Address</th>
                                    <th>Date & Time</th>
                                   
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                if ($result->num_rows > 0) {
                                    while($row = $result->fetch_assoc()) {
                                ?>
                                <tr>
                                    <td><?php echo $row['id']; ?></td>
                                    <td>
                                        <div class="product-info">
                                            <img src="<?php echo !empty($row['product_image']) ? $row['product_image'] : 'assets/images/no-image.png'; ?>" alt="Product" class="table-avatar">
                                            <div class="product-details">
                                                <div class="product-name"><?php echo htmlspecialchars($row['product_name'] ?? 'Unknown Product'); ?></div>
                                                <div class="product-code"><?php echo htmlspecialchars($row['product_code'] ?? ''); ?> | <?php echo htmlspecialchars($row['brand_name'] ?? 'Unknown Brand'); ?></div>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <?php if(!empty($row['user_id'])): ?>
                                        <div class="user-info">
                                            <div class="user-details">
                                                <div class="user-name"><?php echo htmlspecialchars($row['first_name'] . ' ' . $row['last_name']); ?></div>
                                                <div class="user-email"><?php echo htmlspecialchars($row['email']); ?></div>
                                            </div>
                                        </div>
                                        <?php else: ?>
                                        <div class="user-info">
                                            <div class="user-details">
                                                <div class="user-name">Guest User</div>
                                                <div class="user-email">No account</div>
                                            </div>
                                        </div>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php
                                        $status_class = '';
                                        switch($row['status']) {
                                            case 'authentic':
                                                $status_class = 'status-authentic';
                                                break;
                                            case 'fake':
                                                $status_class = 'status-fake';
                                                break;
                                            default:
                                                $status_class = 'status-unknown';
                                        }
                                        ?>
                                        <span class="status-badge <?php echo $status_class; ?>">
                                            <?php echo ucfirst($row['status']); ?>
                                        </span>
                                    </td>
                                    <td><?php echo htmlspecialchars($row['location_address'] ?? 'Unknown'); ?></td>
                                    <td><?php echo htmlspecialchars($row['ip_address'] ?? 'Unknown'); ?></td>
                                    <td><?php echo date('M d, Y h:i A', strtotime($row['verification_timestamp'])); ?></td>
                                   
                                </tr>
                                <?php 
                                    }
                                } else {
                                ?>
                                <tr>
                                    <td colspan="8" style="text-align: center;">No verification logs found</td>
                                </tr>
                                <?php 
                                }
                                ?>
                            </tbody>
                        </table>
                    </div>
                    <div class="pagination-container">
                        <div class="pagination-info">
                            Showing <?php echo min(($page - 1) * $records_per_page + 1, $total_records); ?> to <?php echo min($page * $records_per_page, $total_records); ?> of <?php echo $total_records; ?> entries
                        </div>
                        <div class="pagination">
                            <?php if($page > 1): ?>
                            <a href="?page=1<?php echo !empty($search) ? '&search=' . urlencode($search) : ''; ?><?php echo !empty($status_filter) ? '&status=' . urlencode($status_filter) : ''; ?><?php echo !empty($date_from) ? '&date_from=' . urlencode($date_from) : ''; ?><?php echo !empty($date_to) ? '&date_to=' . urlencode($date_to) : ''; ?>" class="pagination-btn">
                                <i class="fas fa-angle-double-left"></i>
                            </a>
                            <a href="?page=<?php echo $page - 1; ?><?php echo !empty($search) ? '&search=' . urlencode($search) : ''; ?><?php echo !empty($status_filter) ? '&status=' . urlencode($status_filter) : ''; ?><?php echo !empty($date_from) ? '&date_from=' . urlencode($date_from) : ''; ?><?php echo !empty($date_to) ? '&date_to=' . urlencode($date_to) : ''; ?>" class="pagination-btn">
                                <i class="fas fa-angle-left"></i>
                            </a>
                            <?php else: ?>
                            <button class="pagination-btn disabled">
                                <i class="fas fa-angle-double-left"></i>
                            </button>
                            <button class="pagination-btn disabled">
                                <i class="fas fa-angle-left"></i>
                            </button>
                            <?php endif; ?>
                            
                            <?php
                            $start_page = max(1, $page - 2);
                            $end_page = min($start_page + 4, $total_pages);
                            
                            if ($end_page - $start_page < 4 && $start_page > 1) {
                                $start_page = max(1, $end_page - 4);
                            }
                            
                            for ($i = $start_page; $i <= $end_page; $i++):
                            ?>
                            <a href="?page=<?php echo $i; ?><?php echo !empty($search) ? '&search=' . urlencode($search) : ''; ?><?php echo !empty($status_filter) ? '&status=' . urlencode($status_filter) : ''; ?><?php echo !empty($date_from) ? '&date_from=' . urlencode($date_from) : ''; ?><?php echo !empty($date_to) ? '&date_to=' . urlencode($date_to) : ''; ?>" class="pagination-btn <?php echo $i == $page ? 'active' : ''; ?>">
                                <?php echo $i; ?>
                            </a>
                            <?php endfor; ?>
                            
                            <?php if($page < $total_pages): ?>
                            <a href="?page=<?php echo $page + 1; ?><?php echo !empty($search) ? '&search=' . urlencode($search) : ''; ?><?php echo !empty($status_filter) ? '&status=' . urlencode($status_filter) : ''; ?><?php echo !empty($date_from) ? '&date_from=' . urlencode($date_from) : ''; ?><?php echo !empty($date_to) ? '&date_to=' . urlencode($date_to) : ''; ?>" class="pagination-btn">
                                <i class="fas fa-angle-right"></i>
                            </a>
                            <a href="?page=<?php echo $total_pages; ?><?php echo !empty($search) ? '&search=' . urlencode($search) : ''; ?><?php echo !empty($status_filter) ? '&status=' . urlencode($status_filter) : ''; ?><?php echo !empty($date_from) ? '&date_from=' . urlencode($date_from) : ''; ?><?php echo !empty($date_to) ? '&date_to=' . urlencode($date_to) : ''; ?>" class="pagination-btn">
                                <i class="fas fa-angle-double-right"></i>
                            </a>
                            <?php else: ?>
                            <button class="pagination-btn disabled">
                                <i class="fas fa-angle-right"></i>
                            </button>
                            <button class="pagination-btn disabled">
                                <i class="fas fa-angle-double-right"></i>
                            </button>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div class="modal" id="deleteModal" style="display: none;">
        <div class="modal-content">
            <div class="modal-header">
                <h4>Confirm Deletion</h4>
                <button type="button" class="close-modal">&times;</button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to delete this verification log? This action cannot be undone.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline" id="cancelDelete">Cancel</button>
                <a href="#" id="confirmDelete" class="btn btn-primary" style="background-color: var(--danger-color);">Delete</a>
            </div>
        </div>
    </div>

    <!-- JavaScript -->
    <script>
        // Toggle sidebar
        document.getElementById('toggleMenu').addEventListener('click', function() {
            document.querySelector('.sidebar').classList.toggle('active');
            document.querySelector('.main-content').classList.toggle('active');
        });

        // Map initialization
        document.addEventListener('DOMContentLoaded', function() {
            const map = L.map('verification-map').setView([0, 0], 2);
            
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
            }).addTo(map);

            // Fetch verification locations data using AJAX
            fetch('get-verification-locations.php')
                .then(response => response.json())
                .then(data => {
                    if (data.length > 0) {
                        // Add markers for each location
                        data.forEach(location => {
                            if (location.latitude && location.longitude) {
                                const markerColor = location.status === 'authentic' ? 'green' : 
                                                    location.status === 'fake' ? 'red' : 'orange';
                                
                                const markerIcon = L.divIcon({
                                    className: 'custom-marker',
                                    html: `<i class="fas fa-map-marker-alt" style="color: ${markerColor}; font-size: 24px;"></i>`,
                                    iconSize: [24, 24],
                                    iconAnchor: [12, 24]
                                });
                                
                                const marker = L.marker([location.latitude, location.longitude], { icon: markerIcon }).addTo(map);
                                
                                const popupContent = `
                                    <strong>${location.product_name || 'Unknown Product'}</strong><br>
                                    Status: <span style="color: ${markerColor};">${location.status.charAt(0).toUpperCase() + location.status.slice(1)}</span><br>
                                    ${location.location_address || 'Unknown Location'}<br>
                                    ${new Date(location.verification_timestamp).toLocaleString()}
                                `;
                                
                                marker.bindPopup(popupContent);
                            }
                        });
                        
                        // Fit map to show all markers
                        const validLocations = data.filter(loc => loc.latitude && loc.longitude);
                        if (validLocations.length > 0) {
                            const bounds = L.latLngBounds(validLocations.map(loc => [loc.latitude, loc.longitude]));
                            map.fitBounds(bounds);
                        }
                    }
                })
                .catch(error => console.error('Error loading verification locations:', error));
        });

        // Delete confirmation modal
        function confirmDelete(id) {
            const modal = document.getElementById('deleteModal');
            const confirmBtn = document.getElementById('confirmDelete');
            const cancelBtn = document.getElementById('cancelDelete');
            const closeBtn = document.querySelector('.close-modal');
            
            modal.style.display = 'block';
            confirmBtn.href = 'admin-delete-log.php?id=' + id;
            
            cancelBtn.onclick = function() {
                modal.style.display = 'none';
            }
            
            closeBtn.onclick = function() {
                modal.style.display = 'none';
            }
            
            window.onclick = function(event) {
                if (event.target === modal) {
                    modal.style.display = 'none';
                }
            }
        }
    </script>
    <style>
        /* Modal styles */
        .modal {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 1050;
        }
        
        .modal-content {
            background-color: #fff;
            border-radius: var(--border-radius);
            width: 100%;
            max-width: 500px;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.2);
        }
        
        .modal-header {
            padding: 1rem 1.5rem;
            border-bottom: 1px solid var(--border-color);
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        
        .modal-header h4 {
            margin: 0;
            font-size: 1.125rem;
            font-weight: 600;
        }
        
        .close-modal {
            background: none;
            border: none;
            font-size: 1.5rem;
            cursor: pointer;
            color: var(--gray-color);
        }
        
        .modal-body {
            padding: 1.5rem;
        }
        
        .modal-footer {
            padding: 1rem 1.5rem;
            border-top: 1px solid var(--border-color);
            display: flex;
            align-items: center;
            justify-content: flex-end;
            gap: 0.75rem;
        }
        
        /* Custom map marker */
        .custom-marker {
            text-align: center;
        }
    </style>
</body>
</html>
<?php $conn->close(); ?>