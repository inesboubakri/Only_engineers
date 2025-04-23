<?php
/**
 * User Registration Handler
 * 
 * This script handles the registration of new users in the OnlyEngineers platform.
 */

// Start session
session_start();

// Include database connection
require_once 'db_connection.php';

// Check if form was submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    // Get form data
    $full_name = trim($_POST['fullName']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    
    // Validate inputs
    $errors = [];
    
    // Validate full name
    if (empty($full_name)) {
        $errors[] = "Full name is required";
    } elseif (strlen($full_name) < 3 || strlen($full_name) > 100) {
        $errors[] = "Full name must be between 3 and 100 characters";
    }
    
    // Validate email
    if (empty($email)) {
        $errors[] = "Email is required";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Please enter a valid email address";
    } else {
        // Check if email already exists
        $stmt = $conn->prepare("SELECT user_id FROM users WHERE email = :email");
        $stmt->bindParam(':email', $email);
        $stmt->execute();
        
        if ($stmt->rowCount() > 0) {
            $errors[] = "Email address is already registered";
        }
    }
    
    // Validate password
    if (empty($password)) {
        $errors[] = "Password is required";
    } elseif (strlen($password) < 8) {
        $errors[] = "Password must be at least 8 characters long";
    } elseif (!preg_match('/[A-Z]/', $password)) {
        $errors[] = "Password must contain at least one uppercase letter";
    } elseif (!preg_match('/[a-z]/', $password)) {
        $errors[] = "Password must contain at least one lowercase letter";
    } elseif (!preg_match('/[0-9]/', $password)) {
        $errors[] = "Password must contain at least one number";
    }
    
    // If there are no errors, proceed with registration
    if (empty($errors)) {
        try {
            // Hash the password
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            
            // Insert user into database with empty JSON arrays for the new fields and set is_admin to 0
            $empty_json_array = json_encode([]);
            $is_admin = 0; // Default value for regular users
            
            $stmt = $conn->prepare("INSERT INTO users (full_name, email, password, skills, languages, projects, honors, is_admin) 
                                  VALUES (:full_name, :email, :password, :skills, :languages, :projects, :honors, :is_admin)");
            
            $stmt->bindParam(':full_name', $full_name);
            $stmt->bindParam(':email', $email);
            $stmt->bindParam(':password', $hashed_password);
            $stmt->bindParam(':skills', $empty_json_array);
            $stmt->bindParam(':languages', $empty_json_array);
            $stmt->bindParam(':projects', $empty_json_array);
            $stmt->bindParam(':honors', $empty_json_array);
            $stmt->bindParam(':is_admin', $is_admin, PDO::PARAM_INT);
            
            if ($stmt->execute()) {
                // Get the user ID of the newly registered user
                $user_id = $conn->lastInsertId();
                
                // Store user ID in session
                $_SESSION['user_id'] = $user_id;
                $_SESSION['full_name'] = $full_name;
                $_SESSION['email'] = $email;
                
                // Redirect to profile setup page
                header("Location: ../view/profile-setup.html");
                exit();
            }
        } catch (PDOException $e) {
            $errors[] = "Registration failed: " . $e->getMessage();
        }
    }
    
    // If there are errors, store them in session and redirect back to signup page
    if (!empty($errors)) {
        $_SESSION['signup_errors'] = $errors;
        $_SESSION['form_data'] = [
            'fullName' => $full_name,
            'email' => $email
        ];
        header("Location: ../view/signup.html");
        exit();
    }
}

// If not a POST request, redirect to signup page
header("Location: ../view/signup.html");
exit();
?>
