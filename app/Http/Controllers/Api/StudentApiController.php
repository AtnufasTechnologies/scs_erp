<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\BatchMaster;
use App\Models\BloodGroupMaster;
use App\Models\Campus;
use App\Models\CiaMark;
use App\Models\DepartmentMaster;
use App\Models\ExamSystem\ExamStudent;
use App\Models\ExamSystem\Result as ExamSystemResult;
use App\Models\InterMark;
use App\Models\NationalityMaster;
use App\Models\ProgramCourseMaster;
use App\Models\ReligionMaster;
use App\Models\Semester;
use App\Models\StudentAttendance;
use App\Models\StudentCourseInfo;
use App\Models\StudentMaster;
use App\Models\User;
use App\Models\UserHasRole;
use App\Services\StudentTimetableService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class StudentApiController extends Controller
{
    function login(Request $request)
    {
        $request->validate([
            'rollno' => 'required',
            'password' => 'required'
        ]);

        $user = User::where('roll_no', $request->rollno)->where('status', 'ACTIVE')->first();

        if ($user) {

            if (Hash::check($request->password, $user->password)) {

                //checking user role
                $userRole = UserHasRole::where('user_id', $user->id)->first();

                if ($userRole->role_name == 'student') {
                    $data['user'] = $user;
                    $data['user_role'] = $userRole->role_name;
                } elseif ($userRole->role_name == 'alumni') {
                    $data['user'] = $user;
                    $data['user_role'] = $userRole->role_name;
                } else {
                    return response()->json([
                        'status' => false,
                        'message' => 'Unauthorized Login',
                    ], 401);
                }
                return response()->json([
                    'status' => true,
                    'message' => 'Login Successful',
                    'data' => $data,
                ], 200);
            } else {
                return response()->json([
                    'status' => false,
                    'message' => 'Password Incorrect',

                ], 401);
            }
        } else {
            return response()->json([
                'status' => false,
                'message' => 'User Not Found',

            ], 404);
        }
    }

    function stdprofile(Request $request)
    {
        $request->validate([
            'id' => 'required',
        ]);
        $id = $request->id;

        $data = StudentMaster::where('id', $id)->with([
            'religionmaster:id,name',
            'deptmaster:id,department_code,name',
            'campusmaster:id,slug,name',
            'nationalitymaster:id,name',
            'usertype:id,name',
            'bloodgroup',
            'batchmaster:id,batch_name',
            'stdprogramenrolled',
            'feepayment.feepaymentinfo:id,quarter_title',
            'feepayment.gatewaytype',
            'academicpathway',
            'degreetrack',
            'singleselection',
            'subjectmaster'
        ])->firstOrFail();
        $studentCourses = StudentCourseInfo::with([
            'coursemaster'
        ])
            ->where('student_id', $id)->get();
        // Fetch student's courses with semester and course-type relations
        $studentCourses = StudentCourseInfo::with([
            'coursemaster.semestermaster:id,title',
            'coursemaster.coursetypemaster:id,title',
        ])
            ->where('student_id', $id)
            ->orderByDesc('id')
            ->get()
            ->unique(fn($c) => ($c->semester ?? $c->coursemaster?->semester_id ?? 'na') . '_' . $c->course_id)
            ->values();

        // Build semester ID → title map for grouping
        $semesterMap = Semester::pluck('title', 'id')->toArray();

        // Group courses by the semester stored in student_course_infos (set during enrollment)
        $coursesBySemester = $studentCourses
            ->sortBy(fn($c) => $c->semester ?? $c->coursemaster?->semester_id ?? 999)
            ->groupBy(function ($c) use ($semesterMap) {
                $semId = $c->semester ?? $c->coursemaster?->semester_id;
                return $semesterMap[$semId] ?? ('Semester ' . ($semId ?? '?'));
            });

        $faSegregatedMarks =  CiaMark::where('STUDENT_ID', $id)->with([
            'studentcourseinfo.coursemaster:id,course_title,course_code,semester_id',
            'groupinfo.grouptype:id,name',
        ])->get()->groupBy(fn($c) => $c->SEMESTER_ID);;


        // Course IDs that have FA marks — used to lock edit/delete
        $interMarkedCourseIds = InterMark::where('student_id', $id)
            ->pluck('course_id')
            ->unique()
            ->toArray();

        $ciaMarkedCourseIds = CiaMark::where('STUDENT_ID', $id)
            ->pluck('COURSE_ID')
            ->unique()
            ->toArray();

        // Course IDs that have SA marks (via exam_marks_entries)
        $saMarkedCourseIds = DB::table('exam_marks_entries')
            ->where('erp_student_id', $id)
            ->pluck('erp_subject_id')
            ->unique()
            ->toArray();

        $lockedCourseIds = array_unique(array_merge($interMarkedCourseIds, $ciaMarkedCourseIds, $saMarkedCourseIds));

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

        $deliveryContext = $this->resolveStudentDeliveryContext($data, $studentCourses);
        $timetable = StudentTimetableService::generate($id);

        // Group timetable by weekday
        $timetableByDay = $timetable->groupBy(fn($r) => $r['weekday'] ?? 'Unknown');

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

        $faMarksByCourseSemester = $internalMarks
            ->sortByDesc('id')
            ->groupBy(fn($m) => (string) $m->semester . '_' . (string) $m->course_id)
            ->map(fn($rows) => $rows->first());

        $saMarksByCourseSemester = DB::table('exam_marks_entries as eme')
            ->join('exam_sessions as es', 'es.id', '=', 'eme.exam_session_id')
            ->where('eme.erp_student_id', $id)
            ->select(
                'eme.erp_subject_id as course_id',
                'es.semester as semester',
                DB::raw('MAX(eme.marks) as sa_marks')
            )
            ->groupBy('eme.erp_subject_id', 'es.semester')
            ->get()
            ->keyBy(fn($m) => (string) $m->semester . '_' . (string) $m->course_id);

        $ciaMarksBySemester = $studentCourses
            ->groupBy(fn($c) => (string) ($c->semester ?? $c->coursemaster?->semester_id ?? 'Unknown'))
            ->map(function ($courses, $semester) use ($faMarksByCourseSemester, $saMarksByCourseSemester, $semesterMap) {
                $rows = $courses
                    ->sortBy(fn($c) => $c->coursemaster?->course_code ?? 'ZZZ')
                    ->map(function ($course) use ($semester, $faMarksByCourseSemester, $saMarksByCourseSemester) {
                        $key = (string) $semester . '_' . (string) $course->course_id;
                        $fa = $faMarksByCourseSemester->get($key);
                        $sa = $saMarksByCourseSemester->get($key);

                        return [
                            'course' => $course->coursemaster,
                            'fa_marks' => $fa?->internal_mark,
                            'sa_marks' => $sa?->sa_marks,
                            'semester' => $semester,
                        ];
                    })
                    ->values();

                return [
                    'label' => $semesterMap[(int) $semester] ?? ('Semester ' . $semester),
                    'rows' => $rows,
                ];
            })
            ->values();

        // Exam Results via ExamStudent bridge
        $examStudent = ExamStudent::where('erp_student_id', $id)->first();
        $examResults = collect();
        if ($examStudent) {
            $examResults = ExamSystemResult::where('exam_student_id', $examStudent->id)
                ->where('is_published', true)
                ->with(['examSession', 'resultSubjects'])
                ->orderByDesc('created_at')
                ->get();
        }


        return response()->json([
            'status' => true,
            'message' => 'Student profile fetched successfully',
            'data' => [
                'data'               => $data,
                'studentCourses'     => $studentCourses,
                'coursesBySemester'  => $coursesBySemester,
                'lockedCourseIds'    => $lockedCourseIds,
                'availableCourses'   => $availableCourses,
                'availableSemesters' => $availableSemesters,
                'timetableByDay'     => $timetableByDay,
                'attendanceSummary'  => $attendanceSummary,
                'internalMarks'      => $internalMarks,
                'ciaMarksBySemester' => $ciaMarksBySemester,
                'faSegregatedMarks'  => $faSegregatedMarks,
                'examResults'        => $examResults,
                'examStudent'        => $examStudent,
                'batches'            => BatchMaster::orderBy('batch_name')->get(),
                'departments'        => DepartmentMaster::orderBy('name')->get(),
                'campuses'           => Campus::orderBy('name')->get(),
                'religions'          => ReligionMaster::orderBy('name')->get(),
                'nationalities'      => NationalityMaster::orderBy('name')->get(),
                'bloodGroups'        => BloodGroupMaster::orderBy('name')->get(),
                'studentMajorDeliveryType' => $deliveryContext['studentMajorDeliveryType'],
                'studentApplicableDeliveryTypes' => $deliveryContext['studentApplicableDeliveryTypes'],
                'combo1Title' => $deliveryContext['combo1Title'],
                'combo2Title' => $deliveryContext['combo2Title'],
                'courseDeliveryMap' => $deliveryContext['courseDeliveryMap'],
                'courseOfferingSubjectMap' => $deliveryContext['courseOfferingSubjectMap'],
                'programOfferingSubjectTitle' => $deliveryContext['programOfferingSubjectTitle'],
            ],
        ], 200);
    }

    private function resolveStudentDeliveryContext(?StudentMaster $student, $studentCourses): array
    {
        // API-safe fallback: return predictable delivery metadata even if
        // curriculum mapping models are unavailable in this controller.
        return [
            'studentMajorDeliveryType' => null,
            'studentApplicableDeliveryTypes' => collect()->values(),
            'combo1Title' => '',
            'combo2Title' => '',
            'courseDeliveryMap' => [],
            'courseOfferingSubjectMap' => [],
            'programOfferingSubjectTitle' => '',
        ];
    }
}
