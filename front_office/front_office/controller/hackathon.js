

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

document.addEventListener('DOMContentLoaded', function() {
    // Load hackathons from database
    loadHackathons();

    // Setup event listeners for filters and sorting
    setupEventListeners();
});

async function loadHackathons() {
    try {
        const response = await fetch('http://localhost/projet_web/front_office/front_office/controller/get_hackathons.php');
        const data = await response.json();
        
        if (data.success) {
            displayHackathons(data.data);
            updateHackathonCount(data.data.length);
        } else {
            console.error('Error loading hackathons:', data.message);
        }
    } catch (error) {
        console.error('Failed to load hackathons:', error);
    }
}

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

    // Add event listener for the details button
    const detailsBtn = card.querySelector('.details');
    detailsBtn.addEventListener('click', () => showHackathonDetails(hackathon));

    return card;
}

function getDuration(startDate, endDate) {
    const diff = endDate - startDate;
    const days = diff / (1000 * 60 * 60 * 24);
    
    if (days < 1) return '24 Hours';
    if (days <= 2) return '48 Hours';
    if (days <= 7) return '1 Week';
    return days + 'Days';
}

function updateHackathonCount(count) {
    const countSpan = document.querySelector('.jobs-header .count');
    if (countSpan) {
        countSpan.textContent = count;
    }
}

function setupEventListeners() {
    // Add event listeners for filters, sorting, etc.
    const filterOptions = document.querySelectorAll('.filter-options input');
    filterOptions.forEach(option => {
        option.addEventListener('change', () => {
            // Implement filtering logic here
        });
    });

    // Sort select handler
    const sortSelect = document.querySelector('.sort select');
    if (sortSelect) {
        sortSelect.addEventListener('change', (e) => {
            // Implement sorting logic here
        });
    }
}

function calculatePrizes(totalPrize) {
    const firstPrize = Math.round(totalPrize * 0.5); // 50% du total
    const secondPrize = Math.round(totalPrize * 0.3); // 30% du total
    const thirdPrize = totalPrize - firstPrize - secondPrize; // Le reste (20%)
    return {
        first: firstPrize,
        second: secondPrize,
        third: thirdPrize
    };
}

// Show hackathon details in modal
function showHackathonDetails(hackathon) {
    const modal = document.getElementById('hackathonDetailsModal');
    
    // Create all required elements if they don't exist
    if (!modal.querySelector('.job-title')) {
        const modalContent = `
            <div class="modal-content">
                <div class="modal-header">
                    <div class="header-left">
                        <button class="close-modal">×</button>
                    </div>
                    <div class="header-right">
                        <button class="bookmark-btn">
                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M19 21l-7-5-7 5V5a2 2 0 0 1 2-2h10a2 2 0 0 1 2 2z"/>
                            </svg>
                        </button>
                        <button class="share-btn">
                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M4 12v8a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2v-8"/>
                                <polyline points="16 6 12 2 8 6"/>
                                <line x1="12" y1="2" x2="12" y2="15"/>
                            </svg>
                        </button>
                    </div>
                </div>
                <div class="modal-body">
                    <div class="job-header">
                        <h1 class="job-title"></h1>
                        <div class="company-info">
                            <img class="company-logo" src="" alt="">
                            <div class="company-details">
                                <h2 class="company-name"></h2>
                                <p class="company-location"></p>
                            </div>
                        </div>
                        <div class="job-meta"></div>
                    </div>
                    <div class="job-content">
                        <div class="section">
                            <h3>About this hackathon</h3>
                            <p class="job-description"></p>
                        </div>
                        <div class="section">
                            <h3>Requirements</h3>
                            <ul class="qualifications-list"></ul>
                        </div>
                        <div class="section">
                            <h3>Prizes</h3>
                            <ul class="responsibilities-list"></ul>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button class="delete-front-btn">Delete</button>
                        <button class="edit-front-btn">Edit</button>
                        <button class="apply-now-btn">Register Now</button>
                    </div>
                </div>`;
        modal.innerHTML = modalContent;
    }

    const jobTitle = modal.querySelector('.job-title');
    const companyName = modal.querySelector('.company-name');
    const companyLocation = modal.querySelector('.company-location');
    const jobDescription = modal.querySelector('.job-description');
    const logoImg = modal.querySelector('.company-logo');
    const metaContainer = modal.querySelector('.job-meta');
    const qualList = modal.querySelector('.qualifications-list');
    const prizeList = modal.querySelector('.responsibilities-list');
    const editButton = modal.querySelector('.edit-front-btn');

    // Update content
    jobTitle.textContent = hackathon.name;
    companyName.textContent = hackathon.organizer;
    companyLocation.textContent = `📍 ${hackathon.location}`;
    jobDescription.textContent = hackathon.description;
    
    // Update logo
    logoImg.src = hackathon.image ? '../../../back_office/uploads/hackathon_images/' + hackathon.image : '../ressources/cybersecurity.png';
    logoImg.alt = hackathon.organizer;

    // Update meta information
    const startDate = new Date(hackathon.start_date);
    const endDate = new Date(hackathon.end_date);
    metaContainer.innerHTML = `
        <span class="job-type">🎯 ${getDuration(startDate, endDate)}</span>
        <span class="job-level">📅 ${startDate.toLocaleDateString()} - ${endDate.toLocaleDateString()}</span>
        <span class="job-location">🌍 ${hackathon.location}</span>
        <span class="job-salary">💰 $${hackathon.prize_pool || 'TBA'}</span>
    `;

    // Update required skills
    if (hackathon.required_skills) {
        const skills = hackathon.required_skills.split(',').map(skill => skill.trim());
        qualList.innerHTML = skills.map(skill => `<li>💡 ${skill}</li>`).join('');
    } else {
        qualList.innerHTML = '<li>No specific skills required</li>';
    }

    // Update prizes
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

    // Show modal
    modal.style.display = 'block';

    // Event listener for edit button
    editButton.addEventListener('click', () => {
        modal.style.display = 'none'; // Hide details modal
        // Create hackathon object for edit modal
        const hackathonForEdit = {
            id: hackathon.id,
            name: hackathon.name,
            description: hackathon.description,
            start_date: hackathon.start_date,
            end_date: hackathon.end_date,
            start_time: hackathon.start_time,
            end_time: hackathon.end_time,
            location: hackathon.location,
            required_skills: hackathon.required_skills,
            organizer: hackathon.organizer,
            max_participants: hackathon.max_participants,
            prize_pool: hackathon.prize_pool,
            image: hackathon.image
        };
        openEditModal(hackathonForEdit); // Open edit modal with current hackathon data
    });

    // Event listener for delete button
    const deleteButton = modal.querySelector('.delete-front-btn');
    deleteButton.addEventListener('click', async () => {
        // Créer et afficher le modal de confirmation
        const confirmModal = document.createElement('div');
        confirmModal.className = 'modal confirmation-modal';
        confirmModal.style.cssText = `
            display: block;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
        `;

        const confirmContent = document.createElement('div');
        confirmContent.className = 'modal-content';
        confirmContent.style.cssText = `
            background-color: #fefefe;
            margin: 15% auto;
            padding: 20px;
            border: 1px solid #888;
            width: 80%;
            max-width: 500px;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            position: relative;
            animation: slideIn 0.3s ease-out;
        `;

        confirmContent.innerHTML = `
            <h2 style="margin-top: 0; color: #4f46e5;">Confirmer la suppression</h2>
            <p style="margin: 20px 0; color: #374151;">Êtes-vous sûr de vouloir supprimer ce hackathon ? Cette action est irréversible.</p>
            <div style="display: flex; justify-content: flex-end; gap: 10px;">
                <button class="cancel-btn" style="
                    padding: 8px 16px;
                    border: 1px solid #d1d5db;
                    background-color: white;
                    color: #374151;
                    border-radius: 6px;
                    cursor: pointer;
                    font-weight: 500;
                ">Annuler</button>
                <button class="confirm-btn" style="
                    padding: 8px 16px;
                    background-color: #dc2626;
                    color: white;
                    border: none;
                    border-radius: 6px;
                    cursor: pointer;
                    font-weight: 500;
                ">Supprimer</button>
            </div>
        `;

        confirmModal.appendChild(confirmContent);
        document.body.appendChild(confirmModal);

        // Ajouter une animation CSS
        const style = document.createElement('style');
        style.textContent = `
            @keyframes slideIn {
                from {
                    transform: translateY(-20px);
                    opacity: 0;
                }
                to {
                    transform: translateY(0);
                    opacity: 1;
                }
            }
        `;
        document.head.appendChild(style);

        // Gérer les actions de confirmation
        return new Promise((resolve) => {
            const cancelBtn = confirmContent.querySelector('.cancel-btn');
            const confirmBtn = confirmContent.querySelector('.confirm-btn');

            cancelBtn.addEventListener('click', () => {
                document.body.removeChild(confirmModal);
                resolve(false);
            });

            confirmBtn.addEventListener('click', async () => {
                try {
                    const response = await fetch(`http://localhost/projet_web/back_office/controllers/hackathonsController.php?id=${hackathon.id}`, {
                        method: 'DELETE',
                        headers: {
                            'Content-Type': 'application/json'
                        }
                    });

                    const result = await response.json();
                    if (result.success) {
                        Toastify({
                            text: "Hackathon supprimé avec succès!",
                            duration: 3000,
                            close: true,
                            gravity: "top",
                            position: "right",
                            backgroundColor: "#4CAF50",
                            stopOnFocus: true
                        }).showToast();

                        // Fermer les modals et recharger la liste
                        document.body.removeChild(confirmModal);
                        modal.style.display = 'none';
                        loadHackathons();
                    } else {
                        throw new Error(result.message);
                    }
                } catch (error) {
                    console.error('Erreur:', error);
                    Toastify({
                        text: "Erreur lors de la suppression du hackathon: " + (error.message || "Erreur inconnue"),
                        duration: 3000,
                        close: true,
                        gravity: "top",
                        position: "right",
                        backgroundColor: "#dc2626",
                        stopOnFocus: true
                    }).showToast();
                }
                document.body.removeChild(confirmModal);
            });

            // Fermer en cliquant en dehors
            confirmModal.addEventListener('click', (e) => {
                if (e.target === confirmModal) {
                    document.body.removeChild(confirmModal);
                    resolve(false);
                }
            });
        });
    });

    // Close button handler
    const closeBtn = modal.querySelector('.close-modal');
    if (closeBtn) {
        closeBtn.onclick = () => modal.style.display = 'none';
    }

    // Close on outside click
    window.onclick = (event) => {
        if (event.target === modal) {
            modal.style.display = 'none';
        }
    };
}

// Edit modal functionality
const editModal = document.getElementById('editHackathonModal');
const editForm = document.getElementById('editHackathonForm');
const editModalClose = editModal.querySelector('.close');
const editModalCancel = editModal.querySelector('.cancel-btn');
const editDescription = document.getElementById('edit_description');
const editWordCount = document.getElementById('edit-word-count');

// Fonction pour mettre à jour le compteur de mots
function updateWordCount(textarea, countElement) {
    const wordCount = textarea.value.trim().split(/\s+/).filter(word => word.length > 0).length;
    countElement.textContent = wordCount;
    
    // Mise à jour visuelle du compteur
    if (wordCount < 10 || wordCount > 500) {
        countElement.style.color = '#dc2626'; // Rouge pour erreur
    } else {
        countElement.style.color = '#333'; // Couleur normale
    }
}

// Fonction de validation du formulaire
function validateForm(form) {
    const errors = {};
    const isEdit = form.id === 'editHackathonForm';
    const prefix = isEdit ? 'edit_' : '';
    
    // Validation du nom (3-25 caractères)
    const name = form[prefix + 'name'].value;
    if (name.length < 3 || name.length > 25) {
        showError(prefix + 'name', 'Le nom doit contenir entre 3 et 25 caractères');
        return false;
    }

    // Validation de l'organisateur (3-20 caractères)
    const organizer = form[prefix + 'organizer'].value;
    if (organizer.length < 3 || organizer.length > 20) {
        showError(prefix + 'organizer', 'L\'organisateur doit contenir entre 3 et 20 caractères');
        return false;
    }

    // Validation de la description (10-500 mots)
    const description = form[prefix + 'description'].value;
    const wordCount = description.trim().split(/\s+/).filter(word => word.length > 0).length;
    if (wordCount < 10 || wordCount > 500) {
        showError(prefix + 'description', 'La description doit contenir entre 10 et 500 mots');
        return false;
    }

    // Validation des dates
    const today = new Date();
    today.setHours(0, 0, 0, 0);
    const startDate = new Date(form[prefix + 'start_date'].value);
    const endDate = new Date(form[prefix + 'end_date'].value);

    if (startDate < today) {
        showError(prefix + 'start_date', 'La date de début ne peut pas être antérieure à aujourd\'hui');
        return false;
    }
    if (endDate < today) {
        showError(prefix + 'end_date', 'La date de fin ne peut pas être antérieure à aujourd\'hui');
        return false;
    }
    if (endDate < startDate) {
        showError(prefix + 'end_date', 'La date de fin doit être postérieure à la date de début');
        return false;
    }

    // Validation de la localisation (minimum 3 mots)
    const location = form[prefix + 'location'].value;
    const locationWords = location.trim().split(/\s+/).filter(word => word.length > 0).length;
    if (locationWords < 3) {
        showError(prefix + 'location', 'La localisation doit contenir au moins 3 mots');
        return false;
    }

            // Validation des compétences requises (séparées par des virgules)
        // Validation des compétences requises (séparées par des virgules)
        const skills = form.required_skills.value.trim(); // Trim pour supprimer les espaces inutiles

        // 1. Vérifier qu'il y a bien des virgules
        if (!skills.includes(',')) {
            errors.skills = 'Les compétences doivent être séparées par des virgules';
            showError('required_skills', errors.skills);
            return false;
        }

        // 2. Nettoyer et vérifier les doublons
        const skillsArray = skills.split(',')
            .map(skill => skill.trim()) // Enlever les espaces autour
            .filter(skill => skill.length > 0); // Enlever les chaînes vides

        // Vérifier les doublons (insensible à la casse)
        const uniqueSkills = new Set(skillsArray.map(skill => skill.toLowerCase()));
        if (uniqueSkills.size !== skillsArray.length) {
            errors.skills = 'Une compétence ne peut pas être répétée plusieurs fois';
            showError('required_skills', errors.skills);
            return false;
        }

    // Prize Pool doit être positif
    const prizePool = parseInt(form[prefix + 'prize_pool'].value);
    if (isNaN(prizePool) || prizePool <= 0) {
        showError(prefix + 'prize_pool', 'Le Prize Pool doit être supérieur à 0');
        return false;
    }


            const Participants = parseInt(form[prefix + 'max_participants'].value);
        if (isNaN(Participants) || Participants <= 1 || Participants >= 100) {
            errors.prizePool = 'Le max participants Pool doit être supérieur à 1 et inférieur à 100';
        }


    return true;
}

// Fonction pour afficher les erreurs
function showError(fieldId, message) {
    const errorSpan = document.getElementById(`${fieldId}-error`);
    if (errorSpan) {
        errorSpan.textContent = message;
        errorSpan.style.color = '#dc2626';
        // Ajouter une classe d'erreur au champ
        const field = document.getElementById(fieldId);
        if (field) {
            field.classList.add('error');
        }
    }
    
    // Afficher une notification Toastify
    Toastify({
        text: message,
        duration: 3000,
        close: true,
        gravity: "top",
        position: "right",
        backgroundColor: "#dc2626",
        stopOnFocus: true,
        onClick: function(){} 
    }).showToast();
}

function clearErrors(form) {
    const errorSpans = form.querySelectorAll('.error-message');
    errorSpans.forEach(span => span.textContent = '');
    const errorFields = form.querySelectorAll('.error');
    errorFields.forEach(field => field.classList.remove('error'));
}

function openEditModal(hackathon) {
    // Populate form fields
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

    // Show modal
    editModal.style.display = 'block';
    document.body.style.overflow = 'hidden';

    // Update word count
    updateWordCount(editDescription, editWordCount);
}

function closeEditModal() {
    editModal.style.display = 'none';
    document.body.style.overflow = '';
    editForm.reset();
    clearErrors(editForm);
}

// Event listeners for edit modal
editModalClose.addEventListener('click', closeEditModal);
editModalCancel.addEventListener('click', closeEditModal);

window.addEventListener('click', (e) => {
    if (e.target === editModal) {
        closeEditModal();
    }
});

// Word count for edit description
editDescription.addEventListener('input', () => {
    updateWordCount(editDescription, editWordCount);
});

// Edit form submission
editForm.addEventListener('submit', async function(e) {
    e.preventDefault();
    if (validateForm(this)) {
        const formData = new FormData(this);
        try {
            const response = await fetch(`http://localhost/projet_web/back_office/controllers/hackathonsController.php?id=${formData.get('id')}`, {
                method: 'POST',
                body: formData
            });
            
            const result = await response.json();
            if (result.success) {
                closeEditModal();
                loadHackathons();
                alert('Hackathon updated successfully!');
            } else {
                alert('Error: ' + result.message);
            }
        } catch (error) {
            console.error('Error:', error);
            alert('Error updating hackathon');
        }
    }
});

// Event listener for edit button in details modal
document.querySelector('.edit-front-btn').addEventListener('click', function() {
    const currentHackathon = document.getElementById('hackathonDetailsModal').dataset.currentHackathon;
    if (currentHackathon) {
        document.getElementById('hackathonDetailsModal').style.display = 'none';
        openEditModal(JSON.parse(currentHackathon));
    }
});

document.addEventListener('DOMContentLoaded', function() {
    // Sélecteurs pour le modal d'ajout
    const addHackathonModal = document.getElementById('addHackathonModal');
    const addHackathonButton = document.querySelector('.learn-more');
    const closeButton = addHackathonModal.querySelector('.close');
    const cancelButton = addHackathonModal.querySelector('.cancel-btn');
    const addHackathonForm = document.getElementById('addHackathonForm');

    // Ouvrir le modal
    addHackathonButton.addEventListener('click', () => {
        addHackathonModal.style.display = 'block';
        document.body.style.overflow = 'hidden';
    });

    // Fermer le modal
    function closeAddHackathonModal() {
        addHackathonModal.style.display = 'none';
        document.body.style.overflow = '';
        addHackathonForm.reset();
    }

    closeButton.addEventListener('click', closeAddHackathonModal);
    cancelButton.addEventListener('click', closeAddHackathonModal);

    // Fermer le modal en cliquant en dehors
    window.addEventListener('click', (e) => {
        if (e.target === addHackathonModal) {
            closeAddHackathonModal();
        }
    });

    // Validation du formulaire
    function validateForm(form) {
        const errors = {};
        
        // Validation du nom (3-40 caractères)
        const name = form.name.value;
        if (name.length < 3 || name.length > 25) {
            errors.name = 'Le nom doit contenir entre 3 et 25 caractères';
            showError('name', errors.name);
            return false;
        }

        // Validation de l'organisateur (3-20 caractères)
        const organizer = form.organizer.value;
        if (organizer.length < 3 || organizer.length > 20) {
            errors.organizer = 'L\'organisateur doit contenir entre 3 et 20 caractères';
            showError('organizer', errors.organizer);
            return false;
        }

        // Validation de la description (10-500 mots)
        const description = form.description.value;
        const wordCount = description.trim().split(/\s+/).filter(word => word.length > 0).length;
        if (wordCount < 10 || wordCount > 500) {
            errors.description = 'La description doit contenir entre 10 et 500 mots';
            showError('description', errors.description);
            return false;
        }

        // Validation des dates
        const today = new Date();
        today.setHours(0, 0, 0, 0);
        const startDate = new Date(form.start_date.value);
        const endDate = new Date(form.end_date.value);

        if (startDate < today) {
            errors.startDate = 'La date de début ne peut pas être antérieure à aujourd\'hui';
            showError('start_date', errors.startDate);
            return false;
        }
        if (endDate < today) {
            errors.endDate = 'La date de fin ne peut pas être antérieure à aujourd\'hui';
            showError('end_date', errors.endDate);
            return false;
        }
        if (endDate < startDate) {
            errors.endDate = 'La date de fin doit être postérieure à la date de début';
            showError('end_date', errors.endDate);
            return false;
        }

        // Validation de la localisation (minimum 3 mots)
        const location = form.location.value;
        const locationWords = location.trim().split(/\s+/).filter(word => word.length > 0).length;
        if (locationWords < 3) {
            errors.location = 'La localisation doit contenir au moins 3 mots';
            showError('location', errors.location);
            return false;
        }

        // Validation des compétences requises (séparées par des virgules)
        const skills = form.required_skills.value.trim(); // Trim pour supprimer les espaces inutiles

        // 1. Vérifier qu'il y a bien des virgules
        if (!skills.includes(',')) {
            errors.skills = 'Les compétences doivent être séparées par des virgules';
            showError('required_skills', errors.skills);
            return false;
        }

        // 2. Nettoyer et vérifier les doublons
        const skillsArray = skills.split(',')
            .map(skill => skill.trim()) // Enlever les espaces autour
            .filter(skill => skill.length > 0); // Enlever les chaînes vides

        // Vérifier les doublons (insensible à la casse)
        const uniqueSkills = new Set(skillsArray.map(skill => skill.toLowerCase()));
        if (uniqueSkills.size !== skillsArray.length) {
            errors.skills = 'Une compétence ne peut pas être répétée plusieurs fois';
            showError('required_skills', errors.skills);
            return false;
        }


        // Prize Pool doit être positif
        const prizePool = parseInt(form.prize_pool.value);
        if (isNaN(prizePool) || prizePool <= 0) {
            errors.prizePool = 'Le Prize Pool doit être supérieur à 0';
            showError('prize_pool', errors.prizePool);
            return false;
        }

        return true;
    }

    // Fonction pour afficher les erreurs
    function showError(fieldId, message) {
        const errorSpan = document.getElementById(`${fieldId}-error`);
        if (errorSpan) {
            errorSpan.textContent = message;
            errorSpan.style.color = '#dc2626';
            // Ajouter une classe d'erreur au champ
            const field = document.getElementById(fieldId);
            if (field) {
                field.classList.add('error');
            }
        }
        
        // Afficher une notification Toastify
        Toastify({
            text: message,
            duration: 3000,
            close: true,
            gravity: "top",
            position: "right",
            backgroundColor: "#dc2626",
            stopOnFocus: true,
            onClick: function(){} 
        }).showToast();
    }

    // Nettoyer les messages d'erreur lors de la saisie
    function setupInputValidation() {
        const inputs = addHackathonForm.querySelectorAll('input, textarea');
        inputs.forEach(input => {
            input.addEventListener('input', function() {
                this.classList.remove('error');
                const errorSpan = document.getElementById(`${this.id}-error`);
                if (errorSpan) {
                    errorSpan.textContent = '';
                }
            });
        });
    }

    // Soumission du formulaire
    addHackathonForm.addEventListener('submit', async function(e) {
        e.preventDefault();
        if (validateForm(this)) {
            const formData = new FormData(this);
            try {
                const response = await fetch('http://localhost/projet_web/back_office/controllers/hackathonsController.php', {
                    method: 'POST',
                    body: formData
                });
                
                const result = await response.json();
                if (result.success) {
                    closeAddHackathonModal();
                    loadHackathons(); // Recharger la liste des hackathons
                    alert('Hackathon ajouté avec succès!');
                } else {
                    alert('Erreur: ' + result.message);
                }
            } catch (error) {
                console.error('Erreur:', error);
                alert('Erreur lors de l\'ajout du hackathon');
            }
        }
    });

    // Chargement initial des hackathons
    loadHackathons();
    setupInputValidation();
});