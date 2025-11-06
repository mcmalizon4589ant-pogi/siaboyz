
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

-- Verification query - run this to check if columns were added successfully
SELECT id, name, email, role, position, contact_number, date_hired FROM users;
