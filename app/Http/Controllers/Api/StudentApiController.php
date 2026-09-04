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
use App\Models\StudentCourseInfo;
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
        $fullPayload = $this->stdprofileFull($request)->getData(true);

        return response()->json([
            'status' => true,
            'message' => 'Student courses fetched successfully',
            'data' => [
                'studentCourses' => $fullPayload['data']['studentCourses'] ?? [],
                'coursesBySemester' => $fullPayload['data']['coursesBySemester'] ?? [],
                'lockedCourseIds' => $fullPayload['data']['lockedCourseIds'] ?? [],
                'availableCourses' => $fullPayload['data']['availableCourses'] ?? [],
                'availableSemesters' => $fullPayload['data']['availableSemesters'] ?? [],
                'studentMajorDeliveryType' => $fullPayload['data']['studentMajorDeliveryType'] ?? null,
                'studentApplicableDeliveryTypes' => $fullPayload['data']['studentApplicableDeliveryTypes'] ?? [],
                'combo1Title' => $fullPayload['data']['combo1Title'] ?? '',
                'combo2Title' => $fullPayload['data']['combo2Title'] ?? '',
                'courseDeliveryMap' => $fullPayload['data']['courseDeliveryMap'] ?? [],
                'courseOfferingSubjectMap' => $fullPayload['data']['courseOfferingSubjectMap'] ?? [],
                'programOfferingSubjectTitle' => $fullPayload['data']['programOfferingSubjectTitle'] ?? '',
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
            ],
        ], 200);
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
