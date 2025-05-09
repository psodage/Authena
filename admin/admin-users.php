<?php
session_start();

// Check if admin is logged in

// Database connection
$host = "localhost";
$dbname = "authena";
$username = "root";
$password = "";

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

// Pagination settings
$usersPerPage = 10;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $usersPerPage;

// Search functionality
$search = isset($_GET['search']) ? $_GET['search'] : '';
$searchCondition = '';
$params = [];

if (!empty($search)) {
    $searchCondition = "WHERE username LIKE :search OR email LIKE :search OR first_name LIKE :search OR last_name LIKE :search";
    $params[':search'] = "%$search%";
}

// Filter by status
$status = isset($_GET['status']) ? $_GET['status'] : '';
if (!empty($status)) {
    $searchCondition = empty($searchCondition) ? "WHERE status = :status" : "$searchCondition AND status = :status";
    $params[':status'] = $status;
}

// Sort functionality
$sortBy = isset($_GET['sort']) ? $_GET['sort'] : 'id';
$sortOrder = isset($_GET['order']) ? $_GET['order'] : 'ASC';

// Valid sort columns
$validSortColumns = ['id', 'username', 'email', 'first_name', 'last_name', 'verification_count', 'report_count', 'status', 'created_at'];
if (!in_array($sortBy, $validSortColumns)) {
    $sortBy = 'id';
}

// Fetch users with pagination, search, filter, and sort
$query = "SELECT * FROM users $searchCondition ORDER BY $sortBy $sortOrder LIMIT :offset, :limit";
$stmt = $pdo->prepare($query);

$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->bindValue(':limit', $usersPerPage, PDO::PARAM_INT);

foreach ($params as $key => $value) {
    $stmt->bindValue($key, $value);
}

$stmt->execute();
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Count total users for pagination
$countQuery = "SELECT COUNT(*) FROM users $searchCondition";
$countStmt = $pdo->prepare($countQuery);

foreach ($params as $key => $value) {
    $countStmt->bindValue($key, $value);
}

$countStmt->execute();
$totalUsers = $countStmt->fetchColumn();
$totalPages = ceil($totalUsers / $usersPerPage);

// Get admin info
$adminName = $_SESSION['admin_name'] ?? 'Admin User';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Authena Admin - Users</title>
    <!-- Favicon -->
    <link rel="shortcut icon" href="assets/images/favicon.ico" type="image/x-icon">
    <!-- Font Awesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="dash.css">
    <style>
        /* Additional CSS for Users page */
        .users-container {
            background-color: #fff;
            border-radius: 12px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
            margin-bottom: 24px;
            overflow: hidden;
        }
        
        .users-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 20px 24px;
            border-bottom: 1px solid #f3f4f6;
        }
        
        .users-title {
            font-size: 20px;
            font-weight: 600;
            color: #1f2937;
            margin: 0;
        }
        
        .actions-container {
            display: flex;
            gap: 12px;
        }
        
        .filter-dropdown {
            position: relative;
            display: inline-block;
        }
        
        .filter-dropdown-content {
            display: none;
            position: absolute;
            right: 0;
            min-width: 160px;
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 10px 15px rgba(0, 0, 0, 0.1);
            z-index: 10;
            padding: 10px 0;
        }
        
        .filter-dropdown:hover .filter-dropdown-content {
            display: block;
        }
        
        .filter-dropdown-content a {
            display: block;
            padding: 8px 16px;
            color: #4b5563;
            text-decoration: none;
            font-size: 14px;
            transition: all 0.2s;
        }
        
        .filter-dropdown-content a:hover {
            background-color: #f9fafb;
            color: #4f46e5;
        }
        
        .search-box {
            display: flex;
            align-items: center;
            background-color: #f9fafb;
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            padding: 8px 16px;
            width: 300px;
        }
        
        .search-box input {
            border: none;
            background: transparent;
            width: 100%;
            font-size: 14px;
            padding: 5px;
            outline: none;
            color: #4b5563;
        }
        
        .search-box i {
            color: #9ca3af;
            margin-right: 10px;
        }
        
        .users-table {
            width: 100%;
            border-collapse: collapse;
        }
        
        .users-table th {
            text-align: left;
            padding: 16px 24px;
            font-size: 14px;
            font-weight: 600;
            color: #6b7280;
            border-bottom: 1px solid #f3f4f6;
            white-space: nowrap;
            background-color: #f9fafb;
        }
        
        .users-table td {
            padding: 16px 24px;
            font-size: 14px;
            color: #4b5563;
            border-bottom: 1px solid #f3f4f6;
        }
        
        .users-table tr:hover {
            background-color: #f9fafb;
        }
        
        .users-table tbody tr:last-child td {
            border-bottom: none;
        }
        
        .user-name {
            display: flex;
            align-items: center;
            gap: 12px;
        }
        
        .user-avatar {
            width: 36px;
            height: 36px;
            border-radius: 50%;
            object-fit: cover;
            background-color: #e5e7eb;
        }
        
        .user-details {
            display: flex;
            flex-direction: column;
        }
        
        .user-full-name {
            font-weight: 600;
            color: #1f2937;
        }
        
        .user-username {
            font-size: 13px;
            color: #6b7280;
        }
        
        .status-badge {
            display: inline-flex;
            align-items: center;
            padding: 4px 12px;
            border-radius: 9999px;
            font-size: 12px;
            font-weight: 500;
        }
        
        .status-active {
            background-color: #ecfdf5;
            color: #10b981;
        }
        
        .status-pending {
            background-color: #fffbeb;
            color: #f59e0b;
        }
        
        .status-suspended {
            background-color: #fef2f2;
            color: #ef4444;
        }
        
        .actions {
            display: flex;
            gap: 10px;
        }
        
        .action-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 30px;
            height: 30px;
            border-radius: 6px;
            border: none;
            cursor: pointer;
            color: #fff;
            transition: all 0.2s;
        }
        
        .edit-btn {
            background-color: #4f46e5;
        }
        
        .edit-btn:hover {
            background-color: #4338ca;
        }
        
        .delete-btn {
            background-color: #ef4444;
        }
        
        .delete-btn:hover {
            background-color: #dc2626;
        }
        
        .view-btn {
            background-color: #3b82f6;
        }
        
        .view-btn:hover {
            background-color: #2563eb;
        }
        
        .pagination {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 20px 24px;
            border-top: 1px solid #f3f4f6;
        }
        
        .pagination-info {
            font-size: 14px;
            color: #6b7280;
        }
        
        .pagination-controls {
            display: flex;
            gap: 5px;
        }
        
        .pagination-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 36px;
            height: 36px;
            border-radius: 6px;
            border: 1px solid #e5e7eb;
            background-color: #fff;
            color: #4b5563;
            font-size: 14px;
            cursor: pointer;
            transition: all 0.2s;
        }
        
        .pagination-btn:hover, .pagination-btn.active {
            background-color: #4f46e5;
            color: #fff;
            border-color: #4f46e5;
        }
        
        .pagination-btn.disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }
        
        .sort-icon {
            margin-left: 5px;
            font-size: 10px;
        }
        
        .empty-state {
            padding: 60px 20px;
            text-align: center;
        }
        
        .empty-state i {
            font-size: 48px;
            color: #e5e7eb;
            margin-bottom: 20px;
        }
        
        .empty-state h3 {
            font-size: 18px;
            font-weight: 600;
            color: #1f2937;
            margin-bottom: 10px;
        }
        
        .empty-state p {
            font-size: 14px;
            color: #6b7280;
            max-width: 400px;
            margin: 0 auto;
        }

        @media (max-width: 1024px) {
            .users-table {
                min-width: 900px;
            }
            
            .users-container {
                overflow-x: auto;
            }
            
            .search-box {
                width: 200px;
            }
        }

        @media (max-width: 768px) {
            .users-header {
                flex-direction: column;
                gap: 15px;
                align-items: flex-start;
            }
            
            .actions-container {
                width: 100%;
            }
            
            .search-box {
                width: 100%;
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
                    <li><a href="admin-users.php" class="active"><i class="fas fa-users"></i> Users</a></li>
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

            <!-- Users Content -->
            <div class="dashboard">
                <div class="dashboard-header">
                    <h1>User Management</h1>
                    <div class="breadcrumb">
                        <a href="admin-dashboard.php">Home</a>
                        <span>/</span>
                        <a href="admin-users.php">Users</a>
                    </div>
                </div>

                <div class="users-container">
                    <div class="users-header">
                        <h2 class="users-title">Registered Users</h2>
                        <div class="actions-container">
                            <form action="admin-users.php" method="GET" style="display: flex; gap: 10px;">
                                <div class="search-box">
                                    <i class="fa fa-search"></i>
                                    <input type="text" name="search" placeholder="Search users..." value="<?php echo htmlspecialchars($search); ?>">
                                </div>
                                <button type="submit" class="action-btn view-btn">
                                    <i class="fas fa-search"></i>
                                </button>
                            </form>
                            <div class="filter-dropdown">
                                <button class="action-btn edit-btn">
                                    <i class="fas fa-filter"></i>
                                </button>
                                <div class="filter-dropdown-content">
                                    <a href="admin-users.php">All Users</a>
                                    <a href="admin-users.php?status=active">Active Users</a>
                                    <a href="admin-users.php?status=pending">Pending Users</a>
                                    <a href="admin-users.php?status=suspended">Suspended Users</a>
                                </div>
                            </div>
                            <a href="admin-export-users.php" class="action-btn view-btn" title="Export Users">
                                <i class="fas fa-download"></i>
                            </a>
                        </div>
                    </div>

                    <div style="overflow-x: auto;">
                        <table class="users-table">
                            <thead>
                                <tr>
                                    <th>
                                        <a href="admin-users.php?sort=id&order=<?php echo $sortBy == 'id' && $sortOrder == 'ASC' ? 'DESC' : 'ASC'; ?>&search=<?php echo urlencode($search); ?>&status=<?php echo urlencode($status); ?>">
                                            ID
                                            <?php if ($sortBy == 'id'): ?>
                                                <i class="fas fa-sort-<?php echo $sortOrder == 'ASC' ? 'up' : 'down'; ?> sort-icon"></i>
                                            <?php else: ?>
                                                <i class="fas fa-sort sort-icon"></i>
                                            <?php endif; ?>
                                        </a>
                                    </th>
                                    <th>
                                        <a href="admin-users.php?sort=first_name&order=<?php echo $sortBy == 'first_name' && $sortOrder == 'ASC' ? 'DESC' : 'ASC'; ?>&search=<?php echo urlencode($search); ?>&status=<?php echo urlencode($status); ?>">
                                            User Details
                                            <?php if ($sortBy == 'first_name'): ?>
                                                <i class="fas fa-sort-<?php echo $sortOrder == 'ASC' ? 'up' : 'down'; ?> sort-icon"></i>
                                            <?php else: ?>
                                                <i class="fas fa-sort sort-icon"></i>
                                            <?php endif; ?>
                                        </a>
                                    </th>
                                    <th>
                                        <a href="admin-users.php?sort=email&order=<?php echo $sortBy == 'email' && $sortOrder == 'ASC' ? 'DESC' : 'ASC'; ?>&search=<?php echo urlencode($search); ?>&status=<?php echo urlencode($status); ?>">
                                            Email
                                            <?php if ($sortBy == 'email'): ?>
                                                <i class="fas fa-sort-<?php echo $sortOrder == 'ASC' ? 'up' : 'down'; ?> sort-icon"></i>
                                            <?php else: ?>
                                                <i class="fas fa-sort sort-icon"></i>
                                            <?php endif; ?>
                                        </a>
                                    </th>
                                    <th>Location</th>
                                    <th>
                                        <a href="admin-users.php?sort=verification_count&order=<?php echo $sortBy == 'verification_count' && $sortOrder == 'ASC' ? 'DESC' : 'ASC'; ?>&search=<?php echo urlencode($search); ?>&status=<?php echo urlencode($status); ?>">
                                            Verifications
                                            <?php if ($sortBy == 'verification_count'): ?>
                                                <i class="fas fa-sort-<?php echo $sortOrder == 'ASC' ? 'up' : 'down'; ?> sort-icon"></i>
                                            <?php else: ?>
                                                <i class="fas fa-sort sort-icon"></i>
                                            <?php endif; ?>
                                        </a>
                                    </th>
                                    <th>
                                        <a href="admin-users.php?sort=report_count&order=<?php echo $sortBy == 'report_count' && $sortOrder == 'ASC' ? 'DESC' : 'ASC'; ?>&search=<?php echo urlencode($search); ?>&status=<?php echo urlencode($status); ?>">
                                            Reports
                                            <?php if ($sortBy == 'report_count'): ?>
                                                <i class="fas fa-sort-<?php echo $sortOrder == 'ASC' ? 'up' : 'down'; ?> sort-icon"></i>
                                            <?php else: ?>
                                                <i class="fas fa-sort sort-icon"></i>
                                            <?php endif; ?>
                                        </a>
                                    </th>
                                    <th>
                                        <a href="admin-users.php?sort=status&order=<?php echo $sortBy == 'status' && $sortOrder == 'ASC' ? 'DESC' : 'ASC'; ?>&search=<?php echo urlencode($search); ?>&status=<?php echo urlencode($status); ?>">
                                            Status
                                            <?php if ($sortBy == 'status'): ?>
                                                <i class="fas fa-sort-<?php echo $sortOrder == 'ASC' ? 'up' : 'down'; ?> sort-icon"></i>
                                            <?php else: ?>
                                                <i class="fas fa-sort sort-icon"></i>
                                            <?php endif; ?>
                                        </a>
                                    </th>
                                    <th>
                                        <a href="admin-users.php?sort=created_at&order=<?php echo $sortBy == 'created_at' && $sortOrder == 'ASC' ? 'DESC' : 'ASC'; ?>&search=<?php echo urlencode($search); ?>&status=<?php echo urlencode($status); ?>">
                                            Registered
                                            <?php if ($sortBy == 'created_at'): ?>
                                                <i class="fas fa-sort-<?php echo $sortOrder == 'ASC' ? 'up' : 'down'; ?> sort-icon"></i>
                                            <?php else: ?>
                                                <i class="fas fa-sort sort-icon"></i>
                                            <?php endif; ?>
                                        </a>
                                    </th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($users)): ?>
                                    <tr>
                                        <td colspan="9">
                                            <div class="empty-state">
                                                <i class="fas fa-users-slash"></i>
                                                <h3>No users found</h3>
                                                <p>There are no users matching your search criteria.</p>
                                            </div>
                                        </td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($users as $user): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($user['id']); ?></td>
                                            <td>
                                                <div class="user-name">
                                                    <img src="https://ui-avatars.com/api/?name=<?php echo urlencode($user['first_name'] . '+' . $user['last_name']); ?>&background=random" alt="User Avatar" class="user-avatar">
                                                    <div class="user-details">
                                                        <span class="user-full-name"><?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?></span>
                                                        <span class="user-username">@<?php echo htmlspecialchars($user['username']); ?></span>
                                                    </div>
                                                </div>
                                            </td>
                                            <td><?php echo htmlspecialchars($user['email']); ?></td>
                                            <td><?php echo htmlspecialchars($user['city'] . ', ' . $user['country']); ?></td>
                                            <td><?php echo htmlspecialchars($user['verification_count']); ?></td>
                                            <td><?php echo htmlspecialchars($user['report_count']); ?></td>
                                            <td>
                                                <?php 
                                                    $statusClass = 'status-active';
                                                    if ($user['status'] == 'pending') {
                                                        $statusClass = 'status-pending';
                                                    } elseif ($user['status'] == 'suspended') {
                                                        $statusClass = 'status-suspended';
                                                    }
                                                ?>
                                                <span class="status-badge <?php echo $statusClass; ?>">
                                                    <?php echo ucfirst(htmlspecialchars($user['status'])); ?>
                                                </span>
                                            </td>
                                            <td><?php echo date('M d, Y', strtotime($user['created_at'])); ?></td>
                                            <td>
                                                <div class="actions">
                                                    <a href="admin-view-user.php?id=<?php echo $user['id']; ?>" class="action-btn view-btn" title="View User">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                    <a href="admin-edit-user.php?id=<?php echo $user['id']; ?>" class="action-btn edit-btn" title="Edit User">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                    <button type="button" class="action-btn delete-btn" title="Delete User" data-user-id="<?php echo htmlspecialchars($user['id']); ?>">
                                                    <i class="fas fa-trash-alt"></i>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>

                    <div class="pagination">
                        <div class="pagination-info">
                            Showing <?php echo min(($page - 1) * $usersPerPage + 1, $totalUsers); ?> to <?php echo min($page * $usersPerPage, $totalUsers); ?> of <?php echo $totalUsers; ?> users
                        </div>
                        <div class="pagination-controls">
                            <?php if ($page > 1): ?>
                                <a href="admin-users.php?page=<?php echo $page - 1; ?>&sort=<?php echo $sortBy; ?>&order=<?php echo $sortOrder; ?>&search=<?php echo urlencode($search); ?>&status=<?php echo urlencode($status); ?>" class="pagination-btn">
                                    <i class="fas fa-chevron-left"></i>
                                </a>
                            <?php else: ?>
                                <button class="pagination-btn disabled">
                                    <i class="fas fa-chevron-left"></i>
                                </button>
                            <?php endif; ?>
                            
                            <?php
                            $startPage = max(1, $page - 2);
                            $endPage = min($totalPages, $page + 2);
                            
                            for ($i = $startPage; $i <= $endPage; $i++): ?>
                                <a href="admin-users.php?page=<?php echo $i; ?>&sort=<?php echo $sortBy; ?>&order=<?php echo $sortOrder; ?>&search=<?php echo urlencode($search); ?>&status=<?php echo urlencode($status); ?>" class="pagination-btn <?php echo $i == $page ? 'active' : ''; ?>">
                                    <?php echo $i; ?>
                                </a>
                            <?php endfor; ?>
                            
                            <?php if ($page < $totalPages): ?>
                                <a href="admin-users.php?page=<?php echo $page + 1; ?>&sort=<?php echo $sortBy; ?>&order=<?php echo $sortOrder; ?>&search=<?php echo urlencode($search); ?>&status=<?php echo urlencode($status); ?>" class="pagination-btn">
                                    <i class="fas fa-chevron-right"></i>
                                </a>
                            <?php else: ?>
                                <button class="pagination-btn disabled">
                                    <i class="fas fa-chevron-right"></i>
                                </button>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal for delete confirmation -->
    <div id="deleteModal" class="modal" style="display: none;">
        <div class="modal-content">
            <span class="close">&times;</span>
            <h2>Confirm Deletion</h2>
            <p>Are you sure you want to delete this user? This action cannot be undone.</p>
            <div class="modal-actions">
                <button id="cancelDelete" class="btn-secondary">Cancel</button>
                <form id="deleteForm" action="admin-delete-user.php" method="POST">
                    <input type="hidden" id="deleteUserId" name="user_id" value="">
                    <button type="submit" class="btn-danger">Delete User</button>
                </form>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script>
        // Toggle sidebar
        document.getElementById('toggleMenu').addEventListener('click', function() {
            document.querySelector('.sidebar').classList.toggle('active');
        });
// Replace the existing delete confirmation function with this fixed version
function confirmDelete(userId) {
    // Set the user ID in the hidden input
    document.getElementById('deleteUserId').value = userId;
    
    // Display the modal
    document.getElementById('deleteModal').style.display = 'block';
}

// Set up all event handlers when DOM is fully loaded
document.addEventListener('DOMContentLoaded', function() {
    // Setup for delete modal
    const deleteModal = document.getElementById('deleteModal');
    const closeBtn = document.querySelector('#deleteModal .close');
    const cancelBtn = document.getElementById('cancelDelete');
    
    // Close the modal when clicking the X button
    if (closeBtn) {
        closeBtn.addEventListener('click', function() {
            deleteModal.style.display = 'none';
        });
    }
    
    // Close the modal when clicking Cancel
    if (cancelBtn) {
        cancelBtn.addEventListener('click', function() {
            deleteModal.style.display = 'none';
        });
    }
    
    // Close any modal when clicking outside of it
    window.onclick = function(event) {
        if (event.target === deleteModal) {
            deleteModal.style.display = 'none';
        } else if (event.target === document.getElementById('viewModal')) {
            document.getElementById('viewModal').style.display = 'none';
        } else if (event.target === document.getElementById('editModal')) {
            document.getElementById('editModal').style.display = 'none';
        }
    };
    
    // Make sure the delete form submits properly
    const deleteForm = document.getElementById('deleteForm');
    if (deleteForm) {
        deleteForm.addEventListener('submit', function(e) {
            // Optional: Add client-side validation here if needed
            
            // Hide the modal after submission starts
            deleteModal.style.display = 'none';
        });
    }
    
    // Attach event listeners to ALL delete buttons
    const deleteButtons = document.querySelectorAll('.delete-btn');
    deleteButtons.forEach(button => {
        button.addEventListener('click', function() {
            const userId = this.getAttribute('data-user-id');
            if (userId) {
                confirmDelete(userId);
            } else {
                console.error('User ID not found for delete button');
            }
        });
    });
});
        const modal = document.getElementById('deleteModal');
        const closeBtn = document.getElementsByClassName('close')[0];
        const cancelBtn = document.getElementById('cancelDelete');

        closeBtn.onclick = function() {
            modal.style.display = 'none';
        }

        cancelBtn.onclick = function() {
            modal.style.display = 'none';
        }

        window.onclick = function(event) {
            if (event.target == modal) {
                modal.style.display = 'none';
            }
        }

        // Notification dropdown functionality
        document.querySelector('.notifications').addEventListener('click', function() {
            console.log('Notifications clicked');
            // Implementation for notifications dropdown would go here
        });

        // Add dynamic time update
        function updateOnlineTime() {
            const now = new Date();
            const timeString = now.toLocaleTimeString();
            const dateString = now.toLocaleDateString();
        }
        
        // Update time every second
        setInterval(updateOnlineTime, 1000);
        
        // Initialize tooltips or other UI components
        document.addEventListener('DOMContentLoaded', function() {
            console.log('Users page loaded successfully');
        });
    </script>

    <style>
        /* Modal Styles */
        .modal {
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .modal-content {
            background-color: #fff;
            margin: auto;
            padding: 24px;
            border-radius: 12px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
            width: 400px;
            max-width: 90%;
            position: relative;
        }
        
        .close {
            position: absolute;
            right: 20px;
            top: 15px;
            font-size: 24px;
            font-weight: bold;
            color: #6b7280;
            cursor: pointer;
        }
        
        .close:hover {
            color: #1f2937;
        }
        
        .modal h2 {
            margin-top: 0;
            color: #1f2937;
            font-size: 20px;
            font-weight: 600;
        }
        
        .modal p {
            color: #4b5563;
            font-size: 14px;
            margin-bottom: 24px;
        }
        
        .modal-actions {
            display: flex;
            justify-content: flex-end;
            gap: 12px;
        }
        
        .btn-secondary {
            padding: 8px 16px;
            background-color: #f3f4f6;
            color: #4b5563;
            border: 1px solid #e5e7eb;
            border-radius: 6px;
            font-size: 14px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.2s;
        }
        
        .btn-secondary:hover {
            background-color: #e5e7eb;
        }
        
        .btn-danger {
            padding: 8px 16px;
            background-color: #ef4444;
            color: #fff;
            border: none;
            border-radius: 6px;
            font-size: 14px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.2s;
        }
        
        .btn-danger:hover {
            background-color: #dc2626;
        }
    </style>
    <!-- First, I'll add the HTML for the new modals -->

<!-- View User Modal -->
<div id="viewModal" class="modal" style="display: none;">
    <div class="modal-content" style="width: 600px; max-width: 95%;">
        <span class="close" onclick="closeViewModal()">&times;</span>
        <h2>User Details</h2>
        <div id="userDetails" class="user-details-container">
            <div class="user-profile-header">
                <img id="viewUserAvatar" src="" alt="User Avatar" class="view-user-avatar">
                <div>
                    <h3 id="viewUserName"></h3>
                    <span id="viewUserUsername" class="user-username"></span>
                </div>
            </div>
            <div class="user-info-grid">
                <div class="info-item">
                    <span class="info-label">Email</span>
                    <span id="viewUserEmail" class="info-value"></span>
                </div>
                <div class="info-item">
                    <span class="info-label">Location</span>
                    <span id="viewUserLocation" class="info-value"></span>
                </div>
                <div class="info-item">
                    <span class="info-label">Status</span>
                    <span id="viewUserStatus" class="info-value"></span>
                </div>
                <div class="info-item">
                    <span class="info-label">Registered On</span>
                    <span id="viewUserRegistered" class="info-value"></span>
                </div>
                <div class="info-item">
                    <span class="info-label">Verification Count</span>
                    <span id="viewUserVerifications" class="info-value"></span>
                </div>
                <div class="info-item">
                    <span class="info-label">Report Count</span>
                    <span id="viewUserReports" class="info-value"></span>
                </div>
            </div>
        </div>
        <div class="modal-actions">
            <button onclick="closeViewModal()" class="btn-secondary">Close</button>
            <button onclick="openEditModal(currentUserId)" class="btn-primary">Edit User</button>
        </div>
    </div>
</div>

<!-- Edit User Modal -->
<div id="editModal" class="modal" style="display: none;">
    <div class="modal-content" style="width: 600px; max-width: 95%;">
        <span class="close" onclick="closeEditModal()">&times;</span>
        <h2>Edit User</h2>
        <form id="editUserForm" action="admin-update-user.php" method="POST">
            <input type="hidden" id="editUserId" name="user_id">
            <div class="form-group">
                <label for="editFirstName">First Name</label>
                <input type="text" id="editFirstName" name="first_name" required>
            </div>
            <div class="form-group">
                <label for="editLastName">Last Name</label>
                <input type="text" id="editLastName" name="last_name" required>
            </div>
            <div class="form-group">
                <label for="editUsername">Username</label>
                <input type="text" id="editUsername" name="username" required>
            </div>
            <div class="form-group">
                <label for="editEmail">Email</label>
                <input type="email" id="editEmail" name="email" required>
            </div>
            <div class="form-group">
                <label for="editCity">City</label>
                <input type="text" id="editCity" name="city">
            </div>
            <div class="form-group">
                <label for="editCountry">Country</label>
                <input type="text" id="editCountry" name="country">
            </div>
            <div class="form-group">
                <label for="editStatus">Status</label>
                <select id="editStatus" name="status" required>
                    <option value="active">Active</option>
                    <option value="pending">Pending</option>
                    <option value="suspended">Suspended</option>
                </select>
            </div>
            <div class="modal-actions">
                <button type="button" onclick="closeEditModal()" class="btn-secondary">Cancel</button>
                <button type="submit" class="btn-primary">Save Changes</button>
            </div>
        </form>
    </div>
</div>

<!-- Now I'll add the JavaScript to handle the modals -->
<script>
    // Existing delete modal code remains the same

    // Global variable to track current user being viewed/edited
    let currentUserId = null;

    // View User Modal
    function openViewModal(userId) {
        currentUserId = userId;
        const modal = document.getElementById('viewModal');
        
        // Fetch user data with AJAX
        fetch(`admin-get-user.php?id=${userId}`)
            .then(response => response.json())
            .then(user => {
                // Populate user details
                document.getElementById('viewUserAvatar').src = `https://ui-avatars.com/api/?name=${encodeURIComponent(user.first_name + '+' + user.last_name)}&background=random`;
                document.getElementById('viewUserName').textContent = `${user.first_name} ${user.last_name}`;
                document.getElementById('viewUserUsername').textContent = `@${user.username}`;
                document.getElementById('viewUserEmail').textContent = user.email;
                document.getElementById('viewUserLocation').textContent = `${user.city}, ${user.country}`;
                
                // Set status with appropriate styling
                const statusElement = document.getElementById('viewUserStatus');
                statusElement.textContent = user.status.charAt(0).toUpperCase() + user.status.slice(1);
                statusElement.className = 'info-value status-badge';
                if (user.status === 'active') {
                    statusElement.classList.add('status-active');
                } else if (user.status === 'pending') {
                    statusElement.classList.add('status-pending');
                } else if (user.status === 'suspended') {
                    statusElement.classList.add('status-suspended');
                }
                
                document.getElementById('viewUserRegistered').textContent = new Date(user.created_at).toLocaleDateString('en-US', {
                    year: 'numeric',
                    month: 'long',
                    day: 'numeric'
                });
                document.getElementById('viewUserVerifications').textContent = user.verification_count;
                document.getElementById('viewUserReports').textContent = user.report_count;
                
                // Show the modal
                modal.style.display = 'block';
            })
            .catch(error => {
                console.error('Error fetching user data:', error);
                alert('Failed to load user details. Please try again.');
            });
    }

    function closeViewModal() {
        document.getElementById('viewModal').style.display = 'none';
    }

    // Edit User Modal
    function openEditModal(userId) {
        // Close the view modal if it's open
        closeViewModal();
        
        currentUserId = userId;
        const modal = document.getElementById('editModal');
        
        // Fetch user data with AJAX
        fetch(`admin-get-user.php?id=${userId}`)
            .then(response => response.json())
            .then(user => {
                // Populate the form fields
                document.getElementById('editUserId').value = user.id;
                document.getElementById('editFirstName').value = user.first_name;
                document.getElementById('editLastName').value = user.last_name;
                document.getElementById('editUsername').value = user.username;
                document.getElementById('editEmail').value = user.email;
                document.getElementById('editCity').value = user.city;
                document.getElementById('editCountry').value = user.country;
                document.getElementById('editStatus').value = user.status;
                
                // Show the modal
                modal.style.display = 'block';
            })
            .catch(error => {
                console.error('Error fetching user data:', error);
                alert('Failed to load user data for editing. Please try again.');
            });
    }

    function closeEditModal() {
        document.getElementById('editModal').style.display = 'none';
    }

    // Update the event listeners for the action buttons
    document.addEventListener('DOMContentLoaded', function() {
        // Find all view buttons and add click event listeners
        const viewButtons = document.querySelectorAll('.view-btn');
        viewButtons.forEach(button => {
            button.addEventListener('click', function(e) {
                e.preventDefault();
                const userId = this.getAttribute('href').split('=')[1];
                openViewModal(userId);
            });
        });
        
        // Find all edit buttons and add click event listeners
        const editButtons = document.querySelectorAll('.edit-btn');
        editButtons.forEach(button => {
            button.addEventListener('click', function(e) {
                e.preventDefault();
                const userId = this.getAttribute('href').split('=')[1];
                openEditModal(userId);
            });
        });
        
        // Close modals when clicking outside
        window.onclick = function(event) {
            const viewModal = document.getElementById('viewModal');
            const editModal = document.getElementById('editModal');
            const deleteModal = document.getElementById('deleteModal');
            
            if (event.target === viewModal) {
                viewModal.style.display = 'none';
            } else if (event.target === editModal) {
                editModal.style.display = 'none';
            } else if (event.target === deleteModal) {
                deleteModal.style.display = 'none';
            }
        }
    });
</script>

<!-- Additional CSS styles for the modals -->
<style>
    /* User View Modal Styles */
    .user-details-container {
        margin-bottom: 24px;
    }
    
    .user-profile-header {
        display: flex;
        align-items: center;
        gap: 16px;
        margin-bottom: 24px;
        padding-bottom: 16px;
        border-bottom: 1px solid #e5e7eb;
    }
    
    .view-user-avatar {
        width: 64px;
        height: 64px;
        border-radius: 50%;
        object-fit: cover;
        background-color: #e5e7eb;
    }
    
    .user-profile-header h3 {
        margin: 0;
        font-size: 18px;
        font-weight: 600;
        color: #1f2937;
    }
    
    .user-info-grid {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 20px;
    }
    
    .info-item {
        display: flex;
        flex-direction: column;
    }
    
    .info-label {
        font-size: 12px;
        color: #6b7280;
        margin-bottom: 4px;
    }
    
    .info-value {
        font-size: 14px;
        color: #1f2937;
        font-weight: 500;
    }
    
    /* Edit User Form Styles */
    .form-group {
        margin-bottom: 16px;
    }
    
    .form-group label {
        display: block;
        font-size: 14px;
        color: #4b5563;
        margin-bottom: 6px;
    }
    
    .form-group input,
    .form-group select {
        width: 100%;
        padding: 10px 12px;
        border: 1px solid #e5e7eb;
        border-radius: 6px;
        font-size: 14px;
        color: #1f2937;
        transition: border-color 0.2s;
    }
    
    .form-group input:focus,
    .form-group select:focus {
        border-color: #4f46e5;
        outline: none;
    }
    
    .btn-primary {
        padding: 8px 16px;
        background-color: #4f46e5;
        color: #fff;
        border: none;
        border-radius: 6px;
        font-size: 14px;
        font-weight: 500;
        cursor: pointer;
        transition: all 0.2s;
    }
    
    .btn-primary:hover {
        background-color: #4338ca;
    }
    
    @media (max-width: 640px) {
        .user-info-grid {
            grid-template-columns: 1fr;
        }
    }
</style>
</body>
</html>