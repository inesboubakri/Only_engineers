<?php
// Enable error reporting for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Set CORS and JSON headers
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, DELETE, PATCH, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json");

// Handle CORS preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

try {
    // Database connection
    $pdo = new PDO("mysql:host=localhost;dbname=yasmine;charset=utf8mb4", "root", "");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);

    // Get action from GET parameter
    $action = isset($_GET['action']) ? trim($_GET['action']) : '';

    switch ($action) {
        // List comments for a news article
        case 'list':
            $idnews = isset($_GET['idnews']) ? (int)$_GET['idnews'] : 0;
            if ($idnews <= 0) {
                http_response_code(400);
                echo json_encode(['status' => 'error', 'message' => 'Invalid news ID']);
                exit;
            }

            $stmt = $pdo->prepare("SELECT id_comment AS id, content, created_date FROM commentaire WHERE idnews = ? ORDER BY created_date DESC");
            $stmt->execute([$idnews]);
            $comments = $stmt->fetchAll(PDO::FETCH_ASSOC);

            echo json_encode(['status' => 'success', 'data' => $comments]);
            break;

        // Add a new comment
        case 'add':
            // Check if data is sent via POST
            $content = isset($_POST['content']) ? trim($_POST['content']) : '';
            $idnews = isset($_POST['idnews']) ? (int)$_POST['idnews'] : 0;

            if (empty($content) || $idnews <= 0) {
                http_response_code(400);
                echo json_encode(['status' => 'error', 'message' => 'Content or news ID is invalid']);
                exit;
            }

            // Basic content length validation
            if (strlen($content) > 1000) {
                http_response_code(400);
                echo json_encode(['status' => 'error', 'message' => 'Comment content is too long (max 1000 characters)']);
                exit;
            }

            $stmt = $pdo->prepare("INSERT INTO commentaire (content, idnews, created_date) VALUES (?, ?, NOW())");
            $stmt->execute([$content, $idnews]);

            http_response_code(201);
            echo json_encode(['status' => 'success', 'message' => 'Comment added successfully']);
            break;

        // Delete a comment
        case 'delete':
            $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;

            if ($id <= 0) {
                http_response_code(400);
                echo json_encode(['status' => 'error', 'message' => 'Invalid comment ID']);
                exit;
            }

            $stmt = $pdo->prepare("DELETE FROM commentaire WHERE id_comment = ?");
            $result = $stmt->execute([$id]);

            if ($stmt->rowCount() === 0) {
                http_response_code(404);
                echo json_encode(['status' => 'error', 'message' => 'Comment not found']);
                exit;
            }

            echo json_encode(['status' => 'success', 'message' => 'Comment deleted successfully']);
            break;

        // Update a comment
        case 'update': // Changed from 'edit' to match frontend
            $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
            $content = isset($_POST['content']) ? trim($_POST['content']) : '';

            if ($id <= 0 || empty($content)) {
                http_response_code(400);
                echo json_encode(['status' => 'error', 'message' => 'Invalid comment ID or content']);
                exit;
            }

            // Basic content length validation
            if (strlen($content) > 1000) {
                http_response_code(400);
                echo json_encode(['status' => 'error', 'message' => 'Comment content is too long (max 1000 characters)']);
                exit;
            }

            $stmt = $pdo->prepare("UPDATE commentaire SET content = ? WHERE id_comment = ?");
            $result = $stmt->execute([$content, $id]);

            if ($stmt->rowCount() === 0) {
                http_response_code(404);
                echo json_encode(['status' => 'error', 'message' => 'Comment not found']);
                exit;
            }

            echo json_encode(['status' => 'success', 'message' => 'Comment updated successfully']);
            break;

        // Handle invalid or missing action
        default:
            http_response_code(400);
            echo json_encode(['status' => 'error', 'message' => 'Invalid or missing action']);
            break;
    }

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => 'Database error: ' . $e->getMessage()
    ]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => 'Unexpected error: ' . $e->getMessage()
    ]);
}
?>