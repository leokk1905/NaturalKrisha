<?php
// =============================================
// PRODUCT FORMATTING HELPERS
// Natural Clothing Website
// =============================================

require_once __DIR__ . '/../config.php';

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