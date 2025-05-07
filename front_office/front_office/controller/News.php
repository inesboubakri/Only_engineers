<?php
// Error logging setup
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/error_log.txt');

// En-têtes CORS
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, X-Requested-With');
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Connexion à la base de données
try {
    $pdo = new PDO("mysql:host=localhost;dbname=yasmine;charset=utf8mb4", 'root', '');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    error_log('Connexion échouée : ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Connexion échouée']);
    exit;
}

// Base URL for images
$base_url = 'http://localhost/controller/';

// Récupération de la méthode HTTP
$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'GET':
        $action = isset($_GET['action']) ? $_GET['action'] : 'list';
        if ($action === 'ping') {
            echo json_encode(['success' => true, 'message' => 'Server is running']);
            exit;
        } elseif ($action === 'increment_views' && isset($_GET['idnews'])) {
            try {
                $idnews = intval($_GET['idnews']);
                $stmt = $pdo->prepare("UPDATE news SET nb_vues = nb_vues + 1 WHERE idnews = :idnews");
                $stmt->execute([':idnews' => $idnews]);
                echo json_encode(['success' => true, 'message' => 'Nombre de vues incrémenté']);
            } catch (PDOException $e) {
                error_log('Erreur incrémentation vues : ' . $e->getMessage());
                http_response_code(500);
                echo json_encode(['success' => false, 'message' => 'Erreur incrémentation vues']);
            }
        } elseif ($action === 'increment_likes' && isset($_GET['idnews'])) {
            try {
                $idnews = intval($_GET['idnews']);
                $stmt = $pdo->prepare("SELECT idnews FROM news WHERE idnews = :idnews");
                $stmt->execute([':idnews' => $idnews]);
                if (!$stmt->fetch()) {
                    http_response_code(404);
                    echo json_encode(['success' => false, 'message' => 'Article non trouvé']);
                    exit;
                }
                $stmt = $pdo->prepare("UPDATE news SET jaime = jaime + 1 WHERE idnews = :idnews");
                $stmt->execute([':idnews' => $idnews]);
                $stmt = $pdo->prepare("SELECT jaime FROM news WHERE idnews = :idnews");
                $stmt->execute([':idnews' => $idnews]);
                $result = $stmt->fetch(PDO::FETCH_ASSOC);
                echo json_encode(['success' => true, 'message' => 'Nombre de Jaime incrémenté', 'jaime' => $result['jaime']]);
            } catch (PDOException $e) {
                error_log('Erreur incrémentation Jaime : ' . $e->getMessage());
                http_response_code(500);
                echo json_encode(['success' => false, 'message' => 'Erreur incrémentation Jaime']);
            }
        } elseif ($action === 'get' && isset($_GET['id'])) {
            try {
                $id = intval($_GET['id']);
                $stmt = $pdo->prepare("SELECT * FROM news WHERE idnews = :id");
                $stmt->execute([':id' => $id]);
                $article = $stmt->fetch(PDO::FETCH_ASSOC);
                if ($article) {
                    if ($article['image']) {
                        $image_path = __DIR__ . '/../Uploads/' . $article['image'];
                        $article['image'] = file_exists($image_path) ? $base_url . $article['image'] : null;
                    }
                    echo json_encode(['success' => true, 'data' => [$article]]);
                } else {
                    http_response_code(404);
                    echo json_encode(['success' => false, 'message' => 'Article non trouvé']);
                }
            } catch (PDOException $e) {
                error_log('Erreur lecture article : ' . $e->getMessage());
                http_response_code(500);
                echo json_encode(['success' => false, 'message' => 'Erreur lecture article']);
            }
        } else {
            try {
                $query = "SELECT * FROM news";
                $params = [];
                $conditions = [];

                // Filtre par catégorie
                if (isset($_GET['articleType']) && !empty($_GET['articleType'])) {
                    $categories = explode(',', $_GET['articleType']);
                    $placeholders = implode(',', array_fill(0, count($categories), '?'));
                    $conditions[] = "category IN ($placeholders)";
                    $params = array_merge($params, $categories);
                }

                // Filtre par statut
                if (isset($_GET['status']) && !empty($_GET['status'])) {
                    $statuses = explode(',', $_GET['status']);
                    $placeholders = implode(',', array_fill(0, count($statuses), '?'));
                    $conditions[] = "status IN ($placeholders)";
                    $params = array_merge($params, $statuses);
                }

                // Filtre par temps de lecture
                if (isset($_GET['readingTime']) && !empty($_GET['readingTime'])) {
                    $readingTimes = explode(',', $_GET['readingTime']);
                    $placeholders = implode(',', array_fill(0, count($readingTimes), '?'));
                    $conditions[] = "Reading_Time IN ($placeholders)";
                    $params = array_merge($params, $readingTimes);
                }

                // Filtre par année de publication
                if (isset($_GET['publicationYear']) && !empty($_GET['publicationYear'])) {
                    $years = explode(',', $_GET['publicationYear']);
                    $yearConditions = array_map(function($year) {
                        return "YEAR(created_at) = ?";
                    }, $years);
                    $conditions[] = "(" . implode(' OR ', $yearConditions) . ")";
                    $params = array_merge($params, $years);
                }

                // Combiner les conditions
                if (!empty($conditions)) {
                    $query .= " WHERE " . implode(' AND ', $conditions);
                }

                // Tri
                $sort = isset($_GET['sort']) && in_array($_GET['sort'], ['created_at', 'nb_vues', 'jaime']) ? $_GET['sort'] : 'created_at';
                $query .= " ORDER BY $sort DESC";

                $stmt = $pdo->prepare($query);
                $stmt->execute($params);
                $news = $stmt->fetchAll(PDO::FETCH_ASSOC);
                foreach ($news as &$article) {
                    if ($article['image']) {
                        $image_path = __DIR__ . '/../Uploads/' . $article['image'];
                        $article['image'] = file_exists($image_path) ? $base_url . $article['image'] : null;
                    }
                }
                unset($article);
                echo json_encode(['success' => true, 'data' => $news]);
            } catch (PDOException $e) {
                error_log('Erreur lecture : ' . $e->getMessage());
                http_response_code(500);
                echo json_encode(['success' => false, 'message' => 'Erreur lecture']);
            }
        }
        break;

    case 'POST':
        $required = ['title', 'author', 'content', 'category', 'Reading_Time', 'status'];
        foreach ($required as $field) {
            if (!isset($_POST[$field]) || trim($_POST[$field]) === '') {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => "Champ requis : $field"]);
                exit;
            }
        }

        $title = htmlspecialchars(strip_tags($_POST['title']));
        $author = htmlspecialchars(strip_tags($_POST['author']));
        $content = htmlspecialchars($_POST['content']);
        $category = htmlspecialchars(strip_tags($_POST['category']));
        $status = in_array($_POST['status'], ['published', 'draft']) ? $_POST['status'] : 'draft';
        $Reading_Time = in_array($_POST['Reading_Time'], ['Under 5 min', '5-10 min', '10-15 min', 'Over 15 min']) ? $_POST['Reading_Time'] : '';
        $date = date('Y-m-d H:i:s');
        $nb_vues = 0;
        $jaime = 0;

        if (empty($Reading_Time)) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => "Valeur invalide pour Reading_Time"]);
            exit;
        }

        $imagePath = null;
        if (!empty($_FILES['image']['tmp_name']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $uploadDir = __DIR__ . '/../Uploads/news_images/';
            if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);

            $extension = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
            $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
            if (!in_array($extension, $allowedExtensions)) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'Extension d\'image non autorisée.']);
                exit;
            }

            $imageName = uniqid('news_') . '.' . $extension;
            $destination = $uploadDir . $imageName;

            if (move_uploaded_file($_FILES['image']['tmp_name'], $destination)) {
                $imagePath = 'news_images/' . $imageName;
            }
        }

        try {
            $stmt = $pdo->prepare("INSERT INTO news 
                (title, author, content, category, image, created_at, status, Reading_Time, nb_vues, jaime) 
                VALUES (:title, :author, :content, :category, :image, :created_at, :status, :reading_time, :nb_vues, :jaime)");

            $stmt->execute([
                ':title' => $title,
                ':author' => $author,
                ':content' => $content,
                ':category' => $category,
                ':image' => $imagePath,
                ':created_at' => $date,
                ':status' => $status,
                ':reading_time' => $Reading_Time,
                ':nb_vues' => $nb_vues,
                ':jaime' => $jaime
            ]);

            echo json_encode(['success' => true, 'message' => 'Article ajouté avec succès']);
        } catch (PDOException $e) {
            error_log('Erreur ajout : ' . $e->getMessage());
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Erreur ajout']);
        }
        break;

    default:
        http_response_code(405);
        echo json_encode(['success' => false, 'message' => 'Méthode non autorisée']);
        break;
}
?>