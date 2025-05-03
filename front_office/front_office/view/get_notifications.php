<?php
// Include database connection
require_once '../model/db_connection.php';

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

header('Content-Type: application/json');

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode([
        'success' => false,
        'message' => 'User not logged in'
    ]);
    exit;
}

$user_id = $_SESSION['user_id'];

// Function to get relative time string
function time_elapsed_string($datetime, $full = false) {
    $now = new DateTime;
    $ago = new DateTime($datetime);
    $diff = $now->diff($ago);

    $diff->w = floor($diff->d / 7);
    $diff->d -= $diff->w * 7;

    $string = array(
        'y' => 'year',
        'm' => 'month',
        'w' => 'week',
        'd' => 'day',
        'h' => 'hour',
        'i' => 'minute',
        's' => 'second',
    );
    
    foreach ($string as $k => &$v) {
        if ($diff->$k) {
            $v = $diff->$k . ' ' . $v . ($diff->$k > 1 ? 's' : '');
        } else {
            unset($string[$k]);
        }
    }

    if (!$full) $string = array_slice($string, 0, 1);
    return $string ? implode(', ', $string) . ' ago' : 'just now';
}

try {
    $conn = getConnection();
    
    // Get all notifications for the current user
    $stmt = $conn->prepare("
        SELECT n.id, n.type, n.sender_id, n.user_id, n.message, n.is_read, n.created_at,
               u.full_name, u.profile_picture
        FROM notifications n
        LEFT JOIN users u ON n.sender_id = u.user_id
        WHERE n.user_id = :user_id
        ORDER BY n.created_at DESC
        LIMIT 20
    ");
    
    $stmt->bindParam(':user_id', $user_id);
    $stmt->execute();
    
    $notifications = [];
    
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $sender_image = $row['profile_picture'] ? '../ressources/profile_pictures/' . $row['profile_picture'] : '../ressources/profil.jpg';
        
        $notifications[] = [
            'id' => $row['id'],
            'type' => $row['type'],
            'sender_id' => $row['sender_id'],
            'sender_name' => $row['full_name'],
            'sender_image' => $sender_image,
            'message' => $row['message'],
            'read_status' => $row['is_read'],
            'time_ago' => time_elapsed_string($row['created_at']),
            'created_at' => $row['created_at']
        ];
    }
    
    // Mark notifications as read
    if (!empty($notifications)) {
        $updateStmt = $conn->prepare("
            UPDATE notifications 
            SET is_read = 1 
            WHERE user_id = :user_id AND is_read = 0
        ");
        $updateStmt->bindParam(':user_id', $user_id);
        $updateStmt->execute();
    }
    
    echo json_encode([
        'success' => true,
        'notifications' => $notifications
    ]);
    
} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage()
    ]);
}
?>