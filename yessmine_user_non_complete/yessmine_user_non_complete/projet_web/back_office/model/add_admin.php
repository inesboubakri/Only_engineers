<?php
header('Content-Type: application/json');
require_once '../../front_office/model/db_connection.php';

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Invalid request method');
    }

    $full_name = isset($_POST['full_name']) ? $_POST['full_name'] : '';
    $email = isset($_POST['email']) ? $_POST['email'] : '';
    $password = isset($_POST['password']) ? $_POST['password'] : '';
    $position = isset($_POST['position']) ? $_POST['position'] : '';

    // Validate inputs
    if (empty($full_name) || empty($email) || empty($password) || empty($position)) {
        throw new Exception('All fields are required');
    }

    // Handle profile picture upload
    $profile_picture = '';
    if (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] === 0) {
        $allowed = ['jpg', 'jpeg', 'png', 'gif'];
        $filename = $_FILES['profile_picture']['name'];
        $file_ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));

        if (!in_array($file_ext, $allowed)) {
            throw new Exception('Invalid file type. Allowed: jpg, jpeg, png, gif');
        }

        // Generate unique filename
        $new_filename = 'profile_' . time() . '_' . uniqid() . '.' . $file_ext;
        $upload_path = '../../front_office/assets/uploads/profile_pictures/' . $new_filename;

        if (!move_uploaded_file($_FILES['profile_picture']['tmp_name'], $upload_path)) {
            throw new Exception('Failed to upload profile picture');
        }

        $profile_picture = $new_filename;
    }

    // Hash the password
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    // Set admin values
    $is_admin = 1;
    $profile_completed = 1;
    $created_at = date('Y-m-d H:i:s');

    // Insert admin into database
    $query = "INSERT INTO users (full_name, email, password, position, profile_picture, is_admin, profile_completed, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("sssssiis", $full_name, $email, $hashed_password, $position, $profile_picture, $is_admin, $profile_completed, $created_at);
    
    if ($stmt->execute()) {
        echo json_encode([
            'success' => true,
            'message' => 'Admin created successfully'
        ]);
    } else {
        throw new Exception('Failed to create admin');
    }

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}

$conn->close();
?>