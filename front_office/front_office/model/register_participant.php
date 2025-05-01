<?php
session_start();
require_once 'db_connection.php';

// Fichier de log pour déboguer les problèmes d'enregistrement
$logFile = __DIR__ . '/participant_registration.log';
function writeLog($message) {
    global $logFile;
    $timestamp = date('Y-m-d H:i:s');
    $trace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 2);
    $caller = isset($trace[1]) ? basename($trace[1]['file']) . ':' . $trace[1]['line'] : 'unknown';
    file_put_contents($logFile, "[$timestamp] [$caller] $message" . PHP_EOL, FILE_APPEND);
}

function dumpRequestData() {
    global $logFile;
    
    $message = "\n=== DIAGNOSTIC COMPLET DE LA REQUÊTE ===\n";
    $message .= "URI: " . $_SERVER['REQUEST_URI'] . "\n";
    $message .= "POST DATA: " . print_r($_POST, true) . "\n";
    $message .= "FILES DATA: " . print_r($_FILES, true) . "\n";
    $message .= "SESSION DATA: " . print_r($_SESSION, true) . "\n";
    $message .= "SERVER DATA: " . print_r($_SERVER, true) . "\n";
    
    file_put_contents($logFile, $message, FILE_APPEND);
}

// Nouvelle fonction pour vérifier le type de fichier avec amélioration des types MIME et débogage
function isValidImageType($file) {
    // Vérifier si le fichier existe
    if (!isset($file) || $file['error'] != UPLOAD_ERR_OK) {
        writeLog("Fichier non valide ou erreur lors du téléchargement");
        return false;
    }
    
    // Récupérer le type MIME
    $fileMimeType = $file['type'];
    writeLog("Type MIME déclaré: " . $fileMimeType);
    
    // Les types MIME des images
    $allowedMimeTypes = [
        'image/jpeg', 
        'image/jpg', 
        'image/png', 
        'image/gif',
        'image/x-png' // Certains navigateurs peuvent utiliser ce type MIME pour PNG
    ];
    
    // Utiliser l'extension du fichier comme vérification supplémentaire
    $fileName = $file['name'];
    $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
    
    // Liste des extensions acceptées
    $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif'];
    
    // Vérifier si le type MIME est dans la liste des types acceptés
    $isMimeValid = in_array($fileMimeType, $allowedMimeTypes);
    
    // Vérifier si l'extension est dans la liste des extensions acceptées
    $isExtensionValid = in_array($fileExtension, $allowedExtensions);
    
    writeLog("Extension du fichier: ." . $fileExtension . " (Valide: " . ($isExtensionValid ? "Oui" : "Non") . ")");
    writeLog("Type MIME: " . $fileMimeType . " (Valide: " . ($isMimeValid ? "Oui" : "Non") . ")");
    
    // Accepter le fichier si soit le type MIME soit l'extension est valide
    $isValid = $isExtensionValid || $isMimeValid;
    
    if (!$isValid) {
        writeLog("FICHIER REJETÉ: " . $file['name'] . " - Type ou extension non autorisés");
    } else {
        writeLog("FICHIER ACCEPTÉ: " . $file['name'] . " - Type et/ou extension valides");
    }
    
    return $isValid;
}

function processImageUpload($file, $destinationDir, $filePrefix) {
    if (!isset($file) || $file['error'] != UPLOAD_ERR_OK) {
        $errorMessages = [
            UPLOAD_ERR_INI_SIZE => "La taille du fichier dépasse upload_max_filesize dans php.ini",
            UPLOAD_ERR_FORM_SIZE => "La taille du fichier dépasse MAX_FILE_SIZE spécifié dans le formulaire HTML",
            UPLOAD_ERR_PARTIAL => "Le fichier n'a été que partiellement téléchargé",
            UPLOAD_ERR_NO_FILE => "Aucun fichier n'a été téléchargé",
            UPLOAD_ERR_NO_TMP_DIR => "Dossier temporaire manquant",
            UPLOAD_ERR_CANT_WRITE => "Échec d'écriture du fichier sur le disque",
            UPLOAD_ERR_EXTENSION => "Une extension PHP a arrêté le téléchargement du fichier"
        ];
        $errorCode = isset($file) ? $file['error'] : UPLOAD_ERR_NO_FILE;
        $errorMessage = isset($errorMessages[$errorCode]) ? $errorMessages[$errorCode] : "Erreur inconnue: $errorCode";
        writeLog("Erreur lors de l'upload: " . $errorMessage);
        return '';
    }
    
    // Vérifier le type de fichier
    if (!isValidImageType($file)) {
        writeLog("Type de fichier non autorisé pour " . $file['name']);
        return '';
    }
    
    // Log plus détaillé pour le débogage PNG
    writeLog("Détails du fichier: Nom=" . $file['name'] . ", Type=" . $file['type'] . ", Taille=" . $file['size']);
    
    // S'assurer que le chemin de destination est absolu et correctement formaté
    if (strpos($destinationDir, '..') === 0) {
        $destinationDir = dirname(dirname(__FILE__)) . substr($destinationDir, 2);
    }
    
    // Normaliser les séparateurs de chemin
    $destinationDir = str_replace('\\', '/', $destinationDir);
    
    writeLog("Chemin de destination normalisé: " . $destinationDir);
    
    // Créer le répertoire de destination s'il n'existe pas
    if (!file_exists($destinationDir)) {
        writeLog("Création du répertoire: " . $destinationDir);
        if (!mkdir($destinationDir, 0777, true)) {
            writeLog("Erreur: Impossible de créer le répertoire " . $destinationDir);
            return '';
        }
        chmod($destinationDir, 0777); // S'assurer que les permissions sont correctes
        writeLog("Répertoire créé avec succès et permissions définies");
    } else {
        writeLog("Le répertoire existe déjà: " . $destinationDir);
        // Vérifier les permissions
        if (!is_writable($destinationDir)) {
            writeLog("ATTENTION: Le répertoire n'est pas accessible en écriture, tentative de correction");
            chmod($destinationDir, 0777);
        }
    }
    
    // Déterminer l'extension du fichier à partir de son nom
    $fileExtension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    
    // Pour les PNG, vérifier explicitement l'extension
    if ($fileExtension == 'png') {
        writeLog("Traitement spécial pour fichier PNG");
    }
    
    // Générer un nom de fichier unique pour éviter les écrasements
    $fileName = $filePrefix . '_' . time() . '_' . uniqid() . '.' . $fileExtension;
    
    // Créer les chemins complets et relatifs
    $relativePath = 'participant_photos/' . $fileName;
    $fullPath = $destinationDir . '/' . $fileName;
    
    writeLog("Tentative d'upload: " . $file['tmp_name'] . " vers " . $fullPath);
    
    // Vérifier que le fichier temporaire existe
    if (!file_exists($file['tmp_name'])) {
        writeLog("Le fichier temporaire n'existe pas: " . $file['tmp_name']);
        return '';
    }
    
    // Déplacer le fichier avec plus de robustesse
    $uploadSuccess = false;
    
    // Première tentative avec move_uploaded_file
    try {
        if (move_uploaded_file($file['tmp_name'], $fullPath)) {
            writeLog("Upload réussi avec move_uploaded_file");
            $uploadSuccess = true;
        } else {
            $lastError = error_get_last();
            writeLog("Échec du déplacement: " . ($lastError ? $lastError['message'] : 'Raison inconnue'));
            
            // Deuxième tentative avec copy
            if (copy($file['tmp_name'], $fullPath)) {
                writeLog("Upload réussi avec copy alternative");
                $uploadSuccess = true;
            } else {
                $lastError = error_get_last();
                writeLog("Échec également de la copie: " . ($lastError ? $lastError['message'] : 'Raison inconnue'));
                
                // Troisième tentative avec fonction spécifique pour PNG
                if ($fileExtension == 'png' && function_exists('imagepng')) {
                    writeLog("Tentative avec les fonctions GD pour PNG");
                    $image = @imagecreatefrompng($file['tmp_name']);
                    if ($image) {
                        if (imagepng($image, $fullPath)) {
                            writeLog("Upload PNG réussi avec imagepng");
                            $uploadSuccess = true;
                        } else {
                            writeLog("Échec de imagepng");
                        }
                        imagedestroy($image);
                    } else {
                        writeLog("Échec de imagecreatefrompng");
                    }
                }
            }
        }
    } catch (Exception $e) {
        writeLog("Exception lors du téléchargement: " . $e->getMessage());
    }
    
    if ($uploadSuccess) {
        writeLog("Upload réussi: " . $relativePath);
        writeLog("Vérification que le fichier existe après upload: " . (file_exists($fullPath) ? "OK" : "ÉCHEC"));
        
        // Pour les fichiers PNG, vérifier l'intégrité du fichier
        if ($fileExtension == 'png' && function_exists('getimagesize')) {
            $imageInfo = @getimagesize($fullPath);
            if ($imageInfo && $imageInfo[2] == IMAGETYPE_PNG) {
                writeLog("Vérification du PNG réussie: dimensions " . $imageInfo[0] . "x" . $imageInfo[1]);
            } else {
                writeLog("ATTENTION: Le fichier PNG semble corrompu ou invalide");
            }
        }
        
        return $relativePath;
    } else {
        writeLog("Échec total de l'upload, tous les moyens ont échoué");
        return '';
    }
}

/**
 * Helper function to check if database connection is still valid
 * @param PDO $conn The database connection to check
 * @return bool True if connection is valid, false otherwise
 */
function checkDatabaseConnection($conn) {
    try {
        $conn->query("SELECT 1");
        return true;
    } catch (PDOException $e) {
        writeLog("❌ Erreur de connexion à la base de données: " . $e->getMessage());
        return false;
    }
}

// Amélioration: Trace plus détaillée des requêtes SQL
function writeQueryLog($query, $params = [], $result = null) {
    global $logFile;
    $timestamp = date('Y-m-d H:i:s');
    $logMessage = "[$timestamp] SQL QUERY: $query\n";
    $logMessage .= "[$timestamp] PARAMS: " . print_r($params, true) . "\n";
    $logMessage .= "[$timestamp] RESULT: " . ($result ? "SUCCESS" : "FAILED") . "\n";
    file_put_contents($logFile, $logMessage, FILE_APPEND);
}

// Vérifions la structure de la table participants
function checkTableStructure($conn) {
    try {
        $stmt = $conn->query("DESCRIBE participants");
        $columns = $stmt->fetchAll(PDO::FETCH_COLUMN);
        writeLog("Structure de la table participants: " . implode(", ", $columns));
        
        // Amélioration: Récupération de tous les détails des colonnes
        $columnsDetail = $conn->query("SHOW COLUMNS FROM participants")->fetchAll(PDO::FETCH_ASSOC);
        writeLog("Détails des colonnes de la table participants: " . print_r($columnsDetail, true));
        
        // Vérifions si on peut faire une requête simple sur la table
        $testStmt = $conn->query("SELECT COUNT(*) FROM participants");
        $count = $testStmt->fetchColumn();
        writeLog("Nombre total de participants dans la table: " . $count);
        
        return true;
    } catch (PDOException $e) {
        writeLog("ERREUR CRITIQUE: Problème avec la table participants: " . $e->getMessage());
        writeLog("Trace complète: " . $e->getTraceAsString());
        return false;
    }
}

// Fonction modifiée pour ne plus créer d'utilisateur temporaire
function createTemporaryUser($conn, $fullName, $email) {
    writeLog("Enregistrement du membre d'équipe: $fullName ($email) avec user_id NULL");
    // Pas de création d'utilisateurs temporaires, on retourne simplement NULL comme ID
    return null;
}

writeLog("=== Début d'une nouvelle tentative d'inscription ===");
writeLog("Date et heure: " . date('Y-m-d H:i:s'));
writeLog("Version PHP: " . phpversion());
writeLog("Extensions PHP chargées: " . implode(", ", get_loaded_extensions()));
writeLog("Méthode HTTP: " . $_SERVER['REQUEST_METHOD']);
dumpRequestData();

// Vérifier si nous sommes en mode édition
$editMode = isset($_POST['edit_mode']) && $_POST['edit_mode'] === 'true';
$participantId = isset($_POST['participant_id']) ? intval($_POST['participant_id']) : 0;
$teamId = isset($_POST['team_id']) ? intval($_POST['team_id']) : 0;

writeLog("Mode: " . ($editMode ? "Édition" : "Nouvelle inscription"));
if ($editMode) {
    writeLog("ID Participant: $participantId, ID Équipe: $teamId");
}

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    writeLog("Erreur: Utilisateur non connecté");
    header("Location: ../view/signin.php");
    exit();
}

writeLog("Utilisateur connecté ID: " . $_SESSION['user_id']);
writeLog("Données de session: " . print_r($_SESSION, true));

// Check if form was submitted
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    writeLog("Erreur: Méthode non autorisée");
    header("Location: ../view/hackathons.php");
    exit();
}

// Get form data
$hackathonId = isset($_POST['hackathon_id']) ? intval($_POST['hackathon_id']) : 0;
$participationType = isset($_POST['participation_type']) ? $_POST['participation_type'] : '';
$userId = $_SESSION['user_id'];

writeLog("Données reçues - Hackathon ID: $hackathonId, Type de participation: $participationType, User ID: $userId");

// Validate hackathon ID
if ($hackathonId <= 0) {
    writeLog("Erreur: ID de hackathon invalide");
    header("Location: ../view/hackathons.php?error=invalid_hackathon");
    exit();
}

// Connect to database
try {
    $conn = getConnection();
    writeLog("Connexion à la base de données établie");
    
    // Ajout: Vérifier l'état de la connexion
    writeLog("État de la connexion PDO: " . ($conn ? "Valide" : "Invalide"));
    writeLog("PDO Attributes: " . print_r([
        "ERRMODE" => $conn->getAttribute(PDO::ATTR_ERRMODE),
        "AUTOCOMMIT" => $conn->getAttribute(PDO::ATTR_AUTOCOMMIT),
        "DRIVER_NAME" => $conn->getAttribute(PDO::ATTR_DRIVER_NAME)
    ], true));
    
    // Vérifier la structure de la table
    if (!checkTableStructure($conn)) {
        writeLog("Erreur critique avec la structure de la table participants");
        header("Location: ../view/hackathons.php?error=database_structure");
        exit();
    }
    
    // Vérifier si nous devons sauter la vérification d'inscription existante en mode édition
    if (!$editMode) {
        // Vérification standard pour une nouvelle inscription
        // Check if user is already registered by email
        if (isset($_POST['email'])) { // Vérifier si la clé 'email' existe
            $checkSql = "SELECT * FROM participants WHERE hackathon_id = :hackathon_id AND email = :email";
            $checkParams = [":hackathon_id" => $hackathonId, ":email" => $_POST['email']];
            $checkStmt = $conn->prepare($checkSql);
            $checkStmt->bindParam(":hackathon_id", $hackathonId);
            $checkStmt->bindParam(":email", $_POST['email']);
            
            writeQueryLog($checkSql, $checkParams);
            $checkResult = $checkStmt->execute();
            writeLog("Exécution vérification participant: " . ($checkResult ? "Réussie" : "Échouée"));
            writeLog("Nombre de lignes retournées: " . $checkStmt->rowCount());
            
            if ($checkStmt->rowCount() > 0) {
                writeLog("Erreur: Un participant avec cet email est déjà inscrit à ce hackathon");
                header("Location: ../view/hackathon-details.php?id=$hackathonId&error=already_registered");
                exit();
            }
            
            writeLog("Aucun participant avec cet email n'est inscrit à ce hackathon");
        } else {
            // Si pas d'email fourni, vérifier si l'utilisateur est déjà inscrit par son user_id
            $checkSql = "SELECT * FROM participants WHERE hackathon_id = :hackathon_id AND user_id = :user_id";
            $checkParams = [":hackathon_id" => $hackathonId, ":user_id" => $userId];
            $checkStmt = $conn->prepare($checkSql);
            $checkStmt->bindParam(":hackathon_id", $hackathonId);
            $checkStmt->bindParam(":user_id", $userId);
            
            writeQueryLog($checkSql, $checkParams);
            $checkResult = $checkStmt->execute();
            writeLog("Exécution vérification participant par user_id: " . ($checkResult ? "Réussie" : "Échouée"));
            writeLog("Nombre de lignes retournées: " . $checkStmt->rowCount());
            
            if ($checkStmt->rowCount() > 0) {
                writeLog("Erreur: Cet utilisateur est déjà inscrit à ce hackathon");
                header("Location: ../view/hackathon-details.php?id=$hackathonId&error=already_registered");
                exit();
            }
            
            writeLog("Cet utilisateur n'est pas encore inscrit à ce hackathon");
        }
    } else {
        writeLog("Mode édition - Vérification d'inscription ignorée");
    }
    
    // Create directory for participant photos if it doesn't exist
    $photoDir = '../../ressources/participant_photos';
    if (!file_exists($photoDir)) {
        if (!mkdir($photoDir, 0777, true)) {
            writeLog("ERREUR: Impossible de créer le répertoire: $photoDir");
            header("Location: ../view/register-participant.php?hackathon_id=$hackathonId&error=photo_directory_creation");
            exit();
        }
        writeLog("Répertoire des photos de participants créé: $photoDir");
        // Définir explicitement les permissions après création
        chmod($photoDir, 0777);
        writeLog("Permissions du répertoire définies: 0777");
    } else {
        writeLog("Répertoire des photos de participants existe déjà: $photoDir");
        // Vérifier les permissions
        writeLog("Permissions du répertoire: " . substr(sprintf('%o', fileperms($photoDir)), -4));
        if (!is_writable($photoDir)) {
            writeLog("ERREUR: Le répertoire n'est pas accessible en écriture: $photoDir");
            // Tenter de corriger les permissions
            chmod($photoDir, 0777);
            writeLog("Tentative de correction des permissions du répertoire: 0777");
        }
    }
    
    // Begin transaction for database operations
    $conn->beginTransaction();
    writeLog("Transaction de base de données commencée");
    
    // Handle individual registration
    if ($participationType === 'individual') {
        // Get individual participant data
        $fullName = isset($_POST['full_name']) ? trim($_POST['full_name']) : '';
        $email = isset($_POST['email']) ? trim($_POST['email']) : '';
        $phone = isset($_POST['phone']) ? trim($_POST['phone']) : '';
        $role = isset($_POST['role']) ? trim($_POST['role']) : ''; // Changed from participant_role to role
        
        writeLog("Inscription individuelle - Nom: $fullName, Email: $email, Téléphone: $phone, Rôle: $role");
        
        // Validate required fields
        if (empty($fullName) || empty($email) || empty($phone) || empty($role)) {
            writeLog("Erreur: Champs obligatoires manquants pour l'inscription individuelle");
            header("Location: ../view/register-participant.php?hackathon_id=$hackathonId&error=missing_fields");
            exit();
        }
        
        // Handle photo upload
        $photoPath = '';
        if (isset($_FILES['photo']) && $_FILES['photo']['error'] != UPLOAD_ERR_NO_FILE) {
            writeLog("Photo fournie, traitement de l'upload...");
            $photoPath = processImageUpload($_FILES['photo'], $photoDir, 'participant');
        } else {
            writeLog("Aucune nouvelle photo fournie");
        }
        
        // Si nous sommes en mode édition, mettre à jour les données existantes
        if ($editMode && $participantId > 0) {
            writeLog("Mode édition individuelle - ID participant: $participantId");
            
            // Récupérer les données actuelles pour conserver la photo si aucune nouvelle n'est fournie
            $getCurrentData = $conn->prepare("SELECT * FROM participants WHERE id = :id");
            $getCurrentData->bindParam(":id", $participantId);
            $getCurrentData->execute();
            $currentData = $getCurrentData->fetch(PDO::FETCH_ASSOC);
            
            writeLog("Données actuelles: " . print_r($currentData, true));
            
            // Si pas de nouvelle photo fournie, conserver l'ancienne
            if (empty($photoPath) && isset($currentData['photo'])) {
                $photoPath = $currentData['photo'];
                writeLog("Conservation de la photo existante: $photoPath");
            }
            
            // Préparer la requête de mise à jour
            $sql = "UPDATE participants SET 
                    full_name = :full_name, 
                    email = :email, 
                    phone = :phone,
                    role = :role";
            
            // N'ajouter la photo à la mise à jour que si elle est définie
            if (!empty($photoPath)) {
                $sql .= ", photo = :photo";
            }
            
            $sql .= " WHERE id = :id";
            
            writeLog("SQL update: $sql");
            
            // Préparer la requête
            $stmt = $conn->prepare($sql);
            
            // Lier les paramètres
            $stmt->bindParam(":full_name", $fullName);
            $stmt->bindParam(":email", $email);
            $stmt->bindParam(":phone", $phone);
            $stmt->bindParam(":role", $role);
            $stmt->bindParam(":id", $participantId);
            
            // Lier la photo seulement si elle est définie
            if (!empty($photoPath)) {
                $stmt->bindParam(":photo", $photoPath);
            }
            
            // Exécuter la requête
            try {
                $result = $stmt->execute();
                
                if (!$result) {
                    $errorInfo = $stmt->errorInfo();
                    writeLog("Erreur lors de la mise à jour: " . print_r($errorInfo, true));
                    throw new Exception("Échec de la mise à jour du participant. Erreur SQL: " . $errorInfo[2]);
                }
                
                writeLog("Mise à jour réussie du participant individuel");
                
                // Valider la transaction
                $conn->commit();
                writeLog("Transaction validée");
                
                // Rediriger vers la page de succès
                header("Location: ../view/hackathon-details.php?id=$hackathonId&success=updated");
                exit();
                
            } catch (Exception $e) {
                $conn->rollBack();
                writeLog("Exception lors de la mise à jour: " . $e->getMessage());
                header("Location: ../view/register-participant.php?hackathon_id=$hackathonId&error=update_failed&message=" . urlencode($e->getMessage()));
                exit();
            }
        } 
        // Si nous ne sommes pas en mode édition, faire une nouvelle inscription
        else {
            // Insert participant data into database
            $sql = "INSERT INTO participants (hackathon_id, user_id, full_name, email, phone, photo, participation_type, registration_date) 
                    VALUES (:hackathon_id, NULL, :full_name, :email, :phone, :photo, 'individual', NOW())";
            
            writeLog("Préparation de la requête SQL pour l'insertion du participant individuel");
            writeLog("SQL: $sql");
            
            $stmt = $conn->prepare($sql);
            $params = [
                ":hackathon_id" => $hackathonId,
                ":full_name" => $fullName,
                ":email" => $email,
                ":phone" => $phone,
                ":photo" => $photoPath
            ];
            writeLog("Paramètres SQL: " . print_r($params, true));
            
            $stmt->bindParam(":hackathon_id", $hackathonId);
            // Ne pas lier user_id car il est maintenant NULL
            $stmt->bindParam(":full_name", $fullName);
            $stmt->bindParam(":email", $email);
            $stmt->bindParam(":phone", $phone);
            $stmt->bindParam(":photo", $photoPath);
            
            writeLog("Exécution de la requête d'insertion...");
            
            try {
                writeQueryLog($sql, $params);
                $result = $stmt->execute();
                
                if (!$result) {
                    $errorInfo = $stmt->errorInfo();
                    writeLog("Erreur d'exécution SQL: " . print_r($errorInfo, true));
                    throw new Exception("Échec de l'enregistrement du participant. Erreur SQL: " . $errorInfo[2]);
                }
                
                $participantId = $conn->lastInsertId();
                writeLog("Participant enregistré avec succès avec l'ID: $participantId");
                
                // Vérifier que l'insertion a bien été effectuée
                $verifyStmt = $conn->prepare("SELECT * FROM participants WHERE hackathon_id = :hackathon_id AND email = :email");
                $verifyParams = [":hackathon_id" => $hackathonId, ":email" => $email];
                $verifyStmt->bindParam(":hackathon_id", $hackathonId);
                $verifyStmt->bindParam(":email", $email);
                
                writeQueryLog("SELECT * FROM participants WHERE hackathon_id = :hackathon_id AND email = :email", $verifyParams);
                $verifyStmt->execute();
                
                if ($verifyStmt->rowCount() === 0) {
                    writeLog("ALERTE: L'enregistrement a été signalé comme réussi mais le participant n'est pas retrouvable dans la base de données!");
                    
                    // Amélioration: Vérifier si l'auto-increment fonctionne correctement
                    $checkAI = $conn->query("SHOW TABLE STATUS LIKE 'participants'");
                    $aiInfo = $checkAI->fetch(PDO::FETCH_ASSOC);
                    writeLog("État de l'auto-increment: " . print_r($aiInfo, true));
                    
                    // Vérifier les contraintes de clé étrangère
                    $fkCheck = $conn->query("SELECT * FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE WHERE REFERENCED_TABLE_NAME = 'participants'");
                    $fkInfo = $fkCheck->fetchAll(PDO::FETCH_ASSOC);
                    writeLog("Contraintes de clé étrangère: " . print_r($fkInfo, true));
                } else {
                    $participantData = $verifyStmt->fetch(PDO::FETCH_ASSOC);
                    writeLog("Vérification de l'insertion réussie. Données: " . print_r($participantData, true));
                }
                
                // Commit transaction
                $conn->commit();
                writeLog("Transaction validée");
                
                // Vérifier à nouveau après commit
                $finalCheck = $conn->prepare("SELECT * FROM participants WHERE hackathon_id = :hackathon_id AND user_id = :user_id");
                $finalCheck->bindParam(":hackathon_id", $hackathonId);
                $finalCheck->bindParam(":user_id", $userId);
                $finalCheck->execute();
                
                if ($finalCheck->rowCount() === 0) {
                    writeLog("ALERTE CRITIQUE: Après COMMIT, le participant n'est toujours pas trouvé dans la base de données!");
                } else {
                    writeLog("Confirmation post-COMMIT: Participant correctement enregistré");
                }
                
                // Redirect to success page
                writeLog("Redirection vers la page de succès");
                header("Location: ../view/hackathon-details.php?id=$hackathonId&success=registered");
                exit();
            } catch (Exception $e) {
                $conn->rollBack();
                writeLog("Exception: " . $e->getMessage());
                writeLog("Trace complète: " . $e->getTraceAsString());
                writeLog("Transaction annulée");
                
                // Delete uploaded photo if database insert fails
                if (!empty($photoPath) && file_exists($photoPath) && $photoPath != $currentData['photo']) {
                    unlink($photoPath);
                    writeLog("Photo supprimée en raison de l'échec de l'insertion");
                }
                
                header("Location: ../view/register-participant.php?hackathon_id=$hackathonId&error=database_error");
                exit();
            }
        }
    } 
    // Handle team registration
    elseif ($participationType === 'team') {
        writeLog("📋 Traitement d'inscription d'équipe");
        
        // Get team data
        $teamName = isset($_POST['team_name']) ? trim($_POST['team_name']) : '';
        $teamSize = isset($_POST['team_size']) ? intval($_POST['team_size']) : 0;
        
        writeLog("Données d'équipe - Nom: $teamName, Taille: $teamSize");
        
        // Validate team data
        if (empty($teamName) || $teamSize < 2) {
            $error = empty($teamName) ? "nom d'équipe manquant" : "taille d'équipe invalide";
            writeLog("Erreur: $error");
            header("Location: ../view/register-participant.php?hackathon_id=$hackathonId&error=invalid_team&reason=" . urlencode($error));
            exit();
        }
        
        // Check if team leader info is present
        $leaderName = isset($_POST['leader_name']) ? trim($_POST['leader_name']) : '';
        $leaderEmail = isset($_POST['leader_email']) ? trim($_POST['leader_email']) : '';
        $leaderPhone = isset($_POST['leader_phone']) ? trim($_POST['leader_phone']) : '';
        $leaderRole = isset($_POST['leader_role']) ? trim($_POST['leader_role']) : '';
        
        if (empty($leaderName) || empty($leaderEmail) || empty($leaderPhone) || empty($leaderRole)) {
            writeLog("Erreur: Données du chef d'équipe incomplètes");
            header("Location: ../view/register-participant.php?hackathon_id=$hackathonId&error=incomplete_leader");
            exit();
        }
        
        // Add additional debugging for leader role
        writeLog("Leader role value: '$leaderRole'");
        
        try {
            // Check database connection before proceeding
            if (!checkDatabaseConnection($conn)) {
                throw new Exception("La connexion à la base de données a été perdue");
            }
            
            // Si nous sommes en mode édition d'équipe
            if ($editMode && $teamId > 0) {
                writeLog("Mode édition d'équipe - ID équipe: $teamId");
                
                // Récupérer les données de l'équipe
                $getTeamSql = "SELECT * FROM participants WHERE hackathon_id = :hackathon_id AND team_name = :team_name ORDER BY id ASC";
                $getTeamStmt = $conn->prepare($getTeamSql);
                $getTeamStmt->bindParam(":hackathon_id", $hackathonId);
                $getTeamStmt->bindParam(":team_name", $teamName);
                $getTeamStmt->execute();
                
                $teamMembers = $getTeamStmt->fetchAll(PDO::FETCH_ASSOC);
                $teamMemberCount = count($teamMembers);
                
                writeLog("Équipe trouvée: $teamName avec $teamMemberCount membres");
                writeLog("Membres actuels: " . print_r($teamMembers, true));
                
                // Mise à jour du chef d'équipe (premier membre)
                if (count($teamMembers) > 0) {
                    $teamLeader = $teamMembers[0];
                    $leaderPhotoPath = '';
                    
                    // Traiter la photo du leader
                    if (isset($_FILES['leader_photo']) && $_FILES['leader_photo']['error'] != UPLOAD_ERR_NO_FILE) {
                        $leaderPhotoPath = processImageUpload($_FILES['leader_photo'], $photoDir, 'leader');
                    } else if (!empty($teamLeader['photo'])) {
                        // Conserver la photo existante
                        $leaderPhotoPath = $teamLeader['photo'];
                    }
                    
                    // Requête pour mettre à jour le chef d'équipe
                    $updateLeaderSql = "UPDATE participants SET 
                                        full_name = :full_name,
                                        email = :email,
                                        phone = :phone,
                                        role = :role";
                                        
                    if (!empty($leaderPhotoPath)) {
                        $updateLeaderSql .= ", photo = :photo";
                    }
                    
                    $updateLeaderSql .= " WHERE id = :id";
                    
                    writeLog("SQL mise à jour leader: " . $updateLeaderSql);
                    
                    $updateLeaderStmt = $conn->prepare($updateLeaderSql);
                    $updateLeaderStmt->bindParam(":full_name", $leaderName);
                    $updateLeaderStmt->bindParam(":email", $leaderEmail);
                    $updateLeaderStmt->bindParam(":phone", $leaderPhone);
                    $updateLeaderStmt->bindParam(":role", $leaderRole);
                    
                    if (!empty($leaderPhotoPath)) {
                        $updateLeaderStmt->bindParam(":photo", $leaderPhotoPath);
                    }
                    
                    $updateLeaderStmt->bindParam(":id", $teamLeader['id']);
                    
                    if (!$updateLeaderStmt->execute()) {
                        $errorInfo = $updateLeaderStmt->errorInfo();
                        writeLog("Erreur lors de la mise à jour du chef d'équipe: " . print_r($errorInfo, true));
                        throw new Exception("Échec de la mise à jour du chef d'équipe");
                    }
                    
                    writeLog("Chef d'équipe mis à jour avec succès");
                    
                    // Mise à jour des membres d'équipe existants
                    // Membre 2 (obligatoire)
                    if (count($teamMembers) > 1) {
                        $member2 = $teamMembers[1];
                        $member2Name = isset($_POST['member2_name']) ? trim($_POST['member2_name']) : '';
                        $member2Email = isset($_POST['member2_email']) ? trim($_POST['member2_email']) : '';
                        $member2Phone = isset($_POST['member2_phone']) ? trim($_POST['member2_phone']) : '';
                        $member2Role = isset($_POST['member2_role']) ? trim($_POST['member2_role']) : '';
                        
                        if (empty($member2Name) || empty($member2Email) || empty($member2Phone) || empty($member2Role)) {
                            writeLog("Données de membre 2 incomplètes");
                            throw new Exception("Les informations du membre 2 sont incomplètes");
                        }
                        
                        $member2PhotoPath = '';
                        if (isset($_FILES['member2_photo']) && $_FILES['member2_photo']['error'] != UPLOAD_ERR_NO_FILE) {
                            $member2PhotoPath = processImageUpload($_FILES['member2_photo'], $photoDir, 'member2');
                        } else if (!empty($member2['photo'])) {
                            $member2PhotoPath = $member2['photo'];
                        }
                        
                        $updateMember2Sql = "UPDATE participants SET
                                            full_name = :full_name,
                                            email = :email,
                                            phone = :phone,
                                            role = :role";
                        
                        if (!empty($member2PhotoPath)) {
                            $updateMember2Sql .= ", photo = :photo";
                        }
                        
                        $updateMember2Sql .= " WHERE id = :id";
                        
                        $updateMember2Stmt = $conn->prepare($updateMember2Sql);
                        $updateMember2Stmt->bindParam(":full_name", $member2Name);
                        $updateMember2Stmt->bindParam(":email", $member2Email);
                        $updateMember2Stmt->bindParam(":phone", $member2Phone);
                        $updateMember2Stmt->bindParam(":role", $member2Role);
                        
                        if (!empty($member2PhotoPath)) {
                            $updateMember2Stmt->bindParam(":photo", $member2PhotoPath);
                        }
                        
                        $updateMember2Stmt->bindParam(":id", $member2['id']);
                        
                        if (!$updateMember2Stmt->execute()) {
                            $errorInfo = $updateMember2Stmt->errorInfo();
                            writeLog("Erreur lors de la mise à jour du membre 2: " . print_r($errorInfo, true));
                            throw new Exception("Échec de la mise à jour du membre 2");
                        }
                        
                        writeLog("Membre 2 mis à jour avec succès");
                    } else {
                        writeLog("Membre 2 non trouvé dans l'équipe existante");
                        // Erreur critique, membre 2 devrait exister
                        throw new Exception("Membre 2 non trouvé dans l'équipe");
                    }
                    
                    // Membre 3 (optionnel)
                    $member3Name = isset($_POST['member3_name']) ? trim($_POST['member3_name']) : '';
                    $member3Email = isset($_POST['member3_email']) ? trim($_POST['member3_email']) : '';
                    $member3Phone = isset($_POST['member3_phone']) ? trim($_POST['member3_phone']) : '';
                    $member3Role = isset($_POST['member3_role']) ? trim($_POST['member3_role']) : '';
                    
                    // Si les champs du membre 3 sont remplis
                    if (!empty($member3Name) && !empty($member3Email)) {
                        writeLog("Membre 3 présent dans le formulaire");
                        
                        // Si le membre 3 existe déjà dans l'équipe, on le met à jour
                        if (count($teamMembers) > 2) {
                            $member3 = $teamMembers[2];
                            
                            $member3PhotoPath = '';
                            if (isset($_FILES['member3_photo']) && $_FILES['member3_photo']['error'] != UPLOAD_ERR_NO_FILE) {
                                $member3PhotoPath = processImageUpload($_FILES['member3_photo'], $photoDir, 'member3');
                            } else if (!empty($member3['photo'])) {
                                $member3PhotoPath = $member3['photo'];
                            }
                            
                            $updateMember3Sql = "UPDATE participants SET
                                                full_name = :full_name,
                                                email = :email,
                                                phone = :phone,
                                                role = :role";
                            
                            if (!empty($member3PhotoPath)) {
                                $updateMember3Sql .= ", photo = :photo";
                            }
                            
                            $updateMember3Sql .= " WHERE id = :id";
                            
                            $updateMember3Stmt = $conn->prepare($updateMember3Sql);
                            $updateMember3Stmt->bindParam(":full_name", $member3Name);
                            $updateMember3Stmt->bindParam(":email", $member3Email);
                            $updateMember3Stmt->bindParam(":phone", $member3Phone);
                            $updateMember3Stmt->bindParam(":role", $member3Role);
                            
                            if (!empty($member3PhotoPath)) {
                                $updateMember3Stmt->bindParam(":photo", $member3PhotoPath);
                            }
                            
                            $updateMember3Stmt->bindParam(":id", $member3['id']);
                            
                            if (!$updateMember3Stmt->execute()) {
                                $errorInfo = $updateMember3Stmt->errorInfo();
                                writeLog("Erreur lors de la mise à jour du membre 3: " . print_r($errorInfo, true));
                                throw new Exception("Échec de la mise à jour du membre 3");
                            }
                            
                            writeLog("Membre 3 mis à jour avec succès");
                        } 
                        // Si le membre 3 n'existe pas encore, on l'ajoute
                        else {
                            writeLog("Ajout d'un nouveau membre 3 à l'équipe existante");
                            
                            $member3PhotoPath = '';
                            if (isset($_FILES['member3_photo']) && $_FILES['member3_photo']['error'] != UPLOAD_ERR_NO_FILE) {
                                $member3PhotoPath = processImageUpload($_FILES['member3_photo'], $photoDir, 'member3');
                            }
                            
                            $addMember3Sql = "INSERT INTO participants (hackathon_id, user_id, full_name, email, phone, role, photo, participation_type, team_name, is_team_lead, registration_date) 
                                            VALUES (:hackathon_id, NULL, :full_name, :email, :phone, :role, :photo, 'team', :team_name, 0, NOW())";
                            
                            $addMember3Stmt = $conn->prepare($addMember3Sql);
                            $addMember3Stmt->bindParam(":hackathon_id", $hackathonId);
                            $addMember3Stmt->bindParam(":full_name", $member3Name);
                            $addMember3Stmt->bindParam(":email", $member3Email);
                            $addMember3Stmt->bindParam(":phone", $member3Phone);
                            $addMember3Stmt->bindParam(":role", $member3Role);
                            $addMember3Stmt->bindParam(":photo", $member3PhotoPath);
                            $addMember3Stmt->bindParam(":team_name", $teamName);
                            
                            if (!$addMember3Stmt->execute()) {
                                $errorInfo = $addMember3Stmt->errorInfo();
                                writeLog("Erreur lors de l'ajout du membre 3: " . print_r($errorInfo, true));
                                throw new Exception("Échec de l'ajout du membre 3");
                            }
                            
                            writeLog("Nouveau membre 3 ajouté avec succès");
                        }
                    } 
                    // Si les champs du membre 3 sont vides et qu'un membre 3 existe
                    else if (count($teamMembers) > 2) {
                        writeLog("Membre 3 non présent dans le formulaire mais existe dans la base, suppression");
                        
                        $deleteMember3Sql = "DELETE FROM participants WHERE id = :id";
                        $deleteMember3Stmt = $conn->prepare($deleteMember3Sql);
                        $deleteMember3Stmt->bindParam(":id", $teamMembers[2]['id']);
                        
                        if (!$deleteMember3Stmt->execute()) {
                            $errorInfo = $deleteMember3Stmt->errorInfo();
                            writeLog("Erreur lors de la suppression du membre 3: " . print_r($errorInfo, true));
                            throw new Exception("Échec de la suppression du membre 3");
                        }
                        
                        writeLog("Membre 3 supprimé avec succès");
                    }
                    
                    // Traiter les membres additionnels du formulaire dynamique
                    $additionalMembers = [];
                    if (isset($_POST['team_members']) && is_array($_POST['team_members'])) {
                        foreach ($_POST['team_members'] as $index => $member) {
                            if (!empty($member['full_name']) && !empty($member['email'])) {
                                $additionalMembers[] = $member;
                            }
                        }
                    }
                    
                    writeLog("Membres additionnels trouvés dans le formulaire: " . count($additionalMembers));
                    
                    // Supprimer d'abord tous les membres additionnels existants (au-delà du membre 3)
                    if (count($teamMembers) > 3) {
                        for ($i = 3; $i < count($teamMembers); $i++) {
                            $deleteAdditionalSql = "DELETE FROM participants WHERE id = :id";
                            $deleteAdditionalStmt = $conn->prepare($deleteAdditionalSql);
                            $deleteAdditionalStmt->bindParam(":id", $teamMembers[$i]['id']);
                            
                            if (!$deleteAdditionalStmt->execute()) {
                                $errorInfo = $deleteAdditionalStmt->errorInfo();
                                writeLog("Erreur lors de la suppression du membre additionnel #$i: " . print_r($errorInfo, true));
                                // On continue malgré l'erreur
                            } else {
                                writeLog("Membre additionnel #$i supprimé avec succès");
                            }
                        }
                    }
                    
                    // Ajouter les nouveaux membres additionnels
                    foreach ($additionalMembers as $index => $member) {
                        $memberName = $member['full_name'];
                        $memberEmail = $member['email'];
                        $memberPhone = isset($member['phone']) ? $member['phone'] : '';
                        $memberRole = isset($member['role']) ? $member['role'] : '';
                        
                        writeLog("Traitement du membre additionnel: $memberName, $memberEmail");
                        
                        $memberPhotoKey = "team_members[$index][photo]";
                        $memberPhotoPath = '';
                        
                        if (isset($_FILES[$memberPhotoKey]) && $_FILES[$memberPhotoKey]['error'] != UPLOAD_ERR_NO_FILE) {
                            $memberPhotoPath = processImageUpload($_FILES[$memberPhotoKey], $photoDir, "member_add");
                        }
                        
                        $addMemberSql = "INSERT INTO participants (hackathon_id, user_id, full_name, email, phone, role, photo, participation_type, team_name, is_team_lead, registration_date) 
                                        VALUES (:hackathon_id, NULL, :full_name, :email, :phone, :role, :photo, 'team', :team_name, 0, NOW())";
                        
                        $addMemberStmt = $conn->prepare($addMemberSql);
                        $addMemberStmt->bindParam(":hackathon_id", $hackathonId);
                        $addMemberStmt->bindParam(":full_name", $memberName);
                        $addMemberStmt->bindParam(":email", $memberEmail);
                        $addMemberStmt->bindParam(":phone", $memberPhone);
                        $addMemberStmt->bindParam(":role", $memberRole);
                        $addMemberStmt->bindParam(":photo", $memberPhotoPath);
                        $addMemberStmt->bindParam(":team_name", $teamName);
                        
                        if (!$addMemberStmt->execute()) {
                            $errorInfo = $addMemberStmt->errorInfo();
                            writeLog("Erreur lors de l'ajout du membre additionnel: " . print_r($errorInfo, true));
                            // On continue malgré l'erreur
                        } else {
                            writeLog("Membre additionnel ajouté avec succès");
                        }
                    }
                    
                    // Valider toutes les modifications
                    $conn->commit();
                    writeLog("Toutes les modifications ont été validées avec succès");
                    
                    // Rediriger vers la page de succès
                    header("Location: ../view/hackathon-details.php?id=$hackathonId&success=updated");
                    exit();
                } else {
                    throw new Exception("Équipe trouvée mais aucun membre n'a été récupéré");
                }
            }
            // Si nous ne sommes pas en mode édition, procéder à l'inscription normale
            else {
                writeLog("Mode d'inscription normale d'équipe");
                
                // Process team photo and leader's individual photo
                $teamPhotoPath = '';
                $leaderPhotoPath = '';
                
                // Vérifier si les photos ont été téléchargées
                if (isset($_FILES['team_photo'])) {
                    $teamPhotoPath = processImageUpload($_FILES['team_photo'], '../../ressources/team_photos', 'team');
                } else {
                    writeLog("Pas de photo d'équipe fournie dans le formulaire");
                }
                
                if (isset($_FILES['leader_photo'])) {
                    $leaderPhotoPath = processImageUpload($_FILES['leader_photo'], $photoDir, 'leader');
                } else {
                    writeLog("Pas de photo de leader fournie dans le formulaire");
                }
                
                // Debug: Check team-related database tables and structure
                try {
                    writeLog("🔍 Vérification de la structure des tables...");
                    $tables = ["participants", "hackathons", "users"];
                    foreach ($tables as $table) {
                        $stmt = $conn->query("DESCRIBE $table");
                        $columns = $stmt->fetchAll(PDO::FETCH_COLUMN);
                        writeLog("📋 Structure de la table $table: " . implode(", ", $columns));
                    }
                } catch (Exception $e) {
                    writeLog("⚠️ Erreur lors de la vérification des tables: " . $e->getMessage());
                    // Continue anyway - this is just diagnostic info
                }
                
                writeLog("⚙️ Début de l'inscription de l'équipe: $teamName");
                
                // Insert team leader as participant (l'utilisateur connecté)
                $leaderSql = "INSERT INTO participants (hackathon_id, user_id, full_name, email, phone, role, photo, participation_type, team_name, is_team_lead, registration_date) 
                              VALUES (:hackathon_id, :user_id, :full_name, :email, :phone, :role, :photo, 'team', :team_name, 1, NOW())";
                
                $leaderStmt = $conn->prepare($leaderSql);
                $leaderParams = [
                    ":hackathon_id" => $hackathonId,
                    ":user_id" => $userId, // ID de l'utilisateur connecté
                    ":full_name" => $leaderName,
                    ":email" => $leaderEmail,
                    ":phone" => $leaderPhone,
                    ":role" => $leaderRole,
                    ":photo" => $leaderPhotoPath, // Utilisation de la photo du leader au lieu de la photo d'équipe
                    ":team_name" => $teamName
                ];
                
                writeLog("SQL pour chef d'équipe: " . $leaderSql);
                writeLog("Paramètres: " . print_r($leaderParams, true));
                
                $leaderStmt->bindParam(":hackathon_id", $hackathonId);
                $leaderStmt->bindParam(":user_id", $userId);
                $leaderStmt->bindParam(":full_name", $leaderName);
                $leaderStmt->bindParam(":email", $leaderEmail);
                $leaderStmt->bindParam(":phone", $leaderPhone);
                $leaderStmt->bindParam(":role", $leaderRole);
                $leaderStmt->bindParam(":photo", $leaderPhotoPath); // Mise à jour du paramètre
                $leaderStmt->bindParam(":team_name", $teamName);
                
                try {
                    $leaderResult = $leaderStmt->execute();
                    
                    if (!$leaderResult) {
                        $errorInfo = $leaderStmt->errorInfo();
                        writeLog("Erreur lors de l'insertion du chef d'équipe: " . print_r($errorInfo, true));
                        throw new Exception("Échec de l'enregistrement du chef d'équipe: " . $errorInfo[2]);
                    }
                    
                } catch (PDOException $pdoEx) {
                    writeLog("❌ Erreur PDO spécifique lors de l'insertion du chef d'équipe: " . $pdoEx->getMessage());
                    
                    // Check if this is a "column not found" error 
                    if (strpos($pdoEx->getMessage(), "Unknown column") !== false) {
                        // Try to determine which column is missing
                        $missingColumn = null;
                        if (strpos($pdoEx->getMessage(), "Unknown column 'role'") !== false) {
                            $missingColumn = 'role';
                            writeLog("⚠️ La colonne 'role' est manquante dans la table participants");
                            
                            // Try an alternative query without the role column
                            writeLog("Tentative d'insertion sans la colonne role");
                            $altLeaderSql = "INSERT INTO participants (hackathon_id, user_id, full_name, email, phone, photo, participation_type, team_name, is_team_lead, registration_date) 
                                            VALUES (:hackathon_id, :user_id, :full_name, :email, :phone, :photo, 'team', :team_name, 1, NOW())";
                            $altLeaderStmt = $conn->prepare($altLeaderSql);
                            $altLeaderStmt->bindParam(":hackathon_id", $hackathonId);
                            $altLeaderStmt->bindParam(":user_id", $userId);
                            $altLeaderStmt->bindParam(":full_name", $leaderName);
                            $altLeaderStmt->bindParam(":email", $leaderEmail);
                            $altLeaderStmt->bindParam(":phone", $leaderPhone);
                            $altLeaderStmt->bindParam(":photo", $leaderPhotoPath);
                            $altLeaderStmt->bindParam(":team_name", $teamName);
                            
                            writeLog("Exécution de la requête alternative sans la colonne role");
                            $altLeaderResult = $altLeaderStmt->execute();
                            
                            if (!$altLeaderResult) {
                                $altErrorInfo = $altLeaderStmt->errorInfo();
                                writeLog("La requête alternative a aussi échoué: " . print_r($altErrorInfo, true));
                                throw new Exception("Échec de l'enregistrement même avec la requête alternative: " . $altErrorInfo[2]);
                            } else {
                                writeLog("✅ Requête alternative réussie sans la colonne role");
                                $leaderParticipantId = $conn->lastInsertId();
                                writeLog("✓ Chef d'équipe enregistré avec succès. ID participant: $leaderParticipantId");
                            }
                        } else {
                            // Rethrow the exception if we can't handle it
                            throw $pdoEx;
                        }
                    } else {
                        // Rethrow other PDO exceptions
                        throw $pdoEx;
                    }
                }
                
                if (!isset($leaderParticipantId)) {
                    $leaderParticipantId = $conn->lastInsertId();
                    writeLog("✓ Chef d'équipe enregistré avec succès. ID participant: $leaderParticipantId");
                }
                
                // Process team members
                writeLog("Traitement des membres de l'équipe");
                $teamMembersSuccess = true;
                $errors = [];
                
                // Iterate through team members
                for ($i = 2; $i <= $teamSize; $i++) {
                    $memberName = isset($_POST["member{$i}_name"]) ? trim($_POST["member{$i}_name"]) : '';
                    $memberEmail = isset($_POST["member{$i}_email"]) ? trim($_POST["member{$i}_email"]) : '';
                    $memberRole = isset($_POST["member{$i}_role"]) ? trim($_POST["member{$i}_role"]) : '';
                    $memberPhone = isset($_POST["member{$i}_phone"]) ? trim($_POST["member{$i}_phone"]) : '';
                    
                    writeLog("Traitement du membre #$i - Nom: '$memberName', Email: '$memberEmail', Role: '$memberRole'");
                    
                    // Continue if member fields are empty (can happen if the form allows for more members than were filled out)
                    if (empty($memberName) && empty($memberEmail)) {
                        writeLog("Membre #$i: Champs vides, ignoré");
                        continue;
                    }
                    
                    // Validate member data
                    if (empty($memberName) || empty($memberEmail)) {
                        $teamMembersSuccess = false;
                        $errors[] = "Membre #$i: Données incomplètes";
                        writeLog("Erreur: Membre #$i - Données incomplètes");
                        continue;
                    }
                    
                    writeLog("Enregistrement du membre d'équipe #$i avec user_id=NULL");
                    
                    try {
                        // On n'a plus besoin de créer un utilisateur temporaire, on utilise simplement NULL pour user_id
                        $memberId = null;
                        writeLog("Enregistrement du membre d'équipe #$i avec user_id=NULL");
                        
                        // Gérer la photo pour le membre d'équipe
                        $memberPhotoPath = processImageUpload($_FILES["member{$i}_photo"], $photoDir, "member{$i}");
                        
                        // Determine which SQL to use based on whether role column exists
                        $memberSql = "";
                        if (isset($missingColumn) && $missingColumn === 'role') {
                            // Use SQL without role column
                            $memberSql = "INSERT INTO participants (hackathon_id, user_id, full_name, email, phone, photo, participation_type, team_name, is_team_lead, registration_date) 
                                        VALUES (:hackathon_id, NULL, :full_name, :email, :phone, :photo, 'team', :team_name, 0, NOW())";
                        } else {
                            // Use SQL with role column
                            $memberSql = "INSERT INTO participants (hackathon_id, user_id, full_name, email, phone, photo, role, participation_type, team_name, is_team_lead, registration_date) 
                                        VALUES (:hackathon_id, NULL, :full_name, :email, :phone, :photo, :role, 'team', :team_name, 0, NOW())";
                        }
                        
                        $memberStmt = $conn->prepare($memberSql);
                        
                        $memberStmt->bindParam(":hackathon_id", $hackathonId);
                        // Ne pas lier user_id car on le passe directement comme NULL dans la requête
                        $memberStmt->bindParam(":full_name", $memberName);
                        $memberStmt->bindParam(":email", $memberEmail);
                        $memberStmt->bindParam(":phone", $memberPhone);
                        $memberStmt->bindParam(":photo", $memberPhotoPath); // Ajout de la photo
                        $memberStmt->bindParam(":team_name", $teamName);
                        
                        if (!(isset($missingColumn) && $missingColumn === 'role')) {
                            $memberStmt->bindParam(":role", $memberRole);
                        }
                        
                        writeLog("SQL pour membre d'équipe #$i: " . $memberSql);
                        
                        $memberResult = $memberStmt->execute();
                        
                        if (!$memberResult) {
                            $errorInfo = $memberStmt->errorInfo();
                            writeLog("Erreur lors de l'insertion du membre #$i: " . print_r($errorInfo, true));
                            throw new Exception("Échec de l'enregistrement du membre #$i: " . $errorInfo[2]);
                        }
                        
                        $memberParticipantId = $conn->lastInsertId();
                        writeLog("✓ Membre d'équipe #$i enregistré avec succès. ID participant: $memberParticipantId");
                        
                    } catch (PDOException $pdoEx) {
                        writeLog("Exception PDO pour membre #$i: " . $pdoEx->getMessage());
                        $teamMembersSuccess = false;
                        $errors[] = "Membre #$i: " . $pdoEx->getMessage();
                        // Continue to next member instead of failing completely
                    } catch (Exception $e) {
                        $teamMembersSuccess = false;
                        $errors[] = "Membre #$i: " . $e->getMessage();
                        writeLog("Exception pour membre #$i: " . $e->getMessage());
                    }
                }
                
                // If any member failed to register, still commit what we have but show a warning
                if (!$teamMembersSuccess) {
                    writeLog("⚠️ Certains membres de l'équipe n'ont pas pu être enregistrés");
                    writeLog("Erreurs: " . print_r($errors, true));
                    
                    // Still commit the transaction to save the successful registrations
                    $conn->commit();
                    writeLog("Transaction validée (avec des avertissements)");
                    
                    // Redirect with partial success
                    header("Location: ../view/hackathon-details.php?id=$hackathonId&success=partial_team&errors=" . urlencode(implode("; ", $errors)));
                    exit();
                }
                
                // All successful
                $conn->commit();
                writeLog("✅ Transaction validée, toute l'équipe a été enregistrée avec succès");
                
                // Redirect to success page
                header("Location: ../view/hackathon-details.php?id=$hackathonId&success=registered");
                exit();
            }
        } catch (PDOException $e) {
            // Roll back the transaction on error
            $conn->rollBack();
            writeLog("❌ PDOException dans le traitement de l'équipe: " . $e->getMessage());
            writeLog("Code erreur: " . $e->getCode());
            writeLog("Trace complète: " . $e->getTraceAsString());
            
            // Delete team photo if it was uploaded
            if (!empty($teamPhotoPath) && file_exists($teamPhotoPath)) {
                unlink($teamPhotoPath);
                writeLog("Photo d'équipe supprimée en raison de l'échec de l'inscription");
            }
            
            // Generate more specific error message
            $errorDetail = "db_general";
            if (strpos($e->getMessage(), "Unknown column") !== false) {
                $errorDetail = "schema_mismatch";
                writeLog("Problème de schéma de base de données détecté");
            } elseif (strpos($e->getMessage(), "Duplicate entry") !== false) {
                $errorDetail = "duplicate_team";
                writeLog("Erreur d'équipe en double détectée");
            } elseif (strpos($e->getMessage(), "Connection refused") !== false || 
                     strpos($e->getMessage(), "Could not connect") !== false) {
                $errorDetail = "connection_lost";
                writeLog("Erreur de connexion à la base de données détectée");
            }
            
            // Redirect with specific error
            header("Location: ../view/register-participant.php?hackathon_id=$hackathonId&error=database_error&error_detail=$errorDetail");
            exit();
        } catch (Exception $e) {
            // Roll back the transaction on error
            $conn->rollBack();
            writeLog("❌ Exception: " . $e->getMessage());
            writeLog("Transaction annulée");
            
            // Delete team photo if it was uploaded
            if (!empty($teamPhotoPath) && file_exists($teamPhotoPath)) {
                unlink($teamPhotoPath);
                writeLog("Photo d'équipe supprimée en raison de l'échec de l'inscription");
            }
            
            // Redirect with specific error
            $errorCode = "team_registration_failed";
            $errorMessage = urlencode($e->getMessage());
            header("Location: ../view/register-participant.php?hackathon_id=$hackathonId&error=$errorCode&message=$errorMessage");
            exit();
        }
    }
} catch (PDOException $e) {
    writeLog("Erreur PDO: " . $e->getMessage());
    writeLog("Trace complète: " . $e->getTraceAsString());
    writeLog("Code PDO: " . $e->getCode());
    
    if (isset($conn) && $conn->inTransaction()) {
        $conn->rollBack();
        writeLog("Transaction annulée en raison d'une erreur PDO");
    }
    header("Location: ../view/register-participant.php?hackathon_id=$hackathonId&error=database_connection");
    exit();
} catch (Exception $e) {
    writeLog("Exception générale: " . $e->getMessage());
    writeLog("Trace complète: " . $e->getTraceAsString());
    
    if (isset($conn) && $conn->inTransaction()) {
        $conn->rollBack();
        writeLog("Transaction annulée en raison d'une exception générale");
    }
    header("Location: ../view/register-participant.php?hackathon_id=$hackathonId&error=general_error");
    exit();
}
?>