<?php
// Simple test script to verify database connection
error_reporting(E_ALL);
ini_set('display_errors', 1);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Database Connection Test</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; line-height: 1.6; }
        .success { color: green; font-weight: bold; }
        .error { color: red; font-weight: bold; }
        .container { max-width: 800px; margin: 0 auto; }
        .step { margin-bottom: 20px; padding: 10px; border: 1px solid #ddd; border-radius: 5px; }
        h1 { color: #333; }
        pre { background: #f5f5f5; padding: 10px; overflow: auto; }
    </style>
</head>
<body>
    <div class="container">
        <h1>PDO Database Connection Test</h1>
        
        <?php
        // Step 1: Check if MySQL is running
        echo '<div class="step">';
        echo '<h2>Step 1: Testing MySQL Server</h2>';
        
        try {
            $testDsn = "mysql:host=localhost";
            $testConn = new PDO($testDsn, "root", "");
            echo '<p class="success">✓ MySQL server is running and accessible!</p>';
            
            // Check if PDO is properly enabled
            if (extension_loaded('pdo_mysql')) {
                echo '<p class="success">✓ PDO MySQL extension is enabled</p>';
            } else {
                echo '<p class="error">✗ PDO MySQL extension is not enabled!</p>';
                echo '<p>You need to enable the PDO MySQL extension in your PHP configuration.</p>';
            }
            
        } catch (PDOException $e) {
            echo '<p class="error">✗ Could not connect to MySQL server!</p>';
            echo '<p>Error message: ' . $e->getMessage() . '</p>';
            echo '<p>Possible solutions:</p>';
            echo '<ul>';
            echo '<li>Make sure MySQL is running in XAMPP Control Panel</li>';
            echo '<li>Verify that MySQL is using the default port (3306)</li>';
            echo '<li>Check if username "root" with no password is correct</li>';
            echo '</ul>';
            die('</div></body></html>');
        }
        echo '</div>';
        
        // Step 2: Include config.php and test connection
        echo '<div class="step">';
        echo '<h2>Step 2: Testing Your Application Connection</h2>';
        
        // Include the config file
        require_once 'model/config.php';
        
        if ($db_error) {
            echo '<p class="error">✗ Database connection error!</p>';
            echo '<p>Error message: ' . $db_error . '</p>';
        } else {
            echo '<p class="success">✓ Successfully connected to database!</p>';
            
            // Test query
            try {
                $testStmt = $conn->query("SELECT COUNT(*) FROM cours");
                $count = $testStmt->fetchColumn();
                echo '<p class="success">✓ Successfully queried the database! Found ' . $count . ' courses.</p>';
                
                if ($count > 0) {
                    // Show sample data
                    $sampleStmt = $conn->query("SELECT * FROM cours LIMIT 3");
                    $courses = $sampleStmt->fetchAll(PDO::FETCH_ASSOC);
                    
                    echo '<h3>Sample Data:</h3>';
                    echo '<pre>';
                    print_r($courses);
                    echo '</pre>';
                }
                
            } catch (PDOException $e) {
                echo '<p class="error">✗ Error executing query: ' . $e->getMessage() . '</p>';
            }
        }
        echo '</div>';
        
        // Step 3: Show connection details
        echo '<div class="step">';
        echo '<h2>Step 3: Database Configuration</h2>';
        echo '<p>These are the current database settings:</p>';
        echo '<ul>';
        echo '<li><strong>Server:</strong> ' . $servername . '</li>';
        echo '<li><strong>Database:</strong> ' . $dbname . '</li>';
        echo '<li><strong>Username:</strong> ' . $username . '</li>';
        echo '<li><strong>Password:</strong> ' . (empty($password) ? '(empty)' : '(set)') . '</li>';
        echo '</ul>';
        echo '</div>';
        
        // Step 4: Check if we need to run setup script
        echo '<div class="step">';
        echo '<h2>Step 4: Next Steps</h2>';
        
        if (!$db_error) {
            echo '<p class="success">✓ Your database is working correctly with PDO!</p>';
            echo '<p>You can now:</p>';
            echo '<ul>';
            echo '<li><a href="view/dashboard.html">Go to Dashboard</a></li>';
            echo '<li><a href="../front_office/front_office/view/courses.html">Go to Front Office</a></li>';
            echo '</ul>';
        } else {
            echo '<p>You still have database connection issues. Try the following:</p>';
            echo '<ol>';
            echo '<li>Make sure MySQL is running in XAMPP Control Panel</li>';
            echo '<li>Verify database settings in config.php</li>';
            echo '<li><a href="model/setup_database.php">Run the database setup script</a></li>';
            echo '</ol>';
        }
        echo '</div>';
        ?>
    </div>
</body>
</html>