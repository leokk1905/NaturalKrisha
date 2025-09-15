<?php
// =============================================
// NATURAL CLOTHING - PRODUCT DETAIL PAGE
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
$productManager = new ProductManager();
$cartManager = new CartManager();

// Get product ID from URL
$productId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$productSlug = isset($_GET['slug']) ? $_GET['slug'] : null;

// Get product data
if ($productId) {
    $product = $productManager->getProductById($productId);
} elseif ($productSlug) {
    $product = $productManager->getProductBySlug($productSlug);
} else {
    header('Location: collections.php');
    exit;
}

if (!$product) {
    header('Location: collections.php');
    exit;
}

// Get product variants and format them
require_once __DIR__ . '/api/product_helpers.php';
$rawVariants = $productManager->getProductVariants($product['id']);
$variants = array_map('formatVariant', $rawVariants);

// Debug: Output variant data
if (!empty($variants)) {
    error_log("Product {$product['id']} has " . count($variants) . " variants: " . json_encode($variants));
}

// Calculate in_stock status for product
$product['in_stock'] = (int)$product['stock_quantity'] > 0;

// Get cart count for header
$userId = $_SESSION['user_id'] ?? null;
$sessionId = $_SESSION['guest_session_id'] ?? null;
$cartCount = $cartManager->getCartItemCount($userId, $sessionId);

// Check if user is logged in
$isLoggedIn = isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true;
$userName = $_SESSION['user_name'] ?? null;

// Process product images
$productImages = [];
if ($product['images']) {
    $productImages = explode(',', $product['images']);
} else {
    $productImages = [$product['primary_image']];
}

// Process categories
$productCategories = [];
if ($product['categories']) {
    $productCategories = explode(',', $product['categories']);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($product['name']); ?> - Natural</title>
    
    <!-- Meta tags for SEO -->
    <meta name="description" content="<?php echo htmlspecialchars($product['meta_description'] ?: $product['short_description']); ?>">
    <meta name="keywords" content="sustainable clothing, <?php echo htmlspecialchars($product['name']); ?>, natural fashion">
    
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
        
        .product-gallery {
            position: sticky;
            top: 6rem;
        }
        
        .main-image {
            aspect-ratio: 4/5;
            overflow: hidden;
            border-radius: var(--radius-lg);
            background: var(--card);
            border: 1px solid var(--border);
            margin-bottom: 1rem;
        }
        
        .thumbnail-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 0.5rem;
        }
        
        .thumbnail {
            aspect-ratio: 1;
            overflow: hidden;
            border-radius: var(--radius-md);
            border: 2px solid transparent;
            cursor: pointer;
            transition: all 200ms ease-out;
        }
        
        .thumbnail.active {
            border-color: var(--primary);
        }
        
        .thumbnail:hover {
            transform: scale(1.05);
        }
        
        .img-cover {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        
        .product-info {
            max-width: 500px;
        }
        
        .variant-selector {
            margin: 1.5rem 0;
        }
        
        .variant-option {
            display: inline-block;
            padding: 0.5rem 1rem;
            margin: 0.25rem;
            border: 1px solid var(--border);
            border-radius: var(--radius-md);
            background: var(--card);
            cursor: pointer;
            transition: all 200ms ease-out;
            font-size: 0.875rem;
        }
        
        .variant-option:hover {
            border-color: var(--primary);
        }
        
        .variant-option.selected {
            background: var(--primary);
            color: var(--primary-foreground);
            border-color: var(--primary);
        }
        
        .variant-option.disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }
        
        .quantity-selector {
            display: flex;
            align-items: center;
            gap: 1rem;
            margin: 1.5rem 0;
        }
        
        .quantity-controls {
            display: flex;
            align-items: center;
            border: 1px solid var(--border);
            border-radius: var(--radius-md);
            background: var(--card);
        }
        
        .quantity-btn {
            width: 3rem;
            height: 3rem;
            display: flex;
            align-items: center;
            justify-content: center;
            border: none;
            background: none;
            cursor: pointer;
            transition: all 200ms ease-out;
            color: var(--foreground);
        }
        
        .quantity-btn:hover:not(:disabled) {
            background: var(--muted);
        }
        
        .quantity-btn:disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }
        
        .quantity-input {
            width: 4rem;
            height: 3rem;
            text-align: center;
            border: none;
            background: none;
            font-size: 1rem;
            font-weight: 500;
        }
        
        .add-to-cart-btn {
            width: 100%;
            padding: 1rem 2rem;
            border-radius: var(--radius-md);
            background: var(--primary);
            color: var(--primary-foreground);
            font-weight: 500;
            letter-spacing: 0.025em;
            transition: all 200ms ease-out;
            border: none;
            cursor: pointer;
            font-size: 1rem;
            margin: 1.5rem 0;
        }
        
        .add-to-cart-btn:hover:not(:disabled) {
            transform: scale(1.02);
            box-shadow: var(--shadow-md);
        }
        
        .add-to-cart-btn:disabled {
            opacity: 0.6;
            cursor: not-allowed;
        }
        
        .product-features {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
            margin: 2rem 0;
        }
        
        .feature-item {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding: 1rem;
            background: var(--muted);
            border-radius: var(--radius-md);
            font-size: 0.875rem;
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
        
        .product-tabs {
            margin-top: 3rem;
        }
        
        .tab-buttons {
            display: flex;
            border-bottom: 1px solid var(--border);
            margin-bottom: 2rem;
        }
        
        .tab-button {
            padding: 1rem 1.5rem;
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
        
        .tab-content {
            display: none;
        }
        
        .tab-content.active {
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
        
        .price-display {
            font-size: 1.5rem;
            font-weight: 600;
            color: var(--primary);
            margin: 1rem 0;
        }
        
        .compare-price {
            font-size: 1.25rem;
            color: var(--muted-foreground);
            text-decoration: line-through;
            margin-left: 0.5rem;
        }
        
        .stock-indicator {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            margin: 1rem 0;
            font-size: 0.875rem;
        }
        
        .stock-indicator.in-stock {
            color: rgb(34, 197, 94);
        }
        
        .stock-indicator.low-stock {
            color: rgb(234, 179, 8);
        }
        
        .stock-indicator.out-of-stock {
            color: rgb(239, 68, 68);
        }
        
        .loading-overlay {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(255, 255, 255, 0.8);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 9999;
            opacity: 0;
            visibility: hidden;
            transition: all 300ms ease-out;
        }
        
        .loading-overlay.show {
            opacity: 1;
            visibility: visible;
        }
        
        .spinner {
            width: 40px;
            height: 40px;
            border: 3px solid var(--border);
            border-top: 3px solid var(--primary);
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }
        
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        
        @media (max-width: 768px) {
            .product-layout {
                flex-direction: column;
            }
            
            .product-gallery {
                position: static;
                margin-bottom: 2rem;
            }
            
            .thumbnail-grid {
                grid-template-columns: repeat(6, 1fr);
            }
        }
    </style>
</head>
<body style="font-family: var(--font-sans); background: var(--background); color: var(--foreground); line-height: 1.6;">

    <!-- Loading Overlay -->
    <div class="loading-overlay" id="loading-overlay">
        <div class="spinner"></div>
    </div>

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
                <a href="collections.php">Collections</a>
                <?php if (!empty($productCategories)): ?>
                    <i data-lucide="chevron-right" class="w-4 h-4"></i>
                    <a href="collections.php?category=<?php echo urlencode(strtolower($productCategories[0])); ?>">
                        <?php echo htmlspecialchars($productCategories[0]); ?>
                    </a>
                <?php endif; ?>
                <i data-lucide="chevron-right" class="w-4 h-4"></i>
                <span><?php echo htmlspecialchars($product['name']); ?></span>
            </div>

            <!-- Product Layout -->
            <div class="product-layout flex flex-col lg:flex-row gap-12">
                <!-- Product Gallery -->
                <div class="flex-1">
                    <div class="product-gallery">
                        <!-- Main Image -->
                        <div class="main-image">
                            <img id="main-image" 
                                 src="<?php echo htmlspecialchars($productImages[0]); ?>" 
                                 alt="<?php echo htmlspecialchars($product['name']); ?>" 
                                 class="img-cover">
                        </div>
                        
                        <!-- Thumbnail Grid -->
                        <?php if (count($productImages) > 1): ?>
                            <div class="thumbnail-grid">
                                <?php foreach ($productImages as $index => $image): ?>
                                    <div class="thumbnail <?php echo $index === 0 ? 'active' : ''; ?>" 
                                         onclick="changeMainImage('<?php echo htmlspecialchars($image); ?>', this)">
                                        <img src="<?php echo htmlspecialchars($image); ?>" 
                                             alt="<?php echo htmlspecialchars($product['name']); ?> - Image <?php echo $index + 1; ?>" 
                                             class="img-cover">
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Product Information -->
                <div class="flex-1">
                    <div class="product-info">
                        <!-- Product Title -->
                        <h1 class="text-3xl font-serif font-light mb-4"><?php echo htmlspecialchars($product['name']); ?></h1>
                        
                        <!-- Product Price -->
                        <div class="price-display">
                            <span id="current-price"><?php echo formatPrice($product['price'], $product['currency']); ?></span>
                            <?php if ($product['compare_at_price']): ?>
                                <span class="compare-price"><?php echo formatPrice($product['compare_at_price'], $product['currency']); ?></span>
                            <?php endif; ?>
                        </div>

                        <!-- Stock Indicator -->
                        <div class="stock-indicator <?php echo $product['stock_quantity'] > 10 ? 'in-stock' : ($product['stock_quantity'] > 0 ? 'low-stock' : 'out-of-stock'); ?>" id="stock-indicator">
                            <i data-lucide="<?php echo $product['stock_quantity'] > 0 ? 'check-circle' : 'x-circle'; ?>" class="w-4 h-4"></i>
                            <span id="stock-text">
                                <?php 
                                if ($product['stock_quantity'] > 10) {
                                    echo 'In Stock';
                                } elseif ($product['stock_quantity'] > 0) {
                                    echo 'Only ' . $product['stock_quantity'] . ' left in stock';
                                } else {
                                    echo 'Out of Stock';
                                }
                                ?>
                            </span>
                        </div>

                        <!-- Short Description -->
                        <p class="text-gray-600 mb-6"><?php echo htmlspecialchars($product['short_description']); ?></p>

                        <!-- Sustainability Badge -->
                        <?php if ($product['is_sustainable']): ?>
                            <div class="flex items-center gap-2 mb-6 p-3 bg-green-50 rounded-lg">
                                <i data-lucide="leaf" class="w-5 h-5 text-green-600"></i>
                                <span class="text-green-800 font-medium">Sustainably Made</span>
                            </div>
                        <?php endif; ?>

                        <!-- Variant Selection -->
                        <?php if (!empty($variants)): ?>
                            <div class="variant-selector">
                                <h3 class="font-semibold mb-3">Select Options:</h3>
                                <div id="variant-options">
                                    <?php foreach ($variants as $variant): ?>
                                        <button class="variant-option <?php echo !$variant['in_stock'] ? 'disabled' : ''; ?>" 
                                                data-variant-id="<?php echo $variant['id']; ?>"
                                                data-variant-price="<?php echo $variant['price'] ?: $product['price']; ?>"
                                                data-variant-stock="<?php echo $variant['stock_quantity']; ?>"
                                                onclick="selectVariant(this)"
                                                <?php echo !$variant['in_stock'] ? 'disabled' : ''; ?>>
                                            <?php echo htmlspecialchars($variant['name']); ?>
                                            <?php if (!$variant['in_stock']): ?>
                                                (Out of Stock)
                                            <?php endif; ?>
                                        </button>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        <?php endif; ?>

                        <!-- Quantity Selector -->
                        <div class="quantity-selector">
                            <label for="quantity" class="font-semibold">Quantity:</label>
                            <div class="quantity-controls">
                                <button class="quantity-btn" onclick="changeQuantity(-1)">
                                    <i data-lucide="minus" class="w-4 h-4"></i>
                                </button>
                                <input type="number" 
                                       id="quantity" 
                                       class="quantity-input" 
                                       value="1" 
                                       min="1" 
                                       max="<?php echo $product['stock_quantity']; ?>">
                                <button class="quantity-btn" onclick="changeQuantity(1)">
                                    <i data-lucide="plus" class="w-4 h-4"></i>
                                </button>
                            </div>
                        </div>

                        <!-- Add to Cart Button -->
                        <button class="add-to-cart-btn" 
                                onclick="addToCart()" 
                                id="add-to-cart-btn"
                                <?php echo !$product['in_stock'] ? 'disabled' : ''; ?>
                                <?php echo !empty($variants) ? 'disabled' : ''; ?>>
                            <span id="add-to-cart-text">
                                <?php 
                                if (!$product['in_stock']) {
                                    echo 'Out of Stock';
                                } elseif (!empty($variants)) {
                                    echo 'Select Options First';
                                } else {
                                    echo 'Add to Cart';
                                }
                                ?>
                            </span>
                        </button>

                        <!-- Product Features -->
                        <div class="product-features">
                            <div class="feature-item">
                                <i data-lucide="truck" class="w-5 h-5 text-green-600"></i>
                                <span>Free shipping over ฿1500</span>
                            </div>
                            <div class="feature-item">
                                <i data-lucide="refresh-cw" class="w-5 h-5 text-blue-600"></i>
                                <span>30-day returns</span>
                            </div>
                            <div class="feature-item">
                                <i data-lucide="shield-check" class="w-5 h-5 text-purple-600"></i>
                                <span>1-year warranty</span>
                            </div>
                            <div class="feature-item">
                                <i data-lucide="leaf" class="w-5 h-5 text-green-600"></i>
                                <span>Eco-friendly materials</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Product Details Tabs -->
            <div class="product-tabs">
                <div class="tab-buttons">
                    <button class="tab-button active" onclick="switchTab('description')">Description</button>
                    <button class="tab-button" onclick="switchTab('details')">Details</button>
                    <button class="tab-button" onclick="switchTab('care')">Care Instructions</button>
                    <?php if ($product['sustainability_info']): ?>
                        <button class="tab-button" onclick="switchTab('sustainability')">Sustainability</button>
                    <?php endif; ?>
                </div>

                <!-- Description Tab -->
                <div class="tab-content active" id="description-tab">
                    <div class="prose max-w-none">
                        <?php echo nl2br(htmlspecialchars($product['description'])); ?>
                    </div>
                </div>

                <!-- Details Tab -->
                <div class="tab-content" id="details-tab">
                    <div class="grid md:grid-cols-2 gap-6">
                        <div>
                            <h4 class="font-semibold mb-3">Product Details</h4>
                            <ul class="space-y-2 text-sm">
                                <li><strong>SKU:</strong> <?php echo htmlspecialchars($product['sku']); ?></li>
                                <?php if ($product['material_composition']): ?>
                                    <li><strong>Material:</strong> <?php echo htmlspecialchars($product['material_composition']); ?></li>
                                <?php endif; ?>
                                <?php if ($product['weight']): ?>
                                    <li><strong>Weight:</strong> <?php echo $product['weight']; ?>g</li>
                                <?php endif; ?>
                                <?php if ($product['dimensions']): ?>
                                    <li><strong>Dimensions:</strong> <?php echo htmlspecialchars($product['dimensions']); ?></li>
                                <?php endif; ?>
                            </ul>
                        </div>
                        <?php if (!empty($variants)): ?>
                            <div>
                                <h4 class="font-semibold mb-3">Available Options</h4>
                                <ul class="space-y-2 text-sm">
                                    <?php foreach ($variants as $variant): ?>
                                        <li class="flex justify-between">
                                            <span><?php echo htmlspecialchars($variant['name']); ?></span>
                                            <span class="<?php echo $variant['in_stock'] ? 'text-green-600' : 'text-red-600'; ?>">
                                                <?php echo $variant['in_stock'] ? 'In Stock' : 'Out of Stock'; ?>
                                            </span>
                                        </li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Care Instructions Tab -->
                <div class="tab-content" id="care-tab">
                    <div class="prose max-w-none">
                        <?php if ($product['care_instructions']): ?>
                            <?php echo nl2br(htmlspecialchars($product['care_instructions'])); ?>
                        <?php else: ?>
                            <p>Care instructions will be provided with your purchase.</p>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Sustainability Tab -->
                <?php if ($product['sustainability_info']): ?>
                    <div class="tab-content" id="sustainability-tab">
                        <div class="prose max-w-none">
                            <?php echo nl2br(htmlspecialchars($product['sustainability_info'])); ?>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </main>

    <script>
        // Initialize Lucide icons
        lucide.createIcons();

        let selectedVariantId = null;
        let currentPrice = <?php echo $product['price']; ?>;
        let currentStock = <?php echo $product['stock_quantity']; ?>;

        // Change main image
        function changeMainImage(imageSrc, thumbnail) {
            document.getElementById('main-image').src = imageSrc;
            
            // Update active thumbnail
            document.querySelectorAll('.thumbnail').forEach(thumb => thumb.classList.remove('active'));
            thumbnail.classList.add('active');
        }

        // Select variant
        function selectVariant(button) {
            console.log('selectVariant called with button:', button);
            
            if (button.disabled) {
                console.log('Button is disabled, returning');
                return;
            }

            // Remove previous selection
            document.querySelectorAll('.variant-option').forEach(opt => opt.classList.remove('selected'));
            
            // Select current variant
            button.classList.add('selected');
            
            // Set the selected variant ID
            selectedVariantId = parseInt(button.dataset.variantId);
            const variantPrice = parseFloat(button.dataset.variantPrice);
            const variantStock = parseInt(button.dataset.variantStock);
            
            console.log('Variant data:', {
                selectedVariantId,
                variantPrice,
                variantStock,
                buttonDataset: button.dataset
            });
            
            // Update price
            currentPrice = variantPrice;
            const priceElement = document.getElementById('current-price');
            if (priceElement) {
                priceElement.textContent = formatPrice(variantPrice);
            }
            
            // Update stock
            currentStock = variantStock;
            updateStockIndicator(variantStock);
            
            // Update quantity max
            const quantityInput = document.getElementById('quantity');
            if (quantityInput) {
                quantityInput.max = variantStock;
                if (parseInt(quantityInput.value) > variantStock) {
                    quantityInput.value = Math.max(1, variantStock);
                }
            }
            
            // Update add to cart button - variant is now selected
            console.log('About to call updateAddToCartButton with:', {
                stock: variantStock,
                stockGreaterThanZero: variantStock > 0,
                variantSelected: true
            });
            
            updateAddToCartButton(variantStock > 0, true);
        }

        // Update stock indicator
        function updateStockIndicator(stock) {
            const indicator = document.getElementById('stock-indicator');
            const text = document.getElementById('stock-text');
            
            console.log('updateStockIndicator called:', { 
                stock, 
                indicatorExists: !!indicator, 
                textExists: !!text 
            });
            
            if (!indicator || !text) {
                console.error('Stock indicator elements not found:', { indicator, text });
                return;
            }
            
            const icon = indicator.querySelector('i');
            console.log('Icon element found:', !!icon);
            
            indicator.className = 'stock-indicator';
            
            if (stock > 10) {
                indicator.classList.add('in-stock');
                if (icon) {
                    icon.setAttribute('data-lucide', 'check-circle');
                }
                text.textContent = 'In Stock';
            } else if (stock > 0) {
                indicator.classList.add('low-stock');
                if (icon) {
                    icon.setAttribute('data-lucide', 'alert-circle');
                }
                text.textContent = `Only ${stock} left in stock`;
            } else {
                indicator.classList.add('out-of-stock');
                if (icon) {
                    icon.setAttribute('data-lucide', 'x-circle');
                }
                text.textContent = 'Out of Stock';
            }
            
            if (typeof lucide !== 'undefined') {
                lucide.createIcons();
            }
        }

        // Update add to cart button
        function updateAddToCartButton(inStock, variantSelected = false) {
            const button = document.getElementById('add-to-cart-btn');
            const text = document.getElementById('add-to-cart-text');
            
            console.log('updateAddToCartButton called:', { 
                inStock, 
                variantSelected, 
                selectedVariantId,
                buttonExists: !!button,
                textExists: !!text
            });
            
            if (!button || !text) {
                console.error('Button or text element not found!');
                return;
            }
            
            if (!inStock) {
                button.disabled = true;
                text.textContent = 'Out of Stock';
                console.log('Button disabled: Out of stock');
            } else if (variantSelected && selectedVariantId) {
                button.disabled = false;
                text.textContent = 'Add to Cart';
                console.log('Button enabled: Variant selected with ID:', selectedVariantId);
            } else {
                // Check if variants exist
                const hasVariants = document.querySelectorAll('.variant-option').length > 0;
                console.log('Has variants:', hasVariants, 'Variant count:', document.querySelectorAll('.variant-option').length);
                
                if (hasVariants) {
                    button.disabled = true;
                    text.textContent = 'Select Options First';
                    console.log('Button disabled: Has variants but none selected');
                } else {
                    button.disabled = false;
                    text.textContent = 'Add to Cart';
                    console.log('Button enabled: No variants needed');
                }
            }
        }

        // Change quantity
        function changeQuantity(delta) {
            const quantityInput = document.getElementById('quantity');
            const currentQuantity = parseInt(quantityInput.value);
            const newQuantity = Math.max(1, Math.min(currentStock, currentQuantity + delta));
            
            quantityInput.value = newQuantity;
        }

        // Add to cart
        function addToCart() {
            const quantity = parseInt(document.getElementById('quantity').value);
            
            // Check if variants exist and one is selected
            const hasVariants = document.querySelectorAll('.variant-option').length > 0;
            if (hasVariants && !selectedVariantId) {
                showNotification('Please select size/options first', 'error');
                return;
            }
            
            if (quantity < 1 || quantity > currentStock) {
                showNotification('Please select a valid quantity', 'error');
                return;
            }

            const loadingOverlay = document.getElementById('loading-overlay');
            loadingOverlay.classList.add('show');

            const cartData = {
                product_id: <?php echo $product['id']; ?>,
                quantity: quantity
            };

            if (selectedVariantId) {
                cartData.variant_id = parseInt(selectedVariantId);
            }

            fetch('api/cart.php?action=add', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify(cartData)
            })
            .then(response => response.json())
            .then(data => {
                loadingOverlay.classList.remove('show');
                
                if (data.success) {
                    // Update cart count
                    document.getElementById('cart-count').textContent = data.cart_count;
                    
                    // Show success message
                    showNotification('Product added to cart!', 'success');
                    
                    // Optional: Show quick cart preview or redirect
                    // window.location.href = 'cart.php';
                } else {
                    showNotification(data.message || 'Failed to add product to cart', 'error');
                }
            })
            .catch(error => {
                loadingOverlay.classList.remove('show');
                console.error('Add to cart error:', error);
                showNotification('Error adding product to cart', 'error');
            });
        }

        // Switch tabs
        function switchTab(tabName) {
            // Update tab buttons
            document.querySelectorAll('.tab-button').forEach(btn => btn.classList.remove('active'));
            event.target.classList.add('active');
            
            // Update tab content
            document.querySelectorAll('.tab-content').forEach(content => content.classList.remove('active'));
            document.getElementById(tabName + '-tab').classList.add('active');
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
            // Update cart count
            fetch('api/cart.php?action=count')
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    document.getElementById('cart-count').textContent = data.cart_count;
                }
            })
            .catch(error => console.error('Cart count update error:', error));

            // Add quantity input validation
            document.getElementById('quantity').addEventListener('change', function() {
                const value = parseInt(this.value);
                if (value < 1) {
                    this.value = 1;
                } else if (value > currentStock) {
                    this.value = currentStock;
                }
            });

            // Initialize button state
            const hasVariants = document.querySelectorAll('.variant-option').length > 0;
            if (hasVariants) {
                updateAddToCartButton(currentStock > 0, false);
            } else {
                updateAddToCartButton(currentStock > 0, true);
            }
        });
    </script>
</body>
</html>