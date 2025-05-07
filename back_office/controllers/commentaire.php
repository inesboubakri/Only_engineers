<?php
// En-têtes CORS
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');
header('Content-Type: application/json');

// OPTIONS : Pré-vol
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Connexion à la BD
$host = 'localhost';
$dbname = 'yasmine';
$user = 'root';
$pass = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Erreur connexion BDD : ' . $e->getMessage()]);
    exit;
}

// ROUTEUR
$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    // ✅ LECTURE (Afficher tous les commentaires)
    case 'GET':
        try {
            $stmt = $pdo->query("SELECT * FROM commentaire ORDER BY created_date DESC");
            $comments = $stmt->fetchAll(PDO::FETCH_ASSOC);
            echo json_encode(['success' => true, 'data' => $comments]);
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Erreur lecture : ' . $e->getMessage()]);
        }
        break;

    // ✅ SUPPRESSION
    case 'DELETE':
        $id = $_GET['id_comment'] ?? null;
        if (!$id) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'ID manquant']);
            exit;
        }

        try {
            $stmt = $pdo->prepare("SELECT * FROM commentaire WHERE id_comment = :id");
            $stmt->execute([':id' => $id]);
            $comment = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$comment) {
                http_response_code(404);
                echo json_encode(['success' => false, 'message' => 'Commentaire introuvable']);
                exit;
            }

            $stmt = $pdo->prepare("DELETE FROM commentaire WHERE id_comment = :id");
            $stmt->execute([':id' => $id]);

            echo json_encode(['success' => true, 'message' => 'Commentaire supprimé']);
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Erreur suppression : ' . $e->getMessage()]);
        }
        break;

    // ❌ Méthode non autorisée
    default:
        http_response_code(405);
        echo json_encode(['success' => false, 'message' => 'Méthode non autorisée']);
        break;
}
?>