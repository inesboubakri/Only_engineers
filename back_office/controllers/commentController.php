<?php
session_start();
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

// CONFIGURATION
$config = [
    'host'    => 'localhost',
    'dbname'  => 'yasmine',
    'charset' => 'utf8mb4',
    'user'    => 'root',
    'pass'    => ''
];

try {
    // Connexion PDO
    $pdo = new PDO(
        "mysql:host={$config['host']};dbname={$config['dbname']};charset={$config['charset']}",
        $config['user'],
        $config['pass'],
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );

    // Réponse par défaut
    $response = ['success' => false];

    // OPTIONS pré-vol CORS
    if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
        http_response_code(200);
        exit;
    }

    // On récupère action et données
    $action = $_GET['action'] ?? '';
    $input  = json_decode(file_get_contents('php://input'), true) ?: $_POST;

    switch ($_SERVER['REQUEST_METHOD']) {

        // Récupérer tous les commentaires d’une news
        case 'GET':
            if ($action !== 'getCommentaires') {
                throw new Exception('Action GET invalide.');
            }
            $idnews = filter_input(INPUT_GET, 'idnews', FILTER_VALIDATE_INT);
            if (!$idnews) {
                throw new Exception('Le paramètre idnews est requis.');
            }

            $stmt = $pdo->prepare("
                SELECT c.id_comment, c.content, c.idnews, c.user_id, u.username, c.created_date
                FROM comments AS c
                LEFT JOIN users AS u ON c.user_id = u.user_id
                WHERE c.idnews = :idnews
                ORDER BY c.created_date DESC
            ");
            $stmt->execute([':idnews' => $idnews]);
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $response = [
                'success' => true,
                'data'    => $rows
            ];
            break;

        // Ajouter un commentaire
        case 'POST':
            if ($action !== 'addCommentaire') {
                throw new Exception('Action POST invalide.');
            }
            foreach (['content','idnews','user_id'] as $f) {
                if (empty($input[$f])) {
                    throw new Exception("Le champ '$f' est requis.");
                }
            }
            $stmt = $pdo->prepare("
                INSERT INTO comments (content, idnews, user_id, created_date)
                VALUES (:content, :idnews, :user_id, NOW())
            ");
            $stmt->execute([
                ':content' => htmlspecialchars($input['content'], ENT_QUOTES, 'UTF-8'),
                ':idnews'  => (int)$input['idnews'],
                ':user_id' => (int)$input['user_id']
            ]);
            $response = [
                'success'    => true,
                'id_comment' => $pdo->lastInsertId(),
                'message'    => 'Commentaire ajouté.'
            ];
            break;

        // Modifier un commentaire
        case 'PUT':
            if ($action !== 'editCommentaire') {
                throw new Exception('Action PUT invalide.');
            }
            foreach (['id_comment','content','user_id'] as $f) {
                if (empty($input[$f])) {
                    throw new Exception("Le champ '$f' est requis.");
                }
            }
            // Vérifier appartenance
            $stmt = $pdo->prepare("SELECT user_id FROM comments WHERE id_comment = :id");
            $stmt->execute([':id' => (int)$input['id_comment']]);
            $owner = $stmt->fetchColumn();
            if (!$owner) {
                throw new Exception('Commentaire introuvable.');
            }
            if ((int)$owner !== (int)$input['user_id']) {
                throw new Exception('Non autorisé.');
            }
            // MAJ
            $stmt = $pdo->prepare("
                UPDATE comments
                SET content = :content
                WHERE id_comment = :id
            ");
            $stmt->execute([
                ':content' => htmlspecialchars($input['content'], ENT_QUOTES, 'UTF-8'),
                ':id'      => (int)$input['id_comment']
            ]);
            $response = [
                'success' => true,
                'message' => 'Commentaire modifié.'
            ];
            break;

        // Supprimer un commentaire
        case 'DELETE':
            if ($action !== 'deleteCommentaire') {
                throw new Exception('Action DELETE invalide.');
            }
            parse_str(file_get_contents('php://input'), $params);
            $idc = filter_var($params['id_comment'] ?? null, FILTER_VALIDATE_INT);
            $uid = filter_var($params['user_id']    ?? null, FILTER_VALIDATE_INT);
            if (!$idc || !$uid) {
                throw new Exception('Paramètres id_comment et user_id requis.');
            }
            // Vérifier appartenance
            $stmt = $pdo->prepare("SELECT user_id FROM comments WHERE id_comment = :id");
            $stmt->execute([':id' => $idc]);
            $owner = $stmt->fetchColumn();
            if (!$owner) {
                throw new Exception('Commentaire introuvable.');
            }
            if ((int)$owner !== $uid) {
                throw new Exception('Non autorisé.');
            }
            // Suppression
            $stmt = $pdo->prepare("DELETE FROM comments WHERE id_comment = :id");
            $stmt->execute([':id' => $idc]);
            $response = [
                'success' => true,
                'message' => 'Commentaire supprimé.'
            ];
            break;

        default:
            throw new Exception('Méthode HTTP non prise en charge.');
    }

    echo json_encode($response);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Erreur BD : ' . $e->getMessage()
    ]);
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
