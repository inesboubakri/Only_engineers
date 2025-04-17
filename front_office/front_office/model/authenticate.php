<?php
// Start session
session_start();

// Display errors for debugging during development
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Check if form was submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get user input
    $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
    $password = $_POST['password'] ?? '';
    
    // Database connection parameters
    $servername = "localhost";
    $username = "root";
    $db_password = "";
    $dbname = "onlyengs";
    
    // Validate input
    $errors = [];
    
    if (empty($email)) {
        $errors['email'] = "Email is required";
    } else if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = "Invalid email format";
    }
    
    if (empty($password)) {
        $errors['password'] = "Password is required";
    }
    
    // If there are validation errors, redirect to signin with errors
    if (!empty($errors)) {
        $_SESSION['signin_errors'] = $errors;
        header("Location: ../view/signin.php");
        exit();
    }
    
    // No validation errors, attempt to authenticate
    try {
        // Create connection using PDO
        $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $db_password);
        
        // Set the PDO error mode to exception
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        // Prepare SQL statement to find user by email
        $stmt = $conn->prepare("SELECT user_id, email, password, full_name, is_admin FROM users WHERE email = :email");
        $stmt->bindParam(':email', $email);
        $stmt->execute();
        
        // Check if user exists and password is correct
        if ($stmt->rowCount() > 0) {
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            
            // Verify password (assuming it's hashed with password_hash())
            if (password_verify($password, $user['password'])) {
                // Password is correct, set session variables
                $_SESSION['user_id'] = $user['user_id'];
                $_SESSION['user_email'] = $user['email'];
                $_SESSION['user_name'] = $user['full_name'];
                $_SESSION['is_admin'] = $user['is_admin']; // Store admin status in session
                
                // Check if user is admin and redirect accordingly
                if ($user['is_admin'] == 1) {
                    // Admin user - redirect to back office
                    header("Location: ../../../back_office/view/users.php"); // Updated to .php
                    exit();
                } else {
                    // Regular user - redirect to profile page
                    header("Location: ../view/user-profile.php");
                    exit();
                }
            } else {
                // Password is incorrect
                $_SESSION['signin_error'] = "Invalid email or password";
                header("Location: ../view/signin.php");
                exit();
            }
        } else {
            // User not found
            $_SESSION['signin_error'] = "Invalid email or password";
            header("Location: ../view/signin.php");
            exit();
        }
        
    } catch (PDOException $e) {
        // Log the error (for administrators)
        error_log("Authentication error: " . $e->getMessage());
        
        // Generic error message (for users)
        $_SESSION['signin_error'] = "An error occurred during authentication";
        header("Location: ../view/signin.php");
        exit();
    }
} else {
    // If not a POST request, redirect to signin page
    header("Location: ../view/signin.php");
    exit();
}
?>