<?php

namespace App\Services;

use App\Models\RemunerationRate;
use App\Models\FacultyRemuneration;
use App\Models\Faculty;
use Illuminate\Support\Facades\DB;

class RemunerationService
{
  public function calculateInvigilation($faculty_id, $sessions)
  {
    $rate = RemunerationRate::where('duty_type', 'invigilation')
      ->where('rate_type', 'per_session')
      ->first()?->amount ?? 0;
    return $sessions * $rate;
  }

  public function calculateEvaluation($faculty_id, $copies)
  {
    $rate = RemunerationRate::where('duty_type', 'evaluation')
      ->where('rate_type', 'per_copy')
      ->first()?->amount ?? 0;
    return $copies * $rate;
  }

  public function calculateModeration($faculty_id)
  {
    $rate = RemunerationRate::where('duty_type', 'moderation')
      ->where('rate_type', 'per_session')
      ->first()?->amount ?? 0;
    return $rate;
  }

  /**
   * Generate and store remuneration for a duty
   * @param array $duty ['faculty_id', 'duty_type', 'reference_id', 'quantity']
   * @return FacultyRemuneration|null
   */
  public function generateRemuneration(array $duty)
  {
    $faculty_id = $duty['faculty_id'];
    $duty_type = $duty['duty_type'];
    $reference_id = $duty['reference_id'];
    $quantity = $duty['quantity'] ?? 1;
    $rate = 0;
    $total = 0;

    switch ($duty_type) {
      case 'invigilation':
        $rate = RemunerationRate::where('duty_type', 'invigilation')->where('rate_type', 'per_session')->first()?->amount ?? 0;
        $total = $quantity * $rate;
        break;
      case 'evaluation':
        $rate = RemunerationRate::where('duty_type', 'evaluation')->where('rate_type', 'per_copy')->first()?->amount ?? 0;
        $total = $quantity * $rate;
        break;
      case 'moderation':
        $rate = RemunerationRate::where('duty_type', 'moderation')->where('rate_type', 'per_session')->first()?->amount ?? 0;
        $total = $rate;
        break;
    }

    if ($rate <= 0) {
      return null;
    }

    return FacultyRemuneration::create([
      'faculty_id' => $faculty_id,
      'duty_type' => $duty_type,
      'reference_id' => $reference_id,
      'quantity' => $quantity,
      'rate' => $rate,
      'total_amount' => $total,
      'status' => 'pending',
      'generated_at' => now(),
    ]);
  }
}
