<?php

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;

echo "Creating test data for Student ID 6795...\n\n";

// Get student details
$student = DB::table('student_masters')->where('id', 6795)->first();
if (!$student) {
  die("Student 6795 not found\n");
}
echo "Student: {$student->first_name} {$student->last_name}\n\n";

// Create a regulation if doesn't exist
$regulation = DB::table('exam_regulations')->first();
if (!$regulation) {
  $regulationId = DB::table('exam_regulations')->insertGetId([
    'name' => 'Regulation 2024',
    'type' => 'Semester System',
    'description' => 'Autonomous Regulation 2024',
    'created_at' => now(),
    'updated_at' => now()
  ]);
  echo "✓ Created Regulation (ID: $regulationId)\n";
} else {
  $regulationId = $regulation->id;
  echo "✓ Using existing Regulation (ID: $regulationId)\n";
}

// Create a program if doesn't exist
$program = DB::table('programs')->first();
if (!$program) {
  $programId = DB::table('programs')->insertGetId([
    'name' => 'B.Sc. Computer Science',
    'code' => 'BSC-CS',
    'type' => 'Undergraduate',
    'created_at' => now(),
    'updated_at' => now()
  ]);
  echo "✓ Created Program (ID: $programId)\n";
} else {
  $programId = $program->id;
  echo "✓ Using existing Program (ID: $programId)\n";
}

// Create a program_regulation
$progReg = DB::table('program_regulations')->first();
if (!$progReg) {
  $progRegId = DB::table('program_regulations')->insertGetId([
    'program_id' => $programId,
    'regulation_name' => 'Regulation 2024',
    'regulation_type' => 'Semester',
    'start_year' => 2024,
    'created_at' => now(),
    'updated_at' => now()
  ]);
  echo "✓ Created Program Regulation (ID: $progRegId)\n";
} else {
  $progRegId = $progReg->id;
  echo "✓ Using existing Program Regulation (ID: $progRegId)\n";
}

// Create an exam
$examId = DB::table('exams')->insertGetId([
  'program_id' => $programId,
  'regulation_id' => $progRegId,
  'name' => 'End Semester Examination - December 2025',
  'exam_type' => 'Regular',
  'exam_date' => '2025-12-10',
  'start_date' => '2025-12-10',
  'end_date' => '2025-12-20',
  'status' => 'published',
  'created_at' => now(),
  'updated_at' => now()
]);
echo "✓ Created Exam (ID: $examId)\n";

// Create exam_student
$examStudent = DB::table('exam_students')->where('erp_student_id', 6795)->first();
if (!$examStudent) {
  $examStudentId = DB::table('exam_students')->insertGetId([
    'erp_student_id' => 6795,
    'program_id' => $programId,
    'enrollment_no' => 'EN2025006795',
    'status' => 'active',
    'created_at' => now(),
    'updated_at' => now()
  ]);
  echo "✓ Created Exam Student (ID: $examStudentId)\n";
} else {
  $examStudentId = $examStudent->id;
  echo "✓ Using existing Exam Student (ID: $examStudentId)\n";
}

// Create exam_session
$sessionId = DB::table('exam_sessions')->insertGetId([
  'name' => 'December 2025 Session',
  'academic_year' => '2025-2026',
  'semester' => 3,
  'program_type' => 'UG',
  'regulation_id' => $progRegId,
  'start_date' => '2025-12-10',
  'end_date' => '2025-12-20',
  'created_at' => now(),
  'updated_at' => now()
]);
echo "✓ Created Exam Session (ID: $sessionId)\n";

// Create exam_registration
$registrationId = DB::table('exam_registrations')->insertGetId([
  'erp_student_id' => 6795,
  'exam_session_id' => $sessionId,
  'program_type' => 'regular',
  'is_backlog' => false,
  'status' => 'approved',
  'registered_at' => now(),
  'created_at' => now(),
  'updated_at' => now()
]);
echo "✓ Created Registration (ID: $registrationId)\n";

// Find or create a room
$room = DB::table('rooms')->first();
if (!$room) {
  // Try room_masters
  $room = DB::table('room_masters')->first();
}
$roomId = $room ? $room->id : null;

if ($roomId) {
  // Create seating allocation
  $seatId = DB::table('seating_allocations')->insertGetId([
    'exam_student_id' => $examStudentId,
    'room_id' => $roomId,
    'seat_no' => 'A-15',
    'created_at' => now(),
    'updated_at' => now()
  ]);
  echo "✓ Created Seating Allocation (Seat: A-15, Room ID: $roomId)\n";
} else {
  echo "⚠ Warning: No rooms found in database, seating not created\n";
}

// Create dummy number
$dummyNumber = 'DEC2025' . str_pad($examStudentId, 4, '0', STR_PAD_LEFT);
$dummyId = DB::table('dummy_numbers')->insertGetId([
  'exam_id' => $examId,
  'exam_student_id' => $examStudentId,
  'dummy_number' => $dummyNumber,
  'created_at' => now(),
  'updated_at' => now()
]);
echo "✓ Created Dummy Number ($dummyNumber)\n";

echo "\n" . str_repeat('=', 60) . "\n";
echo "✅ Test data created successfully!\n\n";
echo "Registration ID: $registrationId\n";
echo "View Admit Card: /erp/admin/exam-reports/admit-cards/$registrationId\n";
echo "Download PDF: /erp/admin/exam-reports/admit-cards/$registrationId/download\n";
echo str_repeat('=', 60) . "\n";
