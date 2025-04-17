<?php
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Validate project ID
if (!isset($_GET['id']) || empty($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: view_projects.php?error=1&message=Invalid project ID");
    exit();
}

$project_id = intval($_GET['id']);

require_once('../../../back_office/controller/db_connection.php');

try {
    // Check if the project exists
    $check_stmt = $conn->prepare("SELECT id, project FROM projet WHERE id = ?");
    $check_stmt->execute([$project_id]);
    $project = $check_stmt->fetch(PDO::FETCH_ASSOC);

    if (!$project) {
        header("Location: view_projects.php?error=1&message=Project not found");
        exit();
    }

    // Process deletion if confirmed
    if (isset($_POST['confirm']) && $_POST['confirm'] === 'yes') {
        $delete_stmt = $conn->prepare("DELETE FROM projet WHERE id = ?");
        if ($delete_stmt->execute([$project_id])) {
            header("Location: view_projects.php?success=1&message=Project deleted successfully");
            exit();
        } else {
            $error = "Error deleting project.";
        }
    }
} catch (PDOException $e) {
    $error = "Database error: " . $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Delete Project - Only Engineers</title>
    <link rel="stylesheet" href="../view/styles.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        .container {
            max-width: 600px;
            margin: 50px auto;
            padding: 30px;
            background: white;
            border-radius: 16px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            text-align: center;
        }

        .warning-icon {
            color: #f44336;
            font-size: 64px;
            margin-bottom: 20px;
        }

        h2 {
            color: #333;
            margin-bottom: 20px;
        }

        p {
            margin-bottom: 30px;
            color: #555;
            line-height: 1.5;
        }

        .project-name {
            font-weight: bold;
            color: #333;
        }

        .button-container {
            display: flex;
            justify-content: center;
            gap: 15px;
            margin-top: 30px;
        }

        .delete-btn, .cancel-btn {
            padding: 12px 25px;
            border-radius: 8px;
            font-weight: 500;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
            transition: background-color 0.2s;
        }

        .delete-btn {
            background-color: #f44336;
            color: white;
            border: none;
        }

        .delete-btn:hover {
            background-color: #d32f2f;
        }

        .cancel-btn {
            background-color: #e0e0e0;
            color: #333;
            border: none;
        }

        .cancel-btn:hover {
            background-color: #ccc;
        }

        .alert {
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 8px;
        }

        .alert-danger {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
    </style>
</head>
<body>
    <div class="container">
        <?php if(isset($error)): ?>
        <div class="alert alert-danger">
            <?php echo $error; ?>
        </div>
        <?php endif; ?>

        <div class="warning-icon">⚠️</div>

        <h2>Delete Project</h2>

        <p>
            Are you sure you want to delete the project 
            <span class="project-name">"<?php echo htmlspecialchars($project['project']); ?>"</span>?
            <br><br>
            This action cannot be undone.
        </p>

        <div class="button-container">
            <form method="POST">
                <input type="hidden" name="confirm" value="yes">
                <button type="submit" class="delete-btn">Delete Project</button>
            </form>

            <a href="view_projects.php" class="cancel-btn">Cancel</a>
        </div>
    </div>
</body>
</html>
