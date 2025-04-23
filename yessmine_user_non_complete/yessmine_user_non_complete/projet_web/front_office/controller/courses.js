// Course data
let courseData = {};

// Fetch courses from backend
async function fetchCourses() {
    try {
        const response = await fetch('../back_office/controllers/course_crud.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: 'action=read'
        });
        const data = await response.json();
        
        if (data.status === 'success') {
            // Transform backend data to match front-end structure
            courseData = data.data.reduce((acc, course) => {
                acc[course.course_id] = {
                    id: course.course_id,
                    title: course.title,
                    company: 'OnlyEngineers', // Default company
                    location: 'Self-paced',
                    price: course.fees === '0' ? 'Free' : `$${course.fees}`,
                    type: course.status === 'free' ? 'Beginner' : 'Advanced',
                    duration: '2-5 Hours',
                    description: `${course.title} - Comprehensive course offered by OnlyEngineers.`,
                    learningOutcomes: [],
                    requirements: [],
                    tags: [course.status === 'free' ? 'Free' : 'Paid', '2-5 Hours'],
                    course_link: course.course_link,
                    certification_link: course.certification_link
                };
                return acc;
            }, {});

            // Render courses
            renderCourses();
        }
    } catch (error) {
        console.error('Error fetching courses:', error);
    }
}

// Function to render courses
function renderCourses() {
    const container = document.querySelector('.job-cards');
    if (!container) return;

    container.innerHTML = Object.entries(courseData).map(([id, course]) => `
        <div class="job-card">
            <div class="job-content ${getCourseStyle(course)}">
                <div class="card-header">
                    <span class="date">Updated recently</span>
                    <button class="bookmark">
                        <svg class="bookmark-outline" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M19 21l-7-5-7 5V5a2 2 0 0 1 2-2h10a2 2 0 0 1 2 2z"></path>
                        </svg>
                        <svg class="bookmark-filled" width="16" height="16" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M19 21l-7-5-7 5V5a2 2 0 0 1 2-2h10a2 2 0 0 1 2 2z"></path>
                        </svg>
                    </button>
                </div>
                <h3>${course.company}</h3>
                <div class="job-title">
                    <h4>${course.title}</h4>
                    <img src="../assets/logo.png" alt="${course.company}" class="company-logo">
                </div>
                <div class="tags">
                    ${course.tags.map(tag => `<span>${tag}</span>`).join('')}
                </div>
                <div class="card-footer">
                    <div class="job-details">
                        <div class="salary">${course.price}</div>
                        <div class="location">${course.location}</div>
                    </div>
                    <button class="details">Details</button>
                </div>
            </div>
        </div>
    `).join('');

    // Reattach event listeners
    attachEventListeners();
}

// Helper function to determine course card style
function getCourseStyle(course) {
    if (course.price === 'Free') return 'google';
    return 'microsoft';
}

// DOM Elements
const modal = document.getElementById('courseDetailsModal');
const modalContent = modal.querySelector('.modal-content');
const closeModalBtn = modal.querySelector('.close-modal');
const modalBookmarkBtn = modal.querySelector('.bookmark-btn');
const viewToggleButton = document.querySelector('.view-toggle');
const jobCardsContainer = document.querySelector('.job-cards');
const gridIcon = document.querySelector('.grid-icon');
const listIcon = document.querySelector('.list-icon');
const shareBtn = modal.querySelector('.share-btn');
const readArticleBtn = modal.querySelector('.read-article-btn');

// Article modal elements
const articleModal = document.getElementById('articleReadingModal');
const articleModalContent = articleModal.querySelector('.modal-content');
const closeArticleModalBtn = articleModal.querySelector('.close-modal');
const articleBookmarkBtn = articleModal.querySelector('.bookmark-btn');
const articleShareBtn = articleModal.querySelector('.share-btn');

// Set to store bookmarked courses
const bookmarkedCourses = new Set();

// Function to populate modal with course details
function populateModal(courseId) {
    const course = courseData[courseId];
    if (!course) return;

    // Update modal content
    modalContent.querySelector('.job-title').textContent = course.title;
    modalContent.querySelector('.company-name').textContent = course.company;
    modalContent.querySelector('.company-location').textContent = course.location;
    modalContent.querySelector('.job-description').textContent = course.description;

    // Update learning outcomes
    const qualificationsList = modalContent.querySelector('.qualifications-list');
    qualificationsList.innerHTML = course.learningOutcomes
        .map(outcome => `<li>${outcome}</li>`)
        .join('');

    // Update requirements
    const requirementsList = modalContent.querySelector('.responsibilities-list');
    requirementsList.innerHTML = course.requirements
        .map(req => `<li>${req}</li>`)
        .join('');

    // Update company logo
    const companyLogo = modalContent.querySelector('.company-logo');
    companyLogo.src = `${course.company.toLowerCase()}-logo.png`;
    companyLogo.alt = course.company;

    // Update meta information
    const jobMeta = modalContent.querySelector('.job-meta');
    jobMeta.innerHTML = `
        <span class="meta-item">${course.type}</span>
        <span class="meta-item">${course.duration}</span>
        <span class="meta-item">${course.price}</span>
    `;

    // Update bookmark button state
    modalBookmarkBtn.classList.toggle('active', bookmarkedCourses.has(courseId));

    // Store the current course ID as a data attribute on the read article button
    readArticleBtn.dataset.courseId = courseId;

    // Show modal
    modal.style.display = 'flex';
}

// Function to populate article modal with content
function populateArticleModal(courseId) {
    const course = courseData[courseId];
    if (!course || !course.article) return;

    const article = course.article;

    // Update article modal content
    articleModalContent.querySelector('.article-title').textContent = article.title;
    articleModalContent.querySelector('.author-name').textContent = article.author;
    articleModalContent.querySelector('.publish-date').textContent = article.publishDate;
    
    // Update author avatar
    const authorAvatar = articleModalContent.querySelector('.author-avatar');
    authorAvatar.src = article.authorAvatar;
    authorAvatar.alt = article.author;

    // Update article tags
    const articleTags = articleModalContent.querySelector('.article-tags');
    articleTags.innerHTML = article.tags
        .map(tag => `<span class="article-tag">${tag}</span>`)
        .join('');

    // Update article content
    const articleContent = articleModalContent.querySelector('.article-content');
    articleContent.innerHTML = article.content;

    // Close the course details modal
    modal.style.display = 'none';

    // Show the article modal
    articleModal.classList.add('active');
}

// Event Listeners
document.addEventListener('DOMContentLoaded', function() {
    // Course card event listeners
    document.querySelectorAll('.job-card').forEach(card => {
        const detailsBtn = card.querySelector('.details');
        detailsBtn.addEventListener('click', () => {
            const courseId = getCourseIdFromCard(card);
            populateModal(courseId);
        });

        const bookmarkBtn = card.querySelector('.bookmark');
        bookmarkBtn.addEventListener('click', (e) => {
            e.stopPropagation();
            const courseId = getCourseIdFromCard(card);
            toggleBookmark(courseId, bookmarkBtn);
        });
    });

    // Read Article button click handler
    readArticleBtn.addEventListener('click', () => {
        const courseId = readArticleBtn.dataset.courseId;
        if (courseId) {
            populateArticleModal(courseId);
        }
    });

    // Close article modal when clicking on close button
    closeArticleModalBtn.addEventListener('click', () => {
        articleModal.classList.remove('active');
    });

    // Close article modal when clicking outside
    window.addEventListener('click', (e) => {
        if (e.target === articleModal) {
            articleModal.classList.remove('active');
        }
    });

    // Close course modal when clicking outside or on close button
    closeModalBtn.addEventListener('click', () => {
        modal.style.display = 'none';
    });

    window.addEventListener('click', (e) => {
        if (e.target === modal) {
            modal.style.display = 'none';
        }
    });

    // Modal bookmark button click handler
    modalBookmarkBtn.addEventListener('click', () => {
        const courseId = getCourseIdFromModal();
        const cardBookmarkBtn = findCardBookmarkBtn(courseId);
        toggleBookmark(courseId, cardBookmarkBtn);
        modalBookmarkBtn.classList.toggle('active');
    });

    // Share functionality
    shareBtn.addEventListener('click', async () => {
        const courseId = getCourseIdFromModal();
        const course = courseData[courseId];
        const shareData = {
            title: `${course.title} - ${course.company}`,
            text: `Check out this course: ${course.title} by ${course.company}`,
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

    // Article share functionality
    articleShareBtn.addEventListener('click', async () => {
        const courseId = readArticleBtn.dataset.courseId;
        const course = courseData[courseId];
        if (!course || !course.article) return;
        
        const shareData = {
            title: course.article.title,
            text: `Check out this article: ${course.article.title}`,
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

    fetchCourses();
});

// Helper function to get course ID from card
function getCourseIdFromCard(card) {
    const title = card.querySelector('.job-title h4').textContent;
    const company = card.querySelector('h3').textContent;
    return Object.keys(courseData).find(
        id => courseData[id].title === title && courseData[id].company === company
    );
}

// Toggle bookmark state
function toggleBookmark(courseId, btn) {
    if (bookmarkedCourses.has(courseId)) {
        bookmarkedCourses.delete(courseId);
        btn.classList.remove('active');
    } else {
        bookmarkedCourses.add(courseId);
        btn.classList.add('active');
    }
}

// Helper function to get course ID from modal
function getCourseIdFromModal() {
    const title = modalContent.querySelector('.job-title').textContent;
    const company = modalContent.querySelector('.company-name').textContent;
    return Object.keys(courseData).find(
        id => courseData[id].title === title && courseData[id].company === company
    );
}

// Helper function to find card bookmark button
function findCardBookmarkBtn(courseId) {
    const course = courseData[courseId];
    return Array.from(document.querySelectorAll('.job-card')).find(
        card => card.querySelector('.job-title h4').textContent === course.title
    ).querySelector('.bookmark');
}

// View toggle functionality
viewToggleButton.addEventListener('click', () => {
    jobCardsContainer.classList.toggle('list-layout');
    updateViewToggleState();
});

// Update view toggle state
function updateViewToggleState() {
    const isListView = jobCardsContainer.classList.contains('list-layout');
    gridIcon.style.display = isListView ? 'block' : 'none';
    listIcon.style.display = isListView ? 'none' : 'block';
}

// Set initial state
updateViewToggleState();