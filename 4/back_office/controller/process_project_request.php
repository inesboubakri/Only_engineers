<?php
// Include database connection
require_once('../controller/db_connection.php');

// Set the response header to JSON
header('Content-Type: application/json');

// Initialize response array
$response = array('success' => false, 'message' => '');

// Check if required parameters are present
if (isset($_POST['id']) && isset($_POST['action'])) {
    $requestId = $_POST['id'];
    $action = $_POST['action'];
    
    try {
        // Begin transaction
        $conn->beginTransaction();
        
        // First, get the request details
        $stmt = $conn->prepare("SELECT * FROM project_requests WHERE id = ?");
        $stmt->execute([$requestId]);
        $request = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$request) {
            throw new Exception("Project request not found.");
        }
        
        // Process based on action
        if ($action === 'approve') {
            // Insert into projects table
            $stmt = $conn->prepare("INSERT INTO projet (project, description, type, skills_required, git_link, status) 
                                   VALUES (?, ?, ?, ?, ?, 'active')");
            $stmt->execute([
                $request['project'],
                $request['description'],
                $request['type'],
                $request['skills_required'],
                $request['git_link']
            ]);
            
            // Delete from requests table
            $stmt = $conn->prepare("DELETE FROM project_requests WHERE id = ?");
            $stmt->execute([$requestId]);
            
            $response['success'] = true;
            $response['message'] = 'Project request approved and added to projects.';
        } 
        elseif ($action === 'reject') {
            // Delete from requests table
            $stmt = $conn->prepare("DELETE FROM project_requests WHERE id = ?");
            $stmt->execute([$requestId]);
            
            $response['success'] = true;
            $response['message'] = 'Project request rejected and removed.';
        }
        else {
            throw new Exception("Invalid action specified.");
        }
        
        // Commit transaction
        $conn->commit();
    } 
    catch (Exception $e) {
        // Rollback transaction on error
        $conn->rollBack();
        $response['message'] = 'Error: ' . $e->getMessage();
    }
} 
else {
    $response['message'] = 'Missing required parameters.';
}

// Return the response as JSON
echo json_encode($response);
?>