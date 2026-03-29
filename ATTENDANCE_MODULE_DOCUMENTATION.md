# Faculty Student Attendance Module

## Overview

This module allows faculty members to take, manage, and view student attendance for their assigned subjects.

## Features

### 1. **Subject Dashboard**

- View all subjects assigned to the faculty
- Quick access to take attendance or view records
- Subject details including code, semester, and course

### 2. **Take Attendance**

- Select date and lecture time
- Mark students as: Present, Absent, Late, or Excused
- Add optional remarks for each student
- "Mark All Present" quick action button
- Edit previously recorded attendance

### 3. **View Attendance Records**

- **Statistics Summary**: View attendance percentage for each student
- Color-coded indicators (Green ≥75%, Yellow ≥60%, Red <60%)
- Detailed attendance records organized by date
- Lecture-wise attendance breakdown

## Database Schema

### `student_attendances` Table

- `id`: Primary key
- `syllabus_faculty_id`: Foreign key to syllabus_has_faculties
- `student_id`: Foreign key to student_masters
- `attendance_date`: Date of the lecture
- `lecture_start_time`: Start time of the lecture
- `lecture_end_time`: End time of the lecture (optional)
- `status`: Enum (present, absent, late, excused)
- `remarks`: Optional text field
- `timestamps`: Laravel timestamps
- `deleted_at`: Soft delete timestamp

**Unique Constraint**: Prevents duplicate attendance records for the same student, subject, date, and time.

## Routes

All routes are prefixed with `/faculty/attendance` and protected by authentication middleware.

| Method | Route                       | Controller Method  | Purpose                  |
| ------ | --------------------------- | ------------------ | ------------------------ |
| GET    | `/`                         | `index`            | List all subjects        |
| GET    | `/take/{syllabusFacultyId}` | `takeAttendance`   | Show attendance form     |
| POST   | `/store`                    | `storeAttendance`  | Save attendance records  |
| GET    | `/view/{syllabusFacultyId}` | `viewAttendance`   | View attendance records  |
| DELETE | `/{id}`                     | `deleteAttendance` | Delete attendance record |

## Models

### StudentAttendance

**Location**: `app/Models/StudentAttendance.php`

**Relationships**:

- `syllabusFaculty()`: BelongsTo SyllabusHasFaculty
- `student()`: BelongsTo StudentMaster

**Scopes**:

- `dateRange($startDate, $endDate)`: Filter by date range
- `status($status)`: Filter by attendance status

**Methods**:

- `getAttendancePercentage($studentId, $syllabusFacultyId)`: Calculate attendance percentage

### SyllabusHasFaculty (Updated)

**New Relationships**:

- `attendances()`: HasMany StudentAttendance
- `faculty()`: BelongsTo Faculty

### SubjectHasSyllabus (Updated)

**New Relationships**:

- `semester()`: BelongsTo Semester
- `courseCombination()`: BelongsTo CourseCombination

## Controller

### AttendanceController

**Location**: `app/Faculty/Http/Controllers/AttendanceController.php`

**Methods**:

1. `index()`: Display all subjects assigned to faculty
2. `takeAttendance($syllabusFacultyId)`: Show attendance form with enrolled students
3. `storeAttendance(Request $request)`: Save or update attendance records
4. `viewAttendance($syllabusFacultyId)`: Display attendance statistics and records
5. `deleteAttendance($id)`: Delete a specific attendance record
6. `getEnrolledStudents($syllabusId)`: Private helper to fetch enrolled students

## Views

### 1. Index View (`resources/views/faculty/attendance/index.blade.php`)

- Grid layout of assigned subjects
- Cards with subject details
- Action buttons for taking and viewing attendance

### 2. Take Attendance View (`resources/views/faculty/attendance/take.blade.php`)

- Date and time selection
- Student list with radio button groups
- Remarks input for each student
- Form validation
- "Mark All Present" JavaScript function

### 3. View Records View (`resources/views/faculty/attendance/view.blade.php`)

- Statistics table with attendance percentages
- Accordion-style date grouping
- Color-coded status badges
- Lecture-wise breakdown

## Usage Instructions

### For Faculty

1. **Access the Module**
    - Navigate to `/faculty/attendance`
    - You'll see all your assigned subjects

2. **Taking Attendance**
    - Click "Take Attendance" on a subject card
    - Select the date and lecture time
    - Mark each student's attendance status
    - Add remarks if needed
    - Click "Save Attendance"

3. **Viewing Records**
    - Click "View Records" on a subject card
    - See overall statistics for each student
    - Expand dates to see detailed records
    - View lecture-wise attendance

4. **Editing Attendance**
    - Go to "Take Attendance"
    - Select the same date and time
    - Update student statuses
    - Click "Save Attendance" to update

## Installation & Setup

### Step 1: Run Migration

```bash
php artisan migrate --path=database/migrations/2026_03_25_000001_create_student_attendances_table.php
```

### Step 2: Clear Cache (Optional)

```bash
php artisan cache:clear
php artisan route:clear
php artisan view:clear
```

### Step 3: Verify Routes

```bash
php artisan route:list --name=faculty.attendance
```

## Validation Rules

### Store Attendance Request

- `syllabus_faculty_id`: Required, must exist in syllabus_has_faculties
- `attendance_date`: Required, must be a valid date
- `lecture_start_time`: Required
- `lecture_end_time`: Optional
- `attendance`: Required array
- `attendance.*`: Must be one of: present, absent, late, excused

## Security Features

1. **Authentication**: All routes require faculty authentication
2. **Authorization**: Faculty can only access their assigned subjects
3. **Foreign Key Constraints**: Maintains data integrity
4. **Soft Deletes**: Allows recovery of accidentally deleted records
5. **Unique Constraints**: Prevents duplicate attendance entries
6. **CSRF Protection**: All forms include CSRF tokens

## Customization

### Attendance Statuses

To add or modify attendance statuses, update:

1. Database migration enum field
2. `StudentAttendance` model fillable array
3. View radio button options
4. Controller validation rules

### Attendance Percentage Thresholds

Modify color-coding in `view.blade.php`:

```php
$color = $percentage >= 75 ? 'success' : ($percentage >= 60 ? 'warning' : 'danger');
```

## Troubleshooting

### Issue: Students Not Showing

**Solution**: Ensure students are enrolled in `syllabus_has_students` table

### Issue: Subject Not Listed

**Solution**: Verify faculty assignment in `syllabus_has_faculties` table

### Issue: Duplicate Entry Error

**Solution**: An attendance record already exists for the same student, date, and time

### Issue: Foreign Key Constraint Error

**Solution**: Ensure `student_masters` and `syllabus_has_faculties` tables exist and contain the referenced records

## Future Enhancements

- Export attendance to Excel/PDF
- Bulk import attendance
- SMS/Email notifications to students
- Biometric integration
- Mobile app support
- Attendance analytics and reports
- Auto-attendance based on geolocation
- Integration with QR code system (table already exists: `attendance_qr_masters`)

## Support

For issues or questions, contact the development team or refer to:

- Laravel Documentation: https://laravel.com/docs
- Project Repository: [Your repo URL]

---

**Version**: 1.0  
**Last Updated**: March 25, 2026  
**Author**: Development Team
