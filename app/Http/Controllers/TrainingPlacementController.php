<?php

namespace App\Http\Controllers;

use App\Models\PlacementOpportunity;
use App\Models\Campus;
use App\Models\RoleMaster;
use App\Models\TrainingAttempt;
use App\Models\TrainingProgram;
use App\Models\TrainingResource;
use App\Models\TrainingSurveyOption;
use App\Models\TrainingSurveyQuestion;
use App\Models\TrainingSurveyResponse;
use App\Models\TrainingTargetRole;
use App\Models\TpoEvent;
use App\Models\Subject;
use App\Models\UserHasRole;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
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

  public function index(Request $request)
  {
    $trainingSearch = trim((string) $request->input('search', ''));
    $trainingSearchLike = '%' . $trainingSearch . '%';

    $trainingsQuery = TrainingProgram::with([
      'targetRoles',
      'resources',
      'surveyQuestions.options',
      'attempts',
    ]);

    if ($trainingSearch !== '') {
      $trainingsQuery->where(function ($query) use ($trainingSearchLike) {
        $query->where('title', 'like', $trainingSearchLike)
          ->orWhere('description', 'like', $trainingSearchLike);
      });
    }

    $trainings = $trainingsQuery->latest()->get();

    $roleOptions = $this->roleOptions();

    $currentRole = UserHasRole::where('user_id', Auth::id())->value('role_name');
    $myTrainingIds = TrainingTargetRole::when($currentRole, function ($q) use ($currentRole) {
      $q->where('role_name', $currentRole);
    })->pluck('training_program_id');

    $myTrainingsQuery = TrainingProgram::with('attempts')
      ->whereIn('id', $myTrainingIds);

    if ($trainingSearch !== '') {
      $myTrainingsQuery->where(function ($query) use ($trainingSearchLike) {
        $query->where('title', 'like', $trainingSearchLike)
          ->orWhere('description', 'like', $trainingSearchLike);
      });
    }

    $myTrainings = $myTrainingsQuery
      ->latest()
      ->get();

    $placements = PlacementOpportunity::with(['subject', 'campus'])->latest()->get();
    $subjects = Subject::orderBy('title')->get(['id', 'title', 'code', 'campus_id']);
    $subjectLookup = $subjects->keyBy('id');
    $campuses = Campus::orderBy('name')->get(['id', 'name']);
    $categoryOptions = $this->placementCategoryOptions();
    $placementTypeOptions = $this->placementTypeOptions();
    $openingTypeOptions = $this->openingTypeOptions();
    $monthOptions = $this->monthOptions();
    $yearOptions = $this->studentYearOptions();

    $analytics = $this->buildTrainingAnalytics($trainings);

    return view('tpo.training-placement.index', compact(
      'trainings',
      'roleOptions',
      'myTrainings',
      'analytics',
      'placements',
      'subjects',
      'subjectLookup',
      'campuses',
      'categoryOptions',
      'placementTypeOptions',
      'openingTypeOptions',
      'monthOptions',
      'yearOptions',
      'trainingSearch'
    ));
  }

  public function placementIndex(Request $request)
  {
    $placementSearch = trim((string) $request->input('search', ''));
    $placementSearchLike = '%' . $placementSearch . '%';

    $placementsQuery = PlacementOpportunity::with(['subject', 'campus']);

    if ($placementSearch !== '') {
      $placementsQuery->where(function ($query) use ($placementSearchLike) {
        $query->where('title', 'like', $placementSearchLike)
          ->orWhere('description', 'like', $placementSearchLike)
          ->orWhere('company_name', 'like', $placementSearchLike)
          ->orWhere('location', 'like', $placementSearchLike)
          ->orWhere('country', 'like', $placementSearchLike)
          ->orWhere('category', 'like', $placementSearchLike)
          ->orWhereHas('campus', function ($campusQuery) use ($placementSearchLike) {
            $campusQuery->where('name', 'like', $placementSearchLike);
          })
          ->orWhereHas('subject', function ($subjectQuery) use ($placementSearchLike) {
            $subjectQuery->where('title', 'like', $placementSearchLike)
              ->orWhere('name', 'like', $placementSearchLike);
          });
      });
    }

    $placements = $placementsQuery->latest()->get();
    $subjects = Subject::orderBy('title')->get(['id', 'title', 'code', 'campus_id']);
    $subjectLookup = $subjects->keyBy('id');
    $campuses = Campus::orderBy('name')->get(['id', 'name']);
    $categoryOptions = $this->placementCategoryOptions();
    $placementTypeOptions = $this->placementTypeOptions();
    $openingTypeOptions = $this->openingTypeOptions();
    $monthOptions = $this->monthOptions();
    $yearOptions = $this->studentYearOptions();

    return view('tpo.training-placement.placement', compact(
      'placements',
      'subjects',
      'subjectLookup',
      'campuses',
      'categoryOptions',
      'placementTypeOptions',
      'openingTypeOptions',
      'monthOptions',
      'yearOptions',
      'placementSearch'
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
      'internship_stipend_type' => 'nullable|in:stipend,non_stipend',
      'placement_type' => 'nullable|in:on,off,online_virtual,pool',
      'opening_type' => 'nullable|in:psu,private',
      'documentation_required_text' => 'nullable|string',
    ]);

    $applicabilityScope = (string) $request->input('applicability_scope', 'selected_departments');
    $allowedScopes = ['both_campuses_all_departments', 'selected_campus_all_departments', 'selected_departments'];
    if (!in_array($applicabilityScope, $allowedScopes, true)) {
      return back()->withErrors([
        'applicability_scope' => 'Please select a valid applicability option.',
      ])->withInput();
    }

    $campusId = null;
    if ($applicabilityScope !== 'both_campuses_all_departments') {
      $request->validate([
        'campus_id' => 'required|integer|exists:campuses,id',
      ]);
      $campusId = (int) $request->input('campus_id');
    }

    if ($applicabilityScope === 'both_campuses_all_departments') {
      $subjectIds = Subject::query()
        ->pluck('id')
        ->map(fn($id) => (int) $id)
        ->unique()
        ->values();
    } elseif ($applicabilityScope === 'selected_campus_all_departments') {
      $subjectIds = Subject::query()
        ->where('campus_id', $campusId)
        ->pluck('id')
        ->map(fn($id) => (int) $id)
        ->unique()
        ->values();
    } else {
      $request->validate([
        'subject_ids' => 'required|array|min:1',
        'subject_ids.*' => 'required|integer|exists:subjects,id',
      ]);

      $subjectIds = collect($request->input('subject_ids', []))
        ->map(fn($id) => (int) $id)
        ->unique()
        ->values();

      $matchingSubjectCount = Subject::whereIn('id', $subjectIds)
        ->where('campus_id', $campusId)
        ->count();
      if ($matchingSubjectCount !== $subjectIds->count()) {
        return back()->withErrors([
          'subject_ids' => 'All selected departments must belong to the selected campus.',
        ])->withInput();
      }
    }

    if ($subjectIds->isEmpty()) {
      return back()->withErrors([
        'subject_ids' => 'No departments found for the selected applicability option.',
      ])->withInput();
    }

    $documentationRequired = $this->parseDocumentationList((string) ($validated['documentation_required_text'] ?? ''));
    $internshipStipendType = $validated['internship_stipend_type'] ?? null;
    if ($validated['category'] === 'placements') {
      if (empty($validated['placement_type'])) {
        return back()->withErrors(['placement_type' => 'Placement type is required for Placement category.'])->withInput();
      }
      if (empty($validated['opening_type'])) {
        return back()->withErrors(['opening_type' => 'Opening type is required for Placement category.'])->withInput();
      }
      if (empty($documentationRequired)) {
        return back()->withErrors(['documentation_required_text' => 'Please list required documentation for Placement category.'])->withInput();
      }
      $internshipStipendType = null;
    } elseif ($validated['category'] === 'internship') {
      if (empty($internshipStipendType)) {
        return back()->withErrors(['internship_stipend_type' => 'Please select stipend or non stipend for Internship category.'])->withInput();
      }
      $validated['placement_type'] = null;
      $validated['opening_type'] = null;
      $documentationRequired = null;
    } else {
      $internshipStipendType = null;
      $validated['placement_type'] = null;
      $validated['opening_type'] = null;
      $documentationRequired = null;
    }

    $logoPath = null;
    if ($request->hasFile('logo')) {
      $logoPath = $request->file('logo')->store('placement_logos', 's3');
    }

    DB::transaction(function () use ($validated, $logoPath, $subjectIds, $documentationRequired, $campusId, $internshipStipendType) {
      $attributes = [
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
        'campus_id' => $campusId,
        'subject_id' => $subjectIds->first(),
        'subject_ids' => $subjectIds->all(),
        'internship_stipend_type' => $internshipStipendType,
        'placement_type' => $validated['placement_type'] ?? null,
        'opening_type' => $validated['opening_type'] ?? null,
        'documentation_required' => $documentationRequired,
        'is_active' => 1,
        'created_by' => Auth::id(),
      ];

      PlacementOpportunity::create($this->filterPlacementAttributes($attributes));
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
      'internship_stipend_type' => 'nullable|in:stipend,non_stipend',
      'placement_type' => 'nullable|in:on,off,online_virtual,pool',
      'opening_type' => 'nullable|in:psu,private',
      'documentation_required_text' => 'nullable|string',
      'is_active' => 'nullable|boolean',
    ]);

    $applicabilityScope = (string) $request->input('applicability_scope', 'selected_departments');
    $allowedScopes = ['both_campuses_all_departments', 'selected_campus_all_departments', 'selected_departments'];
    if (!in_array($applicabilityScope, $allowedScopes, true)) {
      return back()->withErrors([
        'applicability_scope' => 'Please select a valid applicability option.',
      ])->withInput();
    }

    $campusId = null;
    if ($applicabilityScope !== 'both_campuses_all_departments') {
      $request->validate([
        'campus_id' => 'required|integer|exists:campuses,id',
      ]);
      $campusId = (int) $request->input('campus_id');
    }

    if ($applicabilityScope === 'both_campuses_all_departments') {
      $subjectIds = Subject::query()
        ->pluck('id')
        ->map(fn($id) => (int) $id)
        ->unique()
        ->values();
    } elseif ($applicabilityScope === 'selected_campus_all_departments') {
      $subjectIds = Subject::query()
        ->where('campus_id', $campusId)
        ->pluck('id')
        ->map(fn($id) => (int) $id)
        ->unique()
        ->values();
    } else {
      $request->validate([
        'subject_ids' => 'required|array|min:1',
        'subject_ids.*' => 'required|integer|exists:subjects,id',
      ]);

      $subjectIds = collect($request->input('subject_ids', []))
        ->map(fn($id) => (int) $id)
        ->unique()
        ->values();

      $matchingSubjectCount = Subject::whereIn('id', $subjectIds)
        ->where('campus_id', $campusId)
        ->count();
      if ($matchingSubjectCount !== $subjectIds->count()) {
        return back()->withErrors([
          'subject_ids' => 'All selected departments must belong to the selected campus.',
        ])->withInput();
      }
    }

    if ($subjectIds->isEmpty()) {
      return back()->withErrors([
        'subject_ids' => 'No departments found for the selected applicability option.',
      ])->withInput();
    }

    $documentationRequired = $this->parseDocumentationList((string) ($validated['documentation_required_text'] ?? ''));
    $internshipStipendType = $validated['internship_stipend_type'] ?? null;
    if ($validated['category'] === 'placements') {
      if (empty($validated['placement_type'])) {
        return back()->withErrors(['placement_type' => 'Placement type is required for Placement category.'])->withInput();
      }
      if (empty($validated['opening_type'])) {
        return back()->withErrors(['opening_type' => 'Opening type is required for Placement category.'])->withInput();
      }
      if (empty($documentationRequired)) {
        return back()->withErrors(['documentation_required_text' => 'Please list required documentation for Placement category.'])->withInput();
      }
      $internshipStipendType = null;
    } elseif ($validated['category'] === 'internship') {
      if (empty($internshipStipendType)) {
        return back()->withErrors(['internship_stipend_type' => 'Please select stipend or non stipend for Internship category.'])->withInput();
      }
      $validated['placement_type'] = null;
      $validated['opening_type'] = null;
      $documentationRequired = null;
    } else {
      $internshipStipendType = null;
      $validated['placement_type'] = null;
      $validated['opening_type'] = null;
      $documentationRequired = null;
    }

    $logoPath = $placement->logo_path;
    if ($request->hasFile('logo')) {
      if ($logoPath) {
        Storage::disk('s3')->delete($logoPath);
      }
      $logoPath = $request->file('logo')->store('placement_logos', 's3');
    }

    DB::transaction(function () use ($validated, $placement, $logoPath, $subjectIds, $documentationRequired, $campusId, $internshipStipendType) {
      $attributes = [
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
        'campus_id' => $campusId,
        'subject_id' => $subjectIds->first(),
        'subject_ids' => $subjectIds->all(),
        'internship_stipend_type' => $internshipStipendType,
        'placement_type' => $validated['placement_type'] ?? null,
        'opening_type' => $validated['opening_type'] ?? null,
        'documentation_required' => $documentationRequired,
        'is_active' => isset($validated['is_active']) ? 1 : 0,
      ];

      $placement->update($this->filterPlacementAttributes($attributes));
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

  public function eventsIndex(Request $request)
  {
    $eventSearch = trim((string) $request->input('search', ''));
    $eventSearchLike = '%' . $eventSearch . '%';

    $eventsQuery = TpoEvent::with(['campus', 'subject', 'approver']);

    if ($eventSearch !== '') {
      $eventsQuery->where(function ($query) use ($eventSearchLike) {
        $query->where('title', 'like', $eventSearchLike)
          ->orWhere('resource_person', 'like', $eventSearchLike)
          ->orWhere('program_description', 'like', $eventSearchLike)
          ->orWhere('event_type', 'like', $eventSearchLike)
          ->orWhereHas('campus', function ($campusQuery) use ($eventSearchLike) {
            $campusQuery->where('name', 'like', $eventSearchLike);
          })
          ->orWhereHas('subject', function ($subjectQuery) use ($eventSearchLike) {
            $subjectQuery->where('title', 'like', $eventSearchLike)
              ->orWhere('name', 'like', $eventSearchLike);
          });
      });
    }

    $events = $eventsQuery
      ->latest('event_date')
      ->latest('id')
      ->get();

    $campuses = Campus::orderBy('name')->get(['id', 'name']);
    $subjects = Subject::orderBy('title')->get(['id', 'title', 'code', 'campus_id']);
    $eventTypeOptions = $this->eventTypeOptions();

    return view('tpo.training-placement.events', compact(
      'events',
      'campuses',
      'subjects',
      'eventTypeOptions',
      'eventSearch'
    ));
  }

  public function storeEvent(Request $request)
  {
    $validated = $request->validate([
      'event_type' => 'required|in:training_program,guest_lecture,workshop',
      'title' => 'required|string|max:255',
      'resource_person' => 'nullable|string|max:255',
      'campus_id' => 'required|integer|exists:campuses,id',
      'subject_id' => 'required|integer|exists:subjects,id',
      'event_date' => 'required|date',
      'program_description' => 'required|string',
      'participant_count' => 'required|integer|min:0',
      'report_file' => 'nullable|file|mimes:pdf,doc,docx,jpg,jpeg,png|max:10240',
    ]);

    $subjectBelongsToCampus = Subject::where('id', (int) $validated['subject_id'])
      ->where('campus_id', (int) $validated['campus_id'])
      ->exists();

    if (!$subjectBelongsToCampus) {
      return back()->withErrors([
        'subject_id' => 'Selected department must belong to the selected campus.',
      ])->withInput();
    }

    $reportPath = null;
    if ($request->hasFile('report_file')) {
      $reportPath = $request->file('report_file')->store('tpo_event_reports', 's3');
    }

    TpoEvent::create([
      'event_type' => $validated['event_type'],
      'title' => $validated['title'],
      'resource_person' => $validated['resource_person'] ?? null,
      'campus_id' => (int) $validated['campus_id'],
      'subject_id' => (int) $validated['subject_id'],
      'event_date' => $validated['event_date'],
      'program_description' => $validated['program_description'],
      'participant_count' => (int) $validated['participant_count'],
      'report_path' => $reportPath,
      'approval_status' => 'pending',
      'approved_by' => null,
      'approved_at' => null,
      'created_by' => Auth::id(),
    ]);

    return back()->with('success', 'Event added successfully.');
  }

  public function updateEvent(Request $request, TpoEvent $event)
  {
    $validated = $request->validate([
      'event_type' => 'required|in:training_program,guest_lecture,workshop',
      'title' => 'required|string|max:255',
      'resource_person' => 'nullable|string|max:255',
      'campus_id' => 'required|integer|exists:campuses,id',
      'subject_id' => 'required|integer|exists:subjects,id',
      'event_date' => 'required|date',
      'program_description' => 'required|string',
      'participant_count' => 'required|integer|min:0',
      'report_file' => 'nullable|file|mimes:pdf,doc,docx,jpg,jpeg,png|max:10240',
    ]);

    $subjectBelongsToCampus = Subject::where('id', (int) $validated['subject_id'])
      ->where('campus_id', (int) $validated['campus_id'])
      ->exists();

    if (!$subjectBelongsToCampus) {
      return back()->withErrors([
        'subject_id' => 'Selected department must belong to the selected campus.',
      ])->withInput();
    }

    $reportPath = $event->report_path;
    if ($request->hasFile('report_file')) {
      if (!empty($reportPath)) {
        Storage::disk('s3')->delete($reportPath);
      }
      $reportPath = $request->file('report_file')->store('tpo_event_reports', 's3');
    }

    $event->update([
      'event_type' => $validated['event_type'],
      'title' => $validated['title'],
      'resource_person' => $validated['resource_person'] ?? null,
      'campus_id' => (int) $validated['campus_id'],
      'subject_id' => (int) $validated['subject_id'],
      'event_date' => $validated['event_date'],
      'program_description' => $validated['program_description'],
      'participant_count' => (int) $validated['participant_count'],
      'report_path' => $reportPath,
      'approval_status' => 'pending',
      'approved_by' => null,
      'approved_at' => null,
    ]);

    return back()->with('success', 'Event updated and moved to pending approval.');
  }

  public function destroyEvent(TpoEvent $event)
  {
    if (!empty($event->report_path)) {
      Storage::disk('s3')->delete($event->report_path);
    }

    $event->delete();

    return back()->with('success', 'Event deleted successfully.');
  }

  public function principalEventsIndex()
  {
    $events = TpoEvent::with(['campus', 'subject', 'creator', 'approver'])
      ->latest('event_date')
      ->latest('id')
      ->get();

    $eventTypeOptions = $this->eventTypeOptions();

    return view('principal.tpo-events.index', compact('events', 'eventTypeOptions'));
  }

  public function principalApproveEvent(Request $request, TpoEvent $event)
  {
    $validated = $request->validate([
      'approval_status' => 'required|in:approved,rejected',
    ]);

    $status = $validated['approval_status'];
    $event->update([
      'approval_status' => $status,
      'approved_by' => Auth::id(),
      'approved_at' => $status === 'approved' ? now() : null,
    ]);

    return back()->with('success', 'Event approval status updated successfully.');
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
      'placements' => 'Placement',
      'project' => 'Project',
    ];
  }

  private function placementTypeOptions(): array
  {
    return [
      'on' => 'On Campus',
      'off' => 'Off Campus',
      'online_virtual' => 'Online / Virtual',
      'pool' => 'Pool Campus',
    ];
  }

  private function openingTypeOptions(): array
  {
    return [
      'psu' => 'PSU',
      'private' => 'Private',
    ];
  }

  private function eventTypeOptions(): array
  {
    return [
      'training_program' => 'Training Program',
      'guest_lecture' => 'Guest Lecture',
      'workshop' => 'Workshop',
    ];
  }

  private function parseDocumentationList(string $documentationText): array
  {
    $lines = preg_split('/\r\n|\r|\n/', trim($documentationText));
    if (!is_array($lines)) {
      return [];
    }

    return collect($lines)
      ->map(fn($line) => trim((string) $line))
      ->filter()
      ->unique()
      ->values()
      ->all();
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

  private function filterPlacementAttributes(array $attributes): array
  {
    $columns = $this->placementColumnListing();
    if (empty($columns)) {
      return [];
    }

    return collect($attributes)
      ->filter(function ($value, $key) use ($columns) {
        return in_array($key, $columns, true);
      })
      ->all();
  }

  private function placementColumnListing(): array
  {
    static $columns = null;

    if (is_array($columns)) {
      return $columns;
    }

    try {
      $columns = Schema::getColumnListing('placement_opportunities');
    } catch (\Throwable $e) {
      $columns = [];
    }

    return $columns;
  }
}
