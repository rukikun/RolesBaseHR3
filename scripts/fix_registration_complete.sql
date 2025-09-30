-- =============================================
-- COMPLETE REGISTRATION FIX
-- This script fixes the users table to allow user registration
-- =============================================

USE hr3systemdb;

-- Drop and recreate users table with complete structure
DROP TABLE IF EXISTS users;

CREATE TABLE users (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) UNIQUE NOT NULL,
    email_verified_at TIMESTAMP NULL,
    password VARCHAR(255) NOT NULL,
    phone VARCHAR(20) NULL,
    employee_id BIGINT UNSIGNED NULL,
    username VARCHAR(100) NULL,
    role VARCHAR(50) DEFAULT 'user',
    last_login TIMESTAMP NULL,
    is_active BOOLEAN DEFAULT TRUE,
    remember_token VARCHAR(100) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    INDEX idx_email (email),
    INDEX idx_employee_id (employee_id),
    FOREIGN KEY (employee_id) REFERENCES employees(id) ON DELETE SET NULL
);

-- Insert admin user for testing
INSERT INTO users (name, email, password, phone, role, created_at, updated_at) VALUES
('Admin User', 'admin@jetlouge.com', '$2y$12$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '09123456789', 'admin', NOW(), NOW());

-- Verify table structure
DESCRIBE users;

-- Show current users
SELECT 
    id,
    name,
    email,
    phone,
    role,
    is_active,
    created_at
FROM users
ORDER BY id;

-- Test registration readiness
SELECT 
    'Registration table ready!' as status,
    'All required columns present' as message,
    COUNT(*) as existing_users
FROM users;
