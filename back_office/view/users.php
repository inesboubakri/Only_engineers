<?php
// Set session parameters BEFORE starting the session
ini_set('session.gc_maxlifetime', 86400); // 24 hours
session_set_cookie_params(86400);

// Start session
session_start();

// Debug session information
error_log("Session in users.php: " . print_r($_SESSION, true));

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
    $stmt = $conn->prepare("SELECT user_id, full_name, email, password, profile_picture, position, is_admin, is_banned FROM users");
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
        .edit-btn, .delete-btn, .ban-btn {
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
        .ban-btn {
            background-color: #f59e0b;
            color: white;
        }
        .ban-btn.unban {
            background-color: #10b981;
        }
        .password-cell {
            max-width: 120px;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }
        
        /* Toast notification styles */
        .toast {
            position: fixed;
            bottom: 20px;
            right: 20px;
            background-color: #4CAF50;
            color: white;
            padding: 16px;
            border-radius: 5px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.2);
            opacity: 0;
            transform: translateY(20px);
            transition: opacity 0.3s, transform 0.3s;
            z-index: 1000;
        }
        
        .toast.show {
            opacity: 1;
            transform: translateY(0);
        }
        
        .toast.error {
            background-color: #f44336;
        }
        
        .toast.warning {
            background-color: #ff9800;
        }
        
        /* Search highlight styles */
        .highlight {
            background-color: #ffeb3b;
            padding: 2px;
            border-radius: 2px;
            font-weight: bold;
        }
        
        .search-active .users-table tr {
            display: none;
        }
        
        .search-active .users-table tr.match {
            display: table-row;
        }
        
        .search-box {
            position: relative;
        }
        
        .search-box input {
            padding-right: 30px;
            width: 200px;
            border-radius: 20px;
            border: 1px solid #ddd;
            padding: 8px 12px;
            transition: width 0.3s ease;
        }
        
        .search-box input:focus {
            width: 250px;
            outline: none;
            border-color: #3b82f6;
        }
        
        .search-icon {
            position: absolute;
            right: 10px;
            top: 50%;
            transform: translateY(-50%);
            cursor: pointer;
        }
        
        .search-count {
            position: absolute;
            right: 10px;
            top: 100%;
            font-size: 12px;
            color: #666;
            margin-top: 5px;
        }

        .stats-button, .add-button {
            display: flex;
            align-items: center;
            gap: 6px;
            background-color: #3b82f6;
            color: white;
            border: none;
            border-radius: 4px;
            padding: 8px 12px;
            font-size: 14px;
            font-weight: 500;
            cursor: pointer;
            transition: background-color 0.2s;
        }
        
        .stats-button {
            background-color: #8b5cf6;
            margin-right: 10px;
        }
        
        .stats-button:hover {
            background-color: #7c3aed;
        }
        
        .add-button:hover {
            background-color: #2563eb;
        }
        
        .stats-icon, .add-icon {
            font-size: 16px;
        }

        /* Export modal styles */
        .modal-backdrop {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            display: flex;
            justify-content: center;
            align-items: center;
            z-index: 1050;
            opacity: 0;
            visibility: hidden;
            transition: opacity 0.3s, visibility 0.3s;
        }
        
        .modal-backdrop.show {
            opacity: 1;
            visibility: visible;
        }
        
        .export-modal {
            background-color: white;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
            width: 400px;
            max-width: 90%;
            padding: 24px;
            transform: translateY(-20px);
            transition: transform 0.3s;
        }
        
        .modal-backdrop.show .export-modal {
            transform: translateY(0);
        }
        
        .export-modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 16px;
        }
        
        .export-modal-title {
            font-size: 18px;
            font-weight: 600;
            margin: 0;
        }
        
        .modal-close {
            background: none;
            border: none;
            font-size: 20px;
            cursor: pointer;
            color: #6b7280;
            padding: 4px;
        }
        
        .export-options {
            display: flex;
            flex-direction: column;
            gap: 12px;
            margin-bottom: 24px;
        }
        
        .export-option {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px;
            border: 1px solid #e5e7eb;
            border-radius: 6px;
            cursor: pointer;
            transition: background-color 0.2s;
        }
        
        .export-option:hover {
            background-color: #f9fafb;
        }
        
        .export-option.selected {
            border-color: #3b82f6;
            background-color: #eff6ff;
        }
        
        .export-icon {
            width: 40px;
            height: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
            border-radius: 6px;
        }
        
        .excel-icon {
            background-color: #e6f5e6;
            color: #166534;
        }
        
        .pdf-icon {
            background-color: #fee2e2;
            color: #b91c1c;
        }
        
        .export-option-content {
            flex: 1;
        }
        
        .export-option-title {
            font-weight: 600;
            margin: 0 0 4px 0;
        }
        
        .export-option-desc {
            font-size: 14px;
            color: #6b7280;
            margin: 0;
        }
        
        .export-modal-footer {
            display: flex;
            justify-content: flex-end;
            gap: 12px;
        }
        
        .export-cancel-btn, .export-confirm-btn {
            padding: 8px 16px;
            border-radius: 6px;
            font-size: 14px;
            font-weight: 500;
            cursor: pointer;
            transition: background-color 0.2s;
        }
        
        .export-cancel-btn {
            background-color: #f3f4f6;
            border: 1px solid #d1d5db;
            color: #374151;
        }
        
        .export-cancel-btn:hover {
            background-color: #e5e7eb;
        }
        
        .export-confirm-btn {
            background-color: #3b82f6;
            border: 1px solid #3b82f6;
            color: white;
        }
        
        .export-confirm-btn:hover {
            background-color: #2563eb;
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

                <div class="nav-icon">📊</div><span>Dashboard</span></li>
                    <li class="active"><div class="nav-icon">👥</div><span>Users</span></li>
                    <li><div class="nav-icon">👩🏻‍💻</div><span>jobs</span></li>
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
                            <button class="filter-btn">Users</button>
                            <button class="filter-btn">Admins</button>
                            <button class="filter-btn">Banned</button>
                            <button class="filter-btn">Unbanned</button>
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
                            <button class="stats-button">
                                <span class="stats-icon">📊</span>
                                <span>Statistics</span>
                            </button>
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
                                                <span class="status-badge admin-badge">Admin</span>
                                            <?php else: ?>
                                                <span class="status-badge">User</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <div class="action-btn-group">
                                                <button class="edit-btn" data-id="<?php echo $user['user_id']; ?>">Edit</button>
                                                <button class="delete-btn" data-id="<?php echo $user['user_id']; ?>">Delete</button>
                                                <button class="ban-btn <?php echo $user['is_banned'] ? 'unban' : 'ban'; ?>" data-id="<?php echo $user['user_id']; ?>" data-banned="<?php echo $user['is_banned']; ?>">
                                                    <?php echo $user['is_banned'] ? 'Unban' : 'Ban'; ?>
                                                </button>
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

    <!-- Export Modal -->
    <div class="modal-backdrop" id="exportModal">
        <div class="export-modal">
            <div class="export-modal-header">
                <h3 class="export-modal-title">Export Users Report</h3>
                <button class="modal-close" id="modalClose">&times;</button>
            </div>
            <div class="export-options">
                <div class="export-option" data-format="excel">
                    <div class="export-icon excel-icon">📊</div>
                    <div class="export-option-content">
                        <h4 class="export-option-title">Excel Format</h4>
                        <p class="export-option-desc">Export users table as Excel spreadsheet</p>
                    </div>
                </div>
                <div class="export-option" data-format="pdf">
                    <div class="export-icon pdf-icon">📄</div>
                    <div class="export-option-content">
                        <h4 class="export-option-title">PDF Format</h4>
                        <p class="export-option-desc">Export users table with statistics as PDF document</p>
                    </div>
                </div>
            </div>
            <div class="export-modal-footer">
                <button class="export-cancel-btn" id="cancelExport">Cancel</button>
                <button class="export-confirm-btn" id="confirmExport">Export</button>
            </div>
        </div>
    </div>

    <!-- Include external JavaScript -->
    <script src="../controllers/userscontrollerfixed.js"></script>
</body>
</html>