<?php

namespace App\Services;

use App\Models\PaymentBatch;
use App\Models\PaymentBatchItem;
use App\Models\FacultyRemuneration;
use Illuminate\Support\Facades\DB;

class PaymentBatchService
{
  /**
   * Mark all remunerations in a batch as paid
   */
  public function markAsPaid($batchId)
  {
    return DB::transaction(function () use ($batchId) {
      $batch = PaymentBatch::findOrFail($batchId);

      if ($batch->status !== 'approved') {
        throw new \Exception('Only approved batches can be marked as paid.');
      }

      foreach ($batch->items as $item) {
        $rem = $item->facultyRemuneration;
        if ($rem) {
          $rem->status = 'paid';
          $rem->save();
        }
      }

      $batch->status = 'paid';
      $batch->save();
      return true;
    });
  }
}
