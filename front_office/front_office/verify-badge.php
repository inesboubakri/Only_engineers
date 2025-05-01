<?php
// verify-badge.php - Script pour vérifier l'authenticité des badges de hackathon

// Démarrer la session
session_start();

// Connexion à la base de données
require_once 'model/db_connection.php';

// Définir le chemin de base pour toutes les ressources
$basePath = '';
if (isset($_SERVER['HTTP_HOST'])) {
    // Obtenir l'URL de base actuelle
    $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https://" : "http://";
    $host = $_SERVER['HTTP_HOST'];
    // Chemin plus direct aux ressources
    $basePath = $protocol . $host . "/hachathon_user/hachathon_user/projet_webb";
}

// Journaliser le chemin de base pour debug
error_log("Base path: " . $basePath);

// Vérifier si un ID de badge est fourni
if (!isset($_GET['id']) || empty($_GET['id'])) {
    $status = 'error';
    $message = 'Aucun ID de badge fourni';
} else {
    // Récupérer l'ID du badge
    $badgeId = $_GET['id'];
    
    try {
        // Créer la connexion
        $conn = getConnection();
        
        // Mettre à jour les statistiques de scan
        $updateSql = "UPDATE badge_validations 
                     SET last_scanned = NOW(), scan_count = scan_count + 1 
                     WHERE badge_id = :badge_id";
        $updateStmt = $conn->prepare($updateSql);
        $updateStmt->bindParam(':badge_id', $badgeId);
        $updateStmt->execute();
        
        // Vérifier si le badge existe
        $sql = "SELECT bv.*, p.full_name as participant_name, h.name as hackathon_name, 
                       h.start_date, h.end_date, h.location, h.organizer, p.photo,
                       p.team_name, p.role
                FROM badge_validations bv
                JOIN participants p ON bv.participant_id = p.id
                JOIN hackathons h ON bv.hackathon_id = h.id
                WHERE bv.badge_id = :badge_id";
                
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':badge_id', $badgeId);
        $stmt->execute();
        
        // Vérifier si des résultats ont été trouvés
        if ($stmt->rowCount() > 0) {
            $badge = $stmt->fetch(PDO::FETCH_ASSOC);
            
            // Vérifier si le hackathon est terminé
            $currentDate = new DateTime();
            $endDate = new DateTime($badge['end_date']);
            $isExpired = $currentDate > $endDate;
            
            $status = 'valid';
            $message = 'Badge valide';
            
            // Formatter les dates
            $startDate = new DateTime($badge['start_date']);
            $formattedStartDate = $startDate->format('d/m/Y');
            $formattedEndDate = $endDate->format('d/m/Y');
            
            // Essayer de récupérer la photo du participant
            $photoUrl = $basePath . '/front_office/front_office/assets/profil.jpg'; // Photo par défaut
            
            if (!empty($badge['photo'])) {
                $photoPath = $basePath . '/front_office/ressources/participant_photos/' . basename($badge['photo']);
                error_log("Photo path: " . $photoPath);
                $photoUrl = $photoPath;
            }
            
        } else {
            $status = 'invalid';
            $message = 'Badge non valide ou inexistant';
        }
        
    } catch(PDOException $e) {
        $status = 'error';
        $message = 'Erreur de connexion à la base de données: ' . $e->getMessage();
        error_log("Database error: " . $e->getMessage());
    }
}

// En fonction du type de requête, renvoyer différentes réponses
$isApi = isset($_GET['api']) && $_GET['api'] == 'true';

if ($isApi) {
    // Pour les requêtes API, renvoyer du JSON
    header('Content-Type: application/json');
    
    $response = [
        'status' => $status,
        'message' => $message
    ];
    
    if (isset($badge)) {
        $response['badge'] = [
            'id' => $badge['badge_id'],
            'participant_name' => $badge['participant_name'],
            'hackathon_name' => $badge['hackathon_name'],
            'role' => $badge['role'] ?? 'Participant',
            'team' => $badge['team_name'] ?? 'N/A',
            'location' => $badge['location'],
            'period' => "$formattedStartDate - $formattedEndDate",
            'is_expired' => $isExpired,
            'scan_count' => $badge['scan_count'],
            'last_scanned' => $badge['last_scanned']
        ];
    }
    
    echo json_encode($response);
    exit;
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vérification de Badge - OnlyEngineers</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }
        
        body {
            font-family: 'Inter', sans-serif;
            background-color: #f5f7fa;
            color: #333;
            line-height: 1.6;
            padding: 20px;
        }
        
        .container {
            max-width: 500px;
            margin: 0 auto;
            padding: 20px;
            background-color: #fff;
            border-radius: 10px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        }
        
        .header {
            text-align: center;
            margin-bottom: 30px;
        }
        
        .logo {
            max-width: 180px;
            margin-bottom: 20px;
        }
        
        .title {
            font-size: 24px;
            font-weight: 700;
            color: #1f2937;
            margin-bottom: 8px;
        }
        
        .subtitle {
            font-size: 14px;
            color: #6b7280;
        }
        
        .status {
            text-align: center;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 30px;
        }
        
        .status-valid {
            background-color: #ecfdf5;
            color: #047857;
            border: 1px solid #a7f3d0;
        }
        
        .status-invalid {
            background-color: #fef2f2;
            color: #b91c1c;
            border: 1px solid #fecaca;
        }
        
        .status-error {
            background-color: #fff1f2;
            color: #9f1239;
            border: 1px solid #fecdd3;
        }
        
        .status-icon {
            font-size: 40px;
            margin-bottom: 10px;
        }
        
        .status-message {
            font-size: 18px;
            font-weight: 600;
            margin-bottom: 5px;
        }
        
        .status-note {
            font-size: 14px;
            opacity: 0.8;
        }
        
        .badge-details {
            margin-top: 20px;
        }
        
        .badge-content {
            display: flex;
            flex-direction: column;
            align-items: center;
        }
        
        .badge-photo {
            width: 100px;
            height: 100px;
            border-radius: 50%;
            object-fit: cover;
            border: 3px solid #6366f1;
            margin-bottom: 15px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
        }
        
        .badge-name {
            font-size: 20px;
            font-weight: 700;
            margin-bottom: 5px;
            text-align: center;
        }
        
        .badge-role {
            font-size: 14px;
            font-weight: 600;
            color: #6366f1;
            margin-bottom: 8px;
            text-align: center;
            padding: 3px 12px;
            background-color: #e0e7ff;
            border-radius: 20px;
            display: inline-block;
        }
        
        .badge-info {
            margin-top: 20px;
            width: 100%;
        }
        
        .info-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 12px;
            font-size: 14px;
        }
        
        .info-label {
            color: #6b7280;
            font-weight: 500;
        }
        
        .info-value {
            color: #1f2937;
            font-weight: 600;
            text-align: right;
        }
        
        .badge-expired {
            background-color: #fef2f2;
            color: #b91c1c;
            padding: 8px 12px;
            border-radius: 6px;
            margin-top: 15px;
            font-weight: 600;
            font-size: 14px;
            text-align: center;
        }
        
        .badge-active {
            background-color: #ecfdf5;
            color: #047857;
            padding: 8px 12px;
            border-radius: 6px;
            margin-top: 15px;
            font-weight: 600;
            font-size: 14px;
            text-align: center;
        }
        
        .link {
            color: #6366f1;
            text-decoration: none;
            font-weight: 500;
        }
        
        .link:hover {
            text-decoration: underline;
        }
        
        .footer {
            margin-top: 40px;
            text-align: center;
            font-size: 12px;
            color: #9ca3af;
        }
        
        .error-details {
            margin-top: 20px;
            padding: 15px;
            background-color: #f3f4f6;
            border-radius: 6px;
            font-family: monospace;
            font-size: 12px;
            white-space: pre-wrap;
            color: #4b5563;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <img src="<?php echo $basePath; ?>/front_office/front_office/assets/logo.svg" alt="OnlyEngineers Logo" class="logo">
            <h1 class="title">Vérification de Badge</h1>
            <p class="subtitle">Système de validation des badges pour hackathons</p>
        </div>
        
        <div class="status status-<?php echo $status; ?>">
            <div class="status-icon">
                <?php if ($status === 'valid'): ?>
                    ✓
                <?php elseif ($status === 'invalid'): ?>
                    ✕
                <?php else: ?>
                    !
                <?php endif; ?>
            </div>
            <div class="status-message">
                <?php echo $message; ?>
            </div>
            <?php if ($status === 'valid'): ?>
                <p class="status-note">
                    Ce badge est authentique et a été émis par OnlyEngineers.
                </p>
            <?php elseif ($status === 'invalid'): ?>
                <p class="status-note">
                    Ce badge n'est pas reconnu dans notre système.
                </p>
            <?php endif; ?>
        </div>
        
        <?php if (isset($badge)): ?>
            <div class="badge-details">
                <div class="badge-content">
                    <img src="<?php echo htmlspecialchars($photoUrl); ?>" alt="<?php echo htmlspecialchars($badge['participant_name']); ?>" class="badge-photo">
                    
                    <div class="badge-name"><?php echo htmlspecialchars($badge['participant_name']); ?></div>
                    <div class="badge-role"><?php echo htmlspecialchars($badge['role'] ?? 'Participant'); ?></div>
                    
                    <?php if (!empty($badge['team_name'])): ?>
                        <div class="badge-team">Équipe: <?php echo htmlspecialchars($badge['team_name']); ?></div>
                    <?php endif; ?>
                    
                    <div class="badge-info">
                        <div class="info-row">
                            <span class="info-label">Hackathon:</span>
                            <span class="info-value"><?php echo htmlspecialchars($badge['hackathon_name']); ?></span>
                        </div>
                        
                        <div class="info-row">
                            <span class="info-label">Période:</span>
                            <span class="info-value"><?php echo $formattedStartDate . ' - ' . $formattedEndDate; ?></span>
                        </div>
                        
                        <div class="info-row">
                            <span class="info-label">Lieu:</span>
                            <span class="info-value"><?php echo htmlspecialchars($badge['location']); ?></span>
                        </div>
                        
                        <div class="info-row">
                            <span class="info-label">Organisateur:</span>
                            <span class="info-value"><?php echo htmlspecialchars($badge['organizer']); ?></span>
                        </div>
                        
                        <div class="info-row">
                            <span class="info-label">ID du Badge:</span>
                            <span class="info-value"><?php echo htmlspecialchars($badge['badge_id']); ?></span>
                        </div>
                        
                        <div class="info-row">
                            <span class="info-label">Nombre de scans:</span>
                            <span class="info-value"><?php echo $badge['scan_count']; ?></span>
                        </div>
                    </div>
                    
                    <?php if ($isExpired): ?>
                        <div class="badge-expired">
                            Ce hackathon est terminé depuis le <?php echo $formattedEndDate; ?>
                        </div>
                    <?php else: ?>
                        <div class="badge-active">
                            Badge actif pour un hackathon en cours ou à venir
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>
        
        <div class="footer">
            <p>&copy; <?php echo date('Y'); ?> OnlyEngineers - Système de validation de badges</p>
            <p><a href="<?php echo $basePath; ?>/front_office/front_office/index.html" class="link">Retour au site principal</a></p>
        </div>
    </div>
</body>
</html>