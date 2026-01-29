# Late Fee Exemption System - Usage Guide

## Overview

The late fee exemption system allows specific students to be exempted from paying late fees on their fee structures.

## Database Table Created

- **Table**: `student_late_fee_exemptions`
- **Columns**:
    - `student_id` - The student who gets the exemption
    - `fee_structure_id` - Specific quarter/fee structure (NULL = all fees)
    - `reason` - Why the exemption was granted
    - `approved_by` - Admin user who approved
    - `is_active` - Whether exemption is currently active

## How It Works

### 1. Blanket Exemption (All Fees)

To exempt a student from ALL late fees:

```sql
INSERT INTO student_late_fee_exemptions
(student_id, fee_structure_id, reason, approved_by, approved_at, is_active)
VALUES
(123, NULL, 'Financial hardship', 1, NOW(), 1);
```

### 2. Specific Fee Structure Exemption

To exempt a student from late fees on a specific quarter:

```sql
INSERT INTO student_late_fee_exemptions
(student_id, fee_structure_id, reason, approved_by, approved_at, is_active)
VALUES
(123, 456, 'Medical emergency during payment period', 1, NOW(), 1);
```

## API Endpoints Added

### Grant Exemption

```php
POST /admin/late-fee-exemption/grant
Parameters:
- student_id (required)
- fee_structure_id (optional, null for blanket exemption)
- reason (required)
```

### View All Exemptions

```php
GET /admin/late-fee-exemptions
```

### Revoke Exemption

```php
POST /admin/late-fee-exemption/{id}/revoke
```

## Controller Methods Added

1. **lateFeeExemptionIndex()** - Lists all exemptions
2. **grantLateFeeExemption()** - Creates/updates exemption
3. **revokeLateFeeExemption()** - Deactivates exemption

## Changes Made to Existing Code

### studentFeeStatus() Method

- Now checks for exemptions before calculating late fees
- Returns `is_late_fee_exempted` flag in response
- Status shows "DUE (Late Fee Exempted)" for exempted students

### createOrder() Method

- Checks exemptions before creating payment records
- Only charges late fees to non-exempted students
- Late fee is 0 for exempted students even if payment is late

## Example Routes (Add to web.php)

```php
Route::middleware(['auth', 'admin'])->prefix('admin')->group(function () {
    Route::get('/late-fee-exemptions', [FeePaymentController::class, 'lateFeeExemptionIndex'])
        ->name('admin.late-fee-exemptions');

    Route::post('/late-fee-exemption/grant', [FeePaymentController::class, 'grantLateFeeExemption'])
        ->name('admin.grant-late-fee-exemption');

    Route::post('/late-fee-exemption/{id}/revoke', [FeePaymentController::class, 'revokeLateFeeExemption'])
        ->name('admin.revoke-late-fee-exemption');
});
```

## Testing

### Test Blanket Exemption

```php
// Grant blanket exemption
StudentLateFeeExemption::create([
    'student_id' => 123,
    'fee_structure_id' => null,  // NULL = all fees
    'reason' => 'Test exemption',
    'approved_by' => 1,
    'approved_at' => now(),
    'is_active' => true
]);

// Check student fee status
// Late fees should be 0 for all unpaid fees
```

### Test Specific Exemption

```php
// Grant exemption for specific quarter
StudentLateFeeExemption::create([
    'student_id' => 123,
    'fee_structure_id' => 456,  // Specific quarter
    'reason' => 'Test exemption',
    'approved_by' => 1,
    'approved_at' => now(),
    'is_active' => true
]);

// Check student fee status
// Late fee should be 0 only for fee_structure_id = 456
```

## Important Notes

1. **Blanket exemptions take precedence** - If a student has a blanket exemption (fee_structure_id = NULL), they are exempt from ALL late fees
2. **Specific exemptions** - Only apply to the specific fee structure
3. **Active status** - Only active exemptions (is_active = true) are considered
4. **Unique constraint** - Each student can have only one exemption per fee structure
5. **Cascade delete** - If student or fee structure is deleted, exemptions are automatically removed

## Response Format

The `studentFeeStatus()` method now returns:

```json
{
    "fee_structure_id": 456,
    "fee_structure_name": "Quarter 1 - 2024",
    "base_amount": 50000,
    "late_days": 15,
    "late_fee": 0,
    "is_late_fee_exempted": true,
    "total_payable": 50000,
    "status": "DUE (Late Fee Exempted)"
}
```
