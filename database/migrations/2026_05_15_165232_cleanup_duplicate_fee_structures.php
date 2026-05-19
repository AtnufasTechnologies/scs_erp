<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use App\Models\FeesStructure;
use App\Models\StudentPayment;

return new class extends Migration
{
  /**
   * Run the migrations.
   * This migration identifies and removes duplicate fee structures,
   * keeping the oldest record (lowest ID) and transferring any payments
   * to that record before deletion.
   */
  public function up(): void
  {
    // Find all duplicate groups
    $duplicates = DB::table('fees_structures')
      ->select('batch_id', 'program_id', 'course_name', 'std_current_year', 'yearly_pay_order', DB::raw('COUNT(*) as count'))
      ->whereNull('deleted_at')
      ->groupBy('batch_id', 'program_id', 'course_name', 'std_current_year', 'yearly_pay_order')
      ->having('count', '>', 1)
      ->get();

    foreach ($duplicates as $duplicate) {
      // Get all fee structures in this duplicate group, ordered by ID (oldest first)
      $feeStructures = FeesStructure::where('batch_id', $duplicate->batch_id)
        ->where('program_id', $duplicate->program_id)
        ->where('course_name', $duplicate->course_name)
        ->where('std_current_year', $duplicate->std_current_year)
        ->where('yearly_pay_order', $duplicate->yearly_pay_order)
        ->orderBy('id', 'asc')
        ->get();

      // Keep the first one (oldest), mark others for deletion
      $keepRecord = $feeStructures->first();
      $duplicatesToRemove = $feeStructures->slice(1);

      foreach ($duplicatesToRemove as $duplicateRecord) {
        // Transfer any student payments from duplicate to the kept record
        StudentPayment::where('fee_structure_id', $duplicateRecord->id)
          ->update(['fee_structure_id' => $keepRecord->id]);

        // Soft delete the duplicate
        $duplicateRecord->delete();

        // Log to Laravel log
        \Log::info('Duplicate fee structure removed', [
          'deleted_id' => $duplicateRecord->id,
          'kept_id' => $keepRecord->id,
          'batch_id' => $duplicate->batch_id,
          'program_id' => $duplicate->program_id,
        ]);
      }
    }
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    // Cannot safely undo this migration as it involves data cleanup
    // Manual restoration required if needed
  }
};
