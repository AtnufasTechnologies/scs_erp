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
use App\Models\PlacementApplication;
use App\Models\PlacementOpportunity;
use App\Models\ReligionMaster;
use App\Models\Semester;
use App\Models\StudentAttendance;
use App\Models\StudentCourseRoster;
use App\Models\SupCiaComponent;
use App\Models\TrainingAttempt;
use App\Models\TrainingPlacementFormTemplate;
use App\Models\TrainingPlacementOptIn;
use App\Models\TrainingProgram;
use App\Models\StudentMaster;
use App\Models\User;
use App\Models\UserHasRole;
use App\Services\StudentTimetableService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;

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

        $id = (int) $request->id;

        $student = StudentMaster::where('id', $id)->with([
            'batchmaster:id,batch_name',
            'religionmaster:id,name',
            'stdprogramenrolled',
            'academicpathway:id,name',
            'degreetrack:id,name',
        ])->firstOrFail();

        $program = $student->stdprogramenrolled;
        $programCode = data_get($program, 'program_code')
            ?? data_get($program, 'code')
            ?? data_get($program, 'program_short_name')
            ?? data_get($program, 'slug');

        $dob = $student->dob ?? $student->date_of_birth ?? null;

        return response()->json([
            'status' => true,
            'message' => 'Student profile fetched successfully',
            'data' => [
                'student_id' => (int) $student->id,
                'roll_no' => $student->roll_no,
                'register_no' => $student->register_no,
                'first_name' => $student->first_name,
                'last_name' => $student->last_name,
                'dob' => $dob,
                'email' => $student->mail_id,
                'phone' => $student->mobile_no,
                'library_code' => $student->library_code,
                'batch' => $student->batchmaster,
                'religion' => $student->religionmaster,
                'enrolled_program' => [
                    'id' => data_get($program, 'id'),
                    'name' => data_get($program, 'name'),
                    'code' => $programCode,
                ],
                'academic_pathway' => $student->academicpathway,
                'degree_track' => $student->degreetrack,
            ],
        ], 200);
    }

    private function stdprofileFull(Request $request)
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
        // Student course list should follow roster assignments.
        $studentCourses = StudentCourseRoster::query()
            ->with([
                'course:id,course_code,course_title,credits,semester_id,course_type',
                'course.semestermaster:id,title',
                'course.coursetypemaster:id,title,description',
            ])
            ->where('student_id', $id)
            ->orderByDesc('id')
            ->get()
            ->map(function ($roster) {
                $courseMaster = $roster->course;
                if (!$courseMaster) {
                    return null;
                }

                $semesterId = (int) ($roster->semester_id ?: $courseMaster->semester_id);
                if ($semesterId <= 0) {
                    return null;
                }

                return (object) [
                    'id' => (int) $roster->id,
                    'course_id' => (int) ($roster->course_id ?? 0),
                    'semester' => $semesterId,
                    'coursemaster' => $courseMaster,
                ];
            })
            ->filter()
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

        $fa1QuizMarks = collect();
        $fa1ComponentId = $this->fa1ComponentId();
        if ($fa1ComponentId !== null && Schema::hasTable('fa_marks')) {
            $fa1QuizMarks = DB::table('fa_marks as fm')
                ->leftJoin('program_course_masters as pcm', 'pcm.id', '=', 'fm.course_id')
                ->leftJoin('semesters as sem', 'sem.id', '=', 'fm.semester_id')
                ->where('fm.student_id', $id)
                ->where('fm.component_id', $fa1ComponentId)
                ->orderByDesc('fm.attempt')
                ->orderByDesc('fm.updated_at')
                ->get([
                    'fm.course_id',
                    'fm.semester_id',
                    'fm.attempt',
                    'fm.score',
                    'fm.updated_at',
                    'pcm.course_code',
                    'pcm.course_title',
                    'sem.title as semester_title',
                ])
                ->unique(fn($row) => (string) $row->semester_id . '_' . (string) $row->course_id)
                ->values();
        }

        $fa1QuizMarksByCourseSemester = $fa1QuizMarks
            ->keyBy(fn($row) => (string) $row->semester_id . '_' . (string) $row->course_id);


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
            ->map(function ($courses, $semester) use ($faMarksByCourseSemester, $saMarksByCourseSemester, $fa1QuizMarksByCourseSemester, $semesterMap) {
                $rows = $courses
                    ->sortBy(fn($c) => $c->coursemaster?->course_code ?? 'ZZZ')
                    ->map(function ($course) use ($semester, $faMarksByCourseSemester, $saMarksByCourseSemester, $fa1QuizMarksByCourseSemester) {
                        $key = (string) $semester . '_' . (string) $course->course_id;
                        $fa = $faMarksByCourseSemester->get($key);
                        $sa = $saMarksByCourseSemester->get($key);
                        $fa1Quiz = $fa1QuizMarksByCourseSemester->get($key);

                        return [
                            'course' => $course->coursemaster,
                            'fa_marks' => $fa?->internal_mark,
                            'fa1_quiz_marks' => $fa1Quiz->score ?? null,
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
                'fa1QuizMarks'       => $fa1QuizMarks,
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

    public function dashboard(Request $request)
    {
        return $this->stdprofileFull($request);
    }

    public function profile(Request $request)
    {
        $fullPayload = $this->stdprofileFull($request)->getData(true);

        return response()->json([
            'status' => true,
            'message' => 'Student profile fetched successfully',
            'data' => [
                'data' => $fullPayload['data']['data'] ?? null,
                'batches' => $fullPayload['data']['batches'] ?? [],
                'departments' => $fullPayload['data']['departments'] ?? [],
                'campuses' => $fullPayload['data']['campuses'] ?? [],
                'religions' => $fullPayload['data']['religions'] ?? [],
                'nationalities' => $fullPayload['data']['nationalities'] ?? [],
                'bloodGroups' => $fullPayload['data']['bloodGroups'] ?? [],
            ],
        ], 200);
    }

    public function courses(Request $request)
    {
        $request->validate([
            'id' => 'required',
        ]);

        $id = (int) $request->id;

        // First source courses directly from roster, grouped semester-wise.
        $studentCourses = StudentCourseRoster::query()
            ->with([
                'course:id,course_code,course_title,credits,semester_id,course_type',
                'course.semestermaster:id,title',
                'course.coursetypemaster:id,title,description',
            ])
            ->where('student_id', $id)
            ->orderByDesc('id')
            ->get()
            ->map(function ($roster) {
                $courseMaster = $roster->course;
                if (!$courseMaster) {
                    return null;
                }

                $semesterId = (int) ($roster->semester_id ?: $courseMaster->semester_id);
                if ($semesterId <= 0) {
                    return null;
                }

                return (object) [
                    'id' => (int) $roster->id,
                    'course_id' => (int) ($roster->course_id ?? 0),
                    'semester' => $semesterId,
                    'coursemaster' => $courseMaster,
                ];
            })
            ->filter()
            ->unique(fn($c) => (string) $c->semester . '_' . (string) $c->course_id)
            ->values();

        $fa1QuizMarksByCourseSemester = collect();
        $fa1ComponentId = $this->fa1ComponentId();
        if ($fa1ComponentId !== null && Schema::hasTable('fa_marks')) {
            $fa1QuizMarksByCourseSemester = DB::table('fa_marks as fm')
                ->where('fm.student_id', $id)
                ->where('fm.component_id', $fa1ComponentId)
                ->orderByDesc('fm.attempt')
                ->orderByDesc('fm.updated_at')
                ->get([
                    'fm.course_id',
                    'fm.semester_id',
                    'fm.score',
                ])
                ->unique(fn($row) => (string) $row->semester_id . '_' . (string) $row->course_id)
                ->keyBy(fn($row) => (string) $row->semester_id . '_' . (string) $row->course_id);
        }

        $semesterMap = Semester::pluck('title', 'id')->toArray();
        $coursesBySemester = $studentCourses
            ->groupBy(fn($c) => (int) ($c->semester ?? $c->coursemaster?->semester_id ?? 0))
            ->sortKeys()
            ->map(function ($courses, $semesterId) use ($semesterMap, $fa1QuizMarksByCourseSemester) {
                $semId = (int) $semesterId;
                return [
                    'semester_id' => $semId,
                    'semester_title' => $semesterMap[$semId] ?? ('Semester ' . ($semId > 0 ? $semId : '?')),
                    'courses' => $courses
                        ->sortBy(fn($c) => $c->coursemaster?->course_code ?? 'ZZZ')
                        ->map(function ($course) use ($semId, $fa1QuizMarksByCourseSemester) {
                            $key = (string) $semId . '_' . (string) $course->course_id;
                            $fa1Quiz = $fa1QuizMarksByCourseSemester->get($key);

                            return [
                                'course_id' => (int) $course->course_id,
                                'course_code' => $course->coursemaster?->course_code,
                                'course_title' => $course->coursemaster?->course_title,
                                'credits' => $course->coursemaster?->credits,
                                'course_type' => $course->coursemaster?->course_type,
                                'fa1_quiz_marks' => $fa1Quiz->score ?? null,
                            ];
                        })
                        ->values(),
                ];
            })
            ->values();

        return response()->json([
            'status' => true,
            'message' => 'Student courses fetched successfully',
            'data' => [
                'coursesBySemester' => $coursesBySemester,
            ],
        ], 200);
    }

    public function timetable(Request $request)
    {
        $fullPayload = $this->stdprofileFull($request)->getData(true);

        return response()->json([
            'status' => true,
            'message' => 'Student timetable fetched successfully',
            'data' => [
                'timetableByDay' => $fullPayload['data']['timetableByDay'] ?? [],
            ],
        ], 200);
    }

    public function coursesWithSyllabus(Request $request)
    {
        $request->validate([
            'id' => 'required|integer',
        ]);

        $studentId = (int) $request->id;

        $semesterMap = Semester::pluck('title', 'id')->toArray();

        $rosterRows = StudentCourseRoster::query()
            ->with([
                'course:id,course_code,course_title,credits,course_type,semester_id',
            ])
            ->where('student_id', $studentId)
            ->orderByDesc('id')
            ->get()
            ->map(function ($roster) {
                $course = $roster->course;
                if (!$course) {
                    return null;
                }

                $semesterId = (int) ($roster->semester_id ?: $course->semester_id);
                if ($semesterId <= 0) {
                    return null;
                }

                return [
                    'roster_id' => (int) $roster->id,
                    'routine_id' => (int) ($roster->routine_id ?? 0),
                    'teaching_assignment_id' => (int) ($roster->ta_id ?? 0),
                    'subject_id' => (int) ($roster->subject_id ?? 0),
                    'batch_id' => (int) ($roster->batch_id ?? 0),
                    'semester_id' => $semesterId,
                    'course' => [
                        'course_id' => (int) ($roster->course_id ?? 0),
                        'course_code' => $course->course_code,
                        'course_title' => $course->course_title,
                        'credits' => $course->credits,
                        'course_type' => $course->course_type,
                    ],
                ];
            })
            ->filter()
            ->values();

        $routineIds = $rosterRows->pluck('routine_id')->filter(fn($id) => (int) $id > 0)->unique()->values();
        $assignmentIds = $rosterRows->pluck('teaching_assignment_id')->filter(fn($id) => (int) $id > 0)->unique()->values();

        $facultyNamesByRoutineId = collect();
        if ($routineIds->isNotEmpty()) {
            $facultyRows = DB::table('subject_has_routines as shr')
                ->leftJoin('faculties as f', 'f.id', '=', 'shr.faculty_id')
                ->when(Schema::hasColumn('faculties', 'deleted_at'), function ($query) {
                    $query->whereNull('f.deleted_at');
                })
                ->whereIn('shr.id', $routineIds->all())
                ->select([
                    'shr.id as routine_id',
                    'f.id as faculty_id',
                    'f.FIRST_NAME',
                    'f.MIDDLE_NAME',
                    'f.LAST_NAME',
                ])
                ->get();

            $facultyNamesByRoutineId = $facultyRows
                ->groupBy('routine_id')
                ->map(function ($rows) {
                    return $rows
                        ->map(function ($row) {
                            return trim(implode(' ', array_filter([
                                (string) ($row->FIRST_NAME ?? ''),
                                (string) ($row->MIDDLE_NAME ?? ''),
                                (string) ($row->LAST_NAME ?? ''),
                            ])));
                        })
                        ->filter(fn($name) => $name !== '')
                        ->unique()
                        ->values();
                });
        }

        $facultyNamesByAssignmentId = collect();
        if ($assignmentIds->isNotEmpty()) {
            $primaryRows = DB::table('teaching_assignments as ta')
                ->leftJoin('faculties as f', 'f.id', '=', 'ta.faculty_id')
                ->when(Schema::hasColumn('faculties', 'deleted_at'), function ($query) {
                    $query->whereNull('f.deleted_at');
                })
                ->whereIn('ta.id', $assignmentIds->all())
                ->select([
                    'ta.id as teaching_assignment_id',
                    'f.FIRST_NAME',
                    'f.MIDDLE_NAME',
                    'f.LAST_NAME',
                ])
                ->get();

            $coFacultyRows = collect();
            if (Schema::hasTable('teaching_assignment_faculties')) {
                $coFacultyRows = DB::table('teaching_assignment_faculties as taf')
                    ->leftJoin('faculties as f', 'f.id', '=', 'taf.faculty_id')
                    ->when(Schema::hasColumn('faculties', 'deleted_at'), function ($query) {
                        $query->whereNull('f.deleted_at');
                    })
                    ->whereIn('taf.teaching_assignment_id', $assignmentIds->all())
                    ->select([
                        'taf.teaching_assignment_id',
                        'f.FIRST_NAME',
                        'f.MIDDLE_NAME',
                        'f.LAST_NAME',
                    ])
                    ->get();
            }

            $facultyNamesByAssignmentId = $primaryRows
                ->concat($coFacultyRows)
                ->groupBy('teaching_assignment_id')
                ->map(function ($rows) {
                    return $rows
                        ->map(function ($row) {
                            return trim(implode(' ', array_filter([
                                (string) ($row->FIRST_NAME ?? ''),
                                (string) ($row->MIDDLE_NAME ?? ''),
                                (string) ($row->LAST_NAME ?? ''),
                            ])));
                        })
                        ->filter(fn($name) => $name !== '')
                        ->unique()
                        ->values();
                });
        }

        $courseIds = $rosterRows->pluck('course.course_id')->filter(fn($id) => (int) $id > 0)->unique()->values();
        $csoRows = collect();
        if ($courseIds->isNotEmpty() && Schema::hasTable('co_has_csos')) {
            $csoRows = DB::table('co_has_csos')
                ->whereIn('co_id', $courseIds->all())
                ->when(Schema::hasColumn('co_has_csos', 'deleted_at'), function ($query) {
                    $query->whereNull('deleted_at');
                })
                ->orderBy('id')
                ->get([
                    'id',
                    'co_id',
                    'title',
                    'lectures_needed',
                ]);
        }

        $csoByCourseId = $csoRows->groupBy('co_id');

        $completionMapByContextCso = collect();
        $completionMapByCourseCso = collect();
        if (Schema::hasTable('syllabus_managers') && Schema::hasTable('syllabus_subunits')) {
            $csoIds = $csoRows->pluck('id')->filter()->unique()->values();

            if ($courseIds->isNotEmpty() && $csoIds->isNotEmpty()) {
                $managerRows = DB::table('syllabus_managers as sm')
                    ->whereIn('sm.co_id', $courseIds->all())
                    ->whereIn('sm.cso_id', $csoIds->all())
                    ->when(Schema::hasColumn('syllabus_managers', 'deleted_at'), function ($query) {
                        $query->whereNull('sm.deleted_at');
                    })
                    ->select([
                        'sm.id',
                        'sm.subject_id',
                        'sm.batch_id',
                        'sm.semester_id',
                        'sm.co_id',
                        'sm.cso_id',
                    ])
                    ->get();

                $statsByManagerId = collect();
                $managerIds = $managerRows->pluck('id')->filter()->unique()->values();
                if ($managerIds->isNotEmpty()) {
                    $statsByManagerId = DB::table('syllabus_subunits as su')
                        ->whereIn('su.syllabus_manager_id', $managerIds->all())
                        ->select([
                            'su.syllabus_manager_id',
                            DB::raw('COUNT(*) as total_subunits'),
                            DB::raw('SUM(CASE WHEN su.is_completed = 1 THEN 1 ELSE 0 END) as completed_subunits'),
                        ])
                        ->groupBy('su.syllabus_manager_id')
                        ->get()
                        ->keyBy('syllabus_manager_id');
                }

                $aggregateCompletion = function ($rows) use ($statsByManagerId) {
                    $total = 0;
                    $completed = 0;
                    foreach ($rows as $row) {
                        $stats = $statsByManagerId->get($row->id);
                        $total += (int) ($stats->total_subunits ?? 0);
                        $completed += (int) ($stats->completed_subunits ?? 0);
                    }

                    return [
                        'total_subunits' => $total,
                        'completed_subunits' => $completed,
                        'completion_percent' => $total > 0 ? round(($completed / $total) * 100, 1) : 0,
                        'is_completed' => $total > 0 && $completed === $total,
                    ];
                };

                $completionMapByCourseCso = $managerRows
                    ->groupBy(fn($row) => implode('_', [
                        (int) $row->co_id,
                        (int) $row->cso_id,
                    ]))
                    ->map($aggregateCompletion);

                $contextRows = $rosterRows
                    ->map(fn($row) => [
                        'subject_id' => (int) ($row['subject_id'] ?? 0),
                        'batch_id' => (int) ($row['batch_id'] ?? 0),
                        'semester_id' => (int) ($row['semester_id'] ?? 0),
                        'co_id' => (int) ($row['course']['course_id'] ?? 0),
                    ])
                    ->filter(fn($row) => $row['subject_id'] > 0 && $row['batch_id'] > 0 && $row['semester_id'] > 0 && $row['co_id'] > 0)
                    ->unique(fn($row) => implode('_', [$row['subject_id'], $row['batch_id'], $row['semester_id'], $row['co_id']]))
                    ->values();

                if ($contextRows->isNotEmpty()) {
                    $contextKeySet = $contextRows
                        ->mapWithKeys(fn($row) => [implode('_', [
                            (int) $row['subject_id'],
                            (int) $row['batch_id'],
                            (int) $row['semester_id'],
                            (int) $row['co_id'],
                        ]) => true]);

                    $contextManagerRows = $managerRows->filter(function ($row) use ($contextKeySet) {
                        $key = implode('_', [
                            (int) ($row->subject_id ?? 0),
                            (int) ($row->batch_id ?? 0),
                            (int) ($row->semester_id ?? 0),
                            (int) ($row->co_id ?? 0),
                        ]);

                        return isset($contextKeySet[$key]);
                    });

                    if ($contextManagerRows->isNotEmpty()) {
                        $completionMapByContextCso = $contextManagerRows
                            ->groupBy(fn($row) => implode('_', [
                                (int) $row->subject_id,
                                (int) $row->batch_id,
                                (int) $row->semester_id,
                                (int) $row->co_id,
                                (int) $row->cso_id,
                            ]))
                            ->map($aggregateCompletion);
                    }
                }
            }
        }

        $coursesBySemester = $rosterRows
            ->unique(fn($row) => (string) $row['semester_id'] . '_' . (string) $row['course']['course_id'])
            ->groupBy('semester_id')
            ->sortKeys()
            ->map(function ($rows, $semesterId) use ($semesterMap, $facultyNamesByRoutineId, $facultyNamesByAssignmentId, $csoByCourseId, $completionMapByContextCso, $completionMapByCourseCso) {
                $sid = (int) $semesterId;

                return [
                    'semester_id' => $sid,
                    'semester_title' => $semesterMap[$sid] ?? ('Semester ' . $sid),
                    'courses' => collect($rows)
                        ->sortBy(fn($row) => (string) ($row['course']['course_code'] ?? 'ZZZ'))
                        ->map(function ($row) use ($facultyNamesByRoutineId, $facultyNamesByAssignmentId, $csoByCourseId, $completionMapByContextCso, $completionMapByCourseCso) {
                            $routineFacultyNames = $facultyNamesByRoutineId->get((int) ($row['routine_id'] ?? 0), collect());
                            $assignmentFacultyNames = $facultyNamesByAssignmentId->get((int) ($row['teaching_assignment_id'] ?? 0), collect());
                            $facultyNames = collect($routineFacultyNames)
                                ->concat(collect($assignmentFacultyNames))
                                ->filter(fn($name) => (string) $name !== '')
                                ->unique()
                                ->values();

                            $subjectId = (int) ($row['subject_id'] ?? 0);
                            $batchId = (int) ($row['batch_id'] ?? 0);
                            $semesterId = (int) ($row['semester_id'] ?? 0);
                            $courseId = (int) ($row['course']['course_id'] ?? 0);

                            $csos = collect($csoByCourseId->get($courseId, collect()))
                                ->map(function ($cso) use ($completionMapByContextCso, $completionMapByCourseCso, $subjectId, $batchId, $semesterId, $courseId) {
                                    $contextKey = implode('_', [$subjectId, $batchId, $semesterId, $courseId, (int) $cso->id]);
                                    $courseKey = implode('_', [$courseId, (int) $cso->id]);

                                    // Prefer exact roster context when available; otherwise use canonical course-level completion.
                                    $completion = $completionMapByContextCso->get($contextKey)
                                        ?? $completionMapByCourseCso->get($courseKey, [
                                            'total_subunits' => 0,
                                            'completed_subunits' => 0,
                                            'completion_percent' => 0,
                                            'is_completed' => false,
                                        ]);

                                    return [
                                        'cso_id' => (int) $cso->id,
                                        'title' => $cso->title,
                                        'lectures_needed' => $cso->lectures_needed !== null ? (int) $cso->lectures_needed : null,
                                        'total_subunits' => (int) ($completion['total_subunits'] ?? 0),
                                        'completed_subunits' => (int) ($completion['completed_subunits'] ?? 0),
                                        'completion_percent' => (float) ($completion['completion_percent'] ?? 0),
                                        'is_completed' => (bool) ($completion['is_completed'] ?? false),
                                    ];
                                })
                                ->values();

                            return [
                                'course_id' => $courseId,
                                'course_code' => $row['course']['course_code'] ?? null,
                                'course_title' => $row['course']['course_title'] ?? null,
                                'credits' => $row['course']['credits'] ?? null,
                                'course_type' => $row['course']['course_type'] ?? null,
                                'faculty_name' => $facultyNames->first(),
                                'faculty_names' => $facultyNames,
                                'csos' => $csos,
                                'completed_csos' => $csos->where('is_completed', true)->values(),
                                'completed_csos_count' => (int) $csos->where('is_completed', true)->count(),
                            ];
                        })
                        ->values(),
                ];
            })
            ->values();

        return response()->json([
            'status' => true,
            'message' => 'Student courses fetched successfully',
            'data' => [
                'coursesBySemester' => $coursesBySemester,
            ],
        ], 200);
    }

    public function attendanceSummary(Request $request)
    {
        $fullPayload = $this->stdprofileFull($request)->getData(true);

        return response()->json([
            'status' => true,
            'message' => 'Student attendance summary fetched successfully',
            'data' => [
                'attendanceSummary' => $fullPayload['data']['attendanceSummary'] ?? [],
            ],
        ], 200);
    }

    public function courseDetails(Request $request)
    {
        $request->validate([
            'id' => 'required|integer',
            'course_id' => 'required|integer',
        ]);

        $studentId = (int) $request->id;
        $courseId = (int) $request->course_id;

        $rosterRows = StudentCourseRoster::query()
            ->with([
                'course:id,course_code,course_title,credits,course_type,semester_id',
            ])
            ->where('student_id', $studentId)
            ->where('course_id', $courseId)
            ->orderByDesc('id')
            ->get();

        if ($rosterRows->isEmpty()) {
            return response()->json([
                'status' => false,
                'message' => 'Course not found in student roster',
                'data' => null,
            ], 404);
        }

        $course = $rosterRows->first()->course;
        $semesterIds = $rosterRows
            ->map(fn($row) => (int) ($row->semester_id ?: $course?->semester_id ?: 0))
            ->filter(fn($id) => $id > 0)
            ->unique()
            ->values();

        $semesterTitleMap = Semester::query()
            ->whereIn('id', $semesterIds->all())
            ->pluck('title', 'id');

        $routineIds = $rosterRows->pluck('routine_id')->filter(fn($id) => (int) $id > 0)->unique()->values();
        $assignmentIds = $rosterRows->pluck('ta_id')->filter(fn($id) => (int) $id > 0)->unique()->values();

        $facultyNames = collect();

        if ($routineIds->isNotEmpty()) {
            $routineFacultyNames = DB::table('subject_has_routines as shr')
                ->leftJoin('faculties as f', 'f.id', '=', 'shr.faculty_id')
                ->whereIn('shr.id', $routineIds->all())
                ->when(Schema::hasColumn('faculties', 'deleted_at'), function ($query) {
                    $query->whereNull('f.deleted_at');
                })
                ->get(['f.FIRST_NAME', 'f.MIDDLE_NAME', 'f.LAST_NAME'])
                ->map(function ($row) {
                    return trim(implode(' ', array_filter([
                        (string) ($row->FIRST_NAME ?? ''),
                        (string) ($row->MIDDLE_NAME ?? ''),
                        (string) ($row->LAST_NAME ?? ''),
                    ])));
                })
                ->filter(fn($name) => $name !== '')
                ->unique()
                ->values();

            $facultyNames = $facultyNames->concat($routineFacultyNames);
        }

        if ($assignmentIds->isNotEmpty()) {
            $primaryFacultyNames = DB::table('teaching_assignments as ta')
                ->leftJoin('faculties as f', 'f.id', '=', 'ta.faculty_id')
                ->whereIn('ta.id', $assignmentIds->all())
                ->when(Schema::hasColumn('faculties', 'deleted_at'), function ($query) {
                    $query->whereNull('f.deleted_at');
                })
                ->get(['f.FIRST_NAME', 'f.MIDDLE_NAME', 'f.LAST_NAME'])
                ->map(function ($row) {
                    return trim(implode(' ', array_filter([
                        (string) ($row->FIRST_NAME ?? ''),
                        (string) ($row->MIDDLE_NAME ?? ''),
                        (string) ($row->LAST_NAME ?? ''),
                    ])));
                })
                ->filter(fn($name) => $name !== '')
                ->unique()
                ->values();

            $facultyNames = $facultyNames->concat($primaryFacultyNames);

            if (Schema::hasTable('teaching_assignment_faculties')) {
                $coFacultyNames = DB::table('teaching_assignment_faculties as taf')
                    ->leftJoin('faculties as f', 'f.id', '=', 'taf.faculty_id')
                    ->whereIn('taf.teaching_assignment_id', $assignmentIds->all())
                    ->when(Schema::hasColumn('faculties', 'deleted_at'), function ($query) {
                        $query->whereNull('f.deleted_at');
                    })
                    ->get(['f.FIRST_NAME', 'f.MIDDLE_NAME', 'f.LAST_NAME'])
                    ->map(function ($row) {
                        return trim(implode(' ', array_filter([
                            (string) ($row->FIRST_NAME ?? ''),
                            (string) ($row->MIDDLE_NAME ?? ''),
                            (string) ($row->LAST_NAME ?? ''),
                        ])));
                    })
                    ->filter(fn($name) => $name !== '')
                    ->unique()
                    ->values();

                $facultyNames = $facultyNames->concat($coFacultyNames);
            }
        }

        $facultyNames = $facultyNames->unique()->values();

        $canonicalCourseProgress = [
            'total_subunits' => 0,
            'completed_subunits' => 0,
        ];
        if (Schema::hasTable('syllabus_managers') && Schema::hasTable('syllabus_subunits')) {
            $canonicalCourseProgress['total_subunits'] = (int) DB::table('syllabus_subunits as su')
                ->join('syllabus_managers as sm', 'sm.id', '=', 'su.syllabus_manager_id')
                ->where('sm.co_id', $courseId)
                ->when(Schema::hasColumn('syllabus_managers', 'deleted_at'), function ($query) {
                    $query->whereNull('sm.deleted_at');
                })
                ->count();

            $canonicalCourseProgress['completed_subunits'] = (int) DB::table('syllabus_subunits as su')
                ->join('syllabus_managers as sm', 'sm.id', '=', 'su.syllabus_manager_id')
                ->where('sm.co_id', $courseId)
                ->where('su.is_completed', 1)
                ->when(Schema::hasColumn('syllabus_managers', 'deleted_at'), function ($query) {
                    $query->whereNull('sm.deleted_at');
                })
                ->count();
        }

        $csos = collect();
        if (Schema::hasTable('co_has_csos')) {
            $csoRows = DB::table('co_has_csos')
                ->where('co_id', $courseId)
                ->when(Schema::hasColumn('co_has_csos', 'deleted_at'), function ($query) {
                    $query->whereNull('deleted_at');
                })
                ->orderBy('id')
                ->get(['id', 'title', 'lectures_needed']);

            $subunitRowsByCsoId = collect();
            if ($csoRows->isNotEmpty() && Schema::hasTable('cso_subunits')) {
                $subunitRows = DB::table('cso_subunits')
                    ->whereIn('cso_id', $csoRows->pluck('id')->all())
                    ->when(Schema::hasColumn('cso_subunits', 'deleted_at'), function ($query) {
                        $query->whereNull('deleted_at');
                    })
                    ->orderBy('id')
                    ->get(['id', 'cso_id', 'title', 'image_path']);

                $subunitRowsByCsoId = $subunitRows->groupBy('cso_id');
            }

            $contexts = $rosterRows
                ->map(function ($row) use ($course) {
                    return [
                        'subject_id' => (int) ($row->subject_id ?? 0),
                        'batch_id' => (int) ($row->batch_id ?? 0),
                        'semester_id' => (int) ($row->semester_id ?: $course?->semester_id ?: 0),
                        'co_id' => (int) ($row->course_id ?? 0),
                    ];
                })
                ->filter(fn($ctx) => $ctx['subject_id'] > 0 && $ctx['batch_id'] > 0 && $ctx['semester_id'] > 0 && $ctx['co_id'] > 0)
                ->unique(fn($ctx) => implode('_', [$ctx['subject_id'], $ctx['batch_id'], $ctx['semester_id'], $ctx['co_id']]))
                ->values();

            $completionByCsoId = collect();
            $subunitProgressByCsoUnit = collect();
            $resourcesByCsoUnit = collect();
            $canonicalTotalSubunits = 0;
            $canonicalCompletedSubunits = 0;

            if (
                $csoRows->isNotEmpty()
                && Schema::hasTable('syllabus_managers')
                && Schema::hasTable('syllabus_subunits')
            ) {
                $managerRows = DB::table('syllabus_managers as sm')
                    ->where('sm.co_id', $courseId)
                    ->whereIn('sm.cso_id', $csoRows->pluck('id')->all())
                    ->when(Schema::hasColumn('syllabus_managers', 'deleted_at'), function ($query) {
                        $query->whereNull('sm.deleted_at');
                    })
                    ->get(['sm.id', 'sm.subject_id', 'sm.batch_id', 'sm.semester_id', 'sm.co_id', 'sm.cso_id']);

                // Prefer student roster context when available, but never block
                // completion mapping if context columns are missing in roster.
                if ($contexts->isNotEmpty()) {
                    $contextKeySet = array_flip($contexts->map(fn($ctx) => implode('_', [
                        $ctx['subject_id'],
                        $ctx['batch_id'],
                        $ctx['semester_id'],
                        $ctx['co_id'],
                    ]))->all());

                    $contextScopedRows = $managerRows
                        ->filter(function ($row) use ($contextKeySet) {
                            $key = implode('_', [
                                (int) $row->subject_id,
                                (int) $row->batch_id,
                                (int) $row->semester_id,
                                (int) $row->co_id,
                            ]);
                            return isset($contextKeySet[$key]);
                        })
                        ->values();

                    if ($contextScopedRows->isNotEmpty()) {
                        $managerRows = $contextScopedRows;
                    }
                }

                $managerRowsById = $managerRows->keyBy('id');
                $statsByManagerId = collect();
                $managerIds = $managerRows->pluck('id')->filter(fn($id) => (int) $id > 0)->unique()->values();

                if ($managerIds->isNotEmpty()) {
                    $canonicalTotalSubunits = (int) DB::table('syllabus_subunits as su')
                        ->whereIn('su.syllabus_manager_id', $managerIds->all())
                        ->count();

                    $canonicalCompletedSubunits = (int) DB::table('syllabus_subunits as su')
                        ->whereIn('su.syllabus_manager_id', $managerIds->all())
                        ->where('su.is_completed', 1)
                        ->count();

                    $statsByManagerId = DB::table('syllabus_subunits as su')
                        ->whereIn('su.syllabus_manager_id', $managerIds->all())
                        ->select([
                            'su.syllabus_manager_id',
                            DB::raw('COUNT(*) as total_subunits'),
                            DB::raw('SUM(CASE WHEN su.is_completed = 1 THEN 1 ELSE 0 END) as completed_subunits'),
                        ])
                        ->groupBy('su.syllabus_manager_id')
                        ->get()
                        ->keyBy('syllabus_manager_id');

                    $syllabusSubunitRows = DB::table('syllabus_subunits as su')
                        ->whereIn('su.syllabus_manager_id', $managerIds->all())
                        ->select([
                            'su.id',
                            'su.syllabus_manager_id',
                            'su.unit_id',
                            'su.is_completed',
                        ])
                        ->get();

                    $subunitCompletionRows = DB::table('syllabus_subunits as su')
                        ->whereIn('su.syllabus_manager_id', $managerIds->all())
                        ->select([
                            'su.syllabus_manager_id',
                            'su.unit_id',
                            DB::raw('MAX(CASE WHEN su.is_completed = 1 THEN 1 ELSE 0 END) as is_completed'),
                        ])
                        ->groupBy('su.syllabus_manager_id', 'su.unit_id')
                        ->get();

                    if (Schema::hasTable('learning_resources')) {
                        $learningResourcesBySubunitId = DB::table('learning_resources')
                            ->whereIn('syllabus_subunit_id', $syllabusSubunitRows->pluck('id')->filter(fn($id) => (int) $id > 0)->unique()->values()->all())
                            ->when(Schema::hasColumn('learning_resources', 'deleted_at'), function ($query) {
                                $query->whereNull('deleted_at');
                            })
                            ->orderByDesc('id')
                            ->get([
                                'id',
                                'syllabus_subunit_id',
                                'title',
                                'description',
                                'file_path',
                                'file_type',
                                'file_size',
                            ])
                            ->groupBy('syllabus_subunit_id');

                        foreach ($syllabusSubunitRows as $syllabusSubunitRow) {
                            $manager = $managerRowsById->get((int) ($syllabusSubunitRow->syllabus_manager_id ?? 0));
                            if (!$manager) {
                                continue;
                            }

                            $csoUnitKey = (int) ($manager->cso_id ?? 0) . '_' . (int) ($syllabusSubunitRow->unit_id ?? 0);

                            $resourceRows = collect($learningResourcesBySubunitId->get((int) ($syllabusSubunitRow->id ?? 0), collect()))
                                ->map(function ($resource) {
                                    return [
                                        'resource_id' => (int) ($resource->id ?? 0),
                                        'title' => $resource->title,
                                        'description' => $resource->description,
                                        'file_path' => $resource->file_path,
                                        'file_type' => $resource->file_type,
                                        'file_size' => $resource->file_size !== null ? (int) $resource->file_size : null,
                                    ];
                                });

                            $existingRows = collect($resourcesByCsoUnit->get($csoUnitKey, []));
                            $resourcesByCsoUnit->put(
                                $csoUnitKey,
                                $existingRows
                                    ->concat($resourceRows)
                                    ->unique('resource_id')
                                    ->values()
                                    ->all()
                            );
                        }
                    }

                    foreach ($subunitCompletionRows as $subunitCompletion) {
                        $manager = $managerRowsById->get((int) ($subunitCompletion->syllabus_manager_id ?? 0));
                        if (!$manager) {
                            continue;
                        }

                        $key = (int) ($manager->cso_id ?? 0) . '_' . (int) ($subunitCompletion->unit_id ?? 0);
                        $existing = $subunitProgressByCsoUnit->get($key, [
                            'total_contexts' => 0,
                            'completed_contexts' => 0,
                        ]);

                        $existing['total_contexts'] += 1;
                        $existing['completed_contexts'] += ((int) ($subunitCompletion->is_completed ?? 0) === 1) ? 1 : 0;

                        $subunitProgressByCsoUnit->put($key, $existing);
                    }
                }

                $completionByCsoId = $managerRows
                    ->groupBy('cso_id')
                    ->map(function ($rows) use ($statsByManagerId) {
                        $total = 0;
                        $completed = 0;
                        foreach ($rows as $row) {
                            $stats = $statsByManagerId->get((int) $row->id);
                            $total += (int) ($stats->total_subunits ?? 0);
                            $completed += (int) ($stats->completed_subunits ?? 0);
                        }

                        return [
                            'total_subunits' => $total,
                            'completed_subunits' => $completed,
                            'completion_percent' => $total > 0 ? round(($completed / $total) * 100, 1) : 0,
                            'is_completed' => $total > 0 && $completed === $total,
                        ];
                    });
            }

            $csos = $csoRows->map(function ($cso) use ($completionByCsoId, $subunitRowsByCsoId, $subunitProgressByCsoUnit, $resourcesByCsoUnit) {
                $completion = $completionByCsoId->get((int) $cso->id, [
                    'total_subunits' => 0,
                    'completed_subunits' => 0,
                    'completion_percent' => 0,
                    'is_completed' => false,
                ]);

                $subunits = collect($subunitRowsByCsoId->get((int) $cso->id, collect()))
                    ->map(function ($subunit) use ($cso, $subunitProgressByCsoUnit, $resourcesByCsoUnit) {
                        $key = (int) $cso->id . '_' . (int) ($subunit->id ?? 0);
                        $progress = $subunitProgressByCsoUnit->get($key, [
                            'total_contexts' => 0,
                            'completed_contexts' => 0,
                        ]);

                        $totalContexts = (int) ($progress['total_contexts'] ?? 0);
                        $completedContexts = (int) ($progress['completed_contexts'] ?? 0);
                        $isCompleted = $totalContexts > 0 && $completedContexts === $totalContexts;

                        return [
                            'subunit_id' => (int) ($subunit->id ?? 0),
                            'title' => $subunit->title,
                            'image_path' => $subunit->image_path,
                            'completed_by_faculty' => $isCompleted,
                            'is_completed' => $isCompleted,
                            'can_rate' => $isCompleted,
                            'total_contexts' => $totalContexts,
                            'completed_contexts' => $completedContexts,
                            'resources' => collect($resourcesByCsoUnit->get($key, []))->values(),
                            'resource_count' => (int) collect($resourcesByCsoUnit->get($key, []))->count(),
                        ];
                    })
                    ->values();

                $csoTotalSubunits = (int) $subunits->count();
                $csoCompletedSubunits = (int) $subunits->where('is_completed', true)->count();
                $csoCompletionPercent = $csoTotalSubunits > 0
                    ? round(($csoCompletedSubunits / $csoTotalSubunits) * 100, 1)
                    : 0;

                return [
                    'cso_id' => (int) $cso->id,
                    'title' => $cso->title,
                    'lectures_needed' => $cso->lectures_needed !== null ? (int) $cso->lectures_needed : null,
                    'total_subunits' => $csoTotalSubunits,
                    'completed_subunits' => $csoCompletedSubunits,
                    'completion_percent' => (float) $csoCompletionPercent,
                    'is_completed' => $csoTotalSubunits > 0 && $csoCompletedSubunits === $csoTotalSubunits,
                    'subunits' => $subunits,
                    'completed_subunit_count' => $csoCompletedSubunits,
                    'context_progress' => [
                        'total_subunits' => (int) ($completion['total_subunits'] ?? 0),
                        'completed_subunits' => (int) ($completion['completed_subunits'] ?? 0),
                        'completion_percent' => (float) ($completion['completion_percent'] ?? 0),
                    ],
                ];
            })->values();

            if ($canonicalTotalSubunits > 0) {
                $csos = $csos->map(function ($cso) use ($canonicalTotalSubunits, $canonicalCompletedSubunits) {
                    // Keep CSO-level detail as-is; canonical totals are applied at course level.
                    $cso['context_progress']['course_total_subunits'] = $canonicalTotalSubunits;
                    $cso['context_progress']['course_completed_subunits'] = $canonicalCompletedSubunits;
                    return $cso;
                })->values();
            }
        }

        $completedCsos = $csos->where('is_completed', true)->values();
        $subunitStatus = $csos
            ->flatMap(function ($cso) {
                return collect($cso['subunits'] ?? [])->map(function ($subunit) use ($cso) {
                    return [
                        'cso_id' => (int) ($cso['cso_id'] ?? 0),
                        'cso_title' => $cso['title'] ?? null,
                        'subunit_id' => (int) ($subunit['subunit_id'] ?? 0),
                        'subunit_title' => $subunit['title'] ?? null,
                        'is_completed' => (bool) ($subunit['is_completed'] ?? false),
                        'completed_by_faculty' => (bool) ($subunit['completed_by_faculty'] ?? false),
                        'can_rate' => (bool) ($subunit['can_rate'] ?? false),
                        'resource_count' => (int) ($subunit['resource_count'] ?? 0),
                    ];
                });
            })
            ->values();

        $completedSubunitStatus = $subunitStatus->where('is_completed', true)->values();
        $totalSubunits = (int) $csos->sum(fn($cso) => (int) ($cso['total_subunits'] ?? 0));
        $completedSubunits = (int) $csos->sum(fn($cso) => (int) ($cso['completed_subunits'] ?? 0));

        // Prefer canonical counts for course-level progress.
        if ((int) ($canonicalCourseProgress['total_subunits'] ?? 0) > 0) {
            $totalSubunits = (int) $canonicalCourseProgress['total_subunits'];
            $completedSubunits = (int) $canonicalCourseProgress['completed_subunits'];
        }

        $courseCompletionPercent = $totalSubunits > 0
            ? round(($completedSubunits / $totalSubunits) * 100, 1)
            : 0;

        return response()->json([
            'status' => true,
            'message' => 'Course details fetched successfully',
            'data' => [
                'course' => [
                    'course_id' => $courseId,
                    'course_code' => $course?->course_code,
                    'course_title' => $course?->course_title,
                    'credits' => $course?->credits,
                    'course_type' => $course?->course_type,
                ],
                'semesters' => $semesterIds
                    ->map(fn($sid) => [
                        'semester_id' => (int) $sid,
                        'semester_title' => $semesterTitleMap[(int) $sid] ?? ('Semester ' . (int) $sid),
                    ])
                    ->values(),
                'faculty_names' => $facultyNames,
                'csos' => $csos,
                'completed_csos' => $completedCsos,
                'subunit_status' => $subunitStatus,
                'completed_subunit_status' => $completedSubunitStatus,
                'course_progress' => [
                    'total_subunits' => $totalSubunits,
                    'completed_subunits' => $completedSubunits,
                    'pending_subunits' => max(0, $totalSubunits - $completedSubunits),
                    'completion_percent' => $courseCompletionPercent,
                ],
                'summary' => [
                    'total_csos' => (int) $csos->count(),
                    'completed_csos' => (int) $completedCsos->count(),
                    'total_subunits' => $totalSubunits,
                    'completed_subunits' => $completedSubunits,
                    'pending_subunits' => max(0, $totalSubunits - $completedSubunits),
                ],
            ],
        ], 200);
    }

    public function marks(Request $request)
    {
        $fullPayload = $this->stdprofileFull($request)->getData(true);

        return response()->json([
            'status' => true,
            'message' => 'Student marks fetched successfully',
            'data' => [
                'internalMarks' => $fullPayload['data']['internalMarks'] ?? [],
                'ciaMarksBySemester' => $fullPayload['data']['ciaMarksBySemester'] ?? [],
                'faSegregatedMarks' => $fullPayload['data']['faSegregatedMarks'] ?? [],
                'fa1QuizMarks' => $fullPayload['data']['fa1QuizMarks'] ?? [],
            ],
        ], 200);
    }

    private function fa1ComponentId(): ?int
    {
        $component = SupCiaComponent::where('IS_DELETED', 0)
            ->orderBy('id')
            ->get()
            ->first(function ($item) {
                $normalized = preg_replace('/[^A-Z0-9]/', '', strtoupper((string) $item->name));
                return in_array($normalized, ['FA1', 'FAI'], true);
            });

        return $component?->id;
    }

    public function results(Request $request)
    {
        $fullPayload = $this->stdprofileFull($request)->getData(true);

        return response()->json([
            'status' => true,
            'message' => 'Student exam results fetched successfully',
            'data' => [
                'examResults' => $fullPayload['data']['examResults'] ?? [],
                'examStudent' => $fullPayload['data']['examStudent'] ?? null,
            ],
        ], 200);
    }

    public function trainingPlacementDashboard(Request $request)
    {
        $request->validate([
            'id' => 'required|integer',
            'user_id' => 'nullable|integer',
        ]);

        $studentId = (int) $request->input('id');
        $student = StudentMaster::findOrFail($studentId);
        $userId = (int) ($request->input('user_id') ?: $this->resolveUserIdForStudent($student));
        $roleNames = $this->resolveRoleNames($userId);

        $optIn = null;
        if (Schema::hasTable('training_placement_opt_ins')) {
            $optIn = TrainingPlacementOptIn::query()
                ->where('student_id', $studentId)
                ->latest('id')
                ->first();
        }

        $formTemplate = null;
        if (Schema::hasTable('training_placement_form_templates')) {
            $formTemplate = TrainingPlacementFormTemplate::query()
                ->where('is_active', 1)
                ->latest('id')
                ->first();
        }

        $hasOptedForPlacement = $this->hasStudentOptedForTrainingPlacement($studentId);
        $tpStatus = $this->resolveTpStatus($optIn);

        $trainings = collect();
        if (Schema::hasTable('training_programs') && Schema::hasTable('training_target_roles')) {
            $trainings = TrainingProgram::query()
                ->with([
                    'targetRoles:id,training_program_id,role_name',
                    'attempts' => function ($query) use ($userId) {
                        $query->where('user_id', $userId)->orderByDesc('id');
                    },
                ])
                ->withCount(['resources', 'surveyQuestions'])
                ->where('is_active', 1)
                ->whereHas('targetRoles', function ($query) use ($roleNames) {
                    $query->whereIn('role_name', $roleNames);
                })
                ->latest('id')
                ->get();
        }

        $activeJobs = collect();
        if (Schema::hasTable('placement_opportunities')) {
            $activeJobs = PlacementOpportunity::query()
                ->where('is_active', 1)
                ->latest('id')
                ->get();
        }

        $applicableJobs = $hasOptedForPlacement
            ? $activeJobs->filter(fn($job) => $this->isJobApplicableBasic($job, $student))->values()
            : collect();

        $myApplications = collect();
        if (Schema::hasTable('placement_applications')) {
            $myApplications = PlacementApplication::query()
                ->with('placement:id,title,company_name,apply_deadline,category,is_active')
                ->where('student_id', $studentId)
                ->latest('applied_at')
                ->latest('id')
                ->get();
        }

        return response()->json([
            'status' => true,
            'message' => 'Training and placement dashboard fetched successfully',
            'data' => [
                'trainingPlacementOptIn' => $optIn,
                'trainingPlacementFormTemplate' => $formTemplate,
                'tpStatus' => $tpStatus,
                'hasOptedForPlacement' => $hasOptedForPlacement,
                'applicableTrainings' => $trainings,
                'availableJobs' => $applicableJobs,
                'myApplications' => $myApplications,
                'summary' => [
                    'training_count' => $trainings->count(),
                    'job_count' => $applicableJobs->count(),
                    'application_count' => $myApplications->count(),
                ],
            ],
        ], 200);
    }

    public function placementOpportunities(Request $request)
    {
        $request->validate([
            'id' => 'required|integer',
            'search' => 'nullable|string|max:255',
            'category' => 'nullable|string|max:120',
        ]);

        $studentId = (int) $request->input('id');
        $student = StudentMaster::findOrFail($studentId);
        $search = strtolower(trim((string) $request->input('search', '')));
        $category = trim((string) $request->input('category', ''));

        $jobs = collect();
        if (Schema::hasTable('placement_opportunities') && $this->hasStudentOptedForTrainingPlacement($studentId)) {
            $jobs = PlacementOpportunity::query()
                ->where('is_active', 1)
                ->latest('id')
                ->get()
                ->filter(fn($job) => $this->isJobApplicableBasic($job, $student));

            if ($search !== '') {
                $jobs = $jobs->filter(function ($job) use ($search) {
                    $haystack = strtolower(implode(' ', [
                        (string) ($job->title ?? ''),
                        (string) ($job->company_name ?? ''),
                        (string) ($job->description ?? ''),
                        (string) ($job->location ?? ''),
                        (string) ($job->country ?? ''),
                    ]));

                    return str_contains($haystack, $search);
                });
            }

            if ($category !== '') {
                $jobs = $jobs->filter(fn($job) => (string) ($job->category ?? '') === $category);
            }

            $jobs = $jobs->values();
        }

        return response()->json([
            'status' => true,
            'message' => 'Placement opportunities fetched successfully',
            'data' => [
                'count' => $jobs->count(),
                'jobs' => $jobs,
            ],
        ], 200);
    }

    public function placementApplications(Request $request)
    {
        $request->validate([
            'id' => 'required|integer',
        ]);

        $studentId = (int) $request->input('id');

        $applications = collect();
        if (Schema::hasTable('placement_applications')) {
            $applications = PlacementApplication::query()
                ->with('placement:id,title,company_name,apply_deadline,category,is_active')
                ->where('student_id', $studentId)
                ->latest('applied_at')
                ->latest('id')
                ->get();
        }

        return response()->json([
            'status' => true,
            'message' => 'Placement applications fetched successfully',
            'data' => [
                'count' => $applications->count(),
                'applications' => $applications,
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

    private function resolveUserIdForStudent(StudentMaster $student): int
    {
        $studentId = (int) ($student->id ?? 0);
        if ($studentId <= 0) {
            return 0;
        }

        $byStudentId = User::query()->where('student_id', $studentId)->value('id');
        if (!empty($byStudentId)) {
            return (int) $byStudentId;
        }

        $rollNo = (string) ($student->roll_no ?? '');
        if ($rollNo !== '') {
            $byRollNo = User::query()->where('roll_no', $rollNo)->value('id');
            if (!empty($byRollNo)) {
                return (int) $byRollNo;
            }
        }

        return 0;
    }

    private function resolveRoleNames(int $userId): array
    {
        if ($userId <= 0) {
            return ['student'];
        }

        $roles = UserHasRole::query()
            ->where('user_id', $userId)
            ->whereNotNull('role_name')
            ->pluck('role_name')
            ->map(fn($role) => trim((string) $role))
            ->filter(fn($role) => $role !== '')
            ->unique()
            ->values()
            ->all();

        if (!in_array('student', $roles, true)) {
            $roles[] = 'student';
        }

        return $roles;
    }

    private function resolveTpStatus(?TrainingPlacementOptIn $optIn): string
    {
        if (!$optIn || empty($optIn->form_file_path)) {
            return 'not_submitted';
        }

        $rawStatus = strtolower((string) ($optIn->approval_status ?? ''));
        if ($rawStatus === 'approved') {
            return 'approved';
        }

        if ($rawStatus === '' || $rawStatus === 'submitted') {
            return 'in_review';
        }

        return $rawStatus;
    }

    private function hasStudentOptedForTrainingPlacement(int $studentId): bool
    {
        if ($studentId <= 0 || !Schema::hasTable('training_placement_opt_ins')) {
            return false;
        }

        $query = TrainingPlacementOptIn::query()
            ->where('student_id', $studentId)
            ->whereNotNull('form_file_path');

        if (Schema::hasColumn('training_placement_opt_ins', 'policy_accepted')) {
            $query->where('policy_accepted', 1);
        }

        if (Schema::hasColumn('training_placement_opt_ins', 'opted_at')) {
            $query->whereNotNull('opted_at');
        }

        return $query->exists();
    }

    private function isJobApplicableBasic(PlacementOpportunity $job, StudentMaster $student): bool
    {
        $jobCampusId = (int) ($job->campus_id ?? 0);
        $studentCampusId = (int) ($student->campus_id ?? 0);

        if ($jobCampusId > 0 && $studentCampusId > 0 && $jobCampusId !== $studentCampusId) {
            return false;
        }

        $allowedYear = strtolower(trim((string) ($job->student_year ?? '')));
        $studentYear = strtolower(trim((string) ($student->current_year ?? '')));

        if ($allowedYear === '' || $studentYear === '') {
            return true;
        }

        if ($allowedYear === 'passout') {
            return str_contains($studentYear, 'passout') || str_contains($studentYear, 'passedout');
        }

        return $allowedYear === $studentYear;
    }
}
