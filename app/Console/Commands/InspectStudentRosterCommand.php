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

    $courseContext = (object) [
      'id' => $courseId,
      'delivery_type' => strtoupper(trim((string) ($this->option('delivery_type') ?? ''))),
      'selection_type' => strtoupper(trim((string) ($this->option('selection_type') ?? ''))),
      'semester_id' => (int) ($this->option('semester_id') ?? 0),
      'batch_id' => (int) ($this->option('batch_id') ?? 0),
    ];

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

    $report = $engine->explainRosterForCourse($courseContext, $context);
    $rows = collect($report['students'] ?? []);

    $this->info('Course: ' . (int) ($report['course']['course_id'] ?? 0));
    $this->info('Delivery: ' . (string) ($report['course']['delivery_type'] ?? ''));
    $this->info('Selection: ' . (string) ($report['course']['selection_type'] ?? ''));
    $this->info('Total candidates: ' . (int) ($report['total_candidates'] ?? 0));
    $this->info('Total roster: ' . (int) ($report['total_roster'] ?? 0));

    if ($rows->isEmpty()) {
      $this->line('No candidate students found for the given context.');
      return self::SUCCESS;
    }

    if ((bool) $this->option('json')) {
      $this->line(json_encode($report, JSON_PRETTY_PRINT));
      return self::SUCCESS;
    }

    $tableRows = $rows->map(function ($row, $index) {
      return [
        '#' => $index + 1,
        'student_id' => (int) ($row['student_id'] ?? 0),
        'name' => (string) ($row['name'] ?? ''),
        'program_id' => (int) ($row['program_id'] ?? 0),
        'batch_id' => (int) ($row['batch_id'] ?? 0),
        'semester_id' => (int) ($row['semester_id'] ?? 0),
        'pathway_id' => (int) ($row['academic_pathway_id'] ?? 0),
        'track_id' => (int) ($row['degree_track_id'] ?? 0),
        'rule' => (string) ($row['rule_code'] ?? ''),
        'source' => (string) ($row['roster_source'] ?? ''),
        'decision' => (string) ($row['decision'] ?? ''),
        'reason' => (string) ($row['reason_code'] ?? ''),
      ];
    })->all();

    $this->table([
      '#',
      'student_id',
      'name',
      'program_id',
      'batch_id',
      'semester_id',
      'pathway_id',
      'track_id',
      'rule',
      'source',
      'decision',
      'reason',
    ], $tableRows);

    return self::SUCCESS;
  }
}
