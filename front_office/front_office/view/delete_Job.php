<?php
require_once __DIR__ . '/../../../config.php';

if (isset($_GET['id'])) {
    $id = $_GET['id'];

    try {
        $stmt = config::getConnexion()->prepare("DELETE FROM offre WHERE id = :id");
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        echo "Job deleted successfully.";
    } catch (Exception $e) {
        echo "Error: " . $e->getMessage();
    }
} else {
    echo "No job ID provided.";
}
?>
