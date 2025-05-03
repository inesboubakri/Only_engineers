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
    
    // Get user statistics
    // Count total users
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
    
    // Fetch position statistics
    $positionStmt = $conn->query("SELECT position, COUNT(*) as count FROM users GROUP BY position ORDER BY count DESC LIMIT 5");
    $positionStats = $positionStmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Fetch all users
    $stmt = $conn->prepare("SELECT user_id, full_name, email, profile_picture, position, 
                              CASE WHEN is_admin = 1 THEN 'Yes' ELSE 'No' END as is_admin, 
                              CASE WHEN is_banned = 1 THEN 'Yes' ELSE 'No' END as is_banned 
                              FROM users");
    $stmt->execute();
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>OnlyEngineers Users Report - PDF Export</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 20px;
            color: #333;
        }
        
        .report-container {
            max-width: 1000px;
            margin: 0 auto;
        }
        
        .report-header {
            text-align: center;
            margin-bottom: 30px;
        }
        
        .report-title {
            font-size: 24px;
            font-weight: bold;
            margin-bottom: 5px;
        }
        
        .report-date {
            font-size: 14px;
            color: #666;
        }
        
        .stats-title {
            font-size: 18px;
            font-weight: bold;
            margin: 20px 0 10px 0;
            padding-bottom: 5px;
            border-bottom: 1px solid #ddd;
        }
        
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 15px;
            margin-bottom: 20px;
        }
        
        .stat-card {
            border: 1px solid #ddd;
            border-radius: 5px;
            padding: 15px;
        }
        
        .stat-card-title {
            font-size: 14px;
            color: #666;
            margin-bottom: 5px;
        }
        
        .stat-card-value {
            font-size: 24px;
            font-weight: bold;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        
        th, td {
            padding: 10px;
            border: 1px solid #ddd;
            text-align: left;
        }
        
        th {
            background-color: #f2f2f2;
            font-weight: bold;
        }
        
        tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        
        .positions-table {
            margin-bottom: 30px;
        }
        
        .position-bar-container {
            width: 100%;
            background-color: #f3f4f6;
            height: 10px;
            border-radius: 5px;
            overflow: hidden;
        }
        
        .position-bar {
            height: 100%;
            background-color: #3b82f6;
            border-radius: 5px;
        }
        
        .print-buttons {
            text-align: center;
            margin: 20px 0;
        }
        
        .print-button {
            background-color: #3b82f6;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            cursor: pointer;
            font-size: 14px;
        }
        
        .back-button {
            background-color: #f3f4f6;
            color: #333;
            border: 1px solid #ddd;
            padding: 10px 20px;
            border-radius: 5px;
            cursor: pointer;
            font-size: 14px;
            margin-left: 10px;
        }
        
        @media print {
            .print-buttons {
                display: none;
            }
            
            body {
                padding: 0;
                margin: 0;
            }
        }
    </style>
</head>
<body>
    <div class="report-container">
        <div class="print-buttons">
            <button class="print-button" onclick="window.print()">Print / Save as PDF</button>
            <button class="back-button" onclick="window.location.href='../view/users.php'">Back to Users</button>
        </div>
        
        <div class="report-header">
            <div class="report-title">OnlyEngineers Users Report</div>
            <div class="report-date">Generated on: <?php echo date('Y-m-d H:i:s'); ?></div>
        </div>
        
        <div class="stats-title">User Statistics Summary</div>
        
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-card-title">Total Users</div>
                <div class="stat-card-value"><?php echo number_format($totalUsers); ?></div>
            </div>
            
            <div class="stat-card">
                <div class="stat-card-title">Admin Users</div>
                <div class="stat-card-value"><?php echo number_format($adminCount); ?></div>
            </div>
            
            <div class="stat-card">
                <div class="stat-card-title">Regular Users</div>
                <div class="stat-card-value"><?php echo number_format($regularUserCount); ?></div>
            </div>
            
            <div class="stat-card">
                <div class="stat-card-title">Banned Users</div>
                <div class="stat-card-value"><?php echo number_format($bannedUsers); ?></div>
            </div>
            
            <div class="stat-card">
                <div class="stat-card-title">Verified Users</div>
                <div class="stat-card-value"><?php echo number_format($verifiedUsers); ?></div>
            </div>
            
            <div class="stat-card">
                <div class="stat-card-title">Unverified Users</div>
                <div class="stat-card-value"><?php echo number_format($unverifiedUsers); ?></div>
            </div>
        </div>
        
        <?php if (count($positionStats) > 0): ?>
        <div class="stats-title">Top User Positions</div>
        
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
                <?php foreach($positionStats as $position): ?>
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
        <?php endif; ?>
        
        <div class="stats-title">Users List</div>
        
        <table class="users-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Full Name</th>
                    <th>Email</th>
                    <th>Position</th>
                    <th>Admin</th>
                    <th>Banned</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach($users as $user): ?>
                <tr>
                    <td><?php echo htmlspecialchars($user['user_id']); ?></td>
                    <td><?php echo htmlspecialchars($user['full_name']); ?></td>
                    <td><?php echo htmlspecialchars($user['email']); ?></td>
                    <td><?php echo htmlspecialchars($user['position']); ?></td>
                    <td><?php echo $user['is_admin']; ?></td>
                    <td><?php echo $user['is_banned']; ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    
    <script>
        // Auto-print when the page loads
        window.addEventListener('load', function() {
            // Uncomment the line below if you want it to automatically open the print dialog
            // window.print();
        });
    </script>
</body>
</html>
<?php
} catch(Exception $e) {
    // In case of an error, return a JSON error response
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Error generating PDF report: ' . $e->getMessage()]);
    exit();
}
?>