<?php

namespace App\Console\Commands;

use App\Models\QuizAttempt;
use App\Models\QuizAttemptAnswer;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class AutoSubmitExpiredQuizAttempts extends Command
{
  protected $signature = 'quiz:auto-submit-expired';

  protected $description = 'Auto-submit all expired in-progress quiz attempts and write CIA marks';

  public function handle(): int
  {
    $attempts = QuizAttempt::where('status', 'in_progress')
      ->whereNotNull('expires_at')
      ->where('expires_at', '<=', now())
      ->with(['quiz.questions.options'])
      ->get();

    $submittedCount = 0;

    foreach ($attempts as $attempt) {
      $quiz = $attempt->quiz;
      if (!$quiz) {
        continue;
      }

      DB::transaction(function () use ($attempt, $quiz, &$submittedCount) {
        $answers = QuizAttemptAnswer::where('quiz_attempt_id', $attempt->id)
          ->pluck('quiz_question_option_id', 'quiz_question_id');

        $correctCount = 0;
        $totalQuestions = $quiz->questions->count();

        foreach ($quiz->questions as $question) {
          $selectedOptionId = $answers[$question->id] ?? null;
          if (!$selectedOptionId) {
            continue;
          }

          $selectedOption = $question->options->firstWhere('id', (int) $selectedOptionId);
          if ($selectedOption && $selectedOption->is_correct) {
            $correctCount++;
          }
        }

        $score = $totalQuestions > 0
          ? (int) round(($correctCount / $totalQuestions) * (float) $quiz->total_marks)
          : 0;

        $attempt->update([
          'status' => 'submitted',
          'raw_score' => $correctCount,
          'total_questions' => $totalQuestions,
          'score' => $score,
          'submitted_at' => now(),
          'submitted_by_timeout' => true,
        ]);

        DB::table('cia_marks')->updateOrInsert(
          [
            'STUDENT_ID' => $attempt->student_id,
            'COURSE_ID' => $quiz->course_id,
            'COURSE_GROUP_ID' => $quiz->cia_group_id,
            'SEMESTER_ID' => $quiz->semester_id,
          ],
          [
            'COURSE_GROUP_MARK' => $score,
            'ENTRY_ID' => null,
          ]
        );

        $submittedCount++;
      });
    }

    $this->info("Auto-submitted {$submittedCount} expired attempt(s).");

    return self::SUCCESS;
  }
}
