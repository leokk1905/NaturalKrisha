-- =============================================
-- INSERT NATURAL CLOTHING PRODUCTS DATA
-- =============================================

-- Insert your specific products
INSERT INTO products (
    name, 
    slug, 
    description, 
    short_description, 
    sku, 
    price, 
    currency,
    material_composition,
    care_instructions,
    sustainability_info,
    is_active,
    is_featured,
    is_sustainable,
    stock_quantity,
    meta_title,
    meta_description
) VALUES 
(
    'Hemp boutique-Tetris sleeveless top',
    'hemp-boutique-tetris-sleeveless-top',
    'Jacquard woven fabric with a geometric pattern featuring a natural yellowish hue. Made from sustainable fabrics combining Hemp 23% and Recycled polyester 77% for comfort and environmental responsibility. The unique Tetris-inspired geometric design adds a modern touch to this eco-conscious piece.',
    'Hemp 23% & Recycled polyester 77% - Geometric pattern, natural yellowish hue',
    'HBT-SLEEVELESS-001',
    850.00,
    'THB',
    'Hemp: 23%, Recycled Polyester: 77%, Jacquard woven fabric, Geometric pattern design, Natural yellowish hue',
    'Machine wash cold (30°C), Use mild detergent, Hang dry or tumble dry low, Iron on low heat if needed, Do not bleach',
    'Made from sustainable fabrics, Hemp is a renewable resource, Recycled polyester reduces waste, Ethical manufacturing practices, Durable construction for longevity',
    TRUE,
    TRUE,
    TRUE,
    50,
    'Hemp boutique-Tetris sleeveless top - Sustainable Fashion | Natural',
    'Eco-friendly sleeveless top made from Hemp 23% and Recycled polyester 77%. Features geometric Tetris pattern in natural yellowish hue. ฿850'
),
(
    'Lyocell / Linen pant',
    'lyocell-linen-pant',
    'Twill weave with a natural fiber texture, soft, breathable and comfortable to wear. Made from sustainable fabrics combining Lyocell 80% and Linen 20% for superior comfort and environmental responsibility. The natural fiber blend provides excellent breathability and moisture-wicking properties.',
    'Lyocell 80% & Linen 20% - Soft, breathable and comfortable',
    'LYC-LINEN-PANT-001',
    1000.00,
    'THB',
    'Lyocell: 80%, Linen: 20%, Twill weave construction, Natural fiber texture, Soft and breathable',
    'Machine wash cold (30°C), Use mild detergent, Hang dry recommended, Iron on medium heat, Do not bleach, Dry clean if needed',
    'Made from sustainable fabrics, Lyocell is made from renewable wood sources, Linen is a natural sustainable fiber, Biodegradable materials, Low environmental impact production',
    TRUE,
    TRUE,
    TRUE,
    30,
    'Lyocell / Linen pant - Sustainable Bottoms | Natural',
    'Comfortable pants made from Lyocell 80% and Linen 20%. Twill weave construction with natural fiber texture. ฿1000'
);

-- Get the product IDs for variant creation
SET @hemp_top_id = (SELECT id FROM products WHERE sku = 'HBT-SLEEVELESS-001');
SET @linen_pant_id = (SELECT id FROM products WHERE sku = 'LYC-LINEN-PANT-001');

-- Insert product variants for Hemp boutique-Tetris sleeveless top (sizes)
INSERT INTO product_variants (product_id, name, sku, price, stock_quantity) VALUES 
(@hemp_top_id, 'Hemp boutique-Tetris sleeveless top - XXS', 'HBT-SLEEVELESS-001-XXS', 850.00, 5),
(@hemp_top_id, 'Hemp boutique-Tetris sleeveless top - XS', 'HBT-SLEEVELESS-001-XS', 850.00, 8),
(@hemp_top_id, 'Hemp boutique-Tetris sleeveless top - S', 'HBT-SLEEVELESS-001-S', 850.00, 10),
(@hemp_top_id, 'Hemp boutique-Tetris sleeveless top - M', 'HBT-SLEEVELESS-001-M', 850.00, 12),
(@hemp_top_id, 'Hemp boutique-Tetris sleeveless top - L', 'HBT-SLEEVELESS-001-L', 850.00, 10),
(@hemp_top_id, 'Hemp boutique-Tetris sleeveless top - XL', 'HBT-SLEEVELESS-001-XL', 850.00, 8),
(@hemp_top_id, 'Hemp boutique-Tetris sleeveless top - XXL', 'HBT-SLEEVELESS-001-XXL', 850.00, 5);

-- Insert product variants for Lyocell / Linen pant (sizes)
INSERT INTO product_variants (product_id, name, sku, price, stock_quantity) VALUES 
(@linen_pant_id, 'Lyocell / Linen pant - XXS', 'LYC-LINEN-PANT-001-XXS', 1000.00, 3),
(@linen_pant_id, 'Lyocell / Linen pant - XS', 'LYC-LINEN-PANT-001-XS', 1000.00, 5),
(@linen_pant_id, 'Lyocell / Linen pant - S', 'LYC-LINEN-PANT-001-S', 1000.00, 8),
(@linen_pant_id, 'Lyocell / Linen pant - M', 'LYC-LINEN-PANT-001-M', 1000.00, 10),
(@linen_pant_id, 'Lyocell / Linen pant - L', 'LYC-LINEN-PANT-001-L', 1000.00, 8),
(@linen_pant_id, 'Lyocell / Linen pant - XL', 'LYC-LINEN-PANT-001-XL', 1000.00, 6),
(@linen_pant_id, 'Lyocell / Linen pant - XXL', 'LYC-LINEN-PANT-001-XXL', 1000.00, 4);

-- Insert variant options (sizes) for Hemp top
INSERT INTO variant_options (variant_id, option_name, option_value) 
SELECT id, 'size', 
    CASE 
        WHEN sku LIKE '%-XXS' THEN 'XXS'
        WHEN sku LIKE '%-XS' THEN 'XS' 
        WHEN sku LIKE '%-S' THEN 'S'
        WHEN sku LIKE '%-M' THEN 'M'
        WHEN sku LIKE '%-L' THEN 'L'
        WHEN sku LIKE '%-XL' THEN 'XL'
        WHEN sku LIKE '%-XXL' THEN 'XXL'
    END
FROM product_variants 
WHERE product_id = @hemp_top_id;

-- Insert variant options (sizes) for Linen pants
INSERT INTO variant_options (variant_id, option_name, option_value) 
SELECT id, 'size', 
    CASE 
        WHEN sku LIKE '%-XXS' THEN 'XXS'
        WHEN sku LIKE '%-XS' THEN 'XS'
        WHEN sku LIKE '%-S' THEN 'S'
        WHEN sku LIKE '%-M' THEN 'M'
        WHEN sku LIKE '%-L' THEN 'L'
        WHEN sku LIKE '%-XL' THEN 'XL'
        WHEN sku LIKE '%-XXL' THEN 'XXL'
    END
FROM product_variants 
WHERE product_id = @linen_pant_id;

-- Insert product images
INSERT INTO product_images (product_id, image_url, alt_text, sort_order, is_primary) VALUES 
-- Hemp boutique-Tetris sleeveless top images
(@hemp_top_id, 'products/shirt.jpg', 'Hemp boutique-Tetris sleeveless top', 1, TRUE),
(@hemp_top_id, 'peoplewearingproducts/shirtwear.jpg', 'Hemp boutique-Tetris sleeveless top worn', 2, FALSE),
(@hemp_top_id, 'peoplewearingproducts/shirtwear2.jpg', 'Hemp boutique-Tetris sleeveless top lifestyle', 3, FALSE),

-- Lyocell / Linen pant images
(@linen_pant_id, 'products/pants.jpg', 'Lyocell / Linen pant', 1, TRUE),
(@linen_pant_id, 'peoplewearingproducts/pantswear.jpg', 'Lyocell / Linen pant worn', 2, FALSE),
(@linen_pant_id, 'peoplewearingproducts/pantswear2.jpg', 'Lyocell / Linen pant lifestyle', 3, FALSE);

-- Link products to categories
INSERT INTO product_categories (product_id, category_id) VALUES 
-- Hemp top categories
(@hemp_top_id, (SELECT id FROM categories WHERE slug = 'tops')),
(@hemp_top_id, (SELECT id FROM categories WHERE slug = 'new-arrivals')),
(@hemp_top_id, (SELECT id FROM categories WHERE slug = 'featured')),
(@hemp_top_id, (SELECT id FROM categories WHERE slug = 'sustainable')),

-- Linen pants categories  
(@linen_pant_id, (SELECT id FROM categories WHERE slug = 'bottoms')),
(@linen_pant_id, (SELECT id FROM categories WHERE slug = 'new-arrivals')),
(@linen_pant_id, (SELECT id FROM categories WHERE slug = 'featured')),
(@linen_pant_id, (SELECT id FROM categories WHERE slug = 'sustainable'));

-- Insert product tags
INSERT INTO tags (name, slug) VALUES 
('Hemp', 'hemp'),
('Recycled', 'recycled'),
('Lyocell', 'lyocell'),
('Linen', 'linen'),
('Sustainable', 'sustainable'),
('Eco-friendly', 'eco-friendly'),
('Natural fibers', 'natural-fibers'),
('Geometric pattern', 'geometric-pattern'),
('Sleeveless', 'sleeveless'),
('Pants', 'pants'),
('Breathable', 'breathable'),
('Comfortable', 'comfortable');

-- Link products to tags
INSERT INTO product_tags (product_id, tag_id) VALUES 
-- Hemp top tags
(@hemp_top_id, (SELECT id FROM tags WHERE slug = 'hemp')),
(@hemp_top_id, (SELECT id FROM tags WHERE slug = 'recycled')),
(@hemp_top_id, (SELECT id FROM tags WHERE slug = 'sustainable')),
(@hemp_top_id, (SELECT id FROM tags WHERE slug = 'eco-friendly')),
(@hemp_top_id, (SELECT id FROM tags WHERE slug = 'geometric-pattern')),
(@hemp_top_id, (SELECT id FROM tags WHERE slug = 'sleeveless')),

-- Linen pants tags
(@linen_pant_id, (SELECT id FROM tags WHERE slug = 'lyocell')),
(@linen_pant_id, (SELECT id FROM tags WHERE slug = 'linen')),
(@linen_pant_id, (SELECT id FROM tags WHERE slug = 'sustainable')),
(@linen_pant_id, (SELECT id FROM tags WHERE slug = 'eco-friendly')),
(@linen_pant_id, (SELECT id FROM tags WHERE slug = 'natural-fibers')),
(@linen_pant_id, (SELECT id FROM tags WHERE slug = 'pants')),
(@linen_pant_id, (SELECT id FROM tags WHERE slug = 'breathable')),
(@linen_pant_id, (SELECT id FROM tags WHERE slug = 'comfortable'));

-- Insert some sample discount codes
INSERT INTO discount_codes (code, name, description, type, value, minimum_order_amount, usage_limit, is_active, expires_at) VALUES 
('WELCOME10', 'Welcome Discount', 'Welcome new customers with 10% off', 'percentage', 10.00, 500.00, NULL, TRUE, '2024-12-31 23:59:59'),
('SUSTAINABLE20', 'Sustainable Fashion', '20% off sustainable items', 'percentage', 20.00, 1000.00, 100, TRUE, '2024-12-31 23:59:59'),
('FREESHIP', 'Free Shipping', 'Free shipping on any order', 'free_shipping', 0.00, 0.00, NULL, TRUE, '2024-12-31 23:59:59'),
('NATURAL50', 'Natural Brand Discount', '50 THB off your order', 'fixed_amount', 50.00, 300.00, 200, TRUE, '2024-12-31 23:59:59');

-- Create a sample admin user (password should be hashed in real implementation)
INSERT INTO admin_users (email, password_hash, first_name, last_name, role, is_active) VALUES 
('krisha1467@gmail.com', '$2y$10$example_hash_here', 'Natural', 'Admin', 'admin', TRUE);

-- Add some additional sample products for variety
INSERT INTO products (name, slug, description, short_description, sku, price, currency, is_active, is_featured, is_sustainable, stock_quantity) VALUES 
(
    'Organic Cotton Tee',
    'organic-cotton-tee',
    'Soft, breathable, timeless design made from 100% certified organic cotton. Perfect for everyday wear.',
    'Soft, breathable, timeless design - 100% organic cotton',
    'ORG-COTTON-TEE-001',
    1350.00, -- Converted from $45 to THB (approx)
    'THB',
    TRUE,
    FALSE,
    TRUE,
    25
),
(
    'Recycled Cardigan',
    'recycled-cardigan',
    'Cozy recycled materials cardigan that combines comfort with sustainability.',
    'Cozy recycled materials - Sustainable and warm',
    'REC-CARDIGAN-001', 
    3750.00, -- Converted from $125 to THB (approx)
    'THB',
    TRUE,
    FALSE,
    TRUE,
    15
);

-- =============================================
-- PRODUCT DATA INSERTION COMPLETE
-- =============================================

SELECT 'Database setup completed successfully!' as status;