<?php

namespace App\Faculty\Http\Controllers;

use App\Http\Controllers\StaticController;
use App\Http\Controllers\Controller;
use App\Models\Quiz;
use App\Models\QuizAttempt;
use App\Models\QuizAttemptPermission;
use App\Models\QuizQuestion;
use App\Models\SubjectFacultyMaster;
use App\Models\SubjectHasRoutine;
use App\Models\SubjectHasSyllabus;
use App\Models\SupCiaComponent;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class QuizController extends Controller
{
  public function index()
  {
    $facultyId = SubjectFacultyMaster::where('access_id', Auth::id())->value('faculty_id');

    $syllabusIds = collect();
    if ($facultyId) {
      $syllabusIds = SubjectHasRoutine::where('faculty_id', $facultyId)
        ->pluck('syllabus_id')
        ->unique()
        ->values();
    }

    $syllabi = SubjectHasSyllabus::whereIn('id', $syllabusIds)
      ->with([
        'subject:id,title',
        'coursemaster:id,course_title,course_code',
        'semestermaster:id,title',
        'batchmaster:id,batch_name',
      ])
      ->orderByDesc('id')
      ->get();

    $fa1Component = $this->resolveFa1Component();

    return view('faculty.quiz.index', compact('syllabi', 'fa1Component'));
  }

  public function myQuizzes()
  {
    $fa1Component = $this->resolveFa1Component();

    $quizzes = Quiz::where('created_by', Auth::id())
      ->when($fa1Component, function ($query) use ($fa1Component) {
        $query->where('sup_cia_component_id', $fa1Component->id);
      })
      ->with([
        'course:id,course_title,course_code',
        'subject:id,title',
        'ciaComponent:id,name',
        'batchmaster:id,batch_name',
        'semestermaster:id,title',
      ])
      ->withCount('questions')
      ->withCount([
        'attempts as submitted_attempts_count' => function ($query) {
          $query->where('status', 'submitted');
        }
      ])
      ->orderByDesc('id')
      ->get();

    return view('faculty.quiz.my_quizzes', compact('quizzes'));
  }

  public function review($id)
  {
    $quiz = Quiz::where('id', $id)
      ->where('created_by', Auth::id())
      ->with([
        'course:id,course_title,course_code',
        'subject:id,title',
        'ciaComponent:id,name',
        'batchmaster:id,batch_name',
        'semestermaster:id,title',
        'questions:id,quiz_id,question_text,question_image,position',
        'questions.options:id,quiz_question_id,option_text,option_image,is_correct,position',
      ])
      ->firstOrFail();

    return view('faculty.quiz.review', compact('quiz'));
  }

  public function updateTiming(Request $request, $id)
  {
    $quiz = Quiz::where('id', $id)
      ->where('created_by', Auth::id())
      ->firstOrFail();

    $request->validate([
      'open_at' => 'required|date',
      'close_at' => 'nullable|date|after_or_equal:open_at',
      'time_limit_minutes' => 'nullable|integer|min:1|max:300',
      'pre_start_countdown_seconds' => 'nullable|integer|min:0|max:300',
    ]);

    $payload = [
      'open_at' => $request->input('open_at'),
      'close_at' => $request->input('close_at'),
      'time_limit_minutes' => $request->input('time_limit_minutes') ?: null,
    ];

    if (Schema::hasColumn('quizzes', 'pre_start_countdown_seconds')) {
      $payload['pre_start_countdown_seconds'] = $request->input('pre_start_countdown_seconds', 10);
    }

    $quiz->update($payload);

    return redirect()->route('faculty.fa1.my-quizzes')
      ->with('success', 'Quiz timing reset successfully.');
  }

  public function editQuestions($id)
  {
    $quiz = Quiz::where('id', $id)
      ->where('created_by', Auth::id())
      ->with([
        'course:id,course_title,course_code',
        'subject:id,title',
        'batchmaster:id,batch_name',
        'semestermaster:id,title',
        'questions:id,quiz_id,question_text,question_image,position',
        'questions.options:id,quiz_question_id,option_text,option_image,is_correct,position',
      ])
      ->withCount('questions')
      ->firstOrFail();

    return view('faculty.quiz.edit_questions', compact('quiz'));
  }

  public function storeQuestions(Request $request, $id)
  {
    $quiz = Quiz::where('id', $id)
      ->where('created_by', Auth::id())
      ->with([
        'questions:id,quiz_id,question_text,question_image,position',
        'questions.options:id,quiz_question_id,option_text,option_image,is_correct,position',
      ])
      ->firstOrFail();

    $request->validate([
      'shuffle_questions' => 'nullable|boolean',
      'shuffle_options' => 'nullable|boolean',
      'existing_questions' => 'nullable|array',
      'existing_questions.*.id' => 'required_with:existing_questions|integer',
      'existing_questions.*.question_text' => 'required_with:existing_questions|string',
      'existing_questions.*.question_image' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:4096',
      'existing_questions.*.remove_question_image' => 'nullable|boolean',
      'existing_questions.*.options' => 'required_with:existing_questions|array|min:2',
      'existing_questions.*.options.*.id' => 'required_with:existing_questions|integer',
      'existing_questions.*.options.*.option_text' => 'required_with:existing_questions|string',
      'existing_questions.*.options.*.option_image' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:4096',
      'existing_questions.*.options.*.remove_option_image' => 'nullable|boolean',
      'existing_questions.*.correct_option' => 'required_with:existing_questions|integer|min:0',
      'questions' => 'nullable|array|min:1',
      'questions.*.question_text' => 'required_with:questions|string',
      'questions.*.question_image' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:4096',
      'questions.*.options' => 'required_with:questions|array|min:2',
      'questions.*.options.*' => 'required_with:questions|string',
      'questions.*.option_images.*' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:4096',
      'questions.*.correct_option' => 'required_with:questions|integer|min:0',
      'bulk_questions_file' => 'nullable|file|mimes:xlsx,xls,csv|max:5120',
    ]);

    $existingQuestionsInput = collect($request->input('existing_questions', []))
      ->filter(function ($question) {
        return !empty($question['id']);
      })
      ->values()
      ->all();

    $manualQuestions = collect($request->input('questions', []))
      ->filter(function ($question) {
        return !empty(trim((string) ($question['question_text'] ?? '')));
      })
      ->values()
      ->all();

    $bulkQuestions = [];
    if ($request->hasFile('bulk_questions_file')) {
      try {
        $bulkQuestions = $this->parseBulkQuestionsFile($request->file('bulk_questions_file'));
      } catch (\Throwable $e) {
        return redirect()->back()
          ->with('error', 'Bulk question upload failed: ' . $e->getMessage())
          ->withInput();
      }
    }

    $allQuestions = array_values(array_merge($manualQuestions, $bulkQuestions));

    if (count($existingQuestionsInput) < 1 && count($allQuestions) < 1) {
      return redirect()->back()
        ->with('error', 'Please edit at least one existing question or add/upload new questions.')
        ->withInput();
    }

    foreach ($existingQuestionsInput as $question) {
      $correctOptionIndex = (int) ($question['correct_option'] ?? -1);
      $options = $question['options'] ?? [];
      if (!array_key_exists($correctOptionIndex, $options)) {
        return redirect()->back()->with('error', 'Invalid correct option selected for one or more existing questions.')->withInput();
      }
    }

    foreach ($allQuestions as $question) {
      $correctOptionIndex = (int) $question['correct_option'];
      if (!array_key_exists($correctOptionIndex, $question['options'])) {
        return redirect()->back()->with('error', 'Invalid correct option selected for one or more questions.')->withInput();
      }
    }

    DB::transaction(function () use ($request, $quiz, $existingQuestionsInput, $allQuestions) {
      $quiz->update([
        'shuffle_questions' => $request->boolean('shuffle_questions'),
        'shuffle_options' => $request->boolean('shuffle_options'),
      ]);

      $existingQuestions = $quiz->questions->keyBy('id');

      foreach ($existingQuestionsInput as $qIndex => $questionData) {
        $questionId = (int) $questionData['id'];
        $question = $existingQuestions->get($questionId);
        if (!$question) {
          continue;
        }

        $questionPayload = [
          'question_text' => trim((string) ($questionData['question_text'] ?? '')),
        ];

        if ($request->hasFile("existing_questions.$qIndex.question_image")) {
          $questionPayload['question_image'] = StaticController::s3_image_uploader(
            $request->file("existing_questions.$qIndex.question_image"),
            'quiz/questions'
          );
        } elseif ((int) ($questionData['remove_question_image'] ?? 0) === 1) {
          $questionPayload['question_image'] = null;
        }

        $question->update($questionPayload);

        $questionOptions = $question->options->keyBy('id');
        $correctOptionIndex = (int) ($questionData['correct_option'] ?? 0);

        foreach (($questionData['options'] ?? []) as $optionIndex => $optionData) {
          $optionId = (int) ($optionData['id'] ?? 0);
          $option = $questionOptions->get($optionId);
          if (!$option) {
            continue;
          }

          $optionPayload = [
            'option_text' => trim((string) ($optionData['option_text'] ?? '')),
            'is_correct' => $correctOptionIndex === (int) $optionIndex,
            'position' => ((int) $optionIndex) + 1,
          ];

          if ($request->hasFile("existing_questions.$qIndex.options.$optionIndex.option_image")) {
            $optionPayload['option_image'] = StaticController::s3_image_uploader(
              $request->file("existing_questions.$qIndex.options.$optionIndex.option_image"),
              'quiz/options'
            );
          } elseif ((int) ($optionData['remove_option_image'] ?? 0) === 1) {
            $optionPayload['option_image'] = null;
          }

          $option->update($optionPayload);
        }
      }

      $nextPosition = ((int) QuizQuestion::where('quiz_id', $quiz->id)->max('position')) + 1;

      foreach ($allQuestions as $index => $questionData) {
        $questionImagePath = null;
        if ($request->hasFile("questions.$index.question_image")) {
          $questionImagePath = StaticController::s3_image_uploader(
            $request->file("questions.$index.question_image"),
            'quiz/questions'
          );
        }

        $question = QuizQuestion::create([
          'quiz_id' => $quiz->id,
          'question_text' => $questionData['question_text'],
          'question_image' => $questionImagePath,
          'position' => $nextPosition++,
        ]);

        foreach ($questionData['options'] as $optionIndex => $optionText) {
          $optionImagePath = null;
          if ($request->hasFile("questions.$index.option_images.$optionIndex")) {
            $optionImagePath = StaticController::s3_image_uploader(
              $request->file("questions.$index.option_images.$optionIndex"),
              'quiz/options'
            );
          }

          $question->options()->create([
            'option_text' => $optionText,
            'option_image' => $optionImagePath,
            'is_correct' => ((int) $questionData['correct_option']) === $optionIndex,
            'position' => $optionIndex + 1,
          ]);
        }
      }
    });

    return redirect()->route('faculty.fa1.questions.edit', $quiz->id)
      ->with('success', 'Quiz updated successfully. Existing questions edited and new questions added.');
  }

  public function downloadBulkTemplate()
  {
    $spreadsheet = new Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();

    $headers = ['question_text', 'option_1', 'option_2', 'option_3', 'option_4', 'correct_option'];
    $sample = ['What is the capital of France?', 'Berlin', 'Madrid', 'Paris', 'Rome', '3'];

    $sheet->fromArray($headers, null, 'A1');
    $sheet->fromArray($sample, null, 'A2');
    $sheet->setTitle('FA1 Questions Template');

    $tempPath = tempnam(sys_get_temp_dir(), 'fa1_quiz_template_');
    if ($tempPath === false) {
      abort(500, 'Unable to create template file.');
    }

    $finalPath = $tempPath . '.xlsx';
    @rename($tempPath, $finalPath);

    $writer = new Xlsx($spreadsheet);
    $writer->save($finalPath);

    return response()->download($finalPath, 'fa1_quiz_bulk_template.xlsx')->deleteFileAfterSend(true);
  }

  public function store(Request $request)
  {
    $request->validate([
      'syllabus_id' => 'required|integer|exists:subject_has_syllabi,id',
      'sup_cia_component_id' => 'required|integer|exists:sup_cia_components,id',
      'total_marks' => 'required|numeric|min:1',
      'open_at' => 'required|date',
      'close_at' => 'nullable|date|after_or_equal:open_at',
      'shuffle_questions' => 'nullable|boolean',
      'shuffle_options' => 'nullable|boolean',
      'time_limit_minutes' => 'nullable|integer|min:1|max:300',
      'pre_start_countdown_seconds' => 'nullable|integer|min:0|max:300',
      'questions' => 'nullable|array|min:1',
      'questions.*.question_text' => 'required_with:questions|string',
      'questions.*.question_image' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:4096',
      'questions.*.options' => 'required_with:questions|array|min:2',
      'questions.*.options.*' => 'required_with:questions|string',
      'questions.*.option_images.*' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:4096',
      'questions.*.correct_option' => 'required_with:questions|integer|min:0',
      'bulk_questions_file' => 'nullable|file|mimes:xlsx,xls,csv|max:5120',
    ]);

    $fa1Component = $this->resolveFa1Component();
    if (!$fa1Component) {
      return redirect()->back()->with('error', 'FA1 component is not configured. Please contact administrator.')->withInput();
    }

    if ((int) $request->sup_cia_component_id !== (int) $fa1Component->id) {
      return redirect()->back()->with('error', 'Only FA1 component is allowed for quiz setup.')->withInput();
    }

    $facultyId = SubjectFacultyMaster::where('access_id', Auth::id())->value('faculty_id');
    if (!$facultyId) {
      return redirect()->back()->with('error', 'Faculty mapping not found for this account.');
    }

    $syllabusAllowed = SubjectHasRoutine::where('faculty_id', $facultyId)
      ->where('syllabus_id', $request->syllabus_id)
      ->exists();

    if (!$syllabusAllowed) {
      return redirect()->back()->with('error', 'You can only create quizzes for your allotted subjects.');
    }

    $syllabus = SubjectHasSyllabus::findOrFail($request->syllabus_id);

    $manualQuestions = collect($request->input('questions', []))
      ->filter(function ($question) {
        return !empty(trim((string) ($question['question_text'] ?? '')));
      })
      ->values()
      ->all();

    $bulkQuestions = [];
    if ($request->hasFile('bulk_questions_file')) {
      try {
        $bulkQuestions = $this->parseBulkQuestionsFile($request->file('bulk_questions_file'));
      } catch (\Throwable $e) {
        return redirect()->back()
          ->with('error', 'Bulk question upload failed: ' . $e->getMessage())
          ->withInput();
      }
    }

    $allQuestions = array_values(array_merge($manualQuestions, $bulkQuestions));

    if (count($allQuestions) < 1) {
      return redirect()->back()
        ->with('error', 'Please add at least one question manually or upload a valid Excel file.')
        ->withInput();
    }

    foreach ($allQuestions as $question) {
      $correctOptionIndex = (int) $question['correct_option'];
      if (!array_key_exists($correctOptionIndex, $question['options'])) {
        return redirect()->back()->with('error', 'Invalid correct option selected for one or more questions.')->withInput();
      }
    }

    $createdQuizId = null;

    DB::transaction(function () use ($request, $syllabus, $facultyId, $fa1Component, &$createdQuizId, $allQuestions) {
      $nextCiaGroupId = ((int) DB::table('cia_group_component')->lockForUpdate()->max('CIA_GROUP_ID')) + 1;
      $nextOrderId = ((int) DB::table('cia_group_component')->lockForUpdate()->max('ORDER_ID')) + 1;

      $examTitle = trim(implode(' - ', array_filter([
        $fa1Component->name,
        $syllabus->subject->title ?? null,
        $syllabus->coursemaster->course_code ?? ($syllabus->coursemaster->course_title ?? null),
      ])));

      DB::table('cia_group_component')->insert([
        'CIA_GROUP_ID' => $nextCiaGroupId,
        'SUP_CIA_COMPONENT_ID' => $request->sup_cia_component_id,
        'MAX_MARK' => $request->total_marks,
        'MIN_MARK' => 0,
        'IS_ACTIVE' => 1,
        'IS_DELETED' => 0,
        'ORDER_ID' => $nextOrderId,
        'created_at' => now(),
        'updated_at' => now(),
      ]);

      $quizPayload = [
        'subject_id' => $syllabus->subject_id,
        'course_id' => $syllabus->course_id,
        'syllabus_id' => $syllabus->id,
        'batch_id' => $syllabus->batch_id,
        'semester_id' => $syllabus->semester_id,
        'faculty_id' => $facultyId,
        'sup_cia_component_id' => $request->sup_cia_component_id,
        'cia_group_id' => $nextCiaGroupId,
        'title' => $examTitle !== '' ? $examTitle : ($fa1Component->name . ' Exam'),
        'total_marks' => $request->total_marks,
        'open_at' => $request->open_at,
        'close_at' => $request->close_at,
        'shuffle_questions' => $request->boolean('shuffle_questions'),
        'shuffle_options' => $request->boolean('shuffle_options'),
        'time_limit_minutes' => $request->input('time_limit_minutes'),
        'is_published' => true,
        'created_by' => Auth::id(),
      ];

      if (Schema::hasColumn('quizzes', 'pre_start_countdown_seconds')) {
        $quizPayload['pre_start_countdown_seconds'] = $request->input('pre_start_countdown_seconds', 10);
      }

      $quiz = Quiz::create($quizPayload);

      $createdQuizId = $quiz->id;

      foreach ($allQuestions as $index => $questionData) {
        $questionImagePath = null;
        if ($request->hasFile("questions.$index.question_image")) {
          $questionImagePath = StaticController::s3_image_uploader(
            $request->file("questions.$index.question_image"),
            'quiz/questions'
          );
        }

        $question = QuizQuestion::create([
          'quiz_id' => $quiz->id,
          'question_text' => $questionData['question_text'],
          'question_image' => $questionImagePath,
          'position' => $index + 1,
        ]);

        foreach ($questionData['options'] as $optionIndex => $optionText) {
          $optionImagePath = null;
          if ($request->hasFile("questions.$index.option_images.$optionIndex")) {
            $optionImagePath = StaticController::s3_image_uploader(
              $request->file("questions.$index.option_images.$optionIndex"),
              'quiz/options'
            );
          }

          $question->options()->create([
            'option_text' => $optionText,
            'option_image' => $optionImagePath,
            'is_correct' => ((int) $questionData['correct_option']) === $optionIndex,
            'position' => $optionIndex + 1,
          ]);
        }
      }
    });

    if ($createdQuizId) {
      return redirect()->route('faculty.fa1.results', $createdQuizId)
        ->with('success', 'FA1 quiz created successfully. Student list and attempt status are shown below.');
    }

    return redirect()->route('faculty.fa1.index')->with('success', 'FA1 quiz created successfully.');
  }

  private function parseBulkQuestionsFile($uploadedFile): array
  {
    $spreadsheet = IOFactory::load($uploadedFile->getRealPath());
    $sheet = $spreadsheet->getActiveSheet();
    $rows = $sheet->toArray(null, true, true, true);

    if (count($rows) < 2) {
      throw new \RuntimeException('The uploaded file is empty. Add header and at least one question row.');
    }

    $headerRow = array_shift($rows);
    $headers = [];
    foreach ($headerRow as $column => $value) {
      $normalized = strtolower(trim((string) $value));
      if ($normalized !== '') {
        $headers[$normalized] = $column;
      }
    }

    $requiredHeaders = ['question_text', 'option_1', 'option_2', 'option_3', 'option_4', 'correct_option'];
    foreach ($requiredHeaders as $requiredHeader) {
      if (!isset($headers[$requiredHeader])) {
        throw new \RuntimeException('Missing required column: ' . $requiredHeader);
      }
    }

    $parsedQuestions = [];
    foreach ($rows as $rowNumber => $row) {
      $questionText = trim((string) ($row[$headers['question_text']] ?? ''));
      $option1 = trim((string) ($row[$headers['option_1']] ?? ''));
      $option2 = trim((string) ($row[$headers['option_2']] ?? ''));
      $option3 = trim((string) ($row[$headers['option_3']] ?? ''));
      $option4 = trim((string) ($row[$headers['option_4']] ?? ''));
      $correctRaw = trim((string) ($row[$headers['correct_option']] ?? ''));

      if ($questionText === '' && $option1 === '' && $option2 === '' && $option3 === '' && $option4 === '' && $correctRaw === '') {
        continue;
      }

      if ($questionText === '' || $option1 === '' || $option2 === '' || $option3 === '' || $option4 === '' || $correctRaw === '') {
        throw new \RuntimeException('Incomplete data at row ' . ($rowNumber + 1) . '.');
      }

      $correctOption = null;
      if (is_numeric($correctRaw)) {
        $correctOption = (int) $correctRaw;
      } else {
        $letters = ['A' => 1, 'B' => 2, 'C' => 3, 'D' => 4];
        $correctOption = $letters[strtoupper($correctRaw)] ?? null;
      }

      if (!in_array($correctOption, [1, 2, 3, 4], true)) {
        throw new \RuntimeException('Invalid correct_option at row ' . ($rowNumber + 1) . '. Use 1-4 or A-D.');
      }

      $parsedQuestions[] = [
        'question_text' => $questionText,
        'options' => [$option1, $option2, $option3, $option4],
        'correct_option' => $correctOption - 1,
      ];
    }

    if (count($parsedQuestions) < 1) {
      throw new \RuntimeException('No valid question rows found in uploaded file.');
    }

    return $parsedQuestions;
  }

  public function results($id)
  {
    $quiz = Quiz::where('id', $id)
      ->where('created_by', Auth::id())
      ->with([
        'course:id,course_title,course_code',
        'subject:id,title',
      ])
      ->firstOrFail();

    $attempts = QuizAttempt::where('quiz_id', $quiz->id)
      ->where('status', 'submitted')
      ->with('student:id,first_name,last_name,roll_no,register_no')
      ->orderByDesc('score')
      ->orderBy('submitted_at')
      ->get();

    $latestSubmittedByStudent = QuizAttempt::where('quiz_id', $quiz->id)
      ->where('status', 'submitted')
      ->orderByDesc('submitted_at')
      ->get()
      ->unique('student_id')
      ->keyBy('student_id');

    $rosterData = $this->resolveQuizEligibleStudents($quiz);
    $enrolledStudents = $rosterData['students'];
    $studentRosterSource = $rosterData['source'];

    $attemptCounts = QuizAttempt::where('quiz_id', $quiz->id)
      ->select('student_id', DB::raw('count(*) as attempts_used'))
      ->groupBy('student_id')
      ->pluck('attempts_used', 'student_id');

    $permissions = QuizAttemptPermission::where('quiz_id', $quiz->id)
      ->pluck('max_attempts', 'student_id');

    $expectedStudentCount = $this->expectedStudentCountForQuiz($quiz);
    $attemptedStudentCount = QuizAttempt::where('quiz_id', $quiz->id)
      ->where('status', 'submitted')
      ->distinct('student_id')
      ->count('student_id');

    return view('faculty.quiz.results', compact(
      'quiz',
      'attempts',
      'latestSubmittedByStudent',
      'enrolledStudents',
      'studentRosterSource',
      'expectedStudentCount',
      'attemptedStudentCount',
      'attemptCounts',
      'permissions'
    ));
  }

  public function allowAttempts(Request $request, $id)
  {
    $quiz = Quiz::where('id', $id)
      ->where('created_by', Auth::id())
      ->firstOrFail();

    $request->validate([
      'student_ids' => 'required|array|min:1',
      'student_ids.*' => 'required|integer|exists:student_masters,id',
      'max_attempts' => 'required|integer|min:2|max:10',
    ]);

    DB::transaction(function () use ($request, $quiz) {
      foreach ($request->student_ids as $studentId) {
        QuizAttemptPermission::updateOrCreate(
          [
            'quiz_id' => $quiz->id,
            'student_id' => $studentId,
          ],
          [
            'max_attempts' => $request->max_attempts,
            'allowed_by' => Auth::id(),
          ]
        );
      }
    });

    return redirect()->route('faculty.fa1.results', $quiz->id)
      ->with('success', 'Attempt permission updated for selected students.');
  }

  private function resolveFa1Component(): ?SupCiaComponent
  {
    return SupCiaComponent::where('IS_DELETED', 0)
      ->orderBy('id')
      ->get()
      ->first(function ($component) {
        $normalized = preg_replace('/[^A-Z0-9]/', '', strtoupper((string) $component->name));
        return in_array($normalized, ['FA1', 'FAI'], true);
      });
  }

  private function resolveQuizEligibleStudents(Quiz $quiz): array
  {
    $baseQuery = DB::table('student_masters as sm')
      ->join('student_course_infos as sci', function ($join) use ($quiz) {
        $join->on('sci.student_id', '=', 'sm.id')
          ->where('sci.course_id', $quiz->course_id);
      })
      ->join('subject_has_student_progams as shsp', function ($join) use ($quiz) {
        $join->on('shsp.student_program_id', '=', 'sm.new_program_id')
          ->where('shsp.subject_id', $quiz->subject_id);
      })
      ->when(!empty($quiz->semester_id), function ($query) use ($quiz) {
        $query->where('sci.semester', $quiz->semester_id);
      })
      ->when(!empty($quiz->batch_id), function ($query) use ($quiz) {
        $query->where('shsp.batch_id', $quiz->batch_id);
      })
      ->where(function ($query) {
        $query->where('sm.is_deleted', 0)
          ->orWhereNull('sm.is_deleted');
      })
      ->where(function ($query) {
        $query->where('sm.is_left', 0)
          ->orWhereNull('sm.is_left');
      })
      ->select('sm.id', 'sm.first_name', 'sm.last_name', 'sm.roll_no', 'sm.register_no')
      ->distinct()
      ->orderBy('sm.roll_no');

    $students = $baseQuery->get();
    if ($students->isNotEmpty()) {
      return [
        'source' => 'primary',
        'students' => $students,
      ];
    }

    // Fallback for legacy setups where subject-to-program mapping is incomplete.
    $fallbackStudents = DB::table('student_masters as sm')
      ->join('student_course_infos as sci', function ($join) use ($quiz) {
        $join->on('sci.student_id', '=', 'sm.id')
          ->where('sci.course_id', $quiz->course_id);
      })
      ->when(!empty($quiz->semester_id), function ($query) use ($quiz) {
        $query->where('sci.semester', $quiz->semester_id);
      })
      ->where(function ($query) {
        $query->where('sm.is_deleted', 0)
          ->orWhereNull('sm.is_deleted');
      })
      ->where(function ($query) {
        $query->where('sm.is_left', 0)
          ->orWhereNull('sm.is_left');
      })
      ->select('sm.id', 'sm.first_name', 'sm.last_name', 'sm.roll_no', 'sm.register_no')
      ->distinct()
      ->orderBy('sm.roll_no')
      ->get();

    return [
      'source' => 'fallback',
      'students' => $fallbackStudents,
    ];
  }

  private function expectedStudentCountForQuiz(Quiz $quiz): int
  {
    $query = DB::table('student_course_infos as sci')
      ->join('student_masters as sm', 'sm.id', '=', 'sci.student_id')
      ->where('sci.course_id', $quiz->course_id)
      ->where(function ($builder) {
        $builder->where('sm.is_deleted', 0)
          ->orWhereNull('sm.is_deleted');
      })
      ->where(function ($builder) {
        $builder->where('sm.is_left', 0)
          ->orWhereNull('sm.is_left');
      });

    if (!empty($quiz->semester_id)) {
      $query->where('sci.semester', $quiz->semester_id);
    }

    if (!empty($quiz->batch_id)) {
      $query->where('sm.batch', $quiz->batch_id);
    }

    return (int) $query->distinct('sci.student_id')->count('sci.student_id');
  }
}
