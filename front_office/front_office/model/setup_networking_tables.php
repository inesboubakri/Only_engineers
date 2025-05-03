<?php
// Include database connection
require_once __DIR__ . '/db_connection.php';

try {
    $conn = getConnection();
    
    // Check if connections table exists
    $tableCheck = $conn->query("SHOW TABLES LIKE 'connections'");
    if ($tableCheck->rowCount() == 0) {
        // Create connections table
        $createConnectionsTable = "CREATE TABLE connections (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NOT NULL,
            connected_user_id INT NOT NULL,
            status ENUM('pending', 'accepted', 'rejected') NOT NULL DEFAULT 'pending',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            UNIQUE KEY unique_connection (user_id, connected_user_id),
            FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE,
            FOREIGN KEY (connected_user_id) REFERENCES users(user_id) ON DELETE CASCADE
        )";
        
        $conn->exec($createConnectionsTable);
        echo "Connections table created successfully.<br>";
    } else {
        echo "Connections table already exists.<br>";
    }
    
    // Check if follows table exists
    $tableCheck = $conn->query("SHOW TABLES LIKE 'follows'");
    if ($tableCheck->rowCount() == 0) {
        // Create follows table
        $createFollowsTable = "CREATE TABLE follows (
            id INT AUTO_INCREMENT PRIMARY KEY,
            follower_id INT NOT NULL,
            following_id INT NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            UNIQUE KEY unique_follow (follower_id, following_id),
            FOREIGN KEY (follower_id) REFERENCES users(user_id) ON DELETE CASCADE,
            FOREIGN KEY (following_id) REFERENCES users(user_id) ON DELETE CASCADE
        )";
        
        $conn->exec($createFollowsTable);
        echo "Follows table created successfully.<br>";
    } else {
        echo "Follows table already exists.<br>";
    }
    
    // Check if notifications table exists
    $tableCheck = $conn->query("SHOW TABLES LIKE 'notifications'");
    if ($tableCheck->rowCount() == 0) {
        // Create notifications table
        $createNotificationsTable = "CREATE TABLE notifications (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NOT NULL,
            from_user_id INT NULL,
            type VARCHAR(50) NOT NULL,
            message TEXT NOT NULL,
            link VARCHAR(255) NULL,
            is_read TINYINT(1) NOT NULL DEFAULT 0,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE,
            FOREIGN KEY (from_user_id) REFERENCES users(user_id) ON DELETE SET NULL
        )";
        
        $conn->exec($createNotificationsTable);
        echo "Notifications table created successfully.<br>";
    } else {
        echo "Notifications table already exists.<br>";
    }
    
    echo "Networking tables setup complete.";
    
} catch (PDOException $e) {
    echo "Database Error: " . $e->getMessage();
}
?>