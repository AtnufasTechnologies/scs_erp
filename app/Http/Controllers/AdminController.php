<?php

namespace App\Http\Controllers;

use App\Models\AcademicBlock;
use App\Models\AcademicDepartment;
use App\Models\AnnualPromotionLog;
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
use App\Models\ProgramWiseSemesterCourse;
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
use App\Models\CiaMark;
use App\Models\AcademicPathwayMaster;
use App\Models\DegreeTrackMaster;
use App\Models\ProgramTrackConfiguration;
use App\Models\Subject;
use App\Models\SubjectCourseMaster;
use App\Models\SubjectFacultyMaster;
use App\Models\SubjectHasStudentProgam;
use App\Models\TeachingAssignment;
use App\Models\SyllabusManager;
use App\Services\StudentTimetableService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Schema;
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
        $batchId = request()->input('batch_id');

        $data = StudentMaster::with([
            'religionmaster:id,name',
            'deptmaster:id,department_code,name',
            'campusmaster:id,slug,name',
            'nationalitymaster:id,name',
            'usertype:id,name',
            'bloodgroup',
            'batchmaster:id,batch_name',
            'programgroup',
            'stdprogramenrolled',
            'academicpathway',
            'degreetrack',
            'singleselection',
            'activeSemesterConfig'

        ])->where('campus_id', 1);

        if (!empty($batchId)) {
            $data->where('batch', $batchId);
        }

        $data = $data->paginate(12)->appends(request()->query());

        return view('admin.students.student-master', ['data' => $data]);
    }

    function stdMasterSiliguri()
    {
        $batchId = request()->input('batch_id');

        $data = StudentMaster::with([
            'religionmaster:id,name',
            'campusmaster:id,slug,name',
            'nationalitymaster:id,name',
            'usertype:id,name',
            'bloodgroup',
            'batchmaster:id,batch_name',
            'stdprogramenrolled',
            'academicpathway',
            'degreetrack',
            'singleselection',
            'activeSemesterConfig'

        ])->where('campus_id', 2);

        if (!empty($batchId)) {
            $data->where('batch', $batchId);
        }

        $data = $data->paginate(12)->appends(request()->query());

        return view('admin.students.student-master', ['data' => $data]);
    }

    function searchStudents(Request $request)
    {
        $searchTerm = $request->input('search');
        $campusId = $request->input('campus_id', 2); // Default to Siliguri
        $batchId = $request->input('batch_id');

        $query = StudentMaster::with([
            'religionmaster:id,name',
            'campusmaster:id,slug,name',
            'deptmaster:id,department_code,name',
            'nationalitymaster:id,name',
            'usertype:id,name',
            'bloodgroup',
            'batchmaster:id,batch_name',
            'stdprogramenrolled',
            'programgroup',
            'academicpathway',
            'degreetrack',
            'singleselection',
            'activeSemesterConfig'
        ])->where('campus_id', $campusId);

        if (!empty($batchId)) {
            $query->where('batch', $batchId);
        }

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

        // Add active semester information to each student from student_semester_configs
        foreach ($students as $student) {
            $student->current_semester = $student->activeSemesterConfig->semester_id ?? null;
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
        ]);
    }

    private function resolveStudentDeliveryContext(?StudentMaster $student, $studentCourses): array
    {
        $programCombination = null;
        if ($student && !empty($student->new_program_id) && !empty($student->batch)) {
            $programCombination = SubjectHasStudentProgam::with([
                'subjectmaster:id,title,code',
                'combomap.combo1:id,title,code',
                'combomap.combo2:id,title,code',
            ])
                ->where('student_program_id', (int) $student->new_program_id)
                ->where('batch_id', (int) $student->batch)
                ->orderBy('id')
                ->first();
        }

        $combo1Id = (int) ($programCombination?->combomap?->combo_id_1 ?? 0);
        if ($combo1Id <= 0) {
            $combo1Id = (int) ($programCombination?->subject_id ?? 0);
        }

        $combo2Id = (int) ($programCombination?->combomap?->combo_id_2 ?? 0);
        $selectedComboId = (int) ($student->selected_combo_id ?? 0);

        $studentMajorDeliveryType = null;
        if ($selectedComboId > 0) {
            if ($selectedComboId === $combo1Id) {
                $studentMajorDeliveryType = ProgramWiseSemesterCourse::DELIVERY_MAJOR_COMBO1;
            } elseif ($combo2Id > 0 && $selectedComboId === $combo2Id) {
                $studentMajorDeliveryType = ProgramWiseSemesterCourse::DELIVERY_MAJOR_COMBO2;
            }
        } elseif ($combo1Id > 0 && $combo2Id <= 0) {
            $studentMajorDeliveryType = ProgramWiseSemesterCourse::DELIVERY_MAJOR_COMBO1;
        }

        $studentApplicableDeliveryTypes = collect([
            $studentMajorDeliveryType,
            ProgramWiseSemesterCourse::DELIVERY_PROGRAMME_COMMON,
            ProgramWiseSemesterCourse::DELIVERY_OPEN_CHOICE,
        ])->filter()->unique()->values();

        $courseDeliveryMap = [];
        $courseOfferingSubjectMap = [];
        $programType = strtoupper(trim((string) ($programCombination?->program_type ?? '')));
        if ($programCombination && $studentCourses) {
            $courseIds = collect($studentCourses)
                ->pluck('course_id')
                ->map(fn($id) => (int) $id)
                ->filter()
                ->unique()
                ->values()
                ->all();

            $semesterIds = collect($studentCourses)
                ->map(fn($course) => (int) ($course->semester ?? $course->coursemaster?->semester_id ?? 0))
                ->filter(fn($id) => $id > 0)
                ->unique()
                ->values()
                ->all();

            if (!empty($courseIds)) {
                $deliveryRowsQuery = ProgramWiseSemesterCourse::where('program_combo_refid', (int) $programCombination->id)
                    ->where('batch', (int) $student->batch)
                    ->whereIn('course_id', $courseIds);

                $pathwayId = (int) ($student->academic_pathway_id ?? 0);
                $degreeTrackId = (int) ($student->degree_track_id ?? 0);

                if (Schema::hasColumn((new ProgramWiseSemesterCourse())->getTable(), 'academic_pathway_id')) {
                    if ($pathwayId > 0) {
                        $deliveryRowsQuery->where('academic_pathway_id', $pathwayId);
                    } else {
                        $deliveryRowsQuery->whereNull('academic_pathway_id');
                    }
                }

                if (Schema::hasColumn((new ProgramWiseSemesterCourse())->getTable(), 'degree_track_id')) {
                    if ($degreeTrackId > 0) {
                        $deliveryRowsQuery->where('degree_track_id', $degreeTrackId);
                    } else {
                        $deliveryRowsQuery->whereNull('degree_track_id');
                    }
                }

                $deliveryRows = $deliveryRowsQuery->get(['semester', 'course_id', 'delivery_category']);

                foreach ($deliveryRows as $row) {
                    $key = (string) ((int) $row->semester) . '_' . (string) ((int) $row->course_id);
                    if (!empty($row->delivery_category)) {
                        $courseDeliveryMap[$key] = (string) $row->delivery_category;
                    }
                }
            }

            if (!empty($courseIds) && !empty($semesterIds)) {
                $syllabusQuery = SyllabusManager::with('subject:id,title,code')
                    ->where('batch_id', (int) $student->batch)
                    ->whereIn('co_id', $courseIds)
                    ->whereIn('semester_id', $semesterIds);

                if (Schema::hasColumn('syllabus_managers', 'status')) {
                    $syllabusQuery->where('status', 'published');
                }

                if ($programType !== '' && Schema::hasColumn('syllabus_managers', 'program_type')) {
                    $syllabusQuery->whereRaw("UPPER(TRIM(COALESCE(program_type, ''))) = ?", [$programType]);
                }

                $syllabusRows = $syllabusQuery->get(['semester_id', 'co_id', 'subject_id']);
                foreach ($syllabusRows as $row) {
                    $key = (string) ((int) $row->semester_id) . '_' . (string) ((int) $row->co_id);
                    $subjectTitle = trim((string) ($row->subject?->title ?? ''));
                    if ($subjectTitle === '') {
                        continue;
                    }

                    if (!isset($courseOfferingSubjectMap[$key])) {
                        $courseOfferingSubjectMap[$key] = [];
                    }

                    if (!in_array($subjectTitle, $courseOfferingSubjectMap[$key], true)) {
                        $courseOfferingSubjectMap[$key][] = $subjectTitle;
                    }
                }

                foreach ($courseOfferingSubjectMap as $key => $subjects) {
                    $courseOfferingSubjectMap[$key] = implode(' / ', $subjects);
                }
            }
        }

        return [
            'studentMajorDeliveryType' => $studentMajorDeliveryType,
            'studentApplicableDeliveryTypes' => $studentApplicableDeliveryTypes,
            'combo1Title' => (string) ($programCombination?->combomap?->combo1?->title ?? ''),
            'combo2Title' => (string) ($programCombination?->combomap?->combo2?->title ?? ''),
            'courseDeliveryMap' => $courseDeliveryMap,
            'courseOfferingSubjectMap' => $courseOfferingSubjectMap,
            'programOfferingSubjectTitle' => (string) ($programCombination?->subjectmaster?->title ?? ''),
        ];
    }

    private function resolveStudentTimetableRows(StudentMaster $student, array $deliveryContext, $studentCourses)
    {
        $batchId = (int) ($student->batch ?? 0);
        if ($batchId <= 0) {
            return collect();
        }

        $programCombinationId = 0;
        if (!empty($student->new_program_id)) {
            $programCombinationId = (int) SubjectHasStudentProgam::query()
                ->where('student_program_id', (int) $student->new_program_id)
                ->where('batch_id', $batchId)
                ->value('id');
        }

        // Source of truth: student mapped courses + resolved per-course delivery type.
        $studentCoursePairs = collect($studentCourses)
            ->map(function ($course) use ($deliveryContext) {
                $courseId = (int) ($course->course_id ?? 0);
                $semesterId = (int) ($course->semester ?? $course->coursemaster?->semester_id ?? 0);
                if ($courseId <= 0 || $semesterId <= 0) {
                    return null;
                }

                $deliveryKey = (string) $semesterId . '_' . (string) $courseId;
                $deliveryType = strtoupper(trim((string) ($deliveryContext['courseDeliveryMap'][$deliveryKey] ?? ($deliveryContext['studentMajorDeliveryType'] ?? ProgramWiseSemesterCourse::DELIVERY_PROGRAMME_COMMON))));
                if ($deliveryType === '') {
                    $deliveryType = ProgramWiseSemesterCourse::DELIVERY_PROGRAMME_COMMON;
                }

                return [
                    'course_id' => $courseId,
                    'semester_id' => $semesterId,
                    'delivery_type' => $deliveryType,
                ];
            })
            ->filter()
            ->unique(fn($row) => $row['semester_id'] . '_' . $row['course_id'] . '_' . $row['delivery_type'])
            ->values();

        if ($studentCoursePairs->isEmpty() || $programCombinationId <= 0) {
            return collect();
        }

        // CurriculumEngine applicability by enrolled program + batch + pathway + degree track.
        $pathwayId = (int) ($student->academic_pathway_id ?? 0);
        $degreeTrackId = (int) ($student->degree_track_id ?? 0);

        $curriculumQuery = ProgramWiseSemesterCourse::query()
            ->where('program_combo_refid', $programCombinationId)
            ->where('batch', $batchId)
            ->whereIn('course_id', $studentCoursePairs->pluck('course_id')->unique()->values()->all())
            ->whereIn('semester', $studentCoursePairs->pluck('semester_id')->unique()->values()->all());

        if (Schema::hasColumn((new ProgramWiseSemesterCourse())->getTable(), 'is_active')) {
            $curriculumQuery->where('is_active', 1);
        }

        if (Schema::hasColumn((new ProgramWiseSemesterCourse())->getTable(), 'academic_pathway_id')) {
            if ($pathwayId > 0) {
                $curriculumQuery->where('academic_pathway_id', $pathwayId);
            } else {
                $curriculumQuery->whereNull('academic_pathway_id');
            }
        }

        if (Schema::hasColumn((new ProgramWiseSemesterCourse())->getTable(), 'degree_track_id')) {
            if ($degreeTrackId > 0) {
                $curriculumQuery->where('degree_track_id', $degreeTrackId);
            } else {
                $curriculumQuery->whereNull('degree_track_id');
            }
        }

        $curriculumRows = $curriculumQuery->get(['course_id', 'semester', 'delivery_category']);
        if ($curriculumRows->isEmpty()) {
            return collect();
        }

        $applicablePairKeys = $curriculumRows
            ->map(function ($row) {
                $delivery = strtoupper(trim((string) ($row->delivery_category ?? ProgramWiseSemesterCourse::DELIVERY_PROGRAMME_COMMON)));
                if ($delivery === '') {
                    $delivery = ProgramWiseSemesterCourse::DELIVERY_PROGRAMME_COMMON;
                }
                return (int) ($row->semester ?? 0) . '_' . (int) ($row->course_id ?? 0) . '_' . $delivery;
            })
            ->unique()
            ->values();

        $selectedPairs = $studentCoursePairs
            ->filter(fn($pair) => $applicablePairKeys->contains($pair['semester_id'] . '_' . $pair['course_id'] . '_' . $pair['delivery_type']))
            ->values();

        if ($selectedPairs->isEmpty()) {
            return collect();
        }

        // Resolve teacher assignment by course + delivery type.
        $selectedCourseIds = $selectedPairs->pluck('course_id')->unique()->values();
        $selectedPairKeys = $selectedPairs
            ->map(fn($pair) => $pair['course_id'] . '_' . $pair['delivery_type'])
            ->unique()
            ->values();

        $assignments = TeachingAssignment::query()
            ->where('is_active', 1)
            ->whereIn('course_id', $selectedCourseIds->all())
            ->get(['id', 'course_id', 'faculty_id', 'delivery_type'])
            ->map(function ($assignment) {
                $deliveryType = strtoupper(trim((string) ($assignment->delivery_type ?? ProgramWiseSemesterCourse::DELIVERY_PROGRAMME_COMMON)));
                if ($deliveryType === '') {
                    $deliveryType = ProgramWiseSemesterCourse::DELIVERY_PROGRAMME_COMMON;
                }
                $assignment->normalized_delivery_type = $deliveryType;
                return $assignment;
            })
            ->filter(fn($assignment) => $selectedPairKeys->contains(((int) $assignment->course_id) . '_' . $assignment->normalized_delivery_type))
            ->values();
        $assignmentIds = $assignments->pluck('id')->map(fn($id) => (int) $id)->filter(fn($id) => $id > 0)->unique()->values();
        $assignmentFacultyIds = $assignments->pluck('faculty_id')->map(fn($id) => (int) $id)->filter(fn($id) => $id > 0)->unique()->values();

        $subjectCourseIds = SubjectCourseMaster::query()
            ->whereIn('course_master_id', $selectedCourseIds->all())
            ->pluck('id')
            ->map(fn($id) => (int) $id)
            ->filter(fn($id) => $id > 0)
            ->unique()
            ->values();

        $hasTeachingAssignmentId = Schema::hasColumn('subject_has_routines', 'teaching_assignment_id');
        $hasTeachingAllocationId = Schema::hasColumn('subject_has_routines', 'teaching_allocation_id');

        $routineQuery = SubjectHasRoutine::query()
            ->where('batch_id', $batchId)
            ->where(function ($outer) use (
                $assignmentIds,
                $assignmentFacultyIds,
                $subjectCourseIds,
                $hasTeachingAssignmentId,
                $hasTeachingAllocationId
            ) {
                $matchedByAllocation = false;
                if ($assignmentIds->isNotEmpty() && $hasTeachingAssignmentId) {
                    $outer->whereIn('teaching_assignment_id', $assignmentIds->all());
                    $matchedByAllocation = true;
                }

                if ($assignmentIds->isNotEmpty() && $hasTeachingAllocationId) {
                    if ($matchedByAllocation) {
                        $outer->orWhereIn('teaching_allocation_id', $assignmentIds->all());
                    } else {
                        $outer->whereIn('teaching_allocation_id', $assignmentIds->all());
                        $matchedByAllocation = true;
                    }
                }

                if ($subjectCourseIds->isNotEmpty() && $assignmentFacultyIds->isNotEmpty()) {
                    $outer->orWhere(function ($legacy) use (
                        $subjectCourseIds,
                        $assignmentFacultyIds,
                        $hasTeachingAssignmentId,
                        $hasTeachingAllocationId
                    ) {
                        if ($hasTeachingAssignmentId) {
                            $legacy->whereNull('teaching_assignment_id');
                        }

                        if ($hasTeachingAllocationId) {
                            $legacy->whereNull('teaching_allocation_id');
                        }

                        $legacy->whereIn('subject_course_id', $subjectCourseIds->all())
                            ->whereIn('faculty_id', $assignmentFacultyIds->all());
                    });
                }
            })
            ->with([
                'weekdaymaster:id,title',
                'hourmaster:id,name',
                'faculty:id,FIRST_NAME,LAST_NAME',
                'subjectCourse:id,subject_id,course_master_id',
                'subjectCourse.courseMaster:id,course_code,course_title,semester_id',
                'teachingAssignment:id,course_id,faculty_id,delivery_type,allocation_group,room',
                'teachingAssignment.course:id,course_code,course_title,semester_id',
                'teachingAllocation:id,course_id,faculty_id,delivery_type,allocation_group,room',
                'teachingAllocation.course:id,course_code,course_title,semester_id',
            ])
            ->orderBy('weekday_id')
            ->orderBy('hour_id');

        return $routineQuery->get();
    }

    private function buildStudentCourseContext(int $studentId): array
    {
        $student = StudentMaster::select(['id', 'new_program_id', 'batch', 'selected_combo_id'])->find($studentId);

        $studentCourses = StudentCourseInfo::with([
            'coursemaster.semestermaster:id,title',
            'coursemaster.coursetypemaster:id,title',
        ])
            ->where('student_id', $studentId)
            ->orderByDesc('id')
            ->get()
            ->unique(fn($c) => ($c->semester ?? $c->coursemaster?->semester_id ?? 'na') . '_' . $c->course_id)
            ->values();

        $semesterMap = Semester::pluck('title', 'id')->toArray();

        $coursesBySemester = $studentCourses
            ->sortBy(fn($c) => $c->semester ?? $c->coursemaster?->semester_id ?? 999)
            ->groupBy(function ($c) use ($semesterMap) {
                $semId = $c->semester ?? $c->coursemaster?->semester_id;
                return $semesterMap[$semId] ?? ('Semester ' . ($semId ?? '?'));
            });

        $interMarkedCourseIds = InterMark::where('student_id', $studentId)
            ->pluck('course_id')
            ->unique()
            ->toArray();

        $ciaMarkedCourseIds = CiaMark::where('STUDENT_ID', $studentId)
            ->pluck('COURSE_ID')
            ->unique()
            ->toArray();

        $saMarkedCourseIds = DB::table('exam_marks_entries')
            ->where('erp_student_id', $studentId)
            ->pluck('erp_subject_id')
            ->unique()
            ->toArray();

        $lockedCourseIds = array_unique(array_merge($interMarkedCourseIds, $ciaMarkedCourseIds, $saMarkedCourseIds));

        $enrolledCourseIds = $studentCourses->pluck('course_id')->toArray();
        $availableCourses  = ProgramCourseMaster::where('is_deleted', 0)
            ->whereNotIn('id', $enrolledCourseIds)
            ->with('semestermaster:id,title', 'coursetypemaster:id,title')
            ->orderBy('semester_id')
            ->orderBy('course_title')
            ->get()
            ->groupBy(fn($c) => $c->semester_id);

        $deliveryContext = $this->resolveStudentDeliveryContext($student, $studentCourses);

        return [
            'studentCourses' => $studentCourses,
            'coursesBySemester' => $coursesBySemester,
            'lockedCourseIds' => $lockedCourseIds,
            'availableCourses' => $availableCourses,
            'courseDeliveryMap' => $deliveryContext['courseDeliveryMap'],
            'courseOfferingSubjectMap' => $deliveryContext['courseOfferingSubjectMap'],
            'studentMajorDeliveryType' => $deliveryContext['studentMajorDeliveryType'],
            'programOfferingSubjectTitle' => $deliveryContext['programOfferingSubjectTitle'],
        ];
    }

    private function stdCoursesAjaxResponse(int $studentId, string $message, int $status = 200)
    {
        $student = StudentMaster::findOrFail($studentId);
        $context = $this->buildStudentCourseContext($studentId);

        $courseListHtml = view('admin.master.partials.student-courses-list', [
            'data' => $student,
            'studentCourses' => $context['studentCourses'],
            'coursesBySemester' => $context['coursesBySemester'],
            'lockedCourseIds' => $context['lockedCourseIds'],
            'courseDeliveryMap' => $context['courseDeliveryMap'],
            'courseOfferingSubjectMap' => $context['courseOfferingSubjectMap'],
            'studentMajorDeliveryType' => $context['studentMajorDeliveryType'],
            'programOfferingSubjectTitle' => $context['programOfferingSubjectTitle'],
        ])->render();

        $availableCoursesHtml = view('admin.master.partials.student-course-options', [
            'availableCourses' => $context['availableCourses'],
        ])->render();

        return response()->json([
            'success' => $status >= 200 && $status < 300,
            'message' => $message,
            'course_list_html' => $courseListHtml,
            'available_courses_html' => $availableCoursesHtml,
        ], $status);
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


        //check current year is beingn updated or not 
        if ($student->current_year < $request->current_year) {
            //entry in promotion log
            $userId = Auth::user()->id;
            AnnualPromotionLog::create([
                'batch' => $student->batch,
                'campus' => $student->campus_id,
                'student_id' => $student->id,
                'promoted_from_year' =>  $student->current_year,
                'promoted_to_year' => $request->current_year,
                'status' => $student->current_year < $request->current_year == true ? 'promoted' : 'not promoted',
                'created_by' => $userId,
                'updated_by' => $userId
            ]);
        }

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
            'batch' => 'required',
            'semester_id'   => 'required|integer|exists:semesters,id',
        ]);

        $batchinfo  = BatchMaster::find($request->batch);
        $academicYear = $batchinfo->batch_name;
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

        if ($request->ajax() || $request->expectsJson()) {
            return $this->stdCoursesAjaxResponse($studentId, $msg);
        }

        return redirect()->route('admin.student.profile', ['id' => $studentId, 'rollno' => $student->roll_no])
            ->with('success', $msg)
            ->withFragment('tab-courses');
    }

    function stdCourseUpdate(Request $request, $studentId, $sciId)
    {
        $sci = StudentCourseInfo::where('student_id', $studentId)->findOrFail($sciId);

        // Check marks lock
        $hasFA = InterMark::where('student_id', $studentId)->where('course_id', $sci->course_id)->exists();
        $hasCia = CiaMark::where('STUDENT_ID', $studentId)->where('COURSE_ID', $sci->course_id)->exists();
        $hasSA = DB::table('exam_marks_entries')
            ->where('erp_student_id', $studentId)
            ->where('erp_subject_id', $sci->course_id)
            ->exists();

        if ($hasFA || $hasCia || $hasSA) {
            if ($request->ajax() || $request->expectsJson()) {
                return $this->stdCoursesAjaxResponse($studentId, 'Cannot modify a course that has marks recorded.', 422);
            }
            return back()->with('error', 'Cannot modify a course that has marks recorded.')->withFragment('tab-courses');
        }

        $sci->update(['is_active' => $sci->is_active ? 0 : 1]);

        if ($request->ajax() || $request->expectsJson()) {
            return $this->stdCoursesAjaxResponse($studentId, 'Course status updated.');
        }

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
        $hasCia = CiaMark::where('STUDENT_ID', $studentId)->where('COURSE_ID', $sci->course_id)->exists();
        $hasSA = DB::table('exam_marks_entries')
            ->where('erp_student_id', $studentId)
            ->where('erp_subject_id', $sci->course_id)
            ->exists();

        if ($hasFA || $hasCia || $hasSA) {
            if ($request->ajax() || $request->expectsJson()) {
                return $this->stdCoursesAjaxResponse($studentId, 'Cannot remove a course that has marks recorded.', 422);
            }
            return back()->with('error', 'Cannot remove a course that has marks recorded.')->withFragment('tab-courses');
        }

        $sci->delete();

        if ($request->ajax() || $request->expectsJson()) {
            return $this->stdCoursesAjaxResponse($studentId, 'Course enrollment removed.');
        }

        $student = StudentMaster::findOrFail($studentId);
        return redirect()->route('admin.student.profile', ['id' => $studentId, 'rollno' => $student->roll_no])
            ->with('success', 'Course enrollment removed.')
            ->withFragment('tab-courses');
    }

    function bulkStudentCourseEnrollment(Request $request)
    {
        $batches = BatchMaster::orderByDesc('id')->get(['id', 'batch_name']);
        $selectedBatchId = (int) $request->query('batch_id');
        $programRows = collect();

        if ($selectedBatchId > 0) {
            $combinations = SubjectHasStudentProgam::with([
                'studentprograminfo:id,code,name',
                'campusmaster:id,name',
            ])
                ->where('batch_id', $selectedBatchId)
                ->orderByDesc('id')
                ->get();

            $combinationIds = $combinations->pluck('id')
                ->map(fn($id) => (int) $id)
                ->filter(fn($id) => $id > 0)
                ->values();

            $subjectIds = $combinations->pluck('subject_id')
                ->map(fn($id) => (int) $id)
                ->filter(fn($id) => $id > 0)
                ->unique()
                ->values();

            $curriculumRowsByCombination = collect();
            $assignmentsBySubjectCourse = collect();

            if ($combinationIds->isNotEmpty()) {
                $curriculumModel = new ProgramWiseSemesterCourse();
                $curriculumTable = $curriculumModel->getTable();
                $hasDisplayOrderColumn = Schema::hasColumn($curriculumTable, 'display_order');
                $hasIsActiveColumn = Schema::hasColumn($curriculumTable, 'is_active');

                $curriculumQuery = ProgramWiseSemesterCourse::with('programinfo:id,course_code,course_title')
                    ->whereIn('program_combo_refid', $combinationIds)
                    ->orderBy('semester');

                if ($hasDisplayOrderColumn) {
                    $curriculumQuery->orderBy('display_order');
                }

                if ($hasIsActiveColumn) {
                    $curriculumQuery->where('is_active', 1);
                }

                $curriculumRows = $curriculumQuery->get([
                    'program_combo_refid',
                    'course_id',
                    'semester',
                    'course_type',
                    'delivery_category',
                ]);

                $curriculumRowsByCombination = $curriculumRows->groupBy(function ($row) {
                    return (int) ($row->program_combo_refid ?? 0);
                });

                $courseIds = $curriculumRows->pluck('course_id')
                    ->map(fn($id) => (int) $id)
                    ->filter(fn($id) => $id > 0)
                    ->unique()
                    ->values();

                if ($courseIds->isNotEmpty()) {
                    $teachingAssignments = TeachingAssignment::with([
                        'faculty:id,FIRST_NAME,LAST_NAME,USER_CODE',
                        'coFacultyMembers:id,FIRST_NAME,LAST_NAME,USER_CODE',
                    ])
                        ->whereIn('course_id', $courseIds)
                        ->when($subjectIds->isNotEmpty(), function ($query) use ($subjectIds) {
                            $query->whereIn('subject_id', $subjectIds);
                        })
                        ->get([
                            'id',
                            'subject_id',
                            'course_id',
                            'faculty_id',
                            'delivery_type',
                            'allocation_group',
                        ]);

                    $assignmentsBySubjectCourse = $teachingAssignments->groupBy(function ($assignment) {
                        return (int) ($assignment->subject_id ?? 0) . '|' . (int) ($assignment->course_id ?? 0);
                    });
                }
            }

            $programRows = $combinations->map(function ($combination) {
                $studentsCount = StudentMaster::where('new_program_id', $combination->student_program_id)
                    ->where('batch', $combination->batch_id)
                    ->where('campus_id', $combination->campus_id)
                    ->where('is_deleted', 0)
                    ->where('is_left', 0)
                    ->count();

                $autoCoursesCount = ProgramWiseSemesterCourse::where('program_combo_refid', $combination->id)
                    ->where('course_type', ProgramWiseSemesterCourse::TYPE_AUTO)
                    ->count();

                return (object) [
                    'combination_id' => (int) $combination->id,
                    'program_code' => (string) optional($combination->studentprograminfo)->code,
                    'program_name' => (string) optional($combination->studentprograminfo)->name,
                    'program_type' => strtoupper((string) ($combination->program_type ?? '')),
                    'campus_name' => (string) optional($combination->campusmaster)->name,
                    'students_count' => (int) $studentsCount,
                    'auto_courses_count' => (int) $autoCoursesCount,
                    'curriculum_done' => $autoCoursesCount > 0,
                ];
            });

            $programRows = $programRows->map(function ($row) use ($combinations, $curriculumRowsByCombination, $assignmentsBySubjectCourse) {
                $combination = $combinations->firstWhere('id', (int) $row->combination_id);
                $subjectId = (int) ($combination->subject_id ?? 0);
                $comboCurriculumRows = $curriculumRowsByCombination->get((int) $row->combination_id, collect());

                $curriculumCourses = $comboCurriculumRows->map(function ($curriculumRow) use ($subjectId, $assignmentsBySubjectCourse) {
                    $courseId = (int) ($curriculumRow->course_id ?? 0);
                    $deliveryType = strtoupper(trim((string) ($curriculumRow->delivery_category ?? $curriculumRow->course_type ?? '-')));
                    $subjectCourseKey = $subjectId . '|' . $courseId;

                    $matchingAssignments = collect($assignmentsBySubjectCourse->get($subjectCourseKey, collect()));

                    $normalizedDeliveryType = preg_replace('/[^A-Z0-9]/', '', $deliveryType);
                    if ($matchingAssignments->isNotEmpty() && $normalizedDeliveryType !== '') {
                        $deliveryMatchedAssignments = $matchingAssignments->filter(function ($assignment) use ($normalizedDeliveryType) {
                            $assignmentDelivery = strtoupper(trim((string) ($assignment->delivery_type ?? '')));
                            $normalizedAssignmentDelivery = preg_replace('/[^A-Z0-9]/', '', $assignmentDelivery);
                            return $normalizedAssignmentDelivery === $normalizedDeliveryType;
                        });

                        if ($deliveryMatchedAssignments->isNotEmpty()) {
                            $matchingAssignments = $deliveryMatchedAssignments;
                        }
                    }

                    $teachers = $matchingAssignments
                        ->map(function ($assignment) {
                            $primaryLabel = trim((string) (optional($assignment->faculty)->FIRST_NAME ?? '') . ' ' . (string) (optional($assignment->faculty)->LAST_NAME ?? ''));
                            $primaryLabel = $primaryLabel !== '' ? $primaryLabel : '-';

                            $coFaculty = collect($assignment->coFacultyMembers ?? [])
                                ->map(function ($faculty) {
                                    return trim((string) ($faculty->FIRST_NAME ?? '') . ' ' . (string) ($faculty->LAST_NAME ?? ''));
                                })
                                ->filter()
                                ->values();

                            if ($coFaculty->isEmpty()) {
                                return $primaryLabel;
                            }

                            return $primaryLabel . ' (Co: ' . $coFaculty->implode(', ') . ')';
                        })
                        ->filter()
                        ->unique()
                        ->values();

                    return [
                        'course_code' => (string) (optional($curriculumRow->programinfo)->course_code ?? '-'),
                        'course_title' => (string) (optional($curriculumRow->programinfo)->course_title ?? '-'),
                        'semester' => (int) ($curriculumRow->semester ?? 0),
                        'course_type' => (string) ($curriculumRow->course_type ?? '-'),
                        'delivery_type' => $deliveryType !== '' ? $deliveryType : '-',
                        'teachers' => $teachers->isNotEmpty() ? $teachers->implode('; ') : 'Not assigned yet',
                    ];
                })
                    ->sortBy([
                        ['semester', 'asc'],
                        ['course_code', 'asc'],
                    ])
                    ->values();

                $row->curriculum_courses = $curriculumCourses->all();
                $row->curriculum_courses_count = $curriculumCourses->count();

                return $row;
            });
        }

        return view('admin.itcell.bulk-enrollment', [
            'batches' => $batches,
            'selectedBatchId' => $selectedBatchId,
            'programRows' => $programRows,
        ]);
    }

    function bulkStudentCourseEnrollmentStore(Request $request)
    {
        $request->validate([
            'batch_id' => 'required|integer|exists:batch_masters,id',
            'program_combination_ids' => 'required|array|min:1',
            'program_combination_ids.*' => 'integer|exists:subject_has_student_progams,id',
            'rollno_action' => 'required|in:reconfigure,dont_reconfigure',
        ]);

        $batchId = (int) $request->batch_id;
        $combinationIds = collect($request->input('program_combination_ids', []))
            ->map(fn($id) => (int) $id)
            ->unique()
            ->values();
        $rollnoAction = (string) $request->rollno_action;

        $batch = BatchMaster::findOrFail($batchId);
        $batchName = (string) ($batch->batch_name ?? date('Y'));
        $combinations = SubjectHasStudentProgam::with([
            'studentprograminfo:id,code,name',
            'campusmaster:id,name',
        ])
            ->where('batch_id', $batchId)
            ->whereIn('id', $combinationIds)
            ->get();

        if ($combinations->isEmpty()) {
            return redirect()->route('bulk.student.course.enrollment', [
                'batch_id' => $batchId,
            ])->with('error', 'No valid program combinations selected for this batch.');
        }

        $now = now();
        $totalStudentsProcessed = 0;
        $totalNewEnrollments = 0;
        $totalSkippedEnrollments = 0;
        $skippedPrograms = [];

        DB::transaction(function () use (
            $combinations,
            $batchName,
            $rollnoAction,
            $now,
            &$totalStudentsProcessed,
            &$totalNewEnrollments,
            &$totalSkippedEnrollments,
            &$skippedPrograms
        ) {
            foreach ($combinations as $combination) {
                $curriculumMappings = ProgramWiseSemesterCourse::where('program_combo_refid', $combination->id)
                    ->whereIn('course_type', [
                        ProgramWiseSemesterCourse::TYPE_AUTO,
                        ProgramWiseSemesterCourse::TYPE_STUDENT_CHOICE,
                        'COMPULSORY',
                        'ELECTIVE',
                    ])
                    ->get([
                        'course_id',
                        'semester',
                        'course_type',
                        'specialization_master_id',
                        'specialization_master_ids',
                    ])
                    ->unique(fn($row) => ((int) $row->course_id) . '_' . ((int) $row->semester) . '_' . strtoupper((string) ($row->course_type ?? '')))
                    ->values();

                if ($curriculumMappings->isEmpty()) {
                    $skippedPrograms[] = trim((optional($combination->studentprograminfo)->code ?? '') . ' - ' . (optional($combination->studentprograminfo)->name ?? 'Program'));
                    continue;
                }

                $students = StudentMaster::where('new_program_id', $combination->student_program_id)
                    ->where('batch', $combination->batch_id)
                    ->where('campus_id', $combination->campus_id)
                    ->where('is_deleted', 0)
                    ->where('is_left', 0)
                    ->orderBy('id')
                    ->get(['id', 'campus_id', 'roll_no', 'new_program_id', 'batch', 'campus_id']);

                if ($students->isEmpty()) {
                    continue;
                }

                if ($rollnoAction === 'reconfigure') {
                    $this->bulkReconfigureRollNo($students, $combination, $batchName);
                }

                $studentIds = $students->pluck('id')->all();
                $academicYear = $batchName;

                $programSpecializationIds = collect($combination->specialization_ids ?? [])
                    ->map(fn($id) => (int) $id)
                    ->filter(fn($id) => $id > 0)
                    ->unique()
                    ->values();

                $studentSpecializationByStudentSemester = [];
                $hasStudentSpecializations = false;
                if (Schema::hasTable('student_specializations')) {
                    $specializationRows = DB::table('student_specializations')
                        ->whereIn('student_id', $studentIds)
                        ->where('subject_has_student_program_id', (int) $combination->id)
                        ->whereNull('deleted_at')
                        ->where('is_active', 1)
                        ->orderByDesc('id')
                        ->get(['student_id', 'specialization_id', 'semester_id']);

                    $hasStudentSpecializations = $specializationRows->isNotEmpty();

                    foreach ($specializationRows as $row) {
                        $studentId = (int) ($row->student_id ?? 0);
                        $specializationId = (int) ($row->specialization_id ?? 0);
                        $semesterId = (int) ($row->semester_id ?? 0);

                        if ($studentId <= 0 || $specializationId <= 0) {
                            continue;
                        }

                        if (!isset($studentSpecializationByStudentSemester[$studentId])) {
                            $studentSpecializationByStudentSemester[$studentId] = [];
                        }

                        // Keep first row due to desc id ordering (latest record wins).
                        if (!isset($studentSpecializationByStudentSemester[$studentId][$semesterId])) {
                            $studentSpecializationByStudentSemester[$studentId][$semesterId] = $specializationId;
                        }
                    }
                }

                $programHasSpecializations = $programSpecializationIds->isNotEmpty() || $hasStudentSpecializations;

                $normalizedMappings = $curriculumMappings->map(function ($row) {
                    $type = strtoupper((string) ($row->course_type ?? ''));
                    $specIds = collect($row->specialization_master_ids ?? [])
                        ->map(fn($id) => (int) $id)
                        ->filter(fn($id) => $id > 0)
                        ->values();

                    $singleSpec = (int) ($row->specialization_master_id ?? 0);
                    if ($singleSpec > 0 && !$specIds->contains($singleSpec)) {
                        $specIds->push($singleSpec);
                    }

                    return (object) [
                        'course_id' => (int) ($row->course_id ?? 0),
                        'semester' => (int) ($row->semester ?? 0),
                        'course_type' => $type,
                        'is_elective' => in_array($type, [ProgramWiseSemesterCourse::TYPE_STUDENT_CHOICE, 'ELECTIVE'], true) ? 1 : 0,
                        'specialization_ids' => $specIds->unique()->values()->all(),
                    ];
                })->filter(fn($row) => $row->course_id > 0 && $row->semester > 0)->values();

                $existingEnrollments = StudentCourseInfo::whereIn('student_id', $studentIds)
                    ->where('academic_year', $academicYear)
                    ->where('is_deleted', 0)
                    ->where(function ($query) use ($normalizedMappings) {
                        foreach ($normalizedMappings as $map) {
                            $query->orWhere(function ($q) use ($map) {
                                $q->where('course_id', (int) $map->course_id)
                                    ->where('semester', (int) $map->semester);
                            });
                        }
                    })
                    ->get(['student_id', 'course_id', 'semester'])
                    ->mapWithKeys(fn($row) => [((int) $row->student_id) . '_' . ((int) $row->course_id) . '_' . ((int) $row->semester) => true]);

                $insertRows = [];

                $studentSpecForSemester = function (int $studentId, int $semester) use ($studentSpecializationByStudentSemester): int {
                    $rows = $studentSpecializationByStudentSemester[$studentId] ?? [];
                    if (isset($rows[$semester]) && (int) $rows[$semester] > 0) {
                        return (int) $rows[$semester];
                    }
                    if (isset($rows[0]) && (int) $rows[0] > 0) {
                        return (int) $rows[0];
                    }
                    return 0;
                };

                foreach ($students as $student) {
                    foreach ($normalizedMappings as $map) {
                        $courseId = (int) $map->course_id;
                        $semester = (int) $map->semester;

                        $mappingSpecIds = collect($map->specialization_ids ?? [])
                            ->map(fn($id) => (int) $id)
                            ->filter(fn($id) => $id > 0)
                            ->values();

                        // If curriculum row is specialization-specific and the program has specializations,
                        // enroll only matching students by specialization_id.
                        if ($programHasSpecializations && $mappingSpecIds->isNotEmpty()) {
                            $studentSpecId = $studentSpecForSemester((int) $student->id, $semester);
                            if ($studentSpecId <= 0 || !$mappingSpecIds->contains($studentSpecId)) {
                                continue;
                            }
                        }

                        $key = ((int) $student->id) . '_' . $courseId . '_' . $semester;

                        if (isset($existingEnrollments[$key])) {
                            $totalSkippedEnrollments++;
                            continue;
                        }

                        $insertRows[] = [
                            'student_id' => (int) $student->id,
                            'course_id' => $courseId,
                            'semester' => $semester,
                            'campus_id' => (int) $student->campus_id,
                            'is_active' => 1,
                            'academic_year' => $academicYear,
                            'is_elective' => (int) ($map->is_elective ?? 0),
                            'created_at' => $now,
                            'updated_at' => $now,
                        ];

                        $totalNewEnrollments++;

                        if (count($insertRows) >= 1000) {
                            DB::table('student_course_infos')->insert($insertRows);
                            $insertRows = [];
                        }
                    }
                }

                if (!empty($insertRows)) {
                    DB::table('student_course_infos')->insert($insertRows);
                }

                $totalStudentsProcessed += $students->count();
            }
        });

        $message = $totalStudentsProcessed . ' student(s) processed. '
            . $totalNewEnrollments . ' enrollment(s) added. '
            . $totalSkippedEnrollments . ' already enrolled (skipped).';

        if (!empty($skippedPrograms)) {
            $message .= ' Skipped (no AUTO curriculum): ' . implode(', ', $skippedPrograms) . '.';
        }

        return redirect()->route('bulk.student.course.enrollment', [
            'batch_id' => $batchId,
        ])->with('success', $message);
    }

    private function bulkReconfigureRollNo($students, SubjectHasStudentProgam $combination, string $batchName): void
    {
        $programCode = strtoupper((string) optional($combination->studentprograminfo)->code);
        if ($programCode === '') {
            $programCode = 'PRG';
        }

        $batchToken = strtoupper(preg_replace('/[^A-Za-z0-9]/', '', $batchName));
        if ($batchToken === '') {
            $batchToken = (string) date('Y');
        }

        $campusToken = ((int) $combination->campus_id) === 1 ? 'SO' : 'SL';
        $prefix = $campusToken . $batchToken . $programCode;

        $counter = 1;
        foreach ($students as $student) {
            $rollNo = $prefix . str_pad((string) $counter, 3, '0', STR_PAD_LEFT);
            StudentMaster::where('id', (int) $student->id)->update(['roll_no' => $rollNo]);
            $counter++;
        }
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
        $data = HourMaster::with('shiftmaster')->get();
        return view('admin.master.hour', ['data' => $data]);
    }

    function addHour(Request $request)
    {
        $hasHourNo = Schema::hasColumn('hour_masters', 'hour_no');

        if ($hasHourNo) {
            $request->validate([
                'hour' => 'required|integer|min:1',
                'shift_id' => 'nullable|integer|min:1',
                'name' => 'nullable|string|max:255',
                'start_time' => 'nullable|date_format:H:i',
                'end_time' => 'nullable|date_format:H:i|after:start_time',
                'is_teaching' => 'nullable|boolean',
                'status' => 'nullable|boolean',
            ]);

            $check = HourMaster::where('hour_no', $request->hour)
                ->when(!empty($request->shift_id), function ($q) use ($request) {
                    return $q->where('shift_id', $request->shift_id);
                })->first();

            if ($check != null) {
                return redirect()->back()->with('success', 'Item already in list');
            }

            $rec = new HourMaster();
            $rec->hour_no = $request->hour;
            $rec->shift_id = $request->shift_id ?? 1;
            $rec->name = $request->name ?: ('Hour ' . $request->hour);
            $rec->start_time = $request->start_time;
            $rec->end_time = $request->end_time;
            $rec->is_teaching = $request->is_teaching ?? 1;
            $rec->status = $request->status ?? 1;
            $rec->save();

            return redirect()->back()->with('success', 'Done');
        }

        $request->validate([
            'hour' => 'required|integer|min:1',
        ]);

        $check = HourMaster::where('title', $request->hour)->first();
        if ($check != null) {
            return redirect()->back()->with('success', 'Item already in list');
        }

        $rec = new HourMaster();
        $rec->title = $request->hour;
        $rec->save();

        return redirect()->back()->with('success', 'Done');
    }

    function updateHour(Request $request, $id)
    {
        $rec = HourMaster::findOrFail($id);
        $hasHourNo = Schema::hasColumn('hour_masters', 'hour_no');

        if ($hasHourNo) {
            $request->validate([
                'hour' => 'required|integer|min:1',
                'shift_id' => 'nullable|integer|min:1',
                'name' => 'nullable|string|max:255',
                'start_time' => 'nullable|date_format:H:i',
                'end_time' => 'nullable|date_format:H:i|after:start_time',
                'is_teaching' => 'nullable|boolean',
                'status' => 'nullable|boolean',
            ]);

            $check = HourMaster::where('hour_no', $request->hour)
                ->when(!empty($request->shift_id), function ($q) use ($request) {
                    return $q->where('shift_id', $request->shift_id);
                })
                ->where('id', '!=', $rec->id)
                ->first();

            if ($check != null) {
                return redirect()->back()->with('success', 'Item already in list');
            }

            $rec->hour_no = $request->hour;
            $rec->shift_id = $request->shift_id ?? $rec->shift_id;
            $rec->name = $request->name ?: ('Hour ' . $request->hour);
            $rec->start_time = $request->start_time;
            $rec->end_time = $request->end_time;
            $rec->is_teaching = $request->is_teaching ?? 1;
            $rec->status = $request->status ?? 1;
            $rec->save();

            return redirect()->back()->with('success', 'Done');
        }

        $request->validate([
            'hour' => 'required|integer|min:1',
        ]);

        $check = HourMaster::where('title', $request->hour)
            ->where('id', '!=', $rec->id)
            ->first();

        if ($check != null) {
            return redirect()->back()->with('success', 'Item already in list');
        }

        $rec->title = $request->hour;
        $rec->save();

        return redirect()->back()->with('success', 'Done');
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
            'academic_pathway_id' => 'required|in:1,2',
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
        $rec->academic_pathway_id = $request->academic_pathway_id; //1 single major, 2 dual major
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
            'batch' => 'required|exists:batch_masters,id',
            'progs' => 'required|array|min:1',
        ]);
        $courseMasterId =  $request->coursemasterId;   //single Id 9 (Course Master Id)
        $progs = $request->progs; //multiple student program ids [1,2,3,4,5]

        $eligibleProgramIds = StudentMaster::where('batch', $request->batch)
            ->whereNotNull('new_program_id')
            ->distinct()
            ->pluck('new_program_id')
            ->map(fn($id) => (int) $id)
            ->toArray();

        $progs = collect($progs)
            ->map(fn($id) => (int) $id)
            ->filter(fn($id) => in_array($id, $eligibleProgramIds))
            ->unique()
            ->values()
            ->toArray();

        if (empty($progs)) {
            return redirect()->back()->with('error', 'No eligible student programs found for the selected batch.');
        }


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

    function fetchStudentProgramsByBatch($batchId)
    {
        $programIds = StudentMaster::where('batch', $batchId)
            ->whereNotNull('new_program_id')
            ->distinct()
            ->pluck('new_program_id')
            ->toArray();

        $programs = StudentProgram::with('campusmaster')
            ->whereIn('id', $programIds)
            ->orderBy('name')
            ->get(['id', 'code', 'name', 'campus_id']);

        return response()->json([
            'success' => true,
            'programs' => $programs,
        ]);
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
            'academic_pathway_id' => 'required|in:1,2',
        ]);
        $id = $request->id;

        FeesStructure::where('id', $id)->update([
            'program_id' => $request->program,
            'academic_pathway_id' => $request->academic_pathway_id,
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
        $batches = BatchMaster::orderBy('batch_name', 'desc')->get(['id', 'batch_name']);

        return view('admin.accounts.fee-course-master', ['data' => $data, 'allcourses' => $allcourses, 'batches' => $batches]);
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

    function subjectSingle(Request $request)
    {
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
        $course_masters = SubjectCourseMaster::with([
            'courseMaster',
            'courseMaster.csos',
            'courseMaster.csos.csosubunits'
        ])->where('subject_id', $subjectId)->get();
        $syllabusByCourse = SyllabusManager::with([
            'batch:id,batch_name',
            'semester:id,title',
            'cso:id,title',
            'syllabusSubunits.csoSubunit:id,title',
        ])
            ->where('subject_id', $subjectId)
            ->whereNotNull('co_id')
            ->orderByDesc('id')
            ->get()
            ->groupBy('co_id');
        $faculties = SubjectFacultyMaster::with('faculty')->where('subject_id', $subjectId)->get();

        return view('admin.itcell.dept-manager', [
            'data' => $courseMaster,
            'students_count' => $studentsCount,
            'semesters_count' => $semestersCount,
            'batchWiseStudents' => $batchWiseStudents,
            'combinations' => $combinations,
            'programs' => $programs,
            'course_masters' => $course_masters,
            'syllabus_by_course' => $syllabusByCourse,
            'deptfaculties' => $faculties,
        ]);
    }

    function subjectCourseUnlinker(int $id)
    {
        $data = SubjectCourseMaster::find($id);
        SubjectCourseMaster::where('id', $id)->delete();

        return redirect()->back()->with('success', 'Course Unlinked Successfully');
    }

    function semesterEngine()
    {
        $data = ProgramTrackConfiguration::latest()->get();
        $pathways = AcademicPathwayMaster::orderBy('name')->get();
        $degreeTracks = DegreeTrackMaster::orderBy('name')->get();

        return view('admin.itcell.semester-engine', [
            'data' => $data,
            'pathways' => $pathways,
            'degreeTracks' => $degreeTracks,
        ]);
    }

    function semesterEngineStore(Request $request)
    {
        $request->validate([
            'programs' => 'required|array|min:1',
            'programs.*' => 'required|integer|exists:student_program,id',
            'configs' => 'required|array|min:1',
            'configs.*.effective_semester' => 'required|integer|min:1|max:20',
            'configs.*.allowed_pathway_id' => 'required|integer|exists:academic_pathway_masters,id',
            'configs.*.allowed_degree_track_id' => 'required|integer|exists:degree_track_masters,id',
        ]);

        $selectedProgramIds = collect($request->programs)
            ->filter(fn($id) => !empty($id))
            ->map(fn($id) => (int) $id)
            ->unique()
            ->values();

        if ($selectedProgramIds->isEmpty()) {
            return redirect()->back()->with('error', 'Please select at least one program.');
        }

        $selectedPrograms = StudentProgram::whereIn('id', $selectedProgramIds)->get()->keyBy('id');

        $normalizedConfigs = collect($request->configs)
            ->filter(function ($row) {
                return !empty($row['effective_semester']) && !empty($row['allowed_pathway_id']) && !empty($row['allowed_degree_track_id']);
            })
            ->map(function ($row) {
                return [
                    'effective_semester' => (int) $row['effective_semester'],
                    'allowed_pathway_id' => (int) $row['allowed_pathway_id'],
                    'allowed_degree_track_id' => (int) $row['allowed_degree_track_id'],
                ];
            })
            ->unique(function ($row) {
                return $row['effective_semester'] . '-' . $row['allowed_pathway_id'] . '-' . $row['allowed_degree_track_id'];
            })
            ->values();

        if ($normalizedConfigs->isEmpty()) {
            return redirect()->back()->with('error', 'Please add at least one valid configuration row.');
        }

        $createdCount = 0;
        $updatedCount = 0;

        DB::beginTransaction();
        try {
            foreach ($selectedProgramIds as $programId) {
                $program = $selectedPrograms->get($programId);
                if (!$program) {
                    continue;
                }

                foreach ($normalizedConfigs as $row) {
                    $record = ProgramTrackConfiguration::withTrashed()->where([
                        'program_id' => $program->id,
                        'effective_semester' => $row['effective_semester'],
                        'allowed_pathway_id' => (string) $row['allowed_pathway_id'],
                        'allowed_degree_track_id' => (string) $row['allowed_degree_track_id'],
                    ])->first();

                    if ($record) {
                        $wasDeleted = !is_null($record->deleted_at);
                        $record->coode = $program->code;
                        $record->title = $program->name;
                        if ($wasDeleted) {
                            $record->deleted_at = null;
                        }
                        $record->save();
                        $updatedCount++;
                        continue;
                    }

                    $newRecord = new ProgramTrackConfiguration();
                    $newRecord->program_id = $program->id;
                    $newRecord->coode = $program->code;
                    $newRecord->title = $program->name;
                    $newRecord->effective_semester = $row['effective_semester'];
                    $newRecord->allowed_pathway_id = (string) $row['allowed_pathway_id'];
                    $newRecord->allowed_degree_track_id = (string) $row['allowed_degree_track_id'];
                    $newRecord->save();
                    $createdCount++;
                }
            }

            DB::commit();
            return redirect()->back()->with('success', "Semester Engine settings saved. Created: {$createdCount}, Updated: {$updatedCount}.");
        } catch (\Throwable $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Failed to save Semester Engine settings: ' . $e->getMessage());
        }
    }

    function semesterEngineUpdate(Request $request, int $id)
    {
        $request->validate([
            'program_id' => 'required|integer|exists:student_program,id',
            'effective_semester' => 'required|integer|min:1|max:20',
            'allowed_pathway_id' => 'required|integer|exists:academic_pathway_masters,id',
            'allowed_degree_track_id' => 'required|integer|exists:degree_track_masters,id',
        ]);

        $record = ProgramTrackConfiguration::findOrFail($id);
        $program = StudentProgram::findOrFail((int) $request->program_id);

        $duplicate = ProgramTrackConfiguration::where('id', '!=', $record->id)
            ->where('program_id', (int) $request->program_id)
            ->where('effective_semester', (int) $request->effective_semester)
            ->where('allowed_pathway_id', (string) $request->allowed_pathway_id)
            ->where('allowed_degree_track_id', (string) $request->allowed_degree_track_id)
            ->exists();

        if ($duplicate) {
            return redirect()->back()->with('error', 'Duplicate configuration exists for this program and semester track combination.');
        }

        $record->program_id = (int) $request->program_id;
        $record->coode = $program->code;
        $record->title = $program->name;
        $record->effective_semester = (int) $request->effective_semester;
        $record->allowed_pathway_id = (string) $request->allowed_pathway_id;
        $record->allowed_degree_track_id = (string) $request->allowed_degree_track_id;
        $record->save();

        return redirect()->back()->with('success', 'Semester engine rule updated successfully.');
    }

    function semesterEngineDelete(int $id)
    {
        $record = ProgramTrackConfiguration::findOrFail($id);
        $record->delete();

        return redirect()->back()->with('success', 'Semester engine rule deleted successfully.');
    }
}
