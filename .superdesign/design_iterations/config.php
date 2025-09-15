<?php
// =============================================
// DATABASE CONFIGURATION
// Natural Clothing Website
// =============================================

// Load database configuration from connectiondb.sql
function loadDatabaseConfig() {
    $config = [
        'host'     => 'localhost',        // Default values
        'username' => 'u833511965_krissy',
        'password' => '|6GLf^HOvRs',
        'database' => 'u833511965_natural',
        'charset'  => 'utf8mb4',
        'port'     => 3306,
    ];
    
    // Try to read configuration from connectiondb.sql
    $connection_file = __DIR__ . '/../../sql/connectiondb.sql';
    
    if (file_exists($connection_file)) {
        $sql_content = file_get_contents($connection_file);
        
        // Extract database configuration from SQL file
        if (preg_match("/SET @db_host = '([^']*)'/",$sql_content, $matches)) {
            $config['host'] = $matches[1];
        }
        if (preg_match("/SET @db_name = '([^']*)'/",$sql_content, $matches)) {
            $config['database'] = $matches[1];
        }
        if (preg_match("/SET @db_username = '([^']*)'/",$sql_content, $matches)) {
            $config['username'] = $matches[1];
        }
        if (preg_match("/SET @db_password = '([^']*)'/",$sql_content, $matches)) {
            $config['password'] = $matches[1];
        }
        if (preg_match("/SET @db_port = ([0-9]+)/",$sql_content, $matches)) {
            $config['port'] = intval($matches[1]);
        }
        if (preg_match("/SET @db_charset = '([^']*)'/",$sql_content, $matches)) {
            $config['charset'] = $matches[1];
        }
    } else {
        // Log warning if connection file is not found
        error_log("Warning: connectiondb.sql not found at {$connection_file}. Using default database configuration.");
    }
    
    return $config;
}

// Load database configuration
$db_config = loadDatabaseConfig();

// Website configuration
$site_config = [
    'site_name'    => 'Natural',
    'site_url'     => 'http://localhost/krissy', // Update this to your actual URL
    'admin_email'  => 'krisha1467@gmail.com',
    'phone'        => '+66 06-4970-3020',
    'timezone'     => 'Asia/Bangkok',
    'currency'     => 'THB',
    'tax_rate'     => 7.00, // Thailand VAT rate
];

// Email configuration (for order confirmations, etc.)
$email_config = [
    'smtp_host'     => 'smtp.gmail.com',  // SMTP server
    'smtp_port'     => 587,               // SMTP port
    'smtp_username' => 'krisha1467@gmail.com', // Your email
    'smtp_password' => '',                // Your email password or app password
    'from_email'    => 'krisha1467@gmail.com',
    'from_name'     => 'Natural Clothing',
];

// Security settings
$security_config = [
    'session_name'      => 'natural_session',
    'password_min_length' => 8,
    'login_attempts'    => 5,           // Max login attempts before lockout
    'lockout_time'      => 900,         // Lockout time in seconds (15 minutes)
    'csrf_token_expire' => 3600,        // CSRF token expiration in seconds
];

// Payment gateway configuration
$payment_config = [
    'stripe_public_key'  => '', // Add your Stripe public key
    'stripe_secret_key'  => '', // Add your Stripe secret key
    'promptpay_enabled'  => true,
    'bank_transfer_enabled' => true,
    'cod_enabled'        => true,
];

// File upload settings
$upload_config = [
    'max_file_size'   => 5 * 1024 * 1024, // 5MB in bytes
    'allowed_types'   => ['jpg', 'jpeg', 'png', 'gif', 'webp'],
    'upload_path'     => 'uploads/',
    'product_images'  => 'uploads/products/',
    'user_avatars'    => 'uploads/avatars/',
];

// Error reporting (set to false in production)
$debug_config = [
    'display_errors' => true,  // Set to false in production
    'log_errors'     => true,
    'error_log_file' => 'logs/error.log',
    'debug_mode'     => true,  // Set to false in production
];

// Set timezone
date_default_timezone_set($site_config['timezone']);

// Database connection function
function getDatabaseConnection() {
    global $db_config;
    
    try {
        $dsn = "mysql:host={$db_config['host']};port={$db_config['port']};dbname={$db_config['database']};charset={$db_config['charset']}";
        
        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ];
        
        return new PDO($dsn, $db_config['username'], $db_config['password'], $options);
    } catch (PDOException $e) {
        error_log("Database connection failed: " . $e->getMessage());
        throw new Exception("Database connection failed");
    }
}

// MySQLi connection function (alternative)
function getMysqliConnection() {
    global $db_config;
    
    $connection = new mysqli(
        $db_config['host'],
        $db_config['username'], 
        $db_config['password'],
        $db_config['database'],
        $db_config['port']
    );
    
    if ($connection->connect_error) {
        error_log("MySQLi connection failed: " . $connection->connect_error);
        throw new Exception("Database connection failed");
    }
    
    $connection->set_charset($db_config['charset']);
    return $connection;
}

// Utility functions
function formatPrice($amount, $currency = 'THB') {
    // Handle null or non-numeric values
    $amount = is_numeric($amount) ? (float)$amount : 0.0;
    
    switch ($currency) {
        case 'THB':
            return '฿' . number_format($amount, 2);
        case 'USD':
            return '$' . number_format($amount, 2);
        default:
            return $currency . ' ' . number_format($amount, 2);
    }
}

function sanitizeInput($data) {
    return htmlspecialchars(strip_tags(trim($data)));
}

function generateCSRFToken() {
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        $_SESSION['csrf_token_time'] = time();
    }
    return $_SESSION['csrf_token'];
}

function verifyCSRFToken($token) {
    global $security_config;
    
    if (!isset($_SESSION['csrf_token']) || !isset($_SESSION['csrf_token_time'])) {
        return false;
    }
    
    if (time() - $_SESSION['csrf_token_time'] > $security_config['csrf_token_expire']) {
        unset($_SESSION['csrf_token']);
        unset($_SESSION['csrf_token_time']);
        return false;
    }
    
    return hash_equals($_SESSION['csrf_token'], $token);
}

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Error handling based on debug mode
if ($debug_config['display_errors']) {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
} else {
    error_reporting(E_ALL);
    ini_set('display_errors', 0);
    ini_set('log_errors', 1);
    ini_set('error_log', $debug_config['error_log_file']);
}

// Create logs directory if it doesn't exist
if (!file_exists('logs')) {
    mkdir('logs', 0755, true);
}

// Create uploads directories if they don't exist
foreach ([$upload_config['upload_path'], $upload_config['product_images'], $upload_config['user_avatars']] as $dir) {
    if (!file_exists($dir)) {
        mkdir($dir, 0755, true);
    }
}

?>