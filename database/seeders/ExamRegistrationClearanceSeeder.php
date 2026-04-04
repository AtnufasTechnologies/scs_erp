<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ExamRegistrationClearanceSeeder extends Seeder
{
  /**
   * Seed dummy attendance, fee payments, and registration-subject data
   * for exam registrations so clearance checks work properly.
   */
  public function run(): void
  {
    $registrations = DB::table('exam_registrations')
      ->select('id', 'erp_student_id', 'exam_session_id', 'is_backlog')
      ->get();

    if ($registrations->isEmpty()) {
      $this->command->info('No exam registrations found. Run ExamModuleTestSeeder first.');
      return;
    }

    $studentIds = $registrations->pluck('erp_student_id')->unique();
    $now = Carbon::now();

    // ─────────────────────────────────────────────────────
    // 1. Generate Student Attendance Data
    // ─────────────────────────────────────────────────────
    $this->command->info('[TEST] Generating student attendance records...');

    // Clean existing test attendance for these students
    DB::table('student_attendances')
      ->whereIn('student_id', $studentIds)
      ->delete();

    $subjectMasters = DB::table('exam_subject_masters')->get();
    $facultyIds = DB::table('faculties')->limit(10)->pluck('id')->toArray();
    if (empty($facultyIds)) {
      $facultyIds = [1];
    }
    $routineIds = DB::table('subject_has_routines')->pluck('id')->toArray();
    if (empty($routineIds)) {
      $routineIds = [1];
    }
    $attendanceBatch = [];
    $totalAttendance = 0;
    $usedKeys = []; // Track unique constraint: course_id+student_id+date+hour_id+faculty_id

    foreach ($studentIds as $index => $studentId) {
      // Vary attendance: ~40% students get 80-95% (cleared), ~30% get 65-74% (not cleared), ~30% get 75-80% (borderline)
      $rand = $index % 10;
      if ($rand < 4) {
        $targetPercent = rand(82, 95); // High attendance - cleared
      } elseif ($rand < 7) {
        $targetPercent = rand(55, 72); // Low attendance - not cleared
      } else {
        $targetPercent = rand(75, 80); // Borderline
      }

      $totalClasses = rand(80, 120);
      $presentCount = (int) round($totalClasses * $targetPercent / 100);

      // Generate attendance records across last 4 months
      $startDate = Carbon::parse('2026-01-06');

      for ($i = 0; $i < $totalClasses; $i++) {
        $date = $startDate->copy()->addDays(intdiv($i, 2)); // ~2 classes per day
        if ($date->isWeekend()) {
          $date->addDays(2);
        }

        $status = $i < $presentCount ? 'present' : 'absent';
        // Shuffle some absences into the middle
        if ($i >= $presentCount && rand(1, 3) == 1) {
          $status = 'present';
        } elseif ($i < $presentCount && rand(1, 8) == 1) {
          $status = 'absent';
        }

        $courseId = $subjectMasters->isNotEmpty()
          ? $subjectMasters->random()->erp_subject_id
          : rand(1, 6);
        $hourId = ($i % 6) + 1;
        $facultyId = $facultyIds[array_rand($facultyIds)];
        $dateStr = $date->format('Y-m-d');

        // Ensure unique: course_id+student_id+date+hour_id+faculty_id
        $uniqueKey = "{$courseId}_{$studentId}_{$dateStr}_{$hourId}_{$facultyId}";
        if (isset($usedKeys[$uniqueKey])) {
          continue; // Skip duplicate
        }
        $usedKeys[$uniqueKey] = true;

        $attendanceBatch[] = [
          'routine_id' => $routineIds[array_rand($routineIds)],
          'student_id' => $studentId,
          'attendance_date' => $dateStr,
          'course_id' => $courseId,
          'hour_id' => $hourId,
          'semester_id' => rand(5, 6),
          'batch' => '6',
          'lecture_start_time' => sprintf('%02d:00:00', 8 + ($i % 6)),
          'lecture_end_time' => sprintf('%02d:00:00', 9 + ($i % 6)),
          'status' => $status,
          'remarks' => null,
          'faculty_id' => $facultyId,
          'qr_url' => null,
          'attendance_method' => 'manual',
          'created_at' => $now,
          'updated_at' => $now,
        ];

        $totalAttendance++;

        // Batch insert every 500 records
        if (count($attendanceBatch) >= 500) {
          DB::table('student_attendances')->insert($attendanceBatch);
          $attendanceBatch = [];
        }
      }
    }

    if (!empty($attendanceBatch)) {
      DB::table('student_attendances')->insert($attendanceBatch);
    }

    $this->command->info("[TEST] Created {$totalAttendance} attendance records for " . $studentIds->count() . " students");

    // ─────────────────────────────────────────────────────
    // 2. Generate Fee Structures & Student Payments
    // ─────────────────────────────────────────────────────
    $this->command->info('[TEST] Generating fee structures and payment records...');

    // Create fee structures for programs 3 and 4 (used by registered students)
    $programIds = DB::table('student_masters')
      ->whereIn('id', $studentIds)
      ->pluck('new_program_id')
      ->unique();

    // Clean existing test fee structures for these programs
    $existingFsIds = DB::table('fees_structures')
      ->whereIn('program_id', $programIds)
      ->pluck('id');

    if ($existingFsIds->isNotEmpty()) {
      DB::table('fee_structure_has_heads')
        ->whereIn('fee_structure_id', $existingFsIds)
        ->delete();
      DB::table('fees_structures')
        ->whereIn('id', $existingFsIds)
        ->delete();
    }

    // Clean existing test payments for these students
    DB::table('student_payments')
      ->whereIn('student_id', $studentIds)
      ->delete();

    $feeStructureIds = [];
    $feeHeadIds = DB::table('fee_heads')->pluck('id')->toArray();
    if (empty($feeHeadIds)) {
      $feeHeadIds = [1, 2, 3, 4, 5];
    }

    foreach ($programIds as $programId) {
      // Create 2 fee structures per program (current quarter + exam fee)
      foreach (
        [
          ['quarter_title' => 'January 2026 to March 2026', 'yearly_pay_order' => 3, 'course_name' => 1],
          ['quarter_title' => 'Examination Fee 2026', 'yearly_pay_order' => 4, 'course_name' => 2],
        ] as $fsData
      ) {
        $fsId = DB::table('fees_structures')->insertGetId([
          'program_id' => $programId,
          'batch_id' => 6,
          'course_name' => $fsData['course_name'],
          'quarter_title' => $fsData['quarter_title'],
          'yearly_pay_order' => $fsData['yearly_pay_order'],
          'std_current_year' => 3,
          'due_date' => '2026-03-15',
          'reminder_date' => '2026-03-01',
          'is_payable' => 1,
          'created_at' => $now,
          'updated_at' => $now,
        ]);

        // Add fee heads with amounts
        $amounts = [15000, 2500, 500, 1000, 800];
        $headBatch = [];
        foreach (array_slice($feeHeadIds, 0, min(5, count($feeHeadIds))) as $idx => $headId) {
          $headBatch[] = [
            'fee_structure_id' => $fsId,
            'fee_head_id' => $headId,
            'amount' => $amounts[$idx] ?? rand(500, 2000),
            'created_at' => $now,
            'updated_at' => $now,
          ];
        }
        DB::table('fee_structure_has_heads')->insert($headBatch);

        $feeStructureIds[$programId][] = $fsId;
      }
    }

    // Calculate total due per program
    $totalDuePerProgram = [];
    foreach ($feeStructureIds as $programId => $fsIds) {
      $totalDuePerProgram[$programId] = DB::table('fee_structure_has_heads')
        ->whereIn('fee_structure_id', $fsIds)
        ->sum('amount');
    }

    // Generate student payments: ~50% fully paid, ~25% partially paid, ~25% no payment
    $paymentBatch = [];
    $totalPayments = 0;

    foreach ($studentIds as $index => $studentId) {
      $student = DB::table('student_masters')
        ->where('id', $studentId)
        ->select('new_program_id')
        ->first();

      if (!$student || !isset($feeStructureIds[$student->new_program_id])) {
        continue;
      }

      $programFsIds = $feeStructureIds[$student->new_program_id];
      $totalDue = $totalDuePerProgram[$student->new_program_id] ?? 0;
      $rand = $index % 4;

      if ($rand < 2) {
        // Fully paid - create payment for each fee structure
        foreach ($programFsIds as $fsId) {
          $fsAmount = DB::table('fee_structure_has_heads')
            ->where('fee_structure_id', $fsId)
            ->sum('amount');

          $paymentBatch[] = [
            'invoice_id' => 'INV-2026-' . str_pad($totalPayments + 1, 6, '0', STR_PAD_LEFT),
            'student_id' => $studentId,
            'fee_structure_id' => $fsId,
            'gateway_type_id' => rand(1, 2),
            'gateway_ref_code' => 'GW' . rand(100000, 999999),
            'transaction_id' => 'TXN' . rand(1000000000, 9999999999),
            'transaction_date' => Carbon::parse('2026-01-' . rand(10, 28))->format('Y-m-d'),
            'amount' => (string) $fsAmount,
            'captured_amount' => (string) $fsAmount,
            'late_fee_amount' => '0',
            'late_days' => 0,
            'status' => 'success',
            'message' => 'Payment successful',
            'raw_response' => null,
            'hash' => null,
            'created_at' => $now,
            'updated_at' => $now,
          ];
          $totalPayments++;
        }
      } elseif ($rand == 2) {
        // Partially paid - pay only first fee structure
        $fsId = $programFsIds[0];
        $fsAmount = DB::table('fee_structure_has_heads')
          ->where('fee_structure_id', $fsId)
          ->sum('amount');

        $paymentBatch[] = [
          'invoice_id' => 'INV-2026-' . str_pad($totalPayments + 1, 6, '0', STR_PAD_LEFT),
          'student_id' => $studentId,
          'fee_structure_id' => $fsId,
          'gateway_type_id' => rand(1, 2),
          'gateway_ref_code' => 'GW' . rand(100000, 999999),
          'transaction_id' => 'TXN' . rand(1000000000, 9999999999),
          'transaction_date' => Carbon::parse('2026-02-' . rand(1, 28))->format('Y-m-d'),
          'amount' => (string) $fsAmount,
          'captured_amount' => (string) $fsAmount,
          'late_fee_amount' => '0',
          'late_days' => 0,
          'status' => 'success',
          'message' => 'Payment successful',
          'raw_response' => null,
          'hash' => null,
          'created_at' => $now,
          'updated_at' => $now,
        ];
        $totalPayments++;
      }
      // else: no payment (25% of students)
    }

    if (!empty($paymentBatch)) {
      DB::table('student_payments')->insert($paymentBatch);
    }

    $this->command->info("[TEST] Created fee structures for programs: " . $programIds->implode(', '));
    $this->command->info("[TEST] Created {$totalPayments} payment records");

    // ─────────────────────────────────────────────────────
    // 3. Generate Registration-Subject Pivot Data
    // ─────────────────────────────────────────────────────
    $this->command->info('[TEST] Generating registration-subject records...');

    DB::table('exam_registration_subjects')->truncate();

    $examSubjects = DB::table('exam_subjects')->get()->groupBy('exam_session_id');
    $regSubjectBatch = [];
    $totalRegSubjects = 0;

    foreach ($registrations as $reg) {
      $sessionSubjects = $examSubjects->get($reg->exam_session_id);
      if (!$sessionSubjects) {
        continue;
      }

      $regularSubjects = $sessionSubjects->where('is_backlog', 0)->values();
      $backlogSubjects = $sessionSubjects->where('is_backlog', 1)->values();

      // All students get all regular subjects
      foreach ($regularSubjects as $subject) {
        $regSubjectBatch[] = [
          'exam_registration_id' => $reg->id,
          'exam_subject_id' => $subject->id,
          'is_backlog' => false,
          'created_at' => $now,
          'updated_at' => $now,
        ];
        $totalRegSubjects++;
      }

      // Backlog students get additional backlog subjects
      if ($reg->is_backlog && $backlogSubjects->isNotEmpty()) {
        foreach ($backlogSubjects as $subject) {
          $regSubjectBatch[] = [
            'exam_registration_id' => $reg->id,
            'exam_subject_id' => $subject->id,
            'is_backlog' => true,
            'created_at' => $now,
            'updated_at' => $now,
          ];
          $totalRegSubjects++;
        }
      }

      // ~30% of regular students also have 1 extra backlog subject (failed a subject earlier)
      if (!$reg->is_backlog && $backlogSubjects->isNotEmpty() && rand(1, 10) <= 3) {
        $extraBacklog = $backlogSubjects->first();
        $regSubjectBatch[] = [
          'exam_registration_id' => $reg->id,
          'exam_subject_id' => $extraBacklog->id,
          'is_backlog' => true,
          'created_at' => $now,
          'updated_at' => $now,
        ];
        $totalRegSubjects++;
      }
    }

    if (!empty($regSubjectBatch)) {
      DB::table('exam_registration_subjects')->insert($regSubjectBatch);
    }

    $this->command->info("[TEST] Created {$totalRegSubjects} registration-subject records");

    // ─────────────────────────────────────────────────────
    // 4. Reset clearance fields to pending for re-check
    // ─────────────────────────────────────────────────────
    DB::table('exam_registrations')->update([
      'attendance_clearance' => 'pending',
      'library_clearance' => 'pending',
      'fees_clearance' => 'pending',
      'attendance_percentage' => null,
    ]);

    $this->command->info('[TEST] All clearance fields reset to pending for fresh check.');
    $this->command->info('[TEST] Seeding complete! Use "Check Clearances" button in the UI to compute clearances.');
  }
}
