# Database Migrations

All database migrations are organized into logical folders for better maintainability and navigation.

## Folder Structure

### 📁 **core** (5 migrations)

Laravel core framework tables for authentication and system functionality.

- Users, passwords, failed jobs, API tokens

### 📁 **admissions** (8 migrations)

Complete student admission process management.

- Registration, applications, fees, payment logs, admission phases

### 📁 **academic** (30 migrations)

Academic structure and curriculum management.

- Programs, courses, subjects, syllabus, learning outcomes, student registrations

### 📁 **faculty** (26 migrations)

Faculty lifecycle and payroll management.

- Master data, work diaries, leave, salary, loans, remuneration

### 📁 **students** (13 migrations)

Student management and tracking.

- Master records, enrollment, attendance, payments

### 📁 **exams** (9 migrations)

Examination and assessment management.

- Exam schedules, registrations, internal marks, question banks

### 📁 **fees** (15 migrations)

Fee structure and financial management.

- Fee structures, heads, late fees, banking

### 📁 **infrastructure** (5 migrations)

Campus infrastructure and facilities.

- Campuses, buildings, classrooms, rooms

### 📁 **masters** (9 migrations)

Master data and lookup tables.

- Countries, religions, weekdays, cognitive levels, paper types

### 📁 **permissions** (10 migrations)

User permissions and access control.

- Permissions, roles, menu access, RBAC

### 📁 **system** (8 migrations)

System utilities and logging.

- Error logs, activity logs, SMS, OTP, notifications

### 📁 **event_coordinator** (10 migrations)

Event and program management.

- Events, programs, faculty duties, participants, sponsorships, fund transactions

### 📁 **hr** (7 migrations)

Human Resources module.

- FDP programs, vacancies, recruitment, pay matrix, payroll

---

**Total:** 155 migration files across 13 organized folders

## Running Migrations

### Run All Migrations

```bash
php artisan migrate
```

### Run Specific Folder

```bash
# Run only core migrations
php artisan migrate --path=database/migrations/core

# Run only HR migrations
php artisan migrate --path=database/migrations/hr

# Run only academic migrations
php artisan migrate --path=database/migrations/academic
```

### Check Migration Status

```bash
# All migrations
php artisan migrate:status

# Specific folder
php artisan migrate:status --path=database/migrations/faculty
```

### Rollback Migrations

```bash
# Rollback last batch
php artisan migrate:rollback

# Rollback specific folder
php artisan migrate:rollback --path=database/migrations/exams

# Rollback all migrations
php artisan migrate:reset
```

## Fresh Installation

For a fresh installation, migrations will run in the correct order automatically:

```bash
php artisan migrate:fresh
```

This will drop all tables and re-run all migrations from all folders.

## Development Guidelines

1. **Create new migrations in the appropriate folder** based on the module/feature
2. **Use descriptive names** that clearly indicate the table or change being made
3. **Document complex migrations** with comments explaining the purpose
4. **Test rollback functionality** to ensure migrations are reversible
5. **Update README** in the respective folder when adding new migrations

## Module-Specific Documentation

Each folder contains its own README.md with detailed information about:

- Tables created
- Purpose and scope
- Specific running instructions
- Related modules

See individual folder READMEs for more details.

## Notes

- All migrations maintain timestamp-based ordering for proper execution sequence
- Foreign key constraints may be handled at application level in some cases for legacy compatibility
- Some tables use soft deletes for data retention
- Indexes are created for performance optimization on frequently queried columns

## Support

For migration-related issues or questions, refer to:

- Laravel Migration Documentation: https://laravel.com/docs/migrations
- Project-specific documentation in `/readme` folder
