<?php
// admin-delete-product.php
session_start();

// Check if user is logged in as admin

// Include database connection
require_once 'db.php';

// Check if form is submitted and product_id is provided
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['product_id'])) {
    $product_id = (int)$_POST['product_id'];
    
    // First, let's get the image URL to delete the image file if exists
    $query = "SELECT image_url FROM products WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param('i', $product_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($product = $result->fetch_assoc()) {
        // Delete the image file if it exists
        if (!empty($product['image_url']) && file_exists($product['image_url'])) {
            unlink($product['image_url']);
        }
        
        // Delete the product from database
        $delete_query = "DELETE FROM products WHERE id = ?";
        $delete_stmt = $conn->prepare($delete_query);
        $delete_stmt->bind_param('i', $product_id);
        
        if ($delete_stmt->execute()) {
            // Success - redirect with success message
            $_SESSION['success_message'] = "Product deleted successfully.";
        } else {
            // Error - redirect with error message
            $_SESSION['error_message'] = "Failed to delete product. Please try again.";
        }
    } else {
        $_SESSION['error_message'] = "Product not found.";
    }
} else {
    $_SESSION['error_message'] = "Invalid request.";
}

// Redirect back to products page
header('Location: admin-products.php');
exit;
?>