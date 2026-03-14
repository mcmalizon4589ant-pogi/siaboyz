
-- ============================================================
-- W.I.Y LAUNDRY SHOP - DATABASE SCHEMA & DATA UPDATE
-- ============================================================
-- This file contains both schema migrations and sample data
-- Run this complete script in phpMyAdmin to update your database

-- Step 1: Check if columns already exist, if not, add them
ALTER TABLE users 
ADD COLUMN IF NOT EXISTS contact_number VARCHAR(20) DEFAULT NULL,
ADD COLUMN IF NOT EXISTS address TEXT DEFAULT NULL,
ADD COLUMN IF NOT EXISTS position VARCHAR(100) DEFAULT NULL,
ADD COLUMN IF NOT EXISTS date_hired DATE DEFAULT NULL;

-- Step 2: Update existing Staff records with default position
UPDATE users SET position = 'Staff' WHERE role = 'Staff' AND (position IS NULL OR position = '');

-- Step 3: Update Owner records with default position
UPDATE users SET position = 'Business Owner' WHERE role = 'Owner' AND (position IS NULL OR position = '');

-- Step 4 (Optional): Set hire date for existing users (comment out if not needed)
-- UPDATE users SET date_hired = CURDATE() WHERE date_hired IS NULL AND role != 'Pending';

-- Step 5: Create archived_employees table for deleted staff records
CREATE TABLE IF NOT EXISTS archived_employees (
    id INT AUTO_INCREMENT PRIMARY KEY,
    original_user_id INT NOT NULL,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL,
    contact_number VARCHAR(20) DEFAULT NULL,
    address TEXT DEFAULT NULL,
    position VARCHAR(100) DEFAULT NULL,
    role VARCHAR(50) NOT NULL,
    date_hired DATE DEFAULT NULL,
    date_terminated DATE DEFAULT NULL,
    termination_reason TEXT DEFAULT NULL,
    terminated_by INT DEFAULT NULL,
    total_days_worked INT DEFAULT 0,
    final_salary DECIMAL(10,2) DEFAULT 0.00,
    archived_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    notes TEXT DEFAULT NULL,
    INDEX idx_original_user_id (original_user_id),
    INDEX idx_archived_date (archived_at)
);

-- ============================================================
-- SAMPLE DATA UPDATES (from sample_data_update.sql)
-- ============================================================

-- Update Owner record with sample data
UPDATE users 
SET contact_number = '09171234567',
    address = 'Manila, Philippines',
    position = 'Business Owner',
    date_hired = '2024-01-01'
WHERE id = 1 AND role = 'Owner';

-- Update first Staff record with sample data
UPDATE users 
SET contact_number = '09281234567',
    address = 'Quezon City, Philippines',
    position = 'Laundry Attendant',
    date_hired = '2024-03-15'
WHERE role = 'Staff' LIMIT 1;

-- ============================================================
-- BIOMETRIC INTEGRATION TABLES
-- ============================================================

-- Add biometric columns to users table
ALTER TABLE users 
ADD COLUMN IF NOT EXISTS biometric_enabled BOOLEAN DEFAULT FALSE,
ADD COLUMN IF NOT EXISTS last_biometric_checkin DATETIME DEFAULT NULL,
ADD COLUMN IF NOT EXISTS biometric_status VARCHAR(50) DEFAULT 'not_enrolled';

-- Create fingerprints table for storing enrolled fingerprints
CREATE TABLE IF NOT EXISTS fingerprints (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL UNIQUE,
    fingerprint_id INT NOT NULL,
    fingerprint_data LONGBLOB,
    enrollment_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    is_enrolled BOOLEAN DEFAULT FALSE,
    enrollment_status VARCHAR(50) DEFAULT 'pending',
    notes TEXT,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user_id (user_id),
    INDEX idx_fingerprint_id (fingerprint_id)
);

-- Create biometric history table for audit trail
CREATE TABLE IF NOT EXISTS biometric_history (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    fingerprint_id INT NOT NULL,
    action VARCHAR(20) NOT NULL,
    timestamp DATETIME DEFAULT CURRENT_TIMESTAMP,
    status VARCHAR(50) NOT NULL,
    ip_address VARCHAR(45) DEFAULT NULL,
    device_info VARCHAR(100) DEFAULT NULL,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user_id (user_id),
    INDEX idx_timestamp (timestamp)
);

-- ============================================================
-- VERIFICATION QUERY
-- ============================================================
-- Run this to check if all columns were added successfully
SELECT id, name, email, role, position, contact_number, date_hired FROM users;

-- Check biometric tables
SHOW TABLES LIKE 'fingerprint%';
