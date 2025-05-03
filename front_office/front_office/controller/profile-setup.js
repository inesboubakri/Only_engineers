// City data based on country selection
const cityData = {
    "France": ["Paris", "Nice", "Marseille", "Lyon", "Toulouse", "Bordeaux", "Lille", "Strasbourg", "Nantes"],
    "Germany": ["Berlin", "Munich", "Hamburg", "Cologne", "Frankfurt", "Stuttgart", "Düsseldorf", "Leipzig", "Dresden"],
    "Tunisia": ["Tunis", "Sfax", "Sousse", "Kairouan", "Bizerte", "Gabès", "Ariana", "Gafsa", "Monastir"]
};

// Function to update cities based on selected country
function updateCities() {
    const countrySelect = document.getElementById('country');
    const citySelect = document.getElementById('city');
    
    // Clear existing options
    citySelect.innerHTML = '<option value="" disabled selected>Select a city</option>';
    
    // Get selected country
    const selectedCountry = countrySelect.value;
    
    // If a country is selected and it exists in our data
    if (selectedCountry && cityData[selectedCountry]) {
        // Enable the city select
        citySelect.disabled = false;
        
        // Add city options
        cityData[selectedCountry].forEach(city => {
            const option = document.createElement('option');
            option.value = city;
            option.textContent = city;
            citySelect.appendChild(option);
        });
    } else {
        // If no country selected or country not in our data, disable city select
        citySelect.disabled = true;
    }
}

// Current step
let currentStep = 1;
const totalSteps = 5;

// Initialize counters
let experienceCounter = 1;
let educationCounter = 1;
let organizationCounter = 1;
let honorCounter = 1;
let courseCounter = 1;
let projectCounter = 1;
let languageCounter = 1;
let skillCounter = 1;

// DOM elements
const form = document.getElementById('profileForm');
const progressFill = document.querySelector('.progress-fill');
const stepCircles = document.querySelectorAll('.step-circle');
const formSections = document.querySelectorAll('.form-section');

// Set max date for birthday (18 years ago)
function setBirthdayMaxDate() {
    const today = new Date();
    
    // Set max date (today's date)
    const maxDate = today.toISOString().split('T')[0];
    
    // Set min date (recommended 100 years ago as reasonable minimum)
    const minDate = new Date(
        today.getFullYear() - 100,
        today.getMonth(),
        today.getDate()
    ).toISOString().split('T')[0];
    
    // Set default date (exactly 18 years ago)
    const eighteenYearsAgo = new Date(
        today.getFullYear() - 18,
        today.getMonth(),
        today.getDate()
    ).toISOString().split('T')[0];
    
    const birthdayField = document.getElementById('birthday');
    birthdayField.max = maxDate;
    birthdayField.min = minDate;
    
    // Set a default value that's valid (exactly 18 years ago)
    birthdayField.value = eighteenYearsAgo;
}

// Navigation buttons
document.getElementById('step1Next').addEventListener('click', () => validateAndProceed(1, 2));
document.getElementById('step2Prev').addEventListener('click', () => navigateTo(1));
document.getElementById('step2Next').addEventListener('click', () => validateAndProceed(2, 3));
document.getElementById('step3Prev').addEventListener('click', () => navigateTo(2));
document.getElementById('step3Next').addEventListener('click', () => validateAndProceed(3, 4));
document.getElementById('step4Prev').addEventListener('click', () => navigateTo(3));
document.getElementById('step4Next').addEventListener('click', () => validateAndProceed(4, 5));
document.getElementById('step5Prev').addEventListener('click', () => navigateTo(4));

// Profile picture upload
document.getElementById('uploadProfileBtn').addEventListener('click', () => {
    document.getElementById('profilePicInput').click();
});

// Add buttons
document.getElementById('addExperienceBtn').addEventListener('click', addExperience);
document.getElementById('addEducationBtn').addEventListener('click', addEducation);
document.getElementById('addOrganizationBtn').addEventListener('click', addOrganization);
document.getElementById('addHonorBtn').addEventListener('click', addHonor);
document.getElementById('addCourseBtn').addEventListener('click', addCourse);
document.getElementById('addProjectBtn').addEventListener('click', addProject);
document.getElementById('addLanguageBtn').addEventListener('click', addLanguage);
document.getElementById('addSkillBtn').addEventListener('click', addSkill);

// Form submission
form.addEventListener('submit', function(e) {
    e.preventDefault();
    
    // Validate final step
    if (validateStep(5)) {
        // Process seeking checkboxes - convert to comma-separated string
        const seekingCheckboxes = document.querySelectorAll('input[name="seeking"]:checked');
        if (seekingCheckboxes.length > 0) {
            // Get all selected values
            const seekingValues = Array.from(seekingCheckboxes).map(cb => cb.value);
            
            // Create a hidden field with the comma-separated values
            const hiddenField = document.createElement('input');
            hiddenField.type = 'hidden';
            hiddenField.name = 'seeking_values';
            hiddenField.value = seekingValues.join(', ');
            form.appendChild(hiddenField);
        }
        
        // Submit the form to the server
        const formData = new FormData(form);
        
        // Use fetch API to submit the form
        fetch('../model/save_profile.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Profile created successfully!');
                // Use the redirect URL provided by the server instead of hardcoded dashboard path
                window.location.href = data.redirect || '../view/user-profile.php';
            } else {
                alert('Error: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred while saving your profile. Please try again.');
        });
    }
});

// Word count for About section
document.getElementById('about').addEventListener('input', function() {
    countWords(this, 'aboutWordCount');
});

// Initialize page
document.addEventListener('DOMContentLoaded', function() {
    // Set up birthday max date (must be 18+ years old)
    setBirthdayMaxDate();
    
    // Set up textarea word counters
    setupTextareaWordCounters();
    
    // Basic field validations
    setupFieldValidations();
    
    // Date range validations
    setupDateValidations();
    
    // Text validations to ensure fields contain letters
    setupTextValidations();
});

// Functions

function navigateTo(step) {
    if (step < 1 || step > totalSteps) return;
    
    // Update current step
    currentStep = step;
    
    // Update progress bar
    const progressPercentage = ((currentStep - 1) / (totalSteps - 1)) * 100;
    progressFill.style.width = `${progressPercentage}%`;
    
    // Update step circles
    stepCircles.forEach((circle, index) => {
        const stepNum = index + 1;
        circle.classList.remove('active', 'completed', 'inactive');
        
        if (stepNum < currentStep) {
            circle.classList.add('completed');
        } else if (stepNum === currentStep) {
            circle.classList.add('active');
        } else {
            circle.classList.add('inactive');
        }
    });
    
    // Show current section, hide others
    formSections.forEach((section, index) => {
        const sectionNum = index + 1;
        section.classList.toggle('active', sectionNum === currentStep);
    });
}

function validateAndProceed(currentStepNum, nextStepNum) {
    if (validateStep(currentStepNum)) {
        navigateTo(nextStepNum);
    }
}

function validateStep(step) {
    let isValid = true;
    
    switch(step) {
        case 1:
            // Validate Position (contains letters)
            const position = document.getElementById('position');
            const positionError = document.getElementById('position-error');
            if (!containsLetters(position.value)) {
                positionError.style.display = 'block';
                position.classList.add('border-red-500');
                isValid = false;
            } else {
                positionError.style.display = 'none';
                position.classList.remove('border-red-500');
            }
            
            // Validate Birthday (at least 18 years old)
            const birthday = document.getElementById('birthday');
            const birthdayError = document.getElementById('birthday-error');
            if (birthday.value) {
                const birthDate = new Date(birthday.value);
                const today = new Date();
                const age = today.getFullYear() - birthDate.getFullYear();
                const monthDiff = today.getMonth() - birthDate.getMonth();
                
                if (age < 18 || (age === 18 && monthDiff < 0) || (age === 18 && monthDiff === 0 && today.getDate() < birthDate.getDate())) {
                    birthdayError.style.display = 'block';
                    birthday.classList.add('border-red-500');
                    isValid = false;
                } else {
                    birthdayError.style.display = 'none';
                    birthday.classList.remove('border-red-500');
                }
            }
            
            // Validate other required fields
            const country = document.getElementById('country');
            const city = document.getElementById('city');
            
            if (!position.value || !country.value || !city.value || !birthday.value) {
                isValid = false;
                alert('Please fill in all required fields.');
            }
            
            break;
            
        case 2:
            // Validate About (10-255 words)
            const about = document.getElementById('about');
            const aboutError = document.getElementById('about-error');
            const aboutWordCount = getWordCount(about.value);
            
            if (aboutWordCount < 10 || aboutWordCount > 255) {
                aboutError.style.display = 'block';
                about.classList.add('border-red-500');
                isValid = false;
            } else {
                aboutError.style.display = 'none';
                about.classList.remove('border-red-500');
            }
            
            // Validate seeking options (at least one selected)
            const seekingOptions = document.querySelectorAll('input[name="seeking"]:checked');
            const seekingError = document.getElementById('seeking-error');
            
            if (seekingOptions.length === 0) {
                seekingError.style.display = 'block';
                isValid = false;
            } else {
                seekingError.style.display = 'none';
            }
            
            break;
            
        case 3:
            // Validate experience entries
            const experienceItems = document.querySelectorAll('.experience-item');
            
            experienceItems.forEach(item => {
                const jobTitle = item.querySelector('input[name="jobTitle[]"]');
                const company = item.querySelector('input[name="company[]"]');
                const startDate = item.querySelector('input[name="expStartDate[]"]');
                const endDate = item.querySelector('input[name="expEndDate[]"]');
                const currentJob = item.querySelector('input[name="currentJob[]"]');
                const description = item.querySelector('textarea[name="expDescription[]"]');
                const dateError = item.querySelector('.date-error');
                
                // Validate text fields
                if (!containsLetters(jobTitle.value)) {
                    jobTitle.classList.add('border-red-500');
                    jobTitle.parentElement.querySelector('.text-validation-error').style.display = 'block';
                    isValid = false;
                }
                
                if (!containsLetters(company.value)) {
                    company.classList.add('border-red-500');
                    company.parentElement.querySelector('.text-validation-error').style.display = 'block';
                    isValid = false;
                }
                
                // Validate required fields
                if (!jobTitle.value || !company.value || !startDate.value || !description.value) {
                    isValid = false;
                }
                
                // Validate date range
                if (!currentJob.checked && endDate.value && startDate.value) {
                    if (!validateDateRange(startDate.value, endDate.value)) {
                        dateError.style.display = 'block';
                        endDate.classList.add('border-red-500');
                        isValid = false;
                    }
                }
                
                // Validate description word count
                const wordCount = getWordCount(description.value);
                if (wordCount < 5) {
                    isValid = false;
                    description.classList.add('border-red-500');
                    item.querySelector('.word-count-error').style.display = 'block';
                } else {
                    description.classList.remove('border-red-500');
                    item.querySelector('.word-count-error').style.display = 'none';
                }
            });
            
            if (!isValid) {
                alert('Please fill in all required fields for each experience entry correctly. Make sure all fields contain valid data and descriptions have at least 5 words.');
            }
            
            break;
            
        case 4:
            // Validate education entries
            const educationItems = document.querySelectorAll('.education-item');
            
            educationItems.forEach(item => {
                const school = item.querySelector('input[name="school[]"]');
                const degree = item.querySelector('select[name="degree[]"]');
                const fieldOfStudy = item.querySelector('input[name="fieldOfStudy[]"]');
                const startDate = item.querySelector('input[name="eduStartDate[]"]');
                const endDate = item.querySelector('input[name="eduEndDate[]"]');
                const currentEdu = item.querySelector('input[name="currentEducation[]"]');
                const description = item.querySelector('textarea[name="eduDescription[]"]');
                const dateError = item.querySelector('.date-error');
                
                // Validate text fields
                if (!containsLetters(school.value)) {
                    school.classList.add('border-red-500');
                    school.parentElement.querySelector('.text-validation-error').style.display = 'block';
                    isValid = false;
                }
                
                if (!containsLetters(fieldOfStudy.value)) {
                    fieldOfStudy.classList.add('border-red-500');
                    fieldOfStudy.parentElement.querySelector('.text-validation-error').style.display = 'block';
                    isValid = false;
                }
                
                // Validate required fields
                if (!school.value || !degree.value || !fieldOfStudy.value || !startDate.value || !description.value) {
                    isValid = false;
                }
                
                // Validate date range
                if (!currentEdu.checked && endDate.value && startDate.value) {
                    if (!validateDateRange(startDate.value, endDate.value)) {
                        dateError.style.display = 'block';
                        endDate.classList.add('border-red-500');
                        isValid = false;
                    }
                }
                
                // Validate description word count
                const wordCount = getWordCount(description.value);
                if (wordCount < 5) {
                    isValid = false;
                    description.classList.add('border-red-500');
                    item.querySelector('.word-count-error').style.display = 'block';
                } else {
                    description.classList.remove('border-red-500');
                    item.querySelector('.word-count-error').style.display = 'none';
                }
            });
            
            if (!isValid) {
                alert('Please fill in all required fields for each education entry correctly. Make sure all fields contain valid data and descriptions have at least 5 words.');
            }
            
            break;
            
        case 5:
            // Validate organizations, honors, courses, projects, languages, and skills
            
            // Organizations
            const orgItems = document.querySelectorAll('.organization-item');
            orgItems.forEach(item => {
                const orgName = item.querySelector('input[name="orgName[]"]');
                const startDate = item.querySelector('input[name="orgStartDate[]"]');
                const endDate = item.querySelector('input[name="orgEndDate[]"]');
                const currentOrg = item.querySelector('input[name="currentOrg[]"]');
                const dateError = item.querySelector('.date-error');
                const description = item.querySelector('textarea[name="orgDescription[]"]');
                
                // Validate text fields
                if (!containsLetters(orgName.value)) {
                    orgName.classList.add('border-red-500');
                    orgName.parentElement.querySelector('.text-validation-error').style.display = 'block';
                    isValid = false;
                } else {
                    orgName.classList.remove('border-red-500');
                    orgName.parentElement.querySelector('.text-validation-error').style.display = 'none';
                }
                
                // Validate date range
                if (!currentOrg.checked && endDate.value && startDate.value) {
                    if (!validateDateRange(startDate.value, endDate.value)) {
                        dateError.style.display = 'block';
                        endDate.classList.add('border-red-500');
                        isValid = false;
                    } else {
                        dateError.style.display = 'none';
                        endDate.classList.remove('border-red-500');
                    }
                }
                
                // Validate description word count
                const wordCount = getWordCount(description.value);
                if (wordCount < 5) {
                    isValid = false;
                    description.classList.add('border-red-500');
                    item.querySelector('.word-count-error').style.display = 'block';
                } else {
                    description.classList.remove('border-red-500');
                    item.querySelector('.word-count-error').style.display = 'none';
                }
            });
            
            // Honors
            const honorItems = document.querySelectorAll('.honors-item');
            honorItems.forEach(item => {
                const honorName = item.querySelector('input[name="honorName[]"]');
                
                // Validate text fields
                if (!containsLetters(honorName.value)) {
                    honorName.classList.add('border-red-500');
                    honorName.parentElement.querySelector('.text-validation-error').style.display = 'block';
                    isValid = false;
                } else {
                    honorName.classList.remove('border-red-500');
                    honorName.parentElement.querySelector('.text-validation-error').style.display = 'none';
                }
            });
            
            // Courses
            const courseItems = document.querySelectorAll('.course-item');
            courseItems.forEach(item => {
                const courseTitle = item.querySelector('input[name="courseTitle[]"]');
                const startDate = item.querySelector('input[name="courseStartDate[]"]');
                const endDate = item.querySelector('input[name="courseEndDate[]"]');
                const currentCourse = item.querySelector('input[name="currentCourse[]"]');
                const dateError = item.querySelector('.date-error');
                
                // Validate text fields
                if (!containsLetters(courseTitle.value)) {
                    courseTitle.classList.add('border-red-500');
                    courseTitle.parentElement.querySelector('.text-validation-error').style.display = 'block';
                    isValid = false;
                } else {
                    courseTitle.classList.remove('border-red-500');
                    courseTitle.parentElement.querySelector('.text-validation-error').style.display = 'none';
                }
                
                // Validate date range
                if (!currentCourse.checked && endDate.value && startDate.value) {
                    if (!validateDateRange(startDate.value, endDate.value)) {
                        dateError.style.display = 'block';
                        endDate.classList.add('border-red-500');
                        isValid = false;
                    } else {
                        dateError.style.display = 'none';
                        endDate.classList.remove('border-red-500');
                    }
                }
            });
            
            // Projects
            const projectItems = document.querySelectorAll('.project-item');
            projectItems.forEach(item => {
                const projectTitle = item.querySelector('input[name="projectTitle[]"]');
                const startDate = item.querySelector('input[name="projectStartDate[]"]');
                const endDate = item.querySelector('input[name="projectEndDate[]"]');
                const currentProject = item.querySelector('input[name="currentProject[]"]');
                const dateError = item.querySelector('.date-error');
                
                // Validate text fields
                if (!containsLetters(projectTitle.value)) {
                    projectTitle.classList.add('border-red-500');
                    projectTitle.parentElement.querySelector('.text-validation-error').style.display = 'block';
                    isValid = false;
                } else {
                    projectTitle.classList.remove('border-red-500');
                    projectTitle.parentElement.querySelector('.text-validation-error').style.display = 'none';
                }
                
                // Validate date range
                if (!currentProject.checked && endDate.value && startDate.value) {
                    if (!validateDateRange(startDate.value, endDate.value)) {
                        dateError.style.display = 'block';
                        endDate.classList.add('border-red-500');
                        isValid = false;
                    } else {
                        dateError.style.display = 'none';
                        endDate.classList.remove('border-red-500');
                    }
                }
            });
            
            // Languages
            const langItems = document.querySelectorAll('.language-item');
            langItems.forEach(item => {
                const language = item.querySelector('input[name="language[]"]');
                const level = item.querySelector('select[name="languageLevel[]"]');
                
                // Validate text fields
                if (!containsLetters(language.value)) {
                    language.classList.add('border-red-500');
                    language.parentElement.querySelector('.text-validation-error').style.display = 'block';
                    isValid = false;
                } else {
                    language.classList.remove('border-red-500');
                    language.parentElement.querySelector('.text-validation-error').style.display = 'none';
                }
                
                if (!language.value || !level.value) {
                    isValid = false;
                    if (!language.value) language.classList.add('border-red-500');
                    if (!level.value) level.classList.add('border-red-500');
                } else {
                    if (language.value) language.classList.remove('border-red-500');
                    if (level.value) level.classList.remove('border-red-500');
                }
            });
            
            // Skills
            const skillItems = document.querySelectorAll('.skill-item');
            skillItems.forEach(item => {
                const skill = item.querySelector('input[name="skill[]"]');
                const level = item.querySelector('select[name="skillLevel[]"]');
                
                // Validate text fields
                if (!containsLetters(skill.value)) {
                    skill.classList.add('border-red-500');
                    skill.parentElement.querySelector('.text-validation-error').style.display = 'block';
                    isValid = false;
                } else {
                    skill.classList.remove('border-red-500');
                    skill.parentElement.querySelector('.text-validation-error').style.display = 'none';
                }
                
                if (!skill.value || !level.value) {
                    isValid = false;
                    if (!skill.value) skill.classList.add('border-red-500');
                    if (!level.value) level.classList.add('border-red-500');
                } else {
                    if (skill.value) skill.classList.remove('border-red-500');
                    if (level.value) level.classList.remove('border-red-500');
                }
            });
            
            if (!isValid) {
                alert('Please fill in all required fields and ensure all entries are correctly formatted.');
            }
            
            break;
    }
    
    return isValid;
}

// Check if text contains at least one letter (not just numbers or symbols)
function containsLetters(text) {
    return /[a-zA-Z\u00C0-\u00FF]/.test(text); // Regular Latin and extended Latin chars
}

// Validate that end date is after start date
function validateDateRange(startDate, endDate) {
    if (!startDate || !endDate) return true;
    
    const start = new Date(startDate);
    const end = new Date(endDate);
    
    return start < end;
}

function updateCities() {
    const country = document.getElementById('country').value;
    const citySelect = document.getElementById('city');
    
    // Clear existing options
    citySelect.innerHTML = '<option value="" disabled selected>Select a city</option>';
    
    if (country) {
        // Enable city select
        citySelect.disabled = false;
        
        // Add cities for selected country
        cityData[country].forEach(city => {
            const option = document.createElement('option');
            option.value = city;
            option.textContent = city;
            citySelect.appendChild(option);
        });
    } else {
        // Disable city select if no country selected
        citySelect.disabled = true;
    }
}

function previewProfilePic(event) {
    const file = event.target.files[0];
    if (file) {
        console.log('Profile picture selected:', file.name, 'Size:', file.size, 'Type:', file.type);
        const reader = new FileReader();
        reader.onload = function(e) {
            document.getElementById('profilePic').src = e.target.result;
            console.log('Preview loaded successfully');
        };
        reader.readAsDataURL(file);
    } else {
        console.error('No file selected for profile picture');
    }
}

function addExperience() {
    experienceCounter++;
    const container = document.getElementById('experienceContainer');
    
    const newExperience = document.createElement('div');
    newExperience.className = 'experience-item';
    newExperience.dataset.index = experienceCounter;
    
    newExperience.innerHTML = `
        <div class="absolute top-4 right-4">
            <i class="fas fa-trash delete-btn" onclick="removeExperience(this)"></i>
        </div>
        <h3 class="text-lg font-semibold mb-4">Experience #${experienceCounter}</h3>
        
        <div class="mb-4">
            <label class="block text-gray-700 mb-2 required-field">Job Title</label>
            <input type="text" name="jobTitle[]" class="w-full px-4 py-2 border border-gray-300 rounded-md" placeholder="e.g. Software Engineer" required>
            <div class="error-message text-validation-error">Title must contain letters, not just numbers or symbols</div>
        </div>
        
        <div class="mb-4">
            <label class="block text-gray-700 mb-2 required-field">Company</label>
            <input type="text" name="company[]" class="w-full px-4 py-2 border border-gray-300 rounded-md" placeholder="e.g. Google" required>
            <div class="error-message text-validation-error">Company name must contain letters, not just numbers or symbols</div>
        </div>
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
            <div>
                <label class="block text-gray-700 mb-2 required-field">Start Date</label>
                <input type="date" name="expStartDate[]" class="w-full px-4 py-2 border border-gray-300 rounded-md" required>
            </div>
            <div>
                <label class="block text-gray-700 mb-2">End Date</label>
                <input type="date" name="expEndDate[]" class="w-full px-4 py-2 border border-gray-300 rounded-md">
                <div class="error-message date-error">End date must be after start date</div>
            </div>
        </div>
        
        <div class="mb-4">
            <div class="flex items-center">
                <input type="checkbox" name="currentJob[]" class="mr-2 current-job-checkbox" onchange="toggleEndDate(this)">
                <label>I currently work here</label>
            </div>
        </div>
        
        <div class="mb-4">
            <label class="block text-gray-700 mb-2 required-field">Description</label>
            <textarea name="expDescription[]" rows="4" class="w-full px-4 py-2 border border-gray-300 rounded-md" placeholder="Describe your responsibilities and achievements..." required></textarea>
            <div class="word-count"><span class="exp-word-count">0</span> words (min 5 words required)</div>
            <div class="error-message word-count-error">Please write at least 5 words</div>
        </div>
    `;
    
    container.appendChild(newExperience);
    
    // Set up word counter for the new description
    const newTextarea = newExperience.querySelector('textarea[name="expDescription[]"]');
    const newWordCount = newExperience.querySelector('.exp-word-count');
    
    newTextarea.addEventListener('input', function() {
        newWordCount.textContent = getWordCount(this.value);
    });
    
    // Set up text field validation
    setupTextValidations(newExperience);
    
    // Set up date validation
    setupDateValidations(newExperience);
}

function removeExperience(btn) {
    const item = btn.closest('.experience-item');
    
    // Only allow removal if there's more than one experience
    if (document.querySelectorAll('.experience-item').length > 1) {
        item.remove();
    } else {
        alert('You must have at least one experience entry.');
    }
}

function addEducation() {
    educationCounter++;
    const container = document.getElementById('educationContainer');
    
    const newEducation = document.createElement('div');
    newEducation.className = 'education-item';
    newEducation.dataset.index = educationCounter;
    
    newEducation.innerHTML = `
        <div class="absolute top-4 right-4">
            <i class="fas fa-trash delete-btn" onclick="removeEducation(this)"></i>
        </div>
        <h3 class="text-lg font-semibold mb-4">Education #${educationCounter}</h3>
        
        <div class="mb-4">
            <label class="block text-gray-700 mb-2 required-field">School</label>
            <input type="text" name="school[]" class="w-full px-4 py-2 border border-gray-300 rounded-md" placeholder="e.g. Stanford University" required>
            <div class="error-message text-validation-error">School name must contain letters, not just numbers or symbols</div>
        </div>
        
        <div class="mb-4">
            <label class="block text-gray-700 mb-2 required-field">Degree</label>
            <select name="degree[]" class="w-full px-4 py-2 border border-gray-300 rounded-md" required>
                <option value="" disabled selected>Select a degree</option>
                <option value="High School Diploma">High School Diploma</option>
                <option value="Associate Degree">Associate Degree</option>
                <option value="Bachelor Degree">Bachelor Degree</option>
                <option value="Bachelor of Engineering">Bachelor of Engineering</option>
                <option value="Master of Science">Master of Science</option>
                <option value="Master of Arts">Master of Arts</option>
                <option value="Master of Business Administration">Master of Business Administration</option>
                <option value="PhD">PhD</option>
                <option value="Other">Other</option>
            </select>
        </div>
        
        <div class="mb-4">
            <label class="block text-gray-700 mb-2 required-field">Field of Study</label>
            <input type="text" name="fieldOfStudy[]" class="w-full px-4 py-2 border border-gray-300 rounded-md" placeholder="e.g. Computer Science, Civil Engineering" required>
            <div class="error-message text-validation-error">Field of study must contain letters, not just numbers or symbols</div>
        </div>
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
            <div>
                <label class="block text-gray-700 mb-2 required-field">Start Date</label>
                <input type="date" name="eduStartDate[]" class="w-full px-4 py-2 border border-gray-300 rounded-md" required>
            </div>
            <div>
                <label class="block text-gray-700 mb-2">End Date</label>
                <input type="date" name="eduEndDate[]" class="w-full px-4 py-2 border border-gray-300 rounded-md">
                <div class="error-message date-error">End date must be after start date</div>
            </div>
        </div>
        
        <div class="mb-4">
            <div class="flex items-center">
                <input type="checkbox" name="currentEducation[]" class="mr-2 current-edu-checkbox" onchange="toggleEduEndDate(this)">
                <label>I am currently studying here</label>
            </div>
        </div>
        
        <div class="mb-4">
            <label class="block text-gray-700 mb-2 required-field">Description</label>
            <textarea name="eduDescription[]" rows="4" class="w-full px-4 py-2 border border-gray-300 rounded-md" placeholder="Describe your studies, achievements, and activities..." required></textarea>
            <div class="word-count"><span class="edu-word-count">0</span> words (min 5 words required)</div>
            <div class="error-message word-count-error">Please write at least 5 words</div>
        </div>
    `;
    
    container.appendChild(newEducation);
    
    // Set up word counter for the new description
    const newTextarea = newEducation.querySelector('textarea[name="eduDescription[]"]');
    const newWordCount = newEducation.querySelector('.edu-word-count');
    
    newTextarea.addEventListener('input', function() {
        newWordCount.textContent = getWordCount(this.value);
    });
    
    // Set up text field validation
    setupTextValidations(newEducation);
    
    // Set up date validation
    setupDateValidations(newEducation);
}

function removeEducation(btn) {
    const item = btn.closest('.education-item');
    
    // Only allow removal if there's more than one education
    if (document.querySelectorAll('.education-item').length > 1) {
        item.remove();
    } else {
        alert('You must have at least one education entry.');
    }
}

function addOrganization() {
    organizationCounter++;
    const container = document.getElementById('organizationsContainer');
    
    const newOrganization = document.createElement('div');
    newOrganization.className = 'organization-item';
    newOrganization.dataset.index = organizationCounter;
    
    newOrganization.innerHTML = `
        <div class="absolute top-4 right-4">
            <i class="fas fa-trash delete-btn" onclick="removeOrganization(this)"></i>
        </div>
        <h4 class="font-medium mb-4">Organization #${organizationCounter}</h4>
        
        <div class="mb-4">
            <label class="block text-gray-700 mb-2 required-field">Organization Name</label>
            <input type="text" name="orgName[]" class="w-full px-4 py-2 border border-gray-300 rounded-md" placeholder="e.g. IEEE, Engineering Club" required>
            <div class="error-message text-validation-error">Organization name must contain letters, not just numbers or symbols</div>
        </div>
        
        <div class="mb-4">
            <label class="block text-gray-700 mb-2">Position</label>
            <input type="text" name="orgPosition[]" class="w-full px-4 py-2 border border-gray-300 rounded-md" placeholder="e.g. Vice President, Member">
            <div class="error-message text-validation-error">Position must contain letters, not just numbers or symbols</div>
        </div>
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
            <div>
                <label class="block text-gray-700 mb-2">Start Date</label>
                <input type="date" name="orgStartDate[]" class="w-full px-4 py-2 border border-gray-300 rounded-md">
            </div>
            <div>
                <label class="block text-gray-700 mb-2">End Date</label>
                <input type="date" name="orgEndDate[]" class="w-full px-4 py-2 border border-gray-300 rounded-md">
                <div class="error-message date-error">End date must be after start date</div>
            </div>
        </div>
        
        <div class="mb-4">
            <div class="flex items-center">
                <input type="checkbox" name="currentOrg[]" class="mr-2" onchange="toggleOrgEndDate(this)">
                <label>I am currently active here</label>
            </div>
        </div>
        
        <div class="mb-4">
            <label class="block text-gray-700 mb-2 required-field">Description</label>
            <textarea name="orgDescription[]" rows="3" class="w-full px-4 py-2 border border-gray-300 rounded-md" placeholder="Describe your role and contributions in this organization..." required></textarea>
            <div class="word-count"><span class="org-word-count">0</span> words (min 5 words required)</div>
            <div class="error-message word-count-error">Please write at least 5 words</div>
        </div>
    `;
    
    container.appendChild(newOrganization);
    
    // Set up word counter for the new description
    const newTextarea = newOrganization.querySelector('textarea[name="orgDescription[]"]');
    const newWordCount = newOrganization.querySelector('.org-word-count');
    
    newTextarea.addEventListener('input', function() {
        newWordCount.textContent = getWordCount(this.value);
    });
    
    // Set up text field validation
    setupTextValidations(newOrganization);
    
    // Set up date validation
    setupDateValidations(newOrganization);
}

function removeOrganization(btn) {
    const item = btn.closest('.organization-item');
    item.remove();
}

function addHonor() {
    honorCounter++;
    const container = document.getElementById('honorsContainer');
    
    const newHonor = document.createElement('div');
    newHonor.className = 'honors-item';
    newHonor.dataset.index = honorCounter;
    
    newHonor.innerHTML = `
        <div class="absolute top-4 right-4">
            <i class="fas fa-trash delete-btn" onclick="removeHonor(this)"></i>
        </div>
        <h4 class="font-medium mb-4">Honor/Award #${honorCounter}</h4>
        
        <div class="mb-4">
            <label class="block text-gray-700 mb-2 required-field">Honor/Award Name</label>
            <input type="text" name="honorName[]" class="w-full px-4 py-2 border border-gray-300 rounded-md" placeholder="e.g. Dean's List, Excellence Award" required>
            <div class="error-message text-validation-error">Honor name must contain letters, not just numbers or symbols</div>
        </div>
        
        <div class="mb-4">
            <label class="block text-gray-700 mb-2">Issuer</label>
            <input type="text" name="honorIssuer[]" class="w-full px-4 py-2 border border-gray-300 rounded-md" placeholder="e.g. University, Professional Association">
            <div class="error-message text-validation-error">Issuer must contain letters, not just numbers or symbols</div>
        </div>
        
        <div class="mb-4">
            <label class="block text-gray-700 mb-2">Date Received</label>
            <input type="date" name="honorDate[]" class="w-full px-4 py-2 border border-gray-300 rounded-md">
        </div>
        
        <div class="mb-4">
            <label class="block text-gray-700 mb-2 required-field">Description</label>
            <textarea name="honorDescription[]" rows="3" class="w-full px-4 py-2 border border-gray-300 rounded-md" placeholder="Describe the honor and achievement..." required></textarea>
            <div class="word-count"><span class="honor-word-count">0</span> words (min 5 words required)</div>
            <div class="error-message word-count-error">Please write at least 5 words</div>
        </div>
    `;
    
    container.appendChild(newHonor);
    
    // Set up word counter for the new description
    const newTextarea = newHonor.querySelector('textarea[name="honorDescription[]"]');
    const newWordCount = newHonor.querySelector('.honor-word-count');
    
    newTextarea.addEventListener('input', function() {
        newWordCount.textContent = getWordCount(this.value);
    });
    
    // Set up text field validation
    setupTextValidations(newHonor);
}

function removeHonor(btn) {
    const item = btn.closest('.honors-item');
    item.remove();
}

function addCourse() {
    courseCounter++;
    const container = document.getElementById('coursesContainer');
    
    const newCourse = document.createElement('div');
    newCourse.className = 'course-item';
    newCourse.dataset.index = courseCounter;
    
    newCourse.innerHTML = `
        <div class="absolute top-4 right-4">
            <i class="fas fa-trash delete-btn" onclick="removeCourse(this)"></i>
        </div>
        <h4 class="font-medium mb-4">Course #${courseCounter}</h4>
        
        <div class="mb-4">
            <label class="block text-gray-700 mb-2 required-field">Course Title</label>
            <input type="text" name="courseTitle[]" class="w-full px-4 py-2 border border-gray-300 rounded-md" placeholder="e.g. Machine Learning Course, Web Development" required>
            <div class="error-message text-validation-error">Course title must contain letters, not just numbers or symbols</div>
        </div>
        
        <div class="mb-4">
            <label class="block text-gray-700 mb-2">Provider/Institution</label>
            <input type="text" name="courseProvider[]" class="w-full px-4 py-2 border border-gray-300 rounded-md" placeholder="e.g. Coursera, edX, University">
            <div class="error-message text-validation-error">Provider must contain letters, not just numbers or symbols</div>
        </div>
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
            <div>
                <label class="block text-gray-700 mb-2">Start Date</label>
                <input type="date" name="courseStartDate[]" class="w-full px-4 py-2 border border-gray-300 rounded-md">
            </div>
            <div>
                <label class="block text-gray-700 mb-2">End Date</label>
                <input type="date" name="courseEndDate[]" class="w-full px-4 py-2 border border-gray-300 rounded-md">
                <div class="error-message date-error">End date must be after start date</div>
            </div>
        </div>
        
        <div class="mb-4">
            <div class="flex items-center">
                <input type="checkbox" name="currentCourse[]" class="mr-2" onchange="toggleCourseEndDate(this)">
                <label>I am currently taking this course</label>
            </div>
        </div>
        
        <div class="mb-4">
            <label class="block text-gray-700 mb-2 required-field">Description</label>
            <textarea name="courseDescription[]" rows="3" class="w-full px-4 py-2 border border-gray-300 rounded-md" placeholder="Describe what you learned in this course..." required></textarea>
            <div class="word-count"><span class="course-word-count">0</span> words (min 5 words required)</div>
            <div class="error-message word-count-error">Please write at least 5 words</div>
        </div>
    `;
    
    container.appendChild(newCourse);
    
    // Set up word counter for the new description
    const newTextarea = newCourse.querySelector('textarea[name="courseDescription[]"]');
    const newWordCount = newCourse.querySelector('.course-word-count');
    
    newTextarea.addEventListener('input', function() {
        newWordCount.textContent = getWordCount(this.value);
    });
    
    // Set up text field validation
    setupTextValidations(newCourse);
    
    // Set up date validation
    setupDateValidations(newCourse);
}

function removeCourse(btn) {
    const item = btn.closest('.course-item');
    item.remove();
}

function addProject() {
    projectCounter++;
    const container = document.getElementById('projectsContainer');
    
    const newProject = document.createElement('div');
    newProject.className = 'project-item';
    newProject.dataset.index = projectCounter;
    
    newProject.innerHTML = `
        <div class="absolute top-4 right-4">
            <i class="fas fa-trash delete-btn" onclick="removeProject(this)"></i>
        </div>
        <h4 class="font-medium mb-4">Project #${projectCounter}</h4>
        
        <div class="mb-4">
            <label class="block text-gray-700 mb-2 required-field">Project Title</label>
            <input type="text" name="projectTitle[]" class="w-full px-4 py-2 border border-gray-300 rounded-md" placeholder="e.g. Web Application, Research Project" required>
            <div class="error-message text-validation-error">Project title must contain letters, not just numbers or symbols</div>
        </div>
        
        <div class="mb-4">
            <label class="block text-gray-700 mb-2">Organization/Client</label>
            <input type="text" name="projectProvider[]" class="w-full px-4 py-2 border border-gray-300 rounded-md" placeholder="e.g. Self-initiated, Client name, Company">
            <div class="error-message text-validation-error">Organization must contain letters, not just numbers or symbols</div>
        </div>
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
            <div>
                <label class="block text-gray-700 mb-2">Start Date</label>
                <input type="date" name="projectStartDate[]" class="w-full px-4 py-2 border border-gray-300 rounded-md">
            </div>
            <div>
                <label class="block text-gray-700 mb-2">End Date</label>
                <input type="date" name="projectEndDate[]" class="w-full px-4 py-2 border border-gray-300 rounded-md">
                <div class="error-message date-error">End date must be after start date</div>
            </div>
        </div>
        
        <div class="mb-4">
            <div class="flex items-center">
                <input type="checkbox" name="currentProject[]" class="mr-2" onchange="toggleProjectEndDate(this)">
                <label>I am currently working on this project</label>
            </div>
        </div>
        
        <div class="mb-4">
            <label class="block text-gray-700 mb-2 required-field">Description</label>
            <textarea name="projectDescription[]" rows="3" class="w-full px-4 py-2 border border-gray-300 rounded-md" placeholder="Describe the project and what you accomplished..." required></textarea>
            <div class="word-count"><span class="project-word-count">0</span> words (min 5 words required)</div>
            <div class="error-message word-count-error">Please write at least 5 words</div>
        </div>
    `;
    
    container.appendChild(newProject);
    
    // Set up word counter for the new description
    const newTextarea = newProject.querySelector('textarea[name="projectDescription[]"]');
    const newWordCount = newProject.querySelector('.project-word-count');
    
    newTextarea.addEventListener('input', function() {
        newWordCount.textContent = getWordCount(this.value);
    });
    
    // Set up text field validation
    setupTextValidations(newProject);
    
    // Set up date validation
    setupDateValidations(newProject);
}

function removeProject(btn) {
    const item = btn.closest('.project-item');
    item.remove();
}

function addLanguage() {
    languageCounter++;
    const container = document.getElementById('languagesContainer');
    
    const newLanguage = document.createElement('div');
    newLanguage.className = 'language-item';
    newLanguage.dataset.index = languageCounter;
    
    newLanguage.innerHTML = `
        <div class="absolute top-4 right-4">
            <i class="fas fa-trash delete-btn" onclick="removeLanguage(this)"></i>
        </div>
        <h4 class="font-medium mb-4">Language #${languageCounter}</h4>
        
        <div class="mb-4">
            <label class="block text-gray-700 mb-2 required-field">Language</label>
            <input type="text" name="language[]" class="w-full px-4 py-2 border border-gray-300 rounded-md" placeholder="e.g. English, French, German" required>
            <div class="error-message text-validation-error">Language must contain letters, not just numbers or symbols</div>
        </div>
        
        <div class="mb-4">
            <label class="block text-gray-700 mb-2 required-field">Proficiency Level</label>
            <select name="languageLevel[]" class="w-full px-4 py-2 border border-gray-300 rounded-md" required>
                <option value="" disabled selected>Select proficiency level</option>
                <option value="Native">Native</option>
                <option value="Fluent">Fluent</option>
                <option value="Advanced">Advanced</option>
                <option value="Intermediate">Intermediate</option>
                <option value="Basic">Basic</option>
            </select>
        </div>
    `;
    
    container.appendChild(newLanguage);
    
    // Set up text field validation
    setupTextValidations(newLanguage);
    
    // Add event listener to check for duplicate languages
    const languageInput = newLanguage.querySelector('input[name="language[]"]');
    languageInput.addEventListener('blur', function() {
        checkDuplicateLanguage(this);
    });
}

function checkDuplicateLanguage(input) {
    const value = input.value.trim().toLowerCase();
    if (!value) return;
    
    const allLanguages = document.querySelectorAll('input[name="language[]"]');
    let count = 0;
    
    allLanguages.forEach(lang => {
        if (lang.value.trim().toLowerCase() === value) {
            count++;
        }
    });
    
    if (count > 1) {
        // Find or create an error message for duplicates
        let errorMsg = input.parentElement.querySelector('.duplicate-error');
        if (!errorMsg) {
            errorMsg = document.createElement('div');
            errorMsg.className = 'error-message duplicate-error';
            errorMsg.textContent = 'This language is already added. Please enter a different language.';
            input.parentElement.appendChild(errorMsg);
        }
        errorMsg.style.display = 'block';
        input.classList.add('border-red-500');
    } else {
        // Hide error message if it exists
        const errorMsg = input.parentElement.querySelector('.duplicate-error');
        if (errorMsg) {
            errorMsg.style.display = 'none';
        }
        input.classList.remove('border-red-500');
    }
}

function addSkill() {
    skillCounter++;
    const container = document.getElementById('skillsContainer');
    
    const newSkill = document.createElement('div');
    newSkill.className = 'skill-item';
    newSkill.dataset.index = skillCounter;
    
    newSkill.innerHTML = `
        <div class="absolute top-4 right-4">
            <i class="fas fa-trash delete-btn" onclick="removeSkill(this)"></i>
        </div>
        <h4 class="font-medium mb-4">Skill #${skillCounter}</h4>
        
        <div class="mb-4">
            <label class="block text-gray-700 mb-2 required-field">Skill</label>
            <input type="text" name="skill[]" class="w-full px-4 py-2 border border-gray-300 rounded-md" placeholder="e.g. JavaScript, Project Management, CAD Design" required>
            <div class="error-message text-validation-error">Skill must contain letters, not just numbers or symbols</div>
        </div>
        
        <div class="mb-4">
            <label class="block text-gray-700 mb-2 required-field">Proficiency Level</label>
            <select name="skillLevel[]" class="w-full px-4 py-2 border border-gray-300 rounded-md" required>
                <option value="" disabled selected>Select proficiency level</option>
                <option value="Expert">Expert</option>
                <option value="Advanced">Advanced</option>
                <option value="Intermediate">Intermediate</option>
                <option value="Beginner">Beginner</option>
            </select>
        </div>
    `;
    
    container.appendChild(newSkill);
    
    // Set up text field validation
    setupTextValidations(newSkill);
    
    // Add event listener to check for duplicate skills
    const skillInput = newSkill.querySelector('input[name="skill[]"]');
    skillInput.addEventListener('blur', function() {
        checkDuplicateSkill(this);
    });
}

function checkDuplicateSkill(input) {
    const value = input.value.trim().toLowerCase();
    if (!value) return;
    
    const allSkills = document.querySelectorAll('input[name="skill[]"]');
    let count = 0;
    
    allSkills.forEach(skill => {
        if (skill.value.trim().toLowerCase() === value) {
            count++;
        }
    });
    
    if (count > 1) {
        // Find or create an error message for duplicates
        let errorMsg = input.parentElement.querySelector('.duplicate-error');
        if (!errorMsg) {
            errorMsg = document.createElement('div');
            errorMsg.className = 'error-message duplicate-error';
            errorMsg.textContent = 'This skill is already added. Please enter a different skill.';
            input.parentElement.appendChild(errorMsg);
        }
        errorMsg.style.display = 'block';
        input.classList.add('border-red-500');
    } else {
        // Hide error message if it exists
        const errorMsg = input.parentElement.querySelector('.duplicate-error');
        if (errorMsg) {
            errorMsg.style.display = 'none';
        }
        input.classList.remove('border-red-500');
    }
}

function removeLanguage(btn) {
    const item = btn.closest('.language-item');
    
    // Only allow removal if there's more than one language
    if (document.querySelectorAll('.language-item').length > 1) {
        item.remove();
    } else {
        alert('You must have at least one language entry.');
    }
}

function removeSkill(btn) {
    const item = btn.closest('.skill-item');
    
    // Only allow removal if there's more than one skill
    if (document.querySelectorAll('.skill-item').length > 1) {
        item.remove();
    } else {
        alert('You must have at least one skill entry.');
    }
}

function toggleEndDate(checkbox) {
    const experienceItem = checkbox.closest('.experience-item');
    const endDateInput = experienceItem.querySelector('input[name="expEndDate[]"]');
    
    endDateInput.disabled = checkbox.checked;
    if (checkbox.checked) {
        endDateInput.value = '';
        experienceItem.querySelector('.date-error').style.display = 'none';
        endDateInput.classList.remove('border-red-500');
    }
}

function toggleEduEndDate(checkbox) {
    const educationItem = checkbox.closest('.education-item');
    const endDateInput = educationItem.querySelector('input[name="eduEndDate[]"]');
    
    endDateInput.disabled = checkbox.checked;
    if (checkbox.checked) {
        endDateInput.value = '';
        educationItem.querySelector('.date-error').style.display = 'none';
        endDateInput.classList.remove('border-red-500');
    }
}

function toggleOrgEndDate(checkbox) {
    const organizationItem = checkbox.closest('.organization-item');
    const endDateInput = organizationItem.querySelector('input[name="orgEndDate[]"]');
    
    endDateInput.disabled = checkbox.checked;
    if (checkbox.checked) {
        endDateInput.value = '';
        organizationItem.querySelector('.date-error').style.display = 'none';
        endDateInput.classList.remove('border-red-500');
    }
}

function toggleCourseEndDate(checkbox) {
    const courseItem = checkbox.closest('.course-item');
    const endDateInput = courseItem.querySelector('input[name="courseEndDate[]"]');
    
    endDateInput.disabled = checkbox.checked;
    if (checkbox.checked) {
        endDateInput.value = '';
        courseItem.querySelector('.date-error').style.display = 'none';
        endDateInput.classList.remove('border-red-500');
    }
}

function toggleProjectEndDate(checkbox) {
    const projectItem = checkbox.closest('.project-item');
    const endDateInput = projectItem.querySelector('input[name="projectEndDate[]"]');
    
    endDateInput.disabled = checkbox.checked;
    if (checkbox.checked) {
        endDateInput.value = '';
        projectItem.querySelector('.date-error').style.display = 'none';
        endDateInput.classList.remove('border-red-500');
    }
}

function getWordCount(text) {
    return text.trim().split(/\s+/).filter(word => word.length > 0).length;
}

function countWords(textarea, counterId) {
    const wordCount = getWordCount(textarea.value);
    document.getElementById(counterId).textContent = wordCount;
}

function setupTextareaWordCounters() {
    // Set up description field validation for all types of description fields
    const setupDescriptionField = (textarea, wordCountSpan, errorElement) => {
        // Update word count on input
        textarea.addEventListener('input', function() {
            const count = getWordCount(this.value);
            wordCountSpan.textContent = count;
            
            // Immediate validation
            if (count < 5 && this.value.trim() !== '') {
                this.classList.add('border-red-500');
                errorElement.style.display = 'block';
            } else {
                this.classList.remove('border-red-500');
                errorElement.style.display = 'none';
            }
        });
        
        // Validate when field loses focus
        textarea.addEventListener('blur', function() {
            const count = getWordCount(this.value);
            if (count < 5 && this.value.trim() !== '') {
                this.classList.add('border-red-500');
                errorElement.style.display = 'block';
            }
        });
    };
    
    // Experience descriptions
    document.querySelectorAll('textarea[name="expDescription[]"]').forEach(textarea => {
        const wordCountSpan = textarea.parentElement.querySelector('.exp-word-count');
        const errorElement = textarea.parentElement.querySelector('.word-count-error');
        setupDescriptionField(textarea, wordCountSpan, errorElement);
    });
    
    // Education descriptions
    document.querySelectorAll('textarea[name="eduDescription[]"]').forEach(textarea => {
        const wordCountSpan = textarea.parentElement.querySelector('.edu-word-count');
        const errorElement = textarea.parentElement.querySelector('.word-count-error');
        setupDescriptionField(textarea, wordCountSpan, errorElement);
    });
    
    // Organization descriptions
    document.querySelectorAll('textarea[name="orgDescription[]"]').forEach(textarea => {
        const wordCountSpan = textarea.parentElement.querySelector('.org-word-count');
        const errorElement = textarea.parentElement.querySelector('.word-count-error');
        setupDescriptionField(textarea, wordCountSpan, errorElement);
    });
    
    // Honor descriptions
    document.querySelectorAll('textarea[name="honorDescription[]"]').forEach(textarea => {
        const wordCountSpan = textarea.parentElement.querySelector('.honor-word-count');
        const errorElement = textarea.parentElement.querySelector('.word-count-error');
        setupDescriptionField(textarea, wordCountSpan, errorElement);
    });
    
    // Course descriptions
    document.querySelectorAll('textarea[name="courseDescription[]"]').forEach(textarea => {
        const wordCountSpan = textarea.parentElement.querySelector('.course-word-count');
        const errorElement = textarea.parentElement.querySelector('.word-count-error');
        setupDescriptionField(textarea, wordCountSpan, errorElement);
    });
    
    // Project descriptions
    document.querySelectorAll('textarea[name="projectDescription[]"]').forEach(textarea => {
        const wordCountSpan = textarea.parentElement.querySelector('.project-word-count');
        const errorElement = textarea.parentElement.querySelector('.word-count-error');
        setupDescriptionField(textarea, wordCountSpan, errorElement);
    });
    
    // About section
    const about = document.getElementById('about');
    const aboutWordCount = document.getElementById('aboutWordCount');
    const aboutError = document.getElementById('about-error');
    
    about.addEventListener('blur', function() {
        const count = getWordCount(this.value);
        if (count < 10 || count > 255) {
            this.classList.add('border-red-500');
            aboutError.style.display = 'block';
        }
    });
}

function setupFieldValidations() {
    // Position validation (must contain letters)
    document.getElementById('position').addEventListener('input', function() {
        const positionError = document.getElementById('position-error');
        if (!containsLetters(this.value)) {
            positionError.style.display = 'block';
            this.classList.add('border-red-500');
        } else {
            positionError.style.display = 'none';
            this.classList.remove('border-red-500');
        }
    });
    
    // Birthday validation (at least 18 years old)
    document.getElementById('birthday').addEventListener('change', function() {
        const birthdayError = document.getElementById('birthday-error');
        if (this.value) {
            const birthDate = new Date(this.value);
            const today = new Date();
            const age = today.getFullYear() - birthDate.getFullYear();
            const monthDiff = today.getMonth() - birthDate.getMonth();
            
            if (age < 18 || (age === 18 && monthDiff < 0) || (age === 18 && monthDiff === 0 && today.getDate() < birthDate.getDate())) {
                birthdayError.style.display = 'block';
                this.classList.add('border-red-500');
            } else {
                birthdayError.style.display = 'none';
                this.classList.remove('border-red-500');
            }
        }
    });
}

function setupTextValidations(container = null) {
    const selectors = [
        'input[name="position"]', 
        'input[name="jobTitle[]"]', 
        'input[name="company[]"]', 
        'input[name="school[]"]', 
        'input[name="fieldOfStudy[]"]',
        'input[name="orgName[]"]',
        'input[name="orgPosition[]"]',
        'input[name="honorName[]"]',
        'input[name="honorIssuer[]"]',
        'input[name="courseTitle[]"]', 
        'input[name="courseProvider[]"]',
        'input[name="projectTitle[]"]', 
        'input[name="projectProvider[]"]',
        'input[name="language[]"]',
        'input[name="skill[]"]'
    ];
    
    // If container is provided, only setup validation for elements within that container
    const root = container || document;
    
    selectors.forEach(selector => {
        root.querySelectorAll(selector).forEach(input => {
            if (input.getAttribute('data-validation-setup') === 'true') return;
            
            input.setAttribute('data-validation-setup', 'true');
            
            input.addEventListener('input', function() {
                const errorElement = this.parentElement.querySelector('.text-validation-error');
                if (!errorElement) return;
                
                if (!containsLetters(this.value) && this.value.trim() !== '') {
                    errorElement.style.display = 'block';
                    this.classList.add('border-red-500');
                } else {
                    errorElement.style.display = 'none';
                    this.classList.remove('border-red-500');
                }
            });
            
            // Initial check
            if (input.value.trim() !== '' && !containsLetters(input.value)) {
                const errorElement = input.parentElement.querySelector('.text-validation-error');
                if (errorElement) {
                    errorElement.style.display = 'block';
                    input.classList.add('border-red-500');
                }
            }
        });
    });
}

function setupDateValidations(container = null) {
    // Function to add date validation to a pair of date inputs
    function addDateValidation(startDateInput, endDateInput, errorElement) {
        if (!startDateInput || !endDateInput || !errorElement) return;
        
        // Skip if already set up
        if (startDateInput.getAttribute('data-date-validation') === 'true') return;
        
        startDateInput.setAttribute('data-date-validation', 'true');
        endDateInput.setAttribute('data-date-validation', 'true');
        
        function validateDates() {
            if (endDateInput.disabled) {
                errorElement.style.display = 'none';
                endDateInput.classList.remove('border-red-500');
                return true;
            }
            
            if (startDateInput.value && endDateInput.value) {
                if (!validateDateRange(startDateInput.value, endDateInput.value)) {
                    errorElement.style.display = 'block';
                    endDateInput.classList.add('border-red-500');
                    return false;
                } else {
                    errorElement.style.display = 'none';
                    endDateInput.classList.remove('border-red-500');
                    return true;
                }
            }
            return true;
        }
        
        startDateInput.addEventListener('change', validateDates);
        endDateInput.addEventListener('change', validateDates);
        
        // Initial validation
        validateDates();
    }
    
    const root = container || document;
    
    // Experience date validation
    root.querySelectorAll('.experience-item').forEach(item => {
        const startDateInput = item.querySelector('input[name="expStartDate[]"]');
        const endDateInput = item.querySelector('input[name="expEndDate[]"]');
        const dateError = item.querySelector('.date-error');
        
        addDateValidation(startDateInput, endDateInput, dateError);
    });
    
    // Education date validation
    root.querySelectorAll('.education-item').forEach(item => {
        const startDateInput = item.querySelector('input[name="eduStartDate[]"]');
        const endDateInput = item.querySelector('input[name="eduEndDate[]"]');
        const dateError = item.querySelector('.date-error');
        
        addDateValidation(startDateInput, endDateInput, dateError);
    });
    
    // Organization date validation
    root.querySelectorAll('.organization-item').forEach(item => {
        const startDateInput = item.querySelector('input[name="orgStartDate[]"]');
        const endDateInput = item.querySelector('input[name="orgEndDate[]"]');
        const dateError = item.querySelector('.date-error');
        
        addDateValidation(startDateInput, endDateInput, dateError);
    });
    
    // Course date validation
    root.querySelectorAll('.course-item').forEach(item => {
        const startDateInput = item.querySelector('input[name="courseStartDate[]"]');
        const endDateInput = item.querySelector('input[name="courseEndDate[]"]');
        const dateError = item.querySelector('.date-error');
        
        addDateValidation(startDateInput, endDateInput, dateError);
    });
    
    // Project date validation
    root.querySelectorAll('.project-item').forEach(item => {
        const startDateInput = item.querySelector('input[name="projectStartDate[]"]');
        const endDateInput = item.querySelector('input[name="projectEndDate[]"]');
        const dateError = item.querySelector('.date-error');
        
        addDateValidation(startDateInput, endDateInput, dateError);
    });
}