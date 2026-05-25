# Fee Structure Program Group to Student Program Migration - Summary

**Date:** May 14, 2026
**Issue:** Student fee payment getting mixed up due to program groups. Some student programs don't have a program group, causing fee matching failures.

## Problem Statement

The fee structure system was using `program_group_id` to link fee courses to programs, but:

1. Some student programs don't have a program group assigned
2. When matching fees for students, the system compares `student->new_program_id` (student_program_id) with `std_program_id` in the pivot table
3. The pivot table was storing `program_group_id` instead of `student_program_id`, causing mismatches

## Solution

Changed the fee structure system to link directly to `student_program` IDs instead of `program_group` IDs.

## Changes Made

### 1. Database Migrations (Run These First!)

Created two migration files that must be run before the code changes take effect:

#### Migration 1: `2026_05_14_000001_convert_fee_structure_program_group_to_student_program.php`

- Converts existing data in `fee_structure_has_many_programs` table
- Changes `std_program_id` from program_group_id to actual student_program_id
- Looks up program_group table to find the correct student_program_id
- Includes rollback capability

#### Migration 2: `2026_05_14_000002_convert_fee_structure_groups_to_student_program.php`

- Updates `fee_structure_groups` table structure
- Adds `student_program_id` column
- Converts existing data from program_group_id to student_program_id
- Drops the old `program_group_id` column
- Includes rollback capability

**⚠️ IMPORTANT: Run these migrations in order:**

```bash
php artisan migrate
```

### 2. Model Updates

#### FeeStructureGroup.php

- **REMOVED:** `programgroupinfo()` relationship (was linking to ProgramGroup)
- **KEPT:** `programinfo()` relationship (links to StudentProgram via student_program_id)
- **ADDED:** `fillable` array with `fee_course_master_id` and `student_program_id`

#### FeeStructureHasManyProgram.php

- **REMOVED:** `programgroupinfo()` relationship (was linking to ProgramGroup)
- **KEPT:** `studentprogram()` relationship (links to StudentProgram via std_program_id)
- Now uses only `studentprogram` for all program lookups

### 3. Controller Updates

#### AdminController.php

**Method: `addFeeStructure()` (around line 1003)**

- Changed from: `$progs[$i]->program_group_id`
- Changed to: `$progs[$i]->student_program_id`
- Updated comment from "connect course group" to "connect course student programs"

**Method: `addCourseMasterGroup()` (around line 1109)**

- Now accepts student_program IDs directly instead of program_group IDs
- Changed: `FeeStructureGroup` now stores `student_program_id` instead of `program_group_id`
- Removed: Logic to convert program_group_id to student_program_id (no longer needed)
- Direct usage: `$std_prg_id = $progs[$i]` instead of looking up from ProgramGroup

**Method: `linkProgramtoFeeStructure()` (around line 1159)**

- Added duplicate check before inserting
- Updated success message from "Programs Group Linked" to "Student Programs Linked"
- Now expects student_program IDs directly in the request

#### StaticController.php

**Method: `fetchProgramGroupNew()`**

- Changed from: Fetching `ProgramGroup::with(['programInfo', 'campus'])`
- Changed to: Fetching `StudentProgram::with(['campusmaster'])`
- Now returns student programs directly instead of program groups

**Method: `fetchCourseMasterGroups($id)`**

- Changed eager loading from: `'programInfo', 'programInfo.campus'`
- Changed to: `'programinfo', 'programinfo.campusmaster'`
- Relationship name changed to match the updated model

#### TestController.php

- Updated migration script to use `student_program_id` directly
- Changed from: `programgroupinfo->program_id`
- Changed to: `student_program_id`

### 4. View Updates

#### fee-course-master.blade.php

**Header Section:**

- Changed text from "linked program groups" to "linked student programs"

**Table Headers:**

- Changed from "Program Groups" to "Student Programs"

**Modal Titles:**

- "Linked Program Groups" → "Linked Student Programs"
- "Link Program Groups" → "Link Student Programs"

**Program Display:**

- Changed from: `$s->programgroupinfo->programInfo->code`
- Changed to: `$s->programinfo->code`
- Changed from: `$s->programgroupinfo->campus->name`
- Changed to: `$s->programinfo->campusmaster->name`

**Program Selection Dropdown:**

- Now shows student programs directly
- Changed from: `$p->programInfo->code` and `$p->campus->name`
- Changed to: `$p->code` and `$p->campusmaster->name`

**Button Text:**

- "Link Groups" → "Link Programs"

#### fee-structure.blade.php

**Linked Programs Display:**

- Changed from: `$s->programgroupinfo->programInfo->code`
- Changed to: `$s->studentprogram->code`

## How to Deploy

### Step 1: Backup Database

```bash
# Create a backup before running migrations
mysqldump -u username -p database_name > backup_before_fee_fix_$(date +%Y%m%d).sql
```

### Step 2: Run Migrations

```bash
php artisan migrate
```

The migrations will automatically:

1. Convert existing program_group_ids to student_program_ids
2. Update table structures
3. Print progress messages showing what's being converted

### Step 3: Clear Caches

```bash
php artisan config:clear
php artisan cache:clear
php artisan view:clear
```

### Step 4: Test

**Test Scenarios:**

1. **View Existing Fee Courses**
    - Go to `/erp/admin/accounts/fee-course-master`
    - Verify that linked programs show correctly
    - Check that program names and campus names display properly

2. **Link New Programs to Course**
    - Click "Link Programs" on any fee course
    - Verify dropdown shows student programs (not program groups)
    - Select programs and link them
    - Confirm they appear in the "Linked Student Programs" list

3. **Create New Fee Structure**
    - Create a new fee structure
    - Verify it automatically links to the correct student programs based on the course

4. **Student Fee View**
    - Log in as a student (especially one without a program_group assigned)
    - Go to `/erp/student/fee-status`
    - Verify fees display correctly
    - Test with student ID that previously had issues

5. **Admin Fee Collection**
    - Search for student in fee collection module
    - Verify correct fees show up for the student's program

## Verification Queries

```sql
-- Check fee_structure_has_many_programs data
SELECT
    fshmp.id,
    fshmp.std_program_id,
    sp.code as program_code,
    sp.name as program_name
FROM fee_structure_has_many_programs fshmp
JOIN student_program sp ON sp.id = fshmp.std_program_id
WHERE fshmp.deleted_at IS NULL
LIMIT 10;

-- Check fee_structure_groups data
SELECT
    fsg.id,
    fsg.fee_course_master_id,
    fsg.student_program_id,
    sp.code as program_code,
    sp.name as program_name
FROM fee_structure_groups fsg
JOIN student_program sp ON sp.id = fsg.student_program_id
WHERE fsg.deleted_at IS NULL
LIMIT 10;

-- Verify students can match their fees
SELECT
    sm.id,
    sm.name,
    sm.new_program_id,
    sp.code as program_code,
    COUNT(DISTINCT fshmp.fee_structure_id) as matching_fee_structures
FROM student_masters sm
JOIN student_program sp ON sp.id = sm.new_program_id
LEFT JOIN fee_structure_has_many_programs fshmp ON fshmp.std_program_id = sm.new_program_id
WHERE sm.new_program_id IS NOT NULL
GROUP BY sm.id, sm.name, sm.new_program_id, sp.code
HAVING matching_fee_structures = 0
LIMIT 10;
-- This should return 0 rows if all students have matching fee structures
```

## Rollback Instructions

If something goes wrong, you can rollback:

```bash
php artisan migrate:rollback --step=2
```

This will:

1. Revert `fee_structure_groups` back to using `program_group_id`
2. Revert `fee_structure_has_many_programs` back to storing program_group_ids
3. Restore the database structure to its previous state

**Note:** You'll also need to revert the code changes manually via git:

```bash
git checkout HEAD -- app/Models/FeeStructureGroup.php
git checkout HEAD -- app/Models/FeeStructureHasManyProgram.php
git checkout HEAD -- app/Http/Controllers/AdminController.php
git checkout HEAD -- app/Http/Controllers/StaticController.php
git checkout HEAD -- app/Http/Controllers/TestController.php
git checkout HEAD -- resources/views/admin/accounts/fee-course-master.blade.php
git checkout HEAD -- resources/views/admin/accounts/fee-structure.blade.php
```

## Impact Analysis

### ✅ What Won't Be Affected

- Existing fee payment records
- Student data
- Fee structures themselves (amounts, heads, dates)
- Program group definitions (they still exist, just not used for fee matching)

### ⚠️ What Will Change

- How programs are linked to fee courses (now direct student_program link)
- Display text in admin interface (shows "Student Programs" instead of "Program Groups")
- Program selection dropdown (shows student programs directly)

### 🎯 What Will Be Fixed

- Students without program_groups will now see their fees correctly
- Fee matching will be consistent and accurate
- No more confusion between program_group_id and student_program_id

## Future Recommendations

1. **Audit Student Programs:** Ensure all students have `new_program_id` set correctly
2. **Consider Deprecating Program Groups:** If not used elsewhere, consider removing the program_group concept entirely
3. **Add Validation:** Add database constraints and application validation to ensure student_program_id is always set
4. **Monitor Fee Matching:** Track students who don't see fees to identify any remaining issues

## Files Modified

1. `/database/migrations/2026_05_14_000001_convert_fee_structure_program_group_to_student_program.php` (NEW)
2. `/database/migrations/2026_05_14_000002_convert_fee_structure_groups_to_student_program.php` (NEW)
3. `/app/Models/FeeStructureGroup.php`
4. `/app/Models/FeeStructureHasManyProgram.php`
5. `/app/Http/Controllers/AdminController.php`
6. `/app/Http/Controllers/StaticController.php`
7. `/app/Http/Controllers/TestController.php`
8. `/resources/views/admin/accounts/fee-course-master.blade.php`
9. `/resources/views/admin/accounts/fee-structure.blade.php`

---

**Status:** ✅ Code Changes Complete - Ready for Migration
**Next Step:** Run migrations and test thoroughly

**Prepared by:** GitHub Copilot
**Date:** May 14, 2026
