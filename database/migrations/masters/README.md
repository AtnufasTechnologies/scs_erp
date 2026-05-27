# Masters Migrations

This folder contains all migrations for master data and lookup tables.

## Tables Created

### Geographic & Personal

- `countries` - Country master data
- `nationality_masters` - Nationality listings
- `religion_masters` - Religion master data

### Academic

- `weekdays` - Weekday master (Mon-Sun)
- `hour_masters` - Hour/period master for timetable
- `cognitive_level_masters` - Bloom's taxonomy cognitive levels
- `paper_type_masters` - Exam paper type classifications
- `methodology_masters` - Teaching methodology types

### System

- `payment_gateway_types` - Payment gateway configurations

## Purpose

Provides lookup tables and master data used across the entire application for consistent data entry and reporting.

## Running Migrations

```bash
php artisan migrate --path=database/migrations/masters
```
