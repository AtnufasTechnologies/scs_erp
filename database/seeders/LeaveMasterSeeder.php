<?php

namespace Database\Seeders;

use App\Models\LeaveMaster;
use Illuminate\Database\Seeder;

class LeaveMasterSeeder extends Seeder
{
  /**
   * Run the database seeds.
   */
  public function run(): void
  {
    $leaveTypes = [
      [
        'leave_type_name' => 'Casual Leave',
        'leave_type_code' => 'casual',
        'description' => 'Leave for personal reasons, family functions, or other casual purposes',
        'allowed_days_per_year' => 10,
        'requires_attachment' => false,
        'is_active' => true,
        'display_order' => 1,
        'badge_color' => 'primary',
      ],
      [
        'leave_type_name' => 'Sick Leave',
        'leave_type_code' => 'sick',
        'description' => 'Leave for medical reasons or health issues',
        'allowed_days_per_year' => null, // Unlimited
        'requires_attachment' => true,
        'is_active' => true,
        'display_order' => 2,
        'badge_color' => 'danger',
      ],
      [
        'leave_type_name' => 'Earned Leave',
        'leave_type_code' => 'earned',
        'description' => 'Leave earned through service, can be accumulated',
        'allowed_days_per_year' => 25,
        'requires_attachment' => false,
        'is_active' => true,
        'display_order' => 3,
        'badge_color' => 'success',
      ],
      [
        'leave_type_name' => 'Maternity Leave',
        'leave_type_code' => 'maternity',
        'description' => 'Leave for maternity purposes',
        'allowed_days_per_year' => 180,
        'requires_attachment' => true,
        'is_active' => true,
        'display_order' => 4,
        'badge_color' => 'info',
      ],
      [
        'leave_type_name' => 'Paternity Leave',
        'leave_type_code' => 'paternity',
        'description' => 'Leave for paternity purposes',
        'allowed_days_per_year' => 15,
        'requires_attachment' => true,
        'is_active' => true,
        'display_order' => 5,
        'badge_color' => 'info',
      ],
      [
        'leave_type_name' => 'Compensatory Off',
        'leave_type_code' => 'comp_off',
        'description' => 'Compensatory leave for extra work done',
        'allowed_days_per_year' => null, // Based on work done
        'requires_attachment' => false,
        'is_active' => true,
        'display_order' => 6,
        'badge_color' => 'warning',
      ],
      [
        'leave_type_name' => 'Other',
        'leave_type_code' => 'other',
        'description' => 'Other types of leave not covered above',
        'allowed_days_per_year' => null,
        'requires_attachment' => false,
        'is_active' => true,
        'display_order' => 99,
        'badge_color' => 'secondary',
      ],
    ];

    foreach ($leaveTypes as $leaveType) {
      LeaveMaster::updateOrCreate(
        ['leave_type_code' => $leaveType['leave_type_code']],
        $leaveType
      );
    }
  }
}
