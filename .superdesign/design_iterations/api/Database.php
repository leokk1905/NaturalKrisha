<?php
// =============================================
// DATABASE API LAYER
// Natural Clothing Website
// =============================================

require_once __DIR__ . '/../config.php';

class Database {
    private static $instance = null;
    private $connection = null;
    
    private function __construct() {
        $this->connect();
    }
    
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new Database();
        }
        return self::$instance;
    }
    
    private function connect() {
        global $db_config;
        
        try {
            $this->connection = new mysqli(
                $db_config['host'],
                $db_config['username'],
                $db_config['password'],
                $db_config['database'],
                $db_config['port']
            );
            
            if ($this->connection->connect_error) {
                throw new Exception("Database connection failed: " . $this->connection->connect_error);
            }
            
            $this->connection->set_charset($db_config['charset']);
            
        } catch (Exception $e) {
            error_log("Database connection error: " . $e->getMessage());
            throw new Exception("Database connection failed");
        }
    }
    
    public function getConnection() {
        return $this->connection;
    }
    
    public function query($sql, $params = []) {
        try {
            if (empty($params)) {
                $result = $this->connection->query($sql);
                if ($result === false) {
                    throw new Exception("Query failed: " . $this->connection->error);
                }
                return $result;
            }
            
            $stmt = $this->connection->prepare($sql);
            if ($stmt === false) {
                throw new Exception("Prepare failed: " . $this->connection->error);
            }
            
            if (!empty($params)) {
                $types = '';
                $values = [];
                
                foreach ($params as $param) {
                    if (is_int($param)) {
                        $types .= 'i';
                    } elseif (is_float($param)) {
                        $types .= 'd';
                    } else {
                        $types .= 's';
                    }
                    $values[] = $param;
                }
                
                $stmt->bind_param($types, ...$values);
            }
            
            if (!$stmt->execute()) {
                throw new Exception("Execute failed: " . $stmt->error);
            }
            
            $result = $stmt->get_result();
            $stmt->close();
            
            return $result;
            
        } catch (Exception $e) {
            error_log("Database query error: " . $e->getMessage());
            throw $e;
        }
    }
    
    public function fetchAll($sql, $params = []) {
        $result = $this->query($sql, $params);
        $data = [];
        
        if ($result && $result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $data[] = $row;
            }
        }
        
        return $data;
    }
    
    public function fetchOne($sql, $params = []) {
        $result = $this->query($sql, $params);
        
        if ($result && $result->num_rows > 0) {
            return $result->fetch_assoc();
        }
        
        return null;
    }
    
    public function insert($table, $data) {
        $columns = implode(', ', array_keys($data));
        $placeholders = str_repeat('?,', count($data) - 1) . '?';
        
        $sql = "INSERT INTO {$table} ({$columns}) VALUES ({$placeholders})";
        
        $this->query($sql, array_values($data));
        
        return $this->connection->insert_id;
    }
    
    public function update($table, $data, $where, $whereParams = []) {
        $setParts = [];
        foreach (array_keys($data) as $column) {
            $setParts[] = "{$column} = ?";
        }
        $setClause = implode(', ', $setParts);
        
        $sql = "UPDATE {$table} SET {$setClause} WHERE {$where}";
        
        $params = array_merge(array_values($data), $whereParams);
        
        $this->query($sql, $params);
        
        return $this->connection->affected_rows;
    }
    
    public function delete($table, $where, $whereParams = []) {
        $sql = "DELETE FROM {$table} WHERE {$where}";
        
        $this->query($sql, $whereParams);
        
        return $this->connection->affected_rows;
    }
    
    public function getLastInsertId() {
        return $this->connection->insert_id;
    }
    
    public function beginTransaction() {
        $this->connection->autocommit(false);
    }
    
    public function commit() {
        $this->connection->commit();
        $this->connection->autocommit(true);
    }
    
    public function rollback() {
        $this->connection->rollback();
        $this->connection->autocommit(true);
    }
    
    public function escape($string) {
        return $this->connection->real_escape_string($string);
    }
    
    public function __destruct() {
        if ($this->connection) {
            $this->connection->close();
        }
    }
}

// =============================================
// PRODUCT DATABASE OPERATIONS
// =============================================

class ProductManager {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance();
    }
    
    public function getAllProducts($limit = null, $offset = 0) {
        $sql = "SELECT p.*, 
                       (SELECT image_url FROM product_images pi WHERE pi.product_id = p.id AND pi.is_primary = 1 LIMIT 1) as primary_image
                FROM products p 
                WHERE p.is_active = 1 
                ORDER BY p.is_featured DESC, p.created_at DESC";
        
        if ($limit) {
            $sql .= " LIMIT ? OFFSET ?";
            return $this->db->fetchAll($sql, [$limit, $offset]);
        }
        
        return $this->db->fetchAll($sql);
    }
    
    public function getProductById($id) {
        $sql = "SELECT p.*, 
                       GROUP_CONCAT(DISTINCT pi.image_url ORDER BY pi.sort_order) as images,
                       GROUP_CONCAT(DISTINCT c.name) as categories
                FROM products p
                LEFT JOIN product_images pi ON p.id = pi.product_id
                LEFT JOIN product_categories pc ON p.id = pc.product_id
                LEFT JOIN categories c ON pc.category_id = c.id
                WHERE p.id = ? AND p.is_active = 1
                GROUP BY p.id";
        
        return $this->db->fetchOne($sql, [$id]);
    }
    
    public function getProductBySlug($slug) {
        $sql = "SELECT p.*, 
                       GROUP_CONCAT(DISTINCT pi.image_url ORDER BY pi.sort_order) as images,
                       GROUP_CONCAT(DISTINCT c.name) as categories
                FROM products p
                LEFT JOIN product_images pi ON p.id = pi.product_id
                LEFT JOIN product_categories pc ON p.id = pc.product_id
                LEFT JOIN categories c ON pc.category_id = c.id
                WHERE p.slug = ? AND p.is_active = 1
                GROUP BY p.id";
        
        return $this->db->fetchOne($sql, [$slug]);
    }
    
    public function getProductVariants($productId) {
        $sql = "SELECT pv.*, 
                       GROUP_CONCAT(CONCAT(vo.option_name, ':', vo.option_value) SEPARATOR '|') as options
                FROM product_variants pv
                LEFT JOIN variant_options vo ON pv.id = vo.variant_id
                WHERE pv.product_id = ? AND pv.is_active = 1
                GROUP BY pv.id
                ORDER BY pv.id";
        
        return $this->db->fetchAll($sql, [$productId]);
    }
    
    public function getFeaturedProducts($limit = 4) {
        $sql = "SELECT p.*, 
                       (SELECT image_url FROM product_images pi WHERE pi.product_id = p.id AND pi.is_primary = 1 LIMIT 1) as primary_image
                FROM products p 
                WHERE p.is_active = 1 AND p.is_featured = 1 
                ORDER BY p.created_at DESC 
                LIMIT ?";
        
        return $this->db->fetchAll($sql, [$limit]);
    }
    
    public function getProductsByCategory($categorySlug, $limit = null) {
        $sql = "SELECT p.*, 
                       (SELECT image_url FROM product_images pi WHERE pi.product_id = p.id AND pi.is_primary = 1 LIMIT 1) as primary_image
                FROM products p
                JOIN product_categories pc ON p.id = pc.product_id
                JOIN categories c ON pc.category_id = c.id
                WHERE p.is_active = 1 AND c.slug = ?
                ORDER BY p.created_at DESC";
        
        if ($limit) {
            $sql .= " LIMIT ?";
            return $this->db->fetchAll($sql, [$categorySlug, $limit]);
        }
        
        return $this->db->fetchAll($sql, [$categorySlug]);
    }
    
    public function searchProducts($query, $limit = 20) {
        $searchTerm = "%{$query}%";
        $sql = "SELECT p.*, 
                       (SELECT image_url FROM product_images pi WHERE pi.product_id = p.id AND pi.is_primary = 1 LIMIT 1) as primary_image
                FROM products p 
                WHERE p.is_active = 1 
                AND (p.name LIKE ? OR p.description LIKE ? OR p.short_description LIKE ?)
                ORDER BY p.is_featured DESC, p.created_at DESC 
                LIMIT ?";
        
        return $this->db->fetchAll($sql, [$searchTerm, $searchTerm, $searchTerm, $limit]);
    }
}

// =============================================
// USER DATABASE OPERATIONS
// =============================================

class UserManager {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance();
    }
    
    public function createUser($userData) {
        // Hash password
        $userData['password_hash'] = password_hash($userData['password'], PASSWORD_DEFAULT);
        unset($userData['password']);
        
        $userData['created_at'] = date('Y-m-d H:i:s');
        
        try {
            return $this->db->insert('users', $userData);
        } catch (Exception $e) {
            if (strpos($e->getMessage(), 'Duplicate entry') !== false) {
                throw new Exception('Email already exists');
            }
            throw $e;
        }
    }
    
    public function getUserByEmail($email) {
        $sql = "SELECT * FROM users WHERE email = ? AND is_active = 1";
        return $this->db->fetchOne($sql, [$email]);
    }
    
    public function getUserById($id) {
        $sql = "SELECT * FROM users WHERE id = ? AND is_active = 1";
        return $this->db->fetchOne($sql, [$id]);
    }
    
    public function validateLogin($email, $password) {
        $user = $this->getUserByEmail($email);
        
        if ($user && password_verify($password, $user['password_hash'])) {
            // Update last login
            $this->db->update('users', 
                ['last_login_at' => date('Y-m-d H:i:s')], 
                'id = ?', 
                [$user['id']]
            );
            
            return $user;
        }
        
        return false;
    }
    
    public function updateUser($userId, $userData) {
        if (isset($userData['password'])) {
            $userData['password_hash'] = password_hash($userData['password'], PASSWORD_DEFAULT);
            unset($userData['password']);
        }
        
        $userData['updated_at'] = date('Y-m-d H:i:s');
        
        return $this->db->update('users', $userData, 'id = ?', [$userId]);
    }
    
    public function getUserAddresses($userId) {
        $sql = "SELECT * FROM user_addresses WHERE user_id = ? ORDER BY is_default DESC, created_at DESC";
        return $this->db->fetchAll($sql, [$userId]);
    }
    
    public function addUserAddress($userId, $addressData) {
        $addressData['user_id'] = $userId;
        $addressData['created_at'] = date('Y-m-d H:i:s');
        
        return $this->db->insert('user_addresses', $addressData);
    }
}

// =============================================
// CART DATABASE OPERATIONS
// =============================================

class CartManager {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance();
    }
    
    public function addToCart($userId, $sessionId, $productId, $variantId, $quantity, $price) {
        // Check if item already exists in cart
        $existingItem = $this->getCartItem($userId, $sessionId, $productId, $variantId);
        
        if ($existingItem) {
            // Update quantity
            $newQuantity = $existingItem['quantity'] + $quantity;
            return $this->updateCartItemQuantity($existingItem['id'], $newQuantity);
        } else {
            // Add new item
            $cartData = [
                'user_id' => $userId,
                'session_id' => $sessionId,
                'product_id' => $productId,
                'variant_id' => $variantId,
                'quantity' => $quantity,
                'price' => $price,
                'created_at' => date('Y-m-d H:i:s')
            ];
            
            return $this->db->insert('cart_items', $cartData);
        }
    }
    
    public function getCartItem($userId, $sessionId, $productId, $variantId) {
        $sql = "SELECT * FROM cart_items 
                WHERE product_id = ? AND variant_id = ? 
                AND " . ($userId ? "user_id = ?" : "session_id = ?");
        
        $params = [$productId, $variantId, $userId ?: $sessionId];
        return $this->db->fetchOne($sql, $params);
    }
    
    public function getCartItems($userId, $sessionId) {
        $sql = "SELECT ci.*, p.name as product_name, p.slug as product_slug, 
                       pv.name as variant_name,
                       (SELECT image_url FROM product_images pi WHERE pi.product_id = p.id AND pi.is_primary = 1 LIMIT 1) as product_image
                FROM cart_items ci
                JOIN products p ON ci.product_id = p.id
                LEFT JOIN product_variants pv ON ci.variant_id = pv.id
                WHERE " . ($userId ? "ci.user_id = ?" : "ci.session_id = ?") . "
                ORDER BY ci.created_at DESC";
        
        return $this->db->fetchAll($sql, [$userId ?: $sessionId]);
    }
    
    public function updateCartItemQuantity($cartItemId, $quantity) {
        if ($quantity <= 0) {
            return $this->removeCartItem($cartItemId);
        }
        
        return $this->db->update('cart_items', 
            ['quantity' => $quantity, 'updated_at' => date('Y-m-d H:i:s')], 
            'id = ?', 
            [$cartItemId]
        );
    }
    
    public function removeCartItem($cartItemId) {
        return $this->db->delete('cart_items', 'id = ?', [$cartItemId]);
    }
    
    public function clearCart($userId, $sessionId) {
        $where = $userId ? 'user_id = ?' : 'session_id = ?';
        return $this->db->delete('cart_items', $where, [$userId ?: $sessionId]);
    }
    
    public function getCartTotal($userId, $sessionId) {
        $sql = "SELECT SUM(ci.quantity * ci.price) as total
                FROM cart_items ci
                WHERE " . ($userId ? "ci.user_id = ?" : "ci.session_id = ?");
        
        $result = $this->db->fetchOne($sql, [$userId ?: $sessionId]);
        return $result ? $result['total'] : 0;
    }
    
    public function getCartItemCount($userId, $sessionId) {
        $sql = "SELECT SUM(quantity) as count
                FROM cart_items 
                WHERE " . ($userId ? "user_id = ?" : "session_id = ?");
        
        $result = $this->db->fetchOne($sql, [$userId ?: $sessionId]);
        return $result ? $result['count'] : 0;
    }
    
    public function mergeGuestCartToUser($sessionId, $userId) {
        // Update all cart items from session to user
        return $this->db->update('cart_items', 
            ['user_id' => $userId, 'session_id' => null], 
            'session_id = ?', 
            [$sessionId]
        );
    }
}

?>