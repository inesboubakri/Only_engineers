<?php
// Set session parameters BEFORE starting the session
ini_set('session.gc_maxlifetime', 86400); // 24 hours
session_set_cookie_params(86400);

// Start session
session_start();

// Check if user is logged in and is an admin
if (!isset($_SESSION['user_id']) || $_SESSION['is_admin'] != 1) {
    // Redirect to login page if not logged in or not an admin
    header("Location: ../../front_office/front_office/view/signin.php");
    exit();
}

// Include database connection
require_once '../model/db_connectionback.php';

// Connect to the database
try {
    // Get database connection
    $conn = getConnection();
    
    if (!$conn) {
        throw new Exception("Failed to connect to database");
    }
    
    // Fetch all users
    $stmt = $conn->prepare("SELECT user_id, full_name, email, profile_picture, position, 
                              CASE WHEN is_admin = 1 THEN 'Yes' ELSE 'No' END as is_admin, 
                              CASE WHEN is_banned = 1 THEN 'Yes' ELSE 'No' END as is_banned 
                              FROM users");
    $stmt->execute();
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Set up the Excel file
    header('Content-Type: application/vnd.ms-excel');
    header('Content-Disposition: attachment;filename="onlyengineers_users_report.xls"');
    header('Cache-Control: max-age=0');
    
    // Create Excel output
    echo "<table border='1'>";
    
    // Table header
    echo "<tr>";
    echo "<th colspan='7'><h2>OnlyEngineers - User Report</h2></th>";
    echo "</tr>";
    
    echo "<tr>";
    echo "<th>ID</th>";
    echo "<th>Full Name</th>";
    echo "<th>Email</th>";
    echo "<th>Position</th>";
    echo "<th>Has Profile Picture</th>";
    echo "<th>Admin</th>";
    echo "<th>Banned</th>";
    echo "</tr>";
    
    // Table data
    foreach ($users as $user) {
        echo "<tr>";
        echo "<td>" . htmlspecialchars($user['user_id']) . "</td>";
        echo "<td>" . htmlspecialchars($user['full_name']) . "</td>";
        echo "<td>" . htmlspecialchars($user['email']) . "</td>";
        echo "<td>" . htmlspecialchars($user['position']) . "</td>";
        echo "<td>" . (!empty($user['profile_picture']) ? 'Yes' : 'No') . "</td>";
        echo "<td>" . $user['is_admin'] . "</td>";
        echo "<td>" . $user['is_banned'] . "</td>";
        echo "</tr>";
    }
    
    echo "</table>";
    
} catch(Exception $e) {
    // In case of an error, return a JSON error response
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Error generating Excel report: ' . $e->getMessage()]);
    exit();
}
?>