<?php
// Prevent PHP errors from corrupting JSON output
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Start session
session_start();

// Include database connection
require_once 'db_connection.php';

// Set proper JSON headers
header('Content-Type: application/json');

// Initialize response array
$response = [
    'success' => false,
    'message' => '',
    'redirect' => '',
    'register_link' => '../view/signup.html' // Ajout du lien d'inscription
];

try {
    // Check if form was submitted
    if ($_SERVER["REQUEST_METHOD"] != "POST") {
        throw new Exception("Invalid request method");
    }

    // Get and validate input
    $email = filter_var($_POST['email'] ?? '', FILTER_VALIDATE_EMAIL);
    $password = $_POST['password'] ?? '';

    if (!$email) {
        throw new Exception("Invalid email format");
    }
    if (empty($password)) {
        throw new Exception("Password is required");
    }

    // Query the database
    $stmt = $conn->prepare("SELECT user_id, full_name, email, password, is_admin, profile_completed FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if (!$user) {
        throw new Exception("User not found");
    }

    // Verify password
    if (!password_verify($password, $user['password'])) {
        throw new Exception("Invalid password");
    }

    // Set session variables
    $_SESSION['user_id'] = $user['user_id'];
    $_SESSION['full_name'] = $user['full_name'];
    $_SESSION['email'] = $user['email'];
    $_SESSION['is_admin'] = $user['is_admin'];

    // Set response
    $response['success'] = true;
    $response['message'] = "Login successful";

    // Determine redirect
    if ($user['is_admin']) {
        $response['redirect'] = "../../back_office/view/users.html";
    } else if ($user['profile_completed']) {
        $response['redirect'] = "../view/profile.php";
    } else {
        $response['redirect'] = "../view/profile-setup.html";
    }

} catch (Exception $e) {
    $response['message'] = $e->getMessage();
    $response['show_register'] = true;
} finally {
    // Return JSON response
    echo json_encode($response);
    exit();
}

// If not a POST request, redirect to sign-in page
header('Location: ../view/signin.html');
exit();
?>
