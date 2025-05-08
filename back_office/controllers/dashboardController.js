/**
 * Dashboard Controller
 * Handles theme switching and dashboard functionality
 */

// DOM Elements
const themeToggle = document.getElementById('theme-toggle');
const body = document.body;

// Initialize the dashboard model
const dashboardModel = new DashboardModel();

// Initialize theme based on user preference or default to light
function initTheme() {
    const savedTheme = localStorage.getItem('theme') || 'light';
    document.body.className = savedTheme + '-theme';
    
    // Set the toggle switch based on the theme
    const themeToggle = document.getElementById('theme-toggle');
    themeToggle.checked = savedTheme === 'dark';
    
    // Update chart colors based on theme
    updateChartColors(savedTheme);
}

// Toggle between light and dark themes
function toggleTheme() {
    const currentTheme = document.body.className.includes('light') ? 'light' : 'dark';
    const newTheme = currentTheme === 'light' ? 'dark' : 'light';
    
    document.body.className = newTheme + '-theme';
    localStorage.setItem('theme', newTheme);
    
    // Update chart colors based on new theme
    updateChartColors(newTheme);
}

// Update chart colors based on the current theme
function updateChartColors(theme) {
    // Get all SVG elements
    const svgElements = document.querySelectorAll('.svg-chart');
    // Apply theme-specific styles to SVGs if needed
}

// Initialize views to handle navigation between dashboard and users screens
function initViews() {
    const navItems = document.querySelectorAll('.sidebar-nav li');
    
    navItems.forEach(item => {
        item.addEventListener('click', function() {
            // Remove active class from all nav items
            navItems.forEach(nav => nav.classList.remove('active'));
            
            // Add active class to clicked item
            this.classList.add('active');
            
            // Get the view name from the nav item text
            const viewName = this.querySelector('span').textContent.toLowerCase();
            
            // Navigate to the appropriate page
            if (viewName === 'dashboard') {
                window.location.href = 'dashboard.html';
            } else if (viewName === 'users') {
                window.location.href = 'users.html';
            }
            // Additional pages can be added here as needed
        });
    });
}

// Initialize time period dropdowns
function initTimeDropdowns() {
    const timeSelectors = document.querySelectorAll('.time-period');
    
    timeSelectors.forEach(selector => {
        // Toggle dropdown on click
        selector.addEventListener('click', function(e) {
            // Don't toggle if click is on a dropdown option
            if (e.target.classList.contains('time-option')) return;
            
            // Close all other dropdowns
            document.querySelectorAll('.time-period.active').forEach(item => {
                if (item !== selector) {
                    item.classList.remove('active');
                }
            });
            
            // Toggle this dropdown
            this.classList.toggle('active');
            e.stopPropagation();
        });
        
        // Handle option selection
        const options = selector.querySelectorAll('.time-option');
        options.forEach(option => {
            option.addEventListener('click', function() {
                // Remove selected class from all options in this dropdown
                selector.querySelectorAll('.time-option').forEach(opt => {
                    opt.classList.remove('selected');
                });
                
                // Add selected class to clicked option
                this.classList.add('selected');
                
                // Update the displayed text (first child node of time-period)
                const displayText = selector.childNodes[0];
                displayText.nodeValue = this.textContent.toLowerCase() + ' ';
                
                // Close the dropdown
                selector.classList.remove('active');
                
                // You could add logic here to refresh the data based on selected time period
                updateCardData(selector.closest('.card'), this.textContent);
            });
        });
    });
    
    // Close dropdowns when clicking outside
    document.addEventListener('click', function(e) {
        if (!e.target.closest('.time-period')) {
            document.querySelectorAll('.time-period.active').forEach(item => {
                item.classList.remove('active');
            });
        }
    });
}

// Initialize user table filters
function initUserTableFilters() {
    const filterButtons = document.querySelectorAll('.filter-btn');
    
    filterButtons.forEach(button => {
        button.addEventListener('click', function() {
            // Remove active class from all filter buttons
            filterButtons.forEach(btn => btn.classList.remove('active'));
            
            // Add active class to clicked button
            this.classList.add('active');
            
            // Get the filter value
            const filterValue = this.textContent.trim();
            
            // Here you would filter the table rows based on the selected filter
            filterUserTable(filterValue);
        });
    });
}

// Filter user table based on selected category
function filterUserTable(filterValue) {
    const tableRows = document.querySelectorAll('.users-table tbody tr');
    
    tableRows.forEach(row => {
        const category = row.querySelector('td:nth-child(4)').textContent;
        
        if (filterValue === 'All Payment') {
            row.style.display = '';
        } else if (category.includes(filterValue)) {
            row.style.display = '';
        } else {
            row.style.display = 'none';
        }
    });
}

// Update card data based on selected time period
function updateCardData(card, timePeriod) {
    const cardTitle = card.querySelector('h3').textContent.trim().toLowerCase();
    console.log(`Updating ${cardTitle} with period: ${timePeriod}`);
    
    // Get the relevant data from the model based on time period
    let data;
    switch(timePeriod.toLowerCase()) {
        case 'last day':
            // Use daily data if available, or calculate from weekly
            data = dashboardModel.getWeeklyData();
            // Just use the last data point for daily view
            data = {
                income: [data.income[data.income.length - 1]],
                expenses: [data.expenses[data.expenses.length - 1]]
            };
            break;
        case 'last week':
            data = dashboardModel.getWeeklyData();
            break;
        case 'last month':
        case 'last 3 months':
        case 'last 6 months':
        case 'this year':
        case 'all time':
            data = dashboardModel.getMonthlyData();
            break;
        default:
            data = dashboardModel.getWeeklyData();
    }
    
    // Update the card based on its type
    switch(cardTitle) {
        case 'income and expenses':
            updateIncomeExpensesChart(card, data, timePeriod);
            break;
        case 'total balance':
        case 'total expenses':
            updateBalanceCard(card, data, timePeriod);
            break;
        case 'categories':
            updateCategoriesCard(card, timePeriod);
            break;
        case 'translations':
            updateTranslationsCard(card, timePeriod);
            break;
        case 'investments':
            updateInvestmentsCard(card, timePeriod);
            break;
        case 'spending parameters':
            updateSpendingParametersCard(card, timePeriod);
            break;
        case 'dissection':
            updateDissectionCard(card, data, timePeriod);
            break;
    }
}

// Update Income and Expenses chart based on time period
function updateIncomeExpensesChart(card, data, timePeriod) {
    // Show visual feedback that the chart is updating
    const chartContainer = card.querySelector('.income-expenses-chart');
    chartContainer.classList.add('updating');
    
    // Add a small delay to show the update animation
    setTimeout(() => {
        chartContainer.classList.remove('updating');
        // Real update would go here, manipulating SVG paths, etc.
        console.log(`Updated income and expenses chart with ${data.income.length} data points`);
    }, 300);
}

// Update balance card (total balance or expenses)
function updateBalanceCard(card, data, timePeriod) {
    // Add visual feedback
    const amount = card.querySelector('.balance-amount, .expenses-amount');
    amount.classList.add('updating');
    
    // Identify which card we're updating
    const isBalance = card.classList.contains('balance-card');
    const chartContainer = card.querySelector(isBalance ? '.balance-chart' : '.expenses-chart');
    
    setTimeout(() => {
        amount.classList.remove('updating');
        
        // Update amount with simulated data based on time period
        let newAmount;
        if (isBalance) {
            // Balances for different time periods
            switch(timePeriod.toLowerCase()) {
                case 'last month': newAmount = '$857,850'; break;
                case 'last 3 months': newAmount = '$982,430'; break;
                case 'last 6 months': newAmount = '$1,128,654'; break;
                case 'this year': newAmount = '$1,345,789'; break;
                case 'all time': newAmount = '$2,574,320'; break;
                default: newAmount = '$857,850';
            }
            amount.textContent = newAmount;
        } else {
            // Expenses for different time periods
            switch(timePeriod.toLowerCase()) {
                case 'last month': newAmount = '$198,110'; break;
                case 'last 3 months': newAmount = '$378,650'; break;
                case 'last 6 months': newAmount = '$641,275'; break;
                case 'this year': newAmount = '$923,450'; break;
                case 'all time': newAmount = '$1,427,890'; break;
                default: newAmount = '$198,110';
            }
            amount.textContent = newAmount;
        }
        
        // Adjust chart path if we have SVG paths
        const chartSvg = chartContainer.querySelector('svg');
        if (chartSvg) {
            // Get the relevant paths
            const areaPath = chartSvg.querySelector(isBalance ? '.balance-path' : '.expenses-path');
            const linePath = chartSvg.querySelector(isBalance ? '.balance-line' : '.expenses-line');
            
            if (areaPath && linePath) {
                // Generate new random paths based on time period (for demo purposes)
                // In a real app, this would use actual data
                const seed = timePeriod.toLowerCase().includes('month') ? 1 : 
                              timePeriod.toLowerCase().includes('3') ? 2 : 
                              timePeriod.toLowerCase().includes('6') ? 3 : 
                              timePeriod.toLowerCase().includes('year') ? 4 : 5;
                              
                // Create different paths based on the seed and whether it's balance or expenses
                let newLinePath, newAreaPath;
                
                if (isBalance) {
                    // Different curve for balance based on time period
                    switch(seed) {
                        case 1: 
                            newLinePath = "M0,70 C30,60 60,50 90,30 C120,10 150,20 180,15 C210,10 240,5 270,10 L300,5";
                            newAreaPath = "M0,70 C30,60 60,50 90,30 C120,10 150,20 180,15 C210,10 240,5 270,10 L300,5 L300,80 L0,80 Z";
                            break;
                        case 2: 
                            newLinePath = "M0,60 C30,50 60,40 90,20 C120,15 150,10 180,5 C210,10 240,15 270,10 L300,5";
                            newAreaPath = "M0,60 C30,50 60,40 90,20 C120,15 150,10 180,5 C210,10 240,15 270,10 L300,5 L300,80 L0,80 Z";
                            break;
                        case 3: 
                            newLinePath = "M0,50 C30,45 60,30 90,20 C120,10 150,5 180,10 C210,15 240,10 270,5 L300,10";
                            newAreaPath = "M0,50 C30,45 60,30 90,20 C120,10 150,5 180,10 C210,15 240,10 270,5 L300,10 L300,80 L0,80 Z";
                            break;
                        default:
                            newLinePath = "M0,40 C30,35 60,25 90,15 C120,5 150,10 180,5 C210,10 240,5 270,15 L300,10";
                            newAreaPath = "M0,40 C30,35 60,25 90,15 C120,5 150,10 180,5 C210,10 240,5 270,15 L300,10 L300,80 L0,80 Z";
                    }
                } else {
                    // Different curve for expenses based on time period
                    switch(seed) {
                        case 1: 
                            newLinePath = "M0,40 C30,50 60,60 90,55 C120,50 150,30 180,20 C210,10 240,15 270,30 L300,35";
                            newAreaPath = "M0,40 C30,50 60,60 90,55 C120,50 150,30 180,20 C210,10 240,15 270,30 L300,35 L300,80 L0,80 Z";
                            break;
                        case 2: 
                            newLinePath = "M0,30 C30,40 60,50 90,60 C120,55 150,40 180,30 C210,20 240,25 270,40 L300,45";
                            newAreaPath = "M0,30 C30,40 60,50 90,60 C120,55 150,40 180,30 C210,20 240,25 270,40 L300,45 L300,80 L0,80 Z";
                            break;
                        case 3: 
                            newLinePath = "M0,20 C30,30 60,50 90,60 C120,65 150,50 180,40 C210,30 240,35 270,50 L300,55";
                            newAreaPath = "M0,20 C30,30 60,50 90,60 C120,65 150,50 180,40 C210,30 240,35 270,50 L300,55 L300,80 L0,80 Z";
                            break;
                        default:
                            newLinePath = "M0,10 C30,20 60,40 90,55 C120,70 150,60 180,50 C210,40 240,45 270,60 L300,65";
                            newAreaPath = "M0,10 C30,20 60,40 90,55 C120,70 150,60 180,50 C210,40 240,45 270,60 L300,65 L300,80 L0,80 Z";
                    }
                }
                
                // Apply the new paths with a smooth transition
                areaPath.setAttribute('d', newAreaPath);
                linePath.setAttribute('d', newLinePath);
            }
        }
    }, 300);
}

// Simple function stubs for other card updates
function updateCategoriesCard(card, timePeriod) {
    console.log(`Updating categories for ${timePeriod}`);
}

function updateTranslationsCard(card, timePeriod) {
    console.log(`Updating translations for ${timePeriod}`);
}

function updateInvestmentsCard(card, timePeriod) {
    console.log(`Updating investments for ${timePeriod}`);
}

function updateSpendingParametersCard(card, timePeriod) {
    console.log(`Updating spending parameters for ${timePeriod}`);
}

function updateDissectionCard(card, data, timePeriod) {
    console.log(`Updating dissection chart for ${timePeriod}`);
}

// Add interactivity to the income-expenses chart
function initChartInteractivity() {
    const chart = document.querySelector('.income-expenses-chart');
    if (!chart) return;
    
    const tooltip = chart.querySelector('.tooltip-point');
    
    // Add hover effect to chart
    chart.addEventListener('mousemove', function(e) {
        // Get chart coordinates
        const rect = chart.getBoundingClientRect();
        const x = e.clientX - rect.left;
        const y = e.clientY - rect.top;
        
        // Calculate position within the chart (accounting for the translation)
        const chartX = x - 40; // Adjust for the 40px translation
        
        // Determine if we're over a path (simplified)
        const isOverIncomeArea = y < rect.height / 2;
        
        // Only show the tooltip if we're inside the chart area
        if (chartX > 0 && chartX < rect.width - 40) {
            // Simple positioning of the tooltip based on mouse
            // In a real implementation, we would interpolate along the path
            const valueY = isOverIncomeArea ? 90 : 130;
            
            // Position the tooltip - convert to viewBox coordinates
            const svgViewBox = {width: 800, height: 200};
            const displayToSvgX = (chartX / (rect.width - 40)) * 760; // 760 is the width after translation
            
            // Move the tooltip point
            const circle = tooltip.querySelector('circle');
            const tooltipRect = tooltip.querySelector('rect');
            const tooltipText = tooltip.querySelector('text');
            
            circle.setAttribute('cx', displayToSvgX);
            circle.setAttribute('cy', valueY);
            
            tooltipRect.setAttribute('x', displayToSvgX - 25);
            tooltipRect.setAttribute('y', valueY - 25);
            
            tooltipText.setAttribute('x', displayToSvgX);
            tooltipText.setAttribute('y', valueY - 10);
            
            // Generate a value based on position and path
            const value = isOverIncomeArea ? 
                (400 + Math.round((Math.sin(displayToSvgX/100) + 1) * 50)) : 
                (300 + Math.round((Math.sin(displayToSvgX/80) + 1) * 60));
            
            tooltipText.textContent = value.toFixed(2);
            
            // Show tooltip
            tooltip.classList.add('active');
        }
    });
    
    // Hide tooltip when leaving the chart
    chart.addEventListener('mouseleave', function() {
        // Reset tooltip to initial position
        const circle = tooltip.querySelector('circle');
        const tooltipRect = tooltip.querySelector('rect');
        const tooltipText = tooltip.querySelector('text');
        
        circle.setAttribute('cx', '440');
        circle.setAttribute('cy', '90');
        tooltipRect.setAttribute('x', '425');
        tooltipRect.setAttribute('y', '65');
        tooltipText.setAttribute('x', '450');
        tooltipText.setAttribute('y', '80');
        tooltipText.textContent = '445.12';
    });
}

// Initialize the dashboard
function initDashboard() {
    initTheme();
    initViews();
    initTimeDropdowns();
    initUserTableFilters();
    
    // Load user data from the model
    const userData = dashboardModel.getUserData();
    const categories = dashboardModel.getCategories();
    const investments = dashboardModel.getInvestments();
    const translations = dashboardModel.getTranslations();
    const cards = dashboardModel.getCards();
    
    // Update charts colors based on theme
    updateChartColors();
    
    console.log('Dashboard initialized with data from model');
}

// Initialize when the DOM is fully loaded
document.addEventListener('DOMContentLoaded', function() {
    // Initialize the dashboard model
    const dashboard = new DashboardModel();
    
    // Initialize theme
    initTheme();
    
    // Initialize views
    initViews();
    
    // Initialize time dropdowns
    initTimeDropdowns();
    
    // Initialize user table filters
    initUserTableFilters();
    
    // Set up event listeners
    document.getElementById('theme-toggle').addEventListener('change', toggleTheme);
    
    // Initialize data
    updateDashboardData(dashboard.getUserData());
    
    // Initialize chart interactivity
    initChartInteractivity();
}); 