// Simple toggle view functionality for articles page
document.addEventListener('DOMContentLoaded', function() {
    // Get the toggle button by ID
    const toggleButton = document.getElementById('articleViewToggle');
    
    // Get the job cards container
    const cardsContainer = document.querySelector('.job-cards');
    
    // Get the icons
    const gridIcon = toggleButton ? toggleButton.querySelector('.grid-icon') : null;
    const listIcon = toggleButton ? toggleButton.querySelector('.list-icon') : null;
    
    // Check if all elements exist
    if (toggleButton && cardsContainer && gridIcon && listIcon) {
        console.log('Toggle elements found, initializing toggle functionality');
        
        // Function to update the icon display
        function updateIcons(isListLayout) {
            gridIcon.style.display = isListLayout ? 'none' : 'block';
            listIcon.style.display = isListLayout ? 'block' : 'none';
        }
        
        // Add click event listener
        toggleButton.addEventListener('click', function() {
            console.log('Toggle button clicked');
            
            // Toggle the list-layout class
            cardsContainer.classList.toggle('list-layout');
            
            // Check if list layout is active
            const isListLayout = cardsContainer.classList.contains('list-layout');
            
            // Update icons
            updateIcons(isListLayout);
            
            console.log('List layout active:', isListLayout);
        });
        
        // Initialize the icons based on current state
        updateIcons(cardsContainer.classList.contains('list-layout'));
    } else {
        console.error('One or more toggle elements not found');
    }
});

/* Toggle notification panel visibility */
document.addEventListener('DOMContentLoaded', function() {
    const notificationIcon = document.querySelector('.notification-icon');
    const notificationPanel = document.querySelector('.notification-panel');
    const notificationsList = document.querySelector('.notifications-list');
    const emptyNotification = document.querySelector('.empty-notification');
    
    // Function to toggle notification panel
    function toggleNotificationPanel() {
        notificationPanel.classList.toggle('show');
        
        // If panel is now visible, load notifications
        if (notificationPanel.classList.contains('show')) {
            loadNotifications();
        }
    }
    
    // Add click event to notification icon
    if (notificationIcon) {
        notificationIcon.addEventListener('click', function(e) {
            e.stopPropagation();
            toggleNotificationPanel();
        });
    }
    
    // Close panel when clicking outside
    document.addEventListener('click', function(e) {
        if (notificationPanel && notificationPanel.classList.contains('show')) {
            if (!notificationPanel.contains(e.target) && e.target !== notificationIcon) {
                notificationPanel.classList.remove('show');
            }
        }
    });
    
    // Stop propagation for clicks inside the panel
    if (notificationPanel) {
        notificationPanel.addEventListener('click', function(e) {
            e.stopPropagation();
        });
    }
    
    // Function to load notifications
    function loadNotifications() {
        if (!notificationsList) return;
        
        // Display loading state
        notificationsList.innerHTML = '<div class="loading">Loading notifications...</div>';
        
        fetch('../view/get_notifications.php')
            .then(response => {
                if (!response.ok) {
                    throw new Error('Network response was not ok');
                }
                return response.json();
            })
            .then(data => {
                // Clear loading message
                notificationsList.innerHTML = '';
                
                // Check if we have notifications
                if (data.success && data.notifications && data.notifications.length > 0) {
                    // Hide empty state
                    if (emptyNotification) {
                        emptyNotification.style.display = 'none';
                    }
                    
                    // Loop through notifications and add them to the list
                    data.notifications.forEach(notification => {
                        const notificationItem = document.createElement('div');
                        notificationItem.className = 'notification-item';
                        notificationItem.setAttribute('data-id', notification.id);
                        
                        // Create notification content based on type
                        let content = '';
                        switch (notification.type) {
                            case 'connection_request':
                                content = `
                                    <div class="notification-avatar">
                                        <img src="${notification.sender_image || '../ressources/profil.jpg'}" alt="User">
                                    </div>
                                    <div class="notification-content">
                                        <p><strong>${notification.sender_name}</strong> sent you a connection request</p>
                                        <div class="notification-actions">
                                            <button class="accept-btn" data-id="${notification.id}" data-sender="${notification.sender_id}">Accept</button>
                                            <button class="decline-btn" data-id="${notification.id}" data-sender="${notification.sender_id}">Decline</button>
                                        </div>
                                        <span class="notification-time">${notification.time_ago}</span>
                                    </div>
                                `;
                                break;
                                
                            case 'connection_accepted':
                                content = `
                                    <div class="notification-avatar">
                                        <img src="${notification.sender_image || '../ressources/profil.jpg'}" alt="User">
                                    </div>
                                    <div class="notification-content">
                                        <p><strong>${notification.sender_name}</strong> accepted your connection request</p>
                                        <span class="notification-time">${notification.time_ago}</span>
                                    </div>
                                `;
                                break;
                                
                            case 'new_follower':
                                content = `
                                    <div class="notification-avatar">
                                        <img src="${notification.sender_image || '../ressources/profil.jpg'}" alt="User">
                                    </div>
                                    <div class="notification-content">
                                        <p><strong>${notification.sender_name}</strong> started following you</p>
                                        <span class="notification-time">${notification.time_ago}</span>
                                    </div>
                                `;
                                break;
                                
                            default:
                                content = `
                                    <div class="notification-avatar">
                                        <img src="${notification.sender_image || '../ressources/profil.jpg'}" alt="User">
                                    </div>
                                    <div class="notification-content">
                                        <p>${notification.message}</p>
                                        <span class="notification-time">${notification.time_ago}</span>
                                    </div>
                                `;
                        }
                        
                        notificationItem.innerHTML = content;
                        notificationsList.appendChild(notificationItem);
                    });
                    
                    // Add event listeners for accept/decline buttons
                    addNotificationButtonListeners();
                    
                } else {
                    // Show empty state
                    if (emptyNotification) {
                        emptyNotification.style.display = 'block';
                    } else {
                        notificationsList.innerHTML = '<div class="empty-notification">No notifications to display</div>';
                    }
                }
            })
            .catch(error => {
                console.error('Error fetching notifications:', error);
                notificationsList.innerHTML = '<div class="error">Error loading notifications. Please try again.</div>';
            });
    }
    
    // Function to add event listeners to notification action buttons
    function addNotificationButtonListeners() {
        // Accept connection request buttons
        document.querySelectorAll('.accept-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                const notificationId = this.dataset.id;
                const senderId = this.dataset.sender;
                
                fetch('../model/networking/connections.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `action=accept_request&user_id=${senderId}&notification_id=${notificationId}`
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Remove this notification item
                        const notificationItem = this.closest('.notification-item');
                        notificationItem.remove();
                        
                        // Show success message
                        showToast('Success', 'Connection request accepted', 'success');
                        
                        // Check if notifications list is now empty
                        if (notificationsList.children.length === 0) {
                            notificationsList.innerHTML = '<div class="empty-notification">No notifications to display</div>';
                        }
                    } else {
                        showToast('Error', data.message, 'error');
                    }
                })
                .catch(error => {
                    console.error('Error accepting connection:', error);
                    showToast('Error', 'Failed to accept request', 'error');
                });
            });
        });
        
        // Decline connection request buttons
        document.querySelectorAll('.decline-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                const notificationId = this.dataset.id;
                const senderId = this.dataset.sender;
                
                fetch('../model/networking/connections.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `action=reject_request&user_id=${senderId}&notification_id=${notificationId}`
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Remove this notification item
                        const notificationItem = this.closest('.notification-item');
                        notificationItem.remove();
                        
                        // Show success message
                        showToast('Success', 'Connection request declined', 'success');
                        
                        // Check if notifications list is now empty
                        if (notificationsList.children.length === 0) {
                            notificationsList.innerHTML = '<div class="empty-notification">No notifications to display</div>';
                        }
                    } else {
                        showToast('Error', data.message, 'error');
                    }
                })
                .catch(error => {
                    console.error('Error declining connection:', error);
                    showToast('Error', 'Failed to decline request', 'error');
                });
            });
        });
    }
    
    // Toast notification function
    function showToast(title, message, type = 'info') {
        const toast = document.createElement('div');
        toast.className = `toast ${type}`;
        toast.innerHTML = `
            <div class="toast-header">
                <strong>${title}</strong>
                <button type="button" class="close-btn">&times;</button>
            </div>
            <div class="toast-body">${message}</div>
        `;
        
        document.body.appendChild(toast);
        
        // Show the toast
        setTimeout(() => toast.classList.add('show'), 10);
        
        // Add event listener for close button
        toast.querySelector('.close-btn').addEventListener('click', () => {
            toast.classList.remove('show');
            setTimeout(() => toast.remove(), 300);
        });
        
        // Auto-close after 5 seconds
        setTimeout(() => {
            toast.classList.remove('show');
            setTimeout(() => toast.remove(), 300);
        }, 5000);
    }
});
