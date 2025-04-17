<?php
// Include database connection
require_once('../controller/db_connection.php');

// Initialize message variables
$message = '';
$messageType = '';

// Check for messages from redirects
if (isset($_GET['success'])) {
    $messageType = 'success';
    $message = $_GET['success'] == 'created' ? 'Project added successfully!' : 
              ($_GET['success'] == 'updated' ? 'Project updated successfully!' : 
              ($_GET['success'] == 'deleted' ? 'Project deleted successfully!' : ''));
} elseif (isset($_GET['error'])) {
    $messageType = 'error';
    $message = 'Error: ' . $_GET['error'];
}

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

// Include the HTML template but with PHP table population
include('Projects.html');

// Override the empty tbody with actual data from database
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

// Add message display if needed
if (!empty($message)) {
    echo '<script>
    document.addEventListener("DOMContentLoaded", function() {
        const messageDiv = document.createElement("div");
        messageDiv.className = "message ' . $messageType . '";
        messageDiv.textContent = "' . $message . '";
        document.querySelector(".users-view").prepend(messageDiv);
        
        setTimeout(function() {
            messageDiv.style.display = "none";
        }, 5000);
    });
    </script>';
}

// Make filter buttons work
echo '<script>
document.addEventListener("DOMContentLoaded", function() {
    const filterButtons = document.querySelectorAll(".filter-btn");
    const filters = ["All", "Web", "Mobile", "AI", "Data"];
    
    for (let i = 0; i < filterButtons.length; i++) {
        filterButtons[i].addEventListener("click", function() {
            window.location.href = "Projects.php?filter=" + filters[i];
        });
        
        // Set active class based on current filter
        if (filters[i] === "' . $filter . '") {
            filterButtons[i].classList.add("active");
        } else {
            filterButtons[i].classList.remove("active");
        }
    }
});
</script>';
?>
