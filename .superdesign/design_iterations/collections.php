<?php
// =============================================
// NATURAL CLOTHING - DYNAMIC COLLECTIONS PAGE
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

// Get parameters
$category = $_GET['category'] ?? null;
$search = $_GET['search'] ?? null;
$limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 12;
$offset = isset($_GET['offset']) ? (int)$_GET['offset'] : 0;

// Get products based on filters
if ($search) {
    $products = $productManager->searchProducts($search, $limit);
    $pageTitle = "Search Results for \"" . htmlspecialchars($search ?? '') . "\"";
} elseif ($category) {
    $products = $productManager->getProductsByCategory($category, $limit);
    $pageTitle = ucfirst(str_replace('-', ' ', $category));
} else {
    $products = $productManager->getAllProducts($limit, $offset);
    $pageTitle = "All Collections";
}

// Get cart count for header
$userId = $_SESSION['user_id'] ?? null;
$sessionId = $_SESSION['guest_session_id'] ?? null;
$cartCount = $cartManager->getCartItemCount($userId, $sessionId);

// Check if user is logged in
$isLoggedIn = isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true;
$userName = $_SESSION['user_name'] ?? null;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($pageTitle); ?> - Natural</title>
    
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
        
        .grid-consistent {
            display: grid;
            gap: 1.5rem;
        }
        
        .grid-2 { grid-template-columns: repeat(2, 1fr); }
        .grid-3 { grid-template-columns: repeat(3, 1fr); }
        .grid-4 { grid-template-columns: repeat(4, 1fr); }
        
        @media (max-width: 768px) {
            .grid-2, .grid-3, .grid-4 { grid-template-columns: 1fr; }
            .grid-responsive { grid-template-columns: repeat(2, 1fr); }
        }
        
        @media (min-width: 769px) and (max-width: 1024px) {
            .grid-4 { grid-template-columns: repeat(3, 1fr); }
            .grid-3 { grid-template-columns: repeat(2, 1fr); }
        }
        
        .card-consistent {
            display: flex;
            flex-direction: column;
            border-radius: var(--radius-md);
            background: var(--card);
            border: 1px solid var(--border);
            min-height: 420px;
            transition: all 300ms ease-out;
        }
        
        .card-consistent:hover {
            transform: translateY(-4px);
            box-shadow: var(--shadow-lg);
        }
        
        .card-consistent .product-image {
            height: 280px;
            overflow: hidden;
            flex-shrink: 0;
            border-radius: var(--radius-md) var(--radius-md) 0 0;
        }
        
        .card-consistent .product-content {
            padding: 1.25rem;
            display: flex;
            flex-direction: column;
            flex-grow: 1;
            justify-content: space-between;
        }
        
        .img-cover {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 300ms ease-out;
        }
        
        .card-consistent:hover .img-cover {
            transform: scale(1.05);
        }
        
        .filter-btn {
            padding: 0.5rem 1rem;
            border-radius: var(--radius-md);
            background: transparent;
            color: var(--foreground);
            border: 1px solid var(--border);
            cursor: pointer;
            transition: all 200ms ease-out;
            font-size: 0.875rem;
        }
        
        .filter-btn:hover, .filter-btn.active {
            background: var(--primary);
            color: var(--primary-foreground);
            border-color: var(--primary);
        }
        
        .search-input {
            padding: 0.75rem 1rem;
            border-radius: var(--radius-md);
            border: 1px solid var(--border);
            background: var(--background);
            color: var(--foreground);
            outline: none;
            transition: all 200ms ease-out;
        }
        
        .search-input:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 2px rgba(134, 155, 121, 0.2);
        }
        
        .btn-primary {
            padding: 0.75rem 1.5rem;
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
        
        .btn-primary:hover {
            transform: scale(1.02);
            box-shadow: var(--shadow-md);
        }
        
        .btn-secondary {
            padding: 0.75rem 1.5rem;
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
        
        .nav-link.active {
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
        
        .nav-link:hover::after, .nav-link.active::after {
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
        
        .fade-in {
            animation: fadeIn 0.6s ease-out;
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
                    <h1 class="text-2xl font-serif font-light natural-text-gradient">Natural</h1>
                </div>
                
                <!-- Navigation Links -->
                <div class="hidden md:flex items-center space-x-8">
                    <a href="index.php" class="nav-link">Home</a>
                    <a href="collections.php" class="nav-link active">Collections</a>
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
                <span><?php echo htmlspecialchars($pageTitle); ?></span>
            </div>

            <!-- Page Header -->
            <div class="text-center mb-12">
                <h1 class="text-4xl font-serif font-light mb-4"><?php echo htmlspecialchars($pageTitle); ?></h1>
                <p class="text-gray-600 max-w-2xl mx-auto">
                    <?php if ($search): ?>
                        Found <?php echo count($products); ?> results for your search
                    <?php else: ?>
                        Discover our sustainable clothing collection crafted with care for you and the planet
                    <?php endif; ?>
                </p>
            </div>

            <!-- Filters & Search -->
            <div class="flex flex-col lg:flex-row gap-6 mb-8">
                <!-- Category Filters -->
                <div class="flex flex-wrap gap-3">
                    <a href="collections.php" class="filter-btn <?php echo !$category ? 'active' : ''; ?>">All</a>
                    <a href="collections.php?category=new-arrivals" class="filter-btn <?php echo $category === 'new-arrivals' ? 'active' : ''; ?>">New Arrivals</a>
                    <a href="collections.php?category=essentials" class="filter-btn <?php echo $category === 'essentials' ? 'active' : ''; ?>">Essentials</a>
                    <a href="collections.php?category=seasonal" class="filter-btn <?php echo $category === 'seasonal' ? 'active' : ''; ?>">Seasonal</a>
                    <a href="collections.php?category=sustainable" class="filter-btn <?php echo $category === 'sustainable' ? 'active' : ''; ?>">Sustainable</a>
                </div>

                <!-- Search -->
                <div class="flex-1 max-w-md lg:ml-auto">
                    <form method="GET" class="flex gap-2">
                        <?php if ($category): ?>
                            <input type="hidden" name="category" value="<?php echo htmlspecialchars($category ?? ''); ?>">
                        <?php endif; ?>
                        <input 
                            type="text" 
                            name="search" 
                            placeholder="Search products..." 
                            value="<?php echo htmlspecialchars($search ?? ''); ?>"
                            class="search-input flex-1"
                        >
                        <button type="submit" class="btn-primary">
                            <i data-lucide="search" class="w-4 h-4"></i>
                        </button>
                    </form>
                </div>
            </div>

            <!-- Products Grid -->
            <div class="grid-consistent grid-4 fade-in" id="products-grid">
                <?php if (empty($products)): ?>
                    <div class="col-span-full text-center py-12">
                        <i data-lucide="search-x" class="w-16 h-16 mx-auto mb-4 text-gray-400"></i>
                        <h3 class="text-xl font-semibold mb-2">No products found</h3>
                        <p class="text-gray-600 mb-6">
                            <?php if ($search): ?>
                                Try adjusting your search terms or browse all collections
                            <?php else: ?>
                                Check back soon for new arrivals in this category
                            <?php endif; ?>
                        </p>
                        <a href="collections.php" class="btn-primary">Browse All Collections</a>
                    </div>
                <?php else: ?>
                    <?php foreach ($products as $product): 
                        $inStock = (int)$product['stock_quantity'] > 0;
                        $primaryImage = $product['primary_image'] ?: 'https://via.placeholder.com/400x500?text=No+Image';
                    ?>
                        <div class="card-consistent" data-product-id="<?php echo $product['id']; ?>">
                            <div class="product-image">
                                <img src="<?php echo htmlspecialchars($primaryImage); ?>" 
                                     alt="<?php echo htmlspecialchars($product['name']); ?>" 
                                     class="img-cover">
                            </div>
                            <div class="product-content">
                                <div>
                                    <h3 class="font-semibold mb-2"><?php echo htmlspecialchars($product['name']); ?></h3>
                                    <p class="text-sm text-gray-600 mb-3"><?php echo htmlspecialchars($product['short_description']); ?></p>
                                    <?php if ($product['is_sustainable']): ?>
                                        <div class="flex items-center gap-1 mb-2">
                                            <i data-lucide="leaf" class="w-4 h-4 text-green-600"></i>
                                            <span class="text-xs text-green-600 font-medium">Sustainable</span>
                                        </div>
                                    <?php endif; ?>
                                </div>
                                <div class="flex justify-between items-center">
                                    <div>
                                        <span class="font-semibold text-lg" style="color: var(--primary);">
                                            <?php echo formatPrice($product['price'], $product['currency']); ?>
                                        </span>
                                        <?php if ($product['compare_at_price']): ?>
                                            <span class="text-sm text-gray-500 line-through ml-2">
                                                <?php echo formatPrice($product['compare_at_price'], $product['currency']); ?>
                                            </span>
                                        <?php endif; ?>
                                    </div>
                                    <div class="flex gap-2">
                                        <a href="product_detail.php?id=<?php echo $product['id']; ?>" class="btn-primary text-sm px-3 py-2 w-full text-center">
                                            View Details
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>

            <!-- Load More Button (if needed) -->
            <?php if (count($products) >= $limit): ?>
                <div class="text-center mt-12">
                    <button 
                        onclick="loadMore()" 
                        class="btn-secondary px-8 py-3"
                        id="load-more-btn"
                    >
                        Load More Products
                    </button>
                </div>
            <?php endif; ?>
        </div>
    </main>

    <!-- Footer -->
    <footer class="section-padding" style="background: var(--card); border-top: 1px solid var(--border);">
        <div class="container-custom">
            <div class="grid grid-4 gap-8">
                <!-- Company Info -->
                <div class="content-spacing">
                    <h3 class="font-serif text-xl natural-text-gradient">Natural</h3>
                    <p class="text-sm text-gray-600">
                        Sustainable clothing for the conscious modern lifestyle.
                    </p>
                    <div class="flex space-x-4">
                        <a href="https://instagram.com/natu.ral10.21" target="_blank" class="p-2 hover:bg-gray-100 rounded-full transition-colors">
                            <i data-lucide="instagram" class="w-5 h-5"></i>
                        </a>
                        <a href="#" class="p-2 hover:bg-gray-100 rounded-full transition-colors">
                            <i data-lucide="twitter" class="w-5 h-5"></i>
                        </a>
                        <a href="#" class="p-2 hover:bg-gray-100 rounded-full transition-colors">
                            <i data-lucide="facebook" class="w-5 h-5"></i>
                        </a>
                    </div>
                </div>
                
                <!-- Quick Links -->
                <div class="content-spacing">
                    <h4 class="font-semibold mb-4">Shop</h4>
                    <div class="space-y-2 text-sm">
                        <div><a href="collections.php?category=new-arrivals" class="text-gray-600 hover:text-primary transition-colors">New Arrivals</a></div>
                        <div><a href="collections.php?category=essentials" class="text-gray-600 hover:text-primary transition-colors">Essentials</a></div>
                        <div><a href="collections.php?category=seasonal" class="text-gray-600 hover:text-primary transition-colors">Seasonal</a></div>
                        <div><a href="collections.php?category=sustainable" class="text-gray-600 hover:text-primary transition-colors">Sale</a></div>
                    </div>
                </div>
                
                <!-- Support -->
                <div class="content-spacing">
                    <h4 class="font-semibold mb-4">Support</h4>
                    <div class="space-y-2 text-sm">
                        <div><a href="#" class="text-gray-600 hover:text-primary transition-colors">Size Guide</a></div>
                        <div><a href="#" class="text-gray-600 hover:text-primary transition-colors">Shipping & Returns</a></div>
                        <div><a href="#" class="text-gray-600 hover:text-primary transition-colors">Care Instructions</a></div>
                        <div><a href="contact.php" class="text-gray-600 hover:text-primary transition-colors">Contact Us</a></div>
                    </div>
                </div>
                
                <!-- Company -->
                <div class="content-spacing">
                    <h4 class="font-semibold mb-4">Company</h4>
                    <div class="space-y-2 text-sm">
                        <div><a href="about.php" class="text-gray-600 hover:text-primary transition-colors">About Us</a></div>
                        <div><a href="sustainability.php" class="text-gray-600 hover:text-primary transition-colors">Sustainability</a></div>
                        <div><a href="#" class="text-gray-600 hover:text-primary transition-colors">Careers</a></div>
                        <div><a href="#" class="text-gray-600 hover:text-primary transition-colors">Press</a></div>
                    </div>
                </div>
            </div>
            
            <div class="border-t pt-8 mt-8 text-center text-sm text-gray-600" style="border-color: var(--border);">
                <p>&copy; 2024 Natural. All rights reserved. Made with love for the planet.</p>
            </div>
        </div>
    </footer>

    <script>
        // Initialize Lucide icons
        lucide.createIcons();
        
        let currentOffset = <?php echo $limit; ?>;
        const currentCategory = "<?php echo htmlspecialchars($category ?? ''); ?>";
        const currentSearch = "<?php echo htmlspecialchars($search ?? ''); ?>";

        // Load more products
        function loadMore() {
            const loadMoreBtn = document.getElementById('load-more-btn');
            const originalText = loadMoreBtn.textContent;
            loadMoreBtn.textContent = 'Loading...';
            loadMoreBtn.disabled = true;

            let url = `api/products.php?action=list&limit=<?php echo $limit; ?>&offset=${currentOffset}`;
            
            if (currentCategory) {
                url = `api/products.php?action=category&category=${currentCategory}&limit=<?php echo $limit; ?>&offset=${currentOffset}`;
            } else if (currentSearch) {
                url = `api/products.php?action=search&search=${currentSearch}&limit=<?php echo $limit; ?>&offset=${currentOffset}`;
            }

            fetch(url)
            .then(response => response.json())
            .then(data => {
                if (data.success && data.products.length > 0) {
                    const productsGrid = document.getElementById('products-grid');
                    
                    data.products.forEach(product => {
                        const productCard = createProductCard(product);
                        productsGrid.appendChild(productCard);
                    });

                    currentOffset += data.products.length;
                    
                    // Hide load more button if we got fewer products than requested
                    if (data.products.length < <?php echo $limit; ?>) {
                        loadMoreBtn.style.display = 'none';
                    }
                } else {
                    loadMoreBtn.style.display = 'none';
                }

                loadMoreBtn.textContent = originalText;
                loadMoreBtn.disabled = false;
            })
            .catch(error => {
                console.error('Load more error:', error);
                loadMoreBtn.textContent = originalText;
                loadMoreBtn.disabled = false;
            });
        }

        // Create product card HTML
        function createProductCard(product) {
            const div = document.createElement('div');
            div.className = 'card-consistent fade-in';
            div.setAttribute('data-product-id', product.id);
            
            const inStock = product.in_stock !== undefined ? product.in_stock : (product.stock_quantity > 0);
            
            const sustainableBadge = product.is_sustainable ? 
                `<div class="flex items-center gap-1 mb-2">
                    <i data-lucide="leaf" class="w-4 h-4 text-green-600"></i>
                    <span class="text-xs text-green-600 font-medium">Sustainable</span>
                </div>` : '';
            
            const comparePrice = product.compare_at_price ? 
                `<span class="text-sm text-gray-500 line-through ml-2">${formatPrice(product.compare_at_price)}</span>` : '';

            div.innerHTML = `
                <div class="product-image">
                    <img src="${product.primary_image}" alt="${product.name}" class="img-cover">
                </div>
                <div class="product-content">
                    <div>
                        <h3 class="font-semibold mb-2">${product.name}</h3>
                        <p class="text-sm text-gray-600 mb-3">${product.short_description}</p>
                        ${sustainableBadge}
                    </div>
                    <div class="flex justify-between items-center">
                        <div>
                            <span class="font-semibold text-lg" style="color: var(--primary);">
                                ${product.formatted_price}
                            </span>
                            ${comparePrice}
                        </div>
                        <div class="flex gap-2">
                            <a href="product_detail.php?id=${product.id}" class="btn-primary text-sm px-3 py-2 w-full text-center">
                                View Details
                            </a>
                        </div>
                    </div>
                </div>
            `;

            // Re-initialize icons for new content
            setTimeout(() => lucide.createIcons(), 100);
            
            return div;
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

        // Format price helper
        function formatPrice(price, currency = 'THB') {
            return new Intl.NumberFormat('th-TH', {
                style: 'currency',
                currency: currency
            }).format(price);
        }

        // Initialize page
        document.addEventListener('DOMContentLoaded', function() {
            updateCartCount();
        });
    </script>
</body>
</html>