# HR Module Documentation

## Overview

The HR (Human Resources) module is a comprehensive system for managing faculty, leave applications, FDP programs, and recruitment/vacancy management in the SCS ERP system.

## Features Implemented

### 1. Faculty Management (CRUD)

- **Create**: Add new faculty members with complete details
- **Read**: View faculty list with search and filter options
- **Update**: Edit faculty information, qualifications, and HR details
- **Delete**: Soft delete faculty records
- **Additional Features**:
    - Mark faculty as "Left" with date of leaving
    - Restore faculty to active status
    - Enhanced faculty profile with additional HR fields (PAN, Aadhar, bank details, etc.)

### 2. Leave Master & Management

The leave management system already existed in the application. The HR module enhances it with:

- **View all leave applications** across all faculty
- **Approve/Reject leave applications** with reasons
- **Forward leave applications** to Principal or higher authorities
- **Change leave type** of submitted applications
- **Bulk approve** multiple applications
- **Leave statistics** and analytics
- **Track leave history** by session and faculty

### 3. FDP (Faculty Development Program) Management

Complete FDP lifecycle management:

- **Create FDP programs** with details (type, duration, venue, fees, etc.)
- **Manage participants** (faculty and staff)
- **Track attendance** and completion status
- **Issue certificates** with tracking numbers
- **Faculty-wise FDP tracker** showing all programs attended by each faculty
- **Program types**: Workshop, Seminar, Conference, Training, Certification, Other
- **Participant statuses**: Registered, Approved, Rejected, Attended, Absent, Completed

### 4. Vacancy & Recruitment Management

Full recruitment workflow:

- **Create job vacancies** with detailed job descriptions
- **Recruitment types**: Regular, Ad-hoc, Contractual, Guest, Visiting
- **Publish vacancies** to public careers page
- **Manage applications** with status tracking
- **Interview scheduling** with venue and time management
- **Application statuses**: Submitted, Under Review, Shortlisted, Interview Scheduled, Selected, Rejected, Withdrawn
- **Public careers page** for external applicants

### 5. Pay Matrix Master & Payroll Management

**NEW FEATURE**: Comprehensive payroll system with standardized salary structures.

#### Pay Matrix Features:

- **Create pay matrix** with designation and grade levels
- **Flexible salary components**: Basic, DA, HRA, TA, Medical, Special allowances
- **Deduction management**: PF, ESI, Professional Tax, TDS
- **Percentage or fixed amount** calculations for allowances/deductions
- **Annual increment** configuration
- **Employment types**: Permanent, Contractual, Ad-hoc, Guest, Visiting
- **Effective date management** for salary revisions
- **Duplicate matrices** for easy creation
- **Archive old matrices** for historical tracking
- **Faculty assignment tracking**

#### Payroll Features:

- **Assign pay matrix to faculty** with effective dates
- **Bulk generate salary slips** for entire faculty
- **Selective generation** for specific faculty members
- **Automatic salary calculations** from pay matrix
- **Pro-rata calculation** based on working days
- **Automatic loan EMI deduction** integration
- **Approve salary slips** workflow
- **Mark as paid** with payment tracking
- **Monthly payroll dashboard** with statistics
- **Payroll analytics** and reporting

For detailed payroll documentation, see [HR_PAYROLL_MATRIX_DOCUMENTATION.md](./HR_PAYROLL_MATRIX_DOCUMENTATION.md)

## Database Structure

### New Tables Created

#### 1. `hr_fdp_programs`

Stores Faculty Development Program information.

**Key Fields**:

- `program_code` (unique identifier)
- `program_title`, `description`
- `program_type` (workshop, seminar, conference, etc.)
- `start_date`, `end_date`, `duration_days`
- `max_participants`, `program_fee`
- `status` (draft, open, ongoing, completed, cancelled)
- `target_audience` (faculty, staff, both)

#### 2. `hr_fdp_participants`

Tracks faculty participation in FDP programs.

**Key Fields**:

- `fdp_program_id`, `faculty_id`
- `participant_type` (faculty/staff)
- `status` (registered, approved, attended, completed, etc.)
- `attendance_status`, `days_attended`
- `certificate_issued`, `certificate_number`, `certificate_date`
- `feedback`, `rating`

#### 3. `hr_vacancies`

Stores job vacancy postings.

**Key Fields**:

- `vacancy_code` (unique identifier)
- `position_title`, `department_id`
- `employment_type` (full-time, part-time, contract, etc.)
- `recruitment_type` (regular, adhoc, contractual, etc.)
- `number_of_positions`
- `job_description`, `qualifications_required`, `experience_required`
- `application_start_date`, `application_end_date`
- `status` (draft, published, closed, cancelled, filled)
- `publish_to_website` (boolean)

#### 4. `hr_vacancy_applications`

Stores job applications from candidates.

**Key Fields**:

- `vacancy_id`, `application_number`
- `applicant_name`, `email`, `phone`
- `highest_qualification`, `specialization`
- `total_experience_years`, `teaching_experience_years`
- `resume_attachment`, `photo_attachment`
- `status` (submitted, under_review, shortlisted, selected, etc.)
- `interview_date`, `interview_time`, `interview_venue`
- `interview_score`, `rejection_reason`

#### 5. Enhanced `faculties` table

Added HR-related fields:

- `employee_type`, `designation`
- `qualification`, `specialization`, `experience_years`
- `pan_number`, `aadhar_number`
- `bank_account_number`, `bank_ifsc_code`, `bank_name`
- `emergency_contact_name`, `emergency_contact_number`
- `permanent_address`

#### 6. `hr_pay_matrix`

Stores standardized salary structure templates (pay matrix).

**Key Fields**:

- `matrix_code` (unique identifier - PM2026XXXX)
- `matrix_name`, `designation`, `grade_level`
- `pay_band`, `grade_pay`
- `employment_type` (permanent, contractual, adhoc, guest, visiting)
- Earnings: `basic_salary`, `da_percentage`, `da_fixed`, `hra_percentage`, `hra_fixed`, `ta`, `medical_allowance`, etc.
- Deductions: `pf_percentage`, `pf_fixed`, `esi_percentage`, `esi_fixed`, `professional_tax`, `tds_percentage`, etc.
- `annual_increment_percentage`, `increment_month`
- `default_working_days`
- `status` (active, inactive, archived)
- `effective_from`, `effective_to`

#### 7. `faculty_salary_masters` (Enhanced)

Added new field:

- `pay_matrix_id` - Links to hr_pay_matrix table for salary structure reference

### Existing Tables (Enhanced)

- `faculty_leave_applications` - Already has forwarding and rejection capabilities
- `leave_masters` - Leave type master (already exists)

## Models Created

### 1. HrFdpProgram (`app/Models/HrFdpProgram.php`)

**Key Methods**:

- `participants()` - Get all participants
- `approvedParticipants()` - Get approved participants
- `completedParticipants()` - Get completed participants
- `isFull()` - Check if program reached max capacity
- Scopes: `active()`, `upcoming()`, `ongoing()`, `completed()`

### 2. HrFdpParticipant (`app/Models/HrFdpParticipant.php`)

**Key Methods**:

- `fdpProgram()` - Get the FDP program
- `faculty()` - Get the faculty member
- Scopes: `approved()`, `completed()`, `pending()`, `attended()`

### 3. HrVacancy (`app/Models/HrVacancy.php`)

**Key Methods**:

- `applications()` - Get all applications
- `shortlistedApplications()` - Get shortlisted applications
- `isOpen()`, `isClosed()` - Check vacancy status
- Scopes: `active()`, `published()`, `open()`, `closed()`

### 4. HrVacancyApplication (`app/Models/HrVacancyApplication.php`)

**Key Methods**:

- `vacancy()` - Get the vacancy
- `hasInterviewScheduled()` - Check if interview is scheduled
- Scopes: `submitted()`, `underReview()`, `shortlisted()`, `selected()`, `rejected()`

### 5. Enhanced Faculty Model

Added relationships:

- `leaveApplications()` - Get all leave applications
- `fdpParticipations()` - Get all FDP participations
- `completedFdpPrograms()` - Get completed FDP programs
- `salaryMaster()` - Get active salary master

### 6. HrPayMatrix (`app/Models/HrPayMatrix.php`)

**NEW MODEL**: Manages pay matrix master records.

**Key Methods**:

- `facultySalaries()` - Get all faculty using this matrix
- `creator()`, `updater()` - Get user who created/updated
- `calculateDA()`, `calculateHRA()`, `calculatePF()`, `calculateESI()`, `calculateTDS()` - Calculate amounts
- `calculateGrossSalary()`, `calculateTotalDeductions()`, `calculateNetSalary()` - Calculate totals
- `getSalaryComponents()` - Get complete salary breakdown
- `isEffective($date)` - Check if effective on date
- `generateMatrixCode()` - Auto-generate unique code
- Scopes: `active()`, `inactive()`, `byEmploymentType()`, `byDesignation()`, `effectiveOn($date)`
- Attributes: `faculty_count`, `full_designation`, `status_color`

### 7. Enhanced FacultySalaryMaster Model

Added relationship:

- `payMatrix()` - Get the pay matrix this salary is based on

## Controllers Created

### 1. HrFacultyController

Manages faculty CRUD operations:

- `index()` - List all faculty with search and filters
- `create()`, `store()` - Add new faculty
- `show()` - View faculty details with leave and FDP stats
- `edit()`, `update()` - Update faculty information
- `destroy()` - Delete faculty
- `markAsLeft()` - Mark faculty as left with DOL
- `restore()` - Restore faculty to active status

### 2. HrLeaveController

Manages leave applications:

- `index()` - List all applications with filters
- `show()` - View application details
- `reviewForm()` - Show review form
- `approve()`, `reject()` - Approve/reject applications
- `forward()` - Forward to Principal or higher authority
- `changeLeaveType()` - Change leave type with reason
- `statistics()` - View leave statistics and analytics
- `bulkApprove()` - Bulk approve applications

### 3. HrFdpController

Manages FDP programs:

- `index()` - List all FDP programs
- `create()`, `store()` - Create new FDP
- `show()` - View FDP details with participant list
- `edit()`, `update()` - Update FDP details
- `destroy()` - Delete FDP
- `addParticipantForm()`, `addParticipant()` - Add participants
- `approveParticipant()` - Approve participant registration
- `completeParticipant()` - Mark participant as completed with certificate
- `facultyTracker()` - Track FDP participation faculty-wise

### 4. HrVacancyController

Manages vacancies and applications:

- `index()` - List all vacancies
- `create()`, `store()` - Create vacancy
- `show()` - View vacancy with application stats
- `edit()`, `update()` - Update vacancy
- `destroy()` - Delete vacancy
- `publish()`, `close()` - Publish/close vacancy
- `applications()` - View all applications for a vacancy
- `showApplication()` - View specific application
- `updateApplicationStatus()` - Update application status
- **Public Methods**:
    - `publicIndex()` - Public careers page
    - `publicShow()` - Public vacancy details
    - `publicApplyForm()` - Public application form
    - `publicApply()` - Submit public application

### 5. HrPayMatrixController

**NEW CONTROLLER**: Manages pay matrix CRUD operations.

- `index()` - List all pay matrices with search/filters
- `create()`, `store()` - Create new pay matrix
- `show()` - View pay matrix with calculations
- `edit()`, `update()` - Update pay matrix
- `destroy()` - Delete pay matrix (checks for usage)
- `archive()` - Archive pay matrix
- `duplicate()` - Create copy of pay matrix
- `applyToFaculty()` - Assign matrix to multiple faculty
- `preview()` - JSON response with salary calculations

### 6. HrPayrollController

**NEW CONTROLLER**: Manages payroll generation and processing.

- `index()` - Payroll dashboard with statistics
- `generateForm()` - Show generation form with faculty lists
- `assignPayMatrix()` - Assign pay matrix to faculty member
- `bulkGenerate()` - Generate salary slips for month
- `show()` - View salary slip details
- `approve()` - Approve salary slip
- `markPaid()` - Mark salary slip as paid with payment details
- `destroy()` - Delete salary slip (draft only)
- `statistics()` - Payroll statistics and analytics

## Routes

### HR Module Routes (Protected)

All routes are under `/erp/hr` prefix:

#### Faculty Management

- `GET /hr/faculty` - List faculty
- `GET /hr/faculty/create` - Create form
- `POST /hr/faculty` - Store new faculty
- `GET /hr/faculty/{id}` - View faculty
- `GET /hr/faculty/{id}/edit` - Edit form
- `PUT /hr/faculty/{id}` - Update faculty
- `DELETE /hr/faculty/{id}` - Delete faculty
- `POST /hr/faculty/{id}/mark-left` - Mark as left
- `POST /hr/faculty/{id}/restore` - Restore faculty

#### Leave Management

- `GET /hr/leave` - List applications
- `GET /hr/leave/{id}` - View application
- `GET /hr/leave/{id}/review` - Review form
- `POST /hr/leave/{id}/approve` - Approve
- `POST /hr/leave/{id}/reject` - Reject
- `POST /hr/leave/{id}/forward` - Forward
- `POST /hr/leave/{id}/change-type` - Change leave type
- `GET /hr/leave/statistics` - Statistics
- `POST /hr/leave/bulk-approve` - Bulk approve

#### FDP Management

- `GET /hr/fdp` - List programs
- `GET /hr/fdp/create` - Create form
- `POST /hr/fdp` - Store program
- `GET /hr/fdp/{id}` - View program
- `GET /hr/fdp/{id}/edit` - Edit form
- `PUT /hr/fdp/{id}` - Update program
- `DELETE /hr/fdp/{id}` - Delete program
- `GET /hr/fdp/{id}/add-participant` - Add participant form
- `POST /hr/fdp/{id}/participants` - Store participant
- `POST /hr/fdp/{id}/participants/{participantId}/approve` - Approve participant
- `POST /hr/fdp/{id}/participants/{participantId}/complete` - Complete participant
- `GET /hr/fdp/tracker/faculty` - Faculty tracker

#### Vacancy Management

- `GET /hr/vacancy` - List vacancies
- `GET /hr/vacancy/create` - Create form
- `POST /hr/vacancy` - Store vacancy
- `GET /hr/vacancy/{id}` - View vacancy
- `GET /hr/vacancy/{id}/edit` - Edit form
- `PUT /hr/vacancy/{id}` - Update vacancy
- `DELETE /hr/vacancy/{id}` - Delete vacancy
- `POST /hr/vacancy/{id}/publish` - Publish
- `POST /hr/vacancy/{id}/close` - Close
- `GET /hr/vacancy/{id}/applications` - List applications
- `GET /hr/vacancy/{vacancyId}/applications/{applicationId}` - View application
- `POST /hr/vacancy/{vacancyId}/applications/{applicationId}/update-status` - Update status

### Public Routes (Unprotected)

- `GET /careers` - Public careers page
- `GET /careers/{id}` - View vacancy details
- `GET /careers/{id}/apply` - Application form
- `POST /careers/{id}/apply` - Submit application

## Views Created

### Dashboard

- `resources/views/hr/dashboard.blade.php` - HR dashboard with stats and quick actions

### Sidebar

- `resources/views/includes/hr-sidebar.blade.php` - HR module navigation sidebar

### Faculty Views

- `resources/views/hr/faculty/index.blade.php` - Faculty list with search/filter

### Leave Views

- `resources/views/hr/leave/index.blade.php` - Leave applications list

### FDP Views

- `resources/views/hr/fdp/index.blade.php` - FDP programs list

### Vacancy Views

- `resources/views/hr/vacancy/index.blade.php` - Vacancies list

**Note**: Additional views (create, edit, show forms) need to be created for full CRUD functionality.

## Installation Steps

### 1. Run Migrations

All HR module migrations are organized in `database/migrations/hr/` folder for better organization.

```bash
# Run all migrations (includes HR migrations)
php artisan migrate

# Or run HR migrations specifically
php artisan migrate --path=database/migrations/hr
```

This will create all the new tables:

- `hr_fdp_programs`
- `hr_fdp_participants`
- `hr_vacancies`
- `hr_vacancy_applications`
- `hr_pay_matrix`
- Add HR fields to `faculties` table
- Add `pay_matrix_id` to `faculty_salary_masters` table

### 2. Seed Leave Masters (Optional)

If you need to create leave type masters:

```php
// Run in tinker or create a seeder
php artisan tinker

App\Models\LeaveMaster::create([
    'leave_type_name' => 'Casual Leave',
    'leave_type_code' => 'CL',
    'allowed_days_per_year' => 10,
    'is_active' => true,
    'display_order' => 1,
    'badge_color' => '#0d6efd'
]);
```

### 3. Access the Module

Navigate to: `/erp/hr/dashboard`

## Usage Guide

### Faculty Management

1. **Add New Faculty**: Navigate to HR > Faculty > Add Faculty
2. **View Faculty**: Click on any faculty from the list
3. **Edit Faculty**: Use the edit button from faculty list or detail page
4. **Mark as Left**: From faculty detail page, use "Mark as Left" button

### Leave Management

1. **View Applications**: Navigate to HR > Leave Applications
2. **Review Application**: Click "Review" button on pending applications
3. **Approve/Reject**: Use the review form to approve or reject with remarks
4. **Forward**: Forward application to Principal or higher authority
5. **Change Leave Type**: Use "Change Type" option if leave category needs correction

### FDP Management

1. **Create Program**: Navigate to HR > FDP > Create FDP
2. **Add Participants**: From FDP detail page, use "Add Participant" button
3. **Approve Participants**: Approve registered participants
4. **Mark Completion**: When program ends, mark participants as completed and issue certificates
5. **Track Faculty**: Use "Faculty Tracker" to see all FDPs attended by each faculty

### Vacancy Management

1. **Post Vacancy**: Navigate to HR > Vacancy > Post Vacancy
2. **Publish**: After creating draft, publish to make it live on careers page
3. **Review Applications**: View and manage all applications from vacancy detail page
4. **Update Status**: Shortlist, schedule interviews, select or reject candidates
5. **Close Vacancy**: Once filled or expired, close the vacancy

### Public Careers Page

- Candidates can visit `/careers` to view all open positions
- They can apply online by uploading resume and other documents
- Application tracking number is generated automatically

## Future Enhancements

- Email notifications for leave approvals/rejections
- FDP certificate PDF generation
- Interview feedback forms
- Applicant ranking system
- Reports and analytics dashboard
- Payroll integration with HR data
- Performance appraisal module

## Technical Notes

### File Locations

```
Migrations: database/migrations/2026_05_27_*
Models: app/Models/HrFdpProgram.php, HrFdpParticipant.php, HrVacancy.php, HrVacancyApplication.php
Controllers: app/Http/Controllers/HrFacultyController.php, HrLeaveController.php, HrFdpController.php, HrVacancyController.php
Routes: routes/web.php (line ~1060-1100)
Views: resources/views/hr/
```

### Dependencies

- Laravel Framework
- Existing SCS ERP infrastructure
- File upload handling (S3 or local storage)
- Existing authentication system

### Security Considerations

- All HR routes should be protected with appropriate middleware
- Role-based access control should be implemented
- Sensitive data (PAN, Aadhar) should be encrypted
- File uploads should be validated and sanitized

## Support

For issues or questions, refer to the main SCS ERP documentation or contact the development team.
