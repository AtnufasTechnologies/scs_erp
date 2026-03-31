<?php

namespace App\Services;

use App\Models\PaymentBatch;
use App\Models\PaymentBatchItem;
use App\Models\FacultyRemuneration;
use Illuminate\Support\Facades\DB;

class PaymentBatchService
{
  /**
   * Create a new payment batch from all pending remunerations
   * @param string $batchName
   * @return PaymentBatch
   */
  public function createBatch($batchName = null)
  {
    return DB::transaction(function () use ($batchName) {
      $pending = FacultyRemuneration::where('status', 'approved')->get();
      if ($pending->isEmpty()) {
        return null;
      }
      $total = $pending->sum('total_amount');
      $batch = PaymentBatch::create([
        'batch_name' => $batchName ?? ('Batch-' . now()->format('Ymd-His')),
        'total_amount' => $total,
        'status' => 'draft',
      ]);
      foreach ($pending as $rem) {
        PaymentBatchItem::create([
          'batch_id' => $batch->id,
          'faculty_remuneration_id' => $rem->id,
        ]);
        $rem->status = 'pending'; // Optionally set to 'pending' in batch
        $rem->save();
      }
      return $batch;
    });
  }

  /**
   * Mark all remunerations in a batch as paid
   * @param int $batchId
   * @return bool
   */
  public function markAsPaid($batchId)
  {
    return DB::transaction(function () use ($batchId) {
      $batch = PaymentBatch::findOrFail($batchId);
      $items = $batch->items;
      foreach ($items as $item) {
        $rem = $item->facultyRemuneration;
        $rem->status = 'paid';
        $rem->save();
      }
      $batch->status = 'paid';
      $batch->save();
      return true;
    });
  }
}
