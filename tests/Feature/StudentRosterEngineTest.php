<?php

namespace Tests\Feature;

use App\Models\ProgramWiseSemesterCourse;
use App\Services\StudentRosterEngine;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class StudentRosterEngineTest extends TestCase
{
  use RefreshDatabase;

  protected function setUp(): void
  {
    parent::setUp();

    $this->ensureRosterTables();
    $this->seedPathwaysAndTracks();
    $this->truncateRosterTables();
  }

  public function test_dual_major_combo1(): void
  {
    $courseId = 1001;
    $this->seedCourse($courseId, 9001);
    $this->seedCombo(11, 101, 201, 1);
    $this->seedComboMap(201, 101, 102);
    $this->seedCurriculumRow([
      'program_combo_refid' => 11,
      'batch' => 1,
      'semester' => 1,
      'course_id' => $courseId,
      'delivery_category' => ProgramWiseSemesterCourse::DELIVERY_MAJOR_COMBO1,
      'course_type' => ProgramWiseSemesterCourse::TYPE_AUTO,
      'offering_dept' => 9001,
    ]);

    $studentId = $this->seedStudent([
      'new_program_id' => 201,
      'batch' => 1,
      'academic_pathway_id' => 2,
      'selected_combo_id' => null,
    ]);
    $this->enrollStudent($studentId, $courseId, 1, 1);

    $roster = $this->roster($courseId, ['batch_id' => 1, 'semester_id' => 1]);
    $this->assertSame([$studentId], $roster->pluck('student_id')->all());
  }

  public function test_dual_major_combo2(): void
  {
    $courseId = 1002;
    $this->seedCourse($courseId, 9002);
    $this->seedCombo(12, 101, 202, 1);
    $this->seedComboMap(202, 101, 102);
    $this->seedCurriculumRow([
      'program_combo_refid' => 12,
      'batch' => 1,
      'semester' => 1,
      'course_id' => $courseId,
      'delivery_category' => ProgramWiseSemesterCourse::DELIVERY_MAJOR_COMBO2,
      'course_type' => ProgramWiseSemesterCourse::TYPE_AUTO,
      'offering_dept' => 9002,
    ]);

    $studentId = $this->seedStudent([
      'new_program_id' => 202,
      'batch' => 1,
      'academic_pathway_id' => 2,
    ]);
    $this->enrollStudent($studentId, $courseId, 1, 1);

    $roster = $this->roster($courseId, ['batch_id' => 1, 'semester_id' => 1]);
    $this->assertSame([$studentId], $roster->pluck('student_id')->all());
  }

  public function test_dual_major_mdc_auto_follows_combo1(): void
  {
    $courseId = 1003;
    $this->seedCourse($courseId, 9003);
    $this->seedCombo(13, 101, 203, 1);
    $this->seedComboMap(203, 101, 102);
    $this->seedCurriculumRow([
      'program_combo_refid' => 13,
      'batch' => 1,
      'semester' => 1,
      'course_id' => $courseId,
      'delivery_category' => ProgramWiseSemesterCourse::DELIVERY_OPEN_CHOICE,
      'course_type' => ProgramWiseSemesterCourse::TYPE_AUTO,
      'offering_dept' => 9010,
    ]);

    $studentId = $this->seedStudent([
      'new_program_id' => 203,
      'batch' => 1,
      'academic_pathway_id' => 2,
    ]);
    $this->enrollStudent($studentId, $courseId, 1);

    $roster = $this->roster($courseId, ['batch_id' => 1, 'semester_id' => 1]);
    $this->assertSame([$studentId], $roster->pluck('student_id')->all());
  }

  public function test_dual_major_mdc_student_choice(): void
  {
    $courseId = 1004;
    $this->seedCourse($courseId, 9050);
    $this->seedCombo(14, 101, 204, 1);
    $this->seedComboMap(204, 101, 102);
    $this->seedCurriculumRow([
      'program_combo_refid' => 14,
      'batch' => 1,
      'semester' => 1,
      'course_id' => $courseId,
      'delivery_category' => ProgramWiseSemesterCourse::DELIVERY_OPEN_CHOICE,
      'course_type' => ProgramWiseSemesterCourse::TYPE_STUDENT_CHOICE,
      'offering_dept' => 9050,
    ]);

    $studentId = $this->seedStudent([
      'new_program_id' => 204,
      'batch' => 1,
      'academic_pathway_id' => 2,
    ]);
    $this->enrollStudent($studentId, $courseId, 1);

    $roster = $this->roster($courseId, ['batch_id' => 1, 'semester_id' => 1]);
    $this->assertSame([$studentId], $roster->pluck('student_id')->all());
  }

  public function test_dual_major_mdc_selected_from_major_a_is_rejected(): void
  {
    $courseId = 1005;
    $this->seedCourse($courseId, 101);
    $this->seedCombo(15, 101, 205, 1);
    $this->seedComboMap(205, 101, 102);
    $this->seedCurriculumRow([
      'program_combo_refid' => 15,
      'batch' => 1,
      'semester' => 1,
      'course_id' => $courseId,
      'delivery_category' => ProgramWiseSemesterCourse::DELIVERY_OPEN_CHOICE,
      'course_type' => ProgramWiseSemesterCourse::TYPE_STUDENT_CHOICE,
      'offering_dept' => 101,
    ]);

    $studentId = $this->seedStudent([
      'new_program_id' => 205,
      'batch' => 1,
      'academic_pathway_id' => 2,
    ]);
    $this->enrollStudent($studentId, $courseId, 1);

    $roster = $this->roster($courseId, ['batch_id' => 1, 'semester_id' => 1]);
    $this->assertCount(0, $roster);
  }

  public function test_dual_major_mdc_selected_from_major_b_is_rejected(): void
  {
    $courseId = 1006;
    $this->seedCourse($courseId, 102);
    $this->seedCombo(16, 101, 206, 1);
    $this->seedComboMap(206, 101, 102);
    $this->seedCurriculumRow([
      'program_combo_refid' => 16,
      'batch' => 1,
      'semester' => 1,
      'course_id' => $courseId,
      'delivery_category' => ProgramWiseSemesterCourse::DELIVERY_OPEN_CHOICE,
      'course_type' => ProgramWiseSemesterCourse::TYPE_STUDENT_CHOICE,
      'offering_dept' => 102,
    ]);

    $studentId = $this->seedStudent([
      'new_program_id' => 206,
      'batch' => 1,
      'academic_pathway_id' => 2,
    ]);
    $this->enrollStudent($studentId, $courseId, 1);

    $roster = $this->roster($courseId, ['batch_id' => 1, 'semester_id' => 1]);
    $this->assertCount(0, $roster);
  }

  public function test_single_major_mdc_compulsory_follows_combo1(): void
  {
    $courseId = 1007;
    $this->seedCourse($courseId, 9007);
    $this->seedCombo(17, 101, 207, 1);
    $this->seedComboMap(207, 101, 102);
    $this->seedCurriculumRow([
      'program_combo_refid' => 17,
      'batch' => 1,
      'semester' => 1,
      'course_id' => $courseId,
      'delivery_category' => ProgramWiseSemesterCourse::DELIVERY_OPEN_CHOICE,
      'course_type' => ProgramWiseSemesterCourse::TYPE_AUTO,
      'offering_dept' => 9007,
    ]);

    $eligibleStudent = $this->seedStudent([
      'new_program_id' => 207,
      'batch' => 1,
      'academic_pathway_id' => 1,
      'selected_combo_id' => 101,
    ]);
    $this->enrollStudent($eligibleStudent, $courseId, 1);

    $ineligibleStudent = $this->seedStudent([
      'new_program_id' => 207,
      'batch' => 1,
      'academic_pathway_id' => 1,
      'selected_combo_id' => 102,
      'roll_no' => 'R-0002',
      'register_no' => 'REG-0002',
    ]);
    $this->enrollStudent($ineligibleStudent, $courseId, 1);

    $roster = $this->roster($courseId, ['batch_id' => 1, 'semester_id' => 1]);
    $this->assertSame([$eligibleStudent], $roster->pluck('student_id')->all());
  }

  public function test_single_major_mdc_student_choice(): void
  {
    $courseId = 1008;
    $this->seedCourse($courseId, 101);
    $this->seedCombo(18, 101, 208, 1);
    $this->seedComboMap(208, 101, 0);
    $this->seedCurriculumRow([
      'program_combo_refid' => 18,
      'batch' => 1,
      'semester' => 1,
      'course_id' => $courseId,
      'delivery_category' => ProgramWiseSemesterCourse::DELIVERY_OPEN_CHOICE,
      'course_type' => ProgramWiseSemesterCourse::TYPE_STUDENT_CHOICE,
      'offering_dept' => 101,
    ]);

    $studentId = $this->seedStudent([
      'new_program_id' => 208,
      'batch' => 1,
      'academic_pathway_id' => 1,
      'selected_combo_id' => 101,
    ]);
    $this->enrollStudent($studentId, $courseId, 1);

    $roster = $this->roster($courseId, ['batch_id' => 1, 'semester_id' => 1]);
    $this->assertSame([$studentId], $roster->pluck('student_id')->all());
  }

  public function test_common_auto_follows_combo1(): void
  {
    $courseId = 1009;
    $this->seedCourse($courseId, 9009);
    $this->seedCombo(19, 101, 209, 1);
    $this->seedComboMap(209, 101, 102);
    $this->seedCurriculumRow([
      'program_combo_refid' => 19,
      'batch' => 1,
      'semester' => 1,
      'course_id' => $courseId,
      'delivery_category' => ProgramWiseSemesterCourse::DELIVERY_PROGRAMME_COMMON,
      'course_type' => ProgramWiseSemesterCourse::TYPE_AUTO,
      'offering_dept' => 9009,
    ]);

    $eligibleStudent = $this->seedStudent([
      'new_program_id' => 209,
      'batch' => 1,
      'academic_pathway_id' => 1,
      'selected_combo_id' => 101,
    ]);
    $this->enrollStudent($eligibleStudent, $courseId, 1);

    $ineligibleStudent = $this->seedStudent([
      'new_program_id' => 209,
      'batch' => 1,
      'academic_pathway_id' => 1,
      'selected_combo_id' => 102,
      'roll_no' => 'R-0002',
      'register_no' => 'REG-0002',
    ]);
    $this->enrollStudent($ineligibleStudent, $courseId, 1);

    $roster = $this->roster($courseId, ['batch_id' => 1, 'semester_id' => 1]);
    $this->assertSame([$eligibleStudent], $roster->pluck('student_id')->all());
  }

  public function test_common_student_choice(): void
  {
    $courseId = 1010;
    $this->seedCourse($courseId, 9010);
    $this->seedCombo(20, 101, 210, 1);
    $this->seedComboMap(210, 101, 102);
    $this->seedCurriculumRow([
      'program_combo_refid' => 20,
      'batch' => 1,
      'semester' => 1,
      'course_id' => $courseId,
      'delivery_category' => ProgramWiseSemesterCourse::DELIVERY_PROGRAMME_COMMON,
      'course_type' => ProgramWiseSemesterCourse::TYPE_STUDENT_CHOICE,
      'offering_dept' => 9010,
    ]);

    $studentId = $this->seedStudent([
      'new_program_id' => 210,
      'batch' => 1,
      'academic_pathway_id' => 1,
      'selected_combo_id' => 102,
    ]);
    $this->enrollStudent($studentId, $courseId, 1);

    $roster = $this->roster($courseId, ['batch_id' => 1, 'semester_id' => 1]);
    $this->assertSame([$studentId], $roster->pluck('student_id')->all());
  }

  public function test_same_course_offered_by_multiple_programs_includes_all_program_students(): void
  {
    $courseId = 1011;
    $this->seedCourse($courseId, 9011);

    $this->seedCombo(21, 101, 211, 1);
    $this->seedComboMap(211, 101, 102);
    $this->seedCurriculumRow([
      'program_combo_refid' => 21,
      'batch' => 1,
      'semester' => 1,
      'course_id' => $courseId,
      'delivery_category' => ProgramWiseSemesterCourse::DELIVERY_MAJOR_COMBO1,
      'course_type' => ProgramWiseSemesterCourse::TYPE_AUTO,
      'offering_dept' => 9011,
    ]);

    $this->seedCombo(22, 101, 212, 1);
    $this->seedComboMap(212, 101, 102);
    $this->seedCurriculumRow([
      'program_combo_refid' => 22,
      'batch' => 1,
      'semester' => 1,
      'course_id' => $courseId,
      'delivery_category' => ProgramWiseSemesterCourse::DELIVERY_MAJOR_COMBO1,
      'course_type' => ProgramWiseSemesterCourse::TYPE_AUTO,
      'offering_dept' => 9011,
    ]);

    $studentOne = $this->seedStudent(['new_program_id' => 211, 'batch' => 1, 'academic_pathway_id' => 2]);
    $studentTwo = $this->seedStudent([
      'new_program_id' => 212,
      'batch' => 1,
      'academic_pathway_id' => 2,
      'roll_no' => 'R-0002',
      'register_no' => 'REG-0002',
    ]);

    $this->enrollStudent($studentOne, $courseId, 1);
    $this->enrollStudent($studentTwo, $courseId, 1);

    $roster = $this->roster($courseId, ['batch_id' => 1, 'semester_id' => 1]);
    $this->assertSame([$studentOne, $studentTwo], $roster->pluck('student_id')->all());
  }

  public function test_where_is_left_students_are_excluded(): void
  {
    $courseId = 1012;
    $this->seedCourse($courseId, 9012);
    $this->seedCombo(23, 101, 213, 1);
    $this->seedComboMap(213, 101, 0);
    $this->seedCurriculumRow([
      'program_combo_refid' => 23,
      'batch' => 1,
      'semester' => 1,
      'course_id' => $courseId,
      'delivery_category' => ProgramWiseSemesterCourse::DELIVERY_MAJOR_COMBO1,
      'course_type' => ProgramWiseSemesterCourse::TYPE_AUTO,
      'offering_dept' => 9012,
    ]);

    $activeStudent = $this->seedStudent([
      'new_program_id' => 213,
      'batch' => 1,
      'academic_pathway_id' => 1,
      'selected_combo_id' => 101,
      'where_is_left' => 0,
    ]);
    $leftStudent = $this->seedStudent([
      'new_program_id' => 213,
      'batch' => 1,
      'academic_pathway_id' => 1,
      'selected_combo_id' => 101,
      'where_is_left' => 1,
      'roll_no' => 'R-0002',
      'register_no' => 'REG-0002',
    ]);

    $this->enrollStudent($activeStudent, $courseId, 1);
    $this->enrollStudent($leftStudent, $courseId, 1);

    $roster = $this->roster($courseId, ['batch_id' => 1, 'semester_id' => 1]);
    $this->assertSame([$activeStudent], $roster->pluck('student_id')->all());
  }

  public function test_normal_course_is_semester_restricted(): void
  {
    $courseId = 1013;
    $this->seedCourse($courseId, 9013);
    $this->seedCombo(24, 101, 214, 1);
    $this->seedComboMap(214, 101, 0);

    $this->seedCurriculumRow([
      'program_combo_refid' => 24,
      'batch' => 1,
      'semester' => 1,
      'course_id' => $courseId,
      'delivery_category' => ProgramWiseSemesterCourse::DELIVERY_MAJOR_COMBO1,
      'course_type' => ProgramWiseSemesterCourse::TYPE_AUTO,
      'offering_dept' => 9013,
    ]);

    $sem1Student = $this->seedStudent([
      'new_program_id' => 214,
      'batch' => 1,
      'academic_pathway_id' => 1,
      'selected_combo_id' => 101,
    ]);
    $sem2Student = $this->seedStudent([
      'new_program_id' => 214,
      'batch' => 1,
      'academic_pathway_id' => 1,
      'selected_combo_id' => 101,
      'roll_no' => 'R-0002',
      'register_no' => 'REG-0002',
    ]);

    $this->enrollStudent($sem1Student, $courseId, 1);
    $this->enrollStudent($sem2Student, $courseId, 2);

    $roster = $this->roster($courseId, ['batch_id' => 1, 'semester_id' => 1]);
    $this->assertSame([$sem1Student], $roster->pluck('student_id')->all());
  }

  public function test_explicit_deanery_group_allows_cross_program(): void
  {
    $courseId = 1014;
    $groupId = 701;
    $this->seedCourse($courseId, 9014);

    $studentOne = $this->seedStudent(['new_program_id' => 301, 'batch' => 1, 'academic_pathway_id' => 1, 'selected_combo_id' => 101]);
    $studentTwo = $this->seedStudent([
      'new_program_id' => 302,
      'batch' => 1,
      'academic_pathway_id' => 1,
      'selected_combo_id' => 101,
      'roll_no' => 'R-0002',
      'register_no' => 'REG-0002',
    ]);

    $this->enrollStudent($studentOne, $courseId, 1, $groupId);
    $this->enrollStudent($studentTwo, $courseId, 1, $groupId);

    $this->seedTeachingGroupItem($groupId, $courseId, 1, 1, 301);
    $this->seedTeachingGroupItem($groupId, $courseId, 1, 1, 302);

    $roster = $this->roster($courseId, ['subject_id' => 999, 'teaching_group_id' => $groupId]);
    $this->assertSame([$studentOne, $studentTwo], $roster->pluck('student_id')->all());
  }

  public function test_explicit_deanery_group_allows_cross_batch(): void
  {
    $courseId = 1015;
    $groupId = 702;
    $this->seedCourse($courseId, 9015);

    $batch1Student = $this->seedStudent(['new_program_id' => 303, 'batch' => 1, 'academic_pathway_id' => 1, 'selected_combo_id' => 101]);
    $batch2Student = $this->seedStudent([
      'new_program_id' => 303,
      'batch' => 2,
      'academic_pathway_id' => 1,
      'selected_combo_id' => 101,
      'roll_no' => 'R-0002',
      'register_no' => 'REG-0002',
    ]);

    $this->enrollStudent($batch1Student, $courseId, 1, $groupId);
    $this->enrollStudent($batch2Student, $courseId, 1, $groupId);

    $this->seedTeachingGroupItem($groupId, $courseId, 1, 1, 303);
    $this->seedTeachingGroupItem($groupId, $courseId, 2, 1, 303);

    $roster = $this->roster($courseId, ['subject_id' => 999, 'teaching_group_id' => $groupId]);
    $this->assertSame([$batch1Student, $batch2Student], $roster->pluck('student_id')->all());
  }

  public function test_explicit_deanery_group_allows_cross_semester(): void
  {
    $courseId = 1016;
    $groupId = 703;
    $this->seedCourse($courseId, 9016);

    $sem1Student = $this->seedStudent(['new_program_id' => 304, 'batch' => 1, 'academic_pathway_id' => 1, 'selected_combo_id' => 101]);
    $sem2Student = $this->seedStudent([
      'new_program_id' => 304,
      'batch' => 1,
      'academic_pathway_id' => 1,
      'selected_combo_id' => 101,
      'roll_no' => 'R-0002',
      'register_no' => 'REG-0002',
    ]);

    $this->enrollStudent($sem1Student, $courseId, 1, $groupId);
    $this->enrollStudent($sem2Student, $courseId, 2, $groupId);

    $this->seedTeachingGroupItem($groupId, $courseId, 1, 1, 304);
    $this->seedTeachingGroupItem($groupId, $courseId, 1, 2, 304);

    $roster = $this->roster($courseId, ['subject_id' => 999, 'teaching_group_id' => $groupId]);
    $this->assertSame([$sem1Student, $sem2Student], $roster->pluck('student_id')->all());
  }

  public function test_same_course_different_semesters_without_group_does_not_combine(): void
  {
    $courseId = 1017;
    $this->seedCourse($courseId, 9017);
    $this->seedCombo(25, 101, 215, 1);
    $this->seedComboMap(215, 101, 0);

    $this->seedCurriculumRow([
      'program_combo_refid' => 25,
      'batch' => 1,
      'semester' => 1,
      'course_id' => $courseId,
      'delivery_category' => ProgramWiseSemesterCourse::DELIVERY_MAJOR_COMBO1,
      'course_type' => ProgramWiseSemesterCourse::TYPE_AUTO,
      'offering_dept' => 9017,
    ]);

    $this->seedCurriculumRow([
      'program_combo_refid' => 25,
      'batch' => 1,
      'semester' => 2,
      'course_id' => $courseId,
      'delivery_category' => ProgramWiseSemesterCourse::DELIVERY_MAJOR_COMBO1,
      'course_type' => ProgramWiseSemesterCourse::TYPE_AUTO,
      'offering_dept' => 9017,
    ]);

    $sem1Student = $this->seedStudent([
      'new_program_id' => 215,
      'batch' => 1,
      'academic_pathway_id' => 1,
      'selected_combo_id' => 101,
    ]);
    $sem2Student = $this->seedStudent([
      'new_program_id' => 215,
      'batch' => 1,
      'academic_pathway_id' => 1,
      'selected_combo_id' => 101,
      'roll_no' => 'R-0002',
      'register_no' => 'REG-0002',
    ]);

    $this->enrollStudent($sem1Student, $courseId, 1);
    $this->enrollStudent($sem2Student, $courseId, 2);

    $roster = $this->roster($courseId, ['batch_id' => 1, 'semester_id' => 1]);
    $this->assertSame([$sem1Student], $roster->pluck('student_id')->all());
  }

  private function roster(int $courseId, array $context)
  {
    return app(StudentRosterEngine::class)->getRoster($courseId, $context)->values();
  }

  private function seedPathwaysAndTracks(): void
  {
    if (!DB::table('academic_pathway_masters')->where('id', 1)->exists()) {
      DB::table('academic_pathway_masters')->insert([
        ['id' => 1, 'name' => 'Single Major', 'created_at' => now(), 'updated_at' => now()],
        ['id' => 2, 'name' => 'Dual Major', 'created_at' => now(), 'updated_at' => now()],
      ]);
    }

    if (!DB::table('degree_track_masters')->where('id', 1)->exists()) {
      DB::table('degree_track_masters')->insert([
        ['id' => 1, 'name' => 'Regular', 'created_at' => now(), 'updated_at' => now()],
        ['id' => 2, 'name' => 'Honours', 'created_at' => now(), 'updated_at' => now()],
        ['id' => 3, 'name' => 'Honours with Research', 'created_at' => now(), 'updated_at' => now()],
      ]);
    }
  }

  private function seedCourse(int $courseId, int $departmentId): void
  {
    DB::table('program_course_masters')->insert([
      'id' => $courseId,
      'department' => $departmentId,
      'course_code' => 'C' . $courseId,
      'course_title' => 'Course ' . $courseId,
      'created_at' => now(),
      'updated_at' => now(),
    ]);
  }

  private function seedCombo(int $comboId, int $subjectId, int $programId, int $batchId): void
  {
    DB::table('subject_has_student_progams')->insert([
      'id' => $comboId,
      'subject_id' => $subjectId,
      'student_program_id' => $programId,
      'batch_id' => $batchId,
      'program_type' => 'UG',
      'created_at' => now(),
      'updated_at' => now(),
    ]);
  }

  private function seedComboMap(int $programId, int $combo1, int $combo2): void
  {
    DB::table('std_prog_combo_maps')->insert([
      'student_program_id' => $programId,
      'combo_id_1' => $combo1,
      'combo_id_2' => $combo2,
      'created_at' => now(),
      'updated_at' => now(),
    ]);
  }

  private function seedCurriculumRow(array $overrides): void
  {
    $table = (new ProgramWiseSemesterCourse())->getTable();

    $row = array_merge([
      'program_combo_refid' => 0,
      'batch' => 1,
      'semester' => 1,
      'course_id' => 0,
      'offering_dept' => null,
      'academic_pathway_id' => null,
      'degree_track_id' => null,
      'course_type' => ProgramWiseSemesterCourse::TYPE_AUTO,
      'delivery_category' => ProgramWiseSemesterCourse::DELIVERY_PROGRAMME_COMMON,
      'specialization_mode' => 'COMMON',
      'specialization_master_id' => null,
      'specialization_master_ids' => null,
      'is_active' => 1,
      'created_at' => now(),
      'updated_at' => now(),
    ], $overrides);

    DB::table($table)->insert($row);
  }

  private function seedStudent(array $overrides): int
  {
    static $studentId = 5000;
    $studentId++;

    $row = array_merge([
      'id' => $studentId,
      'first_name' => 'Student',
      'last_name' => (string) $studentId,
      'gender' => '1',
      'new_program_id' => 0,
      'batch' => 1,
      'academic_pathway_id' => 1,
      'degree_track_id' => 1,
      'selected_combo_id' => null,
      'academic_dept_id' => null,
      'roll_no' => 'R-0001',
      'register_no' => 'REG-0001',
      'is_deleted' => 0,
      'is_left' => 0,
      'where_is_left' => 0,
      'status' => '0',
      'created_at' => now(),
      'updated_at' => now(),
    ], $overrides);

    DB::table('student_masters')->insert($row);

    return (int) $row['id'];
  }

  private function enrollStudent(int $studentId, int $courseId, int $semesterId, int $allocationGroupId = 1): void
  {
    DB::table('student_course_infos')->insert([
      'student_id' => $studentId,
      'course_id' => $courseId,
      'semester' => $semesterId,
      'allocation_group_id' => $allocationGroupId,
      'is_deleted' => 0,
      'created_at' => now(),
      'updated_at' => now(),
    ]);
  }

  private function seedTeachingGroupItem(int $groupId, int $courseId, int $batchId, int $semesterId, int $programId): void
  {
    DB::table('teaching_group_items')->insert([
      'subject_id' => 999,
      'allocation_group_id' => $groupId,
      'course_id' => $courseId,
      'batch_id' => $batchId,
      'semester_id' => $semesterId,
      'student_program_id' => $programId,
      'delivery_type' => ProgramWiseSemesterCourse::DELIVERY_PROGRAMME_COMMON,
      'created_at' => now(),
      'updated_at' => now(),
    ]);
  }

  private function truncateRosterTables(): void
  {
    $table = (new ProgramWiseSemesterCourse())->getTable();

    foreach (
      [
        'teaching_group_items',
        'student_specializations',
        'student_course_infos',
        'student_masters',
        'std_prog_combo_maps',
        'subject_has_student_progams',
        $table,
        'program_course_masters',
        'teaching_assignments',
      ] as $name
    ) {
      if (Schema::hasTable($name)) {
        DB::table($name)->delete();
      }
    }
  }

  private function ensureRosterTables(): void
  {
    if (!Schema::hasTable('program_course_masters')) {
      Schema::create('program_course_masters', function (Blueprint $table) {
        $table->id();
        $table->unsignedBigInteger('department')->nullable();
        $table->string('course_code')->nullable();
        $table->string('course_title')->nullable();
        $table->timestamps();
        $table->softDeletes();
      });
    }

    if (!Schema::hasTable('subject_has_student_progams')) {
      Schema::create('subject_has_student_progams', function (Blueprint $table) {
        $table->id();
        $table->unsignedBigInteger('subject_id')->nullable();
        $table->unsignedBigInteger('student_program_id')->nullable();
        $table->unsignedBigInteger('batch_id')->nullable();
        $table->string('program_type')->nullable();
        $table->timestamps();
        $table->softDeletes();
      });
    }

    if (!Schema::hasTable('std_prog_combo_maps')) {
      Schema::create('std_prog_combo_maps', function (Blueprint $table) {
        $table->id();
        $table->unsignedBigInteger('student_program_id')->nullable();
        $table->unsignedBigInteger('combo_id_1')->nullable();
        $table->unsignedBigInteger('combo_id_2')->nullable();
        $table->timestamps();
        $table->softDeletes();
      });
    }

    if (!Schema::hasTable('student_course_infos')) {
      Schema::create('student_course_infos', function (Blueprint $table) {
        $table->id();
        $table->unsignedBigInteger('student_id');
        $table->unsignedBigInteger('course_id');
        $table->unsignedBigInteger('semester')->nullable();
        $table->unsignedInteger('allocation_group_id')->default(1);
        $table->boolean('is_deleted')->default(0);
        $table->timestamps();
        $table->softDeletes();
      });
    }

    if (!Schema::hasTable('academic_pathway_masters')) {
      Schema::create('academic_pathway_masters', function (Blueprint $table) {
        $table->id();
        $table->string('name')->nullable();
        $table->timestamps();
      });
    }

    if (!Schema::hasTable('degree_track_masters')) {
      Schema::create('degree_track_masters', function (Blueprint $table) {
        $table->id();
        $table->string('name')->nullable();
        $table->timestamps();
      });
    }

    if (!Schema::hasTable('student_masters')) {
      Schema::create('student_masters', function (Blueprint $table) {
        $table->id();
        $table->string('first_name');
        $table->string('last_name')->nullable();
        $table->string('gender')->nullable();
        $table->unsignedBigInteger('new_program_id')->nullable();
        $table->unsignedBigInteger('batch')->nullable();
        $table->unsignedBigInteger('academic_pathway_id')->nullable();
        $table->unsignedBigInteger('degree_track_id')->nullable();
        $table->unsignedBigInteger('selected_combo_id')->nullable();
        $table->unsignedBigInteger('academic_dept_id')->nullable();
        $table->string('roll_no')->nullable();
        $table->string('register_no')->nullable();
        $table->boolean('is_deleted')->default(0);
        $table->boolean('is_left')->default(0);
        $table->boolean('where_is_left')->default(0);
        $table->string('status')->default('0');
        $table->timestamps();
        $table->softDeletes();
      });
    } elseif (!Schema::hasColumn('student_masters', 'where_is_left')) {
      Schema::table('student_masters', function (Blueprint $table) {
        $table->boolean('where_is_left')->default(0)->after('is_left');
      });
    }

    $curriculumTable = (new ProgramWiseSemesterCourse())->getTable();
    if (!Schema::hasTable($curriculumTable)) {
      Schema::create($curriculumTable, function (Blueprint $table) {
        $table->id();
        $table->unsignedBigInteger('program_combo_refid')->nullable();
        $table->unsignedBigInteger('batch')->nullable();
        $table->unsignedBigInteger('semester')->nullable();
        $table->unsignedBigInteger('course_id')->nullable();
        $table->unsignedBigInteger('offering_dept')->nullable();
        $table->unsignedBigInteger('academic_pathway_id')->nullable();
        $table->unsignedBigInteger('degree_track_id')->nullable();
        $table->string('course_type')->default('AUTO');
        $table->string('delivery_category')->nullable();
        $table->string('specialization_mode')->default('COMMON');
        $table->unsignedBigInteger('specialization_master_id')->nullable();
        $table->json('specialization_master_ids')->nullable();
        $table->boolean('is_active')->default(1);
        $table->timestamps();
        $table->softDeletes();
      });
    }

    if (!Schema::hasTable('student_specializations')) {
      Schema::create('student_specializations', function (Blueprint $table) {
        $table->id();
        $table->unsignedBigInteger('student_id');
        $table->unsignedBigInteger('subject_has_student_program_id');
        $table->unsignedBigInteger('specialization_id');
        $table->unsignedBigInteger('semester_id')->nullable();
        $table->boolean('is_active')->default(1);
        $table->timestamps();
        $table->softDeletes();
      });
    }

    if (!Schema::hasTable('teaching_assignments')) {
      Schema::create('teaching_assignments', function (Blueprint $table) {
        $table->id();
        $table->unsignedBigInteger('allocation_group')->nullable();
        $table->timestamps();
        $table->softDeletes();
      });
    }

    if (!Schema::hasTable('teaching_group_items')) {
      Schema::create('teaching_group_items', function (Blueprint $table) {
        $table->id();
        $table->unsignedBigInteger('subject_id')->nullable();
        $table->unsignedInteger('allocation_group_id');
        $table->unsignedBigInteger('course_id');
        $table->unsignedBigInteger('batch_id')->nullable();
        $table->unsignedBigInteger('semester_id')->nullable();
        $table->unsignedBigInteger('student_program_id')->nullable();
        $table->string('delivery_type')->nullable();
        $table->timestamps();
        $table->softDeletes();
      });
    }
  }
}
