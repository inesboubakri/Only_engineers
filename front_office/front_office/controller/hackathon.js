// Hackathon data
const hackathonData = {
    mlh: {
        title: "Global Hack Week",
        company: "Major League Hacking",
        location: "Global",
        prize: "$10,000",
        type: "Online",
        duration: "48 Hours",
        description: "Join MLH's Global Hack Week for an exciting virtual hackathon experience. Work on cutting-edge projects, learn from industry experts, and compete for amazing prizes. This hackathon focuses on AI/ML innovations.",
        requirements: [
            "Basic programming knowledge",
            "Familiarity with AI/ML concepts",
            "Laptop with internet connection",
            "GitHub account",
            "Discord for communication"
        ],
        prizes: [
            "First Place: $5,000 + MLH Swag Pack",
            "Second Place: $3,000 + Premium API Credits",
            "Third Place: $2,000 + Developer Tools Bundle",
            "Best AI Implementation: Special Prize"
        ]
    },
    microsoft: {
        title: "Azure Cloud Challenge",
        company: "Microsoft",
        location: "Seattle, WA",
        prize: "$15,000",
        type: "Hybrid",
        duration: "1 Week",
        description: "Microsoft's Azure Cloud Challenge invites developers to build innovative cloud solutions. Participants will get hands-on experience with Azure services and mentorship from Microsoft engineers.",
        requirements: [
            "Experience with cloud computing",
            "Knowledge of Azure services",
            "Team of 2-4 members",
            "Valid Microsoft account",
            "Basic understanding of web services"
        ],
        prizes: [
            "Grand Prize: $8,000 + Azure Credits",
            "Runner-up: $5,000 + Surface Laptop",
            "Innovation Award: $2,000",
            "Best Student Team: Microsoft Internship Opportunity"
        ]
    },
    google: {
        title: "Android Dev Challenge",
        company: "Google",
        location: "Global",
        prize: "$8,000",
        type: "Online",
        duration: "24 Hours",
        description: "Create innovative Android applications in this fast-paced hackathon. Focus on building apps that solve real-world problems using the latest Android development tools and technologies.",
        requirements: [
            "Android development experience",
            "Knowledge of Kotlin or Java",
            "Android Studio installed",
            "Google Play Console account",
            "Material Design knowledge"
        ],
        prizes: [
            "Best App: $4,000 + Google Pixel",
            "Most Innovative: $2,000 + Google Home",
            "Best UI/UX: $2,000 + Pixel Buds",
            "Community Choice: Special Google Swag Box"
        ]
    }
};

// Theme toggle functionality
const themeToggle = document.getElementById('themeToggle');
const sunIcon = document.querySelector('.sun-icon');
const moonIcon = document.querySelector('.moon-icon');
const body = document.body;

// Check for saved theme preference or use preferred color scheme
const savedTheme = localStorage.getItem('theme');
if (savedTheme === 'dark' || (!savedTheme && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
    body.classList.add('dark-theme');
    sunIcon.style.display = 'none';
    moonIcon.style.display = 'block';
} else {
    body.classList.remove('dark-theme');
    sunIcon.style.display = 'block';
    moonIcon.style.display = 'none';
}

themeToggle.addEventListener('click', () => {
    // Toggle theme
    body.classList.toggle('dark-theme');
    
    // Update icons
    if (body.classList.contains('dark-theme')) {
        sunIcon.style.display = 'none';
        moonIcon.style.display = 'block';
        localStorage.setItem('theme', 'dark');
    } else {
        sunIcon.style.display = 'block';
        moonIcon.style.display = 'none';
        localStorage.setItem('theme', 'light');
    }
});

// View toggle (grid/list)
const viewToggle = document.querySelector('.view-toggle');
const gridIcon = document.querySelector('.grid-icon');
const listIcon = document.querySelector('.list-icon');
const jobCards = document.querySelector('.job-cards');

viewToggle.addEventListener('click', () => {
    jobCards.classList.toggle('list-view');
    
    if (jobCards.classList.contains('list-view')) {
        gridIcon.style.display = 'none';
        listIcon.style.display = 'block';
        localStorage.setItem('hackathonView', 'list');
    } else {
        gridIcon.style.display = 'block';
        listIcon.style.display = 'none';
        localStorage.setItem('hackathonView', 'grid');
    }
});

// Check for saved view preference
const savedView = localStorage.getItem('hackathonView');
if (savedView === 'list') {
    jobCards.classList.add('list-view');
    gridIcon.style.display = 'none';
    listIcon.style.display = 'block';
}

// Hackathon modal functionality
const modal = document.getElementById('hackathonModal');
const closeModal = document.querySelector('.close-modal');
const modalContent = document.querySelector('.modal-content');

// Close modal when clicking the X button
if (closeModal) {
    closeModal.addEventListener('click', () => {
        modal.style.display = 'none';
        document.body.style.overflow = 'auto';
    });
}

// Close modal when clicking outside the modal content
window.addEventListener('click', (event) => {
    if (event.target === modal) {
        modal.style.display = 'none';
        document.body.style.overflow = 'auto';
    }
});

// Function to open the modal with hackathon details
function openHackathonModal(hackathonId) {
    // Show loading state
    modal.style.display = 'flex';
    document.body.style.overflow = 'hidden';
    
    const modalBody = document.querySelector('.modal-body');
    modalBody.innerHTML = '<div class="loading">Loading...</div>';
    
    // Fetch hackathon details
    fetch(`../controller/get_hackathon_details.php?id=${hackathonId}`)
        .then(response => response.json())
        .then(data => {
            if (data.error) {
                modalBody.innerHTML = `<div class="error">${data.error}</div>`;
                return;
            }
            
            // Update modal content with hackathon details
            modalBody.innerHTML = `
                <div class="modal-header">
                    <div class="hackathon-logo">
                        <img src="${data.logo || '../assets/logo.png'}" alt="${data.title}">
                    </div>
                    <div class="hackathon-title">
                        <h2>${data.title}</h2>
                        <div class="hackathon-organizer">
                            <span>Organized by ${data.organizer}</span>
                        </div>
                        <div class="hackathon-location">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0118 0z"></path>
                                <circle cx="12" cy="10" r="3"></circle>
                            </svg>
                            <span>${data.location}</span>
                        </div>
                    </div>
                    <div class="hackathon-actions">
                        <button class="bookmark-btn" data-id="${data.hackathon_id}">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M19 21l-7-5-7 5V5a2 2 0 012-2h10a2 2 0 012 2z"></path>
                            </svg>
                            <span>Bookmark</span>
                        </button>
                        <button class="share-btn" data-id="${data.hackathon_id}">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <circle cx="18" cy="5" r="3"></circle>
                                <circle cx="6" cy="12" r="3"></circle>
                                <circle cx="18" cy="19" r="3"></circle>
                                <line x1="8.59" y1="13.51" x2="15.42" y2="17.49"></line>
                                <line x1="15.41" y1="6.51" x2="8.59" y2="10.49"></line>
                            </svg>
                            <span>Share</span>
                        </button>
                    </div>
                </div>
                
                <div class="modal-section">
                    <div class="section-header">
                        <h3>Hackathon Details</h3>
                    </div>
                    <div class="hackathon-details">
                        <div class="detail-item">
                            <span class="detail-label">Date</span>
                            <span class="detail-value">${data.formatted_date}</span>
                        </div>
                        <div class="detail-item">
                            <span class="detail-label">Type</span>
                            <span class="detail-value">${data.type}</span>
                        </div>
                        <div class="detail-item">
                            <span class="detail-label">Duration</span>
                            <span class="detail-value">${data.duration}</span>
                        </div>
                        <div class="detail-item">
                            <span class="detail-label">Prize Pool</span>
                            <span class="detail-value">${data.prize_pool}</span>
                        </div>
                        <div class="detail-item">
                            <span class="detail-label">Team Size</span>
                            <span class="detail-value">${data.team_size || 'Any'}</span>
                        </div>
                    </div>
                </div>
                
                <div class="modal-section">
                    <div class="section-header">
                        <h3>About</h3>
                    </div>
                    <div class="hackathon-description">
                        <p>${data.description}</p>
                    </div>
                </div>
                
                <div class="location-section">
                    <h2 class="section-title">Location</h2>
                    <div id="map-container" data-latitude="${data.latitude || ''}" data-longitude="${data.longitude || ''}"></div>
                </div>
                
                <div class="modal-section">
                    <div class="section-header">
                        <h3>Requirements</h3>
                    </div>
                    <div class="requirements-list">
                        <ul>
                            ${data.requirements_array.map(req => `<li>${req.trim()}</li>`).join('')}
                        </ul>
                    </div>
                </div>
                
                <div class="modal-section">
                    <div class="section-header">
                        <h3>Prizes</h3>
                    </div>
                    <div class="prizes-list">
                        <ul>
                            ${data.prizes_array.map(prize => `<li>${prize.trim()}</li>`).join('')}
                        </ul>
                    </div>
                </div>
                
                <div class="modal-section">
                    <div class="section-header">
                        <h3>Similar Hackathons</h3>
                    </div>
                    <div class="similar-hackathons">
                        ${data.similar_hackathons.map(h => `
                            <div class="similar-hackathon" onclick="openHackathonModal(${h.hackathon_id})">
                                <div class="similar-hackathon-logo">
                                    <img src="${h.logo || '../assets/logo.png'}" alt="${h.title}">
                                </div>
                                <div class="similar-hackathon-info">
                                    <h4>${h.title}</h4>
                                    <span>${h.organizer}</span>
                                    <div class="similar-hackathon-location">
                                        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                            <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0118 0z"></path>
                                            <circle cx="12" cy="10" r="3"></circle>
                                        </svg>
                                        <span>${h.location}</span>
                                    </div>
                                </div>
                            </div>
                        `).join('')}
                    </div>
                </div>
                
                <div class="modal-footer">
                    <a href="${data.registration_link}" class="register-btn" target="_blank">Register Now</a>
                </div>
            `;
            
            // Add event listeners for bookmark and share buttons
            setupModalEventListeners();
            
            // Initialize map if coordinates are available
            if (data.latitude && data.longitude) {
                // Delay slightly to ensure DOM is ready
                setTimeout(() => {
                    displayLocationMap(data.latitude, data.longitude, 'map-container');
                }, 100);
            }
        })
        .catch(error => {
            modalBody.innerHTML = `<div class="error">Error fetching hackathon details: ${error.message}</div>`;
        });
}

// Function to set up event listeners for elements in the modal
function setupModalEventListeners() {
    // Bookmark functionality
    const bookmarkBtns = document.querySelectorAll('.bookmark-btn');
    bookmarkBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            const hackathonId = this.getAttribute('data-id');
            toggleBookmark(hackathonId, this);
        });
    });
    
    // Share functionality
    const shareBtns = document.querySelectorAll('.share-btn');
    shareBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            const hackathonId = this.getAttribute('data-id');
            shareHackathon(hackathonId);
        });
    });
}

// Function to toggle bookmark
function toggleBookmark(hackathonId, button) {
    // Get existing bookmarks from local storage
    let bookmarks = JSON.parse(localStorage.getItem('hackathonBookmarks') || '[]');
    
    // Check if already bookmarked
    const index = bookmarks.indexOf(hackathonId);
    
    if (index === -1) {
        // Add to bookmarks
        bookmarks.push(hackathonId);
        button.classList.add('active');
        button.querySelector('span').textContent = 'Bookmarked';
    } else {
        // Remove from bookmarks
        bookmarks.splice(index, 1);
        button.classList.remove('active');
        button.querySelector('span').textContent = 'Bookmark';
    }
    
    // Save updated bookmarks
    localStorage.setItem('hackathonBookmarks', JSON.stringify(bookmarks));
}

// Function to share hackathon
function shareHackathon(hackathonId) {
    const url = `${window.location.origin}${window.location.pathname}?id=${hackathonId}`;
    
    // Check if Web Share API is supported
    if (navigator.share) {
        navigator.share({
            title: 'Check out this hackathon!',
            url: url
        })
        .catch(error => console.log('Error sharing:', error));
    } else {
        // Fallback - copy to clipboard
        navigator.clipboard.writeText(url)
            .then(() => {
                alert('Link copied to clipboard!');
            })
            .catch(err => {
                console.error('Could not copy text: ', err);
            });
    }
}

// Add click event to hackathon cards to open modal
document.addEventListener('DOMContentLoaded', function() {
    const hackathonCards = document.querySelectorAll('.job-card');
    
    hackathonCards.forEach(card => {
        card.addEventListener('click', function() {
            const hackathonId = this.getAttribute('data-id');
            openHackathonModal(hackathonId);
        });
    });
    
    // Check URL for hackathon ID parameter
    const urlParams = new URLSearchParams(window.location.search);
    const hackathonId = urlParams.get('id');
    
    if (hackathonId) {
        openHackathonModal(hackathonId);
    }
});

// Filter functionality
const filterRadios = document.querySelectorAll('.filter-options input[type="radio"]');
const searchInput = document.querySelector('.search-bar input');
const clearAllBtn = document.querySelector('.clear-all');

// Apply filters when radio buttons are clicked
filterRadios.forEach(radio => {
    radio.addEventListener('change', applyFilters);
});

// Apply filters when search input changes
searchInput.addEventListener('input', applyFilters);

// Clear all filters
clearAllBtn.addEventListener('click', () => {
    // Uncheck all radio buttons
    filterRadios.forEach(radio => {
        radio.checked = false;
    });
    
    // Clear search input
    searchInput.value = '';
    
    // Apply filters (resets to show all)
    applyFilters();
});

// Function to apply filters
function applyFilters() {
    const searchTerm = searchInput.value.toLowerCase();
    const typeFilter = document.querySelector('input[name="type"]:checked')?.parentElement.querySelector('.label-text').textContent;
    const prizeFilter = document.querySelector('input[name="prize"]:checked')?.parentElement.querySelector('.label-text').textContent;
    const durationFilter = document.querySelector('input[name="duration"]:checked')?.parentElement.querySelector('.label-text').textContent;
    
    const hackathonCards = document.querySelectorAll('.job-card');
    
    hackathonCards.forEach(card => {
        let visible = true;
        
        // Search term filter
        if (searchTerm) {
            const titleElement = card.querySelector('.job-title h3');
            const organizerElement = card.querySelector('.company-name');
            const locationElement = card.querySelector('.job-location');
            
            const title = titleElement ? titleElement.textContent.toLowerCase() : '';
            const organizer = organizerElement ? organizerElement.textContent.toLowerCase() : '';
            const location = locationElement ? locationElement.textContent.toLowerCase() : '';
            
            if (!title.includes(searchTerm) && !organizer.includes(searchTerm) && !location.includes(searchTerm)) {
                visible = false;
            }
        }
        
        // Type filter
        if (typeFilter && visible) {
            const typeElement = card.querySelector('.job-tag:nth-child(1)');
            const type = typeElement ? typeElement.textContent.trim() : '';
            
            if (type !== typeFilter.trim()) {
                visible = false;
            }
        }
        
        // Prize filter
        if (prizeFilter && visible) {
            const prizeElement = card.querySelector('.prize-pool');
            const prize = prizeElement ? prizeElement.textContent.trim() : '';
            
            // Check if prize is in the range
            if (prizeFilter.includes('-')) {
                const [min, max] = prizeFilter.replace(/[^0-9-]/g, '').split('-').map(Number);
                const prizeValue = parseInt(prize.replace(/[^0-9]/g, ''));
                
                if (prizeValue < min * 1000 || prizeValue > max * 1000) {
                    visible = false;
                }
            } else if (prizeFilter.includes('+')) {
                const min = parseInt(prizeFilter.replace(/[^0-9]/g, ''));
                const prizeValue = parseInt(prize.replace(/[^0-9]/g, ''));
                
                if (prizeValue < min * 1000) {
                    visible = false;
                }
            }
        }
        
        // Duration filter
        if (durationFilter && visible) {
            const durationElement = card.querySelector('.duration-tag');
            const duration = durationElement ? durationElement.textContent.trim() : '';
            
            if (duration !== durationFilter.trim()) {
                visible = false;
            }
        }
        
        // Show or hide the card
        card.style.display = visible ? 'flex' : 'none';
    });
}

/**
 * Hackathon Controller
 * Handles hackathon-related functionality in the front office
 */

// Track if the modal is open
let hackathonModalOpen = false;

// Initialize hackathon form functionality when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    // Set up the "Add a new hackathon" button in the promo card if it exists
    const addHackathonBtn = document.querySelector('.add-hackathon-btn');
    if (addHackathonBtn) {
        addHackathonBtn.addEventListener('click', function(e) {
            e.preventDefault();
            openHackathonFormModal();
        });
    }

    // Set up the existing hackathon request form if it's already in the DOM
    setupHackathonFormValidation();
});

// Open the hackathon form modal
function openHackathonFormModal() {
    console.log('Opening hackathon form modal');
    
    // Don't open multiple modals
    if (hackathonModalOpen) return;
    hackathonModalOpen = true;
    
    // Show the modal if it already exists in DOM
    const existingModal = document.getElementById('hackathon-request-modal');
    if (existingModal) {
        existingModal.style.display = 'flex';
        return;
    }
}

// Setup form validation and submission
function setupHackathonFormValidation() {
    const requestForm = document.getElementById('hackathon-request-form');
    if (!requestForm) return;

    // Image preview functionality
    const imageInput = document.getElementById('hackathon-image');
    const imagePreview = document.getElementById('image-preview');
    
    if (imageInput && imagePreview) {
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

    // Close modal handlers
    const closeModalBtns = document.querySelectorAll('.close-modal, .cancel-btn');
    closeModalBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            const modal = document.getElementById('hackathon-request-modal');
            if (modal) {
                modal.style.display = 'none';
                hackathonModalOpen = false;
            }
        });
    });
    
    // Form validation and submission
    const submitButton = document.getElementById('submit-hackathon');
    if (submitButton) {
        requestForm.addEventListener('submit', function(e) {
            e.preventDefault();
            if (validateHackathonForm()) {
                submitHackathonForm();
            }
        });
        
        submitButton.addEventListener('click', function(e) {
            e.preventDefault();
            if (validateHackathonForm()) {
                submitHackathonForm();
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
    const form = document.getElementById('hackathon-request-form');
    const formData = new FormData(form);
    
    // Show loading state
    const submitButton = document.getElementById('submit-hackathon');
    const originalText = submitButton.textContent;
    submitButton.disabled = true;
    submitButton.textContent = 'Submitting...';
    
    // Submit form via AJAX
    fetch(form.action, {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Show success message
            showToast('Hackathon request submitted successfully!');
            
            // Close modal
            const modal = document.getElementById('hackathon-request-modal');
            if (modal) {
                modal.style.display = 'none';
                hackathonModalOpen = false;
            }
            
            // Reset form
            form.reset();
            document.getElementById('image-preview').style.display = 'none';
            
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

// Function to show a toast notification
function showToast(message) {
    // Check if there's an existing toast container
    let toastContainer = document.querySelector('.toast-container');
    
    // Create toast container if it doesn't exist
    if (!toastContainer) {
        toastContainer = document.createElement('div');
        toastContainer.className = 'toast-container';
        document.body.appendChild(toastContainer);
    }
    
    // Create toast element
    const toast = document.createElement('div');
    toast.className = 'toast';
    toast.textContent = message;
    
    // Add toast to container
    toastContainer.appendChild(toast);
    
    // Show toast
    setTimeout(() => {
        toast.classList.add('show');
    }, 10);
    
    // Hide toast after 3 seconds
    setTimeout(() => {
        toast.classList.remove('show');
        setTimeout(() => {
            toast.remove();
            
            // Remove container if it's empty
            if (toastContainer.children.length === 0) {
                toastContainer.remove();
            }
        }, 300);
    }, 3000);
}