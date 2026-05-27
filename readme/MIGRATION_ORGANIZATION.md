# Migration Organization Guide

## Overview

All 155 database migrations have been successfully organized into 13 logical module-based folders. This organization improves code maintainability, makes it easier to locate specific migrations, and provides clear separation of concerns.

## Complete Folder Structure

```
database/migrations/
│
├── 📄 README.md                    # Main documentation
├── 📄 MIGRATION_SUMMARY.txt        # Quick reference guide
├── 📄 FOLDER_STRUCTURE.txt         # Visual folder hierarchy
├── 🔧 check-migrations.sh          # Status checker script
│
├── 📁 core/ (5)                    # Laravel framework tables
├── 📁 admissions/ (8)              # Student admission process
├── 📁 academic/ (30)               # Programs, subjects, curriculum
├── 📁 faculty/ (26)                # Faculty management & payroll
├── 📁 students/ (13)               # Student records & attendance
├── 📁 exams/ (9)                   # Examinations & assessments
├── 📁 fees/ (15)                   # Fee structures & banking
├── 📁 infrastructure/ (5)          # Campus & facilities
├── 📁 masters/ (9)                 # Master data & lookups
├── 📁 permissions/ (10)            # User access & RBAC
├── 📁 system/ (8)                  # Logs & utilities
├── 📁 event_coordinator/ (10)      # Events & programs
└── 📁 hr/ (7)                      # Human Resources
```

## Migration Distribution

| Folder            | Files   | Percentage |
| ----------------- | ------- | ---------- |
| academic          | 30      | 19.4%      |
| faculty           | 26      | 16.8%      |
| fees              | 15      | 9.7%       |
| students          | 13      | 8.4%       |
| event_coordinator | 10      | 6.5%       |
| permissions       | 10      | 6.5%       |
| exams             | 9       | 5.8%       |
| masters           | 9       | 5.8%       |
| admissions        | 8       | 5.2%       |
| system            | 8       | 5.2%       |
| hr                | 7       | 4.5%       |
| core              | 5       | 3.2%       |
| infrastructure    | 5       | 3.2%       |
| **TOTAL**         | **155** | **100%**   |

## Documentation Structure

Each folder contains:

- ✅ Migration files (.php)
- ✅ README.md with detailed documentation
- ✅ Purpose and scope definition
- ✅ Tables created list
- ✅ Usage examples

## Common Commands

### Check All Migration Status

```bash
./check-migrations.sh
```

### Run All Migrations

```bash
php artisan migrate
```

### Run Specific Module

```bash
# Examples for different modules
php artisan migrate --path=database/migrations/core
php artisan migrate --path=database/migrations/academic
php artisan migrate --path=database/migrations/hr
php artisan migrate --path=database/migrations/faculty
```

### Check Status of Specific Module

```bash
php artisan migrate:status --path=database/migrations/exams
```

### Rollback Specific Module

```bash
php artisan migrate:rollback --path=database/migrations/fees
```

### Fresh Installation (Development Only)

```bash
# WARNING: This drops all tables!
php artisan migrate:fresh
```

## Module Descriptions

### 🔷 Core (5 migrations)

Laravel framework essentials: user authentication, password resets, failed jobs queue, and personal access tokens for API authentication.

### 🔷 Admissions (8 migrations)

Complete student admission lifecycle from initial registration through application submission, fee payment, program selection, and final admission processing.

### 🔷 Academic (30 migrations)

Comprehensive academic structure including programs, courses, subjects, syllabus management, learning outcomes (PO/CO/CSO), course offerings, and student registrations.

### 🔷 Faculty (26 migrations)

Faculty lifecycle management including master records, subject assignments, work diaries, class substitutions, leave applications, salary structure, payroll processing, loans, and remuneration.

### 🔷 Students (13 migrations)

Student master data, enrollment tracking, regular and extra class attendance (including QR-based), fee payments, and late fee exemptions.

### 🔷 Exams (9 migrations)

Examination management covering exam schedules, student registrations, subject-wise exam enrollment, internal marks entry, assessment logs, and question bank management.

### 🔷 Fees (15 migrations)

Fee structure framework with program-wise configuration, fee heads breakdown, structure grouping, late fee calculation, and college bank account integration.

### 🔷 Infrastructure (5 migrations)

Physical campus management including campus master data, academic blocks, lecture halls, classroom allocation, and user-campus associations.

### 🔷 Masters (9 migrations)

Lookup tables and master data: countries, nationalities, religions, weekdays, hour periods, cognitive levels (Bloom's taxonomy), paper types, teaching methodologies, and payment gateways.

### 🔷 Permissions (10 migrations)

Role-based access control (RBAC) system with permission definitions, role assignments, user-permission mappings, menu access control, and account office specific permissions.

### 🔷 System (8 migrations)

System utilities including error logs, user activity tracking, SMS logs and templates, OTP generation/verification, failed transaction logs, and admin notifications.

### 🔷 Event Coordinator (10 migrations)

Event and program management with event master records, program details, multi-college participation, faculty duty assignments, participant registration, fund transactions, and sponsorships.

### 🔷 HR (7 migrations)

Human Resources module covering Faculty Development Programs (FDP), FDP participant tracking, job vacancy management, recruitment applications, pay matrix (salary structure templates), and payroll integration.

## Benefits of This Organization

✅ **Improved Navigation** - Quickly locate migrations by module
✅ **Better Maintainability** - Clear separation of concerns
✅ **Easier Onboarding** - New developers can understand structure faster
✅ **Modular Management** - Run/rollback specific modules independently
✅ **Reduced Conflicts** - Team members work on different folders
✅ **Clear Documentation** - Each folder is self-documented
✅ **Scalability** - Easy to add new modules without cluttering

## Development Workflow

### Adding New Migrations

1. Determine which module your migration belongs to
2. Create migration in the appropriate folder:
    ```bash
    php artisan make:migration create_example_table --path=database/migrations/academic
    ```
3. Update the folder's README.md if adding significant new tables

### For New Modules

1. Create new folder: `mkdir database/migrations/module_name`
2. Add README.md documenting the module
3. Create migrations in that folder
4. Update main README.md to include the new module

## Testing Organization

Verification completed:

- ✅ All 155 migration files successfully moved
- ✅ No migrations remaining in root migrations folder
- ✅ Laravel recognizes all migrations: `php artisan migrate` returns "Nothing to migrate"
- ✅ 116 migrations tracked in database
- ✅ All folder-specific status checks working
- ✅ 13 README files created (1 main + 1 per folder)
- ✅ Helper scripts created (check-migrations.sh)
- ✅ Documentation files created (MIGRATION_SUMMARY.txt, FOLDER_STRUCTURE.txt)

## Related Documentation

- [Main Migration README](./README.md)
- [HR Module Documentation](../../readme/HR_MODULE_DOCUMENTATION.md)
- [HR Payroll Documentation](../../readme/HR_PAYROLL_MATRIX_DOCUMENTATION.md)
- [COE Exam Modules](../../readme/COE_EXAM_MODULES_SUMMARY.md)
- [Attendance Module](../../readme/ATTENDANCE_MODULE_DOCUMENTATION.md)
- [Payroll System](../../readme/PAYROLL_SYSTEM_DOCUMENTATION.md)

## Support & Troubleshooting

### Migration Not Found

If Laravel can't find a migration, specify the path:

```bash
php artisan migrate --path=database/migrations/folder_name
```

### Check What's Been Run

```bash
php artisan tinker --execute="DB::table('migrations')->get()->pluck('migration')"
```

### Fresh Start (Development Only)

```bash
php artisan migrate:fresh --seed
```

---

**Last Updated:** May 27, 2026
**Total Migrations:** 155 files across 13 folders
**Status:** ✅ Fully Organized and Documented
