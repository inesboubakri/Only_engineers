<?php
/**
 * User Profile Page
 * 
 * This page displays the user's profile after they have completed the profile setup process.
 */

// Start session
session_start();

// Include database connection
require_once '../model/db_connection.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    // Redirect to login page if not logged in
    header('Location: signin.html');
    exit;
}

$user_id = $_SESSION['user_id'];

// Get user data from database
$stmt = $conn->prepare("SELECT * FROM users WHERE user_id = :user_id");
$stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
$stmt->execute();

$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    // User not found
    header('Location: signin.html');
    exit;
}

// Decode JSON data for arrays
$skills = json_decode($user['skills'] ?? '[]', true);
$languages = json_decode($user['languages'] ?? '[]', true);
$projects = json_decode($user['projects'] ?? '[]', true);
$honors = json_decode($user['honors'] ?? '[]', true);

// Debug: Log the raw experiences and educations JSON
error_log('Raw experiences JSON: ' . ($user['experiences'] ?? 'NULL'));
error_log('Raw educations JSON: ' . ($user['educations'] ?? 'NULL'));

$experiences = json_decode($user['experiences'] ?? '[]', true);
$educations = json_decode($user['educations'] ?? '[]', true);

// Debug: Log the decoded experiences and educations arrays
error_log('Decoded experiences: ' . print_r($experiences, true));
error_log('Decoded educations: ' . print_r($educations, true));
error_log('Number of experiences: ' . count($experiences));
error_log('Number of educations: ' . count($educations));

// Function to format date
function formatDate($date) {
    if (empty($date)) return '';
    return date('M Y', strtotime($date));
}

// Calculate age from birthday
function calculateAge($birthday) {
    if (empty($birthday)) return '';
    $birthDate = new DateTime($birthday);
    $today = new DateTime('today');
    $age = $birthDate->diff($today)->y;
    return $age;
}

// Get profile picture URL
$profile_picture = '../assets/uploads/profile_pictures/' . ($user['profile_picture'] ?? 'default-profile.png');
if (empty($user['profile_picture']) || !file_exists($profile_picture)) {
    $profile_picture = '../assets/img/default-profile.png';
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($user['full_name']); ?> - OnlyEngineers</title>
    <link rel="stylesheet" href="../view/styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        /* CSS Variables */
        :root {
            --primary-color: #3F86ED;
            --primary-hover: #3D55CD;
            --primary-light: #e6f0ff;
            --gradient-primary: linear-gradient(135deg, #3F86ED, #24CCAA);
            --gradient-secondary: linear-gradient(135deg, #10B981, #34D399);
            --card-bg: #ffffff;
            --card-header-bg: #ffffff;
            --text-color: #333333;
            --text-muted: #6c757d;
            --heading-color: #222222;
            --link-color: #4F6EF7;
            --border-color: #e5e7eb;
            --hover-bg: #f8f9fa;
            --skill-bg: #f0f4ff;
            --progress-bg: #e9ecef;
            --shadow-sm: 0 2px 4px rgba(0, 0, 0, 0.05);
            --shadow-md: 0 4px 6px rgba(0, 0, 0, 0.07);
            --shadow-lg: 0 10px 15px rgba(0, 0, 0, 0.07);
            --transition-bounce: cubic-bezier(0.34, 1.56, 0.64, 1);
        }
        
        /* Dark Theme Variables */
        body.dark-theme {
            --card-bg: #1e1e1e;
            --card-header-bg: #252525;
            --text-color: #e0e0e0;
            --text-muted: #a0a0a0;
            --heading-color: #f0f0f0;
            --link-color: #6a86ff;
            --border-color: #333333;
            --hover-bg: #2a2a2a;
            --skill-bg: #2d3748;
            --progress-bg: #262626;
        }
        
        /* Profile Layout & Base Styles */
        .profile-content {
            display: block;
            width: 100%;
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 1rem;
        }

        /* Profile Header Styles - Updated to match screenshot */
        .profile-header-container {
            width: 100%;
            margin-bottom: 2rem;
            background-color: var(--card-bg);
            box-shadow: var(--shadow-md);
            overflow: hidden;
            border-radius: 12px;
            transform: translateY(0);
            transition: transform 0.3s var(--transition-bounce), box-shadow 0.3s ease;
        }
        
        .profile-header-container:hover {
            transform: translateY(-5px);
            box-shadow: var(--shadow-lg);
        }
        
        .profile-header {
            position: relative;
            overflow: hidden;
        }
        
        /* Profile Cover Styles - Updated to match second screenshot */
        .profile-cover {
            position: relative;
            height: 200px;
            background: linear-gradient(to right, #0072ff, #00c6ff);
            overflow: hidden;
        }
        
        .edit-cover-btn {
            position: absolute;
            right: 20px;
            top: 20px;
            background-color: white;
            color: #333;
            border: none;
            border-radius: 24px;
            padding: 8px 16px;
            font-size: 14px;
            font-weight: 500;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 8px;
            transition: all 0.3s ease;
            box-shadow: 0 2px 4px rgba(0,0,0,0.15);
        }
        
        .profile-cover img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transform: scale(1);
            transition: transform 0.5s ease;
        }
        
        .profile-header:hover .profile-cover img {
            transform: scale(1.05);
        }
        
        .profile-info {
            padding: 0 2rem 1.8rem;
            position: relative;
            background-color: white;
        }
        
        /* Updated profile layout to match second screenshot */
        .profile-header-main {
            display: flex;
            justify-content: space-between;
            width: 100%;
            margin-bottom: 1.5rem;
        }
        
        .profile-name-container {
            display: flex;
            flex-direction: column;
        }
        
        .profile-right-section {
            display: flex;
            flex-direction: column;
            align-items: flex-end;
        }
        
        /* Current role styling to match second screenshot */
        .current-role-container {
            display: flex;
            flex-direction: column;
            align-items: flex-end;
            margin-bottom: 1.5rem;
        }
        
        .current-role-label {
            color: #666;
            font-size: 0.9rem;
            margin-bottom: 0.5rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .current-role-value {
            background-color: #f8f9fa;
            color: #333;
            padding: 0.5rem 1rem;
            border-radius: 4px;
            font-size: 1rem;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            border: 1px solid #e0e0e0;
        }
        
        .current-role-value i {
            color: #666;
            margin-left: 0.5rem;
        }
        
        /* Skills container styling for right alignment */
        .skills-container {
            display: flex;
            flex-direction: column;
            align-items: flex-end;
        }
        
        .skills-header {
            display: flex;
            align-items: center;
            justify-content: flex-end;
            margin-bottom: 0.75rem;
            gap: 0.75rem;
            width: 100%;
        }
        
        .skills-title {
            font-size: 1rem;
            font-weight: 600;
            color: #444;
        }
        
        .skills-star {
            color: #3F86ED;
            cursor: pointer;
            font-size: 1.2rem;
        }
        
        .skills-list {
            display: flex;
            flex-wrap: wrap;
            gap: 0.5rem;
            justify-content: flex-end;
        }
        
        /* Skill pills styling to match second screenshot */
        .skill-pill {
            background-color: #f5f7fa;
            color: #333;
            padding: 0.5rem 1rem;
            border-radius: 20px;
            font-size: 0.9rem;
            font-weight: 500;
            transition: all 0.2s ease;
            border: none;
        }
        
        .skill-pill:hover {
            background-color: #edf2f7;
            transform: translateY(-2px);
        }
        
        /* Updated profile actions */
        .profile-actions {
            display: flex;
            margin-top: 1rem;
        }
        
        .profile-action-button {
            padding: 8px 20px;
            border-radius: 8px;
            font-size: 0.9rem;
            font-weight: 600;
            cursor: pointer;
            display: inline-block;
            transition: all 0.2s ease;
            margin-right: 12px;
            text-align: center;
        }
        
        .primary-button {
            background: #222;
            color: white;
            border: none;
        }
        
        .primary-button:hover {
            background: #444;
        }
        
        .outline-button {
            background-color: white;
            color: #333;
            border: 1px solid #ddd;
        }
        
        .outline-button:hover {
            border-color: #ccc;
            background-color: #f8f8f8;
        }
        
        /* Feature cards with spacing like in the first screenshot */
        .user-stats {
            display: flex;
            flex-direction: row;
            margin-top: 1.5rem;
            margin-bottom: 2rem;
            gap: 1rem;
        }
        
        .user-stat {
            flex: 1;
            display: flex;
            padding: 1.2rem 1.5rem;
            align-items: flex-start;
            gap: 0.8rem;
            transition: all 0.2s ease;
            cursor: pointer;
            background-color: #f0f7ff;
            border-radius: 8px;
            box-shadow: 0 1px 2px rgba(0,0,0,0.05);
        }
        
        .user-stat:hover {
            background-color: #e6f0fe;
        }
        
        .user-stat-icon {
            color: #4285f4;
            font-size: 1.1rem;
        }
        
        .stat-content {
            flex: 1;
            display: flex;
            flex-direction: column;
            gap: 0.2rem;
        }
        
        .stat-title {
            font-weight: 600;
            color: #333;
            margin-bottom: 0;
            font-size: 0.95rem;
        }
        
        .stat-description {
            font-size: 0.85rem;
            color: #666;
            line-height: 1.4;
            display: block;
        }
        
        .stat-action {
            color: #4285f4;
            margin-left: auto;
            align-self: center;
            width: 30px;
            height: 30px;
            background-color: rgba(66, 133, 244, 0.1);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            border: 1px solid rgba(66, 133, 244, 0.2);
        }
        
        /* Add media query for mobile responsiveness */
        @media (max-width: 768px) {
            .user-stats {
                flex-direction: column;
            }
            
            .user-stat {
                border-right: none;
                border-bottom: 1px solid #edf2f7;
            }
            
            .user-stat:last-child {
                border-bottom: none;
            }
        }
        
        .profile-badges {
            display: flex;
            gap: 10px;
            margin-top: 1rem;
        }
        
        .badge {
            background-color: var(--skill-bg);
            color: var(--primary-color);
            padding: 4px 12px;
            border-radius: 16px;
            font-size: 0.8rem;
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 6px;
        }
        
        .premium-badge {
            background-color: #FEF3C7;
            color: #D97706;
        }
        
        /* Content Sections */
        .profile-sections {
            display: flex;
            flex-direction: column;
            gap: 2rem;
            width: 100%;
        }
        
        /* Profile Card Styles */
        .profile-card {
            background-color: var(--card-bg);
            border-radius: 12px;
            box-shadow: var(--shadow-md);
            margin-bottom: 0;
            overflow: hidden;
            transition: all 0.4s var(--transition-bounce);
            border: 1px solid var(--border-color);
            transform: translateY(0);
        }

        .profile-card:hover {
            box-shadow: var(--shadow-lg);
            transform: translateY(-8px);
        }
        
        /* Card Components */
        .card-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 1.2rem 1.5rem;
            border-bottom: 1px solid var(--border-color);
            background-color: var(--card-header-bg);
            position: relative;
            overflow: hidden;
        }
        
        .card-header::before {
            content: '';
            position: absolute;
            left: 0;
            top: 0;
            height: 100%;
            width: 4px;
            background: var(--gradient-primary);
            transform: scaleY(0);
            transition: transform 0.3s ease;
            transform-origin: bottom;
        }
        
        .profile-card:hover .card-header::before {
            transform: scaleY(1);
        }

        .card-title {
            margin: 0;
            font-size: 1.35rem;
            color: var(--heading-color);
            display: flex;
            align-items: center;
            gap: 0.8rem;
            position: relative;
        }

        .card-title i {
            color: var(--primary-color);
            font-size: 1.2rem;
            transition: transform 0.3s ease;
        }
        
        .profile-card:hover .card-title i {
            transform: scale(1.2) rotate(5deg);
        }

        .card-actions {
            display: flex;
            gap: 0.6rem;
        }

        .icon-button {
            background: none;
            border: none;
            color: var(--text-muted);
            cursor: pointer;
            padding: 0.35rem;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.3s ease;
            position: relative;
            z-index: 1;
            overflow: hidden;
        }
        
        .icon-button::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: var(--primary-light);
            border-radius: 50%;
            transform: scale(0);
            transition: transform 0.3s ease;
            z-index: -1;
        }

        .icon-button:hover {
            color: var(--primary-color);
        }
        
        .icon-button:hover::before {
            transform: scale(1);
        }

        .card-content {
            padding: 1.8rem;
        }
        
        /* Timeline Styles */
        .timeline-container {
            position: relative;
            max-width: 100%;
            padding-left: 10px;
        }
        
        .timeline-container::before {
            content: '';
            position: absolute;
            top: 30px;
            bottom: 0;
            left: 22px;
            width: 2px;
            background: linear-gradient(to bottom, var(--primary-color), rgba(79, 110, 247, 0.1));
            z-index: 0;
        }
        
        .timeline-item {
            display: flex;
            margin-bottom: 2rem;
            position: relative;
            z-index: 1;
            transition: all 0.4s var(--transition-bounce);
        }
        
        .timeline-item:hover {
            transform: translateX(8px);
        }
        
        .timeline-item:last-child {
            margin-bottom: 0;
        }
        
        .timeline-icon {
            width: 3.5rem;
            height: 3.5rem;
            border-radius: 50%;
            background: var(--gradient-primary);
            display: flex;
            justify-content: center;
            align-items: center;
            font-weight: bold;
            color: white;
            flex-shrink: 0;
            margin-right: 1.2rem;
            font-size: 1.1rem;
            box-shadow: var(--shadow-md);
            transition: all 0.3s ease;
            position: relative;
            z-index: 2;
        }
        
        .timeline-item:hover .timeline-icon {
            transform: scale(1.1) rotate(5deg);
            box-shadow: var(--shadow-lg);
        }

        .timeline-content {
            flex: 1;
            background-color: var(--card-bg);
            padding: 1.2rem;
            border-radius: 12px;
            box-shadow: var(--shadow-sm);
            border: 1px solid var(--border-color);
            transition: all 0.3s ease;
        }
        
        .timeline-item:hover .timeline-content {
            box-shadow: var(--shadow-md);
        }
        
        .timeline-content h3 {
            margin-top: 0;
            margin-bottom: 0.4rem;
            font-size: 1.2rem;
            color: var(--heading-color);
            transition: color 0.3s ease;
        }
        
        .timeline-item:hover .timeline-content h3 {
            color: var(--primary-color);
        }
        
        .timeline-meta {
            display: flex;
            flex-wrap: wrap;
            gap: 0.6rem;
            color: var(--text-color);
            margin-bottom: 0.4rem;
            font-size: 0.95rem;
        }
        
        .timeline-meta span:not(:last-child)::after {
            content: '•';
            margin-left: 0.6rem;
            color: var(--text-muted);
        }
        
        .timeline-date {
            color: var(--text-muted);
            margin-bottom: 0.6rem;
            font-size: 0.9rem;
            display: inline-block;
            background-color: var(--hover-bg);
            padding: 0.3rem 0.8rem;
            border-radius: 20px;
            transition: all 0.3s ease;
        }
        
        .timeline-item:hover .timeline-date {
            background-color: var(--primary-light);
            color: var(--primary-color);
        }
        
        .timeline-description {
            margin-top: 0.8rem;
            color: var(--text-color);
            font-size: 0.95rem;
            line-height: 1.6;
        }
        
        /* Skill and Language Items */
        .skill-item {
            background-color: var(--skill-bg);
            color: var(--text-color);
            padding: 0.6rem 1.2rem;
            border-radius: 2rem;
            font-size: 0.95rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 0.8rem;
            transition: all 0.4s var(--transition-bounce);
            box-shadow: var(--shadow-sm);
        }
        
        .skill-item:hover {
            background: var(--gradient-primary);
            color: white;
            transform: translateY(-5px) scale(1.05);
            box-shadow: var(--shadow-md);
        }
        
        .skill-item:hover .skill-level {
            background-color: rgba(255, 255, 255, 0.2);
            color: white;
        }
        
        .skill-level {
            font-size: 0.8rem;
            background-color: rgba(0, 0, 0, 0.05);
            padding: 0.25rem 0.7rem;
            border-radius: 1rem;
            color: var(--text-muted);
            transition: all 0.3s ease;
        }
        
        .level-beginner {
            color: #f59e0b;
        }
        
        .level-intermediate {
            color: #10b981;
        }
        
        .level-advanced {
            color: #3b82f6;
        }
        
        .level-expert {
            color: #8b5cf6;
        }
        
        .languages-container {
            display: flex;
            flex-direction: column;
            gap: 1.2rem;
        }
        
        .language-item {
            display: flex;
            flex-direction: column;
            gap: 0.6rem;
            transition: all 0.3s ease;
            padding: 0.8rem;
            border-radius: 10px;
        }
        
        .language-item:hover {
            background-color: var(--hover-bg);
            transform: translateX(5px);
        }
        
        .language-name {
            display: flex;
            justify-content: space-between;
            font-size: 1rem;
            font-weight: 500;
        }
        
        .language-level-text {
            color: var(--text-muted);
            font-size: 0.9rem;
            position: relative;
            top: 1px;
        }
        
        .language-progress {
            height: 8px;
            background-color: var(--progress-bg);
            border-radius: 4px;
            overflow: hidden;
            position: relative;
        }

        .language-progress-bar {
            height: 100%;
            border-radius: 4px;
            background: var(--gradient-primary);
            position: relative;
            transition: width 1.5s cubic-bezier(.17,.67,.83,.67);
            width: 0;
        }
        
        .language-item:hover .language-progress-bar {
            animation: pulse 1.5s infinite;
        }
        
        @keyframes pulse {
            0% {
                opacity: 1;
            }
            50% {
                opacity: 0.7;
            }
            100% {
                opacity: 1;
            }
        }
        
        /* Projects Styles */
        .projects-container {
            display: flex;
            flex-direction: column;
        }
        
        .project-item {
            margin-bottom: 1.8rem;
            padding-bottom: 1.8rem;
            transition: all 0.4s var(--transition-bounce);
            position: relative;
            padding: 1.2rem;
            border-radius: 12px;
        }
        
        .project-item:hover {
            background-color: var(--hover-bg);
            transform: translateY(-5px);
            box-shadow: var(--shadow-md);
        }
        
        .project-item:last-child {
            margin-bottom: 0;
            padding-bottom: 0;
        }
        
        .project-divider {
            height: 1px;
            background: linear-gradient(to right, var(--border-color), transparent);
            margin: 0 0 1.8rem 0;
        }
        
        .project-title {
            font-size: 1.2rem;
            font-weight: 600;
            margin: 0 0 0.8rem 0;
            color: var(--heading-color);
            transition: color 0.3s ease;
            position: relative;
            display: inline-block;
        }
        
        .project-title::after {
            content: '';
            position: absolute;
            left: 0;
            bottom: -4px;
            width: 0;
            height: 2px;
            background: var(--gradient-primary);
            transition: width 0.3s ease;
        }
        
        .project-item:hover .project-title {
            color: var(--primary-color);
        }
        
        .project-item:hover .project-title::after {
            width: 100%;
        }
        
        .project-description {
            font-size: 0.95rem;
            color: var(--text-color);
            margin: 0 0 1rem 0;
            line-height: 1.6;
        }

        .project-link {
            display: inline-flex;
            align-items: center;
            gap: 0.6rem;
            color: var(--primary-color);
            font-size: 0.95rem;
            font-weight: 600;
            text-decoration: none;
            transition: all 0.3s ease;
            padding: 0.5rem 1rem;
            background-color: var(--primary-light);
            border-radius: 20px;
        }

        .project-link:hover {
            gap: 0.9rem;
            background-color: var(--primary-color);
            color: white;
            box-shadow: var(--shadow-sm);
        }
        
        /* Honors Styles */
        .honors-container {
            display: flex;
            flex-direction: column;
        }
        
        .honor-item {
            margin-bottom: 1.8rem;
            padding-bottom: 1.8rem;
            transition: all 0.4s var(--transition-bounce);
            position: relative;
            padding: 1.2rem;
            border-radius: 12px;
        }
        
        .honor-item:hover {
            background-color: var(--hover-bg);
            transform: translateY(-5px);
            box-shadow: var(--shadow-md);
        }
        
        .honor-item:last-child {
            margin-bottom: 0;
            padding-bottom: 0;
        }
        
        .honor-divider {
            height: 1px;
            background: linear-gradient(to right, var(--border-color), transparent);
            margin: 0 0 1.8rem 0;
        }
        
        .honor-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 0.8rem;
        }
        
        .honor-title {
            font-size: 1.2rem;
            font-weight: 600;
            margin: 0;
            color: var(--heading-color);
            transition: color 0.3s ease;
            position: relative;
            display: inline-block;
        }
        
        .honor-title::after {
            content: '';
            position: absolute;
            left: 0;
            bottom: -4px;
            width: 0;
            height: 2px;
            background: var(--gradient-primary);
            transition: width 0.3s ease;
        }
        
        .honor-item:hover .honor-title {
            color: var(--primary-color);
        }
        
        .honor-item:hover .honor-title::after {
            width: 100%;
        }
        
        .honor-date {
            font-size: 0.9rem;
            color: var(--text-muted);
            background-color: var(--skill-bg);
            padding: 0.35rem 0.7rem;
            border-radius: 18px;
            transition: all 0.3s ease;
            box-shadow: var(--shadow-sm);
        }
        
        .honor-item:hover .honor-date {
            background-color: var(--primary-light);
            color: var(--primary-color);
            transform: scale(1.05);
            box-shadow: var(--shadow-md);
        }
        
        .honor-issuer {
            font-size: 0.95rem;
            color: var(--text-color);
            margin: 0 0 0.8rem 0;
            line-height: 1.6;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .honor-issuer::before {
            content: '\f19c';
            font-family: 'Font Awesome 5 Free';
            font-weight: 900;
            color: var(--primary-color);
            font-size: 0.9rem;
            transition: transform 0.3s ease;
        }
        
        .honor-item:hover .honor-issuer::before {
            transform: scale(1.2);
        }
        
        .honor-description {
            font-size: 0.95rem;
            color: var(--text-color);
            margin: 0.8rem 0 0 0;
            line-height: 1.6;
            padding-left: 1.5rem;
            position: relative;
        }
        
        .honor-description::before {
            content: '';
            position: absolute;
            left: 0;
            top: 0.5rem;
            width: 3px;
            height: calc(100% - 1rem);
            background: var(--primary-light);
            border-radius: 3px;
            transition: background 0.3s ease;
        }
        
        .honor-item:hover .honor-description::before {
            background: var(--gradient-primary);
        }
        
        /* Contact Section */
        .contact-item {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            margin-bottom: 1rem;
            padding: 0.5rem;
            border-radius: 4px;
            transition: background-color 0.2s ease;
        }
        
        .contact-item:hover {
            background-color: var(--hover-bg);
        }
        
        .contact-item:last-child {
            margin-bottom: 0;
        }
        
        .contact-item i {
            color: var(--primary-color);
            width: 1.5rem;
            text-align: center;
        }
        
        .contact-item a {
            color: var(--link-color);
            text-decoration: none;
            word-break: break-all;
        }
        
        .contact-item a:hover {
            text-decoration: underline;
        }
        
        /* Stats Section */
        .stats-container {
            display: flex;
            justify-content: space-around;
            text-align: center;
        }
        
        .stat-item {
            display: flex;
            flex-direction: column;
            align-items: center;
        }
        
        .stat-value {
            font-size: 1.75rem;
            font-weight: bold;
            color: var(--primary-color);
        }
        
        .stat-label {
            font-size: 0.9rem;
            color: var(--text-muted);
            margin-top: 0.25rem;
        }
        
        /* About Section */
        .about-section p {
            line-height: 1.6;
            color: var(--text-color);
            margin: 0;
        }
        
        /* Animation Classes */
        .animate-in {
            opacity: 0;
            animation: fadeInUp 0.6s ease forwards;
        }
        
        .delay-1 {
            animation-delay: 0.1s;
        }
        
        .delay-2 {
            animation-delay: 0.2s;
        }
        
        .delay-3 {
            animation-delay: 0.3s;
        }
        
        .delay-4 {
            animation-delay: 0.4s;
        }
        
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        /* Update responsive styles to match screenshot */
        @media (max-width: 768px) {
            .profile-top-section {
                flex-direction: column;
                align-items: center;
                text-align: center;
                padding-top: 0;
            }
            
            .profile-picture-wrapper {
                margin-right: 0;
                margin-bottom: 1.5rem;
            }
            
            .profile-name-title {
                flex-direction: column;
                align-items: center;
            }
            
            .current-role {
                margin-left: 0;
                margin-top: 0.8rem;
            }
            
            .profile-meta {
                justify-content: center;
            }
            
            .profile-actions {
                justify-content: center;
            }
            
            .profile-skills-preview {
                justify-content: center;
            }
            
            .user-connections {
                justify-content: center;
            }
            
            .profile-location {
                justify-content: center;
            }
            
            .user-stats {
                flex-direction: column;
            }
        }
        
        @media (max-width: 576px) {
            .profile-picture-wrapper {
                width: 100px;
                height: 100px;
                margin-top: -50px;
            }
            
            .profile-cover {
                height: 150px;
            }
            
            .profile-name {
                font-size: 1.5rem;
            }
            
            .user-stat {
                padding: 0.8rem;
            }
            
            .stat-description {
                display: none;
            }
        }
        
        .profile-connections {
            display: flex;
            align-items: center;
            gap: 8px;
            color: var(--text-muted);
            font-size: 0.9rem;
            margin-bottom: 0.5rem;
        }
        
        /* Contact in Profile Header */
        .profile-contact {
            display: flex;
            flex-wrap: wrap;
            gap: 0.8rem;
            margin: 1.2rem 0;
        }
        
        .profile-contact .contact-item {
            margin-bottom: 0;
            background-color: var(--skill-bg);
            padding: 0.5rem 1rem;
            border-radius: 24px;
            transition: all 0.3s var(--transition-bounce);
            box-shadow: var(--shadow-sm);
        }
        
        .profile-contact .contact-item:hover {
            background: var(--gradient-primary);
            color: white;
            transform: translateY(-5px) scale(1.05);
            box-shadow: var(--shadow-md);
        }
        
        .profile-contact .contact-item i {
            color: var(--primary-color);
            transition: color 0.3s ease;
        }
        
        .profile-contact .contact-item:hover i,
        .profile-contact .contact-item:hover a {
            color: white;
        }
        
        .profile-badges {
            display: flex;
            gap: 12px;
            margin-top: 1.2rem;
        }
        
        .badge {
            background-color: var(--skill-bg);
            color: var(--primary-color);
            padding: 6px 14px;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 8px;
            transition: all 0.3s var(--transition-bounce);
            box-shadow: var(--shadow-sm);
        }
        
        .badge:hover {
            transform: translateY(-5px);
            box-shadow: var(--shadow-md);
        }
        
        .badge i {
            font-size: 1rem;
        }
        
        .badge.premium-badge {
            background: linear-gradient(135deg, #f59e0b, #f97316);
            color: white;
        }
        
        /* Animation styles */
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        /* Reveal animation for scroll */
        .profile-card, .timeline-item, .project-item, .skill-item, .language-item {
            opacity: 0;
            transform: translateY(20px);
            transition: all 0.6s cubic-bezier(0.16, 1, 0.3, 1);
        }
        
        .revealed {
            opacity: 1;
            transform: translateY(0);
        }
        
        /* Enhanced transitions */
        .profile-card {
            transform-origin: center bottom;
        }
        
        .profile-card:hover {
            transform: translateY(-10px) scale(1.02);
        }
        
        /* Fixed layout styles to ensure proper alignment and display */
        .profile-header-content {
            display: flex;
            flex-direction: column;
            width: 100%;
            padding-top: 1rem;
        }
        
        .profile-top-section {
            display: flex;
            margin-bottom: 1.5rem;
            padding-top: 1rem;
            width: 100%;
        }
        
        .profile-picture-wrapper {
            position: relative;
            width: 150px;
            height: 150px;
            flex-shrink: 0;
            margin-right: 2rem;
            border-radius: 50%;
            box-shadow: 0 4px 10px rgba(0,0,0,0.1);
            overflow: hidden;
            background-color: white;
            margin-top: -75px;
            border: 4px solid white;
        }
        
        .profile-picture {
            width: 100%;
            height: 100%;
            border-radius: 50%;
            object-fit: cover;
            transition: transform 0.5s ease;
        }
        
        .profile-info-content {
            flex: 1;
            display: flex;
            flex-direction: column;
        }
        
        .profile-name {
            font-size: 2.5rem;
            font-weight: 700;
            color: #222;
            margin: 0;
            margin-bottom: 0.5rem;
        }
        
        .profile-headline {
            font-size: 1.1rem;
            color: #333;
            margin-bottom: 0.6rem;
            line-height: 1.5;
        }
        
        .profile-location {
            color: #666;
            font-size: 0.9rem;
            margin-top: 0.3rem;
        }
        
        /* Profile picture edit overlay */
        .edit-photo-overlay {
            position: absolute;
            bottom: 5px;
            right: 5px;
            background: #3F86ED;
            color: white;
            width: 32px;
            height: 32px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            box-shadow: 0 2px 5px rgba(0,0,0,0.2);
            transition: all 0.3s ease;
            z-index: 2;
        }
        
        .edit-photo-overlay:hover {
            transform: scale(1.1);
        }
        
        /* Profile meta and connection styles */
        .profile-meta {
            display: flex;
            flex-wrap: wrap;
            gap: 1rem;
            margin: 0.8rem 0;
        }
        
        .profile-meta-item {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            color: #6c757d;
            font-size: 0.95rem;
            transition: transform 0.3s ease;
        }
        
        .profile-meta-item:hover {
            transform: translateX(3px);
            color: #4F6EF7;
        }
        
        .profile-meta-item i {
            color: #4F6EF7;
            width: 1.2rem;
            text-align: center;
            font-size: 0.9rem;
        }
        
        .user-connections {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            color: #666;
            font-size: 0.95rem;
            margin-bottom: 1rem;
        }
        
        .user-connections i {
            color: #3F86ED;
        }
        
        .connections-count {
            font-weight: 600;
            color: #3F86ED;
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
                <a href="../view/networking.html">Networking</a>
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
                <div class="user-profile active">
                    <a href="../view/profile.php">
                    <img src="<?php echo htmlspecialchars($profile_picture); ?>" alt="User profile" class="avatar">
                </a>
            </div>
        </div>
        </nav>

        <div class="content">
            <!-- Profile Content -->
            <div class="profile-content">
                <!-- Profile Header -->
                <div class="profile-header-container">
        <div class="profile-header">
            <div class="profile-cover">
                            <?php if (!empty($user['cover_image'])): ?>
                            <img src="<?php echo htmlspecialchars($user['cover_image']); ?>" alt="Cover Image">
                            <?php endif; ?>
                <button class="edit-cover-btn">
                                <i class="fas fa-camera"></i> Edit Cover
                </button>
            </div>
                        
            <div class="profile-info">
                            <div class="profile-header-content">
                                <div class="profile-top-section">
                <div class="profile-picture-wrapper">
                                        <img class="profile-picture" src="<?php echo htmlspecialchars($profile_picture); ?>" alt="Profile Picture">
                    <div class="edit-photo-overlay">
                        <i class="fas fa-camera"></i>
                    </div>
                </div>
                
                                    <div class="profile-info-content">
                                        <div class="profile-header-main">
                                            <div class="profile-name-container">
                                                <h1 class="profile-name"><?php echo htmlspecialchars($user['full_name']); ?></h1>
                                                <div class="profile-headline">
                                                    <?php if (!empty($user['headline'])): ?>
                                                        <?php echo htmlspecialchars($user['headline']); ?>
                                                    <?php else: ?>
                                                        Software Engineer
                                                    <?php endif; ?>
                                                </div>
                                                <div class="profile-location">
                                                    <?php if (!empty($user['location'])): ?>
                                                    <span>
                                                        <?php echo htmlspecialchars($user['location']); ?>
                                                    </span>
                                                    <?php endif; ?>
                </div>
                
                                                <div class="profile-meta">
                                                    <?php if (!empty($user['email'])): ?>
                                                    <div class="profile-meta-item">
                                                        <i class="fas fa-envelope"></i>
                                                        <span><?php echo htmlspecialchars($user['email']); ?></span>
                                                    </div>
                                                    <?php endif; ?>
                                                    
                                                    <?php if (!empty($user['phone'])): ?>
                                                    <div class="profile-meta-item">
                                                        <i class="fas fa-phone"></i>
                                                        <span><?php echo htmlspecialchars($user['phone']); ?></span>
                                                    </div>
                                                    <?php endif; ?>
                                                    
                        <?php if (!empty($user['address'])): ?>
                                                    <div class="profile-meta-item">
                                                        <i class="fas fa-home"></i>
                                                        <span><?php echo htmlspecialchars($user['address']); ?></span>
                                                    </div>
                        <?php endif; ?>
                                                    
                                                    <?php if (!empty($user['created_at'])): ?>
                                                    <div class="profile-meta-item">
                                                        <i class="fas fa-user-clock"></i>
                                                        <span>Member since: <?php echo date('M Y', strtotime($user['created_at'])); ?></span>
                                                    </div>
                                                    <?php endif; ?>
                                                </div>
                                                
                                                <div class="user-connections">
                                                    <i class="fas fa-user-friends"></i>
                                                    <span class="connections-count">500+</span> connections
                    </div>
                </div>
                                            
                                            <div class="profile-right-section">
                                                <div class="current-role-container">
                                                    <div class="current-role-label">Current role</div>
                                                    <div class="current-role-value">
                                                        <span><?php echo !empty($user['job_title']) ? htmlspecialchars($user['job_title']) : 'Software Engineer'; ?></span>
                                                        <i class="fas fa-briefcase"></i>
            </div>
        </div>
        
                                                <div class="skills-container">
                                                    <div class="skills-header">
                                                        <div class="skills-title">Skills</div>
                                                        <div class="skills-star"><i class="far fa-star"></i></div>
                                                    </div>
                                                    <div class="skills-list">
                                                        <?php 
                                                        // Display skills
                                                        $previewSkills = array_slice($skills, 0, 5);
                                                        foreach ($previewSkills as $skill):
                                                            $skillName = is_array($skill) ? ($skill['name'] ?? $skill) : $skill;
                                                        ?>
                                                        <div class="skill-pill"><?php echo htmlspecialchars($skillName); ?></div>
                                                        <?php endforeach; ?>
                                                    </div>
                                                </div>
                    </div>
                </div>
                
                <div class="profile-actions">
                    <button class="profile-action-button primary-button">
                                                Edit Profile
                    </button>
                    <button class="profile-action-button outline-button">
                                                Settings
                    </button>
                                        </div>
                                    </div>
                </div>
                
                                <div class="user-stats">
                                    <div class="user-stat">
                                        <div class="user-stat-icon">
                                            <i class="fas fa-file-alt"></i>
                                        </div>
                                        <div class="stat-content">
                                            <div class="stat-title">Ready for work</div>
                                            <div class="stat-description">Show recruiters that you're ready for work.</div>
                                        </div>
                                        <div class="stat-action">
                                            <i class="fas fa-arrow-right"></i>
                                        </div>
                                    </div>
                                    <div class="user-stat">
                                        <div class="user-stat-icon">
                                            <i class="fas fa-share-alt"></i>
                                        </div>
                                        <div class="stat-content">
                                            <div class="stat-title">Share posts</div>
                                            <div class="stat-description">Share latest news to get connected with others.</div>
                                        </div>
                                        <div class="stat-action">
                                            <i class="fas fa-arrow-right"></i>
                                        </div>
                                    </div>
                                    <div class="user-stat">
                                        <div class="user-stat-icon">
                                            <i class="fas fa-pen"></i>
                                        </div>
                                        <div class="stat-content">
                                            <div class="stat-title">Update</div>
                                            <div class="stat-description">Keep your profile updated so that recruiters know you better.</div>
                                        </div>
                                        <div class="stat-action">
                                            <i class="fas fa-arrow-right"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                </div>
            </div>
        </div>
        
                <!-- Profile Sections (Stacked Vertically) -->
                <div class="profile-sections">
                <?php if (!empty($user['about'])): ?>
                    <!-- About Section -->
                <section class="profile-card">
                    <div class="card-header">
                        <h2 class="card-title"><i class="fas fa-user"></i> About</h2>
                        <div class="card-actions">
                            <button class="icon-button">
                                <i class="fas fa-pen"></i>
                            </button>
                        </div>
                    </div>
                    <div class="card-content about-section">
                        <p><?php echo nl2br(htmlspecialchars($user['about'])); ?></p>
                    </div>
                </section>
                <?php endif; ?>
                
                    <!-- What I'm Seeking Section -->
                    <?php if (!empty($user['seeking'])): ?>
                    <section class="profile-card">
                        <div class="card-header">
                            <h2 class="card-title"><i class="fas fa-search"></i> What I'm Seeking</h2>
                            <div class="card-actions">
                                <button class="icon-button">
                                    <i class="fas fa-pen"></i>
                                </button>
                            </div>
                        </div>
                        <div class="card-content">
                            <p><?php echo nl2br(htmlspecialchars($user['seeking'])); ?></p>
                        </div>
                    </section>
                    <?php endif; ?>
                    
                    <!-- Experience Section -->
                <?php if (!empty($experiences)): ?>
                <section class="profile-card">
                    <div class="card-header">
                        <h2 class="card-title"><i class="fas fa-briefcase"></i> Experience</h2>
                        <div class="card-actions">
                            <button class="icon-button">
                                <i class="fas fa-plus"></i>
                            </button>
                        </div>
                    </div>
                    <div class="card-content">
                            <div class="timeline-container">
                                <?php foreach ($experiences as $experience): ?>
                        <div class="timeline-item">
                            <div class="timeline-icon">
                                        <?php 
                                        // Create initials from company name
                                        $company = trim($experience['company'] ?? '');
                                        $initials = '';
                                        if (!empty($company)) {
                                            $words = explode(' ', $company);
                                            if (count($words) >= 2) {
                                                $initials = strtoupper(substr($words[0], 0, 1) . substr($words[1], 0, 1));
                                            } else {
                                                $initials = strtoupper(substr($company, 0, 2));
                                            }
                                        } else {
                                            $initials = 'CO';
                                        }
                                        echo $initials;
                                        ?>
                            </div>
                            <div class="timeline-content">
                                        <h3><?php echo htmlspecialchars($experience['title'] ?? ''); ?></h3>
                                        <div class="timeline-meta">
                                            <?php if (!empty($experience['company'])): ?>
                                            <span class="company"><?php echo htmlspecialchars($experience['company']); ?></span>
                                            <?php endif; ?>
                                            <?php if (!empty($experience['location'])): ?>
                                            <span class="location"><?php echo htmlspecialchars($experience['location']); ?></span>
                                            <?php endif; ?>
                                        </div>
                                        <div class="timeline-date">
                                    <?php 
                                            $startDate = !empty($experience['start_date']) ? date('M Y', strtotime($experience['start_date'])) : '';
                                            $endDate = !empty($experience['end_date']) ? date('M Y', strtotime($experience['end_date'])) : 'Present';
                                            echo $startDate . ' - ' . $endDate;
                                            ?>
                                        </div>
                                        <?php if (!empty($experience['description'])): ?>
                                        <p class="timeline-description"><?php echo nl2br(htmlspecialchars($experience['description'])); ?></p>
                                <?php endif; ?>
                            </div>
                        </div>
                        <?php endforeach; ?>
                            </div>
                    </div>
                </section>
                <?php endif; ?>
                
                    <!-- Education Section -->
                <?php if (!empty($educations)): ?>
                <section class="profile-card">
                    <div class="card-header">
                        <h2 class="card-title"><i class="fas fa-graduation-cap"></i> Education</h2>
                        <div class="card-actions">
                            <button class="icon-button">
                                <i class="fas fa-plus"></i>
                            </button>
                        </div>
                    </div>
                    <div class="card-content">
                            <div class="timeline-container">
                                <?php foreach ($educations as $edu): ?>
                        <div class="timeline-item">
                            <div class="timeline-icon">
                                        <?php 
                                        // Create initials from school name
                                        $school = trim($edu['school'] ?? '');
                                        $initials = '';
                                        if (!empty($school)) {
                                            $words = explode(' ', $school);
                                            if (count($words) >= 2) {
                                                $initials = strtoupper(substr($words[0], 0, 1) . substr($words[1], 0, 1));
                                            } else {
                                                $initials = strtoupper(substr($school, 0, 2));
                                            }
                                        } else {
                                            $initials = 'SC';
                                        }
                                        echo $initials;
                                        ?>
                            </div>
                            <div class="timeline-content">
                                        <h3>
                                    <?php 
                                            $displayTitle = [];
                                            if (!empty($edu['degree'])) $displayTitle[] = htmlspecialchars($edu['degree']);
                                            if (!empty($edu['field_of_study'])) $displayTitle[] = htmlspecialchars($edu['field_of_study']);
                                            echo implode(', ', $displayTitle);
                                            ?>
                                        </h3>
                                        <div class="timeline-meta">
                                            <?php if (!empty($edu['school'])): ?>
                                            <span class="school"><?php echo htmlspecialchars($edu['school']); ?></span>
                                            <?php endif; ?>
                                            <?php if (!empty($edu['location'])): ?>
                                            <span class="location"><?php echo htmlspecialchars($edu['location']); ?></span>
                                            <?php endif; ?>
                                        </div>
                                        <div class="timeline-date">
                                    <?php 
                                            $startDate = !empty($edu['start_date']) ? date('M Y', strtotime($edu['start_date'])) : '';
                                            $endDate = !empty($edu['end_date']) ? date('M Y', strtotime($edu['end_date'])) : 'Present';
                                            echo $startDate . ' - ' . $endDate;
                                            ?>
                                        </div>
                                <?php if (!empty($edu['description'])): ?>
                                <p class="timeline-description"><?php echo nl2br(htmlspecialchars($edu['description'])); ?></p>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                            </div>
                        </div>
                </section>
                <?php endif; ?>
                
                    <!-- Skills Section -->
                <?php if (!empty($skills)): ?>
                <section class="profile-card">
                    <div class="card-header">
                        <h2 class="card-title"><i class="fas fa-tools"></i> Skills</h2>
                        <div class="card-actions">
                            <button class="icon-button">
                                <i class="fas fa-plus"></i>
                            </button>
                        </div>
                    </div>
                    <div class="card-content">
                            <div class="skills-header">
                                <div class="skills-title">Skills</div>
                                <div class="skills-star"><i class="far fa-star"></i></div>
                            </div>
                            <div class="skills">
                                <?php foreach ($skills as $skill): ?>
                                <div class="skill-pill"><?php echo htmlspecialchars($skill['name'] ?? $skill); ?></div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </section>
                <?php endif; ?>
                
                    <!-- Projects Section -->
                    <?php if (!empty($projects)): ?>
                    <section class="profile-card">
                        <div class="card-header">
                            <h2 class="card-title"><i class="fas fa-project-diagram"></i> Projects</h2>
                            <div class="card-actions">
                                <button class="icon-button">
                                    <i class="fas fa-plus"></i>
                                </button>
                        </div>
                    </div>
                        <div class="card-content">
                            <div class="projects-container">
                                <?php foreach ($projects as $index => $project): ?>
                    <div class="project-item">
                                    <h3 class="project-title"><?php echo htmlspecialchars($project['title'] ?? ''); ?></h3>
                                    <p class="project-description"><?php echo nl2br(htmlspecialchars($project['description'] ?? '')); ?></p>
                        <?php if (!empty($project['url'])): ?>
                        <a href="<?php echo htmlspecialchars($project['url']); ?>" target="_blank" class="project-link">
                            <i class="fas fa-external-link-alt"></i> View Project
                        </a>
                        <?php endif; ?>
                    </div>
                                <?php if ($index < count($projects) - 1): ?>
                                <div class="project-divider"></div>
                                <?php endif; ?>
                    <?php endforeach; ?>
                            </div>
                        </div>
                </section>
                <?php endif; ?>
                
                    <!-- Honors Section -->
                <?php if (!empty($honors)): ?>
                    <section class="profile-card">
                        <div class="card-header">
                            <h2 class="card-title"><i class="fas fa-award"></i> Honors & Awards</h2>
                            <div class="card-actions">
                                <button class="icon-button">
                                    <i class="fas fa-plus"></i>
                                </button>
                            </div>
                        </div>
                        <div class="card-content">
                            <div class="honors-container">
                                <?php foreach ($honors as $index => $honor): ?>
                    <div class="honor-item">
                                    <div class="honor-header">
                                        <h3 class="honor-title"><?php echo htmlspecialchars($honor['title'] ?? ''); ?></h3>
                                        <span class="honor-date"><?php echo htmlspecialchars(formatDate($honor['date'] ?? '')); ?></span>
                        </div>
                                    <p class="honor-issuer"><?php echo htmlspecialchars($honor['issuer'] ?? ''); ?></p>
                        <?php if (!empty($honor['description'])): ?>
                                    <p class="honor-description"><?php echo nl2br(htmlspecialchars($honor['description'])); ?></p>
                        <?php endif; ?>
                    </div>
                                <?php if ($index < count($honors) - 1): ?>
                                <div class="honor-divider"></div>
                <?php endif; ?>
                                <?php endforeach; ?>
            </div>
                    </div>
                    </section>
                        <?php endif; ?>
                
                    <!-- Languages Section -->
                    <?php if (!empty($languages)): ?>
                    <section class="profile-card">
                    <div class="card-header">
                            <h2 class="card-title"><i class="fas fa-language"></i> Languages</h2>
                            <div class="card-actions">
                                <button class="icon-button">
                                    <i class="fas fa-plus"></i>
                                </button>
                            </div>
                    </div>
                    <div class="card-content">
                            <div class="languages-container">
                                <?php foreach ($languages as $language): ?>
                                <div class="language-item">
                                    <div class="language-name">
                                        <span><?php echo htmlspecialchars($language['name'] ?? ''); ?></span>
                                        <span class="language-level-text"><?php echo htmlspecialchars($language['level'] ?? ''); ?></span>
                            </div>
                                    <div class="language-progress">
                                        <div class="language-progress-bar" data-level="<?php echo strtolower($language['level'] ?? ''); ?>"></div>
                            </div>
                            </div>
                                <?php endforeach; ?>
                        </div>
                    </div>
                    </section>
                    <?php endif; ?>
                    
                </div> <!-- End of profile-sections -->
            </div> <!-- End of profile-content -->
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Initialize animations with a slight delay
            setTimeout(() => {
                // Add animation classes to profile elements
                document.querySelectorAll('.profile-card').forEach((card, index) => {
                        card.classList.add('animate-in');
                    card.style.animationDelay = `${0.1 + (index * 0.1)}s`;
                });
                
                // Animate timeline items
                document.querySelectorAll('.timeline-item').forEach((item, index) => {
                    item.classList.add('animate-in');
                    item.style.animationDelay = `${0.3 + (index * 0.1)}s`;
                });
                
                // Initialize language progress bars
                document.querySelectorAll('.language-progress-bar').forEach((bar) => {
                    // Get the progress value from the data attribute
                    const level = bar.getAttribute('data-level');
                    let width = 0;
                    
                    switch(level) {
                        case 'beginner':
                            width = 25;
                            break;
                        case 'intermediate':
                            width = 50;
                            break;
                        case 'advanced':
                            width = 75;
                            break;
                        case 'expert':
                        case 'fluent':
                        case 'native':
                            width = 100;
                            break;
                        default:
                            width = 0;
                    }
                    
                    // Set width with a delay for animation
                    setTimeout(() => {
                        bar.style.width = `${width}%`;
                    }, 500);
                });
            }, 200);
            
            // Add scroll reveal animations
            const observer = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        entry.target.classList.add('revealed');
                        observer.unobserve(entry.target);
                    }
                });
            }, { threshold: 0.1 });
            
            // Observe all relevant elements
            document.querySelectorAll('.profile-card, .timeline-item, .project-item, .skill-item, .language-item').forEach(item => {
                observer.observe(item);
            });
            
            // Tab functionality
            document.querySelectorAll('.section-tab').forEach(tab => {
                tab.addEventListener('click', function() {
                    // Remove active class from all tabs
                    document.querySelectorAll('.section-tab').forEach(t => {
                        t.classList.remove('active');
                    });
                    
                    // Add active class to clicked tab
                    this.classList.add('active');
                    
                    // Add logic to show corresponding section (in future implementation)
                });
            });
            
            // Theme toggle functionality
            const themeToggle = document.getElementById('themeToggle');
            const sunIcon = document.querySelector('.sun-icon');
            const moonIcon = document.querySelector('.moon-icon');
            
            if (themeToggle) {
                // Check if dark mode is enabled in local storage
                const isDarkMode = localStorage.getItem('darkMode') === 'true';
                
                // Update body class based on current theme
                if (isDarkMode) {
                    document.body.classList.add('dark-theme');
                    sunIcon.style.display = 'none';
                    moonIcon.style.display = 'inline-block';
                } else {
                    sunIcon.style.display = 'inline-block';
                    moonIcon.style.display = 'none';
                }
                
                // Toggle dark mode
                themeToggle.addEventListener('click', function() {
                    document.body.classList.toggle('dark-theme');
                    
                    const isDarkModeNow = document.body.classList.contains('dark-theme');
                    localStorage.setItem('darkMode', isDarkModeNow);
                    
                    // Toggle icon visibility
                    sunIcon.style.display = isDarkModeNow ? 'none' : 'inline-block';
                    moonIcon.style.display = isDarkModeNow ? 'inline-block' : 'none';
                });
            }

            // Add the signout handler
            document.querySelector('.sign-out-btn').addEventListener('click', function() {
                fetch('../model/signout.php')
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            window.location.href = data.redirect;
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        alert('Error signing out. Please try again.');
                    });
            });
        });
    </script>
</body>
</html>
