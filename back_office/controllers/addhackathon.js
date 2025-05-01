/**
 * Hackathons Controller
 * Handles hackathon management functionality
 */

// Initialize the dashboard model
const dashboardModel = new DashboardModel();

// Track if the modal is open
let hackathonModalOpen = false;

// HACK: Override the initAddPaymentButton function from usersController.js
// This will prevent the default alert from showing
if (typeof window.initAddPaymentButton === 'function') {
    console.log('Overriding initAddPaymentButton function');
    window.initAddPaymentButton = function() {
        console.log('Add Payment button handler has been overridden by hackathon controller');
    };
}

// Initialize hackathon form functionality
function initHackathonForm() {
    console.log('Initializing hackathon form');
    
    // Remove any previous event listeners from the add button
    const addButton = document.querySelector('.add-button');
    
    if (addButton) {
        // Remove all existing event listeners by replacing the button
        const newButton = addButton.cloneNode(true);
        addButton.parentNode.replaceChild(newButton, addButton);
        
        // Add our event listener with immediate execution
        newButton.addEventListener('click', function(e) {
            console.log('Add hackathon button clicked');
            e.preventDefault();
            e.stopPropagation();
            openHackathonFormModal();
            return false;
        });
        
        // Direct onclick attribute as a fallback
        newButton.onclick = function(e) {
            console.log('Add hackathon onclick triggered');
            e.preventDefault();
            openHackathonFormModal();
            return false;
        };
    }
}

// Open the hackathon form modal
function openHackathonFormModal() {
    console.log('Opening hackathon form modal');
    
    // Don't open multiple modals
    if (hackathonModalOpen) return;
    hackathonModalOpen = true;
    
    // Create modal for adding a new hackathon
    const modal = document.createElement('div');
    modal.className = 'modal hackathon-modal';
    modal.innerHTML = `
        <div class="modal-content">
            <div class="modal-header">
                <h2>Add New Hackathon</h2>
                <button class="close-modal">&times;</button>
            </div>
            <div class="modal-body">
                <form id="add-hackathon-form" action="../model/add_hackathon.php" method="post" enctype="multipart/form-data">
                    <div class="form-group">
                        <label for="hackathon-name">Hackathon Name*</label>
                        <input type="text" id="hackathon-name" name="name" required>
                        <div class="error-message" id="name-error"></div>
                    </div>
                    
                    <div class="form-group">
                        <label for="hackathon-description">Description*</label>
                        <textarea id="hackathon-description" name="description" rows="4" required></textarea>
                        <div class="error-message" id="description-error"></div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="start-date">Start Date*</label>
                            <input type="date" id="start-date" name="start_date" required>
                            <div class="error-message" id="start-date-error"></div>
                        </div>
                        
                        <div class="form-group">
                            <label for="end-date">End Date*</label>
                            <input type="date" id="end-date" name="end_date" required>
                            <div class="error-message" id="end-date-error"></div>
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="start-time">Start Time*</label>
                            <input type="time" id="start-time" name="start_time" required>
                            <div class="error-message" id="start-time-error"></div>
                        </div>
                        
                        <div class="form-group">
                            <label for="end-time">End Time*</label>
                            <input type="time" id="end-time" name="end_time" required>
                            <div class="error-message" id="end-time-error"></div>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="location">Location*</label>
                        <input type="text" id="location" name="location" required>
                        <div class="error-message" id="location-error"></div>
                    </div>
                    
                    <div class="form-group">
                        <label for="required-skills">Required Skills*</label>
                        <textarea id="required-skills" name="required_skills" rows="3" required></textarea>
                        <div class="help-text">Enter skills separated by commas (e.g., Python, Machine Learning, Web Development)</div>
                        <div class="error-message" id="skills-error"></div>
                    </div>
                    
                    <div class="form-group">
                        <label for="organizer">Organizer*</label>
                        <input type="text" id="organizer" name="organizer" required>
                        <div class="error-message" id="organizer-error"></div>
                    </div>
                    
                    <div class="form-group">
                        <label for="max-participants">Maximum Participants*</label>
                        <input type="number" id="max-participants" name="max_participants" min="1" required>
                        <div class="error-message" id="max-participants-error"></div>
                    </div>
                    
                    <div class="form-group">
                        <label for="hackathon-image">Hackathon Image*</label>
                        <input type="file" id="hackathon-image" name="image" accept="image/*" required>
                        <div class="help-text">Upload an image for the hackathon (max size: 2MB)</div>
                        <div class="error-message" id="image-error"></div>
                        <div class="image-preview" id="image-preview"></div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button class="cancel-btn">Cancel</button>
                <button class="save-btn" id="submit-hackathon">Add Hackathon</button>
            </div>
        </div>
    `;
    
    // Add modal to the page
    document.body.appendChild(modal);
    
    // Show modal with animation
    setTimeout(() => {
        modal.classList.add('active');
    }, 10);
    
    // Set up image preview functionality
    const imageInput = document.getElementById('hackathon-image');
    const imagePreview = document.getElementById('image-preview');
    
    if (imageInput) {
        imageInput.addEventListener('change', function() {
            const file = this.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    imagePreview.innerHTML = `<img src="${e.target.result}" alt="Preview">`;
                    imagePreview.style.display = 'block';
                };
                reader.readAsDataURL(file);
                
                // Validate file size (max 2MB)
                if (file.size > 2 * 1024 * 1024) {
                    document.getElementById('image-error').textContent = 'File size exceeds 2MB limit';
                    this.value = '';
                    imagePreview.innerHTML = '';
                    imagePreview.style.display = 'none';
                } else {
                    document.getElementById('image-error').textContent = '';
                }
            }
        });
    }
    
    // Close modal handlers
    function closeModal() {
        modal.classList.remove('active');
        setTimeout(() => {
            modal.remove();
            hackathonModalOpen = false;
        }, 300);
    }
    
    modal.querySelector('.close-modal').addEventListener('click', closeModal);
    modal.querySelector('.cancel-btn').addEventListener('click', closeModal);
    
    // Form validation and submission
    const submitButton = document.getElementById('submit-hackathon');
    if (submitButton) {
        submitButton.addEventListener('click', function() {
            if (validateHackathonForm()) {
                submitHackathonForm();
            }
        });
    }
    
    // Set min date for start and end date
    const today = new Date().toISOString().split('T')[0];
    const startDateEl = document.getElementById('start-date');
    const endDateEl = document.getElementById('end-date');
    
    if (startDateEl && endDateEl) {
        startDateEl.min = today;
        endDateEl.min = today;
        
        // Update end date min when start date changes
        startDateEl.addEventListener('change', function() {
            endDateEl.min = this.value;
            if (endDateEl.value && new Date(endDateEl.value) < new Date(this.value)) {
                endDateEl.value = this.value;
            }
        });
    }
}

// Validate the hackathon form
function validateHackathonForm() {
    let isValid = true;
    
    // Clear previous error messages
    const errorElements = document.querySelectorAll('.error-message');
    errorElements.forEach(element => element.textContent = '');
    
    // Validate name (not pure numbers or symbols)
    const name = document.getElementById('hackathon-name').value.trim();
    if (!name) {
        document.getElementById('name-error').textContent = 'Hackathon name is required';
        isValid = false;
    } else if (!/[a-zA-Z]/.test(name) || /^\d+$/.test(name)) {
        document.getElementById('name-error').textContent = 'Name cannot be pure numbers or symbols';
        isValid = false;
    }
    
    // Validate description (10-255 words)
    const description = document.getElementById('hackathon-description').value.trim();
    const wordCount = description ? description.split(/\s+/).filter(word => word.length > 0).length : 0;
    if (!description) {
        document.getElementById('description-error').textContent = 'Description is required';
        isValid = false;
    } else if (wordCount < 10) {
        document.getElementById('description-error').textContent = 'Description must contain at least 10 words';
        isValid = false;
    } else if (wordCount > 255) {
        document.getElementById('description-error').textContent = 'Description cannot exceed 255 words';
        isValid = false;
    }
    
    // Validate dates
    const startDate = document.getElementById('start-date').value;
    const endDate = document.getElementById('end-date').value;
    if (!startDate) {
        document.getElementById('start-date-error').textContent = 'Start date is required';
        isValid = false;
    }
    if (!endDate) {
        document.getElementById('end-date-error').textContent = 'End date is required';
        isValid = false;
    }
    if (startDate && endDate && new Date(endDate) < new Date(startDate)) {
        document.getElementById('end-date-error').textContent = 'End date must be after start date';
        isValid = false;
    }
    
    // Validate times
    const startTime = document.getElementById('start-time').value;
    const endTime = document.getElementById('end-time').value;
    if (!startTime) {
        document.getElementById('start-time-error').textContent = 'Start time is required';
        isValid = false;
    }
    if (!endTime) {
        document.getElementById('end-time-error').textContent = 'End time is required';
        isValid = false;
    }
    if (startDate && endDate && startTime && endTime && startDate === endDate && startTime >= endTime) {
        document.getElementById('end-time-error').textContent = 'End time must be after start time on the same day';
        isValid = false;
    }
    
    // Validate location (not pure numbers or symbols)
    const location = document.getElementById('location').value.trim();
    if (!location) {
        document.getElementById('location-error').textContent = 'Location is required';
        isValid = false;
    } else if (!/[a-zA-Z]/.test(location) || /^\d+$/.test(location)) {
        document.getElementById('location-error').textContent = 'Location cannot be pure numbers or symbols';
        isValid = false;
    }
    
    // Validate required skills
    const skills = document.getElementById('required-skills').value.trim();
    if (!skills) {
        document.getElementById('skills-error').textContent = 'Required skills are required';
        isValid = false;
    }
    
    // Validate organizer (not pure numbers or symbols)
    const organizer = document.getElementById('organizer').value.trim();
    if (!organizer) {
        document.getElementById('organizer-error').textContent = 'Organizer is required';
        isValid = false;
    } else if (!/[a-zA-Z]/.test(organizer) || /^\d+$/.test(organizer)) {
        document.getElementById('organizer-error').textContent = 'Organizer name cannot be pure numbers or symbols';
        isValid = false;
    }
    
    // Validate max participants (must be a number)
    const maxParticipants = document.getElementById('max-participants').value.trim();
    if (!maxParticipants) {
        document.getElementById('max-participants-error').textContent = 'Maximum participants is required';
        isValid = false;
    } else if (!/^\d+$/.test(maxParticipants) || parseInt(maxParticipants) <= 0) {
        document.getElementById('max-participants-error').textContent = 'Maximum participants must be a positive number';
        isValid = false;
    }
    
    // Validate image
    const image = document.getElementById('hackathon-image').files[0];
    if (!image) {
        document.getElementById('image-error').textContent = 'Hackathon image is required';
        isValid = false;
    } else if (!['image/jpeg', 'image/png', 'image/gif', 'image/webp'].includes(image.type)) {
        document.getElementById('image-error').textContent = 'File must be a valid image (JPEG, PNG, GIF, WEBP)';
        isValid = false;
    } else if (image.size > 2 * 1024 * 1024) {
        document.getElementById('image-error').textContent = 'File size exceeds 2MB limit';
        isValid = false;
    }
    
    return isValid;
}

// Submit the hackathon form
function submitHackathonForm() {
    const form = document.getElementById('add-hackathon-form');
    const formData = new FormData(form);
    
    // Show loading state
    const submitButton = document.getElementById('submit-hackathon');
    const originalText = submitButton.textContent;
    submitButton.disabled = true;
    submitButton.textContent = 'Adding...';
    
    // Submit form via AJAX
    fetch(form.action, {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Show success message
            showToast('Hackathon added successfully!');
            
            // Close modal
            const modal = document.querySelector('.hackathon-modal');
            modal.classList.remove('active');
            setTimeout(() => {
                modal.remove();
                hackathonModalOpen = false;
            }, 300);
            
            // Refresh the hackathons table
            refreshHackathonsTable();
        } else {
            // Show error message
            showToast('Error: ' + data.message);
            
            // Reset button
            submitButton.disabled = false;
            submitButton.textContent = originalText;
        }
    })
    .catch(error => {
        // Show error message
        showToast('Error: ' + error.message);
        
        // Reset button
        submitButton.disabled = false;
        submitButton.textContent = originalText;
    });
}

// Function to refresh the hackathons table
function refreshHackathonsTable() {
    // TODO: Implement fetching hackathons from the server and updating the table
    // For now, just reload the page
    window.location.reload();
}

// Function to show a toast notification
function showToast(message) {
    const toast = document.createElement('div');
    toast.className = 'toast';
    toast.textContent = message;
    
    document.body.appendChild(toast);
    
    // Show toast
    setTimeout(() => {
        toast.classList.add('show');
    }, 10);
    
    // Hide toast after 3 seconds
    setTimeout(() => {
        toast.classList.remove('show');
        setTimeout(() => {
            toast.remove();
        }, 300);
    }, 3000);
}

// Ensure our code runs last - after all other scripts
document.addEventListener('DOMContentLoaded', function() {
    // Wait a bit to ensure all other scripts have run
    setTimeout(function() {
        // Override the user controller function first
        if (typeof window.initAddPaymentButton === 'function') {
            window.initAddPaymentButton = function() {
                console.log('Overridden initAddPaymentButton');
            };
        }
        
        // Override any other handlers directly on the button
        const addButton = document.querySelector('.add-button');
        if (addButton) {
            addButton.onclick = null;
        }
        
        // Now initialize our form
        console.log('Initializing hackathon form with delay');
        initHackathonForm();
        
        // Directly attach our handler to be safe
        document.querySelector('.add-button')?.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            openHackathonFormModal();
            return false;
        });
    }, 500); // Half second delay
});
