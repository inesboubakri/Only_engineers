/**
 * OnlyEngineers User Profile
 * 
 * This JavaScript file contains all the logic for the OnlyEngineers
 * user profile page, including:
 * - Profile data management
 * - CRUD operations for all profile sections
 * - UI interactions and event handlers
 * - Form validation
 * - Local storage persistence
 */

// Global variables
let currentDeleteItem = null;
let currentEditingSection = null;
let currentCoverBackground = 'linear-gradient(135deg, #4285F4, #34A853)';
let profileData = {
  personal: {
    name: 'Your Name',
    title: 'Software Engineer',
    location: 'San Francisco, CA',
    website: 'www.yourwebsite.com',
    yearsExperience: 5,
    topSkills: ['JavaScript', 'React', 'Node.js'],
    languages: ['English (Native)', 'Spanish (Professional)', 'French (Basic)'],
    profileImage: 'assets/default-profile.svg',
    coverBackground: 'linear-gradient(135deg, #4285F4, #34A853)'
  },
  settings: {
    email: 'user@example.com',
    password: 'ZAMANKII1',
    birthday: '1990-01-01',
    phone: '+1 555-123-4567',
    notifications: {
      messages: true,
      network: true,
      jobs: false
    }
  },
  about: 'A passionate software engineer with expertise in full-stack development, focused on creating elegant solutions to complex problems. Experienced in building scalable web applications and optimizing performance.',
  seeking: 'Looking for challenging opportunities in software architecture and team leadership. Interested in roles that involve mentoring junior developers and working on innovative products with a global impact.',
  experience: [],
  education: [],
  skills: [],
  languages: [],
  honors: [],
  courses: [],
  portfolio: []
};

// DOM loaded event
document.addEventListener('DOMContentLoaded', () => {
  loadProfileData();
  initializeEventListeners();
  renderProfileData();
  initializeExampleData();
});

/**
 * Load profile data from localStorage if available
 */
function loadProfileData() {
  const savedData = localStorage.getItem('profileData');
  if (savedData) {
    try {
      profileData = JSON.parse(savedData);
    } catch (error) {
      console.error('Error parsing profile data from localStorage:', error);
      // Continue with default data if parse error
    }
  }
}

/**
 * Save profile data to localStorage
 */
function saveProfileData() {
  try {
    localStorage.setItem('profileData', JSON.stringify(profileData));
  } catch (error) {
    console.error('Error saving profile data to localStorage:', error);
    showToast('Error saving your data. Please try again.', 'error');
  }
}

/**
 * Initialize example data for the profile if sections are empty
 */
function initializeExampleData() {
  // Only add example data if sections are empty
  if (profileData.experience.length === 0) {
    profileData.experience = [
      {
        id: generateUniqueId(),
        title: 'Senior Software Engineer',
        company: 'TechCorp Inc.',
        startDate: '2019-03',
        endDate: 'present',
        isCurrent: true,
        location: 'San Francisco, CA',
        description: 'Lead developer for the company\'s flagship product. Managed a team of 5 engineers and implemented CI/CD pipelines that reduced deployment time by 40%.'
      },
      {
        id: generateUniqueId(),
        title: 'Software Engineer',
        company: 'DevStart',
        startDate: '2016-06',
        endDate: '2019-02',
        isCurrent: false,
        location: 'New York, NY',
        description: 'Developed microservices architecture for scalable applications. Optimized database queries resulting in 30% faster page load times.'
      }
    ];
  }

  if (profileData.education.length === 0) {
    profileData.education = [
      {
        id: generateUniqueId(),
        school: 'Stanford University',
        degree: 'Master of Science in Computer Science',
        startDate: '2014-09',
        endDate: '2016-05',
        isCurrent: false,
        description: 'Specialized in Artificial Intelligence and Machine Learning. Active member of the Computer Science Association.'
      },
      {
        id: generateUniqueId(),
        school: 'University of California, Berkeley',
        degree: 'Bachelor of Science in Computer Engineering',
        startDate: '2010-09',
        endDate: '2014-05',
        isCurrent: false,
        description: 'Graduated with honors. Participated in multiple hackathons and coding competitions.'
      }
    ];
  }

  if (profileData.skills.length === 0) {
    profileData.skills = [
      { id: generateUniqueId(), name: 'JavaScript', level: 'Expert' },
      { id: generateUniqueId(), name: 'React', level: 'Advanced' },
      { id: generateUniqueId(), name: 'Node.js', level: 'Advanced' },
      { id: generateUniqueId(), name: 'AWS', level: 'Intermediate' },
      { id: generateUniqueId(), name: 'Python', level: 'Intermediate' },
      { id: generateUniqueId(), name: 'Docker', level: 'Advanced' }
    ];
  }

  if (profileData.languages.length === 0) {
    profileData.languages = [
      { id: generateUniqueId(), name: 'English', proficiency: 'Native' },
      { id: generateUniqueId(), name: 'Spanish', proficiency: 'Professional' },
      { id: generateUniqueId(), name: 'French', proficiency: 'Basic' }
    ];
  }

  if (profileData.honors.length === 0) {
    profileData.honors = [
      {
        id: generateUniqueId(),
        title: 'Outstanding Achievement Award',
        issuer: 'TechCorp Inc.',
        date: '2020-11',
        description: 'Awarded for leading a project that increased company revenue by 25%.'
      }
    ];
  }

  if (profileData.courses.length === 0) {
    profileData.courses = [
      {
        id: generateUniqueId(),
        title: 'Advanced Machine Learning',
        provider: 'Coursera',
        date: '2021-06',
        credential: 'ML1234567',
        url: 'https://www.coursera.org/verify/ML1234567'
      }
    ];
  }

  if (profileData.portfolio.length === 0) {
    profileData.portfolio = [
      {
        id: generateUniqueId(),
        title: 'E-commerce Platform',
        description: 'A full-stack e-commerce solution with real-time inventory management and payment processing.',
        url: 'https://github.com/yourusername/ecommerce',
        technologies: 'React, Node.js, MongoDB, Stripe',
        date: '2021-08'
      }
    ];
  }

  renderAllSections();
  saveProfileData();
}

/**
 * Initialize all event listeners
 */
function initializeEventListeners() {
  // Profile picture upload
  document.getElementById('profile-picture').addEventListener('click', () => {
    document.getElementById('profile-upload').click();
  });
  
  document.getElementById('profile-upload').addEventListener('change', handleProfileImageUpload);
  
  // Cover photo edit
  document.getElementById('edit-cover-btn').addEventListener('click', openColorPaletteModal);
  
  // Settings buttons
  document.getElementById('settings-btn').addEventListener('click', openSettingsModal);
  document.getElementById('nav-settings-btn').addEventListener('click', openSettingsModal);
  
  // Edit profile button
  document.getElementById('edit-profile-btn').addEventListener('click', openEditProfileModal);
  
  // Close all modals
  document.querySelectorAll('.close-modal').forEach(closeBtn => {
    closeBtn.addEventListener('click', closeAllModals);
  });
  
  // Settings form
  document.getElementById('settings-cancel').addEventListener('click', closeAllModals);
  
  // Edit profile form
  document.getElementById('edit-profile-form').addEventListener('submit', handleEditProfileSubmit);
  document.getElementById('profile-edit-cancel').addEventListener('click', closeAllModals);
  
  // Color palette
  document.getElementById('color-palette-save').addEventListener('click', applySelectedColor);
  document.getElementById('color-palette-cancel').addEventListener('click', closeAllModals);
  document.querySelectorAll('.color-option').forEach(option => {
    option.addEventListener('click', selectColorOption);
  });
  document.querySelectorAll('.gradient-option').forEach(option => {
    option.addEventListener('click', selectGradientOption);
  });
  document.getElementById('custom-color-input').addEventListener('change', updateCustomColorPreview);
  
  // Password toggle buttons
  document.querySelectorAll('.toggle-password').forEach(button => {
    button.addEventListener('click', togglePassword);
  });
  
  // Delete confirmation
  document.getElementById('delete-cancel').addEventListener('click', closeAllModals);
  document.getElementById('delete-confirm').addEventListener('click', confirmDelete);
  
  // Setup profile button - only show if profile hasn't been created yet
  if (!profileData.personal.profileCreated) {
    const setupProfileBtn = document.createElement('a');
    setupProfileBtn.id = 'setup-profile-btn';
    setupProfileBtn.className = 'setup-profile-btn';
    setupProfileBtn.innerHTML = '<i class="fas fa-user-cog"></i> Setup Profile';
    setupProfileBtn.href = 'setup.profile.html';
    
    // Add the setup profile button to the profile header
    const profileInfo = document.querySelector('.profile-info');
    if (profileInfo) {
      const buttonContainer = document.createElement('div');
      buttonContainer.className = 'setup-btn-container';
      buttonContainer.appendChild(setupProfileBtn);
      profileInfo.appendChild(buttonContainer);
    }
  }
  
  // Setup profile navigation
  document.querySelectorAll('.setup-tab').forEach(tab => {
    tab.addEventListener('click', (event) => {
      const tabId = event.target.getAttribute('data-tab');
      switchSetupTab(tabId);
    });
  });
  
  document.getElementById('prev-tab-btn')?.addEventListener('click', navigatePreviousTab);
  document.getElementById('next-tab-btn')?.addEventListener('click', navigateNextTab);
  
  // Setup profile form
  document.getElementById('setup-profile-form')?.addEventListener('submit', handleSetupProfileSubmit);
  
  // Setup about section word count
  document.getElementById('setup-about')?.addEventListener('input', (event) => {
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
  
  // Setup current checkboxes
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
  
  // Setup add more buttons
  document.getElementById('add-experience-btn')?.addEventListener('click', addExperienceItem);
  document.getElementById('add-education-btn')?.addEventListener('click', addEducationItem);
  document.getElementById('add-skill-btn')?.addEventListener('click', addSkillItem);
  
  // About section
  document.querySelector('[data-section="about"].edit-btn').addEventListener('click', () => {
    openEditSection('about');
  });
  document.querySelector('[data-section="about"].save-btn').addEventListener('click', () => {
    saveSection('about');
  });
  document.querySelector('[data-section="about"].cancel-btn').addEventListener('click', () => {
    cancelEditSection('about');
  });
  document.getElementById('about-textarea').addEventListener('input', updateWordCount);
  
  // Seeking section
  document.querySelector('[data-section="seeking"].edit-btn').addEventListener('click', () => {
    openEditSection('seeking');
  });
  document.querySelector('[data-section="seeking"].save-btn').addEventListener('click', () => {
    saveSection('seeking');
  });
  document.querySelector('[data-section="seeking"].cancel-btn').addEventListener('click', () => {
    cancelEditSection('seeking');
  });
  
  // Experience section
  document.querySelector('[data-section="experience"].add-btn').addEventListener('click', () => {
    openAddItemForm('experience');
  });
  document.getElementById('experience-form').addEventListener('submit', handleExperienceSubmit);
  document.querySelector('[data-section="experience"].cancel-btn').addEventListener('click', () => {
    cancelEditSection('experience');
  });
  document.getElementById('exp-current').addEventListener('change', handleCurrentCheckbox);
  
  // Education section
  document.querySelector('[data-section="education"].add-btn').addEventListener('click', () => {
    openAddItemForm('education');
  });
  document.getElementById('education-form').addEventListener('submit', handleEducationSubmit);
  document.querySelector('[data-section="education"].cancel-btn').addEventListener('click', () => {
    cancelEditSection('education');
  });
  document.getElementById('edu-current').addEventListener('change', handleEducationCurrentCheckbox);
  
  // Skills section
  document.querySelector('[data-section="skills"].add-btn').addEventListener('click', () => {
    openAddItemForm('skills');
  });
  document.getElementById('skills-form').addEventListener('submit', handleSkillSubmit);
  document.querySelector('[data-section="skills"].cancel-btn').addEventListener('click', () => {
    cancelEditSection('skills');
  });
  
  // Languages section
  document.querySelector('[data-section="languages"].add-btn').addEventListener('click', () => {
    openAddItemForm('languages');
  });
  document.getElementById('languages-form').addEventListener('submit', handleLanguageSubmit);
  // Add direct click handler for languages save button
  const languagesSaveBtn = document.querySelector('#languages-edit .save-btn');
  if (languagesSaveBtn) {
    languagesSaveBtn.addEventListener('click', function() {
      handleLanguageSubmit(new Event('submit'));
    });
  }
  document.querySelector('[data-section="languages"].cancel-btn').addEventListener('click', () => {
    cancelEditSection('languages');
  });
  
  // Honors section
  document.querySelector('[data-section="honors"].add-btn').addEventListener('click', () => {
    openAddItemForm('honors');
  });
  document.getElementById('honors-form').addEventListener('submit', handleHonorSubmit);
  document.querySelector('[data-section="honors"].cancel-btn').addEventListener('click', () => {
    cancelEditSection('honors');
  });
  
  // Courses section
  document.querySelector('[data-section="courses"].add-btn').addEventListener('click', () => {
    openAddItemForm('courses');
  });
  document.getElementById('courses-form').addEventListener('submit', handleCourseSubmit);
  document.querySelector('[data-section="courses"].cancel-btn').addEventListener('click', () => {
    cancelEditSection('courses');
  });
  
  // Portfolio section
  document.querySelector('[data-section="portfolio"].add-btn').addEventListener('click', () => {
    openAddItemForm('portfolio');
  });
  document.getElementById('portfolio-form').addEventListener('submit', handlePortfolioSubmit);
  document.querySelector('[data-section="portfolio"].cancel-btn').addEventListener('click', () => {
    cancelEditSection('portfolio');
  });
  
  // Initialize all event listeners for section forms
  initializeSectionFormListeners();
}

/**
 * Initialize all event listeners for section forms
 */
function initializeSectionFormListeners() {
  // Experience form
  document.getElementById('experience-form').addEventListener('submit', handleExperienceSubmit);
  
  // Education form
  document.getElementById('education-form').addEventListener('submit', handleEducationSubmit);
  
  // Skills form
  document.getElementById('skills-form').addEventListener('submit', handleSkillSubmit);
  // Add direct click handler for skills save button
  const skillsSaveBtn = document.querySelector('#skills-edit .save-btn');
  if (skillsSaveBtn) {
    skillsSaveBtn.addEventListener('click', function() {
      handleSkillSubmit(new Event('submit'));
    });
  }
  
  // Languages form
  document.getElementById('languages-form').addEventListener('submit', handleLanguageSubmit);
  // Add direct click handler for languages save button
  const languagesSaveBtn = document.querySelector('#languages-edit .save-btn');
  if (languagesSaveBtn) {
    languagesSaveBtn.addEventListener('click', function() {
      handleLanguageSubmit(new Event('submit'));
    });
  }
  
  // Honors form
  document.getElementById('honors-form').addEventListener('submit', handleHonorSubmit);
  // Add direct click handler for honors save button
  const honorsSaveBtn = document.querySelector('#honors-edit .save-btn');
  if (honorsSaveBtn) {
    honorsSaveBtn.addEventListener('click', function() {
      handleHonorSubmit(new Event('submit'));
    });
  }
  
  // Courses form
  document.getElementById('courses-form').addEventListener('submit', handleCourseSubmit);
  
  // Portfolio form
  document.getElementById('portfolio-form').addEventListener('submit', handlePortfolioSubmit);
}

/**
 * Render all profile data sections
 */
function renderProfileData() {
  // Render personal information
  document.getElementById('profile-name').textContent = profileData.personal.name;
  document.getElementById('profile-title').textContent = profileData.personal.title;
  document.getElementById('profile-location').textContent = profileData.personal.location;
  document.getElementById('website-link').textContent = profileData.personal.website;
  document.getElementById('website-link').href = profileData.personal.website.startsWith('http') ? 
    profileData.personal.website : `https://${profileData.personal.website}`;
  document.getElementById('years-experience').textContent = profileData.personal.yearsExperience;
  
  // Render profile image
  const profileImg = document.getElementById('profile-img');
  profileImg.src = profileData.personal.profileImage || 'assets/default-profile.svg';
  
  // Render cover background
  const coverPhoto = document.getElementById('cover-photo');
  coverPhoto.style.background = profileData.personal.coverBackground;
  currentCoverBackground = profileData.personal.coverBackground;
  
  // Render top skills
  const topSkillsList = document.getElementById('top-skills-list');
  topSkillsList.innerHTML = '';
  profileData.personal.topSkills.forEach(skill => {
    const li = document.createElement('li');
    li.textContent = skill;
    topSkillsList.appendChild(li);
  });
  
  // Render languages
  const languagesList = document.getElementById('languages-list');
  languagesList.innerHTML = '';
  profileData.personal.languages.forEach(language => {
    const li = document.createElement('li');
    li.textContent = language;
    languagesList.appendChild(li);
  });
  
  // Render about
  document.getElementById('about-content').innerHTML = `<p>${profileData.about}</p>`;
  
  // Render seeking
  // Try to detect if the seeking content contains specific patterns to display as a list
  let seekingContent = profileData.seeking;
  
  // First, clear all checkboxes
  document.querySelectorAll('.seeking-checkbox').forEach(checkbox => {
    checkbox.checked = false;
  });
  
  // Extract additional text (if any) to put in the textarea
  let additionalText = '';
  
  // Check if the content follows our "Looking for X, Y, Z." format
  if (seekingContent && seekingContent.startsWith('Looking for ')) {
    const lookingForText = seekingContent.substring('Looking for '.length);
    
    // Check if there's a period that splits the list from additional text
    const periodIndex = lookingForText.indexOf('.');
    if (periodIndex !== -1) {
      const listPart = lookingForText.substring(0, periodIndex);
      additionalText = lookingForText.substring(periodIndex + 1).trim();
      
      // Check boxes that match the options
      const options = listPart.split(', ').map(option => option.trim());
      options.forEach(option => {
        const matchingCheckbox = Array.from(document.querySelectorAll('.seeking-checkbox'))
          .find(checkbox => checkbox.parentElement.querySelector('label').textContent === option);
        if (matchingCheckbox) {
          matchingCheckbox.checked = true;
        }
      });
    }
  } else {
    // If it doesn't match our format, just put everything in the textarea
    additionalText = seekingContent;
  }
  
  document.getElementById('seeking-textarea').value = additionalText;
  
  // Render all sections
  renderAllSections();
}

/**
 * Render all dynamic sections of the profile
 */
function renderAllSections() {
  renderExperienceItems();
  renderEducationItems();
  renderSkillItems();
  renderLanguageItems();
  renderHonorItems();
  renderCourseItems();
  renderPortfolioItems();
}

/**
 * Handle profile image upload
 * @param {Event} event - The upload event
 */
function handleProfileImageUpload(event) {
  const file = event.target.files[0];
  if (file) {
    // Check if the file is an image
    if (!file.type.match('image.*')) {
      showToast('Please select an image file', 'error');
      return;
    }
    
    // Check if the file size is less than 5MB
    if (file.size > 5 * 1024 * 1024) {
      showToast('Image size should be less than 5MB', 'error');
      return;
    }
    
    const reader = new FileReader();
    reader.onload = function(e) {
      // Update profile image in the UI
      document.getElementById('profile-img').src = e.target.result;
      
      // Update profile data
      profileData.personal.profileImage = e.target.result;
      saveProfileData();
      
      showToast('Profile picture updated successfully');
    };
    reader.readAsDataURL(file);
  }
}

/**
 * Open the color palette modal for cover photo
 */
function openColorPaletteModal() {
  const modal = document.getElementById('color-palette-modal');
  modal.style.display = 'block';
  
  // Reset selected options
  document.querySelectorAll('.color-option.selected, .gradient-option.selected').forEach(item => {
    item.classList.remove('selected');
  });
  
  // Select current color/gradient
  if (currentCoverBackground.includes('linear-gradient')) {
    const gradientOptions = document.querySelectorAll('.gradient-option');
    for (const option of gradientOptions) {
      if (option.getAttribute('data-gradient') === currentCoverBackground) {
        option.classList.add('selected');
        break;
      }
    }
  } else {
    const colorOptions = document.querySelectorAll('.color-option');
    for (const option of colorOptions) {
      if (option.getAttribute('data-color') === currentCoverBackground) {
        option.classList.add('selected');
        break;
      }
    }
    
    // Update color input
    document.getElementById('custom-color-input').value = currentCoverBackground;
  }
}

/**
 * Select a solid color from the palette
 * @param {Event} event - The click event
 */
function selectColorOption(event) {
  // Remove selection from all options
  document.querySelectorAll('.color-option.selected, .gradient-option.selected').forEach(item => {
    item.classList.remove('selected');
  });
  
  // Add selection to clicked option
  event.target.classList.add('selected');
  
  // Update custom color input to match selected color
  document.getElementById('custom-color-input').value = event.target.getAttribute('data-color');
}

/**
 * Select a gradient option from the palette
 * @param {Event} event - The click event
 */
function selectGradientOption(event) {
  // Remove selection from all options
  document.querySelectorAll('.color-option.selected, .gradient-option.selected').forEach(item => {
    item.classList.remove('selected');
  });
  
  // Add selection to clicked option
  event.target.classList.add('selected');
}

/**
 * Update the custom color preview
 * @param {Event} event - The change event
 */
function updateCustomColorPreview(event) {
  // Remove selection from all predefined options
  document.querySelectorAll('.color-option.selected, .gradient-option.selected').forEach(item => {
    item.classList.remove('selected');
  });
  
  // The input value is the custom color
  const customColor = event.target.value;
  
  // Find or create a custom color option
  let customOption = document.querySelector('.color-option[data-custom="true"]');
  if (!customOption) {
    customOption = document.createElement('div');
    customOption.classList.add('color-option');
    customOption.setAttribute('data-custom', 'true');
    document.querySelector('.color-palette').appendChild(customOption);
    
    customOption.addEventListener('click', selectColorOption);
  }
  
  // Update the custom option
  customOption.style.backgroundColor = customColor;
  customOption.setAttribute('data-color', customColor);
  customOption.classList.add('selected');
}

/**
 * Apply the selected color or gradient to the cover photo
 */
function applySelectedColor() {
  // Check if a gradient is selected
  const selectedGradient = document.querySelector('.gradient-option.selected');
  if (selectedGradient) {
    currentCoverBackground = selectedGradient.getAttribute('data-gradient');
  } else {
    // Check if a color is selected
    const selectedColor = document.querySelector('.color-option.selected');
    if (selectedColor) {
      currentCoverBackground = selectedColor.getAttribute('data-color');
    } else {
      // Use the custom color input
      currentCoverBackground = document.getElementById('custom-color-input').value;
    }
  }
  
  // Apply the background to the cover photo
  document.getElementById('cover-photo').style.background = currentCoverBackground;
  
  // Update profile data
  profileData.personal.coverBackground = currentCoverBackground;
  saveProfileData();
  
  closeAllModals();
  showToast('Cover color updated successfully');
}

/**
 * Toggle password visibility
 * @param {Event} event - The click event
 */
function togglePassword(event) {
  const button = event.target.closest('.toggle-password');
  const targetId = button.getAttribute('data-target');
  const passwordInput = document.getElementById(targetId);
  
  if (passwordInput.type === 'password') {
    passwordInput.type = 'text';
    button.innerHTML = '<i class="fas fa-eye-slash"></i>';
  } else {
    passwordInput.type = 'password';
    button.innerHTML = '<i class="fas fa-eye"></i>';
  }
}

/**
 * Open the settings modal
 */
function openSettingsModal() {
  const modal = document.getElementById('settings-modal');
  modal.style.display = 'block';
  
  // Populate form with current data
  document.getElementById('settings-email').value = profileData.settings.email;
  document.getElementById('settings-password').value = ''; // Don't show the password for security
  document.getElementById('settings-new-password').value = '';
  document.getElementById('settings-confirm-password').value = '';
  document.getElementById('settings-birthday').value = profileData.settings.birthday;
  document.getElementById('settings-phone').value = profileData.settings.phone || '';
  
  // Set notification checkboxes
  document.getElementById('notify-messages').checked = profileData.settings.notifications.messages;
  document.getElementById('notify-network').checked = profileData.settings.notifications.network;
  document.getElementById('notify-jobs').checked = profileData.settings.notifications.jobs;
  
  // Initialize password strength meter for new password
  const newPasswordInput = document.getElementById('settings-new-password');
  if (newPasswordInput) {
    newPasswordInput.addEventListener('input', function() {
      isPasswordStrong(this.value);
    });
  }
  
  // Make sure the save button works
  document.getElementById('settings-save').onclick = function() {
    console.log('Settings save button clicked directly');
    
    // Get form values
    const email = document.getElementById('settings-email').value;
    const currentPassword = document.getElementById('settings-password').value;
    const newPassword = document.getElementById('settings-new-password').value;
    const confirmPassword = document.getElementById('settings-confirm-password').value;
    const birthday = document.getElementById('settings-birthday').value;
    const phone = document.getElementById('settings-phone').value;
    
    // Validate and save
    if (newPassword) {
      // Validate password change
      if (!currentPassword) {
        showToast('Please enter your current password', 'error');
        return;
      }
      
      if (currentPassword !== profileData.settings.password) {
        showToast('Current password is incorrect', 'error');
        return;
      }
      
      if (newPassword !== confirmPassword) {
        showToast('New passwords do not match', 'error');
        return;
      }
      
      // Update password
      profileData.settings.password = newPassword;
    }
    
    // Update other settings
    profileData.settings.email = email;
    profileData.settings.birthday = birthday;
    profileData.settings.phone = phone;
    
    // Update notification settings
    profileData.settings.notifications.messages = document.getElementById('notify-messages').checked;
    profileData.settings.notifications.network = document.getElementById('notify-network').checked;
    profileData.settings.notifications.jobs = document.getElementById('notify-jobs').checked;
    
    // Save and close
    saveProfileData();
    closeAllModals();
    showToast('Settings updated successfully');
  };
}

/**
 * Open the edit profile modal
 */
function openEditProfileModal() {
  const modal = document.getElementById('edit-profile-modal');
  modal.style.display = 'block';
  
  // Populate form with current data
  document.getElementById('profile-edit-name').value = profileData.personal.name;
  document.getElementById('profile-edit-title').value = profileData.personal.title;
  document.getElementById('profile-edit-location').value = profileData.personal.location;
  document.getElementById('profile-edit-website').value = profileData.personal.website;
  document.getElementById('profile-edit-experience').value = profileData.personal.yearsExperience;
}

/**
 * Handle edit profile form submission
 * @param {Event} event - The submit event
 */
function handleEditProfileSubmit(event) {
  event.preventDefault();
  
  // Update profile data
  profileData.personal.name = document.getElementById('profile-edit-name').value;
  profileData.personal.title = document.getElementById('profile-edit-title').value;
  profileData.personal.location = document.getElementById('profile-edit-location').value;
  profileData.personal.website = document.getElementById('profile-edit-website').value;
  profileData.personal.yearsExperience = parseInt(document.getElementById('profile-edit-experience').value, 10) || 0;
  
  // Update UI
  document.getElementById('profile-name').textContent = profileData.personal.name;
  document.getElementById('profile-title').textContent = profileData.personal.title;
  document.getElementById('profile-location').textContent = profileData.personal.location;
  document.getElementById('website-link').textContent = profileData.personal.website;
  document.getElementById('website-link').href = profileData.personal.website.startsWith('http') ? 
    profileData.personal.website : `https://${profileData.personal.website}`;
  document.getElementById('years-experience').textContent = profileData.personal.yearsExperience;
  
  saveProfileData();
  closeAllModals();
  showToast('Profile updated successfully');
}

/**
 * Open edit section
 * @param {string} section - The section to edit
 */
function openEditSection(section) {
  // Hide content display and show edit form
  document.getElementById(`${section}-content`).classList.add('hidden');
  document.getElementById(`${section}-edit`).classList.remove('hidden');
  
  // Set current editing section
  currentEditingSection = section;
  
  // Load current content into the textarea
  if (section === 'about') {
    const textarea = document.getElementById('about-textarea');
    textarea.value = profileData.about;
    updateWordCount({ target: textarea });
  } else if (section === 'seeking') {
    // For the seeking section, we need to parse the current options and check the appropriate checkboxes
    let seekingContent = profileData.seeking;
    
    // First, clear all checkboxes
    document.querySelectorAll('.seeking-checkbox').forEach(checkbox => {
      checkbox.checked = false;
    });
    
    // Extract additional text (if any) to put in the textarea
    let additionalText = '';
    
    // Check if the content follows our "Looking for X, Y, Z." format
    if (seekingContent && seekingContent.startsWith('Looking for ')) {
      const lookingForText = seekingContent.substring('Looking for '.length);
      
      // Check if there's a period that splits the list from additional text
      const periodIndex = lookingForText.indexOf('.');
      if (periodIndex !== -1) {
        const listPart = lookingForText.substring(0, periodIndex);
        additionalText = lookingForText.substring(periodIndex + 1).trim();
        
        // Check boxes that match the options
        const options = listPart.split(', ').map(option => option.trim());
        options.forEach(option => {
          const matchingCheckbox = Array.from(document.querySelectorAll('.seeking-checkbox'))
            .find(checkbox => checkbox.parentElement.querySelector('label').textContent === option);
          if (matchingCheckbox) {
            matchingCheckbox.checked = true;
          }
        });
      }
    } else {
      // If it doesn't match our format, just put everything in the textarea
      additionalText = seekingContent;
    }
    
    document.getElementById('seeking-textarea').value = additionalText;
  }
}

/**
 * Cancel editing a section
 * @param {string} section - The section being edited
 */
function cancelEditSection(section) {
  // Hide edit form and show content display
  document.getElementById(`${section}-content`).classList.remove('hidden');
  document.getElementById(`${section}-edit`).classList.add('hidden');
  
  // Reset current editing section
  currentEditingSection = null;
}

/**
 * Save edited section
 * @param {string} section - The section being saved
 */
function saveSection(section) {
  if (section === 'about') {
    const aboutText = document.getElementById('about-textarea').value.trim();
    
    // Validate word count
    const wordCount = countWords(aboutText);
    if (wordCount < 10 || wordCount > 255) {
      showToast('About section must be between 10 and 255 words', 'error');
      return;
    }
    
    profileData.about = aboutText;
    document.getElementById('about-content').innerHTML = `<p>${aboutText}</p>`;
  } else if (section === 'seeking') {
    // Handle seeking section with checkboxes
    let selectedOptions = [];
    document.querySelectorAll('.seeking-checkbox:checked').forEach(checkbox => {
      selectedOptions.push(checkbox.parentElement.querySelector('label').textContent);
    });
    
    const additionalContent = document.getElementById('seeking-textarea').value.trim();
    let content = '';
    let contentHTML = ''; // Define contentHTML outside the if block
    
    if (selectedOptions.length > 0) {
      // Create a list with tick marks for each selected option
      contentHTML = `<p>Looking for:</p><ul class="seeking-list">`;
      selectedOptions.forEach(option => {
        contentHTML += `<li><i class="fas fa-check"></i> ${option}</li>`;
      });
      contentHTML += `</ul>`;
      
      // Add additional content if provided
      if (additionalContent) {
        contentHTML += `<p>${additionalContent}</p>`;
      }
      
      // Store the text version for data purposes
      content = `Looking for ${selectedOptions.join(', ')}.`;
      if (additionalContent) {
        content += ' ' + additionalContent;
      }
    } else if (additionalContent) {
      content = additionalContent;
      contentHTML = `<p>${content}</p>`;
    } else {
      showToast('Please select at least one option or enter some content', 'error');
      return;
    }
    
    profileData.seeking = content;
    document.getElementById('seeking-content').innerHTML = contentHTML;
  }
  
  // Hide edit form and show content display
  document.getElementById(`${section}-content`).classList.remove('hidden');
  document.getElementById(`${section}-edit`).classList.add('hidden');
  
  saveProfileData();
  showToast(`${section.charAt(0).toUpperCase() + section.slice(1)} section updated successfully`);
  
  // Reset current editing section
  currentEditingSection = null;
}

/**
 * Update word count for the about section
 * @param {Event} event - The input event
 */
function updateWordCount(event) {
  const text = event.target.value;
  const wordCount = countWords(text);
  document.getElementById('about-word-count').textContent = wordCount;
  
  // Highlight if outside the allowed range
  if (wordCount < 10 || wordCount > 255) {
    document.getElementById('about-word-count').style.color = 'var(--error-color)';
  } else {
    document.getElementById('about-word-count').style.color = 'var(--text-tertiary)';
  }
}

/**
 * Count words in a text
 * @param {string} text - The text to count words in
 * @returns {number} - The word count
 */
function countWords(text) {
  return text.trim().split(/\s+/).filter(word => word.length > 0).length;
}

/**
 * Open add/edit item form
 * @param {string} section - The section to add/edit an item
 * @param {string|null} itemId - The ID of the item to edit (null for new item)
 */
function openAddItemForm(section, itemId = null) {
  console.log(`openAddItemForm called for section: ${section}, itemId: ${itemId}`);
  
  // Show the form
  document.getElementById(`${section}-content`).classList.add('hidden');
  document.getElementById(`${section}-edit`).classList.remove('hidden');
  
  // Set current editing section
  currentEditingSection = section;
  
  // Reset the form
  document.getElementById(`${section}-form`).reset();
  
  // If editing an existing item, populate the form
  if (itemId) {
    console.log(`Editing existing item with ID: ${itemId} in section: ${section}`);
    const item = profileData[section].find(item => item.id === itemId);
    console.log(`Found item:`, item);
    
    if (item) {
      // Set item ID in the hidden input - handling special cases for experience, education, etc.
      const idFieldPrefix = section === 'experience' ? 'exp' : 
                           section === 'education' ? 'edu' : 
                           section === 'honors' ? 'honor' : 
                           section === 'courses' ? 'course' : 
                           section === 'portfolio' ? 'portfolio' :
                           section.slice(0, -1);
                           
      const idField = document.getElementById(`${idFieldPrefix}-id`);
      console.log(`Setting ID field: ${idFieldPrefix}-id to value: ${itemId}`);
      idField.value = itemId;
      
      // Populate form fields based on section type
      switch (section) {
        case 'experience':
          document.getElementById('exp-title').value = item.title;
          document.getElementById('exp-company').value = item.company;
          document.getElementById('exp-start-date').value = item.startDate;
          
          if (item.isCurrent) {
            document.getElementById('exp-current').checked = true;
            document.getElementById('exp-end-date').disabled = true;
          } else {
            document.getElementById('exp-current').checked = false;
            document.getElementById('exp-end-date').disabled = false;
            document.getElementById('exp-end-date').value = item.endDate;
          }
          
          document.getElementById('exp-location').value = item.location || '';
          document.getElementById('exp-description').value = item.description || '';
          break;
          
        case 'education':
          document.getElementById('edu-school').value = item.school;
          document.getElementById('edu-degree').value = item.degree;
          document.getElementById('edu-start-date').value = item.startDate;
          
          if (item.isCurrent) {
            document.getElementById('edu-current').checked = true;
            document.getElementById('edu-end-date').disabled = true;
          } else {
            document.getElementById('edu-current').checked = false;
            document.getElementById('edu-end-date').disabled = false;
            document.getElementById('edu-end-date').value = item.endDate;
          }
          
          document.getElementById('edu-description').value = item.description || '';
          break;
          
        case 'skills':
          document.getElementById('skill-name').value = item.name;
          document.getElementById('skill-level').value = item.level;
          break;
          
        case 'languages':
          document.getElementById('language-name').value = item.name;
          document.getElementById('language-proficiency').value = item.proficiency;
          break;
          
        case 'honors':
          document.getElementById('honor-title').value = item.title;
          document.getElementById('honor-issuer').value = item.issuer;
          document.getElementById('honor-date').value = item.date;
          document.getElementById('honor-description').value = item.description || '';
          break;
          
        case 'courses':
          document.getElementById('course-title').value = item.title;
          document.getElementById('course-provider').value = item.provider;
          document.getElementById('course-date').value = item.date;
          document.getElementById('course-credential').value = item.credential || '';
          document.getElementById('course-url').value = item.url || '';
          break;
          
        case 'portfolio':
          document.getElementById('portfolio-title').value = item.title;
          document.getElementById('portfolio-description').value = item.description;
          document.getElementById('portfolio-url').value = item.url || '';
          document.getElementById('portfolio-technologies').value = item.technologies || '';
          document.getElementById('portfolio-date').value = item.date || '';
          break;
      }
    }
  } else {
    // Clear the item ID for new items
    const idFieldPrefix = section === 'experience' ? 'exp' : 
                         section === 'education' ? 'edu' : 
                         section === 'honors' ? 'honor' : 
                         section === 'courses' ? 'course' : 
                         section === 'portfolio' ? 'portfolio' :
                         section.slice(0, -1);
                         
    document.getElementById(`${idFieldPrefix}-id`).value = '';
  }
}

/**
 * Handle the checkbox for "I currently work here"
 * @param {Event} event - The change event
 */
function handleCurrentCheckbox(event) {
  const endDateInput = document.getElementById('exp-end-date');
  endDateInput.disabled = event.target.checked;
  if (event.target.checked) {
    endDateInput.value = '';
  }
}

/**
 * Handle the checkbox for "I'm currently studying here"
 * @param {Event} event - The change event
 */
function handleEducationCurrentCheckbox(event) {
  const endDateInput = document.getElementById('edu-end-date');
  endDateInput.disabled = event.target.checked;
  if (event.target.checked) {
    endDateInput.value = '';
  }
}

/**
 * Handle experience form submission
 * @param {Event} event - The submit event
 */
function handleExperienceSubmit(event) {
  event.preventDefault();
  
  // Get form values
  const id = document.getElementById('exp-id').value || generateUniqueId();
  const title = document.getElementById('exp-title').value.trim();
  const company = document.getElementById('exp-company').value.trim();
  const startDate = document.getElementById('exp-start-date').value;
  const isCurrent = document.getElementById('exp-current').checked;
  const endDate = isCurrent ? 'present' : document.getElementById('exp-end-date').value;
  const location = document.getElementById('exp-location').value.trim();
  const description = document.getElementById('exp-description').value.trim();
  
  // Validate required fields
  if (!title || !company || !startDate || (!isCurrent && !endDate)) {
    showToast('Please fill in all required fields', 'error');
    return;
  }
  
  // Create/update experience item
  const experienceItem = {
    id,
    title,
    company,
    startDate,
    endDate,
    isCurrent,
    location,
    description
  };
  
  // Check if editing or adding
  const existingIndex = profileData.experience.findIndex(item => item.id === id);
  if (existingIndex >= 0) {
    // Update existing item
    profileData.experience[existingIndex] = experienceItem;
  } else {
    // Add new item
    profileData.experience.push(experienceItem);
  }
  
  // Sort experience items by date (newest first)
  profileData.experience.sort((a, b) => {
    const dateA = a.isCurrent ? new Date() : new Date(a.endDate);
    const dateB = b.isCurrent ? new Date() : new Date(b.endDate);
    return dateB - dateA;
  });
  
  // Update top skills if needed
  updateTopSkillsFromExperience();
  
  saveProfileData();
  renderExperienceItems();
  cancelEditSection('experience');
  showToast('Experience updated successfully');
}

/**
 * Extract top skills from experience descriptions
 */
function updateTopSkillsFromExperience() {
  // Only update if user has less than 3 skills defined
  if (profileData.personal.topSkills.length >= 3) return;
  
  // Common programming skills to look for
  const skillsToLookFor = [
    'JavaScript', 'Python', 'Java', 'C++', 'C#', 'PHP', 'Ruby', 'Swift',
    'React', 'Angular', 'Vue', 'Node.js', 'Express', 'Django', 'Spring',
    'AWS', 'Azure', 'Docker', 'Kubernetes', 'Machine Learning', 'AI'
  ];
  
  const foundSkills = new Set();
  
  // Look for skills in experience descriptions
  profileData.experience.forEach(exp => {
    const description = exp.description.toLowerCase();
    skillsToLookFor.forEach(skill => {
      if (description.includes(skill.toLowerCase())) {
        foundSkills.add(skill);
      }
    });
  });
  
  // Update top skills if needed
  const skillsArray = Array.from(foundSkills);
  if (skillsArray.length > 0) {
    const currentSkills = profileData.personal.topSkills;
    const newSkills = skillsArray.slice(0, 3 - currentSkills.length);
    profileData.personal.topSkills = [...currentSkills, ...newSkills].slice(0, 3);
    
    // Update top skills in UI
    const topSkillsList = document.getElementById('top-skills-list');
    topSkillsList.innerHTML = '';
    profileData.personal.topSkills.forEach(skill => {
      const li = document.createElement('li');
      li.textContent = skill;
      topSkillsList.appendChild(li);
    });
  }
}

/**
 * Render experience items
 */
function renderExperienceItems() {
  const container = document.getElementById('experience-items');
  container.innerHTML = '';
  
  if (profileData.experience.length === 0) {
    container.innerHTML = '<p class="empty-state">No experience added yet. Click the + button to add your work experience.</p>';
    return;
  }
  
  profileData.experience.forEach(item => {
    const itemElement = document.createElement('div');
    itemElement.className = 'item-card';
    
    // Format dates
    const startDate = formatDate(item.startDate);
    const endDate = item.isCurrent ? 'Present' : formatDate(item.endDate);
    
    itemElement.innerHTML = `
      <div class="item-actions">
        <button class="edit-item-btn" data-id="${item.id}" data-section="experience">
          <i class="fas fa-edit"></i>
        </button>
        <button class="delete-item-btn" data-id="${item.id}" data-section="experience">
          <i class="fas fa-trash-alt"></i>
        </button>
      </div>
      <div class="item-header">
        <h3 class="item-title">${item.title}</h3>
        <div class="item-subtitle">${item.company}</div>
        <div class="item-date">${startDate} - ${endDate}</div>
        ${item.location ? `<div class="item-location"><i class="fas fa-map-marker-alt"></i> ${item.location}</div>` : ''}
      </div>
      <div class="item-description">
        ${item.description || ''}
      </div>
    `;
    
    container.appendChild(itemElement);
  });
  
  // Add event listeners to the edit and delete buttons
  addItemActionEventListeners(container);
  
  // Show the content
  document.getElementById('experience-content').classList.remove('hidden');
  document.getElementById('experience-edit').classList.add('hidden');
}

/**
 * Handle education form submission
 * @param {Event} event - The submit event
 */
function handleEducationSubmit(event) {
  event.preventDefault();
  
  // Get form values
  const id = document.getElementById('edu-id').value || generateUniqueId();
  const school = document.getElementById('edu-school').value.trim();
  const degree = document.getElementById('edu-degree').value.trim();
  const startDate = document.getElementById('edu-start-date').value;
  const isCurrent = document.getElementById('edu-current').checked;
  const endDate = isCurrent ? 'present' : document.getElementById('edu-end-date').value;
  const description = document.getElementById('edu-description').value.trim();
  
  // Validate required fields
  if (!school || !degree || !startDate || (!isCurrent && !endDate)) {
    showToast('Please fill in all required fields', 'error');
    return;
  }
  
  // Create/update education item
  const educationItem = {
    id,
    school,
    degree,
    startDate,
    endDate,
    isCurrent,
    description
  };
  
  // Check if editing or adding
  const existingIndex = profileData.education.findIndex(item => item.id === id);
  if (existingIndex >= 0) {
    // Update existing item
    profileData.education[existingIndex] = educationItem;
  } else {
    // Add new item
    profileData.education.push(educationItem);
  }
  
  // Sort education items by date (newest first)
  profileData.education.sort((a, b) => {
    const dateA = a.isCurrent ? new Date() : new Date(a.endDate);
    const dateB = b.isCurrent ? new Date() : new Date(b.endDate);
    return dateB - dateA;
  });
  
  saveProfileData();
  renderEducationItems();
  cancelEditSection('education');
  showToast('Education updated successfully');
}

/**
 * Render education items
 */
function renderEducationItems() {
  const container = document.getElementById('education-items');
  container.innerHTML = '';
  
  if (profileData.education.length === 0) {
    container.innerHTML = '<p class="empty-state">No education added yet. Click the + button to add your education.</p>';
    return;
  }
  
  profileData.education.forEach(item => {
    const itemElement = document.createElement('div');
    itemElement.className = 'item-card';
    
    // Format dates
    const startDate = formatDate(item.startDate);
    const endDate = item.isCurrent ? 'Present' : formatDate(item.endDate);
    
    itemElement.innerHTML = `
      <div class="item-actions">
        <button class="edit-item-btn" data-id="${item.id}" data-section="education">
          <i class="fas fa-edit"></i>
        </button>
        <button class="delete-item-btn" data-id="${item.id}" data-section="education">
          <i class="fas fa-trash-alt"></i>
        </button>
      </div>
      <div class="item-header">
        <h3 class="item-title">${item.degree}</h3>
        <div class="item-subtitle">${item.school}</div>
        <div class="item-date">${startDate} - ${endDate}</div>
      </div>
      <div class="item-description">
        ${item.description || ''}
      </div>
    `;
    
    container.appendChild(itemElement);
  });
  
  // Add event listeners to the edit and delete buttons
  addItemActionEventListeners(container);
  
  // Show the content
  document.getElementById('education-content').classList.remove('hidden');
  document.getElementById('education-edit').classList.add('hidden');
}

/**
 * Handle skill form submission
 * @param {Event} event - The submit event
 */
function handleSkillSubmit(event) {
  event.preventDefault();
  
  // Get form values
  const id = document.getElementById('skill-id').value || generateUniqueId();
  const name = document.getElementById('skill-name').value.trim();
  const level = document.getElementById('skill-level').value;
  
  console.log('Skill form submitted', {
    id: id,
    name: name,
    level: level,
    isNewSkill: !document.getElementById('skill-id').value
  });
  
  // Validate required fields
  if (!name || !level) {
    showToast('Please fill in all required fields', 'error');
    return;
  }
  
  // Create/update skill item
  const skillItem = {
    id,
    name,
    level
  };
  
  // Check if editing or adding
  const existingIndex = profileData.skills.findIndex(item => item.id === id);
  if (existingIndex >= 0) {
    // Update existing item
    profileData.skills[existingIndex] = skillItem;
  } else {
    // Add new item
    profileData.skills.push(skillItem);
  }
  
  // Update top skills if Advanced or Expert
  if ((level === 'Advanced' || level === 'Expert') && 
      !profileData.personal.topSkills.includes(name) && 
      profileData.personal.topSkills.length < 3) {
    profileData.personal.topSkills.push(name);
    
    // Update top skills in UI
    const topSkillsList = document.getElementById('top-skills-list');
    if (topSkillsList) {
      topSkillsList.innerHTML = '';
      profileData.personal.topSkills.forEach(skill => {
        const li = document.createElement('li');
        li.textContent = skill;
        topSkillsList.appendChild(li);
      });
    }
  }
  
  saveProfileData();
  renderSkillItems();
  cancelEditSection('skills');
  showToast('Skill updated successfully');
}

/**
 * Render skill items
 */
function renderSkillItems() {
  const container = document.getElementById('skills-container');
  container.innerHTML = '';
  
  if (profileData.skills.length === 0) {
    container.innerHTML = '<p class="empty-state">No skills added yet. Click the + button to add your skills.</p>';
    return;
  }
  
  // Sort skills by level (Expert, Advanced, Intermediate, Beginner)
  const levelOrder = { 'Expert': 0, 'Advanced': 1, 'Intermediate': 2, 'Beginner': 3 };
  profileData.skills.sort((a, b) => levelOrder[a.level] - levelOrder[b.level]);
  
  profileData.skills.forEach(item => {
    const skillElement = document.createElement('div');
    skillElement.className = 'skill-badge';
    
    skillElement.innerHTML = `
      <span>${item.name}</span>
      <span class="skill-level">${item.level}</span>
      <div class="badge-actions">
        <button class="edit-badge-btn" data-id="${item.id}" data-section="skills">
          <i class="fas fa-edit"></i>
        </button>
        <button class="delete-badge-btn" data-id="${item.id}" data-section="skills">
          <i class="fas fa-times"></i>
        </button>
      </div>
    `;
    
    container.appendChild(skillElement);
  });
  
  // Add event listeners to the edit and delete buttons
  addBadgeActionEventListeners(container, 'skills');
  
  // Show the content
  document.getElementById('skills-content').classList.remove('hidden');
  document.getElementById('skills-edit').classList.add('hidden');
}

/**
 * Handle language form submission
 * @param {Event} event - The submit event
 */
function handleLanguageSubmit(event) {
  event.preventDefault();
  
  // Get form values
  const id = document.getElementById('language-id').value || generateUniqueId();
  const name = document.getElementById('language-name').value.trim();
  const proficiency = document.getElementById('language-proficiency').value;
  
  console.log('Language form submitted', {
    id: id,
    name: name,
    proficiency: proficiency,
    isNewLanguage: !document.getElementById('language-id').value
  });
  
  // Validate required fields
  if (!name || !proficiency) {
    showToast('Please fill in all required fields', 'error');
    return;
  }
  
  // Create/update language item
  const languageItem = {
    id,
    name,
    proficiency
  };
  
  // Check if editing or adding
  const existingIndex = profileData.languages.findIndex(item => item.id === id);
  if (existingIndex >= 0) {
    // Update existing item
    profileData.languages[existingIndex] = languageItem;
  } else {
    // Add new item
    profileData.languages.push(languageItem);
  }
  
  // Update personal languages if Native or Fluent
  if ((proficiency === 'Native' || proficiency === 'Fluent') && 
      !profileData.personal.languages.includes(`${name} (${proficiency})`) && 
      profileData.personal.languages.length < 3) {
    profileData.personal.languages.push(`${name} (${proficiency})`);
    
    // Update languages in UI
    const languagesList = document.getElementById('languages-list');
    languagesList.innerHTML = '';
    profileData.personal.languages.forEach(language => {
      const li = document.createElement('li');
      li.textContent = language;
      languagesList.appendChild(li);
    });
  }
  
  saveProfileData();
  renderLanguageItems();
  cancelEditSection('languages');
  showToast('Language updated successfully');
}

/**
 * Render language items
 */
function renderLanguageItems() {
  const container = document.getElementById('languages-container');
  container.innerHTML = '';
  
  if (profileData.languages.length === 0) {
    container.innerHTML = '<p class="empty-state">No languages added yet. Click the + button to add your languages.</p>';
    return;
  }
  
  // Sort languages by proficiency (Native, Fluent, Professional, Intermediate, Basic)
  const proficiencyOrder = { 'Native': 0, 'Fluent': 1, 'Professional': 2, 'Intermediate': 3, 'Basic': 4 };
  profileData.languages.sort((a, b) => proficiencyOrder[a.proficiency] - proficiencyOrder[b.proficiency]);
  
  profileData.languages.forEach(item => {
    const languageElement = document.createElement('div');
    languageElement.className = 'language-badge';
    
    languageElement.innerHTML = `
      <span>${item.name}</span>
      <span class="language-proficiency">${item.proficiency}</span>
      <div class="badge-actions">
        <button class="edit-badge-btn" data-id="${item.id}" data-section="languages">
          <i class="fas fa-edit"></i>
        </button>
        <button class="delete-badge-btn" data-id="${item.id}" data-section="languages">
          <i class="fas fa-times"></i>
        </button>
      </div>
    `;
    
    container.appendChild(languageElement);
  });
  
  // Add event listeners to the edit and delete buttons
  addBadgeActionEventListeners(container, 'languages');
  
  // Show the content
  document.getElementById('languages-content').classList.remove('hidden');
  document.getElementById('languages-edit').classList.add('hidden');
}

/**
 * Handle honor form submission
 * @param {Event} event - The submit event
 */
function handleHonorSubmit(event) {
  event.preventDefault();
  console.log('Honor form submitted');
  
  // Get form values
  const id = document.getElementById('honor-id').value || generateUniqueId();
  const title = document.getElementById('honor-title').value.trim();
  const issuer = document.getElementById('honor-issuer').value.trim();
  const date = document.getElementById('honor-date').value;
  const description = document.getElementById('honor-description').value.trim();
  
  // Validate required fields
  if (!title || !issuer || !date) {
    showToast('Please fill in all required fields', 'error');
    return;
  }
  
  // Create/update honor item
  const honorItem = {
    id,
    title,
    issuer,
    date,
    description
  };
  
  // Check if editing or adding
  const existingIndex = profileData.honors.findIndex(item => item.id === id);
  if (existingIndex >= 0) {
    // Update existing item
    profileData.honors[existingIndex] = honorItem;
  } else {
    // Add new item
    profileData.honors.push(honorItem);
  }
  
  saveProfileData();
  renderHonorItems();
  cancelEditSection('honors');
  showToast('Honor updated successfully');
}

/**
 * Render honor items
 */
function renderHonorItems() {
  const container = document.getElementById('honors-items');
  container.innerHTML = '';
  
  if (profileData.honors.length === 0) {
    container.innerHTML = '<p class="empty-state">No honors or awards added yet. Click the + button to add your achievements.</p>';
    return;
  }
  
  profileData.honors.forEach(item => {
    const itemElement = document.createElement('div');
    itemElement.className = 'item-card';
    
    // Format date
    const date = formatDate(item.date);
    
    itemElement.innerHTML = `
      <div class="item-actions">
        <button class="edit-item-btn" data-id="${item.id}" data-section="honors">
          <i class="fas fa-edit"></i>
        </button>
        <button class="delete-item-btn" data-id="${item.id}" data-section="honors">
          <i class="fas fa-trash-alt"></i>
        </button>
      </div>
      <div class="item-header">
        <h3 class="item-title">${item.title}</h3>
        <div class="item-subtitle">${item.issuer}</div>
        <div class="item-date">${date}</div>
      </div>
      <div class="item-description">
        ${item.description || ''}
      </div>
    `;
    
    container.appendChild(itemElement);
  });
  
  // Add event listeners to the edit and delete buttons
  addItemActionEventListeners(container);
  
  // Show the content
  document.getElementById('honors-content').classList.remove('hidden');
  document.getElementById('honors-edit').classList.add('hidden');
}

/**
 * Handle course form submission
 * @param {Event} event - The submit event
 */
function handleCourseSubmit(event) {
  event.preventDefault();
  
  // Get form values
  const id = document.getElementById('course-id').value || generateUniqueId();
  const title = document.getElementById('course-title').value.trim();
  const provider = document.getElementById('course-provider').value.trim();
  const date = document.getElementById('course-date').value;
  const credential = document.getElementById('course-credential').value.trim();
  const url = document.getElementById('course-url').value.trim();
  
  // Validate required fields
  if (!title || !provider || !date) {
    showToast('Please fill in all required fields', 'error');
    return;
  }
  
  // Create/update course item
  const courseItem = {
    id,
    title,
    provider,
    date,
    credential,
    url
  };
  
  // Check if editing or adding
  const existingIndex = profileData.courses.findIndex(item => item.id === id);
  if (existingIndex >= 0) {
    // Update existing item
    profileData.courses[existingIndex] = courseItem;
  } else {
    // Add new item
    profileData.courses.push(courseItem);
  }
  
  // Sort courses by date (newest first)
  profileData.courses.sort((a, b) => new Date(b.date) - new Date(a.date));
  
  saveProfileData();
  renderCourseItems();
  cancelEditSection('courses');
  showToast('Course updated successfully');
}

/**
 * Render course items
 */
function renderCourseItems() {
  const container = document.getElementById('courses-items');
  container.innerHTML = '';
  
  if (profileData.courses.length === 0) {
    container.innerHTML = '<p class="empty-state">No courses or certifications added yet. Click the + button to add your credentials.</p>';
    return;
  }
  
  profileData.courses.forEach(item => {
    const itemElement = document.createElement('div');
    itemElement.className = 'item-card';
    
    // Format date
    const date = formatDate(item.date);
    
    itemElement.innerHTML = `
      <div class="item-actions">
        <button class="edit-item-btn" data-id="${item.id}" data-section="courses">
          <i class="fas fa-edit"></i>
        </button>
        <button class="delete-item-btn" data-id="${item.id}" data-section="courses">
          <i class="fas fa-trash-alt"></i>
        </button>
      </div>
      <div class="item-header">
        <h3 class="item-title">${item.title}</h3>
        <div class="item-subtitle">${item.provider}</div>
        <div class="item-date">${date}</div>
        ${item.credential ? `<div class="item-credential">Credential ID: ${item.credential}</div>` : ''}
        ${item.url ? `<div class="item-url"><a href="${item.url}" target="_blank">See credential</a></div>` : ''}
      </div>
    `;
    
    container.appendChild(itemElement);
  });
  
  // Add event listeners to the edit and delete buttons
  addItemActionEventListeners(container);
  
  // Show the content
  document.getElementById('courses-content').classList.remove('hidden');
  document.getElementById('courses-edit').classList.add('hidden');
}

/**
 * Handle portfolio form submission
 * @param {Event} event - The submit event
 */
function handlePortfolioSubmit(event) {
  event.preventDefault();
  
  // Get form values
  const id = document.getElementById('portfolio-id').value || generateUniqueId();
  const title = document.getElementById('portfolio-title').value.trim();
  const description = document.getElementById('portfolio-description').value.trim();
  const url = document.getElementById('portfolio-url').value.trim();
  const technologies = document.getElementById('portfolio-technologies').value.trim();
  const date = document.getElementById('portfolio-date').value;
  
  // Validate required fields
  if (!title || !description) {
    showToast('Please fill in all required fields', 'error');
    return;
  }
  
  // Create/update portfolio item
  const portfolioItem = {
    id,
    title,
    description,
    url,
    technologies,
    date
  };
  
  // Check if editing or adding
  const existingIndex = profileData.portfolio.findIndex(item => item.id === id);
  if (existingIndex >= 0) {
    // Update existing item
    profileData.portfolio[existingIndex] = portfolioItem;
  } else {
    // Add new item
    profileData.portfolio.push(portfolioItem);
  }
  
  // Sort portfolio items by date (newest first) if date is provided
  if (date) {
    profileData.portfolio.sort((a, b) => {
      if (!a.date) return 1;
      if (!b.date) return -1;
      return new Date(b.date) - new Date(a.date);
    });
  }
  
  saveProfileData();
  renderPortfolioItems();
  cancelEditSection('portfolio');
  showToast('Portfolio project updated successfully');
}

/**
 * Render portfolio items
 */
function renderPortfolioItems() {
  const container = document.getElementById('portfolio-items');
  container.innerHTML = '';
  
  if (profileData.portfolio.length === 0) {
    container.innerHTML = '<p class="empty-state">No portfolio projects added yet. Click the + button to showcase your work.</p>';
    return;
  }
  
  profileData.portfolio.forEach(item => {
    const itemElement = document.createElement('div');
    itemElement.className = 'item-card';
    
    // Format date if available
    const dateDisplay = item.date ? `<div class="item-date">${formatDate(item.date)}</div>` : '';
    
    itemElement.innerHTML = `
      <div class="item-actions">
        <button class="edit-item-btn" data-id="${item.id}" data-section="portfolio">
          <i class="fas fa-edit"></i>
        </button>
        <button class="delete-item-btn" data-id="${item.id}" data-section="portfolio">
          <i class="fas fa-trash-alt"></i>
        </button>
      </div>
      <div class="item-header">
        <h3 class="item-title">${item.title}</h3>
        ${item.technologies ? `<div class="item-technologies">${item.technologies}</div>` : ''}
        ${dateDisplay}
        ${item.url ? `<div class="item-url"><a href="${item.url}" target="_blank">View project</a></div>` : ''}
      </div>
      <div class="item-description">
        ${item.description}
      </div>
    `;
    
    container.appendChild(itemElement);
  });
  
  // Add event listeners to the edit and delete buttons
  addItemActionEventListeners(container);
  
  // Show the content
  document.getElementById('portfolio-content').classList.remove('hidden');
  document.getElementById('portfolio-edit').classList.add('hidden');
}

/**
 * Add event listeners to item action buttons (edit, delete)
 * @param {HTMLElement} container - The container with the action buttons
 */
function addItemActionEventListeners(container) {
  // Edit buttons
  container.querySelectorAll('.edit-item-btn').forEach(btn => {
    btn.addEventListener('click', (event) => {
      const id = event.currentTarget.getAttribute('data-id');
      const section = event.currentTarget.getAttribute('data-section');
      
      // For debugging
      console.log(`Edit button clicked for ${section} item with ID: ${id}`);
      
      // Remove any existing click handlers to avoid duplicates
      btn.removeEventListener('click', () => {});
      
      // Open the edit form for the specific item
      openAddItemForm(section, id);
    });
  });
  
  // Delete buttons
  container.querySelectorAll('.delete-item-btn').forEach(btn => {
    btn.addEventListener('click', (event) => {
      const id = event.currentTarget.getAttribute('data-id');
      const section = event.currentTarget.getAttribute('data-section');
      
      // For debugging
      console.log(`Delete button clicked for ${section} item with ID: ${id}`);
      
      // Remove any existing click handlers to avoid duplicates
      btn.removeEventListener('click', () => {});
      
      // Open the delete confirmation dialog
      openDeleteConfirmation(id, section);
    });
  });
}

/**
 * Add event listeners to badge action buttons (edit, delete)
 * @param {HTMLElement} container - The container with the action buttons
 * @param {string} section - The section (skills or languages)
 */
function addBadgeActionEventListeners(container, section) {
  // Edit buttons
  container.querySelectorAll('.edit-badge-btn').forEach(btn => {
    btn.addEventListener('click', (event) => {
      const id = event.currentTarget.getAttribute('data-id');
      
      // For debugging
      console.log(`Edit badge button clicked for ${section} item with ID: ${id}`);
      
      // Remove any existing click handlers to avoid duplicates
      btn.removeEventListener('click', () => {});
      
      // Open the edit form for the specific item
      openAddItemForm(section, id);
    });
  });
  
  // Delete buttons
  container.querySelectorAll('.delete-badge-btn').forEach(btn => {
    btn.addEventListener('click', (event) => {
      const id = event.currentTarget.getAttribute('data-id');
      
      // For debugging
      console.log(`Delete badge button clicked for ${section} item with ID: ${id}`);
      
      // Remove any existing click handlers to avoid duplicates
      btn.removeEventListener('click', () => {});
      
      // Open the delete confirmation dialog
      openDeleteConfirmation(id, section);
    });
  });
}

/**
 * Open delete confirmation modal
 * @param {string} id - The ID of the item to delete
 * @param {string} section - The section the item belongs to
 */
function openDeleteConfirmation(id, section) {
  currentDeleteItem = { id, section };
  
  // Show the delete confirmation modal
  document.getElementById('delete-confirm-modal').style.display = 'block';
}

/**
 * Confirm and execute delete operation
 */
function confirmDelete() {
  if (!currentDeleteItem) return;
  
  const { id, section } = currentDeleteItem;
  
  // Find the item in the array
  const itemIndex = profileData[section].findIndex(item => item.id === id);
  
  if (itemIndex >= 0) {
    // If it's a skill or language that's in the top lists, remove it from there too
    const item = profileData[section][itemIndex];
    
    if (section === 'skills') {
      const topSkillIndex = profileData.personal.topSkills.indexOf(item.name);
      if (topSkillIndex >= 0) {
        profileData.personal.topSkills.splice(topSkillIndex, 1);
        
        // Update top skills in UI
        const topSkillsList = document.getElementById('top-skills-list');
        if (topSkillsList) {
          topSkillsList.innerHTML = '';
          profileData.personal.topSkills.forEach(skill => {
            const li = document.createElement('li');
            li.textContent = skill;
            topSkillsList.appendChild(li);
          });
        }
      }
    }
    
    if (section === 'languages') {
      const languageWithProficiency = `${item.name} (${item.proficiency})`;
      const topLangIndex = profileData.personal.languages.indexOf(languageWithProficiency);
      if (topLangIndex >= 0) {
        profileData.personal.languages.splice(topLangIndex, 1);
        
        // Update languages in UI
        const languagesList = document.getElementById('languages-list');
        languagesList.innerHTML = '';
        profileData.personal.languages.forEach(language => {
          const li = document.createElement('li');
          li.textContent = language;
          languagesList.appendChild(li);
        });
      }
    }
    
    // Remove the item from the array
    profileData[section].splice(itemIndex, 1);
    
    // Save and re-render
    saveProfileData();
    
    // Re-render the specific section
    switch (section) {
      case 'experience':
        renderExperienceItems();
        break;
      case 'education':
        renderEducationItems();
        break;
      case 'skills':
        renderSkillItems();
        break;
      case 'languages':
        renderLanguageItems();
        break;
      case 'honors':
        renderHonorItems();
        break;
      case 'courses':
        renderCourseItems();
        break;
      case 'portfolio':
        renderPortfolioItems();
        break;
    }
    
    showToast('Item deleted successfully');
  }
  
  closeAllModals();
  currentDeleteItem = null;
}

/**
 * Close all modals
 */
function closeAllModals() {
  document.querySelectorAll('.modal').forEach(modal => {
    modal.style.display = 'none';
  });
}

/**
 * Format a date for display
 * @param {string} dateString - The date string in YYYY-MM format
 * @returns {string} - The formatted date
 */
function formatDate(dateString) {
  if (!dateString || dateString === 'present') return 'Present';
  
  try {
    const date = new Date(dateString);
    return new Intl.DateTimeFormat('en-US', { year: 'numeric', month: 'short' }).format(date);
  } catch (error) {
    return dateString;
  }
}

/**
 * Generate a unique ID
 * @returns {string} - A unique ID
 */
function generateUniqueId() {
  return `id_${Date.now()}_${Math.random().toString(36).substr(2, 9)}`;
}

/**
 * Check if a password is strong
 * @param {string} password - The password to check
 * @returns {boolean} - True if the password is strong
 */
function isPasswordStrong(password) {
  // At least 8 characters, including at least one number and one special character
  const regex = /^(?=.*[0-9])(?=.*[!@#$%^&*])[a-zA-Z0-9!@#$%^&*]{8,}$/;
  
  // Update password strength meter if it exists
  const strengthBar = document.querySelector('.strength-bar');
  const strengthText = document.querySelector('.strength-text');
  
  if (strengthBar && strengthText) {
    // Calculate strength score (0-100)
    let score = 0;
    
    // Length check
    if (password.length >= 8) score += 25;
    if (password.length >= 12) score += 15;
    
    // Character variety checks
    if (/[0-9]/.test(password)) score += 15;
    if (/[a-z]/.test(password)) score += 10;
    if (/[A-Z]/.test(password)) score += 10;
    if (/[^a-zA-Z0-9]/.test(password)) score += 25;
    
    // Update UI
    strengthBar.style.width = `${score}%`;
    
    if (score < 40) {
      strengthBar.style.backgroundColor = 'var(--error-color)';
      strengthText.textContent = 'Weak';
      strengthText.style.color = 'var(--error-color)';
    } else if (score < 70) {
      strengthBar.style.backgroundColor = 'var(--warning-color)';
      strengthText.textContent = 'Medium';
      strengthText.style.color = 'var(--warning-color)';
    } else {
      strengthBar.style.backgroundColor = 'var(--success-color)';
      strengthText.textContent = 'Strong';
      strengthText.style.color = 'var(--success-color)';
    }
  }
  
  return regex.test(password);
}

/**
 * Highlight a field with an error
 * @param {string} fieldId - The ID of the field to highlight
 */
function highlightField(fieldId) {
  const field = document.getElementById(fieldId);
  field.classList.add('error');
  
  // Remove the error class after 3 seconds
  setTimeout(() => {
    field.classList.remove('error');
  }, 3000);
}

/**
 * Show a toast notification
 * @param {string} message - The message to display
 * @param {string} type - The type of toast (success, error, info)
 */
function showToast(message, type = 'success') {
  console.log('Toast message:', message, 'Type:', type);
  
  // Remove any existing toasts first
  const existingToasts = document.querySelectorAll('.toast');
  existingToasts.forEach(toast => {
    document.body.removeChild(toast);
  });
  
  const toastContainer = document.getElementById('toast-container') || document.body;
  
  const toast = document.createElement('div');
  toast.className = `toast ${type}`;
  toast.innerHTML = `
    <div class="toast-icon">
      ${type === 'success' ? '<i class="fas fa-check-circle"></i>' : 
        type === 'error' ? '<i class="fas fa-exclamation-circle"></i>' : 
        '<i class="fas fa-info-circle"></i>'}
    </div>
    <div class="toast-message">${message}</div>
  `;
  
  toastContainer.appendChild(toast);
  
  // Show the toast
  setTimeout(() => {
    toast.classList.add('show');
  }, 100);
  
  // Hide and remove the toast after 3 seconds
  setTimeout(() => {
    toast.classList.remove('show');
    setTimeout(() => {
      if (toast.parentNode) {
        toast.parentNode.removeChild(toast);
      }
    }, 300);
  }, 3000);
}

/**
 * Open the setup profile modal
 */
function openSetupProfileModal() {
  const modal = document.getElementById('setup-profile-modal');
  if (!modal) return;
  
  modal.style.display = 'block';
  
  // Reset progress bar
  const progressBar = document.querySelector('.setup-progress-bar');
  if (progressBar) {
    progressBar.style.width = '16.67%';
  }
  
  // Activate the first tab
  switchSetupTab('basic');
  
  // Pre-fill the form with existing data if available
  if (profileData) {
    // Basic info
    document.getElementById('setup-name').value = profileData.personal.name || '';
    document.getElementById('setup-title').value = profileData.personal.title || '';
    document.getElementById('setup-location').value = profileData.personal.location || '';
    document.getElementById('setup-website').value = profileData.personal.website || '';
    document.getElementById('setup-experience').value = profileData.personal.yearsExperience || '';
    
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
            document.getElementById(inputId).checked = true;
          }
        });
        
        // Set additional text
        document.getElementById('setup-seeking-additional').value = additionalText;
      }
    }
  }
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
  const progressPercent = (tabIndex + 1) * (100 / 6); // 6 tabs
  document.querySelector('.setup-progress-bar').style.width = `${progressPercent}%`;
  
  // Update navigation buttons
  document.getElementById('prev-tab-btn').disabled = (tabIndex === 0);
  document.getElementById('next-tab-btn').textContent = (tabIndex === 5) ? 'Complete Setup' : 'Next';
}

/**
 * Get the index of a tab
 * @param {string} tabId - The ID of the tab
 * @returns {number} - The index of the tab
 */
function getTabIndex(tabId) {
  const tabs = ['basic', 'about', 'experience', 'education', 'skills', 'seeking'];
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
    const prevTabId = ['basic', 'about', 'experience', 'education', 'skills', 'seeking'][currentIndex - 1];
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
  
  if (currentIndex < 5) {
    const nextTabId = ['basic', 'about', 'experience', 'education', 'skills', 'seeking'][currentIndex + 1];
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
  
  // Validate required fields
  if (!name || !title || !location) {
    showToast('Please fill in all required fields in the Basic Info section', 'error');
    switchSetupTab('basic');
    return;
  }
  
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
  
  profileData.about = about;
  profileData.experience = experienceItems;
  profileData.education = educationItems;
  profileData.skills = skillItems;
  profileData.seeking = seekingContent;
  
  // Extract top skills from experience
  if (experienceItems.length > 0) {
    updateTopSkillsFromExperience();
  } else if (skillItems.length > 0) {
    // Use top skills from the skills list
    profileData.personal.topSkills = skillItems.slice(0, 3).map(skill => skill.name);
  }
  
  // Save data and render
  saveProfileData();
  renderProfileData();
  
  // Close modal and show success message
  closeAllModals();
  showToast('Profile setup completed successfully!');
}
