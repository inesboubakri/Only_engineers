<?php
// Include database connection
require_once('../controller/db_connection.php');

// Get filter if any is provided
$filter = isset($_GET['filter']) ? $_GET['filter'] : null;

// Prepare query
$sql = "SELECT * FROM projet";

// Apply filter if specified
if (!empty($filter) && $filter !== 'All') {
    $sql .= " WHERE type LIKE :filter";
}

// Prepare the statement
$stmt = $conn->prepare($sql);

// Bind the filter parameter if it exists
if (!empty($filter) && $filter !== 'All') {
    $filterValue = "%" . $filter . "%";
    $stmt->bindParam(':filter', $filterValue, PDO::PARAM_STR);
}

// Execute query
$stmt->execute();

// Fetch all results
$projects = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Set content type to JSON
header('Content-Type: application/json');

// Return JSON response
echo json_encode($projects);

// Close connection (PDO doesn't require explicit closing, but just for clarity)
$conn = null;
?>
