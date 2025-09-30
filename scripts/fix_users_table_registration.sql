-- =============================================
-- FIX USERS TABLE FOR REGISTRATION
-- This script fixes the users table structure to allow registration
-- =============================================

USE hr3systemdb;

-- Create users table if it doesn't exist with proper structure
CREATE TABLE IF NOT EXISTS users (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) UNIQUE NOT NULL,
    email_verified_at TIMESTAMP NULL,
    password VARCHAR(255) NOT NULL,
    phone VARCHAR(20) NULL,
    remember_token VARCHAR(100) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Add phone column if it doesn't exist
ALTER TABLE users ADD COLUMN IF NOT EXISTS phone VARCHAR(20) NULL;

-- Verify table structure
DESCRIBE users;

-- Show current users
SELECT 
    id,
    name,
    email,
    phone,
    created_at
FROM users
ORDER BY id;

-- Create a test admin user if none exists
INSERT IGNORE INTO users (name, email, password, phone, created_at, updated_at) VALUES
('Admin User', 'admin@jetlouge.com', '$2y$12$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '09123456789', NOW(), NOW());

-- Verification
SELECT 
    'Users table structure fixed!' as status,
    COUNT(*) as total_users
FROM users;
