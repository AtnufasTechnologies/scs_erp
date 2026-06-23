<?php

namespace App\Http\Controllers;

use App\Models\AcademicBlock;
use App\Models\AcademicDepartment;
use App\Models\BatchMaster;
use App\Models\BloodGroupMaster;
use App\Models\Campus;
use App\Models\CognitiveLevelMaster;
use App\Models\CollegeBankAccount;
use App\Models\Deanery;
use App\Models\Department;
use App\Models\DepartmentMaster;
use App\Models\Faculty;
use App\Models\FeeCourseMaster;
use App\Models\FeeHead;
use App\Models\FeeQuarterMaster;
use App\Models\FeesStructure;
use App\Models\FeeStructureGroup;
use App\Models\FeeStructureHasHead;
use App\Models\FeeStructureHasManyProgram;
use App\Models\HourMaster;
use App\Models\LateFee;
use App\Models\LectureHallMaster;
use App\Models\MainProgram;
use App\Models\MenuMaster;
use App\Models\NationalityMaster;
use App\Models\PaperTypeMaster;
use App\Models\ProgramCourseMaster;
use App\Models\ProgramGroup;
use App\Models\ProgramMaster;
use App\Models\ReligionMaster;
use App\Models\RoleMaster;
use App\Models\RoomMaster;
use App\Models\Semester;
use App\Models\InterMark;
use App\Models\StudentAttendance;
use App\Models\StudentCourseInfo;
use App\Models\StudentMaster;
use App\Models\SubjectHasRoutine;
use App\Models\ExamSystem\ExamStudent;
use App\Models\ExamSystem\Result;
use App\Models\Quote;
use App\Models\StudentProgram;
use App\Models\StudentProgramTypeMaster;
use App\Models\User;
use App\Models\UserCampusSetting;
use App\Models\UserHasPermission;
use App\Models\UserHasRole;
use App\Models\UserMenuPermission;
use App\Models\UserType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Illuminate\Testing\Fluent\Concerns\Has;

class AdminController extends Controller
{
    function index()
    {
        $quote = Quote::where('is_active', true)->inRandomOrder()->first();
        return view('admin.dashboard', ['quote' => $quote]);
    }

    function stdMasterSonada()
    {
        $data = StudentMaster::with([
            'religionmaster:id,name',
            'deptmaster:id,department_code,name',
            'campusmaster:id,slug,name',
            'nationalitymaster:id,name',
            'usertype:id,name',
            'bloodgroup',
            'batchmaster:id,batch_name',
            'programgroup'

        ])->where('campus_id', 1)->paginate(12);

        return view('admin.students.student-master', ['data' => $data]);
    }

    function stdMasterSiliguri()
    {
        $data = StudentMaster::with([
            'religionmaster:id,name',
            'campusmaster:id,slug,name',
            'nationalitymaster:id,name',
            'usertype:id,name',
            'bloodgroup',
            'batchmaster:id,batch_name',
            'stdprogramenrolled',

        ])->where('campus_id', 2)->paginate(12);

        return view('admin.students.student-master', ['data' => $data]);
    }

    function searchStudents(Request $request)
    {
        $searchTerm = $request->input('search');
        $campusId = $request->input('campus_id', 2); // Default to Siliguri

        $query = StudentMaster::with([
            'religionmaster:id,name',
            'campusmaster:id,slug,name',
            'deptmaster:id,department_code,name',
            'nationalitymaster:id,name',
            'usertype:id,name',
            'bloodgroup',
            'batchmaster:id,batch_name',
            'stdprogramenrolled',
            'programgroup'
        ])->where('campus_id', $campusId);

        if (!empty($searchTerm)) {
            $query->where(function ($q) use ($searchTerm) {
                $q->where('first_name', 'LIKE', "%{$searchTerm}%")
                    ->orWhere('last_name', 'LIKE', "%{$searchTerm}%")
                    ->orWhere('roll_no', 'LIKE', "%{$searchTerm}%")
                    ->orWhere('register_no', 'LIKE', "%{$searchTerm}%")
                    ->orWhere('mail_id', 'LIKE', "%{$searchTerm}%")
                    ->orWhere('mobile_no', 'LIKE', "%{$searchTerm}%")
                    ->orWhereHas('deptmaster', function ($query) use ($searchTerm) {
                        $query->where('name', 'LIKE', "%{$searchTerm}%");
                    })
                    ->orWhereHas('stdprogramenrolled', function ($query) use ($searchTerm) {
                        $query->where('code', 'LIKE', "%{$searchTerm}%")
                            ->orWhere('name', 'LIKE', "%{$searchTerm}%");
                    })
                    ->orWhereHas('programgroup', function ($query) use ($searchTerm) {
                        $query->where('program_code', 'LIKE', "%{$searchTerm}%");
                    })
                    ->orWhereHas('campusmaster', function ($query) use ($searchTerm) {
                        $query->where('name', 'LIKE', "%{$searchTerm}%");
                    })
                    ->orWhereHas('batchmaster', function ($query) use ($searchTerm) {
                        $query->where('batch_name', 'LIKE', "%{$searchTerm}%");
                    });
            });
        }

        $students = $query->paginate(50);

        // Add semester information to each student
        foreach ($students as $student) {
            $student->current_semester = StudentCourseInfo::where('student_id', $student->id)
                ->distinct('semester')
                ->orderBy('semester', 'desc')
                ->value('semester');
        }

        if ($request->ajax()) {
            return response()->json([
                'students' => $students->items(),
                'total' => $students->total(),
                'current_page' => $students->currentPage(),
                'last_page' => $students->lastPage(),
            ]);
        }

        return view('admin.students.student-master', ['data' => $students]);
    }

    function stdprofile(int $id, string $rollno)
    {
        $data = StudentMaster::where('id', $id)->with([
            'religionmaster:id,name',
            'deptmaster:id,department_code,name',
            'campusmaster:id,slug,name',
            'nationalitymaster:id,name',
            'usertype:id,name',
            'bloodgroup',
            'batchmaster:id,batch_name',
            'programgroup.programInfo',
            'feepayment.feepaymentinfo:id,quarter_title',
            'feepayment.gatewaytype',
        ])->firstOrFail();

        // Fetch student's courses with semester and course-type relations
        $studentCourses = StudentCourseInfo::with([
            'coursemaster.semestermaster:id,title',
            'coursemaster.coursetypemaster:id,title',
        ])
            ->where('student_id', $id)
            ->whereNull('deleted_at')
            ->get();

        // Build semester ID → title map for grouping
        $semesterMap = Semester::pluck('title', 'id')->toArray();

        // Group courses by the semester stored in student_course_infos (set during enrollment)
        $coursesBySemester = $studentCourses->sortBy(fn($c) => $c->semester ?? 999)
            ->groupBy(fn($c) => $semesterMap[$c->semester] ?? ('Semester ' . ($c->semester ?? '?')));

        // Course IDs that have FA marks — used to lock edit/delete
        $faMarkedCourseIds = InterMark::where('student_id', $id)
            ->pluck('course_id')
            ->unique()
            ->toArray();

        // Course IDs that have SA marks (via exam_marks_entries)
        $saMarkedCourseIds = DB::table('exam_marks_entries')
            ->where('erp_student_id', $id)
            ->pluck('erp_subject_id')
            ->unique()
            ->toArray();

        $lockedCourseIds = array_unique(array_merge($faMarkedCourseIds, $saMarkedCourseIds));

        // Available courses for enrollment modal — grouped by semester
        $enrolledCourseIds = $studentCourses->pluck('course_id')->toArray();
        $availableCourses  = ProgramCourseMaster::where('is_deleted', 0)
            ->whereNotIn('id', $enrolledCourseIds)
            ->with('semestermaster:id,title', 'coursetypemaster:id,title')
            ->orderBy('semester_id')
            ->orderBy('course_title')
            ->get()
            ->groupBy(fn($c) => $c->semester_id);

        // All semesters for the modal filter tabs
        $availableSemesters = Semester::orderBy('id')->get();

        // Timetable: all routines for the student's batch
        $timetable = SubjectHasRoutine::where('batch_id', $data->batch)
            ->with([
                'weekdaymaster:id,title',
                'hourmaster:id,title',
                'lecturehallmaster:id,title',
                'faculty:id,FIRST_NAME,LAST_NAME',
                'coursemaster:id,course_title,course_code',
            ])
            ->orderBy('weekday_id')
            ->orderBy('hour_id')
            ->get();

        // Group timetable by weekday
        $timetableByDay = $timetable->groupBy(fn($r) => $r->weekdaymaster->title ?? 'Unknown');

        // Attendance: per-course summary for the student
        $attendanceRaw = StudentAttendance::where('student_id', $id)
            ->with('courseinfo:id,course_title,course_code')
            ->get()
            ->groupBy('course_id');

        $attendanceSummary = $attendanceRaw->map(function ($records) {
            $total   = $records->count();
            $present = $records->where('status', 'present')->count();
            $absent  = $total - $present;
            return [
                'course'     => $records->first()->courseinfo,
                'total'      => $total,
                'present'    => $present,
                'absent'     => $absent,
                'percentage' => $total > 0 ? round(($present / $total) * 100, 1) : 0,
            ];
        })->values();

        // Internal Marks (FA)
        $internalMarks = InterMark::where('student_id', $id)
            ->with([
                'course:id,course_title,course_code',
                'semester:id,title',
            ])
            ->where('is_deleted', 0)
            ->orderBy('semester')
            ->get();

        // Exam Results via ExamStudent bridge
        $examStudent = ExamStudent::where('erp_student_id', $id)->first();
        $examResults = collect();
        if ($examStudent) {
            $examResults = Result::where('exam_student_id', $examStudent->id)
                ->where('is_published', true)
                ->with(['examSession', 'resultSubjects'])
                ->orderByDesc('created_at')
                ->get();
        }

        return view('admin.master.student-profile', [
            'data'               => $data,
            'studentCourses'     => $studentCourses,
            'coursesBySemester'  => $coursesBySemester,
            'lockedCourseIds'    => $lockedCourseIds,
            'availableCourses'   => $availableCourses,
            'availableSemesters' => $availableSemesters,
            'timetableByDay'     => $timetableByDay,
            'attendanceSummary'  => $attendanceSummary,
            'internalMarks'      => $internalMarks,
            'examResults'        => $examResults,
            'examStudent'        => $examStudent,
            'batches'            => BatchMaster::orderBy('batch_name')->get(),
            'departments'        => DepartmentMaster::orderBy('name')->get(),
            'campuses'           => Campus::orderBy('name')->get(),
            'religions'          => ReligionMaster::orderBy('name')->get(),
            'nationalities'      => NationalityMaster::orderBy('name')->get(),
            'bloodGroups'        => BloodGroupMaster::orderBy('name')->get(),
        ]);
    }

    /**
     * Update student details from admin profile edit form.
     */
    function stdUpdate(Request $request, $id)
    {
        $student = StudentMaster::findOrFail($id);

        $validated = $request->validate([
            'first_name'            => 'required|string|max:100',
            'last_name'             => 'nullable|string|max:100',
            'gender'                => 'required|in:1,2',
            'dob'                   => 'nullable|date',
            'mobile_no'             => 'nullable|string|max:15',
            'mail_id'               => 'nullable|email|max:150',
            'address'               => 'nullable|string|max:500',
            'father_name'           => 'nullable|string|max:150',
            'mother_name'           => 'nullable|string|max:150',
            'guardian_name'         => 'nullable|string|max:150',
            'fr_mobile_no'          => 'nullable|string|max:15',
            'mr_mobile_no'          => 'nullable|string|max:15',
            'guardian_mobile_no'    => 'nullable|string|max:15',
            'fr_occupation'         => 'nullable|string|max:150',
            'mr_occupation'         => 'nullable|string|max:150',
            'department'            => 'nullable|integer|exists:department_masters,id',
            'batch'                 => 'nullable|integer|exists:batch_masters,id',
            'campus_id'             => 'nullable|integer|exists:campuses,id',
            'roll_no'               => 'nullable|string|max:50',
            'register_no'           => 'nullable|string|max:100',
            'university_register_no' => 'nullable|string|max:100',
            'current_year'          => 'nullable|integer|min:1|max:6',
            'admission_date'        => 'nullable|date',
            'graduation_year'       => 'nullable|integer',
            'status'                => 'nullable|string|max:50',
            'nationality'           => 'nullable|integer|exists:nationality_masters,id',
            'religion'              => 'nullable|integer|exists:religion_masters,id',
            'community'             => 'nullable|string|max:100',
            'caste'                 => 'nullable|string|max:100',
            'blood_group_id'        => 'nullable|integer|exists:blood_group_masters,id',
            'mother_tongue'         => 'nullable|string|max:100',
            'aadhar_no'             => 'nullable|string|max:20',
            'annual_income'         => 'nullable|numeric',
            'is_roman_catholic'     => 'nullable|boolean',
            'remarks'               => 'nullable|string|max:500',
        ]);

        $validated['is_roman_catholic'] = $request->boolean('is_roman_catholic');

        $student->update($validated);

        return redirect()->route('admin.student.profile', ['id' => $id, 'rollno' => $student->roll_no])
            ->with('success', 'Student details updated successfully.');
    }

    // ── Student Course CRUD ──────────────────────────────────────────────

    function stdCourseStore(Request $request, $studentId)
    {

        $student = StudentMaster::findOrFail($studentId);

        $request->validate([
            'course_ids'    => 'required|array|min:1',
            'course_ids.*'  => 'integer|exists:program_course_masters,id',
            'academic_year' => 'required|string|max:20',
            'semester_id'   => 'required|integer|exists:semesters,id',
        ]);

        $academicYear = $request->academic_year;
        $semesterId   = $request->semester_id;
        $enrolled = 0;
        $skipped  = 0;
        $course =   $request->course_ids;

        for ($i = 0; $i < count($course); $i++) {
            //check if course is already enrolled
            $check = StudentCourseInfo::where('student_id', $studentId)
                ->where('course_id', $course[$i])
                ->where('semester', $semesterId)
                ->where('academic_year', $academicYear)
                ->first();

            if ($check) {
                $skipped++;
                continue;
            }

            StudentCourseInfo::create([
                'student_id'    => $studentId,
                'course_id'     => $course[$i],
                'semester'      => $semesterId,
                'campus_id'     => $student->campus_id,
                'is_active'     => 1,
                'academic_year' => $academicYear,
                'course_status' => 'EN',
            ]);
            $enrolled++;
        }

        $msg = "{$enrolled} course(s) enrolled successfully.";
        if ($skipped) $msg .= " {$skipped} already enrolled (skipped).";

        return redirect()->route('admin.student.profile', ['id' => $studentId, 'rollno' => $student->roll_no])
            ->with('success', $msg)
            ->withFragment('tab-courses');
    }

    function stdCourseUpdate(Request $request, $studentId, $sciId)
    {
        $sci = StudentCourseInfo::where('student_id', $studentId)->findOrFail($sciId);

        // Check marks lock
        $hasFA = InterMark::where('student_id', $studentId)->where('course_id', $sci->course_id)->exists();
        $hasSA = DB::table('exam_marks_entries')
            ->where('erp_student_id', $studentId)
            ->where('erp_subject_id', $sci->course_id)
            ->exists();

        if ($hasFA || $hasSA) {
            return back()->with('error', 'Cannot modify a course that has marks recorded.')->withFragment('tab-courses');
        }

        $sci->update(['is_active' => $sci->is_active ? 0 : 1]);

        $student = StudentMaster::findOrFail($studentId);
        return redirect()->route('admin.student.profile', ['id' => $studentId, 'rollno' => $student->roll_no])
            ->with('success', 'Course status updated.')
            ->withFragment('tab-courses');
    }

    function stdCourseDestroy(Request $request, $studentId, $sciId)
    {
        $sci = StudentCourseInfo::where('student_id', $studentId)->findOrFail($sciId);

        // Check marks lock
        $hasFA = InterMark::where('student_id', $studentId)->where('course_id', $sci->course_id)->exists();
        $hasSA = DB::table('exam_marks_entries')
            ->where('erp_student_id', $studentId)
            ->where('erp_subject_id', $sci->course_id)
            ->exists();

        if ($hasFA || $hasSA) {
            return back()->with('error', 'Cannot remove a course that has marks recorded.')->withFragment('tab-courses');
        }

        $sci->delete();

        $student = StudentMaster::findOrFail($studentId);
        return redirect()->route('admin.student.profile', ['id' => $studentId, 'rollno' => $student->roll_no])
            ->with('success', 'Course enrollment removed.')
            ->withFragment('tab-courses');
    }

    /**
     * Create or reset a student's ERP login account.
     * Default password is the student's roll number.
     */
    function createStudentAccess(Request $request, $studentId)
    {
        $student = StudentMaster::findOrFail($studentId);

        if (!$student->mail_id) {
            return back()->with('error', 'Student has no email address. Cannot create login.');
        }

        // Check if user already exists for this student
        $existing = User::where('student_id', $studentId)->first()
            ?? User::where('email', $student->mail_id)->first();

        $plainPassword = $student->roll_no;

        if ($existing) {
            // Reset password and re-link
            $existing->update([
                'student_id'          => $studentId,
                'roll_no'             => $student->roll_no,
                'password'            => Hash::make($plainPassword),
                'decrypted_password'  => $plainPassword,
                'status'              => 'ACTIVE',
            ]);
            // Ensure student role
            UserHasRole::updateOrCreate(
                ['user_id' => $existing->id],
                ['role_name' => 'student']
            );
            return back()->with('success', "Login reset. Password: {$plainPassword}");
        }

        // Create new user
        $user = User::create([
            'student_id'          => $studentId,
            'name'                => $student->first_name . ' ' . $student->last_name,
            'email'               => $student->mail_id,
            'roll_no'             => $student->roll_no,
            'password'            => Hash::make($plainPassword),
            'decrypted_password'  => $plainPassword,
            'status'              => 'ACTIVE',
        ]);

        UserHasRole::create([
            'user_id'   => $user->id,
            'role_name' => 'student',
        ]);

        return back()->with('success', "Default Login created. Rollno: {$student->roll_no} | Password: {$plainPassword}");
    }

    function batchMaster()
    {
        $data = BatchMaster::get();
        return view('admin.master.batch', ['data' => $data]);
    }

    function updateAdmBatchStatus($id)
    {

        $data = BatchMaster::findOrFail($id);

        if ($data->admission_active_batch == 1) {
            BatchMaster::where('id', $id)->update([
                'admission_active_batch' => 0,
            ]);
        } else {
            BatchMaster::where('admission_active_batch', 1)->update([
                'admission_active_batch' => 0,
            ]);
            BatchMaster::where('id', $id)->update([
                'admission_active_batch' => 1,
            ]);
        }
        return redirect()->back()->with('success', 'Done');
    }

    function addBatch(Request $request)
    {
        $request->validate([
            'batch_name' => 'required|max_digits:4|min_digits:4',
            'fees' => 'required',

        ]);

        $check = BatchMaster::where('batch_name', $request->batch_name)->first();
        if ($check == null) {
            $rec = new BatchMaster();
            $rec->batch_name = $request->batch_name;
            $rec->admn_fee_amount = $request->fees;
            $rec->save();

            return redirect()->back()->with('success', 'Done');
        } else {
            return redirect()->back()->with('success', 'Batch already in list');
        }
    }

    function hourMaster()
    {
        $data = HourMaster::get();
        return view('admin.master.hour', ['data' => $data]);
    }

    function addHour(Request $request)
    {
        $request->validate([
            'hour' => 'required',

        ]);

        $check = HourMaster::where('title', $request->hour)->first();
        if ($check == null) {
            $rec = new HourMaster();
            $rec->title = $request->hour;
            $rec->save();

            return redirect()->back()->with('success', 'Done');
        } else {
            return redirect()->back()->with('success', 'Item already in list');
        }
    }

    function delHour($id)
    {
        HourMaster::findOrFail($id)->delete();
        return redirect()->back()->with('success', 'Done');
    }

    function bloodGroupMaster()
    {
        $data = BloodGroupMaster::get();
        return view('admin.master.blood-group', ['data' => $data]);
    }

    function addBloodGroup(Request $request)
    {
        $request->validate([
            'name' => 'required',
        ]);

        $check = BloodGroupMaster::where('name', $request->name)->first();
        if ($check == null) {
            $rec = new BloodGroupMaster();
            $rec->name = $request->name;
            $rec->save();

            return redirect()->back()->with('success', 'Done');
        } else {
            return redirect()->back()->with('success', 'Item already in list');
        }
    }

    function campusMaster()
    {
        $data = Campus::get();
        return view('admin.master.campus', ['data' => $data]);
    }

    function paperTypeMaster()
    {
        $data = PaperTypeMaster::orderBy('name')->get();
        return view('admin.master.paper-type', ['data' => $data]);
    }

    function addPaperType(Request $request)
    {
        $request->validate([
            'name' => 'required',
        ]);

        $check = PaperTypeMaster::where('name', $request->name)->first();
        if ($check == null) {
            $rec = new PaperTypeMaster();
            $rec->name = $request->name;
            $rec->save();
            return redirect()->back()->with('success', 'Done');
        } else {
            return redirect()->back()->with('success', 'Item already in list');
        }
    }

    function delPaperType($id)
    {
        PaperTypeMaster::findOrFail($id)->delete();
        return redirect()->back()->with('success', 'Deleted');
    }

    function cognitiveLvl()
    {
        $data = CognitiveLevelMaster::get();
        return view('admin.master.cognitive-lvl', ['data' => $data]);
    }

    function addCognitiveLvl(Request $request)
    {
        $request->validate([
            'short_name' => 'required',
            'full_name' => 'required',
        ]);

        $check = CognitiveLevelMaster::where('fullname', $request->full_name)->first();
        if ($check == null) {
            $rec = new CognitiveLevelMaster();
            $rec->shortname = $request->short_name;
            $rec->fullname = $request->full_name;
            $rec->save();

            return redirect()->back()->with('success', 'Done');
        } else {
            return redirect()->back()->with('success', 'Item already in list');
        }
    }

    function delCogLvl($id)
    {
        CognitiveLevelMaster::findOrFail($id)->delete();
        return redirect()->back()->with('success', 'Done');
    }

    function updateCognitiveLvl(Request $request, $id)
    {
        $request->validate([
            'short_name' => 'required',
            'full_name' => 'required',
        ]);

        CognitiveLevelMaster::where('id', $id)->update([
            'shortname' => $request->short_name,
            'fullname' => $request->full_name,
        ]);

        return redirect()->back()->with('success', 'Updated');
    }


    function departmentMaster()
    {
        $data = DepartmentMaster::with('campusmaster')->latest()->get();
        return view('admin.master.department', ['data' => $data]);
    }


    function roomTypeMaster()
    {
        $data = RoomMaster::latest()->get();
        return view('admin.master.rooms', ['data' => $data]);
    }

    function addRoomTypeMaster(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
        ]);

        $rec = new RoomMaster();
        $rec->title = ucfirst($request->title);
        $rec->save();
        return redirect()->back()->with('success', 'Done');
    }

    function updateRoomTypeMaster(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
        ]);

        RoomMaster::where('id', $request->id)->update([
            'title' => ucfirst($request->title)
        ]);

        return redirect()->back()->with('success', 'Update Done');
    }

    function streamMaster()
    {
        $data = ProgramMaster::latest()->get();
        return view('admin.master.stream-master', ['data' => $data]);
    }

    function addStreamMaster(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
        ]);

        $rec = new ProgramMaster();
        $rec->title = ucfirst($request->title);
        $rec->save();
        return redirect()->back()->with('success', 'Done');
    }


    function programMaster() //campus course combination
    {
        $campuses = Campus::latest()->get();
        $programs = ProgramMaster::latest()->get();
        $data = MainProgram::with('campus')->get();
        return view('admin.master.programs', ['data' => $data, 'campuses' => $campuses, 'programs' => $programs]);
    }

    function addStreamCombination(Request $request)
    {
        $request->validate([
            'campus' => 'required',
            'streamtype' => 'required',
        ]);

        $prg =  ProgramMaster::find($request->streamtype);

        $rec = new MainProgram();
        $rec->campus_id = $request->campus;
        $rec->program_id = $prg->id;
        $rec->name = ucfirst($prg->title);
        $rec->save();
        return redirect()->back()->with('success', 'Done');
    }

    function programGroup()
    {
        $data = ProgramGroup::with([
            'campus',
            'programInfo',
        ])->get();
        return view('admin.master.program-group', ['data' => $data]);
    }

    function updateProgramGroup(Request $request)
    {

        return $record = ProgramGroup::findOrFail($request->id);
    }

    //lecture hall

    function lectureHalls()
    {
        $data = LectureHallMaster::with([
            'acblockmaster:id,title',
            'roomtypemaster:id,title'
        ])->get();

        return view('admin.master.lecture-halls', ['data' => $data]);
    }

    function addLectureHall(Request $request)
    {
        $request->validate([
            'acblock_id' => 'required',
            'title' => 'required|string|max:190',
            'roomtype_id' => 'required',
        ]);

        $rec = new LectureHallMaster();
        $rec->acblock_id = $request->acblock_id;
        $rec->roomtype_id = $request->roomtype_id;
        $rec->title = $request->title;
        $rec->save();

        return redirect()->back()->with('succes', 'Done');
    }


    function semesters()
    {
        $data = Semester::latest()->get();
        return view('admin.master.semesters', ['data' => $data]);
    }

    function religionMaster()
    {
        $data = ReligionMaster::latest()->get();
        return view('admin.master.religion', ['data' => $data]);
    }

    function addReligionMaster(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $rec = new ReligionMaster();
        $rec->name = ucfirst($request->name);
        $rec->save();
        return redirect()->back()->with('success', 'Done');
    }

    function delReligion($id)
    {
        ReligionMaster::find($id)->delete();
        return redirect()->back()->with('success', 'Deleted');
    }

    function deanery(Request $request)
    {

        if (!empty($request->campus)) {
            $campus_id = $request->campus;
            $deanery = Deanery::with([
                'program.campus',
                'deanerydeptpivot.department:id,name'
            ])->whereHas('program.campus', function ($q) use ($campus_id) {
                $q->where('id', $campus_id);
            })->latest()->get();
        } else {
            $deanery = Deanery::with([
                'program.campus',
                'deanerydeptpivot.department:id,name'
            ])->latest()->get();
        }

        $programs = MainProgram::with('campus')->get();
        return view('admin.master.deanery', compact('deanery', 'programs'));
    }

    function addDeanery(Request $request)
    {
        $request->validate([
            'program_id' => 'required',
            'title' => 'required'
        ]);

        $rec = new Deanery();
        $rec->program_id = $request->program_id;
        $rec->slug = Str::slug($request->title);
        $rec->title = $request->title;
        $rec->save();
        return redirect()->back()->with('success', 'Done');
    }

    function academicDept()
    {
        $data = AcademicDepartment::with([
            'campus',
            'program',
            'deptmaster'
        ])->latest()->get();
        return view('admin.master.academic-dept', ['data' => $data]);
    }

    // function addAcademicDept(Request $request)
    // {

    //     $request->validate([
    //         'batch' => 'required',
    //         'program_id' => 'required',
    //         'short_name' => 'required|string|max:255',
    //         'full_name' => 'required|string|max:255'
    //     ]);

    //     $record = MainProgram::find($request->program_id);

    //     $rec = new AcademicDepartment();
    //     $rec->campus_id = $record->campus_id;
    //     $rec->session_id = $request->batch;
    //     $rec->main_program_id = $request->program_id;
    //     $rec->short_name = Str::slug($request->full_name);
    //     $rec->name = $request->full_name;
    //     $rec->save();



    //     return redirect()->back()->with('success', 'Done');
    // }


    function connectAcademicToDept(Request $request)
    {
        $request->validate([
            'dept' => 'required',
            'id' => 'required'
        ]);

        AcademicDepartment::where('id', $request->id)->update([
            'dept_id' => $request->dept
        ]);
        return redirect()->back()->with('success', 'Connected Successfully');
    }


    function bankAccounts()
    {
        $data = CollegeBankAccount::latest()->get();
        return view('admin.accounts.banks', ['data' => $data]);
    }

    function addBankInfo(Request $request)
    {
        $request->validate([
            'acclabel' => 'required',
            'accname' => 'required',
            'accno' => 'required',
            'bank' => 'required',
            'ifsc' => 'required',
            'branch_name' => 'required',
        ]);

        if (!empty($request->doc)) {
            $doc = $request->doc;
            $filename = StaticController::s3_file_uploader($doc, 'collegebankaccounts');
        } else {
            $filename = null;
        }

        $rec = new CollegeBankAccount();
        $rec->acc_label = $request->acclabel;
        $rec->acc_no = $request->accno;
        $rec->acc_name = $request->accname;
        $rec->bank_name = $request->bank;
        $rec->ifsc = $request->ifsc;
        $rec->branch = $request->branch_name;
        $rec->doc = $filename;
        $rec->save();

        return redirect()->back()->with('success', 'Done');
    }


    function updateBankInfo(Request $request)
    {
        $request->validate([
            'acclabel' => 'required',
            'accname' => 'required',
            'accno' => 'required',
            'bank' => 'required',
            'ifsc' => 'required',
            'branch_name' => 'required',
        ]);
        $data = CollegeBankAccount::find($request->id);

        if (!empty($request->doc)) {
            $doc = $request->doc;
            $filename = StaticController::s3_file_uploader($doc, 'collegebankaccounts');
        } else {
            if ($data->doc == null) {
                $filename = null;
            } else {
                $filename = $data->doc;
            }
        }

        CollegeBankAccount::where('id', $request->id)->update([
            'acc_label' => $request->acclabel,
            'acc_no' => $request->accno,
            'acc_name' => $request->accname,
            'bank_name' => $request->bank,
            'ifsc' => $request->ifsc,
            'branch' => $request->branch_name,
            'doc' => $filename,
        ]);
        return redirect()->back()->with('success', 'Update Success');
    }


    function feeStructure(Request $request)
    {
        $query = FeesStructure::with([
            'program.campus',
            'batch',
            'feepvthead.head.bankmaster',
            'feepvthead.head:id,head_name,bank_acc_id',
            'feecoursemaster:id,name',
            'programspivot.studentprogram',
        ]);

        if (!empty($request->keyword)) {
            $keyword = $request->keyword;
            $searchValues = preg_split('/\s+/', $keyword, -1, PREG_SPLIT_NO_EMPTY);
            $query->whereHas('feecoursemaster', function ($q) use ($searchValues) {
                foreach ($searchValues as $value) {
                    $q->where('name', 'LIKE', "%$value%");
                }
            });
        }

        if (!empty($request->batch_id)) {
            $query->where('batch_id', $request->batch_id);
        }

        $data = $query->latest()->get();


        return view('admin.accounts.fee-structure', ['data' => $data]);
    }


    function addFeeStructure(Request $request)
    {

        $request->validate([
            'program' => 'required',
            'batch' => 'required',
            'course' => 'required',
            'heads' => 'required|array|min:1',
            'amounts' => 'required|array|min:1',
            'reminder_date' => 'required',
            'due_date' => 'required',
            'quarter_title' => 'required|string|max:255',
            'applicable_year' => 'required',
            'yearly_pay_order' => 'required',


        ]);


        // Check for duplicate fee structure
        $duplicate = FeesStructure::where('batch_id', $request->batch)
            ->where('program_id', $request->program)
            ->where('course_name', $request->course)
            ->where('std_current_year', $request->applicable_year)
            ->where('yearly_pay_order', $request->yearly_pay_order)
            ->first();

        if ($duplicate) {
            return redirect()->back()->with('error', 'A fee structure with these specifications already exists. Please check batch, program, course, year, and payment order.');
        }

        $rec = new FeesStructure();
        $rec->program_id = $request->program; //ug pg
        $rec->batch_id = $request->batch; //batch master: id
        $rec->course_name = $request->course; //fee course master: id
        $rec->reminder_date = $request->reminder_date;
        $rec->due_date = $request->due_date;
        $rec->quarter_title = $request->quarter_title;
        $rec->yearly_pay_order = $request->yearly_pay_order;
        $rec->std_current_year = $request->applicable_year;
        $rec->save();

        $feeStructureId = $rec->id;
        $heads = $request->heads;
        $amount = $request->amounts;

        //saviing heads
        for ($i = 0; $i < count($heads); $i++) {
            $pvt = new FeeStructureHasHead();
            $pvt->fee_structure_id = $feeStructureId;
            $pvt->fee_head_id = $heads[$i];
            $pvt->amount = $amount[$i];
            $pvt->save();
        }

        $course = $request->course;
        $progs = FeeStructureGroup::where('fee_course_master_id', $course)->get();
        //connect course student programs
        for ($i = 0; $i < count($progs); $i++) {
            $pg = new FeeStructureHasManyProgram();
            $pg->fee_structure_id = $rec->id;
            $pg->std_program_id = $progs[$i]->student_program_id; //direct student_program_id
            $pg->save();
        }

        return redirect()->back()->with('success', 'Done');
    }

    function cloneFeeStructure(Request $request, $id)
    {
        $request->validate([
            'batch_id'      => 'required|integer',
            'reminder_date' => 'required|date',
            'due_date'      => 'required|date',
        ]);

        $original = FeesStructure::with(['feepvthead', 'programspivot'])->findOrFail($id);

        // Clone the fee structure with new batch and dates
        $clone = $original->replicate();
        $clone->batch_id      = $request->batch_id;
        $clone->reminder_date = $request->reminder_date;
        $clone->due_date      = $request->due_date;
        $clone->is_payable    = 0;
        $clone->save();

        // Clone fee heads
        foreach ($original->feepvthead as $head) {
            FeeStructureHasHead::create([
                'fee_structure_id' => $clone->id,
                'fee_head_id'      => $head->fee_head_id,
                'amount'           => $head->amount,
            ]);
        }

        // Clone linked programs
        foreach ($original->programspivot as $prog) {
            FeeStructureHasManyProgram::create([
                'fee_structure_id' => $clone->id,
                'std_program_id'   => $prog->std_program_id,
            ]);
        }

        return redirect()->back()->with('success', 'Fee structure cloned successfully for the new batch.');
    }

    function cloneAllFeeStructures(Request $request)
    {
        $request->validate([
            'source_batch_id' => 'required|integer',
            'batch_id'        => 'required|integer|different:source_batch_id',
            'reminder_date'   => 'required|date',
            'due_date'        => 'required|date|after_or_equal:reminder_date',
        ]);

        $structures = FeesStructure::with(['feepvthead', 'programspivot'])
            ->where('batch_id', $request->source_batch_id)
            ->get();

        if ($structures->isEmpty()) {
            return redirect()->back()->with('error', 'No fee structures found for the selected source batch.');
        }

        $count = 0;
        foreach ($structures as $original) {
            $clone                = $original->replicate();
            $clone->batch_id      = $request->batch_id;
            $clone->reminder_date = $request->reminder_date;
            $clone->due_date      = $request->due_date;
            $clone->is_payable    = 0;
            $clone->save();

            foreach ($original->feepvthead as $head) {
                FeeStructureHasHead::create([
                    'fee_structure_id' => $clone->id,
                    'fee_head_id'      => $head->fee_head_id,
                    'amount'           => $head->amount,
                ]);
            }

            foreach ($original->programspivot as $prog) {
                FeeStructureHasManyProgram::create([
                    'fee_structure_id' => $clone->id,
                    'std_program_id'   => $prog->std_program_id,
                ]);
            }

            $count++;
        }

        return redirect()->back()->with('success', "{$count} fee structure(s) cloned successfully to the new batch.");
    }

    function unlinkStdProgram($id)
    {
        FeeStructureHasManyProgram::find($id)->delete();
        return redirect()->back()->with('success', 'Done');
    }

    function unlinkStdProgramDirect($id)
    {
        return  FeeStructureGroup::find($id)->first();
        FeeStructureHasManyProgram::where('fee_structure_group_id', $id)->first();
        return redirect()->back()->with('success', 'Done');
    }


    function addCourseMasterGroup(Request $request)
    {

        $request->validate([
            'progs' => 'required|array|min:1',
        ]);
        $courseMasterId =  $request->coursemasterId;   //single Id 9 (Course Master Id)
        $progs = $request->progs; //multiple student program ids [1,2,3,4,5]


        // Add to FeeStructureGroup, associate course master with student programs directly, prevent duplicates
        for ($i = 0; $i < count($progs); $i++) {
            // Only add if not already present
            $exists = FeeStructureGroup::where('fee_course_master_id', $courseMasterId)
                ->where('student_program_id', $progs[$i])
                ->exists();
            if (!$exists) {
                $rec = new FeeStructureGroup();
                $rec->fee_course_master_id = $courseMasterId;
                $rec->student_program_id = $progs[$i];
                $rec->save();
            }


            //fetch in Fees_structure this course_name: id exist
            $feestructure_data =   FeesStructure::where('course_name', $courseMasterId)->get();

            if ($feestructure_data != null) {

                // If fee structure exists, link the student program to it in FeeStructureHasManyProgram
                foreach ($feestructure_data as $fs) {
                    $fs_id = $fs->id;
                    // Check if already linked to avoid duplicates
                    $existsLink = FeeStructureHasManyProgram::where('fee_structure_id', $fs_id)
                        ->where('std_program_id', $progs[$i])
                        ->exists();

                    if (!$existsLink) {
                        $link = new FeeStructureHasManyProgram();
                        $link->fee_structure_id = $fs_id;
                        $link->std_program_id = $progs[$i];
                        $link->save();
                    }
                }
            }
        }

        return redirect()->back()->with('success', 'Connected Successfully');
    }

    function feeStructureGroupUnlink(int $id)
    {
        $data = FeeStructureGroup::findOrFail($id);

        $fee_course_master_id = $data->fee_course_master_id;
        $student_program_id = $data->student_program_id;
        //Check if any fee structure exists for the course master id
        $feeStructures = FeesStructure::where('course_name', $fee_course_master_id)->get();
        //unlink from FeeStructureHasManyProgram where feeStructures->id and student_program_id
        foreach ($feeStructures as $fs) {
            FeeStructureHasManyProgram::where('fee_structure_id', $fs->id)
                ->where('std_program_id', $student_program_id)
                ->delete();
        }

        FeeStructureGroup::findOrFail($id)->delete();
        return redirect()->back()->with('success', 'Deleted');
    }

    function feeStructureStdProgramUnlink(int $id)
    {
        FeeStructureHasManyProgram::findOrFail($id)->delete();

        if (request()->ajax()) {
            return response()->json(['success' => true, 'message' => 'Program unlinked successfully.']);
        }
        return redirect()->back()->with('success', 'Deleted');
    }

    function connectFeesStructureSingleWithStdProgram(Request $request)
    {
        $request->validate([
            'selected_program' => 'required',
        ]);

        $feeStructureId = $request->id;
        $stdProgramId = $request->selected_program;
        // Check if already linked to avoid duplicates
        $exists = FeeStructureHasManyProgram::where('fee_structure_id', $feeStructureId)
            ->where('std_program_id', $stdProgramId)
            ->exists();

        if (!$exists) {
            $rec = new FeeStructureHasManyProgram();
            $rec->fee_structure_id = $feeStructureId;
            $rec->std_program_id = $stdProgramId;
            $rec->save();

            // Load the program details for AJAX response
            if ($request->ajax()) {
                $program = $rec->studentprogram()->with('campusmaster')->first();
                return response()->json([
                    'success' => true,
                    'message' => 'Student program linked successfully.',
                    'program' => [
                        'id' => $rec->id,
                        'code' => $program->code ?? '',
                        'name' => $program->name ?? '',
                        'campus' => $program->campusmaster->name ?? 'No Campus'
                    ]
                ]);
            }
            return redirect()->back()->with('success', 'Student program linked to the fee structure successfully.');
        } else {
            if ($request->ajax()) {
                return response()->json(['success' => false, 'message' => 'This student program is already linked.'], 422);
            }
            return redirect()->back()->with('error', 'This student program is already linked to the fee structure.');
        }
    }

    function linkProgramtoFeeStructure(Request $request)
    {
        $request->validate([
            'progs' => 'required|array|min:1',
        ]);

        $progs = $request->progs; // student program ids
        for ($i = 0; $i < count($progs); $i++) {
            // Check if already linked to avoid duplicates
            $exists = FeeStructureHasManyProgram::where('fee_structure_id', $request->feeStructureId)
                ->where('std_program_id', $progs[$i])
                ->exists();

            if (!$exists) {
                $rec = new FeeStructureHasManyProgram();
                $rec->fee_structure_id = $request->feeStructureId;
                $rec->std_program_id = $progs[$i];
                $rec->save();
            }
        }
        return redirect()->back()->with('success', 'Student Programs Linked to Fee Structure');
    }

    function feeHeads()
    {
        $data = FeeHead::with('bankmaster')->latest()->get();
        return view('admin.accounts.fee-heads', ['data' => $data]);
    }

    function addFeeHead(Request $request)
    {
        $request->validate([
            'head_name' => 'required|string|max:255',
            'bank' => 'required'
        ]);
        $rec = new FeeHead();
        $rec->head_name = $request->head_name;
        $rec->bank_acc_id = $request->bank;

        $rec->save();
        return redirect()->back()->with('success', 'Done');
    }

    function updateFeeHead(Request $request)
    {
        $request->validate([
            'head_name' => 'required|string|max:255',
        ]);

        $data =  FeeHead::find($request->id);

        if (!empty($request->bank)) {
            $bank = $request->bank;
        } else {
            $bank = $data->bank_acc_id;
        }

        FeeHead::where('id', $request->id)->update([
            'head_name' => $request->head_name,
            'bank_acc_id' => $bank
        ]);

        return redirect()->back()->with('success', 'Update Done');
    }

    function delFeeHead($id)
    {
        FeeHead::findOrFail($id)->delete();
        return redirect()->back()->with('success', 'Deleted');
    }

    function delFeeHeadPvt($id)
    {
        FeeStructureHasHead::findOrFail($id)->delete();
        return redirect()->back()->with('success', 'Deleted');
    }


    function updateHeadSingle(Request $request)
    {


        $request->validate([
            'amount' => 'required',
        ]);

        FeeStructureHasHead::where('id', $request->id)->update([
            'amount' => $request->amount
        ]);
        return redirect()->back()->with('success', 'Updated');
    }

    function updateFeeStructure(Request $request)
    {
        $request->validate([
            'program' => 'required',
            'batch' => 'required',
        ]);
        $id = $request->id;

        FeesStructure::where('id', $id)->update([
            'program_id' => $request->program,
            'batch_id' => $request->batch,
            'reminder_date' => $request->reminder_date,
            'due_date' => $request->due_date,
        ]);

        $amount = $request->amounts;
        $heads = $request->heads;
        $feeStructureId = $id;
        //saviing heads if added
        if (!empty($heads) && is_array($heads) && !empty($amount) && is_array($amount)) {
            for ($i = 0; $i < count($heads); $i++) {
                // Skip if head_id is null or amount is not set
                if (empty($heads[$i]) || !isset($amount[$i])) {
                    continue;
                }

                $check = FeeStructureHasHead::where('fee_structure_id', $feeStructureId)->where('fee_head_id', $heads[$i])->count();
                if ($check == 0) {
                    $pvt = new FeeStructureHasHead();
                    $pvt->fee_structure_id = $id;
                    $pvt->fee_head_id = $heads[$i];
                    $pvt->amount = $amount[$i];
                    $pvt->save();
                }
            }
        }



        return redirect()->back()->with('success', 'Fee Structure Updated');
    }

    function feeCourseMaster(Request $request)
    {
        if (!empty($request->coursemaster)) {
            $data = FeeCourseMaster::with('feegroups.programgroup')->where('id', $request->coursemaster)->latest()->get();
        } else {
            $data = FeeCourseMaster::with('feegroups.programgroup')->latest()->get();
        }
        $allcourses = FeeCourseMaster::latest()->get();

        return view('admin.accounts.fee-course-master', ['data' => $data, 'allcourses' => $allcourses]);
    }

    function addCourseFeeMaster(Request $request)
    {

        $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $rec = new FeeCourseMaster();
        $rec->name = $request->name;
        $rec->save();

        return redirect()->back()->with('success', 'Done');
    }

    function updateCourseFeeMaster(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
        ]);

        FeeCourseMaster::where('id', $request->id)->update([
            'name' => $request->name,
        ]);
        return redirect()->back()->with('success', 'Done');
    }

    function updateFeeStructureStatus($id)
    {

        $data =  FeesStructure::findOrFail($id);

        if ($data->is_payable == 1) {
            FeesStructure::where('id', $id)->update([
                'is_payable' => 0,
            ]);
            $newStatus = 0;
        } else {
            FeesStructure::where('id', $id)->update([
                'is_payable' => 1,
            ]);
            $newStatus = 1;
        }

        if (request()->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Status updated successfully.',
                'is_payable' => $newStatus
            ]);
        }

        return redirect()->back()->with('success', 'Status Updated');
    }

    function delFeeCourseMaster($id)
    {
        FeeCourseMaster::findOrFail($id)->delete();
        return redirect()->back()->with('success', 'Deleted');
    }

    function deleteFeeStructure($id)
    {
        FeeStructureHasHead::where('fee_Structure_id', $id)->delete();
        FeeStructureHasManyProgram::where('fee_Structure_id', $id)->delete();
        FeesStructure::findOrFail($id)->delete();
        // OR permanent delete
        // $fS->forceDelete();

        return redirect()
            ->back()
            ->with('success', 'Fee Structure deleted successfully.');
    }

    function facultyMaster()
    {
        $data = Faculty::with([
            'nationality'
        ])->get();
        return view('admin.academics.faculty', ['data' => $data]);
    }

    function updateFaculty(Request $request)
    {

        Faculty::where('id', $request->id)->update([
            'USER_CODE' => $request->empid,
            'FIRST_NAME' => $request->fname,
            'LAST_NAME' => $request->lname,
            'DOB' => $request->dob,
            'GENDER' => $request->gender,
            'MOBILE_NO' => $request->mobile_no,
            'MAIL_ID' => $request->mail_id,
        ]);

        return redirect()->back()->with('success', 'Updated');
    }

    function userList()
    {
        $data = User::with('menupermission')
            ->with('userroletype')
            ->with('campuspermission.campus:id,name')
            ->where('id', '!=', 1)
            ->latest()
            ->get();
        return view('admin.user-manager.access-management', ['data' => $data]);
    }

    function createNewUser(Request $request)
    {


        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:6',
        ]);

        $rec = new User();
        $rec->name = $request->name;
        $rec->email = $request->email;
        $rec->password = Hash::make($request->password);
        $rec->status = 'ACTIVE';
        $rec->otp_verification = 1;
        $rec->save();

        $userId = $rec->id;
        if ($request->user_type == 'super-admin') {
            $roles = MenuMaster::pluck('id')->toArray();
            for ($i = 0; $i < count($roles); $i++) {
                $permission = new UserMenuPermission();
                $permission->user_id = $userId;
                $permission->menu_master_id = $roles[$i];
                $record = MenuMaster::find($roles[$i]);
                $permission->permission_name = $record->slug;
                $permission->save();
            }
        } else {

            //check CAMPUS ASSIGNMENT
            if (!empty($request->campus)) {
                $campus = new UserCampusSetting();
                $campus->user_id = $userId;
                $campus->campus_id = $request->campus;
                $campus->save();
            }
        }

        //adding role_type
        $userType = new UserHasRole();
        $userType->user_id = $userId;
        $userType->role_name = $request->user_type; //default to admin
        $userType->save();

        return redirect()->back()->with('success', 'New User Created');
    }

    function updatePermission(Request $request)
    {

        $request->validate([
            'roles' => 'required|array|min:1',
            'user_id' => 'required',
        ]);

        $userId = $request->user_id;

        $roles = $request->roles;

        for ($i = 0; $i < count($roles); $i++) {

            $duplicateCheck = UserMenuPermission::where('user_id', $userId)->where('menu_master_id', $roles[$i])->first();
            if ($duplicateCheck == null) {

                $record = MenuMaster::find($roles[$i]);

                $permission = new UserMenuPermission();
                $permission->user_id = $userId;
                $permission->menu_master_id = $roles[$i];
                $permission->permission_name = $record->slug;
                $permission->save();
            }
        }

        return redirect()->back()->with('success', 'Permissions Updated');
    }

    function removeUserPermission($id)
    {
        UserHasPermission::find($id)->delete();
        return redirect()->back()->with('success', 'Permission Removed');
    }

    function latefee()
    {
        $data = LateFee::find(1);
        return view('admin.accounts.latefee', ['data' => $data]);
    }

    function smsData($msgid)
    {
        $data = StaticController::fetchMessageData($msgid);
        return $data;
    }

    function deleteUserAccess($id)
    {
        User::findOrFail($id)->delete();
        //delete user campus setting
        UserCampusSetting::where('user_id', $id)->delete();
        //delete user menu permission
        UserMenuPermission::where('user_id', $id)->delete();
        //delete user role
        UserHasRole::where('user_id', $id)->delete();
        //delete user
        return redirect()->back()->with('success', 'User Deleted');
    }

    function userTypes()
    {
        $data = UserType::latest()->get();
        return view('admin.user-manager.user-types', ['data' => $data]);
    }

    function addUserType(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $slug = Str::slug($request->name);

        UserType::where('slug', $slug)->first();
        $check = UserType::where('slug', $slug)->first();
        if ($check !== null) {
            return redirect()->back()->with('error', 'User type already exists');
        } else {
            $rec = new UserType();
            $rec->name = $request->name;
            $rec->slug = $slug;
            $rec->is_active = 1;
            $rec->save();
        }

        return redirect()->back()->with('success', 'Done');
    }

    function roleMaster()
    {
        $data = RoleMaster::latest()->get();
        return view('admin.user-manager.role-master', ['data' => $data]);
    }

    function addRole(Request $request)
    {
        $request->validate([
            'role_name' => 'required|string|max:255',
            'description' => 'nullable|string|max:255',
        ]);

        $slug = Str::slug($request->role_name);
        $check = RoleMaster::where('slug', $slug)->first();
        if ($check !== null) {
            return redirect()->back()->with('error', 'Role already exists');
        }

        $rec = new RoleMaster();
        $rec->role_name = $request->role_name;
        $rec->slug = $slug;
        $rec->description = $request->description;
        $rec->is_active = 1;
        $rec->save();

        return redirect()->back()->with('success', 'Role added successfully');
    }

    function updateRole(Request $request, $id)
    {
        $request->validate([
            'role_name' => 'required|string|max:255',
            'description' => 'nullable|string|max:255',
        ]);

        $role = RoleMaster::findOrFail($id);
        $slug = Str::slug($request->role_name);
        $check = RoleMaster::where('slug', $slug)->where('id', '!=', $id)->first();
        if ($check !== null) {
            return redirect()->back()->with('error', 'Role name already exists');
        }

        $role->role_name = $request->role_name;
        $role->slug = $slug;
        $role->description = $request->description;
        $role->is_active = $request->is_active ?? 1;
        $role->save();

        return redirect()->back()->with('success', 'Role updated successfully');
    }

    function deleteRole($id)
    {
        RoleMaster::findOrFail($id)->delete();
        return redirect()->back()->with('success', 'Role deleted successfully');
    }

    function menuAccessTypes()
    {

        $data = MenuMaster::latest()->get();
        return view('admin.user-manager.menu-rights', ['data' => $data]);
    }

    function addMenuAccessType(Request $request)
    {

        $request->validate([
            'name' => 'required|string|max:255',
            'module_type' => 'required|string|max:255',

        ]);
        $slug = Str::slug($request->name);
        MenuMaster::where('slug', $slug)->first();
        $check = MenuMaster::where('slug', $slug)->first();
        if ($check !== null) {
            return redirect()->back()->with('error', 'Permission already exists');
        }
        $rec = new MenuMaster();
        $rec->menu_name = $request->name;
        $rec->slug = $slug;
        $rec->module_type = $request->module_type;
        $rec->save();
        //add permission to super admin
        $superAdmins = User::whereHas('userroletype', function ($q) {
            $q->orWhere('role_name', 'super-admin');
            $q->orWhere('role_name', 'principal');
        })->get();

        foreach ($superAdmins as $sa) {
            $permission = new UserMenuPermission();
            $permission->user_id = $sa->id;
            $permission->menu_master_id = $rec->id;
            $permission->permission_name = $rec->slug;
            $permission->save();
        }

        return redirect()->back()->with('success', 'Done');
    }

    function deleteUserPermission($id)
    {
        UserMenuPermission::findOrFail($id)->delete();
        return redirect()->back()->with('success', 'Deleted');
    }


    function studentProgramMaster()
    {
        $data = StudentProgramTypeMaster::with('stdprograms')->latest()->get();

        return view('admin.master.std-program-type', ['data' => $data]);
    }

    function studentProgramTypeMultiUpdate(Request $request)
    {
        $request->validate([
            'program_type' => 'required',
            'programs' => 'required|array|min:1',
        ]);

        $programTypeId = $request->program_type;
        $programIds = $request->programs;

        for ($i = 0; $i < count($programIds); $i++) {
            StudentProgram::where('id', $programIds[$i])->update([
                'program_type' => $programTypeId
            ]);
        }

        return redirect()->back()->with('success', 'Student Programs updated successfully');
    }
}
