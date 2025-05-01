<?php
// Script pour corriger la contrainte de clé étrangère qui cause l'erreur 150

require_once 'db_connection.php';

// Fichier de log pour tracer les opérations
$logFile = __DIR__ . '/fix_foreign_key.log';

function writeLog($message) {
    global $logFile;
    $timestamp = date('Y-m-d H:i:s');
    file_put_contents($logFile, "[$timestamp] $message" . PHP_EOL, FILE_APPEND);
}

writeLog("=== Début de la correction de la contrainte de clé étrangère ===");

try {
    // Connexion à la base de données
    $conn = getConnection();
    writeLog("Connexion à la base de données établie");
    
    // Étape 1: Obtenir des informations sur les tables
    writeLog("Vérification de la structure des tables users et participants");
    
    // Obtenir la clé primaire de la table users
    $pkQuery = $conn->query("SHOW KEYS FROM users WHERE Key_name = 'PRIMARY'");
    
    if (!$pkQuery) {
        throw new Exception("Impossible d'obtenir les clés de la table users. Vérifiez que la table existe.");
    }
    
    $pk = $pkQuery->fetch(PDO::FETCH_ASSOC);
    
    if (!$pk) {
        throw new Exception("Clé primaire non trouvée dans la table users");
    }
    
    $usersPrimaryKey = $pk['Column_name'];
    writeLog("Clé primaire de la table users identifiée: " . $usersPrimaryKey);
    
    // Obtenir les informations sur la colonne ID dans users
    $usersIdQuery = $conn->query("SHOW COLUMNS FROM users WHERE Field = '$usersPrimaryKey'");
    $usersId = $usersIdQuery->fetch(PDO::FETCH_ASSOC);
    
    if (!$usersId) {
        throw new Exception("Colonne $usersPrimaryKey non trouvée dans la table users");
    }
    
    writeLog("Structure de l'ID dans users: Type = " . $usersId['Type'] . ", Null = " . $usersId['Null'] . ", Key = " . $usersId['Key']);
    
    // Obtenir les informations sur la colonne user_id dans participants
    $participantsIdQuery = $conn->query("SHOW COLUMNS FROM participants WHERE Field = 'user_id'");
    $participantsId = $participantsIdQuery->fetch(PDO::FETCH_ASSOC);
    
    if (!$participantsId) {
        throw new Exception("Colonne user_id non trouvée dans la table participants");
    }
    
    writeLog("Structure de user_id dans participants: Type = " . $participantsId['Type'] . ", Null = " . $participantsId['Null'] . ", Key = " . $participantsId['Key']);
    
    // Identifier les contraintes existantes
    $fkQuery = $conn->query("
        SELECT CONSTRAINT_NAME
        FROM information_schema.TABLE_CONSTRAINTS
        WHERE TABLE_NAME = 'participants'
        AND CONSTRAINT_TYPE = 'FOREIGN KEY'
        AND TABLE_SCHEMA = DATABASE()
    ");
    
    $fkConstraints = $fkQuery->fetchAll(PDO::FETCH_COLUMN);
    writeLog("Contraintes de clé étrangère existantes: " . implode(", ", $fkConstraints ?: ["aucune"]));
    
    // Exécuter chaque opération individuellement sans utiliser de transaction
    
    // Étape 2: Supprimer toutes les contraintes de clé étrangère existantes
    foreach ($fkConstraints as $constraint) {
        $dropQuery = "ALTER TABLE participants DROP FOREIGN KEY `$constraint`";
        writeLog("Exécution: $dropQuery");
        try {
            $conn->exec($dropQuery);
            writeLog("Contrainte $constraint supprimée");
        } catch (PDOException $e) {
            writeLog("Erreur lors de la suppression de la contrainte $constraint: " . $e->getMessage());
            // Continue avec les autres opérations même si celle-ci échoue
        }
    }
    
    // Étape 3: S'assurer que les types de colonnes correspondent
    // S'assurer que user_id dans participants a exactement le même type que id dans users
    $alterUserIdQuery = "ALTER TABLE participants MODIFY user_id " . $usersId['Type'] . " NULL";
    writeLog("Modification du type de user_id: $alterUserIdQuery");
    try {
        $conn->exec($alterUserIdQuery);
        writeLog("Type de user_id modifié pour correspondre exactement à " . $usersPrimaryKey . " dans users");
    } catch (PDOException $e) {
        writeLog("Erreur lors de la modification du type: " . $e->getMessage());
        // Continue quand même
    }
    
    // Étape 4: Créer un index pour la clé étrangère si nécessaire
    $indexQuery = $conn->query("SHOW INDEX FROM participants WHERE Column_name = 'user_id'");
    $hasIndex = $indexQuery->rowCount() > 0;
    
    if (!$hasIndex) {
        $createIndexQuery = "CREATE INDEX idx_user_id ON participants(user_id)";
        writeLog("Création d'un index pour user_id: $createIndexQuery");
        try {
            $conn->exec($createIndexQuery);
            writeLog("Index créé pour user_id");
        } catch (PDOException $e) {
            writeLog("Erreur lors de la création de l'index: " . $e->getMessage());
            // Continue quand même
        }
    } else {
        writeLog("Un index existe déjà pour user_id, aucun besoin d'en créer un nouveau");
    }
    
    // Étape 5: Ajouter la nouvelle contrainte de clé étrangère
    $addFKQuery = "ALTER TABLE participants ADD CONSTRAINT fk_participant_user 
                  FOREIGN KEY (user_id) REFERENCES users($usersPrimaryKey) 
                  ON DELETE SET NULL ON UPDATE CASCADE";
    
    writeLog("Ajout de la nouvelle contrainte: $addFKQuery");
    try {
        $conn->exec($addFKQuery);
        writeLog("Nouvelle contrainte ajoutée avec succès");
    } catch (PDOException $e) {
        writeLog("Erreur lors de l'ajout de la contrainte: " . $e->getMessage());
        throw new Exception("Impossible d'ajouter la contrainte de clé étrangère: " . $e->getMessage());
    }
    
    // Vérifier la structure finale
    $finalCheck = $conn->query("
        SELECT * 
        FROM information_schema.TABLE_CONSTRAINTS 
        WHERE TABLE_NAME = 'participants'
        AND CONSTRAINT_TYPE = 'FOREIGN KEY'
        AND TABLE_SCHEMA = DATABASE()
    ");
    
    $finalConstraints = $finalCheck->fetchAll(PDO::FETCH_ASSOC);
    writeLog("Contraintes finales: " . print_r($finalConstraints, true));
    
    echo "<h1>Correction réussie</h1>";
    echo "<p>La contrainte de clé étrangère a été correctement établie entre les tables participants et users.</p>";
    echo "<p>La clé primaire identifiée dans la table users est: <strong>" . htmlspecialchars($usersPrimaryKey) . "</strong></p>";
    echo "<p>Vous pouvez maintenant enregistrer des participants avec ou sans compte utilisateur (user_id peut être NULL).</p>";
    
} catch (PDOException $e) {
    writeLog("Erreur PDO: " . $e->getMessage() . " (Code: " . $e->getCode() . ")");
    writeLog("Trace: " . $e->getTraceAsString());
    
    echo "<h1>Erreur lors de la correction</h1>";
    echo "<p>Une erreur de base de données est survenue: " . htmlspecialchars($e->getMessage()) . "</p>";
    echo "<p>Code d'erreur: " . $e->getCode() . "</p>";
    echo "<p>Consultez le fichier log pour plus de détails: fix_foreign_key.log</p>";
    
} catch (Exception $e) {
    writeLog("Exception: " . $e->getMessage());
    writeLog("Trace: " . $e->getTraceAsString());
    
    echo "<h1>Erreur lors de la correction</h1>";
    echo "<p>Une erreur est survenue: " . htmlspecialchars($e->getMessage()) . "</p>";
    echo "<p>Consultez le fichier log pour plus de détails: fix_foreign_key.log</p>";
}

writeLog("=== Fin de la correction de la contrainte de clé étrangère ===");
?>