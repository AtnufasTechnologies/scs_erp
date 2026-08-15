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
use App\Services\StudentRosterEngine;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;

class FacultyDashboardController extends Controller
{
  private StudentRosterEngine $studentRosterEngine;

  public function __construct(StudentRosterEngine $studentRosterEngine)
  {
    $this->studentRosterEngine = $studentRosterEngine;
  }

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

    $hasTeachingAllocationLink = Schema::hasColumn('subject_has_routines', 'teaching_allocation_id');

    // Get assigned subject routines with progress context.
    $allSubjectsRoutines = SubjectHasRoutine::where(function ($query) use ($facultyId) {
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
        'syllabus.subject:id,title',
        'syllabus.batchmaster:id,batch_name',
        'syllabus.semestermaster:id,title',
        'syllabus.courseLink.courseMaster:id,course_title,course_code',
      ])
      ->get(['syllabus_id', 'shift']);

    $syllabusShiftMap = $allSubjectsRoutines
      ->groupBy('syllabus_id')
      ->map(function ($rows) {
        return $rows
          ->pluck('shift')
          ->map(fn($shift) => strtolower(trim((string) $shift)))
          ->filter()
          ->unique()
          ->values();
      });

    $distinctSubjectRoutines = $allSubjectsRoutines
      ->unique('syllabus_id')
      ->values();

    $assignedSubjects = $distinctSubjectRoutines
      ->take(5)
      ->map(function ($routine) use ($syllabusShiftMap) {
        $syllabus = $routine->syllabus;
        if (!$syllabus) return null;

        $courseId = (int) ($syllabus->course_id ?? 0);
        $assignedShifts = collect($syllabusShiftMap->get((int) $syllabus->id, []))
          ->map(fn($shift) => strtolower(trim((string) $shift)))
          ->filter()
          ->unique()
          ->values();

        $baseManagersQuery = SyllabusManager::where('subject_id', $syllabus->subject_id)
          ->where('batch_id', $syllabus->batch_id)
          ->where('semester_id', $syllabus->semester_id)
          ->when($courseId > 0, function ($query) use ($courseId) {
            $query->where('co_id', $courseId);
          })
          ->when(!empty($syllabus->program_type), function ($query) use ($syllabus) {
            $query->where('program_type', $syllabus->program_type);
          })
          ->with('syllabusSubunits');

        if ($assignedShifts->isNotEmpty()) {
          $syllabusManagers = (clone $baseManagersQuery)
            ->whereIn('shift', $assignedShifts->all())
            ->get();

          if ($syllabusManagers->isEmpty()) {
            $syllabusManagers = (clone $baseManagersQuery)
              ->where(function ($query) {
                $query->whereNull('shift')
                  ->orWhere('shift', '')
                  ->orWhere('shift', 'common');
              })
              ->get();
          }
        } else {
          $syllabusManagers = (clone $baseManagersQuery)
            ->where(function ($query) {
              $query->whereNull('shift')
                ->orWhere('shift', '')
                ->orWhere('shift', 'common');
            })
            ->get();

          if ($syllabusManagers->isEmpty()) {
            $syllabusManagers = $baseManagersQuery->get();
          }
        }

        $uniqueUnits = $syllabusManagers
          ->pluck('syllabusSubunits')
          ->flatten(1)
          ->unique('id')
          ->values();

        $totalUnits = $uniqueUnits->count();
        $completedUnits = $uniqueUnits->where('is_completed', 1)->count();

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

    $totalSubjectsCount = $distinctSubjectRoutines->count();

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

    $hasTeachingAllocationLink = Schema::hasColumn('subject_has_routines', 'teaching_allocation_id');

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
        'teachingAssignment:id,course_id,faculty_id,delivery_type,allocation_group,room',
        'teachingAssignment.course:id,course_code,course_title,course_type',
        'teachingAssignment.course.coursetypemaster:id,title',
        'teachingAssignment.faculty:id,FIRST_NAME,LAST_NAME,USER_CODE',
        'teachingAssignment.primaryFacultyMembers:id,FIRST_NAME,LAST_NAME,USER_CODE',
        'teachingAssignment.coFacultyMembers:id,FIRST_NAME,LAST_NAME,USER_CODE',
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
        'teachingAllocation.faculty:id,FIRST_NAME,LAST_NAME,USER_CODE',
        'teachingAllocation.primaryFacultyMembers:id,FIRST_NAME,LAST_NAME,USER_CODE',
        'teachingAllocation.coFacultyMembers:id,FIRST_NAME,LAST_NAME,USER_CODE',
      ]);
    }

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
      ->map(function ($routine) use ($programType, $hasTeachingAllocationLink) {
        $assignment = $routine->teachingAssignment;
        if (!$assignment && $hasTeachingAllocationLink) {
          $assignment = $routine->teachingAllocation;
        }
        $course = $routine->subjectCourse->courseMaster ?? optional($assignment)->course;
        $primaryFacultyNames = collect($assignment?->primaryFacultyMembers ?? [])
          ->map(fn($primaryFaculty) => trim((string) ($primaryFaculty->FIRST_NAME ?? '') . ' ' . (string) ($primaryFaculty->LAST_NAME ?? '')))
          ->filter()
          ->values()
          ->all();

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
          'faculty' => !empty($primaryFacultyNames)
            ? implode(', ', $primaryFacultyNames)
            : trim((string) ($assignment?->faculty?->FIRST_NAME ?? '') . ' ' . (string) ($assignment?->faculty?->LAST_NAME ?? '')),
          'co_faculty' => collect($assignment?->coFacultyMembers ?? [])
            ->map(fn($coFaculty) => trim((string) ($coFaculty->FIRST_NAME ?? '') . ' ' . (string) ($coFaculty->LAST_NAME ?? '')))
            ->filter()
            ->values()
            ->all(),
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
    $hasTeachingAllocationLink = Schema::hasColumn('subject_has_routines', 'teaching_allocation_id');
    $searchTerm = trim((string) $request->query('search', ''));
    $requestedProgramType = strtoupper(trim((string) $request->query('program_type', 'ALL')));
    $programType = in_array($requestedProgramType, ['UG', 'PG', 'ALL'], true) ? $requestedProgramType : 'ALL';

    $routineSelectColumns = ['syllabus_id', 'shift', 'teaching_group_id', 'teaching_assignment_id'];
    if ($hasTeachingAllocationLink) {
      $routineSelectColumns[] = 'teaching_allocation_id';
    }

    $assignedRoutines = SubjectHasRoutine::where(function ($query) use ($facultyId) {
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
      ->whereNotNull('syllabus_id')
      ->get($routineSelectColumns);

    $syllabusShiftMap = $assignedRoutines
      ->groupBy('syllabus_id')
      ->map(function ($rows) {
        return $rows
          ->pluck('shift')
          ->map(fn($shift) => strtolower(trim((string) $shift)))
          ->filter()
          ->unique()
          ->values();
      });

    $syllabusContextMap = $assignedRoutines
      ->groupBy('syllabus_id')
      ->map(function ($rows) {
        $first = $rows->first();
        return [
          'teaching_group_id' => (int) ($first->teaching_group_id ?? 0),
          'teaching_assignment_id' => (int) (($first->teaching_assignment_id ?? 0) ?: ($first->teaching_allocation_id ?? 0)),
        ];
      });

    $assignedSyllabusIds = $assignedRoutines
      ->pluck('syllabus_id')
      ->filter()
      ->map(fn($id) => (int) $id)
      ->unique()
      ->values();

    // Prefer exact syllabus assignments from routines. Fallback to subject-level mapping if no routines exist.
    if ($assignedSyllabusIds->isNotEmpty()) {
      $dataQuery = SubjectHasSyllabus::whereIn('id', $assignedSyllabusIds)
        ->with([
          'subject:id,title',
          'batchmaster:id,batch_name',
          'semestermaster:id,title',
          'courseLink.courseMaster.coursetypemaster',
        ]);
    } else {
      $assignedSubjectIds = SubjectFacultyMaster::where('faculty_id', $facultyId)
        ->pluck('subject_id')
        ->unique();

      $dataQuery = SubjectHasSyllabus::whereIn('subject_id', $assignedSubjectIds)
        ->with([
          'subject:id,title',
          'batchmaster:id,batch_name',
          'semestermaster:id,title',
          'courseLink.courseMaster.coursetypemaster',
        ]);
    }

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

    $subjectsPaginator = $dataQuery
      ->orderByDesc('id')
      ->paginate(8)
      ->withQueryString();

    $data = collect($subjectsPaginator->items());

    // Load all syllabus managers (CSOs) with their units for each subject
    $data->each(function ($syllabus) use ($syllabusShiftMap, $syllabusContextMap) {
      $courseId = (int) ($syllabus->course_id ?? 0);
      $assignedShifts = collect($syllabusShiftMap->get((int) $syllabus->id, []))
        ->map(fn($shift) => strtolower(trim((string) $shift)))
        ->filter()
        ->unique()
        ->values();

      // Build scoped base query for this exact course offering.
      $baseManagersQuery = SyllabusManager::where('subject_id', $syllabus->subject_id)
        ->where('batch_id', $syllabus->batch_id)
        ->where('semester_id', $syllabus->semester_id)
        ->when($courseId > 0, function ($query) use ($courseId) {
          $query->where('co_id', $courseId);
        })
        ->when(!empty($syllabus->program_type), function ($query) use ($syllabus) {
          $query->where('program_type', $syllabus->program_type);
        })
        ->with([
          'cso:id,title,lectures_needed',
          'syllabusSubunits.csoSubunit.taxonomies.rbtmaster'
        ]);

      // Shift-aware fetch: prefer assigned shift rows; fallback to common when shift-specific rows do not exist.
      if ($assignedShifts->isNotEmpty()) {
        $syllabusManagers = (clone $baseManagersQuery)
          ->whereIn('shift', $assignedShifts->all())
          ->get();

        if ($syllabusManagers->isEmpty()) {
          $syllabusManagers = (clone $baseManagersQuery)
            ->where(function ($query) {
              $query->whereNull('shift')
                ->orWhere('shift', '')
                ->orWhere('shift', 'common');
            })
            ->get();
        }
      } else {
        $syllabusManagers = (clone $baseManagersQuery)
          ->where(function ($query) {
            $query->whereNull('shift')
              ->orWhere('shift', '')
              ->orWhere('shift', 'common');
          })
          ->get();

        if ($syllabusManagers->isEmpty()) {
          $syllabusManagers = $baseManagersQuery->get();
        }
      }

      // Group by CSO and deduplicate units to avoid inflated counts.
      $syllabus->csoGroups = $syllabusManagers
        ->groupBy(function ($manager) {
          return (int) ($manager->cso_id ?? 0);
        })
        ->map(function ($managers) {
          $firstManager = $managers->first();
          $uniqueUnits = $managers
            ->pluck('syllabusSubunits')
            ->flatten(1)
            ->unique('id')
            ->values();

          return [
            'cso' => optional($firstManager)->cso,
            'units' => $uniqueUnits,
          ];
        })
        ->values();

      // Keep a flat deduplicated list for completion stats.
      $syllabus->syllabusunits = $syllabus->csoGroups
        ->pluck('units')
        ->flatten(1)
        ->unique('id')
        ->values();

      $syllabusContext = (array) ($syllabusContextMap->get((int) $syllabus->id, []) ?? []);
      $roster = collect();

      if ($courseId > 0) {
        $roster = $this->studentRosterEngine->getRoster($courseId, [
          'subject_id' => (int) ($syllabus->subject_id ?? 0),
          'batch_id' => (int) ($syllabus->batch_id ?? 0),
          'semester_id' => (int) ($syllabus->semester_id ?? 0),
          'program_type' => (string) ($syllabus->program_type ?? ''),
          'teaching_group_id' => (int) ($syllabusContext['teaching_group_id'] ?? 0),
          'teaching_assignment_id' => (int) ($syllabusContext['teaching_assignment_id'] ?? 0),
        ]);
      }

      $syllabus->roster_students = $roster
        ->map(function ($row) {
          return [
            'student_id' => (int) ($row['student_id'] ?? 0),
            'roll_no' => (string) ($row['roll_no'] ?? ''),
            'register_no' => (string) ($row['register_no'] ?? ''),
            'student_name' => (string) ($row['student_name'] ?? ''),
            'program_id' => (int) ($row['program_id'] ?? 0),
            'batch_id' => (int) ($row['batch_id'] ?? 0),
            'semester_id' => (int) ($row['semester_id'] ?? 0),
          ];
        })
        ->values();

      $syllabus->roster_count = (int) $syllabus->roster_students->count();
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
      'subjectsPaginator' => $subjectsPaginator,
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
