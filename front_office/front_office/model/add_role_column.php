<?php
// Script to check if the role column exists in the participants table and add it if missing
require_once 'db_connection.php';

try {
    $conn = getConnection();
    
    // Check if the role column exists
    $columnExists = false;
    $checkCol = $conn->query("SHOW COLUMNS FROM participants LIKE 'role'");
    if ($checkCol->rowCount() > 0) {
        echo "The 'role' column already exists in the participants table.<br>";
        $columnExists = true;
    } else {
        echo "The 'role' column does not exist in the participants table.<br>";
    }
    
    // Add the column if it doesn't exist
    if (!$columnExists) {
        $conn->exec("ALTER TABLE participants ADD COLUMN role VARCHAR(50) DEFAULT NULL AFTER is_team_lead");
        echo "Added 'role' column to the participants table.<br>";
    }
    
    // Show the current table structure
    echo "<h3>Current structure of the participants table:</h3>";
    $result = $conn->query("DESCRIBE participants");
    echo "<table border='1'><tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th></tr>";
    while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
        echo "<tr>";
        echo "<td>{$row['Field']}</td>";
        echo "<td>{$row['Type']}</td>";
        echo "<td>{$row['Null']}</td>";
        echo "<td>{$row['Key']}</td>";
        echo "<td>{$row['Default']}</td>";
        echo "<td>{$row['Extra']}</td>";
        echo "</tr>";
    }
    echo "</table>";
    
    echo "<br>Script completed successfully.";
    
} catch (PDOException $e) {
    echo "Database error: " . $e->getMessage();
}
?>