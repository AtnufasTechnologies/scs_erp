<?php

namespace App\Http\Controllers;

use App\Models\Quiz;
use App\Models\QuizAttempt;
use App\Models\QuizAttemptAnswer;
use App\Models\QuizAttemptPermission;
use App\Models\StudentMaster;
use App\Models\StudentMasterUserPivot;
use App\Models\SupCiaComponent;
use App\Models\User;
use App\Models\UserHasRole;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;

class StudentQuizController extends Controller
{
  private const ROLLNO_VERIFIED_SESSION_KEY = 'fa1_exam_rollno_verified';
  private const STUDENT_ID_SESSION_KEY = 'fa1_exam_student_id';

  public function accessPage()
  {
    return view('student.quiz.entry');
  }

  public function logout()
  {
    session()->forget([
      self::STUDENT_ID_SESSION_KEY,
      self::ROLLNO_VERIFIED_SESSION_KEY,
    ]);

    Auth::logout();

    return redirect()->route('student.fa1.access')
      ->with('success', 'Logged out successfully.');
  }

  public function verifyAccess(Request $request)
  {
    $request->validate([
      'roll_no' => 'required|string|max:50',
      'password' => 'required|string|min:6',
    ]);

    $enteredRollNo = strtolower(trim((string) $request->input('roll_no')));

    $authUser = User::whereRaw('LOWER(TRIM(roll_no)) = ?', [$enteredRollNo])
      ->where('status', 'ACTIVE')
      ->first();

    if (!$authUser || !Hash::check((string) $request->input('password'), (string) $authUser->password)) {
      return redirect()->route('student.fa1.access')
        ->with('error', 'Invalid login credentials. Please check roll number and password.');
    }

    $roleName = strtolower((string) UserHasRole::where('user_id', (int) $authUser->id)->value('role_name'));
    if (!in_array($roleName, ['student', 'alumni'], true)) {
      return redirect()->route('student.fa1.access')
        ->with('error', 'Unauthorized login for FA1 examination portal.');
    }

    Auth::login($authUser, true);

    $student = $this->resolveStudentForUser($authUser);
    if (!$student) {
      return redirect()->route('student.fa1.access')
        ->with('error', 'Student profile not found for this login. Please login with your student account.');
    }

    if (!in_array($enteredRollNo, $this->studentLoginAliases($student), true)) {
      return redirect()->route('student.fa1.access')
        ->with('error', 'Roll number mismatch. Please enter your own registered roll number.');
    }

    session([
      self::STUDENT_ID_SESSION_KEY => (int) $student->id,
      self::ROLLNO_VERIFIED_SESSION_KEY => $enteredRollNo,
    ]);

    return redirect()->route('student.fa1.index')
      ->with('success', 'Roll number verified. You can now attend FA1 exams.');
  }

  public function index()
  {
    $student = $this->resolveCurrentStudent();

    if (!$student) {
      return redirect()->route('student.fa1.access')
        ->with('error', 'Student profile not found for this login.');
    }

    session([self::STUDENT_ID_SESSION_KEY => (int) $student->id]);

    if (!$this->isRollNoVerified($student)) {
      return redirect()->route('student.fa1.access')
        ->with('info', 'Enter your roll number to access FA1 examination portal.');
    }

    $this->autoSubmitExpiredAttempts($student->id);

    $quizzes = $this->portalQuizQuery($student)
      ->with([
        'course:id,course_title,course_code',
        'subject:id,title',
      ])
      ->orderBy('open_at')
      ->get();

    $attempts = QuizAttempt::where('student_id', $student->id)
      ->whereIn('quiz_id', $quizzes->pluck('id'))
      ->orderByDesc('attempt_no')
      ->get();

    $attemptsByQuiz = $attempts->groupBy('quiz_id');

    $permissionMap = QuizAttemptPermission::whereIn('quiz_id', $quizzes->pluck('id'))
      ->where('student_id', $student->id)
      ->pluck('max_attempts', 'quiz_id');

    $attemptSummary = [];
    foreach ($quizzes as $quiz) {
      $quizAttempts = $attemptsByQuiz->get($quiz->id, collect());
      $submittedCount = $quizAttempts->where('status', 'submitted')->count();
      $inProgress = $quizAttempts->firstWhere('status', 'in_progress');
      $maxAttempts = (int) ($permissionMap[$quiz->id] ?? 1);

      $attemptSummary[$quiz->id] = [
        'submitted_count' => $submittedCount,
        'has_in_progress' => (bool) $inProgress,
        'max_attempts' => $maxAttempts,
        'latest_attempt' => $quizAttempts->first(),
      ];
    }

    return view('student.quiz.index', [
      'quizzes' => $quizzes,
      'attemptSummary' => $attemptSummary,
    ]);
  }

  public function lobby($id)
  {
    $student = $this->resolveCurrentStudent();
    if (!$student) {
      return redirect()->route('student.fa1.index')->with('error', 'Student profile not found for this login.');
    }

    if (!$this->isRollNoVerified($student)) {
      return redirect()->route('student.fa1.index')->with('error', 'Please enter your roll number to access FA1 examination portal.');
    }

    $quiz = $this->portalQuizQuery($student)
      ->with([
        'course:id,course_title,course_code',
        'subject:id,title',
      ])
      ->findOrFail($id);

    $this->autoSubmitExpiredAttempts($student->id, $quiz->id);

    $maxAttempts = $this->maxAttemptsAllowed($quiz->id, $student->id);
    $submittedCount = QuizAttempt::where('quiz_id', $quiz->id)
      ->where('student_id', $student->id)
      ->where('status', 'submitted')
      ->count();

    $inProgressAttempt = QuizAttempt::where('quiz_id', $quiz->id)
      ->where('student_id', $student->id)
      ->where('status', 'in_progress')
      ->orderByDesc('attempt_no')
      ->first();

    $hasRemainingAttempts = $submittedCount < $maxAttempts || (bool) $inProgressAttempt;
    $secondsUntilOpen = now()->lt($quiz->open_at) ? now()->diffInSeconds($quiz->open_at, false) : 0;
    $preStartCountdown = max(0, (int) ($quiz->pre_start_countdown_seconds ?? 10));

    return view('student.quiz.lobby', compact(
      'quiz',
      'maxAttempts',
      'submittedCount',
      'inProgressAttempt',
      'hasRemainingAttempts',
      'secondsUntilOpen',
      'preStartCountdown'
    ));
  }

  public function start($id)
  {
    $student = $this->resolveCurrentStudent();
    if (!$student) {
      return redirect()->route('student.fa1.index')->with('error', 'Student profile not found for this login.');
    }

    if (!$this->isRollNoVerified($student)) {
      return redirect()->route('student.fa1.index')->with('error', 'Please enter your roll number to access FA1 examination portal.');
    }

    $quiz = $this->eligibleQuizQuery($student)->findOrFail($id);

    $this->autoSubmitExpiredAttempts($student->id, $quiz->id);

    $attempt = QuizAttempt::where('quiz_id', $quiz->id)
      ->where('student_id', $student->id)
      ->where('status', 'in_progress')
      ->orderByDesc('attempt_no')
      ->first();

    if (!$attempt) {
      $maxAttempts = $this->maxAttemptsAllowed($quiz->id, $student->id);
      $submittedCount = QuizAttempt::where('quiz_id', $quiz->id)
        ->where('student_id', $student->id)
        ->where('status', 'submitted')
        ->count();

      if ($submittedCount >= $maxAttempts) {
        return redirect()->route('student.fa1.lobby', $quiz->id)->with('info', 'No remaining attempts for this exam.');
      }

      $attemptNo = $submittedCount + 1;
      $expiresAt = $this->calculateAttemptExpiry($quiz, now());

      $attempt = QuizAttempt::create([
        'quiz_id' => $quiz->id,
        'student_id' => $student->id,
        'attempt_no' => $attemptNo,
        'status' => 'in_progress',
        'started_at' => now(),
        'expires_at' => $expiresAt,
      ]);
    }

    return redirect()->route('student.fa1.show', $quiz->id);
  }

  public function show($id)
  {
    $student = $this->resolveCurrentStudent();
    if (!$student) {
      return redirect()->route('student.fa1.index')->with('error', 'Student profile not found for this login.');
    }

    if (!$this->isRollNoVerified($student)) {
      return redirect()->route('student.fa1.index')->with('error', 'Please enter your roll number to access FA1 examination portal.');
    }

    $quiz = $this->eligibleQuizQuery($student)->with(['questions.options'])->findOrFail($id);

    $this->autoSubmitExpiredAttempts($student->id, $quiz->id);

    $maxAttempts = $this->maxAttemptsAllowed($quiz->id, $student->id);

    $attempt = QuizAttempt::where('quiz_id', $quiz->id)
      ->where('student_id', $student->id)
      ->where('status', 'in_progress')
      ->orderByDesc('attempt_no')
      ->first();

    if (!$attempt) {
      return redirect()->route('student.fa1.lobby', $quiz->id)->with('info', 'Click Start Exam to begin your attempt.');
    }

    if ($attempt->expires_at && now()->greaterThan($attempt->expires_at)) {
      $this->finalizeAttempt($attempt, true);
      return redirect()->route('student.fa1.index')->with('info', 'Quiz auto-submitted because time limit expired.');
    }

    $questionItems = $this->orderedQuestionsForAttempt($quiz, $attempt);
    $savedAnswers = QuizAttemptAnswer::where('quiz_attempt_id', $attempt->id)
      ->pluck('quiz_question_option_id', 'quiz_question_id')
      ->toArray();

    $remainingSeconds = null;
    if ($attempt->expires_at) {
      $remainingSeconds = max(0, now()->diffInSeconds($attempt->expires_at, false));
    }

    return view('student.quiz.show', compact(
      'quiz',
      'attempt',
      'questionItems',
      'savedAnswers',
      'remainingSeconds',
      'maxAttempts'
    ));
  }

  public function saveAnswer(Request $request, $id)
  {
    $student = $this->resolveCurrentStudent();
    if (!$student) {
      return response()->json(['message' => 'Student profile not found.'], 422);
    }

    if (!$this->isRollNoVerified($student)) {
      return response()->json(['message' => 'Roll number verification required.'], 422);
    }

    $quiz = $this->eligibleQuizQuery($student)->with(['questions.options'])->findOrFail($id);

    $request->validate([
      'question_id' => 'required|integer',
      'option_id' => 'nullable|integer',
    ]);

    $attempt = QuizAttempt::where('quiz_id', $quiz->id)
      ->where('student_id', $student->id)
      ->where('status', 'in_progress')
      ->orderByDesc('attempt_no')
      ->first();

    if (!$attempt) {
      return response()->json(['message' => 'No active attempt found.'], 422);
    }

    if ($attempt->expires_at && now()->greaterThan($attempt->expires_at)) {
      $this->finalizeAttempt($attempt, true);
      return response()->json(['message' => 'Time limit expired. Quiz auto-submitted.'], 422);
    }

    $question = $quiz->questions->firstWhere('id', (int) $request->question_id);
    if (!$question) {
      return response()->json(['message' => 'Invalid question.'], 422);
    }

    $selectedOption = null;
    if ($request->filled('option_id')) {
      $selectedOption = $question->options->firstWhere('id', (int) $request->option_id);
      if (!$selectedOption) {
        return response()->json(['message' => 'Invalid option.'], 422);
      }
    }

    QuizAttemptAnswer::updateOrCreate(
      [
        'quiz_attempt_id' => $attempt->id,
        'quiz_question_id' => $question->id,
      ],
      [
        'quiz_question_option_id' => $selectedOption?->id,
        'is_correct' => $selectedOption ? (bool) $selectedOption->is_correct : false,
      ]
    );

    return response()->json(['message' => 'Saved']);
  }

  public function submit(Request $request, $id)
  {
    $student = $this->resolveCurrentStudent();
    if (!$student) {
      return redirect()->route('student.fa1.index')->with('error', 'Student profile not found for this login.');
    }

    if (!$this->isRollNoVerified($student)) {
      return redirect()->route('student.fa1.index')->with('error', 'Please enter your roll number to access FA1 examination portal.');
    }

    $quiz = $this->eligibleQuizQuery($student)->with(['questions.options'])->findOrFail($id);

    $this->autoSubmitExpiredAttempts($student->id, $quiz->id);

    $attempt = QuizAttempt::where('quiz_id', $quiz->id)
      ->where('student_id', $student->id)
      ->where('status', 'in_progress')
      ->orderByDesc('attempt_no')
      ->first();

    if (!$attempt) {
      return redirect()->route('student.fa1.index')->with('info', 'Quiz attempt not found or already submitted.');
    }

    $answers = $request->input('answers', []);
    foreach ($quiz->questions as $question) {
      $selectedOptionId = $answers[$question->id] ?? null;
      if (!$selectedOptionId) {
        continue;
      }

      $selectedOption = $question->options->firstWhere('id', (int) $selectedOptionId);
      if (!$selectedOption) {
        continue;
      }

      QuizAttemptAnswer::updateOrCreate(
        [
          'quiz_attempt_id' => $attempt->id,
          'quiz_question_id' => $question->id,
        ],
        [
          'quiz_question_option_id' => $selectedOption->id,
          'is_correct' => (bool) $selectedOption->is_correct,
        ]
      );
    }

    $timedOut = $attempt->expires_at && now()->greaterThan($attempt->expires_at);
    $this->finalizeAttempt($attempt, $timedOut || $request->boolean('auto_timeout'));

    return redirect()->route('student.fa1.index')->with('success', 'Quiz submitted successfully. Marks have been recorded.');
  }

  private function resolveCurrentStudent(): ?StudentMaster
  {
    $user = Auth::user();
    if ($user) {
      return $this->resolveStudentForUser($user);
    }

    $sessionStudentId = (int) session(self::STUDENT_ID_SESSION_KEY, 0);
    if ($sessionStudentId > 0) {
      return StudentMaster::where('id', $sessionStudentId)->first();
    }

    return null;
  }

  private function resolveStudentForUser(User $user): ?StudentMaster
  {
    if (!empty($user->student_id)) {
      $byId = StudentMaster::where('id', (int) $user->student_id)->first();
      if ($byId) {
        return $byId;
      }
    }

    if (!empty($user->roll_no)) {
      $normalizedRoll = strtolower(trim((string) $user->roll_no));
      $byRoll = StudentMaster::whereRaw('LOWER(TRIM(roll_no)) = ?', [$normalizedRoll])->first();
      if ($byRoll) {
        return $byRoll;
      }

      $byRegisterNo = StudentMaster::whereRaw('LOWER(TRIM(register_no)) = ?', [$normalizedRoll])->first();
      if ($byRegisterNo) {
        return $byRegisterNo;
      }

      $byUniversityRegisterNo = StudentMaster::whereRaw('LOWER(TRIM(university_register_no)) = ?', [$normalizedRoll])->first();
      if ($byUniversityRegisterNo) {
        return $byUniversityRegisterNo;
      }
    }

    if (!empty($user->email)) {
      $normalizedEmail = strtolower(trim((string) $user->email));
      $byEmail = StudentMaster::whereRaw('LOWER(TRIM(mail_id)) = ?', [$normalizedEmail])->first();
      if ($byEmail) {
        return $byEmail;
      }
    }

    $pivotStudentId = (int) StudentMasterUserPivot::where('user_id', (int) $user->id)->value('student_master_id');
    if ($pivotStudentId > 0) {
      $byPivot = StudentMaster::where('id', $pivotStudentId)->first();
      if ($byPivot) {
        return $byPivot;
      }
    }

    return null;
  }

  private function eligibleQuizQuery(StudentMaster $student)
  {
    $now = now();

    return $this->portalQuizQuery($student)
      ->where('open_at', '<=', $now)
      ->orderBy('open_at', 'desc');
  }

  private function portalQuizQuery(StudentMaster $student)
  {
    $now = now();
    $fa1ComponentId = $this->fa1ComponentId();

    return Quiz::query()
      ->where('is_published', true)
      ->where(function ($query) use ($now) {
        $query->whereNull('close_at')->orWhere('close_at', '>=', $now);
      })
      ->where(function ($query) use ($fa1ComponentId) {
        if ($fa1ComponentId) {
          $query->where('sup_cia_component_id', $fa1ComponentId);
          return;
        }

        $query->whereRaw('1 = 0');
      })
      ->whereExists(function ($query) use ($student) {
        $query->select(DB::raw(1))
          ->from('student_course_infos')
          ->whereColumn('student_course_infos.course_id', 'quizzes.course_id')
          ->where('student_course_infos.student_id', $student->id)
          ->where(function ($semesterQuery) {
            $semesterQuery->whereColumn('student_course_infos.semester', 'quizzes.semester_id')
              ->orWhereNull('quizzes.semester_id');
          });
      })
      ->whereExists(function ($query) use ($student) {
        $query->select(DB::raw(1))
          ->from('subject_has_student_progams')
          ->whereColumn('subject_has_student_progams.subject_id', 'quizzes.subject_id')
          ->whereColumn('subject_has_student_progams.batch_id', 'quizzes.batch_id')
          ->where('subject_has_student_progams.student_program_id', $student->new_program_id);
      });
  }

  private function fa1ComponentId(): ?int
  {
    $component = SupCiaComponent::where('IS_DELETED', 0)
      ->orderBy('id')
      ->get()
      ->first(function ($item) {
        $normalized = preg_replace('/[^A-Z0-9]/', '', strtoupper((string) $item->name));
        return in_array($normalized, ['FA1', 'FAI'], true);
      });

    return $component?->id;
  }

  private function isRollNoVerified(StudentMaster $student): bool
  {
    $sessionRoll = strtolower(trim((string) session(self::ROLLNO_VERIFIED_SESSION_KEY)));
    if ($sessionRoll === '') {
      return false;
    }

    return in_array($sessionRoll, $this->studentLoginAliases($student), true);
  }

  private function studentLoginAliases(StudentMaster $student): array
  {
    return array_values(array_filter(array_unique([
      strtolower(trim((string) $student->roll_no)),
      strtolower(trim((string) $student->register_no)),
      strtolower(trim((string) $student->university_register_no)),
    ])));
  }

  private function maxAttemptsAllowed(int $quizId, int $studentId): int
  {
    $allowed = QuizAttemptPermission::where('quiz_id', $quizId)
      ->where('student_id', $studentId)
      ->value('max_attempts');

    return (int) ($allowed ?? 1);
  }

  private function calculateAttemptExpiry(Quiz $quiz, Carbon $startedAt): ?Carbon
  {
    $timeLimitExpiry = null;
    if (!empty($quiz->time_limit_minutes)) {
      $timeLimitExpiry = $startedAt->copy()->addMinutes((int) $quiz->time_limit_minutes);
    }

    if ($timeLimitExpiry && $quiz->close_at) {
      return $timeLimitExpiry->lessThan($quiz->close_at) ? $timeLimitExpiry : $quiz->close_at->copy();
    }

    if ($timeLimitExpiry) {
      return $timeLimitExpiry;
    }

    return $quiz->close_at ? $quiz->close_at->copy() : null;
  }

  private function finalizeAttempt(QuizAttempt $attempt, bool $submittedByTimeout = false): void
  {
    $quiz = $attempt->quiz()->with(['questions.options'])->first();
    if (!$quiz || $attempt->status === 'submitted') {
      return;
    }

    DB::transaction(function () use ($attempt, $quiz, $submittedByTimeout) {
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
        ? round(($correctCount / $totalQuestions) * (float) $quiz->total_marks, 2)
        : 0;

      $attempt->update([
        'status' => 'submitted',
        'raw_score' => $correctCount,
        'total_questions' => $totalQuestions,
        'score' => $score,
        'submitted_at' => now(),
        'submitted_by_timeout' => $submittedByTimeout,
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
          'ENTRY_ID' => Auth::id(),
        ]
      );

      if (Schema::hasTable('fa_marks')) {
        DB::table('fa_marks')->upsert(
          [[
            'student_id' => $attempt->student_id,
            'course_id' => $quiz->course_id,
            'batch_id' => $quiz->batch_id,
            'semester_id' => $quiz->semester_id,
            'component_id' => $quiz->sup_cia_component_id,
            'attempt' => (int) $attempt->attempt_no,
            'score' => $score,
            'updated_at' => now(),
            'created_at' => now(),
          ]],
          ['student_id', 'course_id', 'batch_id', 'semester_id', 'component_id', 'attempt'],
          ['score', 'updated_at']
        );
      }
    });
  }

  private function autoSubmitExpiredAttempts(int $studentId, ?int $quizId = null): void
  {
    $query = QuizAttempt::where('student_id', $studentId)
      ->where('status', 'in_progress')
      ->whereNotNull('expires_at')
      ->where('expires_at', '<=', now());

    if ($quizId) {
      $query->where('quiz_id', $quizId);
    }

    $attempts = $query->get();
    foreach ($attempts as $attempt) {
      $this->finalizeAttempt($attempt, true);
    }
  }

  private function orderedQuestionsForAttempt(Quiz $quiz, QuizAttempt $attempt): array
  {
    $questionOrderKey = 'quiz_' . $quiz->id . '_attempt_' . $attempt->id . '_question_order';
    $optionOrderKey = 'quiz_' . $quiz->id . '_attempt_' . $attempt->id . '_option_order';

    $questionIds = $quiz->questions->pluck('id')->all();
    $sessionQuestionIds = session($questionOrderKey, []);
    if (empty($sessionQuestionIds) || count(array_intersect($sessionQuestionIds, $questionIds)) !== count($questionIds)) {
      $sessionQuestionIds = $questionIds;
      if ($quiz->shuffle_questions) {
        shuffle($sessionQuestionIds);
      }
      session([$questionOrderKey => $sessionQuestionIds]);
    }

    $sessionOptionOrders = session($optionOrderKey, []);
    $result = [];

    foreach ($sessionQuestionIds as $questionId) {
      $question = $quiz->questions->firstWhere('id', $questionId);
      if (!$question) {
        continue;
      }

      $optionIds = $question->options->pluck('id')->all();
      $stored = $sessionOptionOrders[$question->id] ?? [];
      if (empty($stored) || count(array_intersect($stored, $optionIds)) !== count($optionIds)) {
        $stored = $optionIds;
        if ($quiz->shuffle_options) {
          shuffle($stored);
        }
        $sessionOptionOrders[$question->id] = $stored;
      }

      $orderedOptions = [];
      foreach ($stored as $optionId) {
        $option = $question->options->firstWhere('id', $optionId);
        if ($option) {
          $orderedOptions[] = $option;
        }
      }

      $result[] = [
        'question' => $question,
        'options' => $orderedOptions,
      ];
    }

    session([$optionOrderKey => $sessionOptionOrders]);

    return $result;
  }
}
