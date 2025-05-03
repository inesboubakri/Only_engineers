<?php
// Start session
session_start();

// Debug session information
error_log("Session check in toggle_ban_user.php: " . print_r($_SESSION, true));

// Check if user is logged in and is an admin - with more detailed error for debugging
if (!isset($_SESSION['user_id'])) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Unauthorized access: No user session']);
    exit();
}

if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] != 1) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Unauthorized access: Not an admin user']);
    exit();
}

// Include database connection
require_once 'db_connectionback.php';

// Check if the request method is POST and user_id is provided
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['user_id'])) {
    // Get the user ID
    $userId = intval($_POST['user_id']);
    
    try {
        // Get database connection
        $conn = getConnection();
        
        if (!$conn) {
            throw new Exception("Failed to connect to database");
        }
        
        // Get current ban status
        $statusStmt = $conn->prepare("SELECT is_banned FROM users WHERE user_id = :user_id");
        $statusStmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
        $statusStmt->execute();
        $user = $statusStmt->fetch(PDO::FETCH_ASSOC);
        
        if ($statusStmt->rowCount() > 0) {
            // Toggle the ban status (if currently 1, make it 0; if currently 0, make it 1)
            $newStatus = $user['is_banned'] ? 0 : 1;
            $actionText = $newStatus ? 'banned' : 'unbanned';
            
            // Update the user's ban status
            $updateStmt = $conn->prepare("UPDATE users SET is_banned = :is_banned WHERE user_id = :user_id");
            $updateStmt->bindParam(':is_banned', $newStatus, PDO::PARAM_INT);
            $updateStmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
            $updateStmt->execute();
            
            // Return success message
            header('Content-Type: application/json');
            echo json_encode([
                'success' => true, 
                'message' => "User {$actionText} successfully",
                'is_banned' => $newStatus
            ]);
        } else {
            // Return error if no user was found
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'User not found']);
        }
    } catch(Exception $e) {
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