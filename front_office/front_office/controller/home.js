// Home page JavaScript functionality

// Contact form submission handler
document.addEventListener('DOMContentLoaded', function() {
    // Get the contact form element
    const contactForm = document.getElementById('contactForm');
    const formStatus = document.getElementById('form-status');
    
    // Add submit event listener to the form
    if (contactForm) {
        contactForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            // Get form values
            const name = document.getElementById('name').value;
            const email = document.getElementById('email').value;
            const subject = document.getElementById('subject').value;
            const message = document.getElementById('message').value;
            
            // Show loading state
            const submitButton = document.getElementById('submit-form');
            submitButton.disabled = true;
            submitButton.textContent = 'Sending...';
            
            // Show sending message
            formStatus.textContent = 'Processing your message...';
            formStatus.style.color = '#4F6EF7';
            
            // Simulate sending (since we can't actually send emails from client-side JavaScript)
            setTimeout(() => {
                // Show success message
                formStatus.textContent = 'Thank you for your message! We will get back to you soon.';
                formStatus.style.color = '#4CAF50';
                
                // Reset form
                contactForm.reset();
                
                // Reset button
                submitButton.disabled = false;
                submitButton.textContent = 'Send Message';
                
                // Log the message details to console (for demonstration purposes)
                console.log('Message details:', {
                    name,
                    email,
                    subject,
                    message,
                    recipient: 'boubakriines11@gmail.com'
                });
                
                // In a real implementation, this would send the data to a server
                // But since we're running locally, we're just simulating success
            }, 1500);
        });
    }
    
    // Smooth scrolling for navigation links
    const navLinks = document.querySelectorAll('.nav-link[href^="#"]');
    
    // Get all sections that we want to track for scrolling
    const sections = [
        document.querySelector('#why-us-section'),
        document.querySelector('#jobs-section'),
        document.querySelector('#feedback-section'),
        document.querySelector('#contact-section')
    ].filter(section => section !== null); // Filter out any null sections
    
    // Add click event listener to each navigation link
    navLinks.forEach(link => {
        link.addEventListener('click', function(e) {
            // Prevent default anchor click behavior
            e.preventDefault();
            
            // Remove active class from all links
            navLinks.forEach(navLink => navLink.classList.remove('active'));
            
            // Add active class to clicked link
            this.classList.add('active');
            
            // Get the target element from the href attribute
            const targetId = this.getAttribute('href');
            
            // Skip if the href is just "#" (empty link)
            if (targetId === '#') return;
            
            const targetElement = document.querySelector(targetId);
            
            // If target element exists, scroll to it
            if (targetElement) {
                // Get the navbar height to offset the scroll position
                const navbarHeight = document.querySelector('.navbar').offsetHeight;
                
                // Get the current position of the target element
                const targetPosition = targetElement.getBoundingClientRect().top + window.pageYOffset;
                
                // Calculate the adjusted position accounting for navbar height
                const offsetPosition = targetPosition - navbarHeight;
                
                // Smooth scroll to the adjusted position
                window.scrollTo({
                    top: offsetPosition,
                    behavior: 'smooth'
                });
                
                // Update URL hash (optional)
                history.pushState(null, null, targetId);
            }
        });
    });
    
    // Function to determine which section is currently in view
    function setActiveNavOnScroll() {
        // Get current scroll position
        const scrollPosition = window.scrollY;
        
        // Get navbar height for offset calculation
        const navbarHeight = document.querySelector('.navbar').offsetHeight;
        
        // Find the current section
        let currentSection = null;
        
        sections.forEach(section => {
            // Get section's position and height
            const sectionTop = section.offsetTop - navbarHeight - 20; // Offset by navbar height plus a small buffer
            const sectionHeight = section.offsetHeight;
            
            // Check if the current scroll position is within this section
            if (scrollPosition >= sectionTop && scrollPosition < sectionTop + sectionHeight) {
                currentSection = section.id;
            }
        });
        
        // Update active class on navigation links
        if (currentSection) {
            navLinks.forEach(link => {
                link.classList.remove('active');
                if (link.getAttribute('href') === `#${currentSection}`) {
                    link.classList.add('active');
                }
            });
        }
    }
    
    // Add scroll event listener to update active navigation link
    window.addEventListener('scroll', setActiveNavOnScroll);
    
    // Set active nav on page load
    setActiveNavOnScroll();
});
