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
    
    // Count total users
    $totalUsersStmt = $conn->query("SELECT COUNT(*) FROM users");
    $totalUsers = $totalUsersStmt->fetchColumn();
    
    // Count verified users (assuming users with profile_picture are verified)
    $verifiedUsersStmt = $conn->query("SELECT COUNT(*) FROM users WHERE profile_picture IS NOT NULL AND profile_picture != ''");
    $verifiedUsers = $verifiedUsersStmt->fetchColumn();
    
    // Calculate percentage increase (hardcoded for demo purposes)
    $totalUsersPercentage = "+24.5%";
    $verifiedUsersPercentage = "+18.3%";
    
    // Fetch all users
    $stmt = $conn->prepare("SELECT user_id, full_name, email, password, profile_picture, position, is_admin FROM users");
    $stmt->execute();
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
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
    <title>Users Management | Dashboard</title>
    <link rel="stylesheet" href="styles.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        /* Styles spécifiques pour la table users */
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
        .user-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            object-fit: cover;
        }
        .status-badge {
            padding: 4px 8px;
            border-radius: 12px;
            font-size: 0.8em;
            font-weight: 500;
        }
        .verified {
            background-color: #d1fae5;
            color: #065f46;
        }
        .not-verified {
            background-color: #fee2e2;
            color: #b91c1c;
        }
        .admin-badge {
            background-color: #dbeafe;
            color: #1e40af;
        }
        /* Style for action buttons */
        .action-btn-group {
            display: flex;
            gap: 5px;
        }
        .edit-btn, .delete-btn {
            padding: 5px 10px;
            border-radius: 4px;
            border: none;
            cursor: pointer;
            font-size: 12px;
        }
        .edit-btn {
            background-color: #3b82f6;
            color: white;
        }
        .delete-btn {
            background-color: #ef4444;
            color: white;
        }
        .password-cell {
            max-width: 120px;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
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
                    <li class="active"><a href="users.php"><div class="nav-icon">👥</div><span>Users</span></a></li>
                    <li><a href="jobs.html"><div class="nav-icon">👩🏻‍💻</div><span>Jobs</span></a></li>
                    <li><a href="Projects.html"><div class="nav-icon">🚀</div><span>Projects</span></a></li>
                    <li><a href="articles.html"><div class="nav-icon">📰</div><span>News</span></a></li>
                    <li><a href="hackathons.html"><div class="nav-icon">🏆</div><span>Hackathons</span></a></li>
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
                <h1>Hello, Admin welcome back <span class="wave-emoji">👋</span></h1>
                <div class="header-right">
                    <div class="search-box">
                        <input type="text" placeholder="search">
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
                        <span>Admin User</span>
                    </div>
                </div>
            </div>

            <!-- Users Content -->
            <div class="users-view">
                <!-- Top Cards Row -->
                <div class="users-top-cards">
                    <div class="service-card report-card">
                        <div class="service-icon blue">
                            <span>📋</span>
                        </div>
                        <div class="service-title">Users Report</div>
                        <div class="service-description">Generate comprehensive users analytics report</div>
                        <button class="service-button">Generate Report</button>
                    </div>
                    
                    <div class="service-card stats-card">
                        <div class="service-icon purple">
                            <span>👥</span>
                        </div>
                        <div class="service-title">Total Users</div>
                        <div class="service-amount"><?php echo $totalUsers; ?></div>
                        <div class="service-change positive"><?php echo $totalUsersPercentage; ?></div>
                    </div>
                    
                    <div class="service-card verified-card">
                        <div class="service-icon teal">
                            <span>✅</span>
                        </div>
                        <div class="service-title">Verified Users</div>
                        <div class="service-amount"><?php echo $verifiedUsers; ?></div>
                        <div class="service-change positive"><?php echo $verifiedUsersPercentage; ?></div>
                    </div>
                </div>
                
                <!-- Users Table Section -->
                <div class="card users-table-card">
                    <!-- Success message after editing user -->
                    <?php if (isset($_GET['success']) && $_GET['success'] == 1): ?>
                        <div class="success-message" style="background-color: #d1fae5; color: #065f46; padding: 10px; border-radius: 4px; margin-bottom: 15px;">
                            User updated successfully!
                        </div>
                    <?php endif; ?>
                    
                    <!-- Table Filter Options -->
                    <div class="table-filters">
                        <div class="filter-options">
                            <button class="filter-btn active">All</button>
                            <button class="filter-btn">Verified</button>
                            <button class="filter-btn">Admins</button>
                            <button class="filter-btn">New</button>
                            <button class="filter-btn">Incomplete</button>
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
                                <span>Add User</span>
                            </button>
                        </div>
                    </div>
                    
                    <!-- Users Table -->
                    <div class="table-container">
                        <table class="users-table">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Profile</th>
                                    <th>Full Name</th>
                                    <th>Email</th>
                                    <th>Password (Hashed)</th>
                                    <th>Profile Picture</th>
                                    <th>Position</th>
                                    <th>Admin</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($users as $user): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($user['user_id']); ?></td>
                                        <td>
                                            <?php 
                                            $profilePicUrl = '../../front_office/front_office/ressources/profil.jpg'; // Default image
                                            
                                            if (!empty($user['profile_picture'])) {
                                                $picturePath = '../../front_office/front_office/ressources/profile_pictures/' . $user['profile_picture'];
                                                if (file_exists($picturePath)) {
                                                    $profilePicUrl = $picturePath;
                                                }
                                            }
                                            ?>
                                            <img src="<?php echo $profilePicUrl; ?>" class="user-avatar" alt="User Profile">
                                        </td>
                                        <td><?php echo htmlspecialchars($user['full_name']); ?></td>
                                        <td><?php echo htmlspecialchars($user['email']); ?></td>
                                        <td class="password-cell" title="<?php echo htmlspecialchars($user['password']); ?>">
                                            <?php echo htmlspecialchars(substr($user['password'], 0, 20) . '...'); ?>
                                        </td>
                                        <td><?php echo htmlspecialchars($user['profile_picture'] ?? 'No image'); ?></td>
                                        <td><?php echo htmlspecialchars($user['position']); ?></td>
                                        <td>
                                            <?php if ($user['is_admin'] == 1): ?>
                                                <span class="status-badge admin-badge">1</span>
                                            <?php else: ?>
                                                <span class="status-badge">0</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <div class="action-btn-group">
                                                <button class="edit-btn" data-id="<?php echo $user['user_id']; ?>">Edit</button>
                                                <button class="delete-btn" data-id="<?php echo $user['user_id']; ?>">Delete</button>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                                <?php if (empty($users)): ?>
                                    <tr>
                                        <td colspan="9" style="text-align: center;">No users found</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Include external JavaScript -->
    <script src="../controllers/userscontrollerfixed.js"></script>
</body>
</html>