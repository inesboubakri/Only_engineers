<?php
/**
 * User Profile Controller
 * 
 * This script handles fetching and processing user profile data.
 */

// Start session
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    // Redirect to login page if not logged in
    header('Location: ../view/signin.html');
    exit();
}

// Include database connection
require_once 'db_connection.php';

// Get user ID from session
$user_id = $_SESSION['user_id'];

// Fetch user data
$user_sql = "SELECT * FROM users WHERE id = ?";
$stmt = $conn->prepare($user_sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user_result = $stmt->get_result();

if ($user_result->num_rows === 0) {
    // User not found, redirect to login
    header('Location: ../view/signin.html');
    exit();
}

$user = $user_result->fetch_assoc();

// Check if profile is completed
if (!$user['profile_completed']) {
    // Redirect to profile setup if not completed
    header('Location: ../view/profile-setup.html');
    exit();
}

// Parse JSON data from the user table
$experiences = json_decode($user['experience'] ?? '[]', true);
$education = json_decode($user['education'] ?? '[]', true);
$skills = json_decode($user['skills'] ?? '[]', true);
$languages = json_decode($user['languages'] ?? '[]', true);
$projects = json_decode($user['projects'] ?? '[]', true);
$honors = json_decode($user['honors'] ?? '[]', true);

// Sort experiences by current status and dates
usort($experiences, function($a, $b) {
    if ($a['is_current'] && !$b['is_current']) return -1;
    if (!$a['is_current'] && $b['is_current']) return 1;
    
    $aEndDate = $a['is_current'] ? date('Y-m-d') : ($a['end_date'] ?? '1900-01-01');
    $bEndDate = $b['is_current'] ? date('Y-m-d') : ($b['end_date'] ?? '1900-01-01');
    
    if ($aEndDate != $bEndDate) {
        return strcmp($bEndDate, $aEndDate); // Descending by end date
    }
    
    return strcmp($b['start_date'] ?? '1900-01-01', $a['start_date'] ?? '1900-01-01'); // Descending by start date
});

// Sort education by current status and dates
usort($education, function($a, $b) {
    if ($a['is_current'] && !$b['is_current']) return -1;
    if (!$a['is_current'] && $b['is_current']) return 1;
    
    $aEndDate = $a['is_current'] ? date('Y-m-d') : ($a['end_date'] ?? '1900-01-01');
    $bEndDate = $b['is_current'] ? date('Y-m-d') : ($b['end_date'] ?? '1900-01-01');
    
    if ($aEndDate != $bEndDate) {
        return strcmp($bEndDate, $aEndDate); // Descending by end date
    }
    
    return strcmp($b['start_date'] ?? '1900-01-01', $a['start_date'] ?? '1900-01-01'); // Descending by start date
});

// Sort honors by date
usort($honors, function($a, $b) {
    return strcmp($b['date'] ?? '1900-01-01', $a['date'] ?? '1900-01-01'); // Descending by date
});

// Format date function
function format_date($date) {
    if (empty($date)) return '';
    $date_obj = new DateTime($date);
    return $date_obj->format('M Y');
}

// Get age from birthday
function get_age($birthday) {
    if (empty($birthday)) return '';
    $birth_date = new DateTime($birthday);
    $today = new DateTime();
    $age = $today->diff($birth_date);
    return $age->y;
}

// Default profile picture if none uploaded
$profile_picture = !empty($user['profile_picture']) ? $user['profile_picture'] : '../assets/default-profile.png';

// Include the profile view
include_once '../view/profile_view.php';
?>
