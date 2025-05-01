<?php
session_start();
require_once '../model/update_participants_table.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: ../view/signin.php?error=login_required");
    exit();
}

// Check if form was submitted
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: ../view/hackathons.php");
    exit();
}

// Get basic form data
$hackathonId = isset($_POST['hackathon_id']) ? (int)$_POST['hackathon_id'] : 0;
$participationType = isset($_POST['participation_type']) ? $_POST['participation_type'] : '';
$userId = $_SESSION['user_id'];

// Validate hackathon ID
if ($hackathonId <= 0) {
    header("Location: ../view/hackathons.php?error=invalid_hackathon");
    exit();
}

// Process based on participation type
if ($participationType === 'individual') {
    // Handle individual registration
    
    // Get form data
    $fullName = isset($_POST['full_name']) ? trim($_POST['full_name']) : '';
    $email = isset($_POST['email']) ? trim($_POST['email']) : '';
    $phone = isset($_POST['phone']) ? trim($_POST['phone']) : '';
    
    // Validate required fields
    if (empty($fullName) || empty($email) || empty($phone)) {
        header("Location: ../view/register-hackathon.php?hackathon_id=$hackathonId&error=missing_fields");
        exit();
    }
    
    // Handle photo upload
    $photoResult = null;
    if (isset($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
        // Include user_id in the identifier for better photo management
        $photoResult = uploadParticipantPhoto($_FILES['photo'], 'individual', $userId);
        
        if (!$photoResult['success']) {
            header("Location: ../view/register-hackathon.php?hackathon_id=$hackathonId&error=photo_upload&message=" . urlencode($photoResult['message']));
            exit();
        }
    } else {
        // No photo or upload error
        if (isset($_FILES['photo']) && $_FILES['photo']['error'] !== UPLOAD_ERR_NO_FILE) {
            $errorMessage = "Photo upload error: " . $_FILES['photo']['error'];
            header("Location: ../view/register-hackathon.php?hackathon_id=$hackathonId&error=photo_upload&message=" . urlencode($errorMessage));
            exit();
        }
    }
    
    // Register individual participant
    $registrationResult = registerIndividualParticipant($userId, $hackathonId, $fullName, $email, $phone, $photoResult['path']);
    
    if ($registrationResult['success']) {
        header("Location: ../view/hackathon-details.php?id=$hackathonId&success=registered");
        exit();
    } else {
        // If registration fails, delete uploaded photo
        if (file_exists('../../ressources/' . $photoResult['path'])) {
            unlink('../../ressources/' . $photoResult['path']);
        }
        
        header("Location: ../view/register-hackathon.php?hackathon_id=$hackathonId&error=registration_failed&message=" . urlencode($registrationResult['message']));
        exit();
    }
} 
elseif ($participationType === 'team') {
    // Handle team registration
    
    // Get team leader data
    $teamLeaderName = isset($_POST['team_leader_name']) ? trim($_POST['team_leader_name']) : '';
    $teamLeaderEmail = isset($_POST['team_leader_email']) ? trim($_POST['team_leader_email']) : '';
    $teamLeaderPhone = isset($_POST['team_leader_phone']) ? trim($_POST['team_leader_phone']) : '';
    
    // Validate team leader data
    if (empty($teamLeaderName) || empty($teamLeaderEmail) || empty($teamLeaderPhone)) {
        header("Location: ../view/register-hackathon.php?hackathon_id=$hackathonId&error=missing_leader_fields");
        exit();
    }
    
    // Handle team leader photo upload
    $leaderPhotoResult = null;
    if (isset($_FILES['team_leader_photo']) && $_FILES['team_leader_photo']['error'] === UPLOAD_ERR_OK) {
        // Use user ID and "leader" as identifier for team leader photos
        $leaderPhotoResult = uploadParticipantPhoto($_FILES['team_leader_photo'], 'team', $userId.'_leader');
        
        if (!$leaderPhotoResult['success']) {
            header("Location: ../view/register-hackathon.php?hackathon_id=$hackathonId&error=leader_photo_upload&message=" . urlencode($leaderPhotoResult['message']));
            exit();
        }
    } else {
        header("Location: ../view/register-hackathon.php?hackathon_id=$hackathonId&error=leader_photo_required");
        exit();
    }
    
    // Process team members
    $teamMembers = [];
    $memberCount = isset($_POST['member_count']) ? (int)$_POST['member_count'] : 0;
    
    // Check if there are team members
    if ($memberCount <= 0) {
        header("Location: ../view/register-hackathon.php?hackathon_id=$hackathonId&error=no_team_members");
        exit();
    }
    
    // Create directories for team photos if they don't exist
    $teamPhotosDir = '../../ressources/temp_uploads/team_photos/';
    if (!file_exists($teamPhotosDir)) {
        mkdir($teamPhotosDir, 0777, true);
    }
    
    // Process each team member
    $uploadedPhotos = [];
    
    for ($i = 1; $i <= $memberCount; $i++) {
        $memberName = isset($_POST["member_{$i}_name"]) ? trim($_POST["member_{$i}_name"]) : '';
        $memberEmail = isset($_POST["member_{$i}_email"]) ? trim($_POST["member_{$i}_email"]) : '';
        $memberPhone = isset($_POST["member_{$i}_phone"]) ? trim($_POST["member_{$i}_phone"]) : '';
        
        // Validate member data
        if (empty($memberName) || empty($memberEmail) || empty($memberPhone)) {
            // Clean up any uploaded photos
            foreach ($uploadedPhotos as $photoPath) {
                if (file_exists('../../ressources/' . $photoPath)) {
                    unlink('../../ressources/' . $photoPath);
                }
            }
            
            header("Location: ../view/register-hackathon.php?hackathon_id=$hackathonId&error=missing_member_fields&member=$i");
            exit();
        }
        
        // Handle member photo upload
        $memberPhotoResult = null;
        if (isset($_FILES["member_{$i}_photo"]) && $_FILES["member_{$i}_photo"]['error'] === UPLOAD_ERR_OK) {
            // Add member number to the identifier for better organization
            $memberPhotoResult = uploadParticipantPhoto($_FILES["member_{$i}_photo"], 'team', $userId.'_member'.$i);
            
            if (!$memberPhotoResult['success']) {
                // Clean up any uploaded photos
                foreach ($uploadedPhotos as $photoPath) {
                    if (file_exists('../../ressources/' . $photoPath)) {
                        unlink('../../ressources/' . $photoPath);
                    }
                }
                
                header("Location: ../view/register-hackathon.php?hackathon_id=$hackathonId&error=member_photo_upload&member=$i&message=" . urlencode($memberPhotoResult['message']));
                exit();
            }
            
            $uploadedPhotos[] = $memberPhotoResult['path'];
        } else {
            // Clean up any uploaded photos
            foreach ($uploadedPhotos as $photoPath) {
                if (file_exists('../../ressources/' . $photoPath)) {
                    unlink('../../ressources/' . $photoPath);
                }
            }
            
            header("Location: ../view/register-hackathon.php?hackathon_id=$hackathonId&error=member_photo_required&member=$i");
            exit();
        }
        
        // Add member to team
        $teamMembers[] = [
            'name' => $memberName,
            'email' => $memberEmail,
            'phone' => $memberPhone,
            'photo' => $memberPhotoResult['path']
        ];
    }
    
    // Register team
    $registrationResult = registerTeam($userId, $hackathonId, $teamLeaderName, $teamLeaderEmail, $teamLeaderPhone, $teamMembers);
    
    if ($registrationResult['success']) {
        header("Location: ../view/hackathon-details.php?id=$hackathonId&success=team_registered");
        exit();
    } else {
        // Clean up uploaded photos
        if (file_exists('../../ressources/' . $leaderPhotoResult['path'])) {
            unlink('../../ressources/' . $leaderPhotoResult['path']);
        }
        
        foreach ($uploadedPhotos as $photoPath) {
            if (file_exists('../../ressources/' . $photoPath)) {
                unlink('../../ressources/' . $photoPath);
            }
        }
        
        header("Location: ../view/register-hackathon.php?hackathon_id=$hackathonId&error=registration_failed&message=" . urlencode($registrationResult['message']));
        exit();
    }
} else {
    header("Location: ../view/hackathons.php?error=invalid_participation_type");
    exit();
}
?>