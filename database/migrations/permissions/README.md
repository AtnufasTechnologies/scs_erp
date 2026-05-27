# Permissions Migrations

This folder contains all migrations related to user permissions, roles, and access control.

## Tables Created

### Permission System

- `permission_masters` - Permission definitions
- `user_has_permissions` - User-permission assignments
- `user_has_roles` - User-role assignments
- `role_masters` - Role definitions
- `account_office_permissions` - Account office specific permissions

### Menu & Access Control

- `menu_masters` - Menu structure and items
- `user_menu_permissions` - User menu access control

### Legacy Tables

- `permission_tables` - Spatie permission package tables (deprecated)

## Purpose

Manages role-based access control (RBAC), permission assignments, and menu-level access restrictions across the application.

## Running Migrations

```bash
php artisan migrate --path=database/migrations/permissions
```
