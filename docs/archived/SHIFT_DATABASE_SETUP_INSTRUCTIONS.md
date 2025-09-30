# Shift Database Setup Instructions

## Manual Database Setup for hr3systemdb

Since the automated scripts are having issues, please follow these manual steps to set up the shift system database:

### Step 1: Open MySQL Command Line or phpMyAdmin

**Option A: MySQL Command Line**
```bash
mysql -u root -p
```

**Option B: Use phpMyAdmin** (if you have XAMPP)
- Open http://localhost/phpmyadmin
- Select the `hr3systemdb` database

### Step 2: Execute the Following SQL Commands

Copy and paste these SQL commands one by one:

#### 1. Create shift_types table
```sql
USE hr3systemdb;

CREATE TABLE IF NOT EXISTS shift_types (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    type ENUM('day', 'night', 'swing', 'weekend') NOT NULL DEFAULT 'day',
    default_start_time TIME NOT NULL,
    default_end_time TIME NOT NULL,
    break_duration INT NOT NULL DEFAULT 60,
    hourly_rate DECIMAL(8,2) NULL,
    description TEXT NULL,
    color_code VARCHAR(7) NOT NULL DEFAULT '#007bff',
    is_active BOOLEAN NOT NULL DEFAULT TRUE,
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_shift_types_active (is_active),
    INDEX idx_shift_types_type (type)
);
```

#### 2. Create shifts table
```sql
CREATE TABLE IF NOT EXISTS shifts (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    employee_id BIGINT UNSIGNED NOT NULL,
    shift_type_id BIGINT UNSIGNED NOT NULL,
    date DATE NOT NULL,
    start_time TIME NOT NULL,
    end_time TIME NOT NULL,
    break_duration INT NOT NULL DEFAULT 60,
    notes TEXT NULL,
    status ENUM('scheduled', 'in_progress', 'completed', 'cancelled') NOT NULL DEFAULT 'scheduled',
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (employee_id) REFERENCES employees(id) ON DELETE CASCADE,
    FOREIGN KEY (shift_type_id) REFERENCES shift_types(id) ON DELETE CASCADE,
    INDEX idx_shifts_employee (employee_id),
    INDEX idx_shifts_date (date),
    INDEX idx_shifts_status (status),
    UNIQUE KEY unique_employee_date (employee_id, date)
);
```

#### 3. Create shift_requests table
```sql
CREATE TABLE IF NOT EXISTS shift_requests (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    employee_id BIGINT UNSIGNED NOT NULL,
    request_type ENUM('shift_change', 'time_off', 'overtime', 'swap') NOT NULL DEFAULT 'shift_change',
    current_shift_id BIGINT UNSIGNED NULL,
    requested_date DATE NOT NULL,
    requested_start_time TIME NULL,
    requested_end_time TIME NULL,
    reason TEXT NOT NULL,
    status ENUM('pending', 'approved', 'rejected') NOT NULL DEFAULT 'pending',
    approved_by BIGINT UNSIGNED NULL,
    approved_at TIMESTAMP NULL,
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (employee_id) REFERENCES employees(id) ON DELETE CASCADE,
    FOREIGN KEY (current_shift_id) REFERENCES shifts(id) ON DELETE SET NULL,
    FOREIGN KEY (approved_by) REFERENCES employees(id) ON DELETE SET NULL,
    INDEX idx_shift_requests_employee (employee_id),
    INDEX idx_shift_requests_status (status),
    INDEX idx_shift_requests_date (requested_date)
);
```

#### 4. Insert default shift types
```sql
INSERT IGNORE INTO shift_types (name, type, default_start_time, default_end_time, break_duration, hourly_rate, description, color_code) VALUES
('Morning Shift', 'day', '08:00:00', '16:00:00', 60, 15.00, 'Standard morning shift', '#28a745'),
('Evening Shift', 'swing', '16:00:00', '00:00:00', 60, 17.00, 'Evening shift with swing hours', '#fd7e14'),
('Night Shift', 'night', '00:00:00', '08:00:00', 60, 20.00, 'Overnight shift', '#6f42c1'),
('Weekend Day', 'weekend', '09:00:00', '17:00:00', 60, 18.00, 'Weekend day shift', '#20c997'),
('Weekend Night', 'weekend', '22:00:00', '06:00:00', 60, 22.00, 'Weekend night shift', '#e83e8c');
```

#### 5. Insert sample shifts (optional)
```sql
INSERT IGNORE INTO shifts (employee_id, shift_type_id, date, start_time, end_time, status) VALUES
(1, 1, CURDATE(), '08:00:00', '16:00:00', 'scheduled'),
(2, 2, CURDATE(), '16:00:00', '00:00:00', 'scheduled'),
(3, 1, DATE_ADD(CURDATE(), INTERVAL 1 DAY), '08:00:00', '16:00:00', 'scheduled'),
(1, 3, DATE_ADD(CURDATE(), INTERVAL 2 DAY), '00:00:00', '08:00:00', 'scheduled');
```

#### 6. Insert sample shift requests (optional)
```sql
INSERT IGNORE INTO shift_requests (employee_id, request_type, requested_date, reason, status) VALUES
(1, 'time_off', DATE_ADD(CURDATE(), INTERVAL 7 DAY), 'Personal appointment', 'pending'),
(2, 'shift_change', DATE_ADD(CURDATE(), INTERVAL 3 DAY), 'Family commitment', 'pending'),
(3, 'overtime', CURDATE(), 'Extra project work needed', 'approved');
```

### Step 3: Verify Tables Were Created

Run this query to verify all tables exist:
```sql
SHOW TABLES LIKE 'shift%';
```

You should see:
- shift_requests
- shift_types  
- shifts

### Step 4: Check Data

Verify the data was inserted:
```sql
SELECT COUNT(*) as shift_types_count FROM shift_types;
SELECT COUNT(*) as shifts_count FROM shifts;
SELECT COUNT(*) as shift_requests_count FROM shift_requests;
```

### Step 5: Test the Connection

Once completed, the shift management system should work properly with:
- ✅ Shift types available for assignment
- ✅ Employee shift scheduling
- ✅ Shift request management
- ✅ Calendar view functionality

## Troubleshooting

If you get foreign key errors:
1. Make sure the `employees` table exists first
2. Ensure there are employees with IDs 1, 2, 3 for the sample data
3. You can skip the sample data inserts (steps 5-6) if needed

## Next Steps

After completing this setup:
1. The shift schedule management page should load without errors
2. You can create new shifts and assign employees
3. The calendar view will display scheduled shifts
4. Shift requests can be submitted and managed

Let me know once you've completed these steps and I'll help test the functionality!
