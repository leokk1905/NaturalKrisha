<?php
// =============================================
// PRODUCTS API ENDPOINTS
// Natural Clothing Website
// =============================================

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Headers: Content-Type');

require_once __DIR__ . '/Database.php';
require_once __DIR__ . '/../config.php';

$productManager = new ProductManager();

// Get request parameters
$action = $_GET['action'] ?? 'list';
$id = $_GET['id'] ?? null;
$slug = $_GET['slug'] ?? null;
$category = $_GET['category'] ?? null;
$search = $_GET['search'] ?? null;
$limit = isset($_GET['limit']) ? (int)$_GET['limit'] : null;
$offset = isset($_GET['offset']) ? (int)$_GET['offset'] : 0;

try {
    switch ($action) {
        case 'list':
            handleProductList($limit, $offset);
            break;
            
        case 'featured':
            handleFeaturedProducts($limit);
            break;
            
        case 'detail':
            if ($id) {
                handleProductDetail($id);
            } elseif ($slug) {
                handleProductDetailBySlug($slug);
            } else {
                throw new Exception('Product ID or slug required');
            }
            break;
            
        case 'variants':
            if (!$id) {
                throw new Exception('Product ID required');
            }
            handleProductVariants($id);
            break;
            
        case 'category':
            if (!$category) {
                throw new Exception('Category slug required');
            }
            handleProductsByCategory($category, $limit);
            break;
            
        case 'search':
            if (!$search) {
                throw new Exception('Search query required');
            }
            handleProductSearch($search, $limit);
            break;
            
        default:
            throw new Exception('Invalid action');
    }
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}

function handleProductList($limit, $offset) {
    global $productManager;
    
    $products = $productManager->getAllProducts($limit, $offset);
    
    // Format products for display
    $formattedProducts = array_map('formatProduct', $products);
    
    echo json_encode([
        'success' => true,
        'products' => $formattedProducts,
        'count' => count($formattedProducts)
    ]);
}

function handleFeaturedProducts($limit) {
    global $productManager;
    
    $limit = $limit ?: 4;
    $products = $productManager->getFeaturedProducts($limit);
    
    // Format products for display
    $formattedProducts = array_map('formatProduct', $products);
    
    echo json_encode([
        'success' => true,
        'products' => $formattedProducts,
        'count' => count($formattedProducts)
    ]);
}

function handleProductDetail($id) {
    global $productManager;
    
    $product = $productManager->getProductById($id);
    
    if (!$product) {
        throw new Exception('Product not found');
    }
    
    // Get variants
    $variants = $productManager->getProductVariants($id);
    
    $formattedProduct = formatProductDetail($product);
    $formattedProduct['variants'] = array_map('formatVariant', $variants);
    
    echo json_encode([
        'success' => true,
        'product' => $formattedProduct
    ]);
}

function handleProductDetailBySlug($slug) {
    global $productManager;
    
    $product = $productManager->getProductBySlug($slug);
    
    if (!$product) {
        throw new Exception('Product not found');
    }
    
    // Get variants
    $variants = $productManager->getProductVariants($product['id']);
    
    $formattedProduct = formatProductDetail($product);
    $formattedProduct['variants'] = array_map('formatVariant', $variants);
    
    echo json_encode([
        'success' => true,
        'product' => $formattedProduct
    ]);
}

function handleProductVariants($productId) {
    global $productManager;
    
    $variants = $productManager->getProductVariants($productId);
    
    $formattedVariants = array_map('formatVariant', $variants);
    
    echo json_encode([
        'success' => true,
        'variants' => $formattedVariants
    ]);
}

function handleProductsByCategory($categorySlug, $limit) {
    global $productManager;
    
    $products = $productManager->getProductsByCategory($categorySlug, $limit);
    
    $formattedProducts = array_map('formatProduct', $products);
    
    echo json_encode([
        'success' => true,
        'products' => $formattedProducts,
        'category' => $categorySlug,
        'count' => count($formattedProducts)
    ]);
}

function handleProductSearch($query, $limit) {
    global $productManager;
    
    $limit = $limit ?: 20;
    $products = $productManager->searchProducts($query, $limit);
    
    $formattedProducts = array_map('formatProduct', $products);
    
    echo json_encode([
        'success' => true,
        'products' => $formattedProducts,
        'query' => $query,
        'count' => count($formattedProducts)
    ]);
}

function formatProduct($product) {
    global $site_config;
    
    return [
        'id' => (int)$product['id'],
        'name' => $product['name'],
        'slug' => $product['slug'],
        'short_description' => $product['short_description'],
        'price' => (float)$product['price'],
        'compare_at_price' => $product['compare_at_price'] ? (float)$product['compare_at_price'] : null,
        'currency' => $product['currency'],
        'formatted_price' => formatPrice($product['price'], $product['currency']),
        'primary_image' => $product['primary_image'] ?: 'https://via.placeholder.com/400x500?text=No+Image',
        'is_featured' => (bool)$product['is_featured'],
        'is_sustainable' => (bool)$product['is_sustainable'],
        'stock_quantity' => (int)$product['stock_quantity'],
        'in_stock' => (int)$product['stock_quantity'] > 0,
        'material_composition' => $product['material_composition'],
    ];
}

function formatProductDetail($product) {
    $formatted = formatProduct($product);
    
    // Add detailed fields
    $formatted['description'] = $product['description'];
    $formatted['care_instructions'] = $product['care_instructions'];
    $formatted['sustainability_info'] = $product['sustainability_info'];
    $formatted['sku'] = $product['sku'];
    $formatted['weight'] = $product['weight'] ? (float)$product['weight'] : null;
    $formatted['dimensions'] = $product['dimensions'];
    $formatted['meta_title'] = $product['meta_title'];
    $formatted['meta_description'] = $product['meta_description'];
    
    // Process images
    if ($product['images']) {
        $formatted['images'] = explode(',', $product['images']);
    } else {
        $formatted['images'] = [$formatted['primary_image']];
    }
    
    // Process categories
    if ($product['categories']) {
        $formatted['categories'] = explode(',', $product['categories']);
    } else {
        $formatted['categories'] = [];
    }
    
    return $formatted;
}

function formatVariant($variant) {
    $formatted = [
        'id' => (int)$variant['id'],
        'name' => $variant['name'],
        'sku' => $variant['sku'],
        'price' => $variant['price'] ? (float)$variant['price'] : null,
        'compare_at_price' => $variant['compare_at_price'] ? (float)$variant['compare_at_price'] : null,
        'stock_quantity' => (int)$variant['stock_quantity'],
        'in_stock' => (int)$variant['stock_quantity'] > 0,
        'weight' => $variant['weight'] ? (float)$variant['weight'] : null,
        'barcode' => $variant['barcode'],
        'is_active' => (bool)$variant['is_active'],
        'options' => []
    ];
    
    // Process variant options
    if ($variant['options']) {
        $options = explode('|', $variant['options']);
        foreach ($options as $option) {
            if (strpos($option, ':') !== false) {
                list($name, $value) = explode(':', $option, 2);
                $formatted['options'][$name] = $value;
            }
        }
    }
    
    return $formatted;
}

?>