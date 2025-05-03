<?php
session_start();

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    // If no user is logged in, redirect to login page
    header("Location: signin.php");
    exit();
}

// Include networking functionality for notifications
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
    
    // Fetch user data
    $stmt = $conn->prepare("SELECT * FROM users WHERE user_id = :user_id");
    $stmt->bindParam(':user_id', $_SESSION['user_id']);
    $stmt->execute();
    
    // Check if user exists
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
        // User not found, redirect to login
        header("Location: signin.php");
        exit();
    }
    
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
            height: 240px; /* Reduced height since content moved to white section */
            position: relative;
        }
        
        @keyframes gradient-animation {
            0% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
            100% { background-position: 0% 50%; }
        }
        
        .profile-photo {
    width: 135px;  /* Augmenté de 100px à 150px */
    height: 150px; /* Augmenté de 100px à 150px */
    border-radius: 80%;
    border: 5px solid white;
    overflow: hidden;
    margin-top: -75px; /* Ajusté pour correspondre à la moitié de la nouvelle hauteur */
    position: relative;
    z-index: 6;
}
.profile-photo mb-12{

width: 180px;  /* Augmenté de 100px à 150px */
    height: 180px;
}
.mb-12 {
    margin-bottom: 0mm;
}
.block-text-gray-500-text-sm{
    text-size-adjust: 10px;
}
        
        .edit-profile-btn {
            background-color: #1c2331;
            color: white;
            border-radius: 25px;
            font-weight: 500;
            padding: 8px 20px;
        }
        
        .settings-btn {
            background-color: white;
            color: #333;
            border-radius: 25px;
            font-weight: 500;
            padding: 8px 20px;
            border: 1px solid #e5e7eb;
        }
        
        .current-role {
            background-color:#e5e7eb8a;
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
        
        .profile-section {
            background-color: white;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
            margin-bottom: 20px;
            overflow: hidden;
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
            margin-bottom: 4.5mm;
            
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
            font-weight: bold;
            color: #000000;
            margin-left: 3.5mm;
            margin-bottom: 3mm;
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
              color: #3b82f6;
          }
          
          .nav-links a.active:after {
              content: '';
              position: absolute;
              bottom: 0;
              left: 0;
              right: 0;
              height: 2px;
              background-color: #3b82f6;
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
              width: 8px;
              height: 8px;
              background-color: #ef4444;
              border-radius: 50%;
          }
          
          .avatar {
              width: 32px;
              height: 32px;
              border-radius: 50%;
              object-fit: cover;
              margin-left: 12px;
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
          .btnlogout{
            border: 1px solid #e5e7eb8a;
            border-radius: 5px;
            background-color:rgba(229, 231, 235, 0.4);
            width: 22mm;
            height :8mm;
            margin-left: 268mm;
            margin-top: 8mm;
            text-align: center;
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
                <a href="../view/networking.php">Networking</a>
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
                    <path d="M21 12.79A9 9 0 1 1 11.21 3 A7 7 0 0 0 21 12.79z"></path>
                </svg>
            </button>
            <div class="user-profile">
                <a href="../view/user-profile.php">
                    <img src="<?php echo htmlspecialchars($profilePicUrl); ?>" alt="User profile" class="avatar">
                </a>
            </div>
        </div>
    </nav>

    <div class="container mx-auto p-4 max-w-6xl">
        <!-- Gradient Header - Reduced height as content moved to white section -->
        <div class="gradient-header mb-0 overflow-hidden">
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
                    <p class="text-md mb-4 text-gray-500"><?php echo htmlspecialchars($userData['city']); ?>, <?php echo htmlspecialchars($userData['country']); ?></p>
            
                    <!-- Boutons d'action -->
                    <div class="flex gap-3">
                        <button class="edit-profile-btn bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600 transition" onclick="window.location.href='profile-setup-edit.php'">Edit Profile</button>
                        <button class="settings-btn bg-gray-200 text-gray-800 px-4 py-2 rounded hover:bg-gray-300 transition" onclick="window.location.href='logout.php'">Log Out</button>
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
            
            <!-- Three Action Boxes -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-6">
                <div class="action-box">
                    <div>
                        <h4 class="font-medium text-gray-700 mb-1">Ready for work</h4>
                        <p class="text-sm text-gray-500">Show recruiters that you're ready for work.</p>
                    </div>
                    <div class="w-10 h-10 flex items-center justify-center rounded-full border-2 border-blue-500 text-blue-500 bg-transparent">
                        <i class="fas fa-arrow-right"></i>
                      </div>
                      
                      
                </div>
                
                <div class="action-box">
                    <div>
                        <h4 class="font-medium text-gray-700 mb-1">Share posts</h4>
                        <p class="text-sm text-gray-500">Share latest news to get connected with others.</p>
                    </div>
                    <div class="w-10 h-10 flex items-center justify-center rounded-full border-2 border-blue-500 text-blue-500 bg-transparent">
                        <i class="fas fa-arrow-right"></i>
                      </div>
                      
                </div>
                
                <div class="action-box">
                    <div>
                        <h4 class="font-medium text-gray-700 mb-1">Update</h4>
                        <p class="text-sm text-gray-500">Keep your profile updated so that recruiters know you better.</p>
                    </div>
                    <div class="w-10 h-10 flex items-center justify-center rounded-full border-2 border-blue-500 text-blue-500 bg-transparent">
                        <i class="fas fa-arrow-right"></i>
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
                    <h3 class="section-title text-xl">Contact Information</h3>
                    <div class="flex items-center mb-3">
                        <i class="fas fa-envelope text-gray-500 w-8"></i>
                        <span><?php echo htmlspecialchars($userData['email']); ?></span>
                    </div>
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
                <a href="#" class="text-gray-500 hover:text-red-500"><i class="fas fa-envelope"></i></a>
            </div>
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