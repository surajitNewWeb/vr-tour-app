// Main JavaScript for VR Tour Application - Frontend

document.addEventListener('DOMContentLoaded', function() {
    // Initialize tooltips
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    const tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });

    // Initialize popovers
    const popoverTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="popover"]'));
    const popoverList = popoverTriggerList.map(function (popoverTriggerEl) {
        return new bootstrap.Popover(popoverTriggerEl);
    });

    // Mobile menu toggle
    const mobileMenuToggle = document.querySelector('.navbar-toggler');
    if (mobileMenuToggle) {
        mobileMenuToggle.addEventListener('click', function() {
            document.body.classList.toggle('mobile-menu-open');
        });
    }

    // Search functionality
    const searchForm = document.querySelector('.search-form');
    if (searchForm) {
        searchForm.addEventListener('submit', function(e) {
            e.preventDefault();
            const searchInput = this.querySelector('input[type="search"]');
            const searchTerm = searchInput.value.trim();
            
            if (searchTerm.length > 2) {
                // Implement search functionality
                console.log('Searching for:', searchTerm);
                // window.location.href = `tours.php?search=${encodeURIComponent(searchTerm)}`;
            } else {
                // Show error
                searchInput.focus();
            }
        });
    }

    // Tour card interactions
    const tourCards = document.querySelectorAll('.tour-card');
    tourCards.forEach(card => {
        card.addEventListener('click', function(e) {
            if (!e.target.closest('.btn')) {
                const tourId = this.dataset.tourId;
                window.location.href = `tour.php?id=${tourId}`;
            }
        });
    });

    // Favorite button functionality
    const favoriteButtons = document.querySelectorAll('.favorite-btn');
    favoriteButtons.forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            
            const tourId = this.dataset.tourId;
            const isFavorite = this.classList.contains('active');
            
            // Toggle visual state
            this.classList.toggle('active');
            
            // Update icon
            const icon = this.querySelector('i');
            if (icon) {
                if (this.classList.contains('active')) {
                    icon.classList.remove('far');
                    icon.classList.add('fas');
                } else {
                    icon.classList.remove('fas');
                    icon.classList.add('far');
                }
            }
            
            // Send AJAX request to update favorites
            toggleFavorite(tourId, !isFavorite);
        });
    });

    // VR experience initialization
    initVRExperience();

    // Image lazy loading
    initLazyLoading();

    // Form validation
    initFormValidation();
});

// Toggle favorite status
function toggleFavorite(tourId, isFavorite) {
    // Check if user is logged in
    if (typeof isUserLoggedIn === 'undefined' || !isUserLoggedIn) {
        showLoginPrompt();
        return;
    }

    // Send AJAX request
    fetch('../includes/favorites.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `tour_id=${tourId}&action=${isFavorite ? 'add' : 'remove'}`
    })
    .then(response => response.json())
    .then(data => {
        if (!data.success) {
            // Revert visual state if failed
            const btn = document.querySelector(`.favorite-btn[data-tour-id="${tourId}"]`);
            if (btn) {
                btn.classList.toggle('active');
                const icon = btn.querySelector('i');
                if (icon) {
                    if (btn.classList.contains('active')) {
                        icon.classList.remove('far');
                        icon.classList.add('fas');
                    } else {
                        icon.classList.remove('fas');
                        icon.classList.add('far');
                    }
                }
            }
            showToast('Error updating favorites', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showToast('Network error', 'error');
    });
}

// Initialize VR experience
function initVRExperience() {
    // Check if we're on a VR page
    if (document.querySelector('a-scene')) {
        // Add loading indicator
        const scene = document.querySelector('a-scene');
        scene.addEventListener('loaded', function() {
            document.getElementById('vr-loading').style.display = 'none';
        });

        // Add keyboard controls for non-VR users
        document.addEventListener('keydown', function(e) {
            const camera = document.querySelector('[camera]');
            if (!camera) return;

            const position = camera.getAttribute('position');
            let newPosition = {x: position.x, y: position.y, z: position.z};
            const rotation = camera.getAttribute('rotation');

            switch(e.key) {
                case 'ArrowUp':
                    newPosition.x += Math.sin(rotation.y * Math.PI / 180) * 0.5;
                    newPosition.z += Math.cos(rotation.y * Math.PI / 180) * 0.5;
                    break;
                case 'ArrowDown':
                    newPosition.x -= Math.sin(rotation.y * Math.PI / 180) * 0.5;
                    newPosition.z -= Math.cos(rotation.y * Math.PI / 180) * 0.5;
                    break;
                case 'ArrowLeft':
                    newPosition.x += Math.sin((rotation.y - 90) * Math.PI / 180) * 0.5;
                    newPosition.z += Math.cos((rotation.y - 90) * Math.PI / 180) * 0.5;
                    break;
                case 'ArrowRight':
                    newPosition.x += Math.sin((rotation.y + 90) * Math.PI / 180) * 0.5;
                    newPosition.z += Math.cos((rotation.y + 90) * Math.PI / 180) * 0.5;
                    break;
                default:
                    return;
            }

            camera.setAttribute('position', newPosition);
        });
    }
}

// Initialize lazy loading
function initLazyLoading() {
    if ('IntersectionObserver' in window) {
        const lazyImages = document.querySelectorAll('img.lazy');
        
        const imageObserver = new IntersectionObserver((entries, observer) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    const img = entry.target;
                    img.src = img.dataset.src;
                    img.classList.remove('lazy');
                    imageObserver.unobserve(img);
                }
            });
        });

        lazyImages.forEach(img => {
            imageObserver.observe(img);
        });
    }
}

// Initialize form validation
function initFormValidation() {
    // Select all forms with validation needs
    const forms = document.querySelectorAll('form[novalidate]');
    
    forms.forEach(form => {
        form.addEventListener('submit', function(e) {
            if (!this.checkValidity()) {
                e.preventDefault();
                e.stopPropagation();
            }
            
            this.classList.add('was-validated');
        });
    });
}

// Show toast notification
function showToast(message, type = 'info') {
    // Create toast container if it doesn't exist
    let toastContainer = document.getElementById('toast-container');
    if (!toastContainer) {
        toastContainer = document.createElement('div');
        toastContainer.id = 'toast-container';
        toastContainer.className = 'toast-container position-fixed top-0 end-0 p-3';
        document.body.appendChild(toastContainer);
    }

    // Create toast
    const toastEl = document.createElement('div');
    toastEl.className = `toast align-items-center text-white bg-${type} border-0`;
    toastEl.setAttribute('role', 'alert');
    toastEl.setAttribute('aria-live', 'assertive');
    toastEl.setAttribute('aria-atomic', 'true');
    
    toastEl.innerHTML = `
        <div class="d-flex">
            <div class="toast-body">
                ${message}
            </div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
        </div>
    `;

    toastContainer.appendChild(toastEl);
    
    // Show toast
    const toast = new bootstrap.Toast(toastEl);
    toast.show();
    
    // Remove toast after it's hidden
    toastEl.addEventListener('hidden.bs.toast', function() {
        toastEl.remove();
    });
}

// Show login prompt
function showLoginPrompt() {
    const loginModal = new bootstrap.Modal(document.getElementById('loginModal'));
    loginModal.show();
}

// Debounce function for search and resize events
function debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
}

// Format tour duration
function formatDuration(minutes) {
    if (minutes < 60) {
        return `${minutes} min`;
    } else {
        const hours = Math.floor(minutes / 60);
        const mins = minutes % 60;
        return mins > 0 ? `${hours}h ${mins}m` : `${hours}h`;
    }
}

// Get URL parameters
function getUrlParams() {
    const params = {};
    const queryString = window.location.search;
    const urlParams = new URLSearchParams(queryString);
    
    for (const [key, value] of urlParams) {
        params[key] = value;
    }
    
    return params;
}

// Scroll to top function
function scrollToTop() {
    window.scrollTo({
        top: 0,
        behavior: 'smooth'
    });
}

// Add scroll to top button
function initScrollToTop() {
    const scrollButton = document.createElement('button');
    scrollButton.id = 'scroll-to-top';
    scrollButton.className = 'btn btn-primary scroll-to-top';
    scrollButton.innerHTML = '<i class="fas fa-chevron-up"></i>';
    scrollButton.addEventListener('click', scrollToTop);
    document.body.appendChild(scrollButton);

    // Show/hide based on scroll position
    window.addEventListener('scroll', debounce(function() {
        if (window.pageYOffset > 300) {
            scrollButton.classList.add('visible');
        } else {
            scrollButton.classList.remove('visible');
        }
    }, 100));
}

// Initialize when DOM is loaded
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initScrollToTop);
} else {
    initScrollToTop();
}