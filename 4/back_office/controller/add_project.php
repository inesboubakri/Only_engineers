<?php
// Check if form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Include database connection
    require_once('../controller/db_connection.php');
    
    // Collect and validate form data
    if (
        isset($_POST['project']) && !empty($_POST['project']) &&
        isset($_POST['description']) && !empty($_POST['description']) &&
        isset($_POST['type']) && !empty($_POST['type']) &&
        isset($_POST['skills_required']) && !empty($_POST['skills_required']) &&
        isset($_POST['git_link']) && !empty($_POST['git_link']) &&
        isset($_POST['status'])
    ) {
        // Sanitize inputs
        $project = htmlspecialchars($_POST['project']);
        $description = htmlspecialchars($_POST['description']);
        $type = htmlspecialchars($_POST['type']);
        $skills_required = htmlspecialchars($_POST['skills_required']);
        $git_link = htmlspecialchars($_POST['git_link']);
        $status = htmlspecialchars($_POST['status']);
        
        // Prepare SQL query
        if (isset($_POST['custom_id']) && !empty($_POST['custom_id'])) {
            $custom_id = (int)$_POST['custom_id'];
            
            // ✅ Use prepared statement to avoid SQL injection
            $sql = "INSERT INTO projet (id, project, description, type, skills_required, git_link, status) 
                    VALUES (:id, :project, :description, :type, :skills_required, :git_link, :status)";
            $stmt = $conn->prepare($sql);
            $stmt->bindParam(':id', $custom_id, PDO::PARAM_INT);
        } else {
            // ✅ Let MySQL auto-generate the ID
            $sql = "INSERT INTO projet (project, description, type, skills_required, git_link, status) 
                    VALUES (:project, :description, :type, :skills_required, :git_link, :status)";
            $stmt = $conn->prepare($sql);
        }
        
        // Bind the remaining parameters
        $stmt->bindParam(':project', $project);
        $stmt->bindParam(':description', $description);
        $stmt->bindParam(':type', $type);
        $stmt->bindParam(':skills_required', $skills_required);
        $stmt->bindParam(':git_link', $git_link);
        $stmt->bindParam(':status', $status);
        
        // Execute query and handle result
        try {
            $stmt->execute();
            header("Location: ../view/Projects.php?success=created");
        } catch (PDOException $e) {
            header("Location: ../view/Projects.php?error=" . urlencode($e->getMessage()));
        }
    } else {
        header("Location: ../view/Projects.php?error=missing_fields");
    }
    
    // Close connection (PDO doesn't require explicit close, but just for clarity)
    $conn = null;
    exit();
} else {
    header("Location: ../view/Projects.php");
    exit();
}
?>
