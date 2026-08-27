<?php

namespace App\Http\Controllers;

use App\Models\BatchMaster;
use App\Models\Semester;
use App\Models\StudentMaster;
use App\Models\StudentCourseInfo;
use App\Models\StudentCourseRoster;
use App\Models\StudentOfferingRegistration;
use App\Models\SubjectCourseOffering;
use App\Models\SubjectHasDeptAdmin;
use App\Models\SubjectTypeMaster;
use App\Http\Controllers\StaticController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CourseOfferingController extends Controller
{
    // ─────────────────────────────────────────────────────────────────────
    //  ADMIN / DEPARTMENT SIDE
    // ─────────────────────────────────────────────────────────────────────

  /** List all offerings for the logged-in dept's subject. */
  public function index(Request $request)
  {
    $subjectId = $this->resolveSubjectId();

    $batchFilter    = $request->input('batch_id');
    $semesterFilter = $request->input('semester_id');

    $query = SubjectCourseOffering::with([
      'batch',
      'semester',
      'courseType',
      'confirmedRegistrations',
      'waitlistedRegistrations',
    ])->where('subject_id', $subjectId);

    if ($batchFilter) {
      $query->where('batch_id', $batchFilter);
    }
    if ($semesterFilter) {
      $query->where('semester_id', $semesterFilter);
    }

    $offerings = $query->orderBy('batch_id')->orderBy('semester_id')->get();

    return view('admin.subject.course-offerings', [
      'offerings' => $offerings,
      'batches'   => BatchMaster::all(),
      'semesters' => Semester::all(),
      'courseTypes' => SubjectTypeMaster::all(),
      'subjectId' => $subjectId,
    ]);
  }

  /** Create a new offering. */
  public function store(Request $request)
  {
    $subjectId = $this->resolveSubjectId();

    $request->validate([
      'batch_id'        => 'required|exists:batch_masters,id',
      'semester_id'     => 'required|exists:semesters,id',
      'course_type_id'  => 'required|exists:subject_type_masters,id',
      'intake_capacity' => 'required|integer|min:1',
    ]);

    // Prevent duplicate offering for same subject+batch+semester+course_type
    $exists = SubjectCourseOffering::where('subject_id', $subjectId)
      ->where('batch_id', $request->batch_id)
      ->where('semester_id', $request->semester_id)
      ->where('course_type_id', $request->course_type_id)
      ->exists();

    if ($exists) {
      return redirect()->back()->with('error', 'An offering for this batch, semester and course type already exists.');
    }

    SubjectCourseOffering::create([
      'subject_id'               => $subjectId,
      'batch_id'                 => $request->batch_id,
      'semester_id'              => $request->semester_id,
      'course_type_id'           => $request->course_type_id,
      'intake_capacity'          => $request->intake_capacity,
      'is_registration_open'     => false,
      'registration_opens_at'    => $request->registration_opens_at ?: null,
      'registration_closes_at'   => $request->registration_closes_at ?: null,
    ]);

    return redirect()->back()->with('success', 'Course offering created successfully.');
  }

  /** Update intake capacity and registration window. */
  public function update(Request $request, $id)
  {
    $offering = SubjectCourseOffering::where('subject_id', $this->resolveSubjectId())->findOrFail($id);

    $request->validate([
      'intake_capacity'          => 'required|integer|min:1',
      'registration_opens_at'    => 'nullable|date',
      'registration_closes_at'   => 'nullable|date|after_or_equal:registration_opens_at',
    ]);

    $confirmedCount = $offering->confirmedRegistrations()->count();
    if ($request->intake_capacity < $confirmedCount) {
      return redirect()->back()->with(
        'error',
        "Cannot reduce capacity below the {$confirmedCount} already-confirmed registrations."
      );
    }

    $offering->intake_capacity         = $request->intake_capacity;
    $offering->registration_opens_at   = $request->registration_opens_at ?: null;
    $offering->registration_closes_at  = $request->registration_closes_at ?: null;
    $offering->save();

    return redirect()->back()->with('success', 'Offering updated successfully.');
  }

  /** Delete an offering (only if no confirmed registrations). */
  public function destroy($id)
  {
    $offering = SubjectCourseOffering::where('subject_id', $this->resolveSubjectId())->findOrFail($id);

    if ($offering->confirmedRegistrations()->exists()) {
      return redirect()->back()->with(
        'error',
        'Cannot delete this offering — students are already confirmed. Close registration and cancel them first.'
      );
    }

    $offering->delete();
    return redirect()->back()->with('success', 'Offering deleted.');
  }

  /** Toggle registration open / closed. */
  public function toggleRegistration($id)
  {
    $offering = SubjectCourseOffering::where('subject_id', $this->resolveSubjectId())->findOrFail($id);
    $offering->is_registration_open = !$offering->is_registration_open;
    $offering->save();

    $state = $offering->is_registration_open ? 'opened' : 'closed';
    return redirect()->back()->with('success', "Registration {$state} for this offering.");
  }

  /** View the full list of registrations for a single offering. */
  public function registrationList($id)
  {
    $offering = SubjectCourseOffering::with([
      'batch',
      'semester',
      'courseType',
      'registrations.student',
    ])->where('subject_id', $this->resolveSubjectId())->findOrFail($id);

    return view('admin.subject.offering-registrations', compact('offering'));
  }

  /** Admin-side cancel of a student registration; promotes first waitlisted. */
  public function adminCancelRegistration($registrationId)
  {
    $reg = StudentOfferingRegistration::with('offering')->findOrFail($registrationId);

    // Ensure the admin owns this offering's subject
    if ($reg->offering->subject_id !== $this->resolveSubjectId()) {
      abort(403);
    }

    DB::transaction(function () use ($reg) {
      $wasConfirmed = $reg->status === 'confirmed';
      $offeringId   = $reg->offering_id;

      $reg->status = 'cancelled';
      $reg->save();

      if ($wasConfirmed) {
        // Promote the first waitlisted entry to confirmed
        $next = StudentOfferingRegistration::where('offering_id', $offeringId)
          ->where('status', 'waitlisted')
          ->orderBy('queue_position')
          ->first();

        if ($next) {
          $next->status = 'confirmed';
          $next->save();
        }
      }
    });

    return redirect()->back()->with('success', 'Registration cancelled and queue updated.');
  }

    // ─────────────────────────────────────────────────────────────────────
    //  STUDENT SIDE
    // ─────────────────────────────────────────────────────────────────────

  /** Student sees offerings open for their batch. */
  public function studentView()
  {
    $student = $this->resolveStudent();

    $offerings = SubjectCourseOffering::with([
      'subject',
      'batch',
      'semester',
      'courseType',
      'confirmedRegistrations',
      'waitlistedRegistrations'
    ])
      ->where('batch_id', $student->batch)
      ->where('is_registration_open', true)
      ->get()
      ->map(function ($offering) use ($student) {
        $myReg = StudentOfferingRegistration::where('offering_id', $offering->id)
          ->where('student_id', $student->id)
          ->whereNotIn('status', ['cancelled'])
          ->first();
        $offering->my_registration = $myReg;
        return $offering;
      });

    $myRegistrations = StudentOfferingRegistration::with([
      'offering.subject',
      'offering.batch',
      'offering.semester',
      'offering.courseType'
    ])
      ->where('student_id', $student->id)
      ->whereNotIn('status', ['cancelled'])
      ->get();

    return view('student.course-offerings', compact('offerings', 'myRegistrations', 'student'));
  }

  /** Student registers in a FIFO queue. */
  public function studentRegister(Request $request)
  {
    $request->validate(['offering_id' => 'required|exists:subject_course_offerings,id']);

    $student  = $this->resolveStudent();
    $offering = SubjectCourseOffering::findOrFail($request->offering_id);

    if (!$offering->is_registration_open) {
      return redirect()->back()->with('error', 'Registration is not open for this offering.');
    }

    // Check batch match
    if ($offering->batch_id != $student->batch) {
      return redirect()->back()->with('error', 'This offering is not available for your batch.');
    }

    // Already registered?
    $already = StudentOfferingRegistration::where('offering_id', $offering->id)
      ->where('student_id', $student->id)
      ->whereNotIn('status', ['cancelled'])
      ->exists();

    if ($already) {
      return redirect()->back()->with('error', 'You are already registered for this offering.');
    }

    DB::transaction(function () use ($offering, $student) {
      $confirmedCount = $offering->confirmedRegistrations()->count();
      $totalCount     = StudentOfferingRegistration::where('offering_id', $offering->id)
        ->whereNotIn('status', ['cancelled'])
        ->count();

      $status        = $confirmedCount < $offering->intake_capacity ? 'confirmed' : 'waitlisted';
      $queuePosition = $totalCount + 1;

      StudentOfferingRegistration::create([
        'offering_id'    => $offering->id,
        'student_id'     => $student->id,
        'queue_position' => $queuePosition,
        'status'         => $status,
      ]);
    });

    $reg = StudentOfferingRegistration::where('offering_id', $offering->id)
      ->where('student_id', $student->id)
      ->whereNotIn('status', ['cancelled'])
      ->first();

    $msg = $reg->status === 'confirmed'
      ? 'You have been confirmed for this offering (Seat #' . $reg->queue_position . ').'
      : 'Seats are full. You have been added to the waitlist (Position #' . $reg->queue_position . ').';

    return redirect()->back()->with('success', $msg);
  }

  /** Student cancels their own registration; promotes first waitlisted. */
  public function studentCancel($registrationId)
  {
    $student = $this->resolveStudent();

    $reg = StudentOfferingRegistration::where('student_id', $student->id)
      ->findOrFail($registrationId);

    if ($reg->status === 'cancelled') {
      return redirect()->back()->with('error', 'Registration is already cancelled.');
    }

    DB::transaction(function () use ($reg) {
      $wasConfirmed = $reg->status === 'confirmed';
      $offeringId   = $reg->offering_id;

      $reg->status = 'cancelled';
      $reg->save();

      if ($wasConfirmed) {
        $next = StudentOfferingRegistration::where('offering_id', $offeringId)
          ->where('status', 'waitlisted')
          ->orderBy('queue_position')
          ->first();

        if ($next) {
          $next->status = 'confirmed';
          $next->save();
        }
      }
    });

    return redirect()->back()->with('success', 'Registration cancelled successfully.');
  }

  /**
   * Copy all roster-assigned courses for the logged-in student into student_course_infos.
   */
  public function syncRosterCoursesToStudentInfo()
  {
    $student = $this->resolveStudent();
    $academicYear = (string) (optional($student->batchmaster)->batch_name ?? date('Y'));

    $rosterRows = StudentCourseRoster::query()
      ->with('course:id,semester_id')
      ->where('student_id', (int) $student->id)
      ->orderBy('id')
      ->get()
      ->unique('course_id')
      ->values();

    if ($rosterRows->isEmpty()) {
      return redirect()->back()->with('error', 'No course roster records found for your account.');
    }

    $columns = $this->studentCourseInfoColumns();
    $added = 0;
    $restored = 0;
    $updated = 0;
    $invalid = 0;

    DB::transaction(function () use ($rosterRows, $student, $academicYear, $columns, &$added, &$restored, &$updated, &$invalid) {
      foreach ($rosterRows as $row) {
        $courseId = (int) ($row->course_id ?? 0);
        $semesterId = (int) ($row->course->semester_id ?? 0);

        if ($courseId <= 0 || $semesterId <= 0) {
          $invalid++;
          continue;
        }

        // Roster is source of truth; keep one course-info record per student+course.
        $existing = StudentCourseInfo::withTrashed()
          ->where('student_id', (int) $student->id)
          ->where('course_id', $courseId)
          ->orderByDesc('id')
          ->first();

        if ($existing) {
          $updates = [];
          if (in_array('semester', $columns, true)) {
            $updates['semester'] = $semesterId;
          }
          if (in_array('academic_year', $columns, true)) {
            $updates['academic_year'] = $academicYear;
          }
          if (in_array('is_active', $columns, true)) {
            $updates['is_active'] = 1;
          }
          if (in_array('campus_id', $columns, true)) {
            $updates['campus_id'] = (int) ($student->campus_id ?? 0);
          }

          if ($existing->trashed()) {
            $existing->restore();

            if (!empty($updates)) {
              $existing->update($updates);
            }

            $restored++;
          } else {
            if (!empty($updates)) {
              $existing->update($updates);
            }
            $updated++;
          }
          continue;
        }

        $payload = [
          'student_id' => (int) $student->id,
          'course_id' => $courseId,
          'semester' => $semesterId,
          'academic_year' => $academicYear,
          'campus_id' => (int) ($student->campus_id ?? 0),
          'is_active' => 1,
          'is_elective' => 0,
        ];

        $filteredPayload = collect($payload)
          ->filter(fn($value, $key) => in_array($key, $columns, true))
          ->all();

        if (!empty($filteredPayload)) {
          StudentCourseInfo::create($filteredPayload);
          $added++;
        }
      }
    });

    $parts = [];
    if ($added > 0) {
      $parts[] = $added . ' added';
    }
    if ($restored > 0) {
      $parts[] = $restored . ' restored';
    }
    if ($updated > 0) {
      $parts[] = $updated . ' updated';
    }
    if ($invalid > 0) {
      $parts[] = $invalid . ' skipped (invalid semester/course)';
    }

    if (empty($parts)) {
      return redirect()->back()->with('error', 'No eligible courses found to sync.');
    }

    return redirect()->back()->with('success', 'Roster sync completed: ' . implode(', ', $parts) . '.');
  }

  /**
   * ITCELL utility: sync roster-assigned courses into student_course_infos for all students.
   */
  public function syncRosterCoursesForAllStudents()
  {
    $userRole = (string) StaticController::fetchUserRole((int) Auth::id());
    if (!in_array($userRole, ['itcell', 'super-admin'], true)) {
      abort(403, 'Only ITCELL can execute this action.');
    }

    $columns = $this->studentCourseInfoColumns();
    if (empty($columns)) {
      return redirect()->back()->with('error', 'Unable to resolve student course info schema.');
    }

    $rows = DB::table('student_course_rosters as scr')
      ->join('program_course_masters as pcm', 'pcm.id', '=', 'scr.course_id')
      ->join('student_masters as sm', 'sm.id', '=', 'scr.student_id')
      ->leftJoin('batch_masters as bm', 'bm.id', '=', 'sm.batch')
      ->whereNull('scr.deleted_at')
      ->select([
        'scr.student_id',
        'scr.course_id',
        'pcm.semester_id',
        'sm.campus_id',
        'bm.batch_name',
      ])
      ->groupBy([
        'scr.student_id',
        'scr.course_id',
        'pcm.semester_id',
        'sm.campus_id',
        'bm.batch_name',
      ])
      ->orderBy('scr.student_id')
      ->orderBy('scr.course_id')
      ->get();

    if ($rows->isEmpty()) {
      return redirect()->back()->with('error', 'No roster records found to sync.');
    }

    $added = 0;
    $restored = 0;
    $updated = 0;
    $invalid = 0;

    foreach ($rows as $row) {
      $studentId = (int) ($row->student_id ?? 0);
      $courseId = (int) ($row->course_id ?? 0);
      $semesterId = (int) ($row->semester_id ?? 0);
      $academicYear = trim((string) ($row->batch_name ?? ''));
      if ($academicYear === '') {
        $academicYear = (string) date('Y');
      }

      if ($studentId <= 0 || $courseId <= 0 || $semesterId <= 0) {
        $invalid++;
        continue;
      }

      // Roster is the source of truth; keep one course-info record per student+course.
      $existing = StudentCourseInfo::withTrashed()
        ->where('student_id', $studentId)
        ->where('course_id', $courseId)
        ->orderByDesc('id')
        ->first();

      if ($existing) {
        $updates = [];
        if (in_array('semester', $columns, true)) {
          $updates['semester'] = $semesterId;
        }
        if (in_array('academic_year', $columns, true)) {
          $updates['academic_year'] = $academicYear;
        }
        if (in_array('is_active', $columns, true)) {
          $updates['is_active'] = 1;
        }
        if (in_array('campus_id', $columns, true)) {
          $updates['campus_id'] = (int) ($row->campus_id ?? 0);
        }

        if ($existing->trashed()) {
          $existing->restore();
          if (!empty($updates)) {
            $existing->update($updates);
          }
          $restored++;
        } else {
          if (!empty($updates)) {
            $existing->update($updates);
          }
          $updated++;
        }
        continue;
      }

      $payload = [
        'student_id' => $studentId,
        'course_id' => $courseId,
        'semester' => $semesterId,
        'academic_year' => $academicYear,
        'campus_id' => (int) ($row->campus_id ?? 0),
        'is_active' => 1,
        'is_elective' => 0,
      ];

      $filteredPayload = collect($payload)
        ->filter(fn($value, $key) => in_array($key, $columns, true))
        ->all();

      if (!empty($filteredPayload)) {
        StudentCourseInfo::create($filteredPayload);
        $added++;
      }
    }

    return redirect()->back()->with('success', 'All-student roster sync completed: '
      . $added . ' added, '
      . $restored . ' restored, '
      . $updated . ' updated, '
      . $invalid . ' skipped (invalid rows).');
  }

  // ─────────────────────────────────────────────────────────────────────
  //  HELPERS
  // ─────────────────────────────────────────────────────────────────────

  private function resolveSubjectId(): int
  {
    return (int) SubjectHasDeptAdmin::where('user_id', Auth::id())->value('subject_id');
  }

  private function resolveStudent(): StudentMaster
  {
    return StudentMaster::findOrFail(Auth::user()->student_id);
  }

  private function studentCourseInfoColumns(): array
  {
    static $columns = null;

    if (is_array($columns)) {
      return $columns;
    }

    try {
      $columns = Schema::getColumnListing('student_course_infos');
    } catch (\Throwable $e) {
      $columns = [];
    }

    return $columns;
  }
}
