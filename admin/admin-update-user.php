<?php
session_start();

// Check if admin is logged in

// Check if form was submitted
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $_SESSION['error'] = "Invalid request method.";
    header('Location: admin-users.php');
    exit;
}

// Database connection
$host = "localhost";
$dbname = "authena";
$username = "root";
$password = "";

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    $_SESSION['error'] = "Database connection failed: " . $e->getMessage();
    header('Location: admin-users.php');
    exit;
}

// Get form data
$userId = isset($_POST['user_id']) ? (int)$_POST['user_id'] : 0;
$firstName = isset($_POST['first_name']) ? trim($_POST['first_name']) : '';
$lastName = isset($_POST['last_name']) ? trim($_POST['last_name']) : '';
$username = isset($_POST['username']) ? trim($_POST['username']) : '';
$email = isset($_POST['email']) ? trim($_POST['email']) : '';
$city = isset($_POST['city']) ? trim($_POST['city']) : '';
$country = isset($_POST['country']) ? trim($_POST['country']) : '';
$status = isset($_POST['status']) ? trim($_POST['status']) : '';

// Validate data
if ($userId <= 0 || empty($firstName) || empty($lastName) || empty($username) || empty($email) || empty($status)) {
    $_SESSION['error'] = "All required fields must be filled.";
    header('Location: admin-users.php');
    exit;
}

// Validate email
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $_SESSION['error'] = "Invalid email format.";
    header('Location: admin-users.php');
    exit;
}

// Validate status
$validStatuses = ['active', 'suspended'];
if (!in_array($status, $validStatuses)) {
    $_SESSION['error'] = "Invalid status value.";
    header('Location: admin-users.php');
    exit;
}

// Check if username or email already exists (except for the current user)
$checkQuery = "SELECT id FROM users WHERE (username = :username OR email = :email) AND id != :id";
$checkStmt = $pdo->prepare($checkQuery);
$checkStmt->bindValue(':username', $username);
$checkStmt->bindValue(':email', $email);
$checkStmt->bindValue(':id', $userId, PDO::PARAM_INT);
$checkStmt->execute();

if ($checkStmt->rowCount() > 0) {
    $_SESSION['error'] = "Username or email already exists.";
    header('Location: admin-users.php');
    exit;
}

// Update user data
$updateQuery = "UPDATE users SET 
                first_name = :first_name,
                last_name = :last_name,
                username = :username,
                email = :email,
                city = :city,
                country = :country,
                status = :status,
                updated_at = NOW()
                WHERE id = :id";

$updateStmt = $pdo->prepare($updateQuery);
$updateStmt->bindValue(':first_name', $firstName);
$updateStmt->bindValue(':last_name', $lastName);
$updateStmt->bindValue(':username', $username);
$updateStmt->bindValue(':email', $email);
$updateStmt->bindValue(':city', $city);
$updateStmt->bindValue(':country', $country);
$updateStmt->bindValue(':status', $status);
$updateStmt->bindValue(':id', $userId, PDO::PARAM_INT);

try {
    $updateStmt->execute();
    $_SESSION['success'] = "User updated successfully.";
} catch (PDOException $e) {
    $_SESSION['error'] = "Failed to update user: " . $e->getMessage();
}

// Redirect back to users page
header('Location: admin-users.php');
exit;
?>