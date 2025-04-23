<?php
header('Content-Type: application/json');
require_once '../../front_office/model/db_connection.php';

try {
    // Récupérer tous les utilisateurs avec leur photo de profil
    $query = "SELECT user_id, full_name, email, profile_completed, created_at, is_admin, position, profile_picture FROM users";
    $result = $conn->query($query);
    
    if (!$result) {
        throw new Exception("Erreur lors de la requête : " . $conn->error);
    }
    
    $users = [];
    while ($row = $result->fetch_assoc()) {
        $users[] = [
            'id' => $row['user_id'],
            'name' => $row['full_name'],
            'email' => $row['email'],
            'status' => $row['profile_completed'] ? 'Complete' : 'Incomplete',
            'created_at' => $row['created_at'],
            'is_admin' => $row['is_admin'],
            'position' => $row['position'],
            'profile_picture' => $row['profile_picture']
        ];
    }
    
    echo json_encode([
        'success' => true,
        'users' => $users
    ]);

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}

$conn->close();
?>