<?php
// Start session
session_start();

// Check if user is logged in and is an admin
if (!isset($_SESSION['user_id']) || $_SESSION['is_admin'] != 1) {
    // Redirect to login page if not logged in or not an admin
    header("Location: ../../front_office/front_office/view/signin.php");
    exit();
}

// Include database connection
require_once 'db_connectionback.php';

// Check if hackathon ID is provided
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: ../view/hackathons.php?success=0&message=No hackathon ID provided");
    exit();
}

$hackathonId = $_GET['id'];

try {
    // Get database connection
    $conn = getConnection();
    
    if (!$conn) {
        throw new Exception("Failed to connect to database");
    }
    
    // Begin transaction
    $conn->beginTransaction();
    
    // First, check if the hackathon exists and get its image path if any
    $stmt = $conn->prepare("SELECT image FROM hackathons WHERE id = :id");
    $stmt->bindParam(':id', $hackathonId);
    $stmt->execute();
    
    $hackathon = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$hackathon) {
        throw new Exception("Hackathon not found");
    }
    
    // Delete hackathon from the database
    $stmt = $conn->prepare("DELETE FROM hackathons WHERE id = :id");
    $stmt->bindParam(':id', $hackathonId);
    
    if ($stmt->execute()) {
        // If successful, commit the transaction
        $conn->commit();
        
        // Delete the image file if it exists
        if (!empty($hackathon['image'])) {
            $imagePath = '../../front_office/front_office/ressources/' . $hackathon['image'];
            if (file_exists($imagePath)) {
                unlink($imagePath);
            }
        }
        
        header("Location: ../view/hackathons.php?success=1&message=Hackathon deleted successfully");
        exit();
    } else {
        // If failed, rollback the transaction
        $conn->rollBack();
        header("Location: ../view/hackathons.php?success=0&message=Failed to delete hackathon");
        exit();
    }
} catch(Exception $e) {
    // If an exception occurs, rollback the transaction
    if (isset($conn)) {
        $conn->rollBack();
    }
    
    header("Location: ../view/hackathons.php?success=0&message=" . urlencode($e->getMessage()));
    exit();
}
?>