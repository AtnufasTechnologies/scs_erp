# Admissions Migrations

This folder contains all migrations related to the student admission process.

## Tables Created

- `admission_registrations` - Initial student registration
- `admission_applications` - Detailed admission applications
- `admission_application_fees` - Fee payments for applications
- `admission_application_payment_logs` - Payment transaction logs
- `admission_first_phases` - First phase admission data
- `admission_final_phases` - Final phase admission data
- `admission_settings` - Admission configuration settings
- `applicant_program_change_infos` - Program change requests

## Purpose

Manages the complete student admission lifecycle from initial registration through application submission, fee payment, and final admission processing.

## Running Migrations

```bash
php artisan migrate --path=database/migrations/admissions
```
