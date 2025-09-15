<?php
// =============================================
// NATURAL CLOTHING - DYNAMIC HOMEPAGE
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

// Get featured products
$featuredProducts = $productManager->getFeaturedProducts(4);

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
    <title>Natural - Sustainable Clothing for Modern Life</title>
    
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
        /* Keep all existing styles from natural_clothing_1.html */
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
            .card-consistent {
                min-height: 380px;
            }
            .card-consistent .product-image {
                height: 200px;
            }
        }
        
        @media (min-width: 769px) and (max-width: 1024px) {
            .grid-4 { grid-template-columns: repeat(2, 1fr); }
            .grid-3 { grid-template-columns: repeat(2, 1fr); }
        }
        
        @media (max-width: 480px) {
            .card-consistent {
                min-height: 350px;
            }
            .card-consistent .product-image {
                height: 180px;
            }
            .grid-4 {
                grid-template-columns: repeat(2, 1fr);
                gap: 1rem;
            }
        }
        
        .card-consistent {
            display: flex;
            flex-direction: column;
            border-radius: var(--radius-md);
            background: var(--card);
            border: 1px solid var(--border);
            min-height: 420px;
        }
        
        .card-consistent .product-image {
            height: 240px;
            overflow: hidden;
            flex-shrink: 0;
        }
        
        .card-consistent .product-content {
            padding: 1rem;
            display: flex;
            flex-direction: column;
            flex-grow: 1;
            justify-content: space-between;
        }
        
        .card-collection {
            aspect-ratio: 3/4;
            overflow: hidden;
            border-radius: var(--radius-lg);
        }
        
        .img-cover {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        
        .heading-xl { font-size: clamp(2.5rem, 5vw, 4rem); }
        .heading-lg { font-size: clamp(2rem, 4vw, 3rem); }
        .heading-md { font-size: clamp(1.5rem, 3vw, 2rem); }
        .heading-sm { font-size: clamp(1.25rem, 2.5vw, 1.5rem); }
        
        .section-padding { padding: clamp(3rem, 8vw, 6rem) 0; }
        .content-spacing > * + * { margin-top: 1.5rem; }
        
        .animate-hero {
            animation: heroEntry 0.8s ease-out forwards;
            opacity: 0;
            transform: translateY(40px);
        }
        
        .animate-hero-delayed {
            animation: heroEntry 1s ease-out 0.3s forwards;
            opacity: 0;
            transform: translateY(20px);
        }
        
        .animate-card-hover {
            transition: transform 300ms ease-out, box-shadow 300ms ease-out;
        }
        
        .animate-card-hover:hover {
            transform: scale(1.03);
            box-shadow: var(--shadow-lg);
        }
        
        .animate-float {
            animation: float 3s ease-in-out infinite;
        }
        
        .animate-float-delayed {
            animation: float 3s ease-in-out infinite;
            animation-delay: 1.5s;
        }
        
        @keyframes heroEntry {
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        @keyframes float {
            0%, 100% { transform: translateY(0) rotate(0deg); }
            50% { transform: translateY(-6px) rotate(2deg); }
        }
        
        .btn-primary {
            padding: 0.875rem 2rem;
            border-radius: var(--radius-md);
            background: var(--primary);
            color: var(--primary-foreground);
            font-weight: 500;
            letter-spacing: 0.025em;
            transition: all 200ms ease-out;
            border: none;
            cursor: pointer;
        }
        
        .btn-primary:hover {
            transform: scale(1.02);
            background: var(--natural-green-dark);
            box-shadow: var(--shadow-md);
        }
        
        .btn-secondary {
            padding: 0.875rem 2rem;
            border-radius: var(--radius-md);
            background: transparent;
            color: var(--primary);
            font-weight: 500;
            letter-spacing: 0.025em;
            transition: all 200ms ease-out;
            border: 2px solid var(--primary);
            cursor: pointer;
        }
        
        .btn-secondary:hover {
            background: var(--primary);
            color: var(--primary-foreground);
            transform: scale(1.02);
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
        
        /* Login/Profile dropdown */
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
        
        .loading {
            opacity: 0.6;
            pointer-events: none;
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
                    <h1 class="text-2xl font-serif font-light natural-text-gradient">Natural</h1>
                </div>
                
                <!-- Navigation Links -->
                <div class="hidden md:flex items-center space-x-8">
                    <a href="index.php" class="nav-link active">Home</a>
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

    <!-- Hero Section -->
    <section id="home" class="relative section-padding" style="background: linear-gradient(135deg, var(--natural-cream) 0%, var(--secondary) 100%);">
        <div class="container-custom">
            <div class="grid lg:grid-cols-2 gap-12 items-center">
                <!-- Text Content -->
                <div class="content-spacing">
                    <h2 class="heading-xl font-serif font-light animate-hero">Lets wear and change together</h2>
                    <p class="text-lg text-gray-600 animate-hero-delayed">
                        Discover sustainable clothing that combines minimalist design with exceptional quality. 
                        Every piece is crafted from eco-friendly materials for the conscious modern lifestyle.
                    </p>
                    <div class="flex flex-col sm:flex-row gap-4 animate-hero-delayed">
                        <a href="collections.php" class="btn-primary">Shop Collection</a>
                        <a href="about.php" class="btn-secondary">Learn Our Story</a>
                    </div>
                </div>
                
                <!-- Hero Image -->
                <div class="relative">
                    <div class="card-consistent" style="aspect-ratio: 4/5;">
                        <img src="peoplewearingproducts/shirtwear.jpg" alt="Natural clothing model" class="img-cover">
                    </div>
                    <!-- Decorative Elements -->
                    <div class="absolute -top-6 -right-6 animate-float" style="color: var(--natural-green-medium);">
                        <i data-lucide="leaf" class="w-12 h-12"></i>
                    </div>
                    <div class="absolute -bottom-4 -left-4 animate-float-delayed" style="color: var(--natural-green-light);">
                        <i data-lucide="flower" class="w-8 h-8"></i>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Featured Collections -->
    <section id="collections" class="section-padding">
        <div class="container-custom">
            <div class="text-center content-spacing mb-12">
                <h2 class="heading-lg font-serif">Featured Collections</h2>
                <p class="text-gray-600 max-w-2xl mx-auto">
                    Thoughtfully curated collections that blend timeless design with sustainable practices
                </p>
            </div>
            
            <div class="grid-consistent grid-4">
                <!-- Collection 1 -->
                <a href="collections.php?category=new-arrivals" class="card-collection animate-card-hover" style="background: var(--card);">
                    <div class="relative h-64">
                        <img src="https://images.unsplash.com/photo-1523381210434-271e8be1f52b?ixlib=rb-4.0.3&auto=format&fit=crop&w=600&q=80" alt="New Arrivals" class="img-cover">
                        <div class="absolute inset-0 bg-black bg-opacity-20 flex items-end p-6">
                            <div>
                                <h3 class="text-white font-semibold text-xl">New Arrivals</h3>
                                <p class="text-white text-sm opacity-90">Fresh sustainable pieces</p>
                            </div>
                        </div>
                    </div>
                </a>
                
                <!-- Collection 2 -->
                <a href="collections.php?category=essentials" class="card-collection animate-card-hover" style="background: var(--card);">
                    <div class="relative h-64">
                        <img src="https://images.unsplash.com/photo-1515372039744-b8f02a3ae446?ixlib=rb-4.0.3&auto=format&fit=crop&w=600&q=80" alt="Essentials" class="img-cover">
                        <div class="absolute inset-0 bg-black bg-opacity-20 flex items-end p-6">
                            <div>
                                <h3 class="text-white font-semibold text-xl">Essentials</h3>
                                <p class="text-white text-sm opacity-90">Wardrobe staples</p>
                            </div>
                        </div>
                    </div>
                </a>
                
                <!-- Collection 3 -->
                <a href="collections.php?category=seasonal" class="card-collection animate-card-hover" style="background: var(--card);">
                    <div class="relative h-64">
                        <img src="https://images.unsplash.com/photo-1434389677669-e08b4cac3105?ixlib=rb-4.0.3&auto=format&fit=crop&w=600&q=80" alt="Seasonal" class="img-cover">
                        <div class="absolute inset-0 bg-black bg-opacity-20 flex items-end p-6">
                            <div>
                                <h3 class="text-white font-semibold text-xl">Seasonal</h3>
                                <p class="text-white text-sm opacity-90">Current season highlights</p>
                            </div>
                        </div>
                    </div>
                </a>
                
                <!-- Collection 4 -->
                <a href="collections.php?category=sustainable" class="card-collection animate-card-hover" style="background: var(--card);">
                    <div class="relative h-64">
                        <img src="https://images.unsplash.com/photo-1441984904996-e0b6ba687e04?ixlib=rb-4.0.3&auto=format&fit=crop&w=600&q=80" alt="Sustainable" class="img-cover">
                        <div class="absolute inset-0 bg-black bg-opacity-20 flex items-end p-6">
                            <div>
                                <h3 class="text-white font-semibold text-xl">Sustainable</h3>
                                <p class="text-white text-sm opacity-90">Our eco-friendly line</p>
                            </div>
                        </div>
                    </div>
                </a>
            </div>
        </div>
    </section>

    <!-- Product Showcase -->
    <section class="section-padding" style="background: var(--muted);">
        <div class="container-custom">
            <div class="text-center content-spacing mb-12">
                <h2 class="heading-lg font-serif">Featured Products</h2>
                <p class="text-gray-600">Discover our most loved sustainable pieces</p>
            </div>
            
            <div class="grid-consistent grid-4" id="featured-products">
                <?php foreach ($featuredProducts as $product): ?>
                <!-- Product: <?php echo htmlspecialchars($product['name']); ?> -->
                <div class="card-consistent animate-card-hover" style="background: var(--card);">
                    <div class="product-image">
                        <img src="<?php echo htmlspecialchars($product['primary_image']); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>" class="img-cover">
                    </div>
                    <div class="product-content">
                        <div>
                            <h3 class="font-semibold mb-2"><?php echo htmlspecialchars($product['name']); ?></h3>
                            <p class="text-sm text-gray-600 mb-3"><?php echo htmlspecialchars($product['short_description']); ?></p>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="font-semibold text-lg" style="color: var(--primary);"><?php echo formatPrice($product['price'], $product['currency']); ?></span>
                            <a href="product_detail.php?id=<?php echo $product['id']; ?>" class="btn-primary text-sm px-4 py-2">View Details</a>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <!-- Brand Story Section -->
    <section id="about" class="section-padding">
        <div class="container-custom">
            <div class="grid lg:grid-cols-2 gap-12 items-center">
                <!-- Image -->
                <div class="relative">
                    <div class="card-consistent" style="aspect-ratio: 5/6;">
                        <img src="peoplewearingproducts/pantswear.jpg" alt="Sustainable fashion process" class="img-cover">
                    </div>
                    <div class="absolute -bottom-6 -right-6 animate-float" style="color: var(--natural-green-medium);">
                        <i data-lucide="sprout" class="w-16 h-16"></i>
                    </div>
                </div>
                
                <!-- Content -->
                <div class="content-spacing">
                    <h2 class="heading-lg font-serif">Our Natural Story</h2>
                    <p class="text-gray-600">
                        Natural was born from a simple belief: fashion should enhance both your life and the planet. 
                        We create timeless pieces using only sustainable materials like organic cotton, hemp, linen, 
                        and recycled fibers.
                    </p>
                    <p class="text-gray-600">
                        Every garment is designed for durability and versatility, ensuring it remains a cherished 
                        part of your wardrobe for years to come. We work directly with ethical manufacturers 
                        who share our commitment to fair labor practices and environmental responsibility.
                    </p>
                    
                    <!-- Stats -->
                    <div class="grid grid-3 gap-6 pt-6">
                        <div class="text-center">
                            <div class="text-2xl font-semibold" style="color: var(--primary);">10,000+</div>
                            <div class="text-sm text-gray-600">Trees Planted</div>
                        </div>
                        <div class="text-center">
                            <div class="text-2xl font-semibold" style="color: var(--primary);">50,000+</div>
                            <div class="text-sm text-gray-600">Happy Customers</div>
                        </div>
                        <div class="text-center">
                            <div class="text-2xl font-semibold" style="color: var(--primary);">100%</div>
                            <div class="text-sm text-gray-600">Sustainable Materials</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Newsletter Section -->
    <section id="sustainability" class="section-padding natural-gradient">
        <div class="container-custom text-center">
            <div class="max-w-2xl mx-auto content-spacing">
                <h2 class="heading-lg font-serif text-white">Join Our Natural Community</h2>
                <p class="text-white opacity-90">
                    Get updates on new sustainable releases, styling tips, and our environmental impact
                </p>
                
                <form id="newsletter-form" class="flex flex-col sm:flex-row gap-4 max-w-md mx-auto">
                    <input 
                        type="email" 
                        id="newsletter-email"
                        placeholder="Your email address" 
                        class="flex-1 px-4 py-3 rounded-lg border-none outline-none"
                        style="background: rgba(255, 255, 255, 0.9);"
                        required
                    >
                    <button type="submit" class="btn-primary" style="background: var(--natural-green-dark); color: white;">Subscribe</button>
                </form>
                
                <div class="absolute top-8 left-8 animate-float" style="color: rgba(255, 255, 255, 0.3);">
                    <i data-lucide="leaf" class="w-8 h-8"></i>
                </div>
                <div class="absolute bottom-8 right-8 animate-float-delayed" style="color: rgba(255, 255, 255, 0.3);">
                    <i data-lucide="flower-2" class="w-6 h-6"></i>
                </div>
            </div>
        </div>
    </section>

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
        
        // Newsletter subscription
        document.getElementById('newsletter-form').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const email = document.getElementById('newsletter-email').value;
            const submitBtn = this.querySelector('button[type="submit"]');
            const originalText = submitBtn.textContent;
            
            submitBtn.textContent = 'Subscribing...';
            submitBtn.disabled = true;
            
            // Simulate newsletter subscription (implement actual API call)
            setTimeout(() => {
                submitBtn.textContent = 'Subscribed!';
                setTimeout(() => {
                    submitBtn.textContent = originalText;
                    submitBtn.disabled = false;
                    document.getElementById('newsletter-email').value = '';
                }, 2000);
            }, 1000);
        });
        
        // Smooth scrolling for navigation links
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    target.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                }
            });
        });
        
        // Add scroll-triggered animations
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
        
        // Observe sections for scroll animations
        document.querySelectorAll('section').forEach(section => {
            section.style.opacity = '0';
            section.style.transform = 'translateY(30px)';
            section.style.transition = 'opacity 0.6s ease-out, transform 0.6s ease-out';
            observer.observe(section);
        });
        
        // Update cart count in real-time
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
        
        // Update cart count on page load and periodically
        updateCartCount();
        setInterval(updateCartCount, 30000); // Update every 30 seconds
    </script>
</body>
</html>