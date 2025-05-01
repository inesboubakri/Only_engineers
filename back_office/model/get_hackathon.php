<?php
/**
 * Get Hackathon Script
 * Fetch a single hackathon by ID for editing or viewing
 */

// Display errors for debugging (comment this out in production)
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Make sure we send JSON content type before any output
header('Content-Type: application/json');

// Start session to get user ID
session_start();

// Include database connection
require_once 'db_connectionback.php';

// Initialize response
$response = ['success' => false, 'message' => ''];

try {
    // Get database connection
    $conn = getConnection();
    
    // Check if connection was successful
    if ($conn === null) {
        throw new Exception('Database connection failed');
    }
    
    // Check if ID is provided
    if (!isset($_GET['id']) || empty($_GET['id'])) {
        throw new Exception('No hackathon ID provided');
    }
    
    // Sanitize the ID
    $hackathonId = filter_var($_GET['id'], FILTER_VALIDATE_INT);
    
    if (!$hackathonId) {
        throw new Exception('Invalid hackathon ID');
    }
    
    // Fetch hackathon from database
    $query = "SELECT * FROM hackathons WHERE id = :id";
    $stmt = $conn->prepare($query);
    $stmt->bindParam(':id', $hackathonId, PDO::PARAM_INT);
    $stmt->execute();
    
    // Check if hackathon exists
    $hackathon = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$hackathon) {
        throw new Exception('Hackathon not found');
    }
    
    // Success response with hackathon data
    $response['success'] = true;
    $response['hackathon'] = $hackathon;
    
} catch (Exception $e) {
    $response['message'] = $e->getMessage();
}

// Return JSON response
echo json_encode($response);
?>