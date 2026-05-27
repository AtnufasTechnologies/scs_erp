# Students Migrations

This folder contains all migrations related to student management and tracking.

## Tables Created

### Student Master Data

- `student_masters` - Student master records
- `student_master_user_pivots` - Student-user account linkage
- `enrolled_student_lists` - Student enrollment records

### Attendance

- `student_attendances` - Regular class attendance
- `extra_class_attendances` - Extra class attendance
- `attendance_qr_masters` - QR code based attendance

### Payments

- `student_payments` - Student fee payments
- `student_late_fee_exemptions` - Late fee exemption records

## Purpose

Manages student master data, enrollment, attendance tracking, and payment records.

## Running Migrations

```bash
php artisan migrate --path=database/migrations/students
```
