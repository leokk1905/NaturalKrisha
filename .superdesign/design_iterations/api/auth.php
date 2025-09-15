<?php
// =============================================
// AUTHENTICATION API ENDPOINTS
// Natural Clothing Website
// =============================================

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

require_once __DIR__ . '/Database.php';
require_once __DIR__ . '/../config.php';

// Start session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$userManager = new UserManager();

// Get request method and action
$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? '';

try {
    switch ($action) {
        case 'login':
            if ($method !== 'POST') {
                throw new Exception('Method not allowed');
            }
            handleLogin();
            break;
            
        case 'register':
            if ($method !== 'POST') {
                throw new Exception('Method not allowed');
            }
            handleRegister();
            break;
            
        case 'logout':
            handleLogout();
            break;
            
        case 'check':
            handleCheckAuth();
            break;
            
        case 'profile':
            if ($method === 'GET') {
                handleGetProfile();
            } elseif ($method === 'POST') {
                handleUpdateProfile();
            }
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

function handleLogin() {
    global $userManager;
    
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!$input) {
        $input = $_POST;
    }
    
    $email = sanitizeInput($input['email'] ?? '');
    $password = $input['password'] ?? '';
    $remember = $input['remember'] ?? false;
    
    if (empty($email) || empty($password)) {
        throw new Exception('Email and password are required');
    }
    
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        throw new Exception('Invalid email format');
    }
    
    $user = $userManager->validateLogin($email, $password);
    
    if (!$user) {
        throw new Exception('Invalid email or password');
    }
    
    // Set session
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['user_email'] = $user['email'];
    $_SESSION['user_name'] = $user['first_name'] . ' ' . $user['last_name'];
    $_SESSION['logged_in'] = true;
    
    // Set remember me cookie if requested
    if ($remember) {
        $token = bin2hex(random_bytes(32));
        setcookie('remember_token', $token, time() + (86400 * 30), '/'); // 30 days
        // You should store this token in database for security
    }
    
    // Merge guest cart if exists
    if (isset($_SESSION['guest_session_id'])) {
        $cartManager = new CartManager();
        $cartManager->mergeGuestCartToUser($_SESSION['guest_session_id'], $user['id']);
        unset($_SESSION['guest_session_id']);
    }
    
    echo json_encode([
        'success' => true,
        'message' => 'Login successful',
        'user' => [
            'id' => $user['id'],
            'email' => $user['email'],
            'first_name' => $user['first_name'],
            'last_name' => $user['last_name']
        ]
    ]);
    exit; // Prevent any further output
}

function handleRegister() {
    global $userManager;
    
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!$input) {
        $input = $_POST;
    }
    
    $firstName = sanitizeInput($input['first_name'] ?? '');
    $lastName = sanitizeInput($input['last_name'] ?? '');
    $email = sanitizeInput($input['email'] ?? '');
    $password = $input['password'] ?? '';
    $confirmPassword = $input['confirm_password'] ?? '';
    $phone = sanitizeInput($input['phone'] ?? '');
    
    // Validation
    if (empty($firstName) || empty($lastName) || empty($email) || empty($password)) {
        throw new Exception('All fields are required');
    }
    
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        throw new Exception('Invalid email format');
    }
    
    if (strlen($password) < 8) {
        throw new Exception('Password must be at least 8 characters long');
    }
    
    if ($password !== $confirmPassword) {
        throw new Exception('Passwords do not match');
    }
    
    // Check if user already exists
    if ($userManager->getUserByEmail($email)) {
        throw new Exception('Email already exists');
    }
    
    $userData = [
        'first_name' => $firstName,
        'last_name' => $lastName,
        'email' => $email,
        'password' => $password,
        'phone' => $phone,
        'is_verified' => false
    ];
    
    $userId = $userManager->createUser($userData);
    
    if ($userId) {
        echo json_encode([
            'success' => true,
            'message' => 'Registration successful',
            'user_id' => $userId
        ]);
        exit; // Prevent any further output
    } else {
        throw new Exception('Registration failed');
    }
}

function handleLogout() {
    // Clear session
    session_destroy();
    
    // Clear remember me cookie
    if (isset($_COOKIE['remember_token'])) {
        setcookie('remember_token', '', time() - 3600, '/');
    }
    
    echo json_encode([
        'success' => true,
        'message' => 'Logout successful'
    ]);
    exit; // Prevent any further output
}

function handleCheckAuth() {
    $isLoggedIn = isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true;
    
    if ($isLoggedIn) {
        echo json_encode([
            'success' => true,
            'logged_in' => true,
            'user' => [
                'id' => $_SESSION['user_id'],
                'email' => $_SESSION['user_email'],
                'name' => $_SESSION['user_name']
            ]
        ]);
        exit; // Prevent any further output
    } else {
        echo json_encode([
            'success' => true,
            'logged_in' => false
        ]);
        exit; // Prevent any further output
    }
}

function handleGetProfile() {
    if (!isset($_SESSION['user_id'])) {
        throw new Exception('Not authenticated');
    }
    
    global $userManager;
    $user = $userManager->getUserById($_SESSION['user_id']);
    
    if (!$user) {
        throw new Exception('User not found');
    }
    
    // Remove sensitive data
    unset($user['password_hash']);
    
    echo json_encode([
        'success' => true,
        'user' => $user
    ]);
}

function handleUpdateProfile() {
    if (!isset($_SESSION['user_id'])) {
        throw new Exception('Not authenticated');
    }
    
    global $userManager;
    
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!$input) {
        $input = $_POST;
    }
    
    $allowedFields = ['first_name', 'last_name', 'phone', 'date_of_birth', 'gender'];
    $updateData = [];
    
    foreach ($allowedFields as $field) {
        if (isset($input[$field])) {
            $updateData[$field] = sanitizeInput($input[$field]);
        }
    }
    
    if (isset($input['password']) && !empty($input['password'])) {
        if (strlen($input['password']) < 8) {
            throw new Exception('Password must be at least 8 characters long');
        }
        $updateData['password'] = $input['password'];
    }
    
    if (empty($updateData)) {
        throw new Exception('No data to update');
    }
    
    $result = $userManager->updateUser($_SESSION['user_id'], $updateData);
    
    if ($result) {
        echo json_encode([
            'success' => true,
            'message' => 'Profile updated successfully'
        ]);
    } else {
        throw new Exception('Update failed');
    }
}

// Helper function to require authentication
function requireAuth() {
    if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
        http_response_code(401);
        echo json_encode([
            'success' => false,
            'message' => 'Authentication required'
        ]);
        exit;
    }
}

?>