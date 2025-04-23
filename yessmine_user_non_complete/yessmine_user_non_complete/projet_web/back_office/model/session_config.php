<?php
// Initialize session with secure settings
ini_set('session.cookie_httponly', 1);
ini_set('session.use_only_cookies', 1);
ini_set('session.cookie_secure', isset($_SERVER['HTTPS']));
error_reporting(E_ALL);
ini_set('display_errors', 0);

session_start();

// Set the session name to distinguish between front and back office
session_name('admin_session');

// Function to get database connection
function getConnection() {
    try {
        require_once '../../front_office/model/db_connection.php';
        if (!isset($conn) || !($conn instanceof PDO)) {
            throw new Exception("Database connection failed");
        }
        return $conn;
    } catch (Exception $e) {
        error_log("Database connection error in session_config: " . $e->getMessage());
        if (!headers_sent()) {
            header('Content-Type: application/json');
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'message' => 'Database connection failed'
            ]);
        }
        exit();
    }
}

// Function to check if user is authenticated and is admin
function checkAdminAuth() {
    if (!isset($_SESSION['user_id'])) {
        return false;
    }

    try {
        $conn = getConnection();
        $stmt = $conn->prepare("SELECT is_admin FROM users WHERE user_id = ?");
        $stmt->execute([$_SESSION['user_id']]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        return $user && $user['is_admin'] == 1;
    } catch(Exception $e) {
        error_log("Error checking admin status: " . $e->getMessage());
        return false;
    }
}

// Function to get current admin user data
function getCurrentAdmin() {
    if (!isset($_SESSION['user_id'])) {
        return null;
    }

    try {
        $conn = getConnection();
        $stmt = $conn->prepare("SELECT user_id, full_name, is_admin FROM users WHERE user_id = ? AND is_admin = 1");
        $stmt->execute([$_SESSION['user_id']]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    } catch(Exception $e) {
        error_log("Error getting admin data: " . $e->getMessage());
        return null;
    }
}
?>