<?php

namespace App\Console\Commands;

use App\Services\StudentRosterEngine;
use Illuminate\Console\Command;

class InspectStudentRosterCommand extends Command
{
  protected $signature = 'roster:inspect
        {course_id : ProgramCourseMaster ID}
        {--subject_id= : Subject ID context}
        {--batch_id= : Batch ID context}
        {--semester_id= : Semester ID context}
        {--program_type= : UG/PG context}
        {--delivery_type= : Delivery type override}
        {--selection_type= : Selection type override}
        {--teaching_group_id= : Explicit teaching group (allocation_group_id)}
        {--teaching_assignment_id= : Teaching assignment ID}
        {--json : Print full JSON payload}';

  protected $description = 'Inspect StudentRosterEngine output for a course and context';

  public function handle(StudentRosterEngine $engine): int
  {
    $courseId = (int) $this->argument('course_id');

    $context = [
      'subject_id' => (int) ($this->option('subject_id') ?? 0),
      'batch_id' => (int) ($this->option('batch_id') ?? 0),
      'semester_id' => (int) ($this->option('semester_id') ?? 0),
      'program_type' => (string) ($this->option('program_type') ?? ''),
      'delivery_type' => (string) ($this->option('delivery_type') ?? ''),
      'selection_type' => (string) ($this->option('selection_type') ?? ''),
      'teaching_group_id' => (int) ($this->option('teaching_group_id') ?? 0),
      'teaching_assignment_id' => (int) ($this->option('teaching_assignment_id') ?? 0),
    ];

    $roster = $engine->getRoster($courseId, $context)->values();

    $this->info('Roster size: ' . $roster->count());

    if ($roster->isEmpty()) {
      $this->line('No students resolved for the given context.');
      return self::SUCCESS;
    }

    if ((bool) $this->option('json')) {
      $this->line($roster->toJson(JSON_PRETTY_PRINT));
      return self::SUCCESS;
    }

    $rows = $roster->map(function ($row, $index) {
      return [
        '#' => $index + 1,
        'student_id' => (int) ($row['student_id'] ?? 0),
        'roll_no' => (string) ($row['roll_no'] ?? ''),
        'name' => (string) ($row['student_name'] ?? ''),
        'program_id' => (int) ($row['program_id'] ?? 0),
        'batch_id' => (int) ($row['batch_id'] ?? 0),
        'semester_id' => (int) ($row['semester_id'] ?? 0),
        'pathway' => (string) ($row['academic_pathway'] ?? ''),
        'track' => (string) ($row['degree_track'] ?? ''),
        'delivery_type' => (string) ($row['delivery_type'] ?? ''),
        'selection_type' => (string) ($row['selection_type'] ?? ''),
        'teaching_group_id' => (int) ($row['teaching_group_id'] ?? 0),
      ];
    })->all();

    $this->table([
      '#',
      'student_id',
      'roll_no',
      'name',
      'program_id',
      'batch_id',
      'semester_id',
      'pathway',
      'track',
      'delivery_type',
      'selection_type',
      'teaching_group_id',
    ], $rows);

    return self::SUCCESS;
  }
}
