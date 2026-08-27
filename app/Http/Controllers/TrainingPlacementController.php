<?php

namespace App\Http\Controllers;

use App\Models\PlacementOpportunity;
use App\Models\PlacementApplication;
use App\Models\Campus;
use App\Models\RoleMaster;
use App\Models\TrainingAttempt;
use App\Models\TrainingProgram;
use App\Models\TrainingResource;
use App\Models\TrainingSurveyOption;
use App\Models\TrainingSurveyQuestion;
use App\Models\TrainingSurveyResponse;
use App\Models\TrainingTargetRole;
use App\Models\TrainingPlacementOptIn;
use App\Models\TrainingPlacementFormTemplate;
use App\Models\TpoEvent;
use App\Models\Subject;
use App\Models\StudentMaster;
use App\Models\UserHasRole;
use App\Models\User;
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
    $selectedCategory = trim((string) $request->input('category', ''));
    $dateFrom = trim((string) $request->input('date_from', ''));
    $dateTo = trim((string) $request->input('date_to', ''));

    if ($dateFrom !== '' && $dateTo !== '' && $dateFrom > $dateTo) {
      [$dateFrom, $dateTo] = [$dateTo, $dateFrom];
    }

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

    if ($selectedCategory !== '') {
      if (in_array($selectedCategory, ['placements', 'placement'], true)) {
        $placementsQuery->whereIn('category', ['placements', 'placement']);
      } else {
        $placementsQuery->where('category', $selectedCategory);
      }
    }

    if ($dateFrom !== '') {
      $placementsQuery->whereDate('drive_date', '>=', $dateFrom);
    }

    if ($dateTo !== '') {
      $placementsQuery->whereDate('drive_date', '<=', $dateTo);
    }

    $placements = $placementsQuery->withCount('applications')->latest()->get();

    $subjects = Subject::orderBy('title')->get(['id', 'title', 'code', 'campus_id']);
    $subjectLookup = $subjects->keyBy('id');
    $campuses = Campus::orderBy('name')->get(['id', 'name']);
    $categoryOptions = $this->placementCategoryOptions();
    $placementTypeOptions = $this->placementTypeOptions();
    $openingTypeOptions = $this->openingTypeOptions();
    $documentationRequirementOptions = $this->documentationRequirementOptions();
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
      'documentationRequirementOptions',
      'monthOptions',
      'yearOptions',
      'placementSearch',
      'selectedCategory',
      'dateFrom',
      'dateTo'
    ));
  }

  public function jobApplicationsIndex(Request $request)
  {
    $search = trim((string) $request->input('search', ''));
    $placementId = (int) $request->input('placement_id', 0);
    $selectedStatus = trim((string) $request->input('status', ''));
    $searchLike = '%' . $search . '%';

    $statusOptions = [
      'submitted' => 'Submitted',
      'under_review' => 'Under Review',
      'shortlisted' => 'Shortlisted',
      'interview_scheduled' => 'Interview Scheduled',
      'selected' => 'Selected',
      'rejected' => 'Rejected',
      'on_hold' => 'On Hold',
    ];

    $placementsForFilter = PlacementOpportunity::query()
      ->orderByDesc('id')
      ->get(['id', 'title', 'company_name']);

    $documentationLabelMap = collect($this->documentationRequirementOptions())
      ->mapWithKeys(function ($label, $key) {
        return [$this->normalizeDocKey((string) $key) => (string) $label];
      })
      ->all();

    $applicationsQuery = PlacementApplication::query()
      ->with([
        'placement:id,title,company_name,category,documentation_required',
        'student:id,first_name,last_name,roll_no,register_no,mail_id',
      ]);

    if ($placementId > 0) {
      $applicationsQuery->where('placement_opportunity_id', $placementId);
    }

    if ($selectedStatus !== '' && array_key_exists($selectedStatus, $statusOptions)) {
      $applicationsQuery->where('status', $selectedStatus);
    }

    if ($search !== '') {
      $applicationsQuery->where(function ($query) use ($searchLike) {
        $query->whereHas('placement', function ($placementQuery) use ($searchLike) {
          $placementQuery->where('title', 'like', $searchLike)
            ->orWhere('company_name', 'like', $searchLike);
        })->orWhereHas('student', function ($studentQuery) use ($searchLike) {
          $studentQuery->where('first_name', 'like', $searchLike)
            ->orWhere('last_name', 'like', $searchLike)
            ->orWhere('roll_no', 'like', $searchLike)
            ->orWhere('register_no', 'like', $searchLike)
            ->orWhere('mail_id', 'like', $searchLike);
        });
      });
    }

    $applications = $applicationsQuery
      ->latest('applied_at')
      ->latest('id')
      ->paginate(25)
      ->appends($request->query());

    return view('tpo.training-placement.job-applications', [
      'applications' => $applications,
      'search' => $search,
      'placementId' => $placementId,
      'selectedStatus' => $selectedStatus,
      'statusOptions' => $statusOptions,
      'placementsForFilter' => $placementsForFilter,
      'documentationLabelMap' => $documentationLabelMap,
    ]);
  }

  public function updateJobApplicationProgress(Request $request, PlacementApplication $application)
  {
    $statusOptions = [
      'submitted',
      'under_review',
      'shortlisted',
      'interview_scheduled',
      'selected',
      'rejected',
      'on_hold',
    ];

    $validated = $request->validate([
      'status' => 'required|string|in:' . implode(',', $statusOptions),
      'remarks' => 'nullable|string|max:1000',
    ]);

    $application->status = (string) $validated['status'];
    $application->remarks = trim((string) ($validated['remarks'] ?? '')) ?: null;
    $application->save();

    return back()->with('success', 'Application progress updated successfully.');
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
      'category' => 'required|in:internship,apprenticeship,placement,placements,project',
      'month' => 'required|integer|min:1|max:12',
      'company_name' => 'nullable|string|max:255',
      'drive_date' => 'nullable|date',
      'apply_deadline' => 'nullable|date',
      'description' => 'required|string',
      'location' => 'required|string|max:255',
      'country' => 'nullable|string|max:255',
      'logo' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:5120',
      'student_year' => 'required|integer|in:1,2,3,4,5',
      'internship_stipend_type' => 'nullable|in:stipend,non_stipend',
      'placement_type' => 'nullable|in:on,off,online_virtual,pool',
      'opening_type' => 'nullable|in:psu,private',
      'documentation_required' => 'nullable|array',
      'documentation_required.*' => 'nullable|string|max:120',
      'documentation_required_custom' => 'nullable|string|max:1200',
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

    $documentationRequired = $this->normalizeDocumentationRequirements(
      (array) ($validated['documentation_required'] ?? []),
      (string) ($validated['documentation_required_custom'] ?? '')
    );
    $normalizedCategory = $validated['category'] === 'placement' ? 'placements' : $validated['category'];
    $internshipStipendType = $validated['internship_stipend_type'] ?? null;
    if ($normalizedCategory === 'placements') {
      if (empty($validated['placement_type'])) {
        return back()->withErrors(['placement_type' => 'Placement type is required for Placement category.'])->withInput();
      }
      if (empty($validated['opening_type'])) {
        return back()->withErrors(['opening_type' => 'Opening type is required for Placement category.'])->withInput();
      }
      if (empty($documentationRequired)) {
        return back()->withErrors(['documentation_required' => 'Please select at least one required document for Placement category.'])->withInput();
      }
      $internshipStipendType = null;
    } elseif ($normalizedCategory === 'internship') {
      if (empty($internshipStipendType)) {
        return back()->withErrors(['internship_stipend_type' => 'Please select stipend or non stipend for Internship category.'])->withInput();
      }
      $validated['placement_type'] = null;
      $validated['opening_type'] = null;
    } else {
      $internshipStipendType = null;
      $validated['placement_type'] = null;
      $validated['opening_type'] = null;
    }

    $logoPath = null;
    if ($request->hasFile('logo')) {
      $logoPath = $request->file('logo')->store('placement_logos', 's3');
    }

    DB::transaction(function () use ($validated, $logoPath, $subjectIds, $documentationRequired, $campusId, $internshipStipendType, $normalizedCategory) {
      $attributes = [
        'title' => $validated['title'],
        'category' => $normalizedCategory,
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
      'category' => 'required|in:internship,apprenticeship,placement,placements,project',
      'month' => 'required|integer|min:1|max:12',
      'company_name' => 'nullable|string|max:255',
      'drive_date' => 'nullable|date',
      'apply_deadline' => 'nullable|date',
      'description' => 'required|string',
      'location' => 'required|string|max:255',
      'country' => 'nullable|string|max:255',
      'logo' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:5120',
      'student_year' => 'required|integer|in:1,2,3,4,5',
      'internship_stipend_type' => 'nullable|in:stipend,non_stipend',
      'placement_type' => 'nullable|in:on,off,online_virtual,pool',
      'opening_type' => 'nullable|in:psu,private',
      'documentation_required' => 'nullable|array',
      'documentation_required.*' => 'nullable|string|max:120',
      'documentation_required_custom' => 'nullable|string|max:1200',
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

    $documentationRequired = $this->normalizeDocumentationRequirements(
      (array) ($validated['documentation_required'] ?? []),
      (string) ($validated['documentation_required_custom'] ?? '')
    );
    $normalizedCategory = $validated['category'] === 'placement' ? 'placements' : $validated['category'];
    $internshipStipendType = $validated['internship_stipend_type'] ?? null;
    if ($normalizedCategory === 'placements') {
      if (empty($validated['placement_type'])) {
        return back()->withErrors(['placement_type' => 'Placement type is required for Placement category.'])->withInput();
      }
      if (empty($validated['opening_type'])) {
        return back()->withErrors(['opening_type' => 'Opening type is required for Placement category.'])->withInput();
      }
      if (empty($documentationRequired)) {
        return back()->withErrors(['documentation_required' => 'Please select at least one required document for Placement category.'])->withInput();
      }
      $internshipStipendType = null;
    } elseif ($normalizedCategory === 'internship') {
      if (empty($internshipStipendType)) {
        return back()->withErrors(['internship_stipend_type' => 'Please select stipend or non stipend for Internship category.'])->withInput();
      }
      $validated['placement_type'] = null;
      $validated['opening_type'] = null;
    } else {
      $internshipStipendType = null;
      $validated['placement_type'] = null;
      $validated['opening_type'] = null;
    }

    $logoPath = $placement->logo_path;
    if ($request->hasFile('logo')) {
      if ($logoPath) {
        Storage::disk('s3')->delete($logoPath);
      }
      $logoPath = $request->file('logo')->store('placement_logos', 's3');
    }

    DB::transaction(function () use ($validated, $placement, $logoPath, $subjectIds, $documentationRequired, $campusId, $internshipStipendType, $normalizedCategory) {
      $attributes = [
        'title' => $validated['title'],
        'category' => $normalizedCategory,
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
    if ($placement->applications()->exists()) {
      return back()->with('error', 'This job description cannot be deleted because students have already applied.');
    }

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
    $subjectSelection = (string) $request->input('subject_id', '');
    $isAllDepartments = $subjectSelection === 'all';

    $validated = $request->validate([
      'event_type' => 'required|in:guest_lecture,workshop',
      'title' => 'required|string|max:255',
      'resource_person' => 'required|string|max:255',
      'campus_id' => 'required|integer|exists:campuses,id',
      'subject_id' => $isAllDepartments ? 'required|in:all' : 'required|integer|exists:subjects,id',
      'event_date' => 'required|date',
      'program_description' => 'nullable|string',
      'participant_count' => 'required|integer|min:0',
      'report_file' => 'nullable|file|mimes:ppt,pptx,pdf,doc,docx|max:51200',
    ]);

    if (!$isAllDepartments) {
      $subjectBelongsToCampus = Subject::where('id', (int) $validated['subject_id'])
        ->where('campus_id', (int) $validated['campus_id'])
        ->exists();

      if (!$subjectBelongsToCampus) {
        return back()->withErrors([
          'subject_id' => 'Selected department must belong to the selected campus.',
        ])->withInput();
      }
    }

    $reportPath = null;
    if ($request->hasFile('report_file')) {
      $reportPath = $request->file('report_file')->store('tpo_event_reports', 's3');
    }

    TpoEvent::create([
      'event_type' => $validated['event_type'],
      'title' => $validated['title'],
      'resource_person' => $validated['resource_person'],
      'campus_id' => (int) $validated['campus_id'],
      'subject_id' => $isAllDepartments ? null : (int) $validated['subject_id'],
      'event_date' => $validated['event_date'],
      'program_description' => trim((string) ($validated['program_description'] ?? '')),
      'participant_count' => (int) $validated['participant_count'],
      'report_path' => $reportPath,
      'approval_status' => 'pending',
      'approved_by' => null,
      'approved_at' => null,
      'created_by' => Auth::id(),
    ]);

    return back()->with('success', 'External facilitator event added successfully.');
  }

  public function updateEvent(Request $request, TpoEvent $event)
  {
    $subjectSelection = (string) $request->input('subject_id', '');
    $isAllDepartments = $subjectSelection === 'all';

    $validated = $request->validate([
      'event_type' => 'required|in:guest_lecture,workshop',
      'title' => 'required|string|max:255',
      'resource_person' => 'required|string|max:255',
      'campus_id' => 'required|integer|exists:campuses,id',
      'subject_id' => $isAllDepartments ? 'required|in:all' : 'required|integer|exists:subjects,id',
      'event_date' => 'required|date',
      'program_description' => 'nullable|string',
      'participant_count' => 'required|integer|min:0',
      'report_file' => 'nullable|file|mimes:ppt,pptx,pdf,doc,docx|max:51200',
    ]);

    if (!$isAllDepartments) {
      $subjectBelongsToCampus = Subject::where('id', (int) $validated['subject_id'])
        ->where('campus_id', (int) $validated['campus_id'])
        ->exists();

      if (!$subjectBelongsToCampus) {
        return back()->withErrors([
          'subject_id' => 'Selected department must belong to the selected campus.',
        ])->withInput();
      }
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
      'resource_person' => $validated['resource_person'],
      'campus_id' => (int) $validated['campus_id'],
      'subject_id' => $isAllDepartments ? null : (int) $validated['subject_id'],
      'event_date' => $validated['event_date'],
      'program_description' => trim((string) ($validated['program_description'] ?? '')),
      'participant_count' => (int) $validated['participant_count'],
      'report_path' => $reportPath,
      'approval_status' => 'pending',
      'approved_by' => null,
      'approved_at' => null,
    ]);

    return back()->with('success', 'External facilitator event updated and moved to pending approval.');
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

  public function placementReportIndex(Request $request)
  {
    $search = trim((string) $request->input('search', ''));
    $placementId = (int) $request->input('placement_id', 0);
    $searchLike = '%' . $search . '%';

    $baseQuery = PlacementApplication::query()
      ->with([
        'placement:id,title,company_name,category',
        'student' => function ($query) {
          $query->with('campusmaster:id,name');
        },
      ])
      ->where('status', 'selected');

    if ($placementId > 0) {
      $baseQuery->where('placement_opportunity_id', $placementId);
    }

    if ($search !== '') {
      $baseQuery->where(function ($query) use ($searchLike) {
        $query->whereHas('placement', function ($placementQuery) use ($searchLike) {
          $placementQuery->where('title', 'like', $searchLike)
            ->orWhere('company_name', 'like', $searchLike);
        })->orWhereHas('student', function ($studentQuery) use ($searchLike) {
          $studentQuery->where('first_name', 'like', $searchLike)
            ->orWhere('last_name', 'like', $searchLike)
            ->orWhere('roll_no', 'like', $searchLike)
            ->orWhere('register_no', 'like', $searchLike)
            ->orWhere('mail_id', 'like', $searchLike);
        });
      });
    }

    $selectedRows = (clone $baseQuery)->get();

    $insights = [
      'total_selected_records' => $selectedRows->count(),
      'unique_selected_students' => $selectedRows->pluck('student_id')->filter()->unique()->count(),
      'unique_selected_jobs' => $selectedRows->pluck('placement_opportunity_id')->filter()->unique()->count(),
      'unique_companies' => $selectedRows->map(fn($row) => (string) ($row->placement->company_name ?? ''))
        ->filter(fn($name) => $name !== '')
        ->unique()
        ->count(),
      'selection_by_job' => $selectedRows
        ->groupBy(fn($row) => (string) ($row->placement->title ?? 'N/A'))
        ->map(fn($rows, $title) => [
          'title' => $title,
          'count' => $rows->count(),
        ])
        ->sortByDesc('count')
        ->values(),
      'selection_by_company' => $selectedRows
        ->groupBy(fn($row) => (string) ($row->placement->company_name ?? 'N/A'))
        ->map(fn($rows, $company) => [
          'company' => $company,
          'count' => $rows->count(),
        ])
        ->sortByDesc('count')
        ->values(),
    ];

    $selectedApplications = $baseQuery
      ->latest('updated_at')
      ->latest('id')
      ->paginate(25)
      ->appends($request->query());

    $placementsForFilter = PlacementOpportunity::query()
      ->orderByDesc('id')
      ->get(['id', 'title', 'company_name']);

    return view('tpo.training-placement.placement-report', [
      'selectedApplications' => $selectedApplications,
      'search' => $search,
      'placementId' => $placementId,
      'placementsForFilter' => $placementsForFilter,
      'insights' => $insights,
    ]);
  }

  public function optedStudentsIndex(Request $request)
  {
    return redirect()->route('tpo.training-placement.student-opt-in-forms.index', [
      'search' => (string) $request->input('search', ''),
    ]);
  }

  public function studentTrainingAnalysis(User $user)
  {
    $attempts = TrainingAttempt::query()
      ->with('trainingProgram:id,title')
      ->where('user_id', $user->id)
      ->orderByDesc('completed_at')
      ->orderByDesc('id')
      ->get();

    if ($attempts->isEmpty()) {
      return redirect()
        ->route('tpo.training-placement.student-opt-in-forms.index')
        ->with('error', 'No training analysis report found for the selected student.');
    }

    $studentMeta = DB::table('student_master_user_pivots as smup')
      ->join('student_masters as sm', 'sm.id', '=', 'smup.student_master_id')
      ->leftJoin('department_masters as dm', 'dm.id', '=', 'sm.department')
      ->leftJoin('campuses as c', 'c.id', '=', 'sm.campus_id')
      ->where('smup.user_id', $user->id)
      ->whereNull('smup.deleted_at')
      ->select([
        'sm.id as student_master_id',
        'sm.first_name',
        'sm.last_name',
        'sm.roll_no',
        'sm.register_no',
        'dm.name as department_name',
        'c.name as campus_name',
      ])
      ->first();

    $summary = [
      'total_attempts' => $attempts->count(),
      'completed_attempts' => $attempts->whereNotNull('completed_at')->count(),
      'avg_score_pct' => round($attempts
        ->filter(fn($attempt) => (int) $attempt->max_score > 0)
        ->map(fn($attempt) => ((int) $attempt->total_score / max(1, (int) $attempt->max_score)) * 100)
        ->avg() ?? 0, 2),
      'highest_score_pct' => round($attempts
        ->filter(fn($attempt) => (int) $attempt->max_score > 0)
        ->map(fn($attempt) => ((int) $attempt->total_score / max(1, (int) $attempt->max_score)) * 100)
        ->max() ?? 0, 2),
      'latest_completion' => optional($attempts->whereNotNull('completed_at')->first())->completed_at,
    ];

    return view('tpo.training-placement.student-training-analysis', [
      'user' => $user,
      'studentMeta' => $studentMeta,
      'attempts' => $attempts,
      'summary' => $summary,
    ]);
  }

  public function studentOptInFormsIndex(Request $request)
  {
    $search = trim((string) $request->input('search', ''));
    $hasOptInTable = Schema::hasTable('training_placement_opt_ins');
    $hasApprovalColumns = $hasOptInTable
      && Schema::hasColumn('training_placement_opt_ins', 'approval_status')
      && Schema::hasColumn('training_placement_opt_ins', 'approved_by')
      && Schema::hasColumn('training_placement_opt_ins', 'approved_at');
    $masterTemplate = null;
    $hasNewProgramColumn = Schema::hasColumn('student_masters', 'new_program_id');
    $hasAcademicPathwayColumn = Schema::hasColumn('student_masters', 'academic_pathway_id');
    $hasDegreeTrackColumn = Schema::hasColumn('student_masters', 'degree_track_id');
    $hasSelectedComboColumn = Schema::hasColumn('student_masters', 'selected_combo_id');
    $hasSubjectsTable = Schema::hasTable('subjects');
    $hasSubjectCodeColumn = $hasSubjectsTable && Schema::hasColumn('subjects', 'code');
    $hasSubjectTitleColumn = $hasSubjectsTable && Schema::hasColumn('subjects', 'title');
    $hasSubjectNameColumn = $hasSubjectsTable && Schema::hasColumn('subjects', 'name');

    if (Schema::hasTable('training_placement_form_templates')) {
      $masterTemplate = TrainingPlacementFormTemplate::query()
        ->where('is_active', 1)
        ->latest('id')
        ->first();
    }

    $studentsQuery = DB::table('student_masters as sm')
      ->leftJoin('department_masters as dm', 'dm.id', '=', 'sm.department')
      ->leftJoin('campuses as c', 'c.id', '=', 'sm.campus_id')
      ->leftJoin('batch_masters as bm', 'bm.id', '=', 'sm.batch')
      ->leftJoin('student_master_user_pivots as smup', function ($join) {
        $join->on('smup.student_master_id', '=', 'sm.id')
          ->whereNull('smup.deleted_at');
      })
      ->leftJoin('users as u', 'u.id', '=', 'smup.user_id')
      ->where(function ($query) {
        $query->whereNull('sm.is_deleted')->orWhere('sm.is_deleted', 0);
      })
      ->where(function ($query) {
        $query->whereNull('sm.is_left')->orWhere('sm.is_left', 0);
      })
      ->select([
        'sm.id as student_id',
        'sm.first_name',
        'sm.last_name',
        'sm.roll_no',
        'sm.register_no',
        'sm.mail_id',
        'dm.name as department_name',
        'c.name as campus_name',
        'bm.batch_name',
        'u.id as user_id',
        'u.email as user_email',
      ]);

    if ($hasNewProgramColumn) {
      $studentsQuery->leftJoin('student_program as sp', 'sp.id', '=', 'sm.new_program_id')
        ->addSelect([
          'sm.new_program_id',
          'sp.name as enrolled_program_name_base',
        ]);
    } else {
      $studentsQuery->addSelect(DB::raw('NULL as new_program_id'));
      $studentsQuery->addSelect(DB::raw('NULL as enrolled_program_name_base'));
    }

    if ($hasAcademicPathwayColumn && Schema::hasTable('academic_pathway_masters')) {
      $studentsQuery->leftJoin('academic_pathway_masters as apm', 'apm.id', '=', 'sm.academic_pathway_id')
        ->addSelect([
          'sm.academic_pathway_id',
          'apm.name as academic_pathway_name',
        ]);
    } else {
      $studentsQuery->addSelect(DB::raw('NULL as academic_pathway_id'));
      $studentsQuery->addSelect(DB::raw('NULL as academic_pathway_name'));
    }

    if ($hasDegreeTrackColumn && Schema::hasTable('degree_track_masters')) {
      $studentsQuery->leftJoin('degree_track_masters as dtm', 'dtm.id', '=', 'sm.degree_track_id')
        ->addSelect([
          'sm.degree_track_id',
          'dtm.name as degree_track_name',
        ]);
    } else {
      $studentsQuery->addSelect(DB::raw('NULL as degree_track_id'));
      $studentsQuery->addSelect(DB::raw('NULL as degree_track_name'));
    }

    if ($hasSelectedComboColumn && $hasSubjectsTable) {
      $studentsQuery->leftJoin('subjects as subj', 'subj.id', '=', 'sm.selected_combo_id')
        ->addSelect('sm.selected_combo_id');

      if ($hasSubjectCodeColumn) {
        $studentsQuery->addSelect('subj.code as selected_combo_code');
      } else {
        $studentsQuery->addSelect(DB::raw('NULL as selected_combo_code'));
      }

      if ($hasSubjectTitleColumn) {
        $studentsQuery->addSelect('subj.title as selected_combo_title');
      } else {
        $studentsQuery->addSelect(DB::raw('NULL as selected_combo_title'));
      }

      if ($hasSubjectNameColumn) {
        $studentsQuery->addSelect('subj.name as selected_combo_name');
      } else {
        $studentsQuery->addSelect(DB::raw('NULL as selected_combo_name'));
      }
    } else {
      $studentsQuery->addSelect(DB::raw('NULL as selected_combo_id'));
      $studentsQuery->addSelect(DB::raw('NULL as selected_combo_code'));
      $studentsQuery->addSelect(DB::raw('NULL as selected_combo_title'));
      $studentsQuery->addSelect(DB::raw('NULL as selected_combo_name'));
    }

    if (Schema::hasColumn('student_masters', 'current_year')) {
      $studentsQuery->addSelect('sm.current_year');
    }

    if (Schema::hasTable('student_semester_configs')) {
      $studentsQuery->leftJoin('student_semester_configs as ssc', function ($join) {
        $join->on('ssc.student_id', '=', 'sm.id')
          ->where('ssc.current_semester', 1);

        if (Schema::hasColumn('student_semester_configs', 'deleted_at')) {
          $join->whereNull('ssc.deleted_at');
        }
      });

      if (Schema::hasTable('semesters')) {
        $studentsQuery->leftJoin('semesters as sem', 'sem.id', '=', 'ssc.semester_id')
          ->addSelect([
            'ssc.semester_id as current_semester_id',
            'sem.title as current_semester_title',
          ]);
      } else {
        $studentsQuery->addSelect('ssc.semester_id as current_semester_id');
      }
    }

    if ($hasOptInTable) {
      $studentsQuery->join('training_placement_opt_ins as tpoi', function ($join) {
        $join->on('tpoi.student_id', '=', 'sm.id')
          ->whereNotNull('tpoi.form_file_path')
          ->where('tpoi.policy_accepted', 1)
          ->whereNotNull('tpoi.opted_at');
      })->addSelect([
        'tpoi.form_file_path',
        'tpoi.policy_accepted',
        'tpoi.policy_accepted_at',
        'tpoi.opted_at',
      ]);

      if ($hasApprovalColumns) {
        $studentsQuery->addSelect([
          'tpoi.approval_status',
          'tpoi.approved_by',
          'tpoi.approved_at',
        ]);

        if (Schema::hasColumn('training_placement_opt_ins', 'rejection_reason')) {
          $studentsQuery->addSelect('tpoi.rejection_reason');
        }
        if (Schema::hasColumn('training_placement_opt_ins', 'rejected_by')) {
          $studentsQuery->addSelect('tpoi.rejected_by');
        }
        if (Schema::hasColumn('training_placement_opt_ins', 'rejected_at')) {
          $studentsQuery->addSelect('tpoi.rejected_at');
        }
      }
    } else {
      // Do not expose full student list when application storage is unavailable.
      $studentsQuery->whereRaw('1 = 0');
    }

    if ($search !== '') {
      $like = '%' . $search . '%';
      $studentsQuery->where(function ($query) use ($like, $hasNewProgramColumn, $hasAcademicPathwayColumn, $hasDegreeTrackColumn, $hasSelectedComboColumn, $hasSubjectsTable, $hasSubjectCodeColumn, $hasSubjectTitleColumn, $hasSubjectNameColumn) {
        $query->where('sm.first_name', 'like', $like)
          ->orWhere('sm.last_name', 'like', $like)
          ->orWhere('sm.roll_no', 'like', $like)
          ->orWhere('sm.register_no', 'like', $like)
          ->orWhere('sm.mail_id', 'like', $like)
          ->orWhere('u.email', 'like', $like);

        if ($hasNewProgramColumn) {
          $query->orWhere('sp.name', 'like', $like);
        }
        if ($hasAcademicPathwayColumn && Schema::hasTable('academic_pathway_masters')) {
          $query->orWhere('apm.name', 'like', $like);
        }
        if ($hasDegreeTrackColumn && Schema::hasTable('degree_track_masters')) {
          $query->orWhere('dtm.name', 'like', $like);
        }
        if ($hasSelectedComboColumn && $hasSubjectsTable) {
          if ($hasSubjectCodeColumn) {
            $query->orWhere('subj.code', 'like', $like);
          }
          if ($hasSubjectTitleColumn) {
            $query->orWhere('subj.title', 'like', $like);
          }
          if ($hasSubjectNameColumn) {
            $query->orWhere('subj.name', 'like', $like);
          }
        }
      });
    }

    $analyticsRows = (clone $studentsQuery)->get();
    $totalOptedStudents = $analyticsRows->count();

    $optedProgramAnalytics = $analyticsRows
      ->groupBy(function ($student) {
        return implode('|', [
          (int) ($student->new_program_id ?? 0),
          (int) ($student->academic_pathway_id ?? 0),
          (int) ($student->degree_track_id ?? 0),
          (int) ($student->selected_combo_id ?? 0),
        ]);
      })
      ->map(function ($students, $compositeKey) {
        $first = $students->first();
        [$programId, $pathwayId, $degreeTrackId, $comboId] = array_map('intval', explode('|', (string) $compositeKey));

        $programCodeParts = [];
        $programCodeParts[] = 'PRG-' . ($programId > 0 ? $programId : 'NA');
        if ($pathwayId > 0) {
          $programCodeParts[] = 'AP-' . $pathwayId;
        }
        if ($degreeTrackId > 0) {
          $programCodeParts[] = 'DT-' . $degreeTrackId;
        }
        if ($comboId > 0) {
          $programCodeParts[] = !empty($first->selected_combo_code)
            ? (string) $first->selected_combo_code
            : ('COMBO-' . $comboId);
        }

        $programNameParts = [];
        $programNameParts[] = !empty($first->enrolled_program_name_base)
          ? (string) $first->enrolled_program_name_base
          : ($programId > 0 ? ('Program #' . $programId) : 'Program N/A');
        if ($pathwayId > 0) {
          $programNameParts[] = 'Pathway: ' . (!empty($first->academic_pathway_name) ? (string) $first->academic_pathway_name : ('#' . $pathwayId));
        }
        if ($degreeTrackId > 0) {
          $programNameParts[] = 'Track: ' . (!empty($first->degree_track_name) ? (string) $first->degree_track_name : ('#' . $degreeTrackId));
        }
        if ($comboId > 0) {
          $comboName = (string) ($first->selected_combo_title ?? $first->selected_combo_name ?? ('#' . $comboId));
          $programNameParts[] = 'Combo: ' . $comboName;
        }

        return [
          'program_code' => implode(' / ', $programCodeParts),
          'program_name' => implode(' | ', $programNameParts),
          'opted_count' => $students->count(),
        ];
      })
      ->sortByDesc('opted_count')
      ->values();

    $optedProgramsCount = $optedProgramAnalytics->count();

    $students = $studentsQuery
      ->orderBy('sm.roll_no')
      ->orderBy('sm.first_name')
      ->paginate(25)
      ->appends($request->query());

    return view('tpo.training-placement.student-opt-in-forms', [
      'students' => $students,
      'search' => $search,
      'hasOptInTable' => $hasOptInTable,
      'hasApprovalColumns' => $hasApprovalColumns,
      'masterTemplate' => $masterTemplate,
      'totalOptedStudents' => $totalOptedStudents,
      'optedProgramsCount' => $optedProgramsCount,
      'optedProgramAnalytics' => $optedProgramAnalytics,
    ]);
  }

  public function studentOptInTemplateStore(Request $request)
  {
    if (!Schema::hasTable('training_placement_form_templates')) {
      return back()->with('error', 'Template storage is not available yet. Please run the latest placement migration.');
    }

    $validated = $request->validate([
      'template_title' => 'nullable|string|max:255',
      'template_file' => 'required|file|mimes:pdf,doc,docx|max:10240',
    ]);

    $templatePath =  StaticController::s3_file_uploader($request->file('template_file'), 'training_placement_templates');
    if ($templatePath === '') {
      return back()->with('error', 'Unable to upload template file. Please try again.');
    }

    DB::transaction(function () use ($validated, $templatePath) {
      TrainingPlacementFormTemplate::query()
        ->where('is_active', 1)
        ->update(['is_active' => 0]);

      TrainingPlacementFormTemplate::create([
        'title' => trim((string) ($validated['template_title'] ?? '')) ?: 'Training and Placement Opt-In Form',
        'file_path' => $templatePath,
        'uploaded_by' => (int) Auth::id(),
        'is_active' => 1,
      ]);
    });

    return back()->with('success', 'Student downloadable Training & Placement form template uploaded successfully.');
  }

  public function studentOptInApprove(int $studentId)
  {
    if (!Schema::hasTable('training_placement_opt_ins')) {
      return back()->with('error', 'Training & Placement opt-in storage is not available yet.');
    }

    $optIn = TrainingPlacementOptIn::query()
      ->where('student_id', $studentId)
      ->whereNotNull('form_file_path')
      ->first();

    if (!$optIn) {
      return back()->with('error', 'No submitted opt-in form found for the selected student.');
    }

    if (!Schema::hasColumn('training_placement_opt_ins', 'approval_status')) {
      return back()->with('error', 'Approval fields are missing. Please run latest placement migration.');
    }

    $optIn->approval_status = 'approved';
    if (Schema::hasColumn('training_placement_opt_ins', 'approved_by')) {
      $optIn->approved_by = (int) Auth::id();
    }
    if (Schema::hasColumn('training_placement_opt_ins', 'approved_at')) {
      $optIn->approved_at = now();
    }
    if (Schema::hasColumn('training_placement_opt_ins', 'rejection_reason')) {
      $optIn->rejection_reason = null;
    }
    if (Schema::hasColumn('training_placement_opt_ins', 'rejected_by')) {
      $optIn->rejected_by = null;
    }
    if (Schema::hasColumn('training_placement_opt_ins', 'rejected_at')) {
      $optIn->rejected_at = null;
    }
    $optIn->save();

    $student = StudentMaster::query()->find($studentId);
    $displayName = $student
      ? trim((string) (($student->first_name ?? '') . ' ' . ($student->last_name ?? '')))
      : '';
    if ($displayName === '') {
      $displayName = $student ? (string) ($student->roll_no ?: ('Student #' . $student->id)) : ('Student #' . $studentId);
    }

    return back()->with('success', 'Training & Placement opt-in approved for ' . $displayName . '.');
  }

  public function studentOptInReject(Request $request, int $studentId)
  {
    if (!Schema::hasTable('training_placement_opt_ins')) {
      return back()->with('error', 'Training & Placement opt-in storage is not available yet.');
    }

    $request->validate([
      'rejection_reason' => 'required|string|max:1000',
    ]);

    $optIn = TrainingPlacementOptIn::query()
      ->where('student_id', $studentId)
      ->whereNotNull('form_file_path')
      ->first();

    if (!$optIn) {
      return back()->with('error', 'No submitted opt-in form found for the selected student.');
    }

    if (!Schema::hasColumn('training_placement_opt_ins', 'approval_status')) {
      return back()->with('error', 'Approval fields are missing. Please run latest placement migration.');
    }

    $optIn->approval_status = 'rejected';
    if (Schema::hasColumn('training_placement_opt_ins', 'approved_by')) {
      $optIn->approved_by = null;
    }
    if (Schema::hasColumn('training_placement_opt_ins', 'approved_at')) {
      $optIn->approved_at = null;
    }
    if (Schema::hasColumn('training_placement_opt_ins', 'rejection_reason')) {
      $optIn->rejection_reason = trim((string) $request->input('rejection_reason'));
    }
    if (Schema::hasColumn('training_placement_opt_ins', 'rejected_by')) {
      $optIn->rejected_by = (int) Auth::id();
    }
    if (Schema::hasColumn('training_placement_opt_ins', 'rejected_at')) {
      $optIn->rejected_at = now();
    }
    $optIn->save();

    $student = StudentMaster::query()->find($studentId);
    $displayName = $student
      ? trim((string) (($student->first_name ?? '') . ' ' . ($student->last_name ?? '')))
      : '';
    if ($displayName === '') {
      $displayName = $student ? (string) ($student->roll_no ?: ('Student #' . $student->id)) : ('Student #' . $studentId);
    }

    return back()->with('success', 'Training & Placement opt-in rejected for ' . $displayName . '.');
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
      'guest_lecture' => 'Guest Lecture',
      'workshop' => 'Workshop',
    ];
  }

  private function documentationRequirementOptions(): array
  {
    return [
      'aadhaar_card' => 'Aadhaar Card',
      'pan_card' => 'PAN Card',
      'marksheet' => 'Marksheet',
      'portfolio' => 'Portfolio',
      'cover_letter' => 'Cover Letter',
      'passport_photo' => 'Passport Photo',
      'identity_card' => 'College ID Card',
      'noc' => 'NOC',
    ];
  }

  private function normalizeDocumentationRequirements(array $selected, string $customText = ''): array
  {
    $knownOptions = $this->documentationRequirementOptions();
    $knownKeys = array_keys($knownOptions);

    $selectedKeys = collect($selected)
      ->map(fn($value) => trim((string) $value))
      ->filter(fn($value) => $value !== '')
      ->map(fn($value) => in_array($value, $knownKeys, true) ? $value : $this->normalizeDocKey($value))
      ->filter(fn($value) => $value !== '')
      ->values();

    $customLines = preg_split('/\r\n|\r|\n|,/', trim($customText));
    if (!is_array($customLines)) {
      $customLines = [];
    }

    $customKeys = collect($customLines)
      ->map(fn($value) => $this->normalizeDocKey((string) $value))
      ->filter(fn($value) => $value !== '')
      ->values();

    return $selectedKeys
      ->merge($customKeys)
      ->unique()
      ->values()
      ->all();
  }

  private function normalizeDocKey(string $value): string
  {
    $normalized = strtolower(trim($value));
    if ($normalized === '') {
      return '';
    }

    $normalized = preg_replace('/[^a-z0-9]+/', '_', $normalized);
    $normalized = trim((string) $normalized, '_');

    return substr($normalized, 0, 120);
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
      1 => '1st Year',
      2 => '2nd Year',
      3 => '3rd Year',
      4 => '4th Year',
      5 => '5th Year',
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
