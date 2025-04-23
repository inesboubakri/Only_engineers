<?php
// Enable error logging to file
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/debug.log');
error_reporting(E_ALL);
ini_set('display_errors', 0);

// Ensure JSON response
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');

// Function to send JSON response
function sendJsonResponse($success, $message = '', $data = [], $stats = null) {
    $response = [
        'success' => $success,
        'message' => $message,
        'data' => $data,
        'stats' => $stats
    ];
    error_log("Sending response: " . print_r($response, true));
    echo json_encode($response);
    exit();
}

try {
    error_log("Starting get_hackathons.php");
    require_once('../model/db_connection.php');
    
    // Get database connection
    error_log("Getting database connection");
    $conn = getConnection();
    if (!$conn) {
        throw new Exception("Database connection failed");
    }
    error_log("Database connection successful");

    // Initialize stats
    $stats = [
        'sources' => ['added-by-me' => 0, 'added-by-others' => 0],
        'prizes' => ['1k-5k' => 0, '5k-10k' => 0, '10k-plus' => 0],
        'durations' => ['24h' => 0, '48h' => 0, '1w' => 0]
    ];

    // Get current user ID from session
    session_start();
    $current_user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;

    // Check if hackathons table exists
    try {
        $result = $conn->query("SHOW TABLES LIKE 'hackathons'");
        if ($result === false || $result->rowCount() == 0) {
            sendJsonResponse(true, 'Database not initialized', [], $stats);
        }
    } catch (PDOException $e) {
        error_log("Error checking table: " . $e->getMessage());
        throw new Exception("Error checking database structure");
    }

    // Fetch hackathons with creator information
    try {
        $query = "SELECT h.*, u.full_name as creator_name, u.is_admin 
                 FROM hackathons h 
                 LEFT JOIN users u ON h.created_by = u.user_id 
                 ORDER BY h.start_date DESC";
        $stmt = $conn->prepare($query);
        $stmt->execute();
        $hackathons = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Error fetching hackathons: " . $e->getMessage());
        throw new Exception("Error retrieving hackathons data");
    }

    if (empty($hackathons)) {
        sendJsonResponse(true, 'No hackathons found', [], $stats);
    }

    // Calculate stats for found hackathons
    foreach ($hackathons as &$hackathon) {
        // Source stats - check if created by current user
        if (isset($hackathon['created_by']) && $current_user_id !== null) {
            if ($hackathon['created_by'] == $current_user_id) {
                $stats['sources']['added-by-me']++;
                $hackathon['is_owner'] = true;
            } else {
                $stats['sources']['added-by-others']++;
                $hackathon['is_owner'] = false;
            }
        } else {
            $stats['sources']['added-by-others']++;
            $hackathon['is_owner'] = false;
        }

        // Prize stats
        $prize = isset($hackathon['prize_pool']) ? (int)$hackathon['prize_pool'] : 0;
        if ($prize >= 1000 && $prize < 5000) {
            $stats['prizes']['1k-5k']++;
        } elseif ($prize >= 5000 && $prize < 10000) {
            $stats['prizes']['5k-10k']++;
        } elseif ($prize >= 10000) {
            $stats['prizes']['10k-plus']++;
        }

        // Duration stats
        try {
            $start = new DateTime($hackathon['start_date']);
            $end = new DateTime($hackathon['end_date']);
            $duration = $start->diff($end)->days;

            if ($duration <= 1) {
                $stats['durations']['24h']++;
            } elseif ($duration <= 2) {
                $stats['durations']['48h']++;
            } else {
                $stats['durations']['1w']++;
            }
        } catch (Exception $e) {
            error_log("Error calculating duration for hackathon {$hackathon['id']}: " . $e->getMessage());
            continue;
        }
    }

    sendJsonResponse(true, '', $hackathons, $stats);

} catch (Exception $e) {
    error_log("Error in get_hackathons.php: " . $e->getMessage());
    error_log("Stack trace: " . $e->getTraceAsString());
    sendJsonResponse(false, 'Error retrieving hackathons: ' . $e->getMessage(), [], null);
} finally {
    if (isset($conn)) {
        $conn = null;
    }
}