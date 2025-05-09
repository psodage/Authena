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

// Get admin info
$adminName = $_SESSION['admin_name'] ?? 'Admin User';

// Pagination setup
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$records_per_page = 10;
$offset = ($page - 1) * $records_per_page;

// Filter setup
$status_filter = isset($_GET['status']) ? $_GET['status'] : '';
$status_condition = '';
if ($status_filter != '') {
    $status_condition = "WHERE fr.status = '$status_filter'";
}

// Count total records
$count_query = "SELECT COUNT(*) as total FROM fake_reports fr $status_condition";
$count_result = $conn->query($count_query);
$total_records = $count_result->fetch_assoc()['total'];
$total_pages = ceil($total_records / $records_per_page);

// Get fake reports with joined data
$query = "SELECT fr.*, p.name as product_name, p.product_code, b.name as brand_name, 
          u.username, u.email, u.first_name, u.last_name
          FROM fake_reports fr
          LEFT JOIN products p ON fr.product_id = p.id
          LEFT JOIN brands b ON p.brand_id = b.id
          LEFT JOIN users u ON fr.user_id = u.id
          $status_condition
          ORDER BY fr.created_at DESC
          LIMIT $offset, $records_per_page";
$result = $conn->query($query);

// Process status update if submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    $report_id = $_POST['report_id'];
    $new_status = $_POST['new_status'];
    $admin_notes = $_POST['admin_notes'];
    
    $update_query = "UPDATE fake_reports SET status = ?, admin_notes = ? WHERE id = ?";
    $stmt = $conn->prepare($update_query);
    $stmt->bind_param("ssi", $new_status, $admin_notes, $report_id);
    
    if ($stmt->execute()) {
        $success_message = "Report status updated successfully!";
        // Refresh the page to show updated data
        header("Location: admin-fake-reports.php" . ($status_filter ? "?status=$status_filter" : ""));
        exit;
    } else {
        $error_message = "Error updating report status: " . $conn->error;
    }
    $stmt->close();
}

// Stats for summary cards
$pending_query = "SELECT COUNT(*) as count FROM fake_reports WHERE status = 'pending'";
$investigating_query = "SELECT COUNT(*) as count FROM fake_reports WHERE status = 'investigating'";
$resolved_query = "SELECT COUNT(*) as count FROM fake_reports WHERE status = 'resolved'";
$rejected_query = "SELECT COUNT(*) as count FROM fake_reports WHERE status = 'rejected'";

$pending_result = $conn->query($pending_query)->fetch_assoc();
$investigating_result = $conn->query($investigating_query)->fetch_assoc();
$resolved_result = $conn->query($resolved_query)->fetch_assoc();
$rejected_result = $conn->query($rejected_query)->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Authena Admin - Fake Reports</title>
    <!-- Favicon -->
    <link rel="shortcut icon" href="assets/images/favicon.ico" type="image/x-icon">
    <!-- Font Awesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="dash.css">
    <style>
        /* Additional styles for fake reports page */
        .status-badge {
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 500;
            display: inline-block;
        }
        .status-pending {
            background-color: #FEF3C7;
            color: #92400E;
        }
        .status-investigating {
            background-color: #DBEAFE;
            color: #1E40AF;
        }
        .status-resolved {
            background-color: #D1FAE5;
            color: #065F46;
        }
        .status-rejected {
            background-color: #FEE2E2;
            color: #991B1B;
        }
        .report-card {
            background-color: #fff;
            border-radius: 12px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
            transition: all 0.2s ease;
            margin-bottom: 1.5rem;
        }
        .report-card:hover {
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            transform: translateY(-2px);
        }
        .report-header {
            padding: 1.5rem;
            border-bottom: 1px solid #f3f4f6;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .report-content {
            padding: 1.5rem;
        }
        .report-footer {
            padding: 1rem 1.5rem;
            background-color: #f9fafb;
            border-top: 1px solid #f3f4f6;
            border-radius: 0 0 12px 12px;
        }
        .summary-card {
            background-color: #fff;
            border-radius: 12px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
            padding: 1.5rem;
            text-align: center;
            transition: all 0.2s ease;
        }
        .summary-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 15px -3px rgba(0,0,0,0.1);
        }
        .summary-card .count {
            font-size: 2rem;
            font-weight: 700;
            margin: 1rem 0;
        }
        .summary-card .title {
            color: #6B7280;
            font-size: 0.9rem;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }
        .summary-card.pending {
            border-top: 4px solid #F59E0B;
        }
        .summary-card.investigating {
            border-top: 4px solid #3B82F6;
        }
        .summary-card.resolved {
            border-top: 4px solid #10B981;
        }
        .summary-card.rejected {
            border-top: 4px solid #EF4444;
        }
        .filter-bar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
            padding: 1rem;
            background-color: #fff;
            border-radius: 12px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        }
        .filter-group {
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }
        .filter-label {
            font-weight: 500;
            color: #374151;
        }
        .filter-select {
            padding: 0.5rem 1rem;
            border-radius: 6px;
            border: 1px solid #D1D5DB;
            background-color: #F9FAFB;
            font-size: 0.95rem;
            outline: none;
            transition: all 0.2s;
        }
        .filter-select:focus {
            border-color: #4F46E5;
            box-shadow: 0 0 0 2px rgba(79, 70, 229, 0.2);
        }
        .filter-button {
            padding: 0.5rem 1rem;
            background-color: #4F46E5;
            color: white;
            border: none;
            border-radius: 6px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.2s;
        }
        .filter-button:hover {
            background-color: #4338CA;
        }
        .pagination {
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 0.5rem;
            margin-top: 2rem;
        }
        .pagination a, .pagination span {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 2.5rem;
            height: 2.5rem;
            border-radius: 6px;
            font-weight: 500;
            text-decoration: none;
            transition: all 0.2s;
            color: #4B5563;
            background-color: #F9FAFB;
        }
        .pagination a:hover {
            background-color: #F3F4F6;
        }
        .pagination .active {
            background-color: #4F46E5;
            color: white;
        }
        .pagination .disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }
        .grid-cols-4 {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 1.5rem;
        }
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            align-items: center;
            justify-content: center;
        }
        .modal-content {
            background-color: white;
            border-radius: 12px;
            width: 500px;
            max-width: 90%;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
        }
        .modal-header {
            padding: 1.5rem;
            border-bottom: 1px solid #f3f4f6;
        }
        .modal-body {
            padding: 1.5rem;
        }
        .modal-footer {
            padding: 1.5rem;
            border-top: 1px solid #f3f4f6;
            text-align: right;
        }
        .close-modal {
            position: absolute;
            top: 1.25rem;
            right: 1.25rem;
            font-size: 1.5rem;
            cursor: pointer;
            color: #6B7280;
        }
        .form-group {
            margin-bottom: 1.5rem;
        }
        label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 500;
            color: #374151;
        }
        select, textarea {
            width: 100%;
            padding: 0.75rem;
            border-radius: 6px;
            border: 1px solid #D1D5DB;
            font-size: 0.95rem;
            transition: all 0.2s;
        }
        select:focus, textarea:focus {
            border-color: #4F46E5;
            outline: none;
            box-shadow: 0 0 0 2px rgba(79, 70, 229, 0.2);
        }
        textarea {
            min-height: 100px;
            resize: vertical;
        }
        .btn {
            padding: 0.75rem 1.5rem;
            border-radius: 6px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.2s;
            border: none;
        }
        .btn-primary {
            background-color: #4F46E5;
            color: white;
        }
        .btn-primary:hover {
            background-color: #4338CA;
        }
        .btn-secondary {
            background-color: #F3F4F6;
            color: #374151;
        }
        .btn-secondary:hover {
            background-color: #E5E7EB;
        }
        .report-info {
            margin-bottom: 1.5rem;
        }
        .report-info h3 {
            margin-bottom: 0.5rem;
            font-size: 1.1rem;
            color: #374151;
        }
        .report-info p {
            color: #6B7280;
            margin-bottom: 0.25rem;
        }
        .info-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 1rem;
        }
        .location-info {
            margin-top: 1rem;
            padding-top: 1rem;
            border-top: 1px solid #f3f4f6;
        }
        .no-reports {
            padding: 3rem;
            text-align: center;
            background-color: #fff;
            border-radius: 12px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        }
        .no-reports i {
            font-size: 3rem;
            color: #D1D5DB;
            margin-bottom: 1rem;
        }
        .no-reports h3 {
            font-size: 1.5rem;
            color: #374151;
            margin-bottom: 0.5rem;
        }
        .no-reports p {
            color: #6B7280;
        }
        .alert {
            padding: 1rem;
            border-radius: 6px;
            margin-bottom: 1.5rem;
        }
        .alert-success {
            background-color: #D1FAE5;
            color: #065F46;
            border-left: 4px solid #10B981;
        }
        .alert-error {
            background-color: #FEE2E2;
            color: #991B1B;
            border-left: 4px solid #EF4444;
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
                    <li><a href="admin-fake-reports.php" class="active"><i class="fas fa-flag"></i> Fake Reports</a></li>
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
                    <input type="text" placeholder="Search reports...">
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

            <!-- Fake Reports Content -->
            <div class="dashboard">
                <div class="dashboard-header">
                    <h1>Fake Product Reports</h1>
                    <div class="breadcrumb">
                        <a href="admin-dashboard.php">Home</a>
                        <span>/</span>
                        <a href="admin-fake-reports.php">Fake Reports</a>
                    </div>
                </div>

                <?php if (isset($success_message)): ?>
                <div class="alert alert-success">
                    <?php echo $success_message; ?>
                </div>
                <?php endif; ?>

                <?php if (isset($error_message)): ?>
                <div class="alert alert-error">
                    <?php echo $error_message; ?>
                </div>
                <?php endif; ?>

                <!-- Summary Cards -->
                <div class="grid-cols-4">
                    <div class="summary-card pending">
                        <div class="title">Pending</div>
                        <div class="count"><?php echo $pending_result['count']; ?></div>
                        <div><i class="fas fa-clock"></i> Awaiting Review</div>
                    </div>
                    <div class="summary-card investigating">
                        <div class="title">Investigating</div>
                        <div class="count"><?php echo $investigating_result['count']; ?></div>
                        <div><i class="fas fa-search"></i> Under Investigation</div>
                    </div>
                    <div class="summary-card resolved">
                        <div class="title">Resolved</div>
                        <div class="count"><?php echo $resolved_result['count']; ?></div>
                        <div><i class="fas fa-check-circle"></i> Completed</div>
                    </div>
                    <div class="summary-card rejected">
                        <div class="title">Rejected</div>
                        <div class="count"><?php echo $rejected_result['count']; ?></div>
                        <div><i class="fas fa-times-circle"></i> Dismissed</div>
                    </div>
                </div>

                <!-- Filter Bar -->
                <div class="filter-bar">
                    <div class="filter-group">
                        <span class="filter-label">Filter by:</span>
                        <form action="" method="get" id="statusFilterForm">
                            <select name="status" class="filter-select" onchange="this.form.submit()">
                                <option value="">All Reports</option>
                                <option value="pending" <?php echo $status_filter == 'pending' ? 'selected' : ''; ?>>Pending</option>
                                <option value="investigating" <?php echo $status_filter == 'investigating' ? 'selected' : ''; ?>>Investigating</option>
                                <option value="resolved" <?php echo $status_filter == 'resolved' ? 'selected' : ''; ?>>Resolved</option>
                                <option value="rejected" <?php echo $status_filter == 'rejected' ? 'selected' : ''; ?>>Rejected</option>
                            </select>
                        </form>
                    </div>
                    <div>
                        <span><strong><?php echo $total_records; ?></strong> reports found</span>
                    </div>
                </div>

                <!-- Reports List -->
                <?php if ($result->num_rows > 0): ?>
                    <?php while($row = $result->fetch_assoc()): ?>
                        <div class="report-card">
                            <div class="report-header">
                                <div>
                                    <h3>Report #<?php echo htmlspecialchars($row['id']); ?></h3>
                                    <small><?php echo htmlspecialchars($row['created_at']); ?></small>
                                </div>
                                <div>
                                    <span class="status-badge status-<?php echo strtolower($row['status']); ?>">
                                        <?php echo ucfirst($row['status']); ?>
                                    </span>
                                </div>
                            </div>
                            <div class="report-content">
                                <div class="info-grid">
                                    <div class="report-info">
                                        <h3>Product Information</h3>
                                        <p><strong>Product:</strong> <?php echo htmlspecialchars($row['product_name']); ?></p>
                                        <p><strong>Code:</strong> <?php echo htmlspecialchars($row['product_code']); ?></p>
                                        <p><strong>Brand:</strong> <?php echo htmlspecialchars($row['brand_name']); ?></p>
                                    </div>
                                    <div class="report-info">
                                        <h3>Reporter Information</h3>
                                        <p><strong>User:</strong> <?php echo htmlspecialchars($row['username']); ?></p>
                                        <p><strong>Email:</strong> <?php echo htmlspecialchars($row['email']); ?></p>
                                        <p><strong>Name:</strong> <?php echo htmlspecialchars($row['first_name'] . ' ' . $row['last_name']); ?></p>
                                    </div>
                                </div>
                                
                                <div class="report-info">
                                    <h3>Report Details</h3>
                                    <p><strong>Report Type:</strong> <?php echo ucfirst($row['report_type']); ?></p>
                                    <p><strong>Description:</strong> <?php echo nl2br(htmlspecialchars($row['description'])); ?></p>
                                    
                                    <div class="location-info">
                                        <p><strong>Location:</strong> <?php echo htmlspecialchars($row['location_address']); ?></p>
                                        <?php if ($row['location_lat'] && $row['location_lng']): ?>
                                            <p><strong>Coordinates:</strong> <?php echo $row['location_lat'] . ', ' . $row['location_lng']; ?></p>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                
                                <?php if ($row['admin_notes']): ?>
                                <div class="report-info">
                                    <h3>Admin Notes</h3>
                                    <p><?php echo nl2br(htmlspecialchars($row['admin_notes'])); ?></p>
                                </div>
                                <?php endif; ?>
                            </div>
                            <div class="report-footer">
                                <button class="btn btn-primary update-status-btn" data-id="<?php echo $row['id']; ?>" data-status="<?php echo $row['status']; ?>" data-notes="<?php echo htmlspecialchars($row['admin_notes']); ?>">
                                    <i class="fas fa-edit"></i> Update Status
                                </button>
                            </div>
                        </div>
                    <?php endwhile; ?>
                    
                    <!-- Pagination -->
                    <div class="pagination">
                        <?php if($page > 1): ?>
                            <a href="?page=1<?php echo $status_filter ? "&status=$status_filter" : ''; ?>"><i class="fas fa-angle-double-left"></i></a>
                            <a href="?page=<?php echo $page-1; ?><?php echo $status_filter ? "&status=$status_filter" : ''; ?>"><i class="fas fa-angle-left"></i></a>
                        <?php else: ?>
                            <span class="disabled"><i class="fas fa-angle-double-left"></i></span>
                            <span class="disabled"><i class="fas fa-angle-left"></i></span>
                        <?php endif; ?>
                        
                        <?php
                        $start_page = max(1, $page - 2);
                        $end_page = min($total_pages, $page + 2);
                        
                        for($i = $start_page; $i <= $end_page; $i++):
                        ?>
                            <a href="?page=<?php echo $i; ?><?php echo $status_filter ? "&status=$status_filter" : ''; ?>" class="<?php echo $i == $page ? 'active' : ''; ?>"><?php echo $i; ?></a>
                        <?php endfor; ?>
                        
                        <?php if($page < $total_pages): ?>
                            <a href="?page=<?php echo $page+1; ?><?php echo $status_filter ? "&status=$status_filter" : ''; ?>"><i class="fas fa-angle-right"></i></a>
                            <a href="?page=<?php echo $total_pages; ?><?php echo $status_filter ? "&status=$status_filter" : ''; ?>"><i class="fas fa-angle-double-right"></i></a>
                        <?php else: ?>
                            <span class="disabled"><i class="fas fa-angle-right"></i></span>
                            <span class="disabled"><i class="fas fa-angle-double-right"></i></span>
                        <?php endif; ?>
                    </div>
                    
                <?php else: ?>
                    <div class="no-reports">
                        <i class="fas fa-search"></i>
                        <h3>No Reports Found</h3>
                        <p>There are no fake product reports matching your criteria.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Update Status Modal -->
    <div class="modal" id="updateStatusModal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Update Report Status</h2>
                <span class="close-modal">&times;</span>
            </div>
            <form method="POST" action="">
                <div class="modal-body">
                    <input type="hidden" name="report_id" id="reportId">
                    
                    <div class="form-group">
                        <label for="newStatus">Status:</label>
                        <select name="new_status" id="newStatus" required>
                            <option value="pending">Pending</option>
                            <option value="investigating">Investigating</option>
                            <option value="resolved">Resolved</option>
                            <option value="rejected">Rejected</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="adminNotes">Admin Notes:</label>
                        <textarea name="admin_notes" id="adminNotes" rows="4" placeholder="Add your notes about this report..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary close-modal-btn">Cancel</button>
                    <button type="submit" name="update_status" class="btn btn-primary">Update Status</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Scripts -->
    <script>
        // Toggle sidebar
        document.getElementById('toggleMenu').addEventListener('click', function() {
            document.querySelector('.sidebar').classList.toggle('active');
        });

        // Modal functionality
        const modal = document.getElementById('updateStatusModal');
        const updateBtns = document.querySelectorAll('.update-status-btn');
        const closeBtns = document.querySelectorAll('.close-modal, .close-modal-btn');
        
        updateBtns.forEach(btn => {
            btn.addEventListener('click', function() {
                const reportId = this.getAttribute('data-id');
                const status = this.getAttribute('data-status');
                const notes = this.getAttribute('data-notes');
                
                document.getElementById('reportId').value = reportId;
                document.getElementById('newStatus').value = status;
                document.getElementById('adminNotes').value = notes;
                
                modal.style.display = 'flex';
            });
        });
        
        closeBtns.forEach(btn => {
            btn.addEventListener('click', function() {
                modal.style.display = 'none';
            });
        });
        
        window.addEventListener('click', function(event) {
            if (event.target == modal) {
                modal.style.display = 'none';
            }
        });
    </script>
</body>
</html>