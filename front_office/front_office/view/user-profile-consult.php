<?php
session_start();

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    // If no user is logged in, redirect to login page
    header("Location: signin.php");
    exit();
}

// Check if user_id is provided in URL
if (!isset($_GET['id']) || empty($_GET['id'])) {
    // If no user ID is provided, redirect to networking page
    header("Location: networking.php");
    exit();
}

$profileUserId = intval($_GET['id']);

// Don't allow viewing your own profile through this page
if ($profileUserId === $_SESSION['user_id']) {
    header("Location: user-profile.php");
    exit();
}

// Include networking functionality
require_once '../model/networking/follows.php';
require_once '../model/networking/connections.php';
require_once '../model/networking/notifications.php';

// Database connection parameters
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "onlyengs";

try {
    // Create connection using PDO
    $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    // Set the PDO error mode to exception
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Fetch the profile user data
    $stmt = $conn->prepare("SELECT * FROM users WHERE user_id = :user_id AND is_admin = 0");
    $stmt->bindParam(':user_id', $profileUserId);
    $stmt->execute();
    
    // Check if user exists and is not an admin
    if ($stmt->rowCount() > 0) {
        $userData = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // Decode JSON fields
        $experiences = json_decode($userData['experiences'], true) ?? [];
        $educations = json_decode($userData['educations'], true) ?? [];
        $organizations = json_decode($userData['organizations'], true) ?? [];
        $honors = json_decode($userData['honors'], true) ?? [];
        $courses = json_decode($userData['courses'], true) ?? [];
        $projects = json_decode($userData['projects'], true) ?? [];
        $languages = json_decode($userData['languages'], true) ?? [];
        $skills = json_decode($userData['skills'], true) ?? [];
        
        // Format seeking values from comma separated list to array
        $seeking = explode(', ', $userData['seeking']);
    } else {
        // User not found or is admin, redirect to networking
        header("Location: networking.php");
        exit();
    }

    // Get current user's info for the navbar
    $currentUserStmt = $conn->prepare("SELECT profile_picture FROM users WHERE user_id = :user_id");
    $currentUserStmt->bindParam(':user_id', $_SESSION['user_id']);
    $currentUserStmt->execute();
    $currentUser = $currentUserStmt->fetch(PDO::FETCH_ASSOC);
    
    // Get connection data between current user and profile user
    $currentUserId = $_SESSION['user_id'];
    $isFollowing = isFollowing($currentUserId, $profileUserId);
    $connectionStatus = getConnectionStatus($currentUserId, $profileUserId);
    $areConnected = areConnected($currentUserId, $profileUserId);
    $isPendingRequest = isPendingConnection($currentUserId, $profileUserId);
    $hasPendingInvitation = isPendingConnection($profileUserId, $currentUserId);
    
    // Get follower and connection counts
    $followersCount = getFollowersCount($profileUserId);
    $followingCount = getFollowingCount($profileUserId);
    $connectionsCount = getConnectionsCount($profileUserId);
    
    // Get unread notifications count
    $unreadNotificationsCount = getUnreadNotificationsCount($currentUserId);
    
} catch(PDOException $e) {
    // Handle database connection error
    echo "Connection failed: " . $e->getMessage();
    exit();
}

// Function to calculate experience (in years and months)
function calculateExperience($experiences) {
    $totalMonths = 0;
    $now = new DateTime();
    
    foreach ($experiences as $exp) {
        $startDate = new DateTime($exp['start_date']);
        
        if (!empty($exp['end_date'])) {
            $endDate = new DateTime($exp['end_date']);
        } else if ($exp['current'] == 1) {
            $endDate = $now;
        } else {
            $endDate = $startDate; // No end date and not current, assume same as start
        }
        
        $interval = $startDate->diff($endDate);
        $months = ($interval->y * 12) + $interval->m;
        $totalMonths += $months;
    }
    
    $years = floor($totalMonths / 12);
    $months = $totalMonths % 12;
    
    if ($years > 0 && $months > 0) {
        return $years . "+ years of experience";
    } else if ($years > 0) {
        return $years . "+ years of experience";
    } else {
        return $months . " months of experience";
    }
}

// Get profile picture url (with default if none)
$defaultImage = "../ressources/profil.jpg";

// Check if profile picture exists and properly format it
if (!empty($userData['profile_picture'])) {
    $profilePicName = $userData['profile_picture'];
    
    // Debug information
    error_log("Profile picture from database: " . $profilePicName);
    
    // Define the consistent directory path for profile pictures
    $profilePicDirectory = "../ressources/profile_pictures/";
    $profilePicUrl = $profilePicDirectory . $profilePicName;
    
    // Verify the file exists
    $realPath = dirname(dirname(__FILE__)) . '/ressources/profile_pictures/' . $profilePicName;
    
    if (!file_exists($realPath)) {
        error_log("Profile picture not found at: " . $realPath);
        $profilePicUrl = $defaultImage;
    } else {
        error_log("Profile picture exists at: " . $realPath);
    }
} else {
    $profilePicUrl = $defaultImage;
    error_log("No profile picture found in user data, using default: " . $defaultImage);
}

// Get current user profile picture for navbar
$currentUserPicUrl = $defaultImage;
if (!empty($currentUser['profile_picture'])) {
    $currentUserPicPath = "../ressources/profile_pictures/" . $currentUser['profile_picture'];
    if (file_exists($currentUserPicPath)) {
        $currentUserPicUrl = $currentUserPicPath;
    }
}

// Generate skill HTML tags
function generateSkillTags($skills, $limit = 5) {
    $html = '';
    $count = 0;
    
    foreach ($skills as $skill) {
        if ($count < $limit) {
            $html .= '<span class="skill-tag">' . htmlspecialchars($skill['name']) . '</span>';
            $count++;
        } else {
            break;
        }
    }
    
    return $html;
}

// Get the current position based on most recent experience that is current
$currentPosition = "Professional";
foreach ($experiences as $exp) {
    if ($exp['current'] == 1) {
        $currentPosition = htmlspecialchars($exp['title']);
        break;
    } else if (!empty($exp['title'])) {
        $currentPosition = htmlspecialchars($exp['title']);
        break;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($userData['full_name']); ?> | Profile Page</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free@6.2.1/css/all.min.css">
    <link rel="stylesheet" href="../view/styles.css">
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            color: #333;
            background-color: #f5f7fa;
            padding-top: 70px; /* Added padding for the navbar */
        }
        
        .gradient-header {
    background: linear-gradient(135deg, #ff7e5f, #feb47b, #70a1ff, #5352ed);
    background-size: 300% 300%;
    animation: gradient-animation 15s ease infinite;
    border-radius: 8px 8px 0 0;
    height: 240px;
    position: relative;
}
        
        @keyframes gradient-animation {
            0% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
            100% { background-position: 0% 50%; }
        }
        
        .profile-photo {
            width: 100px;
            height: 100px;
            border-radius: 50%;
            border: 5px solid white;
            overflow: hidden;
            margin-top: -50px; /* Position photo to overlap header */
            position: relative;
            z-index: 10;
        }
        
        .follow-btn {
            background-color: #3b82f6;
            color: white;
            border-radius: 25px;
            font-weight: 500;
            padding: 8px 20px;
        }
        
        .connect-btn {
            background-color: white;
            color: #333;
            border-radius: 25px;
            font-weight: 500;
            padding: 8px 20px;
            border: 1px solid #3b82f6;
        }
        
        .current-role {
    background-color: #e5e7eb8a;
    border-radius: 8px;
    padding: 15px;
    font-size: 16px;
    margin-top: 90mm;
    margin-right: 52mm;
    width: 50mm;
}
        
        .skill-tag {
            background-color: #f8f0e5;
            color: #333;
            border-radius: 20px;
            padding: 4px 12px;
            font-size: 14px;
            display: inline-block;
            margin-right: 6px;
            margin-bottom: 6px;
        }
        
        .action-box {
            background-color: rgba(240, 248, 255, 0.9);
            border-radius: 8px;
            padding: 15px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .action-arrow {
            width: 30px;
            height: 30px;
            display: flex;
            justify-content: center;
            align-items: center;
            border-radius: 50%;
            color: #2b6cb0;
        }
        
        .mb-12 {
    margin-bottom: 0mm;
}

.profile-photo {
    width: 135px;
    height: 150px;
    border-radius: 80%;
    border: 5px solid white;
    overflow: hidden;
    margin-top: -75px;
    position: relative;
    z-index: 6;
}
        
        .section-title {
            font-weight: 600;
            color: #2c3e50;
            padding-bottom: 8px;
            margin-bottom: 16px;
            border-bottom: 2px solid #f0f0f0;
        }
        
        .skill-badge {
            background-color: #f8f9fa;
            color: #2c3e50;
            padding: 6px 12px;
            border-radius: 30px;
            font-size: 14px;
            display: inline-block;
            margin: 5px;
            border: 1px solid #e9ecef;
        }
        .skills-block {
    display: flex
;
    align-items: center;
    justify-content: flex-end;
    margin-top: -14.5mm;
}
        
        .expertise-level {
            height: 8px;
            border-radius: 4px;
            background-color: #e9ecef;
            margin-top: 5px;
            overflow: hidden;
        }
        
        .expertise-fill {
            height: 100%;
            border-radius: 4px;
            background: linear-gradient(90deg, #4facfe, #00f2fe);
        }
        
        .timeline-item {
            position: relative;
            padding-left: 30px;
            margin-bottom: 20px;
        }
        
        .timeline-item:before {
            content: '';
            position: absolute;
            width: 14px;
            height: 14px;
            border-radius: 50%;
            background: #70a1ff;
            left: 0;
            top: 5px;
        }
        
        .timeline-item:after {
            content: '';
            position: absolute;
            width: 2px;
            height: calc(100% + 15px);
            background: #e9ecef;
            left: 6px;
            top: 20px;
        }
        
        .timeline-item:last-child:after {
            display: none;
        }
        
        .feature-box {
            background-color: #f7f9fc;
            border-radius: 8px;
            padding: 15px;
            transition: all 0.3s ease;
            cursor: pointer;
        }
        
        .feature-box:hover {
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
            transform: translateY(-2px);
        }
        
        .feature-icon {
            width: 40px;
            height: 40px;
            background-color: rgba(112, 161, 255, 0.15);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 12px;
            color: #70a1ff;
        }

        .align-right {
            text-align: right;
            margin-bottom: 0.5rem;
          }
          
          .skills-block {
            display: flex;
            align-items: center;
            justify-content: flex-end;
            margin-top: -14.5mm;
          }
          
          .title {
            font-size: 1.125rem;
            font-weight: 500;
            color: rgb(107 114 128); /* text-gray-800 */
            margin-right: 0.5rem;
          }
          
          .icon {
            color: #cbd5e0; /* text-gray-400 */
          }
          .skills-container {
            display: flex;
            flex-wrap: wrap;
            justify-content: flex-end;
            height: 6mm;
          }
          
          .skill-tag {
    background-color: #f8f0e5;
    color: #333;
    border-radius: 20px;
    padding: 4px 12px;
    font-size: 14px;
    display: inline-block;
    margin-right: 6px;
    margin-bottom: 6px;
}
          
          /* Navbar specific styles */
          .navbar {
              display: flex;
              align-items: center;
              justify-content: space-between;
              padding: 12px 24px;
              background-color: #fff;
              box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
              position: fixed;
              top: 0;
              left: 0;
              right: 0;
              z-index: 1000;
          }
          
          .nav-left, .nav-right {
              display: flex;
              align-items: center;
          }
          
          .nav-center {
              flex-grow: 1;
              margin: 0 20px;
          }
          
          .nav-links {
              display: flex;
              justify-content: center;
          }
          
          .nav-links a {
              margin: 0 12px;
              padding: 8px 4px;
              color: #6b7280;
              text-decoration: none;
              position: relative;
          }
          
          .nav-links a.active {
    background-color: #4F6EF7;
    color: white;
    font-weight: 500;
}
          
.profile-section {
    background-color: white;
    border-radius: 10px;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
    margin-bottom: 20px;
    overflow: hidden;
}

          
          .icon-button {
              background: none;
              border: none;
              cursor: pointer;
              padding: 8px;
              margin: 0 4px;
              border-radius: 50%;
              display: flex;
              align-items: center;
              justify-content: center;
          }
          
          .notification-wrapper {
              position: relative;
          }
          
          .notification-dot {
              position: absolute;
              top: 4px;
              right: 4px;
              width: 18px;
              height: 18px;
              background-color: #ef4444;
              border-radius: 50%;
              display: flex;
              align-items: center;
              justify-content: center;
              color: white;
              font-size: 10px;
              font-weight: bold;
          }
          
          /* Notifications dropdown panel */
          .notifications-panel {
              position: absolute;
              top: 100%;
              right: 0;
              width: 350px;
              max-height: 500px;
              overflow-y: auto;
              background-color: white;
              border-radius: 8px;
              box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
              z-index: 1000;
              display: none;
              margin-top: 10px;
          }
          
          .notifications-header {
              display: flex;
              justify-content: space-between;
              align-items: center;
              padding: 15px;
              border-bottom: 1px solid #e5e7eb;
          }
          
          .notifications-list {
              padding: 0;
              margin: 0;
              list-style-type: none;
          }
          
          .notification-item {
              padding: 12px 15px;
              border-bottom: 1px solid #e5e7eb;
              cursor: pointer;
              transition: background-color 0.2s;
              display: flex;
              align-items: flex-start;
          }
          
          .notification-item:hover {
              background-color: #f9fafb;
          }
          
          .notification-item.unread {
              background-color: #f0f7ff;
          }
          
          .notification-avatar {
              width: 40px;
              height: 40px;
              border-radius: 50%;
              margin-right: 10px;
          }
          
          .notification-content {
              flex: 1;
          }
          
          .notification-message {
              margin: 0 0 5px 0;
              color: #4b5563;
              font-size: 14px;
          }
          
          .notification-time {
              color: #9ca3af;
              font-size: 12px;
          }
          
          .notification-empty {
              padding: 30px;
              text-align: center;
              color: #9ca3af;
          }
          
          .notification-actions {
              display: flex;
              padding: 10px 15px;
              background-color: #f9fafb;
              justify-content: flex-end;
          }
          
          .notification-action-btn {
              background: none;
              border: none;
              color: #4b5563;
              cursor: pointer;
              font-size: 13px;
              padding: 5px 10px;
          }
          
          .notification-action-btn:hover {
              color: #1d4ed8;
          }
          
          .logo img {
    height: 140px;
    width: auto;
    object-fit: contain;
    margin: -38px 0;
    margin-left: -10px;
}
          
          /* Debug styles to check image paths */
          .debug-box {
              background: #f8f9fa;
              padding: 10px;
              margin: 10px 0;
              border: 1px solid #dee2e6;
              border-radius: 5px;
              display: none; /* Hidden by default, enable for debugging */
          }
    </style>
</head>
<body>
    <!-- Navigation Bar -->
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
                <button class="icon-button notification" id="notificationButton">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"></path>
                        <path d="M13.73 21a2 2 0 0 1-3.46 0"></path>
                    </svg>
                </button>
                <?php if ($unreadNotificationsCount > 0): ?>
                <span class="notification-dot"><?php echo $unreadNotificationsCount > 9 ? '9+' : $unreadNotificationsCount; ?></span>
                <?php endif; ?>
                
                <!-- Notifications dropdown panel -->
                <div class="notifications-panel" id="notificationsPanel">
                    <div class="notifications-header">
                        <h3 class="text-gray-800 font-medium">Notifications</h3>
                        <button class="text-sm text-blue-500 hover:text-blue-700" id="markAllReadBtn">Mark all as read</button>
                    </div>
                    <ul class="notifications-list" id="notificationsList">
                        <!-- Notifications will be loaded dynamically -->
                        <li class="notification-empty">Loading notifications...</li>
                    </ul>
                    <div class="notification-actions">
                        <button class="notification-action-btn" id="refreshNotificationsBtn">
                            <i class="fas fa-sync-alt mr-1"></i> Refresh
                        </button>
                    </div>
                </div>
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
                <a href="../view/user-profile.php">
                    <img src="<?php echo htmlspecialchars($currentUserPicUrl); ?>" alt="User profile" class="avatar">
                </a>
            </div>
        </div>
    </nav>

    <div class="container mx-auto p-4 max-w-6xl">
        <!-- Gradient Header - Reduced height as content moved to white section -->
        <div class="gradient-header mb-0 overflow-hidden">
            <!-- Current Role Box -->
            
        </div>
        
        <!-- White section for profile content -->
        <div class="bg-white px-4 pt-1 pb-2 rounded-b-lg mb-4 shadow-sm">
            
            <div class="flex flex-col items-start text-left relative">
                <!-- Photo de profil -->
                <div class="profile-photo mb-12">
                    <img src="<?php echo htmlspecialchars($profilePicUrl); ?>" alt="Profile Picture" class="w-32 h-32 object-cover rounded-full">
                </div>
                
                <!-- Informations du profil -->
                <div>
                    <h1 class="text-3xl font-bold mb-1 text-gray-800"><?php echo htmlspecialchars($userData['full_name']); ?></h1>
                    <h2 class="text-xl text-gray-600 mb-2"><?php echo htmlspecialchars($userData['position']); ?></h2>
                    <p class="text-md mb-4 text-gray-500">
                        <?php echo htmlspecialchars($userData['city']); ?>, <?php echo htmlspecialchars($userData['country']); ?>
                        <span class="ml-3 text-sm">
                            <span class="font-semibold"><?php echo $followersCount; ?></span> followers · 
                            <span class="font-semibold"><?php echo $connectionsCount; ?></span> connections
                        </span>
                    </p>
            
                    <!-- Follow and Connect buttons based on current relationship status -->
                    <div class="flex gap-3">
                        <?php if ($isFollowing): ?>
                            <button id="follow-btn" data-user-id="<?php echo $profileUserId; ?>" class="follow-btn bg-gray-500 text-white px-4 py-2 rounded hover:bg-gray-600 transition">Following</button>
                        <?php else: ?>
                            <button id="follow-btn" data-user-id="<?php echo $profileUserId; ?>" class="follow-btn bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600 transition">Follow</button>
                        <?php endif; ?>
                        
                        <?php if ($areConnected): ?>
                            <button id="connect-btn" data-user-id="<?php echo $profileUserId; ?>" class="connect-btn bg-white text-green-600 border-green-600 px-4 py-2 rounded hover:bg-green-50 transition">
                                <i class="fas fa-check mr-1"></i> Connected
                            </button>
                        <?php elseif ($hasPendingInvitation): ?>
                            <button id="accept-btn" data-user-id="<?php echo $profileUserId; ?>" class="bg-green-500 text-white px-4 py-2 rounded hover:bg-green-600 transition mr-2">
                                Accept
                            </button>
                            <button id="reject-btn" data-user-id="<?php echo $profileUserId; ?>" class="bg-white text-red-500 border-red-500 px-4 py-2 rounded hover:bg-red-50 transition">
                                Decline
                            </button>
                        <?php elseif ($isPendingRequest): ?>
                            <button id="connect-btn" disabled class="connect-btn bg-gray-100 text-gray-500 border-gray-300 px-4 py-2 rounded opacity-75 cursor-not-allowed">
                                Request Sent
                            </button>
                        <?php else: ?>
                            <button id="connect-btn" data-user-id="<?php echo $profileUserId; ?>" class="connect-btn bg-white text-blue-500 border-blue-500 px-4 py-2 rounded hover:bg-blue-50 transition">
                                Connect
                            </button>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <div class="absolute top-4 right-4">
                <div class="current-role text-center">
                    <span class="block text-gray-500 text-sm">Current role</span>
                    <span class="font-semibold text-gray-700"><?php echo $currentPosition; ?></span>
                </div>
            </div>
            
            <!-- Skills Section with Star Icon -->
            <div class="mt-2 flex items-center">
                <div class="flex-1">
                    <div class="align-right">
                        <div class="skills-block">
                          <h3 class="title">Skills</h3>
                          <i class="far fa-star icon"></i>
                        </div>
                      </div>
                      
                      <div class="skills-container">
                        <?php 
                        // Display up to 5 skills
                        echo generateSkillTags($skills, 5);
                        ?>
                    </div>
                    
                </div>
            </div>
            
            <!-- Messages and Contact Options -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-6">
                <div class="action-box">
                    <div>
                        <h4 class="font-medium text-gray-700 mb-1">Send Message</h4>
                        <p class="text-sm text-gray-500">Start a conversation with <?php echo htmlspecialchars(explode(' ', $userData['full_name'])[0]); ?>.</p>
                    </div>
                    <div class="w-10 h-10 flex items-center justify-center rounded-full border-2 border-blue-500 text-blue-500 bg-transparent">
                        <i class="fas fa-envelope"></i>
                    </div>
                </div>
                
                <div class="action-box">
                    <div>
                        <h4 class="font-medium text-gray-700 mb-1">Share Profile</h4>
                        <p class="text-sm text-gray-500">Share this profile with your network.</p>
                    </div>
                    <div class="w-10 h-10 flex items-center justify-center rounded-full border-2 border-blue-500 text-blue-500 bg-transparent">
                        <i class="fas fa-share-alt"></i>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Main content area -->
        <div class="flex flex-col lg:flex-row gap-6">
            <!-- Left column - Main profile sections -->
            <div class="flex-1">
                <!-- About Section -->
                <div class="profile-section p-6 mb-6">
                    <h3 class="section-title text-2xl">About</h3>
                    <p class="mb-4"><?php echo nl2br(htmlspecialchars($userData['about'])); ?></p>
                </div>
                
                <!-- What I'm Seeking Section -->
                <div class="profile-section p-6 mb-6">
                    <h3 class="section-title text-2xl">What I'm Seeking</h3>
                    <ul class="list-disc list-inside space-y-2">
                        <?php foreach ($seeking as $seekItem): ?>
                            <li><?php echo htmlspecialchars(ucfirst($seekItem)); ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
                
                <!-- Experience Section -->
                <?php if (!empty($experiences)): ?>
                <div class="profile-section p-6 mb-6">
                    <h3 class="section-title text-2xl">Experience</h3>
                    <?php foreach ($experiences as $exp): ?>
                    <div class="timeline-item">
                        <div class="flex justify-between items-start mb-2">
                            <h4 class="font-bold text-lg"><?php echo htmlspecialchars($exp['title']); ?></h4>
                            <span class="text-sm text-gray-500">
                                <?php 
                                echo htmlspecialchars(date('Y', strtotime($exp['start_date'])));
                                echo ' - ';
                                if ($exp['current'] == 1) {
                                    echo 'Present';
                                } else if (!empty($exp['end_date'])) {
                                    echo htmlspecialchars(date('Y', strtotime($exp['end_date'])));
                                }
                                ?>
                            </span>
                        </div>
                        <h5 class="font-medium text-md text-gray-700 mb-2"><?php echo htmlspecialchars($exp['company']); ?></h5>
                        <p class="text-gray-600"><?php echo nl2br(htmlspecialchars($exp['description'])); ?></p>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
                
                <!-- Education Section -->
                <?php if (!empty($educations)): ?>
                <div class="profile-section p-6 mb-6">
                    <h3 class="section-title text-2xl">Education</h3>
                    <?php foreach ($educations as $edu): ?>
                    <div class="timeline-item">
                        <div class="flex justify-between items-start mb-2">
                            <h4 class="font-bold text-lg"><?php echo htmlspecialchars($edu['degree']); ?> in <?php echo htmlspecialchars($edu['field']); ?></h4>
                            <span class="text-sm text-gray-500">
                                <?php 
                                echo htmlspecialchars(date('Y', strtotime($edu['start_date'])));
                                echo ' - ';
                                if ($edu['current'] == 1) {
                                    echo 'Present';
                                } else if (!empty($edu['end_date'])) {
                                    echo htmlspecialchars(date('Y', strtotime($edu['end_date'])));
                                }
                                ?>
                            </span>
                        </div>
                        <h5 class="font-medium text-md text-gray-700 mb-2"><?php echo htmlspecialchars($edu['school']); ?></h5>
                        <p class="text-gray-600"><?php echo nl2br(htmlspecialchars($edu['description'])); ?></p>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
                
                <!-- Courses Section -->
                <?php if (!empty($courses)): ?>
                <div class="profile-section p-6 mb-6">
                    <h3 class="section-title text-2xl">Courses & Certifications</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <?php foreach ($courses as $course): ?>
                        <div class="bg-gray-50 p-4 rounded-lg">
                            <div class="flex items-center mb-2">
                                <i class="fas fa-certificate text-blue-500 mr-2"></i>
                                <h4 class="font-bold"><?php echo htmlspecialchars($course['title']); ?></h4>
                            </div>
                            <p class="text-sm text-gray-600"><?php echo htmlspecialchars($course['provider']); ?>, <?php echo !empty($course['end_date']) ? htmlspecialchars(date('Y', strtotime($course['end_date']))) : 'Current'; ?></p>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>
                
                <!-- Projects Section -->
                <?php if (!empty($projects)): ?>
                <div class="profile-section p-6 mb-6">
                    <h3 class="section-title text-2xl">Projects</h3>
                    <?php foreach ($projects as $project): ?>
                    <div class="timeline-item">
                        <div class="flex justify-between items-start mb-2">
                            <h4 class="font-bold text-lg"><?php echo htmlspecialchars($project['title']); ?></h4>
                            <span class="text-sm text-gray-500">
                                <?php 
                                if (!empty($project['start_date'])) {
                                    echo htmlspecialchars(date('Y', strtotime($project['start_date'])));
                                }
                                ?>
                            </span>
                        </div>
                        <p class="text-gray-600 mb-2"><?php echo nl2br(htmlspecialchars($project['description'])); ?></p>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
            </div>
            
            <!-- Right column - Skills, languages, etc. -->
            <div class="lg:w-1/3">
                <!-- Contact Info -->
                <div class="profile-section p-6 mb-6">
                    <h3 class="section-title text-xl">Location</h3>
                    <div class="flex items-center">
                        <i class="fas fa-map-marker-alt text-gray-500 w-8"></i>
                        <span><?php echo htmlspecialchars($userData['city']); ?>, <?php echo htmlspecialchars($userData['country']); ?></span>
                    </div>
                </div>
                
                <!-- Organizations Section -->
                <?php if (!empty($organizations)): ?>
                <div class="profile-section p-6 mb-6">
                    <h3 class="section-title text-xl">Organizations</h3>
                    <?php foreach ($organizations as $org): ?>
                    <div class="flex items-center mb-4">
                        <div class="w-12 h-12 bg-gray-200 rounded-full flex items-center justify-center mr-4">
                            <i class="fas fa-users text-gray-500"></i>
                        </div>
                        <div>
                            <h4 class="font-medium"><?php echo htmlspecialchars($org['name']); ?></h4>
                            <?php if (!empty($org['position'])): ?>
                            <p class="text-sm text-gray-600"><?php echo htmlspecialchars($org['position']); ?></p>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
                
                <!-- Honors & Awards Section -->
                <?php if (!empty($honors)): ?>
                <div class="profile-section p-6 mb-6">
                    <h3 class="section-title text-xl">Honors & Awards</h3>
                    <?php foreach ($honors as $honor): ?>
                    <div class="timeline-item">
                        <div class="flex justify-between items-start mb-2">
                            <h4 class="font-bold"><?php echo htmlspecialchars($honor['name']); ?></h4>
                            <span class="text-sm text-gray-500">
                                <?php echo !empty($honor['date']) ? htmlspecialchars(date('Y', strtotime($honor['date']))) : ''; ?>
                            </span>
                        </div>
                        <?php if (!empty($honor['issuer'])): ?>
                        <p class="text-sm text-gray-600"><?php echo htmlspecialchars($honor['issuer']); ?></p>
                        <?php endif; ?>
                        <?php if (!empty($honor['description'])): ?>
                        <p class="text-sm text-gray-600"><?php echo nl2br(htmlspecialchars($honor['description'])); ?></p>
                        <?php endif; ?>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
                
                <!-- Languages Section -->
                <?php if (!empty($languages)): ?>
                <div class="profile-section p-6 mb-6">
                    <h3 class="section-title text-xl">Languages</h3>
                    <?php foreach ($languages as $lang): ?>
                    <div class="mb-4">
                        <div class="flex justify-between items-center mb-1">
                            <span class="font-medium"><?php echo htmlspecialchars($lang['name']); ?></span>
                            <span class="text-sm text-gray-500"><?php echo htmlspecialchars($lang['proficiency']); ?></span>
                        </div>
                        <div class="expertise-level">
                            <?php
                            $proficiencyPercentage = 0;
                            switch($lang['proficiency']) {
                                case 'Native': $proficiencyPercentage = 100; break;
                                case 'Fluent': $proficiencyPercentage = 90; break;
                                case 'Advanced': $proficiencyPercentage = 80; break;
                                case 'Intermediate': $proficiencyPercentage = 60; break;
                                case 'Basic': $proficiencyPercentage = 30; break;
                                default: $proficiencyPercentage = 50;
                            }
                            ?>
                            <div class="expertise-fill" style="width: <?php echo $proficiencyPercentage; ?>%"></div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
                
                <!-- Skills Section -->
                <?php if (!empty($skills)): ?>
                <div class="profile-section p-6">
                    <h3 class="section-title text-xl">Technical Skills</h3>
                    <?php foreach ($skills as $skill): ?>
                    <div class="mb-4">
                        <div class="flex justify-between items-center mb-1">
                            <span class="font-medium"><?php echo htmlspecialchars($skill['name']); ?></span>
                            <span class="text-sm text-gray-500"><?php echo htmlspecialchars($skill['level']); ?></span>
                        </div>
                        <div class="expertise-level">
                            <?php
                            $skillLevelPercentage = 0;
                            switch($skill['level']) {
                                case 'Expert': $skillLevelPercentage = 95; break;
                                case 'Advanced': $skillLevelPercentage = 80; break;
                                case 'Intermediate': $skillLevelPercentage = 60; break;
                                case 'Beginner': $skillLevelPercentage = 30; break;
                                default: $skillLevelPercentage = 50;
                            }
                            ?>
                            <div class="expertise-fill" style="width: <?php echo $skillLevelPercentage; ?>%"></div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- Footer area -->
        <div class="mt-6 py-6 border-t border-gray-200 text-center text-gray-500">
            <p>© <?php echo date('Y'); ?> <?php echo htmlspecialchars($userData['full_name']); ?>. <?php echo htmlspecialchars($userData['position']); ?></p>
            <div class="flex justify-center mt-3 space-x-4">
                <a href="#" class="text-gray-500 hover:text-blue-500"><i class="fab fa-linkedin-in"></i></a>
                <a href="#" class="text-gray-500 hover:text-blue-400"><i class="fab fa-twitter"></i></a>
                <a href="#" class="text-gray-500 hover:text-gray-700"><i class="fab fa-github"></i></a>
            </div>
        </div>
    </div>

    <!-- Toast notification element -->
    <div id="toast-container" class="fixed top-4 right-4 z-50" style="display: none;">
        <div id="toast" class="flex items-center p-4 mb-4 w-full max-w-xs rounded-lg shadow text-gray-400 bg-white">
            <div id="toast-icon" class="inline-flex flex-shrink-0 justify-center items-center w-8 h-8 rounded-lg">
                <svg id="success-icon" class="w-5 h-5 text-green-500" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg" style="display: none;">
                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                </svg>
                <svg id="error-icon" class="w-5 h-5 text-red-500" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg" style="display: none;">
                    <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                </svg>
                <svg id="info-icon" class="w-5 h-5 text-blue-500" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg" style="display: none;">
                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2h.01a1 1 0 000-2H9z" clip-rule="evenodd"></path>
                </svg>
            </div>
            <div id="toast-content" class="ml-3 text-sm font-normal"></div>
            <button type="button" class="ml-auto -mx-1.5 -my-1.5 rounded-lg focus:ring-2 focus:ring-gray-300 p-1.5 inline-flex h-8 w-8 text-gray-500 hover:text-white bg-gray-100 hover:bg-gray-200" id="close-toast">
                <span class="sr-only">Close</span>
                <svg aria-hidden="true" class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path></svg>
            </button>
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
        
        // Toast notification functionality
        const toastContainer = document.getElementById('toast-container');
        const toast = document.getElementById('toast');
        const toastContent = document.getElementById('toast-content');
        const toastIcon = document.getElementById('toast-icon');
        const successIcon = document.getElementById('success-icon');
        const errorIcon = document.getElementById('error-icon');
        const infoIcon = document.getElementById('info-icon');
        const closeToast = document.getElementById('close-toast');

        // Define the showToast function first, since it's used in multiple places
        function showToast(title, message, type = 'info') {
            toastContent.textContent = message;

            // Reset icons
            successIcon.style.display = 'none';
            errorIcon.style.display = 'none';
            infoIcon.style.display = 'none';

            // Set icon based on type
            if (type === 'success') {
                successIcon.style.display = 'block';
            } else if (type === 'error') {
                errorIcon.style.display = 'block';
            } else if (type === 'info') {
                infoIcon.style.display = 'block';
            }

            // Show toast
            toastContainer.style.display = 'block';

            // Auto-hide after 3 seconds
            setTimeout(() => {
                toastContainer.style.display = 'none';
            }, 3000);
        }

        // Close toast button
        closeToast.addEventListener('click', () => {
            toastContainer.style.display = 'none';
        });

        // Follow button functionality
        const followBtn = document.getElementById('follow-btn');
        if (followBtn) {
            followBtn.addEventListener('click', function() {
                const isFollowing = this.classList.contains('bg-gray-500');
                const userId = this.getAttribute('data-user-id');
                const action = isFollowing ? 'unfollow' : 'follow';
                
                // Send AJAX request
                const formData = new FormData();
                formData.append('action', action);
                formData.append('user_id', userId);
                
                fetch('../model/networking/follows.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        if (data.is_following) {
                            // User is now following
                            this.classList.remove('bg-blue-500');
                            this.classList.add('bg-gray-500');
                            this.textContent = 'Following';
                        } else {
                            // User unfollowed
                            this.classList.remove('bg-gray-500');
                            this.classList.add('bg-blue-500');
                            this.textContent = 'Follow';
                        }
                        // Show success toast instead of alert
                        showToast('Success', data.message, 'success');
                    } else {
                        showToast('Error', data.message, 'error');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    showToast('Error', 'An error occurred. Please try again later.', 'error');
                });
            });
        }

        // Connect button functionality
        const connectBtn = document.getElementById('connect-btn');
        if (connectBtn && !connectBtn.disabled) {
            connectBtn.addEventListener('click', function() {
                const userId = this.getAttribute('data-user-id');
                
                console.log("Connect button clicked for user ID:", userId);
                
                // If button has "Connected" text, it's a remove connection action
                const action = this.textContent.trim().includes('Connected') ? 'remove_connection' : 'send_request';
                
                // Disable button temporarily to prevent multiple clicks
                this.disabled = true;
                this.classList.add('opacity-50', 'cursor-not-allowed');
                
                // Send AJAX request
                const formData = new FormData();
                formData.append('action', action);
                formData.append('user_id', userId);
                
                fetch('../model/networking/connections.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => {
                    console.log("Response received:", response);
                    if (!response.ok) {
                        throw new Error('Network response was not ok');
                    }
                    return response.json();
                })
                .then(data => {
                    console.log("Data received:", data);
                    
                    // Re-enable button
                    this.disabled = false;
                    this.classList.remove('opacity-50', 'cursor-not-allowed');
                    
                    if (data.success) {
                        if (action === 'send_request') {
                            // Update button state
                            this.disabled = true;
                            this.textContent = 'Request Sent';
                            this.classList.add('bg-gray-100', 'text-gray-500', 'border-gray-300', 'opacity-75', 'cursor-not-allowed');
                            this.classList.remove('bg-white', 'text-blue-500', 'border-blue-500', 'hover:bg-blue-50');
                            
                            // Show success toast
                            showToast('Success', data.message, 'success');
                        } else {
                            // Handle other actions if needed
                            showToast('Success', data.message, 'success');
                            // Reload page to reflect updated status
                            setTimeout(() => {
                                window.location.reload();
                            }, 1500);
                        }
                    } else {
                        // Show error message
                        showToast('Error', data.message || 'An error occurred while processing your request', 'error');
                    }
                })
                .catch(error => {
                    // Re-enable button on error
                    this.disabled = false;
                    this.classList.remove('opacity-50', 'cursor-not-allowed');
                    
                    console.error('Error:', error);
                    showToast('Error', 'An error occurred while processing your request. Please try again later.', 'error');
                });
            });
        }

        // Accept connection request button
        const acceptBtn = document.getElementById('accept-btn');
        if (acceptBtn) {
            acceptBtn.addEventListener('click', function() {
                const userId = this.getAttribute('data-user-id');
                
                // Send AJAX request
                const formData = new FormData();
                formData.append('action', 'accept_request');
                formData.append('user_id', userId);
                
                fetch('../model/networking/connections.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Replace both buttons with a "Connected" button
                        const parentDiv = this.parentElement;
                        parentDiv.innerHTML = `
                            <button id="connect-btn" data-user-id="${userId}" class="connect-btn bg-white text-green-600 border-green-600 px-4 py-2 rounded hover:bg-green-50 transition">
                                <i class="fas fa-check mr-1"></i> Connected
                            </button>
                        `;
                        
                        // Show success toast
                        showToast('Success', data.message, 'success');
                        
                        // Reinitialize the connection button event listener
                        document.getElementById('connect-btn').addEventListener('click', function() {
                            const userId = this.getAttribute('data-user-id');
                            const formData = new FormData();
                            formData.append('action', 'remove_connection');
                            formData.append('user_id', userId);
                            
                            fetch('../model/networking/connections.php', {
                                method: 'POST',
                                body: formData
                            })
                            .then(response => response.json())
                            .then(data => {
                                if (data.success) {
                                    this.textContent = 'Connect';
                                    this.classList.remove('text-green-600', 'border-green-600', 'hover:bg-green-50');
                                    this.classList.add('text-blue-500', 'border-blue-500', 'hover:bg-blue-50');
                                    showToast('Success', data.message, 'success');
                                } else {
                                    showToast('Error', data.message, 'error');
                                }
                            })
                            .catch(error => {
                                console.error('Error:', error);
                                showToast('Error', 'An error occurred. Please try again later.', 'error');
                            });
                        });
                    } else {
                        showToast('Error', data.message, 'error');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    showToast('Error', 'An error occurred. Please try again later.', 'error');
                });
            });
        }

        // Reject connection request button
        const rejectBtn = document.getElementById('reject-btn');
        if (rejectBtn) {
            rejectBtn.addEventListener('click', function() {
                const userId = this.getAttribute('data-user-id');
                
                // Send AJAX request
                const formData = new FormData();
                formData.append('action', 'reject_request');
                formData.append('user_id', userId);
                
                fetch('../model/networking/connections.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Replace both buttons with a normal "Connect" button
                        const parentDiv = this.parentElement;
                        parentDiv.innerHTML = `
                            <button id="connect-btn" data-user-id="${userId}" class="connect-btn bg-white text-blue-500 border-blue-500 px-4 py-2 rounded hover:bg-blue-50 transition">
                                Connect
                            </button>
                        `;
                        
                        // Show success toast
                        showToast('Success', data.message, 'success');
                        
                        // Reinitialize the connection button event listener
                        const newConnectBtn = document.getElementById('connect-btn');
                        newConnectBtn.addEventListener('click', function() {
                            const userId = this.getAttribute('data-user-id');
                            const formData = new FormData();
                            formData.append('action', 'send_request');
                            formData.append('user_id', userId);
                            
                            fetch('../model/networking/connections.php', {
                                method: 'POST',
                                body: formData
                            })
                            .then(response => response.json())
                            .then(data => {
                                if (data.success) {
                                    this.disabled = true;
                                    this.textContent = 'Request Sent';
                                    this.classList.add('bg-gray-100', 'text-gray-500', 'border-gray-300', 'opacity-75', 'cursor-not-allowed');
                                    this.classList.remove('bg-white', 'text-blue-500', 'border-blue-500', 'hover:bg-blue-50');
                                    showToast('Success', data.message, 'success');
                                } else {
                                    showToast('Error', data.message, 'error');
                                }
                            })
                            .catch(error => {
                                console.error('Error:', error);
                                showToast('Error', 'An error occurred. Please try again later.', 'error');
                            });
                        });
                    } else {
                        showToast('Error', data.message, 'error');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    showToast('Error', 'An error occurred. Please try again later.', 'error');
                });
            });
        }

        // Notifications functionality
        const notificationButton = document.getElementById('notificationButton');
        const notificationsPanel = document.getElementById('notificationsPanel');
        const notificationsList = document.getElementById('notificationsList');
        const markAllReadBtn = document.getElementById('markAllReadBtn');
        const refreshNotificationsBtn = document.getElementById('refreshNotificationsBtn');
        
        // Toggle notifications panel
        notificationButton.addEventListener('click', function() {
            if (notificationsPanel.style.display === 'block') {
                notificationsPanel.style.display = 'none';
            } else {
                notificationsPanel.style.display = 'block';
                loadNotifications();
            }
        });
        
        // Close notifications panel when clicking outside
        document.addEventListener('click', function(event) {
            const isClickInside = notificationsPanel.contains(event.target) || 
                                  notificationButton.contains(event.target);
            
            if (!isClickInside && notificationsPanel.style.display === 'block') {
                notificationsPanel.style.display = 'none';
            }
        });
        
        // Load notifications
        function loadNotifications() {
            fetch('../view/get_notifications.php')
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Network response was not ok');
                    }
                    return response.json();
                })
                .then(data => {
                    // Update notifications list
                    if (data.success && data.notifications && data.notifications.length > 0) {
                        notificationsList.innerHTML = '';
                        
                        data.notifications.forEach(notification => {
                            // Format timestamp
                            const createdAt = new Date(notification.created_at);
                            const now = new Date();
                            const diffTime = Math.abs(now - createdAt);
                            const diffDays = Math.floor(diffTime / (1000 * 60 * 60 * 24));
                            
                            let timeDisplay;
                            if (diffDays === 0) {
                                // Today - show hours
                                timeDisplay = createdAt.toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'});
                            } else if (diffDays === 1) {
                                timeDisplay = 'Yesterday';
                            } else {
                                timeDisplay = createdAt.toLocaleDateString();
                            }
                            
                            // Default image if no profile picture
                            const profilePicPath = notification.profile_picture ? 
                                                  "../ressources/profile_pictures/" + notification.profile_picture : 
                                                  "../ressources/profil.jpg";
                            
                            // Create notification item
                            const notificationItem = document.createElement('li');
                            notificationItem.className = 'notification-item' + (notification.read_status == 0 ? ' unread' : '');
                            
                            // Special handling for connection request notifications
                            if (notification.type === 'connection_request') {
                                notificationItem.innerHTML = `
                                    <img src="${notification.sender_image}" alt="User" class="notification-avatar">
                                    <div class="notification-content">
                                        <p class="notification-message">${notification.message}</p>
                                        <p class="notification-time">${timeDisplay}</p>
                                        <div class="notification-actions mt-2">
                                            <button class="accept-request-btn bg-green-500 text-white text-xs px-3 py-1 rounded hover:bg-green-600 transition mr-2" 
                                                data-user-id="${notification.sender_id}">
                                                Accept
                                            </button>
                                            <button class="reject-request-btn bg-white text-red-500 text-xs border border-red-500 px-3 py-1 rounded hover:bg-red-50 transition" 
                                                data-user-id="${notification.sender_id}">
                                                Decline
                                            </button>
                                        </div>
                                    </div>
                                `;
                            } else if (notification.type === 'connection_accepted' || notification.type === 'connection_rejected') {
                                // Special styling for connection acceptance/rejection
                                let iconClass = notification.type === 'connection_accepted' ? 
                                    'fas fa-check text-green-500' : 'fas fa-times text-red-500';
                                    
                                notificationItem.innerHTML = `
                                    <img src="${notification.sender_image}" alt="User" class="notification-avatar">
                                    <div class="notification-content">
                                        <p class="notification-message">
                                            <i class="${iconClass} mr-1"></i> ${notification.message}
                                        </p>
                                        <p class="notification-time">${timeDisplay}</p>
                                    </div>
                                `;
                            } else {
                                // Default notification display
                                notificationItem.innerHTML = `
                                    <img src="${notification.sender_image}" alt="User" class="notification-avatar">
                                    <div class="notification-content">
                                        <p class="notification-message">${notification.message}</p>
                                        <p class="notification-time">${timeDisplay}</p>
                                    </div>
                                `;
                            }
                            
                            notificationsList.appendChild(notificationItem);
                        });
                        
                        // Add event listeners to accept/reject buttons
                        attachConnectionRequestListeners();
                    } else {
                        notificationsList.innerHTML = '<li class="notification-empty">No notifications</li>';
                    }
                })
                .catch(error => {
                    console.error('Error fetching notifications:', error);
                    notificationsList.innerHTML = '<li class="notification-empty">Failed to load notifications</li>';
                });
        }
        
        // Attach event listeners to connection request buttons
        function attachConnectionRequestListeners() {
            // Accept button listeners
            document.querySelectorAll('.accept-request-btn').forEach(button => {
                button.addEventListener('click', function(e) {
                    e.stopPropagation(); // Prevent notification item click
                    const userId = this.getAttribute('data-user-id');
                    
                    // Send AJAX request to accept the connection
                    const formData = new FormData();
                    formData.append('action', 'accept_request');
                    formData.append('user_id', userId);
                    
                    fetch('../model/networking/connections.php', {
                        method: 'POST',
                        body: formData
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            // Replace buttons with a success message
                            const actionsDiv = this.parentElement;
                            actionsDiv.innerHTML = '<p class="text-green-500"><i class="fas fa-check"></i> Request accepted</p>';
                            
                            // Show a toast notification
                            showToast('Success', 'Connection request accepted', 'success');
                            
                            // Reload notifications after a short delay
                            setTimeout(() => {
                                loadNotifications();
                            }, 2000);
                        } else {
                            showToast('Error', data.message, 'error');
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        showToast('Error', 'An error occurred. Please try again.', 'error');
                    });
                });
            });
            
            // Reject button listeners
            document.querySelectorAll('.reject-request-btn').forEach(button => {
                button.addEventListener('click', function(e) {
                    e.stopPropagation(); // Prevent notification item click
                    const userId = this.getAttribute('data-user-id');
                    
                    // Send AJAX request to reject the connection
                    const formData = new FormData();
                    formData.append('action', 'reject_request');
                    formData.append('user_id', userId);
                    
                    fetch('../model/networking/connections.php', {
                        method: 'POST',
                        body: formData
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            // Replace buttons with a declined message
                            const actionsDiv = this.parentElement;
                            actionsDiv.innerHTML = '<p class="text-red-500"><i class="fas fa-times"></i> Request declined</p>';
                            
                            // Show a toast notification
                            showToast('Success', 'Connection request declined', 'success');
                            
                            // Reload notifications after a short delay
                            setTimeout(() => {
                                loadNotifications();
                            }, 2000);
                        } else {
                            showToast('Error', data.message, 'error');
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        showToast('Error', 'An error occurred. Please try again.', 'error');
                    });
                });
            });
        }
        
        // Mark all notifications as read
        markAllReadBtn.addEventListener('click', function() {
            fetch('../model/networking/notifications.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'action=mark_all_read'
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const unreadItems = notificationsList.querySelectorAll('.unread');
                    unreadItems.forEach(item => item.classList.remove('unread'));
                    
                    // Hide notification dot
                    const notificationDot = document.querySelector('.notification-dot');
                    if (notificationDot) {
                        notificationDot.style.display = 'none';
                    }
                    
                    showToast('Success', 'All notifications marked as read', 'success');
                }
            })
            .catch(error => {
                console.error('Error marking notifications as read:', error);
                showToast('Error', 'Failed to mark notifications as read', 'error');
            });
        });
        
        // Refresh notifications
        refreshNotificationsBtn.addEventListener('click', loadNotifications);

        // Initial load of notifications dot
        fetch('../view/get_notifications.php')
            .then(response => response.json())
            .then(data => {
                updateNotificationDot(data.unread_count);
            })
            .catch(error => {
                console.error('Error loading notifications count:', error);
            });

        // Helper function to update notification dot
        function updateNotificationDot(count) {
            const notificationDot = document.querySelector('.notification-dot');
            if (notificationDot) {
                if (count > 0) {
                    notificationDot.textContent = count > 9 ? '9+' : count;
                    notificationDot.style.display = 'flex';
                } else {
                    notificationDot.style.display = 'none';
                }
            }
        }
    </script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Initialize networking functionality for user profile
            function initializeNetworkingButtons() {
                // Get any profile-related buttons if they exist
                const profileId = <?php echo json_encode($profileUserId); ?>;
                
                if (!profileId) return;

                // Any additional initialization code can go here
            }
            
            // Run initialization
            initializeNetworkingButtons();
        });
    </script>
</body>
</html>