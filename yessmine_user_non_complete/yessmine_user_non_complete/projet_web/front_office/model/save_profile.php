<?php
/**
 * Profile Setup Processing Script
 * 
 * This script processes the profile setup form submission and saves the data to the database.
 */

// Start session
session_start();

// Include database connection
require_once 'db_connection.php';

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    // Redirect to login page if not logged in
    header('Location: ../view/signin.html');
    exit;
}

$user_id = $_SESSION['user_id'];

// Function to sanitize input
function sanitize_input($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Basic information
        $position = sanitize_input($_POST['position'] ?? '');
        $city = sanitize_input($_POST['city'] ?? '');
        $state = sanitize_input($_POST['state'] ?? '');
        $country = sanitize_input($_POST['country'] ?? '');
        $address = sanitize_input($_POST['address'] ?? '');
        $birthday = sanitize_input($_POST['birthday'] ?? '');
        
        // Debug: Log the address field
        error_log('Address field: ' . $address);
        
        // About section
        $about = sanitize_input($_POST['about'] ?? '');
        
        // Handle profile picture upload
        $profile_picture_path = '';
        if (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] === UPLOAD_ERR_OK) {
            $upload_dir = '../assets/uploads/profile_pictures/';
            
            // Create directory if it doesn't exist
            if (!file_exists($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }
            
            // Generate unique filename
            $file_extension = pathinfo($_FILES['profile_picture']['name'], PATHINFO_EXTENSION);
            $filename = 'profile_' . $user_id . '_' . time() . '.' . $file_extension;
            $target_file = $upload_dir . $filename;
            
            // Move uploaded file
            if (move_uploaded_file($_FILES['profile_picture']['tmp_name'], $target_file)) {
                $profile_picture_path = $filename;
            }
        }
        
        // Update user's basic information
        $stmt = $conn->prepare("UPDATE users SET 
            position = :position, 
            city = :city, 
            state = :state, 
            country = :country,
            address = :address,
            birthday = :birthday, 
            about = :about, 
            profile_picture = :profile_picture,
            profile_completed = 1
            WHERE user_id = :user_id");
        
        $stmt->bindParam(':position', $position);
        $stmt->bindParam(':city', $city);
        $stmt->bindParam(':state', $state);
        $stmt->bindParam(':country', $country);
        $stmt->bindParam(':address', $address);
        $stmt->bindParam(':birthday', $birthday);
        $stmt->bindParam(':about', $about);
        $stmt->bindParam(':profile_picture', $profile_picture_path);
        $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
        
        if (!$stmt->execute()) {
            throw new PDOException("Execute failed for basic info update");
        }
        
        // Process experience data
        if (isset($_POST['experience']) && is_array($_POST['experience'])) {
            // Debug: Log the raw experience data
            error_log('Raw experience data: ' . print_r($_POST['experience'], true));
            
            // Process all experiences
            $experiences_data = [];
            
            foreach ($_POST['experience'] as $exp) {
                // Skip incomplete entries
                if (empty($exp['title']) || empty($exp['company']) || empty($exp['start_date'])) {
                    continue;
                }
                
                $experiences_data[] = [
                    'title' => sanitize_input($exp['title'] ?? ''),
                    'company' => sanitize_input($exp['company'] ?? ''),
                    'location' => sanitize_input($exp['location'] ?? ''),
                    'start_date' => sanitize_input($exp['start_date'] ?? ''),
                    'end_date' => isset($exp['current']) ? null : sanitize_input($exp['end_date'] ?? ''),
                    'is_current' => isset($exp['current']) ? true : false,
                    'description' => sanitize_input($exp['description'] ?? '')
                ];
            }
            
            // Debug: Log the processed experience data
            error_log('Processed experience data: ' . print_r($experiences_data, true));
            
            // Store all experiences as JSON
            $experiences_json = json_encode($experiences_data);
            
            // Debug: Log the JSON string
            error_log('Experiences JSON: ' . $experiences_json);
            
            // Update only the experiences JSON field
            $stmt = $conn->prepare("UPDATE users SET experiences = :experiences WHERE user_id = :user_id");
            
            $stmt->bindParam(':experiences', $experiences_json);
            $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
            
            if (!$stmt->execute()) {
                throw new PDOException("Execute failed for experience update");
            }
        }
        
        // Process education data
        if (isset($_POST['education']) && is_array($_POST['education'])) {
            // Debug: Log the raw education data
            error_log('Raw education data: ' . print_r($_POST['education'], true));
            
            // Process all educations
            $educations_data = [];
            
            foreach ($_POST['education'] as $edu) {
                // Skip incomplete entries
                if (empty($edu['school']) || empty($edu['degree']) || empty($edu['field']) || empty($edu['start_date'])) {
                    continue;
                }
                
                $educations_data[] = [
                    'school' => sanitize_input($edu['school'] ?? ''),
                    'degree' => sanitize_input($edu['degree'] ?? ''),
                    'field' => sanitize_input($edu['field'] ?? ''),
                    'start_date' => sanitize_input($edu['start_date'] ?? ''),
                    'end_date' => isset($edu['current']) ? null : sanitize_input($edu['end_date'] ?? ''),
                    'is_current' => isset($edu['current']) ? true : false,
                    'description' => sanitize_input($edu['description'] ?? '')
                ];
            }
            
            // Debug: Log the processed education data
            error_log('Processed education data: ' . print_r($educations_data, true));
            
            // Store all educations as JSON
            $educations_json = json_encode($educations_data);
            
            // Debug: Log the JSON string
            error_log('Educations JSON: ' . $educations_json);
            
            // Update only the educations JSON field
            $stmt = $conn->prepare("UPDATE users SET educations = :educations WHERE user_id = :user_id");
            
            $stmt->bindParam(':educations', $educations_json);
            $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
            
            if (!$stmt->execute()) {
                throw new PDOException("Execute failed for education update");
            }
        }
        
        // Process skills, languages, projects, and honors as JSON
        $skills_data = [];
        if (isset($_POST['skills']) && is_array($_POST['skills'])) {
            foreach ($_POST['skills'] as $skill) {
                $skills_data[] = [
                    'name' => sanitize_input($skill['name'] ?? ''),
                    'level' => sanitize_input($skill['level'] ?? '')
                ];
            }
        }
        
        $languages_data = [];
        if (isset($_POST['languages']) && is_array($_POST['languages'])) {
            foreach ($_POST['languages'] as $lang) {
                $languages_data[] = [
                    'name' => sanitize_input($lang['name'] ?? ''),
                    'level' => sanitize_input($lang['level'] ?? '')
                ];
            }
        }
        
        $projects_data = [];
        if (isset($_POST['projects']) && is_array($_POST['projects'])) {
            foreach ($_POST['projects'] as $project) {
                $projects_data[] = [
                    'title' => sanitize_input($project['title'] ?? ''),
                    'url' => sanitize_input($project['url'] ?? ''),
                    'description' => sanitize_input($project['description'] ?? '')
                ];
            }
        }
        
        $honors_data = [];
        if (isset($_POST['honors']) && is_array($_POST['honors'])) {
            foreach ($_POST['honors'] as $honor) {
                $honors_data[] = [
                    'title' => sanitize_input($honor['title'] ?? ''),
                    'issuer' => sanitize_input($honor['issuer'] ?? ''),
                    'date' => sanitize_input($honor['date'] ?? ''),
                    'description' => sanitize_input($honor['description'] ?? '')
                ];
            }
        }
        
        // Convert arrays to JSON for storage
        $skills_json = json_encode($skills_data);
        $languages_json = json_encode($languages_data);
        $projects_json = json_encode($projects_data);
        $honors_json = json_encode($honors_data);
        
        // Update JSON fields
        $stmt = $conn->prepare("UPDATE users SET 
            skills = :skills, 
            languages = :languages, 
            projects = :projects, 
            honors = :honors
            WHERE user_id = :user_id");
        
        $stmt->bindParam(':skills', $skills_json);
        $stmt->bindParam(':languages', $languages_json);
        $stmt->bindParam(':projects', $projects_json);
        $stmt->bindParam(':honors', $honors_json);
        $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
        
        if (!$stmt->execute()) {
            throw new PDOException("Execute failed for JSON fields update");
        }
        
        // Set profile completed flag in session
        $_SESSION['profile_completed'] = true;
        
        // Redirect to profile page
        header('Location: ../view/profile.php');
        exit;
        
    } catch (Exception $e) {
        // Log error
        error_log('Profile setup error: ' . $e->getMessage());
        
        // Display detailed error message for debugging
        echo '<script>
            alert("Error: ' . addslashes($e->getMessage()) . '");
            console.log("' . addslashes($e->getMessage()) . '");
            window.location.href = "../view/profile-setup.html";
        </script>';
        exit;
    }
} else {
    // Redirect to profile setup page if not a POST request
    header('Location: ../view/profile-setup.html');
    exit;
}
