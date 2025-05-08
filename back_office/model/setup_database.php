<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Database Setup Wizard</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
        }
        .step {
            margin-bottom: 20px;
            padding: 15px;
            border: 1px solid #ddd;
            border-radius: 5px;
        }
        .success {
            background-color: #d4edda;
            color: #155724;
            border-color: #c3e6cb;
        }
        .warning {
            background-color: #fff3cd;
            color: #856404;
            border-color: #ffeeba;
        }
        .error {
            background-color: #f8d7da;
            color: #721c24;
            border-color: #f5c6cb;
        }
        .info {
            background-color: #d1ecf1;
            color: #0c5460;
            border-color: #bee5eb;
        }
        button {
            padding: 10px 15px;
            background-color: #4CAF50;
            color: white;
            border: none;
            cursor: pointer;
            border-radius: 4px;
            font-size: 16px;
        }
        button:hover {
            background-color: #45a049;
        }
        pre {
            background-color: #f8f9fa;
            padding: 10px;
            border-radius: 4px;
            overflow-x: auto;
        }
    </style>
</head>
<body>
    <h1>Database Setup Wizard</h1>
    
    <?php
    // Wizard logic 
    $servername = "localhost";
    $username   = "root";
    $password   = "";
    $dbname     = "aziz";
    
    // Step 1: Check if we can connect to MySQL server
    echo '<div class="step info" id="step1"><h2>Step 1: Checking MySQL Connection</h2>';
    
    try {
        // Try connecting to MySQL without specifying a database
        $dsn = "mysql:host={$servername}";
        $conn = new PDO($dsn, $username, $password, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
        echo '<div class="success">✅ Connected to MySQL server successfully!</div>';
    } catch (PDOException $e) {
        echo '<div class="error">❌ Failed to connect to MySQL server: ' . $e->getMessage() . '</div>';
        echo '<p>Please make sure:</p>
              <ul>
                <li>MySQL service is running in XAMPP Control Panel</li>
                <li>The username and password are correct (default is usually username="root" with empty password)</li>
              </ul>';
        
        // Show the XAMPP Control Panel instructions
        echo '<div class="info">
                <p><strong>How to start MySQL in XAMPP:</strong></p>
                <ol>
                    <li>Open XAMPP Control Panel</li>
                    <li>Click the "Start" button next to MySQL</li>
                    <li>Wait until the status turns green</li>
                    <li>Refresh this page</li>
                </ol>
              </div>';
        
        echo '</div>';
        echo '<p><button onclick="window.location.reload()">Retry Connection</button></p>';
        // Stop here if we can't connect to MySQL
        echo '</body></html>';
        exit;
    }
    
    echo '</div>';
    
    // Step 2: Create or verify database
    echo '<div class="step info" id="step2"><h2>Step 2: Setting Up Database</h2>';
    
    try {
        // Create database if it doesn't exist
        $sql = "CREATE DATABASE IF NOT EXISTS `{$dbname}`";
        $conn->exec($sql);
        echo "<div class='success'>✅ Database '{$dbname}' checked/created successfully</div>";
        
        // Select the database
        $conn->exec("USE `{$dbname}`");
    } catch (PDOException $e) {
        echo '<div class="error">❌ Error creating/selecting database: ' . $e->getMessage() . '</div>';
        echo '</div>';
        echo '<p><button onclick="window.location.reload()">Retry</button></p>';
        echo '</body></html>';
        exit;
    }
    
    echo '</div>';
    
    // Step 3: Create or verify tables
    echo '<div class="step info" id="step3"><h2>Step 3: Setting Up Tables</h2>';
    
    try {
        // Create table if it doesn't exist
        $sql = "CREATE TABLE IF NOT EXISTS cours (
            id INT AUTO_INCREMENT PRIMARY KEY,
            course_id VARCHAR(20) NOT NULL UNIQUE,
            title VARCHAR(100) NOT NULL,
            fees DECIMAL(10,2) NOT NULL DEFAULT 0,
            course_link VARCHAR(255) NOT NULL,
            certification_link VARCHAR(255) NOT NULL,
            status VARCHAR(50) NOT NULL DEFAULT 'free',
            icon VARCHAR(10) DEFAULT '📚'
        )";
        
        $conn->exec($sql);
        echo "<div class='success'>✅ Table 'cours' checked/created successfully</div>";
        
        // Check if we need to modify any columns
        $needsAlter = false;
        $columns = $conn->query("DESCRIBE cours");
        
        while ($row = $columns->fetch(PDO::FETCH_ASSOC)) {
            if ($row['Field'] === 'title' && preg_match('/^varchar\((\d+)\)$/i', $row['Type'], $matches) && (int)$matches[1] < 100) {
                $conn->exec("ALTER TABLE cours MODIFY title VARCHAR(100) NOT NULL");
                echo "<div class='info'>Updated 'title' column to VARCHAR(100)</div>";
            }
            
            if ($row['Field'] === 'fees' && stripos($row['Type'], 'decimal') === false) {
                $conn->exec("ALTER TABLE cours MODIFY fees DECIMAL(10,2) NOT NULL DEFAULT 0");
                echo "<div class='info'>Updated 'fees' column to DECIMAL(10,2)</div>";
            }
            
            // Check if icon column exists
            if ($row['Field'] === 'icon' && !isset($hasIconColumn)) {
                $hasIconColumn = true;
            }
        }
        
        // Add icon column if it doesn't exist
        if (!isset($hasIconColumn)) {
            $conn->exec("ALTER TABLE cours ADD COLUMN icon VARCHAR(10) DEFAULT '📚'");
            echo "<div class='info'>Added 'icon' column</div>";
        }
        
        // Check if id column exists and is AUTO_INCREMENT
        $hasIdColumn = false;
        $columns = $conn->query("DESCRIBE cours");
        while ($row = $columns->fetch(PDO::FETCH_ASSOC)) {
            if ($row['Field'] === 'id' && stripos($row['Extra'], 'auto_increment') !== false) {
                $hasIdColumn = true;
                break;
            }
        }
        
        if (!$hasIdColumn) {
            // We need to add an ID column, but must do it carefully if there's data
            $conn->beginTransaction();
            try {
                // First check if there's any data
                $countQuery = $conn->query("SELECT COUNT(*) as total FROM cours");
                $count = $countQuery->fetch(PDO::FETCH_ASSOC)['total'];
                
                if ($count > 0) {
                    // There's data - we need to create a temporary table
                    $conn->exec("CREATE TABLE cours_temp LIKE cours");
                    $conn->exec("ALTER TABLE cours_temp ADD COLUMN id INT AUTO_INCREMENT PRIMARY KEY FIRST");
                    $conn->exec("INSERT INTO cours_temp (course_id, title, fees, course_link, certification_link, status, icon) SELECT course_id, title, fees, course_link, certification_link, status, icon FROM cours");
                    $conn->exec("DROP TABLE cours");
                    $conn->exec("RENAME TABLE cours_temp TO cours");
                    echo "<div class='info'>Added auto-increment 'id' column (with data preservation)</div>";
                } else {
                    // No data - direct alter is fine
                    $conn->exec("ALTER TABLE cours ADD COLUMN id INT AUTO_INCREMENT PRIMARY KEY FIRST");
                    echo "<div class='info'>Added auto-increment 'id' column</div>";
                }
                $conn->commit();
            } catch (PDOException $e) {
                $conn->rollBack();
                echo "<div class='error'>Failed to add 'id' column: " . $e->getMessage() . "</div>";
            }
        }
    } catch (PDOException $e) {
        echo '<div class="error">❌ Error setting up tables: ' . $e->getMessage() . '</div>';
        echo '</div>';
        echo '<p><button onclick="window.location.reload()">Retry</button></p>';
        echo '</body></html>';
        exit;
    }
    
    echo '</div>';
    
    // Step 4: Add sample data if needed
    echo '<div class="step info" id="step4"><h2>Step 4: Adding Sample Data</h2>';
    
    try {
        // Check if table is empty
        $result = $conn->query("SELECT COUNT(*) as count FROM cours");
        $count = $result->fetch(PDO::FETCH_ASSOC)['count'];
        
        if ($count == 0) {
            $sampleData = [
                ['CRS-001', 'Web Development', 99.99, 'https://example.com/web-dev', 'https://example.com/web-cert', 'paid', '🌐'],
                ['CRS-002', 'Python Basics', 0, 'https://example.com/python', 'https://example.com/python-cert', 'free', '🐍'],
                ['CRS-003', 'Data Science', 149.99, 'https://example.com/data-science', 'https://example.com/data-science-cert', 'paid', '📊']
            ];
            
            $stmt = $conn->prepare(
                "INSERT INTO cours (course_id, title, fees, course_link, certification_link, status, icon)
                 VALUES (:course_id, :title, :fees, :course_link, :certification_link, :status, :icon)"
            );
            
            $insertCount = 0;
            foreach ($sampleData as $course) {
                [$cid, $ttl, $fee, $cl, $cert, $st, $icon] = $course;
                if ($stmt->execute([
                    ':course_id' => $cid,
                    ':title' => $ttl,
                    ':fees' => $fee,
                    ':course_link' => $cl,
                    ':certification_link' => $cert,
                    ':status' => $st,
                    ':icon' => $icon
                ])) {
                    $insertCount++;
                }
            }
            
            echo "<div class='success'>✅ Added {$insertCount} sample courses to the database</div>";
        } else {
            echo "<div class='info'>Database already contains {$count} courses. No sample data added.</div>";
        }
    } catch (PDOException $e) {
        echo '<div class="warning">⚠️ Could not add sample data: ' . $e->getMessage() . '</div>';
        // This is not critical, so continue
    }
    
    echo '</div>';
    
    // Final step: Test if we can query the database successfully
    echo '<div class="step info" id="step5"><h2>Step 5: Testing Database Connection</h2>';
    
    try {
        $stmt = $conn->query("SELECT * FROM cours LIMIT 1");
        $courseExists = ($stmt && $stmt->rowCount() > 0);
        
        echo "<div class='success'>✅ Database connection is working properly!</div>";
        if ($courseExists) {
            $course = $stmt->fetch(PDO::FETCH_ASSOC);
            echo "<div class='info'>Sample course data: ID: {$course['course_id']}, Title: {$course['title']}</div>";
        }
    } catch (PDOException $e) {
        echo '<div class="error">❌ Error testing database: ' . $e->getMessage() . '</div>';
    }
    
    echo '</div>';
    
    // Close connection
    $conn = null;
    ?>
    
    <div class="step success">
        <h2>Setup Complete!</h2>
        <p>Your database is now configured and ready to use.</p>
        <p>
            <a href="../test_db_connection.php"><button>Test Connection</button></a>
            <a href="../view/dashboard.html"><button style="background-color: #007bff;">Go to Dashboard</button></a>
            <a href="../../front_office/front_office/view/courses.html"><button style="background-color: #6c757d;">Go to Front Office</button></a>
        </p>
    </div>
    
    <script>
    // Add a simple animation to make steps appear sequentially
    document.addEventListener('DOMContentLoaded', function() {
        const steps = document.querySelectorAll('.step');
        steps.forEach((step, index) => {
            setTimeout(() => {
                step.style.display = 'block';
                step.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
            }, index * 500);
        });
    });
    </script>
</body>
</html>