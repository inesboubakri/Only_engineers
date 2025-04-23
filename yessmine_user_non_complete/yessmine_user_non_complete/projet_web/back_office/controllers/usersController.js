/**
 * Users Controller
 * Handles user management functionality
 */

// Initialize the dashboard model
const dashboardModel = new DashboardModel();

// Initialize theme based on user preference or default to light
function initTheme() {
    const savedTheme = localStorage.getItem('theme') || 'light';
    document.body.className = savedTheme + '-theme';
    
    // Set the toggle switch based on the theme
    const themeToggle = document.getElementById('theme-toggle');
    themeToggle.checked = savedTheme === 'dark';
}

// Toggle between light and dark themes
function toggleTheme() {
    const currentTheme = document.body.className.includes('light') ? 'light' : 'dark';
    const newTheme = currentTheme === 'light' ? 'dark' : 'light';
    
    document.body.className = newTheme + '-theme';
    localStorage.setItem('theme', newTheme);
}

// Initialize navigation between dashboard and other pages
function initNavigation() {
    const navItems = document.querySelectorAll('.sidebar-nav li');
    
    navItems.forEach(item => {
        item.addEventListener('click', function(e) {
            // Get the view name from the nav item text
            const viewName = this.querySelector('span').textContent.toLowerCase();
            
            // Handle navigation
            if (viewName === 'dashboard') {
                window.location.href = 'dashboard.html';
            } else if (viewName === 'users') {
                window.location.href = 'users.html';
            } else if (viewName === 'projects') {
                window.location.href = 'projects.html';
            }
        });
    });

    // Add specific handler for sign out button
    const signOutButton = document.getElementById('signOutButton');
    if (signOutButton) {
        signOutButton.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            handleSignOut();
        });
    }
}

// Function to handle sign out
function handleSignOut() {
    // Afficher un indicateur de chargement
    const loadingToast = document.createElement('div');
    loadingToast.className = 'toast loading';
    loadingToast.textContent = 'Déconnexion en cours...';
    document.body.appendChild(loadingToast);

    fetch('../../front_office/model/signout.php', {
        method: 'POST',
        credentials: 'same-origin',
        headers: {
            'Content-Type': 'application/json'
        }
    })
    .then(response => {
        if (!response.ok) {
            throw new Error('Erreur réseau');
        }
        return response.json();
    })
    .then(data => {
        if (data.success) {
            // Nettoyer le stockage local
            localStorage.clear();
            sessionStorage.clear();
            
            // Rediriger vers la page de connexion
            window.location.href = '../../front_office/view/signin.html';
        } else {
            throw new Error(data.message || 'Erreur de déconnexion');
        }
    })
    .catch(error => {
        console.error('Erreur:', error);
        // En cas d'erreur, on redirige quand même vers la page de connexion
        window.location.href = '../../front_office/view/signin.html';
    })
    .finally(() => {
        // Supprimer l'indicateur de chargement
        loadingToast.remove();
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
            
            // Filter the table rows based on the selected filter
            filterUserTable(filterValue);
        });
    });
}

// Filter user table based on selected category
function filterUserTable(filterValue) {
    // Filter table rows
    const tableRows = document.querySelectorAll('.users-table tbody tr');
    let filteredCount = 0;
    
    tableRows.forEach(row => {
        const category = row.querySelector('td:nth-child(4)').textContent;
        
        if (filterValue === 'All Payment') {
            row.style.display = '';
            filteredCount++;
        } else if (category.includes(filterValue)) {
            row.style.display = '';
            filteredCount++;
        } else {
            row.style.display = 'none';
        }
    });
    
    // Also filter grid cards if they exist
    const gridCards = document.querySelectorAll('.user-card');
    if (gridCards.length > 0) {
        gridCards.forEach(card => {
            const category = card.querySelector('.item-value[data-category]').textContent;
            
            if (filterValue === 'All Payment') {
                card.style.display = '';
            } else if (category.includes(filterValue)) {
                card.style.display = '';
            } else {
                card.style.display = 'none';
            }
        });
    }
    
    // Update UI to show filter results
    updateFilterResults(filteredCount);
}

// Initialize view toggle
function initViewToggle() {
    const viewButtons = document.querySelectorAll('.view-btn');
    const tableCard = document.querySelector('.users-table-card');
    
    // Get saved view preference or default to 'table'
    const savedView = localStorage.getItem('usersView') || 'table';
    
    // Set initial view based on saved preference
    setActiveView(savedView);
    
    viewButtons.forEach(button => {
        button.addEventListener('click', function() {
            // Get the view type from data attribute
            const viewType = this.getAttribute('data-view');
            
            // Update active button
            viewButtons.forEach(btn => btn.classList.remove('active'));
            this.classList.add('active');
            
            // Save preference
            localStorage.setItem('usersView', viewType);
            
            // Switch between views
            setActiveView(viewType);
        });
    });
    
    function setActiveView(viewType) {
        // Find the button for this view and make it active
        viewButtons.forEach(btn => {
            if (btn.getAttribute('data-view') === viewType) {
                btn.classList.add('active');
            } else {
                btn.classList.remove('active');
            }
        });
        
        // Apply appropriate class to container
        if (viewType === 'table') {
            tableCard.classList.remove('grid-view');
            tableCard.classList.add('table-view');
            
            // Show table and hide grid
            const tableElement = document.querySelector('.users-table');
            const gridElement = document.querySelector('.users-grid');
            
            if (tableElement) tableElement.style.display = '';
            if (gridElement) gridElement.style.display = 'none';
        } else {
            tableCard.classList.remove('table-view');
            tableCard.classList.add('grid-view');
            
            // Show grid and hide table
            const tableElement = document.querySelector('.users-table');
            const gridElement = document.querySelector('.users-grid');
            
            if (tableElement) tableElement.style.display = 'none';
            
            // If grid view is not already created, create it
            if (!gridElement) {
                createGridView();
            } else {
                // Show the grid and make sure it maintains the same filter state
                gridElement.style.display = 'grid';
                syncFilterStateToGridView();
            }
        }
    }
    
    // Sync the current filter state to the grid view
    function syncFilterStateToGridView() {
        const activeFilter = document.querySelector('.filter-btn.active').textContent.trim();
        const tableRows = document.querySelectorAll('.users-table tbody tr');
        const gridCards = document.querySelectorAll('.user-card');
        
        // For each row, apply the same visibility to the corresponding card
        tableRows.forEach((row, index) => {
            if (index < gridCards.length) {
                gridCards[index].style.display = row.style.display;
            }
        });
    }
    
    function createGridView() {
        // Create grid container if it doesn't exist
        if (!document.querySelector('.users-grid')) {
            const gridContainer = document.createElement('div');
            gridContainer.className = 'users-grid';
            
            // Get data from table rows and create cards
            const tableRows = document.querySelectorAll('.users-table tbody tr');
            
            tableRows.forEach(row => {
                const userInfo = row.querySelector('.user-info');
                const avatar = userInfo.querySelector('img').src;
                const name = userInfo.querySelector('.user-name').textContent;
                const id = userInfo.querySelector('.user-id').textContent;
                const payday = row.querySelector('td:nth-child(2)').textContent;
                const amount = row.querySelector('td:nth-child(3)').textContent;
                const category = row.querySelector('td:nth-child(4)').textContent;
                const status = row.querySelector('.status-badge').textContent;
                const statusClass = row.querySelector('.status-badge').className.split(' ')[1];
                
                // Create user card
                const card = document.createElement('div');
                card.className = 'user-card';
                card.innerHTML = `
                    <div class="user-card-header">
                        <div class="user-card-avatar">
                            <img src="${avatar}" alt="${name}">
                        </div>
                        <div class="user-card-info">
                            <div class="user-card-name">${name}</div>
                            <div class="user-card-id">${id}</div>
                        </div>
                        <button class="more-btn">•••</button>
                    </div>
                    <div class="user-card-details">
                        <div class="user-card-item">
                            <span class="item-label">Payday:</span>
                            <span class="item-value">${payday}</span>
                        </div>
                        <div class="user-card-item">
                            <span class="item-label">Amount:</span>
                            <span class="item-value">${amount}</span>
                        </div>
                        <div class="user-card-item">
                            <span class="item-label">Category:</span>
                            <span class="item-value" data-category>${category}</span>
                        </div>
                        <div class="user-card-status">
                            <span class="status-badge ${statusClass}">${status}</span>
                        </div>
                    </div>
                `;
                
                // Add same display property as the table row to maintain filter state
                card.style.display = row.style.display;
                
                gridContainer.appendChild(card);
            });
            
            // Add the grid to the table card
            const tableCard = document.querySelector('.users-table-card');
            tableCard.appendChild(gridContainer);
            
            // Initially hide the grid view if needed
            if (document.querySelector('.view-btn[data-view="table"]').classList.contains('active')) {
                gridContainer.style.display = 'none';
            } else {
                document.querySelector('.users-table').style.display = 'none';
                gridContainer.style.display = 'grid';
            }
        }
    }
}

// Initialize add payment button
function initAddPaymentButton() {
    const addButton = document.querySelector('.add-button');
    
    if (addButton) {
        addButton.addEventListener('click', function() {
            alert('Add new payment functionality will be implemented here.');
            // Here would be functionality to open a modal or navigate to a new payment form
        });
    }
}

// Initialize report generation button
function initReportButton() {
    const reportButton = document.querySelector('.report-card .service-button');
    
    if (reportButton) {
        reportButton.addEventListener('click', function() {
            showReportModal();
        });
    }
}

// Show report generation modal
function showReportModal() {
    // Create modal for choosing report format
    const modal = document.createElement('div');
    modal.className = 'modal report-modal';
    modal.innerHTML = `
        <div class="modal-content">
            <div class="modal-header">
                <h2>Generate Financial Report</h2>
                <button class="close-modal">&times;</button>
            </div>
            <div class="modal-body">
                <p>Please select the format for your financial report:</p>
                <div class="report-options">
                    <div class="report-option" data-format="pdf">
                        <div class="report-icon">📄</div>
                        <div class="report-format">PDF Format</div>
                        <div class="report-description">Generate a detailed PDF report that can be printed or shared.</div>
                    </div>
                    <div class="report-option" data-format="excel">
                        <div class="report-icon">📊</div>
                        <div class="report-format">Excel Format</div>
                        <div class="report-description">Export data to Excel spreadsheet for further analysis.</div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button class="cancel-btn">Cancel</button>
                <button class="generate-btn" disabled>Generate Report</button>
            </div>
        </div>
    `;
    
    // Add modal to the page
    document.body.appendChild(modal);
    
    // Show modal with animation
    setTimeout(() => {
        modal.classList.add('active');
    }, 10);
    
    // Handle report option selection
    const reportOptions = modal.querySelectorAll('.report-option');
    let selectedFormat = null;
    
    reportOptions.forEach(option => {
        option.addEventListener('click', function() {
            // Remove selected class from all options
            reportOptions.forEach(opt => opt.classList.remove('selected'));
            
            // Add selected class to clicked option
            this.classList.add('selected');
            
            // Enable the generate button
            const generateButton = modal.querySelector('.generate-btn');
            generateButton.removeAttribute('disabled');
            
            // Store the selected format
            selectedFormat = this.getAttribute('data-format');
        });
    });
    
    // Close modal on X button click
    modal.querySelector('.close-modal').addEventListener('click', () => {
        modal.classList.remove('active');
        setTimeout(() => {
            modal.remove();
        }, 300);
    });
    
    // Close modal on Cancel button click
    modal.querySelector('.cancel-btn').addEventListener('click', () => {
        modal.classList.remove('active');
        setTimeout(() => {
            modal.remove();
        }, 300);
    });
    
    // Handle generate button click
    modal.querySelector('.generate-btn').addEventListener('click', () => {
        if (selectedFormat) {
            generateReport(selectedFormat);
            
            // Close modal
            modal.classList.remove('active');
            setTimeout(() => {
                modal.remove();
            }, 300);
        }
    });
}

// Generate report based on selected format
function generateReport(format) {
    // Show loading toast
    showToast(`Preparing ${format.toUpperCase()} report...`);
    
    // Simulate processing delay
    setTimeout(() => {
        if (format === 'pdf') {
            generatePDFReport();
        } else if (format === 'excel') {
            generateExcelReport();
        }
    }, 1000);
}

// Helper function to collect table data
function collectTableData() {
    const tableRows = document.querySelectorAll('.users-table tbody tr');
    const data = [];
    
    tableRows.forEach(row => {
        // Only include visible rows (not filtered out)
        if (row.style.display !== 'none') {
            data.push({
                name: row.querySelector('.user-name').textContent,
                id: row.querySelector('.user-id').textContent,
                payday: row.querySelector('td:nth-child(2)').textContent,
                amount: row.querySelector('td:nth-child(3)').textContent,
                category: row.querySelector('td:nth-child(4)').textContent,
                status: row.querySelector('.status-badge').textContent
            });
        }
    });
    
    return data;
}

// Helper function to generate PDF content using jsPDF
async function generatePDFContent() {
    try {
        // Get table data
        const tableData = collectTableData();
        
        if (!tableData || tableData.length === 0) {
            throw new Error('No data available for PDF report');
        }
        
        // Create new jsPDF instance
        const doc = new jspdf.jsPDF();
        
        // Add title
        doc.setFontSize(18);
        doc.text('Financial Report', 14, 20);
        
        // Add generation date
        doc.setFontSize(11);
        doc.text(`Generated on: ${new Date().toLocaleDateString()}`, 14, 30);
        
        // Prepare table data
        const headers = [['Name', 'ID', 'Payment Date', 'Amount', 'Category', 'Status']];
        const data = tableData.map(row => [
            row.name,
            row.id,
            row.payday,
            row.amount,
            row.category,
            row.status
        ]);
        
        // Add table using autotable plugin
        doc.autoTable({
            head: headers,
            body: data,
            startY: 40,
            styles: {
                fontSize: 10,
                cellPadding: 3
            },
            headStyles: {
                fillColor: [79, 70, 229],
                textColor: 255,
                fontSize: 11,
                fontStyle: 'bold'
            },
            alternateRowStyles: {
                fillColor: [245, 245, 245]
            }
        });
        
        // Convert PDF to blob
        const pdfBlob = doc.output('blob');
        return pdfBlob;
    } catch (error) {
        console.error('Error generating PDF:', error);
        throw error;
    }
}

// Helper function to generate Excel content using SheetJS
async function generateExcelContent() {
    try {
        // Get table data
        const tableData = collectTableData();
        
        if (!tableData || tableData.length === 0) {
            throw new Error('No data available for Excel report');
        }
        
        // Prepare worksheet data
        const wsData = [
            ['Financial Report'],
            [`Generated on: ${new Date().toLocaleDateString()}`],
            [], // Empty row for spacing
            ['Name', 'ID', 'Payment Date', 'Amount', 'Category', 'Status']
        ];
        
        // Add data rows
        tableData.forEach(row => {
            wsData.push([
                row.name,
                row.id,
                row.payday,
                row.amount,
                row.category,
                row.status
            ]);
        });
        
        // Create workbook and worksheet
        const wb = XLSX.utils.book_new();
        const ws = XLSX.utils.aoa_to_sheet(wsData);
        
        // Set column widths
        const colWidths = [
            { wch: 20 }, // Name
            { wch: 15 }, // ID
            { wch: 15 }, // Payment Date
            { wch: 15 }, // Amount
            { wch: 15 }, // Category
            { wch: 15 }  // Status
        ];
        ws['!cols'] = colWidths;
        
        // Add worksheet to workbook
        XLSX.utils.book_append_sheet(wb, ws, 'Financial Report');
        
        // Convert to blob
        const excelBlob = await new Promise((resolve, reject) => {
            try {
                const wbout = XLSX.write(wb, { bookType: 'xlsx', type: 'array' });
                const blob = new Blob([wbout], { type: 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' });
                resolve(blob);
            } catch (e) {
                reject(e);
            }
        });
        
        return excelBlob;
    } catch (error) {
        console.error('Error generating Excel:', error);
        throw error;
    }
}

// Function to trigger native Save As dialog
async function saveFileWithDialog(blob, suggestedName) {
    try {
        // Create a handle to save the file
        const handle = await window.showSaveFilePicker({
            suggestedName: suggestedName,
            types: [{
                description: 'Document',
                accept: {
                    'application/pdf': ['.pdf'],
                    'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet': ['.xlsx']
                }
            }]
        });
        
        // Create a FileSystemWritableFileStream to write to
        const writable = await handle.createWritable();
        
        // Write the blob to the file
        await writable.write(blob);
        
        // Close the file
        await writable.close();
        
        return handle.name;
    } catch (error) {
        if (error.name === 'AbortError') {
            // User cancelled the save dialog
            return null;
        }
        throw error;
    }
}

// Handle PDF save
async function handlePDFSave(saveDialog) {
    try {
        const suggestedName = `Financial_Report_${new Date().toISOString().split('T')[0]}.pdf`;
        
        // Show preparing message
        showToast('Preparing your PDF report...');
        
        // Generate PDF blob
        const pdfBlob = await generatePDFContent();
        
        // Trigger native Save As dialog
        const fileName = await saveFileWithDialog(pdfBlob, suggestedName);
        
        if (fileName) {
            // Show success message
            showToast(`PDF report "${fileName}" has been saved successfully!`);
        }
        
        // Close dialog
        saveDialog.classList.remove('active');
        setTimeout(() => {
            saveDialog.remove();
        }, 300);
    } catch (error) {
        console.error('Error saving PDF:', error);
        showToast(`Error saving PDF: ${error.message}. Please try again.`);
    }
}

// Handle Excel save
async function handleExcelSave(saveDialog) {
    try {
        const suggestedName = `Financial_Report_${new Date().toISOString().split('T')[0]}.xlsx`;
        
        // Show preparing message
        showToast('Preparing your Excel report...');
        
        // Generate Excel blob
        const excelBlob = await generateExcelContent();
        
        // Trigger native Save As dialog
        const fileName = await saveFileWithDialog(excelBlob, suggestedName);
        
        if (fileName) {
            // Show success message
            showToast(`Excel report "${fileName}" has been saved successfully!`);
        }
        
        // Close dialog
        saveDialog.classList.remove('active');
        setTimeout(() => {
            saveDialog.remove();
        }, 300);
    } catch (error) {
        console.error('Error saving Excel:', error);
        showToast(`Error saving Excel: ${error.message}. Please try again.`);
    }
}

// Generate PDF report
function generatePDFReport() {
    // Create file browser dialog
    const saveDialog = document.createElement('div');
    saveDialog.className = 'modal save-dialog';
    saveDialog.innerHTML = `
        <div class="modal-content">
            <div class="modal-header">
                <h2>Save PDF Report</h2>
                <button class="close-modal">&times;</button>
            </div>
            <div class="modal-body">
                <p>Click Save to choose where to save your PDF report.</p>
            </div>
            <div class="modal-footer">
                <button class="cancel-btn">Cancel</button>
                <button class="save-btn">Save PDF</button>
            </div>
        </div>
    `;
    
    // Add dialog to the page
    document.body.appendChild(saveDialog);
    
    // Show dialog with animation
    setTimeout(() => {
        saveDialog.classList.add('active');
    }, 10);
    
    // Close dialog on X button click
    saveDialog.querySelector('.close-modal').addEventListener('click', () => {
        saveDialog.classList.remove('active');
        setTimeout(() => {
            saveDialog.remove();
        }, 300);
    });
    
    // Close dialog on Cancel button click
    saveDialog.querySelector('.cancel-btn').addEventListener('click', () => {
        saveDialog.classList.remove('active');
        setTimeout(() => {
            saveDialog.remove();
        }, 300);
    });
    
    // Handle save button click
    saveDialog.querySelector('.save-btn').addEventListener('click', () => {
        handlePDFSave(saveDialog);
    });
}

// Generate Excel report
function generateExcelReport() {
    // Create file browser dialog
    const saveDialog = document.createElement('div');
    saveDialog.className = 'modal save-dialog';
    saveDialog.innerHTML = `
        <div class="modal-content">
            <div class="modal-header">
                <h2>Save Excel Report</h2>
                <button class="close-modal">&times;</button>
            </div>
            <div class="modal-body">
                <p>Click Save to choose where to save your Excel report.</p>
            </div>
            <div class="modal-footer">
                <button class="cancel-btn">Cancel</button>
                <button class="save-btn">Save Excel</button>
            </div>
        </div>
    `;
    
    // Add dialog to the page
    document.body.appendChild(saveDialog);
    
    // Show dialog with animation
    setTimeout(() => {
        saveDialog.classList.add('active');
    }, 10);
    
    // Close dialog on X button click
    saveDialog.querySelector('.close-modal').addEventListener('click', () => {
        saveDialog.classList.remove('active');
        setTimeout(() => {
            saveDialog.remove();
        }, 300);
    });
    
    // Close dialog on Cancel button click
    saveDialog.querySelector('.cancel-btn').addEventListener('click', () => {
        saveDialog.classList.remove('active');
        setTimeout(() => {
            saveDialog.remove();
        }, 300);
    });
    
    // Handle save button click
    saveDialog.querySelector('.save-btn').addEventListener('click', () => {
        handleExcelSave(saveDialog);
    });
}

// Initialize action buttons
function initActionButtons() {
    // Handle initial buttons and any future buttons added to the DOM
    document.addEventListener('click', function(e) {
        // Check if clicked element is a more button
        if (e.target.classList.contains('more-btn') || e.target.closest('.more-btn')) {
            e.stopPropagation();
            const button = e.target.classList.contains('more-btn') ? e.target : e.target.closest('.more-btn');
            
            // Get the parent row or card
            const row = button.closest('tr');
            const card = button.closest('.user-card');
            
            let userName, userElement;
            
            if (row) {
                userElement = row;
                userName = row.querySelector('.user-name').textContent;
            } else if (card) {
                userElement = card;
                userName = card.querySelector('.user-card-name').textContent;
            } else {
                return;
            }
            
            // Check if menu is already open, close it if it is
            const existingMenu = document.querySelector('.action-menu');
            if (existingMenu) {
                existingMenu.remove();
                // If clicking the same button that opened the current menu, just close and return
                if (button.getAttribute('data-menu-open') === 'true') {
                    button.removeAttribute('data-menu-open');
                    return;
                }
            }
            
            // Mark this button as having an open menu
            button.setAttribute('data-menu-open', 'true');
            
            // Create action menu
            const actionMenu = document.createElement('div');
            actionMenu.className = 'action-menu';
            actionMenu.innerHTML = `
                <div class="action-item modify">
                    <span class="action-icon">✏️</span>
                    <span>Modify</span>
                </div>
                <div class="action-item delete">
                    <span class="action-icon">🗑️</span>
                    <span>Delete</span>
                </div>
            `;
            
            // Position the menu next to the button
            const rect = button.getBoundingClientRect();
            
            // Set position based on whether we're in table or grid view
            actionMenu.style.position = 'absolute';
            actionMenu.style.top = `${rect.bottom + window.scrollY}px`;
            
            if (row) {
                // Table view - position to the left of the button
                actionMenu.style.right = `${document.body.clientWidth - rect.right}px`;
            } else {
                // Grid view - position centered under the button
                actionMenu.style.left = `${rect.left + window.scrollX - 60}px`;
            }
            
            // Add event listeners to menu items
            actionMenu.querySelector('.action-item.modify').addEventListener('click', function() {
                modifyPayment(userElement, userName);
                actionMenu.remove();
                button.removeAttribute('data-menu-open');
            });
            
            actionMenu.querySelector('.action-item.delete').addEventListener('click', function() {
                deletePayment(userElement, userName);
                actionMenu.remove();
                button.removeAttribute('data-menu-open');
            });
            
            // Append menu to the document body
            document.body.appendChild(actionMenu);
        }
    });
    
    // Close any open menu when clicking outside
    document.addEventListener('click', function(e) {
        if (!e.target.closest('.more-btn') && !e.target.closest('.action-menu')) {
            const openMenus = document.querySelectorAll('.action-menu');
            const openButtons = document.querySelectorAll('[data-menu-open="true"]');
            
            openMenus.forEach(menu => menu.remove());
            openButtons.forEach(button => button.removeAttribute('data-menu-open'));
        }
    });
}

// Function to handle payment modification
function modifyPayment(element, userName) {
    console.log(`Modifying payment for ${userName}`);
    
    // Create modal for editing
    const modal = document.createElement('div');
    modal.className = 'modal edit-modal';
    
    // Get payment information to pre-fill the form
    let paymentInfo = {};
    
    if (element.tagName === 'TR') {
        // Table row
        paymentInfo = {
            name: element.querySelector('.user-name').textContent,
            id: element.querySelector('.user-id').textContent,
            payday: element.querySelector('td:nth-child(2)').textContent,
            amount: element.querySelector('td:nth-child(3)').textContent,
            category: element.querySelector('td:nth-child(4)').textContent,
            status: element.querySelector('.status-badge').textContent
        };
    } else {
        // Grid card
        paymentInfo = {
            name: element.querySelector('.user-card-name').textContent,
            id: element.querySelector('.user-card-id').textContent,
            payday: element.querySelectorAll('.item-value')[0].textContent,
            amount: element.querySelectorAll('.item-value')[1].textContent,
            category: element.querySelectorAll('.item-value')[2].textContent,
            status: element.querySelector('.status-badge').textContent
        };
    }
    
    // Create modal content
    modal.innerHTML = `
        <div class="modal-content">
            <div class="modal-header">
                <h2>Modify Payment</h2>
                <button class="close-modal">&times;</button>
            </div>
            <div class="modal-body">
                <form id="edit-payment-form">
                    <div class="form-group">
                        <label for="edit-name">Name</label>
                        <input type="text" id="edit-name" value="${paymentInfo.name}" required>
                    </div>
                    <div class="form-group">
                        <label for="edit-id">ID</label>
                        <input type="text" id="edit-id" value="${paymentInfo.id}" readonly>
                    </div>
                    <div class="form-group">
                        <label for="edit-payday">Payday</label>
                        <input type="text" id="edit-payday" value="${paymentInfo.payday}" required>
                    </div>
                    <div class="form-group">
                        <label for="edit-amount">Amount</label>
                        <input type="text" id="edit-amount" value="${paymentInfo.amount}" required>
                    </div>
                    <div class="form-group">
                        <label for="edit-category">Category</label>
                        <select id="edit-category" required>
                            <option value="Member Payday" ${paymentInfo.category.includes('Member') ? 'selected' : ''}>Member Payday</option>
                            <option value="Staff Payday" ${paymentInfo.category.includes('Staff') ? 'selected' : ''}>Staff Payday</option>
                            <option value="Freelance Payday" ${paymentInfo.category.includes('Freelance') ? 'selected' : ''}>Freelance Payday</option>
                            <option value="Part-Time Payday" ${paymentInfo.category.includes('Part-Time') ? 'selected' : ''}>Part-Time Payday</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="edit-status">Status</label>
                        <select id="edit-status" required>
                            <option value="Payment Success" ${paymentInfo.status.includes('Success') ? 'selected' : ''}>Payment Success</option>
                            <option value="Pending Payment" ${paymentInfo.status.includes('Pending') ? 'selected' : ''}>Pending Payment</option>
                            <option value="Payment Failed" ${paymentInfo.status.includes('Failed') ? 'selected' : ''}>Payment Failed</option>
                        </select>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button class="cancel-btn">Cancel</button>
                <button class="save-btn">Save Changes</button>
            </div>
        </div>
    `;
    
    // Add modal to the page
    document.body.appendChild(modal);
    
    // Show modal with animation
    setTimeout(() => {
        modal.classList.add('active');
    }, 10);
    
    // Close modal on X button click
    modal.querySelector('.close-modal').addEventListener('click', () => {
        modal.classList.remove('active');
        setTimeout(() => {
            modal.remove();
        }, 300);
    });
    
    // Close modal on Cancel button click
    modal.querySelector('.cancel-btn').addEventListener('click', () => {
        modal.classList.remove('active');
        setTimeout(() => {
            modal.remove();
        }, 300);
    });
    
    // Save changes
    modal.querySelector('.save-btn').addEventListener('click', () => {
        // Get form values
        const formData = {
            name: document.getElementById('edit-name').value,
            id: document.getElementById('edit-id').value,
            payday: document.getElementById('edit-payday').value,
            amount: document.getElementById('edit-amount').value,
            category: document.getElementById('edit-category').value,
            status: document.getElementById('edit-status').value
        };
        
        // Update element with new values
        updatePaymentElement(element, formData);
        
        // Close modal
        modal.classList.remove('active');
        setTimeout(() => {
            modal.remove();
        }, 300);
        
        // Show success toast
        showToast(`Payment for ${formData.name} updated successfully`);
    });
}

// Function to update payment element with new data
function updatePaymentElement(element, data) {
    if (element.tagName === 'TR') {
        // Update table row
        element.querySelector('.user-name').textContent = data.name;
        element.querySelector('td:nth-child(2)').textContent = data.payday;
        element.querySelector('td:nth-child(3)').textContent = data.amount;
        element.querySelector('td:nth-child(4)').textContent = data.category;
        
        // Update status badge
        const statusBadge = element.querySelector('.status-badge');
        statusBadge.textContent = data.status;
        
        // Update badge class based on status
        statusBadge.className = 'status-badge';
        if (data.status.includes('Success')) {
            statusBadge.classList.add('success');
        } else if (data.status.includes('Pending')) {
            statusBadge.classList.add('pending');
        } else {
            statusBadge.classList.add('failed');
        }
    } else {
        // Update grid card
        element.querySelector('.user-card-name').textContent = data.name;
        const itemValues = element.querySelectorAll('.item-value');
        itemValues[0].textContent = data.payday;
        itemValues[1].textContent = data.amount;
        itemValues[2].textContent = data.category;
        
        // Update status badge
        const statusBadge = element.querySelector('.status-badge');
        statusBadge.textContent = data.status;
        
        // Update badge class based on status
        statusBadge.className = 'status-badge';
        if (data.status.includes('Success')) {
            statusBadge.classList.add('success');
        } else if (data.status.includes('Pending')) {
            statusBadge.classList.add('pending');
        } else {
            statusBadge.classList.add('failed');
        }
    }
    
    // Also update the other view (if it exists)
    const userId = data.id;
    
    // If we updated a table row, also update the corresponding card
    if (element.tagName === 'TR') {
        const cards = document.querySelectorAll('.user-card');
        cards.forEach(card => {
            if (card.querySelector('.user-card-id').textContent === userId) {
                updatePaymentElement(card, data);
            }
        });
    } 
    // If we updated a card, also update the corresponding table row
    else {
        const rows = document.querySelectorAll('.users-table tbody tr');
        rows.forEach(row => {
            if (row.querySelector('.user-id').textContent === userId) {
                updatePaymentElement(row, data);
            }
        });
    }
}

// Function to handle payment deletion
function deletePayment(element, userName) {
    console.log(`Deleting payment for ${userName}`);
    
    // Create confirmation modal
    const modal = document.createElement('div');
    modal.className = 'modal delete-modal';
    modal.innerHTML = `
        <div class="modal-content">
            <div class="modal-header">
                <h2>Confirm Deletion</h2>
                <button class="close-modal">&times;</button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to delete the payment for <strong>${userName}</strong>?</p>
                <p>This action cannot be undone.</p>
            </div>
            <div class="modal-footer">
                <button class="cancel-btn">Cancel</button>
                <button class="confirm-btn">Delete</button>
            </div>
        </div>
    `;
    
    // Add modal to the page
    document.body.appendChild(modal);
    
    // Show modal with animation
    setTimeout(() => {
        modal.classList.add('active');
    }, 10);
    
    // Close modal on X button click
    modal.querySelector('.close-modal').addEventListener('click', () => {
        modal.classList.remove('active');
        setTimeout(() => {
            modal.remove();
        }, 300);
    });
    
    // Close modal on Cancel button click
    modal.querySelector('.cancel-btn').addEventListener('click', () => {
        modal.classList.remove('active');
        setTimeout(() => {
            modal.remove();
        }, 300);
    });
    
    // Handle deletion
    modal.querySelector('.confirm-btn').addEventListener('click', () => {
        // Get user ID to find matching elements in both views
        let userId;
        
        if (element.tagName === 'TR') {
            userId = element.querySelector('.user-id').textContent;
        } else {
            userId = element.querySelector('.user-card-id').textContent;
        }
        
        // Remove from table view
        const tableRows = document.querySelectorAll('.users-table tbody tr');
        tableRows.forEach(row => {
            if (row.querySelector('.user-id').textContent === userId) {
                row.remove();
            }
        });
        
        // Remove from grid view if it exists
        const gridCards = document.querySelectorAll('.user-card');
        gridCards.forEach(card => {
            if (card.querySelector('.user-card-id').textContent === userId) {
                card.remove();
            }
        });
        
        // Close modal
        modal.classList.remove('active');
        setTimeout(() => {
            modal.remove();
        }, 300);
        
        // Show success toast
        showToast(`Payment for ${userName} deleted successfully`);
    });
}

// Function to show a toast notification
function showToast(message) {
    const toast = document.createElement('div');
    toast.className = 'toast';
    toast.textContent = message;
    
    document.body.appendChild(toast);
    
    // Show toast
    setTimeout(() => {
        toast.classList.add('show');
    }, 10);
    
    // Hide and remove toast after 3 seconds
    setTimeout(() => {
        toast.classList.remove('show');
        setTimeout(() => {
            toast.remove();
        }, 300);
    }, 3000);
}

// Initialize the users page
function initUsersPage() {
    // Initialize theme
    initTheme();
    
    // Initialize navigation
    initNavigation();
    
    // Initialize table filters
    initUserTableFilters();
    
    // Initialize view toggle
    initViewToggle();
    
    // Initialize add payment button
    initAddPaymentButton();
    
    // Initialize report generation button
    initReportButton();
    
    // Initialize action buttons
    initActionButtons();
    
    console.log('Users page initialized');
}

// Initialize when the DOM is fully loaded
document.addEventListener('DOMContentLoaded', function() {
    // Initialize the users page
    initUsersPage();
    
    // Set up theme toggle event listener
    const themeToggle = document.getElementById('theme-toggle');
    if (themeToggle) {
        themeToggle.addEventListener('change', toggleTheme);
    }
});

// Update filter results in UI
function updateFilterResults(count) {
    // Optional - you could add a counter or message showing how many items are displayed
    console.log(`Showing ${count} payments after filtering`);
}

function loadUsers() {
    fetch('../model/get_users.php')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const tbody = document.getElementById('usersTableBody');
                tbody.innerHTML = '';

                data.users.forEach(user => {
                    const row = document.createElement('tr');
                    row.setAttribute('data-user-id', user.id);
                    const profilePicture = user.profile_picture 
                        ? '../../front_office/assets/uploads/profile_pictures/' + user.profile_picture
                        : '../../front_office/assets/default-profile.png';
                    
                    row.innerHTML = `
                        <td>
                            <div class="user-avatar">
                                <img src="${profilePicture}" alt="${user.name}" onerror="this.src='../../front_office/assets/default-profile.png'">
                            </div>
                        </td>
                        <td>${user.name}</td>
                        <td>${user.email}</td>
                        <td>${user.position || 'Not specified'}</td>
                        <td><span class="status-badge ${user.status.toLowerCase()}">${user.status}</span></td>
                        <td>${user.is_admin}</td>
                        <td>${formatDate(user.created_at)}</td>
                        <td><button class="more-btn" onclick="showUserActions(${user.id})">•••</button></td>
                    `;
                    tbody.appendChild(row);
                });
            } else {
                console.error('Error loading users:', data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
        });
}

function loadAdminInfo() {
    fetch('../model/get_admin_info.php')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const adminProfilePic = document.getElementById('adminProfilePic');
                const adminName = document.getElementById('adminName');
                
                if (data.admin.profile_picture) {
                    adminProfilePic.src = '../../front_office/assets/uploads/profile_pictures/' + data.admin.profile_picture;
                }
                
                if (data.admin.name) {
                    adminName.textContent = data.admin.name;
                }
                
                adminProfilePic.onerror = function() {
                    this.src = '../../front_office/assets/default-profile.png';
                };
            }
        })
        .catch(error => {
            console.error('Error loading admin info:', error);
        });
}

function formatDate(dateString) {
    if (!dateString) return 'N/A';
    const date = new Date(dateString);
    if (isNaN(date.getTime())) return dateString;
    return date.toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'long',
        day: 'numeric'
    });
}

function showUserActions(userId) {
    const existingMenu = document.querySelector('.action-menu');
    if (existingMenu) {
        existingMenu.remove();
    }

    const button = event.currentTarget;
    const rect = button.getBoundingClientRect();

    const menu = document.createElement('div');
    menu.className = 'action-menu';
    menu.style.display = 'block';
    menu.style.top = `${rect.bottom + window.scrollY}px`;
    menu.style.left = `${rect.left}px`;

    menu.innerHTML = `
        <div class="action-item" onclick="editUser(${userId})">
            <span>✏️</span> Edit
        </div>
        <div class="action-item" onclick="deleteUser(${userId})">
            <span>🗑️</span> Delete
        </div>
    `;

    document.body.appendChild(menu);

    document.addEventListener('click', function closeMenu(e) {
        if (!menu.contains(e.target) && e.target !== button) {
            menu.remove();
            document.removeEventListener('click', closeMenu);
        }
    });
}

function editUser(userId) {
    console.log('Edit user:', userId);
    const row = document.querySelector(`tr[data-user-id="${userId}"]`);
    if (!row) {
        console.error('User row not found');
        return;
    }

    const user = {
        id: userId,
        name: row.querySelector('td:nth-child(2)').textContent,
        email: row.querySelector('td:nth-child(3)').textContent,
        position: row.querySelector('td:nth-child(4)').textContent,
        is_admin: row.querySelector('td:nth-child(6)').textContent
    };

    document.getElementById('editUserId').value = user.id;
    document.getElementById('editFullName').value = user.name;
    document.getElementById('editEmail').value = user.email;
    document.getElementById('editPosition').value = user.position !== 'Not specified' ? user.position : '';
    document.getElementById('editIsAdmin').value = user.is_admin;

    const modal = document.getElementById('editModal');
    if (!modal) {
        console.error('Modal not found');
        return;
    }

    modal.style.display = 'block';
    modal.offsetHeight; // Force reflow
    requestAnimationFrame(() => {
        modal.classList.add('show');
    });

    const closeBtn = modal.querySelector('.close');
    closeBtn.onclick = closeEditModal;

    window.onclick = function(event) {
        if (event.target === modal) {
            closeEditModal();
        }
    }

    const form = document.getElementById('editUserForm');
    form.onsubmit = function(e) {
        e.preventDefault();
        updateUser(userId);
    }
}

function closeEditModal() {
    const modal = document.getElementById('editModal');
    modal.classList.remove('show');
    setTimeout(() => {
        modal.style.display = 'none';
    }, 300);
}

function updateUser(userId) {
    const formData = new FormData();
    formData.append('user_id', userId);
    formData.append('full_name', document.getElementById('editFullName').value);
    formData.append('email', document.getElementById('editEmail').value);
    formData.append('position', document.getElementById('editPosition').value);
    formData.append('is_admin', document.getElementById('editIsAdmin').value);

    fetch('../model/update_user.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            document.getElementById('editModal').style.display = 'none';
            loadUsers();
            alert('User updated successfully');
        } else {
            alert('Error updating user: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error updating user');
    });
}

function deleteUser(userId) {
    if (confirm('Are you sure you want to delete this user?')) {
        const formData = new FormData();
        formData.append('user_id', userId);

        fetch('../model/delete_user.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                loadUsers();
                alert('User deleted successfully');
            } else {
                alert('Error deleting user: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error deleting user');
        });
    }
}

// Initialize when the DOM is fully loaded
document.addEventListener('DOMContentLoaded', function() {
    // Initialize the users page
    initUsersPage();
    
    // Set up theme toggle event listener
    const themeToggle = document.getElementById('theme-toggle');
    if (themeToggle) {
        themeToggle.addEventListener('change', toggleTheme);
    }
});

// Update filter results in UI
function updateFilterResults(count) {
    // Optional - you could add a counter or message showing how many items are displayed
    console.log(`Showing ${count} payments after filtering`);
}

function loadUsers() {
    fetch('../model/get_users.php')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const tbody = document.getElementById('usersTableBody');
                tbody.innerHTML = '';

                data.users.forEach(user => {
                    const row = document.createElement('tr');
                    row.setAttribute('data-user-id', user.id);
                    const profilePicture = user.profile_picture 
                        ? '../../front_office/assets/uploads/profile_pictures/' + user.profile_picture
                        : '../../front_office/assets/default-profile.png';
                    
                    row.innerHTML = `
                        <td>
                            <div class="user-avatar">
                                <img src="${profilePicture}" alt="${user.name}" onerror="this.src='../../front_office/assets/default-profile.png'">
                            </div>
                        </td>
                        <td>${user.name}</td>
                        <td>${user.email}</td>
                        <td>${user.position || 'Not specified'}</td>
                        <td><span class="status-badge ${user.status.toLowerCase()}">${user.status}</span></td>
                        <td>${user.is_admin}</td>
                        <td>${formatDate(user.created_at)}</td>
                        <td><button class="more-btn" onclick="showUserActions(${user.id})">•••</button></td>
                    `;
                    tbody.appendChild(row);
                });
            } else {
                console.error('Error loading users:', data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
        });
}

function loadAdminInfo() {
    fetch('../model/get_admin_info.php')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const adminProfilePic = document.getElementById('adminProfilePic');
                const adminName = document.getElementById('adminName');
                
                if (data.admin.profile_picture) {
                    adminProfilePic.src = '../../front_office/assets/uploads/profile_pictures/' + data.admin.profile_picture;
                }
                
                if (data.admin.name) {
                    adminName.textContent = data.admin.name;
                }
                
                adminProfilePic.onerror = function() {
                    this.src = '../../front_office/assets/default-profile.png';
                };
            }
        })
        .catch(error => {
            console.error('Error loading admin info:', error);
        });
}

function formatDate(dateString) {
    if (!dateString) return 'N/A';
    const date = new Date(dateString);
    if (isNaN(date.getTime())) return dateString;
    return date.toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'long',
        day: 'numeric'
    });
}

function showUserActions(userId) {
    const existingMenu = document.querySelector('.action-menu');
    if (existingMenu) {
        existingMenu.remove();
    }

    const button = event.currentTarget;
    const rect = button.getBoundingClientRect();

    const menu = document.createElement('div');
    menu.className = 'action-menu';
    menu.style.display = 'block';
    menu.style.top = `${rect.bottom + window.scrollY}px`;
    menu.style.left = `${rect.left}px`;

    menu.innerHTML = `
        <div class="action-item" onclick="editUser(${userId})">
            <span>✏️</span> Edit
        </div>
        <div class="action-item" onclick="deleteUser(${userId})">
            <span>🗑️</span> Delete
        </div>
    `;

    document.body.appendChild(menu);

    document.addEventListener('click', function closeMenu(e) {
        if (!menu.contains(e.target) && e.target !== button) {
            menu.remove();
            document.removeEventListener('click', closeMenu);
        }
    });
}

function editUser(userId) {
    console.log('Edit user:', userId);
    const row = document.querySelector(`tr[data-user-id="${userId}"]`);
    if (!row) {
        console.error('User row not found');
        return;
    }

    const user = {
        id: userId,
        name: row.querySelector('td:nth-child(2)').textContent,
        email: row.querySelector('td:nth-child(3)').textContent,
        position: row.querySelector('td:nth-child(4)').textContent,
        is_admin: row.querySelector('td:nth-child(6)').textContent
    };

    document.getElementById('editUserId').value = user.id;
    document.getElementById('editFullName').value = user.name;
    document.getElementById('editEmail').value = user.email;
    document.getElementById('editPosition').value = user.position !== 'Not specified' ? user.position : '';
    document.getElementById('editIsAdmin').value = user.is_admin;

    const modal = document.getElementById('editModal');
    if (!modal) {
        console.error('Modal not found');
        return;
    }

    modal.style.display = 'block';
    modal.offsetHeight; // Force reflow
    requestAnimationFrame(() => {
        modal.classList.add('show');
    });

    const closeBtn = modal.querySelector('.close');
    closeBtn.onclick = closeEditModal;

    window.onclick = function(event) {
        if (event.target === modal) {
            closeEditModal();
        }
    }

    const form = document.getElementById('editUserForm');
    form.onsubmit = function(e) {
        e.preventDefault();
        updateUser(userId);
    }
}

function closeEditModal() {
    const modal = document.getElementById('editModal');
    modal.classList.remove('show');
    setTimeout(() => {
        modal.style.display = 'none';
    }, 300);
}

function updateUser(userId) {
    const formData = new FormData();
    formData.append('user_id', userId);
    formData.append('full_name', document.getElementById('editFullName').value);
    formData.append('email', document.getElementById('editEmail').value);
    formData.append('position', document.getElementById('editPosition').value);
    formData.append('is_admin', document.getElementById('editIsAdmin').value);

    fetch('../model/update_user.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            document.getElementById('editModal').style.display = 'none';
            loadUsers();
            alert('User updated successfully');
        } else {
            alert('Error updating user: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error updating user');
    });
}

function deleteUser(userId) {
    if (confirm('Are you sure you want to delete this user?')) {
        const formData = new FormData();
        formData.append('user_id', userId);

        fetch('../model/delete_user.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                loadUsers();
                alert('User deleted successfully');
            } else {
                alert('Error deleting user: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error deleting user');
        });
    }
}