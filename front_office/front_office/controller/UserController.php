<?php
// Configuration des en-têtes CORS
header("Access-Control-Allow-Origin: " . ALLOWED_ORIGIN);
header('Access-Control-Allow-Methods: GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');
header('Access-Control-Allow-Credentials: true');
header('Content-Type: application/json');

// Gérer les pré-requêtes OPTIONS
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Charger les configurations
require_once __DIR__ . '/config.php'; // Contient DB_HOST, DB_NAME, DB_USER, DB_PASS, ALLOWED_ORIGIN

// Inclure la gestion de session
session_start();

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Utilisateur non authentifié.']);
    exit;
}

// Gérer les requêtes
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['action']) && $_GET['action'] === 'getUserFullName' && isset($_GET['userId'])) {
    try {
        $userId = (int)$_GET['userId'];

        // Vérifier si l'utilisateur existe
        $stmt = $pdo->prepare("SELECT full_name FROM users WHERE user_id = :user_id");
        $stmt->execute(['user_id' => $userId]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$user) {
            http_response_code(404);
            echo json_encode(['success' => false, 'message' => 'Utilisateur non trouvé.']);
            exit;
        }

        // Renvoyer le full_name
        echo json_encode([
            'success' => true,
            'data' => [
                'user_id' => $userId,
                'full_name' => htmlspecialchars($user['full_name'])
            ]
        ]);
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Erreur lors de la récupération du nom de l\'utilisateur : ' . $e->getMessage()]);
        error_log('Get User Full Name Error: ' . $e->getMessage());
    }
} else {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Requête invalide.']);
}
?>