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

        // Execute query
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
            max-width: 800px;
            margin: 40px auto;
            padding: 30px;
            background: white;
            border-radius: 16px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }
        
        .form-title {
            margin-bottom: 30px;
            font-size: 24px;
            font-weight: 600;
            color: #333;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            color: #444;
        }
        
        input[type="text"],
        input[type="url"],
        textarea,
        select {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 8px;
            font-size: 16px;
            font-family: 'Inter', sans-serif;
        }
        
        textarea {
            min-height: 120px;
            resize: vertical;
        }
        
        .submit-btn {
            background-color: #4CAF50;
            color: white;
            border: none;
            padding: 12px 24px;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 500;
            cursor: pointer;
            transition: background-color 0.2s;
        }
        
        .submit-btn:hover {
            background-color: #45a049;
        }
        
        .cancel-btn {
            background-color: #f44336;
            color: white;
            border: none;
            padding: 12px 24px;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 500;
            cursor: pointer;
            margin-left: 10px;
            text-decoration: none;
            display: inline-block;
            transition: background-color 0.2s;
        }
        
        .cancel-btn:hover {
            background-color: #d32f2f;
        }
        
        .form-footer {
            margin-top: 30px;
            display: flex;
            justify-content: flex-end;
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
                    <a href="../view/Dashboard.html"> Dashboard </a>
                    <a href="../view/index.html">Jobs</a>
                    <a href="../view/projects.html" class="active">Projects</a>
                    <a href="../view/courses.html">Courses</a>
                    <a href="../view/hackathon.html">Hackathons</a>
                    <a href="../view/articles.html">Articles</a>
                    <a href="../view/networking.html">networking</a>
                </nav>
            </div>
            <div class="nav-right">
                <div class="notification-wrapper">
                    <button class="icon-button notification">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"></path>
                            <path d="M13.73 21a2 2 0 0 1-3.46 0"></path>
                        </svg>
                    </button>
                    <div class="notification-dot"></div>
                </div>
                <button class="icon-button theme-toggle" id="themeToggle">
                    <svg class="sun-icon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="12" cy="12" r="5"></circle>
                        <line x1="12" y1="1" x2="12" y2="3"></line>
                        <line x1="12" y1="21" x2="12" y2="23"></line>
                        <line x1="4.22" y1="4.22" x2="5.64" y2="5.64"></line>
                        <line x1="18.36" y1="18.36" x2="19.78" y2="19.78"></line>
                        <line x1="1" y1="12" x2="3" y2="12"></line>
                        <line x1="21" y1="12" x2="23" y2="12"></line>
                        <line x1="4.22" y1="19.78" x2="5.64" y2="18.36"></line>
                        <line x1="18.36" y1="5.64" x2="19.78" y2="4.22"></line>
                    </svg>
                    <svg class="moon-icon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="display: none;">
                        <path d="M21 12.79A9 9 0 1 1 11.21 3 A7 7 0 0 0 21 12.79z"></path>
                    </svg>
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
                
                <?php if(isset($_GET['error'])): ?>
                <div class="alert alert-danger">
                    <?php echo htmlspecialchars($_GET['message'] ?? 'An error occurred'); ?>
                </div>
                <?php endif; ?>
                
                <form action="process_project.php" method="POST">
                    <input type="hidden" name="action" value="create">
                    
                    <div class="form-group">
                        <label for="project">Project Name:</label>
                        <input type="text" id="project" name="project" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="description">Description:</label>
                        <textarea id="description" name="description" rows="4" required></textarea>
                    </div>
                    
                    <div class="form-group">
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
                    
                    <div class="form-group">
                        <label for="skills_required">Skills Required:</label>
                        <textarea id="skills_required" name="skills_required" rows="3" placeholder="Enter skills separated by commas (e.g., React, Node.js, MongoDB)" required></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label for="git_link">Git Repository Link:</label>
                        <input type="url" id="git_link" name="git_link" placeholder="https://github.com/username/repository" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="status">Status:</label>
                        <select id="status" name="status" required>
                            <option value="">Select status</option>
                            <option value="Active">Active</option>
                            <option value="Inactive">Inactive</option>
                        </select>
                    </div>
                    
                    <div class="form-footer">
                        <button type="submit" class="submit-btn">Add Project</button>
                        <a href="projects.php" class="cancel-btn">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</body>
</html>
