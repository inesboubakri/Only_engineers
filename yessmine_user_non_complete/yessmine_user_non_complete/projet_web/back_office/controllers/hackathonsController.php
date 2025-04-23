<?php
error_reporting(E_ALL);
ini_set('display_errors', 0);

// Set headers first before any potential errors
header("Access-Control-Allow-Origin: http://localhost");
header("Access-Control-Allow-Credentials: true");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json; charset=UTF-8");

// Handle CORS preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

try {
    require_once '../model/session_config.php';
    $conn = getConnection();

    // Handle GET requests (fetch all hackathons)
    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        $stmt = $conn->prepare("SELECT h.*, u.full_name as creator_name, u.is_admin 
                               FROM hackathons h 
                               LEFT JOIN users u ON h.created_by = u.user_id 
                               ORDER BY h.start_date DESC");
        $stmt->execute();
        $hackathons = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode($hackathons);
        exit();
    }
    // Handle POST requests (add or update hackathon)
    elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (!checkAdminAuth()) {
            throw new Exception("Unauthorized: Admin access required");
        }
        
        // Get admin data
        $admin = getCurrentAdmin();
        if (!$admin) {
            throw new Exception("Admin data not found");
        }
        
        $id = isset($_GET['id']) ? $_GET['id'] : null;
        
        if ($id) {
            $response = updateHackathon($id, $_POST, $_FILES);
        } else {
            // Add admin ID to the POST data
            $_POST['created_by'] = $admin['user_id'];
            $response = addHackathon($_POST, $_FILES);
        }
        
        echo json_encode($response);
        exit();
    }
    // Handle DELETE requests
    elseif ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
        if (!checkAdminAuth()) {
            throw new Exception("Unauthorized: Admin access required");
        }

        if (!isset($_GET['id'])) {
            throw new Exception("Hackathon ID not provided");
        }

        $result = deleteHackathon($_GET['id']);
        echo json_encode($result);
        exit();
    }
    else {
        throw new Exception("Method not allowed");
    }
} catch (Exception $e) {
    error_log("Error in hackathonsController: " . $e->getMessage());
    http_response_code(400);
    echo json_encode([
        "success" => false,
        "message" => $e->getMessage()
    ]);
    exit();
}

// Function to send logs to JavaScript console
function console_log($data) {
    echo "<script>console.log(" . json_encode($data) . ");</script>";
}

// Handle image upload
function handleImageUpload($file) {
    $targetDir = "../uploads/hackathon_images/";
    if (!file_exists($targetDir)) {
        mkdir($targetDir, 0777, true);
    }

    $fileName = basename($file["name"]);
    $targetFile = $targetDir . time() . '_' . $fileName;
    $imageFileType = strtolower(pathinfo($targetFile, PATHINFO_EXTENSION));

    // Check if it's a real image
    $check = getimagesize($file["tmp_name"]);
    if (!$check) {
        throw new Exception("Le fichier n'est pas une image.");
    }

    // Check file size (max 5MB)
    if ($file["size"] > 5000000) {
        throw new Exception("Désolé, votre fichier est trop volumineux.");
    }

    // Allow certain file formats
    if (!in_array($imageFileType, ["jpg", "jpeg", "png", "gif"])) {
        throw new Exception("Désolé, seuls les fichiers JPG, JPEG, PNG & GIF sont autorisés.");
    }

    if (move_uploaded_file($file["tmp_name"], $targetFile)) {
        return time() . '_' . $fileName;
    }

    throw new Exception("Désolé, une erreur s'est produite lors de l'upload.");
}

// Get all hackathons
function getAllHackathons() {
    global $conn;
    try {
        $sql = "SELECT h.*, u.full_name as creator_name, u.is_admin 
                FROM hackathons h 
                LEFT JOIN users u ON h.created_by = u.user_id 
                ORDER BY h.start_date DESC";
        $stmt = $conn->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch(PDOException $e) {
        error_log("Error in getAllHackathons: " . $e->getMessage());
        return [];
    }
}

// Add new hackathon
function addHackathon($data, $files) {
    global $conn;
    
    try {
        // Validate admin session
        if (!checkAdminAuth()) {
            throw new Exception("Unauthorized: Admin access required");
        }

        // Validate required data
        if (!isset($data['created_by'])) {
            throw new Exception("Created by ID is required");
        }

        // Handle image upload if present
        $imageName = null;
        if (isset($files['image']) && $files['image']['error'] === UPLOAD_ERR_OK) {
            $imageName = handleImageUpload($files['image']);
        }

        // Begin transaction
        $conn->beginTransaction();

        // Insert hackathon
        $sql = "INSERT INTO hackathons (
            name, description, start_date, end_date, start_time, end_time,
            location, required_skills, organizer, max_participants, prize_pool,
            image, created_by
        ) VALUES (
            :name, :description, :start_date, :end_date, :start_time, :end_time,
            :location, :required_skills, :organizer, :max_participants, :prize_pool,
            :image, :created_by
        )";

        $stmt = $conn->prepare($sql);
        
        $params = [
            ':name' => $data['name'],
            ':description' => $data['description'],
            ':start_date' => $data['start_date'],
            ':end_date' => $data['end_date'],
            ':start_time' => $data['start_time'],
            ':end_time' => $data['end_time'],
            ':location' => $data['location'],
            ':required_skills' => $data['required_skills'],
            ':organizer' => $data['organizer'],
            ':max_participants' => $data['max_participants'],
            ':prize_pool' => $data['prize_pool'],
            ':image' => $imageName,
            ':created_by' => $data['created_by']
        ];

        error_log("Executing hackathon insert with params: " . print_r($params, true));

        if (!$stmt->execute($params)) {
            throw new Exception("Failed to add hackathon: " . implode(", ", $stmt->errorInfo()));
        }

        $newId = $conn->lastInsertId();
        
        // Commit transaction
        $conn->commit();
        
        return [
            'success' => true,
            'message' => 'Hackathon added successfully',
            'id' => $newId,
            'created_by' => $data['created_by']
        ];
    } catch (Exception $e) {
        if ($conn->inTransaction()) {
            $conn->rollBack();
        }
        error_log("Error in addHackathon: " . $e->getMessage());
        throw $e;
    }
}

// Update existing hackathon
function updateHackathon($id, $data, $files = null) {
    global $conn;
    try {
        // Verify user is authenticated
        if (!isset($_SESSION['user_id'])) {
            throw new Exception("User not authenticated");
        }

        // Verify if user has permission to update this hackathon
        $check_sql = "SELECT created_by FROM hackathons WHERE id = ?";
        $check_stmt = $conn->prepare($check_sql);
        $check_stmt->execute([$id]);
        $hackathon = $check_stmt->fetch(PDO::FETCH_ASSOC);

        if (!$hackathon) {
            throw new Exception("Hackathon not found");
        }

        // Check if user is admin or creator
        $user_sql = "SELECT is_admin FROM users WHERE user_id = ?";
        $user_stmt = $conn->prepare($user_sql);
        $user_stmt->execute([$_SESSION['user_id']]);
        $user = $user_stmt->fetch(PDO::FETCH_ASSOC);

        if ($hackathon['created_by'] != $_SESSION['user_id'] && !$user['is_admin']) {
            throw new Exception("Not authorized to update this hackathon");
        }

        // Handle image upload if present
        $imageName = null;
        if (isset($files['image']) && $files['image']['error'] === UPLOAD_ERR_OK) {
            $imageName = handleImageUpload($files['image']);
            
            // Delete old image if new one is uploaded
            $sql = "SELECT image FROM hackathons WHERE id = :id";
            $stmt = $conn->prepare($sql);
            $stmt->bindParam(':id', $id);
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($result && $result['image']) {
                $oldImagePath = "../uploads/hackathon_images/" . $result['image'];
                if (file_exists($oldImagePath)) {
                    unlink($oldImagePath);
                }
            }
        }

        // Prepare SQL query
        $sql = "UPDATE hackathons SET 
                name = :name,
                description = :description,
                start_date = :start_date,
                end_date = :end_date,
                start_time = :start_time,
                end_time = :end_time,
                location = :location,
                required_skills = :required_skills,
                organizer = :organizer,
                max_participants = :max_participants,
                prize_pool = :prize_pool"
                . ($imageName ? ", image = :image" : "") .
                " WHERE id = :id";
                
        $stmt = $conn->prepare($sql);
        
        // Prepare parameters
        $params = [
            ':id' => $id,
            ':name' => $data['name'],
            ':description' => $data['description'],
            ':start_date' => $data['start_date'],
            ':end_date' => $data['end_date'],
            ':start_time' => $data['start_time'],
            ':end_time' => $data['end_time'],
            ':location' => $data['location'],
            ':required_skills' => $data['required_skills'],
            ':organizer' => $data['organizer'],
            ':max_participants' => $data['max_participants'],
            ':prize_pool' => $data['prize_pool']
        ];
        
        if ($imageName) {
            $params[':image'] = $imageName;
        }
        
        // Execute query
        if ($stmt->execute($params)) {
            return [
                'success' => true,
                'message' => 'Hackathon updated successfully'
            ];
        }
        
        throw new Exception("Failed to update hackathon");
    } catch(Exception $e) {
        error_log("Error in updateHackathon: " . $e->getMessage());
        return [
            'success' => false,
            'message' => 'Error updating hackathon: ' . $e->getMessage()
        ];
    }
}

// Delete hackathon
function deleteHackathon($id) {
    global $conn;
    try {
        // Verify user is authenticated
        if (!isset($_SESSION['user_id'])) {
            throw new Exception("User not authenticated");
        }

        // Check if user has permission to delete this hackathon
        $check_sql = "SELECT created_by, image FROM hackathons WHERE id = ?";
        $check_stmt = $conn->prepare($check_sql);
        $check_stmt->execute([$id]);
        $hackathon = $check_stmt->fetch(PDO::FETCH_ASSOC);

        if (!$hackathon) {
            throw new Exception("Hackathon not found");
        }

        // Check if user is admin or creator
        $user_sql = "SELECT is_admin FROM users WHERE user_id = ?";
        $user_stmt = $conn->prepare($user_sql);
        $user_stmt->execute([$_SESSION['user_id']]);
        $user = $user_stmt->fetch(PDO::FETCH_ASSOC);

        if ($hackathon['created_by'] != $_SESSION['user_id'] && !$user['is_admin']) {
            throw new Exception("Not authorized to delete this hackathon");
        }

        // Delete image if it exists
        if ($hackathon['image']) {
            $imagePath = "../uploads/hackathon_images/" . $hackathon['image'];
            if (file_exists($imagePath)) {
                unlink($imagePath);
            }
        }

        // Delete the hackathon
        $sql = "DELETE FROM hackathons WHERE id = :id";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':id', $id);
        
        if ($stmt->execute()) {
            return [
                'success' => true,
                'message' => 'Hackathon supprimé avec succès'
            ];
        }
        
        throw new Exception("Failed to delete hackathon");
    } catch(Exception $e) {
        error_log("Error in deleteHackathon: " . $e->getMessage());
        return [
            'success' => false,
            'message' => 'Error deleting hackathon: ' . $e->getMessage()
        ];
    }
}
?>