# 🚀 W.I.Y Laundry Shop - New Features Setup Guide

## ✅ What's New?

### 1. **Enhanced Payroll System (payroll_v2.php)**
   - 15th and 30th cutoff scheduling
   - Individual staff payslip view
   - All staff summary view (Owner only)
   - Print payslip functionality
   - Better UI with color-coded sections

### 2. **Settings Page (settings.php)**
   - Edit personal profile information
   - Update contact details and address
   - Change password
   - View account information
   - Available for both Owner and Staff

### 3. **Staff Management (edit_staff.php)**
   - Owner can edit any staff member's information
   - Update staff role, position, contact details
   - Reset staff password
   - Delete staff account
   - Professional edit interface

### 4. **Improved Staff List (staff_list.php)**
   - Better table design with more information
   - Shows ID, Position, Contact, Date Hired
   - Role badges with colors
   - Edit and Delete buttons
   - More professional layout

---

## 📦 Installation Steps

### Step 1: Update Database
1. Open **phpMyAdmin** (http://localhost/phpmyadmin)
2. Select your database `laundry_shop_db`
3. Go to the **SQL** tab
4. Copy and paste the contents of `update_database.sql`
5. Click **Go** to execute

**Or run this SQL manually:**
```sql
ALTER TABLE users 
ADD COLUMN contact_number VARCHAR(20) DEFAULT NULL,
ADD COLUMN address TEXT DEFAULT NULL,
ADD COLUMN position VARCHAR(100) DEFAULT 'Staff',
ADD COLUMN date_hired DATE DEFAULT NULL;

UPDATE users SET date_hired = CURDATE() WHERE date_hired IS NULL;
```

### Step 2: Test the New Features
1. Login as Owner
2. Check the new **Settings** link in sidebar
3. Click **Staff List** → Click **Edit** button on any staff
4. Click **Payroll** to see the new cutoff-based system
5. Try editing your own profile in **Settings**

---

## 🎯 New Features Overview

### For OWNER:
- **Dashboard** - Overview of system
- **Staff List** - View all staff with Edit/Delete options
- **Attendance** - Monitor time in/out
- **Payroll** - Cutoff-based payroll (15th & 30th)
  - Select staff member or view all
  - Switch between current/previous cutoff
  - Print payslips
- **Settings** - Edit your own profile

### For STAFF:
- **Dashboard** - Personal overview
- **Attendance** - Record time in/out
- **Payroll** - View own payslip by cutoff period
- **Settings** - Edit profile and change password

---

## 📋 Navigation Structure

### Owner Navigation:
```
Dashboard
├── Staff List (with Edit button for each staff)
├── Attendance
├── Payroll (cutoff-based: 1-15, 16-end)
└── Settings (profile management)
```

### Staff Navigation:
```
Dashboard
├── Attendance
├── Payroll (view own payslip)
└── Settings (profile management)
```

---

## 🆕 Database Schema Updates

**New columns added to `users` table:**
- `contact_number` - VARCHAR(20) - Phone number
- `address` - TEXT - Complete address
- `position` - VARCHAR(100) - Job position (default: 'Staff')
- `date_hired` - DATE - Date employee was hired

---

## 🔐 Security Features

1. **Role-based Access Control**
   - Owner can edit all staff
   - Staff can only edit their own profile
   - Staff cannot change their own position/role

2. **Password Security**
   - Current password required to change password
   - Password confirmation required
   - Passwords are hashed with bcrypt

3. **Email Validation**
   - Duplicate email detection
   - Email format validation

---

## 🎨 UI/UX Improvements

1. **Modern Color Scheme**
   - Blue gradient for payroll summary
   - Color-coded role badges
   - Hover effects on tables and buttons

2. **Responsive Design**
   - Works on different screen sizes
   - Mobile-friendly forms
   - Print-friendly payslips

3. **Better User Feedback**
   - Success/error messages
   - Confirmation dialogs for dangerous actions
   - Loading states and transitions

---

## 📝 Usage Examples

### How to View Payroll (Owner):
1. Go to **Payroll** page
2. Select cutoff period (Current/Previous)
3. Choose a staff member from dropdown
4. View detailed payslip
5. Click **Print Payslip** to print

### How to Edit Staff Information (Owner):
1. Go to **Staff List**
2. Click **Edit** button next to staff name
3. Update information (name, email, contact, position, role, etc.)
4. Click **Save Changes**
5. Optional: Reset password or delete account

### How to Update Personal Profile (All Users):
1. Go to **Settings** page
2. Update your information
3. Click **Save Profile Changes**
4. To change password, fill in password fields at bottom

---

## 🐛 Troubleshooting

**Problem:** "No such table: contact_number"
- **Solution:** Run the SQL update script in phpMyAdmin

**Problem:** Settings page not showing
- **Solution:** Clear browser cache and refresh

**Problem:** Edit button not working
- **Solution:** Make sure you're logged in as Owner

**Problem:** Payroll shows wrong cutoff
- **Solution:** Check your system date/time

---

## 🚀 Next Recommended Features

1. **Leave Management System**
2. **Announcement Board**
3. **Reports Generation (PDF/Excel)**
4. **Email Notifications**
5. **Attendance Analytics Dashboard**

---

## 📞 Support

If you encounter any issues:
1. Check browser console for errors
2. Check PHP error logs
3. Verify database structure
4. Make sure all files are uploaded

---

**Enjoy your upgraded W.I.Y Laundry Shop Management System! 🎉**
