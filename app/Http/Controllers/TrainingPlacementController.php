<?php

namespace App\Http\Controllers;

use App\Models\PlacementOpportunity;
use App\Models\RoleMaster;
use App\Models\TrainingAttempt;
use App\Models\TrainingProgram;
use App\Models\TrainingResource;
use App\Models\TrainingSurveyOption;
use App\Models\TrainingSurveyQuestion;
use App\Models\TrainingSurveyResponse;
use App\Models\TrainingTargetRole;
use App\Models\Subject;
use App\Models\UserHasRole;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class TrainingPlacementController extends Controller
{
  public function dashboard()
  {
    $totalTrainings = TrainingProgram::count();
    $activeTrainings = TrainingProgram::where('is_active', 1)->count();
    $totalPlacements = PlacementOpportunity::count();
    $activePlacements = PlacementOpportunity::where('is_active', 1)->count();
    $totalResources = TrainingResource::count();
    $totalSurveyQuestions = TrainingSurveyQuestion::count();

    $analyticsSource = TrainingProgram::with(['targetRoles', 'attempts'])->get();
    $analytics = $this->buildTrainingAnalytics($analyticsSource);
    $overallAssigned = $analytics->sum('assigned_users');
    $overallCompleted = $analytics->sum('completed_users');
    $overallCompletionRate = $overallAssigned > 0
      ? round(($overallCompleted / $overallAssigned) * 100, 2)
      : 0;

    $recentTrainings = TrainingProgram::with('targetRoles')->latest()->take(5)->get();
    $upcomingPlacements = PlacementOpportunity::whereNotNull('drive_date')
      ->whereDate('drive_date', '>=', today())
      ->orderBy('drive_date')
      ->take(5)
      ->get();

    $currentRole = UserHasRole::where('user_id', Auth::id())->value('role_name');
    $myAssignedCount = TrainingTargetRole::when($currentRole, function ($q) use ($currentRole) {
      $q->where('role_name', $currentRole);
    })->distinct('training_program_id')->count('training_program_id');
    $myCompletedCount = TrainingAttempt::where('user_id', Auth::id())
      ->whereNotNull('completed_at')
      ->count();
    $myPendingCount = max(0, $myAssignedCount - $myCompletedCount);

    return view('tpo.training-placement.dashboard', compact(
      'totalTrainings',
      'activeTrainings',
      'totalPlacements',
      'activePlacements',
      'totalResources',
      'totalSurveyQuestions',
      'overallCompletionRate',
      'recentTrainings',
      'upcomingPlacements',
      'myAssignedCount',
      'myCompletedCount',
      'myPendingCount'
    ));
  }

  public function index()
  {
    $trainings = TrainingProgram::with([
      'targetRoles',
      'resources',
      'surveyQuestions.options',
      'attempts',
    ])->latest()->get();

    $roleOptions = $this->roleOptions();

    $currentRole = UserHasRole::where('user_id', Auth::id())->value('role_name');
    $myTrainingIds = TrainingTargetRole::when($currentRole, function ($q) use ($currentRole) {
      $q->where('role_name', $currentRole);
    })->pluck('training_program_id');

    $myTrainings = TrainingProgram::with('attempts')
      ->whereIn('id', $myTrainingIds)
      ->latest()
      ->get();

    $analytics = $this->buildTrainingAnalytics($trainings);

    return view('tpo.training-placement.index', compact(
      'trainings',
      'roleOptions',
      'myTrainings',
      'analytics'
    ));
  }

  public function placementIndex()
  {
    $placements = PlacementOpportunity::with('subject')->latest()->get();
    $subjects = Subject::orderBy('title')->get();
    $categoryOptions = $this->placementCategoryOptions();
    $monthOptions = $this->monthOptions();
    $yearOptions = $this->studentYearOptions();

    return view('tpo.training-placement.placement', compact(
      'placements',
      'subjects',
      'categoryOptions',
      'monthOptions',
      'yearOptions'
    ));
  }

  public function storeTraining(Request $request)
  {
    $validated = $request->validate([
      'title' => 'required|string|max:255',
      'description' => 'nullable|string',
      'applicable_roles' => 'required|array|min:1',
      'applicable_roles.*' => 'required|string|max:100',
    ]);

    DB::transaction(function () use ($validated) {
      $training = TrainingProgram::create([
        'title' => $validated['title'],
        'description' => $validated['description'] ?? null,
        'created_by' => Auth::id(),
        'is_active' => 1,
      ]);

      foreach (array_unique($validated['applicable_roles']) as $roleName) {
        TrainingTargetRole::create([
          'training_program_id' => $training->id,
          'role_name' => $roleName,
        ]);
      }
    });

    return back()->with('success', 'Training created successfully.');
  }

  public function updateTraining(Request $request, TrainingProgram $training)
  {
    $validated = $request->validate([
      'title' => 'required|string|max:255',
      'description' => 'nullable|string',
      'applicable_roles' => 'required|array|min:1',
      'applicable_roles.*' => 'required|string|max:100',
      'is_active' => 'nullable|boolean',
    ]);

    DB::transaction(function () use ($validated, $training) {
      $training->update([
        'title' => $validated['title'],
        'description' => $validated['description'] ?? null,
        'is_active' => isset($validated['is_active']) ? 1 : 0,
      ]);

      $training->targetRoles()->delete();
      foreach (array_unique($validated['applicable_roles']) as $roleName) {
        TrainingTargetRole::create([
          'training_program_id' => $training->id,
          'role_name' => $roleName,
        ]);
      }
    });

    return back()->with('success', 'Training updated successfully.');
  }

  public function destroyTraining(TrainingProgram $training)
  {
    $training->delete();

    return back()->with('success', 'Training deleted successfully.');
  }

  public function storeResource(Request $request, TrainingProgram $training)
  {
    $validated = $request->validate([
      'resource_title' => 'nullable|string|max:255',
      'resource_file' => 'required|file|mimes:ppt,pptx,doc,docx,pdf|max:51200',
    ]);

    $file = $validated['resource_file'];
    $storedPath = $file->store('training_resources', 's3');

    TrainingResource::create([
      'training_program_id' => $training->id,
      'resource_title' => $validated['resource_title'] ?? $file->getClientOriginalName(),
      'file_name' => $file->getClientOriginalName(),
      'file_path' => $storedPath,
      'file_type' => strtolower($file->getClientOriginalExtension()),
      'file_size' => $file->getSize(),
      'uploaded_by' => Auth::id(),
    ]);

    return back()->with('success', 'Training resource uploaded successfully.');
  }

  public function destroyResource(TrainingResource $resource)
  {
    if ($resource->file_path) {
      Storage::disk('s3')->delete($resource->file_path);
    }

    $resource->delete();

    return back()->with('success', 'Training resource removed successfully.');
  }

  public function storeSurveyQuestion(Request $request, TrainingProgram $training)
  {
    $validated = $request->validate([
      'question_text' => 'required|string|max:255',
      'options_text' => 'required|string',
    ]);

    $parsedOptions = $this->parseSurveyOptions($validated['options_text']);

    if (count($parsedOptions) < 2) {
      if ($request->ajax() || $request->wantsJson()) {
        return response()->json([
          'message' => 'Please provide at least two survey options.',
        ], 422);
      }

      return back()->with('error', 'Please provide at least two survey options.');
    }

    $createdQuestion = null;

    DB::transaction(function () use ($training, $validated, $parsedOptions, &$createdQuestion) {
      $nextOrder = (int) $training->surveyQuestions()->max('question_order') + 1;

      $question = TrainingSurveyQuestion::create([
        'training_program_id' => $training->id,
        'question_text' => $validated['question_text'],
        'question_order' => $nextOrder,
        'is_required' => 1,
      ]);

      foreach ($parsedOptions as $index => $option) {
        TrainingSurveyOption::create([
          'training_survey_question_id' => $question->id,
          'option_text' => $option['text'],
          'score' => $option['score'],
          'option_order' => $index + 1,
        ]);
      }

      $createdQuestion = $question->load('options');
    });

    if (!$createdQuestion) {
      if ($request->ajax() || $request->wantsJson()) {
        return response()->json([
          'message' => 'Unable to add survey question right now.',
        ], 500);
      }

      return back()->with('error', 'Unable to add survey question right now.');
    }

    if ($request->ajax() || $request->wantsJson()) {
      return response()->json([
        'message' => 'Survey question added successfully.',
        'question' => [
          'id' => $createdQuestion->id,
          'question_text' => $createdQuestion->question_text,
          'options' => $createdQuestion->options->map(function ($option) {
            return [
              'option_text' => $option->option_text,
              'score' => (int) $option->score,
            ];
          })->values(),
          'delete_url' => route('tpo.training-placement.survey-question.destroy', $createdQuestion->id),
        ],
      ]);
    }

    return back()->with('success', 'Survey question added successfully.');
  }

  public function destroySurveyQuestion(TrainingSurveyQuestion $question)
  {
    $question->delete();

    return back()->with('success', 'Survey question deleted successfully.');
  }

  public function attempt(TrainingProgram $training)
  {
    $training->load(['resources', 'surveyQuestions.options', 'targetRoles']);

    $currentRole = UserHasRole::where('user_id', Auth::id())->value('role_name');
    $isAllowed = $training->targetRoles->pluck('role_name')->contains($currentRole);

    if (!$isAllowed) {
      return back()->with('error', 'This training is not assigned to your role.');
    }

    $attempt = TrainingAttempt::where('training_program_id', $training->id)
      ->where('user_id', Auth::id())
      ->first();

    return view('tpo.training-placement.attempt', compact('training', 'attempt'));
  }

  public function submitAttempt(Request $request, TrainingProgram $training)
  {
    $training->load('surveyQuestions.options');

    $questionIds = $training->surveyQuestions->pluck('id')->toArray();
    if (empty($questionIds)) {
      return back()->with('error', 'No survey questions found for this training.');
    }

    $request->validate([
      'responses' => 'required|array',
    ]);

    foreach ($questionIds as $questionId) {
      if (!$request->filled('responses.' . $questionId)) {
        return back()->with('error', 'Please answer all survey questions.')->withInput();
      }
    }

    DB::transaction(function () use ($request, $training, $questionIds) {
      $attempt = TrainingAttempt::firstOrCreate(
        [
          'training_program_id' => $training->id,
          'user_id' => Auth::id(),
        ],
        [
          'total_score' => 0,
          'max_score' => 0,
        ]
      );

      $attempt->responses()->delete();

      $totalScore = 0;
      $maxScore = 0;

      foreach ($questionIds as $questionId) {
        $selectedOptionId = (int) $request->input('responses.' . $questionId);
        $option = TrainingSurveyOption::where('training_survey_question_id', $questionId)
          ->where('id', $selectedOptionId)
          ->first();

        if (!$option) {
          continue;
        }

        $questionMax = (int) TrainingSurveyOption::where('training_survey_question_id', $questionId)->max('score');
        $maxScore += max(0, $questionMax);
        $totalScore += (int) $option->score;

        TrainingSurveyResponse::create([
          'training_attempt_id' => $attempt->id,
          'training_survey_question_id' => $questionId,
          'training_survey_option_id' => $option->id,
          'awarded_score' => (int) $option->score,
        ]);
      }

      $attempt->update([
        'total_score' => $totalScore,
        'max_score' => $maxScore,
        'completed_at' => now(),
      ]);
    });

    return redirect()->route('tpo.training-placement.index')->with('success', 'Training completed and survey submitted.');
  }

  public function storePlacement(Request $request)
  {
    $validated = $request->validate([
      'title' => 'required|string|max:255',
      'category' => 'required|in:internship,apprenticeship,placements,project',
      'month' => 'required|integer|min:1|max:12',
      'company_name' => 'nullable|string|max:255',
      'drive_date' => 'nullable|date',
      'apply_deadline' => 'nullable|date',
      'description' => 'required|string',
      'location' => 'required|string|max:255',
      'country' => 'nullable|string|max:255',
      'logo' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:5120',
      'student_year' => 'required|in:1st Year,2nd Year,3rd Year,4th Year,5th Year,Passout',
      'subject_id' => 'required|exists:subjects,id',
    ]);

    $logoPath = null;
    if ($request->hasFile('logo')) {
      $logoPath = $request->file('logo')->store('placement_logos', 's3');
    }

    DB::transaction(function () use ($validated, $logoPath) {
      $placement = PlacementOpportunity::create([
        'title' => $validated['title'],
        'category' => $validated['category'],
        'month' => $validated['month'],
        'company_name' => $validated['company_name'] ?? null,
        'drive_date' => $validated['drive_date'] ?? null,
        'apply_deadline' => $validated['apply_deadline'] ?? null,
        'description' => $validated['description'],
        'location' => $validated['location'],
        'country' => $validated['country'] ?? null,
        'logo_path' => $logoPath,
        'student_year' => $validated['student_year'],
        'subject_id' => $validated['subject_id'],
        'is_active' => 1,
        'created_by' => Auth::id(),
      ]);
    });

    return back()->with('success', 'Placement opportunity added successfully.');
  }

  public function updatePlacement(Request $request, PlacementOpportunity $placement)
  {
    $validated = $request->validate([
      'title' => 'required|string|max:255',
      'category' => 'required|in:internship,apprenticeship,placements,project',
      'month' => 'required|integer|min:1|max:12',
      'company_name' => 'nullable|string|max:255',
      'drive_date' => 'nullable|date',
      'apply_deadline' => 'nullable|date',
      'description' => 'required|string',
      'location' => 'required|string|max:255',
      'country' => 'nullable|string|max:255',
      'logo' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:5120',
      'student_year' => 'required|in:1st Year,2nd Year,3rd Year,4th Year,5th Year,Passout',
      'subject_id' => 'required|exists:subjects,id',
      'is_active' => 'nullable|boolean',
    ]);

    $logoPath = $placement->logo_path;
    if ($request->hasFile('logo')) {
      if ($logoPath) {
        Storage::disk('s3')->delete($logoPath);
      }
      $logoPath = $request->file('logo')->store('placement_logos', 's3');
    }

    DB::transaction(function () use ($validated, $placement, $logoPath) {
      $placement->update([
        'title' => $validated['title'],
        'category' => $validated['category'],
        'month' => $validated['month'],
        'company_name' => $validated['company_name'] ?? null,
        'drive_date' => $validated['drive_date'] ?? null,
        'apply_deadline' => $validated['apply_deadline'] ?? null,
        'description' => $validated['description'],
        'location' => $validated['location'],
        'country' => $validated['country'] ?? null,
        'logo_path' => $logoPath,
        'student_year' => $validated['student_year'],
        'subject_id' => $validated['subject_id'],
        'is_active' => isset($validated['is_active']) ? 1 : 0,
      ]);
    });

    return back()->with('success', 'Placement opportunity updated successfully.');
  }

  public function destroyPlacement(PlacementOpportunity $placement)
  {
    if ($placement->logo_path) {
      Storage::disk('s3')->delete($placement->logo_path);
    }

    $placement->delete();

    return back()->with('success', 'Placement opportunity deleted successfully.');
  }

  public function analytics()
  {
    $trainings = TrainingProgram::with(['targetRoles', 'attempts'])->latest()->get();
    $analytics = $this->buildTrainingAnalytics($trainings);

    return view('tpo.training-placement.analytics', compact('analytics'));
  }

  private function roleOptions()
  {
    $masterRoles = RoleMaster::where('is_active', 1)
      ->orderBy('role_name')
      ->get(['slug', 'role_name']);

    if ($masterRoles->isNotEmpty()) {
      return $masterRoles->map(function ($role) {
        return [
          'value' => $role->slug,
          'label' => $role->role_name,
        ];
      })->values();
    }

    return UserHasRole::select('role_name')
      ->whereNotNull('role_name')
      ->distinct()
      ->orderBy('role_name')
      ->get()
      ->map(function ($role) {
        return [
          'value' => $role->role_name,
          'label' => ucfirst(str_replace('-', ' ', $role->role_name)),
        ];
      })->values();
  }

  private function buildTrainingAnalytics($trainings)
  {
    return $trainings->map(function ($training) {
      $targetRoles = $training->targetRoles->pluck('role_name')->filter()->unique()->values();

      $assignedUsers = 0;
      if ($targetRoles->isNotEmpty()) {
        $assignedUsers = UserHasRole::whereIn('role_name', $targetRoles->toArray())
          ->distinct('user_id')
          ->count('user_id');
      }

      $completedUsers = $training->attempts
        ->whereNotNull('completed_at')
        ->pluck('user_id')
        ->unique()
        ->count();

      $completionRate = $assignedUsers > 0
        ? round(($completedUsers / $assignedUsers) * 100, 2)
        : 0;

      return [
        'id' => $training->id,
        'title' => $training->title,
        'target_roles' => $targetRoles,
        'assigned_users' => $assignedUsers,
        'completed_users' => $completedUsers,
        'completion_rate' => $completionRate,
      ];
    });
  }

  private function parseSurveyOptions(string $optionsText): array
  {
    $lines = preg_split('/\r\n|\r|\n/', trim($optionsText));
    $result = [];

    foreach ($lines as $line) {
      $line = trim($line);
      if ($line === '') {
        continue;
      }

      $parts = explode('|', $line);
      $text = trim($parts[0] ?? '');
      if ($text === '') {
        continue;
      }

      $score = isset($parts[1]) ? (int) trim($parts[1]) : 0;
      $result[] = [
        'text' => $text,
        'score' => $score,
      ];
    }

    return $result;
  }

  private function placementCategoryOptions(): array
  {
    return [
      'internship' => 'Internship',
      'apprenticeship' => 'Apprenticeship',
      'placements' => 'Placements',
      'project' => 'Project',
    ];
  }

  private function monthOptions(): array
  {
    return [
      1 => 'January',
      2 => 'February',
      3 => 'March',
      4 => 'April',
      5 => 'May',
      6 => 'June',
      7 => 'July',
      8 => 'August',
      9 => 'September',
      10 => 'October',
      11 => 'November',
      12 => 'December',
    ];
  }

  private function studentYearOptions(): array
  {
    return [
      '1st Year',
      '2nd Year',
      '3rd Year',
      '4th Year',
      '5th Year',
      'Passout',
    ];
  }
}
