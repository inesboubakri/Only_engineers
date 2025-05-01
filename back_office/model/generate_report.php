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
require_once 'db_connectionback.php';

// Connect to the database
try {
    // Get database connection
    $conn = getConnection();
    
    if (!$conn) {
        throw new Exception("Failed to connect to database");
    }
    
    // Collect statistics
    $totalHackathons = $conn->query("SELECT COUNT(*) FROM hackathons")->fetchColumn();
    $activeHackathons = $conn->query("SELECT COUNT(*) FROM hackathons WHERE end_date >= CURDATE()")->fetchColumn();
    $pastHackathons = $conn->query("SELECT COUNT(*) FROM hackathons WHERE end_date < CURDATE()")->fetchColumn();
    $upcomingHackathons = $conn->query("SELECT COUNT(*) FROM hackathons WHERE start_date > CURDATE()")->fetchColumn();
    $totalParticipants = $conn->query("SELECT COUNT(*) FROM participants")->fetchColumn();
    $individualParticipants = $conn->query("SELECT COUNT(*) FROM participants WHERE participation_type = 'individual' OR team_name IS NULL OR team_name = ''")->fetchColumn();
    $teamParticipants = $conn->query("SELECT COUNT(*) FROM participants WHERE participation_type = 'team' AND team_name IS NOT NULL AND team_name != ''")->fetchColumn();
    $totalTeams = $conn->query("SELECT COUNT(DISTINCT team_name) FROM participants WHERE team_name IS NOT NULL AND team_name != ''")->fetchColumn();
    
    // Get participant count per hackathon for chart
    $hackathonStats = $conn->query("SELECT h.name, COUNT(p.id) as participant_count 
                                    FROM hackathons h 
                                    LEFT JOIN participants p ON h.id = p.hackathon_id 
                                    GROUP BY h.id 
                                    ORDER BY participant_count DESC 
                                    LIMIT 5")->fetchAll(PDO::FETCH_ASSOC);
    
    // Most recent hackathons
    $recentHackathons = $conn->query("SELECT name, start_date, end_date, location, organizer FROM hackathons ORDER BY start_date DESC LIMIT 5")->fetchAll(PDO::FETCH_ASSOC);
    
    // Prepare data for chart
    $labels = [];
    $data = [];
    $backgroundColors = ['#4e73df', '#1cc88a', '#36b9cc', '#f6c23e', '#e74a3b'];
    
    foreach ($hackathonStats as $index => $stat) {
        $labels[] = $stat['name'];
        $data[] = $stat['participant_count'];
    }
    
    // Generate HTML output with enhanced design
    header('Content-Type: text/html; charset=utf-8');
    echo '<!DOCTYPE html>
    <html>
    <head>
        <title>Hackathon Analytics Report</title>
        <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
        <style>
            :root {
                --primary: #4e73df;
                --success: #1cc88a;
                --info: #36b9cc;
                --warning: #f6c23e;
                --danger: #e74a3b;
                --secondary: #858796;
                --light: #f8f9fc;
                --dark: #2d3748;
                --shadow: 0 .15rem 1.75rem 0 rgba(58,59,69,.15);
            }
            
            body {
                font-family: "Inter", -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
                line-height: 1.6;
                margin: 0;
                color: #444;
                background-color: #f8f9fc;
                padding: 0;
            }
            
            .container {
                max-width: 1140px;
                margin: 0 auto;
                padding: 0 15px;
            }
            
            .report-header {
                background-color: var(--primary);
                color: white;
                padding: 30px 0;
                margin-bottom: 30px;
                box-shadow: var(--shadow);
            }
            
            .report-header h1 {
                font-size: 28px;
                font-weight: 700;
                margin: 0;
                text-align: center;
            }
            
            .report-header p {
                margin: 10px 0 0;
                text-align: center;
                opacity: 0.8;
                font-weight: 300;
            }
            
            .row {
                display: flex;
                flex-wrap: wrap;
                margin: 0 -15px;
            }
            
            .col {
                flex: 1;
                padding: 0 15px;
                min-width: 250px;
            }
            
            .stats-grid {
                display: grid;
                grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
                gap: 20px;
                margin-bottom: 30px;
            }
            
            .card {
                background-color: #fff;
                border-radius: 8px;
                box-shadow: var(--shadow);
                margin-bottom: 25px;
                overflow: hidden;
            }
            
            .card-header {
                padding: 15px 20px;
                background-color: #f8f9fc;
                border-bottom: 1px solid #e3e6f0;
                font-weight: 700;
                font-size: 16px;
                color: var(--primary);
                display: flex;
                justify-content: space-between;
                align-items: center;
            }
            
            .card-body {
                padding: 20px;
            }
            
            .stat-card {
                padding: 20px;
                display: flex;
                flex-direction: column;
                border-radius: 8px;
                height: 100%;
                transition: transform 0.2s ease;
            }
            
            .stat-card:hover {
                transform: translateY(-3px);
            }
            
            .stat-card.primary {
                border-left: 4px solid var(--primary);
            }
            
            .stat-card.success {
                border-left: 4px solid var(--success);
            }
            
            .stat-card.info {
                border-left: 4px solid var(--info);
            }
            
            .stat-card.warning {
                border-left: 4px solid var(--warning);
            }
            
            .stat-title {
                font-size: 14px;
                font-weight: 700;
                text-transform: uppercase;
                color: var(--secondary);
                margin: 0 0 5px;
            }
            
            .stat-value {
                font-size: 28px;
                font-weight: 700;
                margin: 0;
                color: var(--dark);
            }
            
            .stat-card.primary .stat-value {
                color: var(--primary);
            }
            
            .stat-card.success .stat-value {
                color: var(--success);
            }
            
            .stat-card.info .stat-value {
                color: var(--info);
            }
            
            .stat-card.warning .stat-value {
                color: var(--warning);
            }
            
            .section-title {
                font-size: 20px;
                font-weight: 700;
                margin: 30px 0 20px;
                color: var(--dark);
                padding-bottom: 10px;
                border-bottom: 2px solid #e3e6f0;
            }
            
            .chart-container {
                position: relative;
                height: 350px;
                margin-bottom: 30px;
            }
            
            table {
                width: 100%;
                border-collapse: collapse;
                margin: 20px 0;
                border-radius: 8px;
                overflow: hidden;
            }
            
            th {
                background-color: #f8f9fc;
                padding: 15px;
                text-align: left;
                font-weight: 600;
                color: var(--primary);
                border-bottom: 2px solid #e3e6f0;
            }
            
            td {
                padding: 15px;
                border-bottom: 1px solid #e3e6f0;
            }
            
            tr:hover {
                background-color: #f8f9fc;
            }
            
            .print-button {
                display: block;
                margin: 30px auto;
                padding: 12px 24px;
                background-color: var(--primary);
                color: white;
                border: none;
                border-radius: 5px;
                cursor: pointer;
                font-size: 16px;
                font-weight: 600;
                transition: background-color 0.2s ease;
            }
            
            .print-button:hover {
                background-color: #2e59d9;
            }
            
            .footer {
                text-align: center;
                margin-top: 50px;
                padding: 20px 0;
                color: var(--secondary);
                font-size: 14px;
                border-top: 1px solid #e3e6f0;
            }
            
            .doughnut-container {
                width: 100%;
                max-width: 500px;
                margin: 0 auto;
            }
            
            .participant-breakdown {
                display: flex;
                justify-content: center;
                margin: 30px 0;
                gap: 20px;
                flex-wrap: wrap;
            }
            
            .breakdown-item {
                flex: 1;
                min-width: 160px;
                max-width: 250px;
                padding: 15px;
                border-radius: 8px;
                background: white;
                box-shadow: var(--shadow);
                text-align: center;
            }
            
            .breakdown-value {
                font-size: 24px;
                font-weight: 700;
                margin-bottom: 5px;
            }
            
            .breakdown-label {
                font-size: 14px;
                color: var(--secondary);
                text-transform: uppercase;
            }
            
            @media print {
                body {
                    background-color: white;
                }
                .print-button {
                    display: none;
                }
                .card {
                    box-shadow: none;
                    border: 1px solid #e3e6f0;
                }
                .report-header {
                    box-shadow: none;
                }
            }
            
            @media (max-width: 768px) {
                .stats-grid {
                    grid-template-columns: 1fr;
                }
                .col {
                    flex: 100%;
                }
            }
        </style>
    </head>
    <body>
        <div class="report-header">
            <div class="container">
                <h1>Hackathon Analytics Report</h1>
                <p>Generated on: ' . date('F j, Y') . '</p>
            </div>
        </div>
        
        <div class="container">
            <div class="stats-grid">
                <div class="card">
                    <div class="stat-card primary">
                        <p class="stat-title">Total Hackathons</p>
                        <p class="stat-value">' . $totalHackathons . '</p>
                    </div>
                </div>
                <div class="card">
                    <div class="stat-card success">
                        <p class="stat-title">Active Hackathons</p>
                        <p class="stat-value">' . $activeHackathons . '</p>
                    </div>
                </div>
                <div class="card">
                    <div class="stat-card info">
                        <p class="stat-title">Total Participants</p>
                        <p class="stat-value">' . $totalParticipants . '</p>
                    </div>
                </div>
                <div class="card">
                    <div class="stat-card warning">
                        <p class="stat-title">Total Teams</p>
                        <p class="stat-value">' . $totalTeams . '</p>
                    </div>
                </div>
            </div>
            
            <h2 class="section-title">Hackathon Status Breakdown</h2>
            
            <div class="row">
                <div class="col">
                    <div class="card">
                        <div class="card-header">
                            Status Distribution
                        </div>
                        <div class="card-body">
                            <div class="doughnut-container">
                                <canvas id="statusChart"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="col">
                    <div class="card">
                        <div class="card-header">
                            Participant Type Analysis
                        </div>
                        <div class="card-body">
                            <div class="participant-breakdown">
                                <div class="breakdown-item">
                                    <div class="breakdown-value">' . $individualParticipants . '</div>
                                    <div class="breakdown-label">Individual</div>
                                </div>
                                <div class="breakdown-item">
                                    <div class="breakdown-value">' . $teamParticipants . '</div>
                                    <div class="breakdown-label">Team Members</div>
                                </div>
                                <div class="breakdown-item">
                                    <div class="breakdown-value">' . $totalTeams . '</div>
                                    <div class="breakdown-label">Unique Teams</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <h2 class="section-title">Most Popular Hackathons</h2>
            
            <div class="card">
                <div class="card-header">
                    Hackathon Participation Ranking
                </div>
                <div class="card-body">
                    <div class="chart-container">
                        <canvas id="popularHackathonChart"></canvas>
                    </div>
                </div>
            </div>
            
            <h2 class="section-title">Recent Hackathons</h2>
            
            <div class="card">
                <div class="card-header">
                    Latest Hackathon Events
                </div>
                <div class="card-body">
                    <table>
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Start Date</th>
                                <th>End Date</th>
                                <th>Location</th>
                                <th>Organizer</th>
                            </tr>
                        </thead>
                        <tbody>';
    
    foreach ($recentHackathons as $hackathon) {
        echo '<tr>
                <td>' . htmlspecialchars($hackathon['name']) . '</td>
                <td>' . htmlspecialchars($hackathon['start_date']) . '</td>
                <td>' . htmlspecialchars($hackathon['end_date']) . '</td>
                <td>' . htmlspecialchars($hackathon['location']) . '</td>
                <td>' . htmlspecialchars($hackathon['organizer']) . '</td>
              </tr>';
    }
    
    echo '</tbody>
                    </table>
                </div>
            </div>
            
            <button class="print-button" onclick="window.print()">Print Report</button>
            
            <div class="footer">
                <p>© ' . date('Y') . ' OnlyEngineers - Hackathon Management Dashboard</p>
            </div>
        </div>
        
        <script>
            // Status Chart (Doughnut Chart)
            const statusCtx = document.getElementById("statusChart").getContext("2d");
            const statusChart = new Chart(statusCtx, {
                type: "doughnut",
                data: {
                    labels: ["Active", "Upcoming", "Past"],
                    datasets: [{
                        data: [' . $activeHackathons . ', ' . $upcomingHackathons . ', ' . $pastHackathons . '],
                        backgroundColor: ["#1cc88a", "#4e73df", "#e74a3b"],
                        hoverBackgroundColor: ["#17a673", "#2e59d9", "#e02d1b"],
                        hoverBorderColor: "rgba(234, 236, 244, 1)",
                    }],
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: "bottom"
                        },
                        title: {
                            display: true,
                            text: "Hackathon Status Distribution",
                            font: {
                                size: 16
                            }
                        }
                    },
                    cutout: "70%"
                }
            });
            
            // Popular Hackathon Chart (Bar Chart)
            const popularCtx = document.getElementById("popularHackathonChart").getContext("2d");
            const popularChart = new Chart(popularCtx, {
                type: "bar",
                data: {
                    labels: ' . json_encode($labels) . ',
                    datasets: [{
                        label: "Participants",
                        data: ' . json_encode($data) . ',
                        backgroundColor: ' . json_encode($backgroundColors) . ',
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: false
                        },
                        title: {
                            display: true,
                            text: "Participant Count by Hackathon",
                            font: {
                                size: 16
                            }
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            grid: {
                                drawBorder: false,
                                color: "rgba(0, 0, 0, 0.05)"
                            },
                            ticks: {
                                precision: 0
                            }
                        },
                        x: {
                            grid: {
                                display: false
                            }
                        }
                    }
                }
            });
        </script>
    </body>
    </html>';
    
} catch(Exception $e) {
    echo "Error generating report: " . $e->getMessage();
    exit();
}
?>