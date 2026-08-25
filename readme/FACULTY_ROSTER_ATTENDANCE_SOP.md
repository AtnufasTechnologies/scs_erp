# Faculty SOP: Course Roster and Attendance Workflow

## 1. Purpose

This SOP defines the mandatory process for faculty to prepare, validate, and maintain student course rosters before taking attendance, so that student counts remain consistent across:

1. Faculty Subjects
2. Student Course Roster
3. Attendance

## 2. Scope

Applicable to all faculty users taking attendance through ERP for assigned routines, batches, and semesters.

## 3. Key Rule

Attendance may show eligible students through rule-based resolution even when roster rows are not saved. Therefore, faculty must finalize and save roster records before taking regular attendance.

## 4. Roles and Responsibility

1. Faculty
   Complete roster preparation and validation for each assigned course context.
2. HOD
   Review weekly compliance and exceptions.
3. IT Cell
   Support only technical defects, not missed faculty operational steps.

## 5. Standard Process

### Step A: Open Assigned Course Context

1. Go to Faculty -> Student Course Roster.
2. Select the intended routine/course (correct course code, semester, batch, shift).

### Step B: Build or Verify Roster

1. Open Add More Students.
2. Add or copy all eligible students for that exact routine/course assignment.
3. Save roster.
4. If View page shows Resolved rows, use the one-click Save Resolved Students button (above the search box) to persist all resolved students instantly.

### Step C: Validate Roster Persistence

1. Click View.
2. Confirm students appear as normal roster rows.
3. Confirm Action column shows Remove, not Resolved.

### Step D: Start Attendance

1. Open Faculty Attendance.
2. Verify selected routine, batch, semester, and course match roster context.
3. Generate QR or mark attendance only after Step C is complete.

## 6. Mandatory Pre-Attendance Checklist

Faculty must confirm all of the following:

1. Course code is correct.
2. Semester is correct.
3. Batch is correct.
4. Roster View page is not empty.
5. No unresolved fallback-only state for active classes.

## 7. Handling "Resolved" Rows

If View page shows Resolved entries:

1. This means students are currently loaded via eligibility fallback, not saved roster rows.
2. Use Save Resolved Students (one click) from the View Roster page to persist all resolved students into student_course_rosters.
3. Alternative: Use Add More Students if manual selection is needed.
4. Reopen View and confirm regular rows appear with Remove action.

## 8. Known Mismatch Pattern and Root Cause

Pattern:

1. Student count appears in Attendance or roster summary.
2. View page appears empty or shows only Resolved entries.

Root cause:

1. Faculty did not persist roster rows for that routine/course assignment scope.
2. System fallback resolved students dynamically for attendance display.

## 9. Escalation Matrix

1. Faculty issue (missed save/finalization step)
   Owner: Faculty, reviewed by HOD.
2. Data mismatch after roster is saved and verified
   Owner: IT Cell.
3. Access/scope mismatch (wrong faculty mapping/routine access)
   Owner: Admin + IT Cell.

## 10. Compliance and Audit

HOD should verify weekly:

1. Courses where attendance was taken but roster was not persisted in advance.
2. Faculty-wise exceptions and repeat non-compliance.
3. Corrective actions recorded per incident.

## 11. Quick SOP Summary

1. Open course context.
2. Add/Save roster (or one-click Save Resolved Students when resolved fallback is shown).
3. Validate in View page.
4. Then take attendance.
