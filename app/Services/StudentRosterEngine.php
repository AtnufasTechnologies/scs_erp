<?php

namespace App\Services;

use App\Models\StudentMaster;
use App\Models\StudentRosterRuleMapping;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

class StudentRosterEngine
{
  /**
   * Return the complete student roster for a course.
   *
   * This should become the SINGLE entry point used by:
   *
   * - Faculty / Courses
   * - Attendance
   * - Examination
   * - Timetable student roster
   * - Any future module requiring students for a course
   */
  public function getStudentsForCourse($course, array $context = []): Collection
  {
    /*
        |--------------------------------------------------------------------------
        | 1. Get candidate students
        |--------------------------------------------------------------------------
        |
        | IMPORTANT:
        | We don't apply pathway rules here yet.
        |
        | Candidate generation should return the students who could
        | potentially belong to this course.
        |
        */

    $students = $this->getCandidateStudents($course, $context);

    /*
        |--------------------------------------------------------------------------
        | 2. Evaluate every student through the rule engine
        |--------------------------------------------------------------------------
        */

    return $students
      ->filter(function ($student) use ($course, $context) {

        return $this->qualifies(
          $student,
          $course,
          $context
        );
      })
      ->values();
  }


  /**
   * Check one student against the roster rules.
   */
  public function qualifies(
    StudentMaster $student,
    $course,
    array $context = []
  ): bool {

    /*
        |--------------------------------------------------------------------------
        | GLOBAL RULE #1
        |--------------------------------------------------------------------------
        |
        | Students who have left must NEVER appear anywhere.
        |
        */

    if ((int) $student->is_left === 1) {
      return false;
    }


    /*
        |--------------------------------------------------------------------------
        | GLOBAL RULE #2
        |--------------------------------------------------------------------------
        |
        | Student must have an academic pathway.
        |
        */

    $academicPathwayId = $this->getAcademicPathwayId($student);

    if (!$academicPathwayId) {

      $this->debugDecision(
        $student,
        $course,
        null,
        false,
        'NO_ACADEMIC_PATHWAY',
        'Student has no academic pathway.'
      );

      return false;
    }


    /*
        |--------------------------------------------------------------------------
        | Degree Track
        |--------------------------------------------------------------------------
        */

    $degreeTrackId = $this->getDegreeTrackId($student);


    /*
        |--------------------------------------------------------------------------
        | Resolve rule
        |--------------------------------------------------------------------------
        */

    $rule = $this->resolveRule(
      $academicPathwayId,
      $degreeTrackId,
      $course
    );


    /*
        |--------------------------------------------------------------------------
        | No rule found
        |--------------------------------------------------------------------------
        |
        | NEVER silently drop the student.
        |
        */

    if (!$rule) {

      $this->debugDecision(
        $student,
        $course,
        null,
        false,
        'NO_RULE',
        'No StudentRoster rule matched this student/course combination.',
        [
          'academic_pathway_id' => $academicPathwayId,
          'degree_track_id' => $degreeTrackId,
          'delivery_type' => $course->delivery_type ?? null,
          'selection_type' => $course->selection_type ?? null,
        ]
      );

      return false;
    }


    /*
        |--------------------------------------------------------------------------
        | Teaching Group
        |--------------------------------------------------------------------------
        |
        | Teaching Group is special.
        |
        | It can combine:
        |
        | - multiple courses
        | - multiple programs
        | - multiple batches
        | - multiple semesters
        |
        | Therefore it is checked before normal semester/batch restrictions.
        |
        */

    if (
      (bool) $rule->teaching_group_override &&
      $this->isInTeachingGroup($student, $course, $context)
    ) {

      $this->debugDecision(
        $student,
        $course,
        $rule,
        true,
        'TEACHING_GROUP',
        'Student included through Teaching Group.'
      );

      return true;
    }


    /*
        |--------------------------------------------------------------------------
        | Semester Scope
        |--------------------------------------------------------------------------
        */

    if (
      !$this->passesSemesterScope(
        $student,
        $course,
        $rule
      )
    ) {

      $this->debugDecision(
        $student,
        $course,
        $rule,
        false,
        'SEMESTER_SCOPE',
        'Student failed semester scope.'
      );

      return false;
    }


    /*
        |--------------------------------------------------------------------------
        | Batch Scope
        |--------------------------------------------------------------------------
        */

    if (
      !$this->passesBatchScope(
        $student,
        $course,
        $rule
      )
    ) {

      $this->debugDecision(
        $student,
        $course,
        $rule,
        false,
        'BATCH_SCOPE',
        'Student failed batch scope.'
      );

      return false;
    }


    /*
        |--------------------------------------------------------------------------
        | Specialization
        |--------------------------------------------------------------------------
        */

    if (
      !$this->passesSpecializationScope(
        $student,
        $course,
        $rule
      )
    ) {

      $this->debugDecision(
        $student,
        $course,
        $rule,
        false,
        'SPECIALIZATION_SCOPE',
        'Student failed specialization applicability.'
      );

      return false;
    }


    /*
        |--------------------------------------------------------------------------
        | Major Restriction
        |--------------------------------------------------------------------------
        */

    if (
      !$this->passesMajorRestriction(
        $student,
        $course,
        $rule
      )
    ) {

      $this->debugDecision(
        $student,
        $course,
        $rule,
        false,
        'MAJOR_RESTRICTION',
        'Student failed major restriction.'
      );

      return false;
    }


    /*
        |--------------------------------------------------------------------------
        | Finally execute the rule
        |--------------------------------------------------------------------------
        */

    $result = $this->executeRosterSource(
      $student,
      $course,
      $rule,
      $context
    );


    $this->debugDecision(
      $student,
      $course,
      $rule,
      $result,
      $result
        ? 'INCLUDED'
        : 'ROSTER_SOURCE_REJECTED',
      $result
        ? 'Student included by roster rule.'
        : 'Student did not satisfy roster source.'
    );


    return $result;
  }


    // ========================================================================
    // RULE RESOLUTION
    // ========================================================================


  /**
   * Resolve the applicable rule from the database.
   *
   * Priority:
   *
   * 1. Exact pathway
   * 2. Exact degree track
   * 3. Exact delivery type
   * 4. Exact selection type
   * 5. Active rule
   * 6. Lowest priority number
   */
  protected function resolveRule(
    int $academicPathwayId,
    ?int $degreeTrackId,
    $course
  ): ?StudentRosterRuleMapping {

    return StudentRosterRuleMapping::query()

      ->with([
        'rule',
        'academicPathway',
        'degreeTrack',
      ])

      /*
             * Academic Pathway MUST match.
             */
      ->where(
        'academic_pathway_id',
        $academicPathwayId
      )

      /*
             * Degree Track:
             *
             * Exact track OR generic NULL rule.
             */
      ->where(function ($query) use ($degreeTrackId) {

        $query
          ->where(
            'degree_track_id',
            $degreeTrackId
          )
          ->orWhereNull('degree_track_id');
      })

      /*
             * Delivery type MUST match.
             */
      ->where(
        'delivery_type',
        $course->delivery_type
      )

      /*
             * Selection type:
             *
             * Exact match OR generic rule.
             */
      ->where(function ($query) use ($course) {

        $selectionType =
          $course->selection_type ?? null;

        $query
          ->where(
            'selection_type',
            $selectionType
          )
          ->orWhereNull('selection_type');
      })

      ->where('is_active', true)

      /*
             * Most specific rule first.
             */
      ->orderByRaw(
        'CASE
                    WHEN degree_track_id IS NULL THEN 1
                    ELSE 0
                 END'
      )

      ->orderByRaw(
        'CASE
                    WHEN selection_type IS NULL THEN 1
                    ELSE 0
                 END'
      )

      ->orderBy('priority')

      ->first();
  }


    // ========================================================================
    // ROSTER SOURCE
    // ========================================================================


  /**
   * Execute the source defined by the rule.
   */
  protected function executeRosterSource(
    StudentMaster $student,
    $course,
    StudentRosterRuleMapping $rule,
    array $context = []
  ): bool {

    switch ($rule->roster_source) {

      /*
             * COMBO1
             */
      case 'COMBO1':

        return $this->qualifiesThroughCombo(
          $student,
          $course,
          'COMBO1'
        );


        /*
             * COMBO2
             */
      case 'COMBO2':

        return $this->qualifiesThroughCombo(
          $student,
          $course,
          'COMBO2'
        );


        /*
             * Student-selected MDC/Common/etc.
             */
      case 'STUDENT_SELECTION':

        return $this->qualifiesThroughStudentSelection(
          $student,
          $course
        );


        /*
             * Curriculum applicability.
             */
      case 'CURRICULUM':

        return $this->qualifiesThroughCurriculum(
          $student,
          $course
        );


        /*
             * Explicit Teaching Group.
             */
      case 'TEACHING_GROUP':

        return $this->isInTeachingGroup(
          $student,
          $course,
          $context
        );


      default:

        Log::warning(
          'StudentRosterEngine: Unknown roster source',
          [
            'roster_source' =>
            $rule->roster_source,

            'rule_id' =>
            $rule->id,

            'student_id' =>
            $student->id,

            'course_id' =>
            $course->id ?? null,
          ]
        );

        return false;
    }
  }


    // ========================================================================
    // ACADEMIC PATHWAY
    // ========================================================================


  /**
   * Get student's academic pathway.
   *
   * IMPORTANT:
   * Adjust this method to your actual StudentMaster relationship/column.
   */
  protected function getAcademicPathwayId(
    StudentMaster $student
  ): ?int {

    /*
         * Preferred if StudentMaster has:
         *
         * academicPathway relationship
         */

    if (
      isset($student->academic_pathway_id) &&
      $student->academic_pathway_id
    ) {
      return (int) $student->academic_pathway_id;
    }

    if (
      isset($student->academicPathway) &&
      $student->academicPathway
    ) {
      return (int) $student->academicPathway->id;
    }

    return null;
  }


  /**
   * Get student's degree track.
   *
   * Adjust to your actual StudentMaster structure if necessary.
   */
  protected function getDegreeTrackId(
    StudentMaster $student
  ): ?int {

    if (
      isset($student->degree_track_id) &&
      $student->degree_track_id
    ) {
      return (int) $student->degree_track_id;
    }

    if (
      isset($student->degreeTrack) &&
      $student->degreeTrack
    ) {
      return (int) $student->degreeTrack->id;
    }

    return null;
  }


  // ========================================================================
  // SEMESTER
  // ========================================================================


  protected function passesSemesterScope(
    StudentMaster $student,
    $course,
    StudentRosterRuleMapping $rule
  ): bool {

    if ($rule->semester_scope === 'ANY') {
      return true;
    }

    if ($rule->semester_scope !== 'SAME') {
      return true;
    }

    /*
         * For normal course delivery:
         *
         * student semester must match course semester.
         *
         * Teaching Groups are already handled before this method.
         */

    if (
      !isset($student->semester_id) ||
      !isset($course->semester_id)
    ) {
      return true;
    }

    return (int) $student->semester_id ===
      (int) $course->semester_id;
  }


  // ========================================================================
  // BATCH
  // ========================================================================


  protected function passesBatchScope(
    StudentMaster $student,
    $course,
    StudentRosterRuleMapping $rule
  ): bool {

    if ($rule->batch_scope === 'ANY') {
      return true;
    }

    if ($rule->batch_scope !== 'SAME') {
      return true;
    }

    if (
      !isset($student->batch_id) ||
      !isset($course->batch_id)
    ) {
      return true;
    }

    return (int) $student->batch_id ===
      (int) $course->batch_id;
  }


  // ========================================================================
  // SPECIALIZATION
  // ========================================================================


  protected function passesSpecializationScope(
    StudentMaster $student,
    $course,
    StudentRosterRuleMapping $rule
  ): bool {

    /*
         * Specialization is handled by your existing
         * specialization/curriculum applicability system.
         *
         * Do not invent a second specialization algorithm here.
         */

    if ($rule->specialization_scope === 'ANY') {
      return true;
    }

    /*
         * If your existing curriculum engine already determines
         * specialization applicability, call it here.
         */

    return $this->studentCourseSpecializationApplies(
      $student,
      $course,
      $rule
    );
  }


  protected function studentCourseSpecializationApplies(
    StudentMaster $student,
    $course,
    StudentRosterRuleMapping $rule
  ): bool {

    /*
         * Placeholder for your existing specialization logic.
         *
         * IMPORTANT:
         * Do not return false here until connected to your existing
         * specialization enrollment/curriculum logic.
         */

    return true;
  }


  // ========================================================================
  // MAJOR RESTRICTION
  // ========================================================================


  protected function passesMajorRestriction(
    StudentMaster $student,
    $course,
    StudentRosterRuleMapping $rule
  ): bool {

    if ($rule->major_restriction === 'NONE') {
      return true;
    }


    if (
      $rule->major_restriction ===
      'EXCLUDE_MAJOR_DEPARTMENTS'
    ) {

      return !$this->courseBelongsToStudentsMajorDepartment(
        $student,
        $course
      );
    }


    return true;
  }


  /**
   * Dual Major MDC student-choice rule:
   *
   * Student cannot select MDC from either of their
   * two Major departments.
   *
   * Connect this to your existing Major/pathway structure.
   */
  protected function courseBelongsToStudentsMajorDepartment(
    StudentMaster $student,
    $course
  ): bool {

    /*
         * IMPORTANT:
         *
         * Replace this with your existing major-department
         * relationship.
         *
         * This is intentionally isolated so the main engine
         * doesn't know your exact major implementation.
         */

    return false;
  }


    // ========================================================================
    // COMBO
    // ========================================================================


  /**
   * Determine whether the student is applicable through
   * COMBO1 or COMBO2.
   *
   * Connect this to your existing curriculum/enrollment logic.
   */
  protected function qualifiesThroughCombo(
    StudentMaster $student,
    $course,
    string $combo
  ): bool {

    /*
         * IMPORTANT:
         *
         * Your existing implementation already knows how to
         * determine whether a student belongs to COMBO1/COMBO2.
         *
         * Move that existing logic into this method.
         */

    return false;
  }


    // ========================================================================
    // STUDENT SELECTION
    // ========================================================================


  /**
   * Check student's explicit course selection.
   */
  protected function qualifiesThroughStudentSelection(
    StudentMaster $student,
    $course
  ): bool {

    /*
         * Connect this to the existing student selection table.
         *
         * Example conceptually:
         *
         * return StudentCourseSelection::query()
         *     ->where('student_id', $student->id)
         *     ->where('course_id', $course->id)
         *     ->exists();
         */

    return false;
  }


  // ========================================================================
  // CURRICULUM
  // ========================================================================


  protected function qualifiesThroughCurriculum(
    StudentMaster $student,
    $course
  ): bool {

    /*
         * Use your existing CurriculumEngine applicability logic.
         *
         * The roster engine should NOT recreate the curriculum engine.
         */

    return true;
  }


  // ========================================================================
  // TEACHING GROUP
  // ========================================================================


  protected function isInTeachingGroup(
    StudentMaster $student,
    $course,
    array $context = []
  ): bool {

    /*
         * Connect this to your existing Teaching Group implementation.
         *
         * Teaching Groups are allowed to combine:
         *
         * - courses
         * - programs
         * - semesters
         * - batches
         *
         * Therefore this is intentionally evaluated before
         * normal SAME semester/batch restrictions.
         */

    return false;
  }


  // ========================================================================
  // CANDIDATE STUDENTS
  // ========================================================================


  protected function getCandidateStudents(
    $course,
    array $context = []
  ): Collection {

    /*
         * IMPORTANT:
         *
         * This should be your broad candidate query.
         *
         * Do NOT put the complete roster logic here.
         *
         * The rule engine must decide eligibility.
         */

    return StudentMaster::query()

      /*
             * NEVER include students who have left.
             */
      ->where('is_left', '!=', 1)

      ->get();
  }


  // ========================================================================
  // DEBUGGING
  // ========================================================================


  protected function debugDecision(
    StudentMaster $student,
    $course,
    ?StudentRosterRuleMapping $rule,
    bool $included,
    string $reasonCode,
    string $reason,
    array $extra = []
  ): void {

    /*
         * During initial rollout, logging is extremely useful.
         *
         * This lets us identify exactly why a student disappeared
         * from a roster.
         */

    Log::debug(
      'StudentRosterEngine decision',
      array_merge(
        [
          'student_id' =>
          $student->id,

          'course_id' =>
          $course->id ?? null,

          'included' =>
          $included,

          'reason_code' =>
          $reasonCode,

          'reason' =>
          $reason,

          'rule_id' =>
          $rule?->id,

          'rule_code' =>
          $rule?->rule?->rule_code,

          'academic_pathway_id' =>
          $this->getAcademicPathwayId($student),

          'degree_track_id' =>
          $this->getDegreeTrackId($student),

          'delivery_type' =>
          $course->delivery_type ?? null,

          'selection_type' =>
          $course->selection_type ?? null,
        ],
        $extra
      )
    );
  }
}
