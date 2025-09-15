-- =============================================
-- DATABASE CONNECTION CONFIGURATION
-- Natural Clothing Website
-- =============================================

-- Database Connection Settings
-- Update these values with your actual database credentials

-- Database Host (usually localhost for local development)
SET @db_host = 'localhost';

-- Database Name
SET @db_name = 'u833511965_natural';

-- Database Username (your MySQL username)
SET @db_username = 'u833511965_krissy';

-- Database Password (your MySQL password)
SET @db_password = '|6GLf^HOvRs';

-- Database Port (default MySQL port)
SET @db_port = 3306;

-- Character Set
SET @db_charset = 'utf8mb4';

-- Connection Test Query
-- Run this to test if your connection settings work
SELECT 
    @db_host as database_host,
    @db_name as database_name,
    @db_username as database_username,
    @db_port as database_port,
    @db_charset as character_set,
    NOW() as connection_test_time,
    'Connection settings loaded successfully' as status;

-- =============================================
-- INSTRUCTIONS FOR USE:
-- =============================================
-- 1. Update the SET variables above with your actual database credentials
-- 2. This file will be imported by config.php for database connections
-- 3. Never commit actual passwords to version control
-- 4. Use environment variables in production

-- =============================================
-- PRODUCTION SECURITY NOTES:
-- =============================================
-- For production environments:
-- 1. Use strong passwords
-- 2. Create a dedicated database user (not root)
-- 3. Grant only necessary permissions
-- 4. Use SSL connections if possible
-- 5. Store credentials in environment variables

-- Example production user creation:
-- CREATE USER 'natural_app'@'localhost' IDENTIFIED BY 'your_strong_password';
-- GRANT SELECT, INSERT, UPDATE, DELETE ON natural_clothing_db.* TO 'natural_app'@'localhost';
-- FLUSH PRIVILEGES;