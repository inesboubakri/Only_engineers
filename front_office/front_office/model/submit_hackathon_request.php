<?php
/**
 * Submit Hackathon Request
 * Handles submission of hackathon requests from the front office
 */

// Start session
session_start();

// Make sure we send JSON content type
header('Content-Type: application/json');

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode([
        'success' => false,
        'message' => 'You must be logged in to submit a hackathon request'
    ]);
    exit();
}

// Get the user ID from the session
$userId = $_SESSION['user_id'];

// Include database connection
require_once 'db_connection.php';

try {
    // Get database connection
    $conn = getConnection();
    
    if (!$conn) {
        throw new Exception("Failed to connect to database");
    }
    
    // First, verify if the user exists - IMPORTANT for the foreign key constraint
    $userCheckStmt = $conn->prepare("SELECT user_id FROM users WHERE user_id = :user_id");
    $userCheckStmt->bindParam(':user_id', $userId);
    $userCheckStmt->execute();
    
    if ($userCheckStmt->rowCount() === 0) {
        // User doesn't exist - this is why we're getting the foreign key constraint error
        echo json_encode([
            'success' => false,
            'message' => 'User account not found. Please log out and log in again.'
        ]);
        exit();
    }
    
    // Fetch the actual user ID from the database to ensure it matches exactly
    $userRow = $userCheckStmt->fetch(PDO::FETCH_ASSOC);
    $confirmedUserId = $userRow['user_id']; // Use this instead of session ID
    
    // Check if the hackathon_requests table exists
    $tableCheckSql = "SHOW TABLES LIKE 'hackathon_requests'";
    $tableCheckStmt = $conn->prepare($tableCheckSql);
    $tableCheckStmt->execute();
    
    // If hackathon_requests table doesn't exist, create it
    if ($tableCheckStmt->rowCount() == 0) {
        $createTableSql = "CREATE TABLE hackathon_requests (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(255) NOT NULL,
            description TEXT NOT NULL,
            start_date DATE NOT NULL,
            end_date DATE NOT NULL,
            start_time TIME NOT NULL,
            end_time TIME NOT NULL,
            location VARCHAR(255) NOT NULL,
            latitude DECIMAL(10,8) NULL,
            longitude DECIMAL(11,8) NULL,
            required_skills VARCHAR(255) NOT NULL,
            organizer VARCHAR(255) NOT NULL,
            max_participants INT NOT NULL,
            image VARCHAR(255) NOT NULL,
            user_id INT NOT NULL,
            submitted_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE
        )";
        $conn->exec($createTableSql);
    } else {
        // Check if the latitude and longitude columns exist in the table
        $columnCheckSql = "SHOW COLUMNS FROM hackathon_requests LIKE 'latitude'";
        $columnCheckStmt = $conn->prepare($columnCheckSql);
        $columnCheckStmt->execute();
        
        // If the columns don't exist, add them
        if ($columnCheckStmt->rowCount() == 0) {
            $alterTableSql = "ALTER TABLE hackathon_requests 
                ADD COLUMN latitude DECIMAL(10,8) NULL AFTER location,
                ADD COLUMN longitude DECIMAL(11,8) NULL AFTER latitude";
            $conn->exec($alterTableSql);
        }
    }
    
    // Basic validation
    $requiredFields = ['name', 'description', 'start_date', 'end_date', 'start_time', 'end_time', 
                      'location', 'required_skills', 'organizer', 'max_participants'];
    
    foreach ($requiredFields as $field) {
        if (!isset($_POST[$field]) || empty($_POST[$field])) {
            throw new Exception("Missing required field: $field");
        }
    }
    
    // Validate name (not pure numbers or symbols)
    $name = $_POST['name'];
    if (!preg_match('/[a-zA-Z]/', $name) || preg_match('/^\d+$/', $name)) {
        throw new Exception("Hackathon name cannot be pure numbers or symbols");
    }
    
    // Validate description (10-255 words)
    $description = $_POST['description'];
    $wordCount = count(array_filter(explode(' ', $description)));
    if ($wordCount < 10) {
        throw new Exception("Description must contain at least 10 words");
    } else if ($wordCount > 255) {
        throw new Exception("Description cannot exceed 255 words");
    }
    
    // Validate dates and times
    $startDate = $_POST['start_date'];
    $endDate = $_POST['end_date'];
    $startTime = $_POST['start_time'];
    $endTime = $_POST['end_time'];
    
    $startDateTime = new DateTime("$startDate $startTime");
    $endDateTime = new DateTime("$endDate $endTime");
    
    if ($startDateTime >= $endDateTime) {
        throw new Exception("End time must be after start time");
    }
    
    // Validate location
    $location = $_POST['location'];
    if (!preg_match('/[a-zA-Z]/', $location) || preg_match('/^\d+$/', $location)) {
        throw new Exception("Location cannot be pure numbers or symbols");
    }
    
    // Validate max participants
    $maxParticipants = $_POST['max_participants'];
    if (!is_numeric($maxParticipants) || $maxParticipants <= 0) {
        throw new Exception("Maximum participants must be a positive number");
    }
    
    // Handle image upload
    $uploadDir = __DIR__ . '/../ressources/hackathon_images/';
    $imagePath = null;
    
    // Make sure the upload directory exists
    if (!file_exists($uploadDir)) {
        mkdir($uploadDir, 0777, true);
        error_log("Created directory: " . $uploadDir);
    }
    
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $filename = 'hackathon_' . uniqid() . '_' . basename($_FILES['image']['name']);
        $uploadFile = $uploadDir . $filename;
        
        // Log for debugging
        error_log("Upload path: " . $uploadFile);
        
        if (move_uploaded_file($_FILES['image']['tmp_name'], $uploadFile)) {
            $imagePath = 'hackathon_images/' . $filename;
            error_log("File uploaded successfully to: " . $uploadFile);
        } else {
            throw new Exception("Failed to upload image: " . error_get_last()['message']);
        }
    } else {
        throw new Exception("Hackathon image is required");
    }
    
    try {
        // Begin transaction to ensure database consistency
        $conn->beginTransaction();
        
        // Insert the hackathon request into the hackathon_requests table instead of hackathons
        $sql = "INSERT INTO hackathon_requests (name, description, start_date, end_date, start_time, end_time, 
                location, latitude, longitude, required_skills, organizer, max_participants, image, user_id)
                VALUES (:name, :description, :start_date, :end_date, :start_time, :end_time, 
                :location, :latitude, :longitude, :required_skills, :organizer, :max_participants, :image, :user_id)";
        
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':name', $_POST['name']);
        $stmt->bindParam(':description', $_POST['description']);
        $stmt->bindParam(':start_date', $_POST['start_date']);
        $stmt->bindParam(':end_date', $_POST['end_date']);
        $stmt->bindParam(':start_time', $_POST['start_time']);
        $stmt->bindParam(':end_time', $_POST['end_time']);
        $stmt->bindParam(':location', $_POST['location']);
        
        // Bind latitude and longitude if they exist
        if (isset($_POST['latitude']) && isset($_POST['longitude'])) {
            $stmt->bindParam(':latitude', $_POST['latitude']);
            $stmt->bindParam(':longitude', $_POST['longitude']);
        } else {
            $nullValue = null;
            $stmt->bindParam(':latitude', $nullValue, PDO::PARAM_NULL);
            $stmt->bindParam(':longitude', $nullValue, PDO::PARAM_NULL);
        }
        
        $stmt->bindParam(':required_skills', $_POST['required_skills']);
        $stmt->bindParam(':organizer', $_POST['organizer']);
        $stmt->bindParam(':max_participants', $_POST['max_participants']);
        $stmt->bindParam(':image', $imagePath);
        $stmt->bindParam(':user_id', $confirmedUserId); // Use the confirmed user ID
        
        $stmt->execute();
        
        // Commit the transaction
        $conn->commit();
        
        echo json_encode([
            'success' => true, 
            'message' => 'Hackathon request submitted successfully! It will be reviewed by an administrator.'
        ]);
    } catch (PDOException $innerEx) {
        // Rollback the transaction on error
        if ($conn->inTransaction()) {
            $conn->rollBack();
        }
        
        // Check for foreign key constraint violation specifically
        if ($innerEx->getCode() == 23000) {
            echo json_encode([
                'success' => false,
                'message' => 'User validation failed. Please log out and log in again before submitting.'
            ]);
        } else {
            throw $innerEx; // Re-throw to be caught by the outer catch block
        }
    }
    
} catch(PDOException $e) {
    // Handle PDO exceptions specifically to get more detailed error information
    echo json_encode([
        'success' => false,
        'message' => "Database error: " . $e->getMessage()
    ]);
} catch(Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>