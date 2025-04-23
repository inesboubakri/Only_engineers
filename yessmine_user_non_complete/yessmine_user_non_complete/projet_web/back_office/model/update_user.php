<?php
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 0);

// Ensure proper JSON response
header('Content-Type: application/json');

require_once '../../front_office/model/db_connection.php';

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Invalid request method');
    }

    // Validation des données
    $user_id = isset($_POST['user_id']) ? intval($_POST['user_id']) : 0;
    $full_name = isset($_POST['full_name']) ? trim($_POST['full_name']) : '';
    $email = isset($_POST['email']) ? trim($_POST['email']) : '';
    $position = isset($_POST['position']) ? trim($_POST['position']) : '';
    $is_admin = isset($_POST['is_admin']) ? intval($_POST['is_admin']) : 0;

    // Validation du format des données
    if ($user_id <= 0) {
        throw new Exception('Invalid user ID');
    }

    if (empty($full_name) || strlen($full_name) < 3 || !preg_match('/^[a-zA-Z\s]+$/', $full_name)) {
        throw new Exception('Invalid full name');
    }

    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        throw new Exception('Invalid email format');
    }

    if (!empty($position) && strlen($position) < 2) {
        throw new Exception('Position must be at least 2 characters long');
    }

    if ($is_admin !== 0 && $is_admin !== 1) {
        throw new Exception('Invalid admin status');
    }

    // Initialize variables
    $profile_picture_path = null;
    $stmt = null;

    // Gestion de l'upload de l'image
    if (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] === UPLOAD_ERR_OK) {
        $file = $_FILES['profile_picture'];
        
        // Validate file type
        $allowed_types = ['image/jpeg', 'image/png'];
        if (!in_array($file['type'], $allowed_types)) {
            throw new Exception('Invalid file type. Only JPG and PNG are allowed.');
        }

        // Validate file size (2MB max)
        if ($file['size'] > 2 * 1024 * 1024) {
            throw new Exception('File size must be less than 2MB');
        }

        // Create uploads directory if it doesn't exist
        $upload_dir = '../../front_office/assets/uploads/profile_pictures/';
        if (!file_exists($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }

        // Create unique filename
        $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $new_filename = 'profile_' . $user_id . '_' . time() . '.' . $extension;
        $upload_path = $upload_dir . $new_filename;

        // Move uploaded file
        if (!move_uploaded_file($file['tmp_name'], $upload_path)) {
            throw new Exception('Failed to upload file');
        }

        $profile_picture_path = $new_filename;

        // Delete old profile picture if it exists
        $check_stmt = $conn->prepare("SELECT profile_picture FROM users WHERE user_id = ?");
        if (!$check_stmt) {
            throw new Exception("Failed to prepare statement: " . $conn->error);
        }
        $check_stmt->bind_param("i", $user_id);
        $check_stmt->execute();
        $result = $check_stmt->get_result();
        if ($row = $result->fetch_assoc()) {
            $old_picture = $row['profile_picture'];
            if ($old_picture && file_exists($upload_dir . $old_picture)) {
                unlink($upload_dir . $old_picture);
            }
        }
        $check_stmt->close();
    }

    // Update database
    if ($profile_picture_path) {
        $query = "UPDATE users SET full_name = ?, email = ?, position = ?, is_admin = ?, profile_picture = ? WHERE user_id = ?";
        $stmt = $conn->prepare($query);
        if (!$stmt) {
            throw new Exception("Failed to prepare update statement: " . $conn->error);
        }
        $stmt->bind_param("sssisi", $full_name, $email, $position, $is_admin, $profile_picture_path, $user_id);
    } else {
        $query = "UPDATE users SET full_name = ?, email = ?, position = ?, is_admin = ? WHERE user_id = ?";
        $stmt = $conn->prepare($query);
        if (!$stmt) {
            throw new Exception("Failed to prepare update statement: " . $conn->error);
        }
        $stmt->bind_param("sssii", $full_name, $email, $position, $is_admin, $user_id);
    }
    
    if (!$stmt->execute()) {
        throw new Exception("Failed to update user: " . $stmt->error);
    }

    echo json_encode([
        'success' => true,
        'user' => [
            'id' => $user_id,
            'name' => $full_name,
            'email' => $email,
            'position' => $position,
            'is_admin' => $is_admin,
            'profile_picture' => $profile_picture_path
        ]
    ]);

    $stmt->close();

} catch (Throwable $e) {
    error_log("Update user error: " . $e->getMessage());
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}

$conn->close();
?>