<?php
// Database upgrade script to modify table structure
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "aziz";

echo "<h1>Database Structure Update</h1>";

try {
    // Connect to the database
    $dsn = "mysql:host={$servername};dbname={$dbname};charset=utf8mb4";
    $conn = new PDO($dsn, $username, $password, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
    
    echo "<p style='color:green'>✓ Connected to database successfully</p>";
    
    // Execute multiple ALTER statements to update the table
    $alterStatements = [
        // Increase title field size
        "ALTER TABLE cours MODIFY title VARCHAR(100) NOT NULL",
        
        // Change fees to decimal
        "ALTER TABLE cours MODIFY fees DECIMAL(10,2) NOT NULL DEFAULT 0",
        
        // Make some fields optional (NULL allowed)
        "ALTER TABLE cours MODIFY course_link VARCHAR(255) NULL",
        "ALTER TABLE cours MODIFY certification_link VARCHAR(255) NULL",
        
        // Add default for status
        "ALTER TABLE cours MODIFY status VARCHAR(50) NOT NULL DEFAULT 'free'",
        
        // Add icon column if it doesn't exist
        "ALTER TABLE cours ADD COLUMN IF NOT EXISTS icon VARCHAR(10) DEFAULT '📚'",
        
        // Add auto-increment ID as the first column if it doesn't exist
        "ALTER TABLE cours ADD COLUMN IF NOT EXISTS id INT AUTO_INCREMENT FIRST",
        
        // Add primary key to ID (if needed)
        "ALTER TABLE cours DROP PRIMARY KEY, ADD PRIMARY KEY (id), ADD UNIQUE (course_id)"
    ];
    
    // Execute each statement and report
    foreach ($alterStatements as $sql) {
        try {
            $conn->exec($sql);
            echo "<p style='color:green'>✓ Executed: " . htmlspecialchars($sql) . "</p>";
        } catch (PDOException $e) {
            // Don't stop on errors - some may fail if constraints already exist
            echo "<p style='color:orange'>⚠ Statement failed (may be OK): " . htmlspecialchars($sql) . "</p>";
            echo "<p style='color:orange'>⚠ Error: " . $e->getMessage() . "</p>";
        }
    }
    
    // Verify the current table structure
    $result = $conn->query("DESCRIBE cours");
    
    echo "<h2>Updated Table Structure:</h2>";
    echo "<table border='1' cellpadding='5'>";
    echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th></tr>";
    
    while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
        echo "<tr>";
        foreach ($row as $key => $value) {
            echo "<td>" . htmlspecialchars($value ?? 'NULL') . "</td>";
        }
        echo "</tr>";
    }
    
    echo "</table>";
    
    // Verify data in the table
    $countResult = $conn->query("SELECT COUNT(*) as count FROM cours");
    $count = $countResult->fetch(PDO::FETCH_ASSOC)['count'];
    
    echo "<h2>Data in Table:</h2>";
    echo "<p>The table contains {$count} records.</p>";
    
    if ($count > 0) {
        $dataResult = $conn->query("SELECT * FROM cours LIMIT 5");
        
        echo "<table border='1' cellpadding='5'>";
        
        // Print headers
        $first = true;
        while ($row = $dataResult->fetch(PDO::FETCH_ASSOC)) {
            if ($first) {
                echo "<tr>";
                foreach ($row as $key => $value) {
                    echo "<th>" . htmlspecialchars($key) . "</th>";
                }
                echo "</tr>";
                $first = false;
            }
            
            echo "<tr>";
            foreach ($row as $value) {
                echo "<td>" . htmlspecialchars($value ?? 'NULL') . "</td>";
            }
            echo "</tr>";
        }
        
        echo "</table>";
    } else {
        // Add sample data if table is empty
        echo "<p>No data found. Adding sample data...</p>";
        
        $sampleData = [
            ['CRS-001', 'Web Development', 99.99, 'https://example.com/web', 'https://example.com/cert/web', 'paid', '🌐'],
            ['CRS-002', 'Python Programming', 0, 'https://example.com/python', 'https://example.com/cert/python', 'free', '🐍'],
            ['CRS-003', 'Data Science', 149.99, 'https://example.com/data', 'https://example.com/cert/data', 'paid', '📊']
        ];
        
        $stmt = $conn->prepare("INSERT INTO cours (course_id, title, fees, course_link, certification_link, status, icon) VALUES (?, ?, ?, ?, ?, ?, ?)");
        
        $insertCount = 0;
        foreach ($sampleData as $course) {
            if ($stmt->execute($course)) {
                $insertCount++;
            }
        }
        
        echo "<p style='color:green'>✓ Added {$insertCount} sample courses</p>";
    }
    
    echo "<p style='color:green'><strong>Database structure updated successfully!</strong></p>";
    echo "<p><a href='../db_connection_test.php'>Test Database Connection</a></p>";
    
} catch (PDOException $e) {
    echo "<p style='color:red'>Error: " . $e->getMessage() . "</p>";
}
?>