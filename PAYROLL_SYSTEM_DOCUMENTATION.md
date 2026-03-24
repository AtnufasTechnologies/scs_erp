# Faculty Payroll System - Documentation

## Overview

Complete automated payroll management system for faculty members with EMI/loan deduction support and automated monthly generation.

## Features Implemented

### 1. **Admin Payroll Management** (`/erp/admin/accounts/payroll`)

- ✅ Create, View, Edit, Delete salary slips
- ✅ Approve salary slips
- ✅ Mark as paid with payment tracking
- ✅ Bulk generation for all faculty members
- ✅ Filter by faculty, year, month, status
- ✅ Dashboard with statistics

### 2. **Faculty Loans Management** (`/erp/admin/accounts/payroll/loans`)

- ✅ Create and track faculty loans
- ✅ Automatic EMI deduction from monthly salary
- ✅ Track loan progress and installments
- ✅ Manage loan status (active/completed/suspended)
- ✅ Dashboard with recovery statistics

### 3. **Faculty View** (`/faculty/payroll`)

- ✅ View all salary slips
- ✅ Filter by year, month, status
- ✅ Download individual slip as PDF
- ✅ Download yearly compilation PDF
- ✅ View detailed earnings & deductions breakdown

### 4. **Automated Monthly Generation**

- ✅ Console command for automated generation
- ✅ Copies previous month's salary structure
- ✅ Automatic EMI deduction from active loans
- ✅ Progress tracking with EMI installments

## Database Structure

### Tables Created:

1. **faculty_salary_slips** - Stores all salary slip records
2. **faculty_loans** - Tracks faculty loans and EMI payments

## How to Use

### Admin Operations:

#### 1. Create Salary Slip (Manual)

```
Navigate to: Accounts Office > Faculty Payroll > Create
- Select faculty member
- Enter month/year
- Fill earnings (Basic, DA, HRA, TA, allowances)
- Fill deductions (PF, ESI, Tax, TDS)
- Active loan EMI is added automatically
- System calculates gross, deductions, and net salary
```

#### 2. Bulk Generate Salary Slips

```
Navigate to: Accounts Office > Faculty Payroll > Bulk Generate button
- Select month and year
- Set working days
- Optional: Add salary increase percentage
- System generates slips for all active faculty
- Uses previous month's salary structure as template
```

#### 3. Approve and Mark as Paid

```
- View salary slip details
- Click "Approve" to approve draft slips
- Click "Mark as Paid" to record payment
- Enter payment date, mode, and reference
```

#### 4. Manage Faculty Loans

```
Navigate to: Accounts Office > Faculty Loans
- Click "Create Loan"
- Select faculty, loan type, amount
- Enter EMI amount and total installments
- Loan EMI is automatically deducted each month when salary slip is generated
- System tracks progress and marks as completed when fully paid
```

### Automated Monthly Generation:

#### Manual Command Execution:

```bash
# Generate for current month
php artisan payroll:generate-monthly

# Generate for specific month/year
php artisan payroll:generate-monthly --month=03 --year=2026

# Force regeneration (overwrites existing slips)
php artisan payroll:generate-monthly --force
```

#### Setup Automated Scheduling:

**Step 1: Add to Task Scheduler**

Edit `app/Console/Kernel.php` and add:

```php
protected function schedule(Schedule $schedule)
{
    // Generate salary slips on 1st of every month at 6 AM
    $schedule->command('payroll:generate-monthly')
        ->monthlyOn(1, '06:00')
        ->timezone('Asia/Kolkata');
}
```

**Step 2: Setup Cron Job**

On your server, run:

```bash
crontab -e
```

Add this line:

```
* * * * * cd /path/to/your/project && php artisan schedule:run >> /dev/null 2>&1
```

Replace `/path/to/your/project` with your actual project path.

**Alternative Schedules:**

For testing, you can use different schedules:

```php
// Every day at 9 AM
$schedule->command('payroll:generate-monthly')->dailyAt('09:00');

// Every Monday at 8 AM
$schedule->command('payroll:generate-monthly')->weeklyOn(1, '08:00');

// Manually trigger via URL (for testing)
Route::get('/admin/trigger-payroll', function() {
    Artisan::call('payroll:generate-monthly');
    return 'Payroll generation triggered';
});
```

## Salary Calculation Logic

### Earnings:

- Basic Salary (Base amount)
- DA (Dearness Allowance)
- HRA (House Rent Allowance)
- TA (Transport Allowance)
- Medical Allowance
- Special Allowance
- Other Allowances

**Gross Salary = Sum of all earnings**

### Deductions:

- PF (Provident Fund)
- ESI (Employee State Insurance)
- Professional Tax
- TDS (Tax Deducted at Source)
- **Loan/EMI Deduction** (Automatic from active loans)
- Other Deductions

**Total Deductions = Sum of all deductions**

### Net Salary:

**Net Salary = Gross Salary - Total Deductions**

## EMI Deduction Flow:

1. When salary slip is created/generated:
    - System checks for active loans for that faculty
    - If found, adds EMI amount to `loan_deduction` field
2. After saving salary slip:
    - System calls `loan->deductEMI()` method
    - Increments `paid_installments`
    - Updates `total_paid` amount
    - Reduces `remaining_amount`
    - Marks loan as "completed" when all installments paid

## Default Salary Structure (Bulk Generation):

When no previous salary exists for a faculty:

```
Basic Salary: ₹30,000 (or custom default)
DA: 10% of basic (₹3,000)
HRA: 20% of basic (₹6,000)
TA: ₹1,000 (fixed)
PF: 12% of basic (₹3,600)
```

## Access Control:

Sidebar menu items require permission:

- Menu slug: `faculty-pay-roll`
- Grant this permission to accounts office users via User Access Management

## API/Routes Structure:

### Admin Routes:

- `GET  /erp/admin/accounts/payroll` - List all salary slips
- `GET  /erp/admin/accounts/payroll/create` - Create form
- `POST /erp/admin/accounts/payroll` - Store new slip
- `GET  /erp/admin/accounts/payroll/{id}` - View details
- `PUT  /erp/admin/accounts/payroll/{id}` - Update slip
- `DELETE /erp/admin/accounts/payroll/{id}` - Delete slip
- `POST /erp/admin/accounts/payroll/bulk-generate` - Bulk generate
- `POST /erp/admin/accounts/payroll/{id}/approve` - Approve slip
- `POST /erp/admin/accounts/payroll/{id}/mark-paid` - Mark as paid
- `GET  /erp/admin/accounts/payroll/loans` - Loan management
- `POST /erp/admin/accounts/payroll/loans` - Create loan

### Faculty Routes:

- `GET /faculty/payroll` - View my salary slips
- `GET /faculty/payroll/{id}` - View slip details
- `GET /faculty/payroll/{id}/download` - Download PDF
- `GET /faculty/payroll/bulk/download` - Download yearly PDF

## Troubleshooting:

### Issue: Salary slips not generating automatically

**Solution:**

- Check if cron job is running: `tail -f /var/log/cron`
- Verify Laravel scheduler: `php artisan schedule:list`
- Check logs: `tail -f storage/logs/laravel.log`

### Issue: EMI not deducting

**Solution:**

- Ensure loan status is "active"
- Check if loan has remaining installments
- Verify `faculty_id` matches between loan and salary slip

### Issue: PDF download not working

**Solution:**

- Check if DomPDF is installed: `composer show | grep dompdf`
- Verify `numberToWords()` helper exists in `app/Helpers/Qs.php`

## Future Enhancements:

- [ ] Bonus/incentive management
- [ ] Salary revision history
- [ ] Tax calculation automation (IT computation)
- [ ] Payslip email notification
- [ ] Attendance integration for leave deductions
- [ ] Bank file generation for bulk transfer
- [ ] Payroll analytics and reports

## Support:

For issues or questions, contact the development team.

---

**Version:** 1.0  
**Last Updated:** March 21, 2026  
**Developed for:** SCS ERP System
