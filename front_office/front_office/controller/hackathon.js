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
        modal.querySelector('.company-location').textContent = data.location;
        modal.querySelector('.job-description').textContent = data.description;

        // Update meta information
        const metaContainer = modal.querySelector('.job-meta');
        metaContainer.innerHTML = `
            <span class="job-type">${data.type}</span>
            <span class="job-level">${data.duration}</span>
            <span class="job-location">${data.location}</span>
            <span class="job-salary">${data.prize}</span>
        `;

        // Update requirements
        const qualList = modal.querySelector('.qualifications-list');
        qualList.innerHTML = data.requirements.map(req => `<li>${req}</li>`).join('');

        // Update prizes
        const prizeList = modal.querySelector('.responsibilities-list');
        prizeList.innerHTML = data.prizes.map(prize => `<li>${prize}</li>`).join('');

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