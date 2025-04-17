// Project data
const projectData = {
    "AI Image Generator": {
        title: "AI Image Generator",
        company: "ml-powered-image-creation",
        category: "AI/ML",
        type: "Web Application",
        tech: ["Python", "React", "TensorFlow", "OpenAI API"],
        description: "A web application that generates unique images using AI. Users can input text descriptions and get AI-generated images in various styles.",
        features: [
            "Text-to-image generation using OpenAI's DALL-E API",
            "Multiple style options (realistic, artistic, abstract)",
            "Image history and favorites",
            "Share generated images on social media",
            "Custom style training"
        ],
        requirements: [
            "Python 3.8+",
            "React 18",
            "OpenAI API key",
            "TensorFlow 2.x",
            "Node.js 16+"
        ],
        difficulty: "Advanced",
        duration: "2-3 months",
        lastUpdated: "2024-02-15"
    }
};

// Set to store bookmarked projects
const bookmarkedProjects = new Set();

// Helper function to get project ID from card
function getProjectIdFromCard(card) {
    const title = card.querySelector('h3').textContent;
    const subtitle = card.querySelector('.job-title h4').textContent;
    return Object.keys(projectData).find(
        id => projectData[id].title === title
    );
}

// Toggle bookmark state
function toggleBookmark(projectId, btn) {
    if (bookmarkedProjects.has(projectId)) {
        bookmarkedProjects.delete(projectId);
        btn.classList.remove('active');
    } else {
        bookmarkedProjects.add(projectId);
        btn.classList.add('active');
    }
}

// Helper function to get project ID from modal
function getProjectIdFromModal() {
    const title = modalContent.querySelector('.job-title').textContent;
    return Object.keys(projectData).find(
        id => projectData[id].title === title
    );
}

// Helper function to find card bookmark button
function findCardBookmarkBtn(projectId) {
    const project = projectData[projectId];
    return Array.from(document.querySelectorAll('.job-card')).find(
        card => card.querySelector('h3').textContent === project.title
    ).querySelector('.bookmark');
}

// Function to populate modal with project details
function populateModal(projectId) {
    const project = projectData[projectId];
    if (!project) return;

    const modal = document.getElementById('projectDetailsModal');
    const modalContent = modal.querySelector('.modal-content');

    // Update modal content
    modalContent.querySelector('.job-title').textContent = project.title;
    modalContent.querySelector('.company-name').textContent = project.company;
    modalContent.querySelector('.company-location').textContent = project.location;
    modalContent.querySelector('.job-description').textContent = project.description;

    // Update features list
    const featuresList = modalContent.querySelector('.qualifications-list');
    featuresList.innerHTML = project.features
        .map(feature => `<li>${feature}</li>`)
        .join('');

    // Update requirements
    const requirementsList = modalContent.querySelector('.responsibilities-list');
    requirementsList.innerHTML = project.requirements
        .map(req => `<li>${req}</li>`)
        .join('');

    // Update company logo
    const companyLogo = modalContent.querySelector('.company-logo');
    companyLogo.src = `${project.company.toLowerCase()}-logo.png`;
    companyLogo.alt = project.company;

    // Update meta information
    const jobMeta = modalContent.querySelector('.job-meta');
    jobMeta.innerHTML = `
        <span class="meta-item">${project.type}</span>
        <span class="meta-item">${project.duration}</span>
        <span class="meta-item">${project.location}</span>
    `;

    // Update bookmark button state
    const modalBookmarkBtn = modal.querySelector('.bookmark-btn');
    modalBookmarkBtn.classList.toggle('active', bookmarkedProjects.has(projectId));

    // Show modal
    modal.style.display = 'flex';
}

// Initialize everything when DOM is loaded
document.addEventListener('DOMContentLoaded', () => {
    const modal = document.getElementById('projectDetailsModal');
    const modalContent = modal.querySelector('.modal-content');
    const closeModalBtn = modal.querySelector('.close-modal');
    const modalBookmarkBtn = modal.querySelector('.bookmark-btn');
    const viewToggleButton = document.querySelector('.view-toggle');
    const jobCardsContainer = document.querySelector('.job-cards');
    const gridIcon = document.querySelector('.grid-icon');
    const listIcon = document.querySelector('.list-icon');
    const shareBtn = modal.querySelector('.share-btn');

    // Event Listeners for project cards
    document.querySelectorAll('.job-card').forEach(card => {
        const detailsBtn = card.querySelector('.details');
        detailsBtn.addEventListener('click', () => {
            const projectId = getProjectIdFromCard(card);
            populateModal(projectId);
        });

        const bookmarkBtn = card.querySelector('.bookmark');
        bookmarkBtn.addEventListener('click', (e) => {
            e.stopPropagation();
            const projectId = getProjectIdFromCard(card);
            toggleBookmark(projectId, bookmarkBtn);
        });
    });

    // Modal bookmark button click handler
    modalBookmarkBtn.addEventListener('click', () => {
        const projectId = getProjectIdFromModal();
        const cardBookmarkBtn = findCardBookmarkBtn(projectId);
        toggleBookmark(projectId, cardBookmarkBtn);
        modalBookmarkBtn.classList.toggle('active');
    });

    // Close modal when clicking outside or on close button
    window.addEventListener('click', (e) => {
        if (e.target === modal) {
            modal.style.display = 'none';
        }
    });

    closeModalBtn.addEventListener('click', () => {
        modal.style.display = 'none';
    });

    // Initialize view based on layout
    function updateViewToggleState() {
        const isListLayout = jobCardsContainer.classList.contains('list-layout');
        gridIcon.style.display = isListLayout ? 'none' : 'block';
        listIcon.style.display = isListLayout ? 'block' : 'none';
    }

    // Set initial state
    updateViewToggleState();

    // Add click event listener for view toggle
    viewToggleButton.addEventListener('click', () => {
        jobCardsContainer.classList.toggle('list-layout');
        updateViewToggleState();
    });

    // Share functionality
    shareBtn.addEventListener('click', async () => {
        const projectId = getProjectIdFromModal();
        const project = projectData[projectId];
        const shareData = {
            title: `${project.title} - ${project.company}`,
            text: `Check out this project: ${project.title} by ${project.company}`,
            url: window.location.href
        };

        try {
            if (navigator.share) {
                await navigator.share(shareData);
            } else {
                await navigator.clipboard.writeText(window.location.href);
                alert('Link copied to clipboard!');
            }
        } catch (err) {
            console.error('Error sharing:', err);
        }
    });
});

// Theme toggle functionality
const themeToggle = document.querySelector('.theme-toggle');
const root = document.documentElement;
const savedTheme = localStorage.getItem('theme') || 'light';

// Set initial theme
root.setAttribute('data-theme', savedTheme);

// Update icon visibility based on theme
document.querySelector('.sun-icon').style.display = savedTheme === 'dark' ? 'none' : 'block';
document.querySelector('.moon-icon').style.display = savedTheme === 'dark' ? 'block' : 'none';

// Theme toggle click handler
themeToggle.addEventListener('click', () => {
    const currentTheme = root.getAttribute('data-theme');
    const newTheme = currentTheme === 'dark' ? 'light' : 'dark';
    
    root.setAttribute('data-theme', newTheme);
    localStorage.setItem('theme', newTheme);
    
    // Toggle icon visibility
    document.querySelector('.sun-icon').style.display = newTheme === 'dark' ? 'none' : 'block';
    document.querySelector('.moon-icon').style.display = newTheme === 'dark' ? 'block' : 'none';
}); 