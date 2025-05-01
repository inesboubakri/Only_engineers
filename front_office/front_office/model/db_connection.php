<?php
// Database connection parameters
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "onlyengs";

// Create connection using PDO
function getConnection() {
    global $servername, $username, $password, $dbname;
    
    try {
        $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
        // Set the PDO error mode to exception
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        // Test the connection with a simple query
        $conn->query("SELECT 1");
        
        return $conn;
    } catch(PDOException $e) {
        // Log the error (if logging is enabled)
        $logFile = __DIR__ . '/database_errors.log';
        $message = date('Y-m-d H:i:s') . " - Database connection error: " . $e->getMessage() . "\n";
        $message .= "Database settings: host=$servername, db=$dbname, user=$username\n";
        $message .= "PHP version: " . phpversion() . "\n";
        $message .= "PDO drivers available: " . implode(', ', PDO::getAvailableDrivers()) . "\n";
        file_put_contents($logFile, $message, FILE_APPEND);
        
        // Re-throw the exception so the calling code can see the real error
        throw $e;
    }
}

// Function to check if database connection is successful
function testDatabaseConnection() {
    try {
        $conn = getConnection();
        return true;
    } catch(PDOException $e) {
        return false;
    }
}
?>
