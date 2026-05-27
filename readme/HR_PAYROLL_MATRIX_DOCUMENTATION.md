# HR Payroll & Pay Matrix System - Documentation

## Overview

Complete payroll management system with pay matrix master for standardized salary structures. This system integrates with the existing HR module to provide comprehensive salary management for faculty members.

## New Features Implemented

### 1. **Pay Matrix Master** (`/erp/hr/pay-matrix`)

The Pay Matrix system provides a standardized salary structure framework that can be assigned to multiple faculty members.

#### Key Features:

- ✅ Create, View, Edit, Delete pay matrices
- ✅ Define salary structure by designation and grade level
- ✅ Support for multiple employment types (Permanent, Contractual, Ad-hoc, Guest, Visiting)
- ✅ Flexible allowance and deduction configuration
- ✅ Percentage-based or fixed amount calculations
- ✅ Annual increment configuration
- ✅ Effective date management
- ✅ Duplicate pay matrix for easy creation
- ✅ Archive inactive matrices
- ✅ Track faculty count using each matrix

#### Pay Matrix Components:

**Identification:**

- Matrix Code (Auto-generated: PM2026XXXX)
- Matrix Name
- Designation (Professor, Associate Professor, etc.)
- Grade Level (Level-1, Level-2, etc.)
- Pay Band & Grade Pay
- Employment Type

**Earnings:**

- Basic Salary (Fixed)
- DA - Dearness Allowance (% or Fixed)
- HRA - House Rent Allowance (% or Fixed)
- TA - Transport Allowance (Fixed)
- Medical Allowance (Fixed)
- Special Allowance (Fixed)
- Other Allowances (Fixed)

**Deductions:**

- PF - Provident Fund (% or Fixed)
- ESI - Employee State Insurance (% or Fixed)
- Professional Tax (Fixed)
- TDS - Tax Deducted at Source (%)
- Other Deductions (Fixed)

**Additional Settings:**

- Annual Increment Percentage
- Increment Month (1-12)
- Default Working Days
- Effective From/To Dates
- Status (Active/Inactive/Archived)

### 2. **Payroll Generation** (`/erp/hr/payroll`)

Comprehensive payroll management with pay matrix integration.

#### Key Features:

- ✅ Assign pay matrix to faculty members
- ✅ Bulk generate salary slips for all faculty
- ✅ Selective generation for specific faculty
- ✅ Automatic salary calculation from pay matrix
- ✅ Pro-rata calculation based on working days
- ✅ Automatic loan EMI deduction
- ✅ Approve salary slips
- ✅ Mark as paid with payment tracking
- ✅ Monthly payroll dashboard
- ✅ Payroll statistics and analytics

#### Payroll Workflow:

**Step 1: Assign Pay Matrix to Faculty**

```
1. Navigate to Generate Payroll page
2. Faculty without salary master are listed
3. Select appropriate pay matrix for each faculty
4. Set effective from date
5. System creates salary master with matrix components
```

**Step 2: Generate Salary Slips**

```
1. Select month and year
2. Set working days for the month
3. Option to select specific faculty or generate for all
4. System creates salary slips with:
   - Earnings from pay matrix (pro-rated if needed)
   - Deductions from pay matrix
   - Automatic loan EMI deduction
   - Calculated gross, total deductions, and net salary
5. Slips created in 'draft' status
```

**Step 3: Approve Salary Slips**

```
1. Review salary slip details
2. Approve to move from 'draft' to 'approved' status
3. Only approved slips can be marked as paid
```

**Step 4: Mark as Paid**

```
1. Select approved salary slip
2. Enter payment date, mode, and reference
3. System marks slip as 'paid'
4. Updates loan progress if applicable
```

## Database Structure

**Note**: All HR module migrations are organized in `database/migrations/hr/` folder.

### New Tables:

#### 1. `hr_pay_matrix`

Stores pay matrix master records with complete salary structure.

**Key Fields:**

- `matrix_code` - Unique identifier (PM2026XXXX)
- `matrix_name` - Display name
- `designation` - Job position
- `grade_level` - Salary grade
- `pay_band`, `grade_pay` - Government pay scale reference
- `employment_type` - Permanent/Contractual/etc.
- Earnings fields (basic*salary, da*%, hra\_%, ta, etc.)
- Deduction fields (pf*%, esi*%, professional_tax, etc.)
- `annual_increment_percentage` - Yearly increment
- `increment_month` - Month for annual increment
- `status` - Active/Inactive/Archived
- `effective_from`, `effective_to` - Date range
- Audit fields (created_by, updated_by)

**Indexes:**

- designation, grade_level, status, employment_type
- effective_from, effective_to

#### 2. `faculty_salary_masters` (Updated)

Added new field:

- `pay_matrix_id` - Links to hr_pay_matrix table

### Existing Tables Used:

- `faculty_salary_slips` - Monthly salary slip records
- `faculty_loans` - Faculty loan tracking
- `faculties` - Faculty master data

## Models

### New Models:

#### 1. `App\Models\HrPayMatrix`

**Relationships:**

- `facultySalaries()` - hasMany FacultySalaryMaster
- `creator()` - belongsTo User (created_by)
- `updater()` - belongsTo User (updated_by)

**Scopes:**

- `active()` - Active matrices only
- `inactive()` - Inactive matrices only
- `byEmploymentType($type)` - Filter by employment type
- `byDesignation($designation)` - Filter by designation
- `effectiveOn($date)` - Effective on specific date

**Helper Methods:**

- `calculateDA()` - Calculate DA (percentage or fixed)
- `calculateHRA()` - Calculate HRA (percentage or fixed)
- `calculatePF()` - Calculate PF (percentage or fixed)
- `calculateESI()` - Calculate ESI (percentage or fixed)
- `calculateTDS()` - Calculate TDS (percentage)
- `calculateGrossSalary()` - Total earnings
- `calculateTotalDeductions()` - Total deductions
- `calculateNetSalary()` - Gross - Deductions
- `getSalaryComponents()` - Complete breakdown array
- `isEffective($date)` - Check if effective on date
- `generateMatrixCode()` - Auto-generate unique code

**Attributes:**

- `faculty_count` - Count of faculty using this matrix
- `full_designation` - Designation + Grade Level
- `status_color` - Badge color for status

#### 2. `App\Models\FacultySalaryMaster` (Updated)

**New Relationship:**

- `payMatrix()` - belongsTo HrPayMatrix

## Controllers

### New Controllers:

#### 1. `App\Http\Controllers\HrPayMatrixController`

**Methods:**

- `index()` - List all pay matrices with search/filters
- `create()` - Show create form
- `store()` - Save new pay matrix
- `show($id)` - View pay matrix details with calculations
- `edit($id)` - Show edit form
- `update($id)` - Update pay matrix
- `destroy($id)` - Delete pay matrix (checks for usage)
- `archive($id)` - Archive pay matrix
- `duplicate($id)` - Create copy of pay matrix
- `applyToFaculty(Request, $id)` - Assign matrix to multiple faculty
- `preview($id)` - JSON response with calculations

#### 2. `App\Http\Controllers\HrPayrollController`

**Methods:**

- `index()` - Payroll dashboard with statistics
- `generateForm()` - Show generation form with faculty lists
- `assignPayMatrix(Request)` - Assign pay matrix to faculty
- `bulkGenerate(Request)` - Generate salary slips for month
- `show($id)` - View salary slip details
- `approve($id)` - Approve salary slip
- `markPaid(Request, $id)` - Mark as paid with payment details
- `destroy($id)` - Delete salary slip (draft only)
- `statistics()` - Payroll statistics and analytics

## Routes

### Pay Matrix Routes:

```php
GET    /erp/hr/pay-matrix                      - List pay matrices
GET    /erp/hr/pay-matrix/create               - Create form
POST   /erp/hr/pay-matrix                      - Store new matrix
GET    /erp/hr/pay-matrix/{id}                 - View matrix
GET    /erp/hr/pay-matrix/{id}/edit            - Edit form
PUT    /erp/hr/pay-matrix/{id}                 - Update matrix
DELETE /erp/hr/pay-matrix/{id}                 - Delete matrix
POST   /erp/hr/pay-matrix/{id}/archive         - Archive matrix
POST   /erp/hr/pay-matrix/{id}/duplicate       - Duplicate matrix
POST   /erp/hr/pay-matrix/{id}/apply-to-faculty - Apply to faculty
GET    /erp/hr/pay-matrix/{id}/preview         - Preview calculations
```

### Payroll Routes:

```php
GET    /erp/hr/payroll                         - Payroll dashboard
GET    /erp/hr/payroll/generate                - Generation form
POST   /erp/hr/payroll/assign-pay-matrix       - Assign matrix to faculty
POST   /erp/hr/payroll/bulk-generate           - Bulk generate slips
GET    /erp/hr/payroll/{id}                    - View slip
POST   /erp/hr/payroll/{id}/approve            - Approve slip
POST   /erp/hr/payroll/{id}/mark-paid          - Mark as paid
DELETE /erp/hr/payroll/{id}                    - Delete slip
GET    /erp/hr/payroll/statistics/overview     - Statistics
```

## Views

### Pay Matrix Views:

- `resources/views/hr/pay-matrix/index.blade.php` - List view with filters
- Future: create.blade.php, edit.blade.php, show.blade.php

### Payroll Views:

- `resources/views/hr/payroll/index.blade.php` - Dashboard with statistics
- `resources/views/hr/payroll/generate.blade.php` - Generation form
- `resources/views/hr/payroll/show.blade.php` - Salary slip detail
- Future: statistics.blade.php

### Dashboard Updates:

- Added 4 payroll statistics cards
- Added 4 payroll quick action buttons
- Integration with existing HR dashboard

### Sidebar Updates:

- New "Payroll & Salary" section
- Links to Pay Matrix, Payroll, and Statistics

## Usage Guide

### For HR Admin:

#### Creating Pay Matrix:

1. Navigate to HR > Pay Matrix > Create Pay Matrix
2. Fill in basic details:
    - Matrix name
    - Designation (Professor, Associate Professor, etc.)
    - Grade Level (Level-1, Level-2, etc.)
    - Employment Type
3. Configure earnings:
    - Enter basic salary
    - Set DA as percentage of basic OR fixed amount
    - Set HRA as percentage of basic OR fixed amount
    - Enter other allowances
4. Configure deductions:
    - Set PF as percentage of basic OR fixed amount
    - Set ESI as percentage of gross OR fixed amount
    - Set TDS percentage
    - Enter other deductions
5. Set additional parameters:
    - Annual increment percentage
    - Increment month
    - Default working days
    - Effective from/to dates
6. Save the pay matrix

#### Assigning Pay Matrix to Faculty:

**Method 1: From Generate Payroll Page**

1. Navigate to HR > Payroll > Generate Payroll
2. Faculty without salary master are listed at top
3. Select pay matrix from dropdown for each faculty
4. Set effective from date
5. Click "Assign" button
6. System creates salary master with all components from pay matrix

**Method 2: From Pay Matrix Detail Page**

1. Navigate to specific pay matrix
2. Click "Apply to Faculty" button
3. Select multiple faculty members
4. Set effective from date
5. Submit to assign matrix to all selected faculty

#### Generating Monthly Payroll:

1. Navigate to HR > Payroll > Generate Payroll
2. Select month and year
3. Enter working days for the month
4. Choose option:
    - **Generate for All**: Creates slips for all faculty with active salary master
    - **Select Specific**: Choose individual faculty members
5. Click "Generate Salary Slips"
6. System creates slips with:
    - Pro-rated salary if working days differ from default
    - Automatic loan EMI deduction
    - All calculations from pay matrix
7. Review generated slips in Payroll dashboard

#### Approving and Processing Payroll:

1. Navigate to HR > Payroll
2. Filter by month/year
3. Review draft salary slips
4. Click on individual slip to view details
5. Verify all calculations
6. Click "Approve" to approve slip
7. Once approved, click "Mark as Paid"
8. Enter:
    - Payment date
    - Payment mode (Bank Transfer/Cash/Cheque)
    - Payment reference (Transaction ID/Cheque number)
9. Submit to mark as paid
10. System updates loan progress automatically

## Salary Calculation Logic

### Basic Calculation:

```
Gross Salary = Basic + DA + HRA + TA + Medical + Special + Other Allowances

Total Deductions = PF + ESI + Professional Tax + TDS + Loan EMI + Other Deductions

Net Salary = Gross Salary - Total Deductions
```

### Pro-rata Calculation:

When working days differ from default:

```
Ratio = Actual Working Days / Default Working Days

Pro-rated Basic = Basic Salary × Ratio
Pro-rated DA = DA × Ratio
Pro-rated HRA = HRA × Ratio
(and so on for all components)
```

### Percentage-based Calculations:

```
DA (if percentage) = Basic Salary × (DA Percentage / 100)
HRA (if percentage) = Basic Salary × (HRA Percentage / 100)
PF (if percentage) = Basic Salary × (PF Percentage / 100)
ESI (if percentage) = Gross Salary × (ESI Percentage / 100)
TDS = Gross Salary × (TDS Percentage / 100)
```

## Integration with Existing Systems

### Faculty Loans:

- Payroll generation automatically fetches active loans
- EMI amount is deducted from salary slip
- Loan progress is updated when slip is marked as paid
- Loan status changes to 'completed' when fully paid

### Annual Sessions:

- Salary slips linked to academic session
- Helps in year-wise reporting and analysis

### Faculty Master:

- Only active faculty (IS_LEFT = 0) are included
- Links to faculty details for slip generation

## Reports and Analytics

### Available Reports:

1. Monthly Payroll Summary
2. Pay Matrix Usage Report
3. Faculty-wise Salary History
4. Deduction Analysis
5. Loan Recovery Tracking

### Statistics Dashboard:

- Total faculty count
- Monthly slip generation count
- Approval and payment status
- Total monthly payout
- Active pay matrices
- Pay matrix-wise distribution

## Best Practices

### Pay Matrix Management:

1. Create separate matrices for different designations
2. Use grade levels for experience-based variations
3. Set effective dates when making changes
4. Archive old matrices instead of deleting
5. Duplicate matrices for similar structures
6. Review calculations before activating

### Payroll Processing:

1. Assign pay matrix before month-end
2. Update working days based on calendar
3. Generate slips at month-end
4. Review all slips before approval
5. Approve slips before payment processing
6. Mark as paid only after actual payment
7. Maintain payment references for audit

### Data Maintenance:

1. Regular backup of payroll data
2. Archive old salary slips annually
3. Update pay matrices for annual increments
4. Review and update deduction percentages
5. Verify loan EMI deductions monthly

## Security and Access Control

### Recommended Permissions:

- **HR Admin**: Full access to all payroll functions
- **Accounts Officer**: View and approve access
- **Faculty**: View own salary slips only (separate module)
- **Principal**: Approval authority for high-value slips

## Future Enhancements

### Planned Features:

1. **Form 16 Generation**: Annual tax certificate
2. **PF Statement**: Monthly PF contribution report
3. **Salary Revision**: Bulk update for annual increments
4. **Arrears Calculation**: For backdated salary revisions
5. **Bonus Calculation**: Festival bonus processing
6. **Leave Encashment**: Convert leave to salary
7. **Overtime Calculation**: Additional hours payment
8. **Commission/Incentive**: Performance-based payments
9. **Email Notifications**: Auto-send salary slips
10. **PDF Generation**: Downloadable salary slips
11. **Excel Export**: Bulk data export for analysis
12. **Payroll Comparison**: Month-over-month analysis
13. **Budget Integration**: Link to budget planning
14. **Bank Integration**: Direct salary transfer files
15. **Attendance Integration**: Auto-fetch working days

## Technical Notes

### Performance Considerations:

- Bulk generation uses chunking for large faculty lists
- Indexes on all frequently queried fields
- Lazy loading for relationships
- Pagination for large datasets

### Error Handling:

- Transaction management for data integrity
- Validation at multiple levels
- Proper error logging
- User-friendly error messages
- Rollback on failures

### Data Integrity:

- Soft deletes for audit trail
- Foreign key relationships (application level)
- Status-based workflow enforcement
- Audit fields for tracking changes
- Automatic code generation for uniqueness

## Troubleshooting

### Common Issues:

**Issue**: Faculty not showing in payroll generation

- **Solution**: Ensure faculty has active salary master assigned
- Check if faculty is marked as left (IS_LEFT = 1)

**Issue**: Salary calculation mismatch

- **Solution**: Verify pay matrix calculations
- Check working days ratio
- Verify loan EMI deduction

**Issue**: Cannot delete pay matrix

- **Solution**: Pay matrix is assigned to faculty
- Archive instead of delete
- Reassign faculty to different matrix first

**Issue**: Duplicate salary slips

- **Solution**: System prevents duplicates by month/year/faculty
- Check existing slips before generation
- Delete draft duplicates if any

## Support and Maintenance

For issues or questions regarding the payroll system, contact the development team or refer to:

- System logs: `storage/logs/laravel.log`
- Database schema: Refer to migration files
- Code documentation: Inline comments in controllers and models

## Conclusion

The HR Payroll & Pay Matrix system provides a comprehensive solution for standardized salary management. It integrates seamlessly with the existing HR module and provides flexibility for various salary structures while maintaining data integrity and audit trails.
