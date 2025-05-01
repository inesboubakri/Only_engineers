<?php
// Script to add photo column to participants table
require_once 'db_connection.php';

try {
    // Connect to database
    $conn = getConnection();
    
    // Check if photo column already exists
    $result = $conn->query("SHOW COLUMNS FROM participants LIKE 'photo'");
    $exists = $result->rowCount() > 0;
    
    if (!$exists) {
        // Add photo column
        $sql = "ALTER TABLE participants ADD COLUMN photo VARCHAR(255) AFTER phone";
        $conn->exec($sql);
        echo "Success: Photo column added to participants table.";
    } else {
        echo "Photo column already exists in participants table.";
    }
} catch(PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>