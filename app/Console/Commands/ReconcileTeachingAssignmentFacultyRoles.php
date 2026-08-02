<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ReconcileTeachingAssignmentFacultyRoles extends Command
{
  /**
   * The name and signature of the console command.
   *
   * @var string
   */
  protected $signature = 'teaching-assignment:reconcile-faculty-roles
                            {--dry-run : Preview counts without writing changes}';

  /**
   * The console command description.
   *
   * @var string
   */
  protected $description = 'Backfill/reconcile Primary faculty role rows for teaching assignments and print summary counts';

  /**
   * Execute the console command.
   */
  public function handle(): int
  {
    if (!Schema::hasTable('teaching_assignments')) {
      $this->error('Table teaching_assignments does not exist.');
      return self::FAILURE;
    }

    if (!Schema::hasTable('teaching_assignment_faculties')) {
      $this->error('Table teaching_assignment_faculties does not exist. Run migrations first.');
      return self::FAILURE;
    }

    $dryRun = (bool) $this->option('dry-run');
    $now = now();

    $stats = [
      'total_assignments' => 0,
      'eligible_assignments' => 0,
      'backfilled' => 0,
      'upgraded' => 0,
      'skipped_primary_exists' => 0,
      'skipped_invalid_faculty' => 0,
    ];

    DB::table('teaching_assignments')
      ->select(['id', 'faculty_id'])
      ->orderBy('id')
      ->chunkById(300, function ($assignments) use (&$stats, $dryRun, $now) {
        foreach ($assignments as $assignment) {
          $stats['total_assignments']++;

          $assignmentId = (int) ($assignment->id ?? 0);
          $primaryFacultyId = (int) ($assignment->faculty_id ?? 0);

          if ($assignmentId <= 0 || $primaryFacultyId <= 0) {
            $stats['skipped_invalid_faculty']++;
            continue;
          }

          $stats['eligible_assignments']++;

          $hasPrimary = DB::table('teaching_assignment_faculties')
            ->where('teaching_assignment_id', $assignmentId)
            ->where('teaching_role', 'Primary')
            ->exists();

          if ($hasPrimary) {
            $stats['skipped_primary_exists']++;
            continue;
          }

          $existingPrimaryFacultyRow = DB::table('teaching_assignment_faculties')
            ->where('teaching_assignment_id', $assignmentId)
            ->where('faculty_id', $primaryFacultyId)
            ->first();

          if ($existingPrimaryFacultyRow) {
            $stats['upgraded']++;

            if (!$dryRun) {
              DB::table('teaching_assignment_faculties')
                ->where('id', (int) $existingPrimaryFacultyRow->id)
                ->update([
                  'teaching_role' => 'Primary',
                  'updated_at' => $now,
                ]);
            }

            continue;
          }

          $stats['backfilled']++;

          if (!$dryRun) {
            DB::table('teaching_assignment_faculties')->insert([
              'teaching_assignment_id' => $assignmentId,
              'faculty_id' => $primaryFacultyId,
              'teaching_role' => 'Primary',
              'created_at' => $now,
              'updated_at' => $now,
            ]);
          }
        }
      }, 'id');

    $this->info($dryRun
      ? 'Dry run complete. No database rows were changed.'
      : 'Reconciliation complete. Primary role rows have been reconciled.');

    $this->table(
      ['Metric', 'Count'],
      [
        ['Total assignments scanned', $stats['total_assignments']],
        ['Eligible assignments', $stats['eligible_assignments']],
        ['Backfilled (inserted)', $stats['backfilled']],
        ['Upgraded to Primary', $stats['upgraded']],
        ['Skipped (Primary exists)', $stats['skipped_primary_exists']],
        ['Skipped (invalid/missing faculty)', $stats['skipped_invalid_faculty']],
      ]
    );

    return self::SUCCESS;
  }
}
