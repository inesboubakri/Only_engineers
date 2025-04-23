// Theme Toggle Functionality
document.addEventListener('DOMContentLoaded', () => {
    const themeToggle = document.getElementById('themeToggle');
    const sunIcon = themeToggle.querySelector('.sun-icon');
    const moonIcon = themeToggle.querySelector('.moon-icon');
    const root = document.documentElement;

    // Check for saved theme preference
    const savedTheme = localStorage.getItem('theme');
    if (savedTheme === 'dark') {
        root.setAttribute('data-theme', 'dark');
        sunIcon.style.display = 'none';
        moonIcon.style.display = 'block';
    }

    // Theme toggle event listener
    themeToggle.addEventListener('click', () => {
        const isDark = root.getAttribute('data-theme') === 'dark';
        
        if (isDark) {
            root.removeAttribute('data-theme');
        } else {
            root.setAttribute('data-theme', 'dark');
        }
        
        // Toggle icons
        sunIcon.style.display = !isDark ? 'none' : 'block';
        moonIcon.style.display = !isDark ? 'block' : 'none';
        
        // Save preference
        localStorage.setItem('theme', !isDark ? 'dark' : 'light');
    });
});

// Modal functionality
document.addEventListener('DOMContentLoaded', () => {
    const modal = document.getElementById('hackathonDetailsModal');
    const detailButtons = document.querySelectorAll('.details');
    const closeModal = document.querySelector('.close-modal');
    const modalBookmarkBtn = modal.querySelector('.bookmark-btn');

    // Store bookmarked state for each hackathon
    const bookmarkedHackathons = new Set();

    function populateModal(hackathonId) {
        const data = hackathonData[hackathonId];
        if (!data) return;

        // Update modal content
        modal.querySelector('.job-title').textContent = data.title;
        modal.querySelector('.company-name').textContent = data.company;
        modal.querySelector('.company-location').textContent = `📍 ${data.location}`;
        modal.querySelector('.job-description').textContent = data.description;

        // Update meta information
        const metaContainer = modal.querySelector('.job-meta');
        metaContainer.innerHTML = `
            <span class="job-type">🎯 ${data.type}</span>
            <span class="job-level">⏱️ ${data.duration}</span>
            <span class="job-location">🌍 ${data.location}</span>
            <span class="job-salary">💰 ${data.prize}</span>
        `;

        // Update requirements
        const qualList = modal.querySelector('.qualifications-list');
        qualList.innerHTML = data.requirements.map(req => `<li>💡 ${req}</li>`).join('');

        // Update prizes
        const prizeList = modal.querySelector('.responsibilities-list');
        prizeList.innerHTML = data.prizes.map(prize => `<li>🏆 ${prize}</li>`).join('');

        // Set company logo
        const logo = modal.querySelector('.company-logo');
        logo.src = `${hackathonId.toLowerCase()}-logo.png`;
        logo.alt = `${data.company} logo`;

        // Update bookmark button state
        modalBookmarkBtn.classList.toggle('active', bookmarkedHackathons.has(hackathonId));

        // Store the current hackathon ID on the modal
        modal.dataset.currentHackathon = hackathonId;

        // Show modal
        modal.classList.add('active');
    }

    // Event listeners for detail buttons
    detailButtons.forEach(button => {
        button.addEventListener('click', (e) => {
            const hackathonCard = e.target.closest('.job-card');
            const hackathonContent = hackathonCard.querySelector('.job-content');
            const company = Array.from(hackathonContent.classList)
                .find(cls => Object.keys(hackathonData).includes(cls));
            
            if (company) {
                populateModal(company);
            }
        });
    });

    // Bookmark button click handler
    if (modalBookmarkBtn) {
        modalBookmarkBtn.addEventListener('click', () => {
            const currentHackathon = modal.dataset.currentHackathon;
            if (currentHackathon) {
                if (bookmarkedHackathons.has(currentHackathon)) {
                    bookmarkedHackathons.delete(currentHackathon);
                    modalBookmarkBtn.classList.remove('active');
                } else {
                    bookmarkedHackathons.add(currentHackathon);
                    modalBookmarkBtn.classList.add('active');
                }

                // Also update the bookmark button in the hackathon card
                const hackathonCard = document.querySelector(`.job-content.${currentHackathon}`);
                if (hackathonCard) {
                    const cardBookmarkBtn = hackathonCard.querySelector('.bookmark');
                    cardBookmarkBtn.classList.toggle('active', bookmarkedHackathons.has(currentHackathon));
                }
            }
        });
    }

    // Close modal when clicking the close button
    if (closeModal) {
        closeModal.addEventListener('click', () => {
            modal.classList.remove('active');
        });
    }

    // Close modal when clicking outside
    modal.addEventListener('click', (e) => {
        if (e.target === modal) {
            modal.classList.remove('active');
        }
    });

    // Share button functionality
    const shareButton = document.querySelector('.share-btn');
    if (shareButton) {
        shareButton.addEventListener('click', () => {
            const hackathonTitle = modal.querySelector('.job-title').textContent;
            const company = modal.querySelector('.company-name').textContent;
            
            const shareText = `Check out this ${hackathonTitle} hackathon by ${company}!`;
            
            if (navigator.share) {
                navigator.share({
                    title: `${hackathonTitle} by ${company}`,
                    text: shareText,
                    url: window.location.href
                });
            } else {
                navigator.clipboard.writeText(shareText + ' ' + window.location.href)
                    .then(() => alert('Link copied to clipboard!'))
                    .catch(err => console.error('Failed to copy:', err));
            }
        });
    }

    // View toggle functionality
    const viewToggleButton = document.querySelector('.view-toggle');
    const jobCardsContainer = document.querySelector('.job-cards');
    const gridIcon = document.querySelector('.grid-icon');
    const listIcon = document.querySelector('.list-icon');

    if (viewToggleButton && jobCardsContainer && gridIcon && listIcon) {
        function updateViewToggleState() {
            const isListLayout = jobCardsContainer.classList.contains('list-layout');
            gridIcon.style.display = isListLayout ? 'none' : 'block';
            listIcon.style.display = isListLayout ? 'block' : 'none';
        }

        // Set initial state
        updateViewToggleState();

        // Add click event listener
        viewToggleButton.addEventListener('click', () => {
            jobCardsContainer.classList.toggle('list-layout');
            updateViewToggleState();
        });
    }
});

// Main Hackathon Management Functions
document.addEventListener('DOMContentLoaded', function() {
    let hackathonStats = {};
    const searchInput = document.getElementById('searchInput');
    const sourceFilters = document.querySelectorAll('input[name="source"]');
    const prizeFilters = document.querySelectorAll('input[name="prize"]');
    const durationFilters = document.querySelectorAll('input[name="duration"]');
    const clearAllButton = document.querySelector('.clear-all');
    const addHackathonForm = document.getElementById('addHackathonForm');
    const editHackathonForm = document.getElementById('editHackathonForm');
    const addModal = document.getElementById('addHackathonModal');
    const editModal = document.getElementById('editHackathonModal');
    
    // Current user ID - should be set after login
    let currentUserId = null;

    // Initialize the application
    function init() {
        loadUserSession();
        loadHackathons();
        setupEventListeners();
    }

    // Load user session data
    async function loadUserSession() {
        try {
            const response = await fetch('../model/get_current_user.php', {
                credentials: 'include'
            });
            const data = await response.json();
            if (data.success && data.user_id) {
                currentUserId = data.user_id;
                sessionStorage.setItem('user_id', currentUserId);
            } else {
                console.warn('No user ID found in session');
                window.location.href = 'signin.html';
            }
        } catch (error) {
            console.error('Error loading user session:', error);
        }
    }

    // Load hackathons and update counters
    function loadHackathons() {
        fetch('../controller/get_hackathons.php')
            .then(response => response.json())
            .then(result => {
                if (result.success) {
                    window.hackathons = result.data;
                    hackathonStats = result.stats;
                    updateHackathonCount(window.hackathons.length);
                    displayHackathons(window.hackathons);
                    updateFilterCounts(hackathonStats);
                }
            })
            .catch(error => console.error('Error:', error));
    }

    // Update filter counters
    function updateFilterCounts(stats) {
        // Source counts
        document.querySelector('input[value="added-by-me"] + .label-text + .count').textContent = stats.sources['added-by-me'];
        document.querySelector('input[value="added-by-others"] + .label-text + .count').textContent = stats.sources['added-by-others'];

        // Prize counts
        Object.entries(stats.prizes).forEach(([range, count]) => {
            document.querySelector(`input[value="${range}"] + .label-text + .count`).textContent = count;
        });

        // Duration counts
        Object.entries(stats.durations).forEach(([duration, count]) => {
            document.querySelector(`input[value="${duration}"] + .label-text + .count`).textContent = count;
        });
    }

    // Filter hackathons based on selected criteria
    function filterHackathons() {
        const searchTerm = searchInput.value.toLowerCase();
        const selectedSource = document.querySelector('input[name="source"]:checked')?.value;
        const selectedPrize = document.querySelector('input[name="prize"]:checked')?.value;
        const selectedDuration = document.querySelector('input[name="duration"]:checked')?.value;

        let filtered = [...window.hackathons];

        // Search filter
        if (searchTerm) {
            filtered = filtered.filter(h => 
                h.name.toLowerCase().includes(searchTerm) ||
                h.description.toLowerCase().includes(searchTerm) ||
                (h.required_skills && h.required_skills.toLowerCase().includes(searchTerm))
            );
        }

        // Source filter
        if (selectedSource && currentUserId) {
            filtered = filtered.filter(h => 
                selectedSource === 'added-by-me' ? 
                String(h.created_by) === String(currentUserId) : 
                String(h.created_by) !== String(currentUserId)
            );
        }

        // Prize filter
        if (selectedPrize) {
            const prizeRanges = {
                '1k-5k': h => h.prize_pool >= 1000 && h.prize_pool < 5000,
                '5k-10k': h => h.prize_pool >= 5000 && h.prize_pool < 10000,
                '10k-plus': h => h.prize_pool >= 10000
            };
            filtered = filtered.filter(prizeRanges[selectedPrize]);
        }

        // Duration filter
        if (selectedDuration) {
            const durationRanges = {
                '24h': h => getDurationHours(h.start_date, h.end_date) <= 24,
                '48h': h => getDurationHours(h.start_date, h.end_date) <= 48 && getDurationHours(h.start_date, h.end_date) > 24,
                '1w': h => getDurationHours(h.start_date, h.end_date) > 48
            };
            filtered = filtered.filter(durationRanges[selectedDuration]);
        }

        displayHackathons(filtered);
        updateHackathonCount(filtered.length);
    }

    // Display hackathons in the UI
    function displayHackathons(hackathons) {
        const jobCards = document.querySelector('.job-cards');
        // Clear existing cards except the promo card
        const promoCard = jobCards.querySelector('.promo-card');
        jobCards.innerHTML = '';
        if (promoCard) {
            jobCards.appendChild(promoCard);
        }

        hackathons.forEach(hackathon => {
            const card = createHackathonCard(hackathon);
            jobCards.insertBefore(card, promoCard);
        });
    }

    // Create a hackathon card element
    function createHackathonCard(hackathon) {
        const card = document.createElement('div');
        card.className = 'job-card';
        
        const startDate = new Date(hackathon.start_date);
        const endDate = new Date(hackathon.end_date);
        const formattedDate = `${startDate.toLocaleDateString()} - ${endDate.toLocaleDateString()}`;
        
        card.innerHTML = `
            <div class="job-content">
                <div class="card-header">
                    <span class="date">${formattedDate}</span>
                    <button class="bookmark">
                        <svg class="bookmark-outline" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M19 21l-7-5-7 5V5a2 2 0 0 1 2-2h10a2 2 0 0 1 2 2z"></path>
                        </svg>
                        <svg class="bookmark-filled" width="16" height="16" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M19 21l-7-5-7 5V5a2 2 0 0 1 2-2h10a2 2 0 0 1 2 2z"></path>
                        </svg>
                    </button>
                </div>
                <h3>${hackathon.organizer}</h3>
                <div class="job-title">
                    <h4>${hackathon.name}</h4>
                    <img src="${hackathon.image ? '../../../back_office/uploads/hackathon_images/' + hackathon.image : '../ressources/cybersecurity.png'}" alt="${hackathon.name}" class="company-logo">
                </div>
                <div class="tags">
                    <span>${hackathon.location}</span>
                    <span>${getDuration(startDate, endDate)}</span>
                    ${hackathon.required_skills ? `<span>${hackathon.required_skills}</span>` : ''}
                </div>
            </div>
            <div class="card-footer">
                <div class="job-details">
                    <div class="salary">${hackathon.prize_pool ? '$' + hackathon.prize_pool : 'Prize TBA'}</div>
                    <div class="location">${hackathon.location}</div>
                </div>
                <button class="details" data-hackathon-id="${hackathon.id}">Details</button>
            </div>
        `;

        // Add event listener for details button
        const detailsBtn = card.querySelector('.details');
        detailsBtn.addEventListener('click', () => showHackathonDetails(hackathon));

        return card;
    }

    // Update hackathon count display
    function updateHackathonCount(count) {
        const countSpan = document.querySelector('.jobs-header .count');
        if (countSpan) {
            countSpan.textContent = count;
        }
    }

    // Setup event listeners
    function setupEventListeners() {
        // Search and filter events
        searchInput.addEventListener('input', filterHackathons);
        
        sourceFilters.forEach(filter => {
            filter.addEventListener('change', filterHackathons);
        });

        prizeFilters.forEach(filter => {
            filter.addEventListener('change', filterHackathons);
        });

        durationFilters.forEach(filter => {
            filter.addEventListener('change', filterHackathons);
        });

        clearAllButton.addEventListener('click', () => {
            searchInput.value = '';
            sourceFilters.forEach(filter => filter.checked = false);
            prizeFilters.forEach(filter => filter.checked = false);
            durationFilters.forEach(filter => filter.checked = false);
            filterHackathons();
        });

        // Add hackathon form submission
        addHackathonForm.addEventListener('submit', async function(e) {
            e.preventDefault();
            
            if (!currentUserId) {
                showToast('You must be logged in to add a hackathon', 'error');
                return;
            }

            if (validateForm(this)) {
                const formData = new FormData(this);
                
                try {
                    const response = await fetch('http://localhost/projet_web/back_office/controllers/hackathonsController.php', {
                        method: 'POST',
                        body: formData,
                        credentials: 'include'
                    });
                    
                    const result = await response.json();
                    console.log('Server response:', result);
                    
                    if (result.success) {
                        closeModal(addModal);
                        loadHackathons();
                        showToast('Hackathon added successfully!', 'success');
                        this.reset();
                    } else {
                        throw new Error(result.message || 'Failed to add hackathon');
                    }
                } catch (error) {
                    console.error('Error:', error);
                    showToast(error.message, 'error');
                }
            }
        });

        // Edit hackathon form submission
        editHackathonForm.addEventListener('submit', async function(e) {
            e.preventDefault();
            
            if (!currentUserId) {
                showToast('You must be logged in to edit a hackathon', 'error');
                return;
            }

            if (validateForm(this, true)) {
                const formData = new FormData(this);
                const hackathonId = formData.get('id');

                try {
                    const response = await fetch(`http://localhost/projet_web/back_office/controllers/hackathonsController.php?id=${hackathonId}`, {
                        method: 'POST',
                        body: formData,
                        credentials: 'include'
                    });
                    
                    const result = await response.json();
                    console.log('Server response:', result);
                    
                    if (result.success) {
                        closeModal(editModal);
                        loadHackathons();
                        showToast('Hackathon updated successfully!', 'success');
                    } else {
                        throw new Error(result.message || 'Failed to update hackathon');
                    }
                } catch (error) {
                    console.error('Error:', error);
                    showToast('Error updating hackathon: ' + error.message, 'error');
                }
            }
        });
    }

    // Show hackathon details in modal
    function showHackathonDetails(hackathon) {
        const modal = document.getElementById('hackathonDetailsModal');
        
        // Populate modal content
        modal.querySelector('.job-title').textContent = hackathon.name;
        modal.querySelector('.company-name').textContent = hackathon.organizer;
        modal.querySelector('.company-location').textContent = `📍 ${hackathon.location}`;
        modal.querySelector('.job-description').textContent = hackathon.description;

        // Set logo
        const logoImg = modal.querySelector('.company-logo');
        logoImg.src = hackathon.image ? '../../../back_office/uploads/hackathon_images/' + hackathon.image : '../ressources/cybersecurity.png';
        logoImg.alt = hackathon.organizer;

        // Update meta information
        const startDate = new Date(hackathon.start_date);
        const endDate = new Date(hackathon.end_date);
        modal.querySelector('.job-meta').innerHTML = `
            <span class="job-type">🎯 ${getDuration(startDate, endDate)}</span>
            <span class="job-level">📅 ${startDate.toLocaleDateString()} - ${endDate.toLocaleDateString()}</span>
            <span class="job-location">🌍 ${hackathon.location}</span>
            <span class="job-salary">💰 $${hackathon.prize_pool || 'TBA'}</span>
        `;

        // Update required skills
        const qualList = modal.querySelector('.qualifications-list');
        if (hackathon.required_skills) {
            const skills = hackathon.required_skills.split(',').map(skill => skill.trim());
            qualList.innerHTML = skills.map(skill => `<li>💡 ${skill}</li>`).join('');
        } else {
            qualList.innerHTML = '<li>No specific skills required</li>';
        }

        // Update prizes
        const prizeList = modal.querySelector('.responsibilities-list');
        if (hackathon.prize_pool) {
            const prizes = calculatePrizes(parseInt(hackathon.prize_pool));
            prizeList.innerHTML = `
                <li>🥇 First prize: $${prizes.first.toLocaleString()}</li>
                <li>🥈 Second prize: $${prizes.second.toLocaleString()}</li>
                <li>🥉 Third prize: $${prizes.third.toLocaleString()}</li>
            `;
        } else {
            prizeList.innerHTML = '<li>Prizes to be announced</li>';
        }

        // Set up edit and delete buttons
        const editBtn = modal.querySelector('.edit-front-btn');
        const deleteBtn = modal.querySelector('.delete-front-btn');
        
        editBtn.onclick = () => openEditModal(hackathon);
        deleteBtn.onclick = () => confirmDelete(hackathon.id);

        // Show modal
        modal.style.display = 'block';
    }

    // Open edit modal with hackathon data
    function openEditModal(hackathon) {
        document.getElementById('edit_id').value = hackathon.id;
        document.getElementById('edit_name').value = hackathon.name;
        document.getElementById('edit_description').value = hackathon.description;
        document.getElementById('edit_start_date').value = hackathon.start_date;
        document.getElementById('edit_end_date').value = hackathon.end_date;
        document.getElementById('edit_start_time').value = hackathon.start_time;
        document.getElementById('edit_end_time').value = hackathon.end_time;
        document.getElementById('edit_location').value = hackathon.location;
        document.getElementById('edit_required_skills').value = hackathon.required_skills;
        document.getElementById('edit_organizer').value = hackathon.organizer;
        document.getElementById('edit_max_participants').value = hackathon.max_participants;
        document.getElementById('edit_prize_pool').value = hackathon.prize_pool;

        // Close details modal and open edit modal
        document.getElementById('hackathonDetailsModal').style.display = 'none';
        editModal.style.display = 'block';
    }

    // Confirm hackathon deletion
    function confirmDelete(hackathonId) {
        if (confirm('Are you sure you want to delete this hackathon? This action cannot be undone.')) {
            fetch(`http://localhost/projet_web/back_office/controllers/hackathonsController.php?id=${hackathonId}`, {
                method: 'DELETE',
                headers: {
                    'Content-Type': 'application/json'
                }
            })
            .then(response => response.json())
            .then(result => {
                if (result.success) {
                    loadHackathons();
                    document.getElementById('hackathonDetailsModal').style.display = 'none';
                    showToast('Hackathon deleted successfully!', 'success');
                } else {
                    throw new Error(result.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showToast('Error deleting hackathon: ' + error.message, 'error');
            });
        }
    }

    // Close modal
    function closeModal(modal) {
        modal.style.display = 'none';
        if (modal === addModal) {
            addHackathonForm.reset();
        }
    }

    // Validate form
    function validateForm(form, isEdit = false) {
        const prefix = isEdit ? 'edit_' : '';
        const errors = {};
        
        // Name validation (3-40 characters)
        const name = form[prefix + 'name'].value;
        if (name.length < 3 || name.length > 40) {
            errors.name = 'Name must be between 3 and 40 characters';
        }

        // Organizer validation (3-20 characters)
        const organizer = form[prefix + 'organizer'].value;
        if (organizer.length < 3 || organizer.length > 20) {
            errors.organizer = 'Organizer must be between 3 and 20 characters';
        }

        // Description validation (10-500 words)
        const description = form[prefix + 'description'].value;
        const wordCount = description.trim().split(/\s+/).filter(word => word.length > 0).length;
        if (wordCount < 10 || wordCount > 500) {
            errors.description = 'Description must be between 10 and 500 words';
        }

        // Date validation
        const today = new Date();
        today.setHours(0, 0, 0, 0);
        const startDate = new Date(form[prefix + 'start_date'].value);
        const endDate = new Date(form[prefix + 'end_date'].value);

        if (startDate < today) {
            errors.start_date = 'Start date cannot be in the past';
        }
        if (endDate < today) {
            errors.end_date = 'End date cannot be in the past';
        }
        if (endDate < startDate) {
            errors.end_date = 'End date must be after start date';
        }

        // Location validation (minimum 3 words)
        const location = form[prefix + 'location'].value;
        const locationWords = location.trim().split(/\s+/).filter(word => word.length > 0).length;
        if (locationWords < 3) {
            errors.location = 'Location must contain at least 3 words';
        }

        // Skills validation (comma separated)
        const skills = form[prefix + 'required_skills'].value;
        if (!skills.includes(',')) {
            errors.required_skills = 'Skills must be comma separated';
        }

        // Prize pool validation (positive number)
        const prizePool = parseInt(form[prefix + 'prize_pool'].value);
        if (isNaN(prizePool) || prizePool <= 0) {
            errors.prize_pool = 'Prize pool must be a positive number';
        }

        // Display errors
        Object.keys(errors).forEach(key => {
            const errorSpan = document.getElementById(`${prefix + key}-error`);
            if (errorSpan) {
                errorSpan.textContent = errors[key];
                errorSpan.style.color = '#dc2626';
                document.getElementById(prefix + key).classList.add('error');
            }
        });

        return Object.keys(errors).length === 0;
    }

    // Helper function to calculate duration in hours
    function getDurationHours(startDate, endDate) {
        const start = new Date(startDate);
        const end = new Date(endDate);
        return Math.ceil((end - start) / (1000 * 60 * 60));
    }

    // Helper function to show toast notifications
    function showToast(message, type = 'success') {
        const colors = {
            success: '#4CAF50',
            error: '#dc2626',
            info: '#3b82f6'
        };

        Toastify({
            text: message,
            duration: 3000,
            close: true,
            gravity: "top",
            position: "right",
            backgroundColor: colors[type] || colors.info,
            stopOnFocus: true
        }).showToast();
    }

    // Initialize the application
    init();
});

// Utility function to calculate duration between two dates
function getDuration(startDate, endDate) {
    const diff = endDate - startDate;
    const days = Math.ceil(diff / (1000 * 60 * 60 * 24));
    
    if (days < 1) return '24 Hours';
    if (days <= 2) return '48 Hours';
    if (days <= 7) return '1 Week';
    return `${days} Days`;
}

// Utility function to calculate prize distribution
function calculatePrizes(totalPrize) {
    return {
        first: Math.round(totalPrize * 0.5),    // 50% for first place
        second: Math.round(totalPrize * 0.3),   // 30% for second place
        third: Math.round(totalPrize * 0.2)     // 20% for third place
    };
}