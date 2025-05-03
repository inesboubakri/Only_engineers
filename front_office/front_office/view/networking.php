<?php
// Start session
session_start();

// Include database connection
require_once '../model/db_connection.php';
// Include notifications functionality
require_once '../model/networking/notifications.php';

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

    <!-- Notification Panel -->
    <div id="notificationPanel" class="fixed right-0 top-16 w-80 bg-white shadow-lg rounded-l-lg transform translate-x-full transition-transform duration-300 z-50">
        <div class="flex justify-between items-center p-4 border-b">
            <h3 class="font-semibold text-gray-800">Notifications</h3>
            <button id="closeNotifications" class="text-gray-500 hover:text-gray-700">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="18" y1="6" x2="6" y2="18"></line>
                    <line x1="6" y1="6" x2="18" y2="18"></line>
                </svg>
            </button>
        </div>
        <div id="notificationList" class="overflow-y-auto max-h-80">
            <div class="p-4 text-center text-gray-500">
                <div class="animate-spin rounded-full h-6 w-6 border-b-2 border-blue-500 mx-auto mb-2"></div>
                Loading notifications...
            </div>
        </div>
    </div>

    <!-- Toast Notification for Success/Error messages -->
    <div id="toast" class="fixed bottom-4 right-4 bg-white rounded-lg shadow-lg p-4 transform translate-y-full transition-transform duration-300 max-w-xs z-50">
        <div class="flex items-center">
            <div id="toastIcon" class="flex-shrink-0 w-8 h-8 flex items-center justify-center rounded-full mr-3">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" id="toastIconSVG"></svg>
            </div>
            <div class="flex-1">
                <h4 id="toastTitle" class="font-medium text-sm"></h4>
                <p id="toastMessage" class="text-xs text-gray-600"></p>
            </div>
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

        // Notification & Toast System
        document.addEventListener('DOMContentLoaded', function() {
            // Toast notification system
            const toast = document.getElementById('toast');
            const toastTitle = document.getElementById('toastTitle');
            const toastMessage = document.getElementById('toastMessage');
            const toastIcon = document.getElementById('toastIcon');
            const toastIconSVG = document.getElementById('toastIconSVG');
            
            // Show toast notification
            window.showToast = function(title, message, type = 'info') {
                toastTitle.textContent = title;
                toastMessage.textContent = message;
                
                // Set icon and color based on type
                toastIcon.className = 'flex-shrink-0 w-8 h-8 flex items-center justify-center rounded-full mr-3';
                
                if (type === 'success') {
                    toastIcon.classList.add('bg-green-100', 'text-green-500');
                    toastIconSVG.innerHTML = '<path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline points="22 4 12 14.01 9 11.01"></polyline>';
                } else if (type === 'error') {
                    toastIcon.classList.add('bg-red-100', 'text-red-500');
                    toastIconSVG.innerHTML = '<circle cx="12" cy="12" r="10"></circle><line x1="12" y1="8" x2="12" y2="12"></line><line x1="12" y1="16" x2="12.01" y2="16"></line>';
                } else {
                    toastIcon.classList.add('bg-blue-100', 'text-blue-500');
                    toastIconSVG.innerHTML = '<circle cx="12" cy="12" r="10"></circle><line x1="12" y1="16" x2="12" y2="12"></line><line x1="12" y1="8" x2="12.01" y2="8"></line>';
                }
                
                // Show toast
                toast.classList.remove('translate-y-full');
                
                // Auto hide after 3 seconds
                setTimeout(() => {
                    toast.classList.add('translate-y-full');
                }, 3000);
            };
            
            // Notification panel functionality
            const notificationBtn = document.querySelector('.notification');
            const notificationPanel = document.getElementById('notificationPanel');
            const closeNotificationsBtn = document.getElementById('closeNotifications');
            const notificationDot = document.querySelector('.notification-dot');
            const notificationList = document.getElementById('notificationList');
            
            // Toggle notification panel
            notificationBtn.addEventListener('click', function() {
                if (notificationPanel.classList.contains('translate-x-full')) {
                    // Show panel
                    notificationPanel.classList.remove('translate-x-full');
                    // Load notifications
                    loadNotifications();
                } else {
                    // Hide panel
                    notificationPanel.classList.add('translate-x-full');
                }
            });
            
            // Close notification panel
            closeNotificationsBtn.addEventListener('click', function() {
                notificationPanel.classList.add('translate-x-full');
            });
            
            // Function to load notifications
            function loadNotifications() {
                fetch('../view/get_notifications.php')
                    .then(response => {
                        if (!response.ok) {
                            throw new Error('Network response was not ok');
                        }
                        return response.json();
                    })
                    .then(data => {
                        // Hide the notification dot when notifications are viewed
                        notificationDot.style.display = 'none';
                        
                        // Clear the loading indicator
                        notificationList.innerHTML = '';
                        
                        if (data.success) {
                            if (data.notifications.length === 0) {
                                notificationList.innerHTML = '<div class="p-4 text-center text-gray-500">No notifications</div>';
                            } else {
                                // Display notifications
                                data.notifications.forEach(notification => {
                                    const notificationItem = document.createElement('div');
                                    notificationItem.className = 'p-4 border-b hover:bg-gray-50 transition-colors';
                                    
                                    let iconHTML = '';
                                    
                                    // Set icon based on notification type
                                    switch(notification.type) {
                                        case 'follow':
                                            iconHTML = '<div class="w-8 h-8 bg-blue-100 rounded-full flex items-center justify-center text-blue-500 mr-3"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="8.5" cy="7" r="4"></circle><line x1="20" y1="8" x2="20" y2="14"></line><line x1="23" y1="11" x2="17" y2="11"></line></svg></div>';
                                            break;
                                        case 'connection_request':
                                            iconHTML = '<div class="w-8 h-8 bg-purple-100 rounded-full flex items-center justify-center text-purple-500 mr-3"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="8.5" cy="7" r="4"></circle><path d="M20 8v6"></path><path d="M23 11h-6"></path></svg></div>';
                                            break;
                                        case 'connection_accepted':
                                            iconHTML = '<div class="w-8 h-8 bg-green-100 rounded-full flex items-center justify-center text-green-500 mr-3"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="8.5" cy="7" r="4"></circle><polyline points="17 11 19 13 23 9"></polyline></svg></div>';
                                            break;
                                        case 'connection_rejected':
                                            iconHTML = '<div class="w-8 h-8 bg-red-100 rounded-full flex items-center justify-center text-red-500 mr-3"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="8.5" cy="7" r="4"></circle><line x1="18" y1="8" x2="23" y2="13"></line><line x1="23" y1="8" x2="18" y2="13"></line></svg></div>';
                                            break;
                                        default:
                                            iconHTML = '<div class="w-8 h-8 bg-gray-100 rounded-full flex items-center justify-center text-gray-500 mr-3"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="16" x2="12" y2="12"></line><line x1="12" y1="8" x2="12.01" y2="8"></line></svg></div>';
                                    }
                                    
                                    // Create notification content with buttons for connection requests
                                    let actionsHTML = '';
                                    if (notification.type === 'connection_request' && notification.sender_id) {
                                        actionsHTML = `
                                            <div class="flex mt-2 space-x-2">
                                                <button class="accept-request-btn px-3 py-1 bg-blue-500 text-white text-xs rounded hover:bg-blue-600 transition" data-user-id="${notification.sender_id}">Accept</button>
                                                <button class="reject-request-btn px-3 py-1 bg-gray-200 text-gray-800 text-xs rounded hover:bg-gray-300 transition" data-user-id="${notification.sender_id}">Decline</button>
                                            </div>
                                        `;
                                    }
                                    
                                    notificationItem.innerHTML = `
                                        <div class="flex items-start">
                                            ${iconHTML}
                                            <div class="flex-1">
                                                <p class="text-sm mb-1">${notification.message}</p>
                                                <p class="text-xs text-gray-500">${getTimeAgo(notification.created_at)}</p>
                                                ${actionsHTML}
                                            </div>
                                        </div>
                                    `;
                                    
                                    notificationList.appendChild(notificationItem);
                                });
                                
                                // Add event listeners for connection request buttons
                                document.querySelectorAll('.accept-request-btn').forEach(button => {
                                    button.addEventListener('click', function() {
                                        handleConnectionAction('accept_request', this.getAttribute('data-user-id'));
                                    });
                                });
                                
                                document.querySelectorAll('.reject-request-btn').forEach(button => {
                                    button.addEventListener('click', function() {
                                        handleConnectionAction('reject_request', this.getAttribute('data-user-id'));
                                    });
                                });
                            }
                        } else {
                            notificationList.innerHTML = '<div class="p-4 text-center text-red-500">Error loading notifications</div>';
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        notificationList.innerHTML = '<div class="p-4 text-center text-red-500">Failed to load notifications</div>';
                    });
            }
            
            // Handle connection actions (accept/reject)
            function handleConnectionAction(action, userId) {
                const formData = new FormData();
                formData.append('action', action);
                formData.append('user_id', userId);
                
                fetch('../model/networking/connections.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        showToast('Success', data.message, 'success');
                        // Reload notifications to update the list
                        loadNotifications();
                    } else {
                        showToast('Error', data.message, 'error');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    showToast('Error', 'An error occurred. Please try again later.', 'error');
                });
            }
            
            // Helper function to format time ago
            function getTimeAgo(timestamp) {
                const now = new Date();
                const past = new Date(timestamp);
                const diffInSeconds = Math.floor((now - past) / 1000);
                
                if (diffInSeconds < 60) {
                    return 'Just now';
                }
                
                const diffInMinutes = Math.floor(diffInSeconds / 60);
                if (diffInMinutes < 60) {
                    return diffInMinutes + ' minute' + (diffInMinutes > 1 ? 's' : '') + ' ago';
                }
                
                const diffInHours = Math.floor(diffInMinutes / 60);
                if (diffInHours < 24) {
                    return diffInHours + ' hour' + (diffInHours > 1 ? 's' : '') + ' ago';
                }
                
                const diffInDays = Math.floor(diffInHours / 24);
                if (diffInDays < 7) {
                    return diffInDays + ' day' + (diffInDays > 1 ? 's' : '') + ' ago';
                }
                
                const diffInWeeks = Math.floor(diffInDays / 7);
                if (diffInWeeks < 4) {
                    return diffInWeeks + ' week' + (diffInWeeks > 1 ? 's' : '') + ' ago';
                }
                
                const diffInMonths = Math.floor(diffInDays / 30);
                return diffInMonths + ' month' + (diffInMonths > 1 ? 's' : '') + ' ago';
            }
        });
    </script>
</body>
</html>