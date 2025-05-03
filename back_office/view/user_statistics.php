<?php
// Set session parameters BEFORE starting the session
ini_set('session.gc_maxlifetime', 86400); // 24 hours
session_set_cookie_params(86400);

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
    
    // Get total user count
    $totalUsersStmt = $conn->query("SELECT COUNT(*) FROM users");
    $totalUsers = $totalUsersStmt->fetchColumn();
    
    // Get admin count
    $adminCountStmt = $conn->query("SELECT COUNT(*) FROM users WHERE is_admin = 1");
    $adminCount = $adminCountStmt->fetchColumn();
    
    // Get regular user count
    $regularUserCount = $totalUsers - $adminCount;
    
    // Get banned user count
    $bannedUsersStmt = $conn->query("SELECT COUNT(*) FROM users WHERE is_banned = 1");
    $bannedUsers = $bannedUsersStmt->fetchColumn();
    
    // Get active user count
    $activeUsers = $totalUsers - $bannedUsers;
    
    // Get users with profile pictures (verified)
    $verifiedUsersStmt = $conn->query("SELECT COUNT(*) FROM users WHERE profile_picture IS NOT NULL AND profile_picture != ''");
    $verifiedUsers = $verifiedUsersStmt->fetchColumn();
    
    // Get unverified user count
    $unverifiedUsers = $totalUsers - $verifiedUsers;
    
    // Get position statistics
    $positionStmt = $conn->query("SELECT position, COUNT(*) as count FROM users GROUP BY position ORDER BY count DESC");
    $positionStats = $positionStmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Get new users (registered in the last 30 days)
    $newUsersStmt = $conn->query("SELECT COUNT(*) FROM users WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)");
    $newUsers = $newUsersStmt->fetchColumn();
    
    // Monthly registration trends (past 6 months)
    $monthlyTrendsStmt = $conn->query("
        SELECT 
            DATE_FORMAT(created_at, '%Y-%m') as month,
            COUNT(*) as count
        FROM 
            users
        WHERE 
            created_at >= DATE_SUB(NOW(), INTERVAL 6 MONTH)
        GROUP BY 
            DATE_FORMAT(created_at, '%Y-%m')
        ORDER BY 
            month ASC
    ");
    $monthlyTrends = $monthlyTrendsStmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Generate months array for consistent chart even if some months have no registrations
    $monthsData = [];
    for ($i = 5; $i >= 0; $i--) {
        $monthKey = date('Y-m', strtotime("-$i month"));
        $monthsData[$monthKey] = 0;
    }
    
    // Fill in actual data
    foreach ($monthlyTrends as $trend) {
        if (isset($monthsData[$trend['month']])) {
            $monthsData[$trend['month']] = (int)$trend['count'];
        }
    }
    
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
    <title>User Statistics | OnlyEngineers Admin</title>
    <link rel="stylesheet" href="styles.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        .stats-container {
            padding: 20px;
        }
        
        .stats-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }
        
        .stats-header h1 {
            font-size: 24px;
            margin: 0;
        }
        
        .back-button {
            background-color: #f3f4f6;
            border: none;
            border-radius: 4px;
            padding: 8px 16px;
            display: flex;
            align-items: center;
            gap: 8px;
            cursor: pointer;
            transition: background-color 0.2s;
            font-size: 14px;
        }
        
        .back-button:hover {
            background-color: #e5e7eb;
        }
        
        .stats-cards {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .stat-card {
            background-color: white;
            border-radius: 8px;
            padding: 20px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
            display: flex;
            flex-direction: column;
        }
        
        .stat-card-header {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 15px;
        }
        
        .stat-icon {
            width: 40px;
            height: 40px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
        }
        
        .blue-bg {
            background-color: #dbeafe;
            color: #1e40af;
        }
        
        .green-bg {
            background-color: #d1fae5;
            color: #065f46;
        }
        
        .red-bg {
            background-color: #fee2e2;
            color: #b91c1c;
        }
        
        .purple-bg {
            background-color: #ede9fe;
            color: #5b21b6;
        }
        
        .amber-bg {
            background-color: #fef3c7;
            color: #92400e;
        }
        
        .stat-title {
            font-size: 14px;
            color: #6b7280;
            margin: 0;
        }
        
        .stat-value {
            font-size: 28px;
            font-weight: 600;
            margin: 5px 0;
        }
        
        .stat-percentage {
            font-size: 14px;
            display: flex;
            align-items: center;
            gap: 5px;
        }
        
        .positive {
            color: #10b981;
        }
        
        .negative {
            color: #ef4444;
        }
        
        .charts-container {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .chart-card {
            background-color: white;
            border-radius: 8px;
            padding: 20px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
            display: flex;
            flex-direction: column;
        }
        
        .chart-header {
            margin-bottom: 15px;
        }
        
        .chart-title {
            font-size: 18px;
            font-weight: 600;
            margin: 0;
        }
        
        .chart-description {
            font-size: 14px;
            color: #6b7280;
            margin: 5px 0 0 0;
        }
        
        /* Chart container with fixed height */
        .chart-container {
            position: relative;
            height: 300px;
            width: 100%;
            margin-top: 15px;
        }
        
        /* Ensure all charts have proper sizing */
        canvas {
            max-height: 300px;
        }
        
        .positions-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        
        .positions-table th, .positions-table td {
            padding: 10px 15px;
            text-align: left;
            border-bottom: 1px solid #e5e7eb;
        }
        
        .positions-table th {
            font-weight: 600;
            color: #6b7280;
        }
        
        .positions-table tr:last-child td {
            border-bottom: none;
        }
        
        .position-bar-container {
            width: 100%;
            background-color: #f3f4f6;
            height: 8px;
            border-radius: 4px;
            overflow: hidden;
        }
        
        .position-bar {
            height: 100%;
            background-color: #3b82f6;
            border-radius: 4px;
        }
        
        @media (max-width: 768px) {
            .charts-container {
                grid-template-columns: 1fr;
            }
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
                    <li><div class="nav-icon">📊</div><span>Dashboard</span></li>
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
                <h1>User Statistics <span class="wave-emoji">📊</span></h1>
                <div class="header-right">
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

            <!-- Statistics Content -->
            <div class="stats-container">
                <div class="stats-header">
                    <h1>User Analytics Dashboard</h1>
                    <button class="back-button" onclick="window.location.href='users.php'">
                        <span>←</span>
                        <span>Back to Users</span>
                    </button>
                </div>
                
                <!-- Stats Cards -->
                <div class="stats-cards">
                    <div class="stat-card">
                        <div class="stat-card-header">
                            <div class="stat-icon blue-bg">👥</div>
                            <div>
                                <h3 class="stat-title">Total Users</h3>
                            </div>
                        </div>
                        <div class="stat-value"><?php echo number_format($totalUsers); ?></div>
                        <div class="stat-percentage positive">
                            <span>↑</span>
                            <span><?php echo $newUsers; ?> new in last 30 days</span>
                        </div>
                    </div>
                    
                    <div class="stat-card">
                        <div class="stat-card-header">
                            <div class="stat-icon purple-bg">👤</div>
                            <div>
                                <h3 class="stat-title">Regular Users</h3>
                            </div>
                        </div>
                        <div class="stat-value"><?php echo number_format($regularUserCount); ?></div>
                        <div class="stat-percentage">
                            <span><?php echo round(($regularUserCount / $totalUsers) * 100); ?>% of total users</span>
                        </div>
                    </div>
                    
                    <div class="stat-card">
                        <div class="stat-card-header">
                            <div class="stat-icon amber-bg">👑</div>
                            <div>
                                <h3 class="stat-title">Admin Users</h3>
                            </div>
                        </div>
                        <div class="stat-value"><?php echo number_format($adminCount); ?></div>
                        <div class="stat-percentage">
                            <span><?php echo round(($adminCount / $totalUsers) * 100); ?>% of total users</span>
                        </div>
                    </div>
                    
                    <div class="stat-card">
                        <div class="stat-card-header">
                            <div class="stat-icon green-bg">✅</div>
                            <div>
                                <h3 class="stat-title">Active Users</h3>
                            </div>
                        </div>
                        <div class="stat-value"><?php echo number_format($activeUsers); ?></div>
                        <div class="stat-percentage positive">
                            <span>↑</span>
                            <span><?php echo round(($activeUsers / $totalUsers) * 100); ?>% of total users</span>
                        </div>
                    </div>
                    
                    <div class="stat-card">
                        <div class="stat-card-header">
                            <div class="stat-icon red-bg">🚫</div>
                            <div>
                                <h3 class="stat-title">Banned Users</h3>
                            </div>
                        </div>
                        <div class="stat-value"><?php echo number_format($bannedUsers); ?></div>
                        <div class="stat-percentage">
                            <span><?php echo round(($bannedUsers / $totalUsers) * 100); ?>% of total users</span>
                        </div>
                    </div>
                    
                    <div class="stat-card">
                        <div class="stat-card-header">
                            <div class="stat-icon green-bg">📷</div>
                            <div>
                                <h3 class="stat-title">Verified Users</h3>
                            </div>
                        </div>
                        <div class="stat-value"><?php echo number_format($verifiedUsers); ?></div>
                        <div class="stat-percentage positive">
                            <span>↑</span>
                            <span><?php echo round(($verifiedUsers / $totalUsers) * 100); ?>% of total users</span>
                        </div>
                    </div>
                    
                    <div class="stat-card">
                        <div class="stat-card-header">
                            <div class="stat-icon amber-bg">⚠️</div>
                            <div>
                                <h3 class="stat-title">Unverified Users</h3>
                            </div>
                        </div>
                        <div class="stat-value"><?php echo number_format($unverifiedUsers); ?></div>
                        <div class="stat-percentage">
                            <span><?php echo round(($unverifiedUsers / $totalUsers) * 100); ?>% of total users</span>
                        </div>
                    </div>
                    
                    <div class="stat-card">
                        <div class="stat-card-header">
                            <div class="stat-icon blue-bg">🆕</div>
                            <div>
                                <h3 class="stat-title">New Users (30 days)</h3>
                            </div>
                        </div>
                        <div class="stat-value"><?php echo number_format($newUsers); ?></div>
                        <div class="stat-percentage positive">
                            <span>↑</span>
                            <span><?php echo round(($newUsers / $totalUsers) * 100); ?>% of total users</span>
                        </div>
                    </div>
                </div>
                
                <!-- Charts -->
                <div class="charts-container">
                    <div class="chart-card">
                        <div class="chart-header">
                            <h2 class="chart-title">Admin vs Regular Users</h2>
                            <p class="chart-description">Distribution of administrators and regular users on the platform</p>
                        </div>
                        <div class="chart-container">
                            <canvas id="adminUsersChart"></canvas>
                        </div>
                    </div>
                    
                    <div class="chart-card">
                        <div class="chart-header">
                            <h2 class="chart-title">User Verification Status</h2>
                            <p class="chart-description">Distribution of verified vs unverified users</p>
                        </div>
                        <div class="chart-container">
                            <canvas id="verificationChart"></canvas>
                        </div>
                    </div>
                    
                    <div class="chart-card">
                        <div class="chart-header">
                            <h2 class="chart-title">User Positions</h2>
                            <p class="chart-description">Most common positions among users</p>
                        </div>
                        <?php if (count($positionStats) > 0) : ?>
                            <table class="positions-table">
                                <thead>
                                    <tr>
                                        <th>Position</th>
                                        <th>Count</th>
                                        <th>Percentage</th>
                                        <th>Distribution</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach(array_slice($positionStats, 0, 5) as $position): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($position['position'] ?: 'Not specified'); ?></td>
                                            <td><?php echo $position['count']; ?></td>
                                            <td><?php echo round(($position['count'] / $totalUsers) * 100); ?>%</td>
                                            <td>
                                                <div class="position-bar-container">
                                                    <div class="position-bar" style="width: <?php echo round(($position['count'] / $totalUsers) * 100); ?>%;"></div>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        <?php else: ?>
                            <p>No position data available.</p>
                        <?php endif; ?>
                    </div>
                    
                    <div class="chart-card">
                        <div class="chart-header">
                            <h2 class="chart-title">Active vs Banned Users</h2>
                            <p class="chart-description">Distribution of active and banned users on the platform</p>
                        </div>
                        <div class="chart-container">
                            <canvas id="bannedUsersChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Theme toggling
        document.addEventListener('DOMContentLoaded', function() {
            const savedTheme = localStorage.getItem('theme') || 'light';
            document.body.className = savedTheme + '-theme';
            
            const themeToggle = document.getElementById('theme-toggle');
            if (themeToggle) {
                themeToggle.checked = savedTheme === 'dark';
                themeToggle.addEventListener('change', function() {
                    const currentTheme = document.body.className.includes('light') ? 'light' : 'dark';
                    const newTheme = currentTheme === 'light' ? 'dark' : 'light';
                    
                    document.body.className = newTheme + '-theme';
                    localStorage.setItem('theme', newTheme);
                });
            }
            
            // Initialize admin vs regular users chart
            const adminUsersCtx = document.getElementById('adminUsersChart').getContext('2d');
            const adminUsersChart = new Chart(adminUsersCtx, {
                type: 'pie',
                data: {
                    labels: ['Regular Users', 'Admin Users'],
                    datasets: [{
                        data: [<?php echo $regularUserCount; ?>, <?php echo $adminCount; ?>],
                        backgroundColor: [
                            'rgba(139, 92, 246, 0.7)',
                            'rgba(245, 158, 11, 0.7)'
                        ],
                        borderColor: [
                            'rgba(139, 92, 246, 1)',
                            'rgba(245, 158, 11, 1)'
                        ],
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'bottom'
                        }
                    }
                }
            });
            
            // Initialize verification chart
            const verificationCtx = document.getElementById('verificationChart').getContext('2d');
            const verificationChart = new Chart(verificationCtx, {
                type: 'doughnut',
                data: {
                    labels: ['Verified Users', 'Unverified Users', 'Banned Users'],
                    datasets: [{
                        data: [
                            <?php echo $verifiedUsers - ($verifiedUsers * $bannedUsers / $totalUsers); ?>, 
                            <?php echo $unverifiedUsers - ($unverifiedUsers * $bannedUsers / $totalUsers); ?>,
                            <?php echo $bannedUsers; ?>
                        ],
                        backgroundColor: [
                            'rgba(16, 185, 129, 0.7)',
                            'rgba(245, 158, 11, 0.7)',
                            'rgba(239, 68, 68, 0.7)'
                        ],
                        borderColor: [
                            'rgba(16, 185, 129, 1)',
                            'rgba(245, 158, 11, 1)',
                            'rgba(239, 68, 68, 1)'
                        ],
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'bottom'
                        }
                    }
                }
            });
            
            // Initialize active vs banned users chart
            const bannedUsersCtx = document.getElementById('bannedUsersChart').getContext('2d');
            const bannedUsersChart = new Chart(bannedUsersCtx, {
                type: 'pie',
                data: {
                    labels: ['Active Users', 'Banned Users'],
                    datasets: [{
                        data: [<?php echo $activeUsers; ?>, <?php echo $bannedUsers; ?>],
                        backgroundColor: [
                            'rgba(16, 185, 129, 0.7)',
                            'rgba(239, 68, 68, 0.7)'
                        ],
                        borderColor: [
                            'rgba(16, 185, 129, 1)',
                            'rgba(239, 68, 68, 1)'
                        ],
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'bottom'
                        }
                    }
                }
            });
        });
    </script>
</body>
</html>