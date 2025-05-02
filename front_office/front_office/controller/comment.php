<?php
// Affiche toutes les erreurs PHP pour le débogage
ini_set('display_errors', 1);
error_reporting(E_ALL);

// En-têtes pour CORS et JSON
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, DELETE, PATCH, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json");

// Traitement principal
try {
    // Connexion à la base de données
    $pdo = new PDO("mysql:host=localhost;dbname=yasmine;charset=utf8", "root", "");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);

    // Récupération de l'action depuis GET
    $action = $_GET['action'] ?? '';

    switch ($action) {
        // 🔽 Lister les commentaires d'une news
        case 'list':
            $idnews = isset($_GET['idnews']) ? (int)$_GET['idnews'] : 0;
            if ($idnews <= 0) {
                http_response_code(400);
                echo json_encode(['status' => 'error', 'message' => 'ID de news invalide']);
                exit;
            }

            $stmt = $pdo->prepare("SELECT id_comment, content, created_date FROM commentaire WHERE idnews = ? ORDER BY created_date DESC");
            $stmt->execute([$idnews]);
            $comments = $stmt->fetchAll(PDO::FETCH_ASSOC);

            echo json_encode(['status' => 'success', 'data' => $comments]);
            break;

        // 🔽 Ajouter un commentaire
        case 'add':
            // Vérifie si les données sont en JSON ou en POST
            $content = trim($_POST['content'] ?? '');
            $idnews = isset($_POST['idnews']) ? (int)$_POST['idnews'] : 0;

            if (!$content || $idnews <= 0) {
                http_response_code(400);
                echo json_encode(['status' => 'error', 'message' => 'Contenu ou ID de news invalide']);
                exit;
            }

            $stmt = $pdo->prepare("INSERT INTO commentaire (content, idnews, created_date) VALUES (?, ?, NOW())");
            $stmt->execute([$content, $idnews]);

            echo json_encode(['status' => 'success', 'message' => 'Commentaire ajouté']);
            break;

        // 🔽 Supprimer un commentaire
        case 'delete':
            $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;

            if ($id <= 0) {
                http_response_code(400);
                echo json_encode(['status' => 'error', 'message' => 'ID invalide']);
                exit;
            }

            $stmt = $pdo->prepare("DELETE FROM commentaire WHERE id_comment = ?");
            $stmt->execute([$id]);

            echo json_encode(['status' => 'success', 'message' => 'Commentaire supprimé']);
            break;

        // 🔽 Modifier un commentaire
        case 'edit':
            $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
            $content = trim($_POST['content'] ?? '');

            if ($id <= 0 || !$content) {
                http_response_code(400);
                echo json_encode(['status' => 'error', 'message' => 'Données de modification invalides']);
                exit;
            }

            $stmt = $pdo->prepare("UPDATE commentaire SET content = ? WHERE id_comment = ?");
            $stmt->execute([$content, $id]);

            echo json_encode(['status' => 'success', 'message' => 'Commentaire modifié']);
            break;

        // ❌ Action invalide ou absente
        default:
            http_response_code(400);
            echo json_encode(['status' => 'error', 'message' => 'Action invalide ou manquante']);
            break;
    }

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => 'Erreur de base de données : ' . $e->getMessage()
    ]);
}
?>
