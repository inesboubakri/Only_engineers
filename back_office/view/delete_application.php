<?php
define('BASE_PATH', dirname(__DIR__, 2)); // Points to C:\xampp\htdocs\projet_web\projet_web
require_once BASE_PATH . '/config.php';
require_once BASE_PATH . '/front_office/front_office/controller/controller_apply.php';
if (!isset($_GET['ID'])) {
    echo "No application ID provided.";
    exit();
}

$id = $_GET['ID'];

try {
    $stmt = config::getConnexion()->prepare("DELETE FROM candidature WHERE ID = :ID");
    $stmt->bindParam(':ID', $id, PDO::PARAM_INT);
    $stmt->execute();

    // Redirect back to the list page
    header("Location: jobs.php");
    exit();
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
    exit();
}
?>