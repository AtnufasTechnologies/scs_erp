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

    $components = SupCiaComponent::where('IS_DELETED', 0)->orderBy('id')->get();

    $quizzes = Quiz::where('created_by', Auth::id())
      ->with([
        'course:id,course_title,course_code',
        'subject:id,title',
        'ciaComponent:id,name',
      ])
      ->withCount('questions')
      ->withCount([
        'attempts as submitted_attempts_count' => function ($query) {
          $query->where('status', 'submitted');
        }
      ])
      ->orderByDesc('id')
      ->get();

    return view('faculty.quiz.index', compact('syllabi', 'components', 'quizzes'));
  }

  public function store(Request $request)
  {
    $request->validate([
      'title' => 'required|string|max:255',
      'syllabus_id' => 'required|integer|exists:subject_has_syllabi,id',
      'sup_cia_component_id' => 'required|integer|exists:sup_cia_components,id',
      'total_marks' => 'required|numeric|min:1',
      'open_at' => 'required|date',
      'close_at' => 'nullable|date|after_or_equal:open_at',
      'shuffle_questions' => 'nullable|boolean',
      'shuffle_options' => 'nullable|boolean',
      'time_limit_minutes' => 'nullable|integer|min:1|max:300',
      'questions' => 'required|array|min:1',
      'questions.*.question_text' => 'required|string',
      'questions.*.question_image' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:4096',
      'questions.*.options' => 'required|array|min:2',
      'questions.*.options.*' => 'required|string',
      'questions.*.option_images.*' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:4096',
      'questions.*.correct_option' => 'required|integer|min:0',
    ]);

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

    foreach ($request->questions as $question) {
      $correctOptionIndex = (int) $question['correct_option'];
      if (!array_key_exists($correctOptionIndex, $question['options'])) {
        return redirect()->back()->with('error', 'Invalid correct option selected for one or more questions.')->withInput();
      }
    }

    DB::transaction(function () use ($request, $syllabus, $facultyId) {
      $nextCiaGroupId = ((int) DB::table('cia_group_component')->lockForUpdate()->max('CIA_GROUP_ID')) + 1;
      $nextOrderId = ((int) DB::table('cia_group_component')->lockForUpdate()->max('ORDER_ID')) + 1;

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

      $quiz = Quiz::create([
        'subject_id' => $syllabus->subject_id,
        'course_id' => $syllabus->course_id,
        'syllabus_id' => $syllabus->id,
        'batch_id' => $syllabus->batch_id,
        'semester_id' => $syllabus->semester_id,
        'faculty_id' => $facultyId,
        'sup_cia_component_id' => $request->sup_cia_component_id,
        'cia_group_id' => $nextCiaGroupId,
        'title' => $request->title,
        'total_marks' => $request->total_marks,
        'open_at' => $request->open_at,
        'close_at' => $request->close_at,
        'shuffle_questions' => $request->boolean('shuffle_questions'),
        'shuffle_options' => $request->boolean('shuffle_options'),
        'time_limit_minutes' => $request->input('time_limit_minutes'),
        'is_published' => true,
        'created_by' => Auth::id(),
      ]);

      foreach ($request->questions as $index => $questionData) {
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

    return redirect()->route('faculty.quiz.index')->with('success', 'Quiz created successfully.');
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

    $enrolledStudents = DB::table('student_masters as sm')
      ->join('student_course_infos as sci', function ($join) use ($quiz) {
        $join->on('sci.student_id', '=', 'sm.id')
          ->where('sci.course_id', $quiz->course_id)
          ->where('sci.semester', $quiz->semester_id);
      })
      ->join('subject_has_student_progams as shsp', function ($join) use ($quiz) {
        $join->on('shsp.student_program_id', '=', 'sm.new_program_id')
          ->where('shsp.subject_id', $quiz->subject_id)
          ->where('shsp.batch_id', $quiz->batch_id);
      })
      ->where('sm.is_deleted', 0)
      ->where('sm.is_left', 0)
      ->select('sm.id', 'sm.first_name', 'sm.last_name', 'sm.roll_no', 'sm.register_no')
      ->distinct()
      ->orderBy('sm.roll_no')
      ->get();

    $attemptCounts = QuizAttempt::where('quiz_id', $quiz->id)
      ->select('student_id', DB::raw('count(*) as attempts_used'))
      ->groupBy('student_id')
      ->pluck('attempts_used', 'student_id');

    $permissions = QuizAttemptPermission::where('quiz_id', $quiz->id)
      ->pluck('max_attempts', 'student_id');

    return view('faculty.quiz.results', compact(
      'quiz',
      'attempts',
      'enrolledStudents',
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

    return redirect()->route('faculty.quiz.results', $quiz->id)
      ->with('success', 'Attempt permission updated for selected students.');
  }
}
