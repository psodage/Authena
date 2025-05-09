<?php
// verify_product.php - Handles product verification API requests

session_start();
// Database connection
$db_host = "localhost";
$db_user = "root";  // Replace with your database username
$db_pass = "";  // Replace with your database password
$db_name = "authena";

$conn = new mysqli($db_host, $db_user, $db_pass, $db_name);
if ($conn->connect_error) {
    die(json_encode(['status' => 'error', 'message' => 'Database connection failed']));
}

header('Content-Type: application/json');

// API endpoint to verify product
if (isset($_POST['action']) && $_POST['action'] == 'verify_product') {
    $qrCode = $_POST['qr_code'];
    $userId = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;
    $ip = $_SERVER['REMOTE_ADDR'];
    $deviceInfo = $_SERVER['HTTP_USER_AGENT'];
    $lat = isset($_POST['lat']) ? $_POST['lat'] : null;
    $lng = isset($_POST['lng']) ? $_POST['lng'] : null;
    $address = isset($_POST['address']) ? $_POST['address'] : null;
    
    // Check if product exists in database
    $stmt = $conn->prepare("SELECT p.*, b.name as brand_name, c.name as category_name 
                           FROM products p 
                           LEFT JOIN brands b ON p.brand_id = b.id 
                           LEFT JOIN categories c ON p.category_id = c.id 
                           WHERE p.unique_identifier = ? OR p.product_code = ?");
    $stmt->bind_param("ss", $qrCode, $qrCode);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $response = [
        'status' => 'unknown',
        'product' => null,
        'message' => 'Unknown product'
    ];
    
    if ($result->num_rows > 0) {
        $product = $result->fetch_assoc();
        
        // Check if product is active
        if ($product['status'] == 'active') {
            $response = [
                'status' => 'authentic',
                'product' => $product,
                'message' => 'Authentic product verified'
            ];
        } else {
            $response = [
                'status' => 'fake',
                'product' => null,
                'message' => 'This product is not active in our system'
            ];
        }
        
        // Log verification
        $verificationStatus = $response['status'];
        $stmt = $conn->prepare("INSERT INTO verification_logs (product_id, user_id, ip_address, device_info, 
                               location_lat, location_lng, location_address, status) 
                               VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        
        // Convert $userId to null if it's empty (for binding to work correctly)
        $userId = $userId ?: null;
        
        // Fix binding parameters
        $stmt->bind_param("iissddss", $product['id'], $userId, $ip, $deviceInfo, $lat, $lng, $address, $verificationStatus);
        $stmt->execute();
        
        // Update product verification count
        $stmt = $conn->prepare("UPDATE products SET verification_count = verification_count + 1 WHERE id = ?");
        $stmt->bind_param("i", $product['id']);
        $stmt->execute();
        
        // Update user verification count if user is logged in
        if ($userId) {
            $stmt = $conn->prepare("UPDATE users SET verification_count = verification_count + 1 WHERE id = ?");
            $stmt->bind_param("i", $userId);
            $stmt->execute();
        }
    }
    
    echo json_encode($response);
    exit;
}

// API endpoint to report fake product
if (isset($_POST['action']) && $_POST['action'] == 'report_fake') {
    $userId = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;
    $reportType = isset($_POST['report_type']) ? $_POST['report_type'] : 'counterfeit';
    $description = isset($_POST['description']) ? $_POST['description'] : '';
    $lat = isset($_POST['lat']) ? $_POST['lat'] : null;
    $lng = isset($_POST['lng']) ? $_POST['lng'] : null;
    $address = isset($_POST['address']) ? $_POST['address'] : null;
    
    // Check if product_id or product_code was provided
    $productId = null;
    if (isset($_POST['product_id']) && !empty($_POST['product_id'])) {
        $productId = $_POST['product_id'];
    } elseif (isset($_POST['product_code']) && !empty($_POST['product_code'])) {
        // Find product ID from code
        $productCode = $_POST['product_code'];
        $stmt = $conn->prepare("SELECT id FROM products WHERE unique_identifier = ? OR product_code = ?");
        $stmt->bind_param("ss", $productCode, $productCode);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $product = $result->fetch_assoc();
            $productId = $product['id'];
        } else {
            // If product doesn't exist, create a temporary record for the report
            $stmt = $conn->prepare("INSERT INTO products (product_code, unique_identifier, status, name) VALUES (?, ?, 'reported', 'Reported Product')");
            $stmt->bind_param("ss", $productCode, $productCode);
            $stmt->execute();
            $productId = $conn->insert_id;
        }
    }
    
    if ($productId) {
        try {
            // Convert userId to null if it's empty (for binding to work correctly)
            $userId = $userId ?: null;
            
            // Fix null values for lat/lng
            $lat = $lat ?: null;
            $lng = $lng ?: null;
            
            // Insert into fake_reports table
            $stmt = $conn->prepare("INSERT INTO fake_reports (product_id, user_id, report_type, description, 
                                   location_lat, location_lng, location_address) 
                                   VALUES (?, ?, ?, ?, ?, ?, ?)");
            
            $stmt->bind_param("iissdds", $productId, $userId, $reportType, $description, $lat, $lng, $address);
            $result = $stmt->execute();
            
            if ($result) {
                // Update user report count if user is logged in
                if ($userId) {
                    $stmt = $conn->prepare("UPDATE users SET report_count = report_count + 1 WHERE id = ?");
                    $stmt->bind_param("i", $userId);
                    $stmt->execute();
                }
                
                echo json_encode(['success' => true, 'message' => 'Report submitted successfully']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Database error: ' . $stmt->error]);
            }
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'No product identifier provided']);
    }
    exit;
}

// Default response for invalid requests
echo json_encode(['status' => 'error', 'message' => 'Invalid request']);
?>