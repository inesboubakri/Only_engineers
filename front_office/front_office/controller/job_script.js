document.addEventListener('DOMContentLoaded', () => {
    const modal = document.getElementById('jobDetailsModal');
    const detailButtons = document.querySelectorAll('.details');
    const closeModal = document.querySelector('.close-modal');

    // Add event listeners to all "Details" buttons
    detailButtons.forEach(button => {
        button.addEventListener('click', () => {
            // Retrieve job details from data attributes
            const jobTitle = button.dataset.title;
            const jobCompany = button.dataset.company;
            const jobLocation = button.dataset.location;
            const jobDescription = button.dataset.description;
            const jobType = button.dataset.type;
            const jobLogo = button.dataset.logo;
            const jobId = button.dataset.id;

            // Populate modal with job details
            modal.querySelector('.job-title').textContent = jobTitle;
            modal.querySelector('.company-name').textContent = jobCompany;
            modal.querySelector('.company-location').textContent = jobLocation;
            modal.querySelector('.job-description').textContent = jobDescription;
            modal.querySelector('.job-type').textContent = jobType;
            modal.querySelector('.company-logo').src = jobLogo;

            // Update modal buttons with the correct job ID
            modal.querySelector('.edit-job-btn').href = `edit_Job.php?id=${jobId}`;
            modal.querySelector('.delete-job-btn').href = `delete_Job.php?id=${jobId}`;
            modal.querySelector('.apply-now-btn').dataset.offreId = jobId;

            // Show the modal
            modal.classList.add('active');
        });
    });

    // Close modal when clicking the close button
    closeModal.addEventListener('click', () => {
        modal.classList.remove('active');
    });

    // Close modal when clicking outside the modal content
    modal.addEventListener('click', (e) => {
        const modalContent = modal.querySelector('.modal-content');
        if (!modalContent.contains(e.target)) {
            modal.classList.remove('active');
        }
    });
});

// JavaScript for search functionality
document.getElementById('searchInput').addEventListener('keyup', function () {
    const searchValue = this.value.toLowerCase();
    const tableRows = document.querySelectorAll('#applicationsTableBody tr');

    tableRows.forEach(row => {
        const rowText = row.textContent.toLowerCase();
        if (rowText.includes(searchValue)) {
            row.style.display = ''; // Show row
        } else {
            row.style.display = 'none'; // Hide row
        }
    });
});
 // JavaScript for sorting functionality
 const sortIcon = document.getElementById('sortIcon');
 const sortMenu = document.getElementById('sortMenu');

 // Toggle sort menu visibility
 sortIcon.addEventListener('click', () => {
     sortMenu.style.display = sortMenu.style.display === 'block' ? 'none' : 'block';
 });

 // Sort table rows based on selected option
 sortMenu.addEventListener('click', (event) => {
     if (event.target.tagName === 'BUTTON') {
         const sortBy = event.target.dataset.sort;
         const tableRows = Array.from(document.querySelectorAll('#applicationsTableBody tr'));

         tableRows.sort((a, b) => {
             const aText = a.querySelector(`td:nth-child(${getColumnIndex(sortBy)})`).textContent.toLowerCase();
             const bText = b.querySelector(`td:nth-child(${getColumnIndex(sortBy)})`).textContent.toLowerCase();

             return aText.localeCompare(bText);
         });
         const tableBody = document.getElementById('applicationsTableBody');
                tableBody.innerHTML = ''; // Clear the table body
                tableRows.forEach(row => tableBody.appendChild(row)); // Append sorted rows

                // Hide the sort menu after sorting
                sortMenu.style.display = 'none';
            }
        });

        // Helper function to get column index based on sort option
        function getColumnIndex(sortBy) {
            switch (sortBy) {
                case 'firstName':
                    return 2; // First Name column index
                case 'email':
                    return 4; // Email column index
                case 'role':
                    return 5; // Role column index
                default:
                    return 1; // Default to ID column
            }
        }

