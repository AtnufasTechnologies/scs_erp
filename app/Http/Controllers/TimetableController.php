<?php

namespace App\Http\Controllers;

use App\Models\BatchMaster;
use App\Models\Faculty;
use App\Models\FacultySubstitution;
use App\Models\HourMaster;
use App\Models\LectureHallMaster;
use App\Models\ProgramCourseMaster;
use App\Models\ProgramWiseSemesterCourse;
use App\Models\Semester;
use App\Models\Subject;
use App\Models\SubjectCourseMaster;
use App\Models\SubjectFacultyMaster;
use App\Models\SubjectHasRoutine;
use App\Models\SubjectHasSemester;
use App\Models\SubjectHasSyllabus;
use App\Models\SubjectHasStudentProgam;
use App\Models\TeachingAssignment;
use App\Models\ShiftMaster;
use App\Models\Weekday;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\SubstitutionHistoryExport;
use App\Services\TimetableConflictService;
use Illuminate\Bus\Batch;

class TimetableController extends Controller
{
    function index(int $id, $slug)
    {
        $data = Subject::find($id);
        if (!$data) {
            return redirect()->back()->with('error', 'Subject not found.');
        }

        $teachingAssignments = TeachingAssignment::with([
            'course.coursetypemaster:id,title',
            'faculty:id,USER_CODE,FIRST_NAME,LAST_NAME',
            'primaryFacultyMembers:id,USER_CODE,FIRST_NAME,LAST_NAME',
            'coFacultyMembers:id,USER_CODE,FIRST_NAME,LAST_NAME',
        ])
            ->where('subject_id', $id)
            ->where('is_active', 1)
            ->orderBy('allocation_group')
            ->orderBy('id')
            ->get();

        $allTeachingAssignments = TeachingAssignment::with([
            'course:id,course_code,course_title',
            'faculty:id,USER_CODE,FIRST_NAME,LAST_NAME',
            'primaryFacultyMembers:id,USER_CODE,FIRST_NAME,LAST_NAME',
            'coFacultyMembers:id,USER_CODE,FIRST_NAME,LAST_NAME',
        ])
            ->where('subject_id', $id)
            ->orderByDesc('id')
            ->get();

        $mappedCourses = SubjectCourseMaster::where('subject_id', $id)
            ->with('courseMaster:id,course_code,course_title')
            ->get();

        $courseMasterIds = $mappedCourses->pluck('course_master_id')->filter()->unique()->values();
        $teachingCourseIds = $teachingAssignments->pluck('course_id')->filter()->unique()->values();
        $courseMasterIds = $courseMasterIds->merge($teachingCourseIds)->unique()->values();

        $courseLabelMap = $mappedCourses
            ->filter(fn($item) => $item->courseMaster)
            ->mapWithKeys(function ($item) {
                return [
                    (int) $item->courseMaster->id => trim(($item->courseMaster->course_code ?? '-') . ' - ' . ($item->courseMaster->course_title ?? 'N/A')),
                ];
            });

        $teachingCourseLabelMap = $teachingAssignments
            ->filter(fn($assignment) => $assignment->course)
            ->mapWithKeys(function ($assignment) {
                return [
                    (int) $assignment->course_id => trim(($assignment->course->course_code ?? '-') . ' - ' . ($assignment->course->course_title ?? 'N/A')),
                ];
            });

        $courseLabelMap = $courseLabelMap->merge($teachingCourseLabelMap);

        $curriculumDeliveryRows = ProgramWiseSemesterCourse::query()
            ->whereIn('course_id', $courseMasterIds)
            ->where('offering_dept', $id)
            ->get([
                'course_id',
                'batch',
                'semester',
                'offering_dept',
                'delivery_category',
                'course_type',
                'specialization_mode',
                'specialization_master_id',
                'specialization_master_ids',
            ])
            ->map(function ($row) {
                $specializationIds = [];
                if (is_array($row->specialization_master_ids)) {
                    $specializationIds = array_values(array_filter($row->specialization_master_ids, fn($val) => !is_null($val) && $val !== ''));
                }

                return [
                    'course_id' => (int) $row->course_id,
                    'batch' => (int) $row->batch,
                    'semester' => (int) $row->semester,
                    'offering_dept' => (int) ($row->offering_dept ?? 0),
                    'delivery_category' => (string) $row->delivery_category,
                    'selector' => (string) ($row->course_type ?? ''),
                    'course_type' => (string) ($row->course_type ?? ''),
                    'specialization_mode' => (string) ($row->specialization_mode ?? ''),
                    'specialization_master_id' => (int) ($row->specialization_master_id ?? 0),
                    'specialization_master_ids' => $specializationIds,
                ];
            })
            ->map(function ($row) use ($courseLabelMap) {
                $row['course_label'] = $courseLabelMap[(int) $row['course_id']] ?? ('Course #' . $row['course_id']);
                return $row;
            })
            ->values();

        $mappedFaculties = SubjectFacultyMaster::where('subject_id', $id)
            ->with('faculty:id,USER_CODE,FIRST_NAME,LAST_NAME')
            ->get();

        $courses = $teachingAssignments
            ->filter(fn($assignment) => $assignment->course)
            ->unique('course_id')
            ->values();

        $faculties = $teachingAssignments
            ->flatMap(function ($assignment) {
                $primaryFaculty = $assignment->primaryFacultyMembers ?? collect();
                if ($primaryFaculty->isNotEmpty()) {
                    return $primaryFaculty;
                }

                return $assignment->faculty ? collect([$assignment->faculty]) : collect();
            })
            ->unique('id')
            ->values();

        $facultyDeliveryTypes = [];
        foreach ($teachingAssignments as $assignment) {
            if (empty($assignment->delivery_type)) {
                continue;
            }

            $assignmentFacultyIds = collect($assignment->allAssignedFacultyIds())
                ->map(fn($id) => (int) $id)
                ->filter(fn($id) => $id > 0)
                ->unique()
                ->values();

            if ($assignmentFacultyIds->isEmpty() && !empty($assignment->faculty_id)) {
                $assignmentFacultyIds = collect([(int) $assignment->faculty_id]);
            }

            foreach ($assignmentFacultyIds as $facultyId) {
                if (!isset($facultyDeliveryTypes[$facultyId])) {
                    $facultyDeliveryTypes[$facultyId] = [];
                }

                if (!in_array($assignment->delivery_type, $facultyDeliveryTypes[$facultyId], true)) {
                    $facultyDeliveryTypes[$facultyId][] = $assignment->delivery_type;
                }
            }
        }

        $assignmentMeta = [];
        foreach ($teachingAssignments as $assignment) {
            if (empty($assignment->course_id)) {
                continue;
            }

            $assignmentFacultyIds = collect($assignment->allAssignedFacultyIds())
                ->map(fn($id) => (int) $id)
                ->filter(fn($id) => $id > 0)
                ->unique()
                ->values();

            if ($assignmentFacultyIds->isEmpty() && !empty($assignment->faculty_id)) {
                $assignmentFacultyIds = collect([(int) $assignment->faculty_id]);
            }

            foreach ($assignmentFacultyIds as $facultyId) {
                $key = $assignment->course_id . '|' . $facultyId;
                if (!isset($assignmentMeta[$key])) {
                    $assignmentMeta[$key] = [
                        'delivery_type' => $assignment->delivery_type,
                        'allocation_group' => $assignment->allocation_group,
                        'allocation_group_label' => $assignment->allocation_group_label,
                    ];
                }
            }
        }

        $teachingAssignmentOptions = $teachingAssignments
            ->filter(fn($assignment) => $assignment->course)
            ->map(function ($assignment) {
                $primaryFacultyCollection = $assignment->primaryFacultyMembers ?? collect();
                if ($primaryFacultyCollection->isEmpty() && $assignment->faculty) {
                    $primaryFacultyCollection = collect([$assignment->faculty]);
                }

                $primaryFacultyLabels = $primaryFacultyCollection
                    ->map(fn($faculty) => trim((string) ($faculty->USER_CODE ?? '-') . ' - ' . (string) ($faculty->FIRST_NAME ?? '-') . ' ' . (string) ($faculty->LAST_NAME ?? '')))
                    ->filter()
                    ->values()
                    ->all();

                $primaryFacultyIds = $primaryFacultyCollection
                    ->pluck('id')
                    ->map(fn($id) => (int) $id)
                    ->filter(fn($id) => $id > 0)
                    ->unique()
                    ->values()
                    ->all();

                $facultyName = trim(($assignment->faculty->FIRST_NAME ?? '') . ' ' . ($assignment->faculty->LAST_NAME ?? ''));
                $facultyLabel = !empty($primaryFacultyLabels)
                    ? implode(', ', $primaryFacultyLabels)
                    : trim(($assignment->faculty->USER_CODE ?? '') . ' - ' . $facultyName);

                return [
                    'id' => $assignment->id,
                    'course_id' => $assignment->course_id,
                    'faculty_id' => $assignment->faculty_id,
                    'primary_faculty_ids' => $primaryFacultyIds,
                    'primary_faculty_text' => $primaryFacultyLabels,
                    'co_faculty_ids' => $assignment->coFacultyMembers
                        ->pluck('id')
                        ->map(fn($id) => (int) $id)
                        ->values()
                        ->all(),
                    'co_faculty_label' => $assignment->coFacultyMembers
                        ->map(fn($faculty) => trim((string) ($faculty->USER_CODE ?? '-') . ' - ' . (string) ($faculty->FIRST_NAME ?? '-') . ' ' . (string) ($faculty->LAST_NAME ?? '')))
                        ->filter()
                        ->values()
                        ->all(),
                    'delivery_type' => $assignment->delivery_type,
                    'allocation_group' => $assignment->allocation_group,
                    'allocation_group_label' => $assignment->allocation_group_label,
                    'course_label' => trim(
                        ($assignment->course->coursetypemaster->title ?? '') .
                            ' - ' .
                            ($assignment->course->course_code ?? '-') .
                            ' - ' .
                            ($assignment->course->course_title ?? 'N/A')
                    ),
                    'faculty_label' => $facultyLabel,
                ];
            })
            ->values();

        $teachingAssignmentList = $allTeachingAssignments
            ->map(function ($assignment) {
                $primaryFacultyCollection = $assignment->primaryFacultyMembers ?? collect();
                if ($primaryFacultyCollection->isEmpty() && $assignment->faculty) {
                    $primaryFacultyCollection = collect([$assignment->faculty]);
                }

                $primaryFacultyText = $primaryFacultyCollection
                    ->map(fn($faculty) => trim((string) ($faculty->USER_CODE ?? '-') . ' - ' . (string) ($faculty->FIRST_NAME ?? '-') . ' ' . (string) ($faculty->LAST_NAME ?? '')))
                    ->filter()
                    ->values()
                    ->all();

                return [
                    'id' => $assignment->id,
                    'subject_id' => $assignment->subject_id,
                    'course_id' => $assignment->course_id,
                    'faculty_id' => $assignment->faculty_id,
                    'primary_faculty_ids' => $primaryFacultyCollection
                        ->pluck('id')
                        ->map(fn($id) => (int) $id)
                        ->filter(fn($id) => $id > 0)
                        ->unique()
                        ->values()
                        ->all(),
                    'primary_faculty_text' => $primaryFacultyText,
                    'co_faculty_ids' => $assignment->coFacultyMembers
                        ->pluck('id')
                        ->map(fn($id) => (int) $id)
                        ->values()
                        ->all(),
                    'co_faculty_text' => $assignment->coFacultyMembers
                        ->map(fn($faculty) => trim((string) ($faculty->USER_CODE ?? '-') . ' - ' . (string) ($faculty->FIRST_NAME ?? '-') . ' ' . (string) ($faculty->LAST_NAME ?? '')))
                        ->filter()
                        ->values()
                        ->all(),
                    'course_text' => trim(($assignment->course->course_code ?? '-') . ' - ' . ($assignment->course->course_title ?? '-')),
                    'faculty_text' => !empty($primaryFacultyText)
                        ? implode(', ', $primaryFacultyText)
                        : trim(($assignment->faculty->USER_CODE ?? '-') . ' - ' . ($assignment->faculty->FIRST_NAME ?? '-') . ' ' . ($assignment->faculty->LAST_NAME ?? '')),
                    'delivery_type' => $assignment->delivery_type,
                    'allocation_group' => $assignment->allocation_group,
                    'allocation_group_label' => $assignment->allocation_group_label,
                    'is_active' => (int) $assignment->is_active,
                    'status_label' => (int) $assignment->is_active === 1 ? 'Active' : 'Inactive',
                    'room' => $assignment->room ?: '-',
                    'remarks' => $assignment->remarks ?: '-',
                    'room_raw' => $assignment->room ?? '',
                    'remarks_raw' => $assignment->remarks ?? '',
                ];
            })
            ->values();

        $subjectUsesShifts = (int) ($data->has_shift_delivery ?? 0) === 1;
        $enabledShiftIds = $this->getSubjectEnabledShiftIds($data);
        $shiftOptionsQuery = ShiftMaster::where('is_active', 1)
            ->orderBy('sort_order');
        if ($subjectUsesShifts && !empty($enabledShiftIds)) {
            $shiftOptionsQuery->whereIn('id', $enabledShiftIds);
        }
        $shiftOptions = $shiftOptionsQuery->get(['id', 'title', 'slug']);

        if ($subjectUsesShifts && $shiftOptions->isEmpty()) {
            $shiftOptions = ShiftMaster::where('slug', $this->getDefaultShiftSlug())
                ->where('is_active', 1)
                ->get(['id', 'title', 'slug']);
        }
        $subjectSemesters = SubjectHasSemester::where('subject_id', $id)
            ->with([
                'semestermaster:id,title',
                'batchmaster:id,batch_name',
            ])
            ->orderBy('batch_id')
            ->orderBy('semester_id')
            ->get();
        $semesterMasters = Semester::orderBy('title')->get();
        $batches = BatchMaster::latest()->get();
        $lectureHalls = LectureHallMaster::orderBy('title')->get(['id', 'title']);

        return view('admin.subject.timetable', [
            'data' => $data,
            'subjectSemesters' => $subjectSemesters,
            'semesterMasters' => $semesterMasters,
            'batches' => $batches,
            'lectureHalls' => $lectureHalls,
            'courses' => $courses,
            'faculties' => $faculties,
            'mappedCourses' => $mappedCourses,
            'mappedFaculties' => $mappedFaculties,
            'curriculumDeliveryRows' => $curriculumDeliveryRows,
            'assignmentMeta' => $assignmentMeta,
            'facultyDeliveryTypes' => $facultyDeliveryTypes,
            'teachingAssignmentOptions' => $teachingAssignmentOptions,
            'teachingAssignmentList' => $teachingAssignmentList,
            'subjectUsesShifts' => $subjectUsesShifts,
            'shiftOptions' => $shiftOptions,
        ]);
    }

    function history(int $id)
    {
        $data = Subject::find($id);
        if (!$data) {
            return redirect()->back()->with('error', 'Subject not found.');
        }

        $subjectUsesShifts = (int) ($data->has_shift_delivery ?? 0) === 1;
        $enabledShiftIds = $this->getSubjectEnabledShiftIds($data);

        $shiftOptionsQuery = ShiftMaster::query()->orderBy('sort_order');
        if ($subjectUsesShifts && !empty($enabledShiftIds)) {
            $shiftOptionsQuery->whereIn('id', $enabledShiftIds);
        }

        $shiftOptions = $shiftOptionsQuery->get(['id', 'title', 'slug']);
        if ($subjectUsesShifts && $shiftOptions->isEmpty()) {
            $shiftOptions = ShiftMaster::where('slug', $this->getDefaultShiftSlug())
                ->get(['id', 'title', 'slug']);
        }

        $shiftTitleMap = $shiftOptions
            ->mapWithKeys(fn($row) => [(string) $row->slug => (string) $row->title])
            ->all();

        $syllabi = SubjectHasSyllabus::query()
            ->where('subject_id', $id)
            ->with([
                'batchmaster:id,batch_name',
                'semestermaster:id,title',
            ])
            ->orderBy('batch_id')
            ->orderBy('semester_id')
            ->get(['id', 'subject_id', 'batch_id', 'semester_id', 'program_type']);

        $syllabusIds = $syllabi->pluck('id')->filter()->values();

        $routines = collect();
        if ($syllabusIds->isNotEmpty()) {
            $routines = SubjectHasRoutine::query()
                ->whereIn('syllabus_id', $syllabusIds)
                ->with([
                    'syllabus:id,batch_id,semester_id,program_type,course_id',
                    'syllabus.batchmaster:id,batch_name',
                    'syllabus.semestermaster:id,title',
                    'weekdaymaster:id,title',
                    'hourmaster:id,hour_no,name,start_time,end_time,shift_id',
                    'lecturehallmaster:id,title',
                    'teachingAssignment:id,course_id,faculty_id,delivery_type,allocation_group',
                    'teachingAssignment.primaryFacultyMembers:id,USER_CODE,FIRST_NAME,LAST_NAME',
                    'teachingAssignment.coFacultyMembers:id,USER_CODE,FIRST_NAME,LAST_NAME',
                    'teachingAllocation:id,course_id,faculty_id,delivery_type,allocation_group',
                    'teachingAllocation.primaryFacultyMembers:id,USER_CODE,FIRST_NAME,LAST_NAME',
                    'teachingAllocation.coFacultyMembers:id,USER_CODE,FIRST_NAME,LAST_NAME',
                ])
                ->orderBy('batch_id')
                ->orderBy('syllabus_id')
                ->orderBy('weekday_id')
                ->orderBy('hour_id')
                ->get();
        }

        $days = collect([
            1 => 'Monday',
            2 => 'Tuesday',
            3 => 'Wednesday',
            4 => 'Thursday',
            5 => 'Friday',
            6 => 'Saturday',
        ]);

        $defaultShiftSlug = $this->getDefaultShiftSlug();
        $defaultShiftsForEmptyGroups = $shiftOptions->pluck('slug')->filter()->values();
        if ($defaultShiftsForEmptyGroups->isEmpty()) {
            $defaultShiftsForEmptyGroups = collect([$defaultShiftSlug]);
        }

        $groups = [];

        foreach ($syllabi as $syllabus) {
            $batchId = (int) ($syllabus->batch_id ?? 0);
            $semesterId = (int) ($syllabus->semester_id ?? 0);
            $programType = strtoupper(trim((string) ($syllabus->program_type ?? 'UG')));
            $programType = $programType === 'PG' ? 'PG' : 'UG';

            $comboKey = $batchId . '|' . $semesterId . '|' . $programType;
            $shiftsForCombo = $routines
                ->filter(function ($routine) use ($batchId, $semesterId, $programType) {
                    return (int) ($routine->batch_id ?? 0) === $batchId
                        && (int) ($routine->syllabus->semester_id ?? 0) === $semesterId
                        && strtoupper(trim((string) ($routine->program_type ?? $routine->syllabus->program_type ?? 'UG'))) === $programType;
                })
                ->pluck('shift')
                ->map(fn($value) => trim((string) $value))
                ->filter()
                ->unique()
                ->values();

            if ($shiftsForCombo->isEmpty()) {
                $shiftsForCombo = $defaultShiftsForEmptyGroups;
            }

            foreach ($shiftsForCombo as $shiftSlug) {
                $groupKey = $comboKey . '|' . $shiftSlug;
                if (isset($groups[$groupKey])) {
                    continue;
                }

                $groups[$groupKey] = [
                    'batch_id' => $batchId,
                    'batch_name' => (string) ($syllabus->batchmaster->batch_name ?? ('Batch ' . $batchId)),
                    'semester_id' => $semesterId,
                    'semester_title' => (string) ($syllabus->semestermaster->title ?? ('Semester ' . $semesterId)),
                    'program_type' => $programType,
                    'shift' => (string) $shiftSlug,
                    'shift_title' => (string) ($shiftTitleMap[(string) $shiftSlug] ?? ucfirst((string) $shiftSlug)),
                    'entries' => [],
                    'hours' => [],
                ];
            }
        }

        $fallbackFacultyIds = $routines
            ->map(function ($routine) {
                $assignment = $routine->teachingAssignment ?: $routine->teachingAllocation;
                return (int) ($assignment->faculty_id ?? 0);
            })
            ->filter(fn($id) => $id > 0)
            ->unique()
            ->values();

        $fallbackFacultyMap = $fallbackFacultyIds->isNotEmpty()
            ? Faculty::query()
            ->whereIn('id', $fallbackFacultyIds)
            ->get(['id', 'USER_CODE', 'FIRST_NAME', 'LAST_NAME'])
            ->keyBy('id')
            : collect();

        foreach ($routines as $routine) {
            $syllabus = $routine->syllabus;
            if (!$syllabus) {
                continue;
            }

            $batchId = (int) ($routine->batch_id ?? $syllabus->batch_id ?? 0);
            $semesterId = (int) ($syllabus->semester_id ?? 0);
            $programType = strtoupper(trim((string) ($routine->program_type ?? $syllabus->program_type ?? 'UG')));
            $programType = $programType === 'PG' ? 'PG' : 'UG';
            $shiftSlug = trim((string) ($routine->shift ?: $defaultShiftSlug));

            $groupKey = $batchId . '|' . $semesterId . '|' . $programType . '|' . $shiftSlug;
            if (!isset($groups[$groupKey])) {
                $groups[$groupKey] = [
                    'batch_id' => $batchId,
                    'batch_name' => (string) ($syllabus->batchmaster->batch_name ?? ('Batch ' . $batchId)),
                    'semester_id' => $semesterId,
                    'semester_title' => (string) ($syllabus->semestermaster->title ?? ('Semester ' . $semesterId)),
                    'program_type' => $programType,
                    'shift' => (string) $shiftSlug,
                    'shift_title' => (string) ($shiftTitleMap[(string) $shiftSlug] ?? ucfirst((string) $shiftSlug)),
                    'entries' => [],
                    'hours' => [],
                ];
            }

            $hourNo = (int) ($routine->hourmaster->hour_no ?? $routine->hour_id ?? 0);

            $dayName = (string) ($days[(int) ($routine->weekday_id ?? 0)] ?? '');
            if ($dayName === '') {
                $rawDayTitle = strtoupper(trim((string) ($routine->weekdaymaster->title ?? '')));
                $dayAliasMap = [
                    'MONDAY' => 'Monday',
                    'TUESDAY' => 'Tuesday',
                    'WEDNESDAY' => 'Wednesday',
                    'THURSDAY' => 'Thursday',
                    'FRIDAY' => 'Friday',
                    'SATURDAY' => 'Saturday',
                ];
                $dayName = (string) ($dayAliasMap[$rawDayTitle] ?? '');
            }

            if ($hourNo <= 0 || $dayName === '') {
                continue;
            }

            $assignment = $routine->teachingAssignment ?: $routine->teachingAllocation;

            $courseLabel = trim((string) (
                optional(optional($assignment)->course)->course_code
                ? optional($assignment->course)->course_code . ' - ' . optional($assignment->course)->course_title
                : (optional($syllabus->coursemaster)->course_code ? optional($syllabus->coursemaster)->course_code . ' - ' . optional($syllabus->coursemaster)->course_title : 'Course')
            ));

            $primaryFacultyLabels = collect($assignment?->primaryFacultyMembers ?? [])
                ->map(fn($faculty) => trim((string) ($faculty->USER_CODE ?? '-') . ' - ' . (string) ($faculty->FIRST_NAME ?? '-') . ' ' . (string) ($faculty->LAST_NAME ?? '-')))
                ->filter()
                ->values();

            if ($primaryFacultyLabels->isEmpty() && !empty($assignment?->faculty_id)) {
                $faculty = $fallbackFacultyMap->get((int) $assignment->faculty_id);
                if ($faculty) {
                    $primaryFacultyLabels = collect([
                        trim((string) ($faculty->USER_CODE ?? '-') . ' - ' . (string) ($faculty->FIRST_NAME ?? '-') . ' ' . (string) ($faculty->LAST_NAME ?? '-')),
                    ]);
                }
            }

            $delivery = trim((string) ($assignment->delivery_type ?? ''));
            $allocation = trim((string) ($assignment->allocation_group_label ?? ''));
            $room = trim((string) (optional($routine->lecturehallmaster)->title ?? '-'));

            $groups[$groupKey]['entries'][$hourNo][$dayName][] = [
                'course' => $courseLabel !== '' ? $courseLabel : 'Course',
                'faculty' => $primaryFacultyLabels->isNotEmpty() ? implode(', ', $primaryFacultyLabels->all()) : '-',
                'delivery' => $delivery,
                'allocation' => $allocation,
                'room' => $room !== '' ? $room : '-',
            ];

            $hourName = (string) ($routine->hourmaster->name ?? ('Hour ' . $hourNo));
            $start = (string) ($routine->hourmaster->start_time ?? '');
            $end = (string) ($routine->hourmaster->end_time ?? '');
            $hourLabel = $hourName;
            if ($start !== '' && $end !== '') {
                $hourLabel .= ' (' . $start . ' - ' . $end . ')';
            }

            $groups[$groupKey]['hours'][$hourNo] = [
                'hour_no' => $hourNo,
                'label' => $hourLabel,
            ];
        }

        $groupCollection = collect($groups)
            ->map(function ($group) use ($days) {
                $hours = collect($group['hours'])
                    ->sortBy('hour_no')
                    ->values();

                if ($hours->isEmpty()) {
                    $hours = collect(range(1, 6))->map(fn($hourNo) => [
                        'hour_no' => $hourNo,
                        'label' => 'Hour ' . $hourNo,
                    ]);
                }

                $group['hours'] = $hours->all();
                $group['days'] = $days->values()->all();
                return $group;
            })
            ->sortBy([
                ['batch_id', 'desc'],
                ['semester_id', 'asc'],
                ['program_type', 'asc'],
                ['shift_title', 'asc'],
            ])
            ->values();

        return view('admin.subject.timetable-history', [
            'data' => $data,
            'groups' => $groupCollection,
            'totalGroups' => $groupCollection->count(),
        ]);
    }

    function getTeachingHoursByShift(Request $request)
    {
        try {
            $shiftId = (int) $request->get('shift_id');

            if ($shiftId <= 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Shift is required.'
                ], 422);
            }

            $hours = HourMaster::query()
                ->where('shift_id', $shiftId)
                ->where('status', 1)
                ->where('is_teaching', 1)
                ->orderBy('hour_no')
                ->get(['id', 'hour_no', 'name', 'start_time', 'end_time'])
                ->map(function ($hour) {
                    return [
                        'id' => (int) $hour->id,
                        'hour_no' => (int) $hour->hour_no,
                        'name' => (string) ($hour->name ?? ''),
                        'start_time' => (string) ($hour->start_time ?? ''),
                        'end_time' => (string) ($hour->end_time ?? ''),
                    ];
                })
                ->values();

            return response()->json([
                'success' => true,
                'data' => $hours,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch hours: ' . $e->getMessage(),
            ], 500);
        }
    }

    function getQuickCourses(Request $request)
    {
        try {
            $batchId = (int) $request->get('batch_id');
            $semesterId = (int) $request->get('semester_id');
            $subjectId = (int) $request->get('subject_id');

            if ($batchId <= 0 || $semesterId <= 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Batch and semester are required.'
                ], 422);
            }

            $activeCourseIdsQuery = TeachingAssignment::query()
                ->where('is_active', 1)
                ->whereNotNull('course_id');

            if ($subjectId > 0) {
                $activeCourseIdsQuery->where('subject_id', $subjectId);
            }

            $activeCourseIds = $activeCourseIdsQuery
                ->pluck('course_id')
                ->filter()
                ->unique()
                ->values();

            if ($activeCourseIds->isEmpty()) {
                return response()->json([
                    'success' => true,
                    'data' => [],
                ]);
            }

            $rows = ProgramWiseSemesterCourse::query()
                ->where('batch', $batchId)
                ->where('semester', $semesterId)
                ->whereIn('course_id', $activeCourseIds)
                ->select('course_id')
                ->distinct()
                ->orderBy('course_id')
                ->get();

            $courseIds = $rows->pluck('course_id')->map(fn($id) => (int) $id)->unique()->values();

            $courseTitles = ProgramCourseMaster::query()
                ->whereIn('id', $courseIds)
                ->get(['id', 'course_code', 'course_title'])
                ->keyBy('id');

            $data = $courseIds->map(function ($courseId) use ($courseTitles) {
                $course = $courseTitles->get($courseId);
                $label = $course
                    ? trim(($course->course_code ?? '-') . ' - ' . ($course->course_title ?? '-'))
                    : ('Course #' . $courseId);

                return [
                    'course_id' => $courseId,
                    'course_label' => $label,
                ];
            })->values();

            return response()->json([
                'success' => true,
                'data' => $data,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch quick courses: ' . $e->getMessage(),
            ], 500);
        }
    }

    function editSemesterTimetable($subjectId, $batchId, $semesterId)
    {
        $data = Subject::findOrFail($subjectId);
        $batch = BatchMaster::find($batchId);
        $semester = Semester::find($semesterId);

        $syllabi = SubjectHasSyllabus::where('subject_id', $subjectId)
            ->where('batch_id', $batchId)
            ->where('semester_id', $semesterId)
            ->get();

        $routines = SubjectHasRoutine::whereIn('syllabus_id', $syllabi->pluck('id'))
            ->with([
                'weekdaymaster:id,title',
                'hourmaster:id,title',
                'lecturehallmaster:id,title',
            ])
            ->get();

        $weekdays = Weekday::orderBy('id')->get();
        $hours = HourMaster::orderBy('id')->get();
        $lectureHalls = LectureHallMaster::orderBy('title')->get();

        return view('admin.subject.timetable-edit', [
            'data' => $data,
            'batch' => $batch,
            'semester' => $semester,
            'syllabi' => $syllabi,
            'routines' => $routines,
            'weekdays' => $weekdays,
            'hours' => $hours,
            'lectureHalls' => $lectureHalls,
        ]);
    }

    function getTimetableData(Request $request, $subjectId, $batchId, $semesterId)
    {
        try {
            $activeShift = $this->resolveTimetableShift($request, (int) $subjectId);
            $programType = $this->resolveTimetableProgramType($request);
            $quickCourses = $this->buildQuickCoursesForBatchSemester((int) $subjectId, (int) $batchId, (int) $semesterId, $programType);


            // Get all syllabi for this subject/batch/semester
            $syllabi = SubjectHasSyllabus::where('subject_id', $subjectId)
                ->where('batch_id', $batchId)
                ->where('semester_id', $semesterId)
                ->with(['subject'])
                ->get();

            if ($syllabi->isEmpty()) {
                return response()->json([
                    'success' => true,
                    'data' => [],
                    'quick_courses' => $quickCourses,
                ]);
            }

            // Get routine data for all syllabi
            $syllabusIds = $syllabi->pluck('id');
            $routinesQuery = SubjectHasRoutine::whereIn('syllabus_id', $syllabusIds)
                ->where('shift', $activeShift);

            if ($this->supportsRoutineProgramType()) {
                $routinesQuery->where('program_type', $programType);
            }

            $routines = $routinesQuery
                ->with([
                    'teachingAssignment:id,course_id,faculty_id,delivery_type,allocation_group',
                    'teachingAssignment.primaryFacultyMembers:id,USER_CODE,FIRST_NAME,LAST_NAME',
                    'teachingAssignment.coFacultyMembers:id,USER_CODE,FIRST_NAME,LAST_NAME',
                    'teachingAllocation:id,course_id,faculty_id,delivery_type,allocation_group',
                    'teachingAllocation.primaryFacultyMembers:id,USER_CODE,FIRST_NAME,LAST_NAME',
                    'teachingAllocation.coFacultyMembers:id,USER_CODE,FIRST_NAME,LAST_NAME',
                    'lecturehallmaster:id,title',
                ])
                ->get();

            $weekdays = [1 => 'Monday', 2 => 'Tuesday', 3 => 'Wednesday', 4 => 'Thursday', 5 => 'Friday', 6 => 'Saturday'];
            $timetableData = [];

            $teachingAssignments = TeachingAssignment::with([
                'course.coursetypemaster:id,title',
                'faculty:id,USER_CODE,FIRST_NAME,LAST_NAME',
                'primaryFacultyMembers:id,USER_CODE,FIRST_NAME,LAST_NAME',
                'coFacultyMembers:id,USER_CODE,FIRST_NAME,LAST_NAME',
            ])
                ->where('subject_id', $subjectId)
                ->get();

            $assignmentById = $teachingAssignments->keyBy('id');
            $assignmentByPair = [];
            $assignmentByCourse = [];
            foreach ($teachingAssignments as $assignment) {
                $assignmentFacultyIds = collect($assignment->allAssignedFacultyIds())
                    ->map(fn($id) => (int) $id)
                    ->filter(fn($id) => $id > 0)
                    ->unique()
                    ->values();

                if ($assignmentFacultyIds->isEmpty() && !empty($assignment->faculty_id)) {
                    $assignmentFacultyIds = collect([(int) $assignment->faculty_id]);
                }

                if (!empty($assignment->course_id)) {
                    foreach ($assignmentFacultyIds as $facultyId) {
                        $pairKey = $assignment->course_id . '|' . $facultyId;
                        if (!isset($assignmentByPair[$pairKey])) {
                            $assignmentByPair[$pairKey] = [];
                        }
                        $assignmentByPair[$pairKey][] = $assignment;
                    }
                }

                if (!empty($assignment->course_id) && !isset($assignmentByCourse[$assignment->course_id])) {
                    $assignmentByCourse[$assignment->course_id] = $assignment;
                }
            }

            // Get all courses and faculties for lookup
            $courseRelations = SubjectCourseMaster::where('subject_id', $subjectId)
                ->with('courseMaster.coursetypemaster')
                ->get();

            // Create lookup collections - one by course_master_id and one by subject_course_master id
            $courseRelationsByMasterId = $courseRelations->keyBy('course_master_id');
            $courseRelationsBySubjectCourseId = $courseRelations->keyBy('id');

            // Get faculty relations, but also prepare for direct faculty lookup
            $facultyRelations = SubjectFacultyMaster::where('subject_id', $subjectId)
                ->with('faculty')
                ->get()
                ->keyBy('faculty_id');

            // Get direct faculty lookup for faculty_ids stored in routines
            $assignedFacultyIds = $teachingAssignments
                ->flatMap(function ($assignment) {
                    return collect($assignment->allAssignedFacultyIds())
                        ->map(fn($id) => (int) $id)
                        ->filter(fn($id) => $id > 0)
                        ->values();
                })
                ->unique()
                ->values();

            $allFaculties = Faculty::whereIn('id', $routines->pluck('faculty_id')->filter()->merge($assignedFacultyIds)->unique())
                ->get()
                ->keyBy('id');

            foreach ($routines as $routine) {
                $dayName = $weekdays[$routine->weekday_id] ?? '';
                if ($dayName) {
                    // Get the syllabus for this routine to find the course_master_id
                    $syllabus = $syllabi->firstWhere('id', $routine->syllabus_id);
                    $courseMasterId = $syllabus->course_id ?? null;
                    $facultyId = $routine->faculty_id; // Now using proper faculty_id column
                    $subjectCourseId = $routine->subject_course_id; // New subject_course_id column

                    // Get course name from subject_course_id or course_master_id
                    $courseName = '';
                    $courseRelation = null;

                    // Try to get course info from subject_course_id first (most specific)
                    if ($subjectCourseId && $courseRelationsBySubjectCourseId->has($subjectCourseId)) {
                        $courseRelation = $courseRelationsBySubjectCourseId->get($subjectCourseId);
                    } elseif ($courseMasterId && $courseRelationsByMasterId->has($courseMasterId)) {
                        // Fall back to course_master_id lookup
                        $courseRelation = $courseRelationsByMasterId->get($courseMasterId);
                    }

                    if ($courseRelation) {
                        $courseName = ($courseRelation->courseMaster->coursetypemaster->title ?? '') . ' - ' .
                            ($courseRelation->courseMaster->course_code ?? '') . ' - ' .
                            ($courseRelation->courseMaster->course_title ?? '');
                        $lookupCourseId = $courseRelation->course_master_id; // Use the actual course_master_id
                    } else {
                        $lookupCourseId = $courseMasterId ?: $subjectCourseId;
                    }

                    $assignment = null;
                    $routineAssignmentId = (int) ($routine->teaching_assignment_id ?? $routine->teaching_allocation_id ?? 0);
                    if (!empty($routineAssignmentId)) {
                        $assignment = $assignmentById[$routineAssignmentId] ?? null;
                    }

                    if (!$assignment && !empty($routine->teachingAssignment)) {
                        $assignment = $routine->teachingAssignment;
                    }

                    if (!$assignment && !empty($routine->teachingAllocation)) {
                        $assignment = $routine->teachingAllocation;
                    }

                    if ($assignment) {
                        if (!empty($assignment->faculty_id)) {
                            $facultyId = (int) $assignment->faculty_id;
                        }

                        if (!empty($assignment->course_id)) {
                            $lookupCourseId = (int) $assignment->course_id;
                            $courseName = trim(
                                ($assignment->course->coursetypemaster->title ?? '') . ' - ' .
                                    ($assignment->course->course_code ?? '') . ' - ' .
                                    ($assignment->course->course_title ?? '')
                            );
                        }
                    }

                    $coFacultyNames = collect($assignment?->coFacultyMembers ?? [])
                        ->map(fn($faculty) => trim((string) ($faculty->FIRST_NAME ?? '') . ' ' . (string) ($faculty->LAST_NAME ?? '')))
                        ->filter()
                        ->values()
                        ->all();

                    $primaryFacultyNames = collect($assignment?->primaryFacultyMembers ?? [])
                        ->map(fn($faculty) => trim((string) ($faculty->USER_CODE ?? '-') . ' - ' . (string) ($faculty->FIRST_NAME ?? '-') . ' ' . (string) ($faculty->LAST_NAME ?? '')))
                        ->filter()
                        ->values()
                        ->all();

                    // Get faculty name from the faculty_id (try direct lookup first, then relation)
                    $facultyName = '';
                    if ($facultyId) {
                        if ($allFaculties->has($facultyId)) {
                            $faculty = $allFaculties->get($facultyId);
                            $facultyName = trim(($faculty->FIRST_NAME ?? '') . ' ' . ($faculty->LAST_NAME ?? ''));
                        } elseif ($facultyRelations->has($facultyId)) {
                            $facultyRelation = $facultyRelations->get($facultyId);
                            $facultyName = trim(($facultyRelation->faculty->FIRST_NAME ?? '') . ' ' . ($facultyRelation->faculty->LAST_NAME ?? ''));
                        }
                    }

                    $timetableData[] = [
                        'routine_id' => $routine->id, // Include routine ID for direct deletion
                        'hour_number' => $routine->hour_id,
                        'day_of_week' => $dayName,
                        'shift' => $routine->shift,
                        'program_type' => (string) ($routine->program_type ?? $programType),
                        'subject_id' => $lookupCourseId ?: $syllabus->subject_id, // Return proper course id
                        'teacher_id' => $facultyId,
                        'teaching_assignment_id' => $routineAssignmentId ?: null,
                        'subject_name' => $courseName ?: ($syllabus->subject->subject_title ?? $syllabus->subject->title ?? 'Subject'),
                        'teacher_name' => !empty($primaryFacultyNames) ? implode(', ', $primaryFacultyNames) : ($facultyName ?: 'Teacher'),
                        'primary_faculty_names' => $primaryFacultyNames,
                        'co_faculty_names' => $coFacultyNames,
                        'lecturehall_id' => (int) ($routine->lecturehall_id ?? 0),
                        'lecturehall_name' => (string) (optional($routine->lecturehallmaster)->title ?? ''),
                        'delivery_type' => $assignment ? ($assignment->delivery_type ?? null) : null,
                        'allocation_group' => $assignment ? ($assignment->allocation_group ?? null) : null,
                        'allocation_group_label' => $assignment ? ($assignment->allocation_group_label ?? null) : null,
                        'slot_active' => $this->supportsRoutineIsActive() ? (int) ($routine->is_active ?? 1) : 1,
                    ];
                }
            }

            return response()->json([
                'success' => true,
                'data' => $timetableData,
                'quick_courses' => $quickCourses,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch timetable: ' . $e->getMessage()
            ], 500);
        }
    }

    private function buildQuickCoursesForBatchSemester(int $subjectId, int $batchId, int $semesterId, string $programType = 'UG'): array
    {
        $combinationIds = $this->resolveSubjectProgramCombinationIds($subjectId, $batchId, $programType);

        $curriculumRows = ProgramWiseSemesterCourse::query()
            ->with([
                'programinfo:id,course_code,course_title',
                'specializationmaster:id,name',
            ])
            ->when($combinationIds->isNotEmpty(), fn($query) => $query->whereIn('program_combo_refid', $combinationIds))
            ->where('batch', $batchId)
            ->where('semester', $semesterId)
            ->whereNotNull('course_id')
            ->get([
                'id',
                'course_id',
                'batch',
                'semester',
                'offering_dept',
                'delivery_category',
                'course_type',
                'specialization_mode',
                'specialization_master_id',
                'specialization_master_ids',
            ]);

        $batchSemesterCourseIds = $curriculumRows
            ->pluck('course_id')
            ->map(fn($id) => (int) $id)
            ->filter()
            ->unique()
            ->values();

        if ($batchSemesterCourseIds->isEmpty()) {
            return [];
        }

        $curriculumByCourseId = $curriculumRows
            ->groupBy(fn($row) => (int) $row->course_id);

        $activeAssignments = TeachingAssignment::query()
            ->with([
                'course:id,course_code,course_title',
                'faculty:id,USER_CODE,FIRST_NAME,LAST_NAME',
                'primaryFacultyMembers:id,USER_CODE,FIRST_NAME,LAST_NAME',
                'coFacultyMembers:id,USER_CODE,FIRST_NAME,LAST_NAME',
            ])
            ->where('subject_id', $subjectId)
            ->where('is_active', 1)
            ->whereNotNull('course_id')
            ->whereIn('course_id', $batchSemesterCourseIds)
            ->get([
                'id',
                'subject_id',
                'course_id',
                'faculty_id',
                'delivery_type',
                'allocation_group',
                'room',
                'remarks',
            ]);

        if ($activeAssignments->isEmpty()) {
            return [];
        }

        return $activeAssignments
            ->map(function ($assignment) use ($curriculumByCourseId, $batchId, $semesterId) {
                $courseId = (int) $assignment->course_id;
                $curriculum = optional($curriculumByCourseId->get($courseId))->first();
                $course = $assignment->course;
                $faculty = $assignment->faculty;

                $primaryFacultyCollection = $assignment->primaryFacultyMembers ?? collect();
                if ($primaryFacultyCollection->isEmpty() && $faculty) {
                    $primaryFacultyCollection = collect([$faculty]);
                }

                $specializationIds = collect((array) ($curriculum->specialization_master_ids ?? []))
                    ->map(fn($id) => (int) $id)
                    ->filter()
                    ->unique()
                    ->values()
                    ->all();

                $facultyName = trim(($faculty->FIRST_NAME ?? '') . ' ' . ($faculty->LAST_NAME ?? ''));

                $primaryFacultyNames = $primaryFacultyCollection
                    ->map(fn($primaryFaculty) => trim((string) ($primaryFaculty->FIRST_NAME ?? '') . ' ' . (string) ($primaryFaculty->LAST_NAME ?? '')))
                    ->filter()
                    ->values()
                    ->all();

                $primaryFacultyLabels = $primaryFacultyCollection
                    ->map(fn($primaryFaculty) => trim((string) ($primaryFaculty->USER_CODE ?? '-') . ' - ' . (string) ($primaryFaculty->FIRST_NAME ?? '-') . ' ' . (string) ($primaryFaculty->LAST_NAME ?? '')))
                    ->filter()
                    ->values()
                    ->all();

                return [
                    'assignment_id' => (int) $assignment->id,
                    'subject_id' => (int) $assignment->subject_id,
                    'course_id' => $courseId,
                    'course_code' => (string) ($course->course_code ?? ''),
                    'course_name' => (string) ($course->course_title ?? ''),
                    'course_label' => trim(((string) ($course->course_code ?? '-')) . ' - ' . ((string) ($course->course_title ?? '-'))),
                    'faculty_id' => (int) ($assignment->faculty_id ?? 0),
                    'primary_faculty_ids' => $primaryFacultyCollection
                        ->pluck('id')
                        ->map(fn($id) => (int) $id)
                        ->filter(fn($id) => $id > 0)
                        ->unique()
                        ->values()
                        ->all(),
                    'faculty_code' => (string) ($faculty->USER_CODE ?? ''),
                    'faculty_name' => !empty($primaryFacultyNames) ? implode(', ', $primaryFacultyNames) : (string) $facultyName,
                    'faculty_label' => !empty($primaryFacultyLabels)
                        ? implode(', ', $primaryFacultyLabels)
                        : trim(((string) ($faculty->USER_CODE ?? '-')) . ' - ' . ($facultyName !== '' ? $facultyName : '-')),
                    'co_faculty_ids' => $assignment->coFacultyMembers
                        ->pluck('id')
                        ->map(fn($id) => (int) $id)
                        ->values()
                        ->all(),
                    'co_faculty_names' => $assignment->coFacultyMembers
                        ->map(fn($coFaculty) => trim((string) ($coFaculty->FIRST_NAME ?? '') . ' ' . (string) ($coFaculty->LAST_NAME ?? '')))
                        ->filter()
                        ->values()
                        ->all(),
                    'co_faculty_label' => $assignment->coFacultyMembers
                        ->map(fn($coFaculty) => trim((string) ($coFaculty->USER_CODE ?? '-') . ' - ' . (string) ($coFaculty->FIRST_NAME ?? '-') . ' ' . (string) ($coFaculty->LAST_NAME ?? '')))
                        ->filter()
                        ->values()
                        ->all(),
                    'delivery_type' => (string) ($assignment->delivery_type ?? ''),
                    'allocation_group' => (int) ($assignment->allocation_group ?? 0),
                    'allocation_group_label' => (string) ($assignment->allocation_group_label ?? ''),
                    'room' => (string) ($assignment->room ?? ''),
                    'remarks' => (string) ($assignment->remarks ?? ''),
                    'batch' => (int) ($curriculum->batch ?? $batchId),
                    'semester' => (int) ($curriculum->semester ?? $semesterId),
                    'offering_dept' => (int) ($curriculum->offering_dept ?? 0),
                    'course_type' => (string) ($curriculum->course_type ?? ''),
                    'delivery_category' => (string) ($curriculum->delivery_category ?? ''),
                    'specialization_mode' => (string) ($curriculum->specialization_mode ?? ''),
                    'specialization_master_id' => (int) ($curriculum->specialization_master_id ?? 0),
                    'specialization_master_ids' => $specializationIds,
                    'specialization_name' => (string) (optional($curriculum->specializationmaster)->name ?? ''),
                ];
            })
            ->values()
            ->all();
    }

    function deleteRoutineSlot($routineId)
    {
        try {
            $routine = SubjectHasRoutine::find($routineId);

            if (!$routine) {
                return response()->json([
                    'success' => false,
                    'message' => 'Routine not found'
                ], 404);
            }

            $routine->delete();

            return response()->json([
                'success' => true,
                'message' => 'Routine deleted successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete routine: ' . $e->getMessage()
            ], 500);
        }
    }

    function clearAllRoutines(Request $request, $subjectId, $batchId, $semesterId)
    {
        try {
            $activeShift = $this->resolveTimetableShift($request, (int) $subjectId);
            $programType = $this->resolveTimetableProgramType($request);

            // Get all syllabi for this subject/batch/semester
            $syllabusIds = SubjectHasSyllabus::where('subject_id', $subjectId)
                ->where('batch_id', $batchId)
                ->where('semester_id', $semesterId)
                ->pluck('id');

            if ($syllabusIds->isEmpty()) {
                return response()->json([
                    'success' => true,
                    'message' => 'No timetable data found to clear'
                ]);
            }

            // Get routine IDs to delete associated substitutions
            $routineIdsQuery = SubjectHasRoutine::whereIn('syllabus_id', $syllabusIds)
                ->where('shift', $activeShift);

            if ($this->supportsRoutineProgramType()) {
                $routineIdsQuery->where('program_type', $programType);
            }

            $routineIds = $routineIdsQuery->pluck('id');

            // Delete associated substitutions first
            $substitutionsDeleted = FacultySubstitution::whereIn('routine_id', $routineIds)->count();
            FacultySubstitution::whereIn('routine_id', $routineIds)->delete();

            // Delete all routines for these syllabi
            $deleteQuery = SubjectHasRoutine::whereIn('syllabus_id', $syllabusIds)
                ->where('shift', $activeShift);

            if ($this->supportsRoutineProgramType()) {
                $deleteQuery->where('program_type', $programType);
            }

            $deletedCount = (clone $deleteQuery)->count();
            $deleteQuery->delete();

            $message = "Successfully cleared {$deletedCount} timetable entries";
            if ($substitutionsDeleted > 0) {
                $message .= " and {$substitutionsDeleted} related substitutions";
            }

            return response()->json([
                'success' => true,
                'message' => $message
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to clear timetable: ' . $e->getMessage()
            ], 500);
        }
    }

    function storeSemesterTimetable(Request $request, $subjectId, $batchId, $semesterId)
    {
        try {
            $activeShift = $this->resolveTimetableShift($request, (int) $subjectId);
            $programType = $this->resolveTimetableProgramType($request);

            // Handle bulk timetable save from grid
            if ($request->has('timetable')) {
                return $this->storeBulkTimetableNew($request, $subjectId, $batchId, $semesterId, $activeShift, $programType);
            }

            // Handle individual slot save
            $validated = $request->validate([
                'syllabus_id' => 'required|integer',
                'weekday_id' => 'required|integer',
                'hour_id' => 'required|integer',
                'lecturehall_id' => 'nullable|integer',
            ]);

            // Check if the syllabus exists and belongs to the correct subject/batch/semester
            $syllabusExists = SubjectHasSyllabus::where('id', $validated['syllabus_id'])
                ->where('subject_id', $subjectId)
                ->where('batch_id', $batchId)
                ->where('semester_id', $semesterId)
                ->exists();

            if (!$syllabusExists) {
                return redirect()
                    ->back()
                    ->with('info', 'Invalid syllabus selected.')
                    ->withInput();
            }

            // Check for conflicts in the same time slot (weekday + hour) for different syllabi
            $timeSlotConflict = SubjectHasRoutine::whereHas('syllabus', function ($query) use ($subjectId, $batchId, $semesterId) {
                $query->where('subject_id', $subjectId)
                    ->where('batch_id', $batchId)
                    ->where('semester_id', $semesterId);
            })
                ->where('weekday_id', $validated['weekday_id'])
                ->where('hour_id', $validated['hour_id'])
                ->where('shift', $activeShift);

            if ($this->supportsRoutineProgramType()) {
                $timeSlotConflict->where('program_type', $programType);
            }

            $timeSlotConflict = $timeSlotConflict->exists();

            if ($timeSlotConflict) {
                return redirect()
                    ->back()
                    ->with('info', 'This time slot is already occupied for this subject.')
                    ->withInput();
            }

            // Check if this specific syllabus already has a routine for this time slot
            $alreadyExists = SubjectHasRoutine::where('syllabus_id', $validated['syllabus_id'])
                ->where('weekday_id', $validated['weekday_id'])
                ->where('hour_id', $validated['hour_id'])
                ->where('shift', $activeShift);

            if ($this->supportsRoutineProgramType()) {
                $alreadyExists->where('program_type', $programType);
            }

            $alreadyExists = $alreadyExists->exists();

            if ($alreadyExists) {
                return redirect()
                    ->back()
                    ->with('info', 'Timetable slot already exists for the selected syllabus.')
                    ->withInput();
            }

            // Prepare data for creation
            $routineData = [
                'syllabus_id' => $validated['syllabus_id'],
                'batch_id' => $batchId, // Add batch_id for substitution
                'shift' => $activeShift,
                'weekday_id' => $validated['weekday_id'],
                'hour_id' => $validated['hour_id'],
            ];

            if ($this->supportsRoutineProgramType()) {
                $routineData['program_type'] = $programType;
            }

            // Add lecture hall if provided
            if (!empty($validated['lecturehall_id'])) {
                $routineData['lecturehall_id'] = $validated['lecturehall_id'];
            }

            // Create the routine
            SubjectHasRoutine::create($routineData);

            return redirect()
                ->route('department.timetable.edit', [$subjectId, $batchId, $semesterId])
                ->with('success', 'Timetable slot created successfully.');
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create timetable: ' . $e->getMessage()
            ], 500);
        }
    }

    private function storeBulkTimetableNew(Request $request, $subjectId, $batchId, $semesterId, string $activeShift, string $programType)
    {
        try {
            $timetable = $request->input('timetable', []);
            $supportsRoutineIsActive = $this->supportsRoutineIsActive();
            $createdCount = 0;
            $updatedCount = 0;
            $restoredCount = 0;
            $archivedCount = 0;

            $weekdays = [
                'Monday' => 1,
                'Tuesday' => 2,
                'Wednesday' => 3,
                'Thursday' => 4,
                'Friday' => 5,
                'Saturday' => 6
            ];

            $existingSyllabi = SubjectHasSyllabus::where('subject_id', $subjectId)
                ->where('batch_id', $batchId)
                ->where('semester_id', $semesterId)
                ->get(['id', 'course_id']);

            $syllabusById = $existingSyllabi->keyBy('id');
            $syllabusByCourseId = $existingSyllabi->keyBy('course_id');
            $syllabusIds = $existingSyllabi->pluck('id');

            $existingRoutinesQuery = SubjectHasRoutine::withTrashed()
                ->whereIn('syllabus_id', $syllabusIds)
                ->where('shift', $activeShift);

            if ($this->supportsRoutineProgramType()) {
                $existingRoutinesQuery->where('program_type', $programType);
            }

            $existingRoutines = $existingRoutinesQuery->get();

            $existingById = $existingRoutines->keyBy('id');
            $existingByKey = [];
            foreach ($existingRoutines as $routine) {
                $courseMasterId = (int) (optional($syllabusById->get($routine->syllabus_id))->course_id ?? 0);
                $key = implode('|', [
                    (int) $routine->weekday_id,
                    (int) $routine->hour_id,
                    $courseMasterId,
                    (int) ($routine->faculty_id ?? 0),
                    (int) ($routine->teaching_allocation_id ?? $routine->teaching_assignment_id ?? 0),
                ]);

                if (!isset($existingByKey[$key])) {
                    $existingByKey[$key] = [];
                }
                $existingByKey[$key][] = $routine;
            }

            $teachingAssignments = TeachingAssignment::where('subject_id', $subjectId)
                ->where('is_active', 1)
                ->get()
                ->keyBy('id');

            if ($supportsRoutineIsActive) {
                $timetable = $this->normalizeIncomingTimetableSlotActivity($timetable, $teachingAssignments);
            }

            $subjectCourseMap = SubjectCourseMaster::where('subject_id', $subjectId)
                ->get(['id', 'course_master_id'])
                ->keyBy('course_master_id');

            $seenSlots = [];
            $matchedRoutineIds = [];

            foreach ($timetable as $slot) {
                if (empty($slot['subject_id']) || empty($slot['day_of_week']) || empty($slot['hour_number'])) {
                    continue;
                }

                $dayName = $slot['day_of_week'];
                $hourNumber = (int)$slot['hour_number'];
                $teachingAssignmentId = !empty($slot['teaching_assignment_id']) ? (int) $slot['teaching_assignment_id'] : null;
                $assignment = $teachingAssignmentId ? ($teachingAssignments[$teachingAssignmentId] ?? null) : null;

                $courseMasterId = (int) ($slot['subject_id'] ?? 0);
                if ($courseMasterId <= 0 && $assignment) {
                    $courseMasterId = (int) $assignment->course_id;
                }

                $facultyId = !empty($slot['teacher_id']) ? (int) $slot['teacher_id'] : null;
                if (empty($facultyId) && $assignment) {
                    $facultyId = (int) $assignment->faculty_id;
                }

                if (!isset($weekdays[$dayName])) continue;
                $weekdayId = $weekdays[$dayName];

                if ($courseMasterId <= 0) {
                    continue;
                }

                $slotKey = implode('|', [
                    $dayName,
                    $hourNumber,
                    $courseMasterId,
                    $facultyId ?: 0,
                    $teachingAssignmentId ?: 0,
                ]);

                if (isset($seenSlots[$slotKey])) {
                    continue;
                }
                $seenSlots[$slotKey] = true;

                // Find the subject_course_master record ID for this course_master_id
                $syllabus = $syllabusByCourseId->get($courseMasterId);
                if (!$syllabus) {
                    $syllabusLookup = [
                        'subject_id' => $subjectId,
                        'batch_id' => $batchId,
                        'semester_id' => $semesterId,
                        'course_id' => $courseMasterId,
                    ];

                    $syllabus = SubjectHasSyllabus::firstOrCreate($syllabusLookup);
                    $syllabusByCourseId->put($courseMasterId, $syllabus);
                    $syllabusById->put($syllabus->id, $syllabus);
                }

                $subjectCourseId = optional($subjectCourseMap->get($courseMasterId))->id;

                $entryKey = implode('|', [
                    $weekdayId,
                    $hourNumber,
                    $courseMasterId,
                    (int) ($facultyId ?: 0),
                    (int) ($teachingAssignmentId ?: 0),
                ]);

                $routine = null;
                $incomingRoutineId = !empty($slot['routine_id']) ? (int) $slot['routine_id'] : null;

                if ($incomingRoutineId && isset($existingById[$incomingRoutineId])) {
                    $candidate = $existingById[$incomingRoutineId];
                    if ((int) $candidate->syllabus_id === (int) $syllabus->id) {
                        $routine = $candidate;
                    }
                }

                if (!$routine && !empty($existingByKey[$entryKey])) {
                    foreach ($existingByKey[$entryKey] as $candidate) {
                        if (!isset($matchedRoutineIds[$candidate->id])) {
                            $routine = $candidate;
                            break;
                        }
                    }
                }

                $payload = [
                    'syllabus_id' => $syllabus->id,
                    'batch_id' => $batchId,
                    'shift' => $activeShift,
                    'weekday_id' => $weekdayId,
                    'hour_id' => $hourNumber,
                    'lecturehall_id' => !empty($slot['lecturehall_id']) ? (int) $slot['lecturehall_id'] : null,
                    'faculty_id' => $facultyId,
                    'subject_course_id' => $subjectCourseId,
                    'teaching_assignment_id' => $teachingAssignmentId,
                ];

                if ($supportsRoutineIsActive) {
                    $payload['is_active'] = (int) (!empty($slot['slot_active']) ? 1 : 0);
                }

                if ($this->supportsRoutineProgramType()) {
                    $payload['program_type'] = $programType;
                }

                if ($routine) {
                    $wasTrashed = method_exists($routine, 'trashed') ? $routine->trashed() : false;
                    if ($wasTrashed) {
                        $routine->restore();
                        $restoredCount++;
                    }

                    $routine->fill($payload);
                    $routine->save();

                    $updatedCount++;
                    $matchedRoutineIds[$routine->id] = true;
                    continue;
                }

                $created = SubjectHasRoutine::create(array_merge($payload, [
                    'substitution_faculty_id' => null,
                ]));
                $createdCount++;
                $matchedRoutineIds[$created->id] = true;
            }

            // Archive slots removed from the current payload. Soft delete preserves historical links.
            foreach ($existingRoutines as $existingRoutine) {
                if ($existingRoutine->trashed()) {
                    continue;
                }

                if (isset($matchedRoutineIds[$existingRoutine->id])) {
                    continue;
                }

                $existingRoutine->delete();
                $archivedCount++;
            }

            return response()->json([
                'success' => true,
                'message' => "Timetable synced successfully. {$createdCount} created, {$updatedCount} updated, {$restoredCount} restored, {$archivedCount} archived."
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to save timetable: ' . $e->getMessage()
            ], 500);
        }
    }

    private function storeBulkTimetable(Request $request, $subjectId, $batchId, $semesterId)
    {
        try {
            $slots = $request->input('slots', []);
            $savedCount = 0;

            // Get weekday and hour mappings
            $weekdays = ['Monday' => 1, 'Tuesday' => 2, 'Wednesday' => 3, 'Thursday' => 4, 'Friday' => 5, 'Saturday' => 6];

            // Get or create a default syllabus for this subject/batch/semester
            $syllabus = SubjectHasSyllabus::firstOrCreate([
                'subject_id' => $subjectId,
                'batch_id' => $batchId,
                'semester_id' => $semesterId,
            ], [
                'course_id' => 1, // Default course ID, adjust as needed
            ]);

            foreach ($slots as $slotKey => $slotData) {
                if (empty($slotData)) continue;

                // Parse slot key (hour_day format)
                $parts = explode('_', $slotKey);
                if (count($parts) !== 2) continue;

                $hourId = (int)$parts[0];
                $dayName = $parts[1];

                if (!isset($weekdays[$dayName])) continue;
                $weekdayId = $weekdays[$dayName];

                // Check if slot already exists
                $exists = SubjectHasRoutine::where('syllabus_id', $syllabus->id)
                    ->where('weekday_id', $weekdayId)
                    ->where('hour_id', $hourId)
                    ->exists();

                if (!$exists) {
                    SubjectHasRoutine::create([
                        'syllabus_id' => $syllabus->id,
                        'batch_id' => $batchId, // Add batch_id for substitution
                        'weekday_id' => $weekdayId,
                        'hour_id' => $hourId,
                        'lecturehall_id' => null, // Can be added later
                    ]);
                    $savedCount++;
                }
            }

            if ($savedCount > 0) {
                return redirect()
                    ->back()
                    ->with('success', "Timetable saved successfully. {$savedCount} slots created.");
            } else {
                return redirect()
                    ->back()
                    ->with('info', 'No new slots were added. All selected slots may already exist.');
            }
        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->with('error', 'Failed to save timetable: ' . $e->getMessage())
                ->withInput();
        }
    }

    function substitution($subjectId, $slug)
    {
        $data = Subject::findOrFail($subjectId);
        $batches = BatchMaster::latest()->get();
        $subjectUsesShifts = (int) ($data->has_shift_delivery ?? 0) === 1;
        $enabledShiftIds = $this->getSubjectEnabledShiftIds($data);
        $shiftOptionsQuery = ShiftMaster::where('is_active', 1)
            ->orderBy('sort_order');
        if ($subjectUsesShifts && !empty($enabledShiftIds)) {
            $shiftOptionsQuery->whereIn('id', $enabledShiftIds);
        }
        $shiftOptions = $shiftOptionsQuery->get(['id', 'title', 'slug']);

        if ($subjectUsesShifts && $shiftOptions->isEmpty()) {
            $shiftOptions = ShiftMaster::where('slug', $this->getDefaultShiftSlug())
                ->where('is_active', 1)
                ->get(['id', 'title', 'slug']);
        }

        return view('admin.subject.substitution', [
            'data' => $data,
            'batches' => $batches,
            'subjectUsesShifts' => $subjectUsesShifts,
            'shiftOptions' => $shiftOptions,
        ]);
    }

    function getSubstitutionSchedule(Request $request, $batchId, $day)
    {
        try {
            $subjectId = (int) $request->get('subject_id');
            $activeShift = $this->resolveTimetableShift($request, $subjectId);

            // Get weekday ID from day name
            $weekdays = [
                'Monday' => 1,
                'Tuesday' => 2,
                'Wednesday' => 3,
                'Thursday' => 4,
                'Friday' => 5,
                'Saturday' => 6
            ];

            if (!isset($weekdays[$day])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid day provided'
                ], 400);
            }

            $weekdayId = $weekdays[$day];

            // Get all routines for the specific batch and day
            $routines = SubjectHasRoutine::where('batch_id', $batchId)
                ->where('weekday_id', $weekdayId)
                ->where('shift', $activeShift)
                ->with([
                    'faculty:id,FIRST_NAME,LAST_NAME,USER_CODE',
                    'substitutionFaculty:id,FIRST_NAME,LAST_NAME,USER_CODE',
                    'subjectCourse.courseMaster:id,course_title,course_code',
                    'subjectCourse.subject:id,title,code',
                    'syllabus.semestermaster:id,title'
                ])
                ->orderBy('hour_id')
                ->get();

            $scheduleData = [];

            foreach ($routines as $routine) {
                $originalFaculty = $routine->faculty;
                $substituteFaculty = $routine->substitutionFaculty;

                $scheduleData[] = [
                    'routine_id' => $routine->id,
                    'hour_number' => $routine->hour_id,
                    'shift' => $routine->shift,
                    'subject_title' => $routine->subjectCourse->subject->title ?? 'N/A',
                    'subject_code' => $routine->subjectCourse->subject->code ?? 'N/A',
                    'course_title' => $routine->subjectCourse->courseMaster->course_title ?? 'N/A',
                    'course_code' => $routine->subjectCourse->courseMaster->course_code ?? 'N/A',
                    'semester_title' => $routine->syllabus->semestermaster->title ?? 'N/A',
                    'original_faculty_id' => $routine->faculty_id,
                    'original_faculty_name' => $originalFaculty ? trim(($originalFaculty->FIRST_NAME ?? '') . ' ' . ($originalFaculty->LAST_NAME ?? '')) : 'No Teacher',
                    'original_faculty_code' => $originalFaculty->USER_CODE ?? '',
                    'substitute_faculty_id' => $routine->substitution_faculty_id,
                    'substitute_faculty_name' => $substituteFaculty ? trim(($substituteFaculty->FIRST_NAME ?? '') . ' ' . ($substituteFaculty->LAST_NAME ?? '')) : null,
                    'substitute_faculty_code' => $substituteFaculty->USER_CODE ?? '',
                    'has_substitution' => !is_null($routine->substitution_faculty_id)
                ];
            }

            return response()->json([
                'success' => true,
                'data' => $scheduleData
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch substitution schedule: ' . $e->getMessage()
            ], 500);
        }
    }

    function updateSubstitution(Request $request, $routineId)
    {
        try {
            $validated = $request->validate([
                'substitute_faculty_id' => 'nullable|exists:faculties,id',
                'reason' => 'nullable|string|max:255'
            ]);

            $routine = SubjectHasRoutine::findOrFail($routineId);

            $routine->update([
                'substitution_faculty_id' => $validated['substitute_faculty_id'],
                // You might want to add a reason column to store the substitution reason
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Substitution updated successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update substitution: ' . $e->getMessage()
            ], 500);
        }
    }

    function getTeacherConflicts(Request $request, $hourNumber, $day, TimetableConflictService $conflictService)
    {
        try {
            $subjectId = (int) $request->get('subject_id');
            $activeShift = $this->resolveTimetableShift($request, $subjectId);

            // Get weekday ID from day name
            $weekdays = [
                'Monday' => 1,
                'Tuesday' => 2,
                'Wednesday' => 3,
                'Thursday' => 4,
                'Friday' => 5,
                'Saturday' => 6
            ];

            if (!isset($weekdays[$day])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid day provided'
                ], 400);
            }

            $weekdayId = $weekdays[$day];
            $result = $conflictService->getFacultyConflictsForSlot(
                (int) $weekdayId,
                (int) $hourNumber,
                (string) $activeShift,
                (int) $request->get('ignore_routine_id', 0)
            );

            return response()->json($result, $result['success'] ? 200 : 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to check teacher conflicts: ' . $e->getMessage()
            ], 500);
        }
    }

    function validateTimetableConflict(Request $request, TimetableConflictService $conflictService)
    {
        $validated = $request->validate([
            'subject_id' => 'required|integer|exists:subjects,id',
            'batch_id' => 'required|integer|exists:batch_masters,id',
            'semester_id' => 'required|integer|exists:semesters,id',
            'program_type' => 'required|string|in:UG,PG',
            'weekday_id' => 'required|integer|min:1|max:7',
            'hour_id' => 'required|integer|min:1',
            'shift' => 'nullable|string|max:20',
            'teaching_assignment_id' => 'required|integer|exists:teaching_assignments,id',
            'lecturehall_id' => 'nullable|integer',
            'ignore_routine_id' => 'nullable|integer',
            'draft_entries' => 'nullable|array',
            'draft_entries.*.routine_id' => 'nullable|integer',
            'draft_entries.*.weekday_id' => 'nullable|integer',
            'draft_entries.*.hour_id' => 'nullable|integer',
            'draft_entries.*.teaching_assignment_id' => 'nullable|integer',
            'draft_entries.*.lecturehall_id' => 'nullable|integer',
            'draft_entries.*.shift_id' => 'nullable|integer',
            'draft_entries.*.slot_active' => 'nullable|in:0,1',
        ]);

        $result = $conflictService->validate([
            'subject_id' => (int) $validated['subject_id'],
            'batch_id' => (int) $validated['batch_id'],
            'semester_id' => (int) $validated['semester_id'],
            'program_type' => (string) strtoupper($validated['program_type']),
            'weekday_id' => (int) $validated['weekday_id'],
            'hour_id' => (int) $validated['hour_id'],
            'shift' => (string) ($validated['shift'] ?? $this->getDefaultShiftSlug()),
            'teaching_assignment_id' => (int) $validated['teaching_assignment_id'],
            'lecturehall_id' => (int) ($validated['lecturehall_id'] ?? 0),
            'ignore_routine_id' => (int) ($validated['ignore_routine_id'] ?? 0),
            'draft_entries' => $validated['draft_entries'] ?? [],
        ]);

        return response()->json($result, $result['success'] ? 200 : 422);
    }

    private function resolveTimetableShift(Request $request, int $subjectId): string
    {
        $defaultShift = $this->getDefaultShiftSlug();
        $requestedShift = $request->get('shift');

        if (!$this->subjectUsesShifts($subjectId)) {
            return $defaultShift;
        }

        $allowedShiftSlugs = $this->getAllowedShiftSlugsForSubject($subjectId);
        if (empty($allowedShiftSlugs)) {
            $allowedShiftSlugs = [$defaultShift];
        }

        if (!empty($requestedShift) && in_array($requestedShift, $allowedShiftSlugs, true) && $this->isKnownShift($requestedShift)) {
            return $requestedShift;
        }

        return $allowedShiftSlugs[0] ?? $defaultShift;
    }

    private function resolveTimetableProgramType(Request $request): string
    {
        $programType = strtoupper(trim((string) $request->get('program_type', 'UG')));
        return $programType === 'PG' ? 'PG' : 'UG';
    }

    private function supportsRoutineProgramType(): bool
    {
        return Schema::hasColumn('subject_has_routines', 'program_type');
    }

    private function supportsRoutineIsActive(): bool
    {
        return Schema::hasColumn('subject_has_routines', 'is_active');
    }

    private function normalizeIncomingTimetableSlotActivity(array $timetable, $teachingAssignments): array
    {
        $indexedGroups = [];

        foreach ($timetable as $index => $slot) {
            if (!is_array($slot)) {
                continue;
            }

            $day = trim((string) ($slot['day_of_week'] ?? ''));
            $hour = (int) ($slot['hour_number'] ?? 0);
            if ($day === '' || $hour <= 0) {
                continue;
            }

            $assignmentId = (int) ($slot['teaching_assignment_id'] ?? 0);
            $assignment = $assignmentId > 0 ? ($teachingAssignments->get($assignmentId) ?? null) : null;

            $courseId = (int) ($slot['subject_id'] ?? 0);
            if ($courseId <= 0 && $assignment) {
                $courseId = (int) ($assignment->course_id ?? 0);
            }

            $deliveryType = trim((string) ($slot['delivery_type'] ?? ($assignment->delivery_type ?? '')));
            $allocationGroup = (int) ($slot['allocation_group'] ?? ($assignment->allocation_group ?? 0));

            $identityKey = implode('|', [$day, $hour, $courseId, strtoupper($deliveryType), $allocationGroup]);
            if (!isset($indexedGroups[$identityKey])) {
                $indexedGroups[$identityKey] = [];
            }

            $indexedGroups[$identityKey][] = $index;
            if (!array_key_exists('slot_active', $timetable[$index])) {
                $timetable[$index]['slot_active'] = 1;
            }
        }

        foreach ($indexedGroups as $groupIndexes) {
            if (count($groupIndexes) <= 1) {
                continue;
            }

            $activeIndexes = [];
            foreach ($groupIndexes as $index) {
                if ((int) ($timetable[$index]['slot_active'] ?? 1) === 1) {
                    $activeIndexes[] = $index;
                }
            }

            $winnerIndex = !empty($activeIndexes) ? $activeIndexes[0] : $groupIndexes[0];
            foreach ($groupIndexes as $index) {
                $timetable[$index]['slot_active'] = $index === $winnerIndex ? 1 : 0;
            }
        }

        return $timetable;
    }

    private function supportsSubjectShiftIds(): bool
    {
        return Schema::hasColumn('subjects', 'shift_ids');
    }

    private function getSubjectEnabledShiftIds(?Subject $subject): array
    {
        if (!$subject || !$this->supportsSubjectShiftIds()) {
            return [];
        }

        $rawShiftIds = $subject->shift_ids;
        if (is_string($rawShiftIds)) {
            $decoded = json_decode($rawShiftIds, true);
            $rawShiftIds = is_array($decoded) ? $decoded : [];
        }

        if (!is_array($rawShiftIds)) {
            return [];
        }

        return collect($rawShiftIds)
            ->map(fn($id) => (int) $id)
            ->filter(fn($id) => $id > 0)
            ->unique()
            ->values()
            ->all();
    }

    private function getAllowedShiftSlugsForSubject(int $subjectId): array
    {
        if ($subjectId <= 0 || !$this->supportsSubjectShiftIds()) {
            return [];
        }

        $subject = Subject::find($subjectId);
        if (!$subject || (int) ($subject->has_shift_delivery ?? 0) !== 1) {
            return [];
        }

        $enabledShiftIds = $this->getSubjectEnabledShiftIds($subject);
        if (empty($enabledShiftIds)) {
            return [];
        }

        return ShiftMaster::where('is_active', 1)
            ->whereIn('id', $enabledShiftIds)
            ->orderBy('sort_order')
            ->pluck('slug')
            ->filter()
            ->values()
            ->all();
    }

    private function resolveSubjectProgramCombinationIds(int $subjectId, int $batchId, string $programType)
    {
        $normalizedProgramType = strtoupper(trim($programType));

        $baseQuery = SubjectHasStudentProgam::query()
            ->where('subject_id', $subjectId)
            ->where('batch_id', $batchId);

        $exactMatchIds = (clone $baseQuery)
            ->whereRaw("UPPER(TRIM(COALESCE(program_type, ''))) = ?", [$normalizedProgramType])
            ->pluck('id')
            ->map(fn($id) => (int) $id)
            ->filter()
            ->unique()
            ->values();

        if ($exactMatchIds->isNotEmpty()) {
            return $exactMatchIds;
        }

        $legacyBlankIds = (clone $baseQuery)
            ->whereRaw("TRIM(COALESCE(program_type, '')) = ''")
            ->pluck('id')
            ->map(fn($id) => (int) $id)
            ->filter()
            ->unique()
            ->values();

        if ($legacyBlankIds->isNotEmpty()) {
            return $legacyBlankIds;
        }

        return (clone $baseQuery)
            ->pluck('id')
            ->map(fn($id) => (int) $id)
            ->filter()
            ->unique()
            ->values();
    }

    private function subjectUsesShifts(int $subjectId): bool
    {
        if ($subjectId <= 0) {
            return false;
        }

        return Subject::where('id', $subjectId)->value('has_shift_delivery') == 1;
    }

    private function isKnownShift(string $shift): bool
    {
        return ShiftMaster::where('slug', $shift)->exists();
    }

    private function getDefaultShiftSlug(): string
    {
        $common = ShiftMaster::where('slug', 'common')->value('slug');
        if (!empty($common)) {
            return $common;
        }

        $fallback = ShiftMaster::orderBy('sort_order')->value('slug');
        return $fallback ?: 'common';
    }

    function saveSubstitutions(Request $request)
    {
        try {
            $validated = $request->validate([
                'substitutions' => 'required|array|min:1',
                'substitutions.*.routine_id' => 'required|exists:subject_has_routines,id',
                'substitutions.*.original_teacher_id' => 'required|exists:faculties,id',
                'substitutions.*.substitute_teacher_id' => 'required|exists:faculties,id',
                'substitutions.*.hour_number' => 'required|integer|min:1|max:24',
                'substitutions.*.day_of_week' => 'required|in:Monday,Tuesday,Wednesday,Thursday,Friday,Saturday,Sunday',
                'substitutions.*.reason' => 'nullable|string|max:255',
                'substitution_date' => 'required|date|after_or_equal:today',
                'batch_id' => 'nullable|exists:batch_masters,id'
            ]);

            // Additional validation: Check for duplicate substitute teachers
            $substituteTeachers = collect($validated['substitutions'])
                ->groupBy(function ($sub) {
                    return $sub['hour_number'] . '-' . $sub['day_of_week'];
                });

            $duplicateWarnings = [];
            foreach ($substituteTeachers as $timeSlot => $subs) {
                $duplicates = collect($subs)->pluck('substitute_teacher_id')->duplicates();
                if ($duplicates->isNotEmpty()) {
                    $duplicateWarnings[] = "Same substitute teacher assigned multiple times at hour " . explode('-', $timeSlot)[0];
                }
            }

            $savedCount = 0;
            $updatedCount = 0;
            $errors = [];

            foreach ($validated['substitutions'] as $substitution) {
                try {
                    // Check if substitution already exists for this date/routine
                    $existing = FacultySubstitution::where('routine_id', $substitution['routine_id'])
                        ->where('substitution_date', $validated['substitution_date'])
                        ->first();

                    if ($existing) {
                        // Update existing record
                        $existing->update([
                            'original_faculty_id' => $substitution['original_teacher_id'],
                            'substitute_faculty_id' => $substitution['substitute_teacher_id'],
                            'hour_number' => $substitution['hour_number'],
                            'day_of_week' => $substitution['day_of_week'],
                            'reason' => $substitution['reason'],
                            'created_by' => Auth::id(),
                            'is_active' => true
                        ]);
                        $updatedCount++;
                    } else {
                        // Create new record
                        FacultySubstitution::create([
                            'routine_id' => $substitution['routine_id'],
                            'original_faculty_id' => $substitution['original_teacher_id'],
                            'substitute_faculty_id' => $substitution['substitute_teacher_id'],
                            'substitution_date' => $validated['substitution_date'],
                            'hour_number' => $substitution['hour_number'],
                            'day_of_week' => $substitution['day_of_week'],
                            'reason' => $substitution['reason'],
                            'created_by' => Auth::id(),
                            'is_active' => true
                        ]);
                        $savedCount++;
                    }

                    // Update the actual routine with substitution
                    $routine = SubjectHasRoutine::find($substitution['routine_id']);
                    if ($routine) {
                        $routine->update([
                            'substitution_faculty_id' => $substitution['substitute_teacher_id']
                        ]);
                    }
                } catch (\Exception $e) {
                    $errors[] = "Failed to save substitution for routine {$substitution['routine_id']}: " . $e->getMessage();
                }
            }

            $response = [
                'success' => true,
                'message' => "Substitutions processed successfully",
                'saved_count' => $savedCount + $updatedCount,
                'new_count' => $savedCount,
                'updated_count' => $updatedCount,
                'substitution_date' => $validated['substitution_date'],
                'total_processed' => count($validated['substitutions'])
            ];

            // Combine all warnings and errors
            $allErrors = array_merge($errors, $duplicateWarnings);
            if (!empty($allErrors)) {
                $response['message'] .= '. Some issues were encountered.';
            }

            return response()->json($response);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to save substitutions: ' . $e->getMessage()
            ], 500);
        }
    }

    function getSubstitutionHistory(Request $request)
    {
        try {
            $validated = $request->validate([
                'batch_id' => 'nullable|exists:batch_masters,id',
                'shift' => 'nullable|string|max:20',
                'start_date' => 'nullable|date',
                'end_date' => 'nullable|date|after_or_equal:start_date',
                'faculty_id' => 'nullable|exists:faculties,id',
                'limit' => 'nullable|integer|min:1|max:100'
            ]);

            $query = FacultySubstitution::with([
                'routine.syllabus.semestermaster',
                'routine.subjectCourse.subject',
                'routine.subjectCourse.courseMaster',
                'originalFaculty',
                'substituteFaculty',
                'createdBy'
            ])
                ->where('is_active', true)
                ->orderBy('substitution_date', 'desc')
                ->orderBy('hour_number');

            // Apply filters
            if (!empty($validated['batch_id'])) {
                $query->whereHas('routine', function ($q) use ($validated) {
                    $q->where('batch_id', $validated['batch_id']);
                });
            }

            if (!empty($validated['shift']) && $this->isKnownShift($validated['shift'])) {
                $query->whereHas('routine', function ($q) use ($validated) {
                    $q->where('shift', $validated['shift']);
                });
            }

            if (!empty($validated['start_date'])) {
                $query->where('substitution_date', '>=', $validated['start_date']);
            }

            if (!empty($validated['end_date'])) {
                $query->where('substitution_date', '<=', $validated['end_date']);
            }

            if (!empty($validated['faculty_id'])) {
                $query->where(function ($q) use ($validated) {
                    $q->where('original_faculty_id', $validated['faculty_id'])
                        ->orWhere('substitute_faculty_id', $validated['faculty_id']);
                });
            }

            $limit = $validated['limit'] ?? 50;
            $substitutions = $query->paginate($limit);

            $historyData = $substitutions->getCollection()->map(function ($substitution) {
                return [
                    'id' => $substitution->id,
                    'substitution_date' => $substitution->substitution_date->format('Y-m-d'),
                    'formatted_date' => $substitution->substitution_date->format('l, F j, Y'),
                    'day_of_week' => $substitution->day_of_week,
                    'hour_number' => $substitution->hour_number,
                    'shift' => $substitution->routine->shift ?? 'common',
                    'subject_title' => $substitution->routine->subjectCourse->subject->title ?? 'N/A',
                    'course_title' => $substitution->routine->subjectCourse->courseMaster->course_title ?? 'N/A',
                    'semester_title' => $substitution->routine->syllabus->semestermaster->title ?? 'N/A',
                    'original_faculty' => [
                        'id' => $substitution->original_faculty_id,
                        'name' => trim(($substitution->originalFaculty->FIRST_NAME ?? '') . ' ' . ($substitution->originalFaculty->LAST_NAME ?? '')),
                        'code' => $substitution->originalFaculty->USER_CODE ?? 'N/A'
                    ],
                    'substitute_faculty' => [
                        'id' => $substitution->substitute_faculty_id,
                        'name' => trim(($substitution->substituteFaculty->FIRST_NAME ?? '') . ' ' . ($substitution->substituteFaculty->LAST_NAME ?? '')),
                        'code' => $substitution->substituteFaculty->USER_CODE ?? 'N/A'
                    ],
                    'reason' => $substitution->reason,
                    'created_by' => $substitution->createdBy->name ?? 'System',
                    'created_at' => $substitution->created_at->format('Y-m-d H:i:s')
                ];
            });

            return response()->json([
                'success' => true,
                'data' => $historyData,
                'pagination' => [
                    'current_page' => $substitutions->currentPage(),
                    'last_page' => $substitutions->lastPage(),
                    'per_page' => $substitutions->perPage(),
                    'total' => $substitutions->total()
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch substitution history: ' . $e->getMessage()
            ], 500);
        }
    }

    function substitutionHistoryPage()
    {
        $batches = BatchMaster::latest()->get();
        $faculties = Faculty::orderBy('FIRST_NAME')->get();
        $shiftOptions = ShiftMaster::where('is_active', 1)
            ->orderBy('sort_order')
            ->get(['title', 'slug']);

        return view('admin.subject.substitution-history', [
            'batches' => $batches,
            'faculties' => $faculties,
            'shiftOptions' => $shiftOptions,
        ]);
    }

    function exportSubstitutionHistory(Request $request)
    {
        try {
            $validated = $request->validate([
                'batch_id' => 'nullable|exists:batch_masters,id',
                'shift' => 'nullable|string|max:20',
                'start_date' => 'nullable|date',
                'end_date' => 'nullable|date|after_or_equal:start_date',
                'faculty_id' => 'nullable|exists:faculties,id'
            ]);

            $query = FacultySubstitution::with([
                'routine.syllabus.semestermaster',
                'routine.subjectCourse.subject',
                'routine.subjectCourse.courseMaster',
                'originalFaculty',
                'substituteFaculty',
                'createdBy'
            ])
                ->where('is_active', true)
                ->orderBy('substitution_date', 'desc')
                ->orderBy('hour_number');

            // Apply filters (same logic as getSubstitutionHistory)
            if (!empty($validated['batch_id'])) {
                $query->whereHas('routine', function ($q) use ($validated) {
                    $q->where('batch_id', $validated['batch_id']);
                });
            }

            if (!empty($validated['shift']) && $this->isKnownShift($validated['shift'])) {
                $query->whereHas('routine', function ($q) use ($validated) {
                    $q->where('shift', $validated['shift']);
                });
            }

            if (!empty($validated['start_date'])) {
                $query->where('substitution_date', '>=', $validated['start_date']);
            }

            if (!empty($validated['end_date'])) {
                $query->where('substitution_date', '<=', $validated['end_date']);
            }

            if (!empty($validated['faculty_id'])) {
                $query->where(function ($q) use ($validated) {
                    $q->where('original_faculty_id', $validated['faculty_id'])
                        ->orWhere('substitute_faculty_id', $validated['faculty_id']);
                });
            }

            $substitutions = $query->get();

            // Format data for Excel export
            $exportData = $substitutions->map(function ($substitution) {
                return [
                    'date' => $substitution->substitution_date->format('Y-m-d'),
                    'day' => $substitution->day_of_week,
                    'hour' => $substitution->hour_number,
                    'subject' => $substitution->routine?->subjectCourse?->subject?->title ?? 'N/A',
                    'course' => $substitution->routine?->subjectCourse?->courseMaster?->course_title ?? 'N/A',
                    'semester' => $substitution->routine?->syllabus?->semestermaster?->title ?? 'N/A',
                    'original_teacher' => trim(($substitution->originalFaculty?->FIRST_NAME ?? '') . ' ' . ($substitution->originalFaculty?->LAST_NAME ?? '')),
                    'original_teacher_code' => $substitution->originalFaculty?->USER_CODE ?? 'N/A',
                    'substitute_teacher' => trim(($substitution->substituteFaculty?->FIRST_NAME ?? '') . ' ' . ($substitution->substituteFaculty?->LAST_NAME ?? '')),
                    'substitute_teacher_code' => $substitution->substituteFaculty?->USER_CODE ?? 'N/A',
                    'reason' => $substitution->reason ?? '',
                    'created_by' => $substitution->createdBy?->name ?? 'System',
                    'created_at' => $substitution->created_at->format('Y-m-d H:i:s')
                ];
            });

            // Generate filename with filters applied
            $filename = 'substitution_history_' . now()->format('Y-m-d_H-i-s');
            if (!empty($validated['batch_id'])) {
                $batch = BatchMaster::find($validated['batch_id']);
                $filename .= '_batch_' . ($batch?->batch_name ?? $validated['batch_id']);
            }
            if (!empty($validated['start_date'])) {
                $filename .= '_from_' . $validated['start_date'];
            }
            if (!empty($validated['end_date'])) {
                $filename .= '_to_' . $validated['end_date'];
            }
            $filename .= '.xlsx';

            return Excel::download(new SubstitutionHistoryExport($exportData), $filename);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to export substitution history: ' . $e->getMessage()
            ], 500);
        }
    }

    public function facultyTimetable(Request $request, $facultyId)
    {
        $faculty = Faculty::findOrFail($facultyId);
        $hasTeachingAllocationLink = Schema::hasColumn('subject_has_routines', 'teaching_allocation_id');

        $subjectId = (int) $request->query('subject_id', 0);
        $batchId = (int) $request->query('batch', 0);
        $semesterId = (int) $request->query('semester_id', 0);
        $programType = $this->resolveTimetableProgramType($request);
        $activeShift = (string) $request->query('shift', $this->getDefaultShiftSlug());
        if ($subjectId > 0) {
            $activeShift = $this->resolveTimetableShift($request, $subjectId);
        } elseif (!$this->isKnownShift($activeShift)) {
            $activeShift = $this->getDefaultShiftSlug();
        }

        $shiftOptionsQuery = ShiftMaster::where('is_active', 1)->orderBy('sort_order');
        if ($subjectId > 0) {
            $subject = Subject::find($subjectId);
            $subjectUsesShifts = (int) ($subject->has_shift_delivery ?? 0) === 1;
            $enabledShiftIds = $this->getSubjectEnabledShiftIds($subject);

            if ($subjectUsesShifts && !empty($enabledShiftIds)) {
                $shiftOptionsQuery->whereIn('id', $enabledShiftIds);
            } else {
                $shiftOptionsQuery->where('slug', $this->getDefaultShiftSlug());
            }
        }

        $shiftOptions = $shiftOptionsQuery->get(['id', 'title', 'slug']);
        if ($shiftOptions->isEmpty()) {
            $shiftOptions = ShiftMaster::where('slug', $this->getDefaultShiftSlug())
                ->where('is_active', 1)
                ->get(['id', 'title', 'slug']);
        }

        if ($shiftOptions->isNotEmpty() && !$shiftOptions->pluck('slug')->contains($activeShift)) {
            $activeShift = (string) ($shiftOptions->first()->slug ?? $this->getDefaultShiftSlug());
        }

        $timetableQuery = SubjectHasRoutine::query()
            ->where(function ($query) use ($facultyId) {
                $query->where('faculty_id', $facultyId)
                    ->orWhereHas('teachingAssignment', function ($assignmentQuery) use ($facultyId) {
                        $assignmentQuery->where('faculty_id', $facultyId)
                            ->orWhereHas('facultyAssignments', function ($facultyAssignmentQuery) use ($facultyId) {
                                $facultyAssignmentQuery->where('faculty_id', $facultyId);
                            })
                            ->orWhereHas('coFacultyMembers', function ($coFacultyQuery) use ($facultyId) {
                                $coFacultyQuery->where('faculties.id', $facultyId);
                            });
                    });
            })
            ->when($hasTeachingAllocationLink, function ($query) use ($facultyId) {
                $query->orWhereHas('teachingAllocation', function ($assignmentQuery) use ($facultyId) {
                    $assignmentQuery->where('faculty_id', $facultyId)
                        ->orWhereHas('facultyAssignments', function ($facultyAssignmentQuery) use ($facultyId) {
                            $facultyAssignmentQuery->where('faculty_id', $facultyId);
                        })
                        ->orWhereHas('coFacultyMembers', function ($coFacultyQuery) use ($facultyId) {
                            $coFacultyQuery->where('faculties.id', $facultyId);
                        });
                });
            })
            ->with([
                'weekdaymaster:id,title',
                'hourmaster',
                'faculty:id,USER_CODE,FIRST_NAME,LAST_NAME',
                'teachingAssignment:id,course_id,faculty_id,delivery_type,allocation_group,room',
                'teachingAssignment.course:id,course_code,course_title,course_type',
                'teachingAssignment.course.coursetypemaster:id,title',
                'teachingAssignment.faculty:id,USER_CODE,FIRST_NAME,LAST_NAME',
                'teachingAssignment.coFacultyMembers:id,USER_CODE,FIRST_NAME,LAST_NAME',
                'syllabus.subject:id,title',
                'syllabus.batchmaster:id,batch_name',
                'syllabus.semestermaster:id,title',
                'subjectCourse.courseMaster:id,course_title,course_code,course_type',
                'subjectCourse.courseMaster.coursetypemaster:id,title',
            ]);

        if ($hasTeachingAllocationLink) {
            $timetableQuery->with([
                'teachingAllocation:id,course_id,faculty_id,delivery_type,allocation_group,room',
                'teachingAllocation.course:id,course_code,course_title,course_type',
                'teachingAllocation.course.coursetypemaster:id,title',
                'teachingAllocation.faculty:id,USER_CODE,FIRST_NAME,LAST_NAME',
                'teachingAllocation.coFacultyMembers:id,USER_CODE,FIRST_NAME,LAST_NAME',
            ]);
        }

        if ($subjectId > 0) {
            $timetableQuery->whereHas('syllabus', function ($query) use ($subjectId) {
                $query->where('subject_id', $subjectId);
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

        if ($this->supportsRoutineProgramType()) {
            $timetableQuery->where('program_type', $programType);
        }

        $timetable = $timetableQuery
            ->orderBy('weekday_id')
            ->orderBy('hour_id')
            ->get()
            ->map(function ($routine) use ($programType, $hasTeachingAllocationLink) {
                $assignment = $routine->teachingAssignment;
                if (!$assignment && $hasTeachingAllocationLink) {
                    $assignment = $routine->teachingAllocation;
                }
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

                $weekdayId = (int) ($routine->weekday_id ?? 0);
                $weekdayMap = [
                    1 => 'Monday',
                    2 => 'Tuesday',
                    3 => 'Wednesday',
                    4 => 'Thursday',
                    5 => 'Friday',
                    6 => 'Saturday',
                    7 => 'Sunday',
                ];

                $weekday = (string) ($weekdayMap[$weekdayId] ?? '');
                if ($weekday === '') {
                    $rawWeekdayTitle = strtoupper(trim((string) ($routine->weekdaymaster->title ?? '')));
                    $weekdayAliasMap = [
                        'MONDAY' => 'Monday',
                        'TUESDAY' => 'Tuesday',
                        'WEDNESDAY' => 'Wednesday',
                        'THURSDAY' => 'Thursday',
                        'FRIDAY' => 'Friday',
                        'SATURDAY' => 'Saturday',
                        'SUNDAY' => 'Sunday',
                    ];
                    $weekday = (string) ($weekdayAliasMap[$rawWeekdayTitle] ?? '-');
                }

                return [
                    'weekday' => $weekday,
                    'hour' => $hourLabel,
                    'hour_sort' => $hourNo > 0 ? $hourNo : (int) ($routine->hour_id ?? 0),
                    'subject' => $routine->syllabus->subject->title ?? '-',
                    'shift' => ucfirst((string) ($routine->shift ?? 'common')),
                    'batch' => $routine->syllabus->batchmaster->batch_name ?? '-',
                    'semester' => $routine->syllabus->semestermaster->title ?? '-',
                    'room' => trim((string) ($assignment->room ?? '')) !== '' ? trim((string) ($assignment->room ?? '')) : '-',
                    'course' => trim($courseCode . ($courseCode !== '' ? ' - ' : '') . $courseTitle),
                    'course_type' => (string) ($course->coursetypemaster->title ?? '-'),
                    'faculty' => trim((string) ($assignment?->faculty?->FIRST_NAME ?? '') . ' ' . (string) ($assignment?->faculty?->LAST_NAME ?? '')),
                    'co_faculty' => collect($assignment?->coFacultyMembers ?? [])
                        ->map(fn($faculty) => trim((string) ($faculty->FIRST_NAME ?? '') . ' ' . (string) ($faculty->LAST_NAME ?? '')))
                        ->filter()
                        ->values()
                        ->all(),
                    'program_type' => strtoupper((string) ($routine->program_type ?? $programType)) === 'PG' ? 'PG' : 'UG',
                ];
            })
            ->values();

        return view('admin.subject.timetable.faculty-timetable', [
            'faculty' => $faculty,
            'timetable' => $timetable,
            'subjectId' => $subjectId > 0 ? $subjectId : null,
            'selectedBatchId' => $batchId > 0 ? $batchId : null,
            'selectedSemesterId' => $semesterId > 0 ? $semesterId : null,
            'selectedShift' => $activeShift,
            'selectedProgramType' => $programType,
            'shiftOptions' => $shiftOptions,
            'semesterOptions' => Semester::orderBy('title')->get(['id', 'title']),
        ]);
    }

    /**
     * Get available teachers for substitution at a specific time slot
     * Only returns teachers who are not teaching at that time
     */
    public function getAvailableTeachersForSubstitution(Request $request)
    {
        try {
            $validated = $request->validate([
                'subject_id' => 'required|exists:subjects,id',
                'batch_id' => 'required|exists:batch_masters,id',
                'weekday_id' => 'required|integer|min:1|max:7',
                'hour_id' => 'required|integer|min:1'
            ]);

            $subjectId = $validated['subject_id'];
            $batchId = $validated['batch_id'];
            $weekdayId = $validated['weekday_id'];
            $hourId = $validated['hour_id'];

            // Get all faculty members assigned to this subject
            $subjectFaculties = SubjectFacultyMaster::where('subject_id', $subjectId)
                ->with('faculty')
                ->get();

            // Get faculty IDs who are already teaching at this time slot
            $busyFacultyIds = SubjectHasRoutine::where('weekday_id', $weekdayId)
                ->where('hour_id', $hourId)
                ->whereNotNull('faculty_id')
                ->pluck('faculty_id')
                ->toArray();

            // Filter to get only available teachers
            $availableTeachers = $subjectFaculties->filter(function ($subjectFaculty) use ($busyFacultyIds) {
                return $subjectFaculty->faculty &&
                    !in_array($subjectFaculty->faculty->id, $busyFacultyIds) &&
                    $subjectFaculty->faculty->IS_LEFT == 0;
            })->map(function ($subjectFaculty) {
                $faculty = $subjectFaculty->faculty;
                return [
                    'id' => $faculty->id,
                    'user_code' => $faculty->USER_CODE ?? '',
                    'first_name' => $faculty->FIRST_NAME ?? '',
                    'last_name' => $faculty->LAST_NAME ?? '',
                    'full_name' => trim(($faculty->FIRST_NAME ?? '') . ' ' . ($faculty->LAST_NAME ?? ''))
                ];
            })->values();

            return response()->json([
                'success' => true,
                'available_teachers' => $availableTeachers,
                'total_available' => $availableTeachers->count(),
                'time_slot' => [
                    'weekday_id' => $weekdayId,
                    'hour_id' => $hourId,
                    'batch_id' => $batchId
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch available teachers: ' . $e->getMessage()
            ], 500);
        }
    }
}
