// Admin JavaScript for VR Tour Application

document.addEventListener('DOMContentLoaded', function() {
    // Initialize admin dashboard
    initAdminDashboard();
    
    // Initialize form validation
    initAdminForms();
    
    // Initialize data tables
    initDataTables();
    
    // Initialize image upload preview
    initImageUploadPreview();
    
    // Initialize chart.js if available
    initCharts();
    
    // Initialize tour management
    initTourManagement();
});

// Initialize admin dashboard
function initAdminDashboard() {
    // Dashboard specific functionality
    if (document.querySelector('.dashboard-page')) {
        // Update dashboard stats in real-time
        setInterval(updateDashboardStats, 30000);
        
        // Initialize quick actions
        initQuickActions();
    }
}

// Initialize admin forms
function initAdminForms() {
    // Enhanced form validation
    const adminForms = document.querySelectorAll('form');
    adminForms.forEach(form => {
        form.addEventListener('submit', function(e) {
            // Custom validation for admin forms
            if (!validateAdminForm(this)) {
                e.preventDefault();
                highlightInvalidFields(this);
            }
        });
    });

    // Initialize select2 for better dropdowns
    if (typeof $.fn.select2 !== 'undefined') {
        $('select[data-select2]').select2({
            theme: 'bootstrap-5',
            width: '100%'
        });
    }

    // Initialize date pickers
    if (typeof $.fn.datepicker !== 'undefined') {
        $('[data-datepicker]').datepicker({
            format: 'yyyy-mm-dd',
            autoclose: true,
            todayHighlight: true
        });
    }
}

// Validate admin form
function validateAdminForm(form) {
    let isValid = true;
    const requiredFields = form.querySelectorAll('[required]');
    
    requiredFields.forEach(field => {
        if (!field.value.trim()) {
            isValid = false;
        }
    });
    
    // Custom validation for specific field types
    const emailFields = form.querySelectorAll('input[type="email"]');
    emailFields.forEach(field => {
        if (field.value && !isValidEmail(field.value)) {
            isValid = false;
            field.classList.add('is-invalid');
        }
    });
    
    return isValid;
}

// Email validation
function isValidEmail(email) {
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return emailRegex.test(email);
}

// Highlight invalid fields
function highlightInvalidFields(form) {
    const invalidFields = form.querySelectorAll(':invalid');
    invalidFields.forEach(field => {
        field.classList.add('is-invalid');
    });
}

// Initialize data tables
function initDataTables() {
    if (typeof $.fn.DataTable !== 'undefined') {
        $('.data-table').DataTable({
            responsive: true,
            language: {
                search: "_INPUT_",
                searchPlaceholder: "Search...",
                lengthMenu: "_MENU_ records per page",
                info: "Showing _START_ to _END_ of _TOTAL_ entries",
                infoEmpty: "Showing 0 to 0 of 0 entries",
                infoFiltered: "(filtered from _MAX_ total entries)",
                paginate: {
                    first: "First",
                    last: "Last",
                    next: "Next",
                    previous: "Previous"
                }
            },
            dom: '<"row"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-6"f>>' +
                 '<"row"<"col-sm-12"tr>>' +
                 '<"row"<"col-sm-12 col-md-5"i><"col-sm-12 col-md-7"p>>',
            pageLength: 25,
            order: [[0, 'desc']]
        });
    }
}

// Initialize image upload preview
function initImageUploadPreview() {
    const imageInputs = document.querySelectorAll('input[type="file"][accept^="image/"]');
    
    imageInputs.forEach(input => {
        input.addEventListener('change', function(e) {
            const file = this.files[0];
            if (file) {
                const previewId = this.dataset.preview;
                if (previewId) {
                    const preview = document.getElementById(previewId);
                    if (preview) {
                        const reader = new FileReader();
                        reader.onload = function(e) {
                            preview.src = e.target.result;
                            preview.style.display = 'block';
                        };
                        reader.readAsDataURL(file);
                    }
                }
            }
        });
    });
}

// Initialize charts
function initCharts() {
    if (typeof Chart !== 'undefined') {
        // Visitors chart
        const visitorsCtx = document.getElementById('visitorsChart');
        if (visitorsCtx) {
            new Chart(visitorsCtx, {
                type: 'line',
                data: {
                    labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
                    datasets: [{
                        label: 'Monthly Visitors',
                        data: [1200, 1900, 1500, 2200, 1800, 2500, 3100, 2800, 3400, 2900, 3800, 4200],
                        borderColor: '#4e54c8',
                        tension: 0.3,
                        fill: true,
                        backgroundColor: 'rgba(78, 84, 200, 0.1)'
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: {
                            beginAtZero: true
                        }
                    }
                }
            });
        }

        // Categories chart
        const categoriesCtx = document.getElementById('categoriesChart');
        if (categoriesCtx) {
            new Chart(categoriesCtx, {
                type: 'doughnut',
                data: {
                    labels: ['City', 'Nature', 'Cultural', 'Historical'],
                    datasets: [{
                        data: [40, 30, 20, 10],
                        backgroundColor: [
                            '#4e54c8',
                            '#8f94fb',
                            '#ff6b6b',
                            '#ffc107'
                        ]
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'bottom'
                        }
                    }
                }
            });
        }
    }
}

// Initialize tour management
function initTourManagement() {
    // Tour status toggling
    const statusToggles = document.querySelectorAll('.tour-status-toggle');
    statusToggles.forEach(toggle => {
        toggle.addEventListener('change', function() {
            const tourId = this.dataset.tourId;
            const isPublished = this.checked;
            
            updateTourStatus(tourId, isPublished);
        });
    });

    // Featured tour toggling
    const featuredToggles = document.querySelectorAll('.tour-featured-toggle');
    featuredToggles.forEach(toggle => {
        toggle.addEventListener('change', function() {
            const tourId = this.dataset.tourId;
            const isFeatured = this.checked;
            
            updateTourFeatured(tourId, isFeatured);
        });
    });

    // Delete tour confirmation
    const deleteButtons = document.querySelectorAll('.delete-tour');
    deleteButtons.forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            const tourId = this.dataset.tourId;
            const tourTitle = this.dataset.tourTitle;
            
            showDeleteConfirmation(tourId, tourTitle);
        });
    });
}

// Update tour status
function updateTourStatus(tourId, isPublished) {
    fetch('update-tour-status.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `tour_id=${tourId}&published=${isPublished ? 1 : 0}`
    })
    .then(response => response.json())
    .then(data => {
        if (!data.success) {
            // Revert toggle state
            const toggle = document.querySelector(`.tour-status-toggle[data-tour-id="${tourId}"]`);
            if (toggle) {
                toggle.checked = !isPublished;
            }
            showToast('Error updating tour status', 'error');
        } else {
            showToast(`Tour ${isPublished ? 'published' : 'unpublished'} successfully`, 'success');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showToast('Network error', 'error');
    });
}

// Update tour featured status
function updateTourFeatured(tourId, isFeatured) {
    fetch('update-tour-featured.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `tour_id=${tourId}&featured=${isFeatured ? 1 : 0}`
    })
    .then(response => response.json())
    .then(data => {
        if (!data.success) {
            // Revert toggle state
            const toggle = document.querySelector(`.tour-featured-toggle[data-tour-id="${tourId}"]`);
            if (toggle) {
                toggle.checked = !isFeatured;
            }
            showToast('Error updating featured status', 'error');
        } else {
            showToast(`Tour ${isFeatured ? 'added to' : 'removed from'} featured`, 'success');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showToast('Network error', 'error');
    });
}

// Show delete confirmation
function showDeleteConfirmation(tourId, tourTitle) {
    const modal = new bootstrap.Modal(document.getElementById('deleteConfirmationModal'));
    const modalElement = document.getElementById('deleteConfirmationModal');
    
    // Set tour title in confirmation message
    const messageElement = modalElement.querySelector('.confirmation-message');
    if (messageElement) {
        messageElement.textContent = `Are you sure you want to delete "${tourTitle}"? This action cannot be undone.`;
    }
    
    // Set up delete button
    const deleteButton = modalElement.querySelector('.confirm-delete');
    if (deleteButton) {
        deleteButton.onclick = function() {
            deleteTour(tourId);
            modal.hide();
        };
    }
    
    modal.show();
}

// Delete tour
function deleteTour(tourId) {
    fetch('delete-tour.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `tour_id=${tourId}`
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Remove tour row from table
            const tourRow = document.querySelector(`tr[data-tour-id="${tourId}"]`);
            if (tourRow) {
                tourRow.remove();
            }
            showToast('Tour deleted successfully', 'success');
        } else {
            showToast('Error deleting tour', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showToast('Network error', 'error');
    });
}

// Update dashboard stats
function updateDashboardStats() {
    fetch('get-dashboard-stats.php')
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Update stats cards
            if (data.stats.tours !== undefined) {
                updateStatCard('tours-stat', data.stats.tours);
            }
            if (data.stats.scenes !== undefined) {
                updateStatCard('scenes-stat', data.stats.scenes);
            }
            if (data.stats.users !== undefined) {
                updateStatCard('users-stat', data.stats.users);
            }
            if (data.stats.reviews !== undefined) {
                updateStatCard('reviews-stat', data.stats.reviews);
            }
        }
    })
    .catch(error => {
        console.error('Error updating stats:', error);
    });
}

// Update stat card with animation
function updateStatCard(cardId, newValue) {
    const card = document.getElementById(cardId);
    if (!card) return;
    
    const valueElement = card.querySelector('.stat-value');
    if (!valueElement) return;
    
    const oldValue = parseInt(valueElement.textContent);
    if (oldValue === newValue) return;
    
    // Animate value change
    animateValue(valueElement, oldValue, newValue, 1000);
}

// Animate value change
function animateValue(element, start, end, duration) {
    let startTimestamp = null;
    const step = (timestamp) => {
        if (!startTimestamp) startTimestamp = timestamp;
        const progress = Math.min((timestamp - startTimestamp) / duration, 1);
        const value = Math.floor(progress * (end - start) + start);
        element.textContent = value.toLocaleString();
        if (progress < 1) {
            window.requestAnimationFrame(step);
        }
    };
    window.requestAnimationFrame(step);
}

// Initialize quick actions
function initQuickActions() {
    const quickActionButtons = document.querySelectorAll('.quick-action');
    quickActionButtons.forEach(button => {
        button.addEventListener('click', function() {
            const action = this.dataset.action;
            executeQuickAction(action);
        });
    });
}

// Execute quick action
function executeQuickAction(action) {
    switch(action) {
        case 'new-tour':
            window.location.href = 'manage-tours.php?action=create';
            break;
        case 'new-user':
            window.location.href = 'manage-users.php?action=create';
            break;
        case 'view-reports':
            window.location.href = 'reports.php';
            break;
        default:
            console.log('Unknown action:', action);
    }
}

// Show toast notification (admin version)
function showToast(message, type = 'info') {
    // Use same toast function as main.js or customize for admin
    const toast = {
        info: function(msg) { console.log('INFO:', msg); },
        success: function(msg) { console.log('SUCCESS:', msg); },
        error: function(msg) { console.log('ERROR:', msg); },
        warning: function(msg) { console.log('WARNING:', msg); }
    };
    
    if (toast[type]) {
        toast[type](message);
    }
    
    // You can implement a more sophisticated toast system here
    alert(`${type.toUpperCase()}: ${message}`);
}

// Export functions for global access
window.Admin = {
    updateTourStatus,
    updateTourFeatured,
    deleteTour,
    showToast
};