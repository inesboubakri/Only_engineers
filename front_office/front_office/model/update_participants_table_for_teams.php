<?php
// This script ensures that team registrations correctly process role data
// It also acts as a migration utility if needed

require_once 'db_connection.php';

try {
    $conn = getConnection();
    $logFile = __DIR__ . '/participant_registration.log';
    
    function writeToLog($message) {
        global $logFile;
        $timestamp = date('Y-m-d H:i:s');
        file_put_contents($logFile, "[$timestamp] $message" . PHP_EOL, FILE_APPEND);
    }
    
    writeToLog("Starting team registration database structure check...");
    
    // Make sure the role column exists in the participants table
    $checkRoleColumn = $conn->query("SHOW COLUMNS FROM participants LIKE 'role'");
    if ($checkRoleColumn->rowCount() === 0) {
        writeToLog("Role column does not exist, adding it now...");
        $conn->exec("ALTER TABLE participants ADD COLUMN role VARCHAR(50) DEFAULT NULL AFTER is_team_lead");
        echo "Added 'role' column to the participants table.<br>";
    } else {
        writeToLog("Role column already exists in the participants table.");
        echo "The 'role' column already exists in the participants table.<br>";
    }
    
    // Make sure the team_name column exists
    $checkTeamNameColumn = $conn->query("SHOW COLUMNS FROM participants LIKE 'team_name'");
    if ($checkTeamNameColumn->rowCount() === 0) {
        writeToLog("Team name column does not exist, adding it now...");
        $conn->exec("ALTER TABLE participants ADD COLUMN team_name VARCHAR(50) DEFAULT NULL AFTER user_id");
        echo "Added 'team_name' column to the participants table.<br>";
    } else {
        writeToLog("Team name column already exists in the participants table.");
        echo "The 'team_name' column already exists in the participants table.<br>";
    }
    
    // Make sure the is_team_lead column exists
    $checkTeamLeadColumn = $conn->query("SHOW COLUMNS FROM participants LIKE 'is_team_lead'");
    if ($checkTeamLeadColumn->rowCount() === 0) {
        writeToLog("is_team_lead column does not exist, adding it now...");
        $conn->exec("ALTER TABLE participants ADD COLUMN is_team_lead TINYINT(1) DEFAULT 0 AFTER team_name");
        echo "Added 'is_team_lead' column to the participants table.<br>";
    } else {
        writeToLog("is_team_lead column already exists in the participants table.");
        echo "The 'is_team_lead' column already exists in the participants table.<br>";
    }
    
    // Show the current structure of the participants table
    echo "<h3>Current structure of the participants table:</h3>";
    $result = $conn->query("DESCRIBE participants");
    echo "<pre>";
    while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
        echo "{$row['Field']} - {$row['Type']} - {$row['Null']} - {$row['Default']}\n";
    }
    echo "</pre>";
    
    echo "<p>Script completed successfully. <a href='../view/hackathons.php'>Return to hackathons</a></p>";
    
} catch (PDOException $e) {
    writeToLog("Database ERROR: " . $e->getMessage());
    echo "ERROR: " . $e->getMessage();
}
?>