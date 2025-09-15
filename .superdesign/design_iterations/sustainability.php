<?php
// =============================================
// NATURAL CLOTHING - SUSTAINABILITY PAGE
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
    <title>Sustainability - Natural | Our Environmental Impact</title>
    
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
        /* Consistent sizing system */
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
        }
        
        @media (min-width: 769px) and (max-width: 1024px) {
            .grid-4 { grid-template-columns: repeat(2, 1fr); }
            .grid-3 { grid-template-columns: repeat(2, 1fr); }
        }
        
        .card-consistent {
            aspect-ratio: 4/5;
            overflow: hidden;
            border-radius: var(--radius-md);
            background: var(--card);
            border: 1px solid var(--border);
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
        
        .animate-fade-in {
            animation: fadeInUp 0.8s ease-out forwards;
            opacity: 0;
            transform: translateY(30px);
        }
        
        .animate-fade-in-delayed {
            animation: fadeInUp 0.8s ease-out 0.3s forwards;
            opacity: 0;
            transform: translateY(30px);
        }
        
        @keyframes fadeInUp {
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .animate-float {
            animation: float 3s ease-in-out infinite;
        }
        
        .animate-float-delayed {
            animation: float 3s ease-in-out infinite;
            animation-delay: 1.5s;
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
        
        .nav-link.active {
            color: var(--primary);
        }
        
        .nav-link.active::after {
            width: 100%;
            left: 0;
        }
        
        .impact-card {
            padding: 2rem;
            border-radius: var(--radius-lg);
            background: var(--card);
            border: 1px solid var(--border);
            text-align: center;
            transition: transform 300ms ease-out, box-shadow 300ms ease-out;
        }
        
        .impact-card:hover {
            transform: translateY(-5px);
            box-shadow: var(--shadow-lg);
        }
        
        .progress-bar {
            width: 100%;
            height: 8px;
            background: var(--muted);
            border-radius: 4px;
            overflow: hidden;
        }
        
        .progress-fill {
            height: 100%;
            background: var(--primary);
            border-radius: 4px;
            transition: width 1s ease-out;
        }
        
        .stat-counter {
            font-size: 2.5rem;
            font-weight: 700;
            color: var(--primary);
            font-family: var(--font-serif);
        }
        
        .material-card {
            padding: 1.5rem;
            border-radius: var(--radius-md);
            background: var(--card);
            border: 1px solid var(--border);
            transition: all 300ms ease-out;
        }
        
        .material-card:hover {
            transform: translateY(-3px);
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
                    <a href="sustainability.php" class="nav-link active">Sustainability</a>
                    <a href="contact.php" class="nav-link">Contact</a>
                </div>
                
                <!-- Icons -->
                <div class="flex items-center space-x-4">
                    <a href="cart.php" class="p-2 hover:bg-gray-100 rounded-full transition-colors relative">
                        <i data-lucide="shopping-bag" class="w-5 h-5"></i>
                        <span class="absolute -top-2 -right-2 bg-red-500 text-white text-xs w-5 h-5 rounded-full flex items-center justify-center" id="cart-count"><?php echo $cartCount; ?></span>
                    </a>
                    <a href="login.php" class="p-2 hover:bg-gray-100 rounded-full transition-colors">
                        <i data-lucide="user" class="w-5 h-5"></i>
                    </a>
                    <button class="md:hidden p-2 hover:bg-gray-100 rounded-full transition-colors">
                        <i data-lucide="menu" class="w-5 h-5"></i>
                    </button>
                </div>
            </div>
        </nav>
    </header>

    <!-- Hero Section -->
    <section class="relative section-padding" style="background: linear-gradient(135deg, var(--natural-cream) 0%, var(--secondary) 100%);">
        <div class="container-custom">
            <div class="grid lg:grid-cols-2 gap-12 items-center">
                <!-- Text Content -->
                <div class="content-spacing animate-fade-in">
                    <h1 class="heading-xl font-serif font-light">Sustainability at Natural</h1>
                    <p class="text-lg text-gray-600">
                        Every thread tells a story of responsibility. From regenerative farming to circular design, 
                        we're pioneering a new way of creating fashion that heals rather than harms.
                    </p>
                    <div class="flex flex-col sm:flex-row gap-4">
                        <a href="#impact" class="btn-primary">See Our Impact</a>
                    </div>
                </div>
                
                <!-- Hero Image -->
                <div class="relative animate-fade-in-delayed">
                    <div class="card-consistent" style="aspect-ratio: 4/5;">
                        <img src="https://images.unsplash.com/photo-1441986300917-64674bd600d8?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80" alt="Sustainable farming" class="img-cover">
                    </div>
                    <div class="absolute -top-6 -right-6 animate-float" style="color: var(--natural-green-medium);">
                        <i data-lucide="recycle" class="w-12 h-12"></i>
                    </div>
                    <div class="absolute -bottom-4 -left-4 animate-float-delayed" style="color: var(--natural-green-light);">
                        <i data-lucide="earth" class="w-8 h-8"></i>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Impact Statistics -->
    <section id="impact" class="section-padding">
        <div class="container-custom">
            <div class="text-center content-spacing mb-12">
                <h2 class="heading-lg font-serif">Our Environmental Impact</h2>
                <p class="text-gray-600 max-w-2xl mx-auto">
                    Measurable progress toward a more sustainable future, one garment at a time
                </p>
            </div>
            
            <div class="grid-consistent grid-4 mb-12">
                <!-- Stat 1 -->
                <div class="impact-card">
                    <div class="stat-counter" data-count="10000">0</div>
                    <div class="text-sm text-gray-600 mt-2">Trees Planted</div>
                    <div class="mt-4">
                        <div class="progress-bar">
                            <div class="progress-fill" data-width="75%" style="width: 0%;"></div>
                        </div>
                        <div class="text-xs text-gray-500 mt-1">75% of 2024 Goal</div>
                    </div>
                </div>
                
                <!-- Stat 2 -->
                <div class="impact-card">
                    <div class="stat-counter" data-count="2500000">0</div>
                    <div class="text-sm text-gray-600 mt-2">Liters of Water Saved</div>
                    <div class="mt-4">
                        <div class="progress-bar">
                            <div class="progress-fill" data-width="85%" style="width: 0%;"></div>
                        </div>
                        <div class="text-xs text-gray-500 mt-1">vs. Conventional Production</div>
                    </div>
                </div>
                
                <!-- Stat 3 -->
                <div class="impact-card">
                    <div class="stat-counter" data-count="500">0</div>
                    <div class="text-sm text-gray-600 mt-2">Tons CO2 Offset</div>
                    <div class="mt-4">
                        <div class="progress-bar">
                            <div class="progress-fill" data-width="90%" style="width: 0%;"></div>
                        </div>
                        <div class="text-xs text-gray-500 mt-1">Carbon Neutral Shipping</div>
                    </div>
                </div>
                
                <!-- Stat 4 -->
                <div class="impact-card">
                    <div class="stat-counter" data-count="100">0</div>
                    <div class="text-sm text-gray-600 mt-2">% Sustainable Materials</div>
                    <div class="mt-4">
                        <div class="progress-bar">
                            <div class="progress-fill" data-width="100%" style="width: 0%;"></div>
                        </div>
                        <div class="text-xs text-gray-500 mt-1">Every Single Product</div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Sustainable Materials -->
    <section class="section-padding" style="background: var(--muted);">
        <div class="container-custom">
            <div class="text-center content-spacing mb-12">
                <h2 class="heading-lg font-serif">Our Sustainable Materials</h2>
                <p class="text-gray-600 max-w-2xl mx-auto">
                    We carefully select each fiber for its environmental benefits and quality characteristics
                </p>
            </div>
            
            <div class="grid-consistent grid-3">
                <!-- Material 1 -->
                <div class="material-card">
                    <div class="mb-4" style="color: var(--primary);">
                        <i data-lucide="flower-2" class="w-12 h-12 mx-auto"></i>
                    </div>
                    <h3 class="heading-sm font-serif mb-3">Organic Cotton</h3>
                    <p class="text-gray-600 mb-4">
                        Grown without harmful pesticides or synthetic fertilizers, using 91% less water 
                        than conventional cotton through efficient irrigation.
                    </p>
                    <div class="space-y-2 text-sm">
                        <div class="flex justify-between">
                            <span>Water Usage</span>
                            <span style="color: var(--primary);">-91%</span>
                        </div>
                        <div class="flex justify-between">
                            <span>Chemical Free</span>
                            <span style="color: var(--primary);">100%</span>
                        </div>
                        <div class="flex justify-between">
                            <span>Soil Health</span>
                            <span style="color: var(--primary);">Improved</span>
                        </div>
                    </div>
                </div>
                
                <!-- Material 2 -->
                <div class="material-card">
                    <div class="mb-4" style="color: var(--primary);">
                        <i data-lucide="wheat" class="w-12 h-12 mx-auto"></i>
                    </div>
                    <h3 class="heading-sm font-serif mb-3">Hemp Fiber</h3>
                    <p class="text-gray-600 mb-4">
                        One of the most sustainable crops on Earth, hemp grows quickly, improves soil health, 
                        and requires minimal water and no pesticides.
                    </p>
                    <div class="space-y-2 text-sm">
                        <div class="flex justify-between">
                            <span>Growth Speed</span>
                            <span style="color: var(--primary);">120 days</span>
                        </div>
                        <div class="flex justify-between">
                            <span>Pesticides</span>
                            <span style="color: var(--primary);">None</span>
                        </div>
                        <div class="flex justify-between">
                            <span>Durability</span>
                            <span style="color: var(--primary);">Superior</span>
                        </div>
                    </div>
                </div>
                
                <!-- Material 3 -->
                <div class="material-card">
                    <div class="mb-4" style="color: var(--primary);">
                        <i data-lucide="wind" class="w-12 h-12 mx-auto"></i>
                    </div>
                    <h3 class="heading-sm font-serif mb-3">Linen</h3>
                    <p class="text-gray-600 mb-4">
                        Made from flax plants that use every part of the plant with zero waste. 
                        Naturally biodegradable and becomes softer with each wash.
                    </p>
                    <div class="space-y-2 text-sm">
                        <div class="flex justify-between">
                            <span>Plant Waste</span>
                            <span style="color: var(--primary);">0%</span>
                        </div>
                        <div class="flex justify-between">
                            <span>Biodegradable</span>
                            <span style="color: var(--primary);">100%</span>
                        </div>
                        <div class="flex justify-between">
                            <span>Breathability</span>
                            <span style="color: var(--primary);">Excellent</span>
                        </div>
                    </div>
                </div>
                
                <!-- Material 4 -->
                <div class="material-card">
                    <div class="mb-4" style="color: var(--primary);">
                        <i data-lucide="bamboo" class="w-12 h-12 mx-auto"></i>
                    </div>
                    <h3 class="heading-sm font-serif mb-3">Bamboo Fiber</h3>
                    <p class="text-gray-600 mb-4">
                        Fastest-growing plant on Earth, producing 35% more oxygen than trees. 
                        Naturally antibacterial and incredibly soft.
                    </p>
                    <div class="space-y-2 text-sm">
                        <div class="flex justify-between">
                            <span>Growth Rate</span>
                            <span style="color: var(--primary);">3ft/day</span>
                        </div>
                        <div class="flex justify-between">
                            <span>Antibacterial</span>
                            <span style="color: var(--primary);">Natural</span>
                        </div>
                        <div class="flex justify-between">
                            <span>Softness</span>
                            <span style="color: var(--primary);">Silk-like</span>
                        </div>
                    </div>
                </div>
                
                <!-- Material 5 -->
                <div class="material-card">
                    <div class="mb-4" style="color: var(--primary);">
                        <i data-lucide="recycle" class="w-12 h-12 mx-auto"></i>
                    </div>
                    <h3 class="heading-sm font-serif mb-3">Recycled Fibers</h3>
                    <p class="text-gray-600 mb-4">
                        Post-consumer plastic bottles and textile waste transformed into high-quality 
                        fibers, giving new life to materials that would otherwise pollute.
                    </p>
                    <div class="space-y-2 text-sm">
                        <div class="flex justify-between">
                            <span>Waste Diverted</span>
                            <span style="color: var(--primary);">12 bottles</span>
                        </div>
                        <div class="flex justify-between">
                            <span>Energy Saved</span>
                            <span style="color: var(--primary);">-60%</span>
                        </div>
                        <div class="flex justify-between">
                            <span>Performance</span>
                            <span style="color: var(--primary);">Enhanced</span>
                        </div>
                    </div>
                </div>
                
                <!-- Material 6 -->
                <div class="material-card">
                    <div class="mb-4" style="color: var(--primary);">
                        <i data-lucide="droplets" class="w-12 h-12 mx-auto"></i>
                    </div>
                    <h3 class="heading-sm font-serif mb-3">TENCEL™</h3>
                    <p class="text-gray-600 mb-4">
                        Made from sustainably sourced wood pulp in a closed-loop process that recycles 
                        99% of chemicals and water used in production.
                    </p>
                    <div class="space-y-2 text-sm">
                        <div class="flex justify-between">
                            <span>Chemical Recovery</span>
                            <span style="color: var(--primary);">99%</span>
                        </div>
                        <div class="flex justify-between">
                            <span>Source</span>
                            <span style="color: var(--primary);">FSC Certified</span>
                        </div>
                        <div class="flex justify-between">
                            <span>Comfort</span>
                            <span style="color: var(--primary);">Luxurious</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Circular Design -->
    <section class="section-padding">
        <div class="container-custom">
            <div class="grid lg:grid-cols-2 gap-12 items-center">
                <!-- Content -->
                <div class="content-spacing">
                    <h2 class="heading-lg font-serif">Circular Design Philosophy</h2>
                    <p class="text-gray-600">
                        We design every piece with its entire lifecycle in mind, from the initial 
                        sketch to end-of-life recycling. Our circular approach ensures nothing goes to waste.
                    </p>
                    
                    <div class="space-y-6">
                        <div class="flex items-start space-x-4">
                            <div class="flex-shrink-0 w-12 h-12 rounded-full flex items-center justify-center" style="background: var(--natural-green-light);">
                                <i data-lucide="pencil" class="w-6 h-6" style="color: var(--primary);"></i>
                            </div>
                            <div>
                                <h3 class="font-semibold mb-2">Design for Longevity</h3>
                                <p class="text-gray-600">Timeless styles and reinforced construction ensure each piece lasts for years, not seasons.</p>
                            </div>
                        </div>
                        
                        <div class="flex items-start space-x-4">
                            <div class="flex-shrink-0 w-12 h-12 rounded-full flex items-center justify-center" style="background: var(--natural-green-light);">
                                <i data-lucide="repeat" class="w-6 h-6" style="color: var(--primary);"></i>
                            </div>
                            <div>
                                <h3 class="font-semibold mb-2">Take-Back Program</h3>
                                <p class="text-gray-600">Return worn Natural pieces to us for recycling into new garments or other useful products.</p>
                            </div>
                        </div>
                        
                        <div class="flex items-start space-x-4">
                            <div class="flex-shrink-0 w-12 h-12 rounded-full flex items-center justify-center" style="background: var(--natural-green-light);">
                                <i data-lucide="sparkles" class="w-6 h-6" style="color: var(--primary);"></i>
                            </div>
                            <div>
                                <h3 class="font-semibold mb-2">Repair & Refresh</h3>
                                <p class="text-gray-600">Free repair services and styling consultations to help extend the life of your favorite pieces.</p>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Image -->
                <div class="relative">
                    <div class="card-consistent" style="aspect-ratio: 4/5;">
                        <img src="https://images.unsplash.com/photo-1441986300917-64674bd600d8?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80" alt="Circular design process" class="img-cover">
                    </div>
                    <div class="absolute -bottom-6 -right-6 animate-float" style="color: var(--natural-green-medium);">
                        <i data-lucide="refresh-cw" class="w-16 h-16"></i>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Certifications -->
    <section class="section-padding" style="background: var(--muted);">
        <div class="container-custom">
            <div class="text-center content-spacing mb-12">
                <h2 class="heading-lg font-serif">Our Certifications</h2>
                <p class="text-gray-600 max-w-2xl mx-auto">
                    Third-party verified standards that hold us accountable to our sustainability promises
                </p>
            </div>
            
            <div class="grid-consistent grid-4">
                <!-- Certification 1 -->
                <div class="impact-card">
                    <div class="mb-4" style="color: var(--primary);">
                        <i data-lucide="shield-check" class="w-12 h-12 mx-auto"></i>
                    </div>
                    <h3 class="font-semibold text-lg mb-2">GOTS Certified</h3>
                    <p class="text-sm text-gray-600">
                        Global Organic Textile Standard ensures organic fiber content and ethical production.
                    </p>
                </div>
                
                <!-- Certification 2 -->
                <div class="impact-card">
                    <div class="mb-4" style="color: var(--primary);">
                        <i data-lucide="leaf" class="w-12 h-12 mx-auto"></i>
                    </div>
                    <h3 class="font-semibold text-lg mb-2">OEKO-TEX</h3>
                    <p class="text-sm text-gray-600">
                        Standard 100 certification guarantees textiles free from harmful chemicals.
                    </p>
                </div>
                
                <!-- Certification 3 -->
                <div class="impact-card">
                    <div class="mb-4" style="color: var(--primary);">
                        <i data-lucide="trees" class="w-12 h-12 mx-auto"></i>
                    </div>
                    <h3 class="font-semibold text-lg mb-2">FSC Certified</h3>
                    <p class="text-sm text-gray-600">
                        Forest Stewardship Council ensures responsible sourcing of wood-based fibers.
                    </p>
                </div>
                
                <!-- Certification 4 -->
                <div class="impact-card">
                    <div class="mb-4" style="color: var(--primary);">
                        <i data-lucide="globe" class="w-12 h-12 mx-auto"></i>
                    </div>
                    <h3 class="font-semibold text-lg mb-2">Carbon Neutral</h3>
                    <p class="text-sm text-gray-600">
                        Verified carbon-neutral shipping and operations through renewable energy and offsets.
                    </p>
                </div>
            </div>
        </div>
    </section>

    <!-- Call to Action -->
    <section class="section-padding natural-gradient">
        <div class="container-custom text-center">
            <div class="max-w-2xl mx-auto content-spacing">
                <h2 class="heading-lg font-serif text-white">Join the Sustainable Fashion Movement</h2>
                <p class="text-white opacity-90">
                    Every purchase is a vote for the kind of world we want to live in. 
                    Choose fashion that makes a positive impact.
                </p>
                
                <div class="flex flex-col sm:flex-row gap-4 justify-center">
                    <a href="collections.php" class="btn-primary" style="background: var(--natural-green-dark); color: white;">Shop Sustainable</a>
                    <a href="about.php" class="btn-primary" style="background: rgba(255, 255, 255, 0.1); color: white; border: 2px solid white;">Learn Our Story</a>
                </div>
                
                <div class="absolute top-8 left-8 animate-float" style="color: rgba(255, 255, 255, 0.3);">
                    <i data-lucide="seedling" class="w-8 h-8"></i>
                </div>
                <div class="absolute bottom-8 right-8 animate-float-delayed" style="color: rgba(255, 255, 255, 0.3);">
                    <i data-lucide="heart" class="w-6 h-6"></i>
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
                        <button class="p-2 hover:bg-gray-100 rounded-full transition-colors">
                            <i data-lucide="instagram" class="w-5 h-5"></i>
                        </button>
                        <button class="p-2 hover:bg-gray-100 rounded-full transition-colors">
                            <i data-lucide="twitter" class="w-5 h-5"></i>
                        </button>
                        <button class="p-2 hover:bg-gray-100 rounded-full transition-colors">
                            <i data-lucide="facebook" class="w-5 h-5"></i>
                        </button>
                    </div>
                </div>
                
                <!-- Quick Links -->
                <div class="content-spacing">
                    <h4 class="font-semibold mb-4">Shop</h4>
                    <div class="space-y-2 text-sm">
                        <div><a href="collections.php" class="text-gray-600 hover:text-primary transition-colors">New Arrivals</a></div>
                        <div><a href="collections.php" class="text-gray-600 hover:text-primary transition-colors">Essentials</a></div>
                        <div><a href="collections.php" class="text-gray-600 hover:text-primary transition-colors">Seasonal</a></div>
                        <div><a href="collections.php" class="text-gray-600 hover:text-primary transition-colors">Sale</a></div>
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
        
        // Animated counters
        function animateCounter(element) {
            const target = parseInt(element.dataset.count);
            const duration = 2000;
            const step = target / (duration / 16);
            let current = 0;
            
            const timer = setInterval(() => {
                current += step;
                if (current >= target) {
                    element.textContent = target.toLocaleString() + (target === 100 ? '%' : '');
                    clearInterval(timer);
                } else {
                    element.textContent = Math.floor(current).toLocaleString() + (target === 100 ? '%' : '');
                }
            }, 16);
        }
        
        // Progress bar animation
        function animateProgressBar(element) {
            const targetWidth = element.dataset.width;
            element.style.width = targetWidth;
        }
        
        // Intersection observer for animations
        const observerOptions = {
            threshold: 0.3,
            rootMargin: '0px 0px -50px 0px'
        };
        
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    const counters = entry.target.querySelectorAll('.stat-counter');
                    const progressBars = entry.target.querySelectorAll('.progress-fill');
                    
                    counters.forEach(counter => {
                        animateCounter(counter);
                    });
                    
                    progressBars.forEach(bar => {
                        setTimeout(() => animateProgressBar(bar), 500);
                    });
                    
                    observer.unobserve(entry.target);
                }
            });
        }, observerOptions);
        
        // Observe the impact section
        const impactSection = document.getElementById('impact');
        if (impactSection) {
            observer.observe(impactSection);
        }
        
        // Smooth scrolling for anchor links
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
        
        // Scroll-triggered animations for cards
        const cardObserver = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.style.opacity = '1';
                    entry.target.style.transform = 'translateY(0)';
                }
            });
        }, { threshold: 0.1 });
        
        document.querySelectorAll('.material-card, .impact-card').forEach(card => {
            card.style.opacity = '0';
            card.style.transform = 'translateY(30px)';
            card.style.transition = 'opacity 0.6s ease-out, transform 0.6s ease-out';
            cardObserver.observe(card);
        });
    </script>
</body>
</html>