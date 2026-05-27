<?php

namespace App\Http\Controllers;

use App\Models\HrVacancy;
use App\Models\HrVacancyApplication;
use App\Models\Subject;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class HrVacancyController extends Controller
{
  /**
   * Display a listing of vacancies
   */
  public function index(Request $request)
  {
    $search = $request->get('search');
    $status = $request->get('status');
    $recruitmentType = $request->get('recruitment_type');

    $query = HrVacancy::with(['department', 'creator']);

    if ($search) {
      $query->where(function ($q) use ($search) {
        $q->where('vacancy_code', 'like', "%$search%")
          ->orWhere('position_title', 'like', "%$search%");
      });
    }

    if ($status) {
      $query->where('status', $status);
    }

    if ($recruitmentType) {
      $query->where('recruitment_type', $recruitmentType);
    }

    $vacancies = $query->orderBy('created_at', 'desc')->paginate(20);

    return view('hr.vacancy.index', compact('vacancies', 'search', 'status', 'recruitmentType'));
  }

  /**
   * Show the form for creating a new vacancy
   */
  public function create()
  {
    $departments = Subject::orderBy('subject_name')->get();
    return view('hr.vacancy.create', compact('departments'));
  }

  /**
   * Store a newly created vacancy
   */
  public function store(Request $request)
  {
    $validated = $request->validate([
      'vacancy_code' => 'required|string|max:50|unique:hr_vacancies,vacancy_code',
      'position_title' => 'required|string|max:255',
      'department_id' => 'nullable|exists:subjects,id',
      'employment_type' => 'required|in:full-time,part-time,contract,temporary,visiting',
      'recruitment_type' => 'required|in:regular,adhoc,contractual,guest,visiting',
      'number_of_positions' => 'required|integer|min:1',
      'job_description' => 'nullable|string',
      'qualifications_required' => 'nullable|string',
      'experience_required' => 'nullable|string',
      'salary_range' => 'nullable|string|max:100',
      'application_start_date' => 'required|date',
      'application_end_date' => 'required|date|after:application_start_date',
      'expected_joining_date' => 'nullable|date',
      'contact_person' => 'nullable|string|max:100',
      'contact_email' => 'nullable|email|max:100',
      'contact_phone' => 'nullable|string|max:15',
      'attachment' => 'nullable|file|mimes:pdf|max:5120',
      'remarks' => 'nullable|string',
    ]);

    // Handle attachment upload
    if ($request->hasFile('attachment')) {
      $file = $request->file('attachment');
      $validated['attachment'] = StaticController::s3_file_uploader($file, 'vacancy_attachments');
    }

    $validated['created_by'] = Auth::id();
    $validated['status'] = 'draft';

    $vacancy = HrVacancy::create($validated);

    return redirect()->route('hr.vacancy.show', $vacancy->id)
      ->with('success', 'Vacancy created successfully!');
  }

  /**
   * Display the specified vacancy
   */
  public function show($id)
  {
    $vacancy = HrVacancy::with([
      'department',
      'creator',
      'applications'
    ])->findOrFail($id);

    // Calculate statistics
    $stats = [
      'total_applications' => $vacancy->applications()->count(),
      'submitted' => $vacancy->applications()->submitted()->count(),
      'under_review' => $vacancy->applications()->underReview()->count(),
      'shortlisted' => $vacancy->applications()->shortlisted()->count(),
      'selected' => $vacancy->applications()->selected()->count(),
      'rejected' => $vacancy->applications()->rejected()->count(),
    ];

    return view('hr.vacancy.show', compact('vacancy', 'stats'));
  }

  /**
   * Show the form for editing the specified vacancy
   */
  public function edit($id)
  {
    $vacancy = HrVacancy::findOrFail($id);
    $departments = Subject::orderBy('subject_name')->get();
    return view('hr.vacancy.edit', compact('vacancy', 'departments'));
  }

  /**
   * Update the specified vacancy
   */
  public function update(Request $request, $id)
  {
    $vacancy = HrVacancy::findOrFail($id);

    $validated = $request->validate([
      'vacancy_code' => 'required|string|max:50|unique:hr_vacancies,vacancy_code,' . $id,
      'position_title' => 'required|string|max:255',
      'department_id' => 'nullable|exists:subjects,id',
      'employment_type' => 'required|in:full-time,part-time,contract,temporary,visiting',
      'recruitment_type' => 'required|in:regular,adhoc,contractual,guest,visiting',
      'number_of_positions' => 'required|integer|min:1',
      'job_description' => 'nullable|string',
      'qualifications_required' => 'nullable|string',
      'experience_required' => 'nullable|string',
      'salary_range' => 'nullable|string|max:100',
      'application_start_date' => 'required|date',
      'application_end_date' => 'required|date|after:application_start_date',
      'expected_joining_date' => 'nullable|date',
      'status' => 'required|in:draft,published,closed,cancelled,filled',
      'contact_person' => 'nullable|string|max:100',
      'contact_email' => 'nullable|email|max:100',
      'contact_phone' => 'nullable|string|max:15',
      'attachment' => 'nullable|file|mimes:pdf|max:5120',
      'remarks' => 'nullable|string',
    ]);

    // Handle attachment upload
    if ($request->hasFile('attachment')) {
      $file = $request->file('attachment');
      $validated['attachment'] = StaticController::s3_file_uploader($file, 'vacancy_attachments');
    }

    $vacancy->update($validated);

    return redirect()->route('hr.vacancy.show', $vacancy->id)
      ->with('success', 'Vacancy updated successfully!');
  }

  /**
   * Remove the specified vacancy
   */
  public function destroy($id)
  {
    $vacancy = HrVacancy::findOrFail($id);
    $vacancy->delete();

    return redirect()->route('hr.vacancy.index')
      ->with('success', 'Vacancy deleted successfully!');
  }

  /**
   * Publish vacancy to website
   */
  public function publish($id)
  {
    $vacancy = HrVacancy::findOrFail($id);

    $vacancy->update([
      'status' => 'published',
      'publish_to_website' => true,
      'published_date' => now(),
    ]);

    return redirect()->route('hr.vacancy.show', $id)
      ->with('success', 'Vacancy published successfully!');
  }

  /**
   * Close vacancy
   */
  public function close($id)
  {
    $vacancy = HrVacancy::findOrFail($id);

    $vacancy->update([
      'status' => 'closed',
    ]);

    return redirect()->route('hr.vacancy.show', $id)
      ->with('success', 'Vacancy closed successfully!');
  }

  /**
   * Display all applications for a vacancy
   */
  public function applications($id)
  {
    $vacancy = HrVacancy::with('department')->findOrFail($id);
    $applications = HrVacancyApplication::where('vacancy_id', $id)
      ->with('reviewer')
      ->orderBy('application_date', 'desc')
      ->paginate(20);

    return view('hr.vacancy.applications', compact('vacancy', 'applications'));
  }

  /**
   * Display a specific application
   */
  public function showApplication($vacancyId, $applicationId)
  {
    $application = HrVacancyApplication::where('vacancy_id', $vacancyId)
      ->with(['vacancy', 'reviewer'])
      ->findOrFail($applicationId);

    return view('hr.vacancy.application-show', compact('application'));
  }

  /**
   * Update application status
   */
  public function updateApplicationStatus(Request $request, $vacancyId, $applicationId)
  {
    $validated = $request->validate([
      'status' => 'required|in:submitted,under_review,shortlisted,interview_scheduled,selected,rejected,withdrawn',
      'hr_remarks' => 'nullable|string',
      'rejection_reason' => 'nullable|required_if:status,rejected|string',
      'interview_date' => 'nullable|required_if:status,interview_scheduled|date',
      'interview_time' => 'nullable|required_if:status,interview_scheduled',
      'interview_venue' => 'nullable|string|max:255',
      'interview_score' => 'nullable|integer|min:0|max:100',
    ]);

    $application = HrVacancyApplication::where('vacancy_id', $vacancyId)
      ->findOrFail($applicationId);

    $validated['reviewed_by'] = Auth::id();
    $validated['reviewed_at'] = now();

    $application->update($validated);

    return redirect()->route('hr.vacancy.application.show', [$vacancyId, $applicationId])
      ->with('success', 'Application status updated successfully!');
  }

  /**
   * Public view of vacancies (for website)
   */
  public function publicIndex()
  {
    $vacancies = HrVacancy::published()
      ->open()
      ->with('department')
      ->orderBy('application_end_date', 'asc')
      ->get();

    return view('public.vacancies', compact('vacancies'));
  }

  /**
   * Public view of specific vacancy
   */
  public function publicShow($id)
  {
    $vacancy = HrVacancy::published()
      ->with('department')
      ->findOrFail($id);

    if (!$vacancy->isOpen()) {
      abort(404, 'This vacancy is no longer accepting applications.');
    }

    return view('public.vacancy-detail', compact('vacancy'));
  }

  /**
   * Show public application form
   */
  public function publicApplyForm($id)
  {
    $vacancy = HrVacancy::published()->findOrFail($id);

    if (!$vacancy->isOpen()) {
      return redirect()->route('vacancies.public.show', $id)
        ->with('error', 'This vacancy is no longer accepting applications.');
    }

    return view('public.vacancy-apply', compact('vacancy'));
  }

  /**
   * Submit public application
   */
  public function publicApply(Request $request, $id)
  {
    $vacancy = HrVacancy::published()->findOrFail($id);

    if (!$vacancy->isOpen()) {
      return redirect()->back()
        ->with('error', 'This vacancy is no longer accepting applications.');
    }

    $validated = $request->validate([
      'applicant_name' => 'required|string|max:255',
      'email' => 'required|email|max:100',
      'phone' => 'required|string|max:15',
      'date_of_birth' => 'nullable|date',
      'gender' => 'nullable|in:male,female,other',
      'address' => 'nullable|string',
      'highest_qualification' => 'nullable|string|max:255',
      'specialization' => 'nullable|string|max:255',
      'total_experience_years' => 'nullable|integer|min:0',
      'teaching_experience_years' => 'nullable|integer|min:0',
      'current_employment' => 'nullable|string',
      'resume_attachment' => 'required|file|mimes:pdf|max:5120',
      'photo_attachment' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
      'cover_letter' => 'nullable|string',
    ]);

    // Generate application number
    $validated['application_number'] = 'APP-' . $vacancy->vacancy_code . '-' . str_pad(
      $vacancy->applications()->count() + 1,
      4,
      '0',
      STR_PAD_LEFT
    );

    // Handle file uploads
    if ($request->hasFile('resume_attachment')) {
      $file = $request->file('resume_attachment');
      $validated['resume_attachment'] = StaticController::s3_file_uploader($file, 'vacancy_applications/resumes');
    }

    if ($request->hasFile('photo_attachment')) {
      $file = $request->file('photo_attachment');
      $validated['photo_attachment'] = StaticController::s3_file_uploader($file, 'vacancy_applications/photos');
    }

    $validated['vacancy_id'] = $id;
    $validated['application_date'] = now();
    $validated['status'] = 'submitted';

    HrVacancyApplication::create($validated);

    return redirect()->route('vacancies.public.show', $id)
      ->with('success', 'Your application has been submitted successfully! Application Number: ' . $validated['application_number']);
  }
}
