<?php
require_once __DIR__ . '/../controller/controller_apply.php';

try {
    $stmt = config::getConnexion()->prepare("SELECT * FROM candidature ORDER BY date DESC");
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
    <title>List of Applications</title>
    <link rel="stylesheet" href="../view/list1_styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <script src="../controller/job_script.js" defer></script>
</head>
<body>
    <div class="applications-container">
        <div class="applications-header">
            <h2>List of Applications</h2>
        </div>

        <!-- Search Bar and Sorting Dropdown -->
        <div class="search-container">
            <input type="text" id="searchInput" placeholder="Search for applications...">
            <div class="sort-icon-container">
                <i class="fas fa-sort sort-icon" id="sortIcon" title="Sort"></i>
                <div class="sort-menu" id="sortMenu">
                    <button data-sort="firstName">First Name</button>
                    <button data-sort="email">Email</button>
                    <button data-sort="role">Role</button>
                </div>
            </div>
            <a href="../controller/export_pdf.php" title="Export to PDF">
                <i class="fas fa-file-pdf export-icon"></i>
            </a>
        </div>

        <table class="applications-table">
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
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody id="applicationsTableBody">
                <?php foreach ($jobs as $job): ?>
                    <tr>
                        <td><?= htmlspecialchars($job['ID']) ?></td>
                        <td><?= htmlspecialchars($job['nom_candidat']) ?></td>
                        <td><?= htmlspecialchars($job['prenom_candidat']) ?></td>
                        <td><?= htmlspecialchars($job['email']) ?></td>
                        <td><?= htmlspecialchars($job['role']) ?></td>
                        <td><?= htmlspecialchars($job['adresse']) ?></td>
                        <td><?= htmlspecialchars($job['city']) ?></td>
                        <td><?= htmlspecialchars($job['Date']) ?></td>
                        <td><a href="<?= htmlspecialchars($job['resume']) ?>" target="_blank">View CV</a></td>
                        <td>
                            <!-- Edit Icon -->
                            <a href="edit_application.php?ID=<?= htmlspecialchars($job['ID']) ?>" class="edit-icon" title="Edit">
                                <i class="fas fa-pen"></i>
                            </a>
                            <!-- Delete Icon -->
                            <a href="delete_application.php?ID=<?= htmlspecialchars($job['ID']) ?>" class="delete-icon" title="Delete" onclick="return confirm('Are you sure you want to delete this application?');">
                                <i class="fas fa-trash"></i>
                            </a>
                        </td>
                        
                    </tr>
                <?php endforeach; ?>
            </tbody>
            </table>
        </div>

</body>
</html>
