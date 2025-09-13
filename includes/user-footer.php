<?php
// includes/user-footer.php
?>
</main>

<!-- Footer -->
<footer class="footer">
    <div class="footer-content">
        <div class="footer-section">
            <div class="footer-brand">
                <i class="fas fa-vr-cardboard"></i>
                <span>VR Tours</span>
            </div>
            <p class="footer-description">
                Experience the world through immersive virtual reality tours. Explore museums, landmarks, and cultural
                heritage sites from anywhere.
            </p>
            <div class="footer-social">
                <a href="#" class="social-link" aria-label="Facebook">
                    <i class="fab fa-facebook-f"></i>
                </a>
                <a href="#" class="social-link" aria-label="Twitter">
                    <i class="fab fa-twitter"></i>
                </a>
                <a href="#" class="social-link" aria-label="Instagram">
                    <i class="fab fa-instagram"></i>
                </a>
                <a href="#" class="social-link" aria-label="YouTube">
                    <i class="fab fa-youtube"></i>
                </a>
            </div>
        </div>

        <div class="footer-section">
            <h3>Explore</h3>
            <ul class="footer-links">
                <li><a href="tours.php">Browse Tours</a></li>
                <li><a href="categories.php">Categories</a></li>
                <li><a href="about.php">About Us</a></li>
                <li><a href="contact.php">Contact</a></li>
            </ul>
        </div>

        <div class="footer-section">
            <h3>Support</h3>
            <ul class="footer-links">
                <li><a href="help.php">Help Center</a></li>
                <li><a href="faq.php">FAQ</a></li>
                <li><a href="privacy.php">Privacy Policy</a></li>
                <li><a href="terms.php">Terms of Service</a></li>
            </ul>
        </div>

        <div class="footer-section">
            <h3>Newsletter</h3>
            <p>Subscribe to get updates on new tours and features</p>
            <form class="footer-newsletter" id="newsletter-form">
                <div class="control">
                    <input type="email" placeholder="Your email" required>
                </div>
                <div class="control">
                    <button type="submit">
                        <i class="fas fa-paper-plane"></i>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <div class="footer-bottom">
        <div class="footer-copyright">
            <p>&copy;
                <?php echo date('Y'); ?> VR Tour Application. All rights reserved.
            </p>
        </div>
        <div class="footer-payments">
            <i class="fab fa-cc-visa" title="Visa"></i>
            <i class="fab fa-cc-mastercard" title="Mastercard"></i>
            <i class="fab fa-cc-paypal" title="PayPal"></i>
            <i class="fab fa-cc-apple-pay" title="Apple Pay"></i>
        </div>
    </div>
</footer>

<!-- JavaScript -->
<script>
    // Header and Footer functionality
    document.addEventListener('DOMContentLoaded', function () {
        // Navbar functionality
        const navbar = document.getElementById('navbar');
        const navbarBurger = document.getElementById('navbar-burger');
        const navbarMenu = document.getElementById('navbar-menu');

        // Toggle mobile menu
        if (navbarBurger && navbarMenu) {
            navbarBurger.addEventListener('click', function () {
                this.classList.toggle('active');
                navbarMenu.classList.toggle('active');
            });
        }

        // Close mobile menu when clicking outside
        document.addEventListener('click', function (event) {
            if (navbarMenu.classList.contains('active') &&
                !navbar.contains(event.target) &&
                !navbarBurger.contains(event.target)) {
                navbarBurger.classList.remove('active');
                navbarMenu.classList.remove('active');
            }
        });

        // Navbar scroll effect
        window.addEventListener('scroll', function () {
            if (window.scrollY > 50) {
                navbar.classList.add('scrolled');
            } else {
                navbar.classList.remove('scrolled');
            }
        });

        // Back to top button
        const backToTopButton = document.getElementById('back-to-top');

        if (backToTopButton) {
            window.addEventListener('scroll', function () {
                if (window.scrollY > 300) {
                    backToTopButton.classList.add('visible');
                } else {
                    backToTopButton.classList.remove('visible');
                }
            });

            backToTopButton.addEventListener('click', function () {
                window.scrollTo({
                    top: 0,
                    behavior: 'smooth'
                });
            });
        }

        // Newsletter form
        const newsletterForm = document.getElementById('newsletter-form');

        if (newsletterForm) {
            newsletterForm.addEventListener('submit', function (e) {
                e.preventDefault();
                const emailInput = this.querySelector('input[type="email"]');
                const email = emailInput.value.trim();

                if (email && isValidEmail(email)) {
                    // Simulate subscription success
                    showToast('Successfully subscribed to our newsletter!', 'success');
                    emailInput.value = '';
                } else {
                    showToast('Please enter a valid email address', 'error');
                }
            });
        }

        // Toast system
        window.showToast = function (message, type = 'info') {
            const toastContainer = document.getElementById('toast-container');
            if (!toastContainer) return;

            const toast = document.createElement('div');
            toast.className = `toast toast-${type}`;
            toast.innerHTML = `
                <i class="fas ${getToastIcon(type)} toast-icon"></i>
                <span class="toast-message">${message}</span>
                <button class="toast-close" onclick="this.parentElement.remove()">
                    <i class="fas fa-times"></i>
                </button>
            `;

            toastContainer.appendChild(toast);

            // Auto remove after 5 seconds
            setTimeout(() => {
                if (toast.parentElement) {
                    toast.remove();
                }
            }, 5000);
        };

        function getToastIcon(type) {
            const icons = {
                'success': 'fa-check-circle',
                'error': 'fa-exclamation-circle',
                'warning': 'fa-exclamation-triangle',
                'info': 'fa-info-circle'
            };
            return icons[type] || 'fa-info-circle';
        }

        function isValidEmail(email) {
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            return emailRegex.test(email);
        }

        // Loading spinner control
        window.showLoading = function () {
            const spinner = document.getElementById('loading-spinner');
            if (spinner) {
                spinner.style.display = 'flex';
            }
        };

        window.hideLoading = function () {
            const spinner = document.getElementById('loading-spinner');
            if (spinner) {
                spinner.style.display = 'none';
            }
        };

        // Initialize any animations
        initializeAnimations();
    });

    function initializeAnimations() {
        // Animate elements when they come into view
        const animatedElements = document.querySelectorAll('.feature-item, .stat-item, .tour-card');

        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('animate-in');
                    observer.unobserve(entry.target);
                }
            });
        }, {
            threshold: 0.1,
            rootMargin: '0px 0px -50px 0px'
        });

        animatedElements.forEach(element => {
            observer.observe(element);
        });
    }
</script>

<?php if (basename($_SERVER['PHP_SELF']) == 'tour.php'): ?>
<script src="<?php echo BASE_URL; ?>assets/js/vr-tour.js"></script>
<?php endif; ?>

<!-- Global site tag (gtag.js) - Google Analytics -->
<script async src="https://www.googletagmanager.com/gtag/js?id=GA_MEASUREMENT_ID"></script>
<script>
    window.dataLayer = window.dataLayer || [];
    function gtag() { dataLayer.push(arguments); }
    gtag('js', new Date());
    gtag('config', 'GA_MEASUREMENT_ID');
</script>
</body>

</html>