# Employee Archive System - Installation & Usage Guide

## Overview
The archive system allows you to retain employee information after termination instead of permanently deleting records. This is important for compliance, future reference, and historical data tracking.

## Installation Steps

### 1. Update Database
Run the SQL commands in `update_database.sql` to create the `archived_employees` table:

```sql
-- Open phpMyAdmin (http://localhost/phpmyadmin)
-- Select your database: laundry_shop_db
-- Go to SQL tab
-- Copy and paste the contents of update_database.sql
-- Click "Go" to execute
```

The script will create:
- `archived_employees` table with all employee information
- Fields for tracking termination details (reason, date, terminated by)
- Calculated fields (total days worked, final salary)

### 2. Verify Installation
After running the SQL:
1. Check if the `archived_employees` table exists in phpMyAdmin
2. Verify it has these columns:
   - id, original_user_id, name, email, contact_number, address
   - position, role, date_hired, date_terminated
   - termination_reason, terminated_by, total_days_worked, final_salary
   - archived_at, notes

## How It Works

### Staff Deletion Process (Owner)
1. Go to **Staff List** page
2. Click **Delete** button for any staff member
3. A modal will appear asking for:
   - Confirmation of termination
   - **Reason for termination** (required)
4. Click **Confirm Termination**

### What Happens Behind the Scenes
When you delete/terminate a staff member:

1. **Data Collection:**
   - Fetches all user information from `users` table
   - Calculates total days worked (from date_hired to today)
   - Calculates final salary from attendance records (total_hours × ₱85)

2. **Archival:**
   - Copies all data to `archived_employees` table
   - Adds termination date (current date)
   - Stores termination reason you provided
   - Records who terminated them (owner ID)

3. **Deletion:**
   - Removes user from `users` table
   - User can no longer log in
   - All historical data is preserved in archive

### Viewing Archived Staff
1. Go to **Staff List** page
2. Click **View Archived Staff** button (gray button at top)
3. You'll see:
   - **Statistics:** Total archived, current year terminations, average days worked
   - **Table:** All archived employees with details
   - **View Details button:** Click to see full information modal

## Data Retention

### Information Preserved
- Personal Information: Name, email, contact, address
- Employment Details: Position, role, hire date
- Termination Details: Date, reason, who terminated
- Financial Data: Total days worked, final salary
- Timestamps: When archived

### Benefits
✅ Compliance with labor laws (employee record retention)
✅ Future reference for rehiring decisions
✅ Historical data for analytics
✅ Proof of employment history
✅ Salary and work history tracking

## Usage Tips

### Best Practices
1. **Always provide clear termination reasons:**
   - "Resigned - Personal reasons"
   - "Terminated - Violation of company policy"
   - "End of contract - Seasonal worker"
   - "Laid off - Business restructuring"

2. **Review archived records periodically:**
   - Check for rehiring opportunities
   - Analyze turnover patterns
   - Verify compliance documentation

3. **Use detailed reasons for legal protection:**
   - Document policy violations
   - Note performance issues
   - Record voluntary resignation

### Common Scenarios

**Scenario 1: Employee Resignation**
- Termination Reason: "Voluntary resignation - Found better opportunity"
- System records: Date, final salary, days worked

**Scenario 2: Contract End**
- Termination Reason: "End of 6-month contract - Seasonal work completed"
- System records: Full employment history

**Scenario 3: Termination**
- Termination Reason: "Terminated - Repeated tardiness despite warnings"
- System records: Complete documentation for legal purposes

## Database Structure

### archived_employees table
```
id (Primary Key) - Auto-increment
original_user_id - The user's ID from users table
name - Employee full name
email - Employee email
contact_number - Contact information
address - Physical address
position - Job position (Trainee, Staff, etc.)
role - System role (Staff, Owner)
date_hired - Original hire date
date_terminated - Date of termination
termination_reason - Why employee was terminated
terminated_by - Owner ID who terminated
total_days_worked - Calculated from date_hired
final_salary - Calculated from attendance records
archived_at - Timestamp when archived
notes - Additional notes (optional)
```

## Troubleshooting

### Issue: "Table 'archived_employees' doesn't exist"
**Solution:** Run the SQL commands in `update_database.sql`

### Issue: Modal doesn't appear when clicking Delete
**Solution:** Clear browser cache or hard refresh (Ctrl+F5)

### Issue: Termination reason not being saved
**Solution:** Make sure the textarea is filled before clicking Confirm

### Issue: Final salary shows 0.00
**Solution:** This is normal if employee has no attendance records yet

## Security Notes

- Only **Owner** role can access archived staff
- Only **Owner** can delete/terminate staff
- Archive action is logged with owner ID
- Terminated staff cannot log in anymore
- Original data is preserved for auditing

## Future Enhancements (Optional)

Consider adding:
- Search/filter in archived staff page
- Export archived records to Excel
- Restore archived employee functionality
- Notes field for additional comments
- Attachment uploads (termination letters, etc.)

## Support

If you encounter issues:
1. Check phpMyAdmin for table existence
2. Verify browser console for JavaScript errors
3. Check PHP error logs in XAMPP
4. Ensure `config.php` database connection is working

---

**Last Updated:** January 2025
**System Version:** v1.0
**Database:** laundry_shop_db
