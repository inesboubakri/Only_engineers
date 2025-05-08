<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Include the database configuration
require_once 'model/config.php';

// Get connection status
$connectionTest = testDatabaseConnection();
$isConnected = $connectionTest['connected'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Database Connection Test</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            max-width: 800px;
            margin: 20px auto;
            padding: 20px;
            background-color: #f4f6f9;
        }
        .container {
            background-color: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .status {
            margin: 20px 0;
            padding: 15px;
            border-radius: 4px;
        }
        .success {
            background-color: #d4edda;
            color: #155724;
            border-left: 5px solid #28a745;
        }
        .error {
            background-color: #f8d7da;
            color: #721c24;
            border-left: 5px solid #dc3545;
        }
        h1 {
            color: #333;
            border-bottom: 1px solid #ddd;
            padding-bottom: 10px;
        }
        button, .btn {
            display: inline-block;
            padding: 10px 15px;
            background-color: #007bff;
            color: white;
            border: none;
            border-radius: 4px;
            text-decoration: none;
            cursor: pointer;
            font-size: 16px;
            margin-right: 10px;
        }
        .btn-green {
            background-color: #28a745;
        }
        .btn-red {
            background-color: #dc3545;
        }
        .details {
            background-color: #f8f9fa;
            padding: 15px;
            border-radius: 4px;
            margin: 20px 0;
        }
        pre {
            margin: 0;
            white-space: pre-wrap;
            word-wrap: break-word;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Database Connection Test</h1>
        
        <div class="status <?php echo $isConnected ? 'success' : 'error'; ?>">
            <h2><?php echo $isConnected ? '✅ Connection Successful' : '❌ Connection Failed'; ?></h2>
            <p><?php echo $connectionTest['message']; ?></p>
        </div>
        
        <?php if ($isConnected): ?>
        <div class="details">
            <h3>Connection Details:</h3>
            <ul>
                <li><strong>Server:</strong> localhost</li>
                <li><strong>Database:</strong> aziz</li>
                <li><strong>PDO Driver:</strong> <?php echo extension_loaded('pdo_mysql') ? 'Installed ✓' : 'Not Installed ✗'; ?></li>
            </ul>
            
            <?php
            // Test if we can actually query some data
            try {
                $sql = "SELECT COUNT(*) as count FROM cours";
                $stmt = $conn->query($sql);
                $result = $stmt->fetch(PDO::FETCH_ASSOC);
                echo "<p>Found <strong>{$result['count']}</strong> courses in the database.</p>";
                
                if ($result['count'] > 0) {
                    // Get a sample
                    $sampleQuery = "SELECT * FROM cours LIMIT 1";
                    $stmt = $conn->query($sampleQuery);
                    $sample = $stmt->fetch(PDO::FETCH_ASSOC);
                    
                    echo "<h3>Sample Record:</h3>";
                    echo "<pre>";
                    print_r($sample);
                    echo "</pre>";
                }
            } catch (PDOException $e) {
                echo "<p class='error'>Error querying database: " . $e->getMessage() . "</p>";
            }
            ?>
        </div>
        <?php else: ?>
        <div class="details">
            <h3>Troubleshooting:</h3>
            <ol>
                <li>Make sure MySQL is running in XAMPP Control Panel</li>
                <li>Check that the database credentials in model/config.php are correct</li>
                <li>Ensure the database "aziz" exists (you can run the setup wizard)</li>
                <li>Verify that PDO extension is enabled in php.ini</li>
            </ol>
        </div>
        <?php endif; ?>
        
        <div class="actions">
            <a href="model/setup_database.php" class="btn btn-green">Run Setup Wizard</a>
            <a href="view/dashboard.html" class="btn">Go to Dashboard</a>
            <button onclick="window.location.reload()" class="btn">Retry Connection</button>
        </div>
    </div>
</body>
</html>