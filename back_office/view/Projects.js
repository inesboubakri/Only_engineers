// Open the Add Project Form popup
document.getElementById('addProjectButton').onclick = function() {
    document.getElementById('project').value = '';
    document.getElementById('description').value = '';
    document.getElementById('type').selectedIndex = 0;
    document.getElementById('skills_required').value = '';
    document.getElementById('git_link').value = '';
    document.getElementById('status').selectedIndex = 0;
    document.getElementById('addProjectForm').style.display = 'flex';
};

function closeForm() {
    document.getElementById('addProjectForm').style.display = 'none';
}

function openEditForm(id, project, description, type, skills, git, status) {
    document.getElementById('edit_id').value = id; // Set the ID for the project
    document.getElementById('edit_project').value = project;
    document.getElementById('edit_description').value = description;
    document.getElementById('edit_type').value = type;
    document.getElementById('edit_skills_required').value = skills;
    document.getElementById('edit_git_link').value = git;
    document.getElementById('edit_status').value = status;
    document.getElementById('editProjectForm').style.display = 'flex';
}

function closeEditForm() {
    document.getElementById('editProjectForm').style.display = 'none';
}

function deleteProject(id) {
    if (confirm('Are you sure you want to delete this project?')) {
        window.location.href = '../controller/delete_project.php?id=' + id;
    }
}

document.addEventListener("DOMContentLoaded", function() {
    loadProjects();
});




function loadProjects() {
    const xhr = new XMLHttpRequest();
    xhr.open("GET", "../controller/get_projects.php", true);

    xhr.onload = function() {
        if (this.status === 200) {
            try {
                const projects = JSON.parse(this.responseText);
                displayProjects(projects);
            } catch (e) {
                console.error("Error parsing JSON:", e);
                document.getElementById("projectsTableBody").innerHTML = 
                    "<tr><td colspan='8' style='text-align: center;'>Error loading projects</td></tr>";
            }
        } else {
            document.getElementById("projectsTableBody").innerHTML = 
                "<tr><td colspan='8' style='text-align: center;'>Error: " + this.status + "</td></tr>";
        }
    };

    xhr.onerror = function() {
        document.getElementById("projectsTableBody").innerHTML = 
            "<tr><td colspan='8' style='text-align: center;'>Network error occurred</td></tr>";
    };

    xhr.send();
}

function displayProjects(projects) {
    const tableBody = document.getElementById("projectsTableBody");

    if (projects.length === 0) {
        tableBody.innerHTML = "<tr><td colspan='8' style='text-align: center;'>No projects found</td></tr>";
        return;
    }

    let html = "";

    projects.forEach(function(project) {
        html += `
            <tr>
                <td>${project.id}</td>
                <td><div class="project-icon">${project.project.charAt(0)}</div> ${project.project}</td>
                <td>${project.description}</td>
                <td>${project.type}</td>
                <td>${project.skills_required}</td>
                <td><a href="${project.git_link}" target="_blank">View on GitHub</a></td>
                <td><span class="status-badge ${project.status === 'active' ? 'active-status' : 'inactive-status'}">
                    ${project.status.charAt(0).toUpperCase() + project.status.slice(1)}
                </span></td>
                <td>
                    <button class="edit-btn" onclick="openEditForm(${project.id}, '${project.project.replace(/'/g, "\\'")}', '${project.description.replace(/'/g, "\\'")}', '${project.type}', '${project.skills_required.replace(/'/g, "\\'")}', '${project.git_link}', '${project.status}')">Edit</button>
                    <button class="delete-btn" onclick="deleteProject(${project.id})">Delete</button>
                </td>
            </tr>
        `;
    });

    tableBody.innerHTML = html;
}




// Add validation for the Add Project form (no changes here)
document.querySelector('#addProjectForm form').onsubmit = function (event) {
    const project = document.getElementById('project').value.trim();
    const description = document.getElementById('description').value.trim();
    const type = document.getElementById('type').value;
    const skillsRequired = document.getElementById('skills_required').value.trim();
    const gitLink = document.getElementById('git_link').value.trim();
    const status = document.getElementById('status').value;

    let errors = [];

    // Validate Project Name
    if (!project) {
        errors.push('Project name is required.');
    } else if (project.length < 3 || project.length > 50) {
        errors.push('Project name must be between 3 and 50 characters.');
    }

    // Validate Description
    if (!description) {
        errors.push('Description is required.');
    } else if (description.length < 10) {
        errors.push('Description must be at least 10 characters long.');
    }

    // Validate Type
    const validTypes = ['Web Development', 'Mobile Development', 'AI/ML', 'Data Science', 'Blockchain'];
    if (!validTypes.includes(type)) {
        errors.push('Invalid project type selected.');
    }

    // Validate Skills Required
    if (!skillsRequired) {
        errors.push('Skills required field cannot be empty.');
    }

    // Validate GitHub Link
    if (!gitLink) {
        errors.push('GitHub link is required.');
    } else if (!isValidURL(gitLink)) {
        errors.push('Invalid GitHub link.');
    }

    // Validate Status
    if (!['active', 'inactive'].includes(status)) {
        errors.push('Invalid project status.');
    }

    if (errors.length > 0) {
        event.preventDefault();  // Prevent form submission
        alert(errors.join("\n"));
    }
};
// Add validation for the Edit Project form
document.querySelector('#editProjectForm form').onsubmit = function (event) {
    const project = document.getElementById('edit_project').value.trim();
    const description = document.getElementById('edit_description').value.trim();
    const type = document.getElementById('edit_type').value;
    const skillsRequired = document.getElementById('edit_skills_required').value.trim();
    const gitLink = document.getElementById('edit_git_link').value.trim();
    const status = document.getElementById('edit_status').value;

    let errors = [];

    // Validate Project Name
    if (!project) {
        errors.push('Project name is required.');
    } else if (project.length < 3 || project.length > 50) {
        errors.push('Project name must be between 3 and 50 characters.');
    }

    // Validate Description
    if (!description) {
        errors.push('Description is required.');
    } else if (description.length < 10) {
        errors.push('Description must be at least 10 characters long.');
    }

    // Validate Type
    const validTypes = ['Web Development', 'Mobile Development', 'AI/ML', 'Data Science', 'Blockchain'];
    if (!validTypes.includes(type)) {
        errors.push('Invalid project type selected.');
    }

    // Validate Skills Required
    if (!skillsRequired) {
        errors.push('Skills required field cannot be empty.');
    }

    // Validate GitHub Link
    if (!gitLink) {
        errors.push('GitHub link is required.');
    } else if (!isValidURL(gitLink)) {
        errors.push('Invalid GitHub link.');
    }

    // Validate Status
    if (!['active', 'inactive'].includes(status)) {
        errors.push('Invalid project status.');
    }

    if (errors.length > 0) {
        event.preventDefault();  // Prevent form submission
        alert(errors.join("\n"));
    }
};

function isValidURL(url) {
    const regex = /^(https?|ftp):\/\/[^\s/$.?#].[^\s]*$/i;
    return regex.test(url);
}
