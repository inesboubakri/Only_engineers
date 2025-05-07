<?php
// Include database connection
require_once('../controller/db_connection.php');

// Set the response header to JSON
header('Content-Type: application/json');

try {
    // Query to get all project requests
    $stmt = $conn->prepare("SELECT * FROM project_requests ORDER BY request_date DESC");
    $stmt->execute();
    
    // Fetch all project requests
    $requests = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Return the project requests as JSON
    echo json_encode($requests);
} catch (PDOException $e) {
    // Return an error message
    echo json_encode(['error' => $e->getMessage()]);
}
?>