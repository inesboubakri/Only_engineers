<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Simple database connection test with clear error messages
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Database Connection Troubleshooter</title>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; max-width: 800px; margin: 0 auto; padding: 20px; }
        .success { color: green; border-left: 4px solid green; padding-left: 10px; }
        .error { color: red; border-left: 4px solid red; padding-left: 10px; }
        .warning { color: orange; border-left: 4px solid orange; padding-left: 10px; }
        .step { margin-bottom: 20px; padding: 15px; border: 1px solid #ddd; border-radius: 4px; }
        h1 { color: #333; }
        .fix-steps { background-color: #f8f9fa; padding: 15px; border-radius: 4px; }
    </style>
</head>
<body>
    <h1>Database Connection Troubleshooter</h1>
    
    <?php
    // STEP 1: Check if MySQL is running
    echo '<div class="step">';
    echo '<h2>Step 1: Testing MySQL Server</h2>';
    
    $mysqlRunning = false;
    try {
        $testConn = new PDO("mysql:host=localhost", "root", "");
        echo '<p class="success">✓ MySQL server is running!</p>';
        $mysqlRunning = true;
    } catch (PDOException $e) {
        echo '<p class="error">✗ Cannot connect to MySQL server</p>';
        echo '<p>Error: ' . $e->getMessage() . '</p>';
        
        echo '<div class="fix-steps">';
        echo '<h3>How to fix:</h3>';
        echo '<ol>';
        echo '<li>Open XAMPP Control Panel</li>';
        echo '<li>Look for MySQL row and check if it\'s running (green)</li>';
        echo '<li>If MySQL is not running, click the "Start" button next to MySQL</li>';
        echo '<li>Wait until the status turns green</li>';
        echo '<li>Refresh this page</li>';
        echo '</ol>';
        
        echo '<p>If MySQL won\'t start, try these steps:</p>';
        echo '<ol>';
        echo '<li>Click "Stop" if there\'s a "Running" status but with red background</li>';
        echo '<li>Check XAMPP logs for errors</li>';
        echo '<li>Try restarting XAMPP completely</li>';
        echo '</ol>';
        echo '</div>';
    }
    echo '</div>';
    
    // Only continue if MySQL is running
    if ($mysqlRunning) {
        // STEP 2: Check if the database exists
        echo '<div class="step">';
        echo '<h2>Step 2: Checking Database</h2>';
        
        try {
            $stmt = $testConn->query("SELECT SCHEMA_NAME FROM INFORMATION_SCHEMA.SCHEMATA WHERE SCHEMA_NAME = 'aziz'");
            $dbExists = $stmt->rowCount() > 0;
            
            if ($dbExists) {
                echo '<p class="success">✓ Database "aziz" exists</p>';
            } else {
                echo '<p class="warning">⚠ Database "aziz" does not exist</p>';
                echo '<p>Creating database...</p>';
                
                try {
                    $testConn->exec("CREATE DATABASE `aziz` CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci");
                    echo '<p class="success">✓ Database "aziz" created successfully</p>';
                    $dbExists = true;
                } catch (PDOException $e) {
                    echo '<p class="error">✗ Failed to create database: ' . $e->getMessage() . '</p>';
                }
            }
        } catch (PDOException $e) {
            echo '<p class="error">✗ Error checking database: ' . $e->getMessage() . '</p>';
        }
        
        echo '</div>';
        
        // STEP 3: Test your application's connection
        echo '<div class="step">';
        echo '<h2>Step 3: Testing Application Connection</h2>';
        
        echo '<p>Checking your application\'s database connection using config.php...</p>';
        
        require_once 'model/config.php';
        
        $testResult = testDatabaseConnection();
        
        if ($testResult['connected']) {
            echo '<p class="success">✓ Application connected to database successfully!</p>';
            
            // Additional check - try to query the table
            try {
                $tableCheck = $conn->query("SHOW TABLES LIKE 'cours'");
                if ($tableCheck->rowCount() > 0) {
                    echo '<p class="success">✓ Table "cours" exists</p>';
                    
                    // Check for records
                    $countCheck = $conn->query("SELECT COUNT(*) as count FROM cours");
                    $count = $countCheck->fetch()['count'];
                    echo '<p>Found ' . $count . ' courses in the database</p>';
                    
                    if ($count > 0) {
                        $sample = $conn->query("SELECT * FROM cours LIMIT 1")->fetch();
                        echo '<p>Sample course: ' . htmlspecialchars($sample['title']) . '</p>';
                    }
                } else {
                    echo '<p class="warning">⚠ Table "cours" does not exist</p>';
                    echo '<p>You may need to run the database setup script</p>';
                }
            } catch (PDOException $e) {
                echo '<p class="error">✗ Error querying table: ' . $e->getMessage() . '</p>';
            }
        } else {
            echo '<p class="error">✗ Application failed to connect to the database</p>';
            echo '<p>Error: ' . $testResult['message'] . '</p>';
        }
        
        echo '</div>';
    }
    ?>
    
    <div class="step">
        <h2>Summary and Next Steps</h2>
        <?php if ($mysqlRunning && isset($testResult) && $testResult['connected']): ?>
            <p class="success">✓ Your database connection is working properly!</p>
            <p>You should now be able to use your application without the "failed to connect to database" error.</p>
            <p>Navigate to:</p>
            <ul>
                <li><a href="view/dashboard.html">Dashboard</a></li>
                <li><a href="view/courses.html">Courses</a></li>
                <li><a href="../front_office/front_office/view/courses.html">Front Office</a></li>
            </ul>
        <?php else: ?>
            <p class="error">✗ There are still issues with your database connection</p>
            <p>Please fix the errors identified above and refresh this page to test again.</p>
            <p>If you've fixed MySQL and it's running, you can try these additional resources:</p>
            <ul>
                <li><a href="model/fix_database.php">Run Database Fix Script</a></li>
                <li><a href="model/update_database_structure.php">Update Database Structure</a></li>
            </ul>
        <?php endif; ?>
    </div>
</body>
</html>