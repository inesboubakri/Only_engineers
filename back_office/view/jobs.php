<?php
define('BASE_PATH', dirname(__DIR__, 2)); // Points to C:\xampp\htdocs\projet_web\projet_web
require_once BASE_PATH . '/front_office/front_office/controller/job_offer.php';
require_once BASE_PATH . '/front_office/front_office/controller/controller_apply.php';

try {
    $pdo = config::getConnexion();
    $stmt = $pdo->prepare("SELECT * FROM candidature ORDER BY Date DESC"); // Adjust table name if needed
    $stmt->execute();
    $jobs = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Jobs Management | Dashboard</title>
    <link rel="stylesheet" href="styles.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        /* Styles for the applications table */
        .users-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 0.9em;
        }
        .users-table th, .users-table td {
            padding: 12px 15px;
            text-align: left;
            border-bottom: 1px solid #e0e0e0;
        }
        .users-table th {
            position: sticky;
            top: 0;
            background-color: #f8f9fa;
            font-weight: 600;
        }
        .users-table tr:hover {
            background-color: #f5f5f5;
        }
        .edit-icon, .delete-icon {
            color: #4f46e5;
            margin: 0 8px;
            text-decoration: none;
        }
        .delete-icon {
            color: #dc3545;
        }
        .edit-icon:hover, .delete-icon:hover {
            opacity: 0.7;
        }
        .view-cv {
            color: #007bff;
            text-decoration: none;
        }
        .view-cv:hover {
            text-decoration: underline;
        }
        .accept-icon {
            color: #28a745;
            text-decoration: none;
        }
        .accept-icon:hover {
            text-decoration: underline;
        }
        .reject-icon {
            color: #dc3545;
            text-decoration: none;
        }
        .reject-icon:hover {
            text-decoration: underline;
        }
        .pending-row {
            background-color: #fff3cd;
        }
    </style>
</head>
<body class="light-theme">
    <div class="dashboard-container">
        <!-- Sidebar -->
        <div class="sidebar">
            <div class="logo">
                <div class="logo-icon">W</div>
                <span>Wallet</span>
            </div>
            <nav class="sidebar-nav">
                <ul>
                    <li><div class="nav-icon">📊</div><span>Dashboard</span></li>
                    <li><div class="nav-icon">👥</div><span>Users</span></li>
                    <li class="active"><div class="nav-icon">👩🏻‍💻</div><span>Jobs</span></li>
                    <li><div class="nav-icon">🚀</div><span>Projects</span></li>
                    <li><div class="nav-icon">📰</div><span>News</span></li>
                    <li><div class="nav-icon">🏆</div><span>Hackathons</span></li>
                    <li><div class="nav-icon">📚</div><span>Courses</span></li>
                    <li><div class="nav-icon">💼</div><span>Opportunities</span></li>
                    <li><div class="nav-icon">🔔</div><span>Notifications</span><div class="notification-badge">1</div></li>
                    <li><div class="nav-icon">🚪</div><span>Sign out</span></li>
                </ul>
            </nav>
        </div>

        <!-- Main Content -->
        <div class="main-content">
            <!-- Header -->
            <div class="header">
                <h1>Hello <span class="wave-emoji">👋</span></h1>
                <div class="header-right">
                <div class="search-box">
                    <input type="text" placeholder="Search">
                    <div class="search-icon">🔍</div>
                </div>
                    <div class="header-theme-toggle">
                        <label class="theme-switch">
                            <input type="checkbox" id="theme-toggle">
                            <span class="slider round"></span>
                        </label>
                    </div>
                    <div class="user-profile">
                        <div class="avatar">
                            <img src="https://i.pravatar.cc/100?img=32" alt="Ines Boubakri">
                        </div>
                        <span>Candidate</span>
                    </div>
                </div>
            </div>

            <!-- Applications Content -->
            <div class="users-view">
                <!-- Top Cards Row -->
                <div class="users-top-cards">
                    <div class="service-card report-card">
                        <div class="service-icon blue">
                            <span>📋</span>
                        </div>
                        <div class="service-title">Applications Report</div>
                        <div class="service-description">Generate comprehensive reports about job applications</div>
                        <button class="service-button">Generate Report</button>
                    </div>
                    <div class="service-card stats-card">
                        <div class="service-icon purple">
                            <span>🔍</span>
                        </div>
                        <div class="service-title">Total Applications</div>
                        <div class="service-amount"><?php echo count($jobs); ?></div>
                        <div class="service-change positive">+10%</div>
                    </div>
                    <div class="service-card salary-card">
                        <div class="service-icon teal">
                            <span>📅</span>
                        </div>
                        <div class="service-title">Recent Applications</div>
                        <div class="service-amount"><?php echo count(array_filter($jobs, function($job) { return strtotime($job['Date']) >= strtotime('-30 days'); })); ?></div>
                        <div class="service-change positive">+5</div>
                    </div>
                </div>

                <!-- Applications Table Section -->
                <div class="card users-table-card">
                    <!-- Table Filter Options -->
                    <div class="table-filters">
                        <div class="filter-options">
                            <button class="filter-btn active">All</button>
                            <button class="filter-btn">Full-time</button>
                            <button class="filter-btn">Part-time</button>
                            <button class="filter-btn">Remote</button>
                            <button class="filter-btn">Internship</button>
                        </div>
                        <div class="table-actions">
                            <div class="view-toggle">
                                <button class="view-btn active" data-view="table">
                                    <span class="view-icon">≡</span>
                                </button>
                                <button class="view-btn" data-view="grid">
                                    <span class="view-icon">
                                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                            <rect x="3" y="3" width="7" height="7" rx="1" fill="currentColor"/>
                                            <rect x="3" y="14" width="7" height="7" rx="1" fill="currentColor"/>
                                            <rect x="14" y="3" width="7" height="7" rx="1" fill="currentColor"/>
                                            <rect x="14" y="14" width="7" height="7" rx="1" fill="currentColor"/>
                                        </svg>
                                    </span>
                                </button>
                            </div>
                            <button class="add-button">
                                <span class="add-icon">+</span>
                                <span>Add Application</span>
                            </button>
                        </div>
                    </div>

                    <!-- Applications Table -->
                    <div class="table-container">
                        <table class="users-table">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>First Name</th>
                                    <th>Last Name</th>
                                    <th>Email</th>
                                    <th>Role</th>
                                    <th>Address</th>
                                    <th>City</th>
                                    <th>Date</th>
                                    <th>Resume</th>
                                    <th>Skills</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody id="applicationsTableBody">
                                <?php foreach ($jobs as $job): ?>
                                    <tr class="<?= $job['status'] === 'pending' ? 'pending-row' : '' ?>">
                                        <td><?= htmlspecialchars($job['ID']) ?></td>
                                        <td><?= htmlspecialchars($job['nom_candidat']) ?></td>
                                        <td><?= htmlspecialchars($job['prenom_candidat']) ?></td>
                                        <td><?= htmlspecialchars($job['email']) ?></td>
                                        <td><?= htmlspecialchars($job['role']) ?></td>
                                        <td><?= htmlspecialchars($job['adresse']) ?></td>
                                        <td><?= htmlspecialchars($job['city']) ?></td>
                                        <td><?= htmlspecialchars($job['Date']) ?></td>
                                        <td><a href="/projet_web/projet_web/front_office/front_office/view/<?= htmlspecialchars($job['resume']) ?>" target="_blank" class="view-cv">View CV</a></td>
                                        <td><?= htmlspecialchars($job['skills'] ?: 'None') ?></td>
                                        <td>
                                        <?php if ($job['status'] === 'pending'): ?>
        <!-- Accept Button -->
        <a href="update_application_status.php?ID=<?= htmlspecialchars($job['ID']) ?>&status=accepted" class="accept-icon" title="Accept">
            <i class="fas fa-thumbs-up"></i> Accept
        </a>
        <!-- Reject Button -->
        <a href="update_application_status.php?ID=<?= htmlspecialchars($job['ID']) ?>&status=rejected" class="reject-icon" title="Reject">
            <i class="fas fa-ban"></i> Reject
        </a>
    <?php elseif ($job['status'] === 'accepted' || $job['status'] === 'rejected'): ?>
        <!-- Edit Button -->
        <a href="edit_application.php?ID=<?= htmlspecialchars($job['ID']) ?>" class="edit-icon" title="Edit">
            <i class="fas fa-pen"></i>
        </a>
        <!-- Delete Button -->
        <a href="delete_application.php?ID=<?= htmlspecialchars($job['ID']) ?>" class="delete-icon" title="Delete" onclick="return confirm('Are you sure you want to delete this application?');">
            <i class="fas fa-trash"></i>
        </a>
        <?php if ($job['status'] === 'accepted'): ?>
            <span class="status-icon" title="Accepted">
                <i class="fas fa-check-circle" style="color: green;"></i>
            </span>
        <?php endif; ?>
    <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                                <?php if (empty($jobs)): ?>
                                    <tr>
                                        <td colspan="11">No applications found.</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Theme toggle
        document.getElementById('theme-toggle').addEventListener('change', function() {
            document.body.classList.toggle('dark-theme');
            document.body.classList.toggle('light-theme');
        });

        // Table filtering
        document.querySelectorAll('.filter-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                document.querySelector('.filter-btn.active').classList.remove('active');
                this.classList.add('active');
                const filter = this.textContent.toLowerCase();
                const rows = document.querySelectorAll('#applicationsTableBody tr');
                rows.forEach(row => {
                    const role = row.querySelector('td:nth-child(5)').textContent.toLowerCase();
                    row.style.display = filter === 'all' || role.includes(filter) ? '' : 'none';
                });
            });
        });

        // View toggle
        document.querySelectorAll('.view-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                document.querySelector('.view-btn.active').classList.remove('active');
                this.classList.add('active');
                // Add grid view logic if needed
                console.log(`Switched to ${this.dataset.view} view`);
            });
        });

        // Add application button (placeholder for modal or form)
        document.querySelector('.add-button').addEventListener('click', function() {
            // Implement modal or redirect to add application form
            console.log('Add application clicked');
        });
        // Real-time search functionality
        document.querySelector('.search-box input').addEventListener('input', function () {
            const searchTerm = this.value.toLowerCase();
            const rows = document.querySelectorAll('#applicationsTableBody tr');

            rows.forEach(row => {
                const rowText = row.textContent.toLowerCase();
                if (rowText.includes(searchTerm)) {
                    row.style.display = ''; // Show row
                } else {
                    row.style.display = 'none'; // Hide row
                }
            });
        });
    </script>
</body>
</html>