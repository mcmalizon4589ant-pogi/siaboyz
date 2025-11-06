
UPDATE users 
SET contact_number = '09171234567',
    address = 'Manila, Philippines',
    position = 'Business Owner',
    date_hired = '2024-01-01'
WHERE id = 1 AND role = 'Owner';


UPDATE users 
SET contact_number = '09281234567',
    address = 'Quezon City, Philippines',
    position = 'Laundry Attendant',
    date_hired = '2024-03-15'
WHERE role = 'Staff' LIMIT 1;


SELECT id, name, email, role, position, contact_number, date_hired FROM users;
