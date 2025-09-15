<?php
// =============================================
// CART HELPER FUNCTIONS
// Natural Clothing Website  
// =============================================

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