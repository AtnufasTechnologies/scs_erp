<?php

namespace App\Faculty\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Controllers\StaticController;
use App\Models\Faculty;
use App\Models\SubjectFacultyMaster;
use App\Models\SubjectHasRoutine;
use App\Models\SyllabusManager;
use App\Models\SyllabusSubunit;
use App\Models\UserHasRole;
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
    return view('faculty.dashboard');
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

    // Get subjects assigned to faculty from timetable
    $data = SubjectHasRoutine::where('faculty_id', $facultyId)
      ->with([
        'syllabus.subject:id,title',
        'syllabus.batchmaster:id,batch_name',
        'syllabus.semestermaster:id,title',
        'syllabus.courseLink.courseMaster.coursetypemaster',
        'syllabus.syllabusunits.csoSubunit.taxomonylevel',
      ])
      ->distinct()
      ->get('syllabus_id');
    $batchWiseSubjects = $data->groupBy(function ($item) {
      return $item->syllabus->batchmaster->batch_name;
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
