<?php
session_start();
require_once '../model/db_connection.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    // Redirect to login page if not logged in
    header("Location: ../view/signin.php?redirect=" . urlencode($_SERVER['REQUEST_URI']));
    exit();
}

// Check if hackathon ID is provided
if (!isset($_GET['hackathon_id'])) {
    header("Location: ../view/hackathons.php");
    exit();
}

$hackathonId = intval($_GET['hackathon_id']);
$userId = $_SESSION['user_id'];

try {
    // Connect to database
    $conn = getConnection();
    
    // Check if user is actually registered for this hackathon
    $checkSql = "SELECT * FROM participants WHERE hackathon_id = :hackathon_id AND user_id = :user_id";
    $checkStmt = $conn->prepare($checkSql);
    $checkStmt->bindParam(":hackathon_id", $hackathonId);
    $checkStmt->bindParam(":user_id", $userId);
    $checkStmt->execute();
    
    if ($checkStmt->rowCount() === 0) {
        // User not registered for this hackathon
        header("Location: ../view/hackathon-details.php?id=$hackathonId&error=not_registered");
        exit();
    }
    
    // Get the participant's data to check if they're part of a team
    $participantData = $checkStmt->fetch(PDO::FETCH_ASSOC);
    $participationType = isset($participantData['participation_type']) ? $participantData['participation_type'] : 'individual';
    
    // Début de la transaction pour garantir l'intégrité des données
    $conn->beginTransaction();
    
    if ($participationType === 'team') {
        // Get the team name
        $teamName = isset($participantData['team_name']) ? $participantData['team_name'] : null;
        
        if ($teamName) {
            // Delete all team members associated with this team
            $teamDeleteSql = "DELETE FROM participants WHERE hackathon_id = :hackathon_id AND team_name = :team_name";
            $teamDeleteStmt = $conn->prepare($teamDeleteSql);
            $teamDeleteStmt->bindParam(":hackathon_id", $hackathonId);
            $teamDeleteStmt->bindParam(":team_name", $teamName);
            $teamDeleteStmt->execute();
            
            $deletedCount = $teamDeleteStmt->rowCount();
            
            // Commit transaction
            $conn->commit();
            
            // Registration successfully cancelled
            header("Location: ../view/hackathon-details.php?id=$hackathonId&success=team_cancelled&members=$deletedCount");
            exit();
        } else {
            // Team name not found, fallback to deleting just the user
            $conn->rollBack();
            header("Location: ../view/hackathon-details.php?id=$hackathonId&error=team_name_not_found");
            exit();
        }
    } else {
        // Individual participation, just delete the user's registration
        $deleteSql = "DELETE FROM participants WHERE hackathon_id = :hackathon_id AND user_id = :user_id";
        $deleteStmt = $conn->prepare($deleteSql);
        $deleteStmt->bindParam(":hackathon_id", $hackathonId);
        $deleteStmt->bindParam(":user_id", $userId);
        $deleteStmt->execute();
        
        // Check if deletion was successful
        if ($deleteStmt->rowCount() > 0) {
            // Commit transaction
            $conn->commit();
            
            // Registration successfully cancelled
            header("Location: ../view/hackathon-details.php?id=$hackathonId&success=registration_cancelled");
            exit();
        } else {
            // Failed to cancel registration
            $conn->rollBack();
            header("Location: ../view/hackathon-details.php?id=$hackathonId&error=cancellation_failed");
            exit();
        }
    }
    
} catch(PDOException $e) {
    // Rollback transaction in case of error
    if (isset($conn) && $conn->inTransaction()) {
        $conn->rollBack();
    }
    
    // Log the error for debugging
    error_log("Cancel registration error: " . $e->getMessage());
    
    // Handle database errors
    header("Location: ../view/hackathon-details.php?id=$hackathonId&error=database_error");
    exit();
}
?>