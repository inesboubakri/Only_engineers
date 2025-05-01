<?php
require_once 'db_connection.php';

function normalizeImagePath($imagePath) {
    if (empty($imagePath)) {
        return null;
    }
    
    // Si le chemin commence déjà par hackathon_images/, on le laisse tel quel
    if (strpos($imagePath, 'hackathon_images/') === 0) {
        return $imagePath;
    }
    
    // Si le chemin contient ressources/hackathon_images/, on extrait juste la partie hackathon_images/
    if (strpos($imagePath, 'ressources/hackathon_images/') !== false) {
        return 'hackathon_images/' . basename($imagePath);
    }
    
    // Sinon, on ajoute simplement hackathon_images/ devant le nom du fichier
    return 'hackathon_images/' . basename($imagePath);
}

function getAllHackathons() {
    $conn = getConnection();
    
    try {
        // First, check if the status column exists
        $tableCheckSql = "SHOW COLUMNS FROM hackathons LIKE 'status'";
        $tableCheckStmt = $conn->prepare($tableCheckSql);
        $tableCheckStmt->execute();
        
        // If status column doesn't exist, add it
        if ($tableCheckStmt->rowCount() == 0) {
            $alterTableSql = "ALTER TABLE hackathons ADD COLUMN status VARCHAR(20) DEFAULT 'approved'";
            $conn->exec($alterTableSql);
        }
        
        // Get all approved hackathons with normalized image paths
        $sql = "SELECT h.*, COUNT(p.id) as participant_count 
                FROM hackathons h
                LEFT JOIN participants p ON h.id = p.hackathon_id
                WHERE h.status = 'approved' OR h.status IS NULL
                GROUP BY h.id
                ORDER BY h.start_date ASC";
                
        $stmt = $conn->prepare($sql);
        $stmt->execute();
        $hackathons = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Normaliser les chemins d'images pour chaque hackathon
        foreach ($hackathons as &$hackathon) {
            if (!empty($hackathon['image'])) {
                $hackathon['image'] = normalizeImagePath($hackathon['image']);
            }
        }
        
        return $hackathons;
    } catch(PDOException $e) {
        error_log("Erreur dans getAllHackathons: " . $e->getMessage());
        return [];
    }
}

function getHackathonById($id) {
    $conn = getConnection();
    
    try {
        // Only get hackathons with status 'approved' or where status is NULL
        $sql = "SELECT h.*, COUNT(p.id) as participant_count 
                FROM hackathons h 
                LEFT JOIN participants p ON h.id = p.hackathon_id 
                WHERE h.id = :id AND (h.status = 'approved' OR h.status IS NULL)
                GROUP BY h.id";
        
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        
        $hackathon = $stmt->fetch(PDO::FETCH_ASSOC);
        return $hackathon;
    } catch(PDOException $e) {
        echo "Error: " . $e->getMessage();
        return null;
    }
}
?>