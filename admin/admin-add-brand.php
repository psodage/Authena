<?php
session_start();

// Database connection
$servername = "localhost";
$username = "root"; // Change as per your database credentials
$password = ""; // Change as per your database credentials
$dbname = "authena"; // Change as per your database name

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Initialize variables
$name = $website = $description = $contact_email = $contact_phone = $address = "";
$logo_url = "default_brand_logo.png"; // Default logo if no image is uploaded
$status = "active"; // Default status
$errors = [];
$success_message = "";

// Process form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Validate form data
    if (empty($_POST["name"])) {
        $errors[] = "Brand name is required";
    } else {
        $name = trim($_POST["name"]);
        
        // Check if brand already exists
        $stmt = $conn->prepare("SELECT id FROM brands WHERE name = ?");
        $stmt->bind_param("s", $name);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $errors[] = "Brand name already exists";
        }
        $stmt->close();
    }
    
    // Website validation (optional)
    if (!empty($_POST["website"])) {
        $website = trim($_POST["website"]);
        if (!filter_var($website, FILTER_VALIDATE_URL)) {
            $errors[] = "Invalid website format";
        }
    }
    
    // Email validation
    if (empty($_POST["contact_email"])) {
        $errors[] = "Contact email is required";
    } else {
        $contact_email = trim($_POST["contact_email"]);
        if (!filter_var($contact_email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = "Invalid email format";
        }
    }
    
    // Phone validation (optional)
    if (!empty($_POST["contact_phone"])) {
        $contact_phone = trim($_POST["contact_phone"]);
        // Simple phone validation - can be improved based on your requirements
        if (!preg_match("/^[0-9\-\(\)\/\+\s]*$/", $contact_phone)) {
            $errors[] = "Invalid phone number format";
        }
    }
    
    // Description and address validation
    $description = trim($_POST["description"]);
    $address = trim($_POST["address"]);
    
    // Status validation
    if (!empty($_POST["status"])) {
        $status = $_POST["status"];
        if (!in_array($status, ["active", "inactive"])) {
            $errors[] = "Invalid status";
        }
    }
    
    // Logo upload handling
    if (isset($_FILES["logo"]) && $_FILES["logo"]["error"] == 0) {
        $allowed = ["jpg" => "image/jpg", "jpeg" => "image/jpeg", "png" => "image/png", "gif" => "image/gif"];
        $filename = $_FILES["logo"]["name"];
        $filetype = $_FILES["logo"]["type"];
        $filesize = $_FILES["logo"]["size"];
        
        // Verify file extension
        $ext = pathinfo($filename, PATHINFO_EXTENSION);
        if (!array_key_exists($ext, $allowed)) {
            $errors[] = "Error: Please select a valid file format (JPG, JPEG, PNG, GIF).";
        }
        
        // Verify file size - 5MB maximum
        $maxsize = 5 * 1024 * 1024;
        if ($filesize > $maxsize) {
            $errors[] = "Error: File size is larger than the allowed limit (5MB).";
        }
        
        // Verify MIME type
        if (in_array($filetype, $allowed)) {
            // Check if file exists
            $upload_dir = "uploads/brand_logos/";
            
            // Create directory if it doesn't exist
            if (!file_exists($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }
            
            // Create a unique filename
            $new_filename = uniqid() . "." . $ext;
            $upload_path = $upload_dir . $new_filename;
            
            // Move the file
            if (move_uploaded_file($_FILES["logo"]["tmp_name"], $upload_path)) {
                $logo_url = $upload_path;
            } else {
                $errors[] = "Error: There was an issue uploading your file. Please try again.";
            }
        } else {
            $errors[] = "Error: There was a problem with your upload. Please try again.";
        }
    }
    
    // If no errors, insert into database
    if (empty($errors)) {
        $stmt = $conn->prepare("INSERT INTO brands (name, logo_url, website, description, contact_email, contact_phone, address, status, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())");
        $stmt->bind_param("ssssssss", $name, $logo_url, $website, $description, $contact_email, $contact_phone, $address, $status);
        
        if ($stmt->execute()) {
            $success_message = "Brand added successfully!";
            // Reset form fields after successful submission
            $name = $website = $description = $contact_email = $contact_phone = $address = "";
            $logo_url = "default_brand_logo.png";
            $status = "active";
        } else {
            $errors[] = "Error: " . $stmt->error;
        }
        
        $stmt->close();
    }
}

// Get admin info
$adminName = $_SESSION['admin_name'] ?? 'Admin User';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Authena Admin - Add Brand</title>
    <!-- Favicon -->
    <link rel="shortcut icon" href="assets/images/favicon.ico" type="image/x-icon">
    <!-- Font Awesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="dash.css">
    <style>
        .form-container {
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            padding: 24px;
            margin-bottom: 24px;
        }
        
        .form-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 20px;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-group.full-width {
            grid-column: span 2;
        }
        
        .form-label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            color: #374151;
        }
        
        .form-control {
            width: 100%;
            padding: 10px 14px;
            border: 1px solid #d1d5db;
            border-radius: 6px;
            background-color: #f9fafb;
            font-size: 14px;
            transition: border-color 0.2s ease-in-out, box-shadow 0.2s ease-in-out;
        }
        
        .form-control:focus {
            border-color: #4f46e5;
            box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.1);
            outline: none;
        }
        
        textarea.form-control {
            min-height: 120px;
            resize: vertical;
        }
        
        .select-control {
            width: 100%;
            padding: 10px 14px;
            border: 1px solid #d1d5db;
            border-radius: 6px;
            background-color: #f9fafb;
            font-size: 14px;
            appearance: none;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='16' height='16' fill='%236b7280' viewBox='0 0 16 16'%3E%3Cpath d='M8 10.293l4.146-4.147a.5.5 0 01.708.708l-4.5 4.5a.5.5 0 01-.708 0l-4.5-4.5a.5.5 0 01.708-.708L8 10.293z'/%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-position: right 14px center;
        }
        
        .btn-primary {
            background-color: #4f46e5;
            color: #ffffff;
            border: none;
            border-radius: 6px;
            padding: 10px 20px;
            font-weight: 500;
            cursor: pointer;
            transition: background-color 0.2s ease-in-out;
        }
        
        .btn-primary:hover {
            background-color: #4338ca;
        }
        
        .btn-cancel {
            background-color: #f3f4f6;
            color: #4b5563;
            border: 1px solid #d1d5db;
            border-radius: 6px;
            padding: 10px 20px;
            font-weight: 500;
            cursor: pointer;
            transition: background-color 0.2s ease-in-out;
            margin-right: 10px;
        }
        
        .btn-cancel:hover {
            background-color: #e5e7eb;
        }
        
        .form-actions {
            display: flex;
            justify-content: flex-end;
            margin-top: 20px;
        }
        
        .file-upload {
            position: relative;
            display: inline-block;
            width: 100%;
        }
        
        .file-upload-label {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 100%;
            padding: 40px 20px;
            border: 2px dashed #d1d5db;
            border-radius: 6px;
            background-color: #f9fafb;
            cursor: pointer;
            transition: border-color 0.2s ease-in-out;
            text-align: center;
            flex-direction: column;
        }
        
        .file-upload-label:hover {
            border-color: #4f46e5;
        }
        
        .file-upload-label i {
            font-size: 32px;
            color: #6b7280;
            margin-bottom: 10px;
        }
        
        .file-upload-label span {
            color: #4b5563;
            font-size: 14px;
        }
        
        .file-upload-label .file-name {
            margin-top: 8px;
            font-size: 13px;
            color: #4f46e5;
            word-break: break-all;
        }
        
        .file-upload input[type="file"] {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            opacity: 0;
            cursor: pointer;
        }
        
        .alert {
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 6px;
        }
        
        .alert-danger {
            background-color: #fee2e2;
            color: #b91c1c;
            border: 1px solid #fecaca;
        }
        
        .alert-success {
            background-color: #d1fae5;
            color: #065f46;
            border: 1px solid #a7f3d0;
        }
        
        .alert ul {
            margin: 0;
            padding-left: 20px;
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
                    <li><a href="admin-add-brand.php" class="active"><i class="fas fa-plus-square"></i> Add Brand</a></li>
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

            <!-- Add Brand Content -->
            <div class="dashboard">
                <div class="dashboard-header">
                    <h1>Add New Brand</h1>
                    <div class="breadcrumb">
                        <a href="admin-dashboard.php">Home</a>
                        <span>/</span>
                        <a href="admin-brands.php">Brands</a>
                        <span>/</span>
                        <a href="admin-add-brand.php">Add Brand</a>
                    </div>
                </div>

                <!-- Alert Messages -->
                <?php if (!empty($errors)): ?>
                <div class="alert alert-danger">
                    <ul>
                        <?php foreach ($errors as $error): ?>
                            <li><?php echo htmlspecialchars($error); ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
                <?php endif; ?>

                <?php if (!empty($success_message)): ?>
                <div class="alert alert-success">
                    <?php echo htmlspecialchars($success_message); ?>
                </div>
                <?php endif; ?>

                <!-- Add Brand Form -->
                <div class="form-container">
                    <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="POST" enctype="multipart/form-data">
                        <div class="form-grid">
                            <!-- Brand Logo Upload -->
                            <div class="form-group">
                                <label class="form-label">Brand Logo</label>
                                <div class="file-upload">
                                    <label class="file-upload-label" id="fileUploadLabel">
                                        <i class="fas fa-cloud-upload-alt"></i>
                                        <span>Drag & drop a file or click to browse</span>
                                        <span class="file-name" id="fileName"></span>
                                    </label>
                                    <input type="file" name="logo" id="logoUpload" accept=".jpg,.jpeg,.png,.gif">
                                </div>
                            </div>

                            <!-- Brand Details -->
                            <div class="form-group">
                                <label class="form-label" for="brandName">Brand Name *</label>
                                <input type="text" class="form-control" id="brandName" name="name" value="<?php echo htmlspecialchars($name); ?>" required>
                            </div>

                            <div class="form-group">
                                <label class="form-label" for="website">Website</label>
                                <input type="url" class="form-control" id="website" name="website" value="<?php echo htmlspecialchars($website); ?>" placeholder="https://example.com">
                            </div>

                            <div class="form-group">
                                <label class="form-label" for="contactEmail">Contact Email *</label>
                                <input type="email" class="form-control" id="contactEmail" name="contact_email" value="<?php echo htmlspecialchars($contact_email); ?>" required>
                            </div>

                            <div class="form-group">
                                <label class="form-label" for="contactPhone">Contact Phone</label>
                                <input type="tel" class="form-control" id="contactPhone" name="contact_phone" value="<?php echo htmlspecialchars($contact_phone); ?>" placeholder="+1 (123) 456-7890">
                            </div>

                            <div class="form-group">
                                <label class="form-label" for="status">Status</label>
                                <select class="select-control" id="status" name="status">
                                    <option value="active" <?php echo ($status == "active") ? "selected" : ""; ?>>Active</option>
                                    <option value="inactive" <?php echo ($status == "inactive") ? "selected" : ""; ?>>Inactive</option>
                                </select>
                            </div>

                            <div class="form-group full-width">
                                <label class="form-label" for="address">Address</label>
                                <input type="text" class="form-control" id="address" name="address" value="<?php echo htmlspecialchars($address); ?>" placeholder="123 Main St, City, Country">
                            </div>

                            <div class="form-group full-width">
                                <label class="form-label" for="description">Description</label>
                                <textarea class="form-control" id="description" name="description" rows="4"><?php echo htmlspecialchars($description); ?></textarea>
                            </div>
                        </div>

                        <div class="form-actions">
                            <a href="admin-brands.php" class="btn-cancel">Cancel</a>
                            <button type="submit" class="btn-primary">Add Brand</button>
                        </div>
                    </form>
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

        // File upload preview
        document.getElementById('logoUpload').addEventListener('change', function(e) {
            const fileName = e.target.files[0] ? e.target.files[0].name : '';
            document.getElementById('fileName').textContent = fileName;
        });

        // Notification dropdown functionality
        document.querySelector('.notifications').addEventListener('click', function() {
            // Here you would toggle a notification dropdown
            console.log('Notification clicked');
        });

        // Success message fade out
        const successAlert = document.querySelector('.alert-success');
        if (successAlert) {
            setTimeout(function() {
                successAlert.style.opacity = '0';
                successAlert.style.transition = 'opacity 1s ease';
                setTimeout(function() {
                    successAlert.style.display = 'none';
                }, 1000);
            }, 3000);
        }
    </script>
</body>
</html>