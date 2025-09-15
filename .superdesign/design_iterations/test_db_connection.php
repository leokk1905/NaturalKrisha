<?php
// =============================================
// DATABASE CONNECTION TEST PAGE
// Natural Clothing Website
// =============================================

// Start output buffering to capture any errors
ob_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Database configuration
$config = [
    'host'     => 'localhost',        // Default values
    'username' => 'u833511965_krissy',
    'password' => '|6GLf^HOvRs',
    'database' => 'u833511965_natural',
    'charset'  => 'utf8mb4',
    'port'     => 3306,
];


// Test results array
$tests = [];
$overall_status = true;

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Database Connection Test - Natural</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, sans-serif;
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            min-height: 100vh;
            padding: 20px;
        }
        
        .container {
            max-width: 800px;
            margin: 0 auto;
            background: white;
            border-radius: 10px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            overflow: hidden;
        }
        
        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 30px;
            text-align: center;
        }
        
        .header h1 {
            font-size: 2rem;
            margin-bottom: 10px;
        }
        
        .header p {
            opacity: 0.9;
            font-size: 1.1rem;
        }
        
        .content {
            padding: 30px;
        }
        
        .test-section {
            margin-bottom: 30px;
        }
        
        .test-section h2 {
            color: #333;
            margin-bottom: 15px;
            font-size: 1.5rem;
            display: flex;
            align-items: center;
        }
        
        .status-icon {
            width: 20px;
            height: 20px;
            border-radius: 50%;
            margin-right: 10px;
            display: inline-block;
        }
        
        .status-success {
            background: #10b981;
        }
        
        .status-error {
            background: #ef4444;
        }
        
        .status-warning {
            background: #f59e0b;
        }
        
        .test-item {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 10px;
        }
        
        .test-item.success {
            border-left: 4px solid #10b981;
            background: #f0fdf4;
        }
        
        .test-item.error {
            border-left: 4px solid #ef4444;
            background: #fef2f2;
        }
        
        .test-item.warning {
            border-left: 4px solid #f59e0b;
            background: #fffbeb;
        }
        
        .test-title {
            font-weight: 600;
            color: #1f2937;
            margin-bottom: 5px;
        }
        
        .test-description {
            color: #6b7280;
            font-size: 0.9rem;
            line-height: 1.4;
        }
        
        .config-info {
            background: #f1f5f9;
            border: 1px solid #cbd5e1;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 20px;
        }
        
        .config-info h3 {
            color: #334155;
            margin-bottom: 10px;
        }
        
        .config-item {
            display: flex;
            justify-content: space-between;
            padding: 5px 0;
            border-bottom: 1px solid #e2e8f0;
        }
        
        .config-item:last-child {
            border-bottom: none;
        }
        
        .config-key {
            font-weight: 500;
            color: #475569;
        }
        
        .config-value {
            color: #64748b;
            font-family: monospace;
        }
        
        .overall-status {
            text-align: center;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-size: 1.2rem;
            font-weight: 600;
        }
        
        .overall-status.success {
            background: #dcfce7;
            color: #166534;
            border: 1px solid #bbf7d0;
        }
        
        .overall-status.error {
            background: #fee2e2;
            color: #991b1b;
            border: 1px solid #fecaca;
        }
        
        .refresh-btn {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            padding: 12px 24px;
            border-radius: 6px;
            cursor: pointer;
            font-size: 1rem;
            font-weight: 500;
            transition: transform 0.2s;
            display: block;
            margin: 20px auto 0;
        }
        
        .refresh-btn:hover {
            transform: translateY(-2px);
        }
        
        .timestamp {
            text-align: center;
            color: #6b7280;
            font-size: 0.9rem;
            margin-top: 20px;
        }
        
        pre {
            background: #1f2937;
            color: #f9fafb;
            padding: 15px;
            border-radius: 6px;
            overflow-x: auto;
            font-size: 0.9rem;
            margin-top: 10px;
        }
        
        .table-list {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 10px;
            margin-top: 10px;
        }
        
        .table-item {
            background: #f8fafc;
            padding: 8px 12px;
            border-radius: 6px;
            border: 1px solid #e2e8f0;
            font-family: monospace;
            font-size: 0.9rem;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🌱 Natural Clothing</h1>
            <p>Database Connection Test</p>
        </div>
        
        <div class="content">
            <?php
            // Test 1: PHP Extensions
            $tests['extensions'] = [];
            
            // Check if mysqli extension is loaded
            if (extension_loaded('mysqli')) {
                $tests['extensions'][] = [
                    'name' => 'MySQLi Extension',
                    'status' => 'success',
                    'message' => 'MySQLi extension is loaded and available'
                ];
            } else {
                $tests['extensions'][] = [
                    'name' => 'MySQLi Extension',
                    'status' => 'error', 
                    'message' => 'MySQLi extension is not loaded. Please install php-mysqli'
                ];
                $overall_status = false;
            }
            
            // Check if PDO extension is loaded
            if (extension_loaded('pdo') && extension_loaded('pdo_mysql')) {
                $tests['extensions'][] = [
                    'name' => 'PDO MySQL Extension',
                    'status' => 'success',
                    'message' => 'PDO MySQL extension is loaded and available'
                ];
            } else {
                $tests['extensions'][] = [
                    'name' => 'PDO MySQL Extension', 
                    'status' => 'warning',
                    'message' => 'PDO MySQL extension is not loaded. MySQLi will be used instead'
                ];
            }
            
            // Test 2: Database Connection
            $connection = null;
            $connection_error = '';
            
            try {
                $connection = new mysqli($config['host'], $config['username'], $config['password'], $config['database']);
                
                if ($connection->connect_error) {
                    throw new Exception("Connection failed: " . $connection->connect_error);
                }
                
                // Set charset
                $connection->set_charset($config['charset']);
                
                $tests['connection'][] = [
                    'name' => 'Database Connection',
                    'status' => 'success',
                    'message' => "Successfully connected to database '{$config['database']}' on '{$config['host']}'"
                ];
                
                // Test server info
                $server_info = $connection->server_info;
                $tests['connection'][] = [
                    'name' => 'MySQL Server Version',
                    'status' => 'success',
                    'message' => "MySQL Server Version: {$server_info}"
                ];
                
            } catch (Exception $e) {
                $connection_error = $e->getMessage();
                $tests['connection'][] = [
                    'name' => 'Database Connection',
                    'status' => 'error',
                    'message' => $connection_error
                ];
                $overall_status = false;
            }
            
            // Test 3: Database Structure (if connection successful)
            if ($connection && !$connection->connect_error) {
                
                // Check if database exists and get table count
                $result = $connection->query("SELECT COUNT(*) as table_count FROM information_schema.tables WHERE table_schema = '{$config['database']}'");
                
                if ($result) {
                    $row = $result->fetch_assoc();
                    $table_count = $row['table_count'];
                    
                    if ($table_count > 0) {
                        $tests['structure'][] = [
                            'name' => 'Database Tables',
                            'status' => 'success',
                            'message' => "Found {$table_count} tables in database"
                        ];
                        
                        // Get table names
                        $table_result = $connection->query("SHOW TABLES");
                        $tables = [];
                        while ($table_row = $table_result->fetch_row()) {
                            $tables[] = $table_row[0];
                        }
                        
                        // Check for key tables
                        $required_tables = ['users', 'products', 'orders', 'categories'];
                        $missing_tables = [];
                        
                        foreach ($required_tables as $table) {
                            if (in_array($table, $tables)) {
                                $tests['structure'][] = [
                                    'name' => "Table: {$table}",
                                    'status' => 'success',
                                    'message' => "Core table '{$table}' exists"
                                ];
                            } else {
                                $missing_tables[] = $table;
                            }
                        }
                        
                        if (!empty($missing_tables)) {
                            $tests['structure'][] = [
                                'name' => 'Missing Tables',
                                'status' => 'warning', 
                                'message' => 'Missing core tables: ' . implode(', ', $missing_tables) . '. Run setup_database.sql to create them.'
                            ];
                        }
                        
                    } else {
                        $tests['structure'][] = [
                            'name' => 'Database Tables',
                            'status' => 'warning',
                            'message' => 'Database exists but contains no tables. Run setup_database.sql to create the schema.'
                        ];
                    }
                } else {
                    $tests['structure'][] = [
                        'name' => 'Database Structure Check',
                        'status' => 'error',
                        'message' => 'Could not check database structure: ' . $connection->error
                    ];
                }
            }
            
            // Test 4: Basic Query Test
            if ($connection && !$connection->connect_error) {
                $test_query = "SELECT 1 as test_value, NOW() as test_time";
                $result = $connection->query($test_query);
                
                if ($result) {
                    $row = $result->fetch_assoc();
                    $tests['query'][] = [
                        'name' => 'Basic Query Test',
                        'status' => 'success',
                        'message' => "Query executed successfully. Current time: " . $row['test_time']
                    ];
                } else {
                    $tests['query'][] = [
                        'name' => 'Basic Query Test',
                        'status' => 'error',
                        'message' => 'Query test failed: ' . $connection->error
                    ];
                    $overall_status = false;
                }
            }
            
            // Close connection
            if ($connection) {
                $connection->close();
            }
            ?>
            
            <!-- Overall Status -->
            <div class="overall-status <?php echo $overall_status ? 'success' : 'error'; ?>">
                <?php if ($overall_status): ?>
                    ✅ Database Connection Test Passed!
                <?php else: ?>
                    ❌ Database Connection Test Failed
                <?php endif; ?>
            </div>
            
            <!-- Configuration Info -->
            <div class="config-info">
                <h3>📋 Database Configuration</h3>
                <div class="config-item">
                    <span class="config-key">Host:</span>
                    <span class="config-value"><?php echo htmlspecialchars($config['host']); ?></span>
                </div>
                <div class="config-item">
                    <span class="config-key">Database:</span>
                    <span class="config-value"><?php echo htmlspecialchars($config['database']); ?></span>
                </div>
                <div class="config-item">
                    <span class="config-key">Username:</span>
                    <span class="config-value"><?php echo htmlspecialchars($config['username']); ?></span>
                </div>
                <div class="config-item">
                    <span class="config-key">Charset:</span>
                    <span class="config-value"><?php echo htmlspecialchars($config['charset']); ?></span>
                </div>
                <div class="config-item">
                    <span class="config-key">PHP Version:</span>
                    <span class="config-value"><?php echo PHP_VERSION; ?></span>
                </div>
            </div>
            
            <!-- Test Results -->
            <?php foreach ($tests as $section_name => $section_tests): ?>
                <?php if (!empty($section_tests)): ?>
                    <div class="test-section">
                        <?php
                        $section_titles = [
                            'extensions' => '🔧 PHP Extensions',
                            'connection' => '🔌 Database Connection', 
                            'structure' => '🏗️ Database Structure',
                            'query' => '💾 Query Testing'
                        ];
                        
                        $section_status = 'success';
                        foreach ($section_tests as $test) {
                            if ($test['status'] === 'error') {
                                $section_status = 'error';
                                break;
                            } elseif ($test['status'] === 'warning' && $section_status !== 'error') {
                                $section_status = 'warning';
                            }
                        }
                        ?>
                        
                        <h2>
                            <span class="status-icon status-<?php echo $section_status; ?>"></span>
                            <?php echo $section_titles[$section_name] ?? ucfirst($section_name); ?>
                        </h2>
                        
                        <?php foreach ($section_tests as $test): ?>
                            <div class="test-item <?php echo $test['status']; ?>">
                                <div class="test-title"><?php echo htmlspecialchars($test['name']); ?></div>
                                <div class="test-description"><?php echo htmlspecialchars($test['message']); ?></div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            <?php endforeach; ?>
            
            <!-- Show table list if available -->
            <?php if (isset($tables) && !empty($tables)): ?>
                <div class="test-section">
                    <h2>
                        <span class="status-icon status-success"></span>
                        📊 Database Tables (<?php echo count($tables); ?>)
                    </h2>
                    <div class="table-list">
                        <?php foreach ($tables as $table): ?>
                            <div class="table-item"><?php echo htmlspecialchars($table); ?></div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>
            
            <!-- Additional Information -->
            <div class="test-section">
                <h2>
                    <span class="status-icon status-success"></span>
                    ℹ️ Next Steps
                </h2>
                <div class="test-item success">
                    <div class="test-title">Database Setup</div>
                    <div class="test-description">
                        If you see warnings about missing tables, run the following SQL files in order:
                        <pre>1. sql/setup_database.sql  (Creates all tables)
2. sql/insert_products.sql  (Adds your product data)</pre>
                    </div>
                </div>
                <div class="test-item success">
                    <div class="test-title">Configuration</div>
                    <div class="test-description">
                        Update the database configuration at the top of this file with your actual database credentials.
                    </div>
                </div>
            </div>
            
            <button class="refresh-btn" onclick="location.reload()">🔄 Refresh Test</button>
            
            <div class="timestamp">
                Last tested: <?php echo date('Y-m-d H:i:s T'); ?>
            </div>
        </div>
    </div>
</body>
</html>

