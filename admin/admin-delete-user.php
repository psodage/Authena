<?php
session_start();



// Check if the request method is POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $_SESSION['error'] = "Invalid request method.";
    header('Location: admin-users.php');
    exit();
}

// Check if user_id is provided
if (!isset($_POST['user_id']) || empty($_POST['user_id'])) {
    $_SESSION['error'] = "No user specified for deletion.";
    header('Location: admin-users.php');
    exit();
}

$user_id = (int)$_POST['user_id'];

// Database connection
$host = "localhost";
$dbname = "authena";
$username = "root";
$password = "";

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Begin transaction to ensure data integrity
    $pdo->beginTransaction();
    
    // First verify the user exists
    $checkStmt = $pdo->prepare("SELECT username FROM users WHERE id = :user_id");
    $checkStmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
    $checkStmt->execute();
    
    if ($checkStmt->rowCount() === 0) {
        // User not found
        $_SESSION['error'] = "User not found.";
        header('Location: admin-users.php');
        exit();
    }
    
    $userData = $checkStmt->fetch(PDO::FETCH_ASSOC);
    $username = $userData['username'];
    
    // Option 1: Delete related records first (if you want to completely remove the user)
    // Delete user's verification logs
    $logStmt = $pdo->prepare("DELETE FROM verification_logs WHERE user_id = :user_id");
    $logStmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
    $logStmt->execute();
    
    // Delete user's reports
    $reportStmt = $pdo->prepare("DELETE FROM fake_reports WHERE reported_by = :user_id");
    $reportStmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
    $reportStmt->execute();
    
    // Delete user's other related data (adjust tables as needed)
    // [Add more delete statements here for other related tables]
    
    // Finally delete the user
    $deleteStmt = $pdo->prepare("DELETE FROM users WHERE id = :user_id");
    $deleteStmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
    $deleteStmt->execute();
    
    // Option 2: Alternative approach - mark user as deleted instead of actually deleting
    // Uncomment this block and comment out the deletion blocks above if you prefer this approach
    /*
    $updateStmt = $pdo->prepare("UPDATE users SET status = 'deleted', is_deleted = 1, 
                               deleted_at = NOW(), email = CONCAT(email, '_deleted_', :user_id) 
                               WHERE id = :user_id");
    $updateStmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
    $updateStmt->execute();
    */
    
    // Log the deletion action for audit purposes
    $adminId = $_SESSION['admin_id'];
    $logActionStmt = $pdo->prepare("INSERT INTO admin_logs (admin_id, action, entity_id, entity_type, details, ip_address) 
                                   VALUES (:admin_id, 'delete', :user_id, 'user', :details, :ip)");
    $details = "Deleted user: " . $username;
    $ip = $_SERVER['REMOTE_ADDR'];
    
    $logActionStmt->bindParam(':admin_id', $adminId, PDO::PARAM_INT);
    $logActionStmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
    $logActionStmt->bindParam(':details', $details, PDO::PARAM_STR);
    $logActionStmt->bindParam(':ip', $ip, PDO::PARAM_STR);
    $logActionStmt->execute();
    
    // Commit the transaction
    $pdo->commit();
    
    // Set success message and redirect
    $_SESSION['success'] = "User deleted successfully.";
    
    // Preserve any search, sort, and filter parameters when redirecting
    $redirectUrl = 'admin-users.php';
    if (isset($_SERVER['HTTP_REFERER'])) {
        $refererParts = parse_url($_SERVER['HTTP_REFERER']);
        if (isset($refererParts['query'])) {
            parse_str($refererParts['query'], $queryParams);
            // Remove any sensitive parameters
            unset($queryParams['token']);
            
            if (!empty($queryParams)) {
                $redirectUrl .= '?' . http_build_query($queryParams);
            }
        }
    }
    
    header('Location: ' . $redirectUrl);
    exit();
    
} catch (PDOException $e) {
    // Rollback the transaction in case of error
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    
    // Log the error (to a file, not just to screen)
    error_log("Error deleting user ID $user_id: " . $e->getMessage());
    
    // Set error message and redirect
    $_SESSION['error'] = "Error deleting user. Please try again or contact support.";
    header('Location: admin-users.php');
    exit();
}
?>