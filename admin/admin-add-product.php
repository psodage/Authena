<?php
session_start();

// Check if admin is logged in, redirect if not

// Database connection
require_once 'db.php';

// Get admin info
$adminName = $_SESSION['admin_name'] ?? 'Admin User';

// Fetch all brands for dropdown
$brandQuery = "SELECT id, name FROM brands WHERE status = 'active' ORDER BY name ASC";
$brandResult = $conn->query($brandQuery);
$brands = [];
if ($brandResult && $brandResult->num_rows > 0) {
    while ($row = $brandResult->fetch_assoc()) {
        $brands[] = $row;
    }
}

// Fetch all categories for dropdown
$categoryQuery = "SELECT id, name FROM categories WHERE status = 'active' ORDER BY name ASC";
$categoryResult = $conn->query($categoryQuery);
$categories = [];
if ($categoryResult && $categoryResult->num_rows > 0) {
    while ($row = $categoryResult->fetch_assoc()) {
        $categories[] = $row;
    }
}

// Process form submission
$errors = [];
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate and sanitize inputs
    $name = trim($_POST['name'] ?? '');
    $product_code = trim($_POST['product_code'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $brand_id = intval($_POST['brand_id'] ?? 0);
    $category_id = intval($_POST['category_id'] ?? 0);
    $manufacturing_date = trim($_POST['manufacturing_date'] ?? '');
    $expiry_date = trim($_POST['expiry_date'] ?? '');
    $batch_number = trim($_POST['batch_number'] ?? '');
    $price = floatval($_POST['price'] ?? 0);
    $status = trim($_POST['status'] ?? 'active');
    
    // Generate unique identifier
    $unique_identifier = bin2hex(random_bytes(8));
    
    // Validate required fields
    if (empty($name)) $errors[] = "Product name is required";
    if (empty($product_code)) $errors[] = "Product code is required";
    if ($brand_id <= 0) $errors[] = "Please select a brand";
    if ($category_id <= 0) $errors[] = "Please select a category";
    
    // Handle image upload
    $image_url = '';
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $target_dir = "uploads/products/";
        
        // Create directory if it doesn't exist
        if (!file_exists($target_dir)) {
            mkdir($target_dir, 0777, true);
        }
        
        $file_extension = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
        $file_name = uniqid('product_') . '.' . $file_extension;
        $target_file = $target_dir . $file_name;
        
        // Check file type
        $allowed_extensions = ['jpg', 'jpeg', 'png', 'webp'];
        if (!in_array($file_extension, $allowed_extensions)) {
            $errors[] = "Only JPG, JPEG, PNG, and WEBP files are allowed";
        } else {
            // Move uploaded file to target directory
            if (move_uploaded_file($_FILES['image']['tmp_name'], $target_file)) {
                $image_url = $target_file;
            } else {
                $errors[] = "Failed to upload image";
            }
        }
    }
    
    // Handle additional images
    $additional_images = [];
    if (isset($_FILES['additional_images'])) {
        $file_count = count($_FILES['additional_images']['name']);
        
        for ($i = 0; $i < $file_count; $i++) {
            if ($_FILES['additional_images']['error'][$i] === UPLOAD_ERR_OK) {
                $target_dir = "uploads/products/";
                
                $file_extension = strtolower(pathinfo($_FILES['additional_images']['name'][$i], PATHINFO_EXTENSION));
                $file_name = uniqid('product_add_') . '.' . $file_extension;
                $target_file = $target_dir . $file_name;
                
                // Check file type
                $allowed_extensions = ['jpg', 'jpeg', 'png', 'webp'];
                if (in_array($file_extension, $allowed_extensions)) {
                    // Move uploaded file to target directory
                    if (move_uploaded_file($_FILES['additional_images']['tmp_name'][$i], $target_file)) {
                        $additional_images[] = $target_file;
                    }
                }
            }
        }
    }
    
    // Encode additional images as JSON
    $additional_images_json = json_encode($additional_images);
    
    // Generate QR code URL (you would need a QR code generation library)
    // For now, we'll just create a placeholder
    $qr_code_url = 'qrcodes/' . $unique_identifier . '.png';
    
    // If no errors, insert into database
    if (empty($errors)) {
        // Generate QR code (you would need a QR code generation library)
        // This is a placeholder for where you would generate the actual QR code
        // For example: generateQRCode($unique_identifier, $qr_code_url);
        
        // Prepare SQL statement
        $sql = "INSERT INTO products (
                    name, 
                    product_code, 
                    description, 
                    brand_id, 
                    category_id, 
                    qr_code_url, 
                    unique_identifier, 
                    manufacturing_date, 
                    expiry_date, 
                    batch_number, 
                    image_url, 
                    additional_images, 
                    price, 
                    status, 
                    verification_count, 
                    created_at, 
                    updated_at
                ) VALUES (
                    ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 0, NOW(), NOW()
                )";
        
        $stmt = $conn->prepare($sql);
        $stmt->bind_param(
            "sssiiissssssds",
            $name,
            $product_code,
            $description,
            $brand_id,
            $category_id,
            $qr_code_url,
            $unique_identifier,
            $manufacturing_date,
            $expiry_date,
            $batch_number,
            $image_url,
            $additional_images_json,
            $price,
            $status
        );
        
        if ($stmt->execute()) {
            $success = "Product added successfully!";
            // Clear form after successful submission
            $_POST = [];
        } else {
            $errors[] = "Error: " . $stmt->error;
        }
        
        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Authena Admin - Add Product</title>
    <!-- Favicon -->
    <link rel="shortcut icon" href="assets/images/favicon.ico" type="image/x-icon">
    <!-- Font Awesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <!-- Flatpickr for date inputs -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="dash.css">
    <style>
        .form-container {
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
            padding: 25px;
            margin-bottom: 20px;
        }
        
        .form-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            color: #374151;
        }
        
        .form-control {
            width: 100%;
            padding: 10px 12px;
            border: 1px solid #d1d5db;
            border-radius: 6px;
            font-size: 14px;
            color: #1f2937;
            transition: border-color 0.3s;
        }
        
        .form-control:focus {
            border-color: #4f46e5;
            outline: none;
            box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.1);
        }
        
        textarea.form-control {
            min-height: 120px;
            resize: vertical;
        }
        
        .form-full {
            grid-column: span 2;
        }
        
        .form-actions {
            display: flex;
            justify-content: flex-end;
            margin-top: 20px;
            gap: 10px;
        }
        
        .btn {
            padding: 10px 16px;
            border-radius: 6px;
            font-weight: 500;
            font-size: 14px;
            cursor: pointer;
            transition: all 0.3s;
            border: none;
        }
        
        .btn-primary {
            background-color: #4f46e5;
            color: white;
        }
        
        .btn-primary:hover {
            background-color: #4338ca;
        }
        
        .btn-secondary {
            background-color: #f3f4f6;
            color: #374151;
        }
        
        .btn-secondary:hover {
            background-color: #e5e7eb;
        }
        
        .alert {
            padding: 15px;
            border-radius: 6px;
            margin-bottom: 20px;
        }
        
        .alert-success {
            background-color: #ecfdf5;
            color: #065f46;
            border: 1px solid #a7f3d0;
        }
        
        .alert-danger {
            background-color: #fef2f2;
            color: #991b1b;
            border: 1px solid #fecaca;
        }
        
        .image-preview {
            margin-top: 10px;
            max-width: 200px;
            position: relative;
        }
        
        .image-preview img {
            width: 100%;
            border-radius: 6px;
            border: 1px solid #d1d5db;
        }
        
        .required-field::after {
            content: "*";
            color: #ef4444;
            margin-left: 4px;
        }
        
        .file-upload {
            position: relative;
            overflow: hidden;
            display: inline-block;
            cursor: pointer;
        }
        
        .file-upload input[type="file"] {
            font-size: 100px;
            position: absolute;
            left: 0;
            top: 0;
            opacity: 0;
            cursor: pointer;
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
                    <li><a href="admin-add-product.php" class="active"><i class="fas fa-plus-circle"></i> Add Product</a></li>
                 
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
                            <h4><?php echo htmlspecialchars($adminName); ?></h4>
                            <small>Admin</small>
                        </div>
                    </div>
                </div>
            </header>

            <!-- Add Product Content -->
            <div class="dashboard">
                <div class="dashboard-header">
                    <h1>Add New Product</h1>
                    <div class="breadcrumb">
                        <a href="admin-dashboard.php">Home</a>
                        <span>/</span>
                        <a href="admin-products.php">Products</a>
                        <span>/</span>
                        <a href="admin-add-product.php">Add Product</a>
                    </div>
                </div>

                <!-- Form Container -->
                <div class="form-container">
                    <?php if (!empty($errors)): ?>
                        <div class="alert alert-danger">
                            <strong>Error!</strong>
                            <ul>
                                <?php foreach ($errors as $error): ?>
                                    <li><?php echo htmlspecialchars($error); ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    <?php endif; ?>

                    <?php if ($success): ?>
                        <div class="alert alert-success">
                            <strong>Success!</strong> <?php echo htmlspecialchars($success); ?>
                        </div>
                    <?php endif; ?>

                    <form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="POST" enctype="multipart/form-data">
                        <div class="form-grid">
                            <div class="form-group">
                                <label for="name" class="required-field">Product Name</label>
                                <input type="text" id="name" name="name" class="form-control" value="<?php echo htmlspecialchars($_POST['name'] ?? ''); ?>" required>
                            </div>

                            <div class="form-group">
                                <label for="product_code" class="required-field">Product Code</label>
                                <input type="text" id="product_code" name="product_code" class="form-control" value="<?php echo htmlspecialchars($_POST['product_code'] ?? ''); ?>" required>
                            </div>

                            <div class="form-group">
                                <label for="brand_id" class="required-field">Brand</label>
                                <select id="brand_id" name="brand_id" class="form-control" required>
                                    <option value="">Select Brand</option>
                                    <?php foreach ($brands as $brand): ?>
                                        <option value="<?php echo $brand['id']; ?>" <?php echo (isset($_POST['brand_id']) && $_POST['brand_id'] == $brand['id']) ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($brand['name']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="form-group">
                                <label for="category_id" class="required-field">Category</label>
                                <select id="category_id" name="category_id" class="form-control" required>
                                    <option value="">Select Category</option>
                                    <?php foreach ($categories as $category): ?>
                                        <option value="<?php echo $category['id']; ?>" <?php echo (isset($_POST['category_id']) && $_POST['category_id'] == $category['id']) ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($category['name']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="form-group form-full">
                                <label for="description">Product Description</label>
                                <textarea id="description" name="description" class="form-control"><?php echo htmlspecialchars($_POST['description'] ?? ''); ?></textarea>
                            </div>

                            <div class="form-group">
                                <label for="manufacturing_date">Manufacturing Date</label>
                                <input type="text" id="manufacturing_date" name="manufacturing_date" class="form-control date-picker" value="<?php echo htmlspecialchars($_POST['manufacturing_date'] ?? ''); ?>" placeholder="YYYY-MM-DD">
                            </div>

                            <div class="form-group">
                                <label for="expiry_date">Expiry Date</label>
                                <input type="text" id="expiry_date" name="expiry_date" class="form-control date-picker" value="<?php echo htmlspecialchars($_POST['expiry_date'] ?? ''); ?>" placeholder="YYYY-MM-DD">
                            </div>

                            <div class="form-group">
                                <label for="batch_number">Batch Number</label>
                                <input type="text" id="batch_number" name="batch_number" class="form-control" value="<?php echo htmlspecialchars($_POST['batch_number'] ?? ''); ?>">
                            </div>

                            <div class="form-group">
                                <label for="price">Price</label>
                                <input type="number" id="price" name="price" step="0.01" min="0" class="form-control" value="<?php echo htmlspecialchars($_POST['price'] ?? ''); ?>">
                            </div>

                            <div class="form-group">
                                <label for="image">Product Image</label>
                                <div class="file-upload">
                                    <input type="file" id="image" name="image" accept="image/*" onchange="previewImage(this)">
                                    <button type="button" class="btn btn-secondary">Choose File</button>
                                </div>
                                <div id="image-preview" class="image-preview"></div>
                            </div>

                            <div class="form-group">
                                <label for="additional_images">Additional Images</label>
                                <div class="file-upload">
                                    <input type="file" id="additional_images" name="additional_images[]" accept="image/*" multiple>
                                    <button type="button" class="btn btn-secondary">Choose Files</button>
                                </div>
                                <small>You can select multiple files</small>
                            </div>

                            <div class="form-group">
                                <label for="status">Status</label>
                                <select id="status" name="status" class="form-control">
                                    <option value="active" <?php echo (isset($_POST['status']) && $_POST['status'] == 'active') ? 'selected' : ''; ?>>Active</option>
                                    <option value="inactive" <?php echo (isset($_POST['status']) && $_POST['status'] == 'inactive') ? 'selected' : ''; ?>>Inactive</option>
                                </select>
                            </div>
                        </div>

                        <div class="form-actions">
                            <button type="reset" class="btn btn-secondary">Reset</button>
                            <button type="submit" class="btn btn-primary">Add Product</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script>
        // Toggle sidebar
        document.getElementById('toggleMenu').addEventListener('click', function() {
            document.querySelector('.sidebar').classList.toggle('active');
        });

        // Initialize date pickers
        flatpickr(".date-picker", {
            dateFormat: "Y-m-d",
            allowInput: true
        });

        // Preview image before upload
        function previewImage(input) {
            const preview = document.getElementById('image-preview');
            preview.innerHTML = '';
            
            if (input.files && input.files[0]) {
                const reader = new FileReader();
                
                reader.onload = function(e) {
                    const img = document.createElement('img');
                    img.src = e.target.result;
                    preview.appendChild(img);
                }
                
                reader.readAsDataURL(input.files[0]);
            }
        }

        // Make the file upload button work
        document.querySelectorAll('.file-upload').forEach(function(element) {
            const fileInput = element.querySelector('input[type="file"]');
            const button = element.querySelector('button');
            
            button.addEventListener('click', function() {
                fileInput.click();
            });
            
            fileInput.addEventListener('change', function() {
                let fileName = '';
                if (this.files && this.files.length > 0) {
                    fileName = this.files.length > 1 ? this.files.length + ' files selected' : this.files[0].name;
                }
                button.textContent = fileName || 'Choose File';
            });
        });
    </script>
</body>
</html>