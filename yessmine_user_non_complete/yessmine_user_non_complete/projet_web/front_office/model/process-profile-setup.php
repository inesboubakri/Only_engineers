<?php
/**
 * Process Profile Setup Form
 * 
 * This script handles the profile setup form submission, validates the data,
 * and stores it in the database.
 */

// Start session
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    // Redirect to login page if not logged in
    header('Location: ../view/login.html');
    exit();
}

// Include database connection
require_once 'db_connection.php';

// Function to sanitize input data
function sanitize_input($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

// Function to validate date format
function validate_date($date) {
    $d = DateTime::createFromFormat('Y-m-d', $date);
    return $d && $d->format('Y-m-d') === $date;
}

// Initialize response array
$response = [
    'success' => false,
    'message' => '',
    'errors' => []
];

// Check if form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get user ID from session
    $user_id = $_SESSION['user_id'];
    
    // Basic Information
    $position = sanitize_input($_POST['position'] ?? '');
    $city = sanitize_input($_POST['city'] ?? '');
    $state = sanitize_input($_POST['state'] ?? '');
    $country = sanitize_input($_POST['country'] ?? '');
    $address = sanitize_input($_POST['address'] ?? '');
    $birthday = sanitize_input($_POST['birthday'] ?? '');
    
    // Debug: Log the address field
    error_log('process-profile-setup.php - Address field: ' . $address);
    
    // About
    $about = sanitize_input($_POST['about'] ?? '');
    
    // Validate basic information
    if (empty($position)) {
        $response['errors'][] = 'Position is required';
    }
    
    if (empty($city)) {
        $response['errors'][] = 'City is required';
    }
    
    if (empty($state)) {
        $response['errors'][] = 'State/Province is required';
    }
    
    if (empty($country)) {
        $response['errors'][] = 'Country is required';
    }
    
    if (empty($birthday)) {
        $response['errors'][] = 'Birthday is required';
    } elseif (!validate_date($birthday)) {
        $response['errors'][] = 'Invalid birthday format';
    } else {
        // Check if user is at least 18 years old
        $birthDate = new DateTime($birthday);
        $today = new DateTime();
        $age = $today->diff($birthDate)->y;
        
        if ($age < 18) {
            $response['errors'][] = 'You must be at least 18 years old';
        }
    }
    
    // Validate about section
    if (empty($about)) {
        $response['errors'][] = 'About section is required';
    } else {
        // Count words
        $word_count = str_word_count($about);
        
        if ($word_count < 10) {
            $response['errors'][] = 'About section must be at least 10 words';
        } elseif ($word_count > 255) {
            $response['errors'][] = 'About section must be at most 255 words';
        }
    }
    
    // Process profile picture if uploaded
    $profile_picture_path = null;
    if (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] === UPLOAD_ERR_OK) {
        $file_tmp = $_FILES['profile_picture']['tmp_name'];
        $file_name = $_FILES['profile_picture']['name'];
        $file_size = $_FILES['profile_picture']['size'];
        $file_type = $_FILES['profile_picture']['type'];
        
        // Validate file type
        $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
        if (!in_array($file_type, $allowed_types)) {
            $response['errors'][] = 'Only JPEG, PNG, and GIF images are allowed';
        }
        
        // Validate file size (max 5MB)
        if ($file_size > 5 * 1024 * 1024) {
            $response['errors'][] = 'Image size should be less than 5MB';
        }
        
        // Generate unique filename
        $file_extension = pathinfo($file_name, PATHINFO_EXTENSION);
        $new_file_name = uniqid('profile_') . '.' . $file_extension;
        
        // Set upload directory
        $upload_dir = '../uploads/profile_pictures/';
        
        // Create directory if it doesn't exist
        if (!file_exists($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }
        
        $profile_picture_path = $upload_dir . $new_file_name;
        
        // Move uploaded file to destination
        if (empty($response['errors'])) {
            if (!move_uploaded_file($file_tmp, $profile_picture_path)) {
                $response['errors'][] = 'Failed to upload profile picture';
                $profile_picture_path = null;
            }
        }
    }
    
    // If no errors, proceed with database operations
    if (empty($response['errors'])) {
        try {
            // Start transaction
            $conn->begin_transaction();
            
            // Process experience data
            $experience_data = [];
            if (isset($_POST['experience']) && is_array($_POST['experience'])) {
                // Debug: Log the raw experience data
                error_log('process-profile-setup.php - Raw experience data: ' . print_r($_POST['experience'], true));
                
                foreach ($_POST['experience'] as $exp) {
                    if (empty($exp['title']) || empty($exp['company']) || empty($exp['start_date'])) {
                        continue; // Skip incomplete entries
                    }
                    
                    $experience_data[] = [
                        'title' => sanitize_input($exp['title']),
                        'company' => sanitize_input($exp['company']),
                        'location' => sanitize_input($exp['location'] ?? ''),
                        'start_date' => sanitize_input($exp['start_date']),
                        'end_date' => isset($exp['current']) ? null : sanitize_input($exp['end_date'] ?? ''),
                        'description' => sanitize_input($exp['description'] ?? ''),
                        'is_current' => isset($exp['current']) ? true : false
                    ];
                }
                
                // Debug: Log the processed experience data
                error_log('process-profile-setup.php - Processed experience data: ' . print_r($experience_data, true));
            }
            $experience_json = json_encode($experience_data);
            error_log('process-profile-setup.php - Experience JSON: ' . $experience_json);
            
            // Process education data
            $education_data = [];
            if (isset($_POST['education']) && is_array($_POST['education'])) {
                // Debug: Log the raw education data
                error_log('process-profile-setup.php - Raw education data: ' . print_r($_POST['education'], true));
                
                foreach ($_POST['education'] as $edu) {
                    if (empty($edu['school']) || empty($edu['degree']) || empty($edu['field']) || empty($edu['start_date'])) {
                        continue; // Skip incomplete entries
                    }
                    
                    $education_data[] = [
                        'school' => sanitize_input($edu['school']),
                        'degree' => sanitize_input($edu['degree']),
                        'field' => sanitize_input($edu['field']),
                        'start_date' => sanitize_input($edu['start_date']),
                        'end_date' => isset($edu['current']) ? null : sanitize_input($edu['end_date'] ?? ''),
                        'description' => sanitize_input($edu['description'] ?? ''),
                        'is_current' => isset($edu['current']) ? true : false
                    ];
                }
                
                // Debug: Log the processed education data
                error_log('process-profile-setup.php - Processed education data: ' . print_r($education_data, true));
            }
            $education_json = json_encode($education_data);
            error_log('process-profile-setup.php - Education JSON: ' . $education_json);
            
            // Process skills data
            $skills_data = [];
            if (isset($_POST['skills']) && is_array($_POST['skills'])) {
                foreach ($_POST['skills'] as $skill) {
                    if (empty($skill['name']) || empty($skill['level'])) {
                        continue; // Skip incomplete entries
                    }
                    
                    $skills_data[] = [
                        'name' => sanitize_input($skill['name']),
                        'level' => sanitize_input($skill['level'])
                    ];
                }
            }
            $skills_json = json_encode($skills_data);
            
            // Process languages data
            $languages_data = [];
            if (isset($_POST['languages']) && is_array($_POST['languages'])) {
                foreach ($_POST['languages'] as $lang) {
                    if (empty($lang['name']) || empty($lang['level'])) {
                        continue; // Skip incomplete entries
                    }
                    
                    $languages_data[] = [
                        'name' => sanitize_input($lang['name']),
                        'level' => sanitize_input($lang['level'])
                    ];
                }
            }
            $languages_json = json_encode($languages_data);
            
            // Process projects data
            $projects_data = [];
            if (isset($_POST['projects']) && is_array($_POST['projects'])) {
                foreach ($_POST['projects'] as $proj) {
                    if (empty($proj['name']) || empty($proj['description'])) {
                        continue; // Skip incomplete entries
                    }
                    
                    $projects_data[] = [
                        'name' => sanitize_input($proj['name']),
                        'url' => sanitize_input($proj['url'] ?? ''),
                        'description' => sanitize_input($proj['description'])
                    ];
                }
            }
            $projects_json = json_encode($projects_data);
            
            // Process honors data
            $honors_data = [];
            if (isset($_POST['honors']) && is_array($_POST['honors'])) {
                foreach ($_POST['honors'] as $honor) {
                    if (empty($honor['title']) || empty($honor['issuer']) || empty($honor['date'])) {
                        continue; // Skip incomplete entries
                    }
                    
                    $honors_data[] = [
                        'title' => sanitize_input($honor['title']),
                        'issuer' => sanitize_input($honor['issuer']),
                        'date' => sanitize_input($honor['date']),
                        'description' => sanitize_input($honor['description'] ?? '')
                    ];
                }
            }
            $honors_json = json_encode($honors_data);
            
            // Update user profile with all data
            $update_user_sql = "UPDATE users SET 
                position = ?, 
                city = ?, 
                state = ?, 
                country = ?, 
                address = ?,
                birthday = ?, 
                about = ?, 
                profile_picture = ?,
                skills = ?,
                languages = ?,
                projects = ?,
                honors = ?,
                experiences = ?,
                educations = ?,
                profile_completed = 1
                WHERE user_id = ?";
            
            $stmt = $conn->prepare($update_user_sql);
            $stmt->bind_param("sssssssssssssssi", 
                $position, 
                $city, 
                $state, 
                $country, 
                $address,
                $birthday, 
                $about, 
                $profile_picture_path,
                $skills_json,
                $languages_json,
                $projects_json,
                $honors_json,
                $experience_json,
                $education_json,
                $user_id
            );
            
            $stmt->execute();
            
            // Commit transaction
            $conn->commit();
            
            // Set success response
            $response['success'] = true;
            $response['message'] = 'Profile setup completed successfully';
            
            // Redirect to profile page
            header('Location: ../view/profile.php');
            exit();
            
        } catch (Exception $e) {
            // Rollback transaction on error
            $conn->rollback();
            
            // Set error response
            $response['success'] = false;
            $response['message'] = 'An error occurred: ' . $e->getMessage();
            $response['errors'][] = $e->getMessage();
        }
    } else {
        $response['message'] = 'Please fix the errors and try again';
    }
    
    // If there are errors, return to the form with error messages
    if (!$response['success']) {
        $_SESSION['profile_setup_errors'] = $response['errors'];
        $_SESSION['profile_setup_data'] = $_POST; // Store form data for repopulation
        
        header('Location: ../view/profile-setup.html');
        exit();
    }
} else {
    // If not POST request, redirect to the form
    header('Location: ../view/profile-setup.html');
    exit();
}
