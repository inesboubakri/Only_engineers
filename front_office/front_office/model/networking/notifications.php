<?php
// Include database connection with correct path
require_once __DIR__ . '/../db_connection.php';

/**
 * Create a new notification
 * 
 * @param int $recipientId User ID of the recipient
 * @param int $senderId User ID of the sender (optional)
 * @param string $type Type of notification (follow, connection_request, connection_accepted, connection_rejected)
 * @param string $message Notification message
 * @return bool Success status
 */
function createNotification($recipientId, $senderId, $type, $message) {
    try {
        $conn = getConnection();
        // Enhanced error logging
        error_log("Creating notification: Recipient=$recipientId, Sender=$senderId, Type=$type, Message=$message");
        
        $stmt = $conn->prepare("
            INSERT INTO notifications (user_id, sender_id, type, message, is_read, created_at)
            VALUES (?, ?, ?, ?, 0, NOW())
        ");
        $result = $stmt->execute([$recipientId, $senderId, $type, $message]);
        
        if (!$result) {
            error_log("Failed to execute notification insert: " . implode(', ', $stmt->errorInfo()));
        } else {
            error_log("Notification created successfully with ID: " . $conn->lastInsertId());
        }
        
        return $result;
    } catch (Exception $e) {
        error_log("Error creating notification: " . $e->getMessage());
        error_log("Error trace: " . $e->getTraceAsString());
        return false;
    }
}

/**
 * Get unread notification count for a user
 * 
 * @param int $userId User ID
 * @return int Number of unread notifications
 */
function getUnreadNotificationCount($userId) {
    try {
        $conn = getConnection();
        $stmt = $conn->prepare("
            SELECT COUNT(*) as count 
            FROM notifications 
            WHERE user_id = ? AND is_read = 0
        ");
        $stmt->execute([$userId]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['count'] ?? 0;
    } catch (Exception $e) {
        error_log("Error getting unread notification count: " . $e->getMessage());
        return 0;
    }
}

/**
 * Get unread notifications count for a user
 * 
 * @param int $userId User ID
 * @return int Number of unread notifications
 */
function getUnreadNotificationsCount($userId) {
    try {
        return getUnreadNotificationCount($userId);
    } catch (Exception $e) {
        error_log("Error getting unread notifications count: " . $e->getMessage());
        return 0;
    }
}

/**
 * Get notifications for a user
 *
 * @param int $user_id The user ID
 * @param int $limit Optional limit of results
 * @param int $offset Optional offset for pagination
 * @return array List of notifications
 */
function getNotifications($user_id, $limit = 10, $offset = 0) {
    try {
        $conn = getConnection();
        $stmt = $conn->prepare("
            SELECT n.*, u.full_name as sender_name, u.profile_picture as profile_picture
            FROM notifications n
            LEFT JOIN users u ON n.sender_id = u.user_id
            WHERE n.user_id = ?
            ORDER BY n.created_at DESC
            LIMIT ? OFFSET ?
        ");
        $stmt->execute([$user_id, $limit, $offset]);
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Error getting notifications: " . $e->getMessage());
        return [];
    }
}

/**
 * Mark all notifications as read for a user
 * 
 * @param int $userId User ID
 * @return bool Success status
 */
function markAllNotificationsAsRead($userId) {
    try {
        $conn = getConnection();
        $stmt = $conn->prepare("
            UPDATE notifications 
            SET is_read = 1 
            WHERE user_id = ?
        ");
        return $stmt->execute([$userId]);
    } catch (Exception $e) {
        error_log("Error marking notifications as read: " . $e->getMessage());
        return false;
    }
}

/**
 * Delete a notification
 * 
 * @param int $notificationId Notification ID
 * @param int $userId User ID (for security)
 * @return bool Success status
 */
function deleteNotification($notificationId, $userId) {
    try {
        $conn = getConnection();
        $stmt = $conn->prepare("
            DELETE FROM notifications 
            WHERE id = ? AND user_id = ?
        ");
        return $stmt->execute([$notificationId, $userId]);
    } catch (Exception $e) {
        error_log("Error deleting notification: " . $e->getMessage());
        return false;
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
    
    $user_id = $_SESSION['user_id'];
    $action = $_POST['action'] ?? '';
    $notification_id = $_POST['notification_id'] ?? null;
    
    $result = false;
    $message = '';
    
    switch ($action) {
        case 'mark_read':
            // Mark single notification as read
            try {
                $conn = getConnection();
                $stmt = $conn->prepare("UPDATE notifications SET is_read = 1 WHERE id = ? AND user_id = ?");
                $result = $stmt->execute([$notification_id, $user_id]);
                $message = $result ? 'Notification marked as read' : 'Failed to mark notification as read';
            } catch (Exception $e) {
                error_log("Error marking notification as read: " . $e->getMessage());
                $message = 'Error marking notification as read';
            }
            break;
            
        case 'mark_all_read':
            $result = markAllNotificationsAsRead($user_id);
            $message = $result ? 'All notifications marked as read' : 'Failed to mark notifications as read';
            break;
            
        case 'delete':
            if (!$notification_id) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'Notification ID is required']);
                exit;
            }
            $result = deleteNotification($notification_id, $user_id);
            $message = $result ? 'Notification deleted' : 'Failed to delete notification';
            break;
            
        default:
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Invalid action']);
            exit;
    }
    
    echo json_encode([
        'success' => $result,
        'message' => $message,
        'unread_count' => getUnreadNotificationsCount($user_id)
    ]);
    exit;
}