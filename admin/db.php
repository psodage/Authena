<?php
/**
 * Database Configuration File
 * 
 * This file establishes the connection to the MySQL database for the Authena
 * fake product identification platform.
 * 
 * @package Authena
 */

// Database credentials
define('DB_SERVER', 'localhost');     // Database server
define('DB_USERNAME', 'root'); // Database username
define('DB_PASSWORD', ''); // Database password
define('DB_NAME', 'authena');      // Database name

// Attempt to connect to MySQL database
$conn = new mysqli(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);

// Check connection
if ($conn->connect_error) {
    // Log the error to a file (you might want to set up proper error logging)
    error_log('Database connection failed: ' . $conn->connect_error, 0);
    
    // Display user-friendly message (in production, you may want a more generic message)
    die("Connection failed: Unable to connect to the database. Please contact system administrator.");
}

// Set character set to utf8mb4 to support full Unicode character set including emojis
$conn->set_charset("utf8mb4");

// Optional: Set default timezone for proper timestamp handling
date_default_timezone_set('UTC');

/**
 * Helper function to sanitize user inputs
 * 
 * @param string $data The data to be sanitized
 * @return string The sanitized data
 */
function sanitize_input($data) {
    global $conn;
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $conn->real_escape_string($data);
}

/**
 * Helper function to execute prepared statements
 * 
 * @param string $query SQL query with placeholders
 * @param string $types Parameter types ('s' for string, 'i' for integer, 'd' for double, 'b' for blob)
 * @param array $params Array of parameters to bind
 * @return mysqli_stmt|false Returns the statement object or false on failure
 */
function execute_query($query, $types = '', $params = []) {
    global $conn;
    
    $stmt = $conn->prepare($query);
    
    if (!$stmt) {
        error_log("Query preparation failed: " . $conn->error);
        return false;
    }
    
    // If we have parameters, bind them
    if (!empty($params)) {
        $stmt->bind_param($types, ...$params);
    }
    
    // Execute the statement
    if (!$stmt->execute()) {
        error_log("Query execution failed: " . $stmt->error);
        return false;
    }
    
    return $stmt;
}

/**
 * Helper function for quickly fetching all results from a SELECT query
 * 
 * @param string $query SQL query with placeholders
 * @param string $types Parameter types ('s' for string, 'i' for integer, etc.)
 * @param array $params Array of parameters to bind
 * @return array|false Returns an array of records or false on failure
 */
function fetch_all($query, $types = '', $params = []) {
    $stmt = execute_query($query, $types, $params);
    
    if (!$stmt) {
        return false;
    }
    
    $result = $stmt->get_result();
    $rows = $result->fetch_all(MYSQLI_ASSOC);
    
    $stmt->close();
    return $rows;
}

/**
 * Helper function for fetching a single row from a SELECT query
 * 
 * @param string $query SQL query with placeholders
 * @param string $types Parameter types ('s' for string, 'i' for integer, etc.)
 * @param array $params Array of parameters to bind
 * @return array|false Returns a single record as an associative array or false on failure
 */
function fetch_one($query, $types = '', $params = []) {
    $stmt = execute_query($query, $types, $params);
    
    if (!$stmt) {
        return false;
    }
    
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    
    $stmt->close();
    return $row;
}

/**
 * Helper function for inserting data and returning the auto-increment ID
 * 
 * @param string $query SQL query with placeholders
 * @param string $types Parameter types ('s' for string, 'i' for integer, etc.)
 * @param array $params Array of parameters to bind
 * @return int|false Returns the last inserted ID or false on failure
 */
function insert($query, $types = '', $params = []) {
    $stmt = execute_query($query, $types, $params);
    
    if (!$stmt) {
        return false;
    }
    
    $last_id = $conn->insert_id;
    $stmt->close();
    return $last_id;
}

// Database connection is now established and ready for use in other files