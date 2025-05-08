<?php
require_once __DIR__ . '/../controller/job_offer.php';
require_once __DIR__ . '/../controller/controller_apply.php';

try {
    $stmt = config::getConnexion()->prepare("SELECT * FROM offre ORDER BY date DESC");
    $stmt->execute();
    $jobs = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Job List</title>
    <link rel="stylesheet" href="../view/list1_styles.css">
    <script src="../controller/job_script.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

    <style>
        /* Existing styles */
        .vertical-card-list {
            display: flex;
            flex-direction: column;
            gap: 1rem;
        }

    </style>
</head>
<body>
    <script src="../controller/script.js" defer></script>
    <h2>List of Job Offers</h2>
    <a class="icon-plus" href="add_Job.html">➕ </a>
    <br><br>

    <div class="job-cards">
        <?php foreach ($jobs as $job): ?>
            <?php
                $jobTitle = $job['titre'];
                $logoName = strtolower(str_replace(' ', '-', $job['entreprise']));
            ?>
    
            <div class="job-card">
                <div class="job-content <?= $logoName ?>">
                    <div class="card-header">
                        <span class="date"><?= htmlspecialchars($job['date']) ?></span>
                        <button class="bookmark">
                            <svg class="bookmark-outline" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M19 21l-7-5-7 5V5a2 2 0 0 1 2-2h10a2 2 0 0 1 2 2z"></path>
                            </svg>
                            <svg class="bookmark-filled" width="16" height="16" viewBox="0 0 24 24" fill="currentColor" stroke="none">
                                <path d="M19 21l-7-5-7 5V5a2 2 0 0 1 2-2h10a2 2 0 0 1 2 2z"></path>
                            </svg>
                        </button>
                    </div>
    
                    <h3><?= htmlspecialchars($job['entreprise']) ?></h3>
    
                    <div class="job-title">
                        <h4><?= htmlspecialchars($jobTitle) ?></h4>
                        <img src="ressources/<?= $logoName ?>.png" alt="<?= htmlspecialchars($job['entreprise']) ?>" class="company-logo">
                    </div>
    
                    <div class="tags">
                        <span><?= htmlspecialchars($job['type']) ?></span>
                        <span>Junior level</span>
                        <span>Distant</span>
                        <span>Project work</span>
                        <span>Flexible Schedule</span>
                    </div>
    
                    <?php if (!empty($job['description'])): ?>
                        <p class="description"><?= nl2br(htmlspecialchars($job['description'])) ?></p>
                    <?php endif; ?>
                </div>
    
                <div class="card-footer">
                    <div class="job-details">
                        <div class="salary">—</div>
                        <div class="location"><?= htmlspecialchars($job['emplacement']) ?></div>
                    </div>
                    <button class="details"
                        data-id="<?= htmlspecialchars($job['id']) ?>"
                        data-title="<?= htmlspecialchars($job['titre']) ?>"
                        data-company="<?= htmlspecialchars($job['entreprise']) ?>"
                        data-location="<?= htmlspecialchars($job['emplacement']) ?>"
                        data-description="<?= htmlspecialchars($job['description']) ?>"
                        data-type="<?= htmlspecialchars($job['type']) ?>"
                        data-logo="ressources/<?= $logoName ?>.png">
                        Details
                    </button>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <!-- Job Details Modal (one modal for all jobs) -->
    <div class="job-details-modal" id="jobDetailsModal">
    <div class="modal-content">
        <div class="modal-header">
            <div class="header-left">
                <button class="close-modal">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M18 6L6 18M6 6l12 12" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </button>
            </div>
            <div class="header-right">
                <button class="bookmark-btn">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M19 21l-7-5-7 5V5a2 2 0 0 1 2-2h10a2 2 0 0 1 2 2z"/>
                    </svg>
                </button>
                <button class="share-btn">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M4 12v8a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2v-8"/>
                        <polyline points="16 6 12 2 8 6"/>
                        <line x1="12" y1="2" x2="12" y2="15"/>
                    </svg>
                </button>
            </div>
        </div>
        
        <div class="modal-body">
            <div class="job-header">
                <h1 class="job-title"></h1>
                <div class="company-info">
                    <img class="company-logo" src="" alt="Company logo">
                    <div class="company-details">
                        <h2 class="company-name"></h2>
                        <p class="company-location"></p>
                    </div>
                </div>
                <div class="job-meta">
                    <span class="job-type"></span>
                    <span class="job-level"></span>
                    <span class="job-location"></span>
                    <span class="job-salary"></span>
                </div>
            </div>

            <div class="job-content">
                <div class="section">
                    <h3>About this role</h3>
                    <p class="job-description"></p>
                </div>

                <div class="section">
                    <h3>Qualifications</h3>
                    <ul class="qualifications-list"></ul>
                </div>

                <div class="section">
                    <h3>Responsibilities</h3>
                    <ul class="responsibilities-list"></ul>
                </div>

                <div class="section">
                    <h3>Similar Jobs</h3>
                    <div class="similar-jobs"></div>
                </div>
            </div>
        </div>

        <div class="modal-footer">
            <!-- Apply Now Button -->
            <button class="apply-now-btn" data-offre-id="<?= htmlspecialchars($job['id']) ?>">Apply Now</button>
            <!-- Edit Button -->
            <a href="edit_Job.php?id=<?= htmlspecialchars($job['id']) ?>" target="_blank" class="edit-job-btn">
                <i class="fas fa-edit"></i> Change
            </a>

            <!-- Delete Button -->
            <a href="delete_Job.php?id=<?= htmlspecialchars($job['id']) ?>" target="_blank" class="delete-job-btn">
                <i class="fas fa-trash"></i> Remove
            </a>
        </div>
        </div>
    </div>
<script>
    document.addEventListener('DOMContentLoaded', () => {
    const applyButtons = document.querySelectorAll('.apply-now-btn');

    applyButtons.forEach(button => {
        button.addEventListener('click', (event) => {
            // Prevent the default behavior
            event.preventDefault();

            // Retrieve the data-offre-id value
            const offreId = button.dataset.offreId;

            // Redirect to the apply page with the retrieved ID
            window.location.href = `../controller/controller_apply.php?id=${offreId}`;
        });
    });
});
</script>
</body>
</html>