/**
 * Simple profile picture upload handler
 */
document.addEventListener('DOMContentLoaded', function() {
    // Get elements
    const fileInput = document.getElementById('profile-picture');
    const previewImg = document.getElementById('profile-preview');
    const uploadBtn = document.getElementById('upload-picture-btn');
    const previewContainer = document.querySelector('.profile-picture-preview');
    
    // Check if elements exist
    if (!fileInput || !previewImg || !uploadBtn) {
        console.error('Required elements for profile picture upload not found');
        return;
    }
    
    console.log('Profile picture upload initialized');
    
    // Handle click on upload button
    uploadBtn.addEventListener('click', function() {
        fileInput.click();
    });
    
    // Handle click on preview image
    if (previewContainer) {
        previewContainer.addEventListener('click', function() {
            fileInput.click();
        });
    }
    
    // Handle file selection
    fileInput.addEventListener('change', function(event) {
        console.log('File selected:', this.files);
        
        if (this.files && this.files[0]) {
            const file = this.files[0];
            
            // Simple validation
            if (!file.type.startsWith('image/')) {
                alert('Please select an image file');
                return;
            }
            
            // Create file reader
            const reader = new FileReader();
            
            // Set up reader onload handler
            reader.onload = function(e) {
                console.log('File loaded successfully');
                previewImg.src = e.target.result;
            };
            
            // Read the file
            reader.readAsDataURL(file);
        }
    });
});
