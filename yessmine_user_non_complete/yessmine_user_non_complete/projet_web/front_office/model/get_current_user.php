<?php
// Enable error logging
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/debug.log');
error_reporting(E_ALL);
ini_set('display_errors', 0);

session_start();

// Add CORS headers
header('Access-Control-Allow-Origin: http://localhost');
header('Access-Control-Allow-Credentials: true');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');
header('Content-Type: application/json');
header('Cache-Control: no-cache, must-revalidate');

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

require_once 'db_connection.php';

try {
    if (!isset($_SESSION['user_id'])) {
        error_log('Session user_id not set');
        http_response_code(401);
        echo json_encode([
            'success' => false,
            'message' => 'Not logged in'
        ]);
        exit;
    }

    error_log('Session user_id: ' . $_SESSION['user_id']);
    $conn = getConnection();
    $userId = $_SESSION['user_id'];
    
    $query = "SELECT user_id, full_name, email, is_admin FROM users WHERE user_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->execute([$userId]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user) {
        error_log('User found: ' . json_encode($user));
        echo json_encode([
            'success' => true,
            'user_id' => $user['user_id'],
            'full_name' => $user['full_name'],
            'email' => $user['email'],
            'is_admin' => (bool)$user['is_admin']
        ]);
    } else {
        error_log('User not found for ID: ' . $userId);
        http_response_code(404);
        echo json_encode([
            'success' => false,
            'message' => 'User not found'
        ]);
    }

} catch (Exception $e) {
    error_log("Error in get_current_user.php: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Server error'
    ]);
}
?>