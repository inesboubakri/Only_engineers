<?php
// generate-badge.php - Generates a printable HTML badge for hackathon participants

// Start the session
session_start();

// Database connection
require_once '../model/db_connection.php';

// Check if participant ID or team name is provided
$isTeamRequest = isset($_GET['team_name']) && !empty($_GET['team_name']);
$isIndividualRequest = isset($_GET['id']) && !empty($_GET['id']);

if (!$isIndividualRequest && !$isTeamRequest) {
    die("Error: Participant ID or Team Name is required");
}

try {
    // Create connection
    $conn = getConnection();
    
    $participants = [];
    
    if ($isIndividualRequest) {
        // Individual badge generation
        $participantId = intval($_GET['id']);
        
        // Get participant details with user and hackathon information
        $sql = "SELECT p.*, u.full_name as user_name, u.email,
                       h.name as hackathon_name, h.organizer, h.start_date, h.end_date, 
                       h.location, h.image as hackathon_image 
                FROM participants p
                LEFT JOIN users u ON p.user_id = u.user_id
                JOIN hackathons h ON p.hackathon_id = h.id
                WHERE p.id = :id";
                
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':id', $participantId, PDO::PARAM_INT);
        $stmt->execute();
        
        if ($stmt->rowCount() === 0) {
            die("Error: Participant not found");
        }
        
        $participants[] = $stmt->fetch(PDO::FETCH_ASSOC);
    } else {
        // Team badge generation
        $teamName = $_GET['team_name'];
        $hackathonId = isset($_GET['hackathon_id']) ? intval($_GET['hackathon_id']) : 0;
        
        if ($hackathonId <= 0) {
            die("Error: Hackathon ID is required for team badges");
        }
        
        // Get all team members
        $sql = "SELECT p.*, u.full_name as user_name, u.email,
                       h.name as hackathon_name, h.organizer, h.start_date, h.end_date, 
                       h.location, h.image as hackathon_image 
                FROM participants p
                LEFT JOIN users u ON p.user_id = u.user_id
                JOIN hackathons h ON p.hackathon_id = h.id
                WHERE p.team_name = :team_name 
                AND p.hackathon_id = :hackathon_id";
                
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':team_name', $teamName);
        $stmt->bindParam(':hackathon_id', $hackathonId);
        $stmt->execute();
        
        if ($stmt->rowCount() === 0) {
            die("Error: No team members found");
        }
        
        $participants = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    // Get hackathon details from first participant
    $firstParticipant = $participants[0];
    
    // Format dates for display
    $startDate = new DateTime($firstParticipant['start_date']);
    $endDate = new DateTime($firstParticipant['end_date']);
    $formattedStartDate = $startDate->format('M d, Y');
    $formattedEndDate = $endDate->format('M d, Y');
    $dateRange = $formattedStartDate . ' - ' . $formattedEndDate;
    
    // Get hackathon image
    $hackathonImagePath = "../ressources/";
    if (!empty($firstParticipant['hackathon_image']) && file_exists($hackathonImagePath . $firstParticipant['hackathon_image'])) {
        $hackathonImageUrl = $hackathonImagePath . $firstParticipant['hackathon_image'];
    } else {
        $hackathonImageUrl = "../ressources/cybersecurity.png"; // Default image
    }
    
    // Créer la table badge_validations si elle n'existe pas
    try {
        $createTableSQL = "CREATE TABLE IF NOT EXISTS `badge_validations` (
          `id` int(11) NOT NULL AUTO_INCREMENT,
          `badge_id` varchar(50) NOT NULL,
          `participant_id` int(11) NOT NULL,
          `hackathon_id` int(11) NOT NULL,
          `generated_date` datetime NOT NULL,
          `last_scanned` datetime DEFAULT NULL,
          `scan_count` int(11) DEFAULT 0,
          PRIMARY KEY (`id`),
          UNIQUE KEY `badge_id` (`badge_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
        
        $conn->exec($createTableSQL);
    } catch (PDOException $e) {
        // Log l'erreur mais continuer
        error_log("Erreur lors de la création de la table badge_validations: " . $e->getMessage());
    }
    
} catch(PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hackathon Badges - <?php echo htmlspecialchars($firstParticipant['hackathon_name']); ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        @media print {
            body {
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
                margin: 0;
                padding: 0;
            }
            
            .no-print {
                display: none !important;
            }
            
            .badge-container {
                break-inside: avoid;
                page-break-inside: avoid;
            }
            
            .badge-card {
                box-shadow: none !important;
                border: 1px solid #ddd;
            }
            
            .page-break {
                page-break-before: always;
            }
        }
        
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }
        
        body {
            font-family: 'Inter', sans-serif;
            background-color: #f5f7fa;
            padding: 20px;
            color: #333;
        }
        
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }
        
        .controls {
            margin-bottom: 30px;
            text-align: center;
        }
        
        .btn {
            background-color: #6366f1;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 8px;
            font-weight: 600;
            font-size: 16px;
            cursor: pointer;
            transition: background-color 0.3s, transform 0.2s;
            margin: 0 10px;
        }
        
        .btn:hover {
            background-color: #4f46e5;
            transform: translateY(-2px);
        }
        
        .btn-secondary {
            background-color: #9ca3af;
        }
        
        .btn-secondary:hover {
            background-color: #6b7280;
        }
        
        .badge-container {
            display: flex;
            justify-content: center;
            flex-wrap: wrap;
            gap: 30px;
            margin-bottom: 40px;
        }
        
        .badge-card {
            width: 92mm;  /* ID-1 format width */
            height: 154mm; /* ID-1 format height */
            background: #fff;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
            position: relative;
            display: flex;
            flex-direction: column;
            margin-bottom: 30px;
        }
        
        .badge-header {
            background: linear-gradient(135deg, #6366f1, #4f46e5);
            color: white;
            padding: 15px;
            text-align: center;
            position: relative;
        }
        
        .badge-title {
            font-size: 18px;
            font-weight: 700;
            margin-bottom: 5px;
        }
        
        .badge-subtitle {
            font-size: 14px;
            opacity: 0.9;
        }
        
        .badge-content {
            padding: 15px;
            flex: 1;
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
            font-size: 16px;
            font-weight: 600;
            color: #6366f1;
            margin-bottom: 10px;
            text-align: center;
            padding: 3px 12px;
            background-color: #e0e7ff;
            border-radius: 20px;
            display: inline-block;
        }
        
        .badge-team {
            font-size: 14px;
            color: #4b5563;
            margin-bottom: 15px;
            text-align: center;
        }
        
        .badge-details {
            width: 100%;
            border-top: 1px solid #e5e7eb;
            padding-top: 15px;
            margin-top: auto;
        }
        
        .detail-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 8px;
            font-size: 12px;
        }
        
        .detail-label {
            color: #6b7280;
            font-weight: 500;
        }
        
        .detail-value {
            color: #111827;
            font-weight: 600;
            text-align: right;
        }
        
        .badge-footer {
            background: #f9fafb;
            padding: 12px;
            text-align: center;
            border-top: 1px solid #e5e7eb;
        }
        
        .badge-id {
            font-size: 12px;
            color: #6b7280;
            font-weight: 500;
        }
        
        .badge-qr {
            width: 100px;
            height: 100px;
            margin: 10px auto;
            background-repeat: no-repeat;
            background-position: center;
            background-size: contain;
        }
        
        .event-logo {
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 15px;
        }
        
        .event-logo img {
            height: 40px;
            max-width: 80%;
            object-fit: contain;
        }
        
        .badge-watermark {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%) rotate(-45deg);
            font-size: 100px;
            font-weight: 900;
            color: rgba(99, 102, 241, 0.05);
            pointer-events: none;
            white-space: nowrap;
        }
        
        /* Horizontal badge style */
        .badge-horizontal {
            width: 138mm;
            height: 92mm;
            flex-direction: row;
        }
        
        .badge-horizontal .badge-header {
            width: 35%;
            height: 100%;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }
        
        .badge-horizontal .badge-content {
            width: 65%;
            padding: 20px;
        }
        
        .badge-horizontal .event-logo {
            margin-top: auto;
            margin-bottom: 10px;
        }
        
        .badge-horizontal .badge-photo {
            width: 80px;
            height: 80px;
        }
        
        .instructions {
            max-width: 800px;
            margin: 0 auto 30px;
            background: white;
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }
        
        .instructions h2 {
            margin-bottom: 15px;
            color: #4b5563;
        }
        
        .instructions p {
            margin-bottom: 10px;
            line-height: 1.6;
            color: #6b7280;
        }
        
        .instructions ul {
            margin-bottom: 15px;
            padding-left: 20px;
        }
        
        .instructions li {
            margin-bottom: 5px;
            line-height: 1.6;
            color: #6b7280;
        }
        
        @media (max-width: 768px) {
            .badge-container {
                flex-direction: column;
                align-items: center;
            }
        }
        
        .organizer-info {
            font-size: 12px;
            opacity: 0.7;
            margin-top: 5px;
        }
        
        .participant-heading {
            text-align: center;
            margin: 40px 0 20px;
            color: #4b5563;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="instructions no-print">
            <h2>Your Hackathon Badges are Ready!</h2>
            <p>Below are the official badges for the <?php echo htmlspecialchars($firstParticipant['hackathon_name']); ?> hackathon. 
               You can print them or save as PDF.</p>
            <?php if (count($participants) > 1): ?>
            <p><strong>Team badges:</strong> We've generated badges for all team members. Each participant's badge is shown below.</p>
            <?php endif; ?>
            <ul>
                <li>The vertical badge is designed for lanyards and display purposes.</li>
                <li>The horizontal badge is designed as an alternative format.</li>
                <li>Each badge contains a unique QR code that can be scanned for verification.</li>
            </ul>
            <p>To print your badges:</p>
            <ol>
                <li>Click the "Print Badges" button below</li>
                <li>In the print dialog, select "Save as PDF" if you want a digital copy</li>
                <li>Make sure to print at 100% scale (no shrinking to fit)</li>
                <li>For best results, use card stock paper</li>
            </ol>
        </div>
        
        <div class="controls no-print">
            <button class="btn" onclick="window.print()">Print Badges</button>
            <button class="btn btn-secondary" onclick="window.history.back()">Go Back</button>
        </div>
        
        <?php foreach ($participants as $index => $participant): ?>
        <?php 
            // Generate a unique badge ID for each participant
            $badgeId = 'OE-' . ($participant['id'] ?? $index) . '-' . date('YmdHis');
            
            // Save badge ID in database
            try {
                $badgeSql = "INSERT INTO badge_validations (badge_id, participant_id, hackathon_id, generated_date) 
                             VALUES (:badge_id, :participant_id, :hackathon_id, NOW())
                             ON DUPLICATE KEY UPDATE generated_date = NOW()";
                             
                $badgeStmt = $conn->prepare($badgeSql);
                $badgeStmt->bindParam(':badge_id', $badgeId);
                $badgeStmt->bindParam(':participant_id', $participant['id']);
                $badgeStmt->bindParam(':hackathon_id', $participant['hackathon_id']);
                $badgeStmt->execute();
            } catch (PDOException $e) {
                // Log l'erreur mais continuer la génération du badge
                error_log("Erreur lors de l'enregistrement du badge: " . $e->getMessage());
            }
            
            // Generate QR code data
            // Utiliser HTTP_HOST pour obtenir le nom d'hôte de la requête actuelle
            $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https://" : "http://";
            $host = $_SERVER['HTTP_HOST'];
            
            // Construire l'URL de base à partir de l'URL actuelle
            $currentUrl = $protocol . $host;
            
            // Obtenir le chemin de base du projet
            $scriptPath = $_SERVER['SCRIPT_NAME'];
            $basePath = dirname(dirname(dirname(dirname($scriptPath)))); // Remonter de 4 niveaux depuis le script actuel
            
            // Construire l'URL complète pour la vérification du badge avec un chemin relatif plus court
            $qrCodeData = $currentUrl . $basePath . "/front_office/front_office/verify-badge.php?id=" . $badgeId;
            
            // Débogage: Enregistrer l'URL du QR code générée
            error_log("QR Code URL généré: " . $qrCodeData);
            
            // Determine participant role (if available)
            $role = !empty($participant['role']) ? $participant['role'] : 'Participant';
            
            // Determine team info (if available)
            $teamInfo = !empty($participant['team_name']) ? 'Team: ' . $participant['team_name'] : 'Individual Participant';
            
            // Get participant photo with improved photo path handling
            $photoUrl = "../assets/profil.jpg"; // This is the default we want to avoid
            
            // Debugging: Log which participant we're looking for
            error_log("Finding photo for participant ID: " . ($participant['id'] ?? 'unknown') . ", User ID: " . ($participant['user_id'] ?? 'unknown'));
            
            // Check for photo directly from database field first
            if (!empty($participant['photo'])) {
                // Array of possible base paths to check
                $basePaths = [
                    "../ressources/participant_photos/",
                    "../../ressources/participant_photos/",
                    "../ressources/temp_uploads/team_photos/",
                    "../../ressources/temp_uploads/team_photos/",
                    "../front_office/ressources/participant_photos/",
                    "../../front_office/ressources/participant_photos/",
                    "../../projet_webb/front_office/ressources/participant_photos/"
                ];
                
                // If photo field contains a full path, extract just the filename
                $photoFilename = basename($participant['photo']);
                
                // Try each base path with the photo filename
                $photoFound = false;
                foreach ($basePaths as $basePath) {
                    $fullPath = $basePath . $photoFilename;
                    error_log("Checking path: " . $fullPath);
                    if (file_exists($fullPath)) {
                        $photoUrl = $fullPath;
                        error_log("Photo found at: " . $fullPath);
                        $photoFound = true;
                        break;
                    }
                }
                
                // If photo was not found using the stored filename, try alternate directories
                if (!$photoFound) {
                    error_log("Could not find photo using stored filename: " . $photoFilename);
                }
            }
            
            // If still using default photo, try more aggressively with various naming patterns
            if ($photoUrl === "../assets/profil.jpg") {
                $photoExtensions = ['jpg', 'jpeg', 'png', 'gif'];
                $participantId = $participant['id'] ?? '';
                $userId = $participant['user_id'] ?? '';
                $teamName = $participant['team_name'] ?? '';
                
                $basePaths = [
                    "../ressources/participant_photos/",
                    "../../ressources/participant_photos/",
                    "../ressources/temp_uploads/team_photos/",
                    "../../ressources/temp_uploads/team_photos/",
                    "../temp_uploads/participant_photos/",
                    "../../temp_uploads/participant_photos/",
                    "../front_office/ressources/participant_photos/",
                    "../../front_office/ressources/participant_photos/"
                ];
                
                // Array of possible filename patterns
                $filenamePatterns = [];
                
                // Add various filename patterns based on available IDs
                if (!empty($participantId)) {
                    $filenamePatterns[] = "participant_" . $participantId;
                    $filenamePatterns[] = "p_" . $participantId;
                    $filenamePatterns[] = $participantId;
                }
                
                if (!empty($userId)) {
                    $filenamePatterns[] = "user_" . $userId;
                    $filenamePatterns[] = "u_" . $userId;
                    $filenamePatterns[] = $userId;
                }
                
                if (!empty($participantId) && !empty($userId)) {
                    $filenamePatterns[] = $participantId . "_" . $userId;
                    $filenamePatterns[] = "p" . $participantId . "_u" . $userId;
                }
                
                if (!empty($teamName)) {
                    $filenamePatterns[] = "team_" . $teamName;
                    $filenamePatterns[] = $teamName;
                    
                    if (!empty($participantId)) {
                        $filenamePatterns[] = $teamName . "_" . $participantId;
                        $filenamePatterns[] = "team_" . $teamName . "_" . $participantId;
                    }
                }
                
                // Try each combination of base path, filename pattern, and extension
                $photoFound = false;
                foreach ($basePaths as $basePath) {
                    foreach ($filenamePatterns as $pattern) {
                        foreach ($photoExtensions as $ext) {
                            $fullPath = $basePath . $pattern . "." . $ext;
                            error_log("Checking path: " . $fullPath);
                            if (file_exists($fullPath)) {
                                $photoUrl = $fullPath;
                                error_log("Photo found using pattern: " . $fullPath);
                                $photoFound = true;
                                break 3;  // Break out of all three loops
                            }
                        }
                    }
                }
                
                // As a last resort, check if there are any image files with participant ID in the name
                if (!$photoFound && !empty($participantId)) {
                    foreach ($basePaths as $basePath) {
                        if (is_dir($basePath)) {
                            $files = scandir($basePath);
                            foreach ($files as $file) {
                                // Skip directories
                                if (is_dir($basePath . $file)) {
                                    continue;
                                }
                                
                                // Check if the file contains the participant ID and has an image extension
                                if (strpos($file, $participantId) !== false) {
                                    $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
                                    if (in_array($ext, $photoExtensions)) {
                                        $photoUrl = $basePath . $file;
                                        error_log("Found photo by scanning directory: " . $photoUrl);
                                        $photoFound = true;
                                        break 2;
                                    }
                                }
                            }
                        }
                    }
                }
            }
            
            // If still using default, log that we couldn't find any photo
            if ($photoUrl === "../assets/profil.jpg") {
                error_log("WARNING: Could not find registration photo for participant ID: " . ($participant['id'] ?? 'unknown') . 
                          ", Using default profile photo instead.");
            }
            
            // Use full_name from participants table if user_name is not available
            $participantName = !empty($participant['user_name']) ? $participant['user_name'] : $participant['full_name'];
        ?>
        
        <?php if ($index > 0): ?>
        <div class="participant-heading no-print">
            <h3>Badge for: <?php echo htmlspecialchars($participantName); ?></h3>
        </div>
        <?php endif; ?>
        
        <div class="badge-container <?php echo $index > 0 ? 'page-break' : ''; ?>">
            <!-- Vertical Badge -->
            <div class="badge-card">
                
                <div class="badge-header">
                    <div>
                        <div class="badge-title"><?php echo htmlspecialchars($participant['hackathon_name']); ?></div>
                        <div class="badge-subtitle"><?php echo $dateRange; ?></div>
                    </div>
                </div>
                
                <div class="badge-content">
                    <img src="<?php echo htmlspecialchars($photoUrl); ?>" alt="<?php echo htmlspecialchars($participantName); ?>" class="badge-photo">
                    
                    <div class="badge-name"><?php echo htmlspecialchars($participantName); ?></div>
                    <div class="badge-role"><?php echo htmlspecialchars($role); ?></div>
                    <div class="badge-team"><?php echo htmlspecialchars($teamInfo); ?></div>
                    
                    <div class="event-logo">
                        <img src="<?php echo htmlspecialchars($hackathonImageUrl); ?>" alt="Event Logo">
                    </div>
                    
                    <div class="badge-details">
                        <div class="detail-row">
                            <span class="detail-label">Location:</span>
                            <span class="detail-value"><?php echo htmlspecialchars($participant['location']); ?></span>
                        </div>
                        
                        <div class="detail-row">
                            <span class="detail-label">Organizer:</span>
                            <span class="detail-value"><?php echo htmlspecialchars($participant['organizer']); ?></span>
                        </div>
                    </div>
                </div>
                
                <div class="badge-footer">
                    <div class="badge-qr" style="background-image: url('http://api.qrserver.com/v1/create-qr-code/?size=100x100&data=<?php echo urlencode($qrCodeData); ?>')"></div>
                    <div class="badge-id">ID: <?php echo $badgeId; ?></div>
                    <div class="organizer-info">OnlyEngineers</div>
                </div>
            </div>
            
            <!-- Horizontal Badge -->
            <div class="badge-card badge-horizontal">
                <div class="badge-header">
                    <div>
                        <div class="badge-title"><?php echo htmlspecialchars($participant['hackathon_name']); ?></div>
                        <div class="badge-subtitle"><?php echo $dateRange; ?></div>
                    </div>
                    
                    <div class="event-logo">
                        <img src="<?php echo htmlspecialchars($hackathonImageUrl); ?>" alt="Event Logo">
                    </div>
                </div>
                
                <div class="badge-content">
                    
                    <img src="<?php echo htmlspecialchars($photoUrl); ?>" alt="<?php echo htmlspecialchars($participantName); ?>" class="badge-photo">
                    
                    <div class="badge-name"><?php echo htmlspecialchars($participantName); ?></div>
                    <div class="badge-role"><?php echo htmlspecialchars($role); ?></div>
                    <div class="badge-team"><?php echo htmlspecialchars($teamInfo); ?></div>
                    
                    <div class="badge-details">
                        <div class="detail-row">
                            <span class="detail-label">Location:</span>
                            <span class="detail-value"><?php echo htmlspecialchars($participant['location']); ?></span>
                        </div>
                        
                        <div class="detail-row">
                            <span class="detail-label">Organizer:</span>
                            <span class="detail-value"><?php echo htmlspecialchars($participant['organizer']); ?></span>
                        </div>
                        
                        <div class="detail-row">
                            <span class="detail-label">Badge ID:</span>
                            <span class="detail-value"><?php echo $badgeId; ?></span>
                        </div>
                    </div>
                    
                    <div class="badge-qr" style="background-image: url('http://api.qrserver.com/v1/create-qr-code/?size=100x100&data=<?php echo urlencode($qrCodeData); ?>'); margin-top: 10px;"></div>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
</body>
</html>