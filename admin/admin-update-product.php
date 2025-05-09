<?php
// Start session for authentication
session_start();



// Database connection
require_once 'db.php';

// Initialize variables and sanitize inputs
$product_id = isset($_POST['product_id']) ? (int)$_POST['product_id'] : 0;
$name = isset($_POST['name']) ? $conn->real_escape_string(trim($_POST['name'])) : '';
$product_code = isset($_POST['product_code']) ? $conn->real_escape_string(trim($_POST['product_code'])) : '';
$brand_id = isset($_POST['brand_id']) ? (int)$_POST['brand_id'] : 0;
$category_id = isset($_POST['category_id']) ? (int)$_POST['category_id'] : 0;
$status = isset($_POST['status']) ? $conn->real_escape_string(trim($_POST['status'])) : 'pending';
$description = isset($_POST['description']) ? $conn->real_escape_string(trim($_POST['description'])) : '';

// Response array
$response = [
    'success' => false,
    'message' => '',
    'redirect' => 'admin-products.php'
];

// Validate required fields
if (empty($product_id) || empty($name) || empty($product_code) || empty($brand_id) || empty($category_id)) {
    $_SESSION['error'] = "All required fields must be filled out.";
    header("Location: admin-products.php");
    exit();
}

// Check if product exists
$check_query = "SELECT * FROM products WHERE id = ?";
$check_stmt = $conn->prepare($check_query);
$check_stmt->bind_param("i", $product_id);
$check_stmt->execute();
$result = $check_stmt->get_result();

if ($result->num_rows === 0) {
    $_SESSION['error'] = "Product not found.";
    header("Location: admin-products.php");
    exit();
}

$product = $result->fetch_assoc();
$current_image = $product['image_url'];

// Handle image upload if provided
$image_url = $current_image; // Default to current image
if (isset($_FILES['image']) && $_FILES['image']['size'] > 0) {
    $upload_dir = "uploads/products/";
    
    // Create directory if it doesn't exist
    if (!file_exists($upload_dir)) {
        mkdir($upload_dir, 0777, true);
    }
    
    $file_ext = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
    $valid_extensions = array("jpeg", "jpg", "png", "gif");
    
    if (in_array($file_ext, $valid_extensions)) {
        // Generate unique filename
        $new_filename = uniqid() . "." . $file_ext;
        $upload_path = $upload_dir . $new_filename;
        
        if (move_uploaded_file($_FILES['image']['tmp_name'], $upload_path)) {
            // Delete old image if exists and is not the default
            if (!empty($current_image) && file_exists($current_image) && $current_image != "assets/img/placeholder.png") {
                unlink($current_image);
            }
            
            $image_url = $upload_path;
        } else {
            $_SESSION['error'] = "Failed to upload image. Please try again.";
            header("Location: admin-products.php");
            exit();
        }
    } else {
        $_SESSION['error'] = "Only JPG, JPEG, PNG & GIF files are allowed.";
        header("Location: admin-products.php");
        exit();
    }
}

// Update product in database
$update_query = "UPDATE products SET 
                name = ?, 
                product_code = ?, 
                brand_id = ?, 
                category_id = ?, 
                status = ?, 
                description = ?, 
                image_url = ?,
                updated_at = NOW()
                WHERE id = ?";
                
$update_stmt = $conn->prepare($update_query);
$update_stmt->bind_param("ssiisssi", $name, $product_code, $brand_id, $category_id, $status, $description, $image_url, $product_id);

if ($update_stmt->execute()) {
    $_SESSION['success'] = "Product updated successfully.";
} else {
    $_SESSION['error'] = "Error updating product: " . $conn->error;
}

// Redirect back to products page
header("Location: admin-products.php");
exit();
?>