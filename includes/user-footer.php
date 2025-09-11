<?php
// includes/user-footer.php
?>
    </main>

    <!-- Footer -->
    <footer class="footer">
        <div class="footer-content">
            <div class="container">
                <div class="footer-grid">
                    <!-- Company Info -->
                    <div class="footer-section">
                        <div class="footer-brand">
                            <i class="fas fa-vr-cardboard"></i>
                            <span>VR Tour Application</span>
                        </div>
                        <p class="footer-description">
                            Explore immersive virtual reality experiences from around the world. 
                            Discover museums, landmarks, and hidden gems in stunning 360°.
                        </p>
                        <div class="social-links">
                            <a href="#" class="social-link" aria-label="Facebook">
                                <i class="fab fa-facebook-f"></i>
                            </a>
                            <a href="#" class="social-link" aria-label="Twitter">
                                <i class="fab fa-twitter"></i>
                            </a>
                            <a href="#" class="social-link" aria-label="Instagram">
                                <i class="fab fa-instagram"></i>
                            </a>
                            <a href="#" class="social-link" aria-label="LinkedIn">
                                <i class="fab fa-linkedin-in"></i>
                            </a>
                        </div>
                    </div>
                    
                    <!-- Explore Links -->
                    <div class="footer-section">
                        <h6 class="footer-title">Explore</h6>
                        <ul class="footer-links">
                            <li><a href="<?php echo $base_url; ?>tours.php">All Tours</a></li>
                            <li><a href="<?php echo $base_url; ?>categories.php">Categories</a></li>
                            <li><a href="<?php echo $base_url; ?>featured.php">Featured</a></li>
                            <li><a href="<?php echo $base_url; ?>new.php">New Tours</a></li>
                        </ul>
                    </div>
                    
                    <!-- Company Links -->
                    <div class="footer-section">
                        <h6 class="footer-title">Company</h6>
                        <ul class="footer-links">
                            <li><a href="<?php echo $base_url; ?>about.php">About Us</a></li>
                            <li><a href="<?php echo $base_url; ?>contact.php">Contact</a></li>
                            <li><a href="<?php echo $base_url; ?>blog.php">Blog</a></li>
                            <li><a href="<?php echo $base_url; ?>careers.php">Careers</a></li>
                        </ul>
                    </div>
                    
                    <!-- Newsletter -->
                    <div class="footer-section newsletter-section">
                        <h6 class="footer-title">Stay Updated</h6>
                        <p class="newsletter-description">Subscribe to our newsletter for the latest tours and updates.</p>
                        <form class="newsletter-form" onsubmit="handleNewsletterSubmit(event)">
                            <div class="newsletter-input-group">
                                <input type="email" placeholder="Enter your email" required class="newsletter-input">
                                <button type="submit" class="newsletter-btn">
                                    <span>Subscribe</span>
                                    <i class="fas fa-paper-plane"></i>
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
                
                <div class="footer-divider"></div>
                
                <div class="footer-bottom">
                    <div class="copyright">
                        <p>&copy; <?php echo date('Y'); ?> VR Tour Application. All rights reserved.</p>
                    </div>
                    <div class="legal-links">
                        <a href="<?php echo $base_url; ?>privacy.php">Privacy Policy</a>
                        <a href="<?php echo $base_url; ?>terms.php">Terms of Service</a>
                        <a href="<?php echo $base_url; ?>cookies.php">Cookie Policy</a>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Footer Background Pattern -->
        <div class="footer-pattern"></div>
    </footer>

    <!-- Back to Top Button -->
    <button id="back-to-top" class="back-to-top" aria-label="Back to top">
        <i class="fas fa-arrow-up"></i>
        <span class="back-to-top-text">Top</span>
    </button>

    <!-- JavaScript Libraries -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Main JavaScript -->
    <script src="<?php echo $base_url; ?>assets/js/main.js"></script>

    <!-- Footer Styles -->
    <style>
        /* Footer Styles */
        .footer {
            background: linear-gradient(135deg, #1a202c 0%, #2d3748 50%, #2c5282 100%);
            color: #f7fafc;
            margin-top: 4rem;
            position: relative;
            overflow: hidden;
        }

        .footer::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%23ffffff' fill-opacity='0.02'%3E%3Cpath d='m36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E") repeat;
            opacity: 0.3;
        }

        .footer-content {
            position: relative;
            z-index: 1;
        }

        .footer-pattern {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(45deg, transparent 30%, rgba(102, 126, 234, 0.1) 50%, transparent 70%);
            pointer-events: none;
        }

        .container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 0 24px;
        }

        .footer-grid {
            display: grid;
            grid-template-columns: 2fr 1fr 1fr 1.5fr;
            gap: 3rem;
            padding: 4rem 0 2rem;
        }

        .footer-section {
            position: relative;
        }

        /* Footer Brand */
        .footer-brand {
            display: flex;
            align-items: center;
            margin-bottom: 1.5rem;
            font-family: 'Space Grotesk', sans-serif;
            font-weight: 700;
            font-size: 1.5rem;
        }

        .footer-brand i {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            -webkit-background-clip: text;
            background-clip: text;
            -webkit-text-fill-color: transparent;
            font-size: 2rem;
            margin-right: 0.75rem;
        }

        .footer-brand span {
            background: linear-gradient(135deg, #f7fafc 0%, #e2e8f0 100%);
            -webkit-background-clip: text;
            background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .footer-description {
            color: #a0aec0;
            line-height: 1.7;
            margin-bottom: 2rem;
            font-size: 1rem;
        }

        /* Social Links */
        .social-links {
            display: flex;
            gap: 1rem;
        }

        .social-link {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 48px;
            height: 48px;
            background: rgba(255, 255, 255, 0.1);
            color: #a0aec0;
            border-radius: 12px;
            text-decoration: none;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            position: relative;
            overflow: hidden;
        }

        .social-link::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            transition: all 0.3s ease;
            z-index: -1;
        }

        .social-link:hover {
            color: #ffffff;
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(102, 126, 234, 0.3);
        }

        .social-link:hover::before {
            left: 0;
        }

        /* Footer Titles */
        .footer-title {
            color: #f7fafc;
            font-weight: 600;
            font-size: 1.1rem;
            margin-bottom: 1.5rem;
            position: relative;
            font-family: 'Space Grotesk', sans-serif;
        }

        .footer-title::after {
            content: '';
            position: absolute;
            bottom: -8px;
            left: 0;
            width: 30px;
            height: 3px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 2px;
        }

        /* Footer Links */
        .footer-links {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .footer-links li {
            margin-bottom: 0.75rem;
        }

        .footer-links a {
            color: #a0aec0;
            text-decoration: none;
            font-weight: 500;
            transition: all 0.3s ease;
            position: relative;
            display: inline-block;
            padding: 4px 0;
        }

        .footer-links a::before {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            width: 0;
            height: 2px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            transition: width 0.3s ease;
        }

        .footer-links a:hover {
            color: #ffffff;
            transform: translateX(4px);
        }

        .footer-links a:hover::before {
            width: 100%;
        }

        /* Newsletter Section */
        .newsletter-section {
            background: rgba(255, 255, 255, 0.05);
            padding: 2rem;
            border-radius: 16px;
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.1);
        }

        .newsletter-description {
            color: #a0aec0;
            margin-bottom: 1.5rem;
            font-size: 0.95rem;
            line-height: 1.6;
        }

        .newsletter-form {
            position: relative;
        }

        .newsletter-input-group {
            display: flex;
            gap: 0.5rem;
            flex-wrap: wrap;
        }

        .newsletter-input {
            flex: 1;
            padding: 14px 18px;
            background: rgba(255, 255, 255, 0.1);
            border: 2px solid rgba(255, 255, 255, 0.2);
            border-radius: 12px;
            color: #f7fafc;
            font-size: 1rem;
            font-weight: 500;
            backdrop-filter: blur(10px);
            transition: all 0.3s ease;
            min-width: 200px;
        }

        .newsletter-input::placeholder {
            color: #a0aec0;
        }

        .newsletter-input:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.2);
            background: rgba(255, 255, 255, 0.15);
        }

        .newsletter-btn {
            padding: 14px 24px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            border-radius: 12px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            min-width: 120px;
            justify-content: center;
        }

        .newsletter-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(102, 126, 234, 0.4);
            filter: brightness(1.1);
        }

        .newsletter-btn:active {
            transform: translateY(0);
        }

        /* Footer Bottom */
        .footer-divider {
            height: 1px;
            background: linear-gradient(90deg, transparent 0%, rgba(255, 255, 255, 0.2) 50%, transparent 100%);
            margin: 2rem 0;
        }

        .footer-bottom {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 2rem 0;
            flex-wrap: wrap;
            gap: 1rem;
        }

        .copyright {
            color: #a0aec0;
            font-size: 0.95rem;
        }

        .legal-links {
            display: flex;
            gap: 2rem;
            flex-wrap: wrap;
        }

        .legal-links a {
            color: #a0aec0;
            text-decoration: none;
            font-weight: 500;
            transition: all 0.3s ease;
            position: relative;
        }

        .legal-links a::after {
            content: '';
            position: absolute;
            bottom: -2px;
            left: 0;
            width: 0;
            height: 1px;
            background: #667eea;
            transition: width 0.3s ease;
        }

        .legal-links a:hover {
            color: #ffffff;
        }

        .legal-links a:hover::after {
            width: 100%;
        }

        /* Back to Top Button */
        .back-to-top {
            position: fixed;
            bottom: 2rem;
            right: 2rem;
            width: 56px;
            height: 56px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            border-radius: 50%;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            gap: 2px;
            font-size: 0.9rem;
            font-weight: 600;
            z-index: 1000;
            opacity: 0;
            visibility: hidden;
            transform: scale(0.8);
            box-shadow: 0 4px 20px rgba(102, 126, 234, 0.3);
        }

        .back-to-top.visible {
            opacity: 1;
            visibility: visible;
            transform: scale(1);
        }

        .back-to-top:hover {
            transform: scale(1.1) translateY(-2px);
            box-shadow: 0 8px 30px rgba(102, 126, 234, 0.4);
        }

        .back-to-top-text {
            font-size: 0.7rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        /* Responsive Design */
        @media (max-width: 1024px) {
            .footer-grid {
                grid-template-columns: 1fr 1fr;
                gap: 2rem;
            }
            
            .newsletter-section {
                grid-column: 1 / -1;
            }
        }

        @media (max-width: 768px) {
            .container {
                padding: 0 16px;
            }
            
            .footer-grid {
                grid-template-columns: 1fr;
                gap: 2rem;
                padding: 3rem 0 1.5rem;
            }
            
            .footer-bottom {
                flex-direction: column;
                text-align: center;
                gap: 1.5rem;
            }
            
            .legal-links {
                justify-content: center;
                gap: 1.5rem;
            }
            
            .newsletter-input-group {
                flex-direction: column;
            }
            
            .newsletter-input {
                min-width: auto;
            }
            
            .social-links {
                justify-content: center;
            }
            
            .back-to-top {
                bottom: 1.5rem;
                right: 1.5rem;
                width: 48px;
                height: 48px;
            }
            
            .back-to-top-text {
                display: none;
            }
        }

        @media (max-width: 480px) {
            .footer-grid {
                padding: 2rem 0 1rem;
            }
            
            .newsletter-section {
                padding: 1.5rem;
            }
            
            .footer-brand {
                font-size: 1.25rem;
                justify-content: center;
                text-align: center;
            }
            
            .footer-description {
                text-align: center;
            }
            
            .footer-title {
                text-align: center;
            }
            
            .footer-links {
                text-align: center;
            }
            
            .legal-links {
                flex-direction: column;
                gap: 1rem;
            }
        }

        /* Animation for newsletter success */
        @keyframes successPulse {
            0% { transform: scale(1); }
            50% { transform: scale(1.05); }
            100% { transform: scale(1); }
        }

        .newsletter-btn.success {
            background: linear-gradient(135deg, #48bb78 0%, #38a169 100%);
            animation: successPulse 0.6s ease;
        }

        /* High Contrast Mode Support */
        @media (prefers-contrast: high) {
            .footer {
                background: #000;
                border-top: 3px solid #fff;
            }
            
            .footer-brand span,
            .footer-title,
            .copyright {
                color: #fff;
            }
        }

        /* Reduced Motion Support */
        @media (prefers-reduced-motion: reduce) {
            .social-link,
            .footer-links a,
            .newsletter-btn,
            .back-to-top {
                transition: none;
            }
            
            .back-to-top:hover {
                transform: none;
            }
        }

        /* Print Styles */
        @media print {
            .footer,
            .back-to-top {
                display: none;
            }
        }
    </style>
    
    <!-- Footer JavaScript -->
    <script>
        // Newsletter form handler
        function handleNewsletterSubmit(event) {
            event.preventDefault();
            const form = event.target;
            const btn = form.querySelector('.newsletter-btn');
            const input = form.querySelector('.newsletter-input');
            
            // Add loading state
            btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i><span>Subscribing...</span>';
            btn.disabled = true;
            
            // Simulate API call
            setTimeout(() => {
                btn.innerHTML = '<i class="fas fa-check"></i><span>Subscribed!</span>';
                btn.classList.add('success');
                input.value = '';
                
                // Reset after 3 seconds
                setTimeout(() => {
                    btn.innerHTML = '<span>Subscribe</span><i class="fas fa-paper-plane"></i>';
                    btn.classList.remove('success');
                    btn.disabled = false;
                }, 3000);
            }, 2000);
        }

        // Back to top functionality
        document.addEventListener('DOMContentLoaded', function() {
            const backToTopBtn = document.getElementById('back-to-top');
            
            // Show/hide back to top button
            window.addEventListener('scroll', function() {
                if (window.pageYOffset > 300) {
                    backToTopBtn.classList.add('visible');
                } else {
                    backToTopBtn.classList.remove('visible');
                }
            });
            
            // Scroll to top
            backToTopBtn.addEventListener('click', function() {
                window.scrollTo({
                    top: 0,
                    behavior: 'smooth'
                });
            });
        });

        // Animate footer elements on scroll
        const observerOptions = {
            threshold: 0.1,
            rootMargin: '0px 0px -50px 0px'
        };

        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.style.opacity = '1';
                    entry.target.style.transform = 'translateY(0)';
                }
            });
        }, observerOptions);

        // Observe footer sections
        document.addEventListener('DOMContentLoaded', function() {
            const footerSections = document.querySelectorAll('.footer-section');
            footerSections.forEach(section => {
                section.style.opacity = '0';
                section.style.transform = 'translateY(30px)';
                section.style.transition = 'opacity 0.6s ease, transform 0.6s ease';
                observer.observe(section);
            });
        });
    </script>
    
    <!-- PWA Service Worker -->
    <script>
    if ('serviceWorker' in navigator) {
        navigator.serviceWorker.register('<?php echo $base_url; ?>sw.js')
            .then(() => console.log('Service Worker registered'))
            .catch(err => console.log('Service Worker registration failed: ', err));
    }
    </script>
    
    <!-- Performance Monitoring -->
    <script>
    window.addEventListener('load', function() {
        // Track page load time
        const loadTime = performance.now();
        console.log('Page loaded in', loadTime, 'ms');
        
        // Lazy load images
        const images = document.querySelectorAll('img[data-src]');
        const imageObserver = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    const img = entry.target;
                    img.src = img.dataset.src;
                    img.classList.add('loaded');
                    imageObserver.unobserve(img);
                }
            });
        });
        
        images.forEach(img => imageObserver.observe(img));
    });
    </script>
</body>
</html>