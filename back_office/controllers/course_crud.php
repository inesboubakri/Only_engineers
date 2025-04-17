<?php
require_once '../model/config.php';

// Set header to return JSON
header('Content-Type: application/json');

// Log incoming requests for debugging
error_log('Received CRUD request: ' . json_encode($_POST));

// Get the action from request
$action = isset($_POST['action']) ? $_POST['action'] : '';

// Verify that database connection is available
if (!$conn || !($conn instanceof PDO)) {
    error_log('Database connection is not available');
    echo json_encode(['status' => 'error', 'message' => 'Database connection failed']);
    exit;
}

switch ($action) {
    case 'create':
        createCourse();
        break;
    case 'read':
        readCourses();
        break;
    case 'update':
        updateCourse();
        break;
    case 'delete':
        deleteCourse();
        break;
    default:
        echo json_encode(['status' => 'error', 'message' => 'Invalid action']);
}

function createCourse() {
    global $conn;
    
    try {
        // Ensure the course_id has a value
        $course_id = isset($_POST['courseId']) ? $_POST['courseId'] : '';
        if (empty($course_id)) {
            // Generate a new course ID if not provided
            $course_id = generateCourseId();
        }
        
        // Get other form values
        $title = isset($_POST['courseTitle']) ? $_POST['courseTitle'] : '';
        if (empty($title)) {
            echo json_encode(['status' => 'error', 'message' => 'Course title cannot be empty']);
            return;
        }
        
        $fees = isset($_POST['courseFees']) ? (is_numeric(str_replace('$', '', $_POST['courseFees'])) ? str_replace('$', '', $_POST['courseFees']) : 0) : 0;
        $course_link = isset($_POST['courseLink']) ? $_POST['courseLink'] : '#';
        $certification_link = isset($_POST['courseCertification']) ? $_POST['courseCertification'] : '#';
        $status = isset($_POST['courseStatus']) ? $_POST['courseStatus'] : 'free';
        $icon = isset($_POST['courseIcon']) ? $_POST['courseIcon'] : '📚';
        
        // Log the values being inserted
        error_log("Inserting course: ID=$course_id, Title=$title, Fees=$fees, Status=$status");
        
        // Using PDO now
        $sql = "INSERT INTO cours (course_id, title, fees, course_link, certification_link, status, icon) 
                VALUES (:course_id, :title, :fees, :course_link, :certification_link, :status, :icon)";
                
        $stmt = $conn->prepare($sql);
        
        $stmt->bindParam(':course_id', $course_id);
        $stmt->bindParam(':title', $title);
        $stmt->bindParam(':fees', $fees);
        $stmt->bindParam(':course_link', $course_link);
        $stmt->bindParam(':certification_link', $certification_link);
        $stmt->bindParam(':status', $status);
        $stmt->bindParam(':icon', $icon);
        
        if ($stmt->execute()) {
            echo json_encode([
                'status' => 'success', 
                'message' => 'Course added successfully',
                'data' => [
                    'course_id' => $course_id,
                    'title' => $title,
                    'fees' => $fees,
                    'course_link' => $course_link,
                    'certification_link' => $certification_link,
                    'status' => $status,
                    'icon' => $icon
                ]
            ]);
        } else {
            error_log("Insert error: " . json_encode($stmt->errorInfo()));
            echo json_encode(['status' => 'error', 'message' => 'Error: ' . $stmt->errorInfo()[2]]);
        }
    } catch (PDOException $e) {
        error_log("PDO Exception in createCourse: " . $e->getMessage());
        echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $e->getMessage()]);
    }
}

// Function to generate a unique course ID
function generateCourseId() {
    global $conn;
    
    try {
        // Get the latest course ID number from the database
        $sql = "SELECT course_id FROM cours WHERE course_id LIKE 'CRS-%' ORDER BY CAST(SUBSTRING(course_id, 5) AS UNSIGNED) DESC LIMIT 1";
        $stmt = $conn->query($sql);
        
        if ($stmt && $stmt->rowCount() > 0) {
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            $lastId = $row['course_id'];
            
            // Extract the number part and increment
            $matches = [];
            if (preg_match('/CRS-(\d+)/', $lastId, $matches)) {
                $nextNum = intval($matches[1]) + 1;
                return 'CRS-' . str_pad($nextNum, 3, '0', STR_PAD_LEFT);
            }
        }
    } catch (PDOException $e) {
        error_log("Error generating course ID: " . $e->getMessage());
    }
    
    // If no existing course IDs or couldn't parse, start with CRS-001
    return 'CRS-001';
}

function readCourses() {
    global $conn;
    
    try {
        $sql = "SELECT * FROM cours";
        $stmt = $conn->query($sql);
        
        $courses = [];
        if ($stmt && $stmt->rowCount() > 0) {
            $courses = $stmt->fetchAll(PDO::FETCH_ASSOC);
        }
        
        echo json_encode(['status' => 'success', 'data' => $courses]);
    } catch (PDOException $e) {
        error_log("PDO Exception in readCourses: " . $e->getMessage());
        echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $e->getMessage()]);
    }
}

function updateCourse() {
    global $conn;
    
    try {
        $course_id = isset($_POST['courseId']) ? $_POST['courseId'] : '';
        $title = isset($_POST['courseTitle']) ? $_POST['courseTitle'] : '';
        $fees = isset($_POST['courseFees']) ? (is_numeric(str_replace('$', '', $_POST['courseFees'])) ? str_replace('$', '', $_POST['courseFees']) : 0) : 0;
        $course_link = isset($_POST['courseLink']) ? $_POST['courseLink'] : '#';
        $certification_link = isset($_POST['courseCertification']) ? $_POST['courseCertification'] : '#';
        $status = isset($_POST['courseStatus']) ? $_POST['courseStatus'] : 'free';
        $icon = isset($_POST['courseIcon']) ? $_POST['courseIcon'] : '📚';
        
        // Log received data
        error_log("Updating course ID: $course_id with title: $title");
        
        // Using PDO now
        $sql = "UPDATE cours SET title = :title, fees = :fees, course_link = :course_link, 
                certification_link = :certification_link, status = :status, icon = :icon 
                WHERE course_id = :course_id";
                
        $stmt = $conn->prepare($sql);
        if (!$stmt) {
            error_log("SQL prepare error: " . json_encode($conn->errorInfo()));
            echo json_encode(['status' => 'error', 'message' => 'Database prepare error']);
            return;
        }
        
        $stmt->bindParam(':title', $title);
        $stmt->bindParam(':fees', $fees);
        $stmt->bindParam(':course_link', $course_link);
        $stmt->bindParam(':certification_link', $certification_link);
        $stmt->bindParam(':status', $status);
        $stmt->bindParam(':icon', $icon);
        $stmt->bindParam(':course_id', $course_id);
        
        // Execute update and check results
        if ($stmt->execute()) {
            // Check if any rows were actually affected
            if ($stmt->rowCount() > 0) {
                error_log("Course updated successfully: $course_id");
                echo json_encode([
                    'status' => 'success', 
                    'message' => 'Course updated successfully',
                    'affected_rows' => $stmt->rowCount()
                ]);
            } else {
                error_log("Course update - no changes or ID not found: $course_id");
                echo json_encode([
                    'status' => 'success', 
                    'message' => 'No changes were made or course ID not found',
                    'affected_rows' => 0
                ]);
            }
        } else {
            error_log("Course update error: " . json_encode($stmt->errorInfo()));
            echo json_encode(['status' => 'error', 'message' => 'Error: ' . $stmt->errorInfo()[2]]);
        }
    } catch (PDOException $e) {
        error_log("PDO Exception in updateCourse: " . $e->getMessage());
        echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $e->getMessage()]);
    }
}

function deleteCourse() {
    global $conn;
    
    try {
        $course_id = isset($_POST['courseId']) ? $_POST['courseId'] : '';
        
        if (empty($course_id)) {
            echo json_encode(['status' => 'error', 'message' => 'Course ID is required']);
            return;
        }
        
        $sql = "DELETE FROM cours WHERE course_id = :course_id";
        
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':course_id', $course_id);
        
        if ($stmt->execute()) {
            if ($stmt->rowCount() > 0) {
                echo json_encode(['status' => 'success', 'message' => 'Course deleted successfully']);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Course not found']);
            }
        } else {
            error_log("Delete error: " . json_encode($stmt->errorInfo()));
            echo json_encode(['status' => 'error', 'message' => 'Error: ' . $stmt->errorInfo()[2]]);
        }
    } catch (PDOException $e) {
        error_log("PDO Exception in deleteCourse: " . $e->getMessage());
        echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $e->getMessage()]);
    }
}
?>
