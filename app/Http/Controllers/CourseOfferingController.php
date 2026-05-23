<?php

namespace App\Http\Controllers;

use App\Models\BatchMaster;
use App\Models\Semester;
use App\Models\StudentMaster;
use App\Models\StudentOfferingRegistration;
use App\Models\SubjectCourseOffering;
use App\Models\SubjectHasDeptAdmin;
use App\Models\SubjectTypeMaster;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

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
}
