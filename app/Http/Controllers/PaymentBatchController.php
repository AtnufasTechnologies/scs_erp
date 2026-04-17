<?php

namespace App\Http\Controllers;

use App\Models\PaymentBatch;
use App\Models\PaymentBatchItem;
use App\Models\FacultyRemuneration;
use App\Models\ExamSystem\FacultyProfile;
use App\Services\PaymentBatchService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PaymentBatchController extends Controller
{
  public function index(Request $request)
  {
    $query = PaymentBatch::withCount('items');

    if ($request->filled('status')) {
      $query->where('status', $request->status);
    }

    $batches = $query->orderBy('created_at', 'desc')->paginate(20);

    // Summary stats
    $totalBatches = PaymentBatch::count();
    $totalAmount = PaymentBatch::sum('total_amount');
    $draftCount = PaymentBatch::draft()->count();
    $approvedCount = PaymentBatch::approved()->count();
    $paidCount = PaymentBatch::paid()->count();
    $paidAmount = PaymentBatch::paid()->sum('total_amount');

    // Approved remunerations available for batching
    $availableForBatch = FacultyRemuneration::approved()->count();

    return view('coe.payment-batches.index', compact(
      'batches',
      'totalBatches',
      'totalAmount',
      'draftCount',
      'approvedCount',
      'paidCount',
      'paidAmount',
      'availableForBatch'
    ));
  }

  public function create()
  {
    // Get approved remunerations grouped by faculty
    $remunerations = FacultyRemuneration::with('faculty')
      ->approved()
      ->orderBy('faculty_id')
      ->get();

    $grouped = $remunerations->groupBy('faculty_id')->map(function ($items) {
      return [
        'faculty' => $items->first()->faculty,
        'items' => $items,
        'total' => $items->sum('total_amount'),
        'count' => $items->count(),
      ];
    });

    return view('coe.payment-batches.create', compact('grouped', 'remunerations'));
  }

  public function store(Request $request)
  {
    $request->validate([
      'batch_name' => 'required|string|max:255',
      'remuneration_ids' => 'required|array|min:1',
      'remuneration_ids.*' => 'exists:faculty_remunerations,id',
    ]);

    try {
      DB::beginTransaction();

      $remunerations = FacultyRemuneration::whereIn('id', $request->remuneration_ids)
        ->where('status', 'approved')
        ->get();

      if ($remunerations->isEmpty()) {
        return redirect()->back()->with('error', 'No approved remunerations selected.');
      }

      $totalAmount = $remunerations->sum('total_amount');

      $batch = PaymentBatch::create([
        'batch_name' => $request->batch_name,
        'total_amount' => $totalAmount,
        'status' => 'draft',
      ]);

      foreach ($remunerations as $rem) {
        PaymentBatchItem::create([
          'batch_id' => $batch->id,
          'faculty_remuneration_id' => $rem->id,
        ]);
      }

      DB::commit();
      return redirect()->route('admin.payment-batches.show', $batch->id)
        ->with('success', "Payment batch created with {$remunerations->count()} items totalling " . number_format($totalAmount, 2));
    } catch (\Exception $e) {
      DB::rollBack();
      return redirect()->back()->with('error', 'Failed to create batch: ' . $e->getMessage());
    }
  }

  public function show($id)
  {
    $batch = PaymentBatch::with(['items.facultyRemuneration.faculty'])->findOrFail($id);

    // Group items by faculty for display
    $byFaculty = $batch->items->groupBy(function ($item) {
      return $item->facultyRemuneration->faculty_id ?? 0;
    })->map(function ($items) {
      $first = $items->first()->facultyRemuneration;
      return [
        'faculty' => $first->faculty,
        'items' => $items,
        'total' => $items->sum(fn($i) => $i->facultyRemuneration->total_amount ?? 0),
      ];
    });

    return view('coe.payment-batches.show', compact('batch', 'byFaculty'));
  }

  public function approve($id)
  {
    $batch = PaymentBatch::findOrFail($id);

    if ($batch->status !== 'draft') {
      return redirect()->back()->with('error', 'Only draft batches can be approved.');
    }

    $batch->update(['status' => 'approved']);

    return redirect()->back()->with('success', 'Payment batch approved successfully.');
  }

  public function markPaid($id)
  {
    try {
      $service = new PaymentBatchService();
      $service->markAsPaid($id);

      return redirect()->back()->with('success', 'Payment batch marked as paid. All remunerations updated.');
    } catch (\Exception $e) {
      return redirect()->back()->with('error', 'Failed: ' . $e->getMessage());
    }
  }
}
