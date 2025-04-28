<?php
header('Content-Type: application/json');
require_once('../../../back_office/controller/db_connection.php');

// Assume $success is whether the insert worked or not
if (isset($_POST['project']) && isset($_POST['description']) && isset($_POST['type']) && isset($_POST['skills_required']) && isset($_POST['git_link']) && isset($_POST['status'])) {
    
    // Sanitize input to avoid SQL injection or issues
    $project = trim($_POST['project']);
    $description = trim($_POST['description']);
    $type = trim($_POST['type']);
    $skills_required = trim($_POST['skills_required']);
    $git_link = trim($_POST['git_link']);
    $status = trim($_POST['status']);

    // Validate if fields are not empty
    if (empty($project) || empty($description) || empty($type) || empty($skills_required) || empty($git_link) || empty($status)) {
        echo json_encode(['success' => false, 'message' => 'All fields are required']);
        exit();
    }

    try {
        // Insert into the database
        $stmt = $conn->prepare("INSERT INTO projet (project, description, type, skills_required, git_link, status) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([
            $project,
            $description,
            $type,
            $skills_required,
            $git_link,
            $status
        ]);

        // If insert successful
        echo json_encode(['success' => true, 'message' => 'Project added successfully']);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Missing required fields']);
}
?>
