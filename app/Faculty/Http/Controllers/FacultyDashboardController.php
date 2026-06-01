<?php

namespace App\Faculty\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Controllers\StaticController;
use App\Models\Faculty;
use App\Models\FacultyLeaveApplication;
use App\Models\SubjectFacultyMaster;
use App\Models\SubjectHasRoutine;
use App\Models\SubjectHasSyllabus;
use App\Models\SyllabusManager;
use App\Models\SyllabusSubunit;
use App\Models\UserHasRole;
use App\Models\WorkDiary;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
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

    return view('faculty.dashboard', compact('workDiaryEntries', 'assignedSubjects', 'totalSubjectsCount', 'leaveStats', 'casualLeaves', 'sickLeaves', 'earnedLeaves', 'recentLeaves'));
  }

  public function facultyTimetable(Request $request)
  {

    $userId = Auth::user()->id;
    $facultyId =   SubjectFacultyMaster::where('access_id', $userId)->pluck('faculty_id');


    $faculty = Faculty::findOrFail($facultyId);

    if (!empty($request->batch)) {
      $batchId = $request->batch;
      $timetable = SubjectHasRoutine::where('faculty_id', $facultyId)
        ->where('batch_id', $batchId) // Filter by batch if provided
        ->with([
          'weekdaymaster:id,title',
          'hourmaster:id,title',
          'syllabus.subject:id,title',
          'syllabus.batchmaster:id,batch_name',
          'syllabus.semestermaster:id,title',
          'lecturehallmaster:id,title',
          'subjectCourse.courseMaster:id,course_title,course_code,course_type',
          'subjectCourse.courseMaster.coursetypemaster:id,title',

        ])->get()->map(function ($routine) {
          return [
            'weekday' => $routine->weekdaymaster->title ?? '-',
            'hour' => $routine->hourmaster->title ?? '-',
            'subject' => $routine->syllabus->subject->title ?? '-',
            'batch' => $routine->syllabus->batchmaster->batch_name ?? '-',
            'semester' => $routine->syllabus->semestermaster->title ?? '-',
            'lecture_hall' => $routine->lecturehallmaster->title ?? '-',
            'course' => $routine->subjectCourse->courseMaster->course_code . '-' . $routine->subjectCourse->courseMaster->course_title,
            'course_type' => $routine->subjectCourse->courseMaster->coursetypemaster->title ?? '-',
          ];
        });
    } else {
      $timetable = SubjectHasRoutine::where('faculty_id', $facultyId)

        ->with([
          'weekdaymaster:id,title',
          'hourmaster:id,title',
          'syllabus.subject:id,title',
          'syllabus.batchmaster:id,batch_name',
          'syllabus.semestermaster:id,title',
          'lecturehallmaster:id,title',
          'subjectCourse.courseMaster:id,course_title,course_code,course_type',
          'subjectCourse.courseMaster.coursetypemaster:id,title',

        ])->get()->map(function ($routine) {
          return [
            'weekday' => $routine->weekdaymaster->title ?? '-',
            'hour' => $routine->hourmaster->title ?? '-',
            'subject' => $routine->syllabus->subject->title ?? '-',
            'batch' => $routine->syllabus->batchmaster->batch_name ?? '-',
            'semester' => $routine->syllabus->semestermaster->title ?? '-',
            'lecture_hall' => $routine->lecturehallmaster->title ?? '-',
            'course' => $routine->subjectCourse->courseMaster->course_code . '-' . $routine->subjectCourse->courseMaster->course_title,
            'course_type' => $routine->subjectCourse->courseMaster->coursetypemaster->title ?? '-',
          ];
        });
    }

    return view('faculty.timetable', [
      'faculty' => $faculty,
      'timetable' => $timetable
    ]);
  }

  function subjects(Request $request)
  {
    $userId = Auth::user()->id;
    $facultyId = SubjectFacultyMaster::where('access_id', $userId)->value('faculty_id');

    // Get all subject IDs assigned to this faculty
    $assignedSubjectIds = SubjectFacultyMaster::where('faculty_id', $facultyId)
      ->pluck('subject_id')
      ->unique();

    // Get all syllabi for those subjects with their relationships
    $data = SubjectHasSyllabus::whereIn('subject_id', $assignedSubjectIds)
      ->with([
        'subject:id,title',
        'batchmaster:id,batch_name',
        'semestermaster:id,title',
        'courseLink.courseMaster.coursetypemaster',
      ])
      ->get();

    // Load all syllabus managers (CSOs) with their units for each subject
    $data->each(function ($syllabus) {
      // Get all SyllabusManager records for this subject/batch/semester combination with CSO info
      $syllabusManagers = SyllabusManager::where('subject_id', $syllabus->subject_id)
        ->where('batch_id', $syllabus->batch_id)
        ->where('semester_id', $syllabus->semester_id)
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

    // Group by batch name
    $batchWiseSubjects = $data->groupBy(function ($item) {
      return $item->batchmaster->batch_name ?? 'Unknown Batch';
    });

    return view('faculty.subjects', [
      'batchWiseSubjects' => $batchWiseSubjects
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
