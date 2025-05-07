<?php
require_once('../../../back_office/controller/db_connection.php');

$message = '';
$messageType = '';

// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate and sanitize input
    $project = trim($_POST['project'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $type = trim($_POST['type'] ?? '');
    $skills_required = trim($_POST['skills_required'] ?? '');
    $git_link = trim($_POST['git_link'] ?? '');
    
    // Simple validation
    $errors = [];
    if (empty($project)) {
        $errors[] = "Project name is required";
    }
    if (empty($description)) {
        $errors[] = "Description is required";
    }
    if (empty($type)) {
        $errors[] = "Project type is required";
    }
    if (empty($skills_required)) {
        $errors[] = "Skills required is required";
    }
    if (empty($git_link)) {
        $errors[] = "Git link is required";
    } elseif (!filter_var($git_link, FILTER_VALIDATE_URL)) {
        $errors[] = "Invalid Git link format";
    }
    
    if (empty($errors)) {
        try {
            // Insert into project_requests table
            $stmt = $conn->prepare("INSERT INTO project_requests (project, description, type, skills_required, git_link) 
                                   VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$project, $description, $type, $skills_required, $git_link]);
            
            $message = "Your project has been submitted for review. An administrator will approve it soon.";
            $messageType = "success";
            
            // Clear form data after successful submission
            $project = $description = $type = $skills_required = $git_link = '';
        } catch (PDOException $e) {
            $message = "Error submitting project: " . $e->getMessage();
            $messageType = "error";
        }
    } else {
        $message = "Please correct the following errors:<br>" . implode("<br>", $errors);
        $messageType = "error";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Only Engineers - Add Project</title>
    <link rel="stylesheet" href="../view/styles.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        .form-container {
            max-width: 800px;
            margin: 0 auto;
            padding: 2rem;
            background: white;
            border-radius: 16px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }
        
        .form-group {
            margin-bottom: 1.5rem;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 500;
        }
        
        .form-control {
            width: 100%;
            padding: 0.75rem;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            font-size: 1rem;
            transition: border-color 0.2s;
        }
        
        .form-control:focus {
            border-color: #4f46e5;
            outline: none;
        }
        
        textarea.form-control {
            min-height: 120px;
            resize: vertical;
        }
        
        .btn-submit {
            display: inline-block;
            background: #4f46e5;
            color: white;
            font-weight: 500;
            padding: 0.75rem 1.5rem;
            border-radius: 8px;
            border: none;
            cursor: pointer;
            transition: background 0.2s;
        }
        
        .btn-submit:hover {
            background: #4338ca;
        }
        
        .message {
            padding: 1rem;
            margin-bottom: 1.5rem;
            border-radius: 8px;
        }
        
        .success {
            background-color: #d1fae5;
            color: #065f46;
            border: 1px solid #34d399;
        }
        
        .error {
            background-color: #fee2e2;
            color: #b91c1c;
            border: 1px solid #f87171;
        }
    </style>
</head>
<body>
    <div class="app-container">
        <nav class="navbar">
            <div class="nav-left">
                <a href="#" class="logo">
                    <img src="../assets/logo.png" alt="Only Engineers">
                </a>
            </div>
            <div class="nav-center">
                <nav class="nav-links">
                    <a href="../view/home.html">Home</a>
                    <a href="../view/Dashboard.html">Dashboard</a>
                    <a href="../view/index.html">Jobs</a>
                    <a href="../view/projects.php" class="active">Projects</a>
                    <a href="../view/courses.html">Courses</a>
                    <a href="../view/hackathon.html">Hackathons</a>
                    <a href="../view/articles.html">Articles</a>
                    <a href="../view/networking.html">Networking</a>
                </nav>
            </div>
            <div class="nav-right">
                <div class="user-profile">
                    <a href="../view/user-profile.html">
                        <img src="../assets/profil.jpg" alt="User profile" class="avatar">
                    </a>
                </div>
            </div>
        </nav>

        <div class="content">
            <div class="form-container">
                <h2 style="margin-bottom: 1.5rem;">Submit a New Project</h2>
                
                <?php if (!empty($message)): ?>
                <div class="message <?php echo $messageType; ?>">
                    <?php echo $message; ?>
                </div>
                <?php endif; ?>
                
                <form method="POST" action="">
                    <div class="form-group">
                        <label for="project">Project Name:</label>
                        <input type="text" id="project" name="project" class="form-control" value="<?php echo htmlspecialchars($project ?? ''); ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="description">Description:</label>
                        <textarea id="description" name="description" class="form-control" required><?php echo htmlspecialchars($description ?? ''); ?></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label for="type">Project Type:</label>
                        <select id="type" name="type" class="form-control" required>
                            <option value="" disabled <?php echo empty($type) ? 'selected' : ''; ?>>Select a type</option>
                            <option value="Web Development" <?php echo ($type ?? '') === 'Web Development' ? 'selected' : ''; ?>>Web Development</option>
                            <option value="Mobile Development" <?php echo ($type ?? '') === 'Mobile Development' ? 'selected' : ''; ?>>Mobile Development</option>
                            <option value="AI/ML" <?php echo ($type ?? '') === 'AI/ML' ? 'selected' : ''; ?>>AI/ML</option>
                            <option value="Data Science" <?php echo ($type ?? '') === 'Data Science' ? 'selected' : ''; ?>>Data Science</option>
                            <option value="Blockchain" <?php echo ($type ?? '') === 'Blockchain' ? 'selected' : ''; ?>>Blockchain</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="skills_required">Skills Required (comma-separated):</label>
                        <input type="text" id="skills_required" name="skills_required" class="form-control" 
                               placeholder="e.g. JavaScript, React, Node.js" value="<?php echo htmlspecialchars($skills_required ?? ''); ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="git_link">GitHub Link:</label>
                        <input type="url" id="git_link" name="git_link" class="form-control" 
                               placeholder="https://github.com/username/repository" value="<?php echo htmlspecialchars($git_link ?? ''); ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <button type="submit" class="btn-submit">Submit Project</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</body>
</html>
