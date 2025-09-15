<?php
// =============================================
// CHECKOUT PAGE
// Natural Clothing Website
// =============================================

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
$userManager = new UserManager();

// Get cart data
$userId = $_SESSION['user_id'] ?? null;
$sessionId = $_SESSION['guest_session_id'] ?? null;
$cartItems = $cartManager->getCartItems($userId, $sessionId);
$cartTotal = $cartManager->getCartTotal($userId, $sessionId);
$cartCount = $cartManager->getCartItemCount($userId, $sessionId);

// Redirect if cart is empty
if (empty($cartItems)) {
    header('Location: cart.php');
    exit;
}

// Check if user is logged in
$isLoggedIn = isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true;
$userName = $_SESSION['user_name'] ?? null;
$user = null;

if ($isLoggedIn && $userId) {
    $user = $userManager->getUserById($userId);
}

// Format cart items
require_once __DIR__ . '/api/cart_helpers.php';
$formattedCartItems = array_map('formatCartItem', $cartItems);

// Calculate shipping and tax
$subtotal = $cartTotal;
$shipping = $subtotal >= 1500 ? 0 : 100; // Free shipping over 1500 THB
$tax = $subtotal * 0.07; // 7% VAT
$total = $subtotal + $shipping + $tax;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout - Natural</title>
    
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
        
        .checkout-section {
            background: var(--card);
            border: 1px solid var(--border);
            border-radius: var(--radius-lg);
            padding: 2rem;
            margin-bottom: 2rem;
        }
        
        .form-group {
            margin-bottom: 1.5rem;
        }
        
        .form-label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 500;
            color: var(--foreground);
        }
        
        .form-input {
            width: 100%;
            padding: 0.75rem;
            border: 1px solid var(--border);
            border-radius: var(--radius-md);
            background: var(--background);
            transition: border-color 200ms ease-out;
        }
        
        .form-input:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
        }
        
        .btn-primary {
            background: var(--primary);
            color: var(--primary-foreground);
            padding: 1rem 2rem;
            border-radius: var(--radius-md);
            border: none;
            cursor: pointer;
            font-weight: 500;
            transition: all 200ms ease-out;
            text-decoration: none;
            display: inline-block;
            width: 100%;
            text-align: center;
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
        
        .order-item {
            display: flex;
            align-items: center;
            padding: 1rem 0;
            border-bottom: 1px solid var(--border);
        }
        
        .order-item:last-child {
            border-bottom: none;
        }
        
        .order-summary {
            background: var(--muted);
            padding: 1.5rem;
            border-radius: var(--radius-md);
        }
        
        .summary-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 0.5rem;
        }
        
        .summary-row.total {
            font-weight: 600;
            font-size: 1.125rem;
            border-top: 1px solid var(--border);
            padding-top: 0.5rem;
            margin-top: 1rem;
        }
        
        /* QR Payment Modal */
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.7);
            z-index: 9999;
            animation: fadeIn 0.3s ease-out;
        }
        
        .modal.show {
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .modal-content {
            background: white;
            border-radius: var(--radius-lg);
            padding: 2rem;
            max-width: 400px;
            width: 90%;
            text-align: center;
            position: relative;
            animation: slideIn 0.3s ease-out;
        }
        
        .modal-close {
            position: absolute;
            top: 1rem;
            right: 1rem;
            background: none;
            border: none;
            font-size: 1.5rem;
            cursor: pointer;
            color: var(--foreground);
        }
        
        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }
        
        @keyframes slideIn {
            from { transform: translateY(-50px); opacity: 0; }
            to { transform: translateY(0); opacity: 1; }
        }
        
        .breadcrumb {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            margin-bottom: 2rem;
            font-size: 0.875rem;
            color: var(--muted-foreground);
        }
        
        .breadcrumb a {
            color: var(--primary);
            text-decoration: none;
        }
        
        .breadcrumb a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body style="font-family: var(--font-sans); background: var(--background); color: var(--foreground); line-height: 1.6;">

    <!-- Main Content -->
    <main class="py-8">
        <div class="container-custom">
            <!-- Breadcrumb -->
            <div class="breadcrumb">
                <a href="index.php">Home</a>
                <i data-lucide="chevron-right" class="w-4 h-4"></i>
                <a href="cart.php">Cart</a>
                <i data-lucide="chevron-right" class="w-4 h-4"></i>
                <span>Checkout</span>
            </div>

            <!-- Page Header -->
            <div class="mb-8">
                <h1 class="text-4xl font-serif font-light mb-4">Checkout</h1>
                <p class="text-gray-600">Complete your order</p>
            </div>

            <div class="grid lg:grid-cols-2 gap-8">
                <!-- Checkout Form -->
                <div>
                    <!-- Contact Information -->
                    <div class="checkout-section">
                        <h2 class="text-xl font-semibold mb-4">Contact Information</h2>
                        
                        <?php if (!$isLoggedIn): ?>
                            <p class="text-sm text-gray-600 mb-4">
                                Already have an account? <a href="login.php" class="text-primary font-medium">Sign in</a>
                            </p>
                        <?php endif; ?>
                        
                        <form id="checkout-form">
                            <div class="form-group">
                                <label class="form-label">Email</label>
                                <input type="email" name="email" class="form-input" 
                                       value="<?php echo $user['email'] ?? ''; ?>" 
                                       <?php echo $isLoggedIn ? 'readonly' : 'required'; ?>>
                            </div>
                            
                            <div class="grid md:grid-cols-2 gap-4">
                                <div class="form-group">
                                    <label class="form-label">First Name</label>
                                    <input type="text" name="first_name" class="form-input" 
                                           value="<?php echo $user['first_name'] ?? ''; ?>" required>
                                </div>
                                
                                <div class="form-group">
                                    <label class="form-label">Last Name</label>
                                    <input type="text" name="last_name" class="form-input" 
                                           value="<?php echo $user['last_name'] ?? ''; ?>" required>
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <label class="form-label">Phone</label>
                                <input type="tel" name="phone" class="form-input" 
                                       value="<?php echo $user['phone'] ?? ''; ?>" required>
                            </div>
                        </form>
                    </div>

                    <!-- Shipping Address -->
                    <div class="checkout-section">
                        <h2 class="text-xl font-semibold mb-4">Shipping Address</h2>
                        
                        <div class="form-group">
                            <label class="form-label">Address</label>
                            <input type="text" name="address" class="form-input" required>
                        </div>
                        
                        <div class="grid md:grid-cols-3 gap-4">
                            <div class="form-group">
                                <label class="form-label">City</label>
                                <input type="text" name="city" class="form-input" required>
                            </div>
                            
                            <div class="form-group">
                                <label class="form-label">Province</label>
                                <input type="text" name="province" class="form-input" required>
                            </div>
                            
                            <div class="form-group">
                                <label class="form-label">Postal Code</label>
                                <input type="text" name="postal_code" class="form-input" required>
                            </div>
                        </div>
                    </div>

                    <!-- Payment Method -->
                    <div class="checkout-section">
                        <h2 class="text-xl font-semibold mb-4">Payment Method</h2>
                        
                        <div class="space-y-3">
                            <label class="flex items-center p-3 border border-gray-200 rounded-lg cursor-pointer hover:bg-gray-50">
                                <input type="radio" name="payment_method" value="qr_code" checked class="mr-3">
                                <i data-lucide="qr-code" class="w-5 h-5 mr-2"></i>
                                <span>QR Code Payment (PromptPay)</span>
                            </label>
                            
                            <label class="flex items-center p-3 border border-gray-200 rounded-lg cursor-pointer hover:bg-gray-50 opacity-50">
                                <input type="radio" name="payment_method" value="credit_card" disabled class="mr-3">
                                <i data-lucide="credit-card" class="w-5 h-5 mr-2"></i>
                                <span>Credit Card (Coming Soon)</span>
                            </label>
                            
                            <label class="flex items-center p-3 border border-gray-200 rounded-lg cursor-pointer hover:bg-gray-50 opacity-50">
                                <input type="radio" name="payment_method" value="bank_transfer" disabled class="mr-3">
                                <i data-lucide="building" class="w-5 h-5 mr-2"></i>
                                <span>Bank Transfer (Coming Soon)</span>
                            </label>
                        </div>
                    </div>
                </div>

                <!-- Order Summary -->
                <div>
                    <div class="checkout-section">
                        <h2 class="text-xl font-semibold mb-4">Order Summary</h2>
                        
                        <!-- Cart Items -->
                        <div class="mb-6">
                            <?php foreach ($formattedCartItems as $item): ?>
                                <div class="order-item">
                                    <img src="<?php echo htmlspecialchars($item['product_image']); ?>" 
                                         alt="<?php echo htmlspecialchars($item['product_name']); ?>" 
                                         class="w-16 h-16 object-cover rounded-md mr-4">
                                    
                                    <div class="flex-1">
                                        <h4 class="font-medium"><?php echo htmlspecialchars($item['product_name']); ?></h4>
                                        <?php if ($item['variant_name']): ?>
                                            <p class="text-sm text-gray-600"><?php echo htmlspecialchars($item['variant_name']); ?></p>
                                        <?php endif; ?>
                                        <p class="text-sm text-gray-600">Qty: <?php echo $item['quantity']; ?></p>
                                    </div>
                                    
                                    <div class="text-right">
                                        <p class="font-medium"><?php echo $item['formatted_total']; ?></p>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>

                        <!-- Order Summary -->
                        <div class="order-summary">
                            <div class="summary-row">
                                <span>Subtotal</span>
                                <span><?php echo formatPrice($subtotal); ?></span>
                            </div>
                            
                            <div class="summary-row">
                                <span>Shipping</span>
                                <span><?php echo $shipping > 0 ? formatPrice($shipping) : 'Free'; ?></span>
                            </div>
                            
                            <div class="summary-row">
                                <span>Tax (VAT 7%)</span>
                                <span><?php echo formatPrice($tax); ?></span>
                            </div>
                            
                            <div class="summary-row total">
                                <span>Total</span>
                                <span><?php echo formatPrice($total); ?></span>
                            </div>
                        </div>

                        <!-- Place Order Button -->
                        <button onclick="proceedToPayment()" class="btn-primary mt-6">
                            <i data-lucide="credit-card" class="w-4 h-4 inline mr-2"></i>
                            Complete Order
                        </button>
                        
                        <a href="cart.php" class="btn-secondary mt-3 w-full text-center">
                            <i data-lucide="arrow-left" class="w-4 h-4 inline mr-2"></i>
                            Back to Cart
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <!-- QR Payment Modal -->
    <div id="qr-modal" class="modal">
        <div class="modal-content">
            <button class="modal-close" onclick="closeQRModal()">&times;</button>
            
            <h3 class="text-xl font-semibold mb-4">Complete Payment</h3>
            <p class="text-gray-600 mb-6">Scan the QR code with your banking app to complete the payment</p>
            
            <div class="mb-6">
                <img src="qrpay.jpg" alt="QR Payment Code" class="w-64 h-64 mx-auto border border-gray-200 rounded-lg">
            </div>
            
            <div class="bg-gray-50 p-4 rounded-lg mb-6">
                <div class="text-sm text-gray-600 mb-2">Amount to Pay</div>
                <div class="text-2xl font-bold text-green-600"><?php echo formatPrice($total); ?></div>
            </div>
            
            <div class="space-y-3">
                <button onclick="confirmPayment()" class="btn-primary">
                    <i data-lucide="check" class="w-4 h-4 inline mr-2"></i>
                    I've Completed Payment
                </button>
                
                <button onclick="closeQRModal()" class="btn-secondary w-full">
                    Cancel
                </button>
            </div>
            
            <p class="text-xs text-gray-500 mt-4">
                Please complete payment within 15 minutes
            </p>
        </div>
    </div>

    <script>
        // Initialize Lucide icons
        lucide.createIcons();

        // Proceed to payment
        function proceedToPayment() {
            const form = document.getElementById('checkout-form');
            const formData = new FormData(form);
            
            // Basic validation
            const email = formData.get('email');
            const firstName = formData.get('first_name');
            const lastName = formData.get('last_name');
            
            if (!email || !firstName || !lastName) {
                alert('Please fill in all required fields');
                return;
            }
            
            // Show QR payment modal
            document.getElementById('qr-modal').classList.add('show');
        }

        // Close QR modal
        function closeQRModal() {
            document.getElementById('qr-modal').classList.remove('show');
        }

        // Confirm payment (simulate order completion)
        function confirmPayment() {
            // In a real app, this would verify payment with the backend
            alert('Payment confirmed! Your order has been placed successfully.');
            
            // Redirect to a success page or orders page
            window.location.href = 'orders.php';
        }

        // Close modal when clicking outside
        document.getElementById('qr-modal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeQRModal();
            }
        });

        // Escape key to close modal
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                closeQRModal();
            }
        });
    </script>
</body>
</html>