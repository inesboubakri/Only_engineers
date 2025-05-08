<?php
// Display errors for this utility script
error_reporting(E_ALL);
ini_set('display_errors', 1);

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

echo "<h1>Database Table Creator</h1>";

// Check if the table exists
$tableExists = $conn->query("SHOW TABLES LIKE 'cours'");

if ($tableExists->rowCount() > 0) {
    echo "<p>Table 'cours' already exists. Here's the structure:</p>";
    
    // Show table structure
    $result = $conn->query("DESCRIBE cours");
    echo "<table border='1'>";
    echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th></tr>";
    
    while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
        echo "<tr>";
        echo "<td>" . $row["Field"] . "</td>";
        echo "<td>" . $row["Type"] . "</td>";
        echo "<td>" . $row["Null"] . "</td>";
        echo "<td>" . $row["Key"] . "</td>";
        echo "<td>" . $row["Default"] . "</td>";
        echo "<td>" . $row["Extra"] . "</td>";
        echo "</tr>";
    }
    echo "</table>";
    
    echo "<p>If you want to recreate the table, you need to drop it first:</p>";
    echo "<code>DROP TABLE cours;</code>";
} else {
    // Create the table
    $sql = "CREATE TABLE cours (
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
    )";
    
    if ($conn->exec($sql) !== FALSE) {
        echo "<p>Table 'cours' created successfully!</p>";
        
        // Insert sample data
        $sampleData = [
            ["CRS-001", "Introduction to Web Development", 0, "https://example.com/web", "https://example.com/cert/web", "free", "🌐"],
            ["CRS-002", "Advanced JavaScript", 99.99, "https://example.com/js", "https://example.com/cert/js", "paid", "📱"],
            ["CRS-003", "Python for Beginners", 0, "https://example.com/python", "https://example.com/cert/python", "free", "🐍"]
        ];
        
        $insertCount = 0;
        foreach ($sampleData as $course) {
            $stmt = $conn->prepare("INSERT INTO cours (course_id, title, fees, course_link, certification_link, status, icon) 
                                  VALUES (?, ?, ?, ?, ?, ?, ?)");
            if ($stmt->execute($course)) {
                $insertCount++;
            } else {
                echo "<p>Error inserting sample data: " . implode(", ", $stmt->errorInfo()) . "</p>";
            }
        }
        
        echo "<p>$insertCount sample courses added.</p>";
    } else {
        echo "<p>Error creating table: " . implode(", ", $conn->errorInfo()) . "</p>";
    }
}

// Check the count of records
$result = $conn->query("SELECT COUNT(*) as count FROM cours");
$row = $result->fetch(PDO::FETCH_ASSOC);
echo "<p>Number of courses in database: " . $row["count"] . "</p>";

// Show a few records if they exist
if ($row["count"] > 0) {
    $result = $conn->query("SELECT * FROM cours LIMIT 5");
    echo "<h2>Sample Records:</h2>";
    echo "<table border='1'>";
    
    // Print table headers
    $headers = false;
    while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
        if (!$headers) {
            echo "<tr>";
            foreach ($row as $key => $value) {
                echo "<th>$key</th>";
            }
            echo "</tr>";
            $headers = true;
        }
        
        echo "<tr>";
        foreach ($row as $value) {
            echo "<td>" . htmlspecialchars($value ?? 'NULL') . "</td>";
        }
        echo "</tr>";
    }
    echo "</table>";
}

// Check if we're missing the 7th parameter in the prepared statement
echo "<h2>Testing Prepared Statement:</h2>";
$testParams = ["TEST-ID", "Test Course", 9.99, "http://test.com", "http://cert.com", "paid", "📘"];
$paramCount = count($testParams);

echo "<p>Parameter count: $paramCount</p>";
echo "<pre>";
print_r($testParams);
echo "</pre>";

echo "<h2>Binding String:</h2>";
$bindString = "ssdsss"; // The binding string from your code
$bindLength = strlen($bindString);

echo "<p>Binding string length: $bindLength, should be: $paramCount</p>";
echo "<p>Binding string: $bindString</p>";

if ($bindLength != $paramCount) {
    echo "<p style='color:red'>ERROR: The binding string has $bindLength characters but you're trying to bind $paramCount parameters!</p>";
    echo "<p style='color:green'>CORRECT BINDING STRING: 'ssdssss'</p>";
}

$conn = null;
?>
