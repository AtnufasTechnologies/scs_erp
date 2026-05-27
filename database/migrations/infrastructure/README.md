# Infrastructure Migrations

This folder contains all migrations related to campus infrastructure and physical facilities.

## Tables Created

- `campuses` - Campus master data
- `academic_blocks` - Academic building blocks
- `lecture_hall_masters` - Lecture hall/classroom details
- `room_masters` - Room allocation and details
- `user_campus_settings` - User-campus associations

## Purpose

Manages physical infrastructure including campuses, buildings, classrooms, and room allocation for academic activities.

## Running Migrations

```bash
php artisan migrate --path=database/migrations/infrastructure
```
