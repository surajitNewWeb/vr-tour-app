// assets/js/admin.js

// Sidebar Toggle functionality
document.addEventListener('DOMContentLoaded', function() {
    // Toggle sidebar on button click
    const sidebarToggle = document.querySelector('#sidebarToggle');
    if (sidebarToggle) {
        sidebarToggle.addEventListener('click', function(e) {
            e.preventDefault();
            document.querySelector('body').classList.toggle('sidebar-toggled');
            document.querySelector('.sidebar').classList.toggle('toggled');
            
            if (document.querySelector('.sidebar').classList.contains('toggled')) {
                document.querySelector('.sidebar .collapse').classList.remove('show');
            }
        });
    }
    
    // Close sidebar when clicking outside on mobile
    window.addEventListener('resize', function() {
        if (window.innerWidth < 768) {
            document.querySelector('.sidebar .collapse').classList.remove('show');
        }
    });
    
    // Prevent the content wrapper from scrolling when the fixed side navigation is in use
    const fixedNav = document.querySelector('body.fixed-nav .sidebar');
    if (fixedNav) {
        fixedNav.on('mousewheel DOMMouseScroll wheel', function(e) {
            if (window.innerWidth > 768) {
                const e0 = e.originalEvent;
                const delta = e0.wheelDelta || -e0.detail;
                this.scrollTop += (delta < 0 ? 1 : -1) * 30;
                e.preventDefault();
            }
        });
    }
    
    // Scroll to top button appear
    window.addEventListener('scroll', function() {
        const scrollDistance = window.scrollY;
        const scrollToTop = document.querySelector('.scroll-to-top');
        
        if (scrollToTop) {
            if (scrollDistance > 100) {
                scrollToTop.style.display = 'block';
            } else {
                scrollToTop.style.display = 'none';
            }
        }
    });
    
    // Smooth scrolling using jQuery easing
    document.querySelectorAll('a.scroll-to-top').forEach(anchor => {
        anchor.addEventListener('click', function(e) {
            e.preventDefault();
            const target = document.querySelector(this.getAttribute('href'));
            if (target) {
                window.scrollTo({
                    top: target.offsetTop,
                    behavior: 'smooth'
                });
            }
        });
    });
    
    // Initialize tooltips
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.map(function(tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
    
    // Initialize popovers
    const popoverTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="popover"]'));
    popoverTriggerList.map(function(popoverTriggerEl) {
        return new bootstrap.Popover(popoverTriggerEl);
    });
});

// Chart.js default configuration
Chart.defaults.global.defaultFontFamily = 'Nunito, -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif';
Chart.defaults.global.defaultFontColor = '#858796';