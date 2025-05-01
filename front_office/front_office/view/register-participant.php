<?php
session_start();
require_once '../model/db_connection.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    // Redirect to login page if not logged in
    header("Location: signin.php?redirect=" . urlencode($_SERVER['REQUEST_URI']));
    exit();
}

// Check if hackathon ID is provided in URL
if (!isset($_GET['hackathon_id'])) {
    header("Location: hackathons.php");
    exit();
}

$hackathonId = intval($_GET['hackathon_id']);
$userId = $_SESSION['user_id'];
$editMode = isset($_GET['edit_mode']) && $_GET['edit_mode'] === 'true';
$participantData = null;
$isTeam = false;
$teamData = null;
$teamMembers = [];

// Connect to database
try {
    $conn = getConnection();
    
    // Check if user is already registered for this hackathon
    $checkSql = "SELECT * FROM participants WHERE hackathon_id = :hackathon_id AND user_id = :user_id";
    $checkStmt = $conn->prepare($checkSql);
    $checkStmt->bindParam(":hackathon_id", $hackathonId);
    $checkStmt->bindParam(":user_id", $userId);
    $checkStmt->execute();
    
    if ($checkStmt->rowCount() > 0) {
        $participantData = $checkStmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$editMode) {
            // User already registered and not in edit mode - redirect
            header("Location: hackathon-details.php?id=$hackathonId&error=already_registered");
            exit();
        }
        
        // Check if participant is part of a team
        if (!empty($participantData['team_name'])) {
            $isTeam = true;
            
            // Get team data
            $teamSql = "SELECT * FROM participants WHERE hackathon_id = :hackathon_id AND team_name = :team_name ORDER BY id ASC";
            $teamStmt = $conn->prepare($teamSql);
            $teamStmt->bindParam(":hackathon_id", $hackathonId);
            $teamStmt->bindParam(":team_name", $participantData['team_name']);
            $teamStmt->execute();
            
            $teamData = $teamStmt->fetchAll(PDO::FETCH_ASSOC);
            
            // First member is the leader, rest are team members
            $leaderData = $teamData[0];
            if (count($teamData) > 1) {
                $teamMembers = array_slice($teamData, 1);
            }
        }
    }
    
    // Fetch hackathon details
    $hackathonSql = "SELECT * FROM hackathons WHERE id = :id";
    $hackathonStmt = $conn->prepare($hackathonSql);
    $hackathonStmt->bindParam(":id", $hackathonId);
    $hackathonStmt->execute();
    
    $hackathon = $hackathonStmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$hackathon) {
        header("Location: hackathons.php");
        exit();
    }
    
    // Check if hackathon has already started
    $currentDateTime = date('Y-m-d H:i:s');
    $startDateTime = $hackathon['start_date'] . ' ' . $hackathon['start_time'];
    
    if ($currentDateTime >= $startDateTime) {
        header("Location: hackathon-details.php?id=$hackathonId&error=closed");
        exit();
    }
    
    // Check if hackathon is full (only if not in edit mode)
    if (!$editMode) {
        $participantCountSql = "SELECT COUNT(*) as count FROM participants WHERE hackathon_id = :hackathon_id";
        $participantCountStmt = $conn->prepare($participantCountSql);
        $participantCountStmt->bindParam(":hackathon_id", $hackathonId);
        $participantCountStmt->execute();
        $participantCount = $participantCountStmt->fetch(PDO::FETCH_ASSOC)['count'];
        
        if ($participantCount >= $hackathon['max_participants']) {
            header("Location: hackathon-details.php?id=$hackathonId&error=full");
            exit();
        }
    }
    
    // Get user details
    $userSql = "SELECT * FROM users WHERE user_id = :id";
    $userStmt = $conn->prepare($userSql);
    $userStmt->bindParam(":id", $userId);
    $userStmt->execute();
    
    $user = $userStmt->fetch(PDO::FETCH_ASSOC);
    
} catch(PDOException $e) {
    echo "Connection failed: " . $e->getMessage();
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register for <?php echo htmlspecialchars($hackathon['name']); ?></title>
    <link rel="stylesheet" href="../view/styles.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        .registration-container {
            max-width: 900px;
            margin: 40px auto;
            padding: 30px;
            background-color: white;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }
        
        .registration-header {
            text-align: center;
            margin-bottom: 30px;
        }
        
        .registration-header h1 {
            font-size: 2rem;
            color: #333;
            margin-bottom: 10px;
        }
        
        .registration-header p {
            color: #666;
            font-size: 1rem;
        }
        
        .form-tabs {
            display: flex;
            justify-content: center;
            margin-bottom: 30px;
            border-bottom: 1px solid #e5e7eb;
        }
        
        .form-tab {
            padding: 12px 24px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s;
            position: relative;
            color: #6b7280;
        }
        
        .form-tab.active {
            color: #6366f1;
        }
        
        .form-tab.active::after {
            content: '';
            position: absolute;
            bottom: -1px;
            left: 0;
            right: 0;
            height: 2px;
            background-color: #6366f1;
        }
        
        .form-section {
            display: none;
        }
        
        .form-section.active {
            display: block;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            color: #374151;
        }
        
        .form-control {
            width: 100%;
            padding: 10px 12px;
            border: 1px solid #d1d5db;
            border-radius: 6px;
            font-size: 1rem;
            transition: border-color 0.3s;
        }
        
        .form-control:focus {
            outline: none;
            border-color: #6366f1;
            box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.1);
        }
        
        .submit-btn {
            background-color: #6366f1;
            color: white;
            border: none;
            padding: 12px 25px;
            border-radius: 8px;
            font-weight: 600;
            font-size: 1rem;
            cursor: pointer;
            transition: background-color 0.3s, transform 0.2s;
            display: block;
            width: 100%;
            margin-top: 30px;
        }
        
        .submit-btn:hover {
            background-color: #4f46e5;
            transform: translateY(-2px);
        }
        
        .team-section {
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #e5e7eb;
        }
        
        .team-members {
            margin-top: 20px;
        }
        
        .team-member {
            background-color: #f9fafb;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 15px;
            position: relative;
        }
        
        .remove-member {
            position: absolute;
            top: 10px;
            right: 10px;
            background: none;
            border: none;
            color: #ef4444;
            cursor: pointer;
            font-size: 1.2rem;
        }
        
        .add-team-member {
            display: flex;
            align-items: center;
            justify-content: center;
            color: #6366f1;
            background: none;
            border: 2px dashed #d1d5db;
            border-radius: 8px;
            padding: 15px;
            width: 100%;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s;
        }
        
        .add-team-member:hover {
            border-color: #6366f1;
            background-color: rgba(99, 102, 241, 0.05);
        }
        
        .add-team-member svg {
            margin-right: 8px;
        }
        
        .preview-image {
            max-width: 150px;
            max-height: 150px;
            margin-top: 10px;
            border-radius: 8px;
            display: block;
        }
        
        /* Error Message Styles */
        .alert-error {
            padding: 12px 15px;
            margin-bottom: 20px;
            border-radius: 6px;
            background-color: #fee2e2;
            border: 1px solid #fecaca;
            color: #b91c1c;
            font-weight: 500;
        }
        
        .alert-success {
            padding: 12px 15px;
            margin-bottom: 20px;
            border-radius: 6px;
            background-color: #dcfce7;
            border: 1px solid #bbf7d0;
            color: #15803d;
            font-weight: 500;
        }
        
        .error-details {
            margin-top: 10px;
            padding: 10px;
            background-color: rgba(0,0,0,0.05);
            border-radius: 4px;
            font-family: monospace;
            white-space: pre-wrap;
            font-size: 0.9em;
        }
        
        /* Dark theme adjustments */
        :root[data-theme="dark"] .registration-container {
            background-color: #1e293b;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.3);
        }
        
        :root[data-theme="dark"] .registration-header h1 {
            color: #e2e8f0;
        }
        
        :root[data-theme="dark"] .registration-header p,
        :root[data-theme="dark"] .form-group label {
            color: #e2e8f0;
        }
        
        :root[data-theme="dark"] .form-tabs {
            border-bottom-color: #334155;
        }
        
        :root[data-theme="dark"] .form-tab {
            color: #94a3b8;
        }
        
        :root[data-theme="dark"] .form-tab.active {
            color: #818cf8;
        }
        
        :root[data-theme="dark"] .form-tab.active::after {
            background-color: #818cf8;
        }
        
        :root[data-theme="dark"] .form-control {
            background-color: #0f172a;
            border-color: #334155;
            color: #e2e8f0;
        }
        
        :root[data-theme="dark"] .form-control:focus {
            border-color: #818cf8;
            box-shadow: 0 0 0 3px rgba(129, 140, 248, 0.1);
        }
        
        :root[data-theme="dark"] .team-member {
            background-color: #1e293b;
        }
        
        :root[data-theme="dark"] .add-team-member {
            color: #818cf8;
            border-color: #334155;
        }
        
        :root[data-theme="dark"] .add-team-member:hover {
            border-color: #818cf8;
            background-color: rgba(129, 140, 248, 0.05);
        }
        
        :root[data-theme="dark"] .alert-error {
            background-color: rgba(239, 68, 68, 0.15);
            border-color: rgba(239, 68, 68, 0.3);
            color: #fca5a5;
        }
        
        :root[data-theme="dark"] .alert-success {
            background-color: rgba(34, 197, 94, 0.15);
            border-color: rgba(34, 197, 94, 0.3);
            color: #86efac;
        }
        
        :root[data-theme="dark"] .error-details {
            background-color: rgba(255,255,255,0.05);
            color: #e2e8f0;
        }
    </style>
</head>
<body>
    <div class="app-container">
        <!-- Navbar -->
        <nav class="navbar">
            <div class="nav-left">
                <a href="#" class="logo">
                    <img src="../assets/logo.png" alt="Only Engineers">
                </a>
            </div>
            <div class="nav-center">
                <nav class="nav-links">
                    <a href="home.html">Home</a>
                    <a href="../view/Dashboard.html">Dashboard</a>
                    <a href="../view/index.html">Jobs</a>
                    <a href="../view/projects.html">Projects</a>
                    <a href="../view/courses.html">Courses</a>
                    <a href="../view/hackathons.php" class="active">Hackathons</a>
                    <a href="../view/articles.html">Articles</a>
                    <a href="networking.php">Networking</a>
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
                    <a href="../view/user-profile.php">
                        <img src="../assets/profil.jpg" alt="User profile" class="avatar">
                    </a>
                </div>
            </div>
        </nav>

        <div class="registration-container">
            <div class="registration-header">
                <h1><?php echo $editMode ? 'Edit Registration for ' : 'Register for '; ?><?php echo htmlspecialchars($hackathon['name']); ?></h1>
                <p><?php echo $editMode ? 'Update your registration information below.' : 'Please complete the registration form below to participate in this hackathon.'; ?></p>
            </div>
            
            <?php
            // Display error messages if any
            if (isset($_GET['error'])) {
                $errorType = $_GET['error'];
                $errorMessage = '';
                $errorDetails = '';
                
                switch ($errorType) {
                    case 'missing_fields':
                        $errorMessage = 'Please fill in all required fields.';
                        break;
                    case 'missing_team_name':
                        $errorMessage = 'Please enter a team name.';
                        break;
                    case 'missing_leader_fields':
                        $errorMessage = 'Please complete all team leader information.';
                        break;
                    case 'photo_directory_creation':
                        $errorMessage = 'Unable to create photo directory. Please contact support.';
                        break;
                    case 'database_error':
                        $errorMessage = 'A database error occurred. Please try again.';
                        break;
                    case 'database_connection':
                        $errorMessage = 'Unable to connect to database. Please try again later.';
                        break;
                    case 'team_registration_failed':
                        $errorMessage = 'Team registration failed. Please check the error details below:';
                        
                        // Get specific error from URL parameter if available
                        if (isset($_GET['error_detail'])) {
                            $specificError = $_GET['error_detail'];
                            switch ($specificError) {
                                case 'duplicate_team':
                                    $errorDetails = "A team with this name already exists. Please choose a different team name.";
                                    break;
                                case 'invalid_member_data':
                                    $errorDetails = "One or more team members has invalid information. Please check all fields.";
                                    break;
                                case 'db_connection':
                                    $errorDetails = "Database connection error. Please try again later.";
                                    break;
                                case 'photo_upload':
                                    $errorDetails = "Error uploading one or more photos. Please check file sizes and formats.";
                                    break;
                                default:
                                    $errorDetails = "Error type: " . htmlspecialchars($specificError);
                            }
                        } else {
                            $errorDetails = "- Duplicate team name or email\n- Database connectivity issue\n- Problem with photo uploads\n- One or more form fields might have invalid data\n\nPlease check the information and try again. If the problem persists, contact support.";
                        }
                        
                        // Retrieve log information if available
                        $logFile = '../model/participant_registration.log';
                        if (file_exists($logFile) && is_readable($logFile)) {
                            $logContent = file_get_contents($logFile);
                            $logEntries = explode("\n", $logContent);
                            $relevantEntries = array_filter($logEntries, function($entry) {
                                return strpos($entry, 'ERREUR') !== false || strpos($entry, 'EXCEPTION') !== false;
                            });
                            if (!empty($relevantEntries)) {
                                $lastErrors = array_slice($relevantEntries, -5);
                                $errorDetails .= "\n\nRecent error details:\n" . implode("\n", $lastErrors);
                            }
                        }
                        break;
                    case 'general_error':
                        $errorMessage = 'An unexpected error occurred. Please try again.';
                        break;
                    default:
                        $errorMessage = 'An error occurred: ' . htmlspecialchars($errorType);
                }
                
                echo '<div class="alert-error">';
                echo '<strong>Error:</strong> ' . $errorMessage;
                
                if (!empty($errorDetails)) {
                    echo '<div class="error-details">' . htmlspecialchars($errorDetails) . '</div>';
                }
                
                echo '</div>';
            }
            
            // Display success message if any
            if (isset($_GET['success'])) {
                $successType = $_GET['success'];
                $successMessage = '';
                
                switch ($successType) {
                    case 'registered':
                        $successMessage = 'You have successfully registered for this hackathon!';
                        break;
                    case 'team_registered':
                        $successMessage = 'Your team has been successfully registered for this hackathon!';
                        break;
                    case 'updated':
                        $successMessage = 'Your registration information has been successfully updated!';
                        break;
                    default:
                        $successMessage = 'Success: ' . htmlspecialchars($successType);
                }
                
                echo '<div class="alert-success">';
                echo '<strong>Success!</strong> ' . $successMessage;
                echo '</div>';
            }
            ?>
            
            <?php if ($isTeam): ?>
            <div class="form-tabs">
                <div class="form-tab" data-tab="individual">Individual Registration</div>
                <div class="form-tab active" data-tab="team">Team Registration</div>
            </div>
            <?php else: ?>
            <div class="form-tabs">
                <div class="form-tab active" data-tab="individual">Individual Registration</div>
                <div class="form-tab" data-tab="team">Team Registration</div>
            </div>
            <?php endif; ?>
            
            <!-- Individual Registration Form -->
            <form id="individual-form" class="form-section <?php echo !$isTeam ? 'active' : ''; ?>" action="../model/register_participant.php" method="post" enctype="multipart/form-data">
                <input type="hidden" name="hackathon_id" value="<?php echo $hackathonId; ?>">
                <input type="hidden" name="participation_type" value="individual">
                <?php if ($editMode && !$isTeam && $participantData): ?>
                <input type="hidden" name="edit_mode" value="true">
                <input type="hidden" name="participant_id" value="<?php echo $participantData['id']; ?>">
                <?php endif; ?>
                
                <div class="form-group">
                    <label for="individual-name">Full Name (max 25 characters)</label>
                    <input type="text" id="individual-name" name="full_name" class="form-control" value="<?php echo htmlspecialchars(($editMode && !$isTeam && $participantData) ? $participantData['full_name'] : ($user['full_name'] ?? '')); ?>" required maxlength="25">
                    <span class="error-message" id="individual-name-error"></span>
                </div>
                
                <div class="form-group">
                    <label for="individual-email">Email</label>
                    <input type="email" id="individual-email" name="email" class="form-control" value="<?php echo htmlspecialchars(($editMode && !$isTeam && $participantData) ? $participantData['email'] : ($user['email'] ?? '')); ?>" required>
                    <span class="error-message" id="individual-email-error"></span>
                </div>
                
                <div class="form-group">
                    <label for="individual-phone">Phone Number (8 digits)</label>
                    <input type="tel" id="individual-phone" name="phone" class="form-control" value="<?php echo htmlspecialchars(($editMode && !$isTeam && $participantData) ? $participantData['phone'] : ''); ?>" required maxlength="8" pattern="[0-9]{8}">
                    <span class="error-message" id="individual-phone-error"></span>
                </div>
                
                <div class="form-group">
                    <label for="individual-role">Your Role (required)</label>
                    <select id="individual-role" name="role" class="form-control" required>
                        <option value="">Select your role</option>
                        <option value="developer" <?php echo ($editMode && !$isTeam && $participantData && $participantData['role'] === 'developer') ? 'selected' : ''; ?>>Developer</option>
                        <option value="designer" <?php echo ($editMode && !$isTeam && $participantData && $participantData['role'] === 'designer') ? 'selected' : ''; ?>>Designer</option>
                        <option value="product_manager" <?php echo ($editMode && !$isTeam && $participantData && $participantData['role'] === 'product_manager') ? 'selected' : ''; ?>>Product Manager</option>
                        <option value="data_scientist" <?php echo ($editMode && !$isTeam && $participantData && $participantData['role'] === 'data_scientist') ? 'selected' : ''; ?>>Data Scientist</option>
                        <option value="business_analyst" <?php echo ($editMode && !$isTeam && $participantData && $participantData['role'] === 'business_analyst') ? 'selected' : ''; ?>>Business Analyst</option>
                        <option value="other" <?php echo ($editMode && !$isTeam && $participantData && $participantData['role'] === 'other') ? 'selected' : ''; ?>>Other</option>
                    </select>
                    <span class="error-message" id="individual-role-error"></span>
                </div>
                
                <div class="form-group">
                    <label for="individual-photo">Your Photo</label>
                    <input type="file" id="individual-photo" name="photo" class="form-control" accept="image/*" <?php echo !$editMode ? '' : ''; ?>>
                    <small class="form-text text-muted">
                        <?php if ($editMode && !$isTeam && $participantData && !empty($participantData['photo'])): ?>
                            Current photo: <?php echo htmlspecialchars(basename($participantData['photo'])); ?>.
                            Only upload a new photo if you want to change it.
                        <?php else: ?>
                            Optional: You can upload a profile photo.
                        <?php endif; ?>
                    </small>
                    <?php if ($editMode && !$isTeam && $participantData && !empty($participantData['photo'])): ?>
                        <?php 
                            $photoPath = '../ressources/participant_photos/' . basename($participantData['photo']);
                            if (file_exists($photoPath)):
                        ?>
                            <img id="individual-photo-preview" src="<?php echo $photoPath; ?>" class="preview-image" style="display: block; margin-top: 10px; max-width: 150px;">
                        <?php else: ?>
                            <img id="individual-photo-preview" class="preview-image" style="display: none;">
                        <?php endif; ?>
                    <?php else: ?>
                        <img id="individual-photo-preview" class="preview-image" style="display: none;">
                    <?php endif; ?>
                </div>
                
                <button type="submit" class="submit-btn">
                    <?php echo $editMode ? 'Update Registration' : 'Register for Hackathon'; ?>
                </button>
            </form>
            
            <!-- Team Registration Form -->
            <form id="team-form" class="form-section <?php echo $isTeam ? 'active' : ''; ?>" action="../model/register_participant.php" method="post" enctype="multipart/form-data">
                <input type="hidden" name="hackathon_id" value="<?php echo $hackathonId; ?>">
                <input type="hidden" name="participation_type" value="team">
                <input type="hidden" name="team_size" id="team_size" value="<?php echo $editMode && $isTeam && $teamData ? count($teamData) : '2'; ?>">
                <?php if ($editMode && $isTeam && $participantData): ?>
                <input type="hidden" name="edit_mode" value="true">
                <input type="hidden" name="team_id" value="<?php echo $participantData['id']; ?>">
                <?php endif; ?>
                
                <!-- Team Name -->
                <div class="form-group">
                    <label for="team-name">Team Name (max 15 characters)</label>
                    <input type="text" id="team-name" name="team_name" class="form-control" value="<?php echo htmlspecialchars(($editMode && $isTeam && $participantData) ? $participantData['team_name'] : ''); ?>" required maxlength="15" <?php echo ($editMode && $isTeam) ? 'readonly' : ''; ?>>
                    <?php if ($editMode && $isTeam): ?>
                    <small class="form-text text-muted">Team name cannot be changed after registration.</small>
                    <?php endif; ?>
                    <span class="error-message" id="team-name-error"></span>
                </div>
                
                <!-- Team Leader Information -->
                <h3>Team Leader Information</h3>
                <div class="form-group">
                    <label for="team-leader-name">Full Name (max 25 characters)</label>
                    <input type="text" id="team-leader-name" name="leader_name" class="form-control" value="<?php echo htmlspecialchars(($editMode && $isTeam && isset($leaderData)) ? $leaderData['full_name'] : ($user['full_name'] ?? '')); ?>" required maxlength="25">
                    <span class="error-message" id="team-leader-name-error"></span>
                </div>

                <div class="form-group">
                    <label for="team-leader-email">Email</label>
                    <input type="email" id="team-leader-email" name="leader_email" class="form-control" value="<?php echo htmlspecialchars(($editMode && $isTeam && isset($leaderData)) ? $leaderData['email'] : ($user['email'] ?? '')); ?>" required>
                    <span class="error-message" id="team-leader-email-error"></span>
                </div>

                <div class="form-group">
                    <label for="team-leader-phone">Phone Number (8 digits)</label>
                    <input type="tel" id="team-leader-phone" name="leader_phone" class="form-control" value="<?php echo htmlspecialchars(($editMode && $isTeam && isset($leaderData)) ? $leaderData['phone'] : ''); ?>" required maxlength="8" pattern="[0-9]{8}">
                    <span class="error-message" id="team-leader-phone-error"></span>
                </div>

                <div class="form-group">
                    <label for="team-leader-role">Leader Role (required)</label>
                    <select id="team-leader-role" name="leader_role" class="form-control" required>
                        <option value="">Select your role</option>
                        <option value="developer" <?php echo ($editMode && $isTeam && isset($leaderData) && $leaderData['role'] === 'developer') ? 'selected' : ''; ?>>Developer</option>
                        <option value="designer" <?php echo ($editMode && $isTeam && isset($leaderData) && $leaderData['role'] === 'designer') ? 'selected' : ''; ?>>Designer</option>
                        <option value="product_manager" <?php echo ($editMode && $isTeam && isset($leaderData) && $leaderData['role'] === 'product_manager') ? 'selected' : ''; ?>>Product Manager</option>
                        <option value="data_scientist" <?php echo ($editMode && $isTeam && isset($leaderData) && $leaderData['role'] === 'data_scientist') ? 'selected' : ''; ?>>Data Scientist</option>
                        <option value="business_analyst" <?php echo ($editMode && $isTeam && isset($leaderData) && $leaderData['role'] === 'business_analyst') ? 'selected' : ''; ?>>Business Analyst</option>
                        <option value="other" <?php echo ($editMode && $isTeam && isset($leaderData) && $leaderData['role'] === 'other') ? 'selected' : ''; ?>>Other</option>
                    </select>
                    <span class="error-message" id="team-leader-role-error"></span>
                </div>

                <div class="form-group">
                    <label for="team-leader-photo">Your Photo</label>
                    <input type="file" id="team-leader-photo" name="leader_photo" class="form-control" accept="image/*" <?php echo ($editMode && $isTeam) ? '' : 'required'; ?>>
                    <?php if ($editMode && $isTeam && isset($leaderData) && !empty($leaderData['photo'])): ?>
                    <small class="form-text text-muted">Current photo: <?php echo htmlspecialchars(basename($leaderData['photo'])); ?>. Only upload a new photo if you want to change it.</small>
                    <?php 
                        $photoPath = '../ressources/participant_photos/' . basename($leaderData['photo']);
                        if (file_exists($photoPath)):
                    ?>
                        <img id="team-leader-photo-preview" src="<?php echo $photoPath; ?>" class="preview-image" style="display: block; margin-top: 10px; max-width: 150px;">
                    <?php endif; ?>
                    <?php else: ?>
                    <img id="team-leader-photo-preview" class="preview-image" style="display: none;">
                    <?php endif; ?>
                </div>
                
                <!-- Team Members Section -->
                <div class="team-section">
                    <h3>Team Members</h3>
                    <p>Please add the details of your team members below.</p>
                    
                    <div class="team-members" id="team-members-container">
                    <?php if ($editMode && $isTeam && count($teamMembers) > 2): 
                        // Display additional team members (beyond member2 and member3)
                        for ($i = 3; $i < count($teamMembers); $i++): 
                            $memberIndex = $i - 2; // Adjust index as member2 is 1 and member3 is 2
                            $member = $teamMembers[$i];
                    ?>
                        <div class="team-member" id="team-member-<?php echo $memberIndex; ?>">
                            <button type="button" class="remove-member">&times;</button>
                            <div class="form-group">
                                <label for="member-name-<?php echo $memberIndex; ?>">Full Name (max 25 characters)</label>
                                <input type="text" id="member-name-<?php echo $memberIndex; ?>" name="team_members[<?php echo $memberIndex; ?>][full_name]" class="form-control" value="<?php echo htmlspecialchars($member['full_name'] ?? ''); ?>" required maxlength="25">
                                <span class="error-message" id="member-name-<?php echo $memberIndex; ?>-error"></span>
                            </div>
                            <div class="form-group">
                                <label for="member-email-<?php echo $memberIndex; ?>">Email</label>
                                <input type="email" id="member-email-<?php echo $memberIndex; ?>" name="team_members[<?php echo $memberIndex; ?>][email]" class="form-control" value="<?php echo htmlspecialchars($member['email'] ?? ''); ?>" required>
                                <span class="error-message" id="member-email-<?php echo $memberIndex; ?>-error"></span>
                            </div>
                            <div class="form-group">
                                <label for="member-phone-<?php echo $memberIndex; ?>">Phone Number (8 digits)</label>
                                <input type="tel" id="member-phone-<?php echo $memberIndex; ?>" name="team_members[<?php echo $memberIndex; ?>][phone]" class="form-control" value="<?php echo htmlspecialchars($member['phone'] ?? ''); ?>" required maxlength="8" pattern="[0-9]{8}">
                                <span class="error-message" id="member-phone-<?php echo $memberIndex; ?>-error"></span>
                            </div>
                            <div class="form-group">
                                <label for="member-role-<?php echo $memberIndex; ?>">Role (required)</label>
                                <select id="member-role-<?php echo $memberIndex; ?>" name="team_members[<?php echo $memberIndex; ?>][role]" class="form-control" required>
                                    <option value="">Select role</option>
                                    <option value="developer" <?php echo (isset($member['role']) && $member['role'] === 'developer') ? 'selected' : ''; ?>>Developer</option>
                                    <option value="designer" <?php echo (isset($member['role']) && $member['role'] === 'designer') ? 'selected' : ''; ?>>Designer</option>
                                    <option value="product_manager" <?php echo (isset($member['role']) && $member['role'] === 'product_manager') ? 'selected' : ''; ?>>Product Manager</option>
                                    <option value="data_scientist" <?php echo (isset($member['role']) && $member['role'] === 'data_scientist') ? 'selected' : ''; ?>>Data Scientist</option>
                                    <option value="business_analyst" <?php echo (isset($member['role']) && $member['role'] === 'business_analyst') ? 'selected' : ''; ?>>Business Analyst</option>
                                    <option value="other" <?php echo (isset($member['role']) && $member['role'] === 'other') ? 'selected' : ''; ?>>Other</option>
                                </select>
                                <span class="error-message" id="member-role-<?php echo $memberIndex; ?>-error"></span>
                            </div>
                            <div class="form-group">
                                <label for="member-photo-<?php echo $memberIndex; ?>">Photo</label>
                                <input type="file" id="member-photo-<?php echo $memberIndex; ?>" name="team_members[<?php echo $memberIndex; ?>][photo]" class="form-control" accept="image/*">
                                <?php if (!empty($member['photo'])): ?>
                                <small class="form-text text-muted">Current photo: <?php echo htmlspecialchars(basename($member['photo'])); ?>. Only upload a new photo if you want to change it.</small>
                                <?php 
                                    $photoPath = '../ressources/participant_photos/' . basename($member['photo']);
                                    if (file_exists($photoPath)):
                                ?>
                                    <img class="preview-image member-photo-preview" id="member-photo-preview-<?php echo $memberIndex; ?>" src="<?php echo $photoPath; ?>" style="display: block; margin-top: 10px; max-width: 150px;">
                                <?php else: ?>
                                    <img class="preview-image member-photo-preview" id="member-photo-preview-<?php echo $memberIndex; ?>" style="display: none;">
                                <?php endif; ?>
                                <?php else: ?>
                                <img class="preview-image member-photo-preview" id="member-photo-preview-<?php echo $memberIndex; ?>" style="display: none;">
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endfor; ?>
                    <?php endif; ?>
                    </div>
                    
                    <button type="button" class="add-team-member" id="add-team-member">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M12 5v14M5 12h14"></path>
                        </svg>
                        Add Team Member
                    </button>
                </div>
                
                <hr>
                
                <!-- Member 2 Details -->
                <h3>Team Member 2</h3>
                <div class="team-member">
                    <div class="form-group">
                        <label for="member2-name">Full Name</label>
                        <input type="text" id="member2-name" name="member2_name" class="form-control" value="<?php echo htmlspecialchars(($editMode && $isTeam && isset($teamMembers[0])) ? $teamMembers[0]['full_name'] : ''); ?>" required maxlength="25">
                    </div>
                    
                    <div class="form-group">
                        <label for="member2-email">Email</label>
                        <input type="email" id="member2-email" name="member2_email" class="form-control" value="<?php echo htmlspecialchars(($editMode && $isTeam && isset($teamMembers[0])) ? $teamMembers[0]['email'] : ''); ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="member2-phone">Phone Number</label>
                        <input type="tel" id="member2-phone" name="member2_phone" class="form-control" value="<?php echo htmlspecialchars(($editMode && $isTeam && isset($teamMembers[0])) ? $teamMembers[0]['phone'] : ''); ?>" required maxlength="8" pattern="[0-9]{8}">
                    </div>
                    
                    <div class="form-group">
                        <label for="member2-role">Role (required)</label>
                        <select id="member2-role" name="member2_role" class="form-control" required>
                            <option value="">Select role</option>
                            <option value="developer" <?php echo ($editMode && $isTeam && isset($teamMembers[0]) && $teamMembers[0]['role'] === 'developer') ? 'selected' : ''; ?>>Developer</option>
                            <option value="designer" <?php echo ($editMode && $isTeam && isset($teamMembers[0]) && $teamMembers[0]['role'] === 'designer') ? 'selected' : ''; ?>>Designer</option>
                            <option value="product_manager" <?php echo ($editMode && $isTeam && isset($teamMembers[0]) && $teamMembers[0]['role'] === 'product_manager') ? 'selected' : ''; ?>>Product Manager</option>
                            <option value="data_scientist" <?php echo ($editMode && $isTeam && isset($teamMembers[0]) && $teamMembers[0]['role'] === 'data_scientist') ? 'selected' : ''; ?>>Data Scientist</option>
                            <option value="business_analyst" <?php echo ($editMode && $isTeam && isset($teamMembers[0]) && $teamMembers[0]['role'] === 'business_analyst') ? 'selected' : ''; ?>>Business Analyst</option>
                            <option value="other" <?php echo ($editMode && $isTeam && isset($teamMembers[0]) && $teamMembers[0]['role'] === 'other') ? 'selected' : ''; ?>>Other</option>
                        </select>
                        <span class="error-message" id="member2-role-error"></span>
                    </div>
                    
                    <div class="form-group">
                        <label for="member2-photo">Photo</label>
                        <input type="file" id="member2-photo" name="member2_photo" class="form-control" accept="image/*" <?php echo ($editMode && $isTeam && isset($teamMembers[0]) && !empty($teamMembers[0]['photo'])) ? '' : 'required'; ?>>
                        <?php if ($editMode && $isTeam && isset($teamMembers[0]) && !empty($teamMembers[0]['photo'])): ?>
                        <small class="form-text text-muted">Current photo: <?php echo htmlspecialchars(basename($teamMembers[0]['photo'])); ?>. Only upload a new photo if you want to change it.</small>
                        <?php 
                            $photoPath = '../ressources/participant_photos/' . basename($teamMembers[0]['photo']);
                            if (file_exists($photoPath)):
                        ?>
                            <img id="member2-photo-preview" src="<?php echo $photoPath; ?>" class="preview-image" style="display: block; margin-top: 10px; max-width: 150px;">
                        <?php else: ?>
                            <img id="member2-photo-preview" class="preview-image" style="display: none;">
                        <?php endif; ?>
                        <?php else: ?>
                        <img id="member2-photo-preview" class="preview-image" style="display: none;">
                        <?php endif; ?>
                    </div>
                </div>
                
                <hr>
                
                <!-- Member 3 Details -->
                <h3>Team Member 3</h3>
                <div class="team-member">
                    <div class="form-group">
                        <label for="member3-name">Full Name</label>
                        <input type="text" id="member3-name" name="member3_name" class="form-control" value="<?php echo htmlspecialchars(($editMode && $isTeam && isset($teamMembers[1])) ? $teamMembers[1]['full_name'] : ''); ?>">
                        <small class="form-text text-muted">Optional</small>
                    </div>
                    
                    <div class="form-group">
                        <label for="member3-email">Email</label>
                        <input type="email" id="member3-email" name="member3_email" class="form-control" value="<?php echo htmlspecialchars(($editMode && $isTeam && isset($teamMembers[1])) ? $teamMembers[1]['email'] : ''); ?>">
                        <small class="form-text text-muted">Optional</small>
                    </div>
                    
                    <div class="form-group">
                        <label for="member3-phone">Phone Number</label>
                        <input type="tel" id="member3-phone" name="member3_phone" class="form-control" value="<?php echo htmlspecialchars(($editMode && $isTeam && isset($teamMembers[1])) ? $teamMembers[1]['phone'] : ''); ?>" maxlength="8" pattern="[0-9]{8}">
                        <small class="form-text text-muted">Optional</small>
                    </div>
                    
                    <div class="form-group">
                        <label for="member3-role">Role</label>
                        <select id="member3-role" name="member3_role" class="form-control">
                            <option value="">Select role</option>
                            <option value="developer" <?php echo ($editMode && $isTeam && isset($teamMembers[1]) && $teamMembers[1]['role'] === 'developer') ? 'selected' : ''; ?>>Developer</option>
                            <option value="designer" <?php echo ($editMode && $isTeam && isset($teamMembers[1]) && $teamMembers[1]['role'] === 'designer') ? 'selected' : ''; ?>>Designer</option>
                            <option value="product_manager" <?php echo ($editMode && $isTeam && isset($teamMembers[1]) && $teamMembers[1]['role'] === 'product_manager') ? 'selected' : ''; ?>>Product Manager</option>
                            <option value="data_scientist" <?php echo ($editMode && $isTeam && isset($teamMembers[1]) && $teamMembers[1]['role'] === 'data_scientist') ? 'selected' : ''; ?>>Data Scientist</option>
                            <option value="business_analyst" <?php echo ($editMode && $isTeam && isset($teamMembers[1]) && $teamMembers[1]['role'] === 'business_analyst') ? 'selected' : ''; ?>>Business Analyst</option>
                            <option value="other" <?php echo ($editMode && $isTeam && isset($teamMembers[1]) && $teamMembers[1]['role'] === 'other') ? 'selected' : ''; ?>>Other</option>
                        </select>
                        <small class="form-text text-muted">Optional</small>
                    </div>
                    
                    <div class="form-group">
                        <label for="member3-photo">Photo</label>
                        <input type="file" id="member3-photo" name="member3_photo" class="form-control" accept="image/*">
                        <small class="form-text text-muted">Optional</small>
                        <?php if ($editMode && $isTeam && isset($teamMembers[1]) && !empty($teamMembers[1]['photo'])): ?>
                        <small class="form-text text-muted">Current photo: <?php echo htmlspecialchars(basename($teamMembers[1]['photo'])); ?>. Only upload a new photo if you want to change it.</small>
                        <?php 
                            $photoPath = '../ressources/participant_photos/' . basename($teamMembers[1]['photo']);
                            if (file_exists($photoPath)):
                        ?>
                            <img id="member3-photo-preview" src="<?php echo $photoPath; ?>" class="preview-image" style="display: block; margin-top: 10px; max-width: 150px;">
                        <?php else: ?>
                            <img id="member3-photo-preview" class="preview-image" style="display: none;">
                        <?php endif; ?>
                        <?php else: ?>
                        <img id="member3-photo-preview" class="preview-image" style="display: none;">
                        <?php endif; ?>
                    </div>
                </div>
                
                <button type="submit" class="submit-btn">
                    <?php echo $editMode ? 'Update Team Registration' : 'Register Team for Hackathon'; ?>
                </button>
            </form>
        </div>
    </div>

    <!-- Template for team member form fields -->
    <template id="team-member-template">
        <div class="team-member">
            <button type="button" class="remove-member">&times;</button>
            <div class="form-group">
                <label for="member-name-{index}">Full Name (max 25 characters)</label>
                <input type="text" id="member-name-{index}" name="team_members[{index}][full_name]" class="form-control" required maxlength="25">
                <span class="error-message" id="member-name-{index}-error"></span>
            </div>
            <div class="form-group">
                <label for="member-email-{index}">Email</label>
                <input type="email" id="member-email-{index}" name="team_members[{index}][email]" class="form-control" required>
                <span class="error-message" id="member-email-{index}-error"></span>
            </div>
            <div class="form-group">
                <label for="member-phone-{index}">Phone Number (8 digits)</label>
                <input type="tel" id="member-phone-{index}" name="team_members[{index}][phone]" class="form-control" required maxlength="8" pattern="[0-9]{8}">
                <span class="error-message" id="member-phone-{index}-error"></span>
            </div>
            <div class="form-group">
                <label for="member-role-{index}">Role (required)</label>
                <select id="member-role-{index}" name="team_members[{index}][role]" class="form-control" required>
                    <option value="">Select role</option>
                    <option value="developer">Developer</option>
                    <option value="designer">Designer</option>
                    <option value="product_manager">Product Manager</option>
                    <option value="data_scientist">Data Scientist</option>
                    <option value="business_analyst">Business Analyst</option>
                    <option value="other">Other</option>
                </select>
                <span class="error-message" id="member-role-{index}-error"></span>
            </div>
            <div class="form-group">
                <label for="member-photo-{index}">Photo</label>
                <input type="file" id="member-photo-{index}" name="team_members[{index}][photo]" class="form-control" accept="image/*" required>
                <img class="preview-image member-photo-preview" id="member-photo-preview-{index}" style="display: none;">
            </div>
        </div>
    </template>

    <!-- Toastify CSS -->
    <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/toastify-js/src/toastify.min.css">
    <!-- Toastify JS -->
    <script type="text/javascript" src="https://cdn.jsdelivr.net/npm/toastify-js"></script>
    
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Theme toggle functionality
            const themeToggle = document.getElementById('themeToggle');
            const sunIcon = document.querySelector('.sun-icon');
            const moonIcon = document.querySelector('.moon-icon');
            const htmlRoot = document.documentElement;
            
            // Check for saved theme preference
            const savedTheme = localStorage.getItem('theme') || 'light';
            htmlRoot.setAttribute('data-theme', savedTheme);
            
            // Update icon display based on current theme
            if (savedTheme === 'dark') {
                sunIcon.style.display = 'none';
                moonIcon.style.display = 'block';
            }
            
            // Handle theme toggle click
            themeToggle.addEventListener('click', function() {
                const currentTheme = htmlRoot.getAttribute('data-theme');
                const newTheme = currentTheme === 'light' ? 'dark' : 'light';
                
                // Update theme attribute and save preference
                htmlRoot.setAttribute('data-theme', newTheme);
                localStorage.setItem('theme', newTheme);
                
                // Toggle icon display
                if (newTheme === 'dark') {
                    sunIcon.style.display = 'none';
                    moonIcon.style.display = 'block';
                } else {
                    sunIcon.style.display = 'block';
                    moonIcon.style.display = 'none';
                }
            });
            
            // Form tabs functionality
            const formTabs = document.querySelectorAll('.form-tab');
            const formSections = document.querySelectorAll('.form-section');
            
            formTabs.forEach(tab => {
                tab.addEventListener('click', function() {
                    const tabTarget = this.getAttribute('data-tab');
                    
                    // Update active tab
                    formTabs.forEach(t => t.classList.remove('active'));
                    this.classList.add('active');
                    
                    // Show corresponding form
                    formSections.forEach(section => {
                        section.classList.remove('active');
                        if (section.id === tabTarget + '-form') {
                            section.classList.add('active');
                        }
                    });
                });
            });
            
            // Image preview functionality
            const photoInputs = document.querySelectorAll('input[type="file"]');
            photoInputs.forEach(input => {
                input.addEventListener('change', function() {
                    const previewId = this.id + '-preview';
                    const preview = document.getElementById(previewId);
                    if (preview) {
                        if (this.files && this.files[0]) {
                            const reader = new FileReader();
                            reader.onload = function(e) {
                                preview.src = e.target.result;
                                preview.style.display = 'block';
                            };
                            reader.readAsDataURL(this.files[0]);
                        } else {
                            preview.style.display = 'none';
                        }
                    }
                });
            });
            
            // Dynamic team members functionality
            let memberCount = 0;
            const memberTemplate = document.getElementById('team-member-template').innerHTML;
            const membersContainer = document.getElementById('team-members-container');
            const addMemberButton = document.getElementById('add-team-member');
            
            // Add new team member
            addMemberButton.addEventListener('click', function() {
                memberCount++;
                
                // Create new team member HTML from template
                let memberHtml = memberTemplate.replace(/{index}/g, memberCount);
                
                // Create temporary container to hold the HTML
                const tempContainer = document.createElement('div');
                tempContainer.innerHTML = memberHtml;
                const memberElement = tempContainer.firstElementChild;
                
                // Append to container
                membersContainer.appendChild(memberElement);
                
                // Add event listeners for the new member's elements
                const newPhotoInput = memberElement.querySelector('input[type="file"]');
                if (newPhotoInput) {
                    newPhotoInput.addEventListener('change', function() {
                        const previewId = 'member-photo-preview-' + memberCount;
                        const preview = document.getElementById(previewId);
                        if (preview && this.files && this.files[0]) {
                            const reader = new FileReader();
                            reader.onload = function(e) {
                                preview.src = e.target.result;
                                preview.style.display = 'block';
                            };
                            reader.readAsDataURL(this.files[0]);
                        }
                    });
                }
                
                // Add remove button functionality
                const removeButton = memberElement.querySelector('.remove-member');
                if (removeButton) {
                    removeButton.addEventListener('click', function() {
                        memberElement.remove();
                    });
                }
            });
            
            // Setup validation for both forms
            const individualForm = document.getElementById('individual-form');
            const teamForm = document.getElementById('team-form');
            
            individualForm.addEventListener('submit', function(e) {
                if (!validateForm(individualForm)) {
                    e.preventDefault();
                }
            });
            
            teamForm.addEventListener('submit', function(e) {
                if (!validateForm(teamForm)) {
                    e.preventDefault();
                }
            });
            
            function validateForm(form) {
                let isValid = true;
                
                // Validate required inputs
                const requiredInputs = form.querySelectorAll('[required]');
                requiredInputs.forEach(input => {
                    if (input.value.trim() === '') {
                        showError(input, 'This field is required');
                        isValid = false;
                    } else {
                        clearError(input);
                    }
                    
                    // Check phone number pattern
                    if (input.pattern && input.value.trim() !== '') {
                        const regex = new RegExp(input.pattern);
                        if (!regex.test(input.value)) {
                            showError(input, 'Please enter a valid 8-digit phone number');
                            isValid = false;
                        }
                    }
                    
                    // Check email format
                    if (input.type === 'email' && input.value.trim() !== '') {
                        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                        if (!emailRegex.test(input.value)) {
                            showError(input, 'Please enter a valid email address');
                            isValid = false;
                        }
                    }
                });
                
                return isValid;
            }
            
            function showError(input, message) {
                const errorId = input.id + '-error';
                const errorElement = document.getElementById(errorId);
                if (errorElement) {
                    errorElement.textContent = message;
                    errorElement.style.display = 'block';
                    input.classList.add('error');
                }
            }
            
            function clearError(input) {
                const errorId = input.id + '-error';
                const errorElement = document.getElementById(errorId);
                if (errorElement) {
                    errorElement.textContent = '';
                    errorElement.style.display = 'none';
                    input.classList.remove('error');
                }
            }
        });
    </script>

    <!-- Fix for team member role fields -->
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Function to handle form submission
        const form = document.getElementById('registration-form');
        
        if (form) {
            form.addEventListener('submit', function(event) {
                // Check team member roles if team registration
                if (document.getElementById('participation-team').checked) {
                    // Log key form data for debugging
                    console.log('Team name:', document.getElementById('team-name').value);
                    console.log('Leader role:', document.getElementById('team-leader-role').value);
                    
                    // Make sure all required roles are selected
                    const leaderRoleSelect = document.getElementById('team-leader-role');
                    const member2RoleSelect = document.getElementById('member2-role');
                    
                    if (!leaderRoleSelect.value) {
                        alert('Please select a role for the team leader');
                        event.preventDefault();
                        return false;
                    }
                    
                    if (!member2RoleSelect.value) {
                        alert('Please select a role for team member #2');
                        event.preventDefault();
                        return false;
                    }
                    
                    // Check if any dynamically added members are missing roles
                    const dynamicMembers = document.querySelectorAll('.team-member:not([id])');
                    for (let i = 0; i < dynamicMembers.length; i++) {
                        const roleSelect = dynamicMembers[i].querySelector('select[name^="team_members"][name$="[role]"]');
                        if (roleSelect && !roleSelect.value) {
                            alert('Please select a role for all team members');
                            event.preventDefault();
                            return false;
                        }
                    }
                }
            });
        }

        // Function to add ID attributes to dynamically created team member fields
        function updateDynamicMemberIds() {
            const teamMembers = document.querySelectorAll('.team-member:not([id])');
            let index = 4; // Starting from 4 since we already have predefined members 1-3
            
            teamMembers.forEach(member => {
                member.id = 'team-member-' + index;
                
                // Update the role select with proper ID and name if needed
                const roleSelect = member.querySelector('select[id*="member-role-"]');
                if (roleSelect) {
                    const roleSelectId = roleSelect.id;
                    const currentIndex = roleSelectId.match(/\{index\}/);
                    if (currentIndex) {
                        roleSelect.id = roleSelectId.replace(/\{index\}/, index);
                        roleSelect.name = roleSelect.name.replace(/\{index\}/, index);
                    }
                }
                
                index++;
            });
        }
        
        // Update existing dynamic members
        updateDynamicMemberIds();
        
        // Override the addTeamMember function to ensure role field is properly added
        if (window.addTeamMember) {
            const originalAddTeamMember = window.addTeamMember;
            window.addTeamMember = function() {
                originalAddTeamMember();
                updateDynamicMemberIds();
            }
        }

        // NOUVEAU CODE: Fonction pour mettre à jour la valeur de team_size
        function updateTeamSize() {
            // Compter le nombre de membres (leader + membre fixe + membres dynamiques)
            const fixedMembersCount = 2; // Leader + membre 2 obligatoire
            const optionalMember3Present = document.getElementById('member3-name').value.trim() !== '';
            const dynamicMembersCount = document.querySelectorAll('#team-members-container .team-member').length;
            
            // Calculer la taille totale de l'équipe
            let totalTeamSize = fixedMembersCount;
            if (optionalMember3Present) totalTeamSize++;
            totalTeamSize += dynamicMembersCount;
            
            // Mettre à jour la valeur du champ caché
            document.getElementById('team_size').value = totalTeamSize;
            console.log('Team size updated to: ' + totalTeamSize);
        }
        
        // Déclencher la mise à jour à chaque fois que le formulaire change
        const teamForm = document.getElementById('team-form');
        if (teamForm) {
            // Mettre à jour la taille de l'équipe au chargement
            updateTeamSize();
            
            // Ajouter les écouteurs d'événements pour les changements
            teamForm.addEventListener('input', updateTeamSize);
            
            // S'assurer que la taille est mise à jour lors de l'ajout/suppression de membres
            document.getElementById('add-team-member').addEventListener('click', function() {
                // Attendre que le DOM soit mis à jour
                setTimeout(updateTeamSize, 10);
            });
            
            // Délégation d'événements pour la suppression de membre
            document.getElementById('team-members-container').addEventListener('click', function(e) {
                if (e.target.classList.contains('remove-member')) {
                    // Attendre que le DOM soit mis à jour après la suppression
                    setTimeout(updateTeamSize, 10);
                }
            });
            
            // Observer les changements dans le membre 3 (optionnel)
            const member3Inputs = document.querySelectorAll('#member3-name, #member3-email');
            member3Inputs.forEach(input => {
                input.addEventListener('change', updateTeamSize);
            });
            
            // S'assurer que le formulaire met à jour la taille avant soumission
            teamForm.addEventListener('submit', function() {
                updateTeamSize();
            });
        }
    });
    </script>
</body>
</html>