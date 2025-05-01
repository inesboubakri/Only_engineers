<?php
session_start();
require_once '../model/db_connection.php';

// Check if hackathon ID is provided in URL
if (!isset($_GET['id'])) {
    header("Location: hackathons.php");
    exit();
}

$hackathonId = intval($_GET['id']);

try {
    // Create connection
    $conn = getConnection();
    
    // Fetch hackathon details
    $sql = "SELECT * FROM hackathons WHERE id = :id";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(":id", $hackathonId);
    $stmt->execute();
    
    $hackathon = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$hackathon) {
        header("Location: hackathons.php");
        exit();
    }
    
    // Format dates and times for display
    $startDate = new DateTime($hackathon['start_date'] . ' ' . $hackathon['start_time']);
    $endDate = new DateTime($hackathon['end_date'] . ' ' . $hackathon['end_time']);
    $formattedStartDate = $startDate->format('F j, Y \a\t g:i A');
    $formattedEndDate = $endDate->format('F j, Y \a\t g:i A');
    
    // Handle image path
    $imageFilename = !empty($hackathon['image']) ? $hackathon['image'] : 'default_hackathon.jpg';
    // Remove any potential duplicate 'ressources/' in the path
    $imageFilename = str_replace('ressources/', '', $imageFilename);
    $imagePath = '../ressources/' . $imageFilename;
    $defaultImage = '../ressources/cybersecurity.png';
    
    // Check if image exists
    $fullImagePath = __DIR__ . '/../ressources/' . $imageFilename;
    if (!file_exists($fullImagePath)) {
        $imagePath = $defaultImage;
    }
    
    // Count total participants
    $totalParticipants = 0;
    $participantsSql = "SELECT COUNT(*) as count FROM participants WHERE hackathon_id = :hackathon_id";
    $participantsStmt = $conn->prepare($participantsSql);
    $participantsStmt->bindParam(":hackathon_id", $hackathonId);
    $participantsStmt->execute();
    $participantsResult = $participantsStmt->fetch(PDO::FETCH_ASSOC);
    $totalParticipants = $participantsResult['count'];
    
    // Check if the current user is already registered for this hackathon
    $isUserRegistered = false;
    if (isset($_SESSION['user_id'])) {
        $userId = $_SESSION['user_id'];
        $checkRegistrationSql = "SELECT * FROM participants WHERE hackathon_id = :hackathon_id AND user_id = :user_id";
        $checkRegistrationStmt = $conn->prepare($checkRegistrationSql);
        $checkRegistrationStmt->bindParam(":hackathon_id", $hackathonId);
        $checkRegistrationStmt->bindParam(":user_id", $userId);
        $checkRegistrationStmt->execute();
        $isUserRegistered = ($checkRegistrationStmt->rowCount() > 0);
    }
    
} catch(PDOException $e) {
    echo "Connection failed: " . $e->getMessage();
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($hackathon['name']); ?> - Hackathon Details</title>
    <link rel="stylesheet" href="../view/styles.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Leaflet CSS for OpenStreetMap -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    
    <!-- Ajout du script Leaflet pour les cartes -->
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
            
    <!-- OpenCage Geocoding API pour la conversion d'adresses en coordonnées -->
    <script src="https://cdn.jsdelivr.net/npm/opencage-api-client"></script>
    
    <style>
        /* Animation d'entrée pour toute la page */
        @keyframes fadeInPage {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .app-container {
            animation: fadeInPage 0.8s ease-out forwards;
        }
        
        /* Animation séquentielle pour les éléments de la page */
        .hackathon-header,
        .description-section,
        .sidebar-section,
        .location-section,
        .related-hackathons {
            opacity: 0;
            animation: fadeInPage 0.8s ease-out forwards;
        }
        
        .hackathon-header { animation-delay: 0.2s; }
        .description-section { animation-delay: 0.4s; }
        .sidebar-section { animation-delay: 0.6s; }
        .location-section { animation-delay: 0.8s; }
        .related-hackathons { animation-delay: 1s; }

        .oe-terminal {
            position: absolute; /* Changer de fixed à absolute pour permettre au terminal de disparaître au défilement */
            top: 28mm; 
            right: 181px;
            width: 240px;
            background: rgba(8, 12, 18, 0.98);
            border: 1px solid rgba(0, 180, 216, 0.8);
            border-radius: 12px;
            padding: 18px;
            box-shadow: 0 0 30px rgba(0, 180, 216, 0.3),
                        inset 0 0 20px rgba(0, 180, 216, 0.15);
            font-family: 'Fira Code', 'IBM Plex Mono', monospace;
            font-size: 0.75rem;
            color: rgba(0, 180, 216, 0.95);
            line-height: 1.6;
            z-index: 10000;
            overflow: hidden;
            backdrop-filter: blur(3px);
            transform-style: preserve-3d;
            /* Ajout de l'animation pour le terminal */
            animation: terminalSlideIn 1.2s cubic-bezier(0.2, 1, 0.3, 1) forwards;
            transform: translateX(300px);
        }
        
        @keyframes terminalSlideIn {
            from {
                opacity: 0;
                transform: translateX(300px);
            }
            to {
                opacity: 1;
                transform: translateX(0);
            }
        }

        .oe-terminal::before {
            content: "";
            position: absolute;
            top: -2px;
            left: -2px;
            right: -2px;
            bottom: -2px;
            border: 2px solid transparent;
            border-image: linear-gradient(135deg, 
                          rgba(0, 180, 216, 0.8) 0%, 
                          rgba(72, 202, 228, 0.5) 50%,
                          transparent 100%);
            border-image-slice: 1;
            border-radius: 12px;
            pointer-events: none;
            animation: border-glow 8s linear infinite;
        }

        .oe-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
            padding-bottom: 8px;
            border-bottom: 1px solid rgba(72, 202, 228, 0.3);
        }

        .oe-logo {
            font-weight: 700;
            font-size: 0.8rem;
            letter-spacing: 1px;
            background: linear-gradient(90deg, #00B4D8, #48CAE4);
            -webkit-background-clip: text;
            background-clip: text;
            color: transparent;
            text-shadow: 0 0 12px rgba(0, 180, 216, 0.4);
        }

        .oe-version {
            font-size: 0.65rem;
            color: rgba(142, 142, 147, 0.7);
            letter-spacing: 0.3px;
        }

        .oe-body {
            position: relative;
            height: 120px;
            overflow: hidden;
        }

        .oe-code-line {
            position: absolute;
            width: 100%;
            white-space: nowrap;
            overflow: hidden;
            opacity: 0;
            animation: oe-line-fade 14s linear infinite;
            text-shadow: 0 0 10px rgba(0, 180, 216, 0.25);
        }

        .oe-code-line:nth-child(1) { animation-delay: 0.5s; top: 0; }
        .oe-code-line:nth-child(2) { animation-delay: 2s; top: 24px; color: rgba(72, 202, 228, 0.95); }
        .oe-code-line:nth-child(3) { animation-delay: 4s; top: 48px; color: rgba(0, 200, 150, 0.95); }
        .oe-code-line:nth-child(4) { animation-delay: 6s; top: 72px; }
        .oe-code-line:nth-child(5) { animation-delay: 8s; top: 96px; }

        .oe-cursor {
            display: inline-block;
            width: 8px;
            height: 14px;
            background: linear-gradient(to bottom, #00B4D8, #90E0EF);
            animation: oe-blink 1.1s step-end infinite, oe-cursor-pulse 2s ease-in-out infinite;
            vertical-align: middle;
            margin-left: 3px;
            border-radius: 2px;
        }

        .oe-scan {
            position: absolute;
            left: 0;
            right: 0;
            height: 1px;
            background: linear-gradient(to right, 
                      transparent, 
                      rgba(72, 202, 228, 0.6), 
                      transparent);
            box-shadow: 0 0 15px rgba(72, 202, 228, 0.4);
            animation: oe-scan 8s linear infinite;
        }

        .oe-footer {
            display: flex;
            justify-content: space-between;
            margin-top: 15px;
            font-size: 0.65rem;
            color: rgba(142, 142, 147, 0.6);
            letter-spacing: 0.5px;
        }

        .oe-status {
            display: flex;
            align-items: center;
        }

        .oe-led {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            background: linear-gradient(to bottom right, #00B4D8, #00FF9D);
            margin-right: 6px;
            animation: oe-pulse 2.5s ease-in-out infinite;
            box-shadow: 0 0 10px rgba(0, 255, 157, 0.5);
        }

        .oe-engineer-icon {
            font-size: 0.8rem;
            animation: oe-float 4s ease-in-out infinite;
        }

        @keyframes oe-line-fade {
            0% { opacity: 0; transform: translateX(-15px); }
            10% { opacity: 1; transform: translateX(0); }
            85% { opacity: 1; transform: translateX(0); }
            100% { opacity: 0; transform: translateX(15px); }
        }

        @keyframes oe-blink {
            0%, 100% { opacity: 0; }
            50% { opacity: 1; }
        }

        @keyframes oe-scan {
            0% { top: -10px; opacity: 0; }
            20% { opacity: 1; }
            80% { opacity: 1; }
            100% { top: 130px; opacity: 0; }
        }

        @keyframes oe-pulse {
            0%, 100% { transform: scale(1); opacity: 0.9; }
            50% { transform: scale(1.2); opacity: 1; }
        }

        @keyframes border-glow {
            0% { opacity: 0.7; }
            50% { opacity: 0.3; }
            100% { opacity: 0.7; }
        }

        @keyframes oe-cursor-pulse {
            0%, 100% { box-shadow: 0 0 5px rgba(144, 224, 239, 0.5); }
            50% { box-shadow: 0 0 15px rgba(144, 224, 239, 0.8); }
        }

        @keyframes oe-float {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-5px); }
        }

        /* Signature OnlyEngineers */
        .oe-signature {
            position: absolute;
            bottom: 12px;
            right: 15px;
            font-size: 0.6rem;
            color: rgba(72, 202, 228, 0.5);
            letter-spacing: 1px;
        }


        /* Main container styling */
        .hackathon-details-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }
        
        /* Header with image */
        .hackathon-header {
            display: flex;
            align-items: flex-start;
            margin-bottom: 30px;
            gap: 30px;
        }
        
        .hackathon-image {
            width: 180px;
            height: 180px;
            object-fit: cover;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }
        
        .hackathon-title-area {
            flex: 1;
        }
        
        .hackathon-title-area h1 {
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 10px;
            color: #333;
        }
        
        .organizer {
            font-size: 1.1rem;
            color: #6366f1;
            margin-bottom: 15px;
            font-weight: 500;
        }
        
        .dates-location {
            display: flex;
            flex-direction: column;
            gap: 15px;
            margin-bottom: 20px;
        }
        
        .date-badge, .location-badge {
            display: flex;
            align-items: center;
            background-color: #f8f9fa;
            padding: 8px 15px;
            border-radius: 20px;
            font-size: 0.95rem;
            color: #4b5563;
            gap: 8px;
            width: 14cm;
            max-width: 100%;
        }
        
        .location-badge {
            flex-wrap: wrap;
            max-width: 350px;
            word-break: break-word;
        }
        
        .location-badge svg {
            flex-shrink: 0;
            margin-right: 5px;
        }
        
        .location-text {
            line-height: 1.4;
            width: calc(100% - 25px); /* Largeur disponible moins l'icône */
        }
        
        .date-badge svg, .location-badge svg {
            width: 16px;
            height: 16px;
            color: #6366f1;
        }
        
        .register-btn {
            background-color: #6366f1;
            color: white;
            border: none;
            padding: 12px 25px;
            border-radius: 8px;
            font-weight: 600;
            font-size: 1rem;
            cursor: pointer;
            transition: background-color 0.3s, transform 0.2s;
        }
        
        .register-btn:hover {
            background-color: #4f46e5;
            transform: translateY(-2px);
        }

        .register-btn:disabled {
            background-color: #9ca3af;
            cursor: not-allowed;
            transform: none;
        }
        
        .register-btn:disabled:hover {
            background-color: #9ca3af;
            transform: none;
        }
        
        /* Description and Details */
        .hackathon-content {
            display: grid;
            grid-template-columns: 7fr 3fr;
            gap: 30px;
            margin-bottom: 40px;
        }
        
        .description-section {
            background-color: white;
            border-radius: 12px;
            padding: 25px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        }
        
        .section-title {
            font-size: 1.5rem;
            font-weight: 600;
            margin-bottom: 15px;
            color: #333;
            border-bottom: 2px solid #f1f5f9;
            padding-bottom: 10px;
        }
        
        .description {
            font-size: 1rem;
            line-height: 1.6;
            color: #4b5563;
            white-space: pre-line;
        }
        
        .sidebar-section {
            background-color: white;
            border-radius: 12px;
            padding: 25px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
            position: sticky;
            top: 20px;
            align-self: flex-start;
        }
        
        .detail-row {
            margin-bottom: 15px;
            display: flex;
            justify-content: space-between;
        }
        
        .detail-label {
            font-weight: 500;
            color: #4b5563;
        }
        
        .detail-value {
            color: #111827;
            font-weight: 600;
        }
        
        .skill-tags {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            margin-top: 5px;
        }
        
        .skill-tag {
            background-color: #e0e7ff;
            color: #4338ca;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 500;
        }
        
        /* Map Section */
        .location-section {
            grid-column: 1 / -1;
            background-color: white;
            border-radius: 12px;
            padding: 25px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        }
        
        #map-container {
            height: 400px;
            border-radius: 8px;
            overflow: hidden;
            margin-top: 15px;
        }
        
        /* Related Hackathons */
        .related-hackathons {
            margin-top: 40px;
        }
        
        .related-title {
            font-size: 1.5rem;
            font-weight: 600;
            margin-bottom: 20px;
            color: #333;
        }
        
        .related-cards {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 20px;
        }
        
        .related-card {
            background-color: white;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
            transition: transform 0.2s, box-shadow 0.2s;
            display: flex;
            flex-direction: column;
        }
        
        .related-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 15px rgba(0, 0, 0, 0.1);
        }
        
        .related-card-image {
            height: 160px;
            object-fit: cover;
        }
        
        .related-card-content {
            padding: 15px;
            flex: 1;
            display: flex;
            flex-direction: column;
        }
        
        .related-card-title {
            font-size: 1.1rem;
            font-weight: 600;
            margin-bottom: 8px;
            color: #333;
        }
        
        .related-card-organizer {
            font-size: 0.9rem;
            color: #6366f1;
            margin-bottom: 10px;
        }
        
        .related-card-date {
            font-size: 0.85rem;
            color: #6b7280;
            margin-bottom: 15px;
        }
        
        .view-details-btn {
            background-color: transparent;
            color: #6366f1;
            border: 1px solid #6366f1;
            padding: 8px 15px;
            border-radius: 6px;
            font-weight: 500;
            font-size: 0.9rem;
            cursor: pointer;
            transition: background-color 0.3s;
            margin-top: auto;
            text-align: center;
            text-decoration: none;
            display: block;
        }
        
        .view-details-btn:hover {
            background-color: #6366f1;
            color: white;
        }

        /* Dark theme adjustments */
        :root[data-theme="dark"] .hackathon-title-area h1,
        :root[data-theme="dark"] .section-title, 
        :root[data-theme="dark"] .related-title {
            color: #e2e8f0;
        }
        
        :root[data-theme="dark"] .description-section,
        :root[data-theme="dark"] .sidebar-section,
        :root[data-theme="dark"] .location-section,
        :root[data-theme="dark"] .related-card {
            background-color: #1e293b;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.3);
        }
        
        :root[data-theme="dark"] .description,
        :root[data-theme="dark"] .detail-label,
        :root[data-theme="dark"] .detail-value {
            color: #e2e8f0;
        }
        
        :root[data-theme="dark"] .section-title {
            border-bottom-color: #334155;
        }
        
        :root[data-theme="dark"] .date-badge, 
        :root[data-theme="dark"] .location-badge {
            background-color: #334155;
            color: #e2e8f0;
        }
        
        :root[data-theme="dark"] .skill-tag {
            background-color: #3730a3;
            color: #c7d2fe;
        }
        
        :root[data-theme="dark"] .related-card-title {
            color: #e2e8f0;
        }
        
        :root[data-theme="dark"] .view-details-btn {
            border-color: #4f46e5;
            color: #4f46e5;
        }
        
        :root[data-theme="dark"] .view-details-btn:hover {
            background-color: #4f46e5;
            color: white;
        }

        /* Dark theme adjustments - Terminal OE Style */
        :root[data-theme="dark"] .description-section,
        :root[data-theme="dark"] .sidebar-section,
        :root[data-theme="dark"] .location-section,
        :root[data-theme="dark"] .related-card {
            background: rgba(8, 12, 18, 0.95);
            border: 1px solid rgba(0, 180, 216, 0.8);
            box-shadow: 0 0 30px rgba(0, 180, 216, 0.3),
                        inset 0 0 20px rgba(0, 180, 216, 0.15);
            backdrop-filter: blur(3px);
            transform-style: preserve-3d;
            position: relative;
            overflow: hidden;
        }
        
        /* Terminal border effect for dark theme */
        :root[data-theme="dark"] .description-section::before,
        :root[data-theme="dark"] .sidebar-section::before,
        :root[data-theme="dark"] .location-section::before,
        :root[data-theme="dark"] .related-card::before {
            content: "";
            position: absolute;
            top: -2px;
            left: -2px;
            right: -2px;
            bottom: -2px;
            border: 2px solid transparent;
            border-image: linear-gradient(135deg, 
                          rgba(0, 180, 216, 0.8) 0%, 
                          rgba(72, 202, 228, 0.5) 50%,
                          transparent 100%);
            border-image-slice: 1;
            border-radius: 12px;
            pointer-events: none;
            animation: border-glow 8s linear infinite;
            z-index: 0;
        }

        /* Scanning line effect for dark theme */
        :root[data-theme="dark"] .description-section::after,
        :root[data-theme="dark"] .sidebar-section::after,
        :root[data-theme="dark"] .location-section::after,
        :root[data-theme="dark"] .related-card::after {
            content: "";
            position: absolute;
            left: 0;
            right: 0;
            height: 1px;
            background: linear-gradient(to right, 
                      transparent, 
                      rgba(72, 202, 228, 0.6), 
                      transparent);
            box-shadow: 0 0 15px rgba(72, 202, 228, 0.4);
            animation: oe-scan 8s linear infinite;
        }

        :root[data-theme="dark"] .description-section::after { animation-delay: 0.3s; }
        :root[data-theme="dark"] .sidebar-section::after { animation-delay: 1.2s; }
        :root[data-theme="dark"] .location-section::after { animation-delay: 2.1s; }
        :root[data-theme="dark"] .related-card:nth-child(1)::after { animation-delay: 0.6s; }
        :root[data-theme="dark"] .related-card:nth-child(2)::after { animation-delay: 1.5s; }
        :root[data-theme="dark"] .related-card:nth-child(3)::after { animation-delay: 2.4s; }

        /* Section titles styling for dark theme */
        :root[data-theme="dark"] .section-title,
        :root[data-theme="dark"] .related-title {
            color: rgba(0, 180, 216, 0.95);
            text-shadow: 0 0 10px rgba(0, 180, 216, 0.4);
            border-bottom: 1px solid rgba(72, 202, 228, 0.3);
        }

        /* Text styling for dark theme */
        :root[data-theme="dark"] .description {
            color: rgba(255, 255, 255, 0.85);
        }
        
        :root[data-theme="dark"] .detail-label {
            color: rgba(255, 255, 255, 0.7);
        }
        
        :root[data-theme="dark"] .detail-value {
            color: rgba(0, 180, 216, 0.95);
            font-weight: 600;
        }
        
        /* Skill tags styling for dark theme */
        :root[data-theme="dark"] .skill-tag {
            background-color: rgba(0, 180, 216, 0.2);
            color: rgba(72, 202, 228, 0.95);
            border: 1px solid rgba(72, 202, 228, 0.3);
            box-shadow: 0 0 10px rgba(0, 180, 216, 0.1);
        }

        /* See target button dark theme styling */
        :root[data-theme="dark"] .see-target-btn {
            background: linear-gradient(to bottom, rgba(0, 180, 216, 0.8), rgba(0, 120, 180, 0.8));
            color: white;
            border: 1px solid rgba(72, 202, 228, 0.5);
            box-shadow: 0 0 10px rgba(0, 180, 216, 0.3);
            text-shadow: 0 0 5px rgba(255, 255, 255, 0.5);
        }

        :root[data-theme="dark"] .see-target-btn:hover {
            background: linear-gradient(to bottom, rgba(0, 200, 236, 0.9), rgba(0, 140, 200, 0.9));
            box-shadow: 0 0 15px rgba(0, 180, 216, 0.6);
            transform: translateY(-2px);
        }

        /* Map container dark theme styling */
        :root[data-theme="dark"] #map-container {
            border: 1px solid rgba(0, 180, 216, 0.5);
            box-shadow: 0 0 15px rgba(0, 180, 216, 0.2);
        }
        
        /* Related cards styling */
        :root[data-theme="dark"] .related-card {
            overflow: visible;
        }
        
        :root[data-theme="dark"] .related-card-title {
            color: rgba(0, 180, 216, 0.95);
            text-shadow: 0 0 5px rgba(0, 180, 216, 0.3);
        }
        
        :root[data-theme="dark"] .related-card-organizer {
            color: rgba(0, 180, 216, 0.8);
        }
        
        :root[data-theme="dark"] .related-card-date {
            color: rgba(255, 255, 255, 0.7);
        }
        
        :root[data-theme="dark"] .view-details-btn {
            background-color: rgba(0, 180, 216, 0.2);
            color: rgba(72, 202, 228, 0.95);
            border: 1px solid rgba(72, 202, 228, 0.5);
            transition: all 0.3s ease;
        }
        
        :root[data-theme="dark"] .view-details-btn:hover {
            background: linear-gradient(to bottom, rgba(0, 180, 216, 0.8), rgba(0, 120, 180, 0.8));
            color: white;
            box-shadow: 0 0 15px rgba(0, 180, 216, 0.4);
            transform: translateY(-2px);
        }

        /* Date and location badges in dark mode */
        :root[data-theme="dark"] .date-badge, 
        :root[data-theme="dark"] .location-badge {
            background-color: rgba(8, 12, 18, 0.8);
            border: 1px solid rgba(72, 202, 228, 0.3);
            color: rgba(255, 255, 255, 0.9);
        }
        
        :root[data-theme="dark"] .date-badge svg, 
        :root[data-theme="dark"] .location-badge svg {
            color: rgba(0, 180, 216, 0.95);
        }
        
        /* Responsive design */
        @media (max-width: 1024px) {
            .hackathon-content {
                grid-template-columns: 1fr;
            }
            
            .sidebar-section {
                position: static;
            }
        }
        
        @media (max-width: 768px) {
            .hackathon-header {
                flex-direction: column;
                align-items: center;
                text-align: center;
            }
            
            .dates-location {
                justify-content: center;
            }
            
            .hackathon-title-area h1 {
                font-size: 2rem;
            }
        }
    </style>
        <link href="https://fonts.googleapis.com/css2?family=Fira+Code:wght@400;500&display=swap" rel="stylesheet">

</head>
<body>

        <!-- Terminal OnlyEngineers Elite - Position haut droite -->
    <div class="oe-terminal">
        <div class="oe-header">
            <div class="oe-logo">ONLY_ENGINEERS</div>
            <div class="oe-version">OS v5.3.2</div>
        </div>
        
        <div class="oe-body">
            <div class="oe-scan"></div>
            
            <div class="oe-code-line">$ init --platform=only_engineers<span class="oe-cursor"></span></div>
            <div class="oe-code-line">> Connecting to engineering network...<span class="oe-cursor"></span></div>
            <div class="oe-code-line">> Found 42 active hackathons<span class="oe-cursor"></span></div>
            <div class="oe-code-line">$ analyze --tech=all --level=expert<span class="oe-cursor"></span></div>
            <div class="oe-code-line">> System ready for elite engineers<span class="oe-cursor"></span></div>
        </div>
        
        <div class="oe-footer">
            <div class="oe-status">
                <div class="oe-led"></div>
                <span>ENGINEERING MODE</span>
            </div>
            <div class="oe-engineer-icon">⚙️</div>
        </div>

        <div class="oe-signature">// ONLY_ENGINEERS_CODE //</div>
    </div>




    <div class="app-container">
        <!-- Navbar -->
        <nav class="navbar">
            <div class="nav-left">
                <a href="#" class="logo">
                    <img src="../assets/logo.png" alt="Only Engineers">
                </a>
            </div>
            <div class="nav-center">
                <nav class="nav-links">
                    <a href="home.html">Home</a>
                    <a href="../view/Dashboard.html"> Dashboard </a>
                    <a href="../view/index.html">Jobs</a>
                    <a href="../view/projects.html">Projects</a>
                    <a href="../view/courses.html">Courses</a>
                    <a href="../view/hackathons.php" class="active">Hackathons</a>
                    <a href="../view/articles.html">Articles</a>
                    <a href="networking.php">Networking</a>
                </nav>
            </div>
            <div class="nav-right">
                <div class="notification-wrapper">
                    <button class="icon-button notification">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"></path>
                            <path d="M13.73 21a2 2 0 0 1-3.46 0"></path>
                        </svg>
                    </button>
                    <div class="notification-dot"></div>
                </div>
                <button class="icon-button theme-toggle" id="themeToggle">
                    <svg class="sun-icon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="12" cy="12" r="5"></circle>
                        <line x1="12" y1="1" x2="12" y2="3"></line>
                        <line x1="12" y1="21" x2="12" y2="23"></line>
                        <line x1="4.22" y1="4.22" x2="5.64" y2="5.64"></line>
                        <line x1="18.36" y1="18.36" x2="19.78" y2="19.78"></line>
                        <line x1="1" y1="12" x2="3" y2="12"></line>
                        <line x1="21" y1="12" x2="23" y2="12"></line>
                        <line x1="4.22" y1="19.78" x2="5.64" y2="18.36"></line>
                        <line x1="18.36" y1="5.64" x2="19.78" y2="4.22"></line>
                    </svg>
                    <svg class="moon-icon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="display: none;">
                        <path d="M21 12.79A9 9 0 1 1 11.21 3 A7 7 0 0 0 21 12.79z"></path>
                    </svg>
                </button>
                <div class="user-profile">
                    <a href="../view/user-profile.php">
                        <img src="../assets/profil.jpg" alt="User profile" class="avatar">
                    </a>
                </div>
            </div>
        </nav>

        <!-- Main Content -->
        <div class="hackathon-details-container">
            <!-- Hackathon Header -->
            <div class="hackathon-header">
                <img src="<?php echo htmlspecialchars($imagePath); ?>" alt="<?php echo htmlspecialchars($hackathon['name']); ?>" class="hackathon-image">
                
                <div class="hackathon-title-area">
                    <h1><?php echo htmlspecialchars($hackathon['name']); ?></h1>
                    <div class="organizer">Organized by: <?php echo htmlspecialchars($hackathon['organizer']); ?></div>
                    
                    <div class="dates-location">
                        <div class="date-badge">
                            <svg fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                            </svg>
                            <?php echo $formattedStartDate; ?> - <?php echo $formattedEndDate; ?>
                        </div>
                        
                        <div class="location-badge">
                            <svg fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                            </svg>
                            <?php echo htmlspecialchars($hackathon['location']); ?>
                        </div>
                    </div>
                    
                    <button class="register-btn" id="register-btn">
                        Register for this hackathon
                    </button>
                </div>
            </div>
            
            <!-- Hackathon Content -->
            <div class="hackathon-content">
                <!-- Description Section -->
                <div class="description-section">
                    <h2 class="section-title">About this Hackathon</h2>
                    <div class="description">
                        <?php echo nl2br(htmlspecialchars($hackathon['description'])); ?>
                    </div>
                </div>
                
                <!-- Sidebar with Details -->
                <div class="sidebar-section">
                    <h2 class="section-title">Details</h2>
                    
                    <div class="detail-row">
                        <span class="detail-label">Duration</span>
                        <span class="detail-value">
                            <?php 
                                $interval = $startDate->diff($endDate);
                                $days = $interval->days;
                                $hours = $interval->h;
                                if ($days > 0) {
                                    echo $days . ' day' . ($days > 1 ? 's' : '');
                                    if ($hours > 0) {
                                        echo ', ' . $hours . ' hour' . ($hours > 1 ? 's' : '');
                                    }
                                } else {
                                    echo $hours . ' hour' . ($hours > 1 ? 's' : '');
                                }
                            ?>
                        </span>
                    </div>
                    
                    <div class="detail-row">
                        <span class="detail-label">Max Participants</span>
                        <span class="detail-value"><?php echo htmlspecialchars($hackathon['max_participants']); ?></span>
                    </div>
                    
                    <div class="detail-row">
                        <span class="detail-label">Total Participants</span>
                        <span class="detail-value"><?php echo $totalParticipants; ?></span>
                    </div>
                    
                    <div class="detail-row">
                        <span class="detail-label">Required Skills</span>
                    </div>
                    <div class="skill-tags">
                        <?php 
                            if (!empty($hackathon['required_skills'])) {
                                $skills = explode(',', $hackathon['required_skills']);
                                foreach ($skills as $skill) {
                                    echo '<span class="skill-tag">' . htmlspecialchars(trim($skill)) . '</span>';
                                }
                            }
                        ?>
                    </div>
                    
                    <div class="detail-row">
                        <span class="detail-label">Organizer</span>
                        <span class="detail-value"><?php echo htmlspecialchars($hackathon['organizer']); ?></span>
                    </div>
                    
                    <div class="detail-row">
                        <span class="detail-label">Status</span>
                        <span class="detail-value">
                            <?php 
                                $today = new DateTime();
                                if ($today < $startDate) {
                                    echo '<span style="color: #3b82f6;">Upcoming</span>';
                                } else if ($today > $endDate) {
                                    echo '<span style="color: #ef4444;">Past</span>';
                                } else {
                                    echo '<span style="color: #10b981;">Ongoing</span>';
                                }
                            ?>
                        </span>
                    </div>
                </div>
                
                <!-- Location Map Section -->
                <div class="location-section">
                    <h2 class="section-title">
                        Location
                        <button class="see-target-btn" style="float: right; background-color: #6366f1; color: white; border: none; padding: 5px 12px; border-radius: 6px; font-size: 0.9rem; font-weight: 500; cursor: pointer;">See target</button>
                    </h2>
                    <div id="map-container"></div>
                </div>
            </div>
            
            <!-- Related Hackathons -->
            <div class="related-hackathons">
                <h2 class="related-title">Related Hackathons</h2>
                
                <div class="related-cards">
                    <?php
                    // Fetch 3 related hackathons (same organizer or with similar skills but not the current one)
                    $relatedSql = "SELECT * FROM hackathons WHERE id != :current_id 
                                  AND (organizer = :organizer OR required_skills LIKE :skills) 
                                  AND end_date >= CURDATE()
                                  ORDER BY start_date ASC LIMIT 3";
                    $relatedStmt = $conn->prepare($relatedSql);
                    $skillsParam = '%' . $hackathon['required_skills'] . '%';
                    $relatedStmt->bindParam(":current_id", $hackathonId);
                    $relatedStmt->bindParam(":organizer", $hackathon['organizer']);
                    $relatedStmt->bindParam(":skills", $skillsParam);
                    $relatedStmt->execute();
                    
                    $relatedHackathons = $relatedStmt->fetchAll(PDO::FETCH_ASSOC);
                    
                    if (count($relatedHackathons) > 0):
                        foreach ($relatedHackathons as $related):
                            // Format related hackathon dates
                            $relatedStart = new DateTime($related['start_date']);
                            $relatedEnd = new DateTime($related['end_date']);
                            $relatedDateDisplay = $relatedStart->format('M d') . ' - ' . $relatedEnd->format('M d, Y');
                            
                            // Image path for related hackathons
                            $relatedImage = !empty($related['image']) ? $related['image'] : 'default_hackathon.jpg';
                            $relatedImage = str_replace('ressources/', '', $relatedImage);
                            $relatedImagePath = '../ressources/' . $relatedImage;
                            
                            if (!file_exists(__DIR__ . '/../ressources/' . $relatedImage)) {
                                $relatedImagePath = $defaultImage;
                            }
                    ?>
                    <div class="related-card">
                        <img src="<?php echo htmlspecialchars($relatedImagePath); ?>" alt="<?php echo htmlspecialchars($related['name']); ?>" class="related-card-image">
                        <div class="related-card-content">
                            <h3 class="related-card-title"><?php echo htmlspecialchars($related['name']); ?></h3>
                            <div class="related-card-organizer"><?php echo htmlspecialchars($related['organizer']); ?></div>
                            <div class="related-card-date"><?php echo $relatedDateDisplay; ?></div>
                            <a href="hackathon-details.php?id=<?php echo $related['id']; ?>" class="view-details-btn">View Details</a>
                        </div>
                    </div>
                    <?php
                        endforeach;
                    else:
                    ?>
                    <div>
                        <p>No related hackathons found at the moment.</p>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Toastify CSS -->
    <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/toastify-js/src/toastify.min.css">
    <!-- Toastify JS -->
    <script type="text/javascript" src="https://cdn.jsdelivr.net/npm/toastify-js"></script>
    
    <script>
        // Initialize map with the hackathon location
        document.addEventListener('DOMContentLoaded', function() {
            // Theme toggle functionality
            const themeToggle = document.getElementById('themeToggle');
            const sunIcon = document.querySelector('.sun-icon');
            const moonIcon = document.querySelector('.moon-icon');
            const htmlRoot = document.documentElement;
            
            // Check for saved theme preference
            const savedTheme = localStorage.getItem('theme') || 'light';
            htmlRoot.setAttribute('data-theme', savedTheme);
            
            // Update icon display based on current theme
            if (savedTheme === 'dark') {
                sunIcon.style.display = 'none';
                moonIcon.style.display = 'block';
            }
            
            // Handle theme toggle click
            themeToggle.addEventListener('click', function() {
                const currentTheme = htmlRoot.getAttribute('data-theme');
                const newTheme = currentTheme === 'light' ? 'dark' : 'light';
                
                // Update theme attribute and save preference
                htmlRoot.setAttribute('data-theme', newTheme);
                localStorage.setItem('theme', newTheme);
                
                // Toggle icon display
                if (newTheme === 'dark') {
                    sunIcon.style.display = 'none';
                    moonIcon.style.display = 'block';
                } else {
                    sunIcon.style.display = 'block';
                    moonIcon.style.display = 'none';
                }
            });

            // Variables globales pour la carte et les marqueurs
            let map, userMarker, destinationMarker, routeLayer;
            let hackathonLat, hackathonLng;
            let userLat, userLng;
            const apiKey = "357be27546e04a85b8bb2b37c9c43867"; // Clé API OpenCage

            // Map initialization - S'assurer que le DOM est complètement chargé
            setTimeout(() => {
                initializeHackathonMap();
            }, 500);

            function initializeHackathonMap() {
                console.log("Initialisation de la carte...");
                const mapContainer = document.getElementById('map-container');
                
                if (!mapContainer) {
                    console.error("Conteneur de carte non trouvé !");
                    return;
                }

                <?php if (!empty($hackathon['latitude']) && !empty($hackathon['longitude'])): ?>
                    // Coordonnées disponibles dans la base de données
                    try {
                        hackathonLat = <?php echo floatval($hackathon['latitude']); ?>;
                        hackathonLng = <?php echo floatval($hackathon['longitude']); ?>;
                        console.log("Coordonnées trouvées:", hackathonLat, hackathonLng);
                        initializeMap(hackathonLat, hackathonLng);
                    } catch (e) {
                        console.error("Erreur lors de l'initialisation avec coordonnées:", e);
                        showNoLocationMessage();
                    }
                <?php else: ?>
                    // Tenter de géocoder l'adresse si les coordonnées ne sont pas disponibles
                    try {
                        const locationName = "<?php echo addslashes($hackathon['location']); ?>";
                        console.log("Tentative de géocodage pour:", locationName);
                        if (locationName) {
                            geocodeLocation(locationName);
                        } else {
                            showNoLocationMessage();
                        }
                    } catch (e) {
                        console.error("Erreur lors du géocodage:", e);
                        showNoLocationMessage();
                    }
                <?php endif; ?>
            }
            
            // Function to initialize map with given coordinates
            function initializeMap(lat, lng) {
                if (!lat || !lng || isNaN(lat) || isNaN(lng)) {
                    console.error("Coordonnées invalides:", lat, lng);
                    showNoLocationMessage();
                    return;
                }

                hackathonLat = lat;
                hackathonLng = lng;

                try {
                    // Vérifier que l'objet L (Leaflet) est disponible
                    if (typeof L === 'undefined') {
                        console.error("Leaflet n'est pas chargé!");
                        showNoLocationMessage();
                        return;
                    }

                    // S'assurer que le conteneur de carte existe et est visible
                    const mapContainer = document.getElementById('map-container');
                    if (!mapContainer) {
                        console.error("Conteneur de carte non trouvé lors de l'initialisation!");
                        return;
                    }

                    // Nettoyer le conteneur
                    mapContainer.innerHTML = '';
                    
                    // Définir une hauteur explicite
                    mapContainer.style.height = '400px';
                    
                    // Initialiser la carte avec un délai pour s'assurer que le DOM est prêt
                    map = L.map('map-container').setView([lat, lng], 15);
                    
                    // Ajouter le calque de tuiles OpenStreetMap
                    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                        attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors',
                        maxZoom: 19
                    }).addTo(map);
                    
                    // Ajouter un marqueur à l'emplacement du hackathon
                    destinationMarker = L.marker([lat, lng]).addTo(map)
                        .bindPopup("<strong><?php echo addslashes(htmlspecialchars($hackathon['name'])); ?></strong><br><?php echo addslashes(htmlspecialchars($hackathon['location'])); ?>").openPopup();
                    
                    // Forcer la mise à jour de la carte
                    setTimeout(() => {
                        map.invalidateSize();
                    }, 100);
                    
                    console.log("Carte initialisée avec succès");
                    
                    // Ajouter l'écouteur d'événement pour le bouton "See target"
                    document.querySelector('.see-target-btn').addEventListener('click', function() {
                        getLocation();
                    });
                } catch (e) {
                    console.error("Erreur lors de l'initialisation de la carte:", e);
                    showNoLocationMessage();
                }
            }
            
            // Function to get user's current location
            function getLocation() {
                if (navigator.geolocation) {
                    // Afficher un toast pour indiquer que la localisation est en cours
                    Toastify({
                        text: "Obtention de votre position en cours...",
                        duration: 3000,
                        close: true,
                        gravity: "top",
                        position: "right",
                        backgroundColor: "#6366f1",
                    }).showToast();
                    
                    navigator.geolocation.getCurrentPosition(showPosition, showError);
                } else {
                    Toastify({
                        text: "La géolocalisation n'est pas supportée par votre navigateur.",
                        duration: 3000,
                        close: true,
                        gravity: "top",
                        position: "right",
                        backgroundColor: "#ef4444",
                    }).showToast();
                }
            }
            
            // Function to handle the user's position
            function showPosition(position) {
                userLat = position.coords.latitude;
                userLng = position.coords.longitude;
                
                console.log("Position utilisateur:", userLat, userLng);
                
                // Ajouter le marqueur de l'utilisateur
                if (userMarker) {
                    userMarker.setLatLng([userLat, userLng]);
                } else {
                    userMarker = L.marker([userLat, userLng], {
                        icon: L.divIcon({
                            className: 'user-location-marker',
                            html: '<div style="background-color: #3b82f6; width: 15px; height: 15px; border-radius: 50%; border: 3px solid white; box-shadow: 0 0 10px rgba(0,0,0,0.5);"></div>',
                            iconSize: [15, 15],
                            iconAnchor: [7, 7]
                        })
                    }).addTo(map).bindPopup("Votre position").openPopup();
                }
                
                // Calculer et afficher l'itinéraire
                calculateRoute(userLat, userLng, hackathonLat, hackathonLng);
            }
            
            // Function to handle geolocation errors
            function showError(error) {
                let errorMessage;
                switch(error.code) {
                    case error.PERMISSION_DENIED:
                        errorMessage = "Vous avez refusé la demande de géolocalisation.";
                        break;
                    case error.POSITION_UNAVAILABLE:
                        errorMessage = "Les informations de localisation sont indisponibles.";
                        break;
                    case error.TIMEOUT:
                        errorMessage = "La demande de localisation a expiré.";
                        break;
                    case error.UNKNOWN_ERROR:
                        errorMessage = "Une erreur inconnue s'est produite.";
                        break;
                }
                
                Toastify({
                    text: errorMessage,
                    duration: 3000,
                    close: true,
                    gravity: "top",
                    position: "right",
                    backgroundColor: "#ef4444",
                }).showToast();
            }
            
            // Function to calculate and display the route
            function calculateRoute(startLat, startLng, endLat, endLng) {
                console.log("Calcul de l'itinéraire entre:", startLat, startLng, "et", endLat, endLng);
                
                if (!startLat || !startLng || !endLat || !endLng) {
                    console.error("Coordonnées manquantes pour le calcul d'itinéraire");
                    return;
                }
                
                // URL pour le service d'itinéraire OSRM
                const routeUrl = `https://router.project-osrm.org/route/v1/driving/${startLng},${startLat};${endLng},${endLat}?overview=full&geometries=geojson`;
                
                console.log("URL de requête d'itinéraire:", routeUrl);
                
                // Afficher un toast pour indiquer que le calcul est en cours
                Toastify({
                    text: "Calcul de l'itinéraire en cours...",
                    duration: 3000,
                    close: true,
                    gravity: "top",
                    position: "right",
                    backgroundColor: "#6366f1",
                }).showToast();
                
                fetch(routeUrl)
                    .then(response => {
                        if (!response.ok) {
                            throw new Error(`Erreur HTTP ${response.status}`);
                        }
                        return response.json();
                    })
                    .then(data => {
                        console.log("Réponse d'itinéraire reçue:", data);
                        
                        if (data.code !== 'Ok' || !data.routes || data.routes.length === 0) {
                            throw new Error("Impossible de calculer l'itinéraire");
                        }
                        
                        // Supprimer l'ancien itinéraire s'il existe
                        if (routeLayer) {
                            map.removeLayer(routeLayer);
                        }
                        
                        // Créer le tracé de l'itinéraire
                        const routeCoords = data.routes[0].geometry.coordinates.map(coord => [coord[1], coord[0]]);
                        routeLayer = L.polyline(routeCoords, {
                            color: '#3b82f6',
                            weight: 5,
                            opacity: 0.7,
                            lineJoin: 'round'
                        }).addTo(map);
                        
                        // Ajuster la vue pour inclure tout l'itinéraire
                        map.fitBounds(routeLayer.getBounds(), { padding: [50, 50] });
                        
                        // Calculer la distance et la durée
                        const distance = (data.routes[0].distance / 1000).toFixed(2); // en km
                        const duration = Math.round(data.routes[0].duration / 60); // en minutes
                        
                        // Afficher les informations sur l'itinéraire
                        Toastify({
                            text: `Distance: ${distance} km - Durée estimée: ${duration} min`,
                            duration: 5000,
                            close: true,
                            gravity: "top",
                            position: "right",
                            backgroundColor: "#10b981",
                        }).showToast();
                    })
                    .catch(error => {
                        console.error("Erreur lors du calcul de l'itinéraire:", error);
                        
                        Toastify({
                            text: "Impossible de calculer l'itinéraire. Veuillez réessayer plus tard.",
                            duration: 3000,
                            close: true,
                            gravity: "top",
                            position: "right",
                            backgroundColor: "#ef4444",
                        }).showToast();
                        
                        // En cas d'échec, créer une ligne droite simple entre les deux points
                        if (routeLayer) {
                            map.removeLayer(routeLayer);
                        }
                        
                        routeLayer = L.polyline([[startLat, startLng], [endLat, endLng]], {
                            color: '#ef4444',
                            weight: 3,
                            opacity: 0.7,
                            dashArray: '5, 10',
                            lineJoin: 'round'
                        }).addTo(map);
                        
                        map.fitBounds(routeLayer.getBounds(), { padding: [50, 50] });
                    });
            }
            
            // Function to geocode a location name to coordinates
            function geocodeLocation(locationName) {
                if (!locationName) {
                    showNoLocationMessage();
                    return;
                }
                
                // Ajouter "Tunisia" à la fin de l'adresse si elle ne contient pas déjà "Tunisia" ou "Tunisie"
                if (!locationName.includes('Tunisia') && !locationName.includes('Tunisie')) {
                    locationName = locationName + ', Tunisia';
                }
                
                // URL pour le service de géocodage en HTTPS pour éviter les problèmes mixtes de contenu
                const geocodeUrl = `https://nominatim.openstreetmap.org/search?format=json&q=${encodeURIComponent(locationName)}&limit=1`;
                
                console.log("Envoi de la requête de géocodage:", geocodeUrl);
                
                // Ajouter un message de chargement
                const mapContainer = document.getElementById('map-container');
                mapContainer.innerHTML = '<div style="padding: 20px; text-align: center; color: #6b7280;">Chargement de la carte...</div>';
                
                fetch(geocodeUrl, {
                    headers: {
                        'Accept': 'application/json',
                        'User-Agent': 'HackathonSite/1.0' // Nominatim exige un User-Agent
                    }
                })
                .then(response => {
                    if (!response.ok) {
                        throw new Error(`Erreur HTTP ${response.status}`);
                    }
                    return response.json();
                })
                .then(data => {
                    console.log("Réponse de géocodage reçue:", data);
                    if (data && data.length > 0) {
                        const lat = parseFloat(data[0].lat);
                        const lng = parseFloat(data[0].lon);
                        
                        console.log("Coordonnées géocodées:", lat, lng);
                        if (!isNaN(lat) && !isNaN(lng)) {
                            initializeMap(lat, lng);
                        } else {
                            // Si les coordonnées sont invalides, essayez une recherche simplifiée
                            trySimplifiedGeocoding(locationName);
                        }
                    } else {
                        // Si aucune donnée n'est retournée, essayez une recherche simplifiée
                        trySimplifiedGeocoding(locationName);
                    }
                })
                .catch(error => {
                    console.error("Erreur de géocodage:", error);
                    // En cas d'erreur, essayez une recherche simplifiée
                    trySimplifiedGeocoding(locationName);
                });
            }
            
            // Fonction pour essayer un géocodage simplifié
            function trySimplifiedGeocoding(fullLocationName) {
                console.log("Tentative de géocodage simplifié...");
                
                // Extraire des parties significatives de l'adresse (ville ou institution)
                const locationParts = fullLocationName.split(',');
                let simplifiedLocation = '';
                
                if (fullLocationName.includes('ESPRIT') || fullLocationName.includes('École Superieur')) {
                    simplifiedLocation = 'ESPRIT, Ariana, Tunisia';
                    console.log("Utilisation de l'adresse connue pour ESPRIT");
                } else if (locationParts.length > 1) {
                    // Prendre la première partie (supposée être le nom principal) et la dernière (supposée être le pays)
                    simplifiedLocation = locationParts[0] + ', Tunisia';
                } else {
                    // Sinon, utiliser l'adresse complète
                    simplifiedLocation = fullLocationName;
                }
                
                console.log("Adresse simplifiée:", simplifiedLocation);
                
                const geocodeUrl = `https://nominatim.openstreetmap.org/search?format=json&q=${encodeURIComponent(simplifiedLocation)}&limit=1`;
                
                fetch(geocodeUrl, {
                    headers: {
                        'Accept': 'application/json',
                        'User-Agent': 'HackathonSite/1.0'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    console.log("Réponse pour géocodage simplifié:", data);
                    if (data && data.length > 0) {
                        const lat = parseFloat(data[0].lat);
                        const lng = parseFloat(data[0].lon);
                        
                        if (!isNaN(lat) && !isNaN(lng)) {
                            initializeMap(lat, lng);
                        } else {
                            // Si le géocodage simplifié échoue également, utilisez des coordonnées par défaut pour la Tunisie
                            useFallbackCoordinates();
                        }
                    } else {
                        // Si le géocodage simplifié échoue également, utilisez des coordonnées par défaut pour la Tunisie
                        useFallbackCoordinates();
                    }
                })
                .catch(error => {
                    console.error("Erreur lors du géocodage simplifié:", error);
                    useFallbackCoordinates();
                });
            }
            
            // Fonction pour utiliser des coordonnées de secours
            function useFallbackCoordinates() {
                console.log("Utilisation des coordonnées par défaut");
                
                // Coordonnées par défaut pour la Tunisie (centre approximatif)
                const defaultLat = 36.8065;
                const defaultLng = 10.1815; // Tunis, Tunisie
                
                // Vérifier si l'adresse contient des mots-clés pour des lieux spécifiques
                const locationName = "<?php echo addslashes($hackathon['location']); ?>";
                
                if (locationName.includes('ESPRIT') || locationName.includes('École Superieur')) {
                    // Coordonnées pour ESPRIT (École Supérieure Privée d'Ingénierie et de Technologie)
                    initializeMap(36.8977, 10.1867);
                } else if (locationName.includes('Ariana')) {
                    initializeMap(36.8625, 10.1956);
                } else if (locationName.includes('Sousse')) {
                    initializeMap(35.8245, 10.6346);
                } else if (locationName.includes('Sfax')) {
                    initializeMap(34.7478, 10.7661);
                } else {
                    // Utiliser les coordonnées par défaut de Tunis
                    initializeMap(defaultLat, defaultLng);
                }
            }
            
            // Function to show the "no location" message
            function showNoLocationMessage() {
                const mapContainer = document.getElementById('map-container');
                if (mapContainer) {
                    mapContainer.innerHTML = '<div style="padding: 20px; text-align: center; color: #6b7280;">Aucune coordonnée de localisation disponible pour ce hackathon.</div>';
                }
            }
            
            // Disable registration button if hackathon has already started or is over
            const registerBtn = document.getElementById('register-btn');
            if (registerBtn) {
                // Check if hackathon has already started or is over
                const today = new Date();
                const startDate = new Date("<?php echo $hackathon['start_date'] . ' ' . $hackathon['start_time']; ?>");
                
                if (today >= startDate) {
                    // Hackathon has already started or is over, disable the button
                    registerBtn.disabled = true;
                    registerBtn.innerHTML = "Registration closed";
                    registerBtn.title = "Registration is closed as the hackathon has already started or ended";
                } else if (<?php echo json_encode($isUserRegistered); ?>) {
                    // User is already registered, disable the button and show cancel button
                    registerBtn.disabled = true;
                    registerBtn.innerHTML = "Already registered";
                    registerBtn.title = "You are already registered for this hackathon";
                    
                    // Create cancel registration button
                    const cancelBtn = document.createElement('button');
                    cancelBtn.className = 'cancel-btn';
                    cancelBtn.innerHTML = "Click Here to Cancel";
                    cancelBtn.title = "Cancel your registration for this hackathon";
                    cancelBtn.style.marginLeft = "10px";
                    cancelBtn.style.backgroundColor = "#ef4444";
                    cancelBtn.style.color = "white";
                    cancelBtn.style.border = "none";
                    cancelBtn.style.padding = "12px 25px";
                    cancelBtn.style.borderRadius = "8px";
                    cancelBtn.style.fontWeight = "600";
                    cancelBtn.style.fontSize = "1rem";
                    cancelBtn.style.cursor = "pointer";
                    
                    // Add event listener for cancel button
                    cancelBtn.addEventListener('click', function() {
                        if (confirm("Are you sure you want to cancel your registration for this hackathon?")) {
                            window.location.href = '../controller/cancel-registration.php?hackathon_id=<?php echo $hackathonId; ?>';
                        }
                    });
                    
                    // Create edit registration button
                    const editBtn = document.createElement('button');
                    editBtn.className = 'edit-btn';
                    editBtn.innerHTML = "Edit Registration";
                    editBtn.title = "Edit your registration form for this hackathon";
                    editBtn.style.marginLeft = "10px";
                    editBtn.style.backgroundColor = "#3b82f6";
                    editBtn.style.color = "white";
                    editBtn.style.border = "none";
                    editBtn.style.padding = "12px 25px";
                    editBtn.style.borderRadius = "8px";
                    editBtn.style.fontWeight = "600";
                    editBtn.style.fontSize = "1rem";
                    editBtn.style.cursor = "pointer";
                    
                    // Add event listener for edit button
                    editBtn.addEventListener('click', function() {
                        window.location.href = 'register-participant.php?hackathon_id=<?php echo $hackathonId; ?>&edit_mode=true';
                    });
                    
                    // Create badge button
                    const badgeBtn = document.createElement('button');
                    badgeBtn.className = 'badge-btn';
                    badgeBtn.innerHTML = "Get Your Badges";
                    badgeBtn.title = "Download your participation badge for this hackathon";
                    badgeBtn.style.marginLeft = "10px";
                    badgeBtn.style.backgroundColor = "#10b981";
                    badgeBtn.style.color = "white";
                    badgeBtn.style.border = "none";
                    badgeBtn.style.padding = "12px 25px";
                    badgeBtn.style.borderRadius = "8px";
                    badgeBtn.style.fontWeight = "600";
                    badgeBtn.style.fontSize = "1rem";
                    badgeBtn.style.cursor = "pointer";
                    
                    // Add event listener for badge button
                    badgeBtn.addEventListener('click', function() {
                        <?php if (isset($_SESSION['user_id'])): ?>
                        // First, get the participant ID for this user and hackathon
                        fetch(`../controller/get_participant_id.php?user_id=<?php echo $_SESSION['user_id']; ?>&hackathon_id=<?php echo $hackathonId; ?>`)
                            .then(response => response.json())
                            .then(data => {
                                if (data.success && data.participant_id) {
                                    // Check if participant is part of a team
                                    if (data.team_name) {
                                        // If part of a team, redirect to generate team badges
                                        window.location.href = '../controller/generate-badge.php?team_name=' + encodeURIComponent(data.team_name) + '&hackathon_id=<?php echo $hackathonId; ?>';
                                    } else {
                                        // If individual participant, generate individual badge
                                        window.location.href = '../controller/generate-badge.php?id=' + data.participant_id;
                                    }
                                } else {
                                    Toastify({
                                        text: "Could not generate badge. Please try again later.",
                                        duration: 3000,
                                        close: true,
                                        gravity: "top",
                                        position: "right",
                                        backgroundColor: "#ef4444",
                                    }).showToast();
                                }
                            })
                            .catch(error => {
                                console.error("Error fetching participant ID:", error);
                                Toastify({
                                    text: "Error generating badge. Please try again later.",
                                    duration: 3000,
                                    close: true,
                                    gravity: "top",
                                    position: "right",
                                    backgroundColor: "#ef4444",
                                }).showToast();
                            });
                        <?php else: ?>
                        Toastify({
                            text: "Please log in to download your badge",
                            duration: 3000,
                            close: true,
                            gravity: "top",
                            position: "right",
                            backgroundColor: "#ef4444",
                        }).showToast();
                        <?php endif; ?>
                    });
                    
                    // Insert cancel button after register button
                    registerBtn.parentNode.insertBefore(cancelBtn, registerBtn.nextSibling);
                    
                    // Insert edit button after cancel button
                    registerBtn.parentNode.insertBefore(editBtn, cancelBtn.nextSibling);
                    
                    // Insert badge button after edit button
                    registerBtn.parentNode.insertBefore(badgeBtn, editBtn.nextSibling);
                }
                
                registerBtn.addEventListener('click', function() {
                    if (!this.disabled) {
                        // Rediriger l'utilisateur vers le formulaire d'inscription avec l'ID du hackathon
                        window.location.href = 'register-participant.php?hackathon_id=<?php echo $hackathonId; ?>';
                    }
                });
            }
        });
    </script>
</body>
</html>