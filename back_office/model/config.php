<?php
// Database configuration
$servername = "localhost";
$username = "root"; 
$password = "";
$dbname = "aziz";

// Error handling
$conn = null;
$db_error = "";

// First try to connect to the MySQL server without specifying a database
try {
    // Basic connection to check if MySQL is running
    try {
        $basicDsn = "mysql:host={$servername}";
        $basicConn = new PDO($basicDsn, $username, $password, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
        
        // Successfully connected to MySQL, now check/create database
        try {
            // Check if database exists
            $dbCheckStmt = $basicConn->query("SELECT COUNT(*) FROM INFORMATION_SCHEMA.SCHEMATA WHERE SCHEMA_NAME = '{$dbname}'");
            $dbExists = (int)$dbCheckStmt->fetchColumn() > 0;
            
            // If database doesn't exist, create it
            if (!$dbExists) {
                $createDbSql = "CREATE DATABASE `{$dbname}` CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci";
                $basicConn->exec($createDbSql);
            }
            
            // Now connect to the specific database
            $dsn = "mysql:host={$servername};dbname={$dbname};charset=utf8mb4";
            $conn = new PDO(
                $dsn,
                $username,
                $password,
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false,
                ]
            );
            
            // Check if cours table exists, create if needed
            $tableCheck = $conn->query("SHOW TABLES LIKE 'cours'");
            if ($tableCheck->rowCount() === 0) {
                // Create the courses table
                $createTableSql = "CREATE TABLE cours (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    course_id VARCHAR(20) NOT NULL UNIQUE,
                    title VARCHAR(100) NOT NULL,
                    fees DECIMAL(10,2) DEFAULT 0,
                    course_link VARCHAR(255),
                    certification_link VARCHAR(255),
                    status VARCHAR(50) DEFAULT 'free',
                    icon VARCHAR(10) DEFAULT '📚'
                )";
                $conn->exec($createTableSql);
                
                // Add sample data
                $sampleData = [
                    ['CRS-001', 'Web Development', 99.99, 'https://example.com/web', 'https://example.com/cert/web', 'paid', '🌐'],
                    ['CRS-002', 'Python Programming', 0, 'https://example.com/python', 'https://example.com/cert/python', 'free', '🐍']
                ];
                
                $stmt = $conn->prepare("INSERT INTO cours (course_id, title, fees, course_link, certification_link, status, icon) VALUES (?, ?, ?, ?, ?, ?, ?)");
                foreach ($sampleData as $course) {
                    $stmt->execute($course);
                }
            }
        } catch (PDOException $e) {
            $db_error = "Database error: " . $e->getMessage();
            error_log("Database setup error: " . $e->getMessage());
        }
    } catch (PDOException $e) {
        // This catch is for the first PDO connection attempt
        $db_error = "MySQL connection failed. Make sure MySQL is running in XAMPP Control Panel. Error: " . $e->getMessage();
        error_log("MySQL connection error: " . $e->getMessage());
    }
} catch (Exception $e) {
    // Catch any other exceptions
    $db_error = "General error: " . $e->getMessage();
    error_log("General error in database config: " . $e->getMessage());
}

// Function to test if database is working properly
function testDatabaseConnection() {
    global $conn, $db_error;

    // Ensure PDO connection exists and no prior error
    if ($db_error || !($conn instanceof PDO)) {
        return [
            'status'    => 'error',
            'message'   => $db_error ?: 'Database connection not initialized',
            'connected' => false
        ];
    }

    try {
        // Simple test query
        $stmt = $conn->query("SELECT 1");
        if ($stmt !== false) {
            return [
                'status'    => 'success',
                'message'   => 'Database connection successful',
                'connected' => true
            ];
        } else {
            return [
                'status'    => 'error',
                'message'   => 'Could not execute test query',
                'connected' => false
            ];
        }
    } catch (PDOException $e) {
        return [
            'status'    => 'error',
            'message'   => 'Database test exception: ' . $e->getMessage(),
            'connected' => false
        ];
    }
}
?>
