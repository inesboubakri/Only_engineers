<?php
session_start();
// Check if user is logged in and is an admin
if (!isset($_SESSION['user_id']) || !isset($_SESSION['is_admin']) || !$_SESSION['is_admin']) {
    header("Location: ../../front_office/view/signin.php");
    exit();
}

$logFile = '../../front_office/front_office/model/participant_registration.log';
$logExists = file_exists($logFile);
$logContent = $logExists ? file_get_contents($logFile) : "";

// Parse the log entries
$logEntries = [];
if ($logContent) {
    $lines = explode("\n", $logContent);
    $currentEntry = [];
    $entryContent = "";
    
    foreach ($lines as $line) {
        if (preg_match('/^\[\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}\]/', $line)) {
            // Start of a new log entry
            if (!empty($entryContent)) {
                $currentEntry['content'] = $entryContent;
                $logEntries[] = $currentEntry;
                $entryContent = "";
            }
            
            preg_match('/^\[(\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2})\] \[(.*?)\] (.*)$/', $line, $matches);
            if (count($matches) >= 4) {
                $currentEntry = [
                    'timestamp' => $matches[1],
                    'source' => $matches[2],
                    'message' => $matches[3],
                    'content' => ""
                ];
            } else {
                $currentEntry = [
                    'timestamp' => 'Unknown',
                    'source' => 'Unknown',
                    'message' => $line,
                    'content' => ""
                ];
            }
        } else {
            // Continuation of the current log entry
            $entryContent .= $line . "\n";
        }
    }
    
    // Add the last entry
    if (!empty($currentEntry)) {
        $currentEntry['content'] = $entryContent;
        $logEntries[] = $currentEntry;
    }
}

// Filter entries by registration session
$sessions = [];
$currentSessionId = null;
foreach ($logEntries as $entry) {
    if (strpos($entry['message'], "=== Début d'une nouvelle tentative d'inscription ===") !== false) {
        $currentSessionId = $entry['timestamp'];
        $sessions[$currentSessionId] = [
            'start' => $entry['timestamp'],
            'entries' => [$entry],
            'status' => 'Unknown',
            'type' => 'Unknown',
            'team_name' => '',
            'user_id' => ''
        ];
    } elseif ($currentSessionId !== null) {
        $sessions[$currentSessionId]['entries'][] = $entry;
        
        // Extract important information
        if (strpos($entry['message'], "Participation Type:") !== false) {
            preg_match('/Type de participation: (.*)/', $entry['message'], $matches);
            if (!empty($matches[1])) {
                $sessions[$currentSessionId]['type'] = $matches[1];
            }
        }
        
        if (strpos($entry['message'], "Utilisateur connecté ID:") !== false) {
            preg_match('/Utilisateur connecté ID: (.*)/', $entry['message'], $matches);
            if (!empty($matches[1])) {
                $sessions[$currentSessionId]['user_id'] = $matches[1];
            }
        }
        
        if (strpos($entry['message'], "Données d'équipe - Nom:") !== false) {
            preg_match('/Nom: (.*), Taille:/', $entry['message'], $matches);
            if (!empty($matches[1])) {
                $sessions[$currentSessionId]['team_name'] = $matches[1];
            }
        }
        
        if (strpos($entry['message'], "Redirection vers la page de succès") !== false) {
            $sessions[$currentSessionId]['status'] = 'Success';
        } elseif (strpos($entry['message'], "Exception:") !== false || 
                  strpos($entry['message'], "Erreur:") !== false) {
            $sessions[$currentSessionId]['status'] = 'Failed';
            if (!isset($sessions[$currentSessionId]['error_reason'])) {
                $sessions[$currentSessionId]['error_reason'] = $entry['message'];
            }
        }
    }
}

// Sort sessions by timestamp (newest first)
uasort($sessions, function($a, $b) {
    return strtotime($b['start']) - strtotime($a['start']);
});

// Handle clearing the log file if requested
if (isset($_POST['clear_log']) && $_POST['clear_log'] === 'yes') {
    $backupFile = $logFile . '.' . date('Y-m-d-H-i-s') . '.bak';
    copy($logFile, $backupFile);
    file_put_contents($logFile, "");
    header("Location: " . $_SERVER['PHP_SELF'] . "?cleared=1");
    exit();
}

?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Diagnostics des inscriptions</title>
    <link rel="stylesheet" href="styles.css">
    <style>
        body {
            font-family: 'Arial', sans-serif;
            line-height: 1.6;
            margin: 0;
            padding: 20px;
            background-color: #f5f5f5;
        }
        .container {
            max-width: 1200px;
            margin: 0 auto;
            background: #fff;
            padding: 20px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        h1, h2, h3 {
            color: #333;
        }
        .session {
            border: 1px solid #ddd;
            margin-bottom: 20px;
            padding: 15px;
            border-radius: 4px;
        }
        .session-header {
            display: flex;
            justify-content: space-between;
            background-color: #f9f9f9;
            padding: 10px;
            margin: -15px -15px 15px -15px;
            border-bottom: 1px solid #ddd;
            cursor: pointer;
        }
        .session-details {
            display: none;
        }
        .session.active .session-details {
            display: block;
        }
        .success {
            border-left: 5px solid #28a745;
        }
        .failed {
            border-left: 5px solid #dc3545;
        }
        .unknown {
            border-left: 5px solid #6c757d;
        }
        .log-entry {
            margin-bottom: 10px;
            padding: 5px;
            background-color: #f8f9fa;
            border-left: 3px solid #6c757d;
        }
        .error-log {
            border-left: 3px solid #dc3545;
        }
        .success-log {
            border-left: 3px solid #28a745;
        }
        .warning-log {
            border-left: 3px solid #ffc107;
        }
        .info {
            margin-top: 20px;
            padding: 15px;
            background-color: #e9ecef;
            border-radius: 4px;
        }
        .pagination {
            display: flex;
            list-style: none;
            padding: 0;
            margin: 20px 0;
        }
        .pagination li {
            margin-right: 5px;
        }
        .pagination li a {
            display: block;
            padding: 5px 10px;
            background-color: #f0f0f0;
            text-decoration: none;
            border-radius: 3px;
            color: #333;
        }
        .pagination li.active a {
            background-color: #007bff;
            color: white;
        }
        .filters {
            margin-bottom: 20px;
            padding: 10px;
            background-color: #f8f9fa;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        .clear-log {
            margin-top: 20px;
            text-align: right;
        }
        .clear-log button {
            background-color: #dc3545;
            color: white;
            border: none;
            padding: 8px 15px;
            cursor: pointer;
            border-radius: 4px;
        }
        .clear-log button:hover {
            background-color: #c82333;
        }
        .back-link {
            display: inline-block;
            margin-bottom: 20px;
            color: #007bff;
            text-decoration: none;
        }
        .back-link:hover {
            text-decoration: underline;
        }
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0,0,0,0.5);
        }
        .modal-content {
            background-color: white;
            margin: 10% auto;
            padding: 20px;
            width: 50%;
            box-shadow: 0 5px 15px rgba(0,0,0,0.3);
            position: relative;
        }
        .close {
            position: absolute;
            right: 20px;
            top: 10px;
            font-size: 28px;
            font-weight: bold;
            cursor: pointer;
        }
    </style>
</head>
<body>
    <div class="container">
        <a href="dashboard.html" class="back-link">← Retour au tableau de bord</a>
        <h1>Diagnostics des inscriptions aux Hackathons</h1>
        
        <?php if (isset($_GET['cleared'])): ?>
        <div class="info success-log">
            Le fichier journal a été nettoyé. Une sauvegarde a été créée avant l'effacement.
        </div>
        <?php endif; ?>
        
        <?php if (!$logExists): ?>
        <div class="info error-log">
            Le fichier journal n'existe pas. Aucune tentative d'inscription n'a encore été enregistrée.
        </div>
        <?php else: ?>
            
        <div class="filters">
            <h3>Filtres</h3>
            <form id="filterForm" method="get">
                <div style="display: flex; gap: 10px;">
                    <div>
                        <label for="status">Statut:</label>
                        <select name="status" id="status">
                            <option value="">Tous</option>
                            <option value="Success" <?php echo isset($_GET['status']) && $_GET['status'] === 'Success' ? 'selected' : ''; ?>>Succès</option>
                            <option value="Failed" <?php echo isset($_GET['status']) && $_GET['status'] === 'Failed' ? 'selected' : ''; ?>>Échec</option>
                            <option value="Unknown" <?php echo isset($_GET['status']) && $_GET['status'] === 'Unknown' ? 'selected' : ''; ?>>Inconnu</option>
                        </select>
                    </div>
                    <div>
                        <label for="type">Type:</label>
                        <select name="type" id="type">
                            <option value="">Tous</option>
                            <option value="individual" <?php echo isset($_GET['type']) && $_GET['type'] === 'individual' ? 'selected' : ''; ?>>Individuel</option>
                            <option value="team" <?php echo isset($_GET['type']) && $_GET['type'] === 'team' ? 'selected' : ''; ?>>Équipe</option>
                        </select>
                    </div>
                    <div>
                        <button type="submit">Filtrer</button>
                    </div>
                </div>
            </form>
        </div>
        
        <h2>Sessions d'inscription (<?php echo count($sessions); ?>)</h2>
        
        <?php
        $filteredSessions = $sessions;
        
        // Apply filters
        if (isset($_GET['status']) && !empty($_GET['status'])) {
            $filteredSessions = array_filter($filteredSessions, function($session) {
                return $session['status'] === $_GET['status'];
            });
        }
        
        if (isset($_GET['type']) && !empty($_GET['type'])) {
            $filteredSessions = array_filter($filteredSessions, function($session) {
                return $session['type'] === $_GET['type'];
            });
        }
        
        if (empty($filteredSessions)):
        ?>
        <div class="info">
            Aucune session d'inscription ne correspond aux critères de filtre.
        </div>
        <?php else: ?>
        
        <?php 
        // Pagination
        $sessionsPerPage = 10;
        $currentPage = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $totalPages = ceil(count($filteredSessions) / $sessionsPerPage);
        $currentPageSessions = array_slice($filteredSessions, ($currentPage - 1) * $sessionsPerPage, $sessionsPerPage);
        
        foreach ($currentPageSessions as $timestamp => $session): 
            $statusClass = strtolower($session['status']);
        ?>
        <div class="session <?php echo $statusClass; ?>">
            <div class="session-header" onclick="toggleSession(this.parentNode)">
                <div>
                    <strong>Date:</strong> <?php echo $session['start']; ?> | 
                    <strong>Type:</strong> <?php echo ucfirst($session['type']); ?> | 
                    <strong>Statut:</strong> <?php echo $session['status']; ?>
                    <?php if (!empty($session['team_name'])): ?>
                    | <strong>Équipe:</strong> <?php echo $session['team_name']; ?>
                    <?php endif; ?>
                </div>
                <div>
                    <strong>ID utilisateur:</strong> <?php echo $session['user_id']; ?>
                </div>
            </div>
            <div class="session-details">
                <h3>Détails de la session</h3>
                
                <?php if ($session['status'] === 'Failed' && isset($session['error_reason'])): ?>
                <div class="log-entry error-log">
                    <strong>Raison de l'échec:</strong> <?php echo htmlspecialchars($session['error_reason']); ?>
                </div>
                <?php endif; ?>
                
                <h4>Entrées du journal</h4>
                <?php foreach ($session['entries'] as $entry): 
                    $entryClass = '';
                    if (strpos($entry['message'], 'Erreur:') !== false || strpos($entry['message'], 'Exception:') !== false) {
                        $entryClass = 'error-log';
                    } elseif (strpos($entry['message'], 'Attention:') !== false) {
                        $entryClass = 'warning-log';
                    } elseif (strpos($entry['message'], 'Succès') !== false || strpos($entry['message'], 'réussie') !== false) {
                        $entryClass = 'success-log';
                    }
                ?>
                <div class="log-entry <?php echo $entryClass; ?>">
                    <strong>[<?php echo $entry['timestamp']; ?>]</strong>
                    <strong>[<?php echo $entry['source']; ?>]</strong>
                    <?php echo htmlspecialchars($entry['message']); ?>
                    <?php if (!empty($entry['content'])): ?>
                    <pre><?php echo htmlspecialchars($entry['content']); ?></pre>
                    <?php endif; ?>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endforeach; ?>
        
        <!-- Pagination -->
        <?php if ($totalPages > 1): ?>
        <ul class="pagination">
            <?php if ($currentPage > 1): ?>
            <li><a href="?page=1<?php echo isset($_GET['status']) ? '&status=' . $_GET['status'] : ''; ?><?php echo isset($_GET['type']) ? '&type=' . $_GET['type'] : ''; ?>">«</a></li>
            <li><a href="?page=<?php echo $currentPage - 1; ?><?php echo isset($_GET['status']) ? '&status=' . $_GET['status'] : ''; ?><?php echo isset($_GET['type']) ? '&type=' . $_GET['type'] : ''; ?>">‹</a></li>
            <?php endif; ?>
            
            <?php
            $startPage = max(1, $currentPage - 2);
            $endPage = min($totalPages, $currentPage + 2);
            
            for ($i = $startPage; $i <= $endPage; $i++):
            ?>
            <li <?php echo $i === $currentPage ? 'class="active"' : ''; ?>>
                <a href="?page=<?php echo $i; ?><?php echo isset($_GET['status']) ? '&status=' . $_GET['status'] : ''; ?><?php echo isset($_GET['type']) ? '&type=' . $_GET['type'] : ''; ?>"><?php echo $i; ?></a>
            </li>
            <?php endfor; ?>
            
            <?php if ($currentPage < $totalPages): ?>
            <li><a href="?page=<?php echo $currentPage + 1; ?><?php echo isset($_GET['status']) ? '&status=' . $_GET['status'] : ''; ?><?php echo isset($_GET['type']) ? '&type=' . $_GET['type'] : ''; ?>">›</a></li>
            <li><a href="?page=<?php echo $totalPages; ?><?php echo isset($_GET['status']) ? '&status=' . $_GET['status'] : ''; ?><?php echo isset($_GET['type']) ? '&type=' . $_GET['type'] : ''; ?>">»</a></li>
            <?php endif; ?>
        </ul>
        <?php endif; ?>
        
        <?php endif; // end of filtered sessions check ?>
        
        <div class="clear-log">
            <button onclick="showClearConfirm()">Effacer le journal</button>
        </div>
        
        <!-- Clear log confirmation modal -->
        <div id="clearLogModal" class="modal">
            <div class="modal-content">
                <span class="close" onclick="hideClearConfirm()">&times;</span>
                <h2>Confirmation</h2>
                <p>Êtes-vous sûr de vouloir effacer le journal des inscriptions? Cette action ne peut pas être annulée. Une sauvegarde sera créée avant l'effacement.</p>
                <form method="post" action="<?php echo $_SERVER['PHP_SELF']; ?>">
                    <input type="hidden" name="clear_log" value="yes">
                    <div style="text-align: right;">
                        <button type="button" onclick="hideClearConfirm()" style="background-color: #6c757d; color: white; border: none; padding: 8px 15px; margin-right: 10px; cursor: pointer;">Annuler</button>
                        <button type="submit" style="background-color: #dc3545; color: white; border: none; padding: 8px 15px; cursor: pointer;">Effacer</button>
                    </div>
                </form>
            </div>
        </div>
        
        <?php endif; // end of log exists check ?>
    </div>
    
    <script>
        function toggleSession(session) {
            session.classList.toggle('active');
        }
        
        function showClearConfirm() {
            document.getElementById('clearLogModal').style.display = 'block';
        }
        
        function hideClearConfirm() {
            document.getElementById('clearLogModal').style.display = 'none';
        }
        
        // Close modal when clicking outside of it
        window.onclick = function(event) {
            var modal = document.getElementById('clearLogModal');
            if (event.target === modal) {
                hideClearConfirm();
            }
        }
        
        // Auto-expand the first session
        document.addEventListener('DOMContentLoaded', function() {
            var firstSession = document.querySelector('.session');
            if (firstSession) {
                firstSession.classList.add('active');
            }
        });
    </script>
</body>
</html>