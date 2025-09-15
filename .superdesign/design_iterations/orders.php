<?php
// =============================================
// USER ORDERS PAGE
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

// TODO: Implement order management system
// For now, this is a placeholder page
$orders = []; // This would come from an OrderManager class
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Orders - Natural</title>
    
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
        
        .empty-state {
            text-align: center;
            padding: 4rem 2rem;
            background: var(--card);
            border: 1px solid var(--border);
            border-radius: var(--radius-lg);
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
                <h1 class="text-4xl font-serif font-light mb-4">My Orders</h1>
                <p class="text-gray-600">Track and manage your order history</p>
            </div>

            <!-- Orders Content -->
            <?php if (empty($orders)): ?>
                <!-- Empty State -->
                <div class="empty-state">
                    <div class="mb-6">
                        <i data-lucide="package" class="w-16 h-16 mx-auto text-gray-400 mb-4"></i>
                        <h2 class="text-2xl font-semibold mb-2">No Orders Yet</h2>
                        <p class="text-gray-600 mb-6">You haven't placed any orders yet. Start shopping to see your orders here!</p>
                        <a href="collections.php" class="btn-primary">
                            <i data-lucide="shopping-bag" class="w-4 h-4 inline mr-2"></i>
                            Start Shopping
                        </a>
                    </div>
                </div>
            <?php else: ?>
                <!-- Orders List -->
                <div class="space-y-6">
                    <?php foreach ($orders as $order): ?>
                        <!-- Order Card -->
                        <div class="bg-white border border-gray-200 rounded-lg p-6">
                            <div class="flex justify-between items-start mb-4">
                                <div>
                                    <h3 class="text-lg font-semibold">Order #<?php echo $order['id']; ?></h3>
                                    <p class="text-gray-600">Placed on <?php echo date('F j, Y', strtotime($order['created_at'])); ?></p>
                                </div>
                                <span class="px-3 py-1 bg-green-100 text-green-800 rounded-full text-sm font-medium">
                                    <?php echo ucfirst($order['status']); ?>
                                </span>
                            </div>
                            
                            <div class="border-t pt-4">
                                <div class="flex justify-between items-center">
                                    <div>
                                        <p class="text-gray-600">Total: <span class="font-semibold text-black"><?php echo formatPrice($order['total']); ?></span></p>
                                    </div>
                                    <div class="space-x-2">
                                        <button class="btn-secondary">View Details</button>
                                        <button class="btn-primary">Track Order</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <!-- Help Section -->
            <div class="mt-12 bg-gray-50 rounded-lg p-6">
                <h3 class="text-lg font-semibold mb-4">Need Help?</h3>
                <div class="grid md:grid-cols-3 gap-4">
                    <div class="text-center">
                        <i data-lucide="headphones" class="w-8 h-8 mx-auto text-gray-600 mb-2"></i>
                        <h4 class="font-medium mb-1">Customer Support</h4>
                        <p class="text-sm text-gray-600">Get help with your orders</p>
                        <a href="contact.php" class="text-primary text-sm font-medium">Contact Us</a>
                    </div>
                    
                    <div class="text-center">
                        <i data-lucide="refresh-cw" class="w-8 h-8 mx-auto text-gray-600 mb-2"></i>
                        <h4 class="font-medium mb-1">Returns</h4>
                        <p class="text-sm text-gray-600">Easy 30-day returns</p>
                        <a href="#" class="text-primary text-sm font-medium">Return Policy</a>
                    </div>
                    
                    <div class="text-center">
                        <i data-lucide="truck" class="w-8 h-8 mx-auto text-gray-600 mb-2"></i>
                        <h4 class="font-medium mb-1">Shipping Info</h4>
                        <p class="text-sm text-gray-600">Track your packages</p>
                        <a href="#" class="text-primary text-sm font-medium">Shipping Policy</a>
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