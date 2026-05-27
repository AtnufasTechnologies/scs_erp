# Core Migrations

This folder contains Laravel core authentication and system tables.

## Tables Created

- `users` - User accounts and authentication
- `password_resets` / `password_reset_tokens` - Password reset functionality
- `failed_jobs` - Queue failed jobs tracking
- `personal_access_tokens` - API token authentication (Sanctum)

## Purpose

These are the foundational Laravel framework tables required for basic application functionality including authentication, password resets, and API access.

## Running Migrations

```bash
php artisan migrate --path=database/migrations/core
```
