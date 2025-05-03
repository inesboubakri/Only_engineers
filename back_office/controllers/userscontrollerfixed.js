/**
 * Users Controller Fixed
 * Handles user management functionality for the users.php page
 */

// Initialize theme based on user preference or default to light
function initTheme() {
    const savedTheme = localStorage.getItem('theme') || 'light';
    document.body.className = savedTheme + '-theme';
    
    // Set the toggle switch based on the theme
    const themeToggle = document.getElementById('theme-toggle');
    if (themeToggle) {
        themeToggle.checked = savedTheme === 'dark';
    }
}

// Toggle between light and dark themes
function toggleTheme() {
    const currentTheme = document.body.className.includes('light') ? 'light' : 'dark';
    const newTheme = currentTheme === 'light' ? 'dark' : 'light';
    
    document.body.className = newTheme + '-theme';
    localStorage.setItem('theme', newTheme);
}

// Initialize table filtering functionality
function initTableFiltering() {
    const filterButtons = document.querySelectorAll('.filter-btn');
    const tableRows = document.querySelectorAll('.users-table tbody tr');
    
    // Add data attributes to each row for filtering
    tableRows.forEach(row => {
        const isAdmin = row.querySelector('td:nth-child(8) .admin-badge') !== null;
        const isBanned = row.querySelector('.ban-btn').classList.contains('unban');
        
        // Set data attributes for filtering
        row.setAttribute('data-is-admin', isAdmin ? '1' : '0');
        row.setAttribute('data-is-banned', isBanned ? '1' : '0');
    });
    
    filterButtons.forEach(btn => {
        btn.addEventListener('click', function() {
            // Update active button
            document.querySelector('.filter-btn.active').classList.remove('active');
            this.classList.add('active');
            
            const filterType = this.textContent.trim().toLowerCase();
            console.log(`Filtering by: ${filterType}`);
            
            // Apply filter based on button clicked
            tableRows.forEach(row => {
                const isAdmin = row.getAttribute('data-is-admin') === '1';
                const isBanned = row.getAttribute('data-is-banned') === '1';
                
                // Show/hide rows based on filter
                switch(filterType) {
                    case 'all':
                        row.style.display = '';
                        break;
                    case 'users':
                        row.style.display = !isAdmin ? '' : 'none';
                        break;
                    case 'admins':
                        row.style.display = isAdmin ? '' : 'none';
                        break;
                    case 'banned':
                        row.style.display = isBanned ? '' : 'none';
                        break;
                    case 'unbanned':
                        row.style.display = !isBanned ? '' : 'none';
                        break;
                    default:
                        row.style.display = '';
                }
            });
            
            // Show message if no results
            let visibleRows = 0;
            tableRows.forEach(row => {
                if (row.style.display !== 'none') {
                    visibleRows++;
                }
            });
            
            // Find or create "no results" message
            let noResultsRow = document.querySelector('.no-results-row');
            if (visibleRows === 0) {
                if (!noResultsRow) {
                    noResultsRow = document.createElement('tr');
                    noResultsRow.className = 'no-results-row';
                    const tdElement = document.createElement('td');
                    tdElement.setAttribute('colspan', '9');
                    tdElement.style.textAlign = 'center';
                    tdElement.textContent = 'No matching users found';
                    noResultsRow.appendChild(tdElement);
                    document.querySelector('.users-table tbody').appendChild(noResultsRow);
                }
                noResultsRow.style.display = '';
            } else if (noResultsRow) {
                noResultsRow.style.display = 'none';
            }
        });
    });
}

// Initialize edit button functionality
function initEditButtons() {
    document.querySelectorAll('.edit-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const userId = this.getAttribute('data-id');
            // Redirect to edit page in model directory
            window.location.href = `../model/edit_user.php?id=${userId}`;
        });
    });
}

// Initialize delete button functionality
function initDeleteButtons() {
    document.querySelectorAll('.delete-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const userId = this.getAttribute('data-id');
            if (confirm(`Are you sure you want to delete user ${userId}?`)) {
                // Use fetch to call the delete API
                fetch('../model/delete_user.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `user_id=${userId}`
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Remove the row from the table
                        this.closest('tr').remove();
                        alert(data.message);
                        
                        // Update user counts
                        const totalUsersElement = document.querySelector('.stats-card .service-amount');
                        if (totalUsersElement) {
                            const currentCount = parseInt(totalUsersElement.textContent);
                            if (!isNaN(currentCount)) {
                                totalUsersElement.textContent = currentCount - 1;
                            }
                        }
                    } else {
                        alert('Error: ' + data.message);
                    }
                })
                .catch(error => {
                    alert('Error: ' + error.message);
                });
            }
        });
    });
}

// Initialize ban button functionality
function initBanButtons() {
    console.log('Initializing ban buttons...');
    const banButtons = document.querySelectorAll('.ban-btn');
    console.log(`Found ${banButtons.length} ban buttons`);
    
    banButtons.forEach(btn => {
        btn.addEventListener('click', function() {
            const userId = this.getAttribute('data-id');
            const isBanned = this.getAttribute('data-banned') === '1';
            const actionText = isBanned ? 'unban' : 'ban';
            
            console.log(`Ban button clicked for user ${userId}, current status: ${isBanned ? 'banned' : 'not banned'}`);
            
            if (confirm(`Are you sure you want to ${actionText} user ${userId}?`)) {
                console.log(`Sending request to toggle ban status for user ${userId}`);
                
                // Use fetch to call the toggle_ban_user API
                fetch('../model/toggle_ban_user.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `user_id=${userId}`
                })
                .then(response => {
                    console.log('Received response:', response);
                    return response.json();
                })
                .then(data => {
                    console.log('Processed data:', data);
                    if (data.success) {
                        // Update button text and appearance
                        this.textContent = data.is_banned ? 'Unban' : 'Ban';
                        this.classList.toggle('unban', data.is_banned);
                        this.setAttribute('data-banned', data.is_banned);
                        
                        // Show success message
                        showToast(data.message);
                        console.log(`Successfully ${actionText}ed user ${userId}`);
                    } else {
                        alert('Error: ' + data.message);
                        console.error('Error:', data.message);
                    }
                })
                .catch(error => {
                    alert('Error: ' + error.message);
                    console.error('Fetch error:', error);
                });
            }
        });
    });
}

// Initialize statistics button functionality
function initStatsButton() {
    const statsButton = document.querySelector('.stats-button');
    if (statsButton) {
        statsButton.addEventListener('click', function() {
            // Navigate to the statistics page
            window.location.href = 'user_statistics.php';
        });
    }
}

// Function to show a toast notification
function showToast(message, type = 'success') {
    const toast = document.createElement('div');
    toast.className = `toast ${type}`;
    toast.textContent = message;
    
    document.body.appendChild(toast);
    
    // Show toast
    setTimeout(() => {
        toast.classList.add('show');
    }, 10);
    
    // Hide and remove toast after 3 seconds
    setTimeout(() => {
        toast.classList.remove('show');
        setTimeout(() => {
            toast.remove();
        }, 300);
    }, 3000);
}

// Initialize search functionality
function initSearch() {
    console.log('Initializing search functionality');
    const searchInput = document.querySelector('.search-box input');
    const tableContainer = document.querySelector('.table-container');
    const rows = document.querySelectorAll('.users-table tbody tr');
    
    // Add search count element
    const searchCountElement = document.createElement('div');
    searchCountElement.className = 'search-count';
    document.querySelector('.search-box').appendChild(searchCountElement);
    
    if (!searchInput) {
        console.error('Search input not found');
        return;
    }

    searchInput.addEventListener('input', function() {
        const searchTerm = this.value.trim().toLowerCase();
        let matchCount = 0;
        
        // Remove existing highlights
        const highlights = document.querySelectorAll('.highlight');
        highlights.forEach(el => {
            const parent = el.parentNode;
            parent.replaceChild(document.createTextNode(el.textContent), el);
            parent.normalize();
        });
        
        // Remove match class from all rows
        rows.forEach(row => {
            row.classList.remove('match');
        });
        
        if (searchTerm === '') {
            // Reset search state
            tableContainer.classList.remove('search-active');
            searchCountElement.textContent = '';
            return;
        }

        tableContainer.classList.add('search-active');
        
        rows.forEach(row => {
            let hasMatch = false;
            const cells = row.querySelectorAll('td');
            
            cells.forEach(cell => {
                // Skip image cells
                if (cell.querySelector('.user-avatar')) return;
                // Skip action button cells
                if (cell.querySelector('.action-btn-group')) return;
                
                const content = cell.textContent;
                if (content.toLowerCase().includes(searchTerm)) {
                    hasMatch = true;
                    // Highlight the matching text
                    cell.innerHTML = highlightText(content, searchTerm);
                }
            });
            
            if (hasMatch) {
                row.classList.add('match');
                matchCount++;
            }
        });
        
        // Update match count
        searchCountElement.textContent = `${matchCount} match${matchCount !== 1 ? 'es' : ''}`;
    });
    
    // Add click functionality to search icon
    const searchIcon = document.querySelector('.search-icon');
    if (searchIcon) {
        searchIcon.addEventListener('click', function() {
            searchInput.focus();
        });
    }

    // Add clear search on Escape key
    searchInput.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            this.value = '';
            this.dispatchEvent(new Event('input'));
        }
    });
}

// Helper function to highlight text matches
function highlightText(text, searchTerm) {
    if (!searchTerm) return text;
    
    const regex = new RegExp(escapeRegExp(searchTerm), 'gi');
    return text.replace(regex, match => `<span class="highlight">${match}</span>`);
}

// Helper function to escape special regex characters
function escapeRegExp(string) {
    return string.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
}

// Initialize export report functionality
function initExportReportButton() {
    const reportButton = document.querySelector('.service-button');
    const exportModal = document.getElementById('exportModal');
    const modalClose = document.getElementById('modalClose');
    const cancelExport = document.getElementById('cancelExport');
    const confirmExport = document.getElementById('confirmExport');
    const exportOptions = document.querySelectorAll('.export-option');
    
    // Selected export format (default: excel)
    let selectedFormat = 'excel';
    
    // Show modal when clicking Generate Report button
    if (reportButton) {
        reportButton.addEventListener('click', function() {
            exportModal.classList.add('show');
        });
    }
    
    // Handle export option selection
    exportOptions.forEach(option => {
        option.addEventListener('click', function() {
            // Remove selected class from all options
            exportOptions.forEach(opt => opt.classList.remove('selected'));
            
            // Add selected class to clicked option
            this.classList.add('selected');
            
            // Update selected format
            selectedFormat = this.getAttribute('data-format');
        });
    });
    
    // Default select the first option (Excel)
    if (exportOptions.length > 0) {
        exportOptions[0].classList.add('selected');
    }
    
    // Close modal functions
    function closeExportModal() {
        exportModal.classList.remove('show');
    }
    
    // Close on X button click
    if (modalClose) {
        modalClose.addEventListener('click', closeExportModal);
    }
    
    // Close on Cancel button click
    if (cancelExport) {
        cancelExport.addEventListener('click', closeExportModal);
    }
    
    // Handle export on Confirm button click
    if (confirmExport) {
        confirmExport.addEventListener('click', function() {
            // Determine export URL based on format
            let exportUrl = '';
            
            if (selectedFormat === 'excel') {
                exportUrl = '../model/export_users_excel.php';
            } else if (selectedFormat === 'pdf') {
                exportUrl = '../model/export_users_pdf.php';
            }
            
            // Initiate download by opening URL in a new window/tab
            if (exportUrl) {
                window.open(exportUrl, '_blank');
                closeExportModal();
                showToast(`Exporting users report in ${selectedFormat.toUpperCase()} format...`);
            }
        });
    }
}

// Initialize all functionality when the DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    // Initialize theme
    initTheme();
    
    // Set up theme toggle event listener
    const themeToggle = document.getElementById('theme-toggle');
    if (themeToggle) {
        themeToggle.addEventListener('change', toggleTheme);
    }

    // Initialize table filtering
    initTableFiltering();
    
    // Initialize edit buttons
    initEditButtons();
    
    // Initialize delete buttons
    initDeleteButtons();
    
    // Initialize ban buttons
    initBanButtons();
    
    // Initialize search functionality
    initSearch();

    // Initialize statistics button
    initStatsButton();

    // Initialize export report button
    initExportReportButton();
});