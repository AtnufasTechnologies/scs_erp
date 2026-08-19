<?php

namespace App\Console\Commands;

use App\Models\Quiz;
use App\Models\QuizAttempt;
use App\Models\StudentCourseRoster;
use App\Models\SubjectHasRoutine;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class BackfillQuizTeachingAssignments extends Command
{
  protected $signature = 'quiz:backfill-teaching-assignment
        {--quiz_id= : Backfill only one quiz ID}
        {--created_by= : Backfill quizzes created by one user ID}
        {--dry-run : Preview changes without updating data}';

  protected $description = 'Backfill missing quizzes.teaching_assignment_id using routine and roster evidence';

  public function handle(): int
  {
    if (!Schema::hasColumn('quizzes', 'teaching_assignment_id')) {
      $this->error('Column quizzes.teaching_assignment_id does not exist in this environment.');
      return self::FAILURE;
    }

    $dryRun = (bool) $this->option('dry-run');
    $quizIdFilter = (int) ($this->option('quiz_id') ?? 0);
    $createdByFilter = (int) ($this->option('created_by') ?? 0);

    $query = Quiz::query()
      ->where(function ($q) {
        $q->whereNull('teaching_assignment_id')
          ->orWhere('teaching_assignment_id', '<=', 0);
      })
      ->orderBy('id');

    if ($quizIdFilter > 0) {
      $query->where('id', $quizIdFilter);
    }

    if ($createdByFilter > 0) {
      $query->where('created_by', $createdByFilter);
    }

    $totalScanned = 0;
    $resolved = 0;
    $updated = 0;
    $skipped = 0;

    $query->chunkById(200, function ($quizzes) use (&$totalScanned, &$resolved, &$updated, &$skipped, $dryRun) {
      foreach ($quizzes as $quiz) {
        $totalScanned++;

        $assignmentId = $this->resolveAssignmentIdForQuiz($quiz);
        if ($assignmentId <= 0) {
          $skipped++;
          $this->line("SKIP quiz={$quiz->id} syllabus={$quiz->syllabus_id} course={$quiz->course_id} (no candidate assignment)");
          continue;
        }

        $resolved++;

        if ($dryRun) {
          $this->info("DRY quiz={$quiz->id} -> teaching_assignment_id={$assignmentId}");
          continue;
        }

        Quiz::query()->where('id', (int) $quiz->id)->update([
          'teaching_assignment_id' => $assignmentId,
          'updated_at' => now(),
        ]);

        $updated++;
        $this->info("UPDATED quiz={$quiz->id} -> teaching_assignment_id={$assignmentId}");
      }
    });

    $this->newLine();
    $this->line('Backfill summary');
    $this->line('scanned=' . $totalScanned);
    $this->line('resolved=' . $resolved);
    $this->line('updated=' . ($dryRun ? 0 : $updated));
    $this->line('skipped=' . $skipped);

    if ($dryRun) {
      $this->comment('Dry-run mode: no DB rows were modified.');
    }

    return self::SUCCESS;
  }

  private function resolveAssignmentIdForQuiz(Quiz $quiz): int
  {
    $candidateIds = $this->candidateAssignmentIds($quiz, false);

    // Legacy rows can have assignment links only on soft-deleted routines.
    if ($candidateIds->isEmpty()) {
      $candidateIds = $this->candidateAssignmentIds($quiz, true);
    }

    if ($candidateIds->isEmpty()) {
      return 0;
    }

    if ($candidateIds->count() === 1) {
      return (int) $candidateIds->first();
    }

    $bestFromOverlap = $this->inferByAttemptOverlap($quiz, $candidateIds->all());
    if ($bestFromOverlap > 0) {
      return $bestFromOverlap;
    }

    return (int) $candidateIds->sort()->first();
  }

  private function candidateAssignmentIds(Quiz $quiz, bool $includeTrashed)
  {
    $query = SubjectHasRoutine::query()
      ->where('syllabus_id', (int) $quiz->syllabus_id)
      ->where('faculty_id', (int) $quiz->faculty_id)
      ->whereNotNull('teaching_assignment_id');

    if ($includeTrashed) {
      $query->withTrashed();
    } elseif (Schema::hasColumn('subject_has_routines', 'deleted_at')) {
      $query->whereNull('deleted_at');
    }

    return $query
      ->pluck('teaching_assignment_id')
      ->map(fn($id) => (int) $id)
      ->filter(fn($id) => $id > 0)
      ->unique()
      ->values();
  }

  private function inferByAttemptOverlap(Quiz $quiz, array $candidateIds): int
  {
    $attemptedStudentIds = QuizAttempt::query()
      ->where('quiz_id', (int) $quiz->id)
      ->pluck('student_id')
      ->map(fn($id) => (int) $id)
      ->filter(fn($id) => $id > 0)
      ->unique()
      ->values();

    if ($attemptedStudentIds->isEmpty()) {
      return 0;
    }

    $bestId = StudentCourseRoster::query()
      ->whereIn('ta_id', $candidateIds)
      ->where('course_id', (int) $quiz->course_id)
      ->whereIn('student_id', $attemptedStudentIds->all())
      ->select('ta_id', DB::raw('COUNT(DISTINCT student_id) as matched_students'))
      ->groupBy('ta_id')
      ->orderByDesc('matched_students')
      ->orderBy('ta_id')
      ->value('ta_id');

    return (int) ($bestId ?? 0);
  }
}
