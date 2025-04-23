/**
 * Projects Controller
 * Handles project management functionality
 */

// Initialize the dashboard model
const dashboardModel = new DashboardModel();

// Initialize theme based on user preference or default to light
function initTheme() {
    const savedTheme = localStorage.getItem('theme') || 'light';
    document.body.className = savedTheme + '-theme';
    
    // Set the toggle switch based on the theme
    const themeToggle = document.getElementById('theme-toggle');
    themeToggle.checked = savedTheme === 'dark';
}

// Toggle between light and dark themes
function toggleTheme() {
    const currentTheme = document.body.className.includes('light') ? 'light' : 'dark';
    const newTheme = currentTheme === 'light' ? 'dark' : 'light';
    
    document.body.className = newTheme + '-theme';
    localStorage.setItem('theme', newTheme);
}

// Initialize navigation between dashboard and other pages
function initNavigation() {
    const navItems = document.querySelectorAll('.sidebar-nav li');
    
    navItems.forEach(item => {
        item.addEventListener('click', function() {
            // Get the view name from the nav item text
            const viewName = this.querySelector('span').textContent.toLowerCase();
            
            // Navigate to the appropriate page
            if (viewName === 'dashboard') {
                window.location.href = 'dashboard.html';
            } else if (viewName === 'users') {
                window.location.href = 'users.html';
            } else if (viewName === 'projects') {
                window.location.href = 'projects.html';
            }
            // Additional pages can be added here
        });
    });
}

// Initialize project table filters
function initProjectTableFilters() {
    const filterButtons = document.querySelectorAll('.filter-pill');
    
    filterButtons.forEach(button => {
        button.addEventListener('click', function() {
            // Remove active class from all filter buttons
            filterButtons.forEach(btn => btn.classList.remove('active'));
            
            // Add active class to clicked button
            this.classList.add('active');
            
            // Get the filter value
            const filterValue = this.textContent.trim();
            
            // Filter the table rows based on the selected filter
            filterProjectTable(filterValue);
        });
    });
}

// Filter project table based on selected category
function filterProjectTable(filterValue) {
    // Filter table rows
    const tableRows = document.querySelectorAll('.projects-table tbody tr');
    let filteredCount = 0;
    
    tableRows.forEach(row => {
        const category = row.querySelector('td:nth-child(4)').textContent;
        
        if (filterValue === 'All Projects') {
            row.style.display = '';
            filteredCount++;
        } else if (category.includes(filterValue)) {
            row.style.display = '';
            filteredCount++;
        } else {
            row.style.display = 'none';
        }
    });
    
    // Also filter grid cards if they exist
    const gridCards = document.querySelectorAll('.project-card');
    if (gridCards.length > 0) {
        gridCards.forEach(card => {
            const category = card.querySelector('.item-value[data-category]').textContent;
            
            if (filterValue === 'All Projects') {
                card.style.display = '';
            } else if (category.includes(filterValue)) {
                card.style.display = '';
            } else {
                card.style.display = 'none';
            }
        });
    }
    
    // Update UI to show filter results
    updateFilterResults(filteredCount);
}

// Initialize view toggle
function initViewToggle() {
    const viewButtons = document.querySelectorAll('.view-control');
    const tableContainer = document.querySelector('.projects-table-container');
    
    // Get saved view preference or default to 'list'
    const savedView = localStorage.getItem('projectsView') || 'list';
    
    // Set initial view based on saved preference
    setActiveView(savedView);
    
    viewButtons.forEach(button => {
        button.addEventListener('click', function() {
            // Get the view type from data attribute
            const viewType = this.getAttribute('data-view');
            
            // Update active button
            viewButtons.forEach(btn => btn.classList.remove('active'));
            this.classList.add('active');
            
            // Save preference
            localStorage.setItem('projectsView', viewType);
            
            // Switch between views
            setActiveView(viewType);
        });
    });
    
    function setActiveView(viewType) {
        // Find the button for this view and make it active
        viewButtons.forEach(btn => {
            if (btn.getAttribute('data-view') === viewType) {
                btn.classList.add('active');
            } else {
                btn.classList.remove('active');
            }
        });
        
        // Apply appropriate class to container
        if (viewType === 'list') {
            tableContainer.classList.remove('grid-view');
            tableContainer.classList.add('list-view');
            
            // Show table and hide grid
            const tableElement = document.querySelector('.projects-table');
            const gridElement = document.querySelector('.projects-grid');
            
            if (tableElement) tableElement.style.display = '';
            if (gridElement) gridElement.style.display = 'none';
        } else {
            tableContainer.classList.remove('list-view');
            tableContainer.classList.add('grid-view');
            
            // Show grid and hide table
            const tableElement = document.querySelector('.projects-table');
            const gridElement = document.querySelector('.projects-grid');
            
            if (tableElement) tableElement.style.display = 'none';
            
            // If grid view is not already created, create it
            if (!gridElement) {
                createGridView();
            } else {
                // Show the grid and make sure it maintains the same filter state
                gridElement.style.display = 'grid';
                syncFilterStateToGridView();
            }
        }
    }
    
    // Create grid view from table data
    function createGridView() {
        const tableRows = document.querySelectorAll('.projects-table tbody tr');
        if (tableRows.length === 0) return;
        
        // Create grid container
        const gridContainer = document.createElement('div');
        gridContainer.className = 'projects-grid';
        
        // Convert each table row to a grid card
        tableRows.forEach(row => {
            // Extract data from the row
            const avatarSrc = row.querySelector('.project-avatar img').src;
            const projectName = row.querySelector('.project-name').textContent;
            const projectId = row.querySelector('.project-id').textContent;
            const deadline = row.querySelector('td:nth-child(2)').textContent;
            const budget = row.querySelector('td:nth-child(3)').textContent;
            const category = row.querySelector('td:nth-child(4)').textContent;
            const statusElement = row.querySelector('.status');
            const status = statusElement.textContent;
            const statusClass = statusElement.classList[1]; // Get the status class (in-progress, planning, etc.)
            
            // Create grid card
            const card = document.createElement('div');
            card.className = 'project-card';
            card.innerHTML = `
                <div class="card-header">
                    <div class="project-avatar">
                        <img src="${avatarSrc}" alt="${projectName}">
                    </div>
                    <div class="project-details">
                        <div class="project-name">${projectName}</div>
                        <div class="project-id">${projectId}</div>
                    </div>
                    <div class="action-menu-button">•••</div>
                </div>
                <div class="card-body">
                    <div class="item">
                        <div class="item-label">Deadline</div>
                        <div class="item-value">${deadline}</div>
                    </div>
                    <div class="item">
                        <div class="item-label">Budget</div>
                        <div class="item-value">${budget}</div>
                    </div>
                    <div class="item">
                        <div class="item-label">Category</div>
                        <div class="item-value" data-category>${category}</div>
                    </div>
                    <div class="item">
                        <div class="item-label">Status</div>
                        <div class="item-value">
                            <span class="status ${statusClass}">${status}</span>
                        </div>
                    </div>
                </div>
            `;
            
            // Add the card to the grid
            gridContainer.appendChild(card);
        });
        
        // Insert the grid into the DOM
        const tableContainer = document.querySelector('.projects-table-container');
        tableContainer.appendChild(gridContainer);
    }
    
    // Sync the current filter state to the grid view
    function syncFilterStateToGridView() {
        const activeFilter = document.querySelector('.filter-pill.active').textContent.trim();
        filterProjectTable(activeFilter);
    }
}

// Initialize Add New Project button
function initAddProjectButton() {
    const addButton = document.querySelector('.add-project-button');
    
    addButton.addEventListener('click', function() {
        // Show modal or form to add new project
        alert('Add new project functionality will be implemented here');
    });
}

// Initialize Generate Report button
function initReportButton() {
    const reportButton = document.querySelector('.primary-button');
    
    reportButton.addEventListener('click', function() {
        // Show report generation modal
        alert('Generate projects report functionality will be implemented here');
    });
}

// Initialize action buttons (more buttons)
function initActionButtons() {
    const moreButtons = document.querySelectorAll('.action-menu-button');
    
    moreButtons.forEach(button => {
        button.addEventListener('click', function(event) {
            // Prevent event from bubbling up
            event.stopPropagation();
            
            // Get the project name
            let projectName;
            if (this.closest('.project-card')) {
                projectName = this.closest('.project-card').querySelector('.project-name').textContent;
            } else {
                projectName = this.closest('tr').querySelector('.project-name').textContent;
            }
            
            // Show action menu
            showActionMenu(this, projectName);
        });
    });
}

// Show action menu for a project
function showActionMenu(element, projectName) {
    // Remove any existing action menus
    const existingMenu = document.querySelector('.action-menu');
    if (existingMenu) {
        existingMenu.remove();
    }
    
    // Create action menu
    const actionMenu = document.createElement('div');
    actionMenu.className = 'action-menu';
    actionMenu.innerHTML = `
        <div class="action-item edit" data-action="edit">
            <span class="action-icon">✏️</span>
            <span class="action-text">Edit Project</span>
        </div>
        <div class="action-item view" data-action="view">
            <span class="action-icon">👁️</span>
            <span class="action-text">View Details</span>
        </div>
        <div class="action-item delete" data-action="delete">
            <span class="action-icon">🗑️</span>
            <span class="action-text">Delete Project</span>
        </div>
    `;
    
    // Position the menu near the clicked button
    const rect = element.getBoundingClientRect();
    actionMenu.style.position = 'absolute';
    actionMenu.style.top = `${rect.bottom}px`;
    actionMenu.style.left = `${rect.left}px`;
    actionMenu.style.zIndex = '1000';
    
    // Add the menu to the DOM
    document.body.appendChild(actionMenu);
    
    // Add event listeners to action items
    actionMenu.querySelector('[data-action="edit"]').addEventListener('click', () => {
        modifyProject(element, projectName);
        actionMenu.remove();
    });
    
    actionMenu.querySelector('[data-action="view"]').addEventListener('click', () => {
        viewProjectDetails(element, projectName);
        actionMenu.remove();
    });
    
    actionMenu.querySelector('[data-action="delete"]').addEventListener('click', () => {
        deleteProject(element, projectName);
        actionMenu.remove();
    });
    
    // Close the menu when clicking outside
    document.addEventListener('click', function closeMenu(e) {
        if (!actionMenu.contains(e.target) && e.target !== element) {
            actionMenu.remove();
            document.removeEventListener('click', closeMenu);
        }
    });
}

// Modify project
function modifyProject(element, projectName) {
    // Placeholder for edit functionality
    alert(`Edit project: ${projectName}`);
}

// View project details
function viewProjectDetails(element, projectName) {
    // Placeholder for view functionality
    alert(`View details for project: ${projectName}`);
}

// Delete project
function deleteProject(element, projectName) {
    // Placeholder for delete functionality
    if (confirm(`Are you sure you want to delete project: ${projectName}?`)) {
        // Get the project row or card
        let projectElement;
        if (element.closest('.project-card')) {
            projectElement = element.closest('.project-card');
        } else {
            projectElement = element.closest('tr');
        }
        
        // Remove the element with animation
        projectElement.style.opacity = '0';
        projectElement.style.transform = 'scale(0.8)';
        projectElement.style.transition = 'all 0.3s ease';
        
        setTimeout(() => {
            projectElement.remove();
            showToast(`Project ${projectName} has been deleted`);
        }, 300);
    }
}

// Show toast notification
function showToast(message) {
    // Create toast element if it doesn't exist
    let toast = document.querySelector('.toast');
    if (!toast) {
        toast = document.createElement('div');
        toast.className = 'toast';
        document.body.appendChild(toast);
    }
    
    // Set message and show toast
    toast.textContent = message;
    toast.classList.add('show');
    
    // Hide toast after 3 seconds
    setTimeout(() => {
        toast.classList.remove('show');
    }, 3000);
}

// Update filter results count display
function updateFilterResults(count) {
    const filterResultsSpan = document.querySelector('.filter-results');
    if (filterResultsSpan) {
        filterResultsSpan.textContent = `${count} projects`;
    }
}

// Initialize projects page
function initProjectsPage() {
    initTheme();
    document.getElementById('theme-toggle').addEventListener('change', toggleTheme);
    
    initNavigation();
    initProjectTableFilters();
    initViewToggle();
    initAddProjectButton();
    initReportButton();
    initActionButtons();
}

// Initialize when the DOM is fully loaded
document.addEventListener('DOMContentLoaded', initProjectsPage); 