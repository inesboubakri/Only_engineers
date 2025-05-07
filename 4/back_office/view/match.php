<?php
require_once '../controller/db_connection.php'; // Make sure this returns a PDO instance in $conn

header('Content-Type: application/json');

if (isset($_GET['skills_required'])) {
    $skillsRequired = strtolower($_GET['skills_required']);
    $skillsArray = array_map('trim', explode(',', $skillsRequired));
    $regexPattern = implode("|", array_map('preg_quote', $skillsArray)); // e.g. php|html|css

    try {
        $stmt = $conn->prepare("SELECT full_name, skills FROM users WHERE LOWER(skills) REGEXP :pattern");
        $stmt->bindParam(':pattern', $regexPattern, PDO::PARAM_STR);
        $stmt->execute();

        $matches = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode($matches);
    } catch (PDOException $e) {
        echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
    }
} else {
    echo json_encode(['error' => 'skills_required not set']);
}
