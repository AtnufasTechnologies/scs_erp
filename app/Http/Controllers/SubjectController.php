<?php

namespace App\Http\Controllers;

use App\Models\BatchMaster;
use App\Models\Campus;
use App\Models\CognitiveLevelMaster;
use App\Models\Department;
use App\Models\Faculty;
use App\Models\LectureHallMaster;
use App\Models\MainProgram;
use App\Models\ProgramCourseMaster;
use App\Models\ProgramMaster;
use App\Models\StudentMaster;
use App\Models\StudentProgram;
use App\Models\Subject;
use App\Models\SubjectCourseMaster;
use App\Models\SubjectFacultyMaster;
use App\Models\SubjectHasCombination;
use App\Models\SubjectHasDeptAdmin;
use App\Models\SubjectHasRoutine;
use App\Models\SubjectHasSemester;
use App\Models\SubjectHasStudentProgam;
use App\Models\SubjectHasSyllabus;
use App\Models\SubjectTypeMaster;
use App\Models\SyllabusHasFaculty;
use App\Models\User;
use App\Models\UserHasRole;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class SubjectController extends Controller
{
    function index()
    {
        $data = Subject::with([
            'campusmaster'
        ])->latest()->get();
        return view('admin.master.subject', ['data' => $data]);
    }

    function subjectType()
    {
        $data = SubjectTypeMaster::get();
        return view('admin.master.subject-type', ['data' => $data]);
    }

    function addSubject(Request $request)
    {

        $request->validate([
            'code' => 'required|string|max:100',
            'title' => 'required|string|max:255',
            'campus' => 'required',
        ]);
        $slug = Str::slug($request->title);
        $code = Str::upper($request->code);
        $check = Subject::where('code', $code)->where('campus_id', $request->campus)->count();
        if ($check > 0) {
            return response()->json(['msg' => 'Subject already exists', 'status' => 'error']);
        } else {
            $mainStream = ProgramMaster::find($request->program_id);
            if ($request->campus == 3) {
                $campuses = Campus::all();
                foreach ($campuses as $campus) {
                    $rec = new Subject();
                    $rec->campus_id = $campus->id;
                    $rec->main_program_type = $mainStream->title;
                    $rec->slug =   $slug;
                    $rec->code = $request->code;
                    $rec->title = $request->title;
                    $rec->save();
                }
            } else {
                $rec = new Subject();
                $rec->campus_id = $request->campus;
                $rec->main_program_type = $mainStream->title;
                $rec->slug =   $slug;
                $rec->code = $request->code;
                $rec->title = $request->title;
                $rec->save();
            }


            return redirect()->back()->with('success', 'Created');
        }
    }

    function subjectSingle(Request $request)
    {
        $request->validate([
            'id' => 'required',
        ]);

        $subjectId = $request->id;
        $subject = Subject::with(['semesters'])->find($subjectId);

        // Check if subject exists
        if (!$subject) {
            return redirect()->back()->with('error', 'Subject not found');
        }

        // Course Master
        $courseMaster = $subject;

        // Number of Students (total students in all batches for this subject/department)
        $studentsCount = 0;
        $batchWiseStudents = [];
        $semestersCount = $subject->semesters?->count() ?? 0;

        // Get all batches
        $batches = BatchMaster::all();
        foreach ($batches as $batch) {
            $studentCount = \App\Models\StudentMaster::where('department', $subjectId)
                ->where('batch', $batch->id)
                ->count();
            $batchWiseStudents[] = [
                'batch_name' => $batch->batch_name,
                'student_count' => $studentCount
            ];
            $studentsCount += $studentCount;
        }


        // For combinations modal
        if (!empty($request->batch)) {
            $activeBatch = $request->batch;
        } else {
            $activeBatch = BatchMaster::where('admission_active_batch', 1)->value('id');
        }

        $combinations = SubjectHasStudentProgam::where('subject_id', $subjectId)
            ->with(['studentprograminfo', 'batchmaster'])
            ->where('batch_id', $activeBatch)
            ->get();


        $programs = StudentProgram::where('campus_id', $subject->campus_id)->get();
        $course_master_count = SubjectCourseMaster::where('subject_id', $subjectId)->count();
        $faculties = SubjectFacultyMaster::with('faculty')->where('subject_id', $subjectId)->get();
        return view('admin.subject.department-dashboard', [
            'data' => $courseMaster,
            'students_count' => $studentsCount,
            'semesters_count' => $semestersCount,
            'batchWiseStudents' => $batchWiseStudents,
            'combinations' => $combinations,
            'programs' => $programs,
            'course_master_count' => $course_master_count,
            'deptfaculties' => $faculties,
        ]);
    }


    function viewRoutine($id)
    {
        $data =  SubjectHasSyllabus::where('subject_id', $id)->with([
            'sessionmaster:id,title',
            'semestermaster:id,title',
            'subtypemaster:id,title',
            'timetable.weekdaymaster:id,title',
            'timetable.hourmaster:id,title',
            'timetable.lecturehallmaster.acblockmaster:id,title',
        ])->get();
        return response()->json(['data' => $data]);
    }

    function addSyllabus(Request $request)
    {
        $request->validate([
            'subject_id' => 'required',
            'batch' => 'required',
            'semester_id' => 'required',
            'title' => 'required',
            'desc' => 'required',
            'subject_type_id' => 'required'
        ]);



        $rec = new SubjectHasSyllabus();
        $rec->dept_id = $request->dept_id;
        $rec->subject_id = $request->subject_id;
        $rec->session_id = $request->session_id;
        $rec->semester_id = $request->semester_id;
        $rec->title = $request->title;
        $rec->content = $request->desc;
        $rec->subject_type_id = $request->subject_type_id;
        $rec->save();
        return response()->json(['msg' => 'Data Added', 'status' => 'success']);
    }

    function cogLevelMaster()
    {
        $data = CognitiveLevelMaster::latest()->get();
        return response()->json(['data' => $data]);
    }
    function addCognitiveLevel(Request $request)
    {
        $validator  =  Validator::make($request->all(), [
            'shortname' => 'required',
            'fullname' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => 'Validation failed', 'status' => false], 400);
        }

        $rec = new CognitiveLevelMaster();
        $rec->shortname = $request->shortname;
        $rec->fullname = $request->fullname;
        $rec->save();
        return response()->json(['message' => 'Cognitive Level Created'], 201);
    }


    function addSubjectTimeTable(Request $request)
    {
        $validator  =  Validator::make($request->all(), [
            'syllabus_id' => 'required',
            'weekday_id' => 'required',
            'hour_id' => 'required',
            'lecturehall_id' => 'required',

        ]);

        if ($validator->fails()) {
            return response()->json(['message' => 'Validation failed', 'status' => false], 400);
        }

        $rec = new SubjectHasRoutine();
        $rec->syllabus_id = $request->syllabus_id;
        $rec->weekday_id = $request->weekday_id;
        $rec->hour_id = $request->hour_id;
        $rec->lecturehall_id = $request->lecturehall_id;
        $rec->save();
        return response()->json(['message' => 'TimeTable Created'], 201);
    }

    function addSemesterToSubject(Request $request)
    {

        $request->validate([
            'semesters' => 'required|array|min:1',
            'subject_id' => 'required',
            'batch' => 'required',
        ]);


        $semesters = $request->semesters;
        $subject_id = $request->subject_id;
        $batch = $request->batch;


        for ($i = 0; $i < count($semesters); $i++) {

            $check = SubjectHasSemester::where('subject_id', $subject_id)
                ->where('semester_id', $semesters[$i])
                ->where('batch_id', $batch)
                ->doesntExist();

            if ($check) {
                SubjectHasSemester::create([
                    'subject_id' => $subject_id,
                    'semester_id' => $semesters[$i],
                    'batch_id' => $batch,
                ]);
            }
        }
        return redirect()->back()->with('success', 'Semester(s) Added');
    }

    function deleteSubject($id)
    {
        $subject = Subject::findOrFail($id);
        $subject->delete();
        return redirect()->back()->with('success', 'Subject Deleted');
    }

    function linkStdPrograms(Request $request)
    {
        $validator  =  $request->validate([
            'subject_id' => 'required',
            'batch_id' => 'required',
            'programs' => 'required|array|min:1',
            'program_type' => 'required',
        ]);

        $userId  = Auth::user()->id;
        if (!UserHasRole::where('user_id', $userId)->where('role_name', 'dept-admin-erp')->exists()) {
            return redirect()->back()->with('info', 'Unauthorized to Access this Tool');
        }

        $programs = $request->programs;
        $subject_id = $request->subject_id;
        $data = Subject::find($subject_id);


        for ($i = 0; $i < count($programs); $i++) {

            $recordCheck = SubjectHasStudentProgam::where('subject_id', $subject_id)
                ->where('batch_id', $request->batch_id)
                ->where('student_program_id', $programs[$i])
                ->where('campus_id', $data->campus_id)
                ->where('program_type', $request->program_type)
                ->first();



            if ($recordCheck == null) {

                $departmentId =  StudentProgram::where('id', $programs[$i])->value('department');
                $subject = new SubjectHasStudentProgam();
                $subject->subject_id = $subject_id;
                $subject->batch_id = $request->batch_id;
                $subject->student_program_id = $programs[$i];
                $subject->campus_id = $data->campus_id;
                $subject->program_type = $request->program_type;
                $subject->save();
            } else {
                return redirect()->back()->with('success', 'Combinations Already Linked');
            }
        }

        return redirect()->back()->with('success', 'Combinations linked successfully');
    }

    //department dashboard
    function departmentDashboard(Request $request)
    {
        $userId = Auth::user()->id;


        $subjectId = SubjectHasDeptAdmin::where('user_id', $userId)->value('subject_id');


        $subject = Subject::with(['semesters', 'courseMasterPivot'])->find($subjectId);

        // Check if subject exists
        if (!$subject) {
            return redirect()->back()->with('info', 'Subject not found or user not assigned to any department');
        }

        // Course Master
        $courseMaster = $subject;

        // Number of Students (total students in all batches for this subject/department)
        $studentsCount = 0;
        $batchWiseStudents = [];
        $semestersCount = $subject->semesters?->count() ?? 0;

        // Get all batches
        $batches = BatchMaster::all();
        foreach ($batches as $batch) {
            $studentCount = \App\Models\StudentMaster::where('department', $subjectId)
                ->where('batch', $batch->id)
                ->count();
            $batchWiseStudents[] = [
                'batch_name' => $batch->batch_name,
                'student_count' => $studentCount
            ];
            $studentsCount += $studentCount;
        }


        // For combinations modal
        if (!empty($request->batch)) {
            $activeBatch = $request->batch;
        } else {
            $activeBatch = BatchMaster::where('admission_active_batch', 1)->value('id');
        }

        $combinations = SubjectHasStudentProgam::where('subject_id', $subjectId)
            ->with(['studentprograminfo', 'batchmaster'])
            ->where('batch_id', $activeBatch)
            ->get();


        $programs = StudentProgram::where('campus_id', $subject->campus_id)->get();
        $faculties = SubjectFacultyMaster::with('faculty')->where('subject_id', $subjectId)->get();

        return view('admin.subject.department-dashboard', [
            'data' => $courseMaster,
            'students_count' => $studentsCount,
            'semesters_count' => $semestersCount,
            'batchWiseStudents' => $batchWiseStudents,
            'combinations' => $combinations,
            'programs' => $programs,
            'deptfaculties' => $faculties,
        ]);
    }

    function deleteCombination($id)
    {
        $combination = SubjectHasStudentProgam::findOrFail($id);
        $combination->delete();
        return redirect()->back()->with('success', 'Combination Deleted');
    }

    function courseMaster($academicDeptId, $slug)
    {
        $data = Subject::find($academicDeptId);

        $courses =  SubjectCourseMaster::with([
            'courseMaster',
        ])->where('subject_id', $academicDeptId)->get();

        $programCourseMaster =  ProgramCourseMaster::all();
        $assignedCourseIds = SubjectCourseMaster::where('subject_id', $academicDeptId)
            ->pluck('course_master_id')
            ->where('is_deleted', 0)
            ->toArray();

        $unassignedCourses = ProgramCourseMaster::whereNotIn('id', $assignedCourseIds)
            ->with('coursetypemaster')->get();

        return view('admin.subject.course-master', [
            'data' => $data,
            'course_master' => $unassignedCourses,
            'mycourses' => $courses,
        ]);
    }


    function addCourseMaster(Request $request)
    {
        $request->validate([
            'subject_id' => 'required',
            'courses' => 'required|array|min:1',
        ]);

        $courseMasterId =   $request->courses;

        for ($i = 0; $i < count($courseMasterId); $i++) {
            $courseId = $courseMasterId[$i];
            $existingRecord = SubjectCourseMaster::where('subject_id', $request->subject_id)
                ->where('course_master_id', $courseId)
                ->first();

            if (!$existingRecord) {
                $rec = new SubjectCourseMaster();
                $rec->subject_id = $request->subject_id;
                $rec->course_master_id = $courseId;
                $rec->save();
            }
        }


        return redirect()->back()->with('success', 'Course Master Added');
    }


    function adminCourseMaster(Request $request)
    {
        $courseTypeFilter = $request->input('course_type');

        if ($courseTypeFilter) {
            $data = ProgramCourseMaster::with([
                'coursetypemaster',
                'semestermaster',
            ])->whereHas('coursetypemaster', function ($query) use ($courseTypeFilter) {
                $query->where('id', $courseTypeFilter);
            })->withCount(['stucourseinfo' => function ($query) {
                $query->where('is_deleted', 0);
                $query->where('campus_id', 2);
            }])->get();
        } else {

            $data = ProgramCourseMaster::with([
                'coursetypemaster',
                'semestermaster',
            ])->withCount(['stucourseinfo' => function ($query) {
                $query->where('is_deleted', 0);
                $query->where('campus_id', 2);
            }])->get();
        }
        return view('admin.master.course-master', ['data' => $data]);
    }


    function deptAllCourseCombinations(Request $request)
    {

        $combinations = SubjectHasStudentProgam::with([
            'subjectmaster',
            'studentprograminfo',
            'batchmaster',
            'campusmaster',
        ])->get();

        return view('admin.subject.all-combination', ['combinations' => $combinations]);
    }


    function deleteSemesterFromSubject($id)
    {
        $record = SubjectHasSemester::findOrFail($id);
        $record->delete();
        return redirect()->back()->with('success', 'Semester Removed from Subject');
    }

    function addFacultyMasterToSubject(Request $request)
    {
        $request->validate([
            'subject_id' => 'required',
            'faculty' => 'required|array|min:1',
        ]);

        $facultyIds =   $request->faculty;

        for ($i = 0; $i < count($facultyIds); $i++) {
            $facultyId = $facultyIds[$i];

            $existingRecord = SubjectFacultyMaster::where('subject_id', $request->subject_id)
                ->where('faculty_id', $facultyId)
                ->first();

            if (!$existingRecord) {
                $rec = new SubjectFacultyMaster();
                $rec->subject_id = $request->subject_id;
                $rec->faculty_id = $facultyId;
                $rec->save();
            }
        }

        return redirect()->back()->with('success', 'Faculty Added ');
    }

    function deleteFacultyMasterFromSubject($id)
    {
        $record = SubjectFacultyMaster::findOrFail($id);
        $record->delete();
        return redirect()->back()->with('success', 'Faculty Removed from Subject');
    }

    function studentProgramMaster()
    {
        $data = StudentProgram::with('programgroup')
            ->latest()->get()
            ->map(function ($program) {
                $program->student_count = StudentMaster::where('new_program_id', $program->id)->count();
                return $program;
            });

        return view('admin.subject.student-program-master', ['data' => $data]);
    }

    function addNewStudentProgram(Request $request)
    {
        $request->validate([
            'campus' => 'required',
            'code' => 'required|string|max:100',
            'name' => 'required|string|max:255',
            'semester_count' => 'required|integer|min:1',
            'description' => 'nullable|string',
        ]);

        $rec = new StudentProgram();
        $rec->campus_id = $request->campus;
        $rec->code = Str::upper($request->code);
        $rec->name = Str::lower($request->name);
        $rec->description = Str::lower($request->description);
        $rec->semester_count = $request->semester_count;
        $rec->save();

        return redirect()->back()->with('success', 'New Program Added');
    }

    function updateAcademicDept(Request $request, $id)
    {
        $data = Subject::findOrFail($id);
        $request->validate([
            'campus' => 'required',
            'program_id' => 'required',
        ]);


        $data->campus_id = $request->campus;
        $data->main_program_type = $request->program_id;
        $data->code = $request->code;
        $data->title = $request->title;
        $data->save();

        return redirect()->back()->with('success', 'Academic Department Updated');
    }

    function updateStudentProgram(Request $request, $id)
    {
        $data = StudentProgram::findOrFail($id);
        $request->validate([
            'campus' => 'required',
            'code' => 'required|string|max:100',
            'name' => 'required|string|max:255',
            'semester_count' => 'required|integer|min:1',
            'description' => 'nullable|string',
        ]);

        $data->campus_id = $request->campus;
        $data->code = Str::upper($request->code);
        $data->name = Str::lower($request->name);
        $data->description = Str::lower($request->description);
        $data->semester_count = $request->semester_count;
        $data->save();

        return redirect()->back()->with('success', 'Program Updated');
    }
}
