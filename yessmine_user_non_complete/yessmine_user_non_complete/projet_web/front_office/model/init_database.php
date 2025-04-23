<?php
// Disable direct error output
ini_set('display_errors', 0);
error_reporting(E_ALL);

// Ensure JSON response
header('Content-Type: application/json');

try {
    // First connect without database
    $host = 'localhost';
    $username = 'root';
    $password = '';
    
    $pdo = new PDO("mysql:host=$host", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Read and execute the SQL file
    $sql = file_get_contents(__DIR__ . '/database_setup.sql');
    if ($sql === false) {
        throw new Exception("Could not read database setup file");
    }
    
    // Split into individual queries and execute each one
    $queries = array_filter(array_map('trim', explode(';', $sql)));
    
    foreach($queries as $query) {
        if (!empty($query)) {
            try {
                $pdo->exec($query);
            } catch (PDOException $e) {
                // Log the error but continue with other queries
                error_log("Error executing query: " . $e->getMessage());
                error_log("Query was: " . $query);
            }
        }
    }
    
    // Test if we can connect to the new database
    $test_conn = new PDO("mysql:host=$host;dbname=onlyengineers", $username, $password);
    
    // Test if tables exist
    $tables = ['users', 'hackathons'];
    $missing_tables = [];
    foreach ($tables as $table) {
        $result = $test_conn->query("SHOW TABLES LIKE '$table'");
        if ($result->rowCount() == 0) {
            $missing_tables[] = $table;
        }
    }
    
    if (!empty($missing_tables)) {
        throw new Exception("Database initialized but some tables are missing: " . implode(', ', $missing_tables));
    }
    
    echo json_encode([
        'success' => true,
        'message' => "Database and tables created successfully! You can now return to the hackathons page."
    ]);
    
} catch(PDOException $e) {
    error_log($e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => "Database error: " . $e->getMessage()
    ]);
} catch(Exception $e) {
    error_log($e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>