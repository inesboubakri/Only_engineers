<?php
// En-têtes CORS
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, DELETE, OPTIONS');
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

// Fonction pour échapper les caractères LaTeX
function escapeLatex($string) {
    $replacements = [
        '#' => '\#',
        '$' => '\$',
        '%' => '\%',
        '&' => '\&',
        '_' => '\_',
        '{' => '\{',
        '}' => '\}',
        '~' => '\textasciitilde{}',
        '^' => '\textasciicircum{}',
        '\\' => '\textbackslash{}',
        "\n" => '\\\\',
        '"' => '\"',
        "'" => '\''
    ];
    return str_replace(
        array_keys($replacements),
        array_values($replacements),
        htmlspecialchars($string, ENT_QUOTES, 'UTF-8')
    );
}

// Vérifier si une table existe
function tableExists($pdo, $table) {
    try {
        $stmt = $pdo->query("SELECT 1 FROM $table LIMIT 1");
        return true;
    } catch (PDOException $e) {
        return false;
    }
}

// ROUTEUR
$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? '';

switch ($method) {
    // ✅ LECTURE
    case 'GET':
        if ($action === 'report') {
            // Générer un rapport PDF pour un utilisateur
            $username = $_GET['username'] ?? '';
            if (empty($username)) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'Nom d\'utilisateur requis']);
                exit;
            }

            try {
                // Récupérer les articles et total des vues
                $stmt = $pdo->prepare("
                    SELECT idnews, title, author, content, category, created_at, status, nb_vues, reading_time, jaime,
                           (SELECT COALESCE(SUM(nb_vues), 0) FROM news WHERE author = :author) as total_views,
                           (SELECT COUNT(DISTINCT author) FROM news) as active_authors
                    FROM news WHERE author = :author
                ");
                $stmt->execute([':author' => $username]);
                $articles = $stmt->fetchAll(PDO::FETCH_ASSOC);

                $total_views = $articles[0]['total_views'] ?? 0;
                $active_authors = $articles[0]['active_authors'] ?? 0;

                // Nettoyer les champs supplémentaires
                foreach ($articles as &$article) {
                    unset($article['total_views']);
                    unset($article['active_authors']);
                }

                // Récupérer les commentaires si la table existe
                $comments = [];
                if (tableExists($pdo, 'commentaires')) {
                    $stmt = $pdo->prepare("
                        SELECT c.id_comment, c.content, c.idnews, c.created_date
                        FROM commentaires c
                        JOIN news n ON c.idnews = n.idnews
                        WHERE n.author = :author
                        ORDER BY c.created_date DESC
                    ");
                    $stmt->execute([':author' => $username]);
                    $comments = $stmt->fetchAll(PDO::FETCH_ASSOC);
                }

                $uploadDir = dirname(__DIR__, 2) . '/Uploads/reports/';
                if (!is_dir($uploadDir) && !mkdir($uploadDir, 0755, true)) {
                    http_response_code(500);
                    echo json_encode(['success' => false, 'message' => 'Impossible de créer le répertoire des rapports']);
                    exit;
                }
                if (!is_writable($uploadDir)) {
                    http_response_code(500);
                    echo json_encode(['success' => false, 'message' => 'Le répertoire des rapports n\'est pas accessible en écriture']);
                    exit;
                }

                $baseUrl = 'http://' . $_SERVER['HTTP_HOST'] . dirname($_SERVER['REQUEST_URI'], 3) . '/Uploads/reports/';
                $fileName = 'report_' . uniqid();

                // Générer le document LaTeX
                $latexContent = "\\documentclass[a4paper,12pt]{article}\n";
                $latexContent .= "\\usepackage[utf8]{inputenc}\n";
                $latexContent .= "\\usepackage[T1]{fontenc}\n";
                $latexContent .= "\\usepackage{lmodern}\n";
                $latexContent .= "\\usepackage{geometry}\n";
                $latexContent .= "\\geometry{margin=1in}\n";
                $latexContent .= "\\usepackage{enumitem}\n";
                $latexContent .= "\\usepackage{noto}\n";
                $latexContent .= "\\title{Articles Report for " . escapeLatex($username) . "}\n";
                $latexContent .= "\\author{}\n";
                $latexContent .= "\\date{\\today}\n";
                $latexContent .= "\\begin{document}\n";
                $latexContent .= "\\maketitle\n";

                // Section Utilisateur
                $latexContent .= "\\section*{User Information}\n";
                $latexContent .= "Username: " . escapeLatex($username) . "\\\\\n";
                $latexContent .= "Report Generated: \\today\\\\\n";

                // Section Articles
                $latexContent .= "\\section*{Articles}\n";
                if (empty($articles)) {
                    $latexContent .= "No articles found for this user.\n";
                } else {
                    $latexContent .= "\\begin{itemize}\n";
                    foreach ($articles as $article) {
                        $latexContent .= "\\item\n";
                        $latexContent .= "\\textbf{ID:} " . escapeLatex($article['idnews']) . "\\\\\n";
                        $latexContent .= "\\textbf{Title:} " . escapeLatex(substr($article['title'], 0, 100)) . "\\\\\n";
                        $latexContent .= "\\textbf{Category:} " . escapeLatex($article['category']) . "\\\\\n";
                        $latexContent .= "\\textbf{Created At:} " . escapeLatex(date('Y-m-d H:i:s', strtotime($article['created_at']))) . "\\\\\n";
                        $latexContent .= "\\textbf{Status:} " . escapeLatex($article['status']) . "\\\\\n";
                        $latexContent .= "\\textbf{Views:} " . escapeLatex($article['nb_vues']) . "\\\\\n";
                        $latexContent .= "\\textbf{Reading Time (min):} " . escapeLatex($article['reading_time'] ?? 'N/A') . "\\\\\n";
                        $latexContent .= "\\textbf{Likes:} " . escapeLatex($article['jaime'] ?? 0) . "\n";
                    }
                    $latexContent .= "\\end{itemize}\n";
                }

                // Section Commentaires (seulement si des commentaires existent)
                if (!empty($comments)) {
                    $latexContent .= "\\section*{Comments}\n";
                    $latexContent .= "\\begin{itemize}\n";
                    foreach ($comments as $comment) {
                        $latexContent .= "\\item\n";
                        $latexContent .= "\\textbf{Comment ID:} " . escapeLatex($comment['id_comment']) . "\\\\\n";
                        $latexContent .= "\\textbf{Article ID:} " . escapeLatex($comment['idnews']) . "\\\\\n";
                        $latexContent .= "\\textbf{Content:} " . escapeLatex(substr($comment['content'], 0, 100)) . (strlen($comment['content']) > 100 ? '...' : '') . "\\\\\n";
                        $latexContent .= "\\textbf{Created At:} " . escapeLatex(date('Y-m-d H:i:s', strtotime($comment['created_date']))) . "\n";
                    }
                    $latexContent .= "\\end{itemize}\n";
                }

                // Section Statistiques
                $latexContent .= "\\section*{Statistics}\n";
                $latexContent .= "Total Views: " . escapeLatex($total_views) . "\\\\\n";
                $latexContent .= "Number of Active Authors: " . escapeLatex($active_authors) . "\n";

                $latexContent .= "\\end{document}\n";

                // Sauvegarder le fichier LaTeX
                $latexFile = $uploadDir . $fileName . '.tex';
                if (!file_put_contents($latexFile, $latexContent)) {
                    http_response_code(500);
                    echo json_encode(['success' => false, 'message' => 'Erreur lors de l\'écriture du fichier LaTeX']);
                    exit;
                }

                // Compiler le LaTeX en PDF
                $pdfFile = $uploadDir . $fileName . '.pdf';
                $command = "latexmk -pdf -output-directory=" . escapeshellarg($uploadDir) . " " . escapeshellarg($latexFile) . " 2>&1";
                exec($command, $output, $returnVar);

                // Nettoyer les fichiers temporaires LaTeX
                $tempExtensions = ['.aux', '.log', '.out', '.fls', '.fdb_latexmk'];
                foreach ($tempExtensions as $ext) {
                    $tempFile = $uploadDir . $fileName . $ext;
                    if (file_exists($tempFile)) {
                        unlink($tempFile);
                    }
                }

                if ($returnVar !== 0 || !file_exists($pdfFile)) {
                    http_response_code(500);
                    echo json_encode(['success' => false, 'message' => 'Erreur lors de la compilation du PDF : ' . implode('\n', $output)]);
                    exit;
                }

                $fileUrl = $baseUrl . $fileName . '.pdf';

                echo json_encode([
                    'success' => true,
                    'data' => [
                        'file_url' => $fileUrl,
                        'articles' => $articles,
                        'comments' => $comments,
                        'total_views' => $total_views,
                        'active_authors' => $active_authors
                    ],
                    'message' => 'Report generated successfully'
                ]);
            } catch (Exception $e) {
                http_response_code(500);
                echo json_encode(['success' => false, 'message' => 'Erreur : ' . $e->getMessage()]);
            }
        } elseif ($action === 'stats') {
            // Récupérer les statistiques globales
            try {
                $stmt = $pdo->query("
                    SELECT idnews, title, author, content, category, created_at, status, nb_vues, reading_time, jaime
                    FROM news
                    ORDER BY created_at DESC
                ");
                $news = $stmt->fetchAll(PDO::FETCH_ASSOC);

                $stmt = $pdo->query("
                    SELECT COALESCE(SUM(nb_vues), 0) as total_views,
                           COUNT(DISTINCT author) as active_authors,
                           (SELECT COUNT(*) FROM news WHERE status = 'published') as published,
                           (SELECT COUNT(*) FROM news WHERE status = 'draft') as draft
                    FROM news
                ");
                $stats = $stmt->fetch(PDO::FETCH_ASSOC);

                echo json_encode([
                    'success' => true,
                    'data' => [
                        'news' => $news,
                        'total_views' => $stats['total_views'],
                        'active_authors' => $stats['active_authors'],
                        'published' => $stats['published'],
                        'draft' => $stats['draft']
                    ],
                    'message' => 'Stats retrieved successfully'
                ]);
            } catch (PDOException $e) {
                http_response_code(500);
                echo json_encode(['success' => false, 'message' => 'Erreur SQL : ' . $e->getMessage()]);
            }
        } else {
            try {
                $stmt = $pdo->query("SELECT idnews, title, author, content, category, image, created_at, status, nb_vues, reading_time, jaime FROM news ORDER BY created_at DESC");
                $news = $stmt->fetchAll(PDO::FETCH_ASSOC);
                echo json_encode(['success' => true, 'data' => $news]);
            } catch (PDOException $e) {
                http_response_code(500);
                echo json_encode(['success' => false, 'message' => 'Erreur lecture : ' . $e->getMessage()]);
            }
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
        $created_at = $_POST['created_at'] ?? date('Y-m-d H:i:s');
        $reading_time = isset($_POST['reading_time']) ? (int)$_POST['reading_time'] : 0;
        $jaime = isset($_POST['jaime']) ? (int)$_POST['jaime'] : 0;

        // ✅ GESTION IMAGE
        $imagePath = null;
        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $allowed = ['image/jpeg', 'image/png', 'image/gif'];
            $mime = mime_content_type($_FILES['image']['tmp_name']);
            $maxSize = 2 * 1024 * 1024;

            if (!in_array($mime, $allowed)) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'Format image non autorisé.']);
                exit;
            }

            if ($_FILES['image']['size'] > $maxSize) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'Image trop lourde (max 2MB).']);
                exit;
            }

            $uploadDir = dirname(__DIR__, 2) . '/Uploads/news_images/';
            if (!is_dir($uploadDir) && !mkdir($uploadDir, 0755, true)) {
                http_response_code(500);
                echo json_encode(['success' => false, 'message' => 'Impossible de créer le répertoire des images']);
                exit;
            }

            $extension = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
            $imageName = uniqid('news_') . '.' . $extension;
            $destination = $uploadDir . $imageName;

            if (move_uploaded_file($_FILES['image']['tmp_name'], $destination)) {
                $imagePath = 'news_images/' . $imageName;
            } else {
                http_response_code(500);
                echo json_encode(['success' => false, 'message' => 'Erreur de sauvegarde de l\'image']);
                exit;
            }
        }

        try {
            if ($id) {
                // ✅ UPDATE
                $stmt = $pdo->prepare("SELECT image FROM news WHERE idnews = :id");
                $stmt->execute([':id' => $id]);
                $existingNews = $stmt->fetch(PDO::FETCH_ASSOC);
                $oldImage = $existingNews['image'];

                $sql = "UPDATE news SET 
                        title = :title, 
                        author = :author, 
                        content = :content, 
                        category = :category, 
                        status = :status, 
                        created_at = :created_at,
                        reading_time = :reading_time,
                        jaime = :jaime" . 
                        ($imagePath ? ", image = :image" : "") . 
                        " WHERE idnews = :id";

                $params = [
                    ':title' => $title,
                    ':author' => $author,
                    ':content' => $content,
                    ':category' => $category,
                    ':status' => $status,
                    ':created_at' => $created_at,
                    ':reading_time' => $reading_time,
                    ':jaime' => $jaime,
                    ':id' => $id
                ];
                if ($imagePath) $params[':image'] = $imagePath;

                $stmt = $pdo->prepare($sql);
                $stmt->execute($params);

                // Supprimer l'ancienne image si une nouvelle a été uploadée
                if ($imagePath && $oldImage && file_exists(dirname(__DIR__, 2) . '/Uploads/' . $oldImage)) {
                    unlink(dirname(__DIR__, 2) . '/Uploads/' . $oldImage);
                }

                echo json_encode(['success' => true, 'message' => 'News mise à jour']);
            } else {
                // ✅ INSERT
                $stmt = $pdo->prepare("INSERT INTO news 
                    (title, author, content, category, image, created_at, status, nb_vues, reading_time, jaime) 
                    VALUES (:title, :author, :content, :category, :image, :created_at, :status, 0, :reading_time, :jaime)");

                $stmt->execute([
                    ':title' => $title,
                    ':author' => $author,
                    ':content' => $content,
                    ':category' => $category,
                    ':image' => $imagePath,
                    ':created_at' => $created_at,
                    ':status' => $status,
                    ':reading_time' => $reading_time,
                    ':jaime' => $jaime
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
            $stmt = $pdo->prepare("SELECT image FROM news WHERE idnews = :id");
            $stmt->execute([':id' => $id]);
            $news = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$news) {
                http_response_code(404);
                echo json_encode(['success' => false, 'message' => 'News introuvable']);
                exit;
            }

            $stmt = $pdo->prepare("DELETE FROM news WHERE idnews = :id");
            $stmt->execute([':id' => $id]);

            if (!empty($news['image'])) {
                $imgPath = dirname(__DIR__, 2) . '/Uploads/' . $news['image'];
                if (file_exists($imgPath)) {
                    unlink($imgPath);
                }
            }

            echo json_encode(['success' => true, 'message' => 'News supprimée']);
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