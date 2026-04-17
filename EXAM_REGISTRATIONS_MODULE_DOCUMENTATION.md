# Exam Registrations Module Documentation

## Overview

The **Exam Registrations Module** is a comprehensive system designed to manage student registrations for examinations in the Salesian College ERP system. This module handles the complete lifecycle of exam registrations, including creation, approval, payment tracking, and reporting.

## Table of Contents

1. [Features](#features)
2. [Database Structure](#database-structure)
3. [Module Components](#module-components)
4. [Installation & Setup](#installation--setup)
5. [Usage Guide](#usage-guide)
6. [API Endpoints](#api-endpoints)
7. [Workflow](#workflow)
8. [Permissions & Access Control](#permissions--access-control)
9. [Troubleshooting](#troubleshooting)

---

## Features

### Core Functionality

- **Student Registration Management**: Create and manage exam registrations for students
- **Approval Workflow**: Multi-stage approval process for exam registrations
- **Payment Tracking**: Track registration fees and payment status
- **Bulk Operations**: Approve or reject multiple registrations at once
- **Advanced Filtering**: Filter registrations by exam, status, campus, semester, and student details
- **Registration Types**: Support for both regular and backlog exam registrations
- **Comprehensive Reporting**: Detailed view of registration data with export capabilities
- **Search Functionality**: Quick search by student name, registration number, or roll number

### Additional Features

- Automatic registration number generation
- Payment reference tracking
- Audit trail with approval timestamps
- Status management (Pending, Approved, Rejected, Cancelled)
- Print-friendly registration details
- Real-time validation for duplicate registrations

---

## Database Structure

### Table: `exam_registrations`

```sql
CREATE TABLE exam_registrations (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    exam_id BIGINT UNSIGNED NOT NULL,
    exam_student_id BIGINT UNSIGNED NOT NULL,
    semester_id BIGINT UNSIGNED NULL,
    registration_number VARCHAR(255) UNIQUE NULL,
    is_backlog BOOLEAN DEFAULT FALSE,
    is_regular BOOLEAN DEFAULT TRUE,
    registration_fee DECIMAL(10,2) DEFAULT 0.00,
    fee_paid BOOLEAN DEFAULT FALSE,
    payment_reference VARCHAR(255) NULL,
    registration_date DATE NULL,
    payment_date DATE NULL,
    status ENUM('pending', 'approved', 'rejected', 'cancelled') DEFAULT 'pending',
    remarks TEXT NULL,
    approved_by BIGINT UNSIGNED NULL,
    approved_at TIMESTAMP NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,

    FOREIGN KEY (exam_id) REFERENCES exams(id) ON DELETE CASCADE,
    FOREIGN KEY (exam_student_id) REFERENCES student_masters(id) ON DELETE CASCADE,
    FOREIGN KEY (semester_id) REFERENCES semesters(id) ON DELETE SET NULL,
    FOREIGN KEY (approved_by) REFERENCES users(id) ON DELETE SET NULL,

    INDEX idx_status (status),
    INDEX idx_exam_id (exam_id),
    INDEX idx_student_id (exam_student_id),
    INDEX idx_registration_date (registration_date)
);
```

### Field Descriptions

| Field                 | Type      | Description                                          |
| --------------------- | --------- | ---------------------------------------------------- |
| `id`                  | BIGINT    | Primary key                                          |
| `exam_id`             | BIGINT    | Foreign key to exams table                           |
| `exam_student_id`     | BIGINT    | Foreign key to student_masters table                 |
| `semester_id`         | BIGINT    | Foreign key to semesters table (optional)            |
| `registration_number` | VARCHAR   | Unique auto-generated registration number            |
| `is_backlog`          | BOOLEAN   | True if this is a backlog exam registration          |
| `is_regular`          | BOOLEAN   | True if student is regular (default: true)           |
| `registration_fee`    | DECIMAL   | Amount of registration fee                           |
| `fee_paid`            | BOOLEAN   | Payment status flag                                  |
| `payment_reference`   | VARCHAR   | External payment reference/transaction ID            |
| `registration_date`   | DATE      | Date when registration was created                   |
| `payment_date`        | DATE      | Date when payment was made                           |
| `status`              | ENUM      | Current status (pending/approved/rejected/cancelled) |
| `remarks`             | TEXT      | Additional notes or comments                         |
| `approved_by`         | BIGINT    | User ID who approved the registration                |
| `approved_at`         | TIMESTAMP | Timestamp of approval                                |

---

## Module Components

### 1. Migration File

**Location:** `database/migrations/2026_03_31_000001_create_exam_registrations_table.php`

Creates the exam_registrations table with all necessary fields, foreign keys, and indexes.

### 2. Model

**Location:** `app/Models/ExamSystem/Registration.php`

**Key Features:**

- Eloquent relationships with Student, Exam, Semester, and User models
- Query scopes for filtering (approved, pending, rejected, backlog, regular, feePaid)
- Fillable fields configuration
- Type casting for dates and booleans

**Relationships:**

```php
- student() -> BelongsTo StudentMaster
- exam() -> BelongsTo Exam
- semester() -> BelongsTo Semester
- approvedBy() -> BelongsTo User
```

**Query Scopes:**

```php
Registration::approved()->get();
Registration::pending()->get();
Registration::backlog()->get();
Registration::regular()->get();
Registration::feePaid()->get();
```

### 3. Controller

**Location:** `app/Http/Controllers/ExamRegistrationController.php`

**Methods:**

- `index()` - List all registrations with filters
- `create()` - Show registration creation form
- `store()` - Save new registration
- `show($id)` - Display single registration details
- `edit($id)` - Show edit form
- `update($id)` - Update registration
- `destroy($id)` - Delete registration
- `bulkApprove()` - Approve multiple registrations
- `bulkReject()` - Reject multiple registrations
- `register()` - API endpoint for student registration
- `export()` - Export registrations (placeholder for future implementation)

### 4. Views

#### Index View

**Location:** `resources/views/admin/exam-registrations/index.blade.php`

Features:

- Advanced filtering interface
- Bulk selection and actions
- Pagination
- Status badges
- Quick actions (View, Edit, Delete)

#### Create View

**Location:** `resources/views/admin/exam-registrations/create.blade.php`

Features:

- Exam selection dropdown
- Student selection dropdown
- Semester selection
- Registration fee input
- Status selection
- Registration type checkboxes (Regular/Backlog)
- Remarks textarea

#### Edit View

**Location:** `resources/views/admin/exam-registrations/edit.blade.php`

Features:

- All create view features
- Payment reference tracking
- Fee paid checkbox
- Approval information display

#### Show/Details View

**Location:** `resources/views/admin/exam-registrations/show.blade.php`

Features:

- Complete registration information display
- Student details section
- Exam information section
- Payment details section
- Approval and remarks section
- Print functionality

### 5. Routes

**Location:** `routes/web.php`

```php
Route::group(['prefix' => '/admin/exam-registrations'], function () {
    Route::get('/', 'ExamRegistrationController@index')->name('admin.exam-registrations.index');
    Route::get('/create', 'ExamRegistrationController@create')->name('admin.exam-registrations.create');
    Route::post('/', 'ExamRegistrationController@store')->name('admin.exam-registrations.store');
    Route::get('/{id}', 'ExamRegistrationController@show')->name('admin.exam-registrations.show');
    Route::get('/{id}/edit', 'ExamRegistrationController@edit')->name('admin.exam-registrations.edit');
    Route::put('/{id}', 'ExamRegistrationController@update')->name('admin.exam-registrations.update');
    Route::delete('/{id}', 'ExamRegistrationController@destroy')->name('admin.exam-registrations.destroy');
    Route::post('/bulk-approve', 'ExamRegistrationController@bulkApprove')->name('admin.exam-registrations.bulk-approve');
    Route::post('/bulk-reject', 'ExamRegistrationController@bulkReject')->name('admin.exam-registrations.bulk-reject');
    Route::get('/export', 'ExamRegistrationController@export')->name('admin.exam-registrations.export');
});
```

---

## Installation & Setup

### Step 1: Run Migration

```bash
php artisan migrate
```

This will create the `exam_registrations` table with all required fields and relationships.

### Step 2: Verify Routes

```bash
php artisan route:list --name=exam-registrations
```

### Step 3: Clear Cache (if needed)

```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

### Step 4: Seed Sample Data (Optional)

Create a seeder if you need test data:

```bash
php artisan make:seeder ExamRegistrationSeeder
```

---

## Usage Guide

### Creating a New Registration

1. Navigate to: **Admin Panel → Exam Registrations → New Registration**
2. Select the exam from the dropdown
3. Select the student
4. Choose semester (if applicable)
5. Enter registration fee
6. Set initial status (Pending/Approved/Rejected)
7. Check "Backlog Exam" if applicable
8. Add any remarks
9. Click "Create Registration"

**Validation:**

- System prevents duplicate registrations for the same exam and student
- Required fields: Exam, Student, Status
- Registration number is auto-generated

### Viewing Registrations

**List View:**

- Go to: **Admin Panel → Exam Registrations**
- Use filters to narrow down results:
    - Filter by Exam
    - Filter by Status
    - Filter by Campus
    - Filter by Semester
    - Filter by Type (Regular/Backlog)
    - Search by student name or registration number

**Detail View:**

- Click the "eye" icon on any registration
- View complete information including:
    - Registration details
    - Student information
    - Exam details
    - Payment information
    - Approval history

### Updating a Registration

1. Click the "edit" icon on any registration
2. Modify the fields as needed
3. Update payment status if applicable
4. Change status to approve/reject
5. Click "Update Registration"

**Note:** Approved registrations will automatically record the approver and approval timestamp.

### Bulk Operations

**Bulk Approve:**

1. Check the boxes next to registrations you want to approve
2. Click "Approve Selected"
3. Confirm the action

**Bulk Reject:**

1. Check the boxes next to registrations you want to reject
2. Click "Reject Selected"
3. Confirm the action

### Deleting a Registration

1. Click the "trash" icon on any registration
2. Confirm deletion
3. Registration will be permanently removed

**Warning:** This action cannot be undone. Consider changing status to "Cancelled" instead of deleting.

---

## API Endpoints

### Student Registration Endpoint

**URL:** `/api/exams/register`  
**Method:** POST  
**Authentication:** Required (Sanctum)

**Request Body:**

```json
{
    "exam_id": 1,
    "student_id": 123
}
```

**Success Response:**

```json
{
    "success": true,
    "message": "Registration successful",
    "data": {
        "id": 1,
        "exam_id": 1,
        "exam_student_id": 123,
        "registration_number": "EXAMREG-2026-000001",
        "status": "pending",
        "registration_date": "2026-03-31"
    }
}
```

**Error Response:**

```json
{
    "success": false,
    "message": "Already registered for this exam"
}
```

---

## Workflow

### Registration Lifecycle

```
1. CREATION
   ↓
2. PENDING (Initial Status)
   ↓
3. APPROVAL/REJECTION
   ↓
4. PAYMENT PROCESSING (if applicable)
   ↓
5. APPROVED (Final Status)
```

### Status Flow

```
Pending → Approved → (No further changes)
Pending → Rejected → (No further changes)
Pending → Cancelled → (No further changes)
Approved → Cancelled → (Special case)
```

### Approval Process

1. **Step 1:** Admin creates or student registers for exam
2. **Step 2:** Registration enters "Pending" status
3. **Step 3:** Admin reviews registration details
4. **Step 4:** Admin approves or rejects
5. **Step 5:** System records approver ID and timestamp
6. **Step 6:** Student receives notification (if implemented)

---

## Permissions & Access Control

### Required Permissions (to be implemented)

- `exam-registrations.view` - View registrations
- `exam-registrations.create` - Create new registrations
- `exam-registrations.edit` - Edit registrations
- `exam-registrations.delete` - Delete registrations
- `exam-registrations.approve` - Approve/Reject registrations
- `exam-registrations.bulk-actions` - Perform bulk operations

### User Roles

- **Super Admin:** Full access to all features
- **Exam Coordinator:** Can view, create, edit, and approve
- **Department Admin:** Can view and create for their department
- **Student:** Can register via API (read-only access to their own registrations)

---

## Troubleshooting

### Common Issues

**Issue 1: "Student already registered for this exam"**

- **Cause:** Duplicate registration attempt
- **Solution:** Check existing registrations before creating new one

**Issue 2: Routes not found**

- **Cause:** Route cache not cleared
- **Solution:** Run `php artisan route:cache`

**Issue 3: Foreign key constraint error**

- **Cause:** Referenced exam or student doesn't exist
- **Solution:** Ensure exam and student exist before creating registration

**Issue 4: Registration number not generated**

- **Cause:** Database transaction issue
- **Solution:** Check database logs and ensure auto-increment is working

**Issue 5: Bulk actions not working**

- **Cause:** JavaScript not loaded or CSRF token mismatch
- **Solution:** Check browser console for errors and verify CSRF token

### Debug Mode

Enable debug mode in `.env`:

```
APP_DEBUG=true
```

Check logs:

```
storage/logs/laravel.log
```

---

## Future Enhancements

### Planned Features

1. **Email Notifications:** Send automated emails on registration status changes
2. **SMS Notifications:** SMS alerts for approval/rejection
3. **Payment Gateway Integration:** Direct payment processing
4. **Admit Card Generation:** Auto-generate admit cards for approved registrations
5. **Excel Export:** Export registrations to Excel format
6. **PDF Reports:** Generate comprehensive PDF reports
7. **Student Portal:** Self-service portal for students to register
8. **Dashboard Analytics:** Visual statistics and charts
9. **Automatic Approval:** Rules-based automatic approval system
10. **Mobile App Integration:** REST API for mobile applications

### Integration Points

- **Attendance Module:** Link registrations with attendance tracking
- **Marks Module:** Link registrations with marks entry
- **Fee Module:** Integrate with fee payment system
- **Results Module:** Link registrations with result publication

---

## Support & Maintenance

### Regular Maintenance Tasks

1. Archive old registrations quarterly
2. Verify data integrity monthly
3. Clean up orphaned records
4. Update indexes based on query performance
5. Review and optimize slow queries

### Backup Strategy

- Daily automated backups of exam_registrations table
- Maintain backups for at least 1 year
- Test restore procedures quarterly

### Monitoring

- Track registration counts per exam
- Monitor approval turnaround time
- Track payment collection rates
- Alert on failed registrations

---

## Changelog

### Version 1.0.0 (2026-03-31)

- Initial release
- Core CRUD operations
- Bulk approve/reject functionality
- Advanced filtering
- Payment tracking
- Approval workflow

---

## Credits

**Developed for:** Salesian College Autonomous (Siliguri & Sonada)  
**Module:** Exam Registrations Management  
**Version:** 1.0.0  
**Date:** March 31, 2026

---

## License

This module is part of the Salesian College ERP System and is proprietary software.

---

## Contact

For technical support or questions:

- Email: support@scms.edu.in
- Phone: [Contact Number]
- Issue Tracking: [Internal Issue Tracker URL]

---

**End of Documentation**
