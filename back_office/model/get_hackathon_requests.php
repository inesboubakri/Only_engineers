<?php
/**
 * Get Hackathon Requests
 * Retrieves all pending hackathon requests for admin review
 */

// Start session
session_start();

// Make sure we send JSON content type
header('Content-Type: application/json');

// Check if user is logged in and is an admin
if (!isset($_SESSION['user_id']) || $_SESSION['is_admin'] != 1) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit();
}

// Include database connection
require_once 'db_connectionback.php';

try {
    // Get database connection
    $conn = getConnection();
    
    if (!$conn) {
        throw new Exception("Failed to connect to database");
    }
    
    // Query to check if hackathon_requests table exists
    $tableCheckSql = "SHOW TABLES LIKE 'hackathon_requests'";
    $tableCheckStmt = $conn->prepare($tableCheckSql);
    $tableCheckStmt->execute();
    
    // If table doesn't exist, return empty array
    if ($tableCheckStmt->rowCount() == 0) {
        echo json_encode(['success' => true, 'requests' => [], 'count' => 0]);
        exit();
    }
    
    // Query to get all hackathon requests - changed username to full_name to match the database structure
    $sql = "SELECT hr.*, u.full_name FROM hackathon_requests hr 
            LEFT JOIN users u ON hr.user_id = u.user_id 
            ORDER BY hr.submitted_at DESC";
    
    $stmt = $conn->prepare($sql);
    $stmt->execute();
    
    $requests = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $count = count($requests);
    
    echo json_encode([
        'success' => true, 
        'requests' => $requests,
        'count' => $count
    ]);
    
} catch(Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>