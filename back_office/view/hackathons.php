<?php
// Start session
session_start();

// Check if user is logged in and is an admin
if (!isset($_SESSION['user_id']) || $_SESSION['is_admin'] != 1) {
    // Redirect to login page if not logged in or not an admin
    header("Location: ../../front_office/front_office/view/signin.php");
    exit();
}

// Include database connection
require_once '../model/db_connectionback.php';

// Connect to the database
try {
    // Get database connection
    $conn = getConnection();
    
    if (!$conn) {
        throw new Exception("Failed to connect to database");
    }
    
    // Count total hackathons
    $totalHackathonsStmt = $conn->query("SELECT COUNT(*) FROM hackathons");
    $totalHackathons = $totalHackathonsStmt->fetchColumn();
    
    // Count active hackathons (where end_date is in the future)
    $activeHackathonsStmt = $conn->query("SELECT COUNT(*) FROM hackathons WHERE end_date >= CURDATE()");
    $activeHackathons = $activeHackathonsStmt->fetchColumn();
    
    // Calculate percentage increase (hardcoded for demo purposes)
    $totalHackathonsPercentage = "+15.3%";
    $activeHackathonsPercentage = "+8.7%";
    
    // Fetch all hackathons
    $stmt = $conn->prepare("SELECT * FROM hackathons ORDER BY start_date DESC");
    $stmt->execute();
    $hackathons = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
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
    <title>Hackathons Management | Dashboard</title>
    <link rel="stylesheet" href="styles.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <!-- Ajout de Leaflet CSS et JavaScript pour OpenStreetMap -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.7.1/dist/leaflet.css"
          integrity="sha512-xodZBNTC5n17Xt2atTPuE1HxjVMSvLVW9ocqUKLsCC5CXdbqCmblAshOMAS6/keqq/sMZMZ19scR4PsZChSR7A=="
          crossorigin="anonymous"/>
    <script src="https://unpkg.com/leaflet@1.7.1/dist/leaflet.js"
            integrity="sha512-XQoYMqMTK8LvdxXYG3nZ448hOEQiglfqkJs1NOQV44cWnUrBc8PkAOcXy20w0vlaXaVUearIOBhiXZ5V3ynxwA=="
            crossorigin="anonymous"></script>
    <style>
        /* Modal styling */
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            z-index: 1000;
            opacity: 0;
            transition: opacity 0.3s ease;
        }
        
        .modal.active {
            display: flex;
            align-items: center;
            justify-content: center;
            opacity: 1;
        }
        
        .modal-content {
            width: 90%;
            max-width: 800px; /* Augmenté pour mieux afficher la carte */
            max-height: 90vh;
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.3);
            overflow-y: auto;
        }
        
        .dark-theme .modal-content {
            background-color: #2d3748;
            color: white;
        }
        
        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 15px 20px;
            border-bottom: 1px solid #e2e8f0;
        }
        
        .dark-theme .modal-header {
            border-color: #4a5568;
        }
        
        .modal-header h2 {
            margin: 0;
            font-size: 1.5rem;
            font-weight: 600;
        }
        
        .close-modal {
            background: none;
            border: none;
            font-size: 1.5rem;
            cursor: pointer;
            color: #718096;
        }
        
        .dark-theme .close-modal {
            color: #e2e8f0;
        }
        
        .modal-body {
            padding: 20px;
        }
        
        .modal-footer {
            display: flex;
            justify-content: flex-end;
            padding: 15px 20px;
            border-top: 1px solid #e2e8f0;
        }
        
        .dark-theme .modal-footer {
            border-color: #4a5568;
        }
        
        /* Map container styling */
        #map-container {
            height: 300px;
            width: 100%;
            border-radius: 4px;
            margin-bottom: 15px;
            border: 1px solid #e2e8f0;
        }
        
        .dark-theme #map-container {
            border-color: #4a5568;
        }
        
        .location-info {
            display: flex;
            align-items: center;
            margin-top: 10px;
            padding: 10px;
            background-color: #f7fafc;
            border-radius: 4px;
        }
        
        .dark-theme .location-info {
            background-color: #2d3748;
        }
        
        .location-icon {
            margin-right: 10px;
            color: #4c6ef5;
            font-size: 1.2em;
        }
        
        .location-details {
            flex: 1;
        }
        
        .coordinates-display {
            margin-top: 5px;
            font-size: 0.85em;
            color: #718096;
        }
        
        .dark-theme .coordinates-display {
            color: #a0aec0;
        }
        
        .search-box {
            position: relative;
            margin-bottom: 15px;
        }
        
        .search-input {
            width: 100%;
            padding: 10px;
            padding-right: 40px;
            border: 1px solid #e2e8f0;
            border-radius: 4px;
            font-size: 14px;
        }
        
        .dark-theme .search-input {
            background-color: #3f4a5c;
            border-color: #4a5568;
            color: white;
        }
        
        .search-button {
            position: absolute;
            right: 5px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            color: #4c6ef5;
            font-size: 16px;
            cursor: pointer;
        }
        
        .search-results {
            position: absolute;
            top: 100%;
            left: 0;
            right: 0;
            background-color: white;
            border: 1px solid #e2e8f0;
            border-radius: 0 0 4px 4px;
            max-height: 200px;
            overflow-y: auto;
            z-index: 10;
            display: none;
        }
        
        .dark-theme .search-results {
            background-color: #3f4a5c;
            border-color: #4a5568;
        }
        
        .search-result-item {
            padding: 10px;
            border-bottom: 1px solid #e2e8f0;
            cursor: pointer;
        }
        
        .dark-theme .search-result-item {
            border-color: #4a5568;
        }
        
        .search-result-item:hover {
            background-color: #f7fafc;
        }
        
        .dark-theme .search-result-item:hover {
            background-color: #2d3748;
        }
        
        /* Form styling */
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-row {
            display: flex;
            gap: 15px;
            margin-bottom: 20px;
        }
        
        .form-row .form-group {
            flex: 1;
            margin-bottom: 0;
        }
        
        label {
            display: block;
            margin-bottom: 5px;
            font-weight: 500;
        }
        
        input[type="text"],
        input[type="number"],
        input[type="date"],
        input[type="time"],
        textarea,
        select {
            width: 100%;
            padding: 10px;
            border: 1px solid #e2e8f0;
            border-radius: 4px;
            font-size: 14px;
            transition: border-color 0.3s;
        }
        
        .dark-theme input,
        .dark-theme textarea,
        .dark-theme select {
            background-color: #3f4a5c;
            border-color: #4a5568;
            color: white;
        }
        
        input:focus,
        textarea:focus,
        select:focus {
            border-color: #4c6ef5;
            outline: none;
        }
        
        .help-text {
            font-size: 12px;
            color: #718096;
            margin-top: 5px;
        }
        
        .dark-theme .help-text {
            color: #a0aec0;
        }
        
        .error-message {
            color: #e53e3e;
            font-size: 12px;
            margin-top: 5px;
            min-height: 18px; /* Keep a consistent height */
        }
        
        /* Button styling */
        .cancel-btn,
        .save-btn {
            padding: 10px 20px;
            border: none;
            border-radius: 4px;
            font-weight: 500;
            cursor: pointer;
            transition: background-color 0.3s;
        }
        
        .cancel-btn {
            background-color: #e2e8f0;
            color: #4a5568;
            margin-right: 10px;
        }
        
        .dark-theme .cancel-btn {
            background-color: #4a5568;
            color: white;
        }
        
        .save-btn {
            background-color: #4c6ef5;
            color: white;
        }
        
        .cancel-btn:hover {
            background-color: #cbd5e0;
        }
        
        .dark-theme .cancel-btn:hover {
            background-color: #2d3748;
        }
        
        .save-btn:hover {
            background-color: #3b5bdb;
        }
        
        .save-btn:disabled {
            background-color: #a0aec0;
            cursor: not-allowed;
        }
        
        /* Image preview */
        .image-preview {
            margin-top: 10px;
            display: none;
            max-width: 100%;
            border-radius: 4px;
            overflow: hidden;
        }
        
        .image-preview img {
            width: 100%;
            max-height: 200px;
            object-fit: contain;
        }
        
        /* Toast styling */
        .toast {
            position: fixed;
            bottom: 20px;
            right: 20px;
            background-color: #2d3748;
            color: white;
            padding: 12px 20px;
            border-radius: 4px;
            box-shadow: 0 3px 10px rgba(0, 0, 0, 0.2);
            z-index: 1100;
            opacity: 0;
            transform: translateY(20px);
            transition: opacity 0.3s, transform 0.3s;
        }
        
        .toast.show {
            opacity: 1;
            transform: translateY(0);
        }
        
        /* Hackathon thumbnails */
        .hackathon-thumbnail {
            width: 60px;
            height: 40px;
            object-fit: cover;
            border-radius: 4px;
        }
        
        /* Action buttons */
        .action-btn-group {
            display: flex;
            gap: 5px;
        }
        .edit-btn, .delete-btn {
            padding: 5px 10px;
            border-radius: 4px;
            border: none;
            cursor: pointer;
            font-size: 12px;
        }
        .edit-btn {
            background-color: #3b82f6;
            color: white;
        }
        .delete-btn {
            background-color: #ef4444;
            color: white;
        }
        
        /* Status badges */
        .status-badge {
            padding: 4px 8px;
            border-radius: 12px;
            font-size: 0.8em;
            font-weight: 500;
            display: inline-block;
        }
        .upcoming {
            background-color: #dbeafe;
            color: #1e40af;
        }
        .ongoing {
            background-color: #d1fae5;
            color: #065f46;
        }
        .past {
            background-color: #fee2e2;
            color: #b91c1c;
        }
    </style>
</head>
<body class="light-theme">
    <div class="dashboard-container">
        <!-- Sidebar -->
        <div class="sidebar">
            <div class="logo">
                <div class="logo-icon">O</div>
                <span>OnlyEngineers</span>
            </div>
            
            <nav class="sidebar-nav">
                <ul>
                    <li><a href="dashboard.html"><div class="nav-icon">📊</div><span>Dashboard</span></a></li>
                    <li><a href="users.php"><div class="nav-icon">👥</div><span>Users</span></a></li>
                    <li><a href="jobs.html"><div class="nav-icon">👩🏻‍💻</div><span>jobs</span></a></li>
                    <li><a href="Projects.html"><div class="nav-icon">🚀</div><span>Projects</span></a></li>
                    <li><a href="articles.html"><div class="nav-icon">📰</div><span>News</span></a></li>
                    <li class="active"><a href="hackathons.php"><div class="nav-icon">🏆</div><span>Hackathons</span></a></li>
                    <li><a href="courses.html"><div class="nav-icon">📚</div><span>Courses</span></a></li>
                    <li><div class="nav-icon">💼</div><span>Opportunities</span></li>
                    <li><div class="nav-icon">🔔</div><span>Notifications</span><div class="notification-badge">1</div></li>
                    <li><a href="../../front_office/front_office/view/signin.php"><div class="nav-icon">🚪</div><span>Sign out</span></a></li>
                </ul>
            </nav>
        </div>

        <!-- Main Content -->
        <div class="main-content">
            <!-- Header -->
            <div class="header">
                <h1>Hello, <?php echo isset($_SESSION['full_name']) ? htmlspecialchars($_SESSION['full_name']) : 'Admin'; ?> welcome back <span class="wave-emoji">👋</span></h1>
                <div class="header-right">
                    <div class="search-box">
                        <input type="text" placeholder="search">
                        <div class="search-icon">🔍</div>
                    </div>
                    <!-- Theme toggle in header -->
                    <div class="header-theme-toggle">
                        <label class="theme-switch">
                            <input type="checkbox" id="theme-toggle">
                            <span class="slider round"></span>
                        </label>
                    </div>
                    <div class="user-profile">
                        <div class="avatar">
                            <img src="https://i.pravatar.cc/100?img=32" alt="Admin User">
                        </div>
                        <span><?php echo isset($_SESSION['full_name']) ? htmlspecialchars($_SESSION['full_name']) : 'Admin'; ?></span>
                    </div>
                </div>
            </div>

            <!-- Hackathons Content -->
            <div class="users-view">
                <!-- Top Cards Row -->
                <div class="users-top-cards">
                    <div class="service-card report-card">
                        <div class="service-icon blue">
                            <span>🏆</span>
                        </div>
                        <div class="service-title">Hackathons Analytics</div>
                        <div class="service-description">Generate comprehensive reports with key metrics and insights</div>
                        <button class="service-button">Generate Report</button>
                    </div>
                    
                    <div class="service-card hackathon-stats-card">
                        <div class="service-icon purple">
                            <span>👥</span>
                        </div>
                        <div class="service-title">Active Participants</div>
                        <div class="service-amount"><?php echo $activeHackathons; ?></div>
                        <a href="participants.php" class="service-button">See Participants</a>
                    </div>
                    
                    <div class="service-card company-card">
                        <div class="service-icon teal">
                            <span>📊</span>
                        </div>
                        <div class="service-title">Top Required Skills</div>
                        <div class="skills-chart-container">
                            <div class="mini-sparkline">
                                <!-- SVG chart for skills frequency -->
                                <svg class="skills-chart" viewBox="0 0 100 30" preserveAspectRatio="none">
                                    <path d="M0,25 L10,15 L20,22 L30,10 L40,18 L50,12 L60,22 L70,8 L80,15 L90,20 L100,5" 
                                          fill="none" stroke="#4c6ef5" stroke-width="2" />
                                    <!-- Add data points for emphasis -->
                                    <circle cx="30" cy="10" r="2" fill="#4c6ef5" />
                                    <circle cx="70" cy="8" r="2" fill="#4c6ef5" />
                                    <circle cx="100" cy="5" r="3" fill="#4c6ef5" />
                                </svg>
                            </div>
                            <div class="skills-stats">
                                <?php
                                // Extract all skills from hackathons
                                $allSkills = [];
                                foreach ($hackathons as $hackathon) {
                                    $skillsArray = array_map('trim', explode(',', $hackathon['required_skills']));
                                    foreach ($skillsArray as $skill) {
                                        if (!empty($skill)) {
                                            $allSkills[] = strtolower($skill);
                                        }
                                    }
                                }
                                
                                // Count skill occurrences
                                $skillCounts = array_count_values($allSkills);
                                arsort($skillCounts); // Sort by frequency
                                
                                // Get the top 3 skills
                                $topSkills = array_slice($skillCounts, 0, 3, true);
                                $totalSkills = count($allSkills);
                                
                                // Display top skills count
                                echo '<div class="skills-total">' . count(array_unique($allSkills)) . ' Unique Skills</div>';
                                ?>
                                <div class="skills-top">
                                    <?php foreach ($topSkills as $skill => $count): ?>
                                    <div class="skill-item">
                                        <span class="skill-name"><?php echo htmlspecialchars(ucfirst($skill)); ?></span>
                                        <span class="skill-count"><?php echo $count; ?></span>
                                    </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        </div>
                        <div class="service-change positive">
                            <?php echo count($skillCounts) > 0 ? '+' . round(count($topSkills) / count($skillCounts) * 100) . '% demand' : '0% demand'; ?>
                        </div>
                    </div>
                </div>
                
                <!-- Success message after editing/deleting hackathon -->
                <?php if (isset($_GET['success']) && $_GET['success'] == 1): ?>
                    <div class="success-message" style="background-color: #d1fae5; color: #065f46; padding: 10px; border-radius: 4px; margin-bottom: 15px;">
                        <?php echo isset($_GET['message']) ? htmlspecialchars($_GET['message']) : 'Operation completed successfully!'; ?>
                    </div>
                <?php endif; ?>
                
                <!-- Hackathons Table Section -->
                <div class="card users-table-card">
                    <!-- Table Filter Options -->
                    <div class="table-filters">
                        <div class="filter-options">
                            <button class="filter-btn active">All</button>
                            <button class="filter-btn">Upcoming</button>
                            <button class="filter-btn">Ongoing</button>
                            <button class="filter-btn">Past</button>
                        </div>
                        <div class="table-actions">
                            <div class="view-toggle">
                                <button class="view-btn active" data-view="table">
                                    <span class="view-icon">≡</span>
                                </button>
                                <button class="view-btn" data-view="grid">
                                    <span class="view-icon">
                                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                            <rect x="3" y="3" width="7" height="7" rx="1" fill="currentColor"/>
                                            <rect x="3" y="14" width="7" height="7" rx="1" fill="currentColor"/>
                                            <rect x="14" y="3" width="7" height="7" rx="1" fill="currentColor"/>
                                            <rect x="14" y="14" width="7" height="7" rx="1" fill="currentColor"/>
                                        </svg>
                                    </span>
                                </button>
                            </div>
                            <button class="add-button" id="add-hackathon-btn">
                                <span class="add-icon">+</span>
                                <span>Add Hackathon</span>
                            </button>
                            <button class="add-button" id="check-requests-btn">
                                <span class="add-icon">🔍</span>
                                <span>Check Requests</span>
                            </button>
                        </div>
                    </div>
                    
                    <!-- Hackathons Table -->
                    <div class="table-container">
                        <table class="users-table">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Image</th>
                                    <th>Title</th>
                                    <th>Start Date</th>
                                    <th>End Date</th>
                                    <th>Location</th>
                                    <th>Organizer</th>
                                    <th>Max Participants</th>
                                    <th>Required Skills</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                $today = date('Y-m-d');
                                foreach ($hackathons as $hackathon): 
                                    // Determine hackathon status
                                    $status = 'upcoming';
                                    $statusText = 'Upcoming';
                                    
                                    if ($hackathon['start_date'] <= $today && $hackathon['end_date'] >= $today) {
                                        $status = 'ongoing';
                                        $statusText = 'Ongoing';
                                    } else if ($hackathon['end_date'] < $today) {
                                        $status = 'past';
                                        $statusText = 'Past';
                                    }
                                ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($hackathon['id']); ?></td>
                                        <td>
                                            <?php 
                                            // Determine correct image path based on what's stored in database
                                            $imagePath = '';
                                            
                                            // Check if the image path has resources prefix or not
                                            if (strpos($hackathon['image'], 'ressources/') === 0) {
                                                // Full path with 'ressources/' prefix 
                                                $imagePath = '../../front_office/front_office/' . $hackathon['image'];
                                            } else if (strpos($hackathon['image'], 'hackathon_images/') === 0) {
                                                // Just 'hackathon_images/' without 'ressources/' prefix
                                                $imagePath = '../../front_office/front_office/ressources/' . $hackathon['image'];
                                            } else {
                                                // Just the filename - assume it's in hackathon_images folder
                                                $imagePath = '../../front_office/front_office/ressources/hackathon_images/' . $hackathon['image'];
                                            }
                                            
                                            if (file_exists($imagePath)) {
                                                echo '<img src="' . $imagePath . '" class="hackathon-thumbnail" alt="Hackathon">';
                                            } else {
                                                echo '<span>No image</span>';
                                            }
                                            ?>
                                        </td>
                                        <td><?php echo htmlspecialchars($hackathon['name']); ?></td>
                                        <td><?php echo htmlspecialchars($hackathon['start_date']) . ' ' . htmlspecialchars($hackathon['start_time']); ?></td>
                                        <td><?php echo htmlspecialchars($hackathon['end_date']) . ' ' . htmlspecialchars($hackathon['end_time']); ?></td>
                                        <td><?php echo htmlspecialchars($hackathon['location']); ?></td>
                                        <td><?php echo htmlspecialchars($hackathon['organizer']); ?></td>
                                        <td><?php echo htmlspecialchars($hackathon['max_participants']); ?></td>
                                        <td><?php echo htmlspecialchars($hackathon['required_skills']); ?></td>
                                        <td><span class="status-badge <?php echo $status; ?>"><?php echo $statusText; ?></span></td>
                                        <td>
                                            <div class="action-btn-group">
                                                <button class="edit-btn" data-id="<?php echo $hackathon['id']; ?>">Edit</button>
                                                <button class="delete-btn" data-id="<?php echo $hackathon['id']; ?>">Delete</button>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                                <?php if (empty($hackathons)): ?>
                                    <tr>
                                        <td colspan="11" style="text-align: center;">No hackathons found</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Requests Modal -->
    <div class="modal" id="requests-modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Hackathon Requests</h2>
                <button class="close-modal">&times;</button>
            </div>
            <div class="modal-body">
                <div id="requests-container">
                    <p>Loading requests...</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Add Hackathon Modal with Map -->
    <div class="modal" id="add-hackathon-modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Add New Hackathon</h2>
                <button class="close-modal" id="close-add-modal">&times;</button>
            </div>
            <div class="modal-body">
                <form id="add-hackathon-form" enctype="multipart/form-data">
                    <div class="form-row">
                        <div class="form-group">
                            <label for="name">Hackathon Name*</label>
                            <input type="text" id="name" name="name" required>
                            <div class="error-message" id="name-error"></div>
                        </div>
                        <div class="form-group">
                            <label for="organizer">Organizer*</label>
                            <input type="text" id="organizer" name="organizer" required>
                            <div class="error-message" id="organizer-error"></div>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="description">Description*</label>
                        <textarea id="description" name="description" rows="4" required></textarea>
                        <div class="help-text">Provide a detailed description of the hackathon (10-255 words)</div>
                        <div class="error-message" id="description-error"></div>
                    </div>
                    
                    <!-- Location Search and Map Section -->
                    <div class="form-group">
                        <label for="location">Location*</label>
                        <div class="search-box">
                            <input type="text" id="location" name="location" class="search-input" placeholder="Search for a location..." required>
                            <button type="button" class="search-button" id="search-location-btn">🔍</button>
                            <div class="search-results" id="search-results"></div>
                        </div>
                        <div class="error-message" id="location-error"></div>
                    </div>
                    
                    <!-- Hidden inputs for coordinates -->
                    <input type="hidden" id="latitude" name="latitude">
                    <input type="hidden" id="longitude" name="longitude">
                    
                    <!-- Map Container -->
                    <div id="map-container"></div>
                    
                    <div class="location-info" id="location-info" style="display: none;">
                        <div class="location-icon">📍</div>
                        <div class="location-details">
                            <div id="selected-location-text">No location selected</div>
                            <div class="coordinates-display" id="coordinates-display"></div>
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="start-date">Start Date*</label>
                            <input type="date" id="start-date" name="start_date" required>
                            <div class="error-message" id="start-date-error"></div>
                        </div>
                        <div class="form-group">
                            <label for="start-time">Start Time*</label>
                            <input type="time" id="start-time" name="start_time" required>
                            <div class="error-message" id="start-time-error"></div>
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="end-date">End Date*</label>
                            <input type="date" id="end-date" name="end_date" required>
                            <div class="error-message" id="end-date-error"></div>
                        </div>
                        <div class="form-group">
                            <label for="end-time">End Time*</label>
                            <input type="time" id="end-time" name="end_time" required>
                            <div class="error-message" id="end-time-error"></div>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="required-skills">Required Skills*</label>
                        <textarea id="required-skills" name="required_skills" rows="2" required></textarea>
                        <div class="help-text">Enter skills separated by commas (e.g., Python, Machine Learning, Web Development)</div>
                        <div class="error-message" id="required-skills-error"></div>
                    </div>
                    
                    <div class="form-group">
                        <label for="max-participants">Maximum Participants*</label>
                        <input type="number" id="max-participants" name="max_participants" min="1" required>
                        <div class="error-message" id="max-participants-error"></div>
                    </div>
                    
                    <div class="form-group">
                        <label for="hackathon-image">Hackathon Image*</label>
                        <input type="file" id="hackathon-image" name="image" accept="image/*" required>
                        <div class="help-text">Upload an image for the hackathon (max size: 2MB, formats: JPEG, PNG, GIF, WEBP)</div>
                        <div class="error-message" id="image-error"></div>
                        <div class="image-preview" id="image-preview"></div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="cancel-btn" id="cancel-add-btn">Cancel</button>
                <button type="button" class="save-btn" id="submit-add-btn">Add Hackathon</button>
            </div>
        </div>
    </div>

    <!-- Edit Hackathon Modal with Map -->
    <div class="modal" id="edit-hackathon-modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Edit Hackathon</h2>
                <button class="close-modal" id="close-edit-modal">&times;</button>
            </div>
            <div class="modal-body">
                <form id="edit-hackathon-form" enctype="multipart/form-data">
                    <input type="hidden" id="edit-hackathon-id" name="id">
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="edit-name">Hackathon Name*</label>
                            <input type="text" id="edit-name" name="name" required>
                            <div class="error-message" id="edit-name-error"></div>
                        </div>
                        <div class="form-group">
                            <label for="edit-organizer">Organizer*</label>
                            <input type="text" id="edit-organizer" name="organizer" required>
                            <div class="error-message" id="edit-organizer-error"></div>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="edit-description">Description*</label>
                        <textarea id="edit-description" name="description" rows="4" required></textarea>
                        <div class="help-text">Provide a detailed description of the hackathon (10-255 words)</div>
                        <div class="error-message" id="edit-description-error"></div>
                    </div>
                    
                    <!-- Location Search and Map Section -->
                    <div class="form-group">
                        <label for="edit-location">Location*</label>
                        <div class="search-box">
                            <input type="text" id="edit-location" name="location" class="search-input" placeholder="Search for a location..." required>
                            <button type="button" class="search-button" id="edit-search-location-btn">🔍</button>
                            <div class="search-results" id="edit-search-results"></div>
                        </div>
                        <div class="error-message" id="edit-location-error"></div>
                    </div>
                    
                    <!-- Hidden inputs for coordinates -->
                    <input type="hidden" id="edit-latitude" name="latitude">
                    <input type="hidden" id="edit-longitude" name="longitude">
                    
                    <!-- Map Container -->
                    <div id="edit-map-container"></div>
                    
                    <div class="location-info" id="edit-location-info" style="display: none;">
                        <div class="location-icon">📍</div>
                        <div class="location-details">
                            <div id="edit-selected-location-text">No location selected</div>
                            <div class="coordinates-display" id="edit-coordinates-display"></div>
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="edit-start-date">Start Date*</label>
                            <input type="date" id="edit-start-date" name="start_date" required>
                            <div class="error-message" id="edit-start-date-error"></div>
                        </div>
                        <div class="form-group">
                            <label for="edit-start-time">Start Time*</label>
                            <input type="time" id="edit-start-time" name="start_time" required>
                            <div class="error-message" id="edit-start-time-error"></div>
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="edit-end-date">End Date*</label>
                            <input type="date" id="edit-end-date" name="end_date" required>
                            <div class="error-message" id="edit-end-date-error"></div>
                        </div>
                        <div class="form-group">
                            <label for="edit-end-time">End Time*</label>
                            <input type="time" id="edit-end-time" name="end_time" required>
                            <div class="error-message" id="edit-end-time-error"></div>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="edit-required-skills">Required Skills*</label>
                        <textarea id="edit-required-skills" name="required_skills" rows="2" required></textarea>
                        <div class="help-text">Enter skills separated by commas (e.g., Python, Machine Learning, Web Development)</div>
                        <div class="error-message" id="edit-required-skills-error"></div>
                    </div>
                    
                    <div class="form-group">
                        <label for="edit-max-participants">Maximum Participants*</label>
                        <input type="number" id="edit-max-participants" name="max_participants" min="1" required>
                        <div class="error-message" id="edit-max-participants-error"></div>
                    </div>
                    
                    <!-- Current Image Display -->
                    <div id="edit-current-image"></div>
                    
                    <div class="form-group">
                        <label for="edit-hackathon-image">Update Image (Optional)</label>
                        <input type="file" id="edit-hackathon-image" name="image" accept="image/*">
                        <div class="help-text">Upload a new image (max size: 2MB, formats: JPEG, PNG, GIF, WEBP)</div>
                        <div class="error-message" id="edit-image-error"></div>
                        <div class="image-preview" id="edit-image-preview"></div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="cancel-btn" id="cancel-edit-btn">Cancel</button>
                <button type="button" class="save-btn" id="submit-edit-btn">Update Hackathon</button>
            </div>
        </div>
    </div>

    <script>
        // Basic functionality for theme toggle
        document.getElementById('theme-toggle').addEventListener('change', function() {
            document.body.classList.toggle('dark-theme');
            document.body.classList.toggle('light-theme');
        });
        
        // Generate report functionality
        document.addEventListener('DOMContentLoaded', function() {
            const generateReportBtn = document.querySelector('.report-card .service-button');
            if (generateReportBtn) {
                generateReportBtn.addEventListener('click', function() {
                    // Redirect to the generate_report.php script
                    window.location.href = '../model/generate_report.php';
                });
            }
        });
        
        // Gestion du bouton "Check Requests"
        document.addEventListener('DOMContentLoaded', function() {
            const checkRequestsBtn = document.getElementById('check-requests-btn');
            const requestsModal = document.getElementById('requests-modal');
            
            // Vérifier si les éléments existent
            if (checkRequestsBtn && requestsModal) {
                // Ajouter l'écouteur d'événement pour le clic sur le bouton
                checkRequestsBtn.addEventListener('click', function() {
                    // Afficher la modal
                    requestsModal.style.display = 'flex';
                    requestsModal.classList.add('active');
                    
                    // Charger les requêtes
                    loadHackathonRequests();
                });
                
                // Gérer la fermeture de la modal
                const closeModalBtn = requestsModal.querySelector('.close-modal');
                if (closeModalBtn) {
                    closeModalBtn.addEventListener('click', function() {
                        requestsModal.classList.remove('active');
                        requestsModal.style.display = 'none';
                    });
                }
                
                // Fermer la modal quand on clique en dehors
                window.addEventListener('click', function(e) {
                    if (e.target === requestsModal) {
                        requestsModal.classList.remove('active');
                        requestsModal.style.display = 'none';
                    }
                });
            }
            
            // Fonction pour charger les requêtes de hackathon
            function loadHackathonRequests() {
                const requestsContainer = document.getElementById('requests-container');
                
                if (!requestsContainer) return;
                
                // Afficher l'état de chargement
                requestsContainer.innerHTML = '<p style="text-align: center; padding: 20px;">Chargement des requêtes...</p>';
                
                // Récupérer les requêtes depuis le serveur
                fetch('../model/get_hackathon_requests.php')
                    .then(response => {
                        if (!response.ok) {
                            throw new Error('Erreur réseau');
                        }
                        return response.json();
                    })
                    .then(data => {
                        if (data.success) {
                            // Afficher les requêtes
                            renderHackathonRequests(data.requests);
                        } else {
                            requestsContainer.innerHTML = '<p class="error" style="text-align: center; color: red; padding: 20px;">Erreur lors du chargement des requêtes: ' + data.message + '</p>';
                        }
                    })
                    .catch(error => {
                        console.error('Erreur:', error);
                        requestsContainer.innerHTML = '<p class="error" style="text-align: center; color: red; padding: 20px;">Une erreur est survenue lors du chargement des requêtes</p>';
                    });
            }
            
            // Fonction pour afficher les requêtes
            function renderHackathonRequests(requests) {
                const requestsContainer = document.getElementById('requests-container');
                
                if (!requestsContainer) return;
                
                if (requests.length === 0) {
                    requestsContainer.innerHTML = '<div class="no-requests" style="text-align: center; padding: 30px; color: #718096;">Aucune requête de hackathon en attente</div>';
                    return;
                }
                
                let html = '<div class="pending-count">' + requests.length + ' requête(s) en attente</div>';
                html += '<div class="requests-list">';
                
                requests.forEach(request => {
                    html += '<div class="request-item" data-id="' + request.id + '">';
                    
                    // Entête de la requête
                    html += '<div class="request-header">';
                    html += '<div class="request-title-area">';
                    html += '<h3>' + request.name + '</h3>';
                    html += '<div class="request-meta">';
                    html += '<span>' + request.start_date + ' au ' + request.end_date + '</span>';
                    html += '<span>Par ' + (request.username || 'Utilisateur inconnu') + '</span>';
                    html += '</div>';
                    html += '</div>';
                    
                    // Image de la requête
                    let imagePath = '';
                    if (request.image.startsWith('ressources/')) {
                        imagePath = '../../front_office/front_office/' + request.image;
                    } else if (request.image.startsWith('hackathon_images/')) {
                        imagePath = '../../front_office/front_office/ressources/' + request.image;
                    } else {
                        imagePath = '../../front_office/front_office/ressources/hackathon_images/' + request.image;
                    }
                    html += '<div class="request-image"><img src="' + imagePath + '" alt="Hackathon Image"></div>';
                    html += '</div>';
                    
                    // Détails de la requête
                    html += '<div class="request-details">';
                    html += '<div class="request-description"><p>' + request.description.substring(0, 150) + (request.description.length > 150 ? '...' : '') + '</p></div>';
                    html += '<div class="request-organizer"><strong>Organisateur:</strong> ' + request.organizer + '</div>';
                    html += '<div class="request-location"><strong>Lieu:</strong> ' + request.location + '</div>';
                    html += '<div class="request-skills"><strong>Compétences requises:</strong> ' + request.required_skills + '</div>';
                    html += '<div class="request-max-participants"><strong>Participants max:</strong> ' + request.max_participants + '</div>';
                    html += '</div>';
                    
                    // Actions pour la requête
                    html += '<div class="request-actions">';
                    html += '<button class="reject-btn" data-id="' + request.id + '">Rejeter</button>';
                    html += '<button class="approve-btn" data-id="' + request.id + '">Approuver</button>';
                    html += '</div>';
                    
                    html += '</div>';
                });
                
                html += '</div>';
                
                requestsContainer.innerHTML = html;
                
                // Ajouter les écouteurs d'événements pour les boutons
                document.querySelectorAll('.approve-btn').forEach(button => {
                    button.addEventListener('click', function() {
                        processHackathonRequest(this.getAttribute('data-id'), 'approve');
                    });
                });
                
                document.querySelectorAll('.reject-btn').forEach(button => {
                    button.addEventListener('click', function() {
                        processHackathonRequest(this.getAttribute('data-id'), 'reject');
                    });
                });
            }
            
            // Fonction pour traiter une requête de hackathon
            function processHackathonRequest(requestId, action) {
                // Confirmer avec l'utilisateur
                const actionText = action === 'approve' ? 'approuver' : 'rejeter';
                if (!confirm('Êtes-vous sûr de vouloir ' + actionText + ' cette requête de hackathon ?')) {
                    return;
                }
                
                // Préparer les données du formulaire
                const formData = new FormData();
                formData.append('request_id', requestId);
                formData.append('action', action);
                
                // Envoyer la requête au serveur
                fetch('../model/process_hackathon_request.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Montrer un message de succès
                        showToast(data.message);
                        
                        // Recharger les requêtes
                        loadHackathonRequests();
                    } else {
                        // Montrer un message d'erreur
                        showToast('Erreur: ' + data.message, 'error');
                    }
                })
                .catch(error => {
                    console.error('Erreur:', error);
                    showToast('Une erreur est survenue lors du traitement de la requête', 'error');
                });
            }
            
            // Fonction pour afficher un toast
            function showToast(message, type = 'success') {
                // Créer le toast s'il n'existe pas
                let toast = document.querySelector('.toast');
                if (!toast) {
                    toast = document.createElement('div');
                    toast.className = 'toast';
                    document.body.appendChild(toast);
                }
                
                // Configurer le toast
                toast.textContent = message;
                toast.style.backgroundColor = type === 'success' ? '#48bb78' : '#f56565';
                
                // Afficher le toast
                toast.classList.add('show');
                
                // Masquer le toast après 3 secondes
                setTimeout(() => {
                    toast.classList.remove('show');
                }, 3000);
            }
        });
    </script>
    <script src="../model/dashboardModel.js"></script>
    <!-- IMPORTANT: Load our hackathon controller for the add hackathon functionality -->
    <script src="../controllers/hackathonController.js"></script>
    
    <style>
        /* Request modal styles */
        .pending-count {
            background-color: #e2e8f0;
            padding: 10px 15px;
            border-radius: 6px;
            margin-bottom: 15px;
        }
        
        .dark-theme .pending-count {
            background-color: #4a5568;
        }
        
        .requests-list {
            display: flex;
            flex-direction: column;
            gap: 20px;
        }
        
        .request-item {
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            padding: 15px;
            transition: opacity 0.3s;
        }
        
        .dark-theme .request-item {
            border-color: #4a5568;
        }
        
        .request-header {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
        }
        
        .request-title-area {
            flex: 1;
        }
        
        .request-title-area h3 {
            margin: 0 0 5px 0;
            font-size: 18px;
        }
        
        .request-meta {
            display: flex;
            gap: 15px;
            color: #718096;
            font-size: 14px;
        }
        
        .request-image {
            width: 80px;
            height: 60px;
            margin-left: 15px;
        }
        
        .request-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            border-radius: 4px;
        }
        
        .request-details {
            margin-bottom: 15px;
        }
        
        .request-description p {
            margin-top: 5px;
            font-size: 14px;
            line-height: 1.5;
        }
        
        .request-organizer,
        .request-skills,
        .request-max-participants {
            margin-top: 8px;
            font-size: 14px;
        }
        
        .request-actions {
            display: flex;
            justify-content: flex-end;
            gap: 10px;
            padding-top: 10px;
            border-top: 1px solid #e2e8f0;
        }
        
        .dark-theme .request-actions {
            border-color: #4a5568;
        }
        
        .approve-btn,
        .reject-btn {
            padding: 8px 16px;
            border: none;
            border-radius: 4px;
            font-weight: 500;
            cursor: pointer;
        }
        
        .approve-btn {
            background-color: #48bb78;
            color: white;
        }
        
        .reject-btn {
            background-color: #f56565;
            color: white;
        }
        
        .approve-btn:hover {
            background-color: #38a169;
        }
        
        .reject-btn:hover {
            background-color: #e53e3e;
        }
        
        .no-requests,
        .error-message {
            text-align: center;
            padding: 30px 15px;
            background-color: #f7fafc;
            border-radius: 6px;
        }
        
        .dark-theme .no-requests,
        .dark-theme .error-message {
            background-color: #2d3748;
        }
        
        .error-message p {
            color: #e53e3e;
        }
    </style>
</body>
</html>
``` 