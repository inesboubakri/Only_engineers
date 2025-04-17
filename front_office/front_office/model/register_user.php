<?php
// Display all errors for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();

// Store form data in session without database check
$errors = [];
$formData = [];

// Validate full name
if (empty($_POST['fullName'])) {
    $errors['fullName'] = "Full name is required";
} elseif (strlen($_POST['fullName']) < 3) {
    $errors['fullName'] = "Full name must be at least 3 characters";
} else {
    $formData['fullName'] = $_POST['fullName'];
}

// Validate email
if (empty($_POST['email'])) {
    $errors['email'] = "Email address is required";
} elseif (!filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {
    $errors['email'] = "Please enter a valid email address";
} else {
    $formData['email'] = $_POST['email'];
}

// Validate password
if (empty($_POST['password'])) {
    $errors['password'] = "Password is required";
} elseif (strlen($_POST['password']) < 8) {
    $errors['password'] = "Password must be at least 8 characters long";
} elseif (!preg_match('/[A-Z]/', $_POST['password']) || 
          !preg_match('/[a-z]/', $_POST['password']) || 
          !preg_match('/[0-9]/', $_POST['password'])) {
    $errors['password'] = "Password must contain at least one uppercase letter, one lowercase letter, and one number";
} else {
    $formData['password'] = $_POST['password']; // Will be hashed later
}

// Validate terms agreement
if (!isset($_POST['termsAgree'])) {
    $errors['termsAgree'] = "You must agree to the terms of service";
}

// If there are errors, redirect back to signup page
if (!empty($errors)) {
    $_SESSION['errors'] = $errors;
    $_SESSION['form_data'] = $formData;
    header("Location: ../view/signup.php");
    exit();
}

// If validation passes, store user data in session for profile setup
$_SESSION['signup_data'] = [
    'full_name' => $formData['fullName'],
    'email' => $formData['email'],
    'password' => password_hash($formData['password'], PASSWORD_DEFAULT) // Hash the password
];

// Redirect to profile setup page
header("Location: ../view/profile-setup.php");
exit();
?>
