<?php
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, DELETE, PATCH, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

try {
    $pdo = new PDO('mysql:host=localhost;dbname=yasmine;charset=utf8mb4', 'root', '');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Erreur de connexion : ' . $e->getMessage()]);
    exit;
}

switch ($_SERVER['REQUEST_METHOD']) {
    case 'GET':
        $stmt = $pdo->query("SELECT * FROM commentaire ORDER BY created_date DESC");
        $comments = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode(['success' => true, 'data' => $comments]);
        break;

    case 'POST':
        $data = json_decode(file_get_contents("php://input"), true);

        if (empty($data['content']) || empty($data['idnews'])) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Champs requis manquants.']);
            exit;
        }

        $stmt = $pdo->prepare("INSERT INTO commentaire (content, idnews, created_date) VALUES (:content, :idnews, NOW())");
        $stmt->execute([
            ':content' => $data['content'],
            ':idnews'  => $data['idnews']
        ]);

        echo json_encode(['success' => true, 'message' => 'Commentaire ajouté avec succès.']);
        break;

    case 'PATCH':
        $data = json_decode(file_get_contents("php://input"), true);

        if (empty($data['id_comment']) || empty($data['content'])) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'ID ou contenu manquant.']);
            exit;
        }

        $stmt = $pdo->prepare("UPDATE commentaire SET content = :content WHERE id_comment = :id");
        $stmt->execute([
            ':content' => $data['content'],
            ':id' => $data['id_comment']
        ]);

        echo json_encode(['success' => true, 'message' => 'Commentaire mis à jour.']);
        break;

    case 'DELETE':
        $id = $_GET['id'] ?? null;
        if (!$id) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'ID du commentaire manquant.']);
            exit;
        }

        $stmt = $pdo->prepare("DELETE FROM commentaire WHERE id_comment = ?");
        $stmt->execute([$id]);

        echo json_encode(['success' => true, 'message' => 'Commentaire supprimé.']);
        break;

    default:
        http_response_code(405);
        echo json_encode(['success' => false, 'message' => 'Méthode non autorisée.']);
        break;
}
?>
