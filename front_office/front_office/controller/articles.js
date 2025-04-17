// Article data
const articleData = {
    tech: {
        title: "The Future of Web Development: Trends to Watch in 2025",
        author: "Sarah Johnson",
        authorTitle: "Senior Web Developer",
        date: "Mar 28, 2025",
        readTime: "8 min read",
        description: "Explore the latest trends in web development and what to expect in the coming years.",
        tags: ["Technology", "Web Dev", "Future Trends"],
        content: "The web development landscape continues to evolve at a rapid pace. As we move further into 2025, several key trends are emerging that will shape the future of how we build and interact with web applications.<br><br>First, WebAssembly is gaining significant traction, allowing developers to run high-performance code in the browser regardless of the programming language it was written in. This is enabling more complex applications to run efficiently on the web.<br><br>AI-driven development tools are also becoming mainstream, with code assistants and automated testing frameworks reducing development time and improving code quality. These tools are not replacing developers but rather augmenting their capabilities and allowing them to focus on more creative aspects of development.<br><br>Another important trend is the rise of edge computing in web development. By processing data closer to where it's needed, edge computing reduces latency and improves user experience, especially for applications requiring real-time interactions.<br><br>Privacy-focused development is also becoming a priority, with more frameworks and tools emerging to help developers build applications that respect user privacy by design.<br><br>Finally, we're seeing a shift towards more sustainable web development practices, with developers optimizing for energy efficiency and reduced carbon footprint.<br><br>As these trends continue to evolve, web developers who stay ahead of the curve will be well-positioned to create innovative, efficient, and user-friendly web experiences."
    },
    design: {
        title: "Minimalist Design Principles for Maximum Impact",
        author: "Alex Chen",
        authorTitle: "UX/UI Design Lead",
        date: "Mar 25, 2025",
        readTime: "6 min read",
        description: "Learn how to apply minimalist design principles to create powerful user experiences.",
        tags: ["Design", "UX/UI", "Minimalism"],
        content: "Minimalist design has evolved far beyond the 'less is more' mantra. Today's most effective minimalist interfaces achieve maximum impact by strategically applying core principles that enhance usability while maintaining visual simplicity.<br><br>The first principle is purposeful reduction. This doesn't mean removing elements arbitrarily, but rather eliminating anything that doesn't serve a clear purpose. Each element should earn its place in your design by contributing meaningfully to the user experience.<br><br>Hierarchy through contrast is another crucial principle. In minimalist design, subtle variations in size, weight, and spacing create clear visual hierarchies that guide users through content without needing explicit visual dividers or decorative elements.<br><br>Negative space as a design element is perhaps the most misunderstood aspect of minimalism. Effective minimalist designs use whitespace intentionally to create breathing room, highlight important elements, and improve readability.<br><br>Color with intention is also vital. Minimalist designs often use limited color palettes, but each color choice should be deliberate and serve a specific purpose, whether it's establishing brand identity, creating focal points, or conveying information.<br><br>Finally, typography as interface is a cornerstone of minimalist design. When visual elements are reduced, typography does more heavy lifting, not just conveying information but also establishing hierarchy, creating visual interest, and guiding users through the experience.<br><br>By mastering these principles, designers can create interfaces that feel simple and intuitive to users while actually being the result of careful, strategic decisions at every level."
    },
    career: {
        title: "Navigating Career Transitions in Tech: A Practical Guide",
        author: "Maya Patel",
        authorTitle: "Career Coach & Former Tech Lead",
        date: "Mar 22, 2025",
        readTime: "10 min read",
        description: "Practical advice for successfully transitioning between different roles and specializations in tech.",
        tags: ["Career", "Professional Growth", "Tech Industry"],
        content: "Career transitions within the tech industry have become increasingly common as the field expands and evolves. Whether you're moving from development to product management, switching tech stacks, or transitioning into a leadership role, strategic planning can make the difference between a smooth shift and a frustrating false start.<br><br>The first step is conducting a skills gap analysis. Map your current skills against those required for your target role, being honest about where you stand. This isn't just about technical skills—soft skills like communication and leadership are often equally important, especially when moving into management positions.<br><br>Once you've identified gaps, create a learning roadmap with specific milestones. This might include formal education, certifications, side projects, or mentorship. The key is to make your learning measurable and aligned with your target role's requirements.<br><br>Building a transition portfolio is crucial but often overlooked. This isn't your standard portfolio—it should specifically highlight projects and experiences that demonstrate your capability in your target area, even if they weren't part of your official job duties.<br><br>Strategic networking within your target specialty is also essential. Join communities, attend events, and connect with professionals already in your desired role. These connections can provide insights, opportunities, and advocacy that job applications alone cannot.<br><br>Finally, position your narrative effectively. Craft a compelling story about why your background is actually an advantage, not a limitation. Focus on transferable skills and how your unique perspective will benefit potential employers.<br><br>Remember that transitions take time. By approaching the process systematically and leveraging your existing strengths while developing new ones, you can successfully navigate even significant career shifts within the tech industry."
    },
    ai: {
        title: "Ethical AI Development: Balancing Innovation and Responsibility",
        author: "Dr. James Wilson",
        authorTitle: "AI Ethics Researcher",
        date: "Mar 20, 2025",
        readTime: "12 min read",
        description: "Exploring the ethical considerations and best practices for responsible AI development.",
        tags: ["AI", "Ethics", "Technology"],
        content: "As artificial intelligence becomes increasingly integrated into critical systems and everyday life, the imperative for ethical AI development has never been stronger. Balancing rapid innovation with responsible implementation requires a multifaceted approach that considers technical, social, and governance factors.<br><br>Transparency in AI systems is foundational to ethical development. This means not only making algorithms explainable but also clearly communicating to users when they're interacting with AI, what data is being used, and how decisions are being made. The 'black box' problem remains technically challenging for complex systems, but progress in explainable AI is making transparency increasingly feasible.<br><br>Fairness and bias mitigation must be addressed throughout the development lifecycle. This starts with diverse, representative datasets but extends to regular auditing of systems for unexpected biases and implementing technical solutions to address identified issues. Teams developing AI systems should themselves be diverse to bring multiple perspectives to potential ethical concerns.<br><br>Privacy preservation in the age of data-hungry AI systems requires both technical approaches like federated learning and differential privacy, as well as strong governance frameworks that limit data collection to what's necessary and ensure proper consent mechanisms.<br><br>Human oversight remains essential, particularly for high-stakes applications. This means designing systems where humans remain meaningfully involved in decision processes and can override AI recommendations when necessary. The goal should be augmented intelligence rather than autonomous systems that exclude human judgment.<br><br>Finally, impact assessment should become standard practice, evaluating not just the immediate application of an AI system but its potential societal effects, including economic impacts, accessibility concerns, and environmental considerations.<br><br>By integrating these principles into development processes, we can create AI systems that drive innovation while respecting human values and promoting equitable outcomes."
    },
    productivity: {
        title: "Deep Work in a Distracted World: Strategies for Tech Professionals",
        author: "Thomas Zhang",
        authorTitle: "Productivity Consultant",
        date: "Mar 18, 2025",
        readTime: "7 min read",
        description: "Practical techniques for achieving deep focus and maximizing productivity in tech roles.",
        tags: ["Productivity", "Work Culture", "Focus"],
        content: "The ability to perform deep work—professional activities in a state of distraction-free concentration—has become a rare and valuable skill in today's tech landscape. For developers, designers, and other tech professionals whose work requires complex problem-solving and creativity, mastering deep work isn't just about productivity—it's essential for producing high-quality output.<br><br>Time blocking is the foundation of effective deep work. Rather than reacting to incoming demands throughout the day, proactively schedule blocks of at least 90 minutes for deep work sessions. These should be treated as non-negotiable appointments with yourself, ideally scheduled during your peak cognitive hours.<br><br>Creating a distraction-free environment is equally important. This means more than just silencing notifications—it requires designing your physical and digital workspaces to minimize both external interruptions and the temptation for self-interruption. Tools like website blockers, focus modes, and noise-canceling headphones can help create boundaries.<br><br>Ritual and routine help train your brain to enter deep work states more easily. Develop consistent pre-work rituals that signal to your brain it's time to focus. This might include making a specific beverage, listening to particular music, or reviewing your objectives for the session.<br><br>Depth-friendly collaboration requires establishing team norms that respect focused work. This includes asynchronous communication by default, batching meetings, and creating clear documentation that reduces the need for interruptions. Teams should explicitly discuss and agree on response time expectations for different communication channels.<br><br>Finally, recovery is essential for sustainable deep work. Cognitive resources are finite, and attempting to maintain deep focus without adequate breaks leads to diminishing returns. Incorporate deliberate rest periods between deep work sessions and ensure you're getting sufficient sleep, exercise, and completely disconnected time.<br><br>By implementing these strategies consistently, tech professionals can reclaim their ability to concentrate deeply, producing work that stands out in quality and creativity in an increasingly distracted world."
    }
};

// Modal functionality
document.addEventListener('DOMContentLoaded', () => {
    const modal = document.getElementById('articleDetailsModal');
    const detailButtons = document.querySelectorAll('.details');
    const closeModal = document.querySelector('.close-modal');
    const modalBookmarkBtn = modal ? modal.querySelector('.bookmark-btn') : null;

    // Store bookmarked state for each article
    const bookmarkedArticles = new Set();

    function populateModal(articleType) {
        const data = articleData[articleType];
        if (!data || !modal) return;

        // Update modal title
        modal.querySelector('.article-title').textContent = data.title;
        
        // Update author information
        modal.querySelector('.author-name').textContent = data.author;
        modal.querySelector('.author-title').textContent = 'Self-paced';
        
        // Update tags
        const tagsContainer = modal.querySelector('.article-tags-container');
        if (tagsContainer) {
            // First tag is the main category
            const mainTag = data.tags[0];
            // Second tag is the read time
            const readTimeTag = data.readTime;
            // Third tag is always "Free"
            const freeTag = 'Free';
            
            tagsContainer.innerHTML = `
                <span class="article-tag">${mainTag}</span>
                <span class="article-tag">${readTimeTag}</span>
                <span class="article-tag">${freeTag}</span>
            `;
        }
        
        // Update article description
        const descriptionEl = modal.querySelector('.article-description');
        if (descriptionEl) {
            descriptionEl.textContent = data.description;
        }
        
        // Update "What you'll learn" section
        const contentListEl = modal.querySelector('.article-content-list');
        if (contentListEl) {
            // Create 3 key points from the content
            const contentParagraphs = data.content.split('<br><br>');
            const keyPoints = contentParagraphs.slice(0, 3).map(p => {
                // Take just the first sentence of each paragraph
                const firstSentence = p.split('.')[0] + '.';
                return `<li>${firstSentence}</li>`;
            });
            
            contentListEl.innerHTML = keyPoints.join('');
        }
        
        // Update full article content (hidden initially)
        const fullContentEl = modal.querySelector('.article-full-content');
        if (fullContentEl) {
            fullContentEl.innerHTML = data.content;
            fullContentEl.style.display = 'none'; // Hide initially
        }

        // Update bookmark button state
        if (modalBookmarkBtn) {
            const isBookmarked = bookmarkedArticles.has(articleType);
            modalBookmarkBtn.classList.toggle('active', isBookmarked);
            
            // Update bookmark icon visibility
            const outlineIcon = modalBookmarkBtn.querySelector('.bookmark-outline');
            const filledIcon = modalBookmarkBtn.querySelector('.bookmark-filled');
            
            if (outlineIcon && filledIcon) {
                outlineIcon.style.display = isBookmarked ? 'none' : 'block';
                filledIcon.style.display = isBookmarked ? 'block' : 'none';
            }
        }

        // Store the current article type on the modal
        modal.dataset.currentArticle = articleType;

        // Show modal
        modal.classList.add('active');
        
        // Add event listener to "Read Full Article" button
        const readMoreBtn = modal.querySelector('.read-more-btn');
        if (readMoreBtn) {
            // Remove any existing event listeners
            const newReadMoreBtn = readMoreBtn.cloneNode(true);
            readMoreBtn.parentNode.replaceChild(newReadMoreBtn, readMoreBtn);
            
            newReadMoreBtn.addEventListener('click', () => {
                const fullContent = modal.querySelector('.article-full-content');
                if (fullContent) {
                    if (fullContent.style.display === 'none') {
                        fullContent.style.display = 'block';
                        newReadMoreBtn.textContent = 'Hide Full Article';
                    } else {
                        fullContent.style.display = 'none';
                        newReadMoreBtn.textContent = 'Read Full Article';
                    }
                }
            });
        }
    }

    // Event listeners for detail buttons
    if (detailButtons && modal) {
        detailButtons.forEach(button => {
            button.addEventListener('click', (e) => {
                e.preventDefault();
                
                const articleCard = e.target.closest('.job-card');
                if (!articleCard) return;
                
                const articleContent = articleCard.querySelector('.job-content');
                if (!articleContent) return;
                
                // Find which article type this is by checking classes
                const articleType = Array.from(articleContent.classList)
                    .find(cls => Object.keys(articleData).includes(cls));
                
                if (articleType) {
                    populateModal(articleType);
                }
            });
        });
    }

    // Close modal when clicking the close button
    if (closeModal && modal) {
        closeModal.addEventListener('click', () => {
            modal.classList.remove('active');
        });
    }

    // Bookmark button click handler in modal
    if (modalBookmarkBtn && modal) {
        modalBookmarkBtn.addEventListener('click', () => {
            const currentArticle = modal.dataset.currentArticle;
            if (currentArticle) {
                const outlineIcon = modalBookmarkBtn.querySelector('.bookmark-outline');
                const filledIcon = modalBookmarkBtn.querySelector('.bookmark-filled');
                
                if (bookmarkedArticles.has(currentArticle)) {
                    // Remove bookmark
                    bookmarkedArticles.delete(currentArticle);
                    modalBookmarkBtn.classList.remove('active');
                    
                    if (outlineIcon && filledIcon) {
                        outlineIcon.style.display = 'block';
                        filledIcon.style.display = 'none';
                    }
                    
                    // Also update the card's bookmark button if it exists
                    updateCardBookmarkState(currentArticle, false);
                } else {
                    // Add bookmark
                    bookmarkedArticles.add(currentArticle);
                    modalBookmarkBtn.classList.add('active');
                    
                    if (outlineIcon && filledIcon) {
                        outlineIcon.style.display = 'none';
                        filledIcon.style.display = 'block';
                    }
                    
                    // Also update the card's bookmark button if it exists
                    updateCardBookmarkState(currentArticle, true);
                }
            }
        });
    }
    
    // Function to update the bookmark state on the card when changed in the modal
    function updateCardBookmarkState(articleType, isBookmarked) {
        const cards = document.querySelectorAll('.job-card');
        cards.forEach(card => {
            const content = card.querySelector('.job-content');
            if (content && content.classList.contains(articleType)) {
                const bookmarkBtn = card.querySelector('.bookmark');
                if (bookmarkBtn) {
                    const outlineIcon = bookmarkBtn.querySelector('.bookmark-outline');
                    const filledIcon = bookmarkBtn.querySelector('.bookmark-filled');
                    
                    if (outlineIcon && filledIcon) {
                        outlineIcon.style.display = isBookmarked ? 'none' : 'block';
                        filledIcon.style.display = isBookmarked ? 'block' : 'none';
                    }
                    
                    if (isBookmarked) {
                        bookmarkBtn.classList.add('active');
                    } else {
                        bookmarkBtn.classList.remove('active');
                    }
                }
            }
        });
    }

    // Close modal when clicking outside of it
    if (modal) {
        window.addEventListener('click', (e) => {
            if (e.target === modal) {
                modal.classList.remove('active');
            }
        });
    }
});

// Bookmark functionality for article cards
document.addEventListener('DOMContentLoaded', () => {
    const bookmarkButtons = document.querySelectorAll('.bookmark');
    const modal = document.getElementById('articleDetailsModal');
    
    // Store bookmarked state for each article
    const bookmarkedArticles = new Set();
    
    bookmarkButtons.forEach(button => {
        button.addEventListener('click', (e) => {
            e.preventDefault();
            e.stopPropagation();
            
            const articleCard = button.closest('.job-card');
            const articleContent = articleCard.querySelector('.job-content');
            
            // Find which article type this is by checking classes
            const articleType = Array.from(articleContent.classList)
                .find(cls => Object.keys(articleData).includes(cls));
                
            if (!articleType) return;
            
            const outlineIcon = button.querySelector('.bookmark-outline');
            const filledIcon = button.querySelector('.bookmark-filled');
            
            if (bookmarkedArticles.has(articleType)) {
                // Remove bookmark
                bookmarkedArticles.delete(articleType);
                button.classList.remove('active');
                
                if (outlineIcon && filledIcon) {
                    outlineIcon.style.display = 'block';
                    filledIcon.style.display = 'none';
                }
                
                // Update modal bookmark button if modal is open with this article
                updateModalBookmarkState(articleType, false);
            } else {
                // Add bookmark
                bookmarkedArticles.add(articleType);
                button.classList.add('active');
                
                if (outlineIcon && filledIcon) {
                    outlineIcon.style.display = 'none';
                    filledIcon.style.display = 'block';
                }
                
                // Update modal bookmark button if modal is open with this article
                updateModalBookmarkState(articleType, true);
            }
        });
    });
    
    // Function to update the bookmark state in the modal when changed on the card
    function updateModalBookmarkState(articleType, isBookmarked) {
        if (modal && modal.classList.contains('active') && modal.dataset.currentArticle === articleType) {
            const modalBookmarkBtn = modal.querySelector('.bookmark-btn');
            if (modalBookmarkBtn) {
                const outlineIcon = modalBookmarkBtn.querySelector('.bookmark-outline');
                const filledIcon = modalBookmarkBtn.querySelector('.bookmark-filled');
                
                if (outlineIcon && filledIcon) {
                    outlineIcon.style.display = isBookmarked ? 'none' : 'block';
                    filledIcon.style.display = isBookmarked ? 'block' : 'none';
                }
                
                if (isBookmarked) {
                    modalBookmarkBtn.classList.add('active');
                } else {
                    modalBookmarkBtn.classList.remove('active');
                }
            }
        }
    }
});

// Toggle view functionality for articles page
document.addEventListener('DOMContentLoaded', () => {
    // Get the toggle button and cards container
    const viewToggleBtn = document.querySelector('.view-toggle');
    const jobCardsContainer = document.querySelector('.job-cards');
    
    // Get the grid and list icons
    const gridIcon = document.querySelector('.grid-icon');
    const listIcon = document.querySelector('.list-icon');
    
    if (viewToggleBtn && jobCardsContainer && gridIcon && listIcon) {
        // Function to update the toggle button icons
        function updateToggleIcons() {
            const isListLayout = jobCardsContainer.classList.contains('list-layout');
            gridIcon.style.display = isListLayout ? 'none' : 'block';
            listIcon.style.display = isListLayout ? 'block' : 'none';
        }
        
        // Set initial state
        updateToggleIcons();
        
        // Add click event listener to the toggle button
        viewToggleBtn.addEventListener('click', () => {
            // Toggle the list-layout class on the job cards container
            jobCardsContainer.classList.toggle('list-layout');
            
            // Update the toggle button icons
            updateToggleIcons();
            
            // Log the current state for debugging
            console.log('View toggled. List layout:', jobCardsContainer.classList.contains('list-layout'));
        });
    } else {
        console.error('Toggle view elements not found');
    }
});
