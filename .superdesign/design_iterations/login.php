<?php
// =============================================
// NATURAL CLOTHING - LOGIN/REGISTER PAGE
// =============================================

require_once __DIR__ . '/api/Database.php';
require_once __DIR__ . '/config.php';

// Start session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Redirect if already logged in
if (isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true) {
    header('Location: index.php');
    exit;
}

// Initialize guest session if needed
if (!isset($_SESSION['user_id']) && !isset($_SESSION['guest_session_id'])) {
    $_SESSION['guest_session_id'] = uniqid('guest_', true);
}

// Initialize managers
$cartManager = new CartManager();

// Get cart count for header
$userId = $_SESSION['user_id'] ?? null;
$sessionId = $_SESSION['guest_session_id'] ?? null;
$cartCount = $cartManager->getCartItemCount($userId, $sessionId);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Natural</title>
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Playfair+Display:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    
    <!-- Lucide Icons -->
    <script src="https://unpkg.com/lucide@latest/dist/umd/lucide.min.js"></script>
    
    <!-- Custom Theme CSS -->
    <link rel="stylesheet" href="natural_theme_1.css">
    
    <style>
        .container-custom {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 1.5rem;
        }
        
        .form-container {
            max-width: 400px;
            margin: 0 auto;
            background: var(--card);
            border: 1px solid var(--border);
            border-radius: var(--radius-lg);
            padding: 2.5rem;
            box-shadow: var(--shadow-lg);
        }
        
        .form-input {
            width: 100%;
            padding: 0.875rem 1rem;
            border-radius: var(--radius-md);
            border: 1px solid var(--border);
            background: var(--background);
            color: var(--foreground);
            outline: none;
            transition: all 200ms ease-out;
            font-size: 0.875rem;
        }
        
        .form-input:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 2px rgba(134, 155, 121, 0.2);
        }
        
        .form-input.error {
            border-color: var(--destructive);
            box-shadow: 0 0 0 2px rgba(239, 68, 68, 0.2);
        }
        
        .form-label {
            display: block;
            font-weight: 500;
            margin-bottom: 0.5rem;
            color: var(--foreground);
            font-size: 0.875rem;
        }
        
        .form-group {
            margin-bottom: 1.5rem;
        }
        
        .btn-primary {
            width: 100%;
            padding: 0.875rem 1.5rem;
            border-radius: var(--radius-md);
            background: var(--primary);
            color: var(--primary-foreground);
            font-weight: 500;
            letter-spacing: 0.025em;
            transition: all 200ms ease-out;
            border: none;
            cursor: pointer;
            font-size: 0.875rem;
        }
        
        .btn-primary:hover:not(:disabled) {
            transform: scale(1.02);
            box-shadow: var(--shadow-md);
        }
        
        .btn-primary:disabled {
            opacity: 0.6;
            cursor: not-allowed;
        }
        
        .btn-secondary {
            width: 100%;
            padding: 0.875rem 1.5rem;
            border-radius: var(--radius-md);
            background: transparent;
            color: var(--primary);
            font-weight: 500;
            letter-spacing: 0.025em;
            transition: all 200ms ease-out;
            border: 1px solid var(--primary);
            cursor: pointer;
            font-size: 0.875rem;
        }
        
        .btn-secondary:hover {
            background: var(--primary);
            color: var(--primary-foreground);
        }
        
        .nav-link {
            position: relative;
            padding: 0.5rem 1rem;
            color: var(--foreground);
            text-decoration: none;
            transition: color 200ms ease-out;
        }
        
        .nav-link:hover {
            color: var(--primary);
        }
        
        .nav-link::after {
            content: '';
            position: absolute;
            width: 0;
            height: 2px;
            bottom: 0;
            left: 50%;
            background: var(--primary);
            transition: all 250ms ease-out;
        }
        
        .nav-link:hover::after {
            width: 100%;
            left: 0;
        }
        
        .tab-container {
            display: flex;
            border-bottom: 1px solid var(--border);
            margin-bottom: 2rem;
        }
        
        .tab-button {
            flex: 1;
            padding: 1rem;
            text-align: center;
            background: none;
            border: none;
            color: var(--muted-foreground);
            cursor: pointer;
            transition: all 200ms ease-out;
            font-weight: 500;
            position: relative;
        }
        
        .tab-button.active {
            color: var(--primary);
        }
        
        .tab-button.active::after {
            content: '';
            position: absolute;
            bottom: -1px;
            left: 0;
            right: 0;
            height: 2px;
            background: var(--primary);
        }
        
        .form-section {
            display: none;
        }
        
        .form-section.active {
            display: block;
            animation: fadeIn 0.3s ease-out;
        }
        
        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .error-message {
            color: var(--destructive);
            font-size: 0.875rem;
            margin-top: 0.5rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .success-message {
            color: var(--primary);
            font-size: 0.875rem;
            margin-top: 0.5rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .checkbox-group {
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .checkbox-group input[type="checkbox"] {
            width: 1rem;
            height: 1rem;
            accent-color: var(--primary);
        }
        
        .link-text {
            color: var(--primary);
            text-decoration: none;
            font-weight: 500;
        }
        
        .link-text:hover {
            text-decoration: underline;
        }
        
        .section-padding { 
            padding: clamp(3rem, 8vw, 6rem) 0; 
        }
        
        .loading-spinner {
            width: 1rem;
            height: 1rem;
            border: 2px solid transparent;
            border-top: 2px solid currentColor;
            border-radius: 50%;
            animation: spin 1s linear infinite;
            display: none;
        }
        
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        
        .password-field {
            position: relative;
        }
        
        .password-toggle {
            position: absolute;
            right: 0.75rem;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            color: var(--muted-foreground);
            cursor: pointer;
            padding: 0.25rem;
            border-radius: var(--radius-sm);
        }
        
        .password-toggle:hover {
            background: var(--muted);
        }
    </style>
</head>
<body style="font-family: var(--font-sans); background: var(--background); color: var(--foreground); line-height: 1.6;">

    <!-- Navigation Header -->
    <header class="sticky top-0 z-50" style="background: rgba(249, 250, 251, 0.95); backdrop-filter: blur(10px); border-bottom: 1px solid var(--border);">
        <nav class="container-custom py-4">
            <div class="flex items-center justify-between">
                <!-- Logo -->
                <div class="flex items-center">
                    <a href="index.php" class="text-2xl font-serif font-light natural-text-gradient">Natural</a>
                </div>
                
                <!-- Navigation Links -->
                <div class="hidden md:flex items-center space-x-8">
                    <a href="index.php" class="nav-link">Home</a>
                    <a href="collections.php" class="nav-link">Collections</a>
                    <a href="about.php" class="nav-link">About</a>
                    <a href="sustainability.php" class="nav-link">Sustainability</a>
                    <a href="contact.php" class="nav-link">Contact</a>
                </div>
                
                <!-- Icons -->
                <div class="flex items-center space-x-4">
                    <a href="cart.php" class="p-2 hover:bg-gray-100 rounded-full transition-colors relative">
                        <i data-lucide="shopping-bag" class="w-5 h-5"></i>
                        <span class="absolute -top-2 -right-2 bg-red-500 text-white text-xs w-5 h-5 rounded-full flex items-center justify-center" id="cart-count"><?php echo $cartCount; ?></span>
                    </a>
                    
                    <button class="md:hidden p-2 hover:bg-gray-100 rounded-full transition-colors">
                        <i data-lucide="menu" class="w-5 h-5"></i>
                    </button>
                </div>
            </div>
        </nav>
    </header>

    <!-- Main Content -->
    <main class="section-padding">
        <div class="container-custom">
            <!-- Back Link -->
            <div class="mb-8">
                <a href="index.php" class="link-text flex items-center gap-2">
                    <i data-lucide="arrow-left" class="w-4 h-4"></i>
                    Back to Home
                </a>
            </div>

            <!-- Form Container -->
            <div class="form-container">
                <!-- Tab Navigation -->
                <div class="tab-container">
                    <button class="tab-button active" onclick="switchTab('login')" id="login-tab">Login</button>
                    <button class="tab-button" onclick="switchTab('register')" id="register-tab">Register</button>
                </div>

                <!-- Login Form -->
                <div class="form-section active" id="login-section">
                    <h2 class="text-2xl font-serif font-light mb-6 text-center">Welcome Back</h2>
                    
                    <form id="login-form">
                        <div class="form-group">
                            <label for="login-email" class="form-label">Email Address</label>
                            <input 
                                type="email" 
                                id="login-email" 
                                name="email" 
                                class="form-input" 
                                placeholder="your@email.com"
                                required
                            >
                        </div>

                        <div class="form-group">
                            <label for="login-password" class="form-label">Password</label>
                            <div class="password-field">
                                <input 
                                    type="password" 
                                    id="login-password" 
                                    name="password" 
                                    class="form-input" 
                                    placeholder="Enter your password"
                                    required
                                >
                                <button type="button" class="password-toggle" onclick="togglePassword('login-password')">
                                    <i data-lucide="eye" class="w-4 h-4"></i>
                                </button>
                            </div>
                        </div>

                        <div class="form-group">
                            <div class="flex items-center justify-between">
                                <div class="checkbox-group">
                                    <input type="checkbox" id="remember-me" name="remember">
                                    <label for="remember-me" class="text-sm">Remember me</label>
                                </div>
                                <a href="#" class="link-text text-sm">Forgot password?</a>
                            </div>
                        </div>

                        <div class="form-group">
                            <button type="submit" class="btn-primary" id="login-btn">
                                <span class="flex items-center justify-center gap-2">
                                    <span id="login-text">Sign In</span>
                                    <div class="loading-spinner" id="login-spinner"></div>
                                </span>
                            </button>
                        </div>

                        <div id="login-error" class="error-message" style="display: none;">
                            <i data-lucide="alert-circle" class="w-4 h-4"></i>
                            <span></span>
                        </div>
                    </form>

                    <div class="text-center mt-6">
                        <p class="text-sm text-gray-600">
                            Don't have an account? 
                            <button onclick="switchTab('register')" class="link-text">Create one</button>
                        </p>
                    </div>
                </div>

                <!-- Register Form -->
                <div class="form-section" id="register-section">
                    <h2 class="text-2xl font-serif font-light mb-6 text-center">Create Account</h2>
                    
                    <form id="register-form">
                        <div class="form-group">
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label for="register-first-name" class="form-label">First Name</label>
                                    <input 
                                        type="text" 
                                        id="register-first-name" 
                                        name="first_name" 
                                        class="form-input" 
                                        placeholder="John"
                                        required
                                    >
                                </div>
                                <div>
                                    <label for="register-last-name" class="form-label">Last Name</label>
                                    <input 
                                        type="text" 
                                        id="register-last-name" 
                                        name="last_name" 
                                        class="form-input" 
                                        placeholder="Doe"
                                        required
                                    >
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="register-email" class="form-label">Email Address</label>
                            <input 
                                type="email" 
                                id="register-email" 
                                name="email" 
                                class="form-input" 
                                placeholder="your@email.com"
                                required
                            >
                        </div>

                        <div class="form-group">
                            <label for="register-phone" class="form-label">Phone Number (Optional)</label>
                            <input 
                                type="tel" 
                                id="register-phone" 
                                name="phone" 
                                class="form-input" 
                                placeholder="+66 123 456 7890"
                            >
                        </div>

                        <div class="form-group">
                            <label for="register-password" class="form-label">Password</label>
                            <div class="password-field">
                                <input 
                                    type="password" 
                                    id="register-password" 
                                    name="password" 
                                    class="form-input" 
                                    placeholder="At least 8 characters"
                                    required
                                    minlength="8"
                                >
                                <button type="button" class="password-toggle" onclick="togglePassword('register-password')">
                                    <i data-lucide="eye" class="w-4 h-4"></i>
                                </button>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="register-confirm-password" class="form-label">Confirm Password</label>
                            <div class="password-field">
                                <input 
                                    type="password" 
                                    id="register-confirm-password" 
                                    name="confirm_password" 
                                    class="form-input" 
                                    placeholder="Repeat your password"
                                    required
                                >
                                <button type="button" class="password-toggle" onclick="togglePassword('register-confirm-password')">
                                    <i data-lucide="eye" class="w-4 h-4"></i>
                                </button>
                            </div>
                        </div>

                        <div class="form-group">
                            <div class="checkbox-group">
                                <input type="checkbox" id="terms-agreement" name="terms" required>
                                <label for="terms-agreement" class="text-sm">
                                    I agree to the <a href="#" class="link-text">Terms of Service</a> and 
                                    <a href="#" class="link-text">Privacy Policy</a>
                                </label>
                            </div>
                        </div>

                        <div class="form-group">
                            <button type="submit" class="btn-primary" id="register-btn">
                                <span class="flex items-center justify-center gap-2">
                                    <span id="register-text">Create Account</span>
                                    <div class="loading-spinner" id="register-spinner"></div>
                                </span>
                            </button>
                        </div>

                        <div id="register-error" class="error-message" style="display: none;">
                            <i data-lucide="alert-circle" class="w-4 h-4"></i>
                            <span></span>
                        </div>

                        <div id="register-success" class="success-message" style="display: none;">
                            <i data-lucide="check-circle" class="w-4 h-4"></i>
                            <span></span>
                        </div>
                    </form>

                    <div class="text-center mt-6">
                        <p class="text-sm text-gray-600">
                            Already have an account? 
                            <button onclick="switchTab('login')" class="link-text">Sign in</button>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <script>
        // Initialize Lucide icons
        lucide.createIcons();

        // Tab switching
        function switchTab(tab) {
            // Update tab buttons
            document.querySelectorAll('.tab-button').forEach(btn => btn.classList.remove('active'));
            document.getElementById(tab + '-tab').classList.add('active');
            
            // Update form sections
            document.querySelectorAll('.form-section').forEach(section => section.classList.remove('active'));
            document.getElementById(tab + '-section').classList.add('active');
            
            // Clear any error messages
            hideMessage('login-error');
            hideMessage('register-error');
            hideMessage('register-success');
        }

        // Password toggle
        function togglePassword(inputId) {
            const input = document.getElementById(inputId);
            const icon = input.nextElementSibling.querySelector('i');
            
            if (input.type === 'password') {
                input.type = 'text';
                icon.setAttribute('data-lucide', 'eye-off');
            } else {
                input.type = 'password';
                icon.setAttribute('data-lucide', 'eye');
            }
            
            lucide.createIcons();
        }

        // Show message
        function showMessage(elementId, message, isError = true) {
            const element = document.getElementById(elementId);
            const span = element.querySelector('span');
            span.textContent = message;
            element.style.display = 'flex';
            
            if (isError) {
                element.className = 'error-message';
                element.querySelector('i').setAttribute('data-lucide', 'alert-circle');
            } else {
                element.className = 'success-message';
                element.querySelector('i').setAttribute('data-lucide', 'check-circle');
            }
            
            lucide.createIcons();
        }

        // Hide message
        function hideMessage(elementId) {
            document.getElementById(elementId).style.display = 'none';
        }

        // Set loading state
        function setLoading(formType, isLoading) {
            const btn = document.getElementById(formType + '-btn');
            const text = document.getElementById(formType + '-text');
            const spinner = document.getElementById(formType + '-spinner');
            
            if (isLoading) {
                btn.disabled = true;
                text.textContent = formType === 'login' ? 'Signing In...' : 'Creating Account...';
                spinner.style.display = 'block';
            } else {
                btn.disabled = false;
                text.textContent = formType === 'login' ? 'Sign In' : 'Create Account';
                spinner.style.display = 'none';
            }
        }

        // Login form handler
        document.getElementById('login-form').addEventListener('submit', function(e) {
            e.preventDefault();
            
            hideMessage('login-error');
            setLoading('login', true);
            
            const formData = new FormData(this);
            const data = {
                email: formData.get('email'),
                password: formData.get('password'),
                remember: formData.get('remember') ? true : false
            };
            
            fetch('api/auth.php?action=login', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify(data)
            })
            .then(response => response.json())
            .then(data => {
                setLoading('login', false);
                
                if (data.success) {
                    // Redirect to home page or intended destination
                    const redirectUrl = new URLSearchParams(window.location.search).get('redirect') || 'index.php';
                    window.location.href = redirectUrl;
                } else {
                    showMessage('login-error', data.message || 'Login failed');
                }
            })
            .catch(error => {
                setLoading('login', false);
                console.error('Login error:', error);
                showMessage('login-error', 'An error occurred during login');
            });
        });

        // Register form handler
        document.getElementById('register-form').addEventListener('submit', function(e) {
            e.preventDefault();
            
            hideMessage('register-error');
            hideMessage('register-success');
            setLoading('register', true);
            
            const formData = new FormData(this);
            const password = formData.get('password');
            const confirmPassword = formData.get('confirm_password');
            
            // Client-side validation
            if (password !== confirmPassword) {
                setLoading('register', false);
                showMessage('register-error', 'Passwords do not match');
                return;
            }
            
            if (password.length < 8) {
                setLoading('register', false);
                showMessage('register-error', 'Password must be at least 8 characters long');
                return;
            }
            
            const data = {
                first_name: formData.get('first_name'),
                last_name: formData.get('last_name'),
                email: formData.get('email'),
                phone: formData.get('phone'),
                password: password,
                confirm_password: confirmPassword
            };
            
            fetch('api/auth.php?action=register', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify(data)
            })
            .then(response => response.json())
            .then(data => {
                setLoading('register', false);
                
                if (data.success) {
                    showMessage('register-success', 'Account created successfully! You can now sign in.', false);
                    // Clear form
                    document.getElementById('register-form').reset();
                    // Switch to login tab after 2 seconds
                    setTimeout(() => {
                        switchTab('login');
                    }, 2000);
                } else {
                    showMessage('register-error', data.message || 'Registration failed');
                }
            })
            .catch(error => {
                setLoading('register', false);
                console.error('Registration error:', error);
                showMessage('register-error', 'An error occurred during registration');
            });
        });

        // Password strength indicator (optional enhancement)
        document.getElementById('register-password').addEventListener('input', function() {
            const password = this.value;
            const strengthIndicator = document.getElementById('password-strength');
            
            // You can add password strength indication here
        });

        // Real-time validation for confirm password
        document.getElementById('register-confirm-password').addEventListener('input', function() {
            const password = document.getElementById('register-password').value;
            const confirmPassword = this.value;
            
            if (confirmPassword && password !== confirmPassword) {
                this.classList.add('error');
            } else {
                this.classList.remove('error');
            }
        });

        // Update cart count
        function updateCartCount() {
            fetch('api/cart.php?action=count')
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    document.getElementById('cart-count').textContent = data.cart_count;
                }
            })
            .catch(error => console.error('Cart count update error:', error));
        }

        // Initialize page
        document.addEventListener('DOMContentLoaded', function() {
            updateCartCount();
        });
    </script>
</body>
</html>