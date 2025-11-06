# IMPORTANT: Database Update Instructions

## ⚠️ KAILANGAN MO GAWIN TO BAGO GUMANA ANG SYSTEM!

### Step 1: Open phpMyAdmin
1. Go to: http://localhost/phpmyadmin
2. Login (usually walang password sa XAMPP)
3. Click your database: **laundry_shop_db**

### Step 2: Run SQL Update
1. Click **SQL** tab sa taas
2. Copy at paste ang buong code below:

```sql
-- Add new columns to users table
ALTER TABLE users 
ADD COLUMN IF NOT EXISTS contact_number VARCHAR(20) DEFAULT NULL,
ADD COLUMN IF NOT EXISTS address TEXT DEFAULT NULL,
ADD COLUMN IF NOT EXISTS position VARCHAR(100) DEFAULT NULL,
ADD COLUMN IF NOT EXISTS date_hired DATE DEFAULT NULL;

-- Update existing Staff records
UPDATE users SET position = 'Staff' 
WHERE role = 'Staff' AND (position IS NULL OR position = '');

-- Update Owner records
UPDATE users SET position = 'Business Owner' 
WHERE role = 'Owner' AND (position IS NULL OR position = '');

-- Verify
SELECT id, name, email, role, position, contact_number, date_hired FROM users;
```

3. Click **Go** button
4. Dapat makita mo message: "Query OK, X rows affected"

### Step 3: Verify ang Changes
Dapat makita mo sa result yung:
- contact_number column
- address column
- position column
- date_hired column

---

## 📋 Position/Staff Types Available

Owner pwede pumili ng:
1. **Trainee** - Bagong hire, training pa
2. **Staff** - Regular employee
3. **Laundry Attendant** - Specific role
4. **Supervisor** - May supervisory duties
5. **Admin** - Administrative staff
6. **Manager** - Management level
7. **Custom** - Pwede mag-type ng sariling position

---

## 🎯 How to Use

### For OWNER:

#### Setting Staff Position:
1. Go to **Staff List**
2. Click **Edit** sa staff na gusto mo i-update
3. Fill in:
   - Position/Staff Type (dropdown)
   - Date Hired (kung hired na)
   - Contact Number
   - Address
4. Click **Save Changes**

#### Position Rules:
- **Trainee** = bagong hire, wala pang date hired or nag-training pa
- **Staff** = confirmed employee with date hired
- **Date Hired** = pwede blanko kung Pending or Trainee pa lang

### For STAFF:
- Can view own profile sa **Settings**
- Can update contact info and address
- **Cannot** change own position (only owner can)
- Can see date hired

---

## 🔍 What Shows Where

### Staff List Table:
- Shows **Position** with colored badge:
  - Trainee = yellow badge
  - Supervisor/Admin/Manager = blue badge
  - Others = gray badge
- Shows **Date Hired** or "Not hired yet"
- Shows **Contact Number** or "N/A"

### Edit Staff Page:
- Dropdown for position selection
- Date hired field (optional)
- All contact information

### Settings Page:
- View date hired (read-only for staff)
- Edit personal info
- Change password

---

## 💡 Best Practices

1. **Trainee/Pending Flow:**
   - New signup → Role: "Pending", Position: NULL, Date Hired: NULL
   - Owner approves → Role: "Staff", Position: "Trainee", Date Hired: NULL
   - After training → Keep Role: "Staff", Position: "Staff", Date Hired: SET DATE

2. **Regular Staff:**
   - Role: "Staff"
   - Position: "Staff" or specific role
   - Date Hired: Should be set

3. **Promotions:**
   - Change Position to "Supervisor", "Admin", or "Manager"
   - Keep Date Hired as original hire date

4. **Owner:**
   - Position: "Business Owner" (auto-set)
   - Date Hired: optional

---

## ⚠️ Troubleshooting

### Error: "Undefined array key 'date_hired'"
**Solution:** I-run mo yung SQL update sa Step 2!

### Error: "Unknown column 'position'"
**Solution:** I-run mo ulit yung SQL update, baka di nag-execute properly

### Position not showing sa dropdown
**Solution:** Refresh page after saving

### Date hired shows error
**Solution:** Check if date_hired column exists:
```sql
DESCRIBE users;
```

---

## 📝 Summary of Changes

### Database Changes:
- ✅ Added `contact_number` column
- ✅ Added `address` column  
- ✅ Added `position` column (Trainee, Staff, etc.)
- ✅ Added `date_hired` column (NULL = not yet hired)

### New Features:
- ✅ Position dropdown with preset options
- ✅ Date hired tracking
- ✅ Contact information management
- ✅ Position badges sa staff list
- ✅ Better data validation

### Files Updated:
- settings.php - Fixed date_hired error
- edit_staff.php - Added position dropdown, date_hired field
- staff_list.php - Added position badges, better null handling
- update_database.sql - Complete SQL update script

---

**IMPORTANT: I-run mo muna yung SQL update bago mo i-test ang system!**
