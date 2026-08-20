<?php

namespace App\Http\Controllers;

use App\Models\Quiz;
use App\Models\QuizAttempt;
use App\Models\QuizQuestion;
use App\Models\QuizQuestionOption;
use App\Models\StudentCourseRoster;
use App\Models\SubjectFacultyMaster;
use App\Models\SubjectHasDeptAdmin;
use App\Models\SubjectHasRoutine;
use App\Models\UserHasRole;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class QuizOversightController extends Controller
{
  public function index(Request $request)
  {
    $role = $this->resolveAuthorizedRole();

    $selectedDepartment = trim((string) $request->query('department', ''));
    $selectedStatus = trim((string) $request->query('status', 'all'));
    $selectedCourseCode = strtoupper(trim((string) $request->query('course_code', '')));
    $startDate = trim((string) $request->query('start_date', ''));
    $selectedStartAt = trim((string) $request->query('start_at', ''));
    $defaultGroupBy = $role === 'itcell' ? 'start_time' : 'none';
    $groupBy = trim((string) $request->query('group_by', $defaultGroupBy));
    $allowedStatuses = ['all', 'upcoming', 'live', 'completed'];
    $allowedGroupBy = ['none', 'start_time'];

    if (!in_array($selectedStatus, $allowedStatuses, true)) {
      $selectedStatus = 'all';
    }

    if (!in_array($groupBy, $allowedGroupBy, true)) {
      $groupBy = $defaultGroupBy;
    }

    $startDateValue = null;
    if ($startDate !== '') {
      try {
        $startDateValue = Carbon::parse($startDate)->toDateString();
        $startDate = $startDateValue;
      } catch (\Throwable $e) {
        $startDate = '';
      }
    }

    $startAtValue = null;
    if ($selectedStartAt !== '') {
      try {
        $startAtValue = Carbon::parse($selectedStartAt)->format('Y-m-d H:i:s');
        $selectedStartAt = $startAtValue;
      } catch (\Throwable $e) {
        $selectedStartAt = '';
      }
    }

    $now = now();
    $departmentLabelColumns = collect(['name', 'title', 'subject_name'])
      ->filter(fn($column) => Schema::hasColumn('department_masters', $column))
      ->values();

    $baseQuery = $this->scopedQuizQueryForRole($role);
    if ($role === 'dept-admin-erp') {
      // Department users are always limited to their own department scope.
      $selectedDepartment = '';
    }

    $filteredBaseQuery = (clone $baseQuery)
      ->when($selectedDepartment !== '' && $departmentLabelColumns->isNotEmpty(), function ($query) use ($selectedDepartment, $departmentLabelColumns) {
        $query->whereHas('faculty.department', function ($deptQuery) use ($selectedDepartment, $departmentLabelColumns) {
          $firstColumn = $departmentLabelColumns->first();
          $deptQuery->where($firstColumn, $selectedDepartment);

          foreach ($departmentLabelColumns->slice(1) as $column) {
            $deptQuery->orWhere($column, $selectedDepartment);
          }
        });
      })
      ->when($selectedCourseCode !== '', function ($query) use ($selectedCourseCode) {
        $query->whereHas('course', function ($courseQuery) use ($selectedCourseCode) {
          $courseQuery->whereRaw('UPPER(TRIM(course_code)) LIKE ?', ['%' . $selectedCourseCode . '%']);
        });
      });

    $listingQuery = clone $filteredBaseQuery;
    $this->applyStatusFilter($listingQuery, $selectedStatus, $now);

    if ($startDateValue !== null) {
      $listingQuery->whereDate('open_at', $startDateValue);
    }

    if ($startAtValue !== null) {
      $listingQuery->where('open_at', $startAtValue);
    }

    $quizRelations = [
      'course:id,course_title,course_code',
      'subject:id,title',
      'faculty:id,FIRST_NAME,MIDDLE_NAME,LAST_NAME,DEPARTMENT',
      'faculty.department',
      'creator:id,name',
      'questions:id,quiz_id,question_text,position',
      'attempts:id,quiz_id,student_id,status,score,submitted_at',
    ];

    $quizzes = (clone $listingQuery)->with($quizRelations)
      ->withCount('questions')
      ->withCount([
        'attempts as submitted_attempts_count' => function ($query) {
          $query->where('status', 'submitted');
        }
      ])
      ->orderByDesc('id')
      ->paginate(15)
      ->withQueryString();

    $startTimeStudentMap = [];
    $groupedQuizzesByStartTime = collect();

    $quizzes->getCollection()->transform(function ($quiz) use (&$startTimeStudentMap) {
      $studentIds = $this->expectedStudentIdsForQuiz($quiz);
      $quiz->expected_students_count = $studentIds->count();

      if ($quiz->open_at) {
        $startAtKey = $quiz->open_at->format('Y-m-d H:i:s');

        if (!array_key_exists($startAtKey, $startTimeStudentMap)) {
          $startTimeStudentMap[$startAtKey] = collect();
        }

        $startTimeStudentMap[$startAtKey] = $startTimeStudentMap[$startAtKey]
          ->merge($studentIds)
          ->unique()
          ->values();
      }

      return $quiz;
    });

    if ($groupBy === 'start_time') {
      $allFilteredQuizzes = (clone $listingQuery)
        ->with($quizRelations)
        ->withCount('questions')
        ->withCount([
          'attempts as submitted_attempts_count' => function ($query) {
            $query->where('status', 'submitted');
          }
        ])
        ->orderBy('open_at')
        ->orderByDesc('id')
        ->get();

      $startTimeStudentMap = [];

      $allFilteredQuizzes->transform(function ($quiz) use (&$startTimeStudentMap) {
        $studentIds = $this->expectedStudentIdsForQuiz($quiz);
        $quiz->expected_students_count = $studentIds->count();

        if ($quiz->open_at) {
          $startAtKey = $quiz->open_at->format('Y-m-d H:i:s');

          if (!array_key_exists($startAtKey, $startTimeStudentMap)) {
            $startTimeStudentMap[$startAtKey] = collect();
          }

          $startTimeStudentMap[$startAtKey] = $startTimeStudentMap[$startAtKey]
            ->merge($studentIds)
            ->unique()
            ->values();
        }

        return $quiz;
      });

      $groupedQuizzesByStartTime = $allFilteredQuizzes
        ->groupBy(function ($quiz) {
          return $quiz->open_at ? $quiz->open_at->format('Y-m-d H:i:s') : 'NO_START_TIME';
        })
        ->map(function ($items, $startAtKey) {
          $startAtLabel = $startAtKey === 'NO_START_TIME'
            ? 'No Start Time'
            : Carbon::parse($startAtKey)->format('d M Y h:i A');

          return [
            'start_at' => $startAtKey === 'NO_START_TIME' ? null : $startAtKey,
            'start_at_label' => $startAtLabel,
            'quiz_count' => (int) $items->count(),
            'quizzes' => $items->values(),
          ];
        })
        ->sortBy(function ($group) {
          return $group['start_at'] ?? '9999-12-31 23:59:59';
        })
        ->values();
    }

    $startTimeAnalytics = collect($startTimeStudentMap)
      ->map(function ($studentIds, $startAt) {
        return [
          'start_at' => $startAt,
          'start_at_label' => Carbon::parse($startAt)->format('d M Y h:i A'),
          'unique_students' => (int) collect($studentIds)->count(),
        ];
      })
      ->sortBy('start_at')
      ->values();

    $totalUniqueStudentsByStartTime = (int) $startTimeAnalytics->sum('unique_students');

    $departmentOptions = (clone $baseQuery)
      ->whereNotNull('faculty_id')
      ->with(['faculty.department'])
      ->get()
      ->map(function ($quiz) {
        $dept = optional($quiz->faculty)->department;
        return trim((string) ($dept->name ?? $dept->title ?? $dept->subject_name ?? ''));
      })
      ->filter()
      ->unique()
      ->sort()
      ->values();

    $statusCounts = [
      'all' => (clone $filteredBaseQuery)->count(),
      'upcoming' => $this->countByStatus($filteredBaseQuery, 'upcoming', $now),
      'live' => $this->countByStatus($filteredBaseQuery, 'live', $now),
      'completed' => $this->countByStatus($filteredBaseQuery, 'completed', $now),
    ];

    return view('quiz.oversight.index', [
      'quizzes' => $quizzes,
      'departmentOptions' => $departmentOptions,
      'selectedDepartment' => $selectedDepartment,
      'selectedStatus' => $selectedStatus,
      'selectedCourseCode' => $selectedCourseCode,
      'startDate' => $startDate,
      'selectedStartAt' => $selectedStartAt,
      'groupBy' => $groupBy,
      'statusCounts' => $statusCounts,
      'startTimeAnalytics' => $startTimeAnalytics,
      'totalUniqueStudentsByStartTime' => $totalUniqueStudentsByStartTime,
      'groupedQuizzesByStartTime' => $groupedQuizzesByStartTime,
      'role' => $role,
      'canFilterDepartments' => in_array($role, ['principal', 'itcell'], true),
      'showQuestionsInMonitor' => $role !== 'itcell',
      'monitorIndexRoute' => $this->monitorIndexRouteName($role),
      'monitorResultsRoute' => $this->monitorResultsRouteName($role),
    ]);
  }

  public function results(Request $request, int $quizId)
  {
    $role = $this->resolveAuthorizedRole();
    $baseQuery = $this->scopedQuizQueryForRole($role);

    $quiz = (clone $baseQuery)
      ->with([
        'course:id,course_title,course_code',
        'subject:id,title',
        'faculty:id,FIRST_NAME,MIDDLE_NAME,LAST_NAME,DEPARTMENT',
        'faculty.department',
        'creator:id,name',
      ])
      ->withCount([
        'questions',
        'attempts as submitted_attempts_count' => function ($query) {
          $query->where('status', 'submitted');
        }
      ])
      ->findOrFail($quizId);

    $attempts = QuizAttempt::query()
      ->where('quiz_id', (int) $quiz->id)
      ->where('status', 'submitted')
      ->with('student:id,first_name,last_name,roll_no,register_no')
      ->orderByDesc('score')
      ->orderByDesc('submitted_at')
      ->paginate(30)
      ->withQueryString();

    $attemptedStudentCount = QuizAttempt::query()
      ->where('quiz_id', (int) $quiz->id)
      ->where('status', 'submitted')
      ->distinct('student_id')
      ->count('student_id');

    $latestSubmissionAt = QuizAttempt::query()
      ->where('quiz_id', (int) $quiz->id)
      ->where('status', 'submitted')
      ->max('submitted_at');

    $avgScore = QuizAttempt::query()
      ->where('quiz_id', (int) $quiz->id)
      ->where('status', 'submitted')
      ->avg('score');

    return view('quiz.oversight.results', [
      'quiz' => $quiz,
      'attempts' => $attempts,
      'role' => $role,
      'attemptedStudentCount' => $attemptedStudentCount,
      'latestSubmissionAt' => $latestSubmissionAt,
      'averageScore' => $avgScore,
      'monitorIndexRoute' => $this->monitorIndexRouteName($role),
    ]);
  }

  public function exportQuestionSheet(int $quizId)
  {
    $role = $this->resolveAuthorizedRole();
    if ($role !== 'itcell') {
      abort(403, 'Only ITCELL can access this operation.');
    }

    $quiz = Quiz::query()
      ->with([
        'course:id,course_title,course_code',
        'subject:id,title',
      ])
      ->withCount('questions')
      ->findOrFail($quizId);

    $questions = QuizQuestion::query()
      ->where('quiz_id', (int) $quiz->id)
      ->with(['options' => function ($query) {
        $query->orderBy('position');
      }])
      ->orderBy('position')
      ->orderBy('id')
      ->get();

    $spreadsheet = new Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();
    $sheet->setTitle('Quiz Questions');

    $sheet->setCellValue('A1', 'Quiz ID');
    $sheet->setCellValue('B1', (int) $quiz->id);
    $sheet->setCellValue('A2', 'Quiz Title');
    $sheet->setCellValue('B2', (string) ($quiz->title ?? 'N/A'));
    $sheet->setCellValue('A3', 'Course');
    $sheet->setCellValue('B3', (string) (($quiz->course->course_code ?? 'N/A') . ' - ' . ($quiz->course->course_title ?? 'N/A')));
    $sheet->setCellValue('A4', 'Subject');
    $sheet->setCellValue('B4', (string) ($quiz->subject->title ?? 'N/A'));
    $sheet->setCellValue('A5', 'Instructions');
    $sheet->setCellValue('B5', 'Edit question_text, options and correct_option (1-4 or A-D), then upload this same file.');

    $headerRow = 7;
    $headers = [
      'question_no',
      'question_text',
      'option_1',
      'option_2',
      'option_3',
      'option_4',
      'correct_option',
    ];

    foreach ($headers as $index => $header) {
      $sheet->setCellValueByColumnAndRow($index + 1, $headerRow, $header);
    }

    $row = $headerRow + 1;
    foreach ($questions as $question) {
      $optionMap = $question->options
        ->mapWithKeys(function ($option) {
          return [(int) $option->position => $option];
        });

      $correctOption = $question->options
        ->firstWhere('is_correct', true);

      $sheet->setCellValueByColumnAndRow(1, $row, (int) ($question->position ?? $row - $headerRow));
      $sheet->setCellValueByColumnAndRow(2, $row, (string) ($question->question_text ?? ''));
      $sheet->setCellValueByColumnAndRow(3, $row, (string) optional($optionMap->get(1))->option_text);
      $sheet->setCellValueByColumnAndRow(4, $row, (string) optional($optionMap->get(2))->option_text);
      $sheet->setCellValueByColumnAndRow(5, $row, (string) optional($optionMap->get(3))->option_text);
      $sheet->setCellValueByColumnAndRow(6, $row, (string) optional($optionMap->get(4))->option_text);
      $sheet->setCellValueByColumnAndRow(7, $row, (string) ((int) ($correctOption->position ?? 1)));
      $row++;
    }

    foreach (range('A', 'G') as $column) {
      $sheet->getColumnDimension($column)->setAutoSize(true);
    }

    $filename = 'fa1_quiz_questions_' . (int) $quiz->id . '_' . now()->format('Ymd_His') . '.xlsx';

    return response()->streamDownload(function () use ($spreadsheet) {
      $writer = new Xlsx($spreadsheet);
      $writer->save('php://output');
    }, $filename, [
      'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
      'Cache-Control' => 'max-age=0, no-cache, no-store, must-revalidate',
      'Pragma' => 'no-cache',
      'Expires' => '0',
    ]);
  }

  public function importQuestionSheet(Request $request, int $quizId)
  {
    $role = $this->resolveAuthorizedRole();
    if ($role !== 'itcell') {
      abort(403, 'Only ITCELL can access this operation.');
    }

    $request->validate([
      'question_sheet' => 'required|file|mimes:xlsx,xls,csv|max:10240',
    ]);

    $quiz = Quiz::query()
      ->withCount('questions')
      ->findOrFail($quizId);

    try {
      $spreadsheet = IOFactory::load($request->file('question_sheet')->getRealPath());
      $rows = $spreadsheet->getActiveSheet()->toArray(null, true, true, true);
    } catch (\Throwable $e) {
      return redirect()->route('itcell.quizzes.results', $quizId)
        ->with('error', 'Unable to read uploaded file. Please use the exported template format.');
    }

    if (count($rows) < 8) {
      return redirect()->route('itcell.quizzes.results', $quizId)
        ->with('error', 'Uploaded sheet looks empty. Please use the exported template.');
    }

    $headerRowNumber = 7;
    $headerRow = $rows[$headerRowNumber] ?? [];
    $headerMap = [];
    foreach ($headerRow as $column => $value) {
      $header = strtolower(trim((string) $value));
      if ($header !== '') {
        $headerMap[$header] = $column;
      }
    }

    foreach (['question_no', 'question_text', 'option_1', 'option_2', 'option_3', 'option_4', 'correct_option'] as $requiredHeader) {
      if (!isset($headerMap[$requiredHeader])) {
        return redirect()->route('itcell.quizzes.results', $quizId)
          ->with('error', 'Missing required column: ' . $requiredHeader);
      }
    }

    $rowsToApply = collect();
    $invalidRows = 0;

    foreach ($rows as $rowNumber => $row) {
      if ($rowNumber <= $headerRowNumber) {
        continue;
      }

      $questionNoRaw = trim((string) ($row[$headerMap['question_no']] ?? ''));
      $questionText = trim((string) ($row[$headerMap['question_text']] ?? ''));
      $option1 = trim((string) ($row[$headerMap['option_1']] ?? ''));
      $option2 = trim((string) ($row[$headerMap['option_2']] ?? ''));
      $option3 = trim((string) ($row[$headerMap['option_3']] ?? ''));
      $option4 = trim((string) ($row[$headerMap['option_4']] ?? ''));
      $correctRaw = trim((string) ($row[$headerMap['correct_option']] ?? ''));

      if ($questionNoRaw === '' && $questionText === '' && $option1 === '' && $option2 === '' && $option3 === '' && $option4 === '' && $correctRaw === '') {
        continue;
      }

      if (!ctype_digit($questionNoRaw) || (int) $questionNoRaw <= 0) {
        $invalidRows++;
        continue;
      }

      $correctPosition = $this->normalizeCorrectOptionIndicator($correctRaw);
      if ($questionText === '' || $option1 === '' || $option2 === '' || $option3 === '' || $option4 === '' || $correctPosition === 0) {
        $invalidRows++;
        continue;
      }

      $rowsToApply->push([
        'position' => (int) $questionNoRaw,
        'question_text' => $questionText,
        'options' => [$option1, $option2, $option3, $option4],
        'correct_position' => $correctPosition,
      ]);
    }

    $rowsToApply = $rowsToApply
      ->sortBy('position')
      ->unique('position')
      ->unique()
      ->values();

    if ($rowsToApply->isEmpty()) {
      return redirect()->route('itcell.quizzes.results', $quizId)
        ->with('error', 'No valid question rows found in uploaded sheet.');
    }

    $updatedQuestions = 0;
    $createdQuestions = 0;
    $recalculatedAttempts = 0;

    DB::transaction(function () use ($quiz, $rowsToApply, &$updatedQuestions, &$createdQuestions, &$recalculatedAttempts) {
      $existingQuestions = QuizQuestion::query()
        ->where('quiz_id', (int) $quiz->id)
        ->with(['options' => function ($query) {
          $query->orderBy('position');
        }])
        ->get()
        ->keyBy(function ($question) {
          return (int) ($question->position ?? 0);
        });

      foreach ($rowsToApply as $rowData) {
        $position = (int) $rowData['position'];
        $question = $existingQuestions->get($position);

        if ($question) {
          $question->update([
            'question_text' => (string) $rowData['question_text'],
            'position' => $position,
          ]);
          $updatedQuestions++;
        } else {
          $question = QuizQuestion::query()->create([
            'quiz_id' => (int) $quiz->id,
            'question_text' => (string) $rowData['question_text'],
            'position' => $position,
          ]);
          $createdQuestions++;
        }

        $optionsByPosition = $question->options->keyBy(function ($option) {
          return (int) ($option->position ?? 0);
        });

        foreach ($rowData['options'] as $index => $optionText) {
          $optionPosition = $index + 1;
          $optionPayload = [
            'option_text' => (string) $optionText,
            'position' => $optionPosition,
            'is_correct' => $rowData['correct_position'] === $optionPosition,
          ];

          $existingOption = $optionsByPosition->get($optionPosition);
          if ($existingOption) {
            $existingOption->update($optionPayload);
          } else {
            QuizQuestionOption::query()->create(array_merge($optionPayload, [
              'quiz_question_id' => (int) $question->id,
            ]));
          }
        }
      }

      $recalculatedAttempts = $this->recalculateSubmittedAttemptScores($quiz);
    });

    $message = 'Question sheet imported. Updated questions=' . $updatedQuestions
      . ', created questions=' . $createdQuestions
      . ', recalculated attempts=' . $recalculatedAttempts;

    if ($invalidRows > 0) {
      $message .= ', invalid rows skipped=' . $invalidRows;
    }

    return redirect()->route('itcell.quizzes.results', $quizId)->with('success', $message . '.');
  }

  private function normalizeCorrectOptionIndicator(string $correctRaw): int
  {
    $normalized = strtoupper(trim($correctRaw));
    if ($normalized === '') {
      return 0;
    }

    if (is_numeric($normalized)) {
      $position = (int) $normalized;
      return in_array($position, [1, 2, 3, 4], true) ? $position : 0;
    }

    $map = ['A' => 1, 'B' => 2, 'C' => 3, 'D' => 4];
    return (int) ($map[$normalized] ?? 0);
  }

  private function recalculateSubmittedAttemptScores(Quiz $quiz): int
  {
    $submittedAttempts = QuizAttempt::query()
      ->where('quiz_id', (int) $quiz->id)
      ->where('status', 'submitted')
      ->get(['id', 'student_id', 'attempt_no']);

    $totalQuestions = (int) QuizQuestion::query()
      ->where('quiz_id', (int) $quiz->id)
      ->count();

    foreach ($submittedAttempts as $attempt) {
      DB::table('quiz_attempt_answers as qaa')
        ->leftJoin('quiz_question_options as qqo', 'qqo.id', '=', 'qaa.quiz_question_option_id')
        ->where('qaa.quiz_attempt_id', (int) $attempt->id)
        ->update([
          'qaa.is_correct' => DB::raw('COALESCE(qqo.is_correct, 0)'),
          'qaa.updated_at' => now(),
        ]);

      $correctCount = (int) DB::table('quiz_attempt_answers')
        ->where('quiz_attempt_id', (int) $attempt->id)
        ->where('is_correct', 1)
        ->count();

      $score = $totalQuestions > 0
        ? (int) round(($correctCount / $totalQuestions) * (float) ($quiz->total_marks ?? 0))
        : 0;

      QuizAttempt::query()
        ->where('id', (int) $attempt->id)
        ->update([
          'raw_score' => $correctCount,
          'total_questions' => $totalQuestions,
          'score' => $score,
          'updated_at' => now(),
        ]);

      DB::table('cia_marks')->updateOrInsert(
        [
          'STUDENT_ID' => (int) $attempt->student_id,
          'COURSE_ID' => (int) $quiz->course_id,
          'COURSE_GROUP_ID' => (int) $quiz->cia_group_id,
          'SEMESTER_ID' => (int) $quiz->semester_id,
        ],
        [
          'COURSE_GROUP_MARK' => $score,
          'ENTRY_ID' => (int) Auth::id(),
        ]
      );

      if (Schema::hasTable('fa_marks')) {
        DB::table('fa_marks')->upsert(
          [[
            'student_id' => (int) $attempt->student_id,
            'course_id' => (int) $quiz->course_id,
            'batch_id' => (int) $quiz->batch_id,
            'semester_id' => (int) $quiz->semester_id,
            'component_id' => (int) $quiz->sup_cia_component_id,
            'attempt' => (int) $attempt->attempt_no,
            'score' => $score,
            'updated_at' => now(),
            'created_at' => now(),
          ]],
          ['student_id', 'course_id', 'batch_id', 'semester_id', 'component_id', 'attempt'],
          ['score', 'updated_at']
        );
      }
    }

    return $submittedAttempts->count();
  }

  private function resolveAuthorizedRole(): string
  {
    $rawRole = (string) UserHasRole::where('user_id', Auth::id())->value('role_name');

    if (in_array($rawRole, ['itcell', 'admin', 'super-admin'], true)) {
      return 'itcell';
    }

    if (!in_array($rawRole, ['principal', 'dept-admin-erp'], true)) {
      abort(403, 'Unauthorized access.');
    }

    return $rawRole;
  }

  private function scopedQuizQueryForRole(string $role): Builder
  {
    $query = Quiz::query();

    if ($role !== 'dept-admin-erp') {
      return $query;
    }

    $subjectId = (int) SubjectHasDeptAdmin::where('user_id', Auth::id())->value('subject_id');
    $facultyIds = SubjectFacultyMaster::where('subject_id', $subjectId)->pluck('faculty_id');

    if ($subjectId <= 0 || $facultyIds->isEmpty()) {
      return $query->whereRaw('1 = 0');
    }

    return $query->whereIn('faculty_id', $facultyIds->all());
  }

  private function applyStatusFilter(Builder $query, string $status, $now): void
  {
    if ($status === 'upcoming') {
      $query->whereNotNull('open_at')->where('open_at', '>', $now);
      return;
    }

    if ($status === 'live') {
      $query
        ->whereNotNull('open_at')
        ->where('open_at', '<=', $now)
        ->where(function ($closeQuery) use ($now) {
          $closeQuery->whereNull('close_at')->orWhere('close_at', '>', $now);
        });
      return;
    }

    if ($status === 'completed') {
      $query
        ->whereNotNull('close_at')
        ->where('close_at', '<=', $now);
    }
  }

  private function countByStatus(Builder $baseQuery, string $status, $now): int
  {
    $query = clone $baseQuery;
    $this->applyStatusFilter($query, $status, $now);
    return $query->count();
  }

  private function queryFacultyAssignedRoutines(int $facultyId)
  {
    $hasTeachingAllocationLink = Schema::hasColumn('subject_has_routines', 'teaching_allocation_id');

    return SubjectHasRoutine::query()
      ->where(function ($query) use ($facultyId) {
        $query->where('faculty_id', $facultyId)
          ->orWhereHas('teachingAssignment', function ($assignmentQuery) use ($facultyId) {
            $assignmentQuery->where('faculty_id', $facultyId)
              ->orWhereHas('facultyAssignments', function ($facultyAssignmentQuery) use ($facultyId) {
                $facultyAssignmentQuery->where('faculty_id', $facultyId);
              })
              ->orWhereHas('coFacultyMembers', function ($coFacultyQuery) use ($facultyId) {
                $coFacultyQuery->where('faculties.id', $facultyId);
              });
          });
      })
      ->when($hasTeachingAllocationLink, function ($query) use ($facultyId) {
        $query->orWhereHas('teachingAllocation', function ($assignmentQuery) use ($facultyId) {
          $assignmentQuery->where('faculty_id', $facultyId)
            ->orWhereHas('facultyAssignments', function ($facultyAssignmentQuery) use ($facultyId) {
              $facultyAssignmentQuery->where('faculty_id', $facultyId);
            })
            ->orWhereHas('coFacultyMembers', function ($coFacultyQuery) use ($facultyId) {
              $coFacultyQuery->where('faculties.id', $facultyId);
            });
        });
      });
  }

  private function resolveQuizEligibleStudents(Quiz $quiz): array
  {
    $hasTeachingAssignmentColumn = Schema::hasColumn('subject_has_routines', 'teaching_assignment_id');
    $hasTeachingAllocationColumn = Schema::hasColumn('subject_has_routines', 'teaching_allocation_id');

    if (!$hasTeachingAssignmentColumn && !$hasTeachingAllocationColumn) {
      return [
        'source' => 'roster',
        'students' => collect(),
      ];
    }

    $routineQuery = $this->queryFacultyAssignedRoutines((int) $quiz->faculty_id)
      ->where('syllabus_id', (int) $quiz->syllabus_id);

    if (Schema::hasColumn('subject_has_routines', 'deleted_at')) {
      $routineQuery->whereNull('deleted_at');
    }

    $selectColumns = [];
    if ($hasTeachingAssignmentColumn) {
      $selectColumns[] = 'teaching_assignment_id';
    }
    if ($hasTeachingAllocationColumn) {
      $selectColumns[] = 'teaching_allocation_id';
    }

    $candidateAssignmentIds = collect($routineQuery
      ->get($selectColumns)
      ->flatMap(function ($routine) use ($hasTeachingAssignmentColumn, $hasTeachingAllocationColumn) {
        $ids = [];

        if ($hasTeachingAssignmentColumn) {
          $ids[] = (int) ($routine->teaching_assignment_id ?? 0);
        }

        if ($hasTeachingAllocationColumn) {
          $ids[] = (int) ($routine->teaching_allocation_id ?? 0);
        }

        return $ids;
      }))
      ->filter(fn($id) => $id > 0)
      ->unique()
      ->values();

    if ($candidateAssignmentIds->isEmpty() && method_exists($routineQuery->getModel(), 'withTrashed')) {
      $legacyRoutineQuery = $this->queryFacultyAssignedRoutines((int) $quiz->faculty_id)
        ->withTrashed()
        ->where('syllabus_id', (int) $quiz->syllabus_id);

      $candidateAssignmentIds = collect($legacyRoutineQuery
        ->get($selectColumns)
        ->flatMap(function ($routine) use ($hasTeachingAssignmentColumn, $hasTeachingAllocationColumn) {
          $ids = [];

          if ($hasTeachingAssignmentColumn) {
            $ids[] = (int) ($routine->teaching_assignment_id ?? 0);
          }

          if ($hasTeachingAllocationColumn) {
            $ids[] = (int) ($routine->teaching_allocation_id ?? 0);
          }

          return $ids;
        }))
        ->filter(fn($id) => $id > 0)
        ->unique()
        ->values();
    }

    $selectedAssignmentId = (int) ($quiz->teaching_assignment_id ?? 0);

    if ($selectedAssignmentId <= 0 && $candidateAssignmentIds->count() > 1) {
      $selectedAssignmentId = $this->inferQuizAssignmentIdFromRoster($quiz, $candidateAssignmentIds);
    }

    if ($selectedAssignmentId <= 0) {
      $selectedAssignmentId = (int) ($candidateAssignmentIds->first() ?? 0);
    }

    $assignmentIds = $selectedAssignmentId > 0
      ? collect([$selectedAssignmentId])
      : collect();

    if ($assignmentIds->isEmpty()) {
      return [
        'source' => 'roster',
        'students' => collect(),
      ];
    }

    $studentIds = StudentCourseRoster::query()
      ->whereIn('ta_id', $assignmentIds->all())
      ->where('course_id', (int) $quiz->course_id)
      ->pluck('student_id')
      ->map(fn($studentId) => (int) $studentId)
      ->filter(fn($studentId) => $studentId > 0)
      ->unique()
      ->values();

    return [
      'source' => 'roster',
      'students' => $studentIds,
    ];
  }

  private function inferQuizAssignmentIdFromRoster(Quiz $quiz, $candidateAssignmentIds): int
  {
    $candidateIds = collect($candidateAssignmentIds)
      ->map(fn($id) => (int) $id)
      ->filter(fn($id) => $id > 0)
      ->unique()
      ->values();

    if ($candidateIds->count() < 2) {
      return 0;
    }

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

    $bestAssignmentId = StudentCourseRoster::query()
      ->whereIn('ta_id', $candidateIds->all())
      ->where('course_id', (int) $quiz->course_id)
      ->whereIn('student_id', $attemptedStudentIds->all())
      ->select('ta_id', DB::raw('COUNT(DISTINCT student_id) as matched_students'))
      ->groupBy('ta_id')
      ->orderByDesc('matched_students')
      ->orderBy('ta_id')
      ->value('ta_id');

    return (int) ($bestAssignmentId ?? 0);
  }

  private function expectedStudentIdsForQuiz(Quiz $quiz)
  {
    $rosterData = $this->resolveQuizEligibleStudents($quiz);

    return collect($rosterData['students'] ?? [])
      ->map(fn($studentId) => (int) $studentId)
      ->filter(fn($studentId) => $studentId > 0)
      ->unique()
      ->values();
  }

  private function monitorIndexRouteName(string $role): string
  {
    if ($role === 'principal') {
      return 'principal.quizzes.index';
    }

    if ($role === 'itcell') {
      return 'itcell.quizzes.index';
    }

    return 'department.quizzes.index';
  }

  private function monitorResultsRouteName(string $role): string
  {
    if ($role === 'principal') {
      return 'principal.quizzes.results';
    }

    if ($role === 'itcell') {
      return 'itcell.quizzes.results';
    }

    return 'department.quizzes.results';
  }
}
