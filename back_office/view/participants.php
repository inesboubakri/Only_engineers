<?php
// Start session
session_start();

// Check if user is logged in and is an admin
if (!isset($_SESSION['user_id']) || $_SESSION['is_admin'] != 1) {
    // Redirect to login page if not logged in or not an admin
    header("Location: ../../front_office/front_office/view/signin.php");
    exit();
}

// Include database connection
require_once '../model/db_connectionback.php';

// Connect to the database
try {
    // Get database connection
    $conn = getConnection();
    
    if (!$conn) {
        throw new Exception("Failed to connect to database");
    }
    
    // Fetch all participants with hackathon details
    $query = "SELECT p.id, p.full_name, p.email, p.phone, p.participation_type, p.role, p.team_name, p.registration_date, 
                     h.id as hackathon_id, h.name as hackathon_name, p.photo
              FROM participants p 
              JOIN hackathons h ON p.hackathon_id = h.id 
              ORDER BY p.registration_date DESC";
    
    $stmt = $conn->prepare($query);
    $stmt->execute();
    $participants = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Get count of total participants
    $totalParticipantsStmt = $conn->query("SELECT COUNT(*) FROM participants");
    $totalParticipants = $totalParticipantsStmt->fetchColumn();
    
    // Get team count
    $teamsStmt = $conn->query("SELECT COUNT(DISTINCT team_name) FROM participants WHERE team_name IS NOT NULL AND team_name != ''");
    $totalTeams = $teamsStmt->fetchColumn();
    
    // Get individual participants count
    $individualStmt = $conn->query("SELECT COUNT(*) FROM participants WHERE participation_type = 'individual'");
    $totalIndividuals = $individualStmt->fetchColumn();
    
} catch(Exception $e) {
    echo "Database connection failed: " . $e->getMessage();
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hackathon Participants | Dashboard</title>
    <link rel="stylesheet" href="styles.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        .hackathon-thumbnail {
            width: 60px;
            height: 40px;
            object-fit: cover;
            border-radius: 4px;
        }
        
        .participant-photo {
            width: 40px;
            height: 40px;
            object-fit: cover;
            border-radius: 50%;
        }
        
        .status-badge {
            padding: 4px 8px;
            border-radius: 12px;
            font-size: 0.8em;
            font-weight: 500;
            display: inline-block;
        }
        
        .individual {
            background-color: #dbeafe;
            color: #1e40af;
        }
        
        .team {
            background-color: #d1fae5;
            color: #065f46;
        }
        
        .team_leader {
            background-color: #fef3c7;
            color: #92400e;
        }
        
        .service-button {
            display: inline-block;
            padding: 6px 12px;
            background-color: #4c6ef5;
            color: white;
            border: none;
            border-radius: 4px;
            font-size: 14px;
            cursor: pointer;
            text-decoration: none;
            margin-top: 10px;
            text-align: center;
        }
        
        .service-button:hover {
            background-color: #3b5bdb;
        }
        
        /* Filter dropdown */
        .filter-dropdown {
            position: relative;
            display: inline-block;
            margin-right: 15px;
        }
        
        .filter-dropdown select {
            padding: 8px 12px;
            border-radius: 4px;
            border: 1px solid #e2e8f0;
            background-color: white;
            cursor: pointer;
        }
        
        .dark-theme .filter-dropdown select {
            background-color: #2d3748;
            color: white;
            border-color: #4a5568;
        }
    </style>
</head>
<body class="light-theme">
    <div class="dashboard-container">
        <!-- Sidebar -->
        <div class="sidebar">
            <div class="logo">
                <div class="logo-icon">O</div>
                <span>OnlyEngineers</span>
            </div>
            
            <nav class="sidebar-nav">
                <ul>
                    <li><a href="dashboard.html"><div class="nav-icon">📊</div><span>Dashboard</span></a></li>
                    <li><a href="users.php"><div class="nav-icon">👥</div><span>Users</span></a></li>
                    <li><a href="jobs.html"><div class="nav-icon">👩🏻‍💻</div><span>jobs</span></a></li>
                    <li><a href="Projects.html"><div class="nav-icon">🚀</div><span>Projects</span></a></li>
                    <li><a href="articles.html"><div class="nav-icon">📰</div><span>News</span></a></li>
                    <li><a href="hackathons.php"><div class="nav-icon">🏆</div><span>Hackathons</span></a></li>
                    <li><a href="courses.html"><div class="nav-icon">📚</div><span>Courses</span></a></li>
                    <li><div class="nav-icon">💼</div><span>Opportunities</span></li>
                    <li><div class="nav-icon">🔔</div><span>Notifications</span><div class="notification-badge">1</div></li>
                    <li><a href="../../front_office/front_office/view/signin.php"><div class="nav-icon">🚪</div><span>Sign out</span></a></li>
                </ul>
            </nav>
        </div>

        <!-- Main Content -->
        <div class="main-content">
            <!-- Header -->
            <div class="header">
                <h1>Hackathon Participants <span class="wave-emoji">👥</span></h1>
                <div class="header-right">
                    <div class="search-box">
                        <input type="text" placeholder="Search participants...">
                        <div class="search-icon">🔍</div>
                    </div>
                    <!-- Theme toggle in header -->
                    <div class="header-theme-toggle">
                        <label class="theme-switch">
                            <input type="checkbox" id="theme-toggle">
                            <span class="slider round"></span>
                        </label>
                    </div>
                    <div class="user-profile">
                        <div class="avatar">
                            <img src="https://i.pravatar.cc/100?img=32" alt="Admin User">
                        </div>
                        <span><?php echo isset($_SESSION['full_name']) ? htmlspecialchars($_SESSION['full_name']) : 'Admin'; ?></span>
                    </div>
                </div>
            </div>

            <!-- Participants Content -->
            <div class="users-view">
                <!-- Top Cards Row -->
                <div class="users-top-cards">
                    <div class="service-card report-card">
                        <div class="service-icon blue">
                            <span>👥</span>
                        </div>
                        <div class="service-title">Total Participants</div>
                        <div class="service-amount"><?php echo $totalParticipants; ?></div>
                        <a href="hackathons.php" class="service-button">Back to Hackathons</a>
                    </div>
                    
                    <div class="service-card hackathon-stats-card">
                        <div class="service-icon purple">
                            <span>👤</span>
                        </div>
                        <div class="service-title">Individual Participants</div>
                        <div class="service-amount"><?php echo $totalIndividuals; ?></div>
                    </div>
                    
                    <div class="service-card company-card">
                        <div class="service-icon teal">
                            <span>👪</span>
                        </div>
                        <div class="service-title">Teams</div>
                        <div class="service-amount"><?php echo $totalTeams; ?></div>
                    </div>
                </div>
                
                <!-- Participants Table Section -->
                <div class="card users-table-card">
                    <!-- Table Filter Options -->
                    <div class="table-filters">
                        <div class="filter-options">
                            <button class="filter-btn active">All</button>
                            <button class="filter-btn">Individual</button>
                            <button class="filter-btn">Team Members</button>
                            <button class="filter-btn">Team Leaders</button>
                        </div>
                        <div class="table-actions">
                            <div class="filter-dropdown">
                                <select id="hackathon-filter">
                                    <option value="">All Hackathons</option>
                                    <?php
                                    // Fetch distinct hackathons
                                    $hackathonQuery = "SELECT DISTINCT h.id, h.name FROM hackathons h 
                                                      JOIN participants p ON h.id = p.hackathon_id 
                                                      ORDER BY h.name";
                                    $hackathonStmt = $conn->prepare($hackathonQuery);
                                    $hackathonStmt->execute();
                                    $hackathons = $hackathonStmt->fetchAll(PDO::FETCH_ASSOC);
                                    
                                    foreach ($hackathons as $hackathon) {
                                        echo '<option value="' . $hackathon['id'] . '">' . htmlspecialchars($hackathon['name']) . '</option>';
                                    }
                                    ?>
                                </select>
                            </div>
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
                        </div>
                    </div>
                    
                    <!-- Participants Table -->
                    <div class="table-container">
                        <table class="users-table">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Photo</th>
                                    <th>Name</th>
                                    <th>Email</th>
                                    <th>Phone</th>
                                    <th>Type</th>
                                    <th>Role</th>
                                    <th>Team</th>
                                    <th>Hackathon</th>
                                    <th>Registration Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($participants as $participant): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($participant['id']); ?></td>
                                        <td>
                                            <?php 
                                            // Determine correct image path based on what's stored in database
                                            $photoPath = '';
                                            
                                            if (!empty($participant['photo'])) {
                                                // Check if the photo path has resources prefix or not
                                                if (strpos($participant['photo'], 'ressources/') === 0) {
                                                    // Full path with 'ressources/' prefix 
                                                    $photoPath = '../../front_office/front_office/' . $participant['photo'];
                                                } else if (strpos($participant['photo'], 'participant_photos/') === 0) {
                                                    // Just 'participant_photos/' without 'ressources/' prefix
                                                    $photoPath = '../../front_office/ressources/' . $participant['photo'];
                                                } else {
                                                    // Just the filename - assume it's in participant_photos folder
                                                    $photoPath = '../../front_office/ressources/participant_photos/' . $participant['photo'];
                                                }
                                                
                                                if (file_exists($photoPath)) {
                                                    echo '<img src="' . $photoPath . '" class="participant-photo" alt="Participant">';
                                                } else {
                                                    echo '<span>No photo</span>';
                                                }
                                            } else {
                                                echo '<span>No photo</span>';
                                            }
                                            ?>
                                        </td>
                                        <td><?php echo htmlspecialchars($participant['full_name']); ?></td>
                                        <td><?php echo htmlspecialchars($participant['email']); ?></td>
                                        <td><?php echo htmlspecialchars($participant['phone']); ?></td>
                                        <td>
                                            <?php 
                                            $type = $participant['participation_type'];
                                            $badgeClass = 'individual';
                                            
                                            if ($type === 'team') {
                                                $badgeClass = 'team';
                                            }
                                            
                                            echo '<span class="status-badge ' . $badgeClass . '">' . ucfirst($type) . '</span>';
                                            ?>
                                        </td>
                                        <td>
                                            <?php 
                                            if ($participant['role'] === 'team_leader') {
                                                echo '<span class="status-badge team_leader">Team Leader</span>';
                                            } else {
                                                echo htmlspecialchars($participant['role'] ? $participant['role'] : '-');
                                            }
                                            ?>
                                        </td>
                                        <td><?php echo !empty($participant['team_name']) ? htmlspecialchars($participant['team_name']) : '-'; ?></td>
                                        <td><?php echo htmlspecialchars($participant['hackathon_name']); ?></td>
                                        <td><?php echo date('Y-m-d', strtotime($participant['registration_date'])); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                                <?php if (empty($participants)): ?>
                                    <tr>
                                        <td colspan="10" style="text-align: center;">No participants found</td>
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
        // Basic functionality for theme toggle
        document.getElementById('theme-toggle').addEventListener('change', function() {
            document.body.classList.toggle('dark-theme');
            document.body.classList.toggle('light-theme');
        });
        
        // Filter participants by hackathon
        document.addEventListener('DOMContentLoaded', function() {
            const hackathonFilter = document.getElementById('hackathon-filter');
            const tableRows = document.querySelectorAll('.users-table tbody tr');
            
            if (hackathonFilter) {
                hackathonFilter.addEventListener('change', function() {
                    const selectedHackathon = this.value;
                    
                    tableRows.forEach(row => {
                        const hackathonCell = row.querySelector('td:nth-child(9)'); // Hackathon column
                        const hackathonName = hackathonCell ? hackathonCell.textContent : '';
                        
                        if (!selectedHackathon || row.textContent.includes(selectedHackathon)) {
                            row.style.display = '';
                        } else {
                            row.style.display = 'none';
                        }
                    });
                });
            }
            
            // Participant type filter buttons
            const filterBtns = document.querySelectorAll('.filter-btn');
            if (filterBtns) {
                filterBtns.forEach(btn => {
                    btn.addEventListener('click', function() {
                        // Remove active class from all buttons
                        document.querySelectorAll('.filter-btn').forEach(b => b.classList.remove('active'));
                        // Add active class to clicked button
                        this.classList.add('active');
                        
                        const filterType = this.textContent.trim().toLowerCase();
                        
                        tableRows.forEach(row => {
                            if (filterType === 'all') {
                                row.style.display = '';
                            } else if (filterType === 'individual') {
                                const typeCell = row.querySelector('td:nth-child(6)'); // Type column
                                if (typeCell && typeCell.textContent.toLowerCase().includes('individual')) {
                                    row.style.display = '';
                                } else {
                                    row.style.display = 'none';
                                }
                            } else if (filterType === 'team members') {
                                const typeCell = row.querySelector('td:nth-child(6)'); // Type column
                                const roleCell = row.querySelector('td:nth-child(7)'); // Role column
                                if (typeCell && typeCell.textContent.toLowerCase().includes('team') && 
                                    !(roleCell && roleCell.textContent.toLowerCase().includes('leader'))) {
                                    row.style.display = '';
                                } else {
                                    row.style.display = 'none';
                                }
                            } else if (filterType === 'team leaders') {
                                const roleCell = row.querySelector('td:nth-child(7)'); // Role column
                                if (roleCell && roleCell.textContent.toLowerCase().includes('leader')) {
                                    row.style.display = '';
                                } else {
                                    row.style.display = 'none';
                                }
                            }
                        });
                    });
                });
            }
        });
    </script>
</body>
</html>