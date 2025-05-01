<?php
// get_participant_id.php - Retrieves the participant ID for a specific user and hackathon

header('Content-Type: application/json');

// Check if both user_id and hackathon_id are provided
if (!isset($_GET['user_id']) || !isset($_GET['hackathon_id'])) {
    echo json_encode([
        'success' => false,
        'message' => 'Missing required parameters'
    ]);
    exit;
}

require_once '../model/db_connection.php';

try {
    // Get user_id and hackathon_id from request
    $userId = intval($_GET['user_id']);
    $hackathonId = intval($_GET['hackathon_id']);
    
    // Create database connection
    $conn = getConnection();
    
    // Prepare SQL to get participant ID and team name
    $sql = "SELECT id, team_name FROM participants WHERE user_id = :user_id AND hackathon_id = :hackathon_id LIMIT 1";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
    $stmt->bindParam(':hackathon_id', $hackathonId, PDO::PARAM_INT);
    $stmt->execute();
    
    if ($stmt->rowCount() > 0) {
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        // Return participant ID and team name
        echo json_encode([
            'success' => true,
            'participant_id' => $result['id'],
            'team_name' => $result['team_name']
        ]);
    } else {
        // No registration found
        echo json_encode([
            'success' => false,
            'message' => 'No registration found for this user and hackathon'
        ]);
    }
} catch (PDOException $e) {
    // Database error
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage()
    ]);
}
?>