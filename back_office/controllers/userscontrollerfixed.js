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
    document.querySelectorAll('.filter-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            document.querySelector('.filter-btn.active').classList.remove('active');
            this.classList.add('active');
            // Actual filtering logic would go here
            console.log(`Filtering by: ${this.textContent}`);
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
});