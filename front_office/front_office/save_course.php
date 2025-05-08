<?php
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Set headers
header('Content-Type: application/json');

// Log incoming request
file_put_contents("course_log.txt", "Request received: " . date('Y-m-d H:i:s') . "\n", FILE_APPEND);
file_put_contents("course_log.txt", "POST data: " . print_r($_POST, true) . "\n", FILE_APPEND);

// Get form data
$course_id = $_POST['courseId'] ?? '';
$title = $_POST['courseTitle'] ?? '';
$fees = $_POST['courseFees'] ?? '0';
$course_link = $_POST['courseLink'] ?? '';
$certification_link = $_POST['courseCertification'] ?? '';
$status = $_POST['courseStatus'] ?? '';

// Convert fees to integer if it's "Free"
if ($fees === "Free" || empty($fees)) {
    $fees = 0;
}

// Log processed data
file_put_contents("course_log.txt", "Processed data: course_id=$course_id, title=$title, fees=$fees, status=$status\n", FILE_APPEND);

// Validate required fields
if (empty($course_id) || empty($title) || empty($status)) {
    $response = [
        'success' => false,
        'message' => 'Missing required fields. Please fill in all required fields.'
    ];
    echo json_encode($response);
    file_put_contents("course_log.txt", "Validation error: " . json_encode($response) . "\n", FILE_APPEND);
    exit;
}

try {
    // Database connection
    $db_host = 'localhost';
    $db_name = 'aziz';
    $db_user = 'root';
    $db_pass = '';
    
    // Create PDO connection
    $dsn = "mysql:host={$db_host};dbname={$db_name};charset=utf8mb4";
    $conn = new PDO(
        $dsn,
        $db_user,
        $db_pass,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
    
    file_put_contents("course_log.txt", "Database connected successfully\n", FILE_APPEND);
    
    // Prepare SQL statement
    $sql = "INSERT INTO cours (course_id, title, fees, course_link, certification_link, status) 
            VALUES (:course_id, :title, :fees, :course_link, :certification_link, :status)";
    
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':course_id', $course_id);
    $stmt->bindParam(':title', $title);
    $stmt->bindParam(':fees', $fees);
    $stmt->bindParam(':course_link', $course_link);
    $stmt->bindParam(':certification_link', $certification_link);
    $stmt->bindParam(':status', $status);
    
    // Execute statement
    if ($stmt->execute()) {
        $response = [
            'success' => true,
            'message' => 'Course added successfully!'
        ];
        file_put_contents("course_log.txt", "Insert successful\n", FILE_APPEND);
    } else {
        throw new Exception("Execute failed: " . implode(", ", $stmt->errorInfo()));
    }
    
} catch (Exception $e) {
    $response = [
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage()
    ];
    file_put_contents("course_log.txt", "Error: " . $e->getMessage() . "\n", FILE_APPEND);
}

// Send response
echo json_encode($response);
file_put_contents("course_log.txt", "Response sent: " . json_encode($response) . "\n\n", FILE_APPEND);
?>
