<?php
require_once('../../../back_office/controller/db_connection.php');

if (isset($_POST['id']) && isset($_POST['status'])) {
    $stmt = $conn->prepare("UPDATE projet SET status = ? WHERE id = ?");
    $success = $stmt->execute([$_POST['status'], $_POST['id']]);
    
    echo json_encode(['success' => $success]);
} else {
    echo json_encode(['success' => false, 'message' => 'Missing parameters']);
}
?>
