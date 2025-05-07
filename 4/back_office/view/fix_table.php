<?php
// Include database connection
require_once('../config/db_connection.php');

// SQL to fix the AUTO_INCREMENT column
$sql = "ALTER TABLE `projet` MODIFY `AUTO_INCREMENT` int(11) NOT NULL AUTO_INCREMENT";

if ($conn->query($sql) === TRUE) {
    echo "Table structure has been fixed successfully.<br>";
    
    // Reset AUTO_INCREMENT counter to 1
    $reset_sql = "ALTER TABLE `projet` AUTO_INCREMENT = 1";
    if ($conn->query($reset_sql) === TRUE) {
        echo "AUTO_INCREMENT counter has been reset to 1.<br>";
    } else {
        echo "Error resetting AUTO_INCREMENT counter: " . $conn->error . "<br>";
    }
} else {
    echo "Error fixing table structure: " . $conn->error . "<br>";
}

echo '<br><a href="Projects.php">Return to Projects Page</a>';

// Close connection
$conn->close();
?>
