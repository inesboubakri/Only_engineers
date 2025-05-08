// Wait for DOM to be fully loaded before attaching event listeners
document.addEventListener('DOMContentLoaded', function() {
    console.log("DOM fully loaded");
    
    // Modal functionality
    const modal = document.getElementById("addCourseModal");
    const editModal = document.getElementById("editCourseModal");
    const addCourseBtn = document.getElementById("addCourseBtn");
    const closeModalBtn = document.querySelector(".close-modal");
    const closeEditModalBtn = document.getElementById("closeEditModal");
    const cancelAddCourseBtn = document.getElementById("cancelAddCourse");
    const cancelEditCourseBtn = document.getElementById("cancelEditCourse");
    const addCourseForm = document.getElementById("addCourseForm");
    const editCourseForm = document.getElementById("editCourseForm");
    
    // Open modal when Add Course button is clicked
    addCourseBtn.addEventListener("click", function() {
        console.log("Add Course button clicked");
        modal.style.display = "block";
        // Force the modal to be visible with inline styles
        modal.style.cssText = "display: block !important; opacity: 1; visibility: visible;";
        document.body.style.overflow = "hidden"; // Prevent scrolling behind modal
    });
    
    // Close modal when close button is clicked
    closeModalBtn.addEventListener("click", function() {
        modal.style.cssText = "";
        modal.style.display = "none";
        document.body.style.overflow = ""; // Re-enable scrolling
    });
    
    // Close edit modal when close button is clicked
    closeEditModalBtn.addEventListener("click", function() {
        editModal.style.cssText = "";
        editModal.style.display = "none";
        document.body.style.overflow = ""; // Re-enable scrolling
    });
    
    // Close modal when cancel button is clicked
    cancelAddCourseBtn.addEventListener("click", function() {
        modal.style.cssText = "";
        modal.style.display = "none";
        document.body.style.overflow = "";
        document.getElementById("addCourseForm").reset();
    });
    
    // Close edit modal when cancel button is clicked
    cancelEditCourseBtn.addEventListener("click", function() {
        editModal.style.cssText = "";
        editModal.style.display = "none";
        document.body.style.overflow = "";
    });
    
    // Close modal when clicking outside of modal content
    window.addEventListener("click", function(event) {
        if (event.target == modal) {
            modal.style.cssText = "";
            modal.style.display = "none";
            document.body.style.overflow = "";
        } else if (event.target == editModal) {
            editModal.style.cssText = "";
            editModal.style.display = "none";
            document.body.style.overflow = "";
        }
    });
    
    // Basic functionality for theme toggle
    document.getElementById('theme-toggle').addEventListener('change', function() {
        document.body.classList.toggle('dark-theme');
        document.body.classList.toggle('light-theme');
    });
    
    // Table filtering functionality
    document.querySelectorAll('.filter-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            document.querySelector('.filter-btn.active').classList.remove('active');
            this.classList.add('active');
            // Actual filtering logic would go here
            console.log(`Filtering by: ${this.textContent}`);
        });
    });
    
    // Form validation functions
    function validateCourseId(id) {
        // Course ID should follow format CRS-XXX (where X is a digit)
        const regex = /^CRS-\d{3}$/;
        return regex.test(id);
    }

    function validateCourseIcon(icon) {
        // Icon should not be empty
        return icon.trim() !== '';
    }

    function validateCourseTitle(title) {
        // Title should contain only letters and spaces
        // Should be at least 5 characters long and not more than 100
        const letters = /^[A-Za-z\s]+$/;
        if (!letters.test(title)) {
            return false;
        }
        return title.trim().length >= 5 && title.trim().length <= 100;
    }

    function validateCourseFees(fees, status) {
        // If status is free, any value is acceptable
        if (status === 'free') {
            return true;
        }
        // If status is paid, must be greater than 0 and have valid currency format
        // Format check: Allow formats like $199.99, 199.99, etc.
        if (!/^\$?\d+(\.\d{1,2})?$/.test(fees)) {
            return false;
        }
        
        // Value check: For paid courses, value must be greater than 0
        const numericValue = parseFloat(fees.replace('$', ''));
        return !isNaN(numericValue) && numericValue > 0;
    }

    function validateUrl(url) {
        // Basic URL validation
        try {
            new URL(url);
            return true;
        } catch (e) {
            return false;
        }
    }

    function showError(inputElement, message) {
        // Remove any existing error messages
        const existingError = inputElement.parentElement.querySelector('.error-message');
        if (existingError) {
            existingError.remove();
        }

        // Create and append error message
        const errorDiv = document.createElement('div');
        errorDiv.className = 'error-message';
        errorDiv.textContent = message;
        errorDiv.style.color = 'red';
        errorDiv.style.fontSize = '0.8rem';
        errorDiv.style.marginTop = '5px';
        
        // Insert error message after the input field
        inputElement.parentElement.appendChild(errorDiv);
        
        // Highlight the input field
        inputElement.style.borderColor = 'red';
    }

    function clearError(inputElement) {
        // Remove error message if exists
        const existingError = inputElement.parentElement.querySelector('.error-message');
        if (existingError) {
            existingError.remove();
        }
        
        // Reset input field style
        inputElement.style.borderColor = '';
    }

    function clearAllErrors(form) {
        // Remove all error messages in the form
        form.querySelectorAll('.error-message').forEach(el => el.remove());
        
        // Reset all input field styles
        form.querySelectorAll('input, select').forEach(input => {
            input.style.borderColor = '';
        });
    }

    // Title field validation - allow only letters
    function validateTitleField(field) {
        const letters = /^[A-Za-z\s]+$/;
        const isValid = letters.test(field.value);
        
        if (!isValid && field.value) {
            field.classList.add('error-field');
            // Check if error message already exists
            if (!field.parentElement.querySelector('.error-message')) {
                const errorMsg = document.createElement('div');
                errorMsg.className = 'error-message';
                errorMsg.textContent = 'Only letters and spaces are allowed';
                field.parentElement.appendChild(errorMsg);
            }
            return false;
        } else {
            field.classList.remove('error-field');
            const errorMsg = field.parentElement.querySelector('.error-message');
            if (errorMsg) errorMsg.remove();
            return true;
        }
    }

    // Validate fees field for paid courses
    function validateFeesField(field, status) {
        // If status is free, any value is acceptable
        if (status !== 'paid') {
            clearError(field);
            return true;
        }
        
        // For paid courses, value must be > 0
        const value = field.value.replace('$', '').trim();
        const numericValue = parseFloat(value);
        const isValid = !isNaN(numericValue) && numericValue > 0;
        
        if (!isValid) {
            field.classList.add('error-field');
            // Check if error message already exists
            if (!field.parentElement.querySelector('.error-message')) {
                const errorMsg = document.createElement('div');
                errorMsg.className = 'error-message';
                errorMsg.textContent = 'Paid courses must have a price greater than 0';
                field.parentElement.appendChild(errorMsg);
            }
            return false;
        } else {
            field.classList.remove('error-field');
            const errorMsg = field.parentElement.querySelector('.error-message');
            if (errorMsg) errorMsg.remove();
            return true;
        }
    }

    // Validate form and control submit button state
    function validateFormSubmitButton(formType) {
        const isAddForm = formType === 'add' || !formType;
        
        // Get relevant form elements
        const form = isAddForm ? document.getElementById('addCourseForm') : document.getElementById('editCourseForm');
        const titleField = isAddForm ? document.getElementById('courseTitle') : document.getElementById('editCourseTitle');
        const feesField = isAddForm ? document.getElementById('courseFees') : document.getElementById('editCourseFees');
        const statusField = isAddForm ? document.getElementById('courseStatus') : document.getElementById('editCourseStatus');
        const submitBtn = form.querySelector('.submit-btn');
        
        // Validate fields
        const isTitleValid = validateTitleField(titleField);
        
        // For paid courses, fees must be valid
        let isFeesValid = true;
        if (statusField.value === 'paid') {
            isFeesValid = validateFeesField(feesField, statusField.value);
        }
        
        // Check if required fields are filled
        const requiredFields = form.querySelectorAll('[required]');
        let allRequiredFilled = true;
        requiredFields.forEach(field => {
            if (!field.value) {
                allRequiredFilled = false;
            }
        });
        
        // Enable/disable submit button
        submitBtn.disabled = !(isTitleValid && isFeesValid && allRequiredFilled);
    }

    // Validate add course form
    addCourseForm.addEventListener('submit', function(e) {
        e.preventDefault();
        clearAllErrors(this);
        
        // Get form values
        const courseId = document.getElementById('courseId').value;
        const courseIcon = document.getElementById('courseIcon').value;
        const courseTitle = document.getElementById('courseTitle').value;
        const courseFees = document.getElementById('courseFees').value;
        const courseLink = document.getElementById('courseLink').value;
        const courseCertification = document.getElementById('courseCertification').value;
        const courseStatus = document.getElementById('courseStatus').value;
        
        let isValid = true;
        
        // Validate Course ID
        if (!validateCourseId(courseId)) {
            showError(document.getElementById('courseId'), 'Course ID must follow format CRS-XXX (e.g., CRS-001)');
            isValid = false;
        }
        
        // Validate Course Icon
        if (!validateCourseIcon(courseIcon)) {
            showError(document.getElementById('courseIcon'), 'Course icon cannot be empty');
            isValid = false;
        }
        
        // Validate Course Title
        if (!validateCourseTitle(courseTitle)) {
            showError(document.getElementById('courseTitle'), 'Title must contain only letters and spaces and be between 5 and 100 characters');
            isValid = false;
        }
        
        // Validate Course Fees based on status
        if (!validateCourseFees(courseFees, courseStatus)) {
            showError(document.getElementById('courseFees'), 'Please enter a valid price format (e.g., $199.99) and ensure it is greater than 0 for paid courses');
            isValid = false;
        }
        
        // Validate Course Link
        if (courseLink && !validateUrl(courseLink)) {
            showError(document.getElementById('courseLink'), 'Please enter a valid URL');
            isValid = false;
        }
        
        // Validate Certification Link
        if (courseCertification && !validateUrl(courseCertification)) {
            showError(document.getElementById('courseCertification'), 'Please enter a valid URL');
            isValid = false;
        }
        
        // Validate Status
        if (!courseStatus) {
            showError(document.getElementById('courseStatus'), 'Please select a status');
            isValid = false;
        }
        
        // If form is valid, proceed with submission
        if (isValid) {
            // Create new table row
            const tableBody = document.querySelector('.users-table tbody');
            const newRow = document.createElement('tr');
            
            // Add class for the status badge
            const statusClass = courseStatus === 'free' ? 'free-course' : 'paid-course';
            const feesDisplay = courseStatus === 'free' ? 'Free' : courseFees;
            
            newRow.innerHTML = `
                <td>${courseId}</td>
                <td><div class="course-icon">${courseIcon}</div></td>
                <td>${courseTitle}</td>
                <td>Mixed</td>
                <td>${feesDisplay}</td>
                <td><a href="${courseLink}" target="_blank">View Course</a></td>
                <td><a href="${courseCertification}" target="_blank">Get Certified</a></td>
                <td><span class="status-badge ${statusClass}">${courseStatus.charAt(0).toUpperCase() + courseStatus.slice(1)}</span></td>
                <td><button class="action-btn">•••</button></td>
            `;
            
            // Add the new row to the table
            tableBody.appendChild(newRow);
            
            // Add event listener to the new action button
            const newActionBtn = newRow.querySelector('.action-btn');
            attachActionButtonEvents(newActionBtn);
            
            // Reset form and close modal
            addCourseForm.reset();
            modal.style.display = 'none';
            document.body.style.overflow = ""; // Re-enable scrolling
            
            console.log('New course added:', courseTitle);
        }
    });

    // Override the form submit handler to validate before submission
    document.getElementById('addCourseForm').addEventListener('submit', function(e) {
        // Prevent default only if validation fails
        const titleField = document.getElementById('courseTitle');
        const feesField = document.getElementById('courseFees');
        const statusField = document.getElementById('courseStatus');
        
        const isTitleValid = validateTitleField(titleField);
        let isFeesValid = true;
        if (statusField.value === 'paid') {
            isFeesValid = validateFeesField(feesField, statusField.value);
        }
        
        if (!isTitleValid || !isFeesValid) {
            e.preventDefault();
            return false;
        }
        
        // If we get here, validation passed
        // The normal form submission process will continue
    });

    document.getElementById('editCourseForm').addEventListener('submit', function(e) {
        // Prevent default only if validation fails
        const titleField = document.getElementById('editCourseTitle');
        const feesField = document.getElementById('editCourseFees');
        const statusField = document.getElementById('editCourseStatus');
        
        const isTitleValid = validateTitleField(titleField);
        let isFeesValid = true;
        if (statusField.value === 'paid') {
            isFeesValid = validateFeesField(feesField, statusField.value);
        }
        
        if (!isTitleValid || !isFeesValid) {
            e.preventDefault();
            return false;
        }
        
        // If we get here, validation passed
        // The normal form submission process will continue
    });
    
    // Function to handle edit button click
    function handleEditClick(courseRow) {
        // Get course data from the table row
        const courseId = courseRow.querySelector('td:nth-child(1)').textContent;
        const courseIcon = courseRow.querySelector('.course-icon').textContent;
        const courseTitle = courseRow.querySelector('td:nth-child(3)').textContent;
        
        // Get fees - handle Free or $ amount
        const courseFees = courseRow.querySelector('td:nth-child(5)').textContent;
        
        // Get links from href attributes
        const courseLink = courseRow.querySelector('td:nth-child(6) a').getAttribute('href');
        const courseCertification = courseRow.querySelector('td:nth-child(7) a').getAttribute('href');
        
        // Get status - check if it has free-course or paid-course class
        const statusBadge = courseRow.querySelector('.status-badge');
        const courseStatus = statusBadge.classList.contains('free-course') ? 'free' : 'paid';
        
        // Store row index for later update
        const rowIndex = Array.from(courseRow.parentElement.children).indexOf(courseRow);
        
        // Clear any previous errors
        clearAllErrors(document.getElementById('editCourseForm'));
        
        // Populate the edit form
        document.getElementById('editRowIndex').value = rowIndex;
        document.getElementById('editCourseId').value = courseId;
        document.getElementById('editCourseIcon').value = courseIcon;
        document.getElementById('editCourseTitle').value = courseTitle;
        document.getElementById('editCourseFees').value = courseFees;
        document.getElementById('editCourseLink').value = courseLink;
        document.getElementById('editCourseCertification').value = courseCertification;
        document.getElementById('editCourseStatus').value = courseStatus;
        
        // Display the edit modal
        editModal.style.display = "block";
        editModal.style.cssText = "display: block !important; opacity: 1; visibility: visible;";
        document.body.style.overflow = "hidden"; // Prevent scrolling
    }
    
    // Edit course form submission
    editCourseForm.addEventListener('submit', function(e) {
        e.preventDefault();
        clearAllErrors(this);
        
        updateCourse();
    });
    
    // Action button functionality
    document.querySelectorAll('.action-btn').forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.stopPropagation();
            
            // Get course info
            const courseRow = this.closest('tr');
            const courseTitle = courseRow.querySelector('td:nth-child(3)').textContent;
            console.log('Action button clicked for course:', courseTitle);
            
            // Close any open dropdown
            closeAllActionDropdowns();
            
            // Create and position the dropdown
            const dropdown = document.getElementById('actionDropdownTemplate').cloneNode(true);
            dropdown.id = '';
            dropdown.classList.add('active-dropdown');
            dropdown.style.display = 'block';
            
            // Position the dropdown next to the action button
            const buttonRect = this.getBoundingClientRect();
            dropdown.style.position = 'absolute';
            dropdown.style.top = `${buttonRect.bottom + window.scrollY}px`;
            dropdown.style.right = `${document.body.offsetWidth - buttonRect.right}px`;
            
            // Add to the document
            document.body.appendChild(dropdown);
            
            // Add event listeners for menu items
            dropdown.querySelector('.edit-action').addEventListener('click', function() {
                closeAllActionDropdowns();
                handleEditClick(courseRow);
            });
            
            dropdown.querySelector('.view-action').addEventListener('click', function() {
                alert(`View details for: ${courseTitle}`);
                closeAllActionDropdowns();
            });
            
            dropdown.querySelector('.duplicate-action').addEventListener('click', function() {
                alert(`Duplicate functionality for: ${courseTitle}`);
                closeAllActionDropdowns();
            });
            
            dropdown.querySelector('.delete-action').addEventListener('click', function() {
                if (confirm(`Are you sure you want to delete "${courseTitle}"?`)) {
                    // Remove row from table
                    courseRow.remove();
                    showToast('success', 'Course Deleted', `Deleted course: ${courseTitle}`);
                }
                closeAllActionDropdowns();
            });
        });
    });
    
    // Close dropdowns when clicking elsewhere
    document.addEventListener('click', function(e) {
        if (!e.target.closest('.action-btn')) {
            closeAllActionDropdowns();
        }
    });
    
    function closeAllActionDropdowns() {
        document.querySelectorAll('.active-dropdown').forEach(dropdown => {
            dropdown.remove();
        });
    }
    
    // Also apply event listeners to new action buttons created dynamically
    function attachActionButtonEvents(button) {
        button.addEventListener('click', function(e) {
            e.stopPropagation();
            
            // Get course info
            const courseRow = this.closest('tr');
            const courseTitle = courseRow.querySelector('td:nth-child(3)').textContent;
            console.log('Action button clicked for course:', courseTitle);
            
            // Close any open dropdown
            closeAllActionDropdowns();
            
            // Create and position the dropdown
            const dropdown = document.getElementById('actionDropdownTemplate').cloneNode(true);
            dropdown.id = '';
            dropdown.classList.add('active-dropdown');
            dropdown.style.display = 'block';
            
            // Position the dropdown next to the action button
            const buttonRect = this.getBoundingClientRect();
            dropdown.style.position = 'absolute';
            dropdown.style.top = `${buttonRect.bottom + window.scrollY}px`;
            dropdown.style.right = `${document.body.offsetWidth - buttonRect.right}px`;
            
            // Add to the document
            document.body.appendChild(dropdown);
            
            // Add event listeners for menu items
            dropdown.querySelector('.edit-action').addEventListener('click', function() {
                closeAllActionDropdowns();
                handleEditClick(courseRow);
            });
            
            dropdown.querySelector('.view-action').addEventListener('click', function() {
                showToast('info', 'View Course', `Viewing details for: ${courseTitle}`);
                closeAllActionDropdowns();
            });
            
            dropdown.querySelector('.duplicate-action').addEventListener('click', function() {
                showToast('info', 'Duplicate Course', `Duplicate functionality for: ${courseTitle}`);
                closeAllActionDropdowns();
            });
            
            dropdown.querySelector('.delete-action').addEventListener('click', function() {
                if (confirm(`Are you sure you want to delete "${courseTitle}"?`)) {
                    // Remove row from table
                    courseRow.remove();
                    showToast('success', 'Course Deleted', `Deleted course: ${courseTitle}`);
                }
                closeAllActionDropdowns();
            });
        });
    }
    
    // Input validation on change/input events
    document.querySelectorAll('#addCourseForm input, #addCourseForm select').forEach(input => {
        input.addEventListener('input', function() {
            clearError(this);
        });
        
        input.addEventListener('change', function() {
            clearError(this);
        });
    });

    document.querySelectorAll('#editCourseForm input, #editCourseForm select').forEach(input => {
        input.addEventListener('input', function() {
            clearError(this);
        });
        
        input.addEventListener('change', function() {
            clearError(this);
        });
    });

    // Load courses from database
    loadCourses();
    
    // Add course button opens modal
    document.getElementById('addCourseBtn').addEventListener('click', function() {
        document.getElementById('addCourseModal').style.display = 'block';
    });
    
    // Close add course modal
    document.querySelector('#addCourseModal .close-modal').addEventListener('click', function() {
        document.getElementById('addCourseModal').style.display = 'none';
    });
    
    // Cancel button closes add course modal
    document.getElementById('cancelAddCourse').addEventListener('click', function() {
        document.getElementById('addCourseModal').style.display = 'none';
    });
    
    // Close edit course modal
    document.getElementById('closeEditModal').addEventListener('click', function() {
        document.getElementById('editCourseModal').style.display = 'none';
    });
    
    // Cancel button closes edit course modal
    document.getElementById('cancelEditCourse').addEventListener('click', function() {
        document.getElementById('editCourseModal').style.display = 'none';
    });
    
    // Submit new course form
    document.getElementById('addCourseForm').addEventListener('submit', function(e) {
        e.preventDefault();
        addCourse();
    });
    
    // Submit edit course form
    document.getElementById('editCourseForm').addEventListener('submit', function(e) {
        e.preventDefault();
        updateCourse();
    });
    
    // Setup theme toggling
    document.getElementById('theme-toggle').addEventListener('change', function() {
        document.body.classList.toggle('dark-theme');
    });

    // Set up initial action buttons
    setupActionDropdowns();
});

// Load all courses from the database
function loadCourses() {
    console.log('Attempting to load courses from database...');
    fetch('../controllers/course_crud.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: 'action=read'
    })
    .then(response => response.json())
    .then(data => {
        if (data.status === 'success') {
            displayCourses(data.data);
            showToast('success', 'Success!', `Successfully loaded ${data.data.length} courses`);
        } else {
            console.log('No courses found or error occurred:', data.message);
            // Show empty table
            displayCourses([]);
            
            // Check if database setup might be needed
            showToast('warning', 'No Courses Found', 'No courses were found or there was a database error.');
            
            // After a short delay, show a follow-up notification
            setTimeout(() => {
                if (confirm('Would you like to set up the database now?')) {
                    window.location.href = '../model/setup_database.php';
                }
            }, 1500);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        // Show empty table
        displayCourses([]);
        
        // Show error notification
        showToast('error', 'Connection Failed', 'Failed to connect to the database. Check your network connection.');
        
        // After a short delay, show a follow-up notification
        setTimeout(() => {
            if (confirm('Would you like to run the database setup wizard?')) {
                window.location.href = '../model/setup_database.php';
            }
        }, 1500);
    });
}

// Display courses in the table
function displayCourses(courses) {
    const tableBody = document.querySelector('.users-table tbody');
    tableBody.innerHTML = '';
    
    if (!courses || courses.length === 0) {
        console.log('No courses to display');
        return;
    }
    
    console.log('Displaying courses:', courses.length);
    
    courses.forEach(course => {
        const row = document.createElement('tr');
        
        // Fix for handling empty or null values
        const courseId = course.course_id || '';
        const title = course.title || '';
        const courseLink = course.course_link || '#';
        const certificationLink = course.certification_link || '#';
        const status = course.status || 'free';
        
        // Determine the fee display format correctly
        let feeDisplay;
        if (status === 'free' || course.fees == 0 || course.fees === '0') {
            feeDisplay = 'Free';
        } else {
            feeDisplay = '$' + parseFloat(course.fees).toFixed(2);
        }
        
        // Determine which icon to show
        let icon = '💻'; // Default icon
        
        row.innerHTML = `
            <td>${courseId}</td>
            <td><div class="course-icon">${icon}</div></td>
            <td>${title}</td>
            <td>Mixed</td>
            <td>${feeDisplay}</td>
            <td><a href="${courseLink}" target="_blank">View Course</a></td>
            <td><a href="${certificationLink}" target="_blank">Get Certified</a></td>
            <td><span class="status-badge ${status === 'free' ? 'free-course' : 'paid-course'}">
                ${status.charAt(0).toUpperCase() + status.slice(1)}
            </span></td>
            <td><button class="action-btn" data-course-id="${courseId}">•••</button></td>
        `;
        
        tableBody.appendChild(row);
    });
    
    // Setup action dropdowns for new rows after they're added to the DOM
    setupActionDropdowns();
}

// Add a new course
function addCourse() {
    // Get form data
    const courseId = document.getElementById('courseId').value;
    const courseIcon = document.getElementById('courseIcon').value || '💻';
    const courseTitle = document.getElementById('courseTitle').value;
    const courseFees = document.getElementById('courseFees').value;
    const courseLink = document.getElementById('courseLink').value || '#';
    const courseCertification = document.getElementById('courseCertification').value || '#';
    const courseStatus = document.getElementById('courseStatus').value;
    
    console.log('Adding new course:', courseTitle, 'Status:', courseStatus);
    
    // Validate required fields
    if (!courseTitle) {
        showToast('error', 'Validation Error', 'Course title cannot be empty');
        return;
    }
    
    try {
        // Create form data for submission
        const formData = new FormData();
        formData.append('action', 'create');
        formData.append('courseId', courseId); // Even if empty, server will generate one
        formData.append('courseTitle', courseTitle);
        
        // Handle course fees correctly based on status
        if (courseStatus === 'free') {
            formData.append('courseFees', '0');
        } else {
            formData.append('courseFees', courseFees.replace('$', ''));
        }
        
        formData.append('courseLink', courseLink);
        formData.append('courseCertification', courseCertification);
        formData.append('courseStatus', courseStatus);
        
        // Submit form data
        fetch('../controllers/course_crud.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                showToast('success', 'Course Added', 'Course added successfully!');
                document.getElementById('addCourseModal').style.display = 'none';
                document.getElementById('addCourseForm').reset();
                
                // If server returns the created course data, use it directly instead of reloading
                if (data.data) {
                    const newCourse = data.data;
                    // Add new course to the table
                    const tableBody = document.querySelector('.users-table tbody');
                    const newRow = document.createElement('tr');
                    
                    // Determine the fee display format
                    let feeDisplay = newCourse.status === 'free' ? 'Free' : ('$' + parseFloat(newCourse.fees).toFixed(2));
                    
                    newRow.innerHTML = `
                        <td>${newCourse.course_id}</td>
                        <td><div class="course-icon">${courseIcon}</div></td>
                        <td>${newCourse.title}</td>
                        <td>Mixed</td>
                        <td>${feeDisplay}</td>
                        <td><a href="${newCourse.course_link}" target="_blank">View Course</a></td>
                        <td><a href="${newCourse.certification_link}" target="_blank">Get Certified</a></td>
                        <td><span class="status-badge ${newCourse.status === 'free' ? 'free-course' : 'paid-course'}">
                            ${newCourse.status.charAt(0).toUpperCase() + newCourse.status.slice(1)}
                        </span></td>
                        <td><button class="action-btn" data-course-id="${newCourse.course_id}">•••</button></td>
                    `;
                    
                    tableBody.appendChild(newRow);
                    setupActionDropdowns();
                    
                    // Highlight the new row
                    highlightUpdatedCourse(newCourse.course_id);
                } else {
                    // If no data returned, reload all courses
                    loadCourses();
                }
            } else {
                showToast('error', 'Add Failed', `Failed to add course: ${data.message}`);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showToast('info', 'Adding Locally', 'Failed to communicate with the server. Adding course in offline mode.', 5000);
            
            // Create and add a temporary course row
            const tableBody = document.querySelector('.users-table tbody');
            const newRow = document.createElement('tr');
            
            // Generate a temporary ID for offline mode
            const tempId = 'TMP-' + Math.floor(Math.random() * 1000);
            
            // Determine the fee display format
            let feeDisplay = courseStatus === 'free' ? 'Free' : (courseFees || '$0.00');
            if (!feeDisplay.startsWith('$') && courseStatus === 'paid') {
                feeDisplay = '$' + feeDisplay;
            }
            
            newRow.innerHTML = `
                <td>${courseId || tempId}</td>
                <td><div class="course-icon">${courseIcon || '💻'}</div></td>
                <td>${courseTitle}</td>
                <td>Mixed</td>
                <td>${feeDisplay}</td>
                <td><a href="${courseLink}" target="_blank">View Course</a></td>
                <td><a href="${courseCertification}" target="_blank">Get Certified</a></td>
                <td><span class="status-badge ${courseStatus === 'free' ? 'free-course' : 'paid-course'}">
                    ${courseStatus.charAt(0).toUpperCase() + courseStatus.slice(1)}
                </span></td>
                <td><button class="action-btn" data-course-id="${courseId || tempId}">•••</button></td>
            `;
            
            tableBody.appendChild(newRow);
            
            // Close modal and reset form
            document.getElementById('addCourseModal').style.display = 'none';
            document.getElementById('addCourseForm').reset();
            
            // Setup action dropdown for the new row
            setupActionDropdowns();
        });
    } catch (error) {
        console.error('Critical error in addCourse:', error);
        showToast('error', 'Error', 'An unexpected error occurred. The course will be added in offline mode.');
        
        // Create and add a temporary course row (same as in the catch block above)
        const tableBody = document.querySelector('.users-table tbody');
        const newRow = document.createElement('tr');
        
        // Generate a temporary ID for offline mode
        const tempId = 'TMP-' + Math.floor(Math.random() * 1000);
        
        let feeDisplay = courseStatus === 'free' ? 'Free' : (courseFees || '$0.00');
        
        newRow.innerHTML = `
            <td>${courseId || tempId}</td>
            <td><div class="course-icon">${courseIcon || '💻'}</div></td>
            <td>${courseTitle}</td>
            <td>Mixed</td>
            <td>${feeDisplay}</td>
            <td><a href="${courseLink}" target="_blank">View Course</a></td>
            <td><a href="${courseCertification}" target="_blank">Get Certified</a></td>
            <td><span class="status-badge ${courseStatus === 'free' ? 'free-course' : 'paid-course'}">
                ${courseStatus.charAt(0).toUpperCase() + courseStatus.slice(1)}
            </span></td>
            <td><button class="action-btn" data-course-id="${courseId || tempId}">•••</button></td>
        `;
        
        tableBody.appendChild(newRow);
        
        // Close modal and reset form
        document.getElementById('addCourseModal').style.display = 'none';
        document.getElementById('addCourseForm').reset();
        
        // Setup action dropdown for the new row
        setupActionDropdowns();
    }
}

// Update an existing course
function updateCourse() {
    // Get form data
    const courseId = document.getElementById('editCourseId').value;
    const courseTitle = document.getElementById('editCourseTitle').value;
    const courseFees = document.getElementById('editCourseFees').value;
    const courseLink = document.getElementById('editCourseLink').value;
    const courseCertification = document.getElementById('editCourseCertification').value;
    const courseStatus = document.getElementById('editCourseStatus').value;
    
    // Create form data for submission
    const formData = new FormData();
    formData.append('action', 'update');
    formData.append('courseId', courseId);
    formData.append('courseTitle', courseTitle);
    formData.append('courseFees', courseFees);
    formData.append('courseLink', courseLink);
    formData.append('courseCertification', courseCertification);
    formData.append('courseStatus', courseStatus);
    
    console.log('SENDING UPDATE REQUEST with data:', {
        courseId,
        courseTitle,
        courseFees,
        courseStatus
    });
    
    // Submit form data with improved error handling and UI feedback
    fetch('../controllers/course_crud.php', {
        method: 'POST',
        body: formData
    })
    .then(response => {
        console.log('Raw response status:', response.status);
        return response.json();
    })
    .then(data => {
        console.log('Update response:', data);
        
        if (data.status === 'success') {
            // Close modal first
            document.getElementById('editCourseModal').style.display = 'none';
            
            // Force reload of all courses from server to reflect changes
            fetch('../controllers/course_crud.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'action=read'
            })
            .then(response => response.json())
            .then(freshData => {
                if (freshData.status === 'success') {
                    displayCourses(freshData.data);
                    // Show success message after UI is updated
                    showToast('success', 'Course Updated', 'Course updated successfully!');
                    
                    // Highlight the updated row to make the change obvious
                    highlightUpdatedCourse(courseId);
                } else {
                    showToast('warning', 'Update Partial', 'Course was updated but display refresh failed');
                    location.reload(); // Fallback to page reload
                }
            })
            .catch(() => {
                showToast('warning', 'Update Partial', 'Course updated. Reloading page to show changes...');
                setTimeout(() => {
                    location.reload(); // Fallback to page reload if fetch fails
                }, 1000);
            });
        } else {
            // More detailed error handling
            if (data.message && data.message.includes('Data too long for column')) {
                showToast('error', 'Database Error', 
                    'The title is too long for the database. Please run the database update script to increase the column size.',
                    8000
                );
                
                // Provide direct link to fix the database
                if (confirm('Would you like to run the database update script now to fix this issue?')) {
                    window.location.href = '../config/alter_database.php';
                }
            } else {
                showToast('error', 'Update Failed', `Failed to update course: ${data.message}`);
            }
        }
    })
    .catch(error => {
        console.error('Error during update:', error);
        showToast('error', 'Update Error', 'Failed to update course. Please check the console for details.');
    });
}

// Function to highlight the updated course in the table
function highlightUpdatedCourse(courseId) {
    // Find the updated row
    const rows = document.querySelectorAll('.users-table tbody tr');
    
    rows.forEach(row => {
        const idCell = row.querySelector('td:first-child');
        if (idCell && idCell.textContent === courseId) {
            // Add highlight effect
            row.style.transition = 'background-color 1s ease';
            row.style.backgroundColor = '#fff8dd'; // Light yellow highlight
            
            // Scroll to the row to make it visible
            row.scrollIntoView({
                behavior: 'smooth',
                block: 'center'
            });
            
            // Remove highlight after 3 seconds
            setTimeout(() => {
                row.style.backgroundColor = '';
            }, 3000);
        }
    });
}

// Delete a course
function deleteCourse(courseId) {
    if (confirm('Are you sure you want to delete this course?')) {
        const formData = new FormData();
        formData.append('action', 'delete');
        formData.append('courseId', courseId);
        
        try {
            fetch('../controllers/course_crud.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    showToast('success', 'Course Deleted', 'Course deleted successfully!');
                    loadCourses(); // Reload courses
                } else {
                    showToast('error', 'Delete Failed', `Failed to delete course: ${data.message}`);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showToast('warning', 'Offline Mode', 'Failed to communicate with the server. Course will be removed from the display only.', 5000);
                
                // Find and remove the row from the table (offline mode)
                const rows = document.querySelectorAll('.users-table tbody tr');
                rows.forEach(row => {
                    const idCell = row.querySelector('td:first-child');
                    if (idCell && idCell.textContent === courseId) {
                        row.remove();
                        console.log(`Removed course ${courseId} from display (offline mode)`);
                    }
                });
            });
        } catch (error) {
            console.error('Critical error in deleteCourse:', error);
            showToast('error', 'Error', 'An unexpected error occurred. The course will be removed from the display only.');
            
            // Find and remove the row from the table (offline mode)
            const rows = document.querySelectorAll('.users-table tbody tr');
            rows.forEach(row => {
                const idCell = row.querySelector('td:first-child');
                if (idCell && idCell.textContent === courseId) {
                    row.remove();
                    console.log(`Removed course ${courseId} from display (offline mode)`);
                }
            });
        }
    }
}

// Populate edit form with course data
function populateEditForm(courseId) {
    console.log('Populating edit form for course ID:', courseId);
    
    // Find the course in the table
    const rows = document.querySelectorAll('.users-table tbody tr');
    let found = false;
    
    for (let i = 0; i < rows.length; i++) {
        const row = rows[i];
        const cells = row.querySelectorAll('td');
        const rowId = cells[0].textContent;
        
        console.log(`Checking row ${i}: ID=${rowId}`);
        
        if (rowId === courseId) {
            found = true;
            console.log('Found course in table row', i);
            
            try {
                // Get the row index for later use if needed
                const rowIndex = i;
                document.getElementById('editRowIndex').value = rowIndex;
                
                // Get course data from the row
                document.getElementById('editCourseId').value = cells[0].textContent;
                document.getElementById('editCourseIcon').value = cells[1].querySelector('.course-icon').textContent;
                document.getElementById('editCourseTitle').value = cells[2].textContent;
                document.getElementById('editCourseFees').value = cells[4].textContent;
                document.getElementById('editCourseLink').value = cells[5].querySelector('a').getAttribute('href');
                document.getElementById('editCourseCertification').value = cells[6].querySelector('a').getAttribute('href');
                
                const statusBadge = cells[7].querySelector('.status-badge');
                const status = statusBadge.classList.contains('free-course') ? 'free' : 'paid';
                document.getElementById('editCourseStatus').value = status;
                
                // Show the modal with explicit styling
                const editModal = document.getElementById('editCourseModal');
                editModal.style.display = "block";
                editModal.style.cssText = "display: block !important; opacity: 1; visibility: visible; z-index: 9999;";
                document.body.style.overflow = "hidden"; // Prevent scrolling
                
                console.log('Edit modal should now be visible');
            } catch (error) {
                console.error('Error while populating form:', error);
            }
            
            break;
        }
    }
    
    if (!found) {
        console.error('Course not found in table:', courseId);
        showToast('error', 'Error', 'Could not find course data to edit.');
    }
}

// Setup action dropdowns for table rows
function setupActionDropdowns() {
    console.log('Setting up action dropdowns');
    
    // Remove any existing event listeners by cloning and replacing elements
    const actionButtons = document.querySelectorAll('.action-btn');
    actionButtons.forEach(button => {
        const newButton = button.cloneNode(true);
        button.parentNode.replaceChild(newButton, button);
    });
    
    // Add fresh event listeners
    const newActionButtons = document.querySelectorAll('.action-btn');
    console.log('Found', newActionButtons.length, 'action buttons');
    
    newActionButtons.forEach(button => {
        button.addEventListener('click', function(e) {
            e.stopPropagation();
            e.preventDefault();
            
            console.log('Action button clicked');
            
            // Hide any open dropdown
            const openDropdown = document.querySelector('.action-dropdown-visible');
            if (openDropdown) {
                openDropdown.remove();
            }
            
            // Get course ID from row
            const row = this.closest('tr');
            const courseId = row.querySelector('td:first-child').textContent;
            console.log('Course ID:', courseId);
            
            // Clone dropdown template
            const dropdownTemplate = document.getElementById('actionDropdownTemplate');
            if (!dropdownTemplate) {
                console.error('Action dropdown template not found');
                return;
            }
            
            const dropdown = dropdownTemplate.cloneNode(true);
            dropdown.id = '';
            dropdown.style.display = 'block';
            dropdown.style.position = 'absolute';
            dropdown.classList.add('action-dropdown-visible');
            
            // Position dropdown near the button
            const rect = this.getBoundingClientRect();
            dropdown.style.top = (rect.bottom + window.scrollY) + 'px';
            dropdown.style.left = (rect.left - 150 + window.scrollX) + 'px';
            
            // Add click event listeners
            const editBtn = dropdown.querySelector('.edit-action');
            if (editBtn) {
                editBtn.addEventListener('click', function() {
                    console.log('Edit button clicked for course:', courseId);
                    dropdown.remove();
                    populateEditForm(courseId);
                });
            }
            
            dropdown.querySelector('.delete-action').addEventListener('click', function() {
                dropdown.remove();
                // Call the course deletion function directly with error handling
                deleteCourse(courseId);
            });
            
            dropdown.querySelector('.view-action').addEventListener('click', function() {
                dropdown.remove();
                showToast('info', 'View Course', `Viewing details for ${courseId}`);
            });
            
            dropdown.querySelector('.duplicate-action').addEventListener('click', function() {
                dropdown.remove();
                showToast('info', 'Duplicate Course', 'Duplicate function not implemented yet');
            });
            
            document.body.appendChild(dropdown);
            
            // Close dropdown when clicking outside
            const closeDropdownHandler = function(event) {
                if (!event.target.closest('.action-dropdown-visible')) {
                    dropdown.remove();
                    document.removeEventListener('click', closeDropdownHandler);
                }
            };
            
            // Add the event listener after a small delay to prevent immediate closing
            setTimeout(() => {
                document.addEventListener('click', closeDropdownHandler);
            }, 100);
        });
    });
}

// Interactive Toast Notification System
function showToast(type, title, message, duration = 4000, playSound = true) {
    const container = document.getElementById('toast-container');
    if (!container) {
        console.error('Toast container not found');
        return;
    }
    
    // Create toast element
    const toast = document.createElement('div');
    toast.className = `toast toast-${type}`;
    
    // Determine icon based on type
    let icon = '';
    switch(type) {
        case 'success':
            icon = '✓';
            break;
        case 'error':
            icon = '✕';
            break;
        case 'info':
            icon = 'ℹ';
            break;
        case 'warning':
            icon = '⚠';
            break;
    }
    
    // Set toast content with title and message
    toast.innerHTML = `
        <div class="toast-icon">${icon}</div>
        <div class="toast-content">
            <div class="toast-title">${title}</div>
            <div class="toast-message">${message}</div>
        </div>
        <div class="toast-close">×</div>
        <div class="toast-progress"></div>
    `;
    
    // Add to container
    container.appendChild(toast);
    
    // Play sound if enabled
    if (playSound) {
        playToastSound(type);
    }
    
    // Show toast after a small delay (for animation purposes)
    setTimeout(() => {
        toast.classList.add('show');
        
        // Start progress bar animation
        const progressBar = toast.querySelector('.toast-progress');
        if (progressBar) {
            progressBar.style.transform = 'scaleX(1)';
            progressBar.style.transition = `transform ${duration}ms linear`;
            setTimeout(() => {
                progressBar.style.transform = 'scaleX(0)';
            }, 10);
        }
    }, 10);
    
    // Handle close button
    const closeBtn = toast.querySelector('.toast-close');
    closeBtn.addEventListener('click', () => {
        closeToast(toast);
    });
    
    // Auto close after duration
    if (duration > 0) {
        setTimeout(() => {
            closeToast(toast);
        }, duration);
    }
    
    return toast;
}

// Close toast with animation
function closeToast(toast) {
    toast.classList.remove('show');
    toast.style.opacity = '0';
    toast.style.transform = 'translateX(110%)';
    
    // Remove element after animation completes
    setTimeout(() => {
        // Before removing, check if we need to slide up other toasts
        const container = document.getElementById('toast-container');
        const index = Array.from(container.children).indexOf(toast);
        
        // Calculate toast height + margin for slide effect
        const toastHeight = toast.offsetHeight + 12; // 12px is the gap between toasts
        
        // Remove the toast
        toast.remove();
        
        // Slide up other toasts below this one
        Array.from(container.children).slice(index).forEach(otherToast => {
            otherToast.style.transform = 'translateY(-' + toastHeight + 'px)';
            otherToast.classList.add('toast-slide-up');
            
            // Reset transform after animation
            setTimeout(() => {
                otherToast.style.transform = '';
                otherToast.classList.remove('toast-slide-up');
            }, 300);
        });
    }, 400);
}

// Sound effects for toast notifications
function playToastSound(type) {
    // Create audio element
    const audio = new Audio();
    
    // Set different sounds based on notification type
    switch(type) {
        case 'success':
            audio.src = 'data:audio/mp3;base64,SUQzBAAAAAABEVRYWFgAAAAtAAADY29tbWVudABCaWdTb3VuZEJhbmsuY29tIC8gTGFTb25vdGhlcXVlLm9yZwBURU5DAAAAHQAAA1N3aXRjaCBQbHVzIMKpIE5DSCBTb2Z0d2FyZQBUSVQyAAAABgAAAzIyK2EAVFNTRQAAAAwAAANMYXZmNTcuODMuMTAwAAAAAAAAAAAAAAD/80DEAAAAA0gAAAAATEFNRTMuMTAwVVVVVVVVVVVVVUxBTUUzLjEwMFVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVf/zQsRbAAADSAAAAABVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVf/zQsRbAAADSAAAAABVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVV';
            break;
        case 'error':
            audio.src = 'data:audio/mp3;base64,SUQzBAAAAAABEVRYWFgAAAAtAAADY29tbWVudABCaWdTb3VuZEJhbmsuY29tIC8gTGFTb25vdGhlcXVlLm9yZwBURU5DAAAAHQAAA1N3aXRjaCBQbHVzIMKpIE5DSCBTb2Z0d2FyZQBUSVQyAAAABgAAAzIyK2EAVFNTRQAAAAwAAANMYXZmNTcuODMuMTAwAAAAAAAAAAAAAAD/80DEAAAAA0gAAAAATEFNRTMuMTAwVVVVVVVVVVVVVUxBTUUzLjEwMFVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVf/zQsRbAAADSAAAAABVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVf/zQsRbAAADSAAAAABVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVV';
            break;
        default:
            audio.src = 'data:audio/mp3;base64,SUQzBAAAAAABEVRYWFgAAAAtAAADY29tbWVudABCaWdTb3VuZEJhbmsuY29tIC8gTGFTb25vdGhlcXVlLm9yZwBURU5DAAAAHQAAA1N3aXRjaCBQbHVzIMKpIE5DSCBTb2Z0d2FyZQBUSVQyAAAABgAAAzIyK2EAVFNTRQAAAAwAAANMYXZmNTcuODMuMTAwAAAAAAAAAAAAAAD/80DEAAAAA0gAAAAATEFNRTMuMTAwVVVVVVVVVVVVVUxBTUUzLjEwMFVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVf/zQsRbAAADSAAAAABVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVf/zQsRbAAADSAAAAABVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVV';
    }
    
    try {
        audio.volume = 0.3; // Set lower volume
        audio.play();
    } catch (e) {
        console.log('Could not play toast sound:', e);
    }
}
