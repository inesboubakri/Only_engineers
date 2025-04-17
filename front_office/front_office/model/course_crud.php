<?php
// Ensure no output is sent before our JSON response
ob_start();

// Configure error reporting - log to file instead of output
error_reporting(E_ALL);
ini_set('display_errors', 0); // Turn off display of errors
ini_set('log_errors', 1);
ini_set('error_log', 'php_errors.log');

// Database connection
function getConnection() {
    $servername = "localhost";
    $username = "root";
    $password = "";
    $dbname = "aziz"; // Your database name

    // Create PDO connection
    try {
        $dsn = "mysql:host={$servername};dbname={$dbname};charset=utf8mb4";
        $conn = new PDO(
            $dsn,
            $username,
            $password,
            [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
        );
    } catch (PDOException $e) {
        die('Connection failed: ' . $e->getMessage());
    }
    
    return $conn;
}

// Function to safely output JSON
function outputJSON($data) {
    // Clear any buffered output
    ob_clean();
    
    // Set JSON header
    header('Content-Type: application/json');
    
    // Output JSON
    echo json_encode($data);
    exit;
}

// Handle CRUD operations based on 'operation' parameter
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get operation type
    $operation = isset($_POST['operation']) ? $_POST['operation'] : '';
    
    // Log received data for debugging
    $logFile = 'debug_log.txt';
    $logData = date('Y-m-d H:i:s') . " - Operation: $operation - POST data: " . print_r($_POST, true) . "\n";
    file_put_contents($logFile, $logData, FILE_APPEND);
    
    try {
        switch ($operation) {
            case 'create':
                createCourse();
                break;
            case 'read':
                readCourses();
                break;
            case 'readOne':
                readOneCourse();
                break;
            case 'update':
                updateCourse();
                break;
            case 'delete':
                deleteCourse();
                break;
            default:
                outputJSON(["success" => false, "message" => "Invalid operation"]);
                break;
        }
    } catch (Exception $e) {
        error_log("Exception: " . $e->getMessage());
        outputJSON(["success" => false, "message" => "Server error: " . $e->getMessage()]);
    }
} else {
    outputJSON(["success" => false, "message" => "Invalid request method"]);
}

// Create a new course
function createCourse() {
    $response = ["success" => false, "message" => ""];
    
    // Validate required fields
    if (!isset($_POST['courseId']) || !isset($_POST['courseTitle']) || !isset($_POST['courseStatus'])) {
        $response["message"] = "Missing required fields";
        outputJSON($response);
    }
    
    try {
        // Log the entire POST array for debugging
        error_log("Create course POST data: " . print_r($_POST, true));
        
        $course_id = $_POST['courseId'];
        $title = $_POST['courseTitle'];
        $fees = isset($_POST['courseFees']) ? (($_POST['courseFees'] === 'Free' || empty($_POST['courseFees'])) ? 0 : floatval(str_replace('$', '', $_POST['courseFees']))) : 0;
        $course_link = isset($_POST['courseLink']) ? $_POST['courseLink'] : '';
        $certification_link = isset($_POST['courseCertification']) ? $_POST['courseCertification'] : '';
        $status = $_POST['courseStatus'];
        $icon = isset($_POST['courseIcon']) ? $_POST['courseIcon'] : '📚';
        
        // Get database connection
        $conn = getConnection();
        
        // Check if course ID already exists using direct query
        $checkIdSQL = "SELECT course_id FROM cours WHERE course_id = :course_id";
        $stmt = $conn->prepare($checkIdSQL);
        $stmt->bindParam(':course_id', $course_id);
        $stmt->execute();
        
        if ($stmt->rowCount() > 0) {
            outputJSON([
                "success" => false,
                "message" => "Course ID already exists"
            ]);
        }
        
        // Insert new course using direct query with escaping
        $insertSQL = "INSERT INTO cours (course_id, title, fees, course_link, certification_link, status, icon) VALUES (:course_id, :title, :fees, :course_link, :certification_link, :status, :icon)";
        $stmt = $conn->prepare($insertSQL);
        $stmt->bindParam(':course_id', $course_id);
        $stmt->bindParam(':title', $title);
        $stmt->bindParam(':fees', $fees);
        $stmt->bindParam(':course_link', $course_link);
        $stmt->bindParam(':certification_link', $certification_link);
        $stmt->bindParam(':status', $status);
        $stmt->bindParam(':icon', $icon);
        
        $result = $stmt->execute();
        
        if (!$result) {
            $errorInfo = $stmt->errorInfo();
            error_log("SQL Error: " . print_r($errorInfo, true));
            outputJSON([
                "success" => false,
                "message" => "Error inserting course: " . $errorInfo[2],
                "sql" => $insertSQL
            ]);
        }
        
        outputJSON([
            "success" => true,
            "message" => "Course added successfully"
        ]);
    } catch (Exception $e) {
        error_log("Create course error: " . $e->getMessage());
        outputJSON(["success" => false, "message" => "Server error: " . $e->getMessage()]);
    }
}

// Read all courses
function readCourses() {
    try {
        $conn = getConnection();
        
        $sql = "SELECT * FROM cours ORDER BY course_id";
        $stmt = $conn->query($sql);
        
        if (!$stmt) {
            outputJSON(["success" => false, "message" => "Database error"]);
        }
        
        $courses = $stmt->fetchAll(PDO::FETCH_ASSOC);
        if ($courses) {
            outputJSON(["success" => true, "courses" => $courses]);
        } else {
            outputJSON(["success" => true, "courses" => [], "message" => "No courses found"]);
        }
    } catch (Exception $e) {
        error_log("Read courses error: " . $e->getMessage());
        outputJSON(["success" => false, "message" => "Server error: " . $e->getMessage()]);
    }
}

// Read one course by ID
function readOneCourse() {
    try {
        if (!isset($_POST['courseId'])) {
            outputJSON(["success" => false, "message" => "Course ID is required"]);
        }
        
        $course_id = $_POST['courseId'];
        
        $conn = getConnection();
        
        $stmt = $conn->prepare("SELECT * FROM cours WHERE course_id = :course_id");
        $stmt->bindParam(':course_id', $course_id);
        $stmt->execute();
        
        $course = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($course) {
            outputJSON(["success" => true, "course" => $course]);
        } else {
            outputJSON(["success" => false, "message" => "Course not found"]);
        }
    } catch (Exception $e) {
        error_log("Read one course error: " . $e->getMessage());
        outputJSON(["success" => false, "message" => "Server error: " . $e->getMessage()]);
    }
}

// Update a course
function updateCourse() {
    try {
        if (!isset($_POST['courseId'])) {
            outputJSON(["success" => false, "message" => "Course ID is required"]);
        }
        
        $course_id = $_POST['courseId'];
        $title = isset($_POST['courseTitle']) ? $_POST['courseTitle'] : '';
        $fees = ($_POST['courseFees'] === 'Free' || empty($_POST['courseFees'])) ? 0 : floatval(str_replace('$', '', $_POST['courseFees']));
        $course_link = isset($_POST['courseLink']) ? $_POST['courseLink'] : '';
        $certification_link = isset($_POST['courseCertification']) ? $_POST['courseCertification'] : '';
        $status = isset($_POST['courseStatus']) ? $_POST['courseStatus'] : '';
        $icon = isset($_POST['courseIcon']) ? $_POST['courseIcon'] : '📚';
        
        // Log all values for debugging
        error_log("Update Course - ID: $course_id, Title: $title, Fees: $fees, Status: $status");
        
        $conn = getConnection();
        
        // Check if the course exists
        $stmt = $conn->prepare("SELECT course_id FROM cours WHERE course_id = :course_id");
        $stmt->bindParam(':course_id', $course_id);
        $stmt->execute();
        
        if ($stmt->rowCount() === 0) {
            outputJSON(["success" => false, "message" => "Course not found"]);
        }
        
        // Update the course
        $stmt = $conn->prepare("UPDATE cours SET title = :title, fees = :fees, course_link = :course_link, certification_link = :certification_link, status = :status, icon = :icon WHERE course_id = :course_id");
        $stmt->bindParam(':title', $title);
        $stmt->bindParam(':fees', $fees);
        $stmt->bindParam(':course_link', $course_link);
        $stmt->bindParam(':certification_link', $certification_link);
        $stmt->bindParam(':status', $status);
        $stmt->bindParam(':icon', $icon);
        $stmt->bindParam(':course_id', $course_id);
        
        if ($stmt->execute()) {
            outputJSON(["success" => true, "message" => "Course updated successfully"]);
        } else {
            error_log("SQL Error in updateCourse");
            outputJSON(["success" => false, "message" => "Error updating course"]);
        }
    } catch (Exception $e) {
        error_log("Update course error: " . $e->getMessage());
        outputJSON(["success" => false, "message" => "Server error: " . $e->getMessage()]);
    }
}

// Delete a course
function deleteCourse() {
    try {
        if (!isset($_POST['courseId'])) {
            outputJSON(["success" => false, "message" => "Course ID is required"]);
        }
        
        $course_id = $_POST['courseId'];
        
        $conn = getConnection();
        
        // Check if the course exists
        $stmt = $conn->prepare("SELECT course_id FROM cours WHERE course_id = :course_id");
        $stmt->bindParam(':course_id', $course_id);
        $stmt->execute();
        
        if ($stmt->rowCount() === 0) {
            outputJSON(["success" => false, "message" => "Course not found"]);
        }
        
        // Delete the course
        $stmt = $conn->prepare("DELETE FROM cours WHERE course_id = :course_id");
        $stmt->bindParam(':course_id', $course_id);
        
        if ($stmt->execute()) {
            outputJSON(["success" => true, "message" => "Course deleted successfully"]);
        } else {
            outputJSON(["success" => false, "message" => "Error deleting course"]);
        }
    } catch (Exception $e) {
        error_log("Delete course error: " . $e->getMessage());
        outputJSON(["success" => false, "message" => "Server error: " . $e->getMessage()]);
    }
}
?>
