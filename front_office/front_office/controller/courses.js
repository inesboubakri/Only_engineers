// Course data
const courseData = {
    'google-react': {
        id: 'google-react',
        title: 'Advanced React Development',
        company: 'Google',
        location: 'Self-paced',
        price: 'Free',
        type: 'Advanced',
        duration: '5+ Hours',
        description: 'Master advanced React concepts and patterns used in modern web development. Learn from Google engineers about building scalable and performant applications.',
        learningOutcomes: [
            'Advanced component patterns and best practices',
            'State management with Redux and Context API',
            'Performance optimization techniques',
            'Server-side rendering with Next.js',
            'Testing strategies for React applications'
        ],
        requirements: [
            'Strong understanding of JavaScript fundamentals',
            'Basic experience with React',
            'Familiarity with modern web development tools',
            'Understanding of HTML/CSS'
        ],
        tags: ['Advanced', '5+ Hours', 'Web Dev'],
        article: {
            title: 'Mastering Advanced React Patterns',
            author: 'Sarah Chen',
            authorAvatar: '../assets/profil.jpg',
            publishDate: 'March 25, 2025',
            tags: ['React', 'JavaScript', 'Web Development'],
            content: `
                <p>React has become the cornerstone of modern web development, powering countless applications across the internet. While many developers are familiar with the basics, truly mastering React requires understanding its advanced patterns and optimization techniques.</p>
                
                <h2>Component Composition Patterns</h2>
                <p>One of the most powerful aspects of React is its composability. By breaking your UI into small, reusable components, you can build complex interfaces while keeping your code maintainable. Let's explore some advanced composition patterns:</p>
                
                <h3>Compound Components</h3>
                <p>Compound components are a pattern where components are used together such that they share an implicit state. Think of it like a select and its options - they work together to form a cohesive whole.</p>
                
                <pre><code>// Example of compound components
const Tabs = ({ children, defaultIndex = 0 }) => {
  const [selectedIndex, setSelectedIndex] = useState(defaultIndex);
  
  return React.Children.map(children, (child, index) => {
    return React.cloneElement(child, {
      selected: index === selectedIndex,
      onClick: () => setSelectedIndex(index)
    });
  });
};

const Tab = ({ selected, onClick, children }) => {
  return (
    <button 
      className={selected ? 'active' : ''} 
      onClick={onClick}
    >
      {children}
    </button>
  );
};</code></pre>
                
                <h3>Render Props</h3>
                <p>The render props pattern involves passing a function as a prop that a component uses to render part of its UI. This gives incredible flexibility in how components share data and functionality.</p>
                
                <h2>State Management Approaches</h2>
                <p>As applications grow, state management becomes increasingly complex. While Redux has been the go-to solution for years, React's Context API and hooks have provided new alternatives.</p>
                
                <p>Context API with useReducer can provide Redux-like state management without the additional dependencies. For many applications, this combination offers the perfect balance of power and simplicity.</p>
                
                <h2>Performance Optimization</h2>
                <p>React's virtual DOM is efficient, but there are still many ways to optimize performance:</p>
                
                <ul>
                    <li>Use React.memo for component memoization</li>
                    <li>Implement useCallback for stable function references</li>
                    <li>Apply useMemo for expensive calculations</li>
                    <li>Virtualize long lists with react-window or react-virtualized</li>
                    <li>Code-split your application with React.lazy and Suspense</li>
                </ul>
                
                <p>By applying these advanced patterns and optimization techniques, you'll be able to build React applications that are not only powerful and flexible but also performant and maintainable.</p>
            `
        }
    },
    'microsoft-azure': {
        id: 'microsoft-azure',
        title: 'Azure Cloud Fundamentals',
        company: 'Microsoft',
        location: 'Self-paced',
        price: 'Free',
        type: 'Beginner',
        duration: '2-5 Hours',
        description: 'Get started with Microsoft Azure cloud services. Learn the fundamentals of cloud computing and how to leverage Azure services for your applications.',
        learningOutcomes: [
            'Understanding of cloud computing concepts',
            'Azure core services and architecture',
            'Security and compliance in Azure',
            'Cost management and optimization',
            'Basic cloud deployment strategies'
        ],
        requirements: [
            'Basic understanding of IT concepts',
            'No prior cloud experience required',
            'Familiarity with basic computing terms',
            'Interest in cloud technologies'
        ],
        tags: ['Beginner', '2-5 Hours', 'Cloud'],
        article: {
            title: 'Getting Started with Microsoft Azure: A Beginner\'s Guide',
            author: 'Michael Rodriguez',
            authorAvatar: '../assets/profil.jpg',
            publishDate: 'March 20, 2025',
            tags: ['Azure', 'Cloud Computing', 'Microsoft'],
            content: `
                <p>Cloud computing has revolutionized how businesses deploy and manage their IT infrastructure. Microsoft Azure, one of the leading cloud platforms, offers a comprehensive suite of services that can help organizations of all sizes innovate and scale efficiently.</p>
                
                <h2>What is Microsoft Azure?</h2>
                <p>Azure is Microsoft's cloud computing platform, providing a range of cloud services including those for computing, analytics, storage, and networking. Users can pick and choose from these services to develop and scale new applications, or run existing applications in the public cloud.</p>
                
                <p>The platform aims to help businesses manage challenges and meet their organizational goals. It offers tools for all industries—from e-commerce to gaming to banking—and is compatible with open-source technologies, so you can use the tools and technologies you prefer.</p>
                
                <h2>Core Azure Services</h2>
                
                <h3>Compute Services</h3>
                <p>Azure offers various compute services that allow you to run your applications in the cloud:</p>
                <ul>
                    <li><strong>Virtual Machines (VMs)</strong>: Create Windows or Linux VMs in seconds</li>
                    <li><strong>App Services</strong>: Build and host web apps, mobile backends, and RESTful APIs</li>
                    <li><strong>Azure Functions</strong>: Execute code without managing infrastructure</li>
                    <li><strong>Azure Kubernetes Service (AKS)</strong>: Simplify container orchestration</li>
                </ul>
                
                <h3>Storage Services</h3>
                <p>Azure provides several storage options to meet different needs:</p>
                <ul>
                    <li><strong>Blob Storage</strong>: Store massive amounts of unstructured data</li>
                    <li><strong>File Storage</strong>: Fully managed file shares in the cloud</li>
                    <li><strong>Queue Storage</strong>: Store large numbers of messages for asynchronous processing</li>
                    <li><strong>Table Storage</strong>: NoSQL key-value store for rapid development</li>
                </ul>
                
                <h2>Getting Started with Azure</h2>
                <p>Starting with Azure is straightforward. Microsoft offers a free account with $200 in credits to spend in the first 30 days, along with free access to popular services for 12 months.</p>
                
                <p>The Azure Portal provides a user-friendly interface to manage your resources. Alternatively, you can use command-line tools like Azure CLI or PowerShell, or even infrastructure as code solutions like Azure Resource Manager templates or Terraform.</p>
                
                <h2>Security in Azure</h2>
                <p>Security is a top priority in Azure. The platform offers various security services and features:</p>
                <ul>
                    <li>Azure Active Directory for identity and access management</li>
                    <li>Azure Security Center for unified security management</li>
                    <li>Azure Key Vault for safeguarding cryptographic keys and secrets</li>
                    <li>Azure DDoS Protection to defend against distributed denial-of-service attacks</li>
                </ul>
                
                <p>By understanding these fundamental concepts and services, you'll be well on your way to leveraging the power of Microsoft Azure for your applications and workloads.</p>
            `
        }
    },
    'amazon-ml': {
        id: 'amazon-ml',
        title: 'Machine Learning Basics',
        company: 'Amazon',
        location: 'Self-paced',
        price: 'Free',
        type: 'Intermediate',
        duration: '5+ Hours',
        description: 'Learn the fundamentals of machine learning with Amazon. This course covers essential ML concepts and practical applications using real-world examples.',
        learningOutcomes: [
            'Understanding of ML fundamentals',
            'Data preprocessing and feature engineering',
            'Common ML algorithms and their applications',
            'Model evaluation and validation',
            'Practical ML project implementation'
        ],
        requirements: [
            'Basic Python programming skills',
            'Understanding of basic statistics',
            'Familiarity with data structures',
            'Basic linear algebra knowledge'
        ],
        tags: ['Intermediate', '5+ Hours', 'AI/ML'],
        article: {
            title: 'Introduction to Machine Learning: Concepts and Applications',
            author: 'Dr. Aisha Johnson',
            authorAvatar: '../assets/profil.jpg',
            publishDate: 'March 15, 2025',
            tags: ['Machine Learning', 'AI', 'Data Science'],
            content: `
                <p>Machine Learning (ML) has emerged as one of the most transformative technologies of our time. From recommendation systems to autonomous vehicles, ML is powering innovations across industries. This article introduces the fundamental concepts of machine learning and explores its practical applications.</p>
                
                <h2>What is Machine Learning?</h2>
                <p>Machine Learning is a subset of artificial intelligence that enables systems to learn and improve from experience without being explicitly programmed. Instead of writing code that follows specific instructions to accomplish a task, ML systems are trained on large amounts of data and learn to recognize patterns.</p>
                
                <p>Arthur Samuel, a pioneer in ML, defined it as the "field of study that gives computers the ability to learn without being explicitly programmed." This definition captures the essence of ML: the ability of algorithms to generalize from examples.</p>
                
                <h2>Types of Machine Learning</h2>
                
                <h3>Supervised Learning</h3>
                <p>In supervised learning, algorithms learn from labeled training data. The algorithm makes predictions and is corrected when those predictions are wrong. This process continues until the algorithm achieves an acceptable level of performance.</p>
                
                <p>Common supervised learning algorithms include:</p>
                <ul>
                    <li>Linear Regression</li>
                    <li>Logistic Regression</li>
                    <li>Decision Trees</li>
                    <li>Random Forests</li>
                    <li>Support Vector Machines (SVM)</li>
                    <li>Neural Networks</li>
                </ul>
                
                <h3>Unsupervised Learning</h3>
                <p>Unsupervised learning algorithms work with unlabeled data. They identify patterns and relationships in the data without prior training. These algorithms are useful for clustering, dimensionality reduction, and anomaly detection.</p>
                
                <p>Popular unsupervised learning algorithms include:</p>
                <ul>
                    <li>K-means clustering</li>
                    <li>Hierarchical clustering</li>
                    <li>Principal Component Analysis (PCA)</li>
                    <li>Autoencoders</li>
                </ul>
                
                <h3>Reinforcement Learning</h3>
                <p>Reinforcement learning involves an agent that learns to make decisions by taking actions in an environment to maximize a reward. This type of learning is particularly useful for robotics, gaming, and autonomous systems.</p>
                
                <h2>The Machine Learning Process</h2>
                <p>A typical ML project follows these steps:</p>
                
                <ol>
                    <li><strong>Data Collection</strong>: Gathering relevant data for the problem</li>
                    <li><strong>Data Preprocessing</strong>: Cleaning and preparing the data for analysis</li>
                    <li><strong>Feature Engineering</strong>: Selecting and transforming variables to improve model performance</li>
                    <li><strong>Model Selection</strong>: Choosing appropriate algorithms for the task</li>
                    <li><strong>Training</strong>: Teaching the model using training data</li>
                    <li><strong>Evaluation</strong>: Assessing model performance using validation data</li>
                    <li><strong>Deployment</strong>: Implementing the model in a production environment</li>
                    <li><strong>Monitoring</strong>: Tracking model performance and updating as needed</li>
                </ol>
                
                <h2>Applications of Machine Learning</h2>
                <p>ML is being applied across numerous domains:</p>
                
                <ul>
                    <li><strong>Healthcare</strong>: Disease diagnosis, drug discovery, personalized treatment</li>
                    <li><strong>Finance</strong>: Fraud detection, algorithmic trading, risk assessment</li>
                    <li><strong>Retail</strong>: Recommendation systems, inventory management, price optimization</li>
                    <li><strong>Transportation</strong>: Autonomous vehicles, traffic prediction, route optimization</li>
                    <li><strong>Manufacturing</strong>: Predictive maintenance, quality control, supply chain optimization</li>
                </ul>
                
                <p>As data continues to grow exponentially and computing power becomes more accessible, the potential applications of machine learning will only expand, driving innovation and transformation across industries.</p>
            `
        }
    }
};

// Simple and direct implementation to make the modal work

// Execute immediately - don't wait for DOMContentLoaded
(function() {
    console.log('Courses JS executing immediately');
    
    // Try to attach event listeners as soon as possible
    tryInitialize();
    
    // Also try again when DOM is fully loaded
    document.addEventListener('DOMContentLoaded', function() {
        console.log('DOM fully loaded');
        tryInitialize();
    });
    
    // Try once more after a slight delay to ensure everything is loaded
    setTimeout(tryInitialize, 500);
    
    function tryInitialize() {
        console.log('Trying to initialize button functionality');
        
        // Get button and modal elements
        const addButton = document.querySelector('.learn-more');
        const modal = document.getElementById('addCourseModal');
        
        if (!addButton) {
            console.error('Add course button not found!');
            return;
        }
        
        if (!modal) {
            console.error('Modal element not found!');
            return;
        }
        
        console.log('Elements found:', { button: addButton, modal: modal });
        
        // Add direct click event to the button
        addButton.onclick = function(e) {
            e.preventDefault();
            console.log('Button clicked!');
            
            // Make modal visible with multiple CSS properties to ensure it shows
            modal.style.display = 'block';
            modal.style.visibility = 'visible';
            modal.style.opacity = '1';
            modal.style.zIndex = '9999';
            
            console.log('Modal should be visible now');
        };
        
        // Add close functionality
        const closeButtons = modal.querySelectorAll('.close-modal, #cancelAddCourse');
        closeButtons.forEach(function(btn) {
            if (btn) {
                btn.onclick = function() {
                    console.log('Close button clicked');
                    modal.style.display = 'none';
                };
            }
        });
        
        // Form submission
        const form = document.getElementById('addCourseForm');
        if (form) {
            form.onsubmit = function(e) {
                e.preventDefault();
                console.log('Form submitted');
                alert('Course added successfully (demo)!');
                modal.style.display = 'none';
            };
        }
        
        console.log('Button functionality initialized successfully');
    }
})();