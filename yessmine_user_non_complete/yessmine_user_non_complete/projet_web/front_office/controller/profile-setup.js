/**
 * Profile Setup JavaScript
 * 
 * This file handles the multi-step form for setting up engineer profiles,
 * including validation, dynamic form elements, and form submission.
 */

// Global variables
let currentStep = 1;
const totalSteps = 5;
let experienceCount = 1;
let educationCount = 1;
let skillCount = 1;
let languageCount = 1;
let projectCount = 1;
let honorCount = 1;

// DOM loaded event
document.addEventListener('DOMContentLoaded', function() {
    console.log('DOM loaded, initializing profile setup...');
    
    // Initialize event listeners
    initEventListeners();
    
    // Set up validation for date fields
    setupDateValidation();
    
    // Set up word count for about section
    setupWordCount();
    
    // Set up profile picture upload
    setupProfilePictureUpload();
    
    // Show first step
    goToStep(1);
    
    // Update progress
    updateProgress(1);
});

/**
 * Initialize all event listeners
 */
function initEventListeners() {
    // Step navigation buttons
    document.getElementById('step-1-next').addEventListener('click', () => validateAndProceed(1, 2));
    document.getElementById('step-2-prev').addEventListener('click', () => goToStep(1));
    document.getElementById('step-2-next').addEventListener('click', () => validateAndProceed(2, 3));
    document.getElementById('step-3-prev').addEventListener('click', () => goToStep(2));
    document.getElementById('step-3-next').addEventListener('click', () => validateAndProceed(3, 4));
    document.getElementById('step-4-prev').addEventListener('click', () => goToStep(3));
    document.getElementById('step-4-next').addEventListener('click', () => validateAndProceed(4, 5));
    document.getElementById('step-5-prev').addEventListener('click', () => goToStep(4));
    
    // Add buttons for dynamic elements
    document.getElementById('add-experience-btn').addEventListener('click', addExperience);
    document.getElementById('add-education-btn').addEventListener('click', addEducation);
    document.getElementById('add-skill-btn').addEventListener('click', addSkill);
    document.getElementById('add-language-btn').addEventListener('click', addLanguage);
    document.getElementById('add-project-btn').addEventListener('click', addProject);
    document.getElementById('add-honor-btn').addEventListener('click', addHonor);
    
    // Current job checkboxes
    setupCurrentJobCheckboxes();
    
    // Form submission
    document.getElementById('profile-setup-form').addEventListener('submit', validateAndSubmitForm);
    
    // Close modal buttons
    document.querySelectorAll('.close-modal').forEach(button => {
        button.addEventListener('click', closeModal);
    });
}

/**
 * Setup event listeners for current job checkboxes
 */
function setupCurrentJobCheckboxes() {
    // Initial setup for the first experience item
    const currentJobCheckbox = document.getElementById('current-job-0');
    const endDateField = document.getElementById('exp-end-date-0');
    
    currentJobCheckbox.addEventListener('change', function() {
        endDateField.disabled = this.checked;
        if (this.checked) {
            endDateField.value = '';
        }
    });
    
    // Initial setup for the first education item
    const currentEducationCheckbox = document.getElementById('current-education-0');
    const eduEndDateField = document.getElementById('edu-end-date-0');
    
    currentEducationCheckbox.addEventListener('change', function() {
        eduEndDateField.disabled = this.checked;
        if (this.checked) {
            eduEndDateField.value = '';
        }
    });
}

/**
 * Setup validation for date fields
 */
function setupDateValidation() {
    // Birthday validation (must be at least 18 years old)
    const birthdayField = document.getElementById('birthday');
    
    birthdayField.addEventListener('change', function() {
        validateAge(this);
    });
    
    // Set max date to today
    const today = new Date();
    const maxDate = today.toISOString().split('T')[0];
    birthdayField.setAttribute('max', maxDate);
    
    // Experience date validation
    setupExperienceDateValidation(0);
    
    // Education date validation
    setupEducationDateValidation(0);
}

/**
 * Validate age (must be at least 18 years old)
 * @param {HTMLInputElement} birthdayField - The birthday input field
 * @returns {boolean} - Whether the age is valid
 */
function validateAge(birthdayField) {
    const birthday = new Date(birthdayField.value);
    const today = new Date();
    
    // Calculate age
    let age = today.getFullYear() - birthday.getFullYear();
    const monthDiff = today.getMonth() - birthday.getMonth();
    
    if (monthDiff < 0 || (monthDiff === 0 && today.getDate() < birthday.getDate())) {
        age--;
    }
    
    // Check if age is at least 18
    if (age < 18) {
        showError(birthdayField, 'You must be at least 18 years old');
        return false;
    } else {
        clearError(birthdayField);
        return true;
    }
}

/**
 * Setup experience date validation
 * @param {number} index - The index of the experience item
 */
function setupExperienceDateValidation(index) {
    const startDateField = document.getElementById(`exp-start-date-${index}`);
    const endDateField = document.getElementById(`exp-end-date-${index}`);
    const currentJobCheckbox = document.getElementById(`current-job-${index}`);
    
    startDateField.addEventListener('change', function() {
        validateExperienceDates(index);
    });
    
    endDateField.addEventListener('change', function() {
        validateExperienceDates(index);
    });
}

/**
 * Validate experience dates (start date must be before end date)
 * @param {number} index - The index of the experience item
 * @returns {boolean} - Whether the dates are valid
 */
function validateExperienceDates(index) {
    const startDateField = document.getElementById(`exp-start-date-${index}`);
    const endDateField = document.getElementById(`exp-end-date-${index}`);
    const currentJobCheckbox = document.getElementById(`current-job-${index}`);
    
    // If current job is checked, end date is not required
    if (currentJobCheckbox.checked) {
        clearError(endDateField);
        return true;
    }
    
    // If both dates are filled, validate that start date is before end date
    if (startDateField.value && endDateField.value) {
        const startDate = new Date(startDateField.value);
        const endDate = new Date(endDateField.value);
        
        if (startDate > endDate) {
            showError(endDateField, 'End date must be after start date');
            return false;
        } else {
            clearError(endDateField);
            return true;
        }
    }
    
    return true;
}

/**
 * Setup education date validation
 * @param {number} index - The index of the education item
 */
function setupEducationDateValidation(index) {
    const startDateField = document.getElementById(`edu-start-date-${index}`);
    const endDateField = document.getElementById(`edu-end-date-${index}`);
    const currentEducationCheckbox = document.getElementById(`current-education-${index}`);
    
    startDateField.addEventListener('change', function() {
        validateEducationDates(index);
    });
    
    endDateField.addEventListener('change', function() {
        validateEducationDates(index);
    });
}

/**
 * Validate education dates (start date must be before end date)
 * @param {number} index - The index of the education item
 * @returns {boolean} - Whether the dates are valid
 */
function validateEducationDates(index) {
    const startDateField = document.getElementById(`edu-start-date-${index}`);
    const endDateField = document.getElementById(`edu-end-date-${index}`);
    const currentEducationCheckbox = document.getElementById(`current-education-${index}`);
    
    // If current education is checked, end date is not required
    if (currentEducationCheckbox.checked) {
        clearError(endDateField);
        return true;
    }
    
    // If both dates are filled, validate that start date is before end date
    if (startDateField.value && endDateField.value) {
        const startDate = new Date(startDateField.value);
        const endDate = new Date(endDateField.value);
        
        if (startDate > endDate) {
            showError(endDateField, 'End date must be after start date');
            return false;
        } else {
            clearError(endDateField);
            return true;
        }
    }
    
    return true;
}

/**
 * Setup word count for about section
 */
function setupWordCount() {
    const aboutField = document.getElementById('about');
    const wordCountElement = document.getElementById('word-count');
    
    aboutField.addEventListener('input', function() {
        const wordCount = countWords(this.value);
        wordCountElement.textContent = wordCount;
        
        // Validate word count (10-255 words)
        if (wordCount < 10 || wordCount > 255) {
            wordCountElement.style.color = 'var(--error-color)';
        } else {
            wordCountElement.style.color = 'var(--text-light)';
        }
    });
}

/**
 * Count words in a text
 * @param {string} text - The text to count words in
 * @returns {number} - The word count
 */
function countWords(text) {
    if (!text || text.trim() === '') return 0;
    return text.trim().split(/\s+/).length;
}

/**
 * Setup profile picture upload
 */
function setupProfilePictureUpload() {
    const profilePicture = document.getElementById('profile-picture');
    const profilePreview = document.getElementById('profile-preview');
    const uploadButton = document.getElementById('upload-picture-btn');
    const uploadOverlay = document.querySelector('.upload-overlay');

    // Click on preview image or overlay to trigger file input
    if (profilePreview) {
        profilePreview.addEventListener('click', function() {
            if (profilePicture) profilePicture.click();
        });
    }

    if (uploadOverlay) {
        uploadOverlay.addEventListener('click', function() {
            if (profilePicture) profilePicture.click();
        });
    }

    // Click on upload button to trigger file input
    if (uploadButton) {
        uploadButton.addEventListener('click', function() {
            if (profilePicture) profilePicture.click();
        });
    }

    // Handle file selection
    if (profilePicture) {
        profilePicture.addEventListener('change', function() {
            if (this.files && this.files[0] && profilePreview) {
                const file = this.files[0];
                
                // Validate file type
                const validTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
                if (!validTypes.includes(file.type)) {
                    alert('Please select a valid image file (JPEG, PNG, GIF, or WEBP)');
                    return;
                }
                
                // Validate file size (max 5MB)
                if (file.size > 5 * 1024 * 1024) {
                    alert('Image file is too large. Maximum size is 5MB.');
                    return;
                }
                
                // Preview the image
                const reader = new FileReader();
                reader.onload = function(e) {
                    if (profilePreview && e.target && e.target.result) {
                        profilePreview.src = e.target.result;
                    }
                };
                reader.readAsDataURL(file);
            }
        });
    }
}

/**
 * Navigate to a specific step
 * @param {number} step - The step to navigate to
 */
function goToStep(step) {
    // Hide all steps
    document.querySelectorAll('.setup-step').forEach(stepElement => {
        stepElement.classList.remove('active');
    });
    
    // Show the target step
    document.getElementById(`step-${step}`).classList.add('active');
    
    // Update progress bar
    updateProgress(step);
    
    // Update current step
    currentStep = step;
    
    // Scroll to top
    window.scrollTo(0, 0);
}

// Initialize the profile setup form with new functionality
document.addEventListener('DOMContentLoaded', function() {
    // Initialize the progress bar and step indicators
    updateProgress(currentStep);
    
    // Show the first step
    goToStep(1);
    
    // Set up navigation buttons
    setupNavigation();
    
    // Set up profile picture upload
    setupProfilePictureUpload();
    
    // Set up word counter for about section
    setupWordCounter();
    
    // Set up add buttons for dynamic sections
    document.getElementById('add-experience').addEventListener('click', addExperience);
    document.getElementById('add-education').addEventListener('click', addEducation);
    document.getElementById('add-skill').addEventListener('click', addSkill);
    document.getElementById('add-language').addEventListener('click', addLanguage);
    document.getElementById('add-project').addEventListener('click', addProject);
    document.getElementById('add-honor').addEventListener('click', addHonor);
    
    // Add initial items
    addExperience();
    addEducation();
    addSkill();
    addLanguage();
    
    // Set up form submission
    document.getElementById('profile-setup-form').addEventListener('submit', validateAndSubmitForm);
    
    // Set up modal close button
    document.getElementById('close-modal').addEventListener('click', closeModal);
});

/**
 * Set up navigation buttons
 */
function setupNavigation() {
    // Next buttons
    document.querySelectorAll('.btn-next').forEach(button => {
        button.addEventListener('click', function() {
            const currentStepElement = this.closest('.step-content');
            const currentStepNumber = parseInt(currentStepElement.getAttribute('data-step'));
            const nextStepNumber = currentStepNumber + 1;
            
            validateAndProceed(currentStepNumber, nextStepNumber);
        });
    });
    
    // Previous buttons
    document.querySelectorAll('.btn-prev').forEach(button => {
        button.addEventListener('click', function() {
            const currentStepElement = this.closest('.step-content');
            const currentStepNumber = parseInt(currentStepElement.getAttribute('data-step'));
            const prevStepNumber = currentStepNumber - 1;
            
            goToStep(prevStepNumber);
        });
    });
    
    // Step indicators
    document.querySelectorAll('.step').forEach(step => {
        step.addEventListener('click', function() {
            const clickedStepNumber = parseInt(this.getAttribute('data-step'));
            
            // Only allow clicking on completed steps or the next step
            if (clickedStepNumber <= currentStep) {
                goToStep(clickedStepNumber);
            }
        });
    });
}

/**
 * Set up profile picture upload
 */
function setupProfilePictureUpload() {
    const profilePictureInput = document.getElementById('profile-picture');
    const profilePreview = document.getElementById('profile-preview');
    const defaultProfilePic = profilePreview ? profilePreview.src : '../assets/default-profile.png';
    
    profilePictureInput.addEventListener('change', function() {
        if (this.files && this.files[0]) {
            const file = this.files[0];
            
            // Check file type
            if (!file.type.match('image.*')) {
                showErrorModal('Please select an image file (JPEG, PNG, GIF).');
                this.value = '';
                if (profilePreview) profilePreview.src = defaultProfilePic;
                return;
            }
            
            // Check file size (max 5MB)
            if (file.size > 5 * 1024 * 1024) {
                showErrorModal('Image size should be less than 5MB.');
                this.value = '';
                if (profilePreview) profilePreview.src = defaultProfilePic;
                return;
            }
            
            const reader = new FileReader();
            
            reader.onload = function(e) {
                if (profilePreview) profilePreview.src = e.target.result;
            };
            
            reader.readAsDataURL(file);
        }
    });
}

/**
 * Set up word counter for about section
 */
function setupWordCounter() {
    const aboutField = document.getElementById('about');
    const wordCountElement = document.getElementById('word-count');
    
    aboutField.addEventListener('input', function() {
        const wordCount = countWords(this.value);
        wordCountElement.textContent = `${wordCount} words`;
        
        // Update color based on word count
        if (wordCount < 10) {
            wordCountElement.className = 'word-count text-danger';
        } else if (wordCount > 255) {
            wordCountElement.className = 'word-count text-danger';
        } else {
            wordCountElement.className = 'word-count text-success';
        }
    });
}

/**
 * Count words in a string
 * @param {string} text - The text to count words in
 * @returns {number} - The number of words
 */
function countWords(text) {
    return text.trim().split(/\s+/).filter(word => word.length > 0).length;
}

/**
 * Set up experience date validation
 * @param {number} index - The index of the experience item
 */
function setupExperienceDateValidation(index) {
    const startDateField = document.getElementById(`exp-start-date-${index}`);
    const endDateField = document.getElementById(`exp-end-date-${index}`);
    
    startDateField.addEventListener('change', function() {
        validateExperienceDates(index);
    });
    
    endDateField.addEventListener('change', function() {
        validateExperienceDates(index);
    });
}

/**
 * Validate experience dates
 * @param {number} index - The index of the experience item
 * @returns {boolean} - Whether the dates are valid
 */
function validateExperienceDates(index) {
    const startDateField = document.getElementById(`exp-start-date-${index}`);
    const endDateField = document.getElementById(`exp-end-date-${index}`);
    const currentJobCheckbox = document.getElementById(`current-job-${index}`);
    
    // Skip validation if current job is checked
    if (currentJobCheckbox.checked) {
        clearError(startDateField);
        clearError(endDateField);
        return true;
    }
    
    // Skip validation if either date is not set
    if (!startDateField.value || !endDateField.value) {
        return true;
    }
    
    const startDate = new Date(startDateField.value);
    const endDate = new Date(endDateField.value);
    
    if (startDate > endDate) {
        showError(endDateField, 'End date must be after start date');
        return false;
    } else {
        clearError(endDateField);
        return true;
    }
}

/**
 * Set up education date validation
 * @param {number} index - The index of the education item
 */
function setupEducationDateValidation(index) {
    const startDateField = document.getElementById(`edu-start-date-${index}`);
    const endDateField = document.getElementById(`edu-end-date-${index}`);
    
    startDateField.addEventListener('change', function() {
        validateEducationDates(index);
    });
    
    endDateField.addEventListener('change', function() {
        validateEducationDates(index);
    });
}

/**
 * Validate education dates
 * @param {number} index - The index of the education item
 * @returns {boolean} - Whether the dates are valid
 */
function validateEducationDates(index) {
    const startDateField = document.getElementById(`edu-start-date-${index}`);
    const endDateField = document.getElementById(`edu-end-date-${index}`);
    const currentEducationCheckbox = document.getElementById(`current-education-${index}`);
    
    // Skip validation if current education is checked
    if (currentEducationCheckbox.checked) {
        clearError(startDateField);
        clearError(endDateField);
        return true;
    }
    
    // Skip validation if either date is not set
    if (!startDateField.value || !endDateField.value) {
        return true;
    }
    
    const startDate = new Date(startDateField.value);
    const endDate = new Date(endDateField.value);
    
    if (startDate > endDate) {
        showError(endDateField, 'End date must be after start date');
        return false;
    } else {
        clearError(endDateField);
        return true;
    }
}

/**
 * Validate age (must be at least 18 years old)
 * @param {HTMLElement} birthdayField - The birthday field
 * @returns {boolean} - Whether the age is valid
 */
function validateAge(birthdayField) {
    if (!birthdayField || !birthdayField.value) {
        return false;
    }
    
    const birthday = new Date(birthdayField.value);
    const today = new Date();
    
    // Check if the date is valid
    if (isNaN(birthday.getTime())) {
        return false;
    }
    
    // Calculate age
    let age = today.getFullYear() - birthday.getFullYear();
    const monthDiff = today.getMonth() - birthday.getMonth();
    
    if (monthDiff < 0 || (monthDiff === 0 && today.getDate() < birthday.getDate())) {
        age--;
    }
    
    // Check if age is at least 18
    return age >= 18;
}

/**
 * Go to a specific step
 * @param {number} step - The step to go to
 */
function goToStep(step) {
    // Hide all steps
    document.querySelectorAll('.setup-step').forEach(stepContent => {
        stepContent.classList.remove('active');
    });
    
    // Show the target step
    const targetStep = document.getElementById(`step-${step}`);
    if (targetStep) {
        targetStep.classList.add('active');
    }
    
    // Update current step
    currentStep = step;
    
    // Update progress
    updateProgress(step);
}

/**
 * Update progress bar and step indicators
 * @param {number} step - The current step
 */
function updateProgress(step) {
    // Update progress bar width
    const progressBar = document.getElementById('setup-progress');
    if (progressBar) {
        const progressPercentage = (step / totalSteps) * 100;
        progressBar.style.width = `${progressPercentage}%`;
    }
    
    // Update step indicators
    document.querySelectorAll('.step').forEach((stepElement, index) => {
        const stepNumber = index + 1;
        
        if (stepNumber < step) {
            stepElement.classList.remove('active');
            stepElement.classList.add('completed');
        } else if (stepNumber === step) {
            stepElement.classList.add('active');
            stepElement.classList.remove('completed');
        } else {
            stepElement.classList.remove('active', 'completed');
        }
    });
}

/**
 * Validate and proceed to the next step
 * @param {number} currentStep - The current step
 * @param {number} nextStep - The next step
 */
function validateAndProceed(currentStep, nextStep) {
    console.log('Validating step', currentStep, 'to proceed to step', nextStep);
    // Validate the current step
    if (validateStep(currentStep)) {
        console.log('Step', currentStep, 'is valid, proceeding to step', nextStep);
        goToStep(nextStep);
    } else {
        console.log('Step', currentStep, 'validation failed');
    }
}

/**
 * Validate a specific step
 * @param {number} step - The step to validate
 * @returns {boolean} - Whether the step is valid
 */
function validateStep(step) {
    let isValid = true;
    
    // Clear all errors first
    clearAllErrors();
    
    switch (step) {
        case 1: // Basic Information
            isValid = validateBasicInfo();
            break;
        case 2: // About
            isValid = validateAbout();
            break;
        case 3: // Experience
            isValid = validateExperience();
            break;
        case 4: // Education
            isValid = validateEducation();
            break;
        case 5: // Skills & More
            isValid = validateSkillsAndMore();
            break;
    }
    
    return isValid;
}

/**
 * Validate basic information step
 * @returns {boolean} - Whether the step is valid
 */
function validateBasicInfo() {
    let isValid = true;
    
    // Position validation
    const positionField = document.getElementById('position');
    if (positionField && !positionField.value.trim()) {
        showError(positionField, 'Position is required');
        isValid = false;
    }
    
    // City validation
    const cityField = document.getElementById('city');
    if (cityField && !cityField.value.trim()) {
        showError(cityField, 'City is required');
        isValid = false;
    }
    
    // State validation
    const stateField = document.getElementById('state');
    if (stateField && !stateField.value.trim()) {
        showError(stateField, 'State/Province is required');
        isValid = false;
    }
    
    // Birthday validation
    const birthdayField = document.getElementById('birthday');
    if (birthdayField) {
        if (!birthdayField.value) {
            showError(birthdayField, 'Birthday is required');
            isValid = false;
        } else if (!validateAge(birthdayField)) {
            showError(birthdayField, 'You must be at least 18 years old');
            isValid = false;
        }
    }
    
    return isValid;
}

/**
 * Validate about step
 * @returns {boolean} - Whether the step is valid
 */
function validateAbout() {
    let isValid = true;
    
    // Validate about text
    const aboutField = document.getElementById('about');
    if (!aboutField.value.trim()) {
        showError(aboutField, 'About section is required');
        isValid = false;
    } else {
        const wordCount = countWords(aboutField.value);
        if (wordCount < 10) {
            showError(aboutField, 'About section must be at least 10 words');
            isValid = false;
        } else if (wordCount > 255) {
            showError(aboutField, 'About section must be at most 255 words');
            isValid = false;
        }
    }
    
    return isValid;
}

/**
 * Validate experience step
 * @returns {boolean} - Whether the step is valid
 */
function validateExperience() {
    let isValid = true;
    
    // Validate each experience item
    document.querySelectorAll('.experience-item').forEach((item, index) => {
        // Get the index from the data attribute
        const itemIndex = item.getAttribute('data-index');
        
        // Validate job title
        const jobTitleField = document.getElementById(`job-title-${itemIndex}`);
        if (!jobTitleField.value.trim()) {
            showError(jobTitleField, 'Job title is required');
            isValid = false;
        }
        
        // Validate company
        const companyField = document.getElementById(`company-${itemIndex}`);
        if (!companyField.value.trim()) {
            showError(companyField, 'Company is required');
            isValid = false;
        }
        
        // Validate start date
        const startDateField = document.getElementById(`exp-start-date-${itemIndex}`);
        if (!startDateField.value) {
            showError(startDateField, 'Start date is required');
            isValid = false;
        }
        
        // Validate end date if not current job
        const currentJobCheckbox = document.getElementById(`current-job-${itemIndex}`);
        const endDateField = document.getElementById(`exp-end-date-${itemIndex}`);
        
        if (!currentJobCheckbox.checked && !endDateField.value) {
            showError(endDateField, 'End date is required');
            isValid = false;
        }
        
        // Validate date range
        if (startDateField.value && endDateField.value && !currentJobCheckbox.checked) {
            if (!validateExperienceDates(itemIndex)) {
                isValid = false;
            }
        }
        
        // Validate description
        const descriptionField = document.getElementById(`job-description-${itemIndex}`);
        if (!descriptionField.value.trim()) {
            showError(descriptionField, 'Description is required');
            isValid = false;
        }
    });
    
    return isValid;
}

/**
 * Validate education step
 * @returns {boolean} - Whether the step is valid
 */
function validateEducation() {
    let isValid = true;
    
    // Validate each education item
    document.querySelectorAll('.education-item').forEach((item, index) => {
        // Get the index from the data attribute
        const itemIndex = item.getAttribute('data-index');
        
        // Validate school
        const schoolField = document.getElementById(`school-${itemIndex}`);
        if (!schoolField.value.trim()) {
            showError(schoolField, 'School is required');
            isValid = false;
        }
        
        // Validate degree
        const degreeField = document.getElementById(`degree-${itemIndex}`);
        if (!degreeField.value) {
            showError(degreeField, 'Degree is required');
            isValid = false;
        }
        
        // Validate field of study
        const fieldOfStudyField = document.getElementById(`field-${itemIndex}`);
        if (!fieldOfStudyField.value.trim()) {
            showError(fieldOfStudyField, 'Field of study is required');
            isValid = false;
        }
        
        // Validate start date
        const startDateField = document.getElementById(`edu-start-date-${itemIndex}`);
        if (!startDateField.value) {
            showError(startDateField, 'Start date is required');
            isValid = false;
        }
        
        // Validate end date if not current education
        const currentEducationCheckbox = document.getElementById(`current-education-${itemIndex}`);
        const endDateField = document.getElementById(`edu-end-date-${itemIndex}`);
        
        if (!currentEducationCheckbox.checked && !endDateField.value) {
            showError(endDateField, 'End date is required');
            isValid = false;
        }
        
        // Validate date range
        if (startDateField.value && endDateField.value && !currentEducationCheckbox.checked) {
            if (!validateEducationDates(itemIndex)) {
                isValid = false;
            }
        }
    });
    
    return isValid;
}

/**
 * Validate skills and more step
 * @returns {boolean} - Whether the step is valid
 */
function validateSkillsAndMore() {
    let isValid = true;
    
    // Validate skills
    document.querySelectorAll('.skill-item').forEach((item, index) => {
        // Get the index from the data attribute
        const itemIndex = item.getAttribute('data-index');
        
        // Validate skill name
        const skillField = document.getElementById(`skill-${itemIndex}`);
        if (!skillField.value.trim()) {
            showError(skillField, 'Skill name is required');
            isValid = false;
        }
        
        // Validate skill level
        const skillLevelField = document.getElementById(`skill-level-${itemIndex}`);
        if (!skillLevelField.value) {
            showError(skillLevelField, 'Skill level is required');
            isValid = false;
        }
    });
    
    // Validate languages
    document.querySelectorAll('.language-item').forEach((item, index) => {
        // Get the index from the data attribute
        const itemIndex = item.getAttribute('data-index');
        
        // Validate language name
        const languageField = document.getElementById(`language-${itemIndex}`);
        if (!languageField.value.trim()) {
            showError(languageField, 'Language name is required');
            isValid = false;
        }
        
        // Validate language level
        const languageLevelField = document.getElementById(`language-level-${itemIndex}`);
        if (!languageLevelField.value) {
            showError(languageLevelField, 'Language level is required');
            isValid = false;
        }
    });
    
    // Validate projects
    document.querySelectorAll('.project-item').forEach((item, index) => {
        // Get the index from the data attribute
        const itemIndex = item.getAttribute('data-index');
        
        // Validate project name
        const projectNameField = document.getElementById(`project-name-${itemIndex}`);
        if (!projectNameField.value.trim()) {
            showError(projectNameField, 'Project name is required');
            isValid = false;
        }
        
        // Validate project description
        const projectDescriptionField = document.getElementById(`project-description-${itemIndex}`);
        if (!projectDescriptionField.value.trim()) {
            showError(projectDescriptionField, 'Project description is required');
            isValid = false;
        }
    });
    
    // Validate honors
    document.querySelectorAll('.honor-item').forEach((item, index) => {
        // Get the index from the data attribute
        const itemIndex = item.getAttribute('data-index');
        
        // Validate honor title
        const honorTitleField = document.getElementById(`honor-title-${itemIndex}`);
        if (!honorTitleField.value.trim()) {
            showError(honorTitleField, 'Honor title is required');
            isValid = false;
        }
        
        // Validate honor issuer
        const honorIssuerField = document.getElementById(`honor-issuer-${itemIndex}`);
        if (!honorIssuerField.value.trim()) {
            showError(honorIssuerField, 'Honor issuer is required');
            isValid = false;
        }
        
        // Validate honor date
        const honorDateField = document.getElementById(`honor-date-${itemIndex}`);
        if (!honorDateField.value) {
            showError(honorDateField, 'Date received is required');
            isValid = false;
        }
    });
    
    return isValid;
}

/**
 * Add a new experience item
 */
function addExperience() {
    const experienceContainer = document.getElementById('experience-container');
    const newIndex = experienceCount;
    experienceCount++;
    
    const experienceItem = document.createElement('div');
    experienceItem.className = 'experience-item';
    experienceItem.setAttribute('data-index', newIndex);
    
    experienceItem.innerHTML = `
        <div class="item-header">
            <h3>Experience #${experienceCount}</h3>
            <button type="button" class="btn-remove" title="Remove this experience">
                <i class="fas fa-trash"></i>
            </button>
        </div>

        <div class="form-group">
            <label for="job-title-${newIndex}">Job Title <span class="required">*</span></label>
            <input type="text" id="job-title-${newIndex}" name="experience[${newIndex}][title]" placeholder="e.g. Software Engineer" required>
        </div>

        <div class="form-group">
            <label for="company-${newIndex}">Company <span class="required">*</span></label>
            <input type="text" id="company-${newIndex}" name="experience[${newIndex}][company]" placeholder="e.g. Google" required>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label for="exp-start-date-${newIndex}">Start Date <span class="required">*</span></label>
                <input type="date" id="exp-start-date-${newIndex}" name="experience[${newIndex}][start_date]" required>
            </div>
            <div class="form-group">
                <label for="exp-end-date-${newIndex}">End Date</label>
                <input type="date" id="exp-end-date-${newIndex}" name="experience[${newIndex}][end_date]">
            </div>
        </div>

        <div class="form-group checkbox-group">
            <input type="checkbox" id="current-job-${newIndex}" name="experience[${newIndex}][current]" class="current-job-checkbox">
            <label for="current-job-${newIndex}">I currently work here</label>
        </div>

        <div class="form-group">
            <label for="job-description-${newIndex}">Description <span class="required">*</span></label>
            <textarea id="job-description-${newIndex}" name="experience[${newIndex}][description]" rows="4" placeholder="Describe your responsibilities and achievements..." required></textarea>
        </div>
    `;
    
    experienceContainer.appendChild(experienceItem);
    
    // Set up event listeners for the new experience item
    setupExperienceDateValidation(newIndex);
    
    // Set up current job checkbox
    const currentJobCheckbox = document.getElementById(`current-job-${newIndex}`);
    const endDateField = document.getElementById(`exp-end-date-${newIndex}`);
    
    currentJobCheckbox.addEventListener('change', function() {
        endDateField.disabled = this.checked;
        if (this.checked) {
            endDateField.value = '';
        }
    });
    
    // Set up remove button
    const removeButton = experienceItem.querySelector('.btn-remove');
    removeButton.addEventListener('click', function() {
        experienceItem.remove();
        // Update experience numbers
        updateExperienceNumbers();
    });
}

/**
 * Update experience numbers after removing an item
 */
function updateExperienceNumbers() {
    document.querySelectorAll('.experience-item').forEach((item, index) => {
        item.querySelector('h3').textContent = `Experience #${index + 1}`;
    });
}

/**
 * Add a new education item
 */
function addEducation() {
    const educationContainer = document.getElementById('education-container');
    const newIndex = educationCount;
    educationCount++;
    
    const educationItem = document.createElement('div');
    educationItem.className = 'education-item';
    educationItem.setAttribute('data-index', newIndex);
    
    educationItem.innerHTML = `
        <div class="item-header">
            <h3>Education #${educationCount}</h3>
            <button type="button" class="btn-remove" title="Remove this education">
                <i class="fas fa-trash"></i>
            </button>
        </div>

        <div class="form-group">
            <label for="school-${newIndex}">School <span class="required">*</span></label>
            <input type="text" id="school-${newIndex}" name="education[${newIndex}][school]" placeholder="e.g. Stanford University" required>
        </div>

        <div class="form-group">
            <label for="degree-${newIndex}">Degree <span class="required">*</span></label>
            <select id="degree-${newIndex}" name="education[${newIndex}][degree]" required>
                <option value="">Select a degree</option>
                <option value="High School">High School</option>
                <option value="Associate's Degree">Associate's Degree</option>
                <option value="Bachelor of Engineering">Bachelor of Engineering</option>
                <option value="Bachelor of Science">Bachelor of Science</option>
                <option value="Master of Science">Master of Science</option>
                <option value="Master of Engineering">Master of Engineering</option>
                <option value="Master of Research">Master of Research</option>
                <option value="PhD">PhD</option>
                <option value="Other">Other</option>
            </select>
        </div>

        <div class="form-group">
            <label for="field-${newIndex}">Field of Study <span class="required">*</span></label>
            <input type="text" id="field-${newIndex}" name="education[${newIndex}][field]" placeholder="e.g. Computer Science, Civil Engineering" required>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label for="edu-start-date-${newIndex}">Start Date <span class="required">*</span></label>
                <input type="date" id="edu-start-date-${newIndex}" name="education[${newIndex}][start_date]" required>
            </div>
            <div class="form-group">
                <label for="edu-end-date-${newIndex}">End Date</label>
                <input type="date" id="edu-end-date-${newIndex}" name="education[${newIndex}][end_date]">
            </div>
        </div>

        <div class="form-group checkbox-group">
            <input type="checkbox" id="current-education-${newIndex}" name="education[${newIndex}][current]" class="current-education-checkbox">
            <label for="current-education-${newIndex}">I'm currently studying here</label>
        </div>

        <div class="form-group">
            <label for="education-description-${newIndex}">Description</label>
            <textarea id="education-description-${newIndex}" name="education[${newIndex}][description]" rows="4" placeholder="Describe your studies, achievements, or activities..."></textarea>
        </div>
    `;
    
    educationContainer.appendChild(educationItem);
    
    // Set up event listeners for the new education item
    setupEducationDateValidation(newIndex);
    
    // Set up current education checkbox
    const currentEducationCheckbox = document.getElementById(`current-education-${newIndex}`);
    const endDateField = document.getElementById(`edu-end-date-${newIndex}`);
    
    currentEducationCheckbox.addEventListener('change', function() {
        endDateField.disabled = this.checked;
        if (this.checked) {
            endDateField.value = '';
        }
    });
    
    // Set up remove button
    const removeButton = educationItem.querySelector('.btn-remove');
    removeButton.addEventListener('click', function() {
        educationItem.remove();
        // Update education numbers
        updateEducationNumbers();
    });
}

/**
 * Update education numbers after removing an item
 */
function updateEducationNumbers() {
    document.querySelectorAll('.education-item').forEach((item, index) => {
        item.querySelector('h3').textContent = `Education #${index + 1}`;
    });
}

/**
 * Add a new skill item
 */
function addSkill() {
    const skillsContainer = document.getElementById('skills-container');
    const newIndex = skillCount;
    skillCount++;
    
    const skillItem = document.createElement('div');
    skillItem.className = 'skill-item';
    skillItem.setAttribute('data-index', newIndex);
    
    skillItem.innerHTML = `
        <div class="form-row">
            <div class="form-group grow">
                <input type="text" id="skill-${newIndex}" name="skills[${newIndex}][name]" placeholder="e.g. JavaScript, AutoCAD, Project Management" required>
            </div>
            <div class="form-group">
                <select id="skill-level-${newIndex}" name="skills[${newIndex}][level]" required>
                    <option value="">Level</option>
                    <option value="Beginner">Beginner</option>
                    <option value="Intermediate">Intermediate</option>
                    <option value="Advanced">Advanced</option>
                    <option value="Expert">Expert</option>
                </select>
            </div>
            <button type="button" class="btn-remove" title="Remove this skill">
                <i class="fas fa-times"></i>
            </button>
        </div>
    `;
    
    skillsContainer.appendChild(skillItem);
    
    // Set up remove button
    const removeButton = skillItem.querySelector('.btn-remove');
    removeButton.addEventListener('click', function() {
        skillItem.remove();
    });
}

/**
 * Add a new language item
 */
function addLanguage() {
    const languagesContainer = document.getElementById('languages-container');
    const newIndex = languageCount;
    languageCount++;
    
    const languageItem = document.createElement('div');
    languageItem.className = 'language-item';
    languageItem.setAttribute('data-index', newIndex);
    
    languageItem.innerHTML = `
        <div class="form-row">
            <div class="form-group grow">
                <input type="text" id="language-${newIndex}" name="languages[${newIndex}][name]" placeholder="e.g. English, French, Spanish" required>
            </div>
            <div class="form-group">
                <select id="language-level-${newIndex}" name="languages[${newIndex}][level]" required>
                    <option value="">Level</option>
                    <option value="Basic">Basic</option>
                    <option value="Conversational">Conversational</option>
                    <option value="Proficient">Proficient</option>
                    <option value="Fluent">Fluent</option>
                    <option value="Native">Native</option>
                </select>
            </div>
            <button type="button" class="btn-remove" title="Remove this language">
                <i class="fas fa-times"></i>
            </button>
        </div>
    `;
    
    languagesContainer.appendChild(languageItem);
    
    // Set up remove button
    const removeButton = languageItem.querySelector('.btn-remove');
    removeButton.addEventListener('click', function() {
        languageItem.remove();
    });
}

/**
 * Add a new project item
 */
function addProject() {
    const projectsContainer = document.getElementById('projects-container');
    const newIndex = projectCount;
    projectCount++;
    
    const projectItem = document.createElement('div');
    projectItem.className = 'project-item';
    projectItem.setAttribute('data-index', newIndex);
    
    projectItem.innerHTML = `
        <div class="item-header">
            <h4>Project #${projectCount}</h4>
            <button type="button" class="btn-remove" title="Remove this project">
                <i class="fas fa-trash"></i>
            </button>
        </div>
        <div class="form-group">
            <label for="project-name-${newIndex}">Project Name <span class="required">*</span></label>
            <input type="text" id="project-name-${newIndex}" name="projects[${newIndex}][name]" placeholder="e.g. E-commerce Website, Bridge Design" required>
        </div>
        <div class="form-group">
            <label for="project-url-${newIndex}">Project URL</label>
            <input type="url" id="project-url-${newIndex}" name="projects[${newIndex}][url]" placeholder="e.g. https://github.com/yourusername/project">
        </div>
        <div class="form-group">
            <label for="project-description-${newIndex}">Description <span class="required">*</span></label>
            <textarea id="project-description-${newIndex}" name="projects[${newIndex}][description]" rows="3" placeholder="Describe your project..." required></textarea>
        </div>
    `;
    
    projectsContainer.appendChild(projectItem);
    
    // Set up remove button
    const removeButton = projectItem.querySelector('.btn-remove');
    removeButton.addEventListener('click', function() {
        projectItem.remove();
        // Update project numbers
        updateProjectNumbers();
    });
}

/**
 * Update project numbers after removing an item
 */
function updateProjectNumbers() {
    document.querySelectorAll('.project-item').forEach((item, index) => {
        item.querySelector('h4').textContent = `Project #${index + 1}`;
    });
}

/**
 * Add a new honor item
 */
function addHonor() {
    const honorsContainer = document.getElementById('honors-container');
    const newIndex = honorCount;
    honorCount++;
    
    const honorItem = document.createElement('div');
    honorItem.className = 'honor-item';
    honorItem.setAttribute('data-index', newIndex);
    
    honorItem.innerHTML = `
        <div class="item-header">
            <h4>Honor #${honorCount}</h4>
            <button type="button" class="btn-remove" title="Remove this honor">
                <i class="fas fa-trash"></i>
            </button>
        </div>
        <div class="form-group">
            <label for="honor-title-${newIndex}">Title <span class="required">*</span></label>
            <input type="text" id="honor-title-${newIndex}" name="honors[${newIndex}][title]" placeholder="e.g. Best Graduate Thesis, Innovation Award" required>
        </div>
        <div class="form-group">
            <label for="honor-issuer-${newIndex}">Issuer <span class="required">*</span></label>
            <input type="text" id="honor-issuer-${newIndex}" name="honors[${newIndex}][issuer]" placeholder="e.g. IEEE, University of California" required>
        </div>
        <div class="form-group">
            <label for="honor-date-${newIndex}">Date Received <span class="required">*</span></label>
            <input type="date" id="honor-date-${newIndex}" name="honors[${newIndex}][date]" required>
        </div>
        <div class="form-group">
            <label for="honor-description-${newIndex}">Description</label>
            <textarea id="honor-description-${newIndex}" name="honors[${newIndex}][description]" rows="2" placeholder="Describe this honor or award..."></textarea>
        </div>
    `;
    
    honorsContainer.appendChild(honorItem);
    
    // Set up remove button
    const removeButton = honorItem.querySelector('.btn-remove');
    removeButton.addEventListener('click', function() {
        honorItem.remove();
        // Update honor numbers
        updateHonorNumbers();
    });
}

/**
 * Update honor numbers after removing an item
 */
function updateHonorNumbers() {
    document.querySelectorAll('.honor-item').forEach((item, index) => {
        item.querySelector('h4').textContent = `Honor #${index + 1}`;
    });
}

/**
 * Show error message for a field
 * @param {HTMLElement} field - The field with error
 * @param {string} message - The error message
 */
function showError(field, message) {
    if (!field) return;
    
    field.classList.add('error');
    
    // Create error message element if it doesn't exist
    let errorElement = field.nextElementSibling;
    if (!errorElement || !errorElement.classList.contains('error-message')) {
        errorElement = document.createElement('div');
        errorElement.className = 'error-message';
        field.parentNode.insertBefore(errorElement, field.nextSibling);
    }
    
    errorElement.textContent = message;
}

/**
 * Clear error for a field
 * @param {HTMLElement} field - The field to clear error for
 */
function clearError(field) {
    field.classList.remove('error');
    
    // Remove error message element if it exists
    const errorElement = field.nextElementSibling;
    if (errorElement && errorElement.classList.contains('error-message')) {
        errorElement.remove();
    }
}

/**
 * Clear all errors
 */
function clearAllErrors() {
    // Remove error class from all fields
    document.querySelectorAll('.error').forEach(field => {
        field.classList.remove('error');
    });
    
    // Remove all error messages
    document.querySelectorAll('.error-message').forEach(errorMsg => {
        errorMsg.remove();
    });
}

/**
 * Show error modal
 * @param {string} message - The error message
 */
function showErrorModal(message) {
    const errorModal = document.getElementById('error-modal');
    const errorMessage = document.getElementById('error-message');
    
    errorMessage.textContent = message;
    errorModal.classList.add('show');
}

/**
 * Close modal
 */
function closeModal() {
    const errorModal = document.getElementById('error-modal');
    errorModal.classList.remove('show');
}

/**
 * Validate and submit the form
 * @param {Event} event - The submit event
 */
function validateAndSubmitForm(event) {
    event.preventDefault();
    
    // Validate all steps
    let isValid = true;
    for (let step = 1; step <= totalSteps; step++) {
        if (!validateStep(step)) {
            isValid = false;
            goToStep(step);
            break;
        }
    }
    
    if (isValid) {
        // Submit the form
        event.target.submit();
    }
}
