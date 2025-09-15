<?php
// =============================================
// NATURAL CLOTHING - SHOPPING CART PAGE
// =============================================

// Prevent any output before HTML
ob_start();

require_once __DIR__ . '/api/Database.php';
require_once __DIR__ . '/config.php';

// Start session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Initialize guest session if needed
if (!isset($_SESSION['user_id']) && !isset($_SESSION['guest_session_id'])) {
    $_SESSION['guest_session_id'] = uniqid('guest_', true);
}

// Initialize managers
$cartManager = new CartManager();

// Get cart items and totals
$userId = $_SESSION['user_id'] ?? null;
$sessionId = $_SESSION['guest_session_id'] ?? null;

$rawCartItems = $cartManager->getCartItems($userId, $sessionId);

// Format cart items using helper function (no headers)
require_once __DIR__ . '/api/cart_helpers.php';
$cartItems = array_map('formatCartItem', $rawCartItems);

$cartTotal = $cartManager->getCartTotal($userId, $sessionId);
$cartCount = $cartManager->getCartItemCount($userId, $sessionId);

// Check if user is logged in
$isLoggedIn = isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true;
$userName = $_SESSION['user_name'] ?? null;

// Calculate shipping and tax (example values)
$subtotal = $cartTotal;
$shipping = $subtotal > 0 ? ($subtotal >= 1500 ? 0 : 100) : 0; // Free shipping over ฿1500
$tax = $subtotal * 0.07; // 7% VAT
$finalTotal = $subtotal + $shipping + $tax;

// Clear any unwanted output and start HTML
ob_end_clean();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shopping Cart - Natural</title>
    
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
        
        .cart-item {
            display: flex;
            gap: 1rem;
            padding: 1.5rem;
            background: var(--card);
            border: 1px solid var(--border);
            border-radius: var(--radius-lg);
            margin-bottom: 1rem;
            transition: all 200ms ease-out;
        }
        
        .cart-item:hover {
            box-shadow: var(--shadow-md);
        }
        
        .cart-item-image {
            width: 120px;
            height: 120px;
            flex-shrink: 0;
            border-radius: var(--radius-md);
            overflow: hidden;
        }
        
        .cart-item-content {
            flex: 1;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }
        
        .cart-item-details {
            flex: 1;
        }
        
        .cart-item-actions {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-top: 1rem;
        }
        
        .quantity-controls {
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .quantity-btn {
            width: 2rem;
            height: 2rem;
            border: 1px solid var(--border);
            background: var(--background);
            border-radius: var(--radius-sm);
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 200ms ease-out;
        }
        
        .quantity-btn:hover:not(:disabled) {
            background: var(--muted);
            border-color: var(--primary);
        }
        
        .quantity-btn:disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }
        
        .quantity-input {
            width: 3rem;
            height: 2rem;
            text-align: center;
            border: 1px solid var(--border);
            border-radius: var(--radius-sm);
            background: var(--background);
            font-size: 0.875rem;
        }
        
        .remove-btn {
            padding: 0.5rem;
            color: var(--destructive);
            background: none;
            border: none;
            border-radius: var(--radius-sm);
            cursor: pointer;
            transition: all 200ms ease-out;
        }
        
        .remove-btn:hover {
            background: rgba(239, 68, 68, 0.1);
        }
        
        .cart-summary {
            background: var(--card);
            border: 1px solid var(--border);
            border-radius: var(--radius-lg);
            padding: 2rem;
            position: sticky;
            top: 6rem;
        }
        
        .summary-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0.75rem 0;
        }
        
        .summary-row:not(:last-child) {
            border-bottom: 1px solid var(--border);
        }
        
        .summary-row.total {
            font-weight: 600;
            font-size: 1.125rem;
            color: var(--primary);
        }
        
        .btn-primary {
            width: 100%;
            padding: 1rem 1.5rem;
            border-radius: var(--radius-md);
            background: var(--primary);
            color: var(--primary-foreground);
            font-weight: 500;
            letter-spacing: 0.025em;
            transition: all 200ms ease-out;
            border: none;
            cursor: pointer;
            font-size: 1rem;
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
            padding: 1rem 1.5rem;
            border-radius: var(--radius-md);
            background: transparent;
            color: var(--primary);
            font-weight: 500;
            letter-spacing: 0.025em;
            transition: all 200ms ease-out;
            border: 1px solid var(--primary);
            cursor: pointer;
            font-size: 1rem;
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
        
        .empty-cart {
            text-align: center;
            padding: 4rem 2rem;
            background: var(--card);
            border: 1px solid var(--border);
            border-radius: var(--radius-lg);
        }
        
        .shipping-notice {
            background: var(--muted);
            padding: 1rem;
            border-radius: var(--radius-md);
            margin-bottom: 1.5rem;
            font-size: 0.875rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .section-padding { 
            padding: clamp(3rem, 8vw, 6rem) 0; 
        }
        
        .breadcrumb {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 0.875rem;
            color: var(--muted-foreground);
            margin-bottom: 2rem;
        }
        
        .breadcrumb a {
            color: var(--primary);
            text-decoration: none;
        }
        
        .breadcrumb a:hover {
            text-decoration: underline;
        }
        
        .loading-overlay {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(255, 255, 255, 0.8);
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: var(--radius-lg);
            opacity: 0;
            visibility: hidden;
            transition: all 300ms ease-out;
        }
        
        .loading-overlay.show {
            opacity: 1;
            visibility: visible;
        }
        
        .spinner {
            width: 2rem;
            height: 2rem;
            border: 2px solid var(--border);
            border-top: 2px solid var(--primary);
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }
        
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        
        @media (max-width: 768px) {
            .cart-layout {
                flex-direction: column;
            }
            
            .cart-summary {
                position: static;
            }
            
            .cart-item {
                flex-direction: column;
                gap: 1rem;
            }
            
            .cart-item-image {
                width: 100%;
                height: 200px;
            }
            
            .cart-item-actions {
                flex-direction: column;
                gap: 1rem;
                align-items: stretch;
            }
            
            .quantity-controls {
                justify-content: center;
            }
        }
        
        .img-cover {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        
        .fade-in {
            animation: fadeIn 0.5s ease-out;
        }
        
        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
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
                    
                    <?php if ($isLoggedIn): ?>
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
                    <?php else: ?>
                        <a href="login.php" class="p-2 hover:bg-gray-100 rounded-full transition-colors">
                            <i data-lucide="user" class="w-5 h-5"></i>
                        </a>
                    <?php endif; ?>
                    
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
            <!-- Breadcrumb -->
            <div class="breadcrumb">
                <a href="index.php">Home</a>
                <i data-lucide="chevron-right" class="w-4 h-4"></i>
                <span>Shopping Cart</span>
            </div>

            <!-- Page Header -->
            <div class="mb-8">
                <h1 class="text-4xl font-serif font-light mb-4">Shopping Cart</h1>
                <p class="text-gray-600">
                    <?php echo $cartCount; ?> item<?php echo $cartCount !== 1 ? 's' : ''; ?> in your cart
                </p>
            </div>

            <?php if (empty($cartItems)): ?>
                <!-- Empty Cart -->
                <div class="empty-cart fade-in">
                    <i data-lucide="shopping-bag" class="w-16 h-16 mx-auto mb-4 text-gray-400"></i>
                    <h3 class="text-2xl font-semibold mb-4">Your cart is empty</h3>
                    <p class="text-gray-600 mb-8">
                        Discover our sustainable clothing collection and add some items to your cart.
                    </p>
                    <a href="collections.php" class="btn-primary inline-block" style="width: auto; padding: 1rem 2rem;">
                        Start Shopping
                    </a>
                </div>
            <?php else: ?>
                <!-- Cart Content -->
                <div class="cart-layout flex flex-col lg:flex-row gap-8">
                    <!-- Cart Items -->
                    <div class="flex-1">
                        <!-- Shipping Notice -->
                        <?php if ($subtotal > 0 && $subtotal < 1500): ?>
                            <div class="shipping-notice">
                                <i data-lucide="truck" class="w-4 h-4 text-orange-600"></i>
                                <span>
                                    Add <?php echo formatPrice(1500 - $subtotal); ?> more for free shipping!
                                </span>
                            </div>
                        <?php elseif ($shipping === 0 && $subtotal > 0): ?>
                            <div class="shipping-notice" style="background: rgba(34, 197, 94, 0.1); color: rgb(34, 197, 94);">
                                <i data-lucide="check-circle" class="w-4 h-4"></i>
                                <span>You qualify for free shipping!</span>
                            </div>
                        <?php endif; ?>

                        <!-- Cart Items List -->
                        <div id="cart-items-container">
                            <?php foreach ($cartItems as $item): ?>
                                <div class="cart-item fade-in" data-item-id="<?php echo $item['id']; ?>">
                                    <div class="loading-overlay">
                                        <div class="spinner"></div>
                                    </div>
                                    
                                    <div class="cart-item-image">
                                        <img src="<?php echo htmlspecialchars($item['product_image']); ?>" 
                                             alt="<?php echo htmlspecialchars($item['product_name']); ?>" 
                                             class="img-cover">
                                    </div>
                                    
                                    <div class="cart-item-content">
                                        <div class="cart-item-details">
                                            <h3 class="font-semibold text-lg mb-1">
                                                <a href="product_detail.php?id=<?php echo $item['product_id']; ?>" 
                                                   class="hover:text-primary transition-colors">
                                                    <?php echo htmlspecialchars($item['product_name']); ?>
                                                </a>
                                            </h3>
                                            
                                            <?php if ($item['variant_name']): ?>
                                                <p class="text-sm text-gray-600 mb-2">
                                                    Variant: <?php echo htmlspecialchars($item['variant_name']); ?>
                                                </p>
                                            <?php endif; ?>
                                            
                                            <p class="text-lg font-semibold" style="color: var(--primary);">
                                                <?php echo formatPrice($item['price']); ?>
                                            </p>
                                        </div>
                                        
                                        <div class="cart-item-actions">
                                            <div class="quantity-controls">
                                                <button class="quantity-btn" onclick="updateQuantity(<?php echo $item['id']; ?>, <?php echo $item['quantity'] - 1; ?>)">
                                                    <i data-lucide="minus" class="w-4 h-4"></i>
                                                </button>
                                                <input type="number" 
                                                       class="quantity-input" 
                                                       value="<?php echo $item['quantity']; ?>" 
                                                       min="1" 
                                                       max="10"
                                                       onchange="updateQuantity(<?php echo $item['id']; ?>, this.value)">
                                                <button class="quantity-btn" onclick="updateQuantity(<?php echo $item['id']; ?>, <?php echo $item['quantity'] + 1; ?>)">
                                                    <i data-lucide="plus" class="w-4 h-4"></i>
                                                </button>
                                            </div>
                                            
                                            <div class="flex items-center gap-4">
                                                <span class="font-semibold">
                                                    <?php echo formatPrice($item['total']); ?>
                                                </span>
                                                <button class="remove-btn" onclick="removeItem(<?php echo $item['id']; ?>)">
                                                    <i data-lucide="trash-2" class="w-4 h-4"></i>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>

                        <!-- Cart Actions -->
                        <div class="flex flex-col sm:flex-row gap-4 mt-8">
                            <a href="collections.php" class="btn-secondary">
                                Continue Shopping
                            </a>
                            <button onclick="clearCart()" class="btn-secondary" style="border-color: var(--destructive); color: var(--destructive);">
                                Clear Cart
                            </button>
                        </div>
                    </div>

                    <!-- Cart Summary -->
                    <div class="lg:w-96">
                        <div class="cart-summary" id="cart-summary">
                            <h3 class="text-xl font-semibold mb-6">Order Summary</h3>
                            
                            <div class="summary-row">
                                <span>Subtotal (<?php echo $cartCount; ?> item<?php echo $cartCount !== 1 ? 's' : ''; ?>)</span>
                                <span id="subtotal-amount"><?php echo formatPrice($subtotal); ?></span>
                            </div>
                            
                            <div class="summary-row">
                                <span>Shipping</span>
                                <span id="shipping-amount">
                                    <?php echo $shipping === 0 ? 'Free' : formatPrice($shipping); ?>
                                </span>
                            </div>
                            
                            <div class="summary-row">
                                <span>Tax (7% VAT)</span>
                                <span id="tax-amount"><?php echo formatPrice($tax); ?></span>
                            </div>
                            
                            <div class="summary-row total">
                                <span>Total</span>
                                <span id="total-amount"><?php echo formatPrice($finalTotal); ?></span>
                            </div>
                            
                            <div class="mt-6 space-y-3">
                                <?php if ($isLoggedIn): ?>
                                    <button onclick="proceedToCheckout()" class="btn-primary">
                                        Proceed to Checkout
                                    </button>
                                <?php else: ?>
                                    <a href="login.php?redirect=<?php echo urlencode('cart.php'); ?>" class="btn-primary">
                                        Login to Checkout
                                    </a>
                                    <p class="text-sm text-gray-600 text-center">
                                        Have an account? <a href="login.php" class="text-primary hover:underline">Sign in</a> 
                                        or continue as guest
                                    </p>
                                <?php endif; ?>
                            </div>
                            
                            <!-- Security Icons -->
                            <div class="flex justify-center items-center gap-4 mt-6 pt-6 border-t">
                                <div class="flex items-center gap-2 text-sm text-gray-600">
                                    <i data-lucide="shield-check" class="w-4 h-4"></i>
                                    <span>Secure Checkout</span>
                                </div>
                                <div class="flex items-center gap-2 text-sm text-gray-600">
                                    <i data-lucide="truck" class="w-4 h-4"></i>
                                    <span>Free Returns</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </main>

    <script>
        // Initialize Lucide icons
        lucide.createIcons();

        // Update item quantity
        function updateQuantity(itemId, newQuantity) {
            if (newQuantity < 1) {
                removeItem(itemId);
                return;
            }

            const cartItem = document.querySelector(`[data-item-id="${itemId}"]`);
            const loadingOverlay = cartItem.querySelector('.loading-overlay');
            loadingOverlay.classList.add('show');

            fetch('api/cart.php?action=update', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    cart_item_id: itemId,
                    quantity: parseInt(newQuantity)
                })
            })
            .then(response => response.json())
            .then(data => {
                loadingOverlay.classList.remove('show');
                
                if (data.success) {
                    // Update cart count in header
                    document.getElementById('cart-count').textContent = data.cart_count;
                    
                    // Reload page to update all totals
                    window.location.reload();
                } else {
                    showNotification(data.message || 'Failed to update cart', 'error');
                }
            })
            .catch(error => {
                loadingOverlay.classList.remove('show');
                console.error('Update quantity error:', error);
                showNotification('Error updating quantity', 'error');
            });
        }

        // Remove item from cart
        function removeItem(itemId) {
            if (!confirm('Are you sure you want to remove this item from your cart?')) {
                return;
            }

            const cartItem = document.querySelector(`[data-item-id="${itemId}"]`);
            const loadingOverlay = cartItem.querySelector('.loading-overlay');
            loadingOverlay.classList.add('show');

            fetch('api/cart.php?action=remove', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    cart_item_id: itemId
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Update cart count in header
                    document.getElementById('cart-count').textContent = data.cart_count;
                    
                    // Remove item with animation
                    cartItem.style.opacity = '0';
                    cartItem.style.transform = 'translateX(-100%)';
                    
                    setTimeout(() => {
                        cartItem.remove();
                        
                        // Reload page if cart is empty
                        if (data.cart_count === 0) {
                            window.location.reload();
                        } else {
                            updateCartSummary(data);
                        }
                    }, 300);
                    
                    showNotification('Item removed from cart', 'success');
                } else {
                    loadingOverlay.classList.remove('show');
                    showNotification(data.message || 'Failed to remove item', 'error');
                }
            })
            .catch(error => {
                loadingOverlay.classList.remove('show');
                console.error('Remove item error:', error);
                showNotification('Error removing item', 'error');
            });
        }

        // Clear entire cart
        function clearCart() {
            if (!confirm('Are you sure you want to clear your entire cart?')) {
                return;
            }

            fetch('api/cart.php?action=clear', {
                method: 'POST'
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    window.location.reload();
                } else {
                    showNotification(data.message || 'Failed to clear cart', 'error');
                }
            })
            .catch(error => {
                console.error('Clear cart error:', error);
                showNotification('Error clearing cart', 'error');
            });
        }

        // Proceed to checkout
        function proceedToCheckout() {
            // Redirect to checkout page
            window.location.href = 'checkout.php';
        }

        // Update cart summary
        function updateCartSummary(data) {
            const subtotal = data.cart_total;
            const shipping = subtotal >= 1500 ? 0 : 100;
            const tax = subtotal * 0.07;
            const total = subtotal + shipping + tax;

            document.getElementById('subtotal-amount').textContent = formatPrice(subtotal);
            document.getElementById('shipping-amount').textContent = shipping === 0 ? 'Free' : formatPrice(shipping);
            document.getElementById('tax-amount').textContent = formatPrice(tax);
            document.getElementById('total-amount').textContent = formatPrice(total);
        }

        // Logout function
        function logout() {
            fetch('api/auth.php?action=logout', {
                method: 'POST'
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    window.location.reload();
                }
            })
            .catch(error => {
                console.error('Logout error:', error);
                window.location.href = 'login.php';
            });
        }

        // Show notification
        function showNotification(message, type = 'info') {
            const notification = document.createElement('div');
            notification.className = `fixed top-4 right-4 z-50 p-4 rounded-md text-white max-w-sm
                ${type === 'success' ? 'bg-green-500' : type === 'error' ? 'bg-red-500' : 'bg-blue-500'}`;
            
            notification.innerHTML = `
                <div class="flex items-center justify-between">
                    <span>${message}</span>
                    <button onclick="this.parentElement.parentElement.remove()" class="ml-4">
                        <i data-lucide="x" class="w-4 h-4"></i>
                    </button>
                </div>
            `;
            
            document.body.appendChild(notification);
            lucide.createIcons();
            
            // Auto remove after 5 seconds
            setTimeout(() => {
                if (notification.parentElement) {
                    notification.remove();
                }
            }, 5000);
        }

        // Format price helper
        function formatPrice(price, currency = 'THB') {
            return new Intl.NumberFormat('th-TH', {
                style: 'currency',
                currency: currency
            }).format(price);
        }

        // Initialize page
        document.addEventListener('DOMContentLoaded', function() {
            // Add event listeners for quantity inputs
            document.querySelectorAll('.quantity-input').forEach(input => {
                input.addEventListener('blur', function() {
                    const itemId = this.closest('.cart-item').dataset.itemId;
                    const newQuantity = parseInt(this.value);
                    
                    if (newQuantity !== parseInt(this.defaultValue)) {
                        updateQuantity(itemId, newQuantity);
                    }
                });
                
                input.addEventListener('keypress', function(e) {
                    if (e.key === 'Enter') {
                        this.blur();
                    }
                });
            });
        });
    </script>
</body>
</html>