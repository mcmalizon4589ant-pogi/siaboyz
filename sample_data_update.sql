-- Optional: Sample data update for existing users
-- Run this AFTER running update_database.sql

-- Update sample positions and dates for existing users (adjust IDs as needed)
-- This is just an example, modify based on your actual user IDs

-- Example: Update user with ID 1 (Owner)
UPDATE users 
SET contact_number = '09171234567',
    address = 'Manila, Philippines',
    position = 'Business Owner',
    date_hired = '2024-01-01'
WHERE id = 1 AND role = 'Owner';

-- Example: Update staff members with sample data
-- Modify these IDs based on your actual staff member IDs
UPDATE users 
SET contact_number = '09281234567',
    address = 'Quezon City, Philippines',
    position = 'Laundry Attendant',
    date_hired = '2024-03-15'
WHERE role = 'Staff' LIMIT 1;

-- You can run this to see all users and their IDs
SELECT id, name, email, role, position, contact_number, date_hired FROM users;
