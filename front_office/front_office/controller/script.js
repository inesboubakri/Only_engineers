document.addEventListener('DOMContentLoaded', () => {
    // View Toggle Functionality
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

        updateViewToggleState();

        viewToggleButton.addEventListener('click', () => {
            console.log('Toggle button clicked');
            jobCardsContainer.classList.toggle('list-layout');
            updateViewToggleState();
        });
    } else {
        console.error('View toggle elements not found');
    }

    // Clear All Filters
    const clearAllButton = document.querySelector('.clear-all');
    if (clearAllButton) {
        clearAllButton.addEventListener('click', () => {
            document.querySelectorAll('.filters input[type="radio"]').forEach(input => {
                input.checked = false;
            });
        });
    }

    // Modal and Details Button Functionality
    const modal = document.getElementById('jobDetailsModal');
    const detailButtons = document.querySelectorAll('.details');
    const closeModal = modal?.querySelector('.close-modal');
    const applyButton = modal?.querySelector('.apply-now-btn');

    if (!modal || !closeModal || !applyButton) {
        console.error('Modal or required modal elements not found');
        return;
    }

    detailButtons.forEach(button => {
        button.addEventListener('click', () => {
            console.log('Details button clicked'); // Debug log
            try {
                const jobTitle = button.dataset.title;
                const jobCompany = button.dataset.company;
                const jobLocation = button.dataset.location;
                const jobDescription = button.dataset.description;
                const jobType = button.dataset.type;
                const jobLogo = button.dataset.logo;
                const jobId = button.dataset.id;

                // Populate modal
                modal.querySelector('.job-title').textContent = jobTitle || 'N/A';
                modal.querySelector('.company-name').textContent = jobCompany || 'N/A';
                modal.querySelector('.company-location').textContent = jobLocation || 'N/A';
                modal.querySelector('.job-description').textContent = jobDescription || 'No description available';
                modal.querySelector('.job-type').textContent = jobType || 'N/A';
                modal.querySelector('.company-logo').src = jobLogo || '';

                // Update apply button
                applyButton.dataset.offreId = jobId;

                // Show modal
                modal.classList.add('active');
            } catch (error) {
                console.error('Error populating modal:', error);
            }
        });
    });

    // Close modal
    closeModal.addEventListener('click', () => {
    modal.classList.remove('active');
    });

    // Close modal on outside click
    modal.addEventListener('click', (e) => {
        const modalContent = modal.querySelector('.modal-content');
        if (!modalContent.contains(e.target)) {
            modal.classList.remove('active');
        }
    });

    // Apply button redirect
    applyButton.addEventListener('click', () => {
        const offreId = applyButton.dataset.offreId;
        if (offreId) {
            window.location.href = `../controller/controller_apply.php?id=${offreId}`;
        } else {
            console.error('No job ID found for apply button');
        }
    });

    // Share button functionality
    const shareButton = modal.querySelector('.share-btn');
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
                }).catch(err => console.error('Share failed:', err));
            } else {
                navigator.clipboard.writeText(shareText + ' ' + window.location.href)
                    .then(() => alert('Link copied to clipboard!'))
                    .catch(err => console.error('Failed to copy:', err));
            }
        });
    }
});

// Toggle Other Job Title Field (if used in a form elsewhere)
function toggleOtherField() {
    const selectBox = document.getElementById('JobTitle');
    const otherField = document.getElementById('OtherJobTitle');
    if (selectBox && otherField) {
        otherField.style.display = selectBox.value === 'Other' ? 'inline-block' : 'none';
    }
}