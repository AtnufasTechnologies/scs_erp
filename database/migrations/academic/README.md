# Academic Migrations

This folder contains all migrations related to academic structures and curriculum management.

## Tables Created

### Programs & Courses

- `main_programs` - Main program definitions
- `program_masters` - Program master data
- `program_objectives` - Program educational objectives
- `batch_masters` - Student batch management
- `annual_sessions` - Academic year/session management

### Subjects & Curriculum

- `subjects` - Subject master data
- `subject_type_masters` - Subject type classifications
- `subject_course_masters` - Subject-course mappings
- `subject_course_offerings` - Course offerings per semester
- `subject_combination_masters` - Valid subject combinations
- `subject_has_routines` - Class routines/timetable
- `subject_has_syllabi` - Syllabus assignments
- `subject_has_semesters` - Semester mappings
- `subject_has_student_progams` - Subject-program relationships
- `subject_has_dept_admins` - Department admin assignments

### Syllabus Management

- `syllabus_managers` - Syllabus management
- `syllabus_subunits` - Syllabus subunit breakdown
- `syllabus_has_faculties` - Faculty-syllabus assignments
- `syllabus_has_students` - Student-syllabus enrollment
- `syllabus_pdf_uploads` - Syllabus PDF documents
- `sub_unit_student_feedback` - Student feedback on subunits

### Learning Outcomes

- `po_has_cos` - Program Outcomes to Course Outcomes mapping
- `co_has_csos` - Course Outcomes to Course Specific Outcomes
- `cso_subunits` - CSO subunit breakdown

### Departments & Resources

- `deaneries` - Deanery/faculty structure
- `department_activities` - Departmental activities
- `department_activity_has_participants` - Activity participation
- `learning_resources` - Learning resources library
- `mentorship_tables` - Student mentorship program

### Student Registration

- `student_offering_registrations` - Student course registrations
- `course_seat_allocations` - Seat allocation for courses

## Purpose

Manages the complete academic structure including programs, courses, subjects, syllabus, learning outcomes, and student registrations.

## Running Migrations

```bash
php artisan migrate --path=database/migrations/academic
```
