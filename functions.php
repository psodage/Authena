<?php
/**
 * Common functions file for Authena
 */

/**
 * Get current user ID if logged in
 * 
 * @return int|null User ID or null if not logged in
 */
function getCurrentUserId() {
    if (isset($_SESSION['user_id'])) {
        return (int)$_SESSION['user_id'];
    }
    return null;
}

/**
 * Format date to readable format
 * 
 * @param string $date Date string
 * @param string $format Format string (default: 'F j, Y')
 * @return string Formatted date
 */
function formatDate($date, $format = 'F j, Y') {
    if (empty($date)) {
        return 'N/A';
    }
    return date($format, strtotime($date));
}

/**
 * Clean and validate input
 * 
 * @param string $data Input data
 * @return string Cleaned data
 */
function cleanInput($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

/**
 * Check if user is logged in
 * 
 * @return boolean True if logged in, false otherwise
 */
function isLoggedIn() {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

/**
 * Check if user has admin role
 * 
 * @return boolean True if admin, false otherwise
 */
function isAdmin() {
    return isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin';
}

/**
 * Generate a random string for secure tokens
 * 
 * @param int $length Length of random string
 * @return string Random string
 */
function generateRandomString($length = 32) {
    return bin2hex(random_bytes($length / 2));
}

/**
 * Log system activity
 * 
 * @param string $action Action description
 * @param string $details Additional details
 * @param int $userId User ID or null
 * @return void
 */
function logActivity($action, $details = '', $userId = null) {
    global $conn;
    
    if ($userId === null) {
        $userId = getCurrentUserId();
    }
    
    $ipAddress = $_SERVER['REMOTE_ADDR'];
    $userAgent = $_SERVER['HTTP_USER_AGENT'];
    
    // Only log if database connection exists
    if (isset($conn)) {
        $stmt = $conn->prepare("INSERT INTO activity_logs (user_id, action, details, ip_address, user_agent) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("issss", $userId, $action, $details, $ipAddress, $userAgent);
        $stmt->execute();
    }
}
?>