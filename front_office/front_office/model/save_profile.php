<?php
// Display all errors for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();

// Check if signup data exists in session
if (!isset($_SESSION['signup_data'])) {
    // Return JSON error response
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Please complete the signup process first']);
    exit();
}

// Get signup data from session
$signupData = $_SESSION['signup_data'];
$fullName = $signupData['full_name'];
$email = $signupData['email'];
$password = $signupData['password']; // Already hashed in register_user.php - DO NOT modify this value

// Debug the entire $_FILES array and $_POST array to see what's being received
error_log("=== FORM SUBMISSION DEBUG ===");
error_log("FILES array: " . print_r($_FILES, true));
error_log("POST array excerpt: " . substr(print_r($_POST, true), 0, 1000));

// Process profile picture if uploaded
$profilePicture = '';
if (isset($_FILES['profilePic']) && $_FILES['profilePic']['error'] === UPLOAD_ERR_OK) {
    error_log("Processing profile picture upload");
    
    // Define constants for image handling
    define('MAX_FILE_SIZE', 5 * 1024 * 1024); // 5MB max file size
    define('PROFILE_PIC_DIR', 'ressources/profile_pictures/');
    
    // Detailed error checking for file uploads
    $uploadError = '';
    switch ($_FILES['profilePic']['error']) {
        case UPLOAD_ERR_INI_SIZE:
            $uploadError = 'File exceeds upload_max_filesize in php.ini';
            break;
        case UPLOAD_ERR_FORM_SIZE:
            $uploadError = 'File exceeds MAX_FILE_SIZE in the HTML form';
            break;
        case UPLOAD_ERR_PARTIAL:
            $uploadError = 'File was only partially uploaded';
            break;
        case UPLOAD_ERR_NO_FILE:
            $uploadError = 'No file was uploaded';
            break;
        case UPLOAD_ERR_NO_TMP_DIR:
            $uploadError = 'Missing temporary folder';
            break;
        case UPLOAD_ERR_CANT_WRITE:
            $uploadError = 'Failed to write file to disk';
            break;
        case UPLOAD_ERR_EXTENSION:
            $uploadError = 'A PHP extension stopped the file upload';
            break;
    }
    
    if (!empty($uploadError)) {
        error_log("Profile picture upload error: " . $uploadError);
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Upload error: ' . $uploadError]);
        exit();
    }
    
    // Validate file size manually
    if ($_FILES['profilePic']['size'] > MAX_FILE_SIZE) {
        error_log("File too large: " . $_FILES['profilePic']['size'] . " bytes");
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'File too large. Maximum size is 5MB.']);
        exit();
    }
    
    // Validate file type
    $fileInfo = finfo_open(FILEINFO_MIME_TYPE);
    $mimeType = finfo_file($fileInfo, $_FILES['profilePic']['tmp_name']);
    finfo_close($fileInfo);
    
    $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
    if (!in_array($mimeType, $allowedTypes)) {
        error_log("Invalid file type: " . $mimeType);
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Invalid file type. Only JPG, PNG, GIF and WEBP are allowed.']);
        exit();
    }
    
    // Define upload directory (using absolute path for file operations)
    $uploadDir = dirname(dirname(__FILE__)) . '/ressources/profile_pictures/';
    error_log("Upload directory: " . $uploadDir);
    
    // Check if directory exists and create it if it doesn't
    if (!file_exists($uploadDir)) {
        error_log("Creating directory: " . $uploadDir);
        if (!mkdir($uploadDir, 0755, true)) {
            error_log("Failed to create directory");
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Failed to create upload directory']);
            exit();
        }
    }
    
    // Check if directory is writable
    if (!is_writable($uploadDir)) {
        error_log("Directory is not writable: " . $uploadDir);
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Upload directory is not writable']);
        exit();
    }
    
    // Generate unique filename - store ONLY the filename (not the path)
    $fileExtension = strtolower(pathinfo($_FILES['profilePic']['name'], PATHINFO_EXTENSION));
    $fileName = 'profile_' . time() . '_' . rand(1000, 9999) . '.' . $fileExtension;
    $targetFile = $uploadDir . $fileName;
    
    error_log("Moving file to: " . $targetFile);
    
    // Move uploaded file
    if (move_uploaded_file($_FILES['profilePic']['tmp_name'], $targetFile)) {
        // Store only the filename in the database
        $profilePicture = $fileName;
        error_log("Profile picture saved as: " . $profilePicture);
        
        // Verify the file was successfully created
        if (!file_exists($targetFile)) {
            error_log("File not found after move: " . $targetFile);
        } else {
            error_log("File exists after move: " . $targetFile . " Size: " . filesize($targetFile));
        }
    } else {
        error_log("Failed to move uploaded file. Error: " . $_FILES['profilePic']['error']);
        error_log("Target directory is writable: " . (is_writable($uploadDir) ? 'Yes' : 'No'));
        error_log("Temp file exists: " . (file_exists($_FILES['profilePic']['tmp_name']) ? 'Yes' : 'No'));
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Failed to move uploaded file']);
        exit();
    }
}

// Process form data
$position = $_POST['position'] ?? '';
$country = $_POST['country'] ?? '';
$city = $_POST['city'] ?? '';
$birthday = $_POST['birthday'] ?? '';
$about = $_POST['about'] ?? '';

// Process seeking options (convert to string)
$seeking = '';
if (isset($_POST['seeking_values']) && !empty($_POST['seeking_values'])) {
    $seeking = $_POST['seeking_values'];
} elseif (isset($_POST['seeking']) && is_array($_POST['seeking'])) {
    $seeking = implode(', ', $_POST['seeking']);
} elseif (isset($_POST['seeking']) && is_string($_POST['seeking'])) {
    $seeking = $_POST['seeking'];
}

// Initialize JSON fields with empty arrays
$experiences = '[]';
$educations = '[]';
$organizations = '[]';
$honors = '[]';
$courses = '[]';
$projects = '[]';
$languages = '[]';
$skills = '[]';

// Process experiences (convert to JSON)
if (isset($_POST['jobTitle']) && is_array($_POST['jobTitle'])) {
    $experiencesArray = [];
    foreach ($_POST['jobTitle'] as $i => $title) {
        if (!empty($title)) {
            $experiencesArray[] = [
                'title' => $title,
                'company' => $_POST['company'][$i] ?? '',
                'start_date' => $_POST['expStartDate'][$i] ?? '',
                'end_date' => $_POST['expEndDate'][$i] ?? '',
                'current' => isset($_POST['currentJob'][$i]) ? 1 : 0,
                'description' => $_POST['expDescription'][$i] ?? ''
            ];
        }
    }
    $experiences = json_encode($experiencesArray);
}

// Process education (convert to JSON)
if (isset($_POST['school']) && is_array($_POST['school'])) {
    $educationsArray = [];
    foreach ($_POST['school'] as $i => $school) {
        if (!empty($school)) {
            $educationsArray[] = [
                'school' => $school,
                'degree' => $_POST['degree'][$i] ?? '',
                'field' => $_POST['fieldOfStudy'][$i] ?? '',
                'start_date' => $_POST['eduStartDate'][$i] ?? '',
                'end_date' => $_POST['eduEndDate'][$i] ?? '',
                'current' => isset($_POST['currentEducation'][$i]) ? 1 : 0,
                'description' => $_POST['eduDescription'][$i] ?? ''
            ];
        }
    }
    $educations = json_encode($educationsArray);
}

// Process organizations (convert to JSON)
if (isset($_POST['orgName']) && is_array($_POST['orgName'])) {
    $organizationsArray = [];
    foreach ($_POST['orgName'] as $i => $name) {
        if (!empty($name)) {
            $organizationsArray[] = [
                'name' => $name,
                'position' => $_POST['orgPosition'][$i] ?? '',
                'start_date' => $_POST['orgStartDate'][$i] ?? '',
                'end_date' => $_POST['orgEndDate'][$i] ?? '',
                'current' => isset($_POST['currentOrg'][$i]) ? 1 : 0,
                'description' => $_POST['orgDescription'][$i] ?? ''
            ];
        }
    }
    $organizations = json_encode($organizationsArray);
}

// Process honors (convert to JSON)
if (isset($_POST['honorName']) && is_array($_POST['honorName'])) {
    $honorsArray = [];
    foreach ($_POST['honorName'] as $i => $name) {
        if (!empty($name)) {
            $honorsArray[] = [
                'name' => $name,
                'issuer' => $_POST['honorIssuer'][$i] ?? '',
                'date' => $_POST['honorDate'][$i] ?? '',
                'description' => $_POST['honorDescription'][$i] ?? ''
            ];
        }
    }
    $honors = json_encode($honorsArray);
}

// Process courses (convert to JSON)
if (isset($_POST['courseTitle']) && is_array($_POST['courseTitle'])) {
    $coursesArray = [];
    foreach ($_POST['courseTitle'] as $i => $title) {
        if (!empty($title)) {
            $coursesArray[] = [
                'title' => $title,
                'provider' => $_POST['courseProvider'][$i] ?? '',
                'start_date' => $_POST['courseStartDate'][$i] ?? '',
                'end_date' => $_POST['courseEndDate'][$i] ?? '',
                'current' => isset($_POST['currentCourse'][$i]) ? 1 : 0,
                'description' => $_POST['courseDescription'][$i] ?? ''
            ];
        }
    }
    $courses = json_encode($coursesArray);
}

// Process projects (convert to JSON)
if (isset($_POST['projectTitle']) && is_array($_POST['projectTitle'])) {
    $projectsArray = [];
    foreach ($_POST['projectTitle'] as $i => $title) {
        if (!empty($title)) {
            $projectsArray[] = [
                'title' => $title,
                'provider' => $_POST['projectProvider'][$i] ?? '',
                'start_date' => $_POST['projectStartDate'][$i] ?? '',
                'end_date' => $_POST['projectEndDate'][$i] ?? '',
                'current' => isset($_POST['currentProject'][$i]) ? 1 : 0,
                'description' => $_POST['projectDescription'][$i] ?? ''
            ];
        }
    }
    $projects = json_encode($projectsArray);
}

// Process languages (convert to JSON)
if (isset($_POST['language']) && is_array($_POST['language'])) {
    $languagesArray = [];
    foreach ($_POST['language'] as $i => $language) {
        if (!empty($language)) {
            $languagesArray[] = [
                'name' => $language,
                'proficiency' => $_POST['languageLevel'][$i] ?? ''
            ];
        }
    }
    $languages = json_encode($languagesArray);
}

// Process skills (convert to JSON)
if (isset($_POST['skill']) && is_array($_POST['skill'])) {
    $skillsArray = [];
    foreach ($_POST['skill'] as $i => $skill) {
        if (!empty($skill)) {
            $skillsArray[] = [
                'name' => $skill,
                'level' => $_POST['skillLevel'][$i] ?? ''
            ];
        }
    }
    $skills = json_encode($skillsArray);
}

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
    
    // Prepare SQL statement
    $sql = "INSERT INTO users (
                full_name, 
                email, 
                password, 
                profile_picture, 
                position, 
                country, 
                city, 
                birthday, 
                about, 
                seeking, 
                experiences, 
                educations, 
                organizations, 
                honors, 
                courses, 
                projects, 
                languages, 
                skills,
                is_admin,
                is_banned
            ) VALUES (
                :fullName, :email, :password, :profilePicture, :position, :country, :city, :birthday,
                :about, :seeking, :experiences, :educations, :organizations, :honors, :courses,
                :projects, :languages, :skills, 0, 0
            )";
    
    $stmt = $conn->prepare($sql);
    
    // Bind parameters
    $stmt->bindParam(':fullName', $fullName);
    $stmt->bindParam(':email', $email);
    
    // Make sure the password is exactly as stored in session (already hashed)
    $hashedPassword = $signupData['password'];
    $stmt->bindParam(':password', $hashedPassword);
    
    $stmt->bindParam(':profilePicture', $profilePicture);
    $stmt->bindParam(':position', $position);
    $stmt->bindParam(':country', $country);
    $stmt->bindParam(':city', $city);
    $stmt->bindParam(':birthday', $birthday);
    $stmt->bindParam(':about', $about);
    $stmt->bindParam(':seeking', $seeking);
    $stmt->bindParam(':experiences', $experiences);
    $stmt->bindParam(':educations', $educations);
    $stmt->bindParam(':organizations', $organizations);
    $stmt->bindParam(':honors', $honors);
    $stmt->bindParam(':courses', $courses);
    $stmt->bindParam(':projects', $projects);
    $stmt->bindParam(':languages', $languages);
    $stmt->bindParam(':skills', $skills);
    
    // Execute the query
    $stmt->execute();
    
    // Get the last inserted ID
    $userId = $conn->lastInsertId();
    
    // Clear signup data from session
    unset($_SESSION['signup_data']);
    
    // Set user as logged in
    $_SESSION['user_id'] = $userId;
    $_SESSION['user_name'] = $fullName;
    $_SESSION['user_email'] = $email;
    
    // Return success JSON response
    header('Content-Type: application/json');
    echo json_encode([
        'success' => true, 
        'message' => 'Profile created successfully',
        'redirect' => '../view/user-profile.php'
    ]);
    exit();
} catch(PDOException $e) {
    // Log the error for debugging
    error_log("Database error in save_profile.php: " . $e->getMessage());
    
    // Return error JSON response
    header('Content-Type: application/json');
    echo json_encode([
        'success' => false, 
        'message' => 'An error occurred while saving your profile: ' . $e->getMessage()
    ]);
    exit();
}
?>
