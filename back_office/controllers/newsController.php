<?php
// En-têtes CORS
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, DELETE, PATCH, OPTIONS');
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
    // ✅ LECTURE
    case 'GET':
        try {
            $stmt = $pdo->query("SELECT * FROM news ORDER BY created_at DESC");
            $news = $stmt->fetchAll(PDO::FETCH_ASSOC);
            echo json_encode(['success' => true, 'data' => $news]);
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Erreur lecture : ' . $e->getMessage()]);
        }
        break;

    // ✅ AJOUT ou MISE À JOUR
    case 'POST':
        $required = ['title', 'author', 'content', 'category', 'status'];
        foreach ($required as $field) {
            if (empty($_POST[$field])) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => "Champ requis : $field"]);
                exit;
            }
        }

        $id = isset($_POST['idnews']) ? (int)$_POST['idnews'] : null;
        $title = htmlspecialchars(strip_tags($_POST['title']));
        $author = htmlspecialchars(strip_tags($_POST['author']));
        $content = htmlspecialchars($_POST['content']);
        $category = htmlspecialchars(strip_tags($_POST['category']));
        $status = in_array($_POST['status'], ['published', 'draft']) ? $_POST['status'] : 'draft';
        $date = $_POST['created_at'] ?? date('Y-m-d H:i:s');

        // ✅ GESTION IMAGE
        $imagePath = null;
        if (!empty($_FILES['image']['tmp_name']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $allowed = ['image/jpeg', 'image/png', 'image/gif'];
            $mime = mime_content_type($_FILES['image']['tmp_name']);
            $maxSize = 2 * 1024 * 1024;

            if (!in_array($mime, $allowed)) {
                echo json_encode(['success' => false, 'message' => 'Format image non autorisé.']);
                exit;
            }

            if ($_FILES['image']['size'] > $maxSize) {
                echo json_encode(['success' => false, 'message' => 'Image trop lourde (max 2MB).']);
                exit;
            }

            $uploadDir = __DIR__ . '/../uploads/news_images/';
            if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);

            $extension = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
            $imageName = uniqid('news_') . '.' . $extension;
            $destination = $uploadDir . $imageName;

            if (move_uploaded_file($_FILES['image']['tmp_name'], $destination)) {
                $imagePath = 'news_images/' . $imageName;
            } else {
                echo json_encode(['success' => false, 'message' => 'Erreur de sauvegarde de l\'image.']);
                exit;
            }
        }

        try {
            if ($id) {
                // ✅ UPDATE
                $sql = "UPDATE news SET 
                        title = :title, 
                        author = :author, 
                        content = :content, 
                        category = :category, 
                        status = :status, 
                        created_at = :created_at" . 
                        ($imagePath ? ", image = :image" : "") . 
                        " WHERE idnews = :id";

                $params = compact('title', 'author', 'content', 'category', 'status', 'date');
                $params['id'] = $id;
                if ($imagePath) $params['image'] = $imagePath;

                $stmt = $pdo->prepare($sql);
                $stmt->execute($params);

                echo json_encode(['success' => true, 'message' => 'News mise à jour']);
            } else {
                // ✅ INSERT
                $stmt = $pdo->prepare("INSERT INTO news 
                    (title, author, content, category, image, created_at, status) 
                    VALUES (:title, :author, :content, :category, :image, :created_at, :status)");

                $stmt->execute([
                    ':title' => $title,
                    ':author' => $author,
                    ':content' => $content,
                    ':category' => $category,
                    ':image' => $imagePath,
                    ':created_at' => $date,
                    ':status' => $status
                ]);

                echo json_encode(['success' => true, 'message' => 'News ajoutée avec succès']);
            }
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Erreur SQL : ' . $e->getMessage()]);
        }
        break;

    // ✅ SUPPRESSION
    case 'DELETE':
        $id = $_GET['idnews'] ?? null;
        if (!$id) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'ID manquant']);
            exit;
        }

        try {
            $stmt = $pdo->prepare("SELECT image FROM news WHERE idnews = ?");
            $stmt->execute([$id]);
            $news = $stmt->fetch();

            if (!$news) {
                http_response_code(404);
                echo json_encode(['success' => false, 'message' => 'News introuvable']);
                exit;
            }

            $pdo->prepare("DELETE FROM news WHERE idnews = ?")->execute([$id]);

            if (!empty($news['image'])) {
                $imgPath = __DIR__ . '/../uploads/' . $news['image'];
                if (file_exists($imgPath)) unlink($imgPath);
            }

            echo json_encode(['success' => true, 'message' => 'News supprimée']);
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Erreur suppression : ' . $e->getMessage()]);
        }
        break;

    // ✅ BOOKMARK
    case 'PATCH':
        parse_str(file_get_contents("php://input"), $patch);
        $id = $patch['idnews'] ?? null;
        $bookmarked = $patch['bookmarked'] ?? null;

        if (!$id || !in_array($bookmarked, ['0', '1'])) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Données invalides']);
            exit;
        }

        try {
            $stmt = $pdo->prepare("UPDATE news SET bookmarked = :bookmarked WHERE idnews = :id");
            $stmt->execute([':bookmarked' => $bookmarked, ':id' => $id]);
            echo json_encode(['success' => true, 'message' => 'Bookmark mis à jour']);
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Erreur bookmark : ' . $e->getMessage()]);
        }
        break;

    // ❌ Méthode non autorisée
    default:
        http_response_code(405);
        echo json_encode(['success' => false, 'message' => 'Méthode non autorisée']);
        break;
}
