<?php
// Activer le reporting d'erreurs pour le développement
ini_set('display_errors', 1);
error_reporting(E_ALL);

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Gérer les requêtes OPTIONS pour CORS
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

try {
    // Chemin corrigé pour la connexion DB (adaptez-le à votre structure)
    require_once __DIR__ . '/../../front_office/model/db_connection.php';
    
    if (!isset($conn) || !($conn instanceof PDO)) {
        throw new Exception("Database connection failed");
    }

    // Récupérer l'action de manière sécurisée
    $input = file_get_contents('php://input');
    $data = json_decode($input, true) ?? $_POST;
    $action = $data['action'] ?? '';

    switch ($action) {
        case 'create':
            createCourse($conn, $data);
            break;
        case 'read':
            readCourses($conn);
            break;
        case 'update':
            updateCourse($conn, $data);
            break;
        case 'delete':
            deleteCourse($conn, $data);
            break;
        default:
            throw new Exception('Invalid action', 400);
    }
} catch (Exception $e) {
    http_response_code($e->getCode() ?: 500);
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage(),
        'error_details' => (ini_get('display_errors')) ? $e->getTraceAsString() : null
    ]);
}

function createCourse($conn, $data) {
    // Start session if not already started
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    // Verify user is authenticated and get their ID
    if (!isset($_SESSION['user_id'])) {
        throw new Exception("User not authenticated", 401);
    }
    $created_by = $_SESSION['user_id'];

    // Validate required fields
    if (empty($data['courseTitle'])) {
        throw new Exception("Course title cannot be empty", 400);
    }

    // Generate course ID if not provided
    $course_id = $data['courseId'] ?? generateCourseId($conn);

    // Clean and validate data
    $title = htmlspecialchars(trim($data['courseTitle']));
    $fees = is_numeric(str_replace(['$', ','], '', $data['courseFees'] ?? '0')) 
            ? (float)str_replace(['$', ','], '', $data['courseFees'])
            : 0.00;
    $course_link = filter_var($data['courseLink'] ?? '#', FILTER_SANITIZE_URL);
    $certification_link = filter_var($data['courseCertification'] ?? '#', FILTER_SANITIZE_URL);
    $status = in_array($data['courseStatus'] ?? 'free', ['free', 'paid', 'coming_soon']) 
               ? $data['courseStatus']
               : 'free';

    try {
        // Verify the user exists
        $userCheck = $conn->prepare("SELECT user_id FROM users WHERE user_id = ?");
        $userCheck->execute([$created_by]);
        if ($userCheck->fetchColumn() === false) {
            throw new Exception("User does not exist", 404);
        }

        // Verify course ID doesn't exist
        $courseCheck = $conn->prepare("SELECT COUNT(*) FROM cours WHERE course_id = ?");
        $courseCheck->execute([$course_id]);
        if ($courseCheck->fetchColumn() > 0) {
            http_response_code(409);
            echo json_encode([
                'status' => 'error',
                'message' => 'Course ID already exists',
                'suggested_id' => generateCourseId($conn)
            ]);
            return;
        }

        // Insert the course
        $sql = "INSERT INTO cours (course_id, title, fees, course_link, certification_link, status, created_by) 
                VALUES (?, ?, ?, ?, ?, ?, ?)";
        
        $stmt = $conn->prepare($sql);
        if (!$stmt->execute([$course_id, $title, $fees, $course_link, $certification_link, $status, $created_by])) {
            throw new Exception("Failed to create course");
        }

        echo json_encode([
            'status' => 'success',
            'message' => 'Course created successfully',
            'course_id' => $course_id,
            'created_by' => $created_by
        ]);
        
    } catch (PDOException $e) {
        throw new Exception("Database error: " . $e->getMessage());
    }
}
function readCourses($conn) {
    try {
        $sql = "SELECT * FROM cours ORDER BY course_id DESC";
        $stmt = $conn->query($sql);
        $courses = $stmt->fetchAll();
        
        echo json_encode(['status' => 'success', 'data' => $courses]);
    } catch (PDOException $e) {
        throw new Exception('Database error: ' . $e->getMessage());
    }
}

function updateCourse($conn) {
    $course_id = $_POST['courseId'];
    $title = $_POST['courseTitle'];
    $fees = is_numeric(str_replace('$', '', $_POST['courseFees'])) ? str_replace('$', '', $_POST['courseFees']) : 0;
    $course_link = $_POST['courseLink'];
    $certification_link = $_POST['courseCertification'];
    $status = $_POST['courseStatus'];
    
    try {
        $sql = "UPDATE cours SET title = :title, fees = :fees, course_link = :course_link, 
                certification_link = :certification_link, status = :status 
                WHERE course_id = :course_id";
                
        $stmt = $conn->prepare($sql);
        $result = $stmt->execute([
            ':title' => $title,
            ':fees' => $fees,
            ':course_link' => $course_link,
            ':certification_link' => $certification_link,
            ':status' => $status,
            ':course_id' => $course_id
        ]);
        
        if ($result) {
            $rowCount = $stmt->rowCount();
            echo json_encode([
                'status' => 'success',
                'message' => $rowCount > 0 ? 'Course updated successfully' : 'No changes made or course not found',
                'affected_rows' => $rowCount
            ]);
        } else {
            throw new Exception('Error updating course');
        }
    } catch (PDOException $e) {
        throw new Exception('Database error: ' . $e->getMessage());
    }
}

function deleteCourse($conn) {
    $course_id = $_POST['courseId'];
    
    try {
        $sql = "DELETE FROM cours WHERE course_id = :course_id";
        $stmt = $conn->prepare($sql);
        $result = $stmt->execute([':course_id' => $course_id]);
        
        if ($result) {
            echo json_encode([
                'status' => 'success', 
                'message' => 'Course deleted successfully'
            ]);
        } else {
            throw new Exception('Error deleting course');
        }
    } catch (PDOException $e) {
        throw new Exception('Database error: ' . $e->getMessage());
    }
}


function generateCourseId($conn) {
    try {
        // Vérifier si la table est vide
        $count = $conn->query("SELECT COUNT(*) FROM cours")->fetchColumn();
        if ($count == 0) {
            return 'CRS-001';
        }

        // Récupérer le dernier ID numérique utilisé
        $sql = "SELECT MAX(CAST(SUBSTRING(course_id, 5) AS UNSIGNED)) as max_id 
                FROM cours 
                WHERE course_id LIKE 'CRS-%'";
        
        $stmt = $conn->query($sql);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        $nextNum = ($result && $result['max_id']) ? $result['max_id'] + 1 : 1;
        
        return 'CRS-' . str_pad($nextNum, 3, '0', STR_PAD_LEFT);
        
    } catch (PDOException $e) {
        // Fallback sécurisé en cas d'erreur
        error_log("Error generating course ID: " . $e->getMessage());
        return 'CRS-' . str_pad(rand(1, 999), 3, '0', STR_PAD_LEFT);
    }
}
?>
