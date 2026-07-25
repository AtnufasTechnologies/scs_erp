<?php

namespace App\Faculty\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Controllers\StaticController;
use App\Models\DepartmentActivity;
use App\Models\DepartmentActivityHasParticipant;
use App\Models\EcFacultyDuty;
use App\Models\ExamSystem\InvigilationDuty;
use App\Models\BatchMaster;
use App\Models\Faculty;
use App\Models\FacultyLeaveApplication;
use App\Models\Semester;
use App\Models\ShiftMaster;
use App\Models\Subject;
use App\Models\SubjectFacultyMaster;
use App\Models\SubjectHasRoutine;
use App\Models\SubjectHasSyllabus;
use App\Models\SyllabusManager;
use App\Models\SyllabusSubunit;
use App\Models\UserHasRole;
use App\Models\WorkDiary;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;

class FacultyDashboardController extends Controller
{
  public function index()
  {
    $userId = Auth::user()->id;
    $roleType = UserHasRole::where('user_id', $userId)->value('role_name');
    if ($roleType != 'faculty') {
      Auth::logout();
      return redirect('/')->with('error', 'Unauthorized Access');
    }

    // Get faculty ID
    $facultyId = SubjectFacultyMaster::where('access_id', $userId)->value('faculty_id');

    // Get recent work diary entries (last 10 entries)
    $workDiaryEntries = WorkDiary::where('faculty_id', $facultyId)
      ->orderBy('date', 'desc')
      ->orderBy('created_at', 'desc')
      ->limit(10)
      ->get();

    // Get assigned subjects with progress (limit to 5 for dashboard)
    $allSubjectsRoutines = SubjectHasRoutine::where('faculty_id', $facultyId)
      ->with([
        'syllabus.subject:id,title',
        'syllabus.batchmaster:id,batch_name',
        'syllabus.semestermaster:id,title',
        'syllabus.courseLink.courseMaster:id,course_title,course_code',
      ])
      ->distinct()
      ->get('syllabus_id');

    $assignedSubjects = $allSubjectsRoutines
      ->take(5)
      ->map(function ($routine) {
        $syllabus = $routine->syllabus;
        if (!$syllabus) return null;

        // Get ALL syllabus managers (all CSOs) to fetch all subunits
        $syllabusManagers = SyllabusManager::where('subject_id', $syllabus->subject_id)
          ->where('batch_id', $syllabus->batch_id)
          ->where('semester_id', $syllabus->semester_id)
          ->with('syllabusSubunits')
          ->get();

        $totalUnits = 0;
        $completedUnits = 0;

        // Count units from all CSOs
        foreach ($syllabusManagers as $manager) {
          if ($manager->syllabusSubunits) {
            $totalUnits += $manager->syllabusSubunits->count();
            $completedUnits += $manager->syllabusSubunits->where('is_completed', 1)->count();
          }
        }

        $completionPercentage = $totalUnits > 0 ? round(($completedUnits / $totalUnits) * 100) : 0;

        return [
          'subject_title' => $syllabus->subject->title ?? 'N/A',
          'semester' => $syllabus->semestermaster->title ?? 'N/A',
          'batch' => $syllabus->batchmaster->batch_name ?? 'N/A',
          'total_units' => $totalUnits,
          'completed_units' => $completedUnits,
          'completion_percentage' => $completionPercentage,
          'course_title' => $syllabus->courseLink->courseMaster->course_title ?? 'N/A',
          'course_code' => $syllabus->courseLink->courseMaster->course_code ?? 'N/A',
        ];
      })
      ->filter()
      ->values();

    $totalSubjectsCount = $allSubjectsRoutines->count();

    // Get leave statistics for current session
    $leaveStats = [
      'total' => FacultyLeaveApplication::where('faculty_id', $facultyId)
        ->currentSession()
        ->count(),
      'approved' => FacultyLeaveApplication::where('faculty_id', $facultyId)
        ->currentSession()
        ->approved()
        ->count(),
      'pending' => FacultyLeaveApplication::where('faculty_id', $facultyId)
        ->currentSession()
        ->pending()
        ->count(),
      'rejected' => FacultyLeaveApplication::where('faculty_id', $facultyId)
        ->currentSession()
        ->rejected()
        ->count(),
      'days_taken' => FacultyLeaveApplication::where('faculty_id', $facultyId)
        ->currentSession()
        ->approved()
        ->sum('total_days'),
    ];

    // Get leave breakdown by type for current session
    $casualLeaves = FacultyLeaveApplication::where('faculty_id', $facultyId)
      ->currentSession()
      ->approved()
      ->where(function ($q) {
        $q->where('leave_type', 'casual')
          ->orWhereHas('leaveMaster', function ($sq) {
            $sq->where('leave_type_code', 'CL');
          });
      })
      ->sum('total_days');

    $sickLeaves = FacultyLeaveApplication::where('faculty_id', $facultyId)
      ->currentSession()
      ->approved()
      ->where(function ($q) {
        $q->where('leave_type', 'sick')
          ->orWhereHas('leaveMaster', function ($sq) {
            $sq->where('leave_type_code', 'SL');
          });
      })
      ->sum('total_days');

    $earnedLeaves = FacultyLeaveApplication::where('faculty_id', $facultyId)
      ->currentSession()
      ->approved()
      ->where(function ($q) {
        $q->where('leave_type', 'earned')
          ->orWhereHas('leaveMaster', function ($sq) {
            $sq->where('leave_type_code', 'EL');
          });
      })
      ->sum('total_days');

    // Get recent leave applications (last 3)
    $recentLeaves = FacultyLeaveApplication::where('faculty_id', $facultyId)
      ->currentSession()
      ->with(['leaveMaster'])
      ->orderBy('created_at', 'desc')
      ->limit(3)
      ->get();

    // Get Event Controller Activities assigned to this faculty
    $eventControllerActivities = EcFacultyDuty::where('faculty_id', $facultyId)
      ->with(['event', 'program', 'assignedBy'])
      ->whereHas('event', function ($q) {
        $q->whereIn('status', ['active', 'draft']);
      })
      ->orderBy('created_at', 'desc')
      ->limit(10)
      ->get();

    // Get Invigilation Duties
    $invigilationDuties = InvigilationDuty::where('faculty_id', $facultyId)
      ->with(['exam', 'room'])
      ->where('date', '>=', now()->toDateString())
      ->orderBy('date', 'asc')
      ->limit(10)
      ->get();

    // Get faculty email for matching departmental activities
    $facultyDetails = Faculty::find($facultyId);
    $facultyEmail = $facultyDetails->MAIL_ID ?? null;

    // Get Departmental Activities where faculty is a participant


    $departmentalActivities = DepartmentActivity::with(['subject', 'creator'])
      ->whereIn('status', ['planned', 'ongoing', 'completed'])
      ->orderBy('activity_date', 'desc')
      ->limit(10)
      ->get();



    return view('faculty.dashboard', compact(
      'workDiaryEntries',
      'assignedSubjects',
      'totalSubjectsCount',
      'leaveStats',
      'casualLeaves',
      'sickLeaves',
      'earnedLeaves',
      'recentLeaves',
      'eventControllerActivities',
      'invigilationDuties',
      'departmentalActivities'
    ));
  }

  public function facultyTimetable(Request $request)
  {

    $userId = Auth::user()->id;
    $facultyId = (int) SubjectFacultyMaster::where('access_id', $userId)->value('faculty_id');

    if ($facultyId <= 0) {
      return redirect()->back()->with('error', 'Faculty mapping not found for this account.');
    }

    $faculty = Faculty::findOrFail($facultyId);

    $assignedSubjectIds = SubjectFacultyMaster::where('faculty_id', $facultyId)
      ->pluck('subject_id')
      ->map(fn($id) => (int) $id)
      ->filter(fn($id) => $id > 0)
      ->unique()
      ->values();

    $subjectOptions = Subject::whereIn('id', $assignedSubjectIds)
      ->orderBy('title')
      ->get(['id', 'title']);

    $selectedSubjectId = (int) $request->query('subject_id', 0);
    if ($selectedSubjectId <= 0 && $subjectOptions->isNotEmpty()) {
      $selectedSubjectId = (int) $subjectOptions->first()->id;
    }

    if ($selectedSubjectId > 0 && !$assignedSubjectIds->contains($selectedSubjectId)) {
      $selectedSubjectId = (int) ($subjectOptions->first()->id ?? 0);
    }

    $batchId = (int) $request->query('batch', 0);
    $semesterId = (int) $request->query('semester_id', 0);
    $requestedProgramType = strtoupper(trim((string) $request->query('program_type', 'ALL')));
    $programType = in_array($requestedProgramType, ['UG', 'PG', 'ALL'], true) ? $requestedProgramType : 'ALL';

    $defaultShift = ShiftMaster::where('slug', 'common')->value('slug');
    if (empty($defaultShift)) {
      $defaultShift = ShiftMaster::orderBy('sort_order')->value('slug') ?: 'common';
    }

    $selectedSubject = $selectedSubjectId > 0 ? Subject::find($selectedSubjectId) : null;
    $shiftOptionsQuery = ShiftMaster::where('is_active', 1)->orderBy('sort_order');
    if ($selectedSubject) {
      $subjectUsesShifts = (int) ($selectedSubject->has_shift_delivery ?? 0) === 1;
      $enabledShiftIds = [];
      $rawShiftIds = $selectedSubject->shift_ids;
      if (is_string($rawShiftIds)) {
        $decoded = json_decode($rawShiftIds, true);
        $rawShiftIds = is_array($decoded) ? $decoded : [];
      }
      if (is_array($rawShiftIds)) {
        $enabledShiftIds = collect($rawShiftIds)
          ->map(fn($id) => (int) $id)
          ->filter(fn($id) => $id > 0)
          ->unique()
          ->values()
          ->all();
      }

      if ($subjectUsesShifts && !empty($enabledShiftIds)) {
        $shiftOptionsQuery->whereIn('id', $enabledShiftIds);
      } else {
        $shiftOptionsQuery->where('slug', $defaultShift);
      }
    }

    $shiftOptions = $shiftOptionsQuery->get(['id', 'title', 'slug']);
    if ($shiftOptions->isEmpty()) {
      $shiftOptions = ShiftMaster::where('slug', $defaultShift)
        ->where('is_active', 1)
        ->get(['id', 'title', 'slug']);
    }

    $activeShift = (string) $request->query('shift', $defaultShift);
    if ($shiftOptions->isNotEmpty() && !$shiftOptions->pluck('slug')->contains($activeShift)) {
      $activeShift = (string) ($shiftOptions->first()->slug ?? $defaultShift);
    }

    $timetableQuery = SubjectHasRoutine::query()
      ->where(function ($query) use ($facultyId) {
        $query->where('faculty_id', $facultyId)
          ->orWhereHas('teachingAssignment', function ($assignmentQuery) use ($facultyId) {
            $assignmentQuery->where('faculty_id', $facultyId);
          });
      })
      ->with([
        'weekdaymaster:id,title',
        'hourmaster',
        'teachingAssignment:id,course_id,faculty_id,delivery_type,allocation_group,room',
        'teachingAssignment.course:id,course_code,course_title,course_type',
        'teachingAssignment.course.coursetypemaster:id,title',
        'syllabus.subject:id,title',
        'syllabus.batchmaster:id,batch_name',
        'syllabus.semestermaster:id,title',
        'subjectCourse.courseMaster:id,course_title,course_code,course_type',
        'subjectCourse.courseMaster.coursetypemaster:id,title',
      ]);

    if ($selectedSubjectId > 0) {
      $timetableQuery->whereHas('syllabus', function ($query) use ($selectedSubjectId) {
        $query->where('subject_id', $selectedSubjectId);
      });
    }

    if ($batchId > 0) {
      $timetableQuery->where('batch_id', $batchId);
    }

    if ($semesterId > 0) {
      $timetableQuery->whereHas('syllabus', function ($query) use ($semesterId) {
        $query->where('semester_id', $semesterId);
      });
    }

    $timetableQuery->where('shift', $activeShift);

    if ($programType !== 'ALL' && Schema::hasColumn('subject_has_routines', 'program_type')) {
      $timetableQuery->where('program_type', $programType);
    }

    $timetable = $timetableQuery
      ->orderBy('weekday_id')
      ->orderBy('hour_id')
      ->get()
      ->map(function ($routine) use ($programType) {
        $assignment = $routine->teachingAssignment;
        $course = $routine->subjectCourse->courseMaster ?? optional($assignment)->course;

        $hourNo = (int) ($routine->hourmaster->hour_no ?? $routine->hour_id ?? 0);
        $hourName = (string) ($routine->hourmaster->name ?? $routine->hourmaster->title ?? ('Hour ' . $hourNo));
        $startTime = (string) ($routine->hourmaster->start_time ?? '');
        $endTime = (string) ($routine->hourmaster->end_time ?? '');
        $hourLabel = $hourName;
        if ($startTime !== '' && $endTime !== '') {
          $hourLabel .= ' (' . $startTime . ' - ' . $endTime . ')';
        }

        $courseCode = (string) ($course->course_code ?? '');
        $courseTitle = (string) ($course->course_title ?? '-');

        return [
          'weekday' => $routine->weekdaymaster->title ?? '-',
          'hour' => $hourLabel,
          'hour_sort' => $hourNo > 0 ? $hourNo : (int) ($routine->hour_id ?? 0),
          'subject' => $routine->syllabus->subject->title ?? '-',
          'batch' => $routine->syllabus->batchmaster->batch_name ?? '-',
          'semester' => $routine->syllabus->semestermaster->title ?? '-',
          'room' => trim((string) ($assignment->room ?? '')) !== '' ? trim((string) ($assignment->room ?? '')) : '-',
          'course' => trim($courseCode . ($courseCode !== '' ? ' - ' : '') . $courseTitle),
          'course_type' => (string) ($course->coursetypemaster->title ?? '-'),
          'shift' => ucfirst((string) ($routine->shift ?? 'common')),
          'program_type' => strtoupper((string) ($routine->program_type ?? $programType)) === 'PG' ? 'PG' : 'UG',
        ];
      })
      ->values();

    return view('faculty.timetable', [
      'faculty' => $faculty,
      'timetable' => $timetable,
      'subjectOptions' => $subjectOptions,
      'selectedSubjectId' => $selectedSubjectId > 0 ? $selectedSubjectId : null,
      'selectedBatchId' => $batchId > 0 ? $batchId : null,
      'selectedSemesterId' => $semesterId > 0 ? $semesterId : null,
      'selectedShift' => $activeShift,
      'selectedProgramType' => $programType,
      'shiftOptions' => $shiftOptions,
      'semesterOptions' => Semester::orderBy('title')->get(['id', 'title']),
      'batches' => BatchMaster::latest()->get(),
    ]);
  }

  function subjects(Request $request)
  {
    $userId = Auth::user()->id;
    $facultyId = SubjectFacultyMaster::where('access_id', $userId)->value('faculty_id');
    $searchTerm = trim((string) $request->query('search', ''));
    $requestedProgramType = strtoupper(trim((string) $request->query('program_type', 'ALL')));
    $programType = in_array($requestedProgramType, ['UG', 'PG', 'ALL'], true) ? $requestedProgramType : 'ALL';

    // Get all subject IDs assigned to this faculty
    $assignedSubjectIds = SubjectFacultyMaster::where('faculty_id', $facultyId)
      ->pluck('subject_id')
      ->unique();

    // Get all syllabi for those subjects with their relationships
    $dataQuery = SubjectHasSyllabus::whereIn('subject_id', $assignedSubjectIds)
      ->with([
        'subject:id,title',
        'batchmaster:id,batch_name',
        'semestermaster:id,title',
        'courseLink.courseMaster.coursetypemaster',
      ]);

    if ($programType !== 'ALL') {
      $dataQuery->whereRaw("UPPER(TRIM(COALESCE(program_type, ''))) = ?", [$programType]);
    }

    if ($searchTerm !== '') {
      $likeTerm = '%' . $searchTerm . '%';
      $dataQuery->where(function ($query) use ($likeTerm) {
        $query->whereHas('subject', function ($q) use ($likeTerm) {
          $q->where('title', 'LIKE', $likeTerm);
        })->orWhereHas('batchmaster', function ($q) use ($likeTerm) {
          $q->where('batch_name', 'LIKE', $likeTerm);
        })->orWhereHas('semestermaster', function ($q) use ($likeTerm) {
          $q->where('title', 'LIKE', $likeTerm);
        })->orWhereHas('courseLink.courseMaster', function ($q) use ($likeTerm) {
          $q->where('course_code', 'LIKE', $likeTerm)
            ->orWhere('course_title', 'LIKE', $likeTerm);
        });
      });
    }

    $data = $dataQuery->get();

    // Load all syllabus managers (CSOs) with their units for each subject
    $data->each(function ($syllabus) {
      // Get all SyllabusManager records for this subject/batch/semester combination with CSO info
      $syllabusManagers = SyllabusManager::where('subject_id', $syllabus->subject_id)
        ->where('batch_id', $syllabus->batch_id)
        ->where('semester_id', $syllabus->semester_id)
        ->when(!empty($syllabus->program_type), function ($query) use ($syllabus) {
          $query->where('program_type', $syllabus->program_type);
        })
        ->with([
          'cso:id,title,lectures_needed',
          'syllabusSubunits.csoSubunit.taxomonylevel'
        ])
        ->get();

      // Group by CSO and attach to syllabus
      $syllabus->csoGroups = $syllabusManagers->map(function ($manager) {
        return [
          'cso' => $manager->cso,
          'units' => $manager->syllabusSubunits
        ];
      });

      // Also keep flat list for backward compatibility with completion stats
      $syllabus->syllabusunits = $syllabusManagers->pluck('syllabusSubunits')->flatten();
    });

    // Group by semester first, then batch.
    $semesterWiseSubjects = $data
      ->groupBy(function ($item) {
        return $item->semestermaster->title ?? 'Unknown Semester';
      })
      ->map(function ($semesterRows) {
        return $semesterRows->groupBy(function ($item) {
          return $item->batchmaster->batch_name ?? 'Unknown Batch';
        });
      });

    return view('faculty.subjects', [
      'semesterWiseSubjects' => $semesterWiseSubjects,
      'selectedProgramType' => $programType,
      'searchTerm' => $searchTerm,
    ]);
  }



  function toggleSubunitCompletion($id)
  {
    $syllabusSubunit = SyllabusSubunit::find($id);

    if ($syllabusSubunit) {
      // Toggle the completion status
      $syllabusSubunit->is_completed = $syllabusSubunit->is_completed == 1 ? 0 : 1;
      $syllabusSubunit->save();
    }

    return redirect()->back()->with('success', 'Subunit completion status updated successfully.');
  }

  function profile()
  {
    $userId = Auth::user()->id;
    $facultyId = SubjectFacultyMaster::where('access_id', $userId)->value('faculty_id');
    $faculty = Faculty::findOrFail($facultyId);
    return view('faculty.profile', [
      'faculty' => $faculty
    ]);
  }

  public function updateProfile(Request $request)
  {
    $userId = Auth::user()->id;
    $facultyId = SubjectFacultyMaster::where('access_id', $userId)->value('faculty_id');
    $faculty = Faculty::findOrFail($facultyId);

    // Validate the request
    $validated = $request->validate([
      'fname' => 'required|string|max:255',
      'lname' => 'nullable|string|max:255',
      'gender' => 'required|in:male,female,other',
      'email' => 'required|email|max:255',
      'phone' => 'required|string|max:15',
      'address' => 'nullable|string',
      'dob' => 'nullable|date',
      'specialization' => 'nullable|string',
    ]);

    // Update the faculty record
    $faculty->update([
      'FIRST_NAME' => $validated['fname'],
      'LAST_NAME' => $validated['lname'] ?? null,
      'ADDRESS' => $validated['address'] ?? null,
      'GENDER' => $validated['gender'] == 'male' ? 1 : ($validated['gender'] == 'female' ? 2 : 3),
      'MAIL_ID' => $validated['email'],
      'MOBILE_NO' => $validated['phone'],
      'DOB' => $validated['dob'] ?? null,
    ]);

    return redirect()->route('faculty.profile')->with('success', 'Profile updated successfully!');
  }

  public function updatePhoto(Request $request)
  {
    $userId = Auth::user()->id;
    $facultyId = SubjectFacultyMaster::where('access_id', $userId)->value('faculty_id');
    $faculty = Faculty::findOrFail($facultyId);

    // Validate the photo
    $validated = $request->validate([
      'photo' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048', // 2MB max
    ]);

    // Handle the photo upload
    if ($request->hasFile('photo')) {
      $photo = $request->file('photo');
      $photoPath = StaticController::s3_resize_image_uploader($photo, 'faculty_photos', 300, 300);
    }

    // Update the faculty record
    $faculty->update(['photo' => $photoPath]);

    return redirect()->route('faculty.profile')->with('success', 'Profile photo updated successfully!');
  }
}
