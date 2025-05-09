<?php
session_start();

// Database connection
require_once 'db_con.php';

// Get admin info
$adminName = $_SESSION['admin_name'] ?? 'Admin User';

// Handle search functionality
$searchQuery = $_GET['search'] ?? '';
$searchCondition = '';
$searchParams = [];

if (!empty($searchQuery)) {
    $searchCondition = " WHERE name LIKE ? ";
    $searchParams[] = "%" . $searchQuery . "%";
}

// Handle filter
$statusFilter = $_GET['status'] ?? 'all';
if ($statusFilter !== 'all') {
    if (!empty($searchCondition)) {
        $searchCondition .= " AND status = ? ";
    } else {
        $searchCondition = " WHERE status = ? ";
    }
    $searchParams[] = $statusFilter;
}

// Get total brands count
$countQuery = "SELECT COUNT(*) as total FROM brands" . $searchCondition;
$countStmt = $conn->prepare($countQuery);
if (!empty($searchParams)) {
    $countStmt->execute($searchParams);
} else {
    $countStmt->execute();
}
$totalBrandsResult = $countStmt->fetch(PDO::FETCH_ASSOC);
$totalBrands = $totalBrandsResult['total'];

// Get active brands count
$activeCountQuery = "SELECT COUNT(*) as active FROM brands WHERE status = 'Active'";
$activeCountStmt = $conn->prepare($activeCountQuery);
$activeCountStmt->execute();
$activeBrandsResult = $activeCountStmt->fetch(PDO::FETCH_ASSOC);
$activeBrands = $activeBrandsResult['active'];
$inactiveBrands = $totalBrands - $activeBrands;

// Get total products count
$productsQuery = "SELECT COUNT(*) as products FROM products";
$productsStmt = $conn->prepare($productsQuery);
$productsStmt->execute();
$productsResult = $productsStmt->fetch(PDO::FETCH_ASSOC);
$totalProducts = $productsResult['products'];

// Get total verifications count
$verificationsQuery = "SELECT COUNT(*) as verifications FROM verification_logs";
$verificationsStmt = $conn->prepare($verificationsQuery);
$verificationsStmt->execute();
$verificationsResult = $verificationsStmt->fetch(PDO::FETCH_ASSOC);
$totalVerifications = $verificationsResult['verifications'];

// Pagination
$brandsPerPage = 8;
$totalPages = ceil($totalBrands / $brandsPerPage);
$currentPage = isset($_GET['page']) ? max(1, min((int)$_GET['page'], $totalPages)) : 1;
$offset = ($currentPage - 1) * $brandsPerPage;

// Query for brands with pagination
$brandsQuery = "SELECT b.id, b.name, b.logo_url, b.description, 
                      (SELECT COUNT(*) FROM products WHERE id = b.id) as product_count,
                      (SELECT COUNT(*) FROM verification_logs WHERE id = b.id) as verification_count,
                      b.status, b.created_at 
                FROM brands b" . $searchCondition . 
                " ORDER BY b.created_at DESC LIMIT ?, ?";

$brandsStmt = $conn->prepare($brandsQuery);
$paramIndex = 1;
foreach ($searchParams as $param) {
    $brandsStmt->bindValue($paramIndex, $param);
    $paramIndex++;
}
$brandsStmt->bindValue($paramIndex, $offset, PDO::PARAM_INT);
$brandsStmt->bindValue($paramIndex + 1, $brandsPerPage, PDO::PARAM_INT);
$brandsStmt->execute();
$brands = $brandsStmt->fetchAll(PDO::FETCH_ASSOC);

// Process form actions if any
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action']) && $_POST['action'] === 'update_brand') {
        $brandId = isset($_POST['brand_id']) ? (int)$_POST['brand_id'] : 0;
        $brandName = isset($_POST['brand_name']) ? trim($_POST['brand_name']) : '';
        $brandDescription = isset($_POST['brand_description']) ? trim($_POST['brand_description']) : '';
        $brandStatus = isset($_POST['brand_status']) ? $_POST['brand_status'] : 'Inactive';
        
        if ($brandId > 0 && !empty($brandName)) {
            $updateQuery = "UPDATE brands SET name = ?, description = ?, status = ? WHERE id = ?";
            $updateStmt = $conn->prepare($updateQuery);
            $updateStmt->execute([$brandName, $brandDescription, $brandStatus, $brandId]);
            
            // Handle logo upload if present
            if (isset($_FILES['brand_logo']) && $_FILES['brand_logo']['error'] === UPLOAD_ERR_OK) {
                $targetDir = "uploads/brand_logos/";
                if (!file_exists($targetDir)) {
                    mkdir($targetDir, 0777, true);
                }
                
                $fileName = basename($_FILES["brand_logo"]["name"]);
                $targetFilePath = $targetDir . time() . '_' . $fileName;
                $fileType = pathinfo($targetFilePath, PATHINFO_EXTENSION);
                
                // Allow certain file formats
                $allowTypes = array('jpg', 'png', 'jpeg', 'gif', 'webp');
                if (in_array(strtolower($fileType), $allowTypes)) {
                    // Upload file to server
                    if (move_uploaded_file($_FILES["brand_logo"]["tmp_name"], $targetFilePath)) {
                        // Update logo in database
                        $updateLogoQuery = "UPDATE brands SET logo_url = ? WHERE id = ?";
                        $updateLogoStmt = $conn->prepare($updateLogoQuery);
                        $updateLogoStmt->execute([$targetFilePath, $brandId]);
                    }
                }
            }
            
            // Redirect to avoid form resubmission
            header("Location: admin-brands.php?page=$currentPage&success=Brand updated successfully");
            exit;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Authena Admin - Brands</title>
    <!-- Favicon -->
    <link rel="shortcut icon" href="assets/images/favicon.ico" type="image/x-icon">
    <!-- Font Awesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="dash.css">
    <style>
        /* Additional styles specific to brands page */
        .brands-overview {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .overview-card {
            background: #fff;
            border-radius: 12px;
            padding: 20px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
            display: flex;
            align-items: center;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        
        .overview-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 15px rgba(0, 0, 0, 0.1);
        }
        
        .overview-card .icon {
            width: 50px;
            height: 50px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 15px;
            font-size: 20px;
        }
        
        .overview-card .icon.purple {
            background: rgba(79, 70, 229, 0.1);
            color: #4f46e5;
        }
        
        .overview-card .icon.blue {
            background: rgba(59, 130, 246, 0.1);
            color: #3b82f6;
        }
        
        .overview-card .icon.green {
            background: rgba(16, 185, 129, 0.1);
            color: #10b981;
        }
        
        .overview-card .icon.red {
            background: rgba(239, 68, 68, 0.1);
            color: #ef4444;
        }
        
        .overview-card .info h3 {
            font-size: 24px;
            font-weight: 600;
            margin: 0 0 5px;
        }
        
        .overview-card .info p {
            color: #6b7280;
            margin: 0;
        }
        
        .brands-tools {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }
        
        .search-filter {
            display: flex;
            gap: 15px;
        }
        
        .search-filter .search-box {
            position: relative;
        }
        
        .search-filter .search-box input {
            padding: 10px 15px 10px 40px;
            border-radius: 8px;
            border: 1px solid #e5e7eb;
            width: 280px;
            font-size: 14px;
        }
        
        .search-filter .search-box i {
            position: absolute;
            left: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: #9ca3af;
        }
        
        .status-filter select {
            padding: 10px 15px;
            border-radius: 8px;
            border: 1px solid #e5e7eb;
            background: #fff;
            font-size: 14px;
            color: #374151;
        }
        
        .add-brand-btn {
            background: #4f46e5;
            color: #fff;
            border: none;
            border-radius: 8px;
            padding: 10px 20px;
            font-size: 14px;
            font-weight: 500;
            display: flex;
            align-items: center;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }
        
        .add-brand-btn:hover {
            background: #4338ca;
        }
        
        .add-brand-btn i {
            margin-right: 8px;
        }
        
        .brands-table {
            background: #fff;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
        }
        
        .brands-table table {
            width: 100%;
            border-collapse: collapse;
        }
        
        .brands-table th {
            background: #f9fafb;
            padding: 15px;
            text-align: left;
            font-weight: 600;
            color: #374151;
            font-size: 14px;
        }
        
        .brands-table td {
            padding: 15px;
            border-top: 1px solid #e5e7eb;
            color: #6b7280;
            font-size: 14px;
        }
        
        .brands-table tr:hover {
            background: #f9fafb;
        }
        
        .brands-table .brand-info {
            display: flex;
            align-items: center;
        }
        
        .brands-table .brand-logo {
            width: 40px;
            height: 40px;
            border-radius: 8px;
            object-fit: cover;
            margin-right: 15px;
        }
        
        .brands-table .brand-name {
            font-weight: 500;
            color: #111827;
        }
        
        .status-badge {
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 500;
            display: inline-block;
        }
        
        .status-badge.active {
            background: rgba(16, 185, 129, 0.1);
            color: #10b981;
        }
        
        .status-badge.inactive {
            background: rgba(239, 68, 68, 0.1);
            color: #ef4444;
        }
        
        .actions-cell {
            display: flex;
            gap: 10px;
        }
        
        .action-btn {
            width: 32px;
            height: 32px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }
        
        .action-btn.view {
            background: rgba(79, 70, 229, 0.1);
            color: #4f46e5;
        }
        
        .action-btn.edit {
            background: rgba(59, 130, 246, 0.1);
            color: #3b82f6;
        }
        
        .action-btn.delete {
            background: rgba(239, 68, 68, 0.1);
            color: #ef4444;
        }
        
        .action-btn:hover {
            opacity: 0.8;
        }
        
        .pagination {
            display: flex;
            justify-content: flex-end;
            align-items: center;
            margin-top: 20px;
        }
        
        .pagination-info {
            margin-right: 20px;
            font-size: 14px;
            color: #6b7280;
        }
        
        .pagination-controls {
            display: flex;
            gap: 5px;
        }
        
        .pagination-btn {
            width: 36px;
            height: 36px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #fff;
            border: 1px solid #e5e7eb;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .pagination-btn.active {
            background: #4f46e5;
            color: #fff;
            border-color: #4f46e5;
        }
        
        .pagination-btn:hover:not(.active) {
            background: #f9fafb;
        }
        
        .pagination-btn.disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }
        
        .empty-state {
            background: #fff;
            border-radius: 12px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
            padding: 40px;
            text-align: center;
        }
        
        .empty-state i {
            font-size: 48px;
            color: #e5e7eb;
            margin-bottom: 20px;
        }
        
        .empty-state h3 {
            font-size: 18px;
            color: #374151;
            margin-bottom: 10px;
        }
        
        .empty-state p {
            color: #6b7280;
            margin-bottom: 20px;
        }
        
        /* Modal Styles */
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0,0,0,0.5);
            z-index: 1000;
            justify-content: center;
            align-items: center;
            overflow-y: auto;
        }
        
        .modal-content {
            background: white;
            padding: 30px;
            border-radius: 12px;
            width: 600px;
            max-width: 90%;
            box-shadow: 0 10px 25px rgba(0,0,0,0.1);
            max-height: 90vh;
            overflow-y: auto;
        }
        
        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }
        
        .modal-header h3 {
            margin: 0;
            color: #111827;
            font-size: 20px;
        }
        
        .close-modal {
            background: none;
            border: none;
            font-size: 20px;
            cursor: pointer;
            color: #6b7280;
        }
        
        .modal-body {
            margin-bottom: 20px;
        }
        
        .modal-footer {
            display: flex;
            justify-content: flex-end;
            gap: 10px;
        }
        
        .modal-btn {
            padding: 10px 20px;
            border-radius: 8px;
            font-weight: 500;
            cursor: pointer;
            font-size: 14px;
            transition: all 0.3s ease;
        }
        
        .btn-cancel {
            background: white;
            color: #374151;
            border: 1px solid #e5e7eb;
        }
        
        .btn-primary {
            background: #4f46e5;
            color: white;
            border: none;
        }
        
        .btn-danger {
            background: #ef4444;
            color: white;
            border: none;
        }
        
        .btn-primary:hover {
            background: #4338ca;
        }
        
        .btn-danger:hover {
            background: #dc2626;
        }
        
        .btn-cancel:hover {
            background: #f9fafb;
        }
        
        /* Brand View Modal */
        .brand-details {
            display: flex;
            flex-direction: column;
            gap: 20px;
        }
        
        .brand-header {
            display: flex;
            align-items: center;
            margin-bottom: 15px;
        }
        
        .brand-header img {
            width: 80px;
            height: 80px;
            border-radius: 12px;
            object-fit: cover;
            margin-right: 20px;
        }
        
        .brand-header-info h4 {
            margin: 0 0 5px;
            font-size: 22px;
            color: #111827;
        }
        
        .brand-stats {
            display: flex;
            gap: 15px;
            flex-wrap: wrap;
        }
        
        .brand-stat-item {
            background: #f9fafb;
            padding: 15px;
            border-radius: 8px;
            flex: 1;
            min-width: 120px;
        }
        
        .brand-stat-item h5 {
            margin: 0 0 5px;
            color: #6b7280;
            font-size: 14px;
            font-weight: 500;
        }
        
        .brand-stat-item p {
            margin: 0;
            color: #111827;
            font-size: 18px;
            font-weight: 600;
        }
        
        .brand-description {
            background: #f9fafb;
            padding: 15px;
            border-radius: 8px;
        }
        
        .brand-description h5 {
            margin: 0 0 10px;
            color: #6b7280;
            font-size: 14px;
            font-weight: 500;
        }
        
        .brand-description p {
            margin: 0;
            color: #374151;
            line-height: 1.5;
        }
        
        /* Edit Form Styles */
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            color: #374151;
        }
        
        .form-group input[type="text"],
        .form-group select,
        .form-group textarea {
            width: 100%;
            padding: 10px 15px;
            border-radius: 8px;
            border: 1px solid #e5e7eb;
            font-size: 14px;
            color: #374151;
            background: #fff;
        }
        
        .form-group textarea {
            height: 120px;
            resize: vertical;
        }
        
        .form-group .file-input-wrapper {
            position: relative;
            overflow: hidden;
            display: inline-block;
        }
        
        .form-group .file-input-wrapper input[type="file"] {
            position: absolute;
            left: 0;
            top: 0;
            opacity: 0;
            cursor: pointer;
            width: 100%;
            height: 100%;
        }
        
        .form-group .file-input-btn {
            background: #f3f4f6;
            padding: 10px 15px;
            border-radius: 8px;
            color: #374151;
            font-size: 14px;
            cursor: pointer;
            border: 1px dashed #d1d5db;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
        }
        
        .form-group .file-input-btn i {
            font-size: 16px;
        }
        
        .form-group .file-input-text {
            margin-top: 8px;
            font-size: 12px;
            color: #6b7280;
        }
        
        .form-group .current-logo {
            display: flex;
            align-items: center;
            margin-bottom: 15px;
        }
        
        .form-group .current-logo img {
            width: 50px;
            height: 50px;
            border-radius: 8px;
            object-fit: cover;
            margin-right: 15px;
        }
        
        .form-group .current-logo p {
            margin: 0;
            color: #6b7280;
            font-size: 14px;
        }
        
        /* Success Message */
        .alert {
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
        }
        
        .alert-success {
            background: rgba(16, 185, 129, 0.1);
            color: #10b981;
            border: 1px solid rgba(16, 185, 129, 0.2);
        }
        
        .alert i {
            margin-right: 10px;
            font-size: 16px;
        }
        
        @media (max-width: 1200px) {
            .brands-overview {
                grid-template-columns: repeat(2, 1fr);
            }
        }
        
        @media (max-width: 768px) {
            .brands-overview {
                grid-template-columns: 1fr;
            }
            
            .brands-tools {
                flex-direction: column;
                align-items: flex-start;
                gap: 15px;
            }
            
            .search-filter {
                width: 100%;
                flex-direction: column;
            }
            
            .search-filter .search-box input,
            .status-filter select {
                width: 100%;
            }
            
            .add-brand-btn {
                width: 100%;
                justify-content: center;
            }
            
            .modal-content {
                width: 95%;
                padding: 20px;
            }
            
            .brand-stats {
                flex-direction: column;
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
                    <li><a href="admin-brands.php" class="active"><i class="fas fa-copyright"></i> Brands</a></li>
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
                            <h4><?php echo htmlspecialchars($adminName); ?></h4>
                            <small>Admin</small>
                        </div>
                    </div>
                </div>
            </header>

            <!-- Brands Content -->
            <div class="dashboard">
                <div class="dashboard-header">
                    <h1>Brand Management</h1>
                    <div class="breadcrumb">
                        <a href="admin-dashboard.php">Home</a>
                        <span>/</span>
                        <a href="admin-brands.php">Brands</a>
                    </div>
                </div>
                
                <?php if(isset($_GET['success'])): ?>
                <div class="alert alert-success">
                    <i class="fas fa-check-circle"></i>
                    <?php echo htmlspecialchars($_GET['success']); ?>
                </div>
                <?php endif; ?>

                <!-- Brands Overview Cards -->
                <div class="brands-overview">
                    <div class="overview-card">
                        <div class="icon purple">
                            <i class="fas fa-copyright"></i>
                        </div>
                        <div class="info">
                            <h3><?php echo $totalBrands; ?></h3>
                            <p>Total Brands</p>
                        </div>
                    </div>
                    <div class="overview-card">
                        <div class="icon green">
                            <i class="fas fa-check-circle"></i>
                        </div>
                        <div class="info">
                            <h3><?php echo $activeBrands; ?></h3>
                            <p>Active Brands</p>
                        </div>
                    </div>
                    <div class="overview-card">
                        <div class="icon red">
                            <i class="fas fa-times-circle"></i>
                        </div>
                        <div class="info">
                            <h3><?php echo $inactiveBrands; ?></h3>
                            <p>Inactive Brands</p>
                        </div>
                    </div>
                    <div class="overview-card">
                        <div class="icon blue">
                            <i class="fas fa-boxes"></i>
                        </div>
                        <div class="info">
                            <h3><?php echo $totalProducts; ?></h3>
                            <p>Total Products</p>
                        </div>
                    </div>
                </div>

                <!-- Search and Filter Tools -->
                <div class="brands-tools">
                    <div class="search-filter">
                        <div class="search-box">
                            <i class="fas fa-search"></i>
                            <form action="" method="GET">
                                <input type="text" name="search" placeholder="Search brands..." value="<?php echo htmlspecialchars($searchQuery); ?>">
                                <?php if($statusFilter !== 'all'): ?>
                                    <input type="hidden" name="status" value="<?php echo htmlspecialchars($statusFilter); ?>">
                                <?php endif; ?>
                            </form>
                        </div>
                        <div class="status-filter">
                            <form action="" method="GET" id="statusFilterForm">
                                <select name="status" onchange="this.form.submit()">
                                <option value="all" <?php echo $statusFilter === 'all' ? 'selected' : ''; ?>>All Status</option>
                                    <option value="Active" <?php echo $statusFilter === 'Active' ? 'selected' : ''; ?>>Active</option>
                                    <option value="Inactive" <?php echo $statusFilter === 'Inactive' ? 'selected' : ''; ?>>Inactive</option>
                                </select>
                                <?php if(!empty($searchQuery)): ?>
                                    <input type="hidden" name="search" value="<?php echo htmlspecialchars($searchQuery); ?>">
                                <?php endif; ?>
                            </form>
                        </div>
                    </div>
                    <a href="admin-add-brand.php" class="add-brand-btn">
                        <i class="fas fa-plus"></i> Add New Brand
                    </a>
                </div>

                <!-- Brands Table -->
                <?php if(count($brands) > 0): ?>
                <div class="brands-table">
                    <table>
                        <thead>
                            <tr>
                                <th>Brand</th>
                                <th>Products</th>
                                <th>Verifications</th>
                                <th>Status</th>
                                <th>Date Added</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($brands as $brand): ?>
                            <tr>
                                <td>
                                    <div class="brand-info">
                                        <img src="<?php echo !empty($brand['logo_url']) ? htmlspecialchars($brand['logo_url']) : 'assets/images/placeholder-logo.png'; ?>" alt="<?php echo htmlspecialchars($brand['name']); ?>" class="brand-logo">
                                        <span class="brand-name"><?php echo htmlspecialchars($brand['name']); ?></span>
                                    </div>
                                </td>
                                <td><?php echo $brand['product_count']; ?></td>
                                <td><?php echo number_format($brand['verification_count']); ?></td>
                                <td>
                                    <span class="status-badge <?php echo strtolower($brand['status']); ?>">
                                        <?php echo $brand['status']; ?>
                                    </span>
                                </td>
                                <td><?php echo date('M d, Y', strtotime($brand['created_at'])); ?></td>
                                <td>
                                    <div class="actions-cell">
                                        <a href="javascript:void(0);" onclick="viewBrandDetails(<?php echo $brand['id']; ?>, '<?php echo addslashes(htmlspecialchars($brand['name'])); ?>', '<?php echo !empty($brand['logo_url']) ? addslashes(htmlspecialchars($brand['logo_url'])) : 'assets/images/placeholder-logo.png'; ?>', '<?php echo addslashes(htmlspecialchars($brand['description'] ?? 'No description available.')); ?>', '<?php echo addslashes(htmlspecialchars($brand['status'])); ?>', '<?php echo $brand['product_count']; ?>', '<?php echo $brand['verification_count']; ?>', '<?php echo date('M d, Y', strtotime($brand['created_at'])); ?>')" class="action-btn view" title="View Details">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="javascript:void(0);" onclick="editBrand(<?php echo $brand['id']; ?>, '<?php echo addslashes(htmlspecialchars($brand['name'])); ?>', '<?php echo addslashes(htmlspecialchars($brand['description'] ?? '')); ?>', '<?php echo addslashes(htmlspecialchars($brand['status'])); ?>', '<?php echo !empty($brand['logo_url']) ? addslashes(htmlspecialchars($brand['logo_url'])) : 'assets/images/placeholder-logo.png'; ?>')" class="action-btn edit" title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <a href="javascript:void(0);" onclick="confirmDelete(<?php echo $brand['id']; ?>, '<?php echo addslashes(htmlspecialchars($brand['name'])); ?>')" class="action-btn delete" title="Delete">
                                            <i class="fas fa-trash-alt"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div class="pagination">
                    <div class="pagination-info">
                        Showing <?php echo $totalBrands > 0 ? ($offset + 1) : 0; ?> to <?php echo min($offset + $brandsPerPage, $totalBrands); ?> of <?php echo $totalBrands; ?> brands
                    </div>
                    <div class="pagination-controls">
                        <?php if($currentPage > 1): ?>
                        <a href="?page=<?php echo $currentPage - 1; ?><?php echo !empty($searchQuery) ? '&search=' . urlencode($searchQuery) : ''; ?><?php echo $statusFilter !== 'all' ? '&status=' . urlencode($statusFilter) : ''; ?>" class="pagination-btn">
                            <i class="fas fa-chevron-left"></i>
                        </a>
                        <?php else: ?>
                        <span class="pagination-btn disabled">
                            <i class="fas fa-chevron-left"></i>
                        </span>
                        <?php endif; ?>
                        
                        <?php 
                        // Display limited number of pagination buttons
                        $maxButtons = 5;
                        $startPage = max(1, min($currentPage - floor($maxButtons / 2), $totalPages - $maxButtons + 1));
                        $endPage = min($startPage + $maxButtons - 1, $totalPages);
                        
                        for($i = $startPage; $i <= $endPage; $i++): 
                        ?>
                        <a href="?page=<?php echo $i; ?><?php echo !empty($searchQuery) ? '&search=' . urlencode($searchQuery) : ''; ?><?php echo $statusFilter !== 'all' ? '&status=' . urlencode($statusFilter) : ''; ?>" class="pagination-btn <?php echo $i === $currentPage ? 'active' : ''; ?>">
                            <?php echo $i; ?>
                        </a>
                        <?php endfor; ?>
                        
                        <?php if($currentPage < $totalPages): ?>
                        <a href="?page=<?php echo $currentPage + 1; ?><?php echo !empty($searchQuery) ? '&search=' . urlencode($searchQuery) : ''; ?><?php echo $statusFilter !== 'all' ? '&status=' . urlencode($statusFilter) : ''; ?>" class="pagination-btn">
                            <i class="fas fa-chevron-right"></i>
                        </a>
                        <?php else: ?>
                        <span class="pagination-btn disabled">
                            <i class="fas fa-chevron-right"></i>
                        </span>
                        <?php endif; ?>
                    </div>
                </div>
                <?php else: ?>
                <!-- Empty State -->
                <div class="empty-state">
                    <i class="fas fa-copyright"></i>
                    <h3>No brands found</h3>
                    <p>No brands match your search criteria. Try adjusting your filters or add a new brand.</p>
                    <a href="admin-add-brand.php" class="add-brand-btn">
                        <i class="fas fa-plus"></i> Add New Brand
                    </a>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- View Brand Modal -->
    <div id="viewBrandModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3><i class="fas fa-copyright"></i> Brand Details</h3>
                <button class="close-modal" onclick="closeModal('viewBrandModal')">&times;</button>
            </div>
            <div class="modal-body">
                <div class="brand-details">
                    <div class="brand-header">
                        <img id="viewBrandLogo" src="" alt="Brand Logo">
                        <div class="brand-header-info">
                            <h4 id="viewBrandName"></h4>
                            <span id="viewBrandStatus" class="status-badge"></span>
                        </div>
                    </div>
                    
                    <div class="brand-stats">
                        <div class="brand-stat-item">
                            <h5>Products</h5>
                            <p id="viewBrandProducts"></p>
                        </div>
                        <div class="brand-stat-item">
                            <h5>Verifications</h5>
                            <p id="viewBrandVerifications"></p>
                        </div>
                        <div class="brand-stat-item">
                            <h5>Added On</h5>
                            <p id="viewBrandDate"></p>
                        </div>
                    </div>
                    
                    <div class="brand-description">
                        <h5>Description</h5>
                        <p id="viewBrandDescription"></p>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button class="modal-btn btn-cancel" onclick="closeModal('viewBrandModal')">Close</button>
                <button id="viewModalEditBtn" class="modal-btn btn-primary">Edit Brand</button>
            </div>
        </div>
    </div>

    <!-- Edit Brand Modal -->
    <div id="editBrandModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3><i class="fas fa-edit"></i> Edit Brand</h3>
                <button class="close-modal" onclick="closeModal('editBrandModal')">&times;</button>
            </div>
            <div class="modal-body">
                <form id="editBrandForm" action="" method="POST" enctype="multipart/form-data">
                    <input type="hidden" id="editBrandId" name="brand_id">
                    <input type="hidden" name="action" value="update_brand">
                    
                    <div class="form-group">
                        <label for="brandName">Brand Name</label>
                        <input type="text" id="editBrandName" name="brand_name" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="brandDescription">Description</label>
                        <textarea id="editBrandDescription" name="brand_description"></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label for="brandStatus">Status</label>
                        <select id="editBrandStatus" name="brand_status">
                            <option value="Active">Active</option>
                            <option value="Inactive">Inactive</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label>Logo</label>
                        <div class="current-logo">
                            <img id="editBrandLogo" src="" alt="Current Logo">
                            <p>Current logo. Upload a new one to change it.</p>
                        </div>
                        <div class="file-input-wrapper">
                            <div class="file-input-btn">
                                <i class="fas fa-cloud-upload-alt"></i>
                                <span>Choose File</span>
                            </div>
                            <input type="file" id="brandLogo" name="brand_logo" accept="image/*">
                        </div>
                        <div class="file-input-text">Supported formats: JPG, PNG, GIF, WEBP. Max size: 2MB</div>
                    </div>
                
                    <div class="modal-footer">
                        <button type="button" class="modal-btn btn-cancel" onclick="closeModal('editBrandModal')">Cancel</button>
                        <button type="submit" class="modal-btn btn-primary">Save Changes</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div id="deleteModal" class="modal">
        <div class="modal-content" style="width: 400px;">
            <div class="modal-header">
                <h3><i class="fas fa-exclamation-triangle" style="color: #ef4444;"></i> Confirm Deletion</h3>
                <button class="close-modal" onclick="closeModal('deleteModal')">&times;</button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to delete <strong id="deleteBrandName"></strong>? This action cannot be undone.</p>
                <p style="color: #ef4444; font-size: 13px; margin-top: 10px;">Warning: All products and verification logs associated with this brand will also be deleted.</p>
            </div>
            <div class="modal-footer">
                <button class="modal-btn btn-cancel" onclick="closeModal('deleteModal')">Cancel</button>
                <button id="confirmDeleteBtn" class="modal-btn btn-danger">Delete</button>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script>
        // Toggle sidebar
        document.getElementById('toggleMenu').addEventListener('click', function() {
            document.querySelector('.sidebar').classList.toggle('active');
        });
        
        // View brand details
        function viewBrandDetails(id, name, logo, description, status, products, verifications, date) {
            document.getElementById('viewBrandName').innerText = name;
            document.getElementById('viewBrandLogo').src = logo;
            document.getElementById('viewBrandDescription').innerText = description;
            document.getElementById('viewBrandProducts').innerText = products;
            document.getElementById('viewBrandVerifications').innerText = verifications;
            document.getElementById('viewBrandDate').innerText = date;
            
            const statusBadge = document.getElementById('viewBrandStatus');
            statusBadge.innerText = status;
            statusBadge.className = 'status-badge ' + status.toLowerCase();
            
            document.getElementById('viewModalEditBtn').onclick = function() {
                closeModal('viewBrandModal');
                editBrand(id, name, description, status, logo);
            };
            
            openModal('viewBrandModal');
        }
        
        // Edit brand
        function editBrand(id, name, description, status, logo) {
            document.getElementById('editBrandId').value = id;
            document.getElementById('editBrandName').value = name;
            document.getElementById('editBrandDescription').value = description;
            document.getElementById('editBrandStatus').value = status;
            document.getElementById('editBrandLogo').src = logo;
            
            openModal('editBrandModal');
        }
        
        // Delete confirmation
        function confirmDelete(brandId, brandName) {
            document.getElementById('deleteBrandName').innerText = brandName;
            
            document.getElementById('confirmDeleteBtn').onclick = function() {
    window.location.href = `admin-delete-brand.php?id=${brandId}&page=<?php echo $currentPage; ?><?php echo !empty($searchQuery) ? '&search=' . urlencode($searchQuery) : ''; ?><?php echo $statusFilter !== 'all' ? '&status=' . urlencode($statusFilter) : ''; ?>`;
};
            
            openModal('deleteModal');
        }
        
        // Open modal
        function openModal(modalId) {
            document.getElementById(modalId).style.display = 'flex';
            document.body.style.overflow = 'hidden'; // Prevent scrolling
        }
        
        // Close modal
        function closeModal(modalId) {
            document.getElementById(modalId).style.display = 'none';
            document.body.style.overflow = 'auto'; // Re-enable scrolling
        }
        
        // Close modal if clicked outside
        window.onclick = function(event) {
            const modals = document.getElementsByClassName('modal');
            for (let i = 0; i < modals.length; i++) {
                if (event.target === modals[i]) {
                    closeModal(modals[i].id);
                }
            }
        };
        
        // Preview uploaded file
        document.getElementById('brandLogo').addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    document.getElementById('editBrandLogo').src = e.target.result;
                };
                reader.readAsDataURL(file);
            }
        });
        
        // Notification dropdown functionality
        document.querySelector('.notifications').addEventListener('click', function() {
            // Here you would toggle a notification dropdown
            console.log('Notification clicked');
        });
        
        // Check for success message and remove after 5 seconds
        const successAlert = document.querySelector('.alert-success');
        if (successAlert) {
            setTimeout(function() {
                successAlert.style.opacity = '0';
                setTimeout(function() {
                    successAlert.style.display = 'none';
                }, 500);
            }, 5000);
        }
    </script>
</body>
</html>