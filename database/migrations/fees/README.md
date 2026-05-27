# Fees Migrations

This folder contains all migrations related to fee structure and financial management.

## Tables Created

### Fee Structure

- `fees_structures` - Fee structure master
- `fee_structure_groups` - Fee structure grouping
- `fee_structure_has_heads` - Fee head breakdown
- `fee_structure_has_many_programs` - Program-wise fee mapping
- `fee_heads` - Fee head master data
- `fee_course_masters` - Course-specific fees

### Late Fees

- `late_fees` - Late fee configuration

### Banking

- `college_bank_accounts` - College bank account details

## Purpose

Manages fee structure definition, program-wise fee configuration, late fee calculation, and banking integration.

## Running Migrations

```bash
php artisan migrate --path=database/migrations/fees
```

## Related

- Student payments are managed in the `students` folder
- Admission fees are managed in the `admissions` folder
