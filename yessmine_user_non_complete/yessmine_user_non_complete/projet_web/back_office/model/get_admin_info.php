<?php
error_reporting(0); // Disable error reporting
session_start();
header('Content-Type: application/json');
header('Cache-Control: no-cache, must-revalidate');
require_once '../../front_office/model/db_connection.php';

try {
    if (!isset($_SESSION['user_id'])) {
        throw new Exception("Not logged in");
    }

    $user_id = $_SESSION['user_id'];
    $conn = getConnection();
    
    $query = "SELECT user_id, full_name, profile_picture, is_admin FROM users WHERE user_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->execute([$user_id]);
    $admin = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$admin || !$admin['is_admin']) {
        throw new Exception("Admin not found");
    }

    echo json_encode([
        'success' => true,
        'admin' => [
            'admin_id' => $admin['user_id'],
            'name' => $admin['full_name'],
            'profile_picture' => $admin['profile_picture']
        ]
    ]);

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>