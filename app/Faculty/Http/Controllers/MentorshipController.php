<?php

namespace App\Faculty\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Faculty;
use App\Models\MentorshipAssignment;
use App\Models\MentorshipAssignmentSubmission;
use App\Models\MentorshipGroup;
use App\Models\MentorshipGroupStudent;
use App\Models\MentorshipSession;
use App\Models\MentorshipSessionAttendance;
use App\Models\MentorshipStudentNote;
use App\Models\StudentMaster;
use App\Models\SubjectFacultyMaster;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class MentorshipController extends Controller
{
  // ──────────────────────────────────────────────────────────
  // Helpers
  // ──────────────────────────────────────────────────────────

  private function getFacultyId(): ?int
  {
    return SubjectFacultyMaster::where('access_id', Auth::id())->value('faculty_id');
  }

  // ──────────────────────────────────────────────────────────
  // Groups
  // ──────────────────────────────────────────────────────────

  public function index()
  {
    $facultyId = $this->getFacultyId();
    if (!$facultyId) {
      return redirect()->route('faculty.dashboard')->with('error', 'Faculty profile not found.');
    }

    $groups = MentorshipGroup::where('faculty_id', $facultyId)
      ->withCount('students')
      ->withCount('sessions')
      ->withCount('assignments')
      ->orderByDesc('created_at')
      ->get();

    return view('faculty.mentorship.index', compact('groups'));
  }

  public function createGroup()
  {
    $batches = \App\Models\BatchMaster::orderByDesc('id')->get();
    $semesters = \App\Models\Semester::orderBy('id')->get();
    return view('faculty.mentorship.group-form', compact('batches', 'semesters'));
  }

  public function storeGroup(Request $request)
  {
    $facultyId = $this->getFacultyId();
    if (!$facultyId) {
      return redirect()->route('faculty.dashboard')->with('error', 'Faculty profile not found.');
    }

    $request->validate([
      'name'          => 'required|string|max:255',
      'description'   => 'nullable|string|max:1000',
      'academic_year' => 'nullable|string|max:20',
      'semester'      => 'nullable|string|max:20',
    ]);

    $group = MentorshipGroup::create([
      'faculty_id'    => $facultyId,
      'name'          => $request->name,
      'description'   => $request->description,
      'academic_year' => $request->academic_year,
      'semester'      => $request->semester,
      'status'        => 'active',
    ]);

    return redirect()->route('faculty.mentorship.group.show', $group->id)
      ->with('success', 'Mentorship group created successfully.');
  }

  public function showGroup($id)
  {
    $facultyId = $this->getFacultyId();
    $group = MentorshipGroup::where('faculty_id', $facultyId)->with([
      'students',
      'sessions'    => fn($q) => $q->orderByDesc('session_date'),
      'assignments' => fn($q) => $q->orderByDesc('created_at'),
    ])->findOrFail($id);

    $existingStudentIds = $group->students
      ->pluck('id')
      ->map(fn($studentId) => (int) $studentId)
      ->filter(fn($studentId) => $studentId > 0)
      ->values();

    $facultyCampusId = (int) (Faculty::query()
      ->where('id', (int) $facultyId)
      ->value('CAMPUS_ID') ?? 0);

    $availableStudentsQuery = StudentMaster::query()
      ->select('id', 'first_name', 'last_name', 'roll_no', 'register_no')
      ->where(function ($query) {
        $query->where('is_left', 0)->orWhereNull('is_left');
      })
      ->orderBy('roll_no')
      ->orderBy('first_name')
      ->orderBy('last_name');

    if ($facultyCampusId > 0) {
      $availableStudentsQuery->where('campus_id', $facultyCampusId);
    }

    if ($existingStudentIds->isNotEmpty()) {
      $availableStudentsQuery->whereNotIn('id', $existingStudentIds->all());
    }

    $availableStudents = $availableStudentsQuery->get();

    $sessions    = $group->sessions;
    $assignments = $group->assignments;

    $totalStudents     = $group->students->count();
    $totalSessions     = $sessions->count();
    $completedSessions = $sessions->where('status', 'completed')->count();
    $totalAssignments  = $assignments->count();

    return view('faculty.mentorship.group-show', compact(
      'group',
      'sessions',
      'assignments',
      'availableStudents',
      'totalStudents',
      'totalSessions',
      'completedSessions',
      'totalAssignments'
    ));
  }

  public function editGroup($id)
  {
    $facultyId = $this->getFacultyId();
    $group     = MentorshipGroup::where('faculty_id', $facultyId)->findOrFail($id);
    $batches   = \App\Models\BatchMaster::orderByDesc('id')->get();
    $semesters = \App\Models\Semester::orderBy('id')->get();
    return view('faculty.mentorship.group-form', compact('group', 'batches', 'semesters'));
  }

  public function updateGroup(Request $request, $id)
  {
    $facultyId = $this->getFacultyId();
    $group     = MentorshipGroup::where('faculty_id', $facultyId)->findOrFail($id);

    $request->validate([
      'name'          => 'required|string|max:255',
      'description'   => 'nullable|string|max:1000',
      'academic_year' => 'nullable|string|max:20',
      'semester'      => 'nullable|string|max:20',
      'status'        => 'required|in:active,inactive,archived',
    ]);

    $group->update($request->only('name', 'description', 'academic_year', 'semester', 'status'));

    return redirect()->route('faculty.mentorship.group.show', $id)
      ->with('success', 'Group updated successfully.');
  }

  public function destroyGroup($id)
  {
    $facultyId = $this->getFacultyId();
    $group     = MentorshipGroup::where('faculty_id', $facultyId)->findOrFail($id);
    $group->delete();

    return redirect()->route('faculty.mentorship.index')
      ->with('success', 'Mentorship group deleted.');
  }

  // ──────────────────────────────────────────────────────────
  // Students in Group
  // ──────────────────────────────────────────────────────────

  public function searchStudents(Request $request)
  {
    $q        = $request->input('q', '');
    $students = StudentMaster::where(function ($query) use ($q) {
      $query->where('first_name', 'like', "%{$q}%")
        ->orWhere('last_name', 'like', "%{$q}%")
        ->orWhere('register_no', 'like', "%{$q}%")
        ->orWhere('roll_no', 'like', "%{$q}%");
    })
      ->select('id', 'first_name', 'last_name', 'register_no', 'roll_no')
      ->limit(20)
      ->get()
      ->map(fn($s) => [
        'id'          => $s->id,
        'name'        => trim($s->first_name . ' ' . $s->last_name),
        'register_no' => $s->register_no,
        'roll_no'     => $s->roll_no,
      ]);

    return response()->json($students);
  }

  /**
   * Add a student to group by roll number (AJAX)
   */
  public function addStudentByRoll(Request $request, $groupId)
  {
    $facultyId = $this->getFacultyId();
    $group     = MentorshipGroup::where('faculty_id', $facultyId)->findOrFail($groupId);

    $request->validate([
      'roll_no' => 'required|string',
    ]);

    $student = StudentMaster::where('roll_no', $request->roll_no)->first();
    if (!$student) {
      return response()->json(['success' => false, 'message' => 'Student with this roll number not found.']);
    }

    $exists = MentorshipGroupStudent::where('mentorship_group_id', $group->id)
      ->where('student_id', $student->id)->exists();
    if ($exists) {
      return response()->json(['success' => false, 'message' => 'Student is already in this group.']);
    }

    MentorshipGroupStudent::create([
      'mentorship_group_id' => $group->id,
      'student_id'          => $student->id,
    ]);

    return response()->json(['success' => true, 'message' => 'Student added successfully.']);
  }

  public function addStudents(Request $request, $groupId)
  {
    $facultyId = $this->getFacultyId();
    $group     = MentorshipGroup::where('faculty_id', $facultyId)->findOrFail($groupId);

    $request->validate([
      'student_ids'   => 'required|array|min:1',
      'student_ids.*' => 'exists:student_masters,id',
    ]);

    $added = 0;
    foreach ($request->student_ids as $studentId) {
      MentorshipGroupStudent::firstOrCreate([
        'mentorship_group_id' => $group->id,
        'student_id'          => $studentId,
      ]);
      $added++;
    }

    return response()->json([
      'success' => true,
      'message' => "{$added} student(s) added to group.",
    ]);
  }

  public function removeStudent($groupId, $studentId)
  {
    $facultyId = $this->getFacultyId();
    $group     = MentorshipGroup::where('faculty_id', $facultyId)->findOrFail($groupId);

    MentorshipGroupStudent::where('mentorship_group_id', $group->id)
      ->where('student_id', $studentId)
      ->delete();

    return redirect()->back()->with('success', 'Deleted');
    // return response()->json(['success' => true, 'message' => 'Student removed from group.']);
  }

  // ──────────────────────────────────────────────────────────
  // Sessions
  // ──────────────────────────────────────────────────────────

  public function createSession($groupId)
  {
    $facultyId = $this->getFacultyId();
    $group     = MentorshipGroup::where('faculty_id', $facultyId)->findOrFail($groupId);
    return view('faculty.mentorship.session-form', compact('group'));
  }

  public function storeSession(Request $request, $groupId)
  {
    $facultyId = $this->getFacultyId();
    $group     = MentorshipGroup::where('faculty_id', $facultyId)->findOrFail($groupId);

    $request->validate([
      'title'        => 'required|string|max:255',
      'agenda'       => 'nullable|string|max:2000',
      'session_date' => 'required|date',
      'start_time'   => 'nullable|date_format:H:i|required_with:end_time',
      'end_time'     => [
        'nullable',
        'date_format:H:i',
        'required_with:start_time',
        function ($attribute, $value, $fail) use ($request) {
          $startTime = $request->input('start_time');
          if (!$startTime || !$value) {
            return;
          }

          if (strtotime($value) <= strtotime($startTime)) {
            $fail('The end time must be later than the start time.');
          }
        },
      ],
      'mode'         => 'required|in:in-person,online,hybrid',
    ]);

    $session = MentorshipSession::create([
      'mentorship_group_id' => $group->id,
      'title'               => $request->title,
      'agenda'              => $request->agenda,
      'session_date'        => $request->session_date,
      'start_time'          => $request->start_time,
      'end_time'            => $request->end_time,
      'mode'                => $request->mode,
      'status'              => 'scheduled',
    ]);

    return redirect()->route('faculty.mentorship.session.show', $session->id)
      ->with('success', 'Session created. Mark attendance below.');
  }

  public function showSession($id)
  {
    $facultyId = $this->getFacultyId();
    $session   = MentorshipSession::with(['group', 'attendances.student'])
      ->whereHas('group', fn($q) => $q->where('faculty_id', $facultyId))
      ->findOrFail($id);

    // Merge all group students with any saved attendance records.
    // Students not yet saved will have status = null (no radio pre-checked).
    $savedMap    = $session->attendances->keyBy('student_id');
    $groupStudents = $session->group->students;

    $attendances = $groupStudents->map(function ($student) use ($savedMap, $session) {
      return $savedMap->get($student->id) ?? new MentorshipSessionAttendance([
        'mentorship_session_id' => $session->id,
        'student_id'            => $student->id,
        'status'                => null,
        'remarks'               => null,
      ]);
    });
    // Attach student relation to unsaved records
    $attendances->each(function ($att) use ($savedMap, $groupStudents) {
      if (!$att->exists) {
        $att->setRelation('student', $groupStudents->firstWhere('id', $att->student_id));
      }
    });

    $present = $attendances->where('status', 'present')->count();
    $absent  = $attendances->where('status', 'absent')->count();
    $excused = $attendances->where('status', 'excused')->count();

    return view('faculty.mentorship.session-show', compact('session', 'attendances', 'present', 'absent', 'excused'));
  }

  public function saveAttendance(Request $request, $sessionId)
  {
    $facultyId = $this->getFacultyId();
    $session   = MentorshipSession::whereHas('group', fn($q) => $q->where('faculty_id', $facultyId))
      ->findOrFail($sessionId);

    $request->validate([
      'attendance'              => 'required|array',
      'attendance.*.student_id' => 'required|integer',
      'attendance.*.status'     => 'required|in:present,absent,excused',
      'attendance.*.remarks'    => 'nullable|string|max:500',
    ]);

    foreach ($request->attendance as $row) {
      MentorshipSessionAttendance::updateOrCreate(
        ['mentorship_session_id' => $session->id, 'student_id' => $row['student_id']],
        ['status' => $row['status'], 'remarks' => $row['remarks'] ?? null]
      );
    }

    $session->update([
      'status'  => 'completed',
      'minutes' => $request->input('minutes'),
    ]);
    return redirect()->route('faculty.mentorship.session.show', $session->id)
      ->with('success', 'Attendance saved and session marked completed.');
  }

  public function destroySession($id)
  {
    $facultyId = $this->getFacultyId();
    $session   = MentorshipSession::whereHas('group', fn($q) => $q->where('faculty_id', $facultyId))
      ->findOrFail($id);
    $groupId = $session->mentorship_group_id;
    $session->delete();

    return redirect()->route('faculty.mentorship.group.show', $groupId)
      ->with('success', 'Session deleted.');
  }

  // ──────────────────────────────────────────────────────────
  // Assignments
  // ──────────────────────────────────────────────────────────

  public function createAssignment($groupId)
  {
    $facultyId = $this->getFacultyId();
    $group     = MentorshipGroup::where('faculty_id', $facultyId)->findOrFail($groupId);
    return view('faculty.mentorship.assignment-form', compact('group'));
  }

  public function storeAssignment(Request $request, $groupId)
  {
    $facultyId = $this->getFacultyId();
    $group     = MentorshipGroup::where('faculty_id', $facultyId)->findOrFail($groupId);

    $request->validate([
      'title'       => 'required|string|max:255',
      'description' => 'required|string|max:5000',
      'due_date'    => 'nullable|date|after:today',
      'max_marks'   => 'required|integer|min:1|max:1000',
      'attachment'  => 'nullable|file|mimes:pdf,doc,docx,jpg,jpeg,png|max:5120',
    ]);

    $attachmentPath = null;
    if ($request->hasFile('attachment')) {
      $attachmentPath = $request->file('attachment')->store('mentorship/assignments', 'public');
    }

    $assignment = MentorshipAssignment::create([
      'mentorship_group_id' => $group->id,
      'title'               => $request->title,
      'description'         => $request->description,
      'due_date'            => $request->due_date,
      'max_marks'           => $request->max_marks,
      'status'              => 'active',
      'attachment_path'     => $attachmentPath,
    ]);

    $studentIds = $group->students()->pluck('student_masters.id');
    foreach ($studentIds as $sid) {
      MentorshipAssignmentSubmission::create([
        'mentorship_assignment_id' => $assignment->id,
        'student_id'               => $sid,
        'status'                   => 'pending',
      ]);
    }

    return redirect()->route('faculty.mentorship.assignment.show', $assignment->id)
      ->with('success', 'Assignment created successfully.');
  }

  public function showAssignment($id)
  {
    $facultyId  = $this->getFacultyId();
    $assignment = MentorshipAssignment::with([
      'group',
      'submissions.student',
    ])->whereHas('group', fn($q) => $q->where('faculty_id', $facultyId))
      ->findOrFail($id);

    $submitted = $assignment->submissions->where('status', 'submitted')->count();
    $graded    = $assignment->submissions->where('status', 'graded')->count();
    $pending   = $assignment->submissions->where('status', 'pending')->count();

    return view('faculty.mentorship.assignment-show', compact(
      'assignment',
      'submitted',
      'graded',
      'pending'
    ));
  }

  public function gradeSubmission(Request $request, $submissionId)
  {
    $facultyId  = $this->getFacultyId();
    $submission = MentorshipAssignmentSubmission::whereHas(
      'assignment.group',
      fn($q) => $q->where('faculty_id', $facultyId)
    )->findOrFail($submissionId);

    $request->validate([
      'marks_obtained' => 'required|numeric|min:0|max:' . $submission->assignment->max_marks,
      'feedback'       => 'nullable|string|max:2000',
    ]);

    $submission->update([
      'marks_obtained' => $request->marks_obtained,
      'feedback'       => $request->feedback,
      'status'         => 'graded',
    ]);

    return response()->json(['success' => true, 'message' => 'Submission graded.']);
  }

  public function destroyAssignment($id)
  {
    $facultyId  = $this->getFacultyId();
    $assignment = MentorshipAssignment::whereHas('group', fn($q) => $q->where('faculty_id', $facultyId))
      ->findOrFail($id);
    $groupId = $assignment->mentorship_group_id;
    $assignment->delete();

    return redirect()->route('faculty.mentorship.group.show', $groupId)
      ->with('success', 'Assignment deleted.');
  }

  // ──────────────────────────────────────────────────────────
  // Student Notes
  // ──────────────────────────────────────────────────────────

  public function storeNote(Request $request, $groupId)
  {
    $facultyId = $this->getFacultyId();
    $group     = MentorshipGroup::where('faculty_id', $facultyId)->findOrFail($groupId);

    $request->validate([
      'student_id' => 'required|exists:student_masters,id',
      'note'       => 'required|string|max:2000',
      'category'   => 'required|in:academic,behavioral,personal,achievement,concern,general',
      'noted_on'   => 'required|date',
    ]);

    $note = MentorshipStudentNote::create([
      'mentorship_group_id' => $group->id,
      'faculty_id'          => $facultyId,
      'student_id'          => $request->student_id,
      'note'                => $request->note,
      'category'            => $request->category,
      'noted_on'            => $request->noted_on,
    ]);

    return response()->json(['success' => true, 'message' => 'Note saved.', 'note' => $note]);
  }

  public function destroyNote($id)
  {
    $facultyId = $this->getFacultyId();
    MentorshipStudentNote::where('faculty_id', $facultyId)->findOrFail($id)->delete();
    return response()->json(['success' => true, 'message' => 'Note deleted.']);
  }

  // ──────────────────────────────────────────────────────────
  // Student Profile within Group
  // ──────────────────────────────────────────────────────────

  public function studentProfile($groupId, $studentId)
  {
    $facultyId = $this->getFacultyId();
    $group     = MentorshipGroup::where('faculty_id', $facultyId)->findOrFail($groupId);
    $student   = StudentMaster::findOrFail($studentId);

    $attendances = MentorshipSessionAttendance::whereHas(
      'session',
      fn($q) => $q->where('mentorship_group_id', $groupId)->where('status', 'completed')
    )->where('student_id', $studentId)->get();

    $presentCount = $attendances->where('status', 'present')->count();
    $absentCount  = $attendances->where('status', 'absent')->count();

    $submissions = MentorshipAssignmentSubmission::whereHas(
      'assignment',
      fn($q) => $q->where('mentorship_group_id', $groupId)
    )->with('assignment')->where('student_id', $studentId)->get();

    $notes = MentorshipStudentNote::where('mentorship_group_id', $groupId)
      ->where('student_id', $studentId)
      ->orderByDesc('noted_on')
      ->get();

    return view('faculty.mentorship.student-profile', compact(
      'group',
      'student',
      'presentCount',
      'absentCount',
      'submissions',
      'notes'
    ));
  }
}
