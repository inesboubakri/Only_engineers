<?php
// Start session
session_start();

// Check if user is logged in and is an admin
if (!isset($_SESSION['user_id']) || $_SESSION['is_admin'] != 1) {
    // Return JSON error
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit();
}

// Check if the request method is POST and user_id is provided
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['user_id'])) {
    // Get the user ID
    $userId = intval($_POST['user_id']);
    
    // Database connection parameters
    $servername = "localhost";
    $username = "root";
    $password = "";
    $dbname = "onlyengs";
    
    try {
        // Create connection using PDO
        $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
        
        // Set the PDO error mode to exception
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        // Get user information before deleting (to handle profile picture deletion)
        $profileStmt = $conn->prepare("SELECT profile_picture FROM users WHERE user_id = :user_id");
        $profileStmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
        $profileStmt->execute();
        $user = $profileStmt->fetch(PDO::FETCH_ASSOC);
        
        // Delete the user from the database
        $stmt = $conn->prepare("DELETE FROM users WHERE user_id = :user_id");
        $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
        $stmt->execute();
        
        // Check if any rows were affected
        if ($stmt->rowCount() > 0) {
            // Delete profile picture if it exists
            if (!empty($user['profile_picture'])) {
                $profilePicPath = '../../front_office/front_office/ressources/profile_pictures/' . $user['profile_picture'];
                if (file_exists($profilePicPath)) {
                    unlink($profilePicPath);
                }
            }
            
            // Return success message
            header('Content-Type: application/json');
            echo json_encode(['success' => true, 'message' => 'User deleted successfully']);
        } else {
            // Return error if no user was deleted
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'User not found']);
        }
    } catch(PDOException $e) {
        // Return database error
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
    }
} else {
    // Return error if user_id is not provided
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'User ID is required']);
}
?>