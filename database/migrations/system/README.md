# System Migrations

This folder contains all migrations for system utilities, logs, and supporting features.

## Tables Created

### Logging & Monitoring

- `error_logs` - Application error logging
- `user_activity_logs` - User activity tracking
- `sms_logs` - SMS delivery logs
- `failed_transaction_logs` - Failed payment transaction logs

### Communication

- `sms_templates` - SMS template management
- `otps` - OTP (One-Time Password) generation and verification
- `admin_notifies` - Admin notification system

### Miscellaneous

- `quotes` - Inspirational quotes or announcements

## Purpose

Provides system-level utilities for logging, monitoring, communication, and application support features.

## Running Migrations

```bash
php artisan migrate --path=database/migrations/system
```
