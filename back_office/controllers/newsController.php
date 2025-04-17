<?php
// Ajout des en-têtes CORS pour autoriser les requêtes Cross-Origin
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, DELETE, PATCH, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Gestion de la requête OPTIONS préliminaire
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

header('Content-Type: application/json');

// Connexion à la base de données
$host = 'localhost';
$dbname = 'yasmine';
$user = 'root';
$pass = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Erreur de connexion : ' . $e->getMessage()]);
    exit;
}

switch ($_SERVER['REQUEST_METHOD']) {

    // GET: Récupérer toutes les news
    case 'GET':
        try {
            $stmt = $pdo->query("SELECT * FROM news ORDER BY created_at DESC");
            $news = $stmt->fetchAll(PDO::FETCH_ASSOC);
            if ($news) {
                echo json_encode(['success' => true, 'data' => $news]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Aucune news trouvée.']);
            }
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Erreur lors de la récupération : ' . $e->getMessage()]);
        }
        break;

    // POST: Ajouter ou mettre à jour une news
    case 'POST':
        try {
            // Validation des champs obligatoires
            $requiredFields = ['title', 'author', 'content', 'category', 'status'];
            foreach ($requiredFields as $field) {
                if (empty($_POST[$field])) {
                    throw new Exception("Le champ '$field' est requis.");
                }
            }

            $id = isset($_POST['idnews']) ? (int)$_POST['idnews'] : null;
            $title = htmlspecialchars(strip_tags($_POST['title']));
            $author = htmlspecialchars(strip_tags($_POST['author']));
            $content = htmlspecialchars($_POST['content']);
            $category = htmlspecialchars(strip_tags($_POST['category']));
            $status = in_array($_POST['status'], ['published', 'draft']) ? $_POST['status'] : 'draft';
            $date = $_POST['created_at'] ?? date('Y-m-d H:i:s');

            $imagePath = null;
            if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
                $uploadDir = __DIR__ . '/../uploads/news_images/';
                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0755, true);
                }

                $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
                $fileInfo = finfo_open(FILEINFO_MIME_TYPE);
                $mimeType = finfo_file($fileInfo, $_FILES['image']['tmp_name']);
                finfo_close($fileInfo);

                if (in_array($mimeType, $allowedTypes)) {
                    $extension = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
                    $imageName = uniqid('news_') . '.' . $extension;
                    $destination = $uploadDir . $imageName;

                    if (move_uploaded_file($_FILES['image']['tmp_name'], $destination)) {
                        $imagePath = 'news_images/' . $imageName;
                    } else {
                        throw new Exception("Erreur lors de l'upload de l'image.");
                    }
                } else {
                    throw new Exception("Type d'image non supporté.");
                }
            }

            if ($id) {
                // Mise à jour
                $sql = "UPDATE news SET 
                        title = :title, 
                        author = :author, 
                        content = :content, 
                        category = :category, 
                        status = :status, 
                        created_at = :created_at" .
                        ($imagePath ? ", image = :image" : "") . 
                        " WHERE idnews = :id";
                $stmt = $pdo->prepare($sql);
                $params = [
                    ':title' => $title,
                    ':author' => $author,
                    ':content' => $content,
                    ':category' => $category,
                    ':status' => $status,
                    ':created_at' => $date,
                    ':id' => $id
                ];
                if ($imagePath) {
                    $params[':image'] = $imagePath;
                }
                $stmt->execute($params);
                $message = "News mise à jour avec succès.";
            } else {
                // Ajout
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
                $message = "News ajoutée avec succès.";
            }

            echo json_encode(['success' => true, 'message' => $message]);
        } catch (Exception $e) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
        break;

    // DELETE: Supprimer une news
    case 'DELETE':
        $id = $_GET['idnews'] ?? null;

        if (!$id) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'ID manquant pour suppression.']);
            exit;
        }

        try {
            $stmt = $pdo->prepare("DELETE FROM news WHERE idnews = ?");
            $stmt->execute([$id]);
            echo json_encode(['success' => true, 'message' => 'News supprimée avec succès']);
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Erreur lors de la suppression : ' . $e->getMessage()]);
        }
        break;

    default:
        http_response_code(405);
        echo json_encode(['success' => false, 'message' => 'Méthode non autorisée.']);
        break;
}
?>
