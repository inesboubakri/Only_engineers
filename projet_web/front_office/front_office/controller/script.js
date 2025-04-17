document.addEventListener('DOMContentLoaded', () => {
    const viewToggleButton = document.querySelector('.view-toggle');
const jobCardsContainer = document.querySelector('.job-cards');
    const gridIcon = document.querySelector('.grid-icon');
    const listIcon = document.querySelector('.list-icon');

    if (!viewToggleButton || !jobCardsContainer || !gridIcon || !listIcon) {
        console.error('One or more required elements not found');
        return;
    }

    // Initialize view based on layout
    function updateViewToggleState() {
        const isListLayout = jobCardsContainer.classList.contains('list-layout');
        gridIcon.style.display = isListLayout ? 'none' : 'block';
        listIcon.style.display = isListLayout ? 'block' : 'none';
    }

    // Set initial state
    updateViewToggleState();

    // Add click event listener
    viewToggleButton.addEventListener('click', () => {
        console.log('Toggle button clicked'); // Debug log
        jobCardsContainer.classList.toggle('list-layout');
        updateViewToggleState();
    });

    // Existing code for projects button if needed
    const projectsButton = document.getElementById('projects-button');
    if (projectsButton) {
projectsButton.addEventListener('click', () => {
    console.log('Projects button clicked'); // Debugging log
    jobCardsContainer.classList.toggle('vertical-card-list'); // Toggle vertical layout
    console.log('Current classes:', jobCardsContainer.className); // Debugging log
        });
    }
});

//Bouton ‘Clear All’ 
document.querySelector('.clear-all').addEventListener('click', () => {
    document.querySelectorAll('.filters input[type="radio"]').forEach(input => {
        input.checked = false;
    });
});

// Job data
const jobData = {
    amazon: {
        title: "Senior UI/UX Designer",
        company: "Amazon",
        location: "San Francisco, CA",
        salary: "$250/hr",
        type: "Part time",
        level: "Senior level",
        description: "As an UI/UX Designer on Amazon, you'll focus on design user-friendly on several platform (web, mobile, dashboard, etc) to our users needs. Your innovative solution will enhance the user experience on several platforms.",
        qualifications: [
            "At least 2-4 years of relevant experience in product design or related roles",
            "Knowledge of design validation, either through quantitative or qualitative research",
            "Have good knowledge using Figma and Figjam",
            "Experience with analytics tools to gather data from users"
        ],
        responsibilities: [
            "Create design and user journey on every features and product/business units across multiples devices (Web+App)",
            "Identifying design problems through user journey and devising elegant solutions",
            "Develop low and hi fidelity designs, user experience flow, & prototype, translate it into highly-polished visual composites following style and brand guidelines",
            "Brainstorm and works together with Design Lead, UX Engineers, and PMs to execute a design sprint on specific story or task"
        ]
    },
    google: {
        title: "Junior UI/UX Designer",
        company: "Google",
        location: "California, CA",
        salary: "$150/hr",
        type: "Full time",
        level: "Junior level",
        description: "Join Google's design team and help create beautiful, intuitive interfaces that millions of users love to use.",
        qualifications: [
            "Bachelor's degree in Design, HCI, or related field",
            "1-2 years of experience in UI/UX design",
            "Proficiency in design tools like Figma, Sketch",
            "Understanding of user-centered design principles"
        ],
        responsibilities: [
            "Design user interfaces for Google products",
            "Conduct user research and usability testing",
            "Create wireframes and prototypes",
            "Collaborate with product and engineering teams"
        ]
    },
    dribbble: {
        title: "Senior Motion Designer",
        company: "Dribbble",
        location: "New York, NY",
        salary: "$260/hr",
        type: "Part time",
        level: "Senior level",
        description: "Join Dribbble as a Senior Motion Designer and help bring our platform's visual elements to life through stunning animations and motion graphics. You'll work on creating engaging motion design content that enhances user experience across our platform.",
        qualifications: [
            "5+ years of experience in motion design and animation",
            "Expert knowledge of After Effects, Cinema 4D, and other motion design tools",
            "Strong portfolio demonstrating exceptional motion design work",
            "Experience with UI/UX animation and micro-interactions"
        ],
        responsibilities: [
            "Create engaging motion graphics and animations for our platform",
            "Develop and maintain motion design guidelines",
            "Collaborate with the design team to create cohesive animated experiences",
            "Lead motion design initiatives and mentor junior designers"
        ]
    },
    twitter: {
        title: "UX Designer",
        company: "Twitter",
        location: "California, CA",
        salary: "$120/hr",
        type: "Full time",
        level: "Middle level",
        description: "As a UX Designer at Twitter, you'll be responsible for creating intuitive and engaging user experiences that millions of people interact with daily. You'll work on core features of the platform and help shape the future of social media interaction.",
        qualifications: [
            "3+ years of UX design experience",
            "Strong understanding of user-centered design principles",
            "Experience with design systems and component libraries",
            "Excellent prototyping and wireframing skills"
        ],
        responsibilities: [
            "Design user flows and interactions for Twitter features",
            "Conduct user research and usability testing",
            "Create and maintain design documentation",
            "Collaborate with product and engineering teams"
        ]
    }
};

// Modal functionality
document.addEventListener('DOMContentLoaded', () => {
    const modal = document.getElementById('jobDetailsModal');
    const detailButtons = document.querySelectorAll('.details');
    const closeModal = document.querySelector('.close-modal');
    const modalBookmarkBtn = modal.querySelector('.bookmark-btn');

    // Store bookmarked state for each job
    const bookmarkedJobs = new Set();

    function populateModal(jobId) {
        const data = jobData[jobId];
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
            <span class="job-level">${data.level}</span>
            <span class="job-location">${data.location}</span>
            <span class="job-salary">${data.salary}</span>
        `;

        // Update qualifications
        const qualList = modal.querySelector('.qualifications-list');
        qualList.innerHTML = data.qualifications.map(qual => `<li>${qual}</li>`).join('');

        // Update responsibilities
        const respList = modal.querySelector('.responsibilities-list');
        respList.innerHTML = data.responsibilities.map(resp => `<li>${resp}</li>`).join('');

        // Set company logo
        const logo = modal.querySelector('.company-logo');
        logo.src = `${jobId.toLowerCase()}-logo.png`;
        logo.alt = `${data.company} logo`;

        // Update bookmark button state
        modalBookmarkBtn.classList.toggle('active', bookmarkedJobs.has(jobId));

        // Store the current job ID on the modal
        modal.dataset.currentJob = jobId;

        // Show modal
        modal.classList.add('active');
    }

    // Event listeners for detail buttons
    detailButtons.forEach(button => {
        button.addEventListener('click', (e) => {
            const jobCard = e.target.closest('.job-card');
            const jobContent = jobCard.querySelector('.job-content');
            const company = Array.from(jobContent.classList)
                .find(cls => Object.keys(jobData).includes(cls));
            
            if (company) {
                populateModal(company);
            }
        });
    });

    // Bookmark button click handler
    if (modalBookmarkBtn) {
        modalBookmarkBtn.addEventListener('click', () => {
            const currentJob = modal.dataset.currentJob;
            if (currentJob) {
                if (bookmarkedJobs.has(currentJob)) {
                    bookmarkedJobs.delete(currentJob);
                    modalBookmarkBtn.classList.remove('active');
                } else {
                    bookmarkedJobs.add(currentJob);
                    modalBookmarkBtn.classList.add('active');
                }

                // Also update the bookmark button in the job card
                const jobCard = document.querySelector(`.job-content.${currentJob}`);
                if (jobCard) {
                    const cardBookmarkBtn = jobCard.querySelector('.bookmark');
                    cardBookmarkBtn.classList.toggle('active', bookmarkedJobs.has(currentJob));
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
            const jobTitle = modal.querySelector('.job-title').textContent;
            const company = modal.querySelector('.company-name').textContent;
            
            const shareText = `Check out this ${jobTitle} position at ${company}!`;
            
            if (navigator.share) {
                navigator.share({
                    title: `${jobTitle} at ${company}`,
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
});
