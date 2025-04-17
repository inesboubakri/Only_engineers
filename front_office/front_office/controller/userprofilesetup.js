/**
 * Initialize profile data from localStorage if available
 */
let profileData = {
  personal: {
    name: '',
    title: '',
    location: '',
    website: '',
    email: '',
    password: '',
    birthday: '',
    yearsExperience: 0,
    profileImage: '',
    coverBackground: 'linear-gradient(135deg, #3494E6, #EC6EAD)',
    topSkills: [],
    languages: [],
    profileCreated: false
  },
  about: '',
  seeking: '',
  experience: [],
  education: [],
  skills: [],
  languages: [],
  honors: [],
  courses: [],
  portfolio: []
};

/**
 * Load profile data from localStorage if available
 */
function loadProfileData() {
  const savedData = localStorage.getItem('profileData');
  if (savedData) {
    try {
      const parsedData = JSON.parse(savedData);
      // Ensure all expected properties exist
      profileData = {
        ...profileData,
        ...parsedData,
        personal: { ...profileData.personal, ...parsedData.personal }
      };
    } catch (e) {
      console.error('Error parsing profile data:', e);
    }
  }
}

/**
 * Save profile data to localStorage
 */
function saveProfileData() {
  localStorage.setItem('profileData', JSON.stringify(profileData));
}

/**
 * Count words in a text
 * @param {string} text - The text to count words in
 * @returns {number} - The word count
 */
function countWords(text) {
  if (!text || text.trim() === '') return 0;
  return text.trim().split(/\s+/).length;
}

/**
 * Generate a unique ID
 * @returns {string} - A unique ID
 */
function generateUniqueId() {
  return 'id-' + Math.random().toString(36).substr(2, 9);
}

/**
 * Extract top skills from experience descriptions
 */
function updateTopSkillsFromExperience() {
  // Example: Extract potential skills from experience descriptions
  const skillTerms = ['JavaScript', 'Python', 'React', 'Node.js', 'TypeScript', 'Java', 'C++', 
    'SQL', 'PHP', 'Ruby', 'Swift', 'Go', 'Rust', 'HTML', 'CSS', 'AWS', 'Azure', 'GCP', 
    'Docker', 'Kubernetes', 'MongoDB', 'PostgreSQL', 'MySQL', 'Redis', 'Git', 'CI/CD'];
  
  const skillFrequency = {};
  
  // Scan all experience descriptions
  profileData.experience.forEach(exp => {
    if (!exp.description) return;
    
    const description = exp.description.toLowerCase();
    skillTerms.forEach(skill => {
      if (description.includes(skill.toLowerCase())) {
        skillFrequency[skill] = (skillFrequency[skill] || 0) + 1;
      }
    });
  });
  
  // Sort by frequency and take top 3
  const sortedSkills = Object.keys(skillFrequency)
    .sort((a, b) => skillFrequency[b] - skillFrequency[a])
    .slice(0, 3);
  
  if (sortedSkills.length > 0) {
    profileData.personal.topSkills = sortedSkills;
  }
}

/**
 * Show a toast notification
 * @param {string} message - The message to show
 * @param {string} type - The type of toast (success or error)
 */
function showToast(message, type = 'success') {
  const toast = document.getElementById('toast');
  toast.className = `toast ${type}`;
  toast.querySelector('.toast-message').textContent = message;
  
  toast.classList.add('show');
  
  // Reset the progress bar
  const progress = toast.querySelector('.toast-progress');
  progress.style.width = '0';
  
  // Animate the progress bar
  setTimeout(() => {
    progress.style.width = '100%';
  }, 10);
  
  // Hide the toast after 3 seconds
  setTimeout(() => {
    toast.classList.remove('show');
  }, 3000);
}

/**
 * Switch to a different tab in the setup profile modal
 * @param {string} tabId - The ID of the tab to switch to
 */
function switchSetupTab(tabId) {
  // Deactivate all tabs
  document.querySelectorAll('.setup-tab').forEach(tab => {
    tab.classList.remove('active');
  });
  
  // Hide all tab contents
  document.querySelectorAll('.setup-tab-content').forEach(content => {
    content.classList.remove('active');
  });
  
  // Activate the selected tab
  document.querySelector(`.setup-tab[data-tab="${tabId}"]`).classList.add('active');
  document.getElementById(`${tabId}-tab-content`).classList.add('active');
  
  // Update the progress bar
  const tabIndex = getTabIndex(tabId);
  const tabs = document.querySelectorAll('.setup-tab');
  const progressPercent = (tabIndex + 1) * (100 / tabs.length);
  document.querySelector('.setup-progress-bar').style.width = `${progressPercent}%`;
  
  // Update navigation buttons
  document.getElementById('prev-tab-btn').disabled = (tabIndex === 0);
  document.getElementById('next-tab-btn').textContent = (tabIndex === tabs.length - 1) ? 'Complete Setup' : 'Next';
}

/**
 * Get the index of a tab
 * @param {string} tabId - The ID of the tab
 * @returns {number} - The index of the tab
 */
function getTabIndex(tabId) {
  const tabs = [
    'basic', 
    'appearance', 
    'about', 
    'experience', 
    'education', 
    'skills', 
    'languages', 
    'honors', 
    'courses', 
    'portfolio', 
    'seeking', 
    'security'
  ];
  return tabs.indexOf(tabId);
}

/**
 * Navigate to the previous tab
 */
function navigatePreviousTab() {
  const currentTab = document.querySelector('.setup-tab.active');
  const currentTabId = currentTab.getAttribute('data-tab');
  const currentIndex = getTabIndex(currentTabId);
  
  if (currentIndex > 0) {
    const tabs = [
      'basic', 
      'appearance', 
      'about', 
      'experience', 
      'education', 
      'skills', 
      'languages', 
      'honors', 
      'courses', 
      'portfolio', 
      'seeking', 
      'security'
    ];
    const prevTabId = tabs[currentIndex - 1];
    switchSetupTab(prevTabId);
  }
}

/**
 * Navigate to the next tab
 */
function navigateNextTab() {
  const currentTab = document.querySelector('.setup-tab.active');
  const currentTabId = currentTab.getAttribute('data-tab');
  const currentIndex = getTabIndex(currentTabId);
  const tabs = [
    'basic', 
    'appearance', 
    'about', 
    'experience', 
    'education', 
    'skills', 
    'languages', 
    'honors', 
    'courses', 
    'portfolio', 
    'seeking', 
    'security'
  ];
  
  if (currentIndex < tabs.length - 1) {
    const nextTabId = tabs[currentIndex + 1];
    switchSetupTab(nextTabId);
  } else {
    // If we're on the last tab, submit the form
    document.getElementById('setup-profile-form').dispatchEvent(new Event('submit'));
  }
}

/**
 * Add a new experience item to the setup form
 */
function addExperienceItem() {
  const container = document.getElementById('setup-experience-items');
  const itemCount = container.querySelectorAll('.setup-experience-item').length + 1;
  
  const newItem = document.createElement('div');
  newItem.className = 'setup-experience-item';
  newItem.innerHTML = `
    <div class="form-group">
      <label for="setup-exp-title-${itemCount}">Job Title</label>
      <input type="text" id="setup-exp-title-${itemCount}" placeholder="e.g. Senior Developer">
    </div>
    <div class="form-group">
      <label for="setup-exp-company-${itemCount}">Company</label>
      <input type="text" id="setup-exp-company-${itemCount}" placeholder="e.g. Google">
    </div>
    <div class="form-group">
      <label for="setup-exp-location-${itemCount}">Location</label>
      <input type="text" id="setup-exp-location-${itemCount}" placeholder="e.g. Mountain View, CA">
    </div>
    <div class="form-row">
      <div class="form-group half">
        <label for="setup-exp-start-${itemCount}">Start Date</label>
        <input type="month" id="setup-exp-start-${itemCount}">
      </div>
      <div class="form-group half">
        <label for="setup-exp-end-${itemCount}">End Date</label>
        <input type="month" id="setup-exp-end-${itemCount}">
      </div>
    </div>
    <div class="form-group checkbox-group">
      <input type="checkbox" id="setup-exp-current-${itemCount}" class="setup-exp-current">
      <label for="setup-exp-current-${itemCount}">I currently work here</label>
    </div>
    <div class="form-group">
      <label for="setup-exp-description-${itemCount}">Description</label>
      <textarea id="setup-exp-description-${itemCount}" rows="4" placeholder="Describe your role, responsibilities, and achievements..."></textarea>
    </div>
    <button type="button" class="remove-item-btn" onclick="this.parentNode.remove()">Remove</button>
  `;
  
  container.appendChild(newItem);
  
  // Add event listener for the current checkbox
  const currentCheckbox = document.getElementById(`setup-exp-current-${itemCount}`);
  currentCheckbox.addEventListener('change', (event) => {
    const endDateInput = document.getElementById(`setup-exp-end-${itemCount}`);
    endDateInput.disabled = event.target.checked;
    if (event.target.checked) {
      endDateInput.value = '';
    }
  });
}

/**
 * Add a new education item to the setup form
 */
function addEducationItem() {
  const container = document.getElementById('setup-education-items');
  const itemCount = container.querySelectorAll('.setup-education-item').length + 1;
  
  const newItem = document.createElement('div');
  newItem.className = 'setup-education-item';
  newItem.innerHTML = `
    <div class="form-group">
      <label for="setup-edu-school-${itemCount}">School</label>
      <input type="text" id="setup-edu-school-${itemCount}" placeholder="e.g. University of California, Berkeley">
    </div>
    <div class="form-group">
      <label for="setup-edu-degree-${itemCount}">Degree</label>
      <input type="text" id="setup-edu-degree-${itemCount}" placeholder="e.g. Bachelor of Science in Computer Science">
    </div>
    <div class="form-row">
      <div class="form-group half">
        <label for="setup-edu-start-${itemCount}">Start Date</label>
        <input type="month" id="setup-edu-start-${itemCount}">
      </div>
      <div class="form-group half">
        <label for="setup-edu-end-${itemCount}">End Date</label>
        <input type="month" id="setup-edu-end-${itemCount}">
      </div>
    </div>
    <div class="form-group checkbox-group">
      <input type="checkbox" id="setup-edu-current-${itemCount}" class="setup-edu-current">
      <label for="setup-edu-current-${itemCount}">I'm currently studying here</label>
    </div>
    <div class="form-group">
      <label for="setup-edu-description-${itemCount}">Description</label>
      <textarea id="setup-edu-description-${itemCount}" rows="4" placeholder="Describe your studies, achievements, and relevant coursework..."></textarea>
    </div>
    <button type="button" class="remove-item-btn" onclick="this.parentNode.remove()">Remove</button>
  `;
  
  container.appendChild(newItem);
  
  // Add event listener for the current checkbox
  const currentCheckbox = document.getElementById(`setup-edu-current-${itemCount}`);
  currentCheckbox.addEventListener('change', (event) => {
    const endDateInput = document.getElementById(`setup-edu-end-${itemCount}`);
    endDateInput.disabled = event.target.checked;
    if (event.target.checked) {
      endDateInput.value = '';
    }
  });
}

/**
 * Add a new skill item to the setup form
 */
function addSkillItem() {
  const container = document.getElementById('setup-skills-items');
  const itemCount = container.querySelectorAll('.setup-skills-item').length + 1;
  
  const newItem = document.createElement('div');
  newItem.className = 'setup-skills-item';
  newItem.innerHTML = `
    <div class="form-row">
      <div class="form-group two-thirds">
        <label for="setup-skill-name-${itemCount}">Skill</label>
        <input type="text" id="setup-skill-name-${itemCount}" placeholder="e.g. JavaScript">
      </div>
      <div class="form-group one-third">
        <label for="setup-skill-level-${itemCount}">Level</label>
        <select id="setup-skill-level-${itemCount}">
          <option value="Beginner">Beginner</option>
          <option value="Intermediate">Intermediate</option>
          <option value="Advanced">Advanced</option>
          <option value="Expert">Expert</option>
        </select>
      </div>
    </div>
    <button type="button" class="remove-item-btn" onclick="this.parentNode.remove()">Remove</button>
  `;
  
  container.appendChild(newItem);
}

/**
 * Add a new language item to the setup form
 */
function addLanguageItem() {
  const container = document.getElementById('setup-languages-items');
  const itemCount = container.querySelectorAll('.setup-languages-item').length + 1;
  
  const newItem = document.createElement('div');
  newItem.className = 'setup-languages-item';
  newItem.innerHTML = `
    <div class="form-row">
      <div class="form-group two-thirds">
        <label for="setup-language-name-${itemCount}">Language</label>
        <input type="text" id="setup-language-name-${itemCount}" placeholder="e.g. English">
      </div>
      <div class="form-group one-third">
        <label for="setup-language-level-${itemCount}">Proficiency</label>
        <select id="setup-language-level-${itemCount}">
          <option value="Native">Native</option>
          <option value="Fluent">Fluent</option>
          <option value="Professional">Professional</option>
          <option value="Intermediate">Intermediate</option>
          <option value="Basic">Basic</option>
        </select>
      </div>
    </div>
    <button type="button" class="remove-item-btn" onclick="this.parentNode.remove()">Remove</button>
  `;
  
  container.appendChild(newItem);
}

/**
 * Add a new honor item to the setup form
 */
function addHonorItem() {
  const container = document.getElementById('setup-honors-items');
  const itemCount = container.querySelectorAll('.setup-honors-item').length + 1;
  
  const newItem = document.createElement('div');
  newItem.className = 'setup-honors-item';
  newItem.innerHTML = `
    <div class="form-group">
      <label for="setup-honor-title-${itemCount}">Title</label>
      <input type="text" id="setup-honor-title-${itemCount}" placeholder="e.g. Employee of the Year">
    </div>
    <div class="form-group">
      <label for="setup-honor-issuer-${itemCount}">Issuer</label>
      <input type="text" id="setup-honor-issuer-${itemCount}" placeholder="e.g. Google Inc.">
    </div>
    <div class="form-group">
      <label for="setup-honor-date-${itemCount}">Date</label>
      <input type="month" id="setup-honor-date-${itemCount}">
    </div>
    <div class="form-group">
      <label for="setup-honor-description-${itemCount}">Description</label>
      <textarea id="setup-honor-description-${itemCount}" rows="3" placeholder="Describe this honor or award..."></textarea>
    </div>
    <button type="button" class="remove-item-btn" onclick="this.parentNode.remove()">Remove</button>
  `;
  
  container.appendChild(newItem);
}

/**
 * Add a new course item to the setup form
 */
function addCourseItem() {
  const container = document.getElementById('setup-courses-items');
  const itemCount = container.querySelectorAll('.setup-courses-item').length + 1;
  
  const newItem = document.createElement('div');
  newItem.className = 'setup-courses-item';
  newItem.innerHTML = `
    <div class="form-group">
      <label for="setup-course-title-${itemCount}">Course/Certification Title</label>
      <input type="text" id="setup-course-title-${itemCount}" placeholder="e.g. AWS Certified Developer">
    </div>
    <div class="form-group">
      <label for="setup-course-provider-${itemCount}">Provider</label>
      <input type="text" id="setup-course-provider-${itemCount}" placeholder="e.g. Amazon Web Services">
    </div>
    <div class="form-group">
      <label for="setup-course-date-${itemCount}">Date Completed</label>
      <input type="month" id="setup-course-date-${itemCount}">
    </div>
    <div class="form-group">
      <label for="setup-course-credential-${itemCount}">Credential ID (optional)</label>
      <input type="text" id="setup-course-credential-${itemCount}" placeholder="e.g. ABC123XYZ">
    </div>
    <div class="form-group checkbox-group">
      <input type="checkbox" id="setup-course-expires-${itemCount}">
      <label for="setup-course-expires-${itemCount}">This credential expires</label>
    </div>
    <div class="form-group">
      <label for="setup-course-expiry-${itemCount}">Expiration Date</label>
      <input type="month" id="setup-course-expiry-${itemCount}" disabled>
    </div>
    <button type="button" class="remove-item-btn" onclick="this.parentNode.remove()">Remove</button>
  `;
  
  container.appendChild(newItem);
  
  // Add event listener for the expires checkbox
  const expiresCheckbox = document.getElementById(`setup-course-expires-${itemCount}`);
  expiresCheckbox.addEventListener('change', (event) => {
    const expiryDateInput = document.getElementById(`setup-course-expiry-${itemCount}`);
    expiryDateInput.disabled = !event.target.checked;
  });
}

/**
 * Add a new portfolio item to the setup form
 */
function addPortfolioItem() {
  const container = document.getElementById('setup-portfolio-items');
  const itemCount = container.querySelectorAll('.setup-portfolio-item').length + 1;
  
  const newItem = document.createElement('div');
  newItem.className = 'setup-portfolio-item';
  newItem.innerHTML = `
    <div class="form-group">
      <label for="setup-portfolio-title-${itemCount}">Project Title</label>
      <input type="text" id="setup-portfolio-title-${itemCount}" placeholder="e.g. E-commerce Website">
    </div>
    <div class="form-group">
      <label for="setup-portfolio-link-${itemCount}">Project Link</label>
      <input type="url" id="setup-portfolio-link-${itemCount}" placeholder="https://example.com/project">
    </div>
    <div class="form-group">
      <label for="setup-portfolio-start-${itemCount}">Start Date</label>
      <input type="month" id="setup-portfolio-start-${itemCount}">
    </div>
    <div class="form-group">
      <label for="setup-portfolio-end-${itemCount}">End Date</label>
      <input type="month" id="setup-portfolio-end-${itemCount}">
    </div>
    <div class="form-group">
      <label for="setup-portfolio-description-${itemCount}">Description</label>
      <textarea id="setup-portfolio-description-${itemCount}" rows="4" placeholder="Describe the project, technologies used, and your role..."></textarea>
    </div>
    <button type="button" class="remove-item-btn" onclick="this.parentNode.remove()">Remove</button>
  `;
  
  container.appendChild(newItem);
}

/**
 * Handle the setup profile form submission
 * @param {Event} event - The submit event
 */
function handleSetupProfileSubmit(event) {
  event.preventDefault();
  
  // Basic info
  const name = document.getElementById('setup-name').value;
  const title = document.getElementById('setup-title').value;
  const location = document.getElementById('setup-location').value;
  const website = document.getElementById('setup-website').value;
  const experience = parseInt(document.getElementById('setup-experience').value, 10) || 0;
  
  // All fields are optional in the form - no validation required
  
  // Security info
  const email = document.getElementById('setup-email').value;
  const password = document.getElementById('setup-password').value;
  const confirmPassword = document.getElementById('setup-confirm-password').value;
  const birthday = document.getElementById('setup-birthday').value;
  
  // Validate passwords match
  if (password && password !== confirmPassword) {
    showToast('Passwords do not match', 'error');
    switchSetupTab('security');
    return;
  }
  
  // Appearance
  let profileImage = profileData.personal.profileImage;
  let coverBackground = profileData.personal.coverBackground;
  
  // About
  const about = document.getElementById('setup-about').value.trim();
  const aboutWordCount = countWords(about);
  
  if (about && (aboutWordCount < 10 || aboutWordCount > 255)) {
    showToast('About section must be between 10 and 255 words', 'error');
    switchSetupTab('about');
    return;
  }
  
  // Experience
  const experienceItems = [];
  document.querySelectorAll('.setup-experience-item').forEach((item, index) => {
    const itemIndex = index + 1;
    const title = document.getElementById(`setup-exp-title-${itemIndex}`)?.value;
    const company = document.getElementById(`setup-exp-company-${itemIndex}`)?.value;
    const expLocation = document.getElementById(`setup-exp-location-${itemIndex}`)?.value;
    const startDate = document.getElementById(`setup-exp-start-${itemIndex}`)?.value;
    const isCurrent = document.getElementById(`setup-exp-current-${itemIndex}`)?.checked;
    const endDate = isCurrent ? 'present' : document.getElementById(`setup-exp-end-${itemIndex}`)?.value;
    const description = document.getElementById(`setup-exp-description-${itemIndex}`)?.value;
    
    if (title && company && startDate && (isCurrent || endDate)) {
      experienceItems.push({
        id: generateUniqueId(),
        title,
        company,
        location: expLocation,
        startDate,
        endDate,
        isCurrent,
        description
      });
    }
  });
  
  // Education
  const educationItems = [];
  document.querySelectorAll('.setup-education-item').forEach((item, index) => {
    const itemIndex = index + 1;
    const school = document.getElementById(`setup-edu-school-${itemIndex}`)?.value;
    const degree = document.getElementById(`setup-edu-degree-${itemIndex}`)?.value;
    const startDate = document.getElementById(`setup-edu-start-${itemIndex}`)?.value;
    const isCurrent = document.getElementById(`setup-edu-current-${itemIndex}`)?.checked;
    const endDate = isCurrent ? 'present' : document.getElementById(`setup-edu-end-${itemIndex}`)?.value;
    const description = document.getElementById(`setup-edu-description-${itemIndex}`)?.value;
    
    if (school && degree && startDate && (isCurrent || endDate)) {
      educationItems.push({
        id: generateUniqueId(),
        school,
        degree,
        startDate,
        endDate,
        isCurrent,
        description
      });
    }
  });
  
  // Skills
  const skillItems = [];
  document.querySelectorAll('.setup-skills-item').forEach((item, index) => {
    const itemIndex = index + 1;
    const name = document.getElementById(`setup-skill-name-${itemIndex}`)?.value;
    const level = document.getElementById(`setup-skill-level-${itemIndex}`)?.value;
    
    if (name && level) {
      skillItems.push({
        id: generateUniqueId(),
        name,
        level
      });
    }
  });
  
  // Languages
  const languageItems = [];
  document.querySelectorAll('.setup-languages-item').forEach((item, index) => {
    const itemIndex = index + 1;
    const name = document.getElementById(`setup-language-name-${itemIndex}`)?.value;
    const level = document.getElementById(`setup-language-level-${itemIndex}`)?.value;
    
    if (name && level) {
      languageItems.push({
        id: generateUniqueId(),
        name,
        level
      });
    }
  });
  
  // Honors
  const honorItems = [];
  document.querySelectorAll('.setup-honors-item').forEach((item, index) => {
    const itemIndex = index + 1;
    const title = document.getElementById(`setup-honor-title-${itemIndex}`)?.value;
    const issuer = document.getElementById(`setup-honor-issuer-${itemIndex}`)?.value;
    const date = document.getElementById(`setup-honor-date-${itemIndex}`)?.value;
    const description = document.getElementById(`setup-honor-description-${itemIndex}`)?.value;
    
    if (title && issuer && date) {
      honorItems.push({
        id: generateUniqueId(),
        title,
        issuer,
        date,
        description
      });
    }
  });
  
  // Courses
  const courseItems = [];
  document.querySelectorAll('.setup-courses-item').forEach((item, index) => {
    const itemIndex = index + 1;
    const title = document.getElementById(`setup-course-title-${itemIndex}`)?.value;
    const provider = document.getElementById(`setup-course-provider-${itemIndex}`)?.value;
    const date = document.getElementById(`setup-course-date-${itemIndex}`)?.value;
    const credential = document.getElementById(`setup-course-credential-${itemIndex}`)?.value;
    const expires = document.getElementById(`setup-course-expires-${itemIndex}`)?.checked;
    const expiryDate = expires ? document.getElementById(`setup-course-expiry-${itemIndex}`)?.value : null;
    
    if (title && provider && date) {
      courseItems.push({
        id: generateUniqueId(),
        title,
        provider,
        date,
        credential,
        expires,
        expiryDate
      });
    }
  });
  
  // Portfolio
  const portfolioItems = [];
  document.querySelectorAll('.setup-portfolio-item').forEach((item, index) => {
    const itemIndex = index + 1;
    const title = document.getElementById(`setup-portfolio-title-${itemIndex}`)?.value;
    const link = document.getElementById(`setup-portfolio-link-${itemIndex}`)?.value;
    const startDate = document.getElementById(`setup-portfolio-start-${itemIndex}`)?.value;
    const endDate = document.getElementById(`setup-portfolio-end-${itemIndex}`)?.value;
    const description = document.getElementById(`setup-portfolio-description-${itemIndex}`)?.value;
    
    if (title && description) {
      portfolioItems.push({
        id: generateUniqueId(),
        title,
        link,
        startDate,
        endDate,
        description
      });
    }
  });
  
  // Seeking
  const seekingOptions = [];
  document.querySelectorAll('#seeking-tab-content .seeking-checkbox:checked').forEach(checkbox => {
    seekingOptions.push(checkbox.parentElement.querySelector('label').textContent);
  });
  
  const seekingAdditional = document.getElementById('setup-seeking-additional').value.trim();
  let seekingContent = '';
  
  if (seekingOptions.length > 0) {
    seekingContent = `Looking for ${seekingOptions.join(', ')}.`;
    if (seekingAdditional) {
      seekingContent += ' ' + seekingAdditional;
    }
  } else if (seekingAdditional) {
    seekingContent = seekingAdditional;
  }
  
  // Update profile data
  profileData.personal.name = name;
  profileData.personal.title = title;
  profileData.personal.location = location;
  profileData.personal.website = website;
  profileData.personal.yearsExperience = experience;
  profileData.personal.email = email;
  profileData.personal.password = password;
  profileData.personal.birthday = birthday;
  profileData.personal.profileCreated = true;
  
  profileData.about = about;
  profileData.experience = experienceItems;
  profileData.education = educationItems;
  profileData.skills = skillItems;
  profileData.languages = languageItems;
  profileData.honors = honorItems;
  profileData.courses = courseItems;
  profileData.portfolio = portfolioItems;
  profileData.seeking = seekingContent;
  
  // Extract top skills from experience
  if (experienceItems.length > 0) {
    updateTopSkillsFromExperience();
  } else if (skillItems.length > 0) {
    // Use top skills from the skills list
    profileData.personal.topSkills = skillItems.slice(0, 3).map(skill => skill.name);
  }
  
  // Save data
  saveProfileData();
  
  // Show success message and redirect to profile page
  showToast('Profile setup completed successfully!');
  
  // Redirect to profile page after a short delay
  setTimeout(() => {
    window.location.href = 'user.profile.html';
  }, 1500);
}

/**
 * Pre-fill form with existing profile data - completely empty for first time users
 */
function prefillFormWithProfileData() {
  // Keep the form completely empty for first-time setup
  return;
  
  // About
  document.getElementById('setup-about').value = profileData.about || '';
  const aboutWordCount = countWords(profileData.about || '');
  document.getElementById('setup-about-word-count').textContent = aboutWordCount;
  
  // Seeking
  if (profileData.seeking && profileData.seeking.startsWith('Looking for ')) {
    const lookingForText = profileData.seeking.substring('Looking for '.length);
    let options = [];
    let additionalText = '';
    
    const periodIndex = lookingForText.indexOf('.');
    if (periodIndex !== -1) {
      const listPart = lookingForText.substring(0, periodIndex);
      additionalText = lookingForText.substring(periodIndex + 1).trim();
      options = listPart.split(', ').map(option => option.trim());
      
      // Check the boxes that match options
      options.forEach(option => {
        let inputId = '';
        
        if (option.includes('Leadership')) {
          inputId = 'setup-seeking-leadership';
        } else if (option.includes('Mentoring')) {
          inputId = 'setup-seeking-mentoring';
        } else if (option.includes('Remote')) {
          inputId = 'setup-seeking-remote';
        } else if (option.includes('Freelance')) {
          inputId = 'setup-seeking-freelance';
        } else if (option.includes('Full-time')) {
          inputId = 'setup-seeking-fulltime';
        } else if (option.includes('Startup')) {
          inputId = 'setup-seeking-startup';
        } else if (option.includes('Consulting')) {
          inputId = 'setup-seeking-consulting';
        } else if (option.includes('International')) {
          inputId = 'setup-seeking-international';
        }
        
        if (inputId) {
          const checkbox = document.getElementById(inputId);
          if (checkbox) checkbox.checked = true;
        }
      });
      
      // Set additional text
      document.getElementById('setup-seeking-additional').value = additionalText;
    }
  }
  
  // Experience
  if (profileData.experience && profileData.experience.length > 0) {
    // Remove the default item
    document.getElementById('setup-experience-items').innerHTML = '';
    
    // Add each experience item
    profileData.experience.forEach((exp, index) => {
      addExperienceItem();
      const itemIndex = index + 1;
      
      document.getElementById(`setup-exp-title-${itemIndex}`).value = exp.title || '';
      document.getElementById(`setup-exp-company-${itemIndex}`).value = exp.company || '';
      document.getElementById(`setup-exp-location-${itemIndex}`).value = exp.location || '';
      document.getElementById(`setup-exp-start-${itemIndex}`).value = exp.startDate || '';
      
      const currentCheckbox = document.getElementById(`setup-exp-current-${itemIndex}`);
      if (exp.isCurrent) {
        currentCheckbox.checked = true;
        document.getElementById(`setup-exp-end-${itemIndex}`).disabled = true;
      } else {
        document.getElementById(`setup-exp-end-${itemIndex}`).value = exp.endDate || '';
      }
      
      document.getElementById(`setup-exp-description-${itemIndex}`).value = exp.description || '';
    });
  }
  
  // Education
  if (profileData.education && profileData.education.length > 0) {
    // Remove the default item
    document.getElementById('setup-education-items').innerHTML = '';
    
    // Add each education item
    profileData.education.forEach((edu, index) => {
      addEducationItem();
      const itemIndex = index + 1;
      
      document.getElementById(`setup-edu-school-${itemIndex}`).value = edu.school || '';
      document.getElementById(`setup-edu-degree-${itemIndex}`).value = edu.degree || '';
      document.getElementById(`setup-edu-start-${itemIndex}`).value = edu.startDate || '';
      
      const currentCheckbox = document.getElementById(`setup-edu-current-${itemIndex}`);
      if (edu.isCurrent) {
        currentCheckbox.checked = true;
        document.getElementById(`setup-edu-end-${itemIndex}`).disabled = true;
      } else {
        document.getElementById(`setup-edu-end-${itemIndex}`).value = edu.endDate || '';
      }
      
      document.getElementById(`setup-edu-description-${itemIndex}`).value = edu.description || '';
    });
  }
  
  // Skills
  if (profileData.skills && profileData.skills.length > 0) {
    // Clear existing skills
    document.getElementById('setup-skills-items').innerHTML = '';
    
    // Add each skill item
    profileData.skills.forEach((skill, index) => {
      if (index > 0) addSkillItem();
      const itemIndex = index + 1;
      
      const nameInput = document.getElementById(`setup-skill-name-${itemIndex}`);
      const levelSelect = document.getElementById(`setup-skill-level-${itemIndex}`);
      
      if (nameInput) nameInput.value = skill.name || '';
      if (levelSelect) levelSelect.value = skill.level || 'Intermediate';
    });
  }
}

/**
 * Initialize event listeners
 */
function initializeEventListeners() {
  // Setup about section word count
  const setupAbout = document.getElementById('setup-about');
  if (setupAbout) {
    setupAbout.addEventListener('input', (event) => {
      const text = event.target.value;
      const wordCount = countWords(text);
      const wordCountElement = document.getElementById('setup-about-word-count');
      if (wordCountElement) {
        wordCountElement.textContent = wordCount;
        
        if (wordCount < 10 || wordCount > 255) {
          wordCountElement.style.color = 'var(--error-color)';
        } else {
          wordCountElement.style.color = 'var(--text-tertiary)';
        }
      }
    });
  }
  
  // Setup tab navigation
  document.querySelectorAll('.setup-tab').forEach(tab => {
    tab.addEventListener('click', (event) => {
      const tabId = event.target.getAttribute('data-tab');
      switchSetupTab(tabId);
    });
  });
  
  // Previous/Next tab buttons
  const prevTabBtn = document.getElementById('prev-tab-btn');
  const nextTabBtn = document.getElementById('next-tab-btn');
  
  if (prevTabBtn) prevTabBtn.addEventListener('click', navigatePreviousTab);
  if (nextTabBtn) nextTabBtn.addEventListener('click', navigateNextTab);
  
  // Setup "Add more" buttons
  const addExpBtn = document.getElementById('add-experience-btn');
  const addEduBtn = document.getElementById('add-education-btn');
  const addSkillBtn = document.getElementById('add-skill-btn');
  const addLangBtn = document.getElementById('add-language-btn');
  const addHonorBtn = document.getElementById('add-honor-btn');
  const addCourseBtn = document.getElementById('add-course-btn');
  const addPortfolioBtn = document.getElementById('add-portfolio-btn');
  
  if (addExpBtn) addExpBtn.addEventListener('click', addExperienceItem);
  if (addEduBtn) addEduBtn.addEventListener('click', addEducationItem);
  if (addSkillBtn) addSkillBtn.addEventListener('click', addSkillItem);
  if (addLangBtn) addLangBtn.addEventListener('click', addLanguageItem);
  if (addHonorBtn) addHonorBtn.addEventListener('click', addHonorItem);
  if (addCourseBtn) addCourseBtn.addEventListener('click', addCourseItem);
  if (addPortfolioBtn) addPortfolioBtn.addEventListener('click', addPortfolioItem);
  
  // Setup form submission
  const setupForm = document.getElementById('setup-profile-form');
  if (setupForm) setupForm.addEventListener('submit', handleSetupProfileSubmit);
  
  // Current checkboxes
  document.querySelectorAll('.setup-exp-current').forEach(checkbox => {
    checkbox.addEventListener('change', (event) => {
      const id = event.target.id;
      const number = id.split('-').pop();
      const endDateInput = document.getElementById(`setup-exp-end-${number}`);
      if (endDateInput) {
        endDateInput.disabled = event.target.checked;
        if (event.target.checked) {
          endDateInput.value = '';
        }
      }
    });
  });
  
  document.querySelectorAll('.setup-edu-current').forEach(checkbox => {
    checkbox.addEventListener('change', (event) => {
      const id = event.target.id;
      const number = id.split('-').pop();
      const endDateInput = document.getElementById(`setup-edu-end-${number}`);
      if (endDateInput) {
        endDateInput.disabled = event.target.checked;
        if (event.target.checked) {
          endDateInput.value = '';
        }
      }
    });
  });
  
  // Course expiry checkboxes
  document.querySelectorAll('[id^="setup-course-expires-"]').forEach(checkbox => {
    checkbox.addEventListener('change', (event) => {
      const id = event.target.id.replace('setup-course-expires-', '');
      const expiryDateInput = document.getElementById(`setup-course-expiry-${id}`);
      if (expiryDateInput) {
        expiryDateInput.disabled = !event.target.checked;
      }
    });
  });
  
  // Profile picture upload handler
  const profileUploadBtn = document.getElementById('upload-profile-btn');
  const profileUploadInput = document.getElementById('profile-upload-setup');
  const profilePreviewImg = document.getElementById('profile-preview-img');
  
  if (profileUploadBtn && profileUploadInput && profilePreviewImg) {
    profileUploadBtn.addEventListener('click', () => {
      profileUploadInput.click();
    });
    
    profileUploadInput.addEventListener('change', (event) => {
      if (event.target.files && event.target.files[0]) {
        const reader = new FileReader();
        reader.onload = (e) => {
          profilePreviewImg.src = e.target.result;
          profileData.personal.profileImage = e.target.result;
        };
        reader.readAsDataURL(event.target.files[0]);
      }
    });
  }
  
  // Cover color picker handlers
  const colorOptions = document.querySelectorAll('.color-option');
  const gradientOptions = document.querySelectorAll('.gradient-option');
  const customColorInput = document.getElementById('custom-color-input-setup');
  const coverPreview = document.getElementById('cover-preview');
  
  if (colorOptions && coverPreview) {
    colorOptions.forEach(option => {
      option.addEventListener('click', () => {
        const color = option.getAttribute('data-color');
        coverPreview.style.background = color;
        profileData.personal.coverBackground = color;
      });
    });
  }
  
  if (gradientOptions && coverPreview) {
    gradientOptions.forEach(option => {
      option.addEventListener('click', () => {
        const gradient = option.getAttribute('data-gradient');
        coverPreview.style.background = gradient;
        profileData.personal.coverBackground = gradient;
      });
    });
  }
  
  if (customColorInput && coverPreview) {
    customColorInput.addEventListener('input', () => {
      const color = customColorInput.value;
      coverPreview.style.background = color;
      profileData.personal.coverBackground = color;
    });
  }
  
  // Password toggle functionality
  const togglePasswordBtns = document.querySelectorAll('.toggle-password');
  togglePasswordBtns.forEach(btn => {
    btn.addEventListener('click', () => {
      const passwordInput = btn.previousElementSibling;
      const type = passwordInput.getAttribute('type');
      passwordInput.setAttribute('type', type === 'password' ? 'text' : 'password');
      
      // Toggle icon
      const icon = btn.querySelector('i');
      if (icon.classList.contains('fa-eye')) {
        icon.classList.remove('fa-eye');
        icon.classList.add('fa-eye-slash');
      } else {
        icon.classList.remove('fa-eye-slash');
        icon.classList.add('fa-eye');
      }
    });
  });
  
  // Password strength meter
  const passwordInput = document.getElementById('setup-password');
  const strengthMeter = document.getElementById('setup-password-strength-meter');
  const strengthText = document.getElementById('setup-password-strength-text');
  
  if (passwordInput && strengthMeter && strengthText) {
    passwordInput.addEventListener('input', () => {
      const password = passwordInput.value;
      let strength = 0;
      let message = '';
      
      if (password.length > 0) {
        // Check password length
        if (password.length >= 8) strength += 25;
        
        // Check for lowercase letters
        if (/[a-z]/.test(password)) strength += 25;
        
        // Check for uppercase letters
        if (/[A-Z]/.test(password)) strength += 25;
        
        // Check for numbers or special characters
        if (/[0-9!@#$%^&*]/.test(password)) strength += 25;
        
        // Set message based on strength
        if (strength <= 25) {
          message = 'Weak';
          strengthMeter.style.backgroundColor = '#ff4d4d';
        } else if (strength <= 50) {
          message = 'Fair';
          strengthMeter.style.backgroundColor = '#ffa64d';
        } else if (strength <= 75) {
          message = 'Good';
          strengthMeter.style.backgroundColor = '#ffff4d';
        } else {
          message = 'Strong';
          strengthMeter.style.backgroundColor = '#4dff4d';
        }
      } else {
        message = 'Password strength';
      }
      
      strengthMeter.style.width = `${strength}%`;
      strengthText.textContent = message;
    });
  }
}

// Initialize when the DOM is loaded
document.addEventListener('DOMContentLoaded', () => {
  loadProfileData();
  prefillFormWithProfileData();
  initializeEventListeners();
  
  // Start with the first tab active and update progress bar
  switchSetupTab('basic');
});