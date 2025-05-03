// Notification functionality for all pages
document.addEventListener('DOMContentLoaded', function() {
    // Get notification button and dot
    const notificationBtn = document.querySelector('.notification');
    const notificationPanel = document.getElementById('notificationPanel');
    const notificationDot = document.querySelector('.notification-dot');
    const notificationList = document.getElementById('notificationList');
    const closeNotificationsBtn = document.getElementById('closeNotifications');
    
    if (notificationBtn) {
        // Toggle notification panel
        notificationBtn.addEventListener('click', function() {
            if (notificationPanel.classList.contains('translate-x-full')) {
                // Show panel
                notificationPanel.classList.remove('translate-x-full');
                // Load notifications
                loadNotifications();
            } else {
                // Hide panel
                notificationPanel.classList.add('translate-x-full');
            }
        });
    }
    
    if (closeNotificationsBtn) {
        // Close notification panel
        closeNotificationsBtn.addEventListener('click', function() {
            notificationPanel.classList.add('translate-x-full');
        });
    }
    
    // Function to load notifications
    function loadNotifications() {
        fetch('../view/get_notifications.php')
            .then(response => {
                if (!response.ok) {
                    throw new Error('Network response was not ok');
                }
                return response.json();
            })
            .then(data => {
                // Hide the notification dot when notifications are viewed
                if (notificationDot) {
                    notificationDot.style.display = 'none';
                }
                
                // Clear the loading indicator
                if (notificationList) {
                    notificationList.innerHTML = '';
                
                    if (data.success) {
                        if (data.notifications.length === 0) {
                            notificationList.innerHTML = '<div class="p-4 text-center text-gray-500">No notifications</div>';
                        } else {
                            // Display notifications
                            data.notifications.forEach(notification => {
                                const notificationItem = document.createElement('div');
                                notificationItem.className = 'p-4 border-b hover:bg-gray-50 transition-colors';
                                
                                let iconHTML = '';
                                
                                // Set icon based on notification type
                                switch(notification.type) {
                                    case 'follow':
                                        iconHTML = '<div class="w-8 h-8 bg-blue-100 rounded-full flex items-center justify-center text-blue-500 mr-3"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="8.5" cy="7" r="4"></circle><line x1="20" y1="8" x2="20" y2="14"></line><line x1="23" y1="11" x2="17" y2="11"></line></svg></div>';
                                        break;
                                    case 'connection_request':
                                        iconHTML = '<div class="w-8 h-8 bg-purple-100 rounded-full flex items-center justify-center text-purple-500 mr-3"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="8.5" cy="7" r="4"></circle><path d="M20 8v6"></path><path d="M23 11h-6"></path></svg></div>';
                                        break;
                                    case 'connection_accepted':
                                        iconHTML = '<div class="w-8 h-8 bg-green-100 rounded-full flex items-center justify-center text-green-500 mr-3"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="8.5" cy="7" r="4"></circle><polyline points="17 11 19 13 23 9"></polyline></svg></div>';
                                        break;
                                    case 'connection_rejected':
                                        iconHTML = '<div class="w-8 h-8 bg-red-100 rounded-full flex items-center justify-center text-red-500 mr-3"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="8.5" cy="7" r="4"></circle><line x1="18" y1="8" x2="23" y2="13"></line><line x1="23" y1="8" x2="18" y2="13"></line></svg></div>';
                                        break;
                                    default:
                                        iconHTML = '<div class="w-8 h-8 bg-gray-100 rounded-full flex items-center justify-center text-gray-500 mr-3"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="16" x2="12" y2="12"></line><line x1="12" y1="8" x2="12.01" y2="8"></line></svg></div>';
                                }
                                
                                // Create notification content with buttons for connection requests
                                let actionsHTML = '';
                                if (notification.type === 'connection_request' && notification.sender_id) {
                                    actionsHTML = `
                                        <div class="flex mt-2 space-x-2">
                                            <button class="accept-request-btn px-3 py-1 bg-blue-500 text-white text-xs rounded hover:bg-blue-600 transition" data-user-id="${notification.sender_id}">Accept</button>
                                            <button class="reject-request-btn px-3 py-1 bg-gray-200 text-gray-800 text-xs rounded hover:bg-gray-300 transition" data-user-id="${notification.sender_id}">Decline</button>
                                        </div>
                                    `;
                                }
                                
                                notificationItem.innerHTML = `
                                    <div class="flex items-start">
                                        ${iconHTML}
                                        <div class="flex-1">
                                            <p class="text-sm mb-1">${notification.message}</p>
                                            <p class="text-xs text-gray-500">${getTimeAgo(notification.created_at)}</p>
                                            ${actionsHTML}
                                        </div>
                                    </div>
                                `;
                                
                                notificationList.appendChild(notificationItem);
                            });
                            
                            // Add event listeners for connection request buttons
                            document.querySelectorAll('.accept-request-btn').forEach(button => {
                                button.addEventListener('click', function() {
                                    handleConnectionAction('accept_request', this.getAttribute('data-user-id'));
                                });
                            });
                            
                            document.querySelectorAll('.reject-request-btn').forEach(button => {
                                button.addEventListener('click', function() {
                                    handleConnectionAction('reject_request', this.getAttribute('data-user-id'));
                                });
                            });
                        }
                    } else {
                        notificationList.innerHTML = '<div class="p-4 text-center text-red-500">Error loading notifications</div>';
                    }
                }
            })
            .catch(error => {
                console.error('Error:', error);
                if (notificationList) {
                    notificationList.innerHTML = '<div class="p-4 text-center text-red-500">Failed to load notifications</div>';
                }
            });
    }
    
    // Handle connection actions (accept/reject)
    function handleConnectionAction(action, userId) {
        const formData = new FormData();
        formData.append('action', action);
        formData.append('user_id', userId);
        
        fetch('../model/networking/connections.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showToast('Success', data.message, 'success');
                // Reload notifications to update the list
                loadNotifications();
            } else {
                showToast('Error', data.message, 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showToast('Error', 'An error occurred. Please try again later.', 'error');
        });
    }
    
    // Helper function to format time ago
    function getTimeAgo(timestamp) {
        const now = new Date();
        const past = new Date(timestamp);
        const diffInSeconds = Math.floor((now - past) / 1000);
        
        if (diffInSeconds < 60) {
            return 'Just now';
        }
        
        const diffInMinutes = Math.floor(diffInSeconds / 60);
        if (diffInMinutes < 60) {
            return diffInMinutes + ' minute' + (diffInMinutes > 1 ? 's' : '') + ' ago';
        }
        
        const diffInHours = Math.floor(diffInMinutes / 60);
        if (diffInHours < 24) {
            return diffInHours + ' hour' + (diffInHours > 1 ? 's' : '') + ' ago';
        }
        
        const diffInDays = Math.floor(diffInHours / 24);
        if (diffInDays < 7) {
            return diffInDays + ' day' + (diffInDays > 1 ? 's' : '') + ' ago';
        }
        
        const diffInWeeks = Math.floor(diffInDays / 7);
        if (diffInWeeks < 4) {
            return diffInWeeks + ' week' + (diffInWeeks > 1 ? 's' : '') + ' ago';
        }
        
        const diffInMonths = Math.floor(diffInDays / 30);
        return diffInMonths + ' month' + (diffInMonths > 1 ? 's' : '') + ' ago';
    }
    
    // Toast notification system
    const toast = document.getElementById('toast');
    const toastTitle = document.getElementById('toastTitle');
    const toastMessage = document.getElementById('toastMessage');
    const toastIcon = document.getElementById('toastIcon');
    const toastIconSVG = document.getElementById('toastIconSVG');
    
    // Show toast notification
    window.showToast = function(title, message, type = 'info') {
        if (toast && toastTitle && toastMessage && toastIcon && toastIconSVG) {
            toastTitle.textContent = title;
            toastMessage.textContent = message;
            
            // Set icon and color based on type
            toastIcon.className = 'flex-shrink-0 w-8 h-8 flex items-center justify-center rounded-full mr-3';
            
            if (type === 'success') {
                toastIcon.classList.add('bg-green-100', 'text-green-500');
                toastIconSVG.innerHTML = '<path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline points="22 4 12 14.01 9 11.01"></polyline>';
            } else if (type === 'error') {
                toastIcon.classList.add('bg-red-100', 'text-red-500');
                toastIconSVG.innerHTML = '<circle cx="12" cy="12" r="10"></circle><line x1="12" y1="8" x2="12" y2="12"></line><line x1="12" y1="16" x2="12.01" y2="16"></line>';
            } else {
                toastIcon.classList.add('bg-blue-100', 'text-blue-500');
                toastIconSVG.innerHTML = '<circle cx="12" cy="12" r="10"></circle><line x1="12" y1="16" x2="12" y2="12"></line><line x1="12" y1="8" x2="12.01" y2="8"></line>';
            }
            
            // Show toast
            toast.classList.remove('translate-y-full');
            
            // Auto hide after 3 seconds
            setTimeout(() => {
                toast.classList.add('translate-y-full');
            }, 3000);
        }
    };
});