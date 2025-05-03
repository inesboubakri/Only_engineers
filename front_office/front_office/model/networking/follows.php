<?php
// Include database connection with correct path
require_once __DIR__ . '/../db_connection.php';
// Include notifications functionality
require_once __DIR__ . '/notifications.php';

// Add error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

/**
 * Check if a user is following another user
 *
 * @param int $follower_id The ID of the follower
 * @param int $following_id The ID of the user being followed
 * @return bool True if following, false otherwise
 */
if (!function_exists('isFollowing')) {
    function isFollowing($follower_id, $following_id) {
        try {
            $conn = getConnection();
            $stmt = $conn->prepare("SELECT id FROM networking_follows WHERE follower_id = ? AND following_id = ?");
            $stmt->execute([$follower_id, $following_id]);
            
            return $stmt->rowCount() > 0;
        } catch (PDOException $e) {
            error_log("Error checking follow status: " . $e->getMessage());
            return false;
        }
    }
}

/**
 * Follow a user
 *
 * @param int $follower_id The ID of the follower
 * @param int $following_id The ID of the user to follow
 * @return bool True if successful, false otherwise
 */
function followUser($follower_id, $following_id) {
    try {
        $conn = getConnection();
        
        // Check if already following
        if (isFollowing($follower_id, $following_id)) {
            return true; // Already following
        }
        
        // Insert new follow relationship
        $stmt = $conn->prepare("INSERT INTO networking_follows (follower_id, following_id) VALUES (?, ?)");
        $result = $stmt->execute([$follower_id, $following_id]);
        
        if ($result) {
            try {
                // Create notification for the user being followed
                $stmt = $conn->prepare("SELECT full_name FROM users WHERE user_id = ?");
                $stmt->execute([$follower_id]);
                $user = $stmt->fetch(PDO::FETCH_ASSOC);
                
                $message = $user['full_name'] . " started following you";
                createNotification($following_id, 'follow', $message, $follower_id);
            } catch (Exception $e) {
                error_log("Error creating notification: " . $e->getMessage());
                // Continue even if notification fails
            }
        }
        
        return $result;
    } catch (PDOException $e) {
        error_log("Error following user: " . $e->getMessage());
        return false;
    }
}

/**
 * Unfollow a user
 *
 * @param int $follower_id The ID of the follower
 * @param int $following_id The ID of the user to unfollow
 * @return bool True if successful, false otherwise
 */
function unfollowUser($follower_id, $following_id) {
    try {
        $conn = getConnection();
        
        // Remove follow relationship
        $stmt = $conn->prepare("DELETE FROM networking_follows WHERE follower_id = ? AND following_id = ?");
        return $stmt->execute([$follower_id, $following_id]);
    } catch (PDOException $e) {
        error_log("Error unfollowing user: " . $e->getMessage());
        return false;
    }
}

/**
 * Get number of followers for a user
 *
 * @param int $user_id The user ID
 * @return int Number of followers
 */
function getFollowersCount($user_id) {
    try {
        $conn = getConnection();
        $stmt = $conn->prepare("SELECT COUNT(*) as count FROM networking_follows WHERE following_id = ?");
        $stmt->execute([$user_id]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        return $result['count'] ?? 0;
    } catch (PDOException $e) {
        error_log("Error getting followers count: " . $e->getMessage());
        return 0;
    }
}

/**
 * Get number of users a user is following
 *
 * @param int $user_id The user ID
 * @return int Number of users being followed
 */
function getFollowingCount($user_id) {
    try {
        $conn = getConnection();
        $stmt = $conn->prepare("SELECT COUNT(*) as count FROM networking_follows WHERE follower_id = ?");
        $stmt->execute([$user_id]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        return $result['count'] ?? 0;
    } catch (PDOException $e) {
        error_log("Error getting following count: " . $e->getMessage());
        return 0;
    }
}

/**
 * Get followers of a user
 *
 * @param int $user_id The user ID
 * @param int $limit Optional limit of results
 * @param int $offset Optional offset for pagination
 * @return array List of followers with their basic info
 */
function getFollowers($user_id, $limit = 10, $offset = 0) {
    try {
        $conn = getConnection();
        $stmt = $conn->prepare("
            SELECT u.user_id, u.full_name, u.profile_picture, u.position 
            FROM networking_follows nf
            JOIN users u ON nf.follower_id = u.user_id
            WHERE nf.following_id = ?
            ORDER BY nf.created_at DESC
            LIMIT ? OFFSET ?
        ");
        $stmt->execute([$user_id, $limit, $offset]);
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Error getting followers: " . $e->getMessage());
        return [];
    }
}

/**
 * Get users that a user is following
 *
 * @param int $user_id The user ID
 * @param int $limit Optional limit of results
 * @param int $offset Optional offset for pagination
 * @return array List of followed users with their basic info
 */
function getFollowing($user_id, $limit = 10, $offset = 0) {
    try {
        $conn = getConnection();
        $stmt = $conn->prepare("
            SELECT u.user_id, u.full_name, u.profile_picture, u.position  
            FROM networking_follows nf
            JOIN users u ON nf.following_id = u.user_id
            WHERE nf.follower_id = ?
            ORDER BY nf.created_at DESC
            LIMIT ? OFFSET ?
        ");
        $stmt->execute([$user_id, $limit, $offset]);
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Error getting following: " . $e->getMessage());
        return [];
    }
}

// Handle AJAX requests if this file is accessed directly
if ($_SERVER['REQUEST_METHOD'] === 'POST' && basename($_SERVER['PHP_SELF']) === basename(__FILE__)) {
    session_start();
    
    // Check if user is logged in
    if (!isset($_SESSION['user_id'])) {
        http_response_code(401);
        echo json_encode(['success' => false, 'message' => 'User not logged in']);
        exit;
    }
    
    $follower_id = $_SESSION['user_id'];
    $action = $_POST['action'] ?? '';
    $following_id = $_POST['user_id'] ?? 0;
    
    // Debug output - save to error log
    error_log("Follow action initiated: " . $action . " - Follower: " . $follower_id . " - Following: " . $following_id);
    
    // Validate user_id
    if (!$following_id || !is_numeric($following_id)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Invalid user ID']);
        exit;
    }
    
    // Prevent following self
    if ($follower_id == $following_id) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Cannot follow yourself']);
        exit;
    }
    
    $result = false;
    $message = '';
    
    try {
        switch ($action) {
            case 'follow':
                $result = followUser($follower_id, $following_id);
                $message = $result ? 'Successfully followed user' : 'Failed to follow user';
                break;
                
            case 'unfollow':
                $result = unfollowUser($follower_id, $following_id);
                $message = $result ? 'Successfully unfollowed user' : 'Failed to unfollow user';
                break;
                
            default:
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'Invalid action']);
                exit;
        }
    } catch (Exception $e) {
        error_log("Error in follow/unfollow action: " . $e->getMessage());
        $message = "Error: " . $e->getMessage();
    }
    
    echo json_encode([
        'success' => $result,
        'message' => $message,
        'is_following' => isFollowing($follower_id, $following_id),
        'debug' => [
            'follower_id' => $follower_id,
            'following_id' => $following_id,
            'action' => $action
        ]
    ]);
    exit;
}