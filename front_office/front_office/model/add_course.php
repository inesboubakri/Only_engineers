<?php
// Set headers for JSON response
header('Content-Type: application/json');

// Get the form data
$course_id = isset($_POST['courseId']) ? $_POST['courseId'] : '';
$title = isset($_POST['courseTitle']) ? $_POST['courseTitle'] : '';
$fees = isset($_POST['courseFees']) ? $_POST['courseFees'] : 0;
$course_link = isset($_POST['courseLink']) ? $_POST['courseLink'] : '';
$certification_link = isset($_POST['courseCertification']) ? $_POST['courseCertification'] : '';
$status = isset($_POST['courseStatus']) ? $_POST['courseStatus'] : '';

// Convert fees to integer if it's "Free"
if ($fees === "Free" || empty($fees)) {
    $fees = 0;
}

// Validate required fields
if (empty($course_id) || empty($title) || empty($status)) {
    echo json_encode([
        'success' => false,
        'message' => 'Missing required fields'
    ]);
    exit;
}

try {
    // Connect to database
    $db = new PDO('mysql:host=localhost;dbname=aziz', 'root', '');
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Prepare the insert statement
    $stmt = $db->prepare("INSERT INTO cours (course_id, title, fees, course_link, certification_link, status) 
                         VALUES (:course_id, :title, :fees, :course_link, :certification_link, :status)");
    
    // Bind parameters
    $stmt->bindParam(':course_id', $course_id);
    $stmt->bindParam(':title', $title);
    $stmt->bindParam(':fees', $fees);
    $stmt->bindParam(':course_link', $course_link);
    $stmt->bindParam(':certification_link', $certification_link);
    $stmt->bindParam(':status', $status);
    
    // Execute the statement
    $stmt->execute();
    
    // Return success response
    echo json_encode([
        'success' => true,
        'message' => 'Course added successfully!'
    ]);
    
} catch (PDOException $e) {
    // Return error response
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage()
    ]);
}
?>
