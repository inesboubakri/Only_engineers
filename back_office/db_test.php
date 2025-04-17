<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

$success = false;
$message = "";
$data = null;

// Direct test of database connection
try {
    // Connect to MySQL without selecting a database
    $servername = "localhost";
    $username = "root";
    $password = "";
    $dbname = "aziz";
    
    // Simple style for the page
    echo "<style>
        body { font-family: Arial, sans-serif; max-width: 800px; margin: 40px auto; padding: 20px; line-height: 1.6; }
        .success { color: green; border-left: 4px solid green; padding-left: 10px; }
        .error { color: red; border-left: 4px solid red; padding-left: 10px; }
        pre { background: #f4f4f4; padding: 10px; border-radius: 5px; }
        .step { margin-bottom: 20px; }
    </style>";
    
    echo "<h1>Database Connection Test</h1>";
    
    // STEP 1: Test basic MySQL connection
    echo "<div class='step'>";
    echo "<h2>Step 1: Testing MySQL Connection</h2>";
    
    $testConn = new PDO("mysql:host=$servername", $username, $password);
    $testConn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "<p class='success'>✓ MySQL is running and accessible</p>";
    
    echo "</div>";
    
    // STEP 2: Check if database exists
    echo "<div class='step'>";
    echo "<h2>Step 2: Checking Database</h2>";
    
    $stmt = $testConn->query("SELECT SCHEMA_NAME FROM INFORMATION_SCHEMA.SCHEMATA WHERE SCHEMA_NAME = '$dbname'");
    $dbExists = $stmt->rowCount() > 0;
    
    if ($dbExists) {
        echo "<p class='success'>✓ Database '$dbname' exists</p>";
    } else {
        echo "<p class='error'>✗ Database '$dbname' does not exist</p>";
        echo "<p>Attempting to create database...</p>";
        
        try {
            $testConn->exec("CREATE DATABASE `$dbname` CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci");
            echo "<p class='success'>✓ Created database '$dbname'</p>";
            $dbExists = true;
        } catch (PDOException $e) {
            echo "<p class='error'>✗ Failed to create database: " . $e->getMessage() . "</p>";
        }
    }
    
    echo "</div>";
    
    // STEP 3: Connect to the specific database
    if ($dbExists) {
        echo "<div class='step'>";
        echo "<h2>Step 3: Connecting To Database</h2>";
        
        try {
            $dbConn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
            $dbConn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            echo "<p class='success'>✓ Connected to database '$dbname'</p>";
            
            // Check if table exists
            $stmt = $dbConn->query("SHOW TABLES LIKE 'cours'");
            $tableExists = $stmt->rowCount() > 0;
            
            if ($tableExists) {
                echo "<p class='success'>✓ Table 'cours' exists</p>";
                
                // Check if table has data
                $stmt = $dbConn->query("SELECT COUNT(*) as count FROM cours");
                $count = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
                echo "<p>Found $count courses in database</p>";
                
                if ($count > 0) {
                    $stmt = $dbConn->query("SELECT * FROM cours LIMIT 3");
                    $courses = $stmt->fetchAll(PDO::FETCH_ASSOC);
                    echo "<p>Sample courses:</p>";
                    echo "<pre>" . print_r($courses, true) . "</pre>";
                } else {
                    echo "<p>No courses in database. Adding sample data...</p>";
                    
                    // Insert sample data
                    $sampleData = [
                        ['CRS-001', 'Web Development', 99.99, 'https://example.com/web', 'https://example.com/cert/web', 'paid', '🌐'],
                        ['CRS-002', 'Python Programming', 0, 'https://example.com/python', 'https://example.com/cert/python', 'free', '🐍']
                    ];
                    
                    $stmt = $dbConn->prepare("INSERT INTO cours (course_id, title, fees, course_link, certification_link, status, icon) VALUES (?, ?, ?, ?, ?, ?, ?)");
                    
                    foreach ($sampleData as $course) {
                        $stmt->execute($course);
                    }
                    
                    echo "<p class='success'>✓ Added sample courses</p>";
                }
            } else {
                echo "<p class='error'>✗ Table 'cours' does not exist</p>";
                echo "<p>Creating table...</p>";
                
                // Create table
                $sql = "CREATE TABLE cours (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    course_id VARCHAR(20) NOT NULL UNIQUE,
                    title VARCHAR(100) NOT NULL,
                    fees DECIMAL(10,2) DEFAULT 0,
                    course_link VARCHAR(255),
                    certification_link VARCHAR(255),
                    status VARCHAR(50) DEFAULT 'free',
                    icon VARCHAR(10) DEFAULT '📚'
                )";
                
                $dbConn->exec($sql);
                echo "<p class='success'>✓ Created table 'cours'</p>";
                
                // Insert sample data
                $sampleData = [
                    ['CRS-001', 'Web Development', 99.99, 'https://example.com/web', 'https://example.com/cert/web', 'paid', '🌐'],
                    ['CRS-002', 'Python Programming', 0, 'https://example.com/python', 'https://example.com/cert/python', 'free', '🐍']
                ];
                
                $stmt = $dbConn->prepare("INSERT INTO cours (course_id, title, fees, course_link, certification_link, status, icon) VALUES (?, ?, ?, ?, ?, ?, ?)");
                
                foreach ($sampleData as $course) {
                    $stmt->execute($course);
                }
                
                echo "<p class='success'>✓ Added sample courses</p>";
            }
            
        } catch (PDOException $e) {
            echo "<p class='error'>✗ Failed to connect to database: " . $e->getMessage() . "</p>";
        }
        
        echo "</div>";
    }
    
    // STEP 4: Final test with config.php
    echo "<div class='step'>";
    echo "<h2>Step 4: Testing Your Application's Connection</h2>";
    
    $configPath = __DIR__ . '/model/config.php';
    if (file_exists($configPath)) {
        echo "<p>Testing connection using your application's config.php...</p>";
        
        // Include the config file
        require_once $configPath;
        
        // Test the connection
        $result = testDatabaseConnection();
        
        if ($result['connected']) {
            echo "<p class='success'>✓ Application's database connection successful!</p>";
            echo "<p><strong>Your database is now working with PDO.</strong></p>";
        } else {
            echo "<p class='error'>✗ Application's database connection failed: " . $result['message'] . "</p>";
            echo "<p>We already verified MySQL is running and the database exists, so this is likely a configuration issue.</p>";
        }
    } else {
        echo "<p class='error'>✗ Could not find config.php at $configPath</p>";
    }
    
    echo "</div>";
    
    // Final success message
    echo "<div class='step'>";
    echo "<h2>What's Next</h2>";
    echo "<p>If all steps above show a green checkmark, your database is working correctly with PDO.</p>";
    echo "<p>You should now be able to:</p>";
    echo "<ul>";
    echo "<li>Open your application in the browser</li>";
    echo "<li>Add, update, and delete courses</li>";
    echo "<li>View course listings</li>";
    echo "</ul>";
    echo "<p>Your application URL should be:</p>";
    
    // Determine the URL to the dashboard/front page
    $dashboardPath = __DIR__ . '/view/dashboard.html';
    $frontOfficePath = __DIR__ . '/../front_office/front_office/view/courses.html';
    
    if (file_exists($dashboardPath)) {
        $relativePath = str_replace($_SERVER['DOCUMENT_ROOT'], '', $dashboardPath);
        $url = 'http://' . $_SERVER['HTTP_HOST'] . str_replace('\\', '/', $relativePath);
        echo "<p><a href='$url'>$url</a> (Dashboard)</p>";
    }
    
    if (file_exists($frontOfficePath)) {
        $relativePath = str_replace($_SERVER['DOCUMENT_ROOT'], '', $frontOfficePath);
        $url = 'http://' . $_SERVER['HTTP_HOST'] . str_replace('\\', '/', $relativePath);
        echo "<p><a href='$url'>$url</a> (Front Office)</p>";
    }
    
    echo "</div>";
    
} catch (PDOException $e) {
    echo "<h1>Database Test Failed</h1>";
    echo "<p class='error'>Error: " . $e->getMessage() . "</p>";
    
    echo "<h2>Troubleshooting</h2>";
    echo "<ol>";
    echo "<li>Make sure MySQL is running in XAMPP Control Panel</li>";
    echo "<li>If MySQL isn't running, start it and refresh this page</li>";
    echo "<li>Check that user 'root' with empty password has access to MySQL</li>";
    echo "</ol>";
}