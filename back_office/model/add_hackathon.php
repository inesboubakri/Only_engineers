<?php
/**
 * Add Hackathon Script
 * Handles adding a new hackathon to the database
 */

// Display errors for debugging (comment this out in production)
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Make sure we send JSON content type before any output
header('Content-Type: application/json');

// Start session to get user ID
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'You must be logged in to add a hackathon']);
    exit;
}

// Include database connection
require_once 'db_connectionback.php';
$conn = getConnection(); // Get PDO connection

// Check if connection was successful
if ($conn === null) {
    echo json_encode(['success' => false, 'message' => 'Database connection failed']);
    exit;
}

// Function to validate input
function validateInput($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

// Initialize response
$response = ['success' => false, 'message' => ''];

// Check if the form was submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate all required fields are present
    $requiredFields = ['name', 'description', 'start_date', 'end_date', 'start_time', 'end_time', 
                       'location', 'required_skills', 'organizer', 'max_participants'];
    
    $missingFields = [];
    foreach ($requiredFields as $field) {
        if (!isset($_POST[$field]) || empty($_POST[$field])) {
            $missingFields[] = $field;
        }
    }
    
    if (!empty($missingFields)) {
        $response['message'] = 'Missing required fields: ' . implode(', ', $missingFields);
        echo json_encode($response);
        exit;
    }
    
    // Get user ID from session
    $user_id = $_SESSION['user_id'];
    
    // Validate and sanitize input
    $name = validateInput($_POST['name']);
    $description = validateInput($_POST['description']);
    $start_date = validateInput($_POST['start_date']);
    $end_date = validateInput($_POST['end_date']);
    $start_time = validateInput($_POST['start_time']);
    $end_time = validateInput($_POST['end_time']);
    $location = validateInput($_POST['location']);
    $required_skills = validateInput($_POST['required_skills']);
    $organizer = validateInput($_POST['organizer']);
    $max_participants = validateInput($_POST['max_participants']);
    
    // Validate and sanitize geographic coordinates
    $latitude = isset($_POST['latitude']) ? filter_var($_POST['latitude'], FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION) : null;
    $longitude = isset($_POST['longitude']) ? filter_var($_POST['longitude'], FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION) : null;
    
    // Validate latitude and longitude if provided
    if ($latitude !== null && $longitude !== null) {
        if (!is_numeric($latitude) || $latitude < -90 || $latitude > 90) {
            $response['message'] = 'Invalid latitude value. Must be between -90 and 90.';
            echo json_encode($response);
            exit;
        }
        
        if (!is_numeric($longitude) || $longitude < -180 || $longitude > 180) {
            $response['message'] = 'Invalid longitude value. Must be between -180 and 180.';
            echo json_encode($response);
            exit;
        }
    }
    
    // Additional validation
    
    // Name validation (not pure numbers or symbols)
    if (!preg_match('/[a-zA-Z]/', $name) || preg_match('/^\d+$/', $name)) {
        $response['message'] = 'Hackathon name cannot be pure numbers or symbols';
        echo json_encode($response);
        exit;
    }
    
    // Description validation (10-255 words)
    $wordCount = count(explode(' ', preg_replace('/\s+/', ' ', $description)));
    if ($wordCount < 10) {
        $response['message'] = 'Description must contain at least 10 words';
        echo json_encode($response);
        exit;
    }
    if ($wordCount > 255) {
        $response['message'] = 'Description cannot exceed 255 words';
        echo json_encode($response);
        exit;
    }
    
    // Date validation
    if (strtotime($end_date) < strtotime($start_date)) {
        $response['message'] = 'End date must be after start date';
        echo json_encode($response);
        exit;
    }
    
    // Time validation if same day
    if ($start_date === $end_date && $start_time >= $end_time) {
        $response['message'] = 'End time must be after start time on the same day';
        echo json_encode($response);
        exit;
    }
    
    // Location validation (not pure numbers or symbols)
    if (!preg_match('/[a-zA-Z]/', $location) || preg_match('/^\d+$/', $location)) {
        $response['message'] = 'Location cannot be pure numbers or symbols';
        echo json_encode($response);
        exit;
    }
    
    // Organizer validation (not pure numbers or symbols)
    if (!preg_match('/[a-zA-Z]/', $organizer) || preg_match('/^\d+$/', $organizer)) {
        $response['message'] = 'Organizer name cannot be pure numbers or symbols';
        echo json_encode($response);
        exit;
    }
    
    // Max participants validation (must be a positive number)
    if (!is_numeric($max_participants) || intval($max_participants) <= 0) {
        $response['message'] = 'Maximum participants must be a positive number';
        echo json_encode($response);
        exit;
    }
    
    // Image upload handling
    $image_path = '';
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        // Validate file type
        $allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        if (!in_array($_FILES['image']['type'], $allowed_types)) {
            $response['message'] = 'File must be a valid image (JPEG, PNG, GIF, WEBP)';
            echo json_encode($response);
            exit;
        }
        
        // Validate file size (max 2MB)
        if ($_FILES['image']['size'] > 2 * 1024 * 1024) {
            $response['message'] = 'File size exceeds 2MB limit';
            echo json_encode($response);
            exit;
        }
        
        // Create hackathon images directory if it doesn't exist
        $upload_dir = '../../front_office/front_office/ressources/hackathon_images/';
        if (!file_exists($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }
        
        // Generate unique filename
        $file_extension = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
        $filename = 'hackathon_' . time() . '_' . uniqid() . '.' . $file_extension;
        $upload_path = $upload_dir . $filename;
        
        // Move uploaded file
        if (move_uploaded_file($_FILES['image']['tmp_name'], $upload_path)) {
            $image_path = 'hackathon_images/' . $filename;
        } else {
            $response['message'] = 'Failed to upload image: ' . error_get_last()['message'];
            echo json_encode($response);
            exit;
        }
    } else {
        $upload_error = isset($_FILES['image']) ? $_FILES['image']['error'] : 'No image uploaded';
        $response['message'] = 'Hackathon image error: ' . $upload_error;
        echo json_encode($response);
        exit;
    }
    
    try {
        // Insert into database using PDO
        $sql = "INSERT INTO hackathons (name, description, start_date, end_date, start_time, end_time, 
                                       location, latitude, longitude, required_skills, organizer, max_participants, image, user_id) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        
        $stmt = $conn->prepare($sql);
        
        if (!$stmt) {
            throw new Exception("Prepare failed: " . print_r($conn->errorInfo(), true));
        }
        
        // Bind parameters
        $stmt->bindParam(1, $name);
        $stmt->bindParam(2, $description);
        $stmt->bindParam(3, $start_date);
        $stmt->bindParam(4, $end_date);
        $stmt->bindParam(5, $start_time);
        $stmt->bindParam(6, $end_time);
        $stmt->bindParam(7, $location);
        $stmt->bindParam(8, $latitude);
        $stmt->bindParam(9, $longitude);
        $stmt->bindParam(10, $required_skills);
        $stmt->bindParam(11, $organizer);
        $stmt->bindParam(12, $max_participants, PDO::PARAM_INT);
        $stmt->bindParam(13, $image_path);
        $stmt->bindParam(14, $user_id, PDO::PARAM_INT);
        
        if (!$stmt->execute()) {
            throw new Exception("Execute failed: " . print_r($stmt->errorInfo(), true));
        }
        
        $response['success'] = true;
        $response['message'] = 'Hackathon added successfully';
        $response['hackathon_id'] = $conn->lastInsertId();
        
    } catch (Exception $e) {
        $response['message'] = 'Error: ' . $e->getMessage();
    }
    
    // No need to close PDO connection explicitly
} else {
    $response['message'] = 'Invalid request method';
}

// Return JSON response
echo json_encode($response);
?>