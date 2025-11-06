-- SQL commands to update the users table with additional fields
-- Run this in phpMyAdmin or MySQL console

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

-- Verification query - run this to check if columns were added successfully
SELECT id, name, email, role, position, contact_number, date_hired FROM users;
