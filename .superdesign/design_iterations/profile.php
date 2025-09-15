<?php
// =============================================
// USER PROFILE PAGE
// Natural Clothing Website
// =============================================

require_once __DIR__ . '/api/Database.php';
require_once __DIR__ . '/config.php';

// Start session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if user is logged in
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header('Location: login.php');
    exit;
}

// Initialize managers
$userManager = new UserManager();
$cartManager = new CartManager();

// Get user data
$userId = $_SESSION['user_id'];
$user = $userManager->getUserById($userId);

if (!$user) {
    header('Location: login.php');
    exit;
}

// Get cart count for header
$sessionId = $_SESSION['guest_session_id'] ?? null;
$cartCount = $cartManager->getCartItemCount($userId, $sessionId);

$isLoggedIn = true;
$userName = $_SESSION['user_name'] ?? $user['first_name'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Profile - Natural</title>
    
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
        
        .dropdown {
            position: relative;
            display: inline-block;
        }
        
        .dropdown-content {
            display: none;
            position: absolute;
            right: 0;
            background-color: white;
            min-width: 200px;
            box-shadow: 0px 8px 16px 0px rgba(0,0,0,0.2);
            border-radius: 8px;
            z-index: 1000;
            border: 1px solid var(--border);
        }
        
        .dropdown-content a {
            color: var(--foreground);
            padding: 12px 16px;
            text-decoration: none;
            display: block;
            transition: background-color 200ms;
        }
        
        .dropdown-content a:hover {
            background-color: var(--muted);
        }
        
        .dropdown:hover .dropdown-content {
            display: block;
        }
        
        .profile-card {
            background: var(--card);
            border: 1px solid var(--border);
            border-radius: var(--radius-lg);
            padding: 2rem;
            margin-bottom: 2rem;
        }
        
        .profile-field {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 1rem 0;
            border-bottom: 1px solid var(--border);
        }
        
        .profile-field:last-child {
            border-bottom: none;
        }
        
        .btn-primary {
            background: var(--primary);
            color: var(--primary-foreground);
            padding: 0.75rem 1.5rem;
            border-radius: var(--radius-md);
            border: none;
            cursor: pointer;
            font-weight: 500;
            transition: all 200ms ease-out;
            text-decoration: none;
            display: inline-block;
        }
        
        .btn-primary:hover {
            transform: scale(1.02);
            box-shadow: var(--shadow-md);
        }
        
        .btn-secondary {
            background: var(--secondary);
            color: var(--secondary-foreground);
            padding: 0.75rem 1.5rem;
            border-radius: var(--radius-md);
            border: 1px solid var(--border);
            cursor: pointer;
            font-weight: 500;
            transition: all 200ms ease-out;
            text-decoration: none;
            display: inline-block;
        }
        
        .btn-secondary:hover {
            background: var(--accent);
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
                        <span class="absolute -top-2 -right-2 bg-red-500 text-white text-xs w-5 h-5 rounded-full flex items-center justify-center"><?php echo $cartCount; ?></span>
                    </a>
                    
                    <div class="dropdown">
                        <button class="p-2 hover:bg-gray-100 rounded-full transition-colors">
                            <i data-lucide="user" class="w-5 h-5"></i>
                        </button>
                        <div class="dropdown-content">
                            <div style="padding: 12px 16px; border-bottom: 1px solid var(--border); font-weight: 500;">
                                <?php echo htmlspecialchars($userName); ?>
                            </div>
                            <a href="profile.php">Profile</a>
                            <a href="orders.php">Orders</a>
                            <a href="#" onclick="logout()">Logout</a>
                        </div>
                    </div>
                    
                    <button class="md:hidden p-2 hover:bg-gray-100 rounded-full transition-colors">
                        <i data-lucide="menu" class="w-5 h-5"></i>
                    </button>
                </div>
            </div>
        </nav>
    </header>

    <!-- Main Content -->
    <main class="py-12">
        <div class="container-custom">
            <!-- Page Header -->
            <div class="mb-8">
                <h1 class="text-4xl font-serif font-light mb-4">My Profile</h1>
                <p class="text-gray-600">Manage your account information and preferences</p>
            </div>

            <div class="grid lg:grid-cols-3 gap-8">
                <!-- Profile Information -->
                <div class="lg:col-span-2">
                    <div class="profile-card">
                        <h2 class="text-2xl font-semibold mb-6">Personal Information</h2>
                        
                        <div class="profile-field">
                            <div>
                                <label class="font-medium text-gray-700">First Name</label>
                                <p class="text-gray-900"><?php echo htmlspecialchars($user['first_name']); ?></p>
                            </div>
                            <button class="btn-secondary" onclick="alert('Profile editing feature coming soon!')">Edit</button>
                        </div>
                        
                        <div class="profile-field">
                            <div>
                                <label class="font-medium text-gray-700">Last Name</label>
                                <p class="text-gray-900"><?php echo htmlspecialchars($user['last_name']); ?></p>
                            </div>
                            <button class="btn-secondary" onclick="alert('Profile editing feature coming soon!')">Edit</button>
                        </div>
                        
                        <div class="profile-field">
                            <div>
                                <label class="font-medium text-gray-700">Email</label>
                                <p class="text-gray-900"><?php echo htmlspecialchars($user['email']); ?></p>
                            </div>
                            <button class="btn-secondary" onclick="alert('Profile editing feature coming soon!')">Edit</button>
                        </div>
                        
                        <div class="profile-field">
                            <div>
                                <label class="font-medium text-gray-700">Phone</label>
                                <p class="text-gray-900"><?php echo htmlspecialchars($user['phone'] ?: 'Not provided'); ?></p>
                            </div>
                            <button class="btn-secondary" onclick="alert('Profile editing feature coming soon!')">Edit</button>
                        </div>
                        
                        <div class="profile-field">
                            <div>
                                <label class="font-medium text-gray-700">Member Since</label>
                                <p class="text-gray-900"><?php echo date('F j, Y', strtotime($user['created_at'])); ?></p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Quick Actions -->
                <div>
                    <div class="profile-card">
                        <h3 class="text-xl font-semibold mb-4">Quick Actions</h3>
                        
                        <div class="space-y-3">
                            <a href="orders.php" class="btn-primary w-full text-center">
                                <i data-lucide="package" class="w-4 h-4 inline mr-2"></i>
                                View Orders
                            </a>
                            
                            <a href="cart.php" class="btn-secondary w-full text-center">
                                <i data-lucide="shopping-cart" class="w-4 h-4 inline mr-2"></i>
                                View Cart
                            </a>
                            
                            <a href="collections.php" class="btn-secondary w-full text-center">
                                <i data-lucide="shirt" class="w-4 h-4 inline mr-2"></i>
                                Shop Now
                            </a>
                            
                            <button onclick="alert('Change password feature coming soon!')" class="btn-secondary w-full">
                                <i data-lucide="lock" class="w-4 h-4 inline mr-2"></i>
                                Change Password
                            </button>
                        </div>
                    </div>
                    
                    <!-- Account Stats -->
                    <div class="profile-card">
                        <h3 class="text-xl font-semibold mb-4">Account Stats</h3>
                        
                        <div class="space-y-3">
                            <div class="flex justify-between">
                                <span class="text-gray-600">Total Orders</span>
                                <span class="font-semibold">Coming Soon</span>
                            </div>
                            
                            <div class="flex justify-between">
                                <span class="text-gray-600">Total Spent</span>
                                <span class="font-semibold">Coming Soon</span>
                            </div>
                            
                            <div class="flex justify-between">
                                <span class="text-gray-600">Loyalty Points</span>
                                <span class="font-semibold">Coming Soon</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <script>
        // Initialize Lucide icons
        lucide.createIcons();

        // Logout function
        function logout() {
            fetch('api/auth.php?action=logout', {
                method: 'POST'
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    window.location.href = 'index.php';
                }
            })
            .catch(error => {
                console.error('Logout error:', error);
                window.location.href = 'login.php';
            });
        }
    </script>
</body>
</html>