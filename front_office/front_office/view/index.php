<?php
require_once __DIR__ . '/../controller/job_offer.php';
require_once __DIR__ . '/../controller/controller_apply.php';

try {
    $pdo = config::getConnexion();
    if (!$pdo) {
        throw new Exception("Failed to connect to the database.");
    }
    $stmt = $pdo->prepare("SELECT * FROM offre ORDER BY date DESC");
    $stmt->execute();
    $jobs = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    error_log("Database error: " . $e->getMessage());
    echo "An error occurred while fetching jobs. Please try again later.";
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>LuckyJob</title>
    <link rel="stylesheet" href="../view/styles.css">
    <link rel="stylesheet" href="../view/list1_styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <script src="../controller/script.js"></script>
    
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
    <div class="app-container">
        <nav class="navbar">
            <div class="nav-left">
                <a href="#" class="logo">
                    <img src="../assets/logo.png" alt="Only Engineers">
                </a>
            </div>
            <div class="nav-center">
                <nav class="nav-links">
                    <a href="../view/home.html">Home</a>
                    <a href="#">Dashboard</a>
                    <a href="../view/index.html" class="active">Jobs</a>
                    <a href="../view/projects.html">Projects</a>
                    <a href="../view/courses.html">Courses</a>
                    <a href="../view/hackathon.html">Hackathons</a>
                    <a href="../view/articles.html">Articles</a>
                    <a href="../view/networking.html">Networking</a>
                </nav>
            </div>
            <div class="nav-right">
                <div class="notification-wrapper">
                    <button class="icon-button notification">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"></path>
                            <path d="M13.73 21a2 2 0 0 1-3.46 0"></path>
                        </svg>
                    </button>
                    <span class="notification-dot"></span>
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
                        <path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"></path>
                    </svg>
                </button>
                <div class="user-profile">
                    <a href="../view/user-profile.html">
                        <img src="../assets/profil.jpg" alt="User profile" class="avatar">
                    </a>
                </div>
            </div>
        </nav>

        <div class="content">
            <aside class="sidebar">
                <div class="filters">
                    <div class="filters-header">
                        <h3>Filters</h3>
                        <button class="clear-all">Clear All</button>
                    </div>
                    <div class="search-container">
                        <div class="search-bar">
                            <input type="text" placeholder="Search jobs...">
                        </div>
                    </div>
                    <div class="filter-section">
                        <div class="filter-header">
                            <h4>Job Type</h4>
                            <button class="expand-btn">
                                <svg width="16" height="16" viewBox="0 0 16 16" fill="none">
                                    <path d="M4 6l4 4 4-4" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                                </svg>
                            </button>
                        </div>
                        <div class="filter-options">
                            <label class="radio-option">
                                <input type="radio" name="jobType">
                                <span class="label-text">All</span>
                                <span class="count">(1143)</span>
                            </label>
                            <label class="radio-option selected">
                                <input type="radio" name="jobType" checked>
                                <span class="label-text">Full-Time</span>
                                <span class="count">(510)</span>
                            </label>
                            <label class="radio-option">
                                <input type="radio" name="jobType">
                                <span class="label-text">Part-Time</span>
                                <span class="count">(324)</span>
                            </label>
                            <label class="radio-option">
                                <input type="radio" name="jobType">
                                <span class="label-text">Remote</span>
                                <span class="count">(234)</span>
                            </label>
                            <label class="radio-option">
                                <input type="radio" name="jobType">
                                <span class="label-text">Internship</span>
                                <span class="count">(65)</span>
                            </label>
                            <label class="radio-option">
                                <input type="radio" name="jobType">
                                <span class="label-text">Contract</span>
                                <span class="count">(10)</span>
                            </label>
                        </div>
                    </div>
                </div>
            </aside>

            <section class="jobs">
                <div class="jobs-header">
                    <h2>Recommended jobs <span class="count">386</span></h2>
                    <div class="sort">
                        <span>Sort by:</span>
                        <select>
                            <option>Last updated</option>
                        </select>
                        <button class="view-toggle">
                            <svg class="grid-icon" width="20" height="20" viewBox="0 0 20 20" fill="none">
                                <path d="M4 4h4v4H4zM12 4h4v4h-4zM4 12h4v4H4zM12 12h4v4h-4z" stroke="currentColor" stroke-width="2"/>
                            </svg>
                            <svg class="list-icon" width="20" height="20" viewBox="0 0 20 20" fill="none" style="display: none;">
                                <path d="M3 5h14M3 10h14M3 15h14" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                            </svg>
                        </button>
                    </div>
                </div>

                <div class="job-cards">
                    <div class="promo-card">
                        <div class="promo-content">
                            <h2>Reach thousands of Engineers</h2>
                            <a href="add_Job.html" target="_blank">
                                <button class="add-job-btn">Add Job</button>
                            </a>
                        </div>
                    </div>

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
                        <!-- Job Details Modal -->
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
                    <button class="apply-now-btn" data-offre-id="">Apply Now</button>
                </div>
                <!-- edit Button -->
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
            </section>
        </div>
    </div>

    <script>
    // JavaScript for Modal and Interactions
    document.addEventListener('DOMContentLoaded', () => {
    const modal = document.getElementById('jobDetailsModal');
    const applyButton = modal.querySelector('.apply-now-btn');
    const closeModal = modal.querySelector('.close-modal');

    // Open modal with job details
    document.querySelectorAll('.details').forEach(button => {
        button.addEventListener('click', () => {
            const data = button.dataset;
            modal.querySelector('.job-title').textContent = data.title;
            modal.querySelector('.company-name').textContent = data.company;
            modal.querySelector('.company-location').textContent = data.location;
            modal.querySelector('.job-description').textContent = data.description;
            modal.querySelector('.job-type').textContent = data.type;
            modal.querySelector('.company-logo').src = data.logo;
            applyButton.dataset.offreId = data.id;
            modal.style.display = 'block';
        });
    });

    // Apply button redirect
    applyButton.addEventListener('click', () => {
        const offreId = applyButton.dataset.offreId;
        if (offreId) {
            window.location.href = `../controller/controller_apply.php?id=${offreId}`;
        }
    });

    // Close modal
    closeModal.addEventListener('click', () => {
        modal.style.display = 'none';
    });

    // Close modal on outside click
    window.addEventListener('click', (event) => {
        if (event.target === modal) {
            modal.style.display = 'none';
        }
    });

    // Bookmark toggle
    document.querySelectorAll('.bookmark').forEach(button => {
        button.addEventListener('click', () => {
            button.classList.toggle('active');
        });
    });

    // Theme toggle
    const themeToggle = document.getElementById('themeToggle');
    const root = document.documentElement;
    const savedTheme = localStorage.getItem('theme') || 'light';
    root.setAttribute('data-theme', savedTheme);

    themeToggle.addEventListener('click', () => {
        const currentTheme = root.getAttribute('data-theme');
        const newTheme = currentTheme === 'light' ? 'dark' : 'light';
        root.setAttribute('data-theme', newTheme);
        localStorage.setItem('theme', newTheme);
    });
});
    </script>


</body>
</html>