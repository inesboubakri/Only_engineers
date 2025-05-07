<?php
// En-têtes CORS
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Connexion à la base de données
$pdo = new PDO("mysql:host=localhost;dbname=yasmine;charset=utf8mb4", 'root', '');
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// Récupération de la méthode HTTP
$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {

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

    case 'POST':
        $required = ['title', 'author', 'content', 'category', 'status'];
        foreach ($required as $field) {
            if (empty($_POST[$field])) {
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
        $date = date('Y-m-d H:i:s');

        // Image facultative
        $imagePath = null;
        if (!empty($_FILES['image']['tmp_name']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $uploadDir = __DIR__ . '/../uploads/news_images/';
            if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);

            $extension = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
            $imageName = uniqid('news_') . '.' . $extension;
            $destination = $uploadDir . $imageName;

            if (move_uploaded_file($_FILES['image']['tmp_name'], $destination)) {
                $imagePath = 'news_images/' . $imageName;
            }
        }

        try {
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

            echo json_encode(['success' => true, 'message' => 'Article ajouté avec succès']);
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Erreur ajout : ' . $e->getMessage()]);
        }
        break;

    default:
        http_response_code(405);
        echo json_encode(['success' => false, 'message' => 'Méthode non autorisée']);
        break;
}
