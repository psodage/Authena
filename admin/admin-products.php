<?php
// Start session for authentication
session_start();

// Database connection
require_once 'db.php';

// Initialize variables
$search = isset($_GET['search']) ? $_GET['search'] : '';
$category = isset($_GET['category']) ? $_GET['category'] : '';
$brand = isset($_GET['brand']) ? $_GET['brand'] : '';
$status = isset($_GET['status']) ? $_GET['status'] : '';
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$items_per_page = 10;
$offset = ($page - 1) * $items_per_page;

// Base query
$query = "SELECT p.*, b.name as brand_name, c.name as category_name 
          FROM products p
          LEFT JOIN brands b ON p.brand_id = b.id
          LEFT JOIN categories c ON p.category_id = c.id
          WHERE 1=1";

// Add filters to query
$params = []; // Initialize params array
if (!empty($search)) {
    $query .= " AND (p.name LIKE ? OR p.product_code LIKE ? OR p.description LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

if (!empty($category)) {
    $query .= " AND p.category_id = ?";
    $params[] = $category;
}

if (!empty($brand)) {
    $query .= " AND p.brand_id = ?";
    $params[] = $brand;
}

if (!empty($status)) {
    $query .= " AND p.status = ?";
    $params[] = $status;
}

// Count total records for pagination
$count_query = str_replace("p.*, b.name as brand_name, c.name as category_name", "COUNT(*) as total", $query);
$stmt = $conn->prepare($count_query);
if (!empty($params)) {
    $types = str_repeat('s', count($params));
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$total_result = $stmt->get_result()->fetch_assoc();
$total_items = $total_result['total'];
$total_pages = ceil($total_items / $items_per_page);

// Final query with pagination
$query .= " ORDER BY p.created_at DESC LIMIT $offset, $items_per_page";
$stmt = $conn->prepare($query);
if (!empty($params)) {
    $types = str_repeat('s', count($params));
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$products = $stmt->get_result();

// Get all categories for filter
$cat_query = "SELECT id, name FROM categories ORDER BY name";
$categories = $conn->query($cat_query);

// Get all brands for filter
$brand_query = "SELECT id, name FROM brands ORDER BY name";
$brands = $conn->query($brand_query);

// Get admin info
$adminName = $_SESSION['admin_name'] ?? 'Admin User';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Product Management | Authena Admin</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="products.css">
    <link rel="stylesheet" href="dash.css">
    <style>
        /* Additional styles for modals */
        .modal-xl {
            max-width: 90%;
        }
        .product-info-row {
            margin-bottom: 1rem;
        }
        .product-info-label {
            font-weight: 600;
            color: #495057;
        }
        .product-image-lg {
            max-width: 100%;
            height: auto;
            border-radius: 5px;
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
                    <li><a href="admin-dashboard.php" ><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
                </ul>
                
                <div class="menu-category">Product Management</div>
                <ul>
                    <li><a href="admin-products.php" class="active"><i class="fas fa-boxes"></i> Products</a></li>
                    <li><a href="admin-add-product.php" ><i class="fas fa-plus-circle"></i> Add Product</a></li>
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
            <div class="dashboard">
                <div class="dashboard-header">
                    <h1>Products</h1>
                    <div class="breadcrumb">
                        <a href="admin-dashboard.php">Home</a>
                        <span>/</span>
                        <a href="admin-products.php">Products</a>
                    </div>
                </div>
            <main>
                <!-- Page Content -->
                <div class="content-wrapper">
                    <!-- Page Header -->
                    <div class="page-header d-flex justify-content-between align-items-center">
                        <div>
                            <h1 class="h3 mb-0">Product Management</h1>
                        </div>
                        <div>
                            <a href="#" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addProductModal">
                                <i class="fas fa-plus me-2"></i> Add New Product
                            </a>
                        </div>
                    </div>

                    <!-- Stats Cards -->
                    <div class="row mb-4">
                        <div class="col-md-3">
                            <div class="card stats-card mb-3">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <h6 class="text-muted mb-2">Total Products</h6>
                                            <h3 class="mb-0"><?php echo $total_items; ?></h3>
                                        </div>
                                        <div class="bg-light p-3 rounded-circle">
                                            <i class="fas fa-box text-primary"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-3">
                            <div class="card stats-card mb-3">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <h6 class="text-muted mb-2">Active Products</h6>
                                            <?php
                                            $active_query = "SELECT COUNT(*) as count FROM products WHERE status = 'active'";
                                            $active_result = $conn->query($active_query)->fetch_assoc();
                                            ?>
                                            <h3 class="mb-0"><?php echo $active_result['count']; ?></h3>
                                        </div>
                                        <div class="bg-light p-3 rounded-circle">
                                            <i class="fas fa-check-circle text-success"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-3">
                            <div class="card stats-card mb-3">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <h6 class="text-muted mb-2">Total Brands</h6>
                                            <?php
                                            $brands_count = $conn->query("SELECT COUNT(*) as count FROM brands")->fetch_assoc();
                                            ?>
                                            <h3 class="mb-0"><?php echo $brands_count['count']; ?></h3>
                                        </div>
                                        <div class="bg-light p-3 rounded-circle">
                                            <i class="fas fa-copyright text-info"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-3">
                            <div class="card stats-card mb-3">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <h6 class="text-muted mb-2">Verification Scans</h6>
                                            <?php
                                            $scans_query = "SELECT COUNT(*) as count FROM verification_logs";
                                            $scans_result = $conn->query($scans_query)->fetch_assoc();
                                            ?>
                                            <h3 class="mb-0"><?php echo $scans_result['count']; ?></h3>
                                        </div>
                                        <div class="bg-light p-3 rounded-circle">
                                            <i class="fas fa-qrcode text-warning"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Search and Filter -->
                    <div class="card mb-4">
                        <div class="card-body">
                            <form method="GET" action="admin-products.php" class="row g-3">
                                <div class="col-md-4">
                                    <div class="search-wrapper">
                                        <i class="fas fa-search search-icon"></i>
                                        <input type="text" name="search" class="form-control search-input" placeholder="Search products..." value="<?php echo htmlspecialchars($search); ?>">
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <select name="category" class="form-select">
                                        <option value="">All Categories</option>
                                        <?php while ($cat = $categories->fetch_assoc()): ?>
                                            <option value="<?php echo $cat['id']; ?>" <?php echo ($cat['id'] == $category) ? 'selected' : ''; ?>>
                                                <?php echo htmlspecialchars($cat['name']); ?>
                                            </option>
                                        <?php endwhile; ?>
                                    </select>
                                </div>
                                <div class="col-md-2">
                                    <select name="brand" class="form-select">
                                        <option value="">All Brands</option>
                                        <?php while ($b = $brands->fetch_assoc()): ?>
                                            <option value="<?php echo $b['id']; ?>" <?php echo ($b['id'] == $brand) ? 'selected' : ''; ?>>
                                                <?php echo htmlspecialchars($b['name']); ?>
                                            </option>
                                        <?php endwhile; ?>
                                    </select>
                                </div>
                                <div class="col-md-2">
                                    <select name="status" class="form-select">
                                        <option value="">All Status</option>
                                        <option value="active" <?php echo ($status == 'active') ? 'selected' : ''; ?>>Active</option>
                                        <option value="inactive" <?php echo ($status == 'inactive') ? 'selected' : ''; ?>>Inactive</option>
                                        <option value="pending" <?php echo ($status == 'pending') ? 'selected' : ''; ?>>Pending</option>
                                    </select>
                                </div>
                                <div class="col-md-2">
                                    <button type="submit" class="btn btn-primary w-100">
                                        <i class="fas fa-filter me-2"></i> Filter
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>

                    <!-- Products Table -->
                    <div class="card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h5 class="mb-0">Products List</h5>
                            <div>
                                <div class="btn-group">
                                    <button type="button" class="btn btn-sm btn-outline-secondary">
                                        <i class="fas fa-th-large"></i>
                                    </button>
                                    <button type="button" class="btn btn-sm btn-outline-secondary active">
                                        <i class="fas fa-list"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-hover align-middle mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th scope="col" width="50">#</th>
                                            <th scope="col" width="80">Image</th>
                                            <th scope="col">Product Name</th>
                                            <th scope="col">Product Code</th>
                                            <th scope="col">Brand</th>
                                            <th scope="col">Category</th>
                                            <th scope="col">Created</th>
                                            <th scope="col">Status</th>
                                            <th scope="col" class="text-end actions-column">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php 
                                        $counter = $offset + 1;
                                        if ($products->num_rows > 0):
                                            while ($product = $products->fetch_assoc()): 
                                        ?>
                                        <tr>
                                            <td><?php echo $counter++; ?></td>
                                            <td>
                                                <?php if(!empty($product['image_url'])): ?>
                                                    <img src="<?php echo htmlspecialchars($product['image_url']); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>" class="product-thumbnail">
                                                <?php else: ?>
                                                    <img src="assets/img/placeholder.png" alt="Placeholder" class="product-thumbnail">
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <a href="#" class="text-decoration-none fw-medium view-product" data-bs-toggle="modal" data-bs-target="#viewProductModal" 
                                                   data-id="<?php echo $product['id']; ?>"
                                                   data-name="<?php echo htmlspecialchars($product['name']); ?>"
                                                   data-code="<?php echo htmlspecialchars($product['product_code']); ?>"
                                                   data-brand="<?php echo htmlspecialchars($product['brand_name']); ?>"
                                                   data-category="<?php echo htmlspecialchars($product['category_name']); ?>"
                                                   data-status="<?php echo htmlspecialchars($product['status']); ?>"
                                                   data-created="<?php echo date('M d, Y', strtotime($product['created_at'])); ?>"
                                                   data-image="<?php echo !empty($product['image_url']) ? htmlspecialchars($product['image_url']) : 'assets/img/placeholder.png'; ?>"
                                                   data-description="<?php echo htmlspecialchars($product['description'] ?? ''); ?>">
                                                    <?php echo htmlspecialchars($product['name']); ?>
                                                </a>
                                            </td>
                                            <td><?php echo htmlspecialchars($product['product_code']); ?></td>
                                            <td><?php echo htmlspecialchars($product['brand_name']); ?></td>
                                            <td><?php echo htmlspecialchars($product['category_name']); ?></td>
                                            <td><?php echo date('M d, Y', strtotime($product['created_at'])); ?></td>
                                            <td>
                                                <?php if($product['status'] == 'active'): ?>
                                                    <span class="badge status-active">Active</span>
                                                <?php elseif($product['status'] == 'inactive'): ?>
                                                    <span class="badge status-inactive">Inactive</span>
                                                <?php else: ?>
                                                    <span class="badge status-pending">Pending</span>
                                                <?php endif; ?>
                                            </td>
                                            <td class="text-end">
                                                <div class="dropdown">
                                                    <button class="btn btn-sm btn-light" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                                        <i class="fas fa-ellipsis-v"></i>
                                                    </button>
                                                    <ul class="dropdown-menu dropdown-menu-end">
                                                        <li>
                                                            <a class="dropdown-item edit-product" href="#" data-bs-toggle="modal" data-bs-target="#editProductModal"
                                                               data-id="<?php echo $product['id']; ?>"
                                                               data-name="<?php echo htmlspecialchars($product['name']); ?>"
                                                               data-code="<?php echo htmlspecialchars($product['product_code']); ?>"
                                                               data-brand-id="<?php echo $product['brand_id']; ?>"
                                                               data-category-id="<?php echo $product['category_id']; ?>"
                                                               data-status="<?php echo htmlspecialchars($product['status']); ?>"
                                                               data-description="<?php echo htmlspecialchars($product['description'] ?? ''); ?>"
                                                               data-image="<?php echo !empty($product['image_url']) ? htmlspecialchars($product['image_url']) : ''; ?>">
                                                                <i class="fas fa-edit me-2 text-primary"></i> Edit
                                                            </a>
                                                        </li>
                                                        <li>
    <a class="dropdown-item delete-product" href="#" data-bs-toggle="modal" data-bs-target="#deleteProductModal"
       data-id="<?php echo $product['id']; ?>"
       data-name="<?php echo htmlspecialchars($product['name']); ?>">
        <i class="fas fa-trash-alt me-2 text-danger"></i> Delete
    </a>
</li>
                                                        <li>
                                                            <a class="dropdown-item view-product" href="#" data-bs-toggle="modal" data-bs-target="#viewProductModal"
                                                               data-id="<?php echo $product['id']; ?>"
                                                               data-name="<?php echo htmlspecialchars($product['name']); ?>"
                                                               data-code="<?php echo htmlspecialchars($product['product_code']); ?>"
                                                               data-brand="<?php echo htmlspecialchars($product['brand_name']); ?>"
                                                               data-category="<?php echo htmlspecialchars($product['category_name']); ?>"
                                                               data-status="<?php echo htmlspecialchars($product['status']); ?>"
                                                               data-created="<?php echo date('M d, Y', strtotime($product['created_at'])); ?>"
                                                               data-image="<?php echo !empty($product['image_url']) ? htmlspecialchars($product['image_url']) : 'assets/img/placeholder.png'; ?>"
                                                               data-description="<?php echo htmlspecialchars($product['description'] ?? ''); ?>">
                                                                <i class="fas fa-eye me-2 text-info"></i> View Details
                                                            </a>
                                                        </li>
                                                     
                                                    </ul>
                                                </div>
                                            </td>
                                        </tr>
                                        <?php 
                                            endwhile;
                                        else: 
                                        ?>
                                        <tr>
                                            <td colspan="9" class="text-center py-4">
                                                <div class="d-flex flex-column align-items-center">
                                                    <i class="fas fa-box-open fs-1 text-secondary mb-3"></i>
                                                    <h5>No products found</h5>
                                                    <p class="text-muted">Try adjusting your search or filter criteria</p>
                                                    <a href="#" class="btn btn-sm btn-primary mt-2" data-bs-toggle="modal" data-bs-target="#addProductModal">
                                                        <i class="fas fa-plus me-1"></i> Add New Product
                                                    </a>
                                                </div>
                                            </td>
                                        </tr>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        
                        <!-- Pagination -->
                        <?php if ($total_pages > 1): ?>
                        <div class="card-footer bg-white">
                            <nav>
                                <ul class="pagination justify-content-center mb-0">
                                    <li class="page-item <?php echo ($page <= 1) ? 'disabled' : ''; ?>">
                                        <a class="page-link" href="?page=<?php echo $page - 1; ?>&search=<?php echo urlencode($search); ?>&category=<?php echo urlencode($category); ?>&brand=<?php echo urlencode($brand); ?>&status=<?php echo urlencode($status); ?>">
                                            <i class="fas fa-chevron-left"></i>
                                        </a>
                                    </li>
                                    
                                    <?php for ($i = max(1, $page - 2); $i <= min($page + 2, $total_pages); $i++): ?>
                                        <li class="page-item <?php echo ($page == $i) ? 'active' : ''; ?>">
                                            <a class="page-link" href="?page=<?php echo $i; ?>&search=<?php echo urlencode($search); ?>&category=<?php echo urlencode($category); ?>&brand=<?php echo urlencode($brand); ?>&status=<?php echo urlencode($status); ?>">
                                                <?php echo $i; ?>
                                            </a>
                                        </li>
                                    <?php endfor; ?>
                                    
                                    <li class="page-item <?php echo ($page >= $total_pages) ? 'disabled' : ''; ?>">
                                        <a class="page-link" href="?page=<?php echo $page + 1; ?>&search=<?php echo urlencode($search); ?>&category=<?php echo urlencode($category); ?>&brand=<?php echo urlencode($brand); ?>&status=<?php echo urlencode($status); ?>">
                                            <i class="fas fa-chevron-right"></i>
                                        </a>
                                    </li>
                                </ul>
                            </nav>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </main>
            
            <!-- View Product Modal -->
            <div class="modal fade" id="viewProductModal" tabindex="-1" aria-labelledby="viewProductModalLabel" aria-hidden="true">
                <div class="modal-dialog modal-xl">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="viewProductModalLabel">Product Details</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <div class="row">
                                <div class="col-md-5">
                                    <img id="productImage" src="" alt="Product Image" class="product-image-lg mb-3">
                                </div>
                                <div class="col-md-7">
                                    <div class="product-info">
                                        <h3 id="productName"></h3>
                                        
                                        <div class="product-info-row">
                                            <div class="row">
                                                <div class="col-md-4">
                                                    <div class="product-info-label">Product Code</div>
                                                    <div id="productCode"></div>
                                                </div>
                                                <div class="col-md-4">
                                                    <div class="product-info-label">Brand</div>
                                                    <div id="productBrand"></div>
                                                </div>
                                                <div class="col-md-4">
                                                    <div class="product-info-label">Category</div>
                                                    <div id="productCategory"></div>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <div class="product-info-row">
                                            <div class="row">
                                                <div class="col-md-4">
                                                    <div class="product-info-label">Status</div>
                                                    <div id="productStatus"></div>
                                                </div>
                                                <div class="col-md-4">
                                                    <div class="product-info-label">Created Date</div>
                                                    <div id="productCreated"></div>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <div class="product-info-row">
                                            <div class="product-info-label">Description</div>
                                            <div id="productDescription" class="mt-2"></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                            <button type="button" class="btn btn-primary edit-from-view">Edit Product</button>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Edit Product Modal -->
            <div class="modal fade" id="editProductModal" tabindex="-1" aria-labelledby="editProductModalLabel" aria-hidden="true">
                <div class="modal-dialog modal-lg">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="editProductModalLabel">Edit Product</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <form id="editProductForm" action="admin-update-product.php" method="POST" enctype="multipart/form-data">
                            <div class="modal-body">
                                <input type="hidden" id="editProductId" name="product_id">
                                
                                <div class="mb-3">
                                    <label for="editProductName" class="form-label">Product Name</label>
                                    <input type="text" class="form-control" id="editProductName" name="name" required>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="editProductCode" class="form-label">Product Code</label>
                                    <input type="text" class="form-control" id="editProductCode" name="product_code" required>
                                </div>
                                
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="editProductBrand" class="form-label">Brand</label>
                                            <select class="form-select" id="editProductBrand" name="brand_id" required>
                                            <option value="">Select Brand</option>
                                <?php 
                                $brand_options = $conn->query("SELECT id, name FROM brands ORDER BY name");
                                while ($brand_option = $brand_options->fetch_assoc()): 
                                ?>
                                    <option value="<?php echo $brand_option['id']; ?>">
                                        <?php echo htmlspecialchars($brand_option['name']); ?>
                                    </option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="editProductCategory" class="form-label">Category</label>
                            <select class="form-select" id="editProductCategory" name="category_id" required>
                                <option value="">Select Category</option>
                                <?php 
                                $category_options = $conn->query("SELECT id, name FROM categories ORDER BY name");
                                while ($category_option = $category_options->fetch_assoc()): 
                                ?>
                                    <option value="<?php echo $category_option['id']; ?>">
                                        <?php echo htmlspecialchars($category_option['name']); ?>
                                    </option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                    </div>
                </div>
                
                <div class="mb-3">
                    <label for="editProductStatus" class="form-label">Status</label>
                    <select class="form-select" id="editProductStatus" name="status" required>
                        <option value="active">Active</option>
                        <option value="inactive">Inactive</option>
                        <option value="pending">Pending</option>
                    </select>
                </div>
                
                <div class="mb-3">
                    <label for="editProductDescription" class="form-label">Description</label>
                    <textarea class="form-control" id="editProductDescription" name="description" rows="4"></textarea>
                </div>
                
                <div class="mb-3">
                    <label for="editProductImage" class="form-label">Product Image</label>
                    <input type="file" class="form-control" id="editProductImage" name="image">
                    <div class="form-text">Leave empty to keep the current image.</div>
                    <div id="currentImageContainer" class="mt-2">
                        <img id="currentImage" src="" alt="Current Product Image" style="max-height: 100px; display: none;">
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-primary">Update Product</button>
            </div>
        </form>
    </div>
</div>
</div>

<!-- Add Product Modal -->
<div class="modal fade" id="addProductModal" tabindex="-1" aria-labelledby="addProductModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addProductModalLabel">Add New Product</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="admin-add-product-handler.php" method="POST" enctype="multipart/form-data">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="productName" class="form-label">Product Name</label>
                        <input type="text" class="form-control" id="productName" name="name" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="productCode" class="form-label">Product Code</label>
                        <input type="text" class="form-control" id="productCode" name="product_code" required>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="productBrand" class="form-label">Brand</label>
                                <select class="form-select" id="productBrand" name="brand_id" required>
                                    <option value="">Select Brand</option>
                                    <?php 
                                    $brand_options = $conn->query("SELECT id, name FROM brands ORDER BY name");
                                    while ($brand_option = $brand_options->fetch_assoc()): 
                                    ?>
                                        <option value="<?php echo $brand_option['id']; ?>">
                                            <?php echo htmlspecialchars($brand_option['name']); ?>
                                        </option>
                                    <?php endwhile; ?>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="productCategory" class="form-label">Category</label>
                                <select class="form-select" id="productCategory" name="category_id" required>
                                    <option value="">Select Category</option>
                                    <?php 
                                    $category_options = $conn->query("SELECT id, name FROM categories ORDER BY name");
                                    while ($category_option = $category_options->fetch_assoc()): 
                                    ?>
                                        <option value="<?php echo $category_option['id']; ?>">
                                            <?php echo htmlspecialchars($category_option['name']); ?>
                                        </option>
                                    <?php endwhile; ?>
                                </select>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="productStatus" class="form-label">Status</label>
                        <select class="form-select" id="productStatus" name="status" required>
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                            <option value="pending">Pending</option>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label for="productDescription" class="form-label">Description</label>
                        <textarea class="form-control" id="productDescription" name="description" rows="4"></textarea>
                    </div>
                    
                    <div class="mb-3">
                        <label for="productImage" class="form-label">Product Image</label>
                        <input type="file" class="form-control" id="productImage" name="image">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Add Product</button>
                </div>
            </form>
        </div>
    </div>
</div>
<div class="modal fade" id="deleteProductModal" tabindex="-1" aria-labelledby="deleteProductModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteProductModalLabel">Confirm Deletion</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to delete the product: <strong id="deleteProductName"></strong>?</p>
                <p class="text-danger"><i class="fas fa-exclamation-triangle me-2"></i> This action cannot be undone.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <form id="deleteProductForm" action="admin-delete-product.php" method="POST">
                    <input type="hidden" id="deleteProductId" name="product_id">
                    <button type="submit" class="btn btn-danger">Delete Product</button>
                </form>
            </div>
        </div>
    </div>
</div>
<!-- Bootstrap and other scripts -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
    document.querySelectorAll('.delete-product').forEach(item => {
    item.addEventListener('click', event => {
        let button = event.currentTarget;
        let id = button.getAttribute('data-id');
        let name = button.getAttribute('data-name');
        
        document.getElementById('deleteProductId').value = id;
        document.getElementById('deleteProductName').textContent = name;
    });
});
    // Toggle sidebar
    document.getElementById('toggleMenu').addEventListener('click', function() {
        document.querySelector('.sidebar').classList.toggle('collapsed');
        document.querySelector('.main-content').classList.toggle('expanded');
    });
    
    // Handle view product modal
    document.querySelectorAll('.view-product').forEach(item => {
        item.addEventListener('click', event => {
            let button = event.currentTarget;
            let id = button.getAttribute('data-id');
            let name = button.getAttribute('data-name');
            let code = button.getAttribute('data-code');
            let brand = button.getAttribute('data-brand');
            let category = button.getAttribute('data-category');
            let status = button.getAttribute('data-status');
            let created = button.getAttribute('data-created');
            let image = button.getAttribute('data-image');
            let description = button.getAttribute('data-description');
            
            document.getElementById('productName').textContent = name;
            document.getElementById('productCode').textContent = code;
            document.getElementById('productBrand').textContent = brand;
            document.getElementById('productCategory').textContent = category;
            document.getElementById('productCreated').textContent = created;
            document.getElementById('productDescription').textContent = description || 'No description available';
            
            let statusElement = document.getElementById('productStatus');
            statusElement.textContent = status.charAt(0).toUpperCase() + status.slice(1);
            statusElement.className = '';
            statusElement.classList.add('badge', `status-${status}`);
            
            document.getElementById('productImage').src = image;
            
            // Set up the edit button in view modal
            document.querySelector('.edit-from-view').setAttribute('data-id', id);
            document.querySelector('.edit-from-view').addEventListener('click', function() {
                $('#viewProductModal').modal('hide');
                // Trigger the edit modal for the same product
                let editButton = document.querySelector(`.edit-product[data-id="${id}"]`);
                if (editButton) {
                    editButton.click();
                }
            });
        });
    });
    
    // Handle edit product modal
    document.querySelectorAll('.edit-product').forEach(item => {
        item.addEventListener('click', event => {
            let button = event.currentTarget;
            let id = button.getAttribute('data-id');
            let name = button.getAttribute('data-name');
            let code = button.getAttribute('data-code');
            let brandId = button.getAttribute('data-brand-id');
            let categoryId = button.getAttribute('data-category-id');
            let status = button.getAttribute('data-status');
            let description = button.getAttribute('data-description');
            let image = button.getAttribute('data-image');
            
            document.getElementById('editProductId').value = id;
            document.getElementById('editProductName').value = name;
            document.getElementById('editProductCode').value = code;
            document.getElementById('editProductBrand').value = brandId;
            document.getElementById('editProductCategory').value = categoryId;
            document.getElementById('editProductStatus').value = status;
            document.getElementById('editProductDescription').value = description;
            
            if (image) {
                document.getElementById('currentImage').src = image;
                document.getElementById('currentImage').style.display = 'block';
            } else {
                document.getElementById('currentImage').style.display = 'none';
            }
        });
    });
</script>
</body>
</html>