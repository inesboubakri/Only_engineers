<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

// This script will help troubleshoot and fix database connection issues
echo "<h1>Database Connection Troubleshooter</h1>";

// Database configuration - same as in config.php
$servername = "localhost";
$username   = "root";
$password   = "";
$dbname     = "aziz";

// Step 1: Check if MySQL is running by attempting a basic connection
echo "<h2>Step 1: Testing MySQL Server Connection</h2>";
try {
    // Try connecting without specifying a database
    $basicDsn = "mysql:host={$servername}";
    $basicConn = new PDO($basicDsn, $username, $password);
    $basicConn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "<p style='color:green'>✓ Successfully connected to MySQL server!</p>";
} catch (PDOException $e) {
    echo "<p style='color:red'>✗ Failed to connect to MySQL server: " . $e->getMessage() . "</p>";
    echo "<p><strong>Fix:</strong> Make sure MySQL is running in XAMPP Control Panel.</p>";
    echo "<p>1. Open XAMPP Control Panel<br>2. Click 'Start' next to MySQL<br>3. Refresh this page</p>";
    exit; // Stop here if we can't even connect to MySQL
}

// Step 2: Check if database exists, create if needed
echo "<h2>Step 2: Checking Database</h2>";
try {
    $sql = "SELECT SCHEMA_NAME FROM INFORMATION_SCHEMA.SCHEMATA WHERE SCHEMA_NAME = '{$dbname}'";
    $stmt = $basicConn->query($sql);
    
    if ($stmt->rowCount() > 0) {
        echo "<p style='color:green'>✓ Database '{$dbname}' exists</p>";
    } else {
        echo "<p style='color:orange'>⚠ Database '{$dbname}' does not exist. Creating it now...</p>";
        $createDbSql = "CREATE DATABASE `{$dbname}` CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci";
        if ($basicConn->exec($createDbSql) !== false) {
            echo "<p style='color:green'>✓ Database '{$dbname}' created successfully</p>";
        } else {
            echo "<p style='color:red'>✗ Failed to create database</p>";
            exit;
        }
    }
} catch (PDOException $e) {
    echo "<p style='color:red'>✗ Error checking/creating database: " . $e->getMessage() . "</p>";
    exit;
}

// Step 3: Connect to the specific database
echo "<h2>Step 3: Connecting to '{$dbname}' Database</h2>";
try {
    $dsn = "mysql:host={$servername};dbname={$dbname};charset=utf8mb4";
    $conn = new PDO($dsn, $username, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]);
    echo "<p style='color:green'>✓ Successfully connected to '{$dbname}' database!</p>";
} catch (PDOException $e) {
    echo "<p style='color:red'>✗ Error connecting to database: " . $e->getMessage() . "</p>";
    exit;
}

// Step 4: Check if 'cours' table exists, create if needed
echo "<h2>Step 4: Checking 'cours' Table</h2>";
try {
    $sql = "SHOW TABLES LIKE 'cours'";
    $stmt = $conn->query($sql);
    
    if ($stmt->rowCount() > 0) {
        echo "<p style='color:green'>✓ Table 'cours' exists</p>";
    } else {
        echo "<p style='color:orange'>⚠ Table 'cours' does not exist. Creating it now...</p>";
        
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
        
        if ($conn->exec($createTableSql) !== false) {
            echo "<p style='color:green'>✓ Table 'cours' created successfully</p>";
            
            // Insert sample data
            $sampleData = [
                ['CRS-001', 'Web Development Fundamentals', 99.99, 'https://example.com/web', 'https://example.com/web-cert', 'paid', '🌐'],
                ['CRS-002', 'Python for Beginners', 0, 'https://example.com/python', 'https://example.com/python-cert', 'free', '🐍'],
                ['CRS-003', 'Advanced JavaScript', 149.99, 'https://example.com/js', 'https://example.com/js-cert', 'paid', '📱']
            ];
            
            $insertSql = "INSERT INTO cours (course_id, title, fees, course_link, certification_link, status, icon) 
                          VALUES (?, ?, ?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($insertSql);
            
            $insertedCount = 0;
            foreach ($sampleData as $course) {
                if ($stmt->execute($course)) {
                    $insertedCount++;
                }
            }
            
            if ($insertedCount > 0) {
                echo "<p style='color:green'>✓ Added {$insertedCount} sample courses</p>";
            }
        } else {
            echo "<p style='color:red'>✗ Failed to create 'cours' table</p>";
        }
    }
} catch (PDOException $e) {
    echo "<p style='color:red'>✗ Error checking/creating table: " . $e->getMessage() . "</p>";
}

// Step 5: Verify config.php settings match what we're using
echo "<h2>Step 5: Verifying config.php</h2>";
$configPath = __DIR__ . '/config.php';
if (file_exists($configPath)) {
    echo "<p style='color:green'>✓ config.php file exists</p>";
    echo "<p>Make sure it has these settings:</p>";
    echo "<pre>
\$servername = \"localhost\";
\$username = \"root\";
\$password = \"\";
\$dbname = \"aziz\";
</pre>";
} else {
    echo "<p style='color:red'>✗ config.php file not found at {$configPath}</p>";
}

// Final test - try to fetch data
echo "<h2>Final Test: Querying Data</h2>";
try {
    $sql = "SELECT * FROM cours LIMIT 1";
    $stmt = $conn->query($sql);
    
    if ($stmt && $stmt->rowCount() > 0) {
        $row = $stmt->fetch();
        echo "<p style='color:green'>✓ Successfully retrieved data from 'cours' table!</p>";
        echo "<p>Sample data: Course ID: {$row['course_id']}, Title: {$row['title']}</p>";
    } else {
        echo "<p style='color:orange'>⚠ Table exists but contains no data</p>";
    }
} catch (PDOException $e) {
    echo "<p style='color:red'>✗ Error querying data: " . $e->getMessage() . "</p>";
}

// Display summary
echo "<h2>Summary</h2>";
echo "<div style='background-color: #f0f0f0; padding: 15px; border-radius: 5px;'>";
echo "<p>✓ MySQL server is running<br>";
echo "✓ Database '{$dbname}' exists<br>";
echo "✓ Connection to the database works<br>";
echo "✓ Table 'cours' is ready to use</p>";

echo "<p><strong>Next steps:</strong></p>";
echo "<ol>";
echo "<li>Try your application again</li>";
echo "<li>If you still have issues, check your application code to make sure it's using these same connection details</li>";
echo "</ol>";
echo "</div>";

echo "<p style='margin-top: 20px'><a href='../test_db_connection.php' style='padding: 10px; background-color: #4CAF50; color: white; text-decoration: none; border-radius: 4px;'>Test Database Connection</a></p>";