<?php
/**
 * Database connection for back office
 * This file handles the PDO database connection for the back office
 */

// Database connection parameters
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "onlyengs";

/**
 * Get database connection
 * @return PDO Database connection object
 */
function getConnection() {
    global $servername, $username, $password, $dbname;
    
    try {
        // Create connection using PDO
        $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
        
        // Set the PDO error mode to exception
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        return $conn;
    } catch(PDOException $e) {
        // Log the error
        error_log("Database connection failed: " . $e->getMessage());
        
        // Return null on failure
        return null;
    }
}
?>