<?php
// This file handles the registration of both individual participants and teams
// for hackathons in the participants table

require_once 'db_connection.php';

/**
 * Register an individual participant for a hackathon
 * 
 * @param int $userId User ID of the participant
 * @param int $hackathonId ID of the hackathon
 * @param string $fullName Full name of the participant
 * @param string $email Email of the participant
 * @param string $phone Phone number of the participant
 * @param string $photoPath Path to the uploaded photo
 * @return array Result with success status and message/id
 */
function registerIndividualParticipant($userId, $hackathonId, $fullName, $email, $phone, $photoPath = null) {
    try {
        $conn = getConnection();
        
        // Check if participant is already registered for this hackathon
        $checkSql = "SELECT * FROM participants WHERE user_id = :user_id AND hackathon_id = :hackathon_id";
        $checkStmt = $conn->prepare($checkSql);
        $checkStmt->bindParam(':user_id', $userId);
        $checkStmt->bindParam(':hackathon_id', $hackathonId);
        $checkStmt->execute();
        
        if ($checkStmt->rowCount() > 0) {
            return [
                'success' => false,
                'message' => 'You are already registered for this hackathon'
            ];
        }
        
        // Insert participant data - include photo column
        $sql = "INSERT INTO participants (user_id, hackathon_id, participation_type, full_name, email, phone, photo, registration_date) 
                VALUES (:user_id, :hackathon_id, 'individual', :full_name, :email, :phone, :photo, NOW())";
        
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':user_id', $userId);
        $stmt->bindParam(':hackathon_id', $hackathonId);
        $stmt->bindParam(':full_name', $fullName);
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':phone', $phone);
        $stmt->bindParam(':photo', $photoPath);
        
        $stmt->execute();
        $participantId = $conn->lastInsertId();
        
        return [
            'success' => true,
            'id' => $participantId,
            'message' => 'Individual registration successful'
        ];
    } catch (PDOException $e) {
        return [
            'success' => false,
            'message' => 'Database error: ' . $e->getMessage()
        ];
    }
}

/**
 * Register a team for a hackathon
 * 
 * @param int $userId User ID of the team leader
 * @param int $hackathonId ID of the hackathon
 * @param string $teamLeaderName Full name of the team leader
 * @param string $teamLeaderEmail Email of the team leader
 * @param string $teamLeaderPhone Phone number of the team leader
 * @param array $teamMembers Array of team member data (each containing name, email, phone, photo)
 * @param string $leaderPhotoPath Path to the team leader's photo
 * @return array Result with success status and message/id
 */
function registerTeam($userId, $hackathonId, $teamLeaderName, $teamLeaderEmail, $teamLeaderPhone, $teamMembers, $leaderPhotoPath = null) {
    try {
        $conn = getConnection();
        
        // Begin transaction for team registration
        $conn->beginTransaction();
        
        // Check if user is already registered for this hackathon
        $checkSql = "SELECT * FROM participants WHERE user_id = :user_id AND hackathon_id = :hackathon_id";
        $checkStmt = $conn->prepare($checkSql);
        $checkStmt->bindParam(':user_id', $userId);
        $checkStmt->bindParam(':hackathon_id', $hackathonId);
        $checkStmt->execute();
        
        if ($checkStmt->rowCount() > 0) {
            return [
                'success' => false,
                'message' => 'You are already registered for this hackathon'
            ];
        }
        
        // Generate a team unique identifier
        $teamId = 'team_' . uniqid();
        
        // Insert team leader data
        $leaderSql = "INSERT INTO participants (user_id, hackathon_id, participation_type, full_name, email, phone, photo, team_id, role, registration_date) 
                      VALUES (:user_id, :hackathon_id, 'team', :full_name, :email, :phone, :photo, :team_id, 'team_leader', NOW())";
        
        $leaderStmt = $conn->prepare($leaderSql);
        $leaderStmt->bindParam(':user_id', $userId);
        $leaderStmt->bindParam(':hackathon_id', $hackathonId);
        $leaderStmt->bindParam(':full_name', $teamLeaderName);
        $leaderStmt->bindParam(':email', $teamLeaderEmail);
        $leaderStmt->bindParam(':phone', $teamLeaderPhone);
        $leaderStmt->bindParam(':photo', $leaderPhotoPath);
        $leaderStmt->bindParam(':team_id', $teamId);
        
        $leaderStmt->execute();
        $teamLeaderId = $conn->lastInsertId();
        
        // Insert team members data
        foreach ($teamMembers as $member) {
            $memberSql = "INSERT INTO participants (user_id, hackathon_id, participation_type, full_name, email, phone, photo, team_id, role, registration_date) 
                         VALUES (:user_id, :hackathon_id, 'team', :full_name, :email, :phone, :photo, :team_id, 'team_member', NOW())";
            
            $memberStmt = $conn->prepare($memberSql);
            $memberStmt->bindParam(':user_id', $userId); // Same user_id as team leader
            $memberStmt->bindParam(':hackathon_id', $hackathonId);
            $memberStmt->bindParam(':full_name', $member['name']);
            $memberStmt->bindParam(':email', $member['email']);
            $memberStmt->bindParam(':phone', $member['phone']);
            $memberStmt->bindParam(':photo', $member['photo']);
            $memberStmt->bindParam(':team_id', $teamId);
            
            $memberStmt->execute();
        }
        
        // Commit transaction
        $conn->commit();
        
        return [
            'success' => true,
            'id' => $teamLeaderId,
            'team_id' => $teamId,
            'message' => 'Team registration successful'
        ];
    } catch (PDOException $e) {
        // Roll back transaction on error
        if ($conn->inTransaction()) {
            $conn->rollBack();
        }
        
        return [
            'success' => false,
            'message' => 'Database error: ' . $e->getMessage()
        ];
    }
}

/**
 * Upload participant photo
 * 
 * @param array $photoFile $_FILES['photo'] array
 * @param string $participantType 'individual' or 'team'
 * @param string $identifier Optional identifier (user_id, team_name, etc.) to include in filename
 * @return array Result with success status and file path
 */
function uploadParticipantPhoto($photoFile, $participantType, $identifier = '') {
    // Create directory for photos if it doesn't exist
    $baseDir = '../../ressources/';
    $uploadDir = $participantType === 'team' ? 'temp_uploads/team_photos/' : 'participant_photos/';
    $fullDir = $baseDir . $uploadDir;
    
    if (!file_exists($fullDir)) {
        if (!mkdir($fullDir, 0777, true)) {
            return [
                'success' => false,
                'message' => 'Failed to create upload directory'
            ];
        }
    }
    
    // Generate unique filename with identifier for better organization
    $fileExtension = pathinfo($photoFile['name'], PATHINFO_EXTENSION);
    $fileName = ($identifier ? $identifier . '_' : '') . time() . '_' . uniqid() . '.' . $fileExtension;
    $targetPath = $fullDir . $fileName;
    $relativePath = $uploadDir . $fileName;
    
    // Check file type
    $allowedTypes = ['image/jpeg', 'image/png', 'image/jpg', 'image/gif'];
    if (!in_array($photoFile['type'], $allowedTypes)) {
        return [
            'success' => false,
            'message' => 'Only JPG, JPEG, PNG & GIF files are allowed'
        ];
    }
    
    // Check file size (5MB max)
    if ($photoFile['size'] > 5 * 1024 * 1024) {
        return [
            'success' => false,
            'message' => 'File size must be less than 5MB'
        ];
    }
    
    // Upload file
    if (move_uploaded_file($photoFile['tmp_name'], $targetPath)) {
        return [
            'success' => true,
            'path' => $relativePath
        ];
    } else {
        return [
            'success' => false,
            'message' => 'Failed to upload file'
        ];
    }
}

/**
 * Get participant information by ID
 * 
 * @param int $participantId ID of the participant
 * @return array|false Participant data or false if not found
 */
function getParticipantById($participantId) {
    try {
        $conn = getConnection();
        $sql = "SELECT * FROM participants WHERE participant_id = :participant_id";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':participant_id', $participantId);
        $stmt->execute();
        
        if ($stmt->rowCount() > 0) {
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } else {
            return false;
        }
    } catch (PDOException $e) {
        return false;
    }
}

/**
 * Get all participants for a hackathon
 * 
 * @param int $hackathonId ID of the hackathon
 * @return array Array of participant data
 */
function getHackathonParticipants($hackathonId) {
    try {
        $conn = getConnection();
        $sql = "SELECT * FROM participants WHERE hackathon_id = :hackathon_id";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':hackathon_id', $hackathonId);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        return [];
    }
}

/**
 * Update participant information
 * 
 * @param int $participantId ID of the participant
 * @param array $data Associative array of data to update
 * @return bool Success status
 */
function updateParticipant($participantId, $data) {
    try {
        $conn = getConnection();
        
        $allowedFields = ['full_name', 'email', 'phone'];
        $updateData = [];
        $params = [':participant_id' => $participantId];
        
        foreach ($allowedFields as $field) {
            if (isset($data[$field])) {
                $updateData[] = "$field = :$field";
                $params[":$field"] = $data[$field];
            }
        }
        
        if (empty($updateData)) {
            return false;
        }
        
        $sql = "UPDATE participants SET " . implode(', ', $updateData) . " WHERE participant_id = :participant_id";
        $stmt = $conn->prepare($sql);
        
        return $stmt->execute($params);
    } catch (PDOException $e) {
        return false;
    }
}

/**
 * Update participant photo
 * 
 * @param int $participantId ID of the participant
 * @param string $newPhotoPath New photo path
 * @return bool Success status
 */
function updateParticipantPhoto($participantId, $newPhotoPath) {
    try {
        $conn = getConnection();
        
        // Get current photo path
        $getPhotoSql = "SELECT photo FROM participants WHERE participant_id = :participant_id";
        $getPhotoStmt = $conn->prepare($getPhotoSql);
        $getPhotoStmt->bindParam(':participant_id', $participantId);
        $getPhotoStmt->execute();
        
        if ($getPhotoStmt->rowCount() > 0) {
            $participant = $getPhotoStmt->fetch(PDO::FETCH_ASSOC);
            $oldPhotoPath = $participant['photo'];
            
            // Delete old photo if exists
            if (!empty($oldPhotoPath) && file_exists('../../ressources/' . $oldPhotoPath)) {
                unlink('../../ressources/' . $oldPhotoPath);
            }
        }
        
        // Update photo path
        $updateSql = "UPDATE participants SET photo = :photo WHERE participant_id = :participant_id";
        $updateStmt = $conn->prepare($updateSql);
        $updateStmt->bindParam(':photo', $newPhotoPath);
        $updateStmt->bindParam(':participant_id', $participantId);
        
        return $updateStmt->execute();
    } catch (PDOException $e) {
        return false;
    }
}

/**
 * Delete participant
 * 
 * @param int $participantId ID of the participant
 * @return bool Success status
 */
function deleteParticipant($participantId) {
    try {
        $conn = getConnection();
        
        // Get participant data
        $getParticipantSql = "SELECT * FROM participants WHERE participant_id = :participant_id";
        $getParticipantStmt = $conn->prepare($getParticipantSql);
        $getParticipantStmt->bindParam(':participant_id', $participantId);
        $getParticipantStmt->execute();
        
        if ($getParticipantStmt->rowCount() > 0) {
            $participant = $getParticipantStmt->fetch(PDO::FETCH_ASSOC);
            
            // Delete photo if exists
            $photoPath = $participant['photo'];
            if (!empty($photoPath) && file_exists('../../ressources/' . $photoPath)) {
                unlink('../../ressources/' . $photoPath);
            }
            
            // Delete participant
            $deleteSql = "DELETE FROM participants WHERE participant_id = :participant_id";
            $deleteStmt = $conn->prepare($deleteSql);
            $deleteStmt->bindParam(':participant_id', $participantId);
            
            return $deleteStmt->execute();
        }
        
        return false;
    } catch (PDOException $e) {
        return false;
    }
}

// Script pour mettre à jour la table participants afin de permettre les user_id NULL

// Fichier de log pour tracer les opérations
$logFile = __DIR__ . '/participant_table_update.log';

function writeLog($message) {
    global $logFile;
    $timestamp = date('Y-m-d H:i:s');
    file_put_contents($logFile, "[$timestamp] $message" . PHP_EOL, FILE_APPEND);
}

writeLog("=== Début de la mise à jour de la table participants ===");

try {
    // Connexion à la base de données
    $conn = getConnection();
    writeLog("Connexion à la base de données établie");
    
    // Vérifier la structure actuelle de la table
    $stmt = $conn->query("DESCRIBE participants");
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    writeLog("Structure actuelle de la table participants:");
    foreach ($columns as $column) {
        writeLog("- " . $column['Field'] . ": " . $column['Type'] . ", " . $column['Null'] . ", " . $column['Key'] . ", " . $column['Default']);
    }
    
    // Vérifier les contraintes de clé étrangère
    $fkCheck = $conn->query("SELECT * FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE 
                            WHERE TABLE_NAME = 'participants' 
                            AND COLUMN_NAME = 'user_id' 
                            AND REFERENCED_TABLE_NAME IS NOT NULL");
    
    $hasForeignKey = false;
    $constraintName = "";
    
    if ($fkInfo = $fkCheck->fetch(PDO::FETCH_ASSOC)) {
        $hasForeignKey = true;
        $constraintName = $fkInfo['CONSTRAINT_NAME'];
        writeLog("Contrainte de clé étrangère trouvée: " . $constraintName);
        writeLog("Cette contrainte référence " . $fkInfo['REFERENCED_TABLE_NAME'] . "(" . $fkInfo['REFERENCED_COLUMN_NAME'] . ")");
    } else {
        writeLog("Aucune contrainte de clé étrangère trouvée sur la colonne user_id");
    }
    
    // Début de la transaction
    $conn->beginTransaction();
    writeLog("Transaction démarrée");
    
    // Si une contrainte existe, la supprimer
    if ($hasForeignKey) {
        $dropFK = "ALTER TABLE participants DROP FOREIGN KEY " . $constraintName;
        writeLog("Exécution de: " . $dropFK);
        $conn->exec($dropFK);
        writeLog("Contrainte de clé étrangère supprimée avec succès");
        
        // Modifier la colonne pour accepter NULL
        $alterColumn = "ALTER TABLE participants MODIFY user_id INT NULL";
        writeLog("Exécution de: " . $alterColumn);
        $conn->exec($alterColumn);
        writeLog("Colonne user_id modifiée pour accepter NULL");
        
        // Recréer la contrainte avec l'option NULL
        $addFK = "ALTER TABLE participants ADD CONSTRAINT " . $constraintName . 
                " FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL ON UPDATE CASCADE";
        writeLog("Exécution de: " . $addFK);
        $conn->exec($addFK);
        writeLog("Contrainte de clé étrangère recréée avec l'option NULL");
    } else {
        // Si aucune contrainte n'existe, modifier simplement la colonne
        $alterColumn = "ALTER TABLE participants MODIFY user_id INT NULL";
        writeLog("Exécution de: " . $alterColumn);
        $conn->exec($alterColumn);
        writeLog("Colonne user_id modifiée pour accepter NULL");
    }
    
    // Vérifier la nouvelle structure
    $stmt = $conn->query("DESCRIBE participants");
    $newColumns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    writeLog("Nouvelle structure de la table participants:");
    foreach ($newColumns as $column) {
        writeLog("- " . $column['Field'] . ": " . $column['Type'] . ", " . $column['Null'] . ", " . $column['Key'] . ", " . $column['Default']);
    }
    
    // Valider la transaction
    $conn->commit();
    writeLog("Transaction validée");
    writeLog("Mise à jour de la table participants terminée avec succès");
    
    echo "La table participants a été mise à jour avec succès. Vous pouvez maintenant enregistrer des participants sans compte utilisateur.";
    
} catch (PDOException $e) {
    // En cas d'erreur, annuler la transaction
    if (isset($conn) && $conn->inTransaction()) {
        $conn->rollBack();
        writeLog("Transaction annulée en raison d'une erreur");
    }
    
    writeLog("Erreur PDO: " . $e->getMessage());
    writeLog("Code erreur: " . $e->getCode());
    
    echo "Une erreur est survenue lors de la mise à jour de la table participants: " . $e->getMessage();
} catch (Exception $e) {
    // En cas d'erreur générale
    if (isset($conn) && $conn->inTransaction()) {
        $conn->rollBack();
        writeLog("Transaction annulée en raison d'une erreur");
    }
    
    writeLog("Exception: " . $e->getMessage());
    
    echo "Une erreur est survenue: " . $e->getMessage();
}

writeLog("=== Fin de la mise à jour de la table participants ===");
?>