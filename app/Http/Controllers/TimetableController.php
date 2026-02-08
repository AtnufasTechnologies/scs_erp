<?php

namespace App\Http\Controllers;

use App\Models\BatchMaster;
use App\Models\Faculty;
use App\Models\HourMaster;
use App\Models\LectureHallMaster;
use App\Models\ProgramCourseMaster;
use App\Models\Semester;
use App\Models\Subject;
use App\Models\SubjectCourseMaster;
use App\Models\SubjectFacultyMaster;
use App\Models\SubjectHasRoutine;
use App\Models\SubjectHasSemester;
use App\Models\SubjectHasSyllabus;
use App\Models\Weekday;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TimetableController extends Controller
{
    function index($id)
    {
        $data = Subject::find($id);
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

        return view('admin.subject.timetable', [
            'data' => $data,
            'subjectSemesters' => $subjectSemesters,
            'semesterMasters' => $semesterMasters,
            'batches' => $batches,
        ]);
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

    function getTimetableData($subjectId, $batchId, $semesterId)
    {
        try {
            // Get all syllabi for this subject/batch/semester
            $syllabi = SubjectHasSyllabus::where('subject_id', $subjectId)
                ->where('batch_id', $batchId)
                ->where('semester_id', $semesterId)
                ->with(['subject'])
                ->get();

            if ($syllabi->isEmpty()) {
                return response()->json([
                    'success' => true,
                    'data' => []
                ]);
            }

            // Get routine data for all syllabi
            $syllabusIds = $syllabi->pluck('id');
            $routines = SubjectHasRoutine::whereIn('syllabus_id', $syllabusIds)->get();

            $weekdays = [1 => 'Monday', 2 => 'Tuesday', 3 => 'Wednesday', 4 => 'Thursday', 5 => 'Friday', 6 => 'Saturday'];
            $timetableData = [];

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
            $allFaculties = Faculty::whereIn('id', $routines->pluck('faculty_id')->filter())
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
                        'subject_id' => $lookupCourseId ?: $syllabus->subject_id, // Return proper course id
                        'teacher_id' => $facultyId,
                        'subject_name' => $courseName ?: ($syllabus->subject->subject_title ?? $syllabus->subject->title ?? 'Subject'),
                        'teacher_name' => $facultyName ?: 'Teacher'
                    ];
                }
            }

            return response()->json([
                'success' => true,
                'data' => $timetableData
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch timetable: ' . $e->getMessage()
            ], 500);
        }
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

    function clearAllRoutines($subjectId, $batchId, $semesterId)
    {
        try {
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

            // Delete all routines for these syllabi
            $deletedCount = SubjectHasRoutine::whereIn('syllabus_id', $syllabusIds)->count();
            SubjectHasRoutine::whereIn('syllabus_id', $syllabusIds)->delete();

            return response()->json([
                'success' => true,
                'message' => "Successfully cleared {$deletedCount} timetable entries"
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
            // Handle bulk timetable save from grid
            if ($request->has('timetable')) {
                return $this->storeBulkTimetableNew($request, $subjectId, $batchId, $semesterId);
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
                ->exists();

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
                ->exists();

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
                'weekday_id' => $validated['weekday_id'],
                'hour_id' => $validated['hour_id'],
            ];

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

    private function storeBulkTimetableNew(Request $request, $subjectId, $batchId, $semesterId)
    {
        try {
            $timetable = $request->input('timetable', []);
            $savedCount = 0;

            // Get weekday mappings
            $weekdays = [
                'Monday' => 1,
                'Tuesday' => 2,
                'Wednesday' => 3,
                'Thursday' => 4,
                'Friday' => 5,
                'Saturday' => 6
            ];

            // Clear existing routines for this subject/batch/semester first
            $existingSyllabi = SubjectHasSyllabus::where('subject_id', $subjectId)
                ->where('batch_id', $batchId)
                ->where('semester_id', $semesterId)
                ->pluck('id');

            SubjectHasRoutine::whereIn('syllabus_id', $existingSyllabi)->delete();

            foreach ($timetable as $slot) {
                if (empty($slot['subject_id']) || empty($slot['day_of_week']) || empty($slot['hour_number'])) {
                    continue;
                }

                $dayName = $slot['day_of_week'];
                $hourNumber = (int)$slot['hour_number'];
                $courseMasterId = $slot['subject_id']; // This is course_master_id from frontend
                $facultyId = $slot['teacher_id'] ?? null;

                if (!isset($weekdays[$dayName])) continue;
                $weekdayId = $weekdays[$dayName];

                // Find the subject_course_master record ID for this course_master_id
                $subjectCourseRecord = SubjectCourseMaster::where('subject_id', $subjectId)
                    ->where('course_master_id', $courseMasterId)
                    ->first();

                $subjectCourseId = $subjectCourseRecord ? $subjectCourseRecord->id : null;

                // Create or get syllabus for this specific course
                $syllabus = SubjectHasSyllabus::firstOrCreate([
                    'subject_id' => $subjectId,
                    'batch_id' => $batchId,
                    'semester_id' => $semesterId,
                    'course_id' => $courseMasterId, // Store the course_master_id
                ]);

                // Create routine entry with proper column assignments
                SubjectHasRoutine::create([
                    'syllabus_id' => $syllabus->id,
                    'batch_id' => $batchId, // Add batch_id for substitution
                    'weekday_id' => $weekdayId,
                    'hour_id' => $hourNumber,
                    'faculty_id' => $facultyId, // Use proper faculty_id column
                    'subject_course_id' => $subjectCourseId, // Store subject_course_master ID
                    'lecturehall_id' => null, // Keep this for actual lecture hall if needed
                    'substitution_faculty_id' => null // Keep null for now
                ]);
                $savedCount++;
            }

            return response()->json([
                'success' => true,
                'message' => "Timetable saved successfully. {$savedCount} slots created."
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

    function substitution($subjectId)
    {
        $data = Subject::findOrFail($subjectId);
        $batches = BatchMaster::latest()->get();

        return view('admin.subject.substitution', [
            'data' => $data,
            'batches' => $batches,
        ]);
    }

    function getSubstitutionSchedule($batchId, $day)
    {
        try {
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
                ->with([
                    'faculty:id,FIRST_NAME,LAST_NAME,USER_CODE',
                    'substitutionFaculty:id,FIRST_NAME,LAST_NAME,USER_CODE',
                    'subjectCourse.courseMaster:id,course_title,course_code',
                    'subjectCourse.subject:id,title,code'
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
                    'subject_title' => $routine->subjectCourse->subject->title ?? 'N/A',
                    'subject_code' => $routine->subjectCourse->subject->code ?? 'N/A',
                    'course_title' => $routine->subjectCourse->courseMaster->course_title ?? 'N/A',
                    'course_code' => $routine->subjectCourse->courseMaster->course_code ?? 'N/A',
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
}
