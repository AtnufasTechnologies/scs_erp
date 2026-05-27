# Exams Migrations

This folder contains all migrations related to examination management and assessment.

## Tables Created

### Exam Management

- `exams` - Exam schedule and details
- `exam_registrations` - Student exam registrations
- `exam_registration_subjects` - Subject-wise exam registrations

### Assessment

- `internal_marks` - Internal assessment marks
- `internal_mark_logs` - Internal marks change logs

### Question Banks

- `question_banks` - Question bank master
- `question_bank` - Question bank items

### COE Permissions

- `dcoe_menu_permissions` - Deputy COE menu access permissions

## Purpose

Manages examination lifecycle including exam creation, student registration, internal assessments, and question bank management.

## Running Migrations

```bash
php artisan migrate --path=database/migrations/exams
```
