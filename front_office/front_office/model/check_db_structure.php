<?php
// Script to check and fix the database structure for the participants table
// Specifically, it checks for the existence of the 'role' column and adds it if missing

// Include database connection
require_once 'db_connection.php';

// Function to check if column exists in table
function columnExists($conn, $table, $column) {
    try {
        $stmt = $conn->query("DESCRIBE $table");
        $columns = $stmt->fetchAll(PDO::FETCH_COLUMN);
        return in_array($column, $columns);
    } catch (PDOException $e) {
        echo "<p>Error checking table structure: " . $e->getMessage() . "</p>";
        return false;
    }
}

// Get database connection
try {
    $conn = getConnection();
    echo "<h2>Database Structure Checker</h2>";
    echo "<p>Connected to database successfully.</p>";

    // Check if the role column exists in the participants table
    $hasRoleColumn = columnExists($conn, 'participants', 'role');

    if ($hasRoleColumn) {
        echo "<p style='color: green;'>✅ The 'role' column already exists in the 'participants' table.</p>";
    } else {
        echo "<p style='color: orange;'>⚠️ The 'role' column is missing from the 'participants' table.</p>";
        
        // Add the role column
        try {
            $conn->exec("ALTER TABLE participants ADD COLUMN role VARCHAR(100) DEFAULT NULL AFTER is_team_lead");
            echo "<p style='color: green;'>✅ Successfully added 'role' column to the 'participants' table.</p>";
        } catch (PDOException $e) {
            echo "<p style='color: red;'>❌ Failed to add 'role' column: " . $e->getMessage() . "</p>";
        }
    }

    // Show the current structure of the participants table
    echo "<h3>Current Structure of Participants Table:</h3>";
    
    try {
        $stmt = $conn->query("DESCRIBE participants");
        $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo "<table border='1' cellpadding='5' style='border-collapse: collapse;'>";
        echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th></tr>";
        
        foreach ($columns as $column) {
            echo "<tr>";
            foreach ($column as $key => $value) {
                echo "<td>" . ($value === NULL ? 'NULL' : htmlspecialchars($value)) . "</td>";
            }
            echo "</tr>";
        }
        
        echo "</table>";
    } catch (PDOException $e) {
        echo "<p style='color: red;'>Error retrieving table structure: " . $e->getMessage() . "</p>";
    }

    // Show sample data from the participants table
    echo "<h3>Sample Team Members in Database:</h3>";
    try {
        $stmt = $conn->query("SELECT * FROM participants WHERE participation_type = 'team' LIMIT 10");
        $participants = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        if (count($participants) > 0) {
            echo "<table border='1' cellpadding='5' style='border-collapse: collapse;'>";
            echo "<tr>";
            foreach (array_keys($participants[0]) as $key) {
                echo "<th>" . htmlspecialchars($key) . "</th>";
            }
            echo "</tr>";
            
            foreach ($participants as $participant) {
                echo "<tr>";
                foreach ($participant as $value) {
                    echo "<td>" . ($value === NULL ? 'NULL' : htmlspecialchars($value)) . "</td>";
                }
                echo "</tr>";
            }
            
            echo "</table>";
        } else {
            echo "<p>No team members found in database.</p>";
        }
    } catch (PDOException $e) {
        echo "<p style='color: red;'>Error retrieving sample data: " . $e->getMessage() . "</p>";
    }
    
} catch (PDOException $e) {
    echo "<p style='color: red;'>Database Connection Error: " . $e->getMessage() . "</p>";
}
?>