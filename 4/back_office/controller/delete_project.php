<?php
// Include database connection
require_once('../controller/db_connection.php');

// Check if ID is provided
if (isset($_GET['id']) && !empty($_GET['id'])) {
    // Sanitize and convert ID to integer
    $id = (int)$_GET['id'];
    
    // SQL query to delete record
    $sql = "DELETE FROM projet WHERE id = :id";
    
    // Prepare statement
    $stmt = $conn->prepare($sql);
    
    // Bind the ID parameter
    $stmt->bindParam(':id', $id, PDO::PARAM_INT);
    
    // Execute the query and check if it was successful
    if ($stmt->execute()) {
        header("Location: ../view/Projects.php?success=deleted");
    } else {
        header("Location: ../view/Projects.php?error=failed_to_delete");
    }
} else {
    header("Location: ../view/Projects.php?error=missing_id");
}

// Close connection (PDO doesn't require explicit closing, but just for clarity)
$conn = null;
exit();
?>
