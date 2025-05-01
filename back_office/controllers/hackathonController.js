/**
 * Hackathon Controller
 * Handles interactions between the hackathon view and model
 */

document.addEventListener('DOMContentLoaded', function() {
    // Load hackathons
    loadHackathons();
    
    // Load hackathon requests
    loadHackathonRequests();
    
    // Set up hackathon form submission event
    const addHackathonForm = document.getElementById('add-hackathon-form');
    if (addHackathonForm) {
        addHackathonForm.addEventListener('submit', function(e) {
            e.preventDefault();
            submitHackathonForm();
        });
    }
    
    // Set up hackathon edit form submission event
    const editHackathonForm = document.getElementById('edit-hackathon-form');
    if (editHackathonForm) {
        editHackathonForm.addEventListener('submit', function(e) {
            e.preventDefault();
            submitEditHackathonForm();
        });
    }
    
    // Set up delete hackathon confirmation
    const deleteConfirmBtn = document.getElementById('delete-confirm-btn');
    if (deleteConfirmBtn) {
        deleteConfirmBtn.addEventListener('click', confirmDeleteHackathon);
    }
});

/**
 * Load hackathons from the server
 */
function loadHackathons() {
    const hackathonsContainer = document.getElementById('hackathons-container');
    
    if (!hackathonsContainer) return;
    
    // Show loading state
    hackathonsContainer.innerHTML = '<div class="loading-spinner"></div>';
    
    // Fetch hackathons from the server
    fetch('../model/get_hackathons.php')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                renderHackathons(data.hackathons);
            } else {
                hackathonsContainer.innerHTML = '<p class="error">Failed to load hackathons: ' + data.message + '</p>';
            }
        })
        .catch(error => {
            console.error('Error:', error);
            hackathonsContainer.innerHTML = '<p class="error">An error occurred while loading hackathons</p>';
        });
}

/**
 * Load hackathon requests from the server
 */
function loadHackathonRequests() {
    const requestsContainer = document.getElementById('hackathon-requests-container');
    
    if (!requestsContainer) return;
    
    // Show loading state
    requestsContainer.innerHTML = '<div class="loading-spinner"></div>';
    
    // Fetch hackathon requests from the server
    fetch('../model/get_hackathon_requests.php')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                renderHackathonRequests(data.requests);
            } else {
                requestsContainer.innerHTML = '<p class="error">Failed to load hackathon requests: ' + data.message + '</p>';
            }
        })
        .catch(error => {
            console.error('Error:', error);
            requestsContainer.innerHTML = '<p class="error">An error occurred while loading hackathon requests</p>';
        });
}

/**
 * Render hackathons to the hackathons container
 */
function renderHackathons(hackathons) {
    const hackathonsContainer = document.getElementById('hackathons-container');
    
    if (!hackathonsContainer) return;
    
    if (hackathons.length === 0) {
        hackathonsContainer.innerHTML = '<p class="no-data">No hackathons found</p>';
        return;
    }
    
    let html = '<div class="table-responsive">';
    html += '<table class="table">';
    html += '<thead>';
    html += '<tr>';
    html += '<th>ID</th>';
    html += '<th>Name</th>';
    html += '<th>Location</th>';
    html += '<th>Start Date</th>';
    html += '<th>End Date</th>';
    html += '<th>Status</th>';
    html += '<th>Actions</th>';
    html += '</tr>';
    html += '</thead>';
    html += '<tbody>';
    
    hackathons.forEach(hackathon => {
        html += '<tr>';
        html += '<td>' + hackathon.id + '</td>';
        html += '<td>' + hackathon.name + '</td>';
        html += '<td>' + hackathon.location + '</td>';
        html += '<td>' + hackathon.start_date + '</td>';
        html += '<td>' + hackathon.end_date + '</td>';
        html += '<td>' + (hackathon.status || 'approved') + '</td>';
        html += '<td>';
        html += '<button class="btn btn-sm btn-primary edit-hackathon-btn" data-id="' + hackathon.id + '">Edit</button> ';
        html += '<button class="btn btn-sm btn-danger delete-hackathon-btn" data-id="' + hackathon.id + '">Delete</button>';
        html += '</td>';
        html += '</tr>';
    });
    
    html += '</tbody>';
    html += '</table>';
    html += '</div>';
    
    hackathonsContainer.innerHTML = html;
    
    // Add event listeners to edit and delete buttons
    document.querySelectorAll('.edit-hackathon-btn').forEach(button => {
        button.addEventListener('click', function() {
            const hackathonId = this.getAttribute('data-id');
            openEditHackathonModal(hackathonId);
        });
    });
    
    document.querySelectorAll('.delete-hackathon-btn').forEach(button => {
        button.addEventListener('click', function() {
            const hackathonId = this.getAttribute('data-id');
            openDeleteHackathonModal(hackathonId);
        });
    });
}

/**
 * Render hackathon requests to the requests container
 */
function renderHackathonRequests(requests) {
    const requestsContainer = document.getElementById('hackathon-requests-container');
    
    if (!requestsContainer) return;
    
    if (requests.length === 0) {
        requestsContainer.innerHTML = '<p class="no-data">No pending hackathon requests</p>';
        return;
    }
    
    let html = '<div class="table-responsive">';
    html += '<table class="table">';
    html += '<thead>';
    html += '<tr>';
    html += '<th>ID</th>';
    html += '<th>Name</th>';
    html += '<th>Submitted By</th>';
    html += '<th>Location</th>';
    html += '<th>Start Date</th>';
    html += '<th>End Date</th>';
    html += '<th>Actions</th>';
    html += '</tr>';
    html += '</thead>';
    html += '<tbody>';
    
    requests.forEach(request => {
        html += '<tr>';
        html += '<td>' + request.id + '</td>';
        html += '<td>' + request.name + '</td>';
        html += '<td>' + (request.username || 'Unknown') + '</td>';
        html += '<td>' + request.location + '</td>';
        html += '<td>' + request.start_date + '</td>';
        html += '<td>' + request.end_date + '</td>';
        html += '<td>';
        html += '<button class="btn btn-sm btn-success approve-request-btn" data-id="' + request.id + '">Approve</button> ';
        html += '<button class="btn btn-sm btn-danger reject-request-btn" data-id="' + request.id + '">Reject</button> ';
        html += '<button class="btn btn-sm btn-info view-request-btn" data-id="' + request.id + '">View Details</button>';
        html += '</td>';
        html += '</tr>';
    });
    
    html += '</tbody>';
    html += '</table>';
    html += '</div>';
    
    requestsContainer.innerHTML = html;
    
    // Add event listeners to action buttons
    document.querySelectorAll('.approve-request-btn').forEach(button => {
        button.addEventListener('click', function() {
            const requestId = this.getAttribute('data-id');
            processHackathonRequest(requestId, 'approve');
        });
    });
    
    document.querySelectorAll('.reject-request-btn').forEach(button => {
        button.addEventListener('click', function() {
            const requestId = this.getAttribute('data-id');
            processHackathonRequest(requestId, 'reject');
        });
    });
    
    document.querySelectorAll('.view-request-btn').forEach(button => {
        button.addEventListener('click', function() {
            const requestId = this.getAttribute('data-id');
            openViewRequestModal(requestId, requests);
        });
    });
}

/**
 * Process a hackathon request (approve or reject)
 */
function processHackathonRequest(requestId, action) {
    // Confirm with the user
    const actionText = action === 'approve' ? 'approve' : 'reject';
    if (!confirm(`Are you sure you want to ${actionText} this hackathon request?`)) {
        return;
    }
    
    // Prepare form data
    const formData = new FormData();
    formData.append('request_id', requestId);
    formData.append('action', action);
    
    // Send request to server
    fetch('../model/process_hackathon_request.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showAlert('success', data.message);
            // Reload hackathons and requests
            loadHackathons();
            loadHackathonRequests();
        } else {
            showAlert('danger', 'Error: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showAlert('danger', 'An error occurred while processing the request');
    });
}

/**
 * Open a modal to view hackathon request details
 */
function openViewRequestModal(requestId, requests) {
    // Find the request in the requests array
    const request = requests.find(req => req.id == requestId);
    
    if (!request) {
        showAlert('danger', 'Request not found');
        return;
    }
    
    // Create or get the modal
    let modal = document.getElementById('view-request-modal');
    
    if (!modal) {
        // Create modal if it doesn't exist
        modal = document.createElement('div');
        modal.id = 'view-request-modal';
        modal.className = 'modal fade';
        modal.setAttribute('tabindex', '-1');
        modal.setAttribute('aria-hidden', 'true');
        
        const modalHTML = `
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Hackathon Request Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div id="request-details-container"></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-success" id="modal-approve-btn">Approve</button>
                    <button type="button" class="btn btn-danger" id="modal-reject-btn">Reject</button>
                </div>
            </div>
        </div>
        `;
        
        modal.innerHTML = modalHTML;
        document.body.appendChild(modal);
        
        // Initialize modal
        new bootstrap.Modal(modal);
    }
    
    // Populate modal with request details
    const detailsContainer = document.getElementById('request-details-container');
    
    let html = '<div class="row">';
    
    // Left column - Text details
    html += '<div class="col-md-7">';
    html += '<div class="mb-3"><strong>Name:</strong> ' + request.name + '</div>';
    html += '<div class="mb-3"><strong>Organizer:</strong> ' + request.organizer + '</div>';
    html += '<div class="mb-3"><strong>Location:</strong> ' + request.location + '</div>';
    html += '<div class="mb-3"><strong>Date Range:</strong> ' + request.start_date + ' to ' + request.end_date + '</div>';
    html += '<div class="mb-3"><strong>Time:</strong> ' + request.start_time + ' to ' + request.end_time + '</div>';
    html += '<div class="mb-3"><strong>Max Participants:</strong> ' + request.max_participants + '</div>';
    html += '<div class="mb-3"><strong>Required Skills:</strong> ' + request.required_skills + '</div>';
    html += '<div class="mb-3"><strong>Submitted By:</strong> ' + (request.username || 'Unknown') + '</div>';
    html += '<div class="mb-3"><strong>Submitted At:</strong> ' + request.submitted_at + '</div>';
    html += '<div class="mb-3"><strong>Description:</strong><br>' + request.description + '</div>';
    html += '</div>';
    
    // Right column - Image
    html += '<div class="col-md-5">';
    html += '<div class="text-center mb-3">';
    html += '<img src="../../' + request.image + '" class="img-fluid rounded" alt="Hackathon Image" style="max-height: 300px;">';
    html += '</div>';
    html += '</div>';
    
    html += '</div>';
    
    detailsContainer.innerHTML = html;
    
    // Set up action buttons
    document.getElementById('modal-approve-btn').addEventListener('click', function() {
        // Hide the modal
        bootstrap.Modal.getInstance(modal).hide();
        // Process the request
        processHackathonRequest(requestId, 'approve');
    });
    
    document.getElementById('modal-reject-btn').addEventListener('click', function() {
        // Hide the modal
        bootstrap.Modal.getInstance(modal).hide();
        // Process the request
        processHackathonRequest(requestId, 'reject');
    });
    
    // Show the modal
    bootstrap.Modal.getInstance(modal).show();
}

/**
 * Open the edit hackathon modal
 */
function openEditHackathonModal(hackathonId) {
    // Get the hackathon data
    fetch('../model/get_hackathons.php?id=' + hackathonId)
        .then(response => response.json())
        .then(data => {
            if (data.success && data.hackathons.length > 0) {
                const hackathon = data.hackathons[0];
                
                // Populate the form
                document.getElementById('edit-hackathon-id').value = hackathon.id;
                document.getElementById('edit-name').value = hackathon.name;
                document.getElementById('edit-description').value = hackathon.description;
                document.getElementById('edit-location').value = hackathon.location;
                document.getElementById('edit-start-date').value = hackathon.start_date;
                document.getElementById('edit-end-date').value = hackathon.end_date;
                document.getElementById('edit-start-time').value = hackathon.start_time;
                document.getElementById('edit-end-time').value = hackathon.end_time;
                document.getElementById('edit-required-skills').value = hackathon.required_skills;
                document.getElementById('edit-organizer').value = hackathon.organizer;
                document.getElementById('edit-max-participants').value = hackathon.max_participants;
                
                // Show current image if available
                const currentImageContainer = document.getElementById('edit-current-image');
                if (currentImageContainer && hackathon.image) {
                    currentImageContainer.innerHTML = `
                        <div class="mb-3">
                            <label class="form-label">Current Image:</label>
                            <div>
                                <img src="../../${hackathon.image}" alt="Current hackathon image" style="max-height: 100px;">
                            </div>
                        </div>
                    `;
                }
                
                // Show the modal
                const editModal = new bootstrap.Modal(document.getElementById('edit-hackathon-modal'));
                editModal.show();
            } else {
                showAlert('danger', 'Failed to load hackathon details');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showAlert('danger', 'An error occurred while loading hackathon details');
        });
}

/**
 * Open the delete hackathon confirmation modal
 */
function openDeleteHackathonModal(hackathonId) {
    document.getElementById('delete-hackathon-id').value = hackathonId;
    const deleteModal = new bootstrap.Modal(document.getElementById('delete-hackathon-modal'));
    deleteModal.show();
}

/**
 * Submit the add hackathon form
 */
function submitHackathonForm() {
    const form = document.getElementById('add-hackathon-form');
    const formData = new FormData(form);
    
    // Validate form data
    if (!validateHackathonForm(formData)) {
        return;
    }
    
    // Show loading state
    const submitBtn = form.querySelector('button[type="submit"]');
    const originalBtnText = submitBtn.innerHTML;
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Submitting...';
    
    // Submit form data to server
    fetch('../model/add_hackathon.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showAlert('success', data.message);
            form.reset();
            // Close the modal if it's in a modal
            const modal = bootstrap.Modal.getInstance(document.getElementById('add-hackathon-modal'));
            if (modal) {
                modal.hide();
            }
            // Reload hackathons
            loadHackathons();
        } else {
            showAlert('danger', 'Error: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showAlert('danger', 'An error occurred while submitting the form');
    })
    .finally(() => {
        // Restore button state
        submitBtn.disabled = false;
        submitBtn.innerHTML = originalBtnText;
    });
}

/**
 * Submit the edit hackathon form
 */
function submitEditHackathonForm() {
    const form = document.getElementById('edit-hackathon-form');
    const formData = new FormData(form);
    
    // Validate form data
    if (!validateHackathonForm(formData, true)) {
        return;
    }
    
    // Show loading state
    const submitBtn = form.querySelector('button[type="submit"]');
    const originalBtnText = submitBtn.innerHTML;
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Updating...';
    
    // Submit form data to server
    fetch('../model/edit_hackathon.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showAlert('success', data.message);
            // Close the modal
            const modal = bootstrap.Modal.getInstance(document.getElementById('edit-hackathon-modal'));
            if (modal) {
                modal.hide();
            }
            // Reload hackathons
            loadHackathons();
        } else {
            showAlert('danger', 'Error: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showAlert('danger', 'An error occurred while updating the hackathon');
    })
    .finally(() => {
        // Restore button state
        submitBtn.disabled = false;
        submitBtn.innerHTML = originalBtnText;
    });
}

/**
 * Confirm and delete a hackathon
 */
function confirmDeleteHackathon() {
    const hackathonId = document.getElementById('delete-hackathon-id').value;
    
    if (!hackathonId) {
        showAlert('danger', 'Hackathon ID is missing');
        return;
    }
    
    // Show loading state
    const deleteBtn = document.getElementById('delete-confirm-btn');
    const originalBtnText = deleteBtn.innerHTML;
    deleteBtn.disabled = true;
    deleteBtn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Deleting...';
    
    // Prepare form data
    const formData = new FormData();
    formData.append('hackathon_id', hackathonId);
    
    // Send delete request to server
    fetch('../model/delete_hackathon.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showAlert('success', data.message);
            // Close the modal
            const modal = bootstrap.Modal.getInstance(document.getElementById('delete-hackathon-modal'));
            if (modal) {
                modal.hide();
            }
            // Reload hackathons
            loadHackathons();
        } else {
            showAlert('danger', 'Error: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showAlert('danger', 'An error occurred while deleting the hackathon');
    })
    .finally(() => {
        // Restore button state
        deleteBtn.disabled = false;
        deleteBtn.innerHTML = originalBtnText;
    });
}

/**
 * Validate the hackathon form data
 */
function validateHackathonForm(formData, isEdit = false) {
    // Basic validation
    const name = formData.get('name');
    const description = formData.get('description');
    const startDate = formData.get('start_date');
    const endDate = formData.get('end_date');
    const startTime = formData.get('start_time');
    const endTime = formData.get('end_time');
    const location = formData.get('location');
    const maxParticipants = formData.get('max_participants');
    
    if (!name || !description || !startDate || !endDate || !startTime || !endTime || !location || !maxParticipants) {
        showAlert('danger', 'Please fill in all required fields');
        return false;
    }
    
    // Validate name - must not be pure numbers
    if (/^\d+$/.test(name)) {
        showAlert('danger', 'Name cannot be pure numbers');
        return false;
    }
    
    // Validate dates - end date must be on or after start date
    const startDateObj = new Date(startDate);
    const endDateObj = new Date(endDate);
    
    if (endDateObj < startDateObj) {
        showAlert('danger', 'End date must be on or after start date');
        return false;
    }
    
    // Validate times - if dates are the same, end time must be after start time
    if (startDate === endDate) {
        const startTimeObj = new Date(`2000-01-01T${startTime}`);
        const endTimeObj = new Date(`2000-01-01T${endTime}`);
        
        if (endTimeObj <= startTimeObj) {
            showAlert('danger', 'End time must be after start time when dates are the same');
            return false;
        }
    }
    
    // Validate max participants - must be a positive number
    if (parseInt(maxParticipants) <= 0 || isNaN(parseInt(maxParticipants))) {
        showAlert('danger', 'Maximum participants must be a positive number');
        return false;
    }
    
    // Validate image - required for new hackathons
    const image = formData.get('image');
    if (!isEdit && (!image || image.size === 0)) {
        showAlert('danger', 'Please upload an image');
        return false;
    }
    
    return true;
}

/**
 * Show an alert message
 */
function showAlert(type, message) {
    const alertsContainer = document.getElementById('alerts-container');
    
    if (!alertsContainer) return;
    
    const alertHTML = `
    <div class="alert alert-${type} alert-dismissible fade show" role="alert">
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    `;
    
    alertsContainer.innerHTML = alertHTML + alertsContainer.innerHTML;
    
    // Auto-dismiss after 5 seconds
    setTimeout(() => {
        const alertElement = alertsContainer.querySelector('.alert');
        if (alertElement) {
            bootstrap.Alert.getInstance(alertElement)?.close();
        }
    }, 5000);
}