// index page


// assets/js/main.js
class VRApp {
    constructor() {
        this.init();
    }

    init() {
        this.setupEventListeners();
        this.initComponents();
        this.handleAuthState();
        this.checkPreferredTheme();
    }

    setupEventListeners() {
        // Window events
        window.addEventListener('scroll', this.handleScroll.bind(this));
        window.addEventListener('resize', this.debounce(this.handleResize.bind(this), 250));
        document.addEventListener('keydown', this.handleKeyboardShortcuts.bind(this));

        // Navigation
        document.addEventListener('click', this.handleNavigation.bind(this));
    }

    initComponents() {
        this.initBackToTop();
        this.initMobileNavigation();
        this.initDropdowns();
        this.initModals();
        this.initForms();
        this.initToasts();
        this.initLazyLoading();
    }

    handleAuthState() {
        const authElements = document.querySelectorAll('[data-auth]');
        authElements.forEach(el => {
            const requiresAuth = el.dataset.auth === 'required';
            const isLoggedIn = document.body.classList.contains('user-logged-in');
            
            if (requiresAuth && !isLoggedIn) {
                el.style.display = 'none';
            }
        });
    }

    checkPreferredTheme() {
        const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
        const savedTheme = localStorage.getItem('theme');
        
        if (savedTheme === 'dark' || (!savedTheme && prefersDark)) {
            document.documentElement.classList.add('dark');
        }
    }

    handleScroll() {
        this.toggleBackToTop();
        this.animateOnScroll();
    }

    handleResize() {
        this.handleMobileMenu();
    }

    handleKeyboardShortcuts(e) {
        // Ctrl/Cmd + K for search
        if ((e.ctrlKey || e.metaKey) && e.key === 'k') {
            e.preventDefault();
            this.focusSearch();
        }

        // Escape key
        if (e.key === 'Escape') {
            this.closeAllModals();
        }
    }

    handleNavigation(e) {
        const target = e.target;
        
        // Mobile menu toggle
        if (target.closest('[data-toggle="mobile-menu"]')) {
            e.preventDefault();
            this.toggleMobileMenu();
        }

        // Dropdown toggle
        if (target.closest('[data-toggle="dropdown"]')) {
            e.preventDefault();
            this.toggleDropdown(target.closest('[data-toggle="dropdown"]'));
        }

        // Modal toggle
        if (target.closest('[data-toggle="modal"]')) {
            e.preventDefault();
            this.openModal(target.closest('[data-toggle="modal"]').dataset.target);
        }
    }

    initBackToTop() {
        this.backToTopBtn = document.getElementById('back-to-top');
        if (this.backToTopBtn) {
            this.backToTopBtn.addEventListener('click', () => {
                window.scrollTo({ top: 0, behavior: 'smooth' });
            });
        }
    }

    toggleBackToTop() {
        if (this.backToTopBtn) {
            if (window.scrollY > 300) {
                this.backToTopBtn.classList.add('visible');
            } else {
                this.backToTopBtn.classList.remove('visible');
            }
        }
    }

    initMobileNavigation() {
        this.mobileMenu = document.getElementById('mobile-menu');
        this.menuToggle = document.querySelector('[data-toggle="mobile-menu"]');
    }

    toggleMobileMenu() {
        this.mobileMenu.classList.toggle('active');
        this.menuToggle.classList.toggle('active');
        
        // Prevent body scroll when menu is open
        document.body.classList.toggle('menu-open');
    }

    handleMobileMenu() {
        if (window.innerWidth > 768 && this.mobileMenu.classList.contains('active')) {
            this.mobileMenu.classList.remove('active');
            this.menuToggle.classList.remove('active');
            document.body.classList.remove('menu-open');
        }
    }

    initDropdowns() {
        this.dropdowns = document.querySelectorAll('.dropdown');
        this.dropdowns.forEach(dropdown => {
            dropdown.addEventListener('mouseenter', () => this.openDropdown(dropdown));
            dropdown.addEventListener('mouseleave', () => this.closeDropdown(dropdown));
        });
    }

    openDropdown(dropdown) {
        dropdown.classList.add('open');
    }

    closeDropdown(dropdown) {
        dropdown.classList.remove('open');
    }

    toggleDropdown(button) {
        const dropdown = button.closest('.dropdown');
        dropdown.classList.toggle('open');
    }

    initModals() {
        this.modals = document.querySelectorAll('.modal');
        this.modals.forEach(modal => {
            const closeBtn = modal.querySelector('.modal-close');
            if (closeBtn) {
                closeBtn.addEventListener('click', () => this.closeModal(modal));
            }
            
            // Close modal when clicking outside
            modal.addEventListener('click', (e) => {
                if (e.target === modal) {
                    this.closeModal(modal);
                }
            });
        });
    }

    openModal(modalId) {
        const modal = document.getElementById(modalId);
        if (modal) {
            modal.classList.add('active');
            document.body.classList.add('modal-open');
        }
    }

    closeModal(modal) {
        modal.classList.remove('active');
        document.body.classList.remove('modal-open');
    }

    closeAllModals() {
        this.modals.forEach(modal => this.closeModal(modal));
    }

    initForms() {
        this.forms = document.querySelectorAll('form[data-validate]');
        this.forms.forEach(form => {
            form.setAttribute('novalidate', 'true');
            form.addEventListener('submit', (e) => this.validateForm(e));
        });
    }

    validateForm(e) {
        const form = e.target;
        let isValid = true;

        // Clear previous errors
        form.querySelectorAll('.error-message').forEach(el => el.remove());
        form.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));

        // Validate required fields
        form.querySelectorAll('[required]').forEach(input => {
            if (!input.value.trim()) {
                this.showFieldError(input, 'This field is required');
                isValid = false;
            }
        });

        // Validate email fields
        form.querySelectorAll('input[type="email"]').forEach(input => {
            if (input.value && !this.isValidEmail(input.value)) {
                this.showFieldError(input, 'Please enter a valid email address');
                isValid = false;
            }
        });

        if (!isValid) {
            e.preventDefault();
            // Focus on first error
            const firstError = form.querySelector('.is-invalid');
            if (firstError) {
                firstError.focus();
            }
        } else {
            // Add loading state
            const submitBtn = form.querySelector('button[type="submit"]');
            if (submitBtn) {
                this.setLoadingState(submitBtn, true);
            }
        }
    }

    showFieldError(input, message) {
        input.classList.add('is-invalid');
        
        const errorDiv = document.createElement('div');
        errorDiv.className = 'error-message';
        errorDiv.textContent = message;
        
        input.parentNode.appendChild(errorDiv);
    }

    isValidEmail(email) {
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return emailRegex.test(email);
    }

    setLoadingState(button, isLoading) {
        if (isLoading) {
            button.disabled = true;
            button.innerHTML = '<span class="loading"></span> Processing...';
        } else {
            button.disabled = false;
            button.innerHTML = button.dataset.originalText;
        }
    }

    initToasts() {
        this.toastContainer = document.getElementById('toast-container') || this.createToastContainer();
        
        // Check for toast messages in session storage
        const toastMessage = sessionStorage.getItem('toastMessage');
        const toastType = sessionStorage.getItem('toastType');
        
        if (toastMessage) {
            this.showToast(toastMessage, toastType);
            sessionStorage.removeItem('toastMessage');
            sessionStorage.removeItem('toastType');
        }
    }

    createToastContainer() {
        const container = document.createElement('div');
        container.id = 'toast-container';
        container.className = 'toast-container';
        document.body.appendChild(container);
        return container;
    }

    showToast(message, type = 'info') {
        const toast = document.createElement('div');
        toast.className = `toast toast-${type}`;
        toast.innerHTML = `
            <div class="toast-content">
                <i class="toast-icon ${this.getToastIcon(type)}"></i>
                <span class="toast-message">${message}</span>
            </div>
            <button class="toast-close" onclick="this.parentElement.remove()">
                <i class="fas fa-times"></i>
            </button>
        `;
        
        this.toastContainer.appendChild(toast);
        
        // Auto remove after 5 seconds
        setTimeout(() => {
            if (toast.parentElement) {
                toast.remove();
            }
        }, 5000);
    }

    getToastIcon(type) {
        const icons = {
            success: 'fas fa-check-circle',
            error: 'fas fa-exclamation-circle',
            warning: 'fas fa-exclamation-triangle',
            info: 'fas fa-info-circle'
        };
        return icons[type] || icons.info;
    }

    initLazyLoading() {
        if ('IntersectionObserver' in window) {
            this.lazyObserver = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        const img = entry.target;
                        img.src = img.dataset.src;
                        img.classList.remove('lazy');
                        this.lazyObserver.unobserve(img);
                    }
                });
            });

            document.querySelectorAll('img.lazy').forEach(img => {
                this.lazyObserver.observe(img);
            });
        }
    }

    animateOnScroll() {
        const elements = document.querySelectorAll('.animate-on-scroll');
        elements.forEach(element => {
            const elementTop = element.getBoundingClientRect().top;
            const elementVisible = 150;
            
            if (elementTop < window.innerHeight - elementVisible) {
                element.classList.add('animated');
            }
        });
    }

    focusSearch() {
        const searchInput = document.querySelector('input[type="search"]');
        if (searchInput) {
            searchInput.focus();
        }
    }

    // Utility functions
    debounce(func, wait) {
        let timeout;
        return function executedFunction(...args) {
            const later = () => {
                clearTimeout(timeout);
                func(...args);
            };
            clearTimeout(timetimeout);
            timeout = setTimeout(later, wait);
        };
    }

    throttle(func, limit) {
        let inThrottle;
        return function() {
            const args = arguments;
            const context = this;
            if (!inThrottle) {
                func.apply(context, args);
                inThrottle = true;
                setTimeout(() => inThrottle = false, limit);
            }
        };
    }

    // VR specific functions
    initVRScene() {
        if (typeof AFRAME !== 'undefined') {
            this.setupVRHotspots();
            this.setupVRNavigation();
        }
    }

    setupVRHotspots() {
        const hotspots = document.querySelectorAll('.vr-hotspot');
        hotspots.forEach(hotspot => {
            hotspot.addEventListener('click', (e) => {
                const type = hotspot.dataset.type;
                const target = hotspot.dataset.target;
                const content = hotspot.dataset.content;
                
                this.handleHotspotClick(type, target, content);
            });
        });
    }

    handleHotspotClick(type, target, content) {
        switch (type) {
            case 'navigation':
                this.navigateToScene(target);
                break;
            case 'info':
                this.showInfoPanel(content);
                break;
            case 'media':
                this.playMedia(content);
                break;
        }
    }

    navigateToScene(sceneId) {
        // Implementation for scene navigation
        console.log('Navigating to scene:', sceneId);
    }

    showInfoPanel(content) {
        // Implementation for info panel
        console.log('Showing info:', content);
    }

    playMedia(mediaUrl) {
        // Implementation for media playback
        console.log('Playing media:', mediaUrl);
    }
}

// Initialize the application
document.addEventListener('DOMContentLoaded', function() {
    window.VRApp = new VRApp();
    
    // Initialize VR scene if on VR page
    if (document.querySelector('a-scene')) {
        window.VRApp.initVRScene();
    }
});

// Global functions for easy access
function showToast(message, type = 'info') {
    if (window.VRApp) {
        window.VRApp.showToast(message, type);
    }
}

function setLoading(button, isLoading) {
    if (window.VRApp) {
        window.VRApp.setLoadingState(button, isLoading);
    }
}