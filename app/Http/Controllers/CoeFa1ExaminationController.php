<?php

namespace App\Http\Controllers;

use App\Models\Quiz;
use App\Models\QuizAttempt;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CoeFa1ExaminationController extends Controller
{
  public function index(Request $request)
  {
    $search = trim((string) $request->query('search', ''));
    $status = trim((string) $request->query('status', 'all'));
    $fromDate = trim((string) $request->query('from_date', ''));
    $toDate = trim((string) $request->query('to_date', ''));

    if (!in_array($status, ['all', 'upcoming', 'live', 'completed'], true)) {
      $status = 'all';
    }

    $baseQuery = Quiz::query()
      ->with([
        'course:id,course_code,course_title',
        'subject:id,title',
        'faculty:id,FIRST_NAME,MIDDLE_NAME,LAST_NAME',
        'creator:id,name',
      ])
      ->withCount('questions')
      ->withCount([
        'attempts as submitted_attempts_count' => function ($query) {
          $query->where('status', 'submitted');
        }
      ]);

    if ($search !== '') {
      $baseQuery->where(function ($query) use ($search) {
        $query->where('title', 'like', '%' . $search . '%')
          ->orWhereHas('course', function ($courseQuery) use ($search) {
            $courseQuery->where('course_code', 'like', '%' . $search . '%')
              ->orWhere('course_title', 'like', '%' . $search . '%');
          })
          ->orWhereHas('faculty', function ($facultyQuery) use ($search) {
            $facultyQuery->where('FIRST_NAME', 'like', '%' . $search . '%')
              ->orWhere('MIDDLE_NAME', 'like', '%' . $search . '%')
              ->orWhere('LAST_NAME', 'like', '%' . $search . '%');
          });
      });
    }

    if ($fromDate !== '') {
      $baseQuery->whereDate('open_at', '>=', $fromDate);
    }

    if ($toDate !== '') {
      $baseQuery->whereDate('open_at', '<=', $toDate);
    }

    $now = now();
    $this->applyStatusFilter($baseQuery, $status, $now);

    $quizzes = (clone $baseQuery)
      ->orderByDesc('open_at')
      ->orderByDesc('id')
      ->paginate(20)
      ->withQueryString();

    $statsBase = Quiz::query();
    if ($search !== '') {
      $statsBase->where(function ($query) use ($search) {
        $query->where('title', 'like', '%' . $search . '%')
          ->orWhereHas('course', function ($courseQuery) use ($search) {
            $courseQuery->where('course_code', 'like', '%' . $search . '%')
              ->orWhere('course_title', 'like', '%' . $search . '%');
          })
          ->orWhereHas('faculty', function ($facultyQuery) use ($search) {
            $facultyQuery->where('FIRST_NAME', 'like', '%' . $search . '%')
              ->orWhere('MIDDLE_NAME', 'like', '%' . $search . '%')
              ->orWhere('LAST_NAME', 'like', '%' . $search . '%');
          });
      });
    }
    if ($fromDate !== '') {
      $statsBase->whereDate('open_at', '>=', $fromDate);
    }
    if ($toDate !== '') {
      $statsBase->whereDate('open_at', '<=', $toDate);
    }

    $totalQuizzes = (clone $statsBase)->count();
    $upcomingCount = (clone $statsBase)->whereNotNull('open_at')->where('open_at', '>', $now)->count();
    $completedCount = (clone $statsBase)->whereNotNull('close_at')->where('close_at', '<=', $now)->count();
    $liveCount = max(0, $totalQuizzes - $upcomingCount - $completedCount);

    $totalSubmittedAttempts = QuizAttempt::query()
      ->where('status', 'submitted')
      ->whereIn('quiz_id', (clone $statsBase)->select('id'))
      ->count();

    return view('coe.fa1-examinations.index', [
      'quizzes' => $quizzes,
      'search' => $search,
      'status' => $status,
      'fromDate' => $fromDate,
      'toDate' => $toDate,
      'totalQuizzes' => $totalQuizzes,
      'upcomingCount' => $upcomingCount,
      'liveCount' => $liveCount,
      'completedCount' => $completedCount,
      'totalSubmittedAttempts' => $totalSubmittedAttempts,
    ]);
  }

  public function show(int $quizId)
  {
    $quiz = Quiz::query()
      ->with([
        'course:id,course_code,course_title',
        'subject:id,title',
        'faculty:id,FIRST_NAME,MIDDLE_NAME,LAST_NAME',
        'creator:id,name',
      ])
      ->withCount('questions')
      ->findOrFail($quizId);

    $attemptsQuery = QuizAttempt::query()
      ->where('quiz_id', $quiz->id)
      ->where('status', 'submitted');

    $attempts = (clone $attemptsQuery)
      ->with('student:id,first_name,last_name,roll_no,register_no')
      ->orderByDesc('score')
      ->orderByDesc('submitted_at')
      ->paginate(30)
      ->withQueryString();

    $attemptedStudentCount = (clone $attemptsQuery)
      ->distinct('student_id')
      ->count('student_id');

    $averageScore = (clone $attemptsQuery)->avg('score');
    $highestScore = (clone $attemptsQuery)->max('score');
    $lowestScore = (clone $attemptsQuery)->min('score');
    $latestSubmissionAt = (clone $attemptsQuery)->max('submitted_at');

    $questionAccuracy = DB::table('quiz_questions as qq')
      ->leftJoin('quiz_attempt_answers as qaa', 'qaa.quiz_question_id', '=', 'qq.id')
      ->leftJoin('quiz_attempts as qa', function ($join) use ($quiz) {
        $join->on('qa.id', '=', 'qaa.quiz_attempt_id')
          ->where('qa.quiz_id', '=', $quiz->id)
          ->where('qa.status', '=', 'submitted');
      })
      ->where('qq.quiz_id', $quiz->id)
      ->groupBy('qq.id', 'qq.position', 'qq.question_text')
      ->selectRaw('qq.id, qq.position, qq.question_text, COUNT(qa.id) as total_answers, SUM(CASE WHEN qaa.is_correct = 1 THEN 1 ELSE 0 END) as correct_answers')
      ->orderBy('qq.position')
      ->get()
      ->map(function ($row) {
        $totalAnswers = (int) ($row->total_answers ?? 0);
        $correctAnswers = (int) ($row->correct_answers ?? 0);
        $accuracy = $totalAnswers > 0 ? round(($correctAnswers / $totalAnswers) * 100, 1) : 0;

        return [
          'position' => (int) ($row->position ?? 0),
          'question_text' => (string) ($row->question_text ?? ''),
          'total_answers' => $totalAnswers,
          'correct_answers' => $correctAnswers,
          'accuracy' => $accuracy,
        ];
      });

    return view('coe.fa1-examinations.show', [
      'quiz' => $quiz,
      'attempts' => $attempts,
      'attemptedStudentCount' => $attemptedStudentCount,
      'averageScore' => $averageScore,
      'highestScore' => $highestScore,
      'lowestScore' => $lowestScore,
      'latestSubmissionAt' => $latestSubmissionAt,
      'questionAccuracy' => $questionAccuracy,
    ]);
  }

  private function applyStatusFilter($query, string $status, $now): void
  {
    if ($status === 'upcoming') {
      $query->whereNotNull('open_at')->where('open_at', '>', $now);
      return;
    }

    if ($status === 'completed') {
      $query->whereNotNull('close_at')->where('close_at', '<=', $now);
      return;
    }

    if ($status === 'live') {
      $query->where(function ($q) use ($now) {
        $q->whereNull('open_at')->orWhere('open_at', '<=', $now);
      })->where(function ($q) use ($now) {
        $q->whereNull('close_at')->orWhere('close_at', '>', $now);
      });
    }
  }
}
