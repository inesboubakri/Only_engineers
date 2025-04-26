<?php
// Add CORS headers
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json; charset=UTF-8");

require_once '../model/db_connect.php';

function handleImageUpload($file) {
    $targetDir = "../uploads/hackathon_images/";
    if (!file_exists($targetDir)) {
        mkdir($targetDir, 0777, true);
    }

    $fileName = basename($file["name"]);
    $targetFile = $targetDir . time() . '_' . $fileName;
    $imageFileType = strtolower(pathinfo($targetFile, PATHINFO_EXTENSION));

    // Vérifier si c'est une vraie image
    $check = getimagesize($file["tmp_name"]);
    if (!$check) {
        throw new Exception("Le fichier n'est pas une image.");
    }

    // Vérifier la taille (max 5MB)
    if ($file["size"] > 5000000) {
        throw new Exception("Désolé, votre fichier est trop volumineux.");
    }

    // Autoriser certains formats
    if (!in_array($imageFileType, ["jpg", "jpeg", "png", "gif"])) {
        throw new Exception("Désolé, seuls les fichiers JPG, JPEG, PNG & GIF sont autorisés.");
    }

    if (move_uploaded_file($file["tmp_name"], $targetFile)) {
        return time() . '_' . $fileName;
    }

    throw new Exception("Désolé, une erreur s'est produite lors de l'upload.");
}

function getAllHackathons() {
    global $conn;
    try {
        $sql = "SELECT * FROM hackathons ORDER BY start_date DESC";
        $stmt = $conn->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch(PDOException $e) {
        return [];
    }
}

function addHackathon($data, $files) {
    global $conn;
    try {
        $imageName = null;
        if (isset($files['image']) && $files['image']['error'] === UPLOAD_ERR_OK) {
            $imageName = handleImageUpload($files['image']);
        }

        $sql = "INSERT INTO hackathons (name, description, start_date, end_date, start_time, end_time, 
                location, required_skills, organizer, max_participants, image) 
                VALUES (:name, :description, :start_date, :end_date, :start_time, :end_time, 
                :location, :required_skills, :organizer, :max_participants, :image)";
                
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':name', $data['name']);
        $stmt->bindParam(':description', $data['description']);
        $stmt->bindParam(':start_date', $data['start_date']);
        $stmt->bindParam(':end_date', $data['end_date']);
        $stmt->bindParam(':start_time', $data['start_time']);
        $stmt->bindParam(':end_time', $data['end_time']);
        $stmt->bindParam(':location', $data['location']);
        $stmt->bindParam(':required_skills', $data['required_skills']);
        $stmt->bindParam(':organizer', $data['organizer']);
        $stmt->bindParam(':max_participants', $data['max_participants']);
        $stmt->bindParam(':image', $imageName);
        
        if ($stmt->execute()) {
            return [
                'success' => true,
                'message' => 'Hackathon added successfully',
                'id' => $conn->lastInsertId()
            ];
        }
    } catch(Exception $e) {
        return [
            'success' => false,
            'message' => 'Error adding hackathon: ' . $e->getMessage()
        ];
    }
}

function updateHackathon($id, $data, $files = null) {
    global $conn;
    try {
        $imageName = null;
        if (isset($files['image']) && $files['image']['error'] === UPLOAD_ERR_OK) {
            // Gérer l'upload de la nouvelle image
            $imageName = handleImageUpload($files['image']);
            
            // Supprimer l'ancienne image
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
        $stmt->bindParam(':id', $id);
        $stmt->bindParam(':name', $data['name']);
        $stmt->bindParam(':description', $data['description']);
        $stmt->bindParam(':start_date', $data['start_date']);
        $stmt->bindParam(':end_date', $data['end_date']);
        $stmt->bindParam(':start_time', $data['start_time']);
        $stmt->bindParam(':end_time', $data['end_time']);
        $stmt->bindParam(':location', $data['location']);
        $stmt->bindParam(':required_skills', $data['required_skills']);
        $stmt->bindParam(':organizer', $data['organizer']);
        $stmt->bindParam(':max_participants', $data['max_participants']);
        $stmt->bindParam(':prize_pool', $data['prize_pool']);
        
        if ($imageName) {
            $stmt->bindParam(':image', $imageName);
        }
        
        if ($stmt->execute()) {
            return [
                'success' => true,
                'message' => 'Hackathon updated successfully'
            ];
        }
    } catch(PDOException $e) {
        return [
            'success' => false,
            'message' => 'Error updating hackathon: ' . $e->getMessage()
        ];
    }
}

function deleteHackathon($id) {
    global $conn;
    try {
        // Récupérer l'image avant la suppression
        $sql = "SELECT image FROM hackathons WHERE id = :id";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        // Supprimer l'image si elle existe
        if ($result && $result['image']) {
            $imagePath = "../uploads/hackathon_images/" . $result['image'];
            if (file_exists($imagePath)) {
                unlink($imagePath);
            }
        }

        // Supprimer l'enregistrement
        $sql = "DELETE FROM hackathons WHERE id = :id";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':id', $id);
        
        if ($stmt->execute()) {
            return [
                'success' => true,
                'message' => 'Hackathon supprimé avec succès'
            ];
        }
    } catch(PDOException $e) {
        return [
            'success' => false,
            'message' => 'Erreur lors de la suppression du hackathon: ' . $e->getMessage()
        ];
    }
}

// Handle OPTIONS request for CORS
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Handle requests
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    echo json_encode(getAllHackathons());
}
elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = isset($_GET['id']) ? $_GET['id'] : null;
    if ($id) {
        // Update existing hackathon
        $response = updateHackathon($id, $_POST, $_FILES);
    } else {
        // Add new hackathon
        $response = addHackathon($_POST, $_FILES);
    }
    echo json_encode($response);
}
elseif ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
    $id = isset($_GET['id']) ? $_GET['id'] : null;
    if ($id) {
        $response = deleteHackathon($id);
        echo json_encode($response);
    } else {
        echo json_encode(['success' => false, 'message' => 'ID non fourni']);
    }
}
?>