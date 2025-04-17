<?php
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once('../../../back_office/controller/db_connection.php');

// Check if table structure is correct, drop and recreate if necessary
$checkIdColumn = $conn->query("SHOW COLUMNS FROM projet LIKE 'id'");
if ($checkIdColumn->rowCount() == 0) {
    // Table structure is incorrect, drop and recreate
    $conn->exec("DROP TABLE IF EXISTS projet");
    
    // Recreate the table with correct structure
    $createTable = "CREATE TABLE `projet` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `project` varchar(255) NOT NULL,
        `description` text NOT NULL,
        `type` varchar(100) NOT NULL,
        `skills_required` text NOT NULL,
        `git_link` varchar(250) NOT NULL,
        `status` varchar(50) NOT NULL,
        PRIMARY KEY (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;";
    
    if (!$conn->exec($createTable)) {
        header("Location: add_project.php?error=1&message=" . urlencode("Error creating table"));
        exit();
    }
}

// Determine the action type (create, update, delete)
$action = $_POST['action'] ?? 'create';

// Validate and sanitize input data for create and update
if ($action === 'create' || $action === 'update') {
    $project = trim($_POST['project'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $type = trim($_POST['type'] ?? '');
    $skills_required = trim($_POST['skills_required'] ?? '');
    $git_link = trim($_POST['git_link'] ?? '');
    $status = trim($_POST['status'] ?? '');
    
    // Validate required fields
    if (empty($project) || empty($description) || empty($type) || empty($skills_required) || empty($git_link) || empty($status)) {
        header("Location: " . ($action === 'create' ? 'add_project.php' : 'edit_project.php?id=' . $_POST['id']) . "&error=1&message=All fields are required");
        exit();
    }
}

// Process based on action type
try {
    switch ($action) {
        case 'create':
            // Create new project
            $stmt = $conn->prepare("INSERT INTO projet (project, description, type, skills_required, git_link, status) VALUES (:project, :description, :type, :skills_required, :git_link, :status)");
            
            $stmt->bindParam(':project', $project, PDO::PARAM_STR);
            $stmt->bindParam(':description', $description, PDO::PARAM_STR);
            $stmt->bindParam(':type', $type, PDO::PARAM_STR);
            $stmt->bindParam(':skills_required', $skills_required, PDO::PARAM_STR);
            $stmt->bindParam(':git_link', $git_link, PDO::PARAM_STR);
            $stmt->bindParam(':status', $status, PDO::PARAM_STR);
            
            if ($stmt->execute()) {
                header("Location: projects.php?success=1&message=Project added successfully");
                exit();
            } else {
                throw new Exception("Error executing statement: " . implode(", ", $stmt->errorInfo()));
            }
            break;
            
        case 'update':
            // Update existing project
            $id = intval($_POST['id'] ?? 0);
            
            if ($id <= 0) {
                header("Location: view_projects.php?error=1&message=Invalid project ID");
                exit();
            }
            
            $stmt = $conn->prepare("UPDATE projet SET project = :project, description = :description, type = :type, skills_required = :skills_required, git_link = :git_link, status = :status WHERE id = :id");
            
            $stmt->bindParam(':project', $project, PDO::PARAM_STR);
            $stmt->bindParam(':description', $description, PDO::PARAM_STR);
            $stmt->bindParam(':type', $type, PDO::PARAM_STR);
            $stmt->bindParam(':skills_required', $skills_required, PDO::PARAM_STR);
            $stmt->bindParam(':git_link', $git_link, PDO::PARAM_STR);
            $stmt->bindParam(':status', $status, PDO::PARAM_STR);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            
            if ($stmt->execute()) {
                header("Location: view_projects.php?success=1&message=Project updated successfully");
            } else {
                throw new Exception("Error executing statement: " . implode(", ", $stmt->errorInfo()));
            }
            break;
            
        case 'delete':
            // Delete a project
            $id = intval($_POST['id'] ?? 0);
            
            if ($id <= 0) {
                header("Location: view_projects.php?error=1&message=Invalid project ID");
                exit();
            }
            
            $stmt = $conn->prepare("DELETE FROM projet WHERE id = :id");
            
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            
            if ($stmt->execute()) {
                header("Location: view_projects.php?success=1&message=Project deleted successfully");
            } else {
                throw new Exception("Error executing statement: " . implode(", ", $stmt->errorInfo()));
            }
            break;
            
        default:
            header("Location: projects.php?error=1&message=Invalid action");
    }
} catch (Exception $e) {
    header("Location: projects.php?error=1&message=" . urlencode($e->getMessage()));
}

if (isset($stmt)) {
    $stmt = null;
}
$conn = null;
?>
