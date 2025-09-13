<?php
// register.php
require_once 'includes/config.php';
require_once 'includes/user-auth.php';
require_once 'includes/database.php';

// Check if user is already logged in and redirect
if (isUserLoggedIn()) {
    header("Location: user/dashboard.php");
    exit();
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    
    // Validate input
    $errors = [];
    
    if (empty($username)) {
        $errors[] = "Username is required.";
    }
    
    if (empty($email)) {
        $errors[] = "Email address is required.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Please enter a valid email address.";
    }
    
    if (empty($password)) {
        $errors[] = "Password is required.";
    } elseif (strlen($password) < 6) {
        $errors[] = "Password must be at least 6 characters long.";
    }
    
    // Validate passwords match
    if ($password !== $confirm_password) {
        $errors[] = "Passwords do not match.";
    }
    
    if (empty($errors)) {
        // Use the UserAuth class to register the user
        global $userAuth;
        
        if ($userAuth->register($username, $email, $password)) {
            // Registration successful, redirect to dashboard
            header("Location: user/dashboard.php");
            exit();
        } else {
            $errors[] = "Registration failed. Username or email may already exist.";
        }
    }
    
    if (!empty($errors)) {
        $_SESSION['user_error'] = implode("<br>", $errors);
    }
}

$page_title = "Register - VR Tour Application";
include 'includes/user-header.php';
?>

<style>
/* Professional Register Page Styles */
.auth-wrapper {
    min-height: 100vh;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    padding: 40px 20px;
    display: flex;
    align-items: center;
    justify-content: center;
}

.auth-container {
    background: rgba(255, 255, 255, 0.95);
    backdrop-filter: blur(20px);
    border-radius: 20px;
    box-shadow: 
        0 25px 50px rgba(0, 0, 0, 0.15),
        0 8px 16px rgba(0, 0, 0, 0.1);
    overflow: hidden;
    width: 100%;
    max-width: 480px;
    border: 1px solid rgba(255, 255, 255, 0.2);
}

.auth-header {
    background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%);
    color: white;
    text-align: center;
    padding: 40px 30px 30px;
    position: relative;
}

.auth-header::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%23ffffff' fill-opacity='0.05'%3E%3Ccircle cx='30' cy='30' r='4'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E") repeat;
    opacity: 0.3;
}

.auth-icon {
    width: 60px;
    height: 60px;
    background: rgba(255, 255, 255, 0.2);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 20px;
    font-size: 24px;
    color: white;
    position: relative;
    z-index: 1;
}

.auth-header h3 {
    font-size: 28px;
    font-weight: 700;
    margin: 0;
    position: relative;
    z-index: 1;
}

.auth-header p {
    font-size: 16px;
    opacity: 0.9;
    margin: 8px 0 0 0;
    position: relative;
    z-index: 1;
}

.auth-body {
    padding: 40px 30px;
}

/* Alert Styles */
.alert {
    padding: 16px 20px;
    border-radius: 12px;
    margin-bottom: 24px;
    font-size: 14px;
    font-weight: 500;
    border: none;
}

.alert-danger {
    background: #fef2f2;
    color: #dc2626;
    border-left: 4px solid #dc2626;
}

/* Form Styling */
.mb-3 {
    margin-bottom: 24px;
}

.form-label {
    display: block;
    font-weight: 600;
    font-size: 14px;
    color: #374151;
    margin-bottom: 8px;
    letter-spacing: 0.025em;
}

.form-control {
    width: 100%;
    padding: 16px 20px;
    border: 2px solid #e5e7eb;
    border-radius: 12px;
    font-size: 16px;
    font-weight: 400;
    background: #ffffff;
    transition: all 0.2s ease;
    color: #1f2937;
    font-family: inherit;
}

.form-control:focus {
    outline: none;
    border-color: #4f46e5;
    box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.1);
    background: #fefefe;
}

.form-control:hover {
    border-color: #d1d5db;
}

.form-text {
    font-size: 13px;
    color: #6b7280;
    margin-top: 6px;
    font-weight: 400;
}

/* Button Styling */
.auth-btn {
    width: 100%;
    background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%);
    color: white;
    border: none;
    padding: 16px 24px;
    border-radius: 12px;
    font-size: 16px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.2s ease;
    text-transform: none;
    letter-spacing: 0.025em;
    font-family: inherit;
}

.btn:hover {
    background: linear-gradient(135deg, #4338ca 0%, #6d28d9 100%);
    transform: translateY(-1px);
    box-shadow: 0 8px 25px rgba(79, 70, 229, 0.3);
}

.auth-btn:active {
    transform: translateY(0);
}

.auth-btn:focus {
    outline: none;
    box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.2);
}

/* Divider and Footer */
hr {
    border: none;
    height: 1px;
    background: #e5e7eb;
    margin: 30px 0;
}

.text-center {
    text-align: center;
}

.text-center p {
    color: #6b7280;
    font-size: 14px;
    font-weight: 400;
    margin: 0;
}

.text-center a {
    color: #4f46e5;
    text-decoration: none;
    font-weight: 600;
    transition: color 0.2s ease;
}

.text-center a:hover {
    color: #4338ca;
    text-decoration: underline;
}

/* Input Enhancement with Icons */
.input-group {
    position: relative;
}

.input-group .form-control {
    padding-left: 50px;
}

.input-icon {
    position: absolute;
    left: 16px;
    top: 50%;
    transform: translateY(-50%);
    color: #9ca3af;
    font-size: 16px;
    z-index: 2;
    pointer-events: none;
}

.form-control:focus + .input-icon {
    color: #4f46e5;
}

/* Password Strength Indicator */
.password-strength {
    margin-top: 8px;
    height: 4px;
    background: #e5e7eb;
    border-radius: 2px;
    overflow: hidden;
    opacity: 0;
    transition: opacity 0.2s ease;
}

.password-strength.show {
    opacity: 1;
}

.password-strength-fill {
    height: 100%;
    width: 0%;
    transition: all 0.3s ease;
    border-radius: 2px;
}

.password-strength.weak .password-strength-fill {
    width: 33%;
    background: #ef4444;
}

.password-strength.medium .password-strength-fill {
    width: 66%;
    background: #f59e0b;
}

.password-strength.strong .password-strength-fill {
    width: 100%;
    background: #10b981;
}

/* Loading State */
.btn.loading {
    position: relative;
    color: transparent;
}

.btn.loading::after {
    content: '';
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    width: 20px;
    height: 20px;
    border: 2px solid rgba(255, 255, 255, 0.3);
    border-top: 2px solid white;
    border-radius: 50%;
    animation: spin 1s linear infinite;
}

@keyframes spin {
    to { transform: translate(-50%, -50%) rotate(360deg); }
}

/* Responsive Design */
@media (max-width: 640px) {
    .auth-wrapper {
        padding: 20px 10px;
    }

    .auth-container {
        border-radius: 16px;
        max-width: 100%;
    }

    .auth-header {
        padding: 30px 20px 20px;
    }

    .auth-header h3 {
        font-size: 24px;
    }

    .auth-header p {
        font-size: 14px;
    }

    .auth-body {
        padding: 30px 20px;
    }

    .form-control {
        padding: 14px 16px;
        font-size: 16px;
    }

    .input-group .form-control {
        padding-left: 45px;
    }

    .input-icon {
        left: 14px;
        font-size: 14px;
    }
}

/* High Contrast Mode */
@media (prefers-contrast: high) {
    .auth-container {
        border: 2px solid #000;
        background: #fff;
    }

    .form-control {
        border: 2px solid #000;
    }

    .btn {
        background: #000;
        border: 2px solid #000;
    }
}

/* Reduced Motion */
@media (prefers-reduced-motion: reduce) {
    * {
        animation-duration: 0.01ms !important;
        animation-iteration-count: 1 !important;
        transition-duration: 0.01ms !important;
    }
}
</style>

<div class="auth-wrapper">
    <div class="auth-container">
        <div class="auth-header">
            <div class="auth-icon">
                <i class="fas fa-user-plus"></i>
            </div>
            <h3>Create Account</h3>
            <p>Join our VR tour platform today</p>
        </div>
        
        <div class="auth-body">
            <?php if (isset($_SESSION['user_error'])): ?>
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-circle" style="margin-right: 8px;"></i>
                    <?php echo $_SESSION['user_error']; unset($_SESSION['user_error']); ?>
                </div>
            <?php endif; ?>
            
            <form method="POST" action="" id="register-form">
                <div class="mb-3">
                    <label for="username" class="form-label">Username</label>
                    <div class="input-group">
                        <input type="text" class="form-control" id="username" name="username" 
                               value="<?php echo isset($_POST['username']) ? htmlspecialchars($_POST['username']) : ''; ?>" 
                               required autocomplete="username" placeholder="Choose a unique username">
                        <i class="fas fa-user input-icon"></i>
                    </div>
                </div>
                
                <div class="mb-3">
                    <label for="email" class="form-label">Email Address</label>
                    <div class="input-group">
                        <input type="email" class="form-control" id="email" name="email" 
                               value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>" 
                               required autocomplete="email" placeholder="your@email.com">
                        <i class="fas fa-envelope input-icon"></i>
                    </div>
                </div>
                
                <div class="mb-3">
                    <label for="password" class="form-label">Password</label>
                    <div class="input-group">
                        <input type="password" class="form-control" id="password" name="password" 
                               required autocomplete="new-password" placeholder="Create a strong password">
                        <i class="fas fa-lock input-icon"></i>
                    </div>
                    <small class="form-text text-muted">Minimum 6 characters</small>
                    <div class="password-strength" id="password-strength">
                        <div class="password-strength-fill"></div>
                    </div>
                </div>
                
                <div class="mb-3">
                    <label for="confirm_password" class="form-label">Confirm Password</label>
                    <div class="input-group">
                        <input type="password" class="form-control" id="confirm_password" name="confirm_password" 
                               required autocomplete="new-password" placeholder="Confirm your password">
                        <i class="fas fa-lock input-icon"></i>
                    </div>
                </div>
                
                <button type="submit" class="auth-btn btn btn-primary" id="submit-btn">Register</button>
            </form>
            
            <hr>
            <div class="text-center">
                <p>Already have an account? <a href="login.php">Login here</a></p>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('register-form');
    const passwordInput = document.getElementById('password');
    const confirmPasswordInput = document.getElementById('confirm_password');
    const passwordStrength = document.getElementById('password-strength');
    const submitBtn = document.getElementById('submit-btn');

    // Password strength checker
    if (passwordInput && passwordStrength) {
        passwordInput.addEventListener('input', function() {
            const password = this.value;
            const strength = getPasswordStrength(password);
            
            if (password.length > 0) {
                passwordStrength.classList.add('show');
                passwordStrength.className = `password-strength show ${strength}`;
            } else {
                passwordStrength.classList.remove('show');
            }
        });
    }

    // Password confirmation validation
    if (confirmPasswordInput) {
        confirmPasswordInput.addEventListener('input', function() {
            if (passwordInput.value !== this.value) {
                this.setCustomValidity('Passwords do not match');
            } else {
                this.setCustomValidity('');
            }
        });
    }

    // Form submission with loading state
    if (form && submitBtn) {
        form.addEventListener('submit', function(e) {
            const isValid = form.checkValidity();
            
            if (isValid) {
                submitBtn.classList.add('loading');
                submitBtn.disabled = true;
            }
        });
    }

    // Password strength function
    function getPasswordStrength(password) {
        let score = 0;
        
        if (password.length >= 6) score++;
        if (password.length >= 8) score++;
        if (/[A-Z]/.test(password)) score++;
        if (/[0-9]/.test(password)) score++;
        if (/[^A-Za-z0-9]/.test(password)) score++;
        
        if (score < 2) return 'weak';
        if (score < 4) return 'medium';
        return 'strong';
    }

    // Input validation styling
    const inputs = form.querySelectorAll('input[required]');
    inputs.forEach(input => {
        input.addEventListener('blur', function() {
            if (!this.checkValidity()) {
                this.style.borderColor = '#ef4444';
            } else {
                this.style.borderColor = '#10b981';
            }
        });

        input.addEventListener('input', function() {
            if (this.checkValidity()) {
                this.style.borderColor = '#e5e7eb';
            }
        });
    });
});
</script>

<?php include 'includes/user-footer.php'; ?>