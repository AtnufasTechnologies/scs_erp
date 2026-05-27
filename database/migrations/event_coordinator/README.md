# Event Coordinator Migrations

This folder contains all migrations related to the Event Coordinator module for managing college events and programs.

## Tables Created

### Event Management

- `ec_events` - Event master records
- `ec_programs` - Program details within events
- `ec_program_colleges` - Multi-college program participation

### Faculty & Participants

- `ec_faculty_duties` - Faculty duty assignments for events
- `ec_program_participants` - Event participant registration

### Financial Management

- `ec_fund_transactions` - Event fund transactions
- `ec_sponsors` - Event sponsors and sponsorship details

## Purpose

Manages college events, programs, faculty duties, participant registration, and event financial tracking including sponsorships.

## Running Migrations

```bash
php artisan migrate --path=database/migrations/event_coordinator
```
