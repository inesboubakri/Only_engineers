<?php
// Configuration des en-têtes CORS
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, DELETE, PATCH, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');
header('Content-Type: application/json');

// Gestion des requêtes OPTIONS (pré-vol)
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Configuration des chemins
$protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https://' : 'http://';
$baseUrl = $protocol . $_SERVER['HTTP_HOST'] . '/yasmine/';  // Correction ici pour le chemin du projet
$uploadRelativePath = 'uploads/news_images/';
$uploadAbsolutePath = __DIR__ . '/../../../' . $uploadRelativePath;  // Correction ici pour le chemin absolu

// Vérification du dossier d'upload
if (!is_dir($uploadAbsolutePath)) {
    if (!mkdir($uploadAbsolutePath, 0755, true)) {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Impossible de créer le dossier d\'upload']);
        exit;
    }
}

if (!is_writable($uploadAbsolutePath)) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Dossier d\'upload non accessible en écriture']);
    exit;
}

// Connexion à la base de données
$host = 'localhost';
$dbname = 'yasmine';  // Base de données corrigée
$user = 'root';
$pass = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Erreur de connexion à la base de données']);
    exit;
}

// Gestion des requêtes
switch ($_SERVER['REQUEST_METHOD']) {
    case 'GET':
        handleGetRequest($pdo, $baseUrl, $uploadRelativePath, $uploadAbsolutePath);
        break;
    case 'POST':
        handlePostRequest($pdo, $uploadRelativePath, $uploadAbsolutePath);
        break;
    case 'DELETE':
        handleDeleteRequest($pdo, $uploadAbsolutePath);
        break;
    default:
        http_response_code(405);
        echo json_encode(['success' => false, 'message' => 'Méthode non autorisée']);
        break;
}

// Fonction de gestion des requêtes GET
function handleGetRequest($pdo, $baseUrl, $uploadRelativePath, $uploadAbsolutePath) {
    try {
        $query = "SELECT n.*, u.full_name 
                  FROM news n 
                  LEFT JOIN users u ON n.author_id = u.iduser 
                  WHERE n.status = 'Published'
                  ORDER BY n.created_at DESC";
        $stmt = $pdo->query($query);
        $news = $stmt->fetchAll();

        // Traitement des images et formatage
        $news = array_map(function($item) use ($baseUrl, $uploadRelativePath, $uploadAbsolutePath) {
            $imageName = basename($item['image'] ?? '');
            if (!empty($imageName) && file_exists($uploadAbsolutePath . $imageName)) {
                $item['image_url'] = $baseUrl . $uploadRelativePath . $imageName;
            } else {
                $item['image_url'] = $baseUrl . 'assets/images/default-news.jpg';
            }

            $item['formatted_date'] = date('d/m/Y H:i', strtotime($item['created_at']));
            return $item;
        }, $news);

        echo json_encode(['success' => true, 'data' => $news, 'count' => count($news)]);
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Erreur de récupération des news']);
    }
}

// Fonction de gestion des requêtes POST (Ajout / Mise à jour)
function handlePostRequest($pdo, $uploadRelativePath, $uploadAbsolutePath) {
    try {
        $data = $_POST ?: json_decode(file_get_contents('php://input'), true);

        if (empty($data)) {
            throw new Exception('Données manquantes');
        }

        $requiredFields = ['title', 'content', 'category', 'status'];
        foreach ($requiredFields as $field) {
            if (empty($data[$field])) {
                throw new Exception("Le champ $field est requis");
            }
        }

        $id = isset($data['idnews']) ? (int)$data['idnews'] : null;
        $title = htmlspecialchars(strip_tags($data['title']));
        $content = htmlspecialchars($data['content']);
        $category = htmlspecialchars(strip_tags($data['category']));
        $status = in_array($data['status'], ['Published', 'Draft']) ? $data['status'] : 'Draft';
        $authorId = isset($data['author_id']) ? (int)$data['author_id'] : null;
        $createdAt = $data['created_at'] ?? date('Y-m-d H:i:s');
        $imagePath = null;

        // Gestion image
        if (!empty($_FILES['image']['tmp_name'])) {
            $allowedTypes = ['image/jpeg', 'image/png', 'image/webp'];
            $fileInfo = finfo_open(FILEINFO_MIME_TYPE);
            $mimeType = finfo_file($fileInfo, $_FILES['image']['tmp_name']);
            finfo_close($fileInfo);

            if (!in_array($mimeType, $allowedTypes)) {
                throw new Exception('Type de fichier non autorisé (JPEG, PNG, WebP)');
            }

            if ($_FILES['image']['size'] > 2 * 1024 * 1024) {
                throw new Exception('La taille de l\'image dépasse 2 Mo');
            }

            $extension = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
            $fileName = 'news_' . time() . '_' . uniqid() . '.' . strtolower($extension);
            $destination = $uploadAbsolutePath . $fileName;

            if (!move_uploaded_file($_FILES['image']['tmp_name'], $destination)) {
                throw new Exception('Échec de l\'upload de l\'image');
            }

            $imagePath = $uploadRelativePath . $fileName;

            // Supprimer l’ancienne image si modification
            if ($id) {
                $stmt = $pdo->prepare("SELECT image FROM news WHERE idnews = ?");
                $stmt->execute([$id]);
                $oldImage = $stmt->fetchColumn();
                if ($oldImage && file_exists($uploadAbsolutePath . basename($oldImage))) {
                    unlink($uploadAbsolutePath . basename($oldImage));
                }
            }
        }

        if ($id) {
            $sql = "UPDATE news SET title = ?, content = ?, category = ?, status = ?, created_at = ?";
            $params = [$title, $content, $category, $status, $createdAt];

            if ($imagePath) {
                $sql .= ", image = ?";
                $params[] = $imagePath;
            }

            $sql .= " WHERE idnews = ?";
            $params[] = $id;

            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
            $message = 'News mise à jour avec succès';
        } else {
            $sql = "INSERT INTO news (title, content, category, image, created_at, status, author_id)
                    VALUES (?, ?, ?, ?, ?, ?, ?)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$title, $content, $category, $imagePath, $createdAt, $status, $authorId]);
            $message = 'News créée avec succès';
        }

        echo json_encode(['success' => true, 'message' => $message]);
    } catch (Exception $e) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
}

// Fonction de suppression
function handleDeleteRequest($pdo, $uploadAbsolutePath) {
    $id = $_GET['idnews'] ?? null;

    if (!$id) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'ID de la news manquant']);
        return;
    }

    try {
        $stmt = $pdo->prepare("SELECT image FROM news WHERE idnews = ?");
        $stmt->execute([$id]);
        $image = $stmt->fetchColumn();

        if ($image && file_exists($uploadAbsolutePath . basename($image))) {
            unlink($uploadAbsolutePath . basename($image));
        }

        $stmt = $pdo->prepare("DELETE FROM news WHERE idnews = ?");
        $stmt->execute([$id]);

        if ($stmt->rowCount() > 0) {
            echo json_encode(['success' => true, 'message' => 'News supprimée avec succès']);
        } else {
            http_response_code(404);
            echo json_encode(['success' => false, 'message' => 'News non trouvée']);
        }
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Erreur lors de la suppression']);
    }
}
