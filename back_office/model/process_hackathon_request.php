<?php
/**
 * Process Hackathon Request
 * Handles approval or rejection of hackathon requests
 */

// Start session
session_start();

// Make sure we send JSON content type
header('Content-Type: application/json');

// Check if user is logged in and is an admin
if (!isset($_SESSION['user_id']) || $_SESSION['is_admin'] != 1) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit();
}

// Include database connection
require_once 'db_connectionback.php';

function approveHackathonRequest($requestId) {
    try {
        $pdo = getConnection();
        
        // Begin transaction
        $pdo->beginTransaction();
        
        // Get the request information
        $stmt = $pdo->prepare("SELECT * FROM hackathon_requests WHERE id = ?");
        $stmt->execute([$requestId]);
        $request = $stmt->fetch();
        
        if ($request) {
            // Set up directories
            $uploadDir = __DIR__ . '/../../front_office/front_office/ressources/hackathon_images/';
            $tempDir = __DIR__ . '/../../temp_uploads/';
            
            // Create directories if they don't exist
            if (!file_exists($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }
            if (!file_exists($tempDir)) {
                mkdir($tempDir, 0777, true);
            }
            
            // Handle image
            $imagePath = '';
            if (!empty($request['image'])) {
                $tempPath = $tempDir . basename($request['image']);
                $finalPath = $uploadDir . basename($request['image']);
                
                if (file_exists($tempPath)) {
                    if (rename($tempPath, $finalPath)) {
                        $imagePath = 'hackathon_images/' . basename($request['image']);
                    }
                } else if (file_exists($finalPath)) {
                    // Image is already in the correct folder
                    $imagePath = 'hackathon_images/' . basename($request['image']);
                }
            }
            
            // Insert into hackathons table with user_id
            $stmt = $pdo->prepare("INSERT INTO hackathons 
                (name, description, start_date, end_date, start_time, end_time,
                location, required_skills, organizer, max_participants, image, user_id, status) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'approved')");
                
            $stmt->execute([
                $request['name'],
                $request['description'],
                $request['start_date'],
                $request['end_date'],
                $request['start_time'],
                $request['end_time'],
                $request['location'],
                $request['required_skills'],
                $request['organizer'],
                $request['max_participants'],
                $imagePath,
                $request['user_id']  // Make sure to include the user_id from the request
            ]);
            
            // Delete the request
            $stmt = $pdo->prepare("DELETE FROM hackathon_requests WHERE id = ?");
            $stmt->execute([$requestId]);
            
            // Commit transaction
            $pdo->commit();
            
            // Debug logging
            error_log("Successfully approved hackathon request. Image: " . $imagePath);
            error_log("User ID from request: " . $request['user_id']);
            
            return ['success' => true, 'message' => 'Hackathon request approved successfully'];
        }
        return ['success' => false, 'message' => 'Request not found'];
        
    } catch (PDOException $e) {
        // Rollback transaction on error
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        error_log("Error approving hackathon: " . $e->getMessage());
        return ['success' => false, 'message' => 'Database error: ' . $e->getMessage()];
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $requestId = $_POST['request_id'] ?? null;
    $action = $_POST['action'] ?? null;
    
    if ($requestId && $action) {
        if ($action === 'approve') {
            $result = approveHackathonRequest($requestId);
            echo json_encode($result);
        } else {
            // Handle request rejection
            try {
                $pdo = getConnection();
                $stmt = $pdo->prepare("DELETE FROM hackathon_requests WHERE id = ?");
                $stmt->execute([$requestId]);
                echo json_encode(['success' => true, 'message' => 'Request rejected successfully']);
            } catch (PDOException $e) {
                echo json_encode(['success' => false, 'message' => 'Error rejecting request: ' . $e->getMessage()]);
            }
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Missing required parameters']);
    }
}
?>