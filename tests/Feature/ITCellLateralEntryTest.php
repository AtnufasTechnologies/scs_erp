<?php

namespace Tests\Feature;

use App\Models\BatchMaster;
use App\Models\Campus;
use App\Models\DepartmentMaster;
use App\Models\StudentMaster;
use App\Models\StudentProgram;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ITCellLateralEntryTest extends TestCase
{
  use RefreshDatabase;

  public function test_it_can_create_and_audit_lateral_entry_student(): void
  {
    $user = User::factory()->create();
    $this->actingAs($user);

    $campus = Campus::create(['name' => 'Sonada', 'slug' => 'sonada']);
    $batch = BatchMaster::create(['batch_name' => '2025-26']);
    $department = DepartmentMaster::create(['name' => 'Computer Science', 'department_code' => 'CS', 'campus_id' => $campus->id]);
    $program = StudentProgram::create([
      'name' => 'BSc Computer Science',
      'code' => 'BCS',
      'semester_count' => 6,
      'campus_id' => $campus->id,
      'department' => $department->id,
      'programme' => null,
      'program_type' => '1',
    ]);

    $response = $this->post(route('itcell.lateral-entry.store'), [
      'first_name' => 'Lateral',
      'last_name' => 'Student',
      'gender' => '1',
      'mobile_no' => '9876543210',
      'mail_id' => 'lateral@example.com',
      'campus_id' => $campus->id,
      'department' => $department->id,
      'new_program_id' => $program->id,
      'batch' => $batch->id,
      'admission_date' => '2026-07-28',
      'current_year' => 2,
      'remarks' => 'Lateral entry test',
    ]);

    $response->assertRedirect();
    $response->assertSessionHas('success', 'Lateral entry student created successfully.');

    $student = StudentMaster::where('mail_id', 'lateral@example.com')->firstOrFail();
    $this->assertNotNull($student->roll_no);
    $this->assertEquals($program->id, $student->new_program_id);
    $this->assertEquals($batch->id, $student->batch);
    $this->assertEquals(2, $student->current_year);

    $this->assertDatabaseHas('user_activity_logs', [
      'auditable_type' => StudentMaster::class,
      'auditable_id' => (string) $student->id,
      'event' => 'created',
      'user_id' => $user->id,
      'description' => 'Created StudentMaster',
    ]);
  }

  public function test_it_can_return_programs_for_selected_batch_and_campus(): void
  {
    $user = User::factory()->create();
    $this->actingAs($user);

    $campus = Campus::create(['name' => 'Siliguri', 'slug' => 'siliguri']);
    $batch = BatchMaster::create(['batch_name' => '2026-27']);
    $department = DepartmentMaster::create(['name' => 'Mathematics', 'department_code' => 'MT', 'campus_id' => $campus->id]);
    $program = StudentProgram::create([
      'name' => 'BSc Mathematics',
      'code' => 'BMT',
      'semester_count' => 6,
      'campus_id' => $campus->id,
      'department' => $department->id,
      'programme' => null,
      'program_type' => '1',
    ]);

    StudentMaster::create([
      'first_name' => 'Existing',
      'last_name' => 'Student',
      'gender' => 1,
      'campus_id' => $campus->id,
      'batch' => $batch->id,
      'new_program_id' => $program->id,
      'status' => 'active',
      'user_type' => 'student',
    ]);

    $response = $this->getJson(route('itcell.lateral-entry.programs', [
      'campus_id' => $campus->id,
      'batch_id' => $batch->id,
    ]));

    $response->assertOk();
    $response->assertJsonPath('success', true);
    $response->assertJsonCount(1, 'programs');
    $response->assertJsonFragment(['id' => $program->id]);
  }
}
