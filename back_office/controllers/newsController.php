<?php
// Autoriser les requêtes CORS
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, DELETE, PATCH, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');
header('Content-Type: application/json');

// Gérer les pré-requêtes OPTIONS
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Configuration des chemins
$protocol           = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https://' : 'http://';
$baseUrl            = $protocol . $_SERVER['HTTP_HOST'] . '/';
$uploadRelativePath = 'Uploads/news_images/';
$uploadAbsolutePath = realpath(__DIR__ . '/../' . $uploadRelativePath) . '/';

// Vérifier si le dossier existe et est accessible
if (!is_dir($uploadAbsolutePath) || !is_writable($uploadAbsolutePath)) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Dossier d\'upload inaccessible.']);
    exit;
}

// Connexion à la base de données
$host   = 'localhost';
$dbname = 'yasmine';
$user   = 'root';
$pass   = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Erreur de connexion : ' . $e->getMessage()]);
    exit;
}

switch ($_SERVER['REQUEST_METHOD']) {
    case 'GET':
        try {
            $stmt = $pdo->query("SELECT * FROM news ORDER BY created_at DESC");
            $news = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Ajouter l'URL complète pour les images
            $news = array_map(function($item) use ($baseUrl, $uploadRelativePath, $uploadAbsolutePath) {
                if (!empty($item['image'])) {
                    $imagePath = $uploadAbsolutePath . basename($item['image']);
                    $item['image_url'] = file_exists($imagePath) 
                        ? $baseUrl . $uploadRelativePath . basename($item['image']) 
                        : null;
                } else {
                    $item['image_url'] = null;
                }
                return $item;
            }, $news);

            echo json_encode(['success' => true, 'data' => $news]);
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Erreur de récupération : ' . $e->getMessage()]);
        }
        break;

    case 'POST':
        try {
            $data = $_POST ?: json_decode(file_get_contents("php://input"), true);
            if (!$data) {
                throw new Exception("Aucune donnée reçue.");
            }

            foreach (['title', 'author', 'content', 'category', 'status'] as $field) {
                if (empty($data[$field])) {
                    throw new Exception("Le champ '$field' est requis.");
                }
            }

            $id       = isset($data['idnews']) ? (int)$data['idnews'] : null;
            $title    = htmlspecialchars(strip_tags($data['title']));
            $author   = htmlspecialchars(strip_tags($data['author']));
            $content  = htmlspecialchars($data['content']);
            $category = htmlspecialchars(strip_tags($data['category']));
            $status   = in_array($data['status'], ['published', 'draft']) ? $data['status'] : 'draft';
            $date     = $data['created_at'] ?? date('Y-m-d H:i:s');
            $imagePath = null;

            if (!empty($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
                $allowedMimeTypes  = ['image/jpeg', 'image/png'];
                $allowedExtensions = ['jpg', 'jpeg', 'png'];
                $finfo     = finfo_open(FILEINFO_MIME_TYPE);
                $mimeType  = finfo_file($finfo, $_FILES['image']['tmp_name']);
                finfo_close($finfo);
                $extension = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));

                if (!in_array($mimeType, $allowedMimeTypes) || !in_array($extension, $allowedExtensions)) {
                    throw new Exception("Type ou extension d'image non supporté.");
                }

                if ($_FILES['image']['size'] > 2 * 1024 * 1024) {
                    throw new Exception("Image trop volumineuse (max 2 Mo).");
                }

                $imageName   = uniqid('news مجله_') . '.' . $extension;
                $destination = $uploadAbsolutePath . $imageName;

                if (!move_uploaded_file($_FILES['image']['tmp_name'], $destination)) {
                    throw new Exception("Erreur lors de l'upload de l'image.");
                }

                $imagePath = $uploadRelativePath . $imageName;

                if ($id) {
                    $stmt = $pdo->prepare("SELECT image FROM news WHERE idnews = ?");
                    $stmt->execute([$id]);
                    $oldImage = $stmt->fetchColumn();
                    if ($oldImage && file_exists($uploadAbsolutePath . basename($oldImage))) {
                        unlink($uploadAbsolutePath . basename($oldImage));
                    }
                }
            } elseif ($_FILES['image']['error'] !== UPLOAD_ERR_NO_FILE) {
                throw new Exception("Erreur d'upload : code " . $_FILES['image']['error']);
            }

            if ($id) {
                $sql = "UPDATE news 
                        SET title = :title, author = :author, content = :content, 
                            category = :category, status = :status, created_at = :created_at"
                     . ($imagePath ? ", image = :image" : "")
                     . " WHERE idnews = :id";

                $params = [
                    ':title'      => $title,
                    ':author'     => $author,
                    ':content'    => $content,
                    ':category'   => $category,
                    ':status'     => $status,
                    ':created_at' => $date,
                    ':id'         => $id
                ];
                if ($imagePath) {
                    $params[':image'] = $imagePath;
                }

                $stmt = $pdo->prepare($sql);
                $stmt->execute($params);
                $message = "News mise à jour avec succès.";
            } else {
                $stmt = $pdo->prepare("INSERT INTO news 
                    (title, author, content, category, image, created_at, status) 
                    VALUES (:title, :author, :content, :category, :image, :created_at, :status)");
                $stmt->execute([
                    ':title'      => $title,
                    ':author'     => $author,
                    ':content'    => $content,
                    ':category'   => $category,
                    ':image'      => $imagePath,
                    ':created_at' => $date,
                    ':status'     => $status
                ]);
                $message = "News ajoutée avec succès.";
            }

            echo json_encode(['success' => true, 'message' => $message]);
        } catch (Exception $e) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
        break;

    case 'DELETE':
        $id = $_GET['idnews'] ?? null;
        if (!$id) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'ID manquant pour suppression.']);
            exit;
        }

        try {
            $stmt = $pdo->prepare("SELECT image FROM news WHERE idnews = ?");
            $stmt->execute([$id]);
            $image = $stmt->fetchColumn();
            if ($image && file_exists($uploadAbsolutePath . basename($image))) {
                if (!unlink($uploadAbsolutePath . basename($image))) {
                    error_log("Échec de la suppression de l'image : " . $uploadAbsolutePath . basename($image));
                }
            }

            $stmt = $pdo->prepare("DELETE FROM news WHERE idnews = ?");
            $stmt->execute([$id]);

            echo json_encode(['success' => true, 'message' => 'News supprimée avec succès']);
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Erreur de suppression : ' . $e->getMessage()]);
        }
        break;

    default:
        http_response_code(405);
        echo json_encode(['success' => false, 'message' => 'Méthode non autorisée.']);
        break;
}
?>