<?php
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    require_once('../controller/db_connection.php');
    
    if (isset($_POST['id']) && !empty($_POST['id'])) {
        $id = (int)$_POST['id'];
        
        if (
            isset($_POST['project']) && !empty($_POST['project']) &&
            isset($_POST['description']) && !empty($_POST['description']) &&
            isset($_POST['type']) && !empty($_POST['type']) &&
            isset($_POST['skills_required']) && !empty($_POST['skills_required']) &&
            isset($_POST['git_link']) && !empty($_POST['git_link']) &&
            isset($_POST['status']) && !empty($_POST['status'])
        ) {
            $project = htmlspecialchars($_POST['project']);
            $description = htmlspecialchars($_POST['description']);
            $type = htmlspecialchars($_POST['type']);
            $skills_required = htmlspecialchars($_POST['skills_required']);
            $git_link = htmlspecialchars($_POST['git_link']);
            $status = htmlspecialchars($_POST['status']);

            // Log data and query for debugging (remove these lines in production)
            echo "Updating project with ID: $id <br>";
            echo "Data: $project, $description, $type, $skills_required, $git_link, $status <br>";

            // SQL query for update
            $sql = "UPDATE projet SET 
                        project = :project, 
                        description = :description, 
                        type = :type, 
                        skills_required = :skills_required, 
                        git_link = :git_link, 
                        status = :status 
                    WHERE id = :id";

            // Prepare statement
            $stmt = $conn->prepare($sql);

            // Bind parameters
            $stmt->bindParam(':project', $project);
            $stmt->bindParam(':description', $description);
            $stmt->bindParam(':type', $type);
            $stmt->bindParam(':skills_required', $skills_required);
            $stmt->bindParam(':git_link', $git_link);
            $stmt->bindParam(':status', $status);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);

            // Execute the update query
            if ($stmt->execute()) {
                echo "Update successful!";
                header("Location: ../view/Projects.php?success=updated");
            } else {
                echo "Error: " . $stmt->errorInfo()[2];
                header("Location: ../view/Projects.php?error=" . urlencode($stmt->errorInfo()[2]));
            }
        } else {
            header("Location: ../view/Projects.php?error=missing_fields");
        }
    } else {
        header("Location: ../view/Projects.php?error=invalid_id");
    }
    
    // Close connection (PDO doesn't require explicit closing, but for clarity)
    $conn = null;
    exit();
} else {
    header("Location: ../view/Projects.php");
    exit();
}
?>
