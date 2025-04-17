<?php
// Start session
session_start();

// Include database connection
require_once '../model/db_connection.php';

// Connect to the database
try {
    // Get database connection
    $conn = getConnection();
    
    if (!$conn) {
        throw new Exception("Failed to connect to database");
    }
    
    // Fetch all non-admin users
    $stmt = $conn->prepare("SELECT user_id, full_name, email, profile_picture, position FROM users WHERE is_admin = 0");
    $stmt->execute();
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Count for display
    $totalUsers = count($users);
    
} catch(Exception $e) {
    echo "Database connection failed: " . $e->getMessage();
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>LuckyJob - Networking</title>
    <link rel="stylesheet" href="../view/styles.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        .profile-cards {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 1.5rem;
            padding: 1rem;
        }

        .profile-card {
            background: white;
            border-radius: 24px;
            overflow: hidden;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
        }

        .profile-card.amazon { background-color: rgba(255, 153, 0, 0.1); }
        .profile-card.google { background-color: rgba(66, 133, 244, 0.1); }
        .profile-card.dribbble { background-color: rgba(234, 76, 137, 0.1); }

        .profile-content {
            padding: 1.5rem;
        }

        .card-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 1.5rem;
        }

        .date {
            color: var(--text-secondary);
            font-size: 0.875rem;
        }

        .bookmark {
            width: 32px;
            height: 32px;
            border-radius: 8px;
            border: none;
            display: flex;
            align-items: center;
            justify-content: center;
            background: rgba(0, 0, 0, 0.03);
            cursor: pointer;
            transition: all 0.2s ease;
            color: rgba(0, 0, 0, 0.6);
        }

        .bookmark:hover {
            background: rgba(0, 0, 0, 0.06);
            color: #FF3B30;
        }

        .bookmark.active {
            color: #FF3B30;
        }

        .bookmark.active svg {
            fill: #FF3B30;
        }

        .company-name {
            font-weight: 600;
            font-size: 1.125rem;
            margin-bottom: 0.5rem;
        }

        .profile-title {
            font-size: 1.5rem;
            font-weight: 600;
            margin-bottom: 1.5rem;
            color: var(--text-primary);
        }

        .tag-container {
            display: flex;
            flex-wrap: wrap;
            gap: 0.5rem;
            margin-bottom: 1rem;
        }

        .tag {
            background: rgba(255, 255, 255, 0.8);
            padding: 0.5rem 1rem;
            border-radius: 12px;
            font-size: 0.875rem;
            color: var(--text-primary);
        }

        .profile-footer {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: 1.5rem;
        }

        .location {
            color: var(--text-secondary);
            font-size: 0.875rem;
        }

        .rate {
            font-size: 1.25rem;
            font-weight: 600;
            margin-bottom: 0.25rem;
        }

        .details {
            background: #000000;
            color: white;
            border: none;
            padding: 0.5rem 1.25rem;
            border-radius: 6px;
            font-size: 0.875rem;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .details:hover {
            background: #333333;
            transform: translateY(-1px);
        }

        /* Dark mode adjustments */
        :root[data-theme="dark"] .details {
            background: #4F6EF7;
            color: white;
        }

        :root[data-theme="dark"] .details:hover {
            background: #3D5CE5;
        }

        /* User Cards - Matching Job Cards Style */
        .user-cards {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 1.25rem;
            width: 100%;
            transition: all 0.3s ease;
        }

        .user-card {
            background: white;
            border-radius: 16px;
            padding: 1.5rem;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            display: flex;
            flex-direction: column;
            transition: transform 0.2s ease, box-shadow 0.2s ease;
            height: 100%;
        }

        .user-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
        }

        .user-content {
            background-color: var(--apple-bg);
            border-radius: 20px;
            padding: 1.25rem;
            margin-bottom: 1rem;
            display: flex;
            flex-direction: column;
            flex: 1;
        }

        .user-content.amazon { background-color: #ddd7ff; }
        .user-content.google { background-color:#ddd7ff; }
        .user-content.dribbble { background-color:#ddd7ff; }
        .user-content.airbnb { background-color: #ddd7ff; }
        .user-content.mlh { background-color: #ddd7ff; }
        .user-content.microsoft { background-color: #ddd7ff; }

        .card-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.25rem;
        }

        .date {
            font-size: 0.875rem;
            color: rgba(0, 0, 0, 0.6);
            background: rgba(255, 255, 255, 0.8);
            padding: 0.5rem 0.875rem;
            border-radius: 20px;
        }

        .bookmark {
            background: rgba(0, 0, 0, 0.05);
            border: none;
            width: 32px;
            height: 32px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: rgba(0, 0, 0, 0.6);
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .bookmark:hover {
            background: rgba(0, 0, 0, 0.1);
            color: rgba(0, 0, 0, 0.8);
        }

        .bookmark.active {
            color: #FF3B30;
        }

        .bookmark.active svg {
            fill: #FF3B30;
        }

        .user-name-title {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 1.25rem;
        }

        .user-name-title .title-container {
            flex: 1;
        }

        .user-name {
            font-size: 0.875rem;
            color: rgba(0, 0, 0, 0.6);
            margin-bottom: 0.25rem;
            display: block;
        }

        .user-name-title h4 {
            font-size: 1.5rem;
            font-weight: 600;
            color: rgba(0, 0, 0, 0.9);
            line-height: 1.2;
            margin: 0;
        }

        :root[data-theme="dark"] .user-name {
            color: rgba(255, 255, 255, 0.6);
        }

        :root[data-theme="dark"] .user-name-title h4,
        :root[data-theme="dark"] .user-rate {
            color: rgba(255, 255, 255, 0.9);
        }

        .user-profile-pic {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            object-fit: cover;
            background: #fff;
        }

        .user-tags {
            display: flex;
            flex-wrap: wrap;
            gap: 0.5rem;
            margin-top: auto;
        }

        .user-tags span {
            background: rgba(255, 255, 255, 0.7);
            padding: 0.5rem 1rem;
            border-radius: 20px;
            font-size: 0.875rem;
            color: rgba(0, 0, 0, 0.7);
            font-weight: 450;
        }

        .card-footer {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0.5rem 0;
            margin-top: auto;
        }

        .user-rate {
            font-size: 1.25rem;
            font-weight: 600;
            color: rgba(0, 0, 0, 0.9);
        }

        .user-location {
            font-size: 0.875rem;
            color: rgba(0, 0, 0, 0.5);
        }

        .details {
            background: #000000;
            color: white;
            border: none;
            padding: 0.5rem 1.25rem;
            border-radius: 6px;
            font-size: 0.875rem;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .details:hover {
            background: #333333;
            transform: translateY(-1px);
        }

        /* Dark mode adjustments */
        :root[data-theme="dark"] .user-card {
            background-color: var(--bg-card);
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.2);
        }

        :root[data-theme="dark"] .user-location {
            color: rgba(255, 255, 255, 0.5);
        }

        :root[data-theme="dark"] .user-tags span {
            background: rgba(255, 255, 255, 0.1);
            color: rgba(255, 255, 255, 0.8);
        }

        :root[data-theme="dark"] .details {
            background: #4F6EF7;
            color: white;
        }

        :root[data-theme="dark"] .details:hover {
            background: #3D5CE5;
        }

        /* List layout styles */
        .user-cards.list-layout {
            display: flex;
            flex-direction: column;
            gap: 1rem;
        }

        .user-cards.list-layout .user-card {
            width: 100%;
            margin: 0;
        }

        .user-cards.list-layout .user-content {
            margin-bottom: 0;
            border-radius: 20px;
            padding: 1.5rem;
        }

        .user-cards.list-layout .card-header {
            margin-bottom: 0.75rem;
        }

        .user-cards.list-layout .date {
            font-size: 0.813rem;
            padding: 0.375rem 0.75rem;
            background: rgba(255, 255, 255, 0.8);
            border-radius: 100px;
        }

        .user-cards.list-layout .bookmark {
            width: 32px;
            height: 32px;
            border-radius: 8px;
            background: rgba(0, 0, 0, 0.03);
        }

        .user-cards.list-layout .user-name {
            font-size: 0.875rem;
            font-weight: 500;
            color: rgba(0, 0, 0, 0.6);
            margin-bottom: 0.25rem;
        }

        .user-cards.list-layout .user-profile-pic {
            width: 48px;
            height: 48px;
            border-radius: 8px;
            padding: 0;
            background: none;
            object-fit: cover;
            margin-left: 1rem;
        }

        .user-cards.list-layout .user-name-title {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 1rem;
        }

        .user-cards.list-layout .user-tags {
            margin-top: 1rem;
        }

        .user-cards.list-layout .user-tags span {
            font-size: 0.813rem;
            padding: 0.375rem 0.75rem;
            background: rgba(255, 255, 255, 0.8);
            border-radius: 100px;
            color: rgba(0, 0, 0, 0.7);
        }

        .user-cards.list-layout .card-footer {
            padding: 1rem 1.5rem;
            background: white;
            border-radius: 0 0 20px 20px;
            margin-top: 0;
            border-top: 1px solid rgba(0, 0, 0, 0.05);
        }

        /* Dark mode adjustments */
        :root[data-theme="dark"] .user-cards.list-layout .date,
        :root[data-theme="dark"] .user-cards.list-layout .bookmark,
        :root[data-theme="dark"] .user-cards.list-layout .user-profile-pic,
        :root[data-theme="dark"] .user-cards.list-layout .user-tags span {
            background: rgba(255, 255, 255, 0.1);
        }

        :root[data-theme="dark"] .user-cards.list-layout .user-name {
            color: rgba(255, 255, 255, 0.6);
        }

        :root[data-theme="dark"] .user-cards.list-layout .card-footer {
            background: var(--bg-card);
            border-top: 1px solid rgba(255, 255, 255, 0.1);
        }

        /* Responsive adjustments */
        @media (max-width: 1200px) {
            .user-cards {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        @media (max-width: 768px) {
            .user-cards {
                grid-template-columns: 1fr;
            }
        }

        /* Dark mode adjustments */
        :root[data-theme="dark"] .user-content {
            background-color: var(--bg-card);
        }

        :root[data-theme="dark"] .user-content.amazon { background: var(--amazon-bg); }
        :root[data-theme="dark"] .user-content.google { background: var(--google-bg); }
        :root[data-theme="dark"] .user-content.dribbble { background: var(--dribbble-bg); }

        :root[data-theme="dark"] .user-name-title h4 {
            color: rgba(0, 0, 0, 0.9);
        }

        :root[data-theme="dark"] .user-name {
            color: rgba(0, 0, 0, 0.7);
        }

        :root[data-theme="dark"] .user-tags span {
            background: rgba(255, 255, 255, 0.8);
            color: rgba(0, 0, 0, 0.7);
        }

        :root[data-theme="dark"] .date {
            background: rgba(255, 255, 255, 0.8);
            color: rgba(0, 0, 0, 0.7);
        }

        :root[data-theme="dark"] .bookmark {
            background: rgba(255, 255, 255, 0.8);
            color: rgba(0, 0, 0, 0.7);
        }

        :root[data-theme="dark"] .card-footer {
            background: var(--bg-card);
            border-top: 1px solid rgba(255, 255, 255, 0.1);
        }

        :root[data-theme="dark"] .user-rate {
            color: var(--text-primary);
        }

        :root[data-theme="dark"] .user-location {
            color: rgba(255, 255, 255, 0.5);
        }

        :root[data-theme="dark"] .details {
            background: #4F6EF7;
            color: white;
        }

        :root[data-theme="dark"] .details:hover {
            background: #3D5CE5;
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
                    <a href="#">Dashboard</a>
                    <a href="../view/index.html">Jobs</a>
                    <a href="../view/projects.html">Projects</a>
                    <a href="../view/courses.html">Courses</a>
                    <a href="../view/hackathon.html">Hackathons</a>
                    <a href="../view/articles.html">Articles</a>
                    <a href="../view/networking.php" class="active">Networking</a>
                </nav>
            </div>
            <div class="nav-right">
                <div class="notification-wrapper">
                    <button class="icon-button notification">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"></path>
                            <path d="M13.73 21a2 2 0 0 1-3.46 0"></path>
                        </svg>
                    </button>
                    <span class="notification-dot"></span>
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
                    <a href="../view/user-profile.php">
                        <img src="../assets/profil.jpg" alt="User profile" class="avatar">
                    </a>
                </div>
            </div>
        </nav>

        <div class="content">
            <aside class="sidebar">
                <div class="promo-card">
                    <div class="grid-pattern"></div>
                    <h2>Connect with Engineers</h2>
                    <button class="learn-more">Update Profile</button>
                </div>

                <div class="filters">
                    <div class="filters-header">
                        <h3>Filters</h3>
                        <button class="clear-all">Clear All</button>
                    </div>

                    <div class="filter-section">
                        <div class="filter-header">
                            <h4>Expertise</h4>
                            <button class="expand-btn">
                                <svg width="16" height="16" viewBox="0 0 16 16" fill="none">
                                    <path d="M4 6l4 4 4-4" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                                </svg>
                            </button>
                        </div>
                        <div class="filter-options">
                            <label class="checkbox-option">
                                <input type="checkbox">
                                <span class="label-text">Software Development</span>
                            </label>
                            <label class="checkbox-option">
                                <input type="checkbox">
                                <span class="label-text">Data Science</span>
                            </label>
                            <label class="checkbox-option">
                                <input type="checkbox">
                                <span class="label-text">DevOps</span>
                            </label>
                            <label class="checkbox-option">
                                <input type="checkbox">
                                <span class="label-text">Machine Learning</span>
                            </label>
                        </div>
                    </div>

                    <div class="filter-section">
                        <div class="filter-header">
                            <h4>Location</h4>
                            <button class="expand-btn">
                                <svg width="16" height="16" viewBox="0 0 16 16" fill="none">
                                    <path d="M4 6l4 4 4-4" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                                </svg>
                            </button>
                        </div>
                        <div class="filter-options">
                            <label class="checkbox-option">
                                <input type="checkbox">
                                <span class="label-text">Remote</span>
                            </label>
                            <label class="checkbox-option">
                                <input type="checkbox">
                                <span class="label-text">Europe</span>
                            </label>
                            <label class="checkbox-option">
                                <input type="checkbox">
                                <span class="label-text">North America</span>
                            </label>
                            <label class="checkbox-option">
                                <input type="checkbox">
                                <span class="label-text">Asia</span>
                            </label>
                        </div>
                    </div>
                </div>
            </aside>

            <section class="jobs">
                <div class="jobs-header">
                    <h2>Engineers to Connect With <span class="count"><?php echo $totalUsers; ?></span></h2>
                    <div class="actions">
                        <button class="view-toggle">
                            <svg class="grid-icon" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <rect x="3" y="3" width="7" height="7"></rect>
                                <rect x="14" y="3" width="7" height="7"></rect>
                                <rect x="14" y="14" width="7" height="7"></rect>
                                <rect x="3" y="14" width="7" height="7"></rect>
                            </svg>
                            <svg class="list-icon" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="display: none;">
                                <line x1="3" y1="6" x2="21" y2="6"></line>
                                <line x1="3" y1="12" x2="21" y2="12"></line>
                                <line x1="3" y1="18" x2="21" y2="18"></line>
                            </svg>
                        </button>
                    </div>
                </div>

                <div class="user-cards">
                    <?php 
                    // Define background content classes to cycle through
                    $bgClasses = ['amazon', 'google', 'dribbble', 'airbnb', 'mlh', 'microsoft'];
                    $bgCount = count($bgClasses);
                    
                    // Loop through all users and create cards
                    foreach ($users as $index => $user): 
                        // Use modulo to cycle through background classes
                        $bgClass = $bgClasses[$index % $bgCount];
                        
                        // Check if profile picture exists, otherwise use default
                        $profilePicUrl = '../ressources/profil.jpg'; // Default image
                        if (!empty($user['profile_picture'])) {
                            $picturePath = '../ressources/profile_pictures/' . $user['profile_picture'];
                            if (file_exists($picturePath)) {
                                $profilePicUrl = $picturePath;
                            }
                        }
                    ?>
                    <div class="user-card">
                        <div class="user-content <?php echo $bgClass; ?>">
                            <div class="card-header">
                                <span class="date">Member</span>
                                <button class="bookmark" data-user-id="<?php echo $user['user_id']; ?>">
                                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"></path>
                                    </svg>
                                </button>
                            </div>
                            
                            <div class="user-name-title">
                                <div class="title-container">
                                    <span class="user-name"><?php echo htmlspecialchars($user['full_name']); ?></span>
                                    <h4><?php echo htmlspecialchars($user['position'] ?? 'Engineer'); ?></h4>
                                </div>
                                <img src="<?php echo $profilePicUrl; ?>" alt="<?php echo htmlspecialchars($user['full_name']); ?>" class="user-profile-pic">
                            </div>
                            
                            <div class="user-tags">
                                <?php
                                // Example skills - In a real implementation, these would come from the database
                                $sampleSkills = ['JavaScript', 'Python', 'React', 'Node.js', 'Machine Learning', 'AWS', 'DevOps', 'SQL'];
                                // Randomly select 2-4 skills
                                $numSkills = rand(2, 4);
                                $randomKeys = array_rand($sampleSkills, $numSkills);
                                if (!is_array($randomKeys)) {
                                    $randomKeys = [$randomKeys];
                                }
                                foreach ($randomKeys as $key) {
                                    echo '<span>' . $sampleSkills[$key] . '</span>';
                                }
                                ?>
                            </div>
                        </div>
                        
                        <div class="card-footer">
                            <div>
                                <div class="user-location">Engineer</div>
                            </div>
                            <a href="user-profile-consult.php?id=<?php echo $user['user_id']; ?>" class="details">Profile</a>
                        </div>
                    </div>
                    <?php endforeach; ?>

                    <?php if (empty($users)): ?>
                    <div class="no-users-message">
                        <p>No users found. Be the first to join!</p>
                    </div>
                    <?php endif; ?>
                </div>
            </section>
        </div>
    </div>

    <script>
        // Theme toggle functionality
        const themeToggle = document.getElementById('themeToggle');
        const root = document.documentElement;
        const sunIcon = document.querySelector('.sun-icon');
        const moonIcon = document.querySelector('.moon-icon');
        
        // Check for saved theme preference
        const savedTheme = localStorage.getItem('theme');
        if (savedTheme) {
            root.setAttribute('data-theme', savedTheme);
            sunIcon.style.display = savedTheme === 'dark' ? 'none' : 'block';
            moonIcon.style.display = savedTheme === 'dark' ? 'block' : 'none';
        }

        themeToggle.addEventListener('click', () => {
            const currentTheme = root.getAttribute('data-theme');
            const newTheme = currentTheme === 'dark' ? 'light' : 'dark';
            
            root.setAttribute('data-theme', newTheme);
            localStorage.setItem('theme', newTheme);
            
            sunIcon.style.display = newTheme === 'dark' ? 'none' : 'block';
            moonIcon.style.display = newTheme === 'dark' ? 'block' : 'none';
        });

        // View toggle functionality
        const viewToggle = document.querySelector('.view-toggle');
        const gridIcon = document.querySelector('.grid-icon');
        const listIcon = document.querySelector('.list-icon');
        const userCards = document.querySelector('.user-cards');
        
        viewToggle.addEventListener('click', () => {
            userCards.classList.toggle('list-layout');
            gridIcon.style.display = userCards.classList.contains('list-layout') ? 'none' : 'block';
            listIcon.style.display = userCards.classList.contains('list-layout') ? 'block' : 'none';
        });

        // Heart button functionality
        const heartButtons = document.querySelectorAll('.bookmark');
        heartButtons.forEach(button => {
            button.addEventListener('click', () => {
                button.classList.toggle('active');
            });
        });
    </script>
</body>
</html>