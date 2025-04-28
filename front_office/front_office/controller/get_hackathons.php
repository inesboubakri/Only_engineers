<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

require_once '../../../back_office/model/db_connect.php';

try {
    $sql = "SELECT * FROM hackathons ORDER BY start_date DESC";
    $stmt = $conn->prepare($sql);
    $stmt->execute();
    $hackathons = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode([
        'success' => true,
        'data' => $hackathons
    ]);
} catch(PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => "Erreur lors de la récupération des hackathons: " . $e->getMessage()
    ]);
}
?>