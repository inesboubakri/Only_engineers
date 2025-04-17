<?php
// Script to check if the cours table exists and has the correct structure
error_reporting(E_ALL);
ini_set('display_errors', 1); // Display errors for this utility script

// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "aziz";

// Create PDO connection
try {
    $dsn = "mysql:host={$servername};dbname={$dbname};charset=utf8mb4";
    $conn = new PDO(
        $dsn,
        $username,
        $password,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
} catch (PDOException $e) {
    die('Connection failed: ' . $e->getMessage());
}

echo "<h1>Database Structure Check</h1>";

// Check if the cours table exists
$tableExists = $conn->query("SHOW TABLES LIKE 'cours'");
if ($tableExists->rowCount() > 0) {
    echo "<p style='color:green'>Table 'cours' exists.</p>";
    
    // Check the table structure
    $columns = $conn->query("SHOW COLUMNS FROM cours");
    
    echo "<h2>Table Structure:</h2>";
    echo "<table border='1'>";
    echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th></tr>";
    
    $requiredColumns = [
        'id' => false,
        'course_id' => false,
        'title' => false,
        'fees' => false,
        'course_link' => false,
        'certification_link' => false,
        'status' => false,
        'icon' => false
    ];
    
    while ($row = $columns->fetch(PDO::FETCH_ASSOC)) {
        echo "<tr>";
        echo "<td>" . $row['Field'] . "</td>";
        echo "<td>" . $row['Type'] . "</td>";
        echo "<td>" . $row['Null'] . "</td>";
        echo "<td>" . $row['Key'] . "</td>";
        echo "<td>" . $row['Default'] . "</td>";
        echo "<td>" . $row['Extra'] . "</td>";
        echo "</tr>";
        
        if (isset($requiredColumns[$row['Field']])) {
            $requiredColumns[$row['Field']] = true;
        }
    }
    echo "</table>";
    
    // Check if any required columns are missing
    $missingColumns = [];
    foreach ($requiredColumns as $column => $exists) {
        if (!$exists) {
            $missingColumns[] = $column;
        }
    }
    
    if (!empty($missingColumns)) {
        echo "<p style='color:red'>Missing columns: " . implode(", ", $missingColumns) . "</p>";
        
        // Provide SQL to create the missing columns
        echo "<h2>SQL to Add Missing Columns:</h2>";
        echo "<pre>";
        foreach ($missingColumns as $column) {
            switch ($column) {
                case 'id':
                    echo "ALTER TABLE cours ADD id INT AUTO_INCREMENT PRIMARY KEY;\n";
                    break;
                case 'course_id':
                    echo "ALTER TABLE cours ADD course_id VARCHAR(20) UNIQUE NOT NULL;\n";
                    break;
                case 'title':
                    echo "ALTER TABLE cours ADD title VARCHAR(255) NOT NULL;\n";
                    break;
                case 'fees':
                    echo "ALTER TABLE cours ADD fees DECIMAL(10,2) DEFAULT 0;\n";
                    break;
                case 'course_link':
                    echo "ALTER TABLE cours ADD course_link VARCHAR(255);\n";
                    break;
                case 'certification_link':
                    echo "ALTER TABLE cours ADD certification_link VARCHAR(255);\n";
                    break;
                case 'status':
                    echo "ALTER TABLE cours ADD status VARCHAR(50) DEFAULT 'free';\n";
                    break;
                case 'icon':
                    echo "ALTER TABLE cours ADD icon VARCHAR(10) DEFAULT '📚';\n";
                    break;
            }
        }
        echo "</pre>";
    } else {
        echo "<p style='color:green'>All required columns exist.</p>";
    }
    
    // Show a sample of data
    $sample = $conn->query("SELECT * FROM cours LIMIT 5");
    if ($sample->rowCount() > 0) {
        echo "<h2>Sample Data:</h2>";
        echo "<table border='1'>";
        
        // Print table headers
        $first = true;
        while ($row = $sample->fetch(PDO::FETCH_ASSOC)) {
            if ($first) {
                echo "<tr>";
                foreach ($row as $key => $value) {
                    echo "<th>$key</th>";
                }
                echo "</tr>";
                $first = false;
            }
            
            echo "<tr>";
            foreach ($row as $value) {
                echo "<td>" . htmlspecialchars($value) . "</td>";
            }
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "<p>No data in the table.</p>";
    }
} else {
    echo "<p style='color:red'>Table 'cours' does not exist.</p>";
    
    // Provide SQL to create the table
    echo "<h2>SQL to Create Table:</h2>";
    echo "<pre>";
    echo "CREATE TABLE cours (
    id INT AUTO_INCREMENT PRIMARY KEY,
    course_id VARCHAR(20) UNIQUE NOT NULL,
    title VARCHAR(255) NOT NULL,
    fees DECIMAL(10,2) DEFAULT 0,
    course_link VARCHAR(255),
    certification_link VARCHAR(255),
    status VARCHAR(50) DEFAULT 'free',
    icon VARCHAR(10) DEFAULT '📚',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);</pre>";
}

$conn = null;
?>
