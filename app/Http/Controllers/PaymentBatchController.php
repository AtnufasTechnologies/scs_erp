<?php

namespace App\Http\Controllers;

use App\Models\PaymentBatch;
use App\Models\Faculty;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PaymentBatchController extends Controller
{
  public function index(Request $request)
  {
    $query = PaymentBatch::with(['items']);

    if ($request->has('status') && $request->status != '') {
      $query->where('status', $request->status);
    }

    if ($request->has('batch_month') && $request->batch_month != '') {
      $query->where('batch_month', $request->batch_month);
    }

    $batches = $query->orderBy('created_at', 'desc')->paginate(50);

    return view('coe.payment-batches.index', compact('batch'));
  }

  public function create()
  {
    $faculties = Faculty::all();

    return view('coe.payment-batches.create', compact('faculties'));
  }

  public function store(Request $request)
  {
    $request->validate([
      'batch_name' => 'required|string',
      'batch_month' => 'required|date',
      'total_amount' => 'required|numeric|min:0',
    ]);

    PaymentBatch::create(array_merge($request->all(), ['status' => 'pending']));

    return redirect()->route('coe.payment-batches.index')
      ->with('success', 'Payment batch created successfully');
  }

  public function show($id)
  {
    $batch = PaymentBatch::with(['items.faculty'])->findOrFail($id);
    return view('coe.payment-batches.show', compact('batch'));
  }

  public function edit($id)
  {
    $batch = PaymentBatch::findOrFail($id);

    return view('coe.payment-batches.edit', compact('batch'));
  }

  public function update(Request $request, $id)
  {
    $request->validate([
      'batch_name' => 'required|string',
      'batch_month' => 'required|date',
      'total_amount' => 'required|numeric|min:0',
      'status' => 'nullable|in:pending,processed,completed',
    ]);

    $batch = PaymentBatch::findOrFail($id);
    $batch->update($request->all());

    return redirect()->route('coe.payment-batches.index')
      ->with('success', 'Payment batch updated successfully');
  }

  public function destroy($id)
  {
    $batch = PaymentBatch::findOrFail($id);
    $batch->delete();

    return redirect()->route('coe.payment-batches.index')
      ->with('success', 'Payment batch deleted successfully');
  }

  public function process($id)
  {
    try {
      DB::beginTransaction();

      $batch = PaymentBatch::findOrFail($id);
      $batch->update(['status' => 'processed', 'processed_at' => now()]);

      // Process all items in the batch
      // Update faculty remuneration status
      // Generate payment reports

      DB::commit();
      return redirect()->back()->with('success', 'Payment batch processed successfully');
    } catch (\Exception $e) {
      DB::rollBack();
      return redirect()->back()
        ->with('error', 'Processing failed: ' . $e->getMessage());
    }
  }

  public function export(Request $request)
  {
    $query = PaymentBatch::with(['creator']);

    if ($request->has('status') && $request->status != '') {
      $query->where('status', $request->status);
    }

    $batches = $query->get();
    return response()->json($batches);
  }
}
