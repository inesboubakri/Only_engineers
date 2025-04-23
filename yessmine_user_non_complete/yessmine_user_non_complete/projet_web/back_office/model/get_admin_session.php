<?php
error_reporting(E_ALL);
ini_set('display_errors', 0);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once '../../front_office/model/db_connection.php';
require_once 'session_config.php';

// Set headers
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: http://localhost');
header('Access-Control-Allow-Credentials: true');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

try {
    // Start by checking admin authentication
    if (!checkAdminAuth()) {
        throw new Exception('Unauthorized: Admin access required');
    }

    // Get admin data if authenticated
    $admin = getCurrentAdmin();
    if (!$admin) {
        throw new Exception('Admin data not found');
    }

    // Log successful session data
    error_log('Admin session validated successfully for user ID: ' . $admin['user_id']);

    echo json_encode([
        'success' => true,
        'admin' => [
            'admin_id' => $admin['user_id'],
            'name' => $admin['full_name']
        ]
    ]);

} catch (Exception $e) {
    error_log('Admin session validation error: ' . $e->getMessage());
    http_response_code(401);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>