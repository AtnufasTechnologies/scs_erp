<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

/**
 * DEVELOPER TEST SEEDER - Populates all exam module tables with dummy data.
 *
 * Run: php artisan db:seed --class=ExamModuleTestSeeder
 * Rollback: php artisan db:seed --class=ExamModuleTestSeeder -- --rollback
 *          OR: DELETE FROM exam_sessions WHERE name LIKE '%[TEST]%';
 *
 * All test records are tagged with '[TEST]' in name/remarks fields for easy cleanup.
 */
class ExamModuleTestSeeder extends Seeder
{
  // Tag for identifying test data
  private const TAG = '[TEST]';

  public function run(): void
  {
    $this->command->info('🔧 Seeding Examination Module with test data...');

    // Use real student IDs from student_masters
    $studentIds = DB::table('student_masters')
      ->where('department', 1)
      ->orderBy('id')
      ->limit(30)
      ->pluck('id')
      ->toArray();

    if (count($studentIds) < 10) {
      $this->command->error('Need at least 10 students in student_masters (dept=1). Found: ' . count($studentIds));
      return;
    }

    // Use real faculty IDs
    $facultyIds = DB::table('faculties')
      ->orderBy('id')
      ->limit(10)
      ->pluck('id')
      ->toArray();

    if (count($facultyIds) < 5) {
      $this->command->error('Need at least 5 faculties. Found: ' . count($facultyIds));
      return;
    }

    // Use real user IDs
    $userIds = DB::table('users')->orderBy('id')->limit(5)->pluck('id')->toArray();
    $adminUserId = $userIds[0] ?? 1;

    // Use real subject IDs
    $subjectIds = DB::table('subjects')
      ->whereNull('deleted_at')
      ->orderBy('id')
      ->limit(8)
      ->pluck('id')
      ->toArray();

    $now = Carbon::now();

    // ──────────────────────────────────────────────────────────────
    // 1. ROOMS
    // ──────────────────────────────────────────────────────────────
    $this->command->info('  → Rooms...');
    $roomIds = [];
    $rooms = [
      ['room_no' => 'A-101 ' . self::TAG, 'capacity' => 40, 'location' => 'Block A, Floor 1'],
      ['room_no' => 'A-102 ' . self::TAG, 'capacity' => 35, 'location' => 'Block A, Floor 1'],
      ['room_no' => 'A-201 ' . self::TAG, 'capacity' => 50, 'location' => 'Block A, Floor 2'],
      ['room_no' => 'B-101 ' . self::TAG, 'capacity' => 60, 'location' => 'Block B, Floor 1'],
      ['room_no' => 'B-201 ' . self::TAG, 'capacity' => 45, 'location' => 'Block B, Floor 2'],
    ];
    foreach ($rooms as $room) {
      $roomIds[] = DB::table('rooms')->insertGetId(array_merge($room, [
        'created_at' => $now,
        'updated_at' => $now,
      ]));
    }

    // ──────────────────────────────────────────────────────────────
    // 2. GRADE MAPPINGS (UG program_id = 1)
    // ──────────────────────────────────────────────────────────────
    $this->command->info('  → Grade Mappings...');
    $grades = [
      ['grade' => 'O',  'min_marks' => 90, 'max_marks' => 100, 'grade_point' => 9.99],
      ['grade' => 'A+', 'min_marks' => 80, 'max_marks' => 89,  'grade_point' => 9.0],
      ['grade' => 'A',  'min_marks' => 70, 'max_marks' => 79,  'grade_point' => 8.0],
      ['grade' => 'B+', 'min_marks' => 60, 'max_marks' => 69,  'grade_point' => 7.0],
      ['grade' => 'B',  'min_marks' => 50, 'max_marks' => 59,  'grade_point' => 6.0],
      ['grade' => 'C',  'min_marks' => 40, 'max_marks' => 49,  'grade_point' => 5.0],
      ['grade' => 'P',  'min_marks' => 33, 'max_marks' => 39,  'grade_point' => 4.0],
      ['grade' => 'F',  'min_marks' => 0,  'max_marks' => 32,  'grade_point' => 0.0],
    ];
    foreach ($grades as $g) {
      DB::table('grade_mappings')->insert(array_merge($g, [
        'program_id' => 1,
        'created_at' => $now,
        'updated_at' => $now,
      ]));
    }

    // ──────────────────────────────────────────────────────────────
    // 3. FACULTY PROFILES
    // ──────────────────────────────────────────────────────────────
    $this->command->info('  → Faculty Profiles...');
    $designations = ['Assistant Professor', 'Associate Professor', 'Professor', 'Guest Lecturer'];
    $facultyProfileIds = [];
    foreach (array_slice($facultyIds, 0, 8) as $i => $fid) {
      $fac = DB::table('faculties')->where('id', $fid)->first();
      $facultyProfileIds[] = DB::table('faculty_profiles')->insertGetId([
        'erp_faculty_id' => $fid,
        'name' => trim(($fac->FIRST_NAME ?? '') . ' ' . ($fac->LAST_NAME ?? '')),
        'department' => $fac->DEPARTMENT ?? 1,
        'designation' => $designations[$i % count($designations)],
        'created_at' => $now,
        'updated_at' => $now,
      ]);
    }

    // ──────────────────────────────────────────────────────────────
    // 4. EXAM SESSIONS (3 sessions: past completed, current ongoing, future)
    // ──────────────────────────────────────────────────────────────
    $this->command->info('  → Exam Sessions...');
    $sessionData = [
      [
        'name' => 'June 2025 End Semester ' . self::TAG,
        'academic_year' => '2024-2025',
        'semester' => 2,
        'program_type' => 'UG',
        'regulation_id' => 1,
        'start_date' => '2025-06-01',
        'end_date' => '2025-06-15',
      ],
      [
        'name' => 'April 2026 Mid Semester ' . self::TAG,
        'academic_year' => '2025-2026',
        'semester' => 4,
        'program_type' => 'UG',
        'regulation_id' => 1,
        'start_date' => '2026-04-01',
        'end_date' => '2026-04-15',
      ],
      [
        'name' => 'June 2026 End Semester ' . self::TAG,
        'academic_year' => '2025-2026',
        'semester' => 4,
        'program_type' => 'UG',
        'regulation_id' => 1,
        'start_date' => '2026-06-01',
        'end_date' => '2026-06-15',
      ],
    ];
    $sessionIds = [];
    foreach ($sessionData as $sd) {
      $sessionIds[] = DB::table('exam_sessions')->insertGetId(array_merge($sd, [
        'created_at' => $now,
        'updated_at' => $now,
      ]));
    }

    // ──────────────────────────────────────────────────────────────
    // 5. EXAMS (linked to sessions)
    // ──────────────────────────────────────────────────────────────
    $this->command->info('  → Exams...');
    $examIds = [];
    $examNames = [
      'End Semester Examination - June 2025 ' . self::TAG,
      'Mid Semester Examination - April 2026 ' . self::TAG,
      'End Semester Examination - June 2026 ' . self::TAG,
    ];
    foreach ($sessionData as $i => $sd) {
      $examIds[] = DB::table('exams')->insertGetId([
        'program_id' => 1,
        'name' => $examNames[$i],
        'exam_date' => $sd['start_date'],
        'exam_type' => ($i == 1) ? 'Mid-Semester' : 'Regular',
        'semester' => ($sd['semester'] % 2 == 0) ? 'Even' : 'Odd',
        'start_date' => $sd['start_date'],
        'end_date' => $sd['end_date'],
        'regulation_id' => 1,
        'status' => ($i == 0) ? 'completed' : 'published',
        'created_at' => $now,
        'updated_at' => $now,
      ]);
    }

    // ──────────────────────────────────────────────────────────────
    // 6. EXAM SUBJECT MASTERS (map real subjects to exam subjects)
    // ──────────────────────────────────────────────────────────────
    $this->command->info('  → Exam Subject Masters...');
    $subjectCodes = ['ENG101', 'EDU201', 'PLS301', 'SOC101', 'SOW201', 'HIS301', 'GEO101', 'MUS201'];
    $subjectNames = [
      'English Language',
      'Foundations of Education',
      'Political Systems',
      'Introduction to Sociology',
      'Social Work Practice',
      'World History',
      'Physical Geography',
      'Music Theory'
    ];
    $subjectCredits = [4, 4, 3, 4, 3, 3, 4, 2];
    $subjectTypes = ['theory', 'theory', 'theory', 'theory', 'practical', 'theory', 'practical', 'practical'];

    $examSubjectMasterIds = [];
    foreach ($subjectIds as $i => $sid) {
      $examSubjectMasterIds[] = DB::table('exam_subject_masters')->insertGetId([
        'erp_subject_id' => $sid,
        'program_id' => 1,
        'subject_code' => $subjectCodes[$i] ?? ('SUB' . ($i + 1) . '01'),
        'name' => $subjectNames[$i] ?? ('Subject ' . ($i + 1)),
        'credits' => $subjectCredits[$i] ?? 3,
        'type' => $subjectTypes[$i] ?? 'theory',
        'created_at' => $now,
        'updated_at' => $now,
      ]);
    }

    // ──────────────────────────────────────────────────────────────
    // 7. EXAM SUBJECTS (per session - link subjects to sessions)
    // ──────────────────────────────────────────────────────────────
    $this->command->info('  → Exam Subjects...');
    $examSubjectIds = [];
    foreach ($sessionIds as $si => $sessId) {
      // 6 subjects per session
      foreach (array_slice($subjectIds, 0, 6) as $j => $sid) {
        $examSubjectIds[] = DB::table('exam_subjects')->insertGetId([
          'erp_subject_id' => $sid,
          'exam_session_id' => $sessId,
          'is_backlog' => ($j >= 5) ? 1 : 0,
          'created_at' => $now,
          'updated_at' => $now,
        ]);
      }
    }

    // ──────────────────────────────────────────────────────────────
    // 8. EXAM SCHEDULES (one per exam/subject combo)
    // ──────────────────────────────────────────────────────────────
    $this->command->info('  → Exam Schedules...');
    $examScheduleIds = [];
    foreach ($examIds as $ei => $examId) {
      $baseDate = Carbon::parse($sessionData[$ei]['start_date']);
      foreach (array_slice($examSubjectMasterIds, 0, 6) as $j => $esmId) {
        $examScheduleIds[] = DB::table('exam_schedules')->insertGetId([
          'exam_id' => $examId,
          'exam_subject_id' => $esmId,
          'exam_date' => $baseDate->copy()->addDays($j)->toDateString(),
          'start_time' => ($j % 2 == 0) ? '09:00:00' : '14:00:00',
          'end_time' => ($j % 2 == 0) ? '12:00:00' : '17:00:00',
          'room_id' => $roomIds[$j % count($roomIds)],
          'created_at' => $now,
          'updated_at' => $now,
        ]);
      }
    }

    // ──────────────────────────────────────────────────────────────
    // 9. EXAM STUDENTS (register students in exam system)
    // ──────────────────────────────────────────────────────────────
    $this->command->info('  → Exam Students...');
    $examStudentIds = [];
    foreach (array_slice($studentIds, 0, 25) as $i => $sid) {
      // Check if already exists
      $existing = DB::table('exam_students')->where('erp_student_id', $sid)->first();
      if ($existing) {
        $examStudentIds[] = $existing->id;
        continue;
      }
      $examStudentIds[] = DB::table('exam_students')->insertGetId([
        'erp_student_id' => $sid,
        'program_id' => 1,
        'enrollment_no' => 'EN2025' . str_pad($sid, 6, '0', STR_PAD_LEFT),
        'current_semester' => ($i < 10) ? 4 : (($i < 20) ? 2 : 6),
        'status' => 'active',
        'promotion_status' => null,
        'created_at' => $now,
        'updated_at' => $now,
      ]);
    }

    // ──────────────────────────────────────────────────────────────
    // 10. EXAM REGISTRATIONS (students registered for sessions)
    // ──────────────────────────────────────────────────────────────
    $this->command->info('  → Exam Registrations...');
    $registrationIds = [];
    $statuses = ['approved', 'approved', 'approved', 'approved', 'pending', 'rejected'];
    foreach ($sessionIds as $si => $sessId) {
      // Register 20 students per session
      foreach (array_slice($studentIds, 0, 20) as $j => $sid) {
        $status = $statuses[$j % count($statuses)];
        $registrationIds[] = DB::table('exam_registrations')->insertGetId([
          'erp_student_id' => $sid,
          'exam_session_id' => $sessId,
          'program_type' => 'regular',
          'is_backlog' => ($j >= 18) ? 1 : 0,
          'status' => $status,
          'registered_at' => $now->copy()->subDays(30 - $si * 10),
          'created_at' => $now,
          'updated_at' => $now,
        ]);
      }
    }

    // ──────────────────────────────────────────────────────────────
    // 11. EXAM MARKS WEIGHTAGES (per session/subject)
    // ──────────────────────────────────────────────────────────────
    $this->command->info('  → Marks Weightages...');
    foreach ($sessionIds as $sessId) {
      foreach (array_slice($subjectIds, 0, 6) as $sid) {
        DB::table('exam_marks_weightages')->insert([
          ['exam_session_id' => $sessId, 'erp_subject_id' => $sid, 'component' => 'Theory',    'weightage' => 70.00, 'created_at' => $now, 'updated_at' => $now],
          ['exam_session_id' => $sessId, 'erp_subject_id' => $sid, 'component' => 'Internal',  'weightage' => 20.00, 'created_at' => $now, 'updated_at' => $now],
          ['exam_session_id' => $sessId, 'erp_subject_id' => $sid, 'component' => 'Practical', 'weightage' => 10.00, 'created_at' => $now, 'updated_at' => $now],
        ]);
      }
    }

    // ──────────────────────────────────────────────────────────────
    // 12. SEATING ARRANGEMENTS
    // ──────────────────────────────────────────────────────────────
    $this->command->info('  → Seating Arrangements...');
    foreach ($sessionIds as $si => $sessId) {
      foreach (array_slice($studentIds, 0, 20) as $j => $sid) {
        $roomNo = $rooms[$j % count($rooms)]['room_no'];
        DB::table('exam_seating_arrangements')->insert([
          'exam_session_id' => $sessId,
          'room_no' => $roomNo,
          'seat_no' => 'S' . str_pad($j + 1, 3, '0', STR_PAD_LEFT),
          'erp_student_id' => $sid,
          'created_at' => $now,
          'updated_at' => $now,
        ]);
      }
    }

    // ──────────────────────────────────────────────────────────────
    // 13. DUMMY NUMBERS
    // ──────────────────────────────────────────────────────────────
    $this->command->info('  → Dummy Numbers...');
    foreach ($sessionIds as $si => $sessId) {
      foreach (array_slice($studentIds, 0, 20) as $j => $sid) {
        $dummyNo = 'DN' . $sessId . '-' . str_pad($j + 1, 4, '0', STR_PAD_LEFT);
        DB::table('exam_dummy_numbers')->insert([
          'exam_session_id' => $sessId,
          'erp_student_id' => $sid,
          'dummy_number' => $dummyNo,
          'created_at' => $now,
          'updated_at' => $now,
        ]);
      }
    }

    // ──────────────────────────────────────────────────────────────
    // 14. ATTENDANCE SESSIONS
    // ──────────────────────────────────────────────────────────────
    $this->command->info('  → Attendance Sessions...');
    $attendanceSessionIds = [];
    foreach ($examIds as $ei => $examId) {
      $baseDate = Carbon::parse($sessionData[$ei]['start_date']);
      for ($day = 0; $day < 3; $day++) {
        foreach (['morning', 'evening'] as $sess) {
          $attendanceSessionIds[] = DB::table('attendance_sessions')->insertGetId([
            'exam_id' => $examId,
            'room_id' => $roomIds[$day % count($roomIds)],
            'faculty_id' => $facultyIds[$day % count($facultyIds)],
            'session' => $sess,
            'date' => $baseDate->copy()->addDays($day)->toDateString(),
            'status' => ($ei == 0) ? 'closed' : 'open',
            'created_at' => $now,
            'updated_at' => $now,
          ]);
        }
      }
    }

    // ──────────────────────────────────────────────────────────────
    // 15. EXAM ATTENDANCES (for past/current sessions)
    // ──────────────────────────────────────────────────────────────
    $this->command->info('  → Exam Attendances...');
    // Only populate for sessions 0 (past) and 1 (current)
    foreach ([0, 1] as $si) {
      $examId = $examIds[$si];
      foreach (array_slice($subjectIds, 0, 4) as $subIdx => $sid) {
        foreach (array_slice($studentIds, 0, 20) as $j => $stid) {
          // ~85% present, ~10% absent, ~5% malpractice
          $rand = mt_rand(1, 100);
          $status = ($rand <= 85) ? 'present' : (($rand <= 95) ? 'absent' : 'malpractice');

          DB::table('exam_attendances')->insert([
            'exam_id' => $examId,
            'student_id' => $stid,
            'subject_id' => $sid,
            'room_id' => $roomIds[$j % count($roomIds)],
            'seat_no' => 'S' . str_pad($j + 1, 3, '0', STR_PAD_LEFT),
            'dummy_no' => 'DN' . $sessionIds[$si] . '-' . str_pad($j + 1, 4, '0', STR_PAD_LEFT),
            'status' => $status,
            'marked_by' => $facultyIds[$subIdx % count($facultyIds)],
            'marked_at' => Carbon::parse($sessionData[$si]['start_date'])->addDays($subIdx),
            'remarks' => ($status === 'malpractice') ? 'Unauthorized material found ' . self::TAG : null,
            'created_at' => $now,
            'updated_at' => $now,
          ]);
        }
      }
    }

    // ──────────────────────────────────────────────────────────────
    // 16. EXAM MARKS ENTRIES (for past session - session 0)
    // ──────────────────────────────────────────────────────────────
    $this->command->info('  → Marks Entries...');
    $marksEntryIds = [];
    $pastSessionId = $sessionIds[0];
    foreach (array_slice($subjectIds, 0, 6) as $subIdx => $sid) {
      foreach (array_slice($studentIds, 0, 20) as $j => $stid) {
        // Random marks between 25-95
        $marks = mt_rand(25, 95) + (mt_rand(0, 99) / 100);
        $marksEntryIds[] = DB::table('exam_marks_entries')->insertGetId([
          'exam_session_id' => $pastSessionId,
          'erp_student_id' => $stid,
          'erp_subject_id' => $sid,
          'marks' => round($marks, 2),
          'entered_by' => $adminUserId,
          'mac_address' => 'AA:BB:CC:DD:EE:' . str_pad(dechex($j), 2, '0', STR_PAD_LEFT),
          'entered_at' => $now->copy()->subDays(20),
          'created_at' => $now,
          'updated_at' => $now,
        ]);
      }
    }

    // Also seed some marks for session 1 (current/ongoing)
    $currentSessionId = $sessionIds[1];
    foreach (array_slice($subjectIds, 0, 3) as $subIdx => $sid) {
      foreach (array_slice($studentIds, 0, 15) as $j => $stid) {
        $marks = mt_rand(30, 92) + (mt_rand(0, 99) / 100);
        $marksEntryIds[] = DB::table('exam_marks_entries')->insertGetId([
          'exam_session_id' => $currentSessionId,
          'erp_student_id' => $stid,
          'erp_subject_id' => $sid,
          'marks' => round($marks, 2),
          'entered_by' => $adminUserId,
          'mac_address' => 'AA:BB:CC:DD:EE:' . str_pad(dechex($j + 20), 2, '0', STR_PAD_LEFT),
          'entered_at' => $now->copy()->subDays(5),
          'created_at' => $now,
          'updated_at' => $now,
        ]);
      }
    }

    // ──────────────────────────────────────────────────────────────
    // 17. EXAM MARKS AUDIT LOGS
    // ──────────────────────────────────────────────────────────────
    $this->command->info('  → Marks Audit Logs...');
    // Create audit logs for the first 30 marks entries
    foreach (array_slice($marksEntryIds, 0, 30) as $i => $entryId) {
      $entry = DB::table('exam_marks_entries')->find($entryId);
      if (!$entry) continue;

      DB::table('exam_marks_audit_logs')->insert([
        'exam_marks_entry_id' => $entryId,
        'exam_session_id' => $entry->exam_session_id,
        'erp_student_id' => $entry->erp_student_id,
        'erp_subject_id' => $entry->erp_subject_id,
        'old_marks' => null,
        'new_marks' => $entry->marks,
        'action' => 'created',
        'changed_by' => $adminUserId,
        'mac_address' => $entry->mac_address,
        'remarks' => 'Initial entry ' . self::TAG,
        'created_at' => $now,
        'updated_at' => $now,
      ]);
    }
    // A few 'updated' logs
    foreach (array_slice($marksEntryIds, 0, 5) as $entryId) {
      $entry = DB::table('exam_marks_entries')->find($entryId);
      if (!$entry) continue;
      $newMarks = round($entry->marks + mt_rand(-5, 5), 2);
      DB::table('exam_marks_audit_logs')->insert([
        'exam_marks_entry_id' => $entryId,
        'exam_session_id' => $entry->exam_session_id,
        'erp_student_id' => $entry->erp_student_id,
        'erp_subject_id' => $entry->erp_subject_id,
        'old_marks' => $entry->marks,
        'new_marks' => $newMarks,
        'action' => 'updated',
        'changed_by' => $adminUserId,
        'mac_address' => $entry->mac_address,
        'remarks' => 'Correction ' . self::TAG,
        'created_at' => $now,
        'updated_at' => $now,
      ]);
    }

    // ──────────────────────────────────────────────────────────────
    // 18. EXAM MARKS LOCKS (past session locked, current unlocked)
    // ──────────────────────────────────────────────────────────────
    $this->command->info('  → Marks Locks...');
    foreach (array_slice($subjectIds, 0, 6) as $sid) {
      DB::table('exam_marks_locks')->insert([
        'exam_session_id' => $pastSessionId,
        'erp_subject_id' => $sid,
        'is_locked' => 1,
        'locked_by' => $adminUserId,
        'locked_at' => $now->copy()->subDays(15),
        'unlocked_by' => null,
        'unlocked_at' => null,
        'remarks' => 'Locked after review ' . self::TAG,
        'created_at' => $now,
        'updated_at' => $now,
      ]);
    }
    // Current session: 2 subjects locked, rest unlocked
    foreach (array_slice($subjectIds, 0, 3) as $i => $sid) {
      DB::table('exam_marks_locks')->insert([
        'exam_session_id' => $currentSessionId,
        'erp_subject_id' => $sid,
        'is_locked' => ($i < 2) ? 1 : 0,
        'locked_by' => ($i < 2) ? $adminUserId : null,
        'locked_at' => ($i < 2) ? $now->copy()->subDays(2) : null,
        'unlocked_by' => null,
        'unlocked_at' => null,
        'remarks' => (($i < 2) ? 'Locked for moderation' : 'In progress') . ' ' . self::TAG,
        'created_at' => $now,
        'updated_at' => $now,
      ]);
    }

    // ──────────────────────────────────────────────────────────────
    // 19. EXAM RESULTS (past session)
    // ──────────────────────────────────────────────────────────────
    $this->command->info('  → Results...');
    $resultIds = [];
    $examResultIds = [];
    foreach (array_slice($studentIds, 0, 20) as $j => $stid) {
      $sgpa = round(mt_rand(40, 95) / 10, 2);
      $cgpa = round(($sgpa + mt_rand(40, 90) / 10) / 2, 2);
      $percentage = round($sgpa * 10 + mt_rand(-5, 5), 2);
      $resultStatus = ($sgpa >= 4.0) ? 'pass' : 'fail';

      // Insert into exam_results
      $examResultIds[] = DB::table('exam_results')->insertGetId([
        'exam_session_id' => $pastSessionId,
        'erp_student_id' => $stid,
        'sgpa' => $sgpa,
        'cgpa' => $cgpa,
        'result_status' => $resultStatus,
        'created_at' => $now,
        'updated_at' => $now,
      ]);

      // Insert into results (used by result_subjects FK)
      $esid = $examStudentIds[$j] ?? $j + 1;
      $resultIds[] = DB::table('results')->insertGetId([
        'exam_id' => $examIds[0],
        'exam_session_id' => $pastSessionId,
        'exam_student_id' => $esid,
        'sgpa' => $sgpa,
        'cgpa' => $cgpa,
        'percentage' => $percentage,
        'earned_credits' => mt_rand(15, 24),
        'result_status' => $resultStatus,
        'is_published' => 1,
        'published_at' => $now->copy()->subDays(5),
        'created_at' => $now,
        'updated_at' => $now,
      ]);
    }

    // ──────────────────────────────────────────────────────────────
    // 20. RESULT SUBJECTS
    // ──────────────────────────────────────────────────────────────
    $this->command->info('  → Result Subjects...');
    foreach ($resultIds as $ri => $resultId) {
      foreach (array_slice($subjectIds, 0, 6) as $si => $sid) {
        $faMarks = round(mt_rand(10, 20), 2);
        $saMarks = round(mt_rand(20, 70), 2);
        $total = $faMarks + $saMarks;
        $gradePoint = ($total >= 90) ? 10 : (($total >= 80) ? 9 : (($total >= 70) ? 8 : (($total >= 60) ? 7 : (($total >= 50) ? 6 : (($total >= 40) ? 5 : (($total >= 33) ? 4 : 0))))));
        $gradeMap = [10 => 'O', 9 => 'A+', 8 => 'A', 7 => 'B+', 6 => 'B', 5 => 'C', 4 => 'P', 0 => 'F'];

        DB::table('result_subjects')->insert([
          'result_id' => $resultId,
          'erp_subject_id' => $sid,
          'subject_code' => $subjectCodes[$si] ?? 'SUB',
          'subject_name' => $subjectNames[$si] ?? 'Subject',
          'fa_marks' => $faMarks,
          'sa_marks' => $saMarks,
          'total_marks' => $total,
          'max_marks' => 100,
          'credits' => $subjectCredits[$si] ?? 3,
          'grade_point' => $gradePoint,
          'grade' => $gradeMap[$gradePoint] ?? 'F',
          'result_status' => ($total >= 33) ? 'pass' : 'fail',
          'created_at' => $now,
          'updated_at' => $now,
        ]);
      }
    }

    // ──────────────────────────────────────────────────────────────
    // 21. RESULT LOCKS
    // ──────────────────────────────────────────────────────────────
    $this->command->info('  → Result Locks...');
    DB::table('result_locks')->insert([
      'exam_session_id' => $pastSessionId,
      'is_locked' => 1,
      'locked_by' => $adminUserId,
      'locked_at' => $now->copy()->subDays(10),
      'unlocked_by' => null,
      'unlocked_at' => null,
      'remarks' => 'Results finalized ' . self::TAG,
      'created_at' => $now,
      'updated_at' => $now,
    ]);

    // ──────────────────────────────────────────────────────────────
    // 22. INVIGILATION DUTIES
    // ──────────────────────────────────────────────────────────────
    $this->command->info('  → Invigilation Duties...');
    foreach ($examIds as $ei => $examId) {
      $baseDate = Carbon::parse($sessionData[$ei]['start_date']);
      for ($day = 0; $day < 4; $day++) {
        foreach (['morning', 'evening'] as $sessIdx => $sess) {
          $fi = ($day * 2 + $sessIdx) % count($facultyProfileIds);
          DB::table('invigilation_duties')->insert([
            'exam_id' => $examId,
            'faculty_id' => $facultyProfileIds[$fi],
            'room_id' => $roomIds[$day % count($roomIds)],
            'date' => $baseDate->copy()->addDays($day)->toDateString(),
            'session' => $sess,
            'role' => ($sessIdx == 0) ? 'chief' : 'assistant',
            'status' => ($ei == 0) ? 'completed' : 'pending',
            'created_at' => $now,
            'updated_at' => $now,
          ]);
        }
      }
    }

    // ──────────────────────────────────────────────────────────────
    // 23. EVALUATION DUTIES
    // ──────────────────────────────────────────────────────────────
    $this->command->info('  → Evaluation Duties...');
    foreach ($examIds as $ei => $examId) {
      foreach (array_slice($subjectIds, 0, 5) as $si => $sid) {
        $copiesAssigned = mt_rand(15, 25);
        $copiesEval = ($ei == 0) ? $copiesAssigned : mt_rand(0, $copiesAssigned);
        DB::table('evaluation_duties')->insert([
          'exam_id' => $examId,
          'faculty_id' => $facultyProfileIds[($si + $ei) % count($facultyProfileIds)],
          'subject_id' => $examSubjectMasterIds[$si % count($examSubjectMasterIds)],
          'copies_assigned' => $copiesAssigned,
          'copies_evaluated' => $copiesEval,
          'status' => ($copiesEval >= $copiesAssigned) ? 'completed' : 'pending',
          'created_at' => $now,
          'updated_at' => $now,
        ]);
      }
    }

    // ──────────────────────────────────────────────────────────────
    // 24. MODERATION DUTIES
    // ──────────────────────────────────────────────────────────────
    $this->command->info('  → Moderation Duties...');
    foreach ($examIds as $ei => $examId) {
      foreach (array_slice($subjectIds, 0, 4) as $si => $sid) {
        DB::table('moderation_duties')->insert([
          'exam_id' => $examId,
          'faculty_id' => $facultyProfileIds[($si + 3) % count($facultyProfileIds)],
          'subject_id' => $examSubjectMasterIds[$si % count($examSubjectMasterIds)],
          'moderation_type' => ($si % 2 == 0) ? 'internal' : 'external',
          'status' => ($ei == 0) ? 'completed' : 'pending',
          'created_at' => $now,
          'updated_at' => $now,
        ]);
      }
    }

    // ──────────────────────────────────────────────────────────────
    // 25. MODERATION RECORDS (for past session)
    // ──────────────────────────────────────────────────────────────
    $this->command->info('  → Moderation Records...');
    foreach (array_slice($subjectIds, 0, 3) as $si => $sid) {
      foreach (array_slice($studentIds, 0, 10) as $j => $stid) {
        $evalMarks = round(mt_rand(30, 85) + (mt_rand(0, 99) / 100), 2);
        $modMarks = round($evalMarks + mt_rand(-8, 8), 2);
        $adjusted = round(($evalMarks + $modMarks) / 2, 2);
        $diff = round(abs($evalMarks - $modMarks), 2);

        // Find the corresponding marks entry
        $marksEntry = DB::table('exam_marks_entries')
          ->where('exam_session_id', $pastSessionId)
          ->where('erp_student_id', $stid)
          ->where('erp_subject_id', $sid)
          ->first();

        DB::table('moderation_records')->insert([
          'exam_session_id' => $pastSessionId,
          'erp_student_id' => $stid,
          'erp_subject_id' => $sid,
          'evaluator_marks' => $evalMarks,
          'moderator_marks' => $modMarks,
          'adjusted_marks' => $adjusted,
          'difference' => $diff,
          'moderator_id' => $facultyIds[($si + 3) % count($facultyIds)],
          'adjusted_by' => $adminUserId,
          'status' => ($diff > 10) ? 'flagged' : 'finalized',
          'remarks' => ($diff > 10) ? 'Large deviation detected ' . self::TAG : 'Moderated ' . self::TAG,
          'exam_marks_entry_id' => $marksEntry->id ?? null,
          'created_at' => $now,
          'updated_at' => $now,
        ]);
      }
    }

    // ──────────────────────────────────────────────────────────────
    // 26. DUTY LOGS
    // ──────────────────────────────────────────────────────────────
    $this->command->info('  → Duty Logs...');
    foreach (array_slice($facultyProfileIds, 0, 6) as $fi => $fpid) {
      foreach (['invigilation', 'evaluation', 'moderation'] as $dtype) {
        DB::table('duty_logs')->insert([
          'faculty_id' => $fpid,
          'duty_type' => $dtype,
          'reference_id' => $fi + 1,
          'action' => ($fi % 2 == 0) ? 'completed' : 'assigned',
          'timestamp' => $now->copy()->subDays($fi),
          'created_at' => $now,
          'updated_at' => $now,
        ]);
      }
    }

    // ──────────────────────────────────────────────────────────────
    // 27. EXAM PACKETS
    // ──────────────────────────────────────────────────────────────
    $this->command->info('  → Exam Packets...');
    $packetIds = [];
    foreach (array_slice($subjectIds, 0, 4) as $si => $sid) {
      $packetNo = 'PKT-' . $pastSessionId . '-' . str_pad($si + 1, 3, '0', STR_PAD_LEFT);
      $packetIds[] = DB::table('exam_packets')->insertGetId([
        'exam_session_id' => $pastSessionId,
        'erp_subject_id' => $sid,
        'packet_number' => $packetNo,
        'barcode' => 'BAR' . strtoupper(md5($packetNo . self::TAG)),
        'total_scripts' => 20,
        'status' => ($si < 2) ? 'completed' : 'assigned',
        'evaluator_id' => $facultyIds[$si % count($facultyIds)],
        'assigned_at' => $now->copy()->subDays(18),
        'completed_at' => ($si < 2) ? $now->copy()->subDays(12) : null,
        'generated_by' => $adminUserId,
        'remarks' => 'Test packet ' . self::TAG,
        'current_holder_name' => null,
        'current_holder_role' => null,
        'last_scanned_at' => null,
        'created_at' => $now,
        'updated_at' => $now,
      ]);
    }

    // ──────────────────────────────────────────────────────────────
    // 28. EXAM PACKET STUDENTS
    // ──────────────────────────────────────────────────────────────
    $this->command->info('  → Exam Packet Students...');
    foreach ($packetIds as $pi => $packetId) {
      foreach (array_slice($studentIds, 0, 15) as $j => $stid) {
        DB::table('exam_packet_students')->insert([
          'exam_packet_id' => $packetId,
          'erp_student_id' => $stid,
          'dummy_number' => 'DN' . $pastSessionId . '-' . str_pad($j + 1, 4, '0', STR_PAD_LEFT),
          'created_at' => $now,
          'updated_at' => $now,
        ]);
      }
    }

    // ──────────────────────────────────────────────────────────────
    // 29. EXAM PACKET SCAN LOGS
    // ──────────────────────────────────────────────────────────────
    $this->command->info('  → Exam Packet Scan Logs...');
    foreach ($packetIds as $pi => $packetId) {
      $packet = DB::table('exam_packets')->find($packetId);
      DB::table('exam_packet_scan_logs')->insert([
        [
          'exam_packet_id' => $packetId,
          'barcode' => $packet->barcode,
          'action' => 'received',
          'scanned_by_name' => 'COE Office',
          'scanned_by_user_id' => $adminUserId,
          'holder_name' => 'Exam Store',
          'holder_role' => 'store_keeper',
          'previous_status' => 'generated',
          'new_status' => 'assigned',
          'remarks' => 'Received at store ' . self::TAG,
          'device_info' => 'Scanner-001',
          'ip_address' => '192.168.1.100',
          'latitude' => null,
          'longitude' => null,
          'created_at' => $now->copy()->subDays(18),
          'updated_at' => $now,
        ],
        [
          'exam_packet_id' => $packetId,
          'barcode' => $packet->barcode,
          'action' => 'transferred',
          'scanned_by_name' => 'Store Keeper',
          'scanned_by_user_id' => $adminUserId,
          'holder_name' => 'Evaluator',
          'holder_role' => 'evaluator',
          'previous_status' => 'assigned',
          'new_status' => 'assigned',
          'remarks' => 'Handed to evaluator ' . self::TAG,
          'device_info' => 'Scanner-001',
          'ip_address' => '192.168.1.101',
          'latitude' => null,
          'longitude' => null,
          'created_at' => $now->copy()->subDays(16),
          'updated_at' => $now,
        ],
      ]);
    }

    // ──────────────────────────────────────────────────────────────
    // 30. MAC WHITELISTS
    // ──────────────────────────────────────────────────────────────
    $this->command->info('  → MAC Whitelists...');
    foreach (array_slice($userIds, 0, 3) as $i => $uid) {
      DB::table('exam_mac_whitelists')->insert([
        'erp_user_id' => $uid,
        'mac_address' => 'AA:BB:CC:DD:EE:' . str_pad(dechex($i + 1), 2, '0', STR_PAD_LEFT),
        'added_at' => $now,
        'created_at' => $now,
        'updated_at' => $now,
      ]);
    }

    // ──────────────────────────────────────────────────────────────
    // 31. EXAM PROMOTIONS
    // ──────────────────────────────────────────────────────────────
    $this->command->info('  → Promotions...');
    foreach (array_slice($studentIds, 0, 15) as $j => $stid) {
      DB::table('exam_promotions')->insert([
        'erp_student_id' => $stid,
        'from_session_id' => $sessionIds[0],
        'to_session_id' => $sessionIds[1],
        'promoted_at' => $now->copy()->subDays(10),
        'created_at' => $now,
        'updated_at' => $now,
      ]);
    }

    // ──────────────────────────────────────────────────────────────
    // 32. EXAM BACKLOGS
    // ──────────────────────────────────────────────────────────────
    $this->command->info('  → Backlogs...');
    // Mark 5 students with backlogs in 1-2 subjects
    foreach (array_slice($studentIds, 15, 5) as $j => $stid) {
      foreach (array_slice($subjectIds, $j, 1 + ($j % 2)) as $sid) {
        DB::table('exam_backlogs')->insert([
          'erp_student_id' => $stid,
          'erp_subject_id' => $sid,
          'session_id' => $sessionIds[0],
          'status' => ($j < 3) ? 'pending' : 'cleared',
          'created_at' => $now,
          'updated_at' => $now,
        ]);
      }
    }

    // ──────────────────────────────────────────────────────────────
    // 33. CONDONATION RULES
    // ──────────────────────────────────────────────────────────────
    $this->command->info('  → Condonation Rules...');
    $condonationRuleIds = [];
    $condonationRuleIds[] = DB::table('condonation_rules')->insertGetId([
      'program_id' => 1,
      'rule_name' => 'Attendance Shortage ' . self::TAG,
      'description' => 'Condonation for attendance below 75% but above 65%',
      'max_absences' => 25,
      'created_at' => $now,
      'updated_at' => $now,
    ]);
    $condonationRuleIds[] = DB::table('condonation_rules')->insertGetId([
      'program_id' => 1,
      'rule_name' => 'Medical Condonation ' . self::TAG,
      'description' => 'Condonation for medical reasons with valid certificate',
      'max_absences' => 40,
      'created_at' => $now,
      'updated_at' => $now,
    ]);

    // ──────────────────────────────────────────────────────────────
    // 34. CONDONATION APPLICATIONS
    // ──────────────────────────────────────────────────────────────
    $this->command->info('  → Condonation Applications...');
    foreach (array_slice($examStudentIds, 0, 4) as $j => $esid) {
      DB::table('condonation_applications')->insert([
        'exam_student_id' => $esid,
        'condonation_rule_id' => $condonationRuleIds[$j % count($condonationRuleIds)],
        'status' => ($j < 2) ? 'approved' : 'pending',
        'remarks' => 'Application ' . ($j + 1) . ' ' . self::TAG,
        'created_at' => $now,
        'updated_at' => $now,
      ]);
    }

    // ──────────────────────────────────────────────────────────────
    // 35. MALPRACTICE CASES
    // ──────────────────────────────────────────────────────────────
    $this->command->info('  → Malpractice Cases...');
    DB::table('malpractice_cases')->insert([
      [
        'exam_id' => $examIds[0],
        'student_id' => $studentIds[5],
        'subject_id' => $subjectIds[0],
        'room_id' => $roomIds[0],
        'remarks' => 'Mobile phone found during exam ' . self::TAG,
        'status' => 'reviewed',
        'reported_by' => $facultyIds[0],
        'reported_at' => Carbon::parse($sessionData[0]['start_date'])->addDay(),
        'created_at' => $now,
        'updated_at' => $now,
      ],
      [
        'exam_id' => $examIds[1],
        'student_id' => $studentIds[12],
        'subject_id' => $subjectIds[2],
        'room_id' => $roomIds[2],
        'remarks' => 'Cheat sheet discovered ' . self::TAG,
        'status' => 'pending',
        'reported_by' => $facultyIds[2],
        'reported_at' => Carbon::parse($sessionData[1]['start_date'])->addDays(2),
        'created_at' => $now,
        'updated_at' => $now,
      ],
    ]);

    // ──────────────────────────────────────────────────────────────
    // 36. STUDENT CREDITS (ABC)
    // ──────────────────────────────────────────────────────────────
    $this->command->info('  → Student Credits...');
    foreach (array_slice($examStudentIds, 0, 15) as $j => $esid) {
      foreach (array_slice($examSubjectMasterIds, 0, 4) as $si => $esmId) {
        $creditsEarned = $subjectCredits[$si] ?? 3;
        $gp = round(mt_rand(4, 9), 1);
        $gradePoint = $gp;
        $gradeMap = [9 => 'A+', 8 => 'A', 7 => 'B+', 6 => 'B', 5 => 'C', 4 => 'P'];

        DB::table('student_credits')->insert([
          'exam_student_id' => $esid,
          'exam_subject_id' => $esmId,
          'exam_session_id' => $pastSessionId,
          'credits_earned' => $creditsEarned,
          'credit_type' => 'earned',
          'semester' => 2,
          'grade' => $gradeMap[(int)$gp] ?? 'B',
          'grade_point' => $gradePoint,
          'source_institution' => null,
          'source_subject_code' => null,
          'source_subject_name' => null,
          'transfer_date' => null,
          'transfer_reference' => null,
          'verified_by' => $adminUserId,
          'verified_at' => $now->copy()->subDays(5),
          'status' => 'verified',
          'remarks' => self::TAG,
          'created_at' => $now,
          'updated_at' => $now,
        ]);
      }
    }
    // A few transfer credits
    foreach (array_slice($examStudentIds, 0, 3) as $j => $esid) {
      DB::table('student_credits')->insert([
        'exam_student_id' => $esid,
        'exam_subject_id' => $examSubjectMasterIds[0],
        'exam_session_id' => $pastSessionId,
        'credits_earned' => 3,
        'credit_type' => 'transferred',
        'semester' => 1,
        'grade' => 'B+',
        'grade_point' => 7.0,
        'source_institution' => 'Delhi University ' . self::TAG,
        'source_subject_code' => 'DU-ENG101',
        'source_subject_name' => 'English Communication',
        'transfer_date' => $now->copy()->subDays(60)->toDateString(),
        'transfer_reference' => 'TR-2025-' . str_pad($j + 1, 3, '0', STR_PAD_LEFT),
        'verified_by' => $adminUserId,
        'verified_at' => $now->copy()->subDays(55),
        'status' => 'verified',
        'remarks' => 'Transfer credit ' . self::TAG,
        'created_at' => $now,
        'updated_at' => $now,
      ]);
    }

    // ──────────────────────────────────────────────────────────────
    // 37. EXIT CERTIFICATIONS
    // ──────────────────────────────────────────────────────────────
    $this->command->info('  → Exit Certifications...');
    // A few students eligible for exit certification
    foreach (array_slice($examStudentIds, 0, 3) as $j => $esid) {
      $levels = ['certificate', 'diploma', 'degree'];
      DB::table('exit_certifications')->insert([
        'exam_student_id' => $esid,
        'program_id' => 1,
        'exit_level' => $levels[$j],
        'certificate_no' => 'CERT-2025-' . str_pad($j + 1, 4, '0', STR_PAD_LEFT),
        'total_credits_earned' => ($j + 1) * 40,
        'credits_required' => ($j + 1) * 40,
        'cgpa' => round(mt_rand(60, 85) / 10, 2),
        'semesters_completed' => ($j + 1) * 2,
        'status' => ($j < 2) ? 'issued' : 'pending',
        'issue_date' => ($j < 2) ? $now->copy()->subDays(5)->toDateString() : null,
        'approved_by' => $adminUserId,
        'issued_by' => ($j < 2) ? $adminUserId : null,
        'credit_summary' => json_encode(['sem1' => 20, 'sem2' => 20, 'sem3' => $j > 0 ? 20 : 0]),
        'remarks' => 'Exit cert ' . self::TAG,
        'created_at' => $now,
        'updated_at' => $now,
      ]);
    }

    // ──────────────────────────────────────────────────────────────
    // 38. STUDENT EXAM REGISTRATIONS (old system table)
    // ──────────────────────────────────────────────────────────────
    $this->command->info('  → Student Exam Registrations (old system)...');
    foreach (array_slice($examStudentIds, 0, 10) as $j => $esid) {
      DB::table('student_exam_registrations')->insert([
        'exam_id' => $examIds[0],
        'exam_student_id' => $esid,
        'is_backlog' => ($j >= 8) ? 1 : 0,
        'status' => 'approved',
        'created_at' => $now,
        'updated_at' => $now,
      ]);
    }

    // ──────────────────────────────────────────────────────────────
    // 39. EXAM EXIT CERTIFICATIONS (simplified table)
    // ──────────────────────────────────────────────────────────────
    $this->command->info('  → Exam Exit Certifications...');
    foreach (array_slice($studentIds, 0, 2) as $j => $stid) {
      DB::table('exam_exit_certifications')->insert([
        'erp_student_id' => $stid,
        'exit_level' => ($j == 0) ? 'degree' : 'diploma',
        'session_id' => $pastSessionId,
        'issued_at' => $now->copy()->subDays(3),
        'created_at' => $now,
        'updated_at' => $now,
      ]);
    }

    // ══════════════════════════════════════════════════════════════
    $this->command->info('');
    $this->command->info('✅ Exam Module seeding complete! Summary:');
    $this->command->info('   Sessions: 3 | Exams: 3 | Subjects: ' . count($examSubjectMasterIds));
    $this->command->info('   Students: ' . count($examStudentIds) . ' | Registrations: ' . (20 * 3));
    $this->command->info('   Rooms: ' . count($roomIds) . ' | Schedules: ' . count($examScheduleIds));
    $this->command->info('   Attendance: ~' . (2 * 4 * 20) . ' records');
    $this->command->info('   Marks Entries: ~' . ((6 * 20) + (3 * 15)));
    $this->command->info('   Results: ' . count($resultIds) . ' | Result Subjects: ' . (count($resultIds) * 6));
    $this->command->info('   Invigilation: ' . (3 * 4 * 2) . ' | Evaluation: ' . (3 * 5) . ' | Moderation: ' . (3 * 4));
    $this->command->info('   Packets: ' . count($packetIds) . ' | Scan Logs: ' . (count($packetIds) * 2));
    $this->command->info('   Promotions: 15 | Backlogs: ~7 | Credits: ~' . (15 * 4 + 3));
    $this->command->info('   Exit Certs: 3 | Malpractice: 2 | Condonations: 4');
    $this->command->info('');
    $this->command->info('🏷️  All test data tagged with "' . self::TAG . '" for easy cleanup.');
  }
}
