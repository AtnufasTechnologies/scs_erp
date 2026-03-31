<?php

namespace App\Http\Controllers;

use App\Models\ExamSystem\Promotion;
use App\Models\StudentMaster;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PromotionController extends Controller
{
  public function index(Request $request)
  {
    $query = Promotion::with(['student']);

    if ($request->has('from_semester') && $request->from_semester != '') {
      $query->where('from_semester', $request->from_semester);
    }

    if ($request->has('to_semester') && $request->to_semester != '') {
      $query->where('to_semester', $request->to_semester);
    }

    if ($request->has('status') && $request->status != '') {
      $query->where('status', $request->status);
    }

    $promotions = $query->orderBy('created_at', 'desc')->paginate(50);

    return view('coe.promotion.index', compact('promotions'));
  }

  public function create()
  {
    $students = StudentMaster::where('is_deleted', 0)->where('is_left', 0)->get();

    return view('coe.promotion.create', compact('students'));
  }

  public function store(Request $request)
  {
    $request->validate([
      'exam_student_id' => 'required|exists:student_masters,id',
      'from_semester' => 'required|integer',
      'to_semester' => 'required|integer',
      'academic_year' => 'required|string',
    ]);

    Promotion::create(array_merge($request->all(), ['status' => 'pending']));

    return redirect()->route('coe.promotion.index')
      ->with('success', 'Promotion entry created successfully');
  }

  public function show($id)
  {
    $promotion = Promotion::with(['student'])->findOrFail($id);
    return view('coe.promotion.show', compact('promotion'));
  }

  public function edit($id)
  {
    $promotion = Promotion::findOrFail($id);
    $students = StudentMaster::where('is_deleted', 0)->where('is_left', 0)->get();

    return view('coe.promotion.edit', compact('promotion', 'students'));
  }

  public function update(Request $request, $id)
  {
    $request->validate([
      'exam_student_id' => 'required|exists:student_masters,id',
      'from_semester' => 'required|integer',
      'to_semester' => 'required|integer',
      'academic_year' => 'required|string',
      'status' => 'nullable|in:pending,approved,rejected',
    ]);

    $promotion = Promotion::findOrFail($id);
    $promotion->update($request->all());

    return redirect()->route('coe.promotion.index')
      ->with('success', 'Promotion updated successfully');
  }

  public function destroy($id)
  {
    $promotion = Promotion::findOrFail($id);
    $promotion->delete();

    return redirect()->route('coe.promotion.index')
      ->with('success', 'Promotion deleted successfully');
  }

  public function approve($id)
  {
    $promotion = Promotion::findOrFail($id);
    $promotion->update(['status' => 'approved']);

    return redirect()->back()->with('success', 'Promotion approved');
  }

  public function bulkPromote(Request $request)
  {
    $request->validate([
      'from_semester' => 'required|integer',
      'to_semester' => 'required|integer',
      'academic_year' => 'required|string',
      'student_ids' => 'required|array',
    ]);

    try {
      DB::beginTransaction();

      foreach ($request->student_ids as $studentId) {
        Promotion::create([
          'exam_student_id' => $studentId,
          'from_semester' => $request->from_semester,
          'to_semester' => $request->to_semester,
          'academic_year' => $request->academic_year,
          'status' => 'approved',
        ]);
      }

      DB::commit();
      return redirect()->route('coe.promotion.index')
        ->with('success', 'Bulk promotion completed successfully');
    } catch (\Exception $e) {
      DB::rollBack();
      return redirect()->back()
        ->with('error', 'Bulk promotion failed: ' . $e->getMessage());
    }
  }

  public function export(Request $request)
  {
    $query = Promotion::with(['student', 'fromSemester', 'toSemester']);

    if ($request->has('session_id') && $request->session_id != '') {
      $query->where('session_id', $request->session_id);
    }

    $promotions = $query->get();
    return response()->json($promotions);
  }
}
