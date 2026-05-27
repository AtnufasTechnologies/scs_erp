<?php

namespace App\Http\Controllers;

use App\Models\HrFdpProgram;
use App\Models\HrFdpParticipant;
use App\Models\Faculty;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class HrFdpController extends Controller
{
  /**
   * Display a listing of FDP programs
   */
  public function index(Request $request)
  {
    $search = $request->get('search');
    $status = $request->get('status');
    $type = $request->get('type');

    $query = HrFdpProgram::with(['creator']);

    if ($search) {
      $query->where(function ($q) use ($search) {
        $q->where('program_title', 'like', "%$search%")
          ->orWhere('program_code', 'like', "%$search%")
          ->orWhere('organizer', 'like', "%$search%");
      });
    }

    if ($status) {
      $query->where('status', $status);
    }

    if ($type) {
      $query->where('program_type', $type);
    }

    $fdpPrograms = $query->orderBy('start_date', 'desc')->paginate(20);

    return view('hr.fdp.index', compact('fdpPrograms', 'search', 'status', 'type'));
  }

  /**
   * Show the form for creating a new FDP program
   */
  public function create()
  {
    return view('hr.fdp.create');
  }

  /**
   * Store a newly created FDP program
   */
  public function store(Request $request)
  {
    $validated = $request->validate([
      'program_code' => 'required|string|max:50|unique:hr_fdp_programs,program_code',
      'program_title' => 'required|string|max:255',
      'description' => 'nullable|string',
      'program_type' => 'required|in:workshop,seminar,conference,training,certification,other',
      'organizer' => 'nullable|string|max:255',
      'venue' => 'nullable|string|max:255',
      'start_date' => 'required|date',
      'end_date' => 'required|date|after_or_equal:start_date',
      'program_fee' => 'nullable|numeric|min:0',
      'max_participants' => 'nullable|integer|min:1',
      'target_audience' => 'required|in:faculty,staff,both',
      'coordinator_name' => 'nullable|string|max:100',
      'coordinator_contact' => 'nullable|string|max:50',
      'attachment' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
      'remarks' => 'nullable|string',
    ]);

    // Calculate duration
    $startDate = Carbon::parse($request->start_date);
    $endDate = Carbon::parse($request->end_date);
    $validated['duration_days'] = $startDate->diffInDays($endDate) + 1;

    // Handle attachment upload
    if ($request->hasFile('attachment')) {
      $file = $request->file('attachment');
      $validated['attachment'] = StaticController::s3_file_uploader($file, 'fdp_attachments');
    }

    $validated['created_by'] = Auth::id();
    $validated['status'] = 'draft';

    $fdpProgram = HrFdpProgram::create($validated);

    return redirect()->route('hr.fdp.show', $fdpProgram->id)
      ->with('success', 'FDP program created successfully!');
  }

  /**
   * Display the specified FDP program
   */
  public function show($id)
  {
    $fdpProgram = HrFdpProgram::with([
      'creator',
      'participants.faculty',
      'participants.approver'
    ])->findOrFail($id);

    // Calculate statistics
    $stats = [
      'total_registered' => $fdpProgram->participants()->count(),
      'approved' => $fdpProgram->approvedParticipants()->count(),
      'completed' => $fdpProgram->completedParticipants()->count(),
      'pending' => $fdpProgram->participants()->where('status', 'registered')->count(),
      'certificates_issued' => $fdpProgram->participants()->where('certificate_issued', true)->count(),
    ];

    return view('hr.fdp.show', compact('fdpProgram', 'stats'));
  }

  /**
   * Show the form for editing the specified FDP program
   */
  public function edit($id)
  {
    $fdpProgram = HrFdpProgram::findOrFail($id);
    return view('hr.fdp.edit', compact('fdpProgram'));
  }

  /**
   * Update the specified FDP program
   */
  public function update(Request $request, $id)
  {
    $fdpProgram = HrFdpProgram::findOrFail($id);

    $validated = $request->validate([
      'program_code' => 'required|string|max:50|unique:hr_fdp_programs,program_code,' . $id,
      'program_title' => 'required|string|max:255',
      'description' => 'nullable|string',
      'program_type' => 'required|in:workshop,seminar,conference,training,certification,other',
      'organizer' => 'nullable|string|max:255',
      'venue' => 'nullable|string|max:255',
      'start_date' => 'required|date',
      'end_date' => 'required|date|after_or_equal:start_date',
      'program_fee' => 'nullable|numeric|min:0',
      'max_participants' => 'nullable|integer|min:1',
      'target_audience' => 'required|in:faculty,staff,both',
      'status' => 'required|in:draft,open,ongoing,completed,cancelled',
      'coordinator_name' => 'nullable|string|max:100',
      'coordinator_contact' => 'nullable|string|max:50',
      'attachment' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
      'remarks' => 'nullable|string',
    ]);

    // Calculate duration
    $startDate = Carbon::parse($request->start_date);
    $endDate = Carbon::parse($request->end_date);
    $validated['duration_days'] = $startDate->diffInDays($endDate) + 1;

    // Handle attachment upload
    if ($request->hasFile('attachment')) {
      $file = $request->file('attachment');
      $validated['attachment'] = StaticController::s3_file_uploader($file, 'fdp_attachments');
    }

    $fdpProgram->update($validated);

    return redirect()->route('hr.fdp.show', $fdpProgram->id)
      ->with('success', 'FDP program updated successfully!');
  }

  /**
   * Remove the specified FDP program
   */
  public function destroy($id)
  {
    $fdpProgram = HrFdpProgram::findOrFail($id);
    $fdpProgram->delete();

    return redirect()->route('hr.fdp.index')
      ->with('success', 'FDP program deleted successfully!');
  }

  /**
   * Show form to add participants to FDP program
   */
  public function addParticipantForm($id)
  {
    $fdpProgram = HrFdpProgram::findOrFail($id);
    $faculties = Faculty::where('IS_LEFT', 0)
      ->orderBy('FIRST_NAME')
      ->get();

    // Get already registered faculty IDs
    $registeredFacultyIds = $fdpProgram->participants()->pluck('faculty_id')->toArray();

    return view('hr.fdp.add-participant', compact('fdpProgram', 'faculties', 'registeredFacultyIds'));
  }

  /**
   * Add participant to FDP program
   */
  public function addParticipant(Request $request, $id)
  {
    $fdpProgram = HrFdpProgram::findOrFail($id);

    $request->validate([
      'faculty_ids'      => 'required|array|min:1',
      'faculty_ids.*'    => 'exists:faculties,id',
      'participant_type' => 'required|in:faculty,staff',
      'status'           => 'required|in:registered,approved',
      'remarks'          => 'nullable|string|max:500',
    ]);

    $addedCount   = 0;
    $skippedCount = 0;

    foreach ($request->faculty_ids as $facultyId) {
      // Skip if already registered
      if (HrFdpParticipant::where('fdp_program_id', $id)->where('faculty_id', $facultyId)->exists()) {
        $skippedCount++;
        continue;
      }

      // Stop if program is full
      if ($fdpProgram->isFull()) {
        return redirect()->back()
          ->with('error', 'Program reached maximum participants. Added ' . $addedCount . ', skipped ' . $skippedCount . '.');
      }

      $data = [
        'fdp_program_id'   => $id,
        'faculty_id'       => $facultyId,
        'participant_type' => $request->participant_type,
        'status'           => $request->status,
        'remarks'          => $request->remarks,
        'registration_date' => now(),
      ];

      if ($request->status === 'approved') {
        $data['approved_by'] = Auth::id();
        $data['approved_at'] = now();
      }

      HrFdpParticipant::create($data);
      $addedCount++;
    }

    $message = $addedCount . ' participant(s) added successfully.';
    if ($skippedCount > 0) {
      $message .= ' ' . $skippedCount . ' skipped (already registered).';
    }

    return redirect()->route('hr.fdp.show', $id)->with('success', $message);
  }

  /**
   * Approve participant
   */
  public function approveParticipant($id, $participantId)
  {
    $participant = HrFdpParticipant::where('fdp_program_id', $id)
      ->findOrFail($participantId);

    $participant->update([
      'status' => 'approved',
      'approved_by' => Auth::id(),
      'approved_at' => now(),
    ]);

    return redirect()->back()
      ->with('success', 'Participant approved successfully!');
  }

  /**
   * Mark participant as completed
   */
  public function completeParticipant(Request $request, $id, $participantId)
  {
    $validated = $request->validate([
      'attendance_status' => 'required|in:present,absent,partial',
      'days_attended' => 'required|integer|min:0',
      'certificate_number' => 'nullable|string|max:50',
      'feedback' => 'nullable|string',
      'rating' => 'nullable|integer|min:1|max:5',
    ]);

    $participant = HrFdpParticipant::where('fdp_program_id', $id)
      ->findOrFail($participantId);

    $validated['status'] = 'completed';
    $validated['certificate_issued'] = !empty($request->certificate_number);
    $validated['certificate_date'] = $validated['certificate_issued'] ? now() : null;

    $participant->update($validated);

    return redirect()->back()
      ->with('success', 'Participant marked as completed!');
  }

  /**
   * Track FDP status faculty-wise
   */
  public function facultyTracker(Request $request)
  {
    $search = $request->get('search');
    $facultyId = $request->get('faculty_id');

    if ($facultyId) {
      // Show detailed view for specific faculty
      $faculty = Faculty::with([
        'fdpParticipations.fdpProgram',
        'fdpParticipations.approver'
      ])->findOrFail($facultyId);

      $participations = $faculty->fdpParticipations;

      $stats = [
        'total_programs' => $participations->count(),
        'completed' => $faculty->completedFdpPrograms()->count(),
        'ongoing' => $participations->filter(function ($p) {
          return optional($p->fdpProgram)->status === 'ongoing';
        })->count(),
        'certificates_earned' => $participations->where('certificate_issued', true)->count(),
      ];

      return view('hr.fdp.faculty-detail', compact('faculty', 'participations', 'stats'));
    } else {
      // Show list of all faculties with their FDP stats
      $query = Faculty::where('IS_LEFT', 0);

      if ($search) {
        $query->where(function ($q) use ($search) {
          $q->where('FIRST_NAME', 'like', "%$search%")
            ->orWhere('LAST_NAME', 'like', "%$search%")
            ->orWhere('USER_CODE', 'like', "%$search%");
        });
      }

      $faculties = $query->with('fdpParticipations')->get()->map(function ($faculty) {
        return [
          'faculty' => $faculty,
          'total_programs' => $faculty->fdpParticipations()->count(),
          'completed' => $faculty->completedFdpPrograms()->count(),
          'certificates' => $faculty->fdpParticipations()->where('certificate_issued', true)->count(),
        ];
      })->sortByDesc('total_programs');

      return view('hr.fdp.faculty-tracker', compact('faculties', 'search'));
    }
  }
}
