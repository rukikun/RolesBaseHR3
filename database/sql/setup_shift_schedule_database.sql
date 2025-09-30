-- Shift Schedule Management Database Setup
-- This file creates the necessary tables and sample data for the shift schedule management system

-- Create shift_types table
CREATE TABLE IF NOT EXISTS shift_types (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    type ENUM('day', 'night', 'swing', 'split', 'rotating') NOT NULL,
    default_start_time TIME NOT NULL,
    default_end_time TIME NOT NULL,
    break_duration INT DEFAULT 30,
    hourly_rate DECIMAL(8,2) NULL,
    description TEXT NULL,
    color_code VARCHAR(7) DEFAULT '#007bff',
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Create shifts table
CREATE TABLE IF NOT EXISTS shifts (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    employee_id BIGINT UNSIGNED NOT NULL,
    shift_type_id BIGINT UNSIGNED NOT NULL,
    date DATE NOT NULL,
    start_time TIME NOT NULL,
    end_time TIME NOT NULL,
    notes TEXT NULL,
    status ENUM('scheduled', 'completed', 'cancelled', 'no_show') DEFAULT 'scheduled',
    created_by BIGINT UNSIGNED NULL,
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (employee_id) REFERENCES employees(id) ON DELETE CASCADE,
    FOREIGN KEY (shift_type_id) REFERENCES shift_types(id) ON DELETE CASCADE,
    FOREIGN KEY (created_by) REFERENCES employees(id) ON DELETE SET NULL
);

-- Create shift_requests table
CREATE TABLE IF NOT EXISTS shift_requests (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    employee_id BIGINT UNSIGNED NOT NULL,
    request_type ENUM('change', 'swap', 'overtime', 'time_off') NOT NULL,
    current_shift_id BIGINT UNSIGNED NULL,
    requested_shift_id BIGINT UNSIGNED NULL,
    requested_date DATE NOT NULL,
    reason TEXT NOT NULL,
    status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
    approved_by BIGINT UNSIGNED NULL,
    approved_at TIMESTAMP NULL,
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (employee_id) REFERENCES employees(id) ON DELETE CASCADE,
    FOREIGN KEY (current_shift_id) REFERENCES shifts(id) ON DELETE CASCADE,
    FOREIGN KEY (requested_shift_id) REFERENCES shifts(id) ON DELETE CASCADE,
    FOREIGN KEY (approved_by) REFERENCES employees(id) ON DELETE SET NULL
);

-- Clear existing data to avoid duplicates
DELETE FROM shift_requests WHERE id > 0;
DELETE FROM shifts WHERE id > 0;
DELETE FROM shift_types WHERE id > 0;

-- Reset auto increment
ALTER TABLE shift_types AUTO_INCREMENT = 1;
ALTER TABLE shifts AUTO_INCREMENT = 1;
ALTER TABLE shift_requests AUTO_INCREMENT = 1;

-- Insert sample shift types
INSERT INTO shift_types (name, type, default_start_time, default_end_time, break_duration, hourly_rate, description, color_code, is_active) VALUES
('Morning Shift', 'day', '08:00:00', '16:00:00', 60, 25.00, 'Standard morning shift for day operations', '#28a745', TRUE),
('Evening Shift', 'swing', '16:00:00', '00:00:00', 45, 27.50, 'Evening shift covering afternoon to midnight', '#ffc107', TRUE),
('Night Shift', 'night', '00:00:00', '08:00:00', 30, 30.00, 'Overnight shift with night differential', '#6f42c1', TRUE),
('Split Shift', 'split', '09:00:00', '17:00:00', 90, 24.00, 'Split shift with extended break period', '#17a2b8', TRUE),
('Weekend Day', 'day', '10:00:00', '18:00:00', 60, 28.00, 'Weekend day shift with premium rate', '#fd7e14', TRUE),
('Rotating Shift', 'rotating', '07:00:00', '15:00:00', 45, 26.50, 'Rotating shift pattern for continuous coverage', '#e83e8c', TRUE),
('Part-Time Morning', 'day', '09:00:00', '13:00:00', 0, 22.00, 'Part-time morning shift', '#20c997', TRUE),
('Part-Time Evening', 'swing', '18:00:00', '22:00:00', 0, 24.00, 'Part-time evening shift', '#6610f2', TRUE);

-- Insert sample shifts
INSERT INTO shifts (employee_id, shift_type_id, date, start_time, end_time, notes, status, created_by, created_at) VALUES
(1, 1, '2024-02-01', '08:00:00', '16:00:00', 'Regular morning shift', 'scheduled', 1, '2024-01-25 10:00:00'),
(2, 2, '2024-02-01', '16:00:00', '00:00:00', 'Evening coverage', 'scheduled', 1, '2024-01-25 10:05:00'),
(3, 3, '2024-02-01', '00:00:00', '08:00:00', 'Night security shift', 'scheduled', 1, '2024-01-25 10:10:00'),
(4, 1, '2024-02-02', '08:00:00', '16:00:00', 'Friday morning shift', 'scheduled', 1, '2024-01-25 10:15:00'),
(5, 4, '2024-02-02', '09:00:00', '17:00:00', 'Split shift with lunch break', 'scheduled', 1, '2024-01-25 10:20:00'),
(1, 5, '2024-02-03', '10:00:00', '18:00:00', 'Weekend premium shift', 'scheduled', 1, '2024-01-25 10:25:00'),
(2, 5, '2024-02-04', '10:00:00', '18:00:00', 'Sunday weekend shift', 'scheduled', 1, '2024-01-25 10:30:00'),
(3, 6, '2024-02-05', '07:00:00', '15:00:00', 'Rotating shift week 1', 'scheduled', 1, '2024-01-25 10:35:00'),
(4, 7, '2024-02-05', '09:00:00', '13:00:00', 'Part-time morning coverage', 'scheduled', 1, '2024-01-25 10:40:00'),
(5, 8, '2024-02-05', '18:00:00', '22:00:00', 'Part-time evening support', 'scheduled', 1, '2024-01-25 10:45:00'),
(1, 2, '2024-02-06', '16:00:00', '00:00:00', 'Covering for sick employee', 'scheduled', 1, '2024-01-25 10:50:00'),
(2, 1, '2024-02-06', '08:00:00', '16:00:00', 'Switched to morning shift', 'completed', 1, '2024-01-25 10:55:00'),
(3, 4, '2024-02-07', '09:00:00', '17:00:00', 'Training on split shift', 'completed', 1, '2024-01-25 11:00:00'),
(4, 3, '2024-02-07', '00:00:00', '08:00:00', 'Night shift overtime', 'scheduled', 1, '2024-01-25 11:05:00'),
(5, 1, '2024-02-08', '08:00:00', '16:00:00', 'Regular Thursday shift', 'scheduled', 1, '2024-01-25 11:10:00');

-- Insert sample shift requests
INSERT INTO shift_requests (employee_id, request_type, current_shift_id, requested_shift_id, requested_date, reason, status, created_at) VALUES
(2, 'change', 2, 1, '2024-02-10', 'Need to attend medical appointment in the evening', 'pending', '2024-01-26 09:00:00'),
(3, 'swap', 3, 4, '2024-02-12', 'Family emergency requires day availability', 'pending', '2024-01-26 09:30:00'),
(4, 'overtime', NULL, NULL, '2024-02-15', 'Requesting additional hours for project completion', 'approved', '2024-01-26 10:00:00'),
(1, 'time_off', 1, NULL, '2024-02-20', 'Personal day for wedding anniversary', 'approved', '2024-01-26 10:30:00'),
(5, 'change', 5, 7, '2024-02-25', 'Prefer shorter shift due to childcare needs', 'pending', '2024-01-26 11:00:00'),
(2, 'swap', 11, 12, '2024-02-28', 'Want to switch back to regular schedule', 'rejected', '2024-01-26 11:30:00'),
(3, 'overtime', NULL, NULL, '2024-03-01', 'Available for extra night shifts this week', 'pending', '2024-01-26 12:00:00'),
(4, 'time_off', 14, NULL, '2024-03-05', 'Sick leave - flu symptoms', 'approved', '2024-01-26 12:30:00');

-- Update some requests with approval information
UPDATE shift_requests SET approved_by = 1, approved_at = '2024-01-27 14:00:00' WHERE id IN (3, 4, 8);

-- Create indexes for better performance
CREATE INDEX IF NOT EXISTS idx_shifts_employee_id ON shifts(employee_id);
CREATE INDEX IF NOT EXISTS idx_shifts_shift_type_id ON shifts(shift_type_id);
CREATE INDEX IF NOT EXISTS idx_shifts_date ON shifts(date);
CREATE INDEX IF NOT EXISTS idx_shifts_status ON shifts(status);
CREATE INDEX IF NOT EXISTS idx_shift_requests_employee_id ON shift_requests(employee_id);
CREATE INDEX IF NOT EXISTS idx_shift_requests_status ON shift_requests(status);
CREATE INDEX IF NOT EXISTS idx_shift_requests_date ON shift_requests(requested_date);
CREATE INDEX IF NOT EXISTS idx_shift_types_active ON shift_types(is_active);
