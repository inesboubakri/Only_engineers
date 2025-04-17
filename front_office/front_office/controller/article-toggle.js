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
