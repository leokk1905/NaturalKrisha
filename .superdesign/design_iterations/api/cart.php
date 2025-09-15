<?php
// =============================================
// SHOPPING CART API ENDPOINTS
// Natural Clothing Website
// =============================================

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

require_once __DIR__ . '/Database.php';
require_once __DIR__ . '/../config.php';

// Start session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Initialize guest session if needed
if (!isset($_SESSION['user_id']) && !isset($_SESSION['guest_session_id'])) {
    $_SESSION['guest_session_id'] = uniqid('guest_', true);
}

$cartManager = new CartManager();
$productManager = new ProductManager();

// Get request parameters
$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? '';


try {
    switch ($action) {
        case 'add':
            if ($method !== 'POST') {
                throw new Exception('Method not allowed');
            }
            handleAddToCart();
            break;
            
        case 'update':
            if ($method !== 'PUT' && $method !== 'POST') {
                throw new Exception('Method not allowed');
            }
            handleUpdateCart();
            break;
            
        case 'remove':
            if ($method !== 'DELETE' && $method !== 'POST') {
                throw new Exception('Method not allowed');
            }
            handleRemoveFromCart();
            break;
            
        case 'clear':
            if ($method !== 'DELETE' && $method !== 'POST') {
                throw new Exception('Method not allowed');
            }
            handleClearCart();
            break;
            
        case 'items':
        case 'list':
            handleGetCartItems();
            break;
            
        case 'count':
            handleGetCartCount();
            break;
            
        case 'total':
            handleGetCartTotal();
            break;
            
        default:
            handleGetCartItems();
    }
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}

function handleAddToCart() {
    global $cartManager, $productManager;
    
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!$input) {
        $input = $_POST;
    }
    
    $productId = (int)($input['product_id'] ?? 0);
    $variantId = isset($input['variant_id']) ? (int)$input['variant_id'] : null;
    $quantity = (int)($input['quantity'] ?? 1);
    
    if ($productId <= 0) {
        throw new Exception('Valid product ID required');
    }
    
    if ($quantity <= 0) {
        throw new Exception('Quantity must be greater than 0');
    }
    
    // Verify product exists and is active
    $product = $productManager->getProductById($productId);
    if (!$product) {
        throw new Exception('Product not found');
    }
    
    // If variant specified, verify it exists
    $price = $product['price'];
    if ($variantId) {
        $variants = $productManager->getProductVariants($productId);
        $variant = array_filter($variants, function($v) use ($variantId) {
            return $v['id'] == $variantId;
        });
        
        if (empty($variant)) {
            throw new Exception('Product variant not found');
        }
        
        $variant = reset($variant);
        if ($variant['price']) {
            $price = $variant['price'];
        }
        
        // Check variant stock
        if ($variant['stock_quantity'] < $quantity) {
            throw new Exception('Insufficient stock for selected variant');
        }
    } else {
        // Check product stock
        if ($product['stock_quantity'] < $quantity) {
            throw new Exception('Insufficient stock');
        }
    }
    
    $userId = $_SESSION['user_id'] ?? null;
    $sessionId = $_SESSION['guest_session_id'] ?? null;
    
    $cartItemId = $cartManager->addToCart($userId, $sessionId, $productId, $variantId, $quantity, $price);
    
    if ($cartItemId) {
        // Get updated cart info
        $cartCount = $cartManager->getCartItemCount($userId, $sessionId);
        $cartTotal = $cartManager->getCartTotal($userId, $sessionId);
        
        echo json_encode([
            'success' => true,
            'message' => 'Item added to cart',
            'cart_item_id' => $cartItemId,
            'cart_count' => $cartCount,
            'cart_total' => $cartTotal,
            'formatted_total' => formatPrice($cartTotal)
        ]);
    } else {
        throw new Exception('Failed to add item to cart');
    }
}

function handleUpdateCart() {
    global $cartManager;
    
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!$input) {
        $input = $_POST;
    }
    
    $cartItemId = (int)($input['cart_item_id'] ?? 0);
    $quantity = (int)($input['quantity'] ?? 0);
    
    if ($cartItemId <= 0) {
        throw new Exception('Valid cart item ID required');
    }
    
    if ($quantity < 0) {
        throw new Exception('Quantity cannot be negative');
    }
    
    $result = $cartManager->updateCartItemQuantity($cartItemId, $quantity);
    
    if ($result !== false) {
        $userId = $_SESSION['user_id'] ?? null;
        $sessionId = $_SESSION['guest_session_id'] ?? null;
        
        $cartCount = $cartManager->getCartItemCount($userId, $sessionId);
        $cartTotal = $cartManager->getCartTotal($userId, $sessionId);
        
        echo json_encode([
            'success' => true,
            'message' => $quantity > 0 ? 'Cart updated' : 'Item removed from cart',
            'cart_count' => $cartCount,
            'cart_total' => $cartTotal,
            'formatted_total' => formatPrice($cartTotal)
        ]);
    } else {
        throw new Exception('Failed to update cart');
    }
}

function handleRemoveFromCart() {
    global $cartManager;
    
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!$input) {
        $input = $_POST;
    }
    
    $cartItemId = (int)($input['cart_item_id'] ?? $_GET['id'] ?? 0);
    
    if ($cartItemId <= 0) {
        throw new Exception('Valid cart item ID required');
    }
    
    $result = $cartManager->removeCartItem($cartItemId);
    
    if ($result) {
        $userId = $_SESSION['user_id'] ?? null;
        $sessionId = $_SESSION['guest_session_id'] ?? null;
        
        $cartCount = $cartManager->getCartItemCount($userId, $sessionId);
        $cartTotal = $cartManager->getCartTotal($userId, $sessionId);
        
        echo json_encode([
            'success' => true,
            'message' => 'Item removed from cart',
            'cart_count' => $cartCount,
            'cart_total' => $cartTotal,
            'formatted_total' => formatPrice($cartTotal)
        ]);
    } else {
        throw new Exception('Failed to remove item from cart');
    }
}

function handleClearCart() {
    global $cartManager;
    
    $userId = $_SESSION['user_id'] ?? null;
    $sessionId = $_SESSION['guest_session_id'] ?? null;
    
    $result = $cartManager->clearCart($userId, $sessionId);
    
    echo json_encode([
        'success' => true,
        'message' => 'Cart cleared',
        'cart_count' => 0,
        'cart_total' => 0,
        'formatted_total' => formatPrice(0)
    ]);
}

function handleGetCartItems() {
    global $cartManager;
    
    $userId = $_SESSION['user_id'] ?? null;
    $sessionId = $_SESSION['guest_session_id'] ?? null;
    
    $cartItems = $cartManager->getCartItems($userId, $sessionId);
    $cartTotal = $cartManager->getCartTotal($userId, $sessionId);
    $cartCount = $cartManager->getCartItemCount($userId, $sessionId);
    
    // Format cart items
    $formattedItems = array_map('formatCartItem', $cartItems);
    
    echo json_encode([
        'success' => true,
        'cart_items' => $formattedItems,
        'cart_count' => $cartCount,
        'cart_total' => $cartTotal,
        'formatted_total' => formatPrice($cartTotal),
        'subtotal' => $cartTotal,
        'formatted_subtotal' => formatPrice($cartTotal)
    ]);
}

function handleGetCartCount() {
    global $cartManager;
    
    $userId = $_SESSION['user_id'] ?? null;
    $sessionId = $_SESSION['guest_session_id'] ?? null;
    
    $cartCount = $cartManager->getCartItemCount($userId, $sessionId);
    
    echo json_encode([
        'success' => true,
        'cart_count' => $cartCount
    ]);
}

function handleGetCartTotal() {
    global $cartManager;
    
    $userId = $_SESSION['user_id'] ?? null;
    $sessionId = $_SESSION['guest_session_id'] ?? null;
    
    $cartTotal = $cartManager->getCartTotal($userId, $sessionId);
    
    echo json_encode([
        'success' => true,
        'cart_total' => $cartTotal,
        'formatted_total' => formatPrice($cartTotal)
    ]);
}

function formatCartItem($item) {
    return [
        'id' => (int)$item['id'],
        'product_id' => (int)$item['product_id'],
        'variant_id' => $item['variant_id'] ? (int)$item['variant_id'] : null,
        'product_name' => $item['product_name'],
        'product_slug' => $item['product_slug'],
        'variant_name' => $item['variant_name'],
        'quantity' => (int)$item['quantity'],
        'price' => (float)$item['price'],
        'formatted_price' => formatPrice($item['price']),
        'total' => (float)($item['quantity'] * $item['price']),
        'formatted_total' => formatPrice($item['quantity'] * $item['price']),
        'product_image' => $item['product_image'] ?: 'https://via.placeholder.com/150x200?text=No+Image',
        'created_at' => $item['created_at'],
        'updated_at' => $item['updated_at']
    ];
}

?>