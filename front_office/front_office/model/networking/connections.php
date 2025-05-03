<?php
// connections.php - Functions for handling user connections and follows
require_once __DIR__ . '/../db_connection.php';
require_once __DIR__ . '/notifications.php';
require_once __DIR__ . '/follows.php'; // Include follows.php for isFollowing function

/**
 * Send a connection request from one user to another
 * 
 * @param int $senderId User ID of the sender
 * @param int $recipientId User ID of the recipient
 * @return array Response with success status and message
 */
function sendConnectionRequest($senderId, $recipientId) {
    try {
        // Check if users exist
        if ($senderId === $recipientId) {
            return [
                'success' => false,
                'message' => 'You cannot connect with yourself'
            ];
        }
        
        $conn = getConnection();
        
        // Check if the connections table exists
        try {
            $tableCheckStmt = $conn->query("SHOW TABLES LIKE 'networking_connections'");
            if ($tableCheckStmt->rowCount() == 0) {
                error_log("Error: networking_connections table does not exist");
                return [
                    'success' => false,
                    'message' => 'Database schema issue: networking_connections table not found'
                ];
            }
        } catch (Exception $tableEx) {
            error_log("Error checking networking_connections table: " . $tableEx->getMessage());
        }
        
        // Check if a connection request already exists
        $checkStmt = $conn->prepare("
            SELECT * FROM networking_connections
            WHERE (requester_id = ? AND receiver_id = ?) OR (requester_id = ? AND receiver_id = ?)
        ");
        $checkStmt->execute([$senderId, $recipientId, $recipientId, $senderId]);
        $existingConnection = $checkStmt->fetch(PDO::FETCH_ASSOC);
        
        if ($existingConnection) {
            $status = $existingConnection['status'];
            if ($status === 'pending') {
                return [
                    'success' => false,
                    'message' => 'A connection request is already pending'
                ];
            } elseif ($status === 'accepted') {
                return [
                    'success' => false,
                    'message' => 'You are already connected with this user'
                ];
            } elseif ($status === 'rejected') {
                // Allow re-sending after a rejection
                $updateStmt = $conn->prepare("
                    UPDATE networking_connections
                    SET status = 'pending', updated_at = NOW()
                    WHERE (requester_id = ? AND receiver_id = ?) OR (requester_id = ? AND receiver_id = ?)
                ");
                $updateStmt->execute([$senderId, $recipientId, $recipientId, $senderId]);
                
                // Get sender name for notification
                $nameStmt = $conn->prepare("SELECT full_name FROM users WHERE user_id = ?");
                $nameStmt->execute([$senderId]);
                $sender = $nameStmt->fetch(PDO::FETCH_ASSOC);
                $senderName = $sender['full_name'] ?? 'Someone';
                
                // Create notification
                $message = "{$senderName} has sent you a connection request";
                $notifResult = createNotification($recipientId, $senderId, 'connection_request', $message);
                
                if (!$notifResult) {
                    error_log("Failed to create notification for connection request resend");
                }
                
                return [
                    'success' => true,
                    'message' => 'Connection request sent'
                ];
            }
        }
        
        // Create new connection request
        $stmt = $conn->prepare("
            INSERT INTO networking_connections (requester_id, receiver_id, status, created_at, updated_at)
            VALUES (?, ?, 'pending', NOW(), NOW())
        ");
        $result = $stmt->execute([$senderId, $recipientId]);
        
        if (!$result) {
            return [
                'success' => false,
                'message' => 'Failed to send connection request'
            ];
        }
        
        // Get sender name for notification
        $nameStmt = $conn->prepare("SELECT full_name FROM users WHERE user_id = ?");
        $nameStmt->execute([$senderId]);
        $sender = $nameStmt->fetch(PDO::FETCH_ASSOC);
        $senderName = $sender['full_name'] ?? 'Someone';
        
        // Create notification with detailed error logging
        $message = "{$senderName} has sent you a connection request";
        $notifResult = createNotification($recipientId, $senderId, 'connection_request', $message);
        
        if (!$notifResult) {
            error_log("Failed to create notification for new connection request from $senderId to $recipientId");
            // Continue anyway as the connection request was created successfully
        }
        
        return [
            'success' => true,
            'message' => 'Connection request sent'
        ];
        
    } catch (Exception $e) {
        // Enhanced error logging
        error_log("Error sending connection request: " . $e->getMessage());
        error_log("Error trace: " . $e->getTraceAsString());
        return [
            'success' => false,
            'message' => 'An error occurred while sending the connection request: ' . $e->getMessage()
        ];
    }
}

/**
 * Accept a connection request
 * 
 * @param int $userId User ID accepting the request
 * @param int $requesterId User ID who sent the request
 * @return array Response with success status and message
 */
function acceptConnectionRequest($userId, $requesterId) {
    try {
        $conn = getConnection();
        
        // Check if the connection request exists
        $checkStmt = $conn->prepare("
            SELECT * FROM networking_connections
            WHERE (requester_id = ? AND receiver_id = ?) OR (requester_id = ? AND receiver_id = ?)
        ");
        $checkStmt->execute([$requesterId, $userId, $userId, $requesterId]);
        $connection = $checkStmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$connection) {
            return [
                'success' => false,
                'message' => 'No connection request found'
            ];
        }
        
        if ($connection['status'] !== 'pending') {
            return [
                'success' => false,
                'message' => 'This connection request has already been processed'
            ];
        }
        
        // Update the connection status
        $updateStmt = $conn->prepare("
            UPDATE networking_connections
            SET status = 'accepted', updated_at = NOW()
            WHERE (requester_id = ? AND receiver_id = ?) OR (requester_id = ? AND receiver_id = ?)
        ");
        $result = $updateStmt->execute([$requesterId, $userId, $userId, $requesterId]);
        
        if (!$result) {
            return [
                'success' => false,
                'message' => 'Failed to accept connection request'
            ];
        }
        
        // Get user name for notification
        $nameStmt = $conn->prepare("SELECT full_name FROM users WHERE user_id = ?");
        $nameStmt->execute([$userId]);
        $user = $nameStmt->fetch(PDO::FETCH_ASSOC);
        $userName = $user['full_name'] ?? 'Someone';
        
        // Create notification to the requester with detailed error logging
        $message = "{$userName} has accepted your connection request";
        $notifResult = createNotification($requesterId, $userId, 'connection_accepted', $message);
        
        if (!$notifResult) {
            error_log("Failed to create notification for accepted connection from $userId to $requesterId");
            // Continue anyway as the connection was accepted successfully
        }
        
        return [
            'success' => true,
            'message' => 'Connection request accepted'
        ];
        
    } catch (Exception $e) {
        error_log("Error accepting connection request: " . $e->getMessage());
        error_log("Error trace: " . $e->getTraceAsString());
        return [
            'success' => false,
            'message' => 'An error occurred while accepting the connection request'
        ];
    }
}

/**
 * Reject a connection request
 * 
 * @param int $userId User ID rejecting the request
 * @param int $requesterId User ID who sent the request
 * @return array Response with success status and message
 */
function rejectConnectionRequest($userId, $requesterId) {
    try {
        $conn = getConnection();
        
        // Check if the connection request exists
        $checkStmt = $conn->prepare("
            SELECT * FROM networking_connections
            WHERE (requester_id = ? AND receiver_id = ?) OR (requester_id = ? AND receiver_id = ?)
        ");
        $checkStmt->execute([$requesterId, $userId, $userId, $requesterId]);
        $connection = $checkStmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$connection) {
            return [
                'success' => false,
                'message' => 'No connection request found'
            ];
        }
        
        if ($connection['status'] !== 'pending') {
            return [
                'success' => false,
                'message' => 'This connection request has already been processed'
            ];
        }
        
        // Update the connection status
        $updateStmt = $conn->prepare("
            UPDATE networking_connections
            SET status = 'rejected', updated_at = NOW()
            WHERE (requester_id = ? AND receiver_id = ?) OR (requester_id = ? AND receiver_id = ?)
        ");
        $result = $updateStmt->execute([$requesterId, $userId, $userId, $requesterId]);
        
        if (!$result) {
            return [
                'success' => false,
                'message' => 'Failed to reject connection request'
            ];
        }
        
        // Get user name for notification
        $nameStmt = $conn->prepare("SELECT full_name FROM users WHERE user_id = ?");
        $nameStmt->execute([$userId]);
        $user = $nameStmt->fetch(PDO::FETCH_ASSOC);
        $userName = $user['full_name'] ?? 'Someone';
        
        // Create notification to the requester with detailed error logging
        $message = "{$userName} has declined your connection request";
        $notifResult = createNotification($requesterId, $userId, 'connection_rejected', $message);
        
        if (!$notifResult) {
            error_log("Failed to create notification for rejected connection from $userId to $requesterId");
            // Continue anyway as the connection was rejected successfully
        }
        
        return [
            'success' => true,
            'message' => 'Connection request declined'
        ];
        
    } catch (Exception $e) {
        error_log("Error rejecting connection request: " . $e->getMessage());
        error_log("Error trace: " . $e->getTraceAsString());
        return [
            'success' => false,
            'message' => 'An error occurred while declining the connection request'
        ];
    }
}

/**
 * Follow or unfollow a user
 * 
 * @param int $followerId User ID of the follower
 * @param int $followedId User ID being followed
 * @param bool $follow True to follow, false to unfollow
 * @return array Response with success status and message
 */
function toggleFollow($followerId, $followedId, $follow = true) {
    try {
        // Check if users exist and are not the same
        if ($followerId === $followedId) {
            return [
                'success' => false,
                'message' => 'You cannot follow yourself'
            ];
        }
        
        $conn = getConnection();
        
        if ($follow) {
            $result = followUser($followerId, $followedId);
            return [
                'success' => $result,
                'message' => $result ? 'You are now following this user' : 'Failed to follow user',
                'action' => 'followed'
            ];
        } else {
            $result = unfollowUser($followerId, $followedId);
            return [
                'success' => $result,
                'message' => $result ? 'You have unfollowed this user' : 'Failed to unfollow user',
                'action' => 'unfollowed'
            ];
        }
        
    } catch (Exception $e) {
        error_log("Error in follow/unfollow: " . $e->getMessage());
        return [
            'success' => false,
            'message' => 'An error occurred'
        ];
    }
}

/**
 * Check if users are connected
 * 
 * @param int $userId1 First user ID
 * @param int $userId2 Second user ID
 * @return array Connection status information
 */
function getConnectionStatus($userId1, $userId2) {
    try {
        $conn = getConnection();
        $stmt = $conn->prepare("
            SELECT status, requester_id FROM networking_connections
            WHERE (requester_id = ? AND receiver_id = ?) OR (requester_id = ? AND receiver_id = ?)
        ");
        $stmt->execute([$userId1, $userId2, $userId2, $userId1]);
        $connection = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$connection) {
            return [
                'status' => 'none',
                'message' => 'Not connected'
            ];
        }
        
        $status = $connection['status'];
        $requestSent = $connection['requester_id'] == $userId1;
        
        if ($status === 'pending') {
            if ($requestSent) {
                return [
                    'status' => 'pending_sent',
                    'message' => 'Connection request sent'
                ];
            } else {
                return [
                    'status' => 'pending_received',
                    'message' => 'Connection request received'
                ];
            }
        } elseif ($status === 'accepted') {
            return [
                'status' => 'connected',
                'message' => 'Connected'
            ];
        } elseif ($status === 'rejected') {
            if ($requestSent) {
                return [
                    'status' => 'rejected_sent',
                    'message' => 'Connection request declined'
                ];
            } else {
                return [
                    'status' => 'rejected_received',
                    'message' => 'You declined this connection request'
                ];
            }
        }
        
        return [
            'status' => 'unknown',
            'message' => 'Unknown connection status'
        ];
        
    } catch (Exception $e) {
        error_log("Error getting connection status: " . $e->getMessage());
        return [
            'status' => 'error',
            'message' => 'An error occurred'
        ];
    }
}

/**
 * Check if two users are directly connected (returns boolean)
 * 
 * @param int $userId1 First user ID
 * @param int $userId2 Second user ID
 * @return bool True if users are connected, false otherwise
 */
function areConnected($userId1, $userId2) {
    try {
        $conn = getConnection();
        $stmt = $conn->prepare("
            SELECT status FROM networking_connections
            WHERE ((requester_id = ? AND receiver_id = ?) OR (requester_id = ? AND receiver_id = ?))
            AND status = 'accepted'
        ");
        $stmt->execute([$userId1, $userId2, $userId2, $userId1]);
        
        return $stmt->rowCount() > 0;
        
    } catch (Exception $e) {
        error_log("Error checking if users are connected: " . $e->getMessage());
        return false;
    }
}

/**
 * Check if there is a pending connection request between two users
 * 
 * @param int $userId1 First user ID (sender)
 * @param int $userId2 Second user ID (recipient)
 * @return bool True if there is a pending connection request from userId1 to userId2
 */
function isPendingConnection($userId1, $userId2) {
    try {
        $conn = getConnection();
        $stmt = $conn->prepare("
            SELECT status FROM networking_connections
            WHERE requester_id = ? AND receiver_id = ? AND status = 'pending'
        ");
        $stmt->execute([$userId1, $userId2]);
        
        return $stmt->rowCount() > 0;
        
    } catch (Exception $e) {
        error_log("Error checking pending connection: " . $e->getMessage());
        return false;
    }
}

/**
 * Get number of connections for a user
 *
 * @param int $user_id The user ID
 * @return int Number of connections
 */
function getConnectionsCount($user_id) {
    try {
        $conn = getConnection();
        $stmt = $conn->prepare("
            SELECT COUNT(*) as count FROM networking_connections 
            WHERE (requester_id = ? OR receiver_id = ?) 
            AND status = 'accepted'
        ");
        $stmt->execute([$user_id, $user_id]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        return $result['count'] ?? 0;
    } catch (PDOException $e) {
        error_log("Error getting connections count: " . $e->getMessage());
        return 0;
    }
}

// Handle AJAX requests if this file is accessed directly
if ($_SERVER['REQUEST_METHOD'] === 'POST' && basename($_SERVER['PHP_SELF']) === basename(__FILE__)) {
    session_start();
    header('Content-Type: application/json');
    
    // Enhanced debugging for AJAX requests
    error_log("Received POST request to connections.php");
    error_log("POST data: " . json_encode($_POST));
    
    // Check if user is logged in
    if (!isset($_SESSION['user_id'])) {
        http_response_code(401);
        echo json_encode(['success' => false, 'message' => 'User not logged in']);
        exit;
    }
    
    $userId = $_SESSION['user_id'];
    $action = $_POST['action'] ?? '';
    $targetUserId = $_POST['user_id'] ?? null;
    
    error_log("User ID: $userId, Action: $action, Target User ID: $targetUserId");
    
    if (!$targetUserId) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Target user ID is required']);
        exit;
    }
    
    $result = [];
    
    try {
        switch ($action) {
            case 'send_request':
                $result = sendConnectionRequest($userId, $targetUserId);
                break;
                
            case 'accept_request':
                $result = acceptConnectionRequest($userId, $targetUserId);
                break;
                
            case 'reject_request':
                $result = rejectConnectionRequest($userId, $targetUserId);
                break;
                
            case 'follow':
                $result = toggleFollow($userId, $targetUserId, true);
                break;
                
            case 'unfollow':
                $result = toggleFollow($userId, $targetUserId, false);
                break;
                
            case 'check_status':
                $connectionStatus = getConnectionStatus($userId, $targetUserId);
                $isFollowing = isFollowing($userId, $targetUserId);
                $result = [
                    'success' => true,
                    'connection' => $connectionStatus,
                    'following' => $isFollowing
                ];
                break;
                
            default:
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'Invalid action']);
                exit;
        }
    } catch (Exception $e) {
        error_log("Error processing request: " . $e->getMessage());
        $result = [
            'success' => false,
            'message' => 'An error occurred while processing your request: ' . $e->getMessage()
        ];
    }
    
    // Log the result before sending it
    error_log("Response: " . json_encode($result));
    
    echo json_encode($result);
    exit;
}
?>