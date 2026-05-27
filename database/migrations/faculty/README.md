# Faculty Migrations

This folder contains all migrations related to faculty management, work tracking, and payroll.

## Tables Created

### Faculty Master Data

- `faculties` - Faculty master records
- `subject_faculty_pivots` - Faculty-subject assignments
- `subject_faculty_masters` - Subject-faculty relationships
- `faculty_login_pivots` - Faculty login credentials

### Work Management

- `work_diaries` - Faculty work diary entries
- `faculty_holidays` - Faculty holiday records
- `faculty_substitutions` - Class substitution records

### Leave Management

- `leave_masters` - Leave type master data
- `faculty_leave_applications` - Faculty leave applications

### Payroll & Salary

- `faculty_salary_masters` - Faculty salary structure
- `faculty_salary_slips` - Monthly salary slips
- `faculty_loans` - Faculty loan records
- `faculty_remunerations` - Additional remuneration payments
- `remuneration_rates` - Remuneration rate master
- `payment_batches` - Payment batch processing
- `payment_batch_items` - Payment batch line items

### Analytics

- `faculty_cso_analytics` - Faculty CSO analytics
- `cso_lecture_logs` - CSO lecture tracking

## Purpose

Manages faculty lifecycle including master data, work tracking, leave management, salary processing, and performance analytics.

## Running Migrations

```bash
php artisan migrate --path=database/migrations/faculty
```
