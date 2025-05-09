<?php
session_start();

// Database connection
require_once 'db_con.php';

// Check if admin is logged in (you may want to add this check)


// Check if ID parameter exists
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header('Location: admin-brands.php?error=Invalid brand ID');
    exit;
}

$brandId = (int)$_GET['id'];

try {
    // First check if the brand exists
    $checkQuery = "SELECT id FROM brands WHERE id = ?";
    $checkStmt = $conn->prepare($checkQuery);
    $checkStmt->execute([$brandId]);
    
    if ($checkStmt->rowCount() === 0) {
        header('Location: admin-brands.php?error=Brand not found');
        exit;
    }
    
    // Start transaction
    $conn->beginTransaction();
    
    // Delete associated verification logs
    $deleteLogsQuery = "DELETE FROM verification_logs WHERE id = ?";
    $deleteLogsStmt = $conn->prepare($deleteLogsQuery);
    $deleteLogsStmt->execute([$brandId]);
    
    // Delete associated products
    $deleteProductsQuery = "DELETE FROM products WHERE id = ?";
    $deleteProductsStmt = $conn->prepare($deleteProductsQuery);
    $deleteProductsStmt->execute([$brandId]);
    
    // Delete the brand
    $deleteBrandQuery = "DELETE FROM brands WHERE id = ?";
    $deleteBrandStmt = $conn->prepare($deleteBrandQuery);
    $deleteBrandStmt->execute([$brandId]);
    
    // Commit transaction
    $conn->commit();
    
    // Build redirect URL with parameters
    $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
    $search = isset($_GET['search']) ? $_GET['search'] : '';
    $status = isset($_GET['status']) ? $_GET['status'] : 'all';
    
    $redirectUrl = "admin-brands.php?success=Brand deleted successfully";
    if ($page > 1) {
        $redirectUrl .= "&page=" . $page;
    }
    if (!empty($search)) {
        $redirectUrl .= "&search=" . urlencode($search);
    }
    if ($status !== 'all') {
        $redirectUrl .= "&status=" . urlencode($status);
    }
    
    header("Location: $redirectUrl");
    exit;
    
} catch (PDOException $e) {
    // Rollback transaction on error
    $conn->rollBack();
    
    // Log the error for debugging
    error_log("Brand deletion error: " . $e->getMessage());
    
    // Redirect back with error message
    header('Location: admin-brands.php?error=' . urlencode('Failed to delete brand. Please try again.'));
    exit;
}
?>