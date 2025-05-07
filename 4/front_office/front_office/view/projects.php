<?php
require_once('../../../back_office/controller/db_connection.php');

// Fetch all projects
$projects = [];
try {
    $sql = "SELECT * FROM projet ORDER BY id DESC";
    $stmt = $conn->prepare($sql);
    $stmt->execute();
    $projects = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $error = "Error fetching projects: " . $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Only Engineers - Projects</title>
    <link rel="stylesheet" href="../view/styles.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        .promo-card {
            position: relative;
            background: white;
            border-radius: 16px;
            padding: 1.5rem;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            display: flex;
            flex-direction: column;
            min-height: 300px;
            overflow: hidden;
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }
        @import url('https://fonts.googleapis.com/css2?family=Inter:opsz,wght@14..32,100..900&display=swap');
        .chatbot-popup {
            position: fixed;
            bottom: 20px;
            right: 20px;
            width: 400px;
            height: 600px;
            background-color: #fff;
            overflow: hidden;
            border-radius: 15px;
            box-shadow: 0 0 128px 0 rgba(0, 0, 0, 0.1),
                        0 32px 64px -48px rgba(0, 0, 0, 0.5);
            display: none;
            z-index: 1000;
        }

        .chatbot-popup.visible {
            display: block;
        }

        .chatbot-button {
            background-color: white;
            padding: 0.375rem 1.25rem;
            border-radius: 100px;
            font-size: 0.875rem;
            font-weight: 500;
            color: rgba(0, 0, 0, 0.7);
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
            border-color: transparent;
        }

        .empty-state {
            text-align: center;
            padding: 40px;
            background-color: white;
            border-radius: 16px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            margin: 20px 0;
        }
        
        .empty-state h3 {
            margin-bottom: 15px;
            color: #555;
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
                        <path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"></path>
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
            <aside class="sidebar">
                <div class="filters">
                    <div class="filters-header">
                        <h3>Filters</h3>
                        <button class="clear-all">Clear All</button>
                    </div>
                    <div class="search-container">
                        <div class="search-bar">
                            <input type="text" value="...">
                        </div>
                    </div>
                    <div class="filter-section">
                        <div class="filter-header">
                            <h4>Project Type</h4>
                            <button class="expand-btn">
                                <svg width="16" height="16" viewBox="0 0 16 16" fill="none">
                                    <path d="M4 6l4 4 4-4" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                                </svg>
                            </button>
                        </div>
                        <div class="filter-options">
                            <label class="radio-option">
                                <input type="radio" name="projectType">
                                <span class="label-text">All</span>
                                <span class="count">(245)</span>
                            </label>
                            <label class="radio-option selected">
                                <input type="radio" name="projectType" checked>
                                <span class="label-text">Web Development</span>
                                <span class="count">(120)</span>
                            </label>
                            <label class="radio-option">
                                <input type="radio" name="projectType">
                                <span class="label-text">Mobile Apps</span>
                                <span class="count">(85)</span>
                            </label>
                            <label class="radio-option">
                                <input type="radio" name="projectType">
                                <span class="label-text">AI/ML</span>
                                <span class="count">(40)</span>
                            </label>
                        </div>
                    </div>

                    <div class="filter-section">
                        <div class="filter-header">
                            <h4>Difficulty</h4>
                            <button class="expand-btn">
                                <svg width="16" height="16" viewBox="0 0 16 16" fill="none">
                                    <path d="M12 10L8 6l-4 4" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                                </svg>
                            </button>
                        </div>
                        <div class="filter-options">
                            <label class="radio-option">
                                <input type="radio" name="difficulty">
                                <span class="label-text">Beginner</span>
                                <span class="count">(85)</span>
                            </label>
                            <label class="radio-option">
                                <input type="radio" name="difficulty">
                                <span class="label-text">Intermediate</span>
                                <span class="count">(95)</span>
                            </label>
                            <label class="radio-option">
                                <input type="radio" name="difficulty">
                                <span class="label-text">Advanced</span>
                                <span class="count">(65)</span>
                            </label>
                        </div>
                    </div>

                    <div class="filter-section">
                        <div class="filter-header">
                            <h4>Tech Stack</h4>
                            <button class="expand-btn">
                                <svg width="16" height="16" viewBox="0 0 16 16" fill="none">
                                    <path d="M12 10L8 6l-4 4" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                                </svg>
                            </button>
                        </div>
                        <div class="filter-options">
                            <label class="radio-option">
                                <input type="radio" name="stack">
                                <span class="label-text">React</span>
                                <span class="count">(75)</span>
                            </label>
                            <label class="radio-option">
                                <input type="radio" name="stack">
                                <span class="label-text">Python</span>
                                <span class="count">(65)</span>
                            </label>
                            <label class="radio-option">
                                <input type="radio" name="stack">
                                <span class="label-text">Node.js</span>
                                <span class="count">(55)</span>
                            </label>
                        </div>
                    </div>
                </div>
            </aside>

            <section class="jobs">
                <div class="jobs-header">
                    <h2>Projects <span class="count"><?php echo count($projects); ?></span></h2>
                    <button class="chatbot-button">
                        🤖 chatbot
                    </button>
                    <div class="chatbot-popup" id="chatbot-popup">
                        <div class="chat-header">
                            <div class="header-info">
                                <svg class="bot-avatar" xmlns="http://www.w3.org/2000/svg" width="35" height="35" viewBox="0 0 1024 1024">
                                    <path d="M738.3 287.6H285.7c-59 0-106.8 47.8-106.8 106.8v303.1c0 59 47.8 106.8 106.8 106.8h81.5v111.1c0 .7.8 1.1 1.4.7l166.9-110.6 41.8-.8h117.4l43.6-.4c59 0 106.8-47.8 106.8-106.8V394.5c0-59-47.8-106.9-106.8-106.9zM351.7 448.2c0-29.5 23.9-53.5 53.5-53.5s53.5 23.9-53.5-53.5-23.9 53.5-53.5 53.5-53.5-23.9-53.5-53.5zm157.9 267.1c-67.8 0-123.8-47.5-132.3-109h264.6c-8.6 61.5-64.5 109-132.3 109zm110-213.7c-29.5 0-53.5-23.9-53.5-53.5s23.9-53.5 53.5-53.5 53.5 23.9 53.5 53.5-23.9 53.5-53.5 53.5zM867.2 644.5V453.1h26.5c19.4 0 35.1 15.7 35.1 35.1v121.1c0 19.4-15.7 35.1-35.1 35.1h-26.5zM95.2 609.4V488.2c0-19.4 15.7-35.1 35.1-35.1h26.5v191.3h-26.5c-19.4 0-35.1-15.7-35.1-35.1zM561.5 149.6c0 23.4-15.6 43.3-36.9 49.7v44.9h-30v-44.9c-21.4-6.5-36.9-26.3-36.9-49.7 0-28.6 23.3-51.9 51.9-51.9s51.9 23.3 51.9 51.9z"></path>
                                </svg>
                                <h2 class="logo-texte">OnlyEngineersBot</h2>
                            </div>
                        </div>
                        <div class="chat-body">
                            <div class="message bot-message">
                                <div class="message-text">Hey there 🔴 <br /> How can I help you today?</div>
                            </div>
                        </div>
                        <div class="chat-footer">
                            <form action="#" class="chat-form">
                                <textarea placeholder="Message..." class="message-input" required></textarea>
                                <div class="chat-controls">                                   
                                    <button type="submit" id="send-message" class="material-symbols-rounded">⬆️</button>
                                </div>
                            </form>
                        </div>
                    </div>
                    <script>
                        document.querySelector('.chatbot-button').addEventListener('click', function() {
                            document.getElementById('chatbot-popup').classList.toggle('visible');
                        });
                    </script>
                </div>

                <?php if (!empty($error)): ?>
                <div class="error-message">
                    <strong>Error:</strong> <?php echo htmlspecialchars($error); ?>
                </div>
                <?php endif; ?>

                <div class="job-cards">
                    <?php if (!empty($projects)): ?>
                        <?php 
                        $companyStyles = ["google", "microsoft", "amazon", "facebook", "apple"];
                        $counter = 0;
                        foreach ($projects as $project): 
                            $companyStyle = $companyStyles[$counter % count($companyStyles)];
                            $counter++;
                            $skillsArray = explode(',', $project['skills_required']);
                            $skillsCount = min(count($skillsArray), 3);
                        ?>
                        <div class="job-card">
                            <div class="job-content <?php echo $companyStyle; ?>">
                                <div class="card-header">
                                    <span class="date">Project #<?php echo htmlspecialchars($project['id']); ?></span>
                                </div>
                                <h3><?php echo htmlspecialchars($project['project']); ?></h3>
                                <div class="job-title">
                                    <h4><?php echo htmlspecialchars(substr($project['description'], 0, 50) . (strlen($project['description']) > 50 ? '...' : '')); ?></h4>
                                    <img src="../ressources/default.png" alt="Project Logo" class="company-logo">
                                </div>
                                <div class="tags">
                                    <?php for($i = 0; $i < $skillsCount; $i++): 
                                        $skill = trim($skillsArray[$i]);
                                        if($skill !== ''): ?>
                                        <span><?php echo htmlspecialchars($skill); ?></span>
                                    <?php endif; endfor; ?>
                                    <?php if (!empty($project['status'])): ?>
                                        <span><?php echo htmlspecialchars($project['status']); ?></span>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <div class="card-footer">
                                <div class="job-details">
                                    <div class="salary">Project</div>
                                    <div class="location"><?php echo htmlspecialchars($project['type']); ?></div>
                                </div>
                                <div>
                                    <a href="detail.php?id=<?php echo $project['id']; ?>" class="details">Détail</a>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                    
                    <!-- Promo card -->
                    <div class="promo-card">
                        <div class="promo-content">
                            <h2>Reach thousands of new Projects</h2>
                            <a href="add_project.php" class="learn-more">Add a Project</a>
                        </div>
                    </div>
                    
                    <?php if (empty($projects)): ?>
                    <div class="empty-state">
                        <h3>No projects found</h3>
                        <p>Start by adding a new project to showcase your work!</p>
                        <a href="add_project.php" class="add-btn">Add Your First Project</a>
                    </div>
                    <?php endif; ?>
                </div>
            </section>
        </div>
    </div>

    <script src="../controller/projects.js"></script>
    <?php $conn= null; ?>
</body>
</html>
