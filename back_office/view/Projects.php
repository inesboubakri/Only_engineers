<?php
// Include database connection
require_once('../controller/db_connection.php');

// Initialize response array
$response = array('success' => false, 'message' => '');

// Check if form data was posted for a specific action
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Add, update, or delete operations here
    if (isset($_POST['action'])) {
        $action = $_POST['action'];

        switch ($action) {
            case 'create':
                // Insert new project
                $project = $_POST['project'];
                $description = $_POST['description'];
                $type = $_POST['type'];
                $skills_required = $_POST['skills_required'];
                $git_link = $_POST['git_link'];
                $status = 'inactive'; // Default to inactive

                try {
                    $stmt = $conn->prepare("INSERT INTO projet (project, description, type, skills_required, git_link, status) VALUES (?, ?, ?, ?, ?, ?)");
                    $stmt->execute([$project, $description, $type, $skills_required, $git_link, $status]);

                    $response['success'] = true;
                    $response['message'] = 'Project added successfully!';
                } catch (PDOException $e) {
                    $response['message'] = 'Error: ' . $e->getMessage();
                }
                break;

            case 'delete':
                // Delete project
                $projectId = $_POST['id'];

                try {
                    $stmt = $conn->prepare("DELETE FROM projet WHERE id = ?");
                    $stmt->execute([$projectId]);

                    $response['success'] = true;
                    $response['message'] = 'Project deleted successfully!';
                } catch (PDOException $e) {
                    $response['message'] = 'Error: ' . $e->getMessage();
                }
                break;

            case 'update':
                // Update project
                $id = $_POST['id'];
                $project = $_POST['project'];
                $description = $_POST['description'];
                $type = $_POST['type'];
                $skills_required = $_POST['skills_required'];
                $git_link = $_POST['git_link'];
                $status = $_POST['status'];

                try {
                    $stmt = $conn->prepare("UPDATE projet SET project = ?, description = ?, type = ?, skills_required = ?, git_link = ?, status = ? WHERE id = ?");
                    $stmt->execute([$project, $description, $type, $skills_required, $git_link, $status, $id]);

                    $response['success'] = true;
                    $response['message'] = 'Project updated successfully!';
                } catch (PDOException $e) {
                    $response['message'] = 'Error: ' . $e->getMessage();
                }
                break;

            default:
                $response['message'] = 'Invalid action';
        }
    }
    
    // Return the response as JSON
    echo json_encode($response);
    exit();
}

// Handle the GET request to display projects

// Get filter value if any
$filter = isset($_GET['filter']) ? $_GET['filter'] : 'All';

// Prepare query based on filter
$sql = "SELECT * FROM projet";
if ($filter != 'All') {
    $sql .= " WHERE type LIKE :filter";
}

// Prepare the statement
$stmt = $conn->prepare($sql);

// Bind the filter parameter if it exists
if ($filter != 'All') {
    $filterValue = "%" . $filter . "%";
    $stmt->bindParam(':filter', $filterValue, PDO::PARAM_STR);
}

// Execute query
$stmt->execute();

// Fetch all results
$projects = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Include the HTML template for rendering the table
include('Projects.html');

// Override the empty tbody with actual data from the database
echo '<script>
document.addEventListener("DOMContentLoaded", function() {
    const tableBody = document.getElementById("projectsTableBody");
    tableBody.innerHTML = `';

if ($projects) {
    foreach ($projects as $row) {
        echo '<tr>
            <td>' . $row['id'] . '</td>
            <td><div class="project-icon">' . substr($row['project'], 0, 1) . '</div> ' . $row['project'] . '</td>
            <td>' . $row['description'] . '</td>
            <td>' . $row['type'] . '</td>
            <td>' . $row['skills_required'] . '</td>
            <td><a href="' . $row['git_link'] . '" target="_blank">View on GitHub</a></td>
            <td><span class="status-badge ' . ($row['status'] == 'active' ? 'active-status' : 'inactive-status') . '">' . ucfirst($row['status']) . '</span></td>
            <td>
                <button class="edit-btn" onclick="openEditForm(' . $row['id'] . ', \'' . addslashes($row['project']) . '\', \'' . addslashes($row['description']) . '\', \'' . $row['type'] . '\', \'' . addslashes($row['skills_required']) . '\', \'' . $row['git_link'] . '\', \'' . $row['status'] . '\')">Edit</button>
                <button class="delete-btn" onclick="deleteProject(' . $row['id'] . ')">Delete</button>
            </td>
        </tr>';
    }
} else {
    echo '<tr><td colspan="8" style="text-align: center;">No projects found</td></tr>';
}

echo '`; 
});
</script>';
?>
