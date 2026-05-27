# HR Module Migrations

This folder contains all database migrations related to the HR (Human Resources) module.

## Included Migrations

1. **FDP Programs** - Faculty Development Program management
    - `2026_05_27_000001_create_hr_fdp_programs_table.php`
    - `2026_05_27_000002_create_hr_fdp_participants_table.php`

2. **Vacancies & Recruitment** - Job vacancy and application management
    - `2026_05_27_000003_create_hr_vacancies_table.php`
    - `2026_05_27_000004_create_hr_vacancy_applications_table.php`

3. **Faculty Enhancements** - HR fields for faculty
    - `2026_05_27_000005_add_hr_fields_to_faculties_table.php`

4. **Pay Matrix & Payroll** - Salary structure and payroll management
    - `2026_05_27_100000_create_hr_pay_matrix_table.php`
    - `2026_05_27_100001_add_pay_matrix_to_faculty_salary_masters.php`

5. **Designation & Grade Level Masters** - Academic and administrative position management
    - `2026_05_27_120827_create_hr_designations_table.php` - Designation master (24 positions seeded)
    - `2026_05_27_120827_create_hr_grade_levels_table.php` - Grade level master (L1-L14 pay scales)
    - `2026_05_27_120902_add_designation_and_grade_to_faculties_table.php` - Links faculties to designations
    - `2026_05_27_121429_add_designation_and_grade_foreign_keys_to_pay_matrices_table.php` - Links pay matrix to masters

6. **Promotion Tracking** - Faculty promotion history (disabled - see .bak file)
    - Tracks designation/grade/pay matrix changes over time
    - Maintains complete promotion history for auditing

## Running Migrations

To run these migrations:

```bash
# Run all pending migrations (including HR)
php artisan migrate

# Run HR migrations specifically
php artisan migrate --path=database/migrations/hr

# Check HR migration status
php artisan migrate:status --path=database/migrations/hr

# Rollback HR migrations
php artisan migrate:rollback --path=database/migrations/hr
```

## Tables Created

- `hr_fdp_programs` - FDP program master
- `hr_fdp_participants` - Faculty participation in FDP programs
- `hr_vacancies` - Job vacancy postings
- `hr_vacancy_applications` - Candidate applications
- `hr_pay_matrix` - Standardized salary structure templates
- Enhanced `faculties` table with HR fields
- Enhanced `faculty_salary_masters` table with pay matrix reference

## Documentation

For complete module documentation, see:

- [HR Module Documentation](../../../readme/HR_MODULE_DOCUMENTATION.md)
- [HR Payroll & Pay Matrix Documentation](../../../readme/HR_PAYROLL_MATRIX_DOCUMENTATION.md)
