<?php
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once('../../../back_office/controller/db_connection.php');

// Check if table exists
try {
    $stmt = $conn->query("SHOW TABLES LIKE 'projet'");
    $tableExists = $stmt->rowCount();

    if ($tableExists == 0) {
        // Table doesn't exist, create it
        $createTable = "CREATE TABLE IF NOT EXISTS `projet` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `project` varchar(255) NOT NULL,
            `description` text NOT NULL,
            `type` varchar(100) NOT NULL,
            `skills_required` text NOT NULL,
            `git_link` varchar(250) NOT NULL,
            `status` varchar(50) NOT NULL,
            PRIMARY KEY (`id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;";
        $conn->exec($createTable);
    }
} catch (PDOException $e) {
    echo "Error checking or creating table: " . $e->getMessage();
    exit();
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
            max-width: 1000px;
            margin: 40px auto;
            padding: 30px;
            background: white;
            border-radius: 16px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }
        .form-title {
            margin-bottom: 30px;
            font-size: 26px;
            font-weight: 600;
            color: #333;
            text-align: center;
        }
        .form-group {
            display: flex;
            flex-wrap: wrap;
            margin-bottom: 25px;
            gap: 20px;
        }
        label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #444;
        }
        .form-group .half-width {
            flex: 1 1 48%;
        }
        input[type="text"], input[type="url"], textarea, select {
            width: 100%;
            padding: 14px;
            border: 1px solid #ddd;
            border-radius: 8px;
            font-size: 16px;
            font-family: 'Inter', sans-serif;
            transition: border 0.2s ease-in-out;
        }
        input[type="text"]:focus, input[type="url"]:focus, textarea:focus, select:focus {
            border-color: #4CAF50;
            outline: none;
        }
        textarea {
            min-height: 120px;
            resize: vertical;
        }
        .submit-btn, .cancel-btn {
            padding: 14px 28px;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: background-color 0.2s;
        }
        .submit-btn {
            background-color: #4CAF50;
            color: white;
            border: none;
        }
        .submit-btn:hover {
            background-color: #45a049;
        }
        .cancel-btn {
            background-color: #f44336;
            color: white;
            border: none;
            margin-left: 15px;
        }
        .cancel-btn:hover {
            background-color: #d32f2f;
        }
        .form-footer {
            margin-top: 20px;
            display: flex;
            justify-content: flex-end;
            align-items: center;
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
                <a href="../view/projects.html" class="active">Projects</a>
                <a href="../view/courses.html">Courses</a>
                <a href="../view/hackathon.html">Hackathons</a>
                <a href="../view/articles.html">Articles</a>
                <a href="../view/networking.html">Networking</a>
            </nav>
        </div>
        <div class="nav-right">
            <div class="notification-wrapper">
                <button class="icon-button notification">
                    <!-- Notification Icon -->
                </button>
                <div class="notification-dot"></div>
            </div>
            <button class="icon-button theme-toggle" id="themeToggle">
                <!-- Theme Toggle Icon -->
            </button>
            <div class="user-profile">
                <a href="../view/user-profile.html">
                    <img src="../assets/profil.jpg" alt="User profile" class="avatar">
                </a>
            </div>
        </div>
    </nav>

    <div class="content">
        <div class="form-container">
            <h2 class="form-title">Add a New Project</h2>

            <form id="addProjectForm">
                <input type="hidden" name="action" value="create">

                <div class="form-group">
                    <div class="half-width">
                        <label for="project">Project Name:</label>
                        <input type="text" id="project" name="project" required>
                    </div>
                    <div class="half-width">
                        <label for="git_link">Git Repository Link:</label>
                        <input type="url" id="git_link" name="git_link" placeholder="https://github.com/username/repository" required>
                    </div>
                </div>

                <div class="form-group">
                    <div class="half-width">
                        <label for="description">Description:</label>
                        <textarea id="description" name="description" rows="4" required></textarea>
                    </div>
                    <div class="half-width">
                        <label for="skills_required">Skills Required:</label>
                        <textarea id="skills_required" name="skills_required" rows="4" placeholder="Enter skills separated by commas (e.g., React, Node.js, MongoDB)" required></textarea>
                    </div>
                </div>

                <div class="form-group">
                    <div class="half-width">
                        <label for="type">Project Type:</label>
                        <select id="type" name="type" required>
                            <option value="">Select a type</option>
                            <option value="Web Development">Web Development</option>
                            <option value="Mobile Apps">Mobile Apps</option>
                            <option value="AI/ML">AI/ML</option>
                            <option value="Blockchain">Blockchain</option>
                            <option value="Data Science">Data Science</option>
                        </select>
                    </div>
                    <div class="half-width">
                        <label for="status">Status:</label>
                        <select id="status" name="status" required>
                            <option value="">Select status</option>
                            <option value="Active">Active</option>
                            <option value="Inactive">Inactive</option>
                        </select>
                    </div>
                </div>

                <div class="form-footer">
                    <button type="submit" class="submit-btn">Add Project</button>
                    <a href="projects.html" class="cancel-btn">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- JS FOR DYNAMIC SUBMIT + VALIDATION -->
<script>
document.getElementById('addProjectForm').addEventListener('submit', async function(e) {
    e.preventDefault(); // Prevent classic form submit

    const form = e.target;
    const project = document.getElementById('project').value.trim();
    const description = document.getElementById('description').value.trim();

    // Validation for Project Name
    if (project.length < 3) {
        alert('❌ Project name must be at least 3 characters.');
        return;
    }

    // Validation for Description
    if (description.length < 10) {
        alert('❌ Description must be at least 10 characters.');
        return;
    }

    const formData = new FormData(form);

    try {
        const response = await fetch('process_project.php', {
            method: 'POST',
            body: formData
        });

        const result = await response.json(); // Assuming backend sends JSON

        if (response.ok && result.success) {
            alert('✅ Project added successfully!');
            window.location.href = 'projects.html'; // Redirect after success
        } else {
            alert('❌ Failed to add project: ' + (result.message || 'Unknown error'));
        }

    } catch (error) {
        console.error('Error submitting form:', error);
        alert('❌ Error submitting form. Please try again.');
    }
});
</script>

</body>
</html>
